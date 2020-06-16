<?php

require_once('Smarty_setup.php');
require_once("include/utils/utils.php");
require_once("modules/com_vtiger_workflow/VTWorkflowUtils.php");

global $mod_strings, $app_strings, $theme, $adb;
$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", "$theme");
$smarty->assign("IMAGE_PATH", "themes/$theme/images/");

// Operation to be restricted for non-admin users.
global $current_user;
if(!is_admin($current_user)) {	
	$smarty->display(vtlib_getModuleTemplate('Vtiger','OperationNotPermitted.tpl'));	
} else {
	$module = vtlib_purify($_REQUEST['formodule']);
	$menu_array = array();
        
        $menu_array[$module."Upgrade"]["location"] = "index.php?module=Settings&action=ModuleManager&module_update=Step1&src_module=$module&parenttab=Settings";
        $menu_array[$module."Upgrade"]["image_src"] = "themes/images/vtlib_modmng.gif";
        $menu_array[$module."Upgrade"]["desc"] = getTranslatedString("LBL_UPGRADE", "Settings") . " ". getTranslatedString($module,$module);
        $menu_array[$module."Upgrade"]["label"] = getTranslatedString("LBL_UPGRADE", "Settings");

        $menu_array[$module."License"]["location"] = "index.php?module=$module&action=License&parenttab=Settings";
        $menu_array[$module."License"]["image_src"] = "themes/images/proxy.gif";
        $menu_array[$module."License"]["desc"] = getTranslatedString("LBL_LICENSE_DESC", "$module");
        $menu_array[$module."License"]["label"] = getTranslatedString("LBL_LICENSE", "$module");

	//add blanks for 3-column layout
	$count = count($menu_array)%3;
	if($count>0) {
		for($i=0;$i<3-$count;$i++) {
			$menu_array[] = array();
		}
	}
	
	$smarty->assign('MODULE',$module);
	$smarty->assign('MODULE_LBL',getTranslatedString($module));
	$smarty->assign('MENU_ARRAY', $menu_array);

	$smarty->display(vtlib_getModuleTemplate('Vtiger','Settings.tpl'));
}

?>