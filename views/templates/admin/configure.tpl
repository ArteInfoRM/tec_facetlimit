{*
** 2009-2026 Tecnoacquisti.com
** @license One Paid Licence By WebSite Using This Module.
*}

<style>
    /* Override the fixed select width supplied by HelperForm. */
    .bootstrap select#TEC_FL_BOT_SCORE.fixed-width-xl {
        width: 100% !important;
        max-width: 46rem;
    }
    #tec-fl-picker {
        width: min(48rem, 95vw);
        max-height: 90vh;
        overflow: auto;
        margin: auto;
        padding: 0;
        border: 1px solid #bbcdd2;
        border-radius: 4px;
    }
    #tec-fl-picker::backdrop { background: rgba(0, 0, 0, .45); }
    #tec-fl-picker .tec-fl-choices { max-height: 34vh; overflow: auto; padding: 8px; border: 1px solid #bbcdd2; }
    #tec-fl-picker .tec-fl-tools { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 6px; }
    #tec-fl-picker .tec-fl-tree { list-style: none; margin: 0; padding-left: 18px; }
    #tec-fl-picker .tec-fl-choices > .tec-fl-tree { padding-left: 0; }
    #tec-fl-picker .tec-fl-choice { display: inline-flex; align-items: center; gap: 6px; margin: 0; padding: 4px; font-weight: normal; cursor: pointer; }
    #tec-fl-picker .tec-fl-choice input { margin: 0; }
    #tec-fl-picker .tec-fl-toggle { width: 24px; padding: 3px; }
    #tec-fl-picker .tec-fl-leaf { margin-left: 24px; }
    #tec-fl-picker [hidden] { display: none; }
</style>

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-sliders" aria-hidden="true"></i>
        {l s='Shared filter limits' mod='tec_facetlimit'}
    </div>
    {if $single_shop}
        <form class="form-horizontal" method="post" action="{$config_url|escape:'html':'UTF-8'}">
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-3" for="admission-mode">{l s='Mode' mod='tec_facetlimit'}</label>
                    <div class="col-lg-4">
                        <select class="form-control" id="admission-mode" name="admission_mode">
                            <option value="off"{if $admission_settings.mode == 'off'} selected{/if}>{l s='Off' mod='tec_facetlimit'}</option>
                            <option value="observe"{if $admission_settings.mode == 'observe'} selected{/if}>{l s='Observe' mod='tec_facetlimit'}</option>
                            <option value="protect"{if $admission_settings.mode == 'protect'} selected{/if}>{l s='Protect' mod='tec_facetlimit'}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3" for="admission-shop">{l s='Shop limit' mod='tec_facetlimit'}</label>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <input class="form-control" type="number" id="admission-shop" name="admission_shop"
                                   required min="1" max="9999" step="1" value="{$admission_settings.shop|intval}" aria-describedby="admission-unit">
                            <span class="input-group-addon" id="admission-unit">{l s='requests/min' mod='tec_facetlimit'}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3" for="tec-fl-add">{l s='Category limits' mod='tec_facetlimit'}</label>
                    <div class="col-lg-8" id="tec-fl-category-editor">
                        <input type="hidden" name="admission_categories" value="{$admission_rules|escape:'html':'UTF-8'}">
                        <table class="table" id="tec-fl-category-table" hidden>
                            <thead><tr><th>{l s='Category' mod='tec_facetlimit'}</th><th>{l s='requests/min' mod='tec_facetlimit'}</th><th></th></tr></thead>
                            <tbody></tbody>
                        </table>
                        <template id="tec-fl-rule-row">
                            <tr><td></td><td></td><td class="text-right">
                                <button type="button" class="btn btn-default btn-xs" data-action="edit">{l s='Edit' mod='tec_facetlimit'}</button>
                                <button type="button" class="btn btn-default btn-xs" data-action="remove">{l s='Remove' mod='tec_facetlimit'}</button>
                            </td></tr>
                        </template>
                        <button type="button" class="btn btn-default" id="tec-fl-add" aria-haspopup="dialog">
                            <i class="icon icon-plus" aria-hidden="true"></i> {l s='Add category limit' mod='tec_facetlimit'}
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-default pull-right" type="submit" name="submitFacetAdmission">
                    <i class="process-icon-save" aria-hidden="true"></i> {l s='Save' mod='tec_facetlimit'}
                </button>
            </div>
        </form>
        <dialog id="tec-fl-picker" aria-labelledby="tec-fl-picker-title">
            <form>
                <div class="modal-header">
                    <h4 class="modal-title" id="tec-fl-picker-title">{l s='Set category limits' mod='tec_facetlimit'}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tec-fl-category-search">{l s='Search categories' mod='tec_facetlimit'}</label>
                        <input id="tec-fl-category-search" class="form-control" type="search" autocomplete="off">
                    </div>
                    <div class="tec-fl-tools">
                        <button class="btn btn-default btn-sm" type="button" data-tree-action="expand">{l s='Expand all' mod='tec_facetlimit'}</button>
                        <button class="btn btn-default btn-sm" type="button" data-tree-action="collapse">{l s='Collapse all' mod='tec_facetlimit'}</button>
                        <button class="btn btn-default btn-sm" type="button" data-tree-action="select">{l s='Select all matches' mod='tec_facetlimit'}</button>
                        <button class="btn btn-default btn-sm" type="button" data-tree-action="clear">{l s='Clear selection' mod='tec_facetlimit'}</button>
                    </div>
                    <div class="tec-fl-choices form-group" role="group" aria-label="{l s='Categories' mod='tec_facetlimit'}">
                        {include file='./category-tree.tpl' nodes=$category_tree}
                    </div>
                    <p id="tec-fl-no-matches" hidden>{l s='No matching categories.' mod='tec_facetlimit'}</p>
                    <div class="form-group">
                        <label for="tec-fl-category-rate">{l s='Limit per minute' mod='tec_facetlimit'}</label>
                        <input id="tec-fl-category-rate" class="form-control fixed-width-md" type="number" required min="1" max="9999" step="1">
                    </div>
                    <p class="alert alert-warning" id="tec-fl-limit-error" role="alert" hidden>{l s='Select up to 64 categories in total.' mod='tec_facetlimit'}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="tec-fl-cancel">{l s='Cancel' mod='tec_facetlimit'}</button>
                    <button type="submit" class="btn btn-primary" id="tec-fl-confirm" disabled>{l s='Confirm' mod='tec_facetlimit'}</button>
                </div>
            </form>
        </dialog>
        <script src="{$picker_script|escape:'html':'UTF-8'}"></script>
    {else}
        <p class="alert alert-info">{l s='Select a single shop to configure shared limits.' mod='tec_facetlimit'}</p>
    {/if}
