<?php

include_once 'include/Webservices/Retrieve.php';
include_once 'modules/Webapp/api/ws/FetchRecord.php';
include_once 'include/Webservices/Utils.php';
class Webapp_WS_GetUserPlatform extends Webapp_WS_FetchRecord {

    protected function processRetrieve(Webapp_API_Request $request) {
        global $adb;
        $result = $adb->pquery('SELECT contactid FROM vtiger_contactscf INNER JOIN vtiger_crmentity ON vtiger_contactscf.contactid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? AND vtiger_contactscf.cf_platform_user_id = ? AND vtiger_contactscf.cf_platform_id = ? LIMIT 1', array(0, $request->get('playerid'), $request->get('platformid')));
        $contactId = $adb->query_result($result,0,'contactid');
        if (!$contactId || $contactId < 1) {
            $response = new Webapp_API_Response();
            $response->setError(404, 'Record with this platform user id and platform id not found in database!');
            echo $response->emitJSON();
            exit;
        }
        $webContactId = vtws_getWebserviceEntityId('Contacts', $contactId);
        $request->set('record', $webContactId);
		return parent::processRetrieve($request);
	}
}
