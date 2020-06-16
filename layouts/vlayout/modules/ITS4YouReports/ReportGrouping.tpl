{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{include file='modules/ITS4YouReports/TooltipCss.tpl'}

{strip}
<input type="hidden" name="timelinecolumn_html" id='timelinecolumn_html' value='{$timelinecolumn}'>
<div id='date_options_json' class="none" style='display:none;'>{$date_options_json}</div>
<div id='sum_group_columns' class="none" style='display:none;'>{$sum_group_columns}</div>
<input type="hidden" id="summaries_orderby_val" value="{$summaries_orderby}">

{* define restrictions for ReportType -> ReportTypes start *}
{assign var="matrix_js" value="" }
{assign var="reporttype_readonly" value="" }
{if $REPORTTYPE == "summaries" || $REPORTTYPE == "summaries_matrix"}
    {assign var="reporttype_readonly" value="readonly" }
{elseif $REPORTTYPE == "summaries_matrix"}
    {assign var="matrix2_js" value="matrix_js(2);" }
    {assign var="matrix3_js" value="matrix_js(3);" }
    {assign var="timeline_type2" value="cols" }
{/if}
{* define restrictions for ReportType -> ReportTypes end *}

<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_GROUPING_SUMMARIZE',$MODULE)}
                            <input type="hidden" name='all_related_modules' id='all_related_modules' value="{$REL_MODULES_STR}"/>
                            {assign var="step4_info" value="LBL_STEP4_$REPORTTYPE" }
                            {* tooltip start *}
                            {assign var="TOOLTIP_TEXT" value=vtranslate($step4_info,$MODULE)}
                            {include file='modules/ITS4YouReports/TooltipElement.tpl'}
														{* tooltip end *}
                       </th>
                   </tr>
                </thead>
                <tbody>  
                    <tr>
                        <td>
                            <div class="row-fluid">  
                                <div class="span1">&nbsp;</div>
                                <div class="span10">
                                    <table style="margin:0px;padding:0px;" cellspadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <select id="timeline_type1" name="timeline_type1" class="txtBox" readonly style="float:left;width:7em;margin:auto;">
                                                <option value="rows" selected="selected" >{vtranslate('LBL_ROWS',$MODULE)}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="Group1" name="Group1" class="span5 chzn-select row-fluid"  style="margin:auto;float:left;" onChange="checkTimeLineColumn(this,1)">
                                            <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                            {$RG_BLOCK1}
                                            </select>
                                        </td>
                                        <td>
                                            {$ASCDESC1}
                                        </td>
                                        <td>
                                            <div id="radio_group1" style="margin:auto;float:left;">{$timelinecolumn1_html}</div>
                                        </td>
                                    </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr id="group2_table_row">
                        <td>
                            <div class="row-fluid">  
                                <div class="span1">&nbsp;</div>
                                <div class="span10">
                                    <table style="margin:0px;padding:0px;" cellspadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <select id="timeline_type2" name="timeline_type2" class="txtBox" {$reporttype_readonly} style="float:left;width:7em;margin:auto;">
                                                <option value="rows" {if $timeline_type2=="rows"}selected="selected"{/if} >{vtranslate('LBL_ROWS',$MODULE)}</option>
                                                {if 'true' eq $ALLOW_COLS}
                                                    <option value="cols" {if $timeline_type2=="cols"}selected="selected"{/if} >{vtranslate('LBL_COLS',$MODULE)}</option>
                                                {/if}
                                            </select>
                                        </td>
                                        <td>
                                            <select id="Group2" name="Group2" class="span5 chzn-select row-fluid" style="margin:auto;float:left;" onChange="checkTimeLineColumn(this,2)">
                                            <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                            {$RG_BLOCK2}
                                            </select>
                                        </td>
                                        <td>
                                            {$ASCDESC2}
                                        </td>
                                        <td>
                                            <div id="radio_group2" style="margin:auto;float:left;">{$timelinecolumn2_html}</div>
                                        </td>
                                    </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr id="group3_table_row">
                        <td>
                            <div class="row-fluid">  
                                <div class="span1">&nbsp;</div>
                                <div class="span10">
                                    <table style="margin:0px;padding:0px;" cellspadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <select id="timeline_type3" name="timeline_type3" class="txtBox" {$reporttype_readonly} style="float:left;width:7em;margin:auto;">
                                                <option value="rows" {if $timeline_type3=="rows"}selected="selected"{/if} >{vtranslate('LBL_ROWS',$MODULE)}</option>
                                                {if 'true' eq $ALLOW_COLS}
                                                    <option value="cols" {if $timeline_type3=="cols"}selected="selected"{/if} >{vtranslate('LBL_COLS',$MODULE)}</option>
                                                {/if}
                                            </select>
                                        </td>
                                        <td>
                                            <select id="Group3" name="Group3" class="span5 chzn-select row-fluid" style="margin:auto;float:left;"  onChange="checkTimeLineColumn(this,3)">
                                            <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                            {$RG_BLOCK3}
                                            </select>
                                        </td>
                                        <td>
                                            {$ASCDESC3}
                                        </td>
                                        <td>
                                            <div id="radio_group3" style="margin:auto;float:left;">{$timelinecolumn3_html}</div>
                                        </td>
                                    </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                </tr>
            </tbody> 
        </table> 
        <br>
        <table class="table table-bordered table-report">
            <thead>
                <tr class="blockHeader">
                   <th>
                        {vtranslate('AVAILABLE_SUMMARIES_COLUMNS',$MODULE)}
                   </th>
               </tr>
            </thead>
            <tbody> 
                <tr>
                    <td style="padding:0px;" cellpadding="0" cellspacing="0">
                        <div class="row-fluid padding1per">
                            <div class="span12">
                                <div>{vtranslate('LBL_SELECT_MODULE',$MODULE)}&nbsp;
                                    <select id="SummariesModules" name="SummariesModules" onchange='defineSUMModuleFields(this)' class="txtBox" style="width:auto;margin:auto;" >
                                        {foreach item=modulearr from=$SummariesModules}
                                            <option value={$modulearr.id} {if $modulearr.checked == "checked"}selected="selected"{/if} >{$modulearr.name}</option>
                                        {/foreach}
                                    </select>
                                    <input type="text" id="search_input_sum" onkeyup="getSUMFieldsOptionsSearch(this)" placeholder="{vtranslate('LBL_Search_column',$MODULE)}">
                                </div>
                                
                            </div>
                                <table width="100%" >
                                    <tr>
                                        <td width="40%" >
                                            <div id="availSumModValues" style="display:none;">{$ALL_FIELDS_STRING}</div>
                                            <select id="availListSum" multiple size="11" name="availListSum" class="" ondblclick="addOndblclickSUM(this)" style="width:100%;" >
                                            {$RG_BLOCK4}
                                            </select>
                                        </td>
                                        <td width="10%" >
                                                <input name="add" value=" {vtranslate('LBL_ADD_ITEM',$MODULE)} &gt&gt " class="btn" type="button" onClick="addColumn('selectedSummaries');">
                                        </td>
                                        <td width="40%" >
                                            <input type="hidden" name="selectedSummariesString" id="selectedSummariesString" value="{$selectedSummariesString}">
                                            <select id="selectedSummaries" size="11" name="selectedSummaries" onchange="selectedColumnClick(this);" multiple class="" style="width:100%;">
                                                    {$RG_BLOCK6}
                                            </select>
                                        </td>
                                        <td >
                                            <br><br>
                                            <div class="span1">
                                                <div class="padding5per"><button type="button" class="btn btn-mini vtButton arrowUp row-fluid" onclick="moveUp('selectedSummaries')"><img src="layouts/vlayout/skins/images/Arrow-up.png"></img></button></div>
                                                <div class="padding5per"><button type="button" class="btn btn-mini vtButton arrowDown row-fluid"  onclick="delColumn('selectedSummaries')"><img src="layouts/vlayout/skins/images/no.png"></img></button></div>
                                                <div class="padding5per"><button type="button" class="btn btn-mini vtButton arrowDown row-fluid"  onclick="moveDown('selectedSummaries')"><img src="layouts/vlayout/skins/images/Arrow-down.png"></img></button></div>   
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                    </td>
                </tr>
            <tbody> 
        </table>
        <br>
        <table class="table table-bordered table-report">
            <thead>
                <tr class="blockHeader">
                   <th colspan="6">
                        {vtranslate('LBL_LIMIT',$MODULE)} {vtranslate('LBL_AND',$MODULE)} {vtranslate('LBL_SORT_ORDER',$MODULE)}
                   </th>
               </tr>
            </thead>
            <tbody>  
                <tr>
                    <td style="text-align: right;vertical-align: middle;">
                        {vtranslate('SUMMARIES_ORDER_BY',$MODULE)}&nbsp;
                    </td>
                    <td style="text-align: left;vertical-align: middle;">
                        <div style="float:left;text-align:left;">
                            <input type="hidden" name="summaries_orderby_columnString" id="summaries_orderby_columnString" value="{$summaries_orderby}">
                            <select id="summaries_orderby_column" class="txtBox" style="width:auto;margin:auto;" >
                                <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                {$RG_BLOCK6}
                            </select>&nbsp;
                            <input type="hidden" name="summaries_orderby_typeString" id="summaries_orderby_typeString" value="{$summaries_orderby_type}">
                            <input type='radio' name='summaries_orderby_type' id='summaries_orderby_asc' {if $summaries_orderby_type=="ASC"}checked="checked"{/if} $TimeLineColumnD value='ASC' style="margin:auto;">&nbsp;{vtranslate('Ascending',$MODULE)}&nbsp;
                            <input type='radio' name='summaries_orderby_type' id='summaries_orderby_desc' {if $summaries_orderby_type=="DESC"}checked="checked"{/if} $TimeLineColumnW value='DESC' style="margin:auto;">&nbsp;{vtranslate('Descending',$MODULE)}&nbsp;
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;vertical-align: middle;">
                        {vtranslate('SET_LIMIT',$MODULE)}
                    </td>
                    <td style="text-align: left;vertical-align: middle;">
                        <input type="text" id="summaries_limit" name="summaries_limit" value="{if $SUMMARIES_LIMIT!="0"}{$SUMMARIES_LIMIT}{/if}" class="txtBox" style="width:100px;vertical-align: middle;">&nbsp;&nbsp;<small>{vtranslate('SET_EMPTY_FOR_ALL',$MODULE)}</small>
                    </td>
                </tr>
                <tbody>
            </table>
        </div>
    </div>
</div> 
{/strip}