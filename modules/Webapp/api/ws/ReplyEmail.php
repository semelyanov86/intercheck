<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_ReplyEmail extends Webapp_WS_FetchRecord {

	function getFromEmailAddress() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$fromEmail = false;
		$result = $db->pquery('SELECT email1 FROM vtiger_users WHERE is_admin=?', array('on'));
		if ($db->num_rows($result)) {
			$fromEmail = decode_html($db->query_result($result, 0, 'email1'));
		}
		if (empty($fromEmail)) $fromEmail = $currentUserModel->get('email1');
		return $fromEmail;
	}
	
	function process(Webapp_API_Request $request) {

		global $current_user,$upload_badext;
		$current_user = Users::getActiveAdminUser();
		$adb = PearDatabase::getInstance();
		$moduleName = 'Emails';
		$documentIds = '';
		$signature = 'Yes';
		// This is either SENT or SAVED
		$flag = 'SENT';
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['file'];
		$fromEmail = $this->getFromEmailAddress();
		$recordId = trim($request->get('record'));

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId,$moduleName);
			$recordModel->set('mode', 'edit');
		}else{
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');
		}
		
		$parentEmailId = $request->get('parent_id',null);
		$attachmentsWithParentEmail = array();
		if(!empty($parentEmailId) && !empty ($recordId)) {
			$parentEmailModel = Vtiger_Record_Model::getInstanceById($parentEmailId);
			$attachmentsWithParentEmail = $parentEmailModel->getAttachmentDetails();
		}
		$existingAttachments = $request->get('attachments',array());
		if(empty($recordId)) {
			if(is_array($existingAttachments)) {
				foreach ($existingAttachments as $index =>  $existingAttachInfo) {
					$existingAttachInfo['tmp_name'] = $existingAttachInfo['name'];
					$existingAttachments[$index] = $existingAttachInfo;
					if(array_key_exists('docid',$existingAttachInfo)) {
						$documentIds[] = $existingAttachInfo['docid'];
						unset($existingAttachments[$index]);
					}

				}
			}
		}else{
			//If it is edit view unset the exising attachments
			//remove the exising attachments if it is in edit view

			$attachmentsToUnlink = array();
			$documentsToUnlink = array();

			foreach($attachmentsWithParentEmail as $i => $attachInfo) {
				$found = false;
				foreach ($existingAttachments as $index =>  $existingAttachInfo) {
					if($attachInfo['fileid'] == $existingAttachInfo['fileid']) {
						$found = true;
						break;
					}
				}
				//Means attachment is deleted
				if(!$found) {
					if(array_key_exists('docid',$attachInfo)) {
						$documentsToUnlink[] = $attachInfo['docid'];
					}else{
						$attachmentsToUnlink[] = $attachInfo;
					}
				}
				unset($attachmentsWithParentEmail[$i]);
			}
			//Make the attachments as empty for edit view since all the attachments will already be there
			$existingAttachments = array();
			if(!empty($documentsToUnlink)) {
				$recordModel->deleteDocumentLink($documentsToUnlink);
			}

			if(!empty($attachmentsToUnlink)){
				$recordModel->deleteAttachment($attachmentsToUnlink);
			}

		}

		// This will be used for sending mails to each individual
		$toMailInfo = $request->get('toemailinfo');

		$to = $request->get('to');
		
		$content = $request->getRaw('description');
		$processedContent = Emails_Mailer_Model::getProcessedContent($content); // To remove script tags
		$mailerInstance = Emails_Mailer_Model::getInstance();
		$processedContentWithURLS = decode_html($mailerInstance->convertToValidURL($processedContent));
		$recordModel->set('description', $processedContentWithURLS);
		$recordModel->set('subject', trim($request->get('subject')));
		$recordModel->set('toMailNamesList',$request->get('toMailNamesList'));
		$recordModel->set('saved_toid', $to);
		$recordModel->set('ccmail', trim($request->get('cc')));
		$recordModel->set('bccmail', trim($request->get('bcc')));
		$recordModel->set('assigned_user_id', $current_user->id);
		$recordModel->set('email_flag', $flag);
		$recordModel->set('documentids', $documentIds);
		$recordModel->set('signature',$signature);
		$recordModel->set('from_email',$fromEmail);
		$recordModel->set('toemailinfo', $toMailInfo);
		$recordModel->set('source','API');

		foreach($toMailInfo as $recordId=>$emailValueList) {
			if($recordModel->getEntityType($recordId) == 'Users'){
				$parentIds .= $recordId.'@-1|';
			}else{
				$parentIds .= $recordId.'@1|';
			}
		}
		$recordModel->set('parent_id', $parentIds);

		//save_module still depends on the $_REQUEST, need to clean it up
		$_REQUEST['parent_id'] = $parentIds;
		$success = false;
		if($recordModel->checkUploadSize($documentIds)){
			// Fix content format acceptable to be preserved in table.
			$decodedHtmlDescriptionToSend = $recordModel->get('description');
			$recordModel->set('description', to_html($decodedHtmlDescriptionToSend));
			$recordModel->save();
			// Restore content to be dispatched through HTML mailer.
			
			$ownerId = $recordModel->get('assigned_user_id');
			$date_var = date("Y-m-d H:i:s");
			if(is_array($existingAttachments)){
				foreach ($existingAttachments as $index =>  $existingAttachInfo) {
					$file_name = $existingAttachInfo['attachment'];
					$path = $existingAttachInfo['path'];
					$fileId = $existingAttachInfo['fileid'];

					$oldFileName = $file_name;
					//SEND PDF mail will not be having file id
					if(!empty ($fileId)) {
						$oldFileName = $existingAttachInfo['fileid'].'_'.$file_name;
					}
					$oldFilePath = $path.'/'.$oldFileName;

					$binFile = sanitizeUploadFileName($file_name, $upload_badext);

					$current_id = $adb->getUniqueID("vtiger_crmentity");

					$filename = ltrim(basename(" " . $binFile)); //allowed filename like UTF-8 characters
					$filetype = $existingAttachInfo['type'];
					$filesize = $existingAttachInfo['size'];

					//get the file path inwhich folder we want to upload the file
					$upload_file_path = decideFilePath();
					$newFilePath = $upload_file_path . $current_id . "_" . $binFile;

					copy($oldFilePath, $newFilePath);

					$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
					$params1 = array($current_id, $ownerId, $ownerId, $moduleName . " Attachment", $recordModel->get('description'), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
					$adb->pquery($sql1, $params1);

					$sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
					$params2 = array($current_id, $filename, $recordModel->get('description'), $filetype, $upload_file_path);
					$result = $adb->pquery($sql2, $params2);

					$sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
					$adb->pquery($sql3, array($recordModel->getId(), $current_id));
				}
			}

			$success = true;
			if($flag == 'SENT') {
				$status = $recordModel->send();
				if($status == true) {
					// This is needed to set vtiger_email_track table as it is used in email reporting
					$recordModel->setAccessCountValue();
					$response = new Webapp_API_Response();
					$message = vtranslate('Mail has been Sent','Webapp');
					$response->setResult(array("code"=>"1","message"=>$message));
					return $response;
				}else{
					$success = false;
					$message = $status;
					$response = new Webapp_API_Response();
					$response->setResult(array("code"=>"0","message"=>$message));
					return $response;
				}
			}

		}else{

			$message = vtranslate('LBL_MAX_UPLOAD_SIZE', $moduleName).' '.vtranslate('LBL_EXCEEDED', $moduleName);
			throw new WebServiceException('',$message);
		}
		
	}

}

?>
