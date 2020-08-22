<?php

require_once('../../config.inc.php');

//require_once('include/database/PearDatabase.php');
//require_once('include/utils/utils.php');
//require_once('include/utils/UserInfoUtil.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');
global $currentModule;

$imported_reports = $to_merge = array();

foreach (glob("test/ITS4YouReports/*.php") as $report_file){
echo "<pre>";print_r($report_file);echo "</pre>";
	$name_arr_temp = explode("/",$report_file);
	$name_arr = explode("_",$name_arr_temp[((count($name_arr_temp)-1))]);
	if(is_numeric($name_arr[0])){
		$reports_to_import[$name_arr[0]] = $report_file;
	}else{
		$to_merge[] = $report_file;
	}
}
ksort($reports_to_import);
$reports_to_import = array_merge($reports_to_import, $to_merge);

echo "<pre>";print_r($reports_to_import);echo "</pre>";

echo "ides Import ";
exit;
$ITS4YouReports = new ITS4YouReports(false);
$reports_to_import = $ITS4YouReports->GetReports4YouForImport();
if(!empty($reports_to_import)){
    foreach($reports_to_import as $file_to_import){
    	$return = $ITS4YouReports->ImportReports4You($file_to_import);
    	// WITH MYSQL DEBUG ImportReports4You($file_to_import,$debug)
    	//$return = $ITS4YouReports->ImportReports4You($file_to_import,true);
    	//echo "<tr><td align='left' valign='top' style='padding-left:40px;'>$file_to_import</td></tr>";
    	echo "<tr><td align='left' valign='top' style='padding-left:40px;'>$return</td></tr>";
	}
}else{
	echo "<tr><td align='left' valign='top' style='padding-left:40px;'>".getTranslatedString("LBL_ANY_TO_IMPORT",$currentModule)."</td></tr>";
}
if(isset($_REQUEST["module"]) && $_REQUEST["module"]=="ITS4YouReports" && isset($_REQUEST["action"]) && $_REQUEST["module"]=="ImportReports4You"){
	echo '<meta http-equiv="refresh" content="3;index.php?module=ITS4YouReports&action=index&parenttab=Analytics">';
	exit;
}