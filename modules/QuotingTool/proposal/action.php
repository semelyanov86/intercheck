<?php

chdir(dirname(__FILE__) . "/../../..");
require_once "config.inc.php";
require_once "include/utils/utils.php";
require_once "includes/Loader.php";
vimport("includes.runtime.EntryPoint");
require_once "modules/Users/Users.php";
include "modules/QuotingTool/QuotingTool.php";
global $adb;
global $current_user;
$adb = PearDatabase::getInstance();
$current_user = new Users();
$activeAdmin = $current_user->getActiveAdminUser();
$current_user->retrieve_entity_info($activeAdmin->id, "Users");
$action = isset($_REQUEST["_action"]) ? $_REQUEST["_action"] : NULL;
if ($action) {
    switch ($action) {
        case "submit":
            submit();
            break;
        case "download_pdf":
            downloadPdf();
            break;
        case "get_picklist_values":
            get_picklist_values();
            break;
        case "get_currency_values":
            get_currency_values();
            break;
        case "an_paid":
            an_paid();
            break;
        case "createAuthNetProfile":
            createAuthNetProfile();
            break;
        default:
            break;
    }
}
/**
 * Fn - submit
 */
function submit()
{
    global $current_user;
    global $application_unique_key;
    global $site_URL;
    $response = new Vtiger_Response();
    $response->setEmitType(Vtiger_Response::$EMIT_JSON);
    $quotingTool = new QuotingTool();
    $record = $_REQUEST["record"];
    $status = $_REQUEST["status"];
    $status_text = $_REQUEST["status_text"];
    $sign_to = $_REQUEST["sign_to"];
    $signature = $_REQUEST["signature"];
    $signatureName = $_REQUEST["signature_name"];
    $initialsName = $_REQUEST["initials_primary"];
    $titleSignature = $_REQUEST["title_signature_primary"];
    $isDrawSignature = $_REQUEST["is_draw_signature"];
    $fullContent = $_REQUEST["content"];
    $description = $_REQUEST["description_transaction"];
    $customMappingFields = $_REQUEST["custom_mapping_fields"];
    $childModule = $_REQUEST["child_module"];
    $isCreateNewRecord = $_REQUEST["is_create_new_record"];
    $formCreateNewRecord = $_REQUEST["form_create_record"];
    $primaryModule = $_REQUEST["module"];
    $dataFields = $_REQUEST["data-fields"];
    $dataFields = json_decode($dataFields);
    $linkModule = $dataFields->settings->link_module;
    $parentFieldName = getParentField($primaryModule, $linkModule);
    $parentFieldId = $_REQUEST["record_id"];
    $itemFields = $dataFields->settings->item_fields;
    $customerComment = $_REQUEST["customer_comment"];
    $countData = count($_REQUEST[$itemFields[0]->name]);
    $timestamp = time();
    if (empty($sign_to)) {
        $sign_to = "PRIMARY";
    }
    $sign_to = strtoupper($sign_to);
    $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
    if ($formCreateNewRecord != "true") {
        $success1 = $transactionRecordModel->updateSignature($record, $signature, $signatureName, $fullContent, $description, $sign_to, $initialsName, $titleSignature, $isDrawSignature);
        $success2 = $transactionRecordModel->changeStatus($record, $status, $sign_to);
    }
    $transactionRecord = $transactionRecordModel->findById($record);
    if (!$transactionRecord) {
        $response->setError(200, vtranslate("LBL_INVALID_DOCUMENT", "QuotingTool"));
        return $response->emit();
    }
    $refId = $transactionRecord->get("record_id");
    $quotingToolRecordModel = new QuotingTool_Record_Model();
    $templateRecord = $quotingToolRecordModel->getById($transactionRecord->get("template_id"));
    $mappingFields = array();
    $tempMappingFields = $templateRecord->get("mapping_fields");
    if ($tempMappingFields) {
        $mappingFields = json_decode(htmlspecialchars_decode($tempMappingFields));
    }
    $recordChildModue = "";
    if ($customMappingFields) {
        $tmpCustomMappingFields = json_decode(htmlspecialchars_decode($customMappingFields));
        foreach ($tmpCustomMappingFields as $recordId => $fieldMapping) {
            $mappingFields2 = array();
            foreach ($fieldMapping as $fieldMappingId => $fieldMappingDetail) {
                $fieldMappingValue = $fieldMappingDetail->value;
                switch ($fieldMappingDetail->datatype) {
                    case "date":
                        $fieldMappingValue = Vtiger_Date_UIType::getDBInsertedValue($fieldMappingValue);
                        break;
                    case "time":
                        $fieldMappingValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldMappingValue);
                        break;
                    case "currency":
                        $fieldMappingValue = CurrencyField::convertToDBFormat($fieldMappingValue);
                        break;
                    default:
                        break;
                }
                $objMappingField2 = array("selected-field" => $fieldMappingId, "selected-value" => $fieldMappingValue, "type" => 1);
                $objMappingField2 = (object) $objMappingField2;
                $mappingFields2[] = $objMappingField2;
            }
            if ($formCreateNewRecord == true) {
                $recordId = 0;
            }
            $recordChildModue = mappingData($recordId, $mappingFields2, $status, $isCreateNewRecord, $childModule, $primaryModule);
        }
    }
    if (0 < count($mappingFields) && $isCreateNewRecord != true) {
        mappingData($refId, $mappingFields, $status, $isCreateNewRecord, $childModule, $primaryModule);
    }
    if ($formCreateNewRecord == true) {
        $parentFieldId = $recordChildModue;
    }
    if ($status != -1) {
        for ($i = 1; $i < $countData; $i++) {
            $recordModel = Vtiger_Record_Model::getCleanInstance($linkModule);
            $count = 0;
            foreach ($itemFields as $key => $value) {
                if ($countData < count($_REQUEST[$value->name])) {
                    $_REQUEST[$value->name] = $_REQUEST["multipicklistValue"];
                    if ($_REQUEST[$value->name][$i] == "" || $_REQUEST[$value->name][$i] == "Select an Option") {
                        $count++;
                    }
                    $_REQUEST[$value->name][$i] = str_replace(",", " |##| ", $_REQUEST[$value->name][$i]);
                } else {
                    if ($_REQUEST[$value->name][$i] == "" || $_REQUEST[$value->name][$i] == "Select an Option") {
                        $count++;
                    }
                }
                $recordModel->set($value->name, $_REQUEST[$value->name][$i]);
            }
            if ($parentFieldName) {
                $recordModel->set($parentFieldName, $parentFieldId);
                $recordModel->set("mode", "");
                if ($count != count($itemFields)) {
                    $relRecordId = $recordModel->save();
                }
            } else {
                $recordModel->set("mode", "");
                if ($count != count($itemFields)) {
                    $recordModel->save();
                    $relRecordId = $recordModel->getId();
                }
                $primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
                $linkModuleModel = Vtiger_Module_Model::getInstance($linkModule);
                $relationModel = Vtiger_Relation_Model::getInstance($primaryModuleModel, $linkModuleModel);
                if ($relationModel) {
                    $relationModel->addRelation($parentFieldId, $relRecordId);
                }
            }
        }
    }
    $temFilename = $templateRecord->get("filename");
    $tempHeader = $templateRecord->get("header");
    $tempFooter = $templateRecord->get("footer");
    $pdfContent = $fullContent ? base64_decode($fullContent) : "";
    $pdfHeader = $tempHeader ? base64_decode($tempHeader) : "";
    $pdfFooter = $tempFooter ? base64_decode($tempFooter) : "";
    $tabId = Vtiger_Functions::getModuleId($transactionRecord->get("module"));
    $recordId = $transactionRecord->get("record_id");
    if ($transactionRecord->get("file_name") && $recordId != 0) {
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
        $day = date("d", time($date));
        $month = date("m", time($date));
        $year = date("Y", time($date));
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
    $pdfName = $quotingTool->makeUniqueFile($fileName);
    $pdfName = str_replace(".pdf", "", $pdfName);
    $pdfName = preg_replace("/[^A-Za-z0-9]/", "_", $pdfName) . ".pdf";
    $pdf = $quotingTool->createPdf($pdfContent, $pdfHeader, $pdfFooter, $pdfName, $templateRecord->get("settings_layout"), $refId);
    if ($pdf) {
        global $adb;
        global $current_user;
        global $HELPDESK_SUPPORT_EMAIL_ID;
        global $HELPDESK_SUPPORT_NAME;
        global $site_URL;
        $results = $adb->pquery("SELECT id FROM vtiger_users WHERE is_admin='on' ORDER BY id ASC limit 1", array());
        if (0 < $adb->num_rows($results)) {
            $userId = $adb->query_result($results, 0, "id");
        }
        $current_user = Users::getInstance("Users");
        $current_user->retrieve_entity_info($userId, "Users");
        createDocument($userId, $pdfName, $pdf, $parentFieldId);
    }
    switch ($status) {
        case 1:
            $status_text = "Accept and Sign";
            break;
        case -1:
            $status_text = "Decline";
            break;
    }
    if ($sign_to == "PRIMARY") {
        $newSignedRecord = array("signature" => $signature, "signature_name" => $signatureName, "signature_date" => date("Y-m-d", $timestamp), "cf_signature_time" => date("H:i:s", $timestamp), "filename" => $pdf, "signedrecord_status" => $status_text, "related_to" => $refId, "customer_comment" => $customerComment);
    } else {
        $newSignedRecord = array("secondary_signature" => $signature, "secondary_signature_name" => $signatureName, "secondary_signature_date" => date("Y-m-d", $timestamp), "cf_secondary_signature_time" => date("H:i:s", $timestamp), "secondary_filename" => $pdf, "secondary_signedrecord_status" => $status_text, "related_to" => $refId, "customer_comment" => $customerComment);
    }
    $signedrecordId = isset($_REQUEST["signedrecord_id"]) && $_REQUEST["signedrecord_id"] ? intval($_REQUEST["signedrecord_id"]) : 0;
    if ($signedrecordId) {
        $newSignedRecord["signedrecord_type"] = SignedRecord_Record_Model::TYPE_SIGNED;
    }
    $newSignedRecord["transactionid"] = $record;
    saveSignedRecord($signedrecordId, $newSignedRecord);
    if ($signedrecordId) {
        $signedRecordModel = Vtiger_Record_Model::getInstanceById($signedrecordId);
        $listEmails = json_decode(html_entity_decode($signedRecordModel->get("signedrecord_emails1")));
        $sigStatus = $signedRecordModel->get("signedrecord_type");
        $quotingToolSettingRecordModel = new QuotingTool_SettingRecord_Model();
        $objSettings = $quotingToolSettingRecordModel->findByTemplateId($transactionRecord->get("template_id"));
        $email_signed = $objSettings->get("email_signed");
        if ($listEmails != "" && $email_signed == "1" && $sigStatus == "Signed" && $signatureName != "" && $pdf != "") {
            $fromEmail = html_entity_decode($objSettings->get("email_from_copy"), ENT_QUOTES);
            $strBccEmails = html_entity_decode($objSettings->get("email_bcc_copy"), ENT_QUOTES);
            $emailSubject = html_entity_decode($objSettings->get("email_subject_copy"), ENT_QUOTES);
            $emailContent = nl2br(html_entity_decode($objSettings->get("email_body_copy"), ENT_QUOTES));
            $ccEmails = "";
            $sentEmails = array();
            $counter = 0;
            $arrBccEmails = explode(",", trim($strBccEmails));
            $bccEmails = array();
            foreach ($arrBccEmails as $bcc) {
                $bccEmails[] = $quotingTool->getEmailFromString($bcc);
            }
            foreach ($listEmails as $relatedRecord => $emailList) {
                foreach ($emailList as $email) {
                    if (in_array($email, $sentEmails) || $email == "") {
                        continue;
                    }
                    $sentEmails[] = $email;
                    $entityId = $refId;
                    $cc = implode(",", $ccEmails);
                    $bcc = implode(",", $bccEmails);
                    $emailModuleName = "Emails";
                    $userId = $current_user->id;
                    $emailFocus = CRMEntity::getInstance($emailModuleName);
                    $emailFieldValues = array("assigned_user_id" => $userId, "subject" => $emailSubject, "description" => $emailContent, "from_email" => $fromEmail, "saved_toid" => $email, "ccmail" => $cc, "bccmail" => $bcc, "parent_id" => $entityId . "@" . $userId . "|", "email_flag" => "SENT", "activitytype" => $emailModuleName, "date_start" => date("Y-m-d"), "time_start" => date("H:i:s"), "mode" => "", "signature" => "", "id" => "", "documentids" => "");
                    if (!empty($refId)) {
                        $emailFocus1 = Vtiger_Record_Model::getInstanceById($refId, $emailModuleName);
                        $emailFocus1->set("mode", "edit");
                    } else {
                        $emailFocus1 = Vtiger_Record_Model::getCleanInstance($emailModuleName);
                        $emailFocus1->set("mode", "");
                    }
                    $emailFocus1->set("assigned_user_id", $userId);
                    $emailFocus1->set("subject", $emailSubject);
                    $emailFocus1->set("description", $emailContent);
                    $emailFocus1->set("from_email", $fromEmail);
                    $emailFocus1->set("saved_toid", $email);
                    $emailFocus1->set("ccmail", $cc);
                    $emailFocus1->set("bccmail", $bcc);
                    $emailFocus1->set("parent_id", $entityId . "@" . $userId . "|");
                    $emailFocus1->set("email_flag", "SENT");
                    $emailFocus1->set("activitytype", $emailModuleName);
                    $emailFocus1->set("date_start", date("Y-m-d"));
                    $emailFocus1->set("time_start", date("H:i:s"));
                    $emailFocus1->set("signature", $signature);
                    $emailFocus1->set("documentids", "");
                    $emailFocus1->set("mode", "");
                    $emailFocus1->set("id", "");
                    $emailFocus1->save();
                    $emailId = $emailFocus1->getId();
                    $emailFocus->id = $emailId;
                    $emailFocus->column_fields = $emailFieldValues;
                    if ($emailId) {
                        $trackURL = (string) $site_URL . "/modules/Emails/TrackAccess.php?record=" . $entityId . "&mailid=" . $emailId . "&app_key=" . $application_unique_key;
                        $emailContent = "<img src='" . $trackURL . "' alt='' width='1' height='1'>" . $emailContent;
                        $logo = 0;
                        if (stripos($emailContent, "<img src=\"cid:logo\" />")) {
                            $logo = 1;
                        }
                        $documentRes = $adb->pquery("SELECT vtiger_attachments.attachmentsid FROM vtiger_senotesrel\r\n\t\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?\r\n\t\t\t\t\t\tINNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid\r\n\t\t\t\t\t\tINNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid\r\n\t\t\t\t\t\tINNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid\r\n\t\t\t\t\t\tWHERE vtiger_crmentity.deleted = 0", array($emailId));
                        $numOfRows = $adb->num_rows($documentRes);
                        if ($numOfRows) {
                            for ($i = 0; $i < $numOfRows; $i++) {
                                $sql_rel = "insert into vtiger_seattachmentsrel values(?,?)";
                                $adb->pquery($sql_rel, array($emailId, $adb->query_result($documentRes, $i, "attachmentsid")));
                            }
                        }
                        $attachmentId = $quotingTool->createAttachFile($emailFocus, $pdfName);
                        $fileName = $attachmentId . "_" . $pdfName;
                        $pdf = $quotingTool->createPdf($pdfContent, $pdfHeader, $pdfFooter, $fileName, $templateRecord->get("settings_layout"), $refId);
                        $fromName = $current_user->first_name . " " . $current_user->last_name;
                        $fromName = (string) $fromName . " (" . $fromEmail . ")";
                        $result = send_mail($primaryModule, $email, $fromName, $fromEmail, $emailSubject, $emailContent, $cc, $bcc, "all", $emailId, $logo);
                        $emailFocus->setEmailAccessCountValue($emailId);
                        if (!$result) {
                            $emailFocus->trash($emailModuleName, $emailId);
                        } else {
                            $counter += $result;
                            if ($relatedRecord != $entityId) {
                                global $adb;
                                $mysql = "insert into vtiger_seactivityrel values(?,?)";
                                $adb->pquery($mysql, array($relatedRecord, $emailId));
                            }
                        }
                    }
                }
            }
            if (!$counter) {
                $errorMessage = vtranslate("ERROR_UNABLE_TO_SEND_EMAIL", $primaryModule);
                $response->setError(200, $errorMessage);
                $response->emit();
                exit;
            }
        }
    }
    $make_an_profile = (int) $_POST["an_payment_block_make_profile"];
    if ($make_an_profile == 1 || $make_an_profile == 2) {
        $an_result = createAuthNetProfile();
        if (strpos($an_result, "ERROR") !== false) {
            $response->setError(200, vtranslate($an_result, "ANCustomers"));
        } else {
            $response->setResult($an_result);
        }
    }
    return $response->emit();
}
/**
 * @param $refId
 * @param $tempMappingFields
 * @param $status
 */
