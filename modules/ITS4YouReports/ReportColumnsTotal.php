<?php

require_once("data/Tracker.php");
require_once('Smarty_setup.php');
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
$current_module_strings = return_module_language($current_language, 'ITS4YouReports');

global $list_max_entries_per_page;
global $urlPrefix;

global $currentModule;
global $image_path;
global $theme;
$smarty_obj=new vtigerCRM_Smarty;
$smarty_obj->assign("MOD", $mod_strings);
$smarty_obj->assign("APP", $app_strings);
$smarty_obj->assign("IMAGE_PATH",$image_path);

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

$smarty_obj = $ITS4YouReports->getSelectedValuesToSmarty($smarty_obj,"ReportColumnsTotal");

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,'ReportColumnsTotal.tpl'));
?>
