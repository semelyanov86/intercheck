<?php

require_once "include/utils/utils.php";
include "modules/Emails/mail.php";
include "modules/QuotingTool/QuotingTool.php";
include "test/QuotingTool/resources/mpdf.php";
error_reporting(0);
/**
 * Class QuotingTool_PDFHandler_Action
 */
class QuotingTool_PDFHandler_Action extends Vtiger_Action_Controller
{
    /**
     * Fn - __construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("export");
        $this->exposeMethod("download");
        $this->exposeMethod("preview_and_send_email");
        $this->exposeMethod("duplicate");
        $this->exposeMethod("listExport");        
    }
    /**
     * @param Vtiger_Request $request
     * @return bool
     */
    public function checkPermission(Vtiger_Request $request)
    {
    }
    /**
     * Fn - process
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        $isdebug = $request->get("isdebug");
        if ($isdebug == "on") {
            error_reporting(1);
        }
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * Fn - downloadPreview
     * Save PDF content to the file
     *
     * @link http://www.mpdf1.com/forum/discussion/36/how-to-automatically-save-pdf-file/p1
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function export(Vtiger_Request $request)
    {
        global $site_URL;
        global $current_user;
        global $adb;
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $entityId = $request->get("record");
        $templateId = $request->get("template_id");
        $recordModel = new QuotingTool_Record_Model();
        $record = $recordModel->getById($templateId);
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
        $tabId = Vtiger_Functions::getModuleId($module);
        $recordId = $entityId;
        if ($record->get("file_name")) {
            global $adb;
            $fileName = $record->get("file_name");
            if (strpos("\$record_no\$", $record->get("file_name")) != -1) {
                $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                $resultNo = $recordResult->get($nameFieldModuleNo);
                $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
            }
            if (strpos("\$record_name\$", $record->get("file_name")) != -1) {
                $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                $fileName = str_replace("\$record_name\$", $resultName, $fileName);
            }
            if (strpos("\$template_name\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$template_name\$", $record->get("filename"), $fileName);
            }
            $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
            $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
            list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
            $day = date("d", time($date));
            $month = date("m", time($date));
            $year = date("Y", time($date));
            if (strpos("\$day\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$day\$", $day, $fileName);
            }
            if (strpos("\$month\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$month\$", $month, $fileName);
            }
            if (strpos("\$year\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$year\$", $year, $fileName);
            }
        } else {
            $fileName = $record->get("filename");
        }
        $fileName = $quotingTool->makeUniqueFile($fileName);
        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
        $full_content = base64_encode($record->get("content"));
        $transactionId = $transactionRecordModel->saveTransaction(0, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
        $transactionRecord = $transactionRecordModel->findById($transactionId);
        $hash = $transactionRecord->get("hash");
        $hash = $hash ? $hash : "";
        $keys_values = array();
        $site = rtrim($site_URL, "/");
        $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
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
        $full_content = $record->get("content");
        $tmp_html = str_get_html($full_content);
        foreach ($tmp_html->find("img") as $img) {
            $json_data_info = $img->getAttribute("data-info");
            $data_info = json_decode(html_entity_decode($json_data_info));
            if ($data_info) {
                $tabId = $data_info->settings_field_image;
                if (!empty($tabId)) {
                    $relModuleName = vtlib_getModuleNameById($tabId);
                    $recordIdForRel = $entityId;
                    if ($module != $relModuleName) {
                        $rs = $adb->pquery("SELECT fieldid FROM vtiger_fieldmodulerel WHERE module = '" . $module . "' AND relmodule = '" . $relModuleName . "'", array());
                        if (0 < $adb->num_rows($rs)) {
                            $relatedFieldId = $adb->query_result($rs, 0, 0);
                            $field_model = Vtiger_Field_Model::getInstance($relatedFieldId);
                            $field_name = $field_model->getName();
                            $related_record_model = Vtiger_Record_Model::getInstanceById($recordIdForRel);
                            $recordIdForRel = $related_record_model->get($field_name);
                        } else {
                            $sourceTabId = getTabid($module);
                            $rs = $adb->pquery("SELECT relationfieldid from vtiger_relatedlists where tabid=" . $data_info->settings_field_image . " AND related_tabid = " . $sourceTabId, array());
                            if (0 < $adb->num_rows($rs)) {
                                $relatedFieldId = $adb->query_result($rs, 0, 0);
                                $field_model = Vtiger_Field_Model::getInstance($relatedFieldId);
                                $field_name = $field_model->getName();
                                $related_record_model = Vtiger_Record_Model::getInstanceById($recordIdForRel);
                                $recordIdForRel = $related_record_model->get($field_name);
                            }
                        }
                    }
                }
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
        $record->set("content", $full_content);
        if (!empty($keys_values)) {
            $record->set("content", $quotingTool->mergeCustomTokens($record->get("content"), $keys_values));
            $full_content = base64_encode($record->get("content"));
            $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
        }
        $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
        foreach ($varHeader as $var) {
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
            $record->set("header", $quotingTool->mergeCustomTokens($record->get("header"), $keys_values));
        }
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
            $record->set("footer", $quotingTool->mergeCustomTokens($record->get("footer"), $keys_values));
        }
        $content = $record->get("content");
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        foreach ($html->find("table") as $table) {
            $table->removeAttribute("data-info");
        }
        $content = $html->save();
        $pdf = $quotingTool->createPdf($content, $record->get("header"), $record->get("footer"), $fileName, $record->get("settings_layout"), $entityId);
        $fileContent = "";
        if (is_readable($pdf)) {
            $fileContent = file_get_contents($pdf);
        }
        $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
        $name = str_replace(".pdf", "", $fileName);
        $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
        $name = str_replace(" ", "_", $name);
        $fileName = str_replace("\$", "_", $name);
        $fileName = trim($fileName);
        header("Content-type: application/pdf");
        header("Pragma: public");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . html_entity_decode($fileName, ENT_QUOTES, vglobal("default_charset")) . ".pdf");
        header("Content-Description: PHP Generated Data");
        echo $fileContent;
    }
    public function listExport(Vtiger_Request $request)
    {
        global $site_URL;
        global $current_user;
        global $adb;
        global $vtiger_current_version;
        $checked_params = $request->get("checked_params");
        foreach ($checked_params as $key => $val) {
            $request->set($key, $val);
        }
        $recordIds = $this->getRecordsListFromRequest($request);
        $templateId = $request->get("template_id");
        $list_content = "";
        $list_header = "";
        $list_footer = "";
        $fileName = "";
        $entityId = "";
        $countRecords = count($recordIds);
        $index = 1;
        foreach ($recordIds as $val) {
            $moduleName = $request->getModule();
            $entityId = $val;
            $recordModel = new QuotingTool_Record_Model();
            $record = $recordModel->getById($templateId);
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
            $tabId = Vtiger_Functions::getModuleId($module);
            $recordId = $entityId;
            if ($record->get("file_name")) {
                global $adb;
                $fileName = $record->get("file_name");
                if (strpos("\$record_no\$", $record->get("file_name")) != -1) {
                    $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                    $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                    $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                    $resultNo = $recordResult->get($nameFieldModuleNo);
                    $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
                }
                if (strpos("\$record_name\$", $record->get("file_name")) != -1) {
                    $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                    $fileName = str_replace("\$record_name\$", $resultName, $fileName);
                }
                if (strpos("\$template_name\$", $record->get("file_name")) != -1) {
                    $fileName = str_replace("\$template_name\$", $record->get("filename"), $fileName);
                }
                $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
                $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
                list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
                $day = date("d", time($date));
                $month = date("m", time($date));
                $year = date("Y", time($date));
                if (strpos("\$day\$", $record->get("file_name")) != -1) {
                    $fileName = str_replace("\$day\$", $day, $fileName);
                }
                if (strpos("\$month\$", $record->get("file_name")) != -1) {
                    $fileName = str_replace("\$month\$", $month, $fileName);
                }
                if (strpos("\$year\$", $record->get("file_name")) != -1) {
                    $fileName = str_replace("\$year\$", $year, $fileName);
                }
            } else {
                $fileName = $record->get("filename");
            }
            $fileName = $quotingTool->makeUniqueFile($fileName);
            $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
            $full_content = base64_encode($record->get("content"));
            $transactionId = $transactionRecordModel->saveTransaction(0, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
            $transactionRecord = $transactionRecordModel->findById($transactionId);
            $hash = $transactionRecord->get("hash");
            $hash = $hash ? $hash : "";
            $keys_values = array();
            $site = rtrim($site_URL, "/");
            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
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
            $full_content = $record->get("content");
            $tmp_html = str_get_html($full_content);
            foreach ($tmp_html->find("img") as $img) {
                $json_data_info = $img->getAttribute("data-info");
                $data_info = json_decode(html_entity_decode($json_data_info));
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
            $QuotingToolRecordModel = new QuotingTool_Record_Model();
            foreach ($tmp_html->find("img") as $img) {
                $quoting_tool_product_image = $img->getAttribute("class");
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
            $record->set("content", $full_content);
            if (!empty($keys_values)) {
                $record->set("content", $quotingTool->mergeCustomTokens($record->get("content"), $keys_values));
                $full_content = base64_encode($record->get("content"));
                $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
            }
            $transactionId = $transactionRecordModel->saveTransaction($transactionId, $templateId, $module, $entityId, NULL, NULL, $full_content, $record->get("description"));
            $breakPage = "<div class=\"pagebreak\"></div>";
            if ($index < $countRecords) {
                $list_content .= $full_content . $breakPage;
            } else {
                $list_content .= $full_content;
            }
            foreach ($varHeader as $var) {
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
                $record->set("header", $quotingTool->mergeCustomTokens($record->get("header"), $keys_values));
            }
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
                $record->set("footer", $quotingTool->mergeCustomTokens($record->get("footer"), $keys_values));
            }
            $list_header = $record->get("header");
            $list_footer = $record->get("footer");
            $index++;
        }
        include_once "include/simplehtmldom/simple_html_dom.php";
        $content = $list_content;
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        foreach ($html->find("table") as $table) {
            $table->removeAttribute("data-info");
        }
        $content = $html->save();
        $pdf = $quotingTool->createPdf($content, $list_header, $list_footer, $fileName, $record->get("settings_layout"), $entityId);
        $fileContent = "";
        if (is_readable($pdf)) {
            $fileContent = file_get_contents($pdf);
        }
        $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
        $name = str_replace(".pdf", "", $fileName);
        $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
        $name = str_replace(" ", "_", $name);
        $fileName = str_replace("\$", "_", $name);
        $fileName = trim($fileName);
        header("Content-type: application/pdf");
        header("Pragma: public");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . html_entity_decode($fileName, ENT_QUOTES, vglobal("default_charset")) . ".pdf");
        header("Content-Description: PHP Generated Data");
        echo $fileContent;
    }
    public function getRecordsListFromRequest(Vtiger_Request $request)
    {
        $cvId = $request->get("viewname");
        $module = $request->get("module");
        if (!empty($cvId) && $cvId == "undefined") {
            $sourceModule = $request->get("sourceModule");
            $cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
        }
        $selectedIds = $request->get("selected_ids");
        $excludedIds = $request->get("excluded_ids");
        if (!empty($selectedIds) && $selectedIds != "all" && !empty($selectedIds) && 0 < count($selectedIds)) {
            return $selectedIds;
        }
        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
        if ($customViewModel) {
            $searchKey = $request->get("search_key");
            $searchValue = $request->get("search_value");
            $operator = $request->get("operator");
            if (!empty($operator)) {
                $customViewModel->set("operator", $operator);
                $customViewModel->set("search_key", $searchKey);
                $customViewModel->set("search_value", $searchValue);
            }
            if ($module == "Documents") {
                $customViewModel->set("folder_id", $request->get("folder_id"));
                $customViewModel->set("folder_value", $request->get("folder_value"));
            }
            $customViewModel->set("search_params", $request->get("search_params"));
            return $customViewModel->getRecordIds($excludedIds, $module);
        }
    }
    /**
     * Fn - downloadPreview
     * Save PDF content to the file
     *
     * @link http://www.mpdf1.com/forum/discussion/36/how-to-automatically-save-pdf-file/p1
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function preview_and_send_email(Vtiger_Request $request)
    {
        global $adb;
        global $current_user;
        global $site_URL;
        global $application_unique_key;
        global $vtiger_current_version;
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $data = array();
        $entityId = $request->get("record");
        $quotingTool = new QuotingTool();
        $moduleName = $request->getModule();
        $selectedEmail = $request->get("selectedEmail");
        $documentIds = $request->get("documentids");
        $strCcEmails = $request->get("ccValues");
        if ($strCcEmails === NULL) {
            $strCcEmails = "";
        }
        $arrCcEmails = explode(",", trim($strCcEmails));
        $ccEmails = array();
        foreach ($arrCcEmails as $cc) {
            $ccEmails[] = $quotingTool->getEmailFromString($cc);
        }
        $strBccEmails = $request->get("bccValues");
        if ($strBccEmails === NULL) {
            $strBccEmails = "";
        }
        $arrBccEmails = explode(",", trim($strBccEmails));
        $bccEmails = array();
        foreach ($arrBccEmails as $bcc) {
            $bccEmails[] = $quotingTool->getEmailFromString($bcc);
        }
        $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
        $_FILES = $result["file"];
        if (!$request->get("email_subject") || !$request->get("email_content")) {
            $response->setError(200, vtranslate("LBL_INVALID_EMAIL_TEMPLATE", $moduleName));
            $response->emit();
            exit;
        }
        $emails = array();
        $toEmail = NULL;
        if (is_array($selectedEmail)) {
            foreach ($selectedEmail as $e) {
                list($no, $email_record, $toEmail) = explode("||", $e);
                $emails[$email_record][] = $toEmail;
            }
        } else {
            list($no, $email_record, $toEmail) = explode("||", $selectedEmail);
            $emails[$email_record][] = $toEmail;
        }
        if (empty($emails)) {
            $response->setError(200, vtranslate("LBL_INVALID_EMAIL", $moduleName));
            $response->emit();
            exit;
        }
        $transactionId = $request->get("transaction_id");
        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
        $transactionRecord = $transactionRecordModel->findById($transactionId);
        $hash = $transactionRecord->get("hash");
        $hash = $hash ? $hash : "";
        $varContentEmail = $quotingTool->getVarFromString(base64_decode($_REQUEST["email_content"]));
        $keys_values = array();
        $site = rtrim($site_URL, "/");
        $subLink = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
        $compactLink = preg_replace("(^(https?|ftp)://)", "", $subLink);
        $mergedProposalLink = false;
        foreach ($varContentEmail as $var) {
            if ($var == "\$custom_proposal_link\$") {
                $keys_values["\$custom_proposal_link\$"] = $compactLink;
                $mergedProposalLink = true;
            } else {
                if ($var == "\$custom_user_signature\$") {
                    $keys_values["\$custom_user_signature\$"] = nl2br($current_user->signature);
                }
            }
        }
        if (!empty($keys_values)) {
            $emailContent = $quotingTool->mergeCustomTokens(base64_decode($request->get("email_content")), $keys_values);
        } else {
            $emailContent = base64_decode($request->get("email_content"));
        }
        $emailSubject = base64_decode($request->get("email_subject"));
        $fromName = $current_user->first_name . " " . $current_user->last_name;
        $fromEmail = NULL;
        if ($current_user->email1) {
            $fromEmail = $current_user->email1;
        } else {
            if ($current_user->email2) {
                $fromEmail = $current_user->email2;
            } else {
                if ($current_user->secondaryemail) {
                    $fromEmail = $current_user->secondaryemail;
                }
            }
        }
        if ($fromEmail) {
            $fromName = (string) $fromName . " (" . $fromEmail . ")";
        }
        $counter = 0;
        $check_attach_file = $request->get("check_attach_file") == "on";
        $multipleRecord = $request->get("multi_record");
        $signature = $request->get("signature");
        $recordId = $request->get("record");
        $sentEmails = array();
        if ($multipleRecord == "" && $recordId != "") {
            $multipleRecord = array();
            $multipleRecord[] = $recordId;
        }
        foreach ($multipleRecord as $recordId) {
            foreach ($emails as $relatedRecord => $emailList) {
                foreach ($emailList as $email) {
                    if (in_array($email, $sentEmails)) {
                        continue;
                    }
                    $sentEmails[] = $email;
                    $entityId = $recordId;
                    $cc = implode(",", $ccEmails);
                    $bcc = implode(",", $bccEmails);
                    $emailModuleName = "Emails";
                    $userId = $current_user->id;
                    $emailFocus = CRMEntity::getInstance($emailModuleName);
                    $emailFieldValues = array("assigned_user_id" => $userId, "subject" => $emailSubject, "description" => $emailContent, "from_email" => $fromEmail, "saved_toid" => $email, "ccmail" => $cc, "bccmail" => $bcc, "parent_id" => $entityId . "@" . $userId . "|", "email_flag" => "SENT", "activitytype" => $emailModuleName, "date_start" => date("Y-m-d"), "time_start" => date("H:i:s"), "mode" => "", "signature" => $signature, "id" => "", "documentids" => $documentIds);
                    if (!empty($recordId)) {
                        $emailFocus1 = Vtiger_Record_Model::getInstanceById($recordId, $emailModuleName);
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
                    $emailFocus1->set("documentids", $documentIds);
                    $emailFocus1->set("mode", "");
                    $emailFocus1->set("id", "");
                    $emailFocus1->save();
                    $emailId = $emailFocus1->getId();
                    $emailFocus->id = $emailId;
                    $emailFocus->column_fields = $emailFieldValues;
                    if ($emailId) {
                        $emailTracking = vglobal("email_tracking");
                        if ($emailTracking == "Yes") {
                            $trackURL = (string) $site_URL . "/modules/Emails/TrackAccess.php?record=" . $entityId . "&mailid=" . $emailId . "&app_key=" . $application_unique_key;
                            $emailContent = "<img src='" . $trackURL . "' alt='' width='1' height='1'>" . $emailContent;
                        }
                        $logo = 0;
                        if (stripos($emailContent, "<img src=\"cid:logo\" />")) {
                            $logo = 1;
                        }
                        if ($signature == "Yes") {
                            $currentUserModel = Users_Record_Model::getCurrentUserModel();
                            if ($currentUserModel->get("signature") != "") {
                                $emailContent .= "<br><br>" . decode_html(str_replace(array("\\r\\n", "\\n"), "<br>", $currentUserModel->get("signature")));
                            }
                        }
                        $transactionId = $request->get("transaction_id");
                        $transactionRecordModel = new QuotingTool_TransactionRecord_Model();
                        $transactionRecord = $transactionRecordModel->findById($transactionId);
                        $hash = $transactionRecord->get("hash");
                        $hash = $hash ? $hash : "";
                        $templateId = $transactionRecord->get("template_id");
                        $recordModel = new QuotingTool_Record_Model();
                        $record = $recordModel->getById($templateId);
                        $varContent = $quotingTool->getVarFromString(base64_decode($record->get("content")));
                        $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
                        $record = $record->decompileRecord($entityId, array("header", "content", "footer"), array(), $customFunction);
                        if (0 < count($_FILES)) {
                            foreach ($_FILES as $file) {
                                if ($file["name"] == "") {
                                    continue;
                                }
                                $target_dir = "storage/QuotingTool/";
                                $file_name = basename($file["name"]);
                                $file_name = date("mdYhis") . $file_name;
                                $target_file = $target_dir . $file_name;
                                $filetype = $file["type"];
                                move_uploaded_file($file["tmp_name"], $target_file);
                                $quotingTool->createAttachManuallyFile($emailFocus, $file_name, $filetype);
                            }
                        }
                        $documentRes = $adb->pquery("SELECT vtiger_attachments.attachmentsid FROM vtiger_senotesrel\r\n\t\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?\r\n\t\t\t\t\t\tINNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid\r\n\t\t\t\t\t\tINNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid\r\n\t\t\t\t\t\tINNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid\r\n\t\t\t\t\t\tWHERE vtiger_crmentity.deleted = 0", array($emailId));
                        $numOfRows = $adb->num_rows($documentRes);
                        if ($numOfRows) {
                            for ($i = 0; $i < $numOfRows; $i++) {
                                $sql_rel = "insert into vtiger_seattachmentsrel values(?,?)";
                                $adb->pquery($sql_rel, array($emailId, $adb->query_result($documentRes, $i, "attachmentsid")));
                            }
                        }
                        if ($check_attach_file) {
                            $tabId = Vtiger_Functions::getModuleId($moduleName);
                            $recordId = $entityId;
                            if ($record->get("file_name")) {
                                global $adb;
                                $fileName = $record->get("file_name");
                                if (strpos("\$record_no\$", $record->get("file_name")) != -1) {
                                    $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                                    $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                                    $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                                    $resultNo = $recordResult->get($nameFieldModuleNo);
                                    $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
                                }
                                if (strpos("\$record_name\$", $record->get("file_name")) != -1) {
                                    $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                                    $fileName = str_replace("\$record_name\$", $resultName, $fileName);
                                }
                                if (strpos("\$template_name\$", $record->get("file_name")) != -1) {
                                    $fileName = str_replace("\$template_name\$", $record->get("filename"), $fileName);
                                }
                                $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
                                $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
                                list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
                                $day = date("d", time($date));
                                $month = date("m", time($date));
                                $year = date("Y", time($date));
                                if (strpos("\$day\$", $record->get("file_name")) != -1) {
                                    $fileName = str_replace("\$day\$", $day, $fileName);
                                }
                                if (strpos("\$month\$", $record->get("file_name")) != -1) {
                                    $fileName = str_replace("\$month\$", $month, $fileName);
                                }
                                if (strpos("\$year\$", $record->get("file_name")) != -1) {
                                    $fileName = str_replace("\$year\$", $year, $fileName);
                                }
                            } else {
                                $fileName = $record->get("filename");
                            }
                            $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
                            $name = str_replace(".pdf", "", $fileName);
                            $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
                            $name = str_replace(" ", "_", $name);
                            $fileName = str_replace("\$", "_", $name);
                            $fileName = trim($fileName);
                            $fileName = $quotingTool->makeUniqueFile($fileName);
                            $attachmentId = $quotingTool->createAttachFile($emailFocus, $fileName);
                            $fileName = $attachmentId . "_" . $fileName;
                            $keys_values = array();
                            $site = rtrim($site_URL, "/");
                            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
                            $subLink = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash;
                            $compactLink = preg_replace("(^(https?|ftp)://)", "", $subLink);
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
                            $pdf = $quotingTool->createPdf($record->get("content"), $record->get("header"), $record->get("footer"), $fileName, $record->get("settings_layout"), $entityId);
                        }
                        $parentTabid = getTabid("MultipleSMTP");
                        $from_email_id = $request->get("from_serveremailid");
                        if (!empty($parentTabid) && !empty($from_email_id)) {
                            require_once "modules/MultipleSMTP/mailtask.php";
                            $rsMailServer = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE userid=? AND id=? ORDER BY `sequence` ASC LIMIT 1", array($current_user->id, $from_email_id));
                            if (0 < $adb->num_rows($rsMailServer)) {
                                $mailServerId = $adb->query_result($rsMailServer, 0, "id");
                                $fromemail = $adb->query_result($rsMailServer, 0, "from_email_field");
                                $fromName = $adb->query_result($rsMailServer, 0, "name");
                            }
                            $result = multiplesmtp_sendmail($moduleName, $email, $fromName, $fromemail, $emailSubject, $emailContent, $cc, $bcc, "all", $emailId, $logo, true, $mailServerId);
                        } else {
                            $result = send_mail($moduleName, $email, $fromName, $fromEmail, $emailSubject, $emailContent, $cc, $bcc, "all", $emailId, $logo, false, "No");
                        }
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
        }
        if (!$counter) {
            $errorMessage = vtranslate("ERROR_UNABLE_TO_SEND_EMAIL", $moduleName);
            $response->setError(200, $errorMessage);
            $response->emit();
            exit;
        }
        if ($mergedProposalLink) {
            $timestamp = time();
            $newSignedRecord = array("signature" => $transactionRecord->get("signature"), "signature_name" => $transactionRecord->get("signature_name"), "signedrecord_type" => SignedRecord_Record_Model::TYPE_SENT, "signature_date" => date("Y-m-d", $timestamp), "cf_signature_time" => date("H:i:s", $timestamp), "related_to" => $transactionRecord->get("record_id"), "cf_template" => $transactionRecord->get("filename"), "transactionid" => $transactionId);
            if ($transactionRecord->get("record_id") != 0) {
                $parentRecordModel = Vtiger_Record_Model::getInstanceById($transactionRecord->get("record_id"));
                $parentAssignedTo = $parentRecordModel->get("assigned_user_id");
                $newSignedRecord["assigned_user_id"] = $parentAssignedTo;
            }
            if ($ccEmails[0] != "") {
                foreach ($ccEmails as $email) {
                    $emails[$recordId][] = $email;
                }
            }
            $emails[$recordId][] = $fromEmail;
            if (!empty($transactionId)) {
                $sql = "SELECT signedrecordid FROM vtiger_signedrecordcf WHERE transactionid=? LIMIT 0, 1";
                $rs = $adb->pquery($sql, array($transactionId));
                if (0 < $adb->num_rows($rs)) {
                    $newSignedRecordId = $adb->query_result($rs, 0, "signedrecordid");
                }
            }
            if ($newSignedRecordId) {
                $signedRecordModel = Vtiger_Record_Model::getInstanceById($newSignedRecordId);
                $signedRecordModel->set("id", $newSignedRecordId);
                $signedRecordModel->set("mode", "edit");
            } else {
                $signedRecordModel = Vtiger_Record_Model::getCleanInstance("SignedRecord");
            }
            foreach ($newSignedRecord as $field => $value) {
                $signedRecordModel->set($field, $value);
            }
            $signedRecordModel->set("signedrecord_emails1", json_encode($emails));
            $signedRecordModel->save();
            if ($transactionRecord->get("record_id") != 0) {
                $newSignedRecordId = $signedRecordModel->getId();
                $parentModuleModel = Vtiger_Record_Model::getInstanceById($transactionRecord->get("record_id"));
                $parentModuleName = $parentModuleModel->getModuleName();
                $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
                $relModuleModel = Vtiger_Module_Model::getInstance("SignedRecord");
                $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relModuleModel);
                if ($relationModel) {
                    $relationModel->addRelation($transactionRecord->get("record_id"), $signedRecordModel->getId());
                }
            }
        }
        $data["message"] = vtranslate("LBL_EMAIL_SENT", $moduleName);
        $data["total"] = $counter;
        $response->setResult($data);
        $response->emit();
    }
    /**
     * Fn - downloadPreview
     * Save PDF content to the file
     *
     * @link http://www.mpdf1.com/forum/discussion/36/how-to-automatically-save-pdf-file/p1
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function download(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get("record");
        $recordModel = new QuotingTool_Record_Model();
        $record = $recordModel->getById($recordId);
        if (!$record) {
            echo vtranslate("LBL_NOT_FOUND", $moduleName);
            exit;
        }
        $quotingTool = new QuotingTool();
        $customFunction = json_decode(html_entity_decode($record->get("custom_function")));
        $record = $record->decompileRecord(0, array("header", "content", "footer"), array(), $customFunction);
        $tabId = Vtiger_Functions::getModuleId($moduleName);
        $recordId = $recordId;
        if ($record->get("file_name")) {
            global $adb;
            $fileName = $record->get("file_name");
            if (strpos("\$record_no\$", $record->get("file_name")) != -1) {
                $rs = $adb->pquery("select fieldname from vtiger_field where tabid=" . $tabId . " and uitype=4");
                $nameFieldModuleNo = $adb->query_result($rs, 0, "fieldname");
                $recordResult = Vtiger_Record_Model::getInstanceById($recordId);
                $resultNo = $recordResult->get($nameFieldModuleNo);
                $fileName = str_replace("\$record_no\$", $resultNo, $fileName);
            }
            if (strpos("\$record_name\$", $record->get("file_name")) != -1) {
                $resultName = Vtiger_Util_Helper::getRecordName($recordId);
                $fileName = str_replace("\$record_name\$", $resultName, $fileName);
            }
            if (strpos("\$template_name\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$template_name\$", $record->get("filename"), $fileName);
            }
            $dateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
            $dateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($dateTimeByUserCreate->format("Y-m-d H:i:s"));
            list($date, $time) = explode(" ", $dateTimeByUserFormatCreate);
            $day = date("d", time($date));
            $month = date("m", time($date));
            $year = date("Y", time($date));
            if (strpos("\$day\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$day\$", $day, $fileName);
            }
            if (strpos("\$month\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$month\$", $month, $fileName);
            }
            if (strpos("\$year\$", $record->get("file_name")) != -1) {
                $fileName = str_replace("\$year\$", $year, $fileName);
            }
        } else {
            $fileName = $record->get("filename");
        }
        $fileName = $quotingTool->makeUniqueFile($fileName);
        $pdf = $quotingTool->createPdf($record->get("content"), $record->get("header"), $record->get("footer"), $fileName, $record->get("settings_layout"), $recordId);
        $fileContent = "";
        if (is_readable($pdf)) {
            $fileContent = file_get_contents($pdf);
        }
        $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
        $name = str_replace(".pdf", "", $fileName);
        $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
        $name = str_replace(" ", "_", $name);
        $fileName = str_replace("\$", "_", $name);
        $fileName = trim($fileName);
        header("Content-type: application/pdf");
        header("Pragma: public");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=" . html_entity_decode($fileName, ENT_QUOTES, vglobal("default_charset")) . ".pdf");
        header("Content-Description: PHP Generated Data");
        echo $fileContent;
    }
    /**
     * @param Vtiger_Request $request
     */
    public function duplicate(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $params = array();
        $recordModel = QuotingTool_Record_Model::getCleanInstance($module);
        $recordId = $request->get("record");
        $record = $recordModel->getById($recordId);
        if (!$record) {
            return NULL;
        }
        $data = $record->getData();
        $allow = array("filename", "module", "body", "header", "content", "footer", "description", "deleted", "email_subject", "email_content", "mapping_fields", "attachments", "settings_layout", "custom_function");
        if ($data && !empty($data)) {
            foreach ($data as $key => $value) {
                if (!in_array($key, $allow, true)) {
                    continue;
                }
                if ($key == "filename") {
                    $value = $value . "_" . vtranslate("LBL_COPY", $module);
                } else {
                    if (in_array($key, array("mapping_fields", "attachments", "settings_layout", "custom_function")) && $value) {
                        $value = html_entity_decode($value);
                    }
                }
                $params[$key] = $value;
            }
        }
        $template = $recordModel->save(NULL, $params);
        $id = $template->getId();
        if (!$id) {
            return NULL;
        }
        $historyRecordModel = new QuotingTool_HistoryRecord_Model();
        $historyParams = array("body" => $template->get("body"));
        $historyRecordModel->saveByTemplate($id, $historyParams);
        $SettingRecordModel = new QuotingTool_SettingRecord_Model();
        $SettingRecordModel->updateSettingByTemplate($id, $data["description"], "Accept", "Decline", "{\"image\":\"\",\"size\":\"auto\"}", "0");
        header("Location: index.php?module=" . $module . "&view=List");
    }
    public function get_client_ip()
    {
        $ipaddress = "";
        if (getenv("HTTP_CLIENT_IP")) {
            $ipaddress = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ipaddress = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("HTTP_X_FORWARDED")) {
                    $ipaddress = getenv("HTTP_X_FORWARDED");
                } else {
                    if (getenv("HTTP_FORWARDED_FOR")) {
                        $ipaddress = getenv("HTTP_FORWARDED_FOR");
                    } else {
                        if (getenv("HTTP_FORWARDED")) {
                            $ipaddress = getenv("HTTP_FORWARDED");
                        } else {
                            if (getenv("REMOTE_ADDR")) {
                                $ipaddress = getenv("REMOTE_ADDR");
                            } else {
                                $ipaddress = "UNKNOWN";
                            }
                        }
                    }
                }
            }
        }
        return $ipaddress;
    }
}

?>