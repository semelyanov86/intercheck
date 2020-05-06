<?php

class MultipleSMTP_Module_Model extends Emails_Module_Model
{
    /**
     * Function to save a given record model of the current module
     * @param Vtiger_Record_Model $recordModel
     */
    public function saveRecord($recordModel)
    {
        $moduleName = $this->get("name");
        $focus = CRMEntity::getInstance("Emails");
        $fields = $focus->column_fields;
        foreach ($fields as $fieldName => $fieldValue) {
            $fieldValue = $recordModel->get($fieldName);
            if (is_array($fieldValue)) {
                $focus->column_fields[$fieldName] = $fieldValue;
            } else {
                if ($fieldValue !== NULL) {
                    $focus->column_fields[$fieldName] = decode_html($fieldValue);
                }
            }
        }
        $focus->mode = $recordModel->get("mode");
        $focus->id = $recordModel->getId();
        $moduleName = "Emails";
        $focus->save($moduleName);
        return $recordModel->setId($focus->id);
    }
    public function getSettingLinks()
    {
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Settings", "linkurl" => "index.php?module=MultipleSMTP&parent=Settings&view=Settings", "linkicon" => "");
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=MultipleSMTP&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
    public function getUserServer($userId, $sid = NULL)
    {
        global $adb;
        $info = array();
        if ($sid) {
            $rsServer = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE id=?", array($sid));
            if ($adb->num_rows($rsServer)) {
                $info = $adb->query_result_rowdata($rsServer, 0);
            }
        }
        return $info;
    }
    public function addUserServer($userId)
    {
        global $adb;
        $adb->pquery("INSERT INTO `vte_multiple_smtp` (`userid`) VALUES (?)", array($userId));
        return array("id" => $adb->getLastInsertID());
    }
    public function getUserServers($userId)
    {
        global $adb;
        $rsServer = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE userid=? ORDER BY sequence", array($userId));
        $servers = array();
        if (0 < $adb->num_rows($rsServer)) {
            $i = 0;
            while ($row = $adb->fetch_array($rsServer)) {
                $tmp = $adb->query_result_rowdata($rsServer, $i);
                $i++;
                $servers[] = $tmp;
            }
        }
        return $servers;
    }
    public static function getLastSequence($userId)
    {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT MAX(sequence) as max FROM vte_multiple_smtp WHERE userid = ?";
        $result = $adb->pquery($sql, array($userId), true);
        return $adb->query_result($result, 0, "max");
    }
    public function updateSequence($params)
    {
        $adb = PearDatabase::getInstance();
        $data = $params["data"];
        foreach ($data as $key => $value) {
            $sql = "UPDATE vte_multiple_smtp SET sequence = ? WHERE id = ?;";
            $adb->pquery($sql, array($value["index"], $key), true);
        }
    }
}

?>