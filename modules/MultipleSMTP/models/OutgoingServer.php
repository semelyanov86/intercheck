<?php

class MultipleSMTP_OutgoingServer_Model extends Vtiger_Base_Model
{
    const tableName = "vte_multiple_smtp";
    public function getId()
    {
        return $this->get("id");
    }
    public function isSmtpAuthEnabled()
    {
        $smtp_auth_value = $this->get("smtp_auth");
        return $smtp_auth_value == "on" || $smtp_auth_value == 1 || $smtp_auth_value == "true" ? true : false;
    }
    public function isSendFolder()
    {
        $send_folder_value = $this->get("send_folder");
        return $send_folder_value == "on" || $send_folder_value == 1 || $send_folder_value == "true" ? true : false;
    }
    public function getSubject()
    {
        return "Test mail about the mail server configuration.";
    }
    public function getBody()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        return "Dear " . $currentUser->get("user_name") . ", <br><br><b> This is a test mail sent to confirm if a mail is \r\n                actually being sent through the smtp server that you have configured. </b><br>Feel free to delete this mail.\r\n                <br><br>Thanks  and  Regards,<br> Team vTiger <br><br>";
    }
    public function save($request)
    {
        vimport("~~/modules/MultipleSMTP/mail.php");
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $from_email = $request->get("from_email_field");
        $to_email = getUserEmailId("id", $currentUser->getId());
        $subject = $this->getSubject();
        $description = $this->getBody();
        $olderAction = $_REQUEST["action"];
        $_REQUEST["action"] = "Save";
        if ($to_email != "") {
            $mail_status = send_mail("Users", $to_email, $currentUser->get("user_name"), $from_email, $subject, $description, "", "", "", "", "", true, $this->getId());
        }
        $_REQUEST["action"] = $olderAction;
        if ($mail_status != 1) {
            throw new Exception("Error occurred while sending mail");
        }
        $db = PearDatabase::getInstance();
        $id = $this->getId();
        if (!$id) {
            vimport("~~/modules/MultipleSMTP/models/Module.php");
            $smtpModuleModel = new MultipleSMTP_Module_Model();
            $id = $smtpModuleModel->addUserServer($currentUser->getId());
            $this->set("id", $id);
        }
        $params = array();
        array_push($params, $this->get("server"), $this->get("server_port"), $this->get("server_username"), $this->get("server_password"), $this->isSmtpAuthEnabled(), $this->get("from_email_field"), $this->get("userid"), $this->get("replyto_email_field"), $this->get("sequence"), $this->isSendFolder(), $this->get("name"));
        if (empty($id)) {
            $query = "INSERT INTO " . self::tableName . "(server,server_port,server_username,server_password,smtp_auth,from_email_field, userid,replyto_email_field,sequence,send_folder,`name`) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        } else {
            $query = "UPDATE " . self::tableName . " SET server = ?, server_port= ?, server_username = ?, server_password = ?,\r\n                smtp_auth= ?, from_email_field=?, userid=?, replyto_email_field=?, sequence=?,send_folder=?,`name`=? WHERE id = ?";
            $params[] = $id;
        }
        $db->pquery($query, $params);
        return $id;
    }
    public static function getInstanceFromId($id)
    {
        $db = PearDatabase::getInstance();
        $query = "SELECT * FROM " . self::tableName . " WHERE id=?";
        $params = array($id);
        $result = $db->pquery($query, $params);
        try {
            $modelClassName = Vtiger_Loader::getComponentClassName("Model", "OutgoingServer", "MultipleSMTP");
        } catch (Exception $e) {
            $modelClassName = self;
        }
        $instance = new $modelClassName();
        if (0 < $db->num_rows($result)) {
            $rowData = $db->query_result_rowdata($result, 0);
            $instance->setData($rowData);
        }
        return $instance;
    }
}

?>