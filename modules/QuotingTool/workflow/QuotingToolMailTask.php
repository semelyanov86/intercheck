<?php

require_once "modules/com_vtiger_workflow/VTEntityCache.inc";
require_once "modules/com_vtiger_workflow/VTWorkflowUtils.php";
require_once "modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc";
require_once "modules/Emails/mail.php";
require_once "modules/QuotingTool/QuotingTool.php";
require_once "include/simplehtmldom/simple_html_dom.php";
/**
 * Class QuotingToolMailTask
 */
class QuotingToolMailTask extends VTTask
{
    /**
     * Sending email takes more time, this should be handled via queue all the time.
     * @var bool
     */
    public $executeImmediately = false;
    /**
     * @return array
     */
    public function getFieldNames()
    {
        return array("subject", "content", "recepient", "emailcc", "emailbcc", "fromEmail", "template", "check_attach_file", "template_language", "insert_template");
    }
    /**
     * @param VTWorkflowEntity $entity
     */
    public function doTask($entity)
    {
        global $current_user;
        global $vtiger_current_version;
        global $site_URL;
        global $adb;
        $util = new VTWorkflowUtils();
        $admin = $util->adminUser();
        $module = $entity->getModuleName();
        $taskContents = Zend_Json::decode($this->getContents($entity));
        $from_email = $taskContents["fromEmail"];
        $from_name = $taskContents["fromName"];
        $to_email = $taskContents["toEmail"];
        $cc = $taskContents["ccEmail"];
        $bcc = $taskContents["bccEmail"];
        $emailSubject = $taskContents["subject"];
        $emailContent = $taskContents["content"];
        if (!empty($to_email)) {
            $entityIdDetails = vtws_getIdComponents($entity->getId());
            $entityId = $entityIdDetails[1];
            $moduleName = "Emails";
            $userId = $current_user->id;
            $recordId = $entityId;
            if ($this->insert_template != NULL) {
                $insertTemplate = $this->insert_template;
                $recordModel = new QuotingTool_Record_Model();
                $record = $recordModel->getById($insertTemplate);
                $quotingTool = new QuotingTool();
                $contentOfTemplate = base64_decode($record->get("content"));
                $varContent = $quotingTool->getVarFromString($contentOfTemplate);
                $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
                $_REQUEST["template_id"] = $insertTemplate;
                $record = $record->decompileRecord($recordId, array("content", "header", "footer", "email_subject", "email_content"), array(), $customFunction);
                $full_content = $record->get("content");
                $tmp_html = str_get_html($full_content);
                foreach ($tmp_html->find("img") as $img) {
                    $json_data_info = $img->getAttribute("data-info");
                    $data_info = json_decode(html_entity_decode($json_data_info));
                    $img_class = $img->getAttribute("class");
                    if ($data_info) {
                        $field_id = $data_info->settings_field_image_fields;
                        if (0 < $field_id) {
                            $field_model = Vtiger_Field_Model::getInstance($field_id);
                            $field_name = $field_model->getName();
                            if ($entityId != "") {
                                $related_record_model = Vtiger_Record_Model::getInstanceById($entityId);
                                if ($field_name == "imagename") {
                                    $image = $related_record_model->getImageDetails();
                                    if (7.1 < $vtiger_current_version) {
                                        $imageUrl = $recordModel->getAttachmentFile($image[0]["id"], $image[0]["name"]);
                                    } else {
                                        $img_path = $image[0]["path"] . "_" . $image[0]["name"];
                                        $imageUrl = $site_URL . "/" . $img_path;
                                    }
                                    $img->setAttribute("src", $imageUrl);
                                } else {
                                    if ($related_record_model->get($field_name) != "") {
                                        $img_path_array = explode("\$\$", $related_record_model->get($field_name));
                                        $img->setAttribute("src", $site_URL . $img_path_array[0]);
                                    } else {
                                        $img->outertext = "";
                                    }
                                }
                            }
                        }
                    }
                }
                $signatureImageIndex = 1;
                $QuotingToolRecordModel = new QuotingTool_Record_Model();
                foreach ($tmp_html->find("img") as $img) {
                    $img_class = $quoting_tool_product_image = $img->getAttribute("class");
                    if ($quoting_tool_product_image == "quoting_tool_product_image") {
                        $product_id = $img->getAttribute("data-productid");
                        if ($product_id) {
                            $productRecordModel = Vtiger_Record_Model::getInstanceById($product_id);
                            if ($productRecordModel) {
                                $image = $productRecordModel->getImageDetails();
                                if (7.1 < $vtiger_current_version) {
                                    $imageUrl = $QuotingToolRecordModel->getAttachmentFile($image[0]["id"], $image[0]["name"]);
                                } else {
                                    $img_path = $image[0]["path"] . "_" . $image[0]["name"];
                                    $imageUrl = $site_URL . "/" . $img_path;
                                }
                                $img->setAttribute("src", $imageUrl);
                            } else {
                                $img->setAttribute("src", "");
                            }
                        } else {
                            $img->setAttribute("src", "");
                        }
                    } else {
                        if ($img_class == "quoting_tool-widget-signature-image" || $img_class == "quoting_tool-widget-secondary_signature-image") {
                            $img->setAttribute("data-image-index", "signatureImageIndex" . $signatureImageIndex);
                            $signatureImageIndex++;
                            if ($img_class == "quoting_tool-widget-secondary_signature-image") {
                                $img->setAttribute("style", "height: 40px; width: 130px; display: none;");
                            }
                        }
                    }
                }
                $full_content = $tmp_html->save();
                preg_match_all("'\\[BARCODE\\|(.*?)\\|BARCODE\\]'si", $full_content, $match);
                if (0 < count($match)) {
                    require_once "modules/QuotingTool/resources/barcode/autoload.php";
                    $full_content = preg_replace_callback("/\\[BARCODE\\|(.+?)\\|BARCODE\\]/", function ($barcode_val) {
                        $array_values = explode("=", $barcode_val[1]);
                        list($method, $field_value) = $array_values;
                        $qt = new QuotingTool();
                        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                        $barcode_png = "<img src=\"data:image/png;base64," . base64_encode($generator->getBarcode($field_value, $qt->barcode_type_code[$method])) . "\" />";
                        return $barcode_png;
                    }, $full_content);
                }
                foreach ($tmp_html->find(".widget__bound") as $container) {
                    $container->outertext = "";
                }
                foreach ($tmp_html->find(".quoting_tool-widget-signature-container") as $container) {
                    $container->outertext = "";
                }
                foreach ($tmp_html->find(".quoting_tool-widget-secondary_signature-container") as $container) {
                    $container->outertext = "";
                }
                foreach ($tmp_html->find("table") as $table) {
                    $tableType = $table->getAttribute("data-table-type");
                    $igoreTable = array("pricing_table", "create_related_record", "pricing_table_idc");
                    $tableConfig = $table->find(".show-config");
                    if (in_array($tableType, $igoreTable) || 0 < count($tableConfig)) {
                        $parentTable = $table->parent->parent->parent;
                        $parentTable->outertext = "";
                    }
                }
                $full_content = $tmp_html->save();
                $quotingToolSettingRecordModel = new QuotingTool_SettingRecord_Model();
                $objSettings = $quotingToolSettingRecordModel->findByTemplateId($insertTemplate);
                $ignoreBorderEmail = $objSettings->get("ignore_border_email");
                if (!$ignoreBorderEmail) {
                    $full_content = "<div style=\"height: auto; margin: 0 auto;width: 680.321px; padding: 16mm 15mm;border: 1px solid #c3c3c3;box-shadow: 0 0 8px rgba(0, 0, 0, 0.07), 0 0 0 1px rgba(0, 0, 0, 0.06); \">" . $full_content . "</div>";
                }
            }
            if ($full_content != "") {
                $emailContent .= $full_content;
            }
            $emailFocus = CRMEntity::getInstance($moduleName);
            $emailFieldValues = array("assigned_user_id" => $userId, "subject" => $emailSubject, "description" => $emailContent, "from_email" => $from_email, "saved_toid" => $to_email, "ccmail" => $cc, "bccmail" => $bcc, "parent_id" => $entityId . "@" . $userId . "|", "email_flag" => "SENT", "activitytype" => $moduleName, "date_start" => date("Y-m-d"), "time_start" => date("H:i:s"), "mode" => "", "id" => "");
            if (version_compare($vtiger_current_version, "7.0.0", "<")) {
                $emailFocus->column_fields = $emailFieldValues;
                $emailFocus->save($moduleName);
                $emailId = $emailFocus->id;
            } else {
                if (!empty($recordId)) {
                    $emailFocus1 = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                    $emailFocus1->set("mode", "edit");
                } else {
                    $emailFocus1 = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $emailFocus1->set("mode", "");
                }
                $emailFocus1->set("assigned_user_id", $userId);
                $emailFocus1->set("subject", $emailSubject);
                $emailFocus1->set("description", $emailContent);
                $emailFocus1->set("from_email", $from_email);
                $emailFocus1->set("saved_toid", $to_email);
                $emailFocus1->set("ccmail", $cc);
                $emailFocus1->set("bccmail", $bcc);
                $emailFocus1->set("parent_id", $entityId . "@" . $userId . "|");
                $emailFocus1->set("email_flag", "SENT");
                $emailFocus1->set("activitytype", $moduleName);
                $emailFocus1->set("date_start", date("Y-m-d"));
                $emailFocus1->set("time_start", date("H:i:s"));
                $emailFocus1->set("mode", "");
                $emailFocus1->set("id", "");
                $emailFocus1->save();
                $emailId = $emailFocus1->getId();
                $emailFocus->id = $emailId;
            }
            $emailFocus->column_fields = $emailFieldValues;
            global $site_URL;
            global $application_unique_key;
            $emailId = $emailFocus->id;
            $emailTracking = vglobal("email_tracking");
            if ($emailTracking == "Yes") {
                $trackURL = (string) $site_URL . "/modules/Emails/actions/TrackAccess.php?record=" . $entityId . "&mailid=" . $emailId . "&app_key=" . $application_unique_key;
                $emailContent = "<img src='" . $trackURL . "' alt='' width='1' height='1'>" . $emailContent;
            }
            $logo = 0;
            if (stripos($emailContent, "<img src=\"cid:logo\" />")) {
                $logo = 1;
            }
            $templates = NULL;
            if (is_array($this->template)) {
                $templates = $this->template;
            } else {
                $templates = array($this->template);
                if ($this->template == "") {
                    $templates = array();
                }
            }
            $status = 0;
            if (0 < count($templates)) {
                foreach ($templates as $templateId) {
                    if ($templateId != "0" && $templateId != "") {
                        $id = NULL;
                        $signature = NULL;
                        $signatureName = NULL;
                        $quotingToolModel = new QuotingTool_Record_Model();
                        $quotingToolRecord = $quotingToolModel->getById($templateId);
                        $quotingTool = new QuotingTool();
                        $description = $quotingToolRecord->get("description");
                        $temFilename = $quotingToolRecord->get("filename");
                        $pdfContent = $quotingToolRecord->get("content");
                        $tempHeader = $quotingToolRecord->get("header");
                        $tempFooter = $quotingToolRecord->get("footer");
                        $pdfContent = $pdfContent ? base64_decode($pdfContent) : "";
                        $varContent = $quotingTool->getVarFromString($pdfContent);
                        if (!empty($varContent)) {
                            $_REQUEST["template_id"] = $templateId;
                            $pdfContent = $quotingTool->parseTokens($pdfContent, $module, $entityId, $quotingToolRecord->get("custom_function"));
                        }
                        $tmp_html = str_get_html($pdfContent);
                        $signatureImageIndex = 1;
                        foreach ($tmp_html->find("img") as $img) {
                            $img_class = $img->getAttribute("class");
                            if ($img_class == "quoting_tool-widget-signature-image" || $img_class == "quoting_tool-widget-secondary_signature-image") {
                                $img->setAttribute("data-image-index", "signatureImageIndex" . $signatureImageIndex);
                                $signatureImageIndex++;
                                if ($img_class == "quoting_tool-widget-secondary_signature-image") {
                                    $img->setAttribute("style", "height: 40px; width: 130px; display: none;");
                                }
                            }
                        }
                        $pdfContent = $tmp_html->save();
                        $full_content = base64_encode($pdfContent);
                        $pdfHeader = $tempHeader ? base64_decode($tempHeader) : "";
                        $pdfFooter = $tempFooter ? base64_decode($tempFooter) : "";
                        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
                        $transactionId = $transactionRecordModel->saveTransaction($id, $templateId, $module, $entityId, $signature, $signatureName, $full_content, $description);
                        $transactionRecord = $transactionRecordModel->findById($transactionId);
                        $hash = $transactionRecord->get("hash");
                        $hash = $hash ? $hash : "";
                        $keys_values = array();
                        $site = rtrim($site_URL, "/");
                        $link = (string) $site . "/modules/QuotingTool/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
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
                            $pdfContent = $quotingTool->mergeCustomTokens($pdfContent, $keys_values);
                            $full_content = base64_encode($pdfContent);
                            $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $module, $entityId, $signature, $signatureName, $full_content, $description);
                        }
                        $varFooter = $quotingTool->getVarFromString($pdfFooter);
                        foreach ($varFooter as $var) {
                            if (array_key_exists($var, $companyfields)) {
                                $keys_values[$var] = $companyfields[$var];
                            }
                        }
                        if (!empty($keys_values)) {
                            $pdfFooter = $quotingTool->mergeCustomTokens($pdfFooter, $keys_values);
                        }
                        $varHeader = $quotingTool->getVarFromString($pdfHeader);
                        foreach ($varHeader as $var) {
                            if (array_key_exists($var, $companyfields)) {
                                $keys_values[$var] = $companyfields[$var];
                            }
                        }
                        if (!empty($keys_values)) {
                            $pdfHeader = $quotingTool->mergeCustomTokens($pdfHeader, $keys_values);
                        }
                        if ($this->check_attach_file != NULL) {
                            $tabId = Vtiger_Functions::getModuleId($moduleName);
                            $recordId = $entityId;
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
                            $attachmentId = $quotingTool->createAttachFile($emailFocus, $pdfName);
                            $pdfName = $attachmentId . "_" . $pdfName;
                            $pdf = $quotingTool->createPdf($pdfContent, $pdfHeader, $pdfFooter, $pdfName, $transactionRecordModel->get("settings_layout"));
                        }
                        $varEmailSubject = $quotingTool->getVarFromString($emailSubject);
                        if (!empty($varEmailSubject)) {
                            $emailSubject = $quotingTool->parseTokens($emailSubject, $module, $entityId, $transactionRecordModel->get("custom_function"));
                            $keys_values = array();
                            $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
                            $companyfields = array();
                            foreach ($companyModel->getFields() as $key => $val) {
                                if ($key == "logo") {
                                    continue;
                                }
                                $companyfields["\$" . "Vtiger_Company_" . $key . "\$"] = $companyModel->get($key);
                            }
                            foreach ($varEmailSubject as $var) {
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
                                $emailSubject = $quotingTool->mergeCustomTokens($emailSubject, $keys_values);
                            }
                        }
                        $varEmailContent = $quotingTool->getVarFromString($emailContent);
                        if (!empty($varEmailContent)) {
                            $_REQUEST["template_id"] = $templateId;
                            $emailContent = $quotingTool->parseTokens($emailContent, $module, $entityId, $transactionRecordModel->get("custom_function"));
                            $keys_values = array();
                            $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
                            $companyfields = array();
                            foreach ($companyModel->getFields() as $key => $val) {
                                if ($key == "logo") {
                                    continue;
                                }
                                $companyfields["\$" . "Vtiger_Company_" . $key . "\$"] = $companyModel->get($key);
                            }
                            foreach ($varEmailContent as $var) {
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
                                $emailContent = $quotingTool->mergeCustomTokens($emailContent, $keys_values);
                            }
                        }
                        $status = send_mail($module, $to_email, $from_name, $from_email, $emailSubject, $emailContent, $cc, $bcc, "all", $emailId, $logo);
                    }
                }
            } else {
                $status = send_mail($module, $to_email, $from_name, $from_email, $emailSubject, $emailContent, $cc, $bcc, "", "", $logo);
            }
            if (!empty($emailId)) {
                $emailFocus->setEmailAccessCountValue($emailId);
            }
            if (!$status) {
                $emailFocus->trash($moduleName, $emailId);
            }
        }
        $util->revertUser();
    }
    /**
     * Function to get contents of this task
     * @param <Object> $entity
     * @return <Array> contents
     */
    public function getContents($entity, $entityCache = false)
    {
        if (!$this->contents) {
            global $adb;
            global $current_user;
            $taskContents = array();
            $entityId = $entity->getId();
            $utils = new VTWorkflowUtils();
            $adminUser = $utils->adminUser();
            if (!$entityCache) {
                $entityCache = new VTEntityCache($adminUser);
            }
            $fromUserId = Users::getActiveAdminId();
            $entityOwnerId = $entity->get("assigned_user_id");
            if ($entityOwnerId) {
                list($moduleId, $fromUserId) = explode("x", $entityOwnerId);
            }
            $ownerEntity = $entityCache->forId($entityOwnerId);
            if ($ownerEntity->getModuleName() === "Groups") {
                list($moduleId, $recordId) = vtws_getIdComponents($entityId);
                $fromUserId = Vtiger_Util_Helper::getCreator($recordId);
            }
            if ($this->fromEmail && !($ownerEntity->getModuleName() === "Groups" && strpos($this->fromEmail, "assigned_user_id : (Users) ") !== false)) {
                $et = new VTEmailRecipientsTemplate($this->fromEmail);
                $fromEmailDetails = $et->render($entityCache, $entityId);
                $con1 = strpos($fromEmailDetails, "&lt;");
                $con2 = strpos($fromEmailDetails, "&gt;");
                $con3 = strpos($fromEmailDetails, "<");
                $con4 = strpos($fromEmailDetails, ">");
                if ($con1 && $con2) {
                    list($fromName, $fromEmail) = explode("&lt;", $fromEmailDetails);
                    list($fromEmail, $rest) = explode("&gt;", $fromEmail);
                } else {
                    if ($con3 && $con4) {
                        list($fromName, $fromEmail) = explode("<", $fromEmailDetails);
                        list($fromEmail, $rest) = explode(">", $fromEmail);
                    } else {
                        $fromName = "";
                        $fromEmail = $fromEmailDetails;
                    }
                }
            } else {
                $userObj = CRMEntity::getInstance("Users");
                $userObj->retrieveCurrentUserInfoFromFile($fromUserId);
                if ($userObj) {
                    $fromEmail = $userObj->email1;
                    $fromName = $userObj->user_name;
                } else {
                    $result = $adb->pquery("SELECT user_name, email1 FROM vtiger_users WHERE id = ?", array($fromUserId));
                    $fromEmail = $adb->query_result($result, 0, "email1");
                    $fromName = $adb->query_result($result, 0, "user_name");
                }
            }
            if (!$fromEmail) {
                $utils->revertUser();
                return false;
            }
            $taskContents["fromEmail"] = $fromEmail;
            $taskContents["fromName"] = $fromName;
            if ($entity->getModuleName() === "Events") {
                $contactId = $entity->get("contact_id");
                if ($contactId) {
                    $contactIds = "";
                    list($wsId, $recordId) = explode("x", $entityId);
                    $webserviceObject = VtigerWebserviceObject::fromName($adb, "Contacts");
                    $result = $adb->pquery("SELECT contactid FROM vtiger_cntactivityrel WHERE activityid = ?", array($recordId));
                    $numOfRows = $adb->num_rows($result);
                    for ($i = 0; $i < $numOfRows; $i++) {
                        $contactIds .= vtws_getId($webserviceObject->getEntityId(), $adb->query_result($result, $i, "contactid")) . ",";
                    }
                }
                $entity->set("contact_id", trim($contactIds, ","));
                $entityCache->cache[$entityId] = $entity;
            }
            $et = new VTEmailRecipientsTemplate($this->recepient);
            $toEmail = $et->render($entityCache, $entityId);
            $ecct = new VTEmailRecipientsTemplate($this->emailcc);
            $ccEmail = $ecct->render($entityCache, $entityId);
            $ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
            $bccEmail = $ebcct->render($entityCache, $entityId);
            if (strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
                $utils->revertUser();
                return false;
            }
            $taskContents["toEmail"] = $toEmail;
            $taskContents["ccEmail"] = $ccEmail;
            $taskContents["bccEmail"] = $bccEmail;
            $st = new VTSimpleTemplate($this->subject);
            $taskContents["subject"] = $st->render($entityCache, $entityId);
            $ct = new VTSimpleTemplate($this->content);
            $taskContents["content"] = $ct->render($entityCache, $entityId);
            $this->contents = $taskContents;
            $utils->revertUser();
        }
        if (is_array($this->contents)) {
            $this->contents = Zend_Json::encode($this->contents);
        }
        return $this->contents;
    }
    /**
     * @param $selected_module
     * @return array
     */
    public function getTemplates($selected_module)
    {
        $options = array();
        $quotingToolRecordModel = new QuotingTool_Record_Model();
        $templates = $quotingToolRecordModel->findByModule($selected_module);
        if ($templates && 0 < count($templates)) {
            foreach ($templates as $t) {
                $options[$t->get("id")] = $t->get("filename");
            }
        }
        return $options;
    }
}

?>