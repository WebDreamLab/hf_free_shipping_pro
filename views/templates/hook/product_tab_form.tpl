{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="product-free-shipping" class="panel product-tab">
	<input type="hidden" name="submitted_tabs[]" value="FreeShippingHf">
	<h3 class="panel-heading">{l s='Fixed shipping price' mod='hf_free_shipping_pro'}</h3>
	<table class="table">
		<thead>
		<tr>
			<th>{l s='Zones/Carriers' mod='hf_free_shipping_pro'}</th>
			{foreach $carriers as $carrier}
					<th>{$carrier['name']}</th>
			{/foreach}
		</tr>
		</thead>
		<tbody>
		{foreach $fixed_price as $id_zone => $zone}
			<tr>
				<td>{$zone['name']}</td>
				{foreach $zone['carrier'] as $id_carrier => $carrier}
					<td><input name="fixed_price[{$id_zone}][{$id_carrier}]" type="text" value="{if isset($carrier['price']) && !empty($carrier['price'])}{$carrier['price']}{/if}"></td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div class="panel-footer">
		<a href="{$action_cancel}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='hf_free_shipping_pro'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='hf_free_shipping_pro'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='hf_free_shipping_pro'}</button>
	</div>
</div>

<div class="panel product-tab">
	<h3 class="panel-heading">{l s='Free shipping' mod='hf_free_shipping_pro'}</h3>
	<div class="clearfix">
		<div class="col-md-6">
			<table class="table">
				<thead>
				<tr>
					<th>{l s='Zones' mod='hf_free_shipping_pro'}</th>
					<th>{l s='Active' mod='hf_free_shipping_pro'}</th>
				</tr>
				</thead>
				<tbody>
				{foreach $free_price as $id_zone => $zone}
					<tr>
						<td>{$zone['name']}</td>
						<td><input type="checkbox" name="free_price[{$id_zone}]" value=""{if isset($zone['free_price'])}checked="checked"{/if}></td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<div class="panel-footer">
		<a href="{$action_cancel}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='hf_free_shipping_pro'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='hf_free_shipping_pro'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='hf_free_shipping_pro'}</button>
	</div>
</div>

