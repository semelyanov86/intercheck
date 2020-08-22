<?php

class Settings_ITS4YouReports_Uninstall_View extends Settings_Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if(!$currentUserModel->isAdminUser()) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }
        
    public function preProcess(Vtiger_Request $request, $display = false) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Settings_Vtiger_Index_View::preProcess($request, false);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $mode = $request->get('mode');
        $viewer->assign("MODE", $mode);      
        $viewer->view('Uninstall.tpl', $request->getModule(false));         
    }
    
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array(
            "layouts.vlayout.modules.Settings.ITS4YouReports.resources.Uninstall"
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    } 
}     