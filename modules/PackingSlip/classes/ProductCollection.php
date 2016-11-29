<?php

require_once('include/fields/CurrencyField.php');

Class ProductCollection {
	private $lineProps = array();
	private $db;
	private $collectedLines = array();

	function __construct($db) {
		$this->db = $db;
	}

	public function get($crm_id) {
		$line_coll = $this->db->pquery("SELECT CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.product_no ELSE vtiger_service.service_no END AS productcode, 
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.unit_price ELSE vtiger_service.unit_price END AS unit_price,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.mfr_part_no ELSE '' END AS mfr_part_no,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.vendor_part_no ELSE '' END AS vendor_part_no,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.cost_price ELSE '' END AS cost_price,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.quantity_per_unit ELSE '' END AS quantity_per_unit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.usageunit ELSE '' END AS usageunit,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.reorderlevel ELSE '' END AS reorderlevel,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyindemand ELSE '' END AS qtyindemand,
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyinstock ELSE 'NA' END AS qtyinstock,
											CASE WHEN vtiger_products.productid != '' THEN 'Products' ELSE 'Services' END AS entitytype,
											vtiger_inventoryproductrel.* FROM 
											vtiger_inventoryproductrel LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid 
											LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid 
											WHERE id=? ORDER BY ?", array($crm_id, 'sequence_no'));

		if ($this->db->num_rows($line_coll) > 0) { // If no rows are there, records were saved on InventoryDetails module

			while ($line = $this->db->fetch_array($line_coll)) {
				$this->collectedLines[$line['sequence_no']] = $this->createOldLine($line);
			}

		} else if ($this->db->num_rows($line_coll) == 0) { // Get InventoryDetails lines

			require_once('modules/PackingSlip/classes/DetailModule.php');

			$id = new DetailModule($this->db);
			$id_lines = $id->getRecordSet($crm_id);

			while ($line = $this->db->fetch_array($id_lines)) {
				$line['from_id_module'] = true;
				$this->collectedLines[$line['sequence_no']] = $this->createInventoryDetailsLine($line);
			}

		}

		ksort($this->collectedLines);
		return array_values($this->collectedLines);
	}

	private function startEmptyLine() {
		$this->lineProps = array(
			// Either the line id of the inventoryproductrel table or the inventorydetails CRM ID
			'lineitem_id'				=>		0,
			// CRM ID of either the product or service
			'productid'					=>		0,
			// The sequence as it is presented in the master module
			'sequence_no'				=>		0,
			// Quantity of either the product or service for this line
			'quantity'					=>		0,
			// Listprice for a single product or service
			'listprice'					=>		0,
			// Discount percentage for this line
			'discount_percent'			=>		0,
			// Nominal discount amount for this line
			'discount_amount'			=>		0,
			// Description for this line
			'description'				=>		'',
			// Array with taxes, only when getting old-style lines or when creating new master
			'taxes'						=>		array(),
			// Nominal tax amount for the entire line
			'linetax'					=>		0,
			// Tax percentage for the entire line
			'tax_percent'				=>		0,
			// Gross amount, so quantity * listprice
			'extgross'					=>		0,
			// Either 'p' or 'd' (percentage or direct)
			'discount_type'				=>		'',
			// Net total, so gross total minus discount
			'extnet'					=>		0,
			// The total of this line, after discount and tax
			'linetotal'					=>		0,
			// The product or service name
			'product_name'				=>		'',
			// The Product of service no.
			'product_no'				=>		'',
			// Either "Product" or "Service"
			'entity_type'				=>		'',
			// The current qty in stock
			'stock_quantity'			=>		'',
			// The cost price per unit
			'cost_price'				=>		0,
			// The cost gross, so costprice per unit * quantity of the line
			'cost_gross'				=>		0,
			// The quantity per unit
			'quantity_per_unit'			=>		0,
			// The unit name
			'usageunit'					=>		'',
			// The level at which should be re-ordered
			'reorderlevel'				=>		0,
			// The current level in demand
			'quantityindemand'			=>		0,
			// Units delivered / received
			'units_delivered_received'	=>		0,
			// The vendor CRM ID
			'vendor_id'					=>		0,
			// Array of raw values that are not converted to user format
			'raw'						=> 		array()
			);
	}

	/*
	 * Function that creates a line from the old 'inventoryproductrel' table
	 * @ param: array that represents a row from the 'inventoryproductrel' table
	 */
	private function createOldLine($line) {
		$this->startEmptyLine();

		$this->lineProps['lineitem_id']					= $line['lineitem_id'];
		$this->lineProps['productid'] 					= $line['productid'];
		$this->lineProps['sequence_no'] 				= $line['sequence_no'];
		$this->lineProps['quantity'] 					= CurrencyField::convertToUserFormat($line['quantity']);
		$this->lineProps['listprice'] 					= CurrencyField::convertToUserFormat($line['unit_price']);
		$this->lineProps['discount_percent'] 			= CurrencyField::convertToUserFormat($line['discount_percent']);
		$this->lineProps['discount_amount'] 			= CurrencyField::convertToUserFormat($line['discount_amount']);
		$this->lineProps['description'] 				= $line['comment'];
		$this->lineProps['taxes'] 						= $this->getAllProductTaxes($line); // Array
		$this->lineProps['extgross'] 					= CurrencyField::convertToUserFormat($this->calcLineGrossTotal($line['listprice'], $line['quantity']));
		$this->lineProps['discount_type']				= $this->getDiscountType();
		$this->lineProps['extnet'] 						= CurrencyField::convertToUserFormat($this->calcLineNetTotal($line));
		$this->lineProps['linetotal']					= CurrencyField::convertToUserFormat($this->lineProps['line_net_total'] + $this->lineProps['tax_amount']);
		$this->lineProps['linetax']						= CurrencyField::convertToUserFormat($this->getLineTaxAmount($this->lineProps['taxes']));	
		$this->lineProps['tax_percent']					= $this->getSumOfAllTaxPercentages($this->lineProps['taxes']);
		$this->lineProps['product_name']				= $line['productname'];
		$this->lineProps['product_no']					= $line['productcode'];
		$this->lineProps['entity_type']					= $line['entitytype'];
		$this->lineProps['stock_quantity']				= CurrencyField::convertToUserFormat($line['qtyinstock']);
		$this->lineProps['cost_price']					= CurrencyField::convertToUserFormat($line['cost_price']);
		$this->lineProps['quantity_per_unit']			= CurrencyField::convertToUserFormat($line['quantity_per_unit']);
		$this->lineProps['usageunit']					= getTranslatedString($line['usageunit'], 'Products');
		$this->lineProps['reorderlevel']				= $line['reorderlevel'];
		$this->lineProps['qtyindemand']					= CurrencyField::convertToUserFormat($line['qtyindemand']);
		$this->lineProps['units_delivered_received']	= 0;

		// Create a separate array with all the raw values (not user formatted)
		$this->lineProps['raw']							= $this->setRawValues($line);

		return $this->lineProps;
	}

	private function calcLineGrossTotal($list_price, $quantity) {
		return $quantity * $list_price;
	}

	private function calcLineNetTotal($line) {
		if ($this->lineProps['discount_type'] == 'p') {
			$ret = ($line['listprice'] * $line['quantity']) * (1 - ($line['discount_percent'] / 100));
			return $ret;
		} else {
			$ret = ($line['listprice'] * $line['quantity']) - $line['discount_amount'];
			return $ret;
		}
	}

	private function getDiscountType() {
		if ($this->lineProps['discount_percent'] != NULL && $this->lineProps['discount_percent'] != '' && $this->lineProps['discount_percent'] > 0) {
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
		return ($this->lineProps['extnet'] * ($total_tax_percentage / 100)) > 0 ? ($this->lineProps['extnet'] * ($total_tax_percentage / 100)) : 0;
	}

	/*
	 * Function that adds all tax percentages for this product.
	 * Only used when product is retrieved from old inventoryproductrel table.
	 * @param: Array with taxes created earlier in the "createProductLine" function.
	 */
	private function getSumOfAllTaxPercentages($taxes) {
		$tax_perc_sum = 0;
		foreach ($taxes as $tax) {
			if ($tax['deleted'] == 0) {
				$tax_perc_sum += $tax['current_percentage'];
			}
		}
	}

	private function getAllProductTaxes($line) {
		$av_taxes = $this->getAvailableProductTaxes();
		foreach ($line as $key => $value) {
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

	/*
	 * Function that creates an array of raw database values. These can be used
	 * in the frontend in hidden input fields, so that we don't have to transform
	 * back and forth to and from user format
	 */
	private function setRawValues($line) {
		$ret = array();

		$ret['quantity'] 					= $line['quantity'];
		$ret['listprice'] 					= $line['listprice'];
		$ret['discount_percent']			= $line['discount_percent'];
		$ret['discount_amount']				= $line['discount_amount'];
		$ret['taxes'] 						= $line['from_id_module'] ? array() : $this->getAllProductTaxes($line); // Array
		if ($line['from_id_module']) {
			$ret['extnet'] 					= $line['extnet'];
			$ret['extgross'] 				= $line['extgross'];
			$ret['units_delivered_received']= $line['units_delivered_received'];
			$ret['linetax']					= $line['linetax'];
			$ret['linetotal']				= $line['linetotal'];
			$ret['tax_percent']				= $line['tax_percent'];
		} else {
			$ret['extnet'] 					= $this->calcLineNetTotal($line);
			$ret['extgross']				= $this->calcLineGrossTotal($line['listprice'], $line['quantity']);
			$ret['units_delivered_received']= 0;
			$ret['linetax']					= $this->getLineTaxAmount($ret['taxes']);
			$ret['linetotal']				= $ret['extnet'] + $ret['linetax'];
			$ret['tax_percent']				= $this->getSumOfAllTaxPercentages($ret['taxes']);

		}	
		$ret['stock_quantity']				= $line['qtyinstock'];
		$ret['cost_price']					= $line['cost_price'];
		$ret['qtyindemand']					= $line['qtyindemand'];


		return $ret;
	}

	/*
	 * Function that creates a line from an array that represents an
	 * InventoryDetails record
	 */
	private function createInventoryDetailsLine($line) {
		$this->startEmptyLine();

		$this->lineProps['lineitem_id']					= $line['inventorydetailsid'];
		$this->lineProps['productid'] 					= $line['productid'];
		$this->lineProps['sequence_no'] 				= $line['sequence_no'];
		$this->lineProps['quantity'] 					= CurrencyField::convertToUserFormat($line['quantity']);
		$this->lineProps['listprice'] 					= CurrencyField::convertToUserFormat($line['listprice']);
		$this->lineProps['discount_percent'] 			= CurrencyField::convertToUserFormat($line['discount_percent']);
		$this->lineProps['discount_amount'] 			= CurrencyField::convertToUserFormat($line['discount_amount']);
		$this->lineProps['description'] 				= $line['description'];
		$this->lineProps['taxes'] 						= array(); // Inventorydetails doesn't support separate taxes
		$this->lineProps['extgross'] 					= CurrencyField::convertToUserFormat($line['extgross']);
		$this->lineProps['discount_type']				= $this->getDiscountType();
		$this->lineProps['extnet'] 						= CurrencyField::convertToUserFormat($line['extnet']);
		$this->lineProps['linetotal']					= CurrencyField::convertToUserFormat($line['linetotal']);
		$this->lineProps['linetax']						= CurrencyField::convertToUserFormat($line['linetax']);	
		$this->lineProps['tax_percent']					= CurrencyField::convertToUserFormat($line['tax_percent']);
		$this->lineProps['productname']					= $line['productname'];
		$this->lineProps['entity_type']					= $line['entitytype'];
		$this->lineProps['stock_quantity']				= CurrencyField::convertToUserFormat($line['qtyinstock']);
		$this->lineProps['cost_price']					= CurrencyField::convertToUserFormat($line['cost_price']);
		$this->lineProps['quantity_per_unit']			= CurrencyField::convertToUserFormat($line['qty_per_unit']);
		$this->lineProps['usageunit']					= getTranslatedString($line['usageunit'], 'Products');
		$this->lineProps['reorderlevel']				= CurrencyField::convertToUserFormat($line['reorderlevel']);
		$this->lineProps['qtyindemand']					= CurrencyField::convertToUserFormat($line['qtyindemand']);
		$this->lineProps['units_delivered_received']	= CurrencyField::convertToUserFormat($line['units_delivered_received']);;

		// Create a separate array with all the raw values (not user formatted)
		$this->lineProps['raw']							= $this->setRawValues($line);

		return $this->lineProps;		
	}
}