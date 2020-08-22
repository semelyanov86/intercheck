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
{assign var="custom_style" value=" style='' " }
{strip}
    <div class="row-fluid">       
    <div style="width:100%;">
        <div class="row-fluid">           
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                       			{vtranslate('LBL_REPORT_DETAILS',$MODULE)}
                            {* tooltip start *}
                            {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP1_INFO',$MODULE)}
                            {include file='modules/ITS4YouReports/TooltipElement.tpl'}
														{* tooltip end *}
                       </th>
                   </tr>
                </thead>
                <tbody>    
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">
                                {vtranslate('LBL_REPORT_NAME',$MODULE)}<span class="redColor">*</span>
                            </label>
                        </td>
                        <td {$custom_style} ><input type="text" name="reportname" id="reportname" class="span11" style="margin:auto;" value="{$REPORTNAME}"></td>
                    </tr>
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} ><label class="pull-right marginRight10px">{vtranslate('LBL_PRIMARY_MODULE',$MODULE)}</label></td>
                        <td {$custom_style} >
                            <input name="report_primarymodule" id="report_primarymodule" type="hidden" >
                            <select name="primarymodule" id="primarymodule" class="span3 chzn-select row-fluid"  style="margin:auto;">
                                {foreach item=primarymodulearr from=$PRIMARYMODULES}
                                    {if $RECORD_MODE == "ChangeSteps" || $MODE == "edit"}
                                        {if $primarymodulearr.selected!=''}
                                            <option value="{$primarymodulearr.id}" selected >{$primarymodulearr.module}</option>
                                        {/if}
                                    {else}
                                        <option value="{$primarymodulearr.id}" {if $primarymodulearr.selected!=''}selected{/if} >{$primarymodulearr.module}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} ><label class="pull-right marginRight10px">{vtranslate('LBL_REP_FOLDER',$MODULE)}</label></td>
                        <td {$custom_style} >
                            <select name="reportfolder" id="reportfolder" class="span3 chzn-select row-fluid"  style="margin:auto;">
                            {foreach item=folder from=$REP_FOLDERS}
                                            <option value="{$folder.folderid}" {if $folder.selected!=''}selected{/if} >{$folder.foldername}</option>
                            {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} ><label class="pull-right marginRight10px">{vtranslate('LBL_DESCRIPTION',$MODULE)}</label></td>
                        <td align="left" {$custom_style} ><textarea name="reportdesc" id="reportdesc" class="txtBox" rows="5">{$REPORTDESC}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>      
{/strip}