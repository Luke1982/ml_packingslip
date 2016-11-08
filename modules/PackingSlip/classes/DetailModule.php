<?php

class DetailModule {

	private $db;
	private $fields = array();
	private $master_id = 0;

	function __construct($db) {
		$this->db = $db;
	}

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
		// $this->master_id = $_REQUEST['currentid'];
		if (count($records) > 0) {
			foreach ($records as $record) {
				$this->processSingleRecord($record);
			}
		}
	}

	private function processSingleRecord($record) {
		if ($record['deleted'] == 'false') {
			if ($record['lineitem_id'] == '') {
				$this->saveNewRecord($record);
			} else {
				$this->updateExistingRecord($record);
			}
		} else if ($record['deleted'] == 'true' && $record['lineitem_id'] != '') {
			// This was an existing line, but got deleted
		}
	}

	private function saveNewRecord($record) {
		global $current_user;
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->column_fields = $record;
		$invdet_focus->column_fields['related_to'] = $_REQUEST['currentid'];

 		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user); 
 		$meta = $handler->getMeta();
 		$invdet_focus->column_fields = DataTransform::sanitizeRetrieveEntityInfo($invdet_focus->column_fields,$meta);

		echo "<pre>";
		print_r($invdet_focus->column_fields);
		echo "</pre>";

 		$invdet_focus->save('InventoryDetails');		
	}

	private function updateExistingRecord($record) {
		global $current_user;
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->retrieve_entity_info($record['lineitem_id'], 'InventoryDetails');
		$invdet_focus->mode = 'edit';

		echo "<pre>";
		print_r($_REQUEST);
		echo "</pre>";
		// echo "<pre>";
		// print_r($invdet_focus->column_fields);
		// echo "</pre>";


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

		echo "<pre>";
		print_r($invdet_focus->column_fields);
		echo "</pre>";

		$invdet_focus->save('InventoryDetails');
	}

	private function getWebserviceId($modulename) {
		$r = $this->db->pquery("SELECT id FROM vtiger_ws_entity WHERE name = ?", array($modulename));
		return $this->db->query_result($r, 0, 'id');
	}
}