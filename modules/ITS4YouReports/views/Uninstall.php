<?php

class ITS4YouReports_Uninstall_View extends Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            if(!$currentUserModel->isAdminUser()) {
                    throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
            }
    }
        
    public function preProcess(Vtiger_Request $request, $display = true) {
        
        $ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Vtiger_Basic_View::preProcess($request, false);
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        
        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
        $linkModels = $ITS4YouReports->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
        
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
        $viewer->view('Uninstall.tpl', 'ITS4YouReports');
    }
    
     function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.ITS4YouReports.resources.Uninstall"
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    } 
}     