<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_SaveUserImage extends Webapp_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		
		$module = trim($request->get('module'));
		if($module != 'Users'){
			$message = vtranslate('Invalid Module name','Webapp');
			throw new WebServiceException(404,$message);
		}
		$recordid = trim($request->get('user_id'));
		if($recordid == ''){
			$message = vtranslate('Userid cannot be blank','Webapp');
			throw new WebServiceException(404,$message);
		}
		if (!empty($_FILES['imagename'])) {
			$files = $_FILES['imagename'];
            $id = explode('x', $recordid);
            global $adb,$site_URL,$root_directory;
            $current_user = $this->getActiveUser();
            $moduleName = $module;
            $storagePath = 'storage/';
            $year  = date('Y');
            $month = date('F');
            $day   = date('j');
            $week  = '';
            
			$date_var = date("Y-m-d H:i:s");
			
            if (!is_dir($root_directory.$storagePath . $year)) {
                mkdir($root_directory.$storagePath . $year);
                chmod($root_directory.$storagePath . $year, 0777);
            }

            if (!is_dir($root_directory.$storagePath . $year . "/" . $month)) {
                mkdir($root_directory.$storagePath . "$year/$month");
                chmod($root_directory.$storagePath . "$year/$month", 0777);
            }

            if ($day > 0 && $day <= 7){
                $week = 'week1';
            }elseif ($day > 7 && $day <= 14){
                $week = 'week2';
            }elseif ($day > 14 && $day <= 21){
                $week = 'week3';
            }elseif ($day > 21 && $day <= 28){
                $week = 'week4';
            }else{
                $week = 'week5'; 
            }
            
            if (!is_dir($root_directory.$storagePath . $year . "/" . $month . "/" . $week)) {
                mkdir($root_directory.$storagePath . "$year/$month/$week");
                chmod($root_directory.$storagePath . "$year/$month/$week", 0777);
            }
            $interior = $storagePath . $year . "/" . $month . "/" . $week . "/";
            $crm_id = $adb->getUniqueID("vtiger_crmentity");
            $upload_status = move_uploaded_file($files['tmp_name'],$interior.$crm_id.'_'. $files['name']);
			if($upload_status){
				
				$delquery = 'delete from vtiger_salesmanattachmentsrel where smid = ?';
				$adb->pquery($delquery, array($id[1]));
				
				$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($crm_id, $current_user->id, $current_user->id, $moduleName." Image",'', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
				$adb->pquery($sql1, $params1);
				//Add entry to attachments
				$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
				$params2 = array($crm_id, $files['name'],'', $files['type'], $interior);
				$adb->pquery($sql2, $params2);
				//Add relation
				$sql3 = 'INSERT INTO vtiger_salesmanattachmentsrel VALUES(?,?)';
				$params3 = array($id[1],$crm_id);
				$adb->pquery($sql3, $params3);	
				$ImageUrl = $site_URL.$interior.$crm_id.'_'. $files['name'];
				$adb->pquery('update vtiger_users set imagename=? where id=?',array($files['name'],$id[1]));
				$response = new Webapp_API_Response();
				$message = vtranslate('User Image Uploaded Successfully','Webapp');
				$response->setResult(array("message"=>$message,"ImageUrl"=>$ImageUrl));
				return $response;
			}else{
				$response = new Webapp_API_Response();
				$message = vtranslate('Image Not Uploading, please try again','Webapp');
				$response->setError(403,$message);
				return $response; 
			} 
			      
        }else{
			$response = new Webapp_API_Response();
			$message = vtranslate('Please upload User Image','Webapp');
			$response->setError(403,'Please upload User Image');
			return $response; 
		}
		
	}
		
}
