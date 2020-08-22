<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');
global $app_strings;
global $app_list_strings;
global $mod_strings;

$current_module_strings = return_module_language($current_language, 'ITS4YouReports');

global $list_max_entries_per_page;
global $urlPrefix;

$log = LoggerManager::getLogger('report_type');

global $currentModule;
global $image_path;
global $theme;
global $adb;

$theme_path="themes/".$theme."/";
$report_column=new vtigerCRM_Smarty;
$report_column->assign("MOD", $mod_strings);
$report_column->assign("APP", $app_strings);
$report_column->assign("IMAGE_PATH",$image_path);
$report_column->assign("THEME_PATH",$theme_path);

// ITS4YOU-CR SlOl 10. 9. 2013 16:13:47
$LBL_INFORMATIONS_4YOU = getTranslatedString("LBL_STEP10_INFO",$currentModule);
$report_column->assign("LBL_INFORMATIONS_4YOU", $LBL_INFORMATIONS_4YOU);
// ITS4YOU-END 10. 9. 2013 16:13:50 

$recordid = "";
if(isset($_REQUEST["record"]) && $_REQUEST['record']!=''){
	$recordid = vtlib_purify($_REQUEST["record"]);
}
$oReport = new ITS4YouReports();

$R_Objects = array();
if(isset($_REQUEST["selectedColumnsStr"])){
    $R_Objects = explode("<_@!@_>", $_REQUEST["selectedColumnsStr"]);
    $r_p_module = vtlib_getModuleNameById(vtlib_purify($_REQUEST["primarymodule"]));
    $quick_columns_arraySelected = array();
    $qf_to_go = explode('$_@_$', vtlib_purify($_REQUEST["qf_to_go"]));
    foreach ($qf_to_go as $key=>$qf_to_go_str) {
            $quick_columns_arraySelected[] = trim($qf_to_go_str,"qf:");
    }
}else{
    $sarray = $oReport->getSelectedColumnListArray($recordid);
    foreach ($sarray as $key => $scarray) {
        $R_Objects[] = $scarray["fieldcolname"];
    }
    $r_p_module = $oReport->primarymodule;
}

foreach ($R_Objects as $column_str) {
    $column_arr = explode(":", $column_str);
    $last_key = count($column_arr)-1;
    $column_array_lk = $column_arr[$last_key];
    if(strtolower($column_array_lk)!="mif"){
        if(is_numeric($column_array_lk) || in_array($column_array_lk,array("MIF","INV"))){
                $tablename = trim($column_arr[0], $column_array_lk);
        }else{
                $tablename = $column_arr[0];
        }
        $sql = "SELECT fieldname,uitype FROM vtiger_field WHERE tablename=? AND columnname=?";
        $result = $adb->pquery($sql,array($tablename,$column_arr[1]));
        while($row = $adb->fetchByAssoc($result)){
            if(isset($row["uitype"]) && in_array($row["uitype"],array("12","15","16","52","53","54","59","62","66","67","68","111","115"))){
                        $quick_columns_array[] = $column_str;
                }	
        }
    }
}
$p_options = $oReport->getSMOwnerIDColumn($r_p_module);
foreach ($p_options as $p_key => $p_value) {
    $quick_columns_array[] = $p_key;
}

$BLOCK1 = $oReport->getQuickFiltersHTML($quick_columns_array,$quick_columns_arraySelected);
$report_column->assign("BLOCK1",$BLOCK1);
$report_column->assign("E_BLOCK",empty($BLOCK1));
/*
$sortorder = "";
if(isset($_REQUEST["record"]) && $_REQUEST['record']!='')
{
	$recordid = vtlib_purify($_REQUEST["record"]);
	$oReport = new ITS4YouReports($recordid);
	$BLOCK1 = getPrimaryColumnsHTML($oReport->primarymodule);

	$BLOCK3 = getSecondaryColumnsHTML($oReport->relatedmodulesstring);	
	
	$BLOCK2 = $oReport->getSelectedQFColumnsList($recordid);
	$report_column->assign("BLOCK1",$BLOCK1);
	$report_column->assign("BLOCK2",$BLOCK2);
	$report_column->assign("BLOCK3",$BLOCK3);
}
else
{
	$primarymodule = vtlib_purify($_REQUEST["primarymodule"]);
	$BLOCK1 = getPrimaryColumnsHTML($primarymodule);
	$ITS4YouReports = new ITS4YouReports();
	if(!empty($ITS4YouReports->related_modules[$primarymodule])) {
		foreach($ITS4YouReports->related_modules[$primarymodule] as $key=>$value){
			$BLOCK1 .= getSecondaryColumnsHTML($_REQUEST["secondarymodule_".$value]);
		}
	}
	// ITS4YOU MaJu
    if($primarymodule=='Invoice'){
		$BLOCK1 .= getSecondaryColumnsHTML('Products:Services');
    }
	// ITS4YOU-END
	$report_column->assign("BLOCK1",$BLOCK1);
}
*/
$report_column->display(vtlib_getModuleTemplate($currentModule,"ReportQuickFilter.tpl"));
?>