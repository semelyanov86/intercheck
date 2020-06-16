{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	<input type="hidden" name="reporttype" id="reporttype" value='{$REPORTTYPE}' />

	<div name='editContent'>
        {*************************		 STEP 1 		************************ }
		<div class="reportTab" id="step1">
            {include file='modules/ITS4YouReports/ReportsStep1.tpl'}
		</div>
        {*************************		 STEP 3 END 		************************}

        {*************************		 STEP 4 		************************}
		<div class="{$steps_display}" id="step4">
            {$REPORT_GROUPING}
		</div>
        {*************************		 STEP 4 END 		************************}

        {*************************		 STEP 5 		************************}
		<div class="{$steps_display}" id="step5">
            {$REPORT_COLUMNS}
		</div>
        {*************************		 STEP 5 END 		************************}

        {*************************		 STEP 6 		************************}
		<div class="{$steps_display}" id="step6">
            {$REPORT_COLUMNS_TOTAL}
		</div>
		<div class="{$steps_display}" id="step61">
            {if $RECORDID!=""}
                {$REPORT_CUSTOM_CALCULATIONS}
            {/if}
		</div>
        {*************************		 STEP 6 END 		************************}
        {*************************		 STEP 7 		************************}
		<div class="{$steps_display}" id="step7">
            {$REPORT_LABELS}
		</div>
        {*************************		 STEP 7 END 		************************}
        {*************************		 STEP 8 		************************}
		<div class="{$steps_display}" id="step8">
            {$REPORT_FILTERS}
		</div>
        {*************************		 STEP 8 END 		************************}
        {*************************		 STEP 9 		************************}
		<div class="{$steps_display}" id="step9">
            {$REPORT_SHARING}
		</div>
        {*************************		 STEP 9 END 		************************}
        {*************************		 STEP 10 		************************}
		<div class="{$steps_display}" id="step10">
            {$REPORT_SCHEDULER}
		</div>
        {*************************		 STEP 10 END 		************************}
        {*************************		 STEP 11 		************************}
        {*<div id="step11" style="display:block;">
                {php}include("modules/ITS4YouReports/ReportQuickFilter.php");{/php}
        </div>*}
        {*************************		 STEP 11 END 		************************}
        {*************************		 STEP 12 		************************}
		<div class="{$steps_display}" id="step11">
            {$REPORT_GRAPHS}
		</div>
        {*************************		 STEP 12 END 		************************}
        {*************************		 STEP 13 		************************}
		<div class="{$steps_display}" id="step12">
            {$REPORT_DASHBOARDS}
		</div>
        {*************************		 STEP 13 END 		************************}
	</div>
{/strip}
