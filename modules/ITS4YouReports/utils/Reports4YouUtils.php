<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');
// ITS4YOU-CR SlOl FUNCTIONS
/** Function to get the combo values for the Primary module Columns 
 *  @ param $module(module name) :: Type String
 *  @ param $selected (<selected or ''>) :: Type String
 *  This function generates the combo values for the columns  for the given module 
 *  and return a HTML string 
 */
function getPrimaryColumns_GroupingHTML($module, $selected = "", $ogReport = "") {
    global $app_list_strings, $current_language;
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }
    $id_added = false;
    $shtml = "";
    if (vtlib_isModuleActive($module)) {
        $mod_strings = return_module_language($current_language, $module);

        $block_listed = array();
        $selected = decode_html($selected);

        if (!isset($ogReport->module_list) || empty($ogReport->module_list)) {
            $ogReport->initListOfModules();
        }
        // ITS4YOU-CR SlOl 3. 3. 2014 10:43:03
        if (!isset($ogReport->pri_module_columnslist[$module]) || $ogReport->pri_module_columnslist[$module] == "") {
            $ogReport_pri_module_columnslist = $ogReport->getPriModuleColumnsList($module);
        }
        // ITS4YOU-END 3. 3. 2014 10:43:06
        foreach ($ogReport->module_list[$module] as $key => $value) {
            if (isset($ogReport->pri_module_columnslist[$module][$value]) && !($block_listed[$value])) {
                $block_listed[$value] = true;
                $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$module] . " " . vtranslate($value) . "\" class=\"select\" style=\"border:none\">";
                if ($id_added == false) {
                    $is_selected = '';
                    if ($selected == "vtiger_crmentity:crmid:" . $module . "_ID:crmid:I") {
                        $is_selected = 'selected';
                    }
                    $shtml .= "<option value=\"vtiger_crmentity:crmid:" . $module . "_ID:crmid:I\" {$is_selected}>" . vtranslate(vtranslate($module) . ' ID') . '</option>';
                    $id_added = true;
                }
                foreach ($ogReport->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
                    $is_selected = '';
                    if ($selected == decode_html($field)) {
                        $is_selected = 'selected';
                    }
                    $shtml .= '<option ' . $is_selected . ' value="' . $field . '">(' . vtranslate($module, $module) . ') ' . vtranslate($fieldlabel, $module) . '</option>';
                }
                $shtml .= '</optgroup>';
            }
        }
    }
    return $shtml;
}

/** Function to get the combo values for the Secondary module Columns 
 *  @ param $module(module name) :: Type String
 *  @ param $selected (<selected or ''>) :: Type String
 *  This function generates the combo values for the columns for the given module 
 *  and return a HTML string 
 */
function getSecondaryColumns_GroupingHTML($moduleid, $selected = "", $ogReport = "") {
    global $app_list_strings;
    global $current_language;
    $shtml = '';
    $adb = PearDatabase::getInstance();
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }
    
    $secmodule_arr = explode("x", $moduleid);
    $module_id = $secmodule_arr[0];
    $field_id = (isset($secmodule_arr[1]) && $secmodule_arr[1] != "" ? $secmodule_arr[1] : "");

    if ($module_id != "") {
        $module = vtlib_getModuleNameById($module_id);
        $moduleLabel = vtranslate($module, $module);

        $ui10Prefix = '';
        $fieldname = $fieldname_lbl = "";
        if ($field_id != "" && !in_array($field_id, ITS4YouReports::$customRelationTypes)) {
            $fieldname_row = $adb->fetchByAssoc($adb->pquery("SELECT fieldlabel,uitype FROM vtiger_field WHERE fieldid=?", array($field_id)), 0);
            $uitype = $fieldname_row["uitype"];
            if ('10' === $uitype) {
                $ui10Prefix = ' - '.$moduleLabel;
            }
            $fieldname = " " . $fieldname_row["fieldlabel"];
        } elseif ($field_id == "INV") {
            $fieldname = " Inventory";
        } elseif ($field_id == "MIF") {
            $fieldname = " More Information";
        }
        $optGroupLabel = vtranslate(trim($fieldname), $ogReport->primarymodule) . $ui10Prefix;

        $sec_options = array();
        $selected = decode_html($selected);

        $secmodule = explode(":", $module);
        for ($i = 0; $i < count($secmodule); $i++) {
            if (vtlib_isModuleActive($secmodule[$i])) {
                $block_listed = array();
                foreach ($ogReport->module_list[$secmodule[$i]] as $key => $value) {
                    if (isset($ogReport->sec_module_columnslist[$secmodule[$i] . $fieldname][$value])) {
                        $block_listed[$value] = true;

                        $fieldlabel = vtranslate($value, $secmodule[$i]);
                        $optionLabel = "($optGroupLabel) $fieldlabel";

                        $shtml .= "<optgroup label=\"" . $optionLabel . "\" class=\"select\" style=\"border:none\">";
                        // ITS4YOU-END 18. 2. 2014 12:13:59
                        foreach ($ogReport->sec_module_columnslist[$secmodule[$i] . $fieldname][$value] as $field => $fieldlabel) {
                            if ($selected == decode_html($field)) {
                                $shtml .= "<option selected value=\"" . $field . "\">($optGroupLabel) " . vtranslate($fieldlabel, $secmodule[$i]) . "</option>";
                            } else {
                                $shtml .= "<option value=\"" . $field . "\">($optGroupLabel) " . vtranslate($fieldlabel, $secmodule[$i]) . "</option>";
                            }
                        }
                        $shtml .= "</optgroup>";
                    }
                }
            }
        }
    }
    return $shtml;
}

/** Function to formulate the vtiger_fields for the primary modules 
 *  This function accepts the module name 
 *  as arguments and generates the vtiger_fields for the primary module as
 *  a HTML Combo values
 */
function getPrimaryColumnsHTML($module, $ogReport = "") {
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }
    $shtml = '';
    global $app_list_strings;
    global $app_strings;
    global $current_language;
    $id_added = false;
    $block_listed = array();
    foreach ($ogReport->module_list[$module] as $key => $value) {
        // ITS4YOU-CR SlOl 3. 3. 2014 10:43:03
        if (isset($ogReport->pri_module_columnslist[$module]) || $ogReport->pri_module_columnslist[$module] == "") {
            $ogReport->getPriModuleColumnsList($module);
        }
        // ITS4YOU-END 3. 3. 2014 10:43:06
        if (isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
            $block_listed[$value] = true;
            $translate_module = $module;
            if ($module == "Calendar" && in_array($value, array("LBL_RECURRENCE_INFORMATION", "LBL_RELATED_TO"))) {
                $translate_module = "Events";
            }
            $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$module] . " " . vtranslate($value, $translate_module) . "\" class=\"select\" style=\"border:none\">";
            if ($id_added == false) {
                $shtml .= "<option value=\"vtiger_crmentity:crmid:" . $module . "_ID:crmid:I\">" . vtranslate(vtranslate($module, $translate_module) . ' ID') . "</option>";
                $id_added = true;
            }
            foreach ($ogReport->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
                $shtml .= "<option value=\"" . $field . "\">" . vtranslate($fieldlabel, $translate_module) . "</option>";
            }
            $shtml .= "</optgroup>";
        }
    }
    return $shtml;
}

/** Function to formulate the vtiger_fields for the secondary modules
 *  This function accepts the module name
 *  as arguments and generates the vtiger_fields for the secondary module as
 *  a HTML Combo values
 */
function getSecondaryColumnsHTML($module, $ogReport = "") {
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }
    global $app_list_strings, $app_strings;
    global $current_language;
    $shtml = '';

    if ($module != "") {
        $secmodule = explode(":", $module);
        for ($i = 0; $i < count($secmodule); $i++) {
            $modulename = vtlib_getModuleNameById($secmodule[$i]);
            $mod_strings = return_module_language($current_language, $modulename);
            if (vtlib_isModuleActive($modulename)) {
                $block_listed = array();
                foreach ($ogReport->module_list[$modulename] as $key => $value) {
                    if (isset($ogReport->sec_module_columnslist[$modulename][$value]) && !$block_listed[$value]) {
                        $block_listed[$value] = true;
                        $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$modulename] . " " . vtranslate($value) . "\" class=\"select\" style=\"border:none\">";
                        foreach ($ogReport->sec_module_columnslist[$modulename][$value] as $field => $fieldlabel) {
                            if (isset($mod_strings[$fieldlabel])) {
                                $shtml .= "<option value=\"" . $field . "\">" . $mod_strings[$fieldlabel] . "</option>";
                            } else {
                                $shtml .= "<option value=\"" . $field . "\">" . $fieldlabel . "</option>";
                            }
                        }
                        $shtml .= "</optgroup>";
                    }
                }
            }
        }
    }
    return $shtml;
}

