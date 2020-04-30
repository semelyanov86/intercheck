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

require_once 'include/utils/utils.php';
include_once 'include/Webservices/Query.php';
require_once 'include/Webservices/QueryRelated.php';
include_once 'modules/MailManager/MailManager.php';


class Webapp_WS_AttachedEmail extends Webapp_WS_FetchRecord {
	
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
	
	var $_attachments;
	
	function process(Webapp_API_Request $request) {
		
		$db = PearDatabase::getInstance();
			global $current_user,$adb, $site_URL;
			$current_user = $this->getActiveUser();
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
			$linkto = trim($request->get('linkto'));
			if(!empty($linkto)){
				$foldername = trim($request->get('folderName'));
				$msgno = trim($request->get('mailid'));
				
				$connector = $this->getConnector($foldername);
				
				$mail = $connector->openMail($msgno,$foldername);
				
				$this->_attachments = $mail->attachments();
				
 				// This is to handle larger uploads
				$memory_limit = MailManager_Config_Model::get('MEMORY_LIMIT');
				ini_set('memory_limit', $memory_limit);
			
				$linkedto = MailManager_Relate_Action::associate($mail, $linkto);
				
				$response = new Webapp_API_Response();
				$message = vtranslate('Email Attached Successfully','Webapp');
				$response->setResult(array('module'=>'MailManager', 'message'=>$message, 'linkedto' => $linkedto));
				return $response;
			}else{
				$message = vtranslate('Select at least one record to attach','Webapp');
				throw new WebServiceException(404,$message);
			}
	}
	

	public function getConnector($folder='') {
		if (!$this->mConnector || ($this->mFolder != $folder)) {
			
			if($folder == "__vt_drafts") {
				$draftController = new MailManager_Draft_View();
				$this->mConnector = $draftController->connectorWithModel();
			} else {
				if ($this->mConnector) $this->mConnector->close();

				$model = $this->getMailboxModel();
				$this->mConnector = MailManager_Connector_Connector::connectorWithModel($model, $folder);
			}
			$this->mFolder = $folder;
		}
		return $this->mConnector;
	}

	public function getMailboxModel() {
		if ($this->mMailboxModel === false) {
			$this->mMailboxModel = MailManager_Mailbox_Model::activeInstance();
		}
		return $this->mMailboxModel;
	}
	
}
