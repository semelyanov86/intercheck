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

<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">  
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_SHARING_TYPE',$MODULE)}
	                        {* tooltip start *}
	                        {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP9_INFO',$MODULE)}
	                        {include file='modules/ITS4YouReports/TooltipElement.tpl'}
													{* tooltip end *}
                       </th>
                   </tr>
                </thead>
                <tbody> 
                    <tr>
                        <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate("LBL_TEMPLATE_OWNER",$MODULE)}</label></td>
                        <td>
                            <select name="template_owner" id="template_owner" class="classname chzn-select row-fluid" style="width: 210px;">
                                {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate("LBL_SHARING_TAB",$MODULE)}</label></td>
                        <td>
                            <select name="sharing" id="sharing" class="classname chzn-select row-fluid" onchange="sharing_changed();" style="width: 210px;">
                                {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
                            </select>
                            <div id="sharing_share_div" style="display:none; border-top:2px dotted #DADADA; margin-top:10px; width:100%;">
                                <table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0">
                                    <tr>
                                            <td width="40%" valign=top class="cellBottomDotLinePlain small"><strong>{vtranslate("LBL_MEMBER_AVLBL",$MODULE)}</strong></td>
                                            <td width="10%">&nbsp;</td>
                                            <td width="40%" class="cellBottomDotLinePlain small"><strong>{vtranslate("LBL_MEMBER_SELECTED",$MODULE)}</strong></td>
                                    </tr>
                                    <tr>
                                            <td valign=top class="small">
                                                    {vtranslate("LBL_ENTITY",$MODULE)}:&nbsp;
                                                    <select id="sharingMemberType" name="sharingMemberType" class="classname chzn-select row-fluid" onchange="showSharingMemberTypes()" style="width: 210px;">
                                                    <option value="groups" selected>{vtranslate("LBL_GROUPS",$MODULE)}</option>
                                                    <option value="roles">{vtranslate("LBL_ROLES",$MODULE)}</option>
                                                    <option value="rs">{vtranslate("LBL_ROLES_SUBORDINATES",$MODULE)}</option>
                                                    <option value="users">{vtranslate("LBL_USERS",$MODULE)}</option>
                                                    </select>
                                                    <input type="hidden" name="sharingFindStr" id="sharingFindStr">&nbsp;
                                            </td>
                                            <td width="50">&nbsp;</td>
                                            <td class="small">&nbsp;</td>
                                    </tr>
                                    <tr class="small">
                                        <td valign=top>{vtranslate("LBL_MEMBER",$MODULE)} {vtranslate("LBL_OF",$MODULE)} {vtranslate("LBL_ENTITY",$MODULE)}<br>
                                                <select id="sharingAvailList" name="sharingAvailList" multiple size="10" class="small crmFormList"></select>
                                        </td>
                                        <td width="50">
                                                <div align="center">
                                                        {* //ITS4YOU-UP SlOl 20. 12. 2013 12:02:19 BUTTONS *}
                                                        {*<input type="button" name="sharingAddButt" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" onClick="sharingAddColumn()" class="crmButton small"/><br /><br />
                                                        <input type="button" name="sharingDelButt" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" onClick="sharingDelColumn()" class="crmButton small"/>*}
                                                        <a href="#" class="btn" name="sharingAddButt" onClick="sharingAddColumn()" > >> </a><br /><br />
                                                        <a href="#" class="btn" name="sharingDelButt" onClick="sharingDelColumn()" > << </a>
                                                        {* //ITS4YOU-END 20. 12. 2013 12:02:23 *}
                                                </div>
                                        </td>
                                        <td class="small" {* COLOR style="background-color:#ddFFdd"*} valign=top>{vtranslate("LBL_MEMBER",$MODULE)} {vtranslate("LBL_OF",$MODULE)} {if $GROUPNAME neq ""}&quot;{$GROUPNAME}&quot;{/if}<br>
                                                <select id="sharingSelectedColumns" name="sharingSelectedColumns" multiple size="10" class="small crmFormList">
                                                {foreach item=element from=$MEMBER}
                                                <option value="{$element.0}">{$element.1}</option>
                                                {/foreach}
                                                </select>
                                                <input type="hidden" name="sharingSelectedColumnsString" id="sharingSelectedColumnsString" value="" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>                 
