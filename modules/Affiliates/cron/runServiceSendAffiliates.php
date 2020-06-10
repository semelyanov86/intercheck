<?php
require_once 'vtlib/Vtiger/Net/Client.php';
function runServiceSendAffiliates() {
    global $adb;
    global $platformUrl;
    global $platformToken;
    global $log;
    global $VTIGER_BULK_SAVE_MODE;
    $query = "SELECT affiliatesid FROM vtiger_affiliates INNER JOIN vtiger_crmentity ON vtiger_affiliates.affiliatesid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_affiliates.sync_to_platform = ?";
    $result = $adb->pquery($query, array(0, 1));
    $ids = array();
    if ($adb->num_rows($result)) {
        while ($data = $adb->fetch_array($result)) {
            $ids[] = $data['affiliatesid'];
        }
    }
    foreach($ids as $id) {
        $log->debug('Affiliates: Start syncing document id ' . $id);
        $affModel = Vtiger_Record_Model::getInstanceById($id, 'Affiliates');
        if (!$affModel->get('token')) {
            $affModel->set('token', generateRandomStringSB(10));
        }
        if ($affModel) {
            if ($affModel->get('platform_id')) {
                $httpClient = new Vtiger_Net_Client($platformUrl . '/api/v1/affiliates/' . $affModel->get('platform_id'));
            } else {
                $httpClient = new Vtiger_Net_Client($platformUrl . '/api/v1/affiliates');
            }
                $params = array(
                    'key' => $affModel->get('token'),
                    'name' => $affModel->get('name'),
                    'crmid' => $affModel->getId()
                );
                $headers = array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $platformToken, 'Accept' => 'application/json');
                $httpClient->setHeaders($headers);
                $response = $httpClient->doPost(json_encode($params));
                $result = json_decode($response, true);
                if ($result) {
                    $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
                    $VTIGER_BULK_SAVE_MODE = true;
                    $affModel->set('mode', 'edit');
                    $affModel->set('sync_to_platform', 0);
                    $affModel->set('last_sync_date', date('Y-m-d'));
                    $affModel->set('platform_id', $result['id']);
                    $affModel->save();
                    $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
                }
        }
    }    
}

function generateRandomStringSB($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}