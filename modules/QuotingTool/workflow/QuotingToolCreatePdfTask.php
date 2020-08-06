<?php

require_once "modules/com_vtiger_workflow/VTEntityCache.inc";
require_once "modules/com_vtiger_workflow/VTWorkflowUtils.php";
require_once "modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc";
require_once "modules/Emails/mail.php";
require_once "modules/QuotingTool/QuotingTool.php";
require_once "include/simplehtmldom/simple_html_dom.php";
class QuotingToolCreatePdfTask extends VTTask
{
    public $executeImmediately = true;
    public function getFieldNames()
    {
        return array("title", "description", "folder", "template");
    }
    public function getFolders()
    {
        $fieldvalue = array();
        $adb = PearDatabase::getInstance();
        $sql = "select foldername,folderid from vtiger_attachmentsfolder order by foldername";
        $res = $adb->pquery($sql, array());
        for ($i = 0; $i < $adb->num_rows($res); $i++) {
            $fid = $adb->query_result($res, $i, "folderid");
            $fname = $adb->query_result($res, $i, "foldername");
            $fieldvalue[$fid] = $fname;
        }
        return $fieldvalue;
    }
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
    public function getAdmin()
    {
        $user = new Users();
        $user->retrieveCurrentUserInfoFromFile(1);
        global $current_user;
        $this->originalUser = $current_user;
        $current_user = $user;
        return $user;
    }
    public function doTask($entity)
    {
        global $current_user;
        global $log;
        global $root_directory;
        global $site_URL;
        global $upload_badext;
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $templateId = $this->template;
        $module = $entity->getModuleName();
        $entityIdDetails = vtws_getIdComponents($entity->getId());
        $entityId = $entityIdDetails[1];
        $adminUser = $this->getAdmin();
        $moduleName = "Emails";
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
        $companyModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $companyfields = array();
        foreach ($companyModel->getFields() as $key => $val) {
            if ($key == "logo") {
                continue;
            }
            $companyfields["\$" . "Vtiger_Company_" . $key . "\$"] = $companyModel->get($key);
        }
        foreach ($varContent as $var) {
            if ($var == "\$custom_user_signature\$") {
                $keys_values["\$custom_user_signature\$"] = nl2br($current_user->signature);
            }
            if (array_key_exists($var, $companyfields)) {
                $keys_values[$var] = $companyfields[$var];
            }
        }
        if (!empty($keys_values)) {
            $pdfContent = $quotingTool->mergeCustomTokens($pdfContent, $keys_values);
            $full_content = base64_encode($pdfContent);
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
        $tabId = Vtiger_Functions::getModuleId($module);
        $recordId = $entityId;
        if ($quotingToolRecord->get("file_name")) {
            global $adb;
            $fileName = $quotingToolRecord->get("file_name");
            if (strpos("\$record_no\$", $quotingToolRecord->get("file_name")) != -1) {
                $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                $resultNo = $recordResult->get($nameFieldModuleNo);
                $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
            }
            if (strpos("\$record_name\$", $quotingToolRecord->get("file_name")) != -1) {
                $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                $fileName = str_replace("\$record_name\$", $resultName, $fileName);
            }
            if (strpos("\$template_name\$", $quotingToolRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$template_name\$", $quotingToolRecord->get("filename"), $fileName);
            }
            $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
            $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
            list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
            $day = date("d", time($date));
            $month = date("m", time($date));
            $year = date("Y", time($date));
            if (strpos("\$day\$", $quotingToolRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$day\$", $day, $fileName);
            }
            if (strpos("\$month\$", $quotingToolRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$month\$", $month, $fileName);
            }
            if (strpos("\$year\$", $quotingToolRecord->get("file_name")) != -1) {
                $fileName = str_replace("\$year\$", $year, $fileName);
            }
        } else {
            $fileName = $quotingToolRecord->get("filename");
        }
        $pdfName = $quotingTool->makeUniqueFile($fileName);
        $pdf = $quotingTool->createPdf($pdfContent, $pdfHeader, $pdfFooter, $pdfName, $quotingToolRecord->get("settings_layout"), $entityId);
        if ($pdf) {
            global $adb;
            global $current_user;
            $results = $adb->pquery("SELECT id FROM vtiger_users WHERE is_admin='on' ORDER BY id ASC limit 1", array());
            $userId = 1;
            if (0 < $adb->num_rows($results)) {
                $userId = $adb->query_result($results, 0, "id");
            }
            $current_user = Users::getInstance("Users");
            $current_user->retrieve_entity_info($userId, "Users");
            $adb = PearDatabase::getInstance();
            $filetype = "application/pdf";
            $attach_id = $this->saveAttachment($userId, $pdfName, $pdf);
            $document = CRMEntity::getInstance("Documents");
            $document->column_fields["notes_title"] = $this->title;
            $document->column_fields["filename"] = basename($pdf);
            $document->column_fields["filestatus"] = 1;
            $document->column_fields["filetype"] = $filetype;
            $document->column_fields["filelocationtype"] = "I";
            $document->column_fields["folderid"] = $this->folder;
            $document->column_fields["filesize"] = filesize($pdf);
            $document->column_fields["assigned_user_id"] = $userId;
            $document->column_fields["fileversion"] = $temFilename;
            $document->column_fields["notecontent"] = $this->description;
            $document->save("Documents");
            $doc_id = $document->id;
            if ($doc_id) {
                $adb->pquery("UPDATE vtiger_crmentity SET source =? WHERE crmid =? ", array("DocumentDesigner", $doc_id));
            }
            if ($entityId) {
                $adb->pquery("INSERT INTO vtiger_senotesrel(crmid,notesid) VALUES(?,?)", array($entityId, $doc_id));
            }
            $adb->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)", array($doc_id, $attach_id));
        }
    }
    public function saveAttachment($userId, $file_name, $oldFilePath)
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
        $params2 = array($current_id, $filename, $newFilePath, $filetype, $upload_file_path);
        $adb->pquery($sql2, $params2);
        return $current_id;
    }
    public function createAttachFile($focus, $name, $path = "storage/QuotingTool/")
    {
        global $adb;
        global $current_user;
        $timestamp = date("Y-m-d H:i:s");
        $ownerid = $focus->column_fields["assigned_user_id"];
        $id = $adb->getUniqueID("vtiger_crmentity");
        $filetype = "application/pdf";
        $sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES(?, ?, ?, ?, ?, ?, ?)";
        $params1 = array($id, $current_user->id, $ownerid, "Emails Attachment", $focus->column_fields["description"], $timestamp, $timestamp);
        $adb->pquery($sql1, $params1);
        $sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) VALUES(?, ?, ?, ?, ?)";
        $params2 = array($id, $name, $focus->column_fields["description"], $filetype, $path);
        $adb->pquery($sql2, $params2);
        $sql3 = "INSERT INTO vtiger_seattachmentsrel VALUES(?,?)";
        $adb->pquery($sql3, array($focus->id, $id));
        return $id;
    }
}

?>