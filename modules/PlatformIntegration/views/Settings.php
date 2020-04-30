<?php

class PlatformIntegration_Settings_View extends Settings_Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
    }
    public function preProcess(Vtiger_Request $request)
    {
        parent::preProcess($request);
    }
    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $adb = PearDatabase::getInstance();
        $vteqboBase = new PlatformIntegration_Base_Model($module);
        $mode = $request->getMode();
        if ($mode) {
            $this->{$mode}($request);
        } else {
            global $root_directory;
            $sdkContainer = "modules/PlatformIntegration/helpers/";
            $sdkSource = $sdkContainer . "vendor/";
            if (is_dir($sdkSource)) {
                $autoloadFile = $sdkSource . "/autoload.php";
                if (file_exists($autoloadFile)) {
                    $this->renderSettingsUI($request);
                } else {
                    $this->downloadSDK($request);
                }
            } else {
                    $this->downloadSDK($request);
           }
        }
    }
    public function downloadSDK(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $vteqboModel = new PlatformIntegration_Module_Model($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign("PLATFORM_SDK_LINK", $vteqboModel->platformSdkLink);
        $viewer->assign("PLATFORM_SDK_SOURCE", $vteqboModel->platformSdkSource);
        $viewer->view("DownloadSDK.tpl", $moduleName);
    }
    public function notSupport(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $current_php_servion = phpversion();
        $vteqboBase = new PlatformIntegration_Base_Model($moduleName);
        $phpVersionRequired = $vteqboBase->phpVersionRequired;
        $viewer->assign("CURRENT_PHP_VERSION", $current_php_servion);
        $viewer->assign("PHP_VERSION_REQUIRED", $phpVersionRequired);
        $viewer->view("NotSupport.tpl", $moduleName);
    }
    public function sync(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        global $adb;
        $sql = "SELECT DISTINCT platform_module, vt_module FROM platformintegration_mapping_fields WHERE is_active = 1";
        $res = $adb->pquery($sql, array());
        $mappedModules = array();
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $mappedModules[] = array($row["platform_module"], $row["vt_module"]);
            }
        }
        $sql = "SELECT sync_type FROM platformintegration_api ORDER BY id LIMIT 1";
        $res = $adb->pquery($sql, array());
        $sync_type = "";
        if (0 < $adb->num_rows($res)) {
            $sync_type = $adb->fetchByAssoc($res, 0)["sync_type"];
        }
        $viewer->assign("QUALIFIED_MODULE", $moduleName);
        $viewer->assign("SYNC_TYPE", $sync_type);
        $viewer->assign("MAPPED_MODULES", $mappedModules);
        echo $viewer->view("Sync.tpl", $moduleName, true);
    }
    public function renderSettingsUI(Vtiger_Request $request)
    {
        global $current_user;
        $moduleName = $request->getModule();
        $vteqboModel = new PlatformIntegration_Module_Model($moduleName);
        $qboApi = $vteqboModel->getPlatformApi()["result"];
        $viewer = $this->getViewer($request);
        $viewer->assign("QUALIFIED_MODULE", $moduleName);
        $viewer->assign("PLATFORM_API", $qboApi);
        $allTab = $vteqboModel->getAllTab()["data"];
        $vtigerDate = new Vtiger_Date_UIType();
        foreach ($allTab as $k => $v) {
            $from_date = $allTab[$k]["OtherInfo"]["from_date"];
            if (!empty($from_date)) {
                $from_date = $vtigerDate->getDisplayValue($from_date);
                $allTab[$k]["OtherInfo"]["from_date"] = $from_date;
            }
        }

        $viewer->assign("ALL_TAB", $allTab);
        $supportedQboVersions = $vteqboModel->getSupportedPlatformVersions();
        $viewer->assign("SUPPORTED_PLATFORM_VERSIONS", $supportedQboVersions);
        $tooltipInfo = vtranslate("LBL_TOOLTIP_INFO_PAYMENTS_REQUIRES", $moduleName);
        $tooltipInfoPD = vtranslate("LBL_TOOLTIP_INFO_PD", $moduleName);
        $viewer->assign("TOOLTIP_INFO", $tooltipInfo);
        $viewer->assign("TOOLTIP_INFO_PD", $tooltipInfoPD);
        $dateFormat = $current_user->date_format;
        $viewer->assign("DATE_FORMAT", $dateFormat);
        $engineModel = new PlatformIntegration_Engine_Model($moduleName);
        $authUrl = "";
        $quickbooksCompanyName = "";
        if (!empty($qboApi["realmid"]) && !empty($qboApi["access_token"]) && !empty($qboApi["access_token_secret"]) && !empty($qboApi["consumer_key"]) && !empty($qboApi["consumer_secret"])) {
            $companyInfo = $engineModel->getCompanyInfo();
            if (empty($companyInfo)) {
                $authUrl = $engineModel->getAuthUrl();
            } else {
                $quickbooksCompanyName = $companyInfo->CompanyName;
            }
            $quickbooksCompanyName = vtranslate("LBL_YOU_ARE_CONNECTED_TO_PLATFORM", $moduleName) . "(<b>" . $quickbooksCompanyName . "</b>).";
        }
        $viewer->assign("AUTH_URL", $authUrl);
        $viewer->assign("QUICKBOOKS_COMPANY_NAME", $quickbooksCompanyName);
        $viewer->assign("QBO_API", $qboApi);
        echo $viewer->view("Settings.tpl", $moduleName, true);
    }
    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $bootstrapSwitchJs = "~/" . $template_folder . "/modules/PlatformIntegration/resources/bootstrapswitch/js/bootstrap-switch.min.js";
        } else {
            $template_folder = "layouts/v7";
            $bootstrapSwitchJs = "~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js";
        }
        $jsFileNames = array($bootstrapSwitchJs, "modules." . $moduleName . ".resources.Settings");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
    public function getHeaderCss(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $bootstrapSwitchCss = "~/" . $template_folder . "/modules/PlatformIntegration/resources/bootstrapswitch/css/bootstrap2/bootstrap-switch.min.css";
        } else {
            $template_folder = "layouts/v7";
            $bootstrapSwitchCss = "~/libraries/jquery/bootstrapswitch/css/bootstrap2/bootstrap-switch.min.css";
        }
        $cssFileNames = array("~/" . $template_folder . "/modules/" . $moduleName . "/css/" . $moduleName . ".css", $bootstrapSwitchCss);
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);
        return $headerCssInstances;
    }
}

?>