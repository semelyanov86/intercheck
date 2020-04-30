<?php

include_once dirname(__FILE__) . '/FetchRecord.php';

class Webapp_WS_GlobalSearch extends Webapp_WS_FetchRecord {
	
	function process(Webapp_API_Request $request) {
		global $adb;
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$searchKey = trim($request->get('value'));
		if(empty($searchKey)){
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		$searchModule = false;
			
		if($request->get('searchModule')) {
			$searchModule = trim($request->get('searchModule'));
		}
		$matchingRecordsList = array();
		$matchingRecords =  Vtiger_Record_Model::getSearchResult($searchKey, $searchModule);
		$count = 0;
		foreach ($matchingRecords as $module => $recordModelsList) {
			$noofrecords = count($recordModelsList);
			$matchingRecordsList[$count]['TotalRecords'] = $noofrecords;
			$img_url = Webapp_WS_Utils::getModuleURL($module);
			$matchingRecordsList[$count]['img_url'] = $img_url;
			foreach($recordModelsList as $key => $value){
				$crmid = $value->get('crmid');
				$label = $value->get('label');
				$createdtime = Vtiger_Util_Helper::formatDateDiffInStrings($value->get('createdtime'));
				if($module == 'Calendar' || $module == 'Events'){
					$EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$crmid)); 
					if($adb->num_rows($EventTaskQuery) > 0){
						$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
						$recordid = $wsid.'x'.$crmid;
						$recordModule = 'Calendar';
					}else{
						$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
						$recordid = $wsid.'x'.$crmid;
						$recordModule = 'Events';
					}
				}else{
					$recordModule = $module;
					$wsid = Webapp_WS_Utils::getEntityModuleWSId($recordModule);
					$recordid = $wsid.'x'.$crmid;
				}
				$moduleLabel = vtranslate($recordModule,$recordModule);
				$recordArray = array('record'=>$recordid,'label'=>$label,'createdtime'=>$createdtime);
				$matchingRecordsList[$count]['records'][] = $recordArray;
			}
			$matchingRecordsList[$count]['module'] = $module;
			$matchingRecordsList[$count]['moduleLabel'] = vtranslate($module,$module);
			$count++;
		}
		$response = new Webapp_API_Response();
		if(count($matchingRecordsList) == 0){
			$message = vtranslate('No records found for','Webapp').' "'.$searchKey.'"';
			$response->setResult(array('search_results'=>$matchingRecordsList,'code'=>404,'message'=>$message));
		}else{
			$response->setResult(array('search_results'=>$matchingRecordsList,'message'=>''));
		}
		return $response;
		
	}

}
