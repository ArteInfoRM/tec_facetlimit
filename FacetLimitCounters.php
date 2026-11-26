<?php
/**
 * 2009-2026 Tecnoacquisti.com
 *
 * @author    Arte e Informatica <helpdesk@tecnoacquisti.com>
 * @copyright 2009-2026 Tecnoacquisti.com
 * @license   One Paid Licence By WebSite Using This Module.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Serialize short counter updates across PHP workers sharing the shop cache.
 */
final class FacetLimitCounters
{
    private $prefix;

    public function __construct($directory, $shopId)
    {
        $this->prefix = $directory . '/tec_fl_s' . (int) $shopId . '_';
    }

    /**
     * Commit the shop and category admission decision together.
     */
    public function admission($shopLimit, array $categories, $categoryId, $mode)
    {
        return $this->update('aggregate', function (&$state) use ($shopLimit, $categories, $categoryId, $mode) {
            $now = microtime(true);
            $limits = [0 => $shopLimit] + $categories;
            $state = array_intersect_key($state, $limits);
            $scopes = [0];
            if ($categoryId > 0 && isset($categories[$categoryId])) {
                $scopes[] = $categoryId;
            }
            $retry = 0;
            foreach ($scopes as $id) {
                $rate = $limits[$id];
                $minute = (int) floor($now / 60);
                // Allow a short burst followed by continuous refill at the configured rate.
                $capacity = max(1, (int) ceil($rate / 6));
                if (!isset($state[$id]) || $state[$id]['rate'] !== $rate || $state[$id]['mode'] !== $mode) {
                    $state[$id] = [
                        'rate' => $rate, 'mode' => $mode, 'tokens' => $capacity, 'time' => $now,
                        'minute' => $minute, 'requests' => 0, 'peak' => 0, 'excess' => 0,
                    ];
                }
                $bucket = &$state[$id];
                $bucket['tokens'] = min($capacity, $bucket['tokens'] + max(0, $now - $bucket['time']) * $rate / 60);
                $bucket['time'] = $now;
                if ($minute !== $bucket['minute']) {
                    $bucket['minute'] = $minute;
                    $bucket['requests'] = 0;
                }
                ++$bucket['requests'];
                $bucket['peak'] = max($bucket['peak'], $bucket['requests']);
                $retry = max($retry, (int) ceil((1 - $bucket['tokens']) * 60 / $rate));
                unset($bucket);
            }
            foreach ($scopes as $id) {
                if ($retry > 0) {
                    ++$state[$id]['excess'];
                } else {
                    --$state[$id]['tokens'];
                }
            }

            return $retry;
        });
    }

    /**
     * Reuse a fixed set of shards and expire IP counts on the next window.
     */
    public function incrementIp($ip, $window)
    {
        $hash = hash('sha256', $ip);

        return $this->update('ip_' . substr($hash, 0, 2), function (&$state) use ($hash, $window) {
            $period = (int) floor(time() / $window);
            if (($state['period'] ?? null) !== $period || ($state['window'] ?? null) !== $window) {
                $state = ['period' => $period, 'window' => $window, 'counts' => []];
            }
            $count = ($state['counts'][$hash] ?? 0) + 1;
            $state['counts'][$hash] = $count;

            return $count;
        });
    }

    /**
     * Reset existing file-backed IP counters under their regular writer locks.
     */
    public function clearIpCounts()
    {
        foreach (glob($this->prefix . 'ip_??.json') ?: [] as $path) {
            $name = substr($path, strlen($this->prefix), -5);
            $this->update($name, function (&$state) {
                $state = [];

                return 0;
            });
        }
    }

    /**
     * Atomic rename lets the back office read a complete snapshot without locking.
     */
    public function snapshot()
    {
        $contents = @file_get_contents($this->prefix . 'aggregate.json');

        return $contents === false ? [] : (json_decode($contents, true) ?: []);
    }

    private function update($name, callable $change)
    {
        $base = $this->prefix . $name;
        $lock = @fopen($base . '.lock', 'c');
        if (!$lock) {
            return 0;
        }
        try {
            if (!flock($lock, LOCK_EX)) {
                return 0;
            }
            $contents = @file_get_contents($base . '.json');
            $state = $contents === false ? [] : (json_decode($contents, true) ?: []);
            $result = $change($state);
            $json = json_encode($state);
            if ($json === false || @file_put_contents($base . '.tmp', $json) !== strlen($json)
                || !@rename($base . '.tmp', $base . '.json')) {
                return 0;
            }

            return $result;
        } finally {
            fclose($lock);
        }
    }
}
