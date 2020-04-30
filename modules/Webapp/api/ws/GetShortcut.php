<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_GetShortcut extends Webapp_WS_FetchRecordWithGrouping {
	public $totalQuery = "";
	public $totalParams = array();
	function process(Webapp_API_Request $request) {
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$module = trim($request->get('module'));
		$shortcutType = trim($request->get('shortcutType'));
		$index = trim($request->get('index'));
		$size = trim($request->get('size'));
		$limit = ($index*$size) - $size;
		$shortcutdata = array();
		if($shortcutType == 'filter'){
			$query = "SELECT webapp_filter_shortcut.shortcutid,webapp_filter_shortcut.shortcutname,webapp_filter_shortcut.filterid,vtiger_customview.viewname,webapp_filter_shortcut.search_value,webapp_filter_shortcut.fieldname,webapp_filter_shortcut.userid,webapp_filter_shortcut.module,webapp_filter_shortcut.createdtime FROM webapp_filter_shortcut INNER JOIN vtiger_customview ON vtiger_customview.cvid = webapp_filter_shortcut.filterid WHERE webapp_filter_shortcut.userid = ? ORDER BY webapp_filter_shortcut.createdtime DESC ";
			$this->totalQuery = $query;
			$this->totalParams = array($current_user->id);
			if($index && $size){
				$query .= sprintf(" LIMIT %s, %s", $limit, $size);
			}
			
			$params = array($current_user->id);
			$result = $adb->pquery($query,$params);
			for($i=0;$i<$adb->num_rows($result);$i++){
				$shortcutid = $adb->query_result($result,$i,'shortcutid');
				$shortcutname = $adb->query_result($result,$i,'shortcutname');
				$filterid = $adb->query_result($result,$i,'filterid');
				$viewname = $adb->query_result($result,$i,'viewname');
				$search_value = $adb->query_result($result,$i,'search_value');
				$fieldname = $adb->query_result($result,$i,'fieldname');
				$module = $adb->query_result($result,$i,'module');
				$tabid = getTabid($module);
				$fieldlabel = '';
				if($fieldname != ''){
					$fieldResult = $adb->pquery("SELECT fieldlabel FROM vtiger_field WHERE fieldname = ? AND tabid = ? ",array($fieldname,$tabid));
					$fieldlabel = $adb->query_result($fieldResult,0,'fieldlabel');
					$fieldlabel = vtranslate($fieldlabel,$module);
				}
				$shortcutdata[] = array('shortcutid'=>$shortcutid,'shortcutname'=>$shortcutname,'filterid'=>$filterid,'filtername'=>$viewname,'fieldname'=>$fieldname,'fieldlabel'=>$fieldlabel,'search_value'=>$search_value,'module'=>$module,'moduleLabel'=>vtranslate($module,$module));
			}			

		}
		if($shortcutType == 'record'){
			$query = "SELECT webapp_record_shortcut.shortcutid,webapp_record_shortcut.shortcutname,webapp_record_shortcut.recordid,webapp_record_shortcut.userid,webapp_record_shortcut.module,webapp_record_shortcut.createdtime FROM webapp_record_shortcut INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = webapp_record_shortcut.recordid WHERE vtiger_crmentity.deleted = 0 AND webapp_record_shortcut.userid = ?  ORDER BY webapp_record_shortcut.createdtime DESC ";
			$this->totalQuery = $query;
			$this->totalParams = array($current_user->id);
			if($index && $size){
				$query .= sprintf(" LIMIT %s, %s", $limit, $size);
			}
			$params = array($current_user->id);
			$result = $adb->pquery($query,$params);
			for($i=0;$i<$adb->num_rows($result);$i++){
				$shortcutid = $adb->query_result($result,$i,'shortcutid');
				$shortcutname = $adb->query_result($result,$i,'shortcutname');
				$recordid = $adb->query_result($result,$i,'recordid');
				$module = $adb->query_result($result,$i,'module');
				$recordLabel = '';
				if($recordid){
					if($module == 'Events'){
						$entityQuery = $adb->pquery("SELECT * FROM vtiger_entityname WHERE modulename = ?",array('Calendar'));
					}else{
						$entityQuery = $adb->pquery("SELECT * FROM vtiger_entityname WHERE modulename = ?",array($module));
					}
					
					$entityField = $adb->query_result($entityQuery,0,'fieldname');
					$entityField_array = explode(',',$entityField);
					$entityField = $entityField_array[0];
					if($module == 'Events'){
						$recordModel = Vtiger_Record_Model::getInstanceById($recordid,'Calendar');
					}else{
						$recordModel = Vtiger_Record_Model::getInstanceById($recordid,$module);
					}
					
					$recordLabel = $recordModel->get($entityField);
				}
				$wsid = Webapp_WS_Utils::getEntityModuleWSId($module);
				$shortcutdata[] = array('shortcutid'=>$shortcutid,'shortcutname'=>$shortcutname,'recordid'=>$wsid.'x'.$recordid,'recordLabel'=>$recordLabel,'module'=>$module,'moduleLabel'=>vtranslate($module,$module));
			}

		}
		$isLast = true;
		if($this->totalQuery != ""){
			$totalResults = $adb->pquery($this->totalQuery,$this->totalParams);
			$totalRecords = $adb->num_rows($totalResults);
			if($totalRecords > $index*$size){
				$isLast = false;	
			}else{
				$isLast = true;
			}
		}

		if(count($shortcutdata) > 0){
				$response = new Webapp_API_Response();
				$response->setResult(array("shortcutdata"=>$shortcutdata,"code"=>1,"message"=>'','isLast'=>$isLast));
		}else{
				$response = new Webapp_API_Response();
				$message = vtranslate('No records found','Webapp');
				$response->setResult(array("shortcutdata"=>$shortcutdata,"code"=>0,"message"=>$message,'isLast'=>$isLast));
		}
		return $response;
	}
}