function sgetColumnstoTotalHTMLScript($Objects, $tabid) {
    $mod_arr = explode("x", $tabid);
    $module = vtlib_getModuleNameById($mod_arr[0]);
    $fieldidstr = "";
    if (isset($mod_arr[1]) && $mod_arr[1] != "") {
        $fieldidstr = ":" . $mod_arr[1];
    }
    //retreive the vtiger_tabid	
    global $current_user;
    $adb = PearDatabase::getInstance();
    $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
    if (file_exists($user_privileges_path)) {
        require($user_privileges_path);
    }

    $result = ITS4YouReports::getColumnsTotalRow($tabid);

    if ($adb->num_rows($result) > 0) {
        do {
            $typeofdata = explode("~", $columntototalrow["typeofdata"]);

            $object_name = 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'];

            $Objects[] = $object_name . "_SUM:2" . $fieldidstr;
            $Objects[] = $object_name . "_AVG:3" . $fieldidstr;
            $Objects[] = $object_name . "_MIN:4" . $fieldidstr;
            $Objects[] = $object_name . "_MAX:5" . $fieldidstr;
        } while ($columntototalrow = $adb->fetch_array($result));
    }
    return $Objects;
}

// ITS4YOU-CR SlOl 24. 2. 2014 13:57:34
function sgetNewColumnstoTotalHTMLScript($Objects) {
    $returnObjects = array();
    foreach ($Objects as $key => $ObjectRow) {
        $ObjectRow_array = explode(":", $ObjectRow);
        $last_key = count($ObjectRow_array) - 1;
        $fieldidstr = $fieldid = $clear_tablename = "";
        if (is_numeric($ObjectRow_array[$last_key]) || in_array($ObjectRow_array[$last_key], ITS4YouReports::$customRelationTypes)) {
            $fieldid = $ObjectRow_array[$last_key];
            $fieldidstr = ":" . $fieldid;
            $typeofdata = $ObjectRow_array[$last_key - 1];
            array_pop($ObjectRow_array);
            $clear_tablename = trim($ObjectRow_array[0], "_$fieldid");
            $ObjectRow = implode(":", $ObjectRow_array);
        } else {
            $ObjectRow = implode(":", $ObjectRow_array);
            $typeofdata = $ObjectRow_array[$last_key];
        }

        list($sc_tablename, $sc_columnname, $sc_modulestr) = explode(':', $ObjectRow);
        if ($clear_tablename == "") {
            $clear_tablename = $sc_tablename;
        }
        list($sc_module) = explode('_', $sc_modulestr);
        $sc_module_id = getTabid($sc_module);
        $adb = PearDatabase::getInstance();
        if ($clear_tablename != "") {
            $summariesAvailableUiTypes = array('7', '9', '71', '72', '712');
            if ('9' === $sc_module_id) {
                if ('duration_hours' === $sc_columnname) {
                    $summariesAvailableUiTypes[] = '63';
                }
                if ('duration_minutes' === $sc_columnname) {
                    $summariesAvailableUiTypes[] = '16';
                }
            }
			$sc_field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype FROM vtiger_field WHERE tablename = ? and columnname = ? and tabid=?", array($clear_tablename, $sc_columnname, $sc_module_id)), 0);
            if ($sc_columnname=='ps_producttotalvatsum' 
				|| 'duration_sum_time' === $sc_columnname 
				|| in_array($sc_field_row["uitype"], $summariesAvailableUiTypes) 
				|| ($sc_field_row["uitype"] == '1' && ($typeofdata[0] == "N" || $typeofdata[0] == "I" || $typeofdata[0] == "NN"))) {
                $object_name = 'cb:' . $ObjectRow;

                $returnObjects[] = $object_name . "_SUM:2" . $fieldidstr;
                $returnObjects[] = $object_name . "_AVG:3" . $fieldidstr;
                $returnObjects[] = $object_name . "_MIN:4" . $fieldidstr;
                $returnObjects[] = $object_name . "_MAX:5" . $fieldidstr;
            }
        }
    }
    return $returnObjects;
}

// ITS4YOU-END 24. 2. 2014 13:57:36 
function getPrimaryColumns($Options, $module, $id_added = false, $ogReport = "") {
    if(!isset($Options) || !is_array($Options)){
        $Options = array();
    }
    if($ogReport==""){
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }

    global $app_list_strings;
    global $app_strings;
    global $current_language;
    $mod_strings = return_module_language($current_language, $module);
    $block_listed = array();
    // ITS4YOU-CR SlOl 3. 3. 2014 10:43:03
    if (!isset($ogReport->module_list[$module]) || empty($ogReport->module_list[$module])) {
        $ogReport->initListOfModules();
    }
    if (!isset($ogReport->pri_module_columnslist[$module]) || empty($ogReport->pri_module_columnslist[$module]) || $ogReport->pri_module_columnslist[$module] == "") {
        $ogReport->getPriModuleColumnsList($module);
    }

    $lead_converted_added = false;

    $moduleLabel = vtranslate($module, $module);
    // ITS4YOU-END 3. 3. 2014 10:43:06pri_module_columnslist[$module]
    if ($module == "Calendar") {
        $calendar_block = vtranslate($module, $module);
        $cal_options = $cal_options_f[$calendar_block] = array();
        $skip_fields = array("eventstatus", "status");
        $status_arr = array();
        foreach ($ogReport->pri_module_columnslist[$module] as $block_key => $field_array) {
            foreach ($field_array as $column_str => $column_label) {
                $column_arr = explode(":", $column_str);
                $optionLabel = "($moduleLabel) $column_label";
                if (!in_array($column_arr[1], $skip_fields)) {
                    $cal_options[$block_key][$column_str] = $optionLabel;
                } elseif (empty($status_arr)) {
                    $status_arr = array("value" => $column_str, "text" => $optionLabel);
                }
            }
            $count_arri = 0;
            $due_date_populated = $duration_minutes_populated = $duration_hours_populated = false;
            foreach ($cal_options as $b => $inter) {
                $count_arri++;
                if ($block_key != "Custom Information") {
                    if (!empty($intersect)) {
                        $intersect = array_intersect_assoc($intersect, $cal_options[$block_key]);
                        $Dintersect1 = array_diff($cal_options[$block_key], $intersect);
                        $Dintersect2 = array_diff($cal_options[$prev_block_key], $intersect);
                        foreach ($intersect as $field => $fieldlabel) {
                            if (isset($mod_strings[$fieldlabel]))
                                $fieldlabel = $mod_strings[$fieldlabel];
                            //$optionLabel = "($moduleLabel) $fieldlabel";
                            $optionLabel = $fieldlabel;
                            if (!$ogReport->in_multiarray($field, $cal_options_f[$calendar_block], "value")) {
                                $cal_options_f[$calendar_block][] = array("value" => $field, "text" => $optionLabel);
                            }
                        }
                        foreach ($Dintersect1 as $field => $fieldlabel) {
                            if (isset($mod_strings[$fieldlabel]))
                                $fieldlabel = $mod_strings[$fieldlabel];
                            //$optionLabel = "($moduleLabel) $fieldlabel";
                            $optionLabel = $fieldlabel;
                            // FIX FOR DUAL CALENDAR OPTIONS !!! S
                            if ($ogReport->in_multiarray($field, $cal_options_f[$calendar_block], "value") == true) {
                                continue;
                            }
                            if (strpos($field, "vtiger_activity:due_date:") !== false) {
                                $cal_options_f[$calendar_block][] = array("value" => $field, "text" => $optionLabel);
                                continue;
                            }
                            if (strpos($field, "vtiger_activity:duration_minutes:Calendar_Duration_Minutes:duration_minutes:") !== false) {
                                $cal_options_f[$calendar_block][] = array("value" => $field, "text" => $optionLabel);
                                continue;
                            }
                            if (strpos($field, "vtiger_activity:duration_hours:Calendar_Duration:duration_hours:") !== false) {
                                $cal_options_f[$calendar_block][] = array("value" => $field, "text" => $optionLabel);
                                continue;
                            }
                            // FIX FOR DUAL CALENDAR OPTIONS !!! E
                            if (!$ogReport->in_multiarray($field, $cal_options_f[$block_key], "value")) {
                                $cal_options_f[$block_key][] = array("value" => $field, "text" => $optionLabel);
                            }
                        }
                        foreach ($Dintersect2 as $field => $fieldlabel) {
                            // FIX FOR DUAL CALENDAR OPTIONS !!! S
                            if (strpos($field, "vtiger_activity:due_date:") !== false) {
                                continue;
                            }
                            if (strpos($field, "vtiger_activity:duration_minutes:Calendar_Duration_Minutes:duration_minutes:") !== false) {
                                continue;
                            }
                            if (strpos($field, "vtiger_activity:duration_hours:Calendar_Duration:duration_hours:") !== false) {
                                continue;
                            }
                            if (isset($mod_strings[$fieldlabel]))
                                $fieldlabel = $mod_strings[$fieldlabel];
                            //$optionLabel = "($moduleLabel) $fieldlabel";
                            $optionLabel = $fieldlabel;
                            // FIX FOR DUAL CALENDAR OPTIONS !!! E
                            if (!$ogReport->in_multiarray($field, $cal_options_f[$prev_block_key], "value")) {
                                $cal_options_f[$prev_block_key][] = array("value" => $field, "text" => $optionLabel);
                            }
                        }
                    } else {
                        $intersect = $cal_options[$block_key];
                    }
                    if ($block_key != $prev_block_key) {
                        $prev_block_key = $block_key;
                    }
                } else {
                    foreach ($field_array as $field => $fieldlabel) {
                        if (isset($mod_strings[$fieldlabel]))
                            $fieldlabel = $mod_strings[$fieldlabel];
                        $optionLabel = "($moduleLabel) $fieldlabel";
                        if (!$ogReport->in_multiarray($field, $cal_options_f[$block_key], "value")) {
                            $cal_options_f[$block_key][] = array("value" => $field, "text" => $optionLabel);
                        }
                    }
                }
            }
        }
        $access_count_listed = false;
        if (in_array($module, array("Calendar",)) && $access_count_listed !== true) {
            $optgroup = $app_list_strings['moduleList'][$module] . " - " . vtranslate("Email Information", "ITS4YouReports");
            $access_count_option = "access_count:access_count:" . $module . "_access_count:Access Count:V";
            $access_count_label = vtranslate("Emails") . " " . vtranslate("Access Count");
            $optionLabel = "($moduleLabel) $access_count_label";
            $cal_options_f[$optgroup][] = array("value" => $access_count_option, "text" => $optionLabel);
            $access_count_listed = true;
        }
        if (!empty($status_arr)) {
            $cal_options_f[$calendar_block][] = $status_arr;
        }
//        ksort($cal_options_f);
        $Options = array_merge($Options, $cal_options_f);
    } else {
        foreach ($ogReport->module_list[$module] as $key => $value) {
            if (isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
                $block_listed[$key] = true;
                $optgroup = $app_list_strings['moduleList'][$module] . " - " . vtranslate($value);
                if ($id_added == false) {
                    $Options[$optgroup]["vtiger_crmentity:crmid:" . $module . "_ID:crmid:I"] = "($moduleLabel) ".vtranslate(vtranslate($module) . ' ID');
                    $id_added = true;
                }
                foreach ($ogReport->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
                    if (isset($mod_strings[$fieldlabel]))
                        $fieldlabel = $mod_strings[$fieldlabel];
                    $optionLabel = "($moduleLabel) $fieldlabel";
                    $Options[$optgroup][] = array("value" => $field, "text" => $optionLabel);
                }
            }
        }
    }

    return $Options;
}

