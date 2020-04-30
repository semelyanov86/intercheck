<?php

class Webapp_WS_UpdateSequenceDashboard extends Webapp_WS_Controller {
	function process(Webapp_API_Request $request) {
		global $adb,$current_user; 
		$current_user = $this->getActiveUser();
		
		$userid = $current_user->id;
		$sequence = $request->get('sequence');
		if($sequence != ""){
			$sequence = Zend_Json::decode($sequence);
			$deleteSql = $adb->pquery("DELETE FROM webapp_dashboard_sequence WHERE userid = ?",array($userid));
			foreach($sequence as $key => $value){
				$type = $value['type'];
				$id = $value['id'];
				$insertedResult = $adb->pquery("INSERT INTO webapp_dashboard_sequence (id,userid,type) VALUES(?,?,?)",array($id,$userid,$type));
				
			}
			$response = new Webapp_API_Response();
			$message = vtranslate('sequence updated successfully','Webapp');
			$response->setResult(array('message' => $message));
			return $response;
		}else{
			$message = vtranslate('sequence cannot be empty!','Webapp');
			$response->setError(404, $message);
			return $response;
		}

		
	}
}
