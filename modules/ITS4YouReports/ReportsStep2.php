<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');

require_once('modules/ITS4YouReports/ITS4YouReports.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $list_max_entries_per_page;
global $urlPrefix;
$log = LoggerManager::getLogger('report_list');
global $currentModule;
global $image_path;
global $theme;
global $focus_list;
$recordid = vtlib_purify($_REQUEST['record']);

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
$smarty_obj = new vtigerCRM_Smarty;
$smarty_obj->assign("MOD", $mod_strings);
$smarty_obj->assign("APP", $app_strings);

if(isset($_REQUEST['parenttab']) && $_REQUEST['parenttab']!=''){
	$smarty_obj->assign("CATEGORY", $_REQUEST['parenttab']);
}

$recordid = vtlib_purify($_REQUEST['record']);

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

$smarty_obj->assign("RECORDID",$recordid);
if(isset($_REQUEST['secondarymodule'])){
	$smarty_obj->assign("SEC_MODULE",$_REQUEST['secondarymodule']);
}
// show($ITS4YouReports->related_modules);
if(isset($ITS4YouReports->primarymodule) && $ITS4YouReports->primarymodule!=''){
	$rel_modules = $ITS4YouReports->getReportRelatedModules($ITS4YouReports->primarymoduleid);
	foreach ($rel_modules as $key=>$relmodule) {
		$restricted_modules .= $relmodule['id'].":";
	}

	$smarty_obj->assign("REL_MODULES_STR",trim($restricted_modules, ":"));

	$smarty_obj->assign("RELATEDMODULES",$rel_modules);
}

$smarty_obj->assign("FOLDERID",$ITS4YouReports->folder);
$smarty_obj->assign("IMAGE_PATH", $image_path);
$smarty_obj->assign("THEME_PATH", $theme_path);

$smarty_obj = $ITS4YouReports->getSelectedValuesToSmarty($smarty_obj,"ReportsStep2");

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,"ReportsStep2.tpl"));
?>