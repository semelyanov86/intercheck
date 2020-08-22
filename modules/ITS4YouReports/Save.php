<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once("include/Zend/Json.php");

ITS4YouReports::sshow($_REQUEST);
exit;

global $adb;
global $log,$current_user;
$reportid = vtlib_purify($_REQUEST["record"]);

$debug_save = false;
//$debug_save = true;
/*global $current_user;if($current_user->id=="1"){
	$debug_save = true;
}*/

$export_sql = false;
//$export_sql = true;
/*global $current_user;if($current_user->id=="1"){
	$export_sql = true;
}*/

if($debug_save){
    echo "<pre>";print_r($_REQUEST);echo "</pre>";
    $adb->setDebug(true);
}

$r4u_sesstion_name = ITS4YouReports::getITS4YouReportStoreName();
$r4u_sesstion_unset = ITS4YouReports::unsetITS4YouReportsSerialize($r4u_sesstion_name);
if($debug_save){
	echo "<pre>UNSET Session: ";print_r($r4u_sesstion_unset);echo "</pre>";
}
$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

global $default_charset;
//<<<<<<<report>>>>>>>>>
$reportname = vtlib_purify($_REQUEST["reportname"]);
$reportname = html_entity_decode($reportname,ENT_QUOTES,$default_charset);
$reportdescription = vtlib_purify($_REQUEST["reportdesc"]);
$reportdescription = html_entity_decode($reportdescription,ENT_QUOTES,$default_charset);
$reporttype = vtlib_purify($_REQUEST["reportType"]);
$folderid = vtlib_purify($_REQUEST["reportfolder"]);
$isDuplicate = vtlib_purify($_REQUEST["isDuplicate"]);
//<<<<<<<report>>>>>>>>>
//<<<<<<<selectcolumn>>>>>>>>>
$selectedcolumnstring = vtlib_purify($_REQUEST["selectedColumnsString"]);
$saveselectedcolumns = explode(";",trim($selectedcolumnstring, ";"));
$selectedcolumns = array();
foreach ($saveselectedcolumns AS $sc)
{
    $selectedcolumns[] = $sc;
}
//<<<<<<<selectcolumn>>>>>>>>>

//<<<<<<<selectqfcolumn>>>>>>>>>
/*$selectedqfcolumnstring = vtlib_purify($_REQUEST["selectedQFColumnsString"]);
$saveselectedqfcolumns = explode(";",trim($selectedqfcolumnstring, ";"));
$selectedqfcolumns = array();
foreach ($saveselectedqfcolumns AS $sc)
{
    if($sc!="none"){
		$selectedqfcolumns[] = $sc;
	}
}*/
// ITS4YOU-CR SlOl 27. 2. 2014 9:58:21 
if(isset($_REQUEST["qf_to_go"]) && $_REQUEST["qf_to_go"]!=""){
	$selectedqfcolumns_tp = explode('$_@_$',$_REQUEST["qf_to_go"]);
	foreach ($selectedqfcolumns_tp as $key=>$selectedqfcolumns_tp_val) {
		$selectedqfcolumns[] = trim($selectedqfcolumns_tp_val,"qf:");
	}
}
//<<<<<<<selectqfcolumn>>>>>>>>>
//<<<<<<<selectedSummaries>>>>>>>>>
$selectedSummariesString = vtlib_purify($_REQUEST["selectedSummariesString"]);
$selectedSummaries_array = explode(";",trim($selectedSummariesString, ";"));
$selectedSummaries = array();
foreach ($selectedSummaries_array AS $sm)
{
    $selectedSummaries[] = $sm;
}
//<<<<<<<selectedSummaries>>>>>>>>>
//<<<<<<<SummariesOrderBy>>>>>>>>>
$summaries_orderby = vtlib_purify($_REQUEST["summaries_orderby_columnString"]);
$summaries_orderby_type = vtlib_purify($_REQUEST["summaries_orderby_type"]);
//<<<<<<<SummariesOrderBy>>>>>>>>>

// ITS4YOU-CR SlOl 13. 3. 2014 10:17:31
$lbl_array = array();
$lbl_url_string = vtlib_purify($_REQUEST["labels_to_go"]);
$lbl_url_string = urldecode($lbl_url_string);
$lbl_url_string = html_entity_decode($lbl_url_string,ENT_QUOTES,$default_charset);
// $lbl_url_string = str_replace("@AMPKO@", "&", $lbl_url_string);
if($lbl_url_string!=""){
	$lbl_url_arr = explode('$_@_$', $lbl_url_string);
	foreach ($lbl_url_arr as $key=>$lbl_value) {
		if (strpos($lbl_value, '_SC_lLbLl_') !== false) {
			$temp = explode('_SC_lLbLl_', $lbl_value);
			$temp_lbls = explode('_lLGbGLl_', $temp[1]);
			$lbl_key = $temp_lbls[0];
			$lbl_value = $temp_lbls[1];
			$lbl_array["SC"][$lbl_key] = $lbl_value;
		}
		if (strpos($lbl_value, '_SM_lLbLl_') !== false) {
			$temp = explode('_SM_lLbLl_', $lbl_value);
			$temp_lbls = explode('_lLGbGLl_', $temp[1]);
			$lbl_key = $temp_lbls[0];
			$lbl_value = $temp_lbls[1];
			$lbl_array["SM"][$lbl_key] = $lbl_value;
		}
		
		if (strpos($lbl_value, '_CT_lLbLl_') !== false) {
			$temp = explode('_CT_lLbLl_', $lbl_value);
			$temp_lbls = explode('_lLGbGLl_', $temp[1]);
			$lbl_key = $temp_lbls[0];
			$lbl_value = $temp_lbls[1];
			$lbl_array["CT"][$lbl_key] = $lbl_value;
		}
	}
}
// ITS4YOU-END 13. 3. 2014 10:17:32 

