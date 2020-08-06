<?php

/**
 * Class QuotingTool_TransactionRecord_Model
 */
class QuotingTool_TransactionRecord_Model extends Vtiger_Record_Model
{
    /**
     * Function to get the Detail View url for the record
     * @return string - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        return "";
    }
    /**
     * @return array
     */
    public static function findAll()
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $rs = $db->pquery("SELECT * FROM `vtiger_quotingtool_transactions` WHERE `deleted` != 1");
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return $instances;
    }
    /**
     * @param int $id
     * @return null
     */
    public function findById($id)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT `transaction`.`id`, `transaction`.`module`, `transaction`.`record_id`, `transaction`.`sign_to`, `transaction`.`initials_primary`, `transaction`.`initials_secondary`,\r\n                    `transaction`.`title_signature_primary`,`transaction`.`title_signature_secondary`,`transaction`.`signature`, `transaction`.`signature_name`,\r\n                    `transaction`.`secondary_signature`, `transaction`.`secondary_signature_name`, `transaction`.`is_draw_signature`,\r\n                    `transaction`.`template_id`, `transaction`.`status`, `transaction`.`secondary_status`, `transaction`.`created`,\r\n                    `transaction`.`updated`, `transaction`.`full_content`, `transaction`.`hash`,\r\n                    `template`.`filename`, `template`.`header`, `template`.`content`, `template`.`footer`, \r\n                    `template`.`description`, `template`.`attachments`,`template`.`settings_layout`,`template`.`custom_function`,`template`.`file_name`, `settings`. `label_accept`,\r\n                    `settings`. `label_decline`, `settings`. `background`, `settings`. `expire_in_days`,`settings`. `success_content`,`settings`.`track_open`,`settings`.`decline_message` ,`settings`.`enable_decline_mess`,`settings`.`date_format`   \r\n                FROM `vtiger_quotingtool_transactions` AS `transaction`\r\n                INNER JOIN `vtiger_quotingtool` AS `template` ON (`transaction`.`template_id` = `template`.`id` AND `template`.deleted != 1)\r\n                LEFT JOIN `vtiger_quotingtool_settings` AS `settings` ON (`template`.`id` = `settings`.`template_id`)\r\n                WHERE `transaction`.`id`=? AND `transaction`.`deleted` != 1\r\n                ORDER BY `transaction`.`id` DESC\r\n                LIMIT 1";
        $params = array($id);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return 0 < count($instances) ? $instances[0] : NULL;
    }
    /**
     * @param string $module
     * @return array
     */
    public function findByModule($module)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `vtiger_quotingtool_transactions` WHERE `module`=? AND `deleted` != 1 LIKE ? ORDER BY `id` DESC";
        $params = array($module);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return $instances;
    }
    /**
     * @param int $id
     * @param $templateId
     * @param string $module
     * @param int $recordId
     * @param string $signature
     * @param string $signatureName
     * @param string $content
     * @param string $description
     * @return int|null
     */
    public function saveTransaction($id, $templateId, $module, $recordId, $signature, $signatureName, $content, $description)
    {
        $timestamp = time();
        $stamp = date("Y-m-d H:i:s", $timestamp);
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = NULL;
        if ($id) {
            $sql = "UPDATE `vtiger_quotingtool_transactions` SET `template_id`=?, `module`=?, `record_id`=?, `signature`=?,\r\n                    `signature_name`=?, `description`=?, `full_content`=?, `updated`=? WHERE id=?";
            $params = array($templateId, $module, $recordId, $signature, $signatureName, $description, $content, $stamp, $id);
        } else {
            $hash = $timestamp . QuotingToolUtils::generateToken();
            $sql = "INSERT INTO `vtiger_quotingtool_transactions` (`template_id`, `module`, `record_id`, `signature`, `signature_name`,\r\n                    `full_content`, `description`, `created`, `updated`, `hash`) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $params = array($templateId, $module, $recordId, $signature, $signatureName, $content, $description, $stamp, $stamp, $hash);
        }
        $result = $db->pquery($sql, $params);
        if (!$result) {
            return NULL;
        }
        $returnId = $id ? $id : $db->getLastInsertID();
        return $returnId;
    }
    /**
     * @param int $id
     * @param string $signature
     * @param string $signatureName
     * @param string $dFullContent
     * @param string $description
     * @return int|null
     */
    public function updateSignature($id, $signature, $signatureName, $dFullContent, $description = NULL, $sign_to = "", $initialsName, $titleSignature, $isDrawSignature)
    {
        $stamp = date("Y-m-d H:i:s", time());
        $db = PearDatabase::getInstance();
        $sign_to = strtoupper($sign_to);
        if ($sign_to != "SECONDARY") {
            $sql = "UPDATE `vtiger_quotingtool_transactions` SET `sign_to`=?, `signature`=?, `signature_name`=?, `full_content`=?, `description`=?, `updated`=?, initials_primary=?, title_signature_primary=?,is_draw_signature=?  WHERE `id`=?";
        } else {
            $sql = "UPDATE `vtiger_quotingtool_transactions` SET `sign_to`=?, `secondary_signature`=?, `secondary_signature_name`=?, `full_content`=?, `description`=?, `updated`=?, initials_secondary=?, title_signature_secondary=?, is_draw_signature=? WHERE `id`=?";
        }
        $params = array($sign_to, $signature, $signatureName, $dFullContent, $description, $stamp, $initialsName, $titleSignature, $isDrawSignature, $id);
        $result = $db->pquery($sql, $params);
        return $result ? $id : NULL;
    }
    /**
     * @param int $id
     * @param int $status
     * @return int|null
     */
    public function changeStatus($id, $status, $sign_to = "")
    {
        $stamp = date("Y-m-d H:i:s", time());
        $db = PearDatabase::getInstance();
        $sign_to = strtoupper($sign_to);
        if ($sign_to != "SECONDARY") {
            $sql = "UPDATE `vtiger_quotingtool_transactions` SET `status`=?, `updated`=? WHERE `id`=?";
        } else {
            $sql = "UPDATE `vtiger_quotingtool_transactions` SET `secondary_status`=?, `updated`=? WHERE `id`=?";
        }
        $params = array($status, $stamp, $id);
        $result = $db->pquery($sql, $params);
        return $result ? $id : NULL;
    }
    /**
     * @param string $module
     * @param int $recordId
     * @return Vtiger_Record_Model
     */
    public function getLastTransactionByModule($module, $recordId)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `vtiger_quotingtool_transactions` WHERE `module` LIKE ? AND `record_id`=? AND `deleted` != 1 ORDER BY `id` DESC LIMIT 1";
        $params = array($module, $recordId);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return 0 < count($instances) ? $instances[0] : NULL;
    }
}

?>