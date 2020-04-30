<?php

include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_NewDescribe extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {

		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb;
		$current_user = $this->getActiveUser();
		$roleid = $current_user->roleid;
		$module = trim($request->get('module'));
		
		$idComponents = explode('x',$request->get('record'));
		$record = $idComponents[1];
		if(empty($module)){
			$message = vtranslate('Module cannot be empty!','Webapp');
			throw new WebServiceException(404,$message);
		}
		if($module == 'Users'){
			$current_user = Users::getActiveAdminUser();
		}
		
		$isFilter = trim($request->get('isFilter'));
		$describeInfo = vtws_describe($module, $current_user);
		$fields = $describeInfo['fields'];
		$describe = array();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		
		$taxFields = array();
		if(in_array($module,array('Invoice','Quotes','SalesOrder','PurchaseOrder'))){
			$inventoryTaxes = Inventory_TaxRecord_Model::getProductTaxes();
			foreach($inventoryTaxes as $tax){
				$taxFields[] = $tax->get('taxname');	
			}

		 	$lineItemsFields = array('productid','quantity','listprice','comment','discount_amount','discount_percent','hdnS_H_Percent','txtAdjustment','hdnDiscountPercent','hdnDiscountAmount','hdnTaxType');
		 	$otherhdnFields = array('txtAdjustment','hdnDiscountPercent','hdnDiscountAmount','hdnTaxType');

		}

		$fieldModels = $moduleModel->getFields();

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

		if($module == 'Assets'){
			$entityQuery = $adb->pquery("SELECT * FROM webapp_asset_field WHERE module = ?",array($module));
			$entityField = $adb->query_result($entityQuery,0,'fieldname');
			$entityField_array = explode(':',$entityField);
			$entityField = $entityField_array[2];
		}
		
		$entityQuery11 = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? and tabid= ?",array($entityField,$tabid));
		$fieldlabel = $adb->query_result($entityQuery11,0,'fieldlabel');
		$fieldlabel = vtranslate($fieldlabel,$modulename);
		if($module == 'Documents' && $entityField == 'title'){
			$entityField = 'notes_title';
		}
		if($module == 'HelpDesk' && $entityField == 'title'){
			$entityField = 'ticket_title';
		}
		$describeInfo['entityField'] = array('label'=>$fieldlabel,'value'=>$entityField);
		
		// code End for Entity Field By suresh /
		
		foreach($fields as $index=>$field) {
			
			if($field['name'] == 'terms_conditions'){
				$field['default'] = html_entity_decode(decode_html($field['default']),ENT_QUOTES,$default_charset);
			}
			if($field['name'] == 'reminder_time'){
				$field['label'] = vtranslate($field['label'],'Events');
			}
			if($field['name'] == 'currency_id' && $module == 'PriceBooks'){
				$field['type'] = array();
				$query = "SELECT id,currency_name FROM  `vtiger_currency_info` WHERE currency_status = 'Active' AND deleted = 0";
				$result = $adb->pquery($query,array());
				$numrows = $adb->num_rows($result);
				$query2 = "SELECT id FROM vtiger_ws_entity WHERE name = 'Currency'";
				$resullt2 = $adb->pquery($query2,array());
				$id = $adb->query_result($resullt2,0,'id');
				for($i=0;$i<$numrows;$i++){
					$currency_name = $adb->query_result($result,$i,'currency_name');
					$currency_name = vtranslate($currency_name,$module);
					$value = $adb->query_result($result,$i,'id');
					$picklistValues[] = array('label'=>$currency_name,'value'=>$id.'x'.$value);
				}
				$field['type']['picklistValues'] = $picklistValues;
				$field['type']['defaultValue'] = trim($picklistValues[0]['value']);
				$field['type']['name'] = 'picklist';
			}
			if($field['name'] == 'folderid' && $module == 'Documents'){
				$field['type'] = array();
				$query = "SELECT folderid,foldername FROM  `vtiger_attachmentsfolder` ORDER BY sequence ASC";
				$result = $adb->pquery($query,array());
				$numrows = $adb->num_rows($result);
				$query2 = "SELECT id FROM vtiger_ws_entity WHERE name = 'DocumentFolders'";
				$resullt2 = $adb->pquery($query2,array());
				$id = $adb->query_result($resullt2,0,'id');
				for($i=0;$i<$numrows;$i++){
					$foldername = $adb->query_result($result,$i,'foldername');
					$foldername =  vtranslate($foldername,$module);
					$folderid = $adb->query_result($result,$i,'folderid');
					$picklistValues[] = array('label'=>$foldername,'value'=>$id.'x'.$folderid);
				}
				$field['type']['picklistValues'] = $picklistValues;
				$field['type']['defaultValue'] = trim($picklistValues[0]['value']);
				$field['type']['name'] = 'picklist';
			}
			$fieldModel = $fieldModels[$field['name']];
			$fieldBlock = $fieldModel->block;
			$fieldId = $fieldModel->id;
			
			$restrictedFields = array('sendnotification','duration_hours','isconvertedfromlead','filelocationtype','filestatus','fileversion');
			if(in_array($field['name'],$restrictedFields)){
					unset($field);
					continue;
			}
			if(($field['name'] == 'modifiedby'  ) && ($module == 'Calendar' || $module == 'Events')){
				continue;
			}
			
			if(($module == 'Calendar' || $module == 'Events') && ($field['name'] == 'activitytype')){
				$defaultactivitytype = $current_user->defaultactivitytype;
				if($defaultactivitytype != ''){
					$field['default'] = trim($defaultactivitytype);
				}
			}
			if(($module == 'Calendar' || $module == 'Events') && ($field['name'] == 'eventstatus')){
				$defaulteventstatus = $current_user->defaulteventstatus;
				if($defaulteventstatus != ''){
					$field['default'] = trim($defaulteventstatus);
				}
			}
			if($field['default'] != '' && $field['type']['name'] == 'picklist'){
				$field['type']['defaultValue'] = trim($field['default']);
			}else{
				$field['type']['defaultValue'] = trim($field['default']);
			}
			
			if($fieldModel) {
				$displaytype = $fieldModel->get('displaytype');
				$uitype =  $fieldModel->get('uitype');
				if($uitype == 15 || $uitype == 33){
					$picklistValues1 = array();
					$picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($field['name'],$roleid);
					foreach($picklistValues as $pvalue){
						$picklistValues1[] = array('value'=>$pvalue, 'label'=>vtranslate($pvalue,$module));
						$field['type']['picklistValues'] = $picklistValues1;
					}
					if($uitype == 33 && $field['default'] != ''){
						$value = explode(' |##| ', $field['default']);
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
						$field['default'] = $multipicklistvalue;
						$field['type']['defaultValue'] = $multipicklistvalue;
					}
				}
				//start code to remove unwanted fields 
				$allowedFields = array('productid','time_start','time_end','duration_hours','quantity','listprice','comment','discount_amount','discount_percent','hdnS_H_Percent','hdnSubTotal','hdnGrandTotal','txtAdjustment','hdnDiscountPercent','hdnDiscountAmount','hdnTaxType');
				/*if(!empty($taxFields)){
					foreach ($taxFields as $fields) {
						$allowedFields[] = $fields;
					}
				}*/
				if($isFilter == true){
					$allowedFields[] = 'modifiedtime';
					$allowedFields[] = 'createdtime';
				}
				
				if($displaytype != 1 && !in_array($field['name'],$allowedFields)){
					unset($field);
					continue;
				}

				if($uitype == 4 && $isFilter != true){
					unset($field);
					continue;
				}
				
				$field['headerfield'] = $fieldModel->get('headerfield');
				$field['summaryfield'] = $fieldModel->get('summaryfield');
				$field['uitype'] = $fieldModel->get('uitype');
				$field['typeofdata'] = $fieldModel->get('typeofdata');
				$field['displaytype'] = $fieldModel->get('displaytype');
				$field['quickcreate'] = $fieldModel->get('quickcreate');
				$field['blockId'] = $fieldBlock->id;
				$field['blockname'] = $fieldBlock->label;
				$field['blocklabel'] = vtranslate($fieldBlock->label, $module);
				$getSequencefieldQuery = $adb->pquery("SELECT sequence from vtiger_field where fieldid = ?", array($fieldId));
				$sequence = $adb->query_result($getSequencefieldQuery, 0, 'sequence');
				$field['sequence'] = $sequence;
				if($field['name'] == 'hdnTaxType'){
					$field['sequence'] = "1";
					$field['type']['defaultValue'] = "group";
				}
				if($field['name'] == 'hdnSubTotal'){
					$field['sequence'] = "2";
				}
				if($field['name'] == 'hdnDiscountAmount'){
					$field['sequence'] = "3";
				}
				if($field['name'] == 'hdnDiscountPercent'){
					$field['sequence'] = "4";
				}
				if($field['name'] == 'hdnS_H_Percent'){
					$field['sequence'] = "5";
				}
				if($field['name'] == 'txtAdjustment'){
					$field['sequence'] = "6";
				}
				if($field['name'] == 'hdnGrandTotal'){
					$field['sequence'] = "7";
				}
				
			}

			if($module == 'Calendar' && $field['name'] == 'time_end'){
				continue;

			}

			if($field['name'] == 'quantity' || $field['name'] == 'listprice'){
				$field['mandatory'] = true;
			}

			if($field['mandatory'] == true){
				$field['quickcreate'] = "0";
			}

			
			if($fieldModel && $fieldModel->getFieldDataType() == 'owner') {
				$currentUser = Users_Record_Model::getCurrentUserModel();
                $users = $currentUser->getAccessibleUsers();
                $usersWSId = Webapp_WS_Utils::getEntityModuleWSId('Users');
                foreach ($users as $id => $name) {
                    unset($users[$id]);
                    $users[$usersWSId.'x'.$id] = $name; 
                }
                
                $groups = $currentUser->getAccessibleGroups();
                $groupsWSId = Webapp_WS_Utils::getEntityModuleWSId('Groups');
                foreach ($groups as $id => $name) {
                    unset($groups[$id]);
                    $groups[$groupsWSId.'x'.$id] = $name; 
                }

				//Special treatment to set default mandatory owner field
				if (!$field['default']) {
					$field['default'] = array("value"=>$usersWSId.'x'.$current_user->id,"label"=>html_entity_decode($current_user->first_name.' '.$current_user->last_name, ENT_QUOTES, $default_charset));
					$field['type']['defaultValue'] = $field['default'];
				}
			}
			if($fieldModel && $fieldModel->get('name') == 'salutationtype') {
				$values = $fieldModel->getPicklistValues();
				$picklistValues = array();
				foreach($values as $value => $label) {
					$picklistValues[] = array('value'=>trim($value), 'label'=>trim($label));
				}
				$field['type']['picklistValues'] = $picklistValues;
			}

			foreach($field as $key => $value){
				if($value == null){
					if(in_array($key, array('nullable','mandatory','editable'))){
						$field[$key] = false;
					}else{
						$field[$key] = "";
					}
				}
			}

			if($field['type']['name'] == 'reference'){
				$refersTo = $field['type']['refersTo'];
				$refModule = array();
				if(!empty($refersTo)){
					foreach ($refersTo as $key => $rModule) {
						$refModule[] = array('value'=>$rModule,'label'=>vtranslate($rModule,$rModule));
					}
					$field['type']['refersTo'] = $refModule;
				}
			}

			if(!empty($record)){
				$recordModel = Vtiger_Record_Model::getInstanceById($record,$module);
				//code start for merge by suresh
				$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,77,78,80,81,101);
				if(in_array($field['uitype'], $refrenceUitypes) && $field['name']!='productid'){
					$recordid = $recordModel->get($field['name']);
					if($recordid){
						if($field['name'] == 'assigned_user_id1'){
							$seQuery = $adb->pquery("SELECT first_name,last_name FROM vtiger_users WHERE id = ?",array($recordid));
							$first_name = $adb->query_result($seQuery,0,'first_name');
							$first_name = html_entity_decode($first_name, ENT_QUOTES, $default_charset);
							$last_name = $adb->query_result($seQuery,0,'last_name');
							$last_name = html_entity_decode($last_name, ENT_QUOTES, $default_charset);
						 	$WSId = Webapp_WS_Utils::getEntityModuleWSId('Users');
							$field['value'] = array('value'=>$WSId.'x'.$recordid,'label'=>$first_name.' '.$last_name);
							$field['refrerenceModule'] = 'Users';
						}else{
							$seQuery = $adb->pquery("SELECT setype,label FROM vtiger_crmentity WHERE crmid = ?",array($recordid));
							$setype = $adb->query_result($seQuery,0,'setype');
							$label = $adb->query_result($seQuery,0,'label');
							$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						 	$WSId = Webapp_WS_Utils::getEntityModuleWSId($setype);
							$field['value'] = array('value'=>$WSId.'x'.$recordid,'label'=>$label);
							$field['refrerenceModule'] = $setype;
						}
					}else{
						$field['value'] = array('value'=>"",'label'=>"");
						$field['refrerenceModule'] = "";
					}
				}else if($field['uitype'] == 53){
					$userid = $recordModel->get($field['name']);
					if($userid){
						$seQuery = $adb->pquery("SELECT first_name,last_name FROM vtiger_users WHERE id = ?",array($userid));
						$first_name = $adb->query_result($seQuery,0,'first_name');
						$first_name = html_entity_decode($first_name, ENT_QUOTES, $default_charset);
						$last_name = $adb->query_result($seQuery,0,'last_name');
						$last_name = html_entity_decode($last_name, ENT_QUOTES, $default_charset);
					 	$WSId = Webapp_WS_Utils::getEntityModuleWSId('Users');
						$field['value'] = array('value'=>$WSId.'x'.$userid,'label'=>$first_name.' '.$last_name);
					}else{
						$field['value'] = array('value'=>"",'label'=>"");
					}
				}else if(in_array($field['name'], $lineItemsFields) && !in_array($field['name'], $otherhdnFields)){
					$productDetails = $recordModel->getProducts();
					foreach ($productDetails as $key => $product) {
						$entityType = $product['entityType'.$key];
						$hdnProductId = $product['hdnProductId'.$key];
						$productName = html_entity_decode($product['productName'.$key], ENT_QUOTES, $default_charset);
						$deleted = $product['productDeleted'.$key];
						if($deleted){
							$deleted = "1";
						}else{
							$deleted = "0";
						}
						$deletedMessage = vtranslate('LBL_THIS',$module).' '.vtranslate($entityType,$entityType).' '.vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$module);

						$disabledMessage = vtranslate($entityType,$entityType).''.vtranslate('LBL_MODULE_DISABLED',$module);
						if($field['name'] == 'productid'){
							$presence = array('0', '2');
							$userPrivModel = Users_Privileges_Model::getInstanceById($current_user->id);
							$ProductmoduleModel = Vtiger_Module_Model::getInstance($entityType);
							if (($userPrivModel->isAdminUser() ||
							$userPrivModel->hasGlobalReadPermission() ||
							$userPrivModel->hasModulePermission($ProductmoduleModel->getId())) && in_array($ProductmoduleModel->get('presence'), $presence)) {
								$disabledModule = false;
							}else{
								$disabledModule = true;
							}
							$WSId = Webapp_WS_Utils::getEntityModuleWSId($entityType);
							$field['value'][] = array('value'=>$WSId.'x'.$hdnProductId,'label'=>$productName,'referenceModule'=>$entityType,'deleted'=>$deleted,'deletedMessage'=>$deletedMessage,'disabledModule'=>$disabledModule,'disabledMessage'=>$disabledMessage);
						}
						if($field['name'] == 'quantity'){
							$field['value'][] = $product['qty'.$key];
						}
						if($field['name'] == 'listprice'){
							$field['value'][] = $product['listPrice'.$key];
						}
						if($field['name'] == 'comment'){
							$field['value'][] = html_entity_decode($product['comment'.$key], ENT_QUOTES, $default_charset);
						}
						if($field['name'] == 'discount_amount'){
							$field['value'][] = $product['discount_amount'.$key];
						}
						if($field['name'] == 'discount_percent'){
							$field['value'][] = $product['discount_percent'.$key];
						}
						
					}
					if(in_array($field['name'],$taxFields)){
						$taxfield = ltrim($field['name'],'tax');
						$field['value'] = $productDetails['1']['final_details']['taxes'][$taxfield]['amount'];
					}
					if($field['name'] == 'hdnS_H_Percent'){
						$field['value'] = $productDetails['1']['final_details']['shipping_handling_charge'];
					}
					
				}else if($field['name'] == 'folderid'){
					$WSId = Webapp_WS_Utils::getEntityModuleWSId('DocumentFolders');

					$field['value'] = $WSId.'x'.$recordModel->get($field['name']);
					/*foreach ($picklistValues as $key => $value) {
						if($value['value'] == $field['value']){
							$fieldvalue = $value;
						}
					}
					$field['value'] = $fieldvalue;*/
				}else if($field['name'] == 'currency_id'){
					$WSId = Webapp_WS_Utils::getEntityModuleWSId('Currency');
					$field['value'] = $WSId.'x'.$recordModel->get($field['name']);
					/*foreach ($picklistValues as $key => $value) {
						if($value['value'] == $field['value']){
							$fieldvalue = $value;
						}
					}
					$field['value'] = $fieldvalue;*/
					
				}else if($field['name'] == 'recurringtype'){
					$field['value'] = Webapp_WS_Utils::RecurringDetails($idComponents[1],$module);
					$recurringInfo = $recordModel->getRecurringDetails();
					$recurringInfo['recurringfreq'] = $recurringInfo['repeat_frequency'];
					unset($recurringInfo['repeat_frequency']);
					if($recurringInfo['recurringtype'] == 'Monthly'){
						if($recurringInfo['repeatMonth'] == 'date'){
							$recurringInfo['recurringMonthType'] = "1";
							$recurringInfo['recurringDayOfMonth'] = $recurringInfo['repeatMonth_date'];
							unset($recurringInfo['repeatMonth_date']);
						}else{
							$recurringInfo['recurringMonthType'] = "2";
							if($recurringInfo['repeatMonth_daytype'] == 'first'){
								$recurringInfo['recurringDayType'] = "1";
							}else{
								$recurringInfo['recurringDayType'] = "2";
							}
							$recurringInfo['recurringDayOfWeek'] = $recurringInfo['repeatMonth_day'];
							unset($recurringInfo['repeatMonth_daytype']);
							unset($recurringInfo['repeatMonth_day']);
						}
						unset($recurringInfo['repeatMonth']);
					}
					if($recurringInfo['recurringtype'] == 'Weekly'){
						$repeat_str =explode(' ',$recurringInfo['repeat_str']);
						$weekdays = explode(',',$repeat_str[1]);
						$totalWeek = count($weekdays);
						$weekstr = "";
						for($i=0;$i<$totalWeek;$i++){
							if($i == $totalWeek-1){
								$weekstr.= str_replace("LBL_DAY", "", $weekdays[$i]);
							}else{
								$weekstr.= str_replace("LBL_DAY", "", $weekdays[$i]).',';
							}
						}
						$recurringInfo['recurringWeekDay'] = $weekstr;
					}
					unset($recurringInfo['repeat_str']);
					if($recurringInfo['recurringcheck'] == vtranslate('LBL_YES','Vtiger')){
						$recurringInfo['recurringcheck'] = "1";
					}else{
						$recurringInfo['recurringcheck'] = "0";
					}
					$field['recurringInfo'] = $recurringInfo;
					
				}else if($field['uitype'] == 33){
					$value = $recordModel->get($field['name']);
					if($value){
						$value = explode(' |##| ', $value);
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
					}else{
						$multipicklistvalue = array();
						$values = '';
					}
					$field['value'] = $values;
					$fieldname = $field['name'];
					$field[$fieldname.'_value'] = $multipicklistvalue;
				}else if($field['name'] == 'reminder_time'){
					$reminder_time = $recordModel->get($field['name']);
					if($reminder_time == 0){
					    $field['value'] = array('days'=>0,'hours'=>0,'minutes'=>0);
				    }else{
				   	   $reminder = $reminder_time;
					   $minutes = (int)($reminder)%60;
					   $hours = (int)($reminder/(60))%24;
					   $days =  (int)($reminder/(60*24));
					   $field['value'] = array('days'=>$days,'hours'=>$hours,'minutes'=>$minutes); 
				   }
				}else if($field['uitype'] == 69){
					global $adb,$site_URL;
					$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
									INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid  
									WHERE vtiger_seattachmentsrel.crmid = ?", array($record));
									
					$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
					$document_path = array();
					
					if($AttachmentQueryCount > 0) {
						$name = $adb->query_result($AttachmentQuery, 0, 'name');
						$Path = $adb->query_result($AttachmentQuery, 0, 'path');
						$attachmentsId = $adb->query_result($AttachmentQuery, 0, 'attachmentsid');
						$ImageUrl = $site_URL.$Path.$attachmentsId."_".$name;
						$value = $recordModel->get($field['name']);
					} else {
						$ImageUrl = "";
						$value = "";
					}
					$field['value'] = $value;
					$field['ImageUrl'] = $ImageUrl;
				}else if(in_array($field['uitype'],array('5','6','23'))){
					if($field['name'] == 'date_start'){
						$value = $recordModel->get('date_start').' '.$recordModel->get('time_start');
						$value = Vtiger_Datetime_UIType::getDisplayDateTimeValue($value);
						$DATETIMEVALUE = explode(' ',$value);
						$field['value'] = $DATETIMEVALUE[0];
					}else if($field['name'] == 'due_date'){
						if($recordModel->get('time_end')){
							$value = $recordModel->get('due_date').' '.$recordModel->get('time_end');
							$value = Vtiger_Datetime_UIType::getDisplayDateTimeValue($value);
							$DATETIMEVALUE = explode(' ',$value);
							$field['value'] = $DATETIMEVALUE[0];
						}else{
							$field['value'] = $recordModel->get($field['name']);
							$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
						}	
					}else{

						$field['value'] = $recordModel->get($field['name']);
						$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
					}
				}else if($field['uitype'] == 70){
					$field['value'] = $recordModel->get($field['name']);
					$field['value'] = Vtiger_Datetime_UIType::getDisplayValue($field['value']);
				}else if($field['name'] == 'terms_conditions'){
					$field['value'] = html_entity_decode(decode_html($recordModel->get($field['name'])),ENT_QUOTES,$default_charset);
				}else if($field['name'] == 'filename'){
						global $adb,$site_URL;
						$query = "SELECT * FROM vtiger_attachments INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid=vtiger_attachments.attachmentsid WHERE vtiger_seattachmentsrel.crmid=?";
						$result = $adb->pquery($query,array($record));
						$filename = $adb->query_result($result,0,'name');
						$attachmentsid = $adb->query_result($result,0,'attachmentsid');
						$path = $adb->query_result($result,0,'path');
						$filepath = $site_URL.$path.$attachmentsid.'_'.$filename;
						if(!empty($filename)){
							$field['filepath'] = $filepath;
							$field['ImageUrl'] = $filepath;
							$field['value'] = $filename;
						}else{
							$field['filepath'] = "";
							$field['ImageUrl'] = "";
							$field['value'] = "";
						}
				}else if($field['name'] == 'time_start' || $field['name'] == 'time_end'){
					$fname = $field['name'];
					if($fname == 'time_start'){
						$value = $recordModel->get('date_start').' '.$recordModel->get('time_start');
						$value = Vtiger_Datetime_UIType::getDisplayValue($value);
						$DATETIMEVALUE = explode(' ',$value);
						if(count($DATETIMEVALUE) > 2){
							$field['value'] = $DATETIMEVALUE[1].' '.$DATETIMEVALUE[2];
						}else{
							$field['value'] = $DATETIMEVALUE[1];
						}
					}else{
						$value = $recordModel->get('due_date').' '.$recordModel->get('time_end');
						$value = Vtiger_Datetime_UIType::getDisplayValue($value);
						$DATETIMEVALUE = explode(' ',$value);
						if(count($DATETIMEVALUE) > 2){
							$field['value'] = $DATETIMEVALUE[1].' '.$DATETIMEVALUE[2];
						}else{
							$field['value'] = $DATETIMEVALUE[1];
						}
					}
				}else if($field['type']['name'] == 'time'){
					$field['value'] = $recordModel->get($field['name']);
					if($field['value']){
						$fieldname = $field['name'];
						$fieldvalue = $field['value'];
						$field['value'] = $fieldModels[$fieldname]->getDisplayValue($fieldvalue);

					}

				}else{
					if($recordModel->get($field['name']) == '--None--'){
						$field['value'] = "";
					}else{
						$field['value'] = $recordModel->get($field['name']);
					}
				}
				if($field['value']){
					if(!is_array($field['value'])){
						$field['value'] = html_entity_decode($field['value'],ENT_QUOTES,$default_charset);
						$field['value'] = html_entity_decode($field['value'],ENT_QUOTES,$default_charset);
					}
					if($field['name'] == 'recurringtype'){
						$field['type']['defaultValue'] = $field['recurringInfo'];
						unset($field['recurringInfo']);
					}else if($field['uitype'] == 33){
						$fieldname = $field['name'];
						$field['type']['defaultValue'] = $field[$fieldname.'_value'];
					}else{
						$field['type']['defaultValue'] = $field['value'];
					}
				}
				//code end for merge
			}

			if($field['uitype'] == 83){
				$field['blockId'] = "1833";
				$field['blockname'] = 'LBL_ITEM_TOTAL';
				$field['blocklabel'] = vtranslate('Item Details Total','Webapp');
				$field['subblockname'] = 'LBL_TAXES_ON_CHARGES';
				$field['subblocklabel'] = vtranslate('LBL_TAXES_ON_CHARGES',$module);

			}else if(in_array($field['name'],array('hdnS_H_Percent','hdnSubTotal','hdnGrandTotal','txtAdjustment','hdnDiscountAmount','hdnDiscountPercent','hdnTaxType'))){
				if($field['name'] == 'hdnS_H_Percent'){
					$field['subblockname'] = 'LBL_CHARGES';
					$field['subblocklabel'] = vtranslate('LBL_CHARGES',$module);
				}
				if($field['name'] == 'txtAdjustment'){
					$isAdd =  true;
					if($field['value'] < 0){
						$isAdd =  false;
						$field['value'] = abs($field['value']);
					}
					$field['isAdd'] = $isAdd;
				}
				$field['blockId'] = "1833";
				$field['blockname'] = 'LBL_ITEM_TOTAL';
				$field['blocklabel'] = vtranslate('Item Details Total','Webapp');
			}
			if($field['uitype'] == 72 || $field['uitype'] == 71){
				$fieldname = $field['name'];
				$fieldModel = $fieldModels[$fieldname]; 
				if(is_array($field['value'])){
					
				}else{
					if($field['value']){
						$field['value'] = $fieldModel->getDisplayValue($field['value']);
						$field['type']['defaultValue'] =$field['value'];
					}
				}
			}
			if($module == 'Documents'){
				if($field['name'] == 'filename'){
					$field['mandatory'] = true;
				}
			}
			if($field['type']['name'] == 'double'){
				if(is_array($field['value'])){
					
				}else{
					if($field['value']){
						$field['value'] = Vtiger_Double_UIType::getDisplayValue($field['value']);
						$field['type']['defaultValue'] = Vtiger_Double_UIType::getDisplayValue($field['type']['defaultValue']);
					}
				}
			}
			if($field['uitype'] == 56){
				if($record){
					if($field['value'] == 'on' || $field['value'] == '1'){
						$field['value'] = "1";
					}else if($field['value'] == "1"){
						
					}else{
						$field['value'] = "0";
					}
					$field['type']['defaultValue'] = $field['value'];
				}else{
					if($field['default']){
						if($field['default'] == 'on' || $field['default'] == '1'){
							$field['default'] = "1";
						}else{
							$field['default'] = "0";
						}
						$field['type']['defaultValue'] = $field['default'];
					}
				}
			}
			$newFields[] = $field;
		}

		if($module == 'Events'){
			$USER_MODEL = Users_Record_Model::getCurrentUserModel();
			$AccessibleUsers = array_keys($USER_MODEL->getAccessibleUsers());
			$field = array();
			$query = "SELECT * FROM vtiger_users WHERE status='Active' AND id!='".$USER_MODEL->getId()."'";
			$result = $adb->pquery($query, array());
			$picklistValues = Array();
			if($adb->num_rows($result) > 0) {
				while ($row = $adb->fetch_array($result)) {
					if(in_array($row['id'], $AccessibleUsers)){
						//Need to decode the picklist values twice which are saved from old ui
						$value = $row['first_name'].' '.$row['last_name'];
						$picklistValues[]= array('value'=>decode_html($row['id']), 'label'=>decode_html($value));
					}
				}
			}
			$field['name'] = "invite_user";
			$field['label'] = vtranslate("LBL_INVITE_USERS",$module);
			$field['mandatory'] = false;
			$field['type']['picklistValues'] = $picklistValues;
			$field['type']['defaultValue'] = "";
           /* $field['type']['defaultValue'] = array('value'=>trim($picklistValues[0]['value']),'label'=>$picklistValues[0]['label']);*/
			$field['type']['name'] = "multipicklist";
			$field['nullable'] = true;
			$field['editable'] = true;
			$field['default'] = "";
			$field['headerfield'] = "";
			$field['summaryfield'] = "0";
			$field['uitype'] = "33";
			$field['typeofdata'] = "V~O";
			$field['displaytype'] = "1";
			$field['quickcreate'] = "1";
			$field['blockId'] = "1844";
			$field['blockname'] = "LBL_INVITE_USER_BLOCK";
			$field['blocklabel'] = vtranslate("LBL_INVITE_USER_BLOCK",$module);
			$field['sequence'] = "1";

			if(!empty($record)){
				$getInvites = $adb->pquery("SELECT * FROM vtiger_invitees where activityid = ?", array($record));
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

				$field['value'] = $invite_user_value;
				if(!empty($field['value'])){
					$field['type']['defaultValue'] = $field['value'];   
				}
			}

			$newFields[] = $field;
		}

		foreach($newFields as $key=> $fields){
			$sort[$key] = $fields['sequence'];
			$newFields[$key]['label'] = html_entity_decode($fields['label'], ENT_QUOTES, $default_charset);
		}
		array_multisort($sort, SORT_ASC, $newFields);
		

		$inventoryTaxes = Inventory_TaxRecord_Model::getProductTaxes();
		foreach($newFields as $key=> $fields){
				foreach($inventoryTaxes as $taxFields){
					if($newFields[$key]['name'] == $taxFields->get('taxname')){
						$newFields[$key]['default'] = $taxFields->get('percentage');
					}
			}
			$newFields[$key]['label'] = html_entity_decode($fields['label'], ENT_QUOTES, $default_charset);
		}
		
		$blocks = $moduleModel->getBlocks();
		foreach($blocks as $block){
			$blockId = $block->get('id');
			$blockname = $block->get('label');
			if($module == 'SalesOrder' && $blockname == 'Recurring Invoice Information'){
				continue;
			}
			$blocklabel = vtranslate($block->get('label'),$module);
			$blockfield = array();
			foreach ($newFields as $key => $value) {
				if($value['blockId'] == $blockId){
					unset($value['blockId']);
					unset($value['blockname']);
					unset($value['blocklabel']);
					$blockfield[] = $value;
					unset($newFields[$key]);
				}
			}
			if(empty($blockfield)){
				continue;
			}
			$describe['blocks'][] = array('blockId'=>$blockId,'blockname'=>$blockname,'blocklabel'=>$blocklabel,'fields'=>$blockfield);
		}

		if(in_array($module,array('Invoice','Quotes','SalesOrder','PurchaseOrder'))){
			$blockfield = array();
			foreach ($newFields as $key => $value) {
				if($value['blockname'] == 'LBL_ITEM_TOTAL'){
					unset($value['blockId']);
					unset($value['blockname']);
					unset($value['blocklabel']);
					$blockfield[] = $value;
					unset($newFields[$key]);
				}
			}
			$describe['blocks'][] = array('blockId'=>'1833','blockname'=>'LBL_ITEM_TOTAL','blocklabel'=>vtranslate('Item Details Total','Webapp'),'fields'=>$blockfield);
		}else if($module == 'Events'){
			$blockfield = array();
			foreach ($newFields as $key => $value) {
				if($value['blockname'] == 'LBL_INVITE_USER_BLOCK'){
					unset($value['blockId']);
					unset($value['blockname']);
					unset($value['blocklabel']);
					$blockfield[] = $value;
					unset($newFields[$key]);
				}
			}
			$describe['blocks'][] = array('blockId'=>'1844','blockname'=>'LBL_INVITE_USER_BLOCK','blocklabel'=>vtranslate('LBL_INVITE_USER_BLOCK',$module),'fields'=>$blockfield);
		}

		//code start for barcode field by suresh
		if(in_array($module,array('Invoice','Quotes','SalesOrder','PurchaseOrder','Products'))){
			$moduleName = 'Products';
			$rs_field=$adb->pquery("SELECT * FROM `webapp_barcode_fields` WHERE module=?",array($moduleName));
            if($adb->num_rows($rs_field) > 0) {
                while($row=$adb->fetch_array($rs_field)) {
                	$fieldname = explode(':', $row['fieldname']);
                    $selectedFields=$fieldname[1];
                }
            }
            if($selectedFields != ''){
            	$describe['barcode_field'] = $selectedFields;
            }else{
            	$describe['barcode_field'] = 'productcode';
            }
		}else{
			$describe['barcode_field'] = "";
		}
		//code end for barcode field by suresh
		
		$response = new Webapp_API_Response();
		$QuickCreateAction = $moduleModel->isQuickCreateSupported();
		$describe['QuickCreateAction'] = $QuickCreateAction;
		$describe['label'] = vtranslate($describeInfo['label'],$module);
		$describe['name'] = $describeInfo['name'];
		$describe['createable'] = $describeInfo['createable'];
		$describe['updateable'] = $describeInfo['updateable'];
		$describe['deleteable'] = $describeInfo['deleteable'];
		$describe['retrieveable'] = $describeInfo['retrieveable'];
		$describe['idPrefix'] = $describeInfo['idPrefix'];
		$describe['isEntity'] = $describeInfo['isEntity'];
		$describe['allowDuplicates'] = $describeInfo['allowDuplicates'];
		$describe['labelFields'] = $describeInfo['labelFields'];
		$describe['entityField'] = $describeInfo['entityField'];


		$response->setResult(array('describe'=>$describe));
		
		return $response;
	}

}
 
