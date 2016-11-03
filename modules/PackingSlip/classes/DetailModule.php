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
				$this->saveSingleRecord($record);
			}
		}
	}

	private function saveSingleRecord($record) {
		if ($record['lineitem_id'] == '') {
			$this->saveNewRecord($record);
		} else {
			$this->updateExistingRecord($record);
		}
	}

	private function saveNewRecord($record) {
		require_once('include/Webservices/Create.php');
		global $current_user;

		$enttype_wsid = $this->getWebserviceId($record['entity_type']);
		$acc_wsid = $this->getWebserviceId('Accounts');
		$con_wsid = $this->getWebserviceId('Contacts');
		$ps_wsid = $this->getWebserviceId('PackingSlip');

		$record['description'] = $record['comment'];
		$record['assigned_user_id'] = '19x'.$current_user->id;
		$record['productid'] = $enttype_wsid.'x'.$record['productid'];
		$record['account_id'] = $acc_wsid.'x'.$_REQUEST['ps_accountid'];
		$record['contact_id'] = $con_wsid.'x'.$_REQUEST['ps_contactid'];
		$record['related_to'] = $ps_wsid.'x'.$this->master_id;

		echo "<pre>";
		print_r($record);
		echo "</pre>";

		$created_detailrecord = vtws_create('InventoryDetails', $record, $current_user);

		echo "<pre>";
		print_r($created_detailrecord);
		echo "</pre>";		
	}

	private function getWebserviceId($modulename) {
		$r = $this->db->pquery("SELECT id FROM vtiger_ws_entity WHERE name = ?", array($modulename));
		return $this->db->query_result($r, 0, 'id');
	}
}