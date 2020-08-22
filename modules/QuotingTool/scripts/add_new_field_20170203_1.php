<?php

global $adb;
global $current_user;
$sql = "ALTER TABLE vtiger_quotingtool_transactions ADD `hash` VARCHAR(255) NULL DEFAULT '';";
$params = array();
$rs = $adb->pquery($sql, $params);

?>