<?php

require_once "modules/Emails/class.phpmailer.php";
require_once "include/utils/CommonUtils.php";
require_once "include/utils/VTCacheUtils.php";
/**   Function used to send email
 *   $module 		-- current module
 *   $to_email 	-- to email address
 *   $from_name	-- currently loggedin user name
 *   $from_email	-- currently loggedin vtiger_users's email id. you can give as '' if you are not in HelpDesk module
 *   $subject		-- subject of the email you want to send
 *   $contents		-- body of the email you want to send
 *   $cc		-- add email ids with comma seperated. - optional
 *   $bcc		-- add email ids with comma seperated. - optional.
 *   $attachment	-- whether we want to attach the currently selected file or all vtiger_files.[values = current,all] - optional
 *   $emailid		-- id of the email object which will be used to get the vtiger_attachments
 */
function multiplesmtp_sendmail($module, $to_email, $from_name, $from_email, $subject, $contents, $cc = "", $bcc = "", $attachment = "", $emailid = "", $logo = "", $useGivenFromEmailAddress = false, $serverId)
{
    global $adb;
    global $log;
    global $root_directory;
    global $HELPDESK_SUPPORT_EMAIL_ID;
    global $HELPDESK_SUPPORT_NAME;
    $uploaddir = $root_directory . "/test/upload/";
    $adb->println("To id => '" . $to_email . "'\nSubject ==>'" . $subject . "'\nContents ==> '" . $contents . "'");
    if ($from_email == "") {
        $from_email = getMultipleSMTPUserEmailId("user_name", $from_name);
    }
    $cachedFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
    if ($cachedFromEmail === NULL) {
        $query = "select from_email_field from vte_multiple_smtp where id=?";
        $params = array($serverId);
        $result = $adb->pquery($query, $params);
        $from_email_field = $adb->query_result($result, 0, "from_email_field");
        VTCacheUtils::setOutgoingMailFromEmailAddress($from_email_field);
    }
    if (isMultipleSMTPUserInitiated()) {
        $replyToEmail = $from_email;
    } else {
        $replyToEmail = $from_email_field;
    }
    if (isset($from_email_field) && $from_email_field != "" && !$useGivenFromEmailAddress) {
        $from_email = $from_email_field;
    }
    if ($module != "Calendar") {
        $contents = addMultipleSMTPSignature($contents, $from_name);
    }
    $mail = new PHPMailer();
    setMultipleSMTPMailerProperties($mail, $subject, $contents, $from_email, $from_name, trim($to_email, ","), $attachment, $emailid, $module, $logo, $serverId);
    setMultipleSMTPCCAddress($mail, "cc", $cc);
    setMultipleSMTPCCAddress($mail, "bcc", $bcc);
    if (!empty($replyToEmail)) {
        $mail->AddReplyTo($replyToEmail);
    }
    global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
    if ($HELPDESK_SUPPORT_EMAIL_REPLY_ID && $HELPDESK_SUPPORT_EMAIL_ID != $HELPDESK_SUPPORT_EMAIL_REPLY_ID) {
        $mail->AddReplyTo($HELPDESK_SUPPORT_EMAIL_REPLY_ID);
    }
    if (empty($mail->Host)) {
        return 0;
    }
    $mail_status = MultipleSMTPMailSend($mail);
    if ($mail_status != 1) {
        $mail_error = getMultipleSMTPMailError($mail, $mail_status, $mailto);
    } else {
        $mail_error = $mail_status;
    }
    return $mail_error;
}
/**	Function to get the user Email id based on column name and column value
 *	$name -- column name of the vtiger_users vtiger_table
 *	$val  -- column value
 */
function getMultipleSMTPUserEmailId($name, $val)
{
    global $adb;
    $adb->println("Inside the function getMultipleSMTPUserEmailId. --- " . $name . " = '" . $val . "'");
    if ($val != "") {
        $sql = "SELECT email1, email2, secondaryemail  from vtiger_users WHERE status='Active' AND " . $adb->sql_escape_string($name) . " = ?";
        $res = $adb->pquery($sql, array($val));
        $email = $adb->query_result($res, 0, "email1");
        if ($email == "") {
            $email = $adb->query_result($res, 0, "email2");
        }
        if ($email == "") {
            $email = $adb->query_result($res, 0, "secondaryemail ");
        }
        $adb->println("Email id is selected  => '" . $email . "'");
        return $email;
    }
    $adb->println("User id is empty. so return value is ''");
    return "";
}
/**	Funtion to add the user's signature with the content passed
 *	$contents -- where we want to add the signature
 *	$fromname -- which user's signature will be added to the contents
 */
