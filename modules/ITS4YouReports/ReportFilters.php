<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');

/*
global $app_list_strings;
global $app_strings;
global $mod_strings;

// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
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

// ITS4YOU-CR SlOl 10. 9. 2013 16:13:47
$LBL_INFORMATIONS_4YOU = getTranslatedString("LBL_STEP7_INFO",$currentModule);
$smarty_obj->assign("LBL_INFORMATIONS_4YOU", $LBL_INFORMATIONS_4YOU);
// ITS4YOU-END 10. 9. 2013 16:13:50 

// ITS4YOU-CR SlOl 20.12.2010 R4U
include("modules/ITS4YouReports/StandardFilter.php");

include("modules/ITS4YouReports/AdvancedFilter.php");
// ITS4YOU-END SlOl

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportFilters.tpl'));
*/
global $app_strings;
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

$smarty_obj = $ITS4YouReports->getSelectedValuesToSmarty($smarty_obj,"ReportFilters");

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportFilters.tpl'));
?>