function mappingData($refId, $tempMappingFields, $status, $isCreateNewRecord = false, $childModule, $primaryModule)
{
    $mappingFields = array();
    foreach ($tempMappingFields as $k => $field) {
        $vField = Vtiger_Field_Model::getInstance($field->_obfuscated_73656C65637465642D6669656C64_);
        if ($field->type == $status && $vField) {
            $mappingFields[$vField->get("name")] = $field->_obfuscated_73656C65637465642D76616C7565_;
        }
    }
    if ($isCreateNewRecord == true && $status != 0 && $status != -1) {
        $mappingModel = Vtiger_Record_Model::getCleanInstance($childModule);
        foreach ($mappingFields as $field => $value) {
            $mappingModel->set($field, $value);
        }
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($refId);
        $parentModuleName = $parentRecordModel->getModule()->getName();
        $parentField = getParentField($parentModuleName, $childModule);
        if ($parentField != "") {
            $mappingModel->set($parentField, $refId);
        }
        if (count($mappingFields) == 0) {
            return NULL;
        }
        $mappingModel->save();
        return $mappingModel->getId();
    } else {
        if (($refId == 0 || !isRecordExists($refId)) && $status != 0 && $status != -1) {
            $mappingModel = Vtiger_Record_Model::getCleanInstance($primaryModule);
        } else {
            if ($refId == 0) {
                return NULL;
            }
            $mappingModel = Vtiger_Record_Model::getInstanceById($refId);
            $mappingModel->set("id", $refId);
            $mappingModel->set("mode", "edit");
        }
        foreach ($mappingFields as $field => $value) {
            $mappingModel->set($field, $value);
        }
        if (count($mappingFields) == 0) {
            return NULL;
        }
        $mappingModel->save();
        return $mappingModel->getId();
    }
}
function getParentField($parentModule, $childModule)
{
    global $adb;
    $uitypes = array();
    switch ($parentModule) {
        case "Accounts":
            $uitypes = array("51", "73", "68");
            break;
        case "Contacts":
            $uitypes = array("57", "68");
            break;
        case "Campaigns":
            $uitypes = array("58");
            break;
        case "Products":
            $uitypes = array("59");
            break;
        case "Vendors":
            $uitypes = array("75", "81");
            break;
        case "Potentials":
            $uitypes = array("76");
            break;
        case "Quotes":
            $uitypes = array("78");
            break;
        case "SalesOrder":
            $uitypes = array("80");
            break;
    }
    if (empty($uitypes)) {
        $uitypes = array("10");
    }
    $queryChild = "SELECT * FROM (\r\n            SELECT vtiger_tab.tabid, vtiger_tab.`name` as relmodule , vtiger_field.`fieldname`\r\n            FROM `vtiger_field`\r\n            INNER JOIN vtiger_tab ON vtiger_field.tabid=vtiger_tab.tabid\r\n            WHERE vtiger_field.presence <> 1 AND uitype IN (" . generateQuestionMarks($uitypes) . ")\r\n            UNION\r\n            SELECT vtiger_field.tabid,vtiger_fieldmodulerel.module as relmodule, vtiger_field.`fieldname`\r\n            FROM vtiger_fieldmodulerel\r\n            INNER JOIN vtiger_field ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid\r\n            WHERE vtiger_field.presence <> 1 AND uitype = 10 AND relmodule = ?\r\n            ) as temp\r\n            WHERE `relmodule` NOT IN ('Webmails', 'SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter','PBXManager')\r\n             AND `relmodule` =?\r\n            ";
    $rs = $adb->pquery($queryChild, array($uitypes, $parentModule, $childModule));
    $parentField = "";
    if (0 < $adb->num_rows($rs)) {
        if ($row = $adb->fetchByAssoc($rs)) {
            $parentField = $row["fieldname"];
        }
        return $parentField;
    }
    return "";
}
/** Fn - createSignedRecord
 * @param int $id
 * @param array $data
 */
