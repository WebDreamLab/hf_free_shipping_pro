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
*  @version  Release: $Revision: 6697 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Cart extends CartCore
{
	protected $_id_zone=0;
	protected $_id_carrier=0;
	protected $_manufacturer_amount;
	protected $_supplier_amount;
	protected $_category_amount;
	protected $_category_fixed;
	protected $_free_products;
	protected $_fixed_products;
	protected $_id_products_NF='';
	protected $_id_products_FX='';
	public $my_var = 123;

	function getOrderShippingCost($id_carrier = NULL, $useTax = true,  Country $default_country = NULL, $product_list = NULL)
	{
		//error_reporting(E_ALL);
//ini_set('display_errors', '1');

		global $defaultCountry;
		global $cookie;

		if ($this->isVirtualCart())
			return 0;

		// Checking discounts in cart
		$products = $this->getProducts();
		$discounts = $this->getDiscounts(true);

		if ($discounts)
			foreach ($discounts AS $id_discount)
				if ($id_discount['id_discount_type'] == 3)
				{
					if ($id_discount['minimal'] > 0)
					{
						$total_cart = 0;

						$categories = Discount::getCategories((int)($id_discount['id_discount']));
						if (sizeof($categories))
							foreach($products AS $product)
								if (Product::idIsOnCategoryId((int)($product['id_product']), $categories))
									$total_cart += $product['total_wt'];

						if ($total_cart >= $id_discount['minimal'])
							return 0;
					}
					else
						return 0;
				}

		// Order total in default currency without fees
		$order_total = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);

		// Start with shipping cost at 0
		$shipping_cost = 0;

		// If no product added, return 0
		if ($order_total <= 0 AND !(int)(self::getNbProducts($this->id)))
			return $shipping_cost;

		// Get id zone
		if (isset($this->id_address_delivery)
			AND $this->id_address_delivery
			AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery))
			$id_zone = Address::getZoneById((int)($this->id_address_delivery));
		else
		{
			// This method can be called from the backend, and $defaultCountry won't be defined
			if (!Validate::isLoadedObject($defaultCountry))
				$defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
			$id_zone = (int)$defaultCountry->id_zone;
		}

		// If no carrier, select default one
		if (!$id_carrier)
			$id_carrier = $this->id_carrier;

		if ($id_carrier && !$this->isCarrierInRange($id_carrier, $id_zone))
			$id_carrier = '';

		if (empty($id_carrier) && $this->isCarrierInRange(Configuration::get('PS_CARRIER_DEFAULT'), $id_zone))
			$id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));

		//save id_zone and id_carrier
		$this->_id_zone=$id_zone;
		$this->_id_carrier=$id_carrier;

		if (empty($id_carrier))
		{
			if ((int)($this->id_customer))
			{
				$customer = new Customer((int)($this->id_customer));
				$result = Carrier::getCarriers((int)(Configuration::get('PS_LANG_DEFAULT')), true, false, (int)($id_zone), $customer->getGroups());
				unset($customer);
			}
			else
				$result = Carrier::getCarriers((int)(Configuration::get('PS_LANG_DEFAULT')), true, false, (int)($id_zone));

			$resultsArray = array();
			foreach ($result AS $k => $row)
			{
				if ($row['id_carrier'] == Configuration::get('PS_CARRIER_DEFAULT'))
					continue;

				if (!isset(self::$_carriers[$row['id_carrier']]))
					self::$_carriers[$row['id_carrier']] = new Carrier((int)($row['id_carrier']));

				$carrier = self::$_carriers[$row['id_carrier']];

				// Get only carriers that are compliant with shipping method
				if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
					OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
				{
					unset($result[$k]);
					continue ;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior'])
				{
					// Get only carriers that have a range compatible with cart
					if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->getTotalWeight(), $id_zone)))
						OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, (int)($this->id_currency)))))
					{
						unset($result[$k]);
						continue ;
					}
				}
				//save id_carrier
				$this->_id_carrier=$row['id_carrier'];

				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
				{
					$shipping = $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);

					if (!isset($tmp))
						$tmp = $shipping;

					if ($shipping <= $tmp)
						$id_carrier = (int)($row['id_carrier']);
				}
				else // by price
				{
					$shipping = $carrier->getDeliveryPriceByPrice($this->getShippingOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING), $id_zone, (int)($this->id_currency));

					if (!isset($tmp))
						$tmp = $shipping;

					if ($shipping <= $tmp)
						$id_carrier = (int)($row['id_carrier']);
				}
			}
		}

		if (empty($id_carrier))
			$id_carrier = Configuration::get('PS_CARRIER_DEFAULT');

		//save  id_carrier
		$this->_id_carrier=$id_carrier;

		//string with non free or fixed shipping id products
		$this->_id_products_FX = '';
		//delete elements with free shipping or fixed shipping amount
		//if ($this->_id_carrier!=0 && $this->_id_zone!=0 && $cookie->isLogged()){
		if ($this->_id_carrier!=0 && $this->_id_zone!=0){
			//free shipping per manufacturer and supplier
			$auxMS=$this->getShippingOrderTotalManuSupCat(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);
			//free shipping per manufacturer
			$this->_manufacturer_amount = $auxMS[0];
			//free shipping per supplier
			$this->_supplier_amount = $auxMS[1];
			// order total per category
			$this->_category_amount = $auxMS[2];
			// fixed shipping per category
			$this->_category_fixed = $auxMS[3];
			//free shipping products
			$this->_free_products = $auxMS[4];
			//fixed shipping products
			$this->_fixed_products = $auxMS[5];

			$hfi=0;
			while ($hfi<count($products)){
				$cats=Product::getProductCategories($products[$hfi]['id_product']);
				$catFree=0;
				$catFixed=0;
				for ($i=0;$i<count($cats);$i++){
					if ($this->_category_amount[intval($cats[$i])]){
						$catFree=-1;
						$i=count($cats);
					}
					if ($this->_category_fixed[intval($cats[$i])]){
						$catFixed=-1;
						$i=count($cats);
					}
				}
				if ($this->_manufacturer_amount[intval($products[$hfi]['id_manufacturer'])] ||
					$this->_supplier_amount[intval($products[$hfi]['id_supplier'])] ||
					$catFree ||
					$this->_free_products[intval($products[$hfi]['id_product'])]){
					unset($products[$hfi]);
					$products=array_values($products);
				}else
					if ($catFixed || $this->_fixed_products[intval($products[$hfi]['id_product'])]){
						$this->_id_products_FX .= ','.$products[$hfi]['id_product'];
						unset($products[$hfi]);
						$products=array_values($products);
					}else
						$hfi++;
			}
		}
		//string with non free or fixed shipping id products
		$this->_id_products_NF = '';
		for ($i=0;$i<count($products);$i++){
			$this->_id_products_NF .= ', '.$products[$i]['id_product'];
		}

		// Order total for shipping
		$shipping_order_total = $this->getShippingOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);

		if (!isset(self::$_carriers[$id_carrier]))
			self::$_carriers[$id_carrier] = new Carrier((int)($id_carrier), Configuration::get('PS_LANG_DEFAULT'));
		$carrier = self::$_carriers[$id_carrier];
		if (!Validate::isLoadedObject($carrier))
			die(Tools::displayError('Fatal error: "no default carrier"'));
		if (!$carrier->active)
			return $shipping_cost;

		// Free fees if free carrier
		if ($carrier->is_free == 1)
			return 0;

		// Select carrier tax
		if ($useTax AND !Tax::excludeTaxeOption())
			$carrierTax = Tax::getCarrierTaxRate((int)$carrier->id, (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		$configuration = Configuration::getMultiple(array('PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT'));
		// Free fees
		$free_fees_price = 0;
		if (isset($configuration['PS_SHIPPING_FREE_PRICE']))
			$free_fees_price = Tools::convertPrice((float)($configuration['PS_SHIPPING_FREE_PRICE']), Currency::getCurrencyInstance((int)($this->id_currency)));
		$orderTotalwithDiscounts = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
		if ($orderTotalwithDiscounts >= (float)($free_fees_price) AND (float)($free_fees_price) > 0)
			return $shipping_cost;
		if (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) AND $this->getTotalWeight() >= (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) AND (float)($configuration['PS_SHIPPING_FREE_WEIGHT']) > 0)
			return $shipping_cost;

		// Get shipping cost using correct method
		if ($carrier->range_behavior)
		{
			// Get id zone
			if (
				isset($this->id_address_delivery)
				AND $this->id_address_delivery
				AND Customer::customerHasAddress($this->id_customer, $this->id_address_delivery)
			)
				$id_zone = Address::getZoneById((int)($this->id_address_delivery));
			else
				$id_zone = (int)$defaultCountry->id_zone;
			if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT AND (!Carrier::checkDeliveryPriceByWeight($carrier->id, $this->getTotalWeight(), $id_zone)))
				OR ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE AND (!Carrier::checkDeliveryPriceByPrice($carrier->id, $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, (int)($this->id_currency)))))
				$shipping_cost += 0;
			else if (count($products)>0){
				if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
					$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
				else // by price
					$shipping_cost += $carrier->getDeliveryPriceByPrice($shipping_order_total, $id_zone, (int)($this->id_currency));
			}
		}
		else if (count($products)>0)
		{
			if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
				$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight(), $id_zone);
			else
				$shipping_cost += $carrier->getDeliveryPriceByPrice($shipping_order_total, $id_zone, (int)($this->id_currency));

		}

		// Adding handling charges
		if (isset($configuration['PS_SHIPPING_HANDLING']) AND $carrier->shipping_handling AND count($products)>0)
			$shipping_cost += (float)($configuration['PS_SHIPPING_HANDLING']);

		$shipping_cost = Tools::convertPrice($shipping_cost, Currency::getCurrencyInstance((int)($this->id_currency)));

		// Additional Shipping Cost per product
		foreach($products AS $product)
			$shipping_cost += $product['additional_shipping_cost'] * $product['cart_quantity'];

		//get external shipping cost from module
		if ($carrier->shipping_external)
		{
			$moduleName = $carrier->external_module_name;
			$module = Module::getInstanceByName($moduleName);
			if (key_exists('id_carrier', $module))
				$module->id_carrier = $carrier->id;
			if($carrier->need_range)
				$shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
			else
				$shipping_cost = $module->getOrderShippingCostExternal($this);

			// Check if carrier is available
			if ($shipping_cost === false)
				return false;
		}

		//apply fixed shipping cost per unit
		$prodAux='';
		if (isset($this->_fixed_products))
			foreach($this->_fixed_products as $key=>$value){
				$product = $this->getProducts(true, $key);
				if (intval($value)>0){
					$shipping_cost += $value * $product[0]['cart_quantity'];
					$prodAux .= ', '.$key;
				}
			}
		if (isset($this->_category_fixed)){
			$aux=Db::getInstance()->executeS("
				select cp.`id_product` , c.`id_category`, cp.`quantity`
				from `"._DB_PREFIX_."cart_product` cp
				inner join `"._DB_PREFIX_."category_product` c on c.`id_product` = cp.`id_product`
				where cp.`id_cart`=".intval($this->id)." and cp.`id_product` not in (0".$prodAux.") and cp.`id_product` in (0".$this->_id_products_FX.")");
			for ($i=0; $i<count($aux); $i++){
				if (intval($this->_category_fixed[$aux[$i]['id_category']])>0){
					$shipping_cost += $this->_category_fixed[$aux[$i]['id_category']] * $aux[$i]['quantity'];
				}
			}
		}

		//fix the product total
		$this->getProducts(true);

		// Apply tax
		if (isset($carrierTax))
			$shipping_cost *= 1 + ($carrierTax / 100);

		return (float)(Tools::ps_round((float)($shipping_cost), 2));
	}

	/**
	 * Return cart weight
	 *
	 * @return float Cart weight
	 */
	public function getTotalWeight($products = NULL)
	{
		//if (!isset(self::$_totalWeight[$this->id]) || intval($this->_id_carrier)==0 || intval($this->_id_zone)==0)
		//{
		$result = Db::getInstance()->getRow('
			SELECT SUM((p.`weight` + pa.`weight`) * cp.`quantity`) as nb
			FROM `'._DB_PREFIX_.'cart_product` cp
			LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON cp.`id_product_attribute` = pa.`id_product_attribute`
			WHERE (cp.`id_product_attribute` IS NOT NULL AND cp.`id_product_attribute` != 0)
			AND cp.`id_cart` = '.(int)($this->id).' and cp.`id_product` in (0'.$this->_id_products_NF.')');
		$result2 = Db::getInstance()->getRow('
			SELECT SUM(p.`weight` * cp.`quantity`) as nb
			FROM `'._DB_PREFIX_.'cart_product` cp
			LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
			WHERE (cp.`id_product_attribute` IS NULL OR cp.`id_product_attribute` = 0)
			AND cp.`id_cart` = '.(int)($this->id).' and cp.`id_product` in (0'.$this->_id_products_NF.')');
		self::$_totalWeight[$this->id] = round((float)($result['nb']) + (float)($result2['nb']), 3);
		//}
		return self::$_totalWeight[$this->id];
	}

	/**
	 * This function returns the total cart amount
	 *
	 * Possible values for $type:
	 * Cart::ONLY_PRODUCTS
	 * Cart::ONLY_DISCOUNTS
	 * Cart::BOTH
	 * Cart::BOTH_WITHOUT_SHIPPING
	 * Cart::ONLY_SHIPPING
	 * Cart::ONLY_WRAPPING
	 * Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING
	 *
	 * @param boolean $withTaxes With or without taxes
	 * @param integer $type Total type
	 * @return float Order total
	 */
	public function getShippingOrderTotal($withTaxes = true, $type = Cart::BOTH)
	{
		if (!$this->id)
			return 0;
		$type = (int)($type);
		if (!in_array($type, array(Cart::ONLY_PRODUCTS, Cart::ONLY_DISCOUNTS, Cart::BOTH, Cart::BOTH_WITHOUT_SHIPPING, Cart::ONLY_SHIPPING, Cart::ONLY_WRAPPING, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)))
			die(Tools::displayError());

		// no shipping cost if is a cart with only virtuals products
		$virtual = $this->isVirtualCart();
		if ($virtual AND $type == Cart::ONLY_SHIPPING)
			return 0;
		if ($virtual AND $type == Cart::BOTH)
			$type = Cart::BOTH_WITHOUT_SHIPPING;
		$shipping_fees = ($type != Cart::BOTH_WITHOUT_SHIPPING AND $type != Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) ? $this->getOrderShippingCost(NULL, (int)($withTaxes)) : 0;
		if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
			$type = Cart::ONLY_PRODUCTS;

		$products = $this->getProducts();

		//delete elements with free shipping
		$hfi=0;
		while ($hfi<count($products)){
			if (strpos($this->_id_products_NF, ', '.$products[$hfi]['id_product']) === false){
				unset($products[$hfi]);
				$products=array_values($products);
			}else
				$hfi++;
		}

		$order_total = 0;
		if (Tax::excludeTaxeOption())
			$withTaxes = false;
		foreach ($products AS $product)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{

				// Here taxes are computed only once the quantity has been applied to the product price
				$price = Product::getPriceStatic((int)$product['id_product'], false, (int)$product['id_product_attribute'], 2, NULL, false, true, $product['cart_quantity'], false, (int)$this->id_customer ? (int)$this->id_customer : NULL, (int)$this->id, ($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

				$total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
				$total_price = $price * (int)$product['cart_quantity'];

				if ($withTaxes)
				{
					$total_price = ($total_price - $total_ecotax) * (1 + (float)(Tax::getProductTaxRate((int)$product['id_product'], (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')})) / 100);
					$total_ecotax = $total_ecotax * (1 + Tax::getProductEcotaxRate((int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) / 100);
					$total_price = Tools::ps_round($total_price + $total_ecotax, 2);

				}
			}
			else
			{

				$price = Product::getPriceStatic((int)($product['id_product']), $withTaxes, (int)($product['id_product_attribute']), 2, NULL, false, true, $product['cart_quantity'], false, ((int)($this->id_customer) ? (int)($this->id_customer) : NULL), (int)($this->id), ((int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) ? (int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) : NULL));
				$total_price = Tools::ps_round($price, 2) * (int)($product['cart_quantity']);
			}
			$order_total += $total_price;
		}
		$order_total_products = $order_total;
		if ($type == Cart::ONLY_DISCOUNTS)
			$order_total = 0;
		// Wrapping Fees
		$wrapping_fees = 0;
		if ($this->gift)
		{
			$wrapping_fees = (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
			if ($withTaxes)
			{
				$wrapping_fees_tax = new Tax((int)(Configuration::get('PS_GIFT_WRAPPING_TAX')));
				$wrapping_fees *= 1 + (((float)($wrapping_fees_tax->rate) / 100));
			}
			$wrapping_fees = Tools::convertPrice(Tools::ps_round($wrapping_fees, 2), Currency::getCurrencyInstance((int)($this->id_currency)));
		}

		if ($type != Cart::ONLY_PRODUCTS)
		{
			$discounts = array();
			/* Firstly get all discounts, looking for a free shipping one (in order to substract shipping fees to the total amount) */
			if ($discountIds = $this->getDiscounts(true))
			{
				foreach ($discountIds AS $id_discount)
				{
					$discount = new Discount((int)($id_discount['id_discount']));
					if (Validate::isLoadedObject($discount))
					{
						$discounts[] = $discount;
						if ($discount->id_discount_type == 3)
							foreach($products AS $product)
							{
								$categories = Discount::getCategories($discount->id);
								if (count($categories) AND Product::idIsOnCategoryId($product['id_product'], $categories))
								{
									if($type == Cart::ONLY_DISCOUNTS)
										$order_total -= $shipping_fees;
									$shipping_fees = 0;
									break;
								}
							}
					}
				}
				/* Secondly applying all vouchers to the correct amount */
				$shrunk = false;
				foreach ($discounts AS $discount)
					if ($discount->id_discount_type != 3)
					{
						$order_total -= Tools::ps_round((float)($discount->getValue(sizeof($discounts), $order_total_products, $shipping_fees, $this->id, (int)($withTaxes))), 2);
						if ($discount->id_discount_type == 2)
							if (in_array($discount->behavior_not_exhausted, array(1,2)))
								$shrunk = true;
					}

				$order_total_discount = 0;
				if ($shrunk AND $order_total < (-$wrapping_fees - $order_total_products - $shipping_fees))
					$order_total_discount = -$wrapping_fees - $order_total_products - $shipping_fees;
				else
					$order_total_discount = $order_total;
			}
		}

		if ($type == Cart::ONLY_SHIPPING) return $shipping_fees;
		if ($type == Cart::ONLY_WRAPPING) return $wrapping_fees;
		if ($type == Cart::BOTH) $order_total += $shipping_fees + $wrapping_fees;
		if ($order_total < 0 AND $type != Cart::ONLY_DISCOUNTS) return 0;
		if ($type == Cart::ONLY_DISCOUNTS AND isset($order_total_discount))
			return Tools::ps_round((float)($order_total_discount), 2);
		return Tools::ps_round((float)($order_total), 2);
	}

	public function getShippingOrderTotalManuSupCat($withTaxes = true, $type = Cart::BOTH, $ret_amounts=false)
	{
		if (!$this->id)
			return 0;
		$type = (int)($type);
		if (!in_array($type, array(Cart::ONLY_PRODUCTS, Cart::ONLY_DISCOUNTS, Cart::BOTH, Cart::BOTH_WITHOUT_SHIPPING, Cart::ONLY_SHIPPING, Cart::ONLY_WRAPPING, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)))
			die(Tools::displayError());

		// no shipping cost if is a cart with only virtuals products
		$virtual = $this->isVirtualCart();
		if ($virtual AND $type == Cart::ONLY_SHIPPING)
			return 0;
		if ($virtual AND $type == Cart::BOTH)
			$type = Cart::BOTH_WITHOUT_SHIPPING;
		$shipping_fees = ($type != Cart::BOTH_WITHOUT_SHIPPING AND $type != Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) ? $this->getOrderShippingCost(NULL, (int)($withTaxes)) : 0;
		if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
			$type = Cart::ONLY_PRODUCTS;

		$products = $this->getProducts();

		$manufacturers = array();
		$arrRetMan = array();
		$suppliers = array();
		$arrRetSup = array();
		$categories = array();
		$arrRetCat = array();
		$arrRetProdsFree = array();
		$arrRetProdsFixed = array();
		$restAmountMan = array();
		$restAmountSup = array();
		$restAmountCat = array();

		$order_total = 0;
		if (Tax::excludeTaxeOption())
			$withTaxes = false;
		foreach ($products AS $product)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{

				// Here taxes are computed only once the quantity has been applied to the product price
				$price = Product::getPriceStatic((int)$product['id_product'], false, (int)$product['id_product_attribute'], 2, NULL, false, true, $product['cart_quantity'], false, (int)$this->id_customer ? (int)$this->id_customer : NULL, (int)$this->id, ($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

				$total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
				$total_price = $price * (int)$product['cart_quantity'];

				if ($withTaxes)
				{
					$total_price = ($total_price - $total_ecotax) * (1 + (float)(Tax::getProductTaxRate((int)$product['id_product'], (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')})) / 100);
					$total_ecotax = $total_ecotax * (1 + Tax::getProductEcotaxRate((int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) / 100);
					$total_price = Tools::ps_round($total_price + $total_ecotax, 2);

				}
			}
			else
			{

				$price = Product::getPriceStatic((int)($product['id_product']), $withTaxes, (int)($product['id_product_attribute']), 2, NULL, false, true, $product['cart_quantity'], false, ((int)($this->id_customer) ? (int)($this->id_customer) : NULL), (int)($this->id), ((int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) ? (int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) : NULL));
				$total_price = Tools::ps_round($price, 2) * (int)($product['cart_quantity']);
			}
			$cats=Product::getProductCategories($product['id_product']);
			for ($i=0;$i<count($cats);$i++){
				$arrRetCat[intval($cats[$i]['id_category'])]=0;
				$categories[intval($cats[$i]['id_category'])] += $total_price;
			}
			unset($cats);
			$arrRetMan[intval($product['id_manufacturer'])]=0;
			$arrRetSup[intval($product['id_supplier'])]=0;
			$manufacturers[intval($product['id_manufacturer'])] += $total_price;
			$suppliers[intval($product['id_supplier'])] += $total_price;
		}

		//manufacturer products free?
		$aux=Db::getInstance()->executeS("
			select m.`id_manufacturer`, m.`price`, m.`free` from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."hf_free_shipping_pro_manufacturer` m on m.`id_manufacturer` = p.`id_manufacturer`
			where cp.`id_cart`=".intval($this->id)." and m.`id_zone` = ".intval($this->_id_zone));
		for($i=0;$i<count($aux);$i++){
			if ($aux[$i]['free'] || $aux[$i]['price']<=$manufacturers[intval($aux[$i]['id_manufacturer'])])
				$arrRetMan[intval($aux[$i]['id_manufacturer'])]=-1;
			else{
				$arrRetMan[intval($aux[$i]['id_manufacturer'])]=0;
				$restAmountMan[intval($aux[$i]['id_manufacturer'])]=$aux[$i]['price']-$manufacturers[intval($aux[$i]['id_manufacturer'])];
			}
		}
		//supplier products free?
		$aux=Db::getInstance()->executeS("
			select m.`id_supplier`, m.`price`, m.`free` from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."hf_free_shipping_pro_supplier` m on m.`id_supplier` = p.`id_supplier`
			where cp.`id_cart`=".intval($this->id)." and m.`id_zone` = ".intval($this->_id_zone));
		for($i=0;$i<count($aux);$i++){
			if ($aux[$i]['free'] || $aux[$i]['price']<=$suppliers[intval($aux[$i]['id_supplier'])])
				$arrRetSup[intval($aux[$i]['id_supplier'])]=-1;
			else{
				$arrRetSup[intval($aux[$i]['id_supplier'])]=0;
				$restAmountSup[intval($aux[$i]['id_supplier'])]=$aux[$i]['price']-$suppliers[intval($aux[$i]['id_supplier'])];
			}
		}
		//category products free?
		$aux=Db::getInstance()->executeS("
			select distinct c.`id_category`, m.`price`, m.`free`, f.`price` fixed from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."category_product` c on c.`id_product` = p.`id_product`
			left join `"._DB_PREFIX_."hf_free_shipping_pro_category` m on m.`id_category` = c.`id_category` and m.`id_zone` = ".intval($this->_id_zone)."
			left join `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` f on f.`id_category` = c.`id_category` and f.`id_zone` = ".intval($this->_id_zone)." and f.`id_carrier` = ".intval($this->_id_carrier)."
			where cp.`id_cart`=".intval($this->id));
		for($i=0;$i<count($aux);$i++){
			if (isset($aux[$i]['free']) && ($aux[$i]['free'] || $aux[$i]['price']<=$categories[intval($aux[$i]['id_category'])])){
				$arrRetCat[intval($aux[$i]['id_category'])]=-1;
				$arrRetCatFixed[intval($aux[$i]['id_category'])]=0;
			}else{
				if(intval($aux[$i]['price'])>0){
					$restAmountCat[intval($aux[$i]['id_category'])]=$aux[$i]['price']-$categories[intval($aux[$i]['id_category'])];
				}
				$arrRetCat[intval($aux[$i]['id_category'])]=0;
				if (isset($aux[$i]['fixed']))
					$arrRetCatFixed[intval($aux[$i]['id_category'])]=$aux[$i]['fixed'];
				else{
					$arrRetCatFixed[intval($aux[$i]['id_product'])]=0;
					$cat=new Category(intval($aux[$i]['id_category']));
					$parents=$cat->getParentsCategories();
					$aux2='';
					for($j=1;$j<count($parents);$j++){
						$aux2=Db::getInstance()->getRow("
							select m.`id_category`, m.`price`, m.`free`
							from `"._DB_PREFIX_."hf_free_shipping_pro_category` m where m.`id_category` = ".intval($parents[$j]['id_category'])." and m.`id_zone` = ".intval($this->_id_zone));
						if ($aux2 && ($aux2['free'] || $aux2['price']<=$categories[intval($aux[$i]['id_category'])])){
							$arrRetCat[intval($aux[$i]['id_category'])]=-1;
							$arrRetCatFixed[intval($aux[$i]['id_category'])]=0;
							$j=count($parents);
						}else{
							if($aux2 && !isset($restAmountCat[intval($aux[$i]['id_category'])])){
								$restAmountCat[intval($aux[$i]['id_category'])]=$aux2['price']-$categories[intval($aux[$i]['id_category'])];
							}
							$aux2=Db::getInstance()->getRow("
								select m.`id_category`, m.`price`
								from `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` m where m.`id_category` = ".intval($parents[$j]['id_category'])." and m.`id_zone` = ".intval($this->_id_zone)." and m.`id_carrier` = ".intval($this->_id_carrier));
							if ($aux2){
								$arrRetCatFixed[intval($aux[$i]['id_category'])]=$aux2['price'];
								$arrRetCat[intval($aux[$i]['id_category'])]=0;
								$j=count($parents);
							}
						}
					}
					unset($aux2);
				}
			}
		}
		//products products free or fixed?
		$aux=Db::getInstance()->executeS("
			select cp.`id_product`, fr.`price` free, fx.`price` fixed
			from `"._DB_PREFIX_."cart_product` cp
			left join `"._DB_PREFIX_."hf_free_shipping_pro` fs on fs.`id_product` = cp.`id_product`
			left join `"._DB_PREFIX_."hf_free_shipping_pro_free` fr on fr.`id_free_shipping` = fs.`id_free_shipping` and fr.`id_zone` = ".intval($this->_id_zone)." and fs.`date_from` <= curdate() and (fs.`date_to` >= curdate() or ifnull(fs.`date_to`, '')='')
			left join `"._DB_PREFIX_."hf_free_shipping_pro_fixed` fx on fx.`id_free_shipping` = fs.`id_free_shipping` and fx.`id_zone` = ".intval($this->_id_zone)." and fx.`id_carrier` = ".intval($this->_id_carrier)."
			where cp.`id_cart`=".intval($this->id));
		for($i=0;$i<count($aux);$i++){
			if (isset($aux[$i]['free'])){
				$arrRetProdsFree[intval($aux[$i]['id_product'])]=-1;
				$arrRetProdsFixed[intval($aux[$i]['id_product'])]=0;
			}else{
				$arrRetProdsFree[intval($aux[$i]['id_product'])]=0;
				if (isset($aux[$i]['fixed']))
					$arrRetProdsFixed[intval($aux[$i]['id_product'])]=$aux[$i]['fixed'];
				else
					$arrRetProdsFixed[intval($aux[$i]['id_product'])]=0;
			}
		}

		unset($aux);
		if ($ret_amounts==true){
			return array(
				0=>$restAmountMan,
				1=>$restAmountSup,
				2=>$restAmountCat);
		}else{
			return array(
				0=>$arrRetMan,
				1=>$arrRetSup,
				2=>$arrRetCat,
				3=>$arrRetCatFixed,
				4=>$arrRetProdsFree,
				5=>$arrRetProdsFixed);
		}
	}

	public function freeProducts(){

		if (!$this->id)
			return 0;
		//$type = (int)($type);
		$type =Cart::ONLY_SHIPPING;

		//if (!in_array($type, array(Cart::ONLY_PRODUCTS, Cart::ONLY_DISCOUNTS, Cart::BOTH, Cart::BOTH_WITHOUT_SHIPPING, Cart::ONLY_SHIPPING, Cart::ONLY_WRAPPING, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)))
		//	die(Tools::displayError());

		// no shipping cost if is a cart with only virtuals products
		$virtual = $this->isVirtualCart();
		if ($virtual AND $type == Cart::ONLY_SHIPPING)
			return 0;
		if ($virtual AND $type == Cart::BOTH)
			$type = Cart::BOTH_WITHOUT_SHIPPING;
		$shipping_fees = ($type != Cart::BOTH_WITHOUT_SHIPPING AND $type != Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) ? $this->getOrderShippingCost(NULL, (int)($withTaxes)) : 0;
		if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
			$type = Cart::ONLY_PRODUCTS;

		$products = $this->getProducts();

		$manufacturers = array();
		$arrRetMan = array();
		$suppliers = array();
		$arrRetSup = array();
		$categories = array();
		$arrRetCat = array();
		$arrRetProdsFree = array();
		$arrRetProdsFixed = array();
		$restAmountMan = array();
		$restAmountSup = array();
		$restAmountCat = array();

		$order_total = 0;
		if (Tax::excludeTaxeOption())
			$withTaxes = false;

		foreach ($products AS $product)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{

				// Here taxes are computed only once the quantity has been applied to the product price
				$price = Product::getPriceStatic((int)$product['id_product'], false, (int)$product['id_product_attribute'], 2, NULL, false, true, $product['cart_quantity'], false, (int)$this->id_customer ? (int)$this->id_customer : NULL, (int)$this->id, ($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

				$total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
				$total_price = $price * (int)$product['cart_quantity'];

				if ($withTaxes)
				{
					$total_price = ($total_price - $total_ecotax) * (1 + (float)(Tax::getProductTaxRate((int)$product['id_product'], (int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')})) / 100);
					$total_ecotax = $total_ecotax * (1 + Tax::getProductEcotaxRate((int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) / 100);
					$total_price = Tools::ps_round($total_price + $total_ecotax, 2);

				}
			}
			else
			{

				$price = Product::getPriceStatic((int)($product['id_product']), $withTaxes, (int)($product['id_product_attribute']), 2, NULL, false, true, $product['cart_quantity'], false, ((int)($this->id_customer) ? (int)($this->id_customer) : NULL), (int)($this->id), ((int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) ? (int)($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) : NULL));
				$total_price = Tools::ps_round($price, 2) * (int)($product['cart_quantity']);
			}
			$cats=Product::getProductCategories($product['id_product']);
			for ($i=0;$i<count($cats);$i++){
				$arrRetCat[intval($cats[$i]['id_category'])]=0;
				$categories[intval($cats[$i]['id_category'])] += $total_price;
			}
			unset($cats);
			$arrRetMan[intval($product['id_manufacturer'])]=0;
			$arrRetSup[intval($product['id_supplier'])]=0;
			$manufacturers[intval($product['id_manufacturer'])] += $total_price;
			$suppliers[intval($product['id_supplier'])] += $total_price;
		}

		//manufacturer products free?
		$aux=Db::getInstance()->executeS("
			select m.`id_manufacturer`, m.`price`, m.`free` from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."hf_free_shipping_pro_manufacturer` m on m.`id_manufacturer` = p.`id_manufacturer`
			where cp.`id_cart`=".intval($this->id)." and m.`id_zone` = ".intval($this->_id_zone));
		for($i=0;$i<count($aux);$i++){
			if ($aux[$i]['free'] || $aux[$i]['price']<=$manufacturers[intval($aux[$i]['id_manufacturer'])])
				return true;
		}
		//supplier products free?
		$aux=Db::getInstance()->executeS("
			select m.`id_supplier`, m.`price`, m.`free` from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."hf_free_shipping_pro_supplier` m on m.`id_supplier` = p.`id_supplier`
			where cp.`id_cart`=".intval($this->id)." and m.`id_zone` = ".intval($this->_id_zone));
		for($i=0;$i<count($aux);$i++){
			if ($aux[$i]['free'] || $aux[$i]['price']<=$suppliers[intval($aux[$i]['id_supplier'])])
				return true;
		}
		//category products free?
		$aux=Db::getInstance()->executeS("
			select distinct c.`id_category`, m.`price`, m.`free`, f.`price` fixed from `"._DB_PREFIX_."cart_product` cp
			inner join `"._DB_PREFIX_."product` p on p.`id_product` = cp.`id_product`
			inner join `"._DB_PREFIX_."category_product` c on c.`id_product` = p.`id_product`
			left join `"._DB_PREFIX_."hf_free_shipping_pro_category` m on m.`id_category` = c.`id_category` and m.`id_zone` = ".intval($this->_id_zone)."
			left join `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` f on f.`id_category` = c.`id_category` and f.`id_zone` = ".intval($this->_id_zone)." and f.`id_carrier` = ".intval($this->_id_carrier)."
			where cp.`id_cart`=".intval($this->id));
		for($i=0;$i<count($aux);$i++){
			if (isset($aux[$i]['free']) && ($aux[$i]['free'] || $aux[$i]['price']<=$categories[intval($aux[$i]['id_category'])])){
				return true;
			}else{
				$arrRetCat[intval($aux[$i]['id_category'])]=0;
				if (isset($aux[$i]['fixed']))
					return false;
				else{
					$arrRetCatFixed[intval($aux[$i]['id_product'])]=0;
					$cat=new Category(intval($aux[$i]['id_category']));
					$parents=$cat->getParentsCategories();
					$aux2='';
					for($j=1;$j<count($parents);$j++){
						$aux2=Db::getInstance()->getRow("
							select m.`id_category`, m.`price`, m.`free`
							from `"._DB_PREFIX_."hf_free_shipping_pro_category` m where m.`id_category` = ".intval($parents[$j]['id_category'])." and m.`id_zone` = ".intval($this->_id_zone));
						if ($aux2 && ($aux2['free'] || $aux2['price']<=$categories[intval($aux[$i]['id_category'])])){
							return true;
						}
					}
					unset($aux2);
				}
			}
		}
		//products products free or fixed?
		$aux=Db::getInstance()->executeS("
			select cp.`id_product`, fr.`price` free, fx.`price` fixed
			from `"._DB_PREFIX_."cart_product` cp
			left join `"._DB_PREFIX_."hf_free_shipping_pro` fs on fs.`id_product` = cp.`id_product`
			left join `"._DB_PREFIX_."hf_free_shipping_pro_free` fr on fr.`id_free_shipping` = fs.`id_free_shipping` and fr.`id_zone` = ".intval($this->_id_zone)." and fs.`date_from` <= curdate() and (fs.`date_to` >= curdate() or ifnull(fs.`date_to`, '')='')
			left join `"._DB_PREFIX_."hf_free_shipping_pro_fixed` fx on fx.`id_free_shipping` = fs.`id_free_shipping` and fx.`id_zone` = ".intval($this->_id_zone)." and fx.`id_carrier` = ".intval($this->_id_carrier)."
			where cp.`id_cart`=".intval($this->id));
		for($i=0;$i<count($aux);$i++){
			if (isset($aux[$i]['free']))
				return true;
		}

		unset($aux);

		return false;
	}
}