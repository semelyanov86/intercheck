<?php
ini_set('display_errors', '0');

class VDUploadField_Settings_View extends Settings_Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
    }

    public function preProcess(Vtiger_Request $request)
    {
        parent::preProcess($request);
        $adb = PearDatabase::getInstance();
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $module);        
    }

    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $adb = PearDatabase::getInstance();        
        $mode = $request->getMode();

        if ($mode) {
            $this->$mode($request);
        } else {
            $this->showSettingPage($request);
        }            
        
    }
    
    public function showSettingPage(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('SettingPage.tpl', $module);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array('modules.' . $moduleName . '.resources.Settings');
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}

?>