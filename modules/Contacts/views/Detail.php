<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Detail_View extends Accounts_Detail_View {

    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $recordId = $request->get('record');
        if ($recordId && $recordId > 0) {
            $recModel = Vtiger_Record_Model::getInstanceById($recordId, 'Contacts');
            $userModel = Users_Record_Model::getCurrentUserModel();
            if (!$userModel->get('allow_risks') && !$userModel->isAdminUser()) {
                if ($recModel->get('cf_risk_status') == 'High' || $recModel->get('cf_risk_status') == 'Dangerous') {
                    throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
                }
            }
        }
    }

	function __construct() {
		parent::__construct();
	}

	public function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		// Getting model to reuse it in parent 
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}
}
