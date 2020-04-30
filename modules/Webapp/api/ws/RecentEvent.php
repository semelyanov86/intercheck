<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class Webapp_WS_RecentEvent extends Webapp_WS_Controller {
	public $totalQuery = "";
	public $totalParams = array();
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$userid = $current_user->id;
		$index = trim($request->get('index'));
		$size = trim($request->get('size'));
		$record = trim($request->get('record'));
		$module = trim($request->get('module'));
		
		$limit = ($index*$size) - $size;
		$recentEvent_data = array();
		$generator = new QueryGenerator('Events', $current_user);
		$generator->setFields(array('subject','activitytype','location','date_start','time_start','location','createdtime','modifiedtime','id'));
		$eventQuery = $generator->getQuery();

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);
		
		if($index == '' || $size == '') {
			if($record == '') {
				$eventQuery .= " AND vtiger_crmentity.setype = 'Calendar' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND CONCAT(due_date,' ',time_end) >= '".$nowInDBFormat."'
					ORDER BY vtiger_activity.date_start, time_start DESC ";
				$this->totalQuery = $eventQuery;
				$this->totalParams = array();
			} else {
				if($module == 'Contacts') {
					$recordEventQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									 inner join vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_cntactivityrel.contactid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC';
				} else {
					$recordEventQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									 inner join vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_seactivityrel.crmid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC';
				}
			}
		} else {
			if($record == '') {
		    $this->totalQuery = $eventQuery ." AND vtiger_crmentity.setype = 'Calendar' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND CONCAT(due_date,' ',time_end) >= '".$nowInDBFormat."'
					ORDER BY vtiger_activity.date_start, time_start DESC";
			$this->totalParams = array();
			
			$eventQuery .= " AND vtiger_crmentity.setype = 'Calendar' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND CONCAT(due_date,' ',time_end) >= '".$nowInDBFormat."'
					ORDER BY vtiger_activity.date_start, time_start DESC limit ".$limit.",".$size;
			
			} else {
				if($module == 'Contacts') {
					$recordEventQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									inner join vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_cntactivityrel.contactid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC limit '.$limit.','.$size;
					$this->totalQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									inner join vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_cntactivityrel.contactid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC';
					$this->totalParams = array();
				} else {
					$recordEventQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									inner join vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_seactivityrel.crmid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC limit '.$limit.','.$size;
					$this->totalQuery = 'SELECT vtiger_activity.subject, vtiger_activity.activitytype, vtiger_activity.location, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.location, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_activity.activityid FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
									inner join vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND ( (vtiger_activity.date_start >= binary CURDATE( ) AND vtiger_activity.time_start >= binary TIME( NOW( ) )) OR ( vtiger_activity.date_start > binary CURDATE( ))) AND vtiger_crmentity.deleted =0 and vtiger_seactivityrel.crmid = '.$record.' ORDER BY vtiger_activity.date_start, time_start DESC';
					$this->totalParams = array();
				}
			}
		}
		
		if($record == '') {
			$query = $adb->pquery($eventQuery);
		} else {
			$query = $adb->pquery($recordEventQuery);
		}
		
		for($i=0; $i<$adb->num_rows($query); $i++) {
			$activityid = $adb->query_result($query, $i, 'activityid');
			$eventSubject = $adb->query_result($query, $i, 'subject');
			$eventSubject = html_entity_decode($eventSubject, ENT_QUOTES, $default_charset);
			$eventtype = $adb->query_result($query, $i, 'activitytype');
			$eventtype = html_entity_decode($eventtype, ENT_QUOTES, $default_charset);
			$startDate = $adb->query_result($query, $i, 'date_start');
			$startTime = $adb->query_result($query, $i, 'time_start');
			$location = $adb->query_result($query, $i, 'location');
			$location = html_entity_decode($location, ENT_QUOTES, $default_charset);
			$startDateTime = $startDate." ".$startTime;
			$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
			$fieldModels = $moduleModel->getFields();
			$startDateTime= DateTimeField::convertToUserTimeZone($startDateTime)->format('Y-m-d H:i:s');
			$DATE_TIME_COMPONENTS = explode(' ' ,$startDateTime);
			$startDate = $DATE_TIME_COMPONENTS[0];
			$startTime = $DATE_TIME_COMPONENTS[1];
			
			$FIELD_MODEL = $fieldModels['date_start'];
			$startDate = $FIELD_MODEL->getDisplayValue($startDate);
			$FIELD_MODEL = $fieldModels['time_start'];
			$startTime = $FIELD_MODEL->getDisplayValue($startTime);
			
			$startDateTime = $startDate." ".$startTime;
			$createdTime = $adb->query_result($query, $i, 'createdtime');
			if($createdTime!=''){
				$dateTimeFieldInstance = new DateTimeField($createdTime);
				$createdTime = Vtiger_Date_UIType::getDisplayDateTimeValue($createdTime);
			}
			
			$modifiedtime = $adb->query_result($query, $i, 'modifiedtime');
			if($modifiedtime!=''){
				$dateTimeFieldInstance = new DateTimeField($modifiedtime);
				$modifiedtime = $dateTimeFieldInstance->getDisplayDateTimeValue();
			}
			//for get Webservice Id of Calender and Events Module
			$EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$activityid)); 
		    if($adb->num_rows($EventTaskQuery) > 0){
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
				$recordId = $wsid.'x'.$activityid;
				$recordModule = 'Calendar';
			}else{
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
				$recordId = $wsid.'x'.$activityid;
				$recordModule = 'Events';
			}
			if($recordModule == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $recordModule;
			}
			if(Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $activityid)){
				$recentEvent_data[] = array('activityid'=> $recordId,'module'=>$recordModule,'eventSubject' => $eventSubject, 'activitytype' => $eventtype,'startDate' => $startDate,'startTime' => $startTime, 'startDateTime' => $startDateTime, 'location' => $location,
									'createdTime' => $createdTime, 'modifiedtime' => $modifiedtime, 'hour_format' => $current_user->hour_format);
			}
		}
		
	   	$name = 'startDateTime';
	   	usort($recentEvent_data, function ($a, $b) use(&$name){
		  return strtotime($a[$name]) - strtotime($b[$name]);
		});

	   	$isLast = true;
		if($this->totalQuery != ""){
			$totalResults = $adb->pquery($this->totalQuery,$this->totalParams);
			$totalRecords = $adb->num_rows($totalResults);
			if($totalRecords > $index*$size){
				$isLast = false;	
			}else{
				$isLast = true;
			}
		}

		$response = new Webapp_API_Response();
		if($adb->num_rows($query) == 0){
			$response->setResult(array('recentEvents'=>[], 'module'=>'Events', 'code'=>404,'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'isLast'=>$isLast));
		} else {
			$response->setResult(array('recentEvents'=>$recentEvent_data, 'module'=>'Events', 'message'=>'','isLast'=>$isLast));
		}
		return $response;
	}
}
