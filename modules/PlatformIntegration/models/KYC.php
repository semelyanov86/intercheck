<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_KYC_Model extends PlatformIntegration_Engine_Model
{
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            global $adb;
            if (isset($changedData['user_id'])) {
                $contact_id = $changedData['user_id'];
                $contactModel = new PlatformIntegration_Contacts_Model();
                $mappedInfo = $contactModel->getMappedInfoByVtigerRecord($contact_id, 'Contacts');
                $changedData['user_id'] = $mappedInfo['platform_id'];
            }
            $changedData['crmid'] = $recordModel->getId();
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    public function setValueForVtigerField($recordModel, $qboRecord, $mappedField, $vtField, $realValue)
    {
        return parent::setValueForVtigerField($recordModel, $qboRecord, $mappedField, $vtField, $realValue);
    }
    public function syncParentRecordToPlatform($recordModel)
    {
        try {
            $error = "";
            $code = "Succeed";
            global $adb;
            $moduleName = $this->moduleName;
            $crmId = $recordModel->getId();
            $contact_id = $recordModel->get("cf_contacts_id");
            if (!empty($contact_id)) {
                $platformModule = "User";
                $vtModule = "Contacts";
                $accountsModel = Vtiger_Record_Model::getInstanceById($contact_id, $vtModule);
                if ($accountsModel->get("cf_sync_to_platform") != "1") {
                    $accountsModel->set("cf_sync_to_platform", 1);
                    $accountsModel->set("mode", "edit");
                    $accountsModel->save();
                }
                $mappedInfo = $this->getMappedInfoByVtigerRecord($contact_id, $vtModule);
                if (count($mappedInfo) == 0) {
                    $vtModuleClass = "PlatformIntegration_" . $vtModule . "_Model";
                    if (class_exists($vtModuleClass)) {
                        $obj = new $vtModuleClass($moduleName);
                    } else {
                        $obj = new PlatformIntegration_Engine_Model($moduleName);
                    }
                    $res = $obj->syncVtigerToPlatform($platformModule, $vtModule, $contact_id);
                    if ($res["code"] != "Succeed") {
                        return $res;
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