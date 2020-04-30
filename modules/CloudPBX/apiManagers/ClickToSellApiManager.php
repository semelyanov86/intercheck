<?php
namespace CloudPBX\apiManagers;

use CloudPBX\integration\AbstractCallApiManager;
//use CloudPBX\api\ZadarmaClient;
use GuzzleHttp\Client as GClient;
require_once 'modules/CloudPBX/integration/AbstractCallApiManager.php';
class ClickToSellApiManager extends AbstractCallApiManager {
    
    private $callUrl = '/c2scall.php';
    private $recordUrl = '/v1/pbx/record/request/';
    public function doOutgoingCall($number) {
        
        $currentUser = \Users_Record_Model::getCurrentUserModel();
        $params = array(
            'exten' => 'SIP/' . $currentUser->get('cloud_pbx_extension'),
            'number' => $number
        );
        $client = new GClient(['base_uri' => \Settings_CloudPBX_Record_Model::getClick2SellApiURL() . $this->callUrl]);
        $answer = $client->request('GET', \Settings_CloudPBX_Record_Model::getClick2SellApiURL() . $this->callUrl, [
            'query' => $params
        ]);
        var_dump($answer->getBody());die;
        $answerObject = json_decode($answer);
        if ($answerObject->status != 'success') {            
            throw new \Exception($answerObject->message);
        }
    }
    
    public function getRecordLink($callId) {
        $params = array(
            'call_id' => $callId
        );
        $zd = new ZadarmaClient(
                \Settings_CloudPBX_Record_Model::getZadarmaKey(), 
                \Settings_CloudPBX_Record_Model::getZadarmaSecret()
                );
        $answer = $zd->call($this->recordUrl, $params);
        
        $answerObject = json_decode($answer);
        if ($answerObject->status != 'success') {
            throw new \Exception($answerObject->message);
        }
        return $answerObject->link;
    }

}