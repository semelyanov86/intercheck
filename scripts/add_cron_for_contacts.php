<?php
chdir('../');
require_once 'includes/Loader.php';
require_once 'config.php';
include_once 'vtlib/Vtiger/Module.php';
require_once 'libraries/adodb/adodb.inc.php';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
$adb = PearDatabase::getInstance();
$sql = "SELECT id FROM `vtiger_cron_task` WHERE `module` = 'Contacts'";
$res = $adb->pquery($sql, array());
if (!$adb->num_rows($res)) {
    $adb->pquery("INSERT INTO `vtiger_cron_task` (`name`, `handler_file`, `frequency`, `status`, `module`, `sequence`) VALUES ('Send contacts to platform', 'modules/Contacts/cron/Contacts.service', '180', '0', 'Contacts', '50')", array());
}
echo 'Done!';