function addMultipleSMTPSignature($contents, $fromname)
{
    global $adb;
    $adb->println("Inside the function addMultipleSMTPSignature");
    $sign = VTCacheUtils::getUserSignature($fromname);
    if ($sign === NULL) {
        $result = $adb->pquery("select signature, first_name, last_name from vtiger_users where user_name=?", array($fromname));
        $sign = $adb->query_result($result, 0, "signature");
        VTCacheUtils::setUserSignature($fromname, $sign);
        VTCacheUtils::setUserFullName($fromname, $adb->query_result($result, 0, "first_name") . " " . $adb->query_result($result, 0, "last_name"));
    }
    $sign = nl2br($sign);
    if ($sign != "") {
        $contents .= "<br><br>" . $sign;
        $adb->println("Signature is added with the body => '." . $sign . "'");
    } else {
        $adb->println("Signature is empty for the user => '" . $fromname . "'");
    }
    return $contents;
}
/**	Function to set all the Mailer properties
  *	$mail 		-- reference of the mail object
  *	$subject	-- subject of the email you want to send
  *	$contents	-- body of the email you want to send
  *	$from_email	-- from email id which will be displayed in the mail
  *	$from_name	-- from name which will be displayed in the mail
  *	$to_email 	-- to email address  -- This can be an email in a single string, a comma separated
  *			   list of emails or an array of email addresses
  *	$attachment	-- whether we want to attach the currently selected file or all vtiger_files.
  				[values = current,all] - optional
  *	$emailid	-- id of the email object which will be used to get the vtiger_attachments - optional
  */
function setMultipleSMTPMailerProperties($mail, $subject, $contents, $from_email, $from_name, $to_email, $attachment = "", $emailid = "", $module = "", $logo = "", $serverId)
{
    global $adb;
    $adb->println("Inside the function setMultipleSMTPMailerProperties");
    if ($module == "Support" || $logo == 1) {
        $mail->AddEmbeddedImage("layouts/vlayout/skins/images/logo_mail.jpg", "logo", "logo.jpg", "base64", "image/jpg");
    }
    $mail->Subject = $subject;
    $mail->Body = decode_html($contents);
    $mail->AltBody = strip_tags(preg_replace(array("/<p>/i", "/<br>/i", "/<br \\/>/i"), array("\n", "\n", "\n"), $contents));
    $mail->IsSMTP();
    setMultipleSMTPMailServerProperties($mail, $serverId);
    $mail->From = $from_email;
    $userFullName = trim(VTCacheUtils::getUserFullName($from_name));
    if (empty($userFullName)) {
        $rs = $adb->pquery("select first_name,last_name from vtiger_users where user_name=?", array($from_name));
        $num_rows = $adb->num_rows($rs);
        if (0 < $num_rows) {
            $fullName = getFullNameFromQResult($rs, 0, "Users");
            VTCacheUtils::setUserFullName($from_name, $fullName);
        }
    } else {
        $from_name = $userFullName;
    }
    $mail->FromName = decode_html($from_name);
    if ($to_email != "") {
        if (is_array($to_email)) {
            $j = 0;
            for ($num = count($to_email); $j < $num; $j++) {
                $mail->addAddress($to_email[$j]);
            }
        } else {
            $_tmp = explode(",", $to_email);
            $j = 0;
            for ($num = count($_tmp); $j < $num; $j++) {
                $mail->addAddress($_tmp[$j]);
            }
        }
    }
    $mail->WordWrap = 50;
    if ($attachment == "current" && $emailid != "") {
        if (isset($_REQUEST["filename_hidden"])) {
            $file_name = $_REQUEST["filename_hidden"];
        } else {
            $file_name = $_FILES["filename"]["name"];
        }
        addAttachment($mail, $file_name, $emailid);
    }
    if ($attachment == "all" && $emailid != "") {
        addMultipleSMTPAllAttachments($mail, $emailid);
    }
    $mail->IsHTML(true);
}
/**	Function to set the Mail Server Properties in the object passed
 *	$mail -- reference of the mailobject
 */
