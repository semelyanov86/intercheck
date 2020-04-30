<?php

require_once "modules/PlatformIntegration/models/Base.php";
require_once "modules/PlatformIntegration/helpers/vendor/autoload.php";
require_once "vtlib/Vtiger/Net/Client.php";
use GuzzleHttp\Client;
class PlatformIntegration_Engine_Model extends PlatformIntegration_Base_Model
{
    protected $baseUrl = "http://feature-64108.adssupply.ln6.tempurl.info/";
    protected $scope = "*";
    protected $RedirectURI = "https://www.vtexperts.com/files/OAuth_2/OAuth2PHPExample.php";
    public function syncAllPicklists()
    {
        try {
            global $adb;
            $error = "";
            $code = "Succeed";
            $result = array();
            $resultQBOs = array();
            $moduleInfos = array();
            $sql = "SELECT * FROM platformintegration_modules \r\n                    WHERE (ISNULL(vt_module) OR vt_module = '') \r\n                    AND (ISNULL(tab) OR tab = '') \r\n                    AND (ISNULL(tab_seq) OR tab_seq = '') \r\n                    AND (ISNULL(seq_in_tab) OR seq_in_tab = '')";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                $msgError = vtranslate("LBL_RECORD_NOT_FOUND", "VTEQBO");
                while ($row = $adb->fetchByAssoc($res)) {
                    $res1 = $this->getRecordsFromQbo($row["platform_module"]);
                    if ($res1["code"] != "Succeed" && $res1["error"] != $msgError) {
                        return $res1;
                    }
                    $resultQBO1 = $res1["result"];
                    if (!empty($resultQBO1)) {
                        $resultQBOs[$row["platform_module"]] = $resultQBO1;
                        $moduleInfos[$row["platform_module"]] = $row;
                    }
                }
            }
            $sql = "SELECT * FROM platformintegration_picklist_fields";
            $res = $adb->pquery($sql, array());
            $nr = $adb->num_rows($res);
            if (0 < $nr) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $key = $row["platform_source_module"];
                    $resultQBO = $resultQBOs[$key];
                    if (empty($resultQBO)) {
                        continue;
                    }
                    $moduleInfo = $moduleInfos[$key];
                    foreach ($resultQBO as $record) {
                        $platform_value = $record->Id;
                        $field_name = $moduleInfo["representation_field"];
                        $platform_name = $record->{$field_name};
                        $platform_source_module = $moduleInfo["platform_module"];
                        if ($row["platform_value"] == $platform_value && $row["platform_source_module"] == $platform_source_module) {
                            if ($row["platform_name"] != $platform_name) {
                                $row["platform_name"] = $platform_name;
                                $param = array($platform_name, $row["id"]);
                                $adb->pquery("UPDATE platformintegration_picklist_fields SET platform_name = ? WHERE id = ?", $param);
                            }
                            if ($moduleInfo["platform_module"] == "Account" && $row["platform_type"] != $record->AccountType) {
                                $row["platform_type"] = $record->AccountType;
                                $param = array($record->AccountType, $row["id"]);
                                $adb->pquery("UPDATE platformintegration_picklist_fields SET platform_type = ? WHERE id = ?", $param);
                            }
                            break;
                        }
                    }
                    $result[] = $row;
                }
                foreach ($resultQBOs as $key => $resultQBO) {
                    $moduleInfo = $moduleInfos[$key];
                    foreach ($resultQBO as $record) {
                        $isExisted = false;
                        $platform_value = $record->Id;
                        $field_name = $moduleInfo["representation_field"];
                        $platform_name = $record->{$field_name};
                        $platform_source_module = $moduleInfo["platform_module"];
                        if ($moduleInfo["platform_module"] == "Account") {
                            $accountType = $record->AccountType;
                        } else {
                            $accountType = "";
                        }
                        foreach ($result as $row) {
                            if ($row["platform_value"] == $platform_value && $row["platform_source_module"] == $platform_source_module) {
                                $isExisted = true;
                                break;
                            }
                        }
                        if (!$isExisted) {
                            $param = array("", "", $platform_source_module, $accountType, $platform_value, $platform_name);
                            $adb->pquery("INSERT INTO platformintegration_picklist_fields(platform_module, platform_field, platform_source_module, platform_type, platform_value, platform_name) VALUES (?, ?, ?, ?, ?, ?)", $param);
                            $row = array("platform_module" => "", "platform_field" => "", "platform_source_module" => $platform_source_module, "platform_type" => $accountType, "platform_value" => $platform_value, "platform_name" => $platform_name);
                            $result[] = $row;
                        }
                    }
                }
            } else {
                foreach ($resultQBOs as $key => $resultQBO) {
                    $moduleInfo = $moduleInfos[$key];
                    foreach ($resultQBO as $record) {
                        $platform_value = $record->Id;
                        $field_name = $moduleInfo["representation_field"];
                        $platform_name = $record->{$field_name};
                        $platform_source_module = $moduleInfo["platform_module"];
                        if ($moduleInfo["platform_module"] == "Account") {
                            $accountType = $record->AccountType;
                        } else {
                            $accountType = "";
                        }
                        $param = array("", "", $platform_source_module, $accountType, $platform_value, $platform_name);
                        $adb->pquery("INSERT INTO platformintegration_picklist_fields(platform_module, platform_field, platform_source_module, platform_type, platform_value, platform_name) VALUES (?, ?, ?, ?, ?, ?)", $param);
                        $row = array("platform_module" => "", "platform_field" => "", "platform_source_module" => $platform_source_module, "platform_type" => $accountType, "platform_value" => $platform_value, "platform_name" => $platform_name);
                        $result[] = $row;
                    }
                }
            }
            $this->allPicklists = $result;
            $sql = "SELECT A.vt_module, A.vt_field, B.module_ref\r\n                    FROM platformintegration_mapping_fields A\r\n                    INNER JOIN platformintegration_modules_fields B ON A.platform_module = B.platform_module AND A.platform_field = B.platform_field\r\n                    WHERE B.is_picklist = 1";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $vtField = $row["vt_field"];
                    $vtModule = $row["vt_module"];
                    $vmodule = Vtiger_Module::getInstance($vtModule);
                    if ($vmodule) {
                        $plValues = Vtiger_Util_Helper::getPickListValues($vtField);
                        foreach ($result as $field) {
                            if ($field["platform_source_module"] == $row["module_ref"]) {
                                $realValue = $field["platform_name"];
                                $realValue = html_entity_decode($realValue, ENT_QUOTES);
                                if (in_array($realValue, $plValues) == false) {
                                    $this->addValueToPicklists($vtModule, $vtField, $realValue);
                                }
                            }
                        }
                    }
                }
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getValueFromPicklists($name, $moduleRef = "", $platformModule = "", $qboField = "")
    {
        try {
            $error = "";
            $code = "Succeed";
            $name = trim($name);
            $maxLength = 100;
            $name = substr($name, 0, $maxLength);
            $items = $this->getAllPicklists();
            if (empty($moduleRef)) {
                foreach ($items as $item) {
                    if ($item["platform_name"] == $name && $item["platform_module"] == $platformModule && $item["platform_field"] == $qboField) {
                        return array("code" => $code, "error" => $error, "value" => $item["platform_value"]);
                    }
                }
                foreach ($items as $item) {
                    if ($item["platform_name"] == $name) {
                        return array("code" => $code, "error" => $error, "value" => $item["platform_value"]);
                    }
                }
            } else {
                foreach ($items as $item) {
                    if ($item["platform_name"] == $name && $item["platform_source_module"] == $moduleRef) {
                        return array("code" => $code, "error" => $error, "value" => $item["platform_value"]);
                    }
                }
            }
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            if (empty($moduleRef)) {
                $data = array();
                $data["AccountType"] = "Expense";
                $data["AccountSubType"] = "Utilities";
                $data["Name"] = $name;
                $obj = QuickBooksOnline\API\Facades\FacadeHelper::reflectArrayToObject("Account", $data, true);
                $resultingObj = $dataService->Add($obj);
                $e = $dataService->getLastError();
                if ($e != NULL) {
                    return array("code" => "Failed", "error" => $e->getResponseBody());
                }
                return array("code" => $code, "error" => $error, "value" => $resultingObj->Id);
            }
            $res = $this->getInfoOfQboModule($moduleRef);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $platformModule = $res["result"];
            $otherConditions = "";
            $representation_field = $platformModule["representation_field"];
            if (!empty($representation_field)) {
                $otherConditions = " AND " . $representation_field . " = '" . $name . "'";
            }
            $res = $this->getRecordsFromQbo($moduleRef, "", $otherConditions);
            $items = $res["result"];
            $val = "";
            if (empty($items)) {
                $data = array();
                $defaultValues = json_decode(html_entity_decode($platformModule["default_value"]));
                foreach ($defaultValues as $k => $v) {
                    $data[$k] = $v;
                }
                $data["Name"] = $name;
                $obj = QuickBooksOnline\API\Facades\FacadeHelper::reflectArrayToObject($platformModule["platform_module_table"], $data, true);
                $resultingObj = $dataService->Add($obj);
                $e = $dataService->getLastError();
                if ($e != NULL) {
                    return array("code" => "Failed", "error" => $e->getResponseBody());
                }
                return array("code" => $code, "error" => $error, "value" => $resultingObj->Id);
            } else {
                foreach ($items as $item) {
                    $val = $item->Id;
                    break;
                }
                return array("code" => $code, "error" => $error, "value" => $val);
            }
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncPlatformToVtiger($platformModule, $vtModule, $qboId = "")
    {
        try {
            $error = "";
            $code = "Succeed";
            $mappedFields = $this->getMappedFields($platformModule, $vtModule);
            if (empty($mappedFields)) {
                return array("code" => $code, "error" => vtranslate("MSG_HAVE_NOT_ANY_MAPPED_FIELD", "VTEQBO"));
            }
            $res = $this->getInfoOfPlatformModule($platformModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $platformModuleInfo = $res["result"];
            $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
            $res = $this->getRecordsFromQbo($platformModule, $qboId);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $successed = 0;
            $failed = 0;
            $noChanged = 0;
            foreach ($res["result"] as $record) {
                $mappedInfo = $this->getMappedInfoByPlatformRecord($record->Id, $platformModule);
                if (count($mappedInfo) == 0) {
                    if ($vtModule == "Contacts") {
                        $givenName = $record->GivenName;
                        $familyName = $record->FamilyName;
                        if (empty($givenName) && empty($familyName)) {
                            $noChanged = $noChanged + 1;
                            continue;
                        }
                    }
                    $res3 = $this->insertIntoVtiger($record, $mappedFields, $vtModule, $vt_fields);
                } else {
                    if ($mappedInfo["latest_update"] == $record->MetaData->LastUpdatedTime) {
                        $noChanged = $noChanged + 1;
                        continue;
                    }
                    $res3 = $this->updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                }
                if ($res3["code"] != "Succeed") {
                    $failed = $failed + 1;
                } else {
                    $successed = $successed + 1;
                }
            }
            $error = vtranslate("LBL_SUCCESSED", "VTEQBO");
            $error .= ": " . $successed . ".<br />\n";
            $error .= vtranslate("LBL_FAILED", "VTEQBO");
            $error .= ": " . $failed . ".<br />\n";
            if ($noChanged != 0) {
                $error .= vtranslate("LBL_NO_CHANGED", "VTEQBO");
                $error .= ": " . $noChanged . ".<br />\n";
            }
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncVtigerToPlatform($platformModule, $vtModule, $recordId = "")
    {
        try {
            $error = "";
            $code = "Succeed";
            $mappedFields = $this->getMappedFields($platformModule, $vtModule);
            if (empty($mappedFields)) {
                return array("code" => $code, "error" => vtranslate("MSG_HAVE_NOT_ANY_MAPPED_FIELD", "VTEQBO"));
            }
            $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
            $res = $this->getAllVtigerRecordToSync($vtModule, $recordId);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $successed = 0;
            $failed = 0;
            foreach ($res["result"] as $record) {
                $vtId = $record["crmid"];
                $platformintegrationqueueid = $record["platformintegrationqueueid"];
                $mappedInfo = $this->getMappedInfoByVtigerRecord($vtId, $vtModule);
                $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                if (count($mappedInfo) == 0) {
                    $res3 = $this->insertIntoPlatform($recordModel, $mappedFields, $platformModule, $vt_fields, $platformintegrationqueueid);
                } else {
                    $res3 = $this->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $platformModule, $vt_fields, $platformintegrationqueueid);
                }
                if ($res3["code"] != "Succeed") {
                    $failed = $failed + 1;
                } else {
                    $successed = $successed + 1;
                }
            }
            $error = vtranslate("LBL_SUCCESSED", "VTEQBO");
            $error .= ": " . $successed . ".<br />\n";
            $error .= vtranslate("LBL_FAILED", "VTEQBO");
            $error .= ": " . $failed;
            return array("code" => $code, "error" => $error);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function insertIntoPlatform($recordModel, $mappedFields, $platformModule = "", $vt_fields = false, $platformintegrationqueueid = false)
    {
        try {
            $error = "";
            $sent_data = json_encode($recordModel->getData());
            $received_data = "";
            $platformId = "";
            $code = "Succeed";
            $res = $this->getPlatformApi();
            if ($res["code"] != "Succeed") {
            } else {
                $qboApi = $res["result"];
                if (intval($qboApi["sync2platform"]) != 1) {
                    $res = array();
                    $res["code"] = $code;
                    $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_PLATFORM_IS_DISABLED", "PlatformIntegration");
                    $res["error"] = $error;
                } else {
                    $res = $this->syncParentRecordToPlatform($recordModel);
                    if ($res["code"] != "Succeed") {
                        $code = $res["code"];
                        $error = $res["error"];
                    } else {
                        $res = $this->getDataService();
                        if ($res["code"] != "Succeed") {
                            $code = $res["code"];
                            $error = $res["error"];
                        } else {
                            $dataService = $res["result"];
                            if (empty($platformModule)) {
                                $platformModule = $mappedFields[0]["platform_module"];
                            }
                            $res = $this->getInfoOfPlatformModule($platformModule);
                            if ($res["code"] != "Succeed") {
                                $code = $res["code"];
                                $error = $res["error"];
                            } else {
                                $platformModuleInfo = $res["result"];
                                $vtModule = $mappedFields[0]["vt_module"];
                                $data = array();
                                if (empty($vt_fields)) {
                                    $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
                                }
                                $platformRecord = $this->checkMergerExistedRecordOnPlatform($recordModel, $mappedFields, $platformModule, $vtModule, $vt_fields);
                                $vtId = $recordModel->getId();
                                if ($platformRecord != false) {
                                    $platformId = $platformRecord['id'];
                                    $dataInput = array("platform_module" => $platformModule, "platform_id" => $platformId, "vt_module" => $vtModule, "vt_id" => $vtId, "latest_value" => $received_data, "latest_update" => $platformRecord['updated_at']);
                                    $this->saveMappingInfo($dataInput);
                                    $mappedInfo = $this->getMappedInfoByPlatformRecord($platformId, $platformModule);
                                    $primaryDS = strtolower($qboApi["primary_datasource"]);
                                    $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                                    if ($recordModel->get("cf_sync_to_platformintegration") != "on" && $recordModel->get("cf_sync_to_platformintegration") != "1") {
                                        $recordModel->set("id", $vtId);
                                        $recordModel->set("mode", "edit");
                                        $recordModel->set("cf_sync_to_platformintegration", true);
                                        $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
                                        $recordModel->save();
                                    }
                                    if ($primaryDS == "vtiger") {
                                        $res = $this->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $platformModule, $vt_fields, 0);
                                        if ($res["code"] != "Succeed") {
                                            $code = $res["code"];
                                            $error = $res["error"];
                                        } else {
                                            $this->updateToVtiger($platformRecord, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                                        }
                                    } else {
                                        $res = $this->updateToVtiger($platformRecord, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                                        if ($res["code"] != "Succeed") {
                                            $code = $res["code"];
                                            $error = $res["error"];
                                        } else {
                                            $this->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $platformModule, $vt_fields, 0);
                                        }
                                    }
                                } else {
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
                                    if ($res5["code"] != "Succeed") {
                                        $code = $res5["code"];
                                        $error = $res5["error"];
                                    } else {
                                        try {
                                            $data = $res5["result"];
                                            $resultingObj = $dataService->request('POST', '/api/' . strtolower($platformModule) . '/create', [
                                                'form_params' => $data
                                            ]);
                                            $resultData = json_decode($resultingObj->getBody()->getContents(), true);
                                            $platformId = $resultData['data']['id'];
                                            $received_data = $resultData['data'];
                                            $metaData = $this->addMetaForModule($received_data, $platformModule, $recordModel, $dataService);
                                            if ($metaData) {
                                                $received_data['Meta'] = $metaData;
                                            }
                                            $dataInput = array("platform_module" => $platformModule, "platform_id" => $platformId, "vt_module" => $vtModule, "vt_id" => $recordModel->getId(), "latest_value" => json_encode($recordModel->getData()), "latest_update" => $received_data['updated_at'], "latest_update_vt" => $recordModel->get("modifiedtime"));
//                                            var_dump($dataInput, $received_data, 'engine436');die;
                                            $this->saveMappingInfo($dataInput);
                                            $this->updateLastDateSynched($recordModel);
//                                        var_dump($received_data, 'engine434');die;
                                        } catch (Exception $ex) {
//                                            var_dump($ex->getMessage(), 'engine436');die;
                                            $code = "Failed";
                                            $error = $ex->getCode();
                                            $received_data = $ex->getMessage();
                                            $this->updateStatusOfQueue($platformintegrationqueueid, $code);
                                            $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "VT2Platform", "action_type" => "UPDATE", "platformintegrationlog_status" => $code, "message" => $error, "vt_id" => $recordModel->getId(), "vt_module" => $vtModule, "platform_module" => $platformModule, "platform_id" => $platformId, "sent_data" => $sent_data, "received_data" => $received_data));
                                        }

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
            $this->updateStatusOfQueue($platformintegrationqueueid, $code);
            $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "VT2Platform", "action_type" => "INSERT", "platformintegrationlog_status" => $code, "message" => $error, "vt_module" => $vtModule, "vt_id" => $recordModel->getId(), "platform_module" => $platformModule, "platform_id" => $platformId, "sent_data" => $sent_data, "received_data" => $received_data));
        }
        return array("code" => $code, "error" => $error, "result" => $received_data);
    }
    public function addMetaForModule($resultData, $platformModule, $recordModel, $dataService)
    {
        return false;
    }
    public function syncParentRecordToVtiger($record)
    {
        try {
            return array("code" => "Succeed", "error" => "");
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function syncParentRecordToPlatform($recordModel)
    {
        try {
            return array("code" => "Succeed", "error" => "");
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function insertIntoVtiger($record, $mappedFields, $vtModule = "", $vt_fields = false)
    {
        try {
            $error = "";
            $sent_data = json_encode($record);
            $received_data = "";
            $to_id = "";
            $code = "Succeed";
            $res = $this->getPlatformApi();
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
                    $qbModule = $mappedFields[0]["platform_module"];
                    $res = $this->syncParentRecordToVtiger($record);
                    if ($res["code"] != "Succeed") {
                        $code = $res["code"];
                        $error = $res["error"];
                    } else {
                        if (!$vt_fields) {
                            $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
                        }
                        $vtId = $this->checkMergerExistedRecordOnVtiger($record, $mappedFields, $vtModule, $vt_fields);
                        $qboId = $record->Id;
                        if ($vtId != false) {
                            $dataInput = array("platform_module" => $qbModule, "platform_id" => $qboId, "vt_module" => $vtModule, "vt_id" => $vtId, "latest_value" => $received_data, "latest_update" => $record->MetaData->LastUpdatedTime);
                            $this->saveMappingInfo($dataInput);
                            $mappedInfo = $this->getMappedInfoByPlatformRecord($qboId, $qbModule);
                            $primaryDS = strtolower($qboApi["primary_datasource"]);
                            $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                            if ($recordModel->get("cf_sync_to_qbo") != "on" && $recordModel->get("cf_sync_to_qbo") != "1") {
                                $recordModel->set("id", $vtId);
                                $recordModel->set("mode", "edit");
                                $recordModel->set("cf_sync_to_qbo", true);
                                $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
                                $recordModel->save();
                            }
                            if ($primaryDS == "vtiger") {
                                $res = $this->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $qbModule, $vt_fields, 0);
                                if ($res["code"] != "Succeed") {
                                    $code = $res["code"];
                                    $error = $res["error"];
                                } else {
                                    $this->updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                                }
                            } else {
                                $res = $this->updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                                if ($res["code"] != "Succeed") {
                                    $code = $res["code"];
                                    $error = $res["error"];
                                } else {
                                    $this->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $qbModule, $vt_fields, 0);
                                }
                            }
                        } else {
                            $recordModel = Vtiger_Record_Model::getCleanInstance($vtModule);
                            foreach ($mappedFields as $mappedField) {
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
                            $recordModel->set("cf_sync_to_platformintegration", true);
                            $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
                            $recordModel = $this->preSaveModel($recordModel, $record);
                            $recordModel->save();
                            $res = $this->postSaveModel($recordModel, $record, $mappedFields, false, false);
                            if ($res["code"] != "Succeed") {
                                $recordModel->delete();
                                $code = $res["code"];
                                $error = $res["error"];
                            } else {
                                $to_id = $recordModel->getId();
                                $received_data = json_encode($recordModel->getData());
                                $dataInput = array("platform_module" => $qbModule, "platform_id" => $record['id'], "vt_module" => $vtModule, "vt_id" => $recordModel->getId(), "latest_value" => $received_data, "latest_update" => $record['updated_at']);
                                $this->saveMappingInfo($dataInput);
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $code = "Failed";
            $error = $ex->getMessage();
        } finally {
            $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "Platform2VT", "action_type" => "INSERT", "platformintegrationlog_status" => $code, "message" => $error, "platform_module" => $qbModule, "platform_id" => $record['id'], "vt_module" => $vtModule, "vt_id" => $to_id, "sent_data" => $sent_data, "received_data" => $received_data));
        }
    }
    public function setValueForVtigerField($recordModel, $qboRecord, $mappedField, $vtField, $realValue)
    {
        $recordModel->set($vtField, $realValue);
        return $recordModel;
    }
    public function updateFieldForVtigerModel($dataInputs)
    {
        try {
            $code = "Succeed";
            $error = "";
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $vt_fields = $dataInputs["vt_fields"];
            $vtModule = $mappedField["vt_module"];
            $platformModule = $mappedField["platform_module"];
            $qboField = $mappedField["platform_field"];
            $moduleRef = $mappedField["module_ref"];
            $realValue = $this->getValueFromPlatformRecord($record, $qboField);
            $realValue = html_entity_decode($realValue, ENT_QUOTES);
            $vtField = $mappedField["vt_field"];
            if (empty($realValue)) {
                $recordModel = $this->setValueForVtigerField($recordModel, $record, $mappedField, $vtField, $realValue);
                return array("code" => $code, "error" => $error, "result" => $recordModel);
            }
            $moduleName = $this->getModuleLinkToField($vt_fields, $vtField);
            if (!empty($moduleName) && in_array($moduleName, $this->getAllowedModule())) {
                $realId = $this->getVtigerRecordByName($moduleName, $realValue);
                if (!empty($realId)) {
                    $recordModel = $this->setValueForVtigerField($recordModel, $record, $mappedField, $vtField, $realValue);
                }
                return array("code" => $code, "error" => $error, "result" => $recordModel);
            }
            if ($mappedField["is_picklist"] == "1" && !empty($realValue)) {
                $moduleRef = $mappedField["module_ref"];
                $realValue = $this->getNameFromPicklists($realValue, $platformModule, $qboField, $moduleRef);
                $realValue = html_entity_decode($realValue, ENT_QUOTES);
                $plValues = Vtiger_Util_Helper::getPickListValues($vtField);
                if (in_array($realValue, $plValues) == false) {
                    $this->addValueToPicklists($vtModule, $vtField, $realValue);
                }
            }
            $realValue = $this->convertToVtigerValue($realValue, $mappedField["data_type"]);
            $recordModel = $this->setValueForVtigerField($recordModel, $record, $mappedField, $vtField, $realValue);
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getDisplayNameOfPlatformRecord($id, $platformModule)
    {
        try {
            $code = "Succeed";
            $error = "";
            $res = $this->getInfoOfPlatformModule($platformModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $infoQboModule = $res["result"];
            $res = $this->getRecordByPlatformId($platformModule, $id);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $record = $res["result"];
            $displayField = $infoQboModule["representation_field"];
            $displayName = $record->{$displayField};
            return array("code" => $code, "error" => $error, "result" => $displayName);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getUpdateObject($platformModule, $obj, $changedData)
    {
        if ($platformModule == "Customer") {
            return QuickBooksOnline\API\Facades\Customer::update($obj, $changedData);
        }
        if ($platformModule == "Product" || $platformModule == "Service") {
            return QuickBooksOnline\API\Facades\Item::update($obj, $changedData);
        }
        if ($platformModule == "Invoice") {
            return QuickBooksOnline\API\Facades\Invoice::update($obj, $changedData);
        }
        if ($platformModule == "TaxRate") {
            return QuickBooksOnline\API\Facades\TaxRate::update($obj, $changedData);
        }
        return QuickBooksOnline\API\Facades\Customer::update($obj, $changedData);
    }
    public function updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule = "", $vt_fields = false)
    {
        try {
            global $adb;
            $error = "";
            $code = "Succeed";
            $received_data = "";
            if (empty($vtModule)) {
                $vtModule = $mappedInfo["vt_module"];
            }
            $vtId = $mappedInfo["vt_id"];
            $res = $this->syncParentRecordToVtiger($record);
            if ($res["code"] != "Succeed") {
                $code = $res["code"];
                $error = $res["error"];
            } else {
                if (empty($vt_fields)) {
                    $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
                }
                $res = $this->getPlatformApi();
                if ($res["code"] != "Succeed") {
                } else {
                    $qboApi = $res["result"];
                    if (intval($qboApi["sync2vt"]) != 1) {
                        $res = array();
                        $res["code"] = $code;
                        $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_VTIGER_IS_DISABLED", "VTEQBO");
                        $res["error"] = $error;
                    } else {
                        $primaryDS = strtolower($qboApi["primary_datasource"]);
                        $latestRecord = json_decode(html_entity_decode($mappedInfo["latest_value"]));
                        $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);
                        $recordModel->set("id", $vtId);
                        $recordModel->set("mode", "edit");
                        foreach ($mappedFields as $mappedField) {
                            $vtField = $mappedField["vt_field"];
                            if ($primaryDS == "vtiger") {
                                $currentValue = $recordModel->get($vtField);
                                $latestValue = $latestRecord->{$vtField};
                                if ($mappedField["data_type"] == "number") {
                                    $latestValue = doubleval($latestValue);
                                    $currentValue = doubleval($currentValue);
                                }
                                if ($mappedInfo["latest_update_vt"] != $recordModel->get("modifiedtime") && $latestValue != $currentValue) {
                                    continue;
                                }
                            }
                            $dataInputs = array();
                            $dataInputs["recordModel"] = $recordModel;
                            $dataInputs["record"] = $record;
                            $dataInputs["mappedField"] = $mappedField;
                            $dataInputs["vt_fields"] = $vt_fields;
                            $res2 = $this->updateFieldForVtigerModel($dataInputs);
                            if ($res2["code"] != "Succeed") {
                            } else {
                                $recordModel = $res2["result"];
                            }
                        }
                        $date_var = date("Y-m-d H:i:s");
                        $date_var = $adb->formatDate($date_var, true);
                        $recordModel->set("cf_last_date_synched", $date_var);
                        $recordModel->set("cf_do_not_create_queue_for_platformintegration", true);
                        $recordModel = $this->preSaveModel($recordModel, $record);
                        $recordModel->save();
                        $res = $this->postSaveModel($recordModel, $record, $mappedFields, $mappedInfo, true);
                        $received_data = json_encode($recordModel->getData());
                        $dataInput = array("platform_module" => $mappedInfo["platform_module"], "platform_id" => $record->Id, "vt_module" => $vtModule, "vt_id" => $vtId, "latest_value" => $received_data, "id" => $mappedInfo["id"], "latest_update" => $record['updated_at'], "latest_update_vt" => $recordModel->get("modifiedtime"));
                        $this->saveMappingInfo($dataInput);
                    }
                }
            }
        } catch (Exception $ex) {
            $code = "Failed";
            $error = $ex->getMessage();
        } finally {
            $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "QB2VT", "action_type" => "UPDATE", "platformintegrationlog_status" => $code, "message" => $error, "platform_id" => $mappedInfo["platform_id"], "platform_module" => $mappedInfo["platform_module"], "vt_module" => $vtModule, "vt_id" => $mappedInfo["vt_id"], "sent_data" => json_encode($record), "received_data" => $received_data));
        }
    }
    public function updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $platformModule = "", $vt_fields = false, $platformintegrationqueueid = 0)
    {
        try {
            $error = "";
            $sent_data = json_encode($recordModel->getData());
            $received_data = "";
            $code = "Succeed";
            $vtModule = $mappedInfo["vt_module"];
            if (empty($platformModule)) {
                $platformModule = $mappedInfo["platform_module"];
            }
            if (empty($vt_fields)) {
                $vt_fields = $this->getAllFieldsOfVTModule($vtModule);
            }
            $res = $this->syncParentRecordToPlatform($recordModel);
            if ($res["code"] != "Succeed") {
                $code = $res["code"];
                $error = $res["error"];
            } else {
                $res = $this->getDataService();
                if ($res["code"] != "Succeed") {
                    $code = $res["code"];
                    $error = $res["error"];
                } else {
                    $dataService = $res["result"];
                    $res = $this->getRecordByPlatformId($platformModule, $mappedInfo["platform_id"]);
                    if ($res["code"] != "Succeed") {
                        $code = $res["code"];
                        $error = $res["error"];
                    } else {
                        $obj = $res["result"];
                        $res = $this->getPlatformApi();
                        if ($res["code"] != "Succeed") {
                        } else {
                            $qboApi = $res["result"];
                            if (intval($qboApi["sync2platform"]) != 1) {
                                $res = array();
                                $res["code"] = $code;
                                $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_PLATFORM_IS_DISABLED", "PlatformIntegration");
                                $res["error"] = $error;
                            } else {
                                $primaryDS = strtolower($qboApi["primary_datasource"]);
                                $latestRecord = json_decode(html_entity_decode($mappedInfo["latest_value"]));
                                $changedData = array("Id" => $mappedInfo["platform_id"], "sparse" => "true");
                                foreach ($mappedFields as $mappedField) {
                                    $vtField = $mappedField["vt_field"];
                                    if ($primaryDS == "platform") {
                                        $qbValue = $this->getValueFromPlatformRecord($obj, $mappedField["platform_field"]);
                                        $latestValue = $latestRecord->{$vtField};
                                        if ($mappedField["is_picklist"] == "1" && !empty($latestValue)) {
                                            $res3 = $this->getValueFromPicklists($latestValue, $mappedField["module_ref"], $mappedField["platform_module"], $mappedField["platform_field"]);
                                            if ($res3["code"] != "Succeed") {
                                                $code = $res3["code"];
                                                $error = $res3["error"];
                                            } else {
                                                $latestValue = $res3["value"];
                                            }
                                        }
                                        if ($mappedField["data_type"] == "number") {
                                            $latestValue = doubleval($latestValue);
                                            $qbValue = doubleval($qbValue);
                                        }
                                        if ($mappedInfo["latest_update"] != $obj['updated_at'] && $latestValue != $qbValue) {
                                            continue;
                                        }
                                    }
                                    $dataInputs = array();
                                    $dataInputs["recordModel"] = $recordModel;
                                    $dataInputs["changedData"] = $changedData;
                                    $dataInputs["mappedField"] = $mappedField;
                                    $dataInputs["vt_fields"] = $vt_fields;
                                    $res2 = $this->updateFieldForPlatform($dataInputs);
                                    if ($res2["code"] != "Succeed") {
                                        $code = $res2["code"];
                                        $error = $res2["error"];
                                    } else {
                                        $changedData = $res2["result"];
                                    }
                                }
                                $res3 = $this->preSaveToPlatform($recordModel, $changedData, $mappedFields);
                                if ($res3["code"] != "Succeed") {
                                    $code = $res3["code"];
                                    $error = $res3["error"];
                                } else {
                                    $changedData = $res3["result"];
                                    if ($primaryDS == "platform") {
                                        $res3 = $this->checkConflictDataForOtherFieldsOnPlatform($recordModel, $obj, $changedData, $mappedFields, $latestRecord);
                                        if ($res3["code"] != "Succeed") {
                                            $code = $res3["code"];
                                            $error = $res3["error"];
                                        } else {
                                            $changedData = $res3["result"];
                                        }
                                    }
//                                    $sent_data = json_encode($changedData);
//                                    $updateObj = $this->getUpdateObject($platformModule, $obj, $changedData);
                                    try {
                                        $res = $dataService->patch('/api/' . strtolower($platformModule) . '/update/' . $obj['id'], [
                                            'json' => $changedData
                                        ]);
                                        $received_data = json_decode($res->getBody()->getContents(), true);
//                                        var_dump($received_data, 'engine834');die;
                                    } catch (Exception $ex) {
//                                        var_dump($ex->getMessage(), 'engine837');die;
                                        $code = "Failed";
                                        $error = $ex->getCode();
                                        $received_data = $ex->getMessage();
                                        $this->updateStatusOfQueue($platformintegrationqueueid, $code);
                                        $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "VT2QB", "action_type" => "UPDATE", "platformintegrationlog_status" => $code, "message" => $error, "vt_id" => $mappedInfo["vt_id"], "vt_module" => $mappedInfo["vt_module"], "platform_module" => $mappedInfo["platform_module"], "platform_id" => $mappedInfo["platform_id"], "sent_data" => $sent_data, "received_data" => $received_data));
                                    }
                                    $dataInput = array("platform_module" => $mappedInfo["platform_module"], "platform_id" => $mappedInfo["platform_id"], "vt_module" => $vtModule, "vt_id" => $recordModel->getId(), "latest_value" => json_encode($recordModel->getData()), "id" => $mappedInfo["id"], "latest_update" => $received_data['updated_at'], "latest_update_vt" => $recordModel->get("modifiedtime"));
//                                    var_dump($dataInput, 'engine845');die;
                                    $this->saveMappingInfo($dataInput);
                                    $this->updateLastDateSynched($recordModel);
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
            $this->updateStatusOfQueue($platformintegrationqueueid, $code);
            $this->insertIntoPlatformIntegrationLogs(array("sync_type" => "VT2QB", "action_type" => "UPDATE", "platformintegrationlog_status" => $code, "message" => $error, "vt_id" => $mappedInfo["vt_id"], "vt_module" => $mappedInfo["vt_module"], "platform_module" => $mappedInfo["platform_module"], "platform_id" => $mappedInfo["platform_id"], "sent_data" => $sent_data, "received_data" => $received_data));
        }
        return array("code" => $code, "error" => $error, "result" => $received_data);
    }
    public function updateFieldForPlatform($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $changedData = $dataInputs["changedData"];
            $mappedField = $dataInputs["mappedField"];
            $vt_fields = $dataInputs["vt_fields"];
            $vtField = $mappedField["vt_field"];
            $moduleRef = $mappedField["module_ref"];
            $code = "Succeed";
            $error = "";
            $moduleName = $this->getModuleLinkToField($vt_fields, $vtField);
            $v = $recordModel->get($vtField);
            if (!empty($moduleName) && in_array($moduleName, $this->getAllowedModule())) {
                $v = intval($v);
                if ($mappedField["data_type"] == "reference") {
                    $mappedInfo = $this->getMappedInfoByVtigerRecord($v, $moduleName);
                    $v = $mappedInfo["platform_id"];
                } else {
                    if (0 < $v) {
                        if (empty($moduleRef)) {
                            $parentRecordModel = Vtiger_Record_Model::getInstanceById($v, $moduleName);
                            $parentObj = new $moduleName();
                            $v = $parentRecordModel->get($parentObj->list_link_field);
                        } else {
                            $v = $this->getRelatedPlatformId($v, $moduleRef);
                        }
                    }
                }
            }
            $v = html_entity_decode($v, ENT_QUOTES);
            if ($mappedField["is_picklist"] == "1" && !empty($v)) {
                $res3 = $this->getValueFromPicklists($v, $moduleRef, $mappedField["platform_module"], $mappedField["platform_field"]);
                if ($res3["code"] != "Succeed" && $moduleRef != "DeliveryMethod") {
                    return $res3;
                }
                $v = $res3["value"];
            }
            $changedData = $this->setValueForPlatformRecord($changedData, $mappedField["platform_field"], $v);
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            $code = "Succeed";
            $error = "";
            $changedData = $this->removeBlankFields($changedData, $mappedFields);
            $changedData = $this->trimFieldsWithMaxLength($changedData, $mappedFields);
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function trimFieldsWithMaxLength($changedData, $mappedFields)
    {
        $qbModule = $mappedFields[0]["platform_module"];
        $fields = $this->getFieldsOfPlatformModule($qbModule);
        $maxLengths = array();
        foreach ($fields as $field) {
            $maxLengths[$field["platform_field"]] = intval($field["max_len"]);
        }
        foreach ($changedData as $key => $value) {
            $maxLength = $maxLengths[$key];
            if (0 < $maxLength) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $changedData[$key][$k] = substr($v, 0, $maxLength);
                    }
                } else {
                    $changedData[$key] = substr($value, 0, $maxLength);
                }
            }
        }
        return $changedData;
    }
    public function checkValidApi($qboApi)
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 2.0,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'multipart/form-data'
            ]
        ]);
        $options = [
            'form_params' => [
                "grant_type" => "password",
                "client_id" => $qboApi["consumer_key"],
                "client_secret" => $qboApi["consumer_secret"],
                "username" => $qboApi["realmid"],
                "password" => $qboApi["access_token"],
                "scope" => $this->scope
            ]
        ];
        try {
            $response = $client->post('/oauth/token', $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            return false;
        }
    }
    public function getCompanyInfo()
    {
        try {
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return false;
            }
            $dataService = $res["result"];
            $companyInfo = $dataService->getCompanyInfo();
            return $companyInfo;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function disconnectFromPlatform()
    {
        try {
            global $adb;
            $sql = "UPDATE platformintegration_api SET realmid=?, access_token=?, access_token_secret=?";
            $adb->pquery($sql, array("", "", ""));
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function getDataService()
    {
        try {
            $code = "Succeed";
            $error = "";
            $res = $this->getPlatformApi();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $platformApi = $res["result"];
            $client = new Client([
                'base_uri' => $this->baseUrl,
                'timeout'  => 2.0,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'multipart/form-data',
                    'Authorization' => 'Bearer ' . $platformApi['access_token']
                ]
            ]);

            return array("code" => $code, "error" => $error, "result" => $client);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getRecordsFromQbo($platformModule, $qboId = "", $otherConditions = "")
    {
        try {
            global $adb;
            $code = "Succeed";
            $error = "";
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $res = $this->getInfoOfPlatformModule($platformModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $row = $res["result"];
            $result = array();
            $res = $this->getQueryByPlatformModule($platformModule, $qboId, true, $otherConditions);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $sqls = $res["sql"];
            $maxResults = $this->maxResults;
            $maxPerTime = $this->maxPerTime;
            if (empty($row["vt_module"])) {
                $maxPerTime = 99999999999.0;
            }
            $sqlUpdateStartPosition = "UPDATE platformintegration_modules SET start_position=? WHERE id=?";
            $remainPerTime = $maxPerTime;
            $startPosition = $row["start_position"];
            $startPosition = intval($startPosition);
            if ($startPosition < 1) {
                $startPosition = 1;
            }
            foreach ($sqls as $sql) {
                $startPosition1 = $startPosition;
                $maxResults1 = $maxResults;
                $sql = trim($sql);
                while (true) {
                    if ($remainPerTime < $maxResults1) {
                        $maxResults1 = $remainPerTime;
                    }
                    $fullSql = (string) $sql . " startPosition " . $startPosition1 . " maxResults " . $maxResults1;
                    $result1 = $dataService->Query($fullSql);
                    $e = $dataService->getLastError();
                    if ($e != NULL) {
                        $adb->pquery($sqlUpdateStartPosition, array(1, $row["id"]));
                        return array("code" => "Failed", "error" => $e->getResponseBody());
                    }
                    if (empty($result1)) {
                        $startPosition1 = 1;
                        break;
                    }
                    $result = array_merge($result, $result1);
                    $startPosition1 += $maxResults1;
                    if ($maxPerTime <= count($result)) {
                        break;
                    }
                    $remainPerTime = $maxPerTime - count($result);
                }
                if (!empty($qboId)) {
                    if (!empty($result)) {
                        break;
                    }
                } else {
                    if ($maxPerTime <= count($result)) {
                        break;
                    }
                    $remainPerTime = $maxPerTime - count($result);
                }
            }
            $adb->pquery($sqlUpdateStartPosition, array($startPosition1, $row["id"]));
            if ((empty($qboId) || empty($result)) && $row["has_active_field"] == "1" && false) {
                $res = $this->getQueryByPlatformModule($platformModule, $qboId, false, $otherConditions);
                if ($res["code"] != "Succeed") {
                    return $res;
                }
                $sqls = $res["sql"];
                foreach ($sqls as $sql) {
                    $result2 = $dataService->Query($sql);
                    $e = $dataService->getLastError();
                    if ($e != NULL) {
                        return array("code" => "Failed", "error" => $e->getResponseBody());
                    }
                    if (!empty($result2)) {
                        $result = array_merge($result, $result2);
                    }
                    if (!empty($qboId) && !empty($result)) {
                        break;
                    }
                }
            }
            if (empty($result) || 0 == count($result)) {
                $code = "Failed";
                $error = vtranslate("LBL_RECORD_NOT_FOUND", "PlatformIntegration");
                return array("code" => $code, "error" => $error, "result" => array());
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function countRecordsFromPlatform($platformModule, $otherConditions = "")
    {
        try {
            $code = "Succeed";
            $error = "";
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $res = $this->getInfoOfPlatformModule($platformModule);
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $row = $res["result"];
            $result = 0;
            $res = $this->getQueryByPlatformModule($platformModule, "", true, $otherConditions, true);
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
                $result += intval($result1);
            }
            return array("code" => $code, "error" => $error, "result" => $result);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getRecordByPlatformId($platformModule, $qboId, $otherConditions = "", $isFindDuplicate = false)
    {
        try {
            $code = "Succeed";
            $error = "";
            $entities = NULL;
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            if ($qboId == '') {
                $data = $otherConditions;
                $type = 'POST';
                $address = '/api/' . strtolower($platformModule) . '/find/';
                $options['form_params'] = $data;
                $response = $dataService->request($type, $address, $options);
            } else {
                $data = array('id' => $qboId);
                $type = 'GET';
                $address = '/api/' . strtolower($platformModule) . '/' . $qboId;
                $response = $dataService->request($type, $address);
            }
            $entities = json_decode($response->getBody()->getContents(), true);
            return array("code" => $code, "error" => $error, "result" => $entities['data']);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function preSaveModel($recordModel, $record)
    {
        global $adb;
        $recordModel->set("cf_platform_status", $this->getPlatformStatus($record->Active));
        $crmId = $recordModel->getId();
        $moduleName = $recordModel->getModuleName();
        $this->updateLastDateSynched($recordModel);
        if (empty($crmId)) {
            $recordModel->set("source", "Platform");
            $assignedUserId = $this->getPlatformGroupId();
            $recordModel->set("assigned_user_id", $assignedUserId);
        } else {
            $recordModel2 = Vtiger_Record_Model::getInstanceById($crmId, $moduleName);
            $sql = "SELECT VMF.vt_field, VMF2.data_type\r\n                FROM platformintegration_mapping_fields VMF\r\n                INNER JOIN platformintegration_modules_fields VMF2 ON VMF.platform_module = VMF2.platform_module AND VMF.platform_field=VMF2.platform_field\r\n                WHERE VMF.is_active = 1\r\n                AND VMF.vt_module =?";
            $res = $adb->pquery($sql, array($moduleName));
            if ($adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $vt_field = $row["vt_field"];
                    $data_type = $row["data_type"];
                    $newValue = $recordModel->get($vt_field);
                    $oldValue = $recordModel2->get($vt_field);
                    if (true) {
                        if (empty($newValue)) {
                            $recordModel->set($vt_field, $oldValue);
                        } else {
                            if ($data_type == "number" || $data_type == "reference") {
                                $newValue = floatval($newValue);
                                if ($newValue == 0) {
                                    $recordModel->set($vt_field, $oldValue);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $recordModel;
    }
    public function postSaveModel($recordModel, $record, $mappedFields, $mappedInfo = false, $isUpdate = false)
    {
        $this->updateLastDateSynched($recordModel);
        return array("code" => "Succeed", "error" => "");
    }
    public function updateAddressFieldForQBO($dataInputs)
    {
        try {
            $recordModel = $dataInputs["recordModel"];
            $changedData = $dataInputs["changedData"];
            $mappedField = $dataInputs["mappedField"];
            $vtField = $mappedField["vt_field"];
            $qboField = $mappedField["platform_field"];
            $maxLen = intval($mappedField["max_len"]);
            $code = "Succeed";
            $error = "";
            $v = $recordModel->get($vtField);
            $lines = explode("\n", $v);
            $i = 1;
            $newLine = "";
            $totalCharacters = 0;
            foreach ($lines as $line) {
                $line = trim($line);
                $numCharacters = strlen($line) + 1;
                if ($maxLen < $totalCharacters + $numCharacters) {
                    $numCharacters = $maxLen - $totalCharacters - 1;
                    $line = substr($line, 0, $numCharacters);
                }
                if ($i < 5) {
                    $changedData = $this->setValueForPlatformRecord($changedData, $qboField . (string) $i, $line);
                    $i += 1;
                } else {
                    if (!empty($newLine)) {
                        $newLine .= " ";
                        $numCharacters += 1;
                    }
                    $newLine .= $line;
                }
                $totalCharacters += $numCharacters;
                if ($maxLen <= $totalCharacters) {
                    break;
                }
            }
            if (!empty($newLine)) {
                $changedData = $this->setValueForPlatformRecord($changedData, $qboField . (string) $i, $newLine);
            }
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function updateAddressFieldForVtigerModel($dataInputs)
    {
        try {
            $code = "Succeed";
            $error = "";
            $recordModel = $dataInputs["recordModel"];
            $record = $dataInputs["record"];
            $mappedField = $dataInputs["mappedField"];
            $qboField = $mappedField["platform_field"];
            $vtField = $mappedField["vt_field"];
            $realValue = "";
            for ($i = 1; $i <= 5; $i++) {
                $line = $this->getValueFromPlatformRecord($record, $qboField . (string) $i);
                if ($line != NULL) {
                    $line = $this->convertToVtigerValue($line, $mappedField["data_type"]);
                    if (!empty($realValue)) {
                        $realValue .= "\n";
                    }
                    $realValue .= $line;
                }
            }
            $recordModel->set($vtField, $realValue);
            return array("code" => $code, "error" => $error, "result" => $recordModel);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function getDiscountItemIdOnPlatform()
    {
        try {
            $code = "Succeed";
            $error = "";
            if (!empty($this->discountItemId)) {
                return array("code" => $code, "error" => $error, "result" => $this->discountItemId);
            }
            $res = $this->getDataService();
            if ($res["code"] != "Succeed") {
                return $res;
            }
            $dataService = $res["result"];
            $name = $this->qboDiscountItem["Name"];
            $query = "SELECT * FROM Item WHERE Name = '" . $name . "'";
            $result = $dataService->Query($query);
            $e = $dataService->getLastError();
            if ($e != NULL) {
                return array("code" => "Failed", "error" => $e->getResponseBody());
            }
            if (!empty($result)) {
                $entities = reset($result);
                $discountItemId = $entities->Id;
            } else {
                $data = $this->qboDiscountItem;
                $data["IncomeAccountRef"] = $this->getFirstIncomeAccountRef();
                $obj = QuickBooksOnline\API\Facades\FacadeHelper::reflectArrayToObject("Item", $data, true);
                $resultingObj = $dataService->Add($obj);
                $e = $dataService->getLastError();
                if ($e != NULL) {
                    $code = "Failed";
                    $error = $e->getResponseBody();
                    $received_data = $e->getResponseBody();
                    return array("code" => $code, "error" => $error);
                }
                $discountItemId = $resultingObj->Id;
            }
            $this->discountItemId = $discountItemId;
            return array("code" => $code, "error" => $error, "result" => $discountItemId);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkMergerExistedRecordOnVtiger($record, $mappedFields, $vtModule = "", $vt_fields = false)
    {
        return false;
    }
    public function checkMergerExistedRecordOnVtigerByFields($record, $mappedFields, $vtModule, $platform_fields)
    {
        try {
            global $adb;
            $platform_module = $mappedFields[0]["platform_module"];
            if (empty($vtModule)) {
                $vtModule = $mappedFields[0]["vt_module"];
            }
            $vtFields = array();
            $qboValues = array();
            foreach ($platform_fields as $platform_field) {
                $conditions = array("platform_module" => $platform_module, "platform_field" => $platform_field);
                $res = $this->getInfoFromMappedFields($mappedFields, $conditions);
                if (!empty($res)) {
                    $vtField = $res["vt_field"];
                    $qboValue = $this->getValueFromPlatformRecord($record, $platform_field);
                    if (!empty($qboValue)) {
                        $vtFields[] = $vtField;
                        $qboValues[$vtField] = $qboValue;
                    }
                }
            }
            if (empty($vtFields)) {
                return false;
            }
            $sql = "SELECT VF.columnname, VF.tablename AS table1, VE.tablename AS table2, VE.entityidfield, VF.fieldname\r\n                    FROM vtiger_field VF\r\n                    INNER JOIN vtiger_tab VT ON VF.tabid=VT.tabid\r\n                    INNER JOIN vtiger_entityname VE ON VT.tabid=VE.tabid\r\n                    WHERE (1=0";
            foreach ($vtFields as $vtField) {
                $sql .= " OR VF.fieldname=?";
            }
            $sql .= ") AND VT.`name`=?";
            $vtFields[] = $vtModule;
            $res = $adb->pquery($sql, $vtFields);
            if ($adb->num_rows($res)) {
                $sql2 = "";
                $param2 = array();
                $entityidfield = "";
                while ($row = $adb->fetchByAssoc($res)) {
                    $columnname = $row["columnname"];
                    $table1 = $row["table1"];
                    $table2 = $row["table2"];
                    $entityidfield = $row["entityidfield"];
                    $fieldname = $row["fieldname"];
                    if (empty($sql2)) {
                        $sql2 = "SELECT T1." . $entityidfield . "\r\n                                FROM " . $table2 . " T1\r\n                                INNER JOIN " . $table1 . " T2 ON T1." . $entityidfield . "=T2." . $entityidfield . "\r\n                                INNER JOIN vtiger_crmentity VC ON VC.crmid = T1." . $entityidfield . " AND VC.deleted=0";
                        $sql2 .= $this->getCustomSqlForCheckMergerExisted($record);
                        $sql2 .= " WHERE 1=1";
                    }
                    $sql2 .= " AND T2." . $columnname . "=?";
                    $param2[] = $qboValues[$fieldname];
                }
                $sql2 .= " ORDER BY T1." . $entityidfield . " LIMIT 0, 1";
                $res2 = $adb->pquery($sql2, $param2);
                if ($adb->num_rows($res2)) {
                    $row2 = $adb->fetchByAssoc($res2);
                    return $row2[(string) $entityidfield];
                }
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function getCustomSqlForCheckMergerExisted($record)
    {
        return "";
    }
    public function checkConflictDataForOtherFieldsOnPlatform($recordModel, $obj, $changedData, $mappedFields, $latestRecord)
    {
        try {
            $code = "Succeed";
            $error = "";
            return array("code" => $code, "error" => $error, "result" => $changedData);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function checkMergerExistedRecordOnPlatform($recordModel, $mappedFields, $platformModule, $vtModule, $vt_fields)
    {
        return false;
    }
    public function checkMergerExistedRecordOnPlatformByFields($recordModel, $mappedFields, $platformModule, $vtModule, $platform_fields)
    {
        try {
            $otherConditions = "";
            foreach ($platform_fields as $platform_field) {
                $conditions = array("platform_module" => $platformModule, "platform_field" => $platform_field);
                $res = $this->getInfoFromMappedFields($mappedFields, $conditions);
                if (!empty($res)) {
                    $vtField = $res["vt_field"];
                    $vtValue = $recordModel->get($vtField);
                    $otherConditions .= " AND " . $platform_field . "='" . $vtValue . "'";
                }
            }
            if (empty($otherConditions)) {
                return false;
            }
            $res = $this->getRecordByPlatformId($platformModule, "", $otherConditions, true);
            if ($res["code"] != "Succeed") {
                return false;
            }
            return $res["result"];
        } catch (Exception $ex) {
            return false;
        }
    }
    public function getAuthUrl()
    {
        return true;
    }
    public function getAccessTokenKey($id, $code, $realmId)
    {
        $redirectURI = $this->redirectURI;
        $redirectURI .= $id;
        $res = $this->getPlatformApi();
        $qboApi = $res["result"];
        $dataService = QuickBooksOnline\API\DataService\DataService::Configure(array("auth_mode" => "oauth2", "ClientID" => $qboApi["consumer_key"], "ClientSecret" => $qboApi["consumer_secret"], "RedirectURI" => $redirectURI, "scope" => "com.intuit.quickbooks.accounting", "baseUrl" => "development"));
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        $accessTokenKey = $accessTokenObj->getAccessToken();
        $refresh_token = $accessTokenObj->getRefreshToken();
        $sql = "UPDATE platformintegration_api SET realmid=?, access_token=?, access_token_secret=?";
        global $adb;
        $adb->pquery($sql, array($realmId, $accessTokenKey, $refresh_token));
    }
}

?>