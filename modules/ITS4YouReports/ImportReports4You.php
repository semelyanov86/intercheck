<?php

echo "ides Import ";
exit;
require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once('include/utils/utils.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');
global $currentModule;
$ITS4YouReports = new ITS4YouReports(false);
$reports_to_import = $ITS4YouReports->GetReports4YouForImport();
$layout = Vtiger_Viewer::getDefaultLayoutName();
if(!empty($reports_to_import)){
    foreach($reports_to_import as $file_to_import){
    	$return = $ITS4YouReports->ImportReports4You($file_to_import);
    	// WITH MYSQL DEBUG ImportReports4You($file_to_import,$debug)
    	//$return = $ITS4YouReports->ImportReports4You($file_to_import,true);
    	//echo "<tr><td align='left' valign='top' style='padding-left:40px;'>$file_to_import</td></tr>";
    	echo "<tr><td align='left' valign='top' style='padding-left:40px;'>$return</td></tr>";
	}
}else{
	if($layout !== "v7"){
	   	echo "<tr><td align='left' valign='top' style='padding-left:40px;'>".getTranslatedString("LBL_ANY_TO_IMPORT",$currentModule)."</td></tr>";
	}
}
if(isset($_REQUEST["module"]) && $_REQUEST["module"]=="ITS4YouReports" && isset($_REQUEST["action"]) && $_REQUEST["module"]=="ImportReports4You"){
	echo '<meta http-equiv="refresh" content="3;index.php?module=ITS4YouReports&action=index&parenttab=Analytics">';
	exit;
}
?>