<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class PackingSlip extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'vtiger_packingslip';
	var $table_index= 'packingslipid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;
	var $HasDirectImageField = false;
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_packingslipcf', 'packingslipid');
	// Uncomment the line below to support custom field columns on related lists
	var $related_tables = Array('vtiger_packingslipcf'=>array('packingslipid','vtiger_packingslip', 'packingslipid'));

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_packingslip', 'vtiger_packingslipcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_packingslip'   => 'packingslipid',
		'vtiger_packingslipcf' => 'packingslipid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'Packing Slip Name'=> Array('packingslip' => 'packingslipname'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
		'Delivered' => Array('packingslip' => 'ps_deliver_date'),
		'Related Sales Order' => Array('salesorder' => 'salesorderid'),
		'Related Account' => Array('account' => 'accountid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Packing Slip Name'=> 'packingslipname',
		'Assigned To' => 'assigned_user_id',
		'Delivered' => 'ps_deliver_date',
		'Related Sales Order' => 'salesorderid',
		'Related Account' => 'accountid'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'packingslipname';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'Packing Slip Name'=> Array('packingslip' => 'packingslipname'),
		'Delivered' => Array('packingslip' => 'ps_deliver_date'),
		'Related Sales Order' => Array('salesorder' => 'salesorderid'),
		'Related Account' => Array('account' => 'accountid')		
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Packing Slip Name'=> 'packingslipname',
		'Delivered' => 'ps_deliver_date',
		'Related Sales Order' => 'salesorderid',
		'Related Account' => 'accountid'
	);

	// For Popup window record selection
	var $popup_fields = Array('packingslipname');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'packingslipname';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'packingslipname';

	// Required Information for enabling Import feature
	var $required_fields = Array('packingslipname'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'deliver_date';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'packingslipname');

	function __construct() {
		global $log;
		$this_module = get_class($this);
		$this->column_fields = getColumnFields($this_module);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
		$sql = 'SELECT 1 FROM vtiger_field WHERE uitype=69 and tabid = ?';
		$tabid = getTabid($this_module);
		$result = $this->db->pquery($sql, array($tabid));
		if ($result and $this->db->num_rows($result)==1) {
			$this->HasDirectImageField = true;
		}
	}

	function save_module($module) {
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id,$module);
		}
		//in ajax save we should not call this function, because this will delete all the existing product values
		if(isset($_REQUEST)) {
			if($_REQUEST['action'] != 'PackingSlipAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave')
			{
				//Based on the total Number of rows we will save the product relationship with this entity
				save_packingslip_inventory_product_details($this, 'PackingSlip');
			}
		}
		// Update the currency id and the conversion rate for the invoice
		$update_query = "update vtiger_packingslip set currency_id=?, conversion_rate=? where packingslipid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id); 
		$this->db->pquery($update_query, $update_params);		
	}

	function restore($module, $id) {
		global $current_user;
		$this->db->println("TRANS restore starts $module");
		$this->db->startTransaction();		
		$this->db->pquery('UPDATE vtiger_crmentity SET deleted=0 WHERE crmid = ?', array($id));
		//Restore related entities/records
		$this->restoreRelatedRecords($module,$id);
		$product_info = $this->db->pquery("SELECT productid, quantity, sequence_no, incrementondel from vtiger_inventoryproductrel WHERE id=?",array($id));
		$numrows = $this->db->num_rows($product_info);
		for($index = 0;$index < $numrows;$index++){
			$productid = $this->db->query_result($product_info,$index,'productid');
			$qty = $this->db->query_result($product_info,$index,'quantity');
			deductFromProductStock($productid,$qty);
		}
		
		$this->db->completeTransaction();
		$this->db->println("TRANS restore ends");
	}	

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord, $query='') {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $usewhere='') {
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0];
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			if(!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE vtiger_crmentity.deleted = 0 ".$usewhere;
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		global $current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN 
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role 
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid 
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid 
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					) 
					OR vtiger_crmentity.smownerid IN 
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per 
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					) 
					OR (";

					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " vtiger_groups.groupid IN 
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid 
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user;
		$thismodule = $_REQUEST['module'];

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name 
				FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		$rel_mods[$this->table_name] = 1;
		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			if($rel_mods[$other->table_name]) {
				$rel_mods[$other->table_name] = $rel_mods[$other->table_name] + 1;
				$alias = $other->table_name.$rel_mods[$other->table_name];
				$query_append = "as $alias";
			} else {
				$alias = $other->table_name;
				$query_append = '';
				$rel_mods[$other->table_name] = 1;
			}

			$query .= " LEFT JOIN $other->table_name $query_append ON $alias.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Initialize this instance for importing.
	 */
	function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php
	 */
	function create_import_query($module) {
		global $current_user;
		$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_users_last_import.assigned_user_id='$current_user->id'
			AND vtiger_users_last_import.bean_type='$module'
			AND vtiger_users_last_import.deleted=0";
		return $query;
	}

	/**
	 * Delete the last imported records.
	 */
	function undo_import($module, $user_id) {
		global $adb;
		$count = 0;
		$query1 = "select bean_id from vtiger_users_last_import where assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$result1 = $adb->pquery($query1, array($user_id)) or die("Error getting last import for undo: ".mysql_error());
		while ( $row1 = $adb->fetchByAssoc($result1))
		{
			$query2 = "update vtiger_crmentity set deleted=1 where crmid=?";
			$result2 = $adb->pquery($query2, array($row1['bean_id'])) or die("Error undoing last import: ".mysql_error());
			$count++;
		}
		return $count;
	}

	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	function set_import_assigned_user()
	{
		global $current_user, $adb;
		$record_user = $this->column_fields["assigned_user_id"];

		if($record_user != $current_user->id){
			$sqlresult = $adb->pquery("select id from vtiger_users where id = ? union select groupid as id from vtiger_groups where groupid = ?", array($record_user, $record_user));
			if($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields["assigned_user_id"] = $current_user->id;
			} else {
				$row = $adb->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields["assigned_user_id"] = $row['id'];
				} else {
					$this->column_fields["assigned_user_id"] = $current_user->id;
				}
			}
		}
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, vtiger_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = " WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}

		$query = $select_clause . $from_clause .
					" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
			$this->setModuleSeqNumber('configure', $modulename, $modulename.'-', '0000001');
			// Install an action link in salesorders
			include_once('vtlib/Vtiger/Module.php');
			$mod_so = Vtiger_Module::getInstance('SalesOrder');
			$mod_so->addLink('DETAILVIEWBASIC', 'LBL_CREATE_PACKINGSLIP_FROM_SO', 'index.php?module=PackingSlip&action=EditView&return_action=ListView&parent_so=$RECORD$');
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// Delete 'add packingslip' link in salesorders when PackingSlip module is deleted
			global $adb;
			$adb->pquery("DELETE FROM vtiger_links WHERE linklabel = ?", array('LBL_CREATE_PACKINGSLIP_FROM_SO'));
			// Delete module tables
			$adb->query("DROP TABLE vtiger_packingslip");
			$adb->query("DROP TABLE vtiger_packingslipcf");
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }
}

