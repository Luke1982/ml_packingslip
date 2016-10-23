function InventoryLine(data) {

	var __getInventoryLineProps = function(tableRow) {
		return tableRow.getElementsByClassName("productline_props")[0].getElementsByTagName("input");
	}

	var __props = {};

	this.propInputs = __getInventoryLineProps(data.source);
	this.props = {};
	this.source = data.source;

	// Inputs
	var qtyField = data.source.getElementsByClassName("product_line_qty")[0];
	var priceField = data.source.getElementsByClassName("product_line_listprice")[0];
	var discountField = data.source.getElementsByClassName("product_line_discount")[0];

	// Targets
	var grossPrice = data.source.getElementsByClassName("product_line_gross")[0];

	// Hidden inputs
	var hdnQtyField = data.source.getElementsByClassName("hdn_product_qty")[0];

	this.setProps = function(inputs) {
		for (var i = 0; i < inputs.length; i++) {
			var propName = inputs[i].className.replace("hdn_product_", "");
			var propVal = inputs[i].value;
			__props[propName] = propVal;
		}
		// this.props = __props;
		return __props;
	}

	this.updateLine = function(updatedLine, index) {
		var newPropInputs = __getInventoryLineProps(updatedLine);
		var newProps = this.setProps(newPropInputs);
		// console.log(updatedLine);
		// console.log(newProps);
		// console.log(index);
		console.log(inventoryLines);
		// inventoryLines[index].props = newProps;
	}

	__setNonEditableNo = function(amount, target) {
		target.innerHTML = amount;
	}

	__setInput = function(input, newValue) {
		input.value = newValue;
	}

	// __determineDiscount = function() {

	// }

	qtyField.addEventListener("input", function(e){
		__setNonEditableNo((qtyField.value * priceField.value), grossPrice);
		__setInput(hdnQtyField, qtyField.value);
		updateInventory(e.srcElement.parentNode.parentNode);
		console.log("qty changed");
	});
	priceField.addEventListener("input", function(){
		__setNonEditableNo((qtyField.value * priceField.value), grossPrice);
	});
	// discountField.addEventListener("input", function(){
	// 	var discount = __determineDiscount();
	// });
}