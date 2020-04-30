<?php
register_shutdown_function(function() {
    $error = error_get_last();
    error_log(print_r($error, true));
});
$headers = getallheaders();
if (isset($headers['Echo'])) {
    header("Echo: {$headers['Echo']}");
    exit();
}
chdir(dirname(__FILE__) . '/../../');
include_once 'include/Webservices/Utils.php';
include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/ModuleTypes.php';
//include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
include_once 'libraries/htmlpurifier/library/HTMLPurifier.auto.php';
vimport('includes.http.Request');

global $current_user;

class KYCController {
    public function checkPermission($request)
    {
        return $request->get('publicid') === '8a60daa67a17be46112c53ed7df8521f' && $request->get('record');
    }

    public function process(Vtiger_Request $request, $files) {
        $current_user = CRMEntity::getInstance('Users');
        $current_user->retrieveCurrentUserInfoFromFile(1);
        $response = new Vtiger_Response();
        if (!$this->checkPermission($request)) {
            $response->setError(300, 'Access Denied!');
            $response->emit();
            die;
        }
        $recordId = $request->get('record');
        $actionModel = new VDUploadField_ActionAjax_Action();
        $wsid = vtws_getWebserviceEntityId('KYC', $recordId);
        $data = array('id' => $wsid, 'cf_kyc_verification' => 'In Process');
        foreach ($files as $field_name=>$file) {
            $fieldModel = Vtiger_Field_Model::getInstance($field_name, Vtiger_Module_Model::getInstance('KYC'));
            $newFile = array();
            $newFile['file']['name'] = $file['name'];
            $newFile['file']['size'] = $file['size'];
            $newFile['file']['tmp_name'] = $file['tmp_name'];
            $newFile['file']['type'] = $file['type'];
            $data[$field_name] = $actionModel->save($newFile) . '$$' . $newFile['file']['size'] . '$$' . $newFile['file']['type'] . '$$' . $fieldModel->getId();
        }

        try {
            $kyc = vtws_revise($data, $current_user);
        } catch (WebServiceException $ex) {
            $response->setError($ex->getCode(), $ex->getMessage());
            $response->emit();
            die;
        }
        $response->setResult($kyc);
        $response->emit();
    }

}

$current_user = Users::getActiveAdminUser();

$kycController = new KYCController();
$kycController->process(new Vtiger_Request($_REQUEST), $_FILES);
