<?php
namespace CloudPBX\zadarma;

use CloudPBX\integration\AbstractCallManagerFactory;
use CloudPBX\apiManagers\ZadarmaApiManager;
use CloudPBX\zadarma\notifications\ZadarmaNotification;

class ZadarmaFactory extends AbstractCallManagerFactory {    
    
    /**
     * 
     * @return ZadarmaCallApiManager
     */
    public function getCallApiManager() {
        return new ZadarmaApiManager();
    }

    /**
     * 
     * @return ZadarmaNotificationManager
     */
    public function getNotificationModel($requestData) {
        return ZadarmaNotification::getInstance($requestData);
    }

}