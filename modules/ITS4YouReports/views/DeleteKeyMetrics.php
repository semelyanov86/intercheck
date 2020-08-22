<?php

class ITS4YouReports_DeleteKeyMetrics_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process (Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$KeyMetricsId = $request->get('id');
        if($KeyMetricsId!=""){
            $KeyMetricsModel = ITS4YouReports_KeyMetrics_Model::getInstanceById($KeyMetricsId);
            $KeyMetricsModel->delete();
            
            $result = array('success' => true, 'message' => vtranslate('LBL_KeyMetrics_DELETED', $moduleName), 'info' => array());
            $response = new Vtiger_Response();
    		$response->setResult($result);
    		$response->emit();
        }
	}
}