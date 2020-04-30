<?php

require_once "modules/PlatformIntegration/models/Base.php";
require_once "modules/PlatformIntegration/helpers/vendor/autoload.php";
class PlatformIntegration_TaxRate_Model extends PlatformIntegration_Engine_Model
{
    public function syncAllTaxRates($pDatasource)
    {
        try {
            $error = "";
            $code = "Succeed";
            $res = $this->syncTaxCodeToVtiger();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            if ($pDatasource == "vtiger") {
                $res = $this->sysTaxToQuickbooks($pDatasource);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $res = $this->syncTaxToVtiger($pDatasource);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
            } else {
                $res = $this->syncTaxToVtiger($pDatasource);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $res = $this->sysTaxToQuickbooks($pDatasource);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncTaxCodeToVtiger()
    {
        try {
            global $adb;
            $error = "";
            $code = "Succeed";
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $qboTaxCodes = $dataService->Query("select * from taxcode where active=true");
            $e = $dataService->getLastError();
            if ($e != NULL) {
                return array("code" => "Failed", "error" => $e->getResponseBody());
            }
            foreach ($qboTaxCodes as $qboTaxCode) {
                $taxcode_id = $qboTaxCode->Id;
                $Name = $qboTaxCode->Name;
                $Description = $qboTaxCode->Description;
                $SalesTaxRateList = $qboTaxCode->SalesTaxRateList;
                $sales_taxrate_list = array();
                if (!empty($SalesTaxRateList)) {
                    $SalesTaxRateList = $SalesTaxRateList->TaxRateDetail;
                }
                if (!empty($SalesTaxRateList)) {
                    if (is_array($SalesTaxRateList)) {
                        foreach ($SalesTaxRateList as $taxRate) {
                            $sales_taxrate_list[] = $taxRate->TaxRateRef;
                        }
                    } else {
                        $sales_taxrate_list[] = $SalesTaxRateList->TaxRateRef;
                    }
                }
                $PurchaseTaxRateList = $qboTaxCode->PurchaseTaxRateList;
                $purchase_taxrate_list = array();
                if (!empty($PurchaseTaxRateList)) {
                    $PurchaseTaxRateList = $PurchaseTaxRateList->TaxRateDetail;
                }
                if (!empty($PurchaseTaxRateList)) {
                    if (is_array($PurchaseTaxRateList)) {
                        foreach ($PurchaseTaxRateList as $taxRate) {
                            $purchase_taxrate_list[] = $taxRate->TaxRateRef;
                        }
                    } else {
                        $purchase_taxrate_list[] = $PurchaseTaxRateList->TaxRateRef;
                    }
                }
                $sql = "SELECT id FROM platformintegration_taxcode WHERE taxcode_id=? LIMIT 0, 1";
                $rs = $adb->pquery($sql, array($taxcode_id));
                if (0 < $adb->num_rows($rs)) {
                    $id = $adb->query_result($rs, 0, "id");
                    $sql = "UPDATE platformintegration_taxcode SET taxcode_id=?, name=?, description=? WHERE id=?";
                    $params = array($taxcode_id, $Name, $Description, $id);
                    $adb->pquery($sql, $params);
                } else {
                    $sql = "INSERT INTO platformintegration_taxcode(taxcode_id, name, description) VALUES(?, ?, ?)";
                    $params = array($taxcode_id, $Name, $Description);
                    $adb->pquery($sql, $params);
                }
                $sql2a = "DELETE FROM platformintegration_taxcode_purchase WHERE taxcode_id=?";
                $adb->pquery($sql2a, array($taxcode_id));
                $sql2b = "INSERT INTO platformintegration_taxcode_purchase(taxcode_id, taxrate_id) VALUES(?, ?)";
                foreach ($purchase_taxrate_list as $taxrate) {
                    $adb->pquery($sql2b, array($taxcode_id, $taxrate));
                }
                $sql3a = "DELETE FROM platformintegration_taxcode_sales WHERE taxcode_id=?";
                $adb->pquery($sql3a, array($taxcode_id));
                $sql3b = "INSERT INTO platformintegration_taxcode_sales(taxcode_id, taxrate_id) VALUES(?, ?)";
                foreach ($sales_taxrate_list as $taxrate) {
                    $adb->pquery($sql3b, array($taxcode_id, $taxrate));
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncTaxToVtiger($pDatasource)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $vtiger_current_version;
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $qboTaxRates = $dataService->Query("SELECT * FROM TaxRate WHERE TaxReturnLineRef != '1' ORDER  BY Id");
            $e = $dataService->getLastError();
            if ($e != NULL) {
                return array("code" => "Failed", "error" => $e->getResponseBody());
            }
            $type = "0";
            foreach ($qboTaxRates as $qboTaxRate) {
                if ($this->checkIsTaxOnTransaction($qboTaxRate)) {
                    if (version_compare($vtiger_current_version, "7.0.0", "<")) {
                        $this->insertOrUpdateTaxOnV6($qboTaxRate, $type, $pDatasource);
                    } else {
                        $this->insertOrUpdateTaxOnV7($qboTaxRate, $type, $pDatasource);
                    }
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkIsTaxOnTransaction($qboTaxRate)
    {
        $res = $this->getPlatformApi();
        $qboApi = $res["result"];
        $qboVersion = $this->getPlatformVersion();
        $displayType = $qboTaxRate->DisplayType;
        if ($displayType == "ReadOnly" || empty($displayType)) {
            if ($qboVersion == "US") {
                if ($qboTaxRate->SpecialTaxType != "ZERO_RATE") {
                    return true;
                }
                return false;
            }
            if ($qboVersion == "AUS") {
                $name = $qboTaxRate->Name;
                $gstTaxes = $this->taxesForAUS;
                if (in_array($name, $gstTaxes)) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }
    public function insertOrUpdateTaxOnV6($qboTaxRate, $type, $pDatasource)
    {
        global $adb;
        $taxRates = $this->getTaxRates(true);
        $currentTax = $this->getTaxRateByPlatformId($taxRates, $qboTaxRate->Id);
        $currentValue = json_decode($currentTax["latest_value"]);
        $taxLabel = html_entity_decode($qboTaxRate->Name, ENT_QUOTES);
        $latest_update = $qboTaxRate->MetaData->LastUpdatedTime;
        $taxRateValue = $qboTaxRate->RateValue;
        $isInsert = true;
        if (empty($currentTax)) {
            $currentTax = $this->getTaxRateByPlatformTaxName($taxRates, $taxLabel);
            if (empty($currentTax)) {
                $taxRecordModel = new Settings_Vtiger_TaxRecord_Model();
            } else {
                $taxId = $currentTax["taxid"];
                $taxRecordModel = Settings_Vtiger_TaxRecord_Model::getInstanceById($taxId, $type);
                if ($pDatasource == "vtiger") {
                    if ($currentTax["taxlabel"] != $currentValue["taxlabel"]) {
                        $taxLabel = $currentTax["taxlabel"];
                    }
                    if ($currentTax["percentage"] != $currentValue["percentage"]) {
                        $taxRateValue = $currentTax["percentage"];
                    }
                }
            }
        } else {
            $isInsert = false;
            if ($currentTax["latest_update"] == $latest_update) {
                return true;
            }
            $taxId = $currentTax["taxid"];
            $taxRecordModel = Settings_Vtiger_TaxRecord_Model::getInstanceById($taxId, $type);
            if ($pDatasource == "vtiger") {
                if ($currentTax["taxlabel"] != $currentValue["taxlabel"]) {
                    $taxLabel = $currentTax["taxlabel"];
                }
                if ($currentTax["percentage"] != $currentValue["percentage"]) {
                    $taxRateValue = $currentTax["percentage"];
                }
            }
        }
        $taxLabel = html_entity_decode($taxLabel, ENT_QUOTES);
        $taxRecordModel->set("taxlabel", $taxLabel);
        $taxRecordModel->set("percentage", $taxRateValue);
        if ($qboTaxRate->Active == "true") {
            $taxRecordModel->set("deleted", 0);
        } else {
            $taxRecordModel->set("deleted", 1);
        }
        $taxRecordModel->set("method", "Simple");
        $taxRecordModel->set("taxType", "Fixed");
        $taxRecordModel->set("compoundon", array());
        $taxRecordModel->set("regions", array());
        $taxRecordModel->setType($type);
        $taxRecordModel->set("type", "Fixed");
        $taxRecordModel->set("cf_do_not_create_queue_for_platformintegration", true);
        $taxId = $taxRecordModel->save();
        $latest_value = json_encode($taxRecordModel->getData());
        if ($isInsert) {
            $sql = "INSERT INTO platformintegration_mapping_tax(qb_tax_id, vt_tax_id, latest_value, latest_update) VALUES(?, ?, ?, ?)";
            $adb->pquery($sql, array($qboTaxRate->Id, $taxId, $latest_value, $latest_update));
        } else {
            $sql = "UPDATE platformintegration_mapping_tax\r\n                            SET latest_value=?, latest_update=?\r\n                            WHERE qb_tax_id=? AND vt_tax_id=?";
            $adb->pquery($sql, array($latest_value, $latest_update, $qboTaxRate->Id, $taxId));
        }
    }
    public function insertOrUpdateTaxOnV7($qboTaxRate, $type, $pDatasource)
    {
        global $adb;
        $taxRates = $this->getTaxRates(true);
        $currentTax = $this->getTaxRateByQboId($taxRates, $qboTaxRate->Id);
        $currentValue = json_decode($currentTax["latest_value"]);
        $taxLabel = html_entity_decode($qboTaxRate->Name, ENT_QUOTES);
        $latest_update = $qboTaxRate->MetaData->LastUpdatedTime;
        $taxRateValue = $qboTaxRate->RateValue;
        $isInsert = true;
        if (empty($currentTax)) {
            $currentTax = $this->getTaxRateByPlatformTaxName($taxRates, $taxLabel);
            if (empty($currentTax)) {
                $taxRecordModel = new Inventory_TaxRecord_Model();
            } else {
                $taxId = $currentTax["taxid"];
                $taxRecordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
                if ($pDatasource == "vtiger") {
                    if ($currentTax["taxlabel"] != $currentValue["taxlabel"]) {
                        $taxLabel = $currentTax["taxlabel"];
                    }
                    if ($currentTax["percentage"] != $currentValue["percentage"]) {
                        $taxRateValue = $currentTax["percentage"];
                    }
                }
            }
        } else {
            $isInsert = false;
            if ($currentTax["latest_update"] == $latest_update) {
                return true;
            }
            $taxId = $currentTax["taxid"];
            $taxRecordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
            if ($pDatasource == "vtiger") {
                if ($currentTax["taxlabel"] != $currentValue["taxlabel"]) {
                    $taxLabel = $currentTax["taxlabel"];
                }
                if ($currentTax["percentage"] != $currentValue["percentage"]) {
                    $taxRateValue = $currentTax["percentage"];
                }
            }
        }
        $taxLabel = html_entity_decode($taxLabel, ENT_QUOTES);
        $taxRecordModel->set("taxlabel", $taxLabel);
        $taxRecordModel->set("percentage", $taxRateValue);
        if ($qboTaxRate->Active == "true") {
            $taxRecordModel->set("deleted", 0);
        } else {
            $taxRecordModel->set("deleted", 1);
        }
        $taxRecordModel->set("method", "Simple");
        $taxRecordModel->set("taxType", "Fixed");
        $taxRecordModel->set("compoundon", array());
        $taxRecordModel->set("regions", array());
        $taxRecordModel->setType($type);
        $taxRecordModel->set("type", "Fixed");
        $taxRecordModel->set("cf_do_not_create_queue_for_platformintegration", true);
        $taxId = $taxRecordModel->save();
        $latest_value = json_encode($taxRecordModel->getData());
        if ($isInsert) {
            $sql = "INSERT INTO platformintegration_mapping_tax(qb_tax_id, vt_tax_id, latest_value, latest_update) VALUES(?, ?, ?, ?)";
            $adb->pquery($sql, array($qboTaxRate->Id, $taxId, $latest_value, $latest_update));
        } else {
            $sql = "UPDATE platformintegration_mapping_tax\r\n                            SET latest_value=?, latest_update=?\r\n                            WHERE qb_tax_id=? AND vt_tax_id=?";
            $adb->pquery($sql, array($latest_value, $latest_update, $qboTaxRate->Id, $taxId));
        }
    }
    public function sysTaxToPlatform($pDatasource)
    {
        try {
            $error = "";
            $code = "Succeed";
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
}

?>