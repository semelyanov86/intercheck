<?php

//include_once 'include/data/CRMEntity.php';

class Webapp_WS_RelatedModuleList extends Webapp_WS_Controller {
	public $totalQuery = "";
	public $totalParams = array();
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		
		global $adb,$site_URL,$current_user;
		$current_user = $this->getActiveUser();
		$sourcemoduleName = trim($request->get('module'));
		$recordid = trim($request->get('record'));
		$tablabel = trim($request->get('tablabel'));

		$index = $request->get('index');
		$size = $request->get('size');
		$limit = ($index*$size) - $size;
		$record = explode('x', $recordid); 
		$relatedModuleName = trim($request->get('relatedmodule'));
		if(!getTabid($sourcemoduleName)){
			throw new WebServiceException(404,'"'.$sourcemoduleName.'" Module does not exists');
		}
		if(!getTabid($relatedModuleName)){
			throw new WebServiceException(404,'"'.$relatedModuleName.'" Module does not exists');
		}
		//get source module tabid
		$sql1 = "select tabid,name,tablabel from vtiger_tab where name='".$sourcemoduleName."'";
		$result1 = $adb->pquery($sql1,array()); 
		$sourcemoduletabid =$adb->query_result($result1,0,'tabid');

		//get Related Module tabid
		$sql3 = "select tabid,name from vtiger_tab where name='".$relatedModuleName."'";
		$result3 = $adb->pquery($sql3,array());
		$relatedmoduletabid =$adb->query_result($result3,0,'tabid');

		//get entity table id
		$sql4 = "select id,name from vtiger_ws_entity where name='".$relatedModuleName."'";
		$result4 = $adb->pquery($sql4,array());
		$relatedmoduleentitytabid =$adb->query_result($result4,0,'id');


		$sqltabname = "select tablename,fieldname from vtiger_entityname where tabid = '".$relatedmoduletabid."'";
		$resulttabname = $adb->pquery($sqltabname,array());
		$entityField = $adb->query_result($resulttabname,0,'fieldname');
		$tablename = $adb->query_result($resulttabname,0,'tablename');
		$tablexplode = explode("_",$tablename);	 
		
		$entityField_array = explode(',',$entityField);
		$entityField = $entityField_array[0];
		
		
		$entityQuery11 = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? and tabid= ?",array($entityField,$relatedmoduletabid));
		$fieldlabel = $adb->query_result($entityQuery11,0,'fieldlabel');
		$fieldlabel = vtranslate($fieldlabel,$relatedModuleName);
		
		//get fieldname
		$sql4 = "select fieldlabel, columnname,tablename, uitype from vtiger_field where tabid='".$relatedmoduletabid."' AND presence IN (0,2)";
		$result4 = $adb->pquery($sql4,array());
		$numofrows1 = $adb->num_rows($result4);
		
		$fieldtabmerge2 = '';
		$relatedfield = '';
		for ($j=0; $j < $numofrows1; $j++){
			$relatedfieldname =$adb->query_result($result4,$j,'columnname');	
			$relatedfieldlabel = $adb->query_result($result4, $j,'fieldlabel');
			$relatedfieldtabname =$adb->query_result($result4,$j,'tablename');
			$relatedfielduitype =$adb->query_result($result4,$j,'uitype');
			$relatedfieldarray1[$relatedfieldname]['label'] =  strip_tags($relatedfieldlabel);
			$relatedfieldarray1[$relatedfieldname]['uitype'] =  $relatedfielduitype;
			$relatedfieldarray12 = $relatedfieldarray1;
			$relatedfieldnamelist[$j] = $relatedfieldname;
			array_push($relatedfieldarray12['crmid'], "crmid");
			array_push($relatedfieldnamelist, "crmid");
			
			$relatedfieldarray =  $relatedfieldname;
			$fieldtabmerge = $relatedfieldtabname.'.'.$relatedfieldarray; 
			if ($fieldtabmerge2 == '') {
				$fieldtabmerge2 .= $fieldtabmerge.',vtiger_crmentity.crmid';	
			}else{
				$fieldtabmerge2 .= ','.$fieldtabmerge;	
			}
			$fieldtabmerge1 = $fieldtabmerge2;	
		}

		$innerjoin .= $tablename.' INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid='.$tablename.".".$tablexplode[1].id. " where ".$tablename.'.'.$tablexplode[1].id ;
		 
		//Campare to sourcemodule and relatedmodule
		$comparetabidsql = "SELECT relation_id,name,label FROM vtiger_relatedlists where tabid = '".$sourcemoduletabid."' AND related_tabid = '".$relatedmoduletabid."' AND name != 'get_history'";
		$getfunctionres = $adb->pquery($comparetabidsql,array());
		
		$relatedfunctionname = array();
		foreach($getfunctionres as $gval){
			$relatedfunctionname = $gval['name'];
			$relation_id = $gval['relation_id'];
			$relation_label = $gval['label'];
		}
		global $currentModule;
		$currentModule = $sourcemoduleName;
		if($sourcemoduleName == $relatedModuleName){
			$relation_label = $tablabel;
		}

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($record[1], $sourcemoduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relation_label);
		$query = $relationListView->getRelationQuery();

