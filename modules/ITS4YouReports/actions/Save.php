<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');
//ini_set('display_errors', 1);
//error_reporting(63);
class ITS4YouReports_Save_Action extends Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Reports_Module_Model::getInstance($moduleName);

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

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $requestAll = $request->getAll();

        $record = $requestAll["record"];
        if ($record != "") {
            $mode = "edit";
        } else {
            $mode = "create";
        }

        global $default_charset;
        $reportModel = new ITS4YouReports_Record_Model();
        $reportModel->setModule("ITS4YouReports");
        if (!empty($record) && !$request->get('isDuplicate')) {
            $reportModel->setId($record);
        }

        $reportModel->set('reportname', $requestAll['reportname']);
        $reportModel->set('folderid', $requestAll['reportfolder']);
        $reportModel->set('description', $requestAll['reportdesc']);

        $reportModel->setPrimaryModule($requestAll['primarymodule']);

        $reportModel->set('template_owner', $requestAll['template_owner']);
        $reportModel->set('sharing', $requestAll['sharing']);

        $saveselectedcolumns = explode(";", trim($requestAll['selectedColumnsString'], ";"));
        $reportModel->set('selectedFields', $saveselectedcolumns);

        $selectedSummaries_array = explode(";", trim($requestAll['selectedSummariesString'], ";"));
        $reportModel->set('selectedSummaries', $selectedSummaries_array);

        $reportModel->set('summaries_orderby', $requestAll['summaries_orderby_columnString']);
        $reportModel->set('summaries_orderby_type', $requestAll['summaries_orderby_type']);

        $lbl_array = [];
        $lbl_url_string = $requestAll['labels_to_go'];
        $lbl_url_string = urldecode($lbl_url_string);
        $lbl_url_string = html_entity_decode($lbl_url_string, ENT_QUOTES, $default_charset);
        // $lbl_url_string = str_replace("@AMPKO@", "&", $lbl_url_string);
        if ($lbl_url_string != "") {
            $lbl_url_arr = explode('$_@_$', $lbl_url_string);
            foreach ($lbl_url_arr as $key => $lbl_value) {
                if (strpos($lbl_value, '_SC_lLbLl_') !== false) {
                    $temp = explode('_SC_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["SC"][$lbl_key] = $lbl_value;
                }
                if (strpos($lbl_value, '_SM_lLbLl_') !== false) {
                    $temp = explode('_SM_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["SM"][$lbl_key] = $lbl_value;
                }

                if (strpos($lbl_value, '_CT_lLbLl_') !== false) {
                    $temp = explode('_CT_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["CT"][$lbl_key] = $lbl_value;
                }
            }
        }
        $reportModel->set('lbl_array', $lbl_array);

        for ($gi = 1; $gi < 4; $gi++) {
            $reportModel->set('sort_by' . $gi, $requestAll['Group' . $gi]);
            $reportModel->set('sort_order' . $gi, $requestAll['Sort' . $gi]);
        }

        $reportModel->set('timeline_type2', $requestAll['timeline_type2']);
        $reportModel->set('timeline_type3', $requestAll['timeline_type3']);

        for ($tgi = 1; $tgi < 4; $tgi++) {
            if ($request->has('TimeLineColumn_Group' . $tgi) && !$request->isEmpty('TimeLineColumn_Group' . $tgi) && $requestAll['Group' . $tgi] != "none") {
                $TimeLineColumn_Group = $requestAll['TimeLineColumn_Group' . $tgi];
                $TimeLineColumn_Group_arr = explode("@vlv@", $TimeLineColumn_Group);
                $TimeLineColumn_str = $TimeLineColumn_Group_arr[0];
                $TimeLineColumn_frequency = $TimeLineColumn_Group_arr[1];
                $reportModel->set('TimeLineColumn_str' . $tgi, $TimeLineColumn_str);
                $reportModel->set('TimeLineColumn_frequency' . $tgi, $TimeLineColumn_frequency);
            }
        }

        $reportModel->set('SortByColumn', $requestAll['SortByColumn']);
        $reportModel->set('SortOrderColumn', $requestAll['SortOrderColumn']);

        $pmodule = $requestAll['primarymodule'];
        $reportModel->set('pmodule', $pmodule);
        $smodule = trim($requestAll['secondarymodule'], ":");
        $reportModel->set('smodule', $smodule);

        $reportModel->set('sharetype', $requestAll['sharing']);
        $reportModel->set('shared_entities', $requestAll['sharingSelectedColumnsString']);

        $columnstototal = explode('$_@_$', $requestAll["curl_to_go"]);
        $reportModel->set('columnstototal', $columnstototal);

        $json = new Zend_Json();
        //$std_filter_columns = $ITS4YouReports->getStdFilterColumns();
        $advft_criteria = $requestAll['advft_criteria'];
        $advft_criteria = $json->decode($advft_criteria);
        $reportModel->set('advft_criteria', $advft_criteria);
        $advft_criteria_groups = $requestAll['advft_criteria_groups'];
        $advft_criteria_groups = $json->decode($advft_criteria_groups);
        $reportModel->set('advft_criteria_groups', $advft_criteria_groups);

        $groupft_criteria = $requestAll['groupft_criteria'];
        $groupft_criteria = $json->decode($groupft_criteria);
        $reportModel->set('groupft_criteria', $groupft_criteria);

        $reportModel->set('limit', $requestAll['limit']);
        $reportModel->set('summaries_limit', $requestAll['summaries_limit']);

        $reportModel->set('isReportScheduled', $requestAll['isReportScheduled']);
        $reportModel->set('selectedRecipientsString', $requestAll['selectedRecipientsString']);
        $reportModel->set('scheduledReportFormat', $requestAll['scheduledReportFormat']);
        $reportModel->set('scheduledIntervalString', $requestAll['scheduledIntervalString']);

        $chartType = $requestAll["chartType"];
        $reportModel->set('chartType', $chartType);
        if ($chartType != "" && $chartType != "none") {
            $data_series = $requestAll["data_series"];
            $reportModel->set('data_series', $data_series);
            $charttitle = $requestAll["charttitle"];
            $reportModel->set('charttitle', $charttitle);
        }

        $reportModel->save();

        if ($requestAll["SaveType"] == "Save") {
            $loadUrl = $reportModel->getListViewUrl();
        } else {
            $loadUrl = $reportModel->getDetailViewUrl();
        }
        header("Location: $loadUrl");
    }
}
