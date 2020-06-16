{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    {include file="EditHeader.tpl"|vtemplate_path:$MODULE}

    <form name="NewReport" id="NewReport" class="form-horizontal recordEditView" action="index.php" method="POST" enctype="multipart/form-data" onsubmit="return changeSteps();">

    <input type="hidden" name="module" value="ITS4YouReports">
    {*<input type="text" name="primarymodule" id="primarymodule" value="{$REP_MODULE}">*}
    <input type="hidden" name='secondarymodule' id='secondarymodule' value="{$SEC_MODULE}"/>
    <input type="hidden" name="record" id="record" value="{$RECORDID}">
    <input type="hidden" name='modulesString' id='modulesString' value=''/>
    <input type="hidden" name='reload' id='reload' value='true'/>
    <input type="hidden" name='action' id='action' value='Save'/>
    <input type="hidden" name='file' id='file' value=''/>
    <input type="hidden" name='folder' id='folder' value="{$FOLDERID}"/>
    <input type="hidden" name='relatedmodules' id='relatedmodules' value='{$relmodulesstring}'/>
    <input type="hidden" name='mode' id='mode' value='{$MODE}' />
    <input type="hidden" name='isDuplicate' id='isDuplicate' value='{$isDuplicate}' />
    <input type="hidden" name='SaveType' id='SaveType_v7' value='' />
    <input type="hidden" name='actual_step' id='actual_step' value='1' />
    <input type="hidden" name='cancel_btn_url' id='cancel_btn_url' value='{$cancel_btn_url}' />

    <input type="hidden" name="reporttype" id="reporttype" value="{$REPORTTYPE}">

    {*************************		 STEP 1 		************************}
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
        {$REPORT_CUSTOM_CALCULATIONS}
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
    {*************************		 STEP 14 		************************}
    <div class="{$steps_display}" id="step14">
        {$REPORT_MAPS}
    </div>
    {*************************		 STEP 14 END 		************************}

    {include file="Buttons.tpl"|vtemplate_path:$MODULE}

    </form>
    {include file='modules/ITS4YouReports/EditScript.tpl'}
    </div>
</div>
{/strip}