<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class Webapp_WS_AttendanceUserHours extends Webapp_WS_Controller {
	
		function process(Webapp_API_Request $request) {
		global $current_user,$adb, $site_URL;
		$current_user =  Users::getActiveAdminUser();
		$employee_name = trim($request->get('userid'));
		
		$response = new Webapp_API_Response();
		if (empty($employee_name)) {
			$message = vtranslate('User cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		$date = date('Y-m-d');
		$startDateTime = new DateTimeField($date . ' ' . date('H:i:s'));
		$userStartDate = $startDateTime->getDisplayDate();
		$userStartDateTime = new DateTimeField($userStartDate . ' 00:00:00');
		$startDateTime = $userStartDateTime->getDBInsertDateTimeValue();
		$endDateTime = new DateTimeField($date . ' ' . date('H:i:s'));
		$userEndDate = $endDateTime->getDisplayDate();
		$userEndDateTime = new DateTimeField($userEndDate . ' 23:59:00');
		$endDateTime = $userEndDateTime->getDBInsertDateTimeValue();
		
		$recentEvent_data = array();
		$generator = new QueryGenerator('CTAttendance', $current_user);
		$generator->setFields(array('employee_name','attendance_status','createdtime','modifiedtime','id'));
		$eventQuery = $generator->getQuery();
		$eventQuery .= " and vtiger_ctattendance.employee_name = '$employee_name' ";
		$query = $adb->pquery($eventQuery);
		if($adb->num_rows($query) == 0){
			throw new WebServiceException(404,vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'));
		} else {
			$total_hours = 0;
			$record = '';
			for($i=0; $i<$adb->num_rows($query); $i++) {
				
				define("SECONDS_PER_HOUR", 60*60);
				$id = $adb->query_result($query, $i, 'ctattendanceid');
				$recordid = vtws_getWebserviceEntityId('CTAttendance',$id);
				
				
				$check_in1 = $adb->query_result($query, $i, 'createdtime');
				$dateTimeFieldInstance1 = new DateTimeField($check_in1);
				$check_in = $dateTimeFieldInstance1->getDisplayTime($current_user);
				
				$attendance_status = $adb->query_result($query, $i, 'attendance_status');
				if($attendance_status == 'check_in'){
					$record = $recordid;
					$check_out1 = date('Y-m-d H:i:s');
					$dateTimeFieldInstance2 = new DateTimeField($check_out1);
					$check_out = $dateTimeFieldInstance2->getDisplayTime($current_user);

				}else{
					$check_out1 = $adb->query_result($query, $i, 'modifiedtime');
					$dateTimeFieldInstance2 = new DateTimeField($check_out1);
					$check_out = $dateTimeFieldInstance2->getDisplayTime($current_user);
				}
				
			    $startdatetime = strtotime($check_in1);
			    // calculate the end timestamp
			    $enddatetime = strtotime($check_out1);
			    // calulate the difference in seconds
			    $difference = $enddatetime - $startdatetime;
			    $total_hours = $difference + $total_hours;
			    $hours = round($difference / SECONDS_PER_HOUR, 0, PHP_ROUND_HALF_DOWN);
				$minutes = round(($difference % SECONDS_PER_HOUR) / 60, 0, PHP_ROUND_HALF_DOWN);
			    // output the result
			    $diffrent = $hours . " hr " . $minutes . " min";
				$recentEvent_data[] = array('id'=> $recordid, 'check_in' => $check_in, 'check_out' => $check_out,'attendance_status' => $attendance_status,'total_hours' => $diffrent);	
			}	
			
			$f_hours = round($total_hours / SECONDS_PER_HOUR, 0, PHP_ROUND_HALF_DOWN);
			$f_minutes = round(($total_hours % SECONDS_PER_HOUR) / 60, 0, PHP_ROUND_HALF_DOWN);
				   // output the result
			$f_diffrent = $f_hours . " hrs " . $f_minutes . " min";
			
			$response->setResult(array('attendance_data'=>$recentEvent_data,'total_hours'=>$f_diffrent,'record'=>$record, 'module'=>'CTAttendance', 'message'=>''));
		}
		return $response;
	}
	
}
