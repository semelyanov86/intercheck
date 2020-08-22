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
jQuery().ready(function() {
    var ITS4YouReports_KM_Js = new ITS4YouReports_KeyMetricsList_Js();
    ITS4YouReports_KM_Js.registerReportChangeEvent();
});
</script>

{strip}
    <div class="row-fluid">       
    <div class="listViewHeaders">
        <div class="row-fluid">           
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_KEY_METRICS_INFORMATION',$MODULE)}
                       </th>
                   </tr>
                </thead>
                <tbody>    
                    <tr style="height:25px">
                        <td class="fieldLabel medium" style="vertical-align:middle;" >
                            <label class="pull-right marginRight10px">
                                {vtranslate('label',$MODULE)}<span class="redColor">*</span>
                            </label>
                        </td>
                        <td {$custom_style} >
                            <input id="label" class="input-large span5 nameField" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="label" value="{$label}" data-fieldinfo="" type="text" style="margin-bottom:0px;">                        
                        </td>
                    </tr>
                    
                    <tr style="height:25px">
                        <td class="fieldLabel medium" style="vertical-align:middle;" >
                            <label class="pull-right marginRight10px">
                                {vtranslate('LBL_REPORT_NAME',$MODULE)}<span class="redColor">*</span>
                            </label>
                        </td>
                        <td {$custom_style} >
                            {* dokoncit changovacku / edit *}
                            <select name="reportname" id="reportname" class="span5 chzn-select row-fluid"  style="margin:auto;" onchange="">
                                <option value="" ></option>
                                {* Old Rows Only ...
                                {foreach item=report from=$reportsList}
                                    {assign var="report" value=$report.0}
                                    {assign var="valueid" value=$report.reportid}
                                    <option value="{$valueid}" {if $valueid==$reportid}selected{/if} >{$report.reportname}</option>
                                {/foreach}
                                *}
                                {foreach key=foldername item=report from=$reportsList}
                                    <optgroup label='{$foldername}'>
                                    {foreach key=valueid item=reportname from=$report}
                                        <option value="{$valueid}" {if $metrics_type=="report" && $valueid==$reportid}selected{/if} >{$reportname}</option>
                                    {/foreach}
                                    </optgroup>
                                {/foreach}
                                
                                {foreach key=viewtype item=customview from=$cvList}
                                    <optgroup label='{$viewtype}'>
                                    {foreach key=valueid item=viewname from=$customview}
                                        {assign var=cv_id value="cv_$reportid"}
                                        <option value="{$valueid}" {if $metrics_type=="customview" && $valueid==$cv_id}selected{/if} >{$viewname}</option>
                                    {/foreach}
                                    </optgroup>
                                {/foreach}
                                
                            </select>
                        </td>
                    </tr>
                    
                    <tr style="height:25px">
                        <td class="fieldLabel medium" style="vertical-align:middle;" >
                            <label class="pull-right marginRight10px">
                                {vtranslate('column_str',$MODULE)}<span class="redColor">*</span>
                            </label>
                        </td>
                        <td {$custom_style} >
                            {*<select name="column_str" id="column_str" class="span4 chzn-select row-fluid"  style="margin:auto;">*}
                            <select name="column_str" id="column_str" class="span5 chzn-select row-fluid"  style="margin:auto;">
                                <option value="" >{vtranslate('LBL_NONE',$MODULE)}</option>
                                {if $col_options!=""}
                                    {$col_options}
                                {else}
                                    {foreach key=optgroupvalue item=optgrouparray from=$summaries_otions}
                                        {if $optgroupvalue!=""}
                                            <optgroup label='{vtranslate($optgroupvalue,$report_module)}'>
                                                {foreach item=summaries_column_arr from=$optgrouparray}
                                                    {assign var=summaries_column_val value=$summaries_column_arr.value}
                                                    {assign var=summaries_column_text value=$summaries_column_arr.text}
                                                    <option value="{$summaries_column_val}" {if $column_str_value==$summaries_column_val}selected{/if} >{$summaries_column_text}</option>
                                                {/foreach}
                                            </optgroup>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </select>                        
                        </td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>      
{/strip}