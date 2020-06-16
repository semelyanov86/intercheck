<?php 

class ITS4YouReports_Detail_View extends Vtiger_Index_View {
    protected $reportData;
    protected $calculationFields;
    protected $count;
    protected $isInstalled = true;
    public function __construct() {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->exposeMethod('showDetailViewByMode');
    }
    public function process(Vtiger_Request $request) {
        $this->getProcess($request);
    }
    public function preProcessTplName(Vtiger_Request $request) {
        return (!$this->isInstalled) ? 'IndexViewPreProcess.tpl' : parent::preProcessTplName($request);
    }
    function showDetailViewByMode($request) {
        return $this->getReport($request);
    }
    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);
        $record = $request->get('record');
        $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId()) && !$reportModel->isEditable()) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }
    const REPORT_LIMIT = 1000;
    public function preProcess(Vtiger_Request $request, $display = true) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $detailViewModel = ITS4YouReports_DetailView_Model::getInstance($moduleName, $recordId);
        $reportModel = $detailViewModel->getRecord();
        $viewer->assign('REPORT_NAME', ITS4YouReports::getReportNameById($recordId));
        $reportChanged = $request->get('report_changed');
        parent::preProcess($request);
        $viewer->assign('RECORD_ID', $recordId);
        if ($reportModel) {
            $reportInformations = $reportModel->report->reportinformations;
            $reportType = $reportInformations['reporttype'];
            if (in_array($reportInformations['reporttype'], array('tabular', 'summaries_w_details'))) {
                $columnsLimit = $reportInformations['columns_limit'];
            }
            if ('tabular' !== $reportInformations['reporttype']) {
                $summariesLimit = $reportInformations['summaries_limit'];
            }
        }
        $viewer->assign('REPORTTYPE', $reportType);
        $viewer->assign('COLUMNS_LIMIT', $columnsLimit);
        $viewer->assign('SUMMARIES_LIMIT', $summariesLimit);
        $page = $request->get('page');
        $reportModel->setModule('ITS4YouReports');
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $page);
        $pagingModel->set('limit', self::REPORT_LIMIT);
        $reportData = $reportModel->getReportData($pagingModel);
        $this->reportData = $reportData['data'];
        $this->calculationFields = $reportModel->getReportCalulationData();
        $this->count = $reportData['count'];
        if ('custom_report' !== $reportInformations['reporttype']) {
            $primaryModule = $reportModel->getPrimaryModule();
            $secondaryModules = $reportModel->getSecondaryModules();
            $primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($primaryModuleModel->getId());
            if (!$permission) {
                $viewer->assign('MODULE', $primaryModule);
                $viewer->assign('MESSAGE', vtranslate('LBL_PERMISSION_DENIED'));
                $viewer->view('OperationNotPermitted.tpl', $primaryModule);
                exit;
            }
        }
        $viewer->assign('REPORT_MODEL', $reportModel);
        $viewer->assign('DETAILVIEW_ACTIONS', $detailViewModel->getDetailViewActions());
        $viewer->assign('DETAILVIEW_LINKS', $detailViewModel->getDetailViewLinks());
        $viewer->assign('PDFMakerActive', $detailViewModel->exportPDFAvailable());
        $viewer->assign('IS_TEST_WRITE_ABLE', $detailViewModel->isTestWriteAble());
        if ($layout !== "v7") {
            $viewer->assign("DISPLAY_FILTER_HEADER", true);
        }
        if ($request->get("currentMode") == "save") {
            $recordModel = ITS4YouReports_Record_Model::getInstanceById($recordId);
            if ($recordModel->isEditable()) {
                $ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
                $std_filter_columns = $ITS4YouReports->getStdFilterColumns();
                $advft_criteria = $reportModel->getSelectedAdvancedFilter();
                $advft_criteria_save = $advft_criteria_groups = array();
                if (!empty($advft_criteria)) {
                    foreach ($advft_criteria as $groupi => $conditions_arr) {
                        $advft_criteria_groups[$groupi]["groupcondition"] = $conditions_arr["condition"];
                        foreach ($conditions_arr["columns"] as $conditions_save) {
                            $conditions_save["groupid"] = $groupi;
                            $advft_criteria_save[] = $conditions_save;
                        }
                    }
                }
                $export_sql = false;
                $recordModel->saveAdvancedFilters($advft_criteria_save, $advft_criteria_groups, $std_filter_columns, $export_sql);
                $reportChanged = false;
            }
        }
        $ReportFilters = ITS4YouReports_EditView_Model::ReportFilters($request, $viewer);
        $viewer->assign("REPORT_FILTERS", $ReportFilters);
        $viewer->assign('REPORT_CHANGED', $reportChanged);
        if ($this->isInstalled) {
            $viewer->view('ReportHeader.tpl', $moduleName);
        } else {
            header('Location:  index.php?module=ITS4YouReports&view=List');
            exit;
        }
    }
    function getProcess(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        } else {
            echo $this->getReport($request);
        }
    }
    function getReport(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $record = $request->get('record');
        $page = $request->get('page');
        $data = $this->reportData;
        $calculation = $this->calculationFields;
        $reportModel = ITS4YouReports_Record_Model::getInstanceById($record);
        if (empty($data)) {
            $reportModel->setModule('ITS4YouReports');
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $page);
            $pagingModel->set('limit', self::REPORT_LIMIT + 1);
            $data = $reportModel->getReportData($pagingModel);
        }
        if (isset($data[0])) {
            $data = $data[0];
        }
        $viewer->assign('CALCULATION_FIELDS', $calculation);
        $viewer->assign('DATA', $data);
        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('MODULE', $moduleName);
        if (count($data) > self::REPORT_LIMIT) {
            $viewer->assign('LIMIT_EXCEEDED', true);
        }
        $viewer->view('ReportContents.tpl', $moduleName);
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ['modules.Vtiger.resources.Detail', "modules.$moduleName.resources.Detail"];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
} 

?>