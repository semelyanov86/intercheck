<?php 

require_once ('modules/ITS4YouReports/ITS4YouReports.php');
$x0b = "adb";
$x0c = "vtiger_current_version";
$x0d = "theme";
$x0e = "mod_strings";
$x0f = "app_strings";
$x10 = "list_permissions";
$x11 = "p_errors";
$x12 = @$$x0b->query("SELECT license FROM its4you_reports4you_license WHERE license!='deactivate'");
if (true) {
    require_once ("ListReports4You.php");
} else {
    require_once ('Smarty_setup.php');
    $x13 = new vtigerCRM_Smarty();
    $x14 = "themes/" . $$x0d . "/";
    $x15 = $x14 . "images/";
    $x16 = $$x0e;
    $x13->assign("THEME", $x14);
    $x13->assign("IMAGE_PATH", $x15);
    $x13->assign("MOD", $$x0e);
    $x13->assign("APP", $$x0f);
    if ($x12 && $$x0b->num_rows($x12) > 0) {
        $x13->assign("STEP", "3");
        $x13->assign("CURRENT_STEP", "3");
        $x13->assign("TOTAL_STEPS", "3");
        $x13->assign("STEPNAME", $x16["LBL_DOWNLOAD"]);
    } else {
        $x17 = ITS4YouReports::GetReports4YouForImport();
        if (empty($x17)) {
            $x18 = 2;
        } else {
            $x18 = 3;
        }
        $x13->assign("STEP", "1");
        $x13->assign("CURRENT_STEP", "1");
        $x13->assign("TOTAL_STEPS", $x18);
        $x13->assign("STEPNAME", $x16["LBL_VALIDATION"]);
    }
    $x13->display("modules/ITS4YouReports/install.tpl");
} 

?>