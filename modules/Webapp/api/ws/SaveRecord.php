<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_SaveRecord extends Webapp_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Webapp_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();

		$module = trim($request->get('module'));

		if(empty($module)){
			$message = vtranslate($module,$module)." ".vtranslate('Module cannot be empty','Webapp');
			throw new WebServiceException(404,$message);
		}

		//relation Operation Pramaters
		$parentModuleName = trim($request->get('sourceModule'));
		$sourceRecord = explode('x',$request->get('sourceRecord'));
		$parentRecordId = $sourceRecord[1];

		//start validation for module & fields
		if(!getTabid($module)){
			$message = vtranslate($module,$module)." ".vtranslate('Module does not exists','Webapp');
			throw new WebServiceException(404,$message);
		}
		if(!empty($parentModuleName) && !getTabid($parentModuleName)){
			$message = vtranslate($parentModuleName,$parentModuleName)." ".vtranslate('Module does not exists','Webapp');
			throw new WebServiceException(404,$message);
		}
		
		$recordid = trim($request->get('record'));
		$is_duplicate = trim($request->get('is_duplicate'));
		$imageurl = $request->get('imageurl');
		$valuesJSONString =  $request->get('values');
		$recurringJSONString =  $request->get('recurring_value');
		$recordModel = Vtiger_Record_Model::getCleanInstance($module);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();

		$values = "";
		if(!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}

		$recurringvalues = "";
		if(!empty($recurringJSONString) && is_string($recurringJSONString)) {
			$recurringvalues = Zend_Json::decode($recurringJSONString);
		} else {
			$recurringvalues = $recurringJSONString; // Either empty or already decoded.
		}

		//Pass TaxType in Inventory Modules
		$lineItemsModules = array('Quotes','Invoice','SalesOrder','PurchaseOrder');
		if(in_array($module,$lineItemsModules)){
			if($values['hdnTaxType'] == ''){
				$values['hdnTaxType'] = 'group';
			}
			$taxes = Inventory_TaxRecord_Model::getProductTaxes();
			$lineItems = $values['LineItems'];
			$values['productid'] = $values['LineItems'][0]['productid'];
			if($module == 'SalesOrder'){
				$values['enable_recurring'] = 0;
				$values['invoicestatus'] = "Created";
			}
			foreach ($values['LineItems'] as $key => $value) {
				$values['LineItems'][$key]['listprice'] = CurrencyField::convertToDBFormat($values['LineItems'][$key]['listprice'], $current_user, $skipConversion);
				if($values['hdnTaxType'] == 'individual'){
					$id = explode('x',$values['LineItems'][$key]['productid']);
					$recordModel = Vtiger_Record_Model::getInstanceById($id[1]);
					$itaxes = $recordModel->getTaxes();
					foreach ($itaxes as $taxname => $ivalue) {
						$values['LineItems'][$key][$taxname] = $ivalue['taxpercentage'];
					}
				}else{
					foreach($taxes as $keys =>$taxValues){
						$taxname = $taxValues->get('taxname');
						$percentage = $taxValues->get('percentage');
						$values['LineItems'][$key][$taxname] = $percentage;
					}
				}
			}
		}
		
		$response = new Webapp_API_Response();

		if (empty($values)) {
			$message = vtranslate('Values cannot be empty!','Webapp');
			$response->setError(404, $message);
			return $response;
		}
		
		if($module == 'SalesOrder'){
			$values['enable_recurring'] = 0;
			$values['invoicestatus'] = "Created";
		}
		
		try {

			// Retrieve or Initalize
			if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
				$this->recordValues = vtws_retrieve($recordid, $current_user);
			} else {
				$this->recordValues = array();
			}
			if($module == 'Calendar' || $module == 'Events'){
				$reminder_time = $values['reminder_time'];
			}
			if($module == 'Events') {
				$invite_user = $values['invite_user'];
				$startDate = $values['date_start'];
				if(!empty($startDate)) {
					//Start Date and Time values
					$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($values['time_start']);
					$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($values['date_start']." ".$startTime);
					list($startDate, $startTime) = explode(' ', $startDateTime);
					$values['time_start'] = $startTime;
					$values['date_start'] = $startDate;
				}

				$endDate = $values['due_date'];
				if(!empty($endDate)) {
					//End Date and Time values
					$endTime = $values['time_end'];
					$endDate = Vtiger_Date_UIType::getDBInsertedValue($values['due_date']);

					if ($endTime) {
						$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
						$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($values['due_date']." ".$endTime);
						list($endDate, $endTime) = explode(' ', $endDateTime);
						$values['time_end'] = $endTime;
						$values['due_date'] = $endDate;
					}
				}

				$time = (strtotime($endTime))- (strtotime($startTime));
				$diffinSec=  (strtotime($endDate))- (strtotime($startDate));
				$diff_days=floor($diffinSec/(60*60*24));
				  
				$hours=((float)$time/3600)+($diff_days*24);
				$minutes = ((float)$hours-(int)$hours)*60; 
				
				$values['duration_hours'] = $hours;
				$values['duration_minutes'] = $minutes;
			}

			// Set the modified values
			foreach($values as $name => $value) {
				if($name == 'invite_user'){
					continue;
				}
				if ($name == 'modcommentsid') {
				    continue;
                }
				if($name != 'LineItems') {
				    if (!$fieldList[$name]) {
                        $response->setError(1405, 'Field ' . $name . ' does not exist!');
                        return $response;
                    }
					$uitype = $fieldList[$name]->get('uitype');
					if($uitype == 33) {
						if($value){
							$value = implode(' |##| ', $value);
						}
					}else if($uitype == 5){
						$value = Vtiger_Date_UIType::getDBInsertedValue($value);
					}
				}

				$this->recordValues[$name] = $value;
			}

			if($module == 'Faq'){
				if(!$this->recordValues['faqcategories']){
					$this->recordValues['faqcategories'] = 'General';
				}
			}
			if($module == 'ModComments'){
				$this->recordValues['commentcontent'] = decode_html($this->recordValues['commentcontent']);
				if ($values['modcommentsid']) {
                    $this->recordValues['id'] = $values['modcommentsid'];
                }
			}

			$EventsParentModule = array('Accounts','Campaigns','HelpDesk','Leads','Potentials');
			if(in_array($parentModuleName,$EventsParentModule) && $module == 'Events'){
				$this->recordValues['parent_id'] = $request->get('sourceRecord');
			}
			if($parentModuleName == 'Contacts' && $module == 'Events'){
				$this->recordValues['contact_id'] = $request->get('sourceRecord');
			}

			// Update or Create
			if (isset($this->recordValues['id'])) {
				$mode = 'edit';
				if($module == 'ServiceContracts'){
					$record_id = explode('x',$recordid);
					$recordModel = Vtiger_Record_Model::getInstanceById($record_id[1],$module);
					$recordModel->set('mode','edit');
					foreach($this->recordValues as $key => $value){
						if($key == 'assigned_user_id'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else if($key == 'sc_related_to'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else{
							$recordModel->set($key,$value);
						}
					}
					$recordModel->set('id',$record_id[1]);
					$recordModel->save();
					$moduleWSId = Webapp_WS_Utils::getEntityModuleWSId($module);
					$recordId = $recordModel->getId();
					$this->recordValues['id'] = $moduleWSId.'x'.$recordId;
				}else{
					$this->recordValues = vtws_update($this->recordValues, $current_user);
			    }
			} else {
				$mode = 'create';
				// Set right target module name for Calendar/Event record
				if ($module == 'Calendar') {
					if (!empty($this->recordValues['eventstatus']) && $this->recordValues['activitytype'] != 'Task') {
						$module = 'Events';
					}
				}
				if($module == 'ServiceContracts'){
					$recordModel = Vtiger_Record_Model::getCleanInstance($module);
					$recordModel->set('mode','');
					foreach($this->recordValues as $key => $value){
						if($key == 'assigned_user_id'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else if($key == 'sc_related_to'){
							$values = explode('x',$value);
							$recordModel->set($key,$values[1]);
						}else{
							$recordModel->set($key,$value);
						}
					}
					$recordModel->save();
					$moduleWSId = Webapp_WS_Utils::getEntityModuleWSId($module);
					$recordId = $recordModel->getId();
					$this->recordValues['id'] = $moduleWSId.'x'.$recordId;
				}else{
					$this->recordValues = vtws_create($module, $this->recordValues, $current_user);
				}
			}
			
			if($parentModuleName && $parentRecordId){
				$ID = explode('x', $this->recordValues['id']);
				$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
				$recordModel = Vtiger_Record_Model::getInstanceById($ID[1],$module);
				$relatedModule = $recordModel->getModule();
				$relatedRecordId = $recordModel->getId();
				if($relatedModule->getName() == 'Events'){
					$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
				}
				$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
				$relationModel->addRelation($parentRecordId, $relatedRecordId);
				
				//To store the relationship between Products/Services and PriceBooks
				if ($parentRecordId && ($parentModuleName === 'Products' || $parentModuleName === 'Services') && $module == 'PriceBooks') {
					$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
					$sellingPricesList = $parentModuleModel->getPricesForProducts($recordModel->get('currency_id'), array($parentRecordId));
					$recordModel->updateListPrice($parentRecordId, $sellingPricesList[$parentRecordId]);
				}
			}
			
			if(in_array($module,$lineItemsModules)){
				$ID = explode('x', $this->recordValues['id']);
				$recordModel = Vtiger_Record_Model::getInstanceById($ID[1]);
				$total = $recordModel->get('hdnGrandTotal');
				$basetable = $recordModel->getModule()->get('basetable');
				$basetableid = $recordModel->getModule()->get('basetableid');
				$lineItems = $values['LineItems'];
				$discountTotal = 0;
				foreach ($lineItems as $lineItem) {
					$lineItem['listprice'] = CurrencyField::convertToDBFormat($lineItem['listprice'], $current_user, $skipConversion);
					$productid = explode('x',$lineItem['productid']);
					$productId = $productid[1];
					if(!empty($lineItem['discount'])){
						global $adb;
						$query = "UPDATE vtiger_inventoryproductrel SET discount_percent=? WHERE id=? AND productid=?";
						$result = $adb->pquery($query,array($lineItem['discount'],$ID[1],$productId));
						$discountTotal = $discountTotal + (($lineItem['listprice'] * $lineItem['quantity']) * $lineItem['discount'] / 100);
					}
				}
				global $adb;
				$total = $total - $discountTotal;
				$query = "UPDATE ".$basetable." SET total = ? WHERE ".$basetableid."=?";
				$result = $adb->pquery($query,array($total,$ID[1]));
				 
			}
			if($module == 'Contacts' || $module == 'Products'){
				if($is_duplicate == '1' && !empty($imageurl)){
					$this->SaveImageAsDuplicateRecord($imageurl,$this->recordValues,$module);
				}else{
					$this->uploadAndSaveFiles($_FILES['imagename'],$this->recordValues,$module);
				}
			}
			if($module == 'Documents' || $module == 'ModComments'){
				global $adb;
				$ID = explode('x', $this->recordValues['id']);
				if(!empty($_FILES['filename']) && $module == 'Documents'){
					$query = "UPDATE vtiger_notes SET filestatus = '1' WHERE notesid = ?";
					$result = $adb->pquery($query,array($ID[1]));
				}
				if($module == 'ModComments'){
					$adb->pquery("UPDATE vtiger_modcomments SET userid = ? WHERE modcommentsid = ?",array($current_user->id,$ID[1]));
					$uploadedFileNames = array();
					foreach ($_FILES as $key => $files) {
						$uploadedFileNames[] = $this->uploadAndSaveFiles($files,$this->recordValues,$module);
					}
					if(count($uploadedFileNames)){
						$filename = implode(',',$uploadedFileNames);
						$adb->pquery("UPDATE vtiger_modcomments SET filename = ? WHERE modcommentsid = ?",array($filename,$ID[1]));
					}
				}else{
					$this->uploadAndSaveFiles($_FILES['filename'],$this->recordValues,$module);
				}
			}

			if($module == 'Events' || $module == 'Calendar'){
				global $adb;
				$recordId = explode('x', $this->recordValues['id']);
				if($recordid){
					$delete = $adb->pquery("DELETE FROM vtiger_invitees WHERE activityid=?",array($recordId[1]));
					foreach ($invite_user as $value) {
						$result = $adb->pquery('INSERT INTO vtiger_invitees (activityid,inviteeid,status) values(?,?,?)',array($recordId[1],$value,'sent'));
					}
				}else{
					foreach ($invite_user as $value) {
						$result = $adb->pquery('INSERT INTO vtiger_invitees (activityid,inviteeid,status) values(?,?,?)',array($recordId[1],$value,'sent'));
					}
				}
				if(!empty($recurringvalues)){
					$adb->pquery('DELETE FROM vtiger_activity_recurring_info WHERE activityid = ?',array($recordId[1]));
					$adb->pquery('DELETE FROM vtiger_recurringevents WHERE activityid = ?',array($recordId[1]));
					$recurringdate = Vtiger_Date_UIType::getDBInsertedValue($recurringvalues['recurringdate']);
					$recurringtype = $recurringvalues['recurringtype'];
					$recurringfreq = $recurringvalues['recurringfreq'];
					//$recurringinfo = $recurringvalues['recurringinfo'];
					if($recurringvalues['recurringtype'] == 'Monthly'){
						$recurringMonthType = $recurringvalues['recurringMonthType'];
						if($recurringMonthType == "1"){
							$recurringDayOfMonth = $recurringvalues['recurringDayOfMonth'];
							$recurringinfo = $recurringtype.'::date::'.$recurringDayOfMonth;
						}else{
							$recurringDayOfMonth = $recurringvalues['recurringDayOfMonth'];
							$recurringDayType = $recurringvalues['recurringDayType'];
							if($recurringDayType == '1'){
								$recurringDayType = 'first';
							}else{
								$recurringDayType = 'last';
							}
							$recurringDayOfWeek = $recurringvalues['recurringDayOfWeek'];
							$recurringinfo = $recurringtype.'::day::'.$recurringDayType.'::'.$recurringDayOfWeek;
						}
					}else if($recurringvalues['recurringtype'] == 'Weekly'){
						$recurringWeekDay = Zend_Json::decode($recurringvalues['recurringWeekDay']);
						$recurringinfo = $recurringtype;
						foreach($recurringWeekDay as $keys => $values){
							$recurringinfo = $recurringinfo.'::'.$values;
						}
					}else{
						$recurringinfo = $recurringtype;
					}
					$recurringenddate = Vtiger_Date_UIType::getDBInsertedValue($recurringvalues['recurringenddate']);
					$adb->pquery('INSERT INTO vtiger_recurringevents(activityid,recurringdate,recurringtype,recurringfreq,recurringinfo,recurringenddate) VALUES(?,?,?,?,?,?)',array($recordId[1],$recurringdate,$recurringtype,$recurringfreq,$recurringinfo,$recurringenddate));	
				}
				
				if($reminder_time != ''){
					$recurringQuery = $adb->pquery('SELECT * FROM vtiger_recurringevents WHERE activityid =?',array($recordId[1]));
					if($adb->num_rows($recurringQuery) > 0){
						$recurringid = $adb->query_result($recurringQuery,0,'recurringid');
					}else{
						$recurringid = '0';
					}
					
					if($recordid){
						$reminderquery = $adb->pquery("SELECT * FROM vtiger_activity_reminder WHERE activity_id = ? ",array($recordId[1]));
						if($adb->num_rows($reminderquery) > 0){
							$result = $adb->pquery('UPDATE vtiger_activity_reminder SET reminder_time = ? WHERE activity_id = ?',array($reminder_time,$recordId[1]));
						}else{
							$result = $adb->pquery('INSERT INTO vtiger_activity_reminder (activity_id,reminder_time,reminder_sent,recurringid) values(?,?,?,?)',array($recordId[1],$reminder_time,'0',$recurringid));
						}
					}else{
						$reminderquery = $adb->pquery("SELECT * FROM vtiger_activity_reminder WHERE activity_id = ? ",array($recordId[1]));
						if($adb->num_rows($reminderquery) > 0){
							$result = $adb->pquery('UPDATE vtiger_activity_reminder SET reminder_time = ? WHERE activity_id = ?',array($reminder_time,$recordId[1]));
						}else{
							$result = $adb->pquery('INSERT INTO vtiger_activity_reminder (activity_id,reminder_time,reminder_sent,recurringid) values(?,?,?,?)',array($recordId[1],$reminder_time,'0',$recurringid));
						}
					}
				}else{
					$reminderquery = $adb->pquery("SELECT * FROM vtiger_activity_reminder WHERE activity_id = ? ",array($recordId[1]));
					if($adb->num_rows($reminderquery) > 0){
						$result = $adb->pquery('UPDATE vtiger_activity_reminder SET reminder_time = ? WHERE activity_id = ?',array('0',$recordId[1]));
					}else{
						$result = $adb->pquery('INSERT INTO vtiger_activity_reminder (activity_id,reminder_time,reminder_sent,recurringid) values(?,?,?,?)',array($recordId[1],'0','0','0'));
					}
				}
			}
			
			// Update the record id
			$request->set('record', $this->recordValues['id']);
			
			if($request->get('user_lat')!='' && $request->get('user_long')!='' && $request->get('user_id')!=''){
				
				if($this->recordValues['id']!=''){
					global $adb;
					$date_var = date("Y-m-d H:i:s");
					$userId = explode('x', $request->get('user_id'));
					$recordId = explode('x', $this->recordValues['id']);
					$createdtime = $adb->formatDate($date_var, true);
					$query = $adb->pquery("INSERT INTO webapp_userderoute (userid, latitude, longitude, createdtime,action,record) VALUES (?,?,?,?,?,?)", array($userId[1], $request->get('user_lat'), $request->get('user_long'), $createdtime,$mode,$recordId[1]));
					
				}
				
			}
			$result = array('id'=>$this->recordValues['id'],'module'=>$module,'message'=>vtranslate('Record save successfully','Webapp'));
			// Gather response with full details
			$response->setResult($result);
			//$response = parent::process($request);
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}

	function SaveImageAsDuplicateRecord($imageUrl,$entity,$module){
			$contents=file_get_contents($imageUrl);
			$name = basename($imageUrl);
			$imagename = explode('_',$name);
			foreach($imagename as $key => $value){
				if($key == 1){
					$image = $value;
				}
				if($key > 1){
					$image.= "_".$value;
				}
				
			}
			global $adb,$site_URL,$root_directory;
			$typeQuery = $adb->pquery('SELECT type FROM vtiger_attachments WHERE attachmentsid = ?',array($imagename[0]));
			$type = $adb->query_result($typeQuery,0,'type');
            $docID = explode('x', $entity['id']);
           
            $current_user = $this->getActiveUser();
            $moduleName = $module;
            $storagePath = 'storage/';
            $year  = date('Y');
            $month = date('F');
            $day   = date('j');
            $week  = '';
            
			$date_var = date("Y-m-d H:i:s");
			
            if (!is_dir($root_directory.$storagePath . $year)) {
                mkdir($root_directory.$storagePath . $year);
                chmod($root_directory.$storagePath . $year, 0777);
            }

            if (!is_dir($root_directory.$storagePath . $year . "/" . $month)) {
                mkdir($root_directory.$storagePath . "$year/$month");
                chmod($root_directory.$storagePath . "$year/$month", 0777);
            }

            if ($day > 0 && $day <= 7){
                $week = 'week1';
            }elseif ($day > 7 && $day <= 14){
                $week = 'week2';
            }elseif ($day > 14 && $day <= 21){
                $week = 'week3';
            }elseif ($day > 21 && $day <= 28){
                $week = 'week4';
            }else{
                $week = 'week5'; 
            }
            
            if (!is_dir($root_directory.$storagePath . $year . "/" . $month . "/" . $week)) {
                mkdir($root_directory.$storagePath . "$year/$month/$week");
                chmod($root_directory.$storagePath . "$year/$month/$week", 0777);
            }
            $interior = $storagePath . $year . "/" . $month . "/" . $week . "/";
            $crm_id = $adb->getUniqueID("vtiger_crmentity");
            $save_path = $interior.$crm_id.'_'. $image;
            $upload_status = file_put_contents($save_path,$contents);
            if($upload_status && $moduleName == 'Contacts'){
				$delquery = 'delete from vtiger_seattachmentsrel where crmid = ?';
				$adb->pquery($delquery, array($docID[1]));
				
				$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($crm_id, $current_user->id, $current_user->id, $moduleName." Image",'', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
				$adb->pquery($sql1, $params1);
				//Add entry to attachments
				$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
				$params2 = array($crm_id, $image,'', $type, $interior);
				$adb->pquery($sql2, $params2);
				//Add relation
				$sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
				$params3 = array($docID[1],$crm_id);
				$adb->pquery($sql3, $params3);
				$adb->pquery('UPDATE vtiger_contactdetails SET imagename = ? WHERE contactid = ?',array($image,$docID[1]));
			}else if($upload_status && $moduleName == 'Products'){
				$delquery = 'delete from vtiger_seattachmentsrel where crmid = ?';
				$adb->pquery($delquery, array($docID[1]));
				
				$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($crm_id, $current_user->id, $current_user->id, $moduleName." Image",'', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
				$adb->pquery($sql1, $params1);
				//Add entry to attachments
				$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
				$params2 = array($crm_id, $image,'', $type, $interior);
				$adb->pquery($sql2, $params2);
				//Add relation
				$sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
				$params3 = array($docID[1],$crm_id);
				$adb->pquery($sql3, $params3);	
				$adb->pquery('UPDATE vtiger_products SET imagename = ? WHERE productid = ?',array($image,$docID[1]));
			}       
        }
        
	function uploadAndSaveFiles($files,$entity,$module){
		if (!empty($files)) {
            $docID = explode('x', $entity['id']);
            global $adb,$site_URL,$root_directory;
            $current_user = $this->getActiveUser();
            $moduleName = $module;
            $storagePath = 'storage/';
            $year  = date('Y');
            $month = date('F');
            $day   = date('j');
            $week  = '';
            
			$date_var = date("Y-m-d H:i:s");
			
            if (!is_dir($root_directory.$storagePath . $year)) {
                mkdir($root_directory.$storagePath . $year);
                chmod($root_directory.$storagePath . $year, 0777);
            }

            if (!is_dir($root_directory.$storagePath . $year . "/" . $month)) {
                mkdir($root_directory.$storagePath . "$year/$month");
                chmod($root_directory.$storagePath . "$year/$month", 0777);
            }

            if ($day > 0 && $day <= 7){
                $week = 'week1';
            }elseif ($day > 7 && $day <= 14){
                $week = 'week2';
            }elseif ($day > 14 && $day <= 21){
                $week = 'week3';
            }elseif ($day > 21 && $day <= 28){
                $week = 'week4';
            }else{
                $week = 'week5'; 
            }
            
            if (!is_dir($root_directory.$storagePath . $year . "/" . $month . "/" . $week)) {
                mkdir($root_directory.$storagePath . "$year/$month/$week");
                chmod($root_directory.$storagePath . "$year/$month/$week", 0777);
            }
            $interior = $storagePath . $year . "/" . $month . "/" . $week . "/";
            $crm_id = $adb->getUniqueID("vtiger_crmentity");
            $upload_status = move_uploaded_file($files['tmp_name'],$interior.$crm_id.'_'. $files['name']);
            if($upload_status && $moduleName == 'Documents'){
	            
	            $lastInsertedId = $adb->pquery("select attachmentsid from vtiger_attachments order by attachmentsid DESC limit 0,1");
	            $attachmentsid = $adb->query_result($lastInsertedId, 0, 'attachmentsid');
	            $query1 = $adb->pquery("insert into vtiger_crmentity (`crmid`,`setype`) VALUES(?,?)",array($crm_id,'Documents Attachment'));
	            $query2 = $adb->pquery("insert into vtiger_attachments (`attachmentsid`,`name`,`type`,`path`) VALUES(?,?,?,?)",array($crm_id,$files['name'],$files['type'],$interior));
	            $grtLastInserted = $adb->pquery("select attachmentsid,subject from vtiger_attachments where attachmentsid > ".$attachmentsid);
	            $total = $adb->num_rows($grtLastInserted);
	            for ($i=0; $i < $total; $i++) { 
	                $grtAttachmentsId = $adb->query_result($grtLastInserted, $i, 'attachmentsid');
	                $subject = $adb->query_result($grtLastInserted, $i, 'subject');
	                $adb->pquery("insert into vtiger_seattachmentsrel (`crmid`,`attachmentsid`) VALUES(?,?)",array($docID[1],$grtAttachmentsId));
	            }
	            $adb->pquery("UPDATE vtiger_notes SET filename = '".$files['name']."', filetype = '".$files['type']."', filelocationtype = 'I', filesize = '".$files['size']."' WHERE notesid = ".$docID[1]);
            }if($upload_status && $moduleName == 'ModComments'){
	            
	            $lastInsertedId = $adb->pquery("select attachmentsid from vtiger_attachments order by attachmentsid DESC limit 0,1");
	            $attachmentsid = $adb->query_result($lastInsertedId, 0, 'attachmentsid');
	            $query1 = $adb->pquery("insert into vtiger_crmentity (`crmid`,`setype`) VALUES(?,?)",array($crm_id,'ModComments Attachment'));
	            $query2 = $adb->pquery("insert into vtiger_attachments (`attachmentsid`,`name`,`type`,`path`) VALUES(?,?,?,?)",array($crm_id,$files['name'],$files['type'],$interior));
	            $grtLastInserted = $adb->pquery("select attachmentsid,subject from vtiger_attachments where attachmentsid > ".$attachmentsid);
	            $total = $adb->num_rows($grtLastInserted);
	            for ($i=0; $i < $total; $i++) { 
	                $grtAttachmentsId = $adb->query_result($grtLastInserted, $i, 'attachmentsid');
	                $subject = $adb->query_result($grtLastInserted, $i, 'subject');
	                $adb->pquery("insert into vtiger_seattachmentsrel (`crmid`,`attachmentsid`) VALUES(?,?)",array($docID[1],$grtAttachmentsId));
	            }
	            $adb->pquery("UPDATE vtiger_modcomments SET filename = '".$grtAttachmentsId."' where modcommentsid = ".$docID[1]);
            }else if($upload_status && $moduleName == 'Contacts'){
				$delquery = 'delete from vtiger_seattachmentsrel where crmid = ?';
				$adb->pquery($delquery, array($docID[1]));
				
				$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($crm_id, $current_user->id, $current_user->id, $moduleName." Image",'', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
				$adb->pquery($sql1, $params1);
				//Add entry to attachments
				$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
				$params2 = array($crm_id, $files['name'],'', $files['type'], $interior);
				$adb->pquery($sql2, $params2);
				//Add relation
				$sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
				$params3 = array($docID[1],$crm_id);
				$adb->pquery($sql3, $params3);
				$adb->pquery('UPDATE vtiger_contactdetails SET imagename = ? WHERE contactid = ?',array($files['name'],$docID[1]));
			}else if($upload_status && $moduleName == 'Products'){
				$delquery = 'delete from vtiger_seattachmentsrel where crmid = ?';
				$adb->pquery($delquery, array($docID[1]));
				
				$sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($crm_id, $current_user->id, $current_user->id, $moduleName." Image",'', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
				$adb->pquery($sql1, $params1);
				//Add entry to attachments
				$sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
				$params2 = array($crm_id, $files['name'],'', $files['type'], $interior);
				$adb->pquery($sql2, $params2);
				//Add relation
				$sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
				$params3 = array($docID[1],$crm_id);
				$adb->pquery($sql3, $params3);	
				$adb->pquery('UPDATE vtiger_products SET imagename = ? WHERE productid = ?',array($files['name'],$docID[1]));
			}  
			return $crm_id;     
        }
	}
	
}
