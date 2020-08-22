<?php


/** Function to get primary columns for an advanced filter
 *  This function accepts The module as an argument
 *  This generate columns of the primary modules for the advanced filter 
 *  It returns a HTML string of combo values 
 */
function getPrimaryColumns_AdvFilterHTML($module, $selected = "") {
    $shtml = "";
    global $ITS4YouReports, $app_list_strings, $current_language;
    $mod_strings = return_module_language($current_language, $module);
    $block_listed = array();
    foreach ($ITS4YouReports->module_list[$module] as $key => $value) {
        if (isset($ITS4YouReports->pri_module_columnslist[$module][$key]) && !$block_listed[$key]) {
            $block_listed[$key] = true;
            $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$module] . " " . getTranslatedString($key) . "\" class=\"select\" style=\"border:none\">";
            foreach ($ITS4YouReports->pri_module_columnslist[$module][$key] as $field => $fieldlabel) {
                if (isset($mod_strings[$fieldlabel])) {
                    //fix for ticket 5191
                    $selected = decode_html($selected);
                    $field = decode_html($field);
                    //fix ends
                    if ($selected == $field) {
                        $shtml .= "<option selected value=\"" . $field . "\">" . $mod_strings[$fieldlabel] . "</option>";
                    } else {
                        $shtml .= "<option value=\"" . $field . "\">" . $mod_strings[$fieldlabel] . "</option>";
                    }
                } else {
                    if ($selected == $field) {
                        $shtml .= "<option selected value=\"" . $field . "\">" . $fieldlabel . "</option>";
                    } else {
                        $shtml .= "<option value=\"" . $field . "\">" . $fieldlabel . "</option>";
                    }
                }
            }
            $shtml .= "</optgroup>";
        }
    }
    return $shtml;
}

/** Function to get Secondary columns for an advanced filter
 *  This function accepts The module as an argument
 *  This generate columns of the secondary module for the advanced filter 
 *  It returns a HTML string of combo values
 */
function getSecondaryColumns_AdvFilterHTML($module, $selected = "") {
    global $ITS4YouReports;
    global $app_list_strings;
    global $current_language;

    if ($module != "") {
        $secmodule = explode(":", $module);
        for ($i = 0; $i < count($secmodule); $i++) {
            $modulename = vtlib_getModuleNameById($secmodule[$i]);
            $mod_strings = return_module_language($current_language, $modulename);
            if (vtlib_isModuleActive($modulename)) {
                $block_listed = array();
                foreach ($ITS4YouReports->module_list[$modulename] as $key => $value) {
                    if (isset($ITS4YouReports->sec_module_columnslist[$modulename][$key]) && !$block_listed[$key]) {
                        $block_listed[$key] = true;
                        $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$modulename] . " " . getTranslatedString($key) . "\" class=\"select\" style=\"border:none\">";
                        foreach ($ITS4YouReports->sec_module_columnslist[$modulename][$key] as $field => $fieldlabel) {
                            if (isset($mod_strings[$fieldlabel])) {
                                if ($selected == $field) {
                                    $shtml .= "<option selected value=\"" . $field . "\">" . $mod_strings[$fieldlabel] . "</option>";
                                } else {
                                    $shtml .= "<option value=\"" . $field . "\">" . $mod_strings[$fieldlabel] . "</option>";
                                }
                            } else {
                                if ($selected == $field) {
                                    $shtml .= "<option selected value=\"" . $field . "\">" . $fieldlabel . "</option>";
                                } else {
                                    $shtml .= "<option value=\"" . $field . "\">" . $fieldlabel . "</option>";
                                }
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

function getRelatedColumns($selected = "") {
    global $ITS4YouReports;
    $rel_fields = $ITS4YouReports->adv_rel_fields;
    if ($selected != 'All') {
        $selected = preg_split('/:/', $selected);
    }
    $related_fields = array();
    foreach ($rel_fields as $i => $index) {
        $shtml = '';
        foreach ($index as $key => $value) {
            $fieldarray = preg_split('/::/', $value);
            $shtml .= "<option value=\"" . $fieldarray[0] . "\">" . $fieldarray[1] . "</option>";
        }
        $related_fields[$i] = $shtml;
    }
    if (!empty($selected) && $selected[4] != '')
        return $related_fields[$selected[4]];
    else if ($selected == 'All') {
        return $related_fields;
    } else
        return "";
}

/** Function to get the  advanced filter criteria for an option
 *  This function accepts The option in the advenced filter as an argument
 *  This generate filter criteria for the advanced filter 
 *  It returns a HTML string of combo values
 */
function getAdvCriteriaHTML($selected = "") {
    require_once('modules/ITS4YouReports/ITS4YouReports.php');
    
    global $currentModule;
    foreach (ITS4YouReports::$adv_filter_options as $key => $value) {
        if ($selected == $key) {
            $shtml .= "<option selected value=\"" . $key . "\">" . vtranslate($value,$currentModule) . "</option>";
        } else {
            $shtml .= "<option value=\"" . $key . "\">" . vtranslate($value,$currentModule) . "</option>";
        }
    }

    return $shtml;
}

?>