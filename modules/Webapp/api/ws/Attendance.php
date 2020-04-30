<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_Attendance extends Webapp_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Webapp_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = Users::getActiveAdminUser();
		$module = 'CTAttendance';
		$eventid = trim($request->get('eventid'));
		$recordid = trim($request->get('record'));
		$attendance_status = trim($request->get('attendance_status'));
		$employee_name = trim($request->get('userid'));
		$latitude = trim($request->get('latitude'));
		$longitude = trim($request->get('longitude'));
		
		$response = new Webapp_API_Response();
		
		if (empty($attendance_status)) {
			$message = vtranslate('Status cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		if (empty($employee_name)) {
			$message = vtranslate('User cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		if (empty($latitude)) {
			$message = vtranslate('Latitude cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}	
		if (empty($longitude)) {
			$message = vtranslate('Longitude cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		try {
			if($eventid != ''){
				// Retrieve or Initalize
				if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
					$this->recordValues = vtws_retrieve($recordid, $current_user);
				} else {
					$this->recordValues = array();
				}
				
				// Set the modified values
				$checkin_status = false;
				$this->recordValues['attendance_status'] = trim($attendance_status);
				$this->recordValues['employee_name'] = '19x'.$employee_name;
				$this->recordValues['assigned_user_id'] = '19x'.$current_user->id;
				$calendarid = explode('x',$eventid);
				$eventid = Webapp_WS_Utils::getEntityModuleWSId('Calendar').'x'.$calendarid[1];
				$this->recordValues['eventid'] = $eventid;
				if($attendance_status == 'check_in'){
					$this->recordValues['check_in_location'] = "$latitude,$longitude";
					$this->recordValues['check_in_address'] = trim($request->get('check_in_address'));
					$checkin_status = true;
				}elseif($attendance_status == 'check_out'){
					$this->recordValues['check_out_location'] = "$latitude,$longitude";
					$this->recordValues['check_out_address'] = trim($request->get('check_out_address'));
					$checkin_status = false;
				}
				// Update or Create
				if (isset($this->recordValues['id'])) {
					if($attendance_status == 'check_out') {
						$recordId = explode('x',$this->recordValues['id']);
						$attendanceRecorddModel = Vtiger_Record_Model::getInstanceById($recordId[1], $module);
						$attendanceRecorddModel->set('mode','edit');
						$attendanceRecorddModel->set('check_out_location',"$latitude,$longitude");
						$attendanceRecorddModel->set('check_out_address',trim($request->get('check_out_address')));
						$attendanceRecorddModel->set('attendance_status',$attendance_status);
						$attendanceRecorddModel->set('assigned_user_id',$current_user->id);
						$attendanceRecorddModel->save();
						$message = vtranslate('Shift ended successfully','Webapp');
					}else{
						$this->recordValues = vtws_update($this->recordValues, $current_user);
						$message = vtranslate('Shift ended successfully','Webapp');
					}
				} else {
					$this->recordValues = vtws_create($module, $this->recordValues, $current_user);
					$message = vtranslate('Shift started successfully','Webapp');
				}
				$response->setResult(array('id'=>$this->recordValues['id'],'attendance_status'=>$checkin_status,'message'=>$message));
			}else{
				// Retrieve or Initalize
				if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
					$this->recordValues = vtws_retrieve($recordid, $current_user);
				} else {
					$this->recordValues = array();
				}
				
				// Set the modified values
				
					$this->recordValues['attendance_status'] = trim($attendance_status);
					$this->recordValues['employee_name'] = '19x'.$employee_name;
					$this->recordValues['assigned_user_id'] = '19x'.$current_user->id;
					
					if($attendance_status == 'check_in'){
						$this->recordValues['check_in_location'] = "$latitude,$longitude";
						$this->recordValues['check_in_address'] = trim($request->get('check_in_address'));
					}elseif($attendance_status == 'check_out'){
						$this->recordValues['check_out_location'] = "$latitude,$longitude";
						$this->recordValues['check_out_address'] = trim($request->get('check_out_address'));
					}
				// Update or Create
				if (isset($this->recordValues['id'])) {
					if($attendance_status == 'check_out') {
						$recordId = explode('x',$this->recordValues['id']);
						$attendanceRecorddModel = Vtiger_Record_Model::getInstanceById($recordId[1], $module);
						$attendanceRecorddModel->set('mode','edit');
						$attendanceRecorddModel->set('check_out_location',"$latitude,$longitude");
						$attendanceRecorddModel->set('check_out_address',trim($request->get('check_out_address')));
						$attendanceRecorddModel->set('attendance_status',$attendance_status);
						$attendanceRecorddModel->set('assigned_user_id',$current_user->id);
						$attendanceRecorddModel->save();
						$message = vtranslate('Shift ended successfully','Webapp');
						
					}else{
						$this->recordValues = vtws_update($this->recordValues, $current_user);
						$message = vtranslate('Shift ended successfully','Webapp');
					}
				} else {
					$this->recordValues = vtws_create($module, $this->recordValues, $current_user);
					$message = vtranslate('Shift started successfully','Webapp');
				}

				$result = array('record'=>array('id'=>$this->recordValues['id'],'module'=>$module),'message'=>$message);
				$response->setResult($result);
			}
			
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}
	
}
