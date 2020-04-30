<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_SearchEmail extends Webapp_WS_FetchRecord {
	
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

	static $DB_CACHE_CLEAR_INTERVAL = "-1 day"; // strtotime

	/*
	 * Mail Box URL
	*/
	public $mBoxUrl;

	/*
	 * Mail Box connection instance
	*/
	public $mBox;
	
	function process(Webapp_API_Request $request) {

		//$response = new MailManager_Response();
		$folder = trim($request->get('folderName'));
		$maxEntriesPerPage = $request->get('maxEntriesPerPage');
		$page = trim($request->get('page_number'));
		$q = trim($request->get('search_value'));
		$type = trim($request->get('search_type'));
		if($type == 'DATE'){
			$type = 'ON';
		}
			
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
				
		
		$response = new Webapp_API_Response();
		if ($folder!='' && $maxEntriesPerPage!='' && $page!='' && $q!='') {			
			
				if(empty($type)) {
					$type='ALL';
				}
				if($type == 'ON') {
					$dateFormat = $currentUserModel->get('date_format');
					if ($dateFormat == 'mm-dd-yyyy') {
						$dateArray = explode('-', $q);
						$temp = $dateArray[0];
						$dateArray[0] = $dateArray[1];
						$dateArray[1] = $temp;
						$q = implode('-', $dateArray);
					}
					$query = date('d M Y',strtotime($q));
					$q = ''.$type.' "'.vtlib_purify($query).'"';
				} else {
					$q = ''.$type.' "'.vtlib_purify($q).'"';
				}
				$connector = $this->getConnector($folder);
				$folder = $connector->folderInstance($foldername);
				$folderMails = $this->searchMails($q, $folder, $page, $maxEntriesPerPage);
				
			if(empty($folderMails)){
				$folderMails = array();
			}
			
			$response->setResult(array('pageInfo' =>$folder->pageInfo(),'search_email'=>$folderMails, 'module'=>'MailManager', 'message'=>''));
			return $response;
		}else{
			$message = vtranslate('Required field is missing','Webapp');
			throw new WebServiceException(404,$message);
		}	
	}

	public function searchMails($query, $folder, $start, $maxLimit) {
		$connector = $this->getConnector();
		$nos = imap_search($connector->mBox, $query);
		if (!empty($nos)) {
			$nmsgs = count($nos);

			$reverse_start = $nmsgs - ($start*$maxLimit);
			$reverse_end   = $reverse_start - $maxLimit;

			if ($reverse_start < 1) $reverse_start = 1;
			if ($reverse_end < 1) $reverse_end = 0;

			if($nmsgs > 1)
				$nos = array_slice($nos, $reverse_end, ($reverse_start-$reverse_end));

			// Reverse order the messages
			rsort($nos, SORT_NUMERIC);

			$mails = array();
			$records = imap_fetch_overview($connector->mBox, implode(',', $nos));
			krsort($records);
			// to make sure this should not break in Vtiger6
			

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
						'msgno' => $header->Msgno, 'maildate'=> $maildate, 'attachment'=>$existAttachments,'seen'=>$seen);
				
				
				$data[]= array("message"=>$mails,'mailid'=>$header->Msgno);
			}
			$folder->setPaging($reverse_end, $reverse_start, $maxLimit, $nmsgs, $start);
			return $data;
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
