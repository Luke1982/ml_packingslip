function InventoryLine(data) {
	this.props = __getInventoryLineProps();

	function __getInventoryLineProps() {
		return document.getElementsByClassName("productline_props")[data.index];
	}
}