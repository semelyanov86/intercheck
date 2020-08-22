<?php

class ITS4YouReports_EditFolder_View extends Vtiger_IndexAjax_View {

    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function process(Vtiger_Request $request) {

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $folderId = $request->get('folderid');

        if ($folderId) {
            $folderModel = ITS4YouReports_Folder_Model::getInstanceById($folderId);
        } else {
            $folderModel = ITS4YouReports_Folder_Model::getInstance();
        }

        $viewer->assign('FOLDER_MODEL', $folderModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->view('EditFolder.tpl', $moduleName);
    }
}