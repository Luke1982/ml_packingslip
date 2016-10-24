{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}

<!-- Insert yet another jQuery version -->
<script type="text/javascript" src="modules/PackingSlip/lib/js/sortable.min.js"></script>
<!-- Insert Custom PackingSlip CSS file -->
<link rel="stylesheet" type="text/css" href="modules/PackingSlip/lib/css/PackingSlip.css">
<!-- Insert InvetoryLine JS class -->
<script type="text/javascript" src="modules/PackingSlip/lib/js/InventoryLine.js"></script>

<!-- MajorLabel new inventory lines -->

<pre>{$ASSOCIATEDPRODUCTS|print_r}</pre>

<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable editview_inventory_table" id="proTab">
	<tbody id="proBody">

	<!-- Inventory table subheader -->
	<tr class="editview_inventory_subheader">
		<td width=65% colspan="3" valign="top" class="lvtCol editview_inv_item_header" align="left">{$APP.LBL_ITEM_DETAILS}</td>
		<td width=20% colspan="1" valign="top" class="lvtCol editview_inv_item_header" align="left">
			{$APP.LBL_CURRENCY}
			<select class="small" id="inventory_currency" name="inventory_currency">
			{* Currency logic *}
			{foreach item=currency_details key=count from=$CURRENCIES_LIST}
				{if $currency_details.curid == $INV_CURRENCY_ID}
					{assign var=selected_cur_symbol value=$currency_details.currencysymbol}
					<option value="{$currency_details.curid}" selected="selected">{$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})</option>
				{else}
					<option value="{$currency_details.curid}">{$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})</option>
				{/if}
			{/foreach}				
			</select>
		</td>
		<td width=15% colspan="1" valign="top" class="lvtCol editview_inv_item_header" align="left">
			{$APP.LBL_TAX_MODE}
			{* TODO: create a class method for PackingSlip class that gets and sets the taxtype in editview *}
			<select class="small" id="taxtype" name="taxtype">
				<option value="individual">{$APP.LBL_INDIVIDUAL}</option> 
				<option value="group">{$APP.LBL_GROUP}</option>
			<select>
		</td>
	</tr>	

	<!-- Inventory table subheader -->
	<tr class="editview_inventory_subheader">
		<td width=5% valign="top" class="lvtCol editview_inv_toolcol_header" align="left">{$APP.LBL_TOOLS}</td>
		<td width=50% valign="top" class="lvtCol editview_inv_detailcol_header" align="left">{$APP.LBL_ITEM_NAME}</td>
		<td width=10% valign="top" class="lvtCol editview_inv_qtycol_header" align="left">{$APP.LBL_QTY}</td>
		<td width=20% valign="top" class="lvtCol editview_inv_adjustcol_header" align="left">{$APP.LBL_ADJUSTMENT}</td>
		<td width=15% valign="top" class="lvtCol editview_inv_totalscol_header" align="left">{$APP.LBL_TOTAL}</td>
	</tr>

{foreach from=$ASSOCIATEDPRODUCTS item=product_line key=row_no name=name}