function setMultipleSMTPMailServerProperties($mail, $serverId)
{
    global $adb;
    $adb->println("Inside the function setMultipleSMTPMailServerProperties");
    $res = $adb->pquery("select * from vte_multiple_smtp where id=?", array($serverId));
    if ($adb->num_rows($res) == 0) {
        $res = $adb->pquery("select * from vtiger_systems where server_type=?", array("email"));
    }
    if (isset($_REQUEST["server"])) {
        $server = $_REQUEST["server"];
    } else {
        $server = $adb->query_result($res, 0, "server");
    }
    if (isset($_REQUEST["server_username"])) {
        $username = $_REQUEST["server_username"];
    } else {
        $username = $adb->query_result($res, 0, "server_username");
    }
    if (isset($_REQUEST["server_password"])) {
        $password = $_REQUEST["server_password"];
    } else {
        $password = $adb->query_result($res, 0, "server_password");
    }
    $smtp_auth = false;
    if (isset($_REQUEST["smtp_auth"])) {
        $smtp_auth = $_REQUEST["smtp_auth"];
        if ($smtp_auth == "on") {
            $smtp_auth = true;
        }
    } else {
        if (isset($_REQUEST["module"]) && $_REQUEST["module"] == "Settings" && !isset($_REQUEST["smtp_auth"])) {
            $smtp_auth = false;
        } else {
            $smtp_auth = $adb->query_result($res, 0, "smtp_auth");
            if ($smtp_auth == "1" || $smtp_auth == "true") {
                $smtp_auth = true;
            }
        }
    }
    $adb->println("Mail server name,username & password => '" . $server . "','" . $username . "','" . $password . "'");
    if ($smtp_auth) {
        $mail->SMTPAuth = true;
    }
    $mail->Host = $server;
    $mail->Username = $username;
    $mail->Password = $password;
    $serverinfo = explode("://", $server);
    $smtpsecure = $serverinfo[0];
    if ($smtpsecure == "tls") {
        $mail->SMTPSecure = $smtpsecure;
        $mail->Host = $serverinfo[1];
    }
}
/**	Function to add the file as attachment with the mail object
 *	$mail -- reference of the mail object
 *	$filename -- filename which is going to added with the mail
 *	$record -- id of the record - optional
 */
function addMultipleSMTPAttachment($mail, $filename, $record)
{
    global $adb;
    global $root_directory;
    $adb->println("Inside the function addMultipleSMTPAttachment");
    $adb->println("The file name is => '" . $filename . "'");
    if (is_file($filename) && $filename != "") {
        $mail->addMultipleSMTPAttachment($root_directory . "test/upload/" . $filename);
    }
}
/**     Function to add all the vtiger_files as attachment with the mail object
 *     $mail -- reference of the mail object
 *     $record -- email id ie., record id which is used to get the all vtiger_attachments from database
 */
function addMultipleSMTPAllAttachments($mail, $record)
{
    global $adb;
    global $log;
    global $root_directory;
    $adb->println("Inside the function addMultipleSMTPAllAttachments");
    $sql = "select vtiger_attachments.* from vtiger_attachments inner join vtiger_seattachmentsrel on vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_attachments.attachmentsid where vtiger_crmentity.deleted=0 and vtiger_seattachmentsrel.crmid=?";
    $res = $adb->pquery($sql, array($record));
    $count = $adb->num_rows($res);
    for ($i = 0; $i < $count; $i++) {
        $fileid = $adb->query_result($res, $i, "attachmentsid");
        $filename = decode_html($adb->query_result($res, $i, "name"));
        $filepath = $adb->query_result($res, $i, "path");
        $filewithpath = $root_directory . $filepath . $fileid . "_" . $filename;
        if (is_file($filewithpath)) {
            $mail->AddAttachment($filewithpath, $filename);
        }
    }
}
/**	Function to set the CC or BCC addresses in the mail
 *	$mail -- reference of the mail object
 *	$cc_mod -- mode to set the address ie., cc or bcc
 *	$cc_val -- addresss with comma seperated to set as CC or BCC in the mail
 */
