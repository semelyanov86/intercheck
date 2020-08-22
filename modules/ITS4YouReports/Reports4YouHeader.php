<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once("include/utils/utils.php");
require_once("include/calculator/Calc.php");

global $currentModule,$default_charset;
global $app_strings;
global $app_list_strings;
global $moduleList;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$userName = getFullNameFromArray('Users', $current_user->column_fields);
$smarty_obj = new vtigerCRM_Smarty;
$header_array = getHeaderArray();
$smarty_obj->assign("HEADERS",$header_array);
$smarty_obj->assign("THEME",$theme);
$smarty_obj->assign("IMAGEPATH",$image_path);
$smarty_obj->assign("USER",$userName);

$qc_modules = getQuickCreateModules();
$smarty_obj->assign("QCMODULE", $qc_modules);
$smarty_obj->assign("APP", $app_strings);

$cnt = count($qc_modules);
$smarty_obj->assign("CNT", $cnt);

$smarty_obj->assign("PRINT_URL", "phprint.php?jt=".session_id().$GLOBALS['request_string']);
$smarty_obj->assign("MODULE_NAME", $currentModule);
$date = new DateTimeField(null);
$smarty_obj->assign("DATE", $date->getDisplayDateTimeValue());
$smarty_obj->assign("CURRENT_USER_MAIL", $current_user->email1);
$smarty_obj->assign("CURRENT_USER", $current_user->user_name);
$smarty_obj->assign("CURRENT_USER_ID", $current_user->id);
$smarty_obj->assign("MODULELISTS",$app_list_strings['moduleList']);
$smarty_obj->assign("CATEGORY",getParentTab());
$smarty_obj->assign("CALC",get_calc($image_path));
$smarty_obj->assign("MENUSTRUCTURE",getMenuStructure($currentModule));
$smarty_obj->assign("ANNOUNCEMENT",get_announcements());
$smarty_obj->assign("USE_ASTERISK", get_use_asterisk($current_user->id));

if (is_admin($current_user)) $smarty_obj->assign("ADMIN_LINK", "<a href='index.php?module=Settings&action=index'>".$app_strings['LBL_SETTINGS']."</a>");

$module_path="modules/".$currentModule."/";

require_once('include/Menu.php');

//Assign the entered global search string to a variable and display it again
if($_REQUEST['query_string'] != '')
	$smarty_obj->assign("QUERY_STRING",htmlspecialchars($_REQUEST['query_string'],ENT_QUOTES,$default_charset));//BUGIX " Cross-Site-Scripting "
else
	$smarty_obj->assign("QUERY_STRING","$app_strings[LBL_SEARCH_STRING]");

global $module_menu;

require_once('data/Tracker.php');
$tracFocus=new Tracker();
$list = $tracFocus->get_recently_viewed($current_user->id);
$smarty_obj->assign("TRACINFO",$list);

// Gather the custom link information to display
include_once('vtlib/Vtiger/Link.php');
$hdrcustomlink_params = Array('MODULE'=>$currentModule);
$COMMONHDRLINKS = Vtiger_Link::getAllByType(Vtiger_Link::IGNORE_MODULE, Array('ONDEMANDLINK', 'HEADERLINK','HEADERSCRIPT', 'HEADERCSS'), $hdrcustomlink_params);
$smarty_obj->assign('HEADERLINKS', $COMMONHDRLINKS['HEADERLINK']);
$smarty_obj->assign('ONDEMANDLINKS', $COMMONHDRLINKS['ONDEMANDLINK']);
$smarty_obj->assign('HEADERSCRIPTS', $COMMONHDRLINKS['HEADERSCRIPT']);
$smarty_obj->assign('HEADERCSS', $COMMONHDRLINKS['HEADERCSS']);
// END

// Pass on the version information
global $vtiger_current_version;
$smarty_obj->assign('VERSION', $vtiger_current_version);
// END

$sql="select * from vtiger_organizationdetails";
$result = $adb->pquery($sql, array());
//Handle for allowed organation logo/logoname likes UTF-8 Character
$organization_logo = decode_html($adb->query_result($result,0,'logoname'));
$smarty_obj->assign("LOGO",$organization_logo);

$smarty_obj->display(vtlib_getModuleTemplate($currentModule, 'Reports4YouHeader.tpl'));
?>