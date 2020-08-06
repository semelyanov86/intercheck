<?php

global $adb;
$sql = "SELECT character_set_name, collation_name\n                FROM information_schema.`COLUMNS` C\n                WHERE table_schema = '" . $dbconfig["db_name"] . "'\n                  AND table_name = 'vtiger_tab'\n                  AND column_name = 'name';";
$rsCharset = $adb->pquery($sql, array());
$character_set_name = $adb->query_result($rsCharset, 0, "character_set_name");
$collation_name = $adb->query_result($rsCharset, 0, "collation_name");
$adb->pquery("ALTER TABLE `vtiger_quotingtool`\nMODIFY COLUMN `module`  varchar(250) CHARACTER SET '" . $character_set_name . "' COLLATE '" . $collation_name . "' NOT NULL DEFAULT '' ", array());

?>