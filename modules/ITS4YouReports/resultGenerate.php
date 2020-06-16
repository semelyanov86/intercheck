<?php 

$x3e = "count";
$x3f = "file_exists";
$x40 = "flush";
$x41 = "html_entity_decode";
$x42 = "implode";
$x43 = "md5";
$x44 = "split";
$x45 = "urlencode";
require_once ('Smarty_setup.php');
require_once ('include/logging.php');
require_once ('include/utils/utils.php');
require_once ('modules/ITS4YouReports/ITS4YouReports.php');
require_once ('modules/ITS4YouReports/GenerateObj.php');
require_once ('modules/ITS4YouReports/classes/UIUtils.php');
require_once ('modules/ITS4YouReports/FilterUtils.php');
require_once ("include/Zend/Json.php");
include ('modules/ITS4YouReports/Reports4YouHeader.php');
$x0b = "adb";
$x0c = "vtiger_current_version";
$x0d = "site_URL";
$x0e = "default_charset";
$x0f = "currentModule";
$x10 = "mod_strings";
$x11 = "app_strings";
$$x0b = PearDatabase::getInstance();
global $$x10, $$x0c, $$x0d, $$x0e, $$x0f;
$x12 = $$x0b->query("SELECT license FROM its4you_reports4you_version WHERE version='" . $$x0c . "'");
if (true) {
    $x13 = "professional";
} else {
    $x13 = "invalid";
}
if (true) {
    $x14 = (isset($_REQUEST["record"]) && $_REQUEST["record"] != "" ? $_REQUEST["record"] : "");
    $x15 = ITS4YouReports::getStoredITS4YouReport();
    $x16 = $x15->reportinformations["folderid"];
    $x17 = new GenerateObj($x15);
    $x18 = new vtigerCRM_Smarty;
    if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] != "") {
        $x19 = vtlib_purify($_REQUEST["mode"]);
    } else {
        $x19 = "generate";
    }
    $x18->assign("MODE", $x19);
    $x15->getGroupFilterList($x14);
    $x15->getAdvancedFilterList($x14);
    $x15->getSummariesFilterList($x14);
    $x1a = Zend_Json::encode($x15->adv_sel_fields);
    $x18->assign("SEL_FIELDS", $x1a);
    if (isset($_REQUEST["reload"])) {
        $x1b = $x15->getRequestCriteria($x1a);
    } else {
        $x1b = $x15->advft_criteria;
    }
    $x18->assign("CRITERIA_GROUPS", $x1b);
    $x18->assign("EMPTY_CRITERIA_GROUPS", empty($x1b));
    $x18->assign("SUMMARIES_CRITERIA", $x15->summaries_criteria);
    $x1c = getAdvCriteriaHTML();
    $x18->assign("FOPTION", $x1c);
    $x18->assign("DISPLAY_FILTER_HEADER", true);
    $x1d = $x15->getAdvanceFilterOptionsJSON($x15->primarymodule);
    $x18->assign("COLUMNS_BLOCK", $x1e);
    if ($x19 != "ajax") {
        $x18->assign("filter_columns", $x1d);
        $x1a = Zend_Json::encode($x15->adv_sel_fields);
        $x18->assign("SEL_FIELDS", $x1a);
        $x1f = $x15->getStdFilterColumns();
        $x20 = $x42("<%jsstdjs%>", $x1f);
        $x20 = $x41($x20, ENT_QUOTES, $$x0e);
        $x18->assign("std_filter_columns", $x20);
        $x21 = Zend_Json::encode($x15->Date_Filter_Values);
        $x18->assign("std_filter_criteria", $x21);
    }
    $x22 = $x15->adv_rel_fields;
    $x18->assign("REL_FIELDS", Zend_Json::encode($x22));
    $x23 = $x15->getCriteriaJS();
    $x18->assign("BLOCKJS", $x23);
    $x18->assign("MOD", $$x10);
    $x18->assign("APP", $$x11);
    $x18->assign("IMAGE_PATH", $x24);
    $x25 = $x15->record;
    $x18->assign("REPORTID", $x25);
    $x18->assign("IS_EDITABLE", $x15->is_editable);
    $x18->assign("REPORTSTATE", "SAVED");
    $x18->assign("REPORTNAME", $x15->reportname);
    $x18->assign("REP_MODULE", $x15->primarymoduleid);
    $x18->assign("REPORTTOTHTML", $x26);
    $x18->assign("FOLDERID", $x16);
    $x18->assign("DATEFORMAT", $x27->date_format);
    $x18->assign("JS_DATEFORMAT", parse_calendardate($$x11['NTC_DATE_FORMAT']));
    if ($x28 == true) {
        $x18->assign("EXPORT_PERMITTED", "YES");
    } else {
        $x18->assign("EXPORT_PERMITTED", "NO");
    }
    $x29 = $x15->sgetRptsforFldr($x16);
    for ($x2a = 0;$x2a < $x3e($x29);$x2a++) {
        $x2b = $x29[$x2a]['reportid'];
        $x2c = $x29[$x2a]['reportname'];
        $x2d[$x2b] = $x2c;
    }
    $x2e = getTranslatedString("equals", "CustomView");
    $x2f = getTranslatedString("not equal to", "CustomView");
    $x30 = vtlib_isModuleActive("PDFMaker");
    if ($x30 === true && $x3f('modules/PDFMaker/mpdf/mpdf.php') === true) {
        $x30 = true;
    } else {
        $x30 = false;
    }
    $x18->assign("PDFMakerActive", $x30);
    $x18->assign("IS_TEST_WRITE_ABLE", false);
    $x31 = array();
    $x31[] = $x15->primarymodule;
    if (!empty($x15->secondarymodule)) {
        $x32 = $x44(":", $x15->secondarymodule);
        for ($x2a = 0;$x2a < $x3e($x32);$x2a++) {
            $x31[] = $x32[$x2a];
        }
    }
    $x18->assign("REPINFOLDER", $x2d);
    $x18->assign("DIRECT_OUTPUT", true);
    $x18->assign_by_ref("__REPORTID", $x25);
    $x18->assign_by_ref("__REPORT_RUN_INSTANCE", $x17);
    $x18->assign_by_ref("__REPORT_RUN_FILTER_LIST", $x33);
    $x34 = "";
    if ($x3e($x35) > 0) {
        foreach ($x35 AS $x36 => $x37) {
            foreach ($x37 AS $x38 => $x39) {
                $x34.= '&' . $x45($x36) . '[]=' . $x39;
            }
        }
    }
    foreach ($x3a AS $x36 => $x39) {
        $x34.= '&' . $x45($x36) . '=' . $x39;
    }
    $x18->assign("ADDTOURL", $x34);
    if ($x19 != "ajax") {
        include ('themes/' . $x3b . '/header.php');
        $x40();
    }
    $x3c = $$x10["NO_FILTER_SELECTED"];
    $x18->assign("REPORT_INFO", $x3c);
    $x18->assign("CURRENT_ACTION", vtlib_purify($_REQUEST["action"]));
} else {
    $x3d = "Invalid license key! Please contact the vendor of Reports 4You.";
    echo $x3d;
    exit;
}
$x18->display(vtlib_getModuleTemplate($$x0f, 'ReportGenerate.tpl')); 

?>