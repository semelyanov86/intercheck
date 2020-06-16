<?php

require_once 'modules/ITS4YouReports/ITS4YouReports.php';

require_once 'vtlib/Vtiger/Mailer.php';

ini_set('display_errors', 0);
//error_reporting(63);ini_set('display_errors', 1);

function generate_cool_url($nazov){
//$nazov=trim(strtolower(stripslashes($nazov)));
//$Search = array(" - ","/"," ",",","ľ","š","č","ť","ž","ý","á","í","é","ó","ö","ú","ü","ä","ň","ď","ô","ŕ","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ó","Ú","Ď","\"","°");
$nazov=trim(stripslashes($nazov));
$Search = array(" - ","/",",","ľ","š","č","ť","ž","ý","á","í","é","ó","ö","ú","ü","ä","ň","ď","ô","ŕ","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ó","Ú","Ď","\"","°");
$Replace = array("-","-","-","l","s","c","t","z","y","a","i","e","o","o","u","u","a","n","d","o","r","l","s","c","t","z","y","a","i","e","o","u","d","","");
$return=str_replace($Search, $Replace, $nazov);
// echo $return;
return $return;
}

$vtigerMailer = new Vtiger_Mailer();

if(empty($currentModule)) $currentModule = 'ITS4YouReports';

// $recipientEmails = $this->getRecipientEmails();
$recipientEmails = array("olear@its4you.sk");
foreach($recipientEmails as $name => $email) {
	$vtigerMailer->AddAddress($email, $name);
}

$ITS4YouReports = new ITS4YouReports(true,90);

global $default_charset;
if(!isset($default_charset)){
    $default_charset = "UTF-8";
}
$ITS4YouReports_reportname = generate_cool_url($ITS4YouReports->reportname);
$ITS4YouReports_reportdesc = generate_cool_url($ITS4YouReports->reportdesc);
$currentTime = date('Y-m-d H:i:s');
$subject = $ITS4YouReports_reportname .' - '. $currentTime .' ('. DateTimeField::getDBTimeZone() .')';

$contents = getTranslatedString('LBL_AUTO_GENERATED_REPORT_EMAIL', $currentModule) .'<br/><br/>';
$contents .= '<b>'.getTranslatedString('LBL_REPORT_NAME', $currentModule) .' :</b> '. $ITS4YouReports_reportname .'<br/>';
$contents .= '<b>'.getTranslatedString('LBL_DESCRIPTION', $currentModule) .' :</b><br/>'. $ITS4YouReports_reportdesc .'<br/><br/>';

$vtigerMailer->Subject = "=?ISO-8859-15?Q?".imap_8bit(html_entity_decode($subject,ENT_QUOTES, "UTF-8"))."?=";
$vtigerMailer->Body    = $contents;
$vtigerMailer->ContentType = "text/html";

$generate = new GenerateObj($ITS4YouReports);
//$reportFormat = $this->scheduledFormat;
$reportFormat = "pdf;xls";
$reportFormat = explode(";",$reportFormat);
$tmpDir = "test/ITS4YouReports/";

$attachments = array();
if(in_array('pdf',$reportFormat)) {
    $report_html = $generate->generateReport(90,"HTML",false);
ITS4YouReports::sshow($report_html);
exit;
    $generate_pdf_filename = $tmpDir.generate_cool_url($generate->pdf_filename);
    $fileName = $rootDirectory.$tempFileName.$generate->pdf_filename.'.xls';
    if($generate_pdf_filename!="" && file_exists($generate_pdf_filename)){
        $fileName_arr = explode(".", $generate->pdf_filename);
        $fileName_arr[0] .= '_'. preg_replace('/[^a-zA-Z0-9_-\s]/', '', $currentTime);
		$fileName = implode(".", $fileName_arr);
        $attachments[$fileName] = $generate_pdf_filename;
    }
}

if(in_array('xls',$reportFormat)) {
    $report_data = $generate->generateReport(90,"XLS",false);
    $ITS4YouReports_xls = "Reports4You_1_90.xls";
    $fileName_arr = explode(".", $ITS4YouReports_xls);
    $fileName_arr[0] .= '_'. preg_replace('/[^a-zA-Z0-9_-\s]/', '', $currentTime);
	$fileName = implode(".", $fileName_arr);

    $fileName_path = $tmpDir.$ITS4YouReports_xls;
    $generate->writeReportToExcelFile($fileName_path,$report_data);
    $attachments[$fileName] = $fileName_path;
}

foreach($attachments as $attachmentName => $path) {
	$vtigerMailer->AddAttachment($path, "=?ISO-8859-15?Q?".imap_8bit(html_entity_decode($attachmentName,ENT_QUOTES, "UTF-8"))."?=");
}

$send_result = $vtigerMailer->Send(true);
echo "SEND RESULT -> ".$send_result."<br />";

foreach($attachments as $attachmentName => $path) {
    unlink($path);
}
