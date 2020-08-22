<?php

class Transactions_MassActionAjax_View extends Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     */
    public function checkPermission(Vtiger_Request $request)
    {
    }
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        global $current_user;
        global $platformUrl;
        $recordId = $request->get('record');
        $recModel = Vtiger_Record_Model::getInstanceById($recordId);
        $platformId = $recModel->get('cf_platform_id');
        if ($platformId) {
            $viewer = $this->getViewer($request);
            $viewer->assign("MODULE_NAME", "Transactions");
            $viewer->assign("PLATFORM_ID",$platformId);
            $viewer->assign('PLATFORM_URL', $platformUrl);
            echo $viewer->view("ExternalPayment.tpl", "Transactions", true);
        } else {
            echo "notShow";
        }
    }
}

?>