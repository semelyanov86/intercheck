<?php

global $adb;
$sql = "CREATE TABLE IF NOT EXISTS vtiger_quotingtool_helptext(\r\nid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,\r\nelement VARCHAR(255),\r\nhelptext text)";
$params = array();
$rs = $adb->pquery($sql, $params);
$result = $adb->pquery("select * from `vtiger_quotingtool_helptext`", array());
$data = array("field-helptext", "custom-functions-helptext", "background-helptext", "custom-functions-helptext", "company-information-helptext", "custom-fields-helptext", "auth-integration-helptext", "expire-in-days-helptext", "attachments-helptext", "notes-helptext", "others-helptext", "related-helptext", "product-helptext", "other-information-helptext", "properties-helptext", "email-helptext", "related_module", "status-helptext", "pricing_table", "text_field", "accept-helptext", "tbl_one_column", "tbl_two_columns", "email-dataField", "email-accessibleLink", "email-signature", "inline_input_field");
foreach ($data as $val) {
    $result = $adb->pquery("select * from `vtiger_quotingtool_helptext` WHERE `element` = ?", array($val));
    if ($adb->num_rows($result) == 0) {
        $adb->pquery("INSERT INTO `vtiger_quotingtool_helptext`(`element`, `helptext`) VALUES ( ?, ?)", array($val, $val));
    }
}

?>