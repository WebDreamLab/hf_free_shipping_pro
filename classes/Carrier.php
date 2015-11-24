<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6704 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Carrier extends CarrierCore
{
	public static function getCarriersForOrder($id_zone, $groups = NULL)
	{
		global $cookie, $cart;
		
		$freeProducts=$cart->freeProducts();

		if (is_array($groups) AND !empty($groups))
			$result = Carrier::getCarriers((int)$cookie->id_lang, true, false, (int)$id_zone, $groups, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		else
			$result = Carrier::getCarriers((int)$cookie->id_lang, true, false, (int)$id_zone, array(1), PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		$resultsArray = array();
//		$carriersFree = Db::getInstance()->getRow('select carriers from `'._DB_PREFIX_.'hf_free_shipping_pro_carriers`');

		foreach ($result AS $k => $row)
		{
			$carrier = new Carrier((int)$row['id_carrier']);
			$shippingMethod = $carrier->getShippingMethod();
			if ($shippingMethod != Carrier::SHIPPING_METHOD_FREE)
			{
				// Get only carriers that are compliant with shipping method
				if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
					OR ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
				{
					unset($result[$k]);
					continue ;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior'])
				{
					// Get id zone
					if (!$id_zone)
						$id_zone = (int)$defaultCountry->id_zone;

					// Get only carriers that have a range compatible with cart
					if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone)))
						OR ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $cart->id_currency))))
					{
						unset($result[$k]);
						continue ;
					}
				}
			}
			
			$row['name'] = (strval($row['name']) != '0' ? $row['name'] : Configuration::get('PS_SHOP_NAME'));
			$row['price'] = ($shippingMethod == Carrier::SHIPPING_METHOD_FREE ? 0 : $cart->getOrderShippingCost((int)$row['id_carrier']));
			$row['price_tax_exc'] = ($shippingMethod == Carrier::SHIPPING_METHOD_FREE ? 0 : $cart->getOrderShippingCost((int)$row['id_carrier'], false));
			$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.(int)($row['id_carrier']).'.jpg') ? _THEME_SHIP_DIR_.(int)($row['id_carrier']).'.jpg' : '';

			// If price is false, then the carrier is unavailable (carrier module)
			if ($row['price'] === false)
			{
				unset($result[$k]);
				continue;
			}
			// If is free shipping and not in free carriers, delete
			/*if (strlen($carriersFree['carriers'])>1 && strpos($carriersFree['carriers'], ','.$row['id_carrier'].',') === false && $cart->getOrderShippingCost()==0)
			{
				unset($result[$k]);
				continue;
			}*/
			//if there are products with free shipping or not
			$freeCarrier=Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` where `id_carrier` = '.intval($row['id_carrier']));
			if ($freeCarrier){
				if ($freeProducts && !$freeCarrier['show_free']){
					unset($result[$k]);
					continue;
				}elseif(!$freeProducts && !$freeCarrier['show_not_free']){
					unset($result[$k]);
					continue;
				}
			}else{
				unset($result[$k]);
				continue;
			}

			$resultsArray[] = $row;
		}
		return $resultsArray;
	}
}