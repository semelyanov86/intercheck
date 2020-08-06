<?php

include_once "modules/QuotingTool/QuotingTool.php";
error_reporting(0);
/**
 * Class QuotingTool_List_View
 */
class QuotingTool_List_View extends Vtiger_Index_View
{
    /**
     * QuotingTool_List_View constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * @param Vtiger_Request $request
     * @param bool|true $display
     * @return bool|void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        if ($display) {
            $this->preProcessDisplay($request);
        }
        $adb = PearDatabase::getInstance();
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign("QUALIFIED_MODULE", $module);        
    }
    /**
     * @param Vtiger_Request $request
     * @param $vTELicense
     */
    public function step2(Vtiger_Request $request, $vTELicense)
    {
        global $site_URL;
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign("VTELICENSE", $vTELicense);
        $viewer->assign("SITE_URL", $site_URL);
        $viewer->view("Step2.tpl", $module);
    }
    /**
     * @param Vtiger_Request $request
     */
    public function step3(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view("Step3.tpl", $module);
    }
    /**
     * @param Vtiger_Request $request
     */
    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if ($file != "." && $file != "..") {
                if (is_dir($src . "/" . $file)) {
                    $result = $this->recurse_copy($src . "/" . $file, $dst . "/" . $file);
                } else {
                    $result = copy($src . "/" . $file, $dst . "/" . $file);
                }
            }
        }
        closedir($dir);
    }
    public function process(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $module = $request->getModule();
        $adb = PearDatabase::getInstance();
        $vTELicense = new QuotingTool_VTELicense_Model($module);
        $mode = $request->getMode();
                if ($mode) {
                    $this->{$mode}($request);
                } else {
                    $quotingTool = new QuotingTool();
                    $viewer = $this->getViewer($request);
                    $moduleName = $request->getModule();
                    $pdfLibContainer = "test/QuotingTool/resources/";
                    $pdfLibSource = $pdfLibContainer . "mpdf/mpdf.php";
                    if (file_exists($pdfLibSource)) {
                        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                        $this->viewName = $request->get("viewname");
                        $this->initializeListViewContents($request, $viewer);
                        $viewer->assign("VIEW", $request->get("view"));
                        $viewer->assign("MODULE_MODEL", $moduleModel);
                        $viewer->assign("CURRENT_USER_MODEL", Users_Record_Model::getCurrentUserModel());
                        $viewer->view("ListViewContents.tpl", $moduleName);
                    } else {
                        $mb_string_exists = function_exists("mb_get_info");
                        if ($mb_string_exists === false) {
                            $viewer->assign("MB_STRING_EXISTS", "false");
                        } else {
                            $viewer->assign("MB_STRING_EXISTS", "true");
                        }
                        $viewer->assign("PDF_LIB_LINK", $quotingTool->pdfLibLink);
                        $viewer->assign("PDF_LIB_SOURCE", $pdfLibContainer);
                        $viewer->view("Install.tpl", $moduleName);
                    }
                }
    }
    /**
     * Function to initialize the required data in smarty to display the List View Contents
     * @param Vtiger_Request $request
     * @param Vtiger_Viewer $viewer
     */
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $moduleName = $request->getModule();
        $listViewHeaders = array("id" => "LBL_ID", "filename" => "LBL_FILENAME", "module" => "LBL_MODULE", "is_active" => "LBL_ISACTIVE", "created" => "LBL_CREATED_DATE", "updated" => "LBL_UPDATED_DATE");
        $mbstring = extension_loaded("mbstring") ? "installed" : "";
        $phpZip = extension_loaded("zip") ? "installed" : "";
        $viewer->assign("LISTVIEW_HEADERS", $listViewHeaders);
        $templates = QuotingTool_Record_Model::findAll();
        $viewer->assign("TEMPLATES", $templates);
        $viewer->assign("LISTVIEW_ENTRIES_COUNT", count($templates));
        $viewer->assign("MODULE", $moduleName);
        $viewer->assign("MBSTRING", $mbstring);
        $viewer->assign("PHPZIP", $phpZip);
    }
    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $fontAwesome = "";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $fontAwesome = "~/" . $template_folder . "/modules/" . $moduleName . "/resources/css/font-awesome-4.5.0/css/font-awesome.min.css";
        } else {
            $template_folder = "layouts/v7";
        }
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = array($fontAwesome);
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);
        return $headerCssInstances;
    }
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array("modules.Vtiger.resources.List", "modules." . $moduleName . ".resources.List", "modules.Settings.Vtiger.resources.Index", "~/modules/" . $moduleName . "/resources/uploadfile/vendor/jquery.ui.widget.js", "~/modules/" . $moduleName . "/resources/uploadfile/jquery.iframe-transport.js", "~/modules/" . $moduleName . "/resources/uploadfile/jquery.fileupload.js");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}

?>