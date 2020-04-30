<?php

class Webapp_WS_Logout extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $adb, $current_user;
		$current_user = $this->getActiveUser();
		$userid = $current_user->id;
		// devicetoken will be blank of current user
		$query = "UPDATE webapp_userdevicetoken SET devicetoken = '', sessionid = '' WHERE userid = ?";
		$adb->pquery($query,array($userid));
		$response = new Webapp_API_Response();
		session_regenerate_id(true);
		Vtiger_Session::destroy();
		
		//Track the logout History
		$moduleName = 'Users';
		$moduleModel = Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		$message = vtranslate('Logout Successfully','Webapp');
		$result =  array('code' => 1,'message' => $message);
		$response->setResult($result);
		return $response;
	}
}
