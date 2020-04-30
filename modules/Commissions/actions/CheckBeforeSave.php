<?php

class Commissions_CheckBeforeSave_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        global $adb;
        $dataArr = $request->get('checkBeforeSaveData');
        $response = "OK";
        $message = "";
        $currentPercent = (float) $dataArr['percent'];
        $transactionId = $dataArr['cf_transactions_id'];

        if(true) {
            $record = $dataArr['record'];
            $query = "SELECT SUM(percent) as percents FROM vtiger_commissions INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_commissions.commissionid WHERE vtiger_crmentity.deleted = 0 AND cf_transactions_id = ?";
            if ($record && $record > 0) {
                $query .= " AND vtiger_commissions.commissionid <> ?";
                $res = $adb->pquery($query, array($transactionId, $record));
            } else {
                $res = $adb->pquery($query, array($transactionId));
            }
            $percent_res = (float) $adb->query_result($res,0,"percents");
            $total = $percent_res + $currentPercent;
            if ($total > 100) {
                $response ="ALERT";
                $message = vtranslate('LBL_SAVE_PERCENT_ERROR', 'Commissions');
            }

            echo json_encode(array("response" => $response, "message" => $message));
        }

        //Никакого окна подтверждения выведено не будет, карточка сохранится как обычно
        return;
    }
}
?>