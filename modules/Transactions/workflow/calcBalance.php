<?php

function calcBalance($ws_entity){
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
    $relListModel = Vtiger_RelationListView_Model::getInstance($contactInstance, 'Transactions', 'Transactions');
    $pagingModel = new Vtiger_Paging_Model();
    $pagingModel->set('page', 1);
    $pagingModel->set('limit', 1000);
    $entries = $relListModel->getEntries($pagingModel);
    $total = 0;
    foreach ($entries as $entry) {
        if ($entry->get('trstatus') == 'Approved' && $crmid >= $entry->getId()) {
            $total += $entry->get('amount');
        }
    }
    $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
    $VTIGER_BULK_SAVE_MODE = true;
    $transactionInstance->set('mode', 'edit');
    $transactionInstance->set('cf_balance', $total);
    $transactionInstance->save();
    $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
}
