<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class Webapp_WS_CheckOutgoingServer extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $adb, $current_user;
		$current_user = $this->getActiveUser();
		$response = new Webapp_API_Response();
		$query = "SELECT id FROM vtiger_systems WHERE server_type='email'";
		$result = $adb->pquery($query,array());
		if($adb->num_rows($result) > 0){
			$EmailModuleModel = Vtiger_Module_Model::getInstance('Emails');
			if(!in_array($EmailModuleModel->get('presence'), array(0,2))){
				$message = vtranslate('Emails','Emails').' '.vtranslate('Module is disabled','Webapp');
				$result =  array('code' => 0,'message' => $message);
			}else{
				$message = vtranslate('Outgoing server is Enabled','Webapp');
				$result =  array('code' => 1,'message' => $message);
			}
		}else{
			$message = vtranslate('Outgoing server is not Enabled','Webapp');
			$result =  array('code' => 0,'message' => $message);
		}
		$response->setResult($result);
		return $response;
	}
}
