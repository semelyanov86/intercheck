<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

//Same as Accounts Detail View
class Contacts_DetailView_Model extends Accounts_DetailView_Model {
    /**
     * Function to get the detail view links (links and widgets)
     * @param <array> $linkParams - parameters which will be used to calicaulate the params
     * @return <array> - array of link models in the format as below
     *                   array('linktype'=>list of link models);
     */
    public function getDetailViewLinks($linkParams) {
        global $platformUrl;
        $linkModelList = parent::getDetailViewLinks($linkParams);
        $permission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
        $responsePermission = false;
        if ($permission) {
            $responsePermission = true;
        } else {
            Users_Privileges_Model::getCurrentUserPrivilegesModel();
            $permission = Users_Privileges_Model::isPermitted('PBXManager', 'MakeOutgoingCalls');
            if ($permission) {
                $responsePermission = true;
            }
        }
        if ($responsePermission) {
            $recordModel = $this->getRecord();
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_MAKE_CALL',
                'linkurl' => 'javascript:CloudPBX_Js.showPhonePopup(' . $recordModel->getId() . ', "' . $recordModel->getModuleName() . '");',
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $contactsModuleModel = Vtiger_Module_Model::getInstance('Contacts');
        if($currentUserModel->hasModulePermission($contactsModuleModel->getId())) {
            $recordModel = $this->getRecord();
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_OPEN_PLATFORM',
                'linkurl' => 'javascript:Contacts_Detail_Js.openPlatform("' . $platformUrl . '/auth/' . $recordModel->get('cf_platform_id') . '/' . $recordModel->get('cf_token') . '");',
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }


        return $linkModelList;
    }
}
