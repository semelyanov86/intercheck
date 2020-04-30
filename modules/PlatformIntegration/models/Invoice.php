<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_Invoice_Model extends PlatformIntegration_Engine_Model
{
    protected $discountItemId = "";
    public function __construct()
    {
        $otherUnusedFields = array("contact_id", "potential_id", "assigned_user_id", "account_id", "hdnDiscountPercent", "hdnDiscountAmount", "region_id", "hdnS_H_Percent", "salesorder_id");
        $this->unusedFields = array_merge($this->unusedFields, $otherUnusedFields);
    }
    public function updateFieldForVtigerModel($dataInputs)
    {
        try {
            $qbField = $dataInputs["mappedField"]["qb_field"];
            $code = "Succeed";
            $error = "";
            if ($qbField == "CustomerRef") {
                return $this->updateCustomerRefForVtigerModel($dataInputs);
            }
            if ($qbField == "CustomField.DefinitionId1" || $qbField == "CustomField.DefinitionId2" || $qbField == "CustomField.DefinitionId3") {
                return $this->updateDefinitionIdForVtigerModel($dataInputs);
            }
            if ($qbField == "DiscountLineDetailPercent" || $qbField == "DiscountLineDetailAmount") {
                return $this->updateDiscountLineDetailForVtigerModel($dataInputs);
            }
            if ($qbField == "BillAddr.Line" || $qbField == "ShipAddr.Line") {
                return parent::updateAddressFieldForVtigerModel($dataInputs);
            }
            if ($qbField == "ShippingItem") {
                return $this->updateShippingItemForVtigerModel($dataInputs);
            }
            if ($qbField == "TxnTaxDetail") {
                return $this->updateItemsVtigerModel($dataInputs);
            }
            return parent::updateFieldForVtigerModel($dataInputs);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateShippingItemForVtigerModel($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $code = "Succeed";
            $error = "";
            $realValue = $this->getValueFromQboRecord($record, $mappedField["qb_field"]);
            $vtField = $mappedField["vt_field"];
            $realValue = $this->convertToVtigerValue($realValue, $mappedField["data_type"]);
            $realValue = html_entity_decode($realValue, ENT_QUOTES);
            $recordModel->set($vtField, $realValue);
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateItemsVtigerModel($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $code = "Succeed";
            $error = "";
            $realValue = $this->getValueFromQboRecord($record, $mappedField["qb_field"]);
            $vtField = $mappedField["vt_field"];
            $realValue = $this->convertToVtigerValue($realValue, $mappedField["data_type"]);
            $realValue = html_entity_decode($realValue, ENT_QUOTES);
            $recordModel->set($vtField, $realValue);
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateDiscountLineDetailForVtigerModel($dataInputs)
    {
        $recordModel = $dataInputs["recordModel"];
        $code = "Succeed";
        $error = "";
        return array("code" => $code, "error" => $error, "result" => $recordModel);
    }
    public function updateDefinitionIdForVtigerModel($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $code = "Succeed";
            $error = "";
            $realValue = $this->getValueFromPlatformRecord($record, $mappedField["qb_field"]);
            $vtField = $mappedField["vt_field"];
            $realValue = $this->convertToVtigerValue($realValue, $mappedField["data_type"]);
            $realValue = html_entity_decode($realValue, ENT_QUOTES);
            $recordModel->set($vtField, $realValue);
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getValueFromPlatformRecord($record, $fieldName)
    {
        $val = "";
        if ($fieldName == "CustomField.DefinitionId1" || $fieldName == "CustomField.DefinitionId2" || $fieldName == "CustomField.DefinitionId3") {
            $seq = str_replace("CustomField.DefinitionId", "", $fieldName);
            foreach ($record->CustomField as $customField) {
                if ($customField->DefinitionId == $seq) {
                    $val = $customField->StringValue;
                    break;
                }
            }
        } else {
            if ($fieldName == "SubTotalLineDetail") {
                foreach ($record->Line as $line) {
                    if ($line->DetailType == "SubTotalLineDetail") {
                        $val = $line->Amount;
                        break;
                    }
                }
            } else {
                if ($fieldName == "DiscountLineDetailPercent") {
                    foreach ($record->Line as $line) {
                        if ($line->DetailType == "DiscountLineDetail") {
                            $discountLineDetail = $line->DiscountLineDetail;
                            if ($discountLineDetail->PercentBased == "true") {
                                $val = $discountLineDetail->DiscountPercent;
                                break;
                            }
                        }
                    }
                } else {
                    if ($fieldName == "DiscountLineDetailAmount") {
                        foreach ($record->Line as $line) {
                            if ($line->DetailType == "DiscountLineDetail") {
                                $discountLineDetail = $line->DiscountLineDetail;
                                if ($discountLineDetail->PercentBased == "false") {
                                    $val = $line->Amount;
                                    break;
                                }
                            }
                        }
                    } else {
                        if ($fieldName == "NetAmountTaxable") {
                            foreach ($record->TxnTaxDetail->TaxLine as $line) {
                                if ($line->DetailType == "TaxLineDetail") {
                                    $netAmountTaxable = (double) $line->TaxLineDetail->NetAmountTaxable;
                                    if ($netAmountTaxable != 0) {
                                        $val = $netAmountTaxable;
                                        break;
                                    }
                                }
                            }
                        } else {
                            if ($fieldName == "ShippingItem") {
                                foreach ($record->Line as $line) {
                                    if ($line->DetailType == "SalesItemLineDetail") {
                                        $salesItemLineDetail = $line->SalesItemLineDetail;
                                        if ($salesItemLineDetail->ItemRef == "SHIPPING_ITEM_ID") {
                                            $val = $line->Amount;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $val = parent::getValueFromPlatformRecord($record, $fieldName);
                            }
                        }
                    }
                }
            }
        }
        return $val;
    }
    public function updateCustomerRefForVtigerModel($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $vt_fields = $dataInputs["vt_fields"];
            $code = "Succeed";
            $error = "";
            $realValue = $this->getValueFromPlatformRecord($record, $mappedField["qb_field"]);
            $realValue = html_entity_decode($realValue, ENT_QUOTES);
            $vtField = $mappedField["vt_field"];
            if (empty($realValue)) {
                $recordModel->set($vtField, $realValue);
                return array("code" => $code, "error" => $error, "result" => $recordModel);
            }
            $moduleName = $this->getModuleLinkToField($vt_fields, $vtField);
            if (!empty($moduleName) && in_array($moduleName, $this->getAllowedModule())) {
                $realId = $this->getVtigerRecordByName($moduleName, $realValue);
                $recordModel->set($vtField, $realId);
                return array("code" => $code, "error" => $error, "result" => $recordModel);
            }
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getVtigerRecordByName($vtModule, $name = "")
    {
        if ($vtModule == "Contacts" || $vtModule == "Accounts") {
            global $adb;
            $crmid = "";
            $code = "Succeed";
            $error = "";
            $sql = "SELECT VVL.vt_id FROM vtiger_vteqbolinks VVL \r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE VVL.qb_id=? AND VVL.vt_module=? LIMIT 1";
            $res = $adb->pquery($sql, array($name, $vtModule));
            $nr = $adb->num_rows($res);
            if (0 < $nr) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $crmid = $row["vt_id"];
                }
            }
            return $crmid;
        }
        return parent::getVtigerRecordByName($vtModule, $name);
    }
    public function getAllRecordsFromPlatform($qboModule)
    {
        try {
            global $root_directory;
            $result = array();
            $code = "Succeed";
            $error = "";
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $res = $this->getQueryByPlatformModule($qboModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $sqls = $res["sql"];
            foreach ($sqls as $sql) {
                $sql = trim($sql);
                $result1 = $dataService->Query($sql);
                $e = $dataService->getLastError();
                if ($e != NULL) {
                    return array("code" => "Failed", "error" => $e->getResponseBody());
                }
                if (!empty($result1)) {
                    $result = array_merge($result, $result1);
                }
            }
            if (empty($result) || 0 == count($result)) {
                $code = "Failed";
                $error = vtranslate("LBL_RECORD_NOT_FOUND", "VTEQBO");
                return array("code" => $code, "error" => $error);
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function preSaveModel($recordModel, $record)
    {
        global $adb;
        if (true) {
            $taxType = "group";
            $subTotoal = floatval($this->getValueFromPlatformRecord($record, "SubTotalLineDetail"));
            $discountPercent = floatval($this->getValueFromPlatformRecord($record, "DiscountLineDetailPercent"));
            $discountAmount = floatval($this->getValueFromPlatformRecord($record, "DiscountLineDetailAmount"));
            if ($record->ApplyTaxAfterDiscount == "true") {
                if (!empty($discountPercent)) {
                    $discountAmount = $subTotoal * $discountPercent / 100;
                }
                $subTotoal -= $discountAmount;
                $taxLines = $record->TxnTaxDetail->TaxLine;
                if (!empty($taxLines)) {
                    foreach ($taxLines as $taxLine) {
                        if (!empty($taxLine->TaxLineDetail)) {
                            $netAmountTaxable = floatval($taxLine->TaxLineDetail->NetAmountTaxable);
                            if ($netAmountTaxable != $subTotoal) {
                                $taxType = "individual";
                            }
                            break;
                        }
                    }
                }
            } else {
                $taxType = "individual";
            }
            $recordModel->set("hdnTaxType", $taxType);
        }
        if (true) {
            $contactId = $recordModel->get("contact_id");
            if (empty($contactId)) {
                $accountId = $recordModel->get("account_id");
                $sql = "SELECT VVL2.vt_id\r\n                    FROM vtiger_vteqbolinks VVL1\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL1.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    LEFT JOIN vtiger_vteqbolinks VVL2 ON VVL1.qb_id = VVL2.qb_id AND VVL2.qb_module='Customer'\r\n                    INNER JOIN vtiger_crmentity VC2 ON VVL2.vteqbolinkid = VC2.crmid AND VC2.deleted=0\r\n                    WHERE VVL1.qb_module='Company' AND VVL1.vt_id=? LIMIT 0, 1";
                $res = $adb->pquery($sql, array($accountId));
                if (0 < $adb->num_rows($res)) {
                    if ($row = $adb->fetchByAssoc($res)) {
                        $contactId = $row["vt_id"];
//                        break;
                    }
                    $recordModel->set("contact_id", $contactId);
                }
            }
        }
        $currency_id = $this->getCurrencyId();
        $recordModel->set("currency_id", $currency_id);
        return parent::preSaveModel($recordModel, $record);
    }
    public function postSaveModel($recordModel, $record, $mappedFields, $mappedInfo = false, $isUpdate = false)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $crmid = $recordModel->getId();
            $qboVersion = $this->getPlatformVersion();
            $res = $this->saveItemsToVtiger($recordModel, $record, $mappedInfo, $isUpdate);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $res = $this->saveChargeRelToVtiger($recordModel, $record);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $subTotoal = floatval($this->getValueFromPlatformRecord($record, "SubTotalLineDetail"));
            $discount_amount = "";
            $discount_percent = "";
            if (true) {
                foreach ($mappedFields as $mappedField) {
                    $qbField = $mappedField["qb_field"];
                    if ($qbField == "DiscountLineDetailPercent") {
                        $discount_percent = $this->getValueFromPlatformRecord($record, $qbField);
                    } else {
                        if ($qbField == "DiscountLineDetailAmount") {
                            $discount_amount = $this->getValueFromPlatformRecord($record, $qbField);
                        }
                    }
                    if (!empty($discount_amount) && !empty($discount_percent)) {
                        break;
                    }
                }
                $discount_percent = floatval($discount_percent);
                $discount_amount = floatval($discount_amount);
            }
            $sql = "UPDATE vtiger_invoice SET discount_amount=?, discount_percent=? WHERE invoiceid=?";
            $params = array($discount_amount, $discount_percent, $crmid);
            $adb->pquery($sql, $params);
            if (true) {
                $email = $this->getValueFromPlatformRecord($record, "BillEmail.Address");
                if (!empty($email)) {
                    $sql = "SELECT VVL2.vt_id, VMF.vt_module, VMF.vt_field\r\n                            FROM vtiger_vteqbolinks VVL1\r\n                            INNER JOIN vtiger_crmentity VC1 ON VVL1.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                            INNER JOIN vtiger_vteqbolinks VVL2 ON VVL1.qb_id=VVL2.qb_id\r\n                            INNER JOIN vtiger_crmentity VC2 ON VVL2.vteqbolinkid = VC2.crmid AND VC2.deleted=0\r\n                            INNER JOIN vtiger_crmentity VC3 ON VVL2.vt_id = VC3.crmid AND VC3.deleted=0\r\n                            INNER JOIN vteqbo_mapping_fields VMF ON VVL2.vt_module = VMF.vt_module\r\n                            WHERE VVL1.vt_id=? AND VVL1.vt_module='Accounts'\r\n                            AND VVL2.qb_module IN ('Customer', 'Company')\r\n                            AND VMF.qb_field='PrimaryEmailAddr.Address' AND VMF.is_active=1\r\n                            LIMIT 0, 1";
                    $res = $adb->pquery($sql, array($recordModel->get("account_id")));
                    if ($adb->num_rows($res)) {
                        while ($row = $adb->fetchByAssoc($res)) {
                            $recordId = $row["vt_id"];
                            $vt_module = $row["vt_module"];
                            $vt_field = $row["vt_field"];
                            $recordModel2 = Vtiger_Record_Model::getInstanceById($recordId, $vt_module);
                            $recordModel2->set("id", $recordId);
                            $recordModel2->set("mode", "edit");
                            $recordModel2->set($vt_field, $email);
                            $recordModel2->set("cf_do_not_create_queue_for_vteqbo", true);
                            $recordModel2->save();
                        }
                    }
                }
            }
            if (true) {
                $balance = (double) $record->Balance;
                $totalAmt = (double) $record->TotalAmt;
                $status = "";
                if ($balance <= 0) {
                    $status = "Paid";
                } else {
                    if ($balance < $totalAmt) {
                        $status = "Partially paid";
                    } else {
                        $date1 = DateTime::createFromFormat("Y-m-d", $record->DueDate);
                        $d2 = date("Y-m-d");
                        $date2 = DateTime::createFromFormat("Y-m-d", $d2);
                        if ($date1 <= $date2) {
                            $status = "Overdue";
                        } else {
                            $status = "Due";
                        }
                    }
                }
                $sql = "UPDATE vtiger_invoicecf SET cf_quickbooks_status_inv=?, cf_qb_invoice_no=? WHERE invoiceid=?";
                $adb->pquery($sql, array($status, $record->DocNumber, $crmid));
            }
            if (true) {
                $subject = $recordModel->get("subject");
                if (empty($subject)) {
                    $subject = $this->getValueFromPlatformRecord($record, "DocNumber");
                    $recordModel->set("mode", "edit");
                    $recordModel->set("subject", $subject);
                    $recordModel->set("cf_do_not_create_queue_for_vteqbo", true);
                    $recordModel->save();
                }
            }
            if ($qboVersion == "AUS") {
                $sql1 = "UPDATE vtiger_invoicecf SET cf_amounts_are_in_aus = ? WHERE invoiceid=?";
                $cf_amounts_are_in_aus = $record->GlobalTaxCalculation;
                $adb->pquery($sql1, array($cf_amounts_are_in_aus, $crmid));
            }
            return parent::postSaveModel($recordModel, $record, $mappedFields, $mappedInfo, $isUpdate);
        } catch (Exception $ex) {
            echo "<br />\\n" . str_repeat("-", 80) . "xxx8";
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function saveChargeRelToVtiger($recordModel, $record)
    {
        try {
            global $adb;
            global $vtiger_current_version;
            $code = "Succeed";
            $error = "";
            $crmid = $recordModel->getId();
            if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
                $sql = "SELECT chargeid, taxes FROM vtiger_inventorycharges LIMIT 1";
                $res = $adb->pquery($sql, array());
                $taxIds = array();
                $chargeid = "";
                if (0 < $adb->num_rows($res)) {
                    while ($row = $adb->fetchByAssoc($res)) {
                        $taxIds = json_decode(decode_html($row["taxes"]));
                        $chargeid = $row["chargeid"];
                    }
                }
                if (empty($taxIds)) {
                    return array("code" => $code, "error" => $error);
                }
                $adb->pquery("DELETE FROM vtiger_inventorychargesrel WHERE recordid=?", array($crmid));
                $itemQuery = "INSERT INTO vtiger_inventorychargesrel(recordid, charges) VALUES (?, ?)";
                $taxes = array();
                foreach ($taxIds as $taxid) {
                    $taxes[(string) $taxid] = "";
                }
                $charges = array();
                $charges[(string) $chargeid] = array("value" => $recordModel->get("hdnS_H_Amount"), "taxes" => $taxes);
                $charges = json_encode($charges);
                $itemParams = array($crmid, $charges);
                $adb->pquery($itemQuery, $itemParams);
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function saveItemsToVtiger($recordModel, $record, $mappedInfo, $isUpdate = false)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $crmid = $recordModel->getId();
            $moduleName = $recordModel->getModuleName();
            $res = $this->getQboApi();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $qboApi = $res["result"];
            $primaryDS = strtolower($qboApi["primary_datasource"]);
            $isConflict = false;
            if ($isUpdate && $primaryDS == "vtiger") {
                $isConflict = $this->checkConflictDataOnItemsOfVtiger($crmid, $moduleName);
            }
            if ($isConflict == false) {
                $adb->pquery("DELETE FROM vtiger_inventoryproductrel WHERE id=?", array($crmid));
                $res = $this->getItemsFromQBORecord($recordModel, $record);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $items = $res["items"];
                $sql = "";
                foreach ($items as $item) {
                    $fields = "";
                    $p = "";
                    $params = array();
                    foreach ($item as $k => $v) {
                        if (!empty($fields)) {
                            $fields .= ",";
                            $p .= ",";
                        }
                        $fields .= $k;
                        $p .= "?";
                        $params[] = $v;
                    }
                    $sql = "INSERT INTO vtiger_inventoryproductrel(" . $fields . ") VALUE (" . $p . ")";
                    $adb->pquery($sql, $params);
                }
                $realSubTotal = $res["subTotal"];
                if ($recordModel->get("hdnTaxType") == "individual") {
                    $sql = "UPDATE vtiger_invoice SET subtotal=? WHERE invoiceid=?";
                    $adb->pquery($sql, array($realSubTotal, $crmid));
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getTaxValueById($record, $taxId)
    {
        $val = 0;
        $lines = $record->TxnTaxDetail->TaxLine;
        if (is_array($lines)) {
            foreach ($record->TxnTaxDetail->TaxLine as $line) {
                if ($line->DetailType == "TaxLineDetail" && 0 < $line->Amount && $line->TaxLineDetail->TaxRateRef == $taxId) {
                    return $line->TaxLineDetail->TaxPercent;
                }
            }
        } else {
            if ($lines->DetailType == "TaxLineDetail" && 0 < $lines->Amount && $lines->TaxLineDetail->TaxRateRef == $taxId) {
                return $lines->TaxLineDetail->TaxPercent;
            }
        }
        return $val;
    }
    public function getIdOfVtigerItem($qbId)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $result = 0;
            $sql = "SELECT VVL.vt_id FROM vtiger_vteqbolinks VVL \r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE (VVL.qb_module = 'Product' OR VVL.qb_module = 'Service') AND VVL.qb_id = ? LIMIT 1";
            $res = $adb->pquery($sql, array($qbId));
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $result = $row["vt_id"];
                }
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            global $adb;
            $qboVersion = $this->getPlatformVersion();
            $GlobalTaxCalculation = "";
            $res = $this->getDiscountItemIdOnQBO();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $discountItemId = $res["result"];
            $crmid = $recordModel->getId();
            $taxRates = $this->getTaxRates();
            $wroongMappedFields = array("DiscountLineDetailPercent", "DiscountLineDetailAmount", "ShippingItem", "SubTotalLineDetail", "InvoiceStatus");
            foreach ($wroongMappedFields as $field) {
                unset($changedData[$field]);
            }
            $discountOnLines = 0;
            $lines = array();
            $sql = "SELECT vi.*, VVL.qb_id, VP.productname, VS.servicename\r\n                    FROM vtiger_inventoryproductrel vi\r\n                    LEFT JOIN vtiger_vteqbolinks VVL ON vi.productid = VVL.vt_id AND (VVL.vt_module = 'Products' OR VVL.vt_module = 'Services')\r\n                    LEFT JOIN vtiger_products VP ON VP.productid = vi.productid\r\n                    LEFT JOIN vtiger_service VS ON VS.serviceid = vi.productid\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE vi.id = ?";
            $res = $adb->pquery($sql, array($crmid));
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $quantity = floatval($row["quantity"]);
                    $listprice = floatval($row["listprice"]);
                    $amount = $quantity * $listprice;
                    $discount_amount = floatval($row["discount_amount"]);
                    $discount_percent = floatval($row["discount_percent"]);
                    if (!empty($discount_percent)) {
                        $discount_amount = $amount * $discount_percent / 100;
                    }
                    $discountOnLines += $discount_amount;
                    $totalTax = 0;
                    $taxCodeRefAUS = "";
                    foreach ($taxRates as $taxRate) {
                        $taxTemp = floatval($row[$taxRate["taxname"]]);
                        if (!empty($taxTemp)) {
                            $totalTax += $taxTemp;
                            if ($qboVersion == "AUS") {
                                $taxCodeRefAUS = $taxRate["taxcode_id"];
                            }
                        }
                    }
                    if ($qboVersion == "US") {
                        $taxCodeRef = "NON";
                        if (!empty($totalTax)) {
                            $taxCodeRef = "TAX";
                        }
                    } else {
                        if ($qboVersion == "AUS") {
                            if (empty($taxCodeRefAUS)) {
                                $taxCodeRefAUS = $row["cf_taxcode_in_aus"];
                                if (empty($taxCodeRefAUS)) {
                                    $taxCodeRefAUS = 14;
                                }
                            } else {
                                $GlobalTaxCalculation = "TaxInclusive";
                            }
                            $taxCodeRef = $taxCodeRefAUS;
                        }
                    }
                    $lines[] = array("Description" => $row["comment"], "Amount" => $amount, "DetailType" => "SalesItemLineDetail", "SalesItemLineDetail" => array("ItemRef" => $row["qb_id"], "UnitPrice" => $listprice, "Qty" => $quantity, "TaxCodeRef" => $taxCodeRef));
                    if (0 < $discount_amount) {
                        $description = vtranslate("LBL_DISCOUNT_FOR", "VTEQBO");
                        if (empty($row["productname"])) {
                            $description .= " '" . $row["servicename"] . "'";
                        } else {
                            $description .= " '" . $row["productname"] . "'";
                        }
                        $lines[] = array("Description" => $description, "Amount" => 0 - $discount_amount, "DetailType" => "SalesItemLineDetail", "SalesItemLineDetail" => array("ItemRef" => $discountItemId, "UnitPrice" => 0 - $discount_amount, "Qty" => 1, "TaxCodeRef" => $taxCodeRef));
                    }
                }
            }
            $lines[] = array("Amount" => $recordModel->get("hdnS_H_Amount"), "DetailType" => "SalesItemLineDetail", "SalesItemLineDetail" => array("ItemRef" => array("value" => "SHIPPING_ITEM_ID")));
            $changedData["Line"] = $lines;
            $received = floatval($recordModel->get("received"));
            if (!empty($received)) {
                $depositToAccountRef = 27;
                $res2 = $this->getRecordsFromQbo("DepositToAccount");
                if ($res2["code"] == "Succeed") {
                    $firstRecord = reset($res2["result"]);
                    $depositToAccountRef = $firstRecord->Id;
                }
                $changedData["DepositToAccountRef"] = $depositToAccountRef;
            }
            if (true) {
                $taxLines = array();
                $sql = "SELECT 1";
                foreach ($taxRates as $taxRate) {
                    $sql .= ", SUM(" . $taxRate["taxname"] . ") as " . $taxRate["taxname"];
                }
                $sql .= " FROM vtiger_inventoryproductrel WHERE id=?";
                $res = $adb->pquery($sql, array($crmid));
                $inventory = $adb->fetch_row($res);
                foreach ($taxRates as $taxRate) {
                    $taxValue = floatval($inventory[$taxRate["taxname"]]);
                    if (empty($taxRate["qb_tax_id"]) || $taxValue == 0) {
                        continue;
                    }
                    $taxLines[] = array("DetailType" => "TaxLineDetail", "TaxLineDetail" => array("TaxRateRef" => $taxRate["qb_tax_id"], "PercentBased" => "true", "TaxPercent" => $taxRate["percentage"]));
                }
                if (!empty($taxLines)) {
                    $changedData["TxnTaxDetail"] = array("TxnTaxCodeRef" => 3, "TaxLine" => $taxLines);
                }
                $discountAmountTotal = floatval($recordModel->get("hdnDiscountAmount"));
                $discountPercentTotal = floatval($recordModel->get("hdnDiscountPercent"));
                if ($recordModel->get("hdnTaxType") == "group") {
                    $changedData["ApplyTaxAfterDiscount"] = "true";
                    if (!empty($discountPercentTotal)) {
                        $changedData["Line"][] = array("DetailType" => "DiscountLineDetail", "DiscountLineDetail" => array("PercentBased" => "true", "DiscountPercent" => $discountPercentTotal, "DiscountAccountRef" => "38"));
                    } else {
                        if (!empty($discountAmountTotal)) {
                            $changedData["Line"][] = array("Amount" => $discountAmountTotal, "DetailType" => "DiscountLineDetail", "DiscountLineDetail" => array("PercentBased" => "false", "DiscountAccountRef" => "38"));
                        }
                    }
                } else {
                    $subtotal = floatval($recordModel->get("hdnSubTotal"));
                    if (0 < $discountPercentTotal) {
                        $discountAmountTotal = $subtotal * $discountPercentTotal / 100;
                        $changedData["Line"][] = array("DetailType" => "DiscountLineDetail", "DiscountLineDetail" => array("PercentBased" => "true", "DiscountPercent" => $discountPercentTotal, "DiscountAccountRef" => "38"));
                    } else {
                        $changedData["Line"][] = array("Amount" => $discountAmountTotal, "DetailType" => "DiscountLineDetail", "DiscountLineDetail" => array("PercentBased" => "false", "DiscountAccountRef" => "38"));
                    }
                    if ($discountAmountTotal == 0) {
                        $changedData["ApplyTaxAfterDiscount"] = "true";
                    } else {
                        $changedData["ApplyTaxAfterDiscount"] = "false";
                    }
                }
            }
            if ($qboVersion == "AUS") {
                if (empty($GlobalTaxCalculation)) {
                    $GlobalTaxCalculation = $recordModel->get("cf_amounts_are_in_aus");
                }
                if (empty($GlobalTaxCalculation)) {
                    $GlobalTaxCalculation = "NotApplicable";
                }
                $changedData["GlobalTaxCalculation"] = $GlobalTaxCalculation;
            }
            foreach ($mappedFields as $mappedField) {
                if ($mappedField["qb_field"] == "CustomField.DefinitionId1") {
                    $val = $changedData["CustomField"]["DefinitionId1"];
                    unset($changedData["CustomField"]["DefinitionId1"]);
                    $changedData["CustomField"][] = array("DefinitionId" => "1", "Type" => "StringType", "StringValue" => $val);
                } else {
                    if ($mappedField["qb_field"] == "CustomField.DefinitionId2") {
                        $val = $changedData["CustomField"]["DefinitionId2"];
                        unset($changedData["CustomField"]["DefinitionId2"]);
                        $changedData["CustomField"][] = array("DefinitionId" => "2", "Type" => "StringType", "StringValue" => $val);
                    } else {
                        if ($mappedField["qb_field"] == "CustomField.DefinitionId3") {
                            $val = $changedData["CustomField"]["DefinitionId3"];
                            unset($changedData["CustomField"]["DefinitionId3"]);
                            $changedData["CustomField"][] = array("DefinitionId" => "3", "Type" => "StringType", "StringValue" => $val);
                        }
                    }
                }
            }
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncParentRecordToVtiger($record)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $adb;
            $moduleName = $this->moduleName;
            $qboId = $record->CustomerRef;
            $qboModule = "Company";
            $vtModule = "Accounts";
            $res = $this->getMappedInfoByQboRecord($qboId, $qboModule);
            if (count($res) == 0) {
                $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                if (class_exists($vtModuleClass)) {
                    $obj = new $vtModuleClass($moduleName);
                } else {
                    $obj = new VTEQBO_Engine_Model($moduleName);
                }
                $res = $obj->syncQboToVtiger($qboModule, $vtModule, $qboId);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
            }
            $qboModule = "Customer";
            $vtModule = "Contacts";
            $res = $this->getMappedInfoByQboRecord($qboId, $qboModule);
            if (count($res) == 0) {
                $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                if (class_exists($vtModuleClass)) {
                    $obj = new $vtModuleClass($moduleName);
                } else {
                    $obj = new VTEQBO_Engine_Model($moduleName);
                }
                $res = $obj->syncQboToVtiger($qboModule, $vtModule, $qboId);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
            }
            foreach ($record->Line as $line) {
                if ($line->DetailType == "SalesItemLineDetail") {
                    $salesItemLineDetail = $line->SalesItemLineDetail;
                    if (!empty($salesItemLineDetail)) {
                        $qboId = $salesItemLineDetail->ItemRef;
                        $qboModule = "Product";
                        $vtModule = "Products";
                        $res2 = $this->getMappedInfoByQboRecord($qboId, $qboModule);
                        if (count($res2) == 0) {
                            $qboModule2 = "Service";
                            $vtModule2 = "Services";
                            $res4 = $this->getMappedInfoByQboRecord($qboId, $qboModule2);
                            if (count($res4) == 0) {
                                $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                                if (class_exists($vtModuleClass)) {
                                    $obj = new $vtModuleClass($moduleName);
                                } else {
                                    $obj = new VTEQBO_Engine_Model($moduleName);
                                }
                                $res3 = $obj->syncQboToVtiger($qboModule, $vtModule, $qboId);
                                $vtModuleClass2 = "VTEQBO_" . $vtModule2 . "_Model";
                                if (class_exists($vtModuleClass2)) {
                                    $obj2 = new $vtModuleClass2($moduleName);
                                } else {
                                    $obj2 = new VTEQBO_Engine_Model($moduleName);
                                }
                                $res5 = $obj2->syncQboToVtiger($qboModule2, $vtModule2, $qboId);
                            } else {
                                $vtModuleClass2 = "VTEQBO_" . $vtModule2 . "_Model";
                                if (class_exists($vtModuleClass2)) {
                                    $obj2 = new $vtModuleClass2($moduleName);
                                } else {
                                    $obj2 = new VTEQBO_Engine_Model($moduleName);
                                }
                                $res5 = $obj2->syncQboToVtiger($qboModule2, $vtModule2, $qboId);
                            }
                        } else {
                            $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                            if (class_exists($vtModuleClass)) {
                                $obj = new $vtModuleClass($moduleName);
                            } else {
                                $obj = new VTEQBO_Engine_Model($moduleName);
                            }
                            $res3 = $obj->syncQboToVtiger($qboModule, $vtModule, $qboId);
                        }
                    }
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncParentRecordToPlatform($recordModel)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $adb;
            $moduleName = $this->moduleName;
            $crmId = $recordModel->getId();
            $account_id = $recordModel->get("account_id");
            if (!empty($account_id)) {
                $qboModule = "Company";
                $vtModule = "Accounts";
                $accountsModel = Vtiger_Record_Model::getInstanceById($account_id, $vtModule);
                if ($accountsModel->get("cf_sync_to_qbo") != "1") {
                    $accountsModel->set("cf_sync_to_qbo", 1);
                    $accountsModel->set("mode", "edit");
                    $accountsModel->save();
                }
                $mappedInfo = $this->getMappedInfoByVtigerRecord($account_id, $vtModule);
                if (count($mappedInfo) == 0) {
                    $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new PlatformIntegration_Engine_Model($moduleName);
                    }
                    $res = $obj->syncVtigerToPlatform($qboModule, $vtModule, $account_id);
                    if ($res["code"] != "Succeed") {
                        return $res;
                    }
                }
            }
            $contact_id = $recordModel->get("contact_id");
            if (!empty($contact_id)) {
                $qboModule = "Customer";
                $vtModule = "Contacts";
                $accountsModel = Vtiger_Record_Model::getInstanceById($contact_id, $vtModule);
                if ($accountsModel->get("cf_sync_to_qbo") != "1") {
                    $accountsModel->set("cf_sync_to_qbo", 1);
                    $accountsModel->set("mode", "edit");
                    $accountsModel->save();
                }
                $mappedInfo = $this->getMappedInfoByVtigerRecord($contact_id, $vtModule);
                if (count($mappedInfo) == 0) {
                    $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new PlatformIntegration_Engine_Model($moduleName);
                    }
                    $res = $obj->syncVtigerToQbo($qboModule, $vtModule, $contact_id);
                    if ($res["code"] != "Succeed") {
                        return $res;
                    }
                }
            }
            if (true) {
                $sql = "SELECT I.productid AS crmid, VVL.qb_id\r\n                        FROM vtiger_inventoryproductrel I\r\n                        INNER JOIN vtiger_products P ON I.productid = P.productid\r\n                        LEFT JOIN vtiger_vteqbolinks VVL ON VVL.vt_id = P.productid AND VVL.vt_module='Products'\r\n                        WHERE I.id = ?";
                $res1 = $adb->pquery($sql, array($crmId));
                if ($adb->num_rows($res1)) {
                    $qboModule = "Product";
                    $vtModule = "Products";
                    $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new PlatformIntegration_Engine_Model($moduleName);
                    }
                    while ($row = $adb->fetchByAssoc($res1)) {
                        $product_id = $row["crmid"];
                        $qbId = $row["qb_id"];
                        if (empty($qbId)) {
                            $productModel = Vtiger_Record_Model::getInstanceById($product_id, $vtModule);
                            if ($productModel->get("cf_sync_to_qbo") != "1") {
                                $productModel->set("cf_sync_to_qbo", 1);
                                $productModel->set("mode", "edit");
                                $productModel->save();
                            }
                        }
                        $res2 = $obj->syncVtigerToPlatform($qboModule, $vtModule, $product_id);
                        if ($res2["code"] != "Succeed") {
                            return $res2;
                        }
                    }
                }
            }
            if (true) {
                $sql = "SELECT I.productid AS crmid, VVL.qb_id\r\n                        FROM vtiger_inventoryproductrel I\r\n                        INNER JOIN vtiger_service S ON I.productid = S.serviceid\r\n                        LEFT JOIN vtiger_vteqbolinks VVL ON VVL.vt_id = S.serviceid AND VVL.vt_module='Services'\r\n                        WHERE I.id = ?";
                $res3 = $adb->pquery($sql, array($crmId));
                if ($adb->num_rows($res3)) {
                    $qboModule = "Service";
                    $vtModule = "Services";
                    $vtModuleClass = "VTEQBO_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new VTEQBO_Engine_Model($moduleName);
                    }
                    while ($row = $adb->fetchByAssoc($res3)) {
                        $service_id = $row["crmid"];
                        $qbId = $row["qb_id"];
                        if (empty($qbId)) {
                            $serviceModel = Vtiger_Record_Model::getInstanceById($service_id, $vtModule);
                            if ($serviceModel->get("cf_sync_to_qbo") != "1") {
                                $serviceModel->set("cf_sync_to_qbo", 1);
                                $serviceModel->set("mode", "edit");
                                $serviceModel->save();
                            }
                        }
                        $res4 = $obj->syncVtigerToQbo($qboModule, $vtModule, $service_id);
                        if ($res4["code"] != "Succeed") {
                            return $res4;
                        }
                    }
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateFieldForQBO($dataInputs)
    {
        try {
            $mappedField = $dataInputs["mappedField"];
            $qboField = $mappedField["qb_field"];
            if ($qboField == "BillAddr.Line" || $qboField == "ShipAddr.Line") {
                return parent::updateAddressFieldForQBO($dataInputs);
            }
            if ($qboField == "BillEmail.Address") {
                return $this->updateBillEmailAddressFieldForQBO($dataInputs);
            }
            return parent::updateFieldForQBO($dataInputs);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateBillEmailAddressFieldForQBO($dataInputs)
    {
        try {
            global $adb;
            $recordModel = $dataInputs["recordModel"];
            $changedData = $dataInputs["changedData"];
            $mappedField = $dataInputs["mappedField"];
            $vtField = $mappedField["vt_field"];
            $qboField = $mappedField["qb_field"];
            $code = "Succeed";
            $error = "";
            $sql = "SELECT VVL2.vt_id, VMF.vt_module, VMF.vt_field\r\n                    FROM vtiger_vteqbolinks VVL1\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL1.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    INNER JOIN vtiger_vteqbolinks VVL2 ON VVL1.qb_id=VVL2.qb_id\r\n                    INNER JOIN vtiger_crmentity VC2 ON VVL2.vteqbolinkid = VC2.crmid AND VC2.deleted=0\r\n                    INNER JOIN vteqbo_mapping_fields VMF ON VVL2.vt_module = VMF.vt_module\r\n                    WHERE VVL1.vt_id=? AND VVL1.vt_module='Accounts'\r\n                    AND VVL2.qb_module IN ('Customer', 'Company')\r\n                    AND VMF.qb_field='PrimaryEmailAddr.Address' AND VMF.is_active=1\r\n                    LIMIT 0, 1;";
            $res = $adb->pquery($sql, array($recordModel->get("account_id")));
            if ($adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $recordId = $row["vt_id"];
                    $vt_module = $row["vt_module"];
                    $vt_field = $row["vt_field"];
                    $recordModel2 = Vtiger_Record_Model::getInstanceById($recordId, $vt_module);
                    $value = $recordModel2->get($vt_field);
                    if (!empty($value)) {
                        $value = html_entity_decode($value, ENT_QUOTES);
                        $changedData = $this->setValueForQboRecord($changedData, $qboField, $value);
                    }
                }
            }
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkConflictDataForOtherFieldsOnQBO($recordModel, $obj, $changedData, $mappedFields, $latestRecord)
    {
        try {
            $code = "Succeed";
            $error = "";
            $res = $this->getItemsFromQBORecord($recordModel, $obj);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $mappedTaxes = $this->getAllMappedTaxes();
            $itemsOnQBO = $res["items"];
            $itemsOnLatest = $latestRecord->items;
            $isConflict = false;
            foreach ($itemsOnQBO as $item) {
                $isFound = false;
                foreach ($itemsOnLatest as $index => $data) {
                    if (floatval($item["id"]) == floatval($data->id) && floatval($item["productid"]) == floatval($data->productid) && floatval($item["quantity"]) == floatval($data->quantity) && floatval($item["listprice"]) == floatval($data->listprice) && floatval($item["discount_percent"]) == floatval($data->discount_percent) && floatval($item["discount_floatval(amount"]) == floatval($data->discount_amount) && $item["comment"] == $data->comment) {
                        $isFound = true;
                        foreach ($mappedTaxes as $tax) {
                            $taxName = $tax["taxname"];
                            if (floatval($item[$taxName]) != floatval($data->{$taxName})) {
                                $isFound = false;
                                break;
                            }
                        }
                    }
                }
                if ($isFound == false) {
                    $isConflict = true;
                    break;
                }
            }
            if ($isConflict) {
                unset($changedData["TxnTaxDetail"]);
                unset($changedData["Line"]);
                unset($changedData["TotalAmt"]);
                unset($changedData["Deposit"]);
                unset($changedData["Balance"]);
            }
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkConflictDataOnItemsOfVtiger($crmid, $moduleName)
    {
        global $adb;
        $mappedInfo = $this->getMappedInfoByVtigerRecord($crmid, $moduleName);
        $isConflict = false;
        $mappedTaxes = $this->getAllMappedTaxes();
        $latestRecord = json_decode(html_entity_decode($mappedInfo["latest_value"]));
        $itemsOnLatest = $latestRecord->items;
        $sql = "SELECT vi.*, VVL.qb_id, VP.productname, VS.servicename\r\n            FROM vtiger_inventoryproductrel vi\r\n            LEFT JOIN vtiger_vteqbolinks VVL ON vi.productid = VVL.vt_id AND (VVL.vt_module = 'Products' OR VVL.vt_module = 'Services')\r\n            LEFT JOIN vtiger_products VP ON VP.productid = vi.productid\r\n            LEFT JOIN vtiger_service VS ON VS.serviceid = vi.productid\r\n            INNER JOIN vtiger_crmentity VC1 ON VVL.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n            WHERE vi.id = ?";
        $res = $adb->pquery($sql, array($crmid));
        if (0 < $adb->num_rows($res)) {
            while ($row = $adb->fetchByAssoc($res)) {
                $isFound = false;
                foreach ($itemsOnLatest as $index => $data) {
                    if (floatval($row["id"]) == floatval($data->id) && floatval($row["productid"]) == floatval($data->productid) && floatval($row["quantity"]) == floatval($data->quantity) && floatval($row["listprice"]) == floatval($data->listprice) && floatval($row["discount_percent"]) == floatval($data->discount_percent) && floatval($row["discount_amount"]) == floatval($data->discount_amount) && $row["comment"] == $data->comment) {
                        $isFound = true;
                        foreach ($mappedTaxes as $tax) {
                            $taxName = $tax["taxname"];
                            if (floatval($row[$taxName]) != floatval($data->{$taxName})) {
                                $isFound = false;
                                break;
                            }
                        }
                        if ($isFound) {
                            break;
                        }
                    }
                }
                if ($isFound == false) {
                    $isConflict = true;
                    break;
                }
            }
        }
        return $isConflict;
    }
    public function updateCustomInfoBeforeSaving($default)
    {
        global $adb;
        $latest_value = json_decode($default["latest_value"]);
        $recordId = $latest_value->id;
        $moduleName = $latest_value->record_module;
        $isConflict = $this->checkConflictDataOnItemsOfVtiger($recordId, $moduleName);
        $currentItems = $latest_value->items;
        if ($isConflict == false || empty($currentItems)) {
            $sql = "SELECT VI.*\r\n                FROM vtiger_inventoryproductrel VI\r\n                INNER JOIN vtiger_crmentity VC ON VI.productid=VC.crmid AND VC.deleted=0\r\n                WHERE VI.id = ?";
            $res = $adb->pquery($sql, array($recordId));
            if (0 < $adb->num_rows($res)) {
                $items = array();
                while ($row = $adb->fetchByAssoc($res)) {
                    $items[] = $row;
                }
                $latest_value->items = $items;
            }
            $default["latest_value"] = json_encode($latest_value);
        }
        return parent::updateCustomInfoBeforeSaving($default);
    }
    public function getItemsFromQBORecord($recordModel, $record)
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $qboVersion = $this->getQBOVersion();
            $crmid = $recordModel->getId();
            $res = $this->getDiscountItemIdOnQBO();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $discountItemId = $res["result"];
            $mappedTaxes = $this->getAllMappedTaxes();
            $sequence_no = 0;
            $subTotoal = floatval($this->getValueFromQboRecord($record, "SubTotalLineDetail"));
            $discountPercent = floatval($this->getValueFromQboRecord($record, "DiscountLineDetailPercent"));
            $discountAmount = floatval($this->getValueFromQboRecord($record, "DiscountLineDetailAmount"));
            $oldProduct = 0;
            $oldSequence = 0;
            $oldAmount = 0;
            $realSubTotal = 0;
            $oldTotalTaxItem = 0;
            $items = array();
            $idx = -1;
            foreach ($record->Line as $line) {
                if ($line->DetailType == "SalesItemLineDetail") {
                    $salesItemLineDetail = $line->SalesItemLineDetail;
                    if ($salesItemLineDetail->ItemRef != "SHIPPING_ITEM_ID") {
                        $productid = $salesItemLineDetail->ItemRef;
                        if ($productid != $discountItemId) {
                            $oldProduct = 0;
                            $oldSequence = 0;
                            $res = $this->getIdOfVtigerItem($productid);
                            if ($res["code"] != "Succeed") {
                                return $res;
                            }
                            $productid = $res["result"];
                            if (0 < $productid) {
                                $oldProduct = $productid;
                                $sequence_no = $line->LineNum;
                                $oldSequence = $sequence_no;
                                $quantity = floatval($salesItemLineDetail->Qty);
                                $listprice = floatval($salesItemLineDetail->UnitPrice);
                                if ($quantity == 0 && $listprice == 0) {
                                    $quantity = 1;
                                    $listprice = floatval($line->Amount);
                                }
                                $oldAmount = $quantity * $listprice;
                                $comment = $line->Description;
                                $discount_amount = 0;
                                $discount_percent = 0;
                                $item = array("id" => $crmid, "productid" => $productid, "sequence_no" => $sequence_no, "quantity" => $quantity, "listprice" => $listprice, "discount_percent" => $discount_amount, "discount_amount" => $discount_percent, "comment" => $comment);
                                $taxRef = $line->SalesItemLineDetail->TaxCodeRef;
                                $totalTaxItem = 0;
                                if ($qboVersion == "US") {
                                    foreach ($mappedTaxes as $tax) {
                                        $val = 0;
                                        if ($taxRef == "TAX") {
                                            $val = $this->getTaxValueById($record, $tax["qb_tax_id"]);
                                        }
                                        $totalTaxItem += floatval($val);
                                        $item[$tax["taxname"]] = $val;
                                    }
                                } else {
                                    if ($qboVersion == "AUS") {
                                        $item["cf_taxcode_in_aus"] = $taxRef;
                                        if (!empty($taxRef)) {
                                            foreach ($mappedTaxes as $tax) {
                                                if ($tax["taxcode_id"] == $taxRef) {
                                                    $val = $this->getTaxValueById($record, $tax["qb_tax_id"]);
                                                } else {
                                                    $val = 0;
                                                }
                                                $totalTaxItem += floatval($val);
                                                $item[$tax["taxname"]] = $val;
                                            }
                                        }
                                    }
                                }
                                $items[] = $item;
                                $idx++;
                                $oldTotalTaxItem = $totalTaxItem;
                                $realSubTotal += $oldAmount * (100 + $oldTotalTaxItem) / 100;
                                $sequence_no++;
                            }
                        } else {
                            if ($oldProduct != 0) {
                                $da = floatval($line->Amount);
                                $da = abs($da);
                                if ($recordModel->get("hdnTaxType") == "individual") {
                                    $da1 = 0;
                                    if ($record->ApplyTaxAfterDiscount == "true") {
                                        if ($discountPercent != 0) {
                                            $da1 = $da + ($oldAmount - $da) * $discountPercent / 100;
                                        } else {
                                            if ($discountAmount != 0) {
                                                $da1 = $da + $discountAmount * ($oldAmount - $da) / $subTotoal;
                                            }
                                        }
                                    }
                                    $realSubTotal -= ($da + $da1) * (100 + $oldTotalTaxItem) / 100;
                                }
                                $items[$idx]["discount_amount"] = $da;
                                $oldProduct = 0;
                                $oldSequence = 0;
                            }
                        }
                    }
                }
            }
            return array("code" => $code, "error" => $error, "items" => $items, "subTotal" => $realSubTotal);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function removeBlankFields($changedData, $mappedFields)
    {
        $changedData = parent::removeBlankFields($changedData, $mappedFields);
        if (empty($changedData["TxnTaxDetail"]["TaxLine"])) {
            $changedData["TxnTaxDetail"] = array("TotalTax" => 0, "TxnTaxCodeRef" => "3", "TaxLine" => array());
            unset($changedData["TxnTaxDetail"]);
        }
        return $changedData;
    }
}

?>