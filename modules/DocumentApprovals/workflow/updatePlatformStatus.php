<?php
function UpdatePlatformStatus($ws_entity){
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

    //получение объекта со всеми данными о текущей записи Модуля "DocumentApprovals"
    $docInstance = Vtiger_Record_Model::getInstanceById($crmid);
var_dump($docInstance);die;
}