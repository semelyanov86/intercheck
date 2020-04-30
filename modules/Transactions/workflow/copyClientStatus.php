<?php

function copyClientStatus($ws_entity){
    global $VTIGER_BULK_SAVE_MODE;
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

    $transactionInstance = Vtiger_Record_Model::getInstanceById($crmid);
    $contactId = $transactionInstance->get('payer');
    if (!$contactId) {
        return;
    }
    $contactInstance = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
    $contactStatus = $contactInstance->get('cf_kyc_status');
    $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
    $VTIGER_BULK_SAVE_MODE = true;
    $transactionInstance->set('mode', 'edit');
    $transactionInstance->set('verification_status', $contactStatus);
    $transactionInstance->save();
    $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
}
