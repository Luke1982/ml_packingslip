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
	var unitsDelRecField = data.source.getElementsByClassName("product_line_del_rec")[0];
	var costPriceField = data.source.getElementsByClassName("product_line_costprice")[0];
	var nameField = data.source.getElementsByClassName("product_line_name")[0];
	var commentField = data.source.getElementsByClassName("product_line_comment")[0];
	var priceField = data.source.getElementsByClassName("product_line_listprice")[0];
	var discountField = data.source.getElementsByClassName("product_line_discount")[0];
	var discountRadios = data.source.getElementsByClassName("product_line_disc_radio");
	var totalTaxPerc = data.source.getElementsByClassName("product_line_total_tax_perc")[0];
	var taxInputs = data.source.getElementsByClassName("product_line_tax");

	// Targets
	var grossPrice = data.source.getElementsByClassName("product_line_gross")[0];
	var netPrice = data.source.getElementsByClassName("product_line_net")[0];
	var lineTax = data.source.getElementsByClassName("product_line_tax_amount")[0];
	var lineFinal = data.source.getElementsByClassName("product_line_after_tax")[0];

	// Hidden inputs
	var hdnQtyField = data.source.getElementsByClassName("hdn_product_qty")[0];
	var hdnUnitsDelRecField = data.source.getElementsByClassName("hdn_product_units_del_rec")[0];
	var hdnCostPriceField = data.source.getElementsByClassName("hdn_product_cost_price")[0];
	var hdnDiscField = data.source.getElementsByClassName("hdn_product_discount")[0];
	var hdnDiscTypeField = data.source.getElementsByClassName("hdn_product_discount_type")[0];
	var hdnLineGrossField = data.source.getElementsByClassName("hdn_product_gross")[0];
	var hdnLineNetField = data.source.getElementsByClassName("hdn_product_net")[0];
	var hdnListPriceField = data.source.getElementsByClassName("hdn_product_listprice")[0];
	var hdnDiscPercField = data.source.getElementsByClassName("hdn_product_discount_per")[0];
	var hdnDiscAmountField = data.source.getElementsByClassName("hdn_product_discount_am")[0];
	var hdnTaxAmountField = data.source.getElementsByClassName("hdn_product_tax_am")[0];
	var hdnTaxPercField = data.source.getElementsByClassName("hdn_product_tax_per")[0];
	var hdnTotalField = data.source.getElementsByClassName("hdn_product_total")[0];
	var hdnCommentField = data.source.getElementsByClassName("hdn_product_comment")[0];
	var hdnTotalTaxPercField = data.source.getElementsByClassName("hdn_product_tax_per")[0];

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
			if (source.className == "product_line") {
				return source;
			}
		}
	}

	// Global helpers
	__setNonEditableNo = function(amount, target) {
		target.innerHTML = amount.formatMoney(); // Use global formatting function
		return Math.round(amount * 100) / 100;
	}

	__setInput = function(input, newValue) {
		input.value = newValue;
		// If input is hidden type remove dots
		// and replace comma by dot
		if (input.type == "hidden" && typeof newValue === "string") {
			newValue = newValue.replace(grpSep, "");
			newValue = newValue.replace(decSep, ".");
			input.value = newValue;
		}
		return newValue;
	}

	__getInput = function(input) {
		return input.value.formatJSNo();
	}

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
			parent.getElementsByClassName("discount_symbol")[0].innerHTML = "-/-";
			return {
				"discountValue" : discVal,
				"discountType"	: discType
			};
		} else if (discType == "p") {
			// Discount percentage
			parent.getElementsByClassName("discount_symbol")[0].innerHTML = "%";
			discVal = Math.round(discVal * 100) / 100; // Round to two decimals only when needed
			return {
				"discountValue" : discVal,
				"discountType"	: discType
			};
		}
	}

	function __updateHiddenTaxField(domLine, taxInput) {
		var taxName = taxInput.getAttribute("data-taxname");
		var hiddenTaxFields = domLine.getElementsByClassName("product_line_hdntaxes")[0];
		var inputToUpdate = hiddenTaxFields.getElementsByClassName("hdn_product_"+taxName)[0];
		inputToUpdate.value = taxInput.value.formatJSNo();
	}

	function __updateTotalTaxPercentage(taxPercSum) {
		hdnTotalTaxPercField.value = taxPercSum;
		totalTaxPerc.value = taxPercSum;
		console.log("Tax percentage sum updated");
	}

	__calculateTaxAmount = function(taxPerc, amount) {
		return amount * (taxPerc / 100);
	}

	/*
	 * Gets all input fields that represent a tax percentage
	 * and creates a sum of the percentages. Also updates the
	 * readonly taxpercentage field
	 */
	function __addTaxes() {
		var taxPercSum = 0;
		for (var i = 0; i < taxInputs.length; i++) {
			if (taxInputs[i].value != "") {
				console.log(typeof taxInputs[i].value);
				var toAdd = parseFloat(taxInputs[i].value.formatJSNo()); // Make sure we are using numbers
				taxPercSum += toAdd;
			}
		}
		__updateTotalTaxPercentage(taxPercSum);
		return taxPercSum;
	}

	/*
	 * Eventual function. Uses a DOM node passed into it, to update the
	 * line. After that, it calls the "updateInventory" function for this line
	 */
	function __calcDomLine(domLine) {
		// Set the hidden comment
		var newHdnComment		= hdnCommentField.value = commentField.value
		// Set some hidden inputs for "updateLine" method to pick up
		var newHdnQty 			= __setInput(hdnQtyField, qtyField.value);
		var newHdnListPrice 	= __setInput(hdnListPriceField, priceField.value);
		// Set units selivered / received
		var newHdnUnitsDelRec	= __setInput(hdnUnitsDelRecField, unitsDelRecField.value);
		// Set the costprice
		var newHdnCostPrice		= __setInput(hdnCostPriceField, costPriceField.value);
		// Set the gross line total
		var newLineGross 		= __setNonEditableNo( (qtyField.value * __getInput(priceField)), grossPrice);
		var newLineHdnGross 	= __setInput(hdnLineGrossField, newLineGross);
		// Calculate the discount and net lineprice
		var discount 			= __determineDiscount(domLine);
		if (discount.discountType == "d") {
			// Direct price discount calculations
			var newLineHdnDiscAm 	= __setInput(hdnDiscAmountField, discount.discountValue);
			var newLineHdnDiscPer 	= __setInput(hdnDiscPercField, 0);
			var newLineNet 			= __setNonEditableNo((newLineGross - discount.discountValue), netPrice);
			var newLineHdnNet 		= __setInput(hdnLineNetField, newLineNet);
		} else {
			// Percentage discount calculations
			var newLineHdnDiscPer 	= __setInput(hdnDiscPercField, discount.discountValue);
			var newLineHdnDiscAm 	= __setInput(hdnDiscAmountField, 0);
			var newLineNet 			= __setNonEditableNo((newLineGross * (1 - (discount.discountValue / 100))), netPrice);
			var newLineHdnNet 		= __setInput(hdnLineNetField, newLineNet);
		}
		// Calculate the tax
		var taxTotal 			= __addTaxes();
		var taxAmount 			= __calculateTaxAmount(taxTotal, newLineNet);
		var newLineTax 			= __setNonEditableNo(taxAmount, lineTax);
		var newLineHdnTax 		= __setInput(hdnTaxAmountField, newLineTax);
		var newLineHdnTaxPer	= __setInput(hdnTaxPercField, taxTotal);
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
				if (coll[i].getAttribute("cleanline") != "leavealone") {
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
		// Update the row no's for the new line's name attributes on the hidden inputs
		cleanLine = __updateRowNos(cleanLine);

		parent.insertBefore(cleanLine, line.nextSibling);

		var line = new InventoryLine({
			"source"		: cleanLine
		});

		line.props = line.setProps(line.propInputs);
		line.props.id = inventoryLines.length;

		inventoryLines.push(line);

		// Update the sequence
		updateSeq();
		// Make sure first line delete tool is hidden
		hideFirstDelete();
	}

	/*
	 * Takes a new or copied domline and updates the row
	 * numbers of the hidden inputs for saving later in PHP
	 */
	function __updateRowNos(domLine) {
		var hiddenInputs = domLine.getElementsByClassName("productline_props")[0].getElementsByTagName("input");
		var hiddenTas = domLine.getElementsByClassName("productline_props")[0].getElementsByTagName("textarea");

		hiddenInputs = incrementNames(hiddenInputs);
		hiddenTas = incrementNames(hiddenTas);

		function incrementNames(coll) {
			for (var i = 0; i < coll.length; i++) {
				var baseName = coll[i].name.substring(0, 12);
				var endName = coll[i].name.substring(13, coll[i].name.length);
				coll[i].name = baseName + inventoryLines.length + endName;
				// console.log(coll[i].name);
			}
			return coll;
		}
		return domLine;
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
		// Clear the lineitem_id for this line
		lineClone.getElementsByClassName("hdn_product_line_id")[0].value = "";
		// Get new Radio names for this line
		lineClone = __setNewRadioNames(lineClone);
		// Update the row no's for the new line's name attributes on the hidden inputs
		lineClone = __updateRowNos(lineClone);		

		parent.insertBefore(lineClone, domLine.nextSibling);

		var line = new InventoryLine({
			"source"		: lineClone
		});

		line.props = line.setProps(line.propInputs);
		line.props.id = inventoryLines.length;

		inventoryLines.push(line);

		// Update the sequence
		updateSeq();
		// Make sure first line delete tool is hidden
		hideFirstDelete();	
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
		// Make sure first line delete tool is hidden
		hideFirstDelete();	
	}

	// Event listeners
	qtyField.addEventListener("input", function(e){
		__calcDomLine(findUp("product_line", e.srcElement));
		// __calcDomLine(e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode);
		console.log("qty changed");
	});

	unitsDelRecField.addEventListener("input", function(e){
		__calcDomLine(findUp("product_line", e.srcElement));
		// __calcDomLine(e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode);
		console.log("units delivered / received changed");
	});

	costPriceField.addEventListener("input", function(e){
		__calcDomLine(findUp("product_line", e.srcElement));
		// __calcDomLine(e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode);
		console.log("costprice changed");
	});

	priceField.addEventListener("input", function(e){
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__calcDomLine(parentLine);
		console.log("listprice changed");
	});

	discountField.addEventListener("input", function(e){
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__calcDomLine(parentLine);
		console.log("discount changed");		
	});	

	commentField.addEventListener("input", function(e){
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__calcDomLine(parentLine);
		console.log("comment changed");		
	});

	for (var i = 0; i < discountRadios.length; i++) {
		discountRadios[i].addEventListener("click", function(e){
			var parentLine = findUp("product_line", e.srcElement);
			// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
			__calcDomLine(parentLine);
			console.log("Discount type changed");
		});
	}

	for (var i = 0; i < taxInputs.length; i++) {
		taxInputs[i].addEventListener("input", function(e){
			var parentLine = findUp("product_line", e.srcElement);
			__updateHiddenTaxField(parentLine, e.srcElement);
			// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
			__calcDomLine(parentLine);
			console.log("Tax Changed");
		});
	}

	newLineTool.addEventListener("click", function(e){
		var productTable = document.getElementById("proBody");
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__insertEmptyLine(productTable, parentLine);
		// console.log(inventoryLines);
	});

	deleteLineTool.addEventListener("click", function(e){
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__deleteLine(parentLine);
		// console.log(inventoryLines);
	});

	copyLineTool.addEventListener("click", function(e){
		var productTable = document.getElementById("proBody");
		var parentLine = findUp("product_line", e.srcElement);
		// var parentLine = e.srcElement.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
		__copyLine(productTable, parentLine);
		// console.log(inventoryLines);
	});

	// Autocomplete
	window.acList;

	var ac = new Awesomplete(nameField, {
		list : [],
		autoFirst : true,
		maxItems : 6,
		minChars: 2,
		data: function(item, input) {
			var constructedLabel = "<span class='ac_productno'>"+item.productno+"</span>";
			constructedLabel += "<span class='ac_productname'>"+item.value+"</span><br>";
			constructedLabel += "<span class='ac_venno_tit'>"+item.ven_part_lbl+"</span>";
			constructedLabel += "<span class='ac_vendorno'>"+item.ven_part_no+"</span><br>";
			constructedLabel += "<span class='ac_mfrno_tit'>"+item.mfr_part_lbl+"</span>";
			constructedLabel += "<span class='ac_mfrno'>"+item.mfr_part_no+"</span>";
			return {
				label: constructedLabel,
				value: item.value
			};
		}
	});

	nameField.addEventListener("input", function(e){
		var r = new XMLHttpRequest();
		r.open ("GET", "index.php?module=PackingSlip&action=PackingSlipAjax&file=inventoryAjax&getlist=true&term="+e.srcElement.value);
		r.onreadystatechange = function() {
			if (r.readyState == 4 && r.status == "200") {
				// List is retrieved
				acList = JSON.parse(r.responseText);
				ac._list = acList;
				ac.evaluate();
			}
		}
		r.send();		
	});

	window.addEventListener("awesomplete-selectcomplete", function(e){
		var callingLine = findUp("product_line", e.srcElement);
		__autocompleteCallback(e.text.value, callingLine, acList);
		__calcDomLine(callingLine);
	});	

	function __autocompleteCallback(label, callingLine, source) {
		for(var i = 0; i < source.length; i++) {
			if (source[i].label == label) {
				// Numeric inputs
				__setInput(callingLine.getElementsByClassName("product_line_listprice")[0], source[i].price);
				__setInput(callingLine.getElementsByClassName("product_line_costprice")[0], source[i].costPrice);
				// Other inputs
				callingLine.getElementsByClassName("product_line_comment")[0].value = source[i].desc;
				callingLine.getElementsByClassName("hdn_product_crm_id")[0].value = source[i].crmid;
				callingLine.getElementsByClassName("hdn_product_entity_type")[0].value = source[i].entityType;
				// Fill in the target spans
				callingLine.getElementsByClassName("product_line_in_stock")[0].innerHTML = source[i].inStock;
				callingLine.getElementsByClassName("product_line_qty_per_unit")[0].innerHTML = source[i].packSize + " / " + source[i].usageUnit;
				callingLine.getElementsByClassName("product_line_backorder_lvl")[0].innerHTML = source[i].inDemand;
				callingLine.getElementsByClassName("product_line_ordered")[0].innerHTML = source[i].reOrderLvl;
				// Fill the taxes
				(function(_i){
					__fillTaxesOnAutocomplete(callingLine, source[_i].taxes);
				})(i);
			}
		}
	}

	function __fillTaxesOnAutocomplete(domLine, taxObject) {
		// First clear out all the taxInputs
		for (var i = 0; i < taxInputs.length; i++) {
			taxInputs[i].value = 0;
		}
		// Then fill the ones the product has
		for (var i = 0; i < taxObject.length; i++) {
			domLine.getElementsByClassName("tax"+taxObject[i].taxid)[0].value = taxObject[i].taxpercentage;
		}
	}
}