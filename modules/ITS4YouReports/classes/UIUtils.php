<?php

function getJoinInformation($params) {
    $fieldid_alias = $old_fieldid = $old_oth_as = $mif_alias = "";
    if ($params["fieldid"] != "") {
        $fieldid_alias = "_" . $params["fieldid"];
    }
    if ($params["old_oth_as"] != "") {
        $old_oth_as = $params["old_oth_as"];
    }
    if ($params["old_oth_fieldid"] != "") {
        $old_fieldid = "_" . $params["old_oth_fieldid"];
    }
    if (isset($params["join_type"]) && $params["join_type"] == "MIF") {
        if ($params["fieldtabid"] != "") {
            $mif_alias .= "_" . $params["fieldtabid"];
        }
    }
    // ITS4YOU-UP SlOl 12/14/2013 6:09:39 PM vtiger_cntactivityrel UPDATE
    if ($params["tablename"] == "vtiger_cntactivityrel") {
        $params["tablename"] = "vtiger_contactdetails";
        $old_fieldid = "";
        $mif_alias = $fieldid_alias;
    }
    // ITS4YOU-END 12/14/2013 6:09:41 PM
    $using["join"]["tablename"] = $params["tablename"] . $old_oth_as . $old_fieldid . $mif_alias;
    $using["join"]["columnname"] = $params["columnname"];
    $uifactory = new UIFactory($params);
    $uifactory->getModulesByUitype($params["field_uitype"], $params["tablename"], $params["columnname"]);
    // $using_module_arr = getModulesByUitype($params["field_uitype"],$params["tablename"],$params["columnname"]);
    if (in_array($params["field_uitype"], array("117"))) {
        if ($using["join"]["tablename"] != $params["using_aliastablename"]) {
            $using["using"]["tablename"] = $params["using_aliastablename"];
            $using["using"]["columnname"] = $params["using_columnname"];
        }
    } elseif (isset($params["join_type"]) && $params["join_type"] == "MIF" && empty($using_module_arr)) {
        $using["join"]["tablename"] = $params["tablename"] . $old_oth_as;
        $using["join"]["columnname"] = $params["table_index"][$params["tablename"]];
        if ($using["join"]["tablename"] != $params["using_aliastablename"]) {
            $using["using"]["tablename"] = $params["using_aliastablename"];
            $using["using"]["columnname"] = $params["using_columnname"];
        }
    } elseif ($params["tablename"] != "vtiger_crmentity" || in_array($params["field_uitype"], array("52", "53", "531"))) {
        $adb = PEARDatabase::getInstance();
        $sp_join_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype,tablename,columnname  FROM vtiger_field WHERE fieldid = ?", array($params["fieldid"])), 0);
        if (is_array($params["table_index"]) && isset($params["table_index"][$params["tablename"]]) && $params["tablename"] != $params["primary_table_name"]) {
// show("UIP N1");
            $using["join"]["tablename"] = $params["tablename"] . $old_oth_as . $old_fieldid . $mif_alias;
            $using["join"]["columnname"] = $params["table_index"][$params["tablename"]];
            $using["using"]["tablename"] = $params["primary_table_name"] . $old_oth_as . $old_fieldid . $mif_alias;
            $using["using"]["columnname"] = $params["table_index"][$params["primary_table_name"]];
        } elseif ($params["tablename"] == "vtiger_inventoryproductrel") {
            $using["join"]["tablename"] = $params["tablename"] . $fieldid_alias . $mif_alias;
            $using["join"]["columnname"] = "id";
            $using["using"]["tablename"] = $params["primary_table_name"] . $old_fieldid . $mif_alias;
            $using["using"]["columnname"] = $params["table_index"][$params["primary_table_name"]];
// show("UIP N2");
        } elseif (!empty($using_module_arr)) {
            $gmodule = $using_module_arr[0];
            if (vtlib_isModuleActive($gmodule)) {
                $ui_focus = CRMEntity::getInstance($gmodule);
                $ui_focus->modulename = $gmodule;
                if (is_array($params["table_index"])) {
                    if (isset($ui_focus->table_name) && $ui_focus->tab_name_index[$ui_focus->table_name] != "" && $params["tablename"] != $ui_focus->table_name) {
// show("UIP N3");
                        $using["using"]["tablename"] = $params["tablename"] . $old_fieldid . $mif_alias;
                        $using["using"]["columnname"] = $params["columnname"];
                        $using["join"]["tablename"] = $ui_focus->table_name . $fieldid_alias;
                        $using["join"]["columnname"] = $ui_focus->tab_name_index[$ui_focus->table_name];
                    }
                } else {
// show("UIP N4");
                    // SPECIAL FIELDS JOINING
                    $using["using"]["tablename"] = $sp_join_row["tablename"] . $old_oth_as . $old_fieldid . $mif_alias;
                    $using["using"]["columnname"] = $sp_join_row["columnname"];
                    $using["join"]["tablename"] = $ui_focus->table_name . $fieldid_alias . $mif_alias;
                    $using["join"]["columnname"] = $ui_focus->tab_name_index[$ui_focus->table_name];
                }
            }
        } elseif ($params["tablename"] != $params["primary_table_name"] && is_array($params["table_index"]) && array_key_exists($params["tablename"], $params["table_index"])) {
// show("UIP N5");
            $using["using"]["tablename"] = $params["primary_table_name"] . $old_fieldid . $mif_alias;
            $using["using"]["columnname"] = $params["table_index"];
            $using["join"]["tablename"] = $params["tablename"] . $old_fieldid;
            $using["join"]["columnname"] = $params["table_index"][$params["tablename"]];
        }/* elseif($params["tablename"]!=$params["primary_table_name"] && $params["using_aliastablename"]!="" && $params["using_columnname"]!=""){
          $using["using"]["tablename"]=$params["using_aliastablename"];
          $using["using"]["columnname"]=$params["using_columnname"];
          } */
    }
    
    return $using;
}

