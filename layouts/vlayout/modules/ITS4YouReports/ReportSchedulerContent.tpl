{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<table class="table table-bordered table-report">
    <thead>
        <tr class="blockHeader">
           <th colspan="2">
            {vtranslate('LBL_SCHEDULE_EMAIL',$MODULE)}
            {* tooltip start *}
            {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP10_INFO',$MODULE)}
            {include file='modules/ITS4YouReports/TooltipElement.tpl'}
							{* tooltip end *}
           </th>
       </tr>
    </thead>
    <tbody> 
{* //ITS4YOU-CR SlOl 4. 4. 2016 9:49:42 *}
        <tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('Active',$MODULE)}</label></td>
            <td>
                <input type="checkbox" name="isReportScheduled" id="isReportScheduled" {if $IS_SCHEDULED eq '1'} checked {/if}>
            </td>
        </tr>
        <tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('LBL_GENERATE_SUBJECT',$MODULE)}</label></td>
            <td>
                <input class="span10" type="text" name="generate_subject" id="generate_subject" placeholder="{vtranslate('LBL_GENERATE_PLACEHOLDER',$MODULE)}" value="{$generate_subject}" />
            </td>
        </tr>
        <tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('LBL_GENERATE_TEXT',$MODULE)}</label></td>
            <td>
                {*
                <input class="span10" type="text" name="generate_text" id="generate_text" placeholder="{vtranslate('LBL_GENERATE_PLACEHOLDER',$MODULE)}" value="{$generate_text}" />
                *}
                <textarea class="span10" type="text" name="generate_text" id="generate_text" >{$generate_text}</textarea>                
            </td>
        </tr>
{* //ITS4YOU-END *}
        <tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('LBL_SCHEDULE_FREQUENCY',$MODULE)}</label></td>
            <td>
                <div class="row-fluid">    
                    <div class="span" id="scheduledTypeSpan" >
                        <select class="chzn-select " name="scheduledType" id="scheduledType" onchange="javascript: setScheduleOptions();">
                                <!-- Hourly doesn't make sense on OD as the cron job is running once in 2 hours -->
                                {*<option id="schtype_1" value="1" {if $schtypeid eq 1}selected{/if}>{vtranslate('Hourly',$MODULE)}</option>*}
                                <option id="schtype_2" value="2" {if $schtypeid eq 2}selected{/if}>{vtranslate('Daily',$MODULE)}</option>
                                <option id="schtype_3" value="3" {if $schtypeid eq 3}selected{/if}>{vtranslate('Weekly',$MODULE)}</option>
                                <option id="schtype_4" value="4" {if $schtypeid eq 4}selected{/if}>{vtranslate('BiWeekly',$MODULE)}</option>
                                <option id="schtype_5" value="5" {if $schtypeid eq 5}selected{/if}>{vtranslate('Monthly',$MODULE)}</option>
                                <option id="schtype_6" value="6" {if $schtypeid eq 6}selected{/if}>{vtranslate('Annually',$MODULE)}</option>
                        </select>
                    </div>
                    <div class="span" id="scheduledMonthSpan" style="display: {if $schtypeid eq 6}inline{else}none{/if};">&nbsp;<strong>{'LBL_SCHEDULE_EMAIL_MONTH'|@getTranslatedString:'ITS4YouReports'}</strong>:
                            <select class="chzn-select span2" name="scheduledMonth" id="scheduledMonth">
                                    {assign var="MONTH_STRINGS" value=vtranslate('MONTH_STRINGS',$MODULE)}
                                    {foreach key=mid item=month from=$MONTH_STRINGS}
                                    <option value="{$mid}" {if $schmonth eq $mid}selected{/if}>{$month}</option>
                                    {/foreach}
                            </select>
                    </div>

                    <!-- day of month (monthly, annually) -->
                    <div class="span" id="scheduledDOMSpan" style="display: {if $schtypeid eq 5 || $schtypeid eq 6}inline{else}none{/if};">&nbsp;<strong>{vtranslate('LBL_SCHEDULE_EMAIL_DAY',$MODULE)}</strong>:
                            <select class="chzn-select span1" name="scheduledDOM" id="scheduledDOM">
                                    {section name=day start=1 loop=32}
                                    <option value="{$smarty.section.day.iteration}" {if $schday eq $smarty.section.day.iteration}selected{/if}>{$smarty.section.day.iteration}</option>
                                    {/section}
                            </select>
                    </div>

                    <!-- day of week (weekly/bi-weekly) -->
                    <div class="span" id="scheduledDOWSpan" style="display: {if $schtypeid eq 3 || $schtypeid eq 4}inline{else}none{/if};">&nbsp;<strong>{vtranslate('LBL_SCHEDULE_EMAIL_DOW',$MODULE)}</strong>:
                            <select class="chzn-select  span2" name="scheduledDOW" id="scheduledDOW">
                                    {assign var="WEEKDAY_STRINGS" value=vtranslate('WEEKDAY_STRINGS',$MODULE)}
                                    {foreach key=wid item=week from=$WEEKDAY_STRINGS}
                                    <option value="{$wid}" {if $schweek eq $wid}selected{/if}>{$week}</option>
                                    {/foreach}
                            </select>
                    </div>

                    <!-- time (daily, weekly, bi-weekly, monthly, annully) -->
                    <div class="span" id="scheduledTimeSpan" style="display: {if $schtypeid > 0}inline{else}none{/if};">&nbsp;<strong>{vtranslate('LBL_SCHEDULE_EMAIL_TIME',$MODULE)}</strong>:
                            <input class="span2" type="text" name="scheduledTime" id="scheduledTime" value="{$schtime}" size="5" maxlength="5" />&nbsp;{vtranslate('LBL_TIME_FORMAT_MSG',$MODULE)}
                    </div>
                </div>
                <input type="hidden" name="scheduledIntervalString" value="" />
            </td>
	</tr>
	<tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_FORMAT',$MODULE)}</label></td>
            <td>
                {vtranslate('LBL_REPORT_FORMAT_PDF',$MODULE)} <input type="checkbox" name="scheduledReportFormat_pdf" id="scheduledReportFormat_pdf" {if $REPORT_FORMAT_PDF eq 'true'} checked {/if}>&nbsp;
                {vtranslate('LBL_REPORT_FORMAT_EXCEL',$MODULE)} <input type="checkbox" name="scheduledReportFormat_xls" id="scheduledReportFormat_xls" {if $REPORT_FORMAT_XLS eq 'true'} checked {/if}>&nbsp;
            </td>
	</tr>
	<tr>
            <td class="fieldLabel medium">
              <label class="pull-right marginRight10px">{vtranslate('LBL_USERS_AVAILABEL',$MODULE)}
                <a data-original-title="" href="#" class="editHelpInfo" data-placement="top" data-text="{vtranslate('LBL_USERS_AVAILABEL_INFO',$MODULE)}" data-template="<div class=&quot;tooltip&quot; role=&quot;tooltip&quot;><div class=&quot;tooltip-arrow&quot;></div><div class=&quot;tooltip-inner&quot; style=&quot;text-align: left&quot;></div></div>"><i class="icon-info-sign alignMiddle"></i>&nbsp;</a>
              </label>
            </td>
            <td>
                {vtranslate('LBL_SELECT',$MODULE)}:&nbsp;
                <select id="recipient_type" name="recipient_type" class="chzn-select" onChange="showRecipientsOptions();clearRecipients();">
                        <option value="users">{'LBL_USERS'|@getTranslatedString:'ITS4YouReports'}</option>
                        <option value="groups">{'LBL_GROUPS'|@getTranslatedString:'ITS4YouReports'}</option>
                        <option value="roles">{'LBL_ROLES'|@getTranslatedString:'ITS4YouReports'}</option>
                        <option value="rs">{'LBL_ROLES_SUBORDINATES'|@getTranslatedString:'ITS4YouReports'}</option>
                </select>
                
                <input type="text" id="search_recipient" onkeyup="getRecipientsOptionsSearch(this)" placeholder="{vtranslate('LBL_Search_recipient',$MODULE)}" style="margin:auto;">
                
                <div class="row-fluid">
                    <div class="span5">
                        <strong>{vtranslate('LBL_SELECT_USERS',$MODULE)}</strong><br />
                        <div id="availableRecipientsWrapper" style="width:100%;"></div>
                    </div>
                    <div class="span1" align="center">
                        <br /><br />
                        <a href="#" class="btn addButtonR4You" name="addButton" onClick="addOption()" > >> </a><br /><br />
                        <a href="#" class="btn addButtonR4You" name="delButton" onClick="delOption()" > << </a>
                    </div>
                    <div class="span5">
                        <strong>{vtranslate('LBL_USERS_SELECTED',$MODULE)}</strong><br />
                        <select id="selectedRecipients" name="selectedRecipients" multiple size="6" class="crmFormList" style="width:100%;">
                        {$SELECTED_RECIPIENTS}
                        </select>
                        <input type="hidden" name="selectedRecipientsString"/>
                    </div>
                </div>
            </td>
	</tr>
	<tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px"></label></td>
            <td>
                <input class="span10" type="text" name="generate_other" id="generate_other" placeholder='{vtranslate('LBL_GENERATE_OTHER',$MODULE)}' value="{$generate_other}" />
            </td>
	</tr>
	<tr>
            <td class="fieldLabel medium">
              <label class="pull-right marginRight10px">
                {vtranslate('LBL_GENERATE_FOR',$MODULE)}
                <a data-original-title="" href="#" class="editHelpInfo" data-placement="top" data-text='{vtranslate("LBL_GENERATE_FOR_INFO",$MODULE)}' data-template="<div class=&quot;tooltip&quot; role=&quot;tooltip&quot;><div class=&quot;tooltip-arrow&quot;></div><div class=&quot;tooltip-inner&quot; style=&quot;text-align: left&quot;></div></div>"><i class="icon-info-sign alignMiddle"></i>&nbsp;</a>
              </label>
            </td>
            <td>
                <input type="hidden" name="selectedGenerateForString" id="selectedGenerateForString" value=""/>
                <div id="generate_for_div">
                    {include file='modules/ITS4YouReports/generateFor.tpl'}
                </div>
            </td>
	</tr>
	<tr>
            <td class="fieldLabel medium"><label class="pull-right marginRight10px">{vtranslate('LBL_SCHEDULE_ALL_RECORDS',$MODULE)}</label></td>
            <td>
                <input type="checkbox" name="schedule_all_records" id="schedule_all_records" {if $schedule_all_records eq '1'} checked {/if}>&nbsp;
            </td>
	</tr>

    <tbody>
</table>