//<<<<<<<reportsortcol>>>>>>>>>
$sort_by1 = decode_html(vtlib_purify($_REQUEST["Group1"]));
$sort_order1 = vtlib_purify($_REQUEST["Sort1"]);
$sort_by2 =decode_html(vtlib_purify($_REQUEST["Group2"]));
$sort_order2 = vtlib_purify($_REQUEST["Sort2"]);
$sort_by3 = decode_html(vtlib_purify($_REQUEST["Group3"]));
$sort_order3 = vtlib_purify($_REQUEST["Sort3"]);

$timeline_type2 = vtlib_purify($_REQUEST["timeline_type2"]);
$timeline_type3 = vtlib_purify($_REQUEST["timeline_type3"]);

if(isset($_REQUEST["TimeLineColumn_Group1"]) && $_REQUEST["TimeLineColumn_Group1"]!="" && $_REQUEST["Group1"]!="none"){
    $TimeLineColumn_Group1 = vtlib_purify($_REQUEST["TimeLineColumn_Group1"]);
    $TimeLineColumn_Group1_arr = explode("@vlv@", $TimeLineColumn_Group1);
    $TimeLineColumn_str1 = $TimeLineColumn_Group1_arr[0];
    $TimeLineColumn_frequency1 = $TimeLineColumn_Group1_arr[1];
}
if(isset($_REQUEST["TimeLineColumn_Group2"]) && $_REQUEST["TimeLineColumn_Group2"]!="" && $_REQUEST["Group2"]!="none"){
    $TimeLineColumn_Group2 = vtlib_purify($_REQUEST["TimeLineColumn_Group2"]);
    $TimeLineColumn_Group2_arr = explode("@vlv@", $TimeLineColumn_Group2);
    $TimeLineColumn_str2 = $TimeLineColumn_Group2_arr[0];
    $TimeLineColumn_frequency2 = $TimeLineColumn_Group2_arr[1];
}
if(isset($_REQUEST["TimeLineColumn_Group3"]) && $_REQUEST["TimeLineColumn_Group3"]!="" && $_REQUEST["Group3"]!="none"){
    $TimeLineColumn_Group3 = vtlib_purify($_REQUEST["TimeLineColumn_Group3"]);
    $TimeLineColumn_Group3_arr = explode("@vlv@", $TimeLineColumn_Group3);
    $TimeLineColumn_str3 = $TimeLineColumn_Group3_arr[0];
    $TimeLineColumn_frequency3 = $TimeLineColumn_Group3_arr[1];
}
$sort_by_column = decode_html(vtlib_purify($_REQUEST["SortByColumn"]));
$sort_order_column = vtlib_purify($_REQUEST["SortOrderColumn"]);
//<<<<<<<reportsortcol>>>>>>>>>

if ($reporttype != "grouping" && $reporttype != "timeline")
{
    /*if(!in_array($sort_by1,$selectedcolumns)){
    	$selectedcolumns[] = $sort_by1;
    }
    if(!in_array($sort_by2,$selectedcolumns)){
    	$selectedcolumns[] = $sort_by2;
    }
    if(!in_array($sort_by3,$selectedcolumns)){
    	$selectedcolumns[] = $sort_by3;
    }*/
}

//<<<<<<<reportmodules>>>>>>>>>
// vtlib_getModuleNameById()
$pmodule = vtlib_purify($_REQUEST["primarymodule"]);
$smodule = vtlib_purify(trim($_REQUEST["secondarymodule"],":"));
//<<<<<<<reportmodules>>>>>>>>>

//<<<<<<<standarfilters>>>>>>>>>
$stdDateFilterField = vtlib_purify($_REQUEST["stdDateFilterField"]);
$stdDateFilter = vtlib_purify($_REQUEST["stdDateFilter"]);
$startdate = vtlib_purify($_REQUEST["startdate"]);
$enddate = vtlib_purify($_REQUEST["enddate"]);

$dbCurrentDateTime = new DateTimeField(date('Y-m-d H:i:s'));
if(!empty($startdate)) {
	$startDateTime = new DateTimeField($startdate.' '. $dbCurrentDateTime->getDisplayTime());
	$startdate = $startDateTime->getDBInsertDateValue();
}
if(!empty($enddate)) {
	$endDateTime = new DateTimeField($enddate.' '. $dbCurrentDateTime->getDisplayTime());
	$enddate = $endDateTime->getDBInsertDateValue();
}
//<<<<<<<standardfilters>>>>>>>>>

//<<<<<<<shared entities>>>>>>>>>
$sharetype = vtlib_purify($_REQUEST["sharing"]);
$shared_entities = vtlib_purify($_REQUEST["sharingSelectedColumnsString"]);
//<<<<<<<shared entities>>>>>>>>>

//<<<<<<<columnstototal>>>>>>>>>>
if(isset($_REQUEST["curl_to_go"]) && $_REQUEST["curl_to_go"]!=""){
	$columnstototal = explode('$_@_$',$_REQUEST["curl_to_go"]);
	/*$allKeys = array_keys($_REQUEST);
	for ($i=0;$i<count($allKeys);$i++)
	{
	   $string = substr($allKeys[$i], 0, 3);
	   if($string == "cb:")
	   {
		   $columnstototal[] = $allKeys[$i];
	   }
	}*/
}
//<<<<<<<columnstototal>>>>>>>>>

//<<<<<<<advancedfilter>>>>>>>>
$json = new Zend_Json();

$std_filter_columns = $ITS4YouReports->getStdFilterColumns();
			
$advft_criteria = vtlib_purify($_REQUEST['advft_criteria']);
$advft_criteria = $json->decode($advft_criteria);

$advft_criteria_groups = vtlib_purify($_REQUEST['advft_criteria_groups']);
$advft_criteria_groups = $json->decode($advft_criteria_groups);
//<<<<<<<advancedfilter>>>>>>>>

//<<<<<<<groupconditioncolumn>>>>>>>>
$groupft_criteria = vtlib_purify($_REQUEST['groupft_criteria']);
$groupft_criteria = $json->decode($groupft_criteria);
//<<<<<<<groupconditioncolumn>>>>>>>>

