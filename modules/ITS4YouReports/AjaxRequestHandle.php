<?php

global $adb,$current_language, $current_user;
switch($_REQUEST["handler"])
{
    case "templates_order":
        $inStr = vtlib_purify($_REQUEST["reports_order"]);
        $inStr = rtrim($inStr, "@O@");
        $inArr = explode("@O@", $inStr);
        $tmplArr = array();
        foreach($inArr as $val)
        {
            $valArr = explode("_", $val);
            $tmplArr[$valArr[0]]["order"] = $valArr[1];
            $tmplArr[$valArr[0]]["is_active"] = "1";
            $tmplArr[$valArr[0]]["is_default"] = "0";
        }

        $adb->pquery("DELETE FROM its4you_reports4you_userstatus WHERE userid=?", array($current_user->id));
        $sqlA = "INSERT INTO its4you_reports4you_userstatus(reportid, userid, is_active, is_default, sequence)
                VALUES ";
        $sqlB = "";
        $params = array();
        foreach($tmplArr as $reportid=>$valArr)
        {
            $sqlB .= "(?,?,?,?,?),";
            $params[] = $reportid;
            $params[] = $current_user->id;
            $params[] = $valArr["is_active"];
            $params[] = $valArr["is_default"];
            $params[] = $valArr["order"];
        }

        $result = "error";
        if($sqlB != "")
        {
            $sqlB = rtrim($sqlB, ",");
            $sql = $sqlA.$sqlB;
            $adb->pquery($sql, $params);
            $result = "ok";
        }
        
        echo $result;
        break;
}