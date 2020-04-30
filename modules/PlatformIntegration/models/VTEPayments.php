<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_VTEPayments_Model extends PlatformIntegration_Engine_Model
{
    public function insertIntoVtiger($record, $mappedFields, $vtModule = "", $vt_fields = false)
    {
        try {
            global $adb;
            $error = "";
            $sent_data = json_encode($record);
            $received_data = "";
            $to_id = "";
            $code = "Succeed";
            $lines = $record->Line;
            if (!is_array($lines)) {
                $record->Line = array($lines);
            }
            if (empty($lines)) {
            } else {
                $res = $this->getQboApi();
                if ($res["code"] != "Succeed") {
                } else {
                    $qboApi = $res["result"];
                    if (intval($qboApi["sync2vt"]) != 1) {
                        $res = array();
                        $res["code"] = $code;
                        $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_VTIGER_IS_DISABLED", "PlatformIntegration");
                        $res["error"] = $error;
                    } else {
                        if (empty($vtModule)) {
                            $vtModule = $mappedFields[0]["vt_module"];
                        }
                        $qbModule = $mappedFields[0]["qb_module"];
                        $res = $this->syncParentRecordToVtiger($record);
                        if ($res["code"] != "Succeed") {
                            $code = $res["code"];
                            $error = $res["error"];
                        } else {
                            if (!$vt_fields) {
                                $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
                            }
                            foreach ($record->Line as $line) {
                                $res = $this->insertPaymentRecord($record, $line, $vtModule, $mappedFields, $vt_fields);
                                if ($res["code"] != "Succeed") {
                                    $code = $res["code"];
                                    $error = $res["error"];
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $code = "Failed";
            $error = $ex->getMessage();
        } finally {
            if ($code == "Failed") {
                $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "QB2VT", "action_type" => "INSERT", "vteqbolog_status" => $code, "message" => $error, "qb_module" => $qbModule, "qb_id" => $record->Id, "vt_module" => $vtModule, "vt_id" => $to_id, "sent_data" => $sent_data, "received_data" => $received_data));
            }
        }
    }
    public function updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule = "", $vt_fields = false)
    {
        try {
            global $adb;
            $error = "";
            $code = "Succeed";
            $res = $this->getQboApi();
            if ($res["code"] != "Succeed") {
            } else {
                $qboApi = $res["result"];
                if (intval($qboApi["sync2vt"]) != 1) {
                    $res = array();
                    $res["code"] = $code;
                    $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_VTIGER_IS_DISABLED", "PlatformIntegration");
                    $res["error"] = $error;
                } else {
                    $moduleName = $this->moduleName;
                    $received_data = "";
                    if (empty($vtModule)) {
                        $vtModule = $mappedInfo["vt_module"];
                    }
                    $qbModule = $mappedFields[0]["qb_module"];
                    $vtId = $mappedInfo["vt_id"];
                    $res = $this->syncParentRecordToVtiger($record);
                    if ($res["code"] != "Succeed") {
                        $code = $res["code"];
                        $error = $res["error"];
                    } else {
                        if (empty($vt_fields)) {
                            $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
                        }
                        if (!is_array($record->Line)) {
                            $record->Line = array($record->Line);
                        }
                        $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
                        if (class_exists($vtModuleClass)) {
                            $obj = new $vtModuleClass($moduleName);
                        } else {
                            $obj = new PlatformIntegration_Engine_Model($moduleName);
                        }
                        $mappedInfos = $obj->getMappedInfoByQboRecord($record->Id, $qbModule, true);
                        foreach ($mappedInfos as $mappedInfo) {
                            $found = false;
                            $vtId = $mappedInfo["vt_id"];
                            foreach ($record->Line as $line) {
                                $qboTxnType = $this->getValueFromQboRecord($line, "LinkedTxn.TxnType");
                                if ($qboTxnType != "Invoice") {
                                    continue;
                                }
                                $realValue = $this->getValueFromQboRecord($line, "LinkedTxn.TxnId");
                                $amount = floatval($line->Amount);
                                if ($amount <= 0) {
                                    continue;
                                }
                                $latestRecord = json_decode(html_entity_decode($mappedInfo["latest_value"]));
                                $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                                $recordModel->set("id", $vtId);
                                $recordModel->set("mode", "edit");
                                $recordModel->set("amount_paid", $amount);
                                $recordModel->set("payment_status", "Paid");
                                $sql1 = "SELECT VI.invoiceid, VI.accountid\r\n                        FROM vtiger_vteqbolinks VV\r\n                        INNER JOIN vtiger_crmentity VC ON VV.vteqbolinkid=VC.crmid AND VC.deleted=0\r\n                        INNER JOIN vtiger_invoice VI ON VV.vt_id=VI.invoiceid\r\n                        INNER JOIN vtiger_crmentity VC2 ON VC2.crmid=VI.invoiceid AND VC2.deleted=0\r\n                        WHERE VV.qb_module='Invoice' AND VV.qb_id=? LIMIT 0, 1";
                                $res1 = $adb->pquery($sql1, array($realValue));
                                if ($adb->num_rows($res1)) {
                                    while ($row1 = $adb->fetchByAssoc($res1)) {
                                        $invoiceId = $row1["invoiceid"];
                                        $accountId = $row1["accountid"];
                                        $recordModel->set("invoice", $invoiceId);
                                        $recordModel->set("organization", $accountId);
                                    }
                                }
                                if ($invoiceId == $latestRecord->invoice) {
                                    $found = true;
                                    foreach ($mappedFields as $mappedField) {
                                        if ($mappedField["vt_field"] != "amount_paid" && $mappedField["vt_field"] != "invoice") {
                                            $dataInputs = array();
                                            $dataInputs["recordModel"] = $recordModel;
                                            $dataInputs["record"] = $record;
                                            $dataInputs["mappedField"] = $mappedField;
                                            $dataInputs["vt_fields"] = $vt_fields;
                                            $res = $this->updateFieldForVtigerModel($dataInputs);
                                            if ($res["code"] != "Succeed") {
                                                $code = $res["code"];
                                                $error = $res["error"];
                                            } else {
                                                $recordModel = $res["result"];
                                            }
                                        }
                                    }
                                    $sql2 = "SELECT qb_name FROM vteqbo_picklist_fields WHERE qb_source_module='DepositToAccount' AND qb_value=? LIMIT 0, 1";
                                    $res2 = $adb->pquery($sql2, array($recordModel->get("description")));
                                    if ($adb->num_rows($res2)) {
                                        while ($row2 = $adb->fetchByAssoc($res2)) {
                                            $description = $row2["qb_name"];
                                            $recordModel->set("description", $description);
                                        }
                                    }
                                    $recordModel->set("cf_do_not_create_queue_for_vteqbo", true);
                                    $recordModel->save();
                                    $to_id = $recordModel->getId();
                                    $received_data = json_encode($recordModel->getData());
                                    $dataInput = array("qb_module" => $mappedInfo["qb_module"], "qb_id" => $record->Id, "vt_module" => $vtModule, "vt_id" => $vtId, "latest_value" => $received_data, "id" => $mappedInfo["id"], "latest_update" => $record->MetaData->LastUpdatedTime, "latest_update_vt" => $recordModel->get("modifiedtime"));
                                    $this->saveMappingInfo($dataInput);
                                    $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "QB2VT", "action_type" => "UPDATE", "vteqbolog_status" => $code, "message" => $error, "qb_id" => $mappedInfo["qb_id"], "qb_module" => $mappedInfo["qb_module"], "vt_module" => $vtModule, "vt_id" => $mappedInfo["vt_id"], "sent_data" => json_encode($record), "received_data" => $received_data));
                                    break;
                                } else {
                                    $isExisted = false;
                                    foreach ($mappedInfos as $mappedInfo2) {
                                        $latestRecord2 = json_decode(html_entity_decode($mappedInfo2["latest_value"]));
                                        $vtId2 = $latestRecord2->invoice;
                                        if ($invoiceId == $vtId2) {
                                            $isExisted = true;
                                            break;
                                        }
                                    }
                                    if ($isExisted == false) {
                                        $res = $this->insertPaymentRecord($record, $line, $vtModule, $mappedFields, $vt_fields);
                                        if ($res["code"] != "Succeed") {
                                            $code = $res["code"];
                                            $error = $res["error"];
                                        }
                                    }
                                }
                            }
                            if ($found == false) {
                                $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                                $recordModel->delete();
                                $sql3 = "SELECT VVL.vteqbolinkid\r\n                    FROM vtiger_vteqbolinks VVL\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.vteqbolinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE VVL.vt_module=? AND VVL.vt_id=? LIMIT 0, 1";
                                $res3 = $adb->pquery($sql3, array($vtModule, $vtId));
                                if ($adb->num_rows($res3)) {
                                    while ($row3 = $adb->fetchByAssoc($res3)) {
                                        $vteqbolinkid = $row3["vteqbolinkid"];
                                        $recordModel = Vtiger_Record_Model::getInstanceById($vteqbolinkid, "PlatformIntegrationLinks");
                                        $recordModel->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $code = "Failed";
            $error = $ex->getMessage();
        } finally {
            if ($code == "Failed") {
                $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "QB2VT", "action_type" => "UPDATE", "vteqbolog_status" => $code, "message" => $error, "qb_id" => $mappedInfo["qb_id"], "qb_module" => $mappedInfo["qb_module"], "vt_module" => $vtModule, "vt_id" => $mappedInfo["vt_id"], "sent_data" => json_encode($record), "received_data" => $received_data));
            }
        }
    }
    public function insertPaymentRecord($record, $line, $vtModule, $mappedFields, $vt_fields)
    {
        global $adb;
        $error = "";
        $sent_data = json_encode($record);
        $received_data = "";
        $to_id = "";
        $code = "Succeed";
        if (empty($vtModule)) {
            $vtModule = $mappedFields[0]["vt_module"];
        }
        $qbModule = $mappedFields[0]["qb_module"];
        $amount = floatval($line->Amount);
        if ($amount <= 0) {
            return array("code" => $code, "error" => $error);
        }
        $recordModel = Vtiger_Record_Model::getCleanInstance($vtModule);
        $recordModel->set("amount_paid", $amount);
        $recordModel->set("payment_status", "Paid");
        foreach ($mappedFields as $mappedField) {
            if ($mappedField["vt_field"] != "amount_paid" && $mappedField["vt_field"] != "invoice") {
                $dataInputs = array();
                $dataInputs["recordModel"] = $recordModel;
                $dataInputs["record"] = $record;
                $dataInputs["mappedField"] = $mappedField;
                $dataInputs["vt_fields"] = $vt_fields;
                $res = $this->updateFieldForVtigerModel($dataInputs);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $recordModel = $res["result"];
            }
        }
        $realValue = $this->getValueFromQboRecord($line, "LinkedTxn.TxnId");
        $sql1 = "SELECT VI.invoiceid, VI.accountid\r\n                        FROM vtiger_vteqbolinks VV\r\n                        INNER JOIN vtiger_crmentity VC ON VV.vteqbolinkid=VC.crmid AND VC.deleted=0\r\n                        INNER JOIN vtiger_invoice VI ON VV.vt_id=VI.invoiceid\r\n                        INNER JOIN vtiger_crmentity VC2 ON VC2.crmid=VI.invoiceid AND VC2.deleted=0\r\n                        WHERE VV.qb_module='Invoice' AND VV.qb_id=? LIMIT 0, 1";
        $res1 = $adb->pquery($sql1, array($realValue));
        if ($adb->num_rows($res1)) {
            while ($row = $adb->fetchByAssoc($res1)) {
                $invoiceId = $row["invoiceid"];
                $accountId = $row["accountid"];
                $recordModel->set("invoice", $invoiceId);
                $recordModel->set("organization", $accountId);
            }
        }
        $sql2 = "SELECT qb_name FROM vteqbo_picklist_fields WHERE qb_source_module='DepositToAccount' AND qb_value=? LIMIT 0, 1";
        $res2 = $adb->pquery($sql2, array($recordModel->get("description")));
        if ($adb->num_rows($res2)) {
            while ($row = $adb->fetchByAssoc($res2)) {
                $description = $row["qb_name"];
                $recordModel->set("description", $description);
            }
        }
        $recordModel->set("cf_do_not_create_queue_for_vteqbo", true);
        $recordModel->save();
        $to_id = $recordModel->getId();
        $received_data = json_encode($recordModel->getData());
        $dataInput = array("qb_module" => $qbModule, "qb_id" => $record->Id, "vt_module" => $vtModule, "vt_id" => $recordModel->getId(), "latest_value" => $received_data, "latest_update" => $record->MetaData->LastUpdatedTime);
        $this->saveMappingInfo($dataInput);
        $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "QB2VT", "action_type" => "INSERT", "vteqbolog_status" => $code, "message" => $error, "qb_module" => $qbModule, "qb_id" => $record->Id, "vt_module" => $vtModule, "vt_id" => $to_id, "sent_data" => $sent_data, "received_data" => $received_data));
        return array("code" => $code, "error" => $error);
    }
    public function syncParentRecordToVtiger($record)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $adb;
            $moduleName = $this->moduleName;
            if (!is_array($record->Line)) {
                $record->Line = array($record->Line);
            }
            $qboModule = "Invoice";
            $vtModule = "Invoice";
            $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
            if (class_exists($vtModuleClass)) {
                $obj = new $vtModuleClass($moduleName);
            } else {
                $obj = new PlatformIntegration_Engine_Model($moduleName);
            }
            foreach ($record->Line as $line) {
                $qboTxnType = $this->getValueFromQboRecord($line, "LinkedTxn.TxnType");
                if ($qboTxnType == "Invoice") {
                    $qboId = $this->getValueFromQboRecord($line, "LinkedTxn.TxnId");
                    $res = $this->getMappedInfoByQboRecord($qboId, $qboModule);
                    if (count($res) == 0) {
                        $res = $obj->syncQboToVtiger($qboModule, $vtModule, $qboId);
                        if ($res["code"] != "Succeed") {
                            return $res;
                        }
                    }
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
}

?>