<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once 'include/Webservices/Delete.php';

class Webapp_WS_DeleteShortcut extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $adb,$current_user;
		$current_user = $this->getActiveUser();
		$shortcutid = trim($request->get('shortcutid'));
		$shortcutType = trim($request->get('shortcutType'));
		if(empty($shortcutid)){
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		if(empty($shortcutType)){
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		if (!empty($shortcutid)) {
			if($shortcutType == 'filter'){
				$result = $adb->pquery("DELETE FROM webapp_filter_shortcut WHERE shortcutid = ?",array($shortcutid));
			}
			if($shortcutType == 'record'){
				$result = $adb->pquery("DELETE FROM webapp_record_shortcut WHERE shortcutid = ?",array($shortcutid));
			}
		}
		if($result){
			$response = new Webapp_API_Response();
			$response->setResult(array('deleted' => $shortcutid,"message"=>vtranslate('Shortcut has been deleted','Webapp')));
		}else{
			$response = new Webapp_API_Response();
			$response->setError(0,'Something went wrong. try again');
		}
		
		
		return $response;
	}
}