		//echo $relation_label;exit;
		if(!empty($index) && !empty($size)){
			$this->totalQuery = $query;
			$this->totalParams = array();
			$query .= sprintf(" LIMIT %s, %s", $limit, $size);
		}
		
		
		$getfunctionres = $adb->pquery($query,array());
		$numofrows2 = $adb->num_rows($getfunctionres);
		
		for ($i=0; $i < $numofrows2; $i++) { 
			foreach($relatedfieldarray12 as $fieldnamekey => $fieldValue) {
				$relatedfetchrecord =$adb->query_result($getfunctionres,$i,$fieldnamekey);
				$fetchrecord[$i][$fieldnamekey]['fieldlabel'] = vtranslate($relatedfieldarray12[$fieldnamekey]['label'], $relatedModuleName, $current_user->language);
				$uitype = $relatedfieldarray12[$fieldnamekey]['uitype'];
								
				if($uitype == 10) {
					$getRelatedFieldValueQuery = $adb->pquery("SELECT label from vtiger_crmentity where crmid = ? and deleted = 0", array($relatedfetchrecord));
					$relatedFieldValue = $adb->query_result($getRelatedFieldValueQuery, 0, 'label');
					$relatedFieldValue = html_entity_decode($relatedFieldValue, ENT_QUOTES, $default_charset); 
					$fetchrecord[$i][$fieldnamekey]['value'] = $relatedFieldValue;
					
					if($fieldnamekey == 'contact_relation' && $relatedModuleName == 'CaseRelation') {
						$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
											INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid  
											WHERE vtiger_seattachmentsrel.crmid = ?", array($relatedfetchrecord));
											
						$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
						$document_path = array();
						
						if($AttachmentQueryCount > 0) {
							$name = $adb->query_result($AttachmentQuery, 0, 'name');
							$Path = $adb->query_result($AttachmentQuery, 0, 'path');
							$attachmentsId = $adb->query_result($AttachmentQuery, 0, 'attachmentsid');
							$imagepath = $site_URL.$Path.$attachmentsId."_".$name;
							$fetchrecord[$i][$fieldnamekey]['url'] = $imagepath;
						} else {
							$fetchrecord[$i][$fieldnamekey]['url'] = '';
						}
						
					}
				} else if($uitype == 53) {
					$getAssignedUserNameQuery = $adb->pquery("SELECT first_name, last_name from vtiger_users where id = ?", array($relatedfetchrecord));
					$first_name = $adb->query_result($getAssignedUserNameQuery, 0, 'first_name');
					$last_name = $adb->query_result($getAssignedUserNameQuery, 0, 'last_name');
					$assigned_user_name = $first_name." ".$last_name;
					$assigned_user_name = html_entity_decode($assigned_user_name, ENT_QUOTES, $default_charset);
					$fetchrecord[$i][$fieldnamekey]['value'] = $assigned_user_name; 
				}else {
					$relatedfetchrecord = html_entity_decode($relatedfetchrecord, ENT_QUOTES, $default_charset);
					$fetchrecord[$i][$fieldnamekey]['value'] = $relatedfetchrecord;
					if($fieldnamekey == 'crmid' && $relatedModuleName == 'Documents') {
						$AttachmentQuery =$adb->pquery("select vtiger_attachments.attachmentsid, vtiger_attachments.name, vtiger_attachments.subject, vtiger_attachments.path FROM vtiger_seattachmentsrel
											INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid 
											LEFT JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_seattachmentsrel.crmid 
											WHERE vtiger_seattachmentsrel.crmid = ?", array($relatedfetchrecord));
											
						$AttachmentQueryCount = $adb->num_rows($AttachmentQuery);
						$document_path = array();
						
						if($AttachmentQueryCount > 0) {
							for($j=0;$j<$AttachmentQueryCount;$j++) {
								$name = $adb->query_result($AttachmentQuery, $j, 'name');
								$Path = $adb->query_result($AttachmentQuery, $j, 'path');
								$attachmentsId = $adb->query_result($AttachmentQuery, $j, 'attachmentsid');
								
								$document_path[] = array('doc_url'.$j=>$site_URL.$Path.$attachmentsId."_".$name, 'file_name'.$j=>$name);
							} 
						} 
						$fetchrecord[$i]['filename']['url'] = $document_path;
					}
				}
				
			}
		} 

		if ($numofrows2 == '') {
			$sql3 = "SELECT relcrmid FROM vtiger_crmentityrel INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid WHERE crmid='".$record[1]."' AND relmodule='".$relatedModuleName."' AND vtiger_crmentity.deleted = 0";
			$result3 = $adb->pquery($sql3,array());
			$numofrows3 = $adb->num_rows($result3);
		 
			for ($k=0; $k < $numofrows3 ; $k++) { 
				$relatedmoduleid =$adb->query_result($result3,$k,'relcrmid');
				$sqlfetchrecord = "select ". $fieldtabmerge1." from ".$innerjoin.'='.$relatedmoduleid."";
				$result5 = $adb->pquery($sqlfetchrecord,array());
				$numofrows5 = $adb->num_rows($result5);
				if ($numofrows5 > 0) {
					
					foreach($relatedfieldarray12 as $fieldnamekey => $fieldValue) {
						$relatedfetchrecord =$adb->query_result($getfunctionres,$i,$fieldnamekey);
						
						$fetchrecord[$i][$fieldnamekey]['fieldlabel'] = vtranslate($relatedfieldarray12[$fieldnamekey]['label'], $relatedModuleName, $current_user->language);
						$uitype = $relatedfieldarray12[$fieldnamekey]['uitype'];
						
						if($uitype == 10) {
							$getRelatedFieldValueQuery = $adb->pquery("SELECT label from vtiger_crmentity where crmid = ? and deleted = 0", array($relatedfetchrecord));
							$relatedFieldValue = $adb->query_result($getRelatedFieldValueQuery, 0, 'label'); 
							$relatedFieldValue = html_entity_decode($relatedFieldValue, ENT_QUOTES, $default_charset);
							$fetchrecord[$i][$fieldnamekey]['value'] = $relatedFieldValue;
						} else if($uitype == 53) {
							$getAssignedUserNameQuery = $adb->pquery("SELECT first_name, last_name from vtiger_users where id = ?", array($relatedfetchrecord));
							$first_name = $adb->query_result($getAssignedUserNameQuery, 0, 'first_name');
							$last_name = $adb->query_result($getAssignedUserNameQuery, 0, 'last_name');
							$assigned_user_name = $first_name." ".$last_name;
							$assigned_user_name = html_entity_decode($assigned_user_name, ENT_QUOTES, $default_charset);
							$fetchrecord[$i][$fieldnamekey]['value'] = $assigned_user_name; 
						}else {
							$relatedfetchrecord = html_entity_decode($relatedfetchrecord, ENT_QUOTES, $default_charset);
							$fetchrecord[$i][$fieldnamekey]['value'] = $relatedfetchrecord;
						}
						
					}
				}
			}
		}
		$allowedParams =  array('recordModule','record','entityFieldlabel','entityFieldValue');
		$permittedRecords =  array();
	    foreach ($fetchrecord as $key => $part) {
			if($relatedModuleName == 'Calendar' || $relatedModuleName == 'Events'){
				//for get Webservice Id of Calender and Events Module
				$EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$part['crmid']['value'])); 
				if($adb->num_rows($EventTaskQuery) > 0){
					$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
					$recordId = $wsid.'x'.$part['crmid']['value'];
					$recordModule = 'Calendar';
				}else{
					$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
					$recordId = $wsid.'x'.$part['crmid']['value'];
					$recordModule = 'Events';
				}
			}else{
				$wsid = Webapp_WS_Utils::getEntityModuleWSId($relatedModuleName);
				$recordId = $wsid.'x'.$part['crmid']['value'];
				$recordModule = $relatedModuleName;
			}

