<?php
chdir('../');
require_once 'includes/Loader.php';
require_once 'config.php';
include_once 'vtlib/Vtiger/Module.php';
require_once 'libraries/adodb/adodb.inc.php';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
global $adb;
$emm = new VTEntityMethodManager($adb);
$methods = $emm->methodsForModule('Activities');
if (!in_array('Calc Activities', $methods)) {
    $emm->addEntityMethod("Activities", "Calc Activities","modules/Activities/workflow/calcActivities.php", "calcActivities");
    echo 'We are done';
} else {
    echo 'Method already added';
}