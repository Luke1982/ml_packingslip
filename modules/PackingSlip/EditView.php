<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once ('Smarty_setup.php');
require_once ('data/Tracker.php');
require_once ('modules/Quotes/Quotes.php');
require_once ('modules/SalesOrder/SalesOrder.php');
require_once ('modules/Potentials/Potentials.php');
require_once ('include/CustomFieldUtil.php');
require_once ('include/utils/utils.php');

// error_reporting(E_ALL);
// ini_set("display_errors", "on"); 

global $app_strings, $mod_strings, $currentModule, $log, $current_user, $adb;

$focus = CRMEntity::getInstance($currentModule);
$smarty = new vtigerCRM_Smarty();
//added to fix the issue4600
$searchurl = getBasic_Advance_SearchURL();
$smarty->assign("SEARCH", $searchurl);
//4600 ends

$currencyid = fetchCurrency($current_user->id);
$rate_symbol = getCurrencySymbolandCRate($currencyid);
$rate = $rate_symbol['rate'];
if (isset ($_REQUEST['record']) && $_REQUEST['record'] != '') 
{
		if (isset ($_REQUEST['convertmode']) && $_REQUEST['convertmode'] == 'quotetoissuecard') {
				$quoteid = $_REQUEST['record'];
				$quote_focus = new Quotes();
				$quote_focus->id = $quoteid;
				$quote_focus->retrieve_entity_info($quoteid, "Quotes");
				
				$focus->column_fields['description'] = $quote_focus->column_fields['description'];
				$focus->column_fields['currency_id'] = $quote_focus->column_fields['currency_id'];
				$focus->column_fields['conversion_rate'] = $quote_focus->column_fields['conversion_rate'];
				
				// Reset the value w.r.t Quote Selected
				$currencyid = $quote_focus->column_fields['currency_id'];
				$rate = $quote_focus->column_fields['conversion_rate'];
		
				//Added to display the Quote's associated vtiger_products -- when we create vtiger_invoice from Quotes DetailView 
				$associated_prod = getAssociatedProducts("Quotes", $quote_focus);
				$txtTax = (($quote_focus->column_fields['txtTax'] != '') ? $quote_focus->column_fields['txtTax'] : '0.000');
				$txtAdj = (($quote_focus->column_fields['txtAdjustment'] != '') ? $quote_focus->column_fields['txtAdjustment'] : '0.000');
		
				$smarty->assign("CONVERT_MODE", vtlib_purify($_REQUEST['convertmode']));
				$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);
				$smarty->assign("MODE", $quote_focus->mode);
				$smarty->assign("AVAILABLE_PRODUCTS", 'true');
		}else{
				$focus->id = $_REQUEST['record'];
				$focus->mode = 'edit';
				$focus->retrieve_entity_info($_REQUEST['record'], "PackingSlip");
				$focus->name = $focus->column_fields['subject'];
		}
}

// Check if this PackingSlip was created from an SO
if (isset($_REQUEST['parent_so']) && $_REQUEST['parent_so'] != '') {
	$focus->column_fields['ps_salesorderid'] = $_REQUEST['parent_so'];

	// Get the account and address info
	$account_address_info = $adb->pquery("SELECT vtiger_salesorder.accountid, vtiger_soshipads.ship_city, vtiger_soshipads.ship_code, vtiger_soshipads.ship_street, vtiger_soshipads.ship_country FROM vtiger_salesorder INNER JOIN vtiger_soshipads ON vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid WHERE vtiger_salesorder.salesorderid=?", array($_REQUEST['parent_so']));
	$account_address_info = $adb->fetch_array($account_address_info);

	$focus->column_fields['ps_accountid'] = $account_address_info['accountid'];	
	$focus->column_fields['ps_city'] = $account_address_info['ship_city'];	
	$focus->column_fields['ps_postal_code'] = $account_address_info['ship_code'];	
	$focus->column_fields['ps_address'] = $account_address_info['ship_street'];	
	$focus->column_fields['ps_country'] = $account_address_info['ship_country'];

	// Get the inventory line info from the SO
	$so_focus = new SalesOrder();
	$so_focus->id = $_REQUEST['parent_so'];
	$so_focus->retrieve_entity_info($_REQUEST['parent_so'], "SalesOrder");

	$focus->column_fields['description'] = $so_focus->column_fields['description'];
	$focus->column_fields['currency_id'] = $so_focus->column_fields['currency_id'];
	$focus->column_fields['conversion_rate'] = $so_focus->column_fields['conversion_rate'];	

	$associated_prod = getAssociatedProducts("SalesOrder", $so_focus);
	$txtTax = (($so_focus->column_fields['txtTax'] != '') ? $so_focus->column_fields['txtTax'] : '0.000');
	$txtAdj = (($so_focus->column_fields['txtAdjustment'] != '') ? $so_focus->column_fields['txtAdjustment'] : '0.000');	

	$smarty->assign("CONVERT_MODE", vtlib_purify($_REQUEST['convertmode']));
	$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);
	$smarty->assign("MODE", $so_focus->mode);
	$smarty->assign("AVAILABLE_PRODUCTS", 'true');
}

