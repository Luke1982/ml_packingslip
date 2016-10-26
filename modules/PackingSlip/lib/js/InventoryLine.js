function InventoryLine(data) {

	var __getInventoryLineProps = function(tableRow) {
		return tableRow.getElementsByClassName("productline_props")[0].getElementsByTagName("input");
	}

	var __props = {};

	this.propInputs = __getInventoryLineProps(data.source);
	this.props = {};
	this.source = data.source;
	this.props.id = data.id;

	// Tools
	var newLineTool = data.source.getElementsByClassName("new_line_tool")[0];
	var copyLineTool = data.source.getElementsByClassName("copy_line_tool")[0];
	var deleteLineTool = data.source.getElementsByClassName("delete_line_tool")[0];

	// Inputs
	var qtyField = data.source.getElementsByClassName("product_line_qty")[0];
	var nameField = data.source.getElementsByClassName("product_line_name")[0];
	var commentField = data.source.getElementsByClassName("product_line_comment")[0];
	var priceField = data.source.getElementsByClassName("product_line_listprice")[0];
	var discountField = data.source.getElementsByClassName("product_line_discount")[0];
	var discountRadios = data.source.getElementsByClassName("product_line_disc_radio");
	var taxInputs = data.source.getElementsByClassName("product_line_tax");

	// Targets
	var grossPrice = data.source.getElementsByClassName("product_line_gross")[0];
	var netPrice = data.source.getElementsByClassName("product_line_net")[0];
	var lineTax = data.source.getElementsByClassName("product_line_tax_amount")[0];
	var lineFinal = data.source.getElementsByClassName("product_line_after_tax")[0];

	// Hidden inputs
	var hdnQtyField = data.source.getElementsByClassName("hdn_product_qty")[0];
	var hdnDiscField = data.source.getElementsByClassName("hdn_product_discount")[0];
	var hdnDiscTypeField = data.source.getElementsByClassName("hdn_product_discount_type")[0];
	var hdnLineGrossField = data.source.getElementsByClassName("hdn_product_gross")[0];
	var hdnLineNetField = data.source.getElementsByClassName("hdn_product_net")[0];
	var hdnListPriceField = data.source.getElementsByClassName("hdn_product_listprice")[0];
	var hdnDiscTypeField = data.source.getElementsByClassName("hdn_product_discount_type")[0];
	var hdnDiscAmountField = data.source.getElementsByClassName("hdn_product_discount")[0];
	var hdnTaxAmountField = data.source.getElementsByClassName("hdn_product_tax_am")[0];
	var hdnTotalField = data.source.getElementsByClassName("hdn_product_total")[0];
	var hdnCommentField = data.source.getElementsByClassName("hdn_product_comment")[0];

	// Instance methods
	this.setProps = function(inputs) {
		for (var i = 0; i < inputs.length; i++) {
			var propName = inputs[i].className.replace("hdn_product_", "");
			var propVal = inputs[i].value;
			__props[propName] = propVal;
		}
		__props.comment = hdnCommentField.innerHTML;
		return __props;
	}

	this.updateLine = function(updatedLine) {
		var newPropInputs = __getInventoryLineProps(updatedLine);
		var newProps = this.setProps(newPropInputs);
		__props.comment = hdnCommentField.innerHTML;
	}

	// Dom helpers
	function findUp(className, source) {
		while(source = source.parentElement) {
			if (source.className.indexOf("product_line") == 0) {
				return source;
			}
		}
	}

	// Global helpers
	__setNonEditableNo = function(amount, target) {
		target.innerHTML = amount.toFixed(2); // Always use two decimals for the filled amounts
		return Math.round(amount * 100) / 100;
	}

	__setInput = function(input, newValue) {
		input.value = newValue;
		return newValue;
	}

	__getInput = function(input) {
		return input.value;
	}

	// __triggerInput = function(field) {
	// 	field.dispatchEvent(new Event("input", {
	// 		"bubbles" : true 
	// 	}));
	// }

	__getDiscountType = function(parent) {
		var discRadios = parent.getElementsByClassName("product_line_disc_radio");
		for (var i = 0; i < discRadios.length; i++) {
			if (discRadios[i].checked) {
				return discRadios[i].value;
			}
		}		
	}

	__determineDiscount = function(parent) {
		var currentGross = parent.getElementsByClassName("product_line_gross")[0].innerHTML;
		var discVal = parent.getElementsByClassName("product_line_discount")[0].value;
		var discType = __getDiscountType(parent);
		if (discType == "d") {
			// Direct discount
			return {
				"discountValue" : discVal,
				"discountType"	: discType
			};
		} else if (discType == "p") {
			// Discount percentage
			discVal = currentGross * (discVal / 100);
			discVal = Math.round(discVal * 100) / 100; // Round to two decimals only when needed
			return {
				"discountValue" : discVal,
				"discountType"	: discType
			};
		}
	}

	__calculateTaxAmount = function(taxPerc, amount) {
		return amount * (taxPerc / 100);
	}

	function __addTaxes() {
		var totalTax = 0;
		for (var i = 0; i < taxInputs.length; i++) {
			var toAdd = parseFloat(taxInputs[i].value);
			totalTax += toAdd;
		}
		return totalTax;
	}

	/*
	 * Eventual function. Uses a DOM node passed into it, to update the
	 * line. After that, it calls the "updateInventory" function for this line
	 */
	function __calcDomLine(domLine) {
		// Set the hidden comment
		var newHdnComment		= hdnCommentField.innerHTML = commentField.value
		// Set some hidden inputs for "updateLine" method to pick up
		var newHdnQty 			= __setInput(hdnQtyField, qtyField.value);
		var newHdnListPrice 	= __setInput(hdnListPriceField, priceField.value);
		// Set the gross line total
		var newLineGross 		= __setNonEditableNo( (qtyField.value * __getInput(priceField)), grossPrice);
		var newLineHdnGross 	= __setInput(hdnLineGrossField, newLineGross);
		// Calculate the discount and net lineprice
		var discount 			= __determineDiscount(domLine);
		var newLineHdnDiscType 	= __setInput(hdnDiscTypeField, discount.discountType);
		var newLineHdnDiscAm 	= __setInput(hdnDiscAmountField, discount.discountValue);
		var newLineNet 			= __setNonEditableNo((newLineGross - discount.discountValue), netPrice);
		var newLineHdnNet 		= __setInput(hdnLineNetField, newLineNet);
		// Calculate the tax
		var taxTotal 			= __addTaxes();
		var taxAmount 			= __calculateTaxAmount(taxTotal, newLineNet);
		var newLineTax 			= __setNonEditableNo(taxAmount, lineTax);
		var newLineHdnTax 		= __setInput(hdnTaxAmountField, newLineTax);
		// Final amount for the line after tax and discount
		var newLineTotal		= __setNonEditableNo((newLineNet + newLineTax), lineFinal);
		var newLineHdnTotal		= __setInput(hdnTotalField, (newLineNet + newLineTax));

		// Finally, update the line in JS memory
		updateInventory(domLine);
	}

	/*
	 * Takes an existing line as argument and prepares it
	 * to be inserted by cleaning most inputs.
	 */
	function __cleanLine(line) {
		var inputs = line.getElementsByTagName("input");
		var tas = line.getElementsByTagName("textarea");
		var targets = line.getElementsByClassName("target");
		function emptyValues(coll) {
			for (var i = 0; i < coll.length; i++) {
				// Don't clear tax fields and discount type
				if (coll[i].className.indexOf("product_line_tax") == -1 
					&& coll[i].className.indexOf("disc") == -1) {
					coll[i].value = "";
				}
				if (coll[i].type == "radio") {
					// New name attributes for radio groups
					var baseName = coll[i].name.slice(0, -1);
					inputs[i].name = baseName + inventoryLines.length;
				}
				if (coll[i].className.indexOf("target") != -1) {
					// Set the targets to 0
					coll[i].innerHTML = 0;
				}
			}
		}
		emptyValues(inputs);
		emptyValues(tas);
		emptyValues(targets);

		return line;
	}

	/*
	 * Takes an existing line and copies it, then 
	 * empties it and inserts it after the line it
	 * was copied from 
	 */
	function __insertEmptyLine(parent, line) {
		var lineClone = line.cloneNode(true);
		var cleanLine = __cleanLine(lineClone);
		// Add a new ID to the clean line
		cleanLine.getElementsByClassName("hdn_product_id")[0].value = inventoryLines.length;
		// Set deleted to false on the new line
		cleanLine.getElementsByClassName("hdn_product_isdeleted")[0].value = "false";

		parent.insertBefore(cleanLine, line.nextSibling);

		var line = new InventoryLine({
			"source"		: cleanLine
		});

		line.props = line.setProps(line.propInputs);
		line.props.id = inventoryLines.length;

		inventoryLines.push(line);

		// Update the sequence
		updateSeq();		
	}

	/*
	 * Gets new name attributes for a radio group
	 * helper for copyline function
	 */
	function __setNewRadioNames(domLine) {
		var coll = domLine.getElementsByTagName("input");
		for (var i = 0; i < coll.length; i++) {
			if (coll[i].type == "radio") {
				// New name attributes for radio groups
				var baseName = coll[i].name.slice(0, -1);
				coll[i].name = baseName + inventoryLines.length;
			}
		}
		return domLine;
	}

	/*
	 * Copies a line it was fed
	 * Inserts it after the line it was called on
	 */
	function __copyLine(parent, domLine) {
		var lineClone = domLine.cloneNode(true);
		// Add a new ID to the copied line
		lineClone.getElementsByClassName("hdn_product_id")[0].value = inventoryLines.length;
		// Get new Radio names for this line
		lineClone = __setNewRadioNames(lineClone);

		parent.insertBefore(lineClone, domLine.nextSibling);

		var line = new InventoryLine({
			"source"		: lineClone
		});

		line.props = line.setProps(line.propInputs);
		line.props.id = inventoryLines.length;

		inventoryLines.push(line);

		// Update the sequence
		updateSeq();	
	}

	/*
	 * Deletes the line it was called on
	 */
	function __deleteLine(domLine) {
		domLine.getElementsByClassName("hdn_product_isdeleted")[0].value = "true";
		updateInventory(domLine);
		domLine.style.display = "none";

		// Update the sequence
		updateSeq();		
	}

	// Event listeners
	qtyField.addEventListener("input", function(e){
		__calcDomLine(findUp("product_line", e.srcElement););
		// __calcDomLine(e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode);
		console.log("qty changed");
	});

	priceField.addEventListener("input", function(e){
		var parentLine = findUp("product_line", e.srcElement);;
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__calcDomLine(parentLine);
		console.log("listprice changed");
	});

	discountField.addEventListener("input", function(e){
		var parentLine = findUp("product_line", e.srcElement);;
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__calcDomLine(parentLine);
		console.log("discount changed");		
	});

	for (var i = 0; i < discountRadios.length; i++) {
		discountRadios[i].addEventListener("click", function(e){
			var parentLine = findUp("product_line", e.srcElement);;
			// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
			__calcDomLine(parentLine);
			console.log("Discount type changed");
		});
	}

	for (var i = 0; i < taxInputs.length; i++) {
		taxInputs[i].addEventListener("input", function(e){
			var parentLine = findUp("product_line", e.srcElement);;
			// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
			__calcDomLine(parentLine);
			console.log("Tax Changed");
		});
	}

	newLineTool.addEventListener("click", function(e){
		var productTable = document.getElementById("proBody");
		var parentLine = findUp("product_line", e.srcElement);;
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__insertEmptyLine(productTable, parentLine);
		console.log(inventoryLines);
	});

	deleteLineTool.addEventListener("click", function(e){
		var parentLine = findUp("product_line", e.srcElement);;
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__deleteLine(parentLine);
		console.log(inventoryLines);
	});

	copyLineTool.addEventListener("click", function(e){
		var productTable = document.getElementById("proBody");
		var parentLine = findUp("product_line", e.srcElement);;
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__copyLine(productTable, parentLine);
		console.log(inventoryLines);
	});

	// Autocomplete

	var r = new XMLHttpRequest();
	r.open ("GET", "index.php?module=PackingSlip&action=PackingSlipAjax&file=inventoryAjax&getlist=true");
	r.onreadystatechange = function() {
		if (r.readyState == 4 && r.status == "200") {
			// List is retrieved
			var acList = JSON.parse(r.responseText);

			new Awesomplete(nameField, {
				list : acList,
				autoFirst : true
			});

			window.addEventListener("awesomplete-selectcomplete", function(e){
				var callingLine = findUp("product_line", e.srcElement);
				__autocompleteCallback(e.text.label, callingLine, acList);
				__calcDomLine(callingLine);
			});

		}
	}
	r.send();	

	function __autocompleteCallback(label, callingLine, source) {
		for(var i = 0; i < source.length; i++) {
			if (source[i].label == label) {
				callingLine.getElementsByClassName("product_line_comment")[0].value = source[i].desc;
				callingLine.getElementsByClassName("product_line_listprice")[0].value = source[i].price;
				callingLine.getElementsByClassName("hdn_product_crm_id")[0].value = source[i].crmid;
			}
		}
	}
}