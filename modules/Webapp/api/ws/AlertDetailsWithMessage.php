<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once 'include/Webservices/Query.php';
include_once dirname(__FILE__) . '/FetchAllAlerts.php';

class Webapp_WS_AlertDetailsWithMessage extends Webapp_WS_FetchAllAlerts {
	
	function process(Webapp_API_Request $request) {
		global $current_user;

		$response = new Webapp_API_Response();

		$alertid = trim($request->get('alertid'));
		$current_user = $this->getActiveUser();

		$alert = $this->getAlertDetails($alertid);
		if(empty($alert)) {
			$message = vtranslate('Alert not found','Webapp');
			$response->setError(1401, $message);
		} else {
			$result = array();
			$result['alert'] = $this->getAlertDetails($alertid);
			$response->setResult($result);			
		}

		return $response;
	}
	
	function getAlertDetails($alertid) {
		
		$alertModel = Webapp_WS_AlertModel::modelWithId($alertid);
		
		$alert = false;
		if($alertModel) {
			$alert = $alertModel->serializeToSend();
			
			$alertModel->setUser($this->getActiveUser());
			$alert['message'] = $alertModel->message();
		}
		
		return $alert;
	}
	
}
