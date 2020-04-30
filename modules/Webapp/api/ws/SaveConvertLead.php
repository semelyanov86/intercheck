<?php

vimport('~~/include/Webservices/ConvertLead.php');
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

class Webapp_WS_SaveConvertLead extends Webapp_WS_FetchRecordWithGrouping {

	function process(Webapp_API_Request $request) {
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$roleid = $current_user->roleid;
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->get('module');
		$record = explode('x',$request->get('record'));
		$recordId = $record[1];
		if (empty($moduleName)) {
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		if (empty($record)) {
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		$valuesJSONString =  $request->get('values');
	   
		$values = "";
		if(!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}

		if (empty($values)) {
			$message = vtranslate('Values cannot be empty!','Webapp');
			throw new WebServiceException(404,$message);
		}

		$modules = Zend_Json::decode($request->get('modules'));
		
		$assignId = $values['assigned_user_id'];

		$entityValues = array();
		$entityValues['transferRelatedRecordsTo'] = $values['transferModule'];
		$entityValues['assignedTo'] = $assignId;
		$entityValues['leadId'] =  vtws_getWebserviceEntityId($moduleName, $recordId);
		$entityValues['imageAttachmentId'] = $values['imageAttachmentId'];

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$convertLeadFields = $recordModel->getConvertLeadFields();

		$availableModules = array('Accounts', 'Contacts', 'Potentials');
		foreach ($availableModules as $module) {
			if(vtlib_isModuleActive($module)&& in_array($module, $modules)) {
				$entityValues['entities'][$module]['create'] = true;
				$entityValues['entities'][$module]['name'] = $module;

				// Converting lead should save records source as CRM instead of WEBSERVICE
				$entityValues['entities'][$module]['source'] = 'CRM';
				foreach ($convertLeadFields[$module] as $fieldModel) {
					$fieldName = $fieldModel->getName();
					$fieldValue = $values[$fieldName];

					//Potential Amount Field value converting into DB format
					if ($fieldModel->getFieldDataType() === 'currency') {
					if($fieldModel->get('uitype') == 72){
						// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
						$fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue, null, true);
					} else {
						$fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue);
					}
					} elseif ($fieldModel->getFieldDataType() === 'date') {
						$fieldValue = DateTimeField::convertToDBFormat($fieldValue);
					} elseif ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
						$ids = vtws_getIdComponents($fieldValue);
						if (count($ids) === 1) {
							$fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
						}
					}
					$entityValues['entities'][$module][$fieldName] = $fieldValue;
				}
			}
		}

		$result = vtws_convertlead($entityValues, $currentUser);
		
		if(!empty($result['Accounts'])) {
			$accountIdComponents = vtws_getIdComponents($result['Accounts']);
			$accountId = $accountIdComponents[1];
		}
		if(!empty($result['Contacts'])) {
			$contactIdComponents = vtws_getIdComponents($result['Contacts']);
			$contactId = $contactIdComponents[1];
		}

		if(!empty($accountId)) {
			$transferModule = "Accounts";
			$wsId = Webapp_WS_Utils::getEntityModuleWSId($transferModule);
			$transferRecordId = $wsId.'x'.$accountId;
		} elseif (!empty($contactId)) {
			$transferModule = "Contacts";
			$wsId = Webapp_WS_Utils::getEntityModuleWSId($transferModule);
			$transferRecordId = $wsId.'x'.$contactId;
		}	

		$response = new Webapp_API_Response();
		$response->setResult(array('transferModule'=>$transferModule,'recordid'=>$transferRecordId));
		return $response;
	}
}