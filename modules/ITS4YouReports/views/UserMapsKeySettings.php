<?php

class ITS4YouReports_UserMapsKeySettings_View extends Vtiger_Index_View {

    /**
     * ITS4YouReports_UserMapsKeySettings_View constructor.
     */
    function __construct() {
        parent::__construct();
        $this->exposeMethod('Edit');
        $this->exposeMethod('SaveApiKey');
    }

    function preProcessTplName(Vtiger_Request $request) {
        return 'IndexViewPreProcess.tpl';
    }

    /**
     * @param Vtiger_Request $request
     *
     * @throws Exception
     */
    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        try {
            echo $this->invokeExposedMethod($mode, $request);
        } catch (Exception $e) {
            throw new Exception('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @throws SmartyException
     */
    public function Edit(Vtiger_Request $request) {
        global $current_user;
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $is_admin_user = is_admin($current_user);
        $viewer->assign('IS_ADMIN_USER', $is_admin_user);
        $viewer->assign('USER_DATE_FORMAT', $current_user->date_format);
        $viewer->assign('MODE', 'edit');

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('MAPS_API_USE_TYPE', ITS4YouReports_Bing_Map::getUserApiKeyType());

        $viewer->assign('DEFAULT_MAPS_API_KEY', ITS4YouReports_Bing_Map::getDefaultApiKey());
        $viewer->assign('MAPS_API_KEY',  ITS4YouReports_Bing_Map::getUserApiKey($current_user->id));

        if ($request->has('s')) {
            $viewer->assign('MSG_SAVED', $request->get('s'));
        }

        $viewer->view('UserMapKeyEditView.tpl', $moduleName);
    }

    /**
     * CREATE TABLE IF NOT EXISTS
     */
    public static function checkTable() {
        $db = PearDatabase::getInstance();
        $db->query('CREATE TABLE IF NOT EXISTS `its4you_reports_maps_api_keys` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `userid` int(11) NOT NULL,
                        `maps_api_use_type` varchar(255) NOT NULL,
                        `maps_api_key_value` varchar(255) DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `id` (`id`),
                        UNIQUE KEY `userid` (`userid`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
    }

    /**
     * @param Vtiger_Request $request
     *
     * @throws Exception
     */
    public function SaveApiKey(Vtiger_Request $request) {
        global $current_user;
        $is_admin_user = is_admin($current_user);
        $moduleName = $request->getModule();
        $view = $request->get('view');
        $mapsApiUseType = $request->get('maps_api_use_type');
        $mapsApiKeyDefault = $request->get('maps_api_key_default');
        $mapsApiKeyUser = $request->get('maps_api_key_user');
        self::checkTable();

        try {
            if ('default' === $mapsApiUseType) {
                if ($is_admin_user) {
                    ITS4YouReports_Bing_Map::saveUserApiKey(0, $mapsApiKeyDefault);
                } else {
                    throw new Exception('Only admin is permitted to save default API Key!');
                }
            } else {
                ITS4YouReports_Bing_Map::saveUserApiKey($current_user->id, $mapsApiKeyUser);
            }
        } catch (Exception $exception) {
            throw new Exception('error while saving API Key: ' . $exception->getMessage());
        }

        header(sprintf('Location: index.php?module=%s&view=%s&mode=Edit&s=true', $moduleName, $view));
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return |array
     */
    public function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            sprintf('modules.%s.resources.UserMapsKeySettings', $moduleName),
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

}
