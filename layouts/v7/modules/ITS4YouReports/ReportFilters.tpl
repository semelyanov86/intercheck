{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

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
<input type="hidden" name="quick_filter_criteria" id="quick_filter_criteria" value="" />
{strip}
    {if 'Detail' neq $VIEW}
    <div style="border:1px solid #ccc;padding:4%;">
    {/if}
        {* ADVANCE FILTER START *}
        <div class="row">
            <div class="form-group">
                <label class="control-label textAlignLeft col-lg-12">
                    <h4>{vtranslate('LBL_ADVANCED_FILTER',$MODULE)}</h4>
                </label>
            </div>
        </div>

        <div class="row {if 'Detail' neq $VIEW}well{/if} filterConditionContainer ">
            <div class="form-group">
                <div class='filterContainer'>
                    <div style="display:block" id='adv_filter_div' name='adv_filter_div'>
                        {* ADVANCE FILTER START *}
                        {include file='modules/ITS4YouReports/FiltersCriteria.tpl'}
                        {* ADVANCE FILTER END *}
                    </div>
                    <button type='button' class='btn btn-default' style='float:left;' onclick="addNewConditionGroup('adv_filter_div')"><i class="fa fa-plus"></i>&nbsp;{vtranslate('LBL_NEW_GROUP',$MODULE)}</button>
                </div>
            </div>
        </div>
        {* ADVANCE FILTER END *}

        {* GROUP FILTER START *}
        {if 'Detail' neq $VIEW}
            {assign var="display_summaries_filter" value="display:block;" }
            {if $REPORTTYPE == "tabular"}
                {assign var="display_summaries_filter" value="display:none;" }
            {/if}

            <div style="width:100%;{$display_summaries_filter}" id='group_filter_div' name='group_filter_div' class="paddingTop20">
                <div class="row">
                    <div class="form-group">
                        <label class="control-label textAlignLeft col-lg-12">
                            <h4>{vtranslate('LBL_GROUP_FILTER',$MODULE)}</h4>
                        </label>
                    </div>
                </div>

                <div class="row well filterConditionContainer " id='conditiongrouptable_0'>
                    <div class="form-group" id='ggroupfooter_0'>
                        <button type="button" class="btn btn-default" onclick='addGroupConditionRow("0")'><i class="fa fa-plus"></i>&nbsp;{vtranslate('LBL_NEW_CONDITION',$MODULE)}</button>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group" id='groupconditionglue_0'>

                    </div>
                </div>

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
            <div class="row">
                <div class="form-group">
                    <label class="control-label textAlignLeft col-lg-12">
                        <h4>{vtranslate('LBL_QUICK_FILTER',$MODULE)}</h4>
                    </label>
                </div>
            </div>
            <div class="row well filterConditionContainer " id='quickfiltertable_0'>
                <div class="form-group" id='quickfilter_0'>
                    <input type="hidden" name="quick_filters_save" id="quick_filters_save" value="">
                    <select name="quick_filters" id="quick_filters" multiple class="select2 col-lg-10" >
                    </select>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                addQuickFilterBox();
            });
        </script>
        {* QUICK FILTER END *}
    {/if}
    {if 'Detail' neq $VIEW}
    </div>
    {/if}
{/strip}