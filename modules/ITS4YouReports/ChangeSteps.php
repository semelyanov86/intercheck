<?php


// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');
require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');

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

$sec_module = array();

$step = vtlib_purify($_REQUEST['step']);

//error_reporting(63);
//ini_set('display_errors', 1);
//ITS4YouReports::sshow($step);
switch ($step) {
	case 'step1':
            include("modules/ITS4YouReports/ReportsStep1.php");
	break;
	case 'step2':
            include("modules/ITS4YouReports/ReportsStep2.php");
	break;
	/*case 'step3':
            include("modules/ITS4YouReports/ReportType.php");
        break;*/
	case 'step4':
            include("modules/ITS4YouReports/ReportGrouping.php");
	break;
	case 'step5':
            include("modules/ITS4YouReports/getStep5Columns.php");
	break;
	case 'step6':
            include("modules/ITS4YouReports/ReportColumnsTotal.php");
	break;
	case 'step7':
            include("modules/ITS4YouReports/ReportLabels.php");
	break;
	case 'step8':
		/*if($_REQUEST["record"]!=""){
			include("modules/ITS4YouReports/ReportFilters.php");
		}else{*/
			$BLOCK_R = '';
            $BLOCK1 = "";
            $BLOCK2 = '';

            $Options = array();
            $secondarymodule = '';
            $secondarymodules =Array();
		
			$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
		
		    $primarymodule_id = vtlib_purify($_REQUEST["primarymodule"]);
			$primarymodule = vtlib_getModuleNameById($primarymodule_id);
            $reportid = $ITS4YouReports->record;
            
            $BLOCK1 = $ITS4YouReports->getAdvanceFilterOptionsJSON($primarymodule);
            $BLOCK_R .= $BLOCK1;
	
            // ITS4YOU-CR SlOl 21. 3. 2014 10:20:17 summaries columns for frouping filters start
            $selectedSummariesString = vtlib_purify($_REQUEST["selectedSummariesString"]);
            $selectedSummariesArr = explode(";", $selectedSummariesString);
            $sm_arr = sgetSelectedSummariesOptions($selectedSummariesArr);
            $sm_str = "";
            foreach ($sm_arr as $key=>$opt_arr) {
                    if($sm_str!=""){
                            $sm_str .= "(|@!@|)";
                    }
                    $sm_str .= $opt_arr["value"]."(|@|)".$opt_arr["text"];
            }
            $BLOCK_S = $sm_str;
            $BLOCK_R .= "__BLOCKS__".$BLOCK_S;
            $BLOCK_R .= "__ADVFTCRI__".Zend_JSON::encode($ITS4YouReports->reportinformations["advft_criteria"]);
            $sel_fields = Zend_Json::encode($ITS4YouReports->adv_sel_fields);
            $BLOCK_R .= "__ADVFTCRI__".$sel_fields;
            global $default_charset;
            $std_filter_columns = $ITS4YouReports->getStdFilterColumns();
            $std_filter_columns_js = implode("<%jsstdjs%>", $std_filter_columns);
            $std_filter_columns_js = html_entity_decode($std_filter_columns_js, ENT_QUOTES, $default_charset);
            $BLOCK_R .= "__ADVFTCRI__".$std_filter_columns_js;
            
            $adv_rel_fields = Zend_Json::encode($ITS4YouReports->adv_rel_fields);
            $BLOCK_R .= "__ADVFTCRI__".$adv_rel_fields;
            echo $BLOCK_R;
		//}
	break;
	case 'step9':
		include("modules/ITS4YouReports/ReportSharing.php");
	break;
	case 'step10':
		include("modules/ITS4YouReports/ReportScheduler.php");
	break;
	/*case 'step11':
		include("modules/ITS4YouReports/ReportQuickFilter.php");
	break;*/
        case 'step11':
		include("modules/ITS4YouReports/ReportGraphs.php");
	break;
}

if ($step == "getStdFilter")
{
    
    $Options = array();
	
    $secondarymodule = '';
    $secondarymodules =Array();
    
	$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

    if(isset($_REQUEST["record"]) && $_REQUEST['record']!='')
    {
        $reportid = $_REQUEST['record'];
        $primarymodule = $ITS4YouReports->primarymoduleid;
        $primarymodulename = $ITS4YouReports->primarymodule;
        $ITS4YouReports->getPriModuleColumnsList($primarymodule);

    }
    else
    {
       $primarymodule = $ITS4YouReports->primarymoduleid;
       $primarymodulename = $ITS4YouReports->primarymodule;
       $ITS4YouReports->getPriModuleColumnsList($ITS4YouReports->primarymodule);
       
       $Options = getPrimaryColumns($Options,$ITS4YouReports->primarymodule);
    }
    if(!empty($ITS4YouReports->related_modules[$primarymodulename])) {
  		foreach($ITS4YouReports->related_modules[$primarymodulename] as $key=>$value){
  			if(in_array(getTabid($value),$ITS4YouReports->secondarymodules)){
				$secondarymodules[]= $value;
				$secondarymoduleids[]= getTabid($value);
			}
  		}
  	}

  	$ITS4YouReports->getSecModuleColumnsList($ITS4YouReports->relatedmodulesstring);
        
    $Options = getPrimaryStdFilter($ITS4YouReports->primarymodule,$ITS4YouReports);
	
	if(!empty($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule])) {
  		foreach($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule] as $key=>$value){
                    // $Options = getSecondaryStdFilter($value["id"],$Options);
                    $Options = getSecondaryStdFilter($value,$Options);
  		}
  	}
    if(isset($_REQUEST["selectedStdFilter"]) && $_REQUEST["selectedStdFilter"]!=""){
        $selected_option = vtlib_purify($_REQUEST["selectedStdFilter"]);
    }else{
        $selected_option = $ITS4YouReports->reportinformations["stdDateFilterField"];
    }
    echo Zend_JSON::encode($Options)."#@!@#".$selected_option;
}

?>
