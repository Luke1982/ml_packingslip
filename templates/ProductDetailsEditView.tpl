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

<!-- Insert 3rd party libraries -->
<script type="text/javascript" src="modules/PackingSlip/lib/js/sortable.min.js"></script>
<script type="text/javascript" src="modules/PackingSlip/lib/js/awesomplete.min.js"></script>
<link rel="stylesheet" type="text/css" href="modules/PackingSlip/lib/css/awesomplete.css">
<link rel="stylesheet" type="text/css" href="modules/PackingSlip/lib/css/PackingSlip.css">
<!-- Insert Custom Module CSS file -->
<link rel="stylesheet" type="text/css" href="modules/PackingSlip/lib/css/PackingSlip.css">
<!-- Insert InventoryLine JS class -->
<script type="text/javascript" src="modules/PackingSlip/lib/js/InventoryLine.js"></script>
<script type="text/javascript">var taxType = "{$TAX_TYPE}";</script>

<!-- MajorLabel new inventory lines -->

{* <pre>{$ASSOCIATEDPRODUCTS|print_r}</pre> *}

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
			<select class="small" id="taxtype" name="taxtype">
				<option value="individual" {if $TAX_TYPE eq "individual"}selected{/if}>{$APP.LBL_INDIVIDUAL}</option> 
				<option value="group" {if $TAX_TYPE eq "group"}selected{/if}>{$APP.LBL_GROUP}</option>
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
			<td width=5% valign="top" class="lvtCol editview_inv_toolcol" align="right">
				<table width="100%" class="inv_line_tooltable">
					<tbody>
						<tr>
							<td><a href="javascript:;" class="move_line_tool">Move Line</a></td>
						</tr>
						<tr>
							<td><a href="javascript:;" class="new_line_tool">New Line</a></td>
						</tr>
						<tr>
							<td><a href="javascript:;" class="copy_line_tool">Copy line</a></td>
						</tr>
						<tr>
							<td><a href="javascript:;" class="delete_line_tool">Delete line</a></td>
						</tr>
					</tbody>
				</table>
			</td>
			<!-- Column 2: product of service details -->
			<td width=50% valign="top" class="lvtCol editview_inv_detailcol" align="right">
				<table width="100%"  border="0" cellspacing="0" cellpadding="1">
				   <tr>
						<td class="small" valign="top">
							<input type="text" name="product_line_name" value="{$product_line.product_name}" class="small product_line_name" style="width: 100%;" />
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
							<td align="right" colspan="3" class="product_line_taxes" style="padding:5px;{if $TAX_TYPE eq "group"}display:none;{/if}" nowrap>
								<table width="100%" cellpadding="0" cellspacing="0">
									<tbody>
									<pre>{$product_line.taxes|print_r}</pre>
									{foreach from=$product_line.taxes item=tax key=tax_no}
										{if $tax.deleted == 0}
										<tr>
											<td align="right"><b>{$tax.taxlabel} (%) : </b></td>
											<td width="70" style="padding: 5px 0;" align="right"><input type="number" name="product_line_tax" class="small product_line_tax" value="{$tax.current_percentage}" style="width: 70px;"></td>
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
			<!-- Column 5 -->
			<td width=15% valign="middle" class="lvtCol editview_inv_totalscol" align="right">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TOTAL} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_gross target" style="width: 70px;">{$product_line.line_gross_total}</span></td>
						</tr>
						<tr>
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_net target" style="width: 70px;">{$product_line.line_net_total}</span></td>
						</tr>
						<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_tax">
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_TAX} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_tax_amount target" style="width: 70px;">{$product_line.tax_amount}</span></td>
						</tr>
						<tr {if $TAX_TYPE eq "group"}style="display:none"{/if} class="product_line_totals_net">
							<td align="right" width="50%" style="padding:5px;" nowrap><b>{$APP.LBL_NET_TOTAL} : </b></td>
							<td align="right" width="5%" style="padding:5px;" nowrap><b>{$selected_cur_symbol}</b></td>
							<td align="right" width="45%" style="padding:5px;" nowrap><span class="product_line_after_tax target" style="width: 70px;">{$product_line.total_after_tax}</span></td>
						</tr>
					</tbody>								
				</table>				
			</td>
			<!-- Hidden column that represents all hidden inputs with behind the scenes data -->
			<td width="0" class="productline_props" style="display: none">
				<!-- MajorLabel implementation of hidden inputs -->
				<input type="hidden" class="hdn_product_id" name="hdn_product_id" value="{$row_no}" />
				<input type="hidden" class="hdn_product_isdeleted" name="hdn_product_isdeleted" value="false" />
				<input type="hidden" class="hdn_product_line_id" name="hdn_product_line_id" value="{$product_line.line_id}" />
				<input type="hidden" class="hdn_product_seq" name="hdn_product_seq" value="{$product_line.seq}" />
				<input type="hidden" class="hdn_product_crm_id" name="hdn_product_crm_id" value="{$product_line.product_id}" />
				<input type="hidden" class="hdn_product_entity_type" name="hdn_product_entity_type" value="{$product_line.entity_type}" />
				<input type="hidden" class="hdn_product_qty" name="hdn_product_qty" value="{$product_line.qty}" />
				<input type="hidden" class="hdn_product_listprice" name="hdn_product_listprice" value="{$product_line.list_price}" />
				<input type="hidden" class="hdn_product_discount" name="hdn_product_discount" value="{$show_discount}" />
				<input type="hidden" class="hdn_product_discount_type" name="hdn_product_discount_type" value="{$product_line.discount_type}" />
				{* <input type="hidden" class="hdn_product_tax_p" name="hdn_product_tax_p" value="{$product_line.tax1}" /> *}
				<input type="hidden" class="hdn_product_gross" name="hdn_product_gross" value="{$product_line.line_gross_total}" />
				<input type="hidden" class="hdn_product_net" name="hdn_product_net" value="{$product_line.line_net_total}" />
				<input type="hidden" class="hdn_product_tax_am" name="hdn_product_tax_am" value="{$product_line.tax_amount}" />
				<input type="hidden" class="hdn_product_total" name="hdn_product_total" value="{$product_line.total_after_tax}" />
				<textarea class="hdn_product_comment">{$product_line.comment}</textarea>
				<!-- Vtiger implementation of hidden inputs -->
				{assign var="row_no_plus" value=$row_no+1}
				<input id="deleted{$row_no_plus}" name="deleted{$row_no_plus}" value="0" type="hidden">
				{if $product_line.line_id == "0"}{assign var="line_id" value=""}{else}{assign var=$product_line.line_id value=""}{/if}
				<input id="lineitem_id{$row_no_plus}" name="lineitem_id{$row_no_plus}" value="{$line_id}" type="hidden">
				<input id="hdnProductId{$row_no_plus}" name="hdnProductId{$row_no_plus}" value="{$product_line.product_id}" type="hidden">
				<input id="lineItemType{$row_no_plus}" name="lineItemType{$row_no_plus}" value="{$product_line.entity_type}" type="hidden">
				<textarea id="comment{$row_no_plus}" name="comment{$row_no_plus}">{$product_line.comment}</textarea>
				<input id="qty{$row_no_plus}" name="qty{$row_no_plus}" value="{$product_line.qty}" type="hidden">
				<input id="listPrice{$row_no_plus}" name="listPrice{$row_no_plus}" value="{$product_line.list_price}" type="hidden">
				<input id="discount_type{$row_no_plus}" name="discount_type{$row_no_plus}" value="{$product_line.discount_type}" type="hidden">
				<input id="discount_percentage{$row_no_plus}" name="discount_percentage{$row_no_plus}" value="{$product_line.discount_perc}" type="hidden">
				<input id="discount_amount{$row_no_plus}" name="discount_amount{$row_no_plus}" value="{$product_line.discount_am}" type="hidden">
				{foreach from=$product_line.taxes item=tax key=tax_no}
					{if $tax.deleted == 0}
				<input id="tax{$tax.taxid}_percentage{$row_no_plus}" name="tax{$tax.taxid}_percentage{$row_no_plus}" value="{$tax.current_percentage}" type="hidden">
					{/if}
				{/foreach}
				<input id="hdnTaxTotal{$row_no_plus}" name="hdnTaxTotal{$row_no_plus}" value="{$product_line.tax_amount}" type="hidden">
			</div>
		</tr>
{/foreach}
	</tbody>
