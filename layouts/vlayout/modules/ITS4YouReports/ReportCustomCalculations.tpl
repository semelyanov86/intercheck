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
<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <tbody>
                    {* //ITS4YOU-CR SlOl 16. 5. 2016 8:24:48 *}
                    <tr style="height:25px;display:none;">
                        <td class="dvtCellLabel" nowrap width="100%" align="right" colspan="5" >
                            {assign var="cc_i" value="WCCINRW" }
                            {assign var="cc_special" value="chzn-done" }
                            <div id="cc_row_base">
                                {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                            </div>
                        </td>
                    </tr>
                    
                    {assign var="cc_special" value="" }
                    <tr style="height:25px">
                        <th class="dvtCellLabel" nowrap width="100%" align="right" colspan="5" ><b>{vtranslate('LBL_CUSTOM_CALCULATION',$MODULE)}</b>
												{* tooltip start *}
                        {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP61_INFO',$MODULE)}
                        {include file='modules/ITS4YouReports/TooltipElement.tpl'}
												{* tooltip end *}
												</th>
                    </tr>
                    <tr style="height:25px">
                        <td class="dvtCellLabel" nowrap colspan="5" id="cc_td_cell" >
                            {if empty($CUSTOM_CALCULATIONS)}
                                {assign var="cc_i" value="1" }
                                {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                            {else}
                                {foreach key=cc_i item=cc_calculation_arr from=$CUSTOM_CALCULATIONS}
                                    {include file='CustomCalculationRow.tpl'|@vtemplate_path:$MODULE}
                                {/foreach}
                            {/if}
                        </td>
                    </tr>
                    <tr style="height:25px">
                        <td class="dvtCellLabel" nowrap colspan="5" >
                            <button type='button' class='btn' style='float:left;' onclick="addNewCustomCalculation()"><strong>{vtranslate('LBL_NEW_CUSTOM_CALCULATION',$MODULE)}</strong></button>
                        </td>
                    </tr>
                    {* //ITS4YOU-END *}
                </tbody> 
            </table>
        </div>
    </div>
</div> 
{/strip}