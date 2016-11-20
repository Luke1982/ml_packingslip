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
require_once('include/fields/CurrencyField.php');

$ven_part_no_lbl = getTranslatedString('Vendor PartNo','Products');
$mfr_part_no_lbl = getTranslatedString('Mfr PartNo','Products');

if (isset($_REQUEST['getlist']) && $_REQUEST['getlist'] == true) {
	$term = $_REQUEST['term'];
	$list = array();
	$prod_res = $adb->query("SELECT vtiger_products.productid, vtiger_products.cost_price, vtiger_products.product_no, vtiger_products.productname, vtiger_products.mfr_part_no, vtiger_products.vendor_part_no, vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_products.qtyindemand, vtiger_products.reorderlevel, vtiger_products.pack_size, vtiger_products.usageunit, vtiger_crmentity.description FROM vtiger_products INNER JOIN vtiger_crmentity ON vtiger_products.productid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1 AND vtiger_products.productname LIKE '%$term%' OR vtiger_products.mfr_part_no LIKE '%$term%' OR vtiger_products.vendor_part_no LIKE '%$term%'");
	while ($prod = $adb->fetch_array($prod_res)) {
		$list[] = array(
			'label' 		=> $prod['productname'],
			'value' 		=> $prod['productname'],
			'productno'		=> $prod['product_no'],
			'ven_part_lbl'	=> $ven_part_no_lbl,
			'ven_part_no'	=> $prod['vendor_part_no'] == NULL ? '' : $prod['vendor_part_no'],
			'mfr_part_lbl'	=> $mfr_part_no_lbl,
			'mfr_part_no'	=> $prod['mfr_part_no'] == NULL ? '' : $prod['mfr_part_no'],
			'price' 		=> CurrencyField::convertToUserFormat($prod['unit_price']),
			'desc' 			=> $prod['description'],
			'crmid' 		=> $prod['productid'],
			'taxes'			=> getProductTaxes($prod['productid']),
			'inStock' 		=> $prod['qtyinstock'],
			'inDemand' 		=> $prod['qtyindemand'],
			'reOrderLvl'	=> $prod['reorderlevel'],
			'packSize'		=> $prod['packsize'] == NULL ? '1' : $prod['packsize'],
			'usageUnit'		=> getTranslatedString($prod['usageunit'], 'Products'),
			'costPrice'		=> $prod['cost_price'] == NULL ? '0' : CurrencyField::convertToUserFormat($prod['cost_price']),
			'entityType'	=> 'Products'
			);
	}
	$ser_res = $adb->query("SELECT vtiger_service.serviceid, vtiger_service.service_no, vtiger_service.servicename, vtiger_service.unit_price, vtiger_crmentity.description FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_service.serviceid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted=0 AND vtiger_service.servicename LIKE '%$term%'");
	while ($ser = $adb->fetch_array($ser_res)) {
		$list[] = array(
			'label' 		=> $ser['servicename'],
			'value' 		=> $ser['servicename'],
			'productno'		=> $ser['service_no'],
			'ven_part_lbl'	=> '',
			'ven_part_no'	=> '',
			'mfr_part_lbl'	=> '',
			'mfr_part_no'	=> '',			
			'price' 		=> CurrencyField::convertToUserFormat($ser['unit_price']),
			'desc' 			=> $ser['description'],
			'crmid' 		=> $ser['serviceid'],
			'taxes'			=> getProductTaxes($ser['serviceid']),
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

function getProductTaxes($id) {
	global $adb;
	$taxes = array();

	$r = $adb->pquery("SELECT taxid, taxpercentage FROM vtiger_producttaxrel WHERE productid = ?", array($id));
	while ($tax = $adb->fetch_array($r)) {
		$tax['taxpercentage'] = CurrencyField::convertToUserFormat($tax['taxpercentage']);
		$taxes[] = $tax;
	}

	return $taxes;
}
?>