<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_UpdateLocation extends Webapp_WS_Controller {
	
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		global $adb;
		
		$userId = trim($request->get('userid'));
		$latitude = trim($request->get('latitude'));
		$longitude = trim($request->get('longitude'));
		$userId = substr($userId, stripos($userId, 'x')+1);
		$date_var = date("Y-m-d H:i:s");
		$createdtime = $adb->formatDate($date_var, true);
		$selectQuery = $adb->pquery("SELECT * FROM webapp_userdevicetoken where userid = ?", array($userId));								
		$selectQueryCount = $adb->num_rows($selectQuery);
		if($latitude!='' && $longitude!=''){
			if($selectQueryCount > 0) {
			$query = $adb->pquery("UPDATE webapp_userdevicetoken SET latitude = ?, longitude = ? WHERE userid = ?", array($latitude, $longitude, $userId));
			$query = $adb->pquery("INSERT INTO webapp_userderoute (userid, latitude, longitude, createdtime) VALUES (?,?,?,?)", array($userId, $latitude, $longitude, $createdtime));
			} else {
				$query = $adb->pquery("INSERT INTO webapp_userdevicetoken (userid, latitude, longitude) VALUES (?,?,?)", array($userId, $latitude, $longitude));
				
				$query = $adb->pquery("INSERT INTO webapp_userderoute (userid, latitude, longitude, createdtime) VALUES (?,?,?,?)", array($userId, $latitude, $longitude, $createdtime));
			}
			
			if($query) {
				$message = vtranslate('User Location Updated Successfully','Webapp');
				$userData[] = array('code'=>1,'message'=>'User Location Updated Successfully');
			} else {
				$message = vtranslate('User Location not Updated Successfully','Webapp');
				$userData[] = array('code'=>0,'message'=>$message);
			}
			
		}else{
			$message = vtranslate('User Location not Updated Successfully','Webapp');
			$userData[] = array('code'=>0,'message'=>$message);
		}
		
		$response = new Webapp_API_Response();
		$response->setResult($userData);
		return $response;
	}
}

?>
