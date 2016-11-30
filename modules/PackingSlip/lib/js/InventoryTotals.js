function InventoryTotals(params) {

	/* Store inputs and values into variables */
	var __domBase = document.getElementById(params.baseId);
	var __subTotal = document.getElementById(params.subTotalId);
	var __totalsInputs = __domBase.getElementsByTagName("input");
	var __lineTotals = [];
	var __taxes = [];
	var __shtaxes = [];

	/* Store an exposed object that stores all current values */
	__currentValues = {};
	this.currentValues = __currentValues;
	
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
		return a;
	}

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
		__currentValues.subTotal = t;
	}

	/*
	 * Collector function that can be called from outside
	 * Only executes all necessary functions in the correct order
	 */
	this.updateTotals = function() {
		__getLineTotals();
		this.calcSubTotal();
		__getDiscountType();
		__getDiscountAbsoluteAmount();
		__getTaxPercSum();
		__calcTotalAfterDiscountAndTax();
		__calcSHTotal();
		__calcAmountAfterSHAmount();
		__calTotalAfterAdjustment();
	}

	/*
	 * Initialization function
	 */
	this.init = function () {
		this.updateTotals();
	}

	/*
	 * Function that returns the currently selected discount type
	 * Either "p" or "d"
	 */
	var __getDiscountType = function() {
		for (var i = 0; i < __totalsInputs.length; i++) {
			if (__totalsInputs[i].name == params.discountRadioName && __totalsInputs[i].checked) {
				__currentValues.discountType = __totalsInputs[i].getAttribute("discounttype");
				return __totalsInputs[i].getAttribute("discounttype");
			}
		}
	}

	/*
	 * Function that uses the current discount type and currensponding
	 * input field to calculate the absolute discount amount to be subtracted
	 * from the subtotal
	 */
	var __getDiscountAbsoluteAmount = function() {
		var totalAfterDiscount = __currentValues.discountType == "p" ? __calcDiscountAmountFromPercentage() : __calcDiscountAmountFromDirect();
		__currentValues.totalAfterDiscount = totalAfterDiscount;
	}

	/*
	 * Function that calculates the discount amount when a percentage
	 * discount was awarded
	 */
	var __calcDiscountAmountFromPercentage = function() {
		var p = document.getElementById("totDiscPe").value;
		var t = __currentValues.subTotal;
		var d = t * (p / 100);
		var r = t - d;

		__currentValues.discountAmount = Number(d);
		return r;
	}

	/*
	 * Function that calculates the discount amount when a direct
	 * discount was awarded
	 */
	var __calcDiscountAmountFromDirect = function() {
		var d = document.getElementById("totDiscAm").value;
		var t = __currentValues.subTotal;
		var r = t - d;

		__currentValues.discountAmount = Number(d);
		return r;
	}

	/*
	 * Function that gets all taxes and stores them into
	 * an internal array
	 */
	var __getTotalsTaxes = function() {
		__taxes = []; // Make sure array is empty before we begin

		for (var i = 0; i < __totalsInputs.length; i++) {
			if (__totalsInputs[i].className.indexOf(params.groupTaxClass) != -1) {
				__taxes.push(Number(__totalsInputs[i].value.formatJSNo()));
			}
		}
	}

	/*
	 * Function that gets the total percentage of tax
	 */
	var __getTaxPercSum = function() {
		__getTotalsTaxes(); // Build the array of current taxes first
		__currentValues.taxPercSum = 0; // Start with zero

		for (var i = 0; i < __taxes.length; i++) {
			__currentValues.taxPercSum += __taxes[i];
		}
	}

	/*
	 * Function that calculates the tax amount with the
	 * current values
	 */
	var __calcTaxAmount = function() {
		var t = __currentValues.totalAfterDiscount;
		var tx = __currentValues.taxPercSum;
		var r = t * (tx / 100);

		__currentValues.taxAmount = r;
		return r;
	}

	/*
	 * Function that calculates the total after discount
	 * and tax
	 */
	var __calcTotalAfterDiscountAndTax = function() {
		var taxAmount = __calcTaxAmount();
		var r = __currentValues.totalAfterDiscount + __currentValues.taxAmount;

		__currentValues.totalAfterDiscountAndTax = r;
		return r;
	}

	/*
	 * Function that gets the Shipping and Handling amount
	 */
	var __getSHAmount = function() {
		var a = Number(document.getElementById("totSHAm").value.formatJSNo());
		__currentValues.SHAmount = a;
		return a;
	}

	/*
	 * Function that gets the Sales and Handling tax percentages
	 * and stores them into an array
	 */
	var __getSHTaxes = function() {
		__shtaxes = []; // Make sure array is empty before we begin

		for (var i = 0; i < __totalsInputs.length; i++) {
			if (__totalsInputs[i].className.indexOf(params.shTaxClass) != -1) {
				__shtaxes.push(Number(__totalsInputs[i].value.formatJSNo()));
				console.log(Number(__totalsInputs[i].value.formatJSNo()));
			}
		}
	}

	/*
	 * Function that gets the total percentage of tax
	 * On sales and handling
	 */
	var __getSHTaxPercSum = function() {
		__getSHTaxes(); // Build the array of current taxes first
		__currentValues.SHTaxSum = 0; // Start with zero

		for (var i = 0; i < __shtaxes.length; i++) {
			__currentValues.SHTaxSum += __shtaxes[i];
		}
	}

	/*
	 * Function that calculates the Sales and Handling tax
	 * absolute amount
	 */
	var __calcSHTaxAbsoluteAmount = function() {
		__getSHTaxPercSum(); // Get the total percentage of SH taxes first
		var r = __currentValues.SHAmount * (__currentValues.SHTaxSum / 100);

		__currentValues.SHTaxAbsoluteAmount = r;
		return r;
	}

	/*
	 * Function that calculates the total Sales and Handling,
	 * Including tax over Sales and Handling
	 */
	var __calcSHTotal = function() {
		__calcSHTaxAbsoluteAmount(); // Make sure we have the total SH tax amount
		var r = __currentValues.SHAmount + __currentValues.SHTaxAbsoluteAmount;

		__currentValues.totalSHIncludingTax = r;
		return r;
	}

	/*
	 * Function that calculates the total after Shipping
	 * and handling have been added.
	 */
	var __calcAmountAfterSHAmount = function() {
		__getSHAmount();
		var r = __currentValues.totalAfterDiscountAndTax + __currentValues.totalSHIncludingTax;

		__currentValues.totalAfterSHAmount = r;
		return r;
	}

	/*
	 * Function that takes the total after discount, tax and
	 * Sales and Handling and adds the adjustment
	 */
	var __calTotalAfterAdjustment = function() {
		var a = document.getElementById("totAdjAm").value;
		var r = __currentValues.totalAfterSHAmount + a;

		__currentValues.grandTotal = r;
		return r;
	}
}