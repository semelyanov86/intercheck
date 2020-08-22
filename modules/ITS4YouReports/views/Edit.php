<?php 

Class ITS4YouReports_Edit_View extends Vtiger_Edit_View {
    protected $isInstalled = true;
    public function __construct() {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->exposeMethod('editReport');
        $this->exposeMethod('ReportGrouping');
        $this->exposeMethod('ReportColumns');
        $this->exposeMethod('ChangeSteps');
    }
    public function process(Vtiger_Request $request) {
        $this->getProcess($request);
    }
    public function preProcessTplName(Vtiger_Request $request) {
        return (!$this->isInstalled) ? 'IndexViewPreProcess.tpl' : parent::preProcessTplName($request);
    }
    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = ITS4YouReports_ITS4YouReports_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
        $record = $request->get('record');
        if ($record) {
            $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
            if (!$reportModel->isEditable()) {
                throw new AppException('LBL_PERMISSION_DENIED');
            }
        }
    }
    function preProcess(Vtiger_Request $request, $display = true) {
        $mode = $request->get('mode');
        if ($mode == "ChangeSteps") $display = false;
        else $display = true;
        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        if ($recordId) {
            $viewer->assign('REPORT_NAME', ITS4YouReports::getReportNameById($recordId));
        }
        parent::preProcess($request, $display);
    }
    public function getProcess(Vtiger_Request $request) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if ($layout !== "v7") {
            $mode = $request->getMode();
            if (!empty($mode)) {
                echo $this->invokeExposedMethod($mode, $request);
                exit;
            }
        }
        $this->editReport($request);
    }
    public function ChangeSteps(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $step = $request->get("step");
        $viewer->assign('RECORD_MODE', $request->getMode());
        if ($request->has("reporttype") && !$request->isEmpty("reporttype")) {
            $reporttype = $request->get('reporttype');
            $viewer->assign('REPORTTYPE', $reporttype);
        }
        if ($step == "step1") {
            echo ITS4YouReports_EditView_Model::ReportsStep1($request, $viewer);
        } elseif ($step == "step4") {
            echo ITS4YouReports_EditView_Model::ReportGrouping($request, $viewer);
        } elseif ($step == "step5") {
            echo ITS4YouReports_EditView_Model::ReportColumns($request, $viewer);
        } elseif ($step == "step6") {
            echo ITS4YouReports_EditView_Model::ReportColumnsTotal($request, $viewer);
        } elseif ($step == "step7") {
            echo ITS4YouReports_EditView_Model::ReportLabels($request, $viewer);
        } elseif ($step == "step8") {
            echo ITS4YouReports_EditView_Model::ReportFiltersAjax($request, $viewer);
        } elseif ($step == "step9") {
            echo ITS4YouReports_EditView_Model::ReportSharing($request, $viewer);
        } elseif ($step == "step11") {
            echo ITS4YouReports_EditView_Model::ReportGraphs($request, $viewer);
        } elseif ($step == "step12") {
            echo ITS4YouReports_EditView_Model::ReportDashboards($request, $viewer);
        } elseif ($step == "step14") {
            echo ITS4YouReports_EditView_Model::ReportMaps($request, $viewer);
        }
    }
    public function editReport($request) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $record = $request->get('record');
        $reportType = '';
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if (!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = $this->record ? $this->record : ITS4YouReports_Record_Model::getInstanceById($record);
            $viewer->assign('MODE', '');
            $viewer->assign('isDuplicate', 'true');
        } else if (!empty($record)) {
            $recordModel = $this->record ? $this->record : ITS4YouReports_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = ITS4YouReports_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }
        if (!$this->record) {
            $this->record = $recordModel;
        }
        if ($recordModel) {
            $reportInformations = $recordModel->report->reportinformations;
            $reportType = $reportInformations['reporttype'];
        }
        if ($request->has("reporttype") && !$request->isEmpty("reporttype")) {
            $reportType = $request->get('reporttype');
        }
        $viewer->assign('REPORTTYPE', $reportType);
        if ($record != "") {
            $viewer->assign('MODE', 'edit');
        } else {
            $viewer->assign('MODE', 'create');
        }
        global $current_user;
        $is_admin_user = is_admin($current_user);
        $viewer->assign('IS_ADMIN_USER', $is_admin_user);
        $viewer->assign('USER_DATE_FORMAT', $current_user->date_format);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $f_view_mode = "real";
        $viewer->assign("steps_display", "reportTab hide");
        if ($f_view_mode == "debug_filters") {
            $viewer->assign("steps_display", "reportTab");
        }
        $viewer->assign('VIEW', $request->get('view'));
        $isRelationOperation = $request->get('relationOperation');
        $viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
        if ($isRelationOperation) {
            $viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
            $viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
        }
        if ($request->get('returnview')) {
            $request->setViewerReturnValues($viewer);
        }
        if ($layout == "v7") {
            $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
            $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
        }
        if (empty($reportType)) {
            $viewer->view('ITS4YouReportsType.tpl', $moduleName);
        } else {
            $reportModuleModel = $recordModel->getModule();
            $viewer->assign("REPORTNAME", $reportInformations['reports4youname']);
            $viewer->assign("REPORTDESC", $reportInformations['description']);
            $viewer->assign("REP_FOLDERS", $recordModel->getReportFolders());
            $ReportSharing = ITS4YouReports_EditView_Model::ReportSharing($request, $viewer);
            $viewer->assign("REPORT_SHARING", $ReportSharing);
            $ReportScheduler = ITS4YouReports_EditView_Model::ReportScheduler($request, $viewer);
            $viewer->assign("REPORT_SCHEDULER", $ReportScheduler);
            if ($reportType == "custom_report") {
                if ($is_admin_user != 1) {
                    ITS4YouReports::DieDuePermission();
                }
                $ReportCustomSQL = ITS4YouReports_EditView_Model::ReportCustomSql($request, $viewer);
                $viewer->assign("REPORT_CUSTOMSQL", $ReportCustomSQL);
                $viewer->view('EditCustom.tpl', $moduleName);
            } else {
                if ($request->get('isDuplicate')) {
                    $viewer->assign('isDuplicate', 'true');
                }
                $viewer->assign("PRIMARYMODULES", $recordModel->getPrimaryModules());
                $ReportGrouping = ITS4YouReports_EditView_Model::ReportGrouping($request, $viewer);
                $viewer->assign("REPORT_GROUPING", $ReportGrouping);
                $ReportColumns = ITS4YouReports_EditView_Model::ReportColumns($request, $viewer);
                $viewer->assign("REPORT_COLUMNS", $ReportColumns);
                $ReportColumnsTotal = ITS4YouReports_EditView_Model::ReportColumnsTotal($request, $viewer);
                $viewer->assign("REPORT_COLUMNS_TOTAL", $ReportColumnsTotal);
                $layout = Vtiger_Viewer::getDefaultLayoutName();
                if ($layout == "v7") {
                    $ReportCustomCalculations = ITS4YouReports_EditView_Model::ReportCustomCalculations($request, $viewer);
                    $viewer->assign("REPORT_CUSTOM_CALCULATIONS", $ReportCustomCalculations);
                } else {
                    $ReportCustomCalculations = ITS4YouReports_EditView_Model::ReportCustomCalculations($request, $viewer);
                    $viewer->assign("REPORT_CUSTOM_CALCULATIONS", $ReportCustomCalculations);
                }
                $ReportLabels = ITS4YouReports_EditView_Model::ReportLabels($request, $viewer);
                $viewer->assign("REPORT_LABELS", $ReportLabels);
                $ReportFilters = ITS4YouReports_EditView_Model::ReportFilters($request, $viewer);
                $viewer->assign("REPORT_FILTERS", $ReportFilters);
                $ReportGraphs = ITS4YouReports_EditView_Model::ReportGraphs($request, $viewer);
                $viewer->assign("REPORT_GRAPHS", $ReportGraphs);
                $ReportDashboards = ITS4YouReports_EditView_Model::ReportDashboards($request, $viewer);
                $viewer->assign("REPORT_DASHBOARDS", $ReportDashboards);
                $date_format = '<script> var userDateFormat = \'' . $current_user->date_format . '\' </script>';
                $viewer->assign('DATE_FORMAT', $date_format);
                if ($layout == "v7") {
                    $viewer->view('EditView.tpl', $moduleName);
                } else {
                    $viewer->view('Edit.tpl', $moduleName);
                }
            }
        }
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ["modules.$moduleName.resources.Edit", "modules.$moduleName.resources.ITS4YouReports"];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
} 

?>