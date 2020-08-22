<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');

global $app_strings;
global $mod_strings;

$recordid = vtlib_purify($_REQUEST['record']);

$sec_module = array();

$BLOCK1 = "";

$SumOptions = array();
$searchtype = vtlib_purify($_REQUEST["searchtype"]);
if($searchtype=="secondary"){
	$SumOptions2 = $SumOptionsR = array();
	$SumOptions2 = getSecondaryColumns($SumOptions2,$secondarymodule);
	
	$reporttype = vtlib_purify($_REQUEST['selectedreporttype']);
	if($reporttype=='timeline' || $reporttype=='grouping'){
		foreach ($ITS4YouReports->relatedmodulesarray as $secondarymodule) {
			$SumOptionsL = sgetColumntoTotalOptions($SumOptions2,$secondarymodule);
			if(!empty($SumOptionsL)){
				$SumOptionsR = array_merge($SumOptionsR,$SumOptionsL,array());
			}
		}
	}else{
		$SumOptionsR = $SumOptions2;
	}
	
	foreach ($SumOptionsR AS $optgroup2 => $optionsdata2)
	{
		if ($BLOCK2 != "")
			$BLOCK2 .= "(|@!@|)";
		$BLOCK2 .= $optgroup2;
		$BLOCK2 .= "(|@|)";
		$BLOCK2 .= Zend_JSON::encode($optionsdata2);
	}
	
	$BLOCK2 = ($BLOCK2!='')?$BLOCK2:"EMPTY_RELS(|@!@|)".$mod_strings['NO_REL_FIELDS'];
	$BLOCK_R .= "__BLOCKS__".$BLOCK2;
}else{
	$secondarymodule = '';
	$secondarymodules =Array();
	
	$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
	$primarymodule = $ITS4YouReports->primarymodule;
	$reportid = $ITS4YouReports->record;
	if(!empty($ITS4YouReports->related_modules[$primarymodule])) {
  		foreach($ITS4YouReports->related_modules[$primarymodule] as $key=>$value){
  			if(isset($_REQUEST["secondarymodule_".$value]))
				$secondarymodules[]= $_REQUEST["secondarymodule_".$value];
  		}
  	}

	$secondarymodule_arr = $ITS4YouReports->getReportRelatedModules($ITS4YouReports->primarymoduleid);
	foreach ($secondarymodule_arr as $key=>$relmodule) {
		$a_secondarymodule[] = $relmodule['id'];
	}
	$secondarymodule = implode(":", $a_secondarymodule);
	$ITS4YouReports->getSecModuleColumnsList($secondarymodule);

	$SumOptions = getPrimaryColumns($SumOptions,$primarymodule,true);
	
	$reporttype = vtlib_purify($_REQUEST['selectedreporttype']);
	if($reporttype=='timeline' || $reporttype=='grouping'){
		$SumOptions = sgetColumntoTotalOptions($SumOptions,$primarymodule,array());
	}
}		
$search_for = vtlib_purify(trim($_REQUEST["search_for"]));
if(isset($search_for) && $search_for!=""){
	foreach ($SumOptions as $block_name=>$fields) {
		$fi = 0;
		foreach ($fields as $key=>$field_array) {
			$pos = strpos(strtolower($field_array["text"]), strtolower($search_for));
			if($pos === false){
				// show("The string '$search_for' was not found in the string '".$field_array["text"]."'");
			}else{
				$new_SumOptions[$block_name][$fi] = $field_array;
				$fi++;
			}
		}
	}
	$SumOptions = $new_SumOptions;
}

foreach ($SumOptions AS $optgroup => $optionsdata)
{
	if ($BLOCK1 != "")
		$BLOCK1 .= "(|@!@|)";
	$BLOCK1 .= $optgroup;
	$BLOCK1 .= "(|@|)";
	$BLOCK1 .= Zend_JSON::encode($optionsdata);
}
$BLOCK1 = ($BLOCK1!='')?$BLOCK1:"EMPTY_RELS(|@!@|)".$mod_strings['NO_REL_FIELDS'];
echo $BLOCK1;
?>