function saveSignedRecord($id, $data)
{
    $signedRecordModel = NULL;
    if (empty($id)) {
        $transactionid = $data["transactionid"];
        if (!empty($transactionid)) {
            global $adb;
            $sql = "SELECT signedrecordid FROM vtiger_signedrecordcf WHERE transactionid=? LIMIT 0, 1";
            $rs = $adb->pquery($sql, array($transactionid));
            if (0 < $adb->num_rows($rs)) {
                $id = $adb->query_result($rs, 0, "signedrecordid");
            }
        }
    }
    if ($id) {
        $signedRecordModel = Vtiger_Record_Model::getInstanceById($id);
        $signedRecordModel->set("id", $id);
        $signedRecordModel->set("mode", "edit");
    } else {
        $signedRecordModel = Vtiger_Record_Model::getCleanInstance("SignedRecord");
    }
    foreach ($data as $field => $value) {
        $signedRecordModel->set($field, $value);
    }
    $signedRecordModel->save();
    $signedRecordId = $signedRecordModel->getId();
    if ($signedRecordModel->get("related_to") != "" && $signedRecordModel->get("related_to") != 0) {
        $parentModuleModel = Vtiger_Record_Model::getInstanceById($signedRecordModel->get("related_to"));
        $parentModuleName = $parentModuleModel->getModuleName();
        $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
        $relModuleModel = Vtiger_Module_Model::getInstance("SignedRecord");
        $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relModuleModel);
        $relationModel->addRelation($signedRecordModel->get("related_to"), $signedRecordId);
    }
    return $signedRecordId;
}
/**
 * Fn - downloadPdf
 */
