<?php

include_once dirname(__FILE__) . '/FetchRecord.php';

class Webapp_WS_History extends Webapp_WS_FetchRecord {
	
	function process(Webapp_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		
		$page = intval($request->get('page', 0));
		$module = trim($request->get('module', ''));
		$record = trim($request->get('record', ''));
		$mode   = trim($request->get('mode', ''));
		$index = trim($request->get('index'));
		$size = trim($request->get('size'));

		if($module == '' && $record == ''){
			$Message = vtranslate('Missing mandatory input values.','Webapp');
			throw new WebServiceException(404, $Message);
		}

		if($mode == ''){
			$Message = vtranslate('Missing mandatory input mode.','Webapp');
			throw new WebServiceException(404, $Message);
		}
		
		if($module != '' && $record != ''){
			$module = '';
		}

		
		$options = array(
			'module' => $module,
			'record' => $record,
			'mode'   => $mode,
			'page'   => $page,
			'index' => $index,
			'size' => $size
		);
		
		if($index && $size){
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $index);
			$pagingModel->set('limit',intval($size));	
		}
		
		if($module == 'Home'){
			$historyItems = $this->getHistory($pagingModel,'','','');
		}else{
			$historyItems = $this->vtws_history($options, $current_user);
		}
		
  
		$this->resolveReferences($historyItems, $current_user, $module);
		
		
		foreach ($historyItems as $key => $part) {
			$sort[$key] = strtotime($part['modifiedtime']);
		}
		array_multisort($sort, SORT_DESC, $historyItems);
  		$count = 0;
  		foreach ($historyItems as $key => $part) {
			if($index && $size){
				$count++;
				if($count>$size){
					unset($historyItems[$key]);
				}
			}
		}
  		
