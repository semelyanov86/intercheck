<?php

class PlatformIntegration_Module_Model extends PlatformIntegration_Base_Model
{
    public function getSettingLinks()
    {
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Settings", "linkurl" => "index.php?module=PlatformIntegration&parent=Settings&view=Settings", "linkicon" => "");
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "License & Upgrade", "linkurl" => "index.php?module=PlatformIntegration&parent=Settings&view=Upgrade", "linkicon" => "");
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=PlatformIntegration&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
    public function getFielForConfig($vtModule)
    {
        try {
            global $adb;
            $data = array();
            $code = "Succeed";
            $error = "";
            $moduleModel = Vtiger_Module_Model::getInstance($vtModule);
            if (empty($moduleModel)) {
                return array("code" => "Failed", "error" => vtranslate("LBL_CANNOT_FIND_THIS_VTIGER_MODULE", "PlatformIntegration"));
            }
            $vt_fields = $moduleModel->getFields();
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
            foreach ($vt_fields as $k => $v) {
                if ($vtModule == "Invoice" && $v->get("table") == "vtiger_inventoryproductrel") {
                    unset($vt_fields[$k]);
                    continue;
                }
                if (!$this->checkAllowdField($v)) {
                    if ($vtModule == "Contacts" && $k == "salutationtype") {
                        continue;
                    }
                    unset($vt_fields[$k]);
                }
            }
            $moduleName = $this->moduleName;
            $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
            if (class_exists($vtModuleClass)) {
                $obj = new $vtModuleClass($moduleName);
            } else {
                $obj = $this;
            }
            $vt_fields = $obj->removeUnusedFields($vt_fields);
            $data["vt_fields"] = $vt_fields;
            $data["platform_fields"] = $qbo_fields;
            $data["vtigerModule"] = $vtModule;
            $data["platformModule"] = $qboModule;
            $mappedFields = $this->getMappedFields($qboModule, $vtModule);
            if ($vtModule == "Accounts") {
                foreach ($mappedFields as $k => $v) {
                    if ($v["platform_field"] == "DisplayName") {
                        $data["Map_DisplayName"] = $v["vt_field"];
                        unset($mappedFields[$k]);
                        break;
                    }
                }
            }
            $data["mappedFields"] = $mappedFields;
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
            $sql = "SELECT VM.*, VT.tabid AS vtigertabid\r\n                    FROM platformintegration_modules VM\r\n                    LEFT JOIN vtiger_tab VT ON VM.vt_module=VT.`name`\r\n                    ORDER BY VM.tab_seq, VM.seq_in_tab";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $tab = $row["tab"];
                    $vt_module = $row["vt_module"];
                    $allow_sync = $row["allow_sync"];
                    $sync_scope = $row["sync_scope"];
                    $has_from_date = $row["has_from_date"];
                    $from_date = $row["from_date"];
                    $tooltip = $row["tooltip"];
                    $vt_tab_id = $row["vtigertabid"];
                    $error_missing_module = "";
                    if (empty($vt_tab_id)) {
                        $vt_tab_id = "";
                        $error_missing_module = vtranslate("LBL_CANNOT_FIND_THIS_VTIGER_MODULE_" . strtoupper($vt_module), "PlatformIntegration");
                    }
                    $otherInfo = array();
                    $otherInfo["allow_sync"] = $allow_sync;
                    $otherInfo["sync_scope"] = $sync_scope;
                    $otherInfo["has_from_date"] = $has_from_date;
                    $otherInfo["from_date"] = $from_date;
                    $otherInfo["tooltip"] = $tooltip;
                    $otherInfo["vt_tab_id"] = $vt_tab_id;
                    $otherInfo["error_missing_module"] = $error_missing_module;
                    if (empty($data[$tab])) {
                        $data[$tab] = array();
                    }
                    if (empty($data[$tab]["sync_scope"])) {
                        $data[$tab]["OtherInfo"] = $otherInfo;
                    }
                    if (!empty($error_missing_module)) {
                        $data[$tab]["OtherInfo"]["tooltip_requires"] = vtranslate("LBL_TOOLTIP_INFO_" . strtoupper($vt_module) . "_REQUIRES", "PlatformIntegration");
                        $data[$tab][$vt_module] = array("OtherInfo" => $otherInfo);
                    } else {
                        $data[$tab][$vt_module] = $this->getFielForConfig($vt_module)["data"];
                        $data[$tab][$vt_module]["OtherInfo"] = $otherInfo;
                    }
                }
            }
            return array("code" => $code, "error" => $error, "data" => $data);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkEditableField($field)
    {
        if ($field->isReadOnly()) {
            return false;
        }
        $displayType = $field->getDisplayType();
        $uiType = $field->getUIType();
        if ($displayType != "1" && $uiType != "55") {
            return false;
        }
        if ($uiType == "4") {
            return false;
        }
        $presence = $field->getPresence();
        if (in_array($presence, array(1, 3)) || $displayType == "4") {
            return false;
        }
        if (strcasecmp($field->getFieldDataType(), "autogenerated") === 0 || strcasecmp($field->getFieldDataType(), "id") === 0) {
            return false;
        }
        return true;
    }
}

?>