<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class VDUsers_EditAjax_View extends Vtiger_IndexAjax_View
{
	public function process(Vtiger_Request $request) {
	    global $adb;
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('source_module');
		$module = $request->getModule();
		$result = $adb->pquery('SELECT cvid FROM vtiger_customview WHERE viewname = ?', array('VDUsers'));
            $record = $adb->query_result($result,0,'cvid');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		if(!empty($record)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$customViewModel = new CustomView_Record_Model();
            $customViewModel->setModule($moduleName);
			$viewer->assign('MODE', '');
		}
		// Get roles
		$roles = Settings_Roles_Record_Model::getAll();
		$rolePicklist = VDUsers_Module_Model::getPicklist($roles);
		$roleFields = VDUsers_Module_Model::getRoleFields($roles);
		//echo '<pre>';print_r($roleFields);echo '</pre>';exit;
		
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $recordStructure = $recordStructureInstance->getStructure();
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $module);
		$viewer->assign('SOURCE_MODULE',$moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CV_PRIVATE_VALUE', CustomView_Record_Model::CV_STATUS_PRIVATE);
		$viewer->assign('CV_PENDING_VALUE', CustomView_Record_Model::CV_STATUS_PENDING);
        $viewer->assign('CV_PUBLIC_VALUE', CustomView_Record_Model::CV_STATUS_PUBLIC);
		$viewer->assign('MODULE_MODEL',$moduleModel);
		$viewer->assign('ROLES', $roles);
		$viewer->assign('ROLE_PICKLIST', $rolePicklist);
		$viewer->assign('ROLE_FIELDS', $roleFields);

		echo $viewer->view('EditView.tpl', $module, true);
	}
}