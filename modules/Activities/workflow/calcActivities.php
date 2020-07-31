<?php
function calcActivities($ws_entity){
    global $adb;
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

    //получение объекта со всеми данными о текущей записи Модуля
    $activityModuleInstance = Vtiger_Record_Model::getInstanceById($crmid);

    //получение id HotelsData
    $contactId = $activityModuleInstance->get('cf_contacts_id');
    if ($contactId && $contactId > 0) {
        $contactModel = Vtiger_Record_Model::getInstanceById($contactId);
        $name = $activityModuleInstance->get('name');
        if ($name === 'Session started') {
            $contactModel->set('mode', 'edit');
            $contactModel->set('cf_is_online', 1);
            $contactModel->save();
        } elseif($name === 'Session ended') {
            $contactModel->set('mode', 'edit');
            $contactModel->set('cf_is_online', 0);
            $calcModel = new Activities_Calculation_Model($contactModel, $activityModuleInstance);
            $contactModel->set('cf_login_period', $calcModel->calcPeriod());
            $contactModel->save();
        }
    }

}