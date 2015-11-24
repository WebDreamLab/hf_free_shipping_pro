<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;
if (class_exists('hf_free_shipping_pro')==false) {
class hf_free_shipping_pro extends Module
{
	public function __construct()
	{
		$this->name = 'hf_free_shipping_pro';
		$this->tab = 'front_office_features';
		$this->version = 1.0;
		$this->author = 'hfModules';
		$this->module_key = '57c723af140befd0249df622dbf6d2f9';

		parent::__construct();

		$this->displayName = $this->l('Free, fixed or per amount shipping cost for products');
		$this->description = $this->l('Adds capability for select a free shipping or a fixed shipping cost per quantity.');

	}


	public function install()
	{
		if (!parent::install())
			return false;
		$sql='create table `'._DB_PREFIX_.'hf_free_shipping_pro` (
			  `id_free_shipping` int(11) NOT NULL AUTO_INCREMENT,
			  `id_product` int(11) NOT NULL,
			  `free` tinyint(1) NOT NULL,
			  `fixed` tinyint(1) NOT NULL,
			  `date_from` date NOT NULL,
			  `date_to` date DEFAULT NULL,
			  PRIMARY KEY (`id_free_shipping`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;';
			
		$sql2='create table `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` (
			  `id_free_shipping` int(11) NOT NULL,
			  `id_carrier` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id_free_shipping`,`id_carrier`,`id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
			
		$sql3='create table `'._DB_PREFIX_.'hf_free_shipping_pro_free` (
			  `id_free_shipping` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id_free_shipping`,`id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
			
		$sql4='create table `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` (
			  `id_manufacturer` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  `free` tinyint(1) NOT NULL,
			  PRIMARY KEY (`id_manufacturer`,`id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
			
		$sql5='create table `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` (
			  `id_supplier` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  `free` tinyint(1) NOT NULL,
			  PRIMARY KEY (`id_supplier`,`id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
		
		$sql6='create table `'._DB_PREFIX_.'hf_free_shipping_pro_category` (
			  `id_category` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  `free` tinyint(1) NOT NULL,
			  PRIMARY KEY (`id_category`,`id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
			
		$sql7='create table `'._DB_PREFIX_.'hf_free_shipping_pro_category_fixed` (
			  `id_category` int(11) NOT NULL,
			  `id_carrier` int(11) NOT NULL,
			  `id_zone` int(11) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id_category`,`id_carrier`, `id_zone`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
			
		$sql8='CREATE TABLE `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` (
			  `id_carrier` int(11) NOT NULL,
			  `show_not_free` tinyint(1) NOT NULL,
			  `show_free` tinyint(1) NOT NULL,
			  PRIMARY KEY (`id_carrier`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
		
		if (!Db::getInstance()->Execute($sql) OR
			!Db::getInstance()->Execute($sql2) OR
			!Db::getInstance()->Execute($sql3) OR
			!Db::getInstance()->Execute($sql4) OR
			!Db::getInstance()->Execute($sql5) OR
			!Db::getInstance()->Execute($sql6) OR
			!Db::getInstance()->Execute($sql7) OR
			!Db::getInstance()->Execute($sql8) OR
			!Db::getInstance()->Execute('insert into `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` select id_carrier, -1, -1 from  `'._DB_PREFIX_.'carrier` where `deleted`=0 and `active`=1') OR
			!$this->registerHook('backOfficeFooter') OR
			!$this->registerHook('header') OR
			!$this->registerHook('extraLeft') OR
			!$this->registerHook('shoppingCart') OR
			!copy(str_replace('hf_free_shipping_pro.php', '', __FILE__).'/classes/Cart.php', '../override/classes/Cart.php') OR 
			!copy(str_replace('hf_free_shipping_pro.php', '', __FILE__).'/classes/Carrier.php', '../override/classes/Carrier.php')){
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_fixed;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_free;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_manufacturer;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_supplier;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_category;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_category_fixed;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_carriers;';
			Db::getInstance()->Execute($sql);
			return false;
		}else{
			return true;
		}
	}
	
	function uninstall()
		{
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_fixed;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_free;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_manufacturer;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_supplier;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_category;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_category_fixed;';
			Db::getInstance()->Execute($sql);
			$sql='drop table '._DB_PREFIX_.'hf_free_shipping_pro_carriers;';
			Db::getInstance()->Execute($sql);
			unlink('../override/classes/Cart.php');
			unlink('../override/classes/Carrier.php');
			return (parent::uninstall());
		}
		
	public function getContent(){
		global $protocol_content;
		
		$this->postProcess();
		
		$output='';
		$carriers = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'carrier` where active=1 and deleted=0 and id_carrier>0 order by name');
		$irow=0;
		$output.='
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
			<fieldset><legend>'.$this->l('Free Shipping Pro').'</legend>
				<label>'.$this->l('Available Carriers').'</label>
				<div class="margin-form">
					<table cellspacing="0" cellapadding="0" border="0" class="table">
						<tr>
							<th>'.$this->l('Carriers/show').'</th>
							<th>'.$this->l('Show not free shipping').'</th>
							<th>'.$this->l('Show free shipping').'</th>
						</tr>
						';
		foreach($carriers AS $carrier){
			$carrier_prop = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` where `id_carrier`='.intval($carrier['id_carrier']));
			$output.= '
						<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
							<td>'.$carrier['name'].'</td>
							<td><input type="checkbox" name="HFShipping_NF_'.$carrier['id_carrier'].'" value="'.$carrier['id_carrier'].'" '.($carrier_prop['show_not_free'] ? 'checked' : '').'></td>
							<td><input type="checkbox" name="HFShipping_F_'.$carrier['id_carrier'].'" value="'.$carrier['id_carrier'].'" '.($carrier_prop['show_free'] ? 'checked' : '').'></td>
						</tr>';
		}
		$output.='
					</table>
					<br><br>
					<div class="hint clear" style="display: block;width: 70%;">'.$this->l('Select carriers that will appear when the total order shipping cost are free. If no one selected, all available carriers will appear, as default.').'</div>
				</div>
				<br class="clear"/>
				<br/>
				<input class="button" type="submit" name="submitHFFSP" value="'.$this->l('validate').'" style="margin-left: 200px;"/>
			</fieldset>
		</form>';
		
		return $output;
	}
	
	private function postProcess(){
		if (Tools::isSubmit('submitHFFSP')){
			Db::getInstance()->execute('delete from `'._DB_PREFIX_.'hf_free_shipping_pro_carriers`');
			$carriers=',';
			foreach($_POST as $key=>$value){
				if (strpos($key, 'HFShipping_NF_')!==false){
					$carriers.=$value.',';
					Db::getInstance()->execute('insert into `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` values ('.intval($value).', -1, 0)');
				}
				if (strpos($key, 'HFShipping_F_')!==false){
					if (strpos($carriers, ','.$value.',')===false)
						Db::getInstance()->execute('insert into `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` values ('.intval($value).', 0, -1)');
					else
						Db::getInstance()->execute('update `'._DB_PREFIX_.'hf_free_shipping_pro_carriers` set `show_free`=-1 where `id_carrier`='.intval($value));
				}
			}
		}
	}

	public function getContentProduct($id_product){
	
		$zones = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'zone` where active=1 order by name');
		$carriers = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'carrier` where active=1 and deleted=0 and id_carrier>0 order by name');
		$free_shipping = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro` where id_product='.intval($id_product));
		
		$content='<td>\'+
				\''.$this->l('Free shipping or fixed shipping price per unit').'\'+
			\'</td>\'+
			\'<td>\'+
				\'<div style="margin-right:20px;font-weight:bold;">'.$this->l('Fixed shipping price').'</div>\'+
				\'<br>\'+
				\'<div id="divHFShipping" style="margin-top:10px;">\'+
					\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
						\'<tr>\'+
							\'<th>'.str_replace("'", "\'", $this->l('zones/carriers')).'</th>\'+';
		
		foreach($carriers AS $carrier)
			$content.='
							\'<th>'.$carrier['name'].'</th>\'+';
		
		$content.='
						\'</tr>\'+';
		
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';
			
			foreach($carriers AS $carrier){
				$content.='\'<td><input type="text" name="HFShipping_'.$carrier['id_carrier'].'_'.$zone['id_zone'].'" value="'.$this->getShippingPrice($free_shipping['id_free_shipping'], $carrier['id_carrier'], $zone['id_zone']).'" size="7" rel="HFFS" carrier="'.$carrier['id_carrier'].'" zone="'.$zone['id_zone'].'"></td>\'+';
			}
			
			$content.='
						\'</tr>\'+';
		}
		
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('You can set the free shipping within the next table. You must check tghe zones where the free shipping are active.')).'</div>\'+
				\'</div>\'+
				\'<br><br>\'+
				\'<div style="margin-right:20px;font-weight:bold;">'.$this->l('Free Shipping').'</div>\'+
				\'<br>\'+
				\'<div style="display:inline-block;margin-right:20px;">'.$this->l('From').' <input type="text" name="txtHFDateFrom" id="txtHFDateFrom" value="'.$free_shipping['date_from'].'"></div>\'+
				\'<div style="display:inline-block;margin-right:20px;">'.$this->l('To').' <input type="text" name="txtHFDateTo" id="txtHFDateTo" value="'.$free_shipping['date_to'].'"></div>\'+
				\'<br>\'+
				\'<div id="divHFFreeShipping" style="margin-top:10px;">\'+
					\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
						\'<tr>\'+
							\'<th>'.str_replace("'", "\'", $this->l('zones')).'</th>\'+
							\'<th>'.str_replace("'", "\'", $this->l('active')).'</th>\'+
						\'</tr>\'+';
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';	
			$content.='\'<td><input type="checkbox" name="HFShipping_'.$zone['id_zone'].'" value="1" '.($this->getFreeShippingZone($free_shipping['id_free_shipping'], $zone['id_zone']) ? 'checked' : '').' rel="HFFRS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='
						\'</tr>\'+';
		}
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('You can set the shipping price within the next table. You must set the price for every carrier and zone.')).'</div>\'+
				\'</div>\'+
			\'</td>';
		
		return $content;
	}
	
	public function getContentManufacturer($id_manufacturer){
		
		$zones = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'zone` where active=1 order by name');
		$content='<label>\'+
				\''.$this->l('Free shipping').'\'+
			\'</label>\'+
			\'<div class="margin-form">\'+
				\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
					\'<tr>\'+
						\'<th>'.str_replace("'", "\'", $this->l('zones/free shipping')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('by min. amount')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('always')).'</th>\'+
					\'</tr>\'+';
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';	
			$content.='\'<td><input type="text" name="HFShipping_'.$zone['id_zone'].'" value="'.$this->getShippingPriceManufacturer($id_manufacturer, $zone['id_zone']).'" size="7" rel="HFFS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='\'<td><input type="checkbox" name="HFRShipping_'.$zone['id_zone'].'" value="1" '.($this->getFreeShippingZoneManufacturer($id_manufacturer, $zone['id_zone']) ? 'checked' : '').' rel="HFFRS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='
						\'</tr>\'+';
		}
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('Within the next table, you can specify free shipping for all products of this manufacturer and the zone selected, or set an amount for every zone.')).'</div>\'+
				\'</div>';
		
		return $content;
	}
	
	public function getContentSupplier($id_supplier){
		
		$zones = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'zone` where active=1 order by name');
		$content='<label>\'+
				\''.$this->l('Free shipping').'\'+
			\'</label>\'+
			\'<div class="margin-form">\'+
				\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
					\'<tr>\'+
						\'<th>'.str_replace("'", "\'", $this->l('zones/free shipping')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('by min. amount')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('always')).'</th>\'+
					\'</tr>\'+';
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';	
			$content.='\'<td><input type="text" name="HFShipping_'.$zone['id_zone'].'" value="'.$this->getShippingPriceSupplier($id_supplier, $zone['id_zone']).'" size="7" rel="HFFS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='\'<td><input type="checkbox" name="HFRShipping_'.$zone['id_zone'].'" value="1" '.($this->getFreeShippingZoneSupplier($id_supplier, $zone['id_zone']) ? 'checked' : '').' rel="HFFRS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='
						\'</tr>\'+';
		}
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('Within the next table, you can specify free shipping for all products of this manufacturer and the zone selected, or set an amount for every zone.')).'</div>\'+
				\'</div>';
		
		return $content;
	}
	
	public function getContentCategory($id_category){
		
		$zones = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'zone` where active=1 order by name');
		$carriers = Db::getInstance()->ExecuteS('select * from `'._DB_PREFIX_.'carrier` where active=1 and deleted=0 and id_carrier>0 order by name');
		$content='<label>\'+
				\''.$this->l('Fixed shipping').'\'+
			\'</label>\'+
			\'<div class="margin-form">\'+
				\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
						\'<tr>\'+
							\'<th>'.str_replace("'", "\'", $this->l('zones/carriers')).'</th>\'+';
		
		foreach($carriers AS $carrier)
			$content.='
							\'<th>'.$carrier['name'].'</th>\'+';
		
		$content.='
						\'</tr>\'+';
		
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';
			
			foreach($carriers AS $carrier){
				$content.='\'<td><input type="text" name="HFShipping_'.$carrier['id_carrier'].'_'.$zone['id_zone'].'" value="'.$this->getShippingFixedPriceCategory($id_category, $carrier['id_carrier'], $zone['id_zone']).'" size="7" rel="HFFFS" carrier="'.$carrier['id_carrier'].'" zone="'.$zone['id_zone'].'"></td>\'+';
			}
			
			$content.='
						\'</tr>\'+';
		}
		
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('Within this table you can set the shipping price per unit for all the products behind this category.')).'</div>\'+
			\'</div>\'+
			\'<label>\'+
				\''.$this->l('Free shipping').'\'+
			\'</label>\'+
			\'<div class="margin-form">\'+
				\'<table cellspacing="0" cellapadding="0" border="0" class="table">\'+
					\'<tr>\'+
						\'<th>'.str_replace("'", "\'", $this->l('zones/free shipping')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('by min. amount')).'</th>\'+
						\'<th>'.str_replace("'", "\'", $this->l('always')).'</th>\'+
					\'</tr>\'+';
		$irow=0;
		foreach($zones AS $zone){
			$content.= '
						\'<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">\'+
							\'<td>'.$zone['name'].'</td>\'+';	
			$content.='\'<td><input type="text" name="HFShipping_'.$zone['id_zone'].'" value="'.$this->getShippingPriceCategory($id_category, $zone['id_zone']).'" size="7" rel="HFFS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='\'<td><input type="checkbox" name="HFRShipping_'.$zone['id_zone'].'" value="1" '.($this->getFreeShippingZoneCategory($id_category, $zone['id_zone']) ? 'checked' : '').' rel="HFFRS" zone="'.$zone['id_zone'].'"></td>\'+';
			$content.='
						\'</tr>\'+';
		}
		$content.='			
					\'</table>\'+
					\'<div class="hint clear" style="display: block;width: 70%;">'.str_replace("'", "\'", $this->l('Within the next table, you can specify free shipping for all products of this manufacturer and the zone selected, or set an amount for free shipping in every zone.')).'</div>\'+
				\'</div>';
		
		return $content;
	}
	
	private function getShippingPrice($id_free_shipping, $id_carrier, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` where id_free_shipping='.intval($id_free_shipping).' and id_zone='.intval($id_zone).' and id_carrier='.intval($id_carrier));
		
		if ($result)
			return $result['price'];
		else
			return '';
	}
	
	private function getShippingPriceManufacturer($id_manufacturer, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` where id_manufacturer='.intval($id_manufacturer).' and id_zone='.intval($id_zone));
		
		if ($result)
			return (intval($result['price'])==0 ? '' : $result['price']);
		else
			return '';
	}
	
	private function getShippingPriceSupplier($id_supplier, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` where id_supplier='.intval($id_supplier).' and id_zone='.intval($id_zone));
		
		if ($result)
			return (intval($result['price'])==0 ? '' : $result['price']);
		else
			return '';
	}
	
	private function getShippingPriceCategory($id_category, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_category` where id_category='.intval($id_category).' and id_zone='.intval($id_zone));
		
		if ($result)
			return (intval($result['price'])==0 ? '' : $result['price']);
		else
			return '';
	}
	
	private function getShippingFixedPriceCategory($id_category, $id_carrier, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_category_fixed` where id_category='.intval($id_category).' and id_zone='.intval($id_zone).' and id_carrier='.intval($id_carrier));
		
		if ($result)
			return $result['price'];
		else
			return '';
	}
	
	private function getFreeShippingZone($id_free_shipping, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_free` where id_free_shipping='.intval($id_free_shipping).' and id_zone='.intval($id_zone));
		
		if ($result)
			return true;
		else
			return false;
	}
	
	private function getFreeShippingZoneManufacturer($id_manufacturer, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` where id_manufacturer='.intval($id_manufacturer).' and id_zone='.intval($id_zone));
		
		if ($result){
			if ($result['free']==true)
				return true;
			else
				return false;
		}else
			return false;
	}
	
	private function getFreeShippingZoneSupplier($id_supplier, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` where id_supplier='.intval($id_supplier).' and id_zone='.intval($id_zone));
		
		if ($result){
			if ($result['free']==true)
				return true;
			else
				return false;
		}else
			return false;
	}
	
	private function getFreeShippingZoneCategory($id_category, $id_zone){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro_category` where id_category='.intval($id_category).' and id_zone='.intval($id_zone));
		
		if ($result){
			if ($result['free']==true)
				return true;
			else
				return false;
		}else
			return false;
	}
	
	public function delete($id_product){
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro` where `id_product`='.intval($id_product));
		
		if ($result){
			$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` where `id_free_shipping`='.intval($result['id_free_shipping']);
			Db::getInstance()->ExecuteS($sql);
			$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_free` where `id_free_shipping`='.intval($result['id_free_shipping']);
			Db::getInstance()->ExecuteS($sql);
			$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro` where `id_free_shipping`='.intval($result['id_free_shipping']);
			Db::getInstance()->ExecuteS($sql);
		}
	}
	
	public function save($id_product, $dateFrom, $dateTo, $data){
		//$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro` select ifnull(max(id_free_shipping), 0)+1, '.intval($id_product).', '.intval($freeShip).', '.intval($fixedShip).', \''.$dateFrom.'\', '.($dateTo=='' ? 'null' : '\''.$dateTo.'\'').' from `'._DB_PREFIX_.'hf_free_shipping_pro`';
		$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro` select ifnull(max(id_free_shipping), 0)+1, '.intval($id_product).', -1, -1, \''.$dateFrom.'\', '.($dateTo=='' ? 'null' : '\''.$dateTo.'\'').' from `'._DB_PREFIX_.'hf_free_shipping_pro`';
		Db::getInstance()->ExecuteS($sql);
		$result = Db::getInstance()->getRow('select * from `'._DB_PREFIX_.'hf_free_shipping_pro` where `id_product`='.intval($id_product));
		//if (intval($fixedShip)==-1)
		$this->saveData($result['id_free_shipping'], $data);
	}
	
	private function saveData($id_free_shipping, $data){
		$json=json_decode($data);
		foreach($json->data->fixed AS $value){
			if (intval($value->price)!=0){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_fixed` values ('.intval($id_free_shipping).', '.intval($value->id_carrier).', '.$value->id_zone.', '.str_replace(',', '', $value->price).')';
				Db::getInstance()->ExecuteS($sql);
			}
		}
		foreach($json->data->free AS $value){
			if (intval($value->active)==-1){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_free` values ('.intval($id_free_shipping).', '.intval($value->id_zone).', 0)';
				Db::getInstance()->ExecuteS($sql);
			}
		}
	}
	
	public function deleteManufacturer($id_manufacturer){
		$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` where `id_manufacturer`='.intval($id_manufacturer);
		Db::getInstance()->ExecuteS($sql);
	}
	
	public function saveManufacturer($id_manufacturer, $data){
		$json=json_decode($data);
		foreach($json->data->amount AS $value){
			if (intval($value->price)!=0){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` values ('.intval($id_manufacturer).', '.intval($value->id_zone).', '.str_replace(',', '', $value->price).', 0)';
				Db::getInstance()->ExecuteS($sql);
			}
		}
		foreach($json->data->free AS $value){
			if (intval($value->active)==-1){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` values ('.intval($id_manufacturer).', '.$value->id_zone.', 0, -1)';
				Db::getInstance()->ExecuteS($sql);
				$sql = 'update `'._DB_PREFIX_.'hf_free_shipping_pro_manufacturer` set `free`=-1, `price`=0 where `id_manufacturer`='.intval($id_manufacturer).' and `id_zone`='.intval($value->id_zone);
				Db::getInstance()->ExecuteS($sql);
			}
		}
	}
	
	public function deleteSupplier($id_supplier){
		$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` where `id_supplier`='.intval($id_supplier);
		Db::getInstance()->ExecuteS($sql);
	}
	
	public function saveSupplier($id_supplier, $data){
		$json=json_decode($data);
		foreach($json->data->amount AS $value){
			if (intval($value->price)!=0){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` values ('.intval($id_supplier).', '.intval($value->id_zone).', '.str_replace(',', '', $value->price).', 0)';
				Db::getInstance()->ExecuteS($sql);
			}
		}
		foreach($json->data->free AS $value){
			if (intval($value->active)==-1){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` values ('.intval($id_supplier).', '.$value->id_zone.', 0, -1)';
				Db::getInstance()->ExecuteS($sql);
				$sql = 'update `'._DB_PREFIX_.'hf_free_shipping_pro_supplier` set `free`=-1, `price`=0 where `id_supplier`='.intval($id_supplier).' and `id_zone`='.intval($value->id_zone);
				Db::getInstance()->ExecuteS($sql);
			}
		}
	}
	
	public function deleteCategory($id_category){
		$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_category` where `id_category`='.intval($id_category);
		Db::getInstance()->ExecuteS($sql);
		$sql = 'delete from `'._DB_PREFIX_.'hf_free_shipping_pro_category_fixed` where `id_category`='.intval($id_category);
		Db::getInstance()->ExecuteS($sql);
	}
	
	public function saveCategory($id_category, $data){
		$json=json_decode($data);
		foreach($json->data->amount AS $value){
			if (intval($value->price)!=0){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_category` values ('.intval($id_category).', '.intval($value->id_zone).', '.str_replace(',', '', $value->price).', 0)';
				Db::getInstance()->ExecuteS($sql);
			}
		}
		foreach($json->data->free AS $value){
			if (intval($value->active)==-1){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_category` values ('.intval($id_category).', '.$value->id_zone.', 0, -1)';
				Db::getInstance()->ExecuteS($sql);
				$sql = 'update `'._DB_PREFIX_.'hf_free_shipping_pro_category` set `free`=-1, `price`=0 where `id_category`='.intval($id_category).' and `id_zone`='.intval($value->id_zone);
				Db::getInstance()->ExecuteS($sql);
			}
		}
		foreach($json->data->fixed AS $value){
			if (intval($value->price)!=0){
				$sql = 'insert into `'._DB_PREFIX_.'hf_free_shipping_pro_category_fixed` values ('.intval($id_category).', '.intval($value->id_carrier).', '.$value->id_zone.', '.str_replace(',', '', $value->price).')';
				Db::getInstance()->ExecuteS($sql);
			}
		}
	}
	
	public function isFree($id_product, $isProductForm = false){
		global $smarty;
		global $cookie;
		global $cart;
		$categories=array();
		$suppliers=array();
		$manufacturers=array();
		
		//if ($cookie->isLogged()){
			$id_zone = Address::getZoneById((int)($cart->id_address_delivery));
			$id_carrier = $cookie->id_carrier;
			//if (intval($id_zone>0)){
				//product free or fixed
				$aux=Db::getInstance()->ExecuteS("
					select fr.`price` free, fx.`price` fixed
					from `"._DB_PREFIX_."hf_free_shipping_pro` fs
					left join `"._DB_PREFIX_."hf_free_shipping_pro_free` fr on fr.`id_free_shipping` = fs.`id_free_shipping`".($cookie->isLogged() && intval($id_zone>0) ? " and fr.`id_zone` = ".intval($id_zone) : "")." and fs.`date_from` <= curdate() and (fs.`date_to` >= curdate() or ifnull(fs.`date_to`, '')='')
					left join `"._DB_PREFIX_."hf_free_shipping_pro_fixed` fx on fx.`id_free_shipping` = fs.`id_free_shipping`".($cookie->isLogged() && intval($id_zone>0) ? " and fx.`id_zone` = ".intval($id_zone)." and fx.`id_carrier` = ".intval($id_carrier) : "")."
					where fs.`id_product`=".intval($id_product));
				/*$aux=Db::getInstance()->ExecuteS("
					select fr.`price` free
					from `"._DB_PREFIX_."hf_free_shipping_pro` fs
					left join `"._DB_PREFIX_."hf_free_shipping_pro_free` fr on fr.`id_free_shipping` = fs.`id_free_shipping`".($cookie->isLogged() && intval($id_zone>0) ? " and fr.`id_zone` = ".intval($id_zone) : "")." and fs.`date_from` <= curdate() and (fs.`date_to` >= curdate() or ifnull(fs.`date_to`, '')='')
					where fs.`id_product`=".intval($id_product));*/
				if (count($aux)>0){
					if ($cookie->isLogged()){
						if ($aux[0]['free']!=''){
							$smarty->assign('title_free', $this->l('Free Shipping'));
							return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
						}elseif($aux[0]['fixed']!='')
							return '';
					}else{
						for ($i=0;$i<count($aux);$i++){
							if ($aux[$i]['free']!=''){
								$smarty->assign('title_free', $this->l('See product description for conditions'));
								$smarty->assign('conditions', 'withConds');
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								continue count($aux);
							}//elseif($aux[$i]['fixed']!='')
							//	return '';
						}
					}
				}
				//cat free or fixed
				$cats = Product::getProductCategories($id_product);
				for ($i=0;$i<count($cats);$i++){
					$cat=new Category(intval($cats[$i]['id_category']));
					if (intval($cat->id_parent)>0){
						$parents=$cat->getParentsCategories();
					}else{
						$parents=array();
						//$parents[] = array();
						//$parents[0]['id_category']=$cats[$i]['id_category'];
					}
					for($k=0;$k<count($parents);$k++){
						$aux=Db::getInstance()->ExecuteS("
							select fr.`free` free, fr.`price`, fx.`price` fixed, cl.`name`
							from `"._DB_PREFIX_."category` c
							left join `"._DB_PREFIX_."category_lang` cl on cl.`id_category`=c.`id_category` and cl.`id_lang`=".intval($cookie->id_lang)."
							left join `"._DB_PREFIX_."hf_free_shipping_pro_category` fr on fr.`id_category` = c.`id_category`".($cookie->isLogged() && intval($id_zone>0) ? " and fr.`id_zone` = ".intval($id_zone) : "")."
							left join `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` fx on fx.`id_category` = c.`id_category`".($cookie->isLogged() && intval($id_zone>0) ? " and fx.`id_zone` = ".intval($id_zone)." and fx.`id_carrier` = ".intval($id_carrier) : "")."
							where c.`id_category`=".intval($parents[$k]['id_category']));
						if (count($aux)>0){
							if ($cookie->isLogged()){
								if (intval($aux[0]['free'])!=0){
									$smarty->assign('title_free', $this->l('Free Shipping'));
									return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								}elseif($aux[0]['fixed']!=''){
									return '';
								}elseif ($aux[0]['price']!=''){
									if ($isProductForm){
										$categories[$aux[0]['name']] = $aux[0]['price'];
									}else{
										$smarty->assign('title_free', $this->l('See product description for conditions'));
										$smarty->assign('conditions', 'withConds');
										return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
										continue count($cats);
									}
								}
							}else{
								for ($j=0;$j<count($aux);$j++){
									//if (intval($aux[$j]['free'])!=0 || (intval($aux[$j]['price'])!=0 && $aux[0]['price']=='')){
									if (intval($aux[$j]['free'])!=0 || intval($aux[$j]['price'])!=0){
										$smarty->assign('title_free', $this->l('See product description for conditions'));
										$smarty->assign('conditions', 'withConds');
										return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
										continue count($aux);
									}//elseif($aux[0]['fixed']!='')
									//	return '';
								}
							}
						}
					}
				}
				/*$cats = Product::getProductCategories($id_product);
				for ($i=0;$i<count($cats);$i++){
					/*$aux=Db::getInstance()->getRow("
						select fr.`price` free, fx.`price` fixed
						from `"._DB_PREFIX_."category` c
						left join `"._DB_PREFIX_."hf_free_shipping_pro_category` fr on fr.`id_category` = c.`id_category`".($cookie->isLogged() && intval($id_zone>0) ? " and fr.`id_zone` = ".intval($id_zone) : "")."
						left join `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` fx on fx.`id_category` = c.`id_category` and fx.`id_zone` = ".intval($id_zone)." and fx.`id_carrier` = ".intval($id_carrier)."
						where c.`id_category`=".intval($cats[$i]['id_category']));*/
					/*$aux=Db::getInstance()->ExecuteS("
						select fr.`free` free, fr.`price`, fx.`price` fixed, cl.`name`
						from `"._DB_PREFIX_."category` c
						left join `"._DB_PREFIX_."category_lang` cl on cl.`id_category`=c.`id_category` and cl.`id_lang`=".intval($cookie->id_lang)."
						left join `"._DB_PREFIX_."hf_free_shipping_pro_category` fr on fr.`id_category` = c.`id_category`".($cookie->isLogged() && intval($id_zone>0) ? " and fr.`id_zone` = ".intval($id_zone) : "")."
						left join `"._DB_PREFIX_."hf_free_shipping_pro_category_fixed` fx on fx.`id_category` = c.`id_category`".($cookie->isLogged() && intval($id_zone>0) ? " and fx.`id_zone` = ".intval($id_zone)." and fx.`id_carrier` = ".intval($id_carrier) : "")."
						where c.`id_category`=".intval($cats[$i]['id_category']));
					if (count($aux)>0){
						if ($cookie->isLogged()){
							if (intval($aux[0]['free'])!=0){
								$smarty->assign('title_free', $this->l('Free Shipping'));
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
							}elseif($aux[0]['fixed']!=''){
								return '';
							}elseif ($aux[0]['price']!=''){
								if ($isProductForm){
									$categories[$aux[0]['name']] = $aux[0]['price'];
								}else{
									$smarty->assign('title_free', $this->l('See product description for conditions'));
									return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
									continue count($cats);
								}
							}
						}else{
							for ($j=0;$j<count($aux);$j++){
								if (intval($aux[$j]['free'])!=0 || (intval($aux[$j]['price'])!=0 && $aux[0]['price']=='')){
									$smarty->assign('title_free', $this->l('See product description for conditions'));
									return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
									continue count($aux);
								}//elseif($aux[0]['fixed']!='')
								//	return '';
							}
						}
					}
					/*if ($aux){
						if ($aux['free']!=''){
							$smarty->assign('title_free', $this->l('Free Shipping'));
							return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
						}
						if ($aux['fixed']!='')
							return '';
					}*/
				/*}*/
				//manufacturer free
				/*$aux=Db::getInstance()->getRow("
					select m.`free` free, m.`price` fixed
					from `"._DB_PREFIX_."product` p
					inner join `"._DB_PREFIX_."hf_free_shipping_pro_manufacturer` m on m.`id_manufacturer` = p.`id_manufacturer` and m.`id_zone` = ".intval($id_zone)." and m.`free` = -1
					where p.`id_product`=".intval($id_product));*/
				$aux=Db::getInstance()->ExecuteS("
					select m.`free`, m.`price`, man.`name`
					from `"._DB_PREFIX_."product` p
					inner join `"._DB_PREFIX_."manufacturer` man on man.`id_manufacturer`=p.`id_manufacturer`
					inner join `"._DB_PREFIX_."hf_free_shipping_pro_manufacturer` m on m.`id_manufacturer` = p.`id_manufacturer`".($cookie->isLogged() && intval($id_zone>0) ? " and m.`id_zone` = ".intval($id_zone) : "")."
					where p.`id_product`=".intval($id_product));
				if (count($aux)>0){
					if ($cookie->isLogged()){
						if (intval($aux[0]['free'])!=0){
							$smarty->assign('title_free', $this->l('Free Shipping'));
							return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
						}elseif ($aux[0]['price']!=''){
							if ($isProductForm){
								$manufacturers[$aux[0]['name']] = $aux[0]['price'];
							}else{
								$smarty->assign('title_free', $this->l('See product description for conditions'));
								$smarty->assign('conditions', 'withConds');
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								continue count($aux);
							}
						}
					}else{
						for ($i=0;$i<count($aux);$i++){
							if ($aux[$i]['free']!='' || $aux[$i]['price']!=''){
								$smarty->assign('title_free', $this->l('See product description for conditions'));
								$smarty->assign('conditions', 'withConds');
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								continue count($aux);
							}
						}
					}
				}
				/*if ($aux){
					$smarty->assign('title_free', $this->l('Free Shipping'));
					return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
				}*/
				//supplier free
				/*$aux=Db::getInstance()->getRow("
					select m.`free` free, m.`price` fixed
					from `"._DB_PREFIX_."product` p
					inner join `"._DB_PREFIX_."hf_free_shipping_pro_supplier` m on m.`id_supplier` = p.`id_supplier` and m.`id_zone` = ".intval($id_zone)." and m.`free` = -1
					where p.`id_product`=".intval($id_product));*/
				$aux=Db::getInstance()->ExecuteS("
					select m.`free` free, m.`price`, sup.`name`
					from `"._DB_PREFIX_."product` p
					inner join `"._DB_PREFIX_."supplier` sup on sup.`id_supplier`=p.`id_supplier`
					inner join `"._DB_PREFIX_."hf_free_shipping_pro_supplier` m on m.`id_supplier` = p.`id_supplier`".($cookie->isLogged() && intval($id_zone>0) ? " and m.`id_zone` = ".intval($id_zone) : "")."
					where p.`id_product`=".intval($id_product));
				if (count($aux)>0){
					if ($cookie->isLogged()){
						if (intval($aux[0]['free'])!=0){
							$smarty->assign('title_free', $this->l('Free Shipping'));
							return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
						}elseif ($aux[0]['price']!=''){
							if ($isProductForm){
								$suppliers[$aux[0]['name']] = $aux[0]['price'];
							}else{
								$smarty->assign('title_free', $this->l('See product description for conditions'));
								$smarty->assign('conditions', 'withConds');
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								continue count($aux);
							}
						}
					}else{
						for ($i=0;$i<count($aux);$i++){
							if ($aux[$i]['free']!='' || $aux[$i]['price']!=''){
								$smarty->assign('title_free', $this->l('See product description for conditions'));
								$smarty->assign('conditions', 'withConds');
								return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
								continue count($aux);
							}
						}
					}
				}
				if ($isProductForm){
					return array(
						'cats' => $categories,
						'mans' => $manufacturers,
						'sups' => $suppliers
					);
				}else
					return '';
				/*if ($aux){
					$smarty->assign('title_free', $this->l('Free Shipping'));
					return ($this->display(__FILE__, 'hf_free_shipping_pro_list.tpl'));
				}*/
			//}
		//}
	}
	
	function hookBackOfficeFooter($params)
	{
		global $cookie;
		$iso = Db::getInstance()->getValue('SELECT iso_code FROM '._DB_PREFIX_.'lang WHERE `id_lang` = '.(int)($cookie->id_lang));
		if ((Tools::getValue('tab')=='AdminCatalog' && Tools::getValue('id_product')!='')||
		(Tools::getValue('tab')=='AdminManufacturers' && Tools::getValue('id_manufacturer')!='') ||
		(Tools::getValue('tab')=='AdminSuppliers' && Tools::getValue('id_supplier')!='')||
		(Tools::getValue('tab')=='AdminCatalog' && Tools::getValue('id_category')!='')){
			//echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery-ui-1.8.10.custom.min.js"></script>';
			echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>';
			if ($iso != 'en')
				echo '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/datepicker/ui/i18n/ui.datepicker-'.$iso.'.js"></script>';
			echo '<script language="javascript" type="text/javascript" src="'.($this->_path).'hf_free_shipping_pro.js.php?id_product='.intval(Tools::getValue('id_product')).'&id_category='.intval(Tools::getValue('id_category')).'&id_manufacturer='.intval(Tools::getValue('id_manufacturer')).'&id_supplier='.intval(Tools::getValue('id_supplier')).'&token='.intval(Tools::getValue('token')).'&ap='.dirname($_SERVER['PHP_SELF']).'&id_lang='.$cookie->id_lang.'"></script>';
		}
	}
	
	public function hookHeader(){
		Tools::addCSS(($this->_path).'css/hf_free_shipping_pro.css', 'all');
		Tools::addJS(($this->_path).'hf_free_shipping_pro.js');
	}
	
	public function hookExtraLeft(){
		global $smarty, $cookie;
		$vec=$this->isFree(intval(Tools::getValue('id_product')), true);
		if (count($vec)==3){
			if ((count($vec['cats'])>0 || count($vec['mans'])>0 || count($vec['sups'])>0)){
				$smarty->assign('categories', $vec['cats']);
				$smarty->assign('manufacturers', $vec['mans']);
				$smarty->assign('suppliers', $vec['sups']);
				$smarty->assign('conditions', 'withConds');
				return ($this->display(__FILE__, 'hf_free_shipping_pro_product.tpl'));
			}
		}elseif ($vec!=''){
			$smarty->assign('categories', array());
			$smarty->assign('manufacturers', array());
			$smarty->assign('suppliers', array());
			if (strpos($vec, 'withConds')!==false){
				$smarty->assign('conditions', 'withConds');
			}else
				$smarty->assign('conditions', '');
			//$smarty->assign('conditions', '');
			//return $vec;
			return ($this->display(__FILE__, 'hf_free_shipping_pro_product.tpl'));
		}
		/*if ($this->isFree(intval(Tools::getValue('id_product')))){
			return ($this->display(__FILE__, 'hf_free_shipping_pro_product.tpl'));
		}*/
	}
	
	public function hookShoppingCart(){
		global $cart, $cookie, $smarty;
		
		$return='';
		
		$aux=$cart->getShippingOrderTotalManuSupCat(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING, true);
		$mans=$aux[0];
		$sups=$aux[1];
		$cats=$aux[2];
		foreach ($mans as $key=>$value){
			$aux=Db::getInstance()->getRow("
					select `name`
					from `"._DB_PREFIX_."manufacturer`
					where `id_manufacturer`=".intval($key));
			$smarty->assign('name', $aux['name']);
			$smarty->assign('amount', $value);
			
			$return .= $this->display(__FILE__, 'hf_free_shipping_pro_shcman.tpl');
		}
		foreach ($sups as $key=>$value){
			$aux=Db::getInstance()->getRow("
					select `name`
					from `"._DB_PREFIX_."supplier`
					where `id_supplier`=".intval($key));
					
			$smarty->assign('name', $aux['name']);
			$smarty->assign('amount', $value);
			
			$return .= $this->display(__FILE__, 'hf_free_shipping_pro_shcsup.tpl');
		}
		foreach ($cats as $key=>$value){
			$aux=Db::getInstance()->getRow("
					select `name`
					from `"._DB_PREFIX_."category_lang`
					where `id_category`=".intval($key)." and `id_lang`=".intval($cookie->id_lang));
					
			$smarty->assign('name', $aux['name']);
			$smarty->assign('amount', $value);
			
			$return .= $this->display(__FILE__, 'hf_free_shipping_pro_shccat.tpl');
		}
		return $return;
		
	}
}
}
