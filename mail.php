<?php
require_once('modules/Emails/class.smtp.php');
require_once("modules/Emails/class.phpmailer.php");

$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//$mail->SMTPAuth = true;
$mail->SMTPDebug = 1;
$mail->SMTPAuth = true;
$mail->SMTPAutoTLS = false;
$mail->SMTPSecure = 'tls';
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

$mail->Host = 'mail.canada-relocation.com';
$mail->Port = 587;
$mail->Username = 'support@canada-relocation.com';
$mail->Password = 'DMfhN8ZzT4';

$mail->setFrom('support@canada-relocation.com', 'support@canada-relocation.com');

$mail->addAddress('support@canada-relocation.com');

$mail->Subject = 'This is test message';
$body = '<p><strong>«This is super test» </strong></p>';
var_dump($mail);die;
$mail->msgHTML($body);
$mail->send();
