<?php

function createCommission($ws_entity){
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
    $amount = $transactionInstance->get('amount');
    if ($amount > 0) {
        $userId = $transactionInstance->get('assigned_user_id');
        $comInstance = Vtiger_Record_Model::getCleanInstance('Commissions');
        $comInstance->set('mode', 'create');
        $comInstance->set('assigned_user_id', $userId);
        $comInstance->set('percent', 100);
        $comInstance->set('cf_transactions_id', $transactionInstance->getId());
        $comInstance->save();
    }
}
