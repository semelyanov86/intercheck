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
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('Active',$MODULE)}</label>
            <div class="col-lg-4">
                <input type="checkbox" class="listViewEntriesMainCheckBox" name="isReportScheduled" id="isReportScheduled" {if $IS_SCHEDULED eq '1'} checked {/if}>
            </div>
        </div>
    </div>

    <div id="isReportScheduledArea" class="{if $IS_SCHEDULED neq '1'}hide{/if}" >
        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_GENERATE_SUBJECT',$MODULE)}</label>
                <div class="col-lg-4">
                    <input type="text" class="inputElement" data-rule-required="false" name="generate_subject" id="generate_subject"
                           placeholder="{vtranslate('LBL_GENERATE_PLACEHOLDER',$MODULE)}" value="{$generate_subject}"/>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_GENERATE_TEXT',$MODULE)}</label>
                <div class="col-lg-4 fieldBlockContainer">
                    <textarea rows="3" class="inputElement textAreaElement col-lg-12" id="generate_text" name="generate_text">{$generate_text}</textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <input type="hidden" name="scheduledIntervalString" value=""/>
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SCHEDULE_FREQUENCY',$MODULE)}</label>
                <div class="col-lg-4" id="scheduledTypeSpan">
                    <select class="select2 col-lg-12 inputElement" name="scheduledType" id="scheduledType" data-rule-required="false" onchange="javascript: setScheduleOptions();">
                        <!-- Hourly doesn't make sense on OD as the cron job is running once in 2 hours -->
                        <option id="schtype_2" value="2" {if $schtypeid eq 2}selected{/if}>{vtranslate('Daily',$MODULE)}</option>
                        <option id="schtype_3" value="3" {if $schtypeid eq 3}selected{/if}>{vtranslate('Weekly',$MODULE)}</option>
                        <option id="schtype_4" value="4" {if $schtypeid eq 4}selected{/if}>{vtranslate('BiWeekly',$MODULE)}</option>
                        <option id="schtype_5" value="5" {if $schtypeid eq 5}selected{/if}>{vtranslate('Monthly',$MODULE)}</option>
                        <option id="schtype_6" value="6" {if $schtypeid eq 6}selected{/if}>{vtranslate('Annually',$MODULE)}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- months -->
        <div class="row" id="scheduledMonthSpan" style="display: {if $schtypeid eq 6}block{else}none{/if};">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SCHEDULE_EMAIL_MONTH',$MODULE)}</label>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="scheduledMonth" id="scheduledMonth" data-rule-required="false">
                        {assign var="MONTH_STRINGS" value=vtranslate('MONTH_STRINGS',$MODULE)}
                        {foreach key=mid item=month from=$MONTH_STRINGS}
                            <option value="{$mid}" {if $schmonth eq $mid}selected{/if}>{$month}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>

        <!-- day of month (monthly, annually) -->
        <div class="row" id="scheduledDOMSpan" style="display: {if $schtypeid eq 5 || $schtypeid eq 6}block{else}none{/if};">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SCHEDULE_EMAIL_DAY',$MODULE)}</label>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="scheduledDOM" id="scheduledDOM">
                        {section name=day start=1 loop=32}
                            <option value="{$smarty.section.day.iteration}" {if $schday eq $smarty.section.day.iteration}selected{/if}>{$smarty.section.day.iteration}</option>
                        {/section}
                    </select>
                </div>
            </div>
        </div>

        <!-- day of week (weekly/bi-weekly) -->
        <div class="row" id="scheduledDOWSpan" style="display: {if $schtypeid eq 3 || $schtypeid eq 4}block{else}none{/if};">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SCHEDULE_EMAIL_DOW',$MODULE)}</label>
                <div class="col-lg-4">
                    <select class="select2 col-lg-12 inputElement" name="scheduledDOW" id="scheduledDOW">
                        {assign var="WEEKDAY_STRINGS" value=vtranslate('WEEKDAY_STRINGS',$MODULE)}
                        {foreach key=wid item=week from=$WEEKDAY_STRINGS}
                            <option value="{$wid}" {if $schweek eq $wid}selected{/if}>{$week}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>

        <!-- time (daily, weekly, bi-weekly, monthly, annully) -->
        <div class="row" id="scheduledTimeSpan" style="display: {if $schtypeid > 0}block{else}none{/if};">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_SCHEDULE_EMAIL_TIME',$MODULE)}</label>
                <div class="col-lg-4">
                    <input class="inputElement" type="text" name="scheduledTime" id="scheduledTime" value="{$schtime}" size="5"
                           maxlength="5"/>&nbsp;{vtranslate('LBL_TIME_FORMAT_MSG',$MODULE)}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_FORMAT',$MODULE)}</label>
                <div class="col-lg-4">
                    <label class="checkbox" style="margin-left: 30px;display: inline;"><input name="scheduledReportFormat_pdf"
                                                                                              id="scheduledReportFormat_pdf" {if $REPORT_FORMAT_PDF eq 'true'} checked {/if}
                                                                                              type="checkbox"/>&nbsp;{vtranslate('LBL_REPORT_FORMAT_PDF', $MODULE)}</label>
                    <label class="checkbox" style="margin-left: 30px;display: inline;"><input name="scheduledReportFormat_xls"
                                                                                              id="scheduledReportFormat_xls" {if $REPORT_FORMAT_XLS eq 'true'} checked {/if}
                                                                                              type="checkbox"/>&nbsp;{vtranslate('LBL_REPORT_FORMAT_EXCEL', $MODULE)}</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                {* show all the users,groups,roles and subordinat roles*}
                <label class="col-lg-2 control-label textAlignLeft">
                    {vtranslate('LBL_USERS_AVAILABEL',$MODULE)}
                    <a data-original-title="" href="#" class="editHelpInfo" data-placement="top" data-text="{vtranslate('LBL_USERS_AVAILABEL_INFO',$MODULE)}"
                       data-template="<div class=&quot;tooltip&quot; role=&quot;tooltip&quot;><div class=&quot;tooltip-arrow&quot;></div><div class=&quot;tooltip-inner&quot; style=&quot;text-align: left&quot;></div></div>"><i
                            class="icon-info-sign alignMiddle"></i>&nbsp;</a>
                </label>
                <input type="hidden" name="selectedRecipientsString_v7" />
                <div class='col-lg-4'>
                    {assign var=ALL_ACTIVEUSER_LIST value=$CURRENT_USER->getAccessibleUsers()}
                    {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENT_USER->getAccessibleGroups()}
                    {assign var=recipients value=$SCHEDULED_REPORT->scheduledRecipients}

                    {assign var=recipientsUsers value=$recipients.users}
                    {assign var=recipientsGroups value=$recipients.groups}
                    {assign var=recipientsRoles value=$recipients.roles}
                    {assign var=recipientsRS value=$recipients.rs}

                    <select multiple class="select2 col-lg-12 inputElement" id='selectedRecipients' name='selectedRecipients' data-rule-required="true">
                        <optgroup label="{vtranslate('LBL_USERS',$MODULE)}">
                            {foreach key=USER_ID item=USER_NAME from=$ALL_ACTIVEUSER_LIST}
                                {assign var=CLEAR_USERID value={$USER_ID}}
                                {assign var=USERID value="users::{$USER_ID}"}
                                <option value="{$USERID}" {if is_array($recipientsUsers) && in_array($CLEAR_USERID, $recipientsUsers)} selected {/if}
                                        data-picklistvalue='{$USER_NAME}'> {$USER_NAME} </option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('LBL_GROUPS',$MODULE)}">
                            {foreach key=GROUP_ID item=GROUP_NAME from=$ALL_ACTIVEGROUP_LIST}
                                {assign var=CLEAR_GROUP_ID value={$GROUP_ID}}
                                {assign var=GROUPID value="groups::{$GROUP_ID}"}
                                <option value="{$GROUPID}" {if is_array($recipientsGroups) && in_array($CLEAR_GROUP_ID, $recipientsGroups)} selected {/if}
                                        data-picklistvalue='{$GROUP_NAME}'>{$GROUP_NAME}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('LBL_ROLES',$MODULE)}">
                            {foreach key=ROLE_ID item=ROLE_OBJ from=$ROLES}
                                {assign var=CLEAR_ROLEID value={$ROLE_ID}}
                                {assign var=ROLEID value="roles::{$ROLE_ID}"}
                                <option value="{$ROLEID}" {if is_array($recipientsRoles) && in_array($CLEAR_ROLEID, $recipientsRoles)} selected {/if}
                                        data-picklistvalue='{$ROLE_OBJ->get('rolename')}'>{$ROLE_OBJ->get('rolename')}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('LBL_ROLES_SUBORDINATES',$MODULE)}">
                            {foreach key=RS_ROLE_ID item=RS_ROLE_OBJ from=$ROLES}
                                {assign var=CLEAR_RS_ROLE_ID value={$RS_ROLE_ID}}
                                {assign var=RS_ROLEID value="rs::{$RS_ROLE_ID}"}
                                <option value="{$RS_ROLEID}" {if is_array($recipientsRS) && in_array($CLEAR_RS_ROLE_ID, $recipientsRS)} selected {/if}
                                        data-picklistvalue='{$RS_ROLE_OBJ->get('rolename')}'>{$RS_ROLE_OBJ->get('rolename')}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">&nbsp;</label>
                <div class="col-lg-4">
                    <input type="text" class="inputElement" data-rule-required="false" name="generate_other" id="generate_other"
                           placeholder="{vtranslate('LBL_GENERATE_OTHER',$MODULE)}" value="{$generate_other}"/>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">
                    {vtranslate('LBL_GENERATE_FOR',$MODULE)}
                    <a data-original-title="" href="#" class="editHelpInfo" data-placement="top" data-text='{vtranslate("LBL_GENERATE_FOR_INFO",$MODULE)}'
                       data-template="<div class=&quot;tooltip&quot; role=&quot;tooltip&quot;><div class=&quot;tooltip-arrow&quot;></div><div class=&quot;tooltip-inner&quot; style=&quot;text-align: left&quot;></div></div>"><i
                            class="icon-info-sign alignMiddle"></i>&nbsp;</a>
                </label>
                <div class="col-lg-4">
                    <input type="hidden" name="selectedGenerateForString" id="selectedGenerateForString" value=""/>
                    <div id="generate_for_div">
                        {include file="modules/ITS4YouReports/generateFor.tpl"}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label class="col-lg-2 control-label textAlignLeft">
                    {vtranslate('LBL_SCHEDULE_ALL_RECORDS',$MODULE)}
                </label>
                <div class="col-lg-4">
                    <label class="checkbox" style="margin-left: 30px;display: inline;"><input name="schedule_all_records"
                                                                                              id="schedule_all_records" {if $schedule_all_records eq '1'} checked {/if}
                                                                                              type="checkbox"/></label>
                </div>
            </div>
        </div>
    </div>

</div>
{/strip}