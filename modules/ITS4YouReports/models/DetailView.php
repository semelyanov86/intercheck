<?php

error_reporting(0);

class ITS4YouReports_DetailView_Model extends Vtiger_DetailView_Model {
    /**
     * Function to get the instance
     *
     * @param  <String> $moduleName - module name
     * @param  <String> $recordId - record id
     *
     * @return <Vtiger_DetailView_Model>
     */
    public static function getInstance($moduleName, $recordId) {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
        $instance = new $modelClassName();

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordModel = ITS4YouReports_Record_Model::getCleanInstance($recordId, $moduleName);

        return $instance->setModule($moduleModel)->setRecord($recordModel);
    }

    public function exportPDFAvailable() {
        $PDFMakerInstalled = vtlib_isModuleActive("PDFMaker");
        if ($PDFMakerInstalled === true && file_exists('modules/PDFMaker/resources/mpdf/mpdf.php') === true) {
            $PDFMakerInstalled = true;
        } else {
            $PDFMakerInstalled = false;
        }

        return $PDFMakerInstalled;
    }

    public function isTestWriteAble() {
        $is_writable = is_writable("test");

        return $is_writable;
    }

    /**
     * @return array|mixed
     */
    private static function getExportRestrictedUsers() {
        $restrictedUsers = [];
        $restrictedUsersFilePath = 'modules/ITS4YouReports/exportRestrictions.json';

        if (file_exists($restrictedUsersFilePath)) {
            $fileContent = file_get_contents($restrictedUsersFilePath);
            if (is_array(json_decode($fileContent))) {
                $restrictedUsers = json_decode($fileContent);
            }
        }

        return $restrictedUsers;
    }

    /**
     * Function to get the detail view links (links and widgets)
     *
     * @param string $linkParams
     *
     * @return |array
     * @throws Exception
     */
    public function getDetailViewLinks($linkParams = '')
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $moduleName = $moduleModel->getName();

        $detailViewLinks = [];

        if (!in_array($currentUserModel->getId(), self::getExportRestrictedUsers())) {

            if (ITS4YouReports_Bing_Map::isReadyForMaps($recordModel->getId()) && ITS4YouReports_Bing_Map::getApiKeyForJs()) {
                $detailViewLinks[] = [
                    'id'        => 'generateMap',
                    'linklabel' => vtranslate('LBL_GENERATE_MAP', $moduleName),
                    'linkurl'   => ITS4YouReports_Bing_Map::getGenerateMapUrl($recordModel->getId())
                ];
            }

            $detailViewLinks[] = [
                'linklabel' => vtranslate('LBL_REPORT_PRINT', $moduleName),
                //'linkurl' => $recordModel->getReportPrintURL(),
                'linkurl'   => '',
                'onClick'   => 'printDiv()',
                'linkicon'  => 'print.png'
            ];

            /*
                    $detailViewLinks[] = array(
                'linklabel' => vtranslate('LBL_REPORT_CSV', $moduleName),
                'linkurl' => $recordModel->getReportCSVURL(),
                'linkicon' => 'csv.png'
            );
                    */

            $detailViewLinks[] = [
                'linklabel' => vtranslate('LBL_REPORT_EXPORT_EXCEL', $moduleName),
                'linkurl'   => $recordModel->getReportExcelURL(),
                //'onClick'=>"ExportXLS();",
                'id'        => "XLSExport",
                'linkicon'  => 'xlsx.png'
            ];

            $PDFMakerInstalled = $this->exportPDFAvailable();
            $is_test_write_able = $this->isTestWriteAble();

            $detailViewLinks[] = [
                'linklabel' => vtranslate('LBL_EXPORTPDF_BUTTON', $moduleName),
                'linkurl'   => "",
                'id'        => "btnExport",
                'onClick'   => "generatePDF(" . $this->getRecord()->getId() . ", '$PDFMakerInstalled','$is_test_write_able', 'activate_pdfmaker');",
                'linkicon'  => 'pdf.png'
            ];
        }
        $linkModelList = [];

        foreach ($detailViewLinks as $detailViewLinkEntry) {
            $linkModelList[] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinkEntry);
        }

        return $linkModelList;
    }

    /**
     * Function to get the detail view widgets
     * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
     */
    public function getWidgets() {
        $moduleModel = $this->getModule();
        $widgets = [];

        if ($moduleModel->isTrackingEnabled()) {
            $widgets[] = [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'LBL_RECENT_ACTIVITIES',
                'linkurl' => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId() .
                    '&mode=showRecentActivities&page=1&limit=5',
            ];
        }

        $widgetLinks = [];
        foreach ($widgets as $widgetDetails) {
            $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
        }

        return $widgetLinks;
    }

    /**
     * Function to get the detail view Actions (links and widgets) for Report
     * @return <array> - array of link models in the format as below
     *                   array('linktype'=>list of link models);
     */
    public function getDetailViewActions() {
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $moduleName = $moduleModel->getName();

        $detailViewActions = array();
        if($recordModel->isEditable()) {
            $detailViewActions[] = array(
                'linklabel' => vtranslate('LBL_CUSTOMIZE', $moduleName),
                'linktitle' => vtranslate('LBL_CUSTOMIZE', $moduleName),
                'linkurl' => $recordModel->getEditViewUrl(),
                'linkiconclass' => 'icon-pencil',
            );
        }
        if($recordModel->isEditable()) {
            $detailViewActions[] = array(
                'linklabel' => vtranslate('LBL_DUPLICATE', $moduleName),
                'linkurl' => $recordModel->getDuplicateRecordUrl(),
            );
        }

        $linkModelList = array();
        if (!empty($detailViewActions)) {
            foreach ($detailViewActions as $detailViewLinkEntry) {
                $linkModelList[] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinkEntry);
            }
        }

        return $linkModelList;
    }

}
