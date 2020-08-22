<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once 'modules/ITS4YouReports/ScheduledReports4You.php';
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

$smarty_obj = new vtigerCRM_Smarty;
$smarty_obj->assign("MOD", $mod_strings);
$smarty_obj->assign("APP", $app_strings);

$ITS4YouReports = new ITS4YouReports();

if($ITS4YouReports->record!=""){
	$r_permitted = $ITS4YouReports->CheckReportPermissions($ITS4YouReports->primarymodule, $ITS4YouReports->record);
}

// ITS4YOU-CR SlOl | 1.8.2014 16:10  block mode for Developer Only
$smarty_obj->assign("block_mode", "none");
//$smarty_obj->assign("block_mode", "block");
/*global $current_user;if($current_user->id=="1"){
	$smarty_obj->assign("block_mode", "block");
}*/
// ITS4YOU-END 1.8.2014 16:11 

$smarty_obj->assign("currentModule", "$currentModule");

if(isset($_REQUEST['parenttab']) && $_REQUEST['parenttab']!=''){
	$smarty_obj->assign("CATEGORY", $_REQUEST['parenttab']);
}
	
if($recordid!=''){
    $ITS4YouReports->mode = 'edit';
    if(vtlib_isModuleActive($ITS4YouReports->primarymodule)==false){
            echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
            echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 80%; position: relative; z-index: 10000000;'>

            <table border='0' cellpadding='5' cellspacing='0' width='98%'>
            <tbody><tr>
            <td rowspan='2' width='11%'><img src='". vtiger_imageurl('denied.gif', $theme) ."' ></td>
            <td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>".$mod_strings['LBL_NO_ACCESS']." : ".$ITS4YouReports->primarymodule." </span></td>
            </tr>
            <tr>
            <td class='small' align='right' nowrap='nowrap'>			   	
            <a href='index.php?module=ITS4YouReports&amp;action=index&amp;parenttab=Analytics'>".$app_strings["LBL_CLOSE"]."</a><br>								   		     </td>
            </tr>
            </tbody></table> 
            </div>";
            echo "</td></tr></table>";die;
    }
    $smarty_obj->assign("RECORDID",$recordid);
    $smarty_obj->assign("REPORTNAME",$ITS4YouReports->reportinformations["reports4youname"]);
    $smarty_obj->assign("REPORTDESC",$ITS4YouReports->reportinformations["description"]);
    $smarty_obj->assign("REP_MODULE",$ITS4YouReports->primarymodule);
    $smarty_obj->assign('REPORT_ID',$reportid);
    $smarty_obj->assign("isDuplicate",$_REQUEST["isDuplicate"]);
    $duplicate_reportname = "";
    if($_REQUEST["isDuplicate"]){
        $duplicate_reportname = $ITS4YouReports->reportinformations["reports4youname"];
    }
    $smarty_obj->assign("DUPLICATE_REPORTNAME",$duplicate_reportname);
}else{
    $ITS4YouReports->mode = 'create';
}

$smarty_obj->assign('MAX_LIMIT',$ITS4YouReports->reportinformations["columns_limit"]);
// from NewReport1
$date_format='<script> var userDateFormat = \''.$current_user->date_format.'\' </script>';
$smarty_obj->assign('DATE_FORMAT',$date_format);

// ITS4YOU-CR SlOl  26. 4. 2013 11:14:22
//Constructing the Role Array
$roleDetails=getAllRoleDetails();
$i=0;
$roleIdStr="";
$roleNameStr="";
$userIdStr="";
$userNameStr="";
$grpIdStr="";
$grpNameStr="";

foreach($roleDetails as $roleId=>$roleInfo) {
	if($i !=0) {
		if($i !=1) {
			$roleIdStr .= ", ";
			$roleNameStr .= ", ";
		}
		$roleName=$roleInfo[0];
		$roleIdStr .= "'".$roleId."'";
		$roleNameStr .= "'".addslashes(decode_html($roleName))."'";
	}
	$i++;
}

//Constructing the User Array
$l=0;
$userDetails=getAllUserName();
foreach($userDetails as $userId=>$userInfo) {
	if($l !=0){
		$userIdStr .= ", ";
		$userNameStr .= ", ";
	}
	$userIdStr .= "'".$userId."'";
	$userNameStr .= "'".$userInfo."'";
	$l++;
}
//Constructing the Group Array
$parentGroupArray = array();

$m=0;
$grpDetails=getAllGroupName();
foreach($grpDetails as $grpId=>$grpName) {
	if(! in_array($grpId,$parentGroupArray)) {
		if($m !=0) {
			$grpIdStr .= ", ";
			$grpNameStr .= ", ";
		}
		$grpIdStr .= "'".$grpId."'";
		$grpNameStr .= "'".addslashes(decode_html($grpName))."'";
        $m++;
	}
}
$smarty_obj->assign("ROLEIDSTR",$roleIdStr);
$smarty_obj->assign("ROLENAMESTR",$roleNameStr);
$smarty_obj->assign("USERIDSTR",$userIdStr);
$smarty_obj->assign("USERNAMESTR",$userNameStr);
$smarty_obj->assign("GROUPIDSTR",$grpIdStr);
$smarty_obj->assign("GROUPNAMESTR",$grpNameStr);
// ITS4YOU-END 

$reportdescription = htmlspecialchars($reportdescription, ENT_COMPAT, $default_charset);
$smarty_obj->assign('REPORT_DESC',$reportdescription);
$smarty_obj->assign('FOLDERID',$folderid);

$smarty_obj->assign("CREATE_MODE", $ITS4YouReports->mode);
$smarty_obj->assign("PRIMARYMODULES",$ITS4YouReports->getPrimaryModules());

$smarty_obj->assign("FOLDERID",vtlib_purify($_REQUEST['folder']));
$smarty_obj->assign("REP_FOLDERS",$ITS4YouReports->getReportFolders());

if(!isset($_REQUEST["mode"])){
	// ReportsStep2
	$smarty_obj =	$ITS4YouReports->getSelectedValuesToSmarty($smarty_obj,"all");
	// ReportGrouping
	
	// ReportColumns
	$RC_BLOCK2 = $ITS4YouReports->getSelectedColumnsList($ITS4YouReports->selected_columns_list_arr);
	$smarty_obj->assign("RC_BLOCK2",$RC_BLOCK2);
}

$tool_buttons = Button_Check($currentModule);
$tool_buttons["Import"]="no";
$tool_buttons["Export"]="no";
$smarty_obj->assign('CHECK', $tool_buttons);
if($ITS4YouReports->CheckPermissions("EDIT")) {
  $smarty_obj->assign("EXPORT","yes");
}
if($ITS4YouReports->CheckPermissions("EDIT") && $ITS4YouReports->GetVersionType() != "deactivate" ) {
  $smarty_obj->assign("EDIT","permitted");
  $smarty_obj->assign("IMPORT","yes");
}
if($ITS4YouReports->CheckPermissions("DELETE") && $ITS4YouReports->GetVersionType() != "deactivate" ) {
  $smarty_obj->assign("DELETE","permitted");
}
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
$smarty_obj->assign("IMAGE_PATH", $image_path);
$smarty_obj->assign("THEME_PATH", $theme_path);

$smarty_obj->assign("ERROR_MSG", getTranslatedString('LBL_NO_PERMISSION', $currentModule));

$smarty_obj->display(vtlib_getModuleTemplate($currentModule,"EditReports4You.tpl"));
?>