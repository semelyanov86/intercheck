{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="main-container clearfix">
    {if file_exists('modules/Vtiger/partials/Menubar.tpl')}
	    <div id="modnavigator" class="module-nav editViewModNavigator">
	        <div class="hidden-xs hidden-sm mod-switcher-container">
	            {include file="modules/Vtiger/partials/Menubar.tpl"}
	        </div>
	    </div>
    {/if}
    <div class="editViewPageDiv mailBoxEditDiv viewContent">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="editContainer" style="padding-left: 2%;padding-right: 0%; min-height:70em;">
    <input type="hidden" name="reporttype" id="reporttype" value="{$REPORTTYPE}" />
    <div class="row">
        {assign var=LABELS value = ["1" => "LBL_REPORT_DETAILS",
        "13" => "LBL_REPORT_SQL",
        "4" => "LBL_SPECIFY_GROUPING",
        "5" => "LBL_SELECT_COLUMNS",
        "6" => "LBL_CALCULATIONS",
        "7" => "LBL_LABELS",
        "8" => "LBL_FILTERS",
        "9" => "LBL_SHARING",
        "10" => "LBL_LIMIT_SCHEDULER",
        "11" => "LBL_GRAPHS",
        "12" => "LBL_REPORT_DASHBOARDS",
        "14" => "LBL_REPORT_MAPS"
        ]}
        {include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE ACTIVESTEP=1 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
    </div>
    <div class="clearfix"></div>
    {include file='modules/ITS4YouReports/Buttons.tpl'}
{/strip}