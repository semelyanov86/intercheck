<?php

include_once 'include/Webservices/Retrieve.php';
include_once 'modules/Webapp/api/ws/FetchRecord.php';
class Webapp_WS_FetchRecordPrices extends Webapp_WS_FetchRecord {

    public $allowedFields = array('meeting_price', 'deal_price', 'cf_928');

    protected function processRetrieve(Webapp_API_Request $request) {
		$record = parent::processRetrieve($request);
		$result = array();
		foreach ($record as $k=>$v) {
		    if (in_array($k, $this->allowedFields)) {
		        if ($v != '') {
                    $result[$k] = number_format($v, 2);
                } else {
                    $result[$k] = 0.00;
                }

            }
        }
        return $result;
	}
}
