<?php

class Transactions_CheckBeforeSave_Action extends Vtiger_Action_Controller {

    public $mapTypes = array(
        'Deposit - credit card' => true,
        'card' => true,
        'Deposit - wire transaction' => true,
        'Purchase - campaign' => true,
        'Purchase - market buy' => true,
        'Chargeback' => false,
        'Withdrawal' => false,
        'Wire transaction' => true,
        'Request' => false,
        'Bonus' => false,
        'Market Buy' => true,
        'Click Income' => false,
        'Bid' => true,
        'Impression Income' => false,
        'Purchase - initial' => true,
        'Purchase - additional' => true
    );

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        global $adb;
        $dataArr = $request->get('checkBeforeSaveData');
        $response = "OK";
        $message = "";
        $currentType = $dataArr['transaction_type'];
        $amount = $dataArr['amount'];
        $mapType = $this->mapTypes[$currentType];
        if ($mapType) {
            if ($amount < 0) {
                $response ="ALERT";
                $message = vtranslate('LBL_VALIDATION_POSITIVE_ERROR', 'Transactions');
            }
        }
        if (!$mapType) {
            if ($amount > 0) {
                $response ="ALERT";
                $message = vtranslate('LBL_VALIDATION_NEGATIVE_ERROR', 'Transactions');
            }
        }
        echo json_encode(array("response" => $response, "message" => $message));

        return;
    }
}
?>