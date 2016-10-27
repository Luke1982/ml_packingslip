<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
error_reporting(E_ALL);
ini_set("display_errors", "on");

global $adb;

if (isset($_REQUEST['getlist']) && $_REQUEST['getlist'] == true) {
	$list = array();
	$prod_res = $adb->pquery("SELECT vtiger_products.productid, vtiger_products.product_no, vtiger_products.productname, vtiger_products.mfr_part_no, vtiger_products.vendor_part_no, vtiger_products.unit_price, vtiger_crmentity.description FROM vtiger_products INNER JOIN vtiger_crmentity ON vtiger_products.productid = vtiger_crmentity.crmid", array());
	while ($prod = $adb->fetch_array($prod_res)) {
		$list[] = array(
			'label' => '<b>'.$prod['product_no'].'</b>: '.$prod['productname'].'<br>'.$prod['mfr_part_no'].'<br>'.$prod['vendor_part_no'],
			'value' => $prod['productname'],
			'price' => $prod['unit_price'],
			'desc' => $prod['description'],
			'crmid' => $prod['productid']
			);
	}
	$ser_res = $adb->pquery("SELECT vtiger_service.serviceid, vtiger_service.service_no, vtiger_service.servicename, vtiger_service.unit_price, vtiger_crmentity.description FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_service.serviceid = vtiger_crmentity.crmid", array());
	while ($ser = $adb->fetch_array($ser_res)) {
		$list[] = array(
			'label' => $ser['service_no'].': '.$ser['servicename'],
			'value' => $ser['servicename'],
			'price' => $ser['unit_price'],
			'desc' => $ser['description'],
			'crmid' => $ser['serviceid']
			);		
	}
	echo json_encode($list);
}
?>