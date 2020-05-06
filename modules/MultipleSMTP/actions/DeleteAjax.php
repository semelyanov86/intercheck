<?php

class MultipleSMTP_DeleteAjax_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function __construct()
    {
        parent::__construct();        
    }
    public function process(Vtiger_Request $request)
    {
        if ($request->get("id") && $request->get("userid")) {
            $adb = PearDatabase::getInstance();
            $adb->pquery("DELETE FROM `vte_multiple_smtp` WHERE (`id`=? AND `userid`=?)", array($request->get("id"), $request->get("userid")));
        }
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(array("userid" => $request->get("userid")));
        $response->emit();
    }
}

?>