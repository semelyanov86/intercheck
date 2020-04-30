<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_PlatformItem_Model extends PlatformIntegration_Engine_Model
{
    public function __construct()
    {
        $otherUnusedFields = array("taxclass", "assigned_user_id", "imagename", "vendor_id");
        $this->unusedFields = array_merge($this->unusedFields, $otherUnusedFields);
    }
    public function preSaveModel($recordModel, $record)
    {
        try {
            global $adb;
            $crmid = $recordModel->getId();
            $qboVersion = $this->getPlatformVersion();
            if ($qboVersion == "AUS") {
                $SalesTaxCodeRef = $record->SalesTaxCodeRef;
                $sql = "SELECT `name` FROM platformintegration_taxcode WHERE taxcode_id=? LIMIT 0, 1";
                $rs = $adb->pquery($sql, array($SalesTaxCodeRef));
                if (0 < $adb->num_rows($rs)) {
                    $name = $adb->query_result($rs, 0, "name");
                    $recordModel->set("cf_tax_on_qbo_aus", $name);
                }
            }
            return parent::preSaveModel($recordModel, $record);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function postSaveModel($recordModel, $record, $mappedFields, $mappedInfo = false, $isUpdate = false)
    {
        try {
            global $adb;
            $crmid = $recordModel->getId();
            $this->repopulateTaxes($crmid, $record);
            return parent::postSaveModel($recordModel, $record, $mappedFields, $mappedInfo, $isUpdate);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function repopulateTaxes($crmid, $record = false)
    {
        $qboVersion = $this->getPlatformVersion();
        if ($qboVersion == "US") {
            return $this->repopulateTaxesCommon($crmid, $record);
        }
        if ($qboVersion == "AUS") {
            return $this->repopulateTaxesAUS($crmid, $record);
        }
    }
    public function repopulateTaxesAUS($crmid, $record)
    {
        global $adb;
        $sql = "DELETE FROM vtiger_producttaxrel WHERE productid = ?";
        $adb->pquery($sql, array($crmid));
        if (!empty($record)) {
            $SalesTaxCodeRef = $record->SalesTaxCodeRef;
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid);
            $sql = "DELETE FROM vtiger_producttaxrel WHERE productid = ? AND taxid IN ( SELECT taxid FROM vtiger_inventorytaxinfo WHERE deleted=1 )";
            $adb->pquery($sql, array($crmid));
            $SalesTaxCodeRef = $record->SalesTaxCodeRef;
            if (!empty($SalesTaxCodeRef)) {
                $sql1 = "SELECT t4.taxid, t4.percentage\r\n                    FROM `vteqbo_taxcode` t1\r\n                    INNER JOIN vteqbo_taxcode_sales t2 ON t2.taxcode_id = t1.taxcode_id\r\n                    INNER JOIN vteqbo_mapping_tax t3 ON t3.qb_tax_id = t2.taxrate_id\r\n                    INNER JOIN vtiger_inventorytaxinfo t4 ON t4.taxid = t3.vt_tax_id\r\n                    WHERE t1.taxcode_id=?";
                $rs1 = $adb->pquery($sql1, array($SalesTaxCodeRef));
                if (0 < $adb->num_rows($rs1)) {
                    while ($row = $adb->fetchByAssoc($rs1)) {
                        $taxid = $row["taxid"];
                        $percentage = $row["percentage"];
                        $sql2 = "INSERT INTO vtiger_producttaxrel(productid, taxid, taxpercentage) VALUES(?, ?, ?)";
                        $params = array($crmid, $taxid, $percentage);
                        $adb->pquery($sql2, $params);
                    }
                }
            }
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid);
            $cf_tax_on_qbo_aus = $recordModel->get("cf_tax_on_qbo_aus");
            if (!empty($cf_tax_on_qbo_aus)) {
                $sql1 = "SELECT t4.taxid, t4.percentage\r\n                    FROM `vteqbo_taxcode` t1\r\n                    INNER JOIN vteqbo_taxcode_sales t2 ON t2.taxcode_id = t1.taxcode_id\r\n                    INNER JOIN vteqbo_mapping_tax t3 ON t3.qb_tax_id = t2.taxrate_id\r\n                    INNER JOIN vtiger_inventorytaxinfo t4 ON t4.taxid = t3.vt_tax_id\r\n                    WHERE t1.name=?";
                $rs1 = $adb->pquery($sql1, array($cf_tax_on_qbo_aus));
                if (0 < $adb->num_rows($rs1)) {
                    while ($row = $adb->fetchByAssoc($rs1)) {
                        $taxid = $row["taxid"];
                        $percentage = $row["percentage"];
                        $sql2 = "INSERT INTO vtiger_producttaxrel(productid, taxid, taxpercentage) VALUES(?, ?, ?)";
                        $params = array($crmid, $taxid, $percentage);
                        $adb->pquery($sql2, $params);
                    }
                }
            }
        }
    }
    public function repopulateTaxesCommon($crmid, $record)
    {
        global $adb;
        $recordModel = Vtiger_Record_Model::getInstanceById($crmid);
        if ($recordModel->get("cf_is_taxable") == "on" || $recordModel->get("cf_is_taxable") == "1") {
            $sql = "DELETE FROM vtiger_producttaxrel WHERE productid = ? AND taxid IN ( SELECT taxid FROM vtiger_inventorytaxinfo WHERE deleted=1 )";
            $adb->pquery($sql, array($crmid));
            $sql = "SELECT VI.*, VP.taxpercentage as current_percentage\r\n                        FROM vtiger_inventorytaxinfo VI\r\n                        LEFT JOIN vtiger_producttaxrel VP ON VI.taxid = VP.taxid AND VP.productid = ?\r\n                        WHERE (ISNULL(VP.taxpercentage) OR VI.percentage != VP.taxpercentage) AND VI.deleted=0";
            $res = $adb->pquery($sql, array($crmid));
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    if (empty($row["current_percentage"])) {
                        $sql2 = "INSERT INTO vtiger_producttaxrel(productid, taxid, taxpercentage) VALUES(?, ?, ?)";
                        $params = array($crmid, $row["taxid"], $row["percentage"]);
                        $adb->pquery($sql2, $params);
                    } else {
                        $sql2 = "UPDATE vtiger_producttaxrel SET taxpercentage=? WHERE productid=? AND taxid=?";
                        $params = array($row["percentage"], $crmid, $row["taxid"]);
                        $adb->pquery($sql2, $params);
                    }
                }
            }
        } else {
            $sql = "DELETE FROM vtiger_producttaxrel WHERE productid = ?";
            $adb->pquery($sql, array($crmid));
        }
    }
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $crmid = $recordModel->getId();
            $qboVersion = $this->getPlatformVersion();
            if ($qboVersion == "AUS") {
                $res = $this->updateTaxForPlatformRecord_AUS($recordModel, $changedData, $mappedFields);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $changedData = $res["data"];
            }
            if ($changedData["Type"] == "NonInventory") {
                unset($changedData["QtyOnHand"]);
                $changedData["TrackQtyOnHand"] = "false";
            }
            if (empty($changedData["ParentRef"])) {
                unset($changedData["ParentRef"]);
                $changedData["SubItem"] = "false";
            } else {
                $changedData["SubItem"] = "true";
            }
            if (empty($changedData["QtyOnHand"])) {
                unset($changedData["QtyOnHand"]);
                $changedData["TrackQtyOnHand"] = "false";
            } else {
                $changedData["TrackQtyOnHand"] = "true";
            }
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateTaxForPlatformRecord_AUS($recordModel, $changedData, $mappedFields)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $cf_tax_on_qbo_aus = $recordModel->get("cf_tax_on_qbo_aus");
            $sql = "SELECT taxcode_id FROM vteqbo_taxcode WHERE name=?";
            $rs = $adb->pquery($sql, array($cf_tax_on_qbo_aus));
            $taxCode = "";
            if (0 < $adb->num_rows($rs)) {
                $taxCode = $adb->query_result($rs, 0, "taxcode_id");
            }
            $changedData["SalesTaxCodeRef"] = $taxCode;
            return array("code" => $code, "error" => $error, "data" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkMergerExistedRecordOnVtiger($record, $mappedFields, $vtModule = "", $vt_fields = false)
    {
        try {
            $qb_fields = array("Name");
            $vtId = $this->checkMergerExistedRecordOnVtigerByFields($record, $mappedFields, $vtModule, $qb_fields);
            if ($vtId != false) {
                return $vtId;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function checkMergerExistedRecordOnPlatform($recordModel, $mappedFields, $qboModule, $vtModule, $vt_fields)
    {
        try {
            $qb_fields = array("Name");
            $res = $this->checkMergerExistedRecordOnPlatformByFields($recordModel, $mappedFields, $qboModule, $vtModule, $qb_fields);
            return $res;
        } catch (Exception $ex) {
            return false;
        }
    }
}

?>