if (isset ($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$smarty->assign("DUPLICATE_FROM", $focus->id);
	$ps_associated_prod = get_ps_ass_products($focus);
	$focus->id = '';
	$focus->mode = '';
}
if(empty($_REQUEST['record']) && $focus->mode != 'edit'){
	setObjectValuesFromRequest($focus);
}
/*
if (!empty ($_REQUEST['parent_id']) && !empty ($_REQUEST['return_module'])) {
	if ($_REQUEST['return_module'] == 'Services') {
		$focus->column_fields['product_id'] = $_REQUEST['parent_id'];
		$log->debug("Service Id from the request is " . $_REQUEST['parent_id']);
		$associated_prod = getAssociatedProducts("Services", $focus, $focus->column_fields['product_id']);
	for ($i=1; $i<=count($associated_prod);$i++) {
		$associated_prod_id = $associated_prod[$i]['hdnProductId'.$i];
		$associated_prod_prices = getPricesForProducts($currencyid,array($associated_prod_id),'Services');
		$associated_prod[$i]['listPrice'.$i] = $associated_prod_prices[$associated_prod_id];
	}
		$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);
		$smarty->assign("AVAILABLE_PRODUCTS", 'true');
	}
}
*/
global $theme;
$theme_path = 'themes/' . $theme . '/';
$image_path = $theme_path . 'images/';

$disp_view = getView($focus->mode);
$mode = $focus->mode;
if ($disp_view == 'edit_view')
	$smarty->assign("BLOCKS", getBlocks($currentModule, $disp_view, $mode, $focus->column_fields));
else {
	$bas_block = getBlocks($currentModule, $disp_view, $mode, $focus->column_fields, 'BAS');
	$adv_block = getBlocks($currentModule, $disp_view, $mode, $focus->column_fields, 'ADV');

	$blocks['basicTab'] = $bas_block;
	if (is_array($adv_block))
		$blocks['moreTab'] = $adv_block;

	$smarty->assign("BLOCKS", $blocks);
	$smarty->assign("BLOCKS_COUNT", count($blocks));
}

$smarty->assign("OP_MODE", $disp_view);

$smarty->assign("MODULE", $currentModule);
$smarty->assign("SINGLE_MOD", 'PackingSlip');

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$log->info("PackingSlip view");

if (isset ($focus->name))
	$smarty->assign("NAME", $focus->name);
else
	$smarty->assign("NAME", "");

if ($focus->mode == 'edit') {
	$smarty->assign("UPDATEINFO", updateInfo($focus->id));
	$associated_prod = get_ass_products_ps($focus);
	$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);
	$smarty->assign("MODE", $focus->mode);
}
elseif (isset ($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$associated_prod = $ps_associated_prod;
	$smarty->assign("AVAILABLE_PRODUCTS", 'true');
	$smarty->assign("MODE", $focus->mode);
}
elseif ((isset ($_REQUEST['product_id']) && $_REQUEST['product_id'] != '') || (isset ($_REQUEST['opportunity_id']) && $_REQUEST['opportunity_id'] != '')) {
	$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);
	$smarty->assign("AVAILABLE_PRODUCTS", count($associated_prod)>0 ? 'true' : 'false');
	$smarty->assign("MODE", $focus->mode);
}

if (isset ($cust_fld)) {
	$smarty->assign("CUSTOMFIELD", $cust_fld);
}

$smarty->assign("ASSOCIATEDPRODUCTS", $associated_prod);

if (isset ($_REQUEST['return_module']))
	$smarty->assign("RETURN_MODULE", vtlib_purify($_REQUEST['return_module']));
else
	$smarty->assign("RETURN_MODULE", "PackingSlip");
if (isset ($_REQUEST['return_action']))
	$smarty->assign("RETURN_ACTION", vtlib_purify($_REQUEST['return_action']));
else
	$smarty->assign("RETURN_ACTION", "index");
if (isset ($_REQUEST['return_id']))
	$smarty->assign("RETURN_ID", vtlib_purify($_REQUEST['return_id']));
if (isset ($_REQUEST['return_viewname']))
	$smarty->assign("RETURN_VIEWNAME", vtlib_purify($_REQUEST['return_viewname']));
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("PRINT_URL", "phprint.php?jt=" . session_id() . $GLOBALS['request_string']);
$smarty->assign("ID", $focus->id);

$smarty->assign("CALENDAR_LANG", $app_strings['LBL_JSCALENDAR_LANG']);
$smarty->assign("CALENDAR_DATEFORMAT", parse_calendardate($app_strings['NTC_DATE_FORMAT']));

//in create new PackingSlip, get all available product taxes and shipping & Handling taxes

