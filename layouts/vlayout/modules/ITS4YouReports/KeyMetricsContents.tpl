{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
{*<script type="text/javascript">
jQuery().ready(function() {
  var ITS4YouReports_List_Js = new ITS4YouReports_List_Js();
  alert(ITS4YouReports_List_Js);
});
</script>*}
{strip}
<div id="keyMetricsViewContents">
    <br />
    <div class="listViewTopMenuDiv noprint">
      <div class="listViewActionsDiv row-fluid">
        <button class="btn addButton" data-trigger="addReportWidget" onclick="ITS4YouReports_KeyMetricsList_Js.triggerAddKeyWidget('index.php?module=ITS4YouReports&view=EditKeyMetrics');" >{vtranslate('LBL_ADD_WIDGET')}</button>
      </div>
    </div>
    <br />
    
    <div id="selectAllMsgDiv" class="alert-block msgDiv">
    	<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
    </div>
    <div id="deSelectAllMsgDiv" class="alert-block msgDiv">
    	<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
    </div>
    
    	<div class="bottomscroll-div">
    	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
    	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
    	<p class="listViewLoadingMsg hide">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
    	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
    	<table class="table table-bordered listViewEntriesTable">
    		<thead>
    			<tr class="listViewHeaders">
    				{foreach key=LISTVIEW_HEADER_KEY item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
    					<th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if} class="{$WIDTHTYPE}">
                <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER_KEY}">{$LISTVIEW_HEADER.name}
    						&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}<img class="{$SORT_IMAGE} icon-white">{/if}</a>
    					</th>
    				{/foreach}
    			</tr>
    		</thead>
        <tr>
            {foreach key=LISTVIEW_NAME item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                <td>
                    <div class="row-fluid">
                        {if $LISTVIEW_HEADER.column == "smcreatorid"}
                            <select class="chzn-select listSearchContributor span11" name="{$LISTVIEW_HEADER.column}" multiple style="width:150px;" data-fieldinfo=''>
                                {foreach item=PICKLIST_ARR key=PICKLIST_KEY from=$USERS_OPTIONS}
                                    <option value="{$PICKLIST_ARR.0}" {if $LISTVIEW_HEADER.search_val==$PICKLIST_ARR.0}selected{/if} >{vtranslate($PICKLIST_ARR.1,$MODULE)}</option>
                                {/foreach}
                            </select>
                        {else}
                            <input type="text" name="{$LISTVIEW_HEADER.column}" class="span9 listSearchContributor" value="{$LISTVIEW_HEADER.search_val}" data-fieldinfo=''/>
                        {/if}
                    </div>
                </td>
            {/foreach}
            <td style="text-align:right;padding-right:20px;" >
                <button class="btn" data-trigger="listSearch">{vtranslate('LBL_SEARCH', $MODULE )}</button>
            </td>
        </tr>
        {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
        <tr class="listViewEntries" data-id={$LISTVIEW_ENTRY.id} data-recordUrl='' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
    			{foreach key=LISTVIEW_HEADER_KEY item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
    		    {assign var=COLUMNNAME value=$LISTVIEW_HEADER.column}
            {assign var=COLUMNVALUE value=$LISTVIEW_ENTRY.$COLUMNNAME}
            <td nowrap class="listViewEntryValue"  width="{$WIDTHTYPE}">
              {if $COLUMNNAME=="name"}
                  <a href="index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$LISTVIEW_ENTRY.id}">
                    {$COLUMNVALUE}
                  </a>
              {else if $COLUMNNAME=="smcreatorid"}
                  {getUserFullName($COLUMNVALUE)}
              {else}
                  {$COLUMNVALUE}
              {/if}
            {if $LISTVIEW_HEADER@last}
            </td>
            <td nowrap width="{$WIDTHTYPE}">
                {if $CURRENT_USER_MODEL->id==$LISTVIEW_ENTRY.smcreatorid}
                    <div class="pull-right actions">
                        <span class="actionImages">
                            <a style="text-shadow: none" href='javascript:void(0);' onclick="ITS4YouReports_KeyMetricsList_Js.triggerAddKeyWidget('index.php?module=ITS4YouReports&view=EditKeyMetrics&id={$LISTVIEW_ENTRY.id}');" >
                              <i class="icon-pencil alignMiddle" title="{vtranslate('Edit')}"></i>
                            </a>&nbsp;
                            <a style="text-shadow: none" href='javascript:void(0);' class="deleteRecordButton" onclick="ITS4YouReports_KeyMetricsList_Js.deleteRecord('{$LISTVIEW_ENTRY.id}');" >
                              <i class="icon-trash alignMiddle" title="{vtranslate('Delete')}"></i>
                            </a>&nbsp;                    
                        </span>
                    </div>
                {/if}
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
    					{vtranslate('LBL_NO')} {vtranslate("LBL_KEY_METRICS", $MODULE)} {vtranslate('LBL_FOUND')}. <a href="javascript:void(0);" onclick="ITS4YouReports_KeyMetricsList_Js.triggerAddKeyWidget('index.php?module=ITS4YouReports&view=EditKeyMetrics&id={$LISTVIEW_ENTRY.id}');" id="add_widget_href">{vtranslate("LBL_ADD_WIDGET")}</a>
    				</td>
    			</tr>
    		</tbody>
    	</table>
    {/if}
    {/strip}
    <div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate("ITS4YouReports","ITS4YouReports")} {$VERSION} {vtranslate("COPYRIGHT","ITS4YouReports")}</div>
</div>
