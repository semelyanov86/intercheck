<?php

class MultipleSMTP_Record_Model extends Emails_Record_Model
{
    /**
     * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name
     * @param <Number> $recordId
     * @param <String> $moduleName
     * @return Vtiger_Record_Model or Module Specific Record Model instance
     */
    public static function getInstanceById($recordId, $module = NULL)
    {
        if (is_object($module) && is_a($module, "Vtiger_Module_Model")) {
            $moduleName = $module->get("name");
        } else {
            if (is_string($module)) {
                $module = Vtiger_Module_Model::getInstance($module);
                $moduleName = $module->get("name");
            } else {
                if (empty($module)) {
                    $moduleName = getSalesEntityType($recordId);
                    $module = Vtiger_Module_Model::getInstance($moduleName);
                }
            }
        }
        $focus = CRMEntity::getInstance("Emails");
        $focus->id = $recordId;
        $focus->retrieve_entity_info($recordId, $moduleName);
        $modelClassName = Vtiger_Loader::getComponentClassName("Model", "Record", "MultipleSMTP");
        $instance = new $modelClassName();
        return $instance->setData($focus->column_fields)->set("id", $recordId)->setModuleFromInstance($module)->setEntity($focus);
    }
    /**
     * Static Function to get the instance of a clean Vtiger Record Model for the given module name
     * @param <String> $moduleName
     * @return Vtiger_Record_Model or Module Specific Record Model instance
     */
    public static function getCleanInstance($moduleName)
    {
        $focus = CRMEntity::getInstance("Emails");
        $modelClassName = Vtiger_Loader::getComponentClassName("Model", "Record", "MultipleSMTP");
        $instance = new $modelClassName();
        return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
    }
    /**
     * Function sends mail
     */
    public function send()
    {
        global $vtiger_current_version;
        global $adb;
        global $site_URL;
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $rootDirectory = vglobal("root_directory");
        $mailer = MultipleSMTP_Mailer_Model::getInstance();
        $mailer->IsHTML(true);
        $fromEmail = $this->getMultipleSMTMFromEmailAddress($this->get("from_serveremailid"));
        if (empty($fromEmail)) {
            $fromEmail = $this->getFromEmailAddress();
        }
        $replyTo = $this->getMultipleSMTMReplyToEmailAddress($this->get("from_serveremailid"));
        if (empty($replyTo)) {
            $replyTo = $currentUserModel->get("email1");
        }
        $userName = $this->getMultipleSMTMName($this->get("from_serveremailid"));
        if (empty($userName)) {
            $userName = $currentUserModel->getName();
        }
        $toEmailInfo = array_filter($this->get("toemailinfo"));
        $toMailNamesList = array_filter($this->get("toMailNamesList"));
        foreach ($toMailNamesList as $id => $emailData) {
            foreach ($emailData as $key => $email) {
                if ($toEmailInfo[$id] && !in_array($email["value"], $toEmailInfo[$id])) {
                    array_push($toEmailInfo[$id], $email["value"]);
                }
            }
        }
        $emailsInfo = array();
        foreach ($toEmailInfo as $id => $emails) {
            foreach ($emails as $key => $value) {
                array_push($emailsInfo, $value);
            }
        }
        $toFieldData = array_diff(explode(",", $this->get("saved_toid")), $emailsInfo);
        $toEmailsData = array();
        $i = 1;
        foreach ($toFieldData as $value) {
            $toEmailInfo["to" . $i++] = array($value);
        }
        $attachments = $this->getAttachmentDetails();
        $status = false;
        $mergedDescription = getMergedDescription($this->get("description"), $currentUserModel->getId(), "Users");
        $mergedSubject = getMergedDescription($this->get("subject"), $currentUserModel->getId(), "Users");
        foreach ($toEmailInfo as $id => $emails) {
            $mailer->reinitialize();
            $mailer->ConfigSenderInfo($fromEmail, $userName, $replyTo);
            $old_mod_strings = vglobal("mod_strings");
            $description = $this->get("description");
            $subject = $this->get("subject");
            $parentModule = $this->getEntityType($id);
            if ($parentModule) {
                $currentLanguage = Vtiger_Language_Handler::getLanguage();
                $moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage, $parentModule);
                vglobal("mod_strings", $moduleLanguageStrings["languageStrings"]);
                if ($parentModule != "Users") {
                    $description = getMergedDescription($mergedDescription, $id, $parentModule);
                    $subject = getMergedDescription($mergedSubject, $id, $parentModule);
                } else {
                    $description = getMergedDescription($description, $id, "Users");
                    $subject = getMergedDescription($mergedSubject, $id, "Users");
                    vglobal("mod_strings", $old_mod_strings);
                }
            }
            if (strpos($description, "\$logo\$")) {
                $description = str_replace("\$logo\$", "<img src='cid:logo' />", $description);
                $logo = true;
            }
            foreach ($emails as $email) {
                $mailer->Body = $description;
                if ($parentModule) {
                    if (version_compare($vtiger_current_version, "7.0.0", ">=")) {
                        $mailer->Body = $this->convertUrlsToTrackUrls($mailer->Body, $id);
                    }
                    $mailer->Body .= $this->getTrackImageDetails($id, $this->isEmailTrackEnabled());
                }
                $mailer->Signature = str_replace(array("\\r\\n", "\\n"), "<br>", $currentUserModel->get("signature"));
                if ($mailer->Signature != "") {
                    $mailer->Body .= "<br><br>" . decode_html($mailer->Signature);
                }
                $mailer->Subject = $subject;
                $mailer->AddAddress($email);
                if (is_array($attachments)) {
                    foreach ($attachments as $attachment) {
                        $fileNameWithPath = $rootDirectory . $attachment["path"] . $attachment["fileid"] . "_" . $attachment["attachment"];
                        if (is_file($fileNameWithPath)) {
                            $mailer->AddAttachment($fileNameWithPath, $attachment["attachment"]);
                        }
                    }
                }
                if ($logo) {
                    $mailer->AddEmbeddedImage(dirname(__FILE__) . "/../../../layouts/vlayout/skins/images/logo_mail.jpg", "logo", "logo.jpg", "base64", "image/jpg");
                }
                $ccs = array_filter(explode(",", $this->get("ccmail")));
                $bccs = array_filter(explode(",", $this->get("bccmail")));
                if (!empty($ccs)) {
                    foreach ($ccs as $cc) {
                        $mailer->AddCC($cc);
                    }
                }
                if (!empty($bccs)) {
                    foreach ($bccs as $bcc) {
                        $mailer->AddBCC($bcc);
                    }
                }
            }
            $smtpFrom = trim($this->getMultipleSMTMFromEmailAddress($this->get("from_serveremailid")));
            if ($smtpFrom != "") {
                $fromEmail = $smtpFrom;
            }
            $smtpReplyTo = trim($this->getMultipleSMTMReplyToEmailAddress($this->get("from_serveremailid")));
            if ($smtpReplyTo != "") {
                $replyTo = $smtpReplyTo;
            }
            $mailer->From = $fromEmail;
            $mailer->Sender = $fromEmail;
            $mailer->FromName = $userName;
            $mailer->SendFolder = $this->getMultipleSMTMSendFolder($this->get("from_serveremailid"));
            $mailer->AddReplyTo($replyTo);
            $sql = "SELECT id,shortcode FROM `vte_formbuilder_forms` WHERE shortcode <> '' OR shortcode is NOT null;";
            $re = $adb->pquery($sql, array());
            if (0 < $adb->num_rows($re)) {
                $email_body = $mailer->Body;
                while ($row = $adb->fetchByAssoc($re)) {
                    if (strpos($email_body, $row["shortcode"]) !== false) {
                        $form_link = $site_URL . "modules/VTEFormBuilder/plugin/views/form.php?id=" . $row["id"] . "&parentid=" . $id;
                        $email_body = str_replace($row["shortcode"], "<a href=\"" . $form_link . "\">" . $form_link . "</a>", $email_body);
                    }
                }
                $mailer->Body = $email_body;
            }
            $status = $mailer->Send(true);
            if (!$status) {
                $status = $mailer->getError();
            } else {
                $mailString = $mailer->getMailString();
                $mailBoxModel = MailManager_Mailbox_Model::activeInstance();
                $folderName = $mailBoxModel->folder();
                if (!empty($folderName) && !empty($mailString)) {
                    $connector = MailManager_Connector_Connector::connectorWithModel($mailBoxModel, "");
                    imap_append($connector->mBox, $connector->mBoxUrl . $folderName, $mailString, "\\Seen");
                }
            }
        }
        return $status;
    }
    /**
     * Function to set Access count value by default as 0
     */
    public function setAccessCountValue()
    {
        $record = $this->getId();
        $focus = new Emails();
        $focus->setEmailAccessCountValue($record);
    }
    public static function getMultipleSMTMFromEmailAddress($fromemailid)
    {
        $db = PearDatabase::getInstance();
        $fromEmail = false;
        $query = "select from_email_field from vte_multiple_smtp where id=?";
        $params = array($fromemailid);
        $result = $db->pquery($query, $params);
        $fromEmail = $db->query_result($result, "from_email_field");
        return $fromEmail;
    }
    public function getMultipleSMTMReplyToEmailAddress($fromemailid)
    {
        $db = PearDatabase::getInstance();
        $replytoEmail = false;
        $query = "select replyto_email_field from vte_multiple_smtp where id=?";
        $params = array($fromemailid);
        $result = $db->pquery($query, $params);
        $replytoEmail = $db->query_result($result, "replyto_email_field");
        return $replytoEmail;
    }
    public function getMultipleSMTMSendFolder($fromemailid)
    {
        $db = PearDatabase::getInstance();
        $sendFolder = false;
        $query = "select send_folder from vte_multiple_smtp where id=?";
        $params = array($fromemailid);
        $result = $db->pquery($query, $params);
        $sendFolder = $db->query_result($result, "send_folder");
        return $sendFolder;
    }
    public function getMultipleSMTMName($fromemailid)
    {
        $db = PearDatabase::getInstance();
        $sendFolder = false;
        $query = "select `name` from vte_multiple_smtp where id=?";
        $params = array($fromemailid);
        $result = $db->pquery($query, $params);
        $sendFolder = $db->query_result($result, "name");
        return $sendFolder;
    }
}

?>