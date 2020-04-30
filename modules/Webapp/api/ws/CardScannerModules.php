<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class Webapp_WS_CardScannerModules extends Webapp_WS_Controller {

	function process(Webapp_API_Request $request) {
		global $current_user, $adb, $site_URL; // Few core API assumes this variable availability
		$current_user = $this->getActiveUser();
		$mode = $request->get('mode');
		if($mode == 'vcard'){
			$vcardModules =  array();
			$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
			$presence = array('0', '2');
			$allowedModules =  array('Leads','Contacts','Vendors');
			foreach($allowedModules as $modules){
				$moduleModel = Vtiger_Module_Model::getInstance($modules);
				if (($userPrivModel->isAdminUser() ||
						$userPrivModel->hasGlobalReadPermission() ||
						$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
					$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
					$vcardModules[] = array('moduleName'=>$modules,'moduleLabel'=> vtranslate($moduleModel->get('label'),$modules),'createAction'=>$createAction
								);
				}
			}
			$response = new Webapp_API_Response();
			$response->setResult($cardScannerModules);
			return $response;
		}else{
			$cardScannerModules =  array();
			$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
			$presence = array('0', '2');
			$allowedModules =  array('Leads','Potentials','Contacts','Accounts','Vendors');
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
}