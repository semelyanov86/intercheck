<?php

require_once "modules/PlatformIntegration/models/PlatformCustomer.php";
include_once 'include/Webservices/Utils.php';

class PlatformIntegration_Contacts_Model extends PlatformIntegration_Engine_Model
{
    public function __construct()
    {
        $otherUnusedFields = array("contact_id", "imagename", "account_id", "assigned_user_id", "isconvertedfromlead");
        $this->unusedFields = array_merge($this->unusedFields, $otherUnusedFields);
    }
    public function preSaveModel($recordModel, $record)
    {
        $account_id = "";
        $qboId = $record->Id;
        $qboModule = "Company";
        $mappedInfo = $this->getMappedInfoByPlatformRecord($qboId, $qboModule);
        $recordModel->set("account_id", $mappedInfo["vt_id"]);
        return parent::preSaveModel($recordModel, $record);
    }
    public function syncParentRecordToVtiger($record)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $adb;
            $moduleName = $this->moduleName;
            $qboId = $record->Id;
            $qboModule = "Company";
            $vtModule = "Accounts";
            $mappedInfo = $this->getMappedInfoByPlatformRecord($qboId, $qboModule);
            if (count($mappedInfo) == 0) {
                $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
                if (class_exists($vtModuleClass)) {
                    $obj = new $vtModuleClass($moduleName);
                } else {
                    $obj = new PlatformIntegration_Engine_Model($moduleName);
                }
                $res = $obj->syncPlatformToVtiger($qboModule, $vtModule, $qboId);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncParentRecordToPlatform($recordModel)
    {
        return parent::syncParentRecordToPlatform($recordModel);
/*        try {
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
                if ($accountsModel->get("cf_sync_to_platformintegration") != "1") {
                    $accountsModel->set("cf_sync_to_platformintegration", 1);
                    $accountsModel->set("mode", "edit");
                    $accountsModel->save();
                }
                $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
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
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }*/
    }
    public function updateFieldForVtigerModel($dataInputs)
    {
        try {
            $mappedField = $dataInputs["mappedField"];
            $qboField = $mappedField["platform_field"];
            if ($qboField == "BillAddr.Line" || $qboField == "ShipAddr.Line") {
                return $this->updateAddressFieldForVtigerModel($dataInputs);
            }
            return parent::updateFieldForVtigerModel($dataInputs);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateFieldForPlatform($dataInputs)
    {
        try {
            $mappedField = $dataInputs["mappedField"];
            $qboField = $mappedField["platform_field"];
            if ($qboField == "BillAddr.Line" || $qboField == "ShipAddr.Line") {
                return $this->updateAddressFieldForPlatform($dataInputs);
            }
            return parent::updateFieldForPlatform($dataInputs);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkMergerExistedRecordOnVtiger($record, $mappedFields, $vtModule = "", $vt_fields = false)
    {
        try {
            $qb_fields = array("PrimaryEmailAddr.Address");
            $vtId = $this->checkMergerExistedRecordOnVtigerByFields($record, $mappedFields, $vtModule, $qb_fields);
            if ($vtId != false) {
                return $vtId;
            }
            $qb_fields = array("FamilyName", "GivenName");
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
            $fieldName = array("email");
            $res = $this->checkExistanceContactByEmail($recordModel, $mappedFields, $qboModule, $vtModule, $fieldName);
            if ($res != false) {
                return $res;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function checkExistanceContactByEmail($recordModel, $mappedFields, $platformModule, $vtModule, $platform_field)
    {
        $conditions = array("platform_module" => $platformModule, "platform_field" => $platform_field);
        $res = $this->getInfoFromMappedFields($mappedFields, $conditions);

        if (!empty($res)) {
            $vtField = $res["vt_field"];
            $vtValue = $recordModel->get($vtField);
        } else {
            return false;
        }
        $otherConditions = array(
            $vtField => $vtValue
        );

        $res = $this->getRecordByPlatformId($platformModule, "", $otherConditions, true);
        if ($res["code"] != "Succeed") {
            return false;
        }
        return $res["result"];
    }
    public function getCustomSqlForCheckMergerExisted($record)
    {
        $platformId = $record->Id;
        return " INNER JOIN vtiger_account VA ON T1.accountid = VA.accountid\r\n                INNER JOIN vtiger_platformintegrationlinks VVL ON VVL.vt_module='Accounts' AND VVL.vt_id=VA.accountid AND VVL.platform_id=" . $platformId . "\r\n                INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0";
    }
    public function checkExistedAccountByContact($platformCustomerId, $vtContactId)
    {
        try {
            global $adb;
            $sql = "SELECT VC.contactid\r\n                    FROM vtiger_contactdetails VC\r\n                    INNER JOIN vtiger_crmentity VC1 ON VC.contactid = VC1.crmid AND VC1.deleted = 0\r\n                    INNER JOIN vtiger_account VA ON VC.accountid = VA.accountid\r\n                    INNER JOIN vtiger_crmentity VC2 ON VA.accountid = VC2.crmid AND VC2.deleted = 0\r\n                    INNER JOIN vtiger_platformintegrationlinks VVL ON VVL.vt_module='Accounts' AND VVL.vt_id=VA.accountid\r\n                    INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                    WHERE VVL.platform_id=? AND VC.contactid=? LIMIT 0, 1";
            $res = $adb->pquery($sql, array($platformCustomerId, $vtContactId));
            if (0 < $adb->num_rows($res)) {
                return $vtContactId;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            $code = "Succeed";
            $error = "";
            if (isset($changedData['country']) && $changedData['country']) {
                $changedData['country'] = Vtiger_Functions::getCRMRecordLabel(($changedData['country']));
            }
            if ($recordModel->get('cf_contacttype') == 'FTD') {
                $changedData['is_ftd'] = 1;
            } else {
                $changedData['is_ftd'] = 0;
            }
            $changedData['vtiger_crm_id'] = vtws_getWebserviceEntityId('Contacts',  $recordModel->getId());
            // TODO: add sale_status_id
            $changedData = $this->removeBlankFields($changedData, $mappedFields);
            $changedData = $this->trimFieldsWithMaxLength($changedData, $mappedFields);
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function addMetaForModule($resultData, $platformModule, $recordModel, $dataService)
    {
        if ($platformModule == 'User') {
            $vt_fields = $this->getAllFieldsOfVTModule('Contacts');
            $mappedFields = $this->getMappedFields('UserMetaData', 'Contacts');
            $data = array();
            foreach ($mappedFields as $mappedField) {
                $dataInputs = array();
                $dataInputs["recordModel"] = $recordModel;
                $dataInputs["changedData"] = $data;
                $dataInputs["mappedField"] = $mappedField;
                $dataInputs["vt_fields"] = $vt_fields;
                $res2 = $this->updateFieldForPlatform($dataInputs);
                if ($res2["code"] != "Succeed") {
                    $code = $res2["code"];
                    $error = $res2["error"];
                } else {
                    $data = $res2["result"];
                }
            }
            $res5 = $this->preSaveToPlatform($recordModel, $data, $mappedFields);
            try {
                $data = $res5["result"];
                $data['user_id'] = $resultData['id'];
                $resultingObj = $dataService->request('POST', '/api/user/meta/create', [
                    'form_params' => $data
                ]);
                $resultMetaData = json_decode($resultingObj->getBody()->getContents(), true);
                $platformId = $resultMetaData['data']['id'];
                $received_data = $resultMetaData['data'];
                $dataInput = array("platform_module" => 'UserMetaData', "platform_id" => $platformId, "vt_module" => 'Contacts', "vt_id" => $recordModel->getId(), "latest_value" => json_encode($recordModel->getData()), "latest_update" => $received_data['updated_at'], "latest_update_vt" => $recordModel->get("modifiedtime"));
                $this->saveMappingInfo($dataInput);
//                $this->updateLastDateSynched($recordModel);
                return $received_data;
//                                        var_dump($received_data, 'contacts227');die;
            } catch (Exception $ex) {
//                var_dump($ex->getMessage(), $data, $resultData, 'contacts229');die;
                $code = "Failed";
                $error = $ex->getCode();
                $received_data = $ex->getMessage();
//                $this->updateStatusOfQueue($platformintegrationqueueid, $code);
                $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "VT2Platform", "action_type" => "CREATE", "platformintegrationlog_status" => $code, "message" => $error, "vt_id" => $recordModel->getId(), "vt_module" => 'Contacts', "platform_module" => 'UserMetaData', "platform_id" => $resultData['id'], "sent_data" => json_encode($recordModel->getData()), "received_data" => $received_data));
                return false;
            }
        } else {
            return false;
        }
    }

    public function getMappedInfoByVtigerRecord($vtId, $vtModule, $isGetMultiple = false)
    {
        global $adb;
        $mappedInfo = array();
        $sql = "SELECT VVL.* FROM vtiger_platformintegrationlinks VVL \r\n                INNER JOIN vtiger_crmentity VC1 ON VVL.platformintegrationlinkid = VC1.crmid AND VC1.deleted=0\r\n                WHERE VVL.vt_id IN (" . $vtId . ") AND VVL.vt_module=? AND VVL.platform_module=?";
        if (!$isGetMultiple) {
            $sql .= " LIMIT 1";
        }
        $res = $adb->pquery($sql, array($vtModule, 'User'));
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
}

?>