function downloadPdf()
{
    global $current_user;
    global $site_URL;
    $quotingTool = new QuotingTool();
    $transactionId = $_REQUEST["record"];
    $entityId = $_REQUEST["record_id"];
    $moduleName = $_REQUEST["module"];
    $name = $_REQUEST["name"];
    $pdfContent = $_REQUEST["content"] ? base64_decode($_REQUEST["content"]) : "";
    $pdfHeader = $_REQUEST["header"] ? base64_decode($_REQUEST["header"]) : "";
    $pdfFooter = $_REQUEST["footer"] ? base64_decode($_REQUEST["footer"]) : "";
    $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
    $transactionRecord = $transactionRecordModel->findById($transactionId);
    $hash = $transactionRecord->get("hash");
    $hash = $hash ? $hash : "";
    $keys_values = array();
    $site = rtrim($site_URL, "/");
    $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
    $compactLink = preg_replace("(^(https?|ftp)://)", "", $link);
    $_REQUEST["template_id"] = $transactionRecord->get("template_id");
    $pdfHeader = $quotingTool->parseTokens($pdfHeader, $moduleName, $entityId, $transactionRecord->get("custom_function"));
    $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
    $companyfields = array();
    foreach ($companyModel->getFields() as $key => $val) {
        if ($key == "logo") {
            continue;
        }
        $companyfields["\$" . "Vtiger_Company_" . $key . "\$"] = $companyModel->get($key);
    }
    $varFooter = $quotingTool->getVarFromString($pdfFooter);
    foreach ($varFooter as $var) {
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
        $pdfFooter = $quotingTool->mergeCustomTokens($pdfFooter, $keys_values);
    }
    $pdfFooter = $quotingTool->parseTokens($pdfFooter, $moduleName, $entityId, $transactionRecord->get("custom_function"));
    $varHeader = $quotingTool->getVarFromString($pdfHeader);
    foreach ($varHeader as $var) {
        if (array_key_exists($var, $companyfields)) {
            $keys_values[$var] = $companyfields[$var];
        }
    }
    if (!empty($keys_values)) {
        $pdfHeader = $quotingTool->mergeCustomTokens($pdfHeader, $keys_values);
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
        $day = date("d", time($date));
        $month = date("m", time($date));
        $year = date("Y", time($date));
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
    $pdfName = $quotingTool->makeUniqueFile($fileName);
    $pdf = $quotingTool->createPdf($pdfContent, $pdfHeader, $pdfFooter, $pdfName, $transactionRecord->get("settings_layout"));
    $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
    $name = str_replace(".pdf", "", $fileName);
    $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
    $name = str_replace(" ", "_", $name);
    $fileName = str_replace("\$", "_", $name);
    $pdfName = trim($fileName);
    header("Content-Type: application/octet-stream");
    header("Content-disposition: attachment; filename=\"" . $pdfName . ".pdf\"");
    header("Content-Length: " . filesize($pdf));
    readfile($pdf);
    print readfile($pdf);
    exit;
}
/**
 * Fn - get_picklist_values
 */
function get_picklist_values()
{
    $fieldModules = isset($_REQUEST["fields"]) ? $_REQUEST["fields"] : NULL;
    $response = new Vtiger_Response();
    $response->setEmitType(Vtiger_Response::$EMIT_JSON);
    $data = array();
    if (!$fieldModules) {
        $response->setResult($data);
        $response->emit();
    }
    foreach ($fieldModules as $moduleName => $fields) {
        $module = Vtiger_Module_Model::getInstance($moduleName);
        foreach ($fields as $fieldName => $fieldValue) {
            $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $module);
            if (!$fieldModel) {
                continue;
            }
            $data[$moduleName][$fieldName] = array();
            $datatype = $fieldModel->getFieldDataType();
            if ($datatype == "picklist") {
                $data[$moduleName][$fieldName][-1] = vtranslate("Select an Option");
            }
            $data[$moduleName][$fieldName] = array_merge($data[$moduleName][$fieldName], $fieldModel->getPicklistValues());
        }
    }
    $response->setResult($data);
    $response->emit();
}
/**
 * Fn - get_currency_values
 */
