<?php 

$x2f = "abs";
$x30 = "date";
$x31 = "error_reporting";
$x32 = "explode";
$x33 = "function_exists";
$x34 = "md5";
$x35 = "str_replace";
$x36 = "strlen";
$x37 = "time";
$x31(0);
require_once ('modules/ITS4YouReports/ITS4YouReports.php');
$x0b = "theme_path";
$x0c = "mod_strings";
$x0d = "app_strings";
$x0e = "adb";
$x0f = "vtiger_current_version";
$x10 = "site_URL";
$x11 = "list_permissions";
$x12 = "p_errors";
$x13 = "its4you_validated_ok";
$x14 = "currentModule";
require_once ("Smarty_setup.php");
require_once ("include/nusoap/nusoap.php");
global $$x0b, $$x0c, $$x0d;
global $x15;
$x15 = new soapclient2("http://www.crm4you.sk/ITS4YouReports/ITS4YouWS.php", false);
$x15->soap_defencoding = 'UTF-8';
$x16 = $x15->getError();
$x17 = $$x0c;
$x18 = $$x0d;
$$x0e = PEARDatabase::getInstance();
$x19 = new vtigerCRM_Smarty();
$x1a = "themes/" . $$x0b . "/";
$x1b = $x1a . "images/";
$x19->assign("THEME", $x1a);
$x19->assign("IMAGE_PATH", $x1b);
$x19->assign("MOD", $x17);
$x19->assign("APP", $x18);
$x1c = ITS4YouReports::GetReports4YouForImport();
if (empty($x1c)) {
    $x1d = 2;
} else {
    $x1d = 3;
}
if ($_REQUEST["installtype"] == "validate" && isset($_REQUEST["key"])) {
    $x1e = "invalidated";
    include ("version.php");
    $x1f = "version";
    $x20 = $x35(" ", "_", $$x1f);
    $x21 = $x34("web/" . $$x10);
    $x22 = "professional";
    $x1e = BugoXMTvsRckbsvhTSXL($x22, $$x0f, $x20, $x21);
    if ($x1e == "validated") {
        $$x0e->query("DELETE FROM its4you_reports4you_license");
        $$x0e->query("INSERT INTO its4you_reports4you_license VALUES('" . $x22 . "','" . $_REQUEST["key"] . "')");
        if ($x22 == "professional") $x22 = "";
        else $x22.= "/";
        $$x0e->query("DELETE FROM its4you_reports4you_version");
        $$x0e->query("INSERT INTO its4you_reports4you_version VALUES('" . $$x0f . "','" . $x34($x22 . $$x10) . "')");
        if ($x1d == 2) {
            $x23 = $x18["LBL_FINISH"];
            $x24 = 4;
        } else {
            $x24 = 2;
            $x23 = $x18["LBL_IMPORT"];
        }
        $x19->assign("STEP", $x24);
        $x19->assign("CURRENT_STEP", 2);
        $x19->assign("TOTAL_STEPS", $x1d);
        $x19->assign("STEPNAME", $x23);
        $x25 = $x33("mb_get_info");
        if ($x25 === false) {
            $x19->assign("MB_STRING_EXISTS", 'false');
        } else {
            $x19->assign("MB_STRING_EXISTS", 'true');
        }
    } elseif ($x1e == "validate_err") {
        $x26 = array();
        $x26[] = $x17["LBL_INVALID_FOPEN_CURL"];
        $x19->assign("STEP", "error");
        $x19->assign("INVALID", "true");
        $x19->assign("ERROR_TBL", $x26);
    } else {
        $x26 = array();
        $x26[] = "<span style=" . '"' . "color:red;font-size:13px;font-weight:bold;" . '"' . ">" . $x17["LBL_INVALID_KEY"] . "</span>";
        $x19->assign("STEP", "error");
        $x19->assign("INVALID", "true");
        $x19->assign("ERROR_TBL", $x26);
    }
} elseif ($_REQUEST["installtype"] == "import_reports") {
    $x23 = $x18["LBL_IMPORT"];
    $x24 = 3;
    $x19->assign("STEP", $x24);
    $x19->assign("CURRENT_STEP", 3);
    $x19->assign("TOTAL_STEPS", $x1d);
    $x19->assign("STEPNAME", $x23);
} elseif ($_REQUEST["installtype"] == "import_finish") {
    $x24 = 4;
    $x23 = $x18["LBL_FINISH"];
    $x19->assign("CURRENT_STEP", $x24);
    $x19->assign("STEP", $x24);
    $x19->assign("TOTAL_STEPS", $x1d);
    $x19->assign("STEPNAME", $x23);
} elseif ($_REQUEST["installtype"] == "redirect_recalculate") {
    echo "<meta http-equiv='refresh'  content='0;url=index.php?module=ITS4YouReports&action=index&parenttab=Analytics' />";
    exit;
} else {
    $x19->assign("CURRENT_STEP", "1");
    $x19->assign("TOTAL_STEPS", $x1d);
    $x19->assign("STEP", "1");
    $x23 = $x17["LBL_ACTIVATE_KEY"];
    $x19->assign("STEPNAME", $x23);
}
$x19->display(vtlib_getModuleTemplate($$x14, 'install.tpl'));
exit;
function BugoXMTvsRckbsvhTSXL($x22, $x27, $x20, $x21) {
    global $x2f, $x30, $x31, $x32, $x33, $x34, $x35, $x36, $x37;
    global $x15;
    $x28 = $x37();
    $x29 = array("key" => $_REQUEST["key"], "type" => $x22, "vtiger" => $x27, "reports4you" => $x20, "url" => $x21, "time" => $x28);
    $x1e = $x15->call("activate_license", $x29);
    if ($x1e != "invalidated" && $x1e != "validate_err") {
        $x2a = $x32("_", $x1e);
        $x1e = "invalidated";
        $x2b = $x30("Yy", $x28);
        $x2c = $x36($x22);
        $x2d = $x36($x21);
        $x2e = $x2b;
        $x2e-= ($x2c + $x2d);
        $x2e-= $x28;
        if ($x2a[1] == $x2f($x2e)) {
            $x1e = $x2a[0];
        }
    }
    return $x1e;
} 
?>