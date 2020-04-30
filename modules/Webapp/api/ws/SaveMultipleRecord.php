<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_SaveMultipleRecord extends Webapp_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Webapp_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$module = trim($request->get('module'));
		$valuesJSONString =  $request->get('values');
		
		$values = "";
		if(!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}
		
		$response = new Webapp_API_Response();
		
		if (empty($values)) {
			$message = vtranslate('Values cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		
		try {
			
			foreach($values as $value){
				$this->recordValues = array();
				if($value['lastname']!='' && $value['mobile']!='' && ($module == 'Contacts' || $module == 'Leads')){
					
					global $adb;
					$mobile = $value['mobile'];
					if($module == 'Contacts'){
						
						$getModuleParent = $adb->pquery("SELECT * FROM vtiger_contactdetails
							INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
							INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
					
					 WHERE vtiger_crmentity.deleted = 0 and (vtiger_contactdetails.phone LIKE '%$mobile%' or  vtiger_contactdetails.mobile LIKE '%$mobile%' or vtiger_contactsubdetails.otherphone LIKE '%$mobile%' )", array());
					}elseif($module == 'Leads'){
						$getModuleParent = $adb->pquery("SELECT * FROM vtiger_leadaddress
							INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leadaddress.leadaddressid
							
					
					 WHERE vtiger_crmentity.deleted = 0 and (vtiger_leadaddress.phone LIKE '%$mobile%' or vtiger_leadaddress.mobile LIKE '%$mobile%' )", array());
						
					}
					$findrecords = $adb->num_rows($getModuleParent);
					if($findrecords == 0){
						// Set the modified values
						foreach($value as $name => $value1) {
							$this->recordValues[$name] = $value1;
						}
						$this->recordValues['assigned_user_id'] = '19x'.$current_user->id;
						$this->recordValues = vtws_create($module, $this->recordValues, $current_user);
					}
				}
			}
			$message = vtranslate('Records Sync Successfully','Webapp');
			$userData[] = array('message'=>$message);
		    $response->setResult($userData);		
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}
	
}
