<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ModComments_Delete_Action extends Vtiger_Delete_Action {

	function checkPermission(Vtiger_Request $request) {
		//throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        parent::checkPermission($request);
	}

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('crmid');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $commentModel = ModComments_Record_Model::getInstanceById($recordId, $moduleName);
        $moduleModel = $recordModel->getModule();
        $relatedModel = Vtiger_Record_Model::getInstanceById($recordModel->get('related_to'));
        if(vtlib_isModuleActive('ModTracker')) {
            //Track the time the relation was deleted
            require_once 'modules/ModTracker/ModTracker.php';
            ModTracker::unLinkRelation($relatedModel->getModuleName(), $recordModel->get('related_to'), 'ModComments', $recordId);
        }
        $childArr = array();
        $parents = $commentModel->getChildComments();
        foreach ($parents as $parent) {
            if(vtlib_isModuleActive('ModTracker')) {
                ModTracker::unLinkRelation($relatedModel->getModuleName(), $parent->get('related_to'), 'ModComments', $parent->getId());
            }
            $parentRecord = Vtiger_Record_Model::getInstanceById($parent->getId(), $moduleName);
            $childArr[] = $parent->getId();
            $parents2 = $parent->getChildComments();
            foreach ($parents2 as $parent2) {
                $childArr[] = $parent2->getId();
                $parents3 = $parent2->getChildComments();
                foreach ($parents3 as $parent3) {
                    $childArr[] = $parent3->getId();
                }
            }
            $parentRecord->delete();
        }
        $recordModel->delete();
        $this->massRemoveComments($childArr, $relatedModel);
        $response = new Vtiger_Response();
        $response->setResult(array('success' => true));
        return $response;
    }

    private function massRemoveComments(array $childArr, Vtiger_Record_Model $relatedModel)
    {
        global $adb;
        $sqlString = implode(',', $childArr);
        $query = "SELECT modcommentsid FROM vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid WHERE parent_comments in ( $sqlString ) AND vtiger_crmentity.deleted = 0";
        $result = $adb->pquery($query, array());
        if ($adb->num_rows($result)) {
            while ($data = $adb->fetch_array($result)) {
                $recModel = Vtiger_Record_Model::getInstanceById($data['modcommentsid'], 'ModComments');
                if(vtlib_isModuleActive('ModTracker')) {
                    ModTracker::unLinkRelation($relatedModel->getModuleName(), $recModel->get('related_to'), 'ModComments', $recModel->getId());
                }
                $recModel->delete();
            }
        }
    }
}
