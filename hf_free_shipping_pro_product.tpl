<div class="hffs_block {$conditions}" title="{l s='Free Shipping.' mod='hf_free_shipping_pro'}">
	{l s='Free Shipping.' mod='hf_free_shipping'}
</div>
{if (count($categories)==0 && count($manufacturers)==0 && count($suppliers)==0 && $conditions=='withConds')}
	<div class="hffs_product_conds">{l s='You must be logged to see free shipping conditions.' mod='hf_free_shipping_pro'}</div>
{/if}
{foreach key=key item=item from=$categories}
	<div class="hffs_product_free">{l s='Free shipping by buying ' mod='hf_free_shipping_pro'} {convertPrice price=$item} {l s=' of category ' mod='hf_free_shipping_pro'} {$key}</div>
{/foreach}
{foreach key=key item=item from=$manufacturers}
	<div class="hffs_product_free">{l s='Free shipping by buying ' mod='hf_free_shipping_pro'} {convertPrice price=$item} {l s=' of manufacturer ' mod='hf_free_shipping_pro'} {$key}</div>
{/foreach}
{foreach key=key item=item from=$suppliers}
	<div class="hffs_product_free">{l s='Free shipping by buying ' mod='hf_free_shipping_pro'} {convertPrice price=$item} {l s=' of supplier ' mod='hf_free_shipping_pro'} {$key}</div>
{/foreach}