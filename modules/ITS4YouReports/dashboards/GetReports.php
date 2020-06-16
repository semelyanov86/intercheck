<?php

class ITS4YouReports_GetReports_Dashboard extends Vtiger_IndexAjax_View {

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

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if($layout == "v7"){
            self::processV7($request);
        } else {
            self::processV6($request);
        }
	}

    public function processV6(Vtiger_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);

        $moduleName = $request->get('module');

        $recordId = $request->get("record");
        $viewer->assign('recordid', $recordId);

        $linkId = $request->get('linkid');
        $data = $request->get('data');

        $createdTime = $request->get('createdtime');
              
        //Date conversion from user to database format
        if(!empty($createdTime)) {
            $dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
            $dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
        }

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $recordModel = ITS4YouReports_Record_Model::getInstanceById($recordId);

        $data = $moduleModel->getReports4You($recordId,$request->get('smownerid'),$dates);

        $detailViewUrl = 'index.php?module=ITS4YouReports&view=Detail&record='.$recordId;
        $viewer->assign('detailViewUrl', $detailViewUrl);

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
        $widget->set('title', $recordModel->getName());

        if($request->has('mode') && $request->get('mode')=='widget'){
            //echo substr($data,31,-9);
            echo $data; 
            die();
        }
//echo "<pre>";print_r($data);echo "</pre>";
//echo "<pre>";print_r(2);echo "</pre>";

        //Include special script and css needed for this widget
        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        @ob_clean();
        if(is_array($data)){
            $data = "<script>
jQuery( document ).ready(function() {
  jQuery('#reports4you_widget_$recordId').html('".vtranslate("LBL_NO_DATA_TO_DISPLAY", $moduleName)."');
});
            </script>";
        }

        // ITS4YOU-CR SlOl 9. 3. 2016 15:57:34
        $Rmodule = '';
        $widgetSearchBy = ITS4YouReports::getWidgetSearchArray($recordId);
        if(!empty($widgetSearchBy)){
            if(isset($recordModel->report->primarymodule)){
                $Rmodule = $recordModel->report->primarymodule;
            }
            if(isset($widgetSearchBy["primary_search"]) && $widgetSearchBy["primary_search"]!=""){
                $primary_search = $widgetSearchBy["primary_search"];
                $viewer->assign('primary_search', $primary_search);
                $primary_search_array = $widgetSearchBy["primary_search_values"];
                $viewer->assign('primary_values', $primary_search_array["picklistValues"]);
                $viewer->assign('primary_selected', $primary_search_array["valueArr"]);
                $primary_label = ITS4YouReports::getColumnStr_Label($primary_search);
                $viewer->assign('primary_label', $primary_label);
            }

        }
        $viewer->assign('LModule', $Rmodule);
        // ITS4YOU-END

        $viewer->assign('DATA', $data);
        $viewer->assign('CURRENTUSER', $currentUser);

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

        $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
    }

    /** there is a bug which does not set dashboardtabid of widget while saving vtiger_module_dashboard_widgets */
    protected function fixWidgetTabs(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();

        /** fix start */
        $tabid = $request->get('tab');
        $linkId = $request->get('linkid');

        // CT - current tab
        $sqlCT = 'SELECT id 
                FROM vtiger_module_dashboard_widgets 
                WHERE linkid = ? AND userid = ? AND dashboardtabid=? ';
        $paramsCT = array($linkId, $currentUser->getId(), $tabid);
        $resultCT = $db->pquery($sqlCT, $paramsCT);
        if(!$db->num_rows($resultCT)) {
            $sql = 'SELECT id 
                    FROM vtiger_module_dashboard_widgets 
                    WHERE linkid = ? AND userid = ? AND dashboardtabid=? AND dashboardtabid!=?';
            $params = array($linkId, $currentUser->getId(), 1, $tabid);
            $result = $db->pquery($sql, $params);

            if(!$db->num_rows($result)) {
                $db->pquery('UPDATE vtiger_module_dashboard_widgets
                                  SET dashboardtabid=? 
                                  WHERE linkid=? AND userid=? AND dashboardtabid=?', [$tabid, $linkId, $currentUser->getId(), 1]);
            } else {
                $db->pquery('INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, title, data,dashboardtabid) VALUES(?,?,?,?,?,?)',
                    array($linkId, $currentUser->getId(), '', '', Zend_Json::encode(''),$tabid));
            }
        }
        /** fix end */

    }

    public function processV7(Vtiger_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getDefaultLayoutName();

        $recordId = $request->get("record");
        $viewer->assign('recordid', $recordId);

        $linkId = $request->get('linkid');
        $data = $request->get('data');

        if ($request->has('tab')) {
            $tabId = $request->get('tab');
        }

        self::fixWidgetTabs($request);

        $createdTime = $request->get('createdtime');

        //Date conversion from user to database format
        if(!empty($createdTime)) {
            $dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
            $dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
        }

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $recordModel = ITS4YouReports_Record_Model::getInstanceById($recordId);

        $data = $moduleModel->getReports4You($recordId,$request->get('smownerid'),$dates);

        $detailViewUrl = 'index.php?module=ITS4YouReports&view=Detail&record='.$recordId;
        $viewer->assign('detailViewUrl', $detailViewUrl);

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
        $widget->set('title', $recordModel->getName());
        $widget->set('tabid', $tabId);

        if(!is_array($data) && $request->has('mode') && $request->get('mode')=='widget'){
            echo $data;
            die();
        }

        //Include special script and css needed for this widget
        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        if(is_array($data)){
            $data = '';
            if('v7' !== $layout) {
                $data .= '<script>';
            }
            $data .= "jQuery( document ).ready(function() {
  jQuery('#reports4you_widget_$recordId').html('".vtranslate("LBL_NO_DATA_TO_DISPLAY", $moduleName)."');
});";
            if('v7' !== $layout) {
                $data .= '</script>';
            }
            if('v7' === $layout) {
                echo $data;
                die();
            }
        }

        // ITS4YOU-CR SlOl 9. 3. 2016 15:57:34
        $Rmodule = '';
        $widgetSearchBy = ITS4YouReports::getWidgetSearchArray($recordId);
        if(!empty($widgetSearchBy)){
            if(isset($recordModel->report->primarymodule)){
                $Rmodule = $recordModel->report->primarymodule;
            }
            if(isset($widgetSearchBy["primary_search"]) && $widgetSearchBy["primary_search"]!=""){
                $primary_search = $widgetSearchBy["primary_search"];
                $viewer->assign('primary_search', $primary_search);
                $primary_search_array = $widgetSearchBy["primary_search_values"];
                $viewer->assign('primary_values', $primary_search_array["picklistValues"]);
                $viewer->assign('primary_selected', $primary_search_array["valueArr"]);
                $primary_label = ITS4YouReports::getColumnStr_Label($primary_search);
                $viewer->assign('primary_label', $primary_label);
            }

        }
        $viewer->assign('LModule', $Rmodule);
        // ITS4YOU-END

        $viewer->assign('REPORT_MODEL', $recordModel);

        $viewer->assign('DATA', $data);
        $viewer->assign('CURRENTUSER', $currentUser);

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

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if($layout === "v7"){
            $content = $request->get('content');
            /*
            if(!empty($content)) {
                $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
            } else {
                $viewer->view('dashboards/DashBoardWidget.tpl', $moduleName);
            }
            */
            if(!empty($content)) {
                $viewer->view('dashboards/DashBoardWidgetAjax.tpl', $moduleName);
            } else {
                $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
            }
        } else {
            $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
        }

    }

}
