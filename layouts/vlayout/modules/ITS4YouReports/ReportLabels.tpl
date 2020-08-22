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
{assign var="ROWS_COUNT1" value=$ROWS_COUNT }
{assign var="ROWS_COUNT2" value=$ROWS_COUNT+1 }
{assign var="COL_SPAN" value=2 }
{if $ROWS_COUNT>20}
    {math assign="ROWS_COUNT1" equation="($ROWS_COUNT1/2)+2"}
    {math assign="ROWS_COUNT2" equation="($ROWS_COUNT2/2)+2"}
    {assign var="COL_SPAN" value=$COL_SPAN+2 }
{/if}

<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <thead>
                   <tr class="blockHeader">
                       <th colspan="6">
                          {vtranslate('LBL_LABELS',$MODULE)}
                          <input type="hidden" name="labels_to_go" id="labels_to_go" value="_XYZ_">
	                        {* tooltip start *}
	                        {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP7_INFO',$MODULE)}
	                        {include file='modules/ITS4YouReports/TooltipElement.tpl'}
													{* tooltip end *}
                       </th>
                   </tr>
                </thead>
                <tbody> 
                {assign var="ROWS_I" value=0 }
                {foreach key=lbl_type item=type_arr from=$labels_html}
                    {if $lbl_type == "SC"}
                        {assign var="type_row_lbl" value=vtranslate("LBL_SC_LABELS",$MODULE)}
                    {elseif $lbl_type == "SM"}
                        {assign var="type_row_lbl" value=vtranslate("LBL_SM_LABELS",$MODULE)}
                    {/if}
                    {if !empty($type_arr)}
                        <tr>
                            <td colspan="4" style="text-align: center;color:silver;font-size:1.1em;"><b>{$type_row_lbl}</b></td>
                        </tr>
                    {/if}
                    {assign var="ROWS_I" value=$ROWS_I+1 }
                    {assign var="make_row" value=1 }
                    {foreach key=fieldi item=fieldarray from=$type_arr}
                        {assign var="fieldkey" value=$fieldarray.translated_key}
                        {assign var="fieldinput" value=$fieldarray.translate_html} 
                        {if $ROWS_COUNT>20}
                            {if $make_row == 1}
                                <tr style="height:25px">
                                {/if}
                                <td class="dvtCellLabel" align="left" colspan="1"><b>{$fieldkey}</b></td>
                                <td class="dvtCellInfo" align="left" colspan="1">{$fieldinput}</td>
                                {if $make_row == 2}
                                    {assign var="make_row" value=1 }
                                </tr>
                            {else}
                                {assign var="make_row" value=$make_row+1 }
                            {/if}
                        {else}
                            <tr style="height:25px">
                                <td class="dvtCellLabel" align="left" colspan="1"><b>{$fieldkey}</b></td>
                                <td class="dvtCellInfo" align="left" colspan="1">{$fieldinput}</td>
                            </tr>
                        {/if}
                        {assign var="ROWS_I" value=$ROWS_I+1 }
                    {/foreach}
                {/foreach}
                </tbody> 
            </table>
        </div>
    </div>
</div> 
{/strip} 
