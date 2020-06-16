<?php


require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once('include/utils/UserInfoUtil.php');

global $currentModule;
require_once('modules/'.$currentModule.'/'.$currentModule.'.php');

global $adb, $current_user;
global $app_strings, $mod_strings;
global $theme,$default_charset,$current_language;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
if($ITS4YouReports->CheckPermissions("DETAIL") == false)
  $ITS4YouReports->DieDuePermission();
  
$smarty = new vtigerCRM_Smarty;

$orderby="reports4youid";
$dir="desc";

if(isset($_REQUEST["dir"]) && $_REQUEST["dir"]!=""){
    $dir=  vtlib_purify($_REQUEST["dir"]);
}

if(isset($_REQUEST["orderby"])){
  switch($_REQUEST["orderby"]){
    case "reports4youname":
      $orderby="reports4youname";            
      break;
    
    case "primarymodule":
      $orderby="primarymodule";      
      break;
      
    case "description":
      $orderby="description";      
      break;
  
    case "foldername":
      $orderby="foldername";      
      break;

    case "order":
      $orderby="order";
      break;
  }
}

include("version.php");
               
$version_type = $ITS4YouReports->GetVersionType();
$license_key = 'SergeyEm';

$smarty->assign("VERSION_TYPE",$version_type);
$smarty->assign("VERSION",ucfirst($version_type)." ".$version);
$smarty->assign("LICENSE_KEY",$license_key);

$folders = $ITS4YouReports->getReportFolders();
$smarty->assign("FOLDERS",$folders);
// $to_update = "false";
// $smarty->assign("TO_UPDATE",$to_update);  

$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("PARENTTAB", getParentTab());
$smarty->assign("IMAGE_PATH",$image_path);

$smarty->assign("ORDERBY",$orderby);
$smarty->assign("DIR",$dir);

// global $adb;$adb->setDebug(true);
$return_data = $ITS4YouReports->GetListviewData($orderby, $dir);
// $adb->setDebug(false);
$smarty->assign("REPORTSLIST",$return_data);
$category = getParentTab();
$smarty->assign("CATEGORY",$category);

if(is_admin($current_user)){
  $smarty->assign('IS_ADMIN','1');
}

$smarty->assign("MODULE",$currentModule);

$tool_buttons = Button_Check($currentModule);
$tool_buttons["Import"]="no";
$tool_buttons["Export"]="no";
$smarty->assign('CHECK', $tool_buttons);
if($ITS4YouReports->CheckPermissions("EDIT")) {
  $smarty->assign("EXPORT","yes");
}
if($ITS4YouReports->CheckPermissions("EDIT") && $ITS4YouReports->GetVersionType() != "deactivate" ) {
  $smarty->assign("EDIT","permitted");
  $smarty->assign("IMPORT","yes");
}
if($ITS4YouReports->CheckPermissions("DELETE") && $ITS4YouReports->GetVersionType() != "deactivate" ) {
  $smarty->assign("DELETE","permitted");
}

//search options
$alphabetical = AlphabeticalSearch($currentModule, 'index', 'reports4youname', 'true', 'basic', '', '', '', '', '');
$smarty->assign("ALPHABETICAL", $alphabetical);

$listview_header_search = array(
    "reports4youname" => $mod_strings["LBL_REPORT_NAME"],
    "primarymodule" => $mod_strings["LBL_MODULENAMES"],
    "foldername" => $mod_strings["LBL_FOLDERNAMES"],
    "description" => $mod_strings["LBL_DESCRIPTION"]
);
$smarty->assign("SEARCHLISTHEADER", $listview_header_search);
$smarty->assign("SEARCHFIELD", vtlib_purify($_REQUEST["search_field"]));
$smarty->assign("SEARCHTEXT", vtlib_purify($_REQUEST["search_text"]));

if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '')
    $smarty->display(vtlib_getModuleTemplate($currentModule, 'ListViewEntries.tpl'));
else
	$smarty->display(vtlib_getModuleTemplate($currentModule,'ListReports4You.tpl'));
?>