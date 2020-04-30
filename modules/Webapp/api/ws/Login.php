<?php

class Webapp_WS_Login extends Webapp_WS_Controller {

	function requireLogin() {
		return false;
	}

	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$response = new Webapp_API_Response();

		$username = trim($request->get('username'));
		$password = trim($request->get('password'));

		$current_user = CRMEntity::getInstance('Users');
		$current_user->column_fields['user_name'] = $username;		

		if(!$current_user->doLogin($password)) {
			$message = vtranslate('Authentication Failed','Webapp');
			$response->setError(1210, $message);

		} else {
			
			// Start session now
			$sessionid = Webapp_API_Session::init();

			if($sessionid === false) {
				$message = vtranslate('Session init failed $sessionid\n','Webapp');
				echo $message;
			}

			$current_user->id = $current_user->retrieve_user_id($username);
			$current_user->retrieveCurrentUserInfoFromFile($current_user->id);
			$this->setActiveUser($current_user);
			$theme = $current_user->theme_config;
			
			if($theme == 'RTL'){	
				$theme = true;
			} else if($theme == 'LTR') {
				$theme = false;
			}else{
				$theme = $current_user->theme;
				$explode_theme = explode('_',$theme);
			
				if(isset($explode_theme[1]) && $explode_theme[1] == 'rtl') {
					$theme = true;
				}else if(isset($explode_theme[1]) && $explode_theme[1] == 'ltr'){
					$theme = false;
				}else{
					$theme = false;
				}
			}

			$device_key = $request->get('device_key');
			$device_type = $request->get('device_type');
			if($device_key!='' && $device_type != '' && $current_user->id != ''){
				global $adb;
				$userId = $current_user->id;
				$selectQuery = $adb->pquery("SELECT * FROM webapp_userdevicetoken where userid = ?", array($userId));								
				$selectQueryCount = $adb->num_rows($selectQuery);
				
				if($selectQueryCount > 0) {
					$oldsessionid = $adb->query_result($selectQuery,0,'sessionid');
					if($oldsessionid != ''){
					 	$InsertOldSession = $adb->pquery("INSERT INTO webapp_session_expire (userid, sessionid) VALUES(?,?)",array($userId,$oldsessionid));
					}
					$query = $adb->pquery("UPDATE webapp_userdevicetoken SET devicetoken = ?, device_type = ?, sessionid = ?, currency_id = ? ,time_zone = ?, date_format = ?, hour_format = ?, language = ? WHERE userid = ?", array($device_key, $device_type, $sessionid,$current_user->currency_id,$current_user->time_zone,$current_user->date_format,$current_user->hour_format,$current_user->language, $userId));
					
				} else {
					$query = $adb->pquery("INSERT INTO webapp_userdevicetoken (userid, devicetoken, device_type, longitude, latitude,sessionid,currency_id,time_zone,date_format,hour_format,language) VALUES (?,?,?,?,?,?,?,?,?,?,?)", array($userId, $device_key, $device_type,'0', '0',$sessionid,$current_user->currency_id,$current_user->time_zone,$current_user->date_format,$current_user->hour_format,$current_user->language));
				}
			}
		
			$userId = $current_user->id;
			if($userId!=''){
				$userImage = Webapp_WS_Utils::getUserImage($userId);	
				$first_name = $current_user->first_name;
				$last_name = $current_user->last_name;
					
			}
			
			$moduleModel = Vtiger_Module_Model::getInstance('Webapp');
			if($moduleModel->get('presence') != 0){
				$message = vtranslate('Please Enable Webapp Module','Webapp');
				$response->setError(404, $message);
				return $response;	
			}

			global $adb,$default_module;
			
			$version=$adb->pquery("SELECT * FROM vtiger_tab where name='WebappSettings'",array());
			$mobile_web_version = $adb->query_result($version,0,'version');
			
			//for livetracking access to user
			$liveuserQuery = $adb->pquery("SELECT 1 FROM webapp_livetracking_users WHERE userid = ?",array($current_user->id));
			if($adb->num_rows($liveuserQuery) > 0){
				$livetracking = true;
			}else{
				$livetracking = false;
			}
			//for webapp access to user
			$webappAccessQuery = $adb->pquery("SELECT * FROM webapp_access_users",array());

			$allGroups = array_keys(Settings_Groups_Record_Model::getAll());
			$groupUsers = array();
			$selectedUsers = array();
			if($adb->num_rows($webappAccessQuery) > 0){
				for($i=0;$i<($adb->num_rows($webappAccessQuery));$i++){
					//$selectedUsers[] = $adb->query_result($webappAccessQuery,$i,'userid');
					$userid = $adb->query_result($webappAccessQuery,$i,'userid');
					if(in_array($userid,$allGroups)){
	                    $groupuser = Users_Record_Model::getAccessibleGroupUsers($userid);
	                    $groupUsers = array_merge($groupUsers,$groupuser);
	                }else{
	                	$Users[] = $userid;
	                }
				}
				if(!empty($Users)){
					$selectedUsers = array_merge($Users,$groupUsers);
				}else{
					$selectedUsers = $groupUsers;
				}
				if(in_array('selectAll',$selectedUsers) || in_array($current_user->id,$selectedUsers)){
					$webappAccess = true;
				}else{
					$webappAccess = false;
				}
			}else{
				$webappAccess = false;
			}
			
			$user_type = '';
			$expirydate = '';
			//for webapp usertype and expirydate
		
			global $current_user;
			$current_user = $this->getActiveUser();
			$expirydate = Vtiger_Date_UIType::getDisplayValue($expirydate);

			$resultApi = $adb->pquery("SELECT * FROM webapp_api_settings",array());

			$api_key = $adb->query_result($resultApi,0,'api_key');
			global $default_module,$upload_maxsize;
			$uploaded_maxsizeinmb = $upload_maxsize/(1024*1024);
			$currency_symbol = html_entity_decode($current_user->currency_symbol, ENT_QUOTES, $default_charset);
			$userName = html_entity_decode($first_name." ".$last_name, ENT_QUOTES, $default_charset);

			$result = array();
			$result['login'] = array(
				'userImage'=>$userImage,
				'userName' => $userName,
				'userid' => $current_user->id,
				'email' => $current_user->email1,
				'is_admin'=>$current_user->is_owner,
				'crm_tz' => DateTimeField::getDBTimeZone(),
				'user_tz' => $current_user->time_zone,
                'start_hour'=>$current_user->start_hour,
                'callduration'=>$current_user->callduration,
                'eventduration'=>$current_user->othereventduration,
                'user_currency' => $current_user->currency_code,
                'currency_id'=>$current_user->currency_id,
                'currency_name'=>$current_user->currency_name,
                'currency_code'=>$current_user->currency_code,
                'currency_symbol'=>$currency_symbol,
                'currency_decimal_separator'=>$current_user->currency_decimal_separator,
                'currency_grouping_separator'=>$current_user->currency_grouping_separator,
                'currency_grouping_pattern'=>$current_user->currency_grouping_separator,
                'uploaded_maxsize'=>$uploaded_maxsizeinmb,
                'document_size_validation'=>vtranslate('Upload file size should be less than','Webapp').' '.$uploaded_maxsizeinmb.vtranslate('MB','Documents'),
                'rtl_theme' => $theme,
                'language' => $current_user->language,
				'session'=> $sessionid,				
				'vtiger_version' => Webapp_WS_Utils::getVtigerVersion(),
                'date_format' => $current_user->date_format, 
				'mobile_module_version' => Webapp_WS_Utils::getVersion(),
				'hour_format'=>$current_user->hour_format,
				'default_module'=>$default_module,
				'default_module_label'=>vtranslate($default_module,$default_module),
				'mobile_web_version'=>$mobile_web_version,
				'api_key'=>$api_key,
				'livetracking'=>$livetracking,
				'webapp_access_user' => $webappAccess,
				'user_type'=>$user_type,
				'expirydate'=>$expirydate
			);

			$response->setResult($result);

			$this->postProcess($response);
		
		}
		return $response;
	}

	function postProcess(Webapp_API_Response $response) {
		return $response;
	}
}
