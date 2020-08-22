<?php

class ITS4YouReports_EditKeyMetrics_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process (Vtiger_Request $request) {

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$KeyMetricsId = $request->get('id');

		if ($KeyMetricsId) {
			$KeyMetricsModel = ITS4YouReports_KeyMetrics_Model::getInstanceById($KeyMetricsId);
		} else {
			$KeyMetricsModel = ITS4YouReports_KeyMetrics_Model::getInstance();
		}
        
		$viewer->assign('METRICS_MODEL', $KeyMetricsModel);
		$viewer->assign('MODULE',$moduleName);
        $viewer->view('EditKeyMetrics.tpl', $moduleName);
	}
}