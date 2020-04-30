<?php
 
class Webapp_WS_SendSMS extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $adb;
		global $current_user;
		$current_user = $this->getActiveUser();
		$getSMSNotifier = $adb->pquery("SELECT * from vtiger_smsnotifier_servers where isactive = 1 order by id LIMIT 0,1");
		$countSMSNotifier = $adb->num_rows($getSMSNotifier);
		$response = new Webapp_API_Response();
		if($countSMSNotifier > 0) {
		
			$valuesJSONString =  $request->get('values');
			$values = Zend_Json::decode($valuesJSONString);
			
			//Multiple mobiles numbers separated by comma
			$mobileNumber = trim($values['mobiles']);

			$currentUserModel = Users_Record_Model::getCurrentUserModel();

			//Your message to send, Add URL encoding here.
			$message = $values['message'];
			$recordIds = array();
			$toNumbers = array();
			$recordIds[] = trim($values['recordIds']);
			$toNumbers[] = $mobileNumber;
			$moduleName = trim($request->get('module'));
			if(!empty($toNumbers)) {
				$id = SMSNotifier_Record_Model::SendSMS($message, $toNumbers, $current_user->id, $recordIds, $moduleName);
				$statusDetails = SMSNotifier::getSMSStatusInfo($id);
				$response->setResult(array('id' => $id, 'statusdetails' => $statusDetails[0]));
			}
			
		} else {
			$message = vtranslate('SMSNotifier is not enable in CRM. Please enable it first','Webapp');
			$response->setError(503, $message);
			//$result = array('code'=> 0 , 'message'=>'SMSNotifier is not enable in CRM. Please enable it first');
		}
		return $response;
	}
}
		
