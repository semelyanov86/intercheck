<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

require_once 'include/utils/utils.php';
include_once 'include/Webservices/Query.php';
require_once 'include/Webservices/QueryRelated.php';
include_once 'modules/MailManager/MailManager.php';

class Webapp_WS_FetchEmailById extends Webapp_WS_FetchRecord {
	
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
	static $MODULES = array ( 'Contacts', 'Accounts', 'Leads', 'HelpDesk', 'Potentials');
	var $mUid;
	var $_attachments;
	
	function process(Webapp_API_Request $request) {
		
			$db = PearDatabase::getInstance();
			global $current_user,$adb, $site_URL;
			$current_user = $this->getActiveUser();
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$msgno = trim($request->get('mailid'));
			$folderName = trim($request->get("folderName"));
			$response = new Webapp_API_Response();
			if ($msgno!='' ) {
				$connector = $this->getConnector($folderName);
				$msgno = trim($msgno);
				$connector->markMailRead($msgno);
				
				
				$header = @imap_header($connector->mBox, $msgno);
				
				$from = $header->from;
				$fromname = $from[0]->personal;

			$mail = $connector->openMail($msgno,$folderName);
			
		
			$this->_attachments = $mail->attachments();
			
			$this->mUid = $mail->muid();
			$attachments = $this->loadAttachmentsFromDB(true,false,false);

			$linkedto = self::associatedLink($mail->_uniqueid);

			if (empty($linkedto)) {
				$relatedrecords = array();
				$allowedModules = $this->getCurrentUserMailManagerAllowedModules();

				foreach (self::$MODULES as $MODULE) {
					if(!in_array($MODULE, $allowedModules)) continue;

					//lookup will be from email other than sent mail folder 
					$lookupEmail = $mail->from();
					
					$folder = $connector->folderInstance($folderName);
					$isSentFolder = $folder->isSentFolder();
					//if its sent folder, lookup email will be first TO email
					if($isSentFolder) {
						$toEmail = $mail->to();
						$toEmail = explode(',', $toEmail);
						$lookupEmail = $toEmail[0];
					}
					if(empty($lookupEmail)) continue;

					$lookupResults = $this->lookupModuleRecordsWithEmail($MODULE, $lookupEmail);
					
					foreach ($lookupResults as $lookupResult) {
						if(array_key_exists('parent', $lookupResult)) {
							$lookupResult['module'] = getSalesEntityType($lookupResult['id']);
							$relatedrecords[] = $lookupResult;
						}else{
							$lookupResult['module'] = $MODULE;
							$relatedrecords[] = $lookupResult;
						}
					}
				}
				$linkedto = $relatedrecords;
			}else{
				$module = $linkedto['module'];
				$moduleWSId = Webapp_WS_Utils::getEntityModuleWSId($module);
				$linkedto['wsid'] = $moduleWSId.'x'.$linkedto['record'];
				$linkedto['id'] = $linkedto['record'];
				unset($linkedto['record']);
				$relatedrecords[] = $linkedto;
			}
			
			if (empty($linkedto)) {
				$mailaction = 0;
			}else{
				$mailaction = 1;
			}
			
			$date = $mail->_date;
			if ($date) {
				$maildate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $date));
			}

			$actionlist = $this->mailActionlist($mailaction,$current_user);
			
