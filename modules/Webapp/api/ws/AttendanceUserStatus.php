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
include_once 'include/QueryGenerator/QueryGenerator.php';;

class Webapp_WS_AttendanceUserStatus extends Webapp_WS_Controller {
	
	
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
		$recentEvent_data = array();
		$generator = new QueryGenerator('CTAttendance', $current_user);
		$generator->setFields(array('employee_name','attendance_status','createdtime','modifiedtime','id'));
		$generator->addCondition('attendance_status', 'check_in', 'e');
		$eventQuery = $generator->getQuery();
		$eventQuery .= " and vtiger_ctattendance.employee_name = '$employee_name' AND vtiger_ctattendance.eventid = ''";
		
		$query = $adb->pquery($eventQuery);
		
		$num_rows = $adb->num_rows($query);
		if( $num_rows > 0){
			$response->setResult(array('attendance_status'=>true, 'module'=>'CTAttendance', 'message'=>''));
		} else {
			$response->setResult(array('attendance_status'=>false, 'module'=>'CTAttendance', 'message'=>''));
		}
		return $response;
	}
	
}
