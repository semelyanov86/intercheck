<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_UserData extends Webapp_WS_Controller {
	
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user,$adb, $site_URL;
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$userId = trim($request->get('userid'));
		$response = new Webapp_API_Response();
		if(empty($userId)){
			$message = vtranslate('userid cannot be empty!','Webapp');
			$response->setError(1501, $message);
			return $response;
		}

		$userImage = Webapp_WS_Utils::getUserImage($userId);

		$userRecordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');
		$first_name = $userRecordModel->get('first_name');
		$first_name = html_entity_decode($first_name, ENT_QUOTES, $default_charset);
		$last_name = $userRecordModel->get('last_name');
		$last_name = html_entity_decode($last_name, ENT_QUOTES, $default_charset);
		$email = $userRecordModel->get('email1');
		$userData = array('userImage'=>$userImage, 'email' => $email, 'userName' => $first_name." ".$last_name);
		
		$response->setResult($userData);
			
		return $response;
	}
}
