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
	/**
	 *
	 * @param int $id_zone
	 * @param Array $groups group of the customer
	 * @param array &$error contain an error message if an error occurs
	 * @return Array
	 */
	public static function getCarriersForOrder($id_zone, $groups = null, $cart = null, &$error = array())
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		if (is_null($cart)) {
			$cart = $context->cart;
		}
		if (isset($context->currency)) {
			$id_currency = $context->currency->id;
		}

		if (is_array($groups) && !empty($groups)) {
			$result = Carrier::getCarriers($id_lang, true, false, (int)$id_zone, $groups, self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		} else {
			$result = Carrier::getCarriers($id_lang, true, false, (int)$id_zone, array(Configuration::get('PS_UNIDENTIFIED_GROUP')), self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		}
		$results_array = array();

		foreach ($result as $k => $row) {
			$carrier = new Carrier((int)$row['id_carrier']);
			$shipping_method = $carrier->getShippingMethod();
			if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
				// Get only carriers that are compliant with shipping method
				if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)) {
					$error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
					unset($result[$k]);
					continue;
				}
				if (($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false)) {
					$error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
					unset($result[$k]);
					continue;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior']) {
					// Get id zone
					if (!$id_zone) {
						$id_zone = (int)Country::getIdZone(Country::getDefaultCountryId());
					}

					// Get only carriers that have a range compatible with cart
					if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT
						&& (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone))) {
						$error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
						unset($result[$k]);
						continue;
					}

					if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE
						&& (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $id_currency))) {
						$error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
						unset($result[$k]);
						continue;
					}
				}
			}

			$row['name'] = (strval($row['name']) != '0' ? $row['name'] : Carrier::getCarrierNameFromShopName());
			$row['price'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int)$row['id_carrier'], true, null, null, $id_zone));
			$row['price_tax_exc'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int)$row['id_carrier'], false, null, null, $id_zone));
			$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.(int)$row['id_carrier'].'.jpg') ? _THEME_SHIP_DIR_.(int)$row['id_carrier'].'.jpg' : '';

			// If price is false, then the carrier is unavailable (carrier module)
			if ($row['price'] === false) {
				unset($result[$k]);
				continue;
			}
			$results_array[] = $row;
		}

		// if we have to sort carriers by price
		$prices = array();
		if (Configuration::get('PS_CARRIER_DEFAULT_SORT') == Carrier::SORT_BY_PRICE) {
			foreach ($results_array as $r) {
				$prices[] = $r['price'];
			}
			if (Configuration::get('PS_CARRIER_DEFAULT_ORDER') == Carrier::SORT_BY_ASC) {
				array_multisort($prices, SORT_ASC, SORT_NUMERIC, $results_array);
			} else {
				array_multisort($prices, SORT_DESC, SORT_NUMERIC, $results_array);
			}
		}

		return $results_array;
	}
}