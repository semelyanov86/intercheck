<?php

global $adb;
$sql = "ALTER TABLE `vtiger_quotingtool_settings` ADD `success_content` text NULL";
$params = array();
$rs = $adb->pquery($sql, $params);

?>