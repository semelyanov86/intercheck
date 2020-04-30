{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
<style>
	fieldset {
		display: block;
		margin-left: 2px;
		margin-right: 2px;
		margin-bottom:10px;
		padding-top: 0.35em;
		padding-bottom: 0.625em;
		padding-left: 0.75em;
		padding-right: 0.75em;
		border: 1px solid #e5e5e5;;
	}
	legend {
		display: block;
		width: auto;
		padding: 3px;
		margin-bottom: 3px;
		font-size: 13.5px;
		line-height: 10px;
		color: #333333;
		border: 0;
	}
	.quick_link .col-md-3 {

		height: 24px !important;
	}
</style>
{literal}
<script type="text/javascript">
	var urlForFilter = {'url':'index.php?module=VDUsers&view=EditAjax&source_module=Users'};
</script>
{/literal}
{strip}
<input type="hidden" id="vdviewId" value="{$CVID}" />
<input type="hidden" id="view" value="{$VIEW}" />
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="alphabetSearchKey" value= "{$MODULE_MODEL->getAlphabetSearchField()}" />
<input type="hidden" id="Operator" value="{$OPERATOR}" />
<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<div class="col-sm-12 col-xs-12">
		<span class="pull-right">
			{if $USER_MODEL->isAdminUser()}
				<button class="btn " onclick="runClass(urlForFilter);"id="createFilter" data-url="index.php?module=VDUsers&view=EditAjax&source_module=Users"><i class="fa fa-cog" alt="{vtranslate('LBL_SETTINGS', $MODULE)}" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></i></button>
			{/if}
			{if $EDITUSER eq 1}
		<a href="index.php?module=Users&parent=Settings&view=Edit" class="btn btn-success">Add User</a></span>
	{/if}
	</div>
		<div class="col-sm-12 col-xs-12">

		<fieldset>
	<legend>{vtranslate('LBL_QUICK_LINK',$MODULE)}</legend>
	<div class="quick_link row-fluid">
	{assign var=links value=$ROLES}
		<div>
		{foreach item=link from=$links}
			{assign var=role value=$link->getValue()}
			{if empty($ALLOWED_ROLES) or in_array($role.id, $ALLOWED_ROLES)}
			<div class="col-md-3">
				<a class="vdusers-role" href="#{$role.id}" style="border-bottom: 1px dotted #ccc;">
				{if $role.id == $ROLEID}
					<strong>{$role.title}</strong>
				{else}
					{$role.title}
				{/if}
				</a>
			</div>
			{/if}
		{/foreach}
		</div>
	</div>
</fieldset>


<div class="contents-topscroll noprint">
	<div class="topscroll-div">
		&nbsp;
	 </div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll">
	<div class="bottomscroll-div">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<span class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
		<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
		<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
	</span>

		{assign var=colspan value=2}
	<table class="table table-bordered table-striped ">
		<thead>
			<tr class="listViewHeaders">
				<th width="2%">

				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				{assign var=colspan value=$colspan+1}
				<th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if}>
					<a href="javascript:void(0);" class="listViewHeaderValue" >{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
						&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}<img class="{$SORT_IMAGE} icon-white">{/if}</a>
				</th>
				{/foreach}
			</tr>
		</thead>
        {if $MODULE_MODEL->isQuickSearchEnabled()}
        <tr>
            <td></td>
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
             <td>
                 {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
				 {if isset($SEARCH_DETAILS[$LISTVIEW_HEADER->getName()])}
					 {assign var=SEARCH_INFO value=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]}
					 {else}
                     {assign var=SEARCH_INFO value=""}
				 {/if}
				 {if ($FIELD_UI_TYPE_MODEL->getListSearchTemplateName() neq 'uitypes/UserRoleFieldSearchView.tpl')}
                {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME)
                    FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$CURRENT_USER_MODEL}
					 {else}
					 {include file=vtemplate_path('uitypes/UserRole.tpl',$MODULE_NAME)
					 FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$CURRENT_USER_MODEL}
                    {/if}
             </td>
			{/foreach}
			<td>
				<button class="btn" id="btnVDUsersSearch">{vtranslate('LBL_SEARCH', $MODULE )}</button>
			</td>
        </tr>
        {/if}

        {foreach item=role from=$ROLES}
		{assign var=entry value=$role->getValue()}
		{if $entry.num > 0 }
		<tr>
			<td colspan="{$colspan}" style="background: #FF9B59; padding-left: {($entry.level + 1) * 10}px;">
				<a id="{$entry.id}"></a>
				<strong>
					{$entry.title}
				</strong>
			</td>
		</tr>
		{foreach item=LISTVIEW_ENTRY from=$entry.users name=listview}
		<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
			<td width="5%"></td>
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
			{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
			<td class="listViewEntryValue" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" nowrap>
				{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4') and $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true }
					{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
				{else if $LISTVIEW_HEADER->get('uitype') eq '72'}
					{assign var=CURRENCY_SYMBOL_PLACEMENT value={$CURRENT_USER_MODEL->get('currency_symbol_placement')}}
					{if $CURRENCY_SYMBOL_PLACEMENT eq '1.0$'}
						{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}{$LISTVIEW_ENTRY->get('currencySymbol')}
					{else}
						{$LISTVIEW_ENTRY->get('currencySymbol')}{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
					{/if}
                {else if $LISTVIEW_HEADER->get('uitype') eq '104'}
                    {str_replace('VDUsers','Users',$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}
				{else}
                    {if $LISTVIEW_HEADER->getFieldDataType() eq 'double'}
                        {decimalFormat($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}
                    {else}
                        {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                    {/if}
				{/if}
			</td>
			{if $LISTVIEW_HEADER@last}
			<td nowrap class="{$WIDTHTYPE}">{if $EDITUSER eq 1}<a href="index.php?module=Users&parent=Settings&view=Edit&record={$LISTVIEW_ENTRY->getId()}" ><i class="fa fa-pencil"></i></a>{/if}</td>
			{/if}
			{/foreach}

		</tr>
        {/foreach}
		{/if}
		{/foreach}
	</table>

<!--added this div for Temporarily -->
{if $LISTVIEW_ENTRIES_COUNT eq '0'}
	<table class="emptyRecordsDiv">
		<tbody>
			<tr>
				<td>
					{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
                    {* SalesPlatform.ru begin *}
                    {vtranslate('LBL_NOT_FOUND', $MODULE)} {vtranslate($MODULE, $MODULE)}.{if $IS_MODULE_EDITABLE} {vtranslate('LBL_CREATE')} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{vtranslate($SINGLE_MODULE, $MODULE)}</a>{/if}
					{*{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}.{if $IS_MODULE_EDITABLE} {vtranslate('LBL_CREATE')} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{vtranslate($SINGLE_MODULE, $MODULE)}</a>{/if}*}
                    {* SalesPlatform.ru end *}
				</td>
			</tr>
		</tbody>
	</table>
{/if}
</div>
</div>
	</div>
{/strip}
