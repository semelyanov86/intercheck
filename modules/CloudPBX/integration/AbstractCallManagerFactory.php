<?php
namespace CloudPBX\integration;

use CloudPBX\ProvidersEnum;
use CloudPBX\zadarma\ZadarmaFactory;
use CloudPBX\click2sell\ClickToSellFactory;
use CloudPBX\domru\DomruManagerFactory;
require_once 'modules/CloudPBX/ProvidersEnum.php';
require_once 'modules/CloudPBX/click2sell/ClickToSellFactory.php';

abstract class AbstractCallManagerFactory {

    public abstract function getNotificationModel($requestData);
    public abstract function getCallApiManager();
    
    /**
     * 
     * @return AbstractCallManagerFactory
     * @throws \Exception
     */
    public static function getDefaultFactory() {
        $defaultProvider = \Settings_CloudPBX_Record_Model::getDefaultProvider();
        return self::getEventsFacory($defaultProvider);
    }
    
    public static function getEventsFacory($providerName) {
        switch ($providerName) {
            case ProvidersEnum::ZADARMA :
                return new ZadarmaFactory();
            case ProvidersEnum::CLICK2SELL:
                return new ClickToSellFactory();
            case ProvidersEnum::DOMRU:
                return new DomruManagerFactory();
            default :
                throw new \Exception("Unknown voip");
        }
    }
}