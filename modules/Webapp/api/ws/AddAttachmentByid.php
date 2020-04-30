<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_AddAttachmentByid extends Webapp_WS_FetchRecord {
	
	protected $mConnector = false;

	/**
	 * MailBox folder name
	 * @var string
	 */
	protected $mFolder = false;

	/**
	 * Connector to the IMAP server
	 * @var MailManager_Mailbox_Model
	 */
	protected $mMailboxModel = false;

	var $mUid;
	function process(Webapp_API_Request $request) {
			$linkto = trim($request->get('_mlinkto'));
			$attachid = trim($request->get('attachid'));
			$attachid = Zend_Json::decode($attachid);
			if(!empty($linkto) && !empty($attachid)){
				
				foreach($attachid as $id){
					if(!empty($id)){
						$this->addDocument($id,$linkto);
					}
				}
				$response = new Webapp_API_Response();
				$message = vtranslate('Documents Attached Successfully','Webapp');
				$response->setResult(array("result"=>$message));
				return $response;
			}else{
				$message = vtranslate('Please select one or more attachments','Webapp');
				throw new WebServiceException(404,$message);
			}	
	}
	

	public function addDocument($attachid,$linkto){
		global $current_user,$adb, $site_URL;
			$current_user = $this->getActiveUser();
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$params = array($attachid);
			$filteredColumns = "aname, attachid, path, cid";
			$atResult = $adb->pquery("SELECT {$filteredColumns} FROM vtiger_mailmanager_mailattachments
						WHERE attachid=?", $params);
		if ($adb->num_rows($atResult)) {
				$aname = $adb->query_result($atResult,0,'aname');
				$path = $adb->query_result($atResult,0,'path'); 
		}
		$oldfile = $site_URL.$path.$attachid.'_'.$aname;
		$filesize = filesize($oldfile);	
		$user = $current_user->id;
		
		$DocumentrecordModel = Vtiger_Record_Model::getCleanInstance('Documents');
		$DocumentrecordModel->set('notes_title',$aname);
		$DocumentrecordModel->set('assigned_user_id',$user);
		$DocumentrecordModel->set('notecontent',$aname);
		$DocumentrecordModel->set('filename',$aname);
		$DocumentrecordModel->set('filetype','');
		$DocumentrecordModel->set('filesize',$filesize);
		$DocumentrecordModel->set('filelocationtype','I');
		$DocumentrecordModel->set('filestatus','1');
		$DocumentrecordModel->set('filedownloadcount','0');
		$DocumentrecordModel->set('folderid','1');
		$DocumentrecordModel->set('source','MailManager');
		$DocumentrecordModel->save();
		$recordModel = Vtiger_Record_Model::getInstanceById($linkto);
		$module = $recordModel->get('record_module');
			
		$parentModuleName = $module;
		$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
		$parentRecordId = $linkto;
		$relatedModule = $DocumentrecordModel->getModule();
		$relatedRecordId = $DocumentrecordModel->getId();
		if($relatedModule->getName() == 'Events'){
			$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
		}

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
		$relationModel->addRelation($parentRecordId, $relatedRecordId);
	
		$fileDetails = array('filename'=>$aname,'oldfile'=>$oldfile);
		$this->uploadAndSaveFile($relatedRecordId,$module,$fileDetails);
	     
	}

	function uploadAndSaveFile($id, $module, $file_details){
		global $log;
		$log->debug("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

		global $adb, $current_user;
		global $upload_badext;

		$date_var = date("Y-m-d H:i:s");
		$current_id = $adb->getUniqueID("vtiger_crmentity");
		$file_name = $file_details['filename'];
		$filetmp_name = $file_details['oldfile'];

		//get the file path inwhich folder we want to upload the file
		$upload_file_path = decideFilePath();

		$upload_status = copy($filetmp_name, $upload_file_path . $current_id . "_" . $file_name);
		// temporary file will be deleted at the end of request
		$save_file = 'true';
		if ($save_file == 'true' && $upload_status == 1) {
				//Only one Attachment per entity delete previous attachments
				$res = $adb->pquery('SELECT vtiger_seattachmentsrel.attachmentsid FROM vtiger_seattachmentsrel 
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seattachmentsrel.attachmentsid AND vtiger_crmentity.setype = ? 
									WHERE vtiger_seattachmentsrel.crmid = ?',array($module.' Attachment',$id));
				$oldAttachmentIds = array();
				for($attachItr = 0;$attachItr < $adb->num_rows($res);$attachItr++) {
					$oldAttachmentIds[] = $adb->query_result($res,$attachItr,'attachmentsid');
				}
				if(count($oldAttachmentIds)) {
					$adb->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid IN ('.generateQuestionMarks($oldAttachmentIds).')',$oldAttachmentIds);
					
			}
			//Add entry to crmentity
			$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?,?,?,?,?,?,?)";
			$params1 = array($current_id, $current_user->id, $current_user->id,$module.' Attachment', $description, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			$adb->pquery($sql1, $params1);
			//Add entry to attachments
			$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
			$params2 = array($current_id, $file_name, $description, $filetype, $upload_file_path);
			$adb->pquery($sql2, $params2);
			//Add relation
			$sql3 = 'INSERT INTO vtiger_seattachmentsrel(crmid,attachmentsid) VALUES(?,?)';
			$params3 = array($id, $current_id);
			$adb->pquery($sql3, $params3);
			return $current_id;
		} else {
			//failed to upload file
			return false;
		}
	}
	
}
