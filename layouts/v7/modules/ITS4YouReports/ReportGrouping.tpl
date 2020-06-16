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
    <div style="border:1px solid #ccc;padding:4%;">
        <input type="hidden" name='all_related_modules' id='all_related_modules' value="{$REL_MODULES_STR}"/>
        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <select class="select2 col-lg-12 inputElement" name="timeline_type1" id="timeline_type1" readonly style="float:left;width:7em;margin:auto;" >
                        <option value="rows" selected="selected" >{vtranslate('LBL_ROWS',$MODULE)}</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="Group1" id="Group1" onChange="checkTimeLineColumn(this,1)" >
                        <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                        {$RG_BLOCK1}
                    </select>
                </div>
                <div class="col-lg-2">
                    {$ASCDESC1}
                </div>
                <div class="col-lg-2">
                    <div id="radio_group1" class="col-lg-7">{$timelinecolumn1_html}</div>
                </div>
            </div>
        </div>

        <div class="row" id="group2_table_row">
            <div class="form-group">
                <div class="col-lg-2">
                    <select class="select2 col-lg-12 inputElement" name="timeline_type2" id="timeline_type2" readonly style="float:left;width:7em;margin:auto;" >
                        <option value="rows" {if $timeline_type2=="rows"}selected="selected"{/if} >{vtranslate('LBL_ROWS',$MODULE)}</option>
                        {if 'true' eq $ALLOW_COLS}
                            <option value="cols" {if $timeline_type2=="cols"}selected="selected"{/if} >{vtranslate('LBL_COLS',$MODULE)}</option>
                        {/if}
                    </select>
                </div>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="Group2" id="Group2" onChange="checkTimeLineColumn(this,2)" >
                        <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                        {$RG_BLOCK2}
                    </select>
                </div>
                <div class="col-lg-2">
                    {$ASCDESC2}
                </div>
                <div class="col-lg-2">
                    <div id="radio_group2" class="col-lg-7">{$timelinecolumn2_html}</div>
                </div>
            </div>
        </div>

        <div class="row" id="group3_table_row">
            <div class="form-group">
                <div class="col-lg-2">
                    <select class="select2 col-lg-12 inputElement" name="timeline_type3" id="timeline_type3" readonly style="float:left;width:7em;margin:auto;" >
                        <option value="rows" {if $timeline_type3=="rows"}selected="selected"{/if} >{vtranslate('LBL_ROWS',$MODULE)}</option>
                        {if 'true' eq $ALLOW_COLS}
                            <option value="cols" {if $timeline_type3=="cols"}selected="selected"{/if} >{vtranslate('LBL_COLS',$MODULE)}</option>
                        {/if}
                    </select>
                </div>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="Group3" id="Group3" onChange="checkTimeLineColumn(this,3)" >
                        <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                        {$RG_BLOCK3}
                    </select>
                </div>
                <div class="col-lg-2">
                    {$ASCDESC3}
                </div>
                <div class="col-lg-2">
                    <div id="radio_group3" class="col-lg-7">{$timelinecolumn3_html}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2 fieldBlockContainer">
                    <label class="control-label textAlignLeft">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</label>
                </div>
                <div class="col-lg-2 ">
                    <select class="select2 col-lg-2 inputElement" name="SummariesModules" id="SummariesModules" onchange='defineSUMModuleFields(this)' >
                        {foreach item=modulearr from=$SummariesModules}
                            <option value={$modulearr.id} {if $modulearr.checked == "checked"}selected="selected"{/if} >{$modulearr.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2 ">
                <input type="text" class="inputElement" id="search_input_sum" onkeyup="getSUMFieldsOptionsSearch(this)" placeholder="{vtranslate('LBL_Search_column',$MODULE)}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2 fieldBlockContainer">
                    &nbsp;
                </div>
                <div class="col-lg-3 fieldBlockContainer">
                    <div id="availSumModValues" style="display:none;">{$ALL_FIELDS_STRING}</div>
                    <select class="col-lg-12" name="availListSum" id="availListSum" multiple size="11" ondblclick="addOndblclickSUM(this)" >
                        {$RG_BLOCK4}
                    </select>
                </div>
                <div class="col-lg-1 fieldBlockContainer">
                    <button type='button' class='btn btn-default' onclick="addColumn('selectedSummaries');" style="margin-top: 50%;">{vtranslate('LBL_ADD_ITEM',$MODULE)} <i class="fa fa-arrow-right"></i></button>
                </div>
                <div class="col-lg-3">
                    <input type="hidden" name="selectedSummariesString" id="selectedSummariesString" value="{$selectedSummariesString}">
                    <select id="selectedSummaries" size="11" name="selectedSummaries" onchange="selectedColumnClick(this);" multiple class="inputElement" style="width:100%;" >
                        {$RG_BLOCK6}
                    </select>
                </div>
                <div class="col-lg-1 fieldBlockContainer">
                    <button type='button' class='btn btn-default' onclick="moveUp('selectedSummaries');" title="{vtranslate('LBL_MOVE_UP_ITEM',$MODULE)}"><i class="fa fa-arrow-circle-up"></i></button>
                    <br>
                    <button type='button' class='btn btn-default' onclick="delColumn('selectedSummaries');" title="{vtranslate('LBL_DELETE',$MODULE)}"><i class="fa fa-times-circle "></i></button>
                    <br>
                    <button type='button' class='btn btn-default' onclick="moveDown('selectedSummaries');" title="{vtranslate('LBL_MOVE_UP_ITEM',$MODULE)}"><i class="fa fa-arrow-circle-down"></i></button>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">{vtranslate('SUMMARIES_ORDER_BY',$MODULE)}&nbsp;</label>
                </div>
                <div class="col-lg-3">
                    <input type="hidden" name="summaries_orderby_columnString" id="summaries_orderby_columnString" value="{$summaries_orderby}">
                    <select id="summaries_orderby_column" class="select2 inputElement" >
                        <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                        {$RG_BLOCK6}
                    </select>&nbsp;
                </div>
                <div class="col-lg-2">
                    <input type="hidden" name="summaries_orderby_typeString" id="summaries_orderby_typeString" value="{$summaries_orderby_type}">
                    <input type='radio' name='summaries_orderby_type' id='summaries_orderby_asc' {if $summaries_orderby_type=="ASC"}checked="checked"{/if} $TimeLineColumnD value='ASC' style="margin:auto;">&nbsp;{vtranslate('Ascending',$MODULE)}&nbsp;
                    <input type='radio' name='summaries_orderby_type' id='summaries_orderby_desc' {if $summaries_orderby_type=="DESC"}checked="checked"{/if} $TimeLineColumnW value='DESC' style="margin:auto;">&nbsp;{vtranslate('Descending',$MODULE)}&nbsp;
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">{vtranslate('SET_LIMIT',$MODULE)}&nbsp;</label>
                </div>
                <div class="col-lg-3">
                    <input type="text" class="inputElement" id="summaries_limit" name="summaries_limit" value="{if $SUMMARIES_LIMIT!="0"}{$SUMMARIES_LIMIT}{/if}">
                    <small>&nbsp;&nbsp;{vtranslate('SET_EMPTY_FOR_ALL',$MODULE)}</small>
                </div>
            </div>
        </div>

    </div>
{/strip}