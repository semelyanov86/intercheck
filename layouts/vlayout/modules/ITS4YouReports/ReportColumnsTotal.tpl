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
<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <thead>
                   <tr class="blockHeader">
                       <th colspan="5">
                            {vtranslate('LBL_CALCULATIONS',$MODULE)}
                            <input type="hidden" name="curl_to_go" id="curl_to_go" value="{$CURL}">
                            {* tooltip start *}
                            {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP6_INFO',$MODULE)}
                            {include file='modules/ITS4YouReports/TooltipElement.tpl'}
														{* tooltip end *}
                       </th>
                   </tr>
                </thead>
                <tbody> 
        
                    <tr style="height:25px">
                        <td class="dvtCellLabel" nowrap width="26%" align="right" ><b>{vtranslate("LBL_COLUMNS", $MODULE)}</b></td>
                        <td class="dvtCellLabel" nowrap width="11%" align="center" ><b>{vtranslate("LBL_COLUMNS_SUM", $MODULE)}</b></td>
                        <td class="dvtCellLabel" nowrap width="11%" align="center" ><b>{vtranslate("LBL_COLUMNS_AVERAGE", $MODULE)}</b></td>
                        <td class="dvtCellLabel" nowrap width="11%" align="center" ><b>{vtranslate("LBL_COLUMNS_LOW_VALUE", $MODULE)}</b></td>
                        <td class="dvtCellLabel" nowrap width="11%" align="center" ><b>{vtranslate("LBL_COLUMNS_LARGE_VALUE", $MODULE)}</b></td>
                    </tr>
                    {foreach key=rowname item=calculations from=$BLOCK1}
                        <tr class="lvtColData" onmouseover="this.className='lvtColDataHover'" onmouseout="this.className='lvtColData'" bgcolor="white">
                            <td class="dvtCellLabel" align="right" >{$rowname}</td>
                            {foreach item=checkbox from=$calculations}
                                <td class="dvtCellInfo" align="center" ><input name="{$checkbox.name}" type="checkbox" {$checkbox.checked} value=""></td>
                            {/foreach}
                        </tr>
                    {foreachelse}
                        <tr class="lvtColData" bgcolor="white"><td colspan="5" align="center" style="text-align:center;font-size: 1.5em;width:100%;color:red;" ><b>{vtranslate("NO_CALCULATION_COLUMN", $MODULE)}</b></td></tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

{if $RECORDID == "" && $ACT_MODE=="ChangeSteps" && $cc_populated==""}
  |#<&NBX&>#|
      {include file='modules/ITS4YouReports/ReportCustomCalculations.tpl'}
{/if}
{/strip}