<?php

require_once "modules/PlatformIntegration/models/Engine.php";
class PlatformIntegration_PlatformCustomer_Model extends PlatformIntegration_Engine_Model
{
    public function preSaveToPlatform($recordModel, $changedData, $mappedFields)
    {
        try {
            $cf_quickbooks_status = $recordModel->get("cf_platform_status");
            if ($cf_quickbooks_status == "Active") {
                $changedData["Active"] = "true";
            } else {
                $changedData["Active"] = "false";
            }
            return parent::preSaveToPlatform($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
}

?>