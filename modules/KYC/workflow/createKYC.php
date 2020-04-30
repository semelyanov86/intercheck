<?php

function createKYC($ws_entity){
    // WS id
    global $VTIGER_BULK_SAVE_MODE;
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

    $transactionInstance = Vtiger_Record_Model::getInstanceById($crmid);
    $transactionType = $transactionInstance->get('transaction_type');
    $ccnumbers = $transactionInstance->get('four_digits');
    $contactId = $transactionInstance->get('payer');
    if ($contactId <= 0) {
        return;
    }
    $contactInstance = Vtiger_Record_Model::getInstanceById($contactId);
    $userId = $contactInstance->get('assigned_user_id');
    $relListModel = Vtiger_RelationListView_Model::getInstance($contactInstance, 'Transactions', 'Transactions');
    $pagingModel = new Vtiger_Paging_Model();
    $pagingModel->set('page', 1);
    $pagingModel->set('limit', 100);
    $entries = $relListModel->getEntries($pagingModel);
    if (count($entries) <= 1) {
        if ($transactionType == 'Deposit - credit card') {
            createKYCModel('Credit Card', $userId, $contactInstance->getId());
        }
        createKYCModel('ID', $userId, $contactInstance->getId());
        createKYCModel('POR', $userId, $contactInstance->getId());
    } else {
        if ($transactionType == 'Deposit - credit card' && isCreditCardNew($ccnumbers, $entries, $crmid)) {
            createKYCModel('Credit Card', $userId, $contactInstance->getId());
            $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
            $VTIGER_BULK_SAVE_MODE = true;
            $contactInstance->set('mode', 'edit');
            $contactInstance->set('cf_kyc_status', 'Verified 1');
            $contactInstance->save();
            $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
        }
    }

}

function createKYCModel($type, $userId, $contactId) {
    $kycInstance = Vtiger_Record_Model::getCleanInstance('KYC');
    $kycInstance->set('mode', 'create');
    $kycInstance->set('assigned_user_id', $userId);
    $kycInstance->set('document_type', $type);
    $kycInstance->set('cf_kyc_verification', 'None');
    $kycInstance->set('cf_contacts_id', $contactId);
    $kycInstance->set('cf_sync_to_platformintegration', 1);
    $kycInstance->save();
}

function isCreditCardNew($number, $entries, $crmid)
{
    $res = true;
    foreach ($entries as $entry) {
        if ($crmid == $entry->getId()) {
            continue;
        }
        $recModel = Vtiger_Record_Model::getInstanceById($entry->getId());
        if ($number == $recModel->get('four_digits')) {
            return false;
        }
    }
    return $res;
}
