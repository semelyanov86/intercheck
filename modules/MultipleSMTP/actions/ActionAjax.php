<?php

class MultipleSMTP_ActionAjax_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("enableModule");
        $this->exposeMethod("checkEnable");
        $this->exposeMethod("updateSequence");
        $this->exposeMethod("mergeTemplates");        
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    public function enableModule(Vtiger_Request $request)
    {
        global $adb;
        $value = $request->get("value");
        $adb->pquery("UPDATE `multiple_smtp_settings` SET `enable`=?", array($value));
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(array("result" => "success"));
        $response->emit();
    }
    public function checkEnable(Vtiger_Request $request)
    {
        global $adb;
        $rs = $adb->pquery("SELECT `enable` FROM `multiple_smtp_settings`;", array());
        $enable = $adb->query_result($rs, 0, "enable");
        if (!Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
            $enable = 0;
        }
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(array("enable" => $enable));
        $response->emit();
    }
    public function updateSequence(Vtiger_Request $request)
    {
        $params = $request->get("params");
        MultipleSMTP_Module_Model::updateSequence($params);
        $response = new Vtiger_Response();
        $response->setResult(array("success" => 1, "message" => vtranslate("Update has been completed", $request->getModule(false))));
        $response->emit();
    }
    public function mergeTemplates(Vtiger_Request $request)
    {
        require_once "modules/com_vtiger_workflow/VTSimpleTemplate.inc";
        global $adb;
        $recordId = $request->get("record");
        $vtSimpleTemplate = new VTSimpleTemplate("");
        $mergeComments = array("lastComment", "last5Comments", "allComments");
        $query = "SELECT * FROM vtiger_emailtemplates WHERE body LIKE \"%\$allComments%\" OR body LIKE \"%\$last5Comments%\" OR body LIKE \"%\$lastComments%\"";
        $res = $adb->query($query);
        $data = array();
        while ($row = $adb->fetchByAssoc($res)) {
            $body = $row["body"];
            foreach ($mergeComments as $fieldName) {
                $mergedContent = $vtSimpleTemplate->getComments("HelpDesk", $fieldName, "x" . $recordId);
                $body = str_replace("\$" . $fieldName, $mergedContent, $body);
            }
            $data[] = array("id" => $row["templateid"], "content" => html_entity_decode($body));
        }
        $response = new Vtiger_Response();
        $response->setResult($data);
        $response->emit();
    }
}

?>