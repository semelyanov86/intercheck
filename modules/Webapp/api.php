<?php

header('Content-Type: text/json');

chdir (dirname(__FILE__) . '/../../');

/**
 * URL Verfication - Required to overcome Apache mis-configuration and leading to shared setup mode.
 */
require_once 'config.php';
if (file_exists('config_override.php')) {
    include_once 'config_override.php';
}

// Define GetRelatedList API before including the core files
// NOTE: Make sure GetRelatedList function_exists check is made in include/utils/RelatedListView.php
include_once dirname(__FILE__) . '/api/Relation.php';

include_once dirname(__FILE__) . '/api/Request.php';
include_once dirname(__FILE__) . '/api/Response.php';
include_once dirname(__FILE__) . '/api/Session.php';

include_once dirname(__FILE__) . '/api/ws/Controller.php';
require_once 'includes/main/WebUI.php';

class Webapp_API_Controller {

	static $opControllers = array(
		'login'                   => array('file' => '/api/ws/Login.php', 'class' => 'Webapp_WS_Login'),
		'loginAndFetchModules'    => array('file' => '/api/ws/LoginAndFetchModules.php', 'class' => 'Webapp_WS_LoginAndFetchModules'),
		'fetchModuleFilters'      => array('file' => '/api/ws/FetchModuleFilters.php'  , 'class' => 'Webapp_WS_FetchModuleFilters'),
		'filterDetailsWithCount'  => array('file' => '/api/ws/FilterDetailsWithCount.php', 'class' => 'Webapp_WS_FilterDetailsWithCount'),
		'fetchAllAlerts'          => array('file' => '/api/ws/FetchAllAlerts.php', 'class' => 'Webapp_WS_FetchAllAlerts'),
		'alertDetailsWithMessage' => array('file' => '/api/ws/AlertDetailsWithMessage.php', 'class' => 'Webapp_WS_AlertDetailsWithMessage'),
		'listModuleRecords'       => array('file' => '/api/ws/ListModuleRecords.php', 'class' => 'Webapp_WS_ListModuleRecords'),
		'fetchRecord'             => array('file' => '/api/ws/FetchRecord.php', 'class' => 'Webapp_WS_FetchRecord'),
		'getProcessesStatusesSubStatuses'             => array('file' => '/api/ws/GetProcesses.php', 'class' => 'Webapp_WS_GetProcesses'),
		'getLeadPrices'             => array('file' => '/api/ws/FetchRecordPrices.php', 'class' => 'Webapp_WS_FetchRecordPrices'),
        'getUserPlatform'             => array('file' => '/api/ws/GetUserPlatform.php', 'class' => 'Webapp_WS_GetUserPlatform'),
		'fetchRecordWithGrouping' => array('file' => '/api/ws/FetchRecordWithGrouping.php', 'class' => 'Webapp_WS_FetchRecordWithGrouping'),
		'fetchRecordsWithGrouping' => array('file' => '/api/ws/FetchRecordsWithGrouping.php', 'class' => 'Webapp_WS_FetchRecordsWithGrouping'),
		/*'describe'                => array('file' => '/api/ws/Describe.php', 'class' => 'Webapp_WS_Describe'),*/
		'saveRecord'              => array('file' => '/api/ws/SaveRecord.php', 'class' => 'Webapp_WS_SaveRecord'),
		'createActions'              => array('file' => '/api/ws/CreateActions.php', 'class' => 'Webapp_WS_CreateActions'),
		'syncModuleRecords'       => array('file' => '/api/ws/SyncModuleRecords.php', 'class' => 'Webapp_WS_SyncModuleRecords'),

		'query'                   => array('file' => '/api/ws/Query.php', 'class' => 'Webapp_WS_Query'),
		'queryWithGrouping'       => array('file' => '/api/ws/QueryWithGrouping.php', 'class' => 'Webapp_WS_QueryWithGrouping'),

		'relatedRecordsWithGrouping' => array('file' => '/api/ws/RelatedRecordsWithGrouping.php', 'class' => 'Webapp_WS_RelatedRecordsWithGrouping'),
		'deleteRecords'              => array('file' => '/api/ws/DeleteRecords.php', 'class' => 'Webapp_WS_DeleteRecords'),

		'addRecordComment'           => array('file' => '/api/ws/AddRecordComment.php', 'class' => 'Webapp_WS_AddRecordComment'),
		'history'                    => array('file' => '/api/ws/History.php', 'class' => 'Webapp_WS_History'),
                'taxByType'                  => array('file'=>'/api/ws/TaxByType.php','class'=>'Webapp_WS_TaxByType'),
                'fetchModuleOwners'          => array('file' => '/api/ws/FetchModuleOwners.php', 'class'=>'Webapp_WS_FetchModuleOwners'),
                'relatedModule'          => array('file' => '/api/ws/RelatedModule.php', 'class'=>'Webapp_WS_RelatedModule'),
                'relatedModuleList'          => array('file' => '/api/ws/RelatedModuleList.php', 'class'=>'Webapp_WS_RelatedModuleList'),
                'recentEvent' => array('file' => '/api/ws/RecentEvent.php', 'class'=>'Webapp_WS_RecentEvent'),
                'userData'          => array('file' => '/api/ws/UserData.php', 'class'=>'Webapp_WS_UserData'),
                'listRecordComment' => array('file' => '/api/ws/ListRecordComment.php', 'class' => 'Webapp_WS_ListRecordComment'),
                'getNearestPlace' => array('file' => '/api/ws/GetNearestPlace.php', 'class'=>'Webapp_WS_GetNearestPlace'),
                'searchRecord' => array('file' => '/api/ws/SearchRecord.php', 'class'=>'Webapp_WS_SearchRecord'),
                'updateLocation' => array('file'=>'/api/ws/UpdateLocation.php', 'class'=>'Webapp_WS_UpdateLocation'),
				'attendance' => array('file'=>'/api/ws/Attendance.php', 'class'=>'Webapp_WS_Attendance'),
				'attendanceUserStatus' => array('file'=>'/api/ws/AttendanceUserStatus.php', 'class'=>'Webapp_WS_AttendanceUserStatus'),
				'attendanceUserHours' => array('file'=>'/api/ws/AttendanceUserHours.php', 'class'=>'Webapp_WS_AttendanceUserHours'),
                'getAllUserLocation' => array('file'=>'/api/ws/GetAllUserLocation.php', 'class'=>'Webapp_WS_GetAllUserLocation'),
				'GetUserRoute' => array('file'=>'/api/ws/GetUserRoute.php', 'class'=>'Webapp_WS_GetUserRoute'),
				'GetRouteUserList' => array('file'=>'/api/ws/GetRouteUserList.php', 'class'=>'Webapp_WS_GetRouteUserList'),
				'GetEventList' => array('file'=>'/api/ws/GetEventList.php', 'class'=>'Webapp_WS_GetEventList'),
				'GetDateBaseEventList' => array('file'=>'/api/ws/GetDateBaseEventList.php', 'class'=>'Webapp_WS_GetDateBaseEventList'),
				'GetMonthBaseEventCount' => array('file'=>'/api/ws/GetMonthBaseEventCount.php', 'class'=>'Webapp_WS_GetMonthBaseEventCount'),
				
				'GetFolderList'          => array('file' => '/api/ws/GetFolderList.php',          'class' => 'Webapp_WS_GetFolderList'),
				'FetchFolderEmails'          => array('file' => '/api/ws/FetchFolderEmails.php',          'class' => 'Webapp_WS_FetchFolderEmails'),
				'FetchEmailById'				=> array('file' => '/api/ws/FetchEmailById.php',             'class' => 'Webapp_WS_FetchEmailById'),
				'AttachedEmail'			=> array('file' => '/api/ws/AttachedEmail.php',		'class' => 'Webapp_WS_AttachedEmail'),
				'SearchEmail'			=> array('file' => '/api/ws/SearchEmail.php',		'class' => 'Webapp_WS_SearchEmail'),
				'MarkAsUnreadEmail'			=> array('file' => '/api/ws/MarkAsUnreadEmail.php',		'class' => 'Webapp_WS_MarkAsUnreadEmail'),
				'DeleteEmail'			=> array('file' => '/api/ws/DeleteEmail.php',		'class' => 'Webapp_WS_DeleteEmail'),
				'MoveEmail'			=> array('file' => '/api/ws/MoveEmail.php',		'class' => 'Webapp_WS_MoveEmail'),
				/*'EmailAction'			=> array('file' => '/api/ws/EmailAction.php',		'class' => 'Webapp_WS_EmailAction'),*/
				'AddAttachmentByid'			=> array('file' => '/api/ws/AddAttachmentByid.php',		'class' => 'Webapp_WS_AddAttachmentByid'),
				'FetchCommentModules'			=> array('file' => '/api/ws/FetchCommentModules.php',		'class' => 'Webapp_WS_FetchCommentModules'),
				'SendFeedback'			=> array('file' => '/api/ws/SendFeedback.php',		'class' => 'Webapp_WS_SendFeedback'),
				
				 'getMessageTemplate' => array('file'=>'/api/ws/GetMessageTemplate.php', 'class'=>'Webapp_WS_GetMessageTemplate'),
                'sendEmail' => array('file' =>'/api/ws/SendEmail.php', 'class' =>'Webapp_WS_SendEmail'),
                'ReplyEmail' => array('file' =>'/api/ws/ReplyEmail.php', 'class' =>'Webapp_WS_ReplyEmail'),
				'sendSMS' => array('file' => '/api/ws/SendSMS.php', 'class' => 'Webapp_WS_SendSMS'),
				'saveMultipleRecord'              => array('file' => '/api/ws/SaveMultipleRecord.php', 'class' => 'Webapp_WS_SaveMultipleRecord'),
			    'RelatedRecords'              => array('file' => '/api/ws/RelatedRecords.php', 'class' => 'Webapp_WS_RelatedRecords'),
				'DependencyAddress'              => array('file' => '/api/ws/DependencyAddress.php', 'class' => 'Webapp_WS_DependencyAddress'),
				'forgotPassword' => array('file' => '/api/ws/ForgotPassword.php', 'class' => 'Webapp_WS_ForgotPassword'),
				'updatePendingShift' => array('file' => '/api/ws/UpdatePendingShift.php', 'class' => 'Webapp_WS_UpdatePendingShift'),
				'Upgrade' => array('file' => '/api/ws/Upgrade.php', 'class' => 'Webapp_WS_Upgrade'),
				'SaveUserImage' => array('file' => '/api/ws/SaveUserImage.php', 'class' => 'Webapp_WS_SaveUserImage'),
				'NearbyStatus'=> array('file' => '/api/ws/NearbyStatus.php', 'class' => 'Webapp_WS_NearbyStatus'),
				'CheckOutgoingServer'=> array('file' => '/api/ws/CheckOutgoingServer.php', 'class' => 'Webapp_WS_CheckOutgoingServer'),
				'logout'=> array('file' => '/api/ws/Logout.php', 'class' => 'Webapp_WS_Logout'),
				'globalSearch'=> array('file' => '/api/ws/GlobalSearch.php', 'class' => 'Webapp_WS_GlobalSearch'),
				'saveShortcut'=> array('file' => '/api/ws/SaveShortcut.php', 'class' => 'Webapp_WS_SaveShortcut'),
				'getShortcut'=> array('file' => '/api/ws/GetShortcut.php', 'class' => 'Webapp_WS_GetShortcut'),
				'ConvertLead'=> array('file' => '/api/ws/ConvertLead.php', 'class' => 'Webapp_WS_ConvertLead'),
				'SaveConvertLead'=> array('file' => '/api/ws/SaveConvertLead.php', 'class' => 'Webapp_WS_SaveConvertLead'),
				'deleteShortcut'=> array('file' => '/api/ws/DeleteShortcut.php', 'class' => 'Webapp_WS_DeleteShortcut'),
				'CardScannerModules'=> array('file' => '/api/ws/CardScannerModules.php', 'class' => 'Webapp_WS_CardScannerModules'),
				'Dashboard'=> array('file' => '/api/ws/Dashboard.php', 'class' => 'Webapp_WS_Dashboard'),
				'newDescribe'   => array('file' => '/api/ws/NewDescribe.php', 'class' => 'Webapp_WS_NewDescribe'),
				'picklistDependency'=> array('file' => '/api/ws/PicklistDependency.php', 'class' => 'Webapp_WS_PicklistDependency'),
				'myListModules'=> array('file' => '/api/ws/MyListModules.php', 'class' => 'Webapp_WS_MyListModules'),
				'QRScanning'=> array('file' => '/api/ws/QRScanning.php', 'class' => 'Webapp_WS_QRScanning'),
				'NearByModules'=> array('file' => '/api/ws/NearByModules.php', 'class' => 'Webapp_WS_NearByModules'),
				'DownloadUrl'=> array('file' => '/api/ws/DownloadUrl.php', 'class' => 'Webapp_WS_DownloadUrl'),
				'CTLanguage'=> array('file' => '/api/ws/CTLanguage.php', 'class' => 'Webapp_WS_CTLanguage'),
				'saveAjaxRecord'=> array('file' => '/api/ws/SaveAjaxRecord.php', 'class' => 'Webapp_WS_SaveAjaxRecord'),
				'addWidgets'=> array('file' => '/api/ws/AddWidgets.php', 'class' => 'Webapp_WS_AddWidgets'),
				'deleteWidgets'=> array('file' => '/api/ws/DeleteWidgets.php', 'class' => 'Webapp_WS_DeleteWidgets'),
				'getVCardFields'=> array('file' => '/api/ws/GetVCardFields.php', 'class' => 'Webapp_WS_GetVCardFields'),
				'updateSequence'=> array('file' => '/api/ws/UpdateSequenceDashboard.php', 'class' => 'Webapp_WS_UpdateSequenceDashboard'),
				
				
				
	);

