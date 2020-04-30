<?php
/*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

class Webapp_WS_ConvertLead extends Webapp_WS_FetchRecordWithGrouping {

	function process(Webapp_API_Request $request) {
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$roleid = $current_user->roleid;
		$record = explode('x',$request->get('record'));
		$recordId = $record[1];
		$response = new Webapp_API_Response();
		if (empty($recordId)) {
			$message = vtranslate('record cannot be empty!','Webapp');
			$response->setError(404, $message);
			return $response;
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$LEAD_COMPANY_NAME = $recordModel->get('company');
		$Fields = $recordModel->getConvertLeadFields();
		$moduleModel = $recordModel->getModule();
		$assignedToFieldModel = $moduleModel->getField('assigned_user_id');
		$assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
		$assigned_user_id = '19x'.$recordModel->get('assigned_user_id');

		$usersRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get('assigned_user_id'),'Users');

		$potentialModuleModel = Vtiger_Module_Model::getInstance('Potentials');
		$accountField = Vtiger_Field_Model::getInstance('related_to', $potentialModuleModel);
		$contactField = Vtiger_Field_Model::getInstance('contact_id', $potentialModuleModel);
		$ACCOUNT_FIELD_MODEL = $accountField;
		$CONTACT_FIELD_MODEL = $contactField;
		$contactsModuleModel = Vtiger_Module_Model::getInstance('Contacts');
		$accountField = Vtiger_Field_Model::getInstance('account_id', $contactsModuleModel);
		$CONTACT_ACCOUNT_FIELD_MODEL = $accountField;

		if(!$Fields['Accounts'] && !$Fields['Contacts']){
			$message = vtranslate('LBL_CONVERT_LEAD_ERROR',"Leads");
			$response->setError(413,$message);
			return $response;
		}else{
			$convertLeadFields = array();
			$count = 0;
			foreach($Fields as $index => $value){
				 if($index == 'Contacts' || ($LEAD_COMPANY_NAME != '' && $index == 'Accounts') || ($CONTACT_ACCOUNT_FIELD_MODEL && $CONTACT_ACCOUNT_FIELD_MODEL->isMandatory() && $index != 'Potentials')){
				 	$convertLeadFields[$count] = array('module'=>$index,'moduleLabel'=>vtranslate($index,$index),'selected'=>true);
				 }else{
				 	$convertLeadFields[$count] = array('module'=>$index,'moduleLabel'=>vtranslate($index,$index),'selected'=>false);
				 }
				
				foreach($value as $key => $fields){
					$result = $adb->pquery("SELECT fieldtype FROM vtiger_ws_fieldtype WHERE uitype = ?",array($fields->get('uitype')));
					$fieldtype = $adb->query_result($result, 0, 'fieldtype');
					if(!$fieldtype){
						$typeofdata = explode('~',$fields->get('typeofdata'));
						switch($typeofdata[0]){
							case 'T': $fieldtype = "time";
							case 'D': $fieldtype = "date";
							case 'DT': $fieldtype =  "date";
							case 'E': $fieldtype =  "email";
							case 'N':
							case 'NN': $fieldtype = "double";
							case 'P': $fieldtype = "password";
							case 'I': $fieldtype = "integer";
							case 'V':
							default: $fieldtype = "string";
						}
					}
					if($fields->get('name') == 'closingdate'){
						$fieldtype = 'date';
					}
					if($fields->get('uitype') == 15 || $fields->get('uitype') == 33){
						$picklistValues1 = array();
						$picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($fields->get('name'),$roleid);
						foreach($picklistValues as $pvalue){
							$picklistValues1[] = array('value'=>$pvalue, 'label'=>vtranslate($pvalue,$module));
						}
						$block = $fields->get('block');
						$convertLeadFields[$count]['fields'][] = array('name'=>$fields->get('name'),'label'=>$fields->get('label'),'type'=>array('picklistValues'=>$picklistValues1,'name'=>$fieldtype,'defaultvalue'=>$fields->get('fieldvalue')),'mandatory'=>$fields->isMandatory(),'defaultvalue'=>$fields->get('fieldvalue'), 'isunique'=>$fields->get('isunique'), 'readonly'=>$fields->get('readonly'),'displaytype'=>$fields->get('displaytype'),'typeofdata'=>$fields->get('typeofdata'),'uitype'=>$fields->get('uitype'),'summaryfield'=>$fields->get('summaryfield'),'presence'=>$fields->get('presence'),'blockid'=>$block->id,'blockname'=>vtranslate($block->label,$index));
					}else{
						$block = $fields->get('block');
						$convertLeadFields[$count]['fields'][] = array('name'=>$fields->get('name'),'label'=>$fields->get('label'),'type'=>array('name'=>$fieldtype,'defaultvalue'=>$fields->get('fieldvalue')),'mandatory'=>$fields->isMandatory(),'defaultvalue'=>$fields->get('fieldvalue'),'isunique'=>$fields->get('isunique'),'readonly'=>$fields->get('readonly'),'displaytype'=>$fields->get('displaytype'),'typeofdata'=>$fields->get('typeofdata'),'uitype'=>$fields->get('uitype'),'summaryfield'=>$fields->get('summaryfield'),'presence'=>$fields->get('presence'),'blockid'=>$block->id,'blockname'=>vtranslate($block->label,$index));
					}
					
				}
				$count++;
			}

			$result = $adb->pquery("SELECT fieldtype FROM vtiger_ws_fieldtype WHERE uitype = ?",array($assignedToFieldModel->get('uitype')));
			$fieldtype = $adb->query_result($result, 0, 'fieldtype');
			$block = $assignedToFieldModel->get('block');

			$userName = html_entity_decode($usersRecordModel->get('first_name').' '.$usersRecordModel->get('last_name'),ENT_QUOTES,'UTF-8');

			$convertLeadFields[$count]['fields'][0] = array('name'=>$assignedToFieldModel->get('name'),'label'=>$assignedToFieldModel->get('label'),'type'=>array('name'=>$fieldtype,'defaultvalue'=>$assignedToFieldModel->get('defaultvalue')),'mandatory'=>$assignedToFieldModel->isMandatory(),'defaultvalue'=>array('label'=>$userName,'value'=>$assigned_user_id),'isunique'=>$assignedToFieldModel->get('isunique'),'readonly'=>$assignedToFieldModel->get('readonly'),'displaytype'=>$assignedToFieldModel->get('displaytype'),'typeofdata'=>$assignedToFieldModel->get('typeofdata'),'uitype'=>$assignedToFieldModel->get('uitype'),'summaryfield'=>$assignedToFieldModel->get('summaryfield'),'presence'=>$assignedToFieldModel->get('presence'),'blockid'=>$block->id,'blockname'=>vtranslate($block->label,'Leads'));

			$convertLeadFields[$count]['fields'][1] = array("name"=>"transferModule","label"=>vtranslate('LBL_TRANSFER_RELATED_RECORD', 'Leads'),"type"=> array('name'=>"boolean","defaultvalue"=>""),								  "mandatory"=> true,
												 "isunique"=> false,
							                     "nullable"=> true,
							                     "editable"=>true,
							                     "default"=> "on",
							                     "headerfield"=> "0",
							                     "summaryfield"=> "0",
							                     "uitype"=> "56",
							                     "typeofdata"=> "C~O",
							                     "displaytype"=> "1",
							                     "quickcreate"=> "1");
			foreach($Fields as $modulename => $value){
				if($modulename != 'Potentials'){
					$transferModule = array("label"=>vtranslate("SINGLE_".$modulename,$modulename),'value'=>$modulename,"selected"=>false);
					if($Fields['Contacts'] && $modulename == 'Contacts'){
						$transferModule['selected'] = true;
					}else if(!$Fields['Contacts'] && $modulename =="Accounts"){
						$transferModule['selected'] = true;
					}
					$convertLeadFields[$count]['fields'][1]['type']['values'][] = $transferModule;
					
				}	
			}
			
			$response->setResult($convertLeadFields);
			return $response;
		}
		
	}

}