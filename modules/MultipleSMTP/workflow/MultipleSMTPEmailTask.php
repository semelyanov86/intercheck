<?php

require_once "modules/com_vtiger_workflow/VTEntityCache.inc";
require_once "modules/com_vtiger_workflow/VTWorkflowUtils.php";
require_once "modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc";
require_once "modules/Emails/mail.php";
require_once "include/simplehtmldom/simple_html_dom.php";
require_once "modules/MultipleSMTP/mailtask.php";
class MultipleSMTPEmailTask extends VTTask
{
    public $executeImmediately = false;
    public function getFieldNames()
    {
        return array("subject", "content", "recepient", "emailcc", "emailbcc", "fromEmail");
    }
    public function doTask($entity)
    {
        global $adb;
        global $current_user;
        global $vtiger_current_version;
        $util = new VTWorkflowUtils();
        $admin = $util->adminUser();
        $module = $entity->getModuleName();
        $taskContents = Zend_Json::decode($this->getContents($entity));
        $fromUserId = $taskContents["fromUserId"];
        if (empty($fromUserId)) {
            $fromUserId = $current_user->id;
        }
        $rsMailServer = $adb->pquery("SELECT * FROM vte_multiple_smtp WHERE userid=? ORDER BY `sequence` ASC LIMIT 1", array($fromUserId));
        if (0 < $adb->num_rows($rsMailServer)) {
            $mailServerId = $adb->query_result($rsMailServer, 0, "id");
            $fromemail = $adb->query_result($rsMailServer, 0, "from_email_field");
        }
        $from_email = $fromemail;
        $from_name = $taskContents["fromName"];
        $to_email = $taskContents["toEmail"];
        $cc = $taskContents["ccEmail"];
        $bcc = $taskContents["bccEmail"];
        $subject = $taskContents["subject"];
        $content = $taskContents["content"];
        if (!empty($to_email)) {
            $entityIdDetails = vtws_getIdComponents($entity->getId());
            $entityId = $entityIdDetails[1];
            $moduleName = "Emails";
            $userId = $current_user->id;
            $emailFocus = CRMEntity::getInstance($moduleName);
            $emailFieldValues = array("assigned_user_id" => $userId, "subject" => $subject, "description" => $content, "from_email" => $from_email, "saved_toid" => $to_email, "ccmail" => $cc, "bccmail" => $bcc, "parent_id" => $entityId . "@" . $userId . "|", "email_flag" => "SENT", "activitytype" => $moduleName, "date_start" => date("Y-m-d"), "time_start" => date("H:i:s"), "mode" => "", "id" => "");
            if (version_compare($vtiger_current_version, "7.0.0", "<")) {
                $emailFocus->column_fields = $emailFieldValues;
                $emailFocus->save($moduleName);
                $emailId = $emailFocus->id;
            } else {
                if (!empty($recordId)) {
                    $emailFocus = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                    $emailFocus->set("mode", "edit");
                } else {
                    $emailFocus = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $emailFocus->set("mode", "");
                }
                $emailFocus->column_fields = $emailFieldValues;
                $emailFocus->set("assigned_user_id", $userId);
                $emailFocus->set("subject", $subject);
                $emailFocus->set("description", $content);
                $emailFocus->set("from_email", $from_email);
                $emailFocus->set("saved_toid", $to_email);
                $emailFocus->set("ccmail", $cc);
                $emailFocus->set("bccmail", $bcc);
                $emailFocus->set("parent_id", $entityId . "@" . $userId . "|");
                $emailFocus->set("email_flag", "SENT");
                $emailFocus->set("activitytype", $moduleName);
                $emailFocus->set("date_start", date("Y-m-d"));
                $emailFocus->set("time_start", date("H:i:s"));
                $emailFocus->set("mode", "");
                $emailFocus->set("id", "");
                $emailFocus->save();
                $emailId = $emailFocus->getId();
                $emailFocus->id = $emailId;
            }
            global $site_URL;
            global $application_unique_key;
            $emailId = $emailFocus->id;
            $trackURL = (string) $site_URL . "/modules/Emails/actions/TrackAccess.php?record=" . $emailId . "&parentId=" . $entityId . "&applicationKey=" . $application_unique_key;
            $content = "<img src='" . $trackURL . "' alt='' width='1' height='1'>" . $content;
            if (stripos($content, "<img src=\"cid:logo\" />")) {
                $logo = 1;
            }
            $status = multiplesmtp_sendmail($module, $to_email, $from_name, $from_email, $subject, $content, $cc, $bcc, "", "", $logo, true, $mailServerId);
            if (!empty($emailId)) {
                $successIds = array();
                $result = $adb->pquery("SELECT idlists FROM vtiger_emaildetails WHERE emailid=?", array($mailid));
                $idlists = $adb->query_result($result, 0, "idlists");
                $idlistsArray = explode("|", $idlists);
                for ($i = 0; $i < count($idlistsArray) - 1; $i++) {
                    $crmid = explode("@", $idlistsArray[$i]);
                    array_push($successIds, $crmid[0]);
                }
                $successIds = array_unique($successIds);
                sort($successIds);
                for ($i = 0; $i < count($successIds); $i++) {
                    $adb->pquery("INSERT INTO vtiger_email_track(crmid, mailid,  access_count) VALUES(?,?,?)", array($successIds[$i], $mailid, 0));
                }
            }
            if (!$status) {
            }
        }
        $util->revertUser();
    }
    /**
     * Function to get contents of this task
     * @param <Object> $entity
     * @return <Array> contents
     */
    public function getContents($entity, $entityCache = false)
    {
        if (!$this->contents) {
            global $adb;
            global $current_user;
            $taskContents = array();
            $entityId = $entity->getId();
            $utils = new VTWorkflowUtils();
            $adminUser = $utils->adminUser();
            if (!$entityCache) {
                $entityCache = new VTEntityCache($adminUser);
            }
            $fromUserId = Users::getActiveAdminId();
            $entityOwnerId = $entity->get("assigned_user_id");
            if ($entityOwnerId) {
                list($moduleId, $fromUserId) = explode("x", $entityOwnerId);
            }
            $ownerEntity = $entityCache->forId($entityOwnerId);
            if ($ownerEntity->getModuleName() === "Groups") {
                list($moduleId, $recordId) = vtws_getIdComponents($entityId);
                $fromUserId = Vtiger_Util_Helper::getCreator($recordId);
            }
            $taskContents["fromUserId"] = $fromUserId;
            if ($this->fromEmail && !($ownerEntity->getModuleName() === "Groups" && strpos($this->fromEmail, "assigned_user_id : (Users) ") !== false)) {
                $et = new VTEmailRecipientsTemplate($this->fromEmail);
                $fromEmailDetails = $et->render($entityCache, $entityId);
                if (strpos($this->fromEmail, "&lt;") && strpos($this->fromEmail, "&gt;")) {
                    list($fromName, $fromEmail) = explode("&lt;", $fromEmailDetails);
                    list($fromEmail, $rest) = explode("&gt;", $fromEmail);
                } else {
                    $fromEmail = $fromEmailDetails;
                }
            } else {
                $userObj = CRMEntity::getInstance("Users");
                $userObj->retrieveCurrentUserInfoFromFile($fromUserId);
                if ($userObj) {
                    $fromEmail = $userObj->email1;
                    $fromName = $userObj->user_name;
                } else {
                    $result = $adb->pquery("SELECT user_name, email1 FROM vtiger_users WHERE id = ?", array($fromUserId));
                    $fromEmail = $adb->query_result($result, 0, "email1");
                    $fromName = $adb->query_result($result, 0, "user_name");
                }
            }
            if (!$fromEmail) {
                $utils->revertUser();
                return false;
            }
            $taskContents["fromEmail"] = $fromEmail;
            $taskContents["fromName"] = $fromName;
            if ($entity->getModuleName() === "Events") {
                $contactId = $entity->get("contact_id");
                if ($contactId) {
                    $contactIds = "";
                    list($wsId, $recordId) = explode("x", $entityId);
                    $webserviceObject = VtigerWebserviceObject::fromName($adb, "Contacts");
                    $result = $adb->pquery("SELECT contactid FROM vtiger_cntactivityrel WHERE activityid = ?", array($recordId));
                    $numOfRows = $adb->num_rows($result);
                    for ($i = 0; $i < $numOfRows; $i++) {
                        $contactIds .= vtws_getId($webserviceObject->getEntityId(), $adb->query_result($result, $i, "contactid")) . ",";
                    }
                }
                $entity->set("contact_id", trim($contactIds, ","));
                $entityCache->cache[$entityId] = $entity;
            }
            $et = new VTEmailRecipientsTemplate($this->recepient);
            $toEmail = $et->render($entityCache, $entityId);
            $ecct = new VTEmailRecipientsTemplate($this->emailcc);
            $ccEmail = $ecct->render($entityCache, $entityId);
            $ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
            $bccEmail = $ebcct->render($entityCache, $entityId);
            if (strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
                $utils->revertUser();
                return false;
            }
            $taskContents["toEmail"] = $toEmail;
            $taskContents["ccEmail"] = $ccEmail;
            $taskContents["bccEmail"] = $bccEmail;
            $st = new VTSimpleTemplate($this->subject);
            $taskContents["subject"] = $st->render($entityCache, $entityId);
            $ct = new VTSimpleTemplate($this->content);
            $taskContents["content"] = $ct->render($entityCache, $entityId);
            $this->contents = $taskContents;
            $utils->revertUser();
        }
        if (is_array($this->contents)) {
            $this->contents = Zend_Json::encode($this->contents);
        }
        return $this->contents;
    }
}

?>