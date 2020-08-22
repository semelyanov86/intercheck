<?php

class ITS4YouReports_IndexAjax_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		//$this->exposeMethod('showActiveRecords');
        $this->exposeMethod('showSettingsList');
        $this->exposeMethod('editLicense');
        $this->exposeMethod('ChangeSteps');
	}

	function preProcess(Vtiger_Request $request, $display=true) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/*
	 * Function to show the recently modified or active records for the given module
	 */
	function showActiveRecords(Vtiger_Request $request) {
            $viewer = $this->getViewer($request);
            $moduleName = $request->getModule();

            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $recentRecords = $moduleModel->getRecentRecords();
            $viewer->assign('MODULE', $moduleName);
            $viewer->assign('RECORDS', $recentRecords);
            echo $viewer->view('RecordNamesList.tpl', $moduleName, true);
	}
        
    function showSettingsList(Vtiger_Request $request){
        //$ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $viewer->assign('MODULE', $moduleName);

        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode'));
        $linkModels = $moduleModel->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
                
        $parent_view = $request->get('pview');

        $viewer->assign('CURRENT_PVIEW', $parent_view);

        echo $viewer->view('SettingsList.tpl', $moduleName, true);

    }

	function getRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('cvid');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if(!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }
			return $customViewModel->getRecordIds($excludedIds);
		}
	}
        
    /*function editLicense(Vtiger_Request $request) {

        $ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();

        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();

        $type = $request->get('type');
        $viewer->assign("TYPE", $type);

        $key = $request->get('key');
        $viewer->assign("LICENSEKEY", $key);
        
		$viewer->assign("MODULE", $request->getModule());
        
        echo $viewer->view('EditLicense.tpl', 'ITS4YouReports', true);
    }*/

    public function ChangeSteps(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $step = $request->get("step");

        $viewer->assign('RECORD_MODE', $request->getMode());

        if ($request->has("reporttype") && !$request->isEmpty("reporttype")) {
            $reportType = $request->get('reporttype');
            $viewer->assign('REPORTTYPE', $reportType);
        }

        switch ($step) {
            case 'step1':
                echo ITS4YouReports_EditView_Model::ReportsStep1($request, $viewer);
                break;
            case 'step4':
                echo ITS4YouReports_EditView_Model::ReportGrouping($request, $viewer);
                break;
            case 'step5':
                echo ITS4YouReports_EditView_Model::ReportColumns($request, $viewer);
                break;
            case 'step6':
                echo ITS4YouReports_EditView_Model::ReportColumnsTotal($request, $viewer);
                break;
            case 'step7':
                echo ITS4YouReports_EditView_Model::ReportLabels($request, $viewer);
                break;
            case 'step8':
                echo ITS4YouReports_EditView_Model::ReportFiltersAjax($request, $viewer);
                break;
            case 'step9':
                echo ITS4YouReports_EditView_Model::ReportSharing($request, $viewer);
                break;
            case 'step11':
                echo ITS4YouReports_EditView_Model::ReportGraphs($request, $viewer);
                break;
            case 'step12':
                echo ITS4YouReports_EditView_Model::ReportDashboards($request, $viewer);
                break;
            case 'step14':
                echo ITS4YouReports_EditView_Model::ReportMaps($request, $viewer);
                break;
        }
        session_write_close();
    }

}