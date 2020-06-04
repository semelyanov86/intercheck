<?php

class DocumentApprovals_Record_Model extends Vtiger_Record_Model
{
    public static function getNotesForPage($contactid, $page)
    {
        global $adb;
        $query = "SELECT document_approvalsid, description, vtiger_document_approvals.name FROM vtiger_document_approvals INNER JOIN vtiger_crmentity ON vtiger_document_approvals.document_approvalsid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_document_approvals.cf_contacts_id = ? AND page = ?";
        $result = $adb->pquery($query, array(0, $contactid, $page));
        $notes = array();
        $notetext = '';
        if ($adb->num_rows($result)) {
            while ($data = $adb->fetch_array($result)) {
                if ($data['description']) {
                    $notes[] = $data['description'];
                    $notetext = $notetext . '||' . $data['name'] . ' - ' . $data['description'];
                }
            }
        }
        return trim($notetext, '||');
    }

}