	static function process(Webapp_API_Request $request) {
		$operation = $request->getOperation();
		$sessionid = $request->getSession();
		global $adb;
		/*if($sessionid){
			$checkSession = $adb->pquery("SELECT * FROM webapp_session_expire WHERE sessionid = ?",array($sessionid));
			$totalSession = $adb->num_rows($checkSession);
			if($totalSession > 0){
				session_regenerate_id(true);
				Vtiger_Session::destroy();

				$deleteSession = "UPDATE webapp_userdevicetoken SET devicetoken = '', sessionid = '' WHERE sessionid = ?";
				$adb->pquery($deleteSession,array($sessionid));

				$deleteOldSession = $adb->pquery("DELETE FROM webapp_session_expire WHERE sessionid = ?",array($sessionid));
				$message = vtranslate("Your Session has been Expired","Webapp");
				$response = new Webapp_API_Response();
				$response->setError(1555,$message);
				echo $response->emitJSON();
				exit;

			}
		}*/
		$response = false;
		if(isset(self::$opControllers[$operation])) {

			$operationFile = self::$opControllers[$operation]['file'];
			$operationClass= self::$opControllers[$operation]['class'];

			include_once dirname(__FILE__) . $operationFile;
			$operationController = new $operationClass;

			$operationSession = false;
			if($operationController->requireLogin()) {
				$operationSession = Webapp_API_Session::init($sessionid);
				if($operationController->hasActiveUser() === false) {
					$operationSession = false;
				}
				//Webapp_WS_Utils::initAppGlobals();
			} else {
				// By-pass login
				$operationSession = true;
			}

			if($operationSession === false && $operation != 'forgotPassword') {
				$response = new Webapp_API_Response();							
				$message = vtranslate('Login required - Please login again','Webapp');
				$response->setError(1501, $message);
			} else {
				self::checkUserActive();
				if($operation != 'login' && $operation != 'loginAndFetchModules'){
					
					
					if($operation == 'listModuleRecords'){
						global $current_user;
						$userid = Webapp_WS_Controller::sessionGet('_authenticated_user_id');
						$activeUser = CRMEntity::getInstance('Users');
						$activeUser->retrieveCurrentUserInfoFromFile($userid);
						$current_user = $activeUser;
						$presence = array('0', '2');
						$moduleName = $request->get('module');
						if($moduleName != 'Users'){
							$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
							$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
							if(($userPrivModel->isAdminUser() ||
								$userPrivModel->hasGlobalReadPermission() ||
								$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)){
							}else{
								$message = vtranslate('You have not permission to access this module. either module is disabled','Webapp');
								$response = new Webapp_API_Response();
								$response->setError(403,$message);
								echo $response->emitJSON();
								exit;
							}
						}
					}
		
				}
				try {
					$response = $operationController->process($request);
				} catch(Exception $e) {
					$response = new Webapp_API_Response();											   
					$response->setError($e->getCode(), $e->getMessage());
				}
			}

		} else {
			$response = new Webapp_API_Response();							   
			$response->setError(1404, 'Operation not found: ' . $operation);
		}

		if($response !== false) {
			echo $response->emitJSON();
		}
	}

