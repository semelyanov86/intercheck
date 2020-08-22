<?php

require_once 'modules/ITS4YouReports/ITS4YouReports.php';

global $current_user;if(is_admin($current_user)){
    $adb = PearDatabase::getInstance();
    
    ITS4YouReports::sshow("Ready");
    
    $homeTabId = getTabid("Home");
    $sql = "SELECT linkid,linkurl FROM vtiger_links WHERE tabid = ? AND linktype =? AND linkurl like ?";
    $linkidResult = $adb->pquery($sql, array($homeTabId, 'DASHBOARDWIDGET', "%ITS4YouReports%"));
    $linkidNumrows = $adb->num_rows($linkidResult);
    if ($linkidNumrows > 0) {
        while ($row = $adb->fetchByAssoc($linkidResult)) {
            $id_arr = explode("record=",$row["linkurl"]);
            if(is_array($id_arr) && $id_arr[1]!=""){
                $reportid = $id_arr[1];
                $check_result = $adb->pquery("SELECT deleted FROM its4you_reports4you WHERE reports4youid=?",array($reportid));
                $check_row = $adb->fetchByAssoc($check_result, 0);
                if($check_row["deleted"]!="0"){
                    $sql = "SELECT linkid FROM vtiger_links WHERE tabid = ? AND linktype =? AND linkurl =?";
                    $widgetUrl = 'index.php?module=ITS4YouReports&view=ShowWidget&name=GetReports&record=' . $reportid;
                    $linkidResult = $adb->pquery($sql, array($homeTabId, 'DASHBOARDWIDGET', $widgetUrl));
                    $linkidNumrows = $adb->num_rows($linkidResult);
                    if ($linkidNumrows > 0) {
                        $row = $adb->fetchByAssoc($linkidResult);
                        $linkid = $row['linkid'];
$adb->setDebug(true);
                        $adb->pquery("DELETE FROM vtiger_links WHERE linkid =?", array($linkid));
                        $adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE linkid =?", array($linkid));
$adb->setDebug(false);
                    }
                }
            }
        }
    }
    ITS4YouReports::sshow("Done");
}else{
    ITS4YouReports::sshow("Not Permitted");
}

