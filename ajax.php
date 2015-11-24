<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/hf_free_shipping_pro.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$free_shipping=new hf_free_shipping_pro();

$action = Tools::getValue('action');
$id_product = Tools::getValue('id_product');
$id_manufacturer = Tools::getValue('id_manufacturer');
$id_supplier = Tools::getValue('id_supplier');
$id_category = Tools::getValue('id_category');

if ($action=='save' && $id_product!=''){
	$free_shipping->delete(intval($id_product));
	//$freeShip=Tools::getValue('freeShip');
	//$fixedShip=Tools::getValue('fixedShip');
	$dateFrom=Tools::getValue('datefrom');
	$dateTo=Tools::getValue('dateto');
	$data=Tools::getValue('data');
	
	$free_shipping->delete($id_product);
	//if (intval($freeShip)==-1 || intval($fixedShip)==-1){
		//$free_shipping->save($id_product, $freeShip, $fixedShip, $dateFrom, $dateTo, $data);
		$free_shipping->save($id_product, $dateFrom, $dateTo, $data);
	//}
	exit;
}

if ($action=='isFree' && $id_product!=''){
	echo $free_shipping->isFree($id_product);
	exit;
}

if ($action=='saveManufacturer' && $id_manufacturer!=''){
	$free_shipping->deleteManufacturer(intval($id_manufacturer));
	$data=Tools::getValue('data');

	$free_shipping->saveManufacturer($id_manufacturer, $data);
	exit;
}

if ($action=='saveSupplier' && $id_supplier!=''){
	$free_shipping->deleteSupplier(intval($id_supplier));
	$data=Tools::getValue('data');

	$free_shipping->saveSupplier($id_supplier, $data);
	exit;
}

if ($action=='saveCategory' && $id_category!=''){
	$free_shipping->deleteCategory(intval($id_category));
	$data=Tools::getValue('data');

	$free_shipping->saveCategory($id_category, $data);
	exit;
}
?>