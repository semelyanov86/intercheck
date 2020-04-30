<?php

require_once "include/events/VTEventHandler.inc";
require_once "modules/PlatformIntegration/models/Module.php";
class PlatformIntegrationHandler extends VTEventHandler
{
    public function handleEvent($eventName, $entityData)
    {
        global $adb;
        global $root_directory;
        if ($eventName != "vtiger.entity.aftersave") {
            return true;
        }
        $cf_do_not_create_queue = $entityData->get("cf_do_not_create_queue_for_platformintegration");

        if (!empty($cf_do_not_create_queue)) {
            return true;
        }
        $sdkContainer = "modules/PlatformIntegration/helpers/";
        $sdkSource = $sdkContainer . "vendor/";
        if (is_dir($sdkSource)) {
            $autoloadFile = $sdkSource . "/autoload.php";
            if (!file_exists($autoloadFile)) {
                return true;
            }
            $platformModel = new PlatformIntegration_Engine_Model("PlatformIntegration");
            $res = $platformModel->getPlatformApi();
            if ($res["code"] != "Succeed") {
                return false;
            }
            $qboApi = $res["result"];
            if (intval($qboApi["sync2platform"]) != 1) {
                return false;
            }
            $modulesHandle = $platformModel->getAllowedModule();
            $currentModule = $entityData->getModuleName();
            $recordId = $entityData->getId();
            if (in_array($currentModule, $modulesHandle)) {
                $source = $entityData->get("source");
                $source = strtolower($source);
                $syncToPlatform = $entityData->get("cf_sync_to_platformintegration");
                if ($syncToPlatform == "on" || $syncToPlatform == "1" || $syncToPlatform == 1 || $syncToPlatform == true) {
                    $create_queue = false;
                    $cf_last_date_synched = $entityData->get("cf_last_date_synched");
                    if (empty($cf_last_date_synched)) {
                        if ($source != "platform") {
                            $create_queue = true;
                        }
                    } else {
                        $cf_last_date_synched = new DateTime($cf_last_date_synched);
                        $modifiedtime = new DateTime($entityData->get("modifiedtime"));
                        $diff = $modifiedtime->diff($cf_last_date_synched);
                        if (0 < $diff->y || 0 < $diff->m || 0 < $diff->d || 0 < $diff->h || 0 < $diff->i || 0 < $diff->s) {
                            $create_queue = true;
                        }
                    }

                    if ($create_queue) {
                        $action_type = "Update";
                        $hasInserted = true;
                        $queues = $platformModel->getQueueByRecordId($currentModule, $recordId);

                        if (empty($queues)) {
                            $res2 = $platformModel->getMappedInfoByVtigerRecord($recordId, $currentModule);
                            if (count($res2) == 0) {
                                $action_type = "Insert";
                            }
                        } else {
                            foreach ($queues as $row) {
                                if ($row["action_type"] == "Insert" && $row["platformintegrationqueue_status"] == "Failed") {
                                    $recordModel2 = Vtiger_Record_Model::getInstanceById($row["platformintegrationqueueid"], "PlatformIntegrationQueues");
                                    $recordModel2->set("id", $row["platformintegrationqueueid"]);
                                    $recordModel2->set("mode", "edit");
                                    $recordModel2->set("platformintegrationqueue_status", "Queue");
                                    $recordModel2->set("cf_do_not_create_queue_for_platformintegration", true);
                                    $recordModel2->save();
                                    $hasInserted = false;
                                    break;
                                }
                                if ($action_type == $row["action_type"]) {
                                    if ($row["platformintegrationqueue_status"] == "Queue") {
                                        $hasInserted = false;
                                        break;
                                    }
                                    if ($row["platformintegrationqueue_status"] == "Failed") {
                                        $recordModel2 = Vtiger_Record_Model::getInstanceById($row["platformintegrationqueueid"], "PlatformIntegrationQueues");
                                        $recordModel2->set("id", $row["platformintegrationqueueid"]);
                                        $recordModel2->set("mode", "edit");
                                        $recordModel2->set("platformintegrationqueue_status", "Queue");
                                        $recordModel2->set("cf_do_not_create_queue_for_platformintegration", true);
                                        $recordModel2->save();
                                        $hasInserted = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($hasInserted) {
                            $recordModel3 = Vtiger_Record_Model::getCleanInstance("PlatformIntegrationQueues");
                            $recordModel3->set("from_module", vtranslate($currentModule));
                            $recordModel3->set("from_id", $recordId);
                            $recordModel3->set("sync_type", "VT2Platform");
                            $recordModel3->set("action_type", $action_type);
                            $recordModel3->set("platformintegrationqueue_status", "Queue");
                            $recordModel3->set("cf_do_not_create_queue_for_platformintegration", true);
                            $recordModel3->save();
                        }
                    }
                    if ($currentModule == "Products" || $currentModule == "Services") {
                        $item = new PlatformIntegration_PlatformItem_Model();
                        $item->repopulateTaxes($recordId);
                    }
                } else {
                    $sql = "SELECT platformintegrationqueueid FROM vtiger_platformintegrationqueues WHERE from_module=? AND from_id=? AND platformintegrationqueue_status=?";
                    $res = $adb->pquery($sql, array($currentModule, $recordId, "Queue"));
                    if (0 < $adb->num_rows($res)) {
                        $recordIds = array();
                        while ($row = $adb->fetchByAssoc($res)) {
                            $recordIds[] = $row["platformintegrationqueueid"];
                        }
                        $sql2 = "UPDATE vtiger_crmentity SET deleted=? WHERE crmid IN (" . generateQuestionMarks($recordIds) . ")";
                        $adb->pquery($sql2, array(1, $recordIds));
                    }
                }
            }
        } else {
            return true;
        }
    }
}

?>