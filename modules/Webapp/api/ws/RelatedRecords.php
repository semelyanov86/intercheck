<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

class Webapp_WS_RelatedRecords extends Webapp_WS_FetchRecordWithGrouping {
		protected $record = false;
		function process(Webapp_API_Request $request) {
			global $adb, $current_user;
			$current_user = $this->getActiveUser();
			global $site_URL;
			$presence = array('0', '2');
			$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
			$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
			$db = PearDatabase::getInstance();
			$query = "SELECT vtiger_tab.name FROM  `vtiger_relatedlists` INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_relatedlists.tabid WHERE vtiger_relatedlists.label =  'Documents'";
			$result = $db->pquery($query,array());
			$num_rows = $db->num_rows($result);
			$documentModules = array();
			for($i=0;$i<$num_rows;$i++){
				$documentModules[] = $db->query_result($result,$i,'name');
			}
			$inventoryModules = array('Invoice','SalesOrder','PurchaseOrder','Quotes');
			$query1 = "SELECT vtiger_tab.name, vtiger_tab.tabid FROM vtiger_links INNER JOIN vtiger_tab ON vtiger_links.tabid = vtiger_tab.tabid where vtiger_links.linklabel = ?";
			$params1 = array("DetailViewBlockCommentWidget");
			$results1 = $db->pquery($query1 , $params1);
			$numrows1 = $db->num_rows($results1);
			$CommentsModule = array();
			for($i=0;$i<$numrows1;$i++){
				$CommentsModule[] = $db->query_result($results1,$i,'name');
			}

			if(in_array($request->get('module'), $inventoryModules)){
				if(Inventory_Module_Model::isCommentEnabled()){
					$CommentsModule[] = $request->get('module');
				}
			}

			$query2 = "SELECT vtiger_tab.name, vtiger_tab.tabid FROM vtiger_relatedlists INNER JOIN vtiger_tab ON vtiger_relatedlists.tabid = vtiger_tab.tabid where vtiger_relatedlists.presence = 0 AND vtiger_relatedlists.label=?";
			$params2 = array("Activities");
			$result2 = $db->pquery($query2 , $params2);
			$numrows2 = $db->num_rows($result2);
			$ActivitiesModule = array();
			for($i=0;$i<$numrows2;$i++){
				$ActivitiesModule[] = $db->query_result($result2,$i,'name');
			}
			$record = trim($request->get('record'));
			$module = trim($request->get('module'));
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$FieldModels = $moduleModel->getFields();

			$wsid = Webapp_WS_Utils::getEntityModuleWSId($module);
			$records = explode('x',$record);
			$parentId = $records[1];
			$request->set('record',$record);
			$request->set('module',$module);
			$response = parent::process($request);
			$resultRecord = $response->result['record'];
			$recordLabel = $resultRecord['recordLabel'];
			$relatedRecordList = array();
			$fields = array();
			foreach($resultRecord['blocks'] as $key => $blocks){
				foreach($blocks['fields'] as $fieldkey => $field){
					if($field['summaryfield'] != 1){
					}else{
						$filename = $field['name'];
						$FieldModel = $FieldModels[$filename];
						$displaytype = $FieldModel->get('displaytype');
						if($displaytype == 1){
							$fields['fields'][] = $field;
						}
					}
				}
			}
			if(empty($fields)){
				$relatedRecordList['summary'] = $fields;
			}else{
				$relatedRecordList['summary'][] = $fields;
			}
			
			$query = $db->pquery('SELECT `tabid` FROM `vtiger_tab` WHERE `name`= ?',array($module));
			$tabid = $db->query_result($query,0,'tabid');
			if($module == 'Potentials' ){
				$moduleModel = Vtiger_Module_Model::getInstance('Documents');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Documents
				$limitQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_notes.title, vtiger_notes.folderid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime, vtiger_notes.filename, vtiger_notes.filelocationtype, vtiger_notes.filestatus, vtiger_attachments.path, vtiger_attachments.attachmentsid,vtiger_attachments.type FROM vtiger_notes inner join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid left join vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid inner join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0 inner join vtiger_crmentity crm2 on crm2.crmid=vtiger_senotesrel.crmid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid =vtiger_notes.notesid left join vtiger_attachments on vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id where crm2.crmid=? AND vtiger_notes.filestatus = 1 LIMIT 0,5';
				$params = array($parentId);
				$result = $db->pquery($limitQuery, $params);
				$Documents['fields'] = array('title'=>vtranslate('Title',$module),'filename'=>vtranslate('File Name',$module));
				if($db->num_rows($result)>0){
					for($i=0; $i< $db->num_rows($result); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Documents');
						$Documents['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($result,$i,'crmid');
						$Documents['records'][$i]['title'] = $db->query_result($result,$i,'title');
						$Documents['records'][$i]['filename'] = $db->query_result($result,$i,'filename');
						$Documents['records'][$i]['filelocationtype'] = $db->query_result($result,$i,'filelocationtype');
						$Documents['records'][$i]['modifiedtime'] = Vtiger_DateTime_UIType::getDisplayDateTimeValue($db->query_result($result,$i,'modifiedtime'));
						$attachmentsid = $db->query_result($result,$i,'attachmentsid');
						$path = $db->query_result($result,$i,'path');
						$Documents['records'][$i]['type'] = $db->query_result($result,$i,'type');
						$Documents['records'][$i]['path'] = $site_URL.$path.$attachmentsid.'_'.$Documents['records'][$i]['filename'];
						/*$recordid = $db->query_result($result,$i,'crmid');
						$getCommentsData = $this->getCommentsData($recordid);
						$Documents['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else {
					$Documents['records']=array();
				}
					$relatedRecordList['Documents'][] = $Documents;
				}
				
				$moduleModel = Vtiger_Module_Model::getInstance('ModComments');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Comments
				$query = "SELECT vtiger_modcomments.*, vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime, vtiger_crmentity.smownerid from vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid where vtiger_crmentity.deleted = 0 and vtiger_modcomments.related_to = ? ORDER BY vtiger_modcomments.modcommentsid DESC LIMIT 0,5";
				$getCommentQuery = $db->pquery($query, array($parentId));
				$countComment = $db->num_rows($getCommentQuery);
				$Comments['fields'] = array('commentcontent'=>vtranslate('Comment','ModComments'),'related_to'=>vtranslate('Related To','ModComments'));
				if($countComment > 0){
					for($i=0;$i<$countComment;$i++) {
						$modcommentId = $db->query_result($getCommentQuery, $i, 'modcommentsid');
						$commentcontent = $db->query_result($getCommentQuery, $i, 'commentcontent');
						$commentcontent = html_entity_decode($commentcontent, ENT_QUOTES, $default_charset);
						$relatedTo = $db->query_result($getCommentQuery, $i, 'related_to');
						
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
						$userId = $db->query_result($getCommentQuery, $i, 'smownerid');
						$createdtime = $db->query_result($getCommentQuery, $i, 'createdtime');
						$commentedtime = Vtiger_Util_Helper::formatDateDiffInStrings($createdtime);
						$modifiedtime = $adb->query_result($getCommentQuery, $i, 'modifiedtime');
						$isModified = false;
						$modifiedText = "";
						if($createdtime != $modifiedtime){
							$isModified = true;
							$modifiedtime = Vtiger_Util_Helper::formatDateDiffInStrings($modifiedtime);
							$modifiedText = vtranslate('LBL_COMMENT','ModComments').' '.strtolower(vtranslate('LBL_MODIFIED','ModComments')).' '.$modifiedtime;
						}
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
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('ModComments');
						$Comments['records'][$i] = array('modcommentId'=>$tabid.'x'.$modcommentId, 'commentcontent'=>$commentcontent, 'relatedTo' => $relatedTo, 'userid'=>$userId,'attachments'=>$Attachments,'userName'=>$firstname." ".$lastname,'userImage'=>$userImage,'createdtime'=>$createdtime,'ModifiedTime'=>$commentedtime,'isEdit'=>$isEdit,'isModified'=>$isModified,'modifiedText'=>$modifiedText);
					/*	$recordid = $modcommentId;
						$getCommentsData = $this->getCommentsData($recordid);
						$Comments['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else{
					$Comments['records']=array();
				}
					$relatedRecordList['ModComments'][] = $Comments;
				}

				$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Activities
				global $currentModule;
				$currentModule = $module;
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $module);
				$relatedModuleName = 'Calendar';
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel,$relatedModuleName,'Activities');
				$query = $relationListView->getRelationQuery();
				$query.= ' LIMIT 0,5';
				$Activities['fields'] = array('subject'=>vtranslate('Subject','Calendar'),'activitytype'=>vtranslate('Activity Type','Calendar'),'eventstatus'=>vtranslate('Status','Calendar'));
				$getfunctionres = $db->pquery($query,array());
				$numofrows2 = $db->num_rows($getfunctionres);
				if($numofrows2 > 0){
					for($i=0; $i< $numofrows2; $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
						$Activities['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($getfunctionres,$i,'crmid');
						$Activities['records'][$i]['subject'] = $db->query_result($getfunctionres,$i,'subject');
						$Activities['records'][$i]['activitytype'] = $db->query_result($getfunctionres,$i,'activitytype');
						$Activities['records'][$i]['eventstatus'] = $db->query_result($getfunctionres,$i,'eventstatus');
						$Activities['records'][$i]['date_start'] = $db->query_result($getfunctionres,$i,'date_start');
						$Activities['records'][$i]['time_start'] = $db->query_result($getfunctionres,$i,'time_start');
						$Activities['records'][$i]['due_date'] = $db->query_result($getfunctionres,$i,'due_date');
						$Activities['records'][$i]['time_end'] = $db->query_result($getfunctionres,$i,'time_end');
						$Activities['records'][$i]['modifiedtime'] = Vtiger_Util_Helper::formatDateDiffInStrings($db->query_result($getfunctionres,$i,'modifiedtime'));
						$recordid = $db->query_result($getfunctionres,$i,'crmid');
						/*$getCommentsData = $this->getCommentsData($recordid);
						$Activities['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else{
					$Activities['records']=array();
				}
					$relatedRecordList['Activities'][] = $Activities;
				}

				$moduleModel = Vtiger_Module_Model::getInstance('Products');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Products
				$productQuery = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_products.productname, vtiger_products.product_no, vtiger_products.discontinued, vtiger_products.productcategory, vtiger_products.qtyinstock, vtiger_products.productcode, vtiger_products.unit_price, vtiger_products.commissionrate, vtiger_products.qty_per_unit FROM vtiger_products INNER JOIN vtiger_seproductsrel ON vtiger_products.productid = vtiger_seproductsrel.productid and vtiger_seproductsrel.setype = 'Potentials' INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid INNER JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_seproductsrel.crmid LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE vtiger_crmentity.deleted = 0 AND vtiger_potential.potentialid = ? LIMIT 0,5";
				$params = array($parentId);
				$productresult = $db->pquery($productQuery, $params);
				$Products['fields'] = array('productname'=>vtranslate('Product Name',$module),'unit_price'=>vtranslate('Unit Price',$module));
				if($db->num_rows($productresult)>0){
					for($i=0; $i< $db->num_rows($productresult); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Products');
						$Products['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($productresult,$i,'crmid');
						$Products['records'][$i]['productname'] = $db->query_result($productresult,$i,'productname');
						$Products['records'][$i]['unit_price'] = $db->query_result($productresult,$i,'unit_price');
						$recordid = $db->query_result($productresult,$i,'crmid');
						/*$getCommentsData = $this->getCommentsData($recordid);
						$Products['records'][$i]['commentData'] = $getCommentsData;*/
					}
			   }else {
			   		$Products['records'] = array();
			   }
			   		$relatedRecordList['Products'][] = $Products;
			   }

			   $moduleModel = Vtiger_Module_Model::getInstance('Contacts');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
			   //Contacts
				$contactQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.phone, vtiger_contactdetails.email, vtiger_contactdetails.accountid, vtiger_contactdetails.title, vtiger_crmentity.smownerid, vtiger_contactaddress.mailingcity, vtiger_contactaddress.mailingcountry FROM vtiger_potential left join vtiger_contpotentialrel on vtiger_contpotentialrel.potentialid = vtiger_potential.potentialid inner join vtiger_contactdetails on ((vtiger_contactdetails.contactid = vtiger_contpotentialrel.contactid) or (vtiger_contactdetails.contactid = vtiger_potential.contact_id)) INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid left join vtiger_account on vtiger_account.accountid = vtiger_contactdetails.accountid left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id where vtiger_potential.potentialid = ? and vtiger_crmentity.deleted=0 LIMIT 0,5';
				$params = array($parentId);
				$contactresult = $db->pquery($contactQuery, $params);
				$Contacts['fields'] = array('firstname'=>vtranslate('First Name',$module),'lastname'=>vtranslate('Last Name',$module),'phone'=>vtranslate('Phone',$module),'email'=>vtranslate('Primary Email',$module),'mailingcity'=>vtranslate('City',$module),'mailingcountry'=>vtranslate('Country',$module));
				if($db->num_rows($contactresult)>0){
					for($i=0; $i< $db->num_rows($contactresult); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Contacts');
						$Contacts['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($contactresult,$i,'crmid');
						$Contacts['records'][$i]['firstname'] = $db->query_result($contactresult,$i,'firstname');
						$Contacts['records'][$i]['lastname'] = $db->query_result($contactresult,$i,'lastname');
						$Contacts['records'][$i]['phone'] = $db->query_result($contactresult,$i,'phone');
						$Contacts['records'][$i]['email'] = $db->query_result($contactresult,$i,'email');
						$Contacts['records'][$i]['mailingcity'] = $db->query_result($contactresult,$i,'mailingcity');
						$Contacts['records'][$i]['mailingcountry'] = $db->query_result($contactresult,$i,'mailingcountry');
						$recordid = $db->query_result($contactresult,$i,'crmid');
						/*$getCommentsData = $this->getCommentsData($recordid);
						$Contacts['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else{
					$Contacts['records'] =  array();
				}
					$relatedRecordList['Contacts'][] = $Contacts;
				}

			}else if($module == 'Project'){
				//Project Key Metrics
				$summaryinfo = $this->getSummaryInfo($parentId);
				$relatedRecordList['Project Key Metrics'] = $summaryinfo;

				$moduleModel = Vtiger_Module_Model::getInstance('Documents');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Documents 
				 $limitQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_notes.title, vtiger_notes.folderid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime, vtiger_notes.filename, vtiger_notes.filelocationtype, vtiger_notes.filestatus, vtiger_attachments.path, vtiger_attachments.attachmentsid,vtiger_attachments.type FROM vtiger_notes inner join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid left join vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid inner join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0 inner join vtiger_crmentity crm2 on crm2.crmid=vtiger_senotesrel.crmid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid =vtiger_notes.notesid left join vtiger_attachments on vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id where crm2.crmid=? AND vtiger_notes.filestatus = 1 LIMIT 0,5';
				$params = array($parentId);
				$result = $db->pquery($limitQuery, $params);
				$Documents['fields'] = array('title'=>vtranslate('Title',$module),'filename'=>vtranslate('File Name',$module));
				if($db->num_rows($result)>0){
					for($i=0; $i< $db->num_rows($result); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Documents');
						$Documents['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($result,$i,'crmid');
						$Documents['records'][$i]['title'] = $db->query_result($result,$i,'title');
						$Documents['records'][$i]['filename'] = $db->query_result($result,$i,'filename');
						$Documents['records'][$i]['filelocationtype'] = $db->query_result($result,$i,'filelocationtype');
						$Documents['records'][$i]['modifiedtime'] = Vtiger_DateTime_UIType::getDisplayDateTimeValue($db->query_result($result,$i,'modifiedtime'));
						$attachmentsid = $db->query_result($result,$i,'attachmentsid');
						$path = $db->query_result($result,$i,'path');
						$Documents['records'][$i]['type'] = $db->query_result($result,$i,'type');
						$Documents['records'][$i]['path'] = $site_URL.$path.$attachmentsid.'_'.$Documents['records'][$i]['filename'];
						/*$recordid = $db->query_result($result,$i,'crmid');
						$getCommentsData = $this->getCommentsData($recordid);
						$Documents['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else {
					$Documents['records']=array();
				}
					$relatedRecordList['Documents'][] = $Documents;
				}
				
				$moduleModel = Vtiger_Module_Model::getInstance('ModComments');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Comments
				$query = "SELECT vtiger_modcomments.*, vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime, vtiger_crmentity.smownerid from vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid where vtiger_crmentity.deleted = 0 and vtiger_modcomments.related_to = ? ORDER BY vtiger_modcomments.modcommentsid DESC LIMIT 0,5";
				$getCommentQuery = $db->pquery($query, array($parentId));
				$countComment = $db->num_rows($getCommentQuery);
					$Comments['fields'] = array('commentcontent'=>vtranslate('Comment','ModComments'),'related_to'=>vtranslate('Related To','ModComments'));
				if($countComment > 0){
					for($i=0;$i<$countComment;$i++) {
						$modcommentId = $db->query_result($getCommentQuery, $i, 'modcommentsid');
						$commentcontent = $db->query_result($getCommentQuery, $i, 'commentcontent');
						$commentcontent = html_entity_decode($commentcontent, ENT_QUOTES, $default_charset);
						$relatedTo = $db->query_result($getCommentQuery, $i, 'related_to');
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
						$userId = $db->query_result($getCommentQuery, $i, 'smownerid');
						$createdtime = $db->query_result($getCommentQuery, $i, 'createdtime');
						$commentedtime = Vtiger_Util_Helper::formatDateDiffInStrings($createdtime);
						$modifiedtime = $adb->query_result($getCommentQuery, $i, 'modifiedtime');
						$isModified = false;
						$modifiedText = "";
						if($createdtime != $modifiedtime){
							$isModified = true;
							$modifiedtime = Vtiger_Util_Helper::formatDateDiffInStrings($modifiedtime);
							$modifiedText = vtranslate('LBL_COMMENT','ModComments').' '.strtolower(vtranslate('LBL_MODIFIED','ModComments')).' '.$modifiedtime;
						}
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
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('ModComments');
						$Comments['records'][$i] = array('modcommentId'=>$tabid.'x'.$modcommentId, 'commentcontent'=>$commentcontent, 'relatedTo' => $relatedTo, 'userid'=>$userId,'attachments'=>$Attachments, 'userName'=>$firstname." ".$lastname,'userImage'=>$userImage,'createdtime'=>$createdtime,'ModifiedTime'=>$commentedtime,'isEdit'=>$isEdit,'isModified'=>$isModified,'modifiedText'=>$modifiedText);
						/*$recordid = $modcommentId;
						$getCommentsData = $this->getCommentsData($recordid);
						$Comments['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else{
					$Comments['records']=array();
				}
					$relatedRecordList['ModComments'][] = $Comments;
				}

				$moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Tickets 
				 $ticketsQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_troubletickets.priority, vtiger_troubletickets.title, vtiger_troubletickets.parent_id, vtiger_troubletickets.contact_id, vtiger_crmentity.smownerid, vtiger_troubletickets.status, vtiger_troubletickets.severity, vtiger_troubletickets.ticket_no, vtiger_crmentity.description FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid) LEFT JOIN vtiger_ticketcf ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE vtiger_crmentity.deleted = 0 AND (vtiger_crmentityrel.crmid = ? OR vtiger_crmentityrel.relcrmid = ?) ORDER BY vtiger_crmentity.modifiedtime DESC LIMIT 0,5';
				 $params = array($parentId,$parentId);
				$ticketsresult = $db->pquery($ticketsQuery, $params);
				$Tickets['fields'] = array('title'=>vtranslate('Title',$module),'priority'=>vtranslate('Priority',$module));
				if($db->num_rows($ticketsresult)>0){
					for($i=0; $i< $db->num_rows($ticketsresult); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('Tickets');
						$Tickets['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($ticketsresult,$i,'crmid');
						$Tickets['records'][$i]['title'] = $db->query_result($ticketsresult,$i,'title');
						$Tickets['records'][$i]['priority'] = $db->query_result($ticketsresult,$i,'priority');
						$recordid = $db->query_result($ticketsresult,$i,'crmid');
						/*$getCommentsData = $this->getCommentsData($recordid);
						$Tickets['records'][$i]['commentData'] = $getCommentsData;*/
					}
				}else{
					$Tickets['records'] = array();
				}
					$relatedRecordList['Tickets'][] = $Tickets;
				}

				$moduleModel = Vtiger_Module_Model::getInstance('ProjectMilestone');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
				//Milestone 
				 $milestoneQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_projectmilestone.projectmilestonename, vtiger_projectmilestone.projectmilestonedate, vtiger_projectmilestone.projectmilestonetype FROM vtiger_projectmilestone INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projectmilestone.projectmilestoneid LEFT JOIN vtiger_projectmilestonecf ON vtiger_projectmilestonecf.projectmilestoneid = vtiger_projectmilestone.projectmilestoneid INNER JOIN vtiger_project AS vtiger_projectProject ON vtiger_projectProject.projectid = vtiger_projectmilestone.projectid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE vtiger_crmentity.deleted = 0 AND vtiger_projectProject.projectid = ? LIMIT 0,5';
				 $params = array($parentId);
				$milestoneresult = $db->pquery($milestoneQuery, $params);
				$ProjectMilestone['fields'] = array('projectmilestonename'=>vtranslate('Project Milestone Name',$module),'projectmilestonedate'=>vtranslate('Milestone Date',$module));
				if($db->num_rows($milestoneresult)>0){
					for($i=0; $i< $db->num_rows($milestoneresult); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('ProjectMilestone');
						$ProjectMilestone['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($milestoneresult,$i,'crmid');
						$ProjectMilestone['records'][$i]['projectmilestonename'] = $db->query_result($milestoneresult,$i,'projectmilestonename');
						$ProjectMilestone['records'][$i]['projectmilestonedate'] = $db->query_result($milestoneresult,$i,'projectmilestonedate');
						$recordid = $db->query_result($milestoneresult,$i,'crmid');
						
					}
				}else{
		 	 		$ProjectMilestone['records'] = array();
				}
				$relatedRecordList['ProjectMilestone'][] = $ProjectMilestone;
				}

				$moduleModel = Vtiger_Module_Model::getInstance('ProjectTask');
				if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
		 	 	//ProjectTask 
				 $taskQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_projecttask.projecttaskname, vtiger_projecttask.projecttasktype, vtiger_crmentity.smownerid, vtiger_projecttask.projecttaskprogress, vtiger_projecttask.startdate, vtiger_projecttask.enddate, vtiger_projecttask.projecttaskstatus FROM vtiger_projecttask INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid LEFT JOIN vtiger_projecttaskcf ON vtiger_projecttaskcf.projecttaskid = vtiger_projecttask.projecttaskid INNER JOIN vtiger_project AS vtiger_projectProject ON vtiger_projectProject.projectid = vtiger_projecttask.projectid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE vtiger_crmentity.deleted = 0 AND vtiger_projectProject.projectid = ? LIMIT 0,5';
				 $params = array($parentId);
				$taskresult = $db->pquery($taskQuery, $params);
				$ProjectTask['fields'] = array('projecttaskname'=>vtranslate('Project Task Name',$module),'projecttaskprogress'=>vtranslate('Progress',$module),'projecttaskstatus'=>vtranslate('Status',$module));
				if($db->num_rows($taskresult)>0){
					for($i=0; $i< $db->num_rows($taskresult); $i++ ) {
						$tabid = Webapp_WS_Utils::getEntityModuleWSId('ProjectTask');
						$ProjectTask['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($taskresult,$i,'crmid');
						$ProjectTask['records'][$i]['projecttaskname'] = $db->query_result($taskresult,$i,'projecttaskname');
						$ProjectTask['records'][$i]['projecttaskprogress'] = $db->query_result($taskresult,$i,'projecttaskprogress');
						$ProjectTask['records'][$i]['projecttaskstatus'] = $db->query_result($taskresult,$i,'projecttaskstatus');
						$recordid = $db->query_result($taskresult,$i,'crmid');
						
					}
				}else{
						$ProjectTask['records'] = array();
				}
				$relatedRecordList['ProjectTask'][] = $ProjectTask;
				}
			}else{
				
				if(in_array($module,$documentModules)){
					$moduleModel = Vtiger_Module_Model::getInstance('Documents');
					if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
					//Documents
					$limitQuery = 'SELECT DISTINCT vtiger_crmentity.crmid,vtiger_notes.title, vtiger_notes.folderid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime, vtiger_notes.filename, vtiger_notes.filelocationtype, vtiger_notes.filestatus, vtiger_attachments.path, vtiger_attachments.attachmentsid,vtiger_attachments.type FROM vtiger_notes inner join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid left join vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid inner join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0 inner join vtiger_crmentity crm2 on crm2.crmid=vtiger_senotesrel.crmid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid =vtiger_notes.notesid left join vtiger_attachments on vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id where crm2.crmid=? AND vtiger_notes.filestatus = 1 LIMIT 0,5';
					$params = array($parentId);
					$result = $db->pquery($limitQuery, $params);
					$Documents['fields'] = array('title'=>vtranslate('Title',$module),'filename'=>vtranslate('File Name',$module));
					if($db->num_rows($result)>0){
						for($i=0; $i< $db->num_rows($result); $i++ ) {
							$tabid = Webapp_WS_Utils::getEntityModuleWSId('Documents');
							$Documents['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($result,$i,'crmid');
							$Documents['records'][$i]['title'] = $db->query_result($result,$i,'title');
							$Documents['records'][$i]['filename'] = $db->query_result($result,$i,'filename');
							$Documents['records'][$i]['filelocationtype'] = $db->query_result($result,$i,'filelocationtype');
							$Documents['records'][$i]['modifiedtime'] = Vtiger_DateTime_UIType::getDisplayDateTimeValue($db->query_result($result,$i,'modifiedtime'));
							$attachmentsid = $db->query_result($result,$i,'attachmentsid');
							$path = $db->query_result($result,$i,'path');
							$Documents['records'][$i]['type'] = $db->query_result($result,$i,'type');
							$Documents['records'][$i]['path'] = $site_URL.$path.$attachmentsid.'_'.$Documents['records'][$i]['filename'];
							$record = $db->query_result($result,$i,'crmid');
							$Documents['records'][$i]['actionUrl'] = $site_URL.'/modules/Webapp/api/ws/DownloadUrl.php?record='.$record;
						}
					}else {
						$Documents['records']=array();
					}
					$relatedRecordList['Documents'][] = $Documents;
					}
				}
				
				if(in_array($module,$CommentsModule)){
					$moduleModel = Vtiger_Module_Model::getInstance('ModComments');
					if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
					$query = "SELECT vtiger_modcomments.*, vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime, vtiger_crmentity.smownerid from vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid where vtiger_crmentity.deleted = 0 and vtiger_modcomments.related_to = ? ORDER BY vtiger_modcomments.modcommentsid DESC LIMIT 0,5";
					$getCommentQuery = $db->pquery($query, array($parentId));
					$countComment = $db->num_rows($getCommentQuery);
						$Comments['fields'] = array('commentcontent'=>vtranslate('Comment','ModComments'),'related_to'=>vtranslate('Related To','ModComments'));
					if($countComment > 0){
						for($i=0;$i<$countComment;$i++) {
							$modcommentId = $db->query_result($getCommentQuery, $i, 'modcommentsid');
							$commentcontent = $db->query_result($getCommentQuery, $i, 'commentcontent');
							$commentcontent = html_entity_decode($commentcontent, ENT_QUOTES, $default_charset);
							$relatedTo = $db->query_result($getCommentQuery, $i, 'related_to');
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
							$userId = $db->query_result($getCommentQuery, $i, 'smownerid');
							$createdtime = $db->query_result($getCommentQuery, $i, 'createdtime');
							$commentedtime = Vtiger_Util_Helper::formatDateDiffInStrings($createdtime);
							$modifiedtime = $adb->query_result($getCommentQuery, $i, 'modifiedtime');
							$isModified = false;
							$modifiedText = "";
							if($createdtime != $modifiedtime){
								$isModified = true;
								$modifiedtime = Vtiger_Util_Helper::formatDateDiffInStrings($modifiedtime);
								$modifiedText = vtranslate('LBL_COMMENT','ModComments').' '.strtolower(vtranslate('LBL_MODIFIED','ModComments')).' '.$modifiedtime;
							}
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
							$tabid = Webapp_WS_Utils::getEntityModuleWSId('ModComments');
							$Comments['records'][$i] = array('modcommentId'=>$tabid.'x'.$modcommentId, 'commentcontent'=>$commentcontent, 'relatedTo' => $relatedTo, 'userid'=>$userId,'attachments'=>$Attachments,'userName'=>$firstname." ".$lastname,'userImage'=>$userImage,'createdtime'=>$createdtime,'ModifiedTime'=>$commentedtime,'isEdit'=>$isEdit,'isModified'=>$isModified,'modifiedText'=>$modifiedText);
						}
					}else{
						$Comments['records']=array();
					}
					$relatedRecordList['ModComments'][] = $Comments;
					}
						
				}

				if(in_array($module,$ActivitiesModule)){
					$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
					if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
					global $currentModule;
					$currentModule = $module;
					$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $module);
					$relatedModuleName = 'Calendar';
					$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel,$relatedModuleName,'Activities');
					$query = $relationListView->getRelationQuery();
					$query.= ' LIMIT 0,5';
					$Activities['fields'] = array('subject'=>vtranslate('Subject','Calendar'),'activitytype'=>vtranslate('Activity Type','Calendar'),'eventstatus'=>vtranslate('Status','Calendar'));
					$getfunctionres = $db->pquery($query,array());
					$numofrows2 = $db->num_rows($getfunctionres);
					if($numofrows2 > 0){
						for($i=0; $i< $numofrows2; $i++ ) {
							$tabid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
							$Activities['records'][$i]['crmid'] = $tabid.'x'.$db->query_result($getfunctionres,$i,'crmid');
							$Activities['records'][$i]['subject'] = $db->query_result($getfunctionres,$i,'subject');
							$Activities['records'][$i]['activitytype'] = $db->query_result($getfunctionres,$i,'activitytype');
							$Activities['records'][$i]['eventstatus'] = $db->query_result($getfunctionres,$i,'eventstatus');
							$Activities['records'][$i]['date_start'] = $db->query_result($getfunctionres,$i,'date_start');
							$Activities['records'][$i]['time_start'] = $db->query_result($getfunctionres,$i,'time_start');
							$Activities['records'][$i]['due_date'] = $db->query_result($getfunctionres,$i,'due_date');
							$Activities['records'][$i]['time_end'] = $db->query_result($getfunctionres,$i,'time_end');
							$Activities['records'][$i]['modifiedtime'] = Vtiger_Util_Helper::formatDateDiffInStrings($db->query_result($getfunctionres,$i,'modifiedtime'));
						}
					}else{
						$Activities['records']=array();
					}
					$relatedRecordList['Activities'][] = $Activities;
					}
				}
				if(count($relatedRecordList) > 0){
					
				}else{
					$relatedRecordList = (object)array();
				}
			}
			$relatedModulesList =  array();
			$count = 0;
			foreach($relatedRecordList as $key => $values){
				$relatedModulesList[$count]['related_module_name'] = $key;
				if(in_array($key,array('summary','Activities'))){
					if($key == 'summary'){
						$key = 'LBL_KEY_FIELD';
					}else{
						$key = 'LBL_'.strtoupper($key);
					}
				}
				if($key == 'Tickets'){
					$relatedModulesList[$count]['related_module_label'] = vtranslate('HelpDesk','HelpDesk');
				}else{
					$relatedModulesList[$count]['related_module_label'] = vtranslate($key,'Vtiger');
				}
				$relatedModulesList[$count]['related_module_list'] = $values;
				$count++;
			}

			//code start for permission
			
			$related['related_records']= $relatedModulesList;
			$related['recordLabel'] = $recordLabel;
			$related['editAction'] = $resultRecord['editAction'];
			$related['deleteAction'] = $resultRecord['deleteAction'];
			$related['duplicateAction'] = $resultRecord['duplicateAction'];
			$related['commentModuleAccess'] = $resultRecord['commentModuleAccess'];
			$related['ActivityModuleAccess'] = $resultRecord['ActivityModuleAccess'];
			$related['isAttachmentSupport'] = $resultRecord['isAttachmentSupport'];
			$related['recordShortcut'] = $resultRecord['recordShortcut'];
			if($module == 'Leads'){
				$related['ConvertLead'] = $resultRecord['ConvertLead'];
			}
			//code for image url
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $module);
			$imageDetails = $parentRecordModel->getImageDetails();
			if(!empty($imageDetails)){
				global $site_URL;
				$related['ImageUrl'] = $site_URL.$imageDetails[0]['path'].'_'.$imageDetails[0]['name'];
			}else{
				$related['ImageUrl'] = "";
			}
			//code end for permission
			
			$response = new Webapp_API_Response();
			$response->setResult($related);
			return $response;
			
			
		}
		
		public function getCommentsData($record){
			global $current_user;
			global $site_URL;
			$current_user = $this->getActiveUser();
			$adb = PearDatabase::getInstance();
			$query = "SELECT vtiger_modcomments.*, vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime, vtiger_crmentity.smownerid from vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid where vtiger_crmentity.deleted = 0 and vtiger_modcomments.related_to = ? ORDER BY vtiger_modcomments.modcommentsid DESC LIMIT 0,5";
			$getCommentQuery = $adb->pquery($query, array($record));
			$countComment = $adb->num_rows($getCommentQuery);
			$modcommentsData = array();
			for($i=0;$i<$countComment;$i++) {
				$modcommentId = $adb->query_result($getCommentQuery, $i, 'modcommentsid');
				$commentcontent = $adb->query_result($getCommentQuery, $i, 'commentcontent');
				$relatedTo = $adb->query_result($getCommentQuery, $i, 'related_to');
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
				$userId = $adb->query_result($getCommentQuery, $i, 'smownerid');
				$createdtime = $adb->query_result($getCommentQuery, $i, 'createdtime');
				$commentedtime = Vtiger_Util_Helper::formatDateDiffInStrings($createdtime);
				$modifiedtime = $adb->query_result($getCommentQuery, $i, 'modifiedtime');
				$isModified = false;
				$modifiedText = "";
				if($createdtime != $modifiedtime){
					$isModified = true;
					$modifiedtime = Vtiger_Util_Helper::formatDateDiffInStrings($modifiedtime);
					$modifiedText = vtranslate('LBL_COMMENT','ModComments').' '.strtolower(vtranslate('LBL_MODIFIED','ModComments')).' '.$modifiedtime;
				}
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
				$modcommentsData[] = array('modcommentId'=>'31x'.$modcommentId, 'commentcontent'=>$commentcontent, 'relatedTo' => $relatedTo, 'userid'=>$userId,'attachments'=>$Attachments, 'userName'=>$firstname." ".$lastname,'userImage'=>$userImage, 'createdtime'=>$createdtime,'ModifiedTime'=>$commentedtime,'isEdit'=>$isEdit,'isModified'=>$isModified,'modifiedText'=>$modifiedText);
			}
			return array('countComment'=>$countComment,'comments'=>$modcommentsData);
		}
		
		public function getSummaryInfo($id) {
			
				$adb = PearDatabase::getInstance();

				$query ='SELECT smownerid,enddate,projecttaskstatus,projecttaskpriority
						FROM vtiger_projecttask
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
									AND vtiger_crmentity.deleted=0
								WHERE vtiger_projecttask.projectid = ? ';

				$result = $adb->pquery($query, array($id));

				$tasksOpen = $taskProgress= $taskCompleted = $taskDue = $taskDeferred = $numOfPeople = 0;
				$highTasks = $lowTasks = $normalTasks = $otherTasks = 0;
				$currentDate = date('Y-m-d');
				$inProgressStatus = array('Open', 'In Progress');
				$usersList = array();

				while($row = $adb->fetchByAssoc($result)) {
					$projectTaskStatus = $row['projecttaskstatus'];
					switch($projectTaskStatus){
						case 'Open'		: $tasksOpen++;		break;
						case 'In Progress' : $taskProgress++;break;
						case 'Deferred'	: $taskDeferred++;	break;
						case 'Completed': $taskCompleted++;	break;
					}
					$projectTaskPriority = $row['projecttaskpriority'];
					switch($projectTaskPriority){
						case 'high' : $highTasks++;break;
						case 'low' : $lowTasks++;break;
						case 'normal' : $normalTasks++;break;
						default : $otherTasks++;break;
					}

					if(!empty($row['enddate']) && (strtotime($row['enddate']) < strtotime($currentDate)) &&
							(in_array($row['projecttaskstatus'], $inProgressStatus))) {
						$taskDue++;
					}
					$usersList[] = $row['smownerid'];
				}

				$usersList = array_unique($usersList);
				$numOfPeople = count($usersList);

				$summaryInfo['projecttaskstatus'] =  array(array('Label'=>vtranslate('LBL_TASKS_OPEN','Project'),'value'=>$tasksOpen),
					array('Label'=>vtranslate('Progress','Project'),'value'=>$taskProgress),
					array('Label'=>vtranslate('LBL_TASKS_DUE','Project'),'value'=>$taskDue),
					array('Label'=>vtranslate('LBL_TASKS_COMPLETED','Project'),'value'=>$taskCompleted));

				$summaryInfo['projecttaskpriority'] =  array(array('Label'=>vtranslate('LBL_TASKS_HIGH','Project'),'value'=>$highTasks),
					array('Label'=>vtranslate('LBL_TASKS_NORMAL','Project'),'value'=>$normalTasks),
					array('Label'=>vtranslate('LBL_TASKS_LOW','Project'),'value'=>$lowTasks),
					array('Label'=>vtranslate('LBL_TASKS_OTHER','Project'),'value'=>$otherTasks));

			return $summaryInfo;
	}
	
}
