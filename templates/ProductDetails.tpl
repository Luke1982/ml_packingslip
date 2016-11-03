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

<!-- MajorLabel new inventory lines: createview-->

{* <pre>{$AVAILABLE_FIELDS|print_r}</pre> *}

<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="crmTable editview_inventory_table" id="proTab">
	<tbody id="proBody">

		<!-- Inventory table subheader -->
		<tr class="editview_inventory_subheader">
			<td width=40% colspan="2" valign="top" class="lvtCol editview_inv_item_header" align="left">{$APP.LBL_ITEM_DETAILS}</td>
			<td width=30% colspan="2" valign="top" class="lvtCol editview_inv_item_header" align="left">
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
			<td width=30% colspan="2" valign="top" class="lvtCol editview_inv_item_header" align="left">
				{$APP.LBL_TAX_MODE}
				<select class="small" id="taxtype" name="taxtype">
					<option value="individual" {if $TAX_TYPE eq "individual"}selected{/if}>{$APP.LBL_INDIVIDUAL}</option> 
					<option value="group" {if $TAX_TYPE eq "group"}selected{/if}>{$APP.LBL_GROUP}</option>
				<select>
				<span class="keyboard_hint">{$MOD.LBL_TAXTYPE_HOTKEY_HINT}</span>
			</td>
		</tr>	

		<!-- Inventory table subheader -->
		<tr class="editview_inventory_subheader">
			<td width=5% valign="top" class="lvtCol editview_inv_toolcol_header inv_col_header" align="left">{$APP.LBL_TOOLS}</td>
			<td width=30% valign="top" class="lvtCol editview_inv_detailcol_header inv_col_header" align="left">{$MOD.LBL_ITEM_INFO}</td>
			<td width=15% valign="top" class="lvtCol editview_inv_invcol_header inv_col_header" align="left">{$MOD.LBL_INVENTORY_INFO}</td>
			<td width=15% valign="top" class="lvtCol editview_inv_purcol_header inv_col_header" align="left">{$MOD.LBL_BUY_INFO}</td>
			<td width=15% valign="top" class="lvtCol editview_inv_salescol_header inv_col_header" align="left">{$MOD.LBL_SALES_INFO}</td>
			<td width=20% valign="top" class="lvtCol editview_inv_totalscol_header inv_col_header" align="left">{$APP.LBL_TOTAL}</td>
		</tr>

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
								<input type="radio" name="product_line_disc_type_0" class="product_line_disc_radio" value="p" checked="checked" cleanline="leavealone">% {$APP.LBL_OF_PRICE}
								</label>
								<br>
								<label>
								<input type="radio" name="product_line_disc_type_0" class="product_line_disc_radio" value="d" cleanline="leavealone">{$APP.LBL_DIRECT_PRICE_REDUCTION}
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
				<input type="hidden" class="hdn_product_id" name="hdn_product[0][id]" value="0" />
				<input type="hidden" class="hdn_product_isdeleted" name="hdn_product[0][deleted]" value="false" />
				<input type="hidden" class="hdn_product_line_id" name="hdn_product[0][lineitem_id]" value="" />
				<input type="hidden" class="hdn_product_seq" name="hdn_product[0][sequence_no]" value="1" />
				<input type="hidden" class="hdn_product_crm_id" name="hdn_product[0][productid]" value="" />
				<input type="hidden" class="hdn_product_qty" name="hdn_product[0][quantity]" value="" />
				<input type="hidden" class="hdn_product_units_del_rec" name="hdn_product[0][units_delivered_received]" value="" />
				<input type="hidden" class="hdn_product_cost_price" name="hdn_product[0][cost_price]" value="" />
				<input type="hidden" class="hdn_product_listprice" name="hdn_product[0][listprice]" value="" />
				<input type="hidden" class="hdn_product_discount_per" name="hdn_product[0][discount_percent]" value="" />
				<input type="hidden" class="hdn_product_discount_am" name="hdn_product[0][discount_amount]" value="" cleanline="leavealone"/>
				<input type="hidden" class="hdn_product_gross" name="hdn_product[0][extgross]" value="" />
				<input type="hidden" class="hdn_product_net" name="hdn_product[0][extnet]" value="" />
				<input type="hidden" class="hdn_product_tax_am" name="hdn_product[0][linetax]" value="" />
				<input type="hidden" class="hdn_product_tax_per" name="hdn_product[0][tax_percent]" value="" />
				<input type="hidden" class="hdn_product_total" name="hdn_product[0][linetotal]" value="" />
				<input type="hidden" class="hdn_product_entity_type" name="hdn_product[0][entity_type]" value="" />
				<textarea class="hdn_product_comment" name="hdn_product[0][comment]"></textarea>
				{* Individual taxes *}
				<div class="product_line_hdntaxes">
				{foreach from=$GROUP_TAXES item=tax key=tax_no}
					{if $tax.deleted == 0}
					<input type="hidden" class="hdn_product_{$tax.taxname}" cleanline="leavealone" name="hdn_product[0][{$tax.taxname}]" value="{$tax.percentage}">
					{/if}
				{/foreach}
				</div>
			</td>
		</tr>
	</tbody>
