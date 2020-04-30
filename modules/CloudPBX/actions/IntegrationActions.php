<?php
require_once 'modules/CloudPBX/vendor/autoload.php';
include_once 'modules/CloudPBX/integration/AbstractCallManagerFactory.php';
use CloudPBX\integration\AbstractCallManagerFactory;


class CloudPBX_IntegrationActions_Action extends Vtiger_Action_Controller {
    
    function __construct() {
		parent::__construct();
		$this->exposeMethod('startOutgoingCall');
        $this->exposeMethod('getOutgoingPermissions');
	}
    
    function checkPermission(Vtiger_Request $request) {
		return;
	}
    
    public function startOutgoingCall(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        try {
            $factory = AbstractCallManagerFactory::getDefaultFactory();
            $callApiManager = $factory->getCallApiManager();        
            $callApiManager->doOutgoingCall($request->get('number'));
            $response->setResult(array('success'=>true));
        } catch (Exception $ex) {
            $response->setError($ex->getMessage());
        }
        $response->emit();
        
    }
    
    public function getOutgoingPermissions(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        $responsePermission = false;
        $permission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
        if ($permission) {
            $responsePermission = 'full_permission';
        } else {
            Users_Privileges_Model::getCurrentUserPrivilegesModel();
            $permission = Users_Privileges_Model::isPermitted('PBXManager', 'MakeOutgoingCalls');
            if ($permission) {
                $responsePermission = 'outgoing';
            }
        }
        $response->setResult(array('success' => true, 'permission' => $responsePermission));
        $response->emit();
    }

    public function process(\Vtiger_Request $request) {
        $mode = $request->getMode();
        try {
            if(!empty($mode)) {
                $this->invokeExposedMethod($mode, $request);
                return;
            }
        } catch (Exception $ex) {
            $response = new Vtiger_Response();
            $response->setError(vtranslate($ex->getMessage(), $request->getModule()));
            $response->emit();
            return;
        }
        
    }

}