</table>
<!-- Start product totals table -->
<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable editview_inv_totals">
	<tbody>
		<tr>
			<td width="70%" valign="top" align="right" colspan="1"><span class="inv_totals_text editview_inv_subtot_label">{$APP.LBL_NET_TOTAL}</span></td>
			<td width="30%" valign="top" align="right" colspan="2"><span class="inv_totals_text editview_inv_subtot_value">{$FINALS.subtotal}</span></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_disc_tot_label">{$APP.LBL_DISCOUNT}</span></td>
			<td align="left" valign="top" colspan="1" width="20%">
				<!-- Discount inputs table -->
				<table width="100%"  border="0" align="center" cellspacing="0">
					<tbody>
						<tr>
							<td align="right" width="50%">
								<input type="radio" name="editview_inv_tot_disctype" id="discount_amount_radio" {if $FINALS.discount_type eq 'd'}checked="checked"{/if} />
								<input type="number" style="width: 70px" name="editview_inv_disc_am" class="small inv_totals_input editview_inv_disc_am" value="{$FINALS.discount_amount}">
							</td>
							<td align="right" width="50%">
								<input type="radio" name="editview_inv_tot_disctype" id="discount_perc_radio" {if $FINALS.discount_type eq 'p'}checked="checked"{/if} />
								<input type="number" style="width: 70px" name="editview_inv_disc_per" class="small inv_totals_input editview_inv_disc_per" value="{$FINALS.discount_percent}">
							</td>
						</tr>
						<tr>
							<td align="right" width="50%">{$APP.LBL_DIRECT_PRICE_REDUCTION}</td>
							<td align="right" width="50%">% {$APP.LBL_OF_PRICE}</td>
						</tr>
					</tbody>
				</table>
			</td>
			<td align="right" valign="top" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_disc_tot_value">{$FINALS.total_discount}</span>
			</td>
		</tr>
		<tr {if $TAX_TYPE eq "individual"}style="display:none"{/if} id="inv_totals_group_taxes">
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_tax_tot_label">{$APP.LBL_TAX}</span></td>
			<td align="left" valign="top" colspan="1" width="20%">
				<!-- Taxes inputs table -->
				<table width="100%"  border="0" align="center" cellspacing="0">
					<tbody>
						{foreach from=$GROUP_TAXES item=group_tax key=group_tax_no}
						<tr>
							<td align="right" width="50%"><b>{$group_tax.taxlabel} (%) :</b></td>
							<td align="right" width="50%"><input type="number" name="group_tax_{$group_tax_no}" class="small group_tax" value="{$group_tax.percentage}" style="width: 70px;"></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</td>			
			<td align="right" valign="bottom" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_tax_tot_value">{$FINALS.exciseduty}</span>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_sh_tot_label">{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES}</span></td>
			<td align="right" valign="top" colspan="1" width="20%">
				<input type="number" name="editview_inv_sh" class="small inv_totals_input inv_totals_sh_input" style="width: 70px;" value="{$FINALS.s_h_amount}">
			</td>
			<td align="right" valign="top" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_sh_tot_value">{$FINALS.s_h_amount}</span>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_shtax_tot_label">{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING}</span></td>
			<td align="left" valign="top" colspan="1" width="20%">
				<!-- S&H taxes inputs table -->
				<table width="100%"  border="0" align="center" cellspacing="0">
					<tbody>
						{foreach from=$SH_TAXES item=sh_tax key=sh_tax_no}
						<tr>
							<td align="right" width="50%"><b>{$sh_tax.taxlabel} (%) :</b></td>
							<td align="right" width="50%"><input type="number" name="sh_tax_{$sh_tax_no}" class="small sh_tax inv_totals_input" value="{$sh_tax.percentage}" style="width: 70px;"></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</td>			
			<td align="right" valign="bottom" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_shtax_tot_value">{$FINALS.salescommission}</span>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_sh_tot_label">{$APP.LBL_ADJUSTMENT}</span></td>
			<td align="right" valign="top" colspan="1" width="20%">
				<input type="number" name="editview_inv_adj" class="inv_totals_input small" style="width: 70px;" value="{$FINALS.adjustment}">
			</td>
			<td align="right" valign="top" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_sh_tot_value">{$FINALS.adjustment}</span>
			</td>		
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inv_gt_tot_label">{$APP.LBL_GRAND_TOTAL}</span></td>
			<td colspan="2" align="right"><span class="inv_totals_value editview_inv_gt_tot_value">{$FINALS.total}</span></td>
		</tr>
	</tbody>
</table>
<pre>{$SH_TAXES|print_r}</pre>
<!-- End MajorLabel new inventory lines -->