</table>
<!-- Start product totals table -->
<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable editview_inv_totals">
	<tbody>
		<tr>
			<td width="70%" valign="top" align="right" colspan="1"><span class="inv_totals_text editview_inv_subtot_label">{$APP.LBL_NET_TOTAL}</span></td>
			<td width="30%" valign="top" align="right" colspan="2"><span class="inv_totals_text editview_inv_subtot_value">0</span></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_disc_tot_label">{$APP.LBL_DISCOUNT}</span></td>
			<td align="left" valign="top" colspan="1" width="20%">
				<!-- Discount inputs table -->
				<table width="100%"  border="0" align="center" cellspacing="0">
					<tbody>
						<tr>
							<td align="right" width="50%">
								<input type="radio" name="editview_inv_tot_disctype" id="discount_amount_radio" />
								<input type="number" style="width: 70px" name="editview_inv_disc_am" class="inv_totals_input editview_inv_disc_am" value="">
							</td>
							<td align="right" width="50%">
								<input type="radio" name="editview_inv_tot_disctype" id="discount_perc_radio" />
								<input type="number" style="width: 70px" name="editview_inv_disc_per" class="inv_totals_input editview_inv_disc_per" value="">
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
				<span class="inv_totals_text editview_inv_disc_tot_value">0</span>
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
							<td align="right" width="50%"><input type="number" name="group_tax_{$group_tax_no}" class="group_tax" value="{$group_tax.percentage}" style="width: 70px;"></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</td>			
			<td align="right" valign="bottom" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_tax_tot_value">0</span>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_sh_tot_label">{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES}</span></td>
			<td align="right" valign="top" colspan="1" width="20%">
				<input type="number" name="editview_inv_sh" class="inv_totals_input inv_totals_sh_input" style="width: 70px;" value="{$FINALS.s_h_amount}">
			</td>
			<td align="right" valign="top" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_sh_tot_value">0</span>
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
							<td align="right" width="50%"><input type="number" name="sh_tax_{$sh_tax_no}" class="sh_tax inv_totals_input" value="{$sh_tax.percentage}" style="width: 70px;"></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</td>			
			<td align="right" valign="bottom" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_shtax_tot_value">0</span>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="70%" colspan="1"><span class="inv_totals_text editview_inv_sh_tot_label">{$APP.LBL_ADJUSTMENT}</span></td>
			<td align="right" valign="top" colspan="1" width="20%">
				<input type="number" name="editview_inv_adj" class="inv_totals_input" style="width: 70px;" value="{$FINALS.adjustment}">
			</td>
			<td align="right" valign="top" colspan="1" width="10%">
				<span class="inv_totals_text editview_inv_sh_tot_value">0</span>
			</td>		
		</tr>
		<tr>
			<td align="right"><span class="inv_totals_text editview_inv_gt_tot_label">{$APP.LBL_GRAND_TOTAL}</span></td>
			<td colspan="2" align="right"><span class="inv_totals_value editview_inv_gt_tot_value">0</span></td>
		</tr>
	</tbody>
</table>
<pre>{$GROUP_TAXES|print_r}</pre>
<!-- End MajorLabel new inventory lines -->