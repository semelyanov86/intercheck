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
    <br />
    <div style="border:1px solid #ccc;padding:4%;">
        <div class="row">
            <div class="form-group col-lg-12">
                <label class="control-label textAlignLeft ">
                    <h4 style="float:left;">{vtranslate('LBL_CUSTOM_CALCULATION',$MODULE)}</h4>
                    <a href="javascript:;" title="{$CUSTOM_CALCULATIONS_INFO}"  style="float:left;">
                        <i class="glyphicon glyphicon-info-sign" style="padding-left:0.5em;padding-top:1em;"></i>&nbsp;
                    </a>
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="span9">
                <div class="row-fluid">
                    <div class="col-lg-12 hide">
                        {assign var="cc_i" value="WCCINRW" }
                        {assign var="cc_special" value="" }
                        <div id="cc_row_base">
                            {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                        </div>
                    </div>
                    {assign var="cc_special" value="" }
                    <div class="col-lg-12" id="cc_td_cell">
                        {if empty($CUSTOM_CALCULATIONS)}
                            {assign var="cc_i" value="1" }
                            {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                        {else}
                            {foreach key=cc_i item=cc_calculation_arr from=$CUSTOM_CALCULATIONS}
                                {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                            {/foreach}
                        {/if}
                    </div>
                    <div class="col-lg-12">
                        <br>
                        <button type='button' class='btn btn-default' style='float:left;' onclick="addNewCustomCalculation()"><i class="fa fa-plus"></i>&nbsp;{vtranslate('LBL_NEW_CUSTOM_CALCULATION',$MODULE)}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}