<?php

require_once "modules/PlatformIntegration/models/PlatformItem.php";
class PlatformIntegration_Services_Model extends PlatformIntegration_PlatformItem_Model
{
    public function preSaveToQBO($recordModel, $changedData, $mappedFields)
    {
        try {
            $changedData["Type"] = "Service";
            return parent::preSaveToQBO($recordModel, $changedData, $mappedFields);
        } catch (Exception $ex) {
            return array("code" => "Failed", "error" => $ex->getMessage());
        }
    }
}

?>