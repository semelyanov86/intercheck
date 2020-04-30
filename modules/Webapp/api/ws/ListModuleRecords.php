<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_ListModuleRecords extends Webapp_WS_Controller {
	public $totalQuery = "";
	public $totalParams = array();
	function isCalendarModule($module) {
		return ($module == 'Events' || $module == 'Calendar');
	}
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		
		return $this->processSearchRecordLabel($request);
	}

	function GroupDetails($pagingModel,$paging){
		global $adb;
		$index = $paging['index'];
		$size = $paging['size'];
		$limit = ($index*$size) - $size;
		$query = "SELECT * FROM vtiger_groups";
		if($index != '' && $size != '') {
			$query .= sprintf(" LIMIT %s, %s", $limit, $size);
		}
		$prequeryResult = $adb->pquery($query,array());
		$result = new SqlResultIterator($adb, $prequeryResult);
		$i = 0;
		$modifiedRecord = array();
		foreach($result as $record) {
			if ($record instanceof SqlResultIteratorRow) {
				$record = $record->data;
				$modifiedRecord[$i]['label'] = $modifiedRecord[$i]['id'] = "";
				foreach($record as $key => $values){
					//$modifiedRecord[$i]['modifiedtime']= null;
					//$modifiedRecord[$i]['assigned_user_id'] = null;
					if($key == 'groupid'){
						$modifiedRecord[$i]['id'] = '20x'.$values;
					}else if($key == 'groupname'){
						$modifiedRecord[$i]['label'] = $values;
					}
				}
			}
			$i =$i+1;
		}
		return $modifiedRecord;
	}
	
	function processSearchRecordLabel(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $current_user, $adb, $site_URL; // Few core API assumes this variable availability
		
		$current_user = $this->getActiveUser();
		
		$module = trim($request->get('module'));
		$alertid = trim($request->get('alertid'));
		$filterid = $request->get('filterid');
		if (!$filterid) {
            $filterid = $request->get('Filter');
        }
		$search = trim($request->get('search'));
		if (!$search) {
            $search = trim($request->get('Search'));
        }
		$index = trim($request->get('index'));
		$size = trim($request->get('size'));
		if (!$size) {
            $size = trim($request->get('Take'));
        }
		$type = trim($request->get('type'));
		$field_name = trim($request->get('field_name'));
		$field_name2 = trim($request->get('field_name2'));
		$field_value = addslashes(trim($request->get('field_value')));
		$field_value2 = trim($request->get('field_value2'));
		$order_by = trim($request->get('order_field'));
		$orderby = trim($request->get('orderby'));
		$display_params = $request->get('display_params');
		$search_params = $request->get('search_params');
		$params = Zend_Json::decode($display_params);
		//echo "<pre>";print_r($params);exit;
		$user_type = trim($request->get('user_type'));
		$related = trim($request->get('related'));
		$discontinued = trim($request->get('discontinued'));
		if(empty($discontinued)){
			$discontinued = 0;
		}
		$activitytype = "";
		$activitytype = trim($request->get('activitytype'));
		if($module == 'Calendar'){
			$activitytype = 'Task';
		}

		$searchType = trim($request->get('searchType'));


		if(!getTabid($module)){
			$message = vtranslate($module,$module)." ".vtranslate('Module does not exists','Webapp');
			throw new WebServiceException(404,'"'.vtranslate($module,$module).'" Module does not exists');
		}
		
		if($module == 'Groups'){
			$pagingModel = $this->getPagingModel($request);
			$paging = array('index'=>$index, 'size'=>$size);
			$modifiedRecords = array();
			$modifiedRecords = $this->GroupDetails($pagingModel,$paging);
			$response = new Webapp_API_Response();
			if(count($modifiedRecords) == 0) {
				$response->setResult(array('records'=>$modifiedRecords, 'module'=>$module, 'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'module_record_status'=>false));
			} else {
				$response->setResult(array('records'=>$modifiedRecords, 'module'=>$module, 'message'=>'','module_record_status'=>true));
			}
			return $response;
		}

		if($module == 'Events'){
			$modulename = 'Calendar';
		}else{
			$modulename = $module;
		}
		// code start for Entity Field By suresh /
		$entityQuery = $adb->pquery("SELECT * FROM vtiger_entityname WHERE modulename = ?",array($modulename));
		$entityField = $adb->query_result($entityQuery,0,'fieldname');
		$entityField_array = explode(',',$entityField);
		$entityField = $entityField_array[0];
		$tabid = getTabid($modulename);
		
		$entityQuery11 = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? and tabid= ?",array($entityField,$tabid));
		$fieldlabel = $adb->query_result($entityQuery11,0,'fieldlabel');
		$fieldlabel = vtranslate($fieldlabel,$modulename);
		if($module == 'Documents' && $entityField == 'title'){
			$entityField = 'notes_title';
		}
		if($module == 'HelpDesk' && $entityField == 'title'){
			$entityField = 'ticket_title';
		}
		$entityFields = array('label'=>$fieldlabel,'value'=>$entityField);
		
		
		$userId = array();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fieldModels = $moduleModel->getFields();
		$Modulefields = array_keys($fieldModels);
		//start validation of fieldname by suresh
		if(!empty($field_name) && !in_array($field_name,$Modulefields)){
			$message = vtranslate($field_name,$module)." ".vtranslate('Field does not exists','Webapp');
			throw new WebServiceException(404,$message);
		}
		if(!empty($order_by) && !in_array($order_by,$Modulefields)){
			$message = vtranslate($order_by,$module)." ".vtranslate('Field does not exists','Webapp');
			throw new WebServiceException(404,$message);
		}
		if($field_name){
			$uitype = $fieldModels[$field_name]->get('uitype');
		}
		foreach($params as $fieldname){
			if(!empty($fieldname) && !in_array($fieldname,$Modulefields)){
				$message = vtranslate($fieldname,$module)." ".vtranslate('Field does not exists','Webapp');
				throw new WebServiceException(404,$message);
			}
		}//end validation of fieldname
		
		$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
		if(in_array($uitype,$refrenceUitypes)){
			$relmodule = $module;
			$field_value = trim($field_value);
			$relQuery = "SELECT vtiger_fieldmodulerel.module FROM vtiger_fieldmodulerel INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid WHERE vtiger_fieldmodulerel.relmodule = '$relmodule' AND vtiger_field.fieldname = ?";
			$relResult = $adb->pquery($relQuery,array($field_name));
			if($adb->num_rows($relResult) > 0){
				if($adb->num_rows($relResult) > 1){

				}else{
					$seType = $adb->query_result($relResult,0,'module');
				}
			}
			if($seType){
				$result = $adb->pquery("SELECT crmid FROM vtiger_crmentity WHERE setype = '$seType' AND label LIKE '%".$field_value."%' ");
			}else{

				$result = $adb->pquery("SELECT crmid FROM vtiger_crmentity WHERE label LIKE '%".$field_value."%' ");
			}
			$numofrows = $adb->num_rows($result);
			$otherId =  array();
			for($i=0;$i<$numofrows;$i++){
				 $otherId[] = $adb->query_result($result,$i,'crmid');
			}
			$relation_name = $field_name;
			$field_value = implode(",",$otherId);
			if($field_value == ''){
				$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
				$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
				$response = new Webapp_API_Response();
				$moduleLabel = vtranslate($module,$module);
				$response->setResult(array('records'=>array(), 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'module_record_status'=>false,'createAction'=>$createAction));
				return $response;
			}
		}
		
		if($field_name == 'assigned_user_id'){
			$result = $adb->pquery("SELECT id FROM vtiger_users WHERE first_name LIKE '%".$field_value."%' OR last_name LIKE '%".$field_value."%' ");
			$numofrows = $adb->num_rows($result);
			$userId = array();
			for($i=0;$i<$numofrows;$i++){
				 $userId[] = $adb->query_result($result,$i,'id');
			}
			$field_value = implode(",",$userId);
			if($field_value == ''){
				$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
				$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
				$response = new Webapp_API_Response();
				$moduleLabel = vtranslate($module,$module);
				$response->setResult(array('records'=>array(), 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'module_record_status'=>false,'createAction'=>$createAction));
				return $response;
			}
			
		}
		$WithoutFilterModules = array('Users','WebUserFilterView');
		if(!$filterid && $module != ''  && !in_array($module, $WithoutFilterModules)) {
			$customView = new CustomView();
			$filterid = $customView->getViewId($module);
		}
		$filterOrAlertInstance = false;
		if(!empty($alertid)) {
			$filterOrAlertInstance = Webapp_WS_AlertModel::modelWithId($alertid);
		}
		else if(!empty($filterid)) {
			$filterOrAlertInstance = Webapp_WS_FilterModel::modelWithId($module, $filterid);
		}
		else if(!empty($search)) {
			
			$filterOrAlertInstance = $this->getSearchFilterModel($module, $search);
		}
		
		if($filterOrAlertInstance && strcmp($module, $filterOrAlertInstance->moduleName)) {
			$response = new Webapp_API_Response();
			$message = vtranslate('Mismatched module information.','Webapp');
			$response->setError(1001, $message);
			return $response;
		}
		

		// Initialize with more information
		if($filterOrAlertInstance) {
			$filterOrAlertInstance->setUser($current_user);
		}
		
		// Paging model
		$pagingModel = $this->getPagingModel($request);
		if ($size) {
		    $pagingModel->setLimit($size);
        }
		$paging = array('index'=>$index, 'size'=>$size);
		if($user_type == 'free'){
			$maxLimit = $index * $size;
			//~ if($maxLimit > 30){
				//~ $result = array();
				//~ $response = new Webapp_API_Response();
				//~ $msg = html_entity_decode('You do not have permission to view more records. Please subcscribe for Premium version.');
				//~ $response->setResult(array('records'=>$result,'msg'=>$msg,'module_record_status'=>false,'user_type'=>$user_type));
				//~ return $response;
			//~ }
		}
		/* Start: Added by Vijay Bhavsar */
		if($module == 'Leads') {
			$morefields = array('firstname', 'lastname', 'phone', 'company', 'designation', 'email', 'createdtime', 'modifiedtime','assigned_user_id', 'leadsource', 'leadprocess', 'dealamount', 'lane');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "Contacts"){
			$morefields = array('firstname', 'lastname', 'title', 'phone', 'email', 'createdtime', 'modifiedtime','assigned_user_id');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "Products"){
			$morefields = array('productname','unit_price', 'createdtime', 'modifiedtime','assigned_user_id','discontinued','description');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "Services"){
			$morefields = array('servicename','unit_price', 'createdtime', 'modifiedtime','assigned_user_id','discontinued','description');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "CTUserFilterView"){
			$morefields = array('module_name','filter_id', 'filter_name','createdtime', 'modifiedtime', 'assigned_user_id');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "Documents"){
			$morefields = array('notes_title','filename','filetype','createdtime', 'modifiedtime','assigned_user_id');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else if($module == "CTCalllog"){
			$morefields = array('calllog_no', 'calllog_name', 'modifiedtime','assigned_user_id');
			foreach ($params as $p) {
				if(!in_array($p, $morefields)){
					$morefields[]=$p;
				}
			}
		}else{
			$morefields = array();
			foreach ($params as $p) {
				$morefields[]=$p;
			}
			if($module == 'Users'){

			}else{
				$morefields[]= 'assigned_user_id';
			}
		}
		
		foreach($morefields as $key => $fields){
			if(!in_array($fields,$Modulefields)){
				unset($morefields[$key]);
			}else if($fieldModels[$fields]->isViewEnabled() != 1){
				unset($morefields[$key]);
			}
		}
		
		
		/* End: Added by Vijay Bhavsar */
		if($this->isCalendarModule($module)) {
			
			return $this->processSearchRecordLabelForCalendar($request, $filterOrAlertInstance, $pagingModel, $paging,$field_name, $field_value,$order_by,$orderby,$related,$params,$activitytype,$entityFields,$field_name2,$field_value2,$search_params);
		}

		$records = $this->fetchRecordLabelsForModule($module, $current_user, $morefields, $filterOrAlertInstance, $pagingModel, $paging, $field_name, $field_value,$order_by,$orderby,$related,$activitytype,$discontinued,$searchType,$field_name2,$field_value2,$search_params);
		$modifiedRecords = array();
		foreach($records as $record) {
			if ($record instanceof SqlResultIteratorRow) {
				$record = $record->data;
				// Remove all integer indexed mappings
				for($index = count($record); $index > -1; --$index) {
					if(isset($record[$index])) {
						unset($record[$index]);
					}
				}
			}
			
			if($module == 'WebUserFilterView'){
				$total_records_count = 0;
				$user_id = '19x'.$current_user->id;
				if($user_id!=$record['assigned_user_id']){
					continue;
				}elseif($record['filter_id']!='' && $record['module_name']!='' && ctype_digit($record['filter_id'])){
					if(Vtiger_Module_Model::getInstance($record['module_name'])){
						$listViewModel = Vtiger_ListView_Model::getInstance($record['module_name'], $record['filter_id']);
						$total_records_count = $listViewModel->getListViewCount();
					}else{
						continue;
					}
			
				}
			}
			
			$recordid = $record['id'];
			unset($record['id']);
			
			$eventstart = '';
			if($this->isCalendarModule($module)) {
				$eventstart = $record['date_start'];
				unset($record['date_start']);
			}

			$values = array_values($record);
			if($module == 'Users') {
				$label = implode(' ', $values);
			} else {
				$label = $values[1];
				$fieldnames = Webapp_WS_Utils::getEntityFieldnames($module);
				$label = $record[$fieldnames[0]];
			}
			
			
			$record_id = explode('x', $recordid);
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$fieldModels = $moduleModel->getFields();
			$recordModel = Vtiger_Record_Model::getInstanceById($record_id[1],$module);
			foreach($record as $key => $value){
				$fieldModel = $fieldModels[$key];
				if($fieldModel){
					$uitype = $fieldModel->get('uitype');
					if($value){
						$record[$key] = html_entity_decode($value, ENT_QUOTES, $default_charset);
						if(isset($record[$key])){
				
																					 
							if($uitype != 53 && $uitype != 13 && $uitype != 17 && !in_array($uitype,$refrenceUitypes)){
								$record[$key] = $fieldModel->getDisplayValue($record[$key], $record_id[1], $recordModel);
							}
						}
					}else if($value == 0 && $uitype != 53 && $uitype != 13 && $uitype != 17 && !in_array($uitype,$refrenceUitypes)){
						$fieldModel = $fieldModels[$key];
						$record[$key] = $fieldModel->getDisplayValue($record[$key], $record_id[1], $recordModel);
						$record[$key] = html_entity_decode($value, ENT_QUOTES, $default_charset);
					}else{
						$record[$key] = "";
					}										
				}
			}
			/* Start: Added by Vijay Bhavsar */
			$query = "SELECT * FROM vtiger_smsnotifier_servers WHERE isactive='1'";
			$result = $adb->pquery($query,array());
			$totalRecords = $adb->num_rows($result);
			
			if($module == "Leads") {
				$modifiedRecord = array('id' => $recordid,
                    'label'=>$record['firstname']." ".$record['lastname'],
                    'phone' => $record['phone'],
                    'email' => $record['email'],
                    'createdtime' => $record['createdtime'],
                    'company' => $record['company'],
                    'designation' => $record['designation'],
                    'leadsource' => $record['leadsource'],
                    'leadprocess' => $record['leadprocess'],
                    'dealamount' => $record['dealamount'],
                    'lane' => $record['lane'],
                    'assigned_user_id' => $record['assigned_user_id']
                );
				
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}

				if($totalRecords > 0){
					$modifiedRecord['sms_notifier'] = true;
				}else{
					$modifiedRecord['sms_notifier'] = false;
				}
				
			} else if($module == "Contacts"){
				$record_id = explode('x', $recordid);
				$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
											INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid 
											LEFT JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_seattachmentsrel.crmid 
											WHERE vtiger_seattachmentsrel.crmid = ?", array($record_id[1]));
											
				$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
				$document_path = array();
				
				if($AttachmentQueryCount > 0) {
					$name = $adb->query_result($AttachmentQuery, 0, 'name');
					$Path = $adb->query_result($AttachmentQuery, 0, 'path');
					$attachmentsId = $adb->query_result($AttachmentQuery, 0, 'attachmentsid');
					$contactImageUrl = $site_URL.$Path.$attachmentsId."_".$name;
				} else {
					$contactImageUrl = '';
				}
				$modifiedRecord = array('id' => $recordid, 'label'=>$record['firstname']." ".$record['lastname'],'ImageUrl' => $contactImageUrl,'phone'=> $record['phone'], 'email'=> $record['email']); 
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}

				if($totalRecords > 0){
					$modifiedRecord['sms_notifier'] = true;
				}else{
					$modifiedRecord['sms_notifier'] = false;
				}
			
			}else if($module == "Products") {
				$record_id = explode('x', $recordid);
				$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
											INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid 
											LEFT JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_seattachmentsrel.crmid 
											WHERE vtiger_seattachmentsrel.crmid = ?", array($record_id[1]));
											
				$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
				$document_path = array();
				
				if($AttachmentQueryCount > 0) {
					$name = $adb->query_result($AttachmentQuery, 0, 'name');
					$Path = $adb->query_result($AttachmentQuery, 0, 'path');
					$attachmentsId = $adb->query_result($AttachmentQuery, 0, 'attachmentsid');
					$productImageUrl = $site_URL.$Path.$attachmentsId."_".$name;
				} else {
					$productImageUrl = '';
				}
				$modifiedRecord = array('id' => $recordid, 'label'=>$record['productname'], 'ImageUrl' => $productImageUrl,'unit_price' => $record['unit_price'],'discontinued'=>$record['discontinued'],'description'=>$record['description']);
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}else if($module == "Services") {
				$modifiedRecord = array('id' => $recordid, 'label'=>$record['servicename'], 'unit_price' => $record['unit_price'],'discontinued'=>$record['discontinued'],'description'=>$record['description']); 
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}else if($module == "WebUserFilterView") {
				$modifiedRecord = array('id' => $recordid, 'label'=>$label, 'module_name'=>$record['module_name'], 'filter_id'=>$record['filter_id'], 'filter_name'=>$record['filter_name'],'modifiedtime'=>$record['modifiedtime'],'total_records_count'=>$total_records_count,'assigned_user_id'=>$record['assigned_user_id']); 
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}else if($module == "Documents") {
				$fileUrl = "";
				if($record['filename']){
					global $site_URL;
					$record_id = explode('x', $recordid);
					$attachResult = $adb->pquery("SELECT vtiger_attachments.attachmentsid,vtiger_attachments.path,vtiger_attachments.name FROM vtiger_attachments INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid WHERE vtiger_seattachmentsrel.crmid = ?",array($record_id[1]));
					$attachmentsid  = $adb->query_result($attachResult,0,'attachmentsid');
					$path  = $adb->query_result($attachResult,0,'path');
					$name = $adb->query_result($attachResult,0,'name');
					$fileUrl = $site_URL.$path.$attachmentsid.'_'.$name;
				}
				$modifiedRecord = array('id' => $recordid, 'label'=>$record['notes_title'],'filename'=>$record['filename'],'path'=>$fileUrl,'filetype'=>$record['filetype']); 
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}else if($module == "WebCalllog"){
				$modifiedRecord = array('id' => $recordid, 'label'=>$record['calllog_name']); 
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}else {
				$record_id = explode('x', $recordid);
				if($module == 'Users'){
					$recordModel = Users_Record_Model::getInstanceById($record_id[1],$module);
					$label = $recordModel->get('first_name').' '.$recordModel->get('last_name');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
				}else{
					$recordModel = Vtiger_Record_Model::getInstanceById($record_id[1],$module);
					$label = $recordModel->get('label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
				}
				$modifiedRecord = array('id' => $recordid,'label'=>html_entity_decode($label, ENT_QUOTES, $default_charset));
				foreach ($params as $key => $p) {
					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($uitype == 53){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record[$p],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record[$p]));
							$modifiedRecord[$keys] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
						}else{
							$modifiedRecord[$keys] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
						}
					}else{
						$modifiedRecord[$keys]=$record[$p];
				    }
				    $modifiedRecord[$keys] = html_entity_decode($modifiedRecord[$keys],ENT_QUOTES,$default_charset);
				}
				//$modifiedRecord['modifiedtime'] = $record['modifiedtime'];
				//$modifiedRecord['assigned_user_id'] = $record['assigned_user_id'];
			}
			
			/* End: Added by Vijay Bhavsar */
			
			//get Username Form userid
			if(!empty($modifiedRecord['assigned_user_id'])){
				if($module == 'WebUserFilterView'){
					$modifiedRecord['assigned_user_id'] = explode('x',$modifiedRecord['assigned_user_id']);
					$modifiedRecord['assigned_user_id'] = $modifiedRecord['assigned_user_id'][1];
				}
				$userRecordModel = Vtiger_Record_Model::getInstanceById($modifiedRecord['assigned_user_id'],'Users');
				if(empty($userRecordModel->get('user_name'))){
					$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
					$groupResults = $adb->pquery($query,array($modifiedRecord['assigned_user_id']));
					$modifiedRecord['assigned_user_id'] = html_entity_decode($adb->query_result($groupResults,0,'groupname'),ENT_QUOTES,$default_charset);
				}else{
					$modifiedRecord['assigned_user_id'] = html_entity_decode($userRecordModel->get('first_name').' '.$userRecordModel->get('last_name'),ENT_QUOTES,$default_charset);
				}	
			}
			
			if(!empty($eventstart)) {
				$modifiedRecord['eventstart'] = $eventstart;
			}

			//code start for permission of Edit and Delete
			if($module == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $module;
			}
			$editAction = Users_Privileges_Model::isPermitted($prevModule, 'EditView', $record_id[1]);
			$deleteAction = Users_Privileges_Model::isPermitted($prevModule, 'Delete', $record_id[1]);
			$modifiedRecord['editAction'] = $editAction;
			$modifiedRecord['deleteAction'] = $deleteAction;
			//code End for permission of Edit and Delete

			$modifiedRecords[] = $modifiedRecord;
		}
		
		$USER_MODEL = Users_Record_Model::getCurrentUserModel();
		$AccessibleUsers = array_keys($USER_MODEL->getAccessibleUsers());
		$AllowedUsers = array();
		foreach ($modifiedRecords as $key => $part) {
			if($module == 'Users'){
				$id = explode('x', $part['id']);
				$userid = $id[1];
				if(!in_array($userid, $AccessibleUsers)){
					unset($modifiedRecords[$key]);
				}else{
					$AllowedUsers[] = $modifiedRecords[$key];
				}
			}	
			$sort[$key] = strtotime($part['modifiedtime']);	
		}
		
		if($module == 'Users' && $type == 'owner'){
			$users = $AllowedUsers;
			$pagingModel = $this->getPagingModel($request);
			$paging = array('index'=>$index, 'size'=>$size);
			$modifiedRecords = array();
			$modifiedRecords = $this->GroupDetails($pagingModel,$paging);
			$groups = $modifiedRecords;
			$modifiedRecord = array();
			$modifiedRecord['Users'] = $users;
			$modifiedRecord['Groups'] = $groups;
			$moduleLabel = vtranslate($module,$module);
			$response = new Webapp_API_Response();
			$response->setResult(array('records'=>$modifiedRecord, 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>'','module_record_status'=>true));
			return $response;
		}
		//for create action 
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');
		$ModulesArray = array('SMSNotifier','PBXManager','CTPushNotification','CTCalllog','CTAttendance');
		if(in_array($module, $ModulesArray)){
			$createAction = false;
		}

		if($this->totalQuery != ""){
			$totalResults = $adb->pquery($this->totalQuery,$this->totalParams);
			$totalRecords = $adb->num_rows($totalResults);
			if($paging['index'] && $paging['size']){
				if($totalRecords > $paging['index']*$paging['size']){
					$isLast = false;	
					$pagesize = $paging['index']-1;
					$startRange = $pagesize*$paging['size']+1;
					$lastRange = $paging['index']*$paging['size'];	
				}else{
					$isLast = true;
					$pagesize = $paging['index']-1;
					$startRange = $pagesize*$paging['size']+1;
					$lastRange = $totalRecords;
				}
			}else{
				$isLast = true;
				$startRange = 1;
				$lastRange = $totalRecords;
			}

			$totalLabel = $startRange.' '.vtranslate('LBL_to', $module).' '.$lastRange.' '.vtranslate('LBL_OF', $module).' '.$totalRecords;
			
		}

		if($module == 'Users'){
			$modifiedRecords = $AllowedUsers;
		}

		//code start for mapview by suresh
		$allowedmapviewModules =  array('Leads','Contacts','Accounts','Calendar');
		if(in_array($module, $allowedmapviewModules)){
			$is_map_enable = true;
			foreach ($modifiedRecords as $key => $part) {
				$id = explode('x', $part['id']);
				$recordid = $id[1];
//				$latlongData = $this->getLatLongOfRecord($recordid);
//				$modifiedRecords[$key]['latitude'] = $latlongData['lat'];
//				$modifiedRecords[$key]['longitude'] = $latlongData['long'];
			}
		}else{
			$is_map_enable = false;
		}
		//code end for mapview by suresh

		//code start for is_display_image by suresh
		$allowedDisplayImageModules = array('Contacts','Products');
		if(in_array($module, $allowedDisplayImageModules)){
			$is_display_image = true;
		}else{
			$is_display_image = false;
		}

		
		$moduleLabel = vtranslate($module,$module);
		$response = new Webapp_API_Response();
        if ($module == 'Leads') {
            $name = 'leads';
        } else {
            $name = 'records';
        }
		if(count($modifiedRecords) == 0) {
			$response->setResult(array('totalCount' => 0, $name=>$modifiedRecords, 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'module_record_status'=>false,'createAction'=>$createAction,'isLast'=>$isLast,'entityField'=>$entityFields,'pagingLabel'=>"",'is_map_enable'=>$is_map_enable,'is_display_image'=>$is_display_image));
		} else {
			$response->setResult(array('totalCount' => $totalRecords, $name=>$modifiedRecords, 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>'','module_record_status'=>true,'createAction'=>$createAction,'isLast'=>$isLast,'entityField'=>$entityFields,'pagingLabel'=>$totalLabel,'is_map_enable'=>$is_map_enable,'is_display_image'=>$is_display_image));
		}
		
		return $response;
	}
	
	function processSearchRecordLabelForCalendar(Webapp_API_Request $request,$filterOrAlertInstance = false, $pagingModel = false, $paging = array(),$field_name, $field_value,$order_by,$orderby,$related,$params,$activitytype,$entityFields,$field_name2,$field_value2,$search_params) {
		$current_user = $this->getActiveUser();
		$module = $request->get('module');
		// Fetch both Calendar (Todo) and Event information
		
		if($module == 'Calendar' && $activitytype == 'Task'){
			$moreMetaFields = array('date_start', 'time_start', 'activitytype', 'location', 'subject', 'createdtime','due_date','taskstatus','taskpriority');
		}else{
			$moreMetaFields = array('date_start', 'time_start', 'activitytype', 'location', 'subject', 'createdtime','due_date','eventstatus');
		}
		$display_params = $params;
		foreach($params as $p){
			if(!in_array($p,$moreMetaFields)){
				$moreMetaFields[] = $p;
			}
		}

		$records=$this->fetchRecordLabelsForModule($module, $current_user, $moreMetaFields, $filterOrAlertInstance, $pagingModel, $paging,$field_name, $field_value,$order_by,$orderby,$related,$activitytype,$discontinued,$searchType,$field_name2,$field_value2,$search_params);

		if($module == 'Calendar'){
			if(empty($params)){
				$params = array('date_start','due_date','taskstatus','taskpriority');
			}else{
				$params =  array_merge($params,array('date_start','due_date','taskstatus','taskpriority'));
			}
		}
		$modifiedRecords = array();
		foreach($records as $record) {

			if ($record instanceof SqlResultIteratorRow) {
				$record = $record->data;
				// Remove all integer indexed mappings
				for($index = count($record); $index > -1; --$index) {
					if(isset($record[$index])) {
						unset($record[$index]);
					}
				}
			}
		
		    $recordId = explode('x',$record['id']);
		    global $adb;
		    $modifiedRecord = array();
		    $EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$recordId[1])); 
		    if($adb->num_rows($EventTaskQuery) > 0){
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
				$record['id'] = $wsid.'x'.$recordId[1];
				$modifiedRecord['module'] = 'Calendar';
			}else{
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
				$record['id'] = $wsid.'x'.$recordId[1];
				$modifiedRecord['module'] = 'Events';
			}

			$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$fieldModels = $moduleModel->getFields();
			$Modulefields = array_keys($fieldModels);

			$modifiedRecord['id'] = $record['id'];
			$modifiedRecord['label'] =  $record['subject'];            
			unset($record['id']);

			foreach ($params as $key => $p) {
				if(!empty($p) && !in_array($p,$Modulefields)){
					$message = vtranslate($field_name,$module)." ".vtranslate('Field does not exists','Webapp');
					throw new WebServiceException(404,$message);
				}else{

					$uitype = $fieldModels[$p]->get('uitype');
					$newKey = $key+2;
					$keys = 'label'.$newKey;
					if(in_array($uitype,$refrenceUitypes)){
						if($record[$p] == 0){
							$modifiedRecord[$keys]="";
						}else{
							$labelresult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($record[$p]));
							$new = $adb->query_result($labelresult,0,'label');
							$modifiedRecord[$keys]=$new;
						}
					}else if($p == 'assigned_user_id'){
						$userRecordModel = Vtiger_Record_Model::getInstanceById($record['assigned_user_id'],'Users');
						if(empty($userRecordModel->get('user_name'))){
							$query = "SELECT groupname FROM vtiger_groups WHERE groupid = ?";
							$groupResults = $adb->pquery($query,array($record['assigned_user_id']));
							$modifiedRecord[$keys] = $adb->query_result($groupResults,0,'groupname');
						}else{
							$modifiedRecord[$keys] = $userRecordModel->get('first_name').' '.$userRecordModel->get('last_name');
						}
					}else{
						$recordModel = Vtiger_Record_Model::getInstanceById($recordId[1],'Calendar');
						if($module == 'Calendar' && in_array($p, array('date_start','due_date','taskstatus','taskpriority','time_start','time_end'))){
							if(!in_array($p, $display_params)){
								$keys = $p;
								if(!empty($record[$p])){
									if($p == 'date_start'){
										$startDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($record['date_start'].' '.$record['time_start']);
										$DateTime = explode(' ', $startDateTime);
										$modifiedRecord[$keys] = $DateTime[0];
									}else if($p == 'due_date'){
										$endDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($record['due_date'].' '.$record['time_end']);
										$DateTime = explode(' ', $endDateTime);
										$modifiedRecord[$keys] = $DateTime[0];
									}else if($p == 'time_start' || $p == 'time_end'){
										$date = new DateTime();
										$dateTime = new DateTimeField($date->format('Y-m-d').' '.$record[$p]);
										$value = Vtiger_Time_UIType::getDisplayValue($dateTime->getDisplayTime());
										$value = explode(' ',$value);
										if(count($value) > 1){
										 $values = $value[0].' '.$value[1];
									    }else{
											$values = $value[0];
										}
										$modifiedRecord[$keys] = $values;
									}else{
										$modifiedRecord[$keys] = $fieldModels[$p]->getDisplayValue($record[$p], $recordId[1], $recordModel);
									}
								}else{
									$modifiedRecord[$keys] = "";
								}
							}else{
								if(!empty($record[$p])){
									if($p == 'date_start'){
										$startDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($record['date_start'].' '.$record['time_start']);
										$DateTime = explode(' ', $startDateTime);
										$modifiedRecord[$keys] = $DateTime[0];
									}else if($p == 'due_date'){
										$endDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($record['due_date'].' '.$record['time_end']);
										$DateTime = explode(' ', $endDateTime);
										$modifiedRecord[$keys] = $DateTime[0];
									}else if($p == 'time_start' || $p == 'time_end'){
										$date = new DateTime();
										$dateTime = new DateTimeField($date->format('Y-m-d').' '.$record[$p]);
										$value = Vtiger_Time_UIType::getDisplayValue($dateTime->getDisplayTime());
										$value = explode(' ',$value);
										if(count($value) > 1){
										 $values = $value[0].' '.$value[1];
									    }else{
											$values = $value[0];
										}
										$modifiedRecord[$keys] = $values;
									}else{
										$modifiedRecord[$keys] = $fieldModels[$p]->getDisplayValue($record[$p], $recordId[1], $recordModel);
									}
									$modifiedRecord[$p] = $modifiedRecord[$keys];
								}else{
									$modifiedRecord[$keys] = "";
									$modifiedRecord[$p] = $modifiedRecord[$keys];
								}
							}
						}else{
							if(!empty($record[$p])){
								$modifiedRecord[$keys] = $fieldModels[$p]->getDisplayValue($record[$p], $recordId[1], $recordModel);
							}else{
								$modifiedRecord[$keys] = "";
							}
						}
						//$modifiedRecord[$keys]=$record[$p];
				    }
				}
			}

			//code start for permission of Edit and Delete
			if($module == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $module;
			}
			$editAction = Users_Privileges_Model::isPermitted($prevModule, 'EditView', $recordId[1]);
			$deleteAction = Users_Privileges_Model::isPermitted($prevModule, 'Delete', $recordId[1]);
			$modifiedRecord['editAction'] = $editAction;
			$modifiedRecord['deleteAction'] = $deleteAction;
			//code End for permission of Edit and Delete
			
			if(Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $recordId[1])){
				$modifiedRecords[] = $modifiedRecord;
			}
		}
		
		foreach ($modifiedRecords as $key => $part) {
			$sort[$key] = strtotime($part['startDateTime']);
		}

		if($this->totalQuery != ""){
			$totalResults = $adb->pquery($this->totalQuery,$this->totalParams);
			$totalRecords = $adb->num_rows($totalResults);
			if($paging['index'] && $paging['size']){
				if($totalRecords > $paging['index']*$paging['size']){
					$isLast = false;	
					$pagesize = $paging['index']-1;
					$startRange = $pagesize*$paging['size']+1;
					$lastRange = $paging['index']*$paging['size'];	
				}else{
					$isLast = true;
					$pagesize = $paging['index']-1;
					$startRange = $pagesize*$paging['size']+1;
					$lastRange = $totalRecords;
				}
			}else{
				$isLast = true;
				$startRange = 1;
				$lastRange = $totalRecords;
			}

			$totalLabel = $startRange.' '.vtranslate('LBL_to', $module).' '.$lastRange.' '.vtranslate('LBL_OF', $module).' '.$totalRecords;
			
		}

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
		$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$createAction = $userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'CreateView');

		array_multisort($sort, SORT_DESC, $modifiedRecords);

		//code start for mapview by suresh
		$is_map_enable = true;
		foreach ($modifiedRecords as $key => $part) {
			$id = explode('x', $part['id']);
			$recordid = $id[1];
//			$latlongData = $this->getLatLongOfRecord($recordid);
//			$modifiedRecords[$key]['latitude'] = $latlongData['lat'];
//			$modifiedRecords[$key]['longitude'] = $latlongData['long'];
		}
		//code end for mapview by suresh

		//code start for is_display_image by suresh
		$is_display_image = false;

		$moduleLabel = vtranslate($module,$module);
		$response = new Webapp_API_Response();
		if ($module == 'Leads') {
		    $name = 'leads';
        } else {
		    $name = 'records';
        }
		if(count($modifiedRecords) == 0) {
			$response->setResult(array($name=>$modifiedRecords, 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'module_record_status'=>false,'isLast'=>$isLast,'createAction'=>$createAction,'entityField'=>$entityFields,'pagingLabel'=>"",'is_map_enable'=>$is_map_enable,'is_display_image'=>$is_display_image));
		} else {
			ksort($modifiedRecords);
			$response->setResult(array($name=>$modifiedRecords, 'module'=>$module,'moduleLabel'=>$moduleLabel, 'message'=>'','module_record_status'=>true,'isLast'=>$isLast,'createAction'=>$createAction,'entityField'=>$entityFields,'pagingLabel'=>$totalLabel,'is_map_enable'=>$is_map_enable,'is_display_image'=>$is_display_image));
		}
		
		return $response;
	}
	
	function fetchRecordLabelsForModule($module, $user, $morefields=array(), $filterOrAlertInstance=false, $pagingModel = false, $paging=array(), $field_name, $field_value,$order_by,$orderby,$related,$activitytype,$discontinued,$searchType = '',$field_name2,$field_value2,$search_params) {

		if($module != 'Users') {
			$morefields[]='modifiedtime';
		}
		
		if($this->isCalendarModule($module)) {
			$fieldnames = Webapp_WS_Utils::getEntityFieldnames('Calendar');
		} else {
			$fieldnames = Webapp_WS_Utils::getEntityFieldnames($module);
		}
		
		if(!empty($morefields)) {
			foreach($morefields as $fieldname) $fieldnames[] = $fieldname;
		}

		if($filterOrAlertInstance === false) {
			$filterOrAlertInstance = Webapp_WS_SearchFilterModel::modelWithCriterias($module);
			$filterOrAlertInstance->setUser($user);
		}

		return $this->queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel, $paging, $field_name, $field_value,$order_by,$orderby,$user,$related,$activitytype,$discontinued,$searchType,$field_name2,$field_value2,$search_params);
		
	}
	
	function queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel, $paging = array(), $field_name, $field_value,$order_by,$orderby,$user,$related,$activitytype,$discontinued,$searchType='',$field_name2,$field_value2,$search_params) {

		if (($filterOrAlertInstance instanceof Webapp_WS_SearchFilterModel) && !$this->isCalendarModule($module)) {
			if(!empty($order_by) && !empty($orderby)){
				$orderClause = " ORDER BY ".$order_by." ".$orderby;
			}else{
				$orderClause = '';
			}
			return $filterOrAlertInstance->execute($fieldnames, $pagingModel, $paging, $orderClause);
			
		}
		
		global $adb;
		$moduleWSId = Webapp_WS_Utils::getEntityModuleWSId($module);
		$columnByFieldNames = Webapp_WS_Utils::getModuleColumnTableByFieldNames($module, $fieldnames);
		// Build select clause similar to Webservice query
		$selectColumnClause = "CONCAT('{$moduleWSId}','x',vtiger_crmentity.crmid) as id,";
		foreach($columnByFieldNames as $fieldname=>$fieldinfo) {
			$selectColumnClause .= sprintf("%s.%s as %s,", $fieldinfo['table'],$fieldinfo['column'],$fieldname);
		}
		
		$selectColumnClause = rtrim($selectColumnClause, ',');
		$var =array();
		for($i=0;$i<count($fieldnames);$i++){
			$var[]= $fieldnames[$i];
		}
		if($field_name){
			$var[]=$field_name;
		}
		if($field_name2){
			$var[]=$field_name2;
		}
		$var[]='id';
		$generator = new QueryGenerator($module, $user);
		if($related != 1){
		 $generator->initForCustomViewById($filterOrAlertInstance->filterid);
	    }
		$generator->setFields($var);
		if(!empty($search_params)) {
			$search_params = Zend_JSON::decode($search_params);
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$search_params = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($search_params, $moduleModel);
			$glue = "";
			if(count($generator->getWhereFields()) > 0 && (count($search_params)) > 0) {
				$glue = QueryGenerator::$AND;
			}
			$generator->parseAdvFilterList($search_params, $glue);
		}else{
			$search_params = array();
		}
		$query = $generator->getQuery();
		$query = preg_replace("/SELECT.*FROM(.*)/i", "SELECT $selectColumnClause FROM $1", $query);
		if($module == 'Events' || $module == 'Calendar'){
			$Eventsquery = explode('WHERE',$query);
			$query = $Eventsquery[0]." WHERE vtiger_crmentity.setype = 'Calendar' AND ".$Eventsquery[1];
		}else if($module == 'Products' && $discontinued == 1){
			$Productsquery = explode('WHERE',$query);
			$query = $Productsquery[0]." WHERE vtiger_products.discontinued = 1 AND ".$Productsquery[1];
		}else if($module == 'Services' && $discontinued == 1){
			$Servicesquery = explode('WHERE',$query);
			$query = $Servicesquery[0]." WHERE vtiger_service.discontinued = 1 AND ".$Servicesquery[1];
		}

		if($field_name != '' && $field_value != ''){
			$tablename = $columnByFieldNames[$field_name]['table'];
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$fieldModels = $moduleModel->getFields();
			if($field_name){
				$uitype = $fieldModels[$field_name]->get('uitype');
			}
			$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
			if(in_array($uitype,$refrenceUitypes)){
				$tablename =  $fieldModels[$field_name]->get('table');
				$column =  $fieldModels[$field_name]->get('column');
				$query .= " AND ".$tablename.".".$column." IN (".$field_value.")";
			}else if($field_name == 'assigned_user_id'){
				$tablename =  $fieldModels[$field_name]->get('table');
				$column =  $fieldModels[$field_name]->get('column');
				$query .= " AND ".$tablename.".".$column." IN (".$field_value.")";
			}else{
				$tablename =  $fieldModels[$field_name]->get('table');
				$column =  $fieldModels[$field_name]->get('column');
				if($field_name2 != '' && $field_value2 != ''){
					$query .= " AND ".$tablename.".".$column." = '".$field_value."'";
				}else{
					if($searchType != '' && $searchType == 'barcode'){
						$query .= " AND ".$tablename.".".$column." = '".$field_value."'";
					}else{
						$query .= " AND ".$tablename.".".$column." LIKE '%".$field_value."%'";
					}
				}
			}
			
		}
		if($field_name2 != '' && $field_value2 != ''){
			$tablename = $columnByFieldNames[$field_name2]['table'];
			$moduleModel = Vtiger_Module_Model::getInstance($module);
			$fieldModels = $moduleModel->getFields();
			if($field_name2){
				$uitype = $fieldModels[$field_name2]->get('uitype');
			}
			$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
			if(in_array($uitype,$refrenceUitypes)){
				$tablename =  $fieldModels[$field_name2]->get('table');
				$column =  $fieldModels[$field_name2]->get('column');
				$query .= " AND ".$tablename.".".$column." IN (".$field_value2.")";
			}else if($field_name2 == 'assigned_user_id'){
				$field_value2 = explode('x', $field_value2);
				$tablename =  $fieldModels[$field_name2]->get('table');
				$column =  $fieldModels[$field_name2]->get('column');
				$query .= " AND ".$tablename.".".$column." IN (".$field_value2[1].")";
			}else{
				$tablename =  $fieldModels[$field_name2]->get('table');
				$column =  $fieldModels[$field_name2]->get('column');
				$query .= " AND ".$tablename.".".$column." = '".$field_value2."'";
				
			}
			
		}

		if($pagingModel !== false) {
			$index = $paging['index'];
			$size = $paging['size'];
			$limit = ($index*$size) - $size;

			if($index != '' && $size != '') {
				if($order_by){
				   if($orderby){
				   	$query .= " ORDER BY ".$order_by." ".$orderby;
				   }else{
					$query .= " ORDER BY ".$order_by." ASC";
				   }	
				}else{
				   $query .= " ORDER BY modifiedtime DESC";
				}
				$this->totalQuery = $query;
				$this->totalParams = $filterOrAlertInstance->queryParameters();
				$query .= sprintf(" LIMIT %s, %s", $limit, $size);
			}
		}else{
			$this->totalQuery = $query;
			$this->totalParams = $filterOrAlertInstance->queryParameters();
		}
		$prequeryResult = $adb->pquery($query, $filterOrAlertInstance->queryParameters());
		return new SqlResultIterator($adb, $prequeryResult);
	}

	function getLatLongOfRecord($recordid){
		global $adb;
		$data['lat'] = "";
		$data['long'] = "";
		if($recordid){
			$result  = $adb->pquery("SELECT * FROM `web_address_lat_long` WHERE recordid = ? ",array($recordid));
			if($adb->num_rows($result) > 0){
				$data['lat'] = $adb->query_result($result,0,'latitude');
				$data['long'] = $adb->query_result($result,0,'longitude');
			}

		}

		return $data;
	}
	
}