			if($relatedModuleName == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $relatedModuleName;
			}

			$fetchrecord[$key]['recordModule'] = $recordModule;
			$fetchrecord[$key]['record'] = $recordId;
			$fetchrecord[$key]['entityFieldlabel'] = $fieldlabel;
			$fetchrecord[$key]['entityFieldValue'] = $part[$entityField]['value'];
		    if(!Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $part['crmid']['value'])){
				unset($fetchrecord[$key]);
				continue;
			}else{
				$permittedRecords[] = $fetchrecord[$key];
			}
			if($relatedModuleName != 'Calendar'){
				$sort[$part['modifiedtime']['value']] = strtotime($part['modifiedtime']['value']);
			}
		 }
		 array_multisort($sort, SORT_DESC, $permittedRecords);

		foreach($permittedRecords as $key => $part){
			foreach($part as $keys => $value){
				if(!in_array($keys,$allowedParams) && $keys != 'modifiedtime'){
					unset($permittedRecords[$key][$keys]);
				}
			}

		} 

		$isLast = true;
		if($this->totalQuery != ""){
			$totalResults = $adb->pquery($this->totalQuery,$this->totalParams);
			$totalRecords = $adb->num_rows($totalResults);
			if($totalRecords > $index*$size){
				$isLast = false;	
			}else{
				$isLast = true;
			}
		}
		 
		if ($permittedRecords == '') {
			$allrelatedid =  array('relatedtabid' => $relatedmoduleentitytabid,'relatedModuleName'=>$relatedModuleName,'relatedModuleLabel'=>vtranslate($relatedModuleName,$relatedModuleName),'fetchrecord'=>array(),'message'=>vtranslate('LBL_NO_RECORDS_FOUND','Vtiger'),'isLast'=>$isLast);
		}else{

			$allrelatedid =  array('relatedtabid' => $relatedmoduleentitytabid,'relatedModuleName'=>$relatedModuleName,'relatedModuleLabel'=>vtranslate($relatedModuleName,$relatedModuleName),'fetchrecord'=>$permittedRecords,'message'=>'','isLast'=>$isLast);
		}
		$response = new Webapp_API_Response();
		$response->setResult($allrelatedid);
		return $response;
	}		
}
