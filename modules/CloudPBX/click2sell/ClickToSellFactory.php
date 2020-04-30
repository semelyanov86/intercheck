<?php
namespace CloudPBX\click2sell;

use CloudPBX\integration\AbstractCallManagerFactory;
use CloudPBX\apiManagers\ClickToSellApiManager;
use CloudPBX\click2sell\notifications\ClickToSellNotification;
require_once 'modules/CloudPBX/apiManagers/ClickToSellApiManager.php';

class ClickToSellFactory extends AbstractCallManagerFactory {
    
    /**
     * 
     * @return ClickToSellApiManager
     */
    public function getCallApiManager() {
        return new ClickToSellApiManager();
    }

    /**
     * 
     * @return ClickToSellNotification
     */
    public function getNotificationModel($requestData) {
        return ClickToSellNotification::getInstance($requestData);
    }

}