<?php

require_once('include/fields/CurrencyField.php');

Class ProductCollection {
	private $productProps = array();
	private $db;
	private $collectedProductLines = array();

	function __construct($db) {
		$this->db = $db;
	}

	public function get($crm_id) {
		// $product_coll = $this->db->pquery("SELECT * FROM vtiger_productinventoryrel WHERE id = ? ORDER BY", array($crm_id, 'sequence_no'));
		$product_coll = $this->db->pquery("SELECT CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.product_no ELSE vtiger_service.service_no END AS productcode, 
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.unit_price ELSE vtiger_service.unit_price END AS unit_price,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.mfr_part_no ELSE '' END AS mfr_part_no,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.vendor_part_no ELSE '' END AS vendor_part_no,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.cost_price ELSE '' END AS cost_price,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qty_per_unit ELSE '' END AS qty_per_unit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.usageunit ELSE '' END AS usageunit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.reorderlevel ELSE '' END AS reorderlevel,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyindemand ELSE '' END AS qtyindemand,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyinstock ELSE 'NA' END AS qtyinstock,
											CASE WHEN vtiger_products.productid != '' THEN 'Products' ELSE 'Services' END AS entitytype,
											vtiger_inventoryproductrel.* FROM 
											vtiger_inventoryproductrel LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid 
											LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid 
											WHERE id=? ORDER BY ?", array($crm_id, 'sequence_no'));

		if ($this->db->num_rows($product_coll) > 0) { // If no rows are there, records were saved on InventoryDetails module
			while ($product = $this->db->fetch_array($product_coll)) {
				$this->collectedProductLines[$product['sequence_no']] = $this->createProductLine($product);
			}
		} else if ($this->db->num_rows($product_coll) == 0) { // Get InventoryDetails lines
			$id_lines = $this->getIdRecordSet($crm_id);
			while ($product = $this->db->fetch_array($id_lines)) {
				$this->collectedProductLines[$product['sequence_no']] = $this->createProductLine($product);
			}
		}
		ksort($this->collectedProductLines);
		return array_values($this->collectedProductLines);
	}

	/*
	 * Gets a recordset from the InventoryDetails module table
	 * @param: CRM ID of the parent element
	 */
	private function getIdRecordSet($crm_id) {
		// TODO:
		// - Decide tax percent when id_tax_percent is set in getAllProductTaxes
		// - Use id_extgross if set in calcLineGrossTotal
		// - Use id_extnet if set in calcLineNetTotal
		$product_coll = $this->db->pquery("SELECT vtiger_inventorydetails.inventorydetailsid AS lineitem_id,
											vtiger_inventorydetails.productid AS productid,
											vtiger_inventorydetails.sequence_no AS sequence_no,
											vtiger_inventorydetails.quantity AS quantity,
											vtiger_inventorydetails.listprice AS listprice,
											vtiger_inventorydetails.tax_percent AS id_tax_percent,
											vtiger_inventorydetails.extgross AS id_extgross,
											vtiger_inventorydetails.discount_percent AS discount_percent,
											vtiger_inventorydetails.discount_amount AS discount_amount,
											vtiger_inventorydetails.extnet AS id_extnet,
											vtiger_inventorydetails.linetax AS id_linetax,
											vtiger_inventorydetails.linetotal AS id_linetotal,
											vtiger_inventorydetails.units_delivered_received AS units_delivered_received,
											vtiger_inventorydetails.cost_price AS cost_price,
											vtiger_inventorydetails.cost_gross AS cost_gross,
											vtiger_crmentity.description AS comment,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE '' END AS productname,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qty_per_unit ELSE '' END AS qty_per_unit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.usageunit ELSE '' END AS usageunit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.reorderlevel ELSE '' END AS reorderlevel,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyindemand ELSE '' END AS qtyindemand,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyinstock ELSE 'NA' END AS qtyinstock,
											CASE WHEN vtiger_products.productid != '' THEN 'Products' ELSE 'Services' END AS entitytype 
											FROM vtiger_inventorydetails LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventorydetails.productid 
											LEFT JOIN vtiger_crmentity ON vtiger_inventorydetails.inventorydetailsid = vtiger_crmentity.crmid 
											WHERE vtiger_inventorydetails.related_to = ?", array($crm_id));
		return $product_coll;
	}

	public function save($request) { // TO BE REMOVED!
		echo "<pre>";
		print_r($request['hdn_product']);
		echo "</pre>";
	}

	private function startEmptyProductLine() {
		$this->productProps = array(
			'crment_id'			=>		0,
			'line_id'			=>		0,
			'product_id'		=>		0,
			'seq'				=>		0,
			'qty'				=>		0,
			'list_price'		=>		0,
			'disc_perc'			=>		0,
			'disc_am'			=>		0,
			'comment'			=>		'',
			'desc'				=>		'',
			'taxes'				=>		array(),
			'tax_amount'		=>		0,
			'line_gross_total'	=>		0,
			'discount_type'		=>		'',
			'line_net_total'	=>		0,
			'product_name'		=>		'',
			'product_no'		=>		'',
			'entity_type'		=>		'',
			'stock_qty'			=>		'',
			'mfr_part_no'		=>		'',
			'vendor_part_no'	=>		'',
			'cost_price'		=>		0,
			'qty_per_unit'		=>		0,
			'usageunit'			=>		'',
			'reorderlevel'		=>		0,
			'qtyindemand'		=>		0,
			'units_del_rec'		=>		0
			);
	}

	private function createProductLine($product) {
		$this->startEmptyProductLine();

		$this->productProps['crment_id'] 			= $product['id'];
		$this->productProps['line_id'] 				= $product['lineitem_id'];
		$this->productProps['product_id'] 			= $product['productid'];
		$this->productProps['seq'] 					= $product['sequence_no'];
		$this->productProps['qty'] 					= $product['quantity'];
		$this->productProps['list_price'] 			= CurrencyField::convertToUserFormat($product['listprice']);
		$this->productProps['disc_perc'] 			= CurrencyField::convertToUserFormat($product['discount_percent']);
		$this->productProps['disc_am'] 				= CurrencyField::convertToUserFormat($product['discount_amount']);
		$this->productProps['comment'] 				= $product['comment'];
		$this->productProps['desc'] 				= $product['description'];
		$this->productProps['taxes'] 				= $this->getAllProductTaxes($product);
		$this->productProps['line_gross_total'] 	= $this->calcLineGrossTotal();
		$this->productProps['discount_type']		= $this->getDiscountType();
		$this->productProps['line_net_total'] 		= $this->calcLineNetTotal();
		$this->productProps['tax_amount']			= $this->getLineTaxAmount($this->productProps['taxes']);	
		$this->productProps['total_after_tax']		= $this->productProps['line_net_total'] + $this->productProps['tax_amount']; // TO-DO, rebuild for inventorydetails, has field for this	
		$this->productProps['product_name']			= $product['productname'];
		$this->productProps['product_no']			= $product['productcode'];
		$this->productProps['entity_type']			= $product['entitytype'];
		$this->productProps['stock_qty']			= $product['qtyinstock'];
		$this->productProps['mfr_part_no']			= $product['mfr_part_no'];
		$this->productProps['vendor_part_no']		= $product['vendor_part_no'];
		$this->productProps['cost_price']			= CurrencyField::convertToUserFormat($product['cost_price']);
		$this->productProps['qty_per_unit']			= $product['qty_per_unit'];
		$this->productProps['usageunit']			= getTranslatedString($product['usageunit'], 'Products');
		$this->productProps['reorderlevel']			= $product['reorderlevel'];
		$this->productProps['qtyindemand']			= $product['qtyindemand'];
		$this->productProps['units_del_rec']		= isset($product['units_delivered_received']) ? $product['units_delivered_received'] : 0;

		return $this->productProps;
	}

	private function calcLineGrossTotal() {
		return $this->productProps['qty'] * $this->productProps['list_price'];
	}

	private function calcLineNetTotal() {
		if ($this->productProps['discount_type'] == 'p') {
			$ret = $this->productProps['line_gross_total'] * (1 - ($this->productProps['disc_perc'] / 100));
			return $ret;
		} else {
			$ret = $this->productProps['line_gross_total'] - $this->productProps['disc_am'];
			return $ret;
		}
	}

	private function getDiscountType() {
		if ($this->productProps['disc_perc'] != NULL && $this->productProps['disc_perc'] != '') {
			return 'p';
		} else {
			return 'd';
		}
	}

	private function getLineTaxAmount($taxes) {
		$total_tax_percentage = 0;
		foreach ($taxes as $tax) {
			$total_tax_percentage = $total_tax_percentage + $tax['current_percentage'];
		}
		return ($this->productProps['line_net_total'] * ($total_tax_percentage / 100)) > 0 ? ($this->productProps['line_net_total'] * ($total_tax_percentage / 100)) : 0;
	}

	private function getAllProductTaxes($product) {
		$av_taxes = $this->getAvailableProductTaxes();
		foreach ($product as $key => $value) {
			if('tax' == substr($key,0,3)) {
				$av_taxes[$key]['current_percentage'] = ($value == "" ? 0 : $value);
			}
		}
		return $av_taxes;
	}

	private function getAvailableProductTaxes() {
		$av_taxes = array();
		$res = $this->db->pquery("SELECT * FROM vtiger_inventorytaxinfo", array());
		while($tax = $this->db->fetch_array($res)) {
			$av_taxes[$tax['taxname']] = array(
					'taxid' 			=> $tax['taxid'],
					'taxname' 			=> $tax['taxname'],
					'taxlabel'			=> $tax['taxlabel'],
					'default_percentage'=> $tax['percentage'],
					'deleted'			=> $tax['deleted']
				);
		}
		return $av_taxes;
	}
}