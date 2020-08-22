<?php

include_once 'include/Webservices/VtigerModuleOperation.php';
include_once 'modules/Settings/MailConverter/handlers/MailAttachmentMIME.php';

/**
 * Save the attachment to the database
 */
function SaveAttachmentDB($element) {
    global $adb;
    $attachid = $adb->getUniqueId('vtiger_crmentity');
    $filename = $element['name'];
    $description = $filename;
    $date_var = $adb->formatDate(date('YmdHis'), true);
    $usetime = $adb->formatDate($date_var, true);
    $userid = vtws_getIdComponents($element['assigned_user_id']);
    $userid = $userid[1];
    $setype = $element['setype'];
    $adb->pquery(
        'INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid, modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        array($attachid, $userid, $userid, $userid, $setype, $description, $usetime, $usetime, 1, 0)
    );
    SaveAttachmentFile($attachid, $filename, $element['content']);
    return $attachid;
}

/**
 * Save the attachment to the file
 */
function SaveAttachmentFile($attachid, $filename, $filecontent) {
    global $adb;

    $dirname = decideFilePath();
    if (!is_dir($dirname)) {
        mkdir($dirname);
    }

    $description = $filename;
    $filename = str_replace(' ', '_', $filename);
    $saveasfile = $dirname . $attachid . '_' . $filename;
    if (!file_exists($saveasfile)) {
        $fh = @fopen($saveasfile, 'wb');
        if (!$fh) {
            throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, 'Permission denied, could not open file to save attachment: '.$saveasfile);
        }
        preg_match('/^data:\w+\/\w+;base64,/', $filecontent, $matches);
        if (count($matches)>0) {
            // Base64 Encoded HTML5 Canvas image or similar coming from javascript
            $filecontent = str_replace($matches[0], '', $filecontent);
            $filecontent = str_replace(' ', '+', $filecontent);
        }
        fwrite($fh, base64_decode($filecontent));
        fclose($fh);
    }

    $mimetype = MailAttachmentMIME::detect($saveasfile);

    $adb->pquery(
        'INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?',
        array($attachid, $filename, $description, $mimetype, $dirname)
    );
}
?>