<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
$current_module_strings = return_module_language($current_language, 'ITS4YouReports');
global $list_max_entries_per_page;
global $urlPrefix;
$log = LoggerManager::getLogger('report_list');
global $currentModule;
global $image_path;
global $theme;
global $focus_list;
$recordid = vtlib_purify($_REQUEST['record']);

$BLOCK0 = $BLOCK1 = $BLOCK2 = "";

$SumOptions = array();
$secondarymodule = '';
$secondarymodules =Array();

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

if(isset($_REQUEST['primarymodule']) && $_REQUEST['primarymodule']!=""){
	$primarymodule = vtlib_purify($_REQUEST['primarymodule']);
}else{
	$primarymodule = $ITS4YouReports->primarymodule;
}

$reportid = $ITS4YouReports->record;
$modulename_prefix="";

if(isset($_REQUEST["selectedmodule"]) && $_REQUEST["selectedmodule"]!=""){
	$modulename = vtlib_purify($_REQUEST["selectedmodule"]);
	$ITS4YouReports->getSecModuleColumnsList($modulename);
	$SumOptions = getSecondaryColumns($SumOptions,$modulename,$ITS4YouReports);
	$SPSumOptions[$modulename] = $SumOptions;
	$module_array["id"] = $modulename;
	$modulename_arr = explode("x", $modulename);
	$modulename_id = $modulename_arr[0];
	if($modulename_arr[1]!=""){
		$modulename_prefix = $modulename_arr[1];
	}
}else{
	$module_array["module"]=$ITS4YouReports->primarymodule;
	$module_array["id"]=$ITS4YouReports->primarymoduleid;
	
	$modulename = $module_array["module"];
	$modulename_lbl = getTranslatedString($modulename,$modulename);
	$availModules[$module_array["id"]] = $modulename_lbl;
	
	$modulename_id=$module_array["id"];
    $SumOptions = getPrimaryColumns($SumOptions,$modulename,true,$ITS4YouReports);
    $SPSumOptions[$module_array["id"]][$module_array["id"]] = $SumOptions;
}
$reporttype = vtlib_purify($_REQUEST['selectedreporttype']);

$step5_result="";
if(!isset($_REQUEST["selectedmodule"])){
	$secondarymodule_arr = $ITS4YouReports->getReportRelatedModules($module_array["id"]);
	$ITS4YouReports->getSecModuleColumnsList($secondarymodule);
	$available_modules[]=array("id"=>$ITS4YouReports->primarymoduleid,"name"=>$modulename_lbl,"checked"=>"checked");
	foreach ($secondarymodule_arr as $key=>$value) {
		$available_modules[] = array("id"=>$value["id"],"name"=>$value["name"],"checked"=>"");
	}
	$AV_M = Zend_JSON::encode($available_modules);
	$step5_result .= $AV_M."(!A#V_M@M_M#A!)";
}
/*$SumOptions2 = getSecondaryColumns($SumOptions2,$secondarymodule,$ITS4YouReports);
foreach ($SumOptions2 as $key=>$value) {
	$SPSumOptions[$key]=$value;
}*/

$BLOCK1 = "";
foreach ($SPSumOptions AS $module_key => $SumOptions)
{
	$BLOCK2 = "";
	$r_modulename = vtlib_getModuleNameById($module_key);
	$r_modulename_lbl = getTranslatedString($r_modulename,$r_modulename); 
	
	foreach ($SumOptions as $SumOptions_key=>$SumOptions_value) {
		foreach ($SumOptions_value AS $optgroup => $optionsdata)
		{
			if ($BLOCK2 != "")
				$BLOCK2 .= "(|@!@|)";
			$BLOCK2 .= $optgroup;
			$BLOCK2 .= "(|@|)";

			$BLOCK2 .= Zend_JSON::encode($optionsdata);
		}

		$BLOCK1 .= $module_key."(!#_ID@ID_#!)".$r_modulename_lbl."(!#_ID@ID_#!)".$BLOCK2;
	}
}
$step5_result .= $BLOCK1;

echo $step5_result;
?>