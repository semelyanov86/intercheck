<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'config.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
global $adb;
//$result = $adb->pquery('SELECT @@SESSION.sql_mode AS sql_mode');
//$sql_mode = $adb->query_result($result, 0, 'sql_mode');
//$sql_mode = trim(preg_replace('/,+/', ',', str_replace(array('ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES'), '', $sql_mode)), ',');
//$adb->pquery('SET SESSION sql_mode = ?', array(''));

$webUI = new Vtiger_WebUI();
$webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
//$adb->disconnect();