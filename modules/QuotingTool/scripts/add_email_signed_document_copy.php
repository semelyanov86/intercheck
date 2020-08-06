<?php

global $adb;
global $current_user;
global $HELPDESK_SUPPORT_EMAIL_ID;
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `email_signed` INT(11) NULL DEFAULT '0';";
$params = array();
$rs = $adb->pquery($sql, $params);
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `ignore_border_email` INT(11) NULL DEFAULT '1';";
$params = array();
$rs = $adb->pquery($sql, $params);
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `email_from_copy` VARCHAR (250) NULL DEFAULT ?;";
$params = array($HELPDESK_SUPPORT_EMAIL_ID);
$rs = $adb->pquery($sql, $params);
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `email_bcc_copy` VARCHAR (250) NULL DEFAULT '';";
$params = array();
$rs = $adb->pquery($sql, $params);
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `email_subject_copy` VARCHAR (250) NULL DEFAULT ?;";
$params = array("We've received your electronically signed document.");
$rs = $adb->pquery($sql, $params);
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `email_body_copy` text NULL DEFAULT '';";
$params = array();
$rs = $adb->pquery($sql, $params);

?>