<?php

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
											CASE WHEN vtiger_products.productid != '' THEN vtiger_products.qtyinstock ELSE 'NA' END AS qtyinstock,
											CASE WHEN vtiger_products.productid != '' THEN 'Products' ELSE 'Services' END AS entitytype,
											vtiger_inventoryproductrel.* FROM 
											vtiger_inventoryproductrel LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid 
											LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid 
											WHERE id=? ORDER BY ?", array($crm_id, 'sequence_no'));

		while ($product = $this->db->fetch_array($product_coll)) {
			$this->collectedProductLines[] = $this->createProductLine($product);
		}
		return $this->collectedProductLines;
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
			'tax1'				=>		0,
			'tax2'				=>		0,
			'tax3'				=>		0,
			'line_gross_total'	=>		0,
			'discount_type'		=>		'',
			'line_net_total'	=>		0,
			'product_name'		=>		'',
			'product_no'		=>		'',
			'entity_type'		=>		'',
			'stock_qty'			=>		'',
			'mfr_part_no'		=>		'',
			'vendor_part_no'	=>		''
			);
	}

	private function createProductLine($product) {
		$this->startEmptyProductLine();

		$this->productProps['crment_id'] 			= $product['id'];
		$this->productProps['line_id'] 				= $product['lineitem_id'];
		$this->productProps['product_id'] 			= $product['productid'];
		$this->productProps['seq'] 					= $product['sequence_no'];
		$this->productProps['qty'] 					= $product['quantity'];
		$this->productProps['list_price'] 			= $product['unit_price'];
		$this->productProps['disc_perc'] 			= $product['discount_percent'];
		$this->productProps['disc_am'] 				= $product['discount_amount'];
		$this->productProps['comment'] 				= $product['comment'];
		$this->productProps['desc'] 				= $product['description'];
		$this->productProps['tax1'] 				= $product['tax1'];
		$this->productProps['tax2'] 				= $product['tax2'];
		$this->productProps['tax3'] 				= $product['tax3'];
		$this->productProps['line_gross_total'] 	= $this->calcLineGrossTotal($product['quantity'], $product['unit_price']);
		$this->productProps['discount_type']		= $this->getDiscountType($product['discount_percent'], $product['discount_amount']);
		$this->productProps['line_net_total'] 		= $this->calcLineNetTotal($this->productProps['line_gross_total'], $product['discount_percent'], $product['discount_amount'], $this->productProps['discount_type']);
		$this->productProps['product_name']			= $product['productname'];
		$this->productProps['product_no']			= $product['productcode'];
		$this->productProps['entity_type']			= $product['entitytype'];
		$this->productProps['stock_qty']			= $product['qtyinstock'];
		$this->productProps['mfr_part_no']			= $product['mfr_part_no'];
		$this->productProps['vendor_part_no']		= $product['vendor_part_no'];

		return $this->productProps;
	}

	private function calcLineGrossTotal($qty, $list_price) {
		return $qty * $list_price;
	}

	private function calcLineNetTotal($gross_total, $disc_perc = 0, $disc_amount = 0, $disc_type) {
		if ($disc_type == 'p') {
			$ret = $gross_total - ($gross_total * (1 - ($disc_perc / 100)));
			return $ret;
		} else {
			$ret = $gross_total - $disc_amount;
			return $ret;
		}
	}

	private function getDiscountType($perc = 0, $am = 0) {
		if ($perc != NULL && $perc != '') {
			return 'p';
		} else {
			return 'd';
		}
	}

}