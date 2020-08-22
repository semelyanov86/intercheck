<?php

class EMAILMaker_EMAILContent_Model extends EMAILMaker_EMAILContentUtils_Model {
    protected $isInstalled;
    private static $is_inventory_module = false;
    var $EMAILMaker = false;
    private static $Email_Images = array();
    private static $templateid;
    private static $recipientid;
    private static $recipientmodule;
    private static $module;
    private static $language;
    private static $focus;
    private static $db;
    private static $mod_strings;
    private static $def_charset;
    private static $site_url;
    private static $decimal_point;
    private static $thousands_separator;
    private static $decimals;
    private static $rowbreak;
    private static $ignored_picklist_values = array();
    private static $subject;
    private static $body;
    private static $preview;
    private static $content;
    private static $templatename;
    private static $type;
    private static $section_sep = "&#%ITS%%%@@@%%%ITS%#&";
    private static $rep;
    private static $inventory_table_array = array();
    private static $inventory_id_array = array();
    private static $org_colsOLD = array();
    private static $relBlockModules = array();

    function __construct() {
        $v = "vtiger_current_version";
        $vcv = vglobal($v);
        $i = "site_URL";
        $salt = vglobal($i);
        $d = "default_charset";
        $dc = vglobal($d);
        if (!defined('LOGO_PATH')) {
            define("LOGO_PATH", '/test/logo/');
        }
        self::$db = PearDatabase::getInstance();
        self::$def_charset = $dc;
        $mod_strings_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        self::$mod_strings = $mod_strings_array['languageStrings'];
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->EMAILMaker = new EmailMaker_EmailMaker_Model();
        if ($this->isInstalled) {
            self::$type = "professional";
        } else {
            self::$type = "professional";
        }
        $this->getIgnoredPicklistValues();
        self::$rowbreak = "<rowbreak />";
        self::$site_url = trim($salt, "/");
        self::$inventory_table_array = $this->getInventoryTableArray();
        self::$inventory_id_array = $this->getInventoryIdArray();
        self::$is_inventory_module[self::$module] = $this->isInventoryModule(self::$module);
        self::$org_colsOLD = $this->getOrgOldCols();
    }
    public function getContent($convert_recipient = true, $convert_source = true, $fixImg = false) {
        $v = "vtiger_current_version";
        $vcv = vglobal($v);
        $ir = "img_root_directory";
        $img_root = vglobal($ir);
        if (self::$module == 'Calendar') {
            self::$rep = Array();
        }
        if (self::$type == "invalid") {
            $this->setSubject('');
            $this->setBody('');
            $this->setPreview('');
            return;
        }
        self::$content = self::$subject . self::$section_sep;
        self::$content.= self::$body;
        self::$rep["$" . "siteurl$"] = self::$site_url;
        self::$rep["&nbsp;"] = " ";
        self::$rep["##PAGE##"] = "{PAGENO}";
        self::$rep["##PAGES##"] = "{nb}";
        self::$rep["##DD-MM-YYYY##"] = date("d-m-Y");
        self::$rep["##DD.MM.YYYY##"] = date("d.m.Y");
        self::$rep["##MM-DD-YYYY##"] = date("m-d-Y");
        self::$rep["##YYYY-MM-DD##"] = date("Y-m-d");
        self::$rep["src='"] = "src='" . $img_root;
        if ($convert_source) {
            self::$rep["$" . "s-" . strtolower(self::$module) . "-crmid$"] = self::$focus->id;
            self::$rep["$" . "s-" . strtolower(self::$module) . "_crmid$"] = self::$focus->id;
            if ($vcv == '5.2.1') {
                $displayValueCreated = getDisplayDate(self::$focus->column_fields['createdtime']);
                $displayValueModified = getDisplayDate(self::$focus->column_fields['modifiedtime']);
            } else {
                $createdtime = new DateTimeField(self::$focus->column_fields['createdtime']);
                $displayValueCreated = $createdtime->getDisplayDateTimeValue();
                $modifiedtime = new DateTimeField(self::$focus->column_fields['modifiedtime']);
                $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
            }
        }
        self::$rep['$s-' . strtolower(self::$module) . '-createdtime-datetime$'] = $displayValueCreated;
        self::$rep['$s-' . strtolower(self::$module) . '-modifiedtime-datetime$'] = $displayValueModified;
        self::$rep['$s-' . strtolower(self::$module) . '_createdtime_datetime$'] = $displayValueCreated;
        self::$rep['$s-' . strtolower(self::$module) . '_modifiedtime_datetime$'] = $displayValueModified;
        if ($convert_source) {
            $this->convertEntityImages();
        }
        $this->replaceContent();
        self::$content = html_entity_decode(self::$content, ENT_QUOTES, self::$def_charset);
        $recipient_id = self::$recipientid;
        $recipient_module = self::$recipientmodule;
        if ($convert_recipient && $recipient_id != "" && $recipient_module != "") {
            $recipient_module = self::$recipientmodule;
            $focus_recipient = CRMEntity::getInstance($recipient_module);
            $focus_recipient->id = $recipient_id;
            $this->retrieve_entity_infoCustom($focus_recipient, $focus_recipient->id, $recipient_module);
            self::$rep["$" . strtolower($recipient_module) . "-crmid$"] = $focus_recipient->id;
            self::$rep["$" . strtolower($recipient_module) . "_crmid$"] = $focus_recipient->id;
            $this->replaceContent();
            $this->replaceFieldsToContent($recipient_module, $focus_recipient, false, false, true);
            $this->replaceContent();
        }
        if ($convert_source) {
            $this->convertRelatedModule();
            $this->convertRelatedBlocks();
            $this->replaceFieldsToContent(self::$module, self::$focus);
            $this->convertInventoryModules();
            if ($this->focus->column_fields["assigned_user_id"] == "" && $this->focus->id != "") {
                $this->focus->column_fields["assigned_user_id"] = self::$db->query_result(self::$db->pquery("SELECT smownerid FROM vtiger_crmentity WHERE crmid=?", array(self::$focus->id)), 0, "smownerid");
            }
            self::$content = $this->convertListViewBlock(self::$content);
        }
        $this->handleRowbreak();
        $this->replaceUserCompanyFields($convert_source);
        $this->replaceLabels();
        if (strtoupper(self::$def_charset) != "UTF-8") {
            self::$content = iconv(self::$def_charset, "UTF-8//TRANSLIT", self::$content);
        }
        $this->replaceCustomFunctions();
        $EMAIL_content = array();
        if ($convert_recipient) {
            $Clear_Modules = array("Accounts", "Contacts", "Vendors", "Leads", "Users");
            foreach ($Clear_Modules AS $clear_module) {
                if ($clear_module != $recipient_module) {
                    $tabid1 = getTabId($clear_module);
                    $field_inf = "_fieldinfo_cache";
                    $temp = & VTCacheUtils::$$field_inf;
                    unset($temp[$tabid1]);
                    $focus1 = CRMEntity::getInstance($clear_module);
                    self::$rep["$" . strtolower($clear_module) . "-crmid$"] = "";
                    self::$rep["$" . "s-" . strtolower($clear_module) . "-crmid$"] = "";
                    self::$rep["$" . strtolower($clear_module) . "_crmid$"] = "";
                    self::$rep["$" . "s-" . strtolower($clear_module) . "_crmid$"] = "";
                    $this->replaceFieldsToContent($clear_module, $focus1, false, false, true);
                    $this->replaceFieldsToContent($clear_module, $focus1);
                    unset($focus1);
                }
                $this->replaceContent();
            }
            $this->replaceCustomFunctions("_after");
            list($EMAIL_content["pre_subject"], $EMAIL_content["pre_body"]) = explode(self::$section_sep, self::$content);
        }
        if ($convert_recipient || $fixImg) {
            $this->fixImg();
        }
        list($EMAIL_content["subject"], $EMAIL_content["body"]) = explode(self::$section_sep, self::$content);
        $this->setSubject($EMAIL_content["subject"]);
        $this->setBody($EMAIL_content["body"]);
        $this->setPreview($EMAIL_content);
    }
    private function convertRelatedModule() {
        $v = "vtiger_current_version";
        $vcv = vglobal($v);
        $field_inf = "_fieldinfo_cache";
        $fieldModRel = $this->GetFieldModuleRel();
        $module_tabid = getTabId(self::$module);
        $Query_Parr = array('3', '64', $module_tabid);
        $sql = "SELECT fieldid, fieldname, uitype, columnname FROM vtiger_field WHERE (displaytype != ? OR fieldid = ?) AND tabid";
        if (self::$module == "Calendar") {
            $Query_Parr[] = getTabId("Events");
            $sql.= " IN ( ?, ? ) GROUP BY fieldname";
        } else {
            $sql.= " = ?";
        }
        $result = self::$db->pquery($sql, $Query_Parr);
        $num_rows = self::$db->num_rows($result);
        if ($num_rows > 0) {
            while ($row = self::$db->fetch_array($result)) {
                $columnname = $row["columnname"];
                $fk_record = self::$focus->column_fields[$row["fieldname"]];
                $related_module = $this->getUITypeRelatedModule($row["uitype"], $fk_record);
                if ($related_module != "") {
                    $tabid = getTabId($related_module);
                    $temp = & VTCacheUtils::$$field_inf;
                    unset($temp[$tabid]);
                    $focus2 = CRMEntity::getInstance($related_module);
                    if ($fk_record != "" && $fk_record != "0") {
                        $result_delete = self::$db->pquery("SELECT deleted FROM vtiger_crmentity WHERE crmid=? AND deleted=?", array($fk_record, "0"));
                        if (self::$db->num_rows($result_delete) > 0) {
                            $focus2->retrieve_entity_info($fk_record, $related_module);
                            $focus2->id = $fk_record;
                        }
                    }
                    self::$rep["$" . "r-" . strtolower($related_module) . "-crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_crmid$"] = $focus2->id;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_crmid$"] = $focus2->id;
                    if ($vcv == '5.2.1') {
                        $displayValueCreated = getDisplayDate($focus2->column_fields['createdtime']);
                        $displayValueModified = getDisplayDate($focus2->column_fields['modifiedtime']);
                    } else {
                        $createdtime = new DateTimeField($focus2->column_fields['createdtime']);
                        $displayValueCreated = $createdtime->getDisplayDateTimeValue();
                        $modifiedtime = new DateTimeField($focus2->column_fields['modifiedtime']);
                        $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
                    }
                    self::$rep["$" . "r-" . strtolower($related_module) . "-createdtime-datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-createdtime-datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($related_module) . "-modifiedtime-datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($columnname) . "-modifiedtime-datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_createdtime_datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_createdtime_datetime$"] = $displayValueCreated;
                    self::$rep["$" . "r-" . strtolower($related_module) . "_modifiedtime_datetime$"] = $displayValueModified;
                    self::$rep["$" . "r-" . strtolower($columnname) . "_modifiedtime_datetime$"] = $displayValueModified;
                    if (isset($related_module)) {
                        $entityImg = "";
                        switch ($related_module) {
                            case "Contacts":
                                $entityImg = $this->getContactImage($focus2->id);
                            break;
                            case "Products":
                                $entityImg = $this->getProductImage($focus2->id);
                            break;
                        }
                        self::$rep['$r-' . strtolower($related_module) . '-imagename$'] = $entityImg;
                        self::$rep['$r-' . strtolower($columnname) . '-imagename$'] = $entityImg;
                    }
                    $this->replaceContent();
                    $this->replaceFieldsToContent($related_module, $focus2, true);
                    $this->replaceFieldsToContent($related_module, $focus2, $columnname);
                    $this->replaceInventoryDetailsBlock($related_module, $focus2, $columnname);
                    unset($focus2);
                }
                if ($row["uitype"] == "68") {
                    $fieldModRel[$row["fieldid"]][] = "Contacts";
                    $fieldModRel[$row["fieldid"]][] = "Accounts";
                }
                if (isset($fieldModRel[$row["fieldid"]])) {
                    foreach ($fieldModRel[$row["fieldid"]] as $idx => $relMod) {
                        if ($relMod == $related_module) {
                            continue;
                        }
                        $tmpTabId = getTabId($relMod);
                        $temp = & VTCacheUtils::$$field_inf;
                        unset($temp[$tmpTabId]);
                        if (file_exists("modules/" . $relMod . "/" . $relMod . ".php")) {
                            $tmpFocus = CRMEntity::getInstance($relMod);
                            self::$rep["$" . "r-" . strtolower($relMod) . "-crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_crmid$"] = $tmpFocus->id;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_crmid$"] = $tmpFocus->id;
                            if ($vcv == '5.2.1') {
                                $displayValueCreated = getDisplayDate($tmpFocus->column_fields['createdtime']);
                                $displayValueModified = getDisplayDate($tmpFocus->column_fields['modifiedtime']);
                            } else {
                                $createdtime = new DateTimeField($tmpFocus->column_fields['createdtime']);
                                $displayValueCreated = $createdtime->getDisplayDateTimeValue();
                                $modifiedtime = new DateTimeField($tmpFocus->column_fields['modifiedtime']);
                                $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
                            }
                            self::$rep["$" . "r-" . strtolower($relMod) . "-createdtime-datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-createdtime-datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($relMod) . "-modifiedtime-datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($columnname) . "-modifiedtime-datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_createdtime_datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_createdtime_datetime$"] = $displayValueCreated;
                            self::$rep["$" . "r-" . strtolower($relMod) . "_modifiedtime_datetime$"] = $displayValueModified;
                            self::$rep["$" . "r-" . strtolower($columnname) . "_modifiedtime_datetime$"] = $displayValueModified;
                            $this->replaceFieldsToContent($relMod, $tmpFocus, true);
                            $this->replaceFieldsToContent($relMod, $tmpFocus, $columnname);
                            $this->replaceInventoryDetailsBlock($relMod, $tmpFocus, $columnname);
                            unset($tmpFocus);
                        }
                    }
                }
            }
        }
    }
    private function convertProductBlock($block_type = '') {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $tableDOM = false;
        if (is_array($html->find("td"))) {
            foreach ($html->find("td") as $td) {
                if (trim($td->plaintext) == "#PRODUCTBLOC_" . $block_type . "START#") {
                    $td->parent->outertext = "#PRODUCTBLOC_" . $block_type . "START#";
                    $oParent = $td->parent;
                    while ($oParent->tag != "table") {
                        $oParent = $oParent->parent;
                    }
                    list($tag) = explode(">", $oParent->outertext, 2);
                    $header = $oParent->first_child();
                    if ($header->tag != "tr") {
                        $header = $header->children(0);
                    }
                    $header_style = '';
                    if (is_object($td->parent->prev_sibling()->children[0])) {
                        $header_style = $td->parent->prev_sibling()->children[0]->getAttribute("style");
                    }
                    $footer_tag = "<tr>";
                    if (isset($header_style)) {
                        $StyleHeader = explode(";", $header_style);
                        if (isset($StyleHeader)) {
                            foreach ($StyleHeader as $style_header_tag) {
                                if (strpos($style_header_tag, "border-top") == TRUE) {
                                    $footer_tag.= "<td colspan='" . $td->getAttribute("colspan") . "' style='" . $style_header_tag . "'>&nbsp;</td>";
                                }
                            }
                        }
                    } else {
                        $footer_tag.= "<td colspan='" . $td->getAttribute("colspan") . "' style='border-top:1px solid #000000;'>&nbsp;</td>";
                    }
                    $footer_tag.= "</tr>";
                    $var = $td->parent->next_sibling()->last_child()->plaintext;
                    $subtotal_tr = "";
                    if (strpos($var, "TOTAL") !== false) {
                        if (is_object($td)) {
                            $style_subtotal = $td->getAttribute("style");
                        }
                        $style_subtotal_tag = $style_subtotal_endtag = "";
                        if (isset($td->innertext)) {
                            list($style_subtotal_tag, $style_subtotal_endtag) = explode("#PRODUCTBLOC_" . $block_type . "START#", $td->innertext);
                        }
                        if (isset($style_subtotal)) {
                            $StyleSubtotal = explode(";", $style_subtotal);
                            if (isset($StyleSubtotal)) {
                                foreach ($StyleSubtotal as $style_tag) {
                                    if (strpos($style_tag, "border-top") == TRUE) {
                                        $tag.= " style='" . $style_tag . "'";
                                        break;
                                    }
                                }
                            }
                        } else {
                            $style_subtotal = "";
                        }
                        $subtotal_tr = "<tr>";
                        $subtotal_tr.= "<td colspan='" . ($td->getAttribute("colspan") - 1) . "' style='" . $style_subtotal . ";border-right:none'>" . $style_subtotal_tag . "%G_Subtotal%" . $style_subtotal_endtag . "</td>";
                        $subtotal_tr.= "<td align='right' nowrap='nowrap' style='" . $style_subtotal . "'>" . $style_subtotal_tag . "" . rtrim($var, "$") . "_SUBTOTAL$" . $style_subtotal_endtag . "</td>";
                        $subtotal_tr.= "</tr>";
                    }
                    $tag.= ">";
                    $tableDOM["tag"] = $tag;
                    $tableDOM["header"] = $header->outertext;
                    $tableDOM["footer"] = $footer_tag;
                    $tableDOM["subtotal"] = $subtotal_tr;
                }
                if (trim($td->plaintext) == "#PRODUCTBLOC_" . $block_type . "END#") {
                    $td->parent->outertext = "#PRODUCTBLOC_" . $block_type . "END#";
                }
            }
            self::$content = $html->save();
        }
        return $tableDOM;
    }
    private function convertInventoryModules() {
        $result = self::$db->pquery("select * from vtiger_inventoryproductrel where id=?", array(self::$focus->id));
        $num_rows = self::$db->num_rows($result);
        if ($num_rows > 0) {
            $Products = $this->replaceInventoryDetailsBlock(self::$module, self::$focus);
            $var_array = array();
            $Blocks = array("", "PRODUCTS_", "SERVICES_");
            foreach ($Blocks AS $block_type) {
                if (strpos(self::$content, "#PRODUCTBLOC_" . $block_type . "START#") !== false && strpos(self::$content, "#PRODUCTBLOC_" . $block_type . "END#") !== false) {
                    $tableTag = $this->convertProductBlock($block_type);
                    $ExplodedEMAIL = array();
                    $Exploded = explode("#PRODUCTBLOC_" . $block_type . "START#", self::$content);
                    $ExplodedEMAIL[] = $Exploded[0];
                    for ($iterator = 1;$iterator < count($Exploded);$iterator++) {
                        $SubExploded = explode("#PRODUCTBLOC_" . $block_type . "END#", $Exploded[$iterator]);
                        foreach ($SubExploded as $part) {
                            $ExplodedEMAIL[] = $part;
                        }
                        $highestpartid = $iterator * 2 - 1;
                        $ProductParts[$highestpartid] = $ExplodedEMAIL[$highestpartid];
                        $ExplodedEMAIL[$highestpartid] = '';
                    }
                    if ($Products["P"]) {
                        foreach ($Products["P"] AS $Product_Details) {
                            if (($block_type == "PRODUCTS_" && !empty($Product_Details["SERVICES_RECORD_ID"])) || ($block_type == "SERVICES_" && !empty($Product_Details["PRODUCTS_RECORD_ID"]))) {
                                continue;
                            }
                            foreach ($ProductParts as $productpartid => $productparttext) {
                                foreach ($Product_Details AS $coll => $value) {
                                    $productparttext = str_replace("$" . strtoupper($coll) . "$", $value, $productparttext);
                                }
                                $ExplodedEMAIL[$productpartid].= $productparttext;
                            }
                        }
                    }
                    self::$content = implode('', $ExplodedEMAIL);
                }
            }
        }
    }
    private function handleRowbreak() {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $toSkip = 0;
        if (is_array($html->find("rowbreak"))) {
            foreach ($html->find("rowbreak") as $pb) {
                if ($pb->outertext == self::$rowbreak) {
                    $tmpPb = $pb;
                    while ($tmpPb != null && $tmpPb->tag != "td") {
                        $tmpPb = $tmpPb->parent();
                    }
                    if ($tmpPb->tag == "td") {
                        if ($toSkip > 0) {
                            $toSkip--;
                            continue;
                        }
                        $prev_sibling = $tmpPb->prev_sibling();
                        $prev_sibling_styles = array();
                        while ($prev_sibling != null) {
                            $prev_sibling_styles[] = $this->getDOMElementAtts($prev_sibling);
                            $prev_sibling = $prev_sibling->prev_sibling();
                        }
                        $next_sibling = $tmpPb->next_sibling();
                        $next_sibling_styles = array();
                        while ($next_sibling != null) {
                            $next_sibling_styles[] = $this->getDOMElementAtts($next_sibling);
                            $next_sibling = $next_sibling->next_sibling();
                        }
                        $partsArr = explode(self::$rowbreak, $tmpPb->innertext);
                        for ($i = 0;$i < (count($partsArr) - 1);$i++) {
                            $tmpPb->innertext = $partsArr[$i];
                            $addition = '<tr>';
                            for ($j = 0;$j < count($prev_sibling_styles);$j++) {
                                $addition.= '<td ' . $prev_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition.= '<td style="' . $tmpPb->getAttribute("style") . '">' . $partsArr[$i + 1] . '</td>';
                            for ($j = 0;$j < count($next_sibling_styles);$j++) {
                                $addition.= '<td ' . $next_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition.= '</tr>';
                            $tmpPb->parent()->outertext = $tmpPb->parent()->outertext . $addition;
                        }
                        $toSkip = count($partsArr) - 2;
                    }
                }
            }
            self::$content = $html->save();
        }
    }
    private function getFieldValue($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency = false) {
        return $this->getFieldValueUtils($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency, self::$ignored_picklist_values, self::$def_charset, self::$decimals, self::$decimal_point, self::$thousands_separator, self::$language);
    }
    private function replaceFieldsToContent($emodule, $efocus, $is_related = false, $inventory_currency = false, $is_recipient = false, $related = "r-") {
        $current_user = Users_Record_Model::getCurrentUserModel();
        if ($inventory_currency !== false) {
            $inventory_content = array();
        }
        $convEntity = $emodule;
        if ($is_related === false) {
            if ($is_recipient) {
                $related = "";
            } else {
                $related = "s-";
            }
        } else {
            if ($is_related !== true) {
                $convEntity = $is_related . "-" . $convEntity;
            }
        }
        if (!empty($efocus->id)) {
            $VtigerDetailViewModel = Vtiger_DetailView_Model::getInstance($emodule, $efocus->id);
            $recordModel = $VtigerDetailViewModel->getRecord();
            $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, '');
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($emodule);
            $recordStrucure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, '');
        }
        $stucturedValues = $recordStrucure->getStructure();
        foreach ($stucturedValues AS $BLOCK_LABEL => $BLOCK_FIELDS) {
            foreach ($BLOCK_FIELDS AS $FIELD_NAME => $FIELD_MODEL) {
                $fieldname = $FIELD_MODEL->get('name');
                $fieldlabel = $FIELD_MODEL->get('label');
                $fieldvalue = $FIELD_DISPLAY_VALUE = "";
                if (!empty($efocus->id)) {
                    $fieldvalue = $FIELD_MODEL->get('fieldvalue');
                    $fieldDataType = $FIELD_MODEL->getFieldDataType();
                    if ($fieldDataType == 'multipicklist') {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getDisplayValue($fieldvalue);
                    } else if ($fieldDataType == 'reference' || $fieldDataType == 'owner') {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getEditViewDisplayValue($fieldvalue);
                    } else if ($fieldDataType == 'double' || $fieldDataType == 'percentage') {
                        $FIELD_DISPLAY_VALUE = $this->formatNumberToEMAIL($fieldvalue);
                    } else if ($fieldDataType == 'currency') {
                        if (is_numeric($fieldvalue)) {
                            if ($inventory_currency === false) {
                                $user_currency_data = getCurrencySymbolandCRate($current_user->currency_id);
                                $crate = $user_currency_data["rate"];
                            } else {
                                $crate = $inventory_currency["conversion_rate"];
                            }
                            $fieldvalue = $fieldvalue * $crate;
                        }
                        $FIELD_DISPLAY_VALUE = $this->formatNumberToEMAIL($fieldvalue);
                    } else {
                        $FIELD_DISPLAY_VALUE = $FIELD_MODEL->getDisplayValue($fieldvalue);
                    }
                }
                self::$rep["%" . $related . strtolower($convEntity . "-" . $fieldname) . "%"] = vtranslate($fieldlabel, $emodule);
                self::$rep["%M_" . $fieldlabel . "%"] = vtranslate($fieldlabel, $emodule);
                if ($inventory_currency !== false) {
                    $inventory_content[strtoupper($emodule . "-" . $fieldname) ] = $FIELD_DISPLAY_VALUE;
                    $inventory_content[strtoupper($emodule . "_" . $fieldname) ] = $FIELD_DISPLAY_VALUE;
                } else {
                    self::$rep["$" . $related . strtolower($convEntity . "-" . $fieldname) . "$"] = $FIELD_DISPLAY_VALUE;
                }
            }
        }
        if ($inventory_currency !== false) {
            return $inventory_content;
        } else {
            $this->replaceContent();
            return true;
        }
    }
    private function replaceUserCompanyFields($convert_source) {
        $r = "root_directory";
        $root_dir = vglobal($r);
        $current_user = Users_Record_Model::getCurrentUserModel();
        if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany')) {
            $CompanyDetailsRecord_Model = ITS4YouMultiCompany_Record_Model::getCompanyInstance(self::$focus->column_fields["assigned_user_id"]);
            $CompanyDetails_Model = $CompanyDetailsRecord_Model->getModule();
            $CompanyDetails_Data = $CompanyDetailsRecord_Model->getData();
            $ismulticompany = true;
        } else {
            $CompanyDetails_Model = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $CompanyDetails_Data = $CompanyDetails_Model->getData();
            $ismulticompany = false;
        }
        $CompanyDetails_Fields = $CompanyDetails_Model->getFields();
        foreach ($CompanyDetails_Fields AS $field_name => $field_data) {
            $value = "";
            if ($field_name == "organizationname" || $field_name == "companyname") {
                $coll = "name";
            } elseif ($field_name == "street") {
                $coll = "address";
            } elseif ($field_name == "code") {
                $coll = "zip";
            } elseif ($field_name == "logoname") {
                continue;
            } else {
                $coll = $field_name;
            }
            if ($coll == "logo" && !$ismulticompany && !empty($CompanyDetails_Data["logoname"])) {
                $value = '<img src="' . self::$site_url . LOGO_PATH . $CompanyDetails_Data["logoname"] . '">';
            } elseif (($coll == "logo" || $coll == "stamp") && $ismulticompany && !empty($CompanyDetails_Data[$coll])) {
                $value = $this->getAttachmentImage($CompanyDetails_Data[$coll], self::$site_url);
            } elseif (isset($CompanyDetails_Data[$field_name])) {
                $value = $CompanyDetails_Data[$field_name];
            }
            self::$rep["$" . "company-" . $coll . "$"] = $value;
            if ($ismulticompany) {
                $label = vtranslate($field_data->get("label"), "ITS4YouMultiCompany");
            } else {
                $label = vtranslate($field_name, "Settings:Vtiger");
            }
            self::$rep["%" . "COMPANY_" . strtoupper($coll) . "%"] = $label;
        }
        $tandc = self::$db->query_result(self::$db->pquery("SELECT tandc FROM vtiger_inventory_tandc WHERE type = ?", array("Inventory")), 0, "tandc");
        self::$rep["$" . "TERMS_AND_CONDITIONS$"] = nl2br($tandc);
        if ($convert_source) {
            $user_row = array();
            $assigned_user_id = "";
            if (self::$focus->column_fields["assigned_user_id"] != "") {
                $user_res = self::$db->pquery("SELECT * FROM vtiger_users WHERE id = ?", array(self::$focus->column_fields["assigned_user_id"]));
                $num_user_rows = self::$db->num_rows($user_res);
                if ($num_user_rows > 0) {
                    $user_row = self::$db->fetchByAssoc($user_res);
                    $assigned_user_id = self::$focus->column_fields["assigned_user_id"];
                }
            }
            self::$rep["$" . "s-user_crmid$"] = $assigned_user_id;
            $this->replaceContent();
            $this->replaceUserData($assigned_user_id, $user_row, "s");
            $focus_user = CRMEntity::getInstance("Users");
            if (!empty($assigned_user_id)) {
                $focus_user->id = $assigned_user_id;
                $this->retrieve_entity_infoCustom($focus_user, $focus_user->id, "Users");
            }
            $this->replaceFieldsToContent("Users", $focus_user, false);
        }
        $luserid = $this->get("luserid");
        if (!$luserid) {
            $luserid = $current_user->id;
        }
        self::$rep["$" . "l-user_crmid$"] = $luserid;
        if ($luserid == $current_user->id) {
            $this->replaceUserData($current_user->id, $current_user->column_fields, "l");
        }
        $curr_user_focus = CRMEntity::getInstance("Users");
        $curr_user_focus->id = $luserid;
        $this->retrieve_entity_infoCustom($curr_user_focus, $curr_user_focus->id, "Users");
        if ($luserid != $current_user->id) {
            $this->replaceUserData($current_user->id, $curr_user_focus->column_fields, "l");
        }
        $this->replaceFieldsToContent("Users", $curr_user_focus, true, false, false, "l-");
        self::$rep["$" . "l-users_crmid$"] = $curr_user_focus->id;
        $muserid = $this->get("muserid");
        if ($muserid) {
            $modifiedby_user_res_sql = "WHERE vtiger_users.id = ?";
            $modifiedby_user_res_data = array($muserid);
        } else {
            $modifiedby_user_res_sql = "INNER JOIN vtiger_crmentity ON vtiger_crmentity.modifiedby = vtiger_users.id WHERE vtiger_crmentity.crmid = ?";
            $modifiedby_user_res_data = array(self::$focus->id);
        }
        $modifiedby_user_res = self::$db->pquery("SELECT vtiger_users.* FROM vtiger_users " . $modifiedby_user_res_sql, $modifiedby_user_res_data);
        $modifiedby_user_row = self::$db->fetchByAssoc($modifiedby_user_res);
        $this->replaceUserData($modifiedby_user_row["id"], $modifiedby_user_row, "m");
        $modifiedby_user_focus = CRMEntity::getInstance("Users");
        $modifiedby_user_focus->id = $modifiedby_user_row["id"];
        $this->retrieve_entity_infoCustom($modifiedby_user_focus, $modifiedby_user_focus->id, "Users");
        $this->replaceFieldsToContent("Users", $modifiedby_user_focus, true, false, false, 'm-');
        self::$rep["$" . "m-users_crmid$"] = $modifiedby_user_focus->id;
        $smcreatorid_user_res = self::$db->pquery("SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.smcreatorid = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?", array(self::$focus->id));
        $smcreatorid_user_row = self::$db->fetchByAssoc($smcreatorid_user_res);
        $this->replaceUserData($smcreatorid_user_row["id"], $smcreatorid_user_row, "c");
        $smcreatorid_user_focus = CRMEntity::getInstance("Users");
        $smcreatorid_user_focus->id = $smcreatorid_user_row["id"];
        $this->retrieve_entity_infoCustom($smcreatorid_user_focus, $smcreatorid_user_focus->id, "Users");
        $this->replaceFieldsToContent("Users", $smcreatorid_user_focus, true, false, falce, 'c-');
        self::$rep["$" . "c-users_crmid$"] = $smcreatorid_user_focus->id;
        $this->replaceContent();
    }
    private function replaceLabels() {
        $app_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language);
        $mod_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        $app_lang = $app_lang_array["languageStrings"];
        $mod_lang = $mod_lang_array["languageStrings"];
        list($custom_lang, $languages) = $this->EMAILMaker->GetCustomLabels();
        $currLangId = "";
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == self::$language) {
                $currLangId = $langId;
                break;
            }
        }
        self::$rep["%G_Qty%"] = $app_lang["Quantity"];
        self::$rep["%G_Subtotal%"] = $app_lang["Sub Total"];
        self::$rep["%M_LBL_VENDOR_NAME_TITLE%"] = $app_lang["Vendor Name"];
        $this->replaceContent();
        if (strpos(self::$content, "%G_") !== false) {
            foreach ($app_lang as $key => $value) {
                self::$rep["%G_" . $key . "%"] = $value;
            }
            $this->replaceContent();
        }
        if (strpos(self::$content, "%M_") !== false) {
            foreach ($mod_lang as $key => $value) {
                self::$rep["%M_" . $key . "%"] = $value;
            }
            $this->replaceContent();
            foreach ($app_lang as $key => $value) {
                self::$rep["%M_" . $key . "%"] = $value;
            }
            if (self::$module == "SalesOrder") self::$rep["%G_SO Number%"] = $mod_lang["SalesOrder No"];
            if (self::$module == "Invoice") self::$rep["%G_Invoice No%"] = $mod_lang["Invoice No"];
            self::$rep["%M_Grand Total%"] = vtranslate('Grand Total', self::$module);
            $this->replaceContent();
        }
        if (strpos(self::$content, "%C_") !== false) {
            foreach ($custom_lang as $key => $value) {
                self::$rep["%" . $value->GetKey() . "%"] = $value->GetLangValue($currLangId);
            }
            $this->replaceContent();
        }
        if (count(self::$relBlockModules) > 0) {
            $services_lang = return_specified_module_language(self::$language, "Services");
            $contacts_lang = return_specified_module_language(self::$language, "Contacts");
            foreach (self::$relBlockModules as $relBlockModule) {
                if ($relBlockModule != "") {
                    $relMod_lang = return_specified_module_language(self::$language, $relBlockModule);
                    $r_rbm_upper = "%r-" . strtolower($relBlockModule);
                    self::$rep[$r_rbm_upper . "_Service Name%"] = $services_lang["Service Name"];
                    self::$rep[$r_rbm_upper . "_Secondary Email%"] = $contacts_lang["Secondary Email"];
                    $LD = $this->getRelBlockLabels();
                    foreach ($LD AS $lkey => $llabel) {
                        self::$rep[$r_rbm_upper . "_" . $lkey . "%"] = $app_lang[$llabel];
                    }
                    $rl_res = self::$db->pquery("SELECT vtiger_field.fieldlabel FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.name = ?", array($relBlockModule));
                    while ($rl_row = self::$db->fetchByAssoc($rl_res)) {
                        $key = $rl_row["fieldlabel"];
                        if ($relMod_lang[$key]) {
                            $value = $relMod_lang[$key];
                        } elseif ($app_lang[$key]) {
                            $value = $app_lang[$key];
                        } else {
                            $value = $key;
                        }
                        self::$rep[$r_rbm_upper . "_" . htmlentities($key, ENT_QUOTES, self::$def_charset) . "%"] = $value;
                        self::$rep["%R_" . strtoupper($relBlockModule) . "_" . htmlentities($key, ENT_QUOTES, self::$def_charset) . "%"] = $value;
                    }
                    if ($relBlockModule == "Products") {
                        self::$rep[$r_rbm_upper . "_LBL_LIST_PRICE%"] = $app_lang["LBL_LIST_PRICE"];
                    }
                    $this->replaceContent();
                }
            }
        }
    }
    private function replaceContent() {
        if (!empty(self::$rep)) {
            self::$content = str_replace(array_keys(self::$rep), self::$rep, self::$content);
            self::$rep = array();
        }
    }
    private function getTemplateData() {
        $result = self::$db->pquery("SELECT *  FROM vtiger_emakertemplates WHERE templateid=?", array(self::$templateid));
        $data = self::$db->fetch_array($result);
        $this->setSubject($data["subject"]);
        $email_body = $data["body"];
        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
            $email_body = $ITS4YouStylesModuleModel->addStyles($email_body, self::$templateid, "EMAILMaker");
        }
        $this->setBody($email_body);
        self::$templatename = $data["templatename"];
    }
    private function getIgnoredPicklistValues() {
        $result = self::$db->pquery("SELECT value FROM vtiger_emakertemplates_ignorepicklistvalues", array());
        while ($row = self::$db->fetchByAssoc($result)) {
            self::$ignored_picklist_values[] = $row["value"];
        }
    }
    private function getInventoryProducts($module, $focus) {
        if (!empty($focus->id)) {
            $total_vatsum = $totalwithoutwat = $totalAfterDiscount_subtotal = $total_subtotal = $totalsum_subtotal = 0;
            list($images, $bacImgs) = $this->getInventoryImages($focus->id);
            $recordModel = Inventory_Record_Model::getInstanceById($focus->id);
            $relatedProducts = $recordModel->getProducts();
            $finalDetails = $relatedProducts[1]['final_details'];
            $taxtype = $finalDetails['taxtype'];
            $chargesAndItsTaxes = $finalDetails['chargesAndItsTaxes'];
            $currencyFieldsList = array('NETTOTAL' => 'hdnSubTotal', 'TAXTOTAL' => 'tax_totalamount', 'SHTAXTOTAL' => 'shtax_totalamount', 'TOTALAFTERDISCOUNT' => 'preTaxTotal', 'FINALDISCOUNT' => 'discountTotal_final', 'SHTAXAMOUNT' => 'shipping_handling_charge', 'DEDUCTEDTAXESTOTAL' => 'deductTaxesTotalAmount',);
            foreach ($currencyFieldsList as $variableName => $fieldName) {
                $Details["TOTAL"][$variableName] = $this->formatNumberToEMAIL($finalDetails[$fieldName]);
            }
            $totalwithwat = $finalDetails["preTaxTotal"] + $finalDetails["tax_totalamount"];
            $Details["TOTAL"]["TOTALWITHVAT"] = $this->formatNumberToEMAIL($totalwithwat);
            foreach ($relatedProducts AS $i => $PData) {
                $Details["P"][$i] = array();
                $sequence = $i;
                $producttitle = $productname = $PData["productName" . $sequence];
                $entitytype = $PData["entityType" . $sequence];
                $productid = $psid = $PData["hdnProductId" . $sequence];
                $focus_p = CRMEntity::getInstance("Products");
                if ($entitytype == "Products" && $psid != "") {
                    $focus_p->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_p, $psid, "Products");
                }
                $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
                $Array_P = $this->replaceFieldsToContent("Products", $focus_p, false, $currencytype);
                $Details["P"][$i] = array_merge($Array_P, $Details["P"][$i]);
                unset($focus_p);
                $focus_s = CRMEntity::getInstance("Services");
                if ($entitytype == "Services" && $psid != "") {
                    $focus_s->id = $psid;
                    $this->retrieve_entity_infoCustom($focus_s, $psid, "Services");
                }
                $Array_S = $this->replaceFieldsToContent("Services", $focus_s, false, $currencytype);
                $Details["P"][$i] = array_merge($Array_S, $Details["P"][$i]);
                unset($focus_s);
                $Details["P"][$i]["PRODUCTS_CRMID"] = $Details["P"][$i]["SERVICES_CRMID"] = $qty_per_unit = $usageunit = "";
                if ($entitytype == "Products") {
                    $Details["P"][$i]["PRODUCTS_CRMID"] = $psid;
                    $qty_per_unit = $Details["P"][$i]["PRODUCTS_QTY_PER_UNIT"];
                    $usageunit = $Details["P"][$i]["PRODUCTS_USAGEUNIT"];
                } elseif ($entitytype == "Services") {
                    $Details["P"][$i]["SERVICES_CRMID"] = $psid;
                    $qty_per_unit = $Details["P"][$i]["SERVICES_QTY_PER_UNIT"];
                    $usageunit = $Details["P"][$i]["SERVICES_SERVICE_USAGEUNIT"];
                }
                $psdescription = $Details["P"][$i][strtoupper($entitytype) . "_DESCRIPTION"];
                $Details["P"][$i]["PS_CRMID"] = $psid;
                $Details["P"][$i]["PS_NO"] = $PData["hdnProductcode" . $sequence];
                if (count($PData["subprod_qty_list" . $sequence]) > 0) {
                    foreach ($PData["subprod_qty_list" . $sequence] AS $sid => $SData) {
                        $sname = $SData["name"];
                        if ($SData["qty"] > 0) {
                            $sname.= " (" . $SData["qty"] . ")";
                        }
                        $productname.= "<br/><span style='color:#C0C0C0;font-style:italic;'>" . $sname . "</span>";
                    }
                }
                $comment = $PData["comment" . $sequence];
                if ($comment != "") {
                    if (strpos($comment, '&lt;br /&gt;') === false && strpos($comment, '&lt;br/&gt;') === false && strpos($comment, '&lt;br&gt;') === false) {
                        $comment = str_replace("
", "<br>", nl2br($comment));
                    }
                    $comment = html_entity_decode($comment, ENT_QUOTES, self::$def_charset);
                    $productname.= "<br /><small>" . $comment . "</small>";
                }
                $Details["P"][$i]["PRODUCTNAME"] = $productname;
                $Details["P"][$i]["PRODUCTTITLE"] = $producttitle;
                $inventory_prodrel_desc = $psdescription;
                if (strpos($psdescription, '&lt;br /&gt;') === false && strpos($psdescription, '&lt;br/&gt;') === false && strpos($psdescription, '&lt;br&gt;') === false) {
                    $psdescription = str_replace("
", "<br>", nl2br($psdescription));
                }
                $Details["P"][$i]["PRODUCTDESCRIPTION"] = html_entity_decode($psdescription, ENT_QUOTES, self::$def_charset);
                $Details["P"][$i]["PRODUCTEDITDESCRIPTION"] = $comment;
                if (strpos($inventory_prodrel_desc, '&lt;br /&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br/&gt;') === false && strpos($inventory_prodrel_desc, '&lt;br&gt;') === false) {
                    $inventory_prodrel_desc = str_replace("
", "<br>", nl2br($inventory_prodrel_desc));
                }
                $Details["P"][$i]["CRMNOWPRODUCTDESCRIPTION"] = html_entity_decode($inventory_prodrel_desc, ENT_QUOTES, self::$def_charset);
                $Details["P"][$i]["PRODUCTLISTPRICE"] = $this->formatNumberToEMAIL($PData["listPrice" . $sequence]);
                $Details["P"][$i]["PRODUCTTOTAL"] = $this->formatNumberToEMAIL($PData["productTotal" . $sequence]);
                $Details["P"][$i]["PRODUCTQUANTITY"] = $this->formatNumberToEMAIL($PData["qty" . $sequence]);
                $Details["P"][$i]["PRODUCTQINSTOCK"] = $this->formatNumberToEMAIL($PData["qtyInStock" . $sequence]);
                $Details["P"][$i]["PRODUCTPRICE"] = $this->formatNumberToEMAIL($PData["unitPrice" . $sequence]);
                $Details["P"][$i]["PRODUCTPOSITION"] = $sequence;
                $Details["P"][$i]["PRODUCTQTYPERUNIT"] = $this->formatNumberToEMAIL($qty_per_unit);
                $value = $usageunit;
                if (!in_array(trim($value), self::$ignored_picklist_values)) {
                    $value = $this->getTranslatedStringCustom($value, "Products/Services", self::$language);
                } else {
                    $value = "";
                }
                $Details["P"][$i]["PRODUCTUSAGEUNIT"] = $value;
                $Details["P"][$i]["PRODUCTDISCOUNT"] = $PData["discountTotal" . $sequence];
                $Details["P"][$i]["PRODUCTDISCOUNTPERCENT"] = $PData["discount_percent" . $sequence];
                $totalAfterDiscount = $PData["totalAfterDiscount" . $sequence];
                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNTSUM"] = $totalAfterDiscount;
                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT"] = $this->formatNumberToEMAIL($PData["totalAfterDiscount" . $sequence]);
                $Details["P"][$i]["PRODUCTTOTALSUM"] = $this->formatNumberToEMAIL($PData["netPrice" . $sequence]);
                $totalAfterDiscount_subtotal+= $totalAfterDiscount;
                $total_subtotal+= $PData["productTotal" . $sequence];
                $totalsum_subtotal+= $PData["netPrice" . $sequence];
                $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL"] = $this->formatNumberToEMAIL($totalAfterDiscount_subtotal);
                $Details["P"][$i]["PRODUCTTOTAL_SUBTOTAL"] = $this->formatNumberToEMAIL($total_subtotal);
                $Details["P"][$i]["PRODUCTTOTALSUM_SUBTOTAL"] = $this->formatNumberToEMAIL($totalsum_subtotal);
                $mpdfSubtotalAble[$i]["$" . "TOTALAFTERDISCOUNT_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL"];
                $mpdfSubtotalAble[$i]["$" . "TOTAL_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTTOTAL_SUBTOTAL"];
                $mpdfSubtotalAble[$i]["$" . "TOTALSUM_SUBTOTAL$"] = $Details["P"][$i]["PRODUCTTOTALSUM_SUBTOTAL"];
                $Details["P"][$i]["PRODUCTSEQUENCE"] = $sequence;
                $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "";
                if (isset($images[$productid . "_" . $sequence])) {
                    $width = $height = "";
                    if ($images[$productid . "_" . $sequence]["width"] > 0) $width = " width='" . $images[$productid . "_" . $sequence]["width"] . "' ";
                    if ($images[$productid . "_" . $sequence]["height"] > 0) $height = " height='" . $images[$productid . "_" . $sequence]["height"] . "' ";
                    $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "<img src='" . self::$site_url . "/" . $images[$productid . "_" . $sequence]["src"] . "' " . $width . $height . "/>";
                } elseif (isset($bacImgs[$productid . "_" . $sequence])) {
                    $Details["P"][$i]["PRODUCTS_IMAGENAME"] = "<img src='" . self::$site_url . "/" . $bacImgs[$productid . "_" . $sequence]["src"] . "' width='83' />";
                }
                $taxtotal = $tax_avg_value = "0.00";
                if ($taxtype == "individual") {
                    $tax_details = getTaxDetailsForProduct($productid, "all");
                    $Tax_Values = array();
                    for ($tax_count = 0;$tax_count < count($tax_details);$tax_count++) {
                        $tax_name = $tax_details[$tax_count]["taxname"];
                        $tax_label = $tax_details[$tax_count]["taxlabel"];
                        $tax_value = getInventoryProductTaxValue($focus->id, $productid, $tax_name);
                        $individual_taxamount = $totalAfterDiscount * $tax_value / 100;
                        $taxtotal = $taxtotal + $individual_taxamount;
                        if ($tax_name != "") {
                            $Vat_Block[$tax_name . "-" . $tax_value]["label"] = $tax_label;
                            $Vat_Block[$tax_name . "-" . $tax_value]["netto"]+= $totalAfterDiscount;
                            $vatsum = round($individual_taxamount, self::$decimals);
                            $total_vatsum+= $vatsum;
                            $Vat_Block[$tax_name . "-" . $tax_value]["vat"]+= $vatsum;
                            $Vat_Block[$tax_name . "-" . $tax_value]["value"] = $tax_value;
                            array_push($Tax_Values, $tax_value);
                            array_push($Total_Tax_Values, $tax_value);
                        }
                    }
                    if (count($Tax_Values) > 0) {
                        $tax_avg_value = array_sum($Tax_Values);
                    }
                }
                $Details["P"][$i]["PRODUCTVATPERCENT"] = $this->formatNumberToEMAIL($tax_avg_value);
                $Details["P"][$i]["PRODUCTVATSUM"] = $this->formatNumberToEMAIL($taxtotal);
                $result1 = self::$db->pquery("SELECT * FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?", array(self::$focus->id, $sequence));
                $row1 = self::$db->fetchByAssoc($result1, 0);
                $tabid = getTabid($module);
                $result2 = self::$db->pquery("SELECT fieldname, fieldlabel, columnname, uitype, typeofdata FROM vtiger_field WHERE tablename = ? AND tabid = ?", array("vtiger_inventoryproductrel", $tabid));
                while ($row2 = self::$db->fetchByAssoc($result2)) {
                    if (!isset($Details["P"][$i]["PRODUCT_" . strtoupper($row2["fieldname"]) ])) {
                        $UITypes = array();
                        $value = $row1[$row2["columnname"]];
                        if ($value != "") {
                            $uitype_name = $this->getUITypeName($row2['uitype'], $row2["typeofdata"]);
                            if ($uitype_name != "") $UITypes[$uitype_name][] = $row2["fieldname"];
                            $value = $this->getFieldValue($focus, $module, $row2["fieldname"], $value, $UITypes);
                        }
                        $Details["P"][$i]["PRODUCT_" . strtoupper($row2["fieldname"]) ] = $value;
                    }
                }
            }
        }
        $Details["TOTAL"]["TOTALWITHOUTVAT"] = $this->formatNumberToEMAIL($totalAfterDiscount_subtotal);
        if ($taxtype == "individual") {
            $Details["TOTAL"]["TAXTOTAL"] = $this->formatNumberToEMAIL($total_vatsum);
        }
        $finalDiscountPercent = "";
        $total_vat_percent = 0;
        if (count($finalDetails["taxes"]) > 0) {
            foreach ($finalDetails["taxes"] AS $TAX) {
                $tax_name = $TAX["taxname"];
                $Vat_Block[$tax_name]["label"] = $TAX["taxlabel"];
                $Vat_Block[$tax_name]["netto"] = $finalDetails["totalAfterDiscount"];
                if (isset($Vat_Block[$tax_name]["vat"])) {
                    $Vat_Block[$tax_name]["vat"]+= $TAX["amount"];
                } else {
                    $Vat_Block[$tax_name]["vat"] = $TAX["amount"];
                }
                $Vat_Block[$tax_name]["value"] = $TAX["percentage"];
                $total_vat_percent+= $TAX["percentage"];
            }
        }
        $Details["TOTAL"]["TAXTOTALPERCENT"] = $this->formatNumberToEMAIL($total_vat_percent);
        $hdnDiscountPercent = (float)$focus->column_fields['hdnDiscountPercent'];
        $hdnDiscountAmount = (float)$focus->column_fields['hdnDiscountAmount'];
        if (!empty($hdnDiscountPercent)) {
            $finalDiscountPercent = $hdnDiscountPercent;
        }
        $Details["TOTAL"]["FINALDISCOUNTPERCENT"] = $this->formatNumberToEMAIL($finalDiscountPercent);
        $Details["TOTAL"]["VATBLOCK"] = $Vat_Block;
        $Charges_Block = array();
        if (!empty($chargesAndItsTaxes)) {
            $allCharges = getAllCharges();
            foreach ($chargesAndItsTaxes AS $chargeId => $chargeData) {
                $name = $allCharges[$chargeId]['name'];
                $Charges_Block[] = array('label' => $name, 'value' => $chargeData['value']);
            }
        }
        $Details["TOTAL"]["CHARGESBLOCK"] = $Charges_Block;
        return $Details;
    }
    private function getInventoryCurrencyInfoCustom($module, $focus) {
        $record_id = "";
        $inventory_table = self::$inventory_table_array[$module];
        $inventory_id = self::$inventory_id_array[$module];
        if (!empty($focus->id)) {
            $record_id = $focus->id;
        }
        return $this->getInventoryCurrencyInfoCustomArray($inventory_table, $inventory_id, $record_id);
    }
    private function getInventoryTaxTypeCustom($module, $focus) {
        if (!empty($focus->id)) {
            $res = self::$db->pquery("SELECT taxtype FROM " . self::$inventory_table_array[$module] . " WHERE " . self::$inventory_id_array[$module] . "=?", array($focus->id));
            return self::$db->query_result($res, 0, 'taxtype');
        }
        return "";
    }
    private function formatNumberToEMAIL($value) {
        $number = "";
        if (is_numeric($value)) {
            $number = number_format($value, self::$decimals, self::$decimal_point, self::$thousands_separator);
        }
        return $number;
    }
    private function retrieve_entity_infoCustom(&$focus, $record, $module) {
        $result = Array();
        foreach ($focus->tab_name_index as $table_name => $index) {
            $result[$table_name] = self::$db->pquery("SELECT * FROM " . $table_name . " WHERE " . $index . "=?", array($record));
        }
        $tabid = getTabid($module);
        $result1 = self::$db->pquery("SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?", array($tabid));
        $noofrows = self::$db->num_rows($result1);
        if ($noofrows) {
            while ($resultrow = self::$db->fetch_array($result1)) {
                $fieldcolname = $resultrow["columnname"];
                $tablename = $resultrow["tablename"];
                $fieldname = $resultrow["fieldname"];
                $fld_value = "";
                if (isset($result[$tablename])) {
                    $fld_value = self::$db->query_result($result[$tablename], 0, $fieldcolname);
                }
                $focus->column_fields[$fieldname] = $fld_value;
            }
        }
        $focus->column_fields["record_id"] = $record;
        $focus->column_fields["record_module"] = $module;
    }
    private function replaceCustomFunctions($after = "") {
        if (is_numeric(strpos(self::$content, '[CUSTOMFUNCTION' . strtoupper($after) . '|'))) {
            vglobal('its4you_main_focus', self::$focus);
            $focus = self::$focus;
            $Allowed_Functions = EMAILMaker_AllowedFunctions_Helper::getAllowedFunctions();
            vglobal("PDFMaker_template_id", "email");
            foreach (glob('modules/EMAILMaker/resources/functions/*.php') as $file) {
                include_once $file;
            }
            $customFunctions = explode("[CUSTOMFUNCTION" . strtoupper($after) . "|", self::$content);
            foreach ($customFunctions as $customFunction) {
                $customFunction = explode("|CUSTOMFUNCTION" . strtoupper($after) . "]", $customFunction) [0];
                $Params = $this->getCustomfunctionParams(trim($customFunction));
                $func = $Params[0];
                unset($Params[0]);
                if (in_array(trim($func), $Allowed_Functions)) {
                    $replacement = call_user_func_array(trim($func), $Params);
                } else {
                    $replacement = "";
                }
                self::$rep["[CUSTOMFUNCTION" . strtoupper($after) . "|" . $customFunction . "|CUSTOMFUNCTION" . strtoupper($after) . "]"] = $replacement;
            }
            $this->replaceContent();
        }
    }
    private function itsmd($val) {
        return md5($val);
    }
    private function convertRelatedBlocks() {
        include_once ("modules/EMAILMaker/resources/EMAILMakerRelBlockRun.php");
        if (strpos(self::$content, "#RELBLOCK") !== false) {
            preg_match_all("|#RELBLOCK([0-9]+)_START#|U", self::$content, $RelatedBlocks, PREG_PATTERN_ORDER);
            if (count($RelatedBlocks[1]) > 0) {
                $ConvertRelBlock = array();
                foreach ($RelatedBlocks[1] as $relblockid) {
                    if (!in_array($relblockid, $ConvertRelBlock)) {
                        $secmodule = self::$db->query_result(self::$db->pquery("SELECT secmodule FROM vtiger_emakertemplates_relblocks WHERE relblockid = ?", array($relblockid)), 0, "secmodule");
                        if (strpos(self::$content, "#RELBLOCK" . $relblockid . "_START#") !== false) {
                            if (strpos(self::$content, "#RELBLOCK" . $relblockid . "_END#") !== false) {
                                $tableDOM = $this->convertRelatedBlock($relblockid);
                                $oRelBlockRun = new EMAILMakerRelBlockRun(self::$focus->id, $relblockid, self::$module, $secmodule);
                                $oRelBlockRun->SetEMAILLanguage(self::$language);
                                $RelBlock_Data = $oRelBlockRun->GenerateReport();
                                $ExplodedEMAIL = array();
                                $Exploded = explode("#RELBLOCK" . $relblockid . "_START#", self::$content);
                                $ExplodedEMAIL[] = $Exploded[0];
                                for ($iterator = 1;$iterator < count($Exploded);$iterator++) {
                                    $SubExploded = explode("#RELBLOCK" . $relblockid . "_END#", $Exploded[$iterator]);
                                    foreach ($SubExploded as $part) {
                                        $ExplodedEMAIL[] = $part;
                                    }
                                    $highestpartid = $iterator * 2 - 1;
                                    $ProductParts[$highestpartid] = $ExplodedEMAIL[$highestpartid];
                                    $ExplodedEMAIL[$highestpartid] = '';
                                }
                                if (!in_array($secmodule, self::$relBlockModules)) self::$relBlockModules[] = $secmodule;
                                if (count($RelBlock_Data) > 0) {
                                    foreach ($RelBlock_Data as $RelBlock_Details) {
                                        foreach ($ProductParts as $productpartid => $productparttext) {
                                            $show_line = false;
                                            foreach ($RelBlock_Details AS $coll => $value) {
                                                if (trim($value) != "-" && $coll != "listprice") {
                                                    $show_line = true;
                                                }
                                                $productparttext = str_ireplace("$" . $coll . "$", $value, $productparttext);
                                            }
                                            if ($show_line) {
                                                $ExplodedEMAIL[$productpartid].= $productparttext;
                                            }
                                        }
                                    }
                                }
                                self::$content = implode('', $ExplodedEMAIL);
                            }
                        }
                        $ConvertRelBlock[] = $relblockid;
                    }
                }
            }
        }
    }
    private function convertRelatedBlock($relblockid) {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $tableDOM = false;
        if (is_array($html->find("td"))) {
            foreach ($html->find("td") as $td) {
                if (trim($td->plaintext) == "#RELBLOCK" . $relblockid . "_START#") {
                    $td->parent->outertext = "#RELBLOCK" . $relblockid . "_START#";
                }
                if (trim($td->plaintext) == "#RELBLOCK" . $relblockid . "_END#") {
                    $td->parent->outertext = "#RELBLOCK" . $relblockid . "_END#";
                }
            }
            self::$content = $html->save();
        }
        return $tableDOM;
    }
    private function fillInventoryData($module, $focus) {
        if (isset($focus->column_fields["currency_id"]) && isset($focus->column_fields["conversion_rate"]) && isset($focus->column_fields["hdnGrandTotal"])) {
            self::$inventory_table_array[$module] = $focus->table_name;
            self::$inventory_id_array[$module] = $focus->table_index;
        }
    }
    private function replaceInventoryDetailsBlock($module, $focus, $is_related = false) {
        if (!isset(self::$inventory_table_array[$module])) {
            $this->fillInventoryData($module, $focus);
        }
        if (!isset(self::$inventory_table_array[$module])) {
            return array();
        }
        $prefix = "";
        $IReplacements = array();
        $IReplacements["SUBTOTAL"] = $this->formatNumberToEMAIL($focus->column_fields["hdnSubTotal"]);
        $IReplacements["TOTAL"] = $this->formatNumberToEMAIL($focus->column_fields["hdnGrandTotal"]);
        $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
        $currencytype["currency_symbol"] = str_replace("", "&euro;", $currencytype["currency_symbol"]);
        $currencytype["currency_symbol"] = str_replace("", "&pound;", $currencytype["currency_symbol"]);
        $IReplacements["CURRENCYNAME"] = getTranslatedCurrencyString($currencytype["currency_name"]);
        $IReplacements["CURRENCYSYMBOL"] = $currencytype["currency_symbol"];
        $IReplacements["CURRENCYCODE"] = $currencytype["currency_code"];
        $IReplacements["ADJUSTMENT"] = $this->formatNumberToEMAIL($focus->column_fields["txtAdjustment"]);
        $Products = $this->getInventoryProducts($module, $focus);
        $IReplacements["TOTALWITHOUTVAT"] = $Products["TOTAL"]["TOTALWITHOUTVAT"];
        $IReplacements["VAT"] = $Products["TOTAL"]["TAXTOTAL"];
        $IReplacements["VATPERCENT"] = $Products["TOTAL"]["TAXTOTALPERCENT"];
        $IReplacements["TOTALWITHVAT"] = $Products["TOTAL"]["TOTALWITHVAT"];
        $IReplacements["SHTAXAMOUNT"] = $Products["TOTAL"]["SHTAXAMOUNT"];
        $IReplacements["SHTAXTOTAL"] = $Products["TOTAL"]["SHTAXTOTAL"];
        $IReplacements["DEDUCTEDTAXESTOTAL"] = $Products["TOTAL"]["DEDUCTEDTAXESTOTAL"];
        $IReplacements["TOTALDISCOUNT"] = $Products["TOTAL"]["FINALDISCOUNT"];
        $IReplacements["TOTALDISCOUNTPERCENT"] = $Products["TOTAL"]["FINALDISCOUNTPERCENT"];
        $IReplacements["TOTALAFTERDISCOUNT"] = $Products["TOTAL"]["TOTALAFTERDISCOUNT"];
        foreach ($IReplacements AS $r_key => $r_value) {
            if ($is_related !== false) {
                $prefix = "r-" . strtoupper($is_related) . "";
                self::$rep["$" . strtolower($prefix . "-" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "_" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "-" . $module . "-" . $r_key) . "$"] = $r_value;
                self::$rep["$" . strtolower($prefix . "-" . $module . "_" . $r_key) . "$"] = $r_value;
            } else {
                self::$rep["$" . $r_key . "$"] = $r_value;
                self::$rep["$" . "s-" . strtolower($r_key) . "$"] = $r_value;
            }
        }
        $this->replaceContent();
        if ($is_related === false) {
            $blockTypes = ['VATBLOCK', 'DEDUCTEDTAXESBLOCK', 'CHARGESBLOCK'];
            foreach ($blockTypes AS $blockType) {
                $vattable = '';
                if (count($Products["TOTAL"][$blockType]) > 0) {
                    foreach ($Products["TOTAL"][$blockType] as $keyW => $valueW) {
                        if ((empty($valueW['netto']) && $blockType != 'CHARGESBLOCK') || (empty($valueW['value']) && $blockType == 'CHARGESBLOCK')) {
                            unset($Products["TOTAL"][$blockType][$keyW]);
                        }
                    }
                }
                if (count($Products["TOTAL"][$blockType]) > 0) {
                    $vattable.= "<table border='1' style='border-collapse:collapse;' cellpadding='3'>";
                    $vattable.= '<tr>';
                    if ($blockType == 'CHARGESBLOCK') {
                        $vattable.= '<td></td><td nowrap align="right">' . vtranslate("LBL_CHARGESBLOCK_SUM", "EMAILMaker") . '</td>';
                    } else {
                        $vattable.= '<td nowrap align="center">' . vtranslate('Name') . '</td>
                                          <td nowrap align="center">' . vtranslate("LBL_VATBLOCK_VAT_PERCENT", "EMAILMaker") . '</td>
                                          <td nowrap align="center">' . vtranslate("LBL_VATBLOCK_SUM", "EMAILMaker") . ' (' . $currencytype['currency_symbol'] . ')</td>
                                          <td nowrap align="center">' . vtranslate("LBL_VATBLOCK_VAT_VALUE", "EMAILMaker") . ' (' . $currencytype['currency_symbol'] . ')</td>';
                    }
                    $vattable.= '</tr>';
                    foreach ($Products["TOTAL"][$blockType] as $keyW => $valueW) {
                        $vattable.= '<tr>';
                        if ($blockType == 'CHARGESBLOCK') {
                            $vattable.= '<td nowrap align="right" width="75%">' . $valueW['label'] . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['value']) . '</td>';
                        } else {
                            $vattable.= '<td nowrap align="left" width="20%">' . $valueW['label'] . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['value']) . ' %</td>
                                          <td nowrap align="right" width="30%">' . $this->formatNumberToEMAIL($valueW['netto']) . '</td>
                                          <td nowrap align="right" width="25%">' . $this->formatNumberToEMAIL($valueW['vat']) . '</td>';
                        }
                        $vattable.= '</tr>';
                    }
                    $vattable.= '</table>';
                }
                self::$rep['$' . $blockType . '$'] = $vattable;
                self::$rep['$s-' . strtolower($blockType) . '$'] = $vattable;
            }
            $this->replaceContent();
            foreach (['VAT', 'CHARGES'] AS $blockType) {
                if (strpos(self::$content, '#' . $blockType . 'BLOCK_START#') !== false && strpos(self::$content, '#' . $blockType . 'BLOCK_END#') !== false) {
                    self::$content = $this->convertBlock($blockType, self::$content);
                    $VExplodedEMAIL = [];
                    $VExploded = explode('#' . $blockType . 'BLOCK_START#', self::$content);
                    $VExplodedEMAIL[] = $VExploded[0];
                    for ($iterator = 1;$iterator < count($VExploded);$iterator++) {
                        $VSubExploded = explode('#' . $blockType . 'BLOCK_END#', $VExploded[$iterator]);
                        foreach ($VSubExploded as $Vpart) {
                            $VExplodedEMAIL[] = $Vpart;
                        }
                        $Vhighestpartid = $iterator * 2 - 1;
                        $VProductParts[$Vhighestpartid] = $VExplodedEMAIL[$Vhighestpartid];
                        $VExplodedEMAIL[$Vhighestpartid] = '';
                    }
                    if (count($Products['TOTAL'][$blockType . 'BLOCK']) > 0) {
                        foreach ($Products['TOTAL'][$blockType . 'BLOCK'] as $keyW => $valueW) {
                            foreach ($VProductParts as $productpartid => $productparttext) {
                                foreach ($valueW as $vColl => $vVal) {
                                    if (is_numeric($vVal)) {
                                        $vVal = $this->formatNumberToEMAIL($vVal);
                                    }
                                    $productparttext = str_replace('$' . $blockType . 'BLOCK_' . strtoupper($vColl) . '$', $vVal, $productparttext);
                                }
                                $VExplodedEMAIL[$productpartid].= $productparttext;
                            }
                        }
                    }
                    self::$content = implode('', $VExplodedEMAIL);
                }
            }
        }
        return $Products;
    }
    private function convertEntityImages() {
        self::$rep['$USERS_IMAGENAME$'] = $this->getUserImage(self::$focus->column_fields["assigned_user_id"]);
        self::$rep['$R_USERS_IMAGENAME$'] = $this->getUserImage($_SESSION["authenticated_user_id"]);
        switch (self::$module) {
            case "Contacts":
                self::$rep['$CONTACTS_IMAGENAME$'] = $this->getContactImage(self::$focus->id);
            break;
            case "Products":
                self::$rep['$PRODUCTS_IMAGENAME$'] = $this->getProductImage(self::$focus->id);
            break;
        }
    }
    public function getSubject() {
        return self::$subject;
    }
    public function setSubject($subject) {
        self::$subject = $subject;
    }
    public function getBody() {
        return self::$body;
    }
    public function setBody($body) {
        self::$body = $body;
    }
    public function getPreview() {
        return self::$preview;
    }
    public function setPreview($EMAIL_content) {
        if (isset($EMAIL_content["pre_body"])) {
            self::$preview = $EMAIL_content["pre_body"];
        } else {
            self::$preview = $EMAIL_content["body"];
        }
    }
    public function getAttachments() {
        return $this->getAttachmentsForId(self::$templateid);
    }
    public static function getInstanceById($templateId, $l_language, $l_module = "", $l_crmid = "", $l_recipientid = "", $l_recipientmodule = "") {
        self::$templateid = $templateId;
        self::$language = $l_language;
        self::$module = $l_module;
        if ($l_module != "") {
            $l_focus = CRMEntity::getInstance($l_module);
            if ($l_crmid != "" && $l_crmid != "0") {
                $l_focus->retrieve_entity_info($l_crmid, $l_module);
                $l_focus->id = $l_crmid;
            }
            self::$focus = $l_focus;
        }
        self::$recipientid = $l_recipientid;
        self::$recipientmodule = $l_recipientmodule;
        $self = new self();
        $self->getTemplateData();
        $self->getDecimalData();
        return $self;
    }
    public static function getInstance($l_module, $l_crmid, $l_language, $l_recipientid = "", $l_recipientmodule = "") {
        self::$templateid = "";
        self::$module = $l_module;
        if ($l_module != "") {
            if ($l_crmid != "" && $l_crmid != "0") {
                $l_focus = CRMEntity::getInstance($l_module);
                $l_focus->retrieve_entity_info($l_crmid, $l_module);
                $l_focus->id = $l_crmid;
            }
            self::$focus = $l_focus;
        }
        self::$language = $l_language;
        self::$recipientid = $l_recipientid;
        self::$recipientmodule = $l_recipientmodule;
        $self = new self();
        $self->getDecimalData();
        return $self;
    }
    private function getDecimalData() {
        $result2 = self::$db->pquery("SELECT * FROM vtiger_emakertemplates_settings", array());
        $data = self::$db->fetch_array($result2);
        self::$decimal_point = html_entity_decode($data["decimal_point"], ENT_QUOTES);
        self::$thousands_separator = html_entity_decode(($data["thousands_separator"] != "sp" ? $data["thousands_separator"] : " "), ENT_QUOTES);
        self::$decimals = $data["decimals"];
    }
    private function fixImg() {
        EMAILMaker_EMAILMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $surl = self::$site_url;
        if ($surl[strlen($surl) - 1] != "/") {
            $surl = $surl . "/";
        }
        $i = 1;
        if (is_array($html->find("img"))) {
            foreach ($html->find("img") as $img) {
                if (strpos($img->src . "/", $surl) === 0) {
                    $newPath = str_replace($surl . "/", "", $img->src);
                } elseif (strpos($img->src, $surl) === 0) {
                    $newPath = str_replace($surl, "", $img->src);
                } else {
                    $newPath = $img->src;
                }
                if (file_exists($newPath)) {
                    $img->src = "cid:image" . $i;
                    $Parts = explode(".", $newPath);
                    $img_type = $Parts[count($Parts) - 1];
                    self::$Email_Images["image" . $i] = array("name" => "image" . $i . "." . $img_type, "path" => $newPath);
                    $i++;
                }
            }
        }
        if (is_array($html->find("[background]"))) {
            foreach ($html->find('[background]') as $img) {
                if (strpos($img->background, $surl) === 0) {
                    $newPath = str_replace($surl, "", $img->background);
                    if (strpos($img->src . "/", $surl) === 0) {
                        $newPath = str_replace($surl . "/", "", $img->background);
                    } elseif (strpos($img->src, $surl) === 0) {
                        $newPath = str_replace($surl, "", $img->background);
                    } else {
                        $newPath = $img->background;
                    }
                    if (file_exists($newPath)) {
                        $img->background = "cid:image" . $i;
                        $Parts = explode(".", $newPath);
                        $img_type = $Parts[count($Parts) - 1];
                        self::$Email_Images["image" . $i] = array("name" => "image" . $i . "." . $img_type, "path" => $newPath);
                        $i++;
                    }
                }
            }
        }
        if ($i > 1) {
            self::$content = $html->save();
        }
    }
    private function replaceUserData($id, $data, $type) {
        $Fields = $this->getUserFieldsForPDF();
        foreach ($Fields AS $n => $v) {
            $val = $this->getUserValue($v, $data);
            self::$rep["$" . $type . "-user_" . $n . "$"] = $val;
            self::$rep["$" . $type . "-users_" . $n . "$"] = $val;
        }
        $currency_id = $this->getUserValue("currency_id", $data);
        $currency_info = $this->getInventoryCurrencyInfoCustomArray('', '', $currency_id);
        self::$rep["$" . $type . "-users-currency_name$"] = $currency_info["currency_name"];
        self::$rep["$" . $type . "-users-currency_code$"] = $currency_info["currency_code"];
        self::$rep["$" . $type . "-users-currency_symbol$"] = $currency_info["currency_symbol"];
        $this->replaceContent();
    }
    public function getEmailImages($convert_recipient = true) {
        return self::$Email_Images;
    }
} 

?>