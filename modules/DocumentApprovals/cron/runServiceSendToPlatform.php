<?php
require_once 'vtlib/Vtiger/Net/Client.php';
function runServiceSendToPlatform() {
    global $adb;
    global $platformUrl;
    global $platformToken;
    global $log;
    $query = "SELECT document_approvalsid FROM vtiger_document_approvalscf INNER JOIN vtiger_crmentity ON vtiger_document_approvalscf.document_approvalsid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_document_approvalscf.cf_sync_to_platformintegration = ? AND cf_platform_id IS NOT NULL";
    $result = $adb->pquery($query, array(0, 1));
    $ids = array();
    if ($adb->num_rows($result)) {
        while ($data = $adb->fetch_array($result)) {
            $ids[] = $data['document_approvalsid'];
        }
    }
    $docResult = array();
    $notes = array(1 => '', 2 => '');
    foreach($ids as $id) {
        $log->debug('DocumentApprovals: Start syncing document id ' . $id);
        $docModel = Vtiger_Record_Model::getInstanceById($id, 'DocumentApprovals');
        if ($docModel) {
            $parent = $docModel->get('cf_contacts_id');
            if (!$parent) {
                $log->debug('DocumentApprovals: Document ' . $id . ' does not have parent contact id');
                continue;
            }
            $parentModel = Vtiger_Record_Model::getInstanceById($parent);
            if (!$parentModel) {
                $log->debug('DocumentApprovals: Document ' . $id . ' does not have parent contact model with id ' . $parent);
                continue;
            }
            $platformUserId = $parentModel->get('cf_platform_id');
            if (!$platformUserId) {
                $log->debug('DocumentApprovals: Contact ' . $platformUserId . ' does not have platform id value');
                continue;
            }
            $docResult[$id]['media_id'] = $docModel->get('cf_platform_id');
            $docResult[$id]['status'] = $docModel->get('document_approvals_status');
            $pageNo = $docModel->get('page');
            $notes[$pageNo] = $notes[$pageNo] . '||' . $docModel->get('description');
        }
    }
    $notes[1] = trim($notes[1], '||');
    $notes[2] = trim($notes[2], '||');
    foreach ($docResult as $docId=>$docData) {
        $httpClient = new Vtiger_Net_Client($platformUrl . '/api/v1/users/docs/' . $platformUserId);
        $params = array(
            'notes_docs_1' => $notes[1],
            'notes_docs_2' => $notes[2],
            'media_id' => $docData['media_id'],
            'status' => $docData['status']
        );
        $log->debug('DocumentApprovals: sending params data: ' . json_encode($params));
        $headers = array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $platformToken, 'Accept' => 'application/json');
        $httpClient->setHeaders($headers);
        $response = $httpClient->doPost(json_encode($params));
        if ($response) {
            $docModel = Vtiger_Record_Model::getInstanceById($docId, 'DocumentApprovals');
            $docModel->set('mode', 'edit');
            $docModel->set('cf_sync_to_platformintegration', 0);
            $docModel->set('cf_last_date_synched', date('Y-m-d'));
            $docModel->save();
        }
        $log->debug('DocumentApprovals: received response data ' . $response);
    }
}