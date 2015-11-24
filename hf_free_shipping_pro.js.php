<?php
header('Content-Type: text/javascript');
header('Cache-control: must-revalidate');
echo '/*';
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/hf_free_shipping_pro.php');

$free_shipping=new hf_free_shipping_pro();

$id_product = Tools::getValue('id_product');
$id_manufacturer = Tools::getValue('id_manufacturer');
$id_supplier = Tools::getValue('id_supplier');
$id_category = Tools::getValue('id_category');
$token = Tools::getValue('token');
$admin_dir = Tools::getValue('ap');
echo '*/';
?>
var to;
var returnValue='';
$(document).ready(function(){
	to=setInterval("checkFreeShipping()", 500);
});

checkFreeShipping = function(){
	if ($('#tr_unit_price')!=null){
		clearInterval(to);
		showFreeShipping();
	}
	if ($('#image')!=null){
		clearInterval(to);
		if ($('input[name="submitAddmanufacturer"]').val())
			showFreeShippingManufacturer();
		if ($('input[name="submitAddsupplier"]').val())
			showFreeShippingSupplier();
		if ($('input[name="submitAddcategory"]').val())
			showFreeShippingCategory();
	}
}

showFreeShipping = function(){
	var trInsert = $('#tr_unit_price').next().next().next().next().next().next().next().next().next().next();
	var TR = $('<TR></TR>');
	$(TR).html('<?php echo $free_shipping->getContentProduct($id_product); ?>');
	$(TR).insertAfter($(trInsert));
	var HR = $('<TR></TR>');
	$(HR).html('<td style="padding-bottom:5px;" colspan="2"><hr style="width:100%;"></td>');
	$(HR).insertAfter($(TR));
	$(HR).clone().insertBefore($(TR));
	
	$('input[name="submitAddproduct"]').click(function(){
		return saveFreeShipping('submitAddproduct');
	});
	$('input[name="submitAddproductAndStay"]').click(function(){
		return saveFreeShipping('submitAddproductAndStay');
	});
	
	$('#txtHFDateFrom').datepicker({
		prevText:"",
		nextText:"",
		dateFormat:"yy-mm-dd"
	});
	
	$('#txtHFDateTo').datepicker({
		prevText:"",
		nextText:"",
		dateFormat:"yy-mm-dd"
	});
}

showFreeShippingManufacturer = function(){
	var divInsert = $('input[name="logo"]').parent();
	var DIV= $('<?php echo $free_shipping->getContentManufacturer($id_manufacturer); ?>');
	$(DIV).insertAfter($(divInsert));
	
	$('input[name="submitAddmanufacturer"]').click(function(){
		return saveFreeShippingManuSup('submitAddmanufacturer', 'saveManufacturer');
	});
}

showFreeShippingSupplier = function(){
	var divInsert = $('input[name="logo"]').parent();
	var DIV= $('<?php echo $free_shipping->getContentSupplier($id_supplier); ?>');
	$(DIV).insertAfter($(divInsert));
	
	$('input[name="submitAddsupplier"]').click(function(){
		return saveFreeShippingManuSup('submitAddsupplier', 'saveSupplier');
	});
}

showFreeShippingCategory = function(){
	var divInsert = $('input[name="image"]').parent();
	var DIV= $('<?php echo $free_shipping->getContentCategory($id_category); ?>');
	$(DIV).insertAfter($(divInsert));
	
	$('input[name="submitAddcategory"]').click(function(){
		return saveFreeShippingCategory('submitAddcategory', 'saveCategory');
	});
	$('input[name="submitAddcategoryAndBackToParent"]').click(function(){
		return saveFreeShippingCategory('submitAddcategoryAndBackToParent', 'saveCategory');
	});
}

saveFreeShipping = function (btn){
	if (returnValue=='1')
		return true;
	var params;
	params='{"data": {"fixed": [';
	var inputs;
	inputs=$('input[rel="HFFS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_carrier" : "'+$(inputs[i]).attr('carrier')+'", "id_zone" : "'+$(inputs[i]).attr('zone')+'", "price" : "'+$(inputs[i]).val()+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += '], "free": [';
	
	inputs=$('input[rel="HFFRS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "active" : "'+($(inputs[i]).is(':checked') ? '-1' : '0')+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += ']}}';

	$.post(
		'../modules/hf_free_shipping_pro/ajax.php', 
		{
			'action': 'save',
			'id_product': '<?php echo intval($id_product); ?>',
			'datefrom': $('#txtHFDateFrom').val(),
			'dateto': $('#txtHFDateTo').val(),
			'data': params
		},
		function(rep) {
			returnValue='1';
			$('input[name="'+btn+'"]').trigger('click');
		}
	);
	return false;
}

saveFreeShippingManuSup = function (btn, action){
	if (returnValue=='1')
		return true;
	var params;
	params='{"data": {"amount": [';
	var inputs;
	inputs=$('input[rel="HFFS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "price" : "'+$(inputs[i]).val()+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += '], "free": [';
	
	inputs=$('input[rel="HFFRS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "active" : "'+($(inputs[i]).is(':checked') ? '-1' : '0')+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += ']}}';

	$.post(
		'../modules/hf_free_shipping_pro/ajax.php', 
		{
			'action': action,
			'id_manufacturer': '<?php echo intval($id_manufacturer); ?>',
			'id_supplier': '<?php echo intval($id_supplier); ?>',
			'data': params
		},
		function(rep) {
			returnValue='1';
			$('input[name="'+btn+'"]').trigger('click');
		}
	);
	return false;
}

saveFreeShippingCategory = function (btn, action){
	if (returnValue=='1')
		return true;
	var params;
	params='{"data": {"amount": [';
	var inputs;
	inputs=$('input[rel="HFFS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "price" : "'+$(inputs[i]).val()+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += '], "free": [';
	
	inputs=$('input[rel="HFFRS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "active" : "'+($(inputs[i]).is(':checked') ? '-1' : '0')+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += '], "fixed": [';
	
	inputs=$('input[rel="HFFFS"]');
	for(i=0;i<inputs.length;i++){
		params += '{"id_zone" : "'+$(inputs[i]).attr('zone')+'", "id_carrier" : "'+$(inputs[i]).attr('carrier')+'", "price" : "'+$(inputs[i]).val()+'"}, ';
	}
	if (i>0)
		params = params.substr(0, params.length-2);
	params += ']}}';

	$.post(
		'../modules/hf_free_shipping_pro/ajax.php', 
		{
			'action': action,
			'id_category': '<?php echo intval($id_category); ?>',
			'data': params
		},
		function(rep) {
			returnValue='1';
			$('input[name="'+btn+'"]').trigger('click');
		}
	);
	return false;
}
