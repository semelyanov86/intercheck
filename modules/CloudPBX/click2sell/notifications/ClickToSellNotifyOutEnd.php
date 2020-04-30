<?php
namespace CloudPBX\click2sell\notifications;

class ClickToSellNotifyOutEnd extends ClickToSellNotifyEnd {

    public function getValidationString() {
        return $this->get('internal') . $this->get('destination') . $this->get('call_start');
    }       
}
