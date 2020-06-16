{*<!--
/*********************************************************************************
* The content of this file is subject to the Reports 4 You license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
<div id="listview-actions" class="listview-actions-container">
    {foreach item=LIST_MASSACTION from=$LISTVIEW_MASSACTIONS name=massActions}
        {if $LIST_MASSACTION->getLabel() eq 'LBL_EDIT'}
            {assign var=editAction value=$LIST_MASSACTION}
        {elseif $LIST_MASSACTION->getLabel() eq 'LBL_DELETE'}
            {assign var=deleteAction value=$LIST_MASSACTION}
        {else}
            {$a = array_push($LISTVIEW_MASSACTIONS_1, $LIST_MASSACTION)}
            {* $a is added as its print the index of the array, need to find a way around it *}
        {/if}
    {/foreach}
    <div class="row">
        <div class="col-md-3">
            <div class="btn-group listViewActionsContainer" role="group" aria-label="...">
                {if $deleteAction}
                    <button type="button" class="btn btn-default" id="{$MODULE}_listView_massAction_LBL_MOVE_REPORT"
                            onclick='{$MODULE}_List_Js.massMove("index.php?module={$MODULE}&view=MoveReports")' title="{vtranslate('LBL_MOVE_REPORT', $MODULE)}" disabled="disabled">
                        <i class="vicon-foldermove" style='font-size:13px;'></i>
                    </button>
                {/if}
                {if $deleteAction}
                    <button type="button" class="btn btn-default" id={$MODULE}_listView_massAction_{$deleteAction->getLabel()}
                        {if stripos($deleteAction->getUrl(), 'javascript:')===0}onclick='{$deleteAction->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$deleteAction->getUrl()}')"{/if}
                            title="{vtranslate('LBL_DELETE', $MODULE)}" disabled="disabled"
                            style="margin-left:5px;" >
                        <i class="fa fa-trash"></i>
                    </button>
                {/if}
            </div>
        </div>
        <div class="col-md-6">
            <span class="customFilterMainSpan btn-group">
                &nbsp;
            </span>
        </div>
        <div class="col-md-3">
            {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
            {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
        </div>
    </div>
</div>