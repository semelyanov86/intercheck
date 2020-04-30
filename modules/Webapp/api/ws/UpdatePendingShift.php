<?php

class Webapp_WS_UpdatePendingShift extends Webapp_WS_Controller {
	function process(Webapp_API_Request $request) {
		global $current_user; 
		$current_user = $this->getActiveUser();
		
		$module = 'WebAttendance';
		
		$attendance_status = trim($request->get('attendance_status'));
		$employee_name = trim($request->get('userid'));
		$latitude = trim($request->get('latitude'));
		$longitude = trim($request->get('longitude'));
		$checkin_status = trim($request->get('checkin_status'));
		
		$response = new Webapp_API_Response();
		
		if (empty($attendance_status)) {
			$message = vtranslate('Status cannot be empty!'=>'Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		
		if (empty($employee_name)) {
			$message = vtranslate('User cannot be empty!'=>'Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		
		if (empty($latitude)) {
			$message = vtranslate('Latitude cannot be empty!'=>'Webapp');
			$response->setError(1501, $message);
			return $response;
		}
			
		if (empty($longitude)) {
			$message = vtranslate('Longitude cannot be empty!'=>'Webapp');
			$response->setError(1501, $message);
			return $response;
		}
			
		if($checkin_status == 'Expire') {
			global $adb;
			$getAttendanceQuery = $adb->pquery("SELECT * FROM vtiger_webattendance INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_webattendance.webattendanceid where vtiger_crmentity.deleted = 0 AND vtiger_webattendance.attendance_status = 'check_in' AND vtiger_webattendance.employee_name = ?", array($employee_name));
			$numOfRows = $adb->num_rows($getAttendanceQuery);

			if($numOfRows > 0) {
				for($i=0;$i<$numOfRows;$i++){
					$attendanceid = $adb->query_result($getAttendanceQuery, $i, 'webattendanceid');
					$attendanceRecorddModel = Vtiger_Record_Model::getInstanceById($attendanceid, $module);
					$attendanceRecorddModel->set('mode','edit');
					$attendanceRecorddModel->set('check_out_location',"$latitude,$longitude");
					$attendanceRecorddModel->set('check_out_address',trim($request->get('check_out_address')));
					$attendanceRecorddModel->set('attendance_status',$attendance_status);
					$attendanceRecorddModel->set('assigned_user_id',$current_user->id);
					$attendanceRecorddModel->save();
				}
				$response->setResult(array('status' => true));
			} else {
				$response->setResult(array('status' => false));
			}
		} else {
			$response->setResult(array('status' => false));
		}
		
		return $response;
	}
}
