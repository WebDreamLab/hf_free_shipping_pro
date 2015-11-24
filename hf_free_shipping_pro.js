$(document).ready(function(){
	$('.ajax_block_product').each(function(){
		var obj;
		obj=this;
		var links;
		links=$(this).find('a');
		var idProduct;
		idProduct=0;
		for(i=0;i<links.length;i++){
			href=$(links[i]).attr('href');
			if (href.indexOf('id_product')>0){
				i=links.length;
				idProduct=href.substring(href.indexOf('id_product')+11, href.length)
				if (idProduct.indexOf('&')>0)
					idProduct=idProduct.substring(0, idProduct.indexOf('&'));
			}else{
				if(href.indexOf('.htm')>0 && href.indexOf('-')>0){
					idProduct=href.substring(href.lastIndexOf('/')+1, href.length)
					idProduct=idProduct.substring(0, idProduct.indexOf('-'));
				}
			}
		}
		if (idProduct!=0){
			$.post('modules/hf_free_shipping_pro/ajax.php', {
				action: 'isFree',
				id_product: idProduct
			}, function(data){
				if(data!=''){
					var FS=$(data);
					$(obj).prepend($(FS));
				}
			});
		}
	})
});