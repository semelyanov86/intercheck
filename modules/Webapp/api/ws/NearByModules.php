<?php

class Webapp_WS_NearByModules extends Webapp_WS_Controller {

	function process(Webapp_API_Request $request) {
		global $current_user, $adb, $site_URL; // Few core API assumes this variable availability
		
		$current_user = $this->getActiveUser();

		$cardScannerModules =  array();
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$presence = array('0', '2');
		$allowedModules =  array('Leads','Contacts','Accounts','Calendar');
		foreach($allowedModules as $modules){
			$moduleModel = Vtiger_Module_Model::getInstance($modules);
			if (($userPrivModel->isAdminUser() ||
					$userPrivModel->hasGlobalReadPermission() ||
					$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
				$cardScannerModules[] = array('moduleName'=>$modules,'moduleLabel'=> vtranslate($moduleModel->get('label'),$modules),'createAction'=>$createAction
							);
			}
		}
		$response = new Webapp_API_Response();
		$response->setResult($cardScannerModules);
		return $response;
	}
}