<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once("include/Zend/Json.php");
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');

$return_html = "";
$n_c = 3;
$n_r = 5;
$n = ($n_c*$n_r);
if($_REQUEST["sel_fields"]){
	global $current_user;
	$adb = PearDatabase::getInstance();
	
	$roleid = $current_user->roleid;
	$sub = getSubordinateRoleAndUsers($roleid);
	
	list($s_tablename,$fieldname,$s_module_field_label_str) = explode(":",$_REQUEST["currField"]);
	$s_module_field_arr = explode("_", $s_module_field_label_str);
	
	require_once 'modules/PickList/PickListUtils.php';
	
	$picklistValues = getAssignedPicklistValues($fieldname, $roleid, $adb);
	$valueArr = explode("|##|", $value);
	$pickcount = 0;
	$sel_fields = array();
	if(!empty($picklistValues)){
		foreach($picklistValues as $order=>$pickListValue){
			if(in_array(trim($pickListValue),array_map("trim", $valueArr))){
				$chk_val = "selected";
				$pickcount++;
			}else{
				$chk_val = '';
			}
			if(isset($_REQUEST['file']) && $_REQUEST['file'] == 'QuickCreate'){
				$sel_fields[] = array(htmlentities(getTranslatedString($pickListValue),ENT_QUOTES,$default_charset),$pickListValue,$chk_val );
			}else{
				$sel_fields[] = array(getTranslatedString($pickListValue),$pickListValue,$chk_val );
			}
		}
		if($pickcount == 0 && !empty($value)){
			$sel_fields[] =  array($app_strings['LBL_NOT_ACCESSIBLE'],$value,'selected');
		}
	}
	if($s_module_field_arr[0] == "Calendar"){
		if(in_array(trim("Task"),array_map("trim", $valueArr))){
			$chk_val = "selected";
		}else{
			$chk_val = '';
		}
		$sel_fields[] = array("Task",getTranslatedString("Task"),$chk_val );
		if(in_array(trim("Emails"),array_map("trim", $valueArr))){
			$chk_val = "selected";
		}else{
			$chk_val = '';
		}
		$sel_fields[] = array("Emails",getTranslatedString("Emails"),$chk_val );
	}

	$count_sel_fields = count($sel_fields);
	if($count_sel_fields>$n){
		$return_html .= "<select name='".vtlib_purify($_REQUEST["sfield_name"])."' id='".vtlib_purify($_REQUEST["sfield_name"])."_|_' multiple='true' size='5'>";
	}else{
		$return_html .= "<table>";
	}
	
	$selected_vals = array();
    if(isset($_REQUEST["r_sel_fields"])){
        $selected_vals = explode(",", vtlib_purify($_REQUEST["r_sel_fields"]) );
    }elseif(isset($_REQUEST["record"]) && $_REQUEST["record"]!=""){
        $adb = PearDatabase::getInstance();
        $sql = "SELECT value FROM its4you_reports4you_relcriteria WHERE queryid=? AND columnname=?";
        $result = $adb->pquery($sql,array(vtlib_purify($_REQUEST["record"]),vtlib_purify($_REQUEST["currField"])));
        while($row = $adb->fetchByAssoc($result)){
            $selected_vals = explode(",", $row["value"]);	
        }
	}

	$n_i = $n_ci = 0;
	$count_n = count($sel_fields);
	foreach ($sel_fields as $key=>$sf_array) {
		if($count_sel_fields<=$n){
			if($n_ci==0){
				$return_html .= "<tr>";
			}
			$return_html .= "<td>";
		}
		$sf_value = $sf_array[0];
		$sf_text = $sf_array[1];

		if($count_sel_fields>$n){
			$selected = "";
			if(in_array($sf_value,$selected_vals)){
				$selected = " selected='selected' ";
			}
			$return_html .= "<option id='$key' value='$sf_value' $selected>$sf_text</option>";
		}else{
			$selected = "";
			if(in_array($sf_value,$selected_vals)){
				$selected = " checked='true' ";
			}
			$return_html .= "<input type='checkbox' id='".vtlib_purify($_REQUEST["sfield_name"])."_|_$key' value='$sf_value' $selected>$sf_text";
		}
		
		if($count_sel_fields<=$n){
			$return_html .= "</td>";
		}
		$n_ci++;
		if($count_sel_fields<=$n){
			if($n_ci==$n_c){
				$return_html .= "</tr>";
				$n_ci=0;
			}
		}
		$n_i++;
		if($n_i==$count_n){
			for ($fi=0;$fi<($n_c-$n_ci);$fi++) {
				$return_html .= "<td>&nbsp;</td>";
			}
			$return_html .= "</tr>";
		}
	}
	if($count_sel_fields>$n){
		$return_html .= "</select>";
	}else{

		$return_html .= "</table>";
	}
}
echo $return_html;