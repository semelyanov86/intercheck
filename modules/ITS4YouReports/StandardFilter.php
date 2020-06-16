<?php

require_once('modules/CustomView/CustomView.php');
require_once('modules/ITS4YouReports/classes/UIUtils.php');
$BLOCK1 = "<option selected value='Not Accessible'>".$app_strings['LBL_NOT_ACCESSIBLE']."</option>";

$ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

global $current_user;
$user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
if(file_exists($user_privileges_path)){
    require($user_privileges_path);
}

if(isset($_REQUEST["record"]) && $_REQUEST["record"]!="")
{
    $ITS4YouReports->getSelectedStandardCriteria($reportid);
    $BLOCK1 .= getITSPrimaryStdFilterHTML($ITS4YouReports,$ITS4YouReports->primarymodule,$ITS4YouReports->stdselectedcolumn);
    $BLOCK1 .= getITSSecondaryStdFilterHTML($ITS4YouReports,$ITS4YouReports->relatedmodulesstring,$ITS4YouReports->stdselectedcolumn);

    //added to fix the ticket #5117

    $selectedcolumnvalue = '"'. $ITS4YouReports->stdselectedcolumn . '"';
    if (!$is_admin && isset($ITS4YouReports->stdselectedcolumn) && strpos($BLOCK1, $selectedcolumnvalue) === false)
            $smarty_obj->assign("BLOCK1_STD",$BLOCK1);

    $BLOCKCRITERIA = $ITS4YouReports->getSelectedStdFilterCriteria($ITS4YouReports->stdselectedfilter);
    $smarty_obj->assign("BLOCKCRITERIA_STD",$BLOCKCRITERIA);

    if(isset($ITS4YouReports->startdate) && isset($ITS4YouReports->enddate))
    {
        $smarty_obj->assign("STARTDATE_STD",getValidDisplayDate($ITS4YouReports->startdate));
        $smarty_obj->assign("ENDDATE_STD",getValidDisplayDate($ITS4YouReports->enddate));
    }else{
        $smarty_obj->assign("STARTDATE_STD",$ITS4YouReports->startdate);
        $smarty_obj->assign("ENDDATE_STD",$ITS4YouReports->enddate);
    }	
	
}else{
    $primarymodule = vtlib_purify($_REQUEST["reportmodule"]);

    $BLOCK1 .= getITSPrimaryStdFilterHTML($ITS4YouReports,$primarymodule);
    if(!empty($ITS4YouReports->related_modules[$primarymodule])) {
            foreach($ITS4YouReports->related_modules[$primarymodule] as $key=>$value){
            $BLOCK1 .= getITSSecondaryStdFilterHTML($ITS4YouReports,$_REQUEST["secondarymodule_".$value]);
            }
    }
    $smarty_obj->assign("BLOCK1_STD",$BLOCK1);

    $BLOCKCRITERIA = $ITS4YouReports->getSelectedStdFilterCriteria();
    $smarty_obj->assign("BLOCKCRITERIA_STD",$BLOCKCRITERIA);
}
$BLOCKJS = $ITS4YouReports->getCriteriaJS();
$smarty_obj->assign("BLOCKJS_STD",$BLOCKJS);

?>
