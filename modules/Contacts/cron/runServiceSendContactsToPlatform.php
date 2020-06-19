<?php

require_once 'vtlib/Vtiger/Net/Client.php';
function runServiceSendContactsToPlatform()
{
    global $adb;
    global $platformUrl;
    global $platformToken;
    global $log;
    global $VTIGER_BULK_SAVE_MODE;
    $query = "SELECT contactid FROM vtiger_contactscf INNER JOIN vtiger_crmentity ON vtiger_contactscf.contactid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_contactscf.cf_sync_to_platformintegration = ? AND cf_platform_id IS NOT NULL";
    $result = $adb->pquery($query, array(0, 1));
    $ids = array();
    if ($adb->num_rows($result)) {
        while ($data = $adb->fetch_array($result)) {
            $ids[] = $data['contactid'];
        }
    }
    $contactResult = array();
    $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
    $VTIGER_BULK_SAVE_MODE = true;
    foreach ($ids as $id) {
        $log->debug('ContactPlatform: Start syncing contact id ' . $id);
        $contactModel = Vtiger_Record_Model::getInstanceById($id, 'Contacts');
        if ($contactModel) {
            $httpClient = new Vtiger_Net_Client($platformUrl . '/api/v1/users/change-status/' . $contactModel->get('cf_platform_id'));
            $params = array(
                'cf_contacttype' => $contactModel->get('cf_contacttype'),
                'cf_sale_status' => $contactModel->get('cf_sale_status')
            );
            $log->debug('ContactPlatform: sending params data: ' . json_encode($params));
            $headers = array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $platformToken, 'Accept' => 'application/json');
            $httpClient->setHeaders($headers);
            $response = $httpClient->doPost(json_encode($params));
            if ($response) {
                $contactModel->set('mode', 'edit');
                $contactModel->set('cf_sync_to_platformintegration', 0);
                $contactModel->set('cf_last_date_synched', date('Y-m-d'));
                $contactModel->save();
            }
            $log->debug('ContactPlatform: received response data ' . $response);
        }
    }
    $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
}