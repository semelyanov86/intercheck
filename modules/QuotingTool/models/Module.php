<?php

/**
 * Class QuotingTool_Module_Model
 */
class QuotingTool_Module_Model extends Vtiger_Module_Model
{
    public $listSettingTable = array("quoter_quotes_settings", "quoter_invoice_settings", "quoter_salesorder_settings", "quoter_purchaseorder_settings");
    /**
     * @return array
     */
    public function getSettingLinks()
    {
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=QuotingTool&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
    public function getAllTotalFieldsSetting()
    {
        global $adb;
        $settings = array();
        $listTable = $this->listSettingTable;
        foreach ($listTable as $table) {
            $rs = $adb->pquery("SELECT total_fields,module FROM " . $table, array());
            if (0 < $adb->num_rows($rs)) {
                $moduleName = $adb->query_result($rs, 0, "module");
                $totalFields = unserialize(decode_html($adb->query_result($rs, 0, "total_fields")));
                $settings[$moduleName] = $totalFields;
            }
        }
        $customField = array();
        foreach ($settings as $module => $info) {
            foreach (array_keys($info) as $fieldName) {
                if (!in_array($fieldName, $customField) && !in_array($fieldName, array("discount_amount", "discount_percent"))) {
                    array_push($customField, $fieldName);
                }
            }
        }
        return $customField;
    }
    public static function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] =& $data;
        call_user_func_array("array_multisort", $args);
        return array_pop($args);
    }
}

?>