function setMultipleSMTPCCAddress($mail, $cc_mod, $cc_val)
{
    global $adb;
    $adb->println("Inside the functin setMultipleSMTPCCAddress");
    if ($cc_mod == "cc") {
        $method = "AddCC";
    }
    if ($cc_mod == "bcc") {
        $method = "AddBCC";
    }
    if ($cc_val != "") {
        $ccmail = explode(",", trim($cc_val, ","));
        for ($i = 0; $i < count($ccmail); $i++) {
            $addr = $ccmail[$i];
            $cc_name = preg_replace("/([^@]+)@(.*)/", "\$1", $addr);
            if (stripos($addr, "<")) {
                $name_addr_pair = explode("<", $ccmail[$i]);
                $cc_name = $name_addr_pair[0];
                $addr = trim($name_addr_pair[1], ">");
            }
            if ($ccmail[$i] != "") {
                $mail->{$method}($addr, $cc_name);
            }
        }
    }
}
/**	Function to send the mail which will be called after set all the mail object values
 *	$mail -- reference of the mail object
 */
function MultipleSMTPMailSend($mail)
{
    global $log;
    $log->info("Inside of Send Mail function.");
    if (!$mail->Send()) {
        $log->debug("Error in Mail Sending : Error log = '" . $mail->ErrorInfo . "'");
        return $mail->ErrorInfo;
    }
    $log->info("Mail has been sent from the vtigerCRM system : Status : '" . $mail->ErrorInfo . "'");
    return 1;
}
/**	Function to get the Parent email id from HelpDesk to send the details about the ticket via email
 *	$returnmodule -- Parent module value. Contact or Account for send email about the ticket details
 *	$parentid -- id of the parent ie., contact or vtiger_account
 */
function getMultipleSMTPParentMailId($parentmodule, $parentid)
{
    global $adb;
    $adb->println("Inside the function getMultipleSMTPParentMailId. \n parent module and id => " . $parentmodule . "&" . $parentid);
    if ($parentmodule == "Contacts") {
        $tablename = "vtiger_contactdetails";
        $idname = "contactid";
        $first_email = "email";
        $second_email = "secondaryemail";
    }
    if ($parentmodule == "Accounts") {
        $tablename = "vtiger_account";
        $idname = "accountid";
        $first_email = "email1";
        $second_email = "email2";
    }
    if ($parentid != "") {
        $query = "select * from " . $tablename . " where " . $idname . " = ?";
        $res = $adb->pquery($query, array($parentid));
        $mailid = $adb->query_result($res, 0, $first_email);
        $mailid2 = $adb->query_result($res, 0, $second_email);
    }
    if ($mailid == "" && $mailid2 != "") {
        $mailid = $mailid2;
    }
    return $mailid;
}
/**	Function to parse and get the mail error
 *	$mail -- reference of the mail object
 *	$mail_status -- status of the mail which is sent or not
 *	$to -- the email address to whom we sent the mail and failes
 *	return -- Mail error occured during the mail sending process
 */
function getMultipleSMTPMailError($mail, $mail_status, $to)
{
    global $adb;
    $adb->println("Inside the function getMultipleSMTPMailError");
    $msg = array_search($mail_status, $mail->language);
    $adb->println("Error message ==> " . $msg);
    if ($msg == "connect_host") {
        $error_msg = $msg;
    } else {
        if (strstr($msg, "from_failed")) {
            $error_msg = $msg;
        } else {
            if (strstr($msg, "recipients_failed")) {
                $error_msg = $msg;
            } else {
                $adb->println("Mail error is not as connect_host or from_failed or recipients_failed");
            }
        }
    }
    $adb->println("return error => " . $error_msg);
    return $error_msg;
}
/**	Function to get the mail status string (string of sent mail status)
 *	$mail_status_str -- concatenated string with all the error messages with &&& seperation
 *	return - the error status as a encoded string
 */
function getMultipleSMTPMailErrorString($mail_status_str)
{
    global $adb;
    $adb->println("Inside getMultipleSMTPMailErrorString function.\nMail status string ==> " . $mail_status_str);
    $mail_status_str = trim($mail_status_str, "&&&");
    $mail_status_array = explode("&&&", $mail_status_str);
    $adb->println("All Mail status ==>\n" . $mail_status_str . "\n");
    foreach ($mail_status_array as $key => $val) {
        $list = explode("=", $val);
        $adb->println("Mail id & status ==> " . $list[0] . " = " . $list[1]);
        if ($list[1] == 0) {
            $mail_error_str .= $list[0] . "=" . $list[1] . "&&&";
        }
    }
    $adb->println("Mail error string => '" . $mail_error_str . "'");
    if ($mail_error_str != "") {
        $mail_error_str = "mail_error=" . base64_encode($mail_error_str);
    }
    return $mail_error_str;
}
/**	Function to parse the error string
 *	$mail_error_str -- base64 encoded string which contains the mail sending errors as concatenated with &&&
 *	return - Error message to display
 */
