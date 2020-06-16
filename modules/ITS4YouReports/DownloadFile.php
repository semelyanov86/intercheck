<?php

require_once('config.php');
require_once('include/database/PearDatabase.php');

$adb = PearDatabase::getInstance();

$filepath = vtlib_purify($_REQUEST['filepath']);
$name = vtlib_purify($_REQUEST['filename']);

if($filepath != "")
{
    $filesize = filesize($filepath);
    if(!fopen($filepath, "r"))
    {
        echo 'unable to open file';
    }
    else
    {
        $fileContent = fread(fopen($filepath, "r"), $filesize);
    }
    header("Content-type: $fileType");
    header("Content-length: $filesize");
    header("Cache-Control: private");
    header("Content-Disposition: attachment; filename=$name");
    header("Content-Description: PHP Generated Data");
    echo $fileContent;
}
else
{
    echo "Record doesn't exist.";
}