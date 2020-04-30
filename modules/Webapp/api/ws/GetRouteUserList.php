<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_GetRouteUserList extends Webapp_WS_Controller {
	
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
		$current_user = $this->getActiveUser();
		$userid = $current_user->id;
		$roleid = $current_user->roleid;
		
		require_once('include/utils/UserInfoUtil.php');
		$now_rs_users = getRoleAndSubordinateUsers($roleid);
		foreach ($now_rs_users as $now_rs_userid => $now_rs_username) {
			
			$userRecordModel = Vtiger_Record_Model::getInstanceById($now_rs_userid, 'Users');
			$first_name = trim($userRecordModel->get('first_name'));
			$last_name = trim($userRecordModel->get('last_name'));
			$userName = html_entity_decode($first_name." ".$last_name, ENT_QUOTES, $default_charset);
			$userData[] =  array('userid'=>$now_rs_userid, 'username'=>$userName);		
		}
		
		if(count($userData) == 0) {
			$response->setResult(array('code'=>404,'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger')));
		}
		$response = new Webapp_API_Response();
		$response->setResult($userData);
		return $response;				
	}
}

?>
