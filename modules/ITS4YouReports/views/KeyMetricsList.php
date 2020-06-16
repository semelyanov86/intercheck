<?php


class ITS4YouReports_KeyMetricsList_View extends Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    function preProcess(Vtiger_Request $request, $display=true) {
        parent::preProcess($request, false);
        $viewer = $this->getViewer ($request);
        $moduleName = $request->getModule();

        $moduleModel = ITS4YouReports_KeyMetrics_Model::getInstance($moduleName);

        $linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));


        $viewer->assign('LEFTPANELHIDE', '1');

        //$quickLinkModels = $moduleModel->getSideBarLinks($linkParams);
        //$viewer->assign('QUICK_LINKS', $quickLinkModels);

        $this->initializeListViewContents($request, $viewer);

        if($display) {
            $this->preProcessDisplay($request);
        }
    }

    function preProcessTplName(Vtiger_Request $request) {
        return 'ListViewPreProcess.tpl';
    }

    function process (Vtiger_Request $request) {
        $viewer = $this->getViewer ($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $viewer->assign('LEFTPANELHIDE', '1');

        $this->initializeListViewContents($request, $viewer);

        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign("VERSION", ITS4YouReports_Version_Helper::$version);

        $viewer->view('KeyMetricsContents.tpl', $moduleName);
    }

    function postProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer ($request);
        $moduleName = $request->getModule();

        $viewer->view('ListViewPostProcess.tpl', $moduleName);
        parent::postProcess($request);
    }

    /*
     * Function to initialize the required data in smarty to display the List View Contents
     */
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();

        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
        $sourceModule = $request->get('sourceModule');

        $pageNumber = $request->get('page');
        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');
        if($sortOrder == "ASC"){
            $nextSortOrder = "DESC";
            $sortImage = "icon-chevron-down";
        }else{
            $nextSortOrder = "ASC";
            $sortImage = "icon-chevron-up";
        }

        if(empty ($pageNumber)){
            $pageNumber = '1';
        }

        $moduleModel = ITS4YouReports_KeyMetrics_Model::getInstance($moduleName);

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);

        $listview_header = array("name"=>vtranslate("LBL_WIDGET_NAME",$moduleName),"column"=>"name","search_val"=>"");
        $LISTVIEW_HEADERS["name"] = $listview_header;
        $listview_header = array("name"=>vtranslate("LBL_WIDGET_DESCRIPTION",$moduleName),"column"=>"description","search_val"=>"");
        $LISTVIEW_HEADERS["description"] = $listview_header;

        $listview_header = array("name"=>vtranslate("LBL_WIDGET_SMCREATORID",$moduleName),"column"=>"smcreatorid","search_val"=>"");
        $LISTVIEW_HEADERS["smcreatorid"] = $listview_header;

        $usersOptions = $this->getOwnerUsers();
        $viewer->assign('USERS_OPTIONS', $usersOptions);

        // ITS4YOU-CR SlOl 16. 12. 2015 13:13:12
        $kmSql = "SELECT its4you_reports4you_key_metrics.id, its4you_reports4you_key_metrics.name, its4you_reports4you_key_metrics.description, its4you_reports4you_key_metrics.smcreatorid 
        FROM its4you_reports4you_key_metrics 
        LEFT JOIN vtiger_users ON vtiger_users.id = its4you_reports4you_key_metrics.smcreatorid 
        WHERE its4you_reports4you_key_metrics.deleted = 0 ";

        if(!$currentUserModel->isAdminUser()) {
            $kmSql .= " and ( 
            its4you_reports4you_key_metrics.smcreatorid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where (vtiger_role.parentrole like '" . $currentUser->roleid . "::%' OR vtiger_role.parentrole like '%::" . $currentUserModel->roleid . "%'))
            ) ";
        }

        $params = array();
        if ($request->has('search_params') && !$request->isEmpty('search_params')){
            $search_params = $request->get('search_params');
            foreach($search_params as $si => $s_arr){
                foreach($s_arr as $sai => $s_params){
                    $scolumn = $s_params[0];
                    $svalue = vtlib_purify($s_params[2]);
                    if ($layout == "v7" && 'smcreatorid' === $scolumn) {
                        $users = explode(',', $svalue);
                        $kmSql .= " AND its4you_reports4you_key_metrics.$scolumn IN (" . generateQuestionMarks($users) . ") ";
                        $params[] = $users;
                    } else {
                        $kmSql .= " AND its4you_reports4you_key_metrics.$scolumn like '%$svalue%' ";
                    }
                    if($layout == "v7"){
                        $LISTVIEW_HEADERS[$scolumn]["searchValue"]=$svalue;
                    } else {
                        $LISTVIEW_HEADERS[$scolumn]["search_val"]=$svalue;
                    }
                }
            }
        }

        if ('v7'===$layout) {
            $searchParams=$request->get('search_params');
            if(empty($searchParams)) {
                $searchParams = array();
            }
            //To make smarty to get the details easily accesible
            foreach($searchParams as $fieldListGroup){
                foreach($fieldListGroup as $fieldSearchInfo){
                    $fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
                    $fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
                    $fieldSearchInfo['comparator'] = $fieldSearchInfo[1];
                    $searchParams[$fieldName] = $fieldSearchInfo;
                }
            }
            $viewer->assign('SEARCH_DETAILS', $searchParams);
        }
        $viewer->assign('LISTVIEW_HEADERS', $LISTVIEW_HEADERS);

        $kmSql .= " ORDER BY its4you_reports4you_key_metrics.id DESC ";

        $kmRes = $adb->pquery($kmSql, $params);
        $keyMetricsEntries = array();
        $noOfEntries = $adb->num_rows($kmRes);
        if ($noOfEntries > 0) {
            while ($kmRow = $adb->fetchByAssoc($kmRes)) {
                /*** KM ENTRIES !!! */
                $keyMetricsEntries[] = $kmRow;
            }
        }
        $viewer->assign('LISTVIEW_ENTRIES', $keyMetricsEntries);

        $viewer->assign("LISTVIEW_ENTRIES_COUNT", $noOfEntries);

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        // ITS4YOU-END

        $viewer->assign('MODULE', $moduleName);

        $viewer->assign('LISTVIEW_MASSACTIONS', $linkModels);

        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('PAGE_NUMBER',$pageNumber);

        $viewer->assign('ORDER_BY',$orderBy);
        $viewer->assign('SORT_ORDER',$sortOrder);
        $viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
        $viewer->assign('SORT_IMAGE',$sortImage);
        $viewer->assign('COLUMN_NAME',$orderBy);

        $viewer->assign('SOURCE_MODULE',$sourceModule);

        if ('v7' === $layout) {
            $moduleBasicLinks = $moduleModel->getModuleBasicLinks();
            foreach ($moduleBasicLinks as $basicLink) {
                $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
            }

            $moduleSettingLinks = $moduleModel->getSettingLinks();
            foreach ($moduleSettingLinks as $settingsLink) {
                $settingLinks[] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }

            $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
            $viewer->assign('MODULE_SETTING_ACTIONS', $settingLinks);
        }
    }

    /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

        $jsFileNames = array();
        if ('v7' === $layout) {
            $jsFileNames[] = 'modules.Vtiger.resources.Index';
        }
        $jsFileNames[] = 'modules.Vtiger.resources.List';
        $jsFileNames[] = 'modules.'.$moduleName.'.resources.KeyMetricsList';

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
    
    private function getOwnerUsers(){
        $reportOwners = array();
        $template_owners = get_user_array(false);
        if(!empty($template_owners)){
            //$selectedOwners = explode(',', $this->search_params['owner']);
            foreach($template_owners as $uid => $uname){
                $reportOwners[] = array($uid,$uname,(in_array($uid, $selectedOwners)?"SELECTED":""));
            }
        }
        return $reportOwners;
    }
	
}
?>