function getSecondaryColumns($Options, $module, $ogReport = "") {
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports($recordid);
        }
    }
    global $app_list_strings, $app_strings;
    global $current_language, $default_charset;
    $adb = PearDatabase::getInstance();

    if (!isset($ogReport->module_list) || empty($ogReport->module_list)) {
        $ogReport->initListOfModules();
    }
    if ($module != "") {
        $secmodule = explode(":", $module);

        for ($i = 0; $i < count($secmodule); $i++) {
            $module_prefix = $secmodule[$i];

            $ui10Prefix = '';
            $secmodule_arr = explode("x", $secmodule[$i]);
            $module_id = $secmodule_arr[0];
            $field_id = (isset($secmodule_arr[1]) && $secmodule_arr[1] != "" ? $secmodule_arr[1] : "");
            $fieldname = $opt_fieldname = "";
            $modulename = vtlib_getModuleNameById($module_id);
            $moduleLabel = vtranslate($modulename, $modulename);

            if ($field_id != "" && is_numeric($field_id)) {
                $fieldname_row = $adb->fetchByAssoc($adb->pquery("SELECT fieldlabel, uitype FROM vtiger_field WHERE fieldid=?", array($field_id)), 0);
                $fieldname = " " . $fieldname_row["fieldlabel"];
                $uitype = $fieldname_row["uitype"];
                if ('10' === $uitype) {
                    $ui10Prefix = ' - '.$moduleLabel;
                }
                $opt_fieldname = " (" . vtranslate($fieldname_row["fieldlabel"], $ogReport->primarymodule) . $ui10Prefix . ") ";
            } elseif ($field_id == "INV") {
                $fieldname = " Inventory";
                $opt_fieldname = "(Inventory) ";
            } elseif ($field_id == "MIF") {
                $fieldname = "More Information ";
                $opt_fieldname = " (" . vtranslate('LBL_MORE_INFORMATION', "Users") . ")";
            }
            $optGroupLabel = vtranslate(trim($fieldname), $ogReport->primarymodule) . $ui10Prefix;

            //$mod_strings = return_module_language($current_language, $modulename);
            if (vtlib_isModuleActive($modulename)) {
                $block_listed = array();
                if (isset($_REQUEST["primarymoduleid"]) && $_REQUEST["primarymoduleid"] == 26) {
                    $campaignstatus_listed = false;
                }
                foreach ($ogReport->module_list[$modulename] as $key => $value) {
                    if (!isset($ogReport->sec_module_columnslist)) {
                        $ogReport->getSecModuleColumnsList($module);
                    }
                    if (isset($ogReport->sec_module_columnslist[trim($modulename . ' ' . trim($fieldname))][$value]) && !$block_listed[$value]) {
                        $block_listed[$value] = true;
                        $optgroup = $app_list_strings['moduleList'][$modulename] . " - " . $opt_fieldname . ' ' . vtranslate($value);
                        foreach ($ogReport->sec_module_columnslist[trim($modulename . ' ' . trim($fieldname))][$value] as $field => $fieldlabel) {
                            //if (isset($mod_strings[$fieldlabel]))
                            //    $fieldlabel = $mod_strings[$fieldlabel];
                            $fieldlabel = vtranslate($fieldlabel, $modulename);
                            $optionLabel = " ($optGroupLabel) $fieldlabel";
                            $Options[$module_prefix][$optgroup][] = array("value" => $field, "text" => $optionLabel);
                        }
                        if ($campaignstatus_listed !== true && isset($_REQUEST["primarymoduleid"]) && $_REQUEST["primarymoduleid"] == 26 && in_array($modulename, array("Leads", "Contacts", "Accounts",))) {
                            $campaignrelstatus_option = "vtiger_campaignrelstatus_$field_id:campaignrelstatus:" . $modulename . "_campaignrelstatus:Status:V:$field_id";
                            $campaignrelstatus_label = " ($optGroupLabel) " . vtranslate("Status");
                            $Options[$module_prefix][$optgroup][] = array("value" => $campaignrelstatus_option, "text" => $campaignrelstatus_label);
                            $campaignstatus_listed = true;
                        }
                    }
                }
                $access_count_listed = false;
                if ($_REQUEST["primarymoduleid"] != $_REQUEST["selectedmodule"] && $access_count_listed !== true && in_array($modulename, array("Calendar",))) {
                    $optgroup = $app_list_strings['moduleList'][$modulename] . " - " . vtranslate("Email Information", "ITS4YouReports");
                    $access_count_option = "access_count_$field_id:access_count:" . $modulename . "_access_count:Access Count:V:$field_id";
                    $access_count_label = " ($optGroupLabel) " . vtranslate("Access Count");
                    $Options[$module_prefix][$optgroup][] = array("value" => $access_count_option, "text" => $access_count_label);
                    $access_count_listed = true;
                } elseif ($access_count_listed !== true && in_array($modulename, array("Calendar",))) {
                    $optgroup = $app_list_strings['moduleList'][$modulename] . " - " . vtranslate("Email Information", "ITS4YouReports");
                    $access_count_option = "access_count:access_count:" . $modulename . "_access_count:Access Count:V";
                    $access_count_label = " ($optGroupLabel) " . vtranslate("Access Count");
                    $Options[$module_prefix][$optgroup][] = array("value" => $access_count_option, "text" => $access_count_label);
                    $access_count_listed = true;
                }
            }
        }
    }
    return $Options;
}

