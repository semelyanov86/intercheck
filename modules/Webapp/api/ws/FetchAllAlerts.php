<?php

include_once dirname(__FILE__) . '/models/Alert.php';

class Webapp_WS_FetchAllAlerts extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		$response = new Webapp_API_Response();
		$current_user = $this->getActiveUser();
		$result = array();
		$result['alerts'] = $this->getAlertDetails();
		$response->setResult($result);
		return $response;
	}
	function getAlertDetails() {
		$alertModels = Webapp_WS_AlertModel::models();
		$alerts = array();
		foreach($alertModels as $alertModel) {
			$alerts[] = $alertModel->serializeToSend();;
		}
		return $alerts;
	}
}
