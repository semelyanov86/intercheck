<?php

require_once "vtlib/Vtiger/Module.php";
require_once "modules/com_vtiger_workflow/include.inc";
include_once "modules/QuotingTool/QuotingToolUtils.php";
/**
 * Class QuotingTool
 */
class QuotingTool extends CRMEntity
{
    public $table_name = "vtiger_quotingtool";
    public $table_index = "id";
    public $tab_name = array("vtiger_quotingtool");
    public $tab_name_index = array("vtiger_quotingtool" => "id");
    /**
     * @var array
     */
    public $enableModules = array();
    public $specialModules = array("Users");
    public $ignoreLinkModules = array("Webmails", "SMSNotifier", "Emails", "Integration", "Dashboard", "vtmessages", "vttwitter");
    public $inventoryModules = array();
    public $barcode_type_code = array("TYPE_CODE_39" => "C39", "TYPE_CODE_39_CHECKSUM" => "C39+", "TYPE_CODE_39E" => "C39E", "TYPE_CODE_39E_CHECKSUM" => "C39E+", "TYPE_CODE_93" => "C93", "TYPE_STANDARD_2_5" => "S25", "TYPE_STANDARD_2_5_CHECKSUM" => "S25+", "TYPE_INTERLEAVED_2_5" => "I25", "TYPE_INTERLEAVED_2_5_CHECKSUM" => "I25+", "TYPE_CODE_128" => "C128", "TYPE_CODE_128_A" => "C128A", "TYPE_CODE_128_B" => "C128B", "TYPE_CODE_128_C" => "C128C", "TYPE_EAN_2" => "EAN2", "TYPE_EAN_5" => "EAN5", "TYPE_EAN_8" => "EAN8", "TYPE_EAN_13" => "EAN13", "TYPE_UPC_A" => "UPCA", "TYPE_UPC_E" => "UPCE", "TYPE_MSI" => "MSI", "TYPE_MSI_CHECKSUM" => "MSI+", "TYPE_POSTNET" => "POSTNET", "TYPE_PLANET" => "PLANET", "TYPE_RMS4CC" => "RMS4CC", "TYPE_KIX" => "KIX", "TYPE_IMB" => "IMB", "TYPE_CODABAR" => "CODABAR", "TYPE_CODE_11" => "CODE11", "TYPE_PHARMA_CODE" => "PHARMA", "TYPE_PHARMA_CODE_TWO_TRACKS" => "PHARMA2T");
    /**
     * @var string
     */
    public $pdfLibLink = "https://www.vtexperts.com/files/mpdf.zip";
    /**
     * @var array
     */
    public $workflows = array("QuotingToolMailTask" => "Send Email with Quoting Tool attachments", "QuotingToolCreatePdfTask" => "Save Document from Quoting Tool");
    public $injectFields = array("Users" => array("LBL_USER_ADV_OPTIONS" => array("*"), "LBL_TAG_CLOUD_DISPLAY" => array("*"), "LBL_CURRENCY_CONFIGURATION" => array("*"), "LBL_CALENDAR_SETTINGS" => array("*"), "LBL_USERLOGIN_ROLE" => array("confirm_password", "user_password")));
    public $ignoreSpecialFields = array("starred", "tags", "taxclass");
    public $patternVar = "/\\\$([a-zA-Z0-9_]+?)\\\$/";
    public $patternEscapeCharacters = "/.*?[\\\\](.+?)/";
    const MODULE_NAME = "QuotingTool";
    public function __construct()
    {
        foreach ($this->workflows as $name => $label) {
            $this->workflows[$name] = vtranslate($label, self::MODULE_NAME);
        }
        $this->inventoryModules = getInventoryModules();
    }
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == "module.postinstall") {
            self::updateModule($modulename);
            self::addWidgetTo($modulename);
            self::installWorkflows($modulename);
            self::resetValid();
            self::updateCollation();
            self::copyPlugins();
        } else {
            if ($event_type == "module.disabled") {
                self::removeWidgetTo($modulename);
                self::removeListViewLink($modulename);
                self::disableWorkflow();
            } else {
                if ($event_type == "module.enabled") {
                    self::updateModule($modulename);
                    self::addWidgetTo($modulename);
                    self::addListViewLink($modulename);
                    self::updateLabelLink($modulename);
                    self::installWorkflows($modulename);
                    self::enableWorkflow();
                } else {
                    if ($event_type == "module.preuninstall") {
                        self::removeWidgetTo($modulename);
                        self::removePDFWidget($modulename, $this->enableModules);
                        self::removeWorkflows($modulename);
                        self::removeValid();
                    } else {
                        if ($event_type == "module.preupdate") {
                            self::removeWidgetTo($modulename);
                            self::removePDFWidget($modulename, $this->enableModules);
                        } else {
                            if ($event_type == "module.postupdate") {
                                self::updateModule($modulename);
                                self::addWidgetTo($modulename);
                                self::installWorkflows($modulename);
                                self::resetValid();
                                self::addListViewLink($modulename);
                                self::updateLabelLink($modulename);
                                self::updateButtonRelatedList();
                                self::updateCollation();
                                self::copyPlugins();
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * @param string $moduleName
     */
    public static function addWidgetTo($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        $module = Vtiger_Module::getInstance($moduleName);
        $widgetName = "Quoting Tool";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        if ($module) {
            $css_widgetType = "HEADERCSS";
            $css_widgetLabel = vtranslate($widgetName, $moduleName);
            $css_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $js_widgetType = "HEADERSCRIPT";
            $js_widgetLabel = vtranslate($widgetName, $moduleName);
            $js_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
            $js_link_2 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "Utils.js";
            $module->addLink($css_widgetType, $css_widgetLabel, $css_link);
            $module->addLink($js_widgetType, $js_widgetLabel, $js_link);
            $module->addLink($js_widgetType, $js_widgetLabel, $js_link_2);
        }
        $rs = $adb->pquery("SELECT * FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `vtiger_ws_entity` (`name`, `handler_path`, `handler_class`, `ismodule`)\r\n            VALUES (?, 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1');", array($moduleName));
            $adb->pquery("UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)", array());
        }
        $max_id = $adb->getUniqueID("vtiger_settings_field");
        $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)", array($max_id, "4", "Document Designer", "Settings area for Document Designer", "index.php?module=QuotingTool&parent=Settings&view=Settings", $max_id));
        $adb->pquery("UPDATE `vtiger_tab` SET `source` = '' WHERE `tabid` = ?", array($module->getId()));
    }
    public static function addListViewLink($moduleName)
    {
        global $adb;
        $recordModel = new QuotingTool_Record_Model();
        $allRelatedModule = $recordModel->getAllRelatedModule("");
        $sql = "SELECT `module` FROM `vtiger_quotingtool`\r\n                WHERE `deleted` != 1";
        $rs = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetchByAssoc($rs)) {
                if (!in_array($row["module"], $allRelatedModule)) {
                    array_push($allRelatedModule, $row["module"]);
                }
            }
        }
        foreach ($allRelatedModule as $module) {
            $moduleInstance = Vtiger_Module::getInstance($module);
            $checkLink = $adb->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($moduleInstance->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
            if ($adb->num_rows($checkLink) == 0) {
                $js_widgetType = "LISTVIEWMASSACTION";
                $js_widgetLabel = "Export to PDF/Email";
                $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                $moduleInstance->addLink($js_widgetType, $js_widgetLabel, $js_link);
                $moduleInstance->addLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
            }
        }
        $query = $adb->pquery("SELECT tab.`name` FROM `vtiger_relatedlists` as rl INNER JOIN vtiger_tab as tab ON tab.tabid = rl.tabid\r\n                              WHERE label = ?", array("Signed Documents"));
        if (0 < $adb->num_rows($query)) {
            $listModules = array();
            while ($row = $adb->fetchByAssoc($query)) {
                $listModules[] = $row["name"];
            }
            foreach ($listModules as $module) {
                if (!in_array($module, $allRelatedModule)) {
                    $moduleInstance = Vtiger_Module::getInstance($module);
                    $adb->pquery("DELETE FROM `vtiger_relatedlists` WHERE `tabid`=? AND `label` = ?", array($moduleInstance->getId(), "Signed Documents"));
                }
            }
        }
    }
    public static function removeListViewLink($moduleName)
    {
        global $adb;
        $sql = "DELETE FROM `vtiger_links` WHERE `linklabel` = ?";
        $adb->pquery($sql, array("Export to PDF/Email"));
    }
    public static function updateLabelLink($moduleName)
    {
        global $adb;
        $sql = "UPDATE `vtiger_links` set `linklabel` = 'Export to PDF/Email'  WHERE `linklabel` = ?";
        $adb->pquery($sql, array("Document Designer: PDF/Email"));
    }
    /**
     * @param $moduleName
     */
    public static function removeWidgetTo($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        $module = Vtiger_Module::getInstance($moduleName);
        $widgetName = "Quoting Tool";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $css_link_vt6 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $js_link_vt6 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
            $js_link_2_vt6 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "Utils.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        if ($module) {
            $css_widgetType = "HEADERCSS";
            $css_widgetLabel = vtranslate($widgetName, $moduleName);
            $css_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $js_widgetType = "HEADERSCRIPT";
            $js_widgetLabel = vtranslate($widgetName, $moduleName);
            $js_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
            $js_link_2 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "Utils.js";
            $module->deleteLink($css_widgetType, $css_widgetLabel, $css_link);
            $module->deleteLink($js_widgetType, $js_widgetLabel, $js_link);
            $module->deleteLink($js_widgetType, $js_widgetLabel, $js_link_2);
            if ($vtVersion != "vt6") {
                $module->deleteLink($css_widgetType, $css_widgetLabel, $css_link_vt6);
                $module->deleteLink($js_widgetType, $js_widgetLabel, $js_link_vt6);
                $module->deleteLink($js_widgetType, $js_widgetLabel, $js_link_2_vt6);
            }
        }
        $adb->pquery("DELETE FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?", array("Document Designer"));
    }
    /**
     * Add widget to other module.
     * @param string $moduleName
     * @param array $moduleNames
     * @param string $widgetType
     * @param string $widgetName
     */
    public function addPDFWidget($moduleName, $moduleNames, $widgetType = "DETAILVIEWSIDEBARWIDGET", $widgetName = "Quoting Tool")
    {
        if (empty($moduleNames)) {
            return NULL;
        }
        if (is_string($moduleNames)) {
            $moduleNames = array($moduleNames);
        }
        $widgetLabel = vtranslate($widgetName, $moduleName);
        $url = "module=" . $moduleName . "&view=Widget";
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetLabel, $url, "", "", "");
            }
        }
    }
    /**
     * Remove widget from other modules.
     * @param string $moduleName
     * @param array $moduleNames
     * @param string $widgetType
     * @param string $widgetName
     */
    public function removePDFWidget($moduleName, $moduleNames, $widgetType = "DETAILVIEWSIDEBARWIDGET", $widgetName = "Quoting Tool")
    {
        if (empty($moduleNames)) {
            return NULL;
        }
        if (is_string($moduleNames)) {
            $moduleNames = array($moduleNames);
        }
        $widgetLabel = vtranslate($widgetName, $moduleName);
        $url = "module=" . $moduleName . "&view=Widget";
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetType, $widgetLabel, $url);
            }
        }
    }
    /**
     * @param string $fieldName
     * @param string $moduleName
     * @param bool $restrict
     * @return string
     */
    public function convertFieldToken($fieldName, $moduleName = NULL, $restrict = true)
    {
        $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
        $supportedModulesList = array_flip($supportedModulesList);
        ksort($supportedModulesList);
        if (!$moduleName || $restrict && !in_array($moduleName, $supportedModulesList) && $moduleName != "ModComments" && !in_array($moduleName, $this->specialModules)) {
            $token = "\$" . $fieldName . "\$";
        } else {
            $token = "\$" . $moduleName . "__" . $fieldName . "\$";
            if ($fieldName == "itemNameWithDes") {
                $token = "\$" . $moduleName . "__productid\$" . "\$" . $moduleName . "__comment\$";
            }
        }
        return $token;
    }
    /**
     * @param string $token
     * @return array
     */
    public function extractFieldToken($token)
    {
        $tmp = explode("__", $token);
        list($moduleName, $fieldName) = $tmp;
        if ($tmp[2]) {
            $fieldName .= "__" . $tmp[2];
        }
        return array("moduleName" => $moduleName, "fieldName" => $fieldName);
    }
    /**
     * @param string $subject
     * @return array
     */
    public function getVarFromString($subject)
    {
        $vars = array();
        if ($subject) {
            preg_match_all($this->patternVar, $subject, $matches);
            if ($matches && 0 < count($matches)) {
                $v = array_unique($matches[1]);
                foreach ($v as $t) {
                    if (!in_array($t, $vars)) {
                        $vars[] = "\$" . $t . "\$";
                    }
                }
            }
        }
        return $vars;
    }
    /**
     * @param $subject
     * @return array
     */
    public function getFieldTokenFromString($subject)
    {
        $tokens = array();
        if ($subject) {
            preg_match_all($this->patternVar, $subject, $matches);
            if ($matches && 0 < count($matches)) {
                $tk = array_unique($matches[1]);
                foreach ($tk as $t) {
                    $extract = $this->extractFieldToken($t);
                    $moduleName = $extract["moduleName"];
                    $fieldName = $extract["fieldName"];
                    if (!array_key_exists($moduleName, $tokens)) {
                        $tokens[$moduleName] = array();
                    }
                    $needle = "\$" . $t . "\$";
                    $tokens[$moduleName][$needle] = $fieldName;
                }
            }
        }
        return $tokens;
    }
    /**
     * @param $subject
     * @return array
     */
    public function getEscapeCharactersFromString($subject)
    {
        $characters = array();
        if ($subject) {
            preg_match_all($this->patternEscapeCharacters, $subject, $matches);
            if ($matches && 0 < count($matches)) {
                $m = array_unique($matches[1]);
                foreach ($m as $c) {
                    if (!in_array($c, $characters)) {
                        $needle = "\\" . $c;
                        $characters[$needle] = $c;
                    }
                }
            }
        }
        return $characters;
    }
    /**
     * @param $subject
     * @return array
     */
    public function getEmailFromString($subject)
    {
        $email = "";
        if ($subject) {
            $pattern = "/\\((.*?)\\)/";
            preg_match_all($pattern, $subject, $matches);
            if ($matches && 0 < count($matches)) {
                $email = $matches[1][0] ? $matches[1][0] : $subject;
            }
        }
        return $email;
    }
    /**
     * @param $tokens
     * @param $record
     * @param $content
     * @return mixed
     */
    public function mergeBlockTokens($tokens, $record, $content)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        $crmid = "crmid";
        $inventoryModules = getInventoryModules();
        $productModules = array("Products", "Services");
        $currencyFieldsList = array("adjustment", "grandTotal", "hdnSubTotal", "preTaxTotal", "tax_totalamount", "shtax_totalamount", "discountTotal_final", "discount_amount_final", "shipping_handling_charge", "totalAfterDiscount");
        $blockStartTemplates = array("#PRODUCTBLOC_START#", "#SERVICEBLOC_START#", "#PRODUCTSERVICEBLOC_START#");
        $blockEndTemplates = array("#PRODUCTBLOC_END#", "#SERVICEBLOC_END#", "#PRODUCTSERVICEBLOC_END#");
        $blockTemplates = array_merge($blockStartTemplates, $blockEndTemplates);
        $dataTableType = NULL;
        $currencyFieldsList2 = array("taxTotal", "netPrice", "listPrice", "unitPrice", "productTotal", "discountTotal", "discount_amount");
        foreach ($html->find("table") as $table) {
            $dataTableType = $table->attr["data-table-type"];
            if (!$dataTableType || $dataTableType != "pricing_table") {
                continue;
            }
            $pdfContentModel = new QuotingTool_PDFContent_Model();
            if ($record == 0) {
                return $content;
            }
            $recordModel = Vtiger_Record_Model::getInstanceById($record);
            $moduleName = $recordModel->getModuleName();
            $templateId = $_REQUEST["template_id"];
            $quotingToolRecordModel = new QuotingTool_SettingRecord_Model();
            $settingTemplate = $quotingToolRecordModel->findByTemplateId($templateId);
            $dateFormat = $settingTemplate->get("date_format");
            $dateFormat = str_replace(array("yyyy", "mm", "dd"), array("Y", "m", "d"), $dateFormat);
            $userFormat = DateTimeField::getPHPDateFormat();
            $table->removeAttribute("data-info");
            $isTemplateStart = false;
            $isTemplateEnd = false;
            $newHeader = array();
            $newBody = array();
            $newFooter = array();
            $thead = NULL;
            $tbody = NULL;
            $tfoot = NULL;
            $newHeaderTokens = array();
            $newBodyTokens = array();
            $newFooterTokens = array();
            $dataOddStyle = $table->attr["data-odd-style"];
            $dataEvenStyle = $table->attr["data-even-style"];
            foreach ($table->find("tr") as $row) {
                $isNormalRow = true;
                foreach ($row->children() as $cell) {
                    $cellText = trim($cell->plaintext);
                    if (!in_array($cellText, $blockTemplates)) {
                        continue;
                    }
                    $isNormalRow = false;
                    $cell->parent->outertext = $cellText;
                    if (in_array($cellText, $blockStartTemplates)) {
                        $isTemplateStart = true;
                        break;
                    }
                    if (in_array($cellText, $blockEndTemplates)) {
                        $isTemplateEnd = true;
                        break;
                    }
                }
                if ($isNormalRow) {
                    if (!$isTemplateStart) {
                        $newHeader[] = $row;
                        $newHeaderTokens = array_merge($newHeaderTokens, $this->getFieldTokenFromString($row->outertext));
                    } else {
                        if ($isTemplateStart && !$isTemplateEnd) {
                            $newBody[] = $row;
                            $newBodyTokens = array_replace_recursive($newBodyTokens, $this->getFieldTokenFromString($row->outertext));
                        } else {
                            if ($isTemplateEnd) {
                                $newFooter[] = $row;
                                $newFooterTokens = array_merge($newFooterTokens, $this->getFieldTokenFromString($row->outertext));
                            }
                        }
                    }
                }
                $parent = $row->parent();
                if ($thead === NULL && $parent->tag == "thead") {
                    $thead = $parent;
                } else {
                    if ($tbody === NULL && $parent->tag == "tbody") {
                        $tbody = $parent;
                    } else {
                        if ($tfoot === NULL && $parent->tag == "tfoot") {
                            $tfoot = $parent;
                        }
                    }
                }
            }
            $innertext = "";
            if (in_array($moduleName, $inventoryModules)) {
                $recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
                $products = $recordModel->getProducts();
                if ($products && 0 < count($products)) {
                    $isIndividual = $products[1]["final_details"]["taxtype"];
                }
            }
            $tmpHead = "";
            foreach ($newHeader as $row) {
                foreach ($row->children() as $cell) {
                    if ($cell->getAttribute("data-info") == "isTax" && $isIndividual != "individual") {
                        $cell->outertext = "";
                    }
                }
                $newTheadRowsText = $row->outertext;
                foreach ($newHeaderTokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        $needValue = $recordModel->getDisplayValue($f, $record);
                        $newTheadRowsText = str_replace($k, $needValue, $newTheadRowsText);
                    }
                }
                $tmpHead .= $newTheadRowsText;
            }
            if ($thead !== NULL) {
                $thead->innertext = $tmpHead;
                $innertext .= $thead->outertext;
            } else {
                if ($tbody !== NULL) {
                    $innertext .= "<thead>" . $tmpHead . "</thead>";
                } else {
                    $innertext .= $tmpHead;
                }
            }
            $tmpBody = "";
            if ($tbody !== NULL) {
                $dataOddStyle = $tbody->attr["data-odd-style"];
                $dataEvenStyle = $tbody->attr["data-even-style"];
            }
            $dataOddStyle = $dataOddStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataOddStyle))) : "";
            $dataEvenStyle = $dataEvenStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataEvenStyle))) : "";
            $final_details = array();
            if (in_array($moduleName, $inventoryModules)) {
                $recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
                $products = $recordModel->getProducts();
                if ($products && 0 < count($products)) {
                    $final_details = $products[1]["final_details"];
                    $items = $pdfContentModel->getLineItemsAndTotal($record);
                    if ($items && 0 < count($items)) {
                        $products = $this->mergeRelatedProductWithQueryProduct($products, $items);
                    }
                    $counter = 0;
                    foreach ($products as $k => $value) {
                        $even = ++$counter % 2 == 0;
                        $cloneTbodyRowTokens = $newBodyTokens;
                        foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                            $moduleModel = Vtiger_Module_Model::getInstance($tModuleName);
                            foreach ($tFields as $fToken => $fName) {
                                if ($fName == $crmid) {
                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $record;
                                } else {
                                    if ($fName == "productid") {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $value["productname"];
                                    } else {
                                        if ($fName == "qty_per_unit" || $fName == "unit_price" || $fName == "weight" || $fName == "commissionrate" || $fName == "qtyinstock" || $fName == "quantity" || $fName == "listprice" || $fName == "tax1" || $fName == "tax2" || $fName == "tax3" || $fName == "discount_amount" || $fName == "discount_percent" || in_array($fName, $currencyFieldsList) || in_array($fName, $currencyFieldsList2)) {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = Vtiger_Currency_UIType::transformDisplayValue($value[$fName], NULL, true);
                                            if ($fName == "discount_amount" && $value["discount_amount"] == "") {
                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = Vtiger_Currency_UIType::transformDisplayValue($value["discountTotal"], NULL, true);
                                            }
                                        } else {
                                            if ($fName == "sequence_no") {
                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = $value[$fName];
                                            } else {
                                                if (in_array($tModuleName, $productModules)) {
                                                    $needValue = $value[$fName];
                                                    if (is_numeric($needValue) && is_float($needValue)) {
                                                        $needValue = Vtiger_Currency_UIType::transformDisplayValue($needValue, NULL, true);
                                                    }
                                                    if ($fName == "description") {
                                                        $needValue = nl2br($value["psdescription"]);
                                                    }
                                                    if ($needValue == "") {
                                                        $itemId = $value["productid"] != NULL ? $value["productid"] : $value["serviceid"];
                                                        $productRecordModel = Vtiger_Record_Model::getInstanceById($itemId);
                                                        $needValue = $productRecordModel->getDisplayValue($fName);
                                                    }
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $needValue;
                                                } else {
                                                    $fieldModel = $moduleModel->getField($fName);
                                                    if ($fieldModel) {
                                                        $fieldDataType = $fieldModel->getFieldDataType();
                                                        if ($fieldModel->get("table") == "vtiger_inventoryproductrel") {
                                                            $needValue = $value[$fName];
                                                            if (is_numeric($needValue) && is_float($needValue)) {
                                                                $needValue = Vtiger_Currency_UIType::transformDisplayValue($needValue, NULL, true);
                                                            } else {
                                                                if ($fieldDataType == "text") {
                                                                    $needValue = nl2br($needValue);
                                                                }
                                                            }
                                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = $needValue;
                                                        } else {
                                                            $data = $recordModel->getDisplayValue($fName, $recordModel->getId());
                                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = $data;
                                                            if ($fName == "currency_id") {
                                                                $currencyInfo = $recordModel->getCurrencyInfo();
                                                                $currencySymbol = $currencyInfo["currency_symbol"];
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = $currencySymbol;
                                                            }
                                                            if (($fieldDataType == "date" || $fieldDataType == "datetime") && $data != "" && $dateFormat != "") {
                                                                if ($fieldDataType == "datetime") {
                                                                    $valuesList = explode(" ", $data);
                                                                    if (count($valuesList) == 1) {
                                                                        $data = "";
                                                                    }
                                                                    $newVal = $valuesList[0];
                                                                    $datetime = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $datetime;
                                                                } else {
                                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = DateTime::createFromFormat($userFormat, $data)->format($dateFormat);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($newBody as $row) {
                            $row->setAttribute("data-row-number", $counter);
                            foreach ($row->children() as $cell) {
                                if ($cell->getAttribute("data-info") == "isTax" && $isIndividual != "individual") {
                                    $cell->outertext = "";
                                }
                                $style = $even ? $dataEvenStyle : $dataOddStyle;
                                $oldStyle = $cell->getAttribute("style");
                                $newStyle = NULL;
                                if (!$oldStyle) {
                                    $newStyle = $style;
                                } else {
                                    $oldStyle = trim($oldStyle);
                                    if (QuotingToolUtils::endsWith($oldStyle, ";")) {
                                        $newStyle = $oldStyle . " " . $style;
                                    } else {
                                        $newStyle = $oldStyle . "; " . $style;
                                    }
                                }
                                $newStyle = trim($newStyle);
                                if ($newStyle !== "") {
                                    $cell->setAttribute("style", $newStyle);
                                }
                            }
                            $cloneTbodyRowsTemplate = $row->outertext;
                            foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                                foreach ($tFields as $k => $f) {
                                    if (strpos($k, "cf_acf_rtf") !== false) {
                                        $f = htmlspecialchars_decode($f);
                                    }
                                    $cloneTbodyRowsTemplate = str_replace($k, $f, $cloneTbodyRowsTemplate);
                                }
                            }
                            $tmpBody .= $cloneTbodyRowsTemplate;
                        }
                    }
                }
            } else {
                foreach ($newBody as $row) {
                    $newTbodyRowsText = $row->outertext;
                    foreach ($newBodyTokens as $tModuleName => $tFields) {
                        foreach ($tFields as $k => $f) {
                            if (strpos($k, "cf_acf_rtf") !== false) {
                                $needValue = htmlspecialchars_decode($recordModel->getDisplayValue($f, $record));
                            } else {
                                $needValue = $recordModel->getDisplayValue($f, $record);
                            }
                            $newTbodyRowsText = str_replace($k, $needValue, $newTbodyRowsText);
                        }
                    }
                    $tmpBody .= $newTbodyRowsText;
                }
            }
            if ($tbody !== NULL) {
                $tbody->innertext = $tmpBody;
                $innertext .= $tbody->outertext;
            } else {
                $innertext .= $tmpBody;
            }
            $tmpFoot = "";
            foreach ($newFooter as $row) {
                $newTfootRowsText = $row->outertext;
                if ((strpos($newTfootRowsText, "\$" . $moduleName . "__preTaxTotal\$") !== false || strpos($newTfootRowsText, "\$" . $moduleName . "__tax_totalamount\$") !== false) && $final_details["taxtype"] == "individual") {
                    continue;
                }
                foreach ($tokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        $needValue = NULL;
                        if (in_array($moduleName, $inventoryModules) && isset($final_details[$f])) {
                            $needValue = Vtiger_Currency_UIType::transformDisplayValue($final_details[$f], NULL, true);
                            $needValue = nl2br($needValue);
                        } else {
                            if ($f == $crmid) {
                                $needValue = $record;
                            } else {
                                if ($recordModel->get("hdnDiscountPercent") != "" && $f == "hdnDiscountAmount") {
                                    $needValue = $recordModel->get("hdnSubTotal") * $recordModel->get("hdnDiscountPercent") / 100;
                                } else {
                                    $needValue = $recordModel->getDisplayValue($f, $record);
                                    if ($f == "currency_id") {
                                        $currencyInfo = $recordModel->getCurrencyInfo();
                                        $currencySymbol = $currencyInfo["currency_symbol"];
                                        $needValue = $currencySymbol;
                                    }
                                }
                            }
                        }
                        $newTfootRowsText = str_replace($k, $needValue, $newTfootRowsText);
                    }
                }
                $tmpFoot .= $newTfootRowsText;
            }
            if ($tfoot !== NULL) {
                $tfoot->innertext = $tmpFoot;
            } else {
                if ($tbody !== NULL) {
                }
            }
            $table->innertext = $innertext;
            $tfootInnertext = $tfoot !== NULL ? $tfoot->innertext : $tmpFoot;
            $hasTbody = $tbody !== NULL;
            if ($hasTbody) {
                $newTable = clone $table;
                $newTable->innertext = $tfootInnertext;
                $table->outertext = $table->outertext . $newTable->outertext;
            }
            $content = $html->save();
        }
        return $content;
    }
    /**
     * @param string $content
     * @param array $attributes
     * @return string
     */
    public function cleanAttributes($content, $attributes)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        foreach ($attributes as $attribute) {
            foreach ($html->find("[" . $attribute . "]") as $tag) {
                $tag->setAttribute($attribute, "cleaned");
            }
        }
        $content = $html->save();
        return $content;
    }
    public function numberFormat($number)
    {
        if (is_numeric($number)) {
            $userModel = Users_Record_Model::getCurrentUserModel();
            $currency_grouping_separator = $userModel->get("currency_grouping_separator");
            $currency_decimal_separator = $userModel->get("currency_decimal_separator");
            $no_of_decimal_places = getCurrencyDecimalPlaces();
            return number_format($number, $no_of_decimal_places, $currency_decimal_separator, $currency_grouping_separator);
        }
        return $number;
    }
    /**
     * @param $tokens
     * @param $record
     * @param $content
     * @return mixed
     */
    public function mergeQuoterBlockTokens($tokens, $record, $content)
    {
        global $adb;
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        $crmid = "crmid";
        $blockStartTemplates = array("#PRODUCTBLOC_START#", "#SERVICEBLOC_START#", "#PRODUCTSERVICEBLOC_START#");
        $blockEndTemplates = array("#PRODUCTBLOC_END#", "#SERVICEBLOC_END#", "#PRODUCTSERVICEBLOC_END#");
        $blockTemplates = array_merge($blockStartTemplates, $blockEndTemplates);
        $dataTableType = NULL;
        foreach ($html->find("table") as $table) {
            $dataTableType = $table->attr["data-table-type"];
            if (!$dataTableType || $dataTableType != "pricing_table_idc") {
                continue;
            }
            $attrTable = $table->attr;
            $attrText = "";
            foreach ($attrTable as $attr => $val) {
                if ($attr == "data-info" || $attr == "style") {
                    continue;
                }
                $attrText .= $attr . "=" . $val . " ";
            }
            $inventoryModules = getInventoryModules();
            if ($record == 0) {
                return $content;
            }
            $recordModel = Vtiger_Record_Model::getInstanceById($record);
            $moduleName = $recordModel->getModuleName();
            $dataInfo = $table->getAttribute("data-info");
            if ($dataInfo) {
                $dataInfo = json_decode(html_entity_decode($table->getAttribute("data-info")), true);
            }
            $table->removeAttribute("data-info");
            $isTemplateStart = false;
            $isTemplateEnd = false;
            $newHeader = array();
            $newBody = array();
            $newFooter = array();
            $thead = NULL;
            $tbody = NULL;
            $tfoot = NULL;
            $newHeaderTokens = array();
            $newBodyTokens = array();
            $newFooterTokens = array();
            $dataOddStyle = $table->attr["data-odd-style"];
            $dataEvenStyle = $table->attr["data-even-style"];
            $quoterModel = new Quoter_Module_Model();
            $quoterSettings = $quoterModel->getSettingForModule($moduleName, true);
            if (method_exists($quoterModel, "getSectionGroupSetting")) {
                $sectionsGroupSetting = $quoterModel->getSectionGroupSetting($moduleName);
                $headerSettings = $quoterModel->getAllHeadingSettings();
                $sectionFrom1 = false;
                if ($headerSettings[$moduleName]->section_from_1 == 1) {
                    $sectionFrom1 = true;
                }
            }
            $quoterCustomSettings = array();
            foreach ($quoterSettings as $key => $val) {
                if ($quoterModel->isCustomFields($val->columnName)) {
                    $quoterCustomSettings[$key] = $val->columnName;
                }
            }
            $totalSettings = $quoterModel->getTotalFieldsSetting($moduleName);
            $quoterRecordModel = new Quoter_Record_Model();
            if (in_array($moduleName, $inventoryModules)) {
                $products = $quoterRecordModel->getProducts($moduleName, $record, $quoterSettings);
                if ($products && 0 < count($products)) {
                    $isIndividual = $products[1]["final_details"]["taxtype"];
                }
            }
            foreach ($table->find("tr") as $row) {
                $isNormalRow = true;
                foreach ($row->children() as $cell) {
                    $cellText = trim($cell->plaintext);
                    if (!in_array($cellText, $blockTemplates)) {
                        continue;
                    }
                    $isNormalRow = false;
                    $cell->parent->outertext = $cellText;
                    if (in_array($cellText, $blockStartTemplates)) {
                        $isTemplateStart = true;
                        break;
                    }
                    if (in_array($cellText, $blockEndTemplates)) {
                        $isTemplateEnd = true;
                        break;
                    }
                }
                if ($isNormalRow) {
                    if (!$isTemplateStart) {
                        $newHeader[] = $row;
                        $newHeaderTokens = array_merge($newHeaderTokens, $this->getFieldTokenFromString($row->outertext));
                    } else {
                        if ($isTemplateStart && !$isTemplateEnd) {
                            $newBody[] = $row;
                            $newBodyTokens = array_replace_recursive($newBodyTokens, $this->getFieldTokenFromString($row->outertext));
                        } else {
                            if ($isTemplateEnd) {
                                $newFooter[] = $row;
                                $newFooterTokens = array_merge($newFooterTokens, $this->getFieldTokenFromString($row->outertext));
                            }
                        }
                    }
                }
                $parent = $row->parent();
                if ($thead === NULL && $parent->tag == "thead") {
                    $thead = $parent;
                } else {
                    if ($tbody === NULL && $parent->tag == "tbody") {
                        $tbody = $parent;
                    } else {
                        if ($tfoot === NULL && $parent->tag == "tfoot") {
                            $tfoot = $parent;
                        }
                    }
                }
            }
            $innertext = "";
            $tmpHead = "";
            $countCol = 0;
            foreach ($newHeader as $row) {
                foreach ($row->children() as $cell) {
                    $countCol++;
                    if ($cell->getAttribute("data-info") == "isTax" && $isIndividual != "individual") {
                        $cell->outertext = "";
                        $countCol--;
                    }
                }
                $newTheadRowsText = $row->outertext;
                foreach ($newHeaderTokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        if (strpos($k, "cf_acf_rtf") !== false) {
                            $needValue = htmlspecialchars_decode($recordModel->getDisplayValue($f, $record));
                        } else {
                            $needValue = $recordModel->getDisplayValue($f, $record);
                        }
                        $newTheadRowsText = str_replace($k, $needValue, $newTheadRowsText);
                    }
                }
                $tmpHead .= $newTheadRowsText;
            }
            if ($thead !== NULL) {
                $thead->innertext = $tmpHead;
                $innertext .= $thead->outertext;
            } else {
                if ($tbody !== NULL) {
                    $innertext .= "<thead>" . $tmpHead . "</thead>";
                } else {
                    $innertext .= $tmpHead;
                }
            }
            $tmpBody = "";
            if ($tbody !== NULL) {
                $dataOddStyle = $tbody->attr["data-odd-style"];
                $dataEvenStyle = $tbody->attr["data-even-style"];
            }
            $dataOddStyle = $dataOddStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataOddStyle))) : "";
            $dataEvenStyle = $dataEvenStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataEvenStyle))) : "";
            $final_details = array();
            $sectionGroupsSelected = "";
            if (in_array($moduleName, $inventoryModules)) {
                $products = $quoterRecordModel->getProducts($moduleName, $record, $quoterSettings);
                $fieldSettings = $dataInfo["settings"]["item_fields"];
                if ($dataInfo["settings"]["include_section_groups"] && !empty($sectionsGroupSetting)) {
                    $sectionGroupsSelected = $dataInfo["settings"]["section_groups"];
                    $overrideColumn = $dataInfo["settings"]["override_column_configuration"];
                    if ($products[1]["is_sections_group1"]) {
                        $styleConfig = $dataInfo["settings"]["style_section_groups"];
                        $background = $dataInfo["settings"]["theme"]["settings"]["thead"]["style"]["background-color"];
                        $innertext = "<tr class=\"section\">\r\n                                    <td colspan=\"" . $countCol . "\" style=\" " . $dataOddStyle . "; border: 1px solid " . $background . ";" . $styleConfig . " \">\r\n                                        <span style=\"font-weight: bold;\">" . $products[1]["new_section_value1"] . "</span>\r\n                                    </td>\r\n                                </tr>";
                        $sectionGroupName = $products[1]["section1"];
                        foreach ($sectionsGroupSetting as $key => $section) {
                            if ($section["key"] == $sectionGroupName) {
                                $excludeFields = $section["exclude_field"];
                                break;
                            }
                        }
                        $excludeFieldsLabel = array();
                        $excludeFieldsToken = array();
                        if (0 < count($excludeFields)) {
                            foreach ($fieldSettings as $idx => $field) {
                                if (in_array($field["name"], $excludeFields)) {
                                    $excludeFieldsLabel[] = $field["label"];
                                    $excludeFieldsToken[] = $field["token"];
                                }
                            }
                            $cloneNewHeader = str_get_html($tmpHead);
                            $index = 0;
                            foreach ($cloneNewHeader->find("th") as $th) {
                                if (in_array($th->getAttribute("fieldname"), $excludeFields) && $overrideColumn == false) {
                                    if ($index == 0) {
                                        $th->outertext = "";
                                    } else {
                                        $th->outertext = "";
                                        $prevElem = $cloneNewHeader->find("th")[$index - 1];
                                        $colspanPrev = $prevElem->getAttribute("colspan");
                                        if ($colspanPrev == "") {
                                            $colspanPrev = 1;
                                        }
                                        $prevElem->setAttribute("colspan", $colspanPrev + 1);
                                    }
                                }
                                $index++;
                            }
                            $innertext .= $cloneNewHeader->outertext;
                            if ($sectionGroupsSelected != "" && $sectionGroupsSelected != $sectionGroupName) {
                                $innertext = "";
                            }
                            $isFirstRunning = true;
                        }
                    }
                }
                if ($products && 0 < count($products)) {
                    $isIndividual = $products[1]["final_details"]["taxtype"];
                    $final_details = $totalValues = $quoterRecordModel->getTotalValues($moduleName, array_keys($totalSettings), $record);
                    $counter = 0;
                    $pdfContentModel = new QuotingTool_PDFContent_Model();
                    $items = $pdfContentModel->getLineItemsAndTotal($record);
                    $excludeFieldsToken = array();
                    $sequence = 0;
                    $sectionGroupValue = "";
                    $vteItemsModel = Vtiger_Module_Model::getInstance("VTEItems");
                    foreach ($products as $k => $value) {
                        if ($value["section" . $k] != "") {
                            $sectionGroupValue = $value["section" . $k];
                        }
                        if ($sectionGroupsSelected != "" && $sectionGroupValue != $sectionGroupsSelected) {
                            continue;
                        }
                        $sequence++;
                        $even = ++$counter % 2 == 0;
                        $cloneTbodyRowTokens = $newBodyTokens;
                        foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                            if ($tModuleName == "Products" || $tModuleName == "Services") {
                                $itemId = $items[$k - 1]["productid"] != NULL ? $items[$k - 1]["productid"] : $items[$k - 1]["serviceid"];
                                $productRecordModel = Vtiger_Record_Model::getInstanceById($itemId);
                                foreach ($tFields as $fToken => $fName) {
                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $productRecordModel->getDisplayValue($fName);
                                    if ($fName == "productid") {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $productRecordModel->getId();
                                    }
                                }
                            } else {
                                foreach ($tFields as $fToken => $fName) {
                                    $fkName = $fName . $k;
                                    if ($fName == $crmid) {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $record;
                                    } else {
                                        if ($fName == "productid" || $fName == "related_to") {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = $value["item_name" . $k];
                                        } else {
                                            $needValue = in_array($fName, $quoterCustomSettings) ? $value[$fkName]->get("fieldvalue") : $value[$fkName];
                                            if ($fName == "tax_total") {
                                                $sumTaxDataValue = 0;
                                                if ($isIndividual == "individual") {
                                                    foreach ($value["taxes"] as $key => $tax_data) {
                                                        $sumTaxDataValue = $sumTaxDataValue + $tax_data["percentage"] * $value["total" . $k] / 100;
                                                    }
                                                    $needValue = $sumTaxDataValue;
                                                } else {
                                                    $needValue = 0;
                                                }
                                            }
                                            if (is_numeric($needValue) && !in_array($fName, array("sequence")) && !in_array($fName, $quoterCustomSettings)) {
                                                $needValue = $this->numberFormat($needValue);
                                            }
                                            if ($fName == "quantity") {
                                                $needValue = intval($needValue);
                                            }
                                            if (in_array($fName, $quoterCustomSettings)) {
                                                $itemsFieldModel = Vtiger_Field_Model::getInstance($fName, $vteItemsModel);
                                                if ($itemsFieldModel) {
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $itemsFieldModel->getDisplayValue($needValue);
                                                } else {
                                                    $tableName = sprintf("quoter_%s_settings", strtolower($moduleName));
                                                    $rs = $adb->pquery("SELECT " . $fName . " FROM " . $tableName . " WHERE module = ?", array($moduleName));
                                                    if (0 < $adb->num_rows($rs)) {
                                                        $data = $adb->fetchByAssoc($rs, 0);
                                                        $productField = "";
                                                        foreach ($data as $key => $val) {
                                                            $columnSettings = json_decode(decode_html($val));
                                                            $productField = $columnSettings->productField;
                                                        }
                                                        if ($productField != "") {
                                                            $productModuleModel = Vtiger_Module_Model::getInstance("Products");
                                                            $fieldModel = $productModuleModel->getField($productField);
                                                            $fieldDataType = $fieldModel->getFieldDataType();
                                                            if ($fieldDataType == "date") {
                                                                $needValue = DateTimeField::convertToUserFormat($needValue);
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = $needValue;
                                                            }
                                                            if ($fieldDataType = "multipicklist") {
                                                                $needValue = str_ireplace(" |##| ", ", ", $needValue);
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = $needValue;
                                                            } else {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = nl2br($needValue);
                                                            }
                                                        } else {
                                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = nl2br($needValue);
                                                        }
                                                    }
                                                }
                                            } else {
                                                $cloneTbodyRowTokens[$tModuleName][$fToken] = nl2br($needValue);
                                                if ($dataInfo["settings"]["include_section_groups"] && !empty($sectionsGroupSetting) && $value["is_sections_group" . $k] && $sectionFrom1 == true) {
                                                    $sequence = 1;
                                                }
                                                if ($fName == "sequence") {
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $sequence;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($newBody as $row) {
                            $maxCol = 0;
                            $row->setAttribute("data-row-number", $counter);
                            foreach ($row->children() as $cell) {
                                $maxCol++;
                                if ($cell->getAttribute("data-info") == "isTax" && $isIndividual != "individual") {
                                    $cell->outertext = "";
                                    $maxCol--;
                                }
                                $style = $even ? $dataEvenStyle : $dataOddStyle;
                                $oldStyle = $cell->getAttribute("style");
                                $newStyle = NULL;
                                if (!$oldStyle) {
                                    $newStyle = $style;
                                } else {
                                    $oldStyle = trim($oldStyle);
                                    if (QuotingToolUtils::endsWith($oldStyle, ";")) {
                                        $newStyle = $oldStyle . " " . $style;
                                    } else {
                                        $newStyle = $oldStyle . "; " . $style;
                                    }
                                }
                                $newStyle = trim($newStyle);
                                if ($newStyle !== "") {
                                    $cell->setAttribute("style", $newStyle);
                                }
                            }
                            $cloneTbodyRowsTemplate = $row->outertext;
                            $excludeFields = "";
                            $excludeFieldsLabel = array();
                            if ($dataInfo["settings"]["include_section_groups"] && !empty($sectionsGroupSetting)) {
                                if ($value["is_sections_group" . $k]) {
                                    $excludeFieldsToken = array();
                                    $sectionGroupName = $value["section" . $k];
                                    foreach ($sectionsGroupSetting as $key => $section) {
                                        if ($section["key"] == $sectionGroupName) {
                                            $excludeFields = $section["exclude_field"];
                                            break;
                                        }
                                    }
                                    if (0 < count($excludeFields)) {
                                        foreach ($fieldSettings as $idx => $field) {
                                            if (in_array($field["name"], $excludeFields)) {
                                                $excludeFieldsToken[] = $field["token"];
                                                $excludeFieldsLabel[] = $field["label"];
                                            }
                                        }
                                    }
                                }
                                $index = 0;
                                $cloneBody = str_get_html($cloneTbodyRowsTemplate);
                                $reject = 0;
                                $isMerge = false;
                                if ($overrideColumn == false) {
                                    foreach ($cloneBody->find("td") as $th) {
                                        foreach ($excludeFieldsToken as $token) {
                                            if (strpos($th->innertext, $token) !== false) {
                                                if ($index == 0) {
                                                    $th->outertext = "";
                                                } else {
                                                    $isMerge = true;
                                                    $reject++;
                                                    $th->outertext = "";
                                                    $prevElem = $cloneBody->find("td")[$index - $reject];
                                                    $colspanPrev = $prevElem->getAttribute("colspan");
                                                    if ($colspanPrev == "") {
                                                        $colspanPrev = 1;
                                                    }
                                                    $prevElem->setAttribute("colspan", $colspanPrev + 1);
                                                }
                                                break;
                                            }
                                        }
                                        if (!$isMerge) {
                                            $reject = 0;
                                        }
                                        $isMerge = false;
                                        $index++;
                                    }
                                }
                                $cloneTbodyRowsTemplate = $cloneBody->outertext;
                            }
                            foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                                foreach ($tFields as $kReplace => $fNameReplace) {
                                    if (in_array($kReplace, $excludeFieldsToken) && $overrideColumn == false) {
                                        $fNameReplace = "";
                                    }
                                    if (strpos($kReplace, "cf_acf_rtf") !== false) {
                                        $fNameReplace = htmlspecialchars_decode($fNameReplace);
                                    }
                                    $cloneTbodyRowsTemplate = str_replace("\\\$ " . $kReplace, $fNameReplace, $cloneTbodyRowsTemplate);
                                    $cloneTbodyRowsTemplate = str_replace($kReplace, $fNameReplace, $cloneTbodyRowsTemplate);
                                }
                            }
                            $cellStyle = "";
                            if ($dataInfo["settings"]["theme"]["settings"]["cell"]["style"]) {
                                $cellStyles = $dataInfo["settings"]["theme"]["settings"]["cell"]["style"];
                                foreach ($cellStyles as $attrName => $attrVal) {
                                    $cellStyle .= $attrName . ":" . $attrVal . ";";
                                }
                            }
                            $theadStyle = "";
                            if ($dataInfo["settings"]["theme"]["settings"]["thead"]["style"]) {
                                $theadStyles = $dataInfo["settings"]["theme"]["settings"]["thead"]["style"];
                                foreach ($theadStyles as $attrName => $attrVal) {
                                    $theadStyle .= $attrName . ":" . $attrVal . ";";
                                }
                            }
                            if (!$dataInfo["settings"]["include_section_groups"] && $dataInfo["settings"]["include_sections"] && !empty($value["section" . $k])) {
                                $tmpBody .= "<tr class=\"section\">\r\n                                    <td colspan=\"" . $maxCol . "\" style=\" " . $dataOddStyle . $cellStyle . " \">\r\n                                        <span style=\"font-weight: bold;\">" . $value["section" . $k] . "</span>\r\n                                    </td>\r\n                                </tr>";
                            }
                            if ($dataInfo["settings"]["include_section_groups"] && !empty($sectionsGroupSetting) && $k != 1 && $value["is_sections_group" . $k]) {
                                $styleConfig = $dataInfo["settings"]["style_section_groups"];
                                $height = $sectionGroupsSelected != "" ? "10px" : "30px";
                                $background = $dataInfo["settings"]["theme"]["settings"]["thead"]["style"]["background-color"];
                                $trSectionGroup = "</table><table " . $attrText . " style =\"" . $attrTable["style"] . "\"><thead><tr>\r\n                                    <td colspan=\"" . $countCol . "\" style=\"border: 1px solid " . $background . ";border-left: 1px solid white;border-right: 2px solid white;border-top: 1px solid white;\" height =\"" . $height . "\" >\r\n                                    </td>\r\n                                    </tr>\r\n                                    <tr class=\"section\">\r\n                                    <td colspan=\"" . $countCol . "\" style=\" " . $dataOddStyle . "; border: 1px solid " . $background . ";" . $styleConfig . " \">\r\n                                        <span style=\"font-weight: bold;\">" . $value["new_section_value" . $k] . "</span>\r\n                                    </td>\r\n                                    </tr>";
                                if (0 < count($excludeFields)) {
                                    $cloneNewHeader = str_get_html($tmpHead);
                                    $index = 0;
                                    $reject = 0;
                                    foreach ($cloneNewHeader->find("th") as $th) {
                                        if (in_array($th->getAttribute("fieldname"), $excludeFields) && $overrideColumn == false) {
                                            if ($index == 0) {
                                                $th->outertext = "";
                                            } else {
                                                $reject++;
                                                $th->outertext = "";
                                                $prevElem = $cloneNewHeader->find("th")[$index - $reject];
                                                $colspanPrev = $prevElem->getAttribute("colspan");
                                                if ($colspanPrev == "") {
                                                    $colspanPrev = 1;
                                                }
                                                $prevElem->setAttribute("colspan", $colspanPrev + 1);
                                            }
                                        } else {
                                            $reject = 0;
                                        }
                                        $index++;
                                    }
                                    $trSectionGroup .= $cloneNewHeader->outertext . "</thead>";
                                    $cloneTbodyRowsTemplate = $trSectionGroup . $cloneTbodyRowsTemplate;
                                    $isFirstRunning = true;
                                }
                            }
                            $tmpBody .= $cloneTbodyRowsTemplate;
                            if ($dataInfo["settings"]["include_running_totals"] && !empty($value["running_item_value" . $k])) {
                                foreach ($value["running_item_value" . $k] as $runningItemName => $runningItem) {
                                    $styleRunning = "";
                                    if ($isFirstRunning) {
                                        $styleRunning = "margin-top: 15px";
                                    }
                                    $isFirstRunning = false;
                                    $tmpBody .= "<tr class=\"running_item\">\r\n                                        <td colspan=\"" . $maxCol . "\" style=\"text-align: right; padding:0; " . $cellStyle . "\">\r\n                                        <table width=\"100%\" style=\"" . $styleRunning . "\">\r\n                                        <tr><td><span style=\"font-weight: bold;\">" . vtranslate($totalSettings[$runningItemName]["fieldLabel"], "Quoter") . ": </span></td>\r\n                                        <td width=\"10%\">" . $this->numberFormat($runningItem) . " </td>\r\n                                        </tr>\r\n                                        </table>\r\n                                        </td>\r\n                                    </tr>";
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($newBody as $row) {
                    $newTbodyRowsText = $row->outertext;
                    foreach ($newBodyTokens as $tModuleName => $tFields) {
                        foreach ($tFields as $k => $f) {
                            if (strpos($k, "cf_acf_rtf") !== false) {
                                $needValue = htmlspecialchars_decode($recordModel->getDisplayValue($f, $record));
                            } else {
                                $needValue = $recordModel->getDisplayValue($f, $record);
                            }
                            $newTbodyRowsText = str_replace($k, $needValue, $newTbodyRowsText);
                        }
                    }
                    $tmpBody .= $newTbodyRowsText;
                }
            }
            if ($tbody !== NULL) {
                $tbody->innertext = $tmpBody;
                $innertext .= $tbody->outertext;
            } else {
                $innertext .= $tmpBody;
            }
            $tmpFoot = "";
            foreach ($newFooter as $row) {
                $newTfootRowsText = $row->outertext;
                if ((strpos($newTfootRowsText, "\$VTEItems__pre_tax_total\$") !== false || strpos($newTfootRowsText, "\$VTEItems__tax\$") !== false) && $isIndividual == "individual") {
                    continue;
                }
                foreach ($tokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        $needValue = NULL;
                        if (in_array($moduleName, $inventoryModules) && isset($final_details[$f])) {
                            $needValue = Vtiger_Currency_UIType::transformDisplayValue($final_details[$f], NULL, true);
                            $needValue = nl2br($needValue);
                        } else {
                            if ($f == $crmid) {
                                $needValue = $record;
                            } else {
                                $needValue = $recordModel->getDisplayValue($f, $record);
                            }
                        }
                        $newTfootRowsText = str_replace($k, $needValue, $newTfootRowsText);
                    }
                }
                $tmpFoot .= $newTfootRowsText;
            }
            if ($tfoot !== NULL) {
                $tfoot->innertext = $tmpFoot;
            } else {
                if ($tbody !== NULL) {
                }
            }
            $table->innertext = $innertext;
            $tfootInnertext = $tfoot !== NULL ? $tfoot->innertext : $tmpFoot;
            $hasTbody = $tbody !== NULL;
            if ($hasTbody) {
                $newTable = clone $table;
                $newTable->innertext = $tfootInnertext;
                $table->outertext = $table->outertext . $newTable->outertext;
            }
            $content = $html->save();
        }
        return $content;
    }
    public function getSpecialDateConditionValue($comparator, $value, $type, $queryGenerator = false)
    {
        global $current_user;
        global $default_timezone;
        date_default_timezone_set($current_user->time_zone);
        switch ($comparator) {
            case "lessthandaysago":
                $days = $value;
                $olderDate = date("Y-m-d", strtotime("-" . $days . " days"));
                $today = date("Y-m-d");
                if ($queryGenerator) {
                    return array("comparator" => "bw", "date" => $olderDate . "," . $today);
                }
                return array("comparator" => "bw", "date" => array($olderDate, $today));
            case "morethandaysago":
                $days = $value - 1;
                $olderDate = date("Y-m-d", strtotime("-" . $days . " days"));
                return array("comparator" => "l", "date" => $olderDate);
            case "inlessthan":
                $days = $value;
                $today = date("Y-m-d");
                $futureDate = date("Y-m-d", strtotime("+" . $days . " days"));
                if ($queryGenerator) {
                    return array("comparator" => "bw", "date" => $today . "," . $futureDate);
                }
                return array("comparator" => "bw", "date" => array($today, $futureDate));
            case "inmorethan":
                $days = $value - 1;
                $futureDate = date("Y-m-d", strtotime("+" . $days . " days"));
                return array("comparator" => "g", "date" => $futureDate);
            case "daysago":
                $olderDate = date("Y-m-d", strtotime("-" . $value . " days"));
                if ($type == "DT") {
                    return array("comparator" => "c", "date" => $olderDate);
                }
                return array("comparator" => "e", "date" => $olderDate);
            case "dayslater":
                $futureDate = date("Y-m-d", strtotime("+" . $value . " days"));
                if ($type == "DT") {
                    return array("comparator" => "c", "date" => $futureDate);
                }
                return array("comparator" => "e", "date" => $futureDate);
            case "lessthanhoursbefore":
                $currentTime = date("Y-m-d H:i:s");
                $olderDateTime = date("Y-m-d H:i:s", strtotime("-" . $value . " hours"));
                if ($queryGenerator) {
                    $currentDateTimeInstance = new DateTimeField($currentTime);
                    $currentTime = $currentDateTimeInstance->getDisplayDateTimeValue();
                    $olderDateTimeInstance = new DateTimeField($olderDateTime);
                    $olderDateTime = $olderDateTimeInstance->getDisplayDateTimeValue();
                    return array("comparator" => "bw", "date" => $olderDateTime . "," . $currentTime);
                }
                return array("comparator" => "bw", "date" => array($olderDateTime, $currentTime));
            case "lessthanhourslater":
                $currentTime = date("Y-m-d H:i:s");
                $futureDateTime = date("Y-m-d H:i:s", strtotime("+" . $value . " hours"));
                if ($queryGenerator) {
                    $currentDateTimeInstance = new DateTimeField($currentTime);
                    $currentTime = $currentDateTimeInstance->getDisplayDateTimeValue();
                    $futureDateTimeInstance = new DateTimeField($futureDateTime);
                    $futureDateTime = $futureDateTimeInstance->getDisplayDateTimeValue();
                    return array("comparator" => "bw", "date" => $currentTime . "," . $futureDateTime);
                }
                return array("comparator" => "bw", "date" => array($currentTime, $futureDateTime));
            case "morethanhoursbefore":
                $olderDateTime = date("Y-m-d H:i:s", strtotime("-" . $value . " hours"));
                if ($queryGenerator) {
                    $olderDateTimeInstance = new DateTimeField($olderDateTime);
                    $olderDateTime = $olderDateTimeInstance->getDisplayDateTimeValue();
                }
                return array("comparator" => "l", "date" => $olderDateTime);
            case "morethanhourslater":
                $futureDateTime = date("Y-m-d H:i:s", strtotime("+" . $value . " hours"));
                if ($queryGenerator) {
                    $futureDateTimeInstance = new DateTimeField($futureDateTime);
                    $futureDateTime = $futureDateTimeInstance->getDisplayDateTimeValue();
                }
                return array("comparator" => "g", "date" => $futureDateTime);
        }
        return "";
    }
    public function mergeLinkModulesTokens($tokens, $record, $content)
    {
        global $default_timezone;
        global $vtiger_current_version;
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        $crmid = "crmid";
        $pdfContentModel = new QuotingTool_PDFContent_Model();
        $blockStartTemplates = array("#RELATEDBLOCK_START#");
        $blockEndTemplates = array("#RELATEDBLOCK_END#");
        $blockTemplates = array_merge($blockStartTemplates, $blockEndTemplates);
        $dataTableType = NULL;
        foreach ($html->find("table") as $table) {
            $dataTableType = $table->attr["data-table-type"];
            $dataInfo = $table->attr["data-info"];
            if (!$dataTableType || $dataTableType != "related_module") {
                continue;
            }
            if ($record == 0) {
                return $content;
            }
            $recordModel = Vtiger_Record_Model::getInstanceById($record);
            $moduleName = $recordModel->getModuleName();
            $templateId = $_REQUEST["template_id"];
            $quotingToolRecordModel = new QuotingTool_SettingRecord_Model();
            $settingTemplate = $quotingToolRecordModel->findByTemplateId($templateId);
            $dateFormat = $settingTemplate->get("date_format");
            $dateFormat = str_replace(array("yyyy", "mm", "dd"), array("Y", "m", "d"), $dateFormat);
            $userFormat = DateTimeField::getPHPDateFormat();
            $table->removeAttribute("data-info");
            $isTemplateStart = false;
            $isTemplateEnd = false;
            $newHeader = array();
            $newBody = array();
            $newFooter = array();
            $thead = NULL;
            $tbody = NULL;
            $tfoot = NULL;
            $newHeaderTokens = array();
            $newBodyTokens = array();
            $newFooterTokens = array();
            $dataOddStyle = $table->attr["data-odd-style"];
            $dataEvenStyle = $table->attr["data-even-style"];
            foreach ($table->find("tr") as $row) {
                $isNormalRow = true;
                foreach ($row->children() as $cell) {
                    $cellText = trim($cell->plaintext);
                    if (!in_array($cellText, $blockTemplates)) {
                        continue;
                    }
                    $isNormalRow = false;
                    $cell->parent->outertext = $cellText;
                    if (in_array($cellText, $blockStartTemplates)) {
                        $isTemplateStart = true;
                        break;
                    }
                    if (in_array($cellText, $blockEndTemplates)) {
                        $isTemplateEnd = true;
                        break;
                    }
                }
                if ($isNormalRow) {
                    if (!$isTemplateStart) {
                        $newHeader[] = $row;
                        $newHeaderTokens = array_replace_recursive($newHeaderTokens, $this->getFieldTokenFromString($row->outertext));
                    } else {
                        if ($isTemplateStart && !$isTemplateEnd) {
                            $newBody[] = $row;
                            $newBodyTokens = array_replace_recursive($newBodyTokens, $this->getFieldTokenFromString($row->outertext));
                        } else {
                            if ($isTemplateEnd) {
                                $newFooter[] = $row;
                                $newFooterTokens = array_replace_recursive($newFooterTokens, $this->getFieldTokenFromString($row->outertext));
                            }
                        }
                    }
                }
                $parent = $row->parent();
                if ($thead === NULL && $parent->tag == "thead") {
                    $thead = $parent;
                } else {
                    if ($tbody === NULL && $parent->tag == "tbody") {
                        $tbody = $parent;
                    } else {
                        if ($tfoot === NULL && $parent->tag == "tfoot") {
                            $tfoot = $parent;
                        }
                    }
                }
            }
            $innertext = "";
            $tmpHead = "";
            foreach ($newHeader as $row) {
                $newTheadRowsText = $row->outertext;
                foreach ($newHeaderTokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        $needValue = $recordModel->getDisplayValue($f, $record);
                        $newTheadRowsText = str_replace($k, $needValue, $newTheadRowsText);
                    }
                }
                $tmpHead .= $newTheadRowsText;
            }
            if ($thead !== NULL) {
                $thead->innertext = $tmpHead;
                $innertext .= $thead->outertext;
            } else {
                if ($tbody !== NULL) {
                    $innertext .= "<thead>" . $tmpHead . "</thead>";
                } else {
                    $innertext .= $tmpHead;
                }
            }
            $tmpBody = "";
            if ($tbody !== NULL) {
                $dataOddStyle = $tbody->attr["data-odd-style"];
                $dataEvenStyle = $tbody->attr["data-even-style"];
            }
            $dataOddStyle = $dataOddStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataOddStyle))) : "";
            $dataEvenStyle = $dataEvenStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataEvenStyle))) : "";
            $recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
            $relatedModuleName = "";
            foreach ($newBodyTokens as $tModuleName => $tFields) {
                $relatedModuleName = $tModuleName;
            }
            if (!Vtiger_Module::getInstance($relatedModuleName)) {
                continue;
            }
            global $currentModule;
            $currentModule = $moduleName;
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set("page", 1);
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
            $query = $relationListView->getRelationQuery();
            $whereCondition = array();
            $dataInfo = html_entity_decode($dataInfo);
            $dataInfo = json_decode($dataInfo);
            $groupBy = $dataInfo->settings->item_fields_group;
            $conditions = $dataInfo->settings->conditions;
            $conditionsAll = $conditions->all;
            $conditionsAny = $conditions->any;
            $whereQuerySplit = split("WHERE", $queryGenerator->getWhereClause());
            $whereQuerySplit = $whereQuerySplit[1];
            $whereQuerySplit1 = split("AND", $whereQuerySplit);
            $moduleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
            if (!empty($conditionsAll)) {
                $queryGenerator->clearConditionals();
                foreach ($conditionsAll as $condition) {
                    $fieldName = $condition->fieldname;
                    $fieldInfo = explode(":", $fieldName);
                    list(, , $fieldName, , $fieldType) = $fieldInfo;
                    $columnName = $fieldInfo[0] . ":" . $fieldInfo[1];
                    $fieldInfo = $moduleModel->getField($fieldName);
                    $comparator = $condition->operation;
                    $searchValue = $condition->value;
                    $type = $condition->type;
                    if ($type == "time") {
                        $searchValue = Vtiger_Time_UIType::getTimeValueWithSeconds($searchValue);
                    }
                    $customView = new CustomView($relatedModuleName);
                    $specialDateTimeConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
                    $dateSpecificConditions = $customView->getStdFilterConditions();
                    if ($fieldName == "date_start" || $fieldName == "due_date" || $fieldInfo->getFieldDataType() == "datetime" && !in_array($comparator, $specialDateTimeConditions)) {
                        $dateValues = explode(",", $searchValue);
                        $isFirstDate = true;
                        foreach ($dateValues as $key => $dateValue) {
                            $dateTimeCompoenents = explode(" ", $dateValue);
                            if (empty($dateTimeCompoenents[1])) {
                                if ($isFirstDate) {
                                    $dateTimeCompoenents[1] = "00:00:00";
                                } else {
                                    $dateTimeCompoenents[1] = "23:59:59";
                                }
                            }
                            $dateValue = implode(" ", $dateTimeCompoenents);
                            $dateValues[$key] = $dateValue;
                            $isFirstDate = false;
                        }
                        if (in_array($comparator, $dateSpecificConditions)) {
                            $comparator = "between";
                        }
                        $searchValue = implode(",", $dateValues);
                        $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                    } else {
                        if (($fieldType == "D" || $fieldType == "DT") && in_array($comparator, $specialDateTimeConditions)) {
                            $searchValue = self::getSpecialDateConditionValue($comparator, $searchValue, $fieldType);
                            $queryGenerator->addCondition($fieldName, $searchValue["date"], $searchValue["comparator"], "AND");
                        } else {
                            $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                        }
                    }
                }
                $whereQuerySplitAll = split("WHERE", $queryGenerator->getWhereClause());
                $whereQuerySplitAll = $whereQuerySplitAll[1];
                foreach ($whereQuerySplit1 as $condition1) {
                    $condition1 = trim($condition1);
                    $whereQuerySplitAll = str_replace($condition1, "", $whereQuerySplitAll);
                }
                $whereQuerySplitAll = trim($whereQuerySplitAll);
                $whereQuerySplitAll = trim($whereQuerySplitAll, "AND");
                $whereQuerySplitAll = trim($whereQuerySplitAll);
                if (!empty($whereQuerySplitAll)) {
                    $whereQuerySplit .= " AND " . $whereQuerySplitAll;
                }
            }
            if (!empty($conditionsAny)) {
                $queryGenerator->clearConditionals();
                foreach ($conditionsAny as $condition) {
                    $fieldName = $condition->fieldname;
                    $fieldInfo = explode(":", $fieldName);
                    list(, , $fieldName, , $fieldType) = $fieldInfo;
                    $columnName = $fieldInfo[0] . ":" . $fieldInfo[1];
                    $fieldInfo = $moduleModel->getField($fieldName);
                    $comparator = $condition->operation;
                    $searchValue = $condition->value;
                    $type = $condition->type;
                    if ($type == "time") {
                        $searchValue = Vtiger_Time_UIType::getTimeValueWithSeconds($searchValue);
                    }
                    $customView = new CustomView($relatedModuleName);
                    $specialDateTimeConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
                    $dateSpecificConditions = $customView->getStdFilterConditions();
                    if ($fieldName == "date_start" || $fieldName == "due_date" || $fieldInfo->getFieldDataType() == "datetime" && !in_array($comparator, $specialDateTimeConditions)) {
                        $dateValues = explode(",", $searchValue);
                        $isFirstDate = true;
                        foreach ($dateValues as $key => $dateValue) {
                            $dateTimeCompoenents = explode(" ", $dateValue);
                            if (empty($dateTimeCompoenents[1])) {
                                if ($isFirstDate) {
                                    $dateTimeCompoenents[1] = "00:00:00";
                                } else {
                                    $dateTimeCompoenents[1] = "23:59:59";
                                }
                            }
                            $dateValue = implode(" ", $dateTimeCompoenents);
                            $dateValues[$key] = $dateValue;
                            $isFirstDate = false;
                        }
                        if (in_array($comparator, $dateSpecificConditions)) {
                            $comparator = "between";
                        }
                        $searchValue = implode(",", $dateValues);
                        $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                    } else {
                        if (($fieldType == "D" || $fieldType == "DT") && in_array($comparator, $specialDateTimeConditions)) {
                            $searchValue = self::getSpecialDateConditionValue($comparator, $searchValue, $fieldType);
                            $queryGenerator->addCondition($fieldName, $searchValue["date"], $searchValue["comparator"], "AND");
                        } else {
                            $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                        }
                    }
                }
                $whereQuerySplitAny = split("WHERE", $queryGenerator->getWhereClause());
                $whereQuerySplitAny = $whereQuerySplitAny[1];
                foreach ($whereQuerySplit1 as $condition1) {
                    $condition1 = trim($condition1);
                    $whereQuerySplitAny = str_replace($condition1, "", $whereQuerySplitAny);
                }
                $whereQuerySplitAny = trim($whereQuerySplitAny);
                $whereQuerySplitAny = trim($whereQuerySplitAny, "AND");
                $whereQuerySplitAny = trim($whereQuerySplitAny);
                $whereQuerySplitAny = str_replace(")  AND (", ")  OR (", $whereQuerySplitAny);
                if (!empty($whereQuerySplitAny)) {
                    $whereQuerySplit .= " AND (" . $whereQuerySplitAny . ")";
                }
            }
            $queryOrder = split("ORDER BY", $query);
            if ($queryOrder[1]) {
                $query = $queryOrder[0];
                $query .= " AND " . $whereQuerySplit;
            } else {
                $query .= " AND " . $whereQuerySplit;
            }
            if ($dataInfo->settings->sort_field) {
                $tableName = getTableNameForField($relatedModuleName, $dataInfo->settings->sort_field->name);
                if ($tableName) {
                    $tableName .= ".";
                }
                if ($dataInfo->settings->sort_type) {
                    $query .= " ORDER BY " . $tableName . $dataInfo->settings->sort_field->name . " ASC";
                } else {
                    $query .= " ORDER BY " . $tableName . $dataInfo->settings->sort_field->name . " DESC";
                }
            }
            global $adb;
            $res = $adb->query($query, array());
            date_default_timezone_set($default_timezone);
            $referenceFields = array();
            if (0 < $adb->num_rows($res)) {
                $listfieldsGroup = array();
                if (0 < count($groupBy)) {
                    foreach ($groupBy as $fieldGr) {
                        $listfieldsGroup[] = $fieldGr->id;
                    }
                    $data = array();
                    while ($gr = $adb->fetchByAssoc($res)) {
                        $data[] = $gr;
                    }
                    $oldData = array();
                    $listCrmid = array();
                    foreach ($data as $f) {
                        if (in_array($f["crmid"], $listCrmid)) {
                            continue;
                        }
                        $listCrmid[] = $f["crmid"];
                        foreach ($listfieldsGroup as $fieldGroup) {
                            $fieldModel = Vtiger_Field_Model::getInstance($fieldGroup);
                            $columnField = $fieldModel->column;
                            $oldData[$columnField] = $f[$columnField];
                            if ($f[$columnField] == "") {
                                $vtigerRecordModel = Vtiger_Record_Model::getInstanceById($f["crmid"]);
                                $oldData[$columnField] = $vtigerRecordModel->get($columnField);
                            }
                        }
                        foreach ($data as $x) {
                            if (in_array($x["crmid"], $listCrmid)) {
                                continue;
                            }
                            $cke = true;
                            foreach ($listfieldsGroup as $fieldGroup) {
                                $fieldModel = Vtiger_Field_Model::getInstance($fieldGroup);
                                $columnField = $fieldModel->column;
                                $xValue = $x[$columnField];
                                if ($xValue == "") {
                                    $xRecordModel = Vtiger_Record_Model::getInstanceById($x["crmid"]);
                                    $xValue = $xRecordModel->get($columnField);
                                }
                                if ($xValue != $oldData[$columnField]) {
                                    $cke = false;
                                }
                            }
                            if ($cke) {
                                $listCrmid[] = $x["crmid"];
                            }
                        }
                    }
                    $counter = 0;
                    $textGroup = "";
                    foreach ($listCrmid as $idGroup) {
                        $k = intval($idGroup);
                        if (!$k) {
                            continue;
                        }
                        $even = ++$counter % 2 == 0;
                        $cloneTbodyRowTokens = $newBodyTokens;
                        $linkModuleRecordModel = Vtiger_Record_Model::getInstanceById($k);
                        foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                            $moduleModel = Vtiger_Module_Model::getInstance($tModuleName);
                            foreach ($tFields as $fToken => $fName) {
                                if ($fName == $crmid) {
                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $k;
                                } else {
                                    $fieldModel = $moduleModel->getField($fName);
                                    if ($fieldModel) {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $linkModuleRecordModel->getDisplayValue($fName, $k);
                                        $fieldDataType = $fieldModel->getFieldDataType();
                                        if ($fieldDataType == "reference" || in_array($fieldDataType, array("owner", "url"))) {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = $this->getTextFromHtmlTag($cloneTbodyRowTokens[$tModuleName][$fToken], "a");
                                        }
                                        $fieldValue = $cloneTbodyRowTokens[$tModuleName][$fToken];
                                        if ($fieldDataType == "picklist" && $fieldValue != "") {
                                            if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                                $picklistColor = $fieldModel->getPicklistColors();
                                                $color = $picklistColor[$cloneTbodyRowTokens[$tModuleName][$fToken]];
                                                $textColor = QuotingTool::getTextColor($color);
                                                if ($color != "") {
                                                    $container = "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $cloneTbodyRowTokens[$tModuleName][$fToken] . "</span>";
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $container;
                                                }
                                            }
                                        } else {
                                            if ($fieldDataType == "multipicklist" && $fieldValue != "") {
                                                if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                                    $picklistColor = $fieldModel->getPicklistColors();
                                                    $listValue = explode(",", $fieldValue);
                                                    $index = 0;
                                                    $count = count($listValue);
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = "";
                                                    foreach ($listValue as $picklistValue) {
                                                        $index++;
                                                        $color = $picklistColor[trim($picklistValue)];
                                                        $textColor = QuotingTool::getTextColor($color);
                                                        if ($color != "") {
                                                            if ($index == $count) {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>";
                                                            } else {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>, ";
                                                            }
                                                        } else {
                                                            if ($index == $count) {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= $picklistValue;
                                                            } else {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= $picklistValue . ", ";
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if (($fieldDataType == "date" || $fieldDataType == "datetime") && $fieldValue != "" && $dateFormat != "") {
                                                    if ($fieldDataType == "datetime") {
                                                        $valuesList = explode(" ", $fieldValue);
                                                        if (count($valuesList) == 1) {
                                                            $fieldValue = "";
                                                        }
                                                        $newVal = $valuesList[0];
                                                        $fieldValue = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $fieldValue;
                                                    } else {
                                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = DateTime::createFromFormat($userFormat, $fieldValue)->format($dateFormat);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($newBody as $row) {
                            $maxCol = 0;
                            $row->setAttribute("data-row-number", $counter);
                            foreach ($row->children() as $cell) {
                                $style = $even ? $dataEvenStyle : $dataOddStyle;
                                $oldStyle = $cell->getAttribute("style");
                                $newStyle = NULL;
                                $maxCol++;
                                if (!$oldStyle) {
                                    $newStyle = $style;
                                } else {
                                    $oldStyle = trim($oldStyle);
                                    if (QuotingToolUtils::endsWith($oldStyle, ";")) {
                                        $newStyle = $oldStyle . " " . $style;
                                    } else {
                                        $newStyle = $oldStyle . "; " . $style;
                                    }
                                }
                                $newStyle = trim($newStyle);
                                if ($newStyle !== "") {
                                    $cell->setAttribute("style", $newStyle);
                                }
                            }
                            $cloneTbodyRowsTemplate = $row->outertext;
                            $newTextGroup = "";
                            $breakToken = array();
                            foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                                foreach ($tFields as $k => $f) {
                                    if (strpos($k, "cf_acf_rtf") !== false) {
                                        $f = htmlspecialchars_decode($f);
                                    } else {
                                        $f = nl2br($f);
                                    }
                                    foreach ($groupBy as $fieldGr) {
                                        $breakToken[] = $fieldGr->token;
                                    }
                                    $cloneTbodyRowsTemplate = str_replace($k, $f, $cloneTbodyRowsTemplate);
                                }
                            }
                            foreach ($groupBy as $fieldGr) {
                                if (in_array($fieldGr->token, $breakToken)) {
                                    $fieldDataType = $fieldGr->datatype;
                                    if ($fieldDataType == "reference" || in_array($fieldDataType, array("owner", "url"))) {
                                        $newTextGroup .= $this->getTextFromHtmlTag($linkModuleRecordModel->getDisplayValue($fieldGr->name, $k), "a") . ", ";
                                    } else {
                                        $newTextGroup .= html_entity_decode($linkModuleRecordModel->getDisplayValue($fieldGr->name, $k)) . ", ";
                                    }
                                }
                            }
                            if ($newTextGroup != $textGroup) {
                                $cellGroup = $table->find("#cell_group")[0]->outertext;
                                $cellGroup = str_replace("\$Group_by", substr($newTextGroup, 0, -2), $cellGroup);
                                $rowGroup = "<tr>" . $cellGroup . "</tr>";
                                $tmpBody .= $rowGroup;
                            }
                            $textGroup = $newTextGroup;
                            $tmpBody .= $cloneTbodyRowsTemplate;
                        }
                    }
                } else {
                    $counter = 0;
                    while ($r = $adb->fetchByAssoc($res)) {
                        if ($relatedModuleName == "ModComments") {
                            $k = intval($r["modcommentsid"]);
                        } else {
                            $k = intval($r["crmid"]);
                        }
                        if (!$k) {
                            continue;
                        }
                        $even = ++$counter % 2 == 0;
                        $cloneTbodyRowTokens = $newBodyTokens;
                        $linkModuleRecordModel = Vtiger_Record_Model::getInstanceById($k);
                        foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                            $moduleModel = Vtiger_Module_Model::getInstance($tModuleName);
                            foreach ($tFields as $fToken => $fName) {
                                if ($fName == $crmid) {
                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $k;
                                } else {
                                    $fieldModel = $moduleModel->getField($fName);
                                    if ($fieldModel) {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $linkModuleRecordModel->getDisplayValue($fName, $k);
                                        $fieldDataType = $fieldModel->getFieldDataType();
                                        if ($fieldDataType == "reference" || in_array($fieldDataType, array("owner", "url"))) {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = $this->getTextFromHtmlTag($cloneTbodyRowTokens[$tModuleName][$fToken], "a");
                                        }
                                        $fieldValue = $cloneTbodyRowTokens[$tModuleName][$fToken];
                                        if ($fieldDataType == "picklist" && $fieldValue != "") {
                                            if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                                $picklistColor = $fieldModel->getPicklistColors();
                                                $color = $picklistColor[$cloneTbodyRowTokens[$tModuleName][$fToken]];
                                                $textColor = QuotingTool::getTextColor($color);
                                                if ($color != "") {
                                                    $container = "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $cloneTbodyRowTokens[$tModuleName][$fToken] . "</span>";
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = $container;
                                                }
                                            }
                                        } else {
                                            if ($fieldDataType == "multipicklist" && $fieldValue != "") {
                                                if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                                    $picklistColor = $fieldModel->getPicklistColors();
                                                    $listValue = explode(",", $fieldValue);
                                                    $index = 0;
                                                    $count = count($listValue);
                                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = "";
                                                    foreach ($listValue as $picklistValue) {
                                                        $index++;
                                                        $color = $picklistColor[trim($picklistValue)];
                                                        $textColor = QuotingTool::getTextColor($color);
                                                        if ($color != "") {
                                                            if ($index == $count) {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>";
                                                            } else {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>, ";
                                                            }
                                                        } else {
                                                            if ($index == $count) {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= $picklistValue;
                                                            } else {
                                                                $cloneTbodyRowTokens[$tModuleName][$fToken] .= $picklistValue . ", ";
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if (($fieldDataType == "date" || $fieldDataType == "datetime") && $fieldValue != "" && $dateFormat != "") {
                                                    if ($fieldDataType == "datetime") {
                                                        $valuesList = explode(" ", $fieldValue);
                                                        if (count($valuesList) == 1) {
                                                            $fieldValue = "";
                                                        }
                                                        $newVal = $valuesList[0];
                                                        $fieldValue = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = $fieldValue;
                                                    } else {
                                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = DateTime::createFromFormat($userFormat, $fieldValue)->format($dateFormat);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($newBody as $row) {
                            $row->setAttribute("data-row-number", $counter);
                            foreach ($row->children() as $cell) {
                                $style = $even ? $dataEvenStyle : $dataOddStyle;
                                $oldStyle = $cell->getAttribute("style");
                                $newStyle = NULL;
                                if (!$oldStyle) {
                                    $newStyle = $style;
                                } else {
                                    $oldStyle = trim($oldStyle);
                                    if (QuotingToolUtils::endsWith($oldStyle, ";")) {
                                        $newStyle = $oldStyle . " " . $style;
                                    } else {
                                        $newStyle = $oldStyle . "; " . $style;
                                    }
                                }
                                $newStyle = trim($newStyle);
                                if ($newStyle !== "") {
                                    $cell->setAttribute("style", $newStyle);
                                }
                            }
                            $cloneTbodyRowsTemplate = $row->outertext;
                            foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                                foreach ($tFields as $k => $f) {
                                    if (strpos($k, "cf_acf_rtf") !== false) {
                                        $f = htmlspecialchars_decode($f);
                                    } else {
                                        $f = nl2br($f);
                                    }
                                    $cloneTbodyRowsTemplate = str_replace($k, $f, $cloneTbodyRowsTemplate);
                                }
                            }
                            $tmpBody .= $cloneTbodyRowsTemplate;
                        }
                    }
                }
            }
            if ($tbody !== NULL) {
                $tbody->innertext = $tmpBody;
                $innertext .= $tbody->outertext;
            } else {
                $innertext .= $tmpBody;
            }
            $table->innertext = $innertext;
            $content = $html->save();
        }
        return $content;
    }
    public function mergeCreateRelatedRecord($tokens, $record, $content)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        $crmid = "crmid";
        $pdfContentModel = new QuotingTool_PDFContent_Model();
        $blockStartTemplates = array("#RELATEDBLOCK_START#");
        $blockEndTemplates = array("#RELATEDBLOCK_END#");
        $blockTemplates = array_merge($blockStartTemplates, $blockEndTemplates);
        $dataTableType = NULL;
        foreach ($html->find("table") as $table) {
            $dataTableType = $table->attr["data-table-type"];
            if (!$dataTableType || $dataTableType != "create_related_record") {
                continue;
            }
            $dataInfo = html_entity_decode($table->attr["data-info"]);
            $dataInfo = json_decode($dataInfo);
            $itemFields = $dataInfo->settings->item_fields;
            $moduleName = $dataInfo->settings->link_module;
            $isTemplateStart = false;
            $isTemplateEnd = false;
            $newHeader = array();
            $newBody = array();
            $newFooter = array();
            $thead = NULL;
            $tbody = NULL;
            $tfoot = NULL;
            $newHeaderTokens = array();
            $newBodyTokens = array();
            $newFooterTokens = array();
            $dataOddStyle = $table->attr["data-odd-style"];
            $dataEvenStyle = $table->attr["data-even-style"];
            foreach ($table->find("tr") as $row) {
                $isNormalRow = true;
                foreach ($row->children() as $cell) {
                    $cellText = trim($cell->plaintext);
                    if (!in_array($cellText, $blockTemplates)) {
                        continue;
                    }
                    $isNormalRow = false;
                    $cell->parent->outertext = $cellText;
                    if (in_array($cellText, $blockStartTemplates)) {
                        $isTemplateStart = true;
                        break;
                    }
                    if (in_array($cellText, $blockEndTemplates)) {
                        $isTemplateEnd = true;
                        break;
                    }
                }
                if ($isNormalRow) {
                    if (!$isTemplateStart) {
                        $newHeader[] = $row;
                        $newHeaderTokens = array_replace_recursive($newHeaderTokens, $this->getFieldTokenFromString($row->outertext));
                    } else {
                        if ($isTemplateStart && !$isTemplateEnd) {
                            $newBody[] = $row;
                            $newBodyTokens = array_replace_recursive($newBodyTokens, $this->getFieldTokenFromString($row->outertext));
                        } else {
                            if ($isTemplateEnd) {
                                $newFooter[] = $row;
                                $newFooterTokens = array_replace_recursive($newFooterTokens, $this->getFieldTokenFromString($row->outertext));
                            }
                        }
                    }
                }
                $parent = $row->parent();
                if ($thead === NULL && $parent->tag == "thead") {
                    $thead = $parent;
                } else {
                    if ($tbody === NULL && $parent->tag == "tbody") {
                        $tbody = $parent;
                    } else {
                        if ($tfoot === NULL && $parent->tag == "tfoot") {
                            $tfoot = $parent;
                        }
                    }
                }
            }
            $innertext = "";
            $tmpHead = "";
            foreach ($newHeader as $row) {
                $newTheadRowsText = $row->outertext;
                $tmpHead .= $newTheadRowsText;
            }
            if ($thead !== NULL) {
                $thead->innertext = $tmpHead;
                $innertext .= $thead->outertext;
            } else {
                if ($tbody !== NULL) {
                    $innertext .= "<thead>" . $tmpHead . "</thead>";
                } else {
                    $innertext .= $tmpHead;
                }
            }
            $tmpBody = "";
            if ($tbody !== NULL) {
                $dataOddStyle = $tbody->attr["data-odd-style"];
                $dataEvenStyle = $tbody->attr["data-even-style"];
            }
            $dataOddStyle = $dataOddStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataOddStyle))) : "";
            $dataEvenStyle = $dataEvenStyle ? QuotingToolUtils::convertArrayToInlineStyle(json_decode(html_entity_decode($dataEvenStyle))) : "";
            $counter = 0;
            $even = ++$counter % 2 == 0;
            $cloneTbodyRowTokens = $newBodyTokens;
            foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                foreach ($tFields as $fToken => $fName) {
                    foreach ($itemFields as $key => $values) {
                        if ($fToken == $values->token) {
                            $itemFields[$key]->module = $moduleName;
                            $itemFields[$key]->editable = true;
                            if ($itemFields[$key]->datatype == "reference") {
                                $referenceFields = Vtiger_Module_Model::getInstance($moduleName)->getFieldsByType("reference");
                                foreach ($referenceFields as $field_key => $valueField) {
                                    if ($itemFields[$key]->name == $field_key) {
                                        $referenceModule = $referenceFields[$field_key]->getReferenceList();
                                        $itemFields[$key]->reference_module = $referenceModule[0];
                                    }
                                }
                                $cloneTbodyRowTokens[$tModuleName][$fToken] = "<input class=\"autoComplete\" name=\"" . $values->name . "[]\" type=\"text\" data-info=\"" . htmlentities(json_encode($values)) . "\">";
                            } else {
                                if ($itemFields[$key]->datatype == "boolean") {
                                    $cloneTbodyRowTokens[$tModuleName][$fToken] = "<input class=\"boolean-input\" name=\"" . $values->name . "[]\" type=\"text\" data-info=\"" . htmlentities(json_encode($values)) . "\">";
                                } else {
                                    if ($itemFields[$key]->datatype == "multipicklist") {
                                        $cloneTbodyRowTokens[$tModuleName][$fToken] = "<input class=\"hide\" name=\"multipicklistValue[]\">" . "<input class=\"multipicklist\" name=\"" . $values->name . "[]\" type=\"text\" data-info=\"" . htmlentities(json_encode($values)) . "\">";
                                    } else {
                                        if ($itemFields[$key]->uitype == "19") {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = "<textarea name=\"" . $values->name . "[]\" data-info=\"" . htmlentities(json_encode($values)) . "\"></textarea>";
                                        } else {
                                            $cloneTbodyRowTokens[$tModuleName][$fToken] = "<input name=\"" . $values->name . "[]\" type=\"text\" data-info=\"" . htmlentities(json_encode($values)) . "\">";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($newBody as $row) {
                $row->setAttribute("data-row-number", $counter);
                foreach ($row->children() as $cell) {
                    $style = $even ? $dataEvenStyle : $dataOddStyle;
                    $oldStyle = $cell->getAttribute("style");
                    $newStyle = NULL;
                    if (!$oldStyle) {
                        $newStyle = $style;
                    } else {
                        $oldStyle = trim($oldStyle);
                        if (QuotingToolUtils::endsWith($oldStyle, ";")) {
                            $newStyle = $oldStyle . " " . $style;
                        } else {
                            $newStyle = $oldStyle . "; " . $style;
                        }
                    }
                    $newStyle = trim($newStyle);
                    if ($newStyle !== "") {
                        $cell->setAttribute("style", $newStyle);
                    }
                }
                $cloneTbodyRowsTemplate = $row->outertext;
                foreach ($cloneTbodyRowTokens as $tModuleName => $tFields) {
                    foreach ($tFields as $k => $f) {
                        if (strpos($k, "cf_acf_rtf") !== false) {
                            $f = htmlspecialchars_decode($f);
                        } else {
                            $f = nl2br($f);
                        }
                        $cloneTbodyRowsTemplate = str_replace($k, $f, $cloneTbodyRowsTemplate);
                    }
                }
                $baseRow = str_get_html($cloneTbodyRowsTemplate);
                $baseRow->find("tr")[0]->setAttribute("class", "tbody-content hide");
                $tmpBody .= $baseRow;
                $tmpBody .= $cloneTbodyRowsTemplate;
                $table->setAttribute("data-module", $itemFields[0]->module);
            }
            if ($tbody !== NULL) {
                $tbody->innertext = $tmpBody;
                $innertext .= $tbody->outertext;
            } else {
                $innertext .= $tmpBody;
            }
            $table->innertext = $innertext;
            $content = $html->save();
        }
        return $content;
    }
    public function getTextFromHtmlTag($content, $tagName)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        $text = $content;
        foreach ($html->find($tagName) as $element) {
            $text = $element->plaintext;
        }
        return $text;
    }
    public static function getEntityModulesList()
    {
        $db = PearDatabase::getInstance();
        $presence = array(0, 2);
        $restrictedModules = array("Webmails", "SMSNotifier", "Emails", "Integration", "Dashboard", "ModComments", "vtmessages", "vttwitter");
        $query = "SELECT name FROM vtiger_tab WHERE\r\n\t\t\t\t\t\tpresence IN (" . generateQuestionMarks($presence) . ")\r\n\t\t\t\t\t\tAND isentitytype = ?\r\n\t\t\t\t\t\tAND name NOT IN (" . generateQuestionMarks($restrictedModules) . ")";
        $result = $db->pquery($query, array($presence, 1, $restrictedModules));
        $numOfRows = $db->num_rows($result);
        $modulesList = array();
        for ($i = 0; $i < $numOfRows; $i++) {
            $moduleName = $db->query_result($result, $i, "name");
            $modulesList[$moduleName] = $moduleName;
            if ($moduleName == "Calendar") {
                $modulesList[$moduleName] = $moduleName;
            }
        }
        if (!array_key_exists("Calendar", $modulesList)) {
            unset($modulesList["Events"]);
        }
        return $modulesList;
    }
    public function checkCustomFunctions($content, $module, $record, $customFunctions)
    {
        global $adb;
        global $vtiger_current_version;
        include_once "include/simplehtmldom/simple_html_dom.php";
        if (str_get_html($content)) {
            $html = str_get_html($content);
            foreach ($html->find("div.clear") as $div) {
                if ($div->getAttribute("class") == "clear") {
                    $div->outertext = "";
                }
                $content = $html->save();
            }
        }
        $html = $content;
        if (!$html) {
            return $content;
        }
        $maxOfFunction = 0;
        if (!$customFunctions || !is_array($customFunctions)) {
            return $content;
        }
        foreach ($customFunctions as $key => $value) {
            if ($maxOfFunction < $value->id + 1 && $value->id != NULL) {
                $maxOfFunction = $value->id + 1;
            }
        }
        if ($maxOfFunction < 1) {
            return $content;
        }
        $minOfFunction = $maxOfFunction - 1;
        foreach ($customFunctions as $key => $value) {
            if ($value->id < $minOfFunction) {
                $minOfFunction = $value->id;
            }
        }
        $functionError = array();
        for ($i = $minOfFunction; $i < $maxOfFunction; $i++) {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                $queryGenerator = new EnhancedQueryGenerator($module, $currentUser);
            } else {
                $queryGenerator = new QueryGenerator($module, $currentUser);
            }
            $whereQuerySplit = split("WHERE", $queryGenerator->getWhereClause());
            $whereQuerySplit = $whereQuerySplit[1];
            $whereQuerySplit1 = split("AND", $whereQuerySplit);
            if ($customFunctions->{$i}->all && $customFunctions->{$i}->any) {
                $conditionsAll = $customFunctions->{$i}->all;
                $conditionsAny = $customFunctions->{$i}->any;
            } else {
                $conditionsAll = $customFunctions[$i]->all;
                $conditionsAny = $customFunctions[$i]->any;
            }
            if (!empty($conditionsAll)) {
                $queryGenerator->clearConditionals();
                foreach ($conditionsAll as $condition) {
                    $fieldName = $condition->fieldname;
                    $fieldInfo = explode(":", $fieldName);
                    $fieldName = $fieldInfo[2];
                    $columnName = $fieldInfo[0] . ":" . $fieldInfo[1];
                    $comparator = $condition->operation;
                    $searchValue = $condition->value;
                    $type = $condition->type;
                    if ($type == "time") {
                        $searchValue = Vtiger_Time_UIType::getTimeValueWithSeconds($searchValue);
                    }
                    $customView = new CustomView($module);
                    $specialDateFilters = array("yesterday", "today", "tomorrow");
                    $datefilter = $customView->getDateforStdFilterBytype($comparator);
                    if (in_array($comparator, $specialDateFilters)) {
                        $currentDate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
                        $startDateTime = new DateTimeField($datefilter[0] . " " . $currentDate->format("H:i:s"));
                        $endDateTime = new DateTimeField($datefilter[1] . " " . $currentDate->format("H:i:s"));
                        $startDateTime = $startDateTime->getDisplayDate();
                        $endDateTime = $endDateTime->getDisplayDate();
                        if ($fieldInfo[4] == "DT") {
                            $startdate = explode(" ", $startDateTime);
                            if ($startdate[1] == "") {
                                $startdate[1] = "00:00:00";
                            }
                            $startDateTime = $startdate[0] . " " . $startdate[1];
                            $enddate = explode(" ", $endDateTime);
                            if ($enddate[1] == "") {
                                $enddate[1] = "23:59:59";
                            }
                            $endDateTime = $enddate[0] . " " . $enddate[1];
                        }
                        $value = array();
                        $value[] = $queryGenerator->fixDateTimeValue($fieldName, $startDateTime);
                        $value[] = $queryGenerator->fixDateTimeValue($fieldName, $endDateTime, false);
                        $searchValue = $value;
                        $comparator = "BETWEEN";
                    } else {
                        $startDateTime = new DateTimeField($datefilter[0]);
                        $endDateTime = new DateTimeField($datefilter[1]);
                    }
                    $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                }
                $fromQuerySplitAll = split("LEFT JOIN", $queryGenerator->getFromClause());
                $whereQuerySplitAll = split("WHERE", $queryGenerator->getWhereClause());
                $whereQuerySplitAll = $whereQuerySplitAll[1];
                foreach ($whereQuerySplit1 as $condition1) {
                    $condition1 = trim($condition1);
                    $whereQuerySplitAll = str_replace($condition1, "", $whereQuerySplitAll);
                }
                $whereQuerySplitAll = trim($whereQuerySplitAll);
                $whereQuerySplitAll = trim($whereQuerySplitAll, "AND");
                $whereQuerySplitAll = trim($whereQuerySplitAll);
                if (!empty($whereQuerySplitAll)) {
                    $whereQuerySplit .= " AND " . $whereQuerySplitAll;
                }
            }
            if (!empty($conditionsAny)) {
                $queryGenerator->clearConditionals();
                foreach ($conditionsAny as $condition) {
                    $fieldName = $condition->fieldname;
                    $fieldInfo = explode(":", $fieldName);
                    $fieldName = $fieldInfo[2];
                    $columnName = $fieldInfo[0] . ":" . $fieldInfo[1];
                    $comparator = $condition->operation;
                    $searchValue = $condition->value;
                    $type = $condition->type;
                    if ($type == "time") {
                        $searchValue = Vtiger_Time_UIType::getTimeValueWithSeconds($searchValue);
                    }
                    $customView = new CustomView($module);
                    $specialDateFilters = array("yesterday", "today", "tomorrow");
                    $datefilter = $customView->getDateforStdFilterBytype($comparator);
                    if (in_array($comparator, $specialDateFilters)) {
                        $currentDate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s"));
                        $startDateTime = new DateTimeField($datefilter[0] . " " . $currentDate->format("H:i:s"));
                        $endDateTime = new DateTimeField($datefilter[1] . " " . $currentDate->format("H:i:s"));
                        $startDateTime = $startDateTime->getDisplayDate();
                        $endDateTime = $endDateTime->getDisplayDate();
                        if ($fieldInfo[4] == "DT") {
                            $startdate = explode(" ", $startDateTime);
                            if ($startdate[1] == "") {
                                $startdate[1] = "00:00:00";
                            }
                            $startDateTime = $startdate[0] . " " . $startdate[1];
                            $enddate = explode(" ", $endDateTime);
                            if ($enddate[1] == "") {
                                $enddate[1] = "23:59:59";
                            }
                            $endDateTime = $enddate[0] . " " . $enddate[1];
                        }
                        $value = array();
                        $value[] = $queryGenerator->fixDateTimeValue($fieldName, $startDateTime);
                        $value[] = $queryGenerator->fixDateTimeValue($fieldName, $endDateTime, false);
                        $searchValue = $value;
                        $comparator = "BETWEEN";
                    } else {
                        $startDateTime = new DateTimeField($datefilter[0]);
                        $endDateTime = new DateTimeField($datefilter[1]);
                    }
                    $queryGenerator->addCondition($fieldName, $searchValue, $comparator, "AND");
                }
                $fromQuerySplitAny = split("LEFT JOIN", $queryGenerator->getFromClause());
                $whereQuerySplitAny = split("WHERE", $queryGenerator->getWhereClause());
                $whereQuerySplitAny = $whereQuerySplitAny[1];
                foreach ($whereQuerySplit1 as $condition1) {
                    $condition1 = trim($condition1);
                    $whereQuerySplitAny = str_replace($condition1, "", $whereQuerySplitAny);
                }
                $whereQuerySplitAny = trim($whereQuerySplitAny);
                $whereQuerySplitAny = trim($whereQuerySplitAny, "AND");
                $whereQuerySplitAny = trim($whereQuerySplitAny);
                $whereQuerySplitAny = str_replace(")  AND (", ")  OR (", $whereQuerySplitAny);
                if (!empty($whereQuerySplitAny)) {
                    $whereQuerySplit .= " AND (" . $whereQuerySplitAny . ")";
                }
            }
            if (!$fromQuerySplitAll) {
                $fromQuerySplitAll = array();
            }
            if (!$fromQuerySplitAny) {
                $fromQuerySplitAny = array();
            }
            $fromQuery = implode("LEFT JOIN", $fromQuerySplitAll + $fromQuerySplitAny);
            $sql = "select 1 " . $fromQuery . " WHERE " . $whereQuerySplit;
            $sql .= " AND vtiger_crmentity.crmid = " . $record;
            $rs = $adb->pquery($sql, array());
            if ($adb->num_rows($rs) <= 0) {
                $functionError[$i] = true;
            } else {
                $functionError[$i] = false;
            }
        }
        for ($i = $minOfFunction; $i < $maxOfFunction; $i++) {
            $countFunctionStart = substr_count($html, "#CF_" . ($i + 1) . "_START#");
            $countFunctionEnd = substr_count($html, "#CF_" . ($i + 1) . "_END#");
            $startFunction = "#CF_" . ($i + 1) . "_START#";
            $endFunction = "#CF_" . ($i + 1) . "_END#";
            if ($countFunctionStart == $countFunctionEnd) {
                $countFunction = $countFunctionStart;
            } else {
                if ($countFunctionEnd < $countFunctionStart) {
                    $countFunction = $countFunctionEnd;
                } else {
                    $countFunction = $countFunctionStart;
                }
            }
            for ($function = 1; $function <= $countFunction; $function++) {
                if ($functionError[$i]) {
                    $positonStart = strpos($html, $startFunction, $function);
                    $positonEnd = strpos($html, $endFunction, $function);
                    $positonEnd = $positonEnd - $positonStart;
                    $subStr = substr($html, $positonStart, $positonEnd);
                    $subStr .= $endFunction;
                    $html = str_replace($subStr, "", $html);
                } else {
                    $html = str_replace($startFunction, "", $html, $countFunction);
                    $html = str_replace($endFunction, "", $html, $countFunction);
                }
            }
        }
        return $html;
    }
    /**
     * @param $tokens
     * @param $record
     * @param $content
     * @param string $module
     * @return mixed
     */
    public function mergeTokens($tokens, $record, $content, $module = "Vtiger")
    {
        global $vtiger_current_version;
        $supportedModulesList = self::getEntityModulesList();
        $supportedModulesList = array_flip($supportedModulesList);
        ksort($supportedModulesList);
        $crmid = "crmid";
        $ignore = array("modifiedby", "created_user_id");
        $export = array();
        $templateId = $_REQUEST["template_id"];
        $quotingToolRecordModel = new QuotingTool_SettingRecord_Model();
        $settingTemplate = $quotingToolRecordModel->findByTemplateId($templateId);
        $dateFormat = $settingTemplate->get("date_format");
        $dateFormat = str_replace(array("yyyy", "mm", "dd"), array("Y", "m", "d"), $dateFormat);
        $userFormat = DateTimeField::getPHPDateFormat();
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        if ($record == 0 || !isRecordExists($record)) {
            foreach ($tokens as $tModuleName => $tFields) {
                if (!in_array($tModuleName, $supportedModulesList) && !in_array($tModuleName, $this->specialModules)) {
                    continue;
                }
                $referenceFields = $moduleModel->getFieldsByType("reference");
                if ($tModuleName == $module) {
                    foreach ($tFields as $fToken => $fName) {
                        if ($fName == $crmid) {
                            $tokens[$tModuleName][$fToken] = "";
                        } else {
                            if (!in_array($fName, $ignore) && array_key_exists($fName, $referenceFields)) {
                                $tokens[$tModuleName][$fToken] = "";
                                continue;
                            }
                            $fieldModel = $moduleModel->getField($fName);
                            if (!$fieldModel) {
                                unset($tokens[$tModuleName][$fToken]);
                                continue;
                            }
                            $fieldDataType = $fieldModel->getFieldDataType();
                            $needValue = "";
                            if (in_array($fieldDataType, array("url", "email", "documentsFolder", "fileLocationType", "documentsFileUpload"))) {
                                $needValue = "";
                            } else {
                                if (($fieldDataType == "date" || $fieldDataType == "datetime") && $needValue != "" && $dateFormat != "") {
                                    if ($fieldDataType == "datetime") {
                                        $valuesList = explode(" ", $needValue);
                                        if (count($valuesList) == 1) {
                                            $needValue = "";
                                        }
                                        $newVal = $valuesList[0];
                                        $needValue = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                    } else {
                                        $needValue = DateTime::createFromFormat($userFormat, $needValue)->format($dateFormat);
                                    }
                                }
                            }
                            $tokens[$tModuleName][$fToken] = $needValue;
                        }
                    }
                    $export[] = $tModuleName;
                }
                if ($tModuleName == "Users") {
                    $userModuleModel = Vtiger_Module_Model::getInstance($tModuleName);
                    $referenceFields = $userModuleModel->getFieldsByType("reference");
                    $assignedToId = "";
                    if (QuotingToolUtils::isUserExists($assignedToId)) {
                        $userRecordModel = Vtiger_Record_Model::getInstanceById($assignedToId, $tModuleName);
                        foreach ($tFields as $fToken => $fName) {
                            if ($fName == $crmid) {
                                $tokens[$tModuleName][$fToken] = $userRecordModel->getId();
                            } else {
                                if ($fName == "roleid") {
                                    $tokens[$tModuleName][$fToken] = getRoleName($userRecordModel->get("roleid"));
                                } else {
                                    if (!in_array($fName, $ignore) && array_key_exists($fName, $referenceFields)) {
                                        if (!$userRecordModel->get($fName)) {
                                            $tokens[$tModuleName][$fToken] = "";
                                            continue;
                                        }
                                        $relatedRecordModel = Vtiger_Record_Model::getInstanceById($userRecordModel->get($fName));
                                        $tokens[$tModuleName][$fToken] = $relatedRecordModel ? $relatedRecordModel->getName() : "";
                                    } else {
                                        $fieldModel = $userRecordModel->getField($fName);
                                        if (!$fieldModel) {
                                            unset($tokens[$tModuleName][$fToken]);
                                            continue;
                                        }
                                        $fieldDataType = $fieldModel->getFieldDataType();
                                        $needValue = $userRecordModel->getDisplayValue($fName, $userModuleModel->getId());
                                        if (in_array($fieldDataType, array("email", "documentsFolder", "fileLocationType", "documentsFileUpload"))) {
                                            $needValue = $userRecordModel->get($fName);
                                        }
                                        $tokens[$tModuleName][$fToken] = $needValue;
                                    }
                                }
                            }
                        }
                    }
                    $export[] = $tModuleName;
                }
            }
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
            if (!$recordModel) {
                return $content;
            }
            foreach ($tokens as $tModuleName => $tFields) {
                if (!in_array($tModuleName, $supportedModulesList) && !in_array($tModuleName, $this->specialModules)) {
                    continue;
                }
                $referenceFields = $moduleModel->getFieldsByType("reference");
                if ($tModuleName == $module) {
                    foreach ($tFields as $fToken => $fName) {
                        if ($fName == $crmid) {
                            $tokens[$tModuleName][$fToken] = $recordModel->getId();
                        } else {
                            if (!in_array($fName, $ignore) && array_key_exists($fName, $referenceFields)) {
                                if (!$recordModel->get($fName)) {
                                    $tokens[$tModuleName][$fToken] = "";
                                    continue;
                                }
                                $relatedRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get($fName));
                                $tokens[$tModuleName][$fToken] = $relatedRecordModel ? $relatedRecordModel->getName() : "";
                            } else {
                                $fieldModel = $moduleModel->getField($fName);
                                if (!$fieldModel) {
                                    unset($tokens[$tModuleName][$fToken]);
                                    continue;
                                }
                                $fieldDataType = $fieldModel->getFieldDataType();
                                $needValue = $recordModel->getDisplayValue($fName, $recordModel->getId());
                                if ($fName == "assigned_user_id") {
                                    $needValue = $this->getTextFromHtmlTag($needValue, "a");
                                }
                                if (in_array($fieldDataType, array("url", "email", "documentsFolder", "fileLocationType", "documentsFileUpload", "text"))) {
                                    $needValue = $recordModel->get($fName);
                                } else {
                                    if (($fieldDataType == "date" || $fieldDataType == "datetime") && $needValue != "" && $dateFormat != "") {
                                        if ($fieldDataType == "datetime") {
                                            $valuesList = explode(" ", $needValue);
                                            if (count($valuesList) == 1) {
                                                $needValue = "";
                                            }
                                            $newVal = $valuesList[0];
                                            $needValue = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                        } else {
                                            $needValue = DateTime::createFromFormat($userFormat, $needValue)->format($dateFormat);
                                        }
                                    }
                                }
                                if ($fieldDataType == "picklist" && $needValue != "") {
                                    if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                        $picklistColor = $fieldModel->getPicklistColors();
                                        $color = $picklistColor[$needValue];
                                        $textColor = QuotingTool::getTextColor($color);
                                        if ($color != "") {
                                            $needValue = "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $needValue . "</span>";
                                        }
                                    }
                                } else {
                                    if ($fieldDataType == "multipicklist" && $needValue != "" && version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                        $picklistColor = $fieldModel->getPicklistColors();
                                        $listValue = explode(",", $needValue);
                                        $needValue = "";
                                        $index = 0;
                                        $count = count($listValue);
                                        foreach ($listValue as $picklistValue) {
                                            $index++;
                                            $color = $picklistColor[trim($picklistValue)];
                                            $textColor = QuotingTool::getTextColor($color);
                                            if ($color != "") {
                                                if ($index == $count) {
                                                    $needValue .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>";
                                                } else {
                                                    $needValue .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>, ";
                                                }
                                            } else {
                                                if ($index == $count) {
                                                    $needValue .= $picklistValue;
                                                } else {
                                                    $needValue .= $picklistValue . ", ";
                                                }
                                            }
                                        }
                                    }
                                }
                                $tokens[$tModuleName][$fToken] = $needValue;
                            }
                        }
                    }
                    $export[] = $tModuleName;
                }
                if ($tModuleName == "Users") {
                    $userModuleModel = Vtiger_Module_Model::getInstance($tModuleName);
                    $referenceFields = $userModuleModel->getFieldsByType("reference");
                    $assignedToId = $recordModel->get("assigned_user_id");
                    if (QuotingToolUtils::isUserExists($assignedToId)) {
                        $userRecordModel = Vtiger_Record_Model::getInstanceById($assignedToId, $tModuleName);
                        foreach ($tFields as $fToken => $fName) {
                            if ($fName == $crmid) {
                                $tokens[$tModuleName][$fToken] = $userRecordModel->getId();
                            } else {
                                if ($fName == "roleid") {
                                    $tokens[$tModuleName][$fToken] = getRoleName($userRecordModel->get("roleid"));
                                } else {
                                    if (!in_array($fName, $ignore) && array_key_exists($fName, $referenceFields)) {
                                        if (!$userRecordModel->get($fName)) {
                                            $tokens[$tModuleName][$fToken] = "";
                                            continue;
                                        }
                                        $relatedRecordModel = Vtiger_Record_Model::getInstanceById($userRecordModel->get($fName));
                                        $tokens[$tModuleName][$fToken] = $relatedRecordModel ? $relatedRecordModel->getName() : "";
                                    } else {
                                        $fieldModel = $userRecordModel->getField($fName);
                                        if (!$fieldModel) {
                                            unset($tokens[$tModuleName][$fToken]);
                                            continue;
                                        }
                                        $fieldDataType = $fieldModel->getFieldDataType();
                                        $needValue = $userRecordModel->getDisplayValue($fName, $userModuleModel->getId());
                                        if ($fName == "signature") {
                                            $needValue = preg_replace("#(<\\s*br[^/>]*/?\\s*>\\s*)#is", "<br />", html_entity_decode($userRecordModel->get($fName)));
                                        }
                                        if (in_array($fieldDataType, array("email", "documentsFolder", "fileLocationType", "documentsFileUpload"))) {
                                            $needValue = $userRecordModel->get($fName);
                                        }
                                        $tokens[$tModuleName][$fToken] = $needValue;
                                    }
                                }
                            }
                        }
                    }
                    $export[] = $tModuleName;
                }
                foreach ($referenceFields as $fieldName => $fieldModel) {
                    $relatedFieldValue = $recordModel->get($fieldName);
                    $referenceList = $fieldModel->getReferenceList();
                    $referenceModuleName = NULL;
                    if (in_array($fieldName, $ignore) || !$relatedFieldValue || !QuotingToolUtils::isRecordExists($recordModel->get($fieldName))) {
                        foreach ($referenceList as $ref) {
                            if (!isset($tokens[$ref]) || !$tokens[$ref] || in_array($ref, $export)) {
                                continue;
                            }
                            $relatedFields = $tokens[$ref];
                            $relatedToModuleModel = Vtiger_Module_Model::getInstance($ref);
                            if ($relatedToModuleModel) {
                                foreach ($relatedFields as $fToken => $fName) {
                                    if (strpos($fName, "__") !== false) {
                                        $currentField = explode("__", $fName, 2);
                                        $currentField = $currentField[1];
                                        if ($currentField == $fieldName) {
                                            $tokens[$ref][$fToken] = "";
                                        }
                                        continue;
                                    }
                                    $fieldRelatedModel = Vtiger_Field_Model::getInstance($fName, $relatedToModuleModel);
                                    if ($fieldName == $fName || $fieldRelatedModel) {
                                        $tokens[$ref][$fToken] = "";
                                    }
                                }
                            }
                        }
                        continue;
                    } else {
                        if (1 < count($referenceList)) {
                            $newRecordModel = Vtiger_Record_Model::getInstanceById($relatedFieldValue);
                            $name = $newRecordModel->getModuleName();
                            $referenceModuleName = $name;
                        } else {
                            if ($referenceList) {
                                $referenceModuleName = $referenceList[0];
                            }
                        }
                        $relatedRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get($fieldName), $referenceModuleName);
                        $relatedModuleName = $relatedRecordModel->getModuleName();
                        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName)->getFieldsByType("reference");
                        if (!array_key_exists($relatedModuleName, $tokens)) {
                            continue;
                        }
                        $relatedFields = $tokens[$relatedModuleName];
                        foreach ($relatedFields as $fToken => $fName) {
                            $checkName = explode("__", $fToken, 2);
                            if ($fName != trim($checkName[1], "\$")) {
                                continue;
                            }
                            $checkRelatedField = explode("__", $fName);
                            if ($checkRelatedField[1]) {
                                $fName = $checkRelatedField[0];
                                if ($fieldName != $checkRelatedField[1]) {
                                    continue;
                                }
                            }
                            $relatedFieldModel = $relatedRecordModel->getField($fName);
                            if ($fName == $crmid) {
                                $tokens[$relatedModuleName][$fToken] = $relatedRecordModel->getId();
                            } else {
                                if (!$relatedFieldModel) {
                                    $tokens[$relatedModuleName][$fToken] = "";
                                    continue;
                                }
                                if (array_key_exists($fName, $relatedModuleModel) && $relatedRecordModel->get($fName) && !in_array($fName, $ignore)) {
                                    $refRelatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecordModel->get($fName));
                                    $tokens[$relatedModuleName][$fToken] = $refRelatedRecordModel->getDisplayName();
                                    continue;
                                }
                                $fieldDataType = $relatedFieldModel->getFieldDataType();
                                $needValue = $relatedRecordModel->getDisplayValue($fName, $relatedRecordModel->getId());
                                if ($fName == "assigned_user_id") {
                                    $needValue = strip_tags($needValue);
                                }
                                if (in_array($fieldDataType, array("url", "email", "documentsFolder", "fileLocationType", "documentsFileUpload", "text"))) {
                                    $needValue = $relatedRecordModel->get($fName);
                                } else {
                                    if (($fieldDataType == "date" || $fieldDataType == "datetime") && $needValue != "" && $dateFormat != "") {
                                        if ($fieldDataType == "datetime") {
                                            $valuesList = explode(" ", $needValue);
                                            if (count($valuesList) == 1) {
                                                $needValue = "";
                                            }
                                            $newVal = $valuesList[0];
                                            $needValue = DateTime::createFromFormat($userFormat, $newVal)->format($dateFormat) . " " . $valuesList[1] . " " . $valuesList[2];
                                        } else {
                                            $needValue = DateTime::createFromFormat($userFormat, $needValue)->format($dateFormat);
                                        }
                                    }
                                }
                                if ($fieldDataType == "picklist" && $needValue != "") {
                                    if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                        $picklistColor = $relatedFieldModel->getPicklistColors();
                                        $color = $picklistColor[$needValue];
                                        $textColor = QuotingTool::getTextColor($color);
                                        if ($color != "") {
                                            $needValue = "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $needValue . "</span>";
                                        }
                                    }
                                } else {
                                    if ($fieldDataType == "multipicklist" && $needValue != "" && version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                        $picklistColor = $relatedFieldModel->getPicklistColors();
                                        $listValue = explode(",", $needValue);
                                        $needValue = "";
                                        $index = 0;
                                        $count = count($listValue);
                                        foreach ($listValue as $picklistValue) {
                                            $index++;
                                            $color = $picklistColor[trim($picklistValue)];
                                            $textColor = QuotingTool::getTextColor($color);
                                            if ($color != "") {
                                                if ($index == $count) {
                                                    $needValue .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>";
                                                } else {
                                                    $needValue .= "<span style=\"background-color: " . $color . ";color: " . $textColor . "\">" . $picklistValue . "</span>, ";
                                                }
                                            } else {
                                                if ($index == $count) {
                                                    $needValue .= $picklistValue;
                                                } else {
                                                    $needValue .= $picklistValue . ", ";
                                                }
                                            }
                                        }
                                    }
                                }
                                $tokens[$relatedModuleName][$fToken] = html_entity_decode($relatedRecordModel->get($fName) == "0" ? "" : $needValue);
                            }
                        }
                    }
                }
            }
        }
        foreach ($tokens as $tModuleName => $tFields) {
            if (!in_array($tModuleName, $supportedModulesList) && !in_array($tModuleName, $this->specialModules)) {
                continue;
            }
            foreach ($tFields as $k => $f) {
                if (strpos($k, "cf_acf_rtf") !== false) {
                    $f = htmlspecialchars_decode($f);
                } else {
                    $f = nl2br($f);
                }
                $content = str_replace($k, $f, $content);
            }
        }
        return $content;
    }
    public function mergeAbbrievations($content)
    {
        include_once "include/simplehtmldom/simple_html_dom.php";
        $html = str_get_html($content);
        if (!$html) {
            return $content;
        }
        foreach ($html->find("abbr") as $abbr) {
            $title = $abbr->getAttribute("title");
            $abbr->outertext = $title;
        }
        $content = $html->save();
        return $content;
    }
    /**
     * Fn - runCustomFunctions
     *
     * @param string $content
     * @return string
     */
    public function mergeCustomFunctions($content)
    {
        if (is_numeric(strpos($content, "[CUSTOMFUNCTION|"))) {
            include_once "include/simplehtmldom/simple_html_dom.php";
            foreach (glob("modules/QuotingTool/resources/functions/*.php") as $cfFile) {
                include_once $cfFile;
            }
            $data = array();
            $data["[CUSTOMFUNCTION|"] = "<customfunction>";
            $data["|CUSTOMFUNCTION]"] = "</customfunction>";
            $content = $this->mergeBodyHtml($content, $data);
            $domBodyHtml = str_get_html($content);
            if (is_array($domBodyHtml->find("customfunction"))) {
                foreach ($domBodyHtml->find("customfunction") as $element) {
                    $params = $this->splitParametersFromText(trim($element->plaintext));
                    $function_name = $params[0];
                    unset($params[0]);
                    $result = call_user_func_array($function_name, $params);
                    $result = nl2br($result);
                    $element->outertext = $result;
                }
                $content = $domBodyHtml->save();
            }
        }
        return $content;
    }
    /**
     * Fn - mergeBodyHtml
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    private function mergeBodyHtml($content, $data)
    {
        if (!empty($data)) {
            $content = str_replace(array_keys($data), $data, $content);
            return $content;
        }
    }
    /**
     * @param $content
     * @param $keys_values - Example: array('$custom_proposal_link$' => 'modules/QuotingTool/proposal/index.php?record=1')
     * @return string
     */
    public function mergeCustomTokens($content, $keys_values)
    {
        foreach ($keys_values as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
    /**
     * @param $content
     * @param $keys_values - Example: array('$custom_proposal_link$' => 'modules/QuotingTool/proposal/index.php?record=1')
     * @return string
     */
    public function mergeEscapeCharacters($content, $keys_values)
    {
        foreach ($keys_values as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
    /**
     * Fn - splitParametersFromText
     *
     * @param string $text
     * @return array
     */
    private function splitParametersFromText($text)
    {
        $params = array();
        $end = false;
        do {
            if (strstr($text, "|")) {
                if ($text[0] == "\"") {
                    $delimiter = "\"|";
                    $text = substr($text, 1);
                } else {
                    if (substr($text, 0, 6) == "&quot;") {
                        $delimiter = "&quot;|";
                        $text = substr($text, 6);
                    } else {
                        $delimiter = "|";
                    }
                }
                list($params[], $text) = explode($delimiter, $text, 2);
            } else {
                $params[] = $text;
                $end = true;
            }
        } while (!$end);
        return $params;
    }
    /**
     * @param string $moduleName
     * @return array
     */
    public function getOtherFields($moduleName)
    {
        $blocks = array();
        $blocks[] = array("id" => 0, "name" => "LBL_COMMON_FIELDS", "label" => vtranslate("LBL_COMMON_FIELDS", self::MODULE_NAME), "fields" => array(array("id" => 0, "name" => "crmid", "label" => vtranslate("crmid", self::MODULE_NAME), "token" => $this->convertFieldToken("crmid", $moduleName), "datatype" => "integer")));
        return $this->fillBlockFields($moduleName, $blocks);
    }
    /**
     * @param string $moduleName
     * @return array
     */
    public function getItemDetailsFields($moduleName)
    {
        $blocks = array();
        $blocks[] = array("name" => "LBL_ITEM_DETAILS", "fields" => array(array("name" => "sequence_no", "datatype" => "integer"), array("name" => "totalAfterDiscount", "datatype" => "currency"), array("name" => "netPrice", "datatype" => "currency"), array("name" => "unitPrice", "datatype" => "currency"), array("name" => "itemNameWithDes", "datatype" => "text", "label" => "Item Name (with description)"), array("name" => "taxTotal", "datatype" => "currency", "label" => "Tax ")));
        return $this->fillBlockFields($moduleName, $blocks);
    }
    /**
     * @param Vtiger_Module_Model $moduleModel
     * @param array $excludeBlocks
     * @return array
     * @throws Exception
     */
    public function parseModule($moduleModel, $excludeBlocks = array(), $isCreateField = false)
    {
        $moduleId = $moduleModel->getId();
        $moduleName = $moduleModel->getName();
        $moduleFields = $moduleModel->getFields();
        $moduleInfo = array();
        $moduleInfo["id"] = $moduleId;
        $moduleInfo["name"] = $moduleName;
        if ($moduleModel->childFieldLabel) {
            $moduleInfo["label"] = vtranslate($moduleModel->get("label"), $moduleName) . "-" . $moduleModel->childFieldLabel;
        } else {
            $moduleInfo["label"] = vtranslate($moduleModel->get("label"), $moduleName);
        }
        $moduleInfo["fields"] = array();
        $totalFields = array("hdnSubTotal", "shipping", "hdnGrandTotal", "hdnTaxType", "hdnS_H_Amount", "pre_tax_total", "txtAdjustment", "received", "balance", "paid");
        if (in_array($moduleName, array("Invoice", "Quotes", "PurchaseOrder", "SalesOrder"))) {
            $vteItemsModuleName = "VTEItems";
            $vteItemsModuleModel = Vtiger_Module_Model::getInstance($vteItemsModuleName);
            $quoterModuleName = "Quoter";
            $quoterModel = Vtiger_Module_Model::getInstance($quoterModuleName);
            if ($vteItemsModuleModel && $vteItemsModuleModel->isActive() && $quoterModel && $quoterModel->isActive()) {
                $quotingToolModel = new QuotingTool_Module_Model();
                $totalSetting = $quotingToolModel->getAllTotalFieldsSetting();
                foreach ($totalSetting as $fieldName) {
                    if (!in_array($fieldName, $totalFields)) {
                        array_push($totalFields, $fieldName);
                    }
                }
            }
        }
        foreach ($moduleFields as $moduleField) {
            if ($moduleField->get("presence") == 1 || in_array($moduleField->getName(), $this->ignoreSpecialFields)) {
                continue;
            }
            if ($isCreateField == true && (in_array($moduleField->get("uitype"), array(4, 10, 53, 52, 58)) || $moduleField->get("displaytype") != 1 || $moduleField->get("presence") == 1 || $moduleField->get("name") == "isconvertedfromlead" || strpos($moduleField->get("name"), "isconvertedfrom") !== false)) {
                continue;
            }
            if (in_array($moduleName, array("Invoice", "Quotes", "PurchaseOrder", "SalesOrder")) && in_array($moduleField->get("label"), array("Discount Amount", "Discount Percent", "Items Total", "Tax Region", "Taxes On Charges", "S&amp;H Percent"))) {
                continue;
            }
            $fieldInfo = array();
            $fieldInfo["id"] = $moduleField->getId();
            $fieldInfo["uitype"] = $moduleField->get("uitype");
            $fieldInfo["datatype"] = $moduleField->getFieldDataType();
            $fieldInfo["name"] = $moduleField->getName();
            $fieldLabel = vtranslate($moduleField->get("label"), $moduleName);
            if ($moduleField->get("uitype") == 83) {
                $fieldLabel = "Tax: " . vtranslate($moduleField->get("label"), $moduleName);
            }
            if ($moduleField->getName() == "comment") {
                $fieldLabel = "Item Description";
            }
            $fieldInfo["label"] = $fieldLabel;
            $childField = "";
            if ($moduleModel->childField) {
                $childField = "__" . $moduleModel->childField;
            }
            $fieldInfo["token"] = $this->convertFieldToken($moduleField->getName() . $childField, $moduleName);
            $block = $moduleField->get("block");
            if (in_array($moduleField->getName(), $totalFields) && in_array($moduleName, array("Invoice", "Quotes", "PurchaseOrder", "SalesOrder"))) {
                $block->label = vtranslate("Total", $moduleName);
                $block->name = "Total";
            }
            $fieldInfo["block"] = array("id" => $block->id, "name" => $block->label, "label" => vtranslate($block->label, $moduleName));
            $ignore = false;
            $injectFields = $this->injectFields;
            if (isset($injectFields[$moduleName])) {
                $ignoreBlocks = $injectFields[$moduleName];
                foreach ($ignoreBlocks as $ignoreBlock => $ignoreFields) {
                    if ($block->label != $ignoreBlock) {
                        continue;
                    }
                    foreach ($ignoreFields as $ignoreField) {
                        if ($ignoreField == "*" || $ignoreField == $fieldInfo["name"]) {
                            $ignore = true;
                            break 2;
                        }
                    }
                }
            }
            if (!$ignore) {
                $moduleInfo["fields"][] = $fieldInfo;
            }
        }
        if (in_array($moduleName, $this->inventoryModules)) {
            $moduleInfo["fields"] = array_merge($moduleInfo["fields"], $this->getItemDetailsFields($moduleName));
        }
        $moduleFieldsArray = QuotingTool_Module_Model::array_orderby($moduleInfo["fields"], "label", SORT_ASC);
        $moduleInfo["fields"] = $moduleFieldsArray;
        if ($isCreateField != true) {
            $moduleInfo["fields"] = array_merge($moduleInfo["fields"], $this->getOtherFields($moduleName));
        }
        if ($excludeBlocks && 0 < count($excludeBlocks)) {
            $tmpFields = array();
            foreach ($moduleInfo["fields"] as $f => $fieldInfo) {
                $ignore = false;
                foreach ($excludeBlocks as $ignoreBlock => $ignoreFields) {
                    if ($fieldInfo["block"]["name"] != $ignoreBlock) {
                        continue;
                    }
                    foreach ($ignoreFields as $ignoreField) {
                        if ($ignoreField == "*" || $ignoreField == $fieldInfo["name"]) {
                            $ignore = true;
                            break 2;
                        }
                    }
                }
                if (!$ignore) {
                    $tmpFields[] = $moduleInfo["fields"][$f];
                }
            }
            $moduleInfo["fields"] = $tmpFields;
        }
        return $moduleInfo;
    }
    /**
     * @param Vtiger_Module_Model $currentModuleModel
     * @return array
     */
    public function getRelatedModules($currentModuleModel)
    {
        $relatedModules = array();
        $relatedModulesCheck = array();
        $relatedModulesDuplicate = array();
        $referenceFields = $currentModuleModel->getFieldsByType("reference");
        foreach ($referenceFields as $fieldModel) {
            $referenceModules = $fieldModel->getReferenceList();
            if (count($referenceModules) == 2 && $referenceModules[0] == "Campaigns") {
                unset($referenceModules[0]);
            }
            $relatedModuleKeysCheck = array_keys($relatedModulesDuplicate);
            foreach ($referenceModules as $k => $relatedModule) {
                if ((in_array($relatedModule, $relatedModulesCheck) || count($relatedModulesCheck) == 0) && !in_array($relatedModule, $relatedModuleKeysCheck)) {
                    $relatedModulesDuplicate[$relatedModule] = 1;
                }
                $relatedModulesCheck[] = $relatedModule;
            }
        }
        foreach ($referenceFields as $fieldModel) {
            $referenceModules = $fieldModel->getReferenceList();
            if (count($referenceModules) == 2 && $referenceModules[0] == "Campaigns") {
                unset($referenceModules[0]);
            }
            $relatedModuleKeys = array_keys($relatedModules);
            foreach ($referenceModules as $k => $relatedModule) {
                if (!in_array($relatedModule, $relatedModuleKeys)) {
                    $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
                    if ($relatedModulesDuplicate[$relatedModule] == 1) {
                        $relatedModuleModel->childField = $fieldModel->getName();
                        $relatedModuleModel->childFieldLabel = vtranslate($fieldModel->get("label"), $currentModuleModel->getName());
                        $relatedModules[$referenceModules[$k] . "__" . $fieldModel->getName()] = $relatedModuleModel;
                    } else {
                        $relatedModules[$referenceModules[$k]] = $relatedModuleModel;
                    }
                }
            }
        }
        return $relatedModules;
    }
    /**
     * @param string $content
     * @param string $header
     * @param string $footer
     * @param string $name
     * @param string $path
     * @param array $styles
     * @param array $scripts
     * @param bool $escapeForm
     * @return string - path file
     */
    public function createPdf($content, $header = "", $footer = "", $name, $settings_layout = "", $entityId = "", $path = "storage/QuotingTool/", $styles = array(), $scripts = array(), $escapeForm = true)
    {
        global $site_URL;
        global $adb;
        global $default_timezone;
        global $vtiger_current_version;
        if (!file_exists($path) && !mkdir($path, 511, true)) {
            return "";
        }
        if (!class_exists("mPDF")) {
            require_once "test/QuotingTool/resources/mpdf/mpdf.php";
        }
        include_once "include/simplehtmldom/simple_html_dom.php";
        date_default_timezone_set($default_timezone);
        $header = str_replace("#PG_NUM#", "{PAGENO}", $header);
        $header = str_replace("#PG_NUM_TOTAL#", "{nb}", $header);
        $footer = str_replace("#PG_NUM#", "{PAGENO}", $footer);
        $footer = str_replace("#PG_NUM_TOTAL#", "{nb}", $footer);
        if ($escapeForm) {
            $contentDom = str_get_html($content);
            $inputs = $contentDom->find("input, textarea");
            $tagsLink = $contentDom->find("a");
            if (is_array($tagsLink)) {
                foreach ($tagsLink as $key => $value) {
                    if ($value->getAttribute("data-target") == "#myModal") {
                        $value->href = "";
                    }
                }
            }
            if (is_array($inputs)) {
                foreach ($inputs as $k => $input) {
                    $value = $input->value;
                    if ($input->tag == "textarea") {
                        $value = $input->text();
                        $value = str_replace("&lt;br&gt;", "<br>", $value);
                        $value = nl2br($value);
                    }
                    $class = $input->class;
                    $style = $input->style;
                    $type = $input->type;
                    if (strpos($class, "is_merge_field") !== false) {
                        $style = str_replace("border-color: rgb(204, 204, 204)", "", $style);
                        $replaceBy = "<span class=\"" . $class . " uneditable-input\" style=\"display: inline; " . $style . "\">" . $value . "</span>";
                        $inputs[$k]->outertext = $replaceBy;
                    } else {
                        if ($type == "text") {
                            $replaceBy = "<div class=\"" . $class . " uneditable-input\" style=\"display: inline; " . $style . "\">" . $value . "</div>";
                            $inputs[$k]->outertext = $replaceBy;
                        } else {
                            if ($type == "checkbox") {
                                $inputs[$k]->disabled = "disabled";
                            }
                        }
                    }
                }
            }
            $content = $contentDom->save();
        }
        $tmp_html = str_get_html($content);
        foreach ($tmp_html->find("table") as $table) {
            if ($table->getAttribute("data-table-type") == "create_related_record") {
                foreach ($table->find("textarea") as $textArea) {
                    $value = str_replace("&lt;br&gt;", "<br>", $textArea->innertext);
                    $value = nl2br($value);
                    $textArea->outertext = $value;
                }
                foreach ($table->find("[name=\"data-fields\"]") as $dataFields) {
                    $dataFields->parent->outertext = "";
                }
                $table->find(".tbody-content", 0)->outertext = "";
            }
        }
        foreach ($tmp_html->find("a") as $atag) {
            if ($atag->getAttribute("id") == "addRecord") {
                $atag->outertext = "";
            }
        }
        $QuotingToolRecordModel = new QuotingTool_Record_Model();
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
                                $imageUrl = $QuotingToolRecordModel->getAttachmentFile($image[0]["id"], $image[0]["name"]);
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
            if (strpos("quoting_tool-widget-signature-image", $img->class) === false && strpos("quoting_tool-widget-secondary_signature-image", $img->class) === false) {
                $imageSrc = $img->getAttribute("src");
                $imageSrc = str_replace(" ", "%20", $imageSrc);
                if (strpos($imageSrc, "http://") !== false || strpos($imageSrc, "https://") !== false) {
                    $url = trim($imageSrc);
                    $parsed = parse_url($url);
                    if (isset($parsed["scheme"]) && strtolower($parsed["scheme"]) == "https") {
                        $imageSrc = "http://" . substr($url, 8);
                    }
                    if (!getimagesize($imageSrc)) {
                        if (strpos($imageSrc, rtrim($site_URL, "/")) !== false) {
                            $imageSrc = ltrim(str_replace(rtrim($site_URL, "/"), "", $imageSrc), "/");
                            $imageSrc = dirname(__FILE__) . "/../../" . $imageSrc;
                        }
                        if (!getimagesize($imageSrc) && !$this->checkRemoteFile($imageSrc)) {
                            $img->outertext = "";
                        }
                    }
                } else {
                    if (strpos($imageSrc, "data:image") === false) {
                        $img->setAttribute("src", rtrim($site_URL, "/") . $imageSrc);
                        if (!getimagesize(rtrim($site_URL, "/") . $imageSrc)) {
                            $img->outertext = "";
                        }
                    }
                }
            }
            if ($quoting_tool_product_image == "quoting_tool-widget-signature-image" || $quoting_tool_product_image == "quoting_tool-widget-secondary_signature-image") {
                $parrentTag = $img->parent();
                if ($parrentTag->getAttribute("href") == "javascript:;") {
                    $parrentTag->setAttribute("href", "");
                }
            }
        }
        $content = $tmp_html->save();
        preg_match_all("'\\[BARCODE\\|(.*?)\\|BARCODE\\]'si", $content, $match);
        if (0 < count($match)) {
            require_once "modules/QuotingTool/resources/barcode/autoload.php";
            $content = preg_replace_callback("/\\[BARCODE\\|(.+?)\\|BARCODE\\]/", function ($barcode_val) {
                $array_values = explode("=", $barcode_val[1]);
                list($method, $field_value) = $array_values;
                $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                $barcode_png = "<img src=\"data:image/png;base64," . base64_encode($generator->getBarcode($field_value, $this->barcode_type_code[$method])) . "\" />";
                return $barcode_png;
            }, $content);
        }
        $content = "<div id=\"quoting_tool-body\">" . $content . "</div>";
        $site = rtrim($site_URL, "/");
        $content = preg_replace("/\\/\\/test\\//", "/test/", $content);
        $content = str_replace($site . "/test/upload/images/", "test/upload/images/", $content);
        $content = str_replace($site . "/modules/QuotingTool/resources/images/", "modules/QuotingTool/resources/images/", $content);
        $content = str_replace($site . "/storage/", "storage/", $content);
        $mpdf = new mPDF();
        $mpdf->showImageErrors = true;
        $mPDFFontLocation = "test/QuotingTool/resources/mpdf/ttfonts/";
        $customFontLocation = "test/QuotingTool/resources/font/";
        error_reporting(1 | 4);
        if ($handle = opendir("test/QuotingTool/resources/font")) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $fileSplit = explode(".", $entry);
                    list($nameFont, $fileType) = $fileSplit;
                    copy($customFontLocation . $entry, $mPDFFontLocation . $entry);
                    $mpdf->fontdata[strtolower($nameFont)] = array("R" => $entry);
                }
            }
            closedir($handle);
        }
        $mpdf->available_unifonts = array();
        foreach ($mpdf->fontdata as $f => $fs) {
            if (isset($fs["R"]) && $fs["R"]) {
                $mpdf->available_unifonts[] = $f;
            }
            if (isset($fs["B"]) && $fs["B"]) {
                $mpdf->available_unifonts[] = $f . "B";
            }
            if (isset($fs["I"]) && $fs["I"]) {
                $mpdf->available_unifonts[] = $f . "I";
            }
            if (isset($fs["BI"]) && $fs["BI"]) {
                $mpdf->available_unifonts[] = $f . "BI";
            }
        }
        $mpdf->default_available_fonts = $mpdf->available_unifonts;
        $setting = false;
        if ($settings_layout != "") {
            $settings_layout = json_decode(html_entity_decode($settings_layout));
            $settings_layout = (array) $settings_layout;
            if ($settings_layout["format"] == "Custom") {
                $settings_layout["newformat"] = array($settings_layout["width"], $settings_layout["height"]);
                unset($settings_layout["width"]);
                unset($settings_layout["height"]);
                unset($settings_layout["format"]);
            } else {
                if ($settings_layout["orientation"] == "L") {
                    $settings_layout["newformat"] = $settings_layout["format"] . "-" . $settings_layout["orientation"];
                    unset($settings_layout["orientation"]);
                } else {
                    $settings_layout["newformat"] = $settings_layout["format"];
                }
                unset($settings_layout["format"]);
            }
            $settings_layout["mgl"] = $settings_layout["margin_left"];
            $settings_layout["mgr"] = $settings_layout["margin_right"];
            $settings_layout["mgt"] = $settings_layout["margin_top"];
            $settings_layout["mgb"] = $settings_layout["margin_bottom"];
            unset($settings_layout["margin_left"]);
            unset($settings_layout["margin_right"]);
            unset($settings_layout["margin_top"]);
            unset($settings_layout["margin_bottom"]);
            $mpdf->useActiveForms = true;
            if (!$styles) {
                $styles = array();
            }
            $styles = array_merge($styles, array("modules/QuotingTool/resources/styles.css", "modules/QuotingTool/resources/pdf.css"));
            foreach ($styles as $css) {
                $stylesheet = file_get_contents($css);
                $mpdf->WriteHTML($stylesheet, 1);
            }
            if (!$scripts) {
                $scripts = array();
            }
            foreach ($scripts as $js) {
                $cScript = file_get_contents($js);
                $mpdf->WriteHTML($cScript, 1);
            }
            $mpdf->setAutoTopMargin = "pad";
            $mpdf->setAutoBottomMargin = "pad";
            $mpdf->orig_tMargin = $settings_layout["mgt"];
            $mpdf->orig_fMargin = $settings_layout["mgb"];
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPageByArray($settings_layout);
            $setting = true;
        }
        if ($setting != true) {
            $mpdf->useActiveForms = true;
            $mpdf->setAutoTopMargin = "stretch";
            $mpdf->setAutoBottomMargin = "stretch";
            if (!$styles) {
                $styles = array();
            }
            $styles = array_merge($styles, array("modules/QuotingTool/resources/styles.css", "modules/QuotingTool/resources/pdf.css"));
            foreach ($styles as $css) {
                $stylesheet = file_get_contents($css);
                $mpdf->WriteHTML($stylesheet, 1);
            }
            if (!$scripts) {
                $scripts = array();
            }
            foreach ($scripts as $js) {
                $cScript = file_get_contents($js);
                $mpdf->WriteHTML($cScript, 1);
            }
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
        }
        $mpdf->WriteHTML($content);
        $pattern = "/\t|\n|\\`|\\~|\\!|\\@|\\#|\\%|\\^|\\&|\\*|\\(|\\)|\\+|\\-|\\=|\\[|\\{|\\]|\\}|\\||\\|\\'|\\<|\\,|\\.|\\>|\\?|\\/|\"|'|\\;|\\:/";
        $name = str_replace(".pdf", "", $name);
        $name = preg_replace($pattern, "_", html_entity_decode($name, ENT_QUOTES));
        $name = str_replace(" ", "_", $name);
        $name = str_replace("\$", "_", $name);
        $name = trim($name);
        $fullFileName = $path . $name . ".pdf";
        $mpdf->Output($fullFileName, "F");
        return $fullFileName;
    }
    public function checkRemoteFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result !== false) {
            return true;
        }
        return false;
    }
    /**
     * @param $content
     * @param $module
     * @param $record
     * @return mixed|string
     */
    public function parseTokens($content, $module, $record, $customFunction = array())
    {
        $content = $this->checkCustomFunctions($content, $module, $record, $customFunction);
        $content = $this->mergeAbbrievations($content);
        $tokens = $this->getFieldTokenFromString($content);
        $content = $this->mergeBlockTokens($tokens, $record, $content);
        $vteItemsModuleName = "VTEItems";
        $vteItemsModuleModel = Vtiger_Module_Model::getInstance($vteItemsModuleName);
        $quoterModuleName = "Quoter";
        $quoterModel = Vtiger_Module_Model::getInstance($quoterModuleName);
        if ($vteItemsModuleModel && $vteItemsModuleModel->isActive() && $quoterModel && $quoterModel->isActive()) {
            $content = $this->mergeQuoterBlockTokens($tokens, $record, $content);
        }
        $content = $this->mergeLinkModulesTokens($tokens, $record, $content);
        $content = $this->mergeCreateRelatedRecord($tokens, $record, $content);
        $content = $this->mergeTokens($tokens, $record, $content, $module);
        $content = $this->mergeCustomFunctions($content);
        $escapeCharacters = $this->getEscapeCharactersFromString($content);
        $content = $this->mergeEscapeCharacters($content, $escapeCharacters);
        return $content;
    }
    /**
     * @param string $moduleName
     */
    public function installWorkflows($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        foreach ($this->workflows as $name => $label) {
            $dest1 = "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
            $source1 = "modules/" . $moduleName . "/workflow/" . $name . ".inc";
            @shell_exec("rm -f modules/com_vtiger_workflow/tasks/" . $name . ".inc");
            @shell_exec("rm -f " . $template_folder . "/modules/Settings/Workflows/Tasks/" . $name . ".tpl");
            $file_exist1 = false;
            $file_exist2 = false;
            if (file_exists($dest1)) {
                $file_exist1 = true;
            } else {
                if (copy($source1, $dest1)) {
                    $file_exist1 = true;
                }
            }
            $dest2 = (string) $template_folder . "/modules/Settings/Workflows/Tasks/" . $name . ".tpl";
            $source2 = (string) $template_folder . "/modules/" . $moduleName . "/taskforms/" . $name . ".tpl";
            $templatepath = "modules/" . $moduleName . "/taskforms/" . $name . ".tpl";
            if (file_exists($dest2)) {
                $file_exist2 = true;
            } else {
                if (copy($source2, $dest2)) {
                    $file_exist2 = true;
                }
            }
            if ($file_exist1 && $file_exist2) {
                $sql1 = "SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?";
                $result1 = $adb->pquery($sql1, array($name));
                if ($adb->num_rows($result1) == 0) {
                    $taskType = array("name" => $name, "label" => $label, "classname" => $name, "classpath" => $source1, "templatepath" => $templatepath, "modules" => array("include" => array(), "exclude" => array()), "sourcemodule" => $moduleName);
                    VTTaskType::registerTaskType($taskType);
                }
            }
        }
    }
    /**
     * @param string $moduleName
     */
    private function removeWorkflows($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $sql1 = "DELETE FROM com_vtiger_workflow_tasktypes WHERE sourcemodule = ?";
        $adb->pquery($sql1, array($moduleName));
        foreach ($this->workflows as $name => $label) {
            $likeTasks = "%:\"" . $name . "\":%";
            $sql2 = "DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?";
            $adb->pquery($sql2, array($likeTasks));
            @shell_exec("rm -f modules/com_vtiger_workflow/tasks/" . $name . ".inc");
            @shell_exec("rm -f " . $template_folder . "/modules/Settings/Workflows/Tasks/" . $name . ".tpl");
        }
    }
    private function disableWorkflow()
    {
        global $adb;
        foreach ($this->workflows as $name => $label) {
            $sql2 = "SELECT * FROM com_vtiger_workflowtasks WHERE task LIKE ?";
            $likeTasks = "%:\"" . $name . "\":%";
            $rs = $adb->pquery($sql2, array($likeTasks));
            $num_rows = $adb->num_rows($rs);
            for ($i = 0; $i < $num_rows; $i++) {
                $row = $adb->raw_query_result_rowdata($rs, $i);
                $task = $row["task"];
                require_once "modules/com_vtiger_workflow/VTTaskManager.inc";
                require_once "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
                $unserializeTask = unserialize($task);
                $isActive = $unserializeTask->active;
                if ($isActive) {
                    $unserializeTask->active = false;
                    $unserializeTask->isEnable = true;
                } else {
                    $unserializeTask->isEnable = false;
                }
                $serializeTask = serialize($unserializeTask);
                $query = "UPDATE com_vtiger_workflowtasks SET task=? where workflow_id=? AND task_id=?";
                $adb->pquery($query, array($serializeTask, $row["workflow_id"], $row["task_id"]));
            }
        }
    }
    private function enableWorkflow()
    {
        global $adb;
        foreach ($this->workflows as $name => $label) {
            $sql2 = "SELECT * FROM com_vtiger_workflowtasks WHERE task LIKE ?";
            $likeTasks = "%:\"" . $name . "\":%";
            $rs = $adb->pquery($sql2, array($likeTasks));
            $num_rows = $adb->num_rows($rs);
            for ($i = 0; $i < $num_rows; $i++) {
                $row = $adb->raw_query_result_rowdata($rs, $i);
                $task = $row["task"];
                require_once "modules/com_vtiger_workflow/VTTaskManager.inc";
                require_once "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
                $unserializeTask = unserialize($task);
                $isEnable = $unserializeTask->isEnable;
                if ($isEnable) {
                    $unserializeTask->active = true;
                }
                $serializeTask = serialize($unserializeTask);
                $query = "UPDATE com_vtiger_workflowtasks SET task=? where workflow_id=? AND task_id=?";
                $adb->pquery($query, array($serializeTask, $row["workflow_id"], $row["task_id"]));
            }
        }
    }
    /**
     * @param $name
     * @param string $extension
     * @param string $hash
     * @return string
     */
    public function makeUniqueFile($name, $extension = "pdf", $hash = "")
    {
        $replace = "";
        $file = $name . $replace . $hash . "." . $extension;
        return $file;
    }
    /**
     * @param mixed $focus
     * @param string $name
     * @param string $path
     * @return bool
     */
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
    public function createAttachManuallyFile($focus, $name, $filetype, $path = "storage/QuotingTool/")
    {
        global $adb;
        global $current_user;
        $timestamp = date("Y-m-d H:i:s");
        $ownerid = $focus->column_fields["assigned_user_id"];
        $id = $adb->getUniqueID("vtiger_crmentity");
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
    /**
     * @param $relatedProducts
     * @param $queryProducts
     * @return mixed
     */
    public function mergeRelatedProductWithQueryProduct($relatedProducts, $queryProducts)
    {
        $data = array();
        $queryProductKey = 0;
        foreach ($queryProducts as $p => $array) {
            foreach ($array as $key => $val) {
                if (is_numeric($key)) {
                    unset($queryProducts[$p][$key]);
                }
            }
        }
        foreach ($relatedProducts as $k => $product) {
            $data[$k] = array();
            foreach ($product as $fieldName => $fieldValue) {
                if ($fieldName == "final_details") {
                    continue;
                }
                $myFieldName = rtrim($fieldName, $k);
                $data[$k][$myFieldName] = $fieldValue;
            }
            $data[$k] = array_merge($data[$k], $queryProducts[$queryProductKey++]);
        }
        return $data;
    }
    /**
     * Fn - formatNumber
     * @param $string_number
     * @return float
     */
    public function formatNumber($string_number)
    {
        global $current_user;
        $grouping = $current_user->currency_grouping_separator;
        $decimal = $current_user->currency_decimal_separator;
        $no_of_decimals = $current_user->no_of_currency_decimals;
        return number_format($string_number, $no_of_decimals, $decimal, $grouping);
    }
    public static function resetValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array(static::MODULE_NAME));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);", array(static::MODULE_NAME, "0"));
    }
    public static function updateCollation()
    {
        global $adb;
        $sql = "SHOW FULL COLUMNS FROM vtiger_tab WHERE Field IN ('name')";
        $res = $adb->pquery($sql, array());
        while ($row = $adb->fetchByAssoc($res)) {
            $vtiger_tab_collation = $row["collation"];
        }
        $sql = "SHOW FULL COLUMNS FROM vtiger_quotingtool WHERE Field IN ('module')";
        $res = $adb->pquery($sql, array());
        while ($row = $adb->fetchByAssoc($res)) {
            $vtiger_quotingtool_collation = $row["collation"];
        }
        if ($vtiger_tab_collation != $vtiger_quotingtool_collation) {
            $vtiger_tab_charsets = explode("_", $vtiger_tab_collation);
            $vtiger_tab_charset = $vtiger_tab_charsets[0];
            $sql = "ALTER TABLE `vtiger_quotingtool` MODIFY COLUMN `module`  varchar(255) CHARACTER SET " . $vtiger_tab_charset . " COLLATE " . $vtiger_tab_collation . " NOT NULL";
            $adb->pquery($sql, array());
        }
    }
    public static function removeValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array(static::MODULE_NAME));
    }
    public static function copyPlugins()
    {
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
            $extraPlugin = array("sharedspace", "doNothing", "youtube", "sourcedialog", "quotingtool", "confighelper", "abbr", "codemirror");
            $libraries = "libraries/jquery/ckeditor/plugins/";
            foreach ($extraPlugin as $plugin) {
                $ckPlugin = $libraries . $plugin . "/";
                $librariesSoures = "layouts/v7/modules/QuotingTool/resources/js/libs/ckeditor_4.5.6_full/plugins/" . $plugin . "/";
                self::recurse_copy($librariesSoures, $ckPlugin);
            }
            $officeSource = "layouts/v7/modules/QuotingTool/resources/js/libs/ckeditor_4.5.6_full/skins/office2013";
            $officeCore = "libraries/jquery/ckeditor/skins/office2013";
            self::recurse_copy($officeSource, $officeCore);
        }
    }
    public static function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if ($file != "." && $file != "..") {
                if (is_dir($src . "/" . $file)) {
                    $result = self::recurse_copy($src . "/" . $file, $dst . "/" . $file);
                } else {
                    $result = copy($src . "/" . $file, $dst . "/" . $file);
                }
            }
        }
        closedir($dir);
    }
    /**
     * Fn - getEmailList
     * Copy from SelectEmailFields.php
     *
     * @param string $moduleName
     * @param int $recordId
     * @return array
     */
    public function getEmailList($moduleName, $recordId, $isCreateNewRecord, $multiRecord)
    {
        global $adb;
        $email_field_list = array();
        $listRecord = array();
        if (!empty($multiRecord)) {
            $listRecord = $multiRecord;
        } else {
            array_push($listRecord, $recordId);
        }
        foreach ($listRecord as $recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $accountId = 0;
            $contactId = 0;
            $listReference = array();
            if ($moduleName == "Quotes" || $moduleName == "Invoice" || $moduleName == "Contacts" || $moduleName == "SalesOrder") {
                $accountId = $recordModel->get("account_id");
                $contactId = $recordModel->get("contact_id");
                $listReference[] = "account_id";
                $listReference[] = "contact_id";
            } else {
                if ($moduleName == "HelpDesk") {
                    $accountId = $recordModel->get("parent_id");
                    $contactId = $recordModel->get("contact_id");
                } else {
                    if ($moduleName == "Potentials") {
                        $accountId = $recordModel->get("related_to");
                        $listReference[] = "related_to";
                        $contactId = $recordModel->get("contact_id");
                        if (!in_array("contact_id", $listReference)) {
                            $listReference[] = "contact_id";
                        }
                    } else {
                        if ($moduleName == "Project") {
                            $accountId = $recordModel->get("linktoaccountscontacts");
                            $listReference[] = "linktoaccountscontacts";
                            if ($accountId && getSalesEntityType($accountId) != "Accounts") {
                                $contactId = $accountId;
                                $accountId = 0;
                            }
                        } else {
                            if ($moduleName == "ProjectTask" && QuotingToolUtils::isRecordExists($recordModel->get("projectid"))) {
                                $projectRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get("projectid"));
                                $listReference[] = "projectid";
                                $accountId = $projectRecordModel->get("linktoaccountscontacts");
                                if (!in_array("linktoaccountscontacts", $listReference)) {
                                    $listReference[] = "linktoaccountscontacts";
                                }
                                if ($accountId && getSalesEntityType($accountId) != "Accounts") {
                                    $contactId = $accountId;
                                    $accountId = 0;
                                }
                            } else {
                                if ($moduleName == "ServiceContracts") {
                                    $accountId = $recordModel->get("sc_related_to");
                                    $listReference[] = "sc_related_to";
                                    if ($accountId && getSalesEntityType($accountId) != "Accounts") {
                                        $contactId = $accountId;
                                        $accountId = 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($moduleName == "PurchaseOrder") {
                $contactId = $recordModel->get("contact_id");
            }
            if ($moduleName == "Contacts") {
                $contactId = $recordId;
            }
            if ($moduleName == "Accounts") {
                $accountId = $recordId;
            }
            if ($accountId && QuotingToolUtils::isRecordExists($accountId)) {
                $accountModuleModel = Vtiger_Module_Model::getInstance("Accounts");
                $accountRecordModel = Vtiger_Record_Model::getInstanceById($accountId);
                $emailFields = $accountModuleModel->getFieldsByType("email");
                $emailFields = array_keys($emailFields);
                $i = 1;
                foreach ($emailFields as $fieldname) {
                    $emailValue = $accountRecordModel->get($fieldname);
                    if ($emailValue) {
                        $email_field_list[$i . "||" . $accountId . "||" . $emailValue] = $accountRecordModel->getDisplayName() . " (" . $emailValue . ")";
                        $i++;
                    }
                }
            }
            if ($contactId && QuotingToolUtils::isRecordExists($contactId)) {
                $contactModuleModel = Vtiger_Module_Model::getInstance("Contacts");
                $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactId);
                $emailFields = $contactModuleModel->getFieldsByType("email");
                $emailFields = array_keys($emailFields);
                $i = 1;
                foreach ($emailFields as $fieldname) {
                    $emailValue = $contactRecordModel->get($fieldname);
                    if ($emailValue) {
                        $email_field_list[$i . "||" . $contactId . "||" . $emailValue] = $contactRecordModel->getDisplayName() . " (" . $emailValue . ")";
                        $i++;
                    }
                }
            }
            if ($moduleName == "Leads" || $moduleName == "Accounts" && QuotingToolUtils::isRecordExists($recordId)) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                $emailFields = $moduleModel->getFieldsByType("email");
                $emailFields = array_keys($emailFields);
                $i = 1;
                foreach ($emailFields as $fieldname) {
                    $emailValue = $recordModel->get($fieldname);
                    if ($emailValue) {
                        $email_field_list[$i . "||" . $contactId . "||" . $emailValue] = $recordModel->getDisplayName() . " (" . $emailValue . ")";
                        $i++;
                    }
                }
            }
            if ($moduleName == "PurchaseOrder") {
                $vendorId = $recordModel->get("vendor_id");
                $listReference[] = "vendor_id";
                if (QuotingToolUtils::isRecordExists($vendorId)) {
                    $moduleModel = Vtiger_Module_Model::getInstance("Vendors");
                    $recordModel = Vtiger_Record_Model::getInstanceById($vendorId);
                    $emailFields = $moduleModel->getFieldsByType("email");
                    $emailFields = array_keys($emailFields);
                    $i = 1;
                    $rs = $adb->pquery("SELECT DISTINCT\r\n                        vtiger_crmentity.crmid\r\n                    FROM\r\n                        vtiger_contactdetails\r\n                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid\r\n                        INNER JOIN vtiger_vendorcontactrel ON vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid\r\n                    WHERE\r\n                        vtiger_crmentity.deleted = 0 \r\n                        AND vtiger_vendorcontactrel.vendorid = ? ", array($vendorId));
                    if (0 < $adb->num_rows($rs)) {
                        $contactModuleModel = Vtiger_Module_Model::getInstance("Contacts");
                        $emailFields2 = $contactModuleModel->getFieldsByType("email");
                        $emailFields2 = array_keys($emailFields2);
                        while ($row = $adb->fetchByAssoc($rs)) {
                            $contactId2 = $row["crmid"];
                            $contactRecordModel2 = Vtiger_Record_Model::getInstanceById($contactId2, "Contacts");
                            foreach ($emailFields2 as $fieldname) {
                                $emailValue2 = $contactRecordModel2->get($fieldname);
                                if ($emailValue2) {
                                    $email_field_list[$i . "||" . $contactId2 . "||" . $emailValue2] = $contactRecordModel2->getDisplayName() . " (" . $emailValue2 . ")";
                                    $i++;
                                }
                            }
                        }
                    }
                    foreach ($emailFields as $fieldname) {
                        $emailValue = $recordModel->get($fieldname);
                        if ($emailValue) {
                            $email_field_list[$i . "||" . $contactId . "||" . $emailValue] = $recordModel->getDisplayName() . " (" . $emailValue . ")";
                            $i++;
                        }
                    }
                }
            }
            if (QuotingToolUtils::isRecordExists($recordId)) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                $restrictedFieldnames = array("modifiedby", "created_user_id", "assigned_user_id");
                $referenceFieldsModels = $moduleModel->getFieldsByType("reference");
                foreach ($referenceFieldsModels as $referenceFieldsModel) {
                    $relmoduleFieldname = $referenceFieldsModel->get("name");
                    $relModuleFieldValue = $recordModel->get($relmoduleFieldname);
                    if (!empty($relModuleFieldValue) && !in_array($relmoduleFieldname, $restrictedFieldnames) && QuotingToolUtils::isRecordExists($relModuleFieldValue) && !in_array($relmoduleFieldname, $listReference)) {
                        $relRecordModel = Vtiger_Record_Model::getInstanceById($relModuleFieldValue);
                        $refModuleModel = Vtiger_Module_Model::getInstance($relRecordModel->getModuleName());
                        $relEmailFields = $refModuleModel->getFieldsByType("email");
                        $relEmailFields = array_keys($relEmailFields);
                        $i = 1;
                        foreach ($relEmailFields as $fieldname) {
                            $refEmailValue = $relRecordModel->get($fieldname);
                            if ($refEmailValue) {
                                $email_field_list[$i . "||" . $relModuleFieldValue . "||" . $refEmailValue] = $relRecordModel->getDisplayName() . " (" . $refEmailValue . ")";
                                $i++;
                            }
                        }
                    }
                }
                $emailFields = $moduleModel->getFieldsByType("email");
                $emailFields = array_keys($emailFields);
                $i = 1;
                foreach ($emailFields as $fieldname) {
                    $emailValue = $recordModel->get($fieldname);
                    if ($emailValue) {
                        $email_field_list[$i . "||" . $recordId . "||" . $emailValue] = $recordModel->getDisplayName() . " (" . $emailValue . ")";
                        $i++;
                    }
                }
            }
            if ($isCreateNewRecord == 1 && QuotingToolUtils::isRecordExists($recordId)) {
                $moduleName = $recordModel->getModuleName();
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $emailFields = $moduleModel->getFieldsByType("email");
                $emailFields = array_keys($emailFields);
                $i = 1;
                foreach ($emailFields as $fieldname) {
                    $emailValue = $recordModel->get($fieldname);
                    if ($emailValue) {
                        $email_field_list[$i . "||" . $recordId . "||" . $emailValue] = $recordModel->getDisplayName() . " (" . $emailValue . ")";
                        $i++;
                    }
                }
            }
        }
        $email_field_list = array_unique($email_field_list);
        return $email_field_list;
    }
    public static function getConfig()
    {
        global $site_URL;
        global $current_user;
        $data = array();
        $data["base"] = $site_URL;
        $data["date_format"] = $current_user->date_format;
        $data["hour_format"] = $current_user->hour_format;
        $data["start_hour"] = $current_user->start_hour;
        $data["end_hour"] = $current_user->end_hour;
        $data["time_zone"] = $current_user->time_zone;
        $data["dayoftheweek"] = $current_user->dayoftheweek;
        return $data;
    }
    public static function getModules()
    {
        global $default_charset;
        $data = array();
        $quotingTool = new QuotingTool();
        $inventoryModules = getInventoryModules();
        $quotingTool->enableModules = $quotingTool->getAllEntityModule();
        $sortEnableModules = array();
        foreach ($quotingTool->enableModules as $moduleName) {
            $sortEnableModules[$moduleName] = vtranslate($moduleName, $moduleName);
        }
        asort($sortEnableModules);
        $quotingTool->enableModules = array_keys($sortEnableModules);
        foreach ($quotingTool->enableModules as $module) {
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $moduleInfo = $quotingTool->parseModule($moduleModel);
            $relations = $quotingTool->getRelatedModules($moduleModel);
            array_unshift($moduleInfo["fields"], array("label" => "Please select option", "id" => "-1", "name" => "", "block" => array("label" => "", "name" => "")));
            $moduleInfo["related_modules"] = array();
            $moduleInfo["link_modules"] = array();
            $moduleInfo["final_details"] = array();
            $linkModule = Vtiger_Relation_Model::getAllRelations($moduleModel, $selected = true, $onlyActive = true);
            $selectOptions = array();
            $selectOptions["id"] = -1;
            $selectOptions["name"] = "";
            $selectOptions["label"] = "Please select option";
            $selectOptions["fields"] = array();
            $selectOptions["fields"][] = array("label" => "Please select option");
            $moduleInfo["link_modules"][] = $selectOptions;
            $moduleInfo["create_related_record"][] = $selectOptions;
            foreach ($linkModule as $labelModule) {
                if (in_array($labelModule->get("modulename"), $quotingTool->ignoreLinkModules)) {
                    continue;
                }
                $moduleLinkModel = Vtiger_Module_Model::getInstance($labelModule->get("modulename"));
                $excludeBlocks = array("LBL_ITEM_DETAILS" => array("*"));
                if (in_array("ADD", explode(",", strtoupper($labelModule->get("actions")))) && !in_array($labelModule->get("modulename"), array("Invoice", "Quotes", "PurchaseOrder", "SalesOrder", "Calendar", "Documents"))) {
                    $preAddLinkModule = $quotingTool->parseModule($moduleLinkModel, $excludeBlocks, true);
                    $moduleInfo["create_related_record"][] = $preAddLinkModule;
                }
                $preAddLinkModule = $quotingTool->parseModule($moduleLinkModel, $excludeBlocks);
                $moduleInfo["link_modules"][] = $preAddLinkModule;
            }
            $selectOptions = array();
            $selectOptions["id"] = -1;
            $selectOptions["name"] = "";
            $selectOptions["label"] = "Please select option";
            $selectOptions["fields"] = array();
            $selectOptions["fields"][] = array("label" => "Please select option");
            $moduleInfo["related_modules"][] = $selectOptions;
            foreach ($relations as $relation) {
                if ($relation) {
                    if (in_array($module, array("Invoice", "Quotes", "PurchaseOrder", "SalesOrder")) && in_array($relation->name, array("Products", "Services"))) {
                        continue;
                    }
                    $moduleInfo["related_modules"][] = $quotingTool->parseModule($relation);
                }
            }
            $moduleInfo["picklist"] = static::getPicklistFields($module);
            if (in_array($module, $inventoryModules)) {
                $moduleInfo["final_details"] = QuotingTool::getTotalFields($module);
                $moduleInfo["section_groups"] = QuotingTool::getSectionGroups($module);
            }
            $data[] = $moduleInfo;
        }
        if ($default_charset != "UTF-8") {
            $data = $quotingTool->utf8_converter($data);
        }
        $json_str = json_encode($data, JSON_PRETTY_PRINT);
        return $json_str;
    }
    public function utf8_converter($array)
    {
        array_walk_recursive($array, function (&$item, $key) {
            if (!mb_detect_encoding($item, "utf-8", true)) {
                $item = utf8_encode($item);
            }
        });
        return $array;
    }
    public static function getCustomFunctions()
    {
        $data = array();
        $ready = false;
        $function_name = "";
        $function_params = array();
        $functions = array();
        $files = glob("modules/QuotingTool/resources/functions/*.php");
        foreach ($files as $file) {
            $filename = $file;
            $source = fread(fopen($filename, "r"), filesize($filename));
            $tokens = token_get_all($source);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_FUNCTION) {
                        $ready = true;
                    } else {
                        if ($ready) {
                            if ($token[0] == T_STRING && $function_name == "") {
                                $function_name = $token[1];
                            } else {
                                if ($token[0] == T_VARIABLE) {
                                    $function_params[] = $token[1];
                                }
                            }
                        }
                    }
                } else {
                    if ($ready && $token == "{") {
                        $ready = false;
                        if ($function_name != "") {
                            $functions[$function_name] = $function_params;
                        }
                        $function_name = "";
                        $function_params = array();
                    }
                }
            }
        }
        foreach ($functions as $funcName => $funcParams) {
            $strPrams = implode("|", $funcParams);
            $customFunction = trim($funcName . "|" . str_replace("\$", "", $strPrams), "|");
            $data[] = array("token" => "[CUSTOMFUNCTION|" . $customFunction . "|CUSTOMFUNCTION]", "name" => $funcName, "label" => vtranslate($funcName, self::MODULE_NAME));
        }
        return $data;
    }
    public static function getCustomFields()
    {
        $quotingTool = new QuotingTool();
        $customBlock = array("name" => "LBL_CUSTOM_BLOCK", "fields" => array(array("name" => "custom_proposal_link"), array("name" => "custom_user_signature")));
        $blocks = array();
        $blocks[] = $customBlock;
        $data = $quotingTool->fillBlockFields("", $blocks);
        return $data;
    }
    public static function getCompanyFields()
    {
        $quotingTool = new QuotingTool();
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $fields = array();
        foreach ($moduleModel->getFields() as $key => $val) {
            if ($key == "logo") {
                continue;
            }
            $fields[] = array("name" => "Vtiger_Company_" . $key);
        }
        $customBlock = array("name" => "LBL_COMPANY_BLOCK", "fields" => $fields);
        $blocks = array();
        $blocks[] = $customBlock;
        $data = $quotingTool->fillBlockFields("Vtiger", $blocks);
        return $data;
    }
    /**
     * @param string $rel_module
     * @return array
     */
    public static function getPicklistFields($rel_module)
    {
        global $default_charset;
        $data = array();
        $moduleModel = Vtiger_Module_Model::getInstance($rel_module);
        $fields = $moduleModel->getFields();
        foreach ($fields as $name => $field) {
            $fieldModel = Vtiger_Field_Model::getInstance($field->get("id"));
            $fieldDataType = $fieldModel->getFieldDataType();
            if ($fieldDataType != "picklist" && $fieldDataType != "multipicklist") {
                continue;
            }
            $picklist = $fieldModel->getPicklistValues();
            if ($default_charset != "UTF-8") {
                $newPicklist = array();
                foreach ($picklist as $key => $val) {
                    $piclistVal = iconv(mb_detect_encoding($key, mb_detect_order(), true), "UTF-8", $key);
                    $piclistLabel = iconv(mb_detect_encoding($val, mb_detect_order(), true), "UTF-8", $val);
                    $newPicklist[$piclistVal] = $piclistLabel;
                }
                $picklist = $newPicklist;
            }
            if (!empty($picklist)) {
                $data[] = array("id" => $fieldModel->get("id"), "name" => $fieldModel->get("name"), "label" => $fieldModel->get("label"), "values" => $picklist);
            }
        }
        return $data;
    }
    /**
     * @param string $moduleName
     * @param array $blocks
     * @return array
     */
    public function fillBlockFields($moduleName, $blocks)
    {
        $data = array();
        foreach ($blocks as $block) {
            $blockId = isset($block["id"]) ? $block["id"] : 0;
            $blockName = $block["name"];
            $blockLabel = isset($block["label"]) ? $block["label"] : vtranslate($blockName, self::MODULE_NAME);
            $fields = $block["fields"];
            foreach ($fields as $field) {
                $fieldId = isset($field["id"]) ? $field["id"] : 0;
                $uitype = isset($field["uitype"]) ? $field["uitype"] : 0;
                $datatype = isset($field["datatype"]) ? $field["datatype"] : "text";
                $fieldName = $field["name"];
                $fieldLabel = isset($field["label"]) ? $field["label"] : vtranslate($fieldName, self::MODULE_NAME);
                $token = isset($field["token"]) ? $field["token"] : $this->convertFieldToken($fieldName, $moduleName);
                $data[] = array("id" => $fieldId, "name" => $fieldName, "uitype" => $uitype, "datatype" => $datatype, "label" => $fieldLabel, "token" => $token, "block" => array("id" => $blockId, "name" => $blockName, "label" => $blockLabel));
            }
        }
        return $data;
    }
    public function getAllEntityModule()
    {
        $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
        return $supportedModulesList = array_keys($supportedModulesList);
    }
    /**
     * @param null|string $moduleName
     * @return array
     */
    public static function getTotalFields($moduleName = NULL)
    {
        $data = array();
        $quotingTool = new QuotingTool();
        $totalBlock = array("name" => "LBL_TOTAL_BLOCK", "fields" => array(array("name" => "hdnSubTotal", "datatype" => "currency", "label" => vtranslate("LBL_ITEMS_TOTAL", $moduleName)), array("name" => "discountTotal_final", "datatype" => "currency", "label" => vtranslate("LBL_DISCOUNT", $moduleName)), array("name" => "hdnDiscountPercent", "datatype" => "currency", "label" => "Discount Percent"), array("name" => "hdnS_H_Percent", "datatype" => "currency", "label" => "Taxes On Charges"), array("name" => "region_id", "datatype" => "currency", "label" => "Tax Region"), array("name" => "shipping_handling_charge", "datatype" => "currency", "label" => vtranslate("LBL_SHIPPING_AND_HANDLING_CHARGES", $moduleName)), array("name" => "preTaxTotal", "datatype" => "currency", "label" => vtranslate("LBL_PRE_TAX_TOTAL", $moduleName)), array("name" => "tax_totalamount", "datatype" => "currency", "label" => vtranslate("LBL_TAX", $moduleName)), array("name" => "shtax_totalamount", "datatype" => "currency", "label" => vtranslate("LBL_TAX_FOR_SHIPPING_AND_HANDLING", $moduleName)), array("name" => "adjustment", "datatype" => "currency", "label" => vtranslate("LBL_ADJUSTMENT", $moduleName)), array("name" => "grandTotal", "datatype" => "currency", "label" => vtranslate("LBL_GRAND_TOTAL", $moduleName))));
        if ($moduleName == "Invoice") {
            array_push($totalBlock["fields"], array("name" => "received", "datatype" => "currency", "label" => vtranslate("LBL_RECEIVED", $moduleName)), array("name" => "balance", "datatype" => "currency", "label" => vtranslate("LBL_BALANCE", $moduleName)));
        } else {
            if ($moduleName == "PurchaseOrder") {
                array_push($totalBlock["fields"], array("name" => "paid", "datatype" => "currency", "label" => vtranslate("LBL_PAID", $moduleName)), array("name" => "balance", "datatype" => "currency", "label" => vtranslate("LBL_BALANCE", $moduleName)));
            }
        }
        if ($moduleName) {
            $blocks = array();
            $blocks[] = $totalBlock;
            $data = $quotingTool->fillBlockFields($moduleName, $blocks);
        } else {
            $inventoryModules = getInventoryModules();
            foreach ($inventoryModules as $moduleName) {
                $blocks = array();
                $blocks[] = $totalBlock;
                $data = array_merge($data, $quotingTool->fillBlockFields($moduleName, $blocks));
            }
        }
        return $data;
    }
    public static function getSectionGroups($moduleName = NULL)
    {
        global $adb;
        $listSettingTable = array("quoter_quotes_settings", "quoter_invoice_settings", "quoter_salesorder_settings", "quoter_purchaseorder_settings");
        $sectionsSetting = array();
        $table = "quoter_" . strtolower($moduleName) . "_settings";
        $listTable = $listSettingTable;
        if (in_array($table, $listTable)) {
            $rs = $adb->pquery("SELECT section_group_setting, module FROM " . $table, array());
            if (0 < $adb->num_rows($rs)) {
                $sectionsSetting = unserialize(decode_html($adb->query_result($rs, 0, "section_group_setting")));
                $listSectionGroup = array();
                foreach ($sectionsSetting as $section) {
                    $listSectionGroup[] = $section["key"];
                }
            }
        }
        return $listSectionGroup;
    }
    protected static function updateModule($moduleName)
    {
        require_once "modules/QuotingTool/scripts/add_new_field_20170203_1.php";
        require_once "modules/QuotingTool/scripts/add_new_field_20170306_1.php";
        require_once "modules/QuotingTool/scripts/add_new_field_20180628_1.php";
        require_once "modules/QuotingTool/scripts/add_status_field_20170724_1.php";
        require_once "modules/QuotingTool/scripts/update_status_field_20170810_1.php";
        require_once "modules/QuotingTool/scripts/add_createnewrecords_field_20171101_1.php";
        require_once "modules/QuotingTool/scripts/add_createnewtable_helptext.php";
        require_once "modules/QuotingTool/scripts/add_initials_field.php";
        require_once "modules/QuotingTool/scripts/add_success_content_field_20180831.php";
        require_once "modules/QuotingTool/scripts/add_sharing_fields_20180905.php";
        require_once "modules/QuotingTool/scripts/add_settings_layout_column_20180912.php";
        require_once "modules/QuotingTool/scripts/add_custom_functions_column_20181005.php";
        require_once "modules/QuotingTool/scripts/add_file_name_column_20181022.php";
        require_once "modules/QuotingTool/scripts/add_email_signed_document_copy.php";
    }
    public static function updateButtonRelatedList($buttonName = "ADD", $moduleRelated = "SignedRecord")
    {
        global $adb;
        $related_tabid = getTabid($moduleRelated);
        $adb->pquery("Update vtiger_relatedlists set actions = ? WHERE related_tabid = ? AND actions = ? ", array("", $related_tabid, $buttonName));
    }
    public static function getTextColor($color)
    {
        $color = substr($color, 1, 6);
        $r = intval(substr($color, 0, 2), 16);
        $g = intval(substr($color, 2, 2), 16);
        $b = intval(substr($color, 4, 2), 16);
        $yiq = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return 128 <= $yiq ? "black" : "white";
    }
}

?>