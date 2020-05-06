<?php

require_once "data/CRMEntity.php";
require_once "data/Tracker.php";
require_once "vtlib/Vtiger/Module.php";
require_once "modules/com_vtiger_workflow/include.inc";
class MultipleSMTP extends CRMEntity
{
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        global $adb;
        if ($event_type == "module.postinstall") {
            self::addWidgetTo();
            self::checkEnable();
            self::installWorkflow();
            self::resetValid();
        } else {
            if ($event_type == "module.disabled") {
                self::removeWidgetTo();
            } else {
                if ($event_type == "module.enabled") {
                    self::addWidgetTo();
                    self::installWorkflow();
                } else {
                    if ($event_type == "module.preuninstall") {
                        self::removeWidgetTo();
                        self::removeWorkflows();
                        self::removeValid();
                    } else {
                        if ($event_type != "module.preupdate") {
                            if ($event_type == "module.postupdate") {
                                self::removeWidgetTo();
                                self::addWidgetTo();
                                self::checkEnable();
                                self::installWorkflow();
                                self::resetValid();
                                self::addFieldAplyTo();
                                self::addFieldSequence();
                                self::addFieldSendFolder();
                                self::addFieldName();
                            }
                        }
                    }
                }
            }
        }
    }
    public static function resetValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("MultipleSMTP"));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);", array("MultipleSMTP", "0"));
    }
    public static function removeValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("MultipleSMTP"));
    }
    public static function checkEnable()
    {
        global $adb;
        $rs = $adb->pquery("SELECT `enable` FROM `multiple_smtp_settings`;", array());
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `multiple_smtp_settings` (`enable`) VALUES ('0');", array());
        }
    }
    /**
     * Add widget to other module.
     * @param unknown_type $moduleNames
     * @return unknown_type
     */
    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        include_once "vtlib/Vtiger/Module.php";
        $module = Vtiger_Module::getInstance("MultipleSMTP");
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        if ($module) {
            $module->addLink("HEADERSCRIPT", "MultipleSMTPJs", $template_folder . "/modules/MultipleSMTP/resources/MultipleSMTP.js");
        }
        $max_id = $adb->getUniqueID("vtiger_settings_field");
        $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)", array($max_id, "4", "Individual/Multi SMTP", "Settings area for Individual/Multi SMTP", "index.php?module=MultipleSMTP&parent=Settings&view=Settings", $max_id));
    }
    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        include_once "vtlib/Vtiger/Module.php";
        $module = Vtiger_Module::getInstance("MultipleSMTP");
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $linkVT6 = $template_folder . "/modules/MultipleSMTP/resources/MultipleSMTP.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        if ($module) {
            $module->deleteLink("HEADERSCRIPT", "MultipleSMTPJs", $template_folder . "/modules/MultipleSMTP/resources/MultipleSMTP.js");
            if ($vtVersion != "vt6") {
                $module->deleteLink("HEADERSCRIPT", "MultipleSMTPJs", $linkVT6);
            }
        }
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` IN (?, ?)", array("Multiple SMTP", "Individual/Multi SMTP"));
    }
    public static function installWorkflow()
    {
        global $adb;
        global $vtiger_current_version;
        $name = "MultipleSMTPEmailTask";
        $dest1 = "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
        $source1 = "modules/MultipleSMTP/workflow/" . $name . ".inc";
        if (file_exists($dest1)) {
            $file_exist1 = true;
        } else {
            if (copy($source1, $dest1)) {
                $file_exist1 = true;
            }
        }
        include_once "vtlib/Vtiger/Module.php";
        $module = Vtiger_Module::getInstance("MultipleSMTP");
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $dest2 = $template_folder . "/modules/Settings/Workflows/Tasks/" . $name . ".tpl";
        $source2 = $template_folder . "/modules/MultipleSMTP/taskforms/" . $name . ".tpl";
        if (file_exists($dest2)) {
            $file_exist2 = true;
        } else {
            if (copy($source2, $dest2)) {
                $file_exist2 = true;
            }
        }
        if ($file_exist1 && $file_exist2) {
            $sql1 = "SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?";
            $result1 = $adb->pquery($sql1, array($name));
            if ($adb->num_rows($result1) == 0) {
                $taskType = array("name" => "MultipleSMTPEmailTask", "label" => "Send email uses MultipeSMTP", "classname" => "MultipleSMTPEmailTask", "classpath" => "modules/MultipleSMTP/workflow/MultipleSMTPEmailTask.inc", "templatepath" => "modules/MultipleSMTP/taskforms/MultipleSMTPEmailTask.tpl", "modules" => array("include" => array(), "exclude" => array()), "sourcemodule" => "MultipleSMTP");
                VTTaskType::registerTaskType($taskType);
            }
        }
    }
    public static function removeWorkflows()
    {
        global $adb;
        global $vtiger_current_version;
        $sql1 = "DELETE FROM com_vtiger_workflow_tasktypes WHERE sourcemodule = ?";
        $adb->pquery($sql1, array("MultipleSMTP"));
        $sql2 = "DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?";
        $adb->pquery($sql2, array("%:\"MultipleSMTPEmailTask\":%"));
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        @shell_exec("rm -f modules/com_vtiger_workflow/tasks/MultipleSMTPEmailTask.inc");
        @shell_exec("rm -f " . $template_folder . "/modules/Settings/Workflows/Tasks/MultipleSMTPEmailTask.tpl");
    }
    public function checkColumnExist($tableName, $columnName)
    {
        global $adb;
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?";
        $res = $adb->pquery($sql, array($adb->dbName, $tableName, $columnName));
        if (0 < $adb->num_rows($res)) {
            return true;
        }
        return false;
    }
    public static function addFieldAplyTo()
    {
        global $adb;
        if (!self::checkColumnExist("vte_multiple_smtp", "replyto_email_field")) {
            $sql = "ALTER TABLE `vte_multiple_smtp` ADD COLUMN `replyto_email_field`  varchar(50) NULL;";
            $adb->pquery($sql, array());
        }
    }
    public static function addFieldSequence()
    {
        global $adb;
        if (!self::checkColumnExist("vte_multiple_smtp", "sequence")) {
            $sql = "ALTER TABLE `vte_multiple_smtp` ADD COLUMN `sequence` int(2) NULL;";
            $adb->pquery($sql, array());
        }
    }
    public static function addFieldSendFolder()
    {
        global $adb;
        if (!self::checkColumnExist("vte_multiple_smtp", "send_folder")) {
            $sql = "ALTER TABLE `vte_multiple_smtp` ADD COLUMN `send_folder` varchar(5);";
            $adb->pquery($sql, array());
            $adb->pquery("UPDATE `vte_multiple_smtp` SET `send_folder`='1'", array());
        }
    }
    public static function addFieldName()
    {
        global $adb;
        if (!self::checkColumnExist("vte_multiple_smtp", "name")) {
            $sql = "ALTER TABLE `vte_multiple_smtp` ADD COLUMN `name` varchar(50);";
            $adb->pquery($sql, array());
        }
    }
}

?>