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
		// die();
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