function get_packingslip_inventory_taxtype($id)
{
	global $log, $adb;
	$log->debug("Entering into function get_packingslip_inventory_taxtype($id).");
	$res = $adb->pquery("select taxtype from vtiger_packingslip where packingslipid=?", array($id));
	$taxtype = $adb->query_result($res,0,'taxtype');
	$log->debug("Exit from function get_packingslip_inventory_taxtype($id).");
	return $taxtype;
}

function get_packingslip_inventory_currency_info($id)
{
	global $log, $adb;
	$log->debug("Entering into function get_packingslip_inventory_currency_info($id).");
	$res = $adb->pquery("select currency_id, vtiger_packingslip.conversion_rate as conv_rate, vtiger_currency_info.* from vtiger_packingslip
						inner join vtiger_currency_info on vtiger_packingslip.currency_id = vtiger_currency_info.id
						where packingslipid=?", array($id));
	$currency_info = array();
	$currency_info['currency_id'] = $adb->query_result($res,0,'currency_id');
	$currency_info['conversion_rate'] = $adb->query_result($res,0,'conv_rate');
	$currency_info['currency_name'] = $adb->query_result($res,0,'currency_name');
	$currency_info['currency_code'] = $adb->query_result($res,0,'currency_code');
	$currency_info['currency_symbol'] = $adb->query_result($res,0,'currency_symbol');
	$log->debug("Exit from function get_packingslip_inventory_currency_info($id).");
	return $currency_info;
}
function save_packingslip_inventory_product_details($focus, $module, $update_prod_stock='false', $updateDemand='') {
	global $log, $adb;
	$id=$focus->id;
	$log->debug("Entering into function save_packingslip_inventory_product_details($module).");
	//Added to get the convertid
	if(isset($_REQUEST['convert_from']) && $_REQUEST['convert_from'] !='')
	{
		$id=$_REQUEST['return_id'];
	}
	else if(isset($_REQUEST['duplicate_from']) && $_REQUEST['duplicate_from'] !='')
	{
		$id=$_REQUEST['duplicate_from'];
	}
	$ext_prod_arr = Array();
	if($focus->mode == 'edit')
	{
		if($_REQUEST['taxtype'] == 'group')
			$all_available_taxes = getAllTaxes('available','','edit',$id);
			
		$return_old_values = 'return_old_values';
		delete_packingslip_inventory_product_details($focus);
	}
	else
	{
	if($_REQUEST['taxtype'] == 'group')
		$all_available_taxes = getAllTaxes('available','','edit',$id);
	}
	$tot_no_prod = $_REQUEST['totalProductCount'];
	//If the taxtype is group then retrieve all available taxes, else retrive associated taxes for each product inside loop
	$prod_seq=1;
	for($i=1; $i<=$tot_no_prod; $i++)
	{
		//if the product is deleted then we should avoid saving the deleted products
		if($_REQUEST["deleted".$i] == 1)
			continue;
	    $prod_id = $_REQUEST['hdnProductId'.$i];

		if(isset($_REQUEST['productDescription'.$i]))
			$description = $_REQUEST['productDescription'.$i];
    		$qty = $_REQUEST['qty'.$i];
     		$listprice = $_REQUEST['listPrice'.$i];
			$comment = $_REQUEST['comment'.$i];
	  		deductFromProductStock($prod_id,$qty);
			$query ="insert into vtiger_inventoryproductrel(id, productid, sequence_no, quantity, listprice, comment, description) values(?,?,?,?,?,?,?)";
			$qparams = array($focus->id,$prod_id,$prod_seq,$qty,$listprice,$comment,$description);
			$adb->pquery($query,$qparams);
			$sub_prod_str = $_REQUEST['subproduct_ids'.$i];

		if (!empty($sub_prod_str)) {
			$sub_prod = explode(":",$sub_prod_str);
			for($j=0;$j<count($sub_prod);$j++){
				$query ="insert into vtiger_inventorysubproductrel(id, sequence_no, productid) values(?,?,?)";
				$qparams = array($focus->id,$prod_seq,$sub_prod[$j]);
				$adb->pquery($query,$qparams);
			}
		}
		$prod_seq++;
		//we should update discount and tax details
		$updatequery = "update vtiger_inventoryproductrel set ";
		$updateparams = array();
		//set the discount percentage or discount amount in update query, then set the tax values
		if($_REQUEST['discount_type'.$i] == 'percentage')
		{
			$updatequery .= " discount_percent=?,";
			array_push($updateparams, $_REQUEST['discount_percentage'.$i]);
		}
		elseif($_REQUEST['discount_type'.$i] == 'amount')
		{
			$updatequery .= " discount_amount=?,";
			$discount_amount = $_REQUEST['discount_amount'.$i];
			array_push($updateparams, $discount_amount);
		}
		if($_REQUEST['taxtype'] == 'group')
		{
			for($tax_count=0;$tax_count<count($all_available_taxes);$tax_count++)
			{
				$tax_name = $all_available_taxes[$tax_count]['taxname'];
				$tax_val = $all_available_taxes[$tax_count]['percentage'];
				$request_tax_name = $tax_name."_group_percentage";
				if(isset($_REQUEST[$request_tax_name]))
					$tax_val =$_REQUEST[$request_tax_name];
				$updatequery .= " $tax_name = ?,";
				array_push($updateparams,$tax_val);
			}
				$updatequery = trim($updatequery,',')." where id=? and productid=?";
				array_push($updateparams,$focus->id,$prod_id);
		}
		else
		{
			$taxes_for_product = getTaxDetailsForProduct($prod_id,'all');
			for($tax_count=0;$tax_count<count($taxes_for_product);$tax_count++)
			{
				$tax_name = $taxes_for_product[$tax_count]['taxname'];
				$request_tax_name = $tax_name."_percentage".$i;
			
				$updatequery .= " $tax_name = ?,";
				array_push($updateparams, $_REQUEST[$request_tax_name]);
			}
				$updatequery = trim($updatequery,',')." where id=? and productid=?";
				array_push($updateparams, $focus->id,$prod_id);
		}
		// jens 2006/08/19 - protect against empy update queries
 		if( !preg_match( '/set\s+where/i', $updatequery)) {
 		    $adb->pquery($updatequery,$updateparams);
 		}
	}
	//we should update the netprice (subtotal), taxtype, group discount, S&H charge, S&H taxes, adjustment and total
	//netprice, group discount, taxtype, S&H amount, adjustment and total to entity table
	$updatequery  = " update $focus->table_name set ";
	$updateparams = array();
	$subtotal = $_REQUEST['subtotal'];
	$updatequery .= " subtotal=?,";
	array_push($updateparams, $subtotal);
	$updatequery .= " taxtype=?,";
	array_push($updateparams, $_REQUEST['taxtype']);
	//for discount percentage or discount amount
	if($_REQUEST['discount_type_final'] == 'percentage')
	{
		$updatequery .= " discount_percent=?,";
		array_push($updateparams, $_REQUEST['discount_percentage_final']);
	}
	elseif($_REQUEST['discount_type_final'] == 'amount')
	{
		$discount_amount_final = $_REQUEST['discount_amount_final'];
		$updatequery .= " discount_amount=?,";
		array_push($updateparams, $discount_amount_final);
	}
	
	$shipping_handling_charge = $_REQUEST['shipping_handling_charge'];
	$updatequery .= " s_h_amount=?,";
	array_push($updateparams, $shipping_handling_charge);
	//if the user gave - sign in adjustment then add with the value
	$adjustmentType = '';
	if($_REQUEST['adjustmentType'] == '-')
		$adjustmentType = $_REQUEST['adjustmentType'];
	$adjustment = $_REQUEST['adjustment'];
	$updatequery .= " adjustment=?,";
	array_push($updateparams, $adjustmentType.$adjustment);
	$total = $_REQUEST['total'];
	$updatequery .= " total=?";
	array_push($updateparams, $total);
	//$id_array = Array('PurchaseOrder'=>'purchaseorderid','SalesOrder'=>'salesorderid','Quotes'=>'quoteid','Invoice'=>'invoiceid');
	//Added where condition to which entity we want to update these values
	$updatequery .= " where ".$focus->table_index."=?";
	array_push($updateparams, $focus->id);
	$adb->pquery($updatequery,$updateparams);
	//to save the S&H tax details in vtiger_inventoryshippingrel table
	$sh_tax_details = getAllTaxes('all','sh');
	$sh_query_fields = "id,";
	$sh_query_values = "?,";
	$sh_query_params = array($focus->id);
	for($i=0;$i<count($sh_tax_details);$i++)
	{
		$tax_name = $sh_tax_details[$i]['taxname']."_sh_percent";
		if($_REQUEST[$tax_name] != '')
		{
			$sh_query_fields .= $sh_tax_details[$i]['taxname'].",";
			$sh_query_values .= "?,";
			array_push($sh_query_params, $_REQUEST[$tax_name]);
		}
	}
	$sh_query_fields = trim($sh_query_fields,',');
	$sh_query_values = trim($sh_query_values,',');
	$sh_query = "insert into vtiger_inventoryshippingrel($sh_query_fields) values($sh_query_values)";
	$adb->pquery($sh_query,$sh_query_params);
	$log->debug("Exit from function save_packingslip_inventory_product_details($module).");
}

function delete_packingslip_inventory_product_details($focus, $sql_del = true) {
	global $log, $adb,$updateInventoryProductRel_update_product_array;
	$log->debug("Entering into function delete_packingslip_inventory_product_details(".$focus->id.").");
	
	$product_info = $adb->pquery("SELECT productid, quantity, sequence_no, incrementondel from vtiger_inventoryproductrel WHERE id=?",array($focus->id));
	$numrows = $adb->num_rows($product_info);
	for($index = 0;$index <$numrows;$index++){
		$productid = $adb->query_result($product_info,$index,'productid');
		$sequence_no = $adb->query_result($product_info,$index,'sequence_no');
		$qty = $adb->query_result($product_info,$index,'quantity');
		
		addToProductStock($productid,$qty);
	}
	
	if ($sql_del)
	{
    $adb->pquery("delete from vtiger_inventoryproductrel where id=?", array($focus->id));
    $adb->pquery("delete from vtiger_inventorysubproductrel where id=?", array($focus->id));
    $adb->pquery("delete from vtiger_inventoryshippingrel where id=?", array($focus->id));
  }
  
	$log->debug("Exit from function delete_packingslip_inventory_product_details(".$focus->id.")");
}
?>
