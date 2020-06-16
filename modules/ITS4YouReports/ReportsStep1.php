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
if(isset($_REQUEST["reportname"]) && $_REQUEST["reportname"]!=""){
    $reportname = htmlspecialchars(vtlib_purify($_REQUEST["reportname"]));
}else{
    $reportname = $ITS4YouReports->reportinformations["reports4youname"];
}
$smarty_obj->assign("REPORTNAME",$reportname);
if(isset($_REQUEST["reportdesc"]) && $_REQUEST["reportdesc"]!=""){
    $reportdesc = htmlspecialchars(vtlib_purify($_REQUEST["reportdesc"]));
}else{
    $reportdesc = $ITS4YouReports->reportinformations["reportdesc"];
}
$smarty_obj->assign("REPORTDESC",$reportdesc);
$smarty_obj->assign("REP_MODULE",$ITS4YouReports->primarymodule);
$smarty_obj->assign("PRIMARYMODULES",$ITS4YouReports->getPrimaryModules());
$smarty_obj->assign("REP_FOLDERS",$ITS4YouReports->getReportFolders());
if(isset($ITS4YouReports->primarymodule) && $ITS4YouReports->primarymodule!=''){
	$rel_modules = $ITS4YouReports->getReportRelatedModules($ITS4YouReports->primarymoduleid);
	foreach ($rel_modules as $key=>$relmodule) {
		$restricted_modules .= $relmodule['id'].":";
	}
	$smarty_obj->assign("REL_MODULES_STR",trim($restricted_modules, ":"));

	$smarty_obj->assign("RELATEDMODULES",$rel_modules);
}

$smarty_obj->assign("FOLDERID",vtlib_purify($_REQUEST['folder']));
$smarty_obj->assign("IMAGE_PATH", $image_path);
$smarty_obj->assign("THEME_PATH", $theme_path);
$smarty_obj->assign("ERROR_MSG", $mod_strings['LBL_NO_PERMISSION']);
$smarty_obj->display(vtlib_getModuleTemplate($currentModule,"ReportsStep1.tpl"));

?>