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

require_once __DIR__ . '/FacetLimitCounters.php';

/**
 * @version 1.2.0
 */
class Tec_FacetLimit extends Module
{
    public const CONF_LIMIT = 'TEC_FL_LIMIT';
    public const CONF_WINDOW = 'TEC_FL_WINDOW';
    public const CONF_WHITELIST = 'TEC_FL_WHITELIST';
    public const CONF_ENABLED = 'TEC_FL_ENABLED';
    public const CONF_BOT_SCORE = 'TEC_FL_BOT_SCORE';
    public const CONF_AGGREGATE = 'TEC_FL_AGGREGATE';
    public const LOG_FILE = 'tec_facetlimit.log';

    public const DEFAULT_LIMIT = 30;   // max richieste ?q= per finestra per IP
    public const DEFAULT_WINDOW = 60;   // finestra in secondi
    public const DEFAULT_BOT_SCORE = 4;    // soglia punteggio bot oltre la quale si blocca

    public const SCORE_NO_PS_COOKIE = 2;
    public const SCORE_NO_REFERER = 1;
    public const SCORE_NO_UA = 3;

    public function __construct()
    {
        $this->name = 'tec_facetlimit';
        $this->tab = 'administration';
        $this->version = '1.2.0';
        $this->author = 'Tecnoacquisti.com';
        $this->need_instance = 0;
        $this->bootstrap = true;

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
            && Configuration::updateValue(self::CONF_ENABLED, 1)
            && Configuration::updateValue(self::CONF_LIMIT, self::DEFAULT_LIMIT)
            && Configuration::updateValue(self::CONF_WINDOW, self::DEFAULT_WINDOW)
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
        } elseif (Tools::isSubmit('submitFacetAdmission')) {
            $output .= $this->saveAdmission();
        } elseif (Tools::isSubmit('submitTecFacetLimitWhitelist')) {
            $output .= $this->handleWhitelistSave();
        } elseif (Tools::isSubmit('tec_fl_clear_log')) {
            $output .= $this->handleClearLog();
        }

        $configUrl = $this->context->link->getAdminLink('AdminModules', true)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $settings = $this->admissionSettings();
        $rules = [];
        foreach ($settings['categories'] as $id => $rate) {
            $rules[] = $id . '=' . $rate;
        }
        $singleShop = Shop::getContext() === Shop::CONTEXT_SHOP || !Shop::isFeatureActive();
        $tree = [];
        $categoryNames = [];
        if ($singleShop) {
            $tree = Category::getNestedCategories((int) $this->context->shop->id_category, (int) $this->context->language->id, false);
            $this->collectCategoryNames($tree, '', $categoryNames);
        }
        $this->context->smarty->assign([
            'category_tree' => $tree,
            'category_names' => $categoryNames,
            'picker_script' => $this->_path . 'views/js/category-limits.js',
            'admission_settings' => $settings,
            'admission_rules' => implode(',', $rules),
            'admission_stats' => $this->counters()->snapshot(),
            'current_minute' => (int) floor(time() / 60),
            'single_shop' => $singleShop,
            'current_ip' => $this->getClientIp(),
            'whitelist' => (string) Configuration::get(self::CONF_WHITELIST),
            'config_url' => $configUrl,
            'log_entries' => $this->getLogEntries(),
            'log_size' => $this->getLogSize(),
            'apcu_available' => $this->hasApcu(),
        ]);

        $output .= $this->renderForm();
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;
    }

    protected function renderForm(): string
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTecFacetLimitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => ['title' => $this->l('Settings'), 'icon' => 'icon-cogs'],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable protection'),
                        'name' => self::CONF_ENABLED,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'enabled_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Max requests per window (rate limit)'),
                        'name' => self::CONF_LIMIT,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->l('Max number of ?q= requests allowed per IP in the time window. Default: 30.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Time window (seconds)'),
                        'name' => self::CONF_WINDOW,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->l('Duration of the rate-limit window in seconds. Default: 60.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Bot score threshold'),
                        'name' => self::CONF_BOT_SCORE,
                        'desc' => $this->l('A request is blocked when its score reaches the threshold. Higher values allow more requests. Default: 4.'),
                        'options' => [
                            'query' => [
                                ['id' => 1, 'name' => '1 - ' . $this->l('Any one signal is missing')],
                                ['id' => 2, 'name' => '2 - ' . $this->l('Missing shop cookie or User-Agent')],
                                ['id' => 3, 'name' => '3 - ' . $this->l('Missing User-Agent, or both cookie and Referer')],
                                ['id' => 4, 'name' => '4 - ' . $this->l('Default: missing User-Agent plus cookie or Referer')],
                                ['id' => 5, 'name' => '5 - ' . $this->l('Both User-Agent and shop cookie are missing')],
                                ['id' => 6, 'name' => '6 - ' . $this->l('All three signals are missing')],
                            ],
                            'id' => 'id',
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
            self::CONF_ENABLED => (int) Configuration::get(self::CONF_ENABLED),
            self::CONF_LIMIT => (int) (Configuration::get(self::CONF_LIMIT) ?: self::DEFAULT_LIMIT),
            self::CONF_WINDOW => (int) (Configuration::get(self::CONF_WINDOW) ?: self::DEFAULT_WINDOW),
            self::CONF_BOT_SCORE => $this->getBotScoreThreshold(),
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

        $botScore = Tools::getValue(self::CONF_BOT_SCORE);
        if (!is_string($botScore) || !preg_match('/^[1-6]$/D', $botScore)) {
            return $this->displayError($this->l('Bot score threshold must be between 1 and 6.'));
        }

        Configuration::updateValue(self::CONF_ENABLED, (int) Tools::getValue(self::CONF_ENABLED));
        Configuration::updateValue(self::CONF_LIMIT, $limit);
        Configuration::updateValue(self::CONF_WINDOW, $window);
        Configuration::updateValue(self::CONF_BOT_SCORE, (int) $botScore);

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    private function counters()
    {
        return new FacetLimitCounters(_PS_ROOT_DIR_ . '/var/cache', $this->context->shop->id);
    }

    private function collectCategoryNames(array $nodes, $parentPath, array &$names)
    {
        foreach ($nodes as $node) {
            $path = $parentPath === '' ? $node['name'] : $parentPath . ' / ' . $node['name'];
            $names[(int) $node['id_category']] = $path;
            if (!empty($node['children'])) {
                $this->collectCategoryNames($node['children'], $path, $names);
            }
        }
    }

    private function admissionSettings()
    {
        $stored = json_decode((string) Configuration::get(self::CONF_AGGREGATE), true);

        return $stored ?: ['mode' => 'off', 'shop' => 150, 'categories' => []];
    }

    private function saveAdmission()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            return $this->displayError($this->l('Select a single shop to configure shared limits.'));
        }
        $mode = Tools::getValue('admission_mode');
        $limit = Tools::getValue('admission_shop');
        $rules = Tools::getValue('admission_categories');
        if (!in_array($mode, ['off', 'observe', 'protect'], true)
            || !is_string($limit) || !preg_match('/^[1-9][0-9]{0,3}$/D', $limit)
            || !is_string($rules) || strlen($rules) > 1024) {
            return $this->displayError($this->l('Invalid shared limit settings.'));
        }
        $categories = [];
        foreach ($rules === '' ? [] : explode(',', $rules) as $pair) {
            if (!preg_match('/^([1-9][0-9]{0,8})=([1-9][0-9]{0,3})$/D', trim($pair), $match)
                || isset($categories[$match[1]]) || count($categories) >= 64) {
                return $this->displayError($this->l('Enter up to 64 distinct category ID=limit pairs.'));
            }
            $category = new Category((int) $match[1], null, (int) $this->context->shop->id);
            if (!Validate::isLoadedObject($category) || !$category->isAssociatedToShop()) {
                return $this->displayError($this->l('A category does not belong to this shop.'));
            }
            $categories[(int) $match[1]] = (int) $match[2];
        }
        $settings = ['mode' => $mode, 'shop' => (int) $limit, 'categories' => $categories];
        if (!Configuration::updateValue(
            self::CONF_AGGREGATE,
            json_encode($settings),
            false,
            (int) $this->context->shop->id_shop_group,
            (int) $this->context->shop->id
        )) {
            return $this->displayError($this->l('Could not save shared limits.'));
        }

        return $this->displayConfirmation($this->l('Shared limits saved.'));
    }

    protected function handleWhitelistSave(): string
    {
        $raw = (string) Tools::getValue('tec_fl_whitelist_value');
        $ips = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
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
        foreach (glob(_PS_ROOT_DIR_ . '/var/cache/tec_fl_*.cnt') ?: [] as $legacyCounter) {
            @unlink($legacyCounter);
        }
        $this->counters()->clearIpCounts();

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

        $lines = array_reverse($this->tailFile($logFile, 100));
        $entries = [];
        foreach ($lines as $line) {
            $cols = explode("\t", rtrim($line), 6);
            if (count($cols) < 5) {
                continue;
            }
            $entries[] = [
                'ts' => $cols[0],
                'type' => $cols[1],
                'ip' => $cols[2],
                'why' => $cols[3],
                'uri' => $cols[4],
                'ua' => $cols[5] ?? '',
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
        $lines = [];
        $buffer = '';
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);

        while ($pos > 0 && count($lines) < $n) {
            $chunk = min(4096, $pos);
            $pos -= $chunk;
            fseek($fp, $pos);
            $buffer = fread($fp, $chunk) . $buffer;
            $parts = explode("\n", $buffer);
            $buffer = array_shift($parts);
            $lines = array_merge(array_reverse($parts), $lines);
        }
        if ($buffer !== '') {
            array_unshift($lines, $buffer);
        }
        fclose($fp);

        return array_slice(array_filter($lines), 0, $n);
    }

    public function uninstall(): bool
    {
        foreach ([self::CONF_ENABLED, self::CONF_LIMIT, self::CONF_WINDOW, self::CONF_BOT_SCORE, self::CONF_WHITELIST, self::CONF_AGGREGATE] as $key) {
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

        if (!$this->isFacetedSearchRequest($params)) {
            return;
        }

        $ip = $this->getClientIp();

        if ($this->isWhitelisted($ip)) {
            return;
        }

        $settings = $this->admissionSettings();
        if ($settings['mode'] !== 'off') {
            $categoryId = 0;
            if ($this->getRequestControllerName($params) === 'category') {
                $categoryId = filter_var(Tools::getValue('id_category'), FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);
            }
            $retry = $this->counters()->admission($settings['shop'], $settings['categories'], $categoryId, $settings['mode']);
            if ($retry > 0 && $settings['mode'] === 'protect') {
                $this->sendBlocked($retry);
            }
        }

        if (!isset($_GET['q']) || !$this->isFacetedQValue(trim($_GET['q']))) {
            return;
        }

        // ── 1. Bot fingerprinting (efficace contro botnet distribuite) ────────
        $scoreThreshold = $this->getBotScoreThreshold();
        [$botScore, $botReasons] = $this->computeBotScore();

        if ($botScore >= $scoreThreshold) {
            $this->logBlocked($ip, 'BOT', $botReasons);
            $this->sendBlocked();
        }

        // ── 2. Rate limiting per IP (efficace contro singola sorgente) ────────
        $limit = (int) (Configuration::get(self::CONF_LIMIT) ?: self::DEFAULT_LIMIT);
        $window = (int) (Configuration::get(self::CONF_WINDOW) ?: self::DEFAULT_WINDOW);

        if ($this->increment($ip, $window) > $limit) {
            $this->logBlocked($ip, 'RATELIMIT', ['ip_exceeded']);
            $this->sendBlocked($window);
        }

        // ── 3. Normalizza ?q= per massimizzare i cache hit di ps_facetedsearch ─
        $this->normalizeQParam();
    }

    /**
     * Check whether the current request matches a ps_facetedsearch filter URL.
     */
    private function isFacetedSearchRequest(array $params): bool
    {
        if (($params['controller_type'] ?? -1) !== Dispatcher::FC_FRONT) {
            return false;
        }

        if (!array_key_exists('q', $_GET) && !array_key_exists('q', $_POST)) {
            return false;
        }

        if (Tools::getValue('fc') === 'module') {
            return false;
        }

        $controllerName = $this->getRequestControllerName($params);
        $allowedControllers = [
            'category',
            'manufacturer',
            'supplier',
            'pricesdrop',
            'prices-drop',
            'newproducts',
            'new-products',
            'bestsales',
            'best-sales',
        ];

        if (!in_array($controllerName, $allowedControllers, true)) {
            return false;
        }

        foreach ([$_GET, $_POST] as $input) {
            if (array_key_exists('q', $input)
                && (!is_string($input['q']) || strlen($input['q']) > 8192
                    || !preg_match('//u', $input['q'])
                    || preg_match('/[\x00-\x1F\x7F]/', $input['q']))) {
                http_response_code(400);
                header('Content-Type: text/plain; charset=utf-8');
                header('Cache-Control: no-store');
                exit('Invalid filter query.');
            }
        }

        return trim((string) Tools::getValue('q')) !== '';
    }

    /**
     * Return the front controller name from dispatcher data or context fallback.
     */
    private function getRequestControllerName(array $params): string
    {
        $controller = $params['controller'] ?? null;

        if (is_object($controller)) {
            if (isset($controller->php_self) && $controller->php_self !== '') {
                return strtolower((string) $controller->php_self);
            }

            if (isset($controller->controller_name) && $controller->controller_name !== '') {
                return strtolower((string) $controller->controller_name);
            }
        }

        if (is_string($controller) && $controller !== '') {
            return strtolower($controller);
        }

        $controllerName = Tools::getValue('controller');
        if (is_string($controllerName) && $controllerName !== '') {
            return strtolower($controllerName);
        }

        if (isset($this->context->controller->php_self) && $this->context->controller->php_self !== '') {
            return strtolower((string) $this->context->controller->php_self);
        }

        if (isset($this->context->controller->controller_name) && $this->context->controller->controller_name !== '') {
            return strtolower((string) $this->context->controller->controller_name);
        }

        return '';
    }

    /**
     * Validate the q value shape used by faceted filter tokens.
     */
    private function isFacetedQValue(string $q): bool
    {
        if ($q === '') {
            return false;
        }

        $tokens = array_filter(explode('/', $q));
        if (count($tokens) > 1) {
            foreach ($tokens as $token) {
                if (!$this->isFacetedQToken($token)) {
                    return false;
                }
            }

            return true;
        }

        return $this->isFacetedQToken($q);
    }

    /**
     * Validate a single faceted filter token.
     */
    private function isFacetedQToken(string $token): bool
    {
        return (bool) preg_match('/^[^\/=]+-[^\/=]+$/', trim($token));
    }

    // -------------------------------------------------------------------------
    //  Bot fingerprinting
    // -------------------------------------------------------------------------

    private function getBotScoreThreshold(): int
    {
        $threshold = (int) Configuration::get(self::CONF_BOT_SCORE);
        if ($threshold === 7) {
            return 6;
        }

        return $threshold >= 1 && $threshold <= 6 ? $threshold : self::DEFAULT_BOT_SCORE;
    }

    /**
     * Return the combined score and the missing request signals.
     */
    private function computeBotScore(): array
    {
        $score = 0;
        $reasons = [];

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
            if (strncmp($name, 'PrestaShop-', strlen('PrestaShop-')) === 0) {
                return true;
            }
        }

        return false;
    }

    // -------------------------------------------------------------------------
    //  Rate limiting per IP
    // -------------------------------------------------------------------------

    /**
     * Increment the current shop and IP window in APCu or a locked file shard.
     */
    private function increment(string $ip, int $window): int
    {
        if ($this->hasApcu()) {
            $key = 'tec_fl_' . md5(_PS_ROOT_DIR_ . ':' . $this->context->shop->id . ':' . $ip)
                . '_' . $window . '_' . (int) floor(time() / $window);
            $add = 'apcu_add';
            $increment = 'apcu_inc';
            $add($key, 0, $window * 2);
            $success = false;
            $count = $increment($key, 1, $success);
            if ($success) {
                return (int) $count;
            }
        }

        return $this->counters()->incrementIp($ip, $window);
    }

    private function hasApcu()
    {
        $enabled = 'apcu_enabled';

        return function_exists($enabled) && function_exists('apcu_add') && function_exists('apcu_inc') && $enabled();
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
        header('Cache-Control: no-store');
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
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parts = parse_url($uri);
        $back = $parts['path'] ?? '/';
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
        $logDir = _PS_ROOT_DIR_ . '/var/logs/';
        $logFile = $logDir . self::LOG_FILE;

        if (!is_dir($logDir)) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '-';
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? '-'), 0, 200);
        $why = implode(',', $reasons);
        $line = date('Y-m-d H:i:s') . "\t" . $type . "\t" . $ip . "\t" . $why . "\t" . $uri . "\t" . $ua . PHP_EOL;

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
