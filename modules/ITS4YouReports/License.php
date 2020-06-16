<?php

require_once ('Smarty_setup.php');
require_once ("include/utils/utils.php");
require_once ('modules/ITS4YouReports/ITS4YouReports.php');
$x0b = "mod_strings";
$x0c = "app_strings";
$x0d = $$x0b;
$x0e = "currentModule";
global $$x0b;
global $$x0c;
global $$x0e;
$x0f = new vtigerCRM_Smarty;
$x0f->assign("MOD", $$x0b);
$x0f->assign("APP", $$x0c);
$x0f->assign("MODULE", $$x0e);
$x10 = ITS4YouReports::getStoredITS4YouReport();
$x0f->assign("LICENSE", $x10->GetLicenseKey());
$x0f->assign("VERSION_TYPE", $x10->GetVersionType());
$x0f->display(vtlib_getModuleTemplate($$x0e, 'License.tpl'));
$x11 = "";
if (isset($_REQUEST["deactivate"]) && $_REQUEST["deactivate"] != "") {
    switch ($_REQUEST["deactivate"]) {
        case "invalid_key":
            $x11 = $$x0b["LBL_INVALID_KEY"];
        break;
        case "failed":
            $x11 = $$x0b["LBL_DEACTIVATE_ERROR"];
        break;
        case "ok":
            $x11 = $$x0b["LBL_DEACTIVATE_SUCCESS"];
        break;
    }
} elseif (isset($_REQUEST["reactivate"]) && $_REQUEST["reactivate"] != "") {
    switch ($_REQUEST["reactivate"]) {
        case "invalid":
            $x11 = $$x0b["LBL_INVALID_KEY"];
        break;
        case "error":
            $x11 = $$x0b["REACTIVATE_ERROR"];
        break;
        case "ok":
            $x11 = $$x0b["REACTIVATE_SUCCESS"];
        break;
    }
}
if ($x11 != "") {
    echo "<script>alert('" . $x11 . "');</script>";
}
exit; 

?>