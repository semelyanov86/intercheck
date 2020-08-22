<?php


class ITS4YouReports_ITS4YouReportsSettings_View extends Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if(!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    /**
     * ITS4YouReports_UserMapsKeySettings_View constructor.
     */
    function __construct() {
        parent::__construct();
        $this->exposeMethod('Edit');
        $this->exposeMethod('SaveSettings');
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

        $viewer->assign('IS_ADMIN_USER', is_admin($current_user));
        $viewer->assign('USER_DATE_FORMAT', $current_user->date_format);
        $viewer->assign('MODE', 'edit');

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('SHARINGTYPES', ITS4YouReports_Record_Model::getSharingTypes());
        $viewer->assign('SHARINGTYPE', ITS4YouReports_Record_Model::getDefaultSharingType());

        if ($request->has('s')) {
            $viewer->assign('MSG_SAVED', $request->get('s'));
        }

        $viewer->view('SettingsEditView.tpl', $moduleName);
    }

    /**
     * CREATE TABLE IF NOT EXISTS
     */
    public static function checkTable() {
        $db = PearDatabase::getInstance();
        $db->query('CREATE TABLE IF NOT EXISTS `its4you_reports_crm_settings` (
                            `setting_name` varchar(20) NOT NULL,
                            `setting_value` varchar(50) DEFAULT NULL,
                        PRIMARY KEY (`setting_name`),
                        UNIQUE KEY `setting_name` (`setting_name`)
                    ) ENGINE=InnoDB;
        ');
    }

    /**
     * @param Vtiger_Request $request
     *
     * @throws Exception
     */
    public function SaveSettings(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        $moduleName = $request->getModule();
        $view = $request->get('view');

        self::checkTable();

        try {
            $db->pquery('REPLACE INTO its4you_reports_crm_settings VALUES(?,?)', [
                'default_sharing',
                $request->get('default_sharing')
            ]);
        } catch (Exception $exception) {
            throw new Exception('error while saving CRM ITS4YouReports Settings: ' . $exception->getMessage());
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
            sprintf('modules.%s.resources.ITS4YouReportsSettings', $moduleName),
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

}
