<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
include_once dirname(__FILE__) . '/FetchRecord.php';

class Webapp_WS_AddWidgets extends Webapp_WS_FetchRecord {
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
  		$reportid = trim($request->get('reportid'));
  		$widgetid = trim($request->get('widgetid'));
  		if(!empty($reportid)){
  			$reportModel = Reports_Record_Model::getInstanceById($reportid);
			$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
	        $primaryModule = $reportModel->getPrimaryModule();
	        $moduleModel = Vtiger_Module_Model::getInstance($primaryModule);
	        $widgetTitle = 'ChartReportWidget_'.$primaryModule.'_'.$reportid;
	        $currentuserid = $current_user->id;
	        $dashBoardModel = Vtiger_DashBoard_Model::getInstance('Home');
	        $dashboardTabs = $dashBoardModel->getActiveTabs();
			$tabid = $dashboardTabs[0]["id"];
	        $dashBoardTabId = $tabid;

	        $query = "SELECT 1 FROM vtiger_module_dashboard_widgets WHERE reportid = ? AND userid = ?";
       		$param = array($reportid,$currentuserid);
        	$result = $adb->pquery($query, $param);
        	$numOfRows = $adb->num_rows($result);
        	if($numOfRows > 0){
        	}else{
		        $query2 = "INSERT INTO vtiger_module_dashboard_widgets (userid,reportid,linkid,title,dashboardtabid) VALUES (?,?,?,?,?)";
	        	$param2 = array($currentuserid,$reportid,0,$widgetTitle,$dashBoardTabId);
	       	    $result2 = $adb->pquery($query2, $param2);
        	}

	        if(!$moduleModel->isPermitted('DetailView')){
	        	$MESSAGE = $primaryModule.' '.vtranslate('LBL_NOT_ACCESSIBLE');
				$Message = vtranslate($MESSAGE);
				throw new WebServiceException(403, $Message);
	        }
	        $secondaryModules = $reportModel->getSecondaryModules();
			if(empty($secondaryModules)) {

			}
			$ChartType = $reportChartModel->getChartType();
			$data = array();
			$data1 = $reportChartModel->getData();
			if($ChartType == 'pieChart'){
				$totalRow = count($data1['labels']);
				for($i=0;$i<$totalRow;$i++) {
					$links = $this->getReportChartLinks($data1['links'][$i]);
					$data[] = array('x'=>html_entity_decode($data1['labels'][$i], ENT_QUOTES, $default_charset),'y'=>$data1['values'][$i],'links'=>$links);
				}
			}
			if($ChartType == 'verticalbarChart'){
				$totalRow = count($data1['labels']);
				for($i=0;$i<$totalRow;$i++) {
					$links = $this->getReportChartLinks($data1['links'][$i]);
					$data[] = array('x'=>html_entity_decode($data1['labels'][$i], ENT_QUOTES, $default_charset),'y'=>$data1['values'][$i][0],'links'=>$links);
				}
			}
			if($ChartType == 'horizontalbarChart'){
				$totalRow = count($data1['labels']);
				for($i=0;$i<$totalRow;$i++) {
					$links = $this->getReportChartLinks($data1['links'][$i]);
					$data[] = array('x'=>html_entity_decode($data1['labels'][$i], ENT_QUOTES, $default_charset),'y'=>$data1['values'][$i][0],'links'=>$links);
				}
			}
			if($ChartType == 'lineChart'){
				$totalRow = count($data1['labels']);
				for($i=0;$i<$totalRow;$i++) {
					$links = $this->getReportChartLinks($data1['links'][$i]);
					$data[] = array('x'=>html_entity_decode($data1['labels'][$i], ENT_QUOTES, $default_charset),'y'=>$data1['values'][$i][0],'links'=>$links);
				}
			}
			$name = $data1['graph_label'];
			$label = vtranslate($data1['graph_label'],'Vtiger');
  		}else{
  			if($widgetid != ''){
	  			$query = "SELECT * FROM `vtiger_links` WHERE `linktype` LIKE 'DASHBOARDWIDGET' AND linkid = ?";
	  			$results = $adb->pquery($query,array($widgetid));
	  			if($adb->num_rows($results) != 0){
	  				$widgetname = $adb->query_result($results,0,'linklabel');

	  				$linkurl = $adb->query_result($results,0,'linkurl');
	  				$linkurl = html_entity_decode($linkurl);
	  				$url_components = parse_url($linkurl); 
					parse_str($url_components['query'], $params);
				  	$componentName = $params['name'];
				  	
					$linkId = $widgetid;
					
					$critearia = array();
	  				if($widgetname == 'History'){
	  					$ChartType = '';
	  					$name = $widgetname;
	  					$className = 'Vtiger_History_Dashboard';
	  					$label = vtranslate('History','Vtiger');
	  					$index = 1;
						$size = 10;
						$pagingModel = new Vtiger_Paging_Model();
						$pagingModel->set('page', $index);
						$pagingModel->set('limit',intval($size));

						$historyItems = $this->getHistory($pagingModel,'','','');
						$this->resolveReferences($historyItems, $current_user, $module);

						foreach ($historyItems as $key => $part) {
							$sort[$key] = strtotime($part['modifiedtime']);
						}
						array_multisort($sort, SORT_DESC, $historyItems);
				  		$count = 0;
				  		foreach ($historyItems as $key => $part) {
							$count++;
							if($count>$size){
								unset($historyItems[$key]);
							}
						}
	  					$data = $historyItems;
	  				}
	  				if($widgetname == 'Upcoming Activities'){
	  					$ChartType = '';
	  					$name = $widgetname;
	  					$className = 'Vtiger_CalendarActivities_Dashboard';
	  					$label = vtranslate('Upcoming Activities','Vtiger');
	  					$recentEventData = $this->recentEvent();
	  					$data = $recentEventData;
	  				}
	  				if($widgetname == 'Key Metrics'){
	  					$ChartType = '';
	  					$name = $widgetname;
	  					$className = 'Vtiger_KeyMetrics_Dashboard';
	  					$label = vtranslate('Key Metrics','Vtiger');
	  					$metriclists = $this->keyMetrics();
	  					$data = $metriclists;
	  				}
	  				if($widgetname == 'Potentials by Stage'){
	  					$ChartType = 'multiBarChart';
	  					$moduleName = 'Potentials';
	  					$name = $widgetname;
	  					$className = 'Potentials_GroupedBySalesPerson_Dashboard';
	  					$label = vtranslate('Potentials by Stage',$moduleName);
	  					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
						$data = $moduleModel->getPotentialsCountBySalesPerson();
						$listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
						for($i = 0;$i<count($data);$i++){
				            $data[$i]["links"] = $this->getPotentialsCountBySalesPersonParams($listViewUrl,$data[$i]["last_name"],$data[$i]["link"]);
				        }
						$x = array_unique(array_column($data, 'last_name'));
						$critearia = array_values(array_unique(array_column($data, 'sales_stage')));
						$count = 0;
						$newData = array();
						foreach ($x as $ky => $xvalue) {
				        	foreach ($data as $keys => $values) {
				        		if($values['last_name'] == $xvalue){
				        			$newData[$count]['x'] = html_entity_decode($xvalue, ENT_QUOTES, $default_charset);
				        			$newData[$count]['y'][] = $values['count'];
				        			$newData[$count]['z'][] = html_entity_decode($values['sales_stage'], ENT_QUOTES, $default_charset);
				        			$newData[$count]['links'][] = $values['links'];
				        		}
							}
							foreach($critearia as $keys => $values){
								if(!in_array($values, $newData[$count]['z'])){
									$newData[$count]['y'][] = "0";
									$newData[$count]['z'][] = $values;
								}
							}
							$count++;
						}
						$data = $newData;
	  				}else if($widgetname == 'Pipelined Amount'){
	  					$ChartType = 'multiBarChart';
	  					$moduleName = 'Potentials';
	  					$name = $widgetname;
	  					$className = 'Potentials_PipelinedAmountPerSalesPerson_Dashboard';
	  					$label = vtranslate('Pipelined Amount',$moduleName);
	  					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
						$data = $moduleModel->getPotentialsPipelinedAmountPerSalesPerson();
						$listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
				        for($i = 0;$i<count($data);$i++){
				            $data[$i]["links"] = $this->getPotentialsPipelinedAmountPerSalesPersonParams($listViewUrl,$data[$i]["last_name"],$data[$i]["link"]);
				        }
				        $x = array_unique(array_column($data, 'last_name'));
				        $critearia = array_values(array_unique(array_column($data, 'sales_stage')));
						$count = 0;
						$newData = array();
						foreach ($x as $ky => $xvalue) {
				        	foreach ($data as $keys => $values) {
				        		if($values['last_name'] == $xvalue){
				        			$newData[$count]['x'] = html_entity_decode($xvalue, ENT_QUOTES, $default_charset);
				        			$newData[$count]['y'][] = rtrim(sprintf('%f',floatval($values['amount'])));
				        			$newData[$count]['z'][] = html_entity_decode($values['sales_stage'], ENT_QUOTES, $default_charset);
				        			$newData[$count]['links'][] = $values['links'];
				        		}
							}
							foreach($critearia as $keys => $values){
								if(!in_array($values, $newData[$count]['z'])){
									$newData[$count]['y'][] = "0";
									$newData[$count]['z'][] = $values;
								}
							}
							$count++;
						}
						$data = $newData;
	  				}else if($widgetname == 'Funnel Amount'){
	  					$ChartType = 'funnelChart';
	  					$moduleName = 'Potentials';
	  					$name = $widgetname;
	  					$owner = $current_user->id;
	  					$className = 'Potentials_GroupedBySalesStage_Dashboard';
	  					$label = vtranslate('Funnel Amount',$moduleName);
	  					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
						$data = $moduleModel->getPotentialsCountBySalesStage($owner, $dates);
						$listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
				        for($i = 0;$i<count($data);$i++){
				            $data[$i]["links"] = $this->getPotentialTotalAmountBySalesStage($listViewUrl,$data[$i]['link']);
				        }
				        foreach ($data as $keys => $values) {
							foreach ($values as $key => $value) {
								if($key == '0'){
									$data[$keys]['x'] = html_entity_decode($value, ENT_QUOTES, $default_charset);
									unset($data[$keys][$key]);
								}else if($key == '1'){
									$data[$keys]['y'] = $value;
									unset($data[$keys][$key]);
								}
							}
							unset($data[$keys]['2']);
							unset($data[$keys]['link']);
						}
	  				}else if($widgetname == 'Open Tickets'){
	  					$ChartType = "pieChart";
	  					$moduleName = 'HelpDesk';
	  					$name = $widgetname;
	  					$className = 'HelpDesk_OpenTickets_Dashboard';
	  					$label = vtranslate('Open Tickets',$moduleName);
	  					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
						$data = $moduleModel->getOpenTickets();
						 $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
				         for($i = 0;$i<count($data);$i++){
				            $data[$i]["links"] = $this->getSearchParams($listViewUrl,$data[$i]['id']);
				         }
						foreach ($data as $keys => $values) {
							foreach ($values as $key => $value) {
								if($key == '0' || $key == '1' || $key == '2'){
									unset($data[$keys][$key]);	
								}else if($key == 'name'){
									$data[$keys]['x'] = html_entity_decode($value, ENT_QUOTES, $default_charset);
									unset($data[$keys][$key]);
								}else if($key == 'count'){
									$data[$keys]['y'] = $value;
									unset($data[$keys][$key]);
								}
							}
							unset($data[$keys]['id']);
						}
	  				}
	  				$moduleName = 'Home';
	  				$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
	  				//check profile permissions for Dashboards
					$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
					$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
					$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
						// TODO : Need to optimize the widget which are retrieving twice
				    $dashboardTabs = $dashBoardModel->getActiveTabs();
				    $tabid = $dashboardTabs[0]["id"];
				    $dashBoardModel->set("tabid",$tabid);
	  				$widgets1 = $dashBoardModel->getDashboards($moduleName);
	  				$alreadyAddedWidget =  array();
	  				foreach($widgets1 as $key => $widgetss){
	  					$alreadyAddedWidget[] = $widgetss->get('linklabel');
	  				}
	  				if(!in_array($widgetname, $alreadyAddedWidget)){
		  				if(!empty($componentName)) {
							//$className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
							if(!empty($className)) {
								$widget = NULL;
								if(!empty($linkId)) {
									$widget = new Vtiger_Widget_Model();
									$widget->set('linkid', $linkId);
									$currentuserid = $current_user->id;
									$widget->set('userid', $currentuserid);
									$widget->set('filterid', $request->get('filterid', NULL));
				                    
				                    // In Vtiger7, we need to pin this report widget to first tab of that user

				                    $dasbBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
				                    $defaultTab = $dasbBoardModel->getUserDefaultTab($currentuserid);
				                    $widget->set('tabid',$tabid);
				                    
									if ($request->has('data')) {
										$widget->set('data', $request->get('data'));
									}
									$widget->add();
								}
								
							}
						}
	  				}

	  			}
  			}
  		}
  		if($reportid != ''){
			$response = new Webapp_API_Response();
			$response->setResult(array('reportid'=>$reportid,'label'=>$label,'chartType'=>$ChartType,'data'=>$data));

  		}else{
  			$response = new Webapp_API_Response();
			$response->setResult(array('widgetid'=>$widgetid,'name'=>$name,'label'=>$label,'chartType'=>$ChartType,'critearia'=>$critearia,'data'=>$data));
  		}
		return $response;
	}

	function getReportChartLinks($links){
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$url_components = parse_url($links); 
		parse_str($url_components['query'], $params);
		$module = $params['module'];
		$cvid = $params['viewname'];
		return array('module'=>$module,'cvid'=>$cvid,'search_params'=>$params['search_params']);
	}

	function getPotentialTotalAmountBySalesStage($listViewUrl,$stage) {
        $url_components = parse_url($listViewUrl); 
		parse_str($url_components['query'], $params);
		$module = $params['module'];
		$cvid = $params['viewname'];
        $field_name1 = 'sales_stage';
        $field_value1 = $stage;
        return array('module'=>$module,'cvid'=>$cvid,'conditions'=>array('field_name1'=>$field_name1,'field_value1'=>$field_value1));
    }

	function getSearchParams($listViewUrl,$value) {
		$url_components = parse_url($listViewUrl); 
		parse_str($url_components['query'], $params);
		$module = $params['module'];
		$cvid = $params['viewname'];
        $field_name1 = 'ticketstatus';
        $field_value1 = 'Open';
        $field_name2 = 'assigned_user_id';
        $field_value2 = Webapp_WS_Utils::getEntityModuleWSId('Users').'x'.$value;
        return array('module'=>$module,'cvid'=>$cvid,'conditions'=>array('field_name1'=>$field_name1,'field_value1'=>$field_value1,'field_name2'=>$field_name2,'field_value2'=>$field_value2));
    }

	function getPotentialsPipelinedAmountPerSalesPersonParams($listViewUrl,$assignedto,$stage) {
		$url_components = parse_url($listViewUrl); 
		parse_str($url_components['query'], $params);
		$module = $params['module'];
		$cvid = $params['viewname'];
        $field_name1 = 'sales_stage';
        $field_value1 = $stage;
        $field_name2 = 'assigned_user_id';
        $AllUsers = $this->getAll(true);
        $assignedto = array_search($assignedto, $AllUsers);
        $field_value2 = Webapp_WS_Utils::getEntityModuleWSId('Users').'x'.$assignedto;
        return array('module'=>$module,'cvid'=>$cvid,'conditions'=>array('field_name1'=>$field_name1,'field_value1'=>$field_value1,'field_name2'=>$field_name2,'field_value2'=>$field_value2));
    }

     function getPotentialsCountBySalesPersonParams($listViewUrl,$assignedto,$stage) {
      	$url_components = parse_url($listViewUrl); 
		parse_str($url_components['query'], $params);
		$module = $params['module'];
		$cvid = $params['viewname'];
		$field_name1 = 'sales_stage';
        $field_value1 = $stage;
        $field_name2 = 'assigned_user_id';
        $AllUsers = $this->getAll(true);
        $assignedto = array_search($assignedto, $AllUsers);
        $field_value2 = Webapp_WS_Utils::getEntityModuleWSId('Users').'x'.$assignedto;
        return array('module'=>$module,'cvid'=>$cvid,'conditions'=>array('field_name1'=>$field_name1,'field_value1'=>$field_value1,'field_name2'=>$field_name2,'field_value2'=>$field_value2));
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
			
			unset($item['values']);
			unset($item);
		}
		 
	}
	
	protected function fetchResolvedValueForId($id, $user) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$label = $this->fetchRecordLabelForId($id, $user);
		$label = html_entity_decode($label, ENT_QUOTES, $default_charset);
		return array('value' => $id, 'label'=>$label);
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

	function keyMetrics(){
		global $current_user, $adb;
		$current_user = Users_Record_Model::getCurrentUserModel();
		
		require_once 'modules/CustomView/ListViewTop.php';
		$metriclists = getMetricList();
		
		foreach ($metriclists as $key => $metriclist) {
			$metricresult = NULL;
			$queryGenerator = new EnhancedQueryGenerator($metriclist['module'], $current_user);
			$queryGenerator->initForCustomViewById($metriclist['id']);
            if($metriclist['module'] == "Calendar") {
                // For calendar we need to eliminate emails or else it will break in status empty condition
                $queryGenerator->addCondition('activitytype', "Emails", 'n',  QueryGenerator::$AND);
			}
			$metricsql = $queryGenerator->getQuery();
			$metricresult = $adb->query(Vtiger_Functions::mkCountQuery($metricsql));
			if($metricresult) {
				$rowcount = $adb->fetch_array($metricresult);
				$metriclists[$key]['count'] = $rowcount['count'];
			}
			$metriclists[$key]['cvid'] = $metriclists[$key]['id'];
			unset($metriclists[$key]['id']);
		}
		return $metriclists;
	}

	function recentEvent(){
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $current_user,$adb, $site_URL;
		$current_user = $this->getActiveUser();
		$userid = $current_user->id;
		$index = 1;
		$size = 5;
		//$record = trim($request->get('record'));
		//$module = trim($request->get('module'));
		
		$limit = ($index*$size) - $size;
		$recentEvent_data = array();
		$generator = new QueryGenerator('Events', $current_user);
		$generator->setFields(array('subject','activitytype','location','date_start','time_start','location','createdtime','modifiedtime','id'));
		$eventQuery = $generator->getQuery();
		
		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);
		
		if($index == '' || $size == '') {
			$eventQuery .= " AND vtiger_crmentity.setype = 'Calendar' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND CONCAT(due_date,' ',time_end) >= '".$nowInDBFormat."'
					ORDER BY vtiger_activity.date_start, time_start DESC ";
		} else {
			$eventQuery .= " AND vtiger_crmentity.setype = 'Calendar' AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Cancelled')) AND CONCAT(due_date,' ',time_end) >= '".$nowInDBFormat."'
					ORDER BY vtiger_activity.date_start, time_start DESC limit ".$limit.",".$size;
		}

		$query = $adb->pquery($eventQuery);
		
		
		for($i=0; $i<$adb->num_rows($query); $i++) {
			$activityid = $adb->query_result($query, $i, 'activityid');
			$eventSubject = $adb->query_result($query, $i, 'subject');
			$eventSubject = html_entity_decode($eventSubject, ENT_QUOTES, $default_charset);
			$eventtype = $adb->query_result($query, $i, 'activitytype');
			$eventtype = html_entity_decode($eventtype, ENT_QUOTES, $default_charset);
			$startDate = $adb->query_result($query, $i, 'date_start');
			$startTime = $adb->query_result($query, $i, 'time_start');
			$location = $adb->query_result($query, $i, 'location');
			$location = html_entity_decode($location, ENT_QUOTES, $default_charset);
			$startDateTime = $startDate." ".$startTime;
			$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
			$fieldModels = $moduleModel->getFields();
			$startDateTime= DateTimeField::convertToUserTimeZone($startDateTime)->format('Y-m-d H:i:s');
			$DATE_TIME_COMPONENTS = explode(' ' ,$startDateTime);
			$startDate = $DATE_TIME_COMPONENTS[0];
			$startTime = $DATE_TIME_COMPONENTS[1];
			
			$FIELD_MODEL = $fieldModels['date_start'];
			$startDate = $FIELD_MODEL->getDisplayValue($startDate);
			$FIELD_MODEL = $fieldModels['time_start'];
			$startTime = $FIELD_MODEL->getDisplayValue($startTime);
			
			$startDateTime = $startDate." ".$startTime;
			$createdTime = $adb->query_result($query, $i, 'createdtime');
			if($createdTime!=''){
				$dateTimeFieldInstance = new DateTimeField($createdTime);
				$createdTime = Vtiger_Date_UIType::getDisplayDateTimeValue($createdTime);
			}
			
			$modifiedtime = $adb->query_result($query, $i, 'modifiedtime');
			if($modifiedtime!=''){
				$dateTimeFieldInstance = new DateTimeField($modifiedtime);
				$modifiedtime = $dateTimeFieldInstance->getDisplayDateTimeValue();
			}
			//for get Webservice Id of Calender and Events Module
			$EventTaskQuery = $adb->pquery("SELECT * FROM  `vtiger_activity` WHERE activitytype = ? AND activityid = ?",array('Task',$activityid)); 
		    if($adb->num_rows($EventTaskQuery) > 0){
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Calendar');
				$recordId = $wsid.'x'.$activityid;
				$recordModule = 'Calendar';
			}else{
				$wsid = Webapp_WS_Utils::getEntityModuleWSId('Events');
				$recordId = $wsid.'x'.$activityid;
				$recordModule = 'Events';
			}
			if($recordModule == 'Events'){
				$prevModule = 'Calendar';
			}else{
				$prevModule = $recordModule;
			}
			if(Users_Privileges_Model::isPermitted($prevModule, 'DetailView', $activityid)){
				$recentEvent_data[] = array('activityid'=> $recordId,'module'=>$recordModule,'eventSubject' => $eventSubject, 'activitytype' => $eventtype,'startDate' => $startDate,'startTime' => $startTime, 'startDateTime' => $startDateTime, 'location' => $location,
									'createdTime' => $createdTime, 'modifiedtime' => $modifiedtime, 'hour_format' => $current_user->hour_format);
			}
		}
		
	   $name = 'startDateTime';
	   usort($recentEvent_data, function ($a, $b) use(&$name){
		  return strtotime($a[$name]) - strtotime($b[$name]);
		});

		return $recentEvent_data;
	}

	public static function getAll($onlyActive=true, $excludeDefaultAdmin = true) {
		$db = PearDatabase::getInstance();
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$sql = 'SELECT id,first_name,last_name FROM vtiger_users';
		$params = array();
		if($onlyActive) {
			$sql .= ' WHERE status = ?';
			$params[] = 'Active';
		}
		$result = $db->pquery($sql, $params);

		$noOfUsers = $db->num_rows($result);
		$users = array();
		if($noOfUsers > 0) {
			$focus = new Users();
			for($i=0; $i<$noOfUsers; ++$i) {
				$userId = $db->query_result($result, $i, 'id');
				$userName = $db->query_result($result, $i, 'first_name').' '.$db->query_result($result, $i, 'last_name');
				$users[$userId] = html_entity_decode($userName, ENT_QUOTES ,$default_charset);
			}
		}
		return $users;
	}

}
