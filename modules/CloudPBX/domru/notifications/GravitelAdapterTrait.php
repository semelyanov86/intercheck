<?php
namespace CloudPBX\domru\notifications;

use CloudPBX\ProvidersEnum;
trait GravitelAdapterTrait {    
    
    public function getCrmSavedToken() {
        return \Settings_CloudPBX_Record_Model::getDomruCrmToken();
    }

    public function getSourceUUId() {
        return 'domru_' . $this->get('callid') . '_' . $this->get('user');
    }
    
    protected function getAssignedUser() {
        $db = \PearDatabase::getInstance();
        $result = $db->pquery("SELECT id FROM vtiger_users WHERE cloud_domru_id=?", array(
            $this->getGravitelUserId()
        ));
        
        if($result && $resultRow = $db->fetchByAssoc($result)) {
            return \Vtiger_Record_Model::getInstanceById($resultRow['id'], 'Users');
        }
        
        return null;
    }
    
    protected function getProviderName() {
        return ProvidersEnum::DOMRU;
    }
}