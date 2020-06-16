<?php


class ITS4YouReports_IndexAjax_Action extends Vtiger_Action_Controller {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getStep5Columns');
        $this->exposeMethod('getStep5SUMColumns');
        $this->exposeMethod('getFilterColHtml');
        $this->exposeMethod('getFilterDateHtml');
        $this->exposeMethod('DownloadFile');
        $this->exposeMethod('ExportXLS');
        $this->exposeMethod('showSettingsList');
        $this->exposeMethod('addWidget');
        $this->exposeMethod('getKeyMetricReportColumns');
        $this->exposeMethod('deleteKeyMetricRecord');
        $this->exposeMethod('saveKeyMetricsOrder');
        // ITS4YOU-CR SlOl 4. 4. 2016 7:58:58
        $this->exposeMethod('getSchedulerGenerateFor');
    }

    function checkPermission(Vtiger_Request $request) {
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        return true;
    }

    function postProcess(Vtiger_Request $request) {
        return true;
    }

    function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    function getStep5Columns(Vtiger_Request $request) {
        $this->getColumns($request);
    }

    function getStep5SUMColumns(Vtiger_Request $request) {
        $this->getColumns($request, true);
    }

    function getColumns(Vtiger_Request $request, $is_sum = false) {
        $BLOCK0 = $BLOCK1 = $BLOCK2 = "";
        error_reporting(0);

        $selectedmodule = $request->get("selectedmodule");

        $SumOptions = [];
        $secondarymodule = '';
        $secondarymodules = [];

        $record = $request->get('record');

//ITS4YouReports::sshow($record);
        $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
        $primarymodule = $reportModel->getPrimaryModule();
        $primarymoduleid = $reportModel->getPrimaryModuleId();

        $modulename_prefix = "";
        if ($selectedmodule != $primarymoduleid && $selectedmodule != "") {
            $modulename = $selectedmodule;
            if ($modulename != "34xMIF") {
                $reportModel->getSecModuleColumnsList($modulename);
            }
            $module_array["id"] = $modulename;
            $modulename_arr = explode("x", $modulename);
            $modulename_id = $modulename_arr[0];
            if ($modulename_arr[1] != "") {
                $modulename_prefix = $modulename_arr[1];
            }
        } else {
            $module_array["module"] = $primarymodule;
            $module_array["id"] = $primarymoduleid;

            $modulename = $module_array["module"];
            $modulename_lbl = vtranslate($modulename, $modulename);
            $availModules[$module_array["id"]] = $modulename_lbl;

            $modulename_id = $module_array["id"];
        }

        $relmod_arr = explode("x", $modulename);
        if (is_numeric($relmod_arr[0])) {
            $stabid = $relmod_arr[0];
            $smodule = vtlib_getModuleNameById($stabid);
        }

//error_reporting(63);ini_set("display_errors",1);
        if ($smodule == "ModTracker") {
            $opt_value = [];
            $SPSumOptions[$module_array["id"]][$module_array["id"]][" - " . vtranslate("LBL_UPDATES")] = [];

            foreach (ITS4YouReports::$modTrackerColumns as $mtColumn) {
                if (isset(ITS4YouReports::$modTrackerColumnsArray[$mtColumn]) && ITS4YouReports::$modTrackerColumnsArray[$mtColumn] != "") {
                    $opt_value_str = ITS4YouReports::$modTrackerColumnsArray[$mtColumn];
                    $opt_value["value"] = $opt_value_str;
                    $opt_value["text"] = ITS4YouReports::getColumnStr_Label($opt_value_str);
                    $SPSumOptions[$module_array["id"]][$module_array["id"]][vtranslate("LBL_UPDATES")][] = $opt_value;
                }
            }
        } else {
            if ($is_sum) {
                $SPSumOptions[$module_array["id"]][$modulename_id] = sgetSummariesOptions($modulename);
            } else {
                if ($selectedmodule != $primarymoduleid && $selectedmodule != "") {
                    $SPSumOptions[$modulename] = $reportModel->getSecondaryColumns([], $modulename);
                } else {
                    $prim_options = $reportModel->getPrimaryColumns([], $modulename, true);
                    $SPSumOptions[$module_array["id"]][$modulename_id] = $prim_options;
                }
            }
        }

        $step5_result = "";

        if ($selectedmodule != $primarymoduleid && $selectedmodule == "") {
            $secondarymodule_arr = $reportModel->getReportRelatedModules($module_array["id"]);

            $reportModel->getSecModuleColumnsList($secondarymodule);
            $available_modules[] = ["id" => $primarymoduleid, "name" => $modulename_lbl, "checked" => "checked"];
            foreach ($secondarymodule_arr as $key => $value) {
                $available_modules[] = ["id" => $value["id"], "name" => $value["name"], "checked" => ""];
            }
            $AV_M = Zend_JSON::encode($available_modules);
            $step5_result .= $AV_M . "(!A#V_M@M_M#A!)";
        }

        $BLOCK1 = "";

        foreach ($SPSumOptions AS $module_key => $SumOptions) {
            $BLOCK2 = "";
            $r_modulename = vtlib_getModuleNameById($module_key);
            $r_modulename_lbl = vtranslate($r_modulename, $r_modulename);

            foreach ($SumOptions as $SumOptions_key => $SumOptions_value) {
                foreach ($SumOptions_value AS $optgroup => $optionsdata) {
                    if ($BLOCK2 != "") {
                        $BLOCK2 .= "(|@!@|)";
                    }
                    $BLOCK2 .= $optgroup;
                    $BLOCK2 .= "(|@|)";

                    $BLOCK2 .= Zend_JSON::encode($optionsdata);
                }

                $BLOCK1 .= $module_key . "(!#_ID@ID_#!)" . $r_modulename_lbl . "(!#_ID@ID_#!)" . $BLOCK2;
            }
        }

        $step5_result .= $BLOCK1;

        echo $step5_result;
    }

    function getFilterColHtml(Vtiger_Request $request) {
        require_once('modules/ITS4YouReports/ITS4YouReports.php');

        $return_html = "";
        $n_c = 3;
        $n_r = 5;
        $n = ($n_c * $n_r);
        $sfield_name = $request->get("sfield_name");

        $r_sel_fields = $request->get("r_sel_fields");

        $adb = PearDatabase::getInstance();
        global $current_user;
        //$roleid = $current_user->roleid;
        //$sub = getSubordinateRoleAndUsers($roleid);
        $roleid = $current_user->roleid;
        $sub = getRoleSubordinates($roleid);

        $picklistGroupValues = [];

        $currField = $request->get("currField");
        $currField_arr = explode(":", $currField);
        // list($s_tablename,$columnname,$s_module_field_label_str,$fieldname) = explode(":",$currField);
        $s_tablename = $currField_arr[0];
        $columnname = $currField_arr[1];
        $s_module_field_label_str = $currField_arr[2];
        $fieldname = $currField_arr[3];
        $last_key = (count($currField_arr) - 1);
        $s_tablename_clear = $s_tablename;
        if (is_numeric($currField_arr[$last_key]) || in_array($currField_arr[$last_key], ["INV", "MIF"])) {
            $s_tablename_clear = trim($s_tablename, "_" . $currField_arr[$last_key]);
        }
        $s_module_field_arr = explode("_", $s_module_field_label_str);
        $moduleName = $s_module_field_arr[0];
        $moduleTabId = getTabid($moduleName);
        $uitypeSql = "SELECT uitype FROM vtiger_field WHERE tabid=? AND tablename=? AND columnname=?";

        // ITS4YOU-CR SlOl 18. 3. 2016 14:14:08
        global $current_user;
        $currentUserModel = Users_Record_Model::getInstanceFromUserObject($current_user);
        $picklistGroupValues = $currentUserModel->getAccessibleGroupForModule($moduleName);
        // ITS4YOU-END

//$adb->setDebug(true);
        $uitypeParams = [$moduleTabId, $s_tablename_clear, $columnname];
        $uitypeResult = $adb->pquery($uitypeSql, $uitypeParams);
        $num_rowuitype = $adb->num_rows($uitypeResult);
        if ($num_rowuitype > 0) {
            $uitype_row = $adb->fetchByAssoc($uitypeResult);
        } elseif ($moduleName == "Leads" && $fieldname == "converted") {
            $uitype_row = ["uitype" => "56"];
        } else {
            $uitype_row = ["uitype" => "1"];
        }
        $return_array = ITS4YouReports::getSUiTypeValueArray($uitype_row, $columnname, $fieldname);
        $picklistValues = $return_array['picklistValues'];
        $valueArr = $return_array['valueArr'];

        if ($s_module_field_arr[0] == "Calendar" && $fieldname == "taskstatus") {
            $uitypeSql = "SELECT uitype FROM vtiger_field WHERE tabid=? AND tablename=? AND columnname=?";
            $uitypeParams = [getTabid('Events'), $s_tablename_clear, 'eventstatus'];
            $uitypeResult = $adb->pquery($uitypeSql, $uitypeParams);
            $num_rowuitype = $adb->num_rows($uitypeResult);
            if ($num_rowuitype > 0) {
                $uitype_row = $adb->fetchByAssoc($uitypeResult);
            }
            $return_array = ITS4YouReports::getSUiTypeValueArray($uitype_row, 'eventstatus', 'eventstatus');
            foreach ($return_array["picklistValues"] as $p_key => $p_value) {
                $picklistValues[$p_key] = $p_value;
            }
            foreach ($return_array["valueArr"] as $p_sel) {
                $valueArr[] = $p_sel;
            }
//echo "<pre>";print_r($return_array);echo "</pre>";
        }

        $pickcount = 0;
        $sel_fields = [];
        $field_uitype = $uitype_row["uitype"];

        if (!empty($picklistValues)) {
            foreach ($picklistValues as $order => $pickListValue) {
                $pickListValue = trim($pickListValue);
                if ($uitype_row['uitype'] == '56') {
                    $check_val = ($pickListValue == "LBL_YES" ? "yes" : "no");
                    if (in_array(trim($order), array_map("trim", $valueArr)) || in_array($check_val, $valueArr)) {
                        $chk_val = "selected";
                    } else {
                        $chk_val = "";
                    }
                    $pickcount++;
                } elseif (in_array(trim($pickListValue), array_map("trim", $valueArr))) {
                    $chk_val = "selected";
                    $pickcount++;
                } else {
                    $chk_val = '';
                }
                if ($uitype_row['uitype'] == '56') {
                    $sel_fields[] = [vtranslate($pickListValue, $s_module_field_arr[0]), $order, $chk_val];
                } else {
                    $sel_fields[] = [vtranslate($pickListValue, $s_module_field_arr[0]), $pickListValue, $chk_val];
                }
            }
            if ($pickcount == 0 && !empty($value)) {
                $sel_fields[] = [vtranslate('LBL_NOT_ACCESSIBLE'), $value, 'selected'];
            }
        }

        if ($s_module_field_arr[0] == "Calendar" && $fieldname == "activitytype") {
            if (in_array(trim("Task"), array_map("trim", $valueArr))) {
                $chk_val = "selected";
            } else {
                $chk_val = '';
            }
            $sel_fields[] = ["Task", getTranslatedString("Task"), $chk_val];
            if (in_array(trim("Emails"), array_map("trim", $valueArr))) {
                $chk_val = "selected";
            } else {
                $chk_val = '';
            }
            $sel_fields[] = ["Emails", getTranslatedString("Emails"), $chk_val];
        }

        if (!empty($sel_fields)) {
            require_once('include/Zend/Json.php');
            $count_sel_fields = count($sel_fields);
            $data_fieldinfo = Zend_Json::encode(["type" => "picklist"]);
            $return_html .= "<select name='s_" . $sfield_name . "' id='s_" . $sfield_name . "' style='display: none;width:85%;' class='select2 row-fluid' data-value='value' name='columnname' data-fieldinfo='$data_fieldinfo' multiple='true' size='5'>";

            $selected_vals = [];

            $r_sel_fields = $request->get("r_sel_fields");
            $default_charset = vglobal("default_charset");
            $r_sel_fields = html_entity_decode($r_sel_fields, ENT_QUOTES, $default_charset);
            $record = $request->get("record");

            if ($r_sel_fields != "") {
                $selected_vals = ITS4YouReports::sanitizeAndExplodeOptions($r_sel_fields);
            } elseif ($record != "") {
                $currField = $request->get("currField");
                $sql = "SELECT value FROM its4you_reports4you_relcriteria WHERE queryid=? AND columnname=?";
                $result = $adb->pquery($sql, [$record, $currField]);
                while ($row = $adb->fetchByAssoc($result)) {
                    $selected_vals = ITS4YouReports::sanitizeAndExplodeOptions($row["value"]);
                }
            }

            if (!empty($uitype_row) && in_array($uitype_row["uitype"], ITS4YouReports::$s_users_uitypes)) {
                $return_html .= '<optgroup label="' . vtranslate('LBL_SPECIAL_OPTIONS') . '">';
                $currentUserOptLbl = vtranslate("Current User");
                if (in_array("Current User", $selected_vals)) {
                    $selected = " selected='selected' ";
                }
                $return_html .= "<option id='0' value='Current User' $selected>$currentUserOptLbl</option>";
                $return_html .= '</optgroup>';

                $return_html .= '<optgroup label="' . vtranslate('LBL_USERS') . '">';
            }

            $n_i = $n_ci = 0;
            $count_n = count($sel_fields);

            foreach ($sel_fields as $key => $sf_array) {

                $sf_text = $sf_array[0];
                $sf_value = html_entity_decode($sf_array[1], ENT_QUOTES, $default_charset);
                $selected = "";
                if ($uitype_row["uitype"] == "56") {
                    $sf_value_str = ($sf_value == '1' ? 'yes' : 'no');
                    if ($sf_array[2] == "selected") {
                        $selected = " selected='selected' ";
                    }
                } else {
                    if (in_array($sf_value, $selected_vals)) {
                        $selected = " selected='selected' ";
                    }
                }
                $return_html .= "<option id='$key' value='$sf_value' $selected>$sf_text</option>";

            }
            // OWNER GROUPS !!!
            if (!empty($uitype_row) && in_array($uitype_row["uitype"], ITS4YouReports::$s_users_uitypes)) {
                if (!empty($picklistGroupValues)) {
                    $return_html .= "</optgroup>
                                            <optgroup label='" . vtranslate('LBL_GROUPS') . "'>";
                    foreach ($picklistGroupValues as $order => $pickListValue) {
                        $pickListValue = trim($pickListValue);
                        if (in_array(trim($pickListValue), array_map("trim", $valueArr))) {
                            $chk_val = "selected";
                            $pickcount++;
                        } else {
                            $chk_val = '';
                        }
                        if ($uitype_row['uitype'] == '56') {
                            $group_fields[] = [vtranslate($pickListValue, $s_module_field_arr[0]), $order, $chk_val];
                        } else {
                            $group_fields[] = [vtranslate($pickListValue, $s_module_field_arr[0]), $pickListValue, $chk_val];
                        }
                    }
                    if ($pickcount == 0 && !empty($value)) {
                        $group_fields[] = [vtranslate('LBL_NOT_ACCESSIBLE'), $value, 'selected'];
                    }
                    foreach ($group_fields as $key => $sf_array) {
                        $sf_text = $sf_array[0];
                        $sf_value = html_entity_decode($sf_array[1], ENT_QUOTES, $default_charset);
                        $selected = "";
                        if ($uitype_row["uitype"] == "56") {
                            $sf_value_str = ($sf_value == '1' ? 'yes' : 'no');
                            if ($sf_array[2] == "selected") {
                                $selected = " selected='selected' ";
                            }
                        } else {
                            if (in_array($sf_value, $selected_vals)) {
                                $selected = " selected='selected' ";
                            }
                        }
                        $return_html .= "<option id='$key' value='$sf_value' $selected>$sf_text</option>";
                    }
                    $return_html .= "</optgroup>";
                }
            }
            $return_html .= "</select>";
            $title_select = vtranslate('LBL_SELECT');
            $title_clear = vtranslate('LBL_CLEAR');
//echo "<pre>";print_r($_REQUEST);echo "</pre>";
            $c_index = trim($_REQUEST["sfield_name"], 'fval');
            $layout = Vtiger_Viewer::getDefaultLayoutName();
            if($layout == "v7"){
                $return_html .= '
                    <span class="add-on relatedPopup cursorPointer"><i id="node3span' . $c_index . '_selects" class="fa fa-search relatedPopup" onclick="" title="' . $title_select . '"></i></span>
                    <span class="add-on cursorPointer"><i id="node3span' . $c_index . '_clear" class="fa fa-remove" onclick="ClearFieldToFilter(' . $c_index . ');" title="' . $title_clear . '"></i></span>
                ';
            } else {
                $return_html .= '
    <span class="add-on relatedPopup cursorPointer">
        <i id="node3span' . $c_index . '_selects" class="icon-search relatedPopup" onClick="" title="' . $title_select . '">
        </i>
    </span>
    <span class="add-on clearReferenceSelection cursorPointer">
        <i id="node3span' . $c_index . '_clears" class="icon-remove-sign" onClick="ClearFieldToFilter(' . $c_index . ');" title="' . $title_clear . '">
        </i>
    </span>';
            }
        }

        echo $return_html;
    }

    function getFilterDateHtml(Vtiger_Request $request) {
        $return_html = "";

        $columnIndex = $request->get("columnIndex");
        if ($columnIndex != "") {

            global $current_user;
            $date_format = $current_user->date_format;
            //$date_format = "dd-mm-yyyy";

            $moduleName = $request->getModule();
            $record = $request->get("record");
            $fop_type = $request->get("fop_type");

            $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
            $rel_fields = $reportModel->getAdvRelFields();

            $r_sel_fields = $request->get("r_sel_fields");

            $ctype = "f";
            $s_date_value = $e_date_value = "";
            if ($r_sel_fields != "") {
                $default_charset = vglobal("default_charset");

                if (in_array($fop_type, ITS4YouReports::$dateNcomparators)) {
                    $std_val_array = GenerateObj::getDateNDaysInterval($fop_type, $r_sel_fields);
                } elseif ($fop_type != "custom") {
                    $std_val_array = GenerateObj::getStandarFiltersStartAndEndDate($fop_type);
                } else {
                    $std_val_array = explode("<;@STDV@;>", html_entity_decode($r_sel_fields, ENT_QUOTES, $default_charset));
                }

                if (in_array($fop_type, ["todayless",])) {
                    $s_date_value = "";
                    if ($std_val_array[0] != "--" && $std_val_array[0] != "") {
                        $e_date_value = $std_val_array[0];
                    } else {
                        $e_date_value = $std_val_array[1];
                    }
                } elseif (in_array($fop_type, ["todaymore", "older1days", "older7days", "older15days", "older30days", "older60days", "older90days", "older120days",])) {
                    $s_date_value = $std_val_array[0];
                    $e_date_value = "";
                } else {
                    $s_date_value = $std_val_array[0];
                    $e_date_value = $std_val_array[1];
                }
            }
            if ($fop_type != "custom") {
                $readonly = "true";
            } else {
                $readonly = "false";
            }

            if ($s_date_value != "") {
                if ($fop_type !== 'custom') {
                	$s_date_value_f = getValidDisplayDate($s_date_value);
                } else {
                	if($layout != "v7"){
						$s_date_value_f = getValidDisplayDate($s_date_value);
					} else {
						$s_date_value_f = $s_date_value;
					}
                }
            }
            if ($e_date_value != "") {
                if ($fop_type !== 'custom') {
                	$e_date_value_f = getValidDisplayDate($e_date_value);
                } else {
                	if($layout != "v7"){
						$e_date_value_f = getValidDisplayDate($e_date_value);
					} else {
						$e_date_value_f = $e_date_value;
					}
                }
            }

            //'<input id="Invoice_editView_fieldName_invoicedate" class="span9 dateField" name="invoicedate" data-date-format="dd-mm-yyyy" value="21-07-2014" data-fieldinfo="{\'mandatory\':false,\'presence\':true,\'quickcreate\':false,\'masseditable\':true,\'defaultvalue\':false,\'type\':\'date\',\'name\':\'invoicedate\',\'label\':\'Invoice Date\',\'date-format\':\'dd-mm-yyyy\'}" type="text">';

            $return_html .= "<div class='row-fluid'>";
            $n_val = "";
            if (in_array($fop_type, ITS4YouReports::$dateNcomparators)) {
                $n_val = $r_sel_fields;
            }
            $layout = Vtiger_Viewer::getDefaultLayoutName();
            $CURRENT_USER = Users_Record_Model::getCurrentUserModel();
            if($layout == "v7"){
                $return_html .= "<div class='col-lg-2' >
                                        <div class='row-fluid input-append' id='div_nfval$columnIndex' style='margin-bottom: 3px;float:left;' >
                                            <input id='nfval$columnIndex' name='nfval$columnIndex' class='inputElement dateField form-control' type='text' value='" . $n_val . "' onchange='showNdayRange($columnIndex,this);' style='width: 50px;float:left;'>
                                        </div>
                                    </div>";
                $return_html .= "
                    <div class='col-lg-4'>
                        <div class='input-group inputElement date' style='min-width:150px;margin-bottom: 3px;float:left;'>
                            <div id='jscal_trigger_sdate" . $columnIndex . "' class='span10 row-fluid date hide'>
                                <input style='width: 100px;' type='text' class='inputElement dateField form-control' id='jscal_field_sdate_val_" . $columnIndex . "' name='startdate' value='$s_date_value_f' 
                                data-date-format='{$CURRENT_USER->date_format}' data-rule-required='true' />
                                <span class='input-group-addon'><i class='fa fa-calendar '></i></span>
                            </div>
                            <input data-value='value' class='span10' name='' id='jscal_field_sdate" . $columnIndex . "' readonly='true' 
                            value='" . $s_date_value_f . "' style='padding-top:0.6em;padding-left:0.6em;'>
                        </div>
                    </div>
                    <div class='col-lg-4'>
                        <div class='input-group inputElement date' style='min-width:150px;margin-bottom: 3px;float:left;'>
                            <div id='jscal_trigger_edate" . $columnIndex . "' class='span10 row-fluid date hide'>
                                <input style='width: 100px;' type='text' class='dateField form-control' id='jscal_field_edate_val_" . $columnIndex . "' name='enddate' value='$e_date_value_f' 
                                data-date-format='{$CURRENT_USER->date_format}' data-rule-required='true' />
                                <span class='input-group-addon'><i class='fa fa-calendar '></i></span>
                            </div>
                            <input data-value='value' class='span10' name='' id='jscal_field_edate" . $columnIndex . "' 
                            readonly='true' value='" . $e_date_value_f . "' style='padding-top:0.6em;padding-left:0.6em;'>
                        </div>
                    </div>
                    ";
            } else {
                $return_html .= "<div class='span1' >
                                        <div class='row-fluid input-append' id='div_nfval$columnIndex' >
                                            <input id='nfval$columnIndex' name='nfval$columnIndex' class='repBox' type='text' value='" . $n_val . "' onchange='showNdayRange($columnIndex,this);' style='width: 50px;float:left;'>
                                        </div>
                                    </div>";
                $return_html .= "       <div class='span5'>
                                                <div class='row-fluid input-append'>
                                                    <div id='jscal_trigger_sdate" . $columnIndex . "' class='span10 row-fluid date hide'>
                                                        <input class='span9 dateField' name='startdate' id='jscal_field_sdate_val_" . $columnIndex . "' 
                                                        data-date-format='" . $CURRENT_USER->date_format . "' maxlength='10' value='" . $s_date_value_f . "' type='text'>
                                                        <span class='add-on'><i class='icon-calendar'></i></span>    
                                                    </div>
                                                    <input data-value='value' class='span10' name='' id='jscal_field_sdate" . $columnIndex . "' readonly='true' 
                                                    value='" . $s_date_value_f . "'>
                                                </div>
                                            </div>
                                            <div class='span5'>
                                                <div class='row-fluid input-append'>
                                                    <div id='jscal_trigger_edate" . $columnIndex . "' class='span10 row-fluid date hide'>
                                                        <input class='span9 dateField' name='enddate' id='jscal_field_edate_val_" . $columnIndex . "' 
                                                        data-date-format='" . $CURRENT_USER->date_format . "' maxlength='10' value='" . $e_date_value_f . "' type='text'>
                                                        <span class='add-on'><i class='icon-calendar'></i></span>
                                                    </div>
                                                    <input data-value='value' class='span10' name='' id='jscal_field_edate" . $columnIndex . "' 
                                                    readonly='true' value='" . $e_date_value_f . "'>
                                                </div>
                                            </div>
                                    </div>";
            }
        }

        echo $return_html;
    }

    function DownloadFile(Vtiger_Request $request) {
        require_once('config.php');
        require_once('include/database/PearDatabase.php');

        $adb = PearDatabase::getInstance();

        $filepath = $request->get("filepath");
        $name = $request->get("filename");

        if ($filepath != "") {
            $filesize = filesize($filepath);
            if (!fopen($filepath, "r")) {
                echo 'unable to open file';
            } else {
                $fileContent = fread(fopen($filepath, "r"), $filesize);
            }
            header("Content-type: $fileType");
            header("Content-length: $filesize");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=$name");
            header("Content-Description: PHP Generated Data");
            echo $fileContent;
        } else {
            echo "Record doesn't exist.";
        }
    }

    function showSettingsList(Vtiger_Request $request) {
        //$ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $viewer->assign('MODULE', $moduleName);

        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode')];
        $linkModels = $moduleModel->getSideBarLinks($linkParams);

        $viewer->assign('QUICK_LINKS', $linkModels);

        $parent_view = $request->get('pview');

        $viewer->assign('CURRENT_PVIEW', $parent_view);

        echo $viewer->view('SettingsList.tpl', 'ITS4YouReports', true);
    }

    function addWidget() {
        $success = false;

        global $adb;
        global $current_user;

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $record = $request->get("record");

        if ($record != "") {
            $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
            $createResult = $reportModel->checkDashboardWidget();
        }

        if ($createResult == "Created") {
            $result = ["success" => false, "message" => vtranslate("LBL_ADD_WIDGET_SUCCESS", "ITS4YouReports")];
        } elseif ($createResult == "Exist") {
            $result = ["success" => false, "message" => vtranslate("LBL_ADD_WIDGET_ERROR_EXIST", "ITS4YouReports")];
        } else {
            $result = ["success" => false, "message" => vtranslate("LBL_ADD_WIDGET_ERROR", "ITS4YouReports")];
        }

        $response = new Vtiger_Response();
        try {
            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();

    }

    // ITS4YOU-CR SlOl 15. 1. 2016 14:25:09 key metrics 
    function getKeyMetricReportColumns(Vtiger_Request $request) {

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $moduleName = $request->getModule();

        $col_options = "";
        $error = 0;
        $record = $request->get("reportid");
        if ($record != "") {
            $type_checkout = explode("_", $record);
            if (count($type_checkout) > 1) {
                $type_name = $type_checkout[0];
                switch ($type_name) {
                    case "cv":
                        $col_options = "<option value='COUNT' $selected >" . vtranslate("LBL_COUNT", $moduleName) . " " . vtranslate("LBL_OF", $moduleName) . " " . vtranslate("LBL_RECORDS", $moduleName) . "</option>";
                        break;
                }
            } else {
                $col_options = ITS4YouReports_Record_Model::getKeyMetricsColumnOptions($record);
            }

            if ('v7' === $layout) {
                if ($col_options != 1) {
                    $result = ["success" => true, "data" => $col_options];
                } else {
                    $result = ["success" => false, "message" => vtranslate("LBL_PERM_DENIED", "ITS4YouReports")];
                }
                $response = new Vtiger_Response();
                try {
                    $response->setResult($result);
                } catch (Exception $e) {
                    $response->setError($e->getCode(), $e->getMessage());
                }
                $response->emit();
            } else {
                if ($col_options != 1) {
                    echo "success<#@#>" . $col_options;
                } else {
                    echo "error<#@#>" . vtranslate("LBL_PERM_DENIED", "ITS4YouReports");
                }
            }
        }

    }

    function deleteKeyMetricRecord(Vtiger_Request $request) {
        $error = 0;
//error_reporting(63);ini_set("display_errors",1);      
        $id = $request->get("id");
        if ($id != "") {
            $adb = PearDatabase::getInstance();
            $row = $adb->fetchByAssoc($adb->pquery("SELECT reportid FROM  its4you_reports4you_key_metrics_rows WHERE id=?", [$id]), 0);
            $record = $row["reportid"];
            if ($record != "") {
                $reportModel = ITS4YouReports_Record_Model::getInstanceById($record);
                if (isset($reportModel->report) && !empty($reportModel->report)) {
                    $adb->pquery("UPDATE its4you_reports4you_key_metrics_rows SET deleted=? WHERE id=?", [1, $id]);
                } else {
                    $error = 1;
                }
            }
        } else {
            $error = 1;
        }

        if ($error != 1) {
            $result = ["success" => true, "message" => vtranslate("LBL_KeyMetricsRow_DELETED", "ITS4YouReports")];
        } else {
            $result = ["success" => false, "message" => vtranslate("LBL_PERM_DENIED", "ITS4YouReports")];
        }

        $response = new Vtiger_Response();
        try {
            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    function saveKeyMetricsOrder(Vtiger_Request $request) {
        // {"module":"ITS4YouReports","parent":"","action":"indexAjax","mode":"saveKeyMetricsOrder","picklistValues":{"1":3,"2":1,"4":2}}
        $done = 0;
        $picklistValues = $request->get('picklistValues');
        if (!empty($picklistValues)) {
            $adb = PearDatabase::getInstance();
            foreach ($picklistValues as $keyMetricsRowId => $keyMetricsRowSequence) {
                $adb->pquery("UPDATE its4you_reports4you_key_metrics_rows SET sequence=? WHERE id=?", [$keyMetricsRowSequence, $keyMetricsRowId]);
            }
            $done = 1;
        }

        if ($done == 1) {
            $result = ["success" => true, "message" => vtranslate("LBL_KeyMetricsRow_SeqDone", "ITS4YouReports")];
        } else {
            $result = ["success" => false, "message" => vtranslate("LBL_PERM_DENIED", "ITS4YouReports")];
        }

        $response = new Vtiger_Response();
        try {
            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    // ITS4YOU-END 15. 1. 2016 14:25:03
    // ITS4YOU-CR SlOl 4. 4. 2016 7:59:40
    function getSchedulerGenerateFor(Vtiger_Request $request) {
        $viewer = new Vtiger_Viewer();
        $moduleName = $request->getModule();

        require_once('modules/ITS4YouReports/ITS4YouReports.php');
        $record = $request->get('record');
        $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);

        $primary_module_id = $request->get('reportmodule');
        $primary_module = vtlib_getModuleNameById($primary_module_id);

        $generateForOptions = ITS4YouReports_EditView_Model::getGenerateForOptionsArray($reportModel, $primary_module);

        //echo "<pre>";print_r($generateForOptions);echo "</pre>";
        $viewer->assign('generateForOptionsArray', $generateForOptions);

        $selectedForOptions = $request->get('selectedGenerateFor');
        $viewer->assign('selectedForOptions', $selectedForOptions);

        echo $viewer->view('generateFor.tpl', $moduleName, true);
    }
    // ITS4YOU-END 
}