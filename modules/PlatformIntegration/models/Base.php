<?php

ini_set("display_errors", "on");
error_reporting(32767 & ~2 & ~8 & ~8192 & ~2048);
ini_set("max_execution_time", 6000);
require_once "modules/Accounts/Accounts.php";
require_once "modules/Contacts/Contacts.php";
require_once "modules/Products/Products.php";
require_once "modules/Transactions/Transactions.php";
require_once "modules/Potentials/Potentials.php";
require_once "modules/Services/Services.php";
require_once "modules/Invoice/Invoice.php";
require_once "modules/KYC/KYC.php";
class PlatformIntegration_Base_Model extends Vtiger_Module_Model
{
    protected $maxResults = 200;
    protected $maxPerTime = 250;
    public static $groupName = "Platform";
    public $qboSdkLink = "https://www.vtexperts.com/files/qbo_sdk.zip";
    public $redirectURI = "https://dev03.vtedev.com/quickbooks/oauth-redirect/callback.php?id=";
    public $urlToGetId = "https://dev03.vtedev.com/quickbooks/oauth-redirect/index.php?site_url=";
    public $qboSdkSource = "modules/PlatformIntegration/helpers/";
    public $phpVersionRequired = "5.6.0";
    protected $platformGroupId = "";
    protected $firstAdminUserId = "";
    protected $moduleName = "PlatformIntegration";
    protected $allowedModule = array();
    protected $allConfig = array();
    protected $platformModules = array();
    protected $allPicklists = array();
    protected $taxRates = array();
    protected $unusedFields = array("cf_sync_to_qbo", "cf_platform_status");
    protected $platformDiscountItem = array("Name" => "Discount", "Type" => "Service", "Sku" => "Discount", "UnitPrice" => 0, "IncomeAccountRef" => 1, "PurchaseCost" => 0);
    protected $defaultPlatformVersion = "US";
    protected $platformVersion = array("US", "AUS");
    protected $taxesForAUS = array("GST free (sales)", "GST (sales)");
    public function getSupportedplatformVersions()
    {
        return $this->platformVersion;
    }
    public function getFirstIncomeAccountRef()
    {
        global $adb;
        $sql = "SELECT platform_value FROM `platformintegration_picklist_fields` WHERE platform_source_module = 'Account' AND platform_type = 'Income' LIMIT 0, 1";
        $ret = 1;
        $res = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($res)) {
            $ret = $adb->query_result($res, 0, "platform_value");
        }
        return $ret;
    }
    public function getInfoOfPlatformModule($platformModule)
    {
        try {
            $error = "";
            $code = "Succeed";
            $res = $this->getAllPlatformModules();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $allPlatformModule = $res["result"];
            $result = false;
            foreach ($allPlatformModule as $row) {
                if ($row["platform_module"] == $platformModule) {
                    $result = $row;
                    break;
                }
            }
            if (empty($result)) {
                $error = vtranslate("LBL_CANNOT_FIND_THIS_PLATFORM_MODULE", "PlatformIntegration");
                $code = "Failed";
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getAllPlatformModules()
    {
        try {
            $error = "";
            $code = "Succeed";
            if (!empty($this->platformModules)) {
                return array("code" => $code, "error" => $error, "result" => $this->platformModules);
            }
            $sql = "SELECT * FROM platformintegration_modules";
            global $adb;
            $res = $adb->pquery($sql, array());
            $nr = $adb->num_rows($res);
            $result = array();
            if (0 < $nr) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $result[] = $row;
                }
            } else {
                $error = vtranslate("LBL_CANNOT_FIND_THIS_PLATFORM_MODULE", "PlatformIntegration");
                $code = "Failed";
            }
            $this->platformModules = $result;
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getNameFromPicklists($val, $platformModule = "", $platformField = "", $platformSourceModule = "")
    {
        $items = $this->getAllPicklists();
        foreach ($items as $item) {
            if ($item["platform_value"] == $val && $item["platform_module"] == $platformModule && $item["platform_field"] == $platformField) {
                return $item["platform_name"];
            }
        }
        if (!empty($platformSourceModule)) {
            foreach ($items as $item) {
                if ($item["platform_value"] == $val && $item["platform_source_module"] == $platformSourceModule) {
                    return $item["platform_name"];
                }
            }
        }
        foreach ($items as $item) {
            if ($item["platform_value"] == $val) {
                return $item["platform_name"];
            }
        }
        return $val;
    }
    public function getAllPicklists($reload = false)
    {
        global $adb;
        $result = array();
        if (!empty($this->allPicklists) && !$reload) {
            return $this->allPicklists;
        }
        $sql = "SELECT * FROM platformintegration_picklist_fields";
        $res = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $result[] = $row;
            }
            $this->allPicklists = $result;
        }
        return $result;
    }
    public function getTaxRates($reload = false)
    {
        global $adb;
        if (!empty($this->taxRates) && !$reload) {
            return $this->taxRates;
        }
        $result = array();
        $sql = "SELECT t1.*, t2.platform_tax_id, t2.latest_value, t2.latest_update, t3.taxcode_id\r\n                FROM vtiger_inventorytaxinfo t1\r\n                LEFT JOIN platformintegration_mapping_tax t2 ON t1.taxid = t2.vt_tax_id\r\n                LEFT JOIN platformintegration_taxcode_sales t3 ON t3.taxrate_id = t2.platform_tax_id";
        $res = $adb->pquery($sql, array());
        $nr = $adb->num_rows($res);
        if (0 < $nr) {
            while ($row = $adb->fetchByAssoc($res)) {
                $result[] = $row;
            }
        }
        $this->taxRates = $result;
        return $result;
    }
    public function getTaxRateByPlatformId($taxRates, $platformId)
    {
        if (!empty($platformId)) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate["platform_tax_id"] == $platformId) {
                    return $taxRate;
                }
            }
        }
        return array();
    }
    public function getTaxRateByPlatformTaxName($taxRates, $taxName)
    {
        if (!empty($taxName)) {
            foreach ($taxRates as $taxRate) {
                $taxLabel = strtolower($taxRate["taxlabel"]);
                $taxName = strtolower($taxName);
                if ($taxLabel == $taxName) {
                    return $taxRate;
                }
            }
        }
        return array();
    }
    public function getAllowedModule()
    {
        $result = array();
        if (empty($this->allowedModule)) {
            global $adb;
            $sql = "SELECT DISTINCT vt_module from platformintegration_modules WHERE vt_module IS NOT NULL AND vt_module != ''";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $result[] = $row["vt_module"];
                }
                $this->allowedModule = $result;
            }
        }
        return $this->allowedModule;
    }
    public function getAllFieldsOfVTModule($vtModule)
    {
        $moduleModel = Vtiger_Module_Model::getInstance($vtModule);
        $vt_fields = $moduleModel->getFields();
        return $vt_fields;
    }
    public function getModuleLinkToField($fields, $fieldName = "")
    {
        $module = "";
        foreach ($fields as $field) {
            if ($field->getFieldName() == $fieldName) {
                $uitype = intval($field->get("uitype"));
                if ($uitype == 51 || $uitype == 73) {
                    $module = "Accounts";
                } else {
                    if ($uitype == 57) {
                        $module = "Contacts";
                    }
                }
            }
        }
        return $module;
    }
    public function getMappedInfoByPlatformRecord($platformId, $platformModule, $getAll = false)
    {
        global $adb;
        $mappedInfo = array();
        $sql = "SELECT VVL.*\r\n                FROM vtiger_platformintegrationlinks VVL\r\n                INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                INNER JOIN vtiger_crmentity vc ON VVL.vt_id = vc.crmid\r\n                WHERE vc.deleted = 0\r\n                AND VVL.platform_id IN (" . $platformId . ") AND VVL.platform_module=?";
        $param = array($platformModule);
        $res = $adb->pquery($sql, $param);
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                if ($row["platform_module"] == $platformModule) {
                    if ($getAll == false) {
                        return $row;
                    }
                    $mappedInfo[] = $row;
                }
            }
        }
        return $mappedInfo;
    }
    public function getMappedInfoByVtigerRecord($vtId, $vtModule, $isGetMultiple = false)
    {
        global $adb;
        $mappedInfo = array();
        $sql = "SELECT VVL.* FROM vtiger_platformintegrationlinks VVL \r\n                INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                WHERE VVL.vt_id IN (" . $vtId . ") AND VVL.vt_module=?";
        if (!$isGetMultiple) {
            $sql .= " LIMIT 1";
        }
        $res = $adb->pquery($sql, array($vtModule));
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                if (!$isGetMultiple) {
                    $mappedInfo = $row;
                } else {
                    $mappedInfo[] = $row;
                }
            }
        }
        if ($vtModule == "Contacts" && empty($mappedInfo)) {
            $sql = "SELECT VVL.*\r\n                        FROM vtiger_platformintegrationlinks VVL\r\n                        INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                        INNER JOIN vtiger_account VA ON VA.accountid = VVL.vt_id\r\n                        INNER JOIN vtiger_crmentity VC2 ON VA.accountid = VC2.crmid AND VC2.deleted=0\r\n                        INNER JOIN vtiger_contactdetails VC ON VC.accountid = VA.accountid\r\n                        WHERE VVL.vt_module='Accounts' AND VC.contactid=? LIMIT 1";
            $res = $adb->pquery($sql, array($vtId));
            if (0 < $adb->num_rows($res) && ($row = $adb->fetchByAssoc($res))) {
                $sql2 = "SELECT contactid\r\n                                FROM vtiger_contactdetails\r\n                                WHERE accountid=?\r\n                                ORDER BY contactid DESC\r\n                                LIMIT 0, 1";
                $res2 = $adb->pquery($sql2, array($row["vt_id"]));
                if (0 < $adb->num_rows($res2)) {
                    while ($row2 = $adb->fetchByAssoc($res2)) {
                        $vtId1 = intval($vtId);
                        $vtId2 = intval($row2["contactid"]);
                        if ($vtId1 == $vtId2) {
                            $dataInput = array("platform_module" => "Customer", "platform_id" => $row["platform_id"], "vt_module" => $vtModule, "vt_id" => $vtId, "latest_value" => "", "latest_update" => "");
                            $this->saveMappingInfo($dataInput);
                            $sql4 = "SELECT VVL.* FROM vtiger_platformintegrationlinks VVL \r\n                                            INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                                            WHERE VVL.vt_id=? AND VVL.vt_module=? LIMIT 1";
                            $res4 = $adb->pquery($sql4, array($vtId, $vtModule));
                            if (0 < $adb->num_rows($res4)) {
                                while ($row4 = $adb->fetchByAssoc($res4)) {
                                    $mappedInfo = $row4;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $mappedInfo;
    }
    public function getValueFromPlatformRecord($record, $fieldName)
    {
        $spliter = ".";
        $firstPos = strpos($fieldName, $spliter);
        if ($firstPos == false) {
            return $record->{$fieldName};
        }
        $oldFieldName = substr($fieldName, 0, $firstPos);
        $newFieldName = substr($fieldName, $firstPos + 1);
        $newRecord = $record->{$oldFieldName};
        return $this->getValueFromPlatformRecord($newRecord, $newFieldName);
    }
    public function setValueForPlatformRecord($record, $fieldName = "", $value = "")
    {
        $value = html_entity_decode($value, ENT_QUOTES);
        $spliter = ".";
        $firstPos = strpos($fieldName, $spliter);
        if ($firstPos == false) {
            if (empty($record[$fieldName])) {
                $record[$fieldName] = $value;
            }
        } else {
            $oldFieldName = substr($fieldName, 0, $firstPos);
            $newFieldName = substr($fieldName, $firstPos + 1);
            if (!is_array($record[$oldFieldName])) {
                $record[$oldFieldName] = array();
            }
            $record[$oldFieldName] = $this->setValueForPlatformRecord($record[$oldFieldName], $newFieldName, $value);
        }
        return $record;
    }
    public function getPlatformStatus($s)
    {
        if (!empty($s) && strtolower($s) == "true") {
            return "Active";
        }
        return "Inactive";
    }
    public function getQueueByRecordId($module, $id, $status = "")
    {
        global $adb;
        $sql = "SELECT vtiger_platformintegrationqueues.* FROM vtiger_platformintegrationqueues\r\n                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_platformintegrationqueues.platformintegrationqueueid AND vtiger_crmentity.deleted=0\r\n                    WHERE from_module = ? AND from_id = ?";
        $param = array($module, $id);
        if (!empty($status)) {
            $sql .= " AND platformintegrationqueue_status=?";
            $param[] = $status;
        }
        $res = $adb->pquery($sql, $param);
        $result = array();
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $result[] = $row;
            }
        }
        return $result;
    }
    public function updateStatusOfQueue($qId, $status = "Successful")
    {
        if (!empty($qId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($qId, "PlatformIntegrationQueues");
            $recordModel->set("id", $qId);
            $recordModel->set("mode", "edit");
            $recordModel->set("platformintegrationqueue_status", vtranslate($status, "PlatformIntegration"));
            $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
            $recordModel->save();
        }
    }
    public function addValueToPicklists($vtModule, $vtField, $newValue)
    {
        global $vtiger_current_version;
        $vmodule = Vtiger_Module::getInstance($vtModule);
        if ($vmodule) {
            $moduleModel = Settings_Picklist_Module_Model::getInstance($vtModule);
            $picklistFieldModel = Settings_Picklist_Field_Model::getInstance($vtField, $moduleModel);
            $rolesSelected = array();
            $roleRecordList = Settings_Roles_Record_Model::getAll();
            foreach ($roleRecordList as $roleRecord) {
                $rolesSelected[] = $roleRecord->getId();
            }
            $fieldModel = Vtiger_Field_Model::getInstance($vtField, $moduleModel);
            $allValues = $fieldModel->getPicklistValues();
            if (!array_key_exists($newValue, $allValues)) {
                $moduleModel->addPickListValues($picklistFieldModel, $newValue, $rolesSelected);
            }
            if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
                $moduleModel->handleLabels($vtModule, array($newValue), array(), "add");
            }
        }
    }
    public function getRelatedPlatformId($vt_id, $platform_module)
    {
        global $adb;
        $platformId = "";
        $sql = "SELECT VVL.platform_id FROM vtiger_platformintegrationlinks VVL \r\n                INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                WHERE VVL.vt_id=? AND VVL.platform_module=? LIMIT 1";
        $res = $adb->pquery($sql, array($vt_id, $platform_module));
        if (0 < $adb->num_rows($res)) {
            $platformId = $adb->query_result($res, 0, "platform_id");
        }
        return $platformId;
    }
    public function getMappedFields($platformModule, $vtModule)
    {
        global $adb;
        $sql = "SELECT DISTINCT v1.*, v2.is_picklist, v2.module_ref, v2.data_type,\r\n                    v2.non_editable, v2.platform_field_label, v2.max_len,\r\n                    vf.fieldlabel AS vt_field_label, vf.presence AS vt_presence\r\n                FROM platformintegration_mapping_fields v1\r\n                INNER JOIN platformintegration_modules_fields v2 ON v1.platform_module = v2.platform_module AND v1.platform_field = v2.platform_field\r\n                LEFT JOIN vtiger_field vf ON vf.fieldname = v1.vt_field AND vf.tabid IN (SELECT tabid FROM vtiger_tab WHERE `name`=?)\r\n                WHERE v1.platform_module = ? AND v1.vt_module = ? AND v1.is_active = 1\r\n                ORDER BY v2.non_editable DESC, v1.id ASC";
        $res = $adb->pquery($sql, array($vtModule, $platformModule, $vtModule));
        $mappedFields = array();
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $mappedFields[] = $row;
            }
        }
        return $mappedFields;
    }
    public function getInfoFromMappedFields($mappedFields, $conditions)
    {
        if (is_array($conditions)) {
            foreach ($mappedFields as $mappedField) {
                $hasFields = false;
                $isContinue = false;
                foreach ($conditions['platform_field'] as $k => $v) {
                    if (!empty($v)) {
                        $hasFields = true;
                        if ($mappedField['platform_field'] != $v) {
                            $isContinue = true;
                            break;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
                if ($hasFields) {
                    return $mappedField;
                }
            }
        }
        return array();
    }
    public function getPlatformApi($reload = false)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            if (empty($this->allConfig) || $reload) {
                $sql = "SELECT * FROM platformintegration_api LIMIT 1";
                $res = $adb->pquery($sql, array());
                $nr = $adb->num_rows($res);
                if (0 < $nr) {
                    $result = $adb->fetchByAssoc($res, 0);
                    $this->allConfig = $result;
                } else {
                    $code = "Failed";
                    $error = vtranslate("LBL_CANNOT_FIND_RECORD_PLATFORMAPI", "PlatformIntegration");
                }
            }
            return array("code" => $code, "error" => $error, "result" => $this->allConfig);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getPlatformVersion()
    {
        $res = $this->getPlatformApi();
        $qboApi = $res["result"];
        $qboVersion = $qboApi["platform_version"];
        $qboVersion = strtoupper($qboVersion);
        $supportedQboVersions = $this->getSupportedPlatformVersions();
        if (!in_array($qboVersion, $supportedQboVersions)) {
            $qboVersion = $this->defaultPlatformVersion;
        }
        return $qboVersion;
    }
    public function getVtigerRecordByName($vtModule, $name = "")
    {
        global $adb;
        $crmid = "";
        $obj = new $vtModule();
        $tab_name = $obj->tab_name;
        $tab_name_index = $obj->tab_name_index;
        $table_name = $obj->table_name;
        $table_index = $obj->table_index;
        $list_link_field = $obj->list_link_field;
        $query = "SELECT vtiger_crmentity.crmid";
        $query .= " FROM " . $obj->table_name;
        foreach ($tab_name as $k) {
            if ($k != $table_name && $k != "vtiger_inventoryproductrel") {
                $query .= " INNER JOIN " . $k . " ON " . $k . "." . $tab_name_index[$k] . " = " . $table_name . "." . $table_index;
            }
        }
        $query .= " WHERE vtiger_crmentity.deleted = 0 AND " . $list_link_field . " =? LIMIT 1";
        $res = $adb->pquery($query, array($name));
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $crmid = $row["crmid"];
            }
        }
        return $crmid;
    }
    public function updateLastDateSynched($recordModel)
    {
        global $adb;
        $moduleName = $recordModel->getModuleName();
        $sql = "SELECT VF.fieldid\r\n                FROM vtiger_field VF\r\n                INNER JOIN vtiger_tab VT ON VF.tabid=VT.tabid\r\n                WHERE VF.fieldname = 'cf_last_date_synched' AND VT.`name`=?";
        $res = $adb->pquery($sql, array($moduleName));
        if (0 < $adb->num_rows($res)) {
            $focus = CRMEntity::getInstance($moduleName);
            $table = $focus->customFieldTable[0];
            $field = $focus->customFieldTable[1];
            $sql = "UPDATE " . $table . " SET cf_last_date_synched=? WHERE " . $field . "=?";
            $date_var = date("Y-m-d H:i:s");
            $date_var = $adb->formatDate($date_var, true);
            $param = array($date_var, $recordModel->getId());
            $adb->pquery($sql, $param);
        }
    }
    public function getAllVtigerRecordToSync($vtModule, $recordId = "")
    {
        try {
            global $adb;
            $result = array();
            $code = "Succeed";
            $error = "";
            $obj = new $vtModule();
            $table_name = $obj->table_name;
            $table_index = $obj->table_index;
            $tab_name = $obj->tab_name;
            $tab_name_index = $obj->tab_name_index;
            $customFieldTable = $obj->customFieldTable;
            $maxPerTime = $this->maxPerTime;
            $param = array();
            $query = "SELECT DISTINCT  vtiger_platformintegrationqueues.platformintegrationqueueid, " . $table_name . ".*";
            foreach ($tab_name as $k) {
                if ($k != $table_name && $k != "vtiger_inventoryproductrel") {
                    $query .= ", " . $k . ".* ";
                }
            }
            $query .= " FROM " . $obj->table_name;
            foreach ($tab_name as $k) {
                if ($k != $table_name && $k != "vtiger_inventoryproductrel") {
                    $query .= " INNER JOIN " . $k . " ON " . $k . "." . $tab_name_index[$k] . " = " . $table_name . "." . $table_index;
                }
            }
            $query .= " INNER JOIN vtiger_platformintegrationqueues ON vtiger_platformintegrationqueues.from_id = " . $table_name . "." . $table_index . " AND vtiger_platformintegrationqueues.platformintegrationqueue_status = 'Queue'";
            $query .= " INNER JOIN vtiger_crmentity VC2 ON vtiger_platformintegrationqueues.platformintegrationqueueid = VC2.crmid AND VC2.deleted = 0";
            $query .= " WHERE vtiger_crmentity.deleted = 0";
            $query .= " AND " . $customFieldTable[0] . ".cf_sync_to_platformintegration=1";
            if (!empty($recordId)) {
                $query .= " AND vtiger_crmentity.crmid=?";
                $param[] = $recordId;
            }
            $query .= " ORDER BY vtiger_platformintegrationqueues.platformintegrationqueueid ASC LIMIT 0, " . $maxPerTime;
            $res = $adb->pquery($query, $param);
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $result[] = $row;
                }
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function countAllVtigerRecordToSync($vtModule)
    {
        try {
            global $adb;
            $result = 0;
            $code = "Succeed";
            $error = "";
            $obj = new $vtModule();
            $table_name = $obj->table_name;
            $table_index = $obj->table_index;
            $tab_name = $obj->tab_name;
            $tab_name_index = $obj->tab_name_index;
            $customFieldTable = $obj->customFieldTable;
            $param = array();
            $query = "SELECT COUNT(DISTINCT vtiger_platformintegrationqueues.platformintegrationqueueid) as total_record";
            $query .= " FROM " . $obj->table_name;
            foreach ($tab_name as $k) {
                if ($k != $table_name && $k != "vtiger_inventoryproductrel") {
                    $query .= " INNER JOIN " . $k . " ON " . $k . "." . $tab_name_index[$k] . " = " . $table_name . "." . $table_index;
                }
            }
            $query .= " INNER JOIN vtiger_platformintegrationqueues ON vtiger_platformintegrationqueues.from_id = " . $table_name . "." . $table_index . " AND vtiger_platformintegrationqueues.platformintegrationqueue_status = 'Queue'";
            $query .= " INNER JOIN vtiger_crmentity VC2 ON vtiger_platformintegrationqueues.platformintegrationqueueid = VC2.crmid AND VC2.deleted = 0";
            $query .= " WHERE vtiger_crmentity.deleted = 0";
            $query .= " AND " . $customFieldTable[0] . ".cf_sync_to_platformintegration=1";
            $res = $adb->pquery($query, $param);
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $result = $row["total_record"];
                }
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function saveMappingInfo($data)
    {
        try {
            global $adb;
            $error = "";
            $code = "Succeed";
            $default = array("id" => "", "platform_module" => "", "platform_id" => NULL, "vt_module" => "", "vt_id" => NULL, "latest_value" => "", "latest_update" => "", "latest_update_vt" => "");
            foreach ($default as $k => $v) {
                if (!empty($data[$k])) {
                    $default[$k] = $data[$k];
                }
            }
            if (empty($default["id"])) {
                $sql = "SELECT VVL.platformintegrationlinkid\r\n                    FROM vtiger_platformintegrationlinks VVL\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE VVL.platform_module=? AND VVL.platform_id=? AND VVL.vt_module=? AND VVL.vt_id=? LIMIT 1";
                $res = $adb->pquery($sql, array($default["platform_module"], $default["platform_id"], $default["vt_module"], $default["vt_id"]));
                if ($adb->num_rows($res)) {
                    $row = $adb->fetchByAssoc($res);
                    $default["id"] = $row["platformintegrationlinkid"];
                }
            }
            $moduleName = "PlatformIntegrationLinks";
            if (empty($default["id"])) {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            } else {
                $recordModel = Vtiger_Record_Model::getInstanceById($default["id"], $moduleName);
                $recordModel->set("mode", "edit");
            }
            $default = $this->updateCustomInfoBeforeSaving($default);
            foreach ($default as $k => $v) {
                if ($k != "id" && !empty($v)) {
                    $recordModel->set($k, $v);
                }
            }
            $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
            $recordModel->save();
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateCustomInfoBeforeSaving($default)
    {
        return $default;
    }
    public function getQueryByPlatformModule($platformModule, $platformId = "", $isActive = true, $otherConditions = "", $isCount = false)
    {
        try {
            $code = "Succeed";
            $error = "";
            $res = $this->getInfoOfPlatformModule($platformModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $row = $res["result"];
            $queries = array();
            $query = "SELECT * FROM ";
            if ($isCount) {
                $query = "SELECT COUNT(*) FROM ";
            }
            $platform_module_table = $row["platform_module_table"];
            $query .= " " . $platform_module_table . " WHERE Id!='0'";
            if ($row["has_active_field"] == "1") {
                if ($isActive) {
                    $query .= " AND Active=True";
                } else {
                    $query .= " AND Active=False";
                }
            }
            if (empty($platformId)) {
                $from_date = $row["from_date"];
                if (!empty($from_date)) {
                    $query .= " AND MetaData.CreateTime >= '" . $from_date . "'";
                }
            }
            $query .= " " . $otherConditions;
            if (!empty($row["conditions"])) {
                $conditions = decode_html($row["conditions"]);
                $conditions = json_decode($conditions);
                foreach ($conditions as $condition) {
                    if (!empty($condition)) {
                        $query2 = (string) $query . " AND " . $condition;
                        if (!empty($platformId)) {
                            $query2 .= " AND Id='" . $platformId . "'";
                        }
                        if ($isCount) {
                            $queries[] = $query2;
                        } else {
                            $queries[] = $query2 . "  ORDER BY Id";
                        }
                    }
                }
            } else {
                if (!empty($platformId)) {
                    $query .= " AND Id='" . $platformId . "'";
                }
                if ($isCount) {
                    $queries[] = $query;
                } else {
                    $queries[] = $query . "  ORDER BY Id";
                }
            }
            return array("code" => $code, "error" => $error, "sql" => $queries);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function convertToVtigerValue($val, $dataType = "")
    {
        if ($dataType == "boolean") {
            if ("true" == strtolower($val)) {
                $val = "on";
            } else {
                $val = "off";
            }
        }
        return $val;
    }
    public function getFieldsOfPlatformModule($platformModule)
    {
        global $adb;
        $sql = "SELECT * FROM platformintegration_modules_fields WHERE platform_module=?";
        $res = $adb->pquery($sql, array($platformModule));
        $ret = array();
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    public function getFielForConfig($vtModule)
    {
        try {
            global $adb;
            $data = array();
            $code = "Succeed";
            $error = "";
            $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
            $qbo_fields = array();
            $qboModule = "";
            $sql = "SELECT vmf.*\r\n                    FROM platformintegration_modules_fields vmf\r\n                    INNER JOIN platformintegration_modules vm on vm.platform_module = vmf.platform_module\r\n                    WHERE vm.vt_module = ?";
            $res = $adb->pquery($sql, array($vtModule));
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $qbo_fields[] = $row;
                    if (empty($qboModule)) {
                        $qboModule = $row["platform_module"];
                    }
                }
            }
            $data["vt_fields"] = $vt_fields;
            $data["platform_fields"] = $qbo_fields;
            $data["vtigerModule"] = $vtModule;
            $data["platformModule"] = $qboModule;
            $data["mappedFields"] = $this->getMappedFields($qboModule, $vtModule);
            return array("code" => $code, "error" => $error, "data" => $data);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getAllTab()
    {
        try {
            global $adb;
            $data = array();
            $code = "Succeed";
            $error = "";
            $sql = "SELECT * FROM platformintegration_modules ORDER BY tab_seq, seq_in_tab";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                $currentTab = "";
                while ($row = $adb->fetchByAssoc($res)) {
                    $tab = $row["tab"];
                    $vt_module = $row["vt_module"];
                    if ($currentTab != $tab) {
                        if (empty($data[$tab])) {
                            $data[$tab] = array();
                        }
                        $data[$tab][$vt_module] = $this->getFielForConfig($vt_module)["data"];
                    }
                }
            }
            return array("code" => $code, "error" => $error, "data" => $data);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function insertIntoPlatformIntegrationLogs($data)
    {
        try {
            $default = array("platform_module" => "", "platform_id" => NULL, "vt_module" => "", "vt_id" => NULL, "sync_type" => "Platform2VT", "action_type" => "insert", "platformintegrationlog_status" => "Successful", "message" => "", "sent_data" => "", "received_data" => "");
            foreach ($default as $k => $v) {
                if (!empty($data[$k])) {
                    $default[$k] = $data[$k];
                }
            }
            $moduleName = "PlatformIntegrationLogs";
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            foreach ($default as $k => $v) {
                if ($k == "platformintegrationlog_status") {
                    $v = vtranslate($v, "PlatformIntegration");
                }
                $recordModel->set($k, $v);
            }
            $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
            $recordModel->save();
        } catch (Exception $ex) {
        }
    }
    public function checkValidMappingFields($mappingFields, $vtModule, $qboModule)
    {
        try {
            $vtFields = array();
            $qboFields = array();
            $mappedFields = array();
            $ret = array();
            if ($vtModule == "Contacts") {
                $mappedFields = $this->getMappedFields("Company", "Accounts");
            } else {
                if ($vtModule == "Accounts") {
                    $mappedFields = $this->getMappedFields("Customer", "Contacts");
                }
            }
            foreach ($mappingFields as $mappingField) {
                list($vt_field, $qb_field) = split(",", $mappingField);
                $item = array($vt_field, $qb_field);
                if (in_array($vt_field, $vtFields) && !in_array($item, $ret)) {
                    $ret[] = $item;
                }
                if (in_array($qb_field, $qboFields) && !in_array($item, $ret)) {
                    $ret[] = $item;
                }
                $vtFields[] = $vt_field;
                $qboFields[] = $qb_field;
                if (!empty($mappedFields)) {
                    foreach ($mappedFields as $mappedField) {
                        if ($mappedField["platform_field"] == $qb_field && $mappedField["platform_field"] != "DisplayName" && !in_array($item, $ret)) {
                            $ret[] = $item;
                        }
                    }
                }
            }
            if (empty($ret)) {
                return true;
            }
            return $ret;
        } catch (Exception $ex) {
            return array();
        }
    }
    public function checkValidLogicMappingFields($mappingFields, $vtModule, $qboModule)
    {
        try {
            global $adb;
            $vtFields = array();
            $qboFields = array();
            $ret = array();
            foreach ($mappingFields as $mappingField) {
                list($vt_field, $qb_field) = split(",", $mappingField);
                $vtFields[] = $vt_field;
                $qboFields[] = $qb_field;
            }
            $sql = "SELECT VF.fieldname, VF.uitype, VF.typeofdata\r\n                        FROM vtiger_field VF\r\n                        INNER JOIN vtiger_tab VT ON VF.tabid=VT.tabid\r\n                        WHERE VT.`name`='" . $vtModule . "'\r\n                        AND fieldname IN (" . generateQuestionMarks($vtFields) . ")";
            $res = $adb->pquery($sql, $vtFields);
            $vtFields = array();
            if ($adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $vtFields[$row["fieldname"]] = array("uitype" => $row["uitype"], "typeofdata" => $row["typeofdata"]);
                }
            }
            $sql = "SELECT platform_field, is_picklist, data_type, non_editable\r\n                        FROM platformintegration_modules_fields\r\n                        WHERE platform_module='" . $qboModule . "' AND platform_field IN (" . generateQuestionMarks($qboFields) . ")";
            $res = $adb->pquery($sql, $qboFields);
            $qboFields = array();
            if ($adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $qboFields[$row["qb_field"]] = array("is_picklist" => $row["is_picklist"], "data_type" => $row["data_type"], "non_editable" => $row["non_editable"]);
                }
            }
            foreach ($mappingFields as $mappingField) {
                list($vt_field, $qb_field) = split(",", $mappingField);
                if ($this->checkValidMapFieldType($vtFields[$vt_field], $qboFields[$qb_field]) == false) {
                    $ret[] = array($vt_field, $qb_field);
                }
            }
            if (empty($ret)) {
                return true;
            }
            return $ret;
        } catch (Exception $ex) {
            return array();
        }
    }
    public function checkValidMapFieldType($vtField, $qboField)
    {
        if ($qboField["is_picklist"] == "1" && in_array($vtField["uitype"], array("15", "16", "33")) == false) {
            return false;
        }
        if ($qboField["non_editable"] != "1") {
            $uitype = intval($vtField["uitype"]);
            $typeofdata = $vtField["typeofdata"];
            if ($qboField["data_type"] == "email") {
                if (in_array($uitype, array(13)) == false) {
                    return false;
                }
            } else {
                if ($qboField["data_type"] == "text" || $qboField["data_type"] == "CustomField") {
                    if (in_array($uitype, array(1, 105, 106, 11, 12, 17, 19, 2, 20, 21, 22, 24, 255, 26, 27, 28, 3, 55, 61, 69, 8, 83, 85)) == false) {
                        return false;
                    }
                } else {
                    if ($qboField["data_type"] == "number") {
                        if (in_array($uitype, array(7, 9, 71, 117, 25, 72)) == false && strpos($typeofdata, "NN") === false && strpos($typeofdata, "I") === false) {
                            return false;
                        }
                    } else {
                        if ($qboField["data_type"] == "date" && in_array($uitype, array(5, 6, 23)) == false) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    public function removeBlankFields($changedData, $mappedFields)
    {
        $qbModule = $mappedFields[0]["platform_module"];
        $fields = $this->getFieldsOfPlatformModule($qbModule);
        $fieldsTypes = array();
        foreach ($fields as $field) {
            $fieldsTypes[$field["platform_field"]] = $field["data_type"];
        }
        foreach ($changedData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (empty($v)) {
                        unset($changedData[$key][$k]);
                    }
                }
            } else {
                if ($fieldsTypes[$key] != "boolean") {
                    if (empty($value)) {
                        unset($changedData[$key]);
                    } else {
                        if ($fieldsTypes[$key] == "number" || $fieldsTypes[$key] == "reference") {
                            $value = floatval($value);
                            if ($value == 0) {
                                unset($changedData[$key]);
                            }
                        }
                    }
                } else {
                    if (empty($value)) {
                        $changedData[$key] = "false";
                    } else {
                        $changedData[$key] = "true";
                    }
                }
            }
        }
        return $changedData;
    }
    public function getCurrencyId()
    {
        global $adb;
        $currency_id = "";
        $current_user = Users_Record_Model::getCurrentUserModel();
        if (!empty($current_user)) {
            $currency_id = $current_user->get("currency_id");
        }
        if (empty($currency_id)) {
            $sql = "SELECT id FROM vtiger_currency_info WHERE deleted=0 AND currency_status='Active' ORDER BY id LIMIT 0, 1";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $currency_id = $row["id"];
                }
            }
        }
        $currency_id = floatval($currency_id);
        if ($currency_id == 0) {
            $currency_id = 1;
        }
        return $currency_id;
    }
    public function removeUnusedFields($vt_fields)
    {
        foreach ($vt_fields as $k => $v) {
            $vt_fields[$k]->set("is_show_on_config", 1);
        }
        foreach ($this->unusedFields as $unusedField) {
            if (is_object($vt_fields[$unusedField])) {
                $vt_fields[$unusedField]->set("is_show_on_config", 0);
            }
        }
        return $vt_fields;
    }
    public function getPlatformGroupId()
    {
        if (!empty($this->quickbooksGroupId)) {
            return $this->quickbooksGroupId;
        }
        global $adb;
        $platformGroupId = "";
        $groupName = self::$groupName;
        $sql = "SELECT groupid FROM vtiger_groups WHERE groupname=? LIMIT 0, 1";
        $res = $adb->pquery($sql, array($groupName));
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $platformGroupId = $row["groupid"];
            }
            $this->platformGroupId = $platformGroupId;
        }
        return $this->platformGroupId;
    }
    public function getFirstAdminUserId()
    {
        if (!empty($this->firstAdminUserId)) {
            return $this->firstAdminUserId;
        }
        global $adb;
        $firstAdminUserId = "";
        $sql = "SELECT id FROM vtiger_users WHERE is_admin='on' AND `status`='Active' ORDER BY id LIMIT 0, 1";
        $res = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $firstAdminUserId = $row["id"];
            }
            $this->firstAdminUserId = $firstAdminUserId;
        }
        return $this->firstAdminUserId;
    }
    public function checkAllowdField($field)
    {
        if (!in_array((int) $field->get("displaytype"), array(1, 5)) || $field->isReadOnly() == true || $field->get("uitype") == 4 || $field->get("presence") == 1) {
            return false;
        }
        return true;
    }
    public function checkValidFieldsBeforeSyncing($platformModule, $vtModule)
    {
        $mappedFields = $this->getMappedFields($platformModule, $vtModule);
        foreach ($mappedFields as $mappedField) {
            if ($mappedField["vt_presence"] != "0" && $mappedField["vt_presence"] != "2" && $mappedField["non_editable"] != "1") {
                return false;
            }
        }
        return true;
    }
    public function getAllMappedTaxes()
    {
        global $adb;
        $sql = "SELECT t1.platform_tax_id, t3.taxname, t2.taxcode_id\r\n                FROM platformintegration_mapping_tax t1\r\n                INNER JOIN platformintegration_taxcode_sales t2 ON t1.platform_tax_id = t2.taxrate_id\r\n                LEFT JOIN vtiger_inventorytaxinfo t3 ON t1.vt_tax_id = t3.taxid";
        $res = $adb->pquery($sql, array());
        $mappedTaxes = array();
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $mappedTaxes[] = $row;
            }
        }
        return $mappedTaxes;
    }
    public function getFullRedirectUri()
    {
        $redirectURI = $this->redirectURI;
        $url = $this->urlToGetId;
        global $site_URL;
        $url .= $site_URL;
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        $jsonData = json_decode(curl_exec($curlSession));
        curl_close($curlSession);
        $id = $jsonData->id;
        $redirectURI .= $id;
        return $redirectURI;
    }
    public function checkValidForPhpVersion()
    {
        $current_php_servion = phpversion();
        $phpVersionRequired = $this->phpVersionRequired;
        if (version_compare($current_php_servion, $phpVersionRequired, "<")) {
            return false;
        }
        return true;
    }
    public function createCustomFieldsForAUS()
    {
        $module = "Products";
        $focus = CRMEntity::getInstance($module);
        $table = $focus->customFieldTable[0];
        $blocks = array("Platform");
        $fields = array("Platform" => array("cf_tax_on_platform_aus" => array("label" => "Tax on Platform(Only AUS)", "uitype" => 16, "displaytype" => 1, "picklistvalues" => array("Out of Scope", "GST free", "GST"), "defaultvalue" => "Out of Scope")));
        $this->createCustomField($blocks, $fields, $module, $table);
        $module = "Services";
        $focus = CRMEntity::getInstance($module);
        $table = $focus->customFieldTable[0];
        $this->createCustomField($blocks, $fields, $module, $table);
        $module = "Invoice";
        $focus = CRMEntity::getInstance($module);
        $table = $focus->customFieldTable[0];
        $blocks = array("Platform");
        $fields = array("Platform" => array("cf_amounts_are_in_aus" => array("label" => "Amounts are(Only AUS)", "uitype" => 16, "displaytype" => 3, "picklistvalues" => array("TaxInclusive", "TaxExcluded", "NotApplicable"), "defaultvalue" => "TaxInclusive")));
        $this->createCustomField($blocks, $fields, $module, $table);
        $module = "Invoice";
        $focus = CRMEntity::getInstance($module);
        $table = "vtiger_inventoryproductrel";
        $blocks = array("LBL_ITEM_DETAILS");
        $fields = array("LBL_ITEM_DETAILS" => array("cf_taxcode_in_aus" => array("label" => "Taxcode(Only AUS)", "uitype" => 1, "displaytype" => 5)));
        $this->createCustomField($blocks, $fields, $module, $table);
    }
    public function createCustomField($blocks, $fields, $module, $table)
    {
        $vmodule = Vtiger_Module::getInstance($module);
        if ($vmodule) {
            foreach ($blocks as $blcks) {
                $block = Vtiger_Block::getInstance($blcks, $vmodule);
                if (!$block && $blcks) {
                    $block = new Vtiger_Block();
                    $block->label = $blcks;
                    $block->__create($vmodule);
                }
                $adb = PearDatabase::getInstance();
                $sql_1 = "SELECT sequence FROM `vtiger_field` WHERE block = '" . $block->id . "' ORDER BY sequence DESC LIMIT 0,1";
                $res_1 = $adb->query($sql_1);
                $sequence = 0;
                if ($adb->num_rows($res_1)) {
                    $sequence = $adb->query_result($res_1, "sequence", 0);
                }
                foreach ($fields[$blcks] as $name => $a_field) {
                    $field = Vtiger_Field::getInstance($name, $vmodule);
                    if (!$field && $name && $table) {
                        $sequence++;
                        $field = new Vtiger_Field();
                        $field->name = $name;
                        $field->label = $a_field["label"];
                        $field->table = $table;
                        $field->uitype = $a_field["uitype"];
                        $field->displaytype = $a_field["displaytype"];
                        if ($a_field["uitype"] == 15 || $a_field["uitype"] == 16 || $a_field["uitype"] == "33") {
                            $field->setPicklistValues($a_field["picklistvalues"]);
                            if (!empty($a_field["defaultvalue"])) {
                                $field->defaultvalue = $a_field["defaultvalue"];
                            }
                        }
                        if ($a_field["uitype"] == 70) {
                            $field->typeofdata = $a_field["typeofdata"];
                            $field->columntype = $a_field["columntype"];
                        }
                        $field->sequence = $sequence;
                        $field->generatedtype = 2;
                        $field->__create($block);
                        if ($a_field["uitype"] == 10) {
                            $field->setRelatedModules(array($a_field["related_to_module"]));
                        }
                    }
                }
            }
        }
    }
}

?>