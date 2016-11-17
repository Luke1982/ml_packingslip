<!-- Inventory table productline (detailview) -->
<tr class="detailview_inventoryline">
	<td width=5% valign="top" class="lvtCol detailview_inv_toolcol inv_col" align="left">{$APP.LBL_TOOLS}</td>
	<td width=30% valign="top" class="lvtCol detailview_inv_detailcol inv_col" align="left">
		<h4 class="detailview_invline_productname">{$product_line.product_name}</h4>
		<div class="detailview_invline_comment">{$product_line.comment}</div>
	</td>
	<td width=15% valign="top" class="lvtCol detailview_inv_invcol inv_col" align="left">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>{$MOD.LBL_DEL_REC}</td>
				<td>{$product_line.units_del_rec}</td>
			</tr>				
			<tr>
				<td>{$MOD.LBL_STOCK}</td>
				<td><span class="product_line_in_stock">{$product_line.stock_qty}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_PER_UNIT}</td>
				<td><span class="product_line_qty_per_unit">{$product_line.qty_per_unit}&nbsp;/&nbsp;{$product_line.usageunit}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_BACKORDER_LVL}</td>
				<td><span class="product_line_backorder_lvl">{$product_line.reorderlevel}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_ORDERED}</td>
				<td><span class="product_line_ordered">{$product_line.qtyindemand}</span></td>
			</tr>
		</table>		
	</td>
	<td width=15% valign="top" class="lvtCol detailview_inv_purcol inv_col" align="left">{$MOD.LBL_BUY_INFO}</td>
	<td width=15% valign="top" class="lvtCol detailview_inv_salescol inv_col" align="left">{$MOD.LBL_SALES_INFO}</td>
	<td width=20% valign="top" class="lvtCol detailview_inv_totalscol inv_col" align="left">{$APP.LBL_TOTAL}</td>
</tr>