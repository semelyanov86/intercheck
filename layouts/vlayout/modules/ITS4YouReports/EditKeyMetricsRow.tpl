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
    ITS4YouReports_KM_Js.registerEditKeyMetricsRowStep3();
});
</script>

{strip}
  <div class="container-fluid editViewContainer">
      <div class="contentHeader row-fluid">
          <h3 class="span8 textOverflowEllipsis">
            {if $ID!=""}
                {vtranslate('LBL_EDIT_NEW_KEY_METRICS', $MODULE)}
            {else}
                {vtranslate('LBL_CREATE_NEW_KEY_METRICS', $MODULE)}
            {/if}
            &nbsp;{vtranslate('for_report',$MODULE)}&nbsp;<u>{$reports4youname}</u>
          </h3>
      </div>

<form name="NewKeyMetricsRow" id="NewKeyMetricsRow" action="index.php" method="POST" enctype="multipart/form-data" onsubmit="">

<input type="hidden" name="module" value="ITS4YouReports">
{*<input type="text" name="primarymodule" id="primarymodule" value="{$REP_MODULE}">*}
<input type="hidden" name="reportid" id="reportid" value="{$reportid}">
<input type="hidden" name="km_id" id="km_id" value="{$KM_ID}">
<input type="hidden" name="id" id="id" value="{$ID}">
<input type="hidden" name='action' id='action' value='KeyMetrics'/>
<input type="hidden" name='mode' id='mode' value='addkeymetricsrow' />
{if $ID != ""}
    <input type="hidden" name='updatemode' id='updatemode' value='edit' />
{else}
    <input type="hidden" name='updatemode' id='updatemode' value='create' />
{/if}
<input type="hidden" name='cancel_btn_url' id='cancel_btn_url' value='{$cancel_btn_url}' />

<input type="hidden" name="reporttype" id="reporttype" value="{$REPORTTYPE}">


<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
{*** BUTTONS TOP ***}
<tr>
    <td class="small" style="text-align:center;padding:0px 0px 10px 0px;">
        {* BACK STANDARD DISABLED BUTTON
        <input type="button" name="back_rep_top" id="back_rep_top" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
        *}
        <input type="button" name="back_rep_top" id="back_rep_top" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " class="btn" onclick="window.location.href='index.php?module=ITS4YouReports&view=EditKeyMetricsRow&km_id={$KM_ID}&id={$ID}&reportid={$reportid}';">&nbsp;&nbsp;        
        {* CANCEL RED BUTTON
        <div  id="submitbutton0T" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
            <button type="button" class="btn btn-danger backStep" id="cancelbtn0T" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';"><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
        </div>
        *}
        <button class="btn btn-success" type="submit" id="savemetricbtn" onclick=""><strong>{vtranslate('LBL_SAVE',$MODULE)}</strong></button>
    </td>
