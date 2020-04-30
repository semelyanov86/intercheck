<?php

include_once dirname(__FILE__) . '/QueryWithGrouping.php';

class Webapp_WS_RelatedRecordsWithGrouping extends Webapp_WS_QueryWithGrouping {
	
	function process(Webapp_API_Request $request) {
		global $current_user, $adb, $currentModule;
		$current_user = $this->getActiveUser();
		
		$response = new Webapp_API_Response();

		$record = trim($request->get('record'));
		$relatedmodule = trim($request->get('relatedmodule'));
		$currentPage = $request->get('page', 0);
		
		// Input validation
		if (empty($record)) {
			$message = vtranslate('Record id is empty','Webapp');
			$response->setError(1001, $message);
			return $response;
		}
		$recordid = vtws_getIdComponents($record);
		$recordid = $recordid[1];
		
		$module = Webapp_WS_Utils::detectModulenameFromRecordId($record);

		// Initialize global variable
		$currentModule = $module;
		
		$functionHandler = Webapp_WS_Utils::getRelatedFunctionHandler($module, $relatedmodule); 
		
		if ($functionHandler) {
			$sourceFocus = CRMEntity::getInstance($module);
			$relationResult = call_user_func_array(	array($sourceFocus, $functionHandler), array($recordid, getTabid($module), getTabid($relatedmodule)) );
			$query = $relationResult['query'];
		
			$querySEtype = "vtiger_crmentity.setype as setype";
			if ($relatedmodule == 'Calendar') {
				$querySEtype = "vtiger_activity.activitytype as setype";
			}
			
			$query = sprintf("SELECT vtiger_crmentity.crmid, $querySEtype %s", substr($query, stripos($query, 'FROM')));
			$queryResult = $adb->query($query);
			
			// Gather resolved record id's
			$relatedRecords = array();
			while($row = $adb->fetch_array($queryResult)) {
				$targetSEtype = $row['setype'];
				if ($relatedmodule == 'Calendar') {
					if ($row['setype'] != 'Task' && $row['setype'] != 'Emails') {
						$targetSEtype = 'Events';
					} else {
						$targetSEtype = $relatedmodule;
					}
				}
				$relatedRecords[] = sprintf("%sx%s", Webapp_WS_Utils::getEntityModuleWSId($targetSEtype), $row['crmid']);
			}
			
			// Perform query to get record information with grouping
			$wsquery = sprintf("SELECT * FROM %s WHERE id IN ('%s');", $relatedmodule, implode("','", $relatedRecords));
			$newRequest = new Webapp_API_Request();
			$newRequest->set('module', $relatedmodule);
			$newRequest->set('query', $wsquery);
			$newRequest->set('page', $currentPage);

			$response = parent::process($newRequest);
		}
		
		return $response;
	}
}
