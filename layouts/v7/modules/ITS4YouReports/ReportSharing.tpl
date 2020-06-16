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
<div style="border:1px solid #ccc;padding:4%;">
    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate("LBL_TEMPLATE_OWNER",$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <select class="select2 col-lg-12 inputElement" name="template_owner" id="template_owner" data-rule-required="true">
                    {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate("LBL_SHARING_TAB",$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <select class="select2 col-lg-12 inputElement" name="sharing" id="sharing" data-rule-required="true" onchange="sharing_changed();">
                    {html_options  options=$SHARINGTYPES selected=$SHARINGTYPE}
                </select>
            </div>
        </div>
    </div>

    <div class="row" id="sharing_share_div">
        <div class="form-group">
            {* show all the users,groups,roles and subordinat roles*}
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SHARE_WITH',$MODULE)}</label>
            <div class='col-lg-4'>
                {assign var=ALL_ACTIVEUSER_LIST value=$CURRENT_USER->getAccessibleUsers()}
                {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENT_USER->getAccessibleGroups()}
                {assign var=recipients value=$RECIPIENTS}
                <input type="hidden" name="recipientsString_v7" />
                <select multiple class="select2 col-lg-12 inputElement" id='recipients' name='recipients' data-rule-required="true" >
                    <optgroup label="{vtranslate('LBL_USERS',$MODULE)}">
                        {foreach key=USER_ID item=USER_NAME from=$ALL_ACTIVEUSER_LIST}
                            {assign var=USERID value="users::{$USER_ID}"}
                            <option value="{$USERID}" {if is_array($recipients) && in_array($USERID, $recipients)} selected {/if}
                                    data-picklistvalue='{$USER_NAME}'> {$USER_NAME} </option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_GROUPS',$MODULE)}">
                        {foreach key=GROUP_ID item=GROUP_NAME from=$ALL_ACTIVEGROUP_LIST}
                            {assign var=GROUPID value="groups::{$GROUP_ID}"}
                            <option value="{$GROUPID}" {if is_array($recipients) && in_array($GROUPID, $recipients)} selected {/if}
                                    data-picklistvalue='{$GROUP_NAME}'>{$GROUP_NAME}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_ROLES',$MODULE)}">
                        {foreach key=ROLE_ID item=ROLE_OBJ from=$ROLES}
                            {assign var=ROLEID value="roles::{$ROLE_ID}"}
                            <option value="{$ROLEID}" {if is_array($recipients) && in_array($ROLEID, $recipients)} selected {/if}
                                    data-picklistvalue='{$ROLE_OBJ->get('rolename')}'>{$ROLE_OBJ->get('rolename')}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_ROLES_SUBORDINATES',$MODULE)}">
                        {foreach key=RS_ROLE_ID item=RS_ROLE_OBJ from=$ROLES}
                            {assign var=RS_ROLEID value="rs::{$RS_ROLE_ID}"}
                            <option value="{$RS_ROLEID}" {if is_array($recipients) && in_array($RS_ROLEID, $recipients)} selected {/if}
                                    data-picklistvalue='{$RS_ROLE_OBJ->get('rolename')}'>{$RS_ROLE_OBJ->get('rolename')}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>
    </div>

</div>
{/strip}