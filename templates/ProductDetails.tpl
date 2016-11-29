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
<!-- Set some global JS vars -->
<script type="text/javascript">
	var taxType = "{$TAX_TYPE}";
	var decSep = "{$DECIMAL_SEP}";
	var grpSep = "{$GROUP_SEP}";
	var decimals = "{$DECIMALS}";
</script>

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
		{* 	Logic that calls a productline depending in products being available or not,
			calls the line as many times as needed (1 for createview). *}
		{if $AVAILABLE_PRODUCTS neq true}
			{assign var="CREATEMODE" value=true}
			{assign var="ASSOCIATEDPRODUCTS" value=','|explode:'0'} {* Set an empty array with one key for createview *}
		{/if}
			{* <pre>{$ASSOCIATEDPRODUCTS|print_r}</pre> *}

		{foreach from=$ASSOCIATEDPRODUCTS item=product_line key=row_no name=name}
			{include file="modules/PackingSlip/ProductLine.tpl"}
		{/foreach}

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
<!-- End MajorLabel new inventory lines -->