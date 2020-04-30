<?php

include_once 'include/Webservices/Retrieve.php';

class Webapp_WS_FetchRecord extends Webapp_WS_Controller {
	
	private $module = false;
	
	protected $resolvedValueCache = array();
	
	protected function detectModuleName($recordid) {
		if($this->module === false) {
			$this->module = Webapp_WS_Utils::detectModulenameFromRecordId($recordid);
		}
		return $this->module;
	}
	
	protected function processRetrieve(Webapp_API_Request $request) {
		global $adb;
		$current_user = $this->getActiveUser();

		$recordid = trim($request->get('record'));
		
		$calendarmodule = explode('x', $request->get('record'));
		
		$record = vtws_retrieve($recordid, $current_user);
		
		$recordId = explode('x', $record['id']);
		
		$getLabelQuery = $adb->pquery("SELECT label from vtiger_crmentity where crmid = ?", array($recordId[1]));
		$recordLabel = trim($adb->query_result($getLabelQuery, 0, 'label'));
		$record['recordLabel'] = $recordLabel;
		
		return $record;
	}
	
	function process(Webapp_API_Request $request) {
		$current_user = $this->getActiveUser();
		$record = $this->processRetrieve($request);
		
		$this->resolveRecordValues($record, $current_user);
		
		$response = new Webapp_API_Response();
		$response->setResult($record);
		
		return $response;
	}
	
	function resolveRecordValues(&$record, $user, $ignoreUnsetFields=false) {
		if(empty($record)) return $record;
		
		$fieldnamesToResolve = Webapp_WS_Utils::detectFieldnamesToResolve(
			$this->detectModuleName($record['id']) );
		
		if(!empty($fieldnamesToResolve)) {
			foreach($fieldnamesToResolve as $resolveFieldname) {
				if ($ignoreUnsetFields === false || isset($record[$resolveFieldname])) {
					$fieldvalueid = $record[$resolveFieldname];
					$fieldvalue = $this->fetchRecordLabelForId($fieldvalueid, $user);
					$record[$resolveFieldname] = array('value' => $fieldvalueid, 'label'=>$fieldvalue);
				}
			}
		}

	}
	
	function fetchRecordLabelForId($id, $user) {
		$value = null;
		
		if (isset($this->resolvedValueCache[$id])) {
			$value = $this->resolvedValueCache[$id];
		} else if(!empty($id)) {
			$value = trim(vtws_getName($id, $user));
			$this->resolvedValueCache[$id] = $value;
		} else {
			$value = $id;
		}
		return $value;
	}
}
