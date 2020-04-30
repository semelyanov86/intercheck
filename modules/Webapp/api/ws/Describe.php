<?php

include_once 'include/Webservices/DescribeObject.php';

class Webapp_WS_Describe extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb;
		$current_user = $this->getActiveUser();
		$roleid = $current_user->roleid;
		$module = trim($request->get('module'));
		if(empty($module)){
			$message = vtranslate('Required fields not found','Webapp');
			throw new WebServiceException(404,$message);
		}
		$isFilter = trim($request->get('isFilter'));
		if($module == 'Users'){
			$current_user = Users::getActiveAdminUser();
		}
		$describeInfo = vtws_describe($module, $current_user);
		
		$fields = $describeInfo['fields'];
  
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$QuickCreateAction = $moduleModel->isQuickCreateSupported();
		$describeInfo['QuickCreateAction'] = $QuickCreateAction;
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
			
			$restrictedFields = array('sendnotification','duration_hours','tax1','tax2','tax3','isconvertedfromlead','filelocationtype','filestatus','fileversion');
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
			
			if($field['type']['defaultValue'] == 'Select an Option'){
				 $field['type']['defaultValue'] = ""; 
			}
			
			if($fieldModel) {
				$displaytype = $fieldModel->get('displaytype');
				$uitype =  $fieldModel->get('uitype');
				if($uitype == 15 || $uitype == 33){
					$picklistValues1 = array();
					$picklistValues = Vtiger_Util_Helper::getRoleBasedPicklistValues($field['name'],$roleid);
					if($uitype == 33){
						if(empty($picklistValues)){
							$field['message'] = vtranslate('LBL_NO','Vtiger').' '.vtranslate('LBL_EDIT_PICKLIST_VALUES','Vtiger').' '.vtranslate('LBL_FOUND','Vtiger');
						}
					}
					foreach($picklistValues as $pvalue){
						$picklistValues1[] = array('value'=>$pvalue, 'label'=>vtranslate($pvalue,$module));
						$field['type']['picklistValues'] = $picklistValues1;
					}
						
				}

				$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
				if(in_array($uitype, $refrenceUitypes)){
					$refersTo = $field['type']['refersTo'];
					if(empty($refersTo)){
						unset($field);
						continue;
					}
				}

				if($uitype == 56){
					if($field['default'] == 'on'){
						$field['default'] = "1";
					}else{
						$field['default'] = "0";
					}
					$field['type']['defaultValue'] = $field['default'];
				}

				if($uitype == 33){
						$field['type']['defaultValue'] = "";
					}
				//remove Image Field 
				if($uitype == 69 && $field['name'] != 'imagename'){
					unset($field);
					continue;
				}
				$allowedFields = array('productid','time_start','time_end','duration_hours');
				if($isFilter === 'true'){
					$allowedFields[] = 'modifiedtime';
					$allowedFields[] = 'createdtime';
				}
				if($displaytype != 1 && !in_array($field['name'],$allowedFields)){
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
				$field['blockname'] = vtranslate($fieldBlock->label, $module);
				$getSequencefieldQuery = $adb->pquery("SELECT sequence from vtiger_field where fieldid = ?", array($fieldId));
				$sequence = $adb->query_result($getSequencefieldQuery, 0, 'sequence');
				$field['sequence'] = $sequence;
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
					$field['default'] = $usersWSId.'x'.$current_user->id;
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
			$newFields[] = $field;
		}

		if($module == 'Events'){
			$USER_MODEL = Users_Record_Model::getCurrentUserModel();
			$AccessibleUsers = array_keys($USER_MODEL->getAccessibleUsers());
			$field = array();
			$query = "SELECT * FROM vtiger_users WHERE status='Active' AND  id != ?";
			$result = $adb->pquery($query, array($current_user->id));
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
			$field['label'] = "Invite People";
			$field['mandatory'] = false;
			$field['type']['picklistValues'] = $picklistValues;
            $field['type']['defaultValue'] = trim($picklistValues[0]['value']);
			
			if($field['type']['defaultValue'] == 'Select an Option'){
				 $field['type']['defaultValue'] = ""; 
			}
			if(empty($picklistValues)){
				$field['message'] = vtranslate('LBL_NO','Vtiger').' '.vtranslate('LBL_EDIT_PICKLIST_VALUES','Vtiger').' '.vtranslate('LBL_FOUND','Vtiger');
			}
			$field['type']['name'] = "multipicklist";
			$field['nullable'] = true;
			$field['editable'] = true;
			$field['default'] = "";
			$field['headerfield'] = "0";
			$field['summaryfield'] = "0";
			$field['uitype'] = "33";
			$field['typeofdata'] = "V~O";
			$field['displaytype'] = "1";
			$field['quickcreate'] = "1";
			$field['blockId'] = "0";
			$field['blockname'] = "Invite";
			$field['sequence'] = "1";

			$newFields[] = $field;
		}
		foreach($newFields as $key=> $fields){
			$sort[$key] = $fields['sequence'];
			$newFields[$key]['label'] = html_entity_decode($fields['label'], ENT_QUOTES, $default_charset);
		}
		array_multisort($sort, SORT_ASC, $newFields);
		//start code by suresh to manage block sequence
		$squenceFields = array();
		$tabid = getTabid($module);					 
		$sequenceBlocks = Vtiger_Block_Model::getAllBlockSequenceList($tabid);
		asort($sequenceBlocks);
		
		foreach($sequenceBlocks as $blockId => $value) {
			foreach($newFields as $key=> $fields){
				if($fields['blockId'] == $blockId){
					$squenceFields[] = $fields;
					unset($newFields[$key]);
				}
			}
		}

		foreach($newFields as $key=> $fields){
			$squenceFields[] = $fields;
			unset($newFields[$key]);
		}
		//end sequence code

		$describeInfo['fields'] = $squenceFields;
		
		$response = new Webapp_API_Response();
		$response->setResult(array('describe' => $describeInfo));
		
		return $response;
	}
}
 
