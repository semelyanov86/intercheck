<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';
include_once('vtlib/Vtiger/Unzip.php');
class Webapp_WS_Upgrade extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $adb,$root_directory,$current_user;
		$current_user = $this->getActiveUser();
		$doc_root = $_SERVER['DOCUMENT_ROOT'];
		//delete all user session
        $unsetSesion = WebappSettings_Module_Model::destroyAllUserSession();

        $url = WebappSettings_Module_Model::$CTMOBILE_VERSION_URL;
        $ch = curl_init($url);
		$data = array( "vt_version"=>'7.x');
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		$jason_result = json_decode($result);
		$zip_url = $jason_result->ext_path;
		$ext_version = $jason_result->ext_version;
		mkdir($root_directory."/test/".$ext_version, 0777);
		$destination_path = $root_directory."/test/".$ext_version."/Webappupgrade.zip";
		file_put_contents($destination_path, fopen($zip_url, 'r'));
		chmod($root_directory."/test/".$ext_version."/Webappupgrade.zip",0755);
		
		chmod($root_directory."/test/".$ext_version."/",0777);
		$unzip = new Vtiger_Unzip($root_directory."/test/".$ext_version."/Webappupgrade.zip");
		$unzip->unzipAllEx($root_directory."/test/".$ext_version."/");
		
		$package = new Vtiger_Package();
		$package->update(Vtiger_Module::getInstance('Webapp'),$root_directory."/test/".$ext_version.'/Webapp.zip');
		$package->update(Vtiger_Module::getInstance('WebappSettings'),$root_directory."/test/".$ext_version.'/WebappSettings.zip');
		$package->update(Vtiger_Module::getInstance('WebUserFilterView'),$root_directory."/test/".$ext_version.'/WebUserFilterView.zip');
		$package->update(Vtiger_Module::getInstance('WebAttendance'),$root_directory."/test/".$ext_version.'/WebAttendance.zip');
		$package->update(Vtiger_Module::getInstance('WebPushNotification'),$root_directory."/test/".$ext_version.'/WebPushNotification.zip');

		$array = array('WebAttendance','WebMessageTemplate','Webapp','WebPushNotification','WebUserFilterView','WebappSettings');
		foreach ($array as $key => $value) {
			$path  = $root_directory.'modules/'.$value;
    		chmod($path, 0755);
    		$path  = $root_directory.'layouts/v7/modules/'.$value;
    		chmod($path, 0755);
        } 
		$upload_status =  copy($root_directory.'/test/'.$ext_version.'/WebappApi.php', $root_directory.'/WebappApi.php');
		
		$response = new Webapp_API_Response();
		$message = vtranslate('Your Version updated successfully','Webapp');
		$response->setResult(array('code'=>1,'message'=>$message));
		return $response;				
	}
}

?>
