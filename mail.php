<?php
require_once('modules/Emails/class.smtp.php');
require_once("modules/Emails/class.phpmailer.php");

$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPDebug = 1;

$mail->Host = 'mail.canada-relocation.com';
$mail->Port = 587;
$mail->Username = 'support@canada-relocation.com';
$mail->Password = 'VZBNniEsuGs93Hwbg5u9aSRzmBE4R9';

$mail->setFrom('support@canada-relocation.com', 'support@canada-relocation.com');

$mail->addAddress('se@sergeyem.ru', 'se@sergeyem.ru');

$mail->Subject = 'This is test message';
$body = '<p><strong>«This is super test» </strong></p>';
$mail->msgHTML($body);
$mail->send();
