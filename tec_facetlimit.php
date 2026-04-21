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

class Tec_FacetLimit extends Module
{
    const CONF_LIMIT      = 'TEC_FL_LIMIT';
    const CONF_WINDOW     = 'TEC_FL_WINDOW';
    const CONF_WHITELIST  = 'TEC_FL_WHITELIST';
    const CONF_ENABLED    = 'TEC_FL_ENABLED';
    const CONF_BOT_SCORE  = 'TEC_FL_BOT_SCORE';
    const LOG_FILE        = 'tec_facetlimit.log';

    const DEFAULT_LIMIT      = 30;   // max richieste ?q= per finestra per IP
    const DEFAULT_WINDOW     = 60;   // finestra in secondi
    const DEFAULT_BOT_SCORE  = 4;    // soglia punteggio bot oltre la quale si blocca

    // ── Punteggi fingerprinting bot ──────────────────────────────────────────
    // HTTP/1.0: quasi nessun browser reale usa ancora questo protocollo
    const SCORE_HTTP10        = 4;
    // Assenza del cookie PrestaShop-*: i bot di solito non gestiscono cookie
    const SCORE_NO_PS_COOKIE  = 2;
    // Assenza di Referer: su chiamate AJAX il browser lo manda sempre (same-origin)
    const SCORE_NO_REFERER    = 1;
    // Assenza completa di User-Agent: client non-browser
    const SCORE_NO_UA         = 3;

    public function __construct()
    {
        $this->name          = 'tec_facetlimit';
        $this->tab           = 'administration';
        $this->version       = '1.1.2';
        $this->author        = 'Tecnoacquisti.com';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Faceted Search Rate Limiter');
        $this->description = $this->l('Protects ps_facetedsearch against bot abuse and AI-scraper floods via the ?q= parameter.');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    // -------------------------------------------------------------------------
    //  Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('actionDispatcherBefore')
            && Configuration::updateValue(self::CONF_ENABLED,   1)
            && Configuration::updateValue(self::CONF_LIMIT,     self::DEFAULT_LIMIT)
            && Configuration::updateValue(self::CONF_WINDOW,    self::DEFAULT_WINDOW)
            && Configuration::updateValue(self::CONF_BOT_SCORE, self::DEFAULT_BOT_SCORE)
            && Configuration::updateValue(self::CONF_WHITELIST, '');
    }

