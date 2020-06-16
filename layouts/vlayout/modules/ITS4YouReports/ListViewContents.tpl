{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{strip}
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="numberOfEntries" value= "{$LISTVIEW_ENTRIES_COUNT}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}" >
<input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
<div id="selectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
</div>
<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
</div>

<div class="listViewEntriesDiv contents-bottomscroll">
	<div class="bottomscroll-div">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<p class="listViewLoadingMsg hide">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				<th class="{$WIDTHTYPE}"><input type="checkbox" id="listViewEntriesMainCheckBox"></th>
				{foreach key=LISTVIEW_HEADER_KEY item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					<th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if} class="{$WIDTHTYPE}">
                                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER_KEY}">{$LISTVIEW_HEADER.name}
						&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}<img class="{$SORT_IMAGE} icon-white">{/if}</a>
					</th>
				{/foreach}
			</tr>
		</thead>
                {* //ITS4YOU-CR SlOl | 3.9.2015 14:48 *}
                <tr>
                    <td><button class="btn" data-trigger="listSearch1">{vtranslate('LBL_SEARCH', $MODULE )}</button></td>
                        {foreach key=LISTVIEW_NAME item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <td>
                                <div class="row-fluid">
                                {if $LISTVIEW_HEADER.type == "user"}
                                    <select class="select2 listSearchContributor span9" name="{$LISTVIEW_NAME}" multiple style="width:150px;" data-fieldinfo=''>
                                        {foreach item=PICKLIST_ARR key=PICKLIST_KEY from=$LISTVIEW_HEADER.picklistValues}
                                            <option value="{$PICKLIST_ARR.0}" {$PICKLIST_ARR.2} >{$PICKLIST_ARR.1}</option>
                                        {/foreach}
                                    </select>
                                {else if $LISTVIEW_HEADER.type == "picklist"}
                                    <select class="select2 listSearchContributor span9" name="{$LISTVIEW_NAME}" multiple style="width:150px;" data-fieldinfo=''>
                                        {foreach item=PICKLIST_ARR key=PICKLIST_KEY from=$LISTVIEW_HEADER.picklistValues}
                                            <option value="{$PICKLIST_ARR.0}" {$PICKLIST_ARR.2} >{vtranslate($PICKLIST_ARR.1,$MODULE)}</option>
                                        {/foreach}
                                    </select>
                                {else}
                                    <input type="text" name="{$LISTVIEW_NAME}" class="span9 listSearchContributor" value="{$LISTVIEW_HEADER.searchValue}" data-fieldinfo=''/>
                                {/if}
                                </div>
                                {*
                                {if $LISTVIEW_NAME == "reporttype"}
                                    {*
                                    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
                                    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
                                    * }
                                    <div class="row-fluid">
                                        {*
                                        <select class="select2 listSearchContributor span9" name="{$FIELD_MODEL->get('name')}" multiple style="width:150px;" data-fieldinfo='{$FIELD_INFO|escape}'>
                                            {foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
                                                <option value="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "") } selected{/if}>{$PICKLIST_LABEL}</option>
                                            {/foreach}
                                        </select>
                                        * }
                                    </div>
                                {else if $LISTVIEW_NAME == "tablabel"}
                                    
                                {else if $LISTVIEW_NAME == "list_foldername"}
                                    
                                {else if $LISTVIEW_NAME == "owner"}
                                    
                                {else}
                                    
                                {/if}
                                {*
                                {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                                {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME)
                                   FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
                                   *}
                            </td>
                        {/foreach}
                        <td>
                            <button class="btn" data-trigger="listSearch">{vtranslate('LBL_SEARCH', $MODULE )}</button>
                        </td>
                </tr>
                {* //ITS4YOU-END *}
		{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
		<tr class="listViewEntries" data-id={$LISTVIEW_ENTRY->getId()} data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
			<td class="{$WIDTHTYPE}"><input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"></td>
			{foreach key=LISTVIEW_HEADER_KEY item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				<td nowrap class="listViewEntryValue"  width="{$WIDTHTYPE}">
					<a href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
            {vtranslate($LISTVIEW_ENTRY->get($LISTVIEW_HEADER_KEY), $MODULE)}
          </a>
					{if $LISTVIEW_HEADER@last}
						</td><td nowrap width="{$WIDTHTYPE}">
						<div class="pull-right actions">
							<span class="actionImages">
                {foreach item=LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
                    <a style="text-shadow: none" {if $LINK->get('class')!=''}class="{$LINK->get('class')}"{/if} {if strpos($LINK->getUrl(), 'javascript:')===0} href='javascript:void(0);' onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
                                                {else} href={$LINK->getUrl()} {/if}>{vtranslate($LINK->getLabel(),$QUALIFIED_MODULE)}
                    </a>&nbsp;
                {/foreach}
							</span>
						</div>
						</td>
					{/if}
				</td>
			{/foreach}
		</tr>
		{/foreach}
	</table>

<!--added this div for Temporarily -->
{if $LISTVIEW_ENTRIES_COUNT eq '0'}
	<table class="emptyRecordsDiv">
		<tbody>
			<tr>
				<td>
					{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
					{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}. {vtranslate('LBL_CREATE')} <a href="{$MODULE_MODEL->getCreateRecordUrl()}&folderid={$VIEWNAME}">{vtranslate($SINGLE_MODULE, $MODULE)}</a>
				</td>
			</tr>
		</tbody>
	</table>
{/if}
</div>
</div>
{/strip}
<div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate("ITS4YouReports","ITS4YouReports")} {$VERSION} {vtranslate("COPYRIGHT","ITS4YouReports")}</div>