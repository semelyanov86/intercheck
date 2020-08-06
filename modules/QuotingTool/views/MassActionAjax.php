<?php

include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class QuotingTool_EmailPreviewTemplate_View
 */
class QuotingTool_MassActionAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("createPDFAndRecipientLink");        
    }

    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * @param Vtiger_Request $request
     */
    public function createPDFAndRecipientLink(Vtiger_Request $request)
    {
        global $current_user;
        global $site_URL;
        global $application_unique_key;
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $recordId = $request->get("record");
        $templateId = $request->get("template_id");
        $isCreateNewRecord = $request->get("isCreateNewRecord");
        $childModule = $request->get("childModule");
        $recordModel = new QuotingTool_Record_Model();
        $record = $recordModel->getById($templateId);
        $relModule = $record->get("module");
        $quotingTool = new QuotingTool();
        $varContent = $quotingTool->getVarFromString(base64_decode($record->get("content")));
        $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
        $record = $record->decompileRecord($recordId, array("content", "header", "footer", "email_subject", "email_content"), array(), $customFunction);
        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
        $full_content = base64_encode($record->get("content"));
        $transactionId = $transactionRecordModel->saveTransaction(0, $templateId, $record->get("module"), $recordId, NULL, NULL, $full_content, $record->get("description"));
        $transactionRecord = $transactionRecordModel->findById($transactionId);
        $hash = $transactionRecord->get("hash");
        $hash = $hash ? $hash : "";
        $keys_values = array();
        $site = rtrim($site_URL, "/");
        if ($isCreateNewRecord == 1) {
            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash . "&iscreatenewrecord=true&childmodule=" . $childModule;
        } else {
            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
        }
        $tabId = Vtiger_Functions::getModuleId($transactionRecord->get("module"));
        $recordId = $transactionRecord->get("record_id");
        if ($transactionRecord->get("file_name")) {
            global $adb;
            $fileName = $transactionRecord->get("file_name");
            if (strpos("\$record_no\$", $transactionRecord->get("file_name")) != -1) {
                $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                $resultNo = $recordResult->get($nameFieldModuleNo);
                $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
            }
            if (strpos("\$record_name\$", $transactionRecord->get("file_name")) != -1) {
                $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                $fileName = str_replace("\$record_name\$", $resultName, $fileName);
            }
            if (strpos("\$template_name\$", $transactionRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$template_name\$", $transactionRecord->get("filename"), $fileName);
            }
            $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
            $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
            list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
            $day = date("d", $date);
            $month = date("m", $date);
            $year = date("Y", $date);
            if (strpos("\$day\$", $transactionRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$day\$", $day, $fileName);
            }
            if (strpos("\$month\$", $transactionRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$month\$", $month, $fileName);
            }
            if (strpos("\$year\$", $transactionRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$year\$", $year, $fileName);
            }
        } else {
            $fileName = $transactionRecord->get("filename");
        }
        $fileName = $quotingTool->makeUniqueFile($fileName);
        $keys_values = array();
        $compactLink = preg_replace("(^(https?|ftp)://)", "", $link);
        $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $companyfields = array();
        foreach ($companyModel->getFields() as $key => $val) {
            if ($key == "logo") {
                continue;
            }
            $companyfields["\$" . "Vtiger_Company_" . $key . "\$"] = $companyModel->get($key);
        }
        foreach ($varContent as $var) {
            if ($var == "\$custom_proposal_link\$") {
                $keys_values["\$custom_proposal_link\$"] = $compactLink;
            } else {
                if ($var == "\$custom_user_signature\$") {
                    $keys_values["\$custom_user_signature\$"] = nl2br($current_user->signature);
                }
            }
            if (array_key_exists($var, $companyfields)) {
                $keys_values[$var] = $companyfields[$var];
            }
        }
        if (!empty($keys_values)) {
            $record->set("content", $quotingTool->mergeCustomTokens($record->get("content"), $keys_values));
        }
        $pdf = $quotingTool->createPdf($record->get("content"), $record->get("header"), $record->get("footer"), $fileName, $record->get("settings_layout"));
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data["link"] = $link;
        $data["pdf"] = $pdf;
        $response->setResult($data);
        $response->emit();
    }
}

?>