//<<<<<<<limit>>>>>>>>
$limit = $summaries_limit = 0;
if($_REQUEST["limit"]!="" && $_REQUEST["limit"]>0){
    $limit = vtlib_purify($_REQUEST["limit"]);
}
if($_REQUEST["summaries_limit"]!="" && $_REQUEST["summaries_limit"]>0){
    $summaries_limit = vtlib_purify($_REQUEST["summaries_limit"]);
}
//<<<<<<<limit>>>>>>>>

//<<<<<<<scheduled report>>>>>>>>
$isReportScheduled	= vtlib_purify($_REQUEST['isReportScheduled']);
$selectedRecipients	= vtlib_purify($_REQUEST['selectedRecipientsString']);
$scheduledFormat	= vtlib_purify($_REQUEST['scheduledReportFormat']);
$scheduledInterval	= vtlib_purify($_REQUEST['scheduledIntervalString']);
//<<<<<<<scheduled report>>>>>>>>

// ITS4YOU-CR SlOl 20. 3. 2014 12:02:47
for($tg_i=1;$tg_i<4;$tg_i++){
    if(isset($_REQUEST["TimeLineColumn_Group$tg_i"]) && $_REQUEST["TimeLineColumn_Group$tg_i"]!="" && $_REQUEST["TimeLineColumn_Group$tg_i"]!="none"){
        $tg_col_str = vtlib_purify($_REQUEST["TimeLineColumn_Group$tg_i"]);
        $tg_col_arr = explode("@vlv@", $tg_col_str);
        $timelinecols_arr[$tg_i] = $tg_col_str;
        $timelinecols_frequency[$tg_i] = $tg_col_arr[1];
    }
}
// ITS4YOU-END 20. 3. 2014 12:02:48 

// ITS4YOU-CR SlOl | 2.7.2014 15:18 
if(isset($_REQUEST["chartType"])){
    $chartType = vtlib_purify($_REQUEST["chartType"]);
    if($chartType!="" && $chartType!="none"){
        $data_series = vtlib_purify($_REQUEST["data_series"]);
        $charttitle = vtlib_purify($_REQUEST["charttitle"]);
    }
}
// ITS4YOU-END 2.7.2014 15:18 

