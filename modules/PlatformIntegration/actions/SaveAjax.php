<?php

class PlatformIntegration_SaveAjax_Action extends Vtiger_BasicAjax_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function __construct()
    {
        parent::__construct();        
        $this->exposeMethod("saveAPI");
        $this->exposeMethod("saveMainConfig");
        $this->exposeMethod("saveMappingFields");
        $this->exposeMethod("platformintegrationSync");
        $this->exposeMethod("savePlatformIntegrationDate");
        $this->exposeMethod("downloadSDK");
        $this->exposeMethod("getSummary");
        $this->exposeMethod("getAccessTokenKey");
        $this->exposeMethod("disconnectFromPlatform");
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    public function saveAPI(Vtiger_Request $request)
    {
        global $adb;
        $realmid = $request->get("realmid");
        $access_token = $request->get("access_token");
        $access_token_secret = $request->get("access_token_secret");
        $consumer_key = $request->get("consumer_key");
        $consumer_secret = $request->get("consumer_secret");
        $qboApi = array("consumer_key" => $consumer_key, "consumer_secret" => $consumer_secret, "access_token" => $access_token, "access_token_secret" => $access_token_secret, "realmid" => $realmid);
        $moduleName = $request->getModule();
        $vteqboModel = new PlatformIntegration_Engine_Model($moduleName);
        $plaformKeysData = $vteqboModel->checkValidApi($qboApi);
        if ($plaformKeysData && is_array($plaformKeysData)) {
            $qboApi['access_token_secret'] = $plaformKeysData['refresh_token'];
            $qboApi['access_token'] = $plaformKeysData['access_token'];
            $access_token_secret = $plaformKeysData['refresh_token'];
            $access_token = $plaformKeysData['access_token'];
            $sql = "SELECT id FROM platformintegration_api ORDER BY id LIMIT 1";
            $res = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($res)) {
                $sql = "UPDATE platformintegration_api SET realmid=?, access_token=?, access_token_secret=?," . "consumer_key=?, consumer_secret=?, latest_update=? WHERE id = ?";
                $id = $adb->query_result($res, 0, "id");
                $params = array($realmid, $access_token, $access_token_secret, $consumer_key, $consumer_secret, date("YmdHis"), $id);
                $adb->pquery($sql, $params);
            } else {
                $sql = "INSERT INTO platformintegration_api(realmid, access_token, access_token_secret, consumer_key, consumer_secret, latest_update) VALUES(?, ?, ?, ?, ?, ?)";
                $params = array($realmid, $access_token, $access_token_secret, $consumer_key, $consumer_secret, date("YmdHis"));
                $adb->pquery($sql, $params);
            }
            $res = array();
        } else {
            $res = array("message" => vtranslate("ERROR_CANNOT_CONNECT_TO_PLATFORM", $moduleName));
        }
        $response = new Vtiger_Response();
        $response->setResult($res);
        $response->emit();
    }
    public function saveMainConfig(Vtiger_Request $request)
    {
        global $adb;
        $sync2vt = $request->get("sync2vt");
        if (!empty($sync2vt)) {
            $sync2vt = 1;
        } else {
            $sync2vt = 0;
        }
        $sync2platform = $request->get("sync2platform");
        if (!empty($sync2platform)) {
            $sync2platform = 1;
        } else {
            $sync2platform = 0;
        }
        $primary_datasource = $request->get("primary_datasource");
        $platform_version = $request->get("platform_version");
        $sql = "SELECT id FROM platformintegration_api ORDER BY id LIMIT 1";
        $res = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($res)) {
            $sql = "UPDATE platformintegration_api SET sync2vt=?, sync2platform=?, primary_datasource=?, platform_version=? WHERE id = ?";
            $id = $adb->query_result($res, 0, "id");
            $params = array($sync2vt, $sync2platform, $primary_datasource, $platform_version, $id);
            $adb->pquery($sql, $params);
        } else {
            $sql = "INSERT INTO platformintegration_api(sync2vt, sync2platform, primary_datasource, platform_version) VALUES(?, ?, ?, ?)";
            $params = array($sync2vt, $sync2platform, $primary_datasource, $platform_version);
            $adb->pquery($sql, $params);
        }
        if ($platform_version == "AUS") {
            $obj = new PlatformIntegration_Base_Model();
            $obj->createCustomFieldsForAUS();
        }
        $response = new Vtiger_Response();
        $response->setResult(array());
        $response->emit();
    }
    public function saveMappingFields(Vtiger_Request $request)
    {
        global $adb;
        $moduleName = $request->getModule();
        $vtModule = $request->get("vtModule");
        $platformModule = $request->get("platformModule");
        $mappingFields = $request->get("mappingFields");
        $mappingFields = split(";", $mappingFields);
        foreach ($mappingFields as $k => $v) {
            $v = trim($v);
            if (empty($v)) {
                unset($mappingFields[$k]);
            }
        }
        $response = new Vtiger_Response();
        $platformintegrationModel = new PlatformIntegration_Engine_Model($moduleName);
        if (!empty($mappingFields)) {
            $res = $platformintegrationModel->checkValidMappingFields($mappingFields, $vtModule, $platformModule);
            if (is_array($res)) {
                $error = vtranslate("ERR_DOES_NOT_ALLOW_DUPLICATE_ON_MAPPING_FIELDS", $moduleName);
            } else {
                $res = $platformintegrationModel->checkValidLogicMappingFields($mappingFields, $vtModule, $platformModule);
                if (is_array($res)) {
                    $error = vtranslate("ERR_DOES_NOT_ALLOW_INVALID_LOGIC_ON_MAPPING_FIELDS", $moduleName);
                }
            }
            if (is_array($res)) {
                if (!empty($res)) {
                    $res2 = $platformintegrationModel->getFielForConfig($vtModule);
                    $platform_fields = $res2["data"]["platform_fields"];
                    $vt_fields = $res2["data"]["vt_fields"];
                    $error .= "<br />--------------------------------------------------";
                    foreach ($res as $pair) {
                        $vtFieldName = "";
                        $platformFieldName = "";
                        foreach ($vt_fields as $k => $v) {
                            if ($k == $pair[0]) {
                                $vtFieldName = vtranslate($v->get("label"), $vtModule);
                                break;
                            }
                        }
                        foreach ($platform_fields as $row) {
                            if ($row["platform_field"] == $pair[1]) {
                                $platformFieldName = vtranslate($row["platform_field_label"], $moduleName);
                                break;
                            }
                        }
                        $error .= "<br />+ " . $vtFieldName . " <-->  " . $platformFieldName;
                    }
                }
                $response->setResult(array("error" => $error));
                $response->emit();
                exit;
            }
        }
        $sql = "DELETE FROM platformintegration_mapping_fields WHERE vt_module = ? AND platform_module = ?";
        $adb->pquery($sql, array($vtModule, $platformModule));
        foreach ($mappingFields as $mappingField) {
            list($vt_field, $platform_field) = split(",", $mappingField);
            if (empty($vt_field) || empty($platform_field)) {
                continue;
            }
            $sql = "INSERT INTO platformintegration_mapping_fields(platform_module, platform_field, vt_module, vt_field, is_active) VALUES(?, ?, ?, ?, 1)";
            $params = array($platformModule, $platform_field, $vtModule, $vt_field);
            $adb->pquery($sql, $params);
        }
        if ($vtModule == "Contacts" && empty($mappingFields)) {
            $sql = "UPDATE platformintegration_mapping_fields SET  vt_field=? WHERE platform_module=? AND platform_field=? AND vt_module=? AND is_active=1";
            $params = array("accountname_dn", "Company", "DisplayName", "Accounts");
            $adb->pquery($sql, $params);
        }
        $response->setResult(array("error" => ""));
        $response->emit();
    }
    public function platformintegrationSync(Vtiger_Request $request)
    {
        global $adb;
        $code = "Succeed";
        $error = "";
        $moduleName = $request->getModule();
        $syncType = $request->get("syncType");
        $tab = $request->get("tab");
        $response = new Vtiger_Response();
        $platformintegrationModel = new PlatformIntegration_Engine_Model($moduleName);
        $api = $platformintegrationModel->getPlatformApi();
        if ($api["code"] == "Succeed") {
            $api = $api["result"];
            if (empty($api["primary_datasource"])) {
                $res = array("code" => false, "error" => vtranslate("ERROR_CANNOT_SYNC_BECAUSE_PRIMARY_DATASOURCE_ISNOT_SELECTEED", $moduleName));
                $response->setResult($res);
                $response->emit();
                exit;
            }
            if ($syncType == "Platform2VT" && $api["sync2vt"] != 1) {
                $res = array("code" => false, "error" => vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_VTIGER_IS_DISABLED", $moduleName));
                $response->setResult($res);
                $response->emit();
                exit;
            }
            if ($syncType == "VT2Platform" && $api["sync2platform"] != 1) {
                $res = array("code" => false, "error" => vtranslate("ERROR_CANNOT_SYNC_BECAUSE_SYNC_TO_PLATFORM_IS_DISABLED", $moduleName));
                $response->setResult($res);
                $response->emit();
                exit;
            }
            if ($api["sync_picklist"] == "1") {
                $res = $platformintegrationModel->syncAllPicklists();
                if ($res["code"] != "Succeed") {
                    $response->setResult($res);
                    $response->emit();
                    exit;
                }
            }
            if (true) {
                $pDatasource = strtolower($api["primary_datasource"]);
                $vteqboTaxRateModel = new PlatformIntegration_TaxRate_Model($moduleName);
                $res = $vteqboTaxRateModel->syncAllTaxRates($pDatasource);
                if ($res["code"] != "Succeed") {
                    $response->setResult($res);
                    $response->emit();
                    exit;
                }
            }
            $sql = "SELECT * FROM platformintegration_modules WHERE tab=? ORDER BY seq_in_tab";
            $res = $adb->pquery($sql, array($tab));
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $vtModule = $row["vt_module"];
                    $qboModule = $row["platform_module"];
                    $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new PlatformIntegration_Engine_Model($moduleName);
                    }
                    $error .= "<br />---------------------------------------------------------------------<br /><b>" . $vtModule . "</b><br />";
                    if ($syncType == "Plarfotm2VT") {
                        $res2 = $obj->syncPlatformToVtiger($qboModule, $vtModule);
                        $error .= $res2["error"];
                    } else {
                        if ($syncType == "VT2Platform") {
                            $res2 = $obj->syncVtigerToPlatform($qboModule, $vtModule);
                            $error .= $res2["error"];
                        }
                    }
                }
            }
        } else {
            $code = $api["code"];
            $error = $api["error"];
        }
        $response->setResult(array("code" => $code, "error" => $error));
        $response->emit();
    }
    public function savePlatformIntegrationDate(Vtiger_Request $request)
    {
        global $adb;
        global $current_user;
        $code = "Succeed";
        $error = "";
        $response = new Vtiger_Response();
        $dataInput = $request->get("dateData");
        $allowSyncData = $request->get("allowSyncData");
        $datas = explode(";", $dataInput);
        $currentDateFormat = $current_user->date_format;
        foreach ($datas as $data) {
            $item = explode(",", $data);
            list($tab, $from_date) = $item;
            if (!empty($from_date)) {
                $from_date = getValidDBInsertDateValue($from_date);
            }
            $sql = "UPDATE platformintegration_modules SET from_date=? WHERE tab=?";
            $adb->pquery($sql, array($from_date, $tab));
        }
        $allowSyncDatas = explode(";", $allowSyncData);
        foreach ($allowSyncDatas as $data) {
            $item = explode(",", $data);
            $sql = "UPDATE platformintegration_modules SET allow_sync=? WHERE tab=?";
            $adb->pquery($sql, array($item[1], $item[0]));
        }
        $response->setResult(array("code" => $code, "error" => $error));
        $response->emit();
    }
    public function getSummary(Vtiger_Request $request)
    {
        global $adb;
        $code = "Succeed";
        $error = "";
        $moduleName = $request->getModule();
        $response = new Vtiger_Response();
        $filterMode = $request->get("filter_mode");
        $startRange = $request->get("start_range");
        $endRange = $request->get("end_range");
        $dateFormat = "Y-m-d";
        $filterDate = "AND (VC.createdtime BETWEEN ? AND ?)";
        $params = array();
        if ($filterMode == "today") {
            $startRange = date($dateFormat);
            $endRange = date($dateFormat, strtotime("+1 days"));
            $params = array($startRange, $endRange);
        } else {
            if ($filterMode == "thisweek") {
                $monday = strtotime("last monday");
                $startRange = date($dateFormat, $monday);
                $sunday = strtotime("+7 days", $monday);
                $endRange = date($dateFormat, $sunday);
                $params = array($startRange, $endRange);
            } else {
                if ($filterMode == "range") {
                    $startRange = getValidDBInsertDateValue($startRange);
                    $endRange = getValidDBInsertDateValue($endRange);
                    $endRange = date_create_from_format($dateFormat, $endRange);
                    date_add($endRange, date_interval_create_from_date_string("1 days"));
                    $endRange = date_format($endRange, $dateFormat);
                    $params = array($startRange, $endRange);
                } else {
                    $filterDate = "";
                }
            }
        }
        $vtigerSummary = "";
        $qboSummary = "";
        if (true) {
            $sql = "SELECT VV.vt_module, VV.action_type, VV.platformintegrationlog_status, COUNT(VV.platformintegrationlogid) as total, IFNULL(VM.tab_seq, 10000) as tab_seq\r\n                FROM vtiger_platformintegrationlogs VV\r\n                INNER JOIN vtiger_crmentity VC ON VC.crmid = VV.platformintegrationlogid\r\n                LEFT JOIN platformintegration_modules VM ON VM.platform_module = VV.platform_module\r\n                WHERE VV.sync_type = 'Platform2VT' AND VV.platformintegrationlog_status <> 'Skipped'\r\n                " . $filterDate . "\r\n                GROUP BY VV.vt_module, VV.action_type, VV.platformintegrationlog_status\r\n                ORDER BY tab_seq, VV.vt_module, VV.action_type, VV.platformintegrationlog_status";
            $res1 = $adb->pquery($sql, $params);
            if (0 < $adb->num_rows($res1)) {
                $currentModule = "";
                $currentCreated = 0;
                $currentUpdated = 0;
                $currentFailed = 0;
                while ($row = $adb->fetchByAssoc($res1)) {
                    $newModule = $row["vt_module"];
                    if (empty($newModule)) {
                        $newModule = vtranslate("LBL_PLATFORMINTEGRATION", $moduleName);
                    }
                    if ($newModule != $currentModule) {
                        if ($currentModule != "") {
                            $vtigerSummary .= "<tr><td class='tdName'>" . $currentModule . "</td><td>" . $currentCreated . "</td><td>" . $currentUpdated . "</td><td class='tdFailed'>" . $currentFailed . "</td></tr>";
                        }
                        $currentModule = $newModule;
                        $currentCreated = 0;
                        $currentUpdated = 0;
                        $currentFailed = 0;
                    }
                    $action_type = $row["action_type"];
                    $action_type = strtolower($action_type);
                    $status = $row["platformintegrationlog_status"];
                    $status = strtolower($status);
                    $total = $row["total"];
                    $total = intval($total);
                    if ($action_type == "insert") {
                        if ($status == "successful") {
                            $currentCreated += $total;
                        } else {
                            if ($status == "failed") {
                                $currentFailed += $total;
                            }
                        }
                    } else {
                        if ($action_type == "update") {
                            if ($status == "successful") {
                                $currentUpdated += $total;
                            } else {
                                if ($status == "failed") {
                                    $currentFailed += $total;
                                }
                            }
                        }
                    }
                }
                $vtigerSummary .= "<tr><td class='tdName'>" . $currentModule . "</td><td>" . $currentCreated . "</td><td>" . $currentUpdated . "</td><td class='tdFailed'>" . $currentFailed . "</td></tr>";
            }
            if (empty($vtigerSummary)) {
                $vtigerSummary = vtranslate("LBL_RECORD_NOT_FOUND", $moduleName);
                $vtigerSummary = "<tr><td class='text-center' colspan='4'>" . $vtigerSummary . "</td></tr>";
            }
        }
        if (true) {
            $sql = "SELECT VV.platform_module, VV.action_type, VV.platformintegrationlog_status, COUNT(VV.platformintegrationlogid) as total, IFNULL(VM.tab_seq, 10000) as tab_seq\r\n                FROM vtiger_platformintegrationlogs VV\r\n                INNER JOIN vtiger_crmentity VC ON VC.crmid = VV.platformintegrationlogid\r\n                LEFT JOIN platformintegration_modules VM ON VM.platform_module = VV.platform_module\r\n                WHERE VV.sync_type = 'VT2Platform' AND VV.platformintegrationlog_status <> 'Skipped'\r\n                " . $filterDate . "\r\n                GROUP BY VV.platform_module, VV.action_type, VV.platformintegrationlog_status\r\n                ORDER BY tab_seq, VV.platform_module, VV.action_type, VV.platformintegrationlog_status";
            $res1 = $adb->pquery($sql, $params);
            if (0 < $adb->num_rows($res1)) {
                $currentModule = "";
                $currentCreated = 0;
                $currentUpdated = 0;
                $currentFailed = 0;
                while ($row = $adb->fetchByAssoc($res1)) {
                    $newModule = $row["platform_module"];
                    if (empty($newModule)) {
                        $newModule = vtranslate("LBL_PLATFORMINTEGRRATION", $moduleName);
                    }
                    if ($newModule != $currentModule) {
                        if ($currentModule != "") {
                            $qboSummary .= "<tr><td class='tdName'>" . $currentModule . "</td><td>" . $currentCreated . "</td><td>" . $currentUpdated . "</td><td class='tdFailed'>" . $currentFailed . "</td></tr>";
                        }
                        $currentModule = $newModule;
                        $currentCreated = 0;
                        $currentUpdated = 0;
                        $currentFailed = 0;
                    }
                    $action_type = $row["action_type"];
                    $action_type = strtolower($action_type);
                    $status = $row["platformintegration_status"];
                    $status = strtolower($status);
                    $total = $row["total"];
                    $total = intval($total);
                    if ($action_type == "insert") {
                        if ($status == "successful") {
                            $currentCreated += $total;
                        } else {
                            if ($status == "failed") {
                                $currentFailed += $total;
                            }
                        }
                    } else {
                        if ($action_type == "update") {
                            if ($status == "successful") {
                                $currentUpdated += $total;
                            } else {
                                if ($status == "failed") {
                                    $currentFailed += $total;
                                }
                            }
                        }
                    }
                }
                $qboSummary .= "<tr><td class='tdName'>" . $currentModule . "</td><td>" . $currentCreated . "</td><td>" . $currentUpdated . "</td><td class='tdFailed'>" . $currentFailed . "</td></tr>";
            }
            if (empty($qboSummary)) {
                $qboSummary = vtranslate("LBL_RECORD_NOT_FOUND", $moduleName);
                $qboSummary = "<tr><td class='text-center' colspan='4'>" . $qboSummary . "</td></tr>";
            }
        }
        $response->setResult(array("code" => $code, "error" => $error, "vtigerSummary" => $vtigerSummary, "platformSummary" => $qboSummary));
        $response->emit();
    }
    public function downloadSDK(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $vteModel = new PlatformIntegration_Module_Model($moduleName);
        $error = "";
        $srcZip = $vteModel->qboSdkLink;
        $sdkContainer = "modules/PlatformIntegration/helpers/";
        $sdkSource = $sdkContainer . "vendor/";
        $trgZip = $sdkContainer . "qbo_sdk.zip";
        if (copy($srcZip, $trgZip)) {
            require_once "vtlib/thirdparty/dUnzip2.inc.php";
            $unzip = new dUnzip2($trgZip);
            $unzip->unzipAll(getcwd() . "/" . $sdkContainer);
            if ($unzip) {
                $unzip->close();
            }
            if (!is_dir($sdkSource)) {
                $error = vtranslate("ERR_UNZIP_ERROR", $moduleName);
            }
        } else {
            $error = vtranslate("ERR_DOWNLOAD_ERROR", $moduleName);
        }
        if ($error == "") {
            $result = array("success" => true, "message" => "");
        } else {
            $result = array("success" => false, "message" => $error);
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
    public function getAccessTokenKey(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $code = $request->get("code");
        $realmId = $request->get("realmId");
        $id = $request->get("id");
        $error = "";
        $result = array();
        $vteModel = new PlatformIntegration_Engine_Model($moduleName);
        $vteModel->getAccessTokenKey($id, $code, $realmId);
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
    public function disconnectFromPlatform(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $error = "";
        $result = array();
        $vteModel = new PlatformIntegration_Engine_Model($moduleName);
        $vteModel->disconnectFromQuickbooksOnline();
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}

?>