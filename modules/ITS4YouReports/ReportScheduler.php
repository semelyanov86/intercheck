<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
// require_once('modules/ITS4YouReports/ITS4YouReports.php');
require_once 'modules/ITS4YouReports/ScheduledReports4You.php';

global $app_strings;
global $app_list_strings;
global $mod_strings;
$current_module_strings = return_module_language($current_language, 'ITS4YouReports');
global $list_max_entries_per_page;
global $urlPrefix;

$log = LoggerManager::getLogger('report_type');
global $currentModule;
global $image_path;
global $theme;
global $current_user;

$smarty_obj = new vtigerCRM_Smarty; 
$smarty_obj->assign("MOD", $mod_strings);
$smarty_obj->assign("APP", $app_strings);
$smarty_obj->assign("IMAGE_PATH",$image_path);
$smarty_obj->assign("DATEFORMAT",$current_user->date_format);
$smarty_obj->assign("JS_DATEFORMAT",parse_calendardate($app_strings['NTC_DATE_FORMAT']));

/* SCHEDULE REPORTS START */
$reportid = "";
if(isset($_REQUEST["record"]) && $_REQUEST["record"]!=""){
	$reportid = vtlib_purify($_REQUEST["record"]);
}
$scheduledReport = new ITS4YouScheduledReport($adb, $this->current_user, $reportid);

$availableUsersHTML = $scheduledReport->getAvailableUsersHTML();
$availableGroupsHTML = $scheduledReport->getAvailableGroupsHTML();
$availableRolesHTML = $scheduledReport->getAvailableRolesHTML();
$availableRolesAndSubHTML = $scheduledReport->getAvailableRolesAndSubordinatesHTML();

$smarty_obj->assign("AVAILABLE_USERS", $availableUsersHTML);
$smarty_obj->assign("AVAILABLE_GROUPS", $availableGroupsHTML);
$smarty_obj->assign("AVAILABLE_ROLES", $availableRolesHTML);
$smarty_obj->assign("AVAILABLE_ROLESANDSUB", $availableRolesAndSubHTML);

$scheduledReport->id = $reportid;
$scheduledReport->user = $current_user;
$scheduledReport->getReportScheduleInfo();
if(isset($_REQUEST['mode']) && $_REQUEST['mode']=='ajax'){
	$is_scheduled = $_REQUEST['isReportScheduled'];
	$report_format = $_REQUEST['scheduledReportFormat'];
	$selectedRecipientsHTML = $scheduledReport->getSelectedRecipientsHTML();
}else{
	$is_scheduled = $scheduledReport->isScheduled;
	$report_format = $scheduledReport->scheduledFormat;
	$selectedRecipientsHTML = $scheduledReport->getSelectedRecipientsHTML();
}

$smarty_obj->assign('IS_SCHEDULED', $is_scheduled);
$smarty_obj->assign('REPORT_FORMAT', $report_format);

$smarty_obj->assign("SELECTED_RECIPIENTS", $selectedRecipientsHTML);

$smarty_obj->assign("schtypeid",$scheduledReport->scheduledInterval['scheduletype']);
$smarty_obj->assign("schtime",$scheduledReport->scheduledInterval['time']);
$smarty_obj->assign("schday",$scheduledReport->scheduledInterval['date']);
$smarty_obj->assign("schweek",$scheduledReport->scheduledInterval['day']);
$smarty_obj->assign("schmonth",$scheduledReport->scheduledInterval['month']);
/* SCHEDULE REPORTS END */

$PDFMakerInstalled = vtlib_isModuleActive("PDFMaker");

if($PDFMakerInstalled===true && file_exists('modules/PDFMaker/mpdf/mpdf.php')===true){
    $PDFMakerInstalled = true;
}else{
    $PDFMakerInstalled = false;
}
$smarty_obj->assign("PDFMakerActive", $PDFMakerInstalled);

if(isset($_REQUEST['mode']) && $_REQUEST['mode']=='ajax'){
	$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportSchedulerContent.tpl'));
}else{
	$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportScheduler.tpl'));
}
?>
