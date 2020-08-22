<?php

require_once('include/utils/utils.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');
$return = ITS4YouReports::cleanITS4YouReportsCacheFiles();
echo $return;
exit;