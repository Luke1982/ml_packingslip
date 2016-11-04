<tr class="product_line">
	<!-- Column 1: tools -->
	<td width=5% valign="top" class="lvtCol editview_inv_toolcol" align="right">
		<table width="100%" class="inv_line_tooltable" cellpadding="0" cellspacing="0" border="0">
			<tbody>
				<tr>
					<td><a href="javascript:;" class="icon-move move_line_tool line_tool" title="{$MOD.LBL_MOVE_LINE}"></a></td>
				</tr>
				<tr>
					<td><a href="javascript:;" class="icon-newline new_line_tool line_tool" title="{$MOD.LBL_NEW_LINE}"></a></td>
				</tr>
				<tr>
					<td><a href="javascript:;" class="icon-copyline copy_line_tool line_tool" title="{$MOD.LBL_COPY_LINE}"></a></td>
				</tr>
				<tr>
					<td><a href="javascript:;" class="icon-trash delete_line_tool line_tool" title="{$MOD.LBL_DEL_LINE}"></a></td>
				</tr>
			</tbody>
		</table>
	</td>
	<!-- Column 2: product of service details -->
	<td width=40% valign="top" class="lvtCol editview_inv_detailcol" align="right">
		<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		   <tr>
				<td class="" valign="top">
					<input type="text" name="product_line_name" value="" class="product_line_name" placeholder="{$MOD.LBL_TYPE_TO_SEARCH}" style="width: 100%;" />
				</td>
		   </tr>
		   <tr>
				<td class="setComment">
					<textarea name="product_line_comment" class="product_line_comment" style="width:100%;height:60px"></textarea>
				</td>
		   </tr>
		</table>				
	</td>
	<!-- column 3 - Inventory -->
	<td width=10% class="lvtCol editview_inv_invcol" valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>{$MOD.LBL_DEL_REC}</td>
				<td><input type="number" step="any" name="product_line_del_rec" class="product_line_del_rec" style="width: 70px" value=""></td>
			</tr>				
			<tr>
				<td>{$MOD.LBL_STOCK}</td>
				<td><span class="product_line_in_stock">0</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_PER_UNIT}</td>
				<td><span class="product_line_qty_per_unit">0</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_BACKORDER_LVL}</td>
				<td><span class="product_line_backorder_lvl">0</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_ORDERED}</td>
				<td><span class="product_line_ordered">0</span></td>
			</tr>
		</table>
	</td>
	<!-- column 4 - Purchase info -->
	<td width=10% class="lvtCol editview_inv_purcol" valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>{$MOD.LBL_COSTPRICE}</td>
				<td><input type="number" step="any" name="product_line_costprice" class="product_line_costprice" style="width: 70px" value=""></td>
			</tr>
		</table>
	</td>
	<!-- Column 5: Sales info -->
	<td width=15% valign="top" class="lvtCol editview_inv_salescol" align="right">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td align="left" colspan="1" nowrap>
						<span class="product_line_qty_lbl">{$APP.LBL_QTY} :</span>&nbsp;
						<input name="product_line_qty" type="number" step="any" class="product_line_qty" style="width:50px" value=""/>
					</td>
					<td align="center" colspan="1" nowrap>X</td>
					<td align="right" colspan="1" nowrap>
						<b>{$APP.LBL_LIST_PRICE} : </b><input value="" type="number" step="any" name="product_line_listprice" class="product_line_listprice" style="width:70px">
					</td>
				</tr>
				<tr>
					<td colspan="3" class="sales_col_header">{$APP.LBL_DISCOUNT}</td>
				</tr>						
				<tr>
					<td align="left" colspan="2" nowrap>
						<label>
						<input type="radio" name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="p" checked="checked" cleanline="leavealone">% {$APP.LBL_OF_PRICE}
						</label>
						<br>
						<label>
						<input type="radio" name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="d" cleanline="leavealone">{$APP.LBL_DIRECT_PRICE_REDUCTION}
						</label>
					</td>
					<td align="right" colspan="1" nowrap>
						<b>{$APP.LBL_DISCOUNT} : </b><input type="number" step="any" name="product_line_discount" value="0" class="product_line_discount" style="width: 70px;">
					</td>
				</tr>
				<tr>
					<td align="right" colspan="3" class="product_line_taxes" style="{if $TAX_TYPE eq "group"}display:none;{/if}" nowrap>
						<table width="100%" cellpadding="0" cellspacing="0">
							<tbody>
							<tr>
								<td colspan="2" class="sales_col_header">{$APP.LBL_TAX}</td>
							</tr>									
							{foreach from=$GROUP_TAXES item=tax key=tax_no}
								{if $tax.deleted == 0}
								<tr>
									<td align="right"><b>{$tax.taxlabel} (%) : </b></td>
									<td width="70" align="right"><input type="number" step="any" data-taxname="{$tax.taxname}" name="product_line_tax" class="product_line_tax" cleanline="leavealone" value="{$tax.percentage}" style="width: 70px;"></td>
								</tr>
								{/if}
							{/foreach}										
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>						
		</table>				
	</td>
	<!-- Column 6: totals for product line -->
	<td width=20% class="lvtCol editview_inv_totalscol" align="right">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td align="right" width="50%" nowrap><b>{$APP.LBL_TOTAL} : </b></td>
					<td align="right" width="5%" nowrap><b>{$selected_cur_symbol}</b></td>
					<td align="right" width="45%" nowrap><span class="product_line_gross target" style="width: 70px;">0</span></td>
				</tr>
				<tr>
					<td align="right" width="50%" nowrap><b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </b></td>
					<td align="right" width="5%" nowrap><b>{$selected_cur_symbol}</b></td>
					<td align="right" width="45%" nowrap><span class="product_line_net target" style="width: 70px;">0</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_tax">
					<td align="right" width="50%" nowrap><b>{$APP.LBL_TAX} : </b></td>
					<td align="right" width="5%" nowrap><b>{$selected_cur_symbol}</b></td>
					<td align="right" width="45%" nowrap><span class="product_line_tax_amount target" style="width: 70px;">0</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_net">
					<td align="right" width="50%" nowrap><b>{$APP.LBL_NET_TOTAL} : </b></td>
					<td align="right" width="5%" nowrap><b>{$selected_cur_symbol}</b></td>
					<td align="right" width="45%" nowrap><span class="product_line_after_tax target" style="width: 70px;">0</span></td>
				</tr>
			</tbody>								
		</table>				
	</td>
	<!-- Hidden column that represents all hidden inputs with behind the scenes data -->
	<td width="0" class="productline_props" style="display: none">
		<!-- Hidden inputs line properties -->
		<input type="hidden" class="hdn_product_id" name="hdn_product[{$row_no}][id]" value="0" />
		<input type="hidden" class="hdn_product_isdeleted" name="hdn_product[{$row_no}][deleted]" value="false" />
		<input type="hidden" class="hdn_product_line_id" name="hdn_product[{$row_no}][lineitem_id]" value="" />
		<input type="hidden" class="hdn_product_seq" name="hdn_product[{$row_no}][sequence_no]" value="1" />
		<input type="hidden" class="hdn_product_crm_id" name="hdn_product[{$row_no}][productid]" value="" />
		<input type="hidden" class="hdn_product_qty" name="hdn_product[{$row_no}][quantity]" value="" />
		<input type="hidden" class="hdn_product_units_del_rec" name="hdn_product[{$row_no}][units_delivered_received]" value="" />
		<input type="hidden" class="hdn_product_cost_price" name="hdn_product[{$row_no}][cost_price]" value="" />
		<input type="hidden" class="hdn_product_listprice" name="hdn_product[{$row_no}][listprice]" value="" />
		<input type="hidden" class="hdn_product_discount_per" name="hdn_product[{$row_no}][discount_percent]" value="" />
		<input type="hidden" class="hdn_product_discount_am" name="hdn_product[{$row_no}][discount_amount]" value="" cleanline="leavealone"/>
		<input type="hidden" class="hdn_product_gross" name="hdn_product[{$row_no}][extgross]" value="" />
		<input type="hidden" class="hdn_product_net" name="hdn_product[{$row_no}][extnet]" value="" />
		<input type="hidden" class="hdn_product_tax_am" name="hdn_product[{$row_no}][linetax]" value="" />
		<input type="hidden" class="hdn_product_tax_per" name="hdn_product[{$row_no}][tax_percent]" value="" />
		<input type="hidden" class="hdn_product_total" name="hdn_product[{$row_no}][linetotal]" value="" />
		<input type="hidden" class="hdn_product_entity_type" name="hdn_product[{$row_no}][entity_type]" value="" />
		<textarea class="hdn_product_comment" name="hdn_product[{$row_no}][comment]"></textarea>
		{* Individual taxes *}
		<div class="product_line_hdntaxes">
		{foreach from=$GROUP_TAXES item=tax key=tax_no}
			{if $tax.deleted == 0}
			<input type="hidden" class="hdn_product_{$tax.taxname}" cleanline="leavealone" name="hdn_product[{$row_no}][{$tax.taxname}]" value="{$tax.percentage}">
			{/if}
		{/foreach}
		</div>
	</td>
</tr>