function parseMultipleSMTPEmailErrorString($mail_error_str)
{
    global $adb;
    global $mod_strings;
    $adb->println("Inside the parseMultipleSMTPEmailErrorString function.\n encoded mail error string ==> " . $mail_error_str);
    $mail_error = base64_decode($mail_error_str);
    $adb->println("Original error string => " . $mail_error);
    $mail_status = explode("&&&", trim($mail_error, "&&&"));
    foreach ($mail_status as $key => $val) {
        $status_str = explode("=", $val);
        $adb->println("Mail id => \"" . $status_str[0] . "\".........status => \"" . $status_str[1] . "\"");
        if ($status_str[1] != 1 && $status_str[1] != "") {
            $adb->println("Error in mail sending");
            if ($status_str[1] == "connect_host") {
                $adb->println("if part - Mail sever is not configured");
                $errorstr .= "<br><b><font color=red>" . $mod_strings["MESSAGE_CHECK_MAIL_SERVER_NAME"] . "</font></b>";
                break;
            }
            if ($status_str[1] == "0") {
                $adb->println("first elseif part - status will be 0 which is the case of assigned to vtiger_users's email is empty.");
                $errorstr .= "<br><b><font color=red> " . $mod_strings["MESSAGE_MAIL_COULD_NOT_BE_SEND"] . " " . $mod_strings["MESSAGE_PLEASE_CHECK_FROM_THE_MAILID"] . "</font></b>";
                if ($status_str[0] == "cc_success") {
                    $cc_msg = "But the mail has been sent to CC & BCC addresses.";
                    $errorstr .= "<br><b><font color=purple>" . $cc_msg . "</font></b>";
                }
            } else {
                if (strstr($status_str[1], "from_failed")) {
                    $adb->println("second elseif part - from email id is failed.");
                    $from = explode("from_failed", $status_str[1]);
                    $errorstr .= "<br><b><font color=red>" . $mod_strings["MESSAGE_PLEASE_CHECK_THE_FROM_MAILID"] . " '" . $from[1] . "'</font></b>";
                } else {
                    $adb->println("else part - mail send process failed due to the following reason.");
                    $errorstr .= "<br><b><font color=red> " . $mod_strings["MESSAGE_MAIL_COULD_NOT_BE_SEND_TO_THIS_EMAILID"] . " '" . $status_str[0] . "'. " . $mod_strings["PLEASE_CHECK_THIS_EMAILID"] . "</font></b>";
                }
            }
        }
    }
    $adb->println("Return Error string => " . $errorstr);
    return $errorstr;
}
function isMultipleSMTPUserInitiated()
{
    return ($_REQUEST["module"] == "Emails" || $_REQUEST["module"] == "Webmails") && ($_REQUEST["action"] == "mailsend" || $_REQUEST["action"] == "webmailsend" || $_REQUEST["action"] == "Save");
}
/**
 * Function to get the group users Email ids
 */
function isMultipleSMTPDefaultAssigneeEmailIds($groupId)
{
    global $adb;
    $emails = array();
    if ($groupId != "") {
        require_once "include/utils/GetGroupUsers.php";
        $userGroups = new GetGroupUsers();
        $userGroups->getAllUsersInGroup($groupId);
        if (count($userGroups->group_users) == 0) {
            return array();
        }
        $result = $adb->pquery("SELECT email1,email2,secondaryemail FROM vtiger_users WHERE vtiger_users.id IN\n\t\t\t\t\t\t\t\t\t\t\t(" . generateQuestionMarks($userGroups->group_users) . ") AND vtiger_users.status= ?", array($userGroups->group_users, "Active"));
        $rows = $adb->num_rows($result);
        for ($i = 0; $i < $rows; $i++) {
            $email = $adb->query_result($result, $i, "email1");
            if ($email == "") {
                $email = $adb->query_result($result, $i, "email2");
                if ($email == "") {
                    $email = $adb->query_result($result, $i, "secondaryemail");
                } else {
                    $email = "";
                }
            }
            array_push($emails, $email);
        }
        $adb->println("Email ids are selected  => '" . $emails . "'");
        return $emails;
    }
    $adb->println("User id is empty. so return value is ''");
    return array();
}

?>