	static function checkUserActive(){
		global $adb;
		$userid = Webapp_WS_Controller::sessionGet('_authenticated_user_id');
		if($userid){
			$activeUser = CRMEntity::getInstance('Users');
			$activeUser->retrieveCurrentUserInfoFromFile($userid);
			$current_user = $activeUser;
			$query_user = $adb->pquery("SELECT * FROM webapp_userdevicetoken WHERE userid = ?",array($userid));
			$count = 0;
			if($adb->num_rows($query_user) > 0){
				$currency_id = $adb->query_result($query_user,0,'currency_id');
				$time_zone = $adb->query_result($query_user,0,'time_zone');
				$date_format = $adb->query_result($query_user,0,'date_format');
				$hour_format = $adb->query_result($query_user,0,'hour_format');
				$language = $adb->query_result($query_user,0,'language');

				if(trim($current_user->currency_id) != trim($currency_id)){
					$count++;
				}
				if(trim($current_user->time_zone) != trim($time_zone)){
					$count++;
				}
				if(trim($current_user->date_format) != trim($date_format)){
					$count++;
				}
				if(trim($current_user->hour_format) != trim($hour_format)){
					$count++;
				}
				if(trim($current_user->language) != trim($language)){
					$count++;
				}
			}

			/*if($count != 0){
				session_regenerate_id(true);
				Vtiger_Session::destroy();
				$message = vtranslate("Your Session has been Expired","Webapp");
				$response = new Webapp_API_Response();
				$response->setError(1555,$message);
				echo $response->emitJSON();
				exit;
			}*/


			$checkActive = $adb->pquery("SELECT status,deleted FROM vtiger_users WHERE id = ?",array($userid));

			if($adb->num_rows($checkActive) > 0){
				$status = $adb->query_result($checkActive,0,'status');
				$deleted = $adb->query_result($checkActive,0,'deleted');
				if($status == 'Inactive'){
					$deleteSession = "UPDATE webapp_userdevicetoken SET devicetoken = '', sessionid = '' WHERE userid = ?";
					$adb->pquery($deleteSession,array($userid));
					session_regenerate_id(true);
					Vtiger_Session::destroy();
					$message = vtranslate("Your Session has been Expired","Webapp");
					$response = new Webapp_API_Response();
					$response->setError(1555,$message);
					echo $response->emitJSON();
					exit;
				}
			}else{
				session_regenerate_id(true);
				Vtiger_Session::destroy();
				$message = vtranslate("Your Session has been Expired","Webapp");
				$response = new Webapp_API_Response();
				$response->setError(1555,$message);
				echo $response->emitJSON();
				exit;
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
					$message = vtranslate("Your Session has been Expired","Webapp");
					$response = new Webapp_API_Response();
					$response->setError(1555,$message);
					echo $response->emitJSON();
					exit;
				}
			}else{
				$message = vtranslate("Your Session has been Expired","Webapp");
				$response = new Webapp_API_Response();
				$response->setError(1555,$message);
				echo $response->emitJSON();
				exit;
			}
		}
	}
}

/** Take care of stripping the slashes */
function stripslashes_recursive($value) {
       $value = is_array($value) ? array_map('stripslashes_recursive', $value) : stripslashes($value);
       return $value;
}
/** END **/

if(!defined('MOBILE_API_CONTROLLER_AVOID_TRIGGER')) {
	$clientRequestValues = $_POST; // $_REQUEST or $_GET
    if (empty($clientRequestValues)) {
        $clientRequestValues = json_decode(file_get_contents("php://input"), true);
    }
	$clientRequestValuesRaw = array();

	// Set of request key few controllers are interested in raw values (example, SaveRecord)
	/*$rawValueHeaders = array('values');
	foreach($rawValueHeaders as $rawValueHeader) {
		if(isset($clientRequestValues[$rawValueHeader])) {
			$clientRequestValuesRaw[$rawValueHeader] = $clientRequestValues[$rawValueHeader];
		}
	}*/
	// END

	if (get_magic_quotes_gpc()) {
	    $clientRequestValues = stripslashes_recursive($clientRequestValues);
	}
	Webapp_API_Controller::process(new Webapp_API_Request($clientRequestValues, $clientRequestValuesRaw));
}
