<?php

include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class QuotingTool_ActionAjax_Action
 */
class QuotingTool_ActionAjax_Action extends Vtiger_Action_Controller
{
    /**
     * @constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("save");
        $this->exposeMethod("save_setting");
        $this->exposeMethod("delete");
        $this->exposeMethod("getTemplate");
        $this->exposeMethod("getHistories");
        $this->exposeMethod("getHistory");
        $this->exposeMethod("removeHistories");
        $this->exposeMethod("getAllRecord");
        $this->exposeMethod("exportTemplateQuotingTool");
        $this->exposeMethod("importTemplate");
        $this->exposeMethod("ImportDefaultTemplates");
        $this->exposeMethod("CreateNewProposal");
        $this->exposeMethod("save_proposal");
        $this->exposeMethod("checkMPDF");
        $this->exposeMethod("changeSignTo");
        $this->exposeMethod("getLinkHtml");        
    }
    /**
     * @param Vtiger_Request $request
     * @return bool
     */
    public function checkPermission(Vtiger_Request $request)
    {
    }
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * @param Vtiger_Request $request
     */
    public function save(Vtiger_Request $request)
    {
        global $adb;
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $params = array();
        $recordModel = QuotingTool_Record_Model::getCleanInstance($module);
        $record = $request->get("record");
        $isChangeModule = $request->get("change_module");
        if ($isChangeModule == "true") {
            QuotingTool_Record_Model::checkRemoveLink($record);
        }
        if ($request->has("filename")) {
            $fileName = str_replace(array("\\", "/", ":", "*", "?", "\"", "<", ">", "|"), " ", $request->get("filename"));
            $params["filename"] = $fileName;
        }
        if ($request->has("primary_module")) {
            $params["module"] = $request->get("primary_module");
        }
        if ($request->has("body")) {
            $params["body"] = $request->get("body");
        }
        if ($request->has("header")) {
            $params["header"] = $request->get("header");
        }
        if ($request->has("content")) {
            $params["content"] = $request->get("content");
        }
        if ($request->has("footer")) {
            $params["footer"] = $request->get("footer");
        }
        if ($request->has("description")) {
            $params["description"] = $request->get("description");
        }
        if ($request->has("expire_in_days")) {
            $params["expire_in_days"] = $request->get("expire_in_days");
        }
        if ($request->has("anwidget")) {
            if ($request->get("anwidget") == "true") {
                $params["anwidget"] = 1;
            } else {
                $params["anwidget"] = 0;
            }
        }
        if ($request->has("anblock")) {
            if ($request->get("anblock") == "true") {
                $params["anblock"] = 1;
            } else {
                $params["anblock"] = 0;
            }
        }
        if ($request->has("createnewrecords")) {
            if ($request->get("createnewrecords") == "true") {
                $params["createnewrecords"] = 1;
            } else {
                $params["createnewrecords"] = 0;
            }
        }
        if ($request->has("linkproposal")) {
            $params["linkproposal"] = $request->get("linkproposal");
        }
        if ($request->has("email_subject")) {
            $params["email_subject"] = $request->get("email_subject");
        }
        if ($request->has("email_content")) {
            $params["email_content"] = $request->get("email_content");
        }
        if ($request->has("mapping_fields")) {
            $params["mapping_fields"] = $request->get("mapping_fields") ? QuotingToolUtils::jsonUnescapedSlashes(json_encode($request->get("mapping_fields"), JSON_FORCE_OBJECT)) : NULL;
        }
        if ($request->has("attachments")) {
            $params["attachments"] = $request->get("attachments") ? QuotingToolUtils::jsonUnescapedSlashes(json_encode($request->get("attachments"))) : NULL;
            if ($request->get("attachments") == "empty") {
                $params["attachments"] = NULL;
            }
        }
        if ($request->has("is_active")) {
            $params["is_active"] = $request->get("is_active");
        }
        if ($request->has("check_attach_file")) {
            $params["check_attach_file"] = $request->get("check_attach_file");
        }
        if ($request->has("share_status")) {
            $params["share_status"] = $request->get("share_status");
        }
        if ($request->has("owner")) {
            $owner = $request->get("owner");
            $owner = explode(":", $owner["id"]);
            $owner = $owner[1];
            $params["owner"] = $owner;
        }
        if ($request->has("share_to")) {
            $share_to = $request->get("share_to");
            $share_to = implode("|##|", $share_to);
            $params["share_to"] = $share_to;
        } else {
            $params["share_to"] = "";
        }
        if ($request->has("settings_layout")) {
            $layout_settings = $request->get("settings_layout");
            $layout_settings = json_encode($layout_settings);
            $params["settings_layout"] = $layout_settings;
        }
        if ($request->has("custom_function")) {
            $params["custom_function"] = json_encode($request->get("custom_function"));
        }
        if ($request->has("file_name")) {
            if ($request->has("file_name") == "") {
                $fileName = "\$template_name\$";
            } else {
                $fileName = str_replace(array("\\", "/", ":", "*", "?", "\"", "<", ">", "|"), " ", $request->get("file_name"));
            }
            $params["file_name"] = str_replace(" ", "_", $fileName);
        }
        $template = $recordModel->save($record, $params);
        $parentModuleName = $request->get("primary_module");
        if ($parentModuleName != "") {
            $parentTabid = getTabid($parentModuleName);
            $sql = "SELECT * FROM `vtiger_relatedlists` WHERE tabid=? AND label='Signed Documents'";
            $res = $adb->pquery($sql, array($parentTabid));
            $parentModule = Vtiger_Module::getInstance($parentModuleName);
            if ($adb->num_rows($res) == 0) {
                $childModule = Vtiger_Module::getInstance("SignedRecord");
                $fieldRelation = Vtiger_Field_Model::getInstance("related_to", $childModule);
                $parentModule->setRelatedList($childModule, "Signed Documents", "", "get_dependents_list", $fieldRelation->getId());
            }
            $checkLink = $adb->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($parentModule->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
            if ($adb->num_rows($checkLink) == 0) {
                $js_widgetType = "LISTVIEWMASSACTION";
                $js_widgetLabel = "Export to PDF/Email";
                $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                $parentModule->addLink($js_widgetType, $js_widgetLabel, $js_link);
                $parentModule->addLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
            }
        }
        $id = $template->getId();
        if (!$id) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $data["id"] = $id;
        $data["message"] = vtranslate("LBL_SUCCESSFUL", $module);
        if ($request->get("history")) {
            $historyRecordModel = new QuotingTool_HistoryRecord_Model();
            $historyParams = array("body" => $template->get("body"));
            $newHistory = $historyRecordModel->saveByTemplate($id, $historyParams);
            $calendarDatetimeUIType = new Calendar_Datetime_UIType();
            $data["history"] = array("id" => $newHistory->getId(), "created" => $calendarDatetimeUIType->getDisplayValue($newHistory->get("created")));
        }
        $response->setResult($data);
        return $response->emit();
    }
    /**
     * @param Vtiger_Request $request
     */
    public function save_setting(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $params = array();
        $recordModel = new QuotingTool_SettingRecord_Model();
        $record = $request->get("record");
        if ($request->has("description")) {
            $params["description"] = $request->get("description");
        }
        if ($request->has("label_decline")) {
            $params["label_decline"] = $request->get("label_decline");
        }
        if ($request->has("label_accept")) {
            $params["label_accept"] = $request->get("label_accept");
        }
        if ($request->has("background")) {
            $params["background"] = $request->get("background") ? QuotingToolUtils::jsonUnescapedSlashes(json_encode($request->get("background"), JSON_FORCE_OBJECT)) : NULL;
        }
        if ($request->has("expire_in_days")) {
            $params["expire_in_days"] = $request->get("expire_in_days");
        }
        if ($request->has("success_content")) {
            $params["success_content"] = $request->get("success_content");
        }
        if ($request->has("decline_message")) {
            $params["decline_message"] = $request->get("decline_message");
        }
        if ($request->has("enable_decline_mess")) {
            $params["enable_decline_mess"] = $request->get("enable_decline_mess");
        }
        if ($request->has("email_signed")) {
            $params["email_signed"] = $request->get("email_signed");
        }
        if ($request->has("email_from_copy")) {
            $params["email_from_copy"] = $request->get("email_from_copy");
        }
        if ($request->has("email_bcc_copy")) {
            $params["email_bcc_copy"] = $request->get("email_bcc_copy");
        }
        if ($request->has("email_subject_copy")) {
            $params["email_subject_copy"] = $request->get("email_subject_copy");
        }
        if ($request->has("email_body_copy")) {
            $params["email_body_copy"] = $request->get("email_body_copy");
        }
        if ($request->has("ignore_border_email")) {
            $params["ignore_border_email"] = $request->get("ignore_border_email");
        }
        if ($request->has("track_open")) {
            $params["track_open"] = $request->get("track_open");
        }
        if ($request->has("date_format")) {
            $params["date_format"] = $request->get("date_format");
        }
        $id = $recordModel->saveByTemplate($record, $params);
        if (!$id) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $data["id"] = $id;
        $data["message"] = vtranslate("LBL_SUCCESSFUL", $module);
        $response->setResult($data);
        return $response->emit();
    }
    /**
     * @param Vtiger_Request $request
     */
    public function save_proposal(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $moduleName = $request->getModule();
        $entityId = $request->get("record");
        $idTransaction = $request->get("idTransaction");
        $recordModel = new QuotingTool_Record_Model();
        $record = $recordModel->getById($entityId);
        if (!$record) {
            echo vtranslate("LBL_NOT_FOUND", $moduleName);
            exit;
        }
        $quotingTool = new QuotingTool();
        $module = $record->get("module");
        $varContent = $quotingTool->getVarFromString(base64_decode($record->get("content")));
        $varHeader = $quotingTool->getVarFromString(base64_decode($record->get("header")));
        $varFooter = $quotingTool->getVarFromString(base64_decode($record->get("footer")));
        $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
        $record = $record->decompileRecord($entityId, array("header", "content", "footer"), array(), $customFunction);
        $fileName = $quotingTool->makeUniqueFile($record->get("filename"));
        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
        $full_content = base64_encode($record->get("content"));
        $transactionId = $transactionRecordModel->saveTransaction($idTransaction, $entityId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
        $response->setResult($transactionId);
        return $response->emit();
    }
    /**
     * @param Vtiger_Request $request
     */
    public function delete(Vtiger_Request $request)
    {
        $recordId = $request->get("record");
        $model = new QuotingTool_Record_Model();
        $success = $model->delete($recordId);
        header("Location: index.php?module=QuotingTool&view=List");
    }
    public function getTemplate(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $relModule = $request->get("rel_module");
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $pdfLibContainer = "test/QuotingTool/resources/";
        $pdfLibSource = $pdfLibContainer . "mpdf/mpdf.php";
        if (!file_exists($pdfLibSource)) {
            $data["checkMPDF"] = "index.php?module=QuotingTool&view=List";
            $response->setResult($data);
            return $response->emit();
        }
        if (!$relModule) {
            $response->setError(200, vtranslate("LBL_INVALID_MODULE", $module));
            return $response->emit();
        }
        $quotingToolRecordModel = new QuotingTool_Record_Model();
        $templates = $quotingToolRecordModel->findByModule($relModule);
        $isShowLink = false;
        foreach ($templates as $template) {
            $templateModule = vtranslate($template->get("module"), $template->get("module"));
            $childModule = "";
            if ($template->get("createnewrecords") == 1 && $templateModule != $relModule) {
                $childModule = " <i>(" . $templateModule . ")</i> ";
            }
            if ($template->get("createnewrecords") == 1) {
                $isShowLink = true;
            }
            $data["showLink"] = $isShowLink;
            $data["data"][] = array("id" => $template->getId(), "filename" => $fileName = $template->get("filename") . $childModule, "description" => $template->get("description"), "createnewrecords" => $template->get("createnewrecords"), "modulename" => $template->get("module"));
        }
        $response->setResult($data);
        return $response->emit();
    }
    public function getHistories(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $record = $request->get("record");
        $calendarDatetimeUIType = new Calendar_Datetime_UIType();
        $historyRecordModel = new QuotingTool_HistoryRecord_Model();
        $histories = $historyRecordModel->listAllByTemplateId($record);
        foreach ($histories as $history) {
            $data[] = array("id" => intval($history->getId()), "created" => $calendarDatetimeUIType->getDisplayValue($history->get("created")));
        }
        $response->setResult($data);
        return $response->emit();
    }
    public function getHistory(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $historyId = $request->get("history_id");
        if (!$historyId) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $historyRecordModel = new QuotingTool_HistoryRecord_Model();
        $history = $historyRecordModel->getById($historyId);
        if (!$history) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $data = array("id" => $history->getId(), "body" => $history->get("body"));
        $response->setResult($data);
        return $response->emit();
    }
    public function removeHistories(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $historyIds = $request->get("history_id");
        if (!$historyIds) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $historyIds = array_map("trim", explode(",", $historyIds));
        $historyRecordModel = new QuotingTool_HistoryRecord_Model();
        $success = $historyRecordModel->removeHistories($historyIds);
        if (!$success) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $response->setResult($data);
        return $response->emit();
    }
    public function getAllRecord(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $data = array();
        $quotingToolRecordModel = new QuotingTool_Record_Model();
        $templates = $quotingToolRecordModel->findAll();
        foreach ($templates as $template) {
            $data[] = array("id" => $template->getId(), "filename" => $template->get("filename"), "module" => $template->get("module"), "description" => $template->get("description"));
        }
        $response->setResult($data);
        return $response->emit();
    }
    public function exportTemplateQuotingTool(Vtiger_Request $request)
    {
        global $site_URL;
        if (!file_exists("storage/QuotingTool/") && !mkdir("storage/QuotingTool/", 511, true)) {
            return "";
        }
        include_once "include/simplehtmldom/simple_html_dom.php";
        $recordTemplate = $request->get("idtemplate");
        $recordModel = new QuotingTool_Record_Model();
        $recordSettingModel = new QuotingTool_SettingRecord_Model();
        $record = $recordModel->getById($recordTemplate);
        $time = time();
        $fieldDecode = array("body", "header", "content", "footer", "email_subject", "email_content");
        $fileName = preg_replace("/[^A-Za-z0-9]/", "_", $record->get("filename"));
        $templatePath = "template_" . $fileName . $time;
        mkdir("storage/QuotingTool/" . $templatePath . "/upload/files", 511, true);
        mkdir("storage/QuotingTool/" . $templatePath . "/upload/images", 511, true);
        $fullPath = "storage/QuotingTool/" . $templatePath . "/upload";
        $fullPathFiles = "storage/QuotingTool/" . $templatePath . "/upload/files";
        $fullPathImg = "storage/QuotingTool/" . $templatePath . "/upload/images";
        $recordSetting = $recordSettingModel->findByTemplateId($recordTemplate);
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;
        $root = $dom->createElement("root");
        $dom->appendChild($root);
        $quotingTool = $dom->createElement("quotingtool");
        $root->appendChild($quotingTool);
        foreach ($recordModel->quotingToolFields as $field) {
            if ($field == "attachments") {
                $attachments = json_decode(htmlspecialchars_decode($record->get($field)), true);
                foreach ($attachments as $key => $file) {
                    $fileAtt = explode(".", $file["name"]);
                    $fileAtt[0] = $fileAtt[0] . "_" . $time;
                    $needFile = implode(".", $fileAtt);
                    copy($file["full_path"], (string) $fullPathFiles . "/" . $needFile);
                    $fileAttachMent = explode("/", $file["full_path"]);
                    if ($fileAttachMent) {
                        array_pop($fileAttachMent);
                        $fileAttachMent[] = $needFile;
                        $newFile = implode("/", $fileAttachMent);
                        $attachments[$key]["full_path"] = str_replace($site_URL, "\$site_url\$", $newFile);
                    }
                }
                $newVal = $attachments ? QuotingToolUtils::jsonUnescapedSlashes(json_encode($attachments)) : NULL;
                $quotingTool->appendChild($dom->createElement($field, $newVal));
            } else {
                if (in_array($field, $fieldDecode)) {
                    $content = base64_decode($record->get($field));
                    $html = str_get_html($content);
                    if (!$html) {
                        $content = base64_encode($content);
                        $quotingTool->appendChild($dom->createElement($field, $content));
                        continue;
                    }
                    if (0 < count($html->find("img"))) {
                        foreach ($html->find("img") as $img) {
                            if (strpos($img->attr["src"], $site_URL) !== false) {
                                $linkImg = $img->attr["src"];
                                $imgName = explode("/", $linkImg);
                                if ($imgName) {
                                    $oldImgName = end($imgName);
                                    $fileNameImg = explode(".", $oldImgName);
                                    $fileNameImg[0] = $fileNameImg[0] . "_" . $time;
                                    $needFileNameImg = implode(".", $fileNameImg);
                                    copy($linkImg, (string) $fullPathImg . "/" . $needFileNameImg);
                                    array_pop($imgName);
                                    $imgName[] = $needFileNameImg;
                                    $newVal = str_replace($site_URL, "\$site_url\$", implode("/", $imgName));
                                    $img->setAttribute("src", $newVal);
                                    $linkDataCke = $img->attr["data-cke-saved-src"];
                                    $newlinkDataCke = str_replace($site_URL, "\$site_url\$", $linkDataCke);
                                    $newlinkDataCke = str_replace($oldImgName, $needFileNameImg, $newlinkDataCke);
                                    $img->setAttribute("data-cke-saved-src", $newlinkDataCke);
                                }
                            }
                        }
                    }
                    $content = $html->save();
                    $content = base64_encode($content);
                    $quotingTool->appendChild($dom->createElement($field, $content));
                } else {
                    $quotingTool->appendChild($dom->createElement($field, $record->get($field)));
                }
            }
        }
        $settingQuotingTool = $dom->createElement("quotingtool_settings");
        $root->appendChild($settingQuotingTool);
        foreach ($recordSettingModel->quotingToolSettingFields as $field) {
            if ($field == "background") {
                $img = json_decode(htmlspecialchars_decode($recordSetting->get($field)), true);
                if ($img["image"] != "") {
                    $imgName = explode("/", $img["image"]);
                    if (strpos($img["image"], $site_URL) !== false) {
                        $oldBackGround = end($imgName);
                        $newNameBackGround = explode(".", $oldBackGround);
                        $newNameBackGround[0] = $newNameBackGround[0] . "_" . $time;
                        $needNameBackGround = implode(".", $newNameBackGround);
                        array_pop($imgName);
                        $imgName[] = $needNameBackGround;
                        $newVal = str_replace($site_URL, "\$site_url\$", implode("/", $imgName));
                        copy($img["image"], (string) $fullPathImg . "/" . $needNameBackGround);
                        $img["image"] = $newVal;
                    }
                    $backGround = $img ? QuotingToolUtils::jsonUnescapedSlashes(json_encode($img, JSON_FORCE_OBJECT)) : NULL;
                    $settingQuotingTool->appendChild($dom->createElement($field, $backGround));
                } else {
                    $settingQuotingTool->appendChild($dom->createElement($field, $recordSetting->get($field)));
                }
            } else {
                $settingQuotingTool->appendChild($dom->createElement($field, $recordSetting->get($field)));
            }
        }
        $fileName = $fileName . ".xml";
        $dom->save("storage/QuotingTool/" . $templatePath . "/" . $fileName);
        $dir = "storage/QuotingTool/" . $templatePath;
        $zip_file = $templatePath . ".zip";
        $rootPath = realpath($dir);
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                if ($relativePath && strpos($relativePath, "\\") !== false) {
                    $relativePath = preg_replace("/\\\\/", "/", $relativePath);
                }
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . basename($zip_file));
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($zip_file));
        readfile($zip_file);
        $this->rrmdir($dir);
        exit;
    }
    public function importTemplate(Vtiger_Request $request)
    {
        global $site_URL;
        include_once "modules/QuotingTool/resources/uploadfile/server/php/UploadHandler.php";
        include_once "include/simplehtmldom/simple_html_dom.php";
        $path = "storage/QuotingTool/ImportTemplate/";
        $fieldDecode = array("body", "header", "content", "footer", "email_subject", "email_content");
        if (!file_exists($path) && !mkdir($path, 511, true)) {
            return "";
        }
        $response = new Vtiger_Response();
        $data = array();
        $allFiles = array();
        $recordModel = new QuotingTool_Record_Model();
        $recordSettingModel = new QuotingTool_SettingRecord_Model();
        $id = 0;
        $module = $request->getModule();
        if (!empty($_FILES)) {
            $time = time();
            $options = array("script_url" => "index.php?module=QuotingTool&action=ActionAjax&mode=importTemplate", "upload_dir" => "storage/QuotingTool/ImportTemplate/" . $time . "/", "upload_url" => $site_URL . "storage/QuotingTool/ImportTemplate/" . $time . "/", "print_response" => false);
            $upload_handler = new UploadHandler($options);
            if ($upload_handler->response["files"] && 0 < count($upload_handler->response["files"])) {
                foreach ($upload_handler->response["files"] as $file) {
                    $filePath = utf8_decode(urldecode(str_replace($site_URL, "", $file->url)));
                    $id = QuotingTool_ActionAjax_Action::processImportFile($recordSettingModel, $recordModel, $path, $time, $filePath, $fieldDecode);
                }
            }
        }
        $this->rrmdir($path);
        if (!$id) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $data["id"] = $id;
        $data["message"] = vtranslate("LBL_SUCCESSFUL", $module);
        $response->setResult($data);
        return $response->emit();
    }
    public function processImportFile($recordSettingModel, $recordModel, $path, $time, $filePath, $fieldDecode)
    {
        global $site_URL;
        $pathunzip = $path . $time;
        $zip = new ZipArchive();
        $res = $zip->open($filePath);
        if ($res === true) {
            $zip->extractTo($pathunzip);
            $zip->close();
        }
        $pathImg = "storage/QuotingTool/ImportTemplate/" . $time . "/upload/images";
        $pathFile = "storage/QuotingTool/ImportTemplate/" . $time . "/upload/files";
        $pathCoreImg = "test/upload/images";
        $pathCoreFile = "test/upload/files";
        $this->recurse_copy($pathImg, $pathCoreImg);
        $this->recurse_copy($pathFile, $pathCoreFile);
        $xmlFile = glob("storage/QuotingTool/ImportTemplate/" . $time . "/*.xml");
        $xml = simplexml_load_file(utf8_decode(urldecode($xmlFile[0]))) or exit("Error: Cannot create object");
        $params = array();
        $paramsSetting = array();
        foreach ($recordModel->quotingToolFields as $field) {
            if ($field == "attachments") {
                $needval = str_replace("\$site_url\$", $site_URL, $xml->quotingtool->{$field});
                $params[$field] = $needval;
            } else {
                if (in_array($field, $fieldDecode)) {
                    $content = base64_decode($xml->quotingtool->{$field});
                    $html = str_get_html($content);
                    if (!$html) {
                        $content = base64_encode($content);
                        $params[$field] = $content;
                        continue;
                    }
                    if (0 < count($html->find("img"))) {
                        foreach ($html->find("img") as $img) {
                            if (strpos($img->attr["src"], "\$site_url\$") !== false) {
                                $linkImg = $img->attr["src"];
                                $needVal = str_replace("\$site_url\$", $site_URL, $linkImg);
                                $img->setAttribute("src", $needVal);
                                $linkDataCke = $img->attr["data-cke-saved-src"];
                                $newlinkDataCke = str_replace("\$site_url\$", $site_URL, $linkDataCke);
                                $img->setAttribute("data-cke-saved-src", $newlinkDataCke);
                            }
                        }
                    }
                    $content = $html->save();
                    $content = base64_encode($content);
                    $params[$field] = $content;
                } else {
                    if ($field == "is_active") {
                        $params[$field] = 1;
                    } else {
                        $params[$field] = $xml->quotingtool->{$field};
                    }
                }
            }
        }
        $template = $recordModel->save("", $params);
        $id = $template->getId();
        foreach ($recordSettingModel->quotingToolSettingFields as $field) {
            if ($field == "background") {
                $img = htmlspecialchars_decode($xml->quotingtool_settings->{$field}, true);
                $needVal = str_replace("\$site_url\$", $site_URL, $img);
                $paramsSetting[$field] = $needVal;
            } else {
                $paramsSetting[$field] = $xml->quotingtool_settings->{$field};
            }
        }
        $recordSettingModel->saveByTemplate($id, $paramsSetting);
        $historyRecordModel = new QuotingTool_HistoryRecord_Model();
        $historyParams = array("body" => $xml->quotingtool->body);
        $historyRecordModel->saveByTemplate($id, $historyParams);
        return $id;
    }
    public function ImportDefaultTemplates(Vtiger_Request $request)
    {
        global $site_URL;
        $response = new Vtiger_Response();
        $zipFileName = $request->get("selectedValue");
        $module = "QuotingTool";
        if ($zipFileName && $zipFileName != "Default") {
            include_once "modules/QuotingTool/resources/uploadfile/server/php/UploadHandler.php";
            include_once "include/simplehtmldom/simple_html_dom.php";
            $time = time();
            $path = "storage/QuotingTool/ImportTemplate/" . $time;
            $recordModel = new QuotingTool_Record_Model();
            $recordSettingModel = new QuotingTool_SettingRecord_Model();
            $fieldDecode = array("body", "header", "content", "footer", "email_subject", "email_content");
            if (!file_exists($path) && !mkdir($path, 511, true)) {
                return "";
            }
            $uploadUrl = $site_URL . "storage/QuotingTool/ImportTemplate/" . $time . "/";
            $linkDownload = "https://www.vtexperts.com/files/dd/" . $zipFileName;
            $fileContent = file_put_contents("storage/QuotingTool/ImportTemplate/" . $time . "/" . $zipFileName, fopen($linkDownload, "r"));
            $filePath = utf8_decode(urldecode(str_replace($site_URL, "", $uploadUrl . $zipFileName)));
            $pathunzip = "storage/QuotingTool/ImportTemplate/";
            $id = 0;
            $id = QuotingTool_ActionAjax_Action::processImportFile($recordSettingModel, $recordModel, $pathunzip, $time, $filePath, $fieldDecode);
        }
        $this->rrmdir($path);
        if (!$id || $id == 0) {
            $response->setError(200, vtranslate("LBL_FAILURE", $module));
            return $response->emit();
        }
        $data["id"] = $id;
        $data["message"] = vtranslate("Import Template Successful", $module);
        $response->setResult($data);
        return $response->emit();
    }
    public function CreateNewProposal(Vtiger_Request $request)
    {
        global $site_URL;
        global $current_user;
        global $adb;
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $createNewRecord = $request->get("createewrecords");
        $link = " ";
        $recordModel = new QuotingTool_Record_Model();
        if ($createNewRecord == "true") {
            $templateId = $request->get("template_id");
            $relModule = $request->get("primaryModule");
            $record = $recordModel->getById($templateId);
            $quotingTool = new QuotingTool();
            $varContent = $quotingTool->getVarFromString(base64_decode($record->get("content")));
            $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
            $record = $record->decompileRecord("0", array("content", "header", "footer", "email_subject", "email_content"), array(), $customFunction);
            $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
            $full_content = base64_encode($record->get("content"));
            $transactionId = $transactionRecordModel->saveTransaction(0, $templateId, $record->get("module"), 0, NULL, NULL, $full_content, $record->get("description"));
            $transactionRecord = $transactionRecordModel->findById($transactionId);
            $hash = $transactionRecord->get("hash");
            $hash = $hash ? $hash : "";
            $keys_values = array();
            $site = rtrim($site_URL, "/");
            $link = (string) $site . "/modules/QuotingTool/proposal/index.php?record=" . $transactionId . "&session=" . $hash . "&id=" . $templateId . "&newrecord=true";
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
                $full_content = base64_encode($record->get("content"));
                $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $relModule, 0, NULL, NULL, $full_content, $record->get("description"));
            }
            $data = array("site_url" => preg_replace("(^(https?|ftp)://)", "", $site), "link_propocal" => "/modules/QuotingTool/proposal/index.php?record=" . $transactionId . "&session=" . $hash . "&id=" . $templateId);
        }
        $response->setResult($link);
        $primaryModule = $request->get("primaryModule");
        $moduleInstance = Vtiger_Module_Model::getInstance($primaryModule);
        $arrModule = $recordModel->getRelatedModules($moduleInstance);
        $query = $adb->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `deleted` != 1 AND `createnewrecords` = 1", array());
        $allModule = array();
        if (0 < $adb->num_rows($query)) {
            while ($row = $adb->fetchByAssoc($query)) {
                $allModule[] = $row["module"];
            }
        }
        $relationModule = array();
        foreach ($allModule as $module) {
            if ($primaryModule == $module) {
                continue;
            }
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $relationModule = array_merge($relationModule, $recordModel->getRelatedModules($moduleModel));
        }
        $relationModule = array_unique($relationModule);
        foreach ($arrModule as $val) {
            $parentModule = Vtiger_Module::getInstance($val);
            if ($createNewRecord == "true") {
                $checkLink = $adb->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($parentModule->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
                if ($adb->num_rows($checkLink) == 0) {
                    $js_widgetType = "LISTVIEWMASSACTION";
                    $js_widgetLabel = "Export to PDF/Email";
                    $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                    $parentModule->addLink($js_widgetType, $js_widgetLabel, $js_link);
                    $parentModule->addLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
                }
            } else {
                $listRecord = $adb->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `module`=? AND `deleted` != 1", array($val));
                if ($adb->num_rows($listRecord) == 0 && in_array($val, $relationModule) == false) {
                    $checkLink = $adb->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($parentModule->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
                    if (0 < $adb->num_rows($checkLink)) {
                        $js_widgetType = "LISTVIEWMASSACTION";
                        $js_widgetLabel = "Export to PDF/Email";
                        $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                        $parentModule->deleteLink($js_widgetType, $js_widgetLabel, $js_link);
                        $parentModule->deleteLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
                    }
                }
            }
        }
        return $response->emit();
    }
    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        if ($dir) {
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($src . "/" . $file)) {
                        $result = $this->recurse_copy($src . "/" . $file, $dst . "/" . $file);
                    } else {
                        $result = copy($src . "/" . $file, $dst . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
    }
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    public function checkMPDF(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $pdfLibContainer = "test/QuotingTool/resources/";
        $pdfLibSource = $pdfLibContainer . "mpdf/";
        if (!is_dir($pdfLibSource)) {
            $data = "index.php?module=QuotingTool&view=List";
        } else {
            $data = "success";
        }
        $response->setResult($data);
        return $response->emit();
    }
    public function changeSignTo(Vtiger_Request $request)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        global $adb;
        $transaction_id = $request->get("transaction_id");
        $sign_to = $request->get("sign_to");
        $sql = "UPDATE vtiger_quotingtool_transactions SET sign_to=? WHERE id=?";
        $adb->pquery($sql, array($sign_to, $transaction_id));
        $query = "SELECT * FROM `vtiger_quotingtool_transactions` WHERE id = ? ORDER BY `id` DESC LIMIT 1";
        $rs = $adb->pquery($query, array($transaction_id));
        if (0 < $adb->num_rows($rs)) {
            $content = $adb->query_result($rs, 0, "full_content");
            $contentDecode = base64_decode($content);
            $content_html = str_get_html($contentDecode);
            foreach ($content_html->find("img") as $img) {
                $img_class = $img->getAttribute("class");
                if ($img_class == "quoting_tool-widget-secondary_signature-image") {
                    if ($sign_to == "SECONDARY" && $adb->query_result($rs, 0, "secondary_signature") == "") {
                        $img->setAttribute("style", "height: 40px; width: 130px;");
                    } else {
                        if ($sign_to == "PRIMARY" && $adb->query_result($rs, 0, "secondary_signature") == "") {
                            $img->setAttribute("style", "height: 40px; width: 130px; display: none;");
                        }
                    }
                }
                if ($img_class == "quoting_tool-widget-signature-image") {
                    if ($sign_to == "PRIMARY" && $adb->query_result($rs, 0, "signature") == "") {
                        $img->setAttribute("style", "height: 40px; width: 130px;");
                    } else {
                        if ($sign_to == "SECONDARY" && $adb->query_result($rs, 0, "signature") == "") {
                            $img->setAttribute("style", "height: 40px; width: 130px; display: none;");
                        }
                    }
                }
            }
            $full_content = $content_html->save();
            $full_content = base64_encode($full_content);
            $sql = "UPDATE vtiger_quotingtool_transactions SET full_content=? WHERE id=?";
            $adb->pquery($sql, array($full_content, $transaction_id));
        }
        $response = new Vtiger_Response();
        $response->setResult(array());
        return $response->emit();
    }
    public function getLinkHtml(Vtiger_Request $request)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        global $adb;
        global $site_URL;
        global $current_user;
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $recordIds = $request->get("record");
        $htmlLink = "";
        foreach ($recordIds as $key => $recordId) {
            $templateId = $request->get("template_id");
            $isCreateNewRecord = $request->get("isCreateNewRecord");
            $childModule = $request->get("childModule");
            $recordModel = new QuotingTool_Record_Model();
            $record = $recordModel->getById($templateId);
            $relModule = $record->get("module");
            $quotingTool = new QuotingTool();
            $contentOfTemplate = base64_decode($record->get("content"));
            $varContent = $quotingTool->getVarFromString($contentOfTemplate);
            $hasSignature = 0;
            if (strpos($contentOfTemplate, "quoting_tool-widget-secondary_signature-main") !== false && strpos($contentOfTemplate, "quoting_tool-widget-signature-main")) {
                $hasSignature = 1;
            }
            $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
            $record = $record->decompileRecord($recordId, array("content", "header", "footer", "email_subject", "email_content"), array(), $customFunction);
            $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
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
                        if ($recordId != "") {
                            $related_record_model = Vtiger_Record_Model::getInstanceById($recordId);
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
            $full_content = base64_encode($full_content);
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
                        $keys_values["\$custom_user_signature\$"] = preg_replace("/\\v+|\\\\r\\\\n/", "<br/>", $current_user->signature);
                        $keys_values["\$custom_user_signature\$"] = preg_replace("/\\v+|\\\\n/", "<br/>", $keys_values["\$custom_user_signature\$"]);
                        $keys_values["\$custom_user_signature\$"] = preg_replace("/\\v+|\\\\r/", "<br/>", $keys_values["\$custom_user_signature\$"]);
                    }
                }
                if (array_key_exists($var, $companyfields)) {
                    $keys_values[$var] = $companyfields[$var];
                }
            }
            if (!empty($keys_values)) {
                $full_content = base64_decode($full_content);
                $record->set("content", $quotingTool->mergeCustomTokens($full_content, $keys_values));
                $full_content = base64_encode($record->get("content"));
                $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $relModule, $recordId, NULL, NULL, $full_content, $record->get("description"));
            }
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $textLink = $recordModel->get("quote_no");
            $htmlLink .= "<a href='" . $link . "'>" . $textLink . "</a><br>";
        }
        $response = new Vtiger_Response();
        $response->setResult($htmlLink);
        return $response->emit();
    }
}

?>