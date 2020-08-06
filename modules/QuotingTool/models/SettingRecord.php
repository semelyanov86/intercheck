<?php

/**
 * Class QuotingTool_SettingRecord_Model
 */
class QuotingTool_SettingRecord_Model extends Vtiger_Record_Model
{
    /**
     * The white list fields to export
     * @var array
     */
    public $quotingToolSettingFields = array("background", "label_accept", "label_decline", "expire_in_days");
    /**
     * Function to get the Detail View url for the record
     * @return string - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        return "";
    }
    /**
     * @param int $templateId
     * @return Vtiger_Record_Model
     */
    public function findByTemplateId($templateId)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `vtiger_quotingtool_settings` WHERE `template_id`=? ORDER BY `id` DESC";
        $params = array($templateId);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return 0 < count($instances) ? $instances[0] : NULL;
    }
    /**
     * @param $template_id
     * @param string $description
     * @param string $label_accept
     * @param string $label_decline
     * @param string $background
     * @return int|null
     */
    public function updateSettingByTemplate($template_id, $description, $label_accept, $label_decline, $background, $expire_in_days)
    {
        $stamp = date("Y-m-d H:i:s", time());
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = NULL;
        if ($template_id) {
            $sql = "SELECT * FROM `vtiger_quotingtool_settings` WHERE `template_id`=?";
            $params = array($template_id);
            $rs = $db->pquery($sql, $params);
            if ($db->num_rows($rs)) {
                $sql = "UPDATE `vtiger_quotingtool_settings` SET `description`=?, `label_accept`=?, `label_decline`=?, `background`=?, `updated`=?, `expire_in_days`=? WHERE template_id=?";
                $params = array($description, $label_accept, $label_decline, $background, $stamp, $template_id, $expire_in_days);
            } else {
                $sql = "INSERT INTO `vtiger_quotingtool_settings` (`template_id`, `description`, `label_accept`, `label_decline`, `background`, `created`, `updated`,`expire_in_days`) VALUES (?,?,?,?,?,?,?,?)";
                $params = array($template_id, $description, $label_accept, $label_decline, $background, $stamp, $stamp, $expire_in_days);
            }
        }
        $result = $db->pquery($sql, $params);
        if (!$result) {
            return NULL;
        }
        return $template_id;
    }
    /**
     * @param $template_id
     * @param $data
     * @return int
     */
    public function saveByTemplate($template_id, $data)
    {
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = array();
        $timestamp = date("Y-m-d H:i:s", time());
        $columnNames = array("description", "label_decline", "label_accept", "background", "attachments", "template_id", "created", "updated", "expire_in_days", "success_content", "email_signed", "email_from_copy", "email_bcc_copy", "email_subject_copy", "email_body_copy", "ignore_border_email", "track_open", "decline_message", "enable_decline_mess", "date_format");
        $id = 0;
        if ($template_id) {
            $sql = "SELECT id FROM `vtiger_quotingtool_settings` WHERE `template_id`=?";
            $p = array($template_id);
            $rs = $db->pquery($sql, $p);
            if ($db->num_rows($rs)) {
                if ($row = $db->fetch_array($rs)) {
                    $id = $row["id"];
                }
                $data = array_merge($data, array("updated" => $timestamp, "expire_in_days" => $data["expire_in_days"]));
                $sqlPart2 = "";
                foreach ($data as $name => $value) {
                    if (in_array($name, $columnNames)) {
                        $sqlPart2 .= " " . $name . "=?,";
                    }
                    $params[] = $value;
                }
                $sqlPart2 = rtrim($sqlPart2, ",");
                $sqlPart3 = "WHERE id=?";
                $params[] = $id;
                $sql = "UPDATE vtiger_quotingtool_settings SET " . $sqlPart2 . " " . $sqlPart3;
            } else {
                $data = array_merge($data, array("created" => $timestamp, "updated" => $timestamp, "template_id" => $template_id));
                $sqlPart2 = " (";
                $sqlPart3 = " (";
                foreach ($data as $name => $value) {
                    if (in_array($name, $columnNames)) {
                        $sqlPart2 .= " " . $name . ",";
                        $sqlPart3 .= "?,";
                    }
                    $params[] = $value;
                }
                $sqlPart2 = rtrim($sqlPart2, ",");
                $sqlPart2 .= ") ";
                $sqlPart3 = rtrim($sqlPart3, ",");
                $sqlPart3 .= ") ";
                $sql = "INSERT INTO vtiger_quotingtool_settings " . $sqlPart2 . " VALUES " . $sqlPart3;
            }
            if (!$db->pquery($sql, $params)) {
                return 0;
            }
        }
        return $id ? $id : $db->getLastInsertID();
    }
}

?>