if ($focus->mode != 'edit') {
	$tax_details = getAllTaxes('available');
	$sh_tax_details = getAllTaxes('available', 'sh');
} else {
	$tax_details = getAllTaxes('available', '', $focus->mode, $focus->id);
	$sh_tax_details = getAllTaxes('available', 'sh', 'edit', $focus->id);
}
$smarty->assign("GROUP_TAXES", $tax_details);
$smarty->assign("SH_TAXES", $sh_tax_details);

$tabid = getTabid("PackingSlip");
$validationData = getDBValidationData($focus->tab_name, $tabid);
$data = split_validationdataArray($validationData);
$category = getParentTab();
$smarty->assign("CATEGORY", $category);

$smarty->assign("VALIDATION_DATA_FIELDNAME", $data['fieldname']);
$smarty->assign("VALIDATION_DATA_FIELDDATATYPE", $data['datatype']);
$smarty->assign("VALIDATION_DATA_FIELDLABEL", $data['fieldlabel']);

// Module Sequence Numbering
$mod_seq_field = getModuleSequenceField($currentModule);
if ($focus->mode != 'edit' && $mod_seq_field != null) {
	$autostr = getTranslatedString('MSG_AUTO_GEN_ON_SAVE');
	$mod_seq_string = $adb->pquery("SELECT prefix, cur_id from vtiger_modentity_num where semodule = ? and active=1", array (
		$currentModule
	));
	$mod_seq_prefix = $adb->query_result($mod_seq_string, 0, 'prefix');
	$mod_seq_no = $adb->query_result($mod_seq_string, 0, 'cur_id');
	if ($adb->num_rows($mod_seq_string) == 0 || $focus->checkModuleSeqNumber($focus->table_name, $mod_seq_field['column'], $mod_seq_prefix . $mod_seq_no))
		echo '<br><font color="#FF0000"><b>' . getTranslatedString('LBL_DUPLICATE') . ' ' . getTranslatedString($mod_seq_field['label']) .
		' - ' . getTranslatedString('LBL_CLICK') . ' <a href="index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings&selmodule=' . $currentModule . '">' . getTranslatedString('LBL_HERE') . '</a> ' . getTranslatedString('LBL_TO_CONFIGURE') . ' ' . getTranslatedString($mod_seq_field['label']) . '</b></font>';
	else
		$smarty->assign("MOD_SEQ_ID", $autostr);
} else {
	$smarty->assign("MOD_SEQ_ID", $focus->column_fields[$mod_seq_field['name']]);
}
// END

$smarty->assign("CURRENCIES_LIST", getAllCurrencies());
if ($focus->mode == 'edit' || $_REQUEST['isDuplicate'] == 'true') {
	$inventory_cur_info = get_packingslip_inventory_currency_info($focus->id);
	$smarty->assign("INV_CURRENCY_ID", $inventory_cur_info['currency_id']);
} else {
	$smarty->assign("INV_CURRENCY_ID", $currencyid);
}


$check_button = Button_Check($module);
$smarty->assign("CHECK", $check_button);
$smarty->assign("DUPLICATE",vtlib_purify($_REQUEST['isDuplicate']));
//Get Service or Product by default when create
$smarty->assign('PRODUCT_OR_SERVICE', GlobalVariable::getVariable('product_service_default', 'Products', $currentModule, $current_user->id));
//Set taxt type group or individual by default when create
$smarty->assign('TAX_TYPE', GlobalVariable::getVariable('Tax_Type_Default', 'group', $currentModule, $current_user->id));

$smarty->assign('AVAILABLE_FIELDS', get_detailmodule_fields());

// $_REQUEST['return_action'] = 'test';
// $smarty->assign("RETURN_ACTION", vtlib_purify($_REQUEST['return_action']));

$smarty->assign('FINALS', get_inventory_finals($focus));

$smarty->assign("DECIMAL_SEP", $current_user->column_fields['currency_decimal_separator']);
$smarty->assign("GROUP_SEP", $current_user->column_fields['currency_grouping_separator']);
$smarty->assign("DECIMALS", $current_user->column_fields['no_of_currency_decimals']);

// echo "<pre>";
// print_r($associated_prod);
// echo "</pre>";

if ($focus->mode == 'edit') {
	$smarty->assign("AVAILABLE_PRODUCTS", 'true');
	$smarty->display("modules/PackingSlip/InventoryEditView.tpl");
} else {
	$smarty->display('modules/PackingSlip/InventoryCreateView.tpl');
}

function get_detailmodule_fields() {
	require_once('classes/DetailModule.php');
	global $adb;
	$detail_module = new DetailModule($adb);
	return $detail_module->getAvailableFields();
}

function get_ass_products_ps($focus) {
	require_once('classes/ProductCollection.php');
	global $adb;
	$product_coll = new ProductCollection($adb);
	return $product_coll->get($focus->id);
}

function get_inventory_finals($focus) {
	require_once('classes/InventoryFinals.php');
	global $adb;
	$inv_finals = new InventoryFinals($adb);
	return $inv_finals->get($focus);
}

?>