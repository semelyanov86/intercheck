<?php

//include_once dirname(__FILE__) . '/models/Alert.php';

//include_once 'include/data/CRMEntity.php';
include_once 'include/Webservices/Retrieve.php';

class Webapp_WS_RelatedModule extends Webapp_WS_Controller {
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		global $adb, $current_user;
		$current_user = $this->getActiveUser();
		$record = trim($request->get('record'));
		$view = trim($request->get('view'));
		$moduleName = trim($request->get('module'));
		$records = vtws_retrieve($record, $current_user);
		$recordid = explode('x', $record);
		$sql1 = "select tabid,name,tablabel from vtiger_tab where name='".$moduleName."'";
		$result1 = $adb->pquery($sql1,array()); 
		$matchtabid =$adb->query_result($result1,0,'tabid');

		$parentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
		$relatedid = array();
		if($parentModuleModel->isSummaryViewSupported() && $moduleName != 'Users'){
			$relatedid[] = array('linktype'=>'DETAILVIEWTAB','moduleName' => ($moduleName)?$moduleName:'','record' => $recordid[1],'linkKey'=>'Summary','tablabel'=>vtranslate('LBL_SUMMARY', $moduleName),'img_url'=>Webapp_WS_Utils::getModuleURL('summary'));
		}
		$relatedid[] = array('linktype'=>'DETAILVIEWTAB','moduleName' => ($moduleName)?$moduleName:'','record' => $recordid[1],'linkKey'=>'Details','tablabel'=>vtranslate('LBL_DETAILS', $moduleName),'img_url'=>Webapp_WS_Utils::getModuleURL('details'));
		if($parentModuleModel->isTrackingEnabled()) {
			$relatedid[] = array('linktype'=>'DETAILVIEWTAB','moduleName' => ($moduleName)?$moduleName:'','record' => $recordid[1],'linkKey'=>'Updates','tablabel'=>vtranslate('LBL_UPDATES', $moduleName),'img_url'=>Webapp_WS_Utils::getModuleURL('update'));
		}

		if($moduleName == 'Emails' || $moduleName == 'Calendar'){
			$response = new Webapp_API_Response();
			$response->setResult($relatedid);
			return $response;
		}

		$relationModels = $parentModuleModel->getRelations();

		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		foreach($relationModels as $key => $relationModules){
			$tabid = $relationModules->get('tabid');
			$related_tabid = $relationModules->get('related_tabid');
			$relatedmodulelabel = $relationModules->get('label');
			$actions = $relationModules->get('actions');	
			$relatedmoduleName = $relationModules->get('relatedModuleName');
			
			$relatedfunctionname = $relationModules->get('name');
			$relation_id = $relationModules->get('relation_id');
			$relation_label =  $relationModules->get('label');
			$relationfieldid = $relationModules->get('relationfieldid');
			
			$relatedfieldname = "";
			if($relationfieldid != 0){
				$relatedFieldQuery = $adb->pquery('SELECT fieldname FROM vtiger_field WHERE fieldid = ?',array($relationfieldid));
				$relatedfieldname = $adb->query_result($relatedFieldQuery,0,'fieldname');
			}
			$visible = $relationModules->get('presence');
			if($visible != 0){
				continue;
			}
			
			$moduleModel = Vtiger_Module_Model::getInstance($relatedmoduleName);
			$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
			
			if(($userPrivModel->isAdminUser() ||
						$userPrivModel->hasGlobalReadPermission() ||
						$userPrivModel->hasModulePermission($moduleModel->getId()))){
				global $currentModule;
				$currentModule = $moduleName;
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordid[1], $moduleName);
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedmoduleName, $relatedmodulelabel);
				$query = $relationListView->getRelationQuery();
				
				$relatedmodulelabel = vtranslate($relatedmodulelabel, $relatedModuleName, $current_user->language);
				$moduleModel = Vtiger_Module_Model::getInstance($relatedmoduleName);
				$basetableid = $moduleModel->get('basetableid');
				$getfunctionres = $adb->pquery($query,array());
				$numofrows2 = $adb->num_rows($getfunctionres);
				$recordArray = array();
				for($j=0;$j<$numofrows2;$j++){
					$crmid = $adb->query_result($getfunctionres,$j,$basetableid);
					if(Users_Privileges_Model::isPermitted($relatedmoduleName, 'DetailView', $crmid)){
							$recordArray[] = $crmid;
					}
				}
				if ($numofrows2 == '') {
					$sql3 = "SELECT relcrmid FROM vtiger_crmentityrel INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid WHERE crmid='".$recordid[1]."' AND relmodule='".$relatedmoduleName."' AND vtiger_crmentity.deleted = 0";
					$result3 = $adb->pquery($sql3,array());
					$numofrows2 = $adb->num_rows($result3);	
					for($j=0;$j<$numofrows2;$j++){
						$crmid = $adb->query_result($result3,$j,'relcrmid');
						if(Users_Privileges_Model::isPermitted($relatedmoduleName, 'DetailView', $crmid)){
								$recordArray[] = $crmid;
						}
					}
				}
				$numofrows2 = count($recordArray);
				$relatedid[] =  array('linktype'=>'DETAILVIEWRELATED','moduleName' => ($moduleName)?$moduleName:'','record' => $recordid[1],'related_tabid' => ($related_tabid)?$related_tabid:'','relatedmoduleName' => ($relatedmoduleName)?$relatedmoduleName:'','tabid' => ($tabid)?$tabid:'','tablabel'=>($relatedmodulelabel)?$relatedmodulelabel:'','numofrows'=>$numofrows2, 'actions'=>$actions,'createAction'=>$createAction,'relatedfieldname'=>$relatedfieldname,'img_url'=>Webapp_WS_Utils::getModuleURL($relatedmoduleName));
			}
		}
		
	   	if(empty($relatedid)){
	   		$relatedid = array();
	   	}
   		$response = new Webapp_API_Response();
		$response->setResult($relatedid);
		return $response;
   		
	}
		
}
