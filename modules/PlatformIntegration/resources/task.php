<?php

ini_set("display_errors", "on");
ini_set("max_execution_time", 6000);
chdir("../../..");
require_once "includes/runtime/BaseModel.php";
require_once "modules/Vtiger/models/Record.php";
require_once "modules/Users/models/Record.php";
require_once "includes/runtime/Globals.php";
require_once "include/utils/utils.php";
require_once "includes/runtime/LanguageHandler.php";
require_once "includes/Loader.php";
ini_set("display_errors", 0);
error_reporting(32767 & ~2 & ~8 & ~8192 & ~2048);
$current_user = Users_Record_Model::getCurrentUserModel();
$adb = PearDatabase::getInstance();
if ((int) $current_user->id == 0) {
    $sql = "SELECT id FROM vtiger_users WHERE is_admin='on' AND `status`='Active' LIMIT 0, 1";
    $res = $adb->pquery($sql, array());
    if (0 < $adb->num_rows($res) && ($row = $adb->fetchByAssoc($res))) {
        $current_user->id = intval($row["id"]);
    }
    if ((int) $current_user->id == 0) {
        $current_user->id = 1;
    }
}
foreach (glob("modules/PlatformIntegration/models/*.php") as $filename) {
    require_once $filename;
}
include_once "libraries/htmlpurifier/library/HTMLPurifier/Bootstrap.php";
spl_autoload_register(array("HTMLPurifier_Bootstrap", "autoload"));
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
$serverTime = time();
$progressInfo = array();
$moduleName = $_REQUEST["module"];
$syncType = $_REQUEST["syncType"];
$tab = $_REQUEST["tab"];
$progressInfo["syncType"] = $syncType;
$progressInfo["total"] = 0;
$progressInfo1["allRecords"] = 0;
$progressInfo["synched"] = 0;
$_SESSION["progressInfo"] = $progressInfo;
send_message();
$platformModel = new PlatformIntegration_Engine_Model($moduleName);
$res = $adb->pquery("SELECT * FROM vtiger_tab WHERE `name`=? AND presence=0", array($moduleName));
if ($adb->num_rows($res) == 0) {
    $error = vtranslate("ERROR_THIS_MODULE_IS_DISABLED", $moduleName);
    $_SESSION["progressInfo"]["message"] = $error;
    send_message();
    $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $moduleName, "platform_id" => "", "vt_module" => "", "vt_id" => "", "sent_data" => "", "received_data" => ""));
    exit;
}
$isSyncCommonData = false;
$api = $platformModel->getPlatformApi();

