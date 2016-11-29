<?php

class DetailModule {

	private $db;
	private $fields = array();

	function __construct($db) {
		$this->db = $db;
	}

	// To be deleted? Remember the call in EditView.php!
	public function getAvailableFields() {
		$q = "SELECT vtiger_field.columnname FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_field.tabid = vtiger_tab.tabid WHERE vtiger_field.tablename = ? AND vtiger_field.presence != ?";
		$p = array('vtiger_inventorydetails', 1);
		$r = $this->db->pquery($q, $p);

		while($field = $this->db->fetch_array($r)) {
			$fields[] = $field[0];
		}

		return $fields;
	}

	/*
	 * Gets a recordset from the InventoryDetails module table
	 * @param: CRM ID of the parent element
	 */
	public function getRecordSet($crm_id) {
		$lines = $this->db->pquery("SELECT vtiger_inventorydetails.inventorydetailsid AS inventorydetailsid,
											vtiger_inventorydetails.productid AS productid,
											vtiger_inventorydetails.sequence_no AS sequence_no,
											vtiger_inventorydetails.quantity AS quantity,
											vtiger_inventorydetails.listprice AS listprice,
											vtiger_inventorydetails.tax_percent AS tax_percent,
											vtiger_inventorydetails.extgross AS extgross,
											vtiger_inventorydetails.discount_percent AS discount_percent,
											vtiger_inventorydetails.discount_amount AS discount_amount,
											vtiger_inventorydetails.extnet AS extnet,
											vtiger_inventorydetails.linetax AS linetax,
											vtiger_inventorydetails.linetotal AS linetotal,
											vtiger_inventorydetails.units_delivered_received AS units_delivered_received,
											vtiger_inventorydetails.cost_price AS cost_price,
											vtiger_inventorydetails.cost_gross AS cost_gross,
											vtiger_crmentity.description AS description,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qty_per_unit ELSE '' END AS qty_per_unit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.usageunit ELSE '' END AS usageunit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.reorderlevel ELSE '' END AS reorderlevel,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyindemand ELSE '' END AS qtyindemand,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyinstock ELSE 'NA' END AS qtyinstock,
											CASE WHEN vtiger_products.productid != '' THEN 'Products' ELSE 'Services' END AS entitytype 
											FROM vtiger_inventorydetails LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventorydetails.productid 
											LEFT JOIN vtiger_crmentity ON vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
											LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventorydetails.productid 
											WHERE vtiger_inventorydetails.related_to = ? AND vtiger_crmentity.deleted = ?", array($crm_id, 0));
		return $lines;
	}

	/*
	 * Processes a set of productlines
	 */
	public function processRecords($records = array()) {
		global $currentModule;
		$save_currentModule = $currentModule;
		$currentModule = 'InventoryDetails';

		$master_id = $_REQUEST['currentid'] == '' ? $_REQUEST['record'] : $_REQUEST['currentid'];

		if (count($records) > 0) {
			foreach ($records as $record) {
				$this->processSingleRecord($record, $master_id);
			}
		}
		$currentModule = $save_currentModule;
	}

	/*
	 * Processes a single line/record. Method decides wether to
	 * save, update, delete or do nothing at all when a 
	 * line was only alive on screen but got deleted before
	 * ever being saved as InventoryDetails line
	 *
	 * @param 1: Array of line info. Array keys MUST correspond with InventoryDetails fieldnames
	 * @param 2: ID of the master module, in this case a PackingSlip
	 */
	private function processSingleRecord($record, $master_id) {
		if ($record['deleted'] == 'false') { // Do nothing with records that were only alive on screen but got deleted right away
			if ($record['lineitem_id'] == '') {
				// This is a new line
				$this->saveNewRecord($record, $master_id);
			} else {
				// This is an existing line that needs to be updated
				$this->updateExistingRecord($record);
			}
		} else if ($record['deleted'] == 'true' && $record['lineitem_id'] != '') {
			// This was an existing line, but got deleted
			$this->deleteRecord($record);
		}
	}

	private function saveNewRecord($record, $master_id) {
		global $current_user;
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->column_fields = $record;
		$invdet_focus->column_fields['related_to'] = $master_id;

 		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user); 
 		$meta = $handler->getMeta();
 		$invdet_focus->column_fields = DataTransform::sanitizeRetrieveEntityInfo($invdet_focus->column_fields,$meta);

 		$invdet_focus->save('InventoryDetails');
	}

	private function updateExistingRecord($record) {
		global $current_user;
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->retrieve_entity_info($record['lineitem_id'], 'InventoryDetails');
		$invdet_focus->id = $record['lineitem_id'];
		$invdet_focus->mode = 'edit';

		$invdet_focus->column_fields['account_id'] = $_REQUEST['ps_accountid'];
		$invdet_focus->column_fields['contact_id'] = $_REQUEST['ps_contactid'];
		$invdet_focus->column_fields['assigned_user_id'] = $_REQUEST['assigned_user_id'];

		// Overwrite column fields that have matching keys
		foreach ($record as $key => $value) {
			if (array_key_exists($key, $invdet_focus->column_fields)) {
				$invdet_focus->column_fields[$key] = $record[$key];
			}
		}

		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user);
		$meta = $handler->getMeta();
		$invdet_focus->column_fields = DataTransform::sanitizeRetrieveEntityInfo($invdet_focus->column_fields, $meta);

		$invdet_focus->save('InventoryDetails');
	}

	private function deleteRecord($record) {
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->retrieve_entity_info($record['lineitem_id'], 'InventoryDetails');
		$invdet_focus->id = $record['lineitem_id'];

		DeleteEntity('InventoryDetails', 'InventoryDetails', $invdet_focus, $invdet_focus->id, $invdet_focus->id);
	}
}