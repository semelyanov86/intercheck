<?php

vimport('~~/modules/ITS4YouReports/ITS4YouReports.php');
vimport('~~/modules/ITS4YouReports/Report4YouRun.php');

class ITS4YouReports_Record_Model extends Vtiger_Record_Model {

    public function getITS4YouReport($ogReport = "") {
        if (!isset($ogReport) || $ogReport == "") {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
//            if (ITS4YouReports::isStoredITS4YouReport() === 'atrue') {
            if (ITS4YouReports::isStoredITS4YouReport() === true) {
//ITS4YouReports::sshow("STORED");
                $ogReport = ITS4YouReports::getStoredITS4YouReport();
            }elseif ($request->get("mode") == "getKeyMetricReportColumns"){
//ITS4YouReports::sshow("getKeyMetricReportColumns");
                $ogReport = new ITS4YouReports(true,$request->get("reportid"));
            }elseif ($request->get("view") == "EditKeyMetricsRow" && $request->has("id") && !$request->isEmpty("id")){
//ITS4YouReports::sshow("EditKeyMetricsRow");
                $adb = PearDatabase::getInstance();
                $editResult = $adb->pquery("SELECT reportid FROM its4you_reports4you_key_metrics_rows WHERE id=?",array($request->get("id")));
                if ($adb->num_rows($editResult) > 0) {
                    $row = $adb->fetchByAssoc($editResult,0);
                    $reportid = $row["reportid"];
                }
                $ogReport = new ITS4YouReports(true,$reportid);
            } else {
//ITS4YouReports::sshow("NOT STORED");
                $ogReport = new ITS4YouReports();
            }
        }
        return $ogReport;
    }

    /**
     * Function to get the name of the module to which the record belongs
     * @return <String> - Record Module Name
     */
    public function getModuleName() {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if ($layout == "v7") {
            if(isset($this->module)) {
                $this->module = Vtiger_Module_Model::getInstance('ITS4YouReports');
            }
            $moduleName = vtlib_getModuleNameById($this->module->id);
        } else {
            $moduleName = parent::getModuleName();
        }

        return $moduleName;
    }

    /**
     * Function to get the id of the Report
     * @return <Number> - Report Id
     */
    public function getId() {
        if (isset($this->report->record) && !empty($this->report->record)) {
//echo "<pre>R1</pre>";
            $record = $this->report->record;
        } elseif ($this->get("reportid") != "") {
//echo "<pre>R2</pre>";
            $record = $this->get("reportid");
        } else {
//echo "<pre>R3</pre>";
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $record = $request->get("record");
            $this->setId($record);
        }
//echo "<pre>OLDOK<br/>";print_r($record);echo "</pre>";

        return $record;
        // return $this->get('reportid');
    }

    /**
     * Function to set the id of the Report
     * @param <type> $value - id value
     * @return <Object> - current instance
     */
    public function setId($value) {
        if (isset($this->report->record)) {
            $this->report->record = $value;
        }
        $this->set('reportid', $value);
        return true;
        // return $this->set('reportid', $value);
    }

    /**
     * Fuction to get the Name of the Report
     * @return <String>
     */
    function getName() {
        // return $this->get('reportname');
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has("reportname") && !$request->isEmpty("reportname")) {
            $reportname = $request->get('reportname');
        } else {
            $reportname = $this->report->reportinformations["reports4youname"];
        }
        return $reportname;
    }

    /**
     * Fuction to get the Description of the Report
     * @return <String>
     */
    function getDesc() {
        // return $this->get('reportdesc');
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has("reportdesc") && !$request->isEmpty("reportdesc")) {
            $description = $request->get('reportdesc');
        } else {
            $description = $this->report->reportinformations["description"];
        }
        return $description;
    }

    /**
     * Function deletes the Report
     * @return Boolean
     */
    function delete() {
        $adb = PearDatabase::getInstance();
        //$adb->setDebug(true);
        $params = array(1, $this->getId());
        $adb->pquery("UPDATE its4you_reports4you SET deleted=? WHERE reports4youid=?", $params);

        $widgetUrl = $this->getDashboardWidgetUrl();
        $adb->pquery("DELETE FROM vtiger_links WHERE linkurl =?", array($widgetUrl));

        $homeTabId = getTabid("Home");
        $sql = "SELECT linkid FROM vtiger_links WHERE tabid = ? AND linktype =? AND linkurl =?";
        $linkidResult = $adb->pquery($sql, array($homeTabId, 'DASHBOARDWIDGET', $widgetUrl));
        $linkidNumrows = $adb->num_rows($linkidResult);
        if ($linkidNumrows > 0) {
            $row = $adb->fetchByAssoc($linkidResult);
            $linkid = $row['linkid'];
            $adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE linkid =?", array($linkid));
        }

        return true;
        // return $this->getModule()->deleteRecord($this);
    }

    function getRecordOwner($record) {
        global $adb;
        $sharing_sql = "SELECT owner FROM its4you_reports4you_settings WHERE reportid=?";
        $sharing_result = $adb->pquery($sharing_sql, array($record));
        $sharing = $adb->fetchByAssoc($sharing_result, 0);
        return $sharing["owner"];
    }

    public function getRecordReportType($record) {
        global $adb;
        $reporttype_sql = "SELECT reporttype FROM its4you_reports4you WHERE reports4youid=?";
        $reporttype_result = $adb->pquery($reporttype_sql, array($record));
        $reporttype = $adb->fetchByAssoc($reporttype_result, 0);
        return $reporttype["reporttype"];
    }

    public function getRecordLinks() {

        $links = array();
        $is_editable = $this->isEditable();
        if ($is_editable === true) {
            $recordLinks = array(
                array(
                    'linktype' => 'LISTVIEWRECORD',
                    'linklabel' => '<i class="icon-pencil alignMiddle" title="' . vtranslate("LBL_EDIT", "ITS4YouReports") . '"></i>',
                    'linkurl' => $this->getEditViewUrl(),
                    'linkicon' => 'icon-pencil',
                    'class' => ''
                ),
                array(
                    'linktype' => 'LISTVIEWRECORD',
                    'linklabel' => '<i class="icon-th-list alignMiddle" title="' . vtranslate("LBL_DUPLICATE", "ITS4YouReports") . '"></i>',
                    'linkurl' => $this->getDuplicateRecordUrl(),
                    'linkicon' => 'icon-th-list',
                    'class' => ''
                ),
                array(
                    'linktype' => 'LISTVIEWRECORD',
                    'linklabel' => '<i class="icon-trash alignMiddle" title="' . vtranslate("LBL_DELETE", "ITS4YouReports") . '"></i>',
                    'linkurl' => 'javascript:',
                    'linkicon' => 'icon-trash',
                    'class' => 'deleteRecordButton'
                )
            );
        }

        foreach ($recordLinks as $recordLink) {
            $links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
        }

        return $links;
    }

    /**
     * Function returns the url that delete single Report
     * @return <String>
     */
    function getDeleteSingleReportURL() {
        return "index.php?module=ITS4YouReports&action=DeleteReports4You&record=" . $this->getId();
    }

    /**
     * Function returns the url that generates Report in Excel format
     * @return <String>
     */
    function getReportExcelURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetXLS&record=' . $this->getId();
    }

    /**
     * Function returns the url that generates Report in CSV format
     * @return <String>
     */
    function getReportCSVURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetCSV&record=' . $this->getId();
    }

    /**
     * Function returns the url that generates Report in printable format
     * @return <String>
     */
    function getReportPrintURL() {
        return 'index.php?module=' . $this->getModuleName() . '&view=ExportReport&mode=GetPrintReport&record=' . $this->getId();
    }

    /**
     * Function to get the detail view url
     * @return <String>
     */
    function getDetailViewUrl() {
        return 'index.php?module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getId();
    }

    function getDetailViewUrlForWidgets($moduleName) {
        return 'index.php?module=' . $moduleName . '&view=Detail&record=' . $this->getId();
    }

    /**
     * Function to get the dashboard widget url
     * @return <String>
     */
    function getDashboardWidgetUrl() {
        return ITS4YouReports::$dashboardUrl . $this->getId();
    }

    /**
     * Function to check if homepage widget exist
     * @return true/false
     */
    function checkDashboardWidget($mode = "create") {
        global $current_user;
        global $adb;

        $return = "Exist";

        if (isset($this->report->reportinformations["charts"]) && $this->report->reportinformations["reporttype"] != "tabular") {
            $widgetUrl = $this->getDashboardWidgetUrl();

            $homeTabId = getTabid("Home");
            $sql = "SELECT linkid FROM vtiger_links WHERE tabid = ? AND linktype =? AND linkurl =?";
            $linkidResult = $adb->pquery($sql, array($homeTabId, 'DASHBOARDWIDGET', $widgetUrl));

            $linkidNumrows = $adb->num_rows($linkidResult);
            if ($linkidNumrows > 0) {
                $row = $adb->fetchByAssoc($linkidResult);
                $linkid = $row['linkid'];
            } else {
                $row = $adb->fetchByAssoc($adb->pquery("SELECT MAX(linkid)+1 AS linkid FROM vtiger_links", array()));
                $linkid = $row['linkid'];
                $adb->pquery("UPDATE vtiger_links_seq SET id = ?", array($linkid));
            }

            $sql = "SELECT linkid FROM vtiger_module_dashboard_widgets WHERE userid = ? and linkid = ?";
            $result = $adb->pquery($sql, array($current_user->id, $linkid));

            $numrows = $adb->num_rows($result);
            if ($numrows < 1) {
                if ($mode != "check") {
                    $this->addDashboardWidget($linkid);
                }
                $return = "Created";
            }
        }
        return $return;
    }

    /**
     * Function to ADD homepage widget
     * @return true/false
     */
    function addDashboardWidget($linkid) {
        global $current_user;
        global $adb;

        $widgetPosition = '{"row":"1","col":"1"}';

        $params = array($linkid, $current_user->id);
/*
        $sql = "INSERT INTO vtiger_module_dashboard_widgets (linkid,userid,position) VALUES (?,?,'$widgetPosition')";
        $return = $adb->pquery($sql, $params);
*/
        return $return;
    }

    /**
     * Function to get the edit view url
     * @return <String>
     */
    public function getEditViewUrl() {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $module = $this->getModuleName();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $return_url = "";
        if ($request->get("view") == "Detail") {
            $return_url = "&return_action=Detail";
        }
        return 'index.php?module=' . $module . '&view=Edit&record=' . $this->getId() . $return_url;
    }

    /**
     * Function to get the cancel view url
     * @return <String>
     */
    function getCancelViewUrl() {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $return_url = "";
        $module = $request->get("module");
        if ($request->get("return_action") == "Detail") {
            $cancel_url = '&view=Detail&record=' . $this->getId();
        } else {
            $cancel_url = '&view=List';
        }
        return 'index.php?module=' . $module . $cancel_url;
    }

    /**
     * Funtion to get Duplicate Record Url
     * @return <String>
     */
    public function getDuplicateRecordUrl() {
        $module = $this->getModule();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->get("view") == "Detail") {
            $return_url = "&return_action=Detail";
        }
