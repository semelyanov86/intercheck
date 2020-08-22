<?php

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once("include/Zend/Json.php");
// ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
require_once('modules/ITS4YouReports/ITS4YouReports.php');

$return_html = "";

if(isset($_REQUEST["columnIndex"]) && $_REQUEST["columnIndex"]!=""){
    if(isset($_REQUEST["record"]) && $_REQUEST["record"]!=""){
        $record = vtlib_purify($_REQUEST["record"]);
    }else{
        $record = "";
    }
    if(isset($_REQUEST["fop_type"]) && $_REQUEST["fop_type"]!=""){
        $fop_type = vtlib_purify($_REQUEST["fop_type"]);
    }
    
    $ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
    $rel_fields = $ITS4YouReports->adv_rel_fields;
    global $currentModule;
    
    $columnIndex = vtlib_purify($_REQUEST["columnIndex"]);
    $ctype = "f";
    $s_date_value = $e_date_value = "";
    if(isset($_REQUEST["r_sel_fields"]) && $_REQUEST["r_sel_fields"]!=""){
        global $default_charset;
        $r_sel_fields = vtlib_purify($_REQUEST["r_sel_fields"]);
        $std_val_array = explode("<;@STDV@;>",html_entity_decode($r_sel_fields, ENT_QUOTES, $default_charset));
        
        if(in_array($fop_type, array("todayless",))){
            $s_date_value = "";
            if($std_val_array[0]!="--" && $std_val_array[0]!=""){
                $e_date_value = $std_val_array[0];
            }else{
                $e_date_value = $std_val_array[1];
            }
        }elseif(in_array($fop_type, array("todaymore","older1days","older7days","older15days","older30days","older60days","older90days","older120days",))){
            $s_date_value = $std_val_array[0];
            $e_date_value = "";
        }else{
            $s_date_value = $std_val_array[0];
            $e_date_value = $std_val_array[1];
        }
    }
    
    if($fop_type!="custom"){
        $visibility = "hidden";
    }else{
        $visibility = "visible";
    }
    
    $return_html .= "
    <table>
        <tr>
            <td width='20%'>
                <table><tbody>
                    <tr>
                        <td style='vertical-align:top;'>
                            <input name='startdate' id='jscal_field_sdate$columnIndex' style='border: 1px solid rgb(186, 186, 186);' size='10' maxlength='10' value='$s_date_value' type='text'>
                            <img style='visibility: $visibility;' src='themes/softed/images/btnL3Calendar.gif' id='jscal_trigger_sdate$columnIndex' align='absmiddle'><br>
                            <font size='1'><b>".getTranslatedString("LBL_SF_STARTDATE", $currentModule).":</b><em old='(yyyy-mm-dd)'>(dd-mm-yyyy)</em></font>
                        </td>
                    </tr>
                </tbody></table>
            </td>
            <td width='30%'>
                <table><tbody>
                    <tr>
                        <td style='vertical-align:top;'>
                            <input name='enddate' id='jscal_field_edate$columnIndex' style='border: 1px solid rgb(186, 186, 186);' size='10' maxlength='10' value='$e_date_value' type='text'>
                            <img style='visibility: $visibility;' src='themes/softed/images/btnL3Calendar.gif' id='jscal_trigger_edate$columnIndex' align='absmiddle'><br>
                            <font size='1'><b>".getTranslatedString("LBL_SF_ENDDATE", $currentModule).":</b><em old='(yyyy-mm-dd)'>(dd-mm-yyyy)</em></font>
                        </td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
    </table>
    ";
}

echo $return_html;