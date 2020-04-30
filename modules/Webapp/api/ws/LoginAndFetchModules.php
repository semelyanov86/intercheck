<?php

include_once dirname(__FILE__) . '/Login.php';
require_once('include/utils/utils.php'); 

class Webapp_WS_LoginAndFetchModules extends Webapp_WS_Login {
	
	function postProcess(Webapp_API_Response $response) {
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		
		if ($current_user) {
			$results = $response->getResult();
			$results['modules'] = $this->getAllVisibleModules();
			
			//$results['AccessModule'] = array('CommentsModule'=>$CommentsModule,'ActivitiesModule'=>$ActivitiesModule,'SummaryModule'=>$SummaryModule);
			$response->setResult($results);
		}
	}
	

	public function getAllVisibleModules() {
		global $adb;
		$CommentsModule = $ActivitiesModule = $SummaryModule = array();
		$query = "SELECT vtiger_tab.name, vtiger_tab.tabid FROM vtiger_relatedlists INNER JOIN vtiger_tab ON vtiger_relatedlists.tabid = vtiger_tab.tabid where vtiger_relatedlists.presence = 0 AND vtiger_relatedlists.label=?";
		$params = array("ModComments");
		$result = $adb->pquery($query , $params);
		$numrows = $adb->num_rows($result);
		$CommentsModule = array();
		for($i=0;$i<$numrows;$i++){
			$CommentsModule[] = $adb->query_result($result,$i,'name');
		}
		
		$query2 = "SELECT vtiger_tab.name, vtiger_tab.tabid FROM vtiger_relatedlists INNER JOIN vtiger_tab ON vtiger_relatedlists.tabid = vtiger_tab.tabid where vtiger_relatedlists.presence = 0 AND vtiger_relatedlists.label=?";
		$params2 = array("Activities");
		$result2 = $adb->pquery($query2 , $params2);
		$numrows = $adb->num_rows($result2);
		$ActivitiesModule = array();
		for($i=0;$i<$numrows;$i++){
			$ActivitiesModule[] = $adb->query_result($result2,$i,'name');
		}
		
		$query2 = "SELECT vtiger_tab.name, vtiger_tab.tabid FROM vtiger_relatedlists INNER JOIN vtiger_tab ON vtiger_relatedlists.tabid = vtiger_tab.tabid where vtiger_relatedlists.presence = 0 AND vtiger_relatedlists.label=?";
		$params2 = array("Activities");
		$result2 = $adb->pquery($query2 , $params2);
		$numrows = $adb->num_rows($result2);
		$ActivitiesModule = array();
		for($i=0;$i<$numrows;$i++){
			$ActivitiesModule[] = $adb->query_result($result2,$i,'name');
		}
		
		$query3 = "SELECT * FROM  `vtiger_tab` WHERE  `isentitytype` =1 AND  `presence` =0";
		$result3 = $adb->pquery($query3 ,array());
		$numrows = $adb->num_rows($result3);
		$SummaryModule = array();
		for($i=0;$i<$numrows;$i++){
			$Module = $adb->query_result($result3,$i,'name');
			$moduleModel = Vtiger_Module_Model::getInstance($Module); 
			if($moduleModel->isSummaryViewSupported()) {
				$SummaryModule[] = $Module;
			}else{
				continue;
			}
		}
		$inventoryModules = getInventoryModules();
		$current_user = $this->getActiveUser();
		$listresult = vtws_listtypes(null,$current_user);
		$menuModelsList = Vtiger_Menu_Model::getAll(true);
		$newMenuModulesList = array();
		$modules[vtranslate('Other Modules','Vtiger')]=$modules[vtranslate('LBL_TOOLS','Vtiger')]=$modules[vtranslate('LBL_PROJECT','Vtiger')]=$modules[vtranslate('LBL_SUPPORT','Vtiger')]=$modules[vtranslate('LBL_INVENTORY','Vtiger')]=$modules[vtranslate('LBL_SALES','Vtiger')]=$modules[vtranslate('LBL_MARKETING','Vtiger')]= array();
		$newMenuList = array_keys($modules);
		foreach($newMenuList as $key => $value){
			$newMenuModulesList[$key]['tab_key'] = $key;
			$newMenuModulesList[$key]['tab_name'] = $value;
			$presence = array('0', '2');
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT * FROM vtiger_app2tab WHERE visible = ? ORDER BY appname,sequence', array(1));
			$count = $db->num_rows($result);
			$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$appname = $db->query_result($result, $i, 'appname');
					if(vtranslate('LBL_'.$appname,'Vtiger') == $value){
					$tabid = $db->query_result($result, $i, 'tabid');
					$sequence = $db->query_result($result, $i, 'sequence');
					$moduleName = getTabModuleName($tabid);
					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
					$restrictedModule = array('Webapp','Rss','Portal','RecycleBin','ExtensionStore','CTPushNotification','EmailTemplates','CTAttendance','MailManager');
					if (empty($moduleModel))
						continue;
					if (in_array($moduleModel->get('name'),$restrictedModule))
						continue;
					$moduleModel->set('app2tab_sequence', $sequence);
					if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
						
						$view = 'List';
						$module = $moduleModel->get('name');
						$ModulesArray = array('SMSNotifier','PBXManager','CTPushNotification','CTCalllog','CTAttendance');
						if(in_array($module,$ModulesArray)){
							$QuickCreateAction = false;
							$editAction = false;
							$createAction = false;
						}else{
							$QuickCreateAction = $moduleModel->isQuickCreateSupported();
							$editAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'EditView');
							$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
						}					
						
						
						$singular = vtranslate($moduleModel->get('name'),$module);
						if($appname == ''){
							$appname ='Other Modules';
						}
						$appname = vtranslate('LBL_'.$appname,'Vtiger');
						//allow access false when user type Free
						$restrictedModules = array();
						if(in_array($module,$restrictedModules)){     
							$module_access = false;
						}else{
							$module_access = true;
						}
						if(in_array($module,$CommentsModule)){
							$isCommentModule = true;
						}else{
							$isCommentModule = false;
						}
						if(in_array($module,$ActivitiesModule)){
							$isActivityModule = true;
						}else{
							$isActivityModule = false;
						}
						if(in_array($module,$SummaryModule)){
							$isSummerymodule = true;
						}else{
							$isSummerymodule = false;
						}
						if(in_array($module, $inventoryModules)) {
							$isInventoryModule = true;
						}else{
							$isInventoryModule = false;
						}
						$newMenuModulesList[$key]['modules_list'][] = array(
							'id'=> $moduleModel->get('id'),
							'name' => trim($moduleModel->get('name')),
							'isEntity' => $moduleModel->get('isentitytype'),
							'label' => vtranslate($moduleModel->get('label'),$module),
							'singular' => $singular,
							'parent' => $appname,
							'view' => $view,
							'img_url' => Webapp_WS_Utils::getModuleURL($moduleModel->get('name')),
							'module_access' => $module_access,
							'createAction' => $createAction,
							'editAction' => $editAction,
							'QuickCreateAction'=>$QuickCreateAction,
							'isCommentModule'=>$isCommentModule,
							'isActivityModule'=>$isActivityModule,
							'isSummerymodule'=>$isSummerymodule,
							'isInventoryModule'=>$isInventoryModule
							);
					}	
				}
			}
				if($value == 'Other Modules'){

					if(in_array('MailManager',$CommentsModule)){
						$isCommentModule = true;
					}else{
						$isCommentModule = false;
					}
					if(in_array('MailManager',$ActivitiesModule)){
						$isActivityModule = true;
					}else{
						$isActivityModule = false;
					}
					if(in_array('MailManager',$SummaryModule)){
						$isSummerymodule = true;
					}else{
						$isSummerymodule = false;
					}
					$moduleModel = Vtiger_Module_Model::getInstance('MailManager');
					$QuickCreateAction = false;
					if(array_key_exists("MailManager",$menuModelsList) && ($userPrivModel->isAdminUser() ||
								$userPrivModel->hasGlobalReadPermission() ||
								$userPrivModel->hasModulePermission($menuModelsList['MailManager']->get('id'))) && in_array($menuModelsList['MailManager']->get('presence'), $presence) ){
						$editAction = $userPrivModel->hasModuleActionPermission($menuModelsList['MailManager']->get('id'), 'EditView');
						$createAction = $userPrivModel->hasModuleActionPermission($menuModelsList['MailManager']->get('id'), 'CreateView');
						$newMenuModulesList[$key]['modules_list'][] = array(
						'id'=> $menuModelsList['MailManager']->get('id'),
						'name' => $menuModelsList['MailManager']->get('name'),
						'isEntity' => $menuModelsList['MailManager']->get('isentitytype'),
						'label' => vtranslate($menuModelsList['MailManager']->get('label'),'MailManager'),
						'singular' => $moduleModel->get('label'),
						'parent' => 'Other Modules',
						'view' => 'List',
						'img_url' => Webapp_WS_Utils::getModuleURL('MailManager'),
						'module_access' => true,
						'createAction' => false,
						'editAction' => false,
						'QuickCreateAction'=>$QuickCreateAction,
						'isCommentModule'=>$isCommentModule,
						'isActivityModule'=>$isActivityModule,
						'isSummerymodule'=>$isSummerymodule,
						'isInventoryModule'=>false
						);
					}
					if(in_array('CTUserFilterView',$CommentsModule)){
						$isCommentModule = true;
					}else{
						$isCommentModule = false;
					}
					if(in_array('CTUserFilterView',$ActivitiesModule)){
						$isActivityModule = true;
					}else{
						$isActivityModule = false;
					}
					if(in_array('CTUserFilterView',$SummaryModule)){
						$isSummerymodule = true;
					}else{
						$isSummerymodule = false;
					}
					
					$moduleModel = Vtiger_Module_Model::getInstance('CTUserFilterView');
					$QuickCreateAction = false;
					if(($userPrivModel->isAdminUser() ||
								$userPrivModel->hasGlobalReadPermission() ||
								$userPrivModel->hasModulePermission($moduleModel->get('id'))) && $moduleModel->get('presence') == 0){
						$editAction = $userPrivModel->hasModuleActionPermission($moduleModel->get('id'), 'EditView');
						$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->get('id'), 'CreateView');
						$newMenuModulesList[$key]['modules_list'][] = array(
						'id'=> $moduleModel->get('id'),
						'name' => $moduleModel->get('name'),
						'isEntity' => $moduleModel->get('isentitytype'),
						'label' => vtranslate($moduleModel->get('label'),'CTUserFilterView'),
						'singular' => $moduleModel->get('label'),
						'parent' => 'Other Modules',
						'view' => 'List',
						'img_url' => Webapp_WS_Utils::getModuleURL('CTUserFilterView'),
						'module_access' => true,
						'createAction' => false,
						'editAction' => false,
						'QuickCreateAction'=>$QuickCreateAction,
						'isCommentModule'=>$isCommentModule,
						'isActivityModule'=>$isActivityModule,
						'isSummerymodule'=>$isSummerymodule,
						'isInventoryModule'=>false
						);
					}
				
					if(in_array('Documents',$CommentsModule)){
						$isCommentModule = true;
					}else{
						$isCommentModule = false;
					}
					if(in_array('Documents',$ActivitiesModule)){
						$isActivityModule = true;
					}else{
						$isActivityModule = false;
					}
					if(in_array('Documents',$SummaryModule)){
						$isSummerymodule = true;
					}else{
						$isSummerymodule = false;
					}
					$moduleModel = Vtiger_Module_Model::getInstance('Documents');
					$QuickCreateAction = $moduleModel->isQuickCreateSupported();
					if(array_key_exists("Documents",$menuModelsList) && ($userPrivModel->isAdminUser() ||
								$userPrivModel->hasGlobalReadPermission() ||
								$userPrivModel->hasModulePermission($menuModelsList['Documents']->get('id'))) && in_array($menuModelsList['Documents']->get('presence'), $presence)){
						$editAction = $userPrivModel->hasModuleActionPermission($menuModelsList['Documents']->get('id'), 'EditView');
						$createAction = $userPrivModel->hasModuleActionPermission($menuModelsList['Documents']->get('id'), 'CreateView');
						$newMenuModulesList[$key]['modules_list'][] = array(
						'id'=> $menuModelsList['Documents']->get('id'),
						'name' => $menuModelsList['Documents']->get('name'),
						'isEntity' => $menuModelsList['Documents']->get('isentitytype'),
						'label' => vtranslate($menuModelsList['Documents']->get('label'),'Documents'),
						'singular' => $moduleModel->get('label'),
						'parent' => 'Other Modules',
						'view' => 'List',
						'img_url' =>  Webapp_WS_Utils::getModuleURL('Documents'),
						'module_access' => true,
						'createAction' => $createAction,
						'editAction' => $editAction,
						'QuickCreateAction'=>$QuickCreateAction,
						'isCommentModule'=>$isCommentModule,
						'isActivityModule'=>$isActivityModule,
						'isSummerymodule'=>$isSummerymodule,
						'isInventoryModule'=>false
						);
					}
					if(in_array('Calendar',$CommentsModule)){
						$isCommentModule = true;
					}else{
						$isCommentModule = false;
					}
					if(in_array('Calendar',$ActivitiesModule)){
						$isActivityModule = true;
					}else{
						$isActivityModule = false;
					}
					if(in_array('Calendar',$SummaryModule)){
						$isSummerymodule = true;
					}else{
						$isSummerymodule = false;
					}
					$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
					$QuickCreateAction = $moduleModel->isQuickCreateSupported();
					if(array_key_exists("Calendar",$menuModelsList) && ($userPrivModel->isAdminUser() ||
								$userPrivModel->hasGlobalReadPermission() ||
								$userPrivModel->hasModulePermission($menuModelsList['Calendar']->get('id'))) && in_array($menuModelsList['Calendar']->get('presence'), $presence)){
						$editAction = $userPrivModel->hasModuleActionPermission($menuModelsList['Calendar']->get('id'), 'EditView');
						$createAction = $userPrivModel->hasModuleActionPermission($menuModelsList['Calendar']->get('id'), 'CreateView');
						$newMenuModulesList[$key]['modules_list'][] = array(
						'id'=> $menuModelsList['Calendar']->get('id'),
						'name' => $menuModelsList['Calendar']->get('name'),
						'isEntity' => $menuModelsList['Calendar']->get('isentitytype'),
						'label' => vtranslate($menuModelsList['Calendar']->get('label'),'Calendar'),
						'singular' => 'Task',
						'parent' => 'Other Modules',
						'view' => 'Calendar',
						'img_url' => Webapp_WS_Utils::getModuleURL('Calendar'),
						'module_access' => true,
						'createAction' => $createAction,
						'editAction' => $editAction,
						'QuickCreateAction'=>$QuickCreateAction,
						'isCommentModule'=>$isCommentModule,
						'isActivityModule'=>$isActivityModule,
						'isSummerymodule'=>$isSummerymodule,
						'isInventoryModule'=>false
						);
					}
				}
		}
		}
		$newModulesList = array();
		foreach($newMenuModulesList as $key => $value){
			if(count($value['modules_list']) > 0){
				$newModulesList[] =  $value;
			}else{
				unset($newMenuModulesList[$key]);
			}
		}
		return $newModulesList;
	}	
}
