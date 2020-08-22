<?php 

$x22 = "abs";
$x23 = "date";
$x24 = "error_reporting";
$x25 = "explode";
$x26 = "md5";
$x27 = "str_replace";
$x28 = "strlen";
$x29 = "time";
$x24(0);
require_once ("include/nusoap/nusoap.php");
$x0b = "adb";
$x0c = "vtiger_current_version";
$x0d = "site_URL";
$x0e = "mod_strings";
$x0f = $$x0e;
$x10 = "its4you_validated_ok";
global $x11;
$x11 = new soapclient2("http://www.crm4you.sk/ITS4YouReports/ITS4YouWS.php", false);
$x11->soap_defencoding = 'UTF-8';
$x12 = $x11->getError();
if ($x12 == false) {
    $x13 = "";
    if ($_REQUEST["key"] != "") {
        include ("version.php");
        $x14 = "version";
        $x15 = $x27(" ", "_", $$x14);
        $x16 = $x26("web/" . $$x0d);
        $x17 = "professional";
        $x13 = lASzFfDzipehfoEbjFZQ($x17, $$x0c, $x15, $x16);
    }
    if ($x13 == "validated") {
        $$x0b->query("DELETE FROM its4you_reports4you_license");
        $$x0b->query("INSERT INTO its4you_reports4you_license VALUES('" . $x17 . "','" . $_REQUEST["key"] . "')");
        if ($x17 == "professional") $x17 = "";
        else $x17.= "/";
        $$x0b->query("UPDATE its4you_reports4you_version SET license='" . $x26($x17 . $$x0d) . "' WHERE version='" . $$x0c . "'");
        $x18 = "ok";
    } else {
        $x18 = "";
    }
} else {
    $x18 = "error";
}
$x19 = "index.php?module=ITS4YouReports&action=License&parenttab=Settings&reactivate=$x18";
echo "<script>window.location.replace('$x19');</script>";
exit;
function lASzFfDzipehfoEbjFZQ($x17, $x1a, $x15, $x16) {
    global $x22, $x23, $x24, $x25, $x26, $x27, $x28, $x29;
    global $x11;
    $x1b = $x29();
    $x1c = array("key" => $_REQUEST["key"], "type" => $x17, "vtiger" => $x1a, "pdfmaker" => $x15, "url" => $x16, "time" => $x1b);
    $x13 = $x11->call("reactivate_license", $x1c);
    if ($x13 != "invalidated" && $x13 != "validate_err") {
        $x1d = $x25("_", $x13);
        $x13 = "invalidated";
        $x1e = $x23("Yy", $x1b);
        $x1f = $x28($x17);
        $x20 = $x28($x16);
        $x21 = $x1e;
        $x21-= ($x1f + $x20);
        $x21-= $x1b;
        if ($x1d[1] == $x22($x21)) $x13 = $x1d[0];
    }
    return $x13;
} 

?>