<?php

include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_FetchRecordWithGrouping extends Webapp_WS_FetchRecord {
	
	private $_cachedDescribeInfo = false;
	private $_cachedDescribeFieldInfo = false;
	
	protected function cacheDescribeInfo($describeInfo) {
		$this->_cachedDescribeInfo = $describeInfo;
		$this->_cachedDescribeFieldInfo = array();
		if(!empty($describeInfo['fields'])) {
			foreach($describeInfo['fields'] as $describeFieldInfo) {
				$this->_cachedDescribeFieldInfo[$describeFieldInfo['name']] = $describeFieldInfo;
			}
		}
	}
	
	protected function cachedDescribeInfo() {
		return $this->_cachedDescribeInfo;
	}
	
	protected function cachedDescribeFieldInfo($fieldname) {
		if ($this->_cachedDescribeFieldInfo !== false) {
			if(isset($this->_cachedDescribeFieldInfo[$fieldname])) {
				return $this->_cachedDescribeFieldInfo[$fieldname];
			}
		}
		return false;
	}
	
	protected function cachedEntityFieldnames($module) {
		$describeInfo = $this->cachedDescribeInfo();
		$labelFields = $describeInfo['labelFields'];
		switch($module) {
			case 'HelpDesk': $labelFields = 'ticket_title'; break;
			case 'Documents': $labelFields = 'notes_title'; break;
		}
		return explode(',', $labelFields);
	}
	
	protected function isTemplateRecordRequest(Webapp_API_Request $request) {
		$recordid = $request->get('record');
		return (preg_match("/([0-9]+)x0/", $recordid));
	}
	
	protected function processRetrieve(Webapp_API_Request $request) {
		$recordid = $request->get('record');

		// Create a template record for use 
		if ($this->isTemplateRecordRequest($request)) {
			global $current_user;
			$current_user = $this->getActiveUser();
			
			$module = $this->detectModuleName($recordid);
		 	$describeInfo = vtws_describe($module, $current_user);
		 	Webapp_WS_Utils::fixDescribeFieldInfo($module, $describeInfo);

		 	$this->cacheDescribeInfo($describeInfo);

			$templateRecord = array();
			foreach($describeInfo['fields'] as $describeField) {
				$templateFieldValue = '';
				if (isset($describeField['type']) && isset($describeField['type']['defaultValue'])) {
					$templateFieldValue = trim($describeField['type']['defaultValue']);
				} else if (isset($describeField['default'])) {
					$templateFieldValue = trim($describeField['default']);
				}
				$templateRecord[$describeField['name']] = $templateFieldValue;
			}
			if (isset($templateRecord['assigned_user_id'])) {
				$templateRecord['assigned_user_id'] = sprintf("%sx%s", Webapp_WS_Utils::getEntityModuleWSId('Users'), $current_user->id);
			} 
			// Reset the record id
			$templateRecord['id'] = $recordid;
			
			return $templateRecord;
		}
		
		// Or else delgate the action to parent
		return parent::processRetrieve($request);
	}
	
	function process(Webapp_API_Request $request) {
		$recordid = trim($request->get('record'));
		if(empty($recordid)){
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		$module = $this->detectModuleName($recordid);
		global $adb;
		if($module == 'Calendar' || $module == 'Events'){
			$calendarmodule = explode('x', $request->get('record'));
			$activityid = $calendarmodule[1];
			$EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$activityid)); 
		    if($adb->num_rows($EventTaskQuery) > 0){
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
				$recordid = $wsid.'x'.$activityid;
				$recordModule = 'Calendar';
			}else{
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
				$recordid = $wsid.'x'.$activityid;
				$recordModule = 'Events';
			}
			$request->set('record',$recordid);
		}
		$response = parent::process($request);
		
		return $this->processWithGrouping($request, $response);
	}
	
	protected function processWithGrouping(Webapp_API_Request $request, $response) {
		$isTemplateRecord = $this->isTemplateRecordRequest($request);
		$result = $response->getResult();
		
		$resultRecord = $result['record'];
		$module = $this->detectModuleName($resultRecord['id']);
		if($module == 'Emails'){
			$resultRecord['recordLabel'] = trim($resultRecord['subject']);
		}
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$resultRecord['recordLabel'] = html_entity_decode($resultRecord['recordLabel'], ENT_QUOTES, $default_charset);
		$modifiedRecord = $this->transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord);
		$response->setResult(array('record' => $modifiedRecord));
		
		return $response;
	}
	
	protected function transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord=false) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb,$current_user,$site_URL;
		$current_user = $this->getActiveUser();
		$moduleFieldGroups = Webapp_WS_Utils::gatherModuleFieldGroupInfo($module);
		$recordid = explode("x",$resultRecord['id']);
		$modifiedResult = array();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$duplicateAction = $moduleModel->isDuplicateOptionAllowed('CreateView', $recordid[1]);
		//$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
		$editAction = Users_Privileges_Model::isPermitted($module, 'EditView', $recordid[1]);
		
		$deleteAction = Users_Privileges_Model::isPermitted($module, 'Delete', $recordid[1]);
		$ModulesArray = array('SMSNotifier','PBXManager','CTPushNotification','CTCalllog','CTAttendance','Users');
		if(in_array($module,$ModulesArray)){
			$editAction = false;
			$deleteAction = false;
		}
		
		if($module == 'Documents' || $module == 'Emails' || $module == 'ModComments'){
			$editAction = false;
		}
		if($module == 'Emails' || $module == 'ModComments'){
			$deleteAction = false;
			$duplicateAction = false;
		}
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$modCommentsFields = array_keys($modCommentsModel->getFields());
		$isAttachmentSupport = false;
		if(in_array('filename', $modCommentsFields)){
			$isAttachmentSupport = true;
		}
		$commentModuleAccess = $modCommentsModel->isPermitted('CreateView');
		$ActivityModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		$ActivityModuleAccess = $ActivityModuleModel->isPermitted('CreateView');

		$newblocks = array();
		$moduleblocks = array_keys($moduleModel->getBlocks());
		foreach ($moduleblocks as $key => $value) {
			$newblocks[$value] = vtranslate($value,$module);
		}
		
		$fieldModels = $moduleModel->getFields();
		if($module == 'Calendar' || $module == 'Events'){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1]);
			$activityType = $recordModel->getType();
			if($activityType == 'Events'){
				$moduleName = 'Events';
			}else{
				$moduleName = 'Calendar';
			}
			$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$moduleName);
		}else{
			$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$module);
			$IS_AJAX_ENABLED = $recordModel->isEditable();
		}

		$blocks = array(); $labelFields = false;
		if(array_key_exists('filename',$resultRecord)){
		}else{
			$query = "SELECT * FROM  `vtiger_notes` WHERE notesid = ?";
			$result = $adb->pquery($query,array($recordid[1]));
			$filename = $adb->query_result($result,0,'filename');
			$resultRecord['filename'] = $filename;
		}

		$lineItemsTotalFieldGroup = array();
		$LineItemsFields = array('productid','quantity','listprice','comment','tax1','tax2','tax3','hdnS_H_Percent','discount_amount','discount_percent','txtAdjustment','hdnSubTotal','hdnGrandTotal','hdnDiscountPercent','hdnDiscountAmount','hdnTaxType');
		$lineItemsTotalFields = array('tax1','tax2','tax3','txtAdjustment','hdnSubTotal','hdnGrandTotal','hdnS_H_Percent','hdnDiscountPercent','hdnDiscountAmount','hdnTaxType');

		foreach($moduleFieldGroups as $blocklabel => $fieldgroups) {
			$fields = array();
			/* Start: Added by Vijay Bhavsar */
			$query = "SELECT * FROM vtiger_smsnotifier_servers WHERE isactive='1'";
			$result = $adb->pquery($query,array());
			$totalRecords = $adb->num_rows($result);

			foreach($fieldgroups as $fieldname => $fieldinfo) {
					if(in_array($fieldname, $LineItemsFields)){
					if(in_array($fieldname,$lineItemsTotalFields)){
						if($fieldinfo['uitype'] == 15 ||$fieldinfo['uitype'] == 16){
							$values = $resultRecord[$fieldname];
							if($values){
								$values = vtranslate($values,$module);
							}
						}else if($fieldinfo['uitype'] == 72){
							$values = $resultRecord[$fieldname];
							if($values){
								$fieldModel = $fieldModels[$fieldname];
								$values = $fieldModel->getDisplayValue($values);
							}
						}else if($fieldname == 'hdnS_H_Percent' || $fieldname == 'hdnDiscountPercent'){
							$values = $resultRecord[$fieldname];
							if($values){
								$values = Vtiger_Double_UIType::getDisplayValue($values);
							}
						}else if($fieldinfo['uitype'] == 83){
							$values = $resultRecord['LineItems'][0][$fieldname];
						}else{
							$values = $resultRecord[$fieldname];
						}
					}else{
						$values = array();
						foreach($resultRecord['LineItems'] as $key => $value) {
								if($fieldname == 'productid'){
									$deletedMessage = vtranslate('LBL_THIS',$module).' '.vtranslate($value['entity_type'],$value['entity_type']).' '.vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$module);
									$values[] = array('value'=>$value[$fieldname],'label'=>html_entity_decode($value['product_name'], ENT_QUOTES, $default_charset),'refrerenceModule'=>$value['entity_type'],'deleted'=>$value['deleted'],'deletedMessage'=>$deletedMessage);
								}else{
									if($fieldinfo['uitype'] == 71 || $fieldinfo['uitype'] == 72){
										$valuess = $value[$fieldname];
										if($valuess){
											$fieldModel = $fieldModels[$fieldname];
											$values[] = $fieldModel->getDisplayValue($valuess);
										}else{
											$values[] = $valuess;
										}
									}else if($fieldinfo['uitype'] == 7){
										$valuess = $value[$fieldname];
										if($valuess){
											$values[] = Vtiger_Double_UIType::getDisplayValue($valuess);
										}else{
											$values[] = $valuess;
										}
									}else{

										$values[] = $value[$fieldname];
									}
								}
						}
					}
					$field = array(
						'name'  => $fieldname,
						'value' => $values,
						'label' => $fieldinfo['label'],
						'uitype'=> $fieldinfo['uitype'],
						'summaryfield' => $fieldinfo['summaryfield'],
						'typeofdata' => $fieldinfo['typeofdata']
					);

					//code start for isAjaxEdit by suresh
					$fieldModel = $fieldModels[$fieldname];
					
					$field['is_Ajaxedit'] = false;
					$fieldModelinfo = $fieldModel->getFieldInfo();
					$field['type']['name'] = $fieldModelinfo['type'];
					
					// code end for isAjaxEdit by suresh
					$fields[] = $field;
				}else{
					// Pickup field if its part of the result
					if(isset($resultRecord[$fieldname])) {
						$fieldModel = $fieldModels[$fieldname];
						$displayType = $fieldModel->get('displaytype');
						$uitypes = $fieldModel->get('uitype');
						$allowedFields = array('time_start','time_end');
						$restrictedDisplayTypes = array(1,2);
						//remove fields if invisible from CRM
						if($fieldModels[$fieldname]->isViewEnabled() != 1){
							continue;
						}
						if(!in_array($displayType,$restrictedDisplayTypes) && !in_array($fieldname,$allowedFields)){
							continue;
						}
						if($module == 'Calendar' && $fieldname == 'time_end'){
							continue;
						}
						$typeofdataArray = array('N~O','N~M','NN~O','NN~M');
						if(($fieldinfo['uitype'] == 72 || $fieldinfo['uitype'] == 1) && in_array($fieldinfo['typeofdata'],$typeofdataArray)) {
							$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$module);
							$value = $fieldModel->getDisplayValue($resultRecord[$fieldname], $recordid[1], $recordModel);
							$field = array(
								'name'  => $fieldname,
								'value' => $value,
								'label' => $fieldinfo['label'],
								'uitype'=> $fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
							);
						} else {
							if($fieldinfo['uitype'] == 33){
								$value = explode(' |##| ', $resultRecord[$fieldname]);
								$values = '';
								foreach($value as $key => $v){
									if($key+1 == count($value)){
										$values.= $v;
									}else{
										$values.= $v.',';
									}
								}
								$multipicklistvalue = array();
								foreach($value as $v){
									$multipicklistvalue[] = array('label'=>vtranslate($v,$module),'value'=>$v);
								}
								$field = array(
								'name'  => $fieldname,
								'value' => $values,
								'label' => $fieldinfo['label'],
								'uitype'=> $fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
								);
								$field['type']['defaultValue'] = $multipicklistvalue;
							}else if($fieldname =='time_start' || $fieldname =='time_end'){
								if($fieldname == 'time_start'){
									$value = $resultRecord['date_start'].' '.$resultRecord['time_start'];
									$value = Vtiger_Datetime_UIType::getDisplayValue($value);
									$DATETIMEVALUE = explode(' ',$value);
									if(count($DATETIMEVALUE) > 2){
										$values = $DATETIMEVALUE[1].' '.$DATETIMEVALUE[2];
									}else{
										$values = $DATETIMEVALUE[1];
									}
								}else{
									if(!empty($resultRecord['due_date'])){
										$value = $resultRecord['due_date'].' '.$resultRecord['time_end'];
									}else{
										$value = $date->format('Y-m-d').' '.$resultRecord['time_end'];
									}
									$value = Vtiger_Datetime_UIType::getDisplayValue($value);
									$DATETIMEVALUE = explode(' ',$value);
									if(count($DATETIMEVALUE) > 2){
										$values = $DATETIMEVALUE[1].' '.$DATETIMEVALUE[2];
									}else{
										$values = $DATETIMEVALUE[1];
									}
								}
								$field = array(
								'name'  => $fieldname,
								'value' => $values,
								'label' => $fieldinfo['label'],
								'uitype'=> (string)$fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
							   );
							}else if($fieldinfo['uitype'] == 71 || $fieldinfo['uitype'] == 30){
								$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$module);
								$value = $fieldModel->getDisplayValue($resultRecord[$fieldname], $recordid[1], $recordModel);
								$field = array(
								'name'  => $fieldname,
								'value' => $value,
								'label' => $fieldinfo['label'],
								'uitype'=> (string)$fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
							   );
							  
							   if($fieldname =='reminder_time' && $resultRecord['reminder_time'] == 0){
								  $field['reminder_value'] = array('days'=>0,'hours'=>0,'minutes'=>0);
							   }else{
							   	   $reminder = $resultRecord['reminder_time'];
								   $minutes = (int)($reminder)%60;
								   $hours = (int)($reminder/(60))%24;
								   $days =  (int)($reminder/(60*24));
								   $field['reminder_value'] = array('days'=>$days,'hours'=>$hours,'minutes'=>$minutes);
								   
							   }
							}else if($fieldinfo['uitype'] == 69){
								$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
												INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid  
												WHERE vtiger_seattachmentsrel.crmid = ?", array($recordid[1]));
												
								$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
								$document_path = array();
								
								if($AttachmentQueryCount > 0) {
									$name = $adb->query_result($AttachmentQuery, 0, 'name');
									$Path = $adb->query_result($AttachmentQuery, 0, 'path');
									$attachmentsId = $adb->query_result($AttachmentQuery, 0, 'attachmentsid');
									$ImageUrl = $site_URL.$Path.$attachmentsId."_".$name;
									$value = $name;
								} else {
									$ImageUrl = "";
									$value = "";
								}
								$field = array(
								'name'  => $fieldname,
								'value' => $value,
								'ImageUrl'=>$ImageUrl,
								'label' => $fieldinfo['label'],
								'uitype'=> (string)$fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
							   );
							}else{
								$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
								$field = array(
								'name'  => $fieldname,
								'value' => $resultRecord[$fieldname],
								'label' => $fieldinfo['label'],
								'uitype'=> $fieldinfo['uitype'],
								'summaryfield' => $fieldinfo['summaryfield'],
								'typeofdata' => $fieldinfo['typeofdata']
							   );
							   if(in_array($fieldinfo['uitype'],$refrenceUitypes)){
								   if($resultRecord[$fieldname]['value']){
										$refrerenceModule = Webapp_WS_Utils::detectModulenameFromRecordId($resultRecord[$fieldname]['value']);
										$field['refrerenceModule'] = $refrerenceModule;
								   }else{
									   $field['refrerenceModule'] = "";
								   }
							   }
							   
							}
							
						}
						if($fieldinfo['uitype'] == 15 || $fieldinfo['uitype'] == 16){
							$field['value'] =  vtranslate($resultRecord[$fieldname],$module);
						}
						if($fieldname == 'recurringtype'){
							$field['value'] = Webapp_WS_Utils::RecurringDetails($recordid[1],$module);
						}
						if($fieldname == 'filename'){
							global $adb,$site_URL;
							$query = "SELECT * FROM vtiger_attachments INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid=vtiger_attachments.attachmentsid WHERE vtiger_seattachmentsrel.crmid=?";
							$result = $adb->pquery($query,array($recordid[1]));
							$filename = $adb->query_result($result,0,'name');
							$attachmentsid = $adb->query_result($result,0,'attachmentsid');
							$path = $adb->query_result($result,0,'path');
							$filepath = $site_URL.$path.$attachmentsid.'_'.$filename;
							if(!empty($filename)){
								$field['ImageUrl'] = $filepath;	
								$field['value'] = $filename;
							}else{
								$field['ImageUrl'] = "";  
								$field['value'] = "";
							}
						}
						
						
						// Template record requested send more details if available
						if ($isTemplateRecord) {
							$describeFieldInfo = $this->cachedDescribeFieldInfo($fieldname);
							if ($describeFieldInfo) {
								foreach($describeFieldInfo as $k=>$v) {
									if (isset($field[$k])) continue;
									$field[$k] = $v;
								}
							}
							// Entity fieldnames
							$labelFields = $this->cachedEntityFieldnames($module);
						}
						// Fix the assigned to uitype
						if ($field['uitype'] == '53') {
							$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
						} else if($field['uitype'] == '117') {
							$field['type']['defaultValue'] = trim($field['value']);
						}
	               		// Special case handling to pull configured Terms & Conditions given through webservices.
						else if($field['name'] == 'terms_conditions' && in_array($module, array('Quotes','Invoice', 'SalesOrder', 'PurchaseOrder'))){ 
	   						$field['type']['defaultValue'] = trim($field['value']); 
	                    }else if($field['name'] == 'date_start'){
								$startDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($resultRecord['date_start'].' '.$resultRecord['time_start']);
								$DateTime = explode(' ', $startDateTime);
								$field['value'] = $DateTime[0];
						}else if($field['name'] == 'due_date'){
								$endDateTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue($resultRecord['due_date'].' '.$resultRecord['time_end']);
								$DateTime = explode(' ', $endDateTime);
								$field['value'] = $DateTime[0];
						}else if($field['uitype'] == '70' ) {
							if($field['value']!=''){
								$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$module);
								$userDateTimeString = $fieldModel->getDisplayValue($field['value'], $recordid[1], $recordModel);
								$field['value'] = $userDateTimeString;
								
							}
							
						}else if($field['uitype'] == '9'){
							if($field['value']!=''){
								$field['value'] = Vtiger_Double_UIType::getDisplayValue($field['value']);
								
							}
						}else if($field['uitype'] == '5'  ) {
							if($field['value']!=''){
								$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
								
							}
							
						}else if( $field['uitype'] == '6' ) {
							if($field['value']!=''){
								$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
								
							}
							
						}else if($field['uitype'] == '23' ) {
							if($field['value']!=''){
								$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
								
							}
							
						}
						if(array_key_exists('label',$field['value'])){
							if($field['value']['label']){
								$field['value']['label'] = html_entity_decode($field['value']['label'], ENT_QUOTES, $default_charset);
							}
						}else{
							if($field['name'] == 'description'){
								$field['value'] =  trim(strip_tags($field['value']));
							}else{
								$field['value'] = html_entity_decode($field['value'], ENT_QUOTES, $default_charset);
							}
						}

						//code start for isAjaxEdit by suresh
						if($IS_AJAX_ENABLED && $fieldModel->isEditable() == 'true' && $fieldModel->isAjaxEditable() == 'true' && $field['name'] != 'imagename' && !in_array($module,array('Documents','Emails','SMSNotifier','PBXManager'))){
							$field['is_Ajaxedit'] = true;
							$fieldModelinfo = $fieldModel->getFieldInfo();
							$field['type']['name'] = $fieldModelinfo['type'];
							if($field['type']['name'] == 'salutation'){
								$field['type']['name'] = "string";
							}
							if($fieldModelinfo['type'] == 'picklist' || $fieldModelinfo['type'] == 'multipicklist') {
								$roleid = $current_user->roleid;
								$picklistValues1 = array();
								if($fieldModel->isRoleBased()){
									$picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($field['name'],$roleid);
								}else{
									$picklistValues = Vtiger_Util_Helper::getPickListValues($field['name']);
								}
								foreach($picklistValues as $pvalue){
									$picklistValues1[] = array('value'=>$pvalue, 'label'=>vtranslate($pvalue,$module));
								}
								$field['type']['picklistValues'] = $picklistValues1;
							}
							if($fieldModelinfo['type'] == 'reference'){
								$refModules = $fieldModel->getReferenceList();
								$refModule = array();
								foreach ($refModules as $key => $value) {
									$refModule[] = array('value'=>$value,'label'=>vtranslate($value,$value));
								}
								$field['type']['refersTo'] = $refModule;
							}
						}else{
							$field['is_Ajaxedit'] = false;
							$fieldModelinfo = $fieldModel->getFieldInfo();
							$field['type']['name'] = $fieldModelinfo['type'];
							if($field['type']['name'] == 'salutation'){
								$field['type']['name'] = "string";
							}
							if($fieldModelinfo['type'] == 'picklist' || $fieldModelinfo['type'] == 'multipicklist') {
								$roleid = $current_user->roleid;
								$picklistValues1 = array();

								if($fieldModel->isRoleBased()){
									$picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($field['name'],$roleid);
								}else{
									$picklistValues = Vtiger_Util_Helper::getPickListValues($field['name']);
								}
								foreach($picklistValues as $pvalue){
									$picklistValues1[] = array('value'=>$pvalue, 'label'=>vtranslate($pvalue,$module));
								}
								$field['type']['picklistValues'] = $picklistValues1;
							}
							if($fieldModelinfo['type'] == 'reference'){
								$refModules = $fieldModel->getReferenceList();
								$refModule = array();
								foreach ($refModules as $key => $value) {
									$refModule[] = array('value'=>$value,'label'=>vtranslate($value,$value));
								}
								$field['type']['refersTo'] = $refModule;
							}

						}
						$field['mandatory'] = $fieldModel->isMandatory();
						// code end for isAjaxEdit by suresh

						$fields[] = $field;
					}
				}
				
			}
			$permittedFields = array();
			foreach ($fields as $key => $field) {
				if(in_array($field['name'], $lineItemsTotalFields)){
					if($field['name'] == 'hdnTaxType'){
						$count = 0;
					}
					if($field['name'] == 'hdnSubTotal'){
						$count = 1;
					}
					if($field['name'] == 'hdnDiscountAmount'){
						$count = 2;
					}
					if($field['name'] == 'hdnDiscountPercent'){
						$count = 3;
					}
					if($field['name'] == 'tax1'){
						$count = 4;
					}
					if($field['name'] == 'tax2'){
						$count = 5;
					}
					if($field['name'] == 'tax3'){
						$count = 6;
					}
					if($field['name'] == 'hdnS_H_Percent'){
						$count = 7;
					}
					if($field['name'] == 'txtAdjustment'){
						$count = 8;
					}
					if($field['name'] == 'hdnGrandTotal'){
						$count = 9;
					}
					$lineItemsTotalFieldGroup[$count] = $field;
					unset($fields[$key]);
				}else{
					$permittedFields[] = $field;
				}

			}

			$blockname = array_search($blocklabel,$newblocks);
			$blocklabel = html_entity_decode($blocklabel, ENT_QUOTES, $default_charset);
			$blocks[] = array('name'=>$blockname,'label' => $blocklabel, 'fields' => $permittedFields );
		}
		if(!empty($lineItemsTotalFieldGroup)){
			ksort($lineItemsTotalFieldGroup);
			$blocks[] = array('name'=>'ITEMS_DETAILS_TOTAL','label' => vtranslate('Items Detail Total','Webapp'), 'fields' => $lineItemsTotalFieldGroup );
		}

		if($module == 'Emails'){
			foreach($blocks as $key => $value){
				if($value['label'] == 'Emails_Block1' || $value['label'] == 'Emails_Block2' || $value['label'] == 'Emails_Block3'){
					foreach($value['fields'] as $keys => $field){
						$blocks[0]['fields'][] = $field;
					}
					unset($blocks[$key]);
				}
			}
		}
		
		$sections = array();
		$moduleFieldGroupKeys = array_keys($moduleFieldGroups);
		foreach($moduleFieldGroupKeys as $blocklabel) {
			// Eliminate empty blocks
			if(isset($groups[$blocklabel]) && !empty($groups[$blocklabel])) {
				$sections[] = array( 'label' => $blocklabel, 'count' => count($groups[$blocklabel]) );
			}
		}
		
		$recordLabel = html_entity_decode($resultRecord['recordLabel'], ENT_QUOTES, $default_charset);

		if($module == 'Events') {
			global $adb;
			$recordId = explode('x',$resultRecord['id']);
			
			$getInvites = $adb->pquery("SELECT * FROM vtiger_invitees where activityid = ?", array($recordId[1]));
			$countInvities = $adb->num_rows($getInvites);
			$id = ''; // for Detailview
			$invite_user_value = array(); //for Editview
			for($i=0;$i<$countInvities;$i++){
				$inviteId = $adb->query_result($getInvites, $i, 'inviteeid');
				$userRecordModel = Vtiger_Record_Model::getInstanceById($inviteId, 'Users');
				$firstname = $userRecordModel->get('first_name');
				$firstname = html_entity_decode($firstname, ENT_QUOTES, $default_charset);
				$lastname = $userRecordModel->get('last_name');
				$lastname = html_entity_decode($lastname, ENT_QUOTES, $default_charset);
				if($i == 0) {
					$id .= $firstname." ".$lastname;
				} else {
					$id .= ", ".$firstname." ".$lastname;
				}
				$invite_user_value[] = array('value'=>$inviteId,'label'=>$firstname." ".$lastname);
			}
			
			$invitefields[] = array('name'=>'invite_user', 'value'=>$id,'invite_user_value'=>$invite_user_value, 'label' => vtranslate('LBL_INVITE_USERS',$module), 'uitype' => '33', 'summaryfield' => '0', 'typeofdata' => 'V~O','is_Ajaxedit' => false);
			$blocks[] = array('name'=>'INVITE_USER','label' => vtranslate("LBL_INVITE_USER_BLOCK",$module), 'fields'=> $invitefields);
		}
		
		if($module == 'Leads' || $module == 'Contacts'){

			if($totalRecords > 0){
				$sms_notifier = true;
				$sms_status_message = '';
			}else{
				$sms_notifier = false;
				$sms_status_message = vtranslate('You do not configure SMS Notifier in CRM. Please configure SMS Notifier in your CRM to use this feature.','Webapp');
			}	
			$modifiedResult = array('blocks' => $blocks, 'id' => $resultRecord['id'], 'recordLabel' => $recordLabel,'sms_notifier'=>$sms_notifier,'sms_status_message'=>$sms_status_message,'editAction'=>$editAction,'deleteAction'=>$deleteAction,'duplicateAction'=>$duplicateAction,'commentModuleAccess'=>$commentModuleAccess,'ActivityModuleAccess'=>$ActivityModuleAccess);
			if($module == 'Leads'){
				$recordModel = Vtiger_Record_Model::getInstanceById($recordid[1],$module);
				if(Users_Privileges_Model::isPermitted($moduleModel->getName(), 'ConvertLead', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId()) && !$recordModel->isLeadConverted()){
					$ConvertLead = true;
				}else{
					$ConvertLead = false;
				}
				$modifiedResult['ConvertLead'] = $ConvertLead;
			}
		}else{
			$modifiedResult = array('blocks' => $blocks, 'id' => $resultRecord['id'], 'recordLabel' => $recordLabel,'editAction'=>$editAction,'deleteAction'=>$deleteAction,'duplicateAction'=>$duplicateAction,'commentModuleAccess'=>$commentModuleAccess,'ActivityModuleAccess'=>$ActivityModuleAccess);
		}
		//code for image url
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordid[1], $module);
		$imageDetails = $parentRecordModel->getImageDetails();
		if(!empty($imageDetails)){
			global $site_URL;
			$modifiedResult['ImageUrl'] = $site_URL.$imageDetails[0]['path'].'_'.$imageDetails[0]['name'];
		}else{
			$modifiedResult['ImageUrl'] = "";
		}
		$modifiedResult['isAttachmentSupport'] = $isAttachmentSupport;
		
		$checkShortcut = $adb->pquery("SELECT shortcutid FROM webapp_record_shortcut WHERE recordid = ? AND userid = ? AND module = ? ",array($recordid[1],$current_user->id,$module));
		if($adb->num_rows($checkShortcut) == 0){
			$modifiedResult['recordShortcut'] = true;
		}else{
			$modifiedResult['recordShortcut'] = false;
		}

		$modifiedResult['modulename'] = $module;
		$modifiedResult['modulelabel'] = vtranslate($module,$module);
		
		//code start for check in checkout for Events
		if($module == 'Events'){
			$attendance_data = $this->attendance_status($recordid[1]);
			$modifiedResult['attendance_status'] = $attendance_data['attendance_status'];
			if($attendance_data['ctattendanceid'] != ''){
				$modifiedResult['ctattendanceid'] = Webapp_WS_Utils::getEntityModuleWSId('CTAttendance').'x'.$attendance_data['ctattendanceid'];
			}else{
				$modifiedResult['ctattendanceid'] = $attendance_data['ctattendanceid'];
			}

			$latlongData = $this->getLatLongOfRecord($recordid[1]);
			$modifiedResult['latitude'] = $latlongData['lat'];
			$modifiedResult['longitude'] = $latlongData['long'];
		}
		//code End for check in checkout for Events

		if($labelFields) $modifiedResult['labelFields'] = $labelFields;
		
		return $modifiedResult;
	}

	function getLatLongOfRecord($recordid){
		global $adb;
		$data['lat'] = "";
		$data['long'] = "";
		if($recordid){
			$result  = $adb->pquery("SELECT * FROM `ct_address_lat_long` WHERE recordid = ? ",array($recordid));
			if($adb->num_rows($result) > 0){
				$data['lat'] = $adb->query_result($result,0,'latitude');
				$data['long'] = $adb->query_result($result,0,'longitude');
			}

		}

		return $data;
	}

	function attendance_status($recordid){
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$employee_name = $current_user->id;

		$current_user =  Users::getActiveAdminUser();
		$recentEvent_data = array();
		$generator = new QueryGenerator('CTAttendance', $current_user);
		$generator->setFields(array('employee_name','attendance_status','createdtime','modifiedtime','id'));
		$generator->addCondition('attendance_status', 'check_in', 'e');
		$eventQuery = $generator->getQuery();
		$eventQuery .= " AND vtiger_ctattendance.employee_name = '$employee_name' AND vtiger_ctattendance.eventid = '$recordid'";
		
		$query = $adb->pquery($eventQuery);
		
		$num_rows = $adb->num_rows($query);
		if( $num_rows > 0){
			$ctattendanceid = $adb->query_result($query,0,'ctattendanceid');
			$attendance_status = true;
		} else {
			$attendance_status = false;
			$ctattendanceid = '';
		}
		$data = array();
		$data['attendance_status'] = $attendance_status;
		$data['ctattendanceid'] = $ctattendanceid;
		return $data;
	}
}

?>