<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class Webapp_WS_GetUserRoute extends Webapp_WS_Controller {
	
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb, $current_user;
		$current_user = $this->getActiveUser();
		$userId = trim($request->get('userid'));
		$date = trim($request->get('date'));
		$userData_userroute = array();
		$selectUserQuery = $adb->pquery("SELECT * FROM webapp_userderoute INNER JOIN vtiger_users ON vtiger_users.id = webapp_userderoute.userid WHERE vtiger_users.id = ? and vtiger_users.deleted = 0 and webapp_userderoute.createdtime LIKE '%$date%' and webapp_userderoute.latitude!='0.0' and webapp_userderoute.longitude!='0.0' GROUP BY webapp_userderoute.longitude,webapp_userderoute.latitude HAVING  COUNT(*) > 1", array($userId));								
		$selectUserQueryCount = $adb->num_rows($selectUserQuery);
		if($selectUserQueryCount!=0){
				for($i=0;$i<$selectUserQueryCount;$i++) {			
					$userid = $adb->query_result($selectUserQuery, $i, 'id');
					$createdtime = $adb->query_result($selectUserQuery, $i, 'createdtime');
					$userRecordModel = Vtiger_Record_Model::getInstanceById($userid, 'Users');
					$first_name = trim($userRecordModel->get('first_name'));
					$last_name = trim($userRecordModel->get('last_name'));
					$userName = html_entity_decode($first_name." ".$last_name, ENT_QUOTES, $default_charset);
					$latitude = trim($adb->query_result($selectUserQuery, $i, 'latitude'));
					$longitude = trim($adb->query_result($selectUserQuery, $i, 'longitude'));
					if(empty($latitude)){
						$latitude = 0;
					}
					if(empty($longitude)){
						$longitude = 0;
					}
					if($createdtime!=''){
						$dateTimeFieldInstance = new DateTimeField($createdtime);
						$createdtime = $dateTimeFieldInstance->getDisplayDateTimeValue($current_user);
					}
					
					$createdtime1 = $adb->query_result($selectUserQuery, $i, 'createdtime');
					
					$userData[] =  array('userid'=>$userid, 'createdtime'=>$createdtime, 'latitude'=>$latitude, 'longitude'=>$longitude, 'username'=>$userName, 'timesorting' => $createdtime1, 'type' => 'userroute');
					$userData_userroute[] = array('userid'=>$userid, 'createdtime'=>$createdtime, 'latitude'=>$latitude, 'longitude'=>$longitude, 'username'=>$userName, 'timesorting' => $createdtime1, 'type' => 'userroute');
				
				} 	
		}
		$generator = new QueryGenerator('Events', $current_user);
		$generator->setFields(array('subject','activitytype','location','date_start','time_start','check_in_location','createdtime','modifiedtime','id'));
		$eventQuery = $generator->getQuery();
		$startDateTime = new DateTimeField($date . ' ' . date('H:i:s'));
		$userStartDate = $startDateTime->getDisplayDate();
		$userStartDateTime = new DateTimeField($userStartDate . ' 00:00:00');
		$startDateTime = $userStartDateTime->getDBInsertDateTimeValue();
		
		$endDateTime = new DateTimeField($date . ' ' . date('H:i:s'));
		$userEndDate = $endDateTime->getDisplayDate();
		$userEndDateTime = new DateTimeField($userEndDate . ' 23:59:00');
		$endDateTime = $userEndDateTime->getDBInsertDateTimeValue();
		
		$eventQuery .= " AND CAST((CONCAT(vtiger_activity.date_start,' ',vtiger_activity.time_start)) AS DATETIME) BETWEEN '" . $startDateTime . "' and '" . $endDateTime . "'  AND vtiger_crmentity.deleted =0 AND vtiger_crmentity.smownerid = '$userid' ORDER BY vtiger_activity.date_start, time_start DESC";
		$query = $adb->pquery($eventQuery);
		
		$userData_events = array();
		for($i=0; $i<$adb->num_rows($query); $i++) {
			$activityid = $adb->query_result($query, $i, 'activityid');
			$eventSubject = trim($adb->query_result($query, $i, 'subject'));
			$eventtype = trim($adb->query_result($query, $i, 'activitytype'));
			$startDate = $adb->query_result($query, $i, 'date_start');
			$startTime = $adb->query_result($query, $i, 'time_start');
			$location = trim($adb->query_result($query, $i, 'location'));
			$check_in_location = trim($adb->query_result($query, $i, 'check_in_location'));
			
			$startDateTime = $startDate." ".$startTime;
			if($startDateTime!=''){
				$dateTimeFieldInstance = new DateTimeField($startDateTime);
				$startDateTime = $dateTimeFieldInstance->getDisplayDateTimeValue($current_user);
			}
			
			if($startDate!=''){
				$dateTimeFieldInstance = new DateTimeField($startDate);
				$startDate = $dateTimeFieldInstance->getDisplayDateTimeValue($current_user);
			}
			
			$createdTime = $adb->query_result($query, $i, 'createdtime');
			if($createdTime!=''){
				$dateTimeFieldInstance = new DateTimeField($createdTime);
				$createdTime = $dateTimeFieldInstance->getDisplayDateTimeValue($current_user);
			}
			$modifiedtime = $adb->query_result($query, $i, 'modifiedtime');
			$modifiedtime1 = $adb->query_result($query, $i, 'modifiedtime');
			if($modifiedtime!=''){
				$dateTimeFieldInstance = new DateTimeField($modifiedtime);
				$modifiedtime = $dateTimeFieldInstance->getDisplayDateTimeValue($current_user);
			}
			 $checkRecordExit = $adb->pquery("SELECT * from ct_address_lat_long where recordid = ?", array($activityid));
			 $countRecord = $adb->num_rows($checkRecordExit);
			 if($countRecord > 0) {
				$latitude = $adb->query_result($checkRecordExit, 0, 'latitude');
				$longitude = $adb->query_result($checkRecordExit, 0, 'longitude');
			 }
			 
			 if(empty($latitude)){
				 $latitude = 0; 
			 }
			 
			 if(empty($longitude)){
				 $longitude = 0; 
			 }
			 
			 
				$check_in_latitude = 0;
				$check_in_longitude = 0;
				
				if($check_in_location){
					$check_in_location =  explode(",",$check_in_location);
					$check_in_latitude = $check_in_location[0];
					$check_in_longitude = $check_in_location[1];
					
				}
				
				if(empty($check_in_latitude)){
						$check_in_latitude = 0;
					}
			
			if(empty($check_in_longitude)){
						$check_in_longitude = 0;
					}
			$userData[] = array('activityid'=> '18x'.$activityid, 'eventSubject' => html_entity_decode($eventSubject), 'activitytype' => $eventtype,'startDate' => $startDate,'startTime' => $startTime, 'startDateTime' => $startDateTime, 'location' => $location,
									'createdTime' => $createdTime, 'modifiedtime' => $modifiedtime, 'hour_format' => $current_user->hour_format, 'latitude' => $latitude, 'longitude' => $longitude, 'check_in_latitude'=> $check_in_latitude,'check_in_longitude'=> $check_in_longitude, 'timesorting' => $modifiedtime1, 'type' => 'events');
		
		$userData_events[] = array('activityid'=> '18x'.$activityid, 'eventSubject' => html_entity_decode($eventSubject), 'activitytype' => $eventtype,'startDate' => $startDate,'startTime' => $startTime, 'startDateTime' => $startDateTime, 'location' => $location,
									'createdTime' => $createdTime, 'modifiedtime' => $modifiedtime, 'hour_format' => $current_user->hour_format, 'latitude' => $latitude, 'longitude' => $longitude, 'check_in_latitude'=> $check_in_latitude,'check_in_longitude'=> $check_in_longitude, 'timesorting' => $modifiedtime1, 'type' => 'events');
		}
		$userData_attendance = array();
		
		$generator = new QueryGenerator('WebAttendance', $current_user);
		$generator->setFields(array('employee_name','attendance_status','createdtime','modifiedtime','id','check_in_location','check_out_location'));
		$eventQuery = $generator->getQuery();
		$eventQuery .= " and vtiger_webattendance.employee_name = '$userId' and vtiger_crmentity.createdtime BETWEEN '" . $startDateTime . "' and '" . $endDateTime . "'";
		$query = $adb->pquery($eventQuery);
		
		for($i=0; $i<$adb->num_rows($query); $i++) {
				
				$check_in1 = $adb->query_result($query, $i, 'createdtime');
				$dateTimeFieldInstance1 = new DateTimeField($check_in1);
				$check_in = $dateTimeFieldInstance1->getDisplayDateTimeValue($current_user);
				
				$attendance_status = $adb->query_result($query, $i, 'attendance_status');
				if($attendance_status == 'check_in'){
					$record = $recordid;
					$check_out1 = date('Y-m-d H:i:s');
					$dateTimeFieldInstance2 = new DateTimeField($check_out1);
					$check_out = $dateTimeFieldInstance2->getDisplayDateTimeValue($current_user);

				}else{
					$check_out1 = $adb->query_result($query, $i, 'modifiedtime');
					$dateTimeFieldInstance2 = new DateTimeField($check_out1);
					$check_out = $dateTimeFieldInstance2->getDisplayDateTimeValue($current_user);
				}
				
				$check_in_latitude = 0;
				$check_in_longitude = 0;
				$check_out_latitude = 0;
				$check_out_longitude =  0;
				$check_in_location = $adb->query_result($query, $i, 'check_in_location');
				if($check_in_location){
					$check_in_location =  explode(",",$check_in_location);
					$check_in_latitude = $check_in_location[0];
					$check_in_longitude = $check_in_location[1];
					
				}
				$check_out_location = $adb->query_result($query, $i, 'check_out_location');
				if($check_out_location){
					$check_out_location =  explode(",",$check_out_location);
					$check_out_latitude = $check_out_location[0];
					$check_out_longitude  = $check_out_location[1];
				}
				
				if(empty($check_in_latitude)){
						$check_in_latitude = 0;
					}
				if(empty($check_in_longitude)){
						$check_in_longitude = 0;
					}
				if(empty($check_out_latitude)){
						$check_out_latitude = 0;
					}
				
				if(empty($check_out_longitude)){
						$check_out_longitude = 0;
					}
				
				if($check_in_latitude!=0 && $check_in_longitude!=0 && $check_in!=''){
					$userData[] = array('latitude'=> $check_in_latitude,'longitude'=> $check_in_longitude, 'time' => $check_in, 'timesorting' => $check_in1, 'type' => 'webattendance', 'status' => 'check_in');
				
				}
				if($check_out_latitude!=0 && $check_out_longitude!=0 && $check_out!=''){
					$userData[] = array('latitude'=> $check_out_latitude,'longitude'=> $check_out_longitude, 'time' => $check_out, 'timesorting' => $check_in1, 'type' => 'webattendance', 'status' => 'check_out');
				
				}		
			$userData_attendance[] = array('check_out_latitude'=> $check_out_latitude,'check_out_longitude'=> $check_out_longitude,'check_in_latitude'=> $check_in_latitude,'check_in_longitude'=> $check_in_longitude, 'check_in' => $check_in, 'check_out' => $check_out, 'timesorting' => $check_in1, 'type' => 'webattendance');
			}
		$name = 'timesorting';
		 usort($userData, function ($a, $b) use(&$name){
		  return strtotime($a[$name]) - strtotime($b[$name]);
		});
		$response = new Webapp_API_Response();
		if(count($userData) == 0) {
			$response->setResult(array('code'=>404,'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger')));
		}else{
			$response->setResult($userData);
		}	
		return $response;			
	}
}

?>
