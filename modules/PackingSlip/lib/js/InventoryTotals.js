function InventoryTotals(params) {

	/* Store inputs and values into variables */
	var __domBase = document.getElementById(params.baseId);
	var __subTotal = document.getElementById(params.subTotalId);
	var __lineTotals = [];
	
	/*
	 * Gets all the linetotals and stores them into an
	 * internal property (__lineTotals)
	 */
	var __getLineTotals = function () {
		var a = [];
		var b = document.getElementsByClassName(params.lineTotalClass);

		for (var i = 0; i < b.length; i++) {
			a.push(Number(b[i].innerHTML.formatJSNo()));
		}

		__lineTotals = a;
	}

	// Get the linetotals for the first time
	__getLineTotals();

	/*
	 * Exposed function: resets the __lineTotals to represent current
	 * values and sets the subtotal of the totals table to the sum
	 * of all the linetotals
	 */
	this.calcSubTotal = function() {
		__getLineTotals();

		var t = 0;
		for (var i = 0; i < __lineTotals.length; i++) {
			t += __lineTotals[i];
		}

		__subTotal.innerHTML = t.formatMoney();
	}

}