		$response = new Webapp_API_Response();
		if($index > 1){
			if(count($historyItems) == 0){
				$response->setResult(array('history'=>[],'code'=>404,'message'=>vtranslate('No Activity found','Webapp')));
			}else{
				$result = array('history' => $historyItems);
				$response->setResult($result);
			}
		}else{
			if(count($historyItems) == 0){
				$response->setResult(array('history'=>[],'code'=>404,'message'=>vtranslate('No Activity found','Webapp')));
			}else{
				$result = array('history' => $historyItems);
				$response->setResult($result);
			}
		}
		return $response;
	}
	
	protected function resolveReferences(&$items, $user, $module) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $current_user,$adb; 
		if (!isset($current_user)) $current_user = $user; /* Required in getEntityFieldNameDisplay */
		
		foreach ($items as &$item) {
			
			$item['modifieduser'] = $this->fetchResolvedValueForId($item['modifieduser'], $user);
			if($item['status'] == 4) {
				$item['label'] = str_replace("label",$this->fetchRecordLabelForId($item['id'], $user),$item['label']);
					
			}else if($item['status'] == 2){
				$item['label'] = str_replace("label",$this->fetchRecordLabelForId($item['id'], $user),$item['label']);
			}else if($item['status'] == 1){
				$item['label'] = $item['label'];
			}else{
				$item['label'] = $this->fetchRecordLabelForId($item['id'], $user);
			}
			
			$prev_assigned_user_id = $item['values']['assigned_user_id']['previous'];
			$current_assigned_user_id = $item['values']['assigned_user_id']['current'];
			$item['values']['assigned_user_id']['previous'] = $this->fetchRecordLabelForId('19x'.$prev_assigned_user_id, $user);
			$item['values']['assigned_user_id']['current'] = $this->fetchRecordLabelForId('19x'.$current_assigned_user_id, $user);
			if($item['status'] == 0) {
				foreach($item['values'] as $key => $value) {
					
					$moduleModel = Vtiger_Module_Model::getInstance($item['module']);
					$fieldModels = $moduleModel->getFields();
					$fieldModel = $fieldModels[$key];
					$refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
					$updatedRecord = '';
					$updatedRecordUser = $item['modifieduser']['label'] ." updated ";
					if($key!='' && $item['module']!=''){
						global $adb, $log;
						$id = getTabid($item['module']);
						$query = "select fieldlabel from vtiger_field where tabid = ? and fieldname = ? ";
						$result = $adb->pquery($query, array($id,$key));
						$fieldlabel = decode_html($adb->query_result($result,0,"fieldlabel"));
					}
					
					if($item['module'] == 'Events'){
						$key = vtranslate($fieldlabel, 'Calendar', $user->language);
					}else{
						$key = vtranslate($fieldlabel, $item['module'], $user->language);
					}
					
					if($value['previous'] != '' || $value['current'] != '') {
						if($value['previous'] == '') {
							
							$updatedRecord .= $key .'<b> Updated </b></br>';
							$value['current'] = html_entity_decode($value['current'], ENT_QUOTES, $default_charset);
							$updatedRecord .= 'To <b>'.$value['current'].'</b>';
						} else {
							if($key == 'Last Modified By'){
								$userRecordModel = Vtiger_Record_Model::getInstanceById($value['previous'],'Users');
								$previousName = $userRecordModel->get('first_name').' '.$userRecordModel->get('last_name');
								$userRecordModel = Vtiger_Record_Model::getInstanceById($value['current'],'Users');
								$currentName = $userRecordModel->get('first_name').' '.$userRecordModel->get('last_name');
								$updatedRecord .= $key .'<b> Changed </b> </br> From <b>'. decode_html($previousName) .'</b> To <b>'. decode_html($currentName).'</b>';
							}else{
								$dateUitypes = array('5','6','23','70');
								if($fieldModel){
									$uitype = $fieldModel->get('uitype');
								if(in_array($uitype,$dateUitypes)){
									if($value['current']){
										$value['current'] = $fieldModel->getDisplayValue($value['current']);
									}
									if($value['previous']){
										$value['previous'] = $fieldModel->getDisplayValue($value['previous']);
									}
								}else if($uitype == 56){
									if($value['previous'] == 1){
										 $value['previous']  = vtranslate('Yes',$user->language);
									}else{
										$value['previous']  = vtranslate('No',$user->language);
									}
									if($value['current'] == 1){
										 $value['current']  = vtranslate('Yes',$user->language);
									}else{
										$value['current']  = vtranslate('No',$user->language);
									}
								}else if($uitype == 72 || $uitype == 71){
									if($value['current']){
										$value['current'] = CurrencyField::convertToUserFormat($value['current']);
									}
									if($value['previous']){
										$value['previous'] = CurrencyField::convertToUserFormat($value['previous']);
									}
								}else if($uitype == 9){
									if($value['current']){ 
										$value['current'] = Vtiger_Percentage_UIType::getDisplayValue($value['current']);
									}
									if($value['previous']){ 
										$value['previous'] = Vtiger_Percentage_UIType::getDisplayValue($value['previous']);
									}
								}else if($uitype == 33){
									$current = explode('|##|',$value['current']);
									$value['current'] = '';
									foreach($current as $key => $c){
										if(count($current) == $key+1){
											$value['current'].= $c;
										}else{
											$value['current'].= $c.',';
										}
									}
									$previous = explode('|##|',$value['previous']);
									$value['previous'] = '';
									foreach($previous as $key => $p){
										if(count($previous) == $key+1){
											$value['previous'].= $p;
										}else{
											$value['previous'].= $p.',';
										}
									}
								}else if(in_array($uitype,$refrenceUitypes)){
									$previousResult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($value['previous']));
									$previous = $adb->query_result($previousResult,0,'label');
									$currentResult = $adb->pquery("SELECT label FROM vtiger_crmentity WHERE crmid = ?",array($value['current']));
									$current = $adb->query_result($currentResult,0,'label');
									$value['current'] = $current;
									$value['previous'] = $previous;
								}
								$value['current'] = html_entity_decode($value['current'], ENT_QUOTES, $default_charset);
								$value['previous'] = html_entity_decode($value['previous'], ENT_QUOTES, $default_charset);
								$updatedRecord .= $key .'<b> Changed </b> </br> From <b>'. $value['previous'] .'</b> To <b>'. $value['current'].'</b>';
							}
						}
						}
						
						$item['updateRecord']['modified_user_label'] = $updatedRecordUser;
						$item['updateRecord']['label'][]= $updatedRecord;
					} 
				}
			}			
			
			unset($item);
		}
		 
	}
	
	protected function fetchResolvedValueForId($id, $user) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$label = $this->fetchRecordLabelForId($id, $user);
		$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
		return array('value' => $id, 'label'=>$label);
	}
	

	function vtws_history($element, $user) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$adb = PearDatabase::getInstance();

		// Mandatory input validation
		if (empty($element['module']) && empty($element['record'])) {
			$Message = vtranslate('Missing mandatory input values.','Webapp');
			throw new WebServiceException(419, $Message);
		}

		if (!CRMEntity::getInstance('ModTracker') || !vtlib_isModuleActive('ModTracker')) {
			$Message = vtranslate('Tracking module not active.','Webapp');
			throw new WebServiceException(422, $Message);
		}

		$idComponents = NULL;

		$moduleName = $element['module'];
		$record = $element['record'];
		$mode = empty($element['mode'])? 'Private' : $element['mode']; // Private or All
		$page = empty($element['page'])? 0 : intval($element['page']); // Page to start

		$acrossAllModule = false;
		if ($moduleName == 'Home') $acrossAllModule = true;

		// Pre-condition check
		if (empty($moduleName)) {
			$idComponents = vtws_getIdComponents($record); // We have it - as the input is validated.
		}
		
		
		// Per-condition has been met, perform the operation
		$sql = '';
		$params = array();

		// REFER: modules/ModTracker/ModTracker.php

		// Two split phases for data extraction - so we can apply limit of retrieveal at record level.
		$sql = 'SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
			INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid';
		
		if ($mode == 'Private') {
			$sql .= ' WHERE vtiger_modtracker_basic.whodid = ?';
			$params[] = $user->id;
		} else if ($mode == 'All') {
			if ($acrossAllModule) {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if(!$currentUser->isAdminUser()) {
						$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
						$sql .= ' AND whodid IN ('.  generateQuestionMarks($accessibleUsers).')';
						$params = array_merge($params, $accessibleUsers);
				}
				// TODO collate only active (or enabled) modules for tracking.
			} else if($moduleName) {
				$sql .= ' WHERE vtiger_modtracker_basic.module = ?';
				$params[] = $moduleName;
			} else {
				$sql .= ' WHERE vtiger_modtracker_basic.crmid = ?';
				$params[] = $idComponents[1];
			}
		}
			
		// Get most recently tracked changes with limit
		$index = $element['index'];
		$size = $element['size'];
		$start = ($index*$size) - $size;
		if(!empty($moduleName)) {
			$sql .= sprintf(' ORDER BY vtiger_modtracker_basic.id DESC LIMIT %s,%s', $start, $size);
		}

		$result = $adb->pquery($sql, $params);

		$recordValuesMap = array();
		$orderedIds = array();

		while ($row = $adb->fetch_array($result)) {
			
			if($row['module'] == 'ModComments'){
				$modules = $row['setype'];
				$recordid = $row['related_to'];
			}else{
				$modules = $row['module'];
				$recordid = $row['id'];
			}
			
			if($modules == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $modules;
			}
		
			$orderedIds[] = $row['id'];
			
			$whodid = $this->vtws_history_entityIdHelper('Users', $row['whodid']);
			$crmid = $this->vtws_history_entityIdHelper($acrossAllModule? '' : $moduleName, $row['crmid']);
			$status = $row['status'];
			$statuslabel = '';
			switch ($status) {
				case ModTracker::$UPDATED: $statuslabel = 'updated'; break;
				case ModTracker::$DELETED: $statuslabel = 'deleted'; break;
				case ModTracker::$CREATED: $statuslabel = 'created'; break;
				case ModTracker::$RESTORED: $statuslabel = 'restored'; break;
				case ModTracker::$LINK: $statuslabel = 'link'; break;
				case ModTracker::$UNLINK: $statuslabel = 'unlink'; break;
			}
			$item['modifieduser'] = $whodid;
			$item['id'] = $crmid;
			$item['modifiedtime'] = $row['changedon'];
			$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['changedon']);
			$item['status'] = $status;
			$item['statuslabel'] = $statuslabel;
			$item['module'] = $row['module'];
			if($status == 1 && $statuslabel == 'deleted'){
				$getModTrackerRelQuery = $adb->pquery("SELECT vtiger_modtracker_basic . * 
					FROM vtiger_modtracker_basic
					INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid where id = ?", array($row['id']));
				$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'crmid');
				
				if($targetid) {
					$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? ", array($targetid));
					$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
					$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
					$new_label = 'deleted '.$label;
				}
			}
			if($status == 4){
				$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
				$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
				if($targetid) {
					$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
					$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
					$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
					$item['entitydata'] = $setype." added ".$label;
					
					$new_label = '';
					$new_label = 'Commented On';
					$new_label.= '</br>';
					$new_label.= ' label </br>'.'"'.$label.'"';	
				}
			}
			if($status == 2 && $statuslabel == 'created' && $row['module'] =='ModComments'){
				$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_detail where id = ? AND fieldname = 'related_to'", array($row['id']));
				$parent_id = $adb->query_result($getModTrackerRelQuery, 0, 'postvalue');
				$query = $adb->pquery("SELECT * FROM vtiger_crmentity where crmid = ? and deleted = 0",array($parent_id));
				$label = $adb->query_result($query, 0, 'label');
				$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
				$new_label = '';
				$new_label = 'added';
				$new_label.= ' "label" for </br>'.$label;
				
			}else if($status == 2 && $statuslabel == 'created'){
				$new_label = '';
				$new_label = 'added';
				$new_label.= ' label ';
			}
			
			
			if($status == 5){
				$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
				$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
				if($targetid) {
					$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
					$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
					$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
					$item['entitydata'] = $setype." removed ".$label;
				}
			}

			$item['values'] = array();
			$item['label'] = $new_label;
			$recordValuesMap[$row['id']] = $item;
		}
		$historyItems = array();

		// Minor optimizatin to avoid 2nd query run when there is nothing to expect.
		if (!empty($orderedIds)) {
			$sql = 'SELECT vtiger_modtracker_detail.* FROM vtiger_modtracker_detail';
			$sql .= ' WHERE vtiger_modtracker_detail.id IN (' . generateQuestionMarks($orderedIds) . ')';

			// LIMIT here is not required as $ids extracted is with limit at record level earlier.
			$params = $orderedIds;

			$result = $adb->pquery($sql, $params);
			while ($row = $adb->fetch_array($result)) {
				$item = $recordValuesMap[$row['id']];
				
				// NOTE: For reference field values transform them to webservice id.
				$item['values'][$row['fieldname']] = array(
					'previous' => $row['prevalue'],
					'current'  => $row['postvalue']
				);
				if($row['fieldname'] == 'ModifiedTime' && $item['modifiedtime'] == null){
					$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['postvalue']);
				}
					
				$recordValuesMap[$row['id']] = $item;
			}
			
			// Group the values per basic-transaction
			foreach ($orderedIds as $id) {
				$historyItems[] = $recordValuesMap[$id];
			}
		}
		
        
		return $historyItems;
	}
	
	// vtws_getWebserviceEntityId - seem to be missing the optimization
	// which could pose performance challenge while gathering the changes made
	// this helper function targets to cache and optimize the transformed values.
	function vtws_history_entityIdHelper($moduleName, $id) {
		static $wsEntityIdCache = NULL;
		if ($wsEntityIdCache === NULL) {
			$wsEntityIdCache = array('users' => array(), 'records' => array());
		}

		if (!isset($wsEntityIdCache[$moduleName][$id])) {
			// Determine moduleName based on $id
			if (empty($moduleName)) {
				$moduleName = getSalesEntityType($id);
			}
			if($moduleName == 'Calendar') {
				$moduleName = vtws_getCalendarEntityType($id);
			}

			$wsEntityIdCache[$moduleName][$id] = vtws_getWebserviceEntityId($moduleName, $id);
		}
		return $wsEntityIdCache[$moduleName][$id];
	}
	
	public function getComments($pagingModel, $user, $dateFilter='') {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$adb = PearDatabase::getInstance();
		if (!CRMEntity::getInstance('ModTracker') || !vtlib_isModuleActive('ModTracker')) {
			$Message = vtranslate('Tracking module not active.','Webapp');
			throw new WebServiceException(422, $Message);
		}
		$sql = 'SELECT vtiger_modtracker_basic.*,vtiger_modcomments.*,vtiger_crmentity.setype AS setype,vtiger_crmentity.createdtime AS createdtime, vtiger_crmentity.smownerid AS smownerid,
				crmentity2.crmid AS parentId, crmentity2.setype AS parentModule FROM vtiger_modcomments
				INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
				AND vtiger_crmentity.deleted = 0
				INNER JOIN vtiger_crmentity crmentity2 ON vtiger_modcomments.related_to = crmentity2.crmid
				AND crmentity2.deleted = 0 
				INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid';

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$params = array();

		if($user === 'all') {
			if(!$currentUser->isAdminUser()){
				$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
				$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');
				$sql .= $nonAdminAccessQuery;
				$sql .= ' AND userid IN('.  generateQuestionMarks($accessibleUsers).')';
				$params = array_merge($params,$accessibleUsers);
			}
		}else{
			$sql .= ' AND userid = ?';
			$params[] = $user;
		}
		//handling date filter for history widget in home page
		if(!empty($dateFilter)) {
			$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}

		$sql .= ' ORDER BY vtiger_crmentity.crmid DESC LIMIT ?, ?';
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit();
		$result = $adb->pquery($sql,$params);
		
		$recordValuesMap = array();
		$orderedIds = array();

		while ($row = $adb->fetch_array($result)) {
			if($row['setype'] == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $row['setype'];
			}
			if(Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $row['related_to'])){
				$orderedIds[] = $row['id'];
				$whodid = $this->vtws_history_entityIdHelper('Users', $row['whodid']);
				$crmid = $this->vtws_history_entityIdHelper($acrossAllModule? '' : $moduleName, $row['crmid']);
				$status = $row['status'];
				$statuslabel = '';
				switch ($status) {
					case ModTracker::$UPDATED: $statuslabel = 'updated'; break;
					case ModTracker::$DELETED: $statuslabel = 'deleted'; break;
					case ModTracker::$CREATED: $statuslabel = 'created'; break;
					case ModTracker::$RESTORED: $statuslabel = 'restored'; break;
					case ModTracker::$LINK: $statuslabel = 'link'; break;
					case ModTracker::$UNLINK: $statuslabel = 'unlink'; break;
				}
				$item['modifieduser'] = $whodid;
				$item['id'] = $crmid;
				$item['modifiedtime'] = $row['changedon'];
				$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['changedon']);
				$item['status'] = $status;
				$item['statuslabel'] = $statuslabel;
				$item['module'] = $row['module'];
				if($status == 1 && $statuslabel == 'deleted'){
					$getModTrackerRelQuery = $adb->pquery("SELECT vtiger_modtracker_basic . * 
						FROM vtiger_modtracker_basic
						INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'crmid');
					
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? ", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);

						$new_label = 'deleted '.$label;
					}
				}
				if($status == 4){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						$item['entitydata'] = $setype." added ".$label;
						
						$new_label = '';
						$new_label = 'Commented On';
						$new_label.= '</br>';
						$new_label.= ' label </br>'.'"'.$label.'"';	
					}
				}
				if($status == 2 && $statuslabel == 'created' && $row['module'] =='ModComments'){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_detail where id = ? AND fieldname = 'related_to'", array($row['id']));
					$parent_id = $adb->query_result($getModTrackerRelQuery, 0, 'postvalue');
					$query = $adb->pquery("SELECT * FROM vtiger_crmentity where crmid = ? and deleted = 0",array($parent_id));
					$label = $adb->query_result($query, 0, 'label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
					$new_label = '';
					$new_label = 'added';
					$new_label.= ' "label" for </br>'.$label;
					
				}else if($status == 2 && $statuslabel == 'created'){
					$new_label = '';
					$new_label = 'added';
					$new_label.= ' label ';
				}
				
				
				if($status == 5){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						$item['entitydata'] = $setype." removed ".$label;
					}
				}

				$item['values'] = array();
				$item['label'] = $new_label;
				$recordValuesMap[$row['id']] = $item;
			}
		}
		$historyItems = array();

		// Minor optimizatin to avoid 2nd query run when there is nothing to expect.
		if (!empty($orderedIds)) {
			$sql = 'SELECT vtiger_modtracker_detail.* FROM vtiger_modtracker_detail';
			$sql .= ' WHERE vtiger_modtracker_detail.id IN (' . generateQuestionMarks($orderedIds) . ')';

			// LIMIT here is not required as $ids extracted is with limit at record level earlier.
			$params = $orderedIds;

			$result = $adb->pquery($sql, $params);
			while ($row = $adb->fetch_array($result)) {
				$item = $recordValuesMap[$row['id']];
				
				// NOTE: For reference field values transform them to webservice id.
				$item['values'][$row['fieldname']] = array(
					'previous' => $row['prevalue'],
					'current'  => $row['postvalue']
				);
				if($row['fieldname'] == 'ModifiedTime' && $item['modifiedtime'] == null){
					$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['postvalue']);
				}
					
				$recordValuesMap[$row['id']] = $item;
			}
			
			// Group the values per basic-transaction
			foreach ($orderedIds as $id) {
				$historyItems[] = $recordValuesMap[$id];
			}
		}
		
        
		return $historyItems;
	}

	/**
	 * Function returns comments and recent activities across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type='', $userId='', $dateFilter='') {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		if(!$userId)	$userId	= 'all';
		if(!$type)		$type	= 'all';
		//TODO: need to handle security
		$comments = array();
		if($type == 'all' || $type == 'comments') {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments'); 
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel, $userId, $dateFilter);
			}
			if($type == 'comments') {
				return $comments;
			}
		}
		
		$adb = PearDatabase::getInstance();
		$params = array();
		$sql = 'SELECT vtiger_modtracker_basic.*
				FROM vtiger_modtracker_basic
				INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
				AND module NOT IN ("ModComments","Users") ';

		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($userId === 'all') {
			if(!$currentUser->isAdminUser()) {
				$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
				$sql .= ' AND whodid IN ('.  generateQuestionMarks($accessibleUsers).')';
				$params = array_merge($params, $accessibleUsers);
			}
		}else{
			$sql .= ' AND whodid = ?';
			$params[] = $userId;
		}
		//handling date filter for history widget in home page
		if(!empty($dateFilter)) {
			$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$sql .= ' ORDER BY vtiger_modtracker_basic.id DESC LIMIT ?, ?';
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit();
                
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
		$result = $adb->pquery($sql,$params);
                
		$recordValuesMap = array();
		$orderedIds = array();

		while ($row = $adb->fetch_array($result)) {
			if($row['module'] == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $row['module'];
			}
			if(Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $row['id'])){
				$orderedIds[] = $row['id'];
				$whodid = $this->vtws_history_entityIdHelper('Users', $row['whodid']);
				$crmid = $this->vtws_history_entityIdHelper($acrossAllModule? '' : $moduleName, $row['crmid']);
				$status = $row['status'];
				$statuslabel = '';
				switch ($status) {
					case ModTracker::$UPDATED: $statuslabel = 'updated'; break;
					case ModTracker::$DELETED: $statuslabel = 'deleted'; break;
					case ModTracker::$CREATED: $statuslabel = 'created'; break;
					case ModTracker::$RESTORED: $statuslabel = 'restored'; break;
					case ModTracker::$LINK: $statuslabel = 'link'; break;
					case ModTracker::$UNLINK: $statuslabel = 'unlink'; break;
				}
				$item['modifieduser'] = $whodid;
				$item['id'] = $crmid;
				$item['modifiedtime'] = $row['changedon'];
				$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['changedon']);
				$item['status'] = $status;
				$item['statuslabel'] = $statuslabel;
				$item['module'] = $row['module'];
				if($status == 1 && $statuslabel == 'deleted'){
					$getModTrackerRelQuery = $adb->pquery("SELECT vtiger_modtracker_basic . * 
						FROM vtiger_modtracker_basic
						INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'crmid');
					
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? ", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						$new_label = 'deleted '.$label;
					}
				}
				if($status == 4){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						$item['entitydata'] = $setype." added ".$label;
						
						$new_label = '';
						$new_label = 'Commented On';
						$new_label.= '</br>';
						$new_label.= ' label </br>'.'"'.$label.'"';	
					}
				}
				if($status == 2 && $statuslabel == 'created' && $row['module'] =='ModComments'){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_detail where id = ? AND fieldname = 'related_to'", array($row['id']));
					$parent_id = $adb->query_result($getModTrackerRelQuery, 0, 'postvalue');
					$query = $adb->pquery("SELECT * FROM vtiger_crmentity where crmid = ? and deleted = 0",array($parent_id));
					$label = $adb->query_result($query, 0, 'label');
					$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
					$new_label = '';
					$new_label = 'added';
					$new_label.= ' "label" for </br>'.$label;
					
				}else if($status == 2 && $statuslabel == 'created'){
					$new_label = '';
					$new_label = 'added';
					$new_label.= ' label ';
				}
				
				
				if($status == 5){
					$getModTrackerRelQuery = $adb->pquery("SELECT * FROM vtiger_modtracker_relations where id = ?", array($row['id']));
					$targetid = $adb->query_result($getModTrackerRelQuery, 0, 'targetid');
					if($targetid) {
						$getCRMEntityQuery = $adb->pquery("SELECT setype, label FROM vtiger_crmentity where crmid = ? and deleted = 0", array($targetid));
						$setype = $adb->query_result($getCRMEntityQuery, 0, 'setype');
						$label = $adb->query_result($getCRMEntityQuery, 0, 'label');
						$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
						$item['entitydata'] = $setype." removed ".$label;
					}
				}

				$item['values'] = array();
				$item['label'] = $new_label;
				$recordValuesMap[$row['id']] = $item;
			}
		}
		$activites = array();

		// Minor optimizatin to avoid 2nd query run when there is nothing to expect.
		if (!empty($orderedIds)) {
			$sql = 'SELECT vtiger_modtracker_detail.* FROM vtiger_modtracker_detail';
			$sql .= ' WHERE vtiger_modtracker_detail.id IN (' . generateQuestionMarks($orderedIds) . ')';

			// LIMIT here is not required as $ids extracted is with limit at record level earlier.
			$params = $orderedIds;

			$result = $adb->pquery($sql, $params);
			while ($row = $adb->fetch_array($result)) {
				$item = $recordValuesMap[$row['id']];
				
				// NOTE: For reference field values transform them to webservice id.
				$item['values'][$row['fieldname']] = array(
					'previous' => $row['prevalue'],
					'current'  => $row['postvalue']
				);
				if($row['fieldname'] == 'ModifiedTime' && $item['modifiedtime'] == null){
					$item['ModifiedTime'] = Vtiger_Util_Helper::formatDateDiffInStrings($row['postvalue']);
				}
					
				$recordValuesMap[$row['id']] = $item;
			}
			
			// Group the values per basic-transaction
			foreach ($orderedIds as $id) {
				$activites[] = $recordValuesMap[$id];
			}
		}
		
		$historyItems = array_merge($activites, $comments);
		return $historyItems;
	}

}