//echo "<pre>";print_r('index.php?module=' . $this->getModuleName() . '&view=Edit&record=' . $this->getId() . '&isDuplicate=true'.$cancel_url);echo "</pre>";
        return 'index.php?module=' . $this->getModuleName() . '&view=Edit&record=' . $this->getId() . '&isDuplicate=true' . $return_url;
    }

    /**
     * Function returns the Reports Model instance
     * @param <Number> $recordId
     * @param <String> $module
     * @return <Reports_Record_Model>
     */
    public static function getInstanceById($recordId, $module=null) {
        /*
          $db = PearDatabase::getInstance();

          $self = new self();
          $reportResult = $db->pquery('SELECT * FROM its4you_reports4you WHERE reports4youid = ?', array($recordId));
          if ($db->num_rows($reportResult)) {
          $values = $db->query_result_rowdata($reportResult, 0);
          $module = Vtiger_Module_Model::getInstance('ITS4YouReports');

          //echo "<pre>";print_r($self->setData($values)->setId($values['reports4youid'])->setModuleFromInstance($module->get("name")));echo "</pre>";
          $self->setData($values);
          $self->setId($values['reports4youid']);
          $self->setModuleFromInstance($module->get("name"));
          //$self->setData($values)->setId($values['reports4youid'])->setModuleFromInstance($module->get("name"));

          $self->initialize();
          }
         */
        $self = new ITS4YouReports_Record_Model();
        $self->initialize();
        $self->report->record = $recordId;

        return $self;
    }

    /**
     * Function creates Reports_Record_Model
     * @param <Number> $recordId
     * @return <Reports_Record_Model>
     */
    public static function getCleanInstance($recordId = null) {
        /*        if(empty($recordId)) {
          $self = new ITS4YouReports_Record_Model();
          } else {
          $self = self::getInstanceById($recordId);
          }
         */
// oldo
        $self = new ITS4YouReports_Record_Model();
        $self->setId($recordId);
        $self->initialize();

        /*
          $module = Vtiger_Module_Model::getInstance('Reports');
          $self->setModuleFromInstance($module);
         */
        /*
         */
        return $self;
    }

    /**
     * Function initializes Report
     */
    function initialize() {
        $reportId = $this->getId();
//echo "<pre>$reportId</pre>";
//$this->report = ITS4YouReports_ITS4YouReports_Model::getInstance($reportId);
        //$this->report = ITS4YouReports::getStoredITS4YouReport();
        $this->report = $this->getITS4YouReport();

        // $this->report = ITS4YouReports_ITS4YouReports_Model::getInstance($reportId);
    }

    /**
     * Function returns Primary Module of the Report
     * @return <String>
     */
    function getPrimaryModule() {
        // return $this->report->primarymodule;
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has("primarymodule") && !$request->isEmpty("primarymodule") && vtlib_isModuleActive(vtlib_getModuleNameById($request->get('primarymodule')))) {
            $primarymodule = vtlib_getModuleNameById($request->get('primarymodule'));
        } elseif ($request->has("primarymoduleid") && !$request->isEmpty("primarymoduleid") && vtlib_isModuleActive(vtlib_getModuleNameById($request->get('primarymoduleid')))) {
            $primarymodule = vtlib_getModuleNameById($request->get('primarymoduleid'));
        } else {
            $primarymodule = vtlib_getModuleNameById($this->report->reportinformations["primarymodule"]);
        }
        return $primarymodule;
    }

    function getPrimaryModuleId() {
        //return $this->report->primarymoduleid;
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has("primarymodule") && !$request->isEmpty("primarymodule")) {
            $primarymoduleid = $request->get('primarymodule');
        } elseif ($request->has("primarymoduleid") && !$request->isEmpty("primarymoduleid")) {
            $primarymoduleid = $request->get('primarymoduleid');
        } else {
            $primarymoduleid = $this->report->reportinformations["primarymoduleid"];
        }
        return $primarymoduleid;
    }

    /**
     * Function returns Secondary Module of the Report
     * @return <String>
     */
    function getSecondaryModules() {
        return $this->report->secmodule;
    }

    function getSTDSelectedFilter() {
        return $this->report->stdselectedfilter;
    }

    function getSelectedStandardCriteria($reportid) {
        return $this->report->getSelectedStandardCriteria($reportid);
    }

    function getSTDSelectedColumn() {
        return $this->report->stdselectedcolumn;
    }

    /**
     * Function sets the Primary Module of the Report
     * @param <String> $module
     */
    function setPrimaryModule($module) {
        //$this->report->primarymodule = $module;
        $this->report->reportinformations["primarymodule"] = $module;
    }

    /**
     * Function sets the Secondary Modules for the Report
     * @param <String> $modules, modules separated with colon(:)
     */
    function setSecondaryModule($modules) {
        $this->report->secmodule = $modules;
    }

    public function getLabelsHTML($columns_array, $type = "SC", $lbl_url_selected = array()) {
        return $this->report->getLabelsHTML($columns_array, $type, $lbl_url_selected);
        /* $return = array();
          $calculation_type = "";
          if (!empty($columns_array)) {
          foreach ($columns_array as $key => $TP_column_str) {
          $key = ($key);
          $TP_column_str = ($TP_column_str);
          $input_id = $key . "_" . $type . "_lLbLl_" . $TP_column_str;
          if($type=="SM"){
          $TP_column_str_arr = explode(":",$TP_column_str);
          $calculation_type = $TP_column_str_arr[(count($TP_column_str_arr)-1)];
          }
          $translated_lbl_key = $this->getColumnStr_Label($TP_column_str, $type, $lbl_url_selected);
          $translated_key = $this->getColumnStr_Label($TP_column_str, "key", $lbl_url_selected);
          if ($translated_lbl_key!="") {
          if($decode){
          global $default_charset;
          $decoded_translated_lbl_key = htmlspecialchars($translated_lbl_key,ENT_QUOTES,$default_charset);
          }else{
          $decoded_translated_lbl_key = $translated_lbl_key;
          }
          if($calculation_type!="COUNT"){
          $translated_key = $calculation_type." ".$translated_key;
          }
          $translate_html = "<input type='text' id='$input_id' size='50' value='".$decoded_translated_lbl_key."' onblur='checkEmptyLabel(\"$input_id\")'><input type='hidden' id='hidden_$input_id' value='".$decoded_translated_lbl_key."'>";
          $return[$translated_key] = $translate_html;
          }
          }
          }
          return $return; */
    }

    public function getColumnStr_Label($column_str, $type = "SC", $lbl_url_selected = array()) {
        $translated_value = "";
        $adb = PearDatabase::getInstance();

        if ($column_str != "" && $column_str != "none") {
            $column_str = urldecode($column_str);
            global $current_language;
            $col_arr = explode(":", $column_str);
            $calculation_type = "";
            $lbl_arr = explode("_", $col_arr[2], 2);
            $lbl_module = $lbl_arr[0];
            $lbl_value = $lbl_arr[1];
            $lbl_value_sp = $lbl_value;
            if ($type == "SM") {
                // COUNT ... SUM AVG MIN MAX
                if (is_numeric($col_arr[5])) {
                    $calculation_type = $col_arr[6] . " ";
                } else {
                    $calculation_type = $col_arr[5] . " ";
                }
            }
            if (trim($calculation_type) == "COUNT" && strpos($lbl_value, "COUNT") !== false) {
                $calculation_type = "";
            }

            $lbl_mod_strings = array();
            if ($lbl_module != "" && vtlib_isModuleActive($lbl_module)) {
                $lbl_mod_strings = return_module_language($current_language, $lbl_module);
            }
            if (is_array($lbl_url_selected[$type]) && (array_key_exists($column_str, $lbl_url_selected[$type]) || (array_key_exists(html_entity_decode($column_str, ENT_COMPAT, $default_charset), $lbl_url_selected[$type])) && $type != "key")) {
                $translated_value = ($lbl_url_selected[$type][$column_str] != '') ? $lbl_url_selected[$type][$column_str] : $lbl_url_selected[$type][html_entity_decode($column_str, ENT_COMPAT, $default_charset)];
            } else {
                $numlabels = 0;
                if ($type != "key") {
                    $labelsql = "SELECT columnlabel FROM its4you_reports4you_labels WHERE reportid = ? and type = ? AND columnname=?";
                    $labelres = $adb->pquery($labelsql, array($this->record, $type, $column_str));
                    $numlabels = $adb->num_rows($labelres);
                }
                if ($numlabels > 0) {
                    while ($row = $adb->fetchByAssoc($labelres)) {
                        $translated_value = $row["columnlabel"];
                    }
                } else {
                    if ($lbl_value == "campaignrelstatus") {
                        $translated_lbl = vtranslate($lbl_value, $currentModule) . " " . vtranslate($lbl_module, $lbl_module);
                    }
                    if ($lbl_value == "access_count") {
                        $translated_lbl = vtranslate($lbl_value, "Emails");
                    }
                    $translated_lbl = vtranslate($lbl_value, $lbl_module);
                    /*if (array_key_exists($lbl_value, $lbl_mod_strings) && $lbl_mod_strings[$lbl_value] != "") {
                        $translated_lbl = $lbl_mod_strings[$lbl_value];
                    } elseif (array_key_exists($lbl_value_sp, $lbl_mod_strings) && $lbl_mod_strings[$lbl_value_sp] != "") {
                        $translated_lbl = $lbl_mod_strings[$lbl_value_sp];
                    } elseif (!empty($this->app_strings) && array_key_exists($lbl_value, $this->app_strings) && $this->app_strings[$lbl_value] != "") {
                        $translated_lbl = $this->app_strings[$lbl_value];
                    } elseif (!empty($this->app_strings) && array_key_exists($lbl_value_sp, $this->app_strings) && $this->app_strings[$lbl_value_sp] != "") {
                        $translated_lbl = $this->app_strings[$lbl_value_sp];
                    } else {
                        $translated_lbl = $lbl_value;
                    }*/
                    $translated_value = $calculation_type . $translated_lbl;
                }
            }
        }

        return $translated_value;
    }

    /**
     * Function returns Report Type(Summary/Tabular)
     * @return <String>
     */
    function getReportType() {
        return $this->report->reporttype;
    }

    /**
     * Returns the Reports Owner
     * @return <Number>
     */
    function getOwner() {
        //return $this->get('owner');
        return $this->report->reportinformations["owner"];
    }

    /**
     * Function checks if the Report is editable
     * @return boolean
     */
    public function isEditable() {

        $reporttype = ITS4YouReports_Record_Model::getRecordReportType($this->getId());

        $is_editable = false;
        global $current_user;
        if (is_admin($current_user) !== true) {
            $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
            $edit_all = 1;
            if (file_exists($user_privileges_path)) {
                require($user_privileges_path);
                $edit_all = $profileGlobalPermission[1];
            }
            $owner = $this->getRecordOwner($this->getId());
            $subordinate_users = ITS4YouReports::getSubOrdinateUsersArray(true);
        }

        if ($current_user->id == "5") {
            $edit_all = 1;
        }

        if ($reporttype == "custom_report" && is_admin($current_user) !== true) {
            return $is_editable;
        } elseif (is_admin($current_user) === true || $owner == $current_user->id || $edit_all == 0 || in_array($owner, $subordinate_users)) {
            $is_editable = true;
        }
        return $is_editable;
    }

    /**
     * Function returns Report enabled Modules
     * @return type
     */
    function getReportRelatedModulesList() {
//ITS4YouReports::sshow("WTO1");
        if (empty($this->report->related_modules[$this->report->primarymodule])) {
            $this->report->initListOfModules();
        }
//ITS4YouReports::sshow($this->report->related_modules[$this->report->primarymodule]);
        return $this->report->related_modules;
    }

    function getReportRelatedModules($moduleid) {
        return $this->report->getReportRelatedModules($moduleid);
    }

    function getModulesList() {
        return $this->report->getModulesList();
    }

    function getStartDate() {
        return ($this->report->startdate);
    }

    function getEndDate() {
        return ($this->report->enddate);
    }

    function getSelectedStdFilterCriteria($selecteddatefilter = "") {
        return $this->report->getSelectedStdFilterCriteria($selecteddatefilter);
    }

    function getPriModuleColumnsList($primarymodule) {
//ITS4YouReports::sshow("LOLDO $primarymodule");
//ITS4YouReports::sshow($this->module_list);
        return $this->report->getPriModuleColumnsList($primarymodule);
    }

    function getSecModuleColumnsList($secmodid) {
        //$this->report->getSecModuleColumnsList(vtlib_getModuleNameById($secmodid));
        return $this->report->getSecModuleColumnsList($secmodid);
    }

    function getSecondaryModulesList() {
        return $this->report->secondarymodules;
    }

    function getReportInformations() {
        return $this->report->reportinformations;
    }

    function getTimeLineColumnHTML($group_i = "@NMColStr", $tl_col_str = "") {
        return $this->report->getTimeLineColumnHTML($group_i, $tl_col_str);
    }

    function getPrimaryColumns_GroupingHTML($module, $selected = "") {
        global $app_list_strings, $current_language;
        $id_added = false;
        $mod_strings = return_module_language($current_language, $module);

        $block_listed = array();
        $selected = decode_html($selected);
        foreach ($this->report->module_list[$module] as $key => $value) {
            if (isset($this->report->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
                $block_listed[$value] = true;
                $shtml .= "<optgroup label=\"" . $app_list_strings['moduleList'][$module] . " " . vtranslate($value, $module) . "\" class=\"select\" style=\"border:none\">";
                if ($id_added == false) {
                    $is_selected = '';
                    if ($selected == "vtiger_crmentity:crmid:" . $module . "_ID:crmid:I") {
                        $is_selected = 'selected';
                    }
                    $shtml .= "<option value=\"vtiger_crmentity:crmid:" . $module . "_ID:crmid:I\" {$is_selected}>" .
                        vtranslate($module, $module) . ' ' . vtranslate('ID', $module) .
                        "</option>";
                    $id_added = true;
                }
                foreach ($this->report->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
                    $shtml .= "<option value=\"" . $field . "\"";
                    if ($selected == decode_html($field))
                        $shtml .= " selected ";
                    $shtml .= ">" . vtranslate($fieldlabel, $module) . "</option>";
                }
            }
        }
        return $shtml;
    }

    /**
     * Function returns Primary Module Fields
     * @return <Array>
     */
    function getPrimaryModuleFields() {
        $report = $this->report;
        $primaryModule = $this->getPrimaryModule();
        $report->getPriModuleColumnsList($primaryModule);
        //need to add this vtiger_crmentity:crmid:".$module."_ID:crmid:I
        return $report->pri_module_columnslist;
    }

    /**
     * Function returns Secondary Module fields
     * @return <Array>
     */
    function getSecondaryModuleFields() {
        $report = $this->report;
        $secondaryModule = $this->getSecondaryModules();
        $report->getSecModuleColumnsList($secondaryModule);
        return $report->sec_module_columnslist;
    }

    /**
     * Function returns Report Selected Fields
     * @return <Array>
     */
    function getSelectedFields() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery("SELECT its4you_reports4you_selectcolumn.columnname FROM  its4you_reports4you
                                    INNER JOIN its4you_reports4you_selectquery ON its4you_reports4you_selectquery.queryid =  its4you_reports4you.queryid
                                    INNER JOIN  its4you_reports4you_selectcolumn ON  its4you_reports4you_selectcolumn.queryid = its4you_reports4you_selectquery.queryid
                                    WHERE  its4you_reports4you.reports4youid = ? ORDER BY  its4you_reports4you_selectcolumn.columnindex", array($this->getId()));

        $selectedColumns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            list($tableName, $columnName, $moduleFieldLabel, $fieldName, $type) = preg_split('/:/', $column);
            $fieldLabel = explode('_', $moduleFieldLabel);
            $module = $fieldLabel[0];
            $dbFieldLabel = trim(str_replace(array($module, '_'), " ", $moduleFieldLabel));
            $translatedFieldLabel = vtranslate($dbFieldLabel, $module);
            if (CheckFieldPermission($fieldName, $module) == 'true' && $columnName != 'crmid') {
                $selectedColumns[$translatedFieldLabel] = $column;
            }
        }
        return $selectedColumns;
    }

    /**
     * Function returns Report Calculation Fields
     * @return type
     */
    function getSelectedCalculationFields() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT vtiger_reportsummary.columnname FROM vtiger_reportsummary
                                    INNER JOIN  its4you_reports4you ON  its4you_reports4you.reports4youid = vtiger_reportsummary.reportsummaryid
                                    WHERE  its4you_reports4you.reports4youid=?', array($this->getId()));

        $columns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $columns[] = $db->query_result($result, $i, 'columnname');
        }
        return $columns;
    }

    /**
     * Function returns Report Sort Fields
     * @return type
     */
    function getSelectedSortFields() {
        $db = PearDatabase::getInstance();

        //TODO : handle date fields with group criteria
        $result = $db->pquery('SELECT its4you_reports4you_sortcol.* FROM  its4you_reports4you
                                    INNER JOIN its4you_reports4you_sortcol ON  its4you_reports4you.reports4youid = its4you_reports4you_sortcol.reportid
                                    WHERE  its4you_reports4you.reports4youid = ? ORDER BY its4you_reports4you_sortcol.sortcolid', array($this->getId()));

        $sortColumns = array();
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $column = $db->query_result($result, $i, 'columnname');
            $order = $db->query_result($result, $i, 'sortorder');
            $sortColumns[$column] = $order;
        }
        return $sortColumns;
    }

    /**
     * Function returns Reports Standard Filters
     * @return type
     */
    function getSelectedStandardFilter() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid = ? AND startdate != ? AND enddate != ?', array($this->getId(), '0000-00-00', '0000-00-00'));
        $standardFieldInfo = array();
        if ($db->num_rows($result)) {
            $standardFieldInfo['columnname'] = $db->query_result($result, 0, 'datecolumnname');
            $standardFieldInfo['type'] = $db->query_result($result, 0, 'datefilter');
            $standardFieldInfo['startdate'] = $db->query_result($result, 0, 'startdate');
            $standardFieldInfo['enddate'] = $db->query_result($result, 0, 'enddate');

            if ($standardFieldInfo['type'] == "custom" || $standardFieldInfo['type'] == "") {
                if ($standardFieldInfo["startdate"] != "0000-00-00" && $standardFieldInfo["startdate"] != "") {
                    $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                }
                if ($standardFieldInfo["enddate"] != "0000-00-00" && $standardFieldInfo["enddate"] != "") {
                    $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                    $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
                }
            } else {
                $startDateTime = new DateTimeField($standardFieldInfo["startdate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["startdate"] = $startDateTime->getDisplayDate();
                $endDateTime = new DateTimeField($standardFieldInfo["enddate"] . ' ' . date('H:i:s'));
                $standardFieldInfo["enddate"] = $endDateTime->getDisplayDate();
            }
        }

        return $standardFieldInfo;
    }

    /**
     * Function returns Reports Advanced Filters
     * @return type
     */
    function getSelectedAdvancedFilter() {
        $report = $this->report;
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $report->getAdvancedFilterList($this->report->record);
        if ($request->has("reload")) {
            //$sel_fields = Zend_Json::encode($this->report->adv_sel_fields);
            //$tmp = $this->report->getAdvanceFilterOptionsJSON($this->report->primarymodule);
            $sel_fields = $this->report->adv_sel_fields;
            $criteria_groups = $this->report->getRequestCriteria($sel_fields);
        } else {
            $criteria_groups = $this->report->advft_criteria;
        }
        return $criteria_groups;
    }

    /**
     * Function saves a Report
     */
    function save() {
        $adb = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $layout = Vtiger_Viewer::getDefaultLayoutName();

        $export_sql = false;
        //$export_sql = true;

        $export_to_file = false;
        //$export_to_file = true;

        $debug_save = false;
        //$debug_save = true;

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $request_data = $request->getAll();

        if ($export_sql) {
            ITS4YouReports::sshow($_REQUEST);
//exit;
        }

        $reportid = $this->getId();

        $ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();

        $r4u_sesstion_name = ITS4YouReports::getITS4YouReportStoreName();
        $r4u_sesstion_unset = ITS4YouReports::unsetITS4YouReportsSerialize($r4u_sesstion_name);
        if ($debug_save) {
            echo "<pre>UNSET Session: ";
            print_r($r4u_sesstion_unset);
            echo "</pre>";
        }

        $std_filter_columns = $ITS4YouReports->getStdFilterColumns();

        if($debug_save){
            $adb->setDebug(true);
        }

        //<<<<<<<report>>>>>>>>>
        global $default_charset;
        $reportname = $request->get('reportname');
        $reportname = html_entity_decode($reportname, ENT_QUOTES, $default_charset);
        $reportdescription = $request->get("reportdesc");
        $reportdescription = html_entity_decode($reportdescription, ENT_QUOTES, $default_charset);
        $reporttype = $request->get("reporttype");
        $folderid = $request->get("reportfolder");
        $isDuplicate = $request->get("isDuplicate");
        //<<<<<<<report>>>>>>>>>
        //<<<<<<<selectcolumn>>>>>>>>>
        $selectedcolumnstring = $request->get("selectedColumnsString");
        $selectedcolumnstring = str_replace("@AMPKO@", "&", $selectedcolumnstring);
        $saveselectedcolumns = explode(";", trim($selectedcolumnstring, ";"));
        $selectedcolumns = array();
        foreach ($saveselectedcolumns AS $sc) {
            $selectedcolumns[] = $sc;
        }
        //<<<<<<<selectcolumn>>>>>>>>>
        //<<<<<<<selectedSummaries>>>>>>>>>
        $selectedSummariesString = $request->get("selectedSummariesString");
        $selectedSummariesString = str_replace("@AMPKO@", "&", $selectedSummariesString);
        $selectedSummaries_array = explode(";", trim($selectedSummariesString, ";"));
        $selectedSummaries = array();
        foreach ($selectedSummaries_array AS $sm) {
            $selectedSummaries[] = $sm;
        }
        //<<<<<<<selectedSummaries>>>>>>>>>
        //<<<<<<<SummariesOrderBy>>>>>>>>>
        $summaries_orderby = $request->get("summaries_orderby_columnString");
        $summaries_orderby_type = $request->get("summaries_orderby_type");
        //<<<<<<<SummariesOrderBy>>>>>>>>>
        // ITS4YOU-CR SlOl 13. 3. 2014 10:17:31
        $lbl_array = array();
        $lbl_url_string = $request->get("labels_to_go");
        $lbl_url_string = str_replace("@AMPKO@", "&", $lbl_url_string);
        $lbl_url_string = urldecode($lbl_url_string);
        $lbl_url_string = html_entity_decode($lbl_url_string, ENT_QUOTES, $default_charset);
        if ($lbl_url_string != "") {
            $lbl_url_arr = explode('$_@_$', $lbl_url_string);
            foreach ($lbl_url_arr as $key => $lbl_value) {
                if (strpos($lbl_value, '_SC_lLbLl_') !== false) {
                    $temp = explode('_SC_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["SC"][$lbl_key] = $lbl_value;
                }
                if (strpos($lbl_value, '_SM_lLbLl_') !== false) {
                    $temp = explode('_SM_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["SM"][$lbl_key] = $lbl_value;
                }

                if (strpos($lbl_value, '_CT_lLbLl_') !== false) {
                    $temp = explode('_CT_lLbLl_', $lbl_value);
                    $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                    $lbl_key = $temp_lbls[0];
                    $lbl_value = $temp_lbls[1];
                    $lbl_array["CT"][$lbl_key] = $lbl_value;
                }
            }
        }
        // ITS4YOU-END 13. 3. 2014 10:17:32
        //<<<<<<<reportsortcol>>>>>>>>>
        $sort_by1 = decode_html($request->get("Group1"));
        $sort_order1 = $request->get("Sort1");
        $sort_by2 = decode_html($request->get("Group2"));
        $sort_order2 = $request->get("Sort2");
        $sort_by3 = decode_html($request->get("Group3"));
        $sort_order3 = $request->get("Sort3");

        $timeline_type2 = $request->get("timeline_type2");
        $timeline_type3 = $request->get("timeline_type3");

        if ($request->has("TimeLineColumn_Group1") && !$request->isEmpty("TimeLineColumn_Group1") && $sort_by1 != "none") {
            $TimeLineColumn_Group1 = $request->get("TimeLineColumn_Group1");
            $TimeLineColumn_Group1_arr = explode("@vlv@", $TimeLineColumn_Group1);
            $TimeLineColumn_str1 = $TimeLineColumn_Group1_arr[0];
            $TimeLineColumn_frequency1 = $TimeLineColumn_Group1_arr[1];
        }
        if ($request->has("TimeLineColumn_Group2") && !$request->isEmpty("TimeLineColumn_Group2") && $sort_by2 != "none") {
            $TimeLineColumn_Group2 = $request->get("TimeLineColumn_Group2");
            $TimeLineColumn_Group2_arr = explode("@vlv@", $TimeLineColumn_Group2);
            $TimeLineColumn_str2 = $TimeLineColumn_Group2_arr[0];
            $TimeLineColumn_frequency2 = $TimeLineColumn_Group2_arr[1];
        }
        if ($request->has("TimeLineColumn_Group3") && !$request->isEmpty("TimeLineColumn_Group3") && $sort_by3 != "none") {
            $TimeLineColumn_Group3 = $request->get("TimeLineColumn_Group3");
            $TimeLineColumn_Group3_arr = explode("@vlv@", $TimeLineColumn_Group3);
            $TimeLineColumn_str3 = $TimeLineColumn_Group3_arr[0];
            $TimeLineColumn_frequency3 = $TimeLineColumn_Group3_arr[1];
        }
        // ITS4YOU-UP SlOl 12. 5. 2016 14:12:56
        $sort_by_column = decode_html($request->get("SortByColumn"));
        $sort_order_column = $request->get("SortOrderColumn");

        $sort_by_array = array();
        $scolrow_n = $request->get("scolrow_n");
        $requestAll = $request->getAll();
        if($scolrow_n>0){
            for ($sci=1;$sci<=$scolrow_n;$sci++){
                $SortOrderColumnName = "SortByColumn$sci";
                $SortOrderValue = "SortOrderColumn$sci";
                if(array_key_exists($SortOrderColumnName,$requestAll)){
                    if($requestAll[$SortOrderColumnName]!="" && $requestAll[$SortOrderColumnName]!="none"){
                        $sbColumn = $requestAll[$SortOrderColumnName];
                        $sbOrder = $requestAll[$SortOrderValue];
                        if($sbOrder==""){
                            $sbOrder = "ASC";
                        }
                        $sort_by_array[] = array($sbColumn,$sbOrder);
                    }
                }
            }
        }
        // ITS4YOU-END
        //<<<<<<<reportsortcol>>>>>>>>>
        //<<<<<<<reportmodules>>>>>>>>>
        $pmodule = $request->get("primarymodule");
        $smodule = trim($request->get("secondarymodule"), ":");
        //<<<<<<<reportmodules>>>>>>>>>
        //<<<<<<<shared entities>>>>>>>>>
        $sharetype = $request->get("sharing");
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if($layout == "v7"){
            $shared_entities = $request->get('recipientsString_v7');
        } else {
            $shared_entities = $request->get("sharingSelectedColumnsString");
        }
        //<<<<<<<shared entities>>>>>>>>>
        //<<<<<<<columnstototal>>>>>>>>>>
        if ($request->has("curl_to_go") && !$request->isEmpty("curl_to_go")) {
            $columnstototal = explode('$_@_$', $request->get("curl_to_go"));
        }
        //<<<<<<<columnstototal>>>>>>>>>
        //<<<<<<<advancedfilter>>>>>>>>check
        $json = new Zend_Json();

        $std_filter_columns = $ITS4YouReports->getStdFilterColumns();

        $advft_criteria_groups = $request->get('advft_criteria_groups');
        $new_criteria_groups = $gi_change = array();
        if (!empty($advft_criteria_groups)) {
            $new_gi = 1;
            foreach ($advft_criteria_groups as $advftg_i => $advftg_array) {
                if (!empty($advftg_array)) {
                    $gi_change[$advftg_i] = $new_gi;
                    $new_criteria_groups[$new_gi] = $advftg_array;
                    $new_gi++;
                }
            }
        }
        $advft_criteria_groups = $new_criteria_groups;
        //advft_criteria_groups = $json->decode($advft_criteria_groups);

        $new_advft_criteria = array();
        $advft_criteria = $request->get('advft_criteria');
        if (!empty($advft_criteria)) {
            $new_i = 0;
            foreach ($advft_criteria as $advft_i => $advft_array) {
                if (!empty($advft_array)) {
                    $advft_array["groupid"] = $gi_change[$advft_array["groupid"]];
                    $new_advft_criteria[$new_i] = $advft_array;
                    $new_i++;
                }
            }
        }
        $advft_criteria = $new_advft_criteria;
        //$advft_criteria = $json->decode($advft_criteria);
        //<<<<<<<advancedfilter>>>>>>>>
        //<<<<<<<groupconditioncolumn>>>>>>>>
        $groupft_criteria = $request->get('groupft_criteria');
        //$groupft_criteria = $json->decode($groupft_criteria);
        //<<<<<<<groupconditioncolumn>>>>>>>>
        //<<<<<<<limit>>>>>>>>
        $limit = $summaries_limit = 0;
        if ($request->has("columns_limit") && !$request->isEmpty("columns_limit")) {
            $limit = $request->get("columns_limit");
        }
        if ($request->has("summaries_limit") && !$request->isEmpty("summaries_limit")) {
            $summaries_limit = $request->get("summaries_limit");
        }
        //<<<<<<<limit>>>>>>>>
        //<<<<<<<quick filters>>>>>>>>
        $qfColumns = array();
        $quick_filters_save = $request->get('quick_filters_save');
        if ('' !== $quick_filters_save) {
            $qfColumns = explode(',', $quick_filters_save);
        }
        //<<<<<<<quick filters>>>>>>>>
        //<<<<<<<scheduled report>>>>>>>>
        $isReportScheduled = $request->get('isReportScheduled');
        $scheduledFormat = "";
        $r_key_arr = array();
        //if ($isReportScheduled == 'on' || $isReportScheduled == '1') {
        foreach ($request_data as $r_key => $r_val) {
            if (strpos($r_key, "scheduledReportFormat_") !== false) {
                $r_key_arr[] = substr($r_key, 22);
            }
        }
        //}
        if (!empty($r_key_arr)) {
            $scheduledFormat = implode(";", $r_key_arr);
        }
        if($layout == "v7"){
            $selectedRecipients = $request->get('selectedRecipientsString_v7');
        } else {
            $selectedRecipients = $request->get('selectedRecipientsString');
        }
        $scheduledIntervalArr = $request->get('scheduledIntervalString');
        $selectedRecipients = Zend_JSON::encode($selectedRecipients);
        $scheduledInterval = Zend_JSON::encode($scheduledIntervalArr);
        // ITS4YOU-CR SlOl 10. 5. 2016 8:16:17 - set next triget time by defined interval in Scheduler Tab
        global $current_user;
        require_once 'modules/ITS4YouReports/ScheduledReports4You.php';
        $scheduledReport = new ITS4YouScheduledReport($adb, $current_user, '');
        $scheduledReport->scheduledInterval = $scheduledIntervalArr;
        $next_trigger_time = $scheduledReport->getNextTriggerTime();

        // ITS4YOU-CR SlOl 4. 4. 2016 11:24:20
        $generate_other = $request->get("generate_other");
        $generate_subject = $request->get("generate_subject");
        $generate_text = $request->get("generate_text");
        $schedule_all_records = 0;
        if($request->get("schedule_all_records")=="on"){
            $schedule_all_records = 1;
        }

        $GenerateFor = $request->get("selectedGenerateForString");

        // ITS4YOU-END
        //<<<<<<<scheduled report>>>>>>>>
        // ITS4YOU-CR SlOl 20. 3. 2014 12:02:47
        for ($tg_i = 1; $tg_i < 4; $tg_i++) {
            if ($request->has("TimeLineColumn_Group$tg_i") && !$request->isEmpty("TimeLineColumn_Group$tg_i") && $request->get("TimeLineColumn_Group$tg_i") != "none") {
                $tg_col_str = $request->get("TimeLineColumn_Group$tg_i");
                $tg_col_arr = explode("@vlv@", $tg_col_str);
                $timelinecols_arr[$tg_i] = $tg_col_str;
                $timelinecols_frequency[$tg_i] = $tg_col_arr[1];
            }
        }
        // ITS4YOU-END 20. 3. 2014 12:02:48
        // ITS4YOU-CR SlOl | 2.7.2014 15:18
        // chartType1 data_series1
        if ($request->has("chartType1") && !$request->isEmpty("chartType1")) {
            $charttitle = $request->get("charttitle");
            $x_group = $request->get("x_group");
            $chart_position = $request->get('chart_position');
            $collapse_data_block = $request->get('collapse_data_block');
            $charts = array();
            for ($chi = 1; $chi < 4; $chi++) {
                // charttype 	dataseries 	charttitle 	chart_seq 	x_group
                if ($request->has("chartType$chi") && !$request->isEmpty("chartType$chi")) {
                    $charttype = $request->get("chartType$chi");
                    if ($charttype != "none") {
                        $ch_params = array("charttype" => $charttype,
                            "dataseries" => $request->get("data_series$chi"),
                            "charttitle" => $charttitle,
                            "chart_seq" => $chi,
                            "x_group" => $x_group,
                            'chart_position' => $chart_position,
                            'collapse_data_block' => $collapse_data_block,
                        );
                        $charts[$chi] = $ch_params;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
        }
        // ITS4YOU-END 2.7.2014 15:18
        // ITS4YOU-CR SlOl | 20.8.2015 11:30
        if ($reporttype == "custom_report") {
            $customSql = ITS4YouReports::validateCustomSql($request->get("reportcustomsql"));
        }
        // ITS4YOU-CR SlOl 18. 3. 2016 13:09:57
        $primary_search = $request->get("primary_search");
        // ITS4YOU-CR SlOl 21. 3. 2016 10:32:27
        $allow_in_modules_hidden = $request->get("allow_in_modules_hidden");
        // ITS4YOU-END

        $maps = $request->has('maps') ? $request->get('maps') : [];

        // ITS4YOU-END 20.8.2015 11:30
        if ($reportid != "" && $isDuplicate != "true") {
            $d_selectedcolumns = "DELETE FROM its4you_reports4you_selectcolumn WHERE its4you_reports4you_selectcolumn.queryid = ?";
            $d_columnsqlresult = $adb->pquery($d_selectedcolumns, array($reportid));
            if (!empty($selectedcolumns)) {
                for ($i = 0; $i < count($selectedcolumns); $i++) {
                    if (!empty($selectedcolumns[$i])) {
                        $icolumnsql = "INSERT INTO its4you_reports4you_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) VALUES (?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $i, (decode_html($selectedcolumns[$i])));
                        $params_tofile = array("@reportid", $i, (decode_html($selectedcolumns[$i])));
                        $report_array['its4you_reports4you_selectcolumn'][] = $params_tofile;
                        $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                }
            }

            // ITS4YOU-CR SlOl 7. 3. 2014 11:24:46 Summaries Save
            $d_selectedSummaries = "DELETE FROM its4you_reports4you_summaries WHERE reportsummaryid = ?";
            $d_Summariessqlqfresult = $adb->pquery($d_selectedSummaries, array($reportid));
            if (!empty($selectedSummaries)) {
                for ($i = 0; $i < count($selectedSummaries); $i++) {
                    if (!empty($selectedSummaries[$i])) {
                        $iSmmariessql = "INSERT INTO its4you_reports4you_summaries (reportsummaryid,summarytype,columnname) VALUES (?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $i, (decode_html($selectedSummaries[$i])));
                        $params_tofile = array("@reportid", $i, (decode_html($selectedSummaries[$i])));
                        $report_array['its4you_reports4you_summaries'][] = $params_tofile;
                        $iSummariessqlresult = $adb->pquery($iSmmariessql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                }
            }
            // ITS4YOU-END 7. 3. 2014 11:24:48
            // ITS4YOU-CR SlOl 24. 3. 2014 8:52:40
            $d_selected_s_orderby = "DELETE FROM its4you_reports4you_summaries_orderby WHERE reportid = ?";
            $d_selected_s_orderbyresult = $adb->pquery($d_selected_s_orderby, array($reportid));
            if ($summaries_orderby != "" && $summaries_orderby_type != "") {
                $d_selected_s_orderby_sql = "INSERT INTO  its4you_reports4you_summaries_orderby (reportid,columnindex,summaries_orderby,summaries_orderby_type) VALUES (?,?,?,?)";
                $export_sql === true ? $adb->setDebug(true) : "";
                $params = array($reportid, 0, $summaries_orderby, $summaries_orderby_type);
                $params_tofile = array("@reportid", 0, $summaries_orderby, $summaries_orderby_type);
                $report_array['its4you_reports4you_summaries_orderby'] = $params_tofile;
                $d_selected_s_orderby_result = $adb->pquery($d_selected_s_orderby_sql, $params);
                $export_sql === true ? $adb->setDebug(false) : "";
            }
            // ITS4YOU-END 24. 3. 2014 8:52:42
            // ITS4YOU-CR SlOl 13. 3. 2014 11:34:24
            $d_selectedLabels = "DELETE FROM its4you_reports4you_labels WHERE reportid = ?";
            $d_Labelssqlqfresult = $adb->pquery($d_selectedLabels, array($reportid));
            if (!empty($lbl_array)) {
                foreach ($lbl_array as $type => $type_array) {
                    foreach ($type_array as $column_str => $column_lbl) {
                        $iLabelssql = "INSERT INTO its4you_reports4you_labels (reportid,type,columnname,columnlabel) VALUES (?,?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $type, $column_str, $column_lbl);
                        $params_tofile = array("@reportid", $type, $column_str, $column_lbl);
                        $report_array['its4you_reports4you_labels'][] = $params_tofile;
                        $iLabelssqlresult = $adb->pquery($iLabelssql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                }
            }

            $d_selectedqfcolumns = "DELETE FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
            $d_columnsqlqfresult = $adb->pquery($d_selectedqfcolumns, array($reportid));
            if (!empty($selectedqfcolumns)) {
                for ($i = 0; $i < count($selectedqfcolumns); $i++) {
                    if (!empty($selectedqfcolumns[$i])) {
                        $icolumnsql = "INSERT INTO its4you_reports4you_selectqfcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) VALUES (?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $i, (decode_html($selectedqfcolumns[$i])));
                        $params_tofile = array("@reportid", $i, (decode_html($selectedqfcolumns[$i])));
                        $report_array['its4you_reports4you_selectqfcolumn'][] = $params_tofile;
                        $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                }
            }

            $d_shared = "DELETE FROM its4you_reports4you_sharing WHERE reports4youid = ?";
            $d_sharedresult = $adb->pquery($d_shared, array($reportid));
            if ($shared_entities != "") {
                if ($sharetype == "share") {
                    $selectedsharecolumn = explode("|", $shared_entities);
                    for ($i = 0; $i < count($selectedsharecolumn) - 1; $i++) {
                        $temp = preg_split('/::/', $selectedsharecolumn[$i]);
                        $icolumnsql = "INSERT INTO its4you_reports4you_sharing (reports4youid,shareid,setype) VALUES (?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $temp[1], $temp[0]);
                        $params_tofile = array("@reportid", $temp[1], $temp[0]);
                        $report_array['its4you_reports4you_sharing'][] = $params_tofile;
                        $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                }
            }

            if ($reportid != "") {
                $ireportsql = "UPDATE its4you_reports4you SET reports4youname=?, description=?, folderid=?, reporttype=?, columns_limit=?, summaries_limit=? WHERE reports4youid=?";
                $ireportparams = array($reportname, $reportdescription, $folderid, $reporttype, $limit, $summaries_limit, $reportid);
                $params_tofile = array("@reportid", $reportname, $reportdescription, $folderid, $reporttype, 0, $limit, $summaries_limit);
                // ITS4YOU-END
                $export_sql === true ? $adb->setDebug(true) : "";
                $report_array['its4you_reports4you'] = $params_tofile;
                $ireportresult = $adb->pquery($ireportsql, $ireportparams);
                $export_sql === true ? $adb->setDebug(false) : "";
                if ($ireportresult != false) {
                    if ($reporttype != "custom_report") {
                        //<<<<reportmodules>>>>>>>
                        $d_modules = "DELETE FROM its4you_reports4you_modules WHERE reportmodulesid = ?";
                        $d_modulesresult = $adb->pquery($d_modules, array($reportid));
                        $ireportmodulesql = "INSERT INTO its4you_reports4you_modules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) VALUES (?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $pmodule, $smodule);
                        $params_tofile = array("@reportid", $pmodule, $smodule);
                        $report_array['its4you_reports4you_modules'] = $params_tofile;
                        $ireportmoduleresult = $adb->pquery($ireportmodulesql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                        //<<<<reportmodules>>>>>>>
                        //<<<<step3 its4you_reports4you_sortcol>>>>>>>
                        $d_sortcol1 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
                        $d_sortcol1_result = $adb->pquery($d_sortcol1, array($reportid, 1));
                        if ($sort_by1 != "") {
                            $sort_by1sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?,?)";
                            $export_sql === true ? $adb->setDebug(true) : "";
                            $params = array(1, 0, $reportid, $sort_by1, $sort_order1, $TimeLineColumn_str1, $TimeLineColumn_frequency1);
                            $params_tofile = array(1, 0, "@reportid", $sort_by1, $sort_order1, "rows", $TimeLineColumn_str1, $TimeLineColumn_frequency1);
                            $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                            $sort_by1result = $adb->pquery($sort_by1sql, $params);
                            $export_sql === true ? $adb->setDebug(false) : "";
                        }
                        $d_sortcol2 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
                        $d_sortcol2_result = $adb->pquery($d_sortcol2, array($reportid, 2));
                        if ($sort_by2 != "") {
                            $sort_by2sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?,?,?)";
                            $export_sql === true ? $adb->setDebug(true) : "";
                            $params = array(2, 0, $reportid, $sort_by2, $sort_order2, $timeline_type2, $TimeLineColumn_str2, $TimeLineColumn_frequency2);
                            $params_tofile = array(2, 0, "@reportid", $sort_by2, $sort_order2, $timeline_type2, $TimeLineColumn_str2, $TimeLineColumn_frequency2);
                            $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                            $sort_by2result = $adb->pquery($sort_by2sql, $params);
                            $export_sql === true ? $adb->setDebug(false) : "";
                        }

                        $d_sortcol3 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
                        $d_sortcol3_result = $adb->pquery($d_sortcol3, array($reportid, 3));
                        if ($sort_by3 != "") {
                            $sort_by3sql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) VALUES (?,?,?,?,?,?,?,?)";
                            $export_sql === true ? $adb->setDebug(true) : "";
                            $params = array(3, 0, $reportid, $sort_by3, $sort_order3, $timeline_type3, $TimeLineColumn_str3, $TimeLineColumn_frequency3);
                            $params_tofile = array(3, 0, "@reportid", $sort_by3, $sort_order3, $timeline_type3, $TimeLineColumn_str3, $TimeLineColumn_frequency3);
                            $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                            $sort_by3result = $adb->pquery($sort_by3sql, $params);
                            $export_sql === true ? $adb->setDebug(false) : "";
                        }

                        $d_sortcol4 = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid=?";
                        $d_sortcol4_result = $adb->pquery($d_sortcol4, array($reportid, 4));
                        if(!empty($sort_by_array)){
                            $sci = 1;
                            foreach($sort_by_array as $sort_col_row){
                                $sort_by_columnsql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER) VALUES (?,?,?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array(4, $sci, $reportid, $sort_col_row[0], $sort_col_row[1]);
                                $params_tofile = array(4, $sci, "@reportid", $sort_col_row[0], $sort_col_row[1], "", "", "");
                                $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                                $sort_by_columnresult = $adb->pquery($sort_by_columnsql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                                $sci++;
                            }
                        }
                        //<<<<step3 its4you_reports4you_sortcol>>>>>>>
                        //<<<<step5 standarfilder>>>>>>>
                        $d_datefilter = "DELETE FROM its4you_reports4you_datefilter WHERE datefilterid = ?";
                        $d_datefilter_result = $adb->pquery($d_datefilter, array($reportid));
                        $ireportmodulesql = "INSERT INTO its4you_reports4you_datefilter (DATEFILTERID,DATECOLUMNNAME,DATEFILTER,STARTDATE,ENDDATE) VALUES (?,?,?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $stdDateFilterField, $stdDateFilter, $startdate, $enddate);
                        $params_tofile = array("@reportid", $stdDateFilterField, $stdDateFilter, $startdate, $enddate);
                        $report_array['its4you_reports4you_datefilter'] = $params_tofile;
                        $ireportmoduleresult = $adb->pquery($ireportmodulesql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                        //<<<<step5 standarfilder>>>>>>>
                        //<<<<step4 columnstototal>>>>>>>
                        $d_summary = "DELETE FROM its4you_reports4you_summary WHERE reportsummaryid = ?";
                        $d_summary_result = $adb->pquery($d_summary, array($reportid));
                        for ($i = 0; $i < count($columnstototal); $i++) {
                            $ireportsummarysql = "INSERT INTO its4you_reports4you_summary (REPORTSUMMARYID,SUMMARYTYPE,COLUMNNAME) VALUES (?,?,?)";
                            $export_sql === true ? $adb->setDebug(true) : "";
                            $params = array($reportid, $i, $columnstototal[$i]);
                            $params_tofile = array("@reportid", $i, $columnstototal[$i]);
                            $report_array['its4you_reports4you_summary'][] = $params_tofile;
                            $ireportsummaryresult = $adb->pquery($ireportsummarysql, $params);
                            $export_sql === true ? $adb->setDebug(false) : "";
                        }
                        //<<<<step4 columnstototal>>>>>>>
                        // ITS4YOU-CR SlOl 18. 5. 2016 8:22:00
                        //<<<<step41 custom calculations>>>>>>>
                        $this->saveCustomCalculations($request,$reportid,$export_sql);
                        //<<<<step41 custom calculations>>>>>>>
                        // ITS4YOU-END
                        //<<<<step5 advancedfilter>>>>>>>
                        $default_charset = vglobal("default_charset");

                        $this->saveAdvancedFilters($advft_criteria, $advft_criteria_groups, $std_filter_columns, $export_sql);

                        $d_adv_criteria = "DELETE FROM its4you_reports4you_relcriteria_summaries WHERE reportid = ?";
                        $d_adv_criteria_result = $adb->pquery($d_adv_criteria, array($reportid));
                        foreach ($groupft_criteria as $column_index => $column_condition) {
                            if (empty($column_condition))
                                continue;

                            $adv_filter_column = $column_condition["columnname"];
                            $adv_filter_comparator = $column_condition["comparator"];
                            $adv_filter_value = $column_condition["value"];
                            $adv_filter_column_condition = $column_condition["column_condition"];
                            $adv_filter_groupid = $column_condition["groupid"];

                            $column_info = explode(":", $adv_filter_column);
                            $temp_val = explode(",", $adv_filter_value);
                            if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
                                $val = Array();
                                for ($x = 0; $x < count($temp_val); $x++) {
                                    if (trim($temp_val[$x]) != '') {
                                        $date = new DateTimeField(trim($temp_val[$x]));
                                        if ($column_info[4] == 'D') {
                                            $val[$x] = DateTimeField::convertToDBFormat(
                                                trim($temp_val[$x]));
                                        } elseif ($column_info[4] == 'DT') {
                                            $val[$x] = $date->getDBInsertDateTimeValue();
                                        } else {
                                            $val[$x] = $date->getDBInsertTimeValue();
                                        }
                                    }
                                }
                                $adv_filter_value = implode(",", $val);
                            }

                            $irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria_summaries(reportid,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)";
                            $export_sql === true ? $adb->setDebug(true) : "";
                            $params = array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition);
                            $params_tofile = array("@reportid", $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition);
                            $report_array['its4you_reports4you_relcriteria_summaries'][] = $params_tofile;
                            $irelcriteriaresult = $adb->pquery($irelcriteriasql, $params);
                            $export_sql === true ? $adb->setDebug(false) : "";

                            // Update the condition expression for the group to which the condition column belongs
                            $groupConditionExpression = '';
                            if (!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
                                $groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
                            }
                            $groupConditionExpression = $groupConditionExpression . ' ' . $column_index . ' ' . $adv_filter_column_condition;
                            $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
                        }

                        $report_array = $this->saveQuickFilters($reportid, $qfColumns, $report_array, $export_sql);
                        //<<<<step5 advancedfilter>>>>>>>
                    }

                    $owner = vtlib_purify($_REQUEST["template_owner"]);
                    $sharingtype = vtlib_purify($_REQUEST["sharing"]);
                    if ($owner != "" && $owner != "") {
                        $limitsql = "UPDATE its4you_reports4you_settings SET owner=?, sharingtype=? WHERE reportid=?";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($owner, $sharingtype, $reportid);
                        $params_tofile = array("@reportid", $owner, $sharingtype);
                        $report_array['its4you_reports4you_settings'] = $params_tofile;
                        $limitresult = $adb->pquery($limitsql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }

                    //<<<<step7 scheduledReport>>>>>>>
                    if ($isReportScheduled == 'off' || $isReportScheduled == '0' || $isReportScheduled == '') {
                        $is_active=0;
                    } else {
                        $is_active=1;
                    }
                    $checkScheduledResult = $adb->pquery('SELECT 1 FROM its4you_reports4you_scheduled_reports WHERE reportid=?', array($reportid));
                    if ($adb->num_rows($checkScheduledResult) > 0) {
//                        $result = $adb->pquery("SELECT next_trigger_time FROM its4you_reports4you_scheduled_reports WHERE reportid=?", array($reportid));
//                        $next_trigger_time = $adb->query_result($result, 0, "next_trigger_time");
                        $scheduledReportSql = 'UPDATE its4you_reports4you_scheduled_reports SET is_active=?,recipients=?,schedule=?,format=?,next_trigger_time=?,generate_subject=?,generate_text=?,generate_other=?, schedule_all_records=? WHERE reportid=?';
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($is_active,$selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other, $schedule_all_records, $reportid);
                        $params_tofile = array("@reportid", $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other, $schedule_all_records);
                        $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                        $adb->pquery($scheduledReportSql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    } else {
                        $scheduleReportSql = 'INSERT INTO its4you_reports4you_scheduled_reports (reportid,is_active,recipients,schedule,format,next_trigger_time,generate_subject,generate_text,generate_other,schedule_all_records) VALUES (?,?,?,?,?,?,?,?,?,?)';
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other,$schedule_all_records);
                        $params_tofile = array("@reportid", $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other,$schedule_all_records);
                        $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                        $adb->pquery($scheduleReportSql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                    // ITS4YOU-CR SlOl 4. 4. 2016 13:10:41
                    $deleteGenerateForSql = "DELETE FROM its4you_reports4you_generatefor WHERE reportid=?";
                    $adb->pquery($deleteGenerateForSql, array($reportid));
                    if(!empty($GenerateFor)){
                        foreach($GenerateFor as $user_column_str){
                            if($user_column_str!=""){
                                $generateForSql = 'INSERT INTO its4you_reports4you_generatefor (reportid,user_column_str) VALUES (?,?)';
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($reportid, $user_column_str);
                                $params_tofile = array("@reportid", $user_column_str);
                                $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                                $adb->pquery($generateForSql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }
                    // ITS4YOU-END
                    //<<<<step7 scheduledReport>>>>>>>
                    if ($reporttype != "custom_report") {
                        //<<<<step12 Report Charts >>>>>>>
                        $deleteChartsSql = "DELETE FROM its4you_reports4you_charts WHERE reports4youid=?";
                        $adb->pquery($deleteChartsSql, array($reportid));

                        if (!empty($charts)) {
                            foreach ($charts as $chi => $ch_params) {
                                $ChartsSql = 'INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle,chart_seq,x_group,chart_position,collapse_data_block) VALUES (?,?,?,?,?,?,?,?)';
                                $ch_params_tofile = array_merge(array("reports4youid" => "@reportid"), $ch_params);
                                $ch_params = array_merge(array($reportid), $ch_params);
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $report_array['its4you_reports4you_charts'][] = $ch_params_tofile;
                                $adb->pquery($ChartsSql, array($ch_params));
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                        //<<<<step12 Report Charts >>>>>>>

                        //<<<<step13 Report Dashboards >>>>>>>
                        $deleteDashboardSql = "DELETE FROM its4you_reports4you_widget_search WHERE reportid=?";
                        $adb->pquery($deleteDashboardSql, array($reportid));
                        $DashboardsSql = 'INSERT INTO its4you_reports4you_widget_search (reportid,primary_search,allow_in_modules) VALUES (?,?,?)';
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($reportid, $primary_search,$allow_in_modules_hidden);
                        $params_tofile = array("@reportid", $primary_search,$allow_in_modules_hidden);
                        $report_array['its4you_reports4you_widget_search'][] = $params_tofile;
                        $Dashboardsresult = $adb->pquery($DashboardsSql, $params);
                        if($allow_in_modules_hidden!=""){
                            ITS4YouReports::updateDashboardLinks($reportid,$allow_in_modules_hidden);
                        }else{
                            $dashboard_link = ITS4YouReports::$dashboardUrl.$reportid;
                            $deleteDashboardSql = "DELETE FROM vtiger_links WHERE linkurl=?";
                            $adb->pquery($deleteDashboardSql, array($dashboard_link));
                        }
                        $export_sql === true ? $adb->setDebug(false) : "";
                        //<<<<step13 Report Dashboards >>>>>>>
                    }
                    //<<<<step13 Custom Report SQL >>>>>>>
                    if ($reporttype == "custom_report") {
                        $deleteCustomSql = "DELETE FROM its4you_reports4you_customsql WHERE reports4youid=?";
                        $adb->pquery($deleteCustomSql, array($reportid));
                        $CustomSqlQry = 'INSERT INTO its4you_reports4you_customsql (reports4youid,custom_sql) VALUES (?,?)';
                        $params = array($reportid, $customSql);
                        $params_tofile = array("@reportid", $customSql);
                        $report_array['its4you_reports4you_customsql'] = $params_tofile;
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $adb->pquery($CustomSqlQry, array($params));
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }
                    //<<<<step13 Custom Report SQL >>>>>>>
                } else {
                    $errormessage = "<font color='red'><B>Error Message<ul>
                                            <li><font color='red'>Error while inserting the record</font>
                                            </ul></B></font> <br>";
                    echo $errormessage;
                    die;
                }
            }
            if ($export_sql === true) {
                ITS4YouReports::sshow("EDIT");
            }
        } else {
            $genQueryId = $adb->getUniqueID("its4you_reports4you_selectquery");
            if ($genQueryId < 1) {
                $adb->pquery("DELETE FROM its4you_reports4you_selectquery_seq ", array());
                $adb->pquery("SELECT @max_reportid:=max(reports4youid)+1 FROM `its4you_reports4you`;", array());
                $adb->pquery("INSERT INTO its4you_reports4you_selectquery_seq (id) VALUES (@max_reportid); ", array());
                $genQueryId = $adb->getUniqueID("its4you_reports4you_selectquery");
            }
            $reportid = $genQueryId;
            $this->setId($reportid);
            if ($genQueryId != "") {
                $iquerysql = "insert into its4you_reports4you_selectquery (QUERYID,STARTINDEX,NUMOFOBJECTS) values (?,?,?)";
                $export_sql === true ? $adb->setDebug(true) : "";
                $params = array($genQueryId, 0, 0);
                $params_tofile = array("@reportid", 0, 0);
                $report_array['its4you_reports4you_selectquery'] = $params_tofile;
                $iquerysqlresult = $adb->pquery($iquerysql, $params);
                $export_sql === true ? $adb->setDebug(false) : "";
                if ($iquerysqlresult != false) {
                    //<<<<step2 vtiger_rep4u_selectcolumn>>>>>>>>
                    if (!empty($selectedcolumns)) {
                        for ($i = 0; $i < count($selectedcolumns); $i++) {
                            if (!empty($selectedcolumns[$i])) {
                                $icolumnsql = "insert into its4you_reports4you_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) values (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $i, (decode_html($selectedcolumns[$i])));
                                $params_tofile = array("@reportid", $i, (decode_html($selectedcolumns[$i])));
                                $report_array['its4you_reports4you_selectcolumn'][] = $params_tofile;
                                $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }

                    //<<<< its4you_reports4you_summaries>>>>>>>>
                    if (!empty($selectedSummaries)) {
                        for ($i = 0; $i < count($selectedSummaries); $i++) {
                            if (!empty($selectedSummaries[$i])) {
                                $iSmmariessql = "INSERT INTO its4you_reports4you_summaries (reportsummaryid,summarytype,columnname) VALUES (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $i, (decode_html($selectedSummaries[$i])));
                                $params_tofile = array("@reportid", $i, (decode_html($selectedSummaries[$i])));
                                $report_array['its4you_reports4you_summaries'][] = $params_tofile;
                                $iSummariessqlresult = $adb->pquery($iSmmariessql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }

                    if ($summaries_orderby != "" && $summaries_orderby_type != "") {
                        $d_selected_s_orderby_sql = "INSERT INTO  its4you_reports4you_summaries_orderby (reportid,columnindex,summaries_orderby,summaries_orderby_type) VALUES (?,?,?,?)";
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $params = array($genQueryId, 0, $summaries_orderby, $summaries_orderby_type);
                        $params_tofile = array("@reportid", 0, $summaries_orderby, $summaries_orderby_type);
                        $report_array['its4you_reports4you_summaries_orderby'] = $params_tofile;
                        $d_selected_s_orderby_result = $adb->pquery($d_selected_s_orderby_sql, $params);
                        $export_sql === true ? $adb->setDebug(false) : "";
                    }

                    if (!empty($lbl_array)) {
                        foreach ($lbl_array as $type => $type_array) {
                            foreach ($type_array as $column_str => $column_lbl) {
                                $iLabelssql = "INSERT INTO its4you_reports4you_labels (reportid,type,columnname,columnlabel) VALUES (?,?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $type, $column_str, $column_lbl);
                                $params_tofile = array("@reportid", $type, $column_str, $column_lbl);
                                $report_array['its4you_reports4you_labels'][] = $params_tofile;
                                $iLabelssqlresult = $adb->pquery($iLabelssql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }

                    if (!empty($selectedqfcolumns)) {
                        for ($i = 0; $i < count($selectedqfcolumns); $i++) {
                            if (!empty($selectedqfcolumns[$i])) {
                                $icolumnsql = "insert into its4you_reports4you_selectqfcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) values (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $i, (decode_html($selectedqfcolumns[$i])));
                                $params_tofile = array("@reportid", $i, (decode_html($selectedqfcolumns[$i])));
                                $report_array['its4you_reports4you_selectqfcolumn'][] = $params_tofile;
                                $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }

                    if ($shared_entities != "") {
                        if ($sharetype == "share") {
                            $selectedsharecolumn = explode("|", $shared_entities);
                            for ($i = 0; $i < count($selectedsharecolumn) - 1; $i++) {
                                $temp = preg_split('/::/', $selectedsharecolumn[$i]);
                                $icolumnsql = "INSERT INTO its4you_reports4you_sharing (reports4youid,shareid,setype) VALUES (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($reportid, $temp[1], $temp[0]);
                                $params_tofile = array("@reportid", $temp[1], $temp[0]);
                                $report_array['its4you_reports4you_sharing'][] = $params_tofile;
                                $icolumnsqlresult = $adb->pquery($icolumnsql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                        }
                    }

                    if ($genQueryId != "") {
                        $ireportsql = "insert into its4you_reports4you (reports4youid,reports4youname,description,folderid,reporttype,columns_limit,summaries_limit) values (?,?,?,?,?,?,?)";
                        $ireportparams = array($genQueryId, $reportname, $reportdescription, $folderid, $reporttype, $limit, $summaries_limit);
                        $params_tofile = array("@reportid", $reportname, $reportdescription, $folderid, $reporttype, 0, $limit, $summaries_limit);
                        // ITS4YOU-END
                        $export_sql === true ? $adb->setDebug(true) : "";
                        $report_array['its4you_reports4you'] = $params_tofile;
                        $ireportresult = $adb->pquery($ireportsql, $ireportparams);
                        $export_sql === true ? $adb->setDebug(false) : "";
                        if ($ireportresult != false) {
                            if ($reporttype != "custom_report") {
                                //<<<<reportmodules>>>>>>>
                                $ireportmodulesql = "insert into its4you_reports4you_modules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) values (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $pmodule, $smodule);
                                $params_tofile = array("@reportid", $pmodule, $smodule);
                                $report_array['its4you_reports4you_modules'] = $params_tofile;
                                $ireportmoduleresult = $adb->pquery($ireportmodulesql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                                //<<<<reportmodules>>>>>>>
                                //<<<<step3 its4you_reports4you_sortcol>>>>>>>
                                if ($sort_by1 != "") {
                                    $sort_by1sql = "insert into its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?,?)";
                                    $export_sql === true ? $adb->setDebug(true) : "";
                                    $params = array(1, 0, $genQueryId, $sort_by1, $sort_order1, $TimeLineColumn_str1, $TimeLineColumn_frequency1);
                                    $params_tofile = array(1, 0, "@reportid", $sort_by1, $sort_order1, "rows", $TimeLineColumn_str1, $TimeLineColumn_frequency1);
                                    $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                                    $sort_by1result = $adb->pquery($sort_by1sql, $params);
                                    $export_sql === true ? $adb->setDebug(false) : "";
                                }
                                if ($sort_by2 != "") {
                                    $sort_by2sql = "insert into its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?,?,?)";
                                    $export_sql === true ? $adb->setDebug(true) : "";
                                    $params = array(2, 0, $genQueryId, $sort_by2, $sort_order2, $timeline_type2, $TimeLineColumn_str2, $TimeLineColumn_frequency2);
                                    $params_tofile = array(2, 0, "@reportid", $sort_by2, $sort_order2, $timeline_type2, $TimeLineColumn_str2, $TimeLineColumn_frequency2);
                                    $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                                    $sort_by2result = $adb->pquery($sort_by2sql, $params);
                                    $export_sql === true ? $adb->setDebug(false) : "";
                                }
                                if ($sort_by3 != "") {
                                    $sort_by3sql = "insert into its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER,timeline_type,timeline_columnstr,timeline_columnfreq) values (?,?,?,?,?,?,?,?)";
                                    $export_sql === true ? $adb->setDebug(true) : "";
                                    $params = array(3, 0, $genQueryId, $sort_by3, $sort_order3, $timeline_type3, $TimeLineColumn_str3, $TimeLineColumn_frequency3);
                                    $params_tofile = array(3, 0, "@reportid", $sort_by3, $sort_order3, $timeline_type3, $TimeLineColumn_str3, $TimeLineColumn_frequency3);
                                    $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                                    $sort_by3result = $adb->pquery($sort_by3sql, $params);
                                    $export_sql === true ? $adb->setDebug(false) : "";
                                }
                                if(!empty($sort_by_array)){
                                    $sci = 1;
                                    foreach($sort_by_array as $sort_col_row){
                                        $sort_by_columnsql = "INSERT INTO its4you_reports4you_sortcol (SORTCOLID,SORTCOLSEQUENCE,REPORTID,COLUMNNAME,SORTORDER) VALUES (?,?,?,?,?)";
                                        $export_sql === true ? $adb->setDebug(true) : "";
                                        $params = array(4, $sci, $genQueryId, $sort_col_row[0], $sort_col_row[1]);
                                        $params_tofile = array(4, $sci, "@reportid", $sort_col_row[0], $sort_col_row[1], "", "", "");
                                        $report_array['its4you_reports4you_sortcol'][] = $params_tofile;
                                        $$sort_by_columnresult = $adb->pquery($sort_by_columnsql, $params);
                                        $export_sql === true ? $adb->setDebug(false) : "";
                                        $sci++;
                                    }
                                }
                                //<<<<step3 its4you_reports4you_sortcol>>>>>>>
                                //<<<<step5 standarfilder>>>>>>>
                                $ireportmodulesql = "insert into its4you_reports4you_datefilter (DATEFILTERID,DATECOLUMNNAME,DATEFILTER,STARTDATE,ENDDATE) values (?,?,?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $stdDateFilterField, $stdDateFilter, $startdate, $enddate);
                                $params_tofile = array("@reportid", $stdDateFilterField, $stdDateFilter, $startdate, $enddate);
                                $report_array['its4you_reports4you_datefilter'] = $params_tofile;
                                $ireportmoduleresult = $adb->pquery($ireportmodulesql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                                //<<<<step5 standarfilder>>>>>>>
                                //<<<<step4 columnstototal>>>>>>>
                                for ($i = 0; $i < count($columnstototal); $i++) {
                                    $ireportsummarysql = "insert into its4you_reports4you_summary (REPORTSUMMARYID,SUMMARYTYPE,COLUMNNAME) values (?,?,?)";
                                    $export_sql === true ? $adb->setDebug(true) : "";
                                    $params = array($genQueryId, $i, $columnstototal[$i]);
                                    $params_tofile = array("@reportid", $i, $columnstototal[$i]);
                                    $report_array['its4you_reports4you_summary'][] = $params_tofile;
                                    $ireportsummaryresult = $adb->pquery($ireportsummarysql, $params);
                                    $export_sql === true ? $adb->setDebug(false) : "";
                                }
                                //<<<<step4 columnstototal>>>>>>>
                                // ITS4YOU-CR SlOl 18. 5. 2016 8:22:00
                                //<<<<step41 custom calculations>>>>>>>
                                $this->saveCustomCalculations($request,$genQueryId,$export_sql);
                                //<<<<step41 custom calculations>>>>>>>
                                // ITS4YOU-END
                                //<<<<step5 advancedfilter>>>>>>>
                                // DOKONC SAVE TO FILE OLDO
                                $this->saveAdvancedFilters($advft_criteria, $advft_criteria_groups, $std_filter_columns, $export_sql);

                                foreach ($groupft_criteria as $column_index => $column_condition) {
                                    if (empty($column_condition))
                                        continue;

                                    $adv_filter_column = $column_condition["columnname"];
                                    $adv_filter_comparator = $column_condition["comparator"];
                                    $adv_filter_value = $column_condition["value"];
                                    $adv_filter_column_condition = $column_condition["column_condition"];
                                    $adv_filter_groupid = $column_condition["groupid"];

                                    $column_info = explode(":", $adv_filter_column);
                                    $temp_val = explode(",", $adv_filter_value);
                                    if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
                                        $val = Array();
                                        for ($x = 0; $x < count($temp_val); $x++) {
                                            if (trim($temp_val[$x]) != '') {
                                                $date = new DateTimeField(trim($temp_val[$x]));
                                                if ($column_info[4] == 'D') {
                                                    $val[$x] = DateTimeField::convertToDBFormat(
                                                        trim($temp_val[$x]));
                                                } elseif ($column_info[4] == 'DT') {
                                                    $val[$x] = $date->getDBInsertDateTimeValue();
                                                } else {
                                                    $val[$x] = $date->getDBInsertTimeValue();
                                                }
                                            }
                                        }
                                        $adv_filter_value = implode(",", $val);
                                    }

                                    $irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria_summaries(reportid,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)";
                                    $export_sql === true ? $adb->setDebug(true) : "";
                                    $params = array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition);
                                    $params_tofile = array("@reportid", $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition);
                                    $report_array['its4you_reports4you_relcriteria_summaries'][] = $params_tofile;
                                    $irelcriteriaresult = $adb->pquery($irelcriteriasql, $params);
                                    $export_sql === true ? $adb->setDebug(false) : "";

                                    // Update the condition expression for the group to which the condition column belongs
                                    $groupConditionExpression = '';
                                    if (!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
                                        $groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
                                    }
                                    $groupConditionExpression = $groupConditionExpression . ' ' . $column_index . ' ' . $adv_filter_column_condition;
                                    $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
                                }

                                $report_array = $this->saveQuickFilters($reportid, $qfColumns, $report_array, $export_sql);
                                //<<<<step5 advancedfilter>>>>>>>
                            }

                            //<<<<step6 sharing >>>>>>>
                            $owner = vtlib_purify($_REQUEST["template_owner"]);
                            $sharingtype = vtlib_purify($_REQUEST["sharing"]);
                            if ($owner != "" && $owner != "") {
                                $limitsql = "insert into its4you_reports4you_settings (reportid,owner,sharingtype) VALUES (?,?,?)";
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($genQueryId, $owner, $sharingtype);
                                $params_tofile = array("@reportid", $owner, $sharingtype);
                                $report_array['its4you_reports4you_settings'] = $params_tofile;
                                $limitresult = $adb->pquery($limitsql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                            //<<<<step6 sharing >>>>>>>
                            //<<<<step7 scheduledReport>>>>>>>
                            if ($isReportScheduled == 'off' || $isReportScheduled == '0' || $isReportScheduled == '') {
                                $is_active=0;
                            } else {
                                $is_active=1;
                            }
                            $checkScheduledResult = $adb->pquery('SELECT 1 FROM its4you_reports4you_scheduled_reports WHERE reportid=?', array($reportid));
                            if ($adb->num_rows($checkScheduledResult) > 0) {
                                $result = $adb->pquery("SELECT next_trigger_time FROM its4you_reports4you_scheduled_reports WHERE reportid=?", array($reportid));
                                $next_trigger_time = $adb->query_result($result, 0, "next_trigger_time");
                                $scheduledReportSql = 'UPDATE its4you_reports4you_scheduled_reports SET is_active=?,recipients=?,schedule=?,format=?,next_trigger_time=?,generate_subject=?,generate_text=?,generate_other=?,schedule_all_records=? WHERE reportid=?';
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($is_active,$selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other, $schedule_all_records, $reportid);
                                $params_tofile = array("@reportid", $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other, $schedule_all_records);
                                $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                                $adb->pquery($scheduledReportSql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            } else {
                                $scheduleReportSql = 'INSERT INTO its4you_reports4you_scheduled_reports (reportid,is_active,recipients,schedule,format,next_trigger_time,generate_subject,generate_text,generate_other,schedule_all_records) VALUES (?,?,?,?,?,?,?,?,?,?)';
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($reportid, $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other,$schedule_all_records);
                                $params_tofile = array("@reportid", $is_active, $selectedRecipients, $scheduledInterval, $scheduledFormat, $next_trigger_time, $generate_subject, $generate_text, $generate_other,$schedule_all_records);
                                $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                                $adb->pquery($scheduleReportSql, $params);
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                            // ITS4YOU-CR SlOl 4. 4. 2016 13:10:41
                            $deleteGenerateForSql = "DELETE FROM its4you_reports4you_generatefor WHERE reportid=?";
                            $adb->pquery($deleteGenerateForSql, array($reportid));
                            if(!empty($GenerateFor)){
                                foreach($GenerateFor as $user_column_str){
                                    if($user_column_str!=""){
                                        $generateForSql = 'INSERT INTO its4you_reports4you_generatefor (reportid,user_column_str) VALUES (?,?)';
                                        $export_sql === true ? $adb->setDebug(true) : "";
                                        $params = array($reportid, $user_column_str);
                                        $params_tofile = array("@reportid", $user_column_str);
                                        $report_array['its4you_reports4you_scheduled_reports'] = $params_tofile;
                                        $adb->pquery($generateForSql, $params);
                                        $export_sql === true ? $adb->setDebug(false) : "";
                                    }
                                }
                            }
                            // ITS4YOU-END

                            if ($reporttype != "custom_report") {
                                //<<<<step12 Report Charts >>>>>>>
                                $deleteChartsSql = "DELETE FROM its4you_reports4you_charts WHERE reports4youid=?";
                                $adb->pquery($deleteChartsSql, array($reportid));
                                /*
                                  if ($chartType != "" && $chartType != "none") {
                                  $ChartsSql = 'INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle) VALUES (?,?,?,?)';
                                  $export_sql === true ? $adb->setDebug(true) : "";
                                  $adb->pquery($ChartsSql, array($reportid, $chartType, $data_series, $charttitle));
                                  $export_sql === true ? $adb->setDebug(false) : "";
                                  }
                                 */
                                if (!empty($charts)) {
                                    foreach ($charts as $chi => $ch_params) {
                                        $ChartsSql = 'INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle,chart_seq,x_group,chart_position,collapse_data_block) VALUES (?,?,?,?,?,?,?,?)';
                                        $ch_params = array_merge(array($reportid), $ch_params);
                                        $ch_params_tofile = array_merge(array("@reportid"), $ch_params);
                                        $export_sql === true ? $adb->setDebug(true) : "";
                                        $params = array($ch_params);
                                        $report_array['its4you_reports4you_charts'][] = array($ch_params_tofile);
                                        $adb->pquery($ChartsSql, $params);
                                        $export_sql === true ? $adb->setDebug(false) : "";
                                    }
                                }
                                //<<<<step12 Report Charts >>>>>>>

                                //<<<<step13 Report Dashboards >>>>>>>
                                $DashboardsSql = 'INSERT INTO its4you_reports4you_widget_search (reportid,primary_search,allow_in_modules) VALUES (?,?,?)';
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $params = array($reportid, $primary_search,$allow_in_modules_hidden);
                                $params_tofile = array("@reportid", $primary_search,$allow_in_modules_hidden);
                                $report_array['its4you_reports4you_widget_search'][] = $params_tofile;
                                $Dashboardsresult = $adb->pquery($DashboardsSql, $params);
                                if($allow_in_modules_hidden!=""){
                                    ITS4YouReports::updateDashboardLinks($reportid,$allow_in_modules_hidden);
                                }
                                $export_sql === true ? $adb->setDebug(false) : "";
                                //<<<<step13 Report Dashboards >>>>>>>

                            }
                            //<<<<step13 Custom Report SQL >>>>>>>
                            if ($reporttype == "custom_report") {
                                $CustomSqlQry = 'INSERT INTO its4you_reports4you_customsql (reports4youid,custom_sql) VALUES (?,?)';
                                $params = array($reportid, $customSql);
                                $params_tofile = array("@reportid", $customSql);
                                $report_array['its4you_reports4you_customsql'] = $params_tofile;
                                $export_sql === true ? $adb->setDebug(true) : "";
                                $adb->pquery($CustomSqlQry, array($params));
                                $export_sql === true ? $adb->setDebug(false) : "";
                            }
                            //<<<<step13 Custom Report SQL >>>>>>>
                        } else {
                            $errormessage = "<font color='red'><B>Error Message<ul>
                                                            <li><font color='red'>Error while inserting the record</font>
                                                            </ul></B></font> <br>";
                            echo $errormessage;
                            die;
                        }
                    }
                } else {
                    $errormessage = "<font color='red'><B>Error Message<ul>
                                            <li><font color='red'>Error while inserting the record (QUERYID)</font>
                                            </ul></B></font> <br>";
                    echo $errormessage;
                    die;
                }
            }
            if ($export_sql === true && $isDuplicate != "true") {
                ITS4YouReports::sshow("EDIT");
            } elseif ($export_sql === true) {
                ITS4YouReports::sshow("DUPLICATE");
            }
        }

        if (!empty($maps)) {
            self::saveMaps($reportid, $maps);
        }

        if ($export_to_file === true) {
            $report_array_json = Zend_JSON::encode($report_array);
//			ITS4YouReports::sshow($report_array['its4you_reports4you_charts']);
//			ITS4YouReports::sshow($report_array_json);
            $fileReportContent = "<?php " . '$report_array_json = ' . "'" . $report_array_json . "'; ";
            $file_path = "test/ITS4YouReports/ITS4YouReports_" . $this->getId() . ".php";
            $exportOfReport = fopen($file_path, 'w');
            fwrite($exportOfReport, $fileReportContent);
            fclose($exportOfReport);
            echo "DONE $file_path";
            exit;
        }
        if ($export_sql === true || $debug_save===true) {
            exit;
        }
        $r4u_sesstion_name = ITS4YouReports::getITS4YouReportStoreName();
        $r4u_sesstion_unset = ITS4YouReports::unsetITS4YouReportsSerialize($r4u_sesstion_name);
        return true;
        // ini_set("display_errors", 1);error_reporting(63);
    }

    // ITS4YOU-CR SlOl 18. 5. 2016 12:36:16
    private function saveCustomCalculations($request,$reportid,$export_sql=false){
        $adb = PearDatabase::getInstance();

        $request_data = $request->getAll();

        $cc_array = array();
        foreach ($request_data as $r_key => $r_val) {
            if($r_key!="cc_calculation_WCCINRW"){
                if (strpos($r_key, "cc_calculation_") !== false) {
                    $cc_i = trim($r_key,'cc_calculation_');
                    if($request_data["cc_name_$cc_i"]!=""){
                        $cc_array[$cc_i]["name"] = $request_data["cc_name_$cc_i"];
                        $cc_array[$cc_i]["calculation"] = $request_data["cc_calculation_$cc_i"];
                        $cc_totals = '';
                        if (!empty($request_data['cc_totals_hidden_' . $cc_i])) {
                            $cc_totals = implode(',', json_decode($request_data['cc_totals_hidden_' . $cc_i]));
                        }
                        $cc_array[$cc_i]['totals'] = $cc_totals;
                    }
                }
            }
        }
        if($reportid!=""){
            $adb->pquery("DELETE FROM its4you_reports4you_custom_calculations WHERE reportid=?",array($reportid));
            $adb->pquery("DELETE FROM its4you_reports4you_cc_columns WHERE reportid=?",array($reportid));
        }
        if(!empty($cc_array)){
            require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';
            $cc_options = ITS4YouReports::getColumnsOptionsAlias($request);
            $cc_columns_array = array();

            foreach($cc_array as $cc_nr => $cc_row){
                $cc_label = $cc_row["name"];
                $cc_expr = $cc_row["calculation"];
                $cc_totals = $cc_row['totals'];
                /** CUSTOM CALCULATION START **/
                $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($cc_expr)));
                $expression = $parser->expression();
                $exprEvaluater = new VTFieldExpressionEvaluater($expression);

                /* POUZI PRI GEBEROVANI !!!
                $entityData = new VTEntityData();
                // $entityData->set($n, $v);
                $entityData->set("amount", "99");
                $entityData->set("discount", "9");

                $value = $exprEvaluater->evaluate($entityData);
                */
                $export_sql === true ? $adb->setDebug(true) : "";
                $params = array($reportid, $cc_nr, $cc_label, $cc_expr, $cc_totals);
                $adb->pquery('INSERT INTO its4you_reports4you_custom_calculations 
                                                (reportid, calculationid, calculation_label, calculation_expression, calculation_totals) 
                                        VALUES (' . generateQuestionMarks($params) . ')', $params);
                $export_sql === true ? $adb->setDebug(false) : "";

                if(isset($exprEvaluater->expr->arr) && !empty($exprEvaluater->expr->arr)){
                    foreach($exprEvaluater->expr->arr as $expr_arr){
                        if(isset($expr_arr->arr)){
                            foreach($expr_arr->arr as $chcek_col_str){
                                if(isset($cc_options[$chcek_col_str->value]) && !in_array($cc_options[$chcek_col_str->value],$cc_columns_array)){
                                    $cc_columns_array[] = $cc_options[$chcek_col_str->value];
                                }
                            }
                        }else{
                            foreach($expr_arr as $chcek_col_str){
                                if(isset($cc_options[$chcek_col_str]) && !in_array($cc_options[$chcek_col_str],$cc_columns_array)){
                                    $cc_columns_array[] = $cc_options[$chcek_col_str];
                                }
                            }
                        }
                    }
                }elseif(isset($cc_options[$exprEvaluater->expr->value]) && !in_array($cc_options[$exprEvaluater->expr->value],$cc_columns_array)){
                    $cc_columns_array[] = $cc_options[$exprEvaluater->expr->value];
                }
                /** CUSTOM CALCULATION END **/
            }
        }
        $export_sql === true ? $adb->setDebug(true) : "";
        if(!empty($cc_columns_array)){
            foreach($cc_columns_array as $col_i => $col_str){
                $col_i = $col_i+1;
                $params = array($reportid,$col_i,$col_str);
                $adb->pquery("INSERT INTO  its4you_reports4you_cc_columns (reportid,columnindex,columnname) VALUES (".generateQuestionMarks($params).")",$params);
            }
        }
        $export_sql === true ? $adb->setDebug(false) : "";

        return true;
    }
    // ITS4YOU-END

    /**
     * Function to get the List View url for the module
     * @return <String> - Record List View Url
     */
    public function getListViewUrl() {
        $module = $this->getModule();
        return 'index.php?module=' . $this->getModuleName() . '&view=List';
    }

    /**
     * Function saves Reports Sorting Fields
     */
    function saveSortFields() {
        $db = PearDatabase::getInstance();

        $sortFields = $this->get('sortFields');

        $i = 0;
        foreach ($sortFields as $fieldInfo) {
            $db->pquery('INSERT INTO its4you_reports4you_sortcol(sortcolid, reportid, columnname, sortorder) VALUES (?,?,?,?)', array($i, $this->getId(), $fieldInfo[0], $fieldInfo[1]));
            if (CustomReportUtils::IsDateField($fieldInfo[0])) {
                if (empty($fieldInfo[2])) {
                    $fieldInfo[2] = 'None';
                }
                $db->pquery("INSERT INTO vtiger_reportgroupbycolumn(reportid, sortid, sortcolname, dategroupbycriteria)
                                    VALUES(?,?,?,?)", array($this->getId(), $i, $fieldInfo[0], $fieldInfo[2]));
            }
            $i++;
        }
    }

    /**
     * Function saves Reports Calculation Fields information
     */
    function saveCalculationFields() {
        $db = PearDatabase::getInstance();

        $calculationFields = $this->get('calculationFields');
        for ($i = 0; $i < count($calculationFields); $i++) {
            $db->pquery('INSERT INTO vtiger_reportsummary (reportsummaryid, summarytype, columnname) VALUES (?,?,?)', array($this->getId(), $i, $calculationFields[$i]));
        }
    }

    /**
     * Function saves Reports Standard Filter information
     */
    function saveStandardFilter() {
        $db = PearDatabase::getInstance();

        $standardFilter = $this->get('standardFilter');
        if (!empty($standardFilter)) {
            $db->pquery('INSERT INTO vtiger_reportdatefilter (datefilterid, datecolumnname, datefilter, startdate, enddate)
                                                    VALUES (?,?,?,?,?)', array($this->getId(), $standardFilter['field'], $standardFilter['type'],
                $standardFilter['start'], $standardFilter['end']));
        }
    }

    /**
     * Function saves Reports Sharing information
     */
    function saveSharingInformation() {
        $db = PearDatabase::getInstance();

        $sharingInfo = $this->get('sharingInfo');
        for ($i = 0; $i < count($sharingInfo); $i++) {
            $db->pquery('INSERT INTO vtiger_reportsharing(reportid, shareid, setype) VALUES (?,?,?)', array($this->getId(), $sharingInfo[$i]['id'], $sharingInfo[$i]['type']));
        }
    }

    /**
     * Functions saves Reports selected fields
     */
    function saveSelectedFields() {
        $db = PearDatabase::getInstance();

        $selectedFields = $this->get('selectedFields');

        for ($i = 0; $i < count($selectedFields); $i++) {
            if (!empty($selectedFields[$i])) {
                $db->pquery("INSERT INTO vtiger_selectcolumn(queryid, columnindex, columnname) VALUES (?,?,?)", array($this->getId(), $i, decode_html($selectedFields[$i])));
            }
        }
    }

    /**
     * Function saves Reports Filter information
     */
    function saveAdvancedFilters($advft_criteria = array(), $advft_criteria_groups = array(), $std_filter_columns = array(), $export_sql = false) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $adb = PearDatabase::getInstance();
        $reportid = $this->getId();
        $d_adv_criteria = "DELETE FROM its4you_reports4you_relcriteria WHERE queryid = ?";
        $d_adv_criteria_result = $adb->pquery($d_adv_criteria, array($reportid));

        // ITS4YOU SlOl 23. 11. 2015 13:35:47
        if(empty($std_filter_columns)){
            $ITS4YouReports = ITS4YouReports::getStoredITS4YouReport();
            $std_filter_columns = $ITS4YouReports->getStdFilterColumns();
        }
        // ITS4YOU-END

        if (!empty($std_filter_columns)) {
            global $default_charset;
            foreach ($std_filter_columns as $std_key => $std_value) {
                $std_filter_columns[$std_key] = html_entity_decode($std_value, ENT_QUOTES, $default_charset);
            }
        }

        if (!empty($advft_criteria) && !empty($advft_criteria_groups)) {
            $default_charset = vglobal("default_charset");
            foreach ($advft_criteria as $column_index => $column_condition) {
                if (empty($column_condition))
                    continue;

                $adv_filter_comparator = $column_condition["comparator"];
                if (in_array($column_condition["columnname"], $std_filter_columns)) {
                    $adv_filter_column = $column_condition["columnname"];
                } else {
                    $adv_filter_column = $column_condition["columnname"];
                }
                $adv_filter_value = $column_condition["value"];

                $adv_filter_column_condition = $column_condition["column_condition"];
                $adv_filter_groupid = $column_condition["groupid"];

                $adv_valuehdn = $column_condition["value_hdn"];

                if($adv_valuehdn!=""){
                    $valuehdn = $adv_valuehdn;
                }elseif (in_array($adv_filter_column, $std_filter_columns)) {
                    if(in_array($adv_filter_comparator,ITS4YouReports::$dateNcomparators)){
                        $adv_filter_value = round(vtlib_purify($_REQUEST["nfval$column_index"]));
                    }else {
                        if ( 'custom' !== $adv_filter_comparator ) {
                            $temp_val = explode("<;@STDV@;>", html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset));
                            $val[0] = DateTimeField::convertToDBFormat(trim($temp_val[0]));
                            $val[1] = DateTimeField::convertToDBFormat(trim($temp_val[1]));
                            $adv_filter_value = implode("<;@STDV@;>", $val);
                        }
                    }
                    // $adv_filter_value = html_entity_decode($adv_filter_value, ENT_QUOTES, $default_charset);
                } else {
                    $column_info = explode(":", $adv_filter_column);
                    /// $temp_val = explode(",",$adv_filter_value);
                    $temp_val = $adv_filter_value;
                    if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
                        $val = Array();
                        if (is_array($temp_val)) {
                            for ($x = 0; $x < count($temp_val); $x++) {
                                if (trim($temp_val[$x]) != '') {
                                    $date = new DateTimeField(trim($temp_val[$x]));
                                    if ($column_info[4] == 'D') {
                                        $val[$x] = DateTimeField::convertToDBFormat(
                                            trim($temp_val[$x]));
                                    } elseif ($column_info[4] == 'DT') {
                                        $val[$x] = $date->getDBInsertDateTimeValue();
                                    } else {
                                        $val[$x] = $date->getDBInsertTimeValue();
                                    }
                                }
                            }
                        } else {
							if (trim($temp_val) != '') {
	                            $date = new DateTimeField(trim($temp_val));
	                            if (false === strpos($val, ':')) {
	                            	$val[] = DateTimeField::convertToDBFormat(trim($temp_val));
								} else if (false !== strpos($val, ':')) {
									$val[] = $date->getDBInsertDateTimeValue();
								} else {
									$val[] = $date->getDBInsertTimeValue();
								}
	                        }
						}
                        $adv_filter_value = implode(",", $val);
                    }
                }
                if (is_array($adv_filter_value)) {
                    $adv_filter_value = implode(",", $adv_filter_value);
                }
                $irelcriteriasql = "INSERT INTO its4you_reports4you_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,VALUEHDN,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?,?)";
                $export_sql === true ? $adb->setDebug(true) : "";
                $irelcriteriaresult = $adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $valuehdn, $adv_filter_groupid, $adv_filter_column_condition));
                $export_sql === true ? $adb->setDebug(false) : "";

                // Update the condition expression for the group to which the condition column belongs
                $groupConditionExpression = '';
                if (!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
                    $groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
                }
                $groupConditionExpression = $groupConditionExpression . ' ' . $column_index . ' ' . $adv_filter_column_condition;
                $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
            }

            $d_adv_criteria_grouping = "DELETE FROM its4you_reports4you_relcriteria_grouping WHERE queryid = ?";
            $export_sql === true ? $adb->setDebug(true) : "";
            $d_adv_criteria_grouping_result = $adb->pquery($d_adv_criteria_grouping, array($reportid));
            $export_sql === true ? $adb->setDebug(false) : "";
            foreach ($advft_criteria_groups as $group_index => $group_condition_info) {
                if (!isset($group_condition_info) || empty($group_condition_info))
                    continue;
                if($group_index==$adv_filter_groupid){
                    $group_condition_info['groupcondition'] = "";
                }
                $irelcriteriagroupsql = "INSERT INTO its4you_reports4you_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) VALUES (?,?,?,?)";
                $export_sql === true ? $adb->setDebug(true) : "";
                $irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $reportid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
                $export_sql === true ? $adb->setDebug(false) : "";
                if($group_index==$adv_filter_groupid){
                    break;
                }
            }
        }
    }

    /**
     * Function saves Reports Scheduling information
     */
    function saveScheduleInformation() {
        $db = PearDatabase::getInstance();

        $selectedRecipients = $this->get('selectedRecipients');
        $scheduledInterval = $this->get('scheduledInterval');
        $scheduledFormat = $this->get('scheduledFormat');

        $db->pquery('INSERT INTO vtiger_scheduled_reports(reportid, recipients, schedule, format, next_trigger_time) VALUES
                    (?,?,?,?,?)', array($this->getId(), $selectedRecipients, $scheduledInterval, $scheduledFormat, date("Y-m-d H:i:s")));
    }

    /**
     * Function deletes report scheduling information
     */
    function deleteScheduling() {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_scheduled_reports WHERE reportid = ?', array($this->getId()));
    }

    /**
     * Function returns sql for the report
     * @param <String> $advancedFilterSQL
     * @param <String> $format
     * @return <String>
     */
    function getReportSQL($advancedFilterSQL = false, $format = false) {
        //$report4YouRun = Report4YouRun::getInstance($this->getId());
        //$sql = $report4YouRun->sGetSQLforReport($this->getId(), $advancedFilterSQL, $format);
        //return $sql;
    }

    /**
     * Function returns report's data
     * @param <Vtiger_Paging_Model> $pagingModel
     * @param <String> $filterQuery
     * @return <Array>saveAdvancedFilters
     */
    function getReportData($pagingModel = false, $filterQuery = false) {
        $reportid = $this->getId();
        $report4YouRun = Report4YouRun::getInstance($reportid);
        // $data = $report4YouRun->GenerateReport($reportid, 'HTML', $filterQuery, true, $pagingModel->getStartIndex(), $pagingModel->getPageLimit());
        $data = $report4YouRun->GenerateReport($reportid, 'HTML');
        return $data;
    }

    function getReportCalulationData($filterQuery = false) {
        //$report4YouRun = Report4YouRun::getInstance($this->getId());
        //$data = $report4YouRun->GenerateReport('TOTALXLS', $filterQuery, true);
        //return $data;
    }

    /**
     * Function returns reports is default or not
     * @return <boolean>
     */
    function isDefault() {
        if ($this->get('state') == 'SAVED') {
            return true;
        }
        return false;
    }

    /**
     * Function move report to another specified folder
     * @param folderid
     */
    function move($folderId) {
        $db = PearDatabase::getInstance();
//$db->setDebug(true);
        $db->pquery('UPDATE  its4you_reports4you SET folderid = ? WHERE reports4youid = ?', array($folderId, $this->getId()));
//$db->setDebug(false);
    }

    /**
     * Function to get Calculation fields for Primary module
     * @return <Array> Primary module calculation fields
     */
    function getPrimaryModuleCalculationFields() {
        $primaryModule = $this->getPrimaryModule();
        $primaryModuleFields = $this->getPrimaryModuleFields();
        $calculationFields = array();
        foreach ($primaryModuleFields[$primaryModule] as $blocks) {
            if (!empty($blocks)) {
                foreach ($blocks as $fieldType => $fieldName) {
                    $fieldDetails = explode(':', $fieldType);
                    if ($fieldDetails[4] === "I" || $fieldDetails[4] === "N" || $fieldDetails[4] === "NN") {
                        $calculationFields[$fieldType] = $fieldName;
                    }
                }
            }
        }
        $primaryModuleCalculationFields[$primaryModule] = $calculationFields;
        return $primaryModuleCalculationFields;
    }

    /**
     * Function to get Calculation fields for Secondary modules
     * @return <Array> Secondary modules calculation fields
     */
    function getSecondaryModuleCalculationFields() {
        $secondaryModuleCalculationFields = array();
        $secondaryModules = $this->getSecondaryModules();
        if (!empty($secondaryModules)) {
            $secondaryModulesList = explode(':', $secondaryModules);
            $count = count($secondaryModulesList);

            $secondaryModuleFields = $this->getSecondaryModuleFields();

            for ($i = 0; $i < $count; $i++) {
                $calculationFields = array();
                $secondaryModule = $secondaryModulesList[$i];
                foreach ($secondaryModuleFields[$secondaryModule] as $blocks) {
                    if (!empty($blocks)) {
                        foreach ($blocks as $fieldType => $fieldName) {
                            $fieldDetails = explode(':', $fieldType);
                            if ($fieldDetails[4] === "I" || $fieldDetails[4] === "N" || $fieldDetails[4] === "NN") {
                                $calculationFields[$fieldType] = $fieldName;
                            }
                        }
                    }
                }
                $secondaryModuleCalculationFields[$secondaryModule] = $calculationFields;
            }
        }
        return $secondaryModuleCalculationFields;
    }

    /**
     * Function to get Calculation fields for entire Report
     * @return <Array> report calculation fields
     */
    function getCalculationFields() {
        $primaryModuleCalculationFields = $this->getPrimaryModuleCalculationFields();
        $secondaryModuleCalculationFields = $this->getSecondaryModuleCalculationFields();

        return array_merge($primaryModuleCalculationFields, $secondaryModuleCalculationFields);
    }

    /**
     * Function used to transform the older filter condition to suit newer filters.
     * The newer filters have only two groups one with ALL(AND) condition between each
     * filter and other with ANY(OR) condition, this functions tranforms the older
     * filter with 'AND' condition between filters of a group and will be placed under
     * match ALL conditions group and the rest of it will be placed under match Any group.
     * @return <Array>
     */
    function transformToNewAdvancedFilter() {
        $standardFilter = $this->transformStandardFilter();
        $advancedFilter = $this->getSelectedAdvancedFilter();
        $allGroupColumns = $anyGroupColumns = array();
        foreach ($advancedFilter as $index => $group) {
            $columns = $group['columns'];
            $and = $or = 0;
            $block = $group['condition'];
            if (count($columns) != 1) {
                foreach ($columns as $column) {
                    if ($column['column_condition'] == 'and') {
                        ++$and;
                    } else {
                        ++$or;
                    }
                }
                if ($and == count($columns) - 1 && count($columns) != 1) {
                    $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
                } else {
                    $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
                }
            } else if ($block == 'and' || $index == 1) {
                $allGroupColumns = array_merge($allGroupColumns, $group['columns']);
            } else {
                $anyGroupColumns = array_merge($anyGroupColumns, $group['columns']);
            }
        }
        if ($standardFilter) {
            $allGroupColumns = array_merge($allGroupColumns, $standardFilter);
        }
        $transformedAdvancedCondition = array();
        $transformedAdvancedCondition[1] = array('columns' => $allGroupColumns, 'condition' => 'and');
        $transformedAdvancedCondition[2] = array('columns' => $anyGroupColumns, 'condition' => '');

        return $transformedAdvancedCondition;
    }

    /*
     *  Function used to tranform the standard filter as like as advanced filter format
     * 	@returns array of tranformed standard filter
     */

    public function transformStandardFilter() {
        $standardFilter = $this->getSelectedStandardFilter();
        if (!empty($standardFilter)) {
            $tranformedStandardFilter = array();
            $tranformedStandardFilter['comparator'] = 'bw';

            $fields = explode(':', $standardFilter['columnname']);

            if ($fields[1] == 'createdtime' || $fields[1] == 'modifiedtime' || ($fields[0] == 'vtiger_activity' && $fields[1] == 'date_start')) {
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:DT";
                $date[] = $standardFilter['startdate'] . ' 00:00:00';
                $date[] = $standardFilter['enddate'] . ' 00:00:00';
                $tranformedStandardFilter['value'] = implode(',', $date);
            } else {
                $tranformedStandardFilter['columnname'] = "$fields[0]:$fields[1]:$fields[3]:$fields[2]:D";
                $tranformedStandardFilter['value'] = $standardFilter['startdate'] . ',' . $standardFilter['enddate'];
            }
            return array($tranformedStandardFilter);
        } else {
            return false;
        }
    }

    /**
     * Function returns the Advanced filter SQL
     * @return <String>
     */
    function getAdvancedFilterSQL() {
        /* $advancedFilter = $this->get('advancedFilter');

          $advancedFilterCriteria = array();
          $advancedFilterCriteriaGroup = array();
          foreach($advancedFilter as $groupIndex => $groupInfo) {
          $groupColumns = $groupInfo['columns'];
          $groupCondition = $groupInfo['condition'];

          if (empty ($groupColumns)) {
          unset($advancedFilter[1]['condition']);
          } else {
          if(!empty($groupCondition)){
          $advancedFilterCriteriaGroup[$groupIndex] = array('groupcondition'=>$groupCondition);
          }
          }

          foreach($groupColumns as $groupColumn){
          $groupColumn['groupid'] = $groupIndex;
          $groupColumn['column_condition'] = $groupColumn['column_condition'];
          unset($groupColumn['column_condition']);
          $advancedFilterCriteria[] = $groupColumn;
          }
          }

          $this->reportRun = Report4YouRun::getInstance($this->getId());
          $filterQuery = $this->reportRun->RunTimeAdvFilter($advancedFilterCriteria,$advancedFilterCriteriaGroup);
          return $filterQuery; */
    }

    /**
     * Function to generate data for advanced filter conditions
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array>
     */
    public function generateData($pagingModel = false) {
        $filterQuery = $this->getAdvancedFilterSQL();
        return $this->getReportData($pagingModel, $filterQuery);
    }

    /**
     * Function to generate data for advanced filter conditions
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array>
     */
    public function generateCalculationData() {
        $filterQuery = $this->getAdvancedFilterSQL();
        return $this->getReportCalulationData($filterQuery);
    }

    /**
     * Function to check duplicate exists or not
     * @return <boolean>
     */
    public function checkDuplicate() {
        $db = PearDatabase::getInstance();

        $query = "SELECT 1 FROM  its4you_reports4you WHERE reports4youname = ?";
        $params = array($this->getName());

        $record = $this->getId();
        if ($record && !$this->get('isDuplicate')) {
            $query .= " AND reports4youid != ?";
            array_push($params, $record);
        }

        $result = $db->pquery($query, $params);
        if ($db->num_rows($result)) {
            return true;
        }
        return false;
    }

    /**
     * Function is used for Inventory reports, filters should show line items fields only if they are selected in
     * calculation otherwise it should not be shown
     * @return boolean
     */
    function showLineItemFieldsInFilter($calculationFields = false) {
        if ($calculationFields == false)
            $calculationFields = $this->getSelectedCalculationFields();

        $primaryModule = $this->getPrimaryModule();
        $inventoryModules = array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder');
        if (!in_array($primaryModule, $inventoryModules))
            return false;
        if (!empty($calculationFields)) {
            foreach ($calculationFields as $field) {
                if (stripos($field, 'cb:vtiger_inventoryproductrel') !== false) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    function getPrimaryModules() {
        return $this->report->getPrimaryModules();
    }

    function getReportFolders() {
        return $this->report->getReportFolders();
    }

    function getSelectedColumnsList($selectedColumnsArray, $select_columname = "") {
        return $this->report->getSelectedColumnsList($selectedColumnsArray, $select_columname);
    }

    function getSelectedColumnListArray($reportid, $select_columname = "") {
        return $this->report->getSelectedColumnListArray($reportid, $select_columname);
    }

    function getRelatedModulesArray() {
        return $this->report->relatedmodulesarray;
    }

    function getSelectedColumnsToTotal($reportid) {
        return $this->report->getSelectedColumnsToTotal($reportid);
    }

    function getGroupFilterList($reportid) {
        return $this->report->getGroupFilterList($reportid);
    }

    function getAdvancedFilterList($reportid) {
        return $this->report->getAdvancedFilterList($reportid);
    }

    function getSummariesFilterList($reportid) {
        return $this->report->getSummariesFilterList($reportid);
    }

    function getSummariesCriteria() {
        return $this->report->summaries_criteria;
    }

    function getStdFilterColumns() {
        return $this->report->getStdFilterColumns();
    }

    function getAdvSelFields() {
        return $this->report->adv_sel_fields;
    }

    function getDateFilterValues() {
        return $this->report->Date_Filter_Values;
    }

    function getAdvRelFields() {
        return $this->report->adv_rel_fields;
    }

    function setColumnsSummary($Objects) {
        return $this->report->columnssummary = $Objects;
    }

    function sgetNewColumntoTotalSelected($reportid, $R_Objects, $sgetNewColumntoTotalSelected = array()) {
        return $this->report->sgetNewColumntoTotalSelected($reportid, $R_Objects, $sgetNewColumntoTotalSelected);
    }

    function getAvailableModulesArray($primarymodule, $primarymoduleid) {
        $available_modules = array();

        if ($primarymodule != "") {

            $modulename_lbl = vtranslate($primarymodule, $primarymodule);
            $secondarymodule_arr = $this->report->getReportRelatedModules($primarymoduleid);
            $this->report->getSecModuleColumnsList($primarymoduleid);

            $available_modules[] = array("id" => $primarymoduleid, "name" => $modulename_lbl, "checked" => "checked");

            foreach ($secondarymodule_arr as $key => $value) {
                $exploded_mid = explode("x", $value["id"]);
                if (strtolower($exploded_mid[1]) != "mif") {
                    $available_modules[] = array("id" => $value["id"], "name" => "- " . $value["name"], "checked" => "");
                }
            }
        }
        return $available_modules;
    }

    function getRelatedModulesString() {
        return $this->report->relatedmodulesstring;
    }

    function getSecondaryColumnsHTML($module) {

        if ($module != "") {
            $secmodule = explode(":", $module);
            for ($i = 0; $i < count($secmodule); $i++) {
                $modulename = vtlib_getModuleNameById($secmodule[$i]);

                if (vtlib_isModuleActive($modulename)) {
                    $block_listed = array();
                    $modulename_lang = vtranslate($modulename, $modulename);
                    foreach ($this->report->module_list[$modulename] as $key => $value) {
                        if (isset($this->report->sec_module_columnslist[$modulename][$value]) && !$block_listed[$value]) {
                            $block_listed[$value] = true;
                            $shtml .= "<optgroup label=\"" . $modulename_lang . " " . vtranslate($value) . "\" class=\"select\" style=\"border:none\">";
                            foreach ($this->report->sec_module_columnslist[$modulename][$value] as $field => $fieldlabel) {
                                $shtml .= "<option value=\"" . $field . "\">" . vtranslate($fieldlabel, $modulename) . "</option>";
                            }
                            $shtml .= "</optgroup>";
                        }
                    }
                }
            }
        }
        return $shtml;
    }

    function getSecondaryColumns($Options, $module) {
        $Options = getSecondaryColumns($Options, $module);
//        ITS4YouReports::sshow($Options);exit;
        return $Options;
    }

    function getPrimaryColumns($Options, $module, $id_added = false) {
        $block_listed = array();
        $default_charset = vglobal("default_charset");
        if(!isset($this->report->module_list[$module]) || empty($this->report->module_list[$module])) {
            $this->report->initListOfModules();
        }
        if (!isset($this->report->pri_module_columnslist[$module]) || empty($this->report->pri_module_columnslist[$module])) {
            $this->report->pri_module_columnslist = $this->getPrimaryModuleFields();
        }
        if ($module == "Calendar") {
            $calendar_block = vtranslate($module, $module);
            $cal_options = $cal_options_f[$module] = array();
            $skip_fields = array("eventstatus", "status");
            $status_arr = array();
            $todoBlockName = 'To Do Details';
            $eventBlockName = 'Event Details';
            $arrayToMerge = $calendarMergedFields = array();
            foreach ($this->report->pri_module_columnslist[$module] as $block_key => $field_array) {
                if (in_array($block_key, array($todoBlockName, $eventBlockName))) {
                    $cal_options_f[$todoBlockName] = array();
                    $cal_options_f[$eventBlockName] = array();
                    foreach ($field_array as $field => $fieldLabel) {
                        $arrayToMerge[$block_key][$field] = $fieldLabel;
                    }
                } else {
                    foreach ($field_array as $field => $fieldLabel) {
                        $cal_options_f[$block_key][] = array("value" => $field, "text" => $fieldLabel);
                    }
                }
            }
            $calendarBlockFields = array_merge($arrayToMerge[$todoBlockName], $arrayToMerge[$eventBlockName]);
            $cal_options_f[$module][] = array("value" => "vtiger_crmentity:crmid:" . $module . "_ID:crmid:I", "text" => getTranslatedString(getTranslatedString($module) . ' ID'));
            foreach ($calendarBlockFields as $field => $fieldLabel) {
                $cal_options_f[$module][] = array("value" => $field, "text" => $fieldLabel);
            }
            $todoBlockFields = array_diff($arrayToMerge[$todoBlockName], $arrayToMerge[$eventBlockName]);
            foreach ($todoBlockFields as $field => $fieldLabel) {
                $cal_options_f[$todoBlockName][] = array("value" => $field, "text" => $fieldLabel);
            }
            $eventBlockFields = array_diff($arrayToMerge[$eventBlockName], $arrayToMerge[$todoBlockName]);
            $cal_options_f[$eventBlockName][] = array("value" => "vtiger_invitees:inviteeid:LBL_INVITE_USERS:inviteeid:V", "text" => getTranslatedString('LBL_INVITE_USERS', 'Events'));
            foreach ($eventBlockFields as $field => $fieldLabel) {
                $cal_options_f[$eventBlockName][] = array("value" => $field, "text" => $fieldLabel);
            }
            if (!empty($status_arr)) {
                $cal_options_f[$module][] = $status_arr;
            }
            $Options = array_merge($Options, $cal_options_f);
        } else {
            foreach ($this->report->module_list[$module] as $key => $value) {
                if (isset($this->report->pri_module_columnslist[$module][$value]) && !$block_listed[$key]) {
                    $block_listed[$value] = true;
                    $optgroup = " - " . vtranslate($value);
                    if ($id_added == false) {
                        $Options[$optgroup]["vtiger_crmentity:crmid:" . $module . "_ID:crmid:I"] = getTranslatedString(getTranslatedString($module) . ' ID');
                        $id_added = true;
                    }
                    foreach ($this->report->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
                        $Options[$optgroup][] = array("value" => $field, "text" => html_entity_decode(vtranslate($fieldlabel, $module), ENT_QUOTES, $default_charset));
                    }
                }
            }
//echo "<pre>";print_r($Options);echo "</pre>";
        }

        return $Options;
    }

    function getPrimaryTLStdFilter($module) {
        $Options = array();
        if (vtlib_isModuleActive($module)) {

            $result = getR4UStdCriteriaByModule($module);

            if (isset($result)) {
                foreach ($result as $key => $value) {
                    $fieldid = "";
                    $Options[vtranslate($module, $module)][] = array("value" => $key . "$fieldid", 'text' => vtranslate($value, $module));
                }
            }
        }
        return $Options;
    }

    function getCriteriaJS() {
        $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

        $currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $currentmonth1 = date("Y-m-t");
        $lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
        $lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
        $nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
        $nextmonth1 = date("Y-m-t", strtotime("+1 Month"));

        global $current_user;
        $dayoftheweek = $current_user->column_fields["dayoftheweek"];
        //ITS4YouReports::sshow("DOW3 $dayoftheweek");

        $today_name = date("l");
        if($today_name==$dayoftheweek){
            $lastweek0 = date("Y-m-d", strtotime("-1 week $dayoftheweek"));
            $lastweek1 = date("Y-m-d", strtotime("this $dayoftheweek -1 day"));

            $thisweek0 = date("Y-m-d", strtotime("this $dayoftheweek"));
            $thisweek1 = date("Y-m-d", strtotime("+1 week $dayoftheweek -1 day"));

            $nextweek0 = date("Y-m-d", strtotime("+1 week $dayoftheweek"));
            $nextweek1 = date("Y-m-d", strtotime("+2 week $dayoftheweek -1 day"));
        }else{
            $lastweek0 = date("Y-m-d", strtotime("-2 week $dayoftheweek"));
            $lastweek1 = date("Y-m-d", strtotime("-1 week $dayoftheweek -1 day"));

            $thisweek0 = date("Y-m-d", strtotime("-1 week $dayoftheweek"));
            $thisweek1 = date("Y-m-d", strtotime("this $dayoftheweek -1 day"));

            $nextweek0 = date("Y-m-d", strtotime("this $dayoftheweek"));
            $nextweek1 = date("Y-m-d", strtotime("+1 week $dayoftheweek -1 day"));
        }

        $next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
        $next15days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 14, date("Y")));
        $next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
        $next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
        $next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
        $next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));

        $last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
        $last15days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 14, date("Y")));
        $last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
        $last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
        $last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
        $last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));

        $currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
        $lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
        $lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
        $nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
        $nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

        $todaymore_start = $today;
        $todayless_end = $today;
        $older1days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $older7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
        $older15days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 15, date("Y")));
        $older30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 30, date("Y")));
        $older60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 60, date("Y")));
        $older90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 90, date("Y")));
        $older120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 120, date("Y")));

        if (date("m") <= 3) {
            $cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
        } else if (date("m") > 3 and date("m") <= 6) {
            $pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
        } else if (date("m") > 6 and date("m") <= 9) {
            $nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
        } else if (date("m") > 9 and date("m") <= 12) {
            $nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
        }

        $sjsStr = '<script type="text/javaScript">
                        function showDateRange(s_obj, e_obj, st_obj, et_obj, type ){
                		if (type!="custom"){
					s_obj.style.display="block";
					e_obj.style.display="block";
					st_obj.style.display="none";
					et_obj.style.display="none";
				}else{
					s_obj.style.display="none";
					e_obj.style.display="none";
					st_obj.style.display="block";
					et_obj.style.display="block";
				}
				if( type == "today" ){
					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}else if( type == "yesterday" ){
					s_obj.value = "' . getValidDisplayDate($yesterday) . '";
					e_obj.value = "' . getValidDisplayDate($yesterday) . '";
				}else if( type == "tomorrow" ){

					s_obj.value = "' . getValidDisplayDate($tomorrow) . '";
					e_obj.value = "' . getValidDisplayDate($tomorrow) . '";
				}else if( type == "thisweek" ){

					s_obj.value = "' . getValidDisplayDate($thisweek0) . '";
					e_obj.value = "' . getValidDisplayDate($thisweek1) . '";
				}else if( type == "lastweek" ){

					s_obj.value = "' . getValidDisplayDate($lastweek0) . '";
					e_obj.value = "' . getValidDisplayDate($lastweek1) . '";
				}else if( type == "nextweek" ){

					s_obj.value = "' . getValidDisplayDate($nextweek0) . '";
					e_obj.value = "' . getValidDisplayDate($nextweek1) . '";
				}else if( type == "thismonth" ){

					s_obj.value = "' . getValidDisplayDate($currentmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($currentmonth1) . '";
				}else if( type == "lastmonth" ){

					s_obj.value = "' . getValidDisplayDate($lastmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($lastmonth1) . '";
				}else if( type == "nextmonth" ){

					s_obj.value = "' . getValidDisplayDate($nextmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($nextmonth1) . '";
				}else if( type == "next7days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next7days) . '";
				}else if( type == "next15days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next15days) . '";
				}else if( type == "next30days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next30days) . '";
				}else if( type == "next60days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next60days) . '";
				}else if( type == "next90days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next90days) . '";
				}else if( type == "next120days" ){

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next120days) . '";
				}else if( type == "last7days" ){

					s_obj.value = "' . getValidDisplayDate($last7days) . '";
					e_obj.value =  "' . getValidDisplayDate($today) . '";
				}else if( type == "last15days" ){

					s_obj.value = "' . getValidDisplayDate($last15days) . '";
					e_obj.value =  "' . getValidDisplayDate($today) . '";
				}else if( type == "last30days" ){

					s_obj.value = "' . getValidDisplayDate($last30days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}else if( type == "last60days" ){

					s_obj.value = "' . getValidDisplayDate($last60days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}else if( type == "last90days" ){

					s_obj.value = "' . getValidDisplayDate($last90days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}else if( type == "last120days" ){

					s_obj.value = "' . getValidDisplayDate($last120days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}else if( type == "thisfy" ){

					s_obj.value = "' . getValidDisplayDate($currentFY0) . '";
					e_obj.value = "' . getValidDisplayDate($currentFY1) . '";
				}else if( type == "prevfy" ){

					s_obj.value = "' . getValidDisplayDate($lastFY0) . '";
					e_obj.value = "' . getValidDisplayDate($lastFY1) . '";
				}else if( type == "nextfy" ){

					s_obj.value = "' . getValidDisplayDate($nextFY0) . '";
					e_obj.value = "' . getValidDisplayDate($nextFY1) . '";
				}else if( type == "nextfq" ){

					s_obj.value = "' . getValidDisplayDate($nFq) . '";
					e_obj.value = "' . getValidDisplayDate($nFq1) . '";
				}else if( type == "prevfq" ){

					s_obj.value = "' . getValidDisplayDate($pFq) . '";
					e_obj.value = "' . getValidDisplayDate($pFq1) . '";
				}else if( type == "thisfq" ){
					s_obj.value = "' . getValidDisplayDate($cFq) . '";
					e_obj.value = "' . getValidDisplayDate($cFq1) . '";
				}else if( type == "todaymore" ){
					s_obj.value = "' . getValidDisplayDate($todaymore_start) . '";
					e_obj.value = "";
				}else if( type == "todayless" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($todayless_end) . '";
				}else if( type == "older1days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older1days) . '";
				}else if( type == "older7days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older7days) . '";
				}else if( type == "older15days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older15days) . '";
				}else if( type == "older30days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older30days) . '";
				}else if( type == "older60days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older60days) . '";
				}else if( type == "older90days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older90days) . '";
				}else if( type == "older120days" ){
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older120days) . '";
				}else{
					//s_obj.value = "";
					//e_obj.value = "";
				}
			}        
		</script>';
        return $sjsStr;
    }

    public function getVisibleCriteria($recordid = '') {
        global $mod_strings;
        global $app_strings;

        $adb = PearDatabase::getInstance();

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
            $name = str_replace(' ', '_', $filtername);
            if ($filtername == 'Private') {
                $FilterKey = 'Private';
                $FilterValue = getTranslatedString('PRIVATE_FILTER');
            } elseif ($filtername == 'Shared') {
                $FilterKey = 'Shared';
                $FilterValue = getTranslatedString('SHARE_FILTER');
            } else {
                $FilterKey = 'Public';
                $FilterValue = getTranslatedString('PUBLIC_FILTER');
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

    public function getShareInfo($recordid = '') {
        $adb = PearDatabase::getInstance();
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

    public function getGroupDetails($id) {
        $adb = PearDatabase::getInstance();
        $query = "select * from vtiger_groups where groupid = ?";
        $result = $adb->pquery($query, array($id));
        $num_rows = $adb->num_rows($result);
        if ($num_rows < 1)
            return null;
        $group_details = Array();
        $grpid = $adb->query_result($result, 0, 'groupid');
        $grpname = $adb->query_result($result, 0, 'groupname');
        $grpdesc = $adb->query_result($result, 0, 'description');
        $group_details = Array($grpid, $grpname, $grpdesc);
        return $group_details;
    }

    public function getRecordsListFromRequest(Vtiger_Request $request) {
        $folderId = $request->get('viewname');
        $module = $request->get('module');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $reportFolderModel = Reports_Folder_Model::getInstance();
        $reportFolderModel->set('folderid', $folderId);
        if ($reportFolderModel) {
            return $reportFolderModel->getRecordIds($excludedIds, $module);
        }
    }

    public function getKeyMetricsColumnOptions($record){
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $currentModuleName = "ITS4YouReports";
        $return = 1;
        $col_options = $column_str_val = "";
        if($record!=""){

            if ($request->get("view") == "EditKeyMetricsRow" && $request->has("id") && !$request->isEmpty("id")){
                $adb = PearDatabase::getInstance();
                $editResult = $adb->pquery("SELECT metrics_type, column_str FROM its4you_reports4you_key_metrics_rows WHERE id=?",array($request->get("id")));
                if ($adb->num_rows($editResult) > 0) {
                    $row = $adb->fetchByAssoc($editResult,0);
                    $column_str_val = $row["column_str"];
                    $metrics_type = $row["metrics_type"];
                }
            }

            if($metrics_type=="customview"){
                $col_options .= "<optgroup label='".vtranslate("LBL_COUNT",$currentModuleName)."'>";
                $col_options .= "<option value='COUNT' selected='selected' >".vtranslate("LBL_COUNT",$currentModuleName)." ".vtranslate("LBL_OF",$currentModuleName)." ".vtranslate("LBL_RECORDS",$currentModuleName)."</option>";
                $col_options .= "</optgroup>";
                $return = $col_options;
            }else{
                $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);
//error_reporting(63);ini_set("display_errors",1);
                if(isset($reportModel->report) && !empty($reportModel->report)){
                    if($reportModel->report->reportinformations["reports4youname"]!=""){
                        $reports4youname = $reportModel->report->reportinformations["reports4youname"];
                        if($reportModel->report->reportinformations["primarymodule"]!=""){
                            $primarymoduleid = $reportModel->report->reportinformations["primarymodule"];
                            $primarymodule = vtlib_getModuleNameById($primarymoduleid);

                            $countLbl = vtranslate("LBL_COUNT", $currentModuleName);
                            $all_summaries_otions = sgetSummariesOptions($primarymoduleid);
                            $summaries_otions[$countLbl] = $all_summaries_otions[$countLbl];

                            $selectedColumnsArray = $reportModel->getSelectedColumnListArray($record);
                            if(!empty($selectedColumnsArray)){
                                $calculation_types_array = array("SUM", "AVG", "MIN", "MAX",);
                                $adb = PearDatabase::getInstance();
                                foreach($selectedColumnsArray as $ci => $carray){
                                    $column_string = $carray["fieldcolname"];
                                    $columnRow_array = explode(":", $column_string);
                                    $last_key = count($columnRow_array) - 1;
                                    list($sc_tablename, $sc_columnname, $sc_modulestr) = explode(':', $column_string);
                                    list($sc_module) = explode('_', $sc_modulestr);
                                    $sc_module_id = getTabid($sc_module);
                                    if (is_numeric($columnRow_array[$last_key]) || in_array($columnRow_array[$last_key], ITS4YouReports::$customRelationTypes)) {
                                        $typeofdata = $columnRow_array[$last_key - 1];
                                        $fieldid = $columnRow_array[$last_key];
                                        $clear_tablename = trim($columnRow_array[0], "_$fieldid");
                                    }else{
                                        $typeofdata = $columnRow_array[$last_key];
                                        $clear_tablename = $columnRow_array[0];
                                    }

                                    //$adb->setDebug(true);
                                    $sc_field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype, fieldlabel FROM vtiger_field WHERE tablename = ? and columnname = ? and tabid=?", array($clear_tablename, $sc_columnname, $sc_module_id)), 0);
                                    //$adb->setDebug(false);
                                    if (in_array($sc_field_row["uitype"], array('7', '9', '71', '72', '712')) || ($sc_field_row["uitype"] == '1' && ($typeofdata == "N" || $typeofdata == "I" || $typeofdata == "NN"))) {
                                        $column_label = vtranslate($sc_field_row["fieldlabel"], $sc_module);
                                        foreach($calculation_types_array AS $calculation_type_str){
                                            $calculation_string = $column_string.":".$calculation_type_str;
                                            $calculation_label = vtranslate($calculation_type_str, $currentModuleName).' ' . vtranslate("LBL_OF", $currentModuleName).' '.$column_label;
                                            $summaries_otions[$column_label][] = array("value"=>$calculation_string,"text"=>$calculation_label);
                                        }
                                    }
                                }
                                //echo "<pre>";print_r($summaries_otions);echo "</pre>";
                            }

                            if(!empty($summaries_otions)){
                                foreach($summaries_otions AS $opt_column_group => $columns_array){
                                    if($opt_column_group!=""){
                                        $col_options .= "<optgroup label='$opt_column_group'>";
                                        foreach($columns_array as $ci => $column_array){
                                            $selected = "";
                                            $column_str = $column_array["value"];
                                            $column_lbl = $column_array["text"];

                                            if($column_str_val!="" && $column_str==$column_str_val){
                                                $selected = " selected='selected' ";
                                            }

                                            $col_options .= "<option value='$column_str' $selected >$column_lbl</option>";
                                        }
                                        $col_options .= "</optgroup>";
                                    }
                                }
                            }
                            $return = $col_options;
                        }
                    }
                }
            }
        }
        return $return;
    }

    private function saveQuickFilters($reportid, $qfColumns = array(), $report_array, $export_sql) {
        if (!empty($qfColumns)) {
            $adb = PearDatabase::getInstance();

            $d_qfColumns = "DELETE FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
            $d_qfColumnsResult = $adb->pquery($d_qfColumns, array($reportid));

            $i = 1;
            $savedQF = array();
            foreach ($qfColumns as $qfColumn) {
				$qfColumn = decode_html($qfColumn);
            	if (!in_array($qfColumn, $savedQF)) {
	                $qfColumnsSql = "INSERT INTO its4you_reports4you_selectqfcolumn (queryid,columnindex,columnname) VALUES (?,?,?)";
	                $export_sql === true ? $adb->setDebug(true) : "";
	                $params = array($reportid, $i, $qfColumn);
	                $params_tofile = array("@reportid", $i, $qfColumn);
	                $savedQF[] = $qfColumn;
	                $report_array['its4you_reports4you_summaries'][] = $params_tofile;
	                $qfColumnsResult = $adb->pquery($qfColumnsSql, $params);
	                $export_sql === true ? $adb->setDebug(false) : "";
                	$i++;
                }
            }
        }
        return $report_array;
    }

    /**
     * @param int   $reportId
     * @param array $maps
     */
    private static function saveMaps($reportId, $maps = []) {
        $db = PearDatabase::getInstance();

        if (!empty($reportId) && is_array($maps)) {
            $mapsJson = json_encode($maps);
            $db->pquery('INSERT INTO its4you_reports4you_osm_maps (reportid, maps_json) VALUES (?,?) 
                            ON DUPLICATE KEY UPDATE maps_json=?', [
                $reportId, $mapsJson, $mapsJson
            ]);
        }
    }

    public static function getDefaultSharingType()
    {
        return ITS4YouReports::getDefaultSharingType();
    }

    public static function getSharingTypes()
    {
        return ITS4YouReports::getSharingTypes();
    }

}