{* Some discount logic *}
{if $product_line.discount_type == 'd'}
	{assign var="show_discount" value=$product_line.disc_am scope=local}
{elseif $product_line.discount_type == 'p'}
	{assign var="show_discount" value=$product_line.disc_perc scope=local}
{/if}

		<tr class="product_line">
			<!-- Column 1: tools -->
			<td width=5% valign="top" class="lvtCol editview_inv_toolcol" align="right"></td>
			<!-- Column 2: product of service details -->
			<td width=50% valign="top" class="lvtCol editview_inv_detailcol" align="right">
				<table width="100%"  border="0" cellspacing="0" cellpadding="1">
				   <tr>
						<td class="small" valign="top">
							<input type="text" name="{$product_line.product_name}" value="{$product_line.product_name}" class="small" style="width: 100%;" />
						</td>
				   </tr>
				   <tr>
						<td class="small setComment">
							<textarea name="product_line_comment" class="small product_line_comment" style="width:100%;height:40px">{$product_line.comment}</textarea>
						</td>
				   </tr>
				</table>				
			</td>
			<!-- column 3 - Quantity - starts -->
			<td width=10% class="lvtCol editview_inv_qtycol" valign="top">
				
			</td>
			<!-- column 3 - Quantity - ends -->
			<!-- Column 4 -->
			<td width=20% valign="top" class="lvtCol editview_inv_adjustcol" align="right">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td align="left" colspan="1" style="padding:5px;" nowrap>
								<span class="product_line_qty_lbl">{$APP.LBL_QTY} :</span>&nbsp;
								<input name="product_line_qty" type="number" class="small product_line_qty" style="width:50px" value="{$product_line.qty}"/>
							</td>
							<td align="center" colspan="1" style="padding:5px;" nowrap>X</td>
							<td align="right" colspan="1" style="padding:5px;" nowrap>
								<b>{$APP.LBL_LIST_PRICE} : </b><input value="{$product_line.list_price}" type="number" name="product_line_listprice" class="small product_line_listprice" style="width:70px">
							</td>
						</tr>
						<tr>
							<td align="left" colspan="2" style="padding:5px;" nowrap>
								<input type="radio" {if $product_line.discount_type eq 'p'}checked="checked"{/if} name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="p">% {$APP.LBL_OF_PRICE}
								<br>
								<input type="radio" {if $product_line.discount_type eq 'd'}checked="checked"{/if} name="product_line_disc_type_{$row_no}" class="product_line_disc_radio" value="d">{$APP.LBL_DIRECT_PRICE_REDUCTION}
							</td>
							<td align="right" colspan="1" style="padding:5px;" nowrap>
								<b>{$APP.LBL_DISCOUNT} : </b><input type="text" name="product_line_discount" value="{$show_discount}" class="small product_line_discount" style="width: 70px;">
							</td>
						</tr>
						<tr>
							<td align="right" colspan="3" style="padding:5px;" nowrap>
								<b>{$APP.LBL_TAX} : </b><input type="number" name="product_line_tax" class="small product_line_tax" value="{$product_line.tax1}" style="width: 70px;">
								<b>{$APP.LBL_TAX} : </b><input type="number" name="product_line_tax" class="small product_line_tax" value="{$product_line.tax1}" style="width: 70px;">
								<b>{$APP.LBL_TAX} : </b><input type="number" name="product_line_tax" class="small product_line_tax" value="{$product_line.tax1}" style="width: 70px;">
							</td>
						</tr>
					</tbody>						
				</table>				
			</td>
			<!-- Column 5 -->
			<td width=15% valign="top" class="lvtCol editview_inv_totalscol" align="right">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TOTAL} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_gross" style="width: 70px;">{$product_line.line_gross_total}</span></td>
						</tr>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_net" style="width: 70px;">{$product_line.line_net_total}</span></td>
						</tr>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TAX} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_tax" style="width: 70px;">{$product_line.tax_amount}</span></td>
						</tr>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_NET_TOTAL} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_after_tax" style="width: 70px;">{$product_line.total_after_tax}</span></td>
						</tr>
					</tbody>								
				</table>				
			</td>
			<!-- Hidden column that represents all hidden inputs with behind the scenes data -->
			<td width="0" class="productline_props" style="display: none">
				<input type="hidden" class="hdn_product_isdeleted" name="hdn_product_isdeleted" value="false" />
				<input type="hidden" class="hdn_product_line_id" name="hdn_product_line_id" value="{$product_line.line_id}" />
				<input type="hidden" class="hdn_product_seq" name="hdn_product_seq" value="{$product_line.seq}" />
				<input type="hidden" class="hdn_product_crm_id" name="hdn_product_crm_id" value="{$product_line.product_id}" />
				<input type="hidden" class="hdn_product_entity_type" name="hdn_product_entity_type" value="{$product_line.entity_type}" />
				<input type="hidden" class="hdn_product_qty" name="hdn_product_qty" value="{$product_line.qty}" />
				<input type="hidden" class="hdn_product_listprice" name="hdn_product_listprice" value="{$product_line.list_price}" />
				<input type="hidden" class="hdn_product_discount" name="hdn_product_discount" value="{$show_discount}" />
				<input type="hidden" class="hdn_product_discount_type" name="hdn_product_discount_type" value="{$product_line.discount_type}" />
				<input type="hidden" class="hdn_product_tax_p" name="hdn_product_tax_p" value="{$product_line.tax1}" />
				<input type="hidden" class="hdn_product_gross" name="hdn_product_gross" value="{$product_line.line_gross_total}" />
				<input type="hidden" class="hdn_product_net" name="hdn_product_net" value="{$product_line.line_net_total}" />
				<input type="hidden" class="hdn_product_tax_am" name="hdn_product_tax_am" value="{$product_line.tax_amount}" />
				<input type="hidden" class="hdn_product_total" name="hdn_product_total" value="{$product_line.total_after_tax}" />
			</div>
		</tr>
{/foreach}
	</tbody>
</table>
<!-- Start product totals table -->
<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable editview_inventory_totals">
	<tbody>
		<tr>
			<td width="85%" align="right"><span class="inv_totals_text editview_inventory_subtot_label">{$APP.LBL_NET_TOTAL}</span></td>
			<td width="15%" align="right"><span class="inv_totals_text editview_inventory_subtot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_disc_tot_label">{$APP.LBL_DISCOUNT}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_disc_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_tax_tot_label">{$APP.LBL_TAX}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_tax_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_sh_tot_label">{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_sh_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_shtax_tot_label">{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_shtax_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_adj_tot_label">{$APP.LBL_ADJUSTMENT}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_adj_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inventory_gt_tot_label">{$APP.LBL_GRAND_TOTAL}</span></td>
			<td align="right"><span class="inv_totals_text editview_inventory_gt_tot_value">{* TOTAL HERE *}</span></td>
		</tr>
	</tbody>
</table>
<!-- End MajorLabel new inventory lines -->