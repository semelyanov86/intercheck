<?php

class PlatformIntegrationLinks extends CRMEntity
{
    public $db = NULL;
    public $log = NULL;
    public $table_name = "vtiger_platformintegrationlinks";
    public $table_index = "platformintegrationlinkid";
    public $column_fields = array();
    /** Indicator if this is a custom module or standard module */
    public $IsCustomModule = true;
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = array("vtiger_platformintegrationlinkscf", "platformintegrationlinkid");
    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = array("vtiger_crmentity", "vtiger_platformintegrationlinks", "vtiger_platformintegrationlinkscf");
    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = array("vtiger_crmentity" => "crmid", "vtiger_platformintegrationlinks" => "platformintegrationlinkid", "vtiger_platformintegrationlinkscf" => "platformintegrationlinkid");
    /**
     * Mandatory for Listing (Related listview)
     */
    public $list_fields = array("Platform module" => array("vtiger_platformintegrationlinks", "platform_module"), "Platform record" => array("vtiger_platformintegrationlinks", "platform_id"), "Vtiger module" => array("vtiger_platformintegrationlinks", "vt_module"), "Vtiger record" => array("vtiger_platformintegrationlinks", "vt_id"), "Latest value" => array("vtiger_platformintegrationlinks", "latest_value"), "Last update" => array("vtiger_platformintegrationlinks", "latest_update"), "Last update(on Vtiger)" => array("vtiger_platformintegrationlinks", "latest_update_vt"));
    public $list_fields_name = array("Platform module" => "platform_module", "Platform record" => "qb_id", "Vtiger module" => "vt_module", "Vtiger record" => "vt_id", "Latest value" => "latest_value", "Last update" => "latest_update", "Last update(on Vtiger)" => "latest_update_vt");
    public $list_link_field = "platform_module";
    public $search_fields = array("Platform module" => array("vtiger_platformintegrationlinks", "platform_module"), "Platform record" => array("vtiger_platformintegrationlinks", "platform_id"), "Vtiger module" => array("vtiger_platformintegrationlinks", "vt_module"), "Vtiger record" => array("vtiger_platformintegrationlinks", "vt_id"));
    public $search_fields_name = array("Platform module" => "platform_module", "Platform record" => "platform_id", "Vtiger module" => "vt_module", "Vtiger record" => "vt_id");
    public $popup_fields = array("platform_module", "platform_id", "vt_module", "vt_id");
    public $sortby_fields = array();
    public $def_basicsearch_col = "platform_module";
    public $def_detailview_recname = "";
    public $required_fields = array();
    public $mandatory_fields = array("platform_module", "platform_id", "vt_module", "vt_id");
    public $default_order_by = "platform_module";
    public $default_sort_order = "ASC";
    public function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = new PearDatabase();
        $this->log = $log;
    }
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type
     */
    public function save_module()
    {
    }
    public function vtlib_handler($moduleName, $eventType)
    {
        require_once "include/utils/utils.php";
        global $adb;
        if ($eventType == "module.postinstall") {
            $this->addUserSpecificTable();
            $this->addDefaultModuleTypeEntity();
            $this->addEntityIdentifier();
            $this->addModuleIcon();
        } else {
            if ($eventType != "module.disabled") {
                if ($eventType == "module.enabled") {
                    $this->addUserSpecificTable();
                } else {
                    if ($eventType == "module.preuninstall") {
                        vtws_deleteWebserviceEntity("PlatformIntegrationLinks");
                    } else {
                        if ($eventType != "module.preupdate") {
                            if ($eventType == "module.postupdate") {
                                $this->addUserSpecificTable();
                                $this->addDefaultModuleTypeEntity();
                                $this->addEntityIdentifier();
                                $this->addModuleIcon();
                            }
                        }
                    }
                }
            }
        }
    }
    public function addModuleIcon()
    {
        global $root_directory;
        global $vtiger_current_version;
        $source1 = $root_directory . "/modules/PlatformIntegrationLinks/resources/PlatformIntegrationLinks.png";
        $source2 = $root_directory . "/modules/PlatformIntegrationLinks/resources/moduleImages/PlatformIntegrationLinks.png";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $dest1 = $root_directory . "/layouts/vlayout/skins/images/PlatformIntegrationLinks.png";
            copy($source1, $dest1);
        } else {
            $dest1 = $root_directory . "/layouts/v7/skins/images/PlatformIntegrationLinks.png";
            copy($source1, $dest1);
            $dest2 = $root_directory . "/layouts/v7/skins/images/moduleImages/PlatformIntegrationLinks.png";
            copy($source2, $dest2);
        }
    }
    public function addDefaultModuleTypeEntity()
    {
        global $adb;
        $rs = $adb->query("SELECT * FROM `vtiger_ws_entity` WHERE `name`='PlatformIntegrationLinks'");
        if ($adb->num_rows($rs) == 0) {
            $adb->query("UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)");
            $entityId = $adb->getUniqueID("vtiger_ws_entity");
            $adb->pquery("INSERT INTO `vtiger_ws_entity` (`id`, `name`, `handler_path`, `handler_class`, `ismodule`) VALUES (?, ?, ?, ?, ?);", array($entityId, "PlatformIntegrationLinks", "include/Webservices/VtigerModuleOperation.php", "VtigerModuleOperation", "1"));
        }
    }
    public function addEntityIdentifier()
    {
        global $adb;
        $moduleName = "PlatformIntegrationLinks";
        $sql = "SELECT tabid FROM vtiger_tab WHERE `name` = ?";
        $result = $adb->pquery($sql, array($moduleName));
        if (0 < $adb->num_rows($result)) {
            $tabid = $adb->fetchByAssoc($result, 0)["tabid"];
            $result2 = $adb->pquery("SELECT tabid FROM vtiger_entityname WHERE tablename=? AND tabid=?", array($this->table_name, $tabid));
            if ($adb->num_rows($result2) == 0) {
                $adb->pquery("INSERT INTO vtiger_entityname(tabid, modulename, tablename, fieldname, entityidfield, entityidcolumn) VALUES(?,?,?,?,?,?)", array($tabid, $moduleName, $this->table_name, "platform_module,platform_id,vt_module,vt_id", $this->table_index, $this->table_index));
            } else {
                $adb->pquery("UPDATE vtiger_entityname SET fieldname=?,entityidfield=?,entityidcolumn=? WHERE tablename=? AND tabid=?", array("platform_module,platform_id,vt_module,vt_id", $this->table_index, $this->table_index, $this->table_name, $tabid));
            }
        }
    }
    public function addUserSpecificTable()
    {
        global $vtiger_current_version;
        if (!version_compare($vtiger_current_version, "7.0.0", "<")) {
            $moduleName = "PlatformIntegrationLinks";
            $moduleUserSpecificTable = Vtiger_Functions::getUserSpecificTableName($moduleName);
            if (!Vtiger_Utils::CheckTable($moduleUserSpecificTable)) {
                Vtiger_Utils::CreateTable($moduleUserSpecificTable, "(`recordid` INT(19) NOT NULL,\n\t\t\t\t\t   `userid` INT(19) NOT NULL,\n\t\t\t\t\t   `starred` varchar(100) NULL,\n\t\t\t\t\t   Index `record_user_idx` (`recordid`, `userid`)\n\t\t\t\t\t\t)", true);
            }
        }
    }
}

?>