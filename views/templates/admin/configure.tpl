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
<div class="panel">
	<h3><i class="icon icon-truck"></i> {l s='My shipping module' mod='hf_free_shipping_pro'}</h3>
	<img src="{$module_dir|escape:'html':'UTF-8'}/logo.png" id="payment-logo" class="pull-right" />
	<p>	
		{l s='This module will boost your sales!' mod='hf_free_shipping_pro'}
	</p>
</div>

<div class="panel">
	<h3>{l s='Available carriers' mod='hf_free_shipping_pro'}</h3>
	<form id="module_form" class="defaultForm form-horizontal" action="{$current_index}&configure={$module_name}&tab_module={$module_tab}&module_name={$module_name}&token={$token}" method="post">
		<div class="form-wrapper">
			<div class="form-group">
				<div class="col-lg-9">
					{if isset($carriers) && count($carriers)}
						<table class="table">
							<thead>
								<tr>
									<th>{l s='Carriers/show' mod='hf_free_shipping_pro'}</th>
									<th>{l s='Show not free shipping' mod='hf_free_shipping_pro'}</th>
									<th>{l s='Show free shipping' mod='hf_free_shipping_pro'}</th>
								</tr>
							</thead>
							<tbody>
								{foreach $carriers as $carrier}
									<tr>
										<td>{$carrier['name']|escape:'html':'UTF-8'}</td>
										<td><input type="checkbox" name="carrier_prop[{$carrier['id_carrier']}][show_not_free]" value="1" {if $carrier['show_not_free']} checked="checked"{/if} class="noborder"></td>
										<td><input type="checkbox" name="carrier_prop[{$carrier['id_carrier']}][show_free]" value="1" {if $carrier['show_free']} checked="checked"{/if} class="noborder"></td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					{else}
						<p class="alert alert-warning">{l s='There are no available carriers' mod='hf_free_shipping_pro'}</p>
					{/if}
				</div>
			</div>
		</div><!-- /.form-wrapper -->
		<div class="panel-footer">
			<button type="submit" value="1" name="updateCarriers" class="btn btn-default pull-right">
				<i class="process-icon-save"></i> {l s='Save' mod='hf_free_shipping_pro'}
			</button>
		</div>
	</form>
</div>
