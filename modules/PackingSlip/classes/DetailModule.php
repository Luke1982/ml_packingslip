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

	public function saveRecords($records = array()) {
		$this->master_id = $_REQUEST['currentid'];
		if (count($records) > 0) {
			foreach ($records as $record) {
				$this->processSingleRecord($record);
			}
		}
	}

	private function processSingleRecord($record) {
		if ($record['deleted'] == false) {
			if ($record['lineitem_id'] == '') {
				$this->saveNewRecord($record);
			} else {
				$this->updateExistingRecord($record);
			}
		} else if ($record['deleted'] == true && $record['lineitem_id'] != '') {
			// This was an existing line, but got deleted
		}
	}

	private function saveNewRecord($record) {
		global $current_user;
		require_once('modules/InventoryDetails/InventoryDetails.php');

		$invdet_focus = new InventoryDetails();
		$invdet_focus->column_fields = $record;
		$invdet_focus->column_fields['related_to'] = $this->master_id;

 		$handler = vtws_getModuleHandlerFromName('InventoryDetails', $current_user); 
 		$meta = $handler->getMeta();
 		$invdet_focus->column_fields = DataTransform::sanitizeRetrieveEntityInfo($invdet_focus->column_fields,$meta);
 		$invdet_focus->save('InventoryDetails');		
	}

	private function getWebserviceId($modulename) {
		$r = $this->db->pquery("SELECT id FROM vtiger_ws_entity WHERE name = ?", array($modulename));
		return $this->db->query_result($r, 0, 'id');
	}
}