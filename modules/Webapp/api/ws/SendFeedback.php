<?php

require_once 'modules/Emails/class.phpmailer.php';
require_once 'modules/Emails/mail.php';   
class Webapp_WS_SendFeedback extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
		$user_id = trim($request->get('user_id'));
		$message= $request->get('message');
		if(!empty($user_id) && !empty($message)){
			$userModel = Vtiger_Record_Model::getInstanceById($user_id,'Users');
			$first_name = $userModel->get('first_name');
			$last_name = $userModel->get('last_name');
			$contents = 'From : '.$first_name.' '.$last_name.'<br/>';
			$contents.= $message;

			$to_email = $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
			$from_name = $first_name.' '.$last_name;
			$from_email = $userModel->get('email1');
			$subject = 'Feedback Message';

			$mail = $this->send_mail($to_email,$from_name,$from_email,$subject,$contents);
			$response = new Webapp_API_Response();
			if($mail == 1){
				$message = vtranslate('Mail Sent','Webapp');
				$response->setResult(array('code'=>'1','message'=>$message));
			}else{
				$message = vtranslate('Mail Not Sent','Webapp');
				$response->setResult(array('code'=>'0','message'=>$message));
			}				 
			return $response;
		}else{
			$message = vtranslate('Your feedback is valuable for us - it cannot be empty','Webapp');
			throw new WebServiceException(404,$message);
		}
	}


	function send_mail($to_email,$from_name,$from_email,$subject,$contents,$cc='',$bcc='',$attachment='',$emailid='',$logo='', $useGivenFromEmailAddress=false,$useSignature = 'Yes',$inReplyToMessageId=''){

		global $adb, $log;
		global $root_directory;
		global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;

		$uploaddir = $root_directory ."/test/upload/";

		$adb->println("To id => '".$to_email."'\nSubject ==>'".$subject."'\nContents ==> '".$contents."'");

		//Get the email id of assigned_to user -- pass the value and name, name must be "user_name" or "id"(field names of vtiger_users vtiger_table)
		

		//if the newly defined from email field is set, then use this email address as the from address
		//and use the username as the reply-to address
		$cachedFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
		if($cachedFromEmail === null) {
			$query = "select from_email_field from vtiger_systems where server_type=?";
			$params = array('email');
			$result = $adb->pquery($query,$params);
			$from_email_field = $adb->query_result($result,0,'from_email_field');
			VTCacheUtils::setOutgoingMailFromEmailAddress($from_email_field);
		}
		
		$mail = new PHPMailer();
		$mail->Subject = $subject;
		//Added back as we have changed php mailer library, older library was using html_entity_decode before sending mail
		$mail->Body = decode_html($contents);
		$plainBody = decode_html($contents);
		$plainBody = preg_replace(array("/<p>/i","/<br>/i","/<br \/>/i"),array("\n","\n","\n"),$plainBody);
		$plainBody = strip_tags($plainBody);
		$plainBody = Emails_Mailer_Model::convertToAscii($plainBody);
		$mail->AltBody = $plainBody;
		
		$mail->AddAttachment($attachment);
		
		$mail->IsSMTP();

		$adb->println("Inside the function setMailServerProperties");
			
		$res = $adb->pquery("select * from vtiger_systems where server_type=?", array('email'));
		$server = $adb->query_result($res,0,'server');
		$username = $adb->query_result($res,0,'server_username');
		$password = $adb->query_result($res,0,'server_password');
		$mail->SMTPAuth = true;
		$mail->Host = $server;		// specify main and backup server
		$mail->Username = $username ;	// SMTP username
		$mail->Password = $password ;	// SMTP password
		
		// To Support TLS
		$serverinfo = explode("://", $server);
		$smtpsecure = $serverinfo[0];
		if($smtpsecure == 'tls'){
			$mail->SMTPSecure = $smtpsecure;
			$mail->Host = $serverinfo[1];
		}
		$mail->From = $from_email;
		$mail->FromName = decode_html($from_name);
		$mail->addAddress($to_email);
		$this->setCCAddress($mail,'cc',$cc);
		$this->setCCAddress($mail,'bcc',$bcc);
		if(!empty($from_email)) {
			$mail->AddReplyTo($from_email);
		}

		if (!empty($inReplyToMessageId)) {
			$mail->AddCustomHeader("In-Reply-To", $inReplyToMessageId);
		}
		// vtmailscanner customization: If Support Reply to is defined use it.
		global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
		if($HELPDESK_SUPPORT_EMAIL_REPLY_ID && $HELPDESK_SUPPORT_EMAIL_ID != $HELPDESK_SUPPORT_EMAIL_REPLY_ID) {
			$mail->AddReplyTo($HELPDESK_SUPPORT_EMAIL_REPLY_ID);
		}
		// END

		// Fix: Return immediately if Outgoing server not configured
		if(empty($mail->Host)) {
			return 0;
		}
		// END

		$mail_status = $this->MailSend($mail);
		
		if($mail_status != 1) {
			$mail_error = $this->getMailError($mail,$mail_status,$mailto);
		} else {
			$mail_error = $mail_status;
		}
		return $mail_error;
  }

	function MailSend($mail) {
		global $log;
		$log->info("Inside of Send Mail function.");
		if(!$mail->Send()){
			$log->debug("Error in Mail Sending : Error log = '".$mail->ErrorInfo."'");
			return $mail->ErrorInfo;
		}else{
			 $log->info("Mail has been sent from the vtigerCRM system : Status : '".$mail->ErrorInfo."'");
			return 1;
		}
	}

	function getMailError($mail,$mail_status,$to){
		//Error types in class.phpmailer.php
		/*
		provide_address, mailer_not_supported, execute, instantiate, file_access, file_open, encoding, data_not_accepted, authenticate,
		connect_host, recipients_failed, from_failed
		*/

		global $adb;
		$adb->println("Inside the function getMailError");
		$msg = array_search($mail_status,$mail->language);
		$adb->println("Error message ==> ".$msg);
		if($msg == 'connect_host'){
			$error_msg =  $msg;
		} elseif(strstr($msg,'from_failed')){
			$error_msg = $msg;
		} elseif(strstr($msg,'recipients_failed')) {
			$error_msg = $msg;
		} else {
			$adb->println("Mail error is not as connect_host or from_failed or recipients_failed");
		}

		$adb->println("return error => ".$error_msg);
		return $error_msg;
	}

	function setCCAddress($mail,$cc_mod,$cc_val){
		global $adb;
		$adb->println("Inside the functin setCCAddress");

		if($cc_mod == 'cc')
			$method = 'AddCC';
		if($cc_mod == 'bcc')
			$method = 'AddBCC';
		if($cc_val != '')
		{
			$ccmail = explode(",",trim($cc_val,","));
			for($i=0;$i<count($ccmail);$i++)
			{
				$addr = $ccmail[$i];
				$cc_name = preg_replace('/([^@]+)@(.*)/', '$1', $addr); // First Part Of Email
				if(stripos($addr, '<')) {
					$name_addr_pair = explode("<",$ccmail[$i]);
					$cc_name = $name_addr_pair[0];
					$addr = trim($name_addr_pair[1],">");
				}
				if($ccmail[$i] != '')
					$mail->$method($addr,$cc_name);
			}
		}
	}

}
