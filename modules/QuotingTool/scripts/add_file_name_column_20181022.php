<?php

global $adb;
global $current_user;
$sql1 = "ALTER TABLE `vtiger_quotingtool` ADD COLUMN `file_name`  text NULL ;";
$params = array();
$rs1 = $adb->pquery($sql1, $params);

?>