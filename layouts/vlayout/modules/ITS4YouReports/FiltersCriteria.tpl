{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<script language="JAVASCRIPT" type="text/javascript" src="layouts/vlayout/modules/ITS4YouReports/resources/ITS4YouReports.js"></script>

<input type="hidden" name="std_filter_columns" id="std_filter_columns" value='{$std_filter_columns}' />
<input type="hidden" name="std_filter_criteria" id="std_filter_criteria" value='{$std_filter_criteria}' />
<input type="hidden" name="sel_fields" id="sel_fields" value='{$SEL_FIELDS}' />
{*<input type='hidden' name='filter_columns' id='filter_columns' value='{$COLUMNS_BLOCK_JSON}' />*}

{* //ITS4YOU-CR SlOl 27. 5. 2016 8:21:23 *}
{include file='modules/ITS4YouReports/FieldExpressions.tpl'}
{* //ITS4YOU-END *}

{$BLOCKJS_STD}

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

{* ADVANCE FILTER START *}
<table border=0 cellspacing=0 cellpadding=0 width="100%">
    {if $DISPLAY_FILTER_HEADER === true}
            <tr>
                <td class="detailedViewHeader" nowrap align="left" colspan="8">
                {*
                <input type="text" name="advft_criteria" id="advft_criteria" value="" />
                <input type="text" name="advft_criteria_groups" id="advft_criteria_groups" value="" />
                *}
                    <div style="float:left;min-height: 2.3em;vertical-align: middle;padding-top:0.3em;">  
                        <span class="genHeaderGray" style="">{vtranslate('LBL_ADVANCED_FILTER',$MODULE)}</span> &nbsp;
                    </div>
                    {*{if $EMPTY_CRITERIA_GROUPS == true}
                        <div style="float:left;">  
                            <button type='button' class='btn fgroup_btn' style='float:left;' onclick='addNewConditionGroup("adv_filter_div")'><strong>{vtranslate('LBL_NEW_GROUP',$MODULE)}</strong></button>
                        </div>
                    {/if}*}
                </td>
            </tr>
    {/if}
    <tr>
        <td class="dvtCellLabel" nowrap align="center" style="padding:0px;" colspan="8" >
            <div style="display:block" id='adv_filter_div' name='adv_filter_div'>
                <table class="small" border="0" cellpadding="0" cellspacing="0" width="100%">
                </table>
                {assign var=FCON_I value="0"}
<script type="text/javascript">var window_onload = "";</script>
                {foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
                    {assign var=GROUP_COLUMNS value=$GROUP_CRITERIA.columns}
                    <script type="text/javascript">
window_onload += addConditionGroup('adv_filter_div');
                    </script>
                    {foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
                        <script type="text/javascript">
window_onload += 
                            addConditionRow('{$GROUP_ID}');
                            document.getElementById('fop' + advft_column_index_count).value = '{$COLUMN_CRITERIA.comparator}';
                            var conditionColumnRowElement = document.getElementById('fcol' + advft_column_index_count);
                            setSelectedCriteriaValue(conditionColumnRowElement,'{$COLUMN_CRITERIA.columnname}');
                            reports4you_updatefOptions(conditionColumnRowElement, 'fop' + advft_column_index_count, '{$COLUMN_CRITERIA.comparator}');
                            addRequiredElements('f', advft_column_index_count);
                            updateRelFieldOptions(conditionColumnRowElement, 'fval_' + advft_column_index_count);
                            var columnvalue = '{$COLUMN_CRITERIA.value}';
                            if ('{$COLUMN_CRITERIA.comparator}' == 'bw' && columnvalue != '') {ldelim}
                                    var values = columnvalue.split(",");
                                    document.getElementById('fval' + advft_column_index_count).value = values[0];
                                    if (values.length == 2 && document.getElementById('fval_ext' + advft_column_index_count))
                                        document.getElementById('fval_ext' + advft_column_index_count).value = values[1];
                            {rdelim} else {ldelim}
                                document.getElementById('fval' + advft_column_index_count).value = columnvalue;
                            {rdelim}
                            {* //ITS4YOU-CR SlOl 30. 5. 2016 8:52:17 *}
                            {if $COLUMN_CRITERIA.value_hdn != "" }
                                document.getElementById('fvalhdn' + advft_column_index_count).value = '{$COLUMN_CRITERIA.value_hdn}';
                                jQuery('#fval'+ advft_column_index_count).attr("readonly","true");
                            {/if}
                            {* //ITS4YOU-END *}
                        </script>
                        {if $COLUMN_CRITERIA.column_condition !=""}
                          <input type="hidden" name="hfcon_{$GROUP_ID}_{$FCON_I}" id="hfcon_{$GROUP_ID}_{$FCON_I}" value='{$COLUMN_CRITERIA.column_condition}' />
                        {/if}
                        {assign var=FCON_I value=$FCON_I+1}
                    {/foreach}
                    {foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
                        <script type="text/javascript">
                            if (document.getElementById('fcon{$COLUMN_INDEX}'))
                                document.getElementById('fcon{$COLUMN_INDEX}').value = '{$COLUMN_CRITERIA.column_condition}';
                        </script>
                    {/foreach}
                {foreachelse}
                {/foreach}
                {foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
                    <script type="text/javascript">
                        if (document.getElementById('gpcon{$GROUP_ID}'))
                            document.getElementById('gpcon{$GROUP_ID}').value = '{$GROUP_CRITERIA.condition}';
                    </script>
                {/foreach}
            </div>
            {if $DISPLAY_FILTER_HEADER == true}
                <div style='float:left;'><button type='button' class='btn' style='float:left;' onclick="addNewConditionGroup('adv_filter_div')"><strong>{vtranslate('LBL_NEW_GROUP',$MODULE)}</strong></button></div>
            {/if}
        </td>
    </tr>
</table>
<script type="text/javascript">
window.onload = function(){
    window_onload;
};
</script>
{* ADVANCE FILTER END *}