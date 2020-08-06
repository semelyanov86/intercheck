<?php

global $adb;
global $current_user;
$sql = "ALTER TABLE `vtiger_quotingtool` ADD COLUMN `anblock`  tinyint(3) NULL DEFAULT '0';";
$params = array();
$rs = $adb->pquery($sql, $params);

?>