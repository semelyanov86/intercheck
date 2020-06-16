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
<input type="hidden" name="curl_to_go" id="curl_to_go" value="{$CURL}">
<div style="border:1px solid #ccc;padding:4%;">
    <div class="row">
        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-12">
                <h4>{vtranslate('LBL_COLUMNS',$MODULE)}</h4>
            </label>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-lg-2">
                &nbsp;
            </div>
            <div class="col-lg-1 textAlignCenter">
                <label class="control-label">
                    {vtranslate("LBL_COLUMNS_SUM", $MODULE)}
                </label>
            </div>
            <div class="col-lg-1 textAlignCenter">
                <label class="control-label">
                    {vtranslate("LBL_COLUMNS_AVERAGE", $MODULE)}
                </label>
            </div>
            <div class="col-lg-1 textAlignCenter">
                <label class="control-label">
                    {vtranslate("LBL_COLUMNS_LOW_VALUE", $MODULE)}
                </label>
            </div>
            <div class="col-lg-1 textAlignCenter">
                <label class="control-label">
                    {vtranslate("LBL_COLUMNS_LARGE_VALUE", $MODULE)}
                </label>
            </div>
        </div>
    </div>

    {foreach key=rowname item=calculations from=$BLOCK1}
        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">
                        {$rowname}
                    </label>
                </div>
                {foreach item=checkbox from=$calculations}
                    <div class="col-lg-1 textAlignCenter">
                        <input name="{$checkbox.name}" type="checkbox" {$checkbox.checked} class="inputElement" value="">
                    </div>
                {/foreach}
            </div>
        </div>
    {foreachelse}
        <div class="row">
            <div class="form-group">
                <div class="col-lg-12">
                    <label class="control-label textAlignLeft">
                        {vtranslate("NO_CALCULATION_COLUMN", $MODULE)}
                    </label>
                </div>
            </div>
        </div>
    {/foreach}

</div>

{if $cc_populated!=='true' && $ACT_MODE ==='ChangeSteps'}
    |#<&NBX&>#|
    {include file='modules/ITS4YouReports/ReportCustomCalculations.tpl'}
    {assign var=cc_populated value='true'}
{/if}
    <input type="hidden" id="cc_populated" value="{$cc_populated}">
{/strip}