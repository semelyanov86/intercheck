<?php

class Contacts_LiveUpdateAjax_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function __construct()
    {
        $this->exposeMethod("getContacts");
        $this->exposeMethod("getLastId");
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getContacts(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        global $adb;
        $result = array();
        $res = $adb->pquery('SELECT contactid FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid WHERE contactid > ? AND vtiger_crmentity.deleted = 0 ORDER BY contactid DESC', array($request->get('record')));
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
//                $result[] = Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts')->getData();
                $result[] = array('id' => $row['contactid']);
            }
        }
        $response->setResult($result);
        $response->emit();
    }

    public function getLastId(Vtiger_Request $request)
    {
        global $adb;
        $res = $adb->pquery('SELECT contactid FROM vtiger_contactdetails ORDER BY contactid DESC LIMIT 1');
        $contactid=$adb->query_result($res,0,'contactid');
        $response = new Vtiger_Response();
        $response->setResult(array('id' => $contactid));
        $response->emit();
    }
}

