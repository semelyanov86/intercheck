<?php

class MultipleSMTP_CheckServerInfo_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate($moduleName) . " " . vtranslate("LBL_NOT_ACCESSIBLE"));
        }
    }
    public function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $rsult = $db->pquery("SELECT * FROM `vte_multiple_smtp` WHERE userid = ?", array($currentUser->getId()));
        if ($db->num_rows($rsult)) {
            $response->setResult(true);
        } else {
            $result = $db->pquery("SELECT 1 FROM vtiger_systems WHERE server_type = ?", array("email"));
            if ($db->num_rows($result)) {
                $response->setResult(true);
            } else {
                $response->setResult(false);
            }
        }
        return $response;
    }
}

?>