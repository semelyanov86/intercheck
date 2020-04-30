<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_GetAllUserLocation extends Webapp_WS_Controller {	
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb, $current_user;
		$userId = trim($request->get('userid'));
		$userId = substr($userId, stripos($userId, 'x')+1);
		$usersRecordModel = Users_Record_Model::getInstanceById($userId,'Users');
		$users = $usersRecordModel->getRoleBasedSubordinateUsers();
		$AccesibleUsers = array_keys($users);
		$userData = array();
		$userQuery = $adb->pquery("SELECT DISTINCT(userid) FROM webapp_userderoute WHERE userid != '".$userId."'");
		$userNumRows = $adb->num_rows($userQuery);
		for($k=0;$k<$userNumRows;$k++){
			$user_id = $adb->query_result($userQuery,$k,'userid');
			if(!in_array($user_id,$AccesibleUsers)){
				continue;
			}
		$datefind=date("Y-m-d H:i:s",strtotime("-30 minutes"));
	    $selectUserQuery = $adb->pquery("SELECT * FROM webapp_userderoute INNER JOIN vtiger_users ON vtiger_users.id = webapp_userderoute.userid WHERE vtiger_users.deleted =0
                                    AND webapp_userderoute.userid =? AND webapp_userderoute.createdtime > ? ORDER BY webapp_userderoute.id DESC LIMIT 1", array($user_id,$datefind));									
		$selectUserQueryCount = $adb->num_rows($selectUserQuery);
		
			for($i=0;$i<$selectUserQueryCount;$i++) {
				$userid = $adb->query_result($selectUserQuery, $i, 'id');
				$userRecordModel = Vtiger_Record_Model::getInstanceById($userid, 'Users');
				$first_name = trim($userRecordModel->get('first_name'));
				$last_name = trim($userRecordModel->get('last_name'));
				$userName = html_entity_decode($first_name." ".$last_name, ENT_QUOTES, $default_charset);
				$latitude = trim($adb->query_result($selectUserQuery, $i, 'latitude'));
				$longitude = trim($adb->query_result($selectUserQuery, $i, 'longitude'));
				$userImage = Webapp_WS_Utils::getUserImage($userid);
				$userData[] =  array('userid'=>$userid, 'latitude'=>$latitude, 'longitude'=>$longitude, 'username'=>$userName,'userImage'=>$userImage);
			} 
		}
		$response = new Webapp_API_Response();
		if(count($userData) == 0) {
			$message = vtranslate('LBL_NO_RECORDS_FOUND','Vtiger');
			$response->setResult(array('records'=>array(),'message'=>$message));
		}else{
			$response->setResult(array('records'=>$userData,'message'=>""));
		}
		return $response;				
	}
}

?>
