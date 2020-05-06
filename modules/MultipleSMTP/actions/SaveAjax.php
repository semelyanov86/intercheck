<?php

class MultipleSMTP_SaveAjax_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function process(Vtiger_Request $request)
    {
        $outgoingServerSettingsModel = MultipleSMTP_OutgoingServer_Model::getInstanceFromId($request->get("id"));
        $outgoingServerSettingsModel->setData($request->getAll());
        $response = new Vtiger_Response();
        try {
            $id = $outgoingServerSettingsModel->save($request);
            $data = $outgoingServerSettingsModel->getData();
            $response->setResult($data);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
}

?>