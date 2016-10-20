function InventoryLine(data) {
	this.propInputs = __getInventoryLineProps();
	this.props = {};

	function __getInventoryLineProps() {
		return document.getElementsByClassName("productline_props")[data.index].getElementsByTagName("input");
	}

	this.__setLineProperty = function(propertyName, propertyValue) {
		this.props[propertyName] = propertyValue;
	}

	this.setProps = function() {
		for (var i = 0; i < this.propInputs.length; i++) {
			var propName = this.propInputs[i].className.replace("hdn_product_", "");
			var propVal = this.propInputs[i].value;
			this.__setLineProperty(propName, propVal);
		}
	}
}