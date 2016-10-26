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
			// updateInventory();
			console.log("dragged");
		},
		animation : 150,
		handle: ".move_line_tool"
	});

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
		// If called for a single line
		var toUpdate = checkLineId(line);
		toUpdate.line.updateLine(line);
	} else {
		// Call it for all lines
		var lines = document.getElementsByClassName("product_line");
		for (var i = 0; i < lines.length; i++) {
			(function(_i){
				// checkLineId(lines[_i]);
				// console.log(_i);
			})(i);
		}
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