/** Function to get the HTML strings for the primarymodule standard filters
 * @ param $module : Type String
 * @ param $selected : Type String(optional)
 *  This Returns a HTML combo srings
 */
function getITSPrimaryStdFilterHTML($ITS4YouReports,$moduleid = '', $selected = "") {
    global $app_list_strings;
    global $current_language;
    $shtml = "";
    if (isset($moduleid) && $moduleid != '') {
        $module = vtlib_getModuleNameById($moduleid);

        $result = $ITS4YouReports->getStdCriteriaByModule($moduleid);
        $mod_strings = return_module_language($current_language, $module);

        if (isset($result)) {
            foreach ($result as $key => $value) {
                if (isset($mod_strings[$value])) {
                    if ($key == $selected) {
                        $shtml .= "<option selected value=\"" . $key . "\">" . getTranslatedString($module, $module) . " - " . getTranslatedString($value, $secmodule[$i]) . "</option>";
                    } else {
                        $shtml .= "<option value=\"" . $key . "\">" . getTranslatedString($module, $module) . " - " . getTranslatedString($value, $secmodule[$i]) . "</option>";
                    }
                } else {
                    if ($key == $selected) {
                        $shtml .= "<option selected value=\"" . $key . "\">" . getTranslatedString($module, $module) . " - " . $value . "</option>";
                    } else {
                        $shtml .= "<option value=\"" . $key . "\">" . getTranslatedString($module, $module) . " - " . $value . "</option>";
                    }
                }
            }
        }
    }
    return $shtml;
}

/** Function to get the HTML strings for the secondary  standard filters
 * @ param $module : Type String
 * @ param $selected : Type String(optional)
 *  This Returns a HTML combo srings for the secondary modules
 */
function getITSSecondaryStdFilterHTML($ITS4YouReports,$module = '', $selected = "") {
    global $app_list_strings;
    global $current_language;

    $ITS4YouReports->oCustomView = new CustomView();
    $shtml = "";
    if ($module != "") {
        $secmodule = explode(":", $module);
        for ($i = 0; $i < count($secmodule); $i++) {
            $mod_arr = explode("x", $secmodule[$i]);
            $moduleid = $mod_arr[0];
            $secmodulename = vtlib_getModuleNameById($moduleid);
            $fieldidstr = "";
            if (isset($mod_arr[1]) && $mod_arr[1] != "") {
                $fieldidstr = ":" . $mod_arr[1];
            }

            $result = $ITS4YouReports->oCustomView->getStdCriteriaByModule($secmodulename);
            $mod_strings = return_module_language($current_language, $secmodulename);
            if (isset($result)) {
                foreach ($result as $key => $value) {
                    if (isset($mod_strings[$value])) {
                        if ($key == $selected) {
                            $shtml .= "<option selected value=\"" . $key . "\">" . getTranslatedString($secmodulename, $secmodulename) . " - " . getTranslatedString($value, $secmodulename) . "</option>";
                        } else {
                            $shtml .= "<option value=\"" . $key . "\">" . getTranslatedString($secmodulename, $secmodulename) . " - " . getTranslatedString($value, $secmodulename) . "</option>";
                        }
                    } else {
                        if ($key == $selected) {
                            $shtml .= "<option selected value=\"" . $key . "\">" . getTranslatedString($secmodulename, $secmodulename) . " - " . $value . "</option>";
                        } else {
                            $shtml .= "<option value=\"" . $key . "\">" . getTranslatedString($secmodulename, $secmodulename) . " - " . $value . "</option>";
                        }
                    }
                }
            }
        }
    }
    return $shtml;
}

/**
 * Function to get the field information from module name and field label
 */
function getITSFieldByReportLabel($module, $label) {

    // this is required so the internal cache is populated or reused.
    getColumnFields($module);
    //lookup all the accessible fields
    $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
    if (empty($cachedModuleFields)) {
        return null;
    }
    foreach ($cachedModuleFields as $fieldInfo) {
        $fieldLabel = str_replace(' ', '_', $fieldInfo['fieldlabel']);
        if ($label == $fieldLabel) {
            return $fieldInfo;
        }
    }
    return null;
}

?>