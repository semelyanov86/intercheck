<?php

class SMSNotifier_Uco_Provider implements SMSNotifier_ISMSProvider_Model {

    private $token;
    private $parameters = array();

    private $SERVICE_URI = 'https://as.apeyes.work/api/v1/sms';
    private static $REQUIRED_PARAMETERS = array(array('name'=>'url','label'=>'Service URL','type'=>'text'),
        array('name'=>'AuthToken','label'=>'Auth Token','type'=>'text'),
        array('name'=>'template', 'label' => 'Template ID', 'type'=>'text'),
        array('name'=>'delay','label'=>'Delay','type'=>'text'));

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Uco';
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get service URL to use for a given type
     * @param <String> $type like SEND, PING, QUERY
     */
    public function getServiceURL($type = false) {
        return $this->getParameter('url');
    }

    /**
     * Function to set authentication parameters
     * @param <String> $userName
     * @param <String> $password
     */
    public function setAuthParameters($token, $password = false) {
        $this->token = $token;
//        $this->password = $password;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false) {
        if(isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    protected function prepareParameters() {
        foreach (self::$REQUIRED_PARAMETERS as $key=>$fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    /**
     * Function to handle SMS Send operation
     * @param <String> $message
     * @param <Mixed> $toNumbers One or Array of numbers
     */
    public function send($message, $toNumbers) {
        if(!is_array($toNumbers)) {
            $toNumbers = array($toNumbers);
        }
        $params = $this->prepareParameters();
        $httpClient = new Vtiger_Net_Client($this->getServiceURL());
        $headers = array('Content-Type' => 'application/json');
        $httpClient->setHeaders($headers);
        $results = array();
        foreach($toNumbers as $toNumber) {
            $response = $httpClient->doGet(array('to'=>$toNumber,'token'=>$params['AuthToken'],'template'=>$params['template'],'params'=>json_encode(['text' => $message])));
            $result = array();
            $result['id'] = null;
            $result['to'] = $toNumber;
            $result['status'] = self::MSG_STATUS_PROCESSING;
            $results[] = $result;
        }
        return $results;
    }

    /**
     * Function to get query for status using messgae id
     * @param <Number> $messageId
     */
    public function query($messageId) {
        return self::MSG_STATUS_DISPATCHED;
        $params = $this->prepareParameters();
        $params['Sid'] = $messageId;

        $params = $this->prepareParameters();
        $httpClient = new Vtiger_Net_Client($this->getServiceURL().'/'.$messageId);
        $httpClient->setHeaders(array('Authorization' => 'Basic '.base64_encode($params['AccountSID'].':'.$params['AuthToken'])));

        $xmlResponse = $httpClient->doGet(array());
        $xmlObject = simplexml_load_string($xmlResponse);

        $result = array();
        $result['error'] = false;
        $status = (string)$xmlObject->Message->Status;

        switch($status) {
            case 'queued'		:
            case 'sending'		:	$status = self::MSG_STATUS_PROCESSING;
                $result['needlookup'] = 1;
                break;

            case 'sent'			:	$status = self::MSG_STATUS_DISPATCHED;
                $result['needlookup'] = 1;
                break;

            case 'delivered'	:	$status = self::MSG_STATUS_DELIVERED;
                $result['needlookup'] = 0;
                break;

            case 'undelivered'	:
            case 'failed'		:
            default				:	$status = self::MSG_STATUS_FAILED;
                $result['needlookup'] = 1;
                break;
        }

        $result['status'] = $status;
        $result['statusmessage'] = $status;

        return $result;
    }

    function getProviderEditFieldTemplateName() {
        return 'Uco.tpl';
    }
}
?>