function get_currency_values()
{
    $fieldModules = isset($_REQUEST["fields"]) ? $_REQUEST["fields"] : NULL;
    $response = new Vtiger_Response();
    $response->setEmitType(Vtiger_Response::$EMIT_JSON);
    $data = array();
    if (!$fieldModules) {
        $response->setResult($data);
        $response->emit();
    }
    foreach ($fieldModules as $moduleName => $fields) {
        $module = Vtiger_Module_Model::getInstance($moduleName);
        foreach ($fields as $fieldName => $fieldValue) {
            $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $module);
            $data[$moduleName][$fieldName] = $fieldModel->getCurrencyList();
            foreach ($data[$moduleName][$fieldName] as $k => $cf) {
                $data[$moduleName][$fieldName][$k] = vtranslate($cf, $moduleName);
            }
        }
    }
    $response->setResult($data);
    $response->emit();
}
/**
 * Fn - submit payment to Authorize.Net
 */
function an_paid()
{
    require_once "modules/ANCustomers/libs/InvoiceWidget/QuotingTool.php";
    $response = new Vtiger_Response();
    $response->setEmitType(Vtiger_Response::$EMIT_JSON);
    $anQuotingTool = new ANQuotingTool();
    $paid_status = $anQuotingTool->ANPaid();
    $response->setResult($paid_status);
    $response->emit();
}
function createAuthNetProfile()
{
    require_once "modules/ANCustomers/libs/InvoiceWidget/QuotingTool.php";
    $anQuotingTool = new ANQuotingTool();
    $result = $anQuotingTool->createAuthNetProfile();
    return $result;
}
function createDocument($userId, $file_name, $oldFilePath, $parentFieldId)
{
    global $upload_badext;
    $adb = PearDatabase::getInstance();
    $currentUserModel = Users_Record_Model::getCurrentUserModel();
    $binFile = sanitizeUploadFileName($file_name, $upload_badext);
    $current_id = $adb->getUniqueID("vtiger_crmentity");
    $filename = ltrim(basename(" " . $binFile));
    $filetype = "application/pdf";
    $filesize = "";
    $upload_file_path = decideFilePath();
    $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
    copy($oldFilePath, $newFilePath);
    $attach_id = saveAttachment($userId, $file_name, $oldFilePath);
    $document = CRMEntity::getInstance("Documents");
    $document->column_fields["notes_title"] = $file_name;
    $document->column_fields["filename"] = basename($oldFilePath);
    $document->column_fields["filestatus"] = 1;
    $document->column_fields["filetype"] = $filetype;
    $document->column_fields["filelocationtype"] = "I";
    $document->column_fields["folderid"] = "";
    $document->column_fields["filesize"] = filesize($oldFilePath);
    $document->column_fields["assigned_user_id"] = $userId;
    $document->save("Documents");
    $doc_id = $document->id;
    if ($parentFieldId) {
        $adb->pquery("INSERT INTO vtiger_senotesrel(crmid,notesid) VALUES(?,?)", array($parentFieldId, $doc_id));
    }
    $adb->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)", array($doc_id, $attach_id));
    return false;
}
function saveAttachment($userId, $file_name, $oldFilePath)
{
    global $upload_badext;
    $adb = PearDatabase::getInstance();
    $date_var = date("Y-m-d H:i:s");
    $binFile = sanitizeUploadFileName($file_name, $upload_badext);
    $current_id = $adb->getUniqueID("vtiger_crmentity");
    $filename = ltrim(basename(" " . $binFile));
    $filetype = "application/pdf";
    $filesize = "";
    $upload_file_path = decideFilePath();
    $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
    copy($oldFilePath, $newFilePath);
    $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
    $params1 = array($current_id, $userId, $userId, "Documents Attachment", "", $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
    $adb->pquery($sql1, $params1);
    $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
    $params2 = array($current_id, $filename, "", $filetype, $upload_file_path);
    $result = $adb->pquery($sql2, $params2);
    return $current_id;
}

?>