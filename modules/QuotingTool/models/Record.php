<?php

/**
 * Class QuotingTool_Record_Model
 */
class QuotingTool_Record_Model extends Vtiger_Record_Model
{
    /**
     * The white list fields to export
     * @var array
     */
    public $quotingToolFields = array("filename", "module", "body", "header", "content", "footer", "anwidget", "anblock", "description", "email_subject", "email_content", "mapping_fields", "attachments", "is_active", "createnewrecords", "linkproposal");
    /**
     * Function to get the Detail View url for the record
     * @return string - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        return "index.php?module=QuotingTool&view=Edit&record=" . $this->getId();
    }
    /**
     * @param $id
     * @param $data
     * @return Vtiger_Record_Model
     */
    public function save($id, $data)
    {
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $params = array();
        $timestamp = date("Y-m-d H:i:s", time());
        $columnNames = array("filename", "module", "body", "header", "content", "footer", "description", "deleted", "email_subject", "email_content", "mapping_fields", "attachments", "created", "updated", "anwidget", "anblock", "is_active", "createnewrecords", "linkproposal", "owner", "share_to", "share_status", "settings_layout", "custom_function", "file_name", "check_attach_file");
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
            $sql = "UPDATE vtiger_quotingtool SET " . $sqlPart2 . " " . $sqlPart3;
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
            $sql = "INSERT INTO vtiger_quotingtool " . $sqlPart2 . " VALUES " . $sqlPart3;
        }
        if (!$db->pquery($sql, $params)) {
            return NULL;
        }
        $recordId = $id ? $id : $db->getLastInsertID();
        return $this->getById($recordId);
    }
    /**
     * @return array
     */
    public static function findAll()
    {
        global $current_user;
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT q.* \r\n                FROM vtiger_quotingtool q\r\n                INNER JOIN vtiger_quotingtool_settings qs ON qs.template_id = q.id\r\n                WHERE q.deleted != 1\r\n                order by q.updated desc";
        $rsQuotingTool = $db->pquery($sql);
        if ($db->num_rows($rsQuotingTool)) {
            while ($data = $db->fetch_array($rsQuotingTool)) {
                if ($data["created"] || $data["updated"]) {
                    $startDateTimeByUserCreate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s", strtotime($data["created"])));
                    $startDateTimeByUserFormatCreate = DateTimeField::convertToUserFormat($startDateTimeByUserCreate->format("Y-m-d H:i:s"));
                    $startDateTimeByUserUpdate = DateTimeField::convertToUserTimeZone(date("Y-m-d H:i:s", strtotime($data["updated"])));
                    $startDateTimeByUserFormatUpdate = DateTimeField::convertToUserFormat($startDateTimeByUserUpdate->format("Y-m-d H:i:s"));
                    list($startDate, $startTime) = explode(" ", $startDateTimeByUserFormatCreate);
                    list($dueDate, $endTime) = explode(" ", $startDateTimeByUserFormatUpdate);
                    $currentUser = Users_Record_Model::getCurrentUserModel();
                    if ($currentUser->get("hour_format") == "12") {
                        $startTime = Vtiger_Time_UIType::getTimeValueInAMorPM($startTime);
                        $endTime = Vtiger_Time_UIType::getTimeValueInAMorPM($endTime);
                    }
                    $createTime = $startDate . " " . $startTime;
                    $updateTime = $dueDate . " " . $endTime;
                    $data["created"] = $createTime;
                    $data["updated"] = $updateTime;
                }
                $userId = array();
                if ($data["share_status"] && $data["share_status"] != "public") {
                    if ($data["share_status"] == "private") {
                        if ($current_user->id != $data["owner"]) {
                            continue;
                        }
                    } else {
                        if ($data["share_status"] == "share") {
                            $userId[] = $data["owner"];
                            $share_to = $data["share_to"];
                            $share_to = explode("|##|", $share_to);
                            foreach ($share_to as $key => $member) {
                                $member = explode(":", $member);
                                list($typeMember, $idMember) = $member;
                                if ($typeMember == "Users") {
                                    $userId[] = $idMember;
                                } else {
                                    if ($typeMember == "Roles") {
                                        $sql = "select * from vtiger_user2role where roleid='" . $idMember . "'";
                                        $rs = $db->pquery($sql);
                                        while ($dataRole = $db->fetch_array($rs)) {
                                            $userId[] = $dataRole["userid"];
                                        }
                                    } else {
                                        if ($typeMember == "Groups") {
                                            $sql = "select * from vtiger_users2group where groupid='" . $idMember . "'";
                                            $rs = $db->pquery($sql);
                                            while ($dataGroup = $db->fetch_array($rs)) {
                                                $userId[] = $dataGroup["userid"];
                                            }
                                        } else {
                                            if ($typeMember == "RoleAndSubordinates") {
                                                $rsRoles = $db->pquery("select roleid from vtiger_role where parentrole like '%" . $idMember . "%'");
                                                while ($dataRoles = $db->fetch_array($rsRoles)) {
                                                    $sql = "select * from vtiger_user2role where roleid='" . $dataRoles["roleid"] . "'";
                                                    $rs = $db->pquery($sql);
                                                    while ($dataRole = $db->fetch_array($rs)) {
                                                        $userId[] = $dataRole["userid"];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (0 < count($userId)) {
                    if (in_array($current_user->id, $userId)) {
                        $instances[] = new self($data);
                    } else {
                        continue;
                    }
                } else {
                    $instances[] = new self($data);
                }
            }
        }
        return $instances;
    }
    /**
     * @param $id
     * @return Vtiger_Record_Model
     */
    public function getById($id)
    {
        $db = PearDatabase::getInstance();
        $instances = array();
        $sql = "SELECT * FROM `vtiger_quotingtool` WHERE `id`=? AND `deleted` != 1 ORDER BY `id` LIMIT 1";
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
        global $current_user;
        $db = PearDatabase::getInstance();
        $instances = array();
        $uitypes = array();
        switch ($module) {
            case "Accounts":
                $uitypes = array("51", "73", "68");
                break;
            case "Contacts":
                $uitypes = array("57", "68");
                break;
            case "Campaigns":
                $uitypes = array("58");
                break;
            case "Products":
                $uitypes = array("59");
                break;
            case "Vendors":
                $uitypes = array("75", "81");
                break;
            case "Potentials":
                $uitypes = array("76");
                break;
            case "Quotes":
                $uitypes = array("78");
                break;
            case "SalesOrder":
                $uitypes = array("80");
                break;
        }
        if (empty($uitypes)) {
            $uitypes = array("10");
            $conditionQuery = " AND `vtiger_tab`.name = '" . $module . "'  ";
        }
        $arrModules = array();
        $queryChild = "SELECT * FROM (\r\n            SELECT vtiger_tab.tabid,vtiger_tab.`name` as relmodule\r\n            FROM `vtiger_field`\r\n            INNER JOIN vtiger_tab ON vtiger_field.tabid=vtiger_tab.tabid\r\n            WHERE vtiger_field.presence <> 1 and vtiger_tab.tabid in\r\n\t\t\t\t\t\t(\r\n\t\t\t\t\t\tselect vtiger_relatedlists.related_tabid  from vtiger_relatedlists \r\n\t\t\t\t\t\tINNER JOIN vtiger_tab on vtiger_tab.tabid=vtiger_relatedlists.tabid\r\n\t\t\t\t\t\twhere vtiger_tab.name='" . $module . "'\r\n\t\t\t\t\t\t)\r\n           ) as temp\r\n            WHERE relmodule NOT IN ('Webmails', 'SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter','PBXManager')\r\n            AND relmodule <> ? GROUP BY tabid\r\n            ";
        $reChild = $db->pquery($queryChild, array($module));
        if (0 < $db->num_rows($reChild)) {
            while ($rowChild = $db->fetchByAssoc($reChild)) {
                $arrModules[] = $rowChild["relmodule"];
            }
        }
        $sql = "SELECT * FROM (\r\n                SELECT * FROM `vtiger_quotingtool` WHERE `module` = ? AND `deleted` != 1 AND `is_active` = 1 \r\n                UNION\r\n                SELECT * FROM `vtiger_quotingtool` WHERE `module` IN (" . generateQuestionMarks($arrModules) . ") AND `deleted` != 1 AND `is_active` = 1 AND `createnewrecords` = 1 \r\n                ) as temp ORDER BY filename asc ";
        $params = array($module, $arrModules);
        $rsQuotingTool = $db->pquery($sql, $params);
        if ($db->num_rows($rsQuotingTool)) {
            while ($data = $db->fetch_array($rsQuotingTool)) {
                $userId = array();
                if ($data["share_status"] && $data["share_status"] != "public") {
                    if ($data["share_status"] == "private") {
                        if ($current_user->id != $data["owner"]) {
                            continue;
                        }
                    } else {
                        if ($data["share_status"] == "share") {
                            $userId[] = $data["owner"];
                            $share_to = $data["share_to"];
                            $share_to = explode("|##|", $share_to);
                            foreach ($share_to as $key => $member) {
                                $member = explode(":", $member);
                                list($typeMember, $idMember) = $member;
                                if ($typeMember == "Users") {
                                    $userId[] = $idMember;
                                } else {
                                    if ($typeMember == "Roles") {
                                        $sql = "select * from vtiger_user2role where roleid='" . $idMember . "'";
                                        $rs = $db->pquery($sql);
                                        while ($dataRole = $db->fetch_array($rs)) {
                                            $userId[] = $dataRole["userid"];
                                        }
                                    } else {
                                        if ($typeMember == "Groups") {
                                            $sql = "select * from vtiger_users2group where groupid='" . $idMember . "'";
                                            $rs = $db->pquery($sql);
                                            while ($dataGroup = $db->fetch_array($rs)) {
                                                $userId[] = $dataGroup["userid"];
                                            }
                                        } else {
                                            if ($typeMember == "RoleAndSubordinates") {
                                                $rsRoles = $db->pquery("select roleid from vtiger_role where parentrole like '%" . $idMember . "%'");
                                                while ($dataRoles = $db->fetch_array($rsRoles)) {
                                                    $sql = "select * from vtiger_user2role where roleid='" . $dataRoles["roleid"] . "'";
                                                    $rs = $db->pquery($sql);
                                                    while ($dataRole = $db->fetch_array($rs)) {
                                                        $userId[] = $dataRole["userid"];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (0 < count($userId)) {
                    if (in_array($current_user->id, $userId)) {
                        $instances[] = new self($data);
                    } else {
                        continue;
                    }
                } else {
                    $instances[] = new self($data);
                }
            }
        }
        return $instances;
    }
    public function getRelatedModules($currentModuleModel)
    {
        $relatedModules = array();
        $referenceFields = $currentModuleModel->getFieldsByType("reference");
        foreach ($referenceFields as $fieldModel) {
            $referenceModules = $fieldModel->getReferenceList();
            if (count($referenceModules) == 2 && $referenceModules[0] == "Campaigns") {
                unset($referenceModules[0]);
            }
            foreach ($referenceModules as $k => $relatedModule) {
                if (!in_array($relatedModule, $relatedModules) && $relatedModule != "Users") {
                    $relatedModules[] = $relatedModule;
                }
            }
        }
        return $relatedModules;
    }
    /**
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $db = PearDatabase::getInstance();
        $sql = NULL;
        $stamp = date("Y-m-d H:i:s", time());
        $sql = "UPDATE vtiger_quotingtool SET deleted=1, updated=? WHERE id=?";
        $params = array($stamp, $id);
        $result = $db->pquery($sql, $params);
        $sql = "SELECT `module` FROM `vtiger_quotingtool` WHERE `id` = ? LIMIT 1";
        $rs = $db->pquery($sql, array($id));
        if (0 < $db->num_rows($rs)) {
            while ($row = $db->fetchByAssoc($rs)) {
                $moduleName = $row["module"];
                $query = $db->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `module` = ? AND `deleted` != 1", array($moduleName));
                $numRow = $db->num_rows($query);
                $allRelatedModule = self::getAllRelatedModule("");
                if ($numRow == 0 && in_array($moduleName, $allRelatedModule) == false) {
                    $moduleInstance = Vtiger_Module::getInstance($moduleName);
                    $checkLink = $db->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($moduleInstance->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
                    if (0 < $db->num_rows($checkLink)) {
                        $js_widgetType = "LISTVIEWMASSACTION";
                        $js_widgetLabel = "Export to PDF/Email";
                        $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                        $moduleInstance->deleteLink($js_widgetType, $js_widgetLabel, $js_link);
                        $moduleInstance->deleteLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
                    }
                }
            }
        }
        return $result ? true : false;
    }
    public function compileRecord()
    {
    }
    /**
     * @param int $entityId
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function decompileRecord($entityId = 0, $fields = array(), $options = array(), $customFunction = array())
    {
        $quotingTool = new QuotingTool();
        if (!empty($fields)) {
            foreach ($fields as $field) {
                switch ($field) {
                    case "header":
                    case "content":
                    case "footer":
                    case "email_subject":
                    case "email_content":
                        $tmp = $this->get($field);
                        $tmp = $tmp ? base64_decode($tmp) : "";
                        if ($entityId != "" && $_REQUEST["mode"] != "download" && $_REQUEST["mode"] != "save_proposal") {
                            $tmp = $quotingTool->parseTokens($tmp, $this->get("module"), $entityId, $customFunction);
                        }
                        $this->set($field, $tmp);
                        break;
                    default:
                        break;
                }
            }
        }
        return $this;
    }
    public function isIconHelpText()
    {
        global $adb;
        $sql = "SELECT * from vtiger_quotingtool_helptext";
        $params = array();
        $rs = $adb->pquery($sql, $params);
        $result = array();
        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetchByAssoc($rs)) {
                $result[] = $row;
            }
        }
        return $result;
    }
    public static function checkRemoveLink($id)
    {
        $db = PearDatabase::getInstance();
        $primaryName = "";
        $sql = "SELECT `module`, `createnewrecords` FROM `vtiger_quotingtool` WHERE `id`=? AND `deleted` != 1 ORDER BY `id` LIMIT 1";
        $params = array($id);
        $rs = $db->pquery($sql, $params);
        if (0 < $db->num_rows($rs)) {
            $primaryName = $db->query_result($rs, 0, "module");
            $createNewRecords = $db->query_result($rs, 0, "createnewrecords");
            $moduleInstance = Vtiger_Module::getInstance($primaryName);
            if ($createNewRecords == 1) {
                $allRelatedModule = self::getAllRelatedModule($primaryName);
                $arrModule = self::getRelatedModules($moduleInstance);
                foreach ($arrModule as $val) {
                    $parentModule = Vtiger_Module::getInstance($val);
                    $listRecord = $db->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `module`=? AND `deleted` != 1", array($val));
                    if ($db->num_rows($listRecord) == 0 && in_array($val, $allRelatedModule) == false) {
                        $checkLink = $db->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($parentModule->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
                        if (0 < $db->num_rows($checkLink)) {
                            $js_widgetType = "LISTVIEWMASSACTION";
                            $js_widgetLabel = "Export to PDF/Email";
                            $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                            $parentModule->deleteLink($js_widgetType, $js_widgetLabel, $js_link);
                            $parentModule->deleteLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
                        }
                    }
                }
            }
            $allRelatedModule = self::getAllRelatedModule("");
            $listRecord = $db->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `module`=? AND `deleted` != 1", array($primaryName));
            if ($db->num_rows($listRecord) == 1 && in_array($primaryName, $allRelatedModule) == false) {
                $checkLink = $db->pquery("SELECT `tabid` FROM `vtiger_links` WHERE `tabid`=? AND `linkurl` =?", array($moduleInstance->getId(), "javascript:QuotingToolJS.triggerShowModal()"));
                if (0 < $db->num_rows($checkLink)) {
                    $js_widgetType = "LISTVIEWMASSACTION";
                    $js_widgetLabel = "Export to PDF/Email";
                    $js_link = "javascript:QuotingToolJS.triggerShowModal()";
                    $moduleInstance->deleteLink($js_widgetType, $js_widgetLabel, $js_link);
                    $moduleInstance->deleteLink("DETAILVIEWBASIC", $js_widgetLabel, $js_link);
                }
            }
        }
        return $primaryName;
    }
    public function getModuleById($id)
    {
        global $adb;
        $rs = $adb->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `id` = ? LIMIT 1", array($id));
        if (0 < $adb->num_rows($rs)) {
            return $adb->query_result($rs, 0, "module");
        }
    }
    public static function getAllRelatedModule($primaryModule)
    {
        global $adb;
        $query = $adb->pquery("SELECT `module` FROM `vtiger_quotingtool` WHERE `deleted` != 1 AND `createnewrecords` = 1", array());
        $allModule = array();
        if (0 < $adb->num_rows($query)) {
            while ($row = $adb->fetchByAssoc($query)) {
                $allModule[] = $row["module"];
            }
        }
        $relationModule = array();
        foreach ($allModule as $module) {
            if ($primaryModule == $module) {
                continue;
            }
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $relationModule = array_merge($relationModule, self::getRelatedModules($moduleModel));
        }
        $relationModule = array_unique($relationModule);
        return $relationModule;
    }
    public function getAttachmentFile($fid, $encFileName)
    {
        global $upload_badext;
        global $site_URL;
        $db = PearDatabase::getInstance();
        $query = "SELECT vtiger_attachments.* FROM vtiger_attachments\r\n\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid\r\n\t\t\t\t\tWHERE vtiger_attachments.attachmentsid=? AND vtiger_attachments.name=? LIMIT 1";
        $result = $db->pquery($query, array($fid, $encFileName));
        if ($result && $db->num_rows($result)) {
            $resultData = $db->fetch_array($result);
            $fileId = $resultData["attachmentsid"];
            $filePath = $resultData["path"];
            $fileName = $resultData["name"];
            $storedFileName = $resultData["storedname"];
            $fileType = $resultData["type"];
            $sanitizedFileName = sanitizeUploadFileName($fileName, $upload_badext);
            if (!empty($encFileName)) {
                if (!empty($storedFileName)) {
                    $finalFilePath = $filePath . $fileId . "_" . $storedFileName;
                } else {
                    if (is_null($storedFileName)) {
                        $finalFilePath = $filePath . $fileId . "_" . $encFileName;
                    }
                }
                $isFileExist = false;
                if (file_exists($finalFilePath)) {
                    $isFileExist = true;
                } else {
                    $finalFilePath = $filePath . $fileId . "_" . $sanitizedFileName;
                    if (file_exists($finalFilePath)) {
                        $isFileExist = true;
                    }
                }
                if ($isFileExist) {
                    $site = rtrim($site_URL, "/");
                    return $site . "/" . $finalFilePath;
                }
            }
        }
    }
    public function getDateFormat()
    {
        global $adb;
        $sql = "SELECT * from `vtiger_date_format`";
        $params = array();
        $rs = $adb->pquery($sql, $params);
        $result = array();
        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetchByAssoc($rs)) {
                $result[$row["date_format"]] = $row["date_format"];
            }
        }
        $result["M-d-Y"] = date("M-d-Y");
        $result["d-M-Y"] = date("d-M-Y");
        return $result;
    }
}

?>