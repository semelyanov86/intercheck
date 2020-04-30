<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_EmailAction extends Webapp_WS_FetchRecord {
	function process(Webapp_API_Request $request) {
		$mailaction = trim($request->get('mailaction'));
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$response = new Webapp_API_Response();
		$actionlist = array();
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$presence = array('0', '2');
		if ($mailaction == 1) {
			$linkToAvailableActions = MailManager_Relation_View::linkToAvailableActions();
		}else{
			$linkToAvailableActions = MailManager_Relation_View::getCurrentUserMailManagerAllowedModules();
		}
		foreach($linkToAvailableActions as $moduleName) {
			 $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			 if (($userPrivModel->isAdminUser() ||
						$userPrivModel->hasGlobalReadPermission() ||
						$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
			 	$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
			 	if($createAction){
					 if ($moduleName == 'Calendar'){
						 $label = vtranslate("LBL_ADD_CALENDAR", 'MailManager',$current_user->language);
						 $actionlist[] = array('moduleName' => $moduleName, 'label'=>$label); 
						 
						 $label1 = vtranslate("LBL_ADD_EVENTS", 'MailManager',$current_user->language);
						 $actionlist[] = array('moduleName' => 'Events', 'label'=>$label1); 
					 }else{
						  $label = vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager',$current_user->language);
						  $actionlist[] = array('moduleName' => $moduleName, 'label'=>$label); 
					 }
			 	}
			}
		}
		$response->setResult(array('actionlist'=>$actionlist, 'module'=>'MailManager', 'message'=>''));	
		return $response;
	}
}
