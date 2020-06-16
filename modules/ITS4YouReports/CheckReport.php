<?php

require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
$check=$_REQUEST['check'];
global $default_charset,$mod_strings;
$id='';

if($_REQUEST['check']== 'folderCheck')
{
	$folderName = function_exists(iconv) ? @iconv("UTF-8",$default_charset, vtlib_purify($_REQUEST['folderName'])) : vtlib_purify($_REQUEST['folderName']);
	$folderName =str_replace(array("'",'"'),'',$folderName);
	if($folderName == "")
	{
		echo $mod_strings["LBL_REP_FOLDER_PROBLEM"];
	}else
	{
		$SQL="select * from  its4you_reports4you_folder where foldername=?";
		$sqlresult = $adb->pquery($SQL, array(trim($folderName)));
		$num_folders = trim($adb->num_rows($sqlresult));
		if($num_folders>0){
			echo $mod_strings["LBL_REP_FOLDER_EXIST"];
		}else{
			$SQL="insert into its4you_reports4you_folder (foldername) values (?)";
			$sqlresult = $adb->pquery($SQL, array(trim($folderName)));
			echo $mod_strings["LBL_REP_FOLDER_SUCCESS"];
		}
	}
}
exit; // ITS4YOU-CR SlOl
?>