</div>

{if $single_shop && $admission_stats}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-bar-chart" aria-hidden="true"></i>
            {l s='Filter traffic' mod='tec_facetlimit'}
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">{l s='Scope' mod='tec_facetlimit'}</th>
                        <th scope="col">{l s='Last mode' mod='tec_facetlimit'}</th>
                        <th scope="col" class="text-right">{l s='This minute' mod='tec_facetlimit'}</th>
                        <th scope="col" class="text-right">{l s='Peak per minute' mod='tec_facetlimit'}</th>
                        <th scope="col" class="text-right">{l s='Over quota (total)' mod='tec_facetlimit'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $admission_stats as $id => $stat}
                        <tr>
                            <td>{if $id == 0}{l s='Shop' mod='tec_facetlimit'}{elseif isset($category_names[$id])}{$category_names[$id]|escape:'html':'UTF-8'}{else}{l s='Category' mod='tec_facetlimit'} #{$id|intval}{/if}</td>
                            <td>{if $stat.mode == 'protect'}{l s='Protect' mod='tec_facetlimit'}{else}{l s='Observe' mod='tec_facetlimit'}{/if}</td>
                            <td class="text-right">{if $stat.minute == $current_minute}{$stat.requests|intval}{else}0{/if}</td>
                            <td class="text-right">{$stat.peak|intval}</td>
                            <td class="text-right">{$stat.excess|intval}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-hdd" aria-hidden="true"></i>
        {l s='Rate limiter backend' mod='tec_facetlimit'}
    </div>
    <p>
        {l s='Per-IP counters' mod='tec_facetlimit'}
        {if $apcu_available}
            <span class="label label-success">APCu</span>
        {else}
            <span class="label label-default">{l s='Files' mod='tec_facetlimit'}</span>
        {/if}
    </p>
    {if !$apcu_available}
        <p class="help-block">{l s='Ask your hosting provider to enable APCu so per-IP counters can use shared memory.' mod='tec_facetlimit'}</p>
    {/if}
    <p class="help-block">{l s='Shop and category quotas use files.' mod='tec_facetlimit'}</p>
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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{l s='No User-Agent' mod='tec_facetlimit'}</td>
                    <td><strong>3</strong></td>
                </tr>
                <tr>
                    <td>{l s='No PrestaShop-* cookie' mod='tec_facetlimit'}</td>
                    <td><strong>2</strong></td>
                </tr>
                <tr>
                    <td>{l s='No Referer' mod='tec_facetlimit'}</td>
                    <td><strong>1</strong></td>
                </tr>
            </tbody>
        </table>
        <p class="help-block">
            {l s='A first visit from a direct link can score 3 points when both the cookie and the referrer are missing.' mod='tec_facetlimit'}
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
