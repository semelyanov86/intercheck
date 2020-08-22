<?php

class ITS4YouReports_SaveAjax_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$record = $request->get('record');
		if (!$record) {
                    throw new AppException('LBL_PERMISSION_DENIED');
		}

		$moduleName = $request->getModule();
		$moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);
		$reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId()) && !$reportModel->isEditable()) {
                    throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
//ini_set("display_errors",1);error_reporting(63);
            ini_set("display_errors",0);
            $moduleName = $request->getModule();
            $recordId = $request->get('record');
            $page = $request->get('page');
            $detailViewModel = ITS4YouReports_DetailView_Model::getInstance($moduleName, $recordId);
            $reportModel = $detailViewModel->getRecord();
            $reportModel->setModule('ITS4YouReports');

            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $page);
            $pagingModel->set('limit', 1000);
echo "<pre>";print_r($request);echo "</pre>";
            $reportModel->set('advancedFilter', $request->get('advanced_filter'));
            $mode = $request->getMode();
            $viewer = $this->getViewer($request);
            
            $data = $reportModel->getReportData($pagingModel);
            
/*
            $page = $request->get('page');
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $page);
            $pagingModel->set('limit', ITS4YouReports_Detail_View::REPORT_LIMIT);
            */
            /*if ($mode === 'save') {
                $reportModel->saveAdvancedFilters();
                $reportData = $reportModel->getReportData($pagingModel);
                $data = $reportData[0];
            } else if ($mode === 'generate') {
                $reportData = $reportModel->generateData($pagingModel);
                $data = $reportData[0];
            }*/
//            $calculation = $reportModel->generateCalculationData();
//ini_set("display_errors",1);error_reporting(63);
            $viewer->assign('PRIMARY_MODULE', $reportModel->getPrimaryModule());
            $viewer->assign('CALCULATION_FIELDS', $calculation);
            $viewer->assign('DATA', $data);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('PAGING_MODEL', $pagingModel);
            $viewer->assign('MODULE', $moduleName);
            $viewer->assign('NEW_COUNT',$reportData['count']);

            //$viewer->assign('REPORT_RUN_INSTANCE', ReportRun::getInstance($record));
            $viewer->view('ReportContents.tpl', $moduleName);
	}

        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
