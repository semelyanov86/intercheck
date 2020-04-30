<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';
include_once dirname(__FILE__) . '/SaveRecord.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_CreateActions extends Webapp_WS_SaveRecord {
	protected $recordValues = false;
	protected $recordResults = array();
	
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

		//relation Operation Pramaters
		$sourceRecord = explode('x',$request->get('sourceRecord'));
		$parentRecordId = $sourceRecord[1];
		$userId = $request->get('user');

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

		//Pass TaxType in Inventory Modules
		$supportedModules = array('Activities');
		if(!in_array($module,$supportedModules)){
            $message = vtranslate($module,$module)." ".vtranslate('Not in allowed module list for this route','Webapp');
            throw new WebServiceException(403,$message);
		}
		
		$response = new Webapp_API_Response();

		if (empty($values)) {
			$message = vtranslate('Values cannot be empty!','Webapp');
			$response->setError(404, $message);
			return $response;
		}

		try {
            foreach($values as $fieldValues) {
                $this->recordValues = array();
                // Set the modified values
                foreach($fieldValues as $name => $value) {
                    if($name == 'invite_user'){
                        continue;
                    }
                    if ($name == 'modcommentsid') {
                        continue;
                    }
                    if($name != 'LineItems') {
                        if (!$fieldList[$name]) {
                            $response->setError(1405, 'Field ' . $name . ' does not exist!');
                            return $response;
                        }
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
                $this->recordValues['cf_contacts_id'] = $request->get('sourceRecord');
                if (!isset($this->recordValues['assigned_user_id'])) {
                    $this->recordValues['assigned_user_id'] = $userId;
                }
                $recordRes = vtws_create($module, $this->recordValues, $current_user);
                $this->recordResults[$recordRes['id']] = $recordRes;
            }

			$result = array('data'=>$this->recordResults,'module'=>$module,'message'=>vtranslate('Records saved successfully','Webapp'));
			// Gather response with full details
			$response->setResult($result);
			//$response = parent::process($request);
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}

}
