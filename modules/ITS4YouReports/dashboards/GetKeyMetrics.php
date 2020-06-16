<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');

class ITS4YouReports_GetKeyMetrics_Dashboard extends Vtiger_IndexAjax_View {
    
    function getSearchParams($value,$assignedto,$dates) {
        $listSearchParams = array();
        $conditions = array(array('leadstatus','e',$value));
        if($assignedto != '') array_push($conditions,array('assigned_user_id','e',getUserFullName($assignedto)));
        if(!empty($dates)){
            array_push($conditions,array('createdtime','bw',$dates['start'].' 00:00:00,'.$dates['end'].' 23:59:59'));
        }
        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
                
		$moduleName = "ITS4YouReports";
                
        $recordId = $request->get("record");
        $viewer->assign('recordid', $recordId);

		$linkId = $request->get('linkid');
		
		$createdTime = $request->get('createdtime');
		
		//Date conversion from user to database format
		if(!empty($createdTime)) {
			$dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
		}
		
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        
        $name = "";
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT name 
								FROM its4you_reports4you_key_metrics 
								WHERE id=?', array($recordId));
        if ($db->num_rows($result) > 0) {
            $row = $db->fetchByAssoc($result);
            $name = $row['name'];
        }

        $data = array();
        
        $key_metrics_id = $request->get("record");
        
        ITS4YouReports_CVRecord_Helper::setCustomViewRecordsToView();
		$kmSql = ITS4YouReports_KeyMetrics_Model::getKeyMetricsRowsSql();
		$kmRes = $db->pquery($kmSql, array($key_metrics_id));
        $keyMetricsEntries = array();
        $noOfEntries = $db->num_rows($kmRes);
        if ($noOfEntries > 0) {
            while ($kmRow = $db->fetchByAssoc($kmRes)) {
                if ($kmRow['report_deleted']) {
					continue;
				}
				$reportid = $kmRow['reportid'];
                $label = $kmRow['label'];
                $recordModel = ITS4YouReports_Record_Model::getInstanceById($reportid);
                $recordModel->setId($reportid);
                $metrics_type = $kmRow['metrics_type'];

                if($metrics_type=="customview" && in_array($reportid, ITS4YouReports_CVRecord_Helper::$cvIdsToView)){
                    $rheight = "2";
                    
                    $cv_array = $this->getKeyMetricsWithCountById($reportid);
                    array("cv_id"=>$cvid,"cv_module"=>$module,"count"=>$count,);
                    $keyMetricsEntries = $cv_array["count"];

                    $cv_id = $cv_array["cv_id"];
                    $cv_module = $cv_array["cv_module"];
                    $result_url = "index.php?module=$cv_module&view=List&viewname=$cv_id";

                } elseif($metrics_type=="report") {
                    $row_data = $moduleModel->getReports4YouKeyMetrics($reportid,$kmRow['column_str']);
                    $keyMetricsEntries = $row_data["entries"];
                    $rheight = $row_data["rows"];
                    $result_url = $recordModel->getDetailViewUrlForWidgets($moduleName);
                }
                $data[] = array("name"=>$label,"value"=>$keyMetricsEntries,"rheight"=>$rheight,"result_url"=>$result_url,);
            }
        }
        
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
        $widget->set('title', $name);

		//Include special script and css needed for this widget
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
        if($data==""){
            $data = vtranslate("LBL_NO_DATA_AVAILABLE", $moduleName);
        }
        $viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);
        
        $viewer->assign('MODULE', $moduleName);

		$accessibleUsers = $currentUser->getAccessibleUsersForModule('Leads');
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
        
        $viewer->assign('SETTING_EXIST', false);
        
        $content = $request->get('content');
		if(!empty($content)) {
            $display_widget_header = false;
        }else{
            $display_widget_header = true;
        }
        $viewer->assign('display_widget_header', $display_widget_header);
        
        $viewer->view('dashboards/DashBoardKeyMetrics.tpl', $moduleName);
	}
    
	// NOTE: Move this function to appropriate model.
	function getKeyMetricsWithCountById($cv_id) {
		global $current_user, $adb;
		$current_user = Users_Record_Model::getCurrentUserModel();
		
        $return = array();
        $count = "";
        $cvResult = ITS4YouReports_KeyMetrics_Action::getKeyMetricsCustomViewResult($cv_id);
        
        if ($adb->num_rows($cvResult) > 0) {
            while ($cv_row = $adb->fetchByAssoc($cvResult)) {
                $cvid = $cv_row["cvid"];
                $module = $cv_row["entitytype"];
                
    			$queryGenerator = new QueryGenerator($module, $current_user);
    			$queryGenerator->initForCustomViewById($cvid);
                if($module == "Calendar") {
                    // For calendar we need to eliminate emails or else it will break in status empty condition
                    $queryGenerator->addCondition('activitytype', "Emails", 'n',  QueryGenerator::$AND);
    			}
    			$metricsql = $queryGenerator->getQuery();
                $metricresult = $adb->query(Vtiger_Functions::mkCountQuery($metricsql));
    			if($metricresult) {
    				$rowcount = $adb->fetch_array($metricresult);
    				$count = $rowcount['count'];
    			}
            }
            $return = array("cv_id"=>$cvid,"cv_module"=>$module,"count"=>$count,);
        }
        return $return;
	}
        
}
