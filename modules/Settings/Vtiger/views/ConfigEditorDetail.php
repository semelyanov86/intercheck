<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_ConfigEditorDetail_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
	    global $restrictedFieldRoles;
	    global $restrictedFieldRolesPhones;
		$qualifiedName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();
        $restrictedRoles = $this->getRestrictedRoles($restrictedFieldRoles);
        $restrictedRolesPhones = $this->getRestrictedRoles($restrictedFieldRolesPhones);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('ROLE_NAMES', $restrictedRoles);
		$viewer->assign('ROLE_NAMES_PHONES', $restrictedRolesPhones);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('ConfigEditorDetail.tpl', $qualifiedName);
	}
	function getPageTitle(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_CONFIG_EDITOR',$qualifiedModuleName);
	}

	public function getRestrictedRoles($roles)
    {
        $res = '';
        $rolesArr = explode('||', $roles);
        foreach ($rolesArr as $roleId) {
            $roleModel = Settings_Roles_Record_Model::getInstanceById($roleId);
            if ($roleModel) {
                $res .= $roleModel->getName();
                $res .= ', ';
            }
        }
        $res = trim($res);
        return trim($res, ',');
    }

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.ConfigEditor",
			"modules.Settings.$moduleName.resources.ConfigEditorDetail",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}