if ($api["code"] == "Succeed") {
    $api = $api["result"];
    if (empty($api["primary_datasource"])) {
        $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_PRIMARY_DATASOURCE_ISNOT_SELECTEED", $moduleName);
        $_SESSION["progressInfo"]["message"] = $error;
        send_message();
        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $moduleName, "platform_id" => "", "vt_module" => "", "vt_id" => "", "sent_data" => "", "received_data" => ""));
        exit;
    }
    if ($syncType == "Platform2VT" && $api["sync2vt"] != 1) {
        $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_VTIGER_IS_DISABLED", $moduleName);
        $_SESSION["progressInfo"]["message"] = $error;
        send_message();
        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $moduleName, "platform_id" => "", "vt_module" => "", "vt_id" => "", "sent_data" => "", "received_data" => ""));
        exit;
    }
    if ($syncType == "VT2Platform" && $api["sync2platform"] != 1) {
        $error = vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_PLATFORM_IS_DISABLED", $moduleName);
        $_SESSION["progressInfo"]["message"] = $error;
        send_message();
        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $moduleName, "platform_id" => "", "vt_module" => "", "vt_id" => "", "sent_data" => "", "received_data" => ""));
        exit;
    }
    $sql = "SELECT * FROM platformintegration_modules WHERE tab=? ORDER BY seq_in_tab";
    $res = $adb->pquery($sql, array($tab));
    if (0 < $adb->num_rows($res)) {
        while ($row = $adb->fetchByAssoc($res)) {
            if ($row['platform_module'] == 'Company') {
                continue;
            }
            $vtModule = $row["vt_module"];
            $platformModule = $row["platform_module"];
            $_SESSION["progressInfo"]["vtModule"] = $vtModule;
            $_SESSION["progressInfo"]["platformModule"] = $platformModule;
            $_SESSION["progressInfo"]["total"] = 0;
            $_SESSION["progressInfo"]["synched"] = 0;
            if ($row["allow_sync"] != "1") {
                $error = vtranslate("LBL_THIS_MODULE_IS_NOT_ALLOWED_TO_SYNC", "PlatformIntegration");
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
                $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                continue;
            }
            $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
            if (class_exists($vtModuleClass)) {
                $obj = new $vtModuleClass($moduleName);
            } else {
                $obj = new PlatformIntegration_Engine_Model($moduleName);
            }
            if ($obj->checkValidFieldsBeforeSyncing($platformModule, $vtModule) == false) {
                $error = vtranslate("MSG_CANNOT_SYNC_BECAUSE_MAPPED_FIELDS_ARE_INVALID", "PlatformIntegration");
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
                $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                continue;
            }
            $res1 = $obj->getInfoOfPlatformModule($platformModule);
            if ($res1["code"] != "Succeed") {
                $error = $res1["error"];
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
                $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                continue;
            }
            $qboModuleInfo = $res1["result"];
            if (strpos($qboModuleInfo["sync_scope"], $syncType) === false) {
                $error = vtranslate("MSG_SYSTEM_NOT_ALLOW_DO_THIS_PROCESS", "PlatformIntegration");
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
                $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                continue;
            }
            $mappedFields = $obj->getMappedFields($platformModule, $vtModule);
            if (empty($mappedFields)) {
                $error = vtranslate("MSG_HAVE_NOT_ANY_MAPPED_FIELD", "PlatformIntegration");
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
                $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                continue;
            }
            if ($isSyncCommonData) {
                if ($api["sync_picklist"] == "1") {
                    $_SESSION["progressInfo"]["message"] = vtranslate("LBL_SYSTEM_IS_SYNCING_PICKLIST", "PlatformIntegration");
                    send_message();
                    $resPL = $platformModel->syncAllPicklists();
                    if ($resPL["code"] != "Succeed") {
                        $error = $resPL["error"];
                        $_SESSION["progressInfo"]["message"] = $error;
                        send_message();
                        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                        exit;
                    }
                }
                if (true) {
                    $_SESSION["progressInfo"]["message"] = vtranslate("LBL_SYSTEM_IS_SYNCING_TAXES", "PlatformIntegration");
                    send_message();
                    $pDatasource = strtolower($api["primary_datasource"]);
                    $vteqboTaxRateModel = new PlatformIntegration_TaxRate_Model($moduleName);
                    $resTaxes = $vteqboTaxRateModel->syncAllTaxRates($pDatasource);
                    if ($resTaxes["code"] != "Succeed") {
                        $error = $resTaxes["error"];
                        $_SESSION["progressInfo"]["message"] = $error;
                        send_message();
                        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                        exit;
                    }
                }
                $isSyncCommonData = false;
            }
            $noChanged = 0;
            $successed = 0;
            $failed = 0;
            $error = "";
            $_SESSION["progressInfo"]["message"] = vtranslate("LBL_SYSTEM_IS_PREPARING_DATA", "PlatformIntegration");
            send_message();
            if ($syncType == "Platform2VT") {
                $error = "";
                $code = "Succeed";
                $vt_fields = $obj->getAllFieldsOfVTModule($vtModule);
                $res2 = $obj->getRecordsFromPlatform($platformModule);
                if ($res2["code"] != "Succeed") {
                    $error = $res2["error"];
                    $_SESSION["progressInfo"]["message"] = $error;
                    send_message();
                    $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                    continue;
                }
                $res2a = $obj->countRecordsFromPlatform($platformModule);
                $_SESSION["progressInfo"]["allRecords"] = $res2a["result"];
                $_SESSION["progressInfo"]["message"] = "";
                $totalRecord = count($res2["result"]);
                $_SESSION["progressInfo"]["total"] = $totalRecord;
                $synchedRecord = 0;
                $_SESSION["progressInfo"]["synched"] = $synchedRecord;
                if ($totalRecord == 0) {
                    $_SESSION["progressInfo"]["message"] = $synchedRecord . "/" . $totalRecord . " " . vtranslate("LBL_RECORDS", $moduleName);
                }
                send_message();
                $ids = "0";
                foreach ($res2["result"] as $record) {
                    $ids .= "," . $record->Id;
                }
                $mappedInfosA = $obj->getMappedInfoByPlatformRecord($ids, $platformModule, true);
                $mappedInfos = array();
                foreach ($mappedInfosA as $mappedInfo) {
                    $mappedInfos[strtolower($mappedInfo["platform_id"])] = $mappedInfo;
                }
                foreach ($res2["result"] as $record) {
                    $synchedRecord += 1;
                    $_SESSION["progressInfo"]["synched"] = $synchedRecord;
                    send_message();
                    $mappedInfo = $mappedInfos[strtolower($record->Id)];
                    if (count($mappedInfo) == 0) {
                        if ($vtModule == "Contacts") {
                            $givenName = $record->GivenName;
                            $familyName = $record->FamilyName;
                            if (empty($givenName) && empty($familyName)) {
                                $noChanged = $noChanged + 1;
                                continue;
                            }
                        }
                        $res3 = $obj->insertIntoVtiger($record, $mappedFields, $vtModule, $vt_fields);
                    } else {
                        if ($mappedInfo["latest_update"] == $record->MetaData->LastUpdatedTime) {
                            $noChanged = $noChanged + 1;
                            continue;
                        }
                        $res3 = $obj->updateToVtiger($record, $mappedFields, $mappedInfo, $vtModule, $vt_fields);
                    }
                    if ($res3["code"] != "Succeed") {
                        $failed = $failed + 1;
                    } else {
                        $successed = $successed + 1;
                    }
                }
            } else {
                if ($syncType == "VT2Platform") {
                    $vt_fields = $obj->getAllFieldsOfVTModule($vtModule);
                    $res2 = $obj->getAllVtigerRecordToSync($vtModule);
                    if ($res2["code"] != "Succeed") {
                        $error = $res2["error"];
                        $_SESSION["progressInfo"]["message"] = $error;
                        send_message();
                        $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $platformModule, "platform_id" => "", "vt_module" => $vtModule, "vt_id" => "", "sent_data" => "", "received_data" => ""));
                        exit;
                    }
                    $res2a = $obj->countAllVtigerRecordToSync($vtModule);
                    $_SESSION["progressInfo"]["allRecords"] = $res2a["result"];
                    $_SESSION["progressInfo"]["message"] = "";
                    $totalRecord = count($res2["result"]);
                    $_SESSION["progressInfo"]["total"] = $totalRecord;
                    $synchedRecord = 0;
                    $_SESSION["progressInfo"]["synched"] = $synchedRecord;
                    if ($totalRecord == 0) {
                        $_SESSION["progressInfo"]["message"] = $synchedRecord . "/" . $totalRecord . " " . vtranslate("LBL_RECORDS", $moduleName);
                    }
                    send_message();
                    if ($vtModule != "Contacts") {
                        $ids = "0";
                        foreach ($res2["result"] as $record) {
                            $ids .= "," . $record["crmid"];
                        }
                        $mappedInfosA = $obj->getMappedInfoByVtigerRecord($ids, $vtModule, true);
                        $mappedInfos = array();
                        foreach ($mappedInfosA as $mappedInfo) {
                            $vt_id = strtolower($mappedInfo["vt_id"]);
                            $mappedInfos[(string) $vt_id] = $mappedInfo;
                        }
                    }
                    foreach ($res2["result"] as $record) {
                        $synchedRecord += 1;
                        $_SESSION["progressInfo"]["synched"] = $synchedRecord;
                        send_message();
                        $vtId = $record["crmid"];
                        $vteqboqueueid = $record["platformintegrationqueueid"];
                        if ($vtModule == "Contacts") {
                            $mappedInfo = $obj->getMappedInfoByVtigerRecord($vtId, $vtModule);
                        } else {
                            $mappedInfo = $mappedInfos[(string) $vtId];
                        }
                        $recordModel = Vtiger_Record_Model::getInstanceById($vtId, $vtModule);

                        if (count($mappedInfo) == 0) {
                            $res3 = $obj->insertIntoPlatform($recordModel, $mappedFields, $platformModule, $vt_fields, $vteqboqueueid);
                        } else {
                            $res3 = $obj->updatePlatformRecord($recordModel, $mappedFields, $mappedInfo, $platformModule, $vt_fields, $vteqboqueueid);
                        }
                        if ($res3["code"] != "Succeed") {
                            $failed = $failed + 1;
                        } else {
                            $successed = $successed + 1;
                        }
//                        var_dump($res3, 'task289');die;
                    }
                }
            }
            if ($totalRecord != 0) {
                if ($noChanged != 0) {
                    $error .= vtranslate("LBL_NO_CHANGED", "PlatformIntegration");
                    $error .= ": " . $noChanged . ".<br />\n";
                }
                $error .= vtranslate("LBL_SUCCESSED", "PlatformIntegration");
                $error .= ": " . $successed . ".<br />\n";
                $error .= vtranslate("LBL_FAILED", "PlatformIntegration");
                $error .= ": " . $failed;
                $_SESSION["progressInfo"]["vtModule"] = "";
                $_SESSION["progressInfo"]["platformModule"] = "";
                $_SESSION["progressInfo"]["total"] = 0;
                $_SESSION["progressInfo"]["synched"] = 0;
                $_SESSION["progressInfo"]["message"] = $error;
                send_message();
            }
        }
    }
} else {
    $error = $api["error"];
    $_SESSION["progressInfo"]["message"] = $error;
    send_message();
    $platformModel->insertIntoPlatformIntegrationLogs(array("sync_type" => $syncType, "action_type" => "UPDATE", "platformintegrationlog_status" => "Failed", "message" => $error, "platform_module" => $moduleName, "platform_id" => "", "vt_module" => "", "vt_id" => "", "sent_data" => "", "received_data" => ""));
}
exit;
/**
    Constructs the SSE data format and flushes that data to the client.
*/
function send_message()
{
    $moduleName = "PlatformIntegration";
    $progressInfo1 = $_SESSION["progressInfo"];
    $vtModule = $progressInfo1["vtModule"];
    $qboModule = $progressInfo1["platformModule"];
    $totalRecord = $progressInfo1["total"];
    $synchedRecord = $progressInfo1["synched"];
    $message = $progressInfo1["message"];
    $type = $progressInfo1["syncType"];
    $allRecords = $progressInfo1["allRecords"];
    $blockName = "";
    $fromTo = "";
    $result = "";
    if ($type == "Platform2VT") {
        $blockName .= vtranslate("LBL_SYNC_TO_VTIGER", $moduleName);
        if (!empty($qboModule) && !empty($vtModule)) {
            $fromTo .= "<b>" . vtranslate($qboModule, $moduleName) . " -> " . vtranslate($vtModule) . ":</b>&nbsp;&nbsp;";
        }
        if (empty($message)) {
            if ($synchedRecord != 0 && $totalRecord != 0) {
                $result .= $synchedRecord . "/" . $totalRecord . " " . vtranslate("LBL_RECORDS", $moduleName);
                $result .= "<i style=\"float: right; margin-right: 20px;\"><b>Total: </b>" . $allRecords . " " . vtranslate("LBL_RECORDS", $moduleName) . "</i>";
            }
        } else {
            $result .= $message;
        }
    } else {
        $blockName .= vtranslate("LBL_SYNC_TO_QUICKBOOKS", $moduleName);
        if (!empty($qboModule) && !empty($vtModule)) {
            $fromTo .= "<b>" . vtranslate($vtModule) . " -> " . vtranslate($qboModule, $moduleName) . ":</b>&nbsp;&nbsp;";
        }
        if (empty($message)) {
            if ($synchedRecord != 0 && $totalRecord != 0) {
                $result .= $synchedRecord . "/" . $totalRecord . " " . vtranslate("LBL_RECORDS", $moduleName);
                $result .= "<i style=\"float: right; margin-right: 20px;\"><b>Total: </b>" . $allRecords . " " . vtranslate("LBL_RECORDS", $moduleName) . "</i>";
            }
        } else {
            $result .= $message;
        }
    }
    $d = array("blockName" => $blockName, "fromTo" => $fromTo, "result" => $result);
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;
    ob_flush();
    flush();
}

?>