{* Some discount logic *}
{if $product_line.discount_type == 'd'}
	{assign var="show_discount" value=$product_line.disc_am scope=local}
{elseif $product_line.discount_type == 'p'}
	{assign var="show_discount" value=$product_line.disc_perc scope=local}
{/if}

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
					<td><a href="javascript:;" style="display: none" class="icon-trash delete_line_tool line_tool" title="{$MOD.LBL_DEL_LINE}"></a></td>
				</tr>
			</tbody>
		</table>
	</td>
	<!-- Column 2: product of service details -->
	<td width=40% valign="top" class="lvtCol editview_inv_detailcol" align="right">
		<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		   <tr>
				<td class="" valign="top">
					<input type="text" name="product_line_name" value="{if !$CREATEMODE}{$product_line.product_name}{/if}" class="product_line_name" placeholder="{$MOD.LBL_TYPE_TO_SEARCH}" style="width: 100%;" />
				</td>
		   </tr>
		   <tr>
				<td class="setComment">
					<textarea name="product_line_comment" class="product_line_comment" style="width:100%;height:100px">{if !$CREATEMODE}{$product_line.comment}{/if}</textarea>
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
				<td><span class="product_line_in_stock">{if !$CREATEMODE}{$product_line.stock_qty}{else}0{/if}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_PER_UNIT}</td>
				<td><span class="product_line_qty_per_unit">{if !$CREATEMODE}{$product_line.pack_size}&nbsp;/&nbsp;{$product_line.usageunit}{else}0{/if}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_BACKORDER_LVL}</td>
				<td><span class="product_line_backorder_lvl">{if !$CREATEMODE}{$product_line.reorderlevel}{else}0{/if}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_ORDERED}</td>
				<td><span class="product_line_ordered">{if !$CREATEMODE}{$product_line.qtyindemand}{else}0{/if}</span></td>
			</tr>
		</table>
	</td>
	<!-- column 4 - Purchase info -->
	<td width=10% class="lvtCol editview_inv_purcol" valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>{$MOD.LBL_COSTPRICE}</td>
				<td><input type="number" step="any" name="product_line_costprice" class="product_line_costprice" style="width: 70px" value="{if !$CREATEMODE}{$product_line.cost_price}{else}0{/if}"></td>
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
						<input name="product_line_qty" type="number" step="any" class="product_line_qty" style="width:50px" value="{if !$CREATEMODE}{$product_line.qty}{/if}"/>
					</td>
					<td align="center" colspan="1" nowrap>X</td>
					<td align="right" colspan="1" nowrap>
						<b>{$APP.LBL_LIST_PRICE} : </b><input value="{if !$CREATEMODE}{$product_line.list_price}{/if}" type="number" step="any" name="product_line_listprice" class="product_line_listprice" style="width:70px">
					</td>
				</tr>
				<tr>
					<td colspan="3" class="sales_col_header">{$APP.LBL_DISCOUNT}</td>
				</tr>						
				<tr>
					<td align="left" colspan="2" nowrap>
						<label>
						<input type="radio" {if $product_line.discount_type eq 'p'}checked="checked"{/if} name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="p" {if $CREATEMODE}checked="checked"{/if} cleanline="leavealone">% {$APP.LBL_OF_PRICE}
						</label>
						<br>
						<label>
						<input type="radio" {if $product_line.discount_type eq 'd'}checked="checked"{/if} name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="d" cleanline="leavealone">{$APP.LBL_DIRECT_PRICE_REDUCTION}
						</label>
					</td>
					<td align="right" colspan="1" nowrap>
						<b>{$APP.LBL_DISCOUNT} : </b><input type="number" step="any" name="product_line_discount" value="{if !$CREATEMODE}{$show_discount}{/if}" class="product_line_discount" style="width: 70px;">
					</td>
				</tr>
				<tr>
					<td align="right" colspan="3" class="product_line_taxes" style="{if $TAX_TYPE eq "group"}display:none;{/if}" nowrap>
						<table width="100%" class="product_line_taxtable">
							<tbody>
							<tr>
								<td colspan="2" class="sales_col_header">{$APP.LBL_TAX}</td>
							</tr>
							{if $CREATEMODE}
								{* Get system taxes when creating new record *}
								{foreach from=$GROUP_TAXES item=tax key=tax_no}
									{if $tax.deleted == 0}
									<tr>
										<td><b>{$tax.taxlabel} (%) : </b></td>
										<td><input type="number" step="any" data-taxname="{$tax.taxname}" name="product_line_tax" class="product_line_tax" cleanline="leavealone" value="{$tax.percentage}" style="width: 70px;"></td>
									</tr>
									{/if}
								{/foreach}
							{else}
								{* Get taxes saved earlier when editing existing record *}
								{foreach from=$product_line.taxes item=tax key=tax_no}
									{if $tax.deleted == 0}
									<tr>
										<td><b>{$tax.taxlabel} (%) : </b></td>
										<td><input type="number" data-taxname="{$tax.taxname}" name="product_line_tax" class="small product_line_tax" value="{$tax.current_percentage}" style="width: 70px;"></td>
									</tr>
									{/if}
								{/foreach}								
							{/if}								
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
					<td nowrap><b>{$APP.LBL_TOTAL} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_gross target" style="width: 70px;">{if !$CREATEMODE}{$product_line.line_gross_total}{else}0{/if}</span></td>
				</tr>
				<tr>
					<td nowrap><b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_net target" style="width: 70px;">{if !$CREATEMODE}{$product_line.line_net_total}{else}0{/if}</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_tax">
					<td nowrap><b>{$APP.LBL_TAX} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_tax_amount target" style="width: 70px;">{if !$CREATEMODE}{$product_line.line_tax_amount}{else}0{/if}</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_net">
					<td nowrap><b>{$APP.LBL_NET_TOTAL} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_after_tax target" style="width: 70px;">{if !$CREATEMODE}{$product_line.total_after_tax}{else}0{/if}</span></td>
				</tr>
			</tbody>								
		</table>				
	</td>
	<!-- Hidden column that represents all hidden inputs with behind the scenes data -->
	<td width="0" class="productline_props" style="display: none">
		<!-- Hidden inputs line properties -->
		<input type="hidden" class="hdn_product_id" name="hdn_product[{$row_no}][id]" value="{$row_no}" />
		<input type="hidden" class="hdn_product_isdeleted" name="hdn_product[{$row_no}][deleted]" value="false" />
		<input type="hidden" class="hdn_product_line_id" name="hdn_product[{$row_no}][lineitem_id]" value="{if !$CREATEMODE}{$product_line.line_id}{/if}" />
		<input type="hidden" class="hdn_product_seq" name="hdn_product[{$row_no}][sequence_no]" value="{if !$CREATEMODE}{$product_line.seq}{else}1{/if}" />
		<input type="hidden" class="hdn_product_crm_id" name="hdn_product[{$row_no}][productid]" value="{if !$CREATEMODE}{$product_line.product_id}{/if}" />
		<input type="hidden" class="hdn_product_qty" name="hdn_product[{$row_no}][quantity]" value="{if !$CREATEMODE}{$product_line.qty}{/if}" />
		<input type="hidden" class="hdn_product_units_del_rec" name="hdn_product[{$row_no}][units_delivered_received]" value="" />
		<input type="hidden" class="hdn_product_cost_price" name="hdn_product[{$row_no}][cost_price]" value="" />
		<input type="hidden" class="hdn_product_listprice" name="hdn_product[{$row_no}][listprice]" value="{if !$CREATEMODE}{$product_line.list_price}{/if}" />
		<input type="hidden" class="hdn_product_discount_per" name="hdn_product[{$row_no}][discount_percent]" value="" />
		<input type="hidden" class="hdn_product_discount_am" name="hdn_product[{$row_no}][discount_amount]" value="" cleanline="leavealone"/>
		<input type="hidden" class="hdn_product_gross" name="hdn_product[{$row_no}][extgross]" value="{if !$CREATEMODE}{$product_line.line_gross_total}{/if}" />
		<input type="hidden" class="hdn_product_net" name="hdn_product[{$row_no}][extnet]" value="{if !$CREATEMODE}{$product_line.line_net_total}{/if}" />
		<input type="hidden" class="hdn_product_tax_am" name="hdn_product[{$row_no}][linetax]" value="{if !$CREATEMODE}{$product_line.tax_amount}{/if}" />
		<input type="hidden" class="hdn_product_tax_per" name="hdn_product[{$row_no}][tax_percent]" value="" />
		<input type="hidden" class="hdn_product_total" name="hdn_product[{$row_no}][linetotal]" value="{if !$CREATEMODE}{$product_line.total_after_tax}{/if}" />
		<input type="hidden" class="hdn_product_entity_type" name="hdn_product[{$row_no}][entity_type]" value="{if !$CREATEMODE}{$product_line.entity_type}{/if}" />
		<textarea class="hdn_product_comment" name="hdn_product[{$row_no}][comment]">{if !$CREATEMODE}{$product_line.comment}{/if}</textarea>
		{* Individual taxes *}
		<div class="product_line_hdntaxes">
		{if $CREATEMODE}
			{* Get system taxes when creating new record *}
			{foreach from=$GROUP_TAXES item=tax key=tax_no}
				{if $tax.deleted == 0}
				<input type="hidden" class="hdn_product_{$tax.taxname}" cleanline="leavealone" name="hdn_product[{$row_no}][{$tax.taxname}]" value="{$tax.percentage}">
				{/if}
			{/foreach}
		{else}
			{* Get taxes saved earlier when editing existing record *}
			{foreach from=$product_line.taxes item=tax key=tax_no}
				{if $tax.deleted == 0}
				<input type="hidden" class="hdn_product_{$tax.taxname}" cleanline="leavealone" name="hdn_product[{$row_no}][{$tax.taxname}]" value="{$tax.current_percentage}">
				{/if}								
			{/foreach}								
		{/if}		
		</div>
	</td>
</tr>