function sgetColumntoTotalOptions($Options, $primarymodule, $secondarymodules) {
    $SOptions = sgetColumnstoTotalObjectsOptions($Options, $primarymodule);
    if (!empty($secondarymodules)) {
        //$secondarymodule = explode(":",$secondarymodule);
        for ($i = 0; $i < count($secondarymodules); $i++) {
            $SOptions = sgetColumnstoTotalObjectsOptions($Options, $secondarymodules[$i]);
        }
    }
    return $SOptions;
}

// ITS4YOU-CR SlOl 1. 10. 2013 14:50:37 getfieldid
function get_field_id($key) {
    $fieldid = "";
    if ($key != "") {
        $adb = PearDatabase::getInstance();
        $key_arr = explode(":", $key);
        $key_subarr = explode("_", $key_arr[3]);
        $key_tabid = getTabid($key_subarr[0]);
        $sql = "SELECT fieldid FROM vtiger_field WHERE tablename=? AND fieldname=? AND tabid=?";
        $result = $adb->pquery($sql, array($key_arr[0], $key_arr[1], $key_tabid));
        while ($row = $adb->fetchByAssoc($result)) {
            $fieldid = ":" . $row["fieldid"];
        }
    }
    return $fieldid;
}

// ITS4YOU-UP SlOl 1. 10. 2013 14:56:39 fieldid
function getPrimaryStdFilter($module, $ogReport = "") {
    global $app_list_strings;
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports($recordid);
        }
    }
    global $current_language;
    $Options = array();
    if (vtlib_isModuleActive($module)) {
        $ogReport->oCustomView = new CustomView();
        // $result = $ogReport->oCustomView->getStdCriteriaByModule($module);
        $result = $ogReport->getStdCriteriaByModule($module);

        $mod_strings = return_module_language($current_language, $module);

        if (isset($result)) {
            foreach ($result as $key => $value) {
                $fieldid = "";
                if (isset($mod_strings[$value])) {
                    $Options[] = array("value" => $key . "$fieldid", 'text' => vtranslate($module, $module) . " - " . vtranslate($value, $secmodule[$i]));
                } else {
                    $Options[] = array("value" => $key . "$fieldid", 'text' => vtranslate($module, $module) . " - " . $value);
                }
            }
        }
    }
    return $Options;
}

function getSecondaryStdFilter($module_arr, $Options, $ogReport = "") {
    global $app_list_strings;
    global $current_language;
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports($recordid);
        }
    }
    $adb = PearDatabase::getInstance();

    $moduleid = $module_arr["id"];
    $modulename = $module_arr["name"];

    $mod_arr = explode("x", $moduleid);
    $sec_module_id = $mod_arr[0];
    $module = vtlib_getModuleNameById($mod_arr[0]);
    if (vtlib_isModuleActive($module)) {
        $field_id = "";
        if (isset($mod_arr[1]) && $mod_arr[1] != "") {
            $field_id = $mod_arr[1];
        }
        if (!in_array($field_id, ITS4YouReports::$customRelationTypes)) {
            $ogReport->oCustomView = new CustomView();
            $result = $ogReport->getStdCriteriaByModule($module);
            $mod_strings = return_module_language($current_language, $module);
            if (isset($result)) {
                foreach ($result as $key => $value) {
                    $option_val_arr = explode(":", $key);
                    $tablename = $option_val_arr[0];
                    if ($field_id != "") {
                        // $ogReport->ui10_related_modules
                        $field_ui_type = "";
                        $field_uitype_sql = "SELECT uitype FROM vtiger_field WHERE fieldid = ?";
                        $field_uitype_result = $adb->pquery($field_uitype_sql, array($field_id));
                        if (($field_uitype_result) && $adb->num_rows($field_uitype_result) > 0) {
                            $field_uitype_row = $adb->fetchByAssoc($field_uitype_result);
                            $field_ui_type = $field_uitype_row["uitype"];
                        }
                        $field_id_str = $field_id;
                        if ($field_ui_type == "10") {
                            $field_id_str = "$sec_module_id:$field_id_str";
                        }
                        $option_val_arr[] = $field_id_str;
                    }
                    $option_val_arr[0] = $tablename."_$field_id";
                    $option_str = implode(":", $option_val_arr);
                    if (isset($mod_strings[$value])) {
                        $Options[] = array("value" => $option_str, 'text' => $modulename . " - " . vtranslate($value, $module));
                    } else {
                        $Options[] = array("value" => $option_str, 'text' => $modulename . " - " . $value);
                    }
                }
            }
        }
    }
    return $Options;
}

// TIMELINE Columns START
function getPrimaryTLStdFilter($module, $ogReport = "") {
    global $app_list_strings;
    if ($ogReport == "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports();
        }
    }
    global $current_language;
    $Options = array();
    if (vtlib_isModuleActive($module)) {
        $result = getR4UStdCriteriaByModule($module);

        if (isset($result)) {
            foreach ($result as $key => $value) {
                $fieldid = "";
                $Options[vtranslate($module, $module)][] = array("value" => $key . "$fieldid", 'text' => $value . " - " . vtranslate($value, $secmodule[$i]));
            }
        }
    }

    return $Options;
}

function getSecondaryTLStdFilter($moduleid, $Options, $ogReport = "") {
    global $app_list_strings;
    if ($ogReport != "") {
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $ogReport = ITS4YouReports::getStoredITS4YouReport();
        } else {
            $ogReport = new ITS4YouReports($recordid);
        }
    }
    global $current_language;

    $adb = PEARDatabase::getInstance();
    $module_arr = explode("x", $moduleid);
    $module = vtlib_getModuleNameById($module_arr[0]);
    $module_lbl = vtranslate($module, $module);
    $fieldlabel = "";
    if (isset($module_arr[1]) && !empty($module_arr[1]) && is_numeric($module_arr[1])) {
        $field_sql = "SELECT fieldlabel FROM vtiger_field WHERE fieldid=?";
        $field_result = $adb->pquery($field_sql, array($module_arr[1]));
        $field_row = $adb->fetchByAssoc($field_result, 0);
        $fieldlabel = $field_row["fieldlabel"];
        if ($fieldlabel != "") {
            if (vtlib_isModuleActive($module)) {
                $fieldlabel = vtranslate($fieldlabel, $module);
            } else {
                $fieldlabel = vtranslate($fieldlabel);
            }
        }
    }
    if ($fieldlabel != "") {
        $optgroup_key .= "$fieldlabel ($module_lbl)";
    } else {
        $optgroup_key .= $module_lbl;
    }

    if (vtlib_isModuleActive($module)) {
        $ogReport->oCustomView = new CustomView();
        if ($module != "") {
            $secmodule = explode(":", $module);
            $module_sarr = explode("x", $moduleid);
            if (isset($module_sarr[1]) && !empty($module_sarr[1])) {
                $fieldid = ":" . $module_sarr[1];
            }
            for ($i = 0; $i < count($secmodule); $i++) {
                $result = $ogReport->getStdCriteriaByModule($secmodule[$i]);
                $mod_strings = return_module_language($current_language, $secmodule[$i]);

                if (isset($result)) {
                    foreach ($result as $key => $value) {
                        //$fieldid = get_field_id($key);
                        if (isset($mod_strings[$value])) {
                            $Options[$optgroup_key][] = array("value" => $key . "$fieldid", 'text' => vtranslate($value, $secmodule[$i]));
                        } else {
                            $Options[vtranslate($secmodule[$i], $secmodule[$i])][] = array("value" => $key . "$fieldid", 'text' => $value);
                        }
                    }
                }
            }
        }
    }
    return $Options;
}

