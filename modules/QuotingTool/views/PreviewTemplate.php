<?php

include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class QuotingTool_EmailPreviewTemplate_View
 */
class QuotingTool_PreviewTemplate_View extends Vtiger_IndexAjax_View
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        global $site_URL;
        global $current_user;
        global $adb;
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
                $tabId = $data_info->settings_field_image;
                if (!empty($tabId)) {
                    $relModuleName = vtlib_getModuleNameById($tabId);
                    $recordIdForRel = $recordId;
                    if ($childModule != $relModuleName) {
                        $rs = $adb->pquery("SELECT fieldid FROM vtiger_fieldmodulerel WHERE module = '" . $childModule . "' AND relmodule = '" . $relModuleName . "'", array());
                        if (0 < $adb->num_rows($rs)) {
                            $relatedFieldId = $adb->query_result($rs, 0, 0);
                            $field_model = Vtiger_Field_Model::getInstance($relatedFieldId);
                            $field_name = $field_model->getName();
                            $related_record_model = Vtiger_Record_Model::getInstanceById($recordIdForRel);
                            $recordIdForRel = $related_record_model->get($field_name);
                        } else {
                            $sourceTabId = getTabid($childModule);
                            $rs = $adb->pquery("SELECT relationfieldid from vtiger_relatedlists where tabid=" . $tabId . " AND related_tabid = " . $sourceTabId, array());
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
            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash . "&iscreatenewrecord=true&childmodule=" . $childModule . "&preview=true";
        } else {
            $link = (string) $site . "/modules/" . $moduleName . "/proposal/index.php?record=" . $transactionId . "&session=" . $hash . "&preview=true";
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
        echo $link;
    }
}

?>