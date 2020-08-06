<?php

include_once "modules/Vtiger/CRMEntity.php";
include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class SignedRecord
 */
class SignedRecord extends Vtiger_CRMEntity
{
    public $table_name = "vtiger_signedrecord";
    public $table_index = "signedrecordid";
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = array("vtiger_signedrecordcf", "signedrecordid");
    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = array("vtiger_crmentity", "vtiger_signedrecord", "vtiger_signedrecordcf");
    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = array("vtiger_crmentity" => "crmid", "vtiger_signedrecord" => "signedrecordid", "vtiger_signedrecordcf" => "signedrecordid");
    /**
     * Mandatory for Listing (Related listview)
     */
    public $list_fields = array("Signed Record Number" => array("signedrecord", "signedrecordno"), "Ticket" => array("crmentity", "ticketid"));
    public $list_fields_name = array("Signed Record Number" => "signedrecordno", "Ticket" => "ticketid");
    public $list_link_field = "signedrecordno";
    public $search_fields = array("Signed Record Number" => array("signedrecord", "signedrecordno"), "Ticket" => array("vtiger_crmentity", "ticketid"));
    public $search_fields_name = array("Signed Record Number" => "signedrecordno", "Ticket" => "ticketid");
    public $popup_fields = array("signedrecordno");
    public $def_basicsearch_col = "signedrecordno";
    public $def_detailview_recname = "signedrecordno";
    public $mandatory_fields = array("signedrecordno", "ticketid");
    public $default_order_by = "signedrecordno";
    public $default_sort_order = "ASC";
    /**
     * Invoked when special actions are performed on the module.
     * @param String $moduleName - Module name
     * @param String $eventType - Event Type
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        global $vtiger_current_version;
        if ($eventType == "module.postinstall") {
            $this->addUserSpecificTable();
            self::addWidgetTo($moduleName);
            $this->updateWsEntity($moduleName);
            $this->createCustomFields($moduleName);
            $this->UpdateSignedRecord();
            $this->addValueSentPickList();
            $this->addModTrackerforModule();
            if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                $this->changeColorsRecord();
            }
            $this->updateUnsetCustomModel();
            $this->filterAddNewField("related_to", $moduleName, 5);
            $this->filterAddNewField("filename", $moduleName, 6);
            $this->addFieldtoSummaryView("filename", $moduleName);
        } else {
            if ($eventType == "module.disabled") {
                self::removeWidgetTo($moduleName);
            } else {
                if ($eventType == "module.enabled") {
                    $this->addUserSpecificTable();
                    self::addWidgetTo($moduleName);
                    $this->convertToRelatedFields($moduleName);
                    $this->UpdateSignedRecord();
                    $this->addValueSentPickList();
                    if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                        $this->changeColorsRecord();
                    }
                    $this->addModTrackerforModule();
                } else {
                    if ($eventType == "module.preuninstall") {
                        self::removeWidgetTo($moduleName);
                    } else {
                        if ($eventType != "module.preupdate") {
                            if ($eventType == "module.postupdate") {
                                $this->addUserSpecificTable();
                                self::removeWidgetTo($moduleName);
                                self::addWidgetTo($moduleName);
                                $this->createCustomFields($moduleName);
                                $this->convertToRelatedFields($moduleName);
                                $this->UpdateSignedRecord();
                                $this->addValueSentPickList();
                                if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                                    $this->changeColorsRecord();
                                }
                                $this->updateUnsetCustomModel();
                                $this->filterAddNewField("related_to", $moduleName, 5);
                                $this->filterAddNewField("filename", $moduleName, 6);
                                $this->addFieldtoSummaryView("filename", $moduleName);
                                $this->addModTrackerforModule();
                                $this->changeSequence($moduleName);
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * Fn - addWidgetTo
     * Add header script to other module.
     * @param $moduleName
     */
    public static function addWidgetTo($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $css_widgetType = "HEADERCSS";
        $css_widgetName = "SignedRecord";
        $css_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
        $js_widgetType = "HEADERSCRIPT";
        $js_widgetName = "SignedRecord";
        $js_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
        $module = Vtiger_Module::getInstance($moduleName);
        if ($module) {
            $module->addLink($css_widgetType, $css_widgetName, $css_link);
            $module->addLink($js_widgetType, $js_widgetName, $js_link);
        }
        $rs = $adb->pquery("SELECT * FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `vtiger_ws_entity` (`name`, `handler_path`, `handler_class`, `ismodule`)\r\n            VALUES (?, 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1');", array($moduleName));
            $adb->pquery("UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)", array());
        }
    }
    /**
     * Fn - removeWidgetTo
     * @param $moduleName
     */
    public static function removeWidgetTo($moduleName)
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $css_link_vt6 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
            $js_link_vt6 = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        $css_widgetType = "HEADERCSS";
        $css_widgetName = "SignedRecord";
        $css_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "CSS.css";
        $js_widgetType = "HEADERSCRIPT";
        $js_widgetName = "SignedRecord";
        $js_link = (string) $template_folder . "/modules/" . $moduleName . "/resources/" . $moduleName . "JS.js";
        $module = Vtiger_Module::getInstance($moduleName);
        if ($module) {
            $module->deleteLink($css_widgetType, $css_widgetName, $css_link);
            $module->deleteLink($js_widgetType, $js_widgetName, $js_link);
            if ($vtVersion != "vt6") {
                $module->deleteLink($css_widgetType, $css_widgetName, $css_link_vt6);
                $module->deleteLink($js_widgetType, $js_widgetName, $js_link_vt6);
            }
        }
        $adb->pquery("DELETE FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
    }
    /**
     * @param string $moduleName
     * @throws Exception
     */
    private function createCustomFields($moduleName)
    {
        $initData = array($moduleName => array("LBL_DETAIL" => array("cf_signature_time" => array("label" => "Signature time", "table" => "vtiger_signedrecordcf", "uitype" => 14), "cf_secondary_signature_time" => array("label" => "Secondary Signature time", "table" => "vtiger_signedrecordcf", "uitype" => 14), "transactionid" => array("label" => "Transaction", "table" => "vtiger_signedrecordcf", "uitype" => 7), "cf_template" => array("label" => "Template", "table" => "vtiger_signedrecordcf", "uitype" => 1))));
        $adb = PearDatabase::getInstance();
        foreach ($initData as $moduleName => $blocks) {
            foreach ($blocks as $blockName => $fields) {
                $module = Vtiger_Module::getInstance($moduleName);
                $block = Vtiger_Block::getInstance($blockName, $module);
                if (!$block && $blockName) {
                    $block = new Vtiger_Block();
                    $block->label = $blockName;
                    $block->__create($module);
                }
                $currFieldSeqRs = $adb->pquery("SELECT sequence FROM `vtiger_field` WHERE block = ? ORDER BY sequence DESC LIMIT 0,1", array($block->id));
                $sequence = $adb->query_result($currFieldSeqRs, "sequence", 0);
                foreach ($fields as $name => $field) {
                    $existField = Vtiger_Field::getInstance($name, $module);
                    if (!$existField && $name && $field["table"]) {
                        $sequence++;
                        $newField = new Vtiger_Field();
                        $newField->name = $name;
                        $newField->label = $field["label"];
                        $newField->table = $field["table"];
                        $newField->uitype = $field["uitype"];
                        if ($field["uitype"] == 15 || $field["uitype"] == 16 || $field["uitype"] == "33") {
                            $newField->setPicklistValues($field["picklistvalues"]);
                        }
                        $newField->sequence = $sequence;
                        $newField->__create($block);
                        if ($field["uitype"] == 10) {
                            $newField->setRelatedModules(array($field["related_to_module"]));
                        }
                    }
                }
            }
        }
    }
    /**
     * @param string $moduleName
     */
    private function createDependentsList($moduleName)
    {
        $thisModule = Vtiger_Module::getInstance($moduleName);
        $thisModuleLabel = vtranslate($moduleName, $moduleName);
        $funcName = "get_dependents_list";
        $quotingTool = new QuotingTool();
        $entityModule = $quotingTool->getAllEntityModule();
        foreach ($entityModule as $m) {
            $dependentModule = Vtiger_Module::getInstance($m);
            if (!$dependentModule) {
                continue;
            }
            if (!$this->isExistRelatedList($dependentModule->getId(), $thisModule->getId(), $funcName)) {
                $dependentModule->setRelatedList($thisModule, $thisModuleLabel, "", $funcName);
            }
        }
    }
    /**
     * @param int $tabid
     * @param int $related_tabid
     * @param string $name
     * @return bool
     */
    private function isExistRelatedList($tabid, $related_tabid, $name)
    {
        global $adb;
        $rs = $adb->pquery("SELECT COUNT(relation_id) AS total FROM `vtiger_relatedlists` WHERE `tabid` = ? AND `related_tabid` = ? AND `name` LIKE ?", array($tabid, $related_tabid, $name));
        if ($adb->num_rows($rs) && ($data = $adb->fetch_array($rs))) {
            if (0 < $data["total"]) {
                return true;
            }            
        }
        return false;
    }
    /**
     * @param string $moduleName
     */
    private function updateWsEntity($moduleName)
    {
        global $adb;
        $rs = $adb->pquery("SELECT * FROM `vtiger_ws_entity` WHERE `name` = ?", array($moduleName));
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `vtiger_ws_entity` (`name`, `handler_path`, `handler_class`, `ismodule`)\r\n\t\t\t\tVALUES (?, 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1');", array($moduleName));
            $adb->pquery("UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)", array());
        }
    }
    /**
     * @param $moduleName
     */
    private function convertToRelatedFields($moduleName)
    {
        global $adb;
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $relatedFieldName = "related_to";
        $fieldId = 0;
        $rs = $adb->pquery("SELECT `vtiger_field`.`fieldid` FROM `vtiger_field` WHERE `tablename` LIKE ? AND `columnname` LIKE ?", array($this->table_name, $relatedFieldName));
        if ($adb->num_rows($rs) == 0) {
            $blockObject = Vtiger_Block::getInstance("LBL_DETAIL", $moduleModel);
            $blockModel = Vtiger_Block_Model::getInstanceFromBlockObject($blockObject);
            $fieldModel = new Vtiger_Field_Model();
            $fieldModel->set("name", $relatedFieldName)->set("table", $this->table_name)->set("generatedtype", 1)->set("uitype", 10)->set("label", "Related To")->set("typeofdata", "V~O")->set("quickcreate", 1)->set("columntype", "INT(19)");
            $newField = $blockModel->addField($fieldModel);
            $fieldId = $newField->getId();
        } else {
            if ($row = $adb->fetch_array($rs)) {
                $fieldId = $row["fieldid"];
            }
        }
        $resData = array();
        $rs = $adb->pquery("SELECT sr.signedrecordid, sr.ticketid, sr.quoteid, sr.potentialid FROM `vtiger_signedrecord` as sr");
        if ($adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $id = $row["signedrecordid"];
                if ($row["ticketid"]) {
                    $resData[$id] = $row["ticketid"];
                } else {
                    if ($row["quoteid"]) {
                        $resData[$id] = $row["quoteid"];
                    } else {
                        if ($row["potentialid"]) {
                            $resData[$id] = $row["potentialid"];
                        }
                    }
                }
            }
        }
        foreach ($resData as $id => $value) {
            $sql = "UPDATE vtiger_signedrecord SET related_to=? WHERE signedrecordid=?";
            $params = array($value, $id);
            $result = $adb->pquery($sql, $params);
        }
        $field_ticketid = "ticketid";
        $rs = $adb->pquery("SELECT `vtiger_field`.`fieldid` FROM `vtiger_field` WHERE `tablename` LIKE ? AND `columnname` LIKE ?", array($this->table_name, $field_ticketid));
        if ($adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $fieldModel_ticketid = Vtiger_Field_Model::getInstance($row["fieldid"], $moduleModel);
                $fieldModel_ticketid->delete();
            }
        }
        $field_quoteid = "quoteid";
        $rs = $adb->pquery("SELECT `vtiger_field`.`fieldid` FROM `vtiger_field` WHERE `tablename` LIKE ? AND `columnname` LIKE ?", array($this->table_name, $field_quoteid));
        if ($adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $fieldModel_quoteid = Vtiger_Field_Model::getInstance($row["fieldid"], $moduleModel);
                $fieldModel_quoteid->delete();
            }
        }
        $field_potentialid = "potentialid";
        $rs = $adb->pquery("SELECT `vtiger_field`.`fieldid` FROM `vtiger_field` WHERE `tablename` LIKE ? AND `columnname` LIKE ?", array($this->table_name, $field_potentialid));
        if ($adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $fieldModel_potentialid = Vtiger_Field_Model::getInstance($row["fieldid"], $moduleModel);
                $fieldModel_potentialid->delete();
            }
        }
        $result = $adb->pquery("ALTER TABLE vtiger_signedrecord DROP COLUMN ticketid");
        $result = $adb->pquery("ALTER TABLE vtiger_signedrecord DROP COLUMN quoteid");
        $result = $adb->pquery("ALTER TABLE vtiger_signedrecord DROP COLUMN potentialid");
        $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
        foreach ($supportedModulesList as $m) {
            $rs = $adb->pquery("SELECT `vtiger_fieldmodulerel`.`relmodule` FROM `vtiger_fieldmodulerel` WHERE `fieldid` = ? AND `module` LIKE ? AND `relmodule` LIKE ?", array($fieldId, $moduleName, $m));
            if ($adb->num_rows($rs) == 0) {
                $adb->pquery("INSERT INTO `vtiger_fieldmodulerel` (`fieldid`, `module`, `relmodule`) VALUES (?, ?, ?);", array($fieldId, $moduleName, $m));
            }
        }
    }
    public function changeColorsRecord()
    {
        global $adb;
        $adb->pquery("UPDATE vtiger_signedrecord_type SET `color` =? WHERE `signedrecord_type`=?", array("#00ff44", "Signed"));
        $adb->pquery("UPDATE vtiger_signedrecord_type SET `color` =? WHERE `signedrecord_type`=?", array("#00e1ff", "Opened"));
        $adb->pquery("UPDATE vtiger_signedrecord_type SET `color` =? WHERE `signedrecord_type`=?", array("#ff00ff", "Sent"));
    }
    public function UpdateSignedRecord()
    {
        global $adb;
        $entity = new CRMEntity();
        $entity->setModuleSeqNumber("configure", "SignedRecord", "SIG", 1);
        $moduleInstance = Vtiger_Module::getInstance("SignedRecord");
        $blockInstance = Vtiger_Block::getInstance("LBL_HEADER_INFORMATION", $moduleInstance);
        if (!$blockInstance) {
            $blockInstance = new Vtiger_Block();
            $blockInstance->label = "LBL_HEADER_INFORMATION";
            $moduleInstance->addBlock($blockInstance);
        }
        $fieldBrowser = Vtiger_Field::getInstance("signedrecord_browser", $moduleInstance);
        if (!$fieldBrowser) {
            $fieldBrowser = new Vtiger_Field();
            $fieldBrowser->name = "signedrecord_browser";
            $fieldBrowser->label = "Browser";
            $fieldBrowser->table = "vtiger_signedrecord";
            $fieldBrowser->column = "signedrecord_browser";
            $fieldBrowser->columntype = "VARCHAR(250)";
            $fieldBrowser->uitype = 1;
            $fieldBrowser->typeofdata = "V~O";
            $fieldBrowser->sequence = 1;
            $blockInstance->addField($fieldBrowser);
        }
        $fieldIP = Vtiger_Field::getInstance("signedrecord_ip", $moduleInstance);
        if (!$fieldIP) {
            $fieldIP = new Vtiger_Field();
            $fieldIP->name = "signedrecord_ip";
            $fieldIP->label = "IP";
            $fieldIP->table = "vtiger_signedrecord";
            $fieldIP->column = "signedrecord_ip";
            $fieldIP->columntype = "VARCHAR(100)";
            $fieldIP->uitype = 1;
            $fieldIP->typeofdata = "V~O";
            $fieldIP->sequence = 2;
            $blockInstance->addField($fieldIP);
        }
        $fieldCookie = Vtiger_Field::getInstance("signedrecord_cookie", $moduleInstance);
        if (!$fieldCookie) {
            $fieldCookie = new Vtiger_Field();
            $fieldCookie->name = "signedrecord_cookie";
            $fieldCookie->label = "Cookie";
            $fieldCookie->table = "vtiger_signedrecord";
            $fieldCookie->column = "signedrecord_cookie";
            $fieldCookie->columntype = "text";
            $fieldCookie->uitype = 1;
            $fieldCookie->typeofdata = "V~O";
            $fieldCookie->sequence = 3;
            $blockInstance->addField($fieldCookie);
        }
        $fieldPrimaryEmail = Vtiger_Field::getInstance("signedrecord_emails1", $moduleInstance);
        if (!$fieldPrimaryEmail) {
            $fieldPrimaryEmail = new Vtiger_Field();
            $fieldPrimaryEmail->name = "signedrecord_emails1";
            $fieldPrimaryEmail->label = "Primary Signature Emails";
            $fieldPrimaryEmail->table = "vtiger_signedrecord";
            $fieldPrimaryEmail->column = "signedrecord_emails1";
            $fieldPrimaryEmail->columntype = "text";
            $fieldPrimaryEmail->uitype = 1;
            $fieldPrimaryEmail->typeofdata = "V~O";
            $fieldPrimaryEmail->sequence = 3;
            $blockInstance->addField($fieldPrimaryEmail);
        }
        $fieldSencondaryEmail = Vtiger_Field::getInstance("signedrecord_emails2", $moduleInstance);
        if (!$fieldSencondaryEmail) {
            $fieldSencondaryEmail = new Vtiger_Field();
            $fieldSencondaryEmail->name = "signedrecord_emails2";
            $fieldSencondaryEmail->label = "Secondary Signature Emails";
            $fieldSencondaryEmail->table = "vtiger_signedrecord";
            $fieldSencondaryEmail->column = "signedrecord_emails2";
            $fieldSencondaryEmail->columntype = "text";
            $fieldSencondaryEmail->uitype = 1;
            $fieldSencondaryEmail->typeofdata = "V~O";
            $fieldSencondaryEmail->sequence = 3;
            $blockInstance->addField($fieldSencondaryEmail);
        }
        $allFilter = Vtiger_Filter::getInstance("All", $moduleInstance);
        if ($allFilter) {
            $allFilter->delete();
        }
        $filter1 = new Vtiger_Filter();
        $filter1->name = "All";
        $filter1->isdefault = true;
        $moduleInstance->addFilter($filter1);
        $field1 = Vtiger_Field::getInstance("signature_date", $moduleInstance);
        if ($field1) {
            $filter1->addField($field1);
        }
        $field2 = Vtiger_Field::getInstance("cf_signature_time", $moduleInstance);
        if ($field2) {
            $filter1->addField($field2, 1);
        }
        $field3 = Vtiger_Field::getInstance("signedrecord_type", $moduleInstance);
        if ($field3) {
            $filter1->addField($field3, 2);
        }
        $field4 = Vtiger_Field::getInstance("signature_name", $moduleInstance);
        if ($field4) {
            $filter1->addField($field4, 3);
        }
        $field5 = Vtiger_Field::getInstance("signedrecord_status", $moduleInstance);
        if ($field5) {
            $filter1->addField($field5, 4);
        }
    }
    public function addUserSpecificTable()
    {
        global $vtiger_current_version;
        if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
            $moduleName = "SignedRecord";
            $moduleUserSpecificTable = Vtiger_Functions::getUserSpecificTableName($moduleName);
            if (!Vtiger_Utils::CheckTable($moduleUserSpecificTable)) {
                Vtiger_Utils::CreateTable($moduleUserSpecificTable, "(`recordid` INT(19) NOT NULL,\r\n\t\t\t\t\t   `userid` INT(19) NOT NULL,\r\n\t\t\t\t\t   `starred` varchar(100) NULL,\r\n\t\t\t\t\t   Index `record_user_idx` (`recordid`, `userid`)\r\n\t\t\t\t\t\t)", true);
            }
        }
    }
    public function updateUnsetCustomModel()
    {
        global $adb;
        $tabid = getTabid("SignedRecord");
        $adb->pquery("Update vtiger_tab set source = ? WHERE tabid = ?", array("", $tabid));
    }
    public function filterAddNewField($fieldName, $moduleName, $index = 0)
    {
        $moduleInstance = Vtiger_Module::getInstance($moduleName);
        $fieldInstance = Vtiger_Field::getInstance($fieldName, $moduleInstance);
        $filter1 = Vtiger_Filter::getInstance("All", $moduleInstance);
        if ($filter1) {
            $filter1->addField($fieldInstance, $index);
        }
    }
    public function addFieldtoSummaryView($fieldName, $moduleName)
    {
        global $adb;
        $tabid = getTabid($moduleName);
        $adb->pquery("Update vtiger_field set summaryfield = 1 WHERE tabid = ? AND fieldname = ?", array($tabid, $fieldName));
    }
    public function changeSequence($moduleName)
    {
        global $adb;
        $tabid = getTabid($moduleName);
        $adb->pquery("update vtiger_field set sequence='50' where tabid=" . $tabid);
        $adb->pquery("update vtiger_field set sequence='1',summaryfield='1' where tabid='" . $tabid . "' and fieldname='cf_template'");
        $adb->pquery("update vtiger_field set sequence='2' where tabid='" . $tabid . "' and fieldname='signature_date' ");
        $adb->pquery("update vtiger_field set sequence='3' where tabid='" . $tabid . "' and fieldname='signedrecord_type' ");
        $adb->pquery("update vtiger_field set sequence='4' where tabid='" . $tabid . "' and fieldname='filename' ");
        $adb->pquery("update vtiger_field set sequence='5' where tabid='" . $tabid . "' and fieldname='signature_name' ");
        $adb->pquery("update vtiger_field set sequence='6' where tabid='" . $tabid . "' and fieldname='signedrecord_status' ");
        $adb->pquery("update vtiger_field set sequence='7' where tabid='" . $tabid . "' and fieldname='signedrecordno' ");
        $adb->pquery("update vtiger_field set summaryfield='0' where tabid='" . $tabid . "' and (fieldname='secondary_signature_name' or fieldname='secondary_signature_date' or fieldname='secondary_signedrecord_status')");
    }
    public function addModTrackerforModule()
    {
        require_once "modules/ModTracker/ModTracker.php";
        $moduleInstance = Vtiger_Module::getInstance("SignedRecord");
        $blockInstance = Vtiger_Block::getInstance("LBL_DETAIL", $moduleInstance);
        $createTime = Vtiger_Field::getInstance("createdtime", $moduleInstance);
        if (!$createTime) {
            $createTime = new Vtiger_Field();
            $createTime->label = "Created Time";
            $createTime->name = "createdtime";
            $createTime->table = "vtiger_crmentity";
            $createTime->column = "createdtime";
            $createTime->uitype = 70;
            $createTime->typeofdata = "T~O";
            $createTime->displaytype = 2;
            $blockInstance->addField($createTime);
        }
        $modifiedTime = Vtiger_Field::getInstance("modifiedtime", $moduleInstance);
        if (!$modifiedTime) {
            $modifiedTime = new Vtiger_Field();
            $modifiedTime->label = "Modified Time";
            $modifiedTime->name = "modifiedtime";
            $modifiedTime->table = "vtiger_crmentity";
            $modifiedTime->column = "modifiedtime";
            $modifiedTime->uitype = 70;
            $modifiedTime->typeofdata = "T~O";
            $modifiedTime->displaytype = 2;
            $blockInstance->addField($modifiedTime);
        }
        ModTracker::enableTrackingForModule($moduleInstance->id);
    }
    public function addValueSentPickList()
    {
        global $adb;
        $new_value = "Sent";
        $sql = "select * from vtiger_signedrecord_type where signedrecord_type=?";
        $res = $adb->pquery($sql, array($new_value));
        if ($adb->num_rows($res) == 0) {
            $adb->pquery("insert into vtiger_signedrecord_type (`signedrecord_type`,`sortorderid`,`presence`,`color`) values (?,3,1,'#ff00ff')", array($new_value));
        }
    }
}

?>