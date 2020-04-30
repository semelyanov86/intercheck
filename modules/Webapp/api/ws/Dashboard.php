<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/FetchRecord.php';

class Webapp_WS_Dashboard extends Webapp_WS_FetchRecord {
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();

		$response = new Webapp_API_Response();
		$attendance_status = $this->attendance_status();
		$dashboardWidgetsList = $this->getActiveDashboardWidgetsList();
		if(!empty($dashboardWidgetsList)){
			$WidgetsList = $dashboardWidgetsList['WidgetsList'];
			$ReportsList = $dashboardWidgetsList['ReportsList'];
		}else{
			$WidgetsList =  array();
			$ReportsList = array();
		}
		$userid = $current_user->id;
		$sequence_list = array();
		global $adb;
		$seq_query = "SELECT * FROM webapp_dashboard_sequence WHERE userid = ?";
		$seq_result =  $adb->pquery($seq_query,array($userid));
		if($adb->num_rows($seq_result) > 0){
			for ($i=0; $i < $adb->num_rows($seq_result); $i++) { 
				$id = $adb->query_result($seq_result,$i,'id');
				$type = $adb->query_result($seq_result,$i,'type');
				$sequence_list[] =  array('id'=>$id,'type'=>$type);
			}
		}
		$result = array('attendance_status'=>$attendance_status,'EventsLabel'=>vtranslate('Events','Events'),'WidgetsList'=>$WidgetsList,'ReportsList'=>$ReportsList,'sequence_list'=>$sequence_list);
		$response->setResult($result);
		return $response;
	}

	function getActiveDashboardWidgetsList(){
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $current_user,$adb; // Required for vtws_update API
		$current_user = $this->getActiveUser();

		$moduleName = 'Home';
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);

		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			// TODO : Need to optimize the widget which are retrieving twice
		   $dashboardTabs = $dashBoardModel->getActiveTabs();
		   $tabid = $dashboardTabs[0]["id"];
		   $dashBoardModel->set("tabid",$tabid);
		   $widgets1 = $dashBoardModel->getDashboards($moduleName);
		   $widgets2 = $dashBoardModel->getSelectableDashboard();

		   $widgets['WidgetsList'] = array();
		   $widgets['ReportsList'] = array();
		   $allowedWidgets = array('Open Tickets','Upcoming Activities','Funnel Amount','History','Key Metrics','Potentials by Stage','Pipelined Amount');
		   $addedReport = array();

		   $sequence_list = array();
		  
		   $userid = $current_user->id;
		   $seq_query = "SELECT * FROM webapp_dashboard_sequence WHERE userid = ?";
		   $seq_result =  $adb->pquery($seq_query,array($userid));
		   if($adb->num_rows($seq_result) > 0){
			  for ($i=0; $i < $adb->num_rows($seq_result); $i++) { 
				  $id = $adb->query_result($seq_result,$i,'id');
				  $type = $adb->query_result($seq_result,$i,'type');
				  $sequence_list[] =  array('id'=>$id,'type'=>$type);
			  }
		   }
		   $max = count($sequence_list);
		   foreach ($widgets1 as $key => $widget) {
		   		if($widget->get('reportid') != ''){
		   			$addedReport[] = $widget->get('reportid');
		   		}
		   		if(in_array($widget->get('linklabel'), $allowedWidgets)){
		   			$skey = array_search($widget->get('linkid'), array_column($sequence_list, 'id'));
		   			if(($skey != "" || $sequence_list[$skey]['id'] == $widget->get('linkid')) && $sequence_list[$skey]['type'] == 'widget'){
		   				$sequence = $skey;
		   			}else{
		   				$sequence = $max++;
		   			}
			   		$widgets['WidgetsList'][] =  array('widgetid'=>$widget->get('linkid'),'widgetname'=>$widget->get('linklabel'),'widgetlabel'=>vtranslate($widget->get('linklabel'),'Vtiger'),'is_added'=>true,'sequence'=>$sequence);
			   		$sort[$key] = $widget->get('linkid');
		   		}
		   }
		   foreach ($widgets2 as $key => $widget) {
		   		if(in_array($widget->get('linklabel'), $allowedWidgets)){
		   			$skey = array_search($widget->get('linkid'), array_column($sequence_list, 'id'));
		   			if(($skey != "" || $sequence_list[$skey]['id'] == $widget->get('linkid')) && $sequence_list[$skey]['type'] == 'widget'){
		   				$sequence = $skey;
		   			}else{
		   				$sequence = $max++;
		   			}
			   		$widgets['WidgetsList'][] =  array('widgetid'=>$widget->get('linkid'),'widgetname'=>$widget->get('linklabel'),'widgetlabel'=>vtranslate($widget->get('linklabel'),'Vtiger'),'is_added'=>false,'sequence'=>$sequence);
			   		$sort[$key] = $widget->get('linkid');
		   		}
		   }
		   
		   array_multisort($sort, SORT_ASC, $widgets['WidgetsList']);
		   global $adb;
		   $query = "SELECT vtiger_report.*,vtiger_reportmodules.primarymodule,vtiger_tab.presence FROM vtiger_report INNER JOIN vtiger_reportmodules ON vtiger_report.reportid = vtiger_reportmodules.reportmodulesid INNER JOIN vtiger_tab ON vtiger_reportmodules.primarymodule = vtiger_tab.name WHERE reporttype = ? AND sharingtype = ? AND vtiger_tab.presence IN (0,2)";
		   $results =  $adb->pquery($query,array('chart','Public'));

		   for($i=0;$i<$adb->num_rows($results);$i++){
		   		$reportid = $adb->query_result($results,$i,'reportid');
		   		$reportname = $adb->query_result($results,$i,'reportname');
		   		$reportname = html_entity_decode($reportname, ENT_QUOTES, $default_charset);
		   		$skey = array_search($reportid, array_column($sequence_list, 'id'));
	   			if(($skey != "" || $sequence_list[$skey]['id'] == $reportid) && $sequence_list[$skey]['type'] == 'report'){
	   				$sequence = $skey;
	   			}else{
		   			$sequence = $max++;
		   		}
		   		if(in_array($reportid, $addedReport)){
		   			$is_added = true;
		   		}else{
		   			$is_added = false;
		   		}
		   		$widgets['ReportsList'][] =  array('reportid'=>$reportid,'reportname'=>$reportname,'is_added'=>$is_added,'sequence'=>$sequence);
		   }
		   return $widgets;
		} else {
			return array();
		}
	}

	function attendance_status(){
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$employee_name = $current_user->id;

		$current_user =  Users::getActiveAdminUser();
		$recentEvent_data = array();
		$generator = new QueryGenerator('WebAttendance', $current_user);
		$generator->setFields(array('employee_name','attendance_status','createdtime','modifiedtime','id'));
		$generator->addCondition('attendance_status', 'check_in', 'e');
		$eventQuery = $generator->getQuery();
		$eventQuery .= " AND vtiger_webattendance.employee_name = '$employee_name' AND vtiger_webattendance.eventid = ''";
		
		$query = $adb->pquery($eventQuery);
		
		$num_rows = $adb->num_rows($query);
		if( $num_rows > 0){
			$attendance_status = true;
		} else {
			$attendance_status = false;
		}
		return $attendance_status;
	}

}
