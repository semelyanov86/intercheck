<?php

class Webapp_WS_MyListModules extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		$current_user = $this->getActiveUser();
		$listresult = vtws_listtypes(null,$current_user);
		$menuModelsList = Vtiger_Menu_Model::getAll(true);
		$presence = array('0', '2');
		$modules = array();
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$restrictedModule = array('Webapp','Rss','Portal','RecycleBin','ExtensionStore','CTPushNotification','EmailTemplates','CTAttendance');
		foreach($menuModelsList as $moduleName => $moduleModel){
			if (empty($moduleModel))
					continue;
			if (in_array($moduleModel->get('name'),$restrictedModule))
					continue;
			if (($userPrivModel->isAdminUser() ||
						$userPrivModel->hasGlobalReadPermission() ||
						$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				$modules[] = array('value'=>trim($moduleName),'label'=>vtranslate($moduleModel->get('label'),$moduleName));
			}

		}
		$response = new Webapp_API_Response();
		$response->setResult($modules);
		return $response;
	}
}