// TIMELINE Columns END
function sgetColumnstoTotalObjectsOptions($Options, $module) {
    $relmod_arr = explode("x", $module);
    if (is_numeric($relmod_arr[0])) {
        $tabid = $relmod_arr[0];
        $module = vtlib_getModuleNameById($tabid);
        $r_fieldid = (isset($relmod_arr[1]) && $relmod_arr[1] != "" ? $relmod_arr[1] : "");
    } else {
        $tabid = getTabid($relmod_arr[0]);
        $r_fieldid = "";
    }
    //retreive the vtiger_tabid	
    $adb = PearDatabase::getInstance();
    global $current_user;
    $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
    if (file_exists($user_privileges_path)) {
        require($user_privileges_path);
    }

    $result = ITS4YouReports::getColumnsTotalRow($tabid);

    $columntototalrow = $adb->fetch_array($result);

    $Options = "";
    if ($adb->num_rows($result) > 0) {
        do {
            $typeofdata = explode("~", $columntototalrow["typeofdata"]);
            //if ($typeofdata[0] == "N" || $typeofdata[0] == "I" || $typeofdata[0] == "NN") {
            //vtiger_crmentity:crmid:Accounts_ID:crmid:I
            $optionvalue = $columntototalrow['tablename'] . ":" . $columntototalrow['columnname'] . ":" . $module . "_" . $columntototalrow['fieldlabel'] . ":" . $columntototalrow['fieldname'];

            $optgroup = vtranslate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
            $fieldidstr = ($r_fieldid != "" ? ":" . $r_fieldid : "");
            $Options[$optgroup][] = array("value" => $optionvalue . ':SUM' . $fieldidstr, 'text' => $columntototalrow['fieldlabel'] . " (SUM)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':AVG' . $fieldidstr, 'text' => $columntototalrow['fieldlabel'] . " (AVG)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':MIN' . $fieldidstr, 'text' => $columntototalrow['fieldlabel'] . " (MIN)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':MAX' . $fieldidstr, 'text' => $columntototalrow['fieldlabel'] . " (MAX)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':COUNT' . $fieldidstr, 'text' => $columntototalrow['fieldlabel'] . " (COUNT)");
            //}
        } while ($columntototalrow = $adb->fetch_array($result));
    }

    if (ITS4YouReports::isInventoryModule($module)) {
        $fieldtablename = 'vtiger_inventoryproductrel' . $tabid;
        $fields = array('listprice' => vtranslate('List Price', $module),
            'quantity' => vtranslate('Quantity', $module),
            'ps_producttotal' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL', $this->currentModule),
            'discount' => vtranslate('Discount', $module),
            'ps_productstotalafterdiscount' => $module_lbl . " " . vtranslate('LBL_PRODUCTTOTALAFTERDISCOUNT', $this->currentModule),
            'ps_productvatsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_VAT_SUM', $this->currentModule),
            'ps_producttotalsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL_VAT', $this->currentModule),
        );
        global $site_URL;
	    if (false !== strpos($site_URL, 'z-company')) {
			$fields['ps_profit'] = $module_lbl . ' ' . vtranslate('Profit', $this->currentModule);
		}
        $fields_datatype = array('listprice' => 'I',
            'quantity' => 'I',
            'ps_producttotal' => 'I',
            'discount' => 'I',
            'ps_productstotalafterdiscount' => 'I',
            'ps_productvatsum' => 'I',
            'ps_producttotalsum' => 'I',
        );
        global $site_URL;
	    if (false !== strpos($site_URL, 'z-company')) {
			$fields_datatype['ps_profit'] = 'I';
		}
        foreach ($fields as $fieldcolname => $label) {
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname;

            $optgroup = vtranslate($module, $module) . ' ' . vtranslate("Product", $module) . ' / ' . vtranslate("Service", $module) . ' - ' . $label;
            $Options[$optgroup][] = array("value" => $optionvalue . ':SUM', 'text' => $label . " (SUM)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':AVG', 'text' => $label . " (AVG)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':MIN', 'text' => $label . " (MIN)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':MAX', 'text' => $label . " (MAX)");
            $Options[$optgroup][] = array("value" => $optionvalue . ':COUNT', 'text' => $label . " (COUNT)");
        }
    }
    return $Options;
}

