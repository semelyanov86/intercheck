<?php

 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
chdir (dirname(__FILE__) . '/../../../../');
include_once 'includes/main/WebUI.php';
global $current_user,$adb,$root_directory,$log;

if($_REQUEST['record']){
	$record = $_REQUEST['record'];
	$fileDetails = getFileDetails($record);
	$fileContent = false;

	if (!empty ($fileDetails)) {
		$filePath = $fileDetails['path'];
		$fileName = $fileDetails['name'];

		$fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
		$savedFile = $fileDetails['attachmentsid']."_".$fileName;

		while(ob_get_level()) {
			ob_end_clean();
		}
		$fileSize = filesize($filePath.$savedFile);
		$fileSize = $fileSize + ($fileSize % 1024);

		if (fopen($filePath.$savedFile, "r")) {
			$fileContent = fread(fopen($filePath.$savedFile, "r"), $fileSize);

			header("Content-type: ".$fileDetails['type']);
			header("Pragma: public");
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=\"$fileName\"");
			header("Content-Description: PHP Generated Data");
            header("Content-Encoding: none");
		}
	}
	echo $fileContent;
}

function getFileDetails($record) {
	global $adb;
	$fileDetails = array();

	$result = $adb->pquery("SELECT * FROM vtiger_attachments
						WHERE attachmentsid = ?", array($record));

	if($adb->num_rows($result)) {
		$fileDetails = $adb->query_result_rowdata($result);
	}
	return $fileDetails;
}

