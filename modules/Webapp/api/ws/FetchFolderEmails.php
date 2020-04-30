<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_FetchFolderEmails extends Webapp_WS_FetchRecord {
	
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
	
	function process(Webapp_API_Request $request) {
		$foldername = trim($request->get('foldername'));
		$maxEntriesPerPage = trim($request->get('maxEntriesPerPage'));
		$page_number = trim($request->get('page_number'));
			
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$response = new Webapp_API_Response();
		if ($foldername!='' && $maxEntriesPerPage!='' && $page_number!='') {
			$connector = $this->getConnector($foldername);
			$index = $page_number - 1;
			$folder = $connector->folderInstance($foldername);
			$folderMails = $this->getMails($folder, $index, $maxEntriesPerPage);	
		   $folderCheck = @imap_check($connector->mBox);
		   if(($page_number*$maxEntriesPerPage) >= $folderCheck->Nmsgs){
				$response->setResult(array('pageInfo' =>$folder->pageInfo(),'email'=>$folderMails, 'module'=>'MailManager', 'message'=>'','totalemail'=>$folderCheck->Nmsgs,'isLast'=>true));
		   }else{
			
			$response->setResult(array('pageInfo' =>$folder->pageInfo(),'email'=>$folderMails, 'module'=>'MailManager', 'message'=>'','totalemail'=>$folderCheck->Nmsgs,'isLast'=>false));
		   }
		}else{
			$message = vtranslate('Required field is missing','Webapp');
			throw new WebServiceException(404,$message);
		}	
		
			return $response;
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
	
	public function getMails($foldername, $start, $maxLimit) {
		
		$connector = $this->getConnector($this->mFolder);
		
		 $folderCheck = @imap_check($connector->mBox);
		if ($folderCheck->Nmsgs) {

			$reverse_start = $folderCheck->Nmsgs - ($start*$maxLimit);
			$reverse_end = $reverse_start - $maxLimit + 1;

			if ($reverse_start < 1) $reverse_start = 1;
			if ($reverse_end < 1) $reverse_end = 1;

			$sequence = sprintf("%s:%s", $reverse_start, $reverse_end);

			$records = imap_fetch_overview($connector->mBox, $sequence);
			krsort($records);
			$data = array();
			

			foreach($records as $result) {
				$msgno = $result->msgno;
				$header = @imap_header($connector->mBox, $msgno);
				$struct = @imap_fetchstructure($connector->mBox , $msgno);
				$seen = $result->seen;
				if(isset($struct->parts[0]->parts))
				{
 				  $existAttachments = true;
				}else{
				   $existAttachments = false;
				} 
				$from = $header->from;
				$fromname = $from[0]->personal;
				$fromemail = $from[0]->mailbox.'@'.$from[0]->host;
				
					$date = $header->udate;
					if ($date) {
						$maildate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $date));
					}
					$mails = array('fromname' => $fromname,'from' => $fromemail, 'subject' => $header->subject,
						'msgno' => $header->Msgno, 'maildate'=> $maildate , 'attachment'=>$existAttachments,'seen'=>$seen);
				
				
				$data[]= array("message"=>$mails,'mailid'=>$header->Msgno);
			}
			$foldername->setPaging($reverse_end, $reverse_start, $maxLimit, $folderCheck->Nmsgs, $start);
			return $data;
		}
	}
	
	public function getMailboxModel() {
		if ($this->mMailboxModel === false) {
			$this->mMailboxModel = MailManager_Mailbox_Model::activeInstance();
		}
		return $this->mMailboxModel;
	}
	
}