    // -------------------------------------------------------------------------
    //  Admin configuration page
    // -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitTecFacetLimitModule')) {
            $output .= $this->postProcess();
        } elseif (Tools::isSubmit('submitTecFacetLimitWhitelist')) {
            $output .= $this->handleWhitelistSave();
        } elseif (Tools::isSubmit('tec_fl_clear_log')) {
            $output .= $this->handleClearLog();
        }

        $configUrl = $this->context->link->getAdminLink('AdminModules', true)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $this->context->smarty->assign([
            'current_ip'     => $this->getClientIp(),
            'whitelist'      => (string) Configuration::get(self::CONF_WHITELIST),
            'config_url'     => $configUrl,
            'log_entries'    => $this->getLogEntries(),
            'log_size'       => $this->getLogSize(),
            'apcu_available' => function_exists('apcu_inc'),
        ]);

        $output .= $this->renderForm();
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;
    }

    protected function renderForm(): string
    {
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitTecFacetLimitModule';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = [
            'fields_value' => $this->getConfigFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];
        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => ['title' => $this->l('Settings'), 'icon' => 'icon-cogs'],
                'input'  => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Enable protection'),
                        'name'    => self::CONF_ENABLED,
                        'is_bool' => true,
                        'values'  => [
                            ['id' => 'enabled_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Max requests per window (rate limit)'),
                        'name'  => self::CONF_LIMIT,
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('Max number of ?q= requests allowed per IP in the time window. Default: 30.'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Time window (seconds)'),
                        'name'  => self::CONF_WINDOW,
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('Duration of the rate-limit window in seconds. Default: 60.'),
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Bot score threshold'),
                        'name'    => self::CONF_BOT_SCORE,
                        'desc'    => $this->l('Block when the combined signal score reaches this value. Higher = more permissive. Scores: HTTP/1.0 +4, no User-Agent +3, no PS cookie +2, no Referer +1.'),
                        'options' => [
                            'query' => [
                                ['id' => 1, 'name' => '1 — ' . $this->l('Very aggressive (any signal blocks)')],
                                ['id' => 2, 'name' => '2 — ' . $this->l('Aggressive (no-cookie alone blocks)')],
                                ['id' => 3, 'name' => '3 — ' . $this->l('Recommended (HTTP/1.0, no-UA, no-cookie+no-referer)')],
                                ['id' => 4, 'name' => '4 — ' . $this->l('Recommended · default (HTTP/1.0 or no-UA+no-referer)')],
                                ['id' => 5, 'name' => '5 — ' . $this->l('Strict (HTTP/1.0+no-referer or no-cookie+no-UA)')],
                                ['id' => 6, 'name' => '6 — ' . $this->l('Very strict (HTTP/1.0+no-cookie or all three soft signals)')],
                                ['id' => 7, 'name' => '7 — ' . $this->l('Maximum (HTTP/1.0+no-UA or HTTP/1.0+no-cookie+no-referer)')],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }

    protected function getConfigFormValues(): array
    {
        return [
            self::CONF_ENABLED   => (int) Configuration::get(self::CONF_ENABLED),
            self::CONF_LIMIT     => (int) (Configuration::get(self::CONF_LIMIT)     ?: self::DEFAULT_LIMIT),
            self::CONF_WINDOW    => (int) (Configuration::get(self::CONF_WINDOW)    ?: self::DEFAULT_WINDOW),
            self::CONF_BOT_SCORE => (int) (Configuration::get(self::CONF_BOT_SCORE) ?: self::DEFAULT_BOT_SCORE),
        ];
    }

    protected function postProcess(): string
    {
        $limit = (int) Tools::getValue(self::CONF_LIMIT);
        if ($limit < 1 || $limit > 10000) {
            return $this->displayError($this->l('Max requests must be between 1 and 10000.'));
        }

        $window = (int) Tools::getValue(self::CONF_WINDOW);
        if ($window < 1 || $window > 3600) {
            return $this->displayError($this->l('Time window must be between 1 and 3600 seconds.'));
        }

        $botScore = (int) Tools::getValue(self::CONF_BOT_SCORE);
        if ($botScore < 1 || $botScore > 7) {
            return $this->displayError($this->l('Bot score threshold must be between 1 and 7.'));
        }

        Configuration::updateValue(self::CONF_ENABLED,   (int) Tools::getValue(self::CONF_ENABLED));
        Configuration::updateValue(self::CONF_LIMIT,     $limit);
        Configuration::updateValue(self::CONF_WINDOW,    $window);
        Configuration::updateValue(self::CONF_BOT_SCORE, $botScore);

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    protected function handleWhitelistSave(): string
    {
        $raw     = (string) Tools::getValue('tec_fl_whitelist_value');
        $ips     = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        $invalid = [];
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $invalid[] = htmlspecialchars($ip, ENT_QUOTES);
            }
        }
        if ($invalid) {
            return $this->displayError(
                $this->l('Invalid IP addresses: ') . implode(', ', $invalid)
            );
        }
        Configuration::updateValue(self::CONF_WHITELIST, implode(',', $ips));
        return $this->displayConfirmation($this->l('Whitelist saved.'));
    }

    protected function handleClearLog(): string
    {
        $logFile = _PS_ROOT_DIR_ . '/var/logs/' . self::LOG_FILE;
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        return $this->displayConfirmation($this->l('Log file cleared.'));
    }

    // ── Dati per il template ──────────────────────────────────────────────────

    /**
     * Legge le ultime 100 righe del log e le restituisce come array di hash
     * pronti per il foreach Smarty nel template.
     *
     * @return array<int, array{ts:string, type:string, ip:string, why:string, uri:string, ua:string}>
     */
    private function getLogEntries(): array
    {
        $logFile = _PS_ROOT_DIR_ . '/var/logs/' . self::LOG_FILE;
        if (!file_exists($logFile)) {
            return [];
        }

        $lines   = array_reverse($this->tailFile($logFile, 100));
        $entries = [];
        foreach ($lines as $line) {
            $cols = explode("\t", rtrim($line), 6);
            if (count($cols) < 5) {
                continue;
            }
            $entries[] = [
                'ts'   => $cols[0],
                'type' => $cols[1],
                'ip'   => $cols[2],
                'why'  => $cols[3],
                'uri'  => $cols[4],
                'ua'   => $cols[5] ?? '',
            ];
        }
        return $entries;
    }

    private function getLogSize(): string
    {
        $logFile = _PS_ROOT_DIR_ . '/var/logs/' . self::LOG_FILE;
        if (!file_exists($logFile)) {
            return '';
        }
        $size = filesize($logFile);
        return $size > 0 ? number_format($size / 1024, 1) . ' KB' : '0 KB';
    }

    /**
     * Legge le ultime $n righe di un file senza caricarlo interamente in memoria.
     *
     * @return string[]
     */
    private function tailFile(string $path, int $n): array
    {
        $fp = fopen($path, 'rb');
        if (!$fp) {
            return [];
        }
        $lines  = [];
        $buffer = '';
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);

        while ($pos > 0 && count($lines) < $n) {
            $chunk  = min(4096, $pos);
            $pos   -= $chunk;
            fseek($fp, $pos);
            $buffer = fread($fp, $chunk) . $buffer;
            $parts  = explode("\n", $buffer);
            $buffer = array_shift($parts);
            $lines  = array_merge(array_reverse($parts), $lines);
        }
        if ($buffer !== '') {
            array_unshift($lines, $buffer);
        }
        fclose($fp);

        return array_slice(array_filter($lines), 0, $n);
    }

    public function uninstall(): bool
    {
        foreach ([self::CONF_ENABLED, self::CONF_LIMIT, self::CONF_WINDOW, self::CONF_BOT_SCORE, self::CONF_WHITELIST] as $key) {
            Configuration::deleteByName($key);
        }
        return parent::uninstall();
    }

    // -------------------------------------------------------------------------
    //  Hook principale
    // -------------------------------------------------------------------------

    public function hookActionDispatcherBefore(array $params): void
    {
        if (!(int) Configuration::get(self::CONF_ENABLED)) {
            return;
        }

        // Solo frontend con parametro ?q=
        if (!isset($_GET['q']) || ($params['controller_type'] ?? -1) !== Dispatcher::FC_FRONT) {
            return;
        }

        $ip = $this->getClientIp();

        if ($this->isWhitelisted($ip)) {
            return;
        }

        // ── 1. Bot fingerprinting (efficace contro botnet distribuite) ────────
        $scoreThreshold = (int) (Configuration::get(self::CONF_BOT_SCORE) ?: self::DEFAULT_BOT_SCORE);
        [$botScore, $botReasons] = $this->computeBotScore();

        if ($botScore >= $scoreThreshold) {
            $this->logBlocked($ip, 'BOT', $botReasons);
            $this->sendBlocked();
        }

        // ── 2. Rate limiting per IP (efficace contro singola sorgente) ────────
        $limit  = (int) (Configuration::get(self::CONF_LIMIT)  ?: self::DEFAULT_LIMIT);
        $window = (int) (Configuration::get(self::CONF_WINDOW) ?: self::DEFAULT_WINDOW);

        if ($this->increment($ip, $window) > $limit) {
            $this->logBlocked($ip, 'RATELIMIT', ['ip_exceeded']);
            $this->sendBlocked($window);
        }

        // ── 3. Normalizza ?q= per massimizzare i cache hit di ps_facetedsearch ─
        $this->normalizeQParam();
    }

    // -------------------------------------------------------------------------
    //  Bot fingerprinting
    // -------------------------------------------------------------------------

    /**
     * Calcola un punteggio "probabilità bot" analizzando le caratteristiche
     * della richiesta HTTP. Restituisce [score, motivi[]] per il log.
     *
     * Logica soglie (default threshold = 3):
     *   HTTP/1.0                      → score 4 → blocco immediato da solo
     *   User-Agent assente            → score 3 → blocco immediato da solo
     *   No cookie PS + no Referer     → score 3 → blocco (combinazione)
     *   Solo no cookie PS             → score 2 → passa (primo accesso diretto legittimo)
     *   Solo no Referer               → score 1 → passa (link da email/bookmark)
     *
     * @return array{0: int, 1: string[]}
     */
    private function computeBotScore(): array
    {
        $score   = 0;
        $reasons = [];

        // HTTP/1.0: ignorato dietro reverse proxy (Nginx, Cloudflare, ecc.) perché
        // SERVER_PROTOCOL riflette la connessione interna, non quella del client reale.
        $behindProxy = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            || isset($_SERVER['HTTP_X_REAL_IP'])
            || isset($_SERVER['HTTP_CF_CONNECTING_IP']);

        if (!$behindProxy && ($_SERVER['SERVER_PROTOCOL'] ?? '') === 'HTTP/1.0') {
            $score += self::SCORE_HTTP10;
            $reasons[] = 'http10';
        }

        // User-Agent assente
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($ua === '') {
            $score += self::SCORE_NO_UA;
            $reasons[] = 'no_ua';
        }

        // Cookie PrestaShop-* assente: utenti reali lo acquisiscono alla prima PV
        if (!$this->hasPsSessionCookie()) {
            $score += self::SCORE_NO_PS_COOKIE;
            $reasons[] = 'no_ps_cookie';
        }

        // Referer assente: il browser lo include sempre sulle navigazioni e AJAX same-origin
        if (empty($_SERVER['HTTP_REFERER'])) {
            $score += self::SCORE_NO_REFERER;
            $reasons[] = 'no_referer';
        }

        return [$score, $reasons];
    }

    /**
     * Controlla la presenza di almeno un cookie con prefisso "PrestaShop-".
     * Il nome esatto varia per shop (hash del baseuri), il prefisso è stabile.
     */
    private function hasPsSessionCookie(): bool
    {
        foreach (array_keys($_COOKIE) as $name) {
            if (strncmp($name, 'PrestaShop-', 11) === 0) {
                return true;
            }
        }
        return false;
    }

    // -------------------------------------------------------------------------
    //  Rate limiting per IP
    // -------------------------------------------------------------------------

    /**
     * Incrementa il contatore IP nella finestra corrente.
     * APCu (atomico) se disponibile, altrimenti PS Cache.
     */
    private function increment(string $ip, int $window): int
    {
        $key = 'tec_fl_' . md5($ip) . '_' . (int) floor(time() / $window);

        if (function_exists('apcu_inc')) {
            // Chiamata via variabile per compatibilità con il validatore PS (APCu è opzionale)
            $fn_inc   = 'apcu_inc';
            $fn_store = 'apcu_store';
            $success  = false;
            $count    = $fn_inc($key, 1, $success, $window * 2);
            if (!$success) {
                $fn_store($key, 1, $window * 2);
                return 1;
            }
            return (int) $count;
        }

        $file = _PS_ROOT_DIR_ . '/var/cache/tec_fl_' . md5($ip) . '_' . (int) floor(time() / $window) . '.cnt';
        $fp   = @fopen($file, 'c+');
        if (!$fp) {
            return 1;
        }
        flock($fp, LOCK_EX);
        $count = (int) fgets($fp) + 1;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $count);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $count;
    }

    // -------------------------------------------------------------------------
    //  Normalizzazione ?q=
    // -------------------------------------------------------------------------

    /**
     * Ordina i token di ?q= prima che ps_facetedsearch calcoli il cache hash.
     * ?q=Color-Red/Brand-Nike e ?q=Brand-Nike/Color-Red → stesso MD5 → stessa cache entry.
     */
    private function normalizeQParam(): void
    {
        $parts = array_filter(explode('/', (string) $_GET['q']));
        sort($parts);
        $_GET['q'] = $_REQUEST['q'] = implode('/', $parts);
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    private function getClientIp(): string
    {
        $ip = Tools::getRemoteAddr();
        return $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    private function isWhitelisted(string $ip): bool
    {
        $raw = (string) Configuration::get(self::CONF_WHITELIST);
        if ($raw === '') {
            return false;
        }
        foreach (array_map('trim', explode(',', $raw)) as $allowed) {
            if ($allowed !== '' && $allowed === $ip) {
                return true;
            }
        }
        return false;
    }

    private function sendBlocked(int $retryAfter = 0): void
    {
        http_response_code(429);
        if ($retryAfter > 0) {
            header('Retry-After: ' . $retryAfter);
        }

        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'rate_limited']);
            exit;
        }

        // Navigazione diretta: pagina HTML con link di ritorno alla categoria
        $uri   = $_SERVER['REQUEST_URI'] ?? '/';
        $parts = parse_url($uri);
        $back  = $parts['path'] ?? '/';
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $params);
            unset($params['q']);
            if ($params) {
                $back .= '?' . http_build_query($params);
            }
        }

        $this->context->smarty->assign([
            'blocked_back_url' => $back,
        ]);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->context->smarty->fetch($this->local_path . 'views/templates/front/blocked.tpl');
        exit;
    }

    /**
     * @param string[] $reasons
     */
    private function logBlocked(string $ip, string $type, array $reasons): void
    {
        $logDir  = _PS_ROOT_DIR_ . '/var/logs/';
        $logFile = $logDir . self::LOG_FILE;

        if (!is_dir($logDir)) {
            return;
        }

        $uri  = $_SERVER['REQUEST_URI'] ?? '-';
        $ua   = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? '-'), 0, 200);
        $why  = implode(',', $reasons);
        $line = date('Y-m-d H:i:s') . "\t" . $type . "\t" . $ip . "\t" . $why . "\t" . $uri . "\t" . $ua . PHP_EOL;

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