// ITS4YOU-END FUNTIONS 
// ITS4YOU-CR SlOl 5. 3. 2014 15:48:43
// ITS4YOU-END SUMMARIES FIELDS START
function sgetSummariesHTMLOptions($moduleid, $primarymoduleid = "") {
    //retreive the vtiger_tabid	
    global $current_user;
    $relmod_arr = explode("x", $moduleid);
    if (is_numeric($relmod_arr[0])) {
        $tabid = $relmod_arr[0];
        $module = vtlib_getModuleNameById($tabid);
        $fieldid = $relmod_arr[1];
        $fieldidstr = (isset($fieldid) && $fieldid != "" ? ":" . $fieldid : "");
    } else {
        $tabid = getTabid($relmod_arr[0]);
        $fieldid = $fieldidstr = "";
    }
    //$module_lbl = vtranslate(vtlib_getModuleNameById($tabid), vtlib_getModuleNameById($tabid));
    $adb = PearDatabase::getInstance();
    $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
    if (file_exists($user_privileges_path)) {
        require($user_privileges_path);
    }
//global $adb;$adb->setDebug(true);        
    $result = ITS4YouReports::getColumnsTotalRow($tabid);
//$adb->setDebug(false);
    global $default_charset;

    $options = "";
    $currentModuleName = "ITS4YouReports";
    // ITS4YOU-CR SlOl 7. 3. 2014 11:05:30 
    if (!isset($options[vtranslate("COUNT_GROUP", $currentModuleName)])) {
        if (vtlib_isModuleActive($module)) {
            $c_focus = CRMEntity::getInstance($module);
            // $optionvalue_count = $columntototalrow['tablename'] . ":" . $columntototalrow['columnname'] . ":" . $module . "_" . str_replace(" ", "_", $columntototalrow['fieldlabel']) . ":" . $columntototalrow['fieldname'];
            $optionvalue_count = "vtiger_crmentity:crmid:" . $module . "_" . 'LBL_RECORDS' . $count_module_lbl . ":" . $module . "_count";
            $option_details = explode(":", $optionvalue_count);
            $option_lbl_arr = explode("_", $option_details[2], 2);

            $calculation_type = $option_details[4];
            $count_module_lbl = "";
            $count_module = $option_lbl_arr[0];
            $count_module_lbl = " " . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($count_module, $count_module);
        }
        $options .= '<option value="' . $optionvalue_count . ':V:COUNT' . $fieldidstr . '">' . vtranslate("LBL_COUNT", $currentModuleName) . ' ' . vtranslate("LBL_RECORDS", $currentModuleName) . $count_module_lbl . '</option>';
    }
    // ITS4YOU-END 7. 3. 2014 11:05:37

    if ($adb->num_rows($result) > 0) {
        do {
            $typeofdata = explode("~", $columntototalrow["typeofdata"]);

            if ($columntototalrow['columnname'] != "") {
                //if ($typeofdata[0] == "N" || $typeofdata[0] == "I" || $typeofdata[0] == "NN") {
                //vtiger_crmentity:crmid:Accounts_ID:crmid:I
                $typeofdata_val = ":" . $typeofdata[0];
                $optionvalue = $columntototalrow['tablename'] . ":" . $columntototalrow['columnname'] . ":" . $module . "_" . $columntototalrow['fieldlabel'] . ":" . $columntototalrow['fieldname'] . $typeofdata_val;
                $optionvalue = str_replace("&", "@AMPKO@", html_entity_decode($optionvalue,ENT_QUOTES,$default_charset));

                //$options .= '<optgroup label="' . $module_lbl . " - " . $columntototalrow['fieldlabel'] . '">';
                $options .= '<optgroup label=" - ' . vtranslate($columntototalrow['fieldlabel'],$module) . '">';
                
                // $fieldidstr = ($columntototalrow['fieldid'] != "" ? ":" . $columntototalrow['fieldid'] : "");
                $options .= '<option value="' . $optionvalue . ':SUM' . $fieldidstr . '">'.vtranslate("SUM", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$module) . '</option>';
                $options .= '<option value="' . $optionvalue . ':AVG' . $fieldidstr . '">'.vtranslate("AVG", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$module) . '</option>';
                $options .= '<option value="' . $optionvalue . ':MIN' . $fieldidstr . '">'.vtranslate("MIN", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$module) . '</option>';
                $options .= '<option value="' . $optionvalue . ':MAX' . $fieldidstr . '">'.vtranslate("MAX", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$module) . '</option>';
                $options .= '</optgroup>';
                //}
            }
        } while ($columntototalrow = $adb->fetch_array($result));
    }

    if (ITS4YouReports::isInventoryModule($module)) {
        $fieldtablename = 'vtiger_inventoryproductrel' . $fieldid;
        $fields = array('listprice' => vtranslate('List Price', $module),
            'quantity' => vtranslate('Quantity', $module),
            'ps_producttotal' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL', $currentModuleName),
            'discount' => vtranslate('Discount', $module),
            'ps_productstotalafterdiscount' => $module_lbl . " " . vtranslate('LBL_PRODUCTTOTALAFTERDISCOUNT', $currentModuleName),
            'ps_productvatsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_VAT_SUM', $currentModuleName),
            'ps_producttotalsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL_VAT', $currentModuleName),
        );
        global $site_URL;
	    if (false !== strpos($site_URL, 'z-company')) {
			$fields['ps_profit'] = $module_lbl . ' ' . vtranslate('Profit', $currentModuleName);
		}
        $fields_datatype = array('listprice' => 'I',
            'quantity' => 'I',
            'ps_producttotal' => 'I',
            'discount' => 'I',
            'ps_productstotalafterdiscount' => 'I',
            'ps_productvatsum' => 'I',
            'ps_producttotalsum' => 'I',
        );
        global $site_URL;
	    if (false !== strpos($site_URL, 'z-company')) {
			$fields_datatype['ps_profit'] = 'I';
		}
        foreach ($fields as $fieldcolname => $label) {
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname . ":I".$fieldidstr;
            //$options .= '<optgroup label="' . vtranslate($module, $module) . ' ' . vtranslate("Product", $module) . ' / ' . vtranslate("Service", $module) . ' - ' . $label . '">';
            //$options .= '<optgroup label="' . vtranslate($module, $module) . ' - ' . $label . '">';
            $options .= '<optgroup label="' . $module_lbl . ' - ' . $label . '">';
            $options .= '<option value="' . $optionvalue . ':SUM">'.vtranslate("SUM", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label . '</option>';
            $options .= '<option value="' . $optionvalue . ':AVG">'.vtranslate("AVG", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label . '</option>';
            $options .= '<option value="' . $optionvalue . ':MIN">'.vtranslate("MIN", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label . '</option>';
            $options .= '<option value="' . $optionvalue . ':MAX">'.vtranslate("MAX", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label . '</option>';
            // $options .= '<option value="'.$optionvalue.':COUNT">COUNT '.$label.'</option>';
            $options .= '</optgroup>';
        }
    }
    return $options;
}

function sgetSelectedSummariesOptions($options_array) {
    $options = array();
    if (!empty($options_array)) {
        foreach ($options_array as $key => $option_string) {
            if ($option_string != "") {
                $option_string_dc = str_replace("@AMPKO@", "&", $option_string);

                $option_details = explode(":", $option_string_dc);
                $option_lbl_arr = explode("_", $option_details[2], 2);

                if (is_numeric($option_details[5]) || in_array($option_details[5], ITS4YouReports::$customRelationTypes)) {
                    $calculation_type = $option_details[6];
                } else {
                    $calculation_type = $option_details[5];
                }

                $count_module_lbl = "";
                if (vtlib_isModuleActive($option_lbl_arr[0])) {
                    $count_module = $option_lbl_arr[0];
                    $module_lbl = vtranslate($count_module, $count_module);
                    //$count_module_lbl = " " . vtranslate("LBL_OF", "ITS4YouReports") . " " . $module_lbl;
                    $count_module_lbl = " (" . $module_lbl. ")";
                }
                if ($calculation_type == "COUNT") {
                    $fieldlabel = vtranslate("LBL_RECORDS", "ITS4YouReports") . $count_module_lbl;
                } else {
                    if (vtlib_isModuleActive($option_lbl_arr[0])) {
                        $fieldlabel = vtranslate($option_lbl_arr[1], $option_lbl_arr[0]);
                    } else {
                        $fieldlabel = vtranslate($option_lbl_arr[1]);
                    }
                    $fieldlabel .= " ($module_lbl)";
                }
                $options[] = array("value" => $option_string, "text" => vtranslate($calculation_type, "ITS4YouReports").' ' . vtranslate("LBL_OF", "ITS4YouReports") . " " . $fieldlabel);
            }
        }
    }
    return $options;
}

function sgetSelectedSummariesHTMLOptions($options_array, $summaries_orderby = "") {
    $options_arr = sgetSelectedSummariesOptions($options_array);
    $options_html = "";
    foreach ($options_arr as $key => $option_arr) {
        $selected_option = "";
        if ($option_arr["value"] == $summaries_orderby) {
            $selected_option = " selected ";
        }
        $options_html .= '<option value="' . $option_arr["value"] . '" ' . $selected_option . ' >' . $option_arr["text"] . '</option>';
    }
    return $options_html;
}

function sgetSummariesOptions($module) {
    $options = array();
    if ($module != "") {
        global $current_user;
        $adb = PearDatabase::getInstance();
        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }

        $relmod_arr = explode("x", $module);
        if (is_numeric($relmod_arr[0])) {
            $tabid = $relmod_arr[0];
            $module = vtlib_getModuleNameById($tabid);
            $fieldid = $relmod_arr[1];
            $fieldidstr = (isset($fieldid) && $fieldid != "" ? $fieldid : "");
        } else {
            $tabid = getTabid($relmod_arr[0]);
            $fieldid = $fieldidstr = "";
        }
        $fieldidstr = "";
        if ($fieldid != "") {
            $fieldidstr = ":$fieldid";
        }

        $result = ITS4YouReports::getColumnsTotalRow($tabid);
        
        global $default_charset;
        
        $currentModuleName = "ITS4YouReports";

        $options = array();
        // ITS4YOU-CR SlOl 7. 3. 2014 11:05:30 
        if (!isset($options[vtranslate("COUNT_GROUP", $currentModuleName)])) {
            if (vtlib_isModuleActive($module)) {
                $c_focus = CRMEntity::getInstance($module);
                // $optionvalue_count = $columntototalrow['tablename'] . ":" . $columntototalrow['columnname'] . ":" . $module . "_" . str_replace(" ", "_", $columntototalrow['fieldlabel']) . ":" . $columntototalrow['fieldname'];
                $optionvalue_count = "vtiger_crmentity:crmid:" . $module . "_" . 'LBL_RECORDS' . $count_module_lbl . ":" . $module . "_count";
                $option_details = explode(":", $optionvalue_count);
                $option_lbl_arr = explode("_", $option_details[2], 2);

                $calculation_type = $option_details[4];
                $count_module_lbl = "";
                $count_module = $option_lbl_arr[0];
                $count_module_lbl = " " . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($count_module, $count_module);
            }
            $options[vtranslate("COUNT_GROUP", $currentModuleName)][] = array("value" => $optionvalue_count . ':V'.$fieldidstr.':COUNT' , "text" => vtranslate("LBL_COUNT", $currentModuleName) . ' ' . vtranslate("LBL_RECORDS", $currentModuleName) . $count_module_lbl);
        }
        // ITS4YOU-END 7. 3. 2014 11:05:37
        if ($adb->num_rows($result) > 0) {
            do {
                $typeofdata = explode("~", $columntototalrow["typeofdata"]);

                global $current_user;
                
                $typeofdata_val = ":" . $typeofdata[0];
                
                $optionvalue = $columntototalrow['tablename'] . ":" . $columntototalrow['columnname'] . ":" . $module . "_" . $columntototalrow['fieldlabel'] . ":" . $columntototalrow['fieldname'] . $typeofdata_val . $fieldidstr;
                $optionvalue = str_replace("&", "@AMPKO@", html_entity_decode($optionvalue,ENT_QUOTES,$default_charset));

                $group_key = vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
                $options[$group_key][] = array("value" => $optionvalue . ':SUM', "text" => vtranslate("SUM", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$columntototalrow['tablabel']));
                $options[$group_key][] = array("value" => $optionvalue . ':AVG', "text" => vtranslate("AVG", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$columntototalrow['tablabel']));
                $options[$group_key][] = array("value" => $optionvalue . ':MIN', "text" => vtranslate("MIN", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$columntototalrow['tablabel']));
                $options[$group_key][] = array("value" => $optionvalue . ':MAX', "text" => vtranslate("MAX", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . vtranslate($columntototalrow['fieldlabel'],$columntototalrow['tablabel']));
                //}
            } while ($columntototalrow = $adb->fetch_array($result));
        }

        if (ITS4YouReports::isInventoryModule($module)) {

            $fieldtablename = 'vtiger_inventoryproductrel' . $fieldid;
            $fields = array('listprice' => vtranslate('List Price', $module),
                'quantity' => vtranslate('Quantity', $module),
                'ps_producttotal' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL', $currentModuleName),
                'discount' => vtranslate('Discount', $module),
                'ps_productstotalafterdiscount' => $module_lbl . " " . vtranslate('LBL_PRODUCTTOTALAFTERDISCOUNT', $currentModuleName),
                'ps_productvatsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_VAT_SUM', $currentModuleName),
                'ps_producttotalsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL_VAT', $currentModuleName),
            );
	        global $site_URL;
		    if (false !== strpos($site_URL, 'z-company')) {
				$fields['ps_profit'] = $module_lbl . ' ' . vtranslate('Profit', $currentModuleName);
			}
            $fields_datatype = array('listprice' => 'I',
                'quantity' => 'I',
                'ps_producttotal' => 'I',
                'discount' => 'I',
                'ps_productstotalafterdiscount' => 'I',
                'ps_productvatsum' => 'I',
                'ps_producttotalsum' => 'I',
            );
	        global $site_URL;
		    if (false !== strpos($site_URL, 'z-company')) {
				$fields_datatype['ps_profit'] = 'I';
			}
            foreach ($fields as $fieldcolname => $label) {
                $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname . ":I".$fieldidstr;

                $group_key = vtranslate($module, $module) . ' ' . vtranslate("Product", $module) . ' / ' . vtranslate("Service", $module) . ' - ' . $label;
                $options[$group_key][] = array("value" => $optionvalue . ':SUM' , "text" => vtranslate("SUM", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label);
                $options[$group_key][] = array("value" => $optionvalue . ':AVG' , "text" => vtranslate("AVG", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label);
                $options[$group_key][] = array("value" => $optionvalue . ':MIN' , "text" => vtranslate("MIN", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label);
                $options[$group_key][] = array("value" => $optionvalue . ':MAX' , "text" => vtranslate("MAX", $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName) . " " . $label);
                // $options[$group_key][] = array("value"=>$optionvalue.':COUNT'.$fieldidstr,"text"=>'COUNT '.$label);
            }
        }
    }
    return $options;
}

// ITS4YOU-END SUMMARIES FIELDS END
// ITS4YOU-CR SlOl | 13.5.2014 13:22 
function getR4UMeta($module, $user) {
    $db = PearDatabase::getInstance();
    if (empty($moduleMetaInfo[$module])) {
        $handler = vtws_getModuleHandlerFromName($module, $user);
        $meta = $handler->getMeta();
        $moduleMetaInfo[$module] = $meta;
    }
    return $moduleMetaInfo[$module];
}

function getR4UColumnsListbyBlock($module, $block) {
    global $mod_strings, $app_strings;
    $block_ids = explode(",", $block);
    $tabid = getTabid($module);
    $adb = PearDatabase::getInstance();

    global $current_user;
    $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
    if (file_exists($user_privileges_path)) {
        require($user_privileges_path);
    }

    if (empty($meta) && $module != 'Calendar') {
        $meta = getR4UMeta($module, $current_user);
    }

    if ($tabid == 9)
        $tabid = "9,16";
    $display_type = " vtiger_field.displaytype in (1,2,3)";

    if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
        $tab_ids = explode(",", $tabid);
        $sql = "select * from vtiger_field ";
        $sql.= " where vtiger_field.tabid in (" . generateQuestionMarks($tab_ids) . ") and vtiger_field.block in (" . generateQuestionMarks($block_ids) . ") and vtiger_field.presence in (0,2) and";
        $sql.= $display_type;
        if ($tabid == 9 || $tabid == 16) {
            $sql.= " and vtiger_field.fieldname not in('notime','duration_minutes','duration_hours')";
        }
        $sql.= " order by sequence";
        $params = array($tab_ids, $block_ids);
    } else {
        $tab_ids = explode(",", $tabid);
        $profileList = getCurrentUserProfileList();
        $sql = "select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid ";
        $sql.= " where vtiger_field.tabid in (" . generateQuestionMarks($tab_ids) . ") and vtiger_field.block in (" . generateQuestionMarks($block_ids) . ") and";
        $sql.= "$display_type and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";

        $params = array($tab_ids, $block_ids);

        if (count($profileList) > 0) {
            $sql.= "  and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
            array_push($params, $profileList);
        }
        if ($tabid == 9 || $tabid == 16) {
            $sql.= " and vtiger_field.fieldname not in('notime','duration_minutes','duration_hours')";
        }

        $sql.= " group by columnname order by sequence";
    }
    if ($tabid == '9,16')
        $tabid = "9";
    $result = $adb->pquery($sql, $params);
    $noofrows = $adb->num_rows($result);
    //Added on 14-10-2005 -- added ticket id in list
    if ($module == 'HelpDesk' && $block == 25) {
        $module_columnlist['vtiger_crmentity:crmid::HelpDesk_Ticket_ID:I'] = 'Ticket ID';
    }
    //Added to include vtiger_activity type in vtiger_activity vtiger_customview list
    if ($module == 'Calendar' && $block == 19) {
        $module_columnlist['vtiger_activity:activitytype:activitytype:Calendar_Activity_Type:V'] = 'Activity Type';
    }

    if ($module == 'SalesOrder' && $block == 63)
        $module_columnlist['vtiger_crmentity:crmid::SalesOrder_Order_No:I'] = vtranslate('Order No');

    if ($module == 'PurchaseOrder' && $block == 57)
        $module_columnlist['vtiger_crmentity:crmid::PurchaseOrder_Order_No:I'] = vtranslate('Order No');

    if ($module == 'Quotes' && $block == 51)
        $module_columnlist['vtiger_crmentity:crmid::Quotes_Quote_No:I'] = vtranslate('Quote No');
    if ($module != 'Calendar') {
        $moduleFieldList = $meta->getModuleFields();
    }
    for ($i = 0; $i < $noofrows; $i++) {
        $fieldtablename = $adb->query_result($result, $i, "tablename");
        $fieldcolname = $adb->query_result($result, $i, "columnname");
        $fieldname = $adb->query_result($result, $i, "fieldname");
        $fieldtype = $adb->query_result($result, $i, "typeofdata");
        $fieldtype = explode("~", $fieldtype);
        $fieldtypeofdata = $fieldtype[0];
        $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
        $field = $moduleFieldList[$fieldname];

        if (!empty($field) && $field->getFieldDataType() == 'reference') {
            $fieldtypeofdata = 'V';
        } else {
            //Here we Changing the displaytype of the field. So that its criteria will be
            //displayed Correctly in Custom view Advance Filter.
            $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $fieldtypeofdata);
        }
        if ($fieldlabel == "Related To") {
            $fieldlabel = "Related to";
        }
        if ($fieldlabel == "Start Date & Time") {
            $fieldlabel = "Start Date";
            if ($module == 'Calendar' && $block == 19)
                $module_columnlist['vtiger_activity:time_start::Calendar_Start_Time:I'] = 'Start Time';
        }
        $fieldlabel1 = $fieldlabel;
        $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $fieldname . ":" . $module . "_" .
                $fieldlabel1 . ":" . $fieldtypeofdata;
        //added to escape attachments fields in customview as we have multiple attachments
        $fieldlabel = vtranslate($fieldlabel); //added to support i18n issue
        if ($module != 'HelpDesk' || $fieldname != 'filename')
            $module_columnlist[$optionvalue] = $fieldlabel;
        if ($fieldtype[1] == "M") {
            $mandatoryvalues[] = "'" . $optionvalue . "'";
            $showvalues[] = $fieldlabel;
            $data_type[$fieldlabel] = $fieldtype[1];
        }
    }
    return $module_columnlist;
}

function getR4UModuleColumnsList($module) {

    $module_info = getR4UCustomViewModuleInfo($module);
    foreach ($module_info[$module] as $key => $value) {
        $columnlist = getR4UColumnsListbyBlock($module, $value);

        if (isset($columnlist)) {
            $ret_module_list[$module][$key] = $columnlist;
        }
    }
    return $ret_module_list;
}

function getR4UStdCriteriaByModule($module) {
    $r4u_stdcriteria_name = ITS4YouReports::getITS4YouReportStoreName("stdcriteria");
    if ($r4u_stdcriteria_name != "" && isset($_SESSION[$r4u_stdcriteria_name]) && !empty($_SESSION[$r4u_stdcriteria_name])) {
        $stdcriteria_list = $_SESSION[$r4u_stdcriteria_name];
    } else {
        $adb = PearDatabase::getInstance();
        $tabid = getTabid($module);

        global $current_user;
        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }

        $module_info = getR4UCustomViewModuleInfo($module);
        $module_list = getR4UModuleColumnsList($module);
        foreach ($module_info[$module] as $key => $blockid) {
        	$multiCheck = explode(',', $blockid);
			if (1 < count($multiCheck)) {
				foreach ($multiCheck as $moreBlockId) {
					$blockids[] = $moreBlockId;
				}
			} else {
				$blockids[] = $blockid;
			}
        }
        
        if ('Calendar' === $module) {
        	$tabCondition = '(vtiger_field.tabid=? OR vtiger_field.tabid=?)';
        } else {
			$tabCondition = 'vtiger_field.tabid=?';
		}

        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid ";
            $sql.= ' where ' . $tabCondition . ' and vtiger_field.block in (' . generateQuestionMarks($blockids) . ')
	                and vtiger_field.uitype in (5,6,23,70)';
            $sql.= " and vtiger_field.presence in (0,2) order by vtiger_field.sequence";
            if ('Calendar' === $module) {
        		$params = array($tabid, 16, $blockids);
	        } else {
				$params = array($tabid, $blockids);
			}
        } else {
            $profileList = getCurrentUserProfileList();
            $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join  vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid ";
            $sql.= " where vtiger_field.tabid=? and vtiger_field.block in (" . generateQuestionMarks($blockids) . ") and vtiger_field.uitype in (5,6,23,70)";
            $sql.= " and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";

            $params = array($tabid, $blockids);

            if (count($profileList) > 0) {
                $sql.= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params, $profileList);
            }

            $sql.= " order by vtiger_field.sequence";
        }
        //$adb->setDebug(true);
        $result = $adb->pquery($sql, $params);
        //$adb->setDebug(false);

        while ($criteriatyperow = $adb->fetch_array($result)) {
            $fieldtablename = $criteriatyperow["tablename"];
            $fieldcolname = $criteriatyperow["columnname"];
            $fieldfieldname = $criteriatyperow["fieldname"];
            $fieldlabel = $criteriatyperow["fieldlabel"];
            $fieldname = $criteriatyperow["fieldname"]; // oldoldo
            $typeofdata_val = "";
            $typeofdata = explode("~", $criteriatyperow["typeofdata"]);
            $typeofdata_val = ":" . $typeofdata[0];
            $fieldlabel1 = $fieldlabel;
            // old $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $fieldname . ":" . $module . "_" . $fieldlabel1;
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldfieldname . $typeofdata_val;
            // vtiger_potential:closingdate:closingdate:Potentials_Expected_Close_Date
            $stdcriteria_list[$optionvalue] = $fieldlabel;
        }
        $_SESSION[$r4u_stdcriteria_name] = $stdcriteria_list;
    }
    return $stdcriteria_list;
}

