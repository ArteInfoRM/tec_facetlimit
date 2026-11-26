<?php
/**
 * 2009-2026 Tecnoacquisti.com
 *
 * @author    Arte e Informatica <helpdesk@tecnoacquisti.com>
 * @copyright 2009-2026 Tecnoacquisti.com
 * @license   One Paid Licence By WebSite Using This Module.
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

define('_PS_VERSION_', 'test');
require dirname(__DIR__) . '/FacetLimitCounters.php';

function checkCounter($expected, $actual)
{
    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected counter result: ' . json_encode([$expected, $actual]));
    }
}

$directory = sys_get_temp_dir() . '/facetlimit-' . bin2hex(random_bytes(12));
mkdir($directory, 0700);
try {
    $shop = new FacetLimitCounters($directory, 1);
    $rates = [354 => 1, 10 => 1];
    checkCounter(0, $shop->admission(12, $rates, 354, 'observe'));
    checkCounter(true, $shop->admission(12, $rates, 354, 'observe') > 0);
    checkCounter(0, $shop->admission(12, $rates, 10, 'observe'));
    checkCounter(true, $shop->admission(12, $rates, 0, 'observe') > 0);
    checkCounter(2, $shop->snapshot()[0]['excess']);
    checkCounter(0, $shop->admission(12, $rates, 354, 'protect'));
    $secondShop = new FacetLimitCounters($directory, 2);
    checkCounter(0, $secondShop->admission(12, $rates, 354, 'protect'));

    // Simulate an idle interval without a wall-clock delay.
    $path = $directory . '/tec_fl_s1_aggregate.json';
    $snapshot = $shop->snapshot();
    foreach ($snapshot as &$bucket) {
        $bucket['time'] -= 60;
    }
    unset($bucket);
    file_put_contents($path, json_encode($snapshot));
    checkCounter(0, $shop->admission(12, $rates, 354, 'protect'));
    checkCounter(1, $shop->incrementIp('192.0.2.1', 3600));
    checkCounter(2, $shop->incrementIp('192.0.2.1', 3600));
    $shop->clearIpCounts();
    checkCounter(1, $shop->incrementIp('192.0.2.1', 3600));
    echo "Shared limit checks passed.\n";
} finally {
    foreach (glob($directory . '/*') as $path) {
        unlink($path);
    }
    rmdir($directory);
}
