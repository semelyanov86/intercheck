<?php
namespace CloudPBX\click2sell\notifications;

class ClickToSellNotifyStart extends ClickToSellNotifyInternal {
    
    protected function getUserPhoneNumber() {
        return $this->get('called_did');
    }
    
}
