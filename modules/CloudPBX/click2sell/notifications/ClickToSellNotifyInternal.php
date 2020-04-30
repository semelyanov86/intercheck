<?php
namespace CloudPBX\click2sell\notifications;

class ClickToSellNotifyInternal extends ClickToSellNotification {
    
    protected $fieldsMapping = array(
        'starttime' => 'call_start',
        'direction' => 'direction',
        'cloud_called_from_number' => 'caller_id',
        'cloud_called_to_number' => 'called_did',
        'user' => 'sp_user',
        'sourceuuid' => 'sourceuuid',
        'cloud_voip_provider' => 'cloud_voip_provider',
        'callstatus' => 'disposition',
        'totalduration' => 'duration',
        'cloud_is_recorder' => 'is_recorded',
        'cloud_recorded_call_id' => 'call_id_with_rec',
        'cloud_call_status_code' => 'status_code',
        'cloud_billduration' => 'duration',
        'customernumber' => 'caller_id',
    );

    public function getValidationString() {
        return $this->get('caller_id') . $this->get('called_did') . $this->get('call_start');
    }
    
    protected function prepareNotificationModel() {
        parent::prepareNotificationModel();               
        $this->set('sourceuuid', $this->getSourceUUId());                        
        $this->set('direction', 'inbound');
        if ($this->get('disposition') == '') {
            $this->set('disposition', 'ringing');
        }
    }
    
    public function validateNotification() {
        parent::validateNotification();
        $internal = $this->get('internal');
        $userModel = $this->getUserByNumber($this->get('internal'));
        if (empty($internal) || $userModel == null) {
            throw new \Exception('No need handle notification');
        }
    }

    protected function getCustomerPhoneNumber() {
        return $this->get('caller_id');
    }
    
    protected function getUserPhoneNumber() {
        return $this->get('internal');
    }

    protected function canCreatePBXRecord() {
        return true;
    }

}