			$response->setResult(array('mailaction' => $mailaction,'actionlist'=>$actionlist,'fromname' => $fromname,'from' => $mail->from(), 'subject' => $mail->subject(),
					'msgno' => $mail->msgNo(), 'msguid' => $mail->uniqueid(), 'to' => $mail->to(),'cc' => $mail->cc(),'bcc' => $mail->bcc(),'attachments' => $attachments, 'linkedto'=> $linkedto, 'relatedrecords'=> $relatedrecords , 'maildate'=> $maildate ,'email_body'=>$mail->body(), 'module'=>'MailManager', 'message'=>''));
			}else{
				$message = vtranslate('Required field is missing','Webapp');
				throw new WebServiceException(404,$message);
			}
		return $response;
			
	}

	public function mailActionlist($mailaction,$current_user){
		$actionlist = array();
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$presence = array('0', '2');
		if ($mailaction == 1) {
			$linkToAvailableActions = MailManager_Relation_View::linkToAvailableActions();
		}else{
			$linkToAvailableActions = MailManager_Relation_View::getCurrentUserMailManagerAllowedModules();
			
		}
		foreach($linkToAvailableActions as $moduleName) {
			 $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			 if (($userPrivModel->isAdminUser() ||
						$userPrivModel->hasGlobalReadPermission() ||
						$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
			 	$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
			 	if($createAction){
					 if ($moduleName == 'Calendar'){
						 $label = vtranslate("LBL_ADD_CALENDAR", 'MailManager',$current_user->language);
						 $actionlist[] = array('moduleName' => $moduleName, 'label'=>$label); 
						 
						 $label1 = vtranslate("LBL_ADD_EVENTS", 'MailManager',$current_user->language);
						 $actionlist[] = array('moduleName' => 'Events', 'label'=>$label1); 
					 }else{
						  $label = vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager',$current_user->language);
						  $actionlist[] = array('moduleName' => $moduleName, 'label'=>$label); 
					 }
			 	}
			}
		}
		return $actionlist;
	}
	
	public function loadAttachmentsFromDB($withContent, $aName=false, $aId=false) {
		global $site_URL;
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if (!empty($this->_attachments)) {
			$this->_attachments = array();
			$mail =new MailManager_Message_Model();
			$params = array($currentUserModel->getId(), $this->mUid);
			$filteredColumns = "aname, attachid, path, cid";

			$whereClause = "";
			if ($aName) { $whereClause .= " AND aname=?"; $params[] = $aName; }
			if ($aId)   { $whereClause .= " AND aid=?"; $params[] = $aId; }

			$atResult = $db->pquery("SELECT {$filteredColumns} FROM vtiger_mailmanager_mailattachments
						WHERE userid=? AND muid=? $whereClause", $params);
			if ($db->num_rows($atResult)) {
				for($atIndex = 0; $atIndex < $db->num_rows($atResult); ++$atIndex) {
					$atResultRow = $db->raw_query_result_rowdata($atResult, $atIndex);
					if($withContent) {
						$binFile = sanitizeUploadFileName($atResultRow['aname'], vglobal('upload_badext'));
						$saved_filename = $atResultRow['path'] . $atResultRow['attachid']. '_' .$binFile;
						if(file_exists($saved_filename)) $fileContent = @fread(fopen($saved_filename, "r"), filesize($saved_filename));
					}
					if(!empty($atResultRow['cid'])) {
						$mail->_inline_attachments[] = array('filename'=>$atResultRow['aname'], 'cid'=>$atResultRow['cid']);
					}
					$filePath = $atResultRow['path'].$atResultRow['attachid'].'_'.sanitizeUploadFileName($atResultRow['aname'], vglobal('upload_badext'));
					$fileSize = $this->convertFileSize(filesize($filePath));
					$data = ($withContent? $fileContent: false);
					$this->_attachments[] = array('filename'=>$atResultRow['aname'], 'data' => $data, 'size' => $fileSize, 'path' => $filePath, 'attachid' => $atResultRow['attachid']);
					$file[] = array('attachid'=>$atResultRow['attachid'],'filepath'=>$site_URL.$filePath,'filesize'=>$fileSize,'filename'=>$atResultRow['aname']);
					// Clear immediately
				}
				return $file; 
				 // Indicate cleanup
			}else{
				return array();
			}
		}else{
			return array();
		}
	}

	function convertFileSize($size) {
		$type = 'Bytes';
		if($size > 1048575) {
			$size = round(($size/(1024*1024)), 2);
			$type = 'MB';
		} else if($size > 1023) {
			$size = round(($size/1024), 2);
			$type = 'KB';
		}
		return $size.' '.$type;
	}

	public function getCurrentUserMailManagerAllowedModules() {
		$moduleListForCreateRecordFromMail = array('Contacts', 'Accounts', 'Leads', 'HelpDesk', 'Calendar', 'Potentials');

		foreach($moduleListForCreateRecordFromMail as $module) {
			if(MailManager::checkModuleWriteAccessForCurrentUser($module)) {
				$mailManagerAllowedModules[] = $module;
			}
		}
		return $mailManagerAllowedModules;
	}
	
	public function lookupModuleRecordsWithEmail($module, $email) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$results = array();
		$activeEmailFields = array();

		$handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
		$meta = $handler->getMeta();
		$emailFields = $meta->getEmailFields();
		$moduleFields = $meta->getModuleFields();
		
		
		foreach($emailFields as $emailFieldName){
			$emailFieldInstance = $moduleFields[$emailFieldName];
			if(!(((int)$emailFieldInstance->getPresence()) == 1)) {
				$activeEmailFields[] = $emailFieldName;
			}
		}
		
		//before calling vtws_query, need to check Active Email Fields are there or not
		if(count($activeEmailFields) > 0) {
			$query = $this->buildSearchQuery($module, $email[0], 'EMAIL');
			
			
			$qresults = vtws_query( $query, $currentUserModel );
			$describe = $this->ws_describe($module);
			$labelFields = explode(',', $describe['labelFields']);

			//overwrite labelfields with field names instead of column names
			$fieldColumnMapping = $meta->getFieldColumnMapping();
			$columnFieldMapping = array_flip($fieldColumnMapping);

			foreach ($labelFields as $i => $columnname) {
				$labelFields[$i] = $columnFieldMapping[$columnname];
			}

			foreach($qresults as $qresult) {
				$labelValues = array();
				foreach($labelFields as $fieldname) {
					if(isset($qresult[$fieldname])) $labelValues[] = $qresult[$fieldname];
				}
				$ids = vtws_getIdComponents($qresult['id']);
				$results[] = array( 'wsid' => $qresult['id'], 'id' => $ids[1], 'label' => implode(' ', $labelValues));
			}
		}
		return $results;
	}
	
	public $wsDescribeCache = array();
	public function ws_describe($module) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!isset($this->wsDescribeCache[$module])) {
			$this->wsDescribeCache[$module] = vtws_describe( $module, $currentUserModel);
		}
		return $this->wsDescribeCache[$module];
	}

	/**
	 * Funtion used to build Web services query
	 * @param String $module - Name of the module
	 * @param String $text - Search String
	 * @param String $type - Tyoe of fields Phone, Email etc
	 * @return String
	 */
	public function buildSearchQuery($module, $text, $type) {
		$describe = $this->ws_describe($module);
		// to check whether fields are accessible to current_user or not
		$labelFields = explode(',',$describe['labelFields']);

		//overwrite labelfields with field names instead of column names
		$currentUserModel = vglobal('current_user');
		$handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
		$meta = $handler->getMeta();
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		$columnFieldMapping = array_flip($fieldColumnMapping);
		foreach ($labelFields as $i => $columnname) {
			$labelFields[$i] = $columnFieldMapping[$columnname];
		}

		foreach($labelFields as $fieldName){
			foreach($describe['fields'] as $describefield){
				if($describefield['name'] == $fieldName){
					$searchFields[] = $fieldName;
					break;
				}
			}
		}
		$whereClause = '';
		foreach($describe['fields'] as $field) {
			if (strcasecmp($type, $field['type']['name']) === 0) {
				$whereClause .= sprintf( " %s LIKE '%%%s%%' OR", $field['name'], $text );
			}
		}
		return sprintf( "SELECT %s FROM %s WHERE %s;", implode(',',$searchFields), $module, rtrim($whereClause, 'OR') );
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

	public static function associatedLink($mailuid) {
		$info = self::lookupMailAssociation($mailuid);
		if ($info) {
			return MailManager_Relate_Action::getSalesEntityInfo($info['crmid']);
		}
		return false;
	}

	static function lookupMailAssociation($mailuid) {
		global $adb;

		// Mail could get associated with two-or-more records if they get deleted after linking.
		$result = $adb->pquery(
			"SELECT vtiger_mailmanager_mailrel.* FROM vtiger_mailmanager_mailrel INNER JOIN
			vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_mailmanager_mailrel.crmid AND vtiger_crmentity.deleted=0
			AND vtiger_mailmanager_mailrel.mailuid=? ", array($mailuid));
		if ($adb->num_rows($result)) {
			for($i = 0;$i < $adb->num_rows($result);$i++){
				$resultrow[] = $adb->fetch_array($result,$i);
			}
			return $resultrow;
		}
		return false;
	}
	
}
