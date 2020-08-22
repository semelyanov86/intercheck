<?php

class Settings_ITS4YouReports_License_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$this->initializeContents($request);
	}

	public function initializeContents(Vtiger_Request $request) {
		$request->set('parent','Settings');

		$moduleName = $request->getModule();
		$qualifiedModule = $request->getModule(false);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$permission = $moduleModel->getLicensePermissions('Edit');
		$reportData = $moduleModel->licensePermissions;

		$installer = 'ITS4YouInstaller';
		$installerModel = Vtiger_Module_Model::getInstance($installer);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign("URL", vglobal("site_URL"));
		$viewer->assign("DEFAULT_VIEW_URL", $moduleModel->getDefaultUrl());
		$viewer->assign('IS_ALLOWED', $permission);
		$viewer->assign('MODULE_MODEL', $moduleModel);


		if(isset($reportData['errors'])) {
			$viewer->assign("ERRORS", $reportData['errors']);
		}

		if(isset($reportData['info'])) {
			$viewer->assign("INFO", $reportData['info']);
		}

		if($installerModel && $installerModel->isActive()) {
			$viewer->assign('IS_INSTALLER_ACTIVE', $installerModel->isActive());
			$viewer->assign('INSTALLER_MODEL', $installerModel);
		}

		$viewer->view('License.tpl', $qualifiedModule);
	}
}