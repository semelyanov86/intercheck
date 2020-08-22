<?php


require_once('include/Zend/Json.php');
require_once('modules/ITS4YouReports/FilterUtils.php');

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

// $ITS4YouReports->initListOfModules();
if(isset($_REQUEST["record"]) && $_REQUEST['record']!='')
{
    $ITS4YouReports->getGroupFilterList($reportid); 
    $ITS4YouReports->getAdvancedFilterList($reportid);
    $ITS4YouReports->getSummariesFilterList($reportid);
}
// $smarty_obj->assign("GROUP_CRITERIA_GROUPS",$ITS4YouReports->groupft_criteria);
ITS4YouReports::sshow($ITS4YouReports->advft_criteria);
$smarty_obj->assign("CRITERIA_GROUPS",$ITS4YouReports->advft_criteria);
$smarty_obj->assign("EMPTY_CRITERIA_GROUPS",empty($ITS4YouReports->advft_criteria));
$smarty_obj->assign("SUMMARIES_CRITERIA",$ITS4YouReports->summaries_criteria);

if(isset($_REQUEST["mode"]) && $_REQUEST["mode"]!=""){
    $mode = vtlib_purify($_REQUEST["mode"]);
}else{
    $mode = "generate";
}
$smarty_obj->assign("MODE", $mode);

$FILTER_OPTION = getAdvCriteriaHTML();
$smarty_obj->assign("FOPTION",$FILTER_OPTION);
$secondarymodule = '';
$secondarymodules =Array();

if(!empty($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule])) {
	foreach($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule] as $key=>$value){
		if(isset($_REQUEST["secondarymodule_".$value]))$secondarymodules []= $_REQUEST["secondarymodule_".$value];
	}
}

$ITS4YouReports->getPriModuleColumnsList($ITS4YouReports->primarymodule);

if(!empty($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule])) {
	foreach($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule] as $key=>$value){
		$secondarymodules[]= $value["id"];
	}
	$secondary_modules_str = implode(":",$secondarymodules);
}
$ITS4YouReports->getSecModuleColumnsList($secondary_modules_str);

$COLUMNS_BLOCK = "<option value=''>".$mod_strings["LBL_NONE"]."</option>";    
$COLUMNS_BLOCK .= getPrimaryColumns_AdvFilterHTML($ITS4YouReports->primarymodule);
$COLUMNS_BLOCK .= getSecondaryColumns_AdvFilterHTML($ITS4YouReports->relatedmodulesstring);

$COLUMNS_BLOCK_JSON = $ITS4YouReports->getAdvanceFilterOptionsJSON($ITS4YouReports->primarymodule);

$smarty_obj->assign("COLUMNS_BLOCK", $COLUMNS_BLOCK);

if($mode!="ajax"){
    // echo "<textarea style='display:none;' id='filter_columns'>".$COLUMNS_BLOCK_JSON."</textarea>";
    $smarty_obj->assign("filter_columns",$COLUMNS_BLOCK_JSON);
    
    $sel_fields = Zend_Json::encode($ITS4YouReports->adv_sel_fields);
    $sel_fields = htmlentities($sel_fields);
    $smarty_obj->assign("SEL_FIELDS",$sel_fields);
    
    global $default_charset;
    $std_filter_columns = $ITS4YouReports->getStdFilterColumns();

    $std_filter_columns_js = implode("<%jsstdjs%>", $std_filter_columns);
    $std_filter_columns_js = html_entity_decode($std_filter_columns_js, ENT_QUOTES, $default_charset);
    // echo "<script type='text/javascript'>window.document.getElementById('std_filter_columns').value = '$std_filter_columns_js';</script>";
    $smarty_obj->assign("std_filter_columns", $std_filter_columns_js);

    $std_filter_criteria = Zend_Json::encode($ITS4YouReports->Date_Filter_Values);
    // echo "<script type='text/javascript'>document.getElementById('std_filter_criteria').value = '$std_filter_criteria';</script>";
    $smarty_obj->assign("std_filter_criteria", $std_filter_criteria);
}

$rel_fields = $ITS4YouReports->adv_rel_fields;
$smarty_obj->assign("REL_FIELDS",Zend_Json::encode($rel_fields));
?>