<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');

class ITS4YouReports_BingMaps_View extends Vtiger_Index_View {

    private static $apiKey = '';

    public static $mapParameterColumns = [
        'zoom',
    ];
    public static $addressColumns = [
        'street',
        'city',
        'state',
        'zip',
        'country',
    ];
    protected static $defaultMapZoom = 10;
    protected $mapColumns = [];

    /**
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return bool|void
     */
    public function preProcess(Vtiger_Request $request, $display = true) {
        echo '<head>
                  <script data-pace-options=\'{ajax: false,document: false,eventLag: false,elements: {selectors: ["#R4YouMap"]}}\' src="modules/ITS4YouReports/lib/pace/pace.min.js"></script>
                  <link href="modules/ITS4YouReports/lib/pace/pace-theme.css" rel="stylesheet" />
              </head>';

        return false;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool|void
     * @throws AppException
     */
    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool|void
     */
    function postProcess(Vtiger_Request $request) {
        return false;
    }

    /**
     * @param Vtiger_request $request
     *
     * @throws SmartyException
     */
    function process(Vtiger_request $request) {
        $reportId = $request->get('record');
        self::$apiKey = ITS4YouReports_Bing_Map::getApiKeyForJs();

        if (!empty(self::$apiKey)) {
            if (!empty($reportId)) {
                $ITS4YouReports = new ITS4YouReports(true, $reportId);
                $this->setMapColumns($ITS4YouReports->reportinformations['maps']);
                $generateObj = new GenerateObj($ITS4YouReports);
                $reportMapData = $generateObj->generateBingMapsData($reportId);
                try {
                    self::displayMap($reportMapData['found']);
                    ITS4YouReports_Bing_Map::resultsToTitle($reportMapData);
                } catch (AppException $e) {
                    throw new Exception('map can not be displayed!');
                }
            } else {
                throw new Exception('record can not be empty!');
            }
        } else {
            throw new Exception('apiKey can not be empty!');
        }
    }

    /**
     * @param $foundData
     *
     * @throws AppException
     * @throws SmartyException
     */
    private function displayMap($foundData) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        if (!empty($foundData)) {
            if (!empty($foundData)) {
                $viewer = $this->getViewer($request);
                $moduleName = $request->getModule();
                $viewer->assign('MODULE', $moduleName);

                $record = $request->get('record');
                $viewer->assign('RECORD_ID', $record);
                $viewer->assign('API_KEY', self::$apiKey);
                $viewer->assign('MAP_ZOOM', (int) $this->getMapZoom());
                $viewer->assign('FOUND_DATA', $foundData);
                $viewer->assign('DEV_MODE', '0'); // 1 / 0

                $viewer->view('BingView.tpl', $moduleName);
            } else {
                throw new AppException('No data to display');
            }
        }
    }

    /**
     * @param array $mapColumns
     */
    public function setMapColumns($mapColumns) {
        $this->mapColumns = $mapColumns;
    }

    /**
     * @return int
     */
    public function getMapZoom() {
        return ($this->mapColumns['zoom'] ? : self::$defaultMapZoom);
    }
}