function getR4UCustomViewModuleInfo($module) {
    $adb = PearDatabase::getInstance();
    global $current_language;
    if ($module == "Events") {
        $current_mod_strings = return_specified_module_language($current_language, "Calendar");
    } else {
        $current_mod_strings = return_specified_module_language($current_language, $module);
    }
    $block_info = Array();
    $modules_list = explode(",", $module);
    if (in_array($module, array("Calendar", "Events"))) {
        $module = "Calendar','Events";
        $modules_list = array('Calendar', 'Events');
    }

    // Tabid mapped to the list of block labels to be skipped for that tab.
    $skipBlocksList = array(
        getTabid('Contacts') => array('LBL_IMAGE_INFORMATION'),
        getTabid('HelpDesk') => array('LBL_COMMENTS'),
        getTabid('Products') => array('LBL_IMAGE_INFORMATION'),
        getTabid('Faq') => array('LBL_COMMENT_INFORMATION'),
        getTabid('Quotes') => array('LBL_RELATED_PRODUCTS'),
        getTabid('PurchaseOrder') => array('LBL_RELATED_PRODUCTS'),
        getTabid('SalesOrder') => array('LBL_RELATED_PRODUCTS'),
        getTabid('Invoice') => array('LBL_RELATED_PRODUCTS')
    );

    $Sql = "select distinct block,vtiger_field.tabid,name,blocklabel from vtiger_field inner join vtiger_blocks on vtiger_blocks.blockid=vtiger_field.block inner join vtiger_tab on vtiger_tab.tabid=vtiger_field.tabid where displaytype != 3 and vtiger_tab.name in (" . generateQuestionMarks($modules_list) . ") and vtiger_field.presence in (0,2) order by block";
    $result = $adb->pquery($Sql, array($modules_list));
    if ($module == "Calendar','Events")
        $module = "Calendar";

    $pre_block_label = '';
    while ($block_result = $adb->fetch_array($result)) {
        $block_label = $block_result['blocklabel'];
        $tabid = $block_result['tabid'];
        // Skip certain blocks of certain modules
        if (array_key_exists($tabid, $skipBlocksList) && in_array($block_label, $skipBlocksList[$tabid]))
            continue;

        if (trim($block_label) == '') {
            $block_info[$pre_block_label] = $block_info[$pre_block_label] . "," . $block_result['block'];
        } else {
            //$lan_block_label = $current_mod_strings[$block_label];
            $lan_block_label = vtranslate($block_label,$module);
            if (isset($block_info[$lan_block_label]) && $block_info[$lan_block_label] != '') {
                $block_info[$lan_block_label] = $block_info[$lan_block_label] . "," . $block_result['block'];
            } else {
                $block_info[$lan_block_label] = $block_result['block'];
            }
        }
        $pre_block_label = $lan_block_label;
    }
    $module_list[$module] = $block_info;

    return $module_list;
}

