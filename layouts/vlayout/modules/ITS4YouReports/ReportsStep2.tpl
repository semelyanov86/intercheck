{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<div class="row-fluid">       
    <div class="span8">
        <div class="row-fluid">  
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_REPORT_DETAILS',$MODULE)}
                            <input type="hidden" name='all_related_modules' id='all_related_modules' value="{$REL_MODULES_STR}"/>
                       </th>
                   </tr>
                </thead>
                <tbody>    
                    <tr>
                        <td>{vtranslate('LBL_RELATIVE_MODULES',$MODULE)}:</td>
                        <td>
                            {if $RELATEDMODULES|@count > 0}
                                <table class="small">
                                {*foreach key=relmodkey item=relmod from=$RELATEDMODULES}
                                        {assign var="relmodname" value=$relmod.name }
                                        {assign var="relmodid" value=$relmod.id }
                                        {assign var="relmodchecked" value=$relmod.checked }
                                                <tr valign='top'><td><input type='checkbox' name="relmodule_{$relmodid}" id="relmodule_{$relmodid}" {if $relmodchecked!=''}checked{/if} value="{$relmodid}" />
                                                        {if $APP.$relmodname neq ''}
                                                                {$APP.$relmodname}
                                                        {else}
                                                                {$relmodname}
                                                        {/if}
                                                </td></tr>
                                {/foreach*}
                                </table>
                            {else}
                                    {vtranslate('NO_REL_MODULES',$MODULE)}
                            {/if}
                        </td>
                    </tr>
                </tbody> 
            </table>
        </div>
    </div>
    <div class="span4">
        <table class="dvtContentSpace" border="0" cellpadding="3" cellspacing="0" width="100%" style="float:left;border:0px;">
            <tr style="height:25px">
                <td class="detailedViewHeader">
                    <i class="icon-info-sign"></i>&nbsp;{vtranslate('LBL_INFORMATIONS_4YOU',$MODULE)}<br>
                </td>
            </tr>
            <tr style="height:25px">
                <td class="dvtCellInfo">
			{vtranslate('LBL_INFORMATIONS_4YOU',$MODULE)}
		</td>
            </tr>
        </table>
    </div>
</div> 