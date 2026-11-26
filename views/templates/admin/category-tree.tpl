{*
** 2009-2026 Tecnoacquisti.com
** @license One Paid Licence By WebSite Using This Module.
*}
<ul class="tec-fl-tree">
    {foreach $nodes as $node}
        <li>
            {if !empty($node.children)}
                <button type="button" class="btn btn-link tec-fl-toggle" aria-expanded="true"
                        aria-controls="tec-fl-children-{$node.id_category|intval}" aria-label="{$category_names[$node.id_category]|escape:'html':'UTF-8'}">
                    <i class="icon icon-folder-open" aria-hidden="true"></i>
                </button>
            {/if}
            <label class="tec-fl-choice{if empty($node.children)} tec-fl-leaf{/if}">
                <input type="checkbox" value="{$node.id_category|intval}" data-path="{$category_names[$node.id_category]|escape:'html':'UTF-8'}">
                {$node.name|escape:'html':'UTF-8'}
            </label>
            {if !empty($node.children)}
                <div id="tec-fl-children-{$node.id_category|intval}">
                    {include file='./category-tree.tpl' nodes=$node.children}
                </div>
            {/if}
        </li>
    {/foreach}
</ul>
