<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class Webapp_WS_GetMonthBaseEventCount extends Webapp_WS_Controller {
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$userid = trim($request->get('userid'));
		$month = trim($request->get('month'));
		$year = trim($request->get('year'));
		$response = new Webapp_API_Response();
		$recentEvent_data = array();
		$generator = new QueryGenerator('Events', $current_user);
		$generator->setFields(array('subject','activitytype','location','date_start','due_date','time_start','time_end','location','createdtime','modifiedtime','id'));
		$eventQuery = $generator->getQuery();
		if ($month == '') {
			$message = vtranslate('Month cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		$year = $request->get('year');
		if ($year == '') {
			$message = vtranslate('Year cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		if ($userid == '') {
			$message = vtranslate('Userid cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}
		$startdate = date($year.'-'.$month.'-01');
		$enddate = date($year.'-'.$month.'-t');
		
		$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($startdate . ' 00:00:00');
		$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($enddate . ' 23:59:00');
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$eventQuery .= " AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.smownerid = '".$current_user->id."' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
				AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND  CONCAT(due_date,' ',time_end) >= '" . $startDateTime . "'  AND vtiger_crmentity.deleted =0  ORDER BY vtiger_activity.date_start, time_start DESC";
																																																																																													
		$query = $adb->pquery($eventQuery);
		for($i=0; $i<$adb->num_rows($query); $i++) {
			$startDate = $adb->query_result($query, $i, 'date_start');
			$startTime = $adb->query_result($query, $i, 'time_start');
			$activityid = $adb->query_result($query, $i, 'activityid');

			$endDate = $adb->query_result($query, $i, 'due_date');
			$endTime = $adb->query_result($query, $i, 'time_end');
			if($startDate!=''){
				$startDateTime = $startDate." ".$startTime;
				$UserStartDateTime= Vtiger_Datetime_UIType::getDisplayDateTimeValue($startDateTime);
				$DATE_TIME_COMPONENTS = explode(' ' ,$UserStartDateTime);
				$startDate = $DATE_TIME_COMPONENTS[0];
				$startTime = $DATE_TIME_COMPONENTS[1];

				$endDateTime = $endDate." ".$endTime;
				$UserEndDateTime= Vtiger_Datetime_UIType::getDisplayDateTimeValue($endDateTime);
				$DATE_TIME_COMPONENTS1 = explode(' ' ,$UserEndDateTime);
				$endDate = $DATE_TIME_COMPONENTS1[0];
				$endTime = $DATE_TIME_COMPONENTS1[1];
				if(Users_Privileges_Model::isPermitted('Calendar', 'DetailView', $activityid)){
					//period between start and end time
					if($current_user->date_format == 'dd-mm-yyyy'){
						$format = 'd-m-Y';
					}else if($current_user->date_format == 'mm-dd-yyyy'){
						$format = 'm-d-Y';
					}else{
						$format = 'Y-m-d';
					}
					$midarray=dateCustBet($startDate,$endDate,$format,$year,$month);
					$recentEvent_data=array_merge($recentEvent_data,$midarray);
				}
			}
		}
		
		$recentEvent_data = array_values(array_unique($recentEvent_data));
										  
		if($adb->num_rows($query) == 0){
			$message = vtranslate('No event for this month','Webapp'); 
			$response->setResult(array('GetEventCount'=>[],'date_format'=>$current_user->date_format,'hour_format'=>$current_user->hour_format,'module'=>'Events','code'=>404,'message'=>$message));
		} else {
			$response->setResult(array('GetEventCount'=>$recentEvent_data,'date_format'=>$current_user->date_format,'hour_format'=>$current_user->hour_format,'module'=>'Events', 'message'=>''));
		}
		return $response;
	}
}

function dateCustBet($date_from,$date_to,$format,$year,$month){
	// Specify the start date. This date can be any English textual format  
	$date_from = strtotime($date_from); // Convert date to a UNIX timestamp  
	// Specify the end date. This date can be any English textual format  
	$date_to = strtotime($date_to); // Convert date to a UNIX timestamp    
	// Loop from the start date to end date and output all dates inbetween  
	$Retmasterdates = array();
	for ($i=$date_from; $i<=$date_to; $i+=86400) {  
		if(date('Y', $i) == $year && date('m', $i) == $month){
	    	$Retmasterdates[] = date($format, $i);  
	    }	
	}
	return $Retmasterdates;
}