</tr>
{*** BUTTONS TOP  END ***}
</table>

      <table class="table table-bordered blockContainer showInlineTable equalSplit">
        <thead>
            <th class="blockHeader" colspan="4">
                {vtranslate('LBL_KEY_METRICS_INFORMATION',$MODULE)}
            </th>
        </thead>
        <tbody>
            <tr>
                <td class="fieldLabel medium" style="vertical-align:middle;">
                    <label class="muted pull-right marginRight10px;">
                        <span class="redColor">*</span>
                        {vtranslate('label',$MODULE)}
                    </label>
                </td>
                <td class="fieldValue medium">
                    <div class="row-fluid">
                        <span class="span10">
                            <input id="label" class="input-large span4 nameField" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="label" value="" data-fieldinfo="" type="text">
                        </span>
                    </div>
                </td>
                <td class="fieldLabel medium" style="vertical-align:middle;">
                    <label class="muted pull-right marginRight10px;">
                        <span class="redColor">*</span>
                        {vtranslate('column_str',$MODULE)}
                    </label>
                </td>
                <td class="fieldValue medium">
                    <div class="row-fluid">
                        <span class="span10">
                            <select name="column_str" id="column_str" class="span4 chzn-select row-fluid"  style="margin:auto;">
                                {foreach key=optgroupvalue item=optgrouparray from=$summaries_otions}
                                    {if $optgroupvalue!=""}
                                        <option value="" >{vtranslate('LBL_NONE',$MODULE)}</option>
                                        <optgroup label='{vtranslate($optgroupvalue,$report_module)}'>
                                            {foreach item=summaries_column_arr from=$optgrouparray}
                                                {assign var=summaries_column_val value=$summaries_column_arr.value}
                                                {assign var=summaries_column_text value=$summaries_column_arr.text}
                                                <option value="{$summaries_column_val}" {if $column_str_value==$summaries_column_val}selected{/if} >{$summaries_column_text}</option>
                                            {/foreach}
                                        </optgroup>
                                    {/if}
                                {/foreach}
                            </select>
                        </span>
                    </div>
                </td>
                {*
                <td class="fieldLabel medium" style="vertical-align:middle;">
                    <label class="muted pull-right marginRight10px">
                        <span class="redColor">*</span>
                        {vtranslate('calculation_type',$MODULE)}
                    </label>
                </td>
                <td class="fieldValue medium">
                    <div class="row-fluid">
                        <span class="span10">
                            <select name="calculation_type" id="calculation_type" class="span4 chzn-select row-fluid"  style="margin:auto;">
                                <option value="" >{vtranslate('LBL_NONE',$MODULE)}</option>
                                {foreach item=calculation_type from=$CALCULATION_ARRAY}
                                    <option value="{$calculation_type}" {if $calculation_type_value==$calculation_type}selected{/if} >{$calculation_type}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                </td>
                *}
              </tr>
              {*
              <tr>
                <td class="fieldLabel medium" style="vertical-align:middle;">
                    <label class="muted pull-right marginRight10px;">
                        <span class="redColor">*</span>
                        {vtranslate('column_str',$MODULE)}
                    </label>
                </td>
                <td class="fieldValue medium">
                    <div class="row-fluid">
                        <span class="span10">
                            <select name="column_str" id="column_str" class="span4 chzn-select row-fluid"  style="margin:auto;">
                                {foreach key=optgroupvalue item=optgrouparray from=$summaries_otions}
                                    {if $optgroupvalue!=""}
                                        <option value="" >{vtranslate('LBL_NONE',$MODULE)}</option>
                                        <optgroup label='{vtranslate($optgroupvalue,$report_module)}'>
                                            {foreach item=summaries_column_arr from=$optgrouparray}
                                                {assign var=summaries_column_val value=$summaries_column_arr.value}
                                                {assign var=summaries_column_text value=$summaries_column_arr.text}
                                                <option value="{$summaries_column_val}" {if $column_str_value==$summaries_column_val}selected{/if} >{$summaries_column_text}</option>
                                            {/foreach}
                                        </optgroup>
                                    {/if}
                                {/foreach}
                            </select>
                        </span>
                    </div>
                </td>
                <td class="fieldLabel medium">
                    &nbsp;
                </td>
                <td class="fieldValue medium">
                    &nbsp;
                </td>
            </tr>
            *}
        </tbody>
      </table>

</form>

{****** BUTTONS BOTTOM ******}
<table width="100%"  border="0" cellspacing="0" cellpadding="5" >
    <tr><td class="small" style="text-align:center;padding:10px 0px 10px 0px;" colspan="3">
        {* BACK STANDARD DISABLED BUTTON
        <input type="button" name="back_rep_top" id="back_rep_top2" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
        *}
        <input type="button" name="back_rep_top" id="back_rep_top2" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " class="btn" onclick="window.location.href='index.php?module=ITS4YouReports&view=EditKeyMetricsRow&km_id={$KM_ID}&id={$ID}&reportid={$reportid}';">&nbsp;&nbsp;        
        {* CANCEL RED BUTTON
        <div  id="submitbutton0B" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
            <button type="button" class="btn btn-danger backStep" id="cancelbtn0B" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';"><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
        </div>
        *}
        <button class="btn btn-success" type="submit" id="savemetricbtn2" onclick=""><strong>{vtranslate('LBL_SAVE',$MODULE)}</strong></button>
    </td></tr>
</table>
{****** BUTTONS BOTTOM END ******}
      
  </div>
{/strip}