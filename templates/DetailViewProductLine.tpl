{* Some discount logic *}
{if $line.discount_type == 'd'}
	{assign var="show_discount" value=$line.discount_amount scope=local}
{elseif $line.discount_type == 'p'}
	{assign var="show_discount" value=$line.discount_percent scope=local}
{/if}
{* Create the link for the product/service title *}
{if $line.entity_type == 'Products'}
{assign var="product_link" value='index.php?module=Products&parenttab=Inventory&action=DetailView&record='}
{assign var="product_link" value=$product_link|cat:$line.product_id}
{else}
{assign var="product_link" value='index.php?module=Services&parenttab=Inventory&action=DetailView&record='}
{assign var="product_link" value=$product_link|cat:$line.product_id}
{/if}
<!-- Inventory table productline (detailview) -->
<tr class="detailview_inventoryline">
	<!-- Column 2: Item info -->
	<td width=25% valign="top" class="lvtCol detailview_inv_detailcol inv_col" align="left">
		<h4 class="detailview_invline_productname"><a target="_blank" href="{$product_link}">{$line.productname}</a></h4>
		<div class="detailview_invline_comment">{$line.description|html_entity_decode|truncate:'250'}</div>
	</td>
	<!-- Column 3: Inventory info -->
	<td width=20% valign="top" class="lvtCol detailview_inv_invcol inv_col" align="left">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td><span class="dtlv_product_line_delrec_lbl">{$MOD.LBL_DEL_REC}</span></td>
				<td><span class="dtlv_product_line_delrec">{$line.units_delivered_received}</span></td>
			</tr>				
			<tr>
				<td><span class="dtlv_product_line_stock_lbl">{$MOD.LBL_STOCK}</span></td>
				<td><span class="dtlv_product_line_stock">{$line.stock_quantity}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_PER_UNIT}</td>
				<td><span class="product_line_qty_per_unit">{$line.qty_per_unit}&nbsp;/&nbsp;{$line.usageunit}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_BACKORDER_LVL}</td>
				<td><span class="product_line_backorder_lvl">{$line.reorderlevel}</span></td>
			</tr>
			<tr>
				<td>{$MOD.LBL_QTY_ORDERED}</td>
				<td><span class="product_line_ordered">{$line.qtyindemand}</span></td>
			</tr>
		</table>		
	</td>
	<!-- Column 4: Purchase info -->
	<td width=20% valign="top" class="lvtCol detailview_inv_purcol inv_col" align="left">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>{$MOD.LBL_COSTPRICE} (<span class="currency_symbol">{$selected_cur_symbol}</span>)</td>
				<td><span class="dtlv_product_line_costprice">{$line.cost_price}</span></td>
			</tr>
		</table>		
	</td>
	<!-- Column 5: Sales info -->
	<td width=20% valign="top" class="lvtCol detailview_inv_salescol inv_col" align="left">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td align="left" colspan="1" nowrap>
						<span class="dtlv_product_line_qty_lbl">{$APP.LBL_QTY} :</span>
					</td>
					<td>
						<span class="dtlv_product_line_qty">{$line.quantity}</span>
					</td>
				</tr>
				<tr>
					<td align="left" nowrap>
						<span class="dtlv_product_line_disc_lbl">{$APP.LBL_LIST_PRICE} (<span class="currency_symbol">{$selected_cur_symbol}</span>)</span>
					</td>
					<td align="right" nowrap>
						<span class="dtlv_product_line_disc">{$line.listprice}</span>
					</td>				
				</tr>
				<tr>
					<td colspan="2" class="sales_col_header">{$APP.LBL_DISCOUNT}</td>
				</tr>						
				<tr>
					<td align="left" nowrap>
						<span class="dtlv_product_line_disc_lbl">{if $line.discount_type eq 'p'}% {$APP.LBL_OF_PRICE}{else}{$APP.LBL_DIRECT_PRICE_REDUCTION}{/if}</span>
					</td>
					<td align="right" nowrap>
						<span class="dtlv_product_line_disc">{$show_discount}</span>
					</td>
				</tr>
				<tr>
					<td align="right" colspan="3" class="product_line_taxes" style="{if $TAX_TYPE eq "group"}display:none;{/if}" nowrap>
						<table width="100%" class="dtlv_product_line_taxtable">
							<tbody>
								<tr>
									<td colspan="2" class="sales_col_header">{$APP.LBL_TAX}</td>
								</tr>
								{* To-do get taxtype to decide whether to show individual tax or not *}
								<tr>
									<td>
										<span class="dtlv_product_line_tax_lbl">{$APP.LBL_TAX}</span>
									</td>
									<td>
										<span class="dtlv_product_line_tax">{$line.tax_percent}&nbsp;%</span>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>						
		</table>
	</td>
	<!-- Column 6: Totals -->
	<td width=15% valign="top" class="lvtCol detailview_inv_totalscol inv_col" align="left">
		<table width="100%" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td nowrap><b>{$APP.LBL_TOTAL} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_gross target" style="width: 70px;">{$line.extgross}</span></td>
				</tr>
				<tr>
					<td nowrap><b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_net target" style="width: 70px;">{$line.extnet}</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_tax">
					<td nowrap><b>{$APP.LBL_TAX} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_tax_amount target" style="width: 70px;">{$line.linetax}</span></td>
				</tr>
				<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_net">
					<td nowrap><b>{$APP.LBL_NET_TOTAL} : </b></td>
					<td nowrap><b>{$selected_cur_symbol}</b></td>
					<td nowrap><span class="product_line_after_tax target" style="width: 70px;">{$line.linetotal}</span></td>
				</tr>
			</tbody>
		</table>		
	</td>
</tr>