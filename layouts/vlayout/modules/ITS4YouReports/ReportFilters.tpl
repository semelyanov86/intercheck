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

<script type="text/javascript">
    var advft_column_index_count = -1;
    var advft_group_index_count = 0;
    var column_index_array = [];
    var group_index_array = [];

    var gf_advft_column_index_count = -1;
    var gf_advft_group_index_count = 0;
    var gf_column_index_array = [];
    var gf_group_index_array = [];
    var rel_fields = {$REL_FIELDS};
</script>

{include file='modules/ITS4YouReports/AdvanceFilter.tpl'}

{$BLOCKJS_STD}

<input type="hidden" name="advft_criteria" id="advft_criteria" value="" />
<input type="hidden" name="advft_criteria_groups" id="advft_criteria_groups" value="" />
<input type="hidden" name="groupft_criteria" id="groupft_criteria" value="" />


<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                        <th style="vertical-align: middle;">
                          {vtranslate('LBL_ADVANCED_FILTER',$MODULE)}
	                        {* tooltip start *}
	                        {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP8_INFO',$MODULE)}
	                        {include file='modules/ITS4YouReports/TooltipElement.tpl'}
													{* tooltip end *}
                        </th>
                    </tr>
                </thead>
                <tbody> 
                {* ADVANCE FILTER START *}
                    <tr>
                        <td>
                            <div class="filterContainer">
                                <div style="display:block" id='adv_filter_div' name='adv_filter_div'>
                                    {* ADVANCE FILTER START *}
                                    {include file='modules/ITS4YouReports/FiltersCriteria.tpl'}
                                    {* ADVANCE FILTER END *}
                                </div>
                                <div style='float:left;'>&nbsp;&nbsp;</div>
                                <button type='button' class='btn' style='float:left;' onclick="addNewConditionGroup('adv_filter_div')"><strong>{vtranslate('LBL_NEW_GROUP',$MODULE)}</strong></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            {* ADVANCE FILTER END *}

            {* GROUP FILTER START *}
            {assign var="display_summaries_filter" value="display:block;" }
            {if $REPORTTYPE == "tabular"}
              {assign var="display_summaries_filter" value="display:none;" }
            {/if}

            <div style="width:100%;{$display_summaries_filter}" id='group_filter_div' name='group_filter_div' class="paddingTop20">

                <table class="table table-bordered table-report">
                    <thead>
                        <tr class="blockHeader">
                            <th>
                                 {vtranslate('LBL_GROUP_FILTER',$MODULE)}
                            </th>
                        </tr>
                    </thead>
                </table>
                <table class="table table-bordered table-report" id='conditiongrouptable_0'>
                    <tr id='ggroupfooter_0'>
                        <td colspan='5' align='left'>
                            {*<input type='button' class='crmbutton edit small' value='New Condition' onclick='addGroupConditionRow("0")' />*}
                            <button type='button' class='btn' style='float:left;' onclick='addGroupConditionRow("0")'><strong>{vtranslate('LBL_NEW_CONDITION',$MODULE)}</strong></button>
                        </td>
                    </tr>
                </table>
                <table class="table">
                    <tr><td align='center' id='groupconditionglue_0'>
                        </td></tr>
                </table>

                {foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$SUMMARIES_CRITERIA}
                    <script type="text/javascript">
                        addGroupConditionRow('0');

                        document.getElementById('ggroupop{$COLUMN_INDEX}').value = '{$COLUMN_CRITERIA.comparator}';
                        var conditionColumnRowElement = document.getElementById('ggroupcol{$COLUMN_INDEX}');
                        conditionColumnRowElement.value = '{$COLUMN_CRITERIA.columnname}';
                        {*reports4you_updatefOptions(conditionColumnRowElement, 'ggroupop{$COLUMN_INDEX}');*}
                        addRequiredElements('g', '{$COLUMN_INDEX}');
                        var columnvalue = '{$COLUMN_CRITERIA.value}';
                        document.getElementById('ggroupval{$COLUMN_INDEX}').value = columnvalue;
                        
                    </script>
                {/foreach}
                {foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$SUMMARIES_CRITERIA}
                    <script type="text/javascript">
                        if (document.getElementById('gcon{$COLUMN_INDEX}'))
                            document.getElementById('gcon{$COLUMN_INDEX}').value = '{$COLUMN_CRITERIA.column_condition}';
                    </script>
                {/foreach}
            </div>

            {* GROUP FILTER END *}
                
		        {* QUICK FILTER START *}
		        <div style="width:100%;" id='quick_filter_div' name='quick_filter_div' class="paddingTop20">
	            <table class="table table-bordered table-report">
	                <thead>
	                    <tr class="blockHeader">
	                        <th style="vertical-align: middle;">
	                          {vtranslate('LBL_QUICK_FILTER',$MODULE)}
	                        </th>
	                    </tr>
	                </thead>
	                <tbody> 
	                {* ADVANCE FILTER START *}
	                    <tr>
	                        <td>
									            <div class="filterConditionContainer " id='quickfiltertable_0'>
									                <div class="form-group" id='quickfilter_0'>
									                    <input type="hidden" name="quick_filters_save" id="quick_filters_save" value="">
									                    <select name="quick_filters" id="quick_filters" multiple class="span12 chzn-select row-fluid" style="width:100%:" >
									                    </select>
									                </div>
									            </div>
	                        </td>
	                    </tr>
	                </tbody>                    
	            </table>
			        <script type="text/javascript">
			            jQuery(document).ready(function () {
			                addQuickFilterBox();
			            });
			        </script>
            </div>
		        {* QUICK FILTER END *}  
       </div>
    </div>
</div> 
{/strip}
