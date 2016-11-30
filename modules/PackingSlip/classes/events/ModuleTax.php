<?php

class ModuleTax extends VTEventHandler{

	private $table_name = 'vtiger_packingslip';

	public function handleEvent($name, $data){
		global $adb;

		$name = $data['tax_type'] == 'sh' ? 'shtax_'.$data['tax_id'] : 'grouptax_'.$data['tax_id'];
		$percentage = $data['tax_value'];

		$q = "ALTER TABLE $this->table_name ADD COLUMN $name DECIMAL(7,3) DEFAULT $percentage";
		$p = array();
		$adb->pquery($q, $p);
	}

}