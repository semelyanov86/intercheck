<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $currentModule;
global $image_path;
global $theme;
global $current_user;

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

$smarty_obj = new vtigerCRM_Smarty; 
$smarty_obj->assign("MOD", $mod_strings);
$smarty_obj->assign("APP", $app_strings);
$smarty_obj->assign("IMAGE_PATH",$image_path);
$smarty_obj->assign("DATEFORMAT",$current_user->date_format);
$smarty_obj->assign("JS_DATEFORMAT",parse_calendardate($app_strings['NTC_DATE_FORMAT']));

$smarty_obj = $ITS4YouReports->getSelectedValuesToSmarty($smarty_obj,"ReportGraphs");

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportGraphs.tpl'));
?>
