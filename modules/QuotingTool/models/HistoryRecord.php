<?php

require_once "modules/QuotingTool/models/QuotingToolRecord.php";
/**
 * Class QuotingTool_HistoryRecord_Model
 */
class QuotingTool_HistoryRecord_Model extends QuotingTool_QuotingToolRecord_Model
{
    public $table_name = "vtiger_quotingtool_histories";
    public $table_index = "id";
    /**
     * @param int $templateId
     * @return Vtiger_Record_Model
     */
    public function findByTemplateId($templateId)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `" . $this->table_name . "` WHERE `template_id`=? ORDER BY `id` DESC";
        $params = array($templateId);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return 0 < count($instances) ? $instances[0] : NULL;
    }
    public function saveByTemplate($templateId, $data)
    {
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = array();
        $timestamp = date("Y-m-d H:i:s", time());
        $columnNames = array("created", "updated", "template_id", "body", "deleted");
        if (!$templateId) {
            return NULL;
        }
        $data = array_merge($data, array("created" => $timestamp, "updated" => $timestamp, "template_id" => $templateId));
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
        $sql = "INSERT INTO `" . $this->table_name . "` " . $sqlPart2 . " VALUES " . $sqlPart3;
        if (!$db->pquery($sql, $params)) {
            return NULL;
        }
        return $this->getById($db->getLastInsertID());
    }
    /**
     * @param int $id
     * @param array $data
     * @return Vtiger_Record_Model
     */
    public function save($id, $data)
    {
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = array();
        $timestamp = date("Y-m-d H:i:s", time());
        $columnNames = array("created", "updated", "template_id", "body", "deleted");
        if ($id) {
            $data = array_merge($data, array("updated" => $timestamp));
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
            $sql = "UPDATE `" . $this->table_name . "` SET " . $sqlPart2 . " " . $sqlPart3;
        } else {
            $data = array_merge($data, array("created" => $timestamp, "updated" => $timestamp));
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
            $sql = "INSERT INTO `" . $this->table_name . "` " . $sqlPart2 . " VALUES " . $sqlPart3;
        }
        if (!$db->pquery($sql, $params)) {
            return NULL;
        }
        $recordId = $id ? $id : $db->getLastInsertID();
        return $this->getById($recordId);
    }
    /**
     * @param $id
     * @return Vtiger_Record_Model
     */
    public function getById($id)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `" . $this->table_name . "` WHERE `id`=? AND `deleted` != 1 ORDER BY `id` LIMIT 1";
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
     * @return array
     */
    public function listAll()
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT history.id, history.body, history.created, template.filename, template.module\r\n                  FROM `" . $this->table_name . "` AS history\r\n                  INNER JOIN `vtiger_quotingtool` AS template ON (history.template_id = template.id AND template.deleted != 1)\r\n                  WHERE history.deleted != 1\r\n                  ORDER BY history.id ASC";
        $rs = $db->pquery($sql);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return $instances;
    }
    /**
     * @param int $templateId
     * @return array
     */
    public function listAllByTemplateId($templateId)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT history.id, history.body, history.created, template.filename, template.module\r\n                  FROM `" . $this->table_name . "` AS history\r\n                  INNER JOIN `vtiger_quotingtool` AS template ON (history.template_id = template.id AND template.deleted != 1)\r\n                  WHERE history.deleted != 1 AND history.template_id = ?\r\n                  ORDER BY history.id ASC";
        $params = array($templateId);
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }
        return $instances;
    }
    /**
     * @param array $historyIds
     * @return bool
     */
    public function removeHistories($historyIds)
    {
        $db = PearDatabase::getInstance();
        $timestamp = date("Y-m-d H:i:s", time());
        $sql = "UPDATE `" . $this->table_name . "` \r\n                  SET \r\n                    deleted = 1, \r\n                    updated = ? \r\n                WHERE id IN (?)";
        $params = array($timestamp, implode(",", $historyIds));
        if (!$db->pquery($sql, $params)) {
            return false;
        }
        return true;
    }
}

?>