<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_SaveAjaxRecord extends Webapp_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Webapp_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();

		$module = trim($request->get('module'));

		if(empty($module)){
			$message = vtranslate($module,$module)." ".vtranslate('Module cannot be empty','Webapp');
			throw new WebServiceException(404,$message);
		}

		//start validation for module & fields
		if(!getTabid($module)){
			$message = vtranslate($module,$module)." ".vtranslate('Module does not exists','Webapp');
			throw new WebServiceException(404,$message);
		}
		
		$recordid = trim($request->get('record'));
		$valuesJSONString =  $request->get('values');
		$recordModel = Vtiger_Record_Model::getCleanInstance($module);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();

		$values = "";
		if(!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}

		$response = new Webapp_API_Response();
		
		if (empty($values)) {
			$message = vtranslate('Values cannot be empty!','Webapp');
			$response->setError(404, $message);
			return $response;
		}

		if($module == 'SalesOrder'){
			$values['enable_recurring'] = 0;
			$values['invoicestatus'] = "Created";
		}
		
		try {
			// Retrieve or Initalize
			if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
				$this->recordValues = vtws_retrieve($recordid, $current_user);
			} 
			
			// Set the modified values
			foreach($values as $name => $value) {
				if($name == 'invite_user'){
					continue;
				}
				if($name != 'LineItems') {
					$uitype = $fieldList[$name]->get('uitype');
					if($uitype == 33) {
						if($value){
							$value = implode(' |##| ', $value);
						}
					}else if($uitype == 5){
						$value = Vtiger_Date_UIType::getDBInsertedValue($value);
					}
				}
							
				$this->recordValues[$name] = $value;
			}

			if($module == 'Faq'){
				if(!$this->recordValues['faqcategories']){
					$this->recordValues['faqcategories'] = 'General';
				}
			}
			
			// Update or Create
			if (isset($this->recordValues['id'])) {
				$mode = 'edit';
				if($module == 'ServiceContracts'){
					$record_id = explode('x',$recordid);
					$recordModel = Vtiger_Record_Model::getInstanceById($record_id[1],$module);
					$recordModel->set('mode','edit');
					foreach($this->recordValues as $key => $value){
						if($key == 'assigned_user_id'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else if($key == 'sc_related_to'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else{
							$recordModel->set($key,$value);
						}
					}
					$recordModel->set('id',$record_id[1]);
					$recordModel->save();
					$moduleWSId = Webapp_WS_Utils::getEntityModuleWSId($module);
					$recordId = $recordModel->getId();
					$this->recordValues['id'] = $moduleWSId.'x'.$recordId;
				}else{
					$this->recordValues = vtws_update($this->recordValues, $current_user);
			    }
			} 
			// Update the record id
			$request->set('record', $this->recordValues['id']);
			
			if($request->get('user_lat')!='' && $request->get('user_long')!='' && $request->get('user_id')!=''){
				
				if($this->recordValues['id']!=''){
					global $adb;
					$date_var = date("Y-m-d H:i:s");
					$userId = explode('x', $request->get('user_id'));
					$recordId = explode('x', $this->recordValues['id']);
					$createdtime = $adb->formatDate($date_var, true);
					$query = $adb->pquery("INSERT INTO webapp_userderoute (userid, latitude, longitude, createdtime,action,record) VALUES (?,?,?,?,?,?)", array($userId[1], $request->get('user_lat'), $request->get('user_long'), $createdtime,$mode,$recordId[1]));
					
				}
				
			}
			$result = array('id'=>$this->recordValues['id'],'module'=>$module,'message'=>vtranslate('Record save successfully','Webapp'));
			$response->setResult($result);
			
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}
	
}
