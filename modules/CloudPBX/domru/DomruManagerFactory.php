<?php
namespace CloudPBX\domru;

use CloudPBX\integration\AbstractCallManagerFactory;
use CloudPBX\domru\notifications\DomruContactNotification;
use CloudPBX\domru\notifications\DomruEventNotification;
use CloudPBX\domru\notifications\DomruHistoryNotification;
use CloudPBX\apiManagers\DomruApiManager;

class DomruManagerFactory extends AbstractCallManagerFactory {
    
    public function getCallApiManager() {
        return new DomruApiManager();
    }

    public function getNotificationModel($request) {
        $notificationType = $request['cmd'];
        switch($notificationType) {
            
            case 'history':
                return new DomruHistoryNotification($request);
            
            case 'event':
                return new DomruEventNotification($request);
            
            case 'contact':
                return new DomruContactNotification($request);
                
            default:
                throw new \Exception('Unknow type');
        }
    }

}