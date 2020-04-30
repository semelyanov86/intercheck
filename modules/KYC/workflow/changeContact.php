<?php

function changeContact($ws_entity){
    // WS id
    $ws_id = $ws_entity->getId();
    $module = $ws_entity->getModuleName();
    if (empty($ws_id) || empty($module)) {
        return;
    }

    // CRM id
    $crmid = vtws_getCRMEntityId($ws_id);
    if ($crmid <= 0) {
        return;
    }

    $kycInstance = Vtiger_Record_Model::getInstanceById($crmid);
    $type = $kycInstance->get('cf_kyc_verification');
    if ($type != 'Verified') {
        return;
    }
    $contactId = $kycInstance->get('cf_contacts_id');
    if (!$contactId) {
        return;
    }
    $contactInstance = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
    $relatedListModel = Vtiger_RelationListView_Model::getInstance($contactInstance, 'KYC', 'KYC');
    $pagingModel = new Vtiger_Paging_Model();
    $pagingModel->set('page', 1);
    $pagingModel->set('limit', 100);
    $entries = $relatedListModel->getEntries($pagingModel);
    $cntStatus = 0;
    foreach($entries as $entry) {
        if ($entry->get('cf_kyc_verification') == 'Verified') {
            $cntStatus++;
        }
    }
    //On one Verified set client status as Partial, on ID & POR set as Verified1, on ID & POR & Credit card set as Verified2
    switch ($cntStatus) {
        case 1:
            $contactStatus = 'Partial';
            break;
        case 2:
            $contactStatus = 'Verified 1';
            break;
        case 3:
            $contactStatus = 'Verified 2';
            break;
        default:
            $contactStatus = 'None';;
    }
    $contactInstance->set('mode', 'edit');
    $contactInstance->set('cf_kyc_status', $contactStatus);
    $contactInstance->save();
}

