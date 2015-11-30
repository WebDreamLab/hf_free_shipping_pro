<?php
/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class Hf_free_shipping_pro extends CarrierModule
{
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'hf_free_shipping_pro';
		$this->tab = 'shipping_logistics';
		$this->version = '1.0.0';
		$this->author = 'WebDreamLab.com';
		$this->need_instance = 1;

		/**
		 * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
		 */
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Free, fixed or per amount shipping cost for products');
		$this->description = $this->l('Adds capability for select a free shipping or a fixed shipping cost per quantity.');
	}

	/**
	 * Don't forget to create update methods if needed:
	 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
	 */
	public function install()
	{
		if (extension_loaded('curl') == false) {
			$this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
			return false;
		}

		include(dirname(__FILE__) . '/sql/install.php');

		$carriers = Carrier::getCarriers($this->context->language->id);


		foreach ($carriers as $carrier)
		{
			$sql = 'INSERT INTO `'._DB_PREFIX_.'hf_free_shipping_pro_carriers`
					SET `id_carrier` = '.$carrier['id_carrier'].',
						`show_not_free` = 1,
						`show_free` = 1';

			if (!Db::getInstance()->execute($sql))
				return false;
		}

		return parent::install() &&
		$this->registerHook('header') &&
		$this->registerHook('backOfficeHeader') &&
		$this->registerHook('updateCarrier') &&
		$this->registerHook('displayAdminProductsExtra') &&
		$this->registerHook('actionProductSave') &&
		$this->registerHook('displayBackOfficeCategory') &&
		$this->registerHook('displayBackOfficeFooter') &&
		$this->registerHook('shoppingCart') &&
		$this->registerHook('displayShoppingCartFooter');
	}

	public function uninstall()
	{
		include(dirname(__FILE__) . '/sql/uninstall.php');

		return parent::uninstall();
	}

	public function getOrderShippingCost($params, $shipping_cost)
	{
		if (Context::getContext()->customer->logged == true)
		{
			$id_address_delivery = Context::getContext()->cart->id_address_delivery;
			$address = new Address($id_address_delivery);

			/**
			 * Send the details through the API
			 * Return the price sent by the API
			 */
			return 10;
		}

		return $shipping_cost;
	}

	public function getOrderShippingCostExternal($params)
	{
		return true;
	}

	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		/**
		 * If values have been submitted in the form, process.
		 */
		if (((bool)Tools::isSubmit('updateCarriers')) == true) {
			$this->postProcess();
		}

		$carriers = $this->loadCarriers();

		$this->context->smarty->assign(array(
			'carriers' => $carriers,
			'module_dir' => _PS_BASE_URL_.$this->_path,
			'token' => Tools::getAdminTokenLite('AdminModules'),
			'current_index' => AdminController::$currentIndex,
			'module_name' => $this->name,
			'module_tab' => $this->tab,
		));

		$output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

		return $output;
	}

	/**
	 * Save form data.
	 */
	protected function postProcess()
	{
		if ($carriers_props = Tools::getValue('carrier_prop')) {

			foreach ($carriers_props as $id_carrier => $carrier_prop) {
				$show_not_free = isset($carrier_prop['show_not_free']) ? 1 : 0;
				$show_free = isset($carrier_prop['show_free']) ? 1 : 0;

				$sql = 'UPDATE `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` SET
				 `show_not_free` = '.$show_not_free.',
				 `show_free` = '.$show_free.'
				 WHERE `id_carrier` = '.$id_carrier;
				Db::getInstance()->execute($sql);
			}
		}
	}

	protected function loadCarriers()
	{
		$carrier_list = Carrier::getCarriers($this->context->language->id, true);
		$collect_carrier_list = array();

		foreach ($carrier_list as $value)
		{
			$carrier_prop = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` WHERE `id_carrier`='.(int)$value['id_carrier']);
			$collect_carrier_list[] = array(
				'id_carrier' => $value['id_carrier'],
				'id_reference' => $value['id_reference'],
				'name' => $value['name'],
				'show_not_free' => $carrier_prop['show_not_free'],
				'show_free' => $carrier_prop['show_free'],
			);
		}

		return $collect_carrier_list;
	}

	/**
	 * Add the CSS & JavaScript files you want to be loaded in the BO.
	 */
	public function hookBackOfficeHeader()
	{
		if (Tools::getValue('module_name') == $this->name) {
			$this->context->controller->addJS($this->_path . 'views/js/back.js');
			$this->context->controller->addCSS($this->_path . 'views/css/back.css');
		}
	}

	/**
	 * Add the CSS & JavaScript files you want to be added on the FO.
	 */
	public function hookHeader()
	{
		$this->context->controller->addJS($this->_path . '/views/js/front.js');
		$this->context->controller->addCSS($this->_path . '/views/css/front.css');
	}

	public function hookUpdateCarrier($params)
	{
		/**
		 * Not needed since 1.5
		 * You can identify the carrier by the id_reference
		 */
	}

	public function hookDisplayAdminProductsExtra()
	{
		$id_product = Tools::getValue('id_product');
		if (!$id_product)
			return 'Please save this product, and then you can edit this settings';
		$product = new Product((int)$id_product);

		$carriers = Carrier::getCarriers($this->context->language->id, true);
		$zones = Zone::getZones();

		$fixed_price = array();
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` WHERE `id_product`='.$id_product;
		$result = Db::getInstance()->executeS($sql);

		foreach ($zones as $zone) {
			foreach ($carriers as $carrier) {
				$fixed_price[$zone['id_zone']]['name'] = $zone['name'];
				$fixed_price[$zone['id_zone']]['carrier'][$carrier['id_carrier']]['name'] = $carrier['name'];
				foreach ($result as $row) {
					if ($row['id_zone'] == $zone['id_zone'] && $row['id_carrier'] == $carrier['id_carrier'])
						$fixed_price[$zone['id_zone']]['carrier'][$carrier['id_carrier']]['price'] = $row['price'];
				}
			}
		}

		$free_price = array();
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'hf_free_shipping_pro_free` WHERE `id_product`='.$id_product;
		$result = Db::getInstance()->executeS($sql);
		foreach ($zones as $zone) {
			$free_price[$zone['id_zone']]['name'] = $zone['name'];
			foreach ($result as $row) {
				if ($row['id_zone'] == $zone['id_zone'])
					$free_price[$zone['id_zone']]['free_price'] = 1;
			}
		}

		$action_cancel = AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules');

		if (Validate::isLoadedObject($product))
		{
			$this->context->smarty->assign(array(
				'carriers' => $carriers,
				'zones' => $zones,
				'fixed_price' => $fixed_price,
				'free_price' => $free_price,
				'action_cancel' => $action_cancel
			));
			return $this->display(__FILE__, 'views/templates/hook/product_tab_form.tpl');
		}
	}

	public function hookActionProductSave($params)
	{
		if (!$params['id_product'])
			return;
		$id_product = (int)$params['id_product'];

		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` WHERE `id_product`='.$id_product);
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'hf_free_shipping_pro_free` WHERE `id_product`='.$id_product);

		$fixed_price = Tools::getValue('fixed_price');
		foreach ($fixed_price as $id_zone => $carrier) {
			foreach ($carrier as $id_carrier => $value) {
				if($value && Validate::isPrice($value)) {
					Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` SET
						`id_product` = '.$id_product.',
						`id_carrier` = '.(int)$id_carrier.',
						`id_zone` = '.(int)$id_zone.',
						`price` = '.$value
					);
				}
			}
		}

		$free_price = Tools::getValue('free_price');
		if ($free_price) {
			foreach ($free_price as $id_zone => $value) {
				Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'hf_free_shipping_pro_free` SET
						`id_product` = '.$id_product.',
						`id_zone` = '.(int)$id_zone
				);
			}
		}
	}

	public function hookDisplayBackOfficeCategory()
	{
		/* Place your code here. */
	}

	public function hookDisplayBackOfficeFooter()
	{
		/* Place your code here. */
	}

	public function hookDisplayShoppingCartFooter()
	{
		/* Place your code here. */
	}
}
