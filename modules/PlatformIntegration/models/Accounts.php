<?php

require_once "modules/PlatformIntegration/models/PlatformCustomer.php";
class PlatformIntegration_Accounts_Model extends PlatformIntegration_PlatformCustomer_Model
{
    public function __construct()
    {
        $otherUnusedFields = array("account_id", "assigned_user_id", "isconvertedfromlead");
        $this->unusedFields = array_merge($this->unusedFields, $otherUnusedFields);
    }
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            global $adb;
            $crmid = $recordModel->getId();
            foreach ($mappedFields as $mappedField) {
                if ($mappedField["platform_field"] == "DisplayName") {
                    if ($mappedField["vt_field"] == "contactname_dn") {
                        $displayName = "";
                        $sql = "SELECT salutation, firstname, lastname FROM vtiger_contactdetails WHERE accountid = ? ORDER BY contactid DESC LIMIT 0, 1";
                        $res = $adb->pquery($sql, array($crmid));
                        if (0 < $adb->num_rows($res)) {
                            while ($row = $adb->fetchByAssoc($res)) {
                                $salutation = $row["salutation"];
                                $firstname = $row["firstname"];
                                $lastname = $row["lastname"];
                                $displayName = (string) $salutation . " " . $firstname . " " . $lastname;
                                $displayName = trim($displayName);
                            }
                        }
                        if (empty($displayName)) {
                            $displayName = $recordModel->get("accountname");
                        }
                    } else {
                        $displayName = $recordModel->get("accountname");
                    }
                    $displayName = html_entity_decode($displayName, ENT_QUOTES);
                    $changedData["DisplayName"] = $displayName;
                    break;
                }
            }
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
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
            $platform_fields = array("PrimaryEmailAddr.Address");
            $vtId = $this->checkMergerExistedRecordOnVtigerByFields($record, $mappedFields, $vtModule, $platform_fields);
            if ($vtId != false) {
                return $vtId;
            }
            $platform_fields = array("CompanyName");
            $vtId = $this->checkMergerExistedRecordOnVtigerByFields($record, $mappedFields, $vtModule, $platform_fields);
            if ($vtId != false) {
                return $vtId;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    public function checkMergerExistedRecordOnPlatform($recordModel, $mappedFields, $platformModule, $vtModule, $vt_fields)
    {
        try {
            $platform_fields = array("PrimaryEmailAddr.Address");
            $res = $this->checkMergerExistedRecordOnPlatformByFields($recordModel, $mappedFields, $platformModule, $vtModule, $platform_fields);
            if ($res != false) {
                return $res;
            }
            $platform_fields = array("CompanyName");
            $res = $this->checkMergerExistedRecordOnPlatformByFields($recordModel, $mappedFields, $platformModule, $vtModule, $platform_fields);
            if ($res != false) {
                return $res;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
}

?>