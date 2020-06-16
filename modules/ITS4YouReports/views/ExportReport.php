<?php

class ITS4YouReports_ExportReport_View extends Vtiger_View_Controller {

    function __construct() {
        parent::__construct();
        //$this->exposeMethod('GetPrintReport');
        $this->exposeMethod('GetXLS');
        $this->exposeMethod('GetCSV');
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

        $record = $request->get('record');
        $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    function preProcess(Vtiger_Request $request, $display=true) {
        return false;
    }

    function postProcess(Vtiger_Request $request) {
        return false;
    }

    function process(Vtiger_request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * Function exports the report in a Excel sheet
     * @param Vtiger_Request $request
     */
    function GetXLS(Vtiger_Request $request) {
        $recordId = $request->get('record');

        if ('' === $recordId) {
            throw new AppException('Missing record ID!');
        }
        error_reporting(0);
//error_reporting(63);ini_set("display_errors",1);

        try {
            if (ITS4YouReports::isStoredITS4YouReport() === true) {
                $ogReport = ITS4YouReports::getStoredITS4YouReport();
            } else {
                $ogReport = new ITS4YouReports();
            }
            $generateObj = new GenerateObj($ogReport);
            $report_data = $generateObj->GenerateReport($recordId, 'XLS', false);

            $rootDirectory = vglobal('root_directory');
            $tmpDir = vglobal('tmp_dir');

            $tempFileName = tempnam($rootDirectory . $tmpDir, 'xls');
            $fileName = $ogReport->reportname . '.xls';
            $default_charset = vglobal("default_charset");
            $fileName = html_entity_decode($fileName, ENT_QUOTES, $default_charset);
            ob_end_clean();
            $generateObj->writeReportToExcelFile($tempFileName, $report_data);
            ob_end_clean();
            if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
                header('Pragma: public');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            }

            header('Content-Type: application/x-msexcel');
            header('Content-Length: ' . @filesize($tempFileName));
            header('Content-disposition: attachment; filename="' . $fileName . '"');

            $fp = fopen($tempFileName, 'rb');
            fpassthru($fp);
            unlink($tempFileName);
//echo "<pre>";print_r($report_data);echo "</pre>";
        }
        catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
            exit;
        }
    }

    /**
     * Function exports report in a CSV file
     * @param Vtiger_Request $request
     */
    function GetCSV(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $reportModel = ITS4YouReports_Record_Model::getInstanceById($recordId);
        $reportModel->set('advancedFilter', $request->get('advanced_filter'));
        $reportModel->getReportCSV();
    }

    /**
     * Function displays the report in printable format
     * @param Vtiger_Request $request
     */
    /* function GetPrintReport(Vtiger_Request $request) {
      $viewer = $this->getViewer($request);
      $moduleName = $request->getModule();

      $recordId = $request->get('record');
      $reportModel = ITS4YouReports_Record_Model::getInstanceById($recordId);
      $reportModel->set('advancedFilter', $request->get('advanced_filter'));
      $printData = $reportModel->getReportPrint();

      $viewer->assign('REPORT_NAME', $reportModel->getName());
      $viewer->assign('PRINT_DATA', $printData['data'][0]);
      $viewer->assign('TOTAL', $printData['total']);
      $viewer->assign('MODULE', $moduleName);
      $viewer->assign('ROW', $printData['data'][1]);

      $viewer->view('PrintReport.tpl', $moduleName);
      } */
}
