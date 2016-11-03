<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
// error_reporting(E_ALL);
// ini_set("display_errors", "on");

global $adb;

$ven_part_no_lbl = getTranslatedString('Vendor PartNo','Products');
$mfr_part_no_lbl = getTranslatedString('Mfr PartNo','Products');

if (isset($_REQUEST['getlist']) && $_REQUEST['getlist'] == true) {
	$list = array();
	$prod_res = $adb->pquery("SELECT vtiger_products.productid, vtiger_products.cost_price, vtiger_products.product_no, vtiger_products.productname, vtiger_products.mfr_part_no, vtiger_products.vendor_part_no, vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_products.qtyindemand, vtiger_products.reorderlevel, vtiger_products.pack_size, vtiger_products.usageunit, vtiger_crmentity.description FROM vtiger_products INNER JOIN vtiger_crmentity ON vtiger_products.productid = vtiger_crmentity.crmid", array());
	while ($prod = $adb->fetch_array($prod_res)) {
		$list[] = array(
			'label' => '<span class="ac_productno">'.$prod['product_no'].': </span><span class="ac_productname">'.$prod['productname'].'</span><br><span class="ac_venno_tit">'.$ven_part_no_lbl.': </span><span class="ac_vendorno">'.$prod['vendor_part_no'].'</span><br><span class="ac_mfrno_tit">'.$mfr_part_no_lbl.': </span><span class="ac_mfrno">'.$prod['mfr_part_no'].'</span>',
			'value' 		=> $prod['productname'],
			'price' 		=> $prod['unit_price'],
			'desc' 			=> $prod['description'],
			'crmid' 		=> $prod['productid'],
			'inStock' 		=> $prod['qtyinstock'],
			'inDemand' 		=> $prod['qtyindemand'],
			'reOrderLvl'	=> $prod['reorderlevel'],
			'packSize'		=> $prod['packsize'] == NULL ? '1' : $prod['packsize'],
			'usageUnit'		=> getTranslatedString($prod['usageunit'], 'Products'),
			'costPrice'		=> $prod['cost_price'] == NULL ? '0' : $prod['cost_price'],
			'entityType'	=> 'Products'
			);
	}
	$ser_res = $adb->pquery("SELECT vtiger_service.serviceid, vtiger_service.service_no, vtiger_service.servicename, vtiger_service.unit_price, vtiger_crmentity.description FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_service.serviceid = vtiger_crmentity.crmid", array());
	while ($ser = $adb->fetch_array($ser_res)) {
		$list[] = array(
			'label' 		=> $ser['service_no'].': '.$ser['servicename'],
			'value' 		=> $ser['servicename'],
			'price' 		=> $ser['unit_price'],
			'desc' 			=> $ser['description'],
			'crmid' 		=> $ser['serviceid'],
			'inStock' 		=> '0',
			'inDemand' 		=> '0',
			'reOrderLvl'	=> '0',
			'packSize'		=> '0',
			'usageUnit'		=> $prod['usageunit'],			
			'costPrice'		=> '0',
			'entityType'	=> 'Services'
			);		
	}
	echo json_encode($list);
}
?>