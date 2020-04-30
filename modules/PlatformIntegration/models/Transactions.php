<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_Transactions_Model extends PlatformIntegration_Engine_Model
{
    public $statusesMapping = array(
        "Pending" => 1,
        "Approved" => 2,
        "Declined" => 3,
        "Cancelled" => 4
    );
    public $typesMapping = array(
        "Deposit - credit card" => 1,
        "Deposit - wire transaction" => 1,
        "Purchase - campaign" => 1,
        "Purchase - market buy" => 1,
        "Bonus" => 2,
        "Income - impression" => 1,
        "Income - click" => 1,
        "Income - lead" => 1,
        "Refund" => 8,
        "Withdrawal" => 8,
        "Chargeback" => 9
    );
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
            if (isset($changedData['related_transaction_id'])) {
                $related_id = $changedData['related_transaction_id'];
                $relatedModel = new PlatformIntegration_Contacts_Model();
                $mappedInfo = $relatedModel->getMappedInfoByVtigerRecord($related_id, 'Transactions');
                $changedData['related_transaction_id'] = $mappedInfo['platform_id'];
            }
            if (isset($changedData['status'])) {
                $changedData['status'] = $this->convertVtigerTransactionStatus($recordModel, $mappedFields);
            }
            if (isset($changedData['type'])) {
                $changedData['type'] = $this->convertVtigerTransactionType($recordModel, $mappedFields);
            }
            if (isset($changedData['created_at'])) {
                $changedData['created_at'] = $recordModel->get('transaction_date') . ' ' . $recordModel->get('cf_transaction_time');
            }
            if (isset($changedData['ftd'])) {
                $changedData['ftd'] = $this->convertVtigerTransactionFTD($recordModel, $mappedFields);
            }
            if (isset($changedData['amount'])) {
                $changedData['amount'] = intval($changedData['amount']);
            }
            if (isset($changedData['usd_value'])) {
                $changedData['usd_value'] = round($changedData['usd_value'], 2);
            }
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
    private function convertVtigerTransactionFTD($recordModel, $mappedFields)
    {
        if ($recordModel->get('ftd_upsale') == 'FTD') {
            return 1;
        } else {
            return 0;
        }
    }
    private function convertVtigerTransactionStatus($recordModel, $mappedFields)
    {
        return $this->statusesMapping[$recordModel->get('trstatus')];
    }
    private function convertVtigerTransactionType($recordModel, $mappedFields)
    {
        return $this->typesMapping[$recordModel->get('transaction_type')];
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
            $contact_id = $recordModel->get("payer");
            $related_id = $recordModel->get('cf_related_transaction');
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
            if (!empty($related_id)) {
                $platformModule = "Transaction";
                $vtModule = "Transactions";
                $transactionsModel = Vtiger_Record_Model::getInstanceById($related_id, $vtModule);
                if ($transactionsModel->get("cf_sync_to_platform") != "1") {
                    $transactionsModel->set("cf_sync_to_platform", 1);
                    $transactionsModel->set("mode", "edit");
                    $transactionsModel->save();
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