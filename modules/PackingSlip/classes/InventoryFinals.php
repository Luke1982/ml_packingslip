<?php

require_once('include/fields/CurrencyField.php');

Class InventoryFinals {
	private $finals = array();
	private $db;
	private $record_grouptaxes = array();

	function __construct($db) {
		$this->db = $db;
	}

	public function get($entity) {
		$res = $this->db->pquery("SELECT * FROM $entity->table_name WHERE $entity->table_index = ?", array($entity->id));

		$record = $this->db->fetch_array($res);

		$finals['adjustment']		=	$record['adjustment'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['adjustment']);
		$finals['salescommission']	=	$record['salescommission'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['salescommission']);
		$finals['exciseduty']		=	$record['exciseduty'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['exciseduty']);
		$finals['subtotal']			=	$record['subtotal'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['subtotal']);
		$finals['total']			=	$record['total'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['total']);
		$finals['taxtype']			=	$record['taxtype'];
		$finals['grouptaxes']		=	$this->getGroupTaxes($record);
		$finals['discount_percent']	=	$record['discount_percent'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['discount_percent']);
		$finals['discount_amount']	=	$record['discount_amount'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['discount_amount']);
		$finals['discount_type']	=	$this->getDiscType($finals['discount_percent'], $finals['discount_amount']);
		$finals['total_discount']	=	$this->getFinalDiscount($finals['discount_percent'], $finals['discount_amount'], $finals['subtotal']);
		$finals['s_h_amount']		=	$record['s_h_amount'] == NULL ? 0 : CurrencyField::convertToUserFormat($record['s_h_amount']);
		$finals['currency_id']		=	$record['currency_id'];
		$finals['conversion_rate']	=	$record['conversion_rate'];

		return $finals;
	}

	private function getFinalDiscount($per, $am, $sub_total) {
		if ($per == 0 || $per == '0.000') {
			return $am;
		} else {
			return $sub_total * ($per / 100);
		}
	}

	private function getDiscType($per, $am) {
		if ($per > 0) {
			return 'p';
		} else {
			return 'd';
		}
	}

	private function getGroupTaxes($record) {
		$group_taxes = array();
		$total_tax_perc = 0;

		$res = $this->db->pquery("SELECT * FROM vtiger_inventorytaxinfo", array());
		while($tax = $this->db->fetch_array($res)) {
			$group_taxes[$tax['taxname']] = array(
					'taxid' 			=> $tax['taxid'],
					'taxname' 			=> $tax['taxname'],
					'taxlabel'			=> $tax['taxlabel'],
					'default_percentage'=> CurrencyField::convertToUserFormat($tax['percentage']),
					'deleted'			=> $tax['deleted'],
					'current_percentage'=> $this->getCurrentPercForTaxId($tax['taxid'], $record, 'grouptax')
				);
			if ($tax['deleted'] == 0) {
				$total_tax_percent += $tax['percentage'];
			}
		}
		$group_taxes['total_tax_percentage'] = CurrencyField::convertToUserFormat($total_tax_percent);
		return $group_taxes;		
	}

	/*
	 * Function that gets the current tax percentage for a
	 * record, based on the tax ID
	 * @param 1: the tax ID we're looking for
	 * @param 2: all the columnfields of the current record
	 * @param 3: the tax type, either 'grouptax' of 'shtax'
	 */
	private function getCurrentPercForTaxId($taxid = 0, $record = array(), $taxtype = '') {
		foreach ($record as $columnname => $value) {
			$searchkey = $taxtype.'_'.$taxid;
			if ($columnname == $searchkey) {
				return CurrencyField::convertToUserFormat($record[$searchkey]);
			}
		}
	}

}