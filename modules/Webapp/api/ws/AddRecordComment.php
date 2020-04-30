<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/SaveRecord.php';

class Webapp_WS_AddRecordComment extends Webapp_WS_SaveRecord {
	
	function process(Webapp_API_Request $request) {
		global $current_user,$adb, $site_URL;
		$values = Zend_Json::decode($request->get('values'));
		$commentId = $request->get('modcommentsid');
		if (!$values) {
		    $values = array();
		    $values['assigned_user_id'] = $request->get('assigneeId');
        }
        if ($commentId) {
            $values['modcommentsid'] = $commentId;
        }
		$relatedTo = trim($values['related_to']);
		if (!$relatedTo) {
		    $relatedTo = $request->get('related_to');
		    $values['related_to'] = $relatedTo;
        }
		$commentContent = $values['commentcontent'];
		if (!$commentContent) {
		    $commentContent = trim($request->get('content'));
		    $values['commentcontent'] = $commentContent;
        }

		if(empty($relatedTo)){		
			$message = vtranslate('Required fields not found','Webapp');		
			throw new WebServiceException(404,$message);		
		}		
		if(empty($commentContent)){		
			$message = vtranslate('Required fields not found','Webapp');		
			throw new WebServiceException(404,$message);		
		}

		$user = $this->getActiveUser();
		$targetModule = 'ModComments';
		$response = false;
		if (vtlib_isModuleActive($targetModule)) {
			$request->set('module', $targetModule);
			$values['assigned_user_id'] = sprintf('%sx%s', Webapp_WS_Utils::getEntityModuleWSId('Users'), $user->id);
			
			$request->set('values', Zend_Json::encode($values) );

			$response = parent::process($request);
			$id = $response->result['id'];

			if(!empty($id)){

				$record = explode('x',$id);
				$modcommentsid = $record[1];
				$query = "SELECT vtiger_modcomments.*, vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime, vtiger_crmentity.smownerid from vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid where vtiger_crmentity.deleted = 0 and vtiger_modcomments.modcommentsid = ? ";

				$getCommentQuery = $adb->pquery($query, array($modcommentsid));
				$countComment = $adb->num_rows($getCommentQuery);

				$modcommentId = $adb->query_result($getCommentQuery, 0, 'modcommentsid');
				$commentcontent = $adb->query_result($getCommentQuery, 0, 'commentcontent');
				$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
				$commentcontent = html_entity_decode($commentcontent, ENT_QUOTES, $default_charset);
				$relatedTo = $adb->query_result($getCommentQuery, 0, 'related_to');
				$filenames = $adb->query_result($getCommentQuery, $i, 'filename');
				if($filenames != '' && $filenames != '0'){
					$files = explode(',',$filenames);
				}else{
					$files = array();
				}
				$Attachments = array();
				foreach ($files as $key => $fileid) {
					$filename = "";
					$file_URL = "";
					$fileAccess =  true;
					$AccessMessage = "";
					if($fileid != '' && $fileid != 0){
						$fileDetails = Webapp_WS_Utils::getAttachments($fileid,$modcommentId);
						$filename = $fileDetails['filename'];
						$file_URL = $fileDetails['file_URL'];
						$file_URL = $site_URL.'modules/Webapp/api/ws/DownloadUrl.php?record='.$fileid;
						$ext = pathinfo($fileDetails['file_URL'], PATHINFO_EXTENSION);
						if(file_get_contents($file_URL) == ""){
							$fileAccess = false;
							$AccessMessage = vtranslate("You don't have permission to access this resource",'Webapp');
						}
					}
					$Attachments[] = array('filename'=>$filename,'file_URL'=>$file_URL,'fileAccess'=>$fileAccess,'AccessMessage'=>$AccessMessage,'extension'=>$ext);
				}
				$parent_comments = $adb->query_result($getCommentQuery, 0, 'parent_comments');
				$userId = $adb->query_result($getCommentQuery, 0, 'smownerid');
				$createdtime = $adb->query_result($getCommentQuery, 0, 'createdtime');
				$modifiedtime = $adb->query_result($getCommentQuery, 0, 'modifiedtime');
				$isModified = false;
				$modifiedText = "";
				if($createdtime != $modifiedtime){
					$isModified = true;
					$modifiedtime = Vtiger_Util_Helper::formatDateDiffInStrings($modifiedtime);
					$modifiedText = vtranslate('LBL_COMMENT','ModComments').' '.strtolower(vtranslate('LBL_MODIFIED','ModComments')).' '.$modifiedtime;
				}
				
				$commentedtime = Vtiger_Util_Helper::formatDateDiffInStrings($createdtime);
				if($userId) {
					$userRecordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');
					$firstname = $userRecordModel->get('first_name');
					$firstname = html_entity_decode($firstname, ENT_QUOTES, $default_charset);
					$lastname = $userRecordModel->get('last_name');
					$lastname = html_entity_decode($lastname, ENT_QUOTES, $default_charset);
					$userImage = Webapp_WS_Utils::getUserImage($userId);
				}
				$isEdit = false;
				if(Users_Privileges_Model::isPermitted('ModComments', 'EditView')){
					if($userId == $current_user->id){
						$isEdit = true;
					}
				}

				$commentsWSid = Webapp_WS_Utils::getEntityModuleWSId('ModComments');
				$modcommentsData = array('modcommentId'=>$commentsWSid.'x'.$modcommentId, 'commentcontent'=>$commentcontent, 'relatedTo' => $relatedTo,'parent_comments'=>$commentsWSid.'x'.$parent_comments, 'userid'=>$userId,'attachments'=>$Attachments,'userName'=>$firstname." ".$lastname,'userImage'=>$userImage, 'createdtime'=>$createdtime,'ModifiedTime'=>$commentedtime,'isEdit'=>$isEdit,'isModified'=>$isModified,'modifiedText'=>$modifiedText);
				$response = new Webapp_API_Response();
				$response->setResult(array('record'=>$modcommentsData,'message'=>vtranslate('Comment saved successfully','Webapp')));
			}else{
				$response = new Webapp_API_Response();
				$response->setResult(array('record'=>array(),'message'=>vtranslate('Comment not saved','Webapp')));
			}
		
		}else{
			$response = new Webapp_API_Response();
			$message = vtranslate('Comment module is not active','Webapp');
			$response->setError(403,$message);
		}
		return $response;
	}
}
