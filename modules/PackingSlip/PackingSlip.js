var inventoryLines = [];

window.addEventListener("load", function(){

	var int = setInterval(function(){
		if (typeof InventoryLine === "function") {
			buildInventory();
			clearInterval(int);
		}
	},100);

	// Use the sortable library
	// https://rubaxa.github.io/Sortable/
	var list = document.getElementById("proBody");
	Sortable.create(list, {
		onUpdate: function(){
			updateSeq();
			console.log("dragged");
		},
		animation : 150,
		handle: ".move_line_tool",
		draggable : ".product_line"
	});

	/*
	 * Function to handle changing tax type
	 */
	function taxType() {
		var taxSelect = document.getElementById("taxtype");
		taxSelect.addEventListener("change", function(){
			updateTaxes(taxSelect.value);
		});
	}
	taxType();	

});

/*
* Function that builds an array of all inventory lines in JS memory
* 
*/
function buildInventory() {
	var lines = document.getElementsByClassName("product_line");
	for (var i = 0; i < lines.length; i++) {
		var line = new InventoryLine({
			"source" 	: lines[i],
			"id"		: i
		});
		line.props = line.setProps(line.propInputs);
		inventoryLines.push(line);
	}
	console.log(inventoryLines);
}

/*
 * Function that updates the JS array of inventory lines
 * or a single line if passed into the function
 */
function updateInventory(line) {
	if (line != undefined) {
		var toUpdate = checkLineId(line);
		toUpdate.line.updateLine(line);
	}
	console.log(inventoryLines);
}

/*
 * Function that checks a DOM line against the JS array of lines
 * and performs the update method on the line that matches
 */
function checkLineId(domLine) {
	var domLineId = domLine.getElementsByClassName("hdn_product_id")[0].value;
	for (var i = 0; i < inventoryLines.length; i++) {
		var idToCheck = inventoryLines[i].props.id;
		if (domLineId == idToCheck) {
			return {"line" : inventoryLines[i]};
		}
	}
}

/*
 * Function that updates the sequence (visible order) of the lines
 */
function updateSeq() {
	var domLines = getNonDeletedDomLines();
	for (var i = 0; i < domLines.length; i++) {
		var lineID = domLines[i].getElementsByClassName("hdn_product_id")[0].value;
		var seqInput = domLines[i].getElementsByClassName("hdn_product_seq")[0];
		inventoryLines[lineID].props.seq = i + 1;
		seqInput.value = i + 1;
	}
}

/*
 * Helper for the updateSeq function.
 * returns an array of non-deleted domLines
 */
function getNonDeletedDomLines() {
	var domLines = document.getElementsByClassName("product_line");
	var nonDeletedLines = [];
	for (var i = 0; i < domLines.length; i++) {
		if (domLines[i].getElementsByClassName("hdn_product_isdeleted")[0].value == "false") {
			nonDeletedLines.push(domLines[i]);
		}
	}
	return nonDeletedLines;
}

/*
 * Handles all tax related stuff when the taxtype changes
 */
function updateTaxes(taxType) {
	// Show or hide appropriate lines
	var pLinesTaxes = document.getElementsByClassName("product_line_taxes");
	var pLinesTaxTotals = document.getElementsByClassName("product_line_totals_tax");
	var pLinesNetTotals = document.getElementsByClassName("product_line_totals_net");
	var totalsGroupTaxes = document.getElementById("inv_totals_group_taxes");
	for (var i = 0; i < pLinesTaxes.length; i++) {
		if (taxType == "group") {
			pLinesTaxTotals[i].style.display = "none";
			pLinesNetTotals[i].style.display = "none";
			pLinesTaxes[i].style.display = "none";
			totalsGroupTaxes.style.display = "table-row";
		} else if (taxType == "individual") {
			pLinesTaxTotals[i].style.display = "table-row";
			pLinesNetTotals[i].style.display = "table-row";
			pLinesTaxes[i].style.display = "table-cell";
			totalsGroupTaxes.style.display = "none";
		}
	}

}

// Key listener
document.addEventListener('keyup', docKeyUp, false);

function docKeyUp(e) {
	console.log(e.keyCode);
	if (e.keyCode == 81) {
		// CTRL + Q
		console.log("CTRL Q pressed");
		var taxType = document.getElementById("taxtype");
		if (taxType.value == "group") {
			taxType.value = "individual";
		} else {
			taxType.value = "group";
		}
		taxType.dispatchEvent(new Event("change", {
				"bubbles" : true 
			}));

	}
}