// global $adb;$adb->setDebug(true);
// echo "<pre>";print_r(array($selectedcolumns,$selectedqfcolumns,$shared_entities,$pmodule,$smodule,$limit,$sort_by1, $sort_order1,$sort_by2, $sort_order2,$sort_by3, $sort_order3,$sort_by_column,$sort_order_column,$stdDateFilterField, $stdDateFilter, $startdate, $enddate,$columnstototal,$advft_criteria,$advft_criteria_groups));echo "</pre>";
// echo "<pre>";print_r($_REQUEST);echo "</pre>";
// exit;
if($reportid != "" && $isDuplicate!="true")
{
	$d_selectedcolumns = "DELETE FROM its4you_reports4you_selectcolumn WHERE its4you_reports4you_selectcolumn.queryid = ?";
	$d_columnsqlresult = $adb->pquery($d_selectedcolumns,array($reportid));
        if(!empty($selectedcolumns))
	{
		for($i=0 ;$i<count($selectedcolumns);$i++)
		{
			if(!empty($selectedcolumns[$i])){
				$icolumnsql = "INSERT INTO its4you_reports4you_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) VALUES (?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$icolumnsqlresult = $adb->pquery($icolumnsql, array($reportid ,$i,(decode_html($selectedcolumns[$i]))));
				$export_sql===true?$adb->setDebug(false):"";
			}
		}
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_selectcolumn");
	
	// ITS4YOU-CR SlOl 7. 3. 2014 11:24:46 Summaries Save
	$d_selectedSummaries = "DELETE FROM its4you_reports4you_summaries WHERE reportsummaryid = ?";
	$d_Summariessqlqfresult = $adb->pquery($d_selectedSummaries,array($reportid));
	if(!empty($selectedSummaries))
	{
		for($i=0 ;$i<count($selectedSummaries);$i++)
		{
			if(!empty($selectedSummaries[$i])){
				$iSmmariessql = "INSERT INTO its4you_reports4you_summaries (reportsummaryid,summarytype,columnname) VALUES (?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$iSummariessqlresult = $adb->pquery($iSmmariessql, array($reportid,$i,(decode_html($selectedSummaries[$i]))));
				$export_sql===true?$adb->setDebug(false):"";
			}
		}
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_summaries");
	// ITS4YOU-END 7. 3. 2014 11:24:48 
	// ITS4YOU-CR SlOl 24. 3. 2014 8:52:40
	$d_selected_s_orderby = "DELETE FROM its4you_reports4you_summaries_orderby WHERE reportid = ?";
	$d_selected_s_orderbyresult = $adb->pquery($d_selected_s_orderby,array($reportid));
	if($summaries_orderby!="" && $summaries_orderby_type!="")
	{
		$d_selected_s_orderby_sql = "INSERT INTO  its4you_reports4you_summaries_orderby (reportid,columnindex,summaries_orderby,summaries_orderby_type) VALUES (?,?,?,?)";
		$export_sql===true?$adb->setDebug(true):"";
		$d_selected_s_orderby_result = $adb->pquery($d_selected_s_orderby_sql, array($reportid,0,$summaries_orderby,$summaries_orderby_type));
		$export_sql===true?$adb->setDebug(false):"";
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_summaries_orderby");
	// ITS4YOU-END 24. 3. 2014 8:52:42 
	
	// ITS4YOU-CR SlOl 13. 3. 2014 11:34:24
	$d_selectedLabels = "DELETE FROM its4you_reports4you_labels WHERE reportid = ?";
	$d_Labelssqlqfresult = $adb->pquery($d_selectedLabels,array($reportid));
	if(!empty($lbl_array))
	{
            foreach ($lbl_array as $type=>$type_array) {
                foreach ($type_array as $column_str=>$column_lbl) {
                        $iLabelssql = "INSERT INTO its4you_reports4you_labels (reportid,type,columnname,columnlabel) VALUES (?,?,?,?)";
                        $export_sql===true?$adb->setDebug(true):"";
						$iLabelssqlresult = $adb->pquery($iLabelssql, array($reportid,$type,$column_str,$column_lbl));
						$export_sql===true?$adb->setDebug(false):"";
                }
            }
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_labels");

        $d_selectedqfcolumns = "DELETE FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
	$d_columnsqlqfresult = $adb->pquery($d_selectedqfcolumns,array($reportid));
	if(!empty($selectedqfcolumns))
	{
		for($i=0 ;$i<count($selectedqfcolumns);$i++)
		{
			if(!empty($selectedqfcolumns[$i])){
				$icolumnsql = "INSERT INTO its4you_reports4you_selectqfcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) VALUES (?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$icolumnsqlresult = $adb->pquery($icolumnsql, array($reportid,$i,(decode_html($selectedqfcolumns[$i]))));
				$export_sql===true?$adb->setDebug(false):"";
			}
		}
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_selectqfcolumn");
	
	$d_shared = "DELETE FROM its4you_reports4you_sharing WHERE reports4youid = ?";
	$d_sharedresult = $adb->pquery($d_shared,array($reportid));
	if($shared_entities != "")
	{
		if($sharetype == "share")
		{
			$selectedsharecolumn = explode("|",$shared_entities);
			for($i=0 ;$i< count($selectedsharecolumn) -1 ;$i++)
			{
				$temp = preg_split('/::/',$selectedsharecolumn[$i]);
				$icolumnsql = "INSERT INTO its4you_reports4you_sharing (reports4youid,shareid,setype) VALUES (?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$icolumnsqlresult = $adb->pquery($icolumnsql, array($reportid,$temp[1],$temp[0]));
				$export_sql===true?$adb->setDebug(false):"";
			}
		}
	}
	$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_sharing");

	if($reportid != "")
	{
		// ITS4YOU MaJu customreports
		if(isset($_REQUEST['customreporttype']) && $_REQUEST['customreporttype']!='')
			$state = $_REQUEST['customreporttype'];
		else
			$state = 'CUSTOM';
		$ireportsql = "UPDATE its4you_reports4you SET reports4youname=?, description=?, folderid=?, reporttype=?, columns_limit=?, summaries_limit=? WHERE reports4youid=?";
		$ireportparams = array($reportname, $reportdescription, $folderid, $reporttype, $limit, $summaries_limit, $reportid);
		// ITS4YOU-END
		$export_sql===true?$adb->setDebug(true):"";
		$ireportresult = $adb->pquery($ireportsql, $ireportparams);
		$export_sql===true?$adb->setDebug(false):"";
		$log->info("Reports4You :: Save->Successfully updated its4you_reports4you");
		if($ireportresult!=false){
			//<<<<reportmodules>>>>>>>
			$d_modules = "DELETE FROM its4you_reports4you_modules WHERE reportmodulesid = ?";
			$d_modulesresult = $adb->pquery($d_modules,array($reportid));
			$ireportmodulesql = "INSERT INTO its4you_reports4you_modules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) VALUES (?,?,?)";
			$export_sql===true?$adb->setDebug(true):"";
			$ireportmoduleresult = $adb->pquery($ireportmodulesql, array($reportid, $pmodule, $smodule));
			$export_sql===true?$adb->setDebug(false):"";
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_modules");
			//<<<<reportmodules>>>>>>>
                        
                        //<<<<step3 its4you_reports4you_sortcol>>>>>>>
			$d_sortcol1 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
			$d_sortcol1_result = $adb->pquery($d_sortcol1,array($reportid,1));
			if($sort_by1 != "")
			{
				$sort_by1sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$sort_by1result = $adb->pquery($sort_by1sql, array(1, $reportid, $sort_by1, $sort_order1,$TimeLineColumn_str1,$TimeLineColumn_frequency1));
				$export_sql===true?$adb->setDebug(false):"";
			}
			$d_sortcol2 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
			$d_sortcol2_result = $adb->pquery($d_sortcol2,array($reportid,2));
			if($sort_by2 != "")
			{
				$sort_by2sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$sort_by2result = $adb->pquery($sort_by2sql, array(2,$reportid,$sort_by2,$sort_order2,$timeline_type2,$TimeLineColumn_str2,$TimeLineColumn_frequency2));
				$export_sql===true?$adb->setDebug(false):"";
			}

			$d_sortcol3 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
			$d_sortcol3_result = $adb->pquery($d_sortcol3,array($reportid,3));
			if($sort_by3 != "")
			{
				$sort_by3sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$sort_by3result = $adb->pquery($sort_by3sql, array(3,$reportid,$sort_by3,$sort_order3,$timeline_type3,$TimeLineColumn_str3,$TimeLineColumn_frequency3));
				$export_sql===true?$adb->setDebug(false):"";
			}
			
			$d_sortcol4 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
			$d_sortcol4_result = $adb->pquery($d_sortcol4,array($reportid,4));
			if($sort_by_column != "")
			{
				$sort_by_columnsql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER) VALUES (?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$sort_by_columnresult = $adb->pquery($sort_by_columnsql, array(4,$reportid,$sort_by_column,$sort_order_column));
				$export_sql===true?$adb->setDebug(false):"";
			}
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_sortcol");
			//<<<<step3 its4you_reports4you_sortcol>>>>>>>

			//<<<<step5 standarfilder>>>>>>>
			$d_datefilter= "DELETE FROM its4you_reports4you_datefilter WHERE datefilterid = ?";
			$d_datefilter_result = $adb->pquery($d_datefilter,array($reportid));
			$ireportmodulesql = "INSERT INTO its4you_reports4you_datefilter (DATEFILTERID,DATECOLUMNNAME,DATEFILTER,STARTDATE,ENDDATE) VALUES (?,?,?,?,?)";
			$export_sql===true?$adb->setDebug(true):"";
			$ireportmoduleresult = $adb->pquery($ireportmodulesql, array($reportid, $stdDateFilterField, $stdDateFilter, $startdate, $enddate));
			$export_sql===true?$adb->setDebug(false):"";
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_datefilter");
			//<<<<step5 standarfilder>>>>>>>

			//<<<<step4 columnstototal>>>>>>>
			$d_summary= "DELETE FROM its4you_reports4you_summary WHERE reportsummaryid = ?";
			$d_summary_result = $adb->pquery($d_summary,array($reportid));
			for ($i=0;$i<count($columnstototal);$i++)
			{
				$ireportsummarysql = "INSERT INTO its4you_reports4you_summary (REPORTSUMMARYID,SUMMARYTYPE,COLUMNNAME) VALUES (?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$ireportsummaryresult = $adb->pquery($ireportsummarysql, array($reportid, $i, $columnstototal[$i]));
				$export_sql===true?$adb->setDebug(false):"";
			}
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_summary");
			//<<<<step4 columnstototal>>>>>>>

			//<<<<step5 advancedfilter>>>>>>>	
                        global $default_charset;
			$d_adv_criteria= "DELETE FROM its4you_reports4you_relcriteria WHERE queryid = ?";
			$d_adv_criteria_result = $adb->pquery($d_adv_criteria,array($reportid));
                        foreach($advft_criteria as $column_index => $column_condition) {
				if(empty($column_condition)) continue;
				
				$adv_filter_comparator = $column_condition["comparator"];
                                if(in_array($column_condition["columnname"], $std_filter_columns)){
                                    $adv_filter_column = $column_condition["columnname"];
                                }else{
                                    $adv_filter_column = $column_condition["columnname"];
                                }
                                $adv_filter_value = $column_condition["value"];

				$adv_filter_column_condition = $column_condition["column_condition"];
				$adv_filter_groupid = $column_condition["groupid"];
                                
                                if(in_array($adv_filter_column, $std_filter_columns)){
                                    $temp_val = explode("<;@STDV@;>",html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset));
                                    $val[0] = DateTimeField::convertToDBFormat(trim($temp_val[0]));
                                    $val[1] = DateTimeField::convertToDBFormat(trim($temp_val[1]));
                                    $adv_filter_value = implode("<;@STDV@;>",$val);
                                    // $adv_filter_value = html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset);
                                }else{
                                    $column_info = explode(":",$adv_filter_column);
                                    $temp_val = explode(",",$adv_filter_value);
                                    if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
                                    {
                                            $val = Array();
                                            for($x=0;$x<count($temp_val);$x++) {
                                                    if(trim($temp_val[$x]) != '') {
                                                            $date = new DateTimeField(trim($temp_val[$x]));
                                                            if($column_info[4] == 'D') {
                                                                    $val[$x] = DateTimeField::convertToDBFormat(
                                                                                    trim($temp_val[$x]));
                                                            } elseif($column_info[4] == 'DT') {
                                                                    $val[$x] = $date->getDBInsertDateTimeValue();
                                                            } else {
                                                                    $val[$x] = $date->getDBInsertTimeValue();
                                                            }
                                                    }
                                            }
                                            $adv_filter_value = implode(",",$val);
                                    }
                                }
                
				$irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));
				$export_sql===true?$adb->setDebug(false):"";
			
				// Update the condition expression for the group to which the condition column belongs
				$groupConditionExpression = '';
				if(!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
					$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
				}
				$groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
				$advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
			}
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_relcriteria");
			
			$d_adv_criteria_grouping = "DELETE FROM its4you_reports4you_relcriteria_grouping WHERE queryid = ?";
			$export_sql===true?$adb->setDebug(true):"";
			$d_adv_criteria_grouping_result = $adb->pquery($d_adv_criteria_grouping,array($reportid));
			$export_sql===true?$adb->setDebug(false):"";
			foreach($advft_criteria_groups as $group_index => $group_condition_info) {				
				if(!isset($group_condition_info) || empty($group_condition_info)) continue;
				$irelcriteriagroupsql = "INSERT INTO its4you_reports4you_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) VALUES (?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $reportid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
				$export_sql===true?$adb->setDebug(false):"";
			}
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_relcriteria_grouping");
			
                        $d_adv_criteria= "DELETE FROM its4you_reports4you_relcriteria_summaries WHERE reportid = ?";
			$d_adv_criteria_result = $adb->pquery($d_adv_criteria,array($reportid));
			foreach($groupft_criteria as $column_index => $column_condition) {
				if(empty($column_condition)) continue;
				
				$adv_filter_column = $column_condition["columnname"];
				$adv_filter_comparator = $column_condition["comparator"];
				$adv_filter_value = $column_condition["value"];
				$adv_filter_column_condition = $column_condition["column_condition"];
				$adv_filter_groupid = $column_condition["groupid"];
			
				$column_info = explode(":",$adv_filter_column);
				$temp_val = explode(",",$adv_filter_value);
                                if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
				{
					$val = Array();
					for($x=0;$x<count($temp_val);$x++) {
						if(trim($temp_val[$x]) != '') {
							$date = new DateTimeField(trim($temp_val[$x]));
							if($column_info[4] == 'D') {
								$val[$x] = DateTimeField::convertToDBFormat(
										trim($temp_val[$x]));
							} elseif($column_info[4] == 'DT') {
								$val[$x] = $date->getDBInsertDateTimeValue();
							} else {
								$val[$x] = $date->getDBInsertTimeValue();
							}
						}
					}
					$adv_filter_value = implode(",",$val);
				}
                
				$irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria_summaries(reportid,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)";
				$export_sql===true?$adb->setDebug(true):"";
				$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));
				$export_sql===true?$adb->setDebug(false):"";
			
				// Update the condition expression for the group to which the condition column belongs
				$groupConditionExpression = '';
				if(!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
					$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
				}
				$groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
				$advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
			}
			$log->info("Reports4You :: Save->Successfully updated its4you_reports4you_relcriteria_summaries");

                        //<<<<step5 advancedfilter>>>>>>>
			
			$owner = vtlib_purify($_REQUEST["template_owner"]);
			$sharingtype = vtlib_purify($_REQUEST["sharing"]);
			if($owner!="" && $owner!=""){
				$limitsql = "UPDATE its4you_reports4you_settings SET owner=?, sharingtype=? WHERE reportid=?";
          		$export_sql===true?$adb->setDebug(true):"";
				  $limitresult = $adb->pquery($limitsql, array($owner,$sharingtype,$reportid));
				$export_sql===true?$adb->setDebug(false):"";
			}
			$log->info("Reports4You :: Save->Successfully updated  its4you_reports4you_settings");
		
			//<<<<step7 scheduledReport>>>>>>>
			if($isReportScheduled == 'off' || $isReportScheduled == '0' || $isReportScheduled == '') {
				$deleteScheduledReportSql = "DELETE FROM its4you_reports4you_scheduled_reports WHERE reportid=?";
				$adb->pquery($deleteScheduledReportSql, array($reportid));
			} else{
				$checkScheduledResult = $adb->pquery('SELECT 1 FROM its4you_reports4you_scheduled_reports WHERE reportid=?', array($reportid));

				if($adb->num_rows($checkScheduledResult) > 0) {
					$scheduledReportSql = 'UPDATE its4you_reports4you_scheduled_reports SET recipients=?,schedule=?,format=? WHERE reportid=?';
					$export_sql===true?$adb->setDebug(true):"";
					$adb->pquery($scheduledReportSql, array($selectedRecipients,$scheduledInterval,$scheduledFormat,$reportid));
					$export_sql===true?$adb->setDebug(false):"";
				} else {
					$scheduleReportSql = 'INSERT INTO its4you_reports4you_scheduled_reports (reportid,recipients,schedule,format,next_trigger_time) VALUES (?,?,?,?,?)';
					$export_sql===true?$adb->setDebug(true):"";
					$adb->pquery($scheduleReportSql, array($reportid,$selectedRecipients,$scheduledInterval,$scheduledFormat,date("Y-m-d H:i:s")));
					$export_sql===true?$adb->setDebug(false):"";
				}
			}
			//<<<<step7 scheduledReport>>>>>>>
                        
                        //<<<<step12 Report Charts >>>>>>>
                        $deleteChartsSql = "DELETE FROM its4you_reports4you_charts WHERE reports4youid=?";
                        $adb->pquery($deleteChartsSql, array($reportid));
                        if($chartType!="" && $chartType!="none"){
                            $ChartsSql = 'INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle) VALUES (?,?,?,?)';
                            $export_sql===true?$adb->setDebug(true):"";
							$adb->pquery($ChartsSql, array($reportid,$chartType,$data_series,$charttitle));
							$export_sql===true?$adb->setDebug(false):"";
                        }
                        //<<<<step12 Report Charts >>>>>>>
		}else{
			$errormessage = "<font color='red'><B>Error Message<ul>
				<li><font color='red'>Error while inserting the record</font>
				</ul></B></font> <br>" ;
			echo $errormessage;
			die;
		}
	}
}else{
	$genQueryId = $adb->getUniqueID("its4you_reports4you_selectquery");
	$reportid = $genQueryId;
	if($genQueryId != "")
	{
		$iquerysql = "insert into its4you_reports4you_selectquery (QUERYID,STARTINDEX,NUMOFOBJECTS) values (?,?,?)";
		$export_sql===true?$adb->setDebug(true):"";
		$iquerysqlresult = $adb->pquery($iquerysql, array($genQueryId,0,0));
		$export_sql===true?$adb->setDebug(false):"";
		$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_selectquery id $genQueryId");
		if($iquerysqlresult!=false)
		{
			//<<<<step2 vtiger_rep4u_selectcolumn>>>>>>>>
			if(!empty($selectedcolumns))
			{
				for($i=0 ;$i<count($selectedcolumns);$i++)
				{
					if(!empty($selectedcolumns[$i])){
						$icolumnsql = "insert into its4you_reports4you_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) values (?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$icolumnsqlresult = $adb->pquery($icolumnsql, array($genQueryId,$i,(decode_html($selectedcolumns[$i]))));
						$export_sql===true?$adb->setDebug(false):"";
					}
				}
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_selectcolumn id $genQueryId");
			
                        //<<<< its4you_reports4you_summaries>>>>>>>>
			if(!empty($selectedSummaries))
			{
                            for($i=0 ;$i<count($selectedSummaries);$i++)
                            {
                                if(!empty($selectedSummaries[$i])){
                                    $iSmmariessql = "INSERT INTO its4you_reports4you_summaries (reportsummaryid,summarytype,columnname) VALUES (?,?,?)";
                                    $export_sql===true?$adb->setDebug(true):"";
                                    $iSummariessqlresult = $adb->pquery($iSmmariessql, array($genQueryId,$i,(decode_html($selectedSummaries[$i]))));
                                    $export_sql===true?$adb->setDebug(false):"";
                                }
                            }
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_summaries id $genQueryId");
			
                        if($summaries_orderby!="" && $summaries_orderby_type!="")
			{
                            $d_selected_s_orderby_sql = "INSERT INTO  its4you_reports4you_summaries_orderby (reportid,columnindex,summaries_orderby,summaries_orderby_type) VALUES (?,?,?,?)";
                            $export_sql===true?$adb->setDebug(true):"";
                            $d_selected_s_orderby_result = $adb->pquery($d_selected_s_orderby_sql, array($genQueryId,0,$summaries_orderby,$summaries_orderby_type));
                            $export_sql===true?$adb->setDebug(false):"";
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_summaries_orderby id $genQueryId");
			
			if(!empty($lbl_array))
			{
                            foreach ($lbl_array as $type=>$type_array) {
                                foreach ($type_array as $column_str=>$column_lbl) {
                                    $iLabelssql = "INSERT INTO its4you_reports4you_labels (reportid,type,columnname,columnlabel) VALUES (?,?,?,?)";
                                    $export_sql===true?$adb->setDebug(true):"";
                                    $iLabelssqlresult = $adb->pquery($iLabelssql, array($genQueryId,$type,$column_str,$column_lbl));
                                    $export_sql===true?$adb->setDebug(false):"";
                                }
                            }
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_labels id $genQueryId");

			if(!empty($selectedqfcolumns))
			{
				for($i=0 ;$i<count($selectedqfcolumns);$i++)
				{
					if(!empty($selectedqfcolumns[$i])){
						$icolumnsql = "insert into its4you_reports4you_selectqfcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) values (?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$icolumnsqlresult = $adb->pquery($icolumnsql, array($genQueryId,$i,(decode_html($selectedqfcolumns[$i]))));
						$export_sql===true?$adb->setDebug(false):"";
					}
				}
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_selectqfcolumn id $genQueryId");
			
			if($shared_entities != "")
			{
				if($sharetype == "Shared")
				{
					$selectedcolumn = explode(";",$shared_entities);
					for($i=0 ;$i< count($selectedcolumn) -1 ;$i++)
					{
						$temp = preg_split('/::/',$selectedcolumn[$i]);
						$icolumnsql = "insert into its4you_reports4you_sharing (reports4youid,shareid,setype) values (?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$icolumnsqlresult = $adb->pquery($icolumnsql, array($genQueryId,$temp[1],$temp[0]));
						$export_sql===true?$adb->setDebug(false):"";
					}
				}
			}
			$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_sharing id $genQueryId");
     
			if($genQueryId != "")
			{
				// ITS4YOU MaJu customreports
				if(isset($_REQUEST['customreporttype']) && $_REQUEST['customreporttype']!='')
					$state = $_REQUEST['customreporttype'];
				else
					$state = 'CUSTOM';
				$ireportsql = "insert into its4you_reports4you (reports4youid,reports4youname,description,folderid,reporttype,columns_limit,summaries_limit) values (?,?,?,?,?,?,?)";
				$ireportparams = array($genQueryId, $reportname, $reportdescription, $folderid,$reporttype,$limit,$summaries_limit);
				// ITS4YOU-END
				$export_sql===true?$adb->setDebug(true):"";
				$ireportresult = $adb->pquery($ireportsql, $ireportparams);
				$export_sql===true?$adb->setDebug(false):"";
				$log->info("Reports4You :: Save->Successfully saved its4you_reports4you id $genQueryId");
				if($ireportresult!=false){
					//<<<<reportmodules>>>>>>>
					$ireportmodulesql = "insert into its4you_reports4you_modules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) values (?,?,?)";
					$export_sql===true?$adb->setDebug(true):"";
					$ireportmoduleresult = $adb->pquery($ireportmodulesql, array($genQueryId, $pmodule, $smodule));
					$export_sql===true?$adb->setDebug(false):"";
					$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_modules id $genQueryId");
					//<<<<reportmodules>>>>>>>

                                        //<<<<step3 its4you_reports4you_sortcol>>>>>>>
					if($sort_by1 != "")
					{
                                                $sort_by1sql = "insert into its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$sort_by1result = $adb->pquery($sort_by1sql, array(1, $genQueryId, $sort_by1, $sort_order1,$TimeLineColumn_str1,$TimeLineColumn_frequency1));
						$export_sql===true?$adb->setDebug(false):"";
					}
					if($sort_by2 != "")
					{
						$sort_by2sql = "insert into its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$sort_by2result = $adb->pquery($sort_by2sql, array(2,$genQueryId,$sort_by2,$sort_order2,$timeline_type2,$TimeLineColumn_str2,$TimeLineColumn_frequency2));
						$export_sql===true?$adb->setDebug(false):"";
					}
					if($sort_by3 != "")
					{
						$sort_by3sql = "insert into its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$sort_by3result = $adb->pquery($sort_by3sql, array(3,$genQueryId,$sort_by3,$sort_order3,$timeline_type3,$TimeLineColumn_str3,$TimeLineColumn_frequency3));
						$export_sql===true?$adb->setDebug(false):"";
					}
					if($sort_by_column != "")
					{
						$sort_by_columnsql = "insert into its4you_reports4you_sortcol (SORTCOLID,REPORTID,COLUMNNAME,SORTORDER) values (?,?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$sort_by_columnresult = $adb->pquery($sort_by_columnsql, array(4,$genQueryId,$sort_by_column,$sort_order_column));
						$export_sql===true?$adb->setDebug(false):"";
					}
					$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_sortcol id $genQueryId");
					//<<<<step3 its4you_reports4you_sortcol>>>>>>>

					//<<<<step5 standarfilder>>>>>>>
					$ireportmodulesql = "insert into its4you_reports4you_datefilter (DATEFILTERID,DATECOLUMNNAME,DATEFILTER,STARTDATE,ENDDATE) values (?,?,?,?,?)";
					$export_sql===true?$adb->setDebug(true):"";
					$ireportmoduleresult = $adb->pquery($ireportmodulesql, array($genQueryId, $stdDateFilterField, $stdDateFilter, $startdate, $enddate));
					$export_sql===true?$adb->setDebug(false):"";
					$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_datefilter id $genQueryId");
					//<<<<step5 standarfilder>>>>>>>

					//<<<<step4 columnstototal>>>>>>>
					for ($i=0;$i<count($columnstototal);$i++)
					{
						$ireportsummarysql = "insert into its4you_reports4you_summary (REPORTSUMMARYID,SUMMARYTYPE,COLUMNNAME) values (?,?,?)";
						$export_sql===true?$adb->setDebug(true):"";
						$ireportsummaryresult = $adb->pquery($ireportsummarysql, array($genQueryId, $i, $columnstototal[$i]));
						$export_sql===true?$adb->setDebug(false):"";
					}
					$log->info("Reports4You :: Save->Successfully saved its4you_reports4you_summary id $genQueryId");
					//<<<<step4 columnstototal>>>>>>>

					//<<<<step5 advancedfilter>>>>>>>					
                                        foreach($advft_criteria as $column_index => $column_condition) {
                                            if(empty($column_condition)) continue;

                                            $adv_filter_column = $column_condition["columnname"];
                                            $adv_filter_comparator = $column_condition["comparator"];
                                            $adv_filter_value = $column_condition["value"];
                                            $adv_filter_column_condition = $column_condition["column_condition"];
                                            $adv_filter_groupid = $column_condition["groupid"];

                                            /*
                            if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
                                            {
                                                    $val = Array();
                                                    for($x=0;$x<count($temp_val);$x++) {
                                                            list($temp_date,$temp_time) = explode(" ",$temp_val[$x]);
                                                            $temp_date = getDBInsertDateValue(trim($temp_date));
                                                            $val[$x] = $temp_date;
                                                            if($temp_time != '') $val[$x] = $val[$x].' '.$temp_time;
                                                    }
                                                    $adv_filter_value = implode(",",$val);
                                            }
                            */
                                            if(in_array($adv_filter_column, $std_filter_columns)){
                                                $temp_val = explode("<;@STDV@;>",html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset));
                                                $val[0] = DateTimeField::convertToDBFormat(trim($temp_val[0]));
                                                $val[1] = DateTimeField::convertToDBFormat(trim($temp_val[1]));
                                                $adv_filter_value = implode("<;@STDV@;>",$val);
                                                // $adv_filter_value = html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset);
                                            }else{
                                                $column_info = explode(":",$adv_filter_column);
                                                $temp_val = explode(",",$adv_filter_value);
                                                if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ))
                                                {
                                                        $val = Array();
                                                        for($x=0;$x<count($temp_val);$x++) {
                                                                if(trim($temp_val[$x]) != '') {
                                                                        $date = new DateTimeField(trim($temp_val[$x]));
                                                                        if($column_info[4] == 'D') {
                                                                                $val[$x] = DateTimeField::convertToDBFormat(
                                                                                                trim($temp_val[$x]));
                                                                        } elseif($column_info[4] == 'DT') {
                                                                                $val[$x] = $date->getDBInsertDateTimeValue();
                                                                        } else {
                                                                                $val[$x] = $date->getDBInsertTimeValue();
                                                                        }
                                                                }
                                                        }
                                                        $adv_filter_value = implode(",",$val);
                                                }
                                            }

                                            $irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)";
                                            $export_sql===true?$adb->setDebug(true):"";
											$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($genQueryId, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));
											$export_sql===true?$adb->setDebug(false):"";

                                            // Update the condition expression for the group to which the condition column belongs
                                            $groupConditionExpression = '';
                                            if(!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
                                                    $groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
                                            }
                                            $groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
                                            $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
                                        }
                                        $log->info("Reports4You :: Save->Successfully saved its4you_reports4you_relcriteria id $genQueryId");
                                        foreach($advft_criteria_groups as $group_index => $group_condition_info) {				
                                                if(!isset($group_condition_info) || empty($group_condition_info)) continue;
                                                $irelcriteriagroupsql = "INSERT INTO its4you_reports4you_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) VALUES (?,?,?,?)";
                                                $export_sql===true?$adb->setDebug(true):"";
												$irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $genQueryId, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
												$export_sql===true?$adb->setDebug(false):"";
                                        }
                                        $log->info("Reports4You :: Save->Successfully saved its4you_reports4you_relcriteria_grouping id $genQueryId");

					//<<<<step5 advancedfilter>>>>>>>
					
					$owner = vtlib_purify($_REQUEST["template_owner"]);
					$sharingtype = vtlib_purify($_REQUEST["sharing"]);
					if($owner!="" && $owner!=""){
						$limitsql = "insert into its4you_reports4you_settings (reportid,owner,sharingtype) VALUES (?,?,?)";
		          		$export_sql===true?$adb->setDebug(true):"";
						  $limitresult = $adb->pquery($limitsql, array($genQueryId,$owner,$sharingtype));
						$export_sql===true?$adb->setDebug(false):"";
					}
					$log->info("Reports4You :: Save->Successfully saved  its4you_reports4you_settings id $genQueryId");
					//<<<<step5 advancedfilter>>>>>>>

					//<<<<step7 scheduledReport>>>>>>>
					if($isReportScheduled == 'on' || $isReportScheduled == '1'){
						$scheduleReportSql = 'INSERT INTO its4you_reports4you_scheduled_reports (reportid,recipients,schedule,format,next_trigger_time) VALUES (?,?,?,?,?)';
						$export_sql===true?$adb->setDebug(true):"";
						$adb->pquery($scheduleReportSql, array($genQueryId,$selectedRecipients,$scheduledInterval,$scheduledFormat,date("Y-m-d H:i:s")));
						$export_sql===true?$adb->setDebug(false):"";
					}
					$log->info("Reports :: Save->Successfully saved its4you_reports4you_scheduled_reports id $genQueryId");
					//<<<<step7 scheduledReport>>>>>>>
                                        
                                        //<<<<step12 Report Charts >>>>>>>
                                        $deleteChartsSql = "DELETE FROM its4you_reports4you_charts WHERE reports4youid=?";
                                        $adb->pquery($deleteChartsSql, array($reportid));
                                        if($chartType!="" && $chartType!="none"){
                                            $ChartsSql = 'INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle) VALUES (?,?,?,?)';
                                            $export_sql===true?$adb->setDebug(true):"";
											$adb->pquery($ChartsSql, array($reportid,$chartType,$data_series,$charttitle));
											$export_sql===true?$adb->setDebug(false):"";
                                        }
                                        //<<<<step12 Report Charts >>>>>>>

				}else{
					$errormessage = "<font color='red'><B>Error Message<ul>
						<li><font color='red'>Error while inserting the record</font>
						</ul></B></font> <br>" ;
					echo $errormessage;
					die;
				}
			}
		}else
		{
			$errormessage = "<font color='red'><B>Error Message<ul>
				<li><font color='red'>Error while inserting the record (QUERYID)</font>
				</ul></B></font> <br>" ;
			echo $errormessage;
			die;
		}
	}
}
if(isset($_REQUEST["SaveType"]) && $_REQUEST["SaveType"]=="Save&Run"){
	$header_loc = "index.php?action=resultGenerate&module=ITS4YouReports&record=$reportid&parenttab=Tools";
}else{
	$header_loc = "index.php?module=ITS4YouReports&action=index&parenttab=Analytics";
}
if($debug_save){
    exit;
}
if($export_sql){
    exit;
}
echo "<script>window.location.replace('$header_loc');</script>";
// header("Location: $header_loc");
exit;
?>