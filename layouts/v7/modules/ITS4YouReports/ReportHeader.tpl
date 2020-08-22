{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <link rel="stylesheet" type="text/css" media="all" href="modules/ITS4YouReports/classes/Reports4YouDefault.css">
    <script type="text/javascript" src="modules/ITS4YouReports/highcharts/js/rgbcolor.js"></script>
    <script type="text/javascript" src="modules/ITS4YouReports/highcharts/js/canvg.js"> </script>

    <div class="reportsDetailHeader no-print">
        <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}'/>
        <input type="hidden" id="reportLimit" value="{$REPORT_LIMIT}"/>

        <input type="hidden" id="pdfmaker_active" value="{$PDFMakerActive}"/>
        <input type="hidden" id="is_test_write_able" value="{$IS_TEST_WRITE_ABLE}"/>
        <input type="hidden" id="div_id" value="activate_pdfmaker"/>

        <input type="hidden" name="report_filename" id="report_filename" value="" />

        <form method="post" action="IndexAjax" target="_blank">
            <input type="hidden" name="module" value="ITS4YouReports"/>
            <input type="hidden" name="action" value="IndexAjax"/>
            <input type="hidden" name="mode" value="ExportXLS"/>
            <input type="hidden" name="filename" value="Test.xls"/>
            <input type="hidden" name="report_html" id="report_html" value=""/>
        </form>
        <form method="post" action="index.php" name="GeneratePDF" id="GeneratePDF" target="_blank">
            <input type="hidden" name="module" value="ITS4YouReports"/>
            <input type="hidden" name="action" value="GeneratePDF"/>
            <input type="hidden" name="form_export_pdf_format" id="form_export_pdf_format" value=""/>
            <input type="hidden" name="form_filename" id="form_filename" value=""/>
            <input type="hidden" name="form_report_name" id="form_report_name" value=""/>
            <input type="hidden" name="form_report_html" id="form_report_html" value=""/>
            <input type="hidden" name="form_report_totals" id="form_report_totals" value=""/>
            <input type="hidden" name="form_chart_canvas" id="form_chart_canvas" value=""/>
        </form>
        <form method="post" action="index.php" name="GenerateXLS" id="GenerateXLS" target="_blank">
            <input type="hidden" name="module" value="ITS4YouReports"/>
            <input type="hidden" name="view" value="ExportReport"/>
            <input type="hidden" name="mode" value="GetXLS"/>
            <input type="hidden" name="record" value="{$RECORD_ID}"/>
        </form>

        <form id="detailViewReport" onSubmit="" method="post" >
            <input type="hidden" name="module" value="ITS4YouReports"/>
            <input type="hidden" name="view" value="Detail"/>
            <input type="hidden" id="record" value="{$RECORD_ID}"/>
            <input type="hidden" name='reload' id='reload' value='true'/>

            <input type="hidden" name="currentMode" id="currentMode" value='generate' />
            <input type="hidden" name='report_changed' id='report_changed' value='{$REPORT_CHANGED}'/>

            <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}'/>

            {include file="DetailViewActions.tpl"|vtemplate_path:$MODULE}
            {if 'custom_report' neq $REPORT_MODEL->getReportType()}
		            <br>
		            <div class=''>
		                {assign var=filterConditionNotExists value=(empty($CRITERIA_GROUPS))}
		                <button class="btn btn-default" name="modify_condition" data-val="{$filterConditionNotExists}">
		                    <strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
		                    <i class="fa {if $filterConditionNotExists eq true} fa-chevron-right {else} fa-chevron-down {/if}"></i>
		                </button>
		            </div>
	            	<br>
		            <div class="row-fluid">
		                <input type="hidden" id="widgetReports4YouId" value="{$RECORD_ID}"/>
		                {if $REPORTTYPE!=="custom_report"}
		                    <div id="filterContainer" class="filterContainer filterElements well filterConditionContainer filterConditionsDiv {if empty($CRITERIA_GROUPS) eq true} hide {/if}">
		                        {$REPORT_FILTERS}
		                    </div>
		                {/if}
		            </div>
		            <br>
						{/if}
            <div class="row-fluid">
                <div class="textAlignCenter">
                    <button class="btn btn-default generateReport" data-mode="generate" value="{vtranslate('LBL_GENERATE_NOW',$MODULE)}"/>
                    <strong>{vtranslate('LBL_GENERATE_NOW',$MODULE)}</strong>
                    </button>&nbsp;
                    {if $REPORTTYPE!="custom_report" && $REPORT_MODEL->isEditable() eq true}
                        <button class="btn btn-success generateReport hide" id="saveReportBtn" data-mode="save" value="{vtranslate('LBL_SAVE',$MODULE)}"/>
                        <strong>{vtranslate('LBL_SAVE',$MODULE)}</strong>
                        </button>
                    {/if}
                    {* //ITS4YOU-CR SlOl | 14.4.2015 11:48  *}
                    {if $checkDashboardWidget != "" && $checkDashboardWidget != "Exist"}
                        <button class="btn addWidget" data-mode="addwidget" value="{vtranslate('LBL_ADD_WIDGET',$MODULE)}"/>
                        <strong>{vtranslate('LBL_ADD_WIDGET',$MODULE)}</strong>
                        </button>
                    {/if}
                    {* //ITS4YOU-END 14.4.2015 11:48  *}
                </div>
            </div>
            <br>
    </form>
    </div>
    <div id="reportContentsDiv" style="padding-bottom:2em;">
{/strip}