/** Function to get visible criteria for a report
 *  This function accepts The reportid as an argument
 *  It returns a array of selected option of sharing along with other options

 */
function getVisibleCriteria($recordid = '') {
    global $mod_strings;
    global $app_strings;
    global $adb, $current_user;
    //print_r("i am here");die;
    $filter = array();
    $selcriteria = "";
    if ($recordid != '') {
        $result = $adb->pquery("SELECT sharingtype FROM its4you_reports4you 
								INNER JOIN its4you_reports4you_settings ON its4you_reports4you.reports4youid = its4you_reports4you_settings.reportid 
								WHERE reports4youid=?", array($recordid));
        $selcriteria = $adb->query_result($result, 0, "sharingtype");
    }
    if ($selcriteria == "") {
        $selcriteria = 'Public';
    }
    $filter_result = $adb->query("select * from its4you_reports4you_reportfilters");
    $numrows = $adb->num_rows($filter_result);
    for ($j = 0; $j < $numrows; $j++) {
        $filter_id = $adb->query_result($filter_result, $j, "filterid");
        $filtername = $adb->query_result($filter_result, $j, "name");
        $name = $filtername;
        if ($filtername == 'Private') {
            $FilterKey = 'Private';
            $FilterValue = vtranslate('PRIVATE_FILTER');
        } elseif ($filtername == 'Shared') {
            $FilterKey = 'Shared';
            $FilterValue = vtranslate('SHARE_FILTER');
        } else {
            $FilterKey = 'Public';
            $FilterValue = vtranslate('PUBLIC_FILTER');
        }
        if ($FilterKey == $selcriteria) {
            $shtml['value'] = $FilterKey;
            $shtml['text'] = $FilterValue;
            $shtml['selected'] = "selected";
        } else {
            $shtml['value'] = $FilterKey;
            $shtml['text'] = $FilterValue;
            $shtml['selected'] = "";
        }
        $filter[] = $shtml;
    }
    return $filter;
}

function getShareInfo($recordid = '') {
    global $adb;
    $member_query = $adb->pquery("SELECT its4you_reports4you_sharing.setype,vtiger_users.id,vtiger_users.user_name FROM its4you_reports4you_sharing INNER JOIN vtiger_users on vtiger_users.id = its4you_reports4you_sharing.shareid WHERE its4you_reports4you_sharing.setype='users' AND its4you_reports4you_sharing.reports4youid = ?", array($recordid));
    $noofrows = $adb->num_rows($member_query);
    if ($noofrows > 0) {
        for ($i = 0; $i < $noofrows; $i++) {
            $userid = $adb->query_result($member_query, $i, 'id');
            $username = $adb->query_result($member_query, $i, 'user_name');
            $setype = $adb->query_result($member_query, $i, 'setype');
            $member_data[] = Array('id' => $setype . "::" . $userid, 'name' => $setype . "::" . $username);
        }
    }

    $member_query = $adb->pquery("SELECT its4you_reports4you_sharing.setype,vtiger_groups.groupid,vtiger_groups.groupname FROM its4you_reports4you_sharing INNER JOIN vtiger_groups on vtiger_groups.groupid = its4you_reports4you_sharing.shareid WHERE its4you_reports4you_sharing.setype='groups' AND its4you_reports4you_sharing.reports4youid = ?", array($recordid));
    $noofrows = $adb->num_rows($member_query);
    if ($noofrows > 0) {
        for ($i = 0; $i < $noofrows; $i++) {
            $grpid = $adb->query_result($member_query, $i, 'groupid');
            $grpname = $adb->query_result($member_query, $i, 'groupname');
            $setype = $adb->query_result($member_query, $i, 'setype');
            $member_data[] = Array('id' => $setype . "::" . $grpid, 'name' => $setype . "::" . $grpname);
        }
    }
    return $member_data;
}

?>