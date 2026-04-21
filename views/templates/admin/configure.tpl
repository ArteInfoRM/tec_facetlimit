{*
** 2009-2026 Tecnoacquisti.com
** @license One Paid Licence By WebSite Using This Module.
*}

{* ── Rate limiter backend status ────────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-hdd"></i>
        {l s='Rate limiter backend' mod='tec_facetlimit'}
    </div>
    <div class="panel-body">
        {if $apcu_available}
            <span class="label label-success" style="font-size:13px;padding:4px 10px;">
                <i class="icon icon-check"></i> APCu
            </span>
            &nbsp;{l s='APCu — atomic shared-memory counter active.' mod='tec_facetlimit'}
        {else}
            <span class="label label-warning" style="font-size:13px;padding:4px 10px;">
                <i class="icon icon-warning-sign"></i> PS Cache
            </span>
            &nbsp;{l s='APCu not available — using PS Cache fallback. Install the php-apcu extension for more reliable rate limiting.' mod='tec_facetlimit'}
        {/if}
    </div>
</div>

{* ── Bot scoring reference ───────────────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-info-sign"></i>
        {l s='Bot scoring reference' mod='tec_facetlimit'}
    </div>
    <div class="panel-body">
        <table class="table table-bordered table-striped" style="width:auto">
            <thead>
                <tr>
                    <th>{l s='Signal' mod='tec_facetlimit'}</th>
                    <th>{l s='Score' mod='tec_facetlimit'}</th>
                    <th>{l s='Notes' mod='tec_facetlimit'}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>HTTP/1.0</code></td>
                    <td><strong>4</strong></td>
                    <td>{l s='Blocks alone — no real browser has used HTTP/1.0 since 1996. Auto-disabled when a reverse-proxy header is detected (X-Forwarded-For, CF-Connecting-IP).' mod='tec_facetlimit'}</td>
                </tr>
                <tr>
                    <td>{l s='No User-Agent' mod='tec_facetlimit'}</td>
                    <td><strong>3</strong></td>
                    <td>{l s='Blocks alone — non-browser client.' mod='tec_facetlimit'}</td>
                </tr>
                <tr>
                    <td>{l s='No PrestaShop-* cookie' mod='tec_facetlimit'}</td>
                    <td><strong>2</strong></td>
                    <td>{l s='Real users always acquire the session cookie on first page view.' mod='tec_facetlimit'}</td>
                </tr>
                <tr>
                    <td>{l s='No Referer' mod='tec_facetlimit'}</td>
                    <td><strong>1</strong></td>
                    <td>{l s='Browsers always send Referer on navigation and same-origin AJAX.' mod='tec_facetlimit'}</td>
                </tr>
            </tbody>
        </table>
        <p class="help-block">
            {l s='Default threshold = 3: HTTP/1.0 alone (4) or missing UA alone (3) block immediately. No-cookie + no-referer (2+1=3) also blocks. First-time visitors with a direct link (no-cookie only, score 2) pass through.' mod='tec_facetlimit'}
        </p>
    </div>
</div>

{* ── IP Whitelist ────────────────────────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-lock"></i>
        {l s='IP Whitelist' mod='tec_facetlimit'}
    </div>
    <div class="panel-body">
        <form action="{$config_url|escape:'html':'UTF-8'}" method="post" id="tec-fl-whitelist-form">
            <input type="hidden" name="submitTecFacetLimitWhitelist" value="1">
            <input type="hidden" name="tec_fl_whitelist_value" id="tec-fl-whitelist-value"
                   value="{$whitelist|escape:'html':'UTF-8'}">

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Whitelisted IPs' mod='tec_facetlimit'}
                </label>
                <div class="col-lg-9">
                    <div id="tec-fl-chips" class="form-control"
                         style="height:auto;min-height:42px;cursor:text;padding:4px 8px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                    </div>

                    <div class="input-group" style="max-width:380px;margin-top:8px;">
                        <input type="text" id="tec-fl-ip-new" class="form-control"
                               placeholder="{l s='e.g. 203.0.113.1 or 2001:db8::1' mod='tec_facetlimit'}">
                        <span class="input-group-btn">
                            <button type="button" id="tec-fl-ip-add-btn" class="btn btn-default">
                                <i class="icon icon-plus"></i>
                                {l s='Add' mod='tec_facetlimit'}
                            </button>
                        </span>
                    </div>

                    <div style="margin-top:8px;">
                        <button type="button" id="tec-fl-add-mine" class="btn btn-default btn-sm"
                                data-ip="{$current_ip|escape:'html':'UTF-8'}">
                            <i class="icon icon-user"></i>
                            {l s='Add my IP' mod='tec_facetlimit'}
                            <strong>({$current_ip|escape:'html':'UTF-8'})</strong>
                        </button>
                    </div>

                    <p class="help-block" style="margin-top:8px;">
                        {l s='IPv4 and IPv6 addresses. IPs in this list bypass all rate-limit and bot checks.' mod='tec_facetlimit'}
                    </p>
                </div>
            </div>

            <div class="panel-footer">
                <button type="submit" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    {l s='Save whitelist' mod='tec_facetlimit'}
                </button>
                <div class="clearfix"></div>
            </div>
        </form>
    </div>
</div>

{* ── Blocked requests log ────────────────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-ban-circle"></i>
        {l s='Blocked requests log' mod='tec_facetlimit'}
        {if $log_size}
            <small class="text-muted">&nbsp;({$log_size|escape:'html':'UTF-8'})</small>
        {/if}
        <div class="panel-heading-action">
            <form action="{$config_url|escape:'html':'UTF-8'}" method="post" style="display:inline"
                  onsubmit="return confirm('{l s='Clear the log file?' mod='tec_facetlimit' js=true}')">
                <input type="hidden" name="tec_fl_clear_log" value="1">
                <button type="submit" class="btn btn-default btn-xs">
                    <i class="icon icon-trash"></i>
                    {l s='Clear log' mod='tec_facetlimit'}
                </button>
            </form>
        </div>
    </div>
    <div class="panel-body" style="overflow-x:auto">
        <p class="help-block">
            {l s='Last 100 blocked entries, most recent first.' mod='tec_facetlimit'}
        </p>
        <table class="table table-bordered table-hover table-condensed" style="font-size:12px">
            <thead>
                <tr>
                    <th>{l s='Time' mod='tec_facetlimit'}</th>
                    <th>{l s='Type' mod='tec_facetlimit'}</th>
                    <th>IP</th>
                    <th>{l s='Signals' mod='tec_facetlimit'}</th>
                    <th>URI</th>
                    <th>User-Agent</th>
                </tr>
            </thead>
            <tbody>
                {if $log_entries}
                    {foreach from=$log_entries item=entry}
                    <tr>
                        <td style="white-space:nowrap">{$entry.ts|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $entry.type === 'BOT'}
                                <span class="label label-danger">{$entry.type|escape:'html':'UTF-8'}</span>
                            {else}
                                <span class="label label-warning">{$entry.type|escape:'html':'UTF-8'}</span>
                            {/if}
                        </td>
                        <td><code>{$entry.ip|escape:'html':'UTF-8'}</code></td>
                        <td><code>{$entry.why|escape:'html':'UTF-8'}</code></td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                            title="{$entry.uri|escape:'html':'UTF-8'}">
                            {$entry.uri|escape:'html':'UTF-8'}
                        </td>
                        <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#777"
                            title="{$entry.ua|escape:'html':'UTF-8'}">
                            {$entry.ua|escape:'html':'UTF-8'}
                        </td>
                    </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            {l s='No blocked requests logged yet.' mod='tec_facetlimit'}
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
</div>

{* ── Whitelist chips JS ──────────────────────────────────────────────────── *}
<script>
(function () {
    'use strict';

    var chips   = document.getElementById('tec-fl-chips');
    var hidden  = document.getElementById('tec-fl-whitelist-value');
    var newInput = document.getElementById('tec-fl-ip-new');

    function getIps() {
        return hidden.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }

    function setIps(arr) {
        hidden.value = arr.join(',');
    }

    function render() {
        chips.innerHTML = '';
        getIps().forEach(function (ip) {
            var chip = document.createElement('span');
            chip.style.cssText = 'display:inline-flex;align-items:center;background:#5bc0de;color:#fff;border-radius:3px;padding:2px 8px;font-size:12px;white-space:nowrap;';
            chip.textContent = ip;

            var rm = document.createElement('a');
            rm.href = '#';
            rm.innerHTML = ' &times;';
            rm.style.cssText = 'color:#fff;margin-left:4px;text-decoration:none;font-weight:bold;';
            rm.dataset.ip = ip;
            rm.addEventListener('click', function (e) {
                e.preventDefault();
                var current = this.dataset.ip;
                setIps(getIps().filter(function (x) { return x !== current; }));
                render();
            });

            chip.appendChild(rm);
            chips.appendChild(chip);
        });
    }

    function addIp(ip) {
        ip = ip.trim();
        if (!ip) { return; }
        var list = getIps();
        if (list.indexOf(ip) === -1) {
            list.push(ip);
            setIps(list);
            render();
        }
        newInput.value = '';
        newInput.focus();
    }

    document.getElementById('tec-fl-ip-add-btn').addEventListener('click', function () {
        addIp(newInput.value);
    });

    newInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addIp(this.value); }
    });

    document.getElementById('tec-fl-add-mine').addEventListener('click', function () {
        addIp(this.dataset.ip);
    });

    chips.addEventListener('click', function (e) {
        if (e.target.tagName !== 'A') { newInput.focus(); }
    });

    render();
}());
</script>
