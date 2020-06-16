<?php

require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/ITS4YouReports/GenerateObj.php');
require_once('modules/ITS4YouReports/helpers/Version.php');
require_once('modules/ITS4YouReports/utils/Reports4YouUtils.php');

function compareDeepValue($val1, $val2) {
    return strcmp($val1['value'], $val2['value']);
}

class ITS4YouReports extends CRMEntity {

    private $profilesActions;
    var $secondarymodules;
    var $relatedmodulesarray = array();
    var $record = '';
    var $currentModule = 'ITS4YouReports';
    public static $MODULE_NAME = 'ITS4YouReports';
    public static $dateNcomparators = array('olderNdays','lastNdays','nextNdays','moreNdays','daysago','daysmore',);
    var $Date_Filter_Values = array('custom' => 'Custom',
                                    'todayless' => 'Less than today',
                                    'yesterday' => 'Yesterday',
                                    'today' => 'Today',
                                    'tomorrow' => 'Tomorrow',
                                    'todaymore' => 'More than today',
                                    'lastweek' => 'Last Week',
                                    'thisweek' => 'Current Week',
                                    'nextweek' => 'Next Week',
                                    'lastmonth' => 'Last Month',
                                    'thismonth' => 'Current Month',
                                    'nextmonth' => 'Next Month',
                                    'olderNdays' => 'Less than N days',
                                    'lastNdays' => 'Last N days',
                                    'nextNdays' => 'Next N days',
                                    'moreNdays' => 'More than N days',
                                    'daysago' => 'Days ago',
                                    'daysmore' => 'Days ahead',
                                    'prevfq' => 'Previous FQ',
                                    'thisfq' => 'Current FQ',
                                    'nextfq' => 'Next FQ',
                                    'prevfy' => 'Previous FY',
                                    'thisfy' => 'Current FY',
                                    'nextfy' => 'Next FY',
                                    'af' => 'EQUALS_FLD',
                                    'nf' => 'NOT_EQUALS_TO_FLD',
                                    'lf' => 'LESS_THAN_FLD',
                                    'gf' => 'GREATER_THAN_FLD',
                                    'mf' => 'LESS_OR_EQUALS_FLD',
                                    'hf' => 'GREATER_OR_EQUALS_FLD',
                                    'isn' => 'is empty',
                                    'isnn' => 'is not empty',
    );

    public static $customRelationTypes = array('INV', 'MIF');

    public static $calculation_type_array = array('count', 'sum', 'avg', 'min', 'max');
    // new + users fields
    public static $s_users_uitypes = array('52', '53', '77', '120', '531',);
    //public static $s_uitypes = array('15', '16', '26', '33', '52', '53', '77',);
    public static $s_uitypes = array('15', '16', '26', '33', '52', '53', '56', '77', '117', '120', '531',);
    public $std_filter_columns = array();
    public $pri_module_columnslist = array();
    public static $adv_filter_options = array('e' => 'EQUALS',
                                              'n' => 'NOT_EQUALS_TO',
                                              's' => 'STARTS_WITH',
                                              'ew' => 'ENDS_WITH',
                                              'c' => 'CONTAINS',
                                              'k' => 'DOES_NOT_CONTAINS',
                                              'l' => 'LESS_THAN',
                                              'g' => 'GREATER_THAN',
                                              'm' => 'LESS_OR_EQUALS',
                                              'h' => 'GREATER_OR_EQUALS',
                                              'bw' => 'BETWEEN',
        //'nbw' => 'not between',
                                              'a' => 'AFTER',
                                              'b' => 'BEFORE',
    );

    public static $is_inventory_module = array();
    public static $modTrackerColumns = array('crmid', 'module', 'whodid', 'changedon', 'status','fieldname','prevalue','postvalue',);
    public static $modTrackerColumnsArray = array(
        'crmid'=>'',
        'module'=>'vtiger_modtracker_basic:module:ModTracker_Module:module:V',
        'whodid'=>'vtiger_modtracker_basic:whodid:ModTracker_Assigned To:whodid:V',
        'changedon'=>'vtiger_modtracker_basic:changedon:ModTracker_Changed On:changedon:DT',
        'status'=>'vtiger_modtracker_basic:status:ModTracker_Status:status:V',
        'fieldname'=>'vtiger_modtracker_detail:fieldname:ModTracker_Field Name:fieldname:V',
        'prevalue'=>'vtiger_modtracker_detail:prevalue:ModTracker_Prevalue:prevalue:V',
        'postvalue'=>'vtiger_modtracker_detail:postvalue:ModTracker_Postvalue:postvalue:V',
    );
    // ITS4YOU-UP SlOl | 24.8.2015 16:24
    // I will call this new inventory fields ps_variablename to get better chance that i will have got unique alias
    // celkova cena -> PRODUCTTOTAL -> ps_producttotal
    // celkom po zlave -> PRODUCTSTOTALAFTERDISCOUNT -> ps_productstotalafterdiscount
    // DPH -> PRODUCTVATSUM -> ps_productvatsum
    // celkova cena s DPH -> PRODUCTTOTALSUM -> ps_producttotalsum
    // kategoria -> PRODUCTCAREGORY -> ps_productcategory
    // cislo produktu/sluzby -> PRODUCTNO -> ps_productno
    public static $intentory_fields = array('ps_producttotalvatsum','s_h_amount','subtotal','salescommission','total','pre_tax_total','prodname', 'quantity', 'listprice', 'discount', 'comment', 'ps_producttotal', 'ps_productstotalafterdiscount', 'ps_productvatsum', 'ps_producttotalsum', 'ps_productcategory', 'ps_productno', 'ps_profit');
    public static $intentory_skip_formating = ['subject','prodname','comment','ps_productcategory','ps_productno'];
    // see GenerateObj.php definition too
    public static $sp_date_options = array('todaymore', 'todayless', 'older1days', 'older7days', 'older15days', 'older30days', 'older60days', 'older90days', 'older120days');
    public static $fld_date_options =  array('af', 'nf', 'lf', 'gf', 'mf', 'hf',);
    public $adv_sel_fields = array();
    private $current_language = '';
    private $app_strings = array();
    private $current_user_profileGlobalPermission = '';
    // ITS4YOU-CR SlOl 21. 3. 2016 11:25:03
    public static $dashboardUrl = 'index.php?module=ITS4YouReports&view=ShowWidget&name=GetReports&record=';
    // ITS4YOU-CR SlOl 7. 4. 2016 12:51:23
    public $hyperlinks_fields = array();

    private $taxtotalUsedInBlock = false;

    // constructor of Reports4You class
    function __construct($run_construct = true, $reportid = "") {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $this->executeWidgetLinks();

        // ITS4YOU-UP SlOl 19. 11. 2015 11:40:26
        ITS4YouReports::checkITS4YouUpdates();
        // ITS4YOU-END

        GenerateObj::checkInstallationMemmoryLimit();

        if (!$reportid && $request->has('record')) {
            $reportid = $request->get('record');
        }
        if ($request->get("view") != "Detail") {
            $run_construct = true;
        }

        if ($reportid && $run_construct === true && (!$request->has('mode') || $request->get('mode') != "ajax")) {
            $this->setITS4YouReport($reportid);
        }

        // WIDGET LINKS FIX !!! s
        $this->fix_widget_labels();
        // WIDGET LINKS FIX !!! e

    }

    protected function setAppLanguage() {
        if (R_DEBUG) {
            $this->sshow("START RT");
            $this->getR4UDifTime(0);
        }
        $this->current_language = (isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != "" ? $_SESSION['authenticated_user_language'] : "en_us");
        if (isset($this->current_language) && $this->current_language != "") {
            $this->app_strings = return_application_language($this->current_language);
        }
    }

    public static function isInventoryModule($module){

        $class_name = $module."_Module_Model";

        if (class_exists($class_name)) {
            if (is_subclass_of($class_name, 'Inventory_Module_Model')) {
                self::$is_inventory_module[$module] =  true;
            } else {
                self::$is_inventory_module[$module] = false;
            }
        }

        return self::$is_inventory_module[$module];
    }

    protected function fixCurrentUser($userId) {
        $seed_user = new Users();
        return $seed_user->retrieveCurrentUserInfoFromFile($userId);
    }

    protected function setITS4YouReport($reportid) {

        $current_user_parent_role_seq = $profileGlobalPermission = '';

        $this->define_rt_vars(false);

        $this->setAppLanguage();

        if (R_DEBUG) {
            $this->getR4UDifTime(1);
        }

        $this->db = PearDatabase::getInstance();

        if (isset($current_user)) {
            $this->setCurrentUser();
        }

        if (R_DEBUG) {
            $this->getR4UDifTime(2);
        }

        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';

        if (R_DEBUG) {
            $this->getR4UDifTime(3);
        }

        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
            $this->current_user_parent_role_seq = $current_user_parent_role_seq;
            $this->current_user_profileGlobalPermission = $profileGlobalPermission;
        }

        // array of action names used in profiles permissions
        $this->profilesActions = array("EDIT" => "EditView", // Create/Edit
                                       "DETAIL" => "DetailView", // View
            // MASS Delete Canceled "DELETE" => "Delete", // Delete
        );

        $this->profilesPermissions = array();

        if (isset($_REQUEST["record"]) && $_REQUEST['record'] != '' && $_REQUEST['view']!="ShowKeyMetrics") {
            $reportid = vtlib_purify($_REQUEST["record"]);
        }

        if (R_DEBUG) {
            $this->getR4UDifTime(4);
        }

        if (isset($reportid) && $reportid != '') {
            $this->record = $reportid;
            $rep_sql = "SELECT * FROM its4you_reports4you 
                            LEFT JOIN its4you_reports4you_modules ON its4you_reports4you_modules.reportmodulesid = its4you_reports4you.reports4youid 
                            INNER JOIN its4you_reports4you_settings ON its4you_reports4you.reports4youid = its4you_reports4you_settings.reportid 
                            WHERE its4you_reports4you.reports4youid = ?";
            $rep_result = $this->db->pquery($rep_sql, array($this->record));
            $report = array();
            $report = $this->db->fetchByAssoc($rep_result, 0);

            if (R_DEBUG) {
                $this->getR4UDifTime(5);
            }
            // ITS4YOU-BF SlOl 18. 2. 2014 9:29:51 if there are duplicated modules saved in secondary modules string 
            $new_arr_relatedmodules = array();
            $to_check = explode(":", $report["secondarymodules"]);
            foreach ($to_check as $key => $modulestr) {
                if (!in_array($modulestr, $new_arr_relatedmodules)) {
                    $new_arr_relatedmodules[] = $modulestr;
                }
            }
            $report["secondarymodules"] = implode(":", $new_arr_relatedmodules);
            // ITS4YOU-END 18. 2. 2014 9:29:55

            if (R_DEBUG) {
                $this->getR4UDifTime(6);
            }

            $selectcolumn_sql = "SELECT * FROM its4you_reports4you_selectcolumn WHERE queryid = ?";
            $selectcolumn_result = $this->db->pquery($selectcolumn_sql, array($this->record));
            $selectedColumnsString = "";
            while ($selectcolumn_val = $this->db->fetchByAssoc($selectcolumn_result)) {
                $selectedColumnsString .= $selectcolumn_val["columnname"] . ";";
            }
            $report["selectedColumnsString"] = $selectedColumnsString;

            if (R_DEBUG) {
                $this->getR4UDifTime(7);
            }

            $selectQFcolumn_sql = "SELECT * FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
            $selectQFcolumn_result = $this->db->pquery($selectQFcolumn_sql, array($this->record));
            $selectedQFColumnsString = "";
            while ($selectQFcolumn_val = $this->db->fetchByAssoc($selectQFcolumn_result)) {
                $selectedQFColumnsString .= $selectQFcolumn_val["columnname"] . ";";
            }
            $report["selectedQFColumnsString"] = $selectedQFColumnsString;
            $sort1 = $sort2 = $sort3 = array();

            if (R_DEBUG) {
                $this->getR4UDifTime(8);
            }

            $sort_by1sql = "SELECT * FROM its4you_reports4you_sortcol WHERE SORTCOLID=? AND REPORTID=?";
            $sort_by1result = $this->db->pquery($sort_by1sql, array('1', $this->record));
            $sort1 = $this->db->fetchByAssoc($sort_by1result, 0);
            if (!empty($sort1)) {
                $report["Group1"] = $sort1["columnname"];
                $report["Sort1"] = $sort1["sortorder"];
                $report["timeline_columnstr1"] = $sort1["timeline_columnstr"] . "@vlv@" . $sort1["timeline_columnfreq"];
                $report["timeline_columnfreq1"] = $sort1["timeline_columnfreq"];
                $report["timeline_type1"] = $sort1["timeline_type"];
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(9);
            }

            $sort_by2sql = "SELECT * FROM its4you_reports4you_sortcol WHERE SORTCOLID=? AND REPORTID=?";
            $sort_by2result = $this->db->pquery($sort_by2sql, array('2', $this->record));
            $sort2 = $this->db->fetchByAssoc($sort_by2result, 0);
            if (!empty($sort2)) {
                $report["Group2"] = $sort2["columnname"];
                $report["Sort2"] = $sort2["sortorder"];
                $report["timeline_columnstr2"] = $sort2["timeline_columnstr"] . "@vlv@" . $sort2["timeline_columnfreq"];
                $report["timeline_type2"] = $sort2["timeline_type"];
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(10);
            }

            $sort_by3sql = "SELECT * FROM its4you_reports4you_sortcol WHERE SORTCOLID=? AND REPORTID=?";
            $sort_by3result = $this->db->pquery($sort_by3sql, array('3', $this->record));
            $sort3 = $this->db->fetchByAssoc($sort_by3result, 0);
            if (!empty($sort3)) {
                $report["Group3"] = $sort3["columnname"];
                $report["Sort3"] = $sort3["sortorder"];
                $report["timeline_columnstr3"] = $sort3["timeline_columnstr"] . "@vlv@" . $sort3["timeline_columnfreq"];
                $report["timeline_type3"] = $sort3["timeline_type"];
            }

            // in case display report type 2 is cols and 3 is rows I will Switch to R-R-C Report
            if ($_REQUEST["view"] !== "Edit" && $report["timeline_type2"] == "cols" && $report["timeline_type3"] == "rows" && $report["Group3"] != "" && $report["Group3"] != "none") {
                $cols_type = $report["timeline_type2"];
                $cols_Group = $report["Group2"];
                $cols_Sort = $report["Sort2"];
                $cols_ColumnStr = $report["timeline_columnstr2"];

                $rows_type = $report["timeline_type3"];
                $rows_Group = $report["Group3"];
                $rows_Sort = $report["Sort3"];
                $rows_ColumnStr = $report["timeline_columnstr3"];
                $report["timeline_type2"] = $rows_type;
                $report["Group2"] = $rows_Group;
                $report["Sort2"] = $rows_Sort;
                $report["timeline_columnstr2"] = $rows_ColumnStr;
                $report["timeline_type3"] = $cols_type;
                $report["Group3"] = $cols_Group;
                $report["Sort3"] = $cols_Sort;
                $report["timeline_columnstr3"] = $cols_ColumnStr;
            }
            // in case display report type 2 is cols and 3 is rows

            if (R_DEBUG) {
                $this->getR4UDifTime(11);
            }

            $sort_by_columnsql = "SELECT * FROM its4you_reports4you_sortcol WHERE SORTCOLID=? AND REPORTID=?";
            $sort_by_columnresult = $this->db->pquery($sort_by_columnsql, array(4, $this->record));
            // ITS4YOU-UP SlOl 24. 5. 2016 6:25:34
            while ($sort_col_row = $this->db->fetchByAssoc($sort_by_columnresult)) {
                $sort_col_arr["SortByColumn"] = $sort_col_row["columnname"];
                $sort_col_arr["SortOrderColumn"] = $sort_col_row["sortorder"];
                $report["sortOrderArray"][] = $sort_col_arr;
            }
            // ITS4YOU-END

            $datefilter_sql = "SELECT * FROM its4you_reports4you_datefilter WHERE datefilterid=?";
            $datefilter_result = $this->db->pquery($datefilter_sql, array($this->record));
            $datefilter = $this->db->fetchByAssoc($datefilter_result, 0);
            $report["stdDateFilterField"] = $datefilter["datecolumnname"];
            $report["stdDateFilter"] = $datefilter["datefilter"];
            $report["startdate"] = $datefilter["startdate"];
            $report["enddate"] = $datefilter["enddate"];

            if (R_DEBUG) {
                $this->getR4UDifTime(12);
            }

            $summary_sql = "SELECT * FROM its4you_reports4you_summary WHERE reportsummaryid=?";
            $summary_result = $this->db->pquery($summary_sql, array($this->record));
            while ($summary = $this->db->fetchByAssoc($summary_result)) {
                $report["columnstototal"][] = $summary;
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(13);
            }

            $advft_criteria_sql = "SELECT * FROM its4you_reports4you_relcriteria WHERE queryid=?";
            $advft_criteria_result = $this->db->pquery($advft_criteria_sql, array($this->record));
            while ($advft_criteria = $this->db->fetchByAssoc($advft_criteria_result)) {
                $report["advft_criteria"][] = $advft_criteria;
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(14);
            }

            $advft_criteria_groups_sql = "SELECT * FROM its4you_reports4you_relcriteria_grouping WHERE queryid=?";
            $advft_criteria_groups_result = $this->db->pquery($advft_criteria_groups_sql, array($this->record));
            while ($advft_criteria_groups = $this->db->fetchByAssoc($advft_criteria_groups_result)) {
                $report["advft_criteria_groups"][] = $advft_criteria_groups;
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(15);
            }

            $summaries_sql = "SELECT * FROM its4you_reports4you_summaries WHERE reportsummaryid=?";
            $summaries_result = $this->db->pquery($summaries_sql, array($this->record));
            while ($summaries = $this->db->fetchByAssoc($summaries_result)) {
                $report["summaries_columns"][] = $summaries;
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(16);
            }

            $charts_sql = "SELECT * FROM its4you_reports4you_charts WHERE reports4youid=?";
            $charts_result = $this->db->pquery($charts_sql, array($this->record));
            $charttype = $dataseries = $charttitle = "";
            if ($this->db->num_rows($charts_result) > 0) {
                while ($charts_row = $this->db->fetchByAssoc($charts_result)) {
                    $report["charts"][$charts_row["chart_seq"]] = $charts_row;
                }
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(17);
            }

            $summaries_orderby_sql = "SELECT * FROM  its4you_reports4you_summaries_orderby WHERE reportid=?";
            $summaries_orderby_result = $this->db->pquery($summaries_orderby_sql, array($this->record));
            while ($summaries_orderby = $this->db->fetchByAssoc($summaries_orderby_result)) {
                $this->reportinformations["summaries_orderby_columns"][0] = array("column" => $summaries_orderby["summaries_orderby"], "type" => $summaries_orderby["summaries_orderby_type"]);
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(18);
            }

            $sharing_sql = "SELECT * FROM its4you_reports4you_settings WHERE reportid=?";
            $sharing_result = $this->db->pquery($sharing_sql, array($this->record));
            $sharing = $this->db->fetchByAssoc($sharing_result, 0);
            $report["template_owner"] = $sharing["owner"];
            $report["sharing"] = $sharing["sharingtype"];
            if ($report["sharing"] == "share") {
                $share_sql = "SELECT shareid, setype FROM  its4you_reports4you_sharing WHERE reports4youid = ? ORDER BY setype ASC";
                $share_result = $this->db->pquery($share_sql, array($this->record));
                $memberArray = array();
                while ($share_row = $this->db->fetchByAssoc($share_result)) {
                    $memberArray[] = $share_row["setype"] . "::" . $share_row["shareid"];
                }
                $this->reportinformations["members_array"] = $memberArray;
            }

            $qf_sql = "SELECT * FROM its4you_reports4you_selectqfcolumn WHERE queryid=? ORDER BY columnindex";
            $qf_result = $this->db->pquery($qf_sql, array($this->record));
            if ($this->db->num_rows($qf_result) > 0) {
                while ($qf_row = $this->db->fetchByAssoc($qf_result)) {
                    $report["quick_filters"][$qf_row["columnindex"]] = $qf_row['columnname'];
                }
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(19);
            }

            foreach ($report as $key => $value) {
                $this->reportinformations[$key] = $value;
            }

            if (R_DEBUG) {
                $this->getR4UDifTime(20);
            }

            // ITS4YOU-CR SlOl 1/12/2014 12:16:43 PM TEST GET FILTERS INTO REPORTS4YOU OBJECT
            $this->getSelectedStandardCriteria($this->record);
            // ITS4YOU-END 1/12/2014 12:16:45 PM
            $this->selected_columns_list_arr = $this->getSelectedColumnListArray($this->record);
            //$this->selected_columns_list_arr = explode(";", html_entity_decode($selectedColumnsString, ENT_QUOTES, $default_charset));

            $maps = [];
            $mapsResult = $this->db->pquery('SELECT maps_json FROM its4you_reports4you_osm_maps WHERE reportid = ?', [$this->record]);
            if ($this->db->num_rows($mapsResult)) {
                $mapsRow = $this->db->fetchByAssoc($mapsResult);
                $maps = json_decode(html_entity_decode($mapsRow['maps_json'], ENT_QUOTES, vglobal('default_charset')), true);
            }
            $this->reportinformations['maps'] = $maps;
        }

        if (R_DEBUG) {
            $this->getR4UDifTime(22);
        }
        if (!isset($this->module_list) || empty($this->module_list)) {
// CANCELED            $this->initListOfModules();
        }
        if (R_DEBUG) {
            $this->getR4UDifTime(23);
        }
        // ITS4YOU-CR SlOl 30. 8. 2013 13:51:52
        if (isset($this->reportinformations["reports4youname"]) && $this->reportinformations["reports4youname"] != '') {
            $this->reportname = $this->reportinformations["reports4youname"];
        }
        if (isset($this->reportinformations["reporttype"]) && $this->reportinformations["reporttype"] != '') {
            $this->reporttype = $this->reportinformations["reporttype"];
        }

        if (isset($this->reportinformations["description"]) && $this->reportinformations["description"] != '') {
            $this->reportdesc = $this->reportinformations["description"];
        }

        if (R_DEBUG) {
            $this->getR4UDifTime(24);
        }
        if (isset($this->reportinformations["primarymodule"]) && $this->reportinformations["primarymodule"] != '') {
            $this->primarymoduleid = $this->reportinformations["primarymodule"];
            $this->primarymodule = vtlib_getModuleNameById($this->reportinformations["primarymodule"]);
            $p_focus = CRMEntity::getInstance($this->primarymodule);
            $this->reportinformations["list_link_field"] = $p_focus->list_link_field;
            $this->getPriModuleColumnsList($this->primarymodule);
        }
        if (R_DEBUG) {
            $this->getR4UDifTime(25);
        }
        $this->folder = ((isset($this->reportinformations["folderid"]) && $this->reportinformations["folderid"] != '') ? $this->reportinformations["folderid"] : '');

        if (R_DEBUG) {
            $this->getR4UDifTime(26);
        }
        $subordinate_users = ITS4YouReports::getSubOrdinateUsersArray();

        // Update subordinate user information for re-use
        $edit_all = $this->current_user_profileGlobalPermission[1];
        if (is_admin($this->current_user) == true || $edit_all == 0 || in_array($report["template_owner"], $subordinate_users) || $report["template_owner"] == $this->current_user->id) {
            $this->is_editable = 'true';
        } else {
            $this->is_editable = 'false';
        }

        $this->Group1 = ((isset($this->reportinformations["Group1"]) && $this->reportinformations["Group1"] != '') ? $this->reportinformations["Group1"] : '');
        $this->Sort1 = ((isset($this->reportinformations["Sort1"]) && $this->reportinformations["Sort1"] != '') ? $this->reportinformations["Sort1"] : '');
        $this->Group2 = ((isset($this->reportinformations["Group2"]) && $this->reportinformations["Group2"] != '') ? $this->reportinformations["Group2"] : '');
        $this->Sort2 = ((isset($this->reportinformations["Sort2"]) && $this->reportinformations["Sort2"] != '') ? $this->reportinformations["Sort2"] : '');
        $this->Group3 = ((isset($this->reportinformations["Group3"]) && $this->reportinformations["Group3"] != '') ? $this->reportinformations["Group3"] : '');
        $this->Sort3 = ((isset($this->reportinformations["Sort3"]) && $this->reportinformations["Sort3"] != '') ? $this->reportinformations["Sort3"] : '');

        if (R_DEBUG) {
            $this->getR4UDifTime(27);
        }
        if (isset($this->reportinformations["secondarymodules"]) && $this->reportinformations["secondarymodules"] != '') {
            $this->relatedmodulesstring = trim($this->reportinformations["secondarymodules"], ':');
            $arr_relatedmodules = explode(':', $this->relatedmodulesstring);

            $this->relatedmodulesarray = (!empty($arr_relatedmodules)) ? $arr_relatedmodules : array();

            $this->getSecModuleColumnsList($this->relatedmodulesstring);
        }
        // ITS4YOU-END 30. 8. 2013 13:51:54 

        if (R_DEBUG) {
            $this->getR4UDifTime(28);
        }
        if (isset($_REQUEST['reportname']) && $_REQUEST['reportname'] != '') {
            $this->reportname = $_REQUEST['reportname'];
        }
        if (isset($_REQUEST['reportdesc']) && $_REQUEST['reportdesc'] != '') {
            $this->reportdesc = $_REQUEST['reportdesc'];
        }

        if (isset($_REQUEST['primarymodule']) && $_REQUEST['primarymodule'] != '') {
            $this->primarymoduleid = $_REQUEST['primarymodule'];
            $this->primarymodule = vtlib_getModuleNameById($this->primarymoduleid);

            $this->getPriModuleColumnsList($this->primarymodule);
        }

        if (R_DEBUG) {
            $this->getR4UDifTime(29);
        }
        $this->folder = (isset($_REQUEST['folderid']) && $_REQUEST['folderid'] != '') ? $_REQUEST['folderid'] : '';

        if (isset($_REQUEST['relatedmodules']) && $_REQUEST['relatedmodules'] != '') {
            $this->relatedmodulesstring = trim($_REQUEST['relatedmodules'], ':');
            $arr_relatedmodules = explode(':', $this->relatedmodulesstring);
            $this->relatedmodulesarray = (!empty($arr_relatedmodules)) ? $arr_relatedmodules : array();

            $this->getSecModuleColumnsList($this->relatedmodulesstring);
        }
        if (R_DEBUG) {
            $this->getR4UDifTime(30);
        }

        if($this->reportinformations["reporttype"]=="custom_report"){
            global $current_user;
            if (!is_admin($current_user) && !ITS4YouReports::CheckSharing($reportid)) {
                ITS4YouReports::DieDuePermission();
                exit;
            }
            if (R_DEBUG) {
                $this->getR4UDifTime(31);
            }
            $custom_sql_qry = "SELECT * FROM its4you_reports4you_customsql WHERE reports4youid=?";
            $custom_sql_result = $this->db->pquery($custom_sql_qry, array($this->record));
            $custom_sql = $this->db->fetchByAssoc($custom_sql_result, 0);
            $this->reportinformations["custom_sql"] = $custom_sql["custom_sql"];
        }

        // ITS4YOU-CR SlOl 18. 3. 2016 13:43:38
        if($this->reportinformations["reporttype"]!="custom_report"){
            $dashboardssql = "SELECT primary_search,allow_in_modules FROM its4you_reports4you_widget_search WHERE reportid=?";
            $dashboardsresult = $this->db->pquery($dashboardssql, array($this->record));
            if($this->db->num_rows($dashboardsresult)>0){
                $dashboards = $this->db->fetchByAssoc($dashboardsresult, 0);
                $this->reportinformations["primary_search"] = $dashboards["primary_search"];
                $this->reportinformations["allow_in_modules"] = $dashboards["allow_in_modules"];
            }
            // ITS4YOU-CR SlOl 18. 5. 2016 13:22:55
            $cc_array = array();
            $cc_res = $this->db->pquery('SELECT calculation_label cc_name, 
                                                calculation_expression cc_calculation,
                                                calculation_totals cc_totals 
                                            FROM its4you_reports4you_custom_calculations 
                                            WHERE reportid = ?',array($this->record));
            if($this->db->num_rows($cc_res)>0){
                while($cc_row = $this->db->fetchByAssoc($cc_res)) {
                    $cc_row['cc_totals_hidden'] = $cc_row['cc_totals'];
                    $cc_row['cc_totals'] = explode(',', $cc_row['cc_totals']);
                    $cc_array[] = $cc_row;
                }
            }
            $this->reportinformations["cc_array"] = $cc_array;
        }
        // ITS4YOU-END 


        if (isset($this->record) && $this->record != "" && $_REQUEST['view']!="ShowKeyMetrics") {
            $r4u_sesstion_name = $this->getITS4YouReportStoreName();
            //session_start();
            //session_register($r4u_sesstion_name);
            $_SESSION[$r4u_sesstion_name] = serialize($this);
        }
    }

    function vtlib_handler($modulename, $event_type) {
        switch ($event_type) {
            case "module.postinstall":
                $this->executeSql();
                $this->RegisterReports4YouScheduler();
                $this->executeWidgetLinks();
                $this->executeActionLinks();
                self::setModuleParentTab();
                break;
            case "module.disabled":
                break;
            case "module.enabled":
                self::setModuleParentTab();
                break;
            case "module.preuninstall":
                // TODO Handle actions when this module is about to be deleted.
                break;
            case "module.preupdate":
                // TODO Handle actions before this module is updated.
                break;
            case "module.postupdate":
                // TODO Handle actions after this module is updated
                $this->executeNewTables();
                $this->executeActionLinks();
                self::setModuleParentTab();
                break;
            case "module.license_activated":
                self::setModuleParentTab();
                break;
            case "module.license_deactivated":
                break;
        }
    }

    public static function executeSql() {
        $adb = PEARDatabase::getInstance();
        $result = $adb->query("SELECT id FROM its4you_reports4you_selectquery_seq");
        if ($adb->num_rows($result) < 1) {
            $adb->query("INSERT INTO its4you_reports4you_selectquery_seq VALUES('0')");
        }
    }

    private function executeNewTables(){
        $adb = PEARDatabase::getInstance();
        $adb->query("CREATE TABLE IF NOT EXISTS `its4you_reports4you_customsql` (
                      `reports4youid` int(11) NOT NULL,
                      `custom_sql` text NOT NULL,
                      PRIMARY KEY (`reports4youid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $adb->query("CREATE TABLE IF NOT EXISTS `its4you_reports4you_mig_four` (
                      `reports4youid` int(11) NOT NULL,
                      `custom_sql` text NOT NULL,
                      PRIMARY KEY (`reports4youid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    private function executeWidgetLinks() {
        require_once('vtlib/Vtiger/Module.php');

        $moduleName = "Home";

        $adb = PEARDatabase::getInstance();

        $link_module = Vtiger_Module::getInstance($moduleName);
//global $adb;$adb->setDebug(true);
        // ITS4YouReportsHighcharts
        $link_label = "ITS4YouReportsHighcharts";
        $result1 = $adb->pquery("SELECT linkid FROM vtiger_links WHERE linklabel=?", array($link_label));
        $exist1 = $adb->num_rows($result1);
        if ($exist1 <= 0){
            $link_url = 'modules/ITS4YouReports/highcharts/js/highcharts.js';
            $link_module->addLink('HEADERSCRIPT',$link_label,$link_url,'','');
        }

        // ITS4YouReportsHighchartsFunnel
        $link_label = "ITS4YouReportsHighchartsFunnel";
        $result2 = $adb->pquery("SELECT linkid FROM vtiger_links WHERE linklabel=?", array($link_label));
        $exist2 = $adb->num_rows($result2);
        if ($exist2 <= 0){
            $link_url = 'modules/ITS4YouReports/highcharts/js/modules/funnel.js';
            $link_module->addLink('HEADERSCRIPT',$link_label,$link_url,'','');
        }

        // ITS4YouReportsHighchartsExporting
        $link_label = "ITS4YouReportsHighchartsExporting";
        $result3 = $adb->pquery("SELECT linkid FROM vtiger_links WHERE linklabel=?", array($link_label));
        $exist3 = $adb->num_rows($result3);
        if ($exist3 <= 0){
            $link_url = 'modules/ITS4YouReports/highcharts/js/modules/exporting.js';
            $link_module->addLink('HEADERSCRIPT',$link_label,$link_url,'','');
        }
//$adb->setDebug(false);
        return true;
    }

    private function executeActionLinks() {
        require_once('vtlib/Vtiger/Module.php');

        $moduleName = get_class($this);

        $db = PEARDatabase::getInstance();

        $reportModel = Vtiger_Module_Model::getInstance($moduleName);
        $reportTabId = $reportModel->getId();

        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_ADD_RECORD', '', '', '0');

        $reportAddRecordLink = $db->pquery('SELECT linkid FROM vtiger_links WHERE tabid=? AND linklabel=?', array($reportTabId, 'LBL_ADD_RECORD'));
        $parentLinkId = $db->query_result($reportAddRecordLink, 0, 'linkid');

        $reportModelHandler = ['path' => 'modules/ITS4YouReports/models/Module.php', 'class' => 'ITS4YouReports_Module_Model', 'method' => 'checkLinkAccess'];
        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_TABULAR_REPORT', 'javascript:ITS4YouReports_List_Js.addReport("' . $reportModel->getCreateRecordUrl() . '&reporttype=tabular")', '', '0', $reportModelHandler, $parentLinkId);
        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_SUMMARIES_REPORT', 'javascript:ITS4YouReports_List_Js.addReport("' . $reportModel->getCreateRecordUrl() . '&reporttype=summaries")', '', '0', $reportModelHandler, $parentLinkId);
        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_SUMMARIES_WITH_DETAILS_REPORT', 'javascript:ITS4YouReports_List_Js.addReport("' . $reportModel->getCreateRecordUrl() . '&reporttype=summaries_w_details")', '', '0', $reportModelHandler, $parentLinkId);
        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_SUMMARIES_MATRIX_REPORT', 'javascript:ITS4YouReports_List_Js.addReport("' . $reportModel->getCreateRecordUrl() . '&reporttype=summaries_matrix")', '', '0', $reportModelHandler, $parentLinkId);
        Vtiger_Link::addLink($reportTabId, 'LISTVIEWBASIC', 'LBL_SELECT_TYPE_VIEW', 'javascript:ITS4YouReports_List_Js.addReport("' . $reportModel->getCreateRecordUrl() . '")', '', '0', $reportModelHandler, $parentLinkId);

        return true;
    }

    public static function define_rt_vars($r_defug = false, $directDebug = false) {
        if ($r_defug || $directDebug === true) {
            //define("RT_START",time());
            list($usec, $sec) = explode(' ', microtime());
            define("RT_START", $sec + $usec);
            if ($directDebug === true) {
                ITS4YouReports::sshow("START- " . RT_START);
            }
        }
        if ($directDebug === false) {
            define("R_DEBUG", $r_defug);
        }
        define("R2_DEBUG", true);
    }

    public static function getR4UDifTime($t_txt = "", $directDebug = false) {

        if (R2_DEBUG || $directDebug == true) {
            if ($t_txt != "") {
                $t_txt .= " ";
            }
            //$c_time = time();
            list($usec, $sec) = explode(' ', microtime());
            $c_time = $sec + $usec;
            echo "<pre>" . $t_txt . "TIME: " . ($c_time - RT_START) . "</pre>";
        }
        return true;
    }

    public static function unsetITS4YouReportsSerialize($ses_name = "") {
        if ($ses_name != "") {
            unset($_SESSION[$ses_name]);
        } else {
            foreach ($_SESSION as $ses_name => $ses_arr) {
                if (strpos($ses_name, "ITS4You") !== false) {
                    unset($_SESSION[$ses_name]);
                }
            }
        }
        return true;
    }

    public static function getITS4YouReportStoreName() {
        global $current_user;
        $r4u_sesstion_name = "";
        if (isset($_REQUEST["record"]) && $_REQUEST["record"] != "") {
            $reportid = vtlib_purify($_REQUEST["record"]);
            //ITS4YouReports::unsetITS4YouReportsSerialize();exit;
            $r4u_sesstion_name = "ITS4YouReport_" . $reportid;
            if (isset($current_user)) {
                $r4u_sesstion_name .= "_" . $current_user->id;
            }
            $input_args = func_get_args();
            if (!empty($input_args)) {
                foreach ($input_args as $input) {
                    $r4u_sesstion_name .= "_$input";
                }
            }
        }
        return $r4u_sesstion_name;
    }

    public static function getStoredITS4YouReport() {
        $r4u_sesstion_name = ITS4YouReports::getITS4YouReportStoreName();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        // used to unlink sessioned reports !
        if ($request->has("refresh") && $request->get('refresh') === "true") {
            ITS4YouReports::sshow(ITS4YouReports::unsetITS4YouReportsSerialize($r4u_sesstion_name));
        }
        // to unlink all
        if ($request->has("mode") && $request->get('mode') === "ChangeSteps") {
            $run_construct = false;
        } else {
            if ($request->has("view") && $request->get('view') === "Edit" && isset($_SESSION[$r4u_sesstion_name])) {
                $run_construct = false;
            } else {
                $run_construct = true;
            }
        }
        if (ITS4YouReports::isStoredITS4YouReport() === true) {
            $return_obj = unserialize($_SESSION[$r4u_sesstion_name]);
        }else{
            $return_obj = new ITS4YouReports($run_construct);
        }
        if (isset($return_obj->reportinformations["deleted"]) && $return_obj->reportinformations["deleted"] !== 0 && $return_obj->reportinformations["deleted"] !== "0") {
            die("<br><br><center>" . vtranslate('LBL_RECORD_DELETE') . " <a href='javascript:window.history.back()'>" . vtranslate('LBL_GO_BACK') . ".</a></center>");
        }
        if ($request->has("record") && !$request->isEmpty("record")) {
            $return_obj->primarymoduleid = $return_obj->reportinformations["primarymodule"];
            $return_obj->primarymodule = vtlib_getModuleNameById($return_obj->primarymoduleid);
        }

        return $return_obj;
    }

    public static function isStoredITS4YouReport() {
        $r4u_sesstion_name = ITS4YouReports::getITS4YouReportStoreName();
        if ($r4u_sesstion_name != "" && isset($_SESSION[$r4u_sesstion_name]) && !empty($_SESSION[$r4u_sesstion_name])) {
            return true;
        }
        return false;
    }

    public static function sshow($variable = array(), $useVarDump=false) {
        echo "<pre>";
        if ($useVarDump) {
            var_dump($variable);
        } else {
            print_r($variable);
        }
        echo "</pre>";
    }

    public function getPrimaryModules() {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has("primarymodule") && $request->get('primarymodule') !== "") {
            $this->primarymoduleid = $request->get("primarymodule");
        }

        $p_key = 0;
        if (!isset($this->module_list) || empty($this->module_list)) {
            $this->initListOfModules();
        }
        foreach ($this->module_list as $modulename => $moduleblocks) {
            $moduleid = getTabid($modulename);
            $m_return = array();
            $m_return['id'] = $moduleid;
            /* if (in_array($modulename, array("Calendar", "PBXManager"))) {
              $module_lbl = vtranslate($modulename, $modulename);
              } else {
              $module_lbl = vtranslate("SINGLE_$modulename", $modulename);
              } */
            $module_lbl = vtranslate($modulename, $modulename);
            $m_return['module'] = $module_lbl;
            $m_return['selected'] = ($this->primarymoduleid == $moduleid) ? 'selected' : '';
            $t_return[$module_lbl] = $m_return;
            $p_key++;
        }
        ksort($t_return);
        $return = array();
        $t_i = 0;
        foreach ($t_return as $m_arr) {
            $return[$t_i] = $m_arr;
            $t_i++;
        }

        if ($this->primarymoduleid == "") {
            $return[0]['selected'] = "selected";
        }

        return $return;
    }

    public function getReportFolders() {
        $adb = PearDatabase::getInstance();
        $sql = "select * from  its4you_reports4you_folder";
        $result = $adb->query($sql);
        $return = array();
        while ($row = $adb->fetchByAssoc($result)) {
            if (isset($_REQUEST['folderid'])) {
                $selected_folderid = vtlib_purify($_REQUEST['folderid']);
            } else {
                $selected_folderid = $this->reportinformations["folderid"];
            }
            $row['selected'] = ( isset($selected_folderid) && $selected_folderid == $row['folderid']) ? 'selected' : '';
            $return[] = $row;
        }
        return $return;
    }

    public function getFolderName($folderid = "") {
        $adb = PearDatabase::getInstance();
        if ($folderid != "") {
            $sql = "select * from  its4you_reports4you_folder WHERE folderid=?";
            $result = $adb->pquery($sql, array($folderid));
            $return = array();
            while ($row = $adb->fetchByAssoc($result)) {
                $row['selected'] = (isset($_REQUEST['folderid']) && $_REQUEST['folderid'] == $row['folderid']) ? 'selected' : '';
                $return = $row["foldername"];
            }
        } else {
            $return = vtranslate("LBL_NONE");
        }
        return $return;
    }

    public function GetListviewData($orderby = "reports4youid", $dir = "asc") {
        global $mod_strings;
        global $current_user;

        $adb = PearDatabase::getInstance();
        $edit_all = 0;

        $status_sql = "SELECT * FROM its4you_reports4you_userstatus
                         INNER JOIN  its4you_reports4you ON its4you_reports4you.reports4youid = its4you_reports4you_userstatus.reportid
                         WHERE userid=?";
        $status_res = $adb->pquery($status_sql, array($this->current_user->id));
        $status_arr = array();
        while ($status_row = $adb->fetchByAssoc($status_res)) {
            $status_arr[$status_row["reports4youid"]]["sequence"] = $status_row["sequence"];
        }

        $originOrderby = $orderby;
        $originDir = $dir;
        if ($orderby == "order") {
            $orderby = "sequence";
        }
        $then_order_by = "";
        if ($orderby == "primarymodule") {
            $then_order_by = " ,sequence ASC ";
        }

        $sql = "SELECT reports4youid, reports4youname, primarymodule, description,  folderid, owner, tablabel, IF(its4you_reports4you_userstatus.sequence IS NOT NULL,its4you_reports4you_userstatus.sequence,1) AS sequence, IF(its4you_reports4you_scheduled_reports.reportid IS NOT NULL,1,0) AS scheduling 
				FROM its4you_reports4you 
				INNER JOIN its4you_reports4you_modules ON its4you_reports4you_modules.reportmodulesid = its4you_reports4you.reports4youid 
				INNER JOIN its4you_reports4you_folder USING(folderid) 
				INNER JOIN vtiger_tab ON vtiger_tab.tabid = its4you_reports4you_modules.primarymodule 
                                INNER JOIN its4you_reports4you_settings ON its4you_reports4you_settings.reportid = its4you_reports4you.reports4youid 
                                LEFT JOIN its4you_reports4you_userstatus ON its4you_reports4you.reports4youid = its4you_reports4you_userstatus.reportid 
                                LEFT JOIN its4you_reports4you_scheduled_reports ON its4you_reports4you.reports4youid = its4you_reports4you_scheduled_reports.reportid ";
        $current_user_id = $this->current_user->id;

        $sql .= " WHERE its4you_reports4you.deleted=0  ";

        if ($current_user->is_admin != "on") {
            // primarymodule!=29 -> primarymodule != Users for nonAdminUsers !!!
            $sql .= " AND primarymodule!=29 ";
        }

        if (!is_admin($this->current_user)) {
            $subordinate_users = ITS4YouReports::getSubOrdinateUsersArray(true);
            $subordinate_users_sql = implode("','", $subordinate_users);
            $sql .= " AND (sharingtype IN ('public','share') OR owner IN ('$subordinate_users_sql') ) ";
        }

        if (isset($_REQUEST["search_field"]) && $_REQUEST["search_field"] != "" && $_REQUEST["search_field"] != "primarymodule" && isset($_REQUEST["search_text"]) && $_REQUEST["search_text"] != "") {
            $where_cond = " AND " . vtlib_purify($_REQUEST["search_field"]) . " LIKE '%" . vtlib_purify($_REQUEST["search_text"]) . "%' ";
            $sql .= $where_cond;
        }
        $sql .= "ORDER BY $orderby $dir $then_order_by";

//echo "<br/><br/><br/><br/><br/><br/>";
//$adb->setDebug(true);
        $result = $adb->pquery($sql, array());
//$adb->setDebug(false);
        $edit = "Edit  ";
        $del = "Del  ";
        $bar = "  | ";
        $cnt = 1;

        $return_data = Array();
        $num_rows = $adb->num_rows($result);

        while ($row = $adb->fetchByAssoc($result)) {
            $currModule = $row['primarymodule'];
            $reports4youid = $row['reports4youid'];

            $view_all = $this->current_user_profileGlobalPermission[0];
            if (!is_admin($this->current_user) || $view_all != 0){
                //in case of template module is not permitted for current user then skip it in list
                if ($this->CheckReportPermissions($currModule, $reports4youid, false) === false)
                    continue;
            }

            $reportsarray = array();
            $reportsarray['status'] = 1;
            $reportsarray['order'] = $row["sequence"];

            $reportsarray['status_lbl'] = ($reportsarray['status'] == 1 ? $this->app_strings["Active"] : $this->app_strings["Inactive"]);
            $reportsarray['reports4youid'] = $reports4youid;
            $reportsarray['description'] = $row['description'];
            $reportsarray['owner'] = getUserFullName($row['owner']);
            $translated_module = vtranslate(vtlib_getModuleNameById($currModule), $currModule);
            $reportsarray['module'] = $translated_module;
            $folderid = $row['folderid'];
            $foldername = $this->getFolderName($folderid);
            $reportsarray['foldername'] = $foldername;
            $reportsarray['filename'] = "<a href=\"index.php?action=resultGenerate&module=ITS4YouReports&record=" . $reports4youid . "&parenttab=Tools\">" . $row['reports4youname'] . "</a>";
            $reportsarray['scheduling'] = $row['scheduling'];

//if ($is_admin == true || $edit_all==0 || in_array($report["template_owner"], $subordinate_users) || $report["template_owner"] == $this->current_user->id)

            if (is_admin($this->current_user) || $edit_all == 0 || in_array($row['owner'], $subordinate_users)) {
                $reportsarray['edit'] = "<a href=\"index.php?action=EditReports4You&module=ITS4YouReports&record=" . $reports4youid . "&parenttab=Tools\">" . strtolower($this->app_strings["LBL_EDIT_BUTTON"]) . "</a> | "
                    . "<a href=\"index.php?action=EditReports4You&module=ITS4YouReports&record=" . $reports4youid . "&isDuplicate=true&parenttab=Tools\">" . strtolower($this->app_strings["LBL_DUPLICATE_BUTTON"]) . "</a> | "
                    . "<a href=\"javascript:deleteSingleReport('$reports4youid');\">" . $this->app_strings["LNK_DELETE"] . "</a>";
            }
            if (isset($_REQUEST["search_field"]) && $_REQUEST["search_field"] != "" && $_REQUEST["search_field"] == "primarymodule" && $_REQUEST["search_text"] != "") {
                if (!is_numeric(strpos(strtolower($translated_module), strtolower(vtlib_purify($_REQUEST["search_text"]))))) {
                    continue;
                }
            }
            if ($orderby == "primarymodule") {
                $return_data[$translated_module][] = $reportsarray;
            } else {
                $return_data[] = $reportsarray;
            }
        }

        if ($orderby == "primarymodule") {
            if ($originDir == "asc") {
                ksort($return_data);
            } else {
                krsort($return_data);
            }
            foreach ($return_data as $tmodule => $return_data_tm) {
                foreach ($return_data_tm as $return_data_tmg)
                    $return_data_togo[] = $return_data_tmg;
            }
            $return_data = $return_data_togo;
        }

        return $return_data;
    }

    public static function DieDuePermission($type = "", $die_columns = array()) {
        global $current_user, $app_strings, $default_theme;
        if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '')
            $theme = $_SESSION['vtiger_authenticated_user_theme'];
        else {
            if (!empty($current_user->theme)) {
                $theme = $current_user->theme;
            } else {
                $theme = $default_theme;
            }
        }

        $sCurrentModule = 'ITS4YouReports';

        switch ($type) {
            case "columns":
                $type_info = "<br />" . vtranslate("LBL_COLUMNS_ERROR", $sCurrentModule);
                if (!empty($die_columns)) {
                    $type_info .= "<br />(" . implode(", ", $die_columns) . ")";
                }
                break;
            case "values":
                $type_info = "<br />" . vtranslate("LBL_FVALUES_ERROR", $sCurrentModule);
                break;
            default:
                if($type!=""){
                    $type_info = "<br />" . vtranslate($type, $sCurrentModule);
                }else{
                    $type_info = "";
                }
                break;
        }

        $output = "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
        $output .= "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
        $output .= "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
      		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
      		<tbody><tr>
      		<td rowspan='2' width='11%'><img src='layouts/vlayout/skins/images/denied.gif' ></td>
      		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>" . vtranslate("LBL_PERM_DENIED", "ITS4YouReports") . $type_info . "</span></td>
      		</tr>
      		<tr>
      		<td class='small' align='right' nowrap='nowrap'>
      		<a href='javascript:window.history.back();'>" . vtranslate("LBL_GO_BACK") . "</a><br></td>
      		</tr>
      		</tbody></table>
      		</div>";
        $output .= "</td></tr></table>";
        echo $output;
        exit;
    }

    //Method for getting the array of profiles permissions to Reports4You actions.
    protected function GetProfilesPermissions() {
        if (count($this->profilesPermissions) == 0) {
            $adb = PearDatabase::getInstance();
            $profiles = getAllProfileInfo();
            $sql = "SELECT * FROM its4you_reports4you_profilespermissions";
            $res = $adb->query($sql);
            $permissions = array();
            while ($row = $adb->fetchByAssoc($res)) {
                //      in case that profile has been deleted we need to set permission only for active profiles
                if (isset($profiles[$row["profileid"]]))
                    $permissions[$row["profileid"]][$row["operation"]] = $row["permissions"];
            }

            foreach ($profiles as $profileid => $profilename) {
                foreach ($this->profilesActions as $actionName) {
                    $actionId = getActionid($actionName);
                    if (!isset($permissions[$profileid][$actionId])) {
                        $permissions[$profileid][$actionId] = "0";
                    }
                }
            }

            ksort($permissions);
            $this->profilesPermissions = $permissions;
        }

        return $this->profilesPermissions;
    }

    //Method for checking the permissions, whether the user has privilegies to perform specific action on PDF Maker.
    public function CheckPermissions($actionKey) {
        $profileid = fetchUserProfileId($this->current_user->id);
        $result = false;

        if (isset($this->profilesActions[$actionKey])) {
            $actionid = getActionid($this->profilesActions[$actionKey]);
            $permissions = $this->GetProfilesPermissions();

            if (isset($permissions[$profileid][$actionid]) && $permissions[$profileid][$actionid] == "0")
                $result = true;
        }

        return $result;
    }

    public function CheckReportPermissions($selected_module, $reports4youid, $die = true) {
        $result = true;

        global $current_user;
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if ($request->has('view') && 'ShowWidget' === $request->get('view')) {
            $die = false;
        }

        if ($selected_module == "Users" && $current_user->is_admin != "on") {
            $result = false;
        } elseif ($selected_module != "" && isPermitted($selected_module, 'DetailView') != "yes") {
            $result = false;
        } elseif ($reports4youid != "" && ITS4YouReports::CheckSharing($reports4youid) === false) {
            $result = false;
        }

        if ($die === true && $result === false) {
            $this->DieDuePermission();
        }

        return $result;
    }

    public static function CheckSharing($reports4youid) {
        //  if this template belongs to current user
        $adb = PearDatabase::getInstance();
        $sql = "SELECT owner, sharingtype FROM its4you_reports4you_settings WHERE reportid = ?";
        $result = $adb->pquery($sql, array($reports4youid));
        $row = $adb->fetchByAssoc($result);

        $owner = $row["owner"];
        $sharingtype = $row["sharingtype"];

        $result = false;

        global $current_user;

        if ($owner == $current_user->id || $current_user->is_admin == "on") {
            $result = true;
        } else {
            switch ($sharingtype) {
                //available for all
                case "public":
                    $result = true;
                    break;
                //available only for superordinate users of template owner, so we get list of all subordinate users of the current user and if template
                //owner is one of them then template is available for current user
                case "private":
                    $subordinateUsers = ITS4YouReports::getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
                        $result = in_array($owner, $subordinateUsers);
                    } else
                        $result = false;
                    break;
                //available only for those that are in share list
                case "share":
                    $subordinateUsers = ITS4YouReports::getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && count($subordinateUsers) > 0 && in_array($owner, $subordinateUsers))
                        $result = true;
                    else {
                        $member_array = ITS4YouReports::GetSharingMemberArray($reports4youid);
                        if (isset($member_array["users"]) && in_array($current_user->id, $member_array["users"]))
                            $result = true;
                        elseif (isset($member_array["roles"]) && in_array($current_user->roleid, $member_array["roles"]))
                            $result = true;
                        else {
                            if (isset($member_array["rs"])) {
                                foreach ($member_array["rs"] as $roleid) {
                                    $roleAndsubordinateRoles = getRoleAndSubordinatesRoleIds($roleid);
                                    if (in_array($current_user->roleid, $roleAndsubordinateRoles)) {
                                        $result = true;
                                        break;
                                    }
                                }
                            }

                            if ($result == false && isset($member_array["groups"])) {
                                $current_user_groups = explode(",", fetchUserGroupids($current_user->id));
                                $res_array = array_intersect($member_array["groups"], $current_user_groups);
                                if (!empty($res_array) && count($res_array) > 0)
                                    $result = true;
                                else
                                    $result = false;
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    public static function getSubRoleUserIds($roleid) {
        $subRoleUserIds = array();
        $subordinateUsers = ITS4YouReports::getRoleAndSubordinateUserIds($roleid);
        if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }

        return $subRoleUserIds;
    }

    public static function GetSharingMemberArray($reports4youid) {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT shareid, setype FROM  its4you_reports4you_sharing WHERE reports4youid = ? ORDER BY setype ASC";
        $result = $adb->pquery($sql, array($reports4youid));
        $memberArray = array();
        while ($row = $adb->fetchByAssoc($result)) {
            $memberArray[$row["setype"]][] = $row["shareid"];
        }

        return $memberArray;
    }

    public function getPriModuleColumnsList($module) {
        if (is_numeric($module)) {
            $module = vtlib_getModuleNameById($module);
        }
        $this->initListOfModules();
        unset($this->hyperlinks_fields);
        global $default_charset;
        foreach ($this->module_list[$module] as $key => $value) {
            if (is_numeric($value) && !is_numeric($key)) {
                $key_s = $key;
                $key = $value;
                $value = $key_s;
            }
            $temp = $this->getColumnsListbyBlock($module, $key);
            //$value = html_entity_decode($value, ENT_QUOTES, $default_charset);
            if (!empty($ret_module_list[$module][$value])) {
                if (!empty($temp)) {
                    $ret_module_list[$module][$value] = array_merge($ret_module_list[$module][$value], $temp);
                }
            } else {
                // $ret_module_list[$module][$value] = $this->getColumnsListbyBlock($module, $key);
                $ret_module_list[$module][$value] = $temp;
            }
        }
        $this->pri_module_columnslist[$module] = $ret_module_list[$module];
        return $ret_module_list;
    }

    /** Function to get the Related module list in vtiger_reports
     *  This function generates the list of secondary modules in vtiger_reports
     *  and returns the related module as an Array
     */
    function getReportRelatedModules($moduleid) {
        global $app_list_strings;
        global $mod_strings;
        $module = vtlib_getModuleNameById($moduleid);

        $optionhtml = Array();
        if (vtlib_isModuleActive($module)) {
            if (!empty($this->related_modules[$module])) {
                foreach ($this->related_modules[$module] as $rel_modules) {
                    $relmod_lang = $rel_modules["name"];
                    $relmod_str = $rel_modules["id"];
                    $relmod_arr = explode("x", $relmod_str);
                    $relmod_id = $relmod_arr[0];
                    $relmod_name = vtlib_getModuleNameById($relmod_id);

                    if (isPermitted($relmod_name, 'index') == "yes") {
                        $rel_tabid = getTabid($relmod_name);
                        $optionhtml[] = array('id' => $relmod_str,
                                              'name' => $relmod_lang,
                                              'checked' => (in_array($relmod_str, $this->relatedmodulesarray)) ? 'checked' : '',
                        );
                    }
                }
                if ($module == "Calendar") {
                    $this->init_list_for_module("Events");
                    foreach ($this->related_modules["Events"] as $rel_modules) {
                        $relmod_lang = $rel_modules["name"] . " " . vtranslate("Events", $module);
                        $relmod_str = $rel_modules["id"];
                        $relmod_arr = explode("x", $relmod_str);
                        $relmod_id = $relmod_arr[0];
                        $relmod_name = vtlib_getModuleNameById($relmod_id);

                        if (isPermitted($relmod_name, 'index') == "yes") {
                            $rel_tabid = getTabid($relmod_name);
                            $optionhtml [] = array('id' => $relmod_str,
                                                   'name' => $relmod_lang,
                                                   'checked' => (in_array($relmod_str, $this->relatedmodulesarray)) ? 'checked' : '',
                            );
                        }
                    }
                }
            }
        }

        return $optionhtml;
    }

    function in_multiarray($elem, $array, $field = "name") {
        $default_charset = vglobal("default_charset");

        if (!empty($array)){
            $top = sizeof($array) - 1;
            $bottom = 0;
            if (!empty($array)) {
                while ($bottom <= $top) {
                    $elem_decode = html_entity_decode($elem, ENT_QUOTES, $default_charset);

                    if ($array[$bottom][$field] == $elem_decode) {
                        return true;
                    } else {
                        if (is_array($array[$bottom][$field])) {
                            if (in_multiarray($elem, ($array[$bottom][$field]))) {
                                return true;
                            }
                        }
                    }
                    $bottom++;
                }
            }
        }
        return false;
    }

    private function init_list_for_module($module) {
        global $old_related_modules;
        global $app_strings;
        $adb = PearDatabase::getInstance();
        $tabid = getTabid($module);
        // special related fields 
//$adb->setDebug(true);
        $standard_fields_res = $adb->pquery("SELECT DISTINCT fieldid, fieldlabel, uitype FROM vtiger_field WHERE uitype IN (51,57,58,59,66,68,73,75,76,78,80,81) AND tabid=?", array($tabid));
//$adb->setDebug(false);

        $related_fields_array = array();
        if ($adb->num_rows($standard_fields_res)) {
            while ($st_rel_row = $adb->fetch_array($standard_fields_res)) {
                $field_id = $st_rel_row["fieldid"];
                $field_rel = $st_rel_row["fieldlabel"];
                $field_rel = vtranslate($field_rel);
                if (!in_array($field_id, $related_fields_array)) {
                    $related_fields_array[] = $field_id;
                    switch ($st_rel_row["uitype"]) {
                        case "51":
                            if (vtlib_isModuleActive("Accounts")) {
                                //$relmodule_lbl = vtranslate("Accounts","Accounts")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Accounts", "Accounts") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Accounts") . "x$field_id");
                                }
                            }
                            break;
                        case "57":
                            if (vtlib_isModuleActive("Contacts")) {
                                $field_rel = vtranslate($field_rel);
                                //$relmodule_lbl = vtranslate("Contacts","Contacts")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Contacts", "Contacts") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Contacts") . "x$field_id");
                                }
                            }
                            break;
                        case "58":
                            if (vtlib_isModuleActive("Campaigns")) {
                                //$relmodule_lbl = vtranslate("Campaigns","Campaigns")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Campaigns", "Campaigns") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Campaigns") . "x$field_id");
                                }
                            }
                            break;
                        case "59":
                            if (vtlib_isModuleActive("Products")) {
                                //$relmodule_lbl = vtranslate("Products","Products")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Products", "Products") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Products") . "x$field_id");
                                }
                            }
                            break;
                        case "66":
                            $related_to_modules = array(
                                "Leads",
                                "Accounts",
                                "Potentials",
                                "Quotes",
                                "PurchaseOrder",
                                "SalesOrder",
                                "Invoice",
                                "Campaigns",
                                "HelpDesk",
                            );
                            foreach($related_to_modules as $related_module){
                                if (vtlib_isModuleActive($related_module)) {
                                    //$relmodule_lbl = vtranslate("Products","Products")." ($field_rel)";
                                    $relmodule_lbl = $field_rel . " (" . vtranslate($related_module, $related_module) . ")";
                                    if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                        $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid($related_module) . "x$field_id");
                                    }
                                }
                            }
                            break;
                        case "68":
                            if (vtlib_isModuleActive("Accounts")) {
                                //$relmodule_lbl = vtranslate("Accounts","Accounts")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Accounts", "Accounts") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Accounts") . "x$field_id");
                                }
                            }
                            if (vtlib_isModuleActive("Contacts")) {
                                //$relmodule_lbl = vtranslate("Contacts","Contacts")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Contacts", "Contacts") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Contacts") . "x$field_id");
                                }
                            }
                            break;
                        case "73":
                            if (vtlib_isModuleActive("Accounts")) {
                                //$relmodule_lbl = vtranslate("Accounts","Accounts")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Accounts", "Accounts") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Accounts") . "x$field_id");
                                }
                            }
                            break;
                        case "75":
                            if (vtlib_isModuleActive("Vendors")) {
                                //$relmodule_lbl = vtranslate("Vendors","Vendors")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Vendors", "Vendors") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Vendors") . "x$field_id");
                                }
                            }
                            break;
                        case "76":
                            if (vtlib_isModuleActive("Potentials")) {
                                //$relmodule_lbl = vtranslate("Potentials","Potentials")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Potentials", "Potentials") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Potentials") . "x$field_id");
                                }
                            }
                            break;
                        case "78":
                            if (vtlib_isModuleActive("Quotes")) {
                                //$relmodule_lbl = vtranslate("Quotes","Quotes")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Quotes", "Quotes") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Quotes") . "x$field_id");
                                }
                            }
                            break;
                        case "80":
                            if (vtlib_isModuleActive("SalesOrder")) {
                                //$relmodule_lbl = vtranslate("SalesOrder","SalesOrder")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("SalesOrder", "SalesOrder") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("SalesOrder") . "x$field_id");
                                }
                            }
                            break;
                        case "81":
                            if (vtlib_isModuleActive("Vendors")) {
                                //$relmodule_lbl = vtranslate("Vendors","Vendors")." ($field_rel)";
                                $relmodule_lbl = $field_rel . " (" . vtranslate("Vendors", "Vendors") . ")";
                                if (!$this->in_multiarray($relmodule_lbl, $this->related_modules[$module])) {
                                    $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid("Vendors") . "x$field_id");
                                }
                            }
                            break;
                    }
                }
            }
        }
    }

    // Initializes the module list for listing columns for report creation.
    public function initListOfModules() {
        global $old_related_modules;
        $adb = PearDatabase::getInstance();

        //$this->inventory_modules = self::$inventory_modules;
        // $restricted_modules = array('Emails', 'Events', 'Webmails');
        $restricted_modules = array('Emails', 'Events', 'Webmails','PBXManager');
        global $current_user;
        if ($current_user->is_admin != "on") {
            $restricted_modules[] = "Users";
        }

        $restricted_blocks = array('LBL_IMAGE_INFORMATION', 'LBL_COMMENTS', 'LBL_COMMENT_INFORMATION');

        $this->module_id = array();
        $this->module_list = array();

        // Prefetch module info to check active or not and also get list of tabs
        $modulerows = vtlib_prefetchModuleActiveInfo(false);

        $cachedInfo = VTCacheUtils::lookupReport_ListofModuleInfos();

        if ($cachedInfo !== false) {
            $this->module_list = $cachedInfo['module_list'];
            $this->related_modules = $cachedInfo['related_modules'];
        } else {
            if ($modulerows) {
                foreach ($modulerows as $resultrow) {
                    if ($resultrow['presence'] == '1')
                        continue;      // skip disabled modules

// ITS4YOU-UP SlOl 21. 2. 2014 11:38:13 add Assigned to Users module
                    if ($resultrow['isentitytype'] != '1' && $resultrow['name'] != "Users")
                        continue;  // skip extension modules
                    if (in_array($resultrow['name'], $restricted_modules)) { // skip restricted modules
                        continue;
                    }
                    if ($resultrow['name'] != 'Calendar') {
                        $this->module_id[$resultrow['tabid']] = $resultrow['name'];
                    } else {
                        $this->module_id[9] = $resultrow['name'];
                        $this->module_id[16] = $resultrow['name'];
                    }
                    // ITS4YOU-CR SlOl  2. 12. 2013 8:42:40 
                    $this->init_list_for_module($resultrow['name']);
                    // ITS4YOU-END 2. 12. 2013 8:42:43
                    $this->module_list[$resultrow['name']] = array();
                }

                $moduleids = array_keys($this->module_id);
                //$adb->setDebug(true);
                $reportblocks = $adb->pquery("SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE blocklabel!= '' AND tabid IN (" . generateQuestionMarks($moduleids) . ") order by tabid, sequence asc", array($moduleids));
                // $reportblocks = $adb->pquery("SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (9,16)", array());
                //$adb->setDebug(false);
                $prev_block_label = '';
                if ($adb->num_rows($reportblocks) > 0) {
                    while ($resultrow = $adb->fetch_array($reportblocks)) {
                        $blockid = $resultrow['blockid'];
                        $blocklabel = $resultrow['blocklabel'];
                        $module = $this->module_id[$resultrow['tabid']];

                        if (in_array($blocklabel, $restricted_blocks) ||
                            in_array($blockid, $this->module_list[$module]) ||
                            isset($this->module_list[$module][vtranslate($blocklabel, $module)])
                        ) {
                            continue;
                        }
                        if ($blocklabel != "") {
                            if ($module == 'Calendar' && $blocklabel == 'LBL_CUSTOM_INFORMATION') {
                                $this->module_list[$module][$blockid] = vtranslate($blocklabel, $module);
                            } elseif ($module == 'Calendar' && in_array($blocklabel, array("LBL_RECURRENCE_INFORMATION", "LBL_RELATED_TO"))) {
                                $this->module_list[$module][$blockid] = vtranslate($blocklabel, "Events");
                            } else {
                                $this->module_list[$module][$blockid] = vtranslate($blocklabel, $module);
                            }
                            $prev_block_label = $blocklabel;
                            // ak je blocklabel prazdny spustat toto ??? zistit !!!
                        } else {
                            $this->module_list[$module][$blockid] = vtranslate($prev_block_label, $module);
                        }
                    }
                }
                // tvorba vazby cez ui10 a pridanie stlpcov k danemu modulu
//    $adb->setDebug(true);
                $relatedmodules_pf = $adb->pquery("
                                    SELECT uitype,fieldid, 
                                    fieldlabel,
                                    CONCAT(fieldlabel,' (',relmodule,') ') AS name, 
                                    vtiger_tab.tabid, relmodule FROM vtiger_fieldmodulerel
                                    INNER JOIN vtiger_tab on vtiger_tab.name = vtiger_fieldmodulerel.module 
                                    INNER JOIN vtiger_field USING (fieldid) 
                                    WHERE vtiger_tab.isentitytype = 1
                                    AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ")
                                    AND vtiger_tab.presence = 0 AND vtiger_field.uitype='10' AND vtiger_field.displaytype in (1,2,3)", array($restricted_modules));
//    $adb->setDebug(false);
                if ($adb->num_rows($relatedmodules_pf) > 0) {
                    $related_fields_array = array();
                    while ($resultrow = $adb->fetch_array($relatedmodules_pf)) {
                        $tabid = $resultrow['tabid'];
                        $module = $this->module_id[$tabid];
                        if (!isset($this->related_modules[$module])) {
                            $this->related_modules[$module] = array();
                        }
                        $this->init_list_for_module($module);
                        if (is_numeric(strpos($resultrow["name"], $resultrow['relmodule']))) {
                            $reltabid = getTabid($resultrow["relmodule"]);
                            $fieldlabel = vtranslate($resultrow['fieldlabel'], $resultrow['relmodule']);
                            $relmodule = vtranslate($resultrow['relmodule'], $resultrow['relmodule']);
                            $relmodule_lbl = $fieldlabel . ' (' . $relmodule . ')';
                            $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => $reltabid . "x" . $resultrow['fieldid']);
                        }
                    }
                }

                // pridanie modulu Users pre assigned user id ui type 53 na vyber poloziek
                // ITS4YOU-CR SlOl 21. 2. 2014 11:02:16
                foreach ($this->related_modules as $modulename => $related_array) {
                    $module_id = getTabid($modulename);
//$adb->setDebug(true);
                    $relatedmodules_at = $adb->pquery("SELECT fieldlabel, fieldid FROM vtiger_field WHERE uitype = ? AND tabid = ? AND presence IN (0,2)", array(53, $module_id));
//$adb->setDebug(false);
                    while ($row = $adb->fetchByAssoc($relatedmodules_at)) {
                        $user_module = "Users";
                        $user_module_lbl = vtranslate($user_module);
                        $this->related_modules[$modulename][] = array("name" => vtranslate($row['fieldlabel']) . " (" . $user_module_lbl . ")", "id" => getTabid($user_module) . "x" . $row['fieldid']);
                    }
                }
                // ITS4YOU-END 21. 2. 2014 11:02:18 
//$adb->setDebug(true);
                /*
                                $relatedmodules_mi = $adb->pquery(
                                        "SELECT vtiger_tab.name AS name, vtiger_relatedlists.tabid FROM vtiger_tab
                                            INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid
                                            WHERE vtiger_tab.isentitytype=1
                                            AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ") 
                                            AND vtiger_tab.presence = 0 AND vtiger_relatedlists.label!='Activity History'
                                        UNION 
                                        SELECT module, vtiger_tab.tabid 
                                            FROM vtiger_fieldmodulerel 
                                            INNER JOIN vtiger_tab on vtiger_tab.name = vtiger_fieldmodulerel.relmodule 
                                            WHERE vtiger_tab.isentitytype = 1 AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ") AND vtiger_tab.presence = 0   
                                        ", array($restricted_modules,$restricted_modules));
                                        */
                $relatedmodules_mi = $adb->pquery(
                    "SELECT vtiger_tab.name AS name, vtiger_relatedlists.tabid FROM vtiger_tab
                            INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid
                            WHERE vtiger_tab.isentitytype=1
                            AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ") 
                            AND vtiger_tab.presence = 0 AND vtiger_relatedlists.label!='Activity History'
                        ", array($restricted_modules));
//$adb->setDebug(false);
                while ($resultrow = $adb->fetch_array($relatedmodules_mi)) {
                    $tabid = $resultrow['tabid'];
                    $module = $this->module_id[$tabid];
                    if(!in_array($resultrow['name'],$restricted_modules)){
                        $relmodule_lbl = vtranslate($resultrow['name']);
                        $this->related_modules[$module][] = array("name" => $relmodule_lbl, "id" => getTabid($resultrow['name']) . "xMIF");
                    }
                }
                // inventory modules related modules Services Products with INV signature (inventory relation)
                foreach ($this->related_modules as $module => $rel_array) {
                    foreach ($rel_array as $r_key => $r_d_array) {
                        if(ITS4YouReports::isInventoryModule($module)){
                            if (!in_array("Products", $r_d_array) && !$this->in_multiarray(vtranslate("Products"), $this->related_modules[$module])) {
                                $related_modules[$module][] = "Products";
                                $this->related_modules[$module][] = array("name" => vtranslate("Products"), "id" => getTabid("Products") . "xINV");
                                $r_d_array[] = "Products";
                            }
                            if (!in_array("Services", $r_d_array) && !$this->in_multiarray(vtranslate("Services"), $this->related_modules[$module])) {
                                $related_modules[$module][] = "Services";
                                $this->related_modules[$module][] = array("name" => vtranslate("Services"), "id" => getTabid("Services") . "xINV");
                                $r_d_array[] = "Services";
                            }
                        }
                    }
                }
                // ITS4YOU-CR SlOl 27. 6. 2016 12:25:09
                foreach ($this->related_modules as $module => $rel_array) {
                    foreach ($rel_array as $r_key => $r_d_array) {
                        if (!in_array("ModTracker", $r_d_array) && !$this->in_multiarray(vtranslate("LBL_UPDATES"), $this->related_modules[$module])) {
                            $related_modules[$module][] = "ModTracker";
                            $this->related_modules[$module][] = array("name" => vtranslate("LBL_UPDATES"), "id" => getTabid("ModTracker") . "xMIF");
                            $r_d_array[] = "ModTracker";
                        }
                        if (!in_array("ModComments", $r_d_array) && !$this->in_multiarray(vtranslate("ModComments"), $this->related_modules[$module])) {
                            $related_modules[$module][] = "ModComments";
                            $this->related_modules[$module][] = array("name" => vtranslate("ModComments"), "id" => getTabid("ModComments") . "xMIF");
                            $r_d_array[] = "ModComments";
                        }
                    }
                }
                // Put the information in cache for re-use
                VTCacheUtils::updateReport_ListofModuleInfos($this->module_list, $this->related_modules);
            }
        }
    }

    //<<<<<<<<advanced filter>>>>>>>>>>>>>>
    public function getAdvanceFilterOptionsJSON($primarymodule) {
        $Options = array();

        $Options_json = "";
        global $default_charset;
        if ($primarymodule != "") {
            $p_options = getPrimaryColumns($Options, $primarymodule, true, $this);

            if (isset($_REQUEST["selectedColumnsStr"]) && $_REQUEST["selectedColumnsStr"] != "") {
                $selectedColumnsStr = vtlib_purify($_REQUEST["selectedColumnsStr"]);
                $selectedColumnsStringDecoded = html_entity_decode($selectedColumnsStr, ENT_QUOTES, $default_charset);
                $selectedColumns_arr = explode("<_@!@_>", $selectedColumnsStringDecoded);
            } else {
                $selectedColumnsStr = $this->reportinformations["selectedColumnsString"];
                $selectedColumnsStringDecoded = html_entity_decode($selectedColumnsStr, ENT_QUOTES, $default_charset);
                $selectedColumns_arr = explode(";", $selectedColumnsStringDecoded);
            }
            if ($selectedColumnsStr != "") {
                $opt_label = vtranslate("LBL_Filter_SelectedColumnsGroup", $this->currentModule);
                foreach ($selectedColumns_arr as $sc_key => $sc_col_str) {
                    if ($sc_col_str != "") {
                        $in_options = false;
                        foreach ($Options as $opt_group => $opt_array) {
                            if ($this->in_multiarray($sc_col_str, $opt_array, "value") === true) {
                                $in_options = true;
                                continue;
                            }
                        }
                        if ($in_options) {
                            continue;
                        } else {
                            /*
                            list($sc_tablename, $sc_columnname, $sc_modulestr) = explode(':', $sc_col_str);
                            list($sc_module) = explode('_', $sc_modulestr);
                            $sc_module_id = getTabid($sc_module);
                            $sc_tablename = trim(strtolower($sc_tablename), "_mif");
                            $adb = PearDatabase::getInstance();
                            //$adb->setDebug(true);
                            $sc_field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype FROM vtiger_field WHERE tablename = ? and columnname = ? and tabid=?", array($sc_tablename, $sc_columnname, $sc_module_id)), 0);
                            //$adb->setDebug(false);
                            $sc_field_uitype = $sc_field_row["uitype"];
                            */
                            $sc_field_uitype = ITS4YouReports::getUITypeFromColumnStr($sc_col_str);
                            if (in_array($sc_field_uitype, ITS4YouReports::$s_uitypes)) {
                                $this->adv_sel_fields[$sc_col_str] = true;
                            }
                            $Options[$opt_label][] = array("value" => $sc_col_str, "text" => ITS4YouReports::getColumnStr_Label($sc_col_str));
                        }
                    }
                }
            }
            $secondarymodules = Array();
            if (!empty($this->related_modules[$primarymodule])) {
                foreach ($this->related_modules[$primarymodule] as $key => $value) {
                    $exploded_mid = explode("x", $value["id"]);
                    if (strtolower($exploded_mid[1]) != "mif") {
                        $secondarymodules[] = $value["id"];
                    }
                }
            }
            if (!empty($secondarymodules)) {
                $secondarymodules_str = implode(":", $secondarymodules);
                $this->getSecModuleColumnsList($secondarymodules_str);
                $Options_sec = getSecondaryColumns(array(), $secondarymodules_str, $this);
                if (!empty($Options_sec)) {
                    foreach ($Options_sec as $moduleid => $sec_options) {
                        $Options = array_merge($Options, $sec_options);
                    }
                }
            }
            foreach ($Options AS $optgroup => $optionsdata) {
                if ($Options_json != "")
                    $Options_json .= "(|@!@|)";
                $Options_json .= $optgroup;
                $Options_json .= "(|@|)";
                $Options_json .= Zend_JSON::encode($optionsdata);
            }
        }
        return $Options_json;
    }

    /** Function to get the list of its4you_reports4you folders when Save and run  the its4you_reports4you
     *  This function gets the its4you_reports4you folders from database and form
     *  a combo values of the folders and return
     *  HTML of the combo values
     */
    function sgetRptFldrSaveReport() {
        $adb = PearDatabase::getInstance();
        $shtml = '';
        $sql = "select * from its4you_reports4you_folder order by folderid";
        $result = $adb->pquery($sql, array());
        $reportfldrow = $adb->fetch_array($result);
        do {
            $shtml .= "<option value='" . $reportfldrow['folderid'] . "'>" . $reportfldrow['foldername'] . "</option>";
        } while ($reportfldrow = $adb->fetch_array($result));

        return $shtml;
    }

    /** Function to get the column to total vtiger_fields in Reports
     *  This function gets columns to total vtiger_field
     *  and generated the html for that vtiger_fields
     *  It returns the HTML of the vtiger_fields along with the check boxes
     */
    function sgetColumntoTotal($primarymoduleid, $secondarymodule) {
        $options = Array();

        $options [] = $this->sgetColumnstoTotalHTML($primarymoduleid, 0);
        if (!empty($secondarymodule)) {
            for ($i = 0; $i < count($secondarymodule); $i++) {
                $options [] = $this->sgetColumnstoTotalHTML(vtlib_getModuleNameById($secondarymodule[$i]), ($i + 1));
            }
        }
        return $options;
    }

    /** Function to get the selected columns of total vtiger_fields in Reports
     *  This function gets selected columns of total vtiger_field
     *  and generated the html for that vtiger_fields
     *  It returns the HTML of the vtiger_fields along with the check boxes
     */
    function sgetColumntoTotalSelected($primarymodule, $secondarymodule, $reportid) {
        $adb = PearDatabase::getInstance();
        $options = Array();
        if ($reportid != "") {
            // if (!isset($this->columnssummary) && $_REQUEST["file"] != "ChangeSteps")
            $ssql = "select its4you_reports4you_summary.* from its4you_reports4you_summary inner join its4you_reports4you on its4you_reports4you.reports4youid = its4you_reports4you_summary.reportsummaryid where its4you_reports4you.reports4youid=?";
            $result = $adb->pquery($ssql, array($reportid));
            if ($result) {
                $reportsummaryrow = $adb->fetch_array($result);

                do {
                    $this->columnssummary[] = $reportsummaryrow["columnname"];
                } while ($reportsummaryrow = $adb->fetch_array($result));
            }
        }
        $options [] = $this->sgetColumnstoTotalHTML($primarymodule, 0);
        if (!empty($secondarymodule)) {
            for ($i = 0; $i < count($secondarymodule); $i++) {
                // ITS4YOU-UP SlOl 1. 10. 2013 13:42:25
                $options [] = $this->sgetColumnstoTotalHTML($secondarymodule[$i], ($i + 1));
            }
        }

        return $options;
    }

    public static function getColumnsTotalRow($tabid) {
        $adb = PearDatabase::getInstance();
        $ret_result = "";
        if ($tabid != "") {
            $sparams = array($tabid);

            global $current_user;
            $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
            if (file_exists($user_privileges_path)) {
                require($user_privileges_path);
            } else {
                $profileGlobalPermission = array(0 => 1, 1 => 1);
            }
            $j_ssql = $w_ssql = "";
            if (is_admin($current_user) != true || $profileGlobalPermission[1] != 0 || $profileGlobalPermission[2] != 0) {
                $profileList = getCurrentUserProfileList();
                if (count($profileList) > 0) {
                    $w_ssql .= " AND vtiger_profile2field.profileid IN ('" . join("'", $profileList) . "')";
                }
                $j_ssql .= " INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid 
                            INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid ";
            }
            $durationsQry = '';
            if($tabid=="9" || $tabid=="16"){
                $durationsQry = ' OR (vtiger_field.columnname IN ("duration_minutes", "duration_hours"))';
            }
            $ssql = "SELECT * FROM vtiger_field 
INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid  
$j_ssql
WHERE 
(vtiger_field.uitype in (7,9,71,72,712) $durationsQry OR (vtiger_field.uitype = 1 AND (vtiger_field.typeofdata LIKE 'N%' OR vtiger_field.typeofdata LIKE 'I%'))) ";
            if($tabid=="9" || $tabid=="16"){
                $ssql .= " AND (vtiger_field.tabid=9 OR vtiger_field.tabid=16)";
            }else{
                $ssql .= " AND vtiger_field.tabid=$tabid ";
            }
            $ssql .= " AND vtiger_field.displaytype in (1,2,3) AND vtiger_field.presence IN (0,2) $w_ssql ";
            $ssql .= " ORDER BY vtiger_field.block asc, vtiger_field.sequence ASC";

            $result = $adb->pquery($ssql, array());
            if ($result) {
                $no_rows = $adb->num_rows($result);
                if ($no_rows > 0) {
                    $ret_result = $result;
                }
            }
        }
        return $ret_result;
    }

    /** Function to form the HTML for columns to total
     *  This function formulates the HTML format of the
     *  vtiger_fields along with four checkboxes
     *  It returns the HTML of the vtiger_fields along with the check boxes
     */
    function sgetColumnstoTotalHTML($moduleid) {
        $mod_arr = explode("x", $moduleid);
        $module = vtlib_getModuleNameById($mod_arr[0]);
        $fieldidstr = "";
        if (isset($mod_arr[1]) && $mod_arr[1] != "") {
            $fieldidstr = ":" . $mod_arr[1];
        }
        $columntototalrow = '';
        //retreive the vtiger_tabid	

        $adb = PearDatabase::getInstance();
        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }
        $tabid = getTabid($module);
        $escapedchars = Array('_SUM', '_AVG', '_MIN', '_MAX', '_CNT');

        $result = self::getColumnsTotalRow($tabid);
        $options_list = Array();
        if ($adb->num_rows($result) > 0) {
            do {
                $typeofdata = explode("~", $columntototalrow["typeofdata"]);

                //if ($typeofdata[0] == "N" || $typeofdata[0] == "I" || $typeofdata[0] == "NN") {
                $options = Array();
                if (isset($this->columnssummary)) {
                    $selectedcolumn = "";
                    $selectedcolumn1 = "";

                    for ($i = 0; $i < count($this->columnssummary); $i++) {
                        $selectedcolumnarray = explode(":", $this->columnssummary[$i]);
                        $selectedcolumn = $selectedcolumnarray[1] . ":" . $selectedcolumnarray[2] . ":" . str_replace($escapedchars, "", $selectedcolumnarray[3]);

                        if ($selectedcolumn != $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel']) {
                            $selectedcolumn = "";
                        } else {
                            $selectedcolumn1[$selectedcolumnarray[4]] = $this->columnssummary[$i];
                        }
                    }

                    if (isset($_REQUEST["record"]) && $_REQUEST["record"] != '') {
                        $options['label'][] = vtranslate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' -' . vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
                    }

                    $options [] = vtranslate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
                    if ($selectedcolumn1[2] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . "_SUM:2" . $fieldidstr) {
                        $options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_SUM:2' . $fieldidstr . '" type="checkbox" value="">';
                    } else {
                        $options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_SUM:2' . $fieldidstr . '" type="checkbox" value="">';
                    }
                    if ($selectedcolumn1[3] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . "_AVG:3" . $fieldidstr) {
                        $options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_AVG:3' . $fieldidstr . '" type="checkbox" value="">';
                    } else {
                        $options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_AVG:3' . $fieldidstr . '" type="checkbox" value="">';
                    }

                    if ($selectedcolumn1[4] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . "_MIN:4" . $fieldidstr) {
                        $options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_MIN:4' . $fieldidstr . '" type="checkbox" value="">';
                    } else {
                        $options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_MIN:4' . $fieldidstr . '" type="checkbox" value="">';
                    }

                    if ($selectedcolumn1[5] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . "_MAX:5" . $fieldidstr) {
                        $options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_MAX:5' . $fieldidstr . '" type="checkbox" value="">';
                    } else {
                        $options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_MAX:5' . $fieldidstr . '" type="checkbox" value="">';
                    }

                    if ($selectedcolumn1[6] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . "_CNT:6" . $fieldidstr) {
                        $options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_CNT:6' . $fieldidstr . '" type="checkbox" value="">';
                    } else {
                        $options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'] . '_CNT:6' . $fieldidstr . '" type="checkbox" value="">';
                    }
                } else {
                    if (isset($_REQUEST["record"]) && $_REQUEST["record"] != '') {
                        $options['label'][] = vtranslate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' -' . vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
                    }

                    $options [] = vtranslate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . vtranslate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);

                    $option_name = 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $module . "_" . $columntototalrow['fieldlabel'];
                    $options [] = '<input name="' . $option_name . '_SUM:2' . $fieldidstr . '" type="checkbox" value="">';
                    $options [] = '<input name="' . $option_name . '_AVG:3' . $fieldidstr . '" type="checkbox" value="">';
                    $options [] = '<input name="' . $option_name . '_MIN:4' . $fieldidstr . '" type="checkbox" value="">';
                    $options [] = '<input name="' . $option_name . '_MAX:5' . $fieldidstr . '" type="checkbox" value="">';
                    $options [] = '<input name="' . $option_name . '_CNT:6' . $fieldidstr . '" type="checkbox" value="">';
                }
                $options_list [] = $options;
                //}
            } while ($columntototalrow = $adb->fetch_array($result));
        }

        return $options_list;
    }

    function getGroupFilterList($reportid) {
        global $modules;
        global $default_charset;

        $adb = PearDatabase::getInstance();
        $groupft_criteria = array();

        $sql = 'SELECT * FROM  its4you_reports4you_relcriteria_grouping WHERE queryid = ? AND groupid = 0 ORDER BY groupid';
        $groupsresult = $adb->pquery($sql, array($reportid));

        //$j = 0;
        while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
            $groupId = $relcriteriagroup["groupid"];
            $groupCondition = $relcriteriagroup["group_condition"];

            $ssql = 'select  its4you_reports4you_relcriteria.* from its4you_reports4you 
						inner join  its4you_reports4you_relcriteria on  its4you_reports4you_relcriteria.queryid = its4you_reports4you.reports4youid
						left join  its4you_reports4you_relcriteria_grouping on  its4you_reports4you_relcriteria.queryid =  its4you_reports4you_relcriteria_grouping.queryid 
								and  its4you_reports4you_relcriteria.groupid =  its4you_reports4you_relcriteria_grouping.groupid';
            $ssql.= " where its4you_reports4you.reports4youid = ? AND  its4you_reports4you_relcriteria.groupid = ? order by  its4you_reports4you_relcriteria.columnindex";

            $result = $adb->pquery($ssql, array($reportid, $groupId));
            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0)
                continue;
            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["columnindex"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = $relcriteriarow["value"];
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = Array();
                    for ($x = 0; $x < count($temp_val); $x++) {
                        list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
                        $temp_date = getValidDisplayDate(trim($temp_date));
                        if (trim($temp_time) != '')
                            $temp_date .= ' ' . $temp_time;
                        $val[$x] = $temp_date;
                    }
                    $advfilterval = implode(",", $val);
                }
                $criteria['value'] = decode_html($advfilterval);
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                $groupft_criteria[$this->j] = $criteria;
                $this->j++;
            }
        }
        $this->groupft_criteria = $groupft_criteria;
        return true;
    }

    /** Function to form a javascript to determine the start date and end date for a standard filter
     *  This function is to form a javascript to determine
     *  the start date and End date from the value selected in the combo lists
     */
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
        //ITS4YouReports::sshow("DOW2 $dayoftheweek");

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

        $sjsStr = '<script language="JavaScript" type="text/javaScript">
			function showDateRange(s_obj, e_obj, st_obj, et_obj, type )
			{
				if (type!="custom")
				{
					s_obj.readOnly=true;
					e_obj.readOnly=true;
					st_obj.style.visibility="hidden";
					et_obj.style.visibility="hidden";
				}
				else
				{
					s_obj.readOnly=false;
					e_obj.readOnly=false;
					st_obj.style.visibility="visible";
					et_obj.style.visibility="visible";
				}
				if( type == "today" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}
				else if( type == "yesterday" )
				{

					s_obj.value = "' . getValidDisplayDate($yesterday) . '";
					e_obj.value = "' . getValidDisplayDate($yesterday) . '";
				}
				else if( type == "tomorrow" )
				{

					s_obj.value = "' . getValidDisplayDate($tomorrow) . '";
					e_obj.value = "' . getValidDisplayDate($tomorrow) . '";
				}        
				else if( type == "thisweek" )
				{

					s_obj.value = "' . getValidDisplayDate($thisweek0) . '";
					e_obj.value = "' . getValidDisplayDate($thisweek1) . '";
				}                
				else if( type == "lastweek" )
				{

					s_obj.value = "' . getValidDisplayDate($lastweek0) . '";
					e_obj.value = "' . getValidDisplayDate($lastweek1) . '";
				}                
				else if( type == "nextweek" )
				{

					s_obj.value = "' . getValidDisplayDate($nextweek0) . '";
					e_obj.value = "' . getValidDisplayDate($nextweek1) . '";
				}                

				else if( type == "thismonth" )
				{

					s_obj.value = "' . getValidDisplayDate($currentmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($currentmonth1) . '";
				}                

				else if( type == "lastmonth" )
				{

					s_obj.value = "' . getValidDisplayDate($lastmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($lastmonth1) . '";
				}             
				else if( type == "nextmonth" )
				{

					s_obj.value = "' . getValidDisplayDate($nextmonth0) . '";
					e_obj.value = "' . getValidDisplayDate($nextmonth1) . '";
				}           
				else if( type == "next7days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next7days) . '";
				}                
				else if( type == "next15days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next15days) . '";
				}                
				else if( type == "next30days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next30days) . '";
				}                
				else if( type == "next60days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next60days) . '";
				}                
				else if( type == "next90days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next90days) . '";
				}        
				else if( type == "next120days" )
				{

					s_obj.value = "' . getValidDisplayDate($today) . '";
					e_obj.value = "' . getValidDisplayDate($next120days) . '";
				}        
				else if( type == "last7days" )
				{

					s_obj.value = "' . getValidDisplayDate($last7days) . '";
					e_obj.value =  "' . getValidDisplayDate($today) . '";
				}          
				else if( type == "last15days" )
				{

					s_obj.value = "' . getValidDisplayDate($last15days) . '";
					e_obj.value =  "' . getValidDisplayDate($today) . '";
				}                        
				else if( type == "last30days" )
				{

					s_obj.value = "' . getValidDisplayDate($last30days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}                
				else if( type == "last60days" )
				{

					s_obj.value = "' . getValidDisplayDate($last60days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last90days" )
				{

					s_obj.value = "' . getValidDisplayDate($last90days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last120days" )
				{

					s_obj.value = "' . getValidDisplayDate($last120days) . '";
					e_obj.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "thisfy" )
				{

					s_obj.value = "' . getValidDisplayDate($currentFY0) . '";
					e_obj.value = "' . getValidDisplayDate($currentFY1) . '";
				}                
				else if( type == "prevfy" )
				{

					s_obj.value = "' . getValidDisplayDate($lastFY0) . '";
					e_obj.value = "' . getValidDisplayDate($lastFY1) . '";
				}                
				else if( type == "nextfy" )
				{

					s_obj.value = "' . getValidDisplayDate($nextFY0) . '";
					e_obj.value = "' . getValidDisplayDate($nextFY1) . '";
				}                
				else if( type == "nextfq" )
				{

					s_obj.value = "' . getValidDisplayDate($nFq) . '";
					e_obj.value = "' . getValidDisplayDate($nFq1) . '";
				}                        
				else if( type == "prevfq" )
				{

					s_obj.value = "' . getValidDisplayDate($pFq) . '";
					e_obj.value = "' . getValidDisplayDate($pFq1) . '";
				}                
				else if( type == "thisfq" )
				{
					s_obj.value = "' . getValidDisplayDate($cFq) . '";
					e_obj.value = "' . getValidDisplayDate($cFq1) . '";
				}        
                                else if( type == "todaymore" )
				{
					s_obj.value = "' . getValidDisplayDate($todaymore_start) . '";
					e_obj.value = "";
				}
                                else if( type == "todayless" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($todayless_end) . '";
				}
                                else if( type == "older1days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older1days) . '";
				}
                                else if( type == "older7days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older7days) . '";
				}
                                else if( type == "older15days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older15days) . '";
				}
                                else if( type == "older30days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older30days) . '";
				}
                                else if( type == "older60days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older60days) . '";
				}
                                else if( type == "older90days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older90days) . '";
				}
                                else if( type == "older120days" )
				{
					s_obj.value = "";
					e_obj.value = "' . getValidDisplayDate($older120days) . '";
				}
				else
				{
//					s_obj.value = "";
//					e_obj.value = "";
				}        
			}        
		</script>';
        return $sjsStr;
    }

    /** Function to get the combo values for the standard filter
     *  This function get the combo values for the standard filter for the given its4you_reports4you
     *  and return a HTML string
     */
    function getSelectedStdFilterCriteria($selecteddatefilter = "") {
        $sshtml = '';

        foreach ($this->Date_Filter_Values AS $key => $value) {
            if ($selecteddatefilter == $key)
                $selected = "selected";
            else
                $selected = "";

            $sshtml .= "<option value='" . $key . "' " . $selected . ">" . vtranslate($value, $this->currentModule) . "</option>";
        }
        return $sshtml;
    }

    function getModulePrefix($module) {
        $adb = PearDatabase::getInstance();
        $secmodule_arr = explode("x", $module);
        $module_id = $secmodule_arr[0];
        $field_id = (isset($secmodule_arr[1]) && $secmodule_arr[1] != "" ? $secmodule_arr[1] : "");

        $fieldname = "";
        if ($field_id != "" && !in_array($field_id, ITS4YouReports::$customRelationTypes)) {
            $pquery = $adb->pquery("SELECT fieldlabel,uitype FROM vtiger_field WHERE fieldid=?", [$field_id]);
            $fieldname_row = $adb->fetchByAssoc($pquery, 0);
            $fieldname = " " . $fieldname_row["fieldlabel"];
        } elseif ($field_id == "INV") {
            $fieldname = " Inventory";
        } elseif ($field_id == "MIF") {
            $fieldname = " More Information";
        }
        return $fieldname;
    }

    function getSecModuleColumnsList($module) {
        if ($module != "") {
            $adb = PearDatabase::getInstance();
            $secmodule = explode(":", $module);
            for ($i = 0; $i < count($secmodule); $i++) {
                $secmodule_arr = explode("x", $secmodule[$i]);
                $module_id = $secmodule_arr[0];
                $field_id = (isset($secmodule_arr[1]) && $secmodule_arr[1] != "" ? $secmodule_arr[1] : "");

                $fieldname = $this->getModulePrefix($secmodule[$i]);

                $modulename = vtlib_getModuleNameById($module_id);
                if ($modulename != "") {
                    if (!isset($this->module_list[$modulename])) {
                        $this->initListOfModules();
                    }
                    if ($this->module_list[$modulename]) {
                        foreach ($this->module_list[$modulename] as $key => $value) {
                            /* $temp = $this->getColumnsListbyBlock($modulename, $key, $field_id);
                              if (!empty($ret_module_list[$modulename . $fieldname][$value])) {
                              if (!empty($temp)) {
                              $ret_module_list[$modulename . $fieldname][$value] = array_merge($ret_module_list[$modulename . $fieldname][$value], $temp);
                              }
                              } else {
                              $ret_module_list[$modulename . $fieldname][$value] = $this->getColumnsListbyBlock($modulename, $key, $field_id);
                              } */
//ITS4YouReports::getR4UDifTime("SEC COl List 03 $modulename -> $key -> $field_id");
                            $ret_module_list[$modulename . $fieldname][$value] = $this->getColumnsListbyBlock($modulename, $key, $field_id);
//ITS4YouReports::getR4UDifTime("SEC COl List 04");
                        }
                        $this->sec_module_columnslist[$modulename . $fieldname] = $ret_module_list[$modulename . $fieldname];
                    }
                }
            }
        }

        return $ret_module_list;
    }

    /** Function to get vtiger_fields for the given module and block
     *  This function gets the vtiger_fields for the given module
     *  It accepts the module and the block as arguments and
     *  returns the array column lists
     *  Array module_columnlist[ vtiger_fieldtablename:fieldcolname:module_fieldlabel1:fieldname:fieldtypeofdata]=fieldlabel
     */
    function getColumnsListbyBlock($module, $block, $relfieldid = "") {
        $r4u_columnlist_name = ITS4YouReports::getITS4YouReportStoreName($module, $block);
        $r4u_rel_fields_name = ITS4YouReports::getITS4YouReportStoreName("adv_rel_fields");
        $r4u_sel_fields_name = ITS4YouReports::getITS4YouReportStoreName("adv_sel_fields");
        //unset($_SESSION[$r4u_columnlist_name]);
        //unset($_SESSION[$r4u_rel_fields_name]);
        //unset($_SESSION[$r4u_sel_fields_name]);
//return false;

        $r4u_columnlist_name = $profileGlobalPermission = "";
        if ($r4u_columnlist_name != "" && isset($_SESSION[$r4u_columnlist_name]) && !empty($_SESSION[$r4u_columnlist_name])) {
            $module_columnlist = unserialize($_SESSION[$r4u_columnlist_name]);
            $this->adv_rel_fields = unserialize($_SESSION[$r4u_rel_fields_name]);
            $this->adv_sel_fields = unserialize($_SESSION[$r4u_sel_fields_name]);

            return $module_columnlist;
        } else {
            unset($_SESSION[$r4u_columnlist_name]);
            //unset($_SESSION[$r4u_rel_fields_name]);
            //unset($_SESSION[$r4u_sel_fields_name]);
            $adb = PearDatabase::getInstance();

            if (is_string($block))
                $block = explode(",", $block);

            $tabid = getTabid($module);
            if ($module == 'Calendar') {
                $tabid = array('9', '16');
            }
            $params = array($tabid, $block);

            $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
            if (file_exists($user_privileges_path)) {
                require($user_privileges_path);
            }

            //Security Check 
            if (is_admin($this->current_user) == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
                $sql = "select * from vtiger_field where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ") and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) ";

                //fix for Ticket #4016
                if ($module == "Calendar")
                    $sql.=" group by vtiger_field.fieldlabel order by sequence";
                else
                    $sql.=" order by sequence";
            }
            else {

                $profileList = getCurrentUserProfileList();
                $sql = "select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ")  and vtiger_field.block in (" . generateQuestionMarks($block) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
                if (count($profileList) > 0) {
                    $sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                    array_push($params, $profileList);
                }

                //fix for Ticket #4016
                if ($module == "Calendar")
                    $sql.=" group by vtiger_field.fieldid,vtiger_field.fieldlabel order by sequence";
                else
                    $sql.=" group by vtiger_field.fieldid order by sequence";
            }
//$adb->setDebug(true);
            $f_result = $adb->pquery($sql, $params);
//$adb->setDebug(false);

            $noofrows = $adb->num_rows($f_result);

            $blockname = getBlockName($block);

            $lead_converted_added = false;
            $duration_added = false;
            $hyperlink_added = false;
            $taxtotal_added = false;
            for ($i = 0; $i < $noofrows; $i++) {

                //$_SESSION_r4u_rel_fields = unserialize($_SESSION[$r4u_rel_fields_name]);
                //$_SESSION_r4u_sel_fields = unserialize($_SESSION[$r4u_sel_fields_name]);

                $fieldid = $adb->query_result($f_result, $i, "fieldid"); // ITS4YOU-UP SlOl 1. 10. 2013 10:46:35
                $fieldtablename = $adb->query_result($f_result, $i, "tablename");
                //if ($this->primarymodule != $module) {
                $fieldtablename = $fieldtablename;
                if ($relfieldid != "") {
                    $fieldtablename .= "_$relfieldid";
                }
                //}
                $fieldcolname = $adb->query_result($f_result, $i, "columnname");
                $fieldname = $adb->query_result($f_result, $i, "fieldname");
                $fieldtype = $adb->query_result($f_result, $i, "typeofdata");
                $uitype = $adb->query_result($f_result, $i, "uitype");
                $fieldtype = explode("~", $fieldtype);
                // Fix to get table alias for orderby and groupby sql
                /* if ($relfieldid == "" && in_array($uitype, array("10",))) {
                  $relfieldid = $fieldid;
                  } */

                $fieldtypeofdata = $fieldtype[0];
                //Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
                $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $fieldtypeofdata);
                if ($uitype == 68 || $uitype == 59) {
                    $fieldtypeofdata = 'V';
                }

                $fieldlabel = $adb->query_result($f_result, $i, "fieldlabel");
                $fieldlabel1 = $fieldlabel;

                if ($relfieldid != "") {
                    $relfieldid_str = $relfieldid;
                }
                // this is defining module id for uitype 10
                if ($relfieldid != "" && $relfieldid != "MIF" && $this->primarymodule != $module) {
                    $pquery1 = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldid = ? ", [$relfieldid]);
                    $rel_field_row = $adb->fetchByAssoc($pquery1, 0);
                    $rel_field_uitype = $rel_field_row["uitype"];
                    if ($rel_field_uitype == 10) {
                        $relfieldid_str = getTabid($module) . ":" . $relfieldid;
                    }
                }
                $module_lbl = vtranslate($module, $module);

                /*if($module=="Calendar" && in_array($fieldcolname,array("time_start","time_end",))){
                    continue;
                }*/
                $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $fieldtypeofdata . ($relfieldid != "" ? ":" . $relfieldid_str : "");

                // REL FIELDS DEV OLDO
                // $optionvalue = $fieldtablename.":".$fieldcolname.":".$module."_".$fieldlabel1.":".$fieldname.":".$fieldtypeofdata;
                $adv_rel_field_val = '$' . $module . '#' . $fieldname . '$' . "::" . $module_lbl . " " . $fieldlabel;
//ITS4YouReports::sshow($adv_rel_field_val);
                $this->adv_rel_fields[$fieldtypeofdata][] = $adv_rel_field_val;
                // ITS4YOU-CR SlOl 26. 3. 2014 10:57:41
                if (in_array($uitype, ITS4YouReports::$s_uitypes) && !array_key_exists($optionvalue, $this->adv_sel_fields) && !in_array($module, array("Users",))) {
                    $this->adv_sel_fields[$optionvalue] = true;
                }
                // ITS4YOU-END 26. 3. 2014 10:57:44 
                $translate_module = $module;
                //added to escape attachments fields in Reports as we have multiple attachments
                if ($module != 'HelpDesk' || $fieldname != 'filename') {
                    $module_columnlist[$optionvalue] = vtranslate($fieldlabel, $translate_module);
                }

                // ITS4YOU-CR SlOl - IS CONVERTED FIELD FOR LEADS 
                if ($module == "Leads" && $block == 13 && $i == ($noofrows - 1) && $lead_converted_added != true) {
                    $sc_col_str = "vtiger_leaddetails:converted:" . $module . "_Converted:converted:C";
                    $this->adv_sel_fields[$sc_col_str] = true;
                    $lead_converted_added = true;
                    $module_columnlist[$sc_col_str] = vtranslate("Converted", $module);
                }
                // CONVERTED END

                /**
                 * DURATION H:i START
                 *
                 * SEC_TO_TIME(SUM(TIME_TO_SEC(Duration)))
                 ** /
                if ($module === 'Calendar' && $block == 19 && $i == ($noofrows - 1) && $duration_added != true) {
                $sc_col_str = 'vtiger_activity:duration_sum_time:' . $module . '_Duration Time:duration_sum_time:T';
                $this->adv_sel_fields[$sc_col_str] = true;
                $duration_added = true;
                $module_columnlist[$sc_col_str] = vtranslate('Duration Time', $module);
                }
                // DURATION H:i END */

                // ITS4YOU-CR SlOl 7. 4. 2016 12:34:21 HYPERLINK
                if($i == ($noofrows - 1) && isset($this->hyperlinks_fields[$module])!=true){
                    $hl_col_str = "its4you_reports4you:r4u_hyperlink:" . $module . "_Hyperlink:r4u_hyperlink:V";
                    $this->hyperlinks_fields[$module] = $hl_col_str;
                    $module_columnlist[$hl_col_str] = vtranslate("Hyperlink", "ITS4YouReports");
                }
                // ITS4YOU-CR SlOl 16. 6. 2016 10:12:24
                $taxtotal_added = false;
                if($i == ($noofrows - 1) && ITS4YouReports::isInventoryModule($module)===true && $taxtotal_added != true){
                    if($this->taxtotalUsedInBlock!==true){
                        $tax_total_lbl = vtranslate("LBL_TOTAL_TAX_AMOUNT", "ITS4YouReports");
                        $tt_col_str = "vtiger_inventoryproductrel:ps_producttotalvatsum:" . $module . "_".$tax_total_lbl.":ps_producttotalvatsum:I";
                        $module_columnlist[$tt_col_str] = $tax_total_lbl;
                        $this->taxtotalUsedInBlock = true;
                    }
                    $taxtotal_added = true;
                }
                // ITS4YOU-END 
                unset($_SESSION[$r4u_rel_fields_name]);
                $_SESSION[$r4u_rel_fields_name] = serialize($this->adv_rel_fields);
                unset($_SESSION[$r4u_sel_fields_name]);
                $_SESSION[$r4u_sel_fields_name] = serialize($this->adv_sel_fields);
            }

            if ($blockname == 'LBL_RELATED_PRODUCTS' && ITS4YouReports::isInventoryModule($module)===true) {
                if ($relfieldid != "") {
                    $pquery = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldid = ? ", [$relfieldid]);
                    $rel_field_row = $adb->fetchByAssoc($pquery, 0);
                    $rel_field_uitype = $rel_field_row["uitype"];
                    if ($rel_field_uitype == 10) {
                        $relfieldid_str = ":" . getTabid($module) . ":" . $relfieldid;
                    } else {
                        $relfieldid_str = ":" . $relfieldid;
                    }
                }

                $fieldtablename = 'vtiger_inventoryproductrel';
                if ($relfieldid != "") {
                    $fieldtablename .= "_$relfieldid";
                }
                $fields = array('prodname' => $module_lbl . " " . vtranslate('LBL_PRODUCT_SERVICE_NAME', $this->currentModule),
                                'ps_productcategory' => $module_lbl . " " . vtranslate('LBL_ITEM_CATEGORY', $this->currentModule),
                                'ps_productno' => $module_lbl . " " . vtranslate('LBL_ITEM_NO', $this->currentModule),
                                'comment' => $module_lbl . " " . vtranslate('Comments', $module),
                                'quantity' => $module_lbl . " " . vtranslate('Quantity', $module),
                                'listprice' => $module_lbl . " " . vtranslate('List Price', $module),
                                'ps_producttotal' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL', $this->currentModule),
                                'discount' => $module_lbl . " " . vtranslate('Discount', $module),
                                'ps_productstotalafterdiscount' => $module_lbl . " " . vtranslate('LBL_PRODUCTTOTALAFTERDISCOUNT', $this->currentModule),
                                'ps_productvatsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_VAT_SUM', $this->currentModule),
                                'ps_producttotalsum' => $module_lbl . " " . vtranslate('LBL_PRODUCT_TOTAL_VAT', $this->currentModule),
                );
                global $site_URL;
                if (false !== strpos($site_URL, 'z-company')) {
                    $fields['ps_profit'] = $module_lbl . ' ' . vtranslate('Profit', $this->currentModule);
                }
                $fields_datatype = array('prodname' => 'V',
                                         'ps_productcategory' => 'V',
                                         'ps_productno' => 'V',
                                         'comment' => 'V',
                                         'prodname' => 'V',
                                         'quantity' => 'I',
                                         'listprice' => 'I',
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
                $module_lbl = vtranslate($module, $module);
                foreach ($fields as $fieldcolname => $label) {
                    $fieldtypeofdata = $fields_datatype[$fieldcolname];
                    $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $label . ":" . $fieldcolname . ":" . $fieldtypeofdata . $relfieldid_str;
                    $module_columnlist[$optionvalue] = $label; // $module_lbl." ".
                }
            }

            $_SESSION[$r4u_columnlist_name] = serialize($module_columnlist);
            return $module_columnlist;
        }
    }

    function getSMOwnerIDColumn($module, $relfieldid = "") {
        $adb = PearDatabase::getInstance();

        $tabid = getTabid($module);
        if ($module == 'Calendar') {
            $tabid = array('9', '16');
        }
        $params = array($tabid);

        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }
        $sql = "select * from vtiger_field where vtiger_field.tabid in (" . generateQuestionMarks($tabid) . ") and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) AND columnname = 'smownerid'";

        //fix for Ticket #4016
        if ($module == "Calendar")
            $sql.=" group by vtiger_field.fieldlabel order by sequence";
        else
            $sql.=" order by sequence";

        $result = $adb->pquery($sql, $params);
        $noofrows = $adb->num_rows($result);
        for ($i = 0; $i < $noofrows; $i++) {
            $fieldid = $adb->query_result($result, $i, "fieldid"); // ITS4YOU-UP SlOl 1. 10. 2013 10:46:35
            $fieldtablename = $adb->query_result($result, $i, "tablename") . $relfieldid;
            $fieldcolname = $adb->query_result($result, $i, "columnname");
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldtype = $adb->query_result($result, $i, "typeofdata");
            $uitype = $adb->query_result($result, $i, "uitype");
            $fieldtype = explode("~", $fieldtype);
            $fieldtypeofdata = $fieldtype[0];

            //Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
            $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $fieldtypeofdata);
            if ($uitype == 68 || $uitype == 59) {
                $fieldtypeofdata = 'V';
            }

            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $fieldlabel1 = $fieldlabel;


            // this is defining module id for uitype 10
            if ($relfieldid != "") {
                $pquery = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldid = ? ", [$relfieldid]);
                $rel_field_row = $adb->fetchByAssoc($pquery, 0);
                $rel_field_uitype = $rel_field_row["uitype"];
                if ($rel_field_uitype == 10) {
                    $relfieldid = getTabid($module) . ":" . $relfieldid;
                }
            }
            $module_lbl = vtranslate($module, $module);
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $fieldtypeofdata . ($relfieldid != "" ? ":" . $relfieldid : "");
            // $optionvalue = $fieldtablename.":".$fieldcolname.":".$module."_".$fieldlabel1.":".$fieldname.":".$fieldtypeofdata;
            $this->adv_rel_fields[$fieldtypeofdata][] = '$' . $module . '#' . $fieldname . '$' . "::" . $module_lbl . " " . $fieldlabel;
            //added to escape attachments fields in Reports as we have multiple attachments
            if ($module != 'HelpDesk' || $fieldname != 'filename')
                $module_columnlist[$optionvalue] = $fieldlabel; // $module_lbl." ".
        }
        return $module_columnlist;
    }

    public function getAdvancedFilterList($reportid) {
        global $modules;
        global $default_charset;
        $adb = PearDatabase::getInstance();
        $advft_criteria = array();

        $sql = 'SELECT * FROM  its4you_reports4you_relcriteria_grouping WHERE queryid = ? AND groupid != 0 ORDER BY groupid';
        $groupsresult = $adb->pquery($sql, array($reportid));

        $std_filter_columns = $this->getStdFilterColumns();
        if (!empty($std_filter_columns)) {
            global $default_charset;
            foreach ($std_filter_columns as $std_key => $std_value) {
                $std_filter_columns[$std_key] = html_entity_decode($std_value, ENT_QUOTES, $default_charset);
            }
        }

        $i = 1;
        //$j = 0;
        while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
            $groupId = $relcriteriagroup["groupid"];
            $groupCondition = $relcriteriagroup["group_condition"];

            $ssql = 'select  its4you_reports4you_relcriteria.* from its4you_reports4you 
						inner join  its4you_reports4you_relcriteria on  its4you_reports4you_relcriteria.queryid = its4you_reports4you.reports4youid
						left join  its4you_reports4you_relcriteria_grouping on  its4you_reports4you_relcriteria.queryid =  its4you_reports4you_relcriteria_grouping.queryid 
								and  its4you_reports4you_relcriteria.groupid =  its4you_reports4you_relcriteria_grouping.groupid';
            $ssql.= " where its4you_reports4you.reports4youid = ? AND  its4you_reports4you_relcriteria.groupid = ? order by  its4you_reports4you_relcriteria.columnindex";

            $result = $adb->pquery($ssql, array($reportid, $groupId));
            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0)
                continue;

            $this->j = 0;
            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["columnindex"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
                $col = explode(":", $relcriteriarow["columnname"]);

                if (in_array($criteria['columnname'], $std_filter_columns)) {
                    $f_date = array();
                    // ITS4YOU-UP SlOl 19. 11. 2015 15:14:41
                    if(!in_array($criteria['comparator'],ITS4YouReports::$dateNcomparators)){
                        $f_date = explode("<;@STDV@;>", $advfilterval);
                        $layout = Vtiger_Viewer::getDefaultLayoutName();
                        if($layout !== "v7"){
                            $f_date[] = DateTimeField::convertToUserFormat($layout[0]);
                            $f_date[] = DateTimeField::convertToUserFormat($layout[1]);
                        }
                        $advfilterval = implode("<;@STDV@;>", $f_date);
                    }
                } else {
                    $temp_val = explode(",", $relcriteriarow["value"]);
                    if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                        $val = Array();
                        for ($x = 0; $x < count($temp_val); $x++) {
                            list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
                            $temp_date = getValidDisplayDate(trim($temp_date));
                            if (trim($temp_time) != '')
                                $temp_date .= ' ' . $temp_time;
                            $val[$x] = $temp_date;
                        }
                        $advfilterval = implode(",", $val);
                    }
                }
                $criteria['value'] = decode_html($advfilterval);
                $criteria['value_hdn'] = $relcriteriarow["valuehdn"];
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                $advft_criteria[$i]['columns'][$this->j] = $criteria;
                $advft_criteria[$i]['condition'] = $groupCondition;
                $this->j++;
            }
            $i++;
        }

        $this->advft_criteria = $advft_criteria;
        return $advft_criteria;
    }

    function getSummariesFilterList($reportid) {
        global $modules;
        global $default_charset; //ITS4YOU VlMe Fix 
        $adb = PearDatabase::getInstance();
        $summaries_criteria = array();

        $ssql = 'select  its4you_reports4you_relcriteria_summaries.* from its4you_reports4you 
                        inner join  its4you_reports4you_relcriteria_summaries on  its4you_reports4you_relcriteria_summaries.reportid = its4you_reports4you.reports4youid';
        $ssql.= " where its4you_reports4you.reports4youid = ? order by  its4you_reports4you_relcriteria_summaries.columnindex";

        $result = $adb->pquery($ssql, array($reportid));
        $noOfColumns = $adb->num_rows($result);
        if ($noOfColumns > 0) {
            $this->j = 0;
            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["columnindex"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset); //ITS4YOU VlMe Fix 
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = $relcriteriarow["value"];
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = Array();
                    for ($x = 0; $x < count($temp_val); $x++) {
                        list($temp_date, $temp_time) = explode(" ", $temp_val[$x]);
                        $temp_date = getValidDisplayDate(trim($temp_date));
                        if (trim($temp_time) != '')
                            $temp_date .= ' ' . $temp_time;
                        $val[$x] = $temp_date;
                    }
                    $advfilterval = implode(",", $val);
                }
                $criteria['value'] = decode_html($advfilterval);
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                /* $summaries_criteria['columns'][$this->j] = $criteria;
                  $summaries_criteria['condition'] = $groupCondition; */
                $summaries_criteria[$this->j] = $criteria;
                $this->j++;
            }
        }
        $this->summaries_criteria = $summaries_criteria;
        return true;
    }

    public function getSelectedColumnListArray($reportid, $select_columname = "") {
        $adb = PearDatabase::getInstance();
        $default_charset = vglobal("default_charset");
        $sarray = array();
        $profileGlobalPermission = $selectedfields = '';

        $ssql = "SELECT its4you_reports4you_selectcolumn.* FROM  its4you_reports4you 
				INNER JOIN  its4you_reports4you_selectquery ON  its4you_reports4you_selectquery.queryid =  its4you_reports4you.reports4youid";
        $ssql .= " LEFT JOIN its4you_reports4you_selectcolumn ON its4you_reports4you_selectcolumn.queryid =  its4you_reports4you_selectquery.queryid";
        $ssql .= " WHERE  its4you_reports4you.reports4youid = ?";
        $ssql .= " ORDER BY its4you_reports4you_selectcolumn.columnindex";
//$adb->setDebug(true);
        $result = $adb->pquery($ssql, array($reportid));
//$adb->setDebug(false);
        $permitted_fields = Array();

// New code START
        $primarymodule_id = $this->reportinformations["primarymodule"];
        $primarymodule = vtlib_getModuleNameById($primarymodule_id);
        $this->init_list_for_module($primarymodule);
        $rel_modules = $this->getReportRelatedModules($primarymodule_id);

// New code END

        while ($columnslistrow = $adb->fetch_array($result)) {
            $fieldname = "";
            $fieldcolname = $columnslistrow["columnname"];
            $fieldcolname = html_entity_decode(trim($fieldcolname), ENT_QUOTES, $default_charset);

            // ITS4YOU-UP SlOl 21. 2. 2014 14:57:45 tru changed to false, because do not make any sense in my code ...
            $selmod_field_disabled = false;
            foreach ($rel_modules as $smodArr) {
                $smod = $smodArr['id'];
                $smod = vtlib_getModuleNameById($smod);
                if (preg_match('/:' . $smod . '_/', $smod) && vtlib_isModuleActive($smod)) {
                    $selmod_field_disabled = false;
                    break;
                }
            }

            if ($selmod_field_disabled == false) {
                list($tablename, $colname, $module_field, $fieldname, $single) = preg_split('/:/', $fieldcolname);
                $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
                if (file_exists($user_privileges_path)) {
                    require($user_privileges_path);
                }
                list($module, $fieldlabel) = explode('_', $module_field, 2);
                if (sizeof($permitted_fields) == 0 && is_admin($this->current_user) == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                    $permitted_fields = $this->getaccesfield($module);
                }
                //$mod_arr = explode('_', $module_field);
                //$mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
                //$fieldlabel = trim($mod_arr[1]);
                //modified code to support i18n issue 
                $mod_lbl = vtranslate($module, $module); //module
                // ITS4YOU-UP SlOl  4. 9. 2013 15:38:30
                if ('duration_sum_time' === $colname) {

                } else if ('inviteeid' === $colname) {
                    $fld_lbl = getTranslatedString('LBL_INVITE_USERS', 'Events');
                    $mod_lbl = vtranslate('Calendar', $module); //module
                }elseif (in_array($fieldlabel, array("campaignrelstatus", "access_count"))) {
                    $fld_lbl = vtranslate($fieldlabel, $this->currentModule); //fieldlabel
                } elseif ($fieldlabel == "LBL PRODUCT SERVICE NAME") {
                    $fld_lbl = vtranslate($fieldlabel, $this->currentModule); //fieldlabel
                } else {
                    $fld_lbl = vtranslate($fieldlabel, $module); //fieldlabel
                }
                // ITS4YOU-END 4. 9. 2013 15:38:34
                $fieldlabel = $fld_lbl . " ($mod_lbl)";

                if ($single == "SUM" || $single == "AVG" || $single == "MIN" || $single == "MAX" || $single == "COUNT")
                    $fieldlabel .= " (" . $single . ")";

                if (CheckFieldPermission($fieldname, $module) == 'true'
                    || ($module=="Calendar" && (CheckFieldPermission($fieldname, $module) == 'true' || CheckFieldPermission($fieldname, 'Events') == 'true'))
                    || in_array($fieldname,ITS4YouReports::$modTrackerColumns)
                    || ($colname == "r4u_hyperlink")
                    || ($colname == "ps_producttotalvatsum")
                    || ($colname == "converted")
                    || ($colname == "campaignrelstatus" && getFieldVisibilityPermission($module, $this->current_user->id, $colname) == 0)
                    || ($colname == "access_count" && getFieldVisibilityPermission("Emails", $this->current_user->id, $colname) == 0)
                    || ($colname == "crmid")
                    || ('inviteeid' === $colname)
                    || ('taxclass' === $colname)
                    || ('duration_sum_time' === $colname && 'true' === CheckFieldPermission('duration_hours', 'Events') && 'true' === CheckFieldPermission('duration_minutes', 'Events'))
                    || in_array($fieldname, self::$intentory_fields)
                ) {

                    if ($select_columname == $fieldcolname) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    $sarray[] = array("fieldcolname" => $fieldcolname,
                                      "selected" => $selected,
                                      "fieldlabel" => $fieldlabel);
                }
                // ITS4YOU-END 4. 9. 2013 15:33:15
            }
        }

        return $sarray;
    }

    //public function getSelectedColumnsList($reportid, $select_columname = "") {
    public function getSelectedColumnsList($sarray, $select_columname = "") {
        global $default_charset;
        $shtml = "";
        if (!is_array($sarray)) {
            $sarray = $this->getSelectedColumnListArray($sarray);
        }

        foreach ($sarray as $s_values) {
            $selecte_col = "";
            if ($s_values["fieldcolname"] == html_entity_decode($select_columname,ENT_QUOTES,$default_charset)) {
                $selecte_col = "selected";
            }
            $shtml .= "<option permission='yes' value=\"" . $s_values["fieldcolname"] . "\" $selecte_col>" . $s_values["fieldlabel"] . "</option>";
        }
        return $shtml;
    }

    function getEscapedColumns($selectedfields) {
        $fieldname = $selectedfields[3];
        if ($fieldname == "parent_id") {
            if ($this->primarymodule == "HelpDesk" && $selectedfields[0] == "vtiger_crmentityRelHelpDesk") {
                $querycolumn = "case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname when 'Contacts' then vtiger_contactdetailsRelHelpDesk.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelHelpDesk.setype 'Entity_type'";
                return $querycolumn;
            }
            if ($this->primarymodule == "Products" || $this->secondarymodule == "Products") {
                $querycolumn = "case vtiger_crmentityRelProducts.setype when 'Accounts' then vtiger_accountRelProducts.accountname when 'Leads' then vtiger_leaddetailsRelProducts.lastname when 'Potentials' then vtiger_potentialRelProducts.potentialname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelProducts.setype 'Entity_type'";
            }
            if ($this->primarymodule == "Calendar" || $this->secondarymodule == "Calendar") {
                $querycolumn = "case vtiger_crmentityRelCalendar.setype when 'Accounts' then vtiger_accountRelCalendar.accountname when 'Leads' then vtiger_leaddetailsRelCalendar.lastname when 'Potentials' then vtiger_potentialRelCalendar.potentialname when 'Quotes' then vtiger_quotesRelCalendar.subject when 'PurchaseOrder' then vtiger_purchaseorderRelCalendar.subject when 'Invoice' then vtiger_invoiceRelCalendar.subject End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelCalendar.setype 'Entity_type'";
            }
        }
        return $querycolumn;
    }

    /* function getaccesfield($module) {
      global $current_user;
      $access_fields = Array();
      $adb = PearDatabase::getInstance();
      $profileList = getCurrentUserProfileList();
      $query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
      $params = array();
      if ($module == "Calendar") {
      $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
      if (count($profileList) > 0) {
      $query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
      array_push($params, $profileList);
      }
      $query .= " group by vtiger_field.fieldid order by block,sequence";
      } else {
      array_push($params, $this->primarymoduleid, $this->relatedmodulesarray);
      $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
      if (count($profileList) > 0) {
      $query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
      array_push($params, $profileList);
      }
      $query .= " group by vtiger_field.fieldid order by block,sequence";
      }
      $result = $adb->pquery($query, $params);


      while ($collistrow = $adb->fetch_array($result)) {
      $access_fields[] = $collistrow["fieldname"];
      }
      return $access_fields;
      } */

    function getaccesfield($module) {
        $access_fields = Array();

        $adb = PearDatabase::getInstance();

        $profileList = getCurrentUserProfileList();
        $query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
        $params = array();
        if ($module == "Calendar") {
            if (count($profileList) > 0) {
                $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
                                                and vtiger_field.presence IN (0,2) and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
                                                and vtiger_field.presence IN (0,2) group by vtiger_field.fieldid order by block,sequence";
            }
        } else {
            array_push($params, $module);
            if (count($profileList) > 0) {
                $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
                                                and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
                                                and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
            }
        }
        $result = $adb->pquery($query, $params);

        while ($collistrow = $adb->fetch_array($result)) {
            $access_fields[] = $collistrow["fieldname"];
        }
        //added to include ticketid for Reports module in select columnlist for all users
        if ($module == "HelpDesk")
            $access_fields[] = "ticketid";
        return $access_fields;
    }

    // ITS4YOU-CR SlOl  3. 9. 2013 9:01:13
    /** Function to set the standard filter vtiger_fields for the given its4you_reports4you
     *  This function gets the standard filter vtiger_fields for the given its4you_reports4you
     *  and set the values to the corresponding variables
     *  It accepts the repordid as argument
     */
    function getSelectedStandardCriteria($reportid) {
        $adb = PearDatabase::getInstance();

        if (isset($_REQUEST["stdDateFilterField"]) && $_REQUEST["stdDateFilterField"] != "") {
            $this->stdselectedcolumn = vtlib_purify($_REQUEST["stdDateFilterField"]);
            $this->stdselectedfilter = vtlib_purify($_REQUEST["stdDateFilter"]);
            $this->startdate = vtlib_purify($_REQUEST["startdate"]);
            $this->enddate = vtlib_purify($_REQUEST["enddate"]);
        } else {

            $sSQL = "select  its4you_reports4you_datefilter.* from  its4you_reports4you_datefilter inner join  its4you_reports4you on  its4you_reports4you.reports4youid =  its4you_reports4you_datefilter.datefilterid where  its4you_reports4you.reports4youid=?";
            $result = $adb->pquery($sSQL, array($reportid));
            $selectedstdfilter = $adb->fetch_array($result);

            $this->stdselectedcolumn = $selectedstdfilter["datecolumnname"];
            $this->stdselectedfilter = $selectedstdfilter["datefilter"];
            if ($selectedstdfilter["datefilter"] == "custom") {
                if ($selectedstdfilter["startdate"] != "0000-00-00") {
                    $this->startdate = $selectedstdfilter["startdate"];
                }
                if ($selectedstdfilter["enddate"] != "0000-00-00") {
                    $this->enddate = $selectedstdfilter["enddate"];
                }
            }
        }
    }

    function getSelectedQFColumnsArray($reportid) {
        global $modules;
        $adb = PearDatabase::getInstance();
        $ssql = "select  its4you_reports4you_selectqfcolumn.* from its4you_reports4you";
        $ssql .= " left join  its4you_reports4you_selectqfcolumn on  its4you_reports4you_selectqfcolumn.queryid = its4you_reports4you.reports4youid";
        $ssql .= " where its4you_reports4you.reports4youid = ?";
        $ssql .= " order by  its4you_reports4you_selectqfcolumn.columnindex";
        $result = $adb->pquery($ssql, array($reportid));
        $permitted_fields = Array();

        $profileGlobalPermission = '';

        $selected_mod = preg_split('/:/', $this->relatedmodulesstring);
        array_push($selected_mod, $this->primarymoduleid);

        $sarray = array();
        while ($columnslistrow = $adb->fetch_array($result)) {
            $fieldname = "";
            $fieldcolname = $columnslistrow["columnname"];

            $selmod_field_disabled = true;
            foreach ($selected_mod as $smod) {
                $smodule = vtlib_getModuleNameById($smod);
                if ((stripos($fieldcolname, ":" . $smodule . "_") > -1) && vtlib_isModuleActive($smodule)) {
                    $selmod_field_disabled = false;
                    break;
                }
            }
            if ($selmod_field_disabled == false) {
                list($tablename, $colname, $module_field, $fieldname, $single) = preg_split('/:/', $fieldcolname);
                $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
                if (file_exists($user_privileges_path)) {
                    require($user_privileges_path);
                }
                list($module, $field) = preg_split('/_/', $module_field);

                if (sizeof($permitted_fields) == 0 && is_admin($this->current_user) == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                    $permitted_fields = $this->getaccesfield($module);
                }
                //$querycolumns = $this->getEscapedColumns($selectedfields);
                $fieldlabel = trim(str_replace($module, " ", $module_field));
                $mod_arr = explode('_', $fieldlabel);
                $mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
                $fieldlabel = trim($fieldlabel);
                //modified code to support i18n issue 
                $mod_lbl = vtranslate($mod, $module); //module
                $fld_lbl = vtranslate($fieldlabel, $module); //fieldlabel
                $fieldlabel = $mod_lbl . " " . $fld_lbl;

                // ITS4YOU-UP SlOl 4. 9. 2013 15:32:14 disabled options changed / we will remove options which are users not permited to view
                /* if(CheckFieldPermission($fieldname,$mod) != 'true' && $colname!="crmid" && !in_array($fieldname,array('prodname','quantity','listprice','discount','comment'))
                  {
                  $shtml .= "<option permission='no' value=\"".$fieldcolname."\" disabled = 'true'>".$fieldlabel."</option>";
                  }
                  else
                  {
                  $shtml .= "<option permission='yes' value=\"".$fieldcolname."\" ".$selected.">".$fieldlabel."</option>";
                  } */
                if (CheckFieldPermission($fieldname, $mod) == 'true' || $colname == "crmid" || in_array($fieldname, self::$intentory_fields)) {
                    $selected = "";
                    $sarray[] = array("fieldcolname" => $fieldcolname,
                                      "selected" => $selected,
                                      "fieldlabel" => $fieldlabel);
                }
                // ITS4YOU-END 4. 9. 2013 15:33:15
            }
            //end
        }
        return $sarray;
    }

    function getSelectedQFColumnsList($reportid) {
        $shtml = "<option permission='yes' value=\"none\" >" . vtranslate("LBL_NONE") . "</option>";
        $sarray = $this->getSelectedQFColumnsArray($reportid);
        foreach ($sarray as $key => $sarray_value) {
            $shtml .= "<option permission='yes' value=\"" . $sarray_value["fieldcolname"] . "\" >" . $sarray_value["fieldlabel"] . "</option>";
        }
        /* 		
          global $modules;
          global $log,$current_user;
          $adb = PearDatabase::getInstance();
          $ssql = "select  its4you_reports4you_selectqfcolumn.* from its4you_reports4you";
          $ssql .= " left join  its4you_reports4you_selectqfcolumn on  its4you_reports4you_selectqfcolumn.queryid = its4you_reports4you.reports4youid";
          $ssql .= " where its4you_reports4you.reports4youid = ?";
          $ssql .= " order by  its4you_reports4you_selectqfcolumn.columnindex";
          $result = $adb->pquery($ssql, array($reportid));
          $permitted_fields = Array();

          $selected_mod = preg_split('/:/',$this->relatedmodulesstring);
          array_push($selected_mod,$this->primarymoduleid);

          while($columnslistrow = $adb->fetch_array($result))
          {
          $fieldname ="";
          $fieldcolname = $columnslistrow["columnname"];

          $selmod_field_disabled = true;
          foreach($selected_mod as $smod){
          $smodule = vtlib_getModuleNameById($smod);
          if((stripos($fieldcolname,":".$smodule."_")>-1) && vtlib_isModuleActive($smodule)){
          $selmod_field_disabled = false;
          break;
          }
          }
          if($selmod_field_disabled==false){
          list($tablename,$colname,$module_field,$fieldname,$single) = preg_split('/:/',$fieldcolname);
          require('user_privileges/user_privileges_'.$current_user->id.'.php');
          list($module,$field) = preg_split('/_/',$module_field);

          if(sizeof($permitted_fields) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
          {
          $permitted_fields = $this->getaccesfield($module);
          }
          $querycolumns = $this->getEscapedColumns($selectedfields);
          $fieldlabel = trim(str_replace($module," ",$module_field));
          $mod_arr=explode('_',$fieldlabel);
          $mod = ($mod_arr[0] == '')?$module:$mod_arr[0];
          $fieldlabel = trim(str_replace("_"," ",$fieldlabel));
          //modified code to support i18n issue
          $mod_lbl = vtranslate($mod,$module); //module
          $fld_lbl = vtranslate($fieldlabel,$module); //fieldlabel
          $fieldlabel = $mod_lbl." ".$fld_lbl;

          global $intentory_fields;
          if(CheckFieldPermission($fieldname,$mod) == 'true' || $colname=="crmid" || in_array($fieldname,$intentory_fields))
          {
          $shtml .= "<option permission='yes' value=\"".$fieldcolname."\" ".$selected.">".$fieldlabel."</option>";
          }
          // ITS4YOU-END 4. 9. 2013 15:33:15
          }
          //end
          } */
        return $shtml;
    }

    public static function getSubOrdinateUsersArray($add_current_userid = false) {
        $adb = PearDatabase::getInstance();
        global $current_user;
        $subordinate_users = array();
        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
            $su_query = $adb->pquery("select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'", array());
            $subordinate_users = Array();
            if ($add_current_userid) {
                $subordinate_users[] = $current_user->id;
            }
            for ($i = 0; $i < $adb->num_rows($su_query); $i++) {
                $subordinate_users[] = $adb->query_result($su_query, $i, 'userid');
            }
        }
        return $subordinate_users;
    }

    public function deleteSingleReports4You() {
        $die = true;
        if ($this->record != "") {
            $adb = PearDatabase::getInstance();
            $subordinate_users = array();
            $is_admin = is_admin($this->current_user);
            if (!$is_admin) {
                $subordinate_users = ITS4YouReports::getSubOrdinateUsersArray(true);
            }
            if (($is_admin) || (in_array($this->reportinformations["template_owner"], $subordinate_users))) {
                $adb->pquery("UPDATE its4you_reports4you SET deleted=? WHERE reports4youid = ?", array(1, $this->record));
                $die = false;
            } else {
                $die = true;
            }
        }
        if ($die === true) {
            $this->DieDuePermission();
        }
        return $die;
    }

    public static function deleteReports4You($reportid) {
        echo "MASS Delete canceled !!!";
        exit;
        /*
          $adb = $this->db;

          $checkSql = "SELECT primarymodule FROM its4you_reports4you_modules WHERE reportmodulesid=?";
          $checkRes = $adb->pquery($checkSql, array($reportid));
          $checkRow = $adb->fetchByAssoc($checkRes);
          $primary_module = vtlib_getModuleNameById($checkRow["primarymodule"]);
          //if we are trying to delete template that is not allowed for current user then die because user should not be able to see the template
          $this->CheckReportPermissions($primary_module, $reportid);

          $d_reportsql = "DELETE FROM its4you_reports4you  WHERE reports4youid = ?";
          $d_reportsqlresult = $adb->pquery($d_reportsql, array($reportid));

          $d_reportsql = "DELETE FROM its4you_reports4you_settings  WHERE reportid = ?";
          $d_reportsqlresult = $adb->pquery($d_reportsql, array($reportid));

          $d_querysql = "DELETE FROM its4you_reports4you_selectquery WHERE queryid = ?";
          $d_queryresult = $adb->pquery($d_querysql, array($reportid));

          $d_selectedcolumns = "DELETE FROM its4you_reports4you_selectcolumn WHERE queryid = ?";
          $d_columnsqlresult = $adb->pquery($d_selectedcolumns, array($reportid));

          $d_selectedqfcolumns = "DELETE FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
          $d_columnsqlqfresult = $adb->pquery($d_selectedqfcolumns, array($reportid));

          $d_shared = "DELETE FROM its4you_reports4you_sharing WHERE reports4youid = ?";
          $d_sharedresult = $adb->pquery($d_shared, array($reportid));

          $d_modules = "DELETE FROM its4you_reports4you_modules WHERE reportmodulesid = ?";
          $d_modulesresult = $adb->pquery($d_modules, array($reportid));

          $d_limit = "DELETE FROM its4you_reports4you_limit WHERE reportid = ?";
          $d_limitresult = $adb->pquery($d_limit, array($reportid));

          $d_sortcol_all = "DELETE FROM its4you_reports4you_sortcol WHERE reportid = ?";
          $d_sortcol_all_result = $adb->pquery($d_sortcol_all, array($reportid));

          $d_datefilter = "DELETE FROM its4you_reports4you_datefilter WHERE datefilterid = ?";
          $d_datefilter_result = $adb->pquery($d_datefilter, array($reportid));

          $d_summary = "DELETE FROM its4you_reports4you_summary WHERE reportsummaryid = ?";
          $d_summary_result = $adb->pquery($d_summary, array($reportid));

          $d_adv_criteria = "DELETE FROM its4you_reports4you_relcriteria WHERE queryid = ?";
          $d_adv_criteria_result = $adb->pquery($d_adv_criteria, array($reportid));

          $d_adv_criteria_grouping = "DELETE FROM its4you_reports4you_relcriteria_grouping WHERE queryid = ?";
          $d_adv_criteria_grouping_result = $adb->pquery($d_adv_criteria_grouping, array($reportid));

          $deleteScheduledReportSql = "DELETE FROM its4you_reports4you_scheduled_reports WHERE reportid=?";
          $adb->pquery($deleteScheduledReportSql, array($reportid));
         */
        return true;
    }

    // END
    /** Function to get the Reports inside each modules
     *  This function accepts the folderid
     *  This Generates the Reports under each Reports module
     *  This Returns a HTML sring
     */
    public static function sgetRptsforFldr($rpt_fldr_id, $paramsList = false, $search_params = array()) {
        $srptdetails = $user_group_query = $current_user_parent_role_seq = '';
        $adb = PearDatabase::getInstance();
        global $mod_strings;
        $returndata = Array();

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        global $current_user;

        require_once('include/utils/UserInfoUtil.php');
        $sql = "SELECT DISTINCT reports4youid, reports4youname, primarymodule, reporttype, its4you_reports4you.description,  folderid, foldername, owner, vtiger_tab.tablabel as tablabel, IF(its4you_reports4you_userstatus.sequence IS NOT NULL,its4you_reports4you_userstatus.sequence,1) AS sequence, its4you_reports4you_scheduled_reports.is_active AS scheduling 
				FROM its4you_reports4you 
				LEFT JOIN its4you_reports4you_modules ON its4you_reports4you_modules.reportmodulesid = its4you_reports4you.reports4youid 
				INNER JOIN its4you_reports4you_folder USING(folderid) 
				LEFT JOIN vtiger_tab ON vtiger_tab.tabid = its4you_reports4you_modules.primarymodule 
          INNER JOIN its4you_reports4you_settings ON its4you_reports4you_settings.reportid = its4you_reports4you.reports4youid
          LEFT JOIN its4you_reports4you_userstatus ON its4you_reports4you.reports4youid = its4you_reports4you_userstatus.reportid
          LEFT JOIN its4you_reports4you_scheduled_reports ON its4you_reports4you.reports4youid = its4you_reports4you_scheduled_reports.reportid
          WHERE its4you_reports4you.deleted=0 ";

        $params = array();

        // If information is required only for specific report folder?
        if ($rpt_fldr_id !== false) {
            $sql .= " AND its4you_reports4you_folder.folderid=?";
            $params[] = $rpt_fldr_id;
        }

        // reportname -> reports4youname
        // reporttype -> reporttype
        // tablabel -> vtiger_tab.name 
        // foldername -> foldername
        // owner -> its4you_reports4you_settings.owner
        // description -> description
//ini_set("display_errors",1);error_reporting(63);
        if(!empty($search_params)){
            $searchArray = ITS4YouReports_List_Header::getSearchParamsArray($search_params);
            foreach($searchArray as $headerColumn => $searchValue){
                $hinfo = new ITS4YouReports_List_Header($headerColumn);
                $search_sql_array[] = $hinfo->getHeaderColumnSql($headerColumn,$searchValue);
            }
            if(!empty($search_sql_array)){
                $search_sql = implode(" AND ", $search_sql_array);
                $sql .= " AND $search_sql ";
            }
        }

        global $current_user;
        $current_user_id = $current_user->id;

        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user_id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }
        require_once('include/utils/GetUserGroups.php');
        $userGroups = new GetUserGroups();
        $userGroups->getAllUserGroups($current_user_id);
        $user_groups = $userGroups->user_groups;
        if (!empty($user_groups) && is_admin($current_user) == false) {
            $user_group_query = " (shareid IN (" . generateQuestionMarks($user_groups) . ") AND setype='groups') OR";
            array_push($params, $user_groups);
        }
        // BF Roles FIX S
        if (isset($current_user->column_fields['roleid']) && is_admin($current_user) == false) {
            $user_group_query .= " (reports4youid IN (SELECT reports4youid FROM its4you_reports4you_sharing WHERE shareid = ? ) AND setype='roles') OR";
            array_push($params, $current_user->column_fields['roleid']);
        }
        // BF Roles FIX E
        $non_admin_query = " its4you_reports4you.reports4youid IN (SELECT reports4youid from its4you_reports4you_sharing WHERE $user_group_query (shareid=? AND setype='users'))";

        if (is_admin($current_user) == false) {
            $sql .= " AND ( (" . $non_admin_query . ") or its4you_reports4you_settings.sharingtype='Public' or its4you_reports4you_settings.owner = ? or its4you_reports4you_settings.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
            array_push($params, $current_user_id);
            array_push($params, $current_user_id);
        }

        if($request->has("orderby") && $request->get("orderby")!="" && $request->has("sortorder") && $request->get("sortorder")!=""){
            switch ($request->get("orderby")) {
                case "reportname":
                    $orderby = "reports4youname";
                    break;
                case "tablabel":
                    $orderby = "vtiger_tab.name";
                    break;
                case "owner":
                    $orderby = "its4you_reports4you_settings.owner";
                    break;
                default:
                    $orderby = $request->get("orderby");
                    break;
            }
            $paramsList['orderBy'] = $orderby;
            $paramsList['sortBy'] = $request->get("sortorder");
            $paramsList['startIndex'] = ($paramsList['startIndex']!=""?$paramsList['startIndex']:0);
            $paramsList['pageLimit'] = ($paramsList['pageLimit']!=""?$paramsList['pageLimit']:20);
        }
        if ($paramsList) {
            $startIndex = $paramsList['startIndex'];
            $pageLimit = $paramsList['pageLimit'];
            $orderBy = $paramsList['orderBy'];
            $sortBy = $paramsList['sortBy'];
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy $sortBy";
            } else {
                $sql .= " ORDER BY its4you_reports4you.reports4youid DESC ";
            }
            $sql .= " LIMIT $startIndex, $pageLimit";
        }
        $query = $adb->pquery("select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'", array());
        $subordinate_users = Array();
        for ($i = 0; $i < $adb->num_rows($query); $i++) {
            $subordinate_users[] = $adb->query_result($query, $i, 'userid');
        }
//ITS4YouReports::sshow("<br/><br/><br/><br/><br/>");
//ITS4YouReports::sshow($paramsList);
//$adb->setDebug(true);
        $result = $adb->pquery($sql, $params);
//$adb->setDebug(false);exit;

        $report = $adb->fetch_array($result);
        if ($adb->num_rows($result) > 0) {
            do {
                $report_details = Array();
                // $report_details['customizable'] = $report["customizable"];
                $report_details['reportid'] = $report["reports4youid"];
                $report_details['primarymodule'] = $report["primarymodule"];
                $report_details['tablabel'] = $report["tablabel"];
                $report_details['owner'] = decode_html(getUserFullName($report["owner"]));
                $report_details['foldername'] = $report["foldername"];
                // $report_details['state'] = $report["state"];
                $reporttype = $report["reporttype"];
                $report_details['reporttype'] = vtranslate($reporttype, "ITS4YouReports");
                $report_details['state'] = "SAVED";
                // ITS4YOU-UP SlOl 26. 2. 2016 13:08:11
                $description = $report["description"];
                $layout = Vtiger_Viewer::getDefaultLayoutName();
                if($layout == "v7"){
                    $maxDescLg = 30;
                } else {
                    $maxDescLg = 50;
                }
                $description = (strlen($description) > $maxDescLg) ? substr($description, 0, $maxDescLg) . '...' : $description;
                $report_details['description'] = $description;


                $reports4youname = '';
                $reports4youname .= $report["reports4youname"];
                if ($report["scheduling"] == "1") {
                    $reports4youname .= '&nbsp;<img src="modules/ITS4YouReports/img/Cron.png" style="vertical-align: middle;height:15px;" />';
                }
                if (ITS4YouReports::getKeyMetricsRowsCount($report["reports4youid"])) {
                    $reports4youname .= '&nbsp;<i class="fa fa-key" title="'.vtranslate('UsedInKeyMetricsRows', $request->getModule()).'"></i>';
                }
                $report_details['reportname'] = $reports4youname;
                $report_details['sharingtype'] = $report["sharingtype"];

                if ($reporttype == "custom_report") {
                    if (is_admin($current_user) || ITS4YouReports::CheckSharing($report['reports4youid'])) {
                        $report_details['editable'] = 'false';
                    } else {
                        $report_details['editable'] = 'false';
                    }
                } elseif (is_admin($current_user) == true || in_array($current_user_id, $subordinate_users) || $report["owner"] == $current_user_id) {
                    $report_details['editable'] = 'true';
                } else {
                    $report_details['editable'] = 'false';
                }
                $primarymodule = vtlib_getModuleNameById($report["primarymodule"]);
                if ($reporttype == "custom_report"
                    || isPermitted($primarymodule, 'index') == "yes"
                    || true === ITS4YouReports::CheckSharing($report["reports4youid"])
                ) {
                    if ($rpt_fldr_id !== false) {
                        $returndata[$report["folderid"]][] = $report_details;
                    } else {
                        $returndata[][] = $report_details;
                    }
                }
            } while ($report = $adb->fetch_array($result));
        }
//echo "<pre><br/><br/><br/><br/><br/><br/>";print_r(count($returndata));echo "</pre>";

        if ($rpt_fldr_id !== false) {
            $returndata = $returndata[$rpt_fldr_id];
        }
        return $returndata;
    }

    // ITS4YOU-CR SlOl 24. 2. 2014 14:25:52
    function sgetNewColumntoTotal($Objects) {
        $options = Array();

        // CHECKOUT FIX ??????
        $primarymoduleid = $secondarymodule = '';

        $options [] = $this->sgetColumnstoTotalHTML($primarymoduleid, 0);
        if (!empty($secondarymodule)) {
            for ($i = 0; $i < count($secondarymodule); $i++) {
                $options [] = $this->sgetColumnstoTotalHTML(vtlib_getModuleNameById($secondarymodule[$i]), ($i + 1));
            }
        }
        return $options;
    }

    // ITS4YOU-CR SlOl 27. 2. 2014 10:57:16
    function getSelectedColumnsToTotal($reportid) {
        $adb = PearDatabase::getInstance();
        $this->columnssummary = array();
        $sgetNewColumntoTotalSelected = $reportsummaryrow = array();
        $ssql = "select distinct its4you_reports4you_summary.* from its4you_reports4you_summary inner join its4you_reports4you on its4you_reports4you.reports4youid = its4you_reports4you_summary.reportsummaryid where its4you_reports4you.reports4youid=?";
//$adb->setDebug(true);
        $result = $adb->pquery($ssql, array($reportid));
//$adb->setDebug(false);
        if ($result) {
            do {
                if ($reportsummaryrow["columnname"] != "") {
                    $sgetNewColumntoTotalSelected[] = $reportsummaryrow["columnname"];
                    $this->columnssummary[] = $reportsummaryrow["columnname"];
                }
            } while ($reportsummaryrow = $adb->fetch_array($result));
        }
        return $sgetNewColumntoTotalSelected;
    }

    // ITS4YOU-CR SlOl 26. 2. 2014 8:33:25
    function sgetNewColumntoTotalSelected($reportid, $R_Objects, $sgetNewColumntoTotalSelected = array()) {
        $adb = PearDatabase::getInstance();
        $default_charset = vglobal("default_charset");
        $options_list = Array();
        /* if ($reportid != "") {
          if ($_REQUEST["file"] != "ChangeSteps")
          $sgetNewColumntoTotalSelected = ITS4YouReports::getSelectedColumnsToTotal($reportid);
          } */
        if (!empty($sgetNewColumntoTotalSelected)) {
            foreach ($sgetNewColumntoTotalSelected as $sgNKey => $sgNVal) {
                $new_sget_array[$sgNKey] = html_entity_decode(trim($sgNVal), ENT_QUOTES, $default_charset);
            }
            $sgetNewColumntoTotalSelected = $new_sget_array;
        }

        foreach ($this->columnssummary as $key => $sum_column) {
            $sum_column = html_entity_decode(trim($sum_column), ENT_QUOTES, $default_charset);
            $options = array();
            $sum_col_array = explode(":", $sum_column);
            $lbl_array = explode("_", $sum_col_array[3]);
            $module_lbl = $lbl_array[0];
            unset($lbl_array[0]);
            $column_lbl = implode(" ", $lbl_array);

            $option_label = vtranslate("SINGLE_" . $module_lbl, $module_lbl) . ' - ' . vtranslate($column_lbl, $module_lbl);

            $type_col_array = explode("_", $sum_col_array[5]);
            $typeofdata = $type_col_array[0];
            $calculation_type = $type_col_array[1];

            $last_key = count($sum_col_array) - 1;
            $fieldid = "";
            if ((is_numeric($sum_col_array[$last_key]) && is_numeric($sum_col_array[($last_key - 1)])) || in_array($sum_col_array[$last_key], ITS4YouReports::$customRelationTypes)) {
                $fieldid = ":" . $sum_col_array[$last_key];
            }
            $selected_col = $sum_col_array[1] . ":" . $sum_col_array[2] . ":" . $sum_col_array[3] . ":" . $sum_col_array[4] . ":" . $typeofdata . $fieldid;
            if (in_array($selected_col, $R_Objects)) {
                if (!isset($options_list[$option_label]) || !in_array($sum_column, $options_list[$option_label])) {
                    $checked = "";
                    if (in_array($sum_column, $sgetNewColumntoTotalSelected)) {
                        $checked = "checked='checked'";
                    }
                    $options_list[$option_label][] = array("name" => $sum_column, "checked" => $checked);
                }
            }
        }

        return $options_list;
    }

    // ITS4YOU-CR SlOl 26. 2. 2014 16:10:21 
    /*function getQuickFiltersHTML($quick_columns_array, $quick_columns_arraySelected = array()) {
        $adb = PearDatabase::getInstance();
        $options_list = Array();
        // $this->record

        if ($this->record != "") {
            // if (!isset($this->columnssummary) && $_REQUEST["file"] != "ChangeSteps")
            $ssql = "SELECT * FROM its4you_reports4you_selectqfcolumn WHERE queryid = ?";
            $result = $adb->pquery($ssql, array($this->record));
            if ($result) {
                while ($reportqfrow = $adb->fetchByAssoc($result)) {
                    if ($reportqfrow["columnname"] != "" && !in_array($reportqfrow["columnname"], $quick_columns_arraySelected)) {
                        $quick_columns_arraySelected[] = $reportqfrow["columnname"];
                    }
                }
            }
        }
        foreach ($quick_columns_array as $key => $sum_column) {
            $options = array();
            $sum_col_array = explode(":", $sum_column);

            $lbl_array = explode("_", $sum_col_array[2]);

            $module_lbl = $lbl_array[0];
            unset($lbl_array[0]);
            $column_lbl = implode(" ", $lbl_array);

            if ($module_lbl == "Calendar") {
                $option_label = vtranslate($module_lbl, $module_lbl) . ' - ' . vtranslate($column_lbl, $module_lbl);
            } else {
                $option_label = vtranslate("SINGLE_" . $module_lbl, $module_lbl) . ' - ' . vtranslate($column_lbl, $module_lbl);
            }

            $type_col_array = explode("_", $sum_col_array[4]);
            $typeofdata = $type_col_array[0];
            $calculation_type = $type_col_array[1];

            $last_key = count($sum_col_array) - 1;
            $fieldid = "";
            if (in_array($sum_col_array[$last_key], ITS4YouReports::$customRelationTypes)) {
                $fieldid = ":" . $sum_col_array[$last_key];
            }
            $selected_col = $sum_col_array[0] . ":" . $sum_col_array[1] . ":" . $sum_col_array[2] . ":" . $sum_col_array[3] . ":" . $typeofdata . $fieldid;

            if (!isset($options_list[$option_label]) || empty($options_list[$option_label])) {
                if (!in_array($sum_column, $options_list[$option_label])) {
                    $checked = "";
                    if (in_array($sum_column, $quick_columns_arraySelected)) {
                        $checked = "checked='checked'";
                    }
                    $options_list[$option_label][] = array("name" => $sum_column, "checked" => $checked);
                }
            }
        }
        return $options_list;
    }*/

    // ITS4YOU-CR SlOl 27. 2. 2014 15:06:27
    function getStdCriteriaByModule($module) {
        $adb = PearDatabase::getInstance();
        $tabid = getTabid($module);

        $blockids = $params = $profileList = $profileGlobalPermission = array();

        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }

        $module_info = $this->getCustomViewModuleInfo($module);
        if (!isset($this->module_list) || empty($this->module_list)) {
            $this->initListOfModules();
        }
        foreach ($this->module_list[$module] as $key => $blockid) {
            $blockids[] = $blockid;
        }
        if (is_array($blockids)) {
            $blocks_params = implode(",", $blockids);
        } else {
            $blocks_params = $blockids;
        }
        $blocks_params = trim($blocks_params, ',');
        // ITS4YOU-UP SlOl 19. 10. 2015 11:39:54
        if($tabid=="9"){
            $where_t_b_conditions = " where vtiger_field.tabid IN (9,16) and vtiger_field.block in ($blocks_params) and vtiger_field.uitype in (5,6,23,70)";
        }else{
            $where_t_b_conditions = " where vtiger_field.tabid=$tabid ";
            if($blocks_params!=""){
                $where_t_b_conditions .= " and vtiger_field.block in ($blocks_params) ";
            }
            $where_t_b_conditions .= " and vtiger_field.uitype in (5,6,23,70) ";
        }
        // ITS4YOU-END
        if (is_admin($this->current_user) == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid ";
            $sql.= $where_t_b_conditions;
            $sql.= " and vtiger_field.presence in (0,2) order by vtiger_field.sequence";
        } else {
            $profileList = getCurrentUserProfileList();
            $sql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join  vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid ";
            $sql.= $where_t_b_conditions;
            $sql.= " and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";

            if (count($profileList) > 0) {
                $sql.= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params, $profileList);
            }

            $sql.= " order by vtiger_field.sequence";
        }
        $result = $adb->pquery($sql, $profileList);
        while ($criteriatyperow = $adb->fetch_array($result)) {
            $fieldtablename = $criteriatyperow["tablename"];
            $fieldcolname = $criteriatyperow["columnname"];
            $fieldlabel = $criteriatyperow["fieldlabel"];
            $fieldname = $criteriatyperow["fieldname"];
            $fieldlabel1 = $fieldlabel;
            $typeofdata = explode("~", $criteriatyperow["typeofdata"]);
            $typeofdata = $typeofdata[0];
//             $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $typeofdata;
            $optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "_" . $fieldlabel1 . ":" . $fieldname . ":" . $typeofdata;
            $stdcriteria_list[$optionvalue] = $fieldlabel;
        }
        return $stdcriteria_list;
    }

    // ITS4YOU-CR SlOl 27. 2. 2014 15:07:44
    function getCustomViewModuleInfo($module) {
        $adb = PearDatabase::getInstance();
        global $current_language;
        $this->module_list = array();
        if ($module == "Events") {
            $module = "Calendar";
        }

        if (vtlib_isModuleActive($module)) {
            $current_mod_strings = return_specified_module_language($current_language, $module);
            $block_info = Array();
            $modules_list = explode(",", $module);
            if ($module == "Calendar") {
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
                    $lan_block_label = $current_mod_strings[$block_label];
                    if (isset($block_info[$lan_block_label]) && $block_info[$lan_block_label] != '') {
                        $block_info[$lan_block_label] = $block_info[$lan_block_label] . "," . $block_result['block'];
                    } else {
                        $block_info[$lan_block_label] = $block_result['block'];
                    }
                }
                $pre_block_label = $lan_block_label;
            }
            $this->module_list[$module] = $block_info;
        }
        return $this->module_list;
    }

    // ITS4YOU-CR SlOl 3. 3. 2014 14:28:15
    function getRequestCriteria($sel_fields = array()) {
        $r_conditions = array();
        global $default_charset;
//<<<<<<<advancedfilter>>>>>>>>
        $_REQUEST["advft_criteria"] = str_replace("<@AMPKO@>","&",$_REQUEST["advft_criteria"]);
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $advft_criteria = $request->get("advft_criteria");
        $advft_criteria_groups = $request->get("advft_criteria_groups");
        /*
        $json = new Zend_Json();
        $advft_criteria = vtlib_purify($_REQUEST['advft_criteria']);
        $advft_criteria = $json->decode($advft_criteria);
        $advft_criteria_groups = vtlib_purify($_REQUEST['advft_criteria_groups']);
        $advft_criteria_groups = $json->decode($advft_criteria_groups);
        */
        if (!is_array($sel_fields)) {
            $sel_fields = Zend_Json::decode($sel_fields);
        }
        if(isset($this->record) && $this->record!=""){
            $r4u_sel_fields_name = ITS4YouReports::getITS4YouReportStoreName("adv_sel_fields");
            $sel_fields = unserialize($_SESSION[$r4u_sel_fields_name]);
        }
//<<<<<<<advancedfilter>>>>>>>>
        echo '<script> var r_sel_fields = new Array(); </script>';
        // $to_echo = "";
        $r_sel_fields_echo = array();
        $l_group_i = $group_i = "";
        $minus_gi = 0;
        foreach ($advft_criteria as $f_fol_i => $condition_array) {
            if (!empty($condition_array)) {
                if (array_key_exists(trim($condition_array["columnname"]), $sel_fields)) {
                    $r_sel_fields = $condition_array["value"];
                    $r_sel_fields_echo[$condition_array["columnname"] . '_' . $condition_array["comparator"]] = $r_sel_fields;
                }
                $group_i = ($condition_array["groupid"] - $minus_gi);

                if (is_array($condition_array["value"])) {
                    $condition_array["value"] = implode(",", $condition_array["value"]);
                }

                $r_conditions[$group_i]["columns"][] = array("columnname" => $condition_array["columnname"],
                                                             "comparator" => $condition_array["comparator"],
                                                             "value" => html_entity_decode($condition_array["value"], ENT_QUOTES, $default_charset),
                                                             "value_hdn" => $condition_array["value_hdn"],
                                                             "column_condition" => $condition_array["column_condition"],
                );
                $r_conditions[$group_i]["condition"] = $advft_criteria_groups[$condition_array["groupid"]]["groupcondition"];
            } else {
                if ($l_group_i != "" && $l_group_i != $group_i) {
                    $minus_gi++;
                }
            }
            $l_group_i = $group_i;
        }
        $c_g_i = 1;
        if (!empty($r_conditions)) {
            foreach ($r_conditions as $c_g_key => $c_g_array) {
                $n_r_conditions[$c_g_i] = $c_g_array;
                $c_g_i++;
            }
            $r_conditions = $n_r_conditions;
        }

        $to_echo_str = [];
        if (!empty($r_sel_fields_echo)) {
            foreach ($r_sel_fields_echo as $ckey => $cvalues) {
                $to_echo_str[] = $ckey . "<;@#@;>" . $cvalues;
            }
            $to_echo_str = implode("<;@B#B@;>", $to_echo_str);
        }
        echo '<script> var r_sel_fields = "' . $to_echo_str . '"; </script>';

        return $r_conditions;
    }

    // ITS4YOU-END 3. 3. 2014 14:28:17
    public static function getLabelsHTML($columns_array, $type = "SC", $lbl_url_selected = array(), $decode = false) {
        $default_charset = vglobal("default_charset");
        $return = array();
        $calculation_type = "";
        global $currentModule;
        if (!empty($columns_array)) {
            foreach ($columns_array as $key => $TP_column_str) {
                $key = ($key);
                $TP_column_str = ($TP_column_str);
                $input_id = $key . "_" . $type . "_lLbLl_" . $TP_column_str;
                if ($type == "SM") {
                    $TP_column_str_arr = explode(":", $TP_column_str);
                    $calculation_type = $TP_column_str_arr[(count($TP_column_str_arr) - 1)];
                }
                $translated_lbl_key = ITS4YouReports::getColumnStr_Label($TP_column_str, $type, $lbl_url_selected);
                $translated_key = ITS4YouReports::getColumnStr_Label($TP_column_str, "key", $lbl_url_selected);
                $translated_key = str_replace("@AMPKO@", "&", $translated_key);
                if ($translated_lbl_key != "") {
                    $translated_lbl_key = html_entity_decode($translated_lbl_key, ENT_QUOTES, $default_charset);
                    $calculation_type = vtranslate($calculation_type, $currentModule);
                    $translated_key = $calculation_type . " " . $translated_key;
                    if ($decode) {
                        global $default_charset;
                        $decoded_translated_lbl_key = htmlspecialchars($translated_lbl_key, ENT_QUOTES, $default_charset);
                    } else {
                        $decoded_translated_lbl_key = $translated_lbl_key;
                    }

                    $translate_html = "<input type='text' class='inputElement' id='$input_id' size='50' value='" . $decoded_translated_lbl_key . "' onblur='checkEmptyLabel(\"$input_id\")'><input type='hidden' id='hidden_$input_id' value='" . $decoded_translated_lbl_key . "'>";
                    $return[] = array("translated_key"=>$translated_key,"translate_html"=>$translate_html);
                }
            }
        }
        return $return;
    }

    public static function getColumnStr_Label($column_str, $type = "SC", $lbl_url_selected = array()) {
        $translated_value = "";
        global $default_charset;
        $adb = PearDatabase::getInstance();
        global $currentModule;

        if ($column_str != "" && $column_str != "none") {
            $column_str = urldecode($column_str);
            global $current_language;
            $col_arr = explode(":", $column_str);
            $calculation_type = "";
            $lbl_arr = explode("_", $col_arr[2], 2);
            $lbl_module = $lbl_arr[0];
            $lbl_value = $lbl_arr[1];
            $lbl_value_sp = $lbl_value;
            if ($type == "SM" ) {
                // COUNT ... SUM AVG MIN MAX
                if (is_numeric($col_arr[5])) {
                    $calculation_type = $col_arr[6] . " ";
                } else {
                    $calculation_type = $col_arr[5] . " ";
                }
            }

            $lbl_mod_strings = array();

            if (!isset($lbl_url_selected[$type])) {
                $lbl_url_selected[$type] = array();
            }
            if ((array_key_exists($column_str, $lbl_url_selected[$type]) || array_key_exists(html_entity_decode($column_str, ENT_COMPAT, $default_charset), $lbl_url_selected[$type])) && $type != "key") {
                $translated_value = ($lbl_url_selected[$type][$column_str] != '') ? $lbl_url_selected[$type][$column_str] : $lbl_url_selected[$type][html_entity_decode($column_str, ENT_COMPAT, $default_charset)];
            } else {
                $labelsql = $reportid = "";
                $numlabels = 0;

                $reportid = vtlib_purify($_REQUEST["record"]);

                if ($type != "key" && $reportid!=""){
                    $labelsql = "SELECT columnlabel FROM its4you_reports4you_labels WHERE reportid = ? and type = ? AND columnname=?";
                }elseif(in_array($lbl_value,array("COUNT Records","LBL_RECORDS")) && isset($_REQUEST["record"])){
                    $reportid = vtlib_purify($_REQUEST["record"]);
                    $column_str = preg_replace("/COUNT Records/", "LBL_RECORDS", $column_str);
                    $labelsql = "SELECT columnlabel FROM its4you_reports4you_labels WHERE reportid = ? and type = ? AND columnname=?";
                }
                if($labelsql!="" && $reportid!=""){
                    $column_str = html_entity_decode($column_str,ENT_QUOTES,$default_charset);
                    $labelres = $adb->pquery($labelsql, array($reportid, $type, str_replace("@AMPKO@", "&", $column_str)));
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
                    // ITS4YOU-CR SlOl 4. 7. 2016 13:00:53 - MISSING COUNT COLUMN IN RESULT
                    if($lbl_value=="COUNT Records"){
                        $calculation_type = "";
                        $translated_lbl = vtranslate($lbl_value, $lbl_module);
                    }elseif($lbl_value=="LBL_RECORDS"){
                        // ITS4YOU-END
                        $translated_lbl = vtranslate($lbl_value, "ITS4YouReports");
                    }else{
                        $translated_lbl = vtranslate($lbl_value, $lbl_module);
                    }
                    $calculation_type = trim($calculation_type);
                    if($calculation_type!=""){
                        $calculation_type = vtranslate($calculation_type, $currentModule)." ";
                    }
                    $translated_value = $calculation_type.$translated_lbl;
                }
            }
        }
        return $translated_value;
    }

    // ITS4YOU-CR SlOl | 14.5.2014 10:21 
    public static function getTimeLineColumnHTML($group_i = "@NMColStr", $tl_col_str = "") {
        global $mod_strings;
        $TimeLineColumnD = $TimeLineColumnW = $TimeLineColumnM = $TimeLineColumnY = $TimeLineColumnQ = "";

        $tl_col_val = "";
        if ($tl_col_str != "") {
            $frequency_arr = explode("@vlv@", $tl_col_str);
            $tl_col_val = $frequency_arr[0];
            $frequency_option = $frequency_arr[1];
            switch ($frequency_option) {
                case "DAYS":
                    $TimeLineColumnD = ' selected="selected" ';
                    break;
                case "WEEK":
                    $TimeLineColumnW = ' selected="selected" ';
                    break;
                case "MONTH":
                    $TimeLineColumnM = ' selected="selected" ';
                    break;
                case "YEAR":
                    $TimeLineColumnY = ' selected="selected" ';
                    break;
                case "QUARTER":
                    $TimeLineColumnQ = ' selected="selected" ';
                    break;
                case "HALFYEAR":
                    $TimeLineColumnH = ' selected="selected" ';
                    break;
                default:
                    $TimeLineColumnD = ' selected="selected" ';
                    break;
            }
        } else {
            $TimeLineColumnD = ' selected="selected" ';
        }

        if ($group_i != "@NMColStr") {
            $group_i = "_Group$group_i";
        }
        /*
          $timelinecolumn = "<table width='20%' style='float:right;' border='0' >
          <tr>
          <td width='16%' style='border:0px;' nowrap >" . vtranslate('TL_DAYS',"ITS4YouReports"). "</td>
          <td width='16%' style='border:0px;' nowrap >" . vtranslate('TL_WEEKS',"ITS4YouReports") . "</td>
          <td width='17%' style='border:0px;' nowrap >" . vtranslate('TL_MONTHS',"ITS4YouReports") . "</td>
          <td width='17%' style='border:0px;' nowrap >" . vtranslate('TL_QUARTERS',"ITS4YouReports") . "</td>
          <td width='17%' style='border:0px;' nowrap >" . vtranslate('TL_HALF_YEARS',"ITS4YouReports") . "</td>
          <td width='17%' style='border:0px;' nowrap >" . vtranslate('TL_YEARS',"ITS4YouReports") . "</td>
          </tr>
          <tr>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnD' $TimeLineColumnD value='$tl_col_val@vlv@DAYS' ></td>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnW' $TimeLineColumnW value='$tl_col_val@vlv@WEEK' ></td>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnM' $TimeLineColumnM value='$tl_col_val@vlv@MONTH' ></td>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnQ' $TimeLineColumnQ value='$tl_col_val@vlv@QUARTER' ></td>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnH' $TimeLineColumnH value='$tl_col_val@vlv@HALFYEAR' ></td>
          <td align='center' style='border:0px;text-align:center;'><input type='radio' name='TimeLineColumn$group_i' id='TimeLineColumnY' $TimeLineColumnY value='$tl_col_val@vlv@YEAR' ></td>
          </tr>
          </table>";
         */
        $timelinecolumn = '<select id="TimeLineColumn' . $group_i . '" name="TimeLineColumn' . $group_i . '" class="select2 col-lg-4 inputElement" style="white-space: nowrap;">
                                        <option value="' . $tl_col_val . '@vlv@DAYS" ' . $TimeLineColumnD . ' >' . vtranslate('TL_DAYS', "ITS4YouReports") . '</option>
                                        <option value="' . $tl_col_val . '@vlv@WEEK" ' . $TimeLineColumnW . ' >' . vtranslate('TL_WEEKS', "ITS4YouReports") . '</option>
                                        <option value="' . $tl_col_val . '@vlv@MONTH" ' . $TimeLineColumnM . ' >' . vtranslate('TL_MONTHS', "ITS4YouReports") . '</option>
                                        <option value="' . $tl_col_val . '@vlv@QUARTER" ' . $TimeLineColumnQ . ' >' . vtranslate('TL_QUARTERS', "ITS4YouReports") . '</option>
                                        <option value="' . $tl_col_val . '@vlv@HALFYEAR" ' . $TimeLineColumnH . ' >' . vtranslate('TL_HALF_YEARS', "ITS4YouReports") . '</option>
                                        <option value="' . $tl_col_val . '@vlv@YEAR" ' . $TimeLineColumnY . ' >' . vtranslate('TL_YEARS', "ITS4YouReports") . '</option>
                                    </select>';

        return $timelinecolumn;
    }

    // ITS4YOU-END 14.5.2014 10:21 
    // ITS4YOU-CR SlOl | 23.6.2014 10:57 
    function getReportHeaderInfo($noofrows = 0,$skip_visibility_heck=false) {
        $final_return = $return = $return_name = $return_val = "";
        $return_arr = $col_order_arr = $summ_order_arr = array();
        $colspan = 2;

        global $default_charset;
        if (isset($this->reportinformations["reports4youid"]) && $this->reportinformations["reports4youid"] != "") {

            $return_val = "<b>" . vtranslate("LBL_Module", $this->currentModule) . ": </b>";
            $primarymodule_id = $this->reportinformations["primarymodule"];
            $primarymodule = vtlib_getModuleNameById($primarymodule_id);
            $return_val .= vtranslate($primarymodule, $primarymodule);
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_TOTAL", $this->currentModule) . ": </b><span id='_reportrun_total'>$noofrows</span> " . vtranslate("LBL_RECORDS", "ITS4YouReports");
            $return_arr[] = array("val" => $return_val, "colspan" => "");


            $return_val = "<b>" . vtranslate("LBL_TEMPLATE_OWNER", $this->currentModule) . ": </b>";
            $return_val .= getUserFullName($this->reportinformations["owner"]);
            $return_arr[] = array("val" => $return_val, "colspan" => "");
            $return_val = "<b>" . vtranslate("LBL_GroupBy", $this->currentModule) . ": </b>";
            for ($gi = 1; $gi < 4; $gi++) {
                if (isset($this->reportinformations["Group$gi"]) && $this->reportinformations["Group$gi"] != "" && $this->reportinformations["Group$gi"] != "none") {
                    $group_col_info = array();
                    // columns visibility control !!!!!! 
                    if ($this->getColumnVisibilityPerm($this->reportinformations["Group$gi"],$skip_visibility_heck) == 0) {
                        $gp_column_lbl = ITS4YouReports::getColumnStr_Label($this->reportinformations["Group$gi"], "SC");
                        $group_col_info[] = $gp_column_lbl;
                        $group_col_info[] = vtranslate($this->reportinformations["Sort$gi"]);
                        if (isset($this->reportinformations["timeline_columnstr$gi"]) && $this->reportinformations["timeline_columnstr$gi"] != "" && $this->reportinformations["timeline_columnstr$gi"] != "@vlv@") {
                            $tr_arr = explode("@vlv@", $this->reportinformations["timeline_columnstr$gi"]);
                            $tl_option = "TL_" . $tr_arr[1];
                            $group_col_info[] = vtranslate("LBL_BY", $this->currentModule);
                            $group_col_info[] = vtranslate($tl_option, $this->currentModule);
                        }
                        $group_cols[] = implode(" ", $group_col_info);
                    }
                }
            }
            if (empty($group_cols)) {
                $group_cols[] = vtranslate("LBL_NONE", $this->currentModule);
            }
            $return_val .= implode(", ", $group_cols);
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_Sharing", $this->currentModule) . ": </b>";
            if (isset($this->reportinformations["sharingtype"]) && $this->reportinformations["sharingtype"] != "") {
                $sharingtype = vtranslate($this->reportinformations["sharingtype"]);
            }
            $sharing_with = "";
            if ($this->reportinformations["sharingtype"] == "share") {
                $sharingMemberArray = $this->reportinformations["members_array"];
                $sharingMemberArray = array_unique($sharingMemberArray);
                if (count($sharingMemberArray) > 0) {
                    $outputMemberArr = array();
                    foreach ($sharingMemberArray as $setype => $shareIdArr) {
                        $shareIdArr = explode("::", $shareIdArr);
                        $shareIdArray[$shareIdArr[0]] = $shareIdArr[1];
                        foreach ($shareIdArray as $shareType => $shareId) {
                            switch ($shareType) {
                                case "groups":
                                    $groupName_array = getGroupName($shareId);
                                    $memberName = $groupName_array[0];
                                    $memberDisplay = "Group::";
                                    break;

                                case "roles":
                                    $memberName = getRoleName($shareId);
                                    $memberDisplay = "Roles::";
                                    break;

                                case "rs":
                                    $memberName = getRoleName($shareId);
                                    $memberDisplay = "RoleAndSubordinates::";
                                    break;

                                case "users":
                                    $memberName = getUserName($shareId);
                                    $memberDisplay = "User::";
                                    break;
                            }
                            // $outputMemberArr[] = $shareType."::".$shareId;
                            $outputMemberArr[] = $memberDisplay . $memberName;
                        }
                    }
                    $outputMemberArr = array_unique($outputMemberArr);
                }
                if (!empty($outputMemberArr)) {
                    $sharing_with = " (" . implode(", ", $outputMemberArr) . ")";
                }
            }

            $return_val .= $sharingtype . $sharing_with;
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_Schedule", $this->currentModule) . ": </b>";
            $adb = PearDatabase::getInstance();
            require_once 'modules/ITS4YouReports/ScheduledReports4You.php';
            $scheduledReport = new ITS4YouScheduledReport($adb, $this->current_user, $this->record);
            $scheduledReport->getReportScheduleInfo();
            $is_scheduled = $scheduledReport->isScheduled;
            $schedule_info = "";
            $scheduled_arr = array(1 => "HOURLY",
                                   2 => "DAILY",
                                   3 => "WEEKLY",
                                   4 => "BIWEEKLY",
                                   5 => "MONTHLY",
                                   6 => "ANNUALLY",);

            if ($is_scheduled) {
                $schtypeid = $scheduledReport->scheduledInterval["scheduletype"];
                // $schedule_info_arr[] = vtranslate("LBL_SCHEDULE_EMAIL", $this->currentModule);
                $schedulerFormatArr = explode(";", $scheduledReport->scheduledFormat);
                if (!empty($schedulerFormatArr)) {
                    foreach ($schedulerFormatArr as $format_str) {
                        $translated_schedulerFormat[] = vtranslate($format_str, $this->currentModule);
                    }
                }
                // $schedule_info_arr[] = $scheduledReport->scheduledFormat;
                $schedule_info_arr[] = implode(", ", $translated_schedulerFormat);
                $schedule_info_arr[] = vtranslate($scheduled_arr[$schtypeid], $this->currentModule);

                $schtime = $scheduledReport->scheduledInterval['time'];
                $schday = $scheduledReport->scheduledInterval['date'];
                $schweek = $scheduledReport->scheduledInterval['day'];
                $schmonth = $scheduledReport->scheduledInterval['month'];

                $WEEKDAY_STRINGS = vtranslate("WEEKDAY_STRINGS", $this->currentModule);
                $MONTH_STRINGS = vtranslate("MONTH_STRINGS", $this->currentModule);

                switch ($schtypeid) {
                    case 1:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                    case 2:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                    case 3:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_ON", $this->currentModule) . " " . $WEEKDAY_STRINGS[$schweek];
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                    case 4:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_ON", $this->currentModule) . " " . $WEEKDAY_STRINGS[$schweek];
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                    case 5:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_ON", $this->currentModule) . " $schday. " . vtranslate("LBL_SCHEDULE_EMAIL_DAY", $this->currentModule);
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                    case 6:
                        $schedule_info_arr[] = vtranslate("LBL_SCH_IN", $this->currentModule) . " " . $MONTH_STRINGS[$schmonth];
                        $schedule_info_arr[] = vtranslate("LBL_SCH_ON", $this->currentModule) . " $schday. " . vtranslate("LBL_SCHEDULE_EMAIL_DAY", $this->currentModule);
                        $schedule_info_arr[] = vtranslate("LBL_SCH_AT", $this->currentModule) . " $schtime";
                        break;
                }
                $schedule_info = implode(" ", $schedule_info_arr);
            } else {
                $schedule_info = vtranslate("LBL_NONE", $this->currentModule);
            }
            $return_val .= $schedule_info;
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_LIMIT", $this->currentModule) . ": </b>";
            if (!empty($this->reportinformations["summaries_columns"])) {
                $return_val .= vtranslate("LBL_Summaries", $this->currentModule) . " ";
                if (isset($this->reportinformations["summaries_limit"]) && $this->reportinformations["summaries_limit"] != "0") {
                    $summ_limit_str = $this->reportinformations["summaries_limit"] . " " . vtranslate("LBL_RECORDS", $this->currentModule);
                } else {
                    $summ_limit_str = vtranslate("LBL_ALL", $this->currentModule) . " " . strtolower(vtranslate("LBL_RECORDS", $this->currentModule));
                }
                $return_val .= $summ_limit_str;
                if ($this->reportinformations["selectedColumnsString"] != "") {
                    $return_val .= ", " . vtranslate("LBL_Details", $this->currentModule) . " ";
                }
            }
            if ($this->reportinformations["selectedColumnsString"] != "") {
                if (isset($this->reportinformations["columns_limit"]) && $this->reportinformations["columns_limit"] != "0") {
                    $limit_str = $this->reportinformations["columns_limit"] . " " . vtranslate("LBL_RECORDS", $this->currentModule);
                } else {
                    $limit_str = vtranslate("LBL_ALL", $this->currentModule) . " " . strtolower(vtranslate("LBL_RECORDS", $this->currentModule));
                }
                $return_val .= $limit_str;
            }
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_CHART_INFO", $this->currentModule) . ": </b>";

            $ch_column_populated = false;
            $n_ch_info = array();
            $new_ch_info = $ch_column_str = "";
            if (!empty($this->reportinformations["charts"])) {
                foreach ($this->reportinformations["charts"] as $chi => $ch_array) {
                    $ch_type = $ch_array["charttype"];

                    if ($ch_column_populated != true) {
                        if ($ch_array["x_group"] == "group1") {
                            $ch_column_lbl = GenerateObj::getHeaderLabel($this->record, "SC", "", $this->reportinformations["Group1"]);
                            if (is_array($ch_column_lbl) && $ch_column_lbl["lbl"] != "") {
                                $ch_column_lbl = $ch_column_lbl["lbl"];
                            }
                            $ch_column[] = $ch_column_lbl;
                        } elseif ($ch_array["x_group"] == "group2") {
                            $ch_column_lbl = GenerateObj::getHeaderLabel($this->record, "SC", "", $this->reportinformations["Group1"]);
                            if (is_array($ch_column_lbl) && $ch_column_lbl["lbl"] != "") {
                                $ch_column_lbl = $ch_column_lbl["lbl"];
                            }
                            $ch_column[] = $ch_column_lbl;
                            $ch_column_lbl = GenerateObj::getHeaderLabel($this->record, "SC", "", $this->reportinformations["Group1"]);
                            if (is_array($ch_column_lbl) && $ch_column_lbl["lbl"] != "") {
                                $ch_column_lbl = $ch_column_lbl["lbl"];
                            }
                            $ch_column[] = $ch_column_lbl;
                        }
                        $ch_column_str = implode(", ", $ch_column);
                        $ch_column_populated = true;
                    }
                    $ch_dataseries = GenerateObj::getHeaderLabel($this->record, "SM", "", $ch_array["dataseries"]);
                    if (is_array($ch_dataseries) && $ch_dataseries["lbl"] != "") {
                        $ch_dataseries = $ch_dataseries["lbl"];
                    }
                    $n_ch_info[] = vtranslate("LBL_CHART_$ch_type", $this->currentModule) . " " . vtranslate("LBL_CHART", $this->currentModule) . " ($ch_dataseries)";
                }
                $new_ch_info = vtranslate("LBL_CHART_DataSeries", $this->currentModule) . " " . $ch_column_str . " [" . implode(", ", $n_ch_info) . "]";
            } else {
                $new_ch_info = " <small><i>(" . vtranslate("LBL_NO_CHARTS", $this->currentModule) . ")</i></small>";
            }
            $return_val .= $new_ch_info;
            /*
              if(isset($this->reportinformations["charts"]) && $this->reportinformations["charts"]["charttype"]=="" && isset($this->reportinformations["charts"]["dataseries"]) && $this->reportinformations["charts"]["dataseries"]!=""){
              $ch_info = " <small><i>(".vtranslate("LBL_NO_CHARTS", $this->currentModule).")</i></small>";
              }elseif(isset($this->reportinformations["Group1"]) && $this->reportinformations["Group1"]!="none" && isset($this->reportinformations["charts"]) && $this->reportinformations["charts"]["charttype"]!="none" && isset($this->reportinformations["charts"]["dataseries"]) && $this->reportinformations["charts"]["dataseries"]!=""){
              // columns visibility control !!!!!!
              if($this->getColumnVisibilityPerm($this->reportinformations["Group1"])==0 && $this->getColumnVisibilityPerm($this->reportinformations["charts"]["dataseries"])==0){
              $ch_column = GenerateObj::getHeaderLabel($this->record,"SC","",$this->reportinformations["Group1"]);
              if(is_array($ch_column) && $ch_column["lbl"]!=""){
              $ch_column = $ch_column["lbl"];
              }
              $ch_dataseries = GenerateObj::getHeaderLabel($this->record,"SM","",$this->reportinformations["charts"]["dataseries"]);
              if(is_array($ch_dataseries) && $ch_dataseries["lbl"]!=""){
              $ch_dataseries = $ch_dataseries["lbl"];
              }
              $ch_type = $this->reportinformations["charts"]["charttype"];
              $ch_info = vtranslate("LBL_CHART_$ch_type", $this->currentModule)." ".vtranslate("LBL_CHART", $this->currentModule)." ($ch_column, $ch_dataseries)";
              }
              }else{
              $ch_info = " <small><i>(".vtranslate("LBL_CHARE", $this->currentModule)." ".vtranslate("LBL_IGNORED", $this->currentModule)."!)</i></small>";
              }
              $return_val .= $ch_info;
             */
            $return_arr[] = array("val" => $return_val, "colspan" => "");

            $return_val = "<b>" . vtranslate("LBL_Columns", $this->currentModule) . ": </b>";

            $col_order_str = "";
            if (isset($this->reportinformations["selectedColumnsString"]) && $this->reportinformations["selectedColumnsString"] != "") {
                $selected_column_string = $this->reportinformations["selectedColumnsString"];
                $selected_column_array = explode(";", html_entity_decode($selected_column_string, ENT_QUOTES, $default_charset));
                if (!empty($selected_column_array)) {
                    foreach ($selected_column_array as $column_str) {
                        if ($column_str != "" && $column_str != "none") {
                            // columns visibility control !!!!!! 
                            if ($this->getColumnVisibilityPerm($column_str,$skip_visibility_heck) == 0) {
                                $column_lbl = ITS4YouReports::getColumnStr_Label($column_str, "SC");
                                $columns[] = $column_lbl;
                            }
                        }
                    }
                }
                // ITS4YOU-CR SlOl 24. 5. 2016 6:29:19
                if(!empty($this->reportinformations["sortOrderArray"])){
                    $col_order_str = vtranslate("LBL_SORT_FIELD", $this->currentModule)." ";
                    foreach($this->reportinformations["sortOrderArray"] as $sort_col_arr){
                        $col_order_column = ITS4YouReports::getColumnStr_Label($sort_col_arr["SortByColumn"], "SC");
                        $col_order_row_str = $col_order_column." ";
                        if ($sort_col_arr["SortOrderColumn"] == "DESC"){
                            $col_order_row_str .= vtranslate("Descending");
                        } else {
                            $col_order_row_str .= vtranslate("Ascending");
                        }
                        $col_order_arr[] = $col_order_row_str;
                    }
                    $col_order_str .= implode(", ", $col_order_arr);
                }
                // ITS4YOU-END
            }
            if (empty($columns)) {
                $columns[] = vtranslate("LBL_NONE", $this->currentModule);
            }
            $return_val .= implode(", ", $columns);
            if ($this->reportinformations["timeline_type2"] == "cols" || $this->reportinformations["timeline_type3"] == "cols") {
                $return_val .= " <small><i>(" . vtranslate("LBL_NOT_A", $this->currentModule) . " " . vtranslate("LBL_IGNORED", $this->currentModule) . "!)</i></small>";
            }
            if ($col_order_str != "") {
                $return_val .= " ($col_order_str)";
            }
            $return_arr[] = array("val" => $return_val, "colspan" => "2");

            $return_val = "<b>" . vtranslate("LBL_SummariesColumns", $this->currentModule) . ": </b>";
            $summ_order_str = "";
            if (isset($this->reportinformations["summaries_columns"]) && !empty($this->reportinformations["summaries_columns"])) {
                foreach ($this->reportinformations["summaries_columns"] as $column_arr) {
                    $column_str = $column_arr["columnname"];
                    // columns visibility control !!!!!! 
                    if ($this->getColumnVisibilityPerm($column_str,$skip_visibility_heck) == 0) {
                        $sm_column_lbl = ITS4YouReports::getColumnStr_Label($column_str, "SM");
                        $summaries_columns[] = $sm_column_lbl;
                    }
                }
                if (isset($this->reportinformations["summaries_orderby_columns"]) && !empty($this->reportinformations["summaries_orderby_columns"])) {
                    if ($this->reportinformations["summaries_orderby_columns"][0]["column"] != "none") {
                        $summ_order_arr[] = vtranslate("LBL_SORT_FIELD", $this->currentModule);
                        $summ_order_column = ITS4YouReports::getColumnStr_Label($this->reportinformations["summaries_orderby_columns"][0]["column"], "SM");
                        $summ_order_arr[] = $summ_order_column;
                        if ($this->reportinformations["summaries_orderby_columns"][0]["type"] == "DESC") {
                            $summ_order_arr[] = vtranslate("Descending");
                        } else {
                            $summ_order_arr[] = vtranslate("Ascending");
                        }
                        $summ_order_str = implode(" ", $summ_order_arr);
                    }
                }
            }
            if (empty($summaries_columns)) {
                $summaries_columns[] = vtranslate("LBL_NONE", $this->currentModule);
            }
            $return_val .= implode(", ", $summaries_columns);
            if ($summ_order_str != "") {
                $return_val .= " ($summ_order_str)";
            }
            $return_arr[] = array("val" => $return_val, "colspan" => "2");

            $return_val = "<b>" . vtranslate("LBL_Filters", $this->currentModule) . ": </b>";

            $std_filter_columns = $this->getStdFilterColumns();

            if (isset($_REQUEST["reload"])) {
                $tmp = $this->getAdvanceFilterOptionsJSON($this->primarymodule);
                $criteria_columns = $this->getRequestCriteria($this->adv_sel_fields);
                if (!empty($criteria_columns)) {
                    if (isset($_REQUEST["reload"])) {
                        foreach($std_filter_columns as $std_k => $std_v){
                            $std_filter_columns[$std_k] = html_entity_decode($std_v, ENT_QUOTES, $default_charset);
                        }
                        foreach ($criteria_columns as $group_id => $group_arr) {
                            $criteria_columns = $group_arr["columns"];
                            if (!empty($criteria_columns)) {
                                foreach ($criteria_columns as $criteria_groups_arr) {
                                    if ($criteria_groups_arr["columnname"] != "") {
                                        // columns visibility control !!!!!! 
                                        if ($this->getColumnVisibilityPerm($criteria_groups_arr["columnname"],$skip_visibility_heck) == 0) {
                                            $column_condition = "";
                                            if ($criteria_groups_arr["column_condition"] != "") {
                                                $column_condition = $criteria_groups_arr["column_condition"];
                                            }
                                            if (in_array(html_entity_decode($criteria_groups_arr["columnname"], ENT_QUOTES, $default_charset), $std_filter_columns)) {
                                                $comparator = $criteria_groups_arr["comparator"];
                                                $comparator_val = $this->Date_Filter_Values[$comparator];
                                                $comparator_info = vtranslate($comparator_val, $this->currentModule);
                                                if ($comparator == "custom") {
                                                    $comparator_info_arr = explode("<;@STDV@;>", html_entity_decode(trim($criteria_groups_arr["value"]), ENT_QUOTES, $default_charset));
                                                    if ($comparator_info_arr[0] != "" && $comparator_info_arr[1] != "") {
                                                        $comparator_info .= vtranslate("BETWEEN", $this->currentModule) . " ";
                                                        $comparator_info .= $comparator_info_arr[0] . " ";
                                                        $comparator_info .= vtranslate("LBL_AND", $this->currentModule) . " ";
                                                        $comparator_info .= $comparator_info_arr[1];
                                                    } elseif ($comparator_info_arr[0] != "") {
                                                        $comparator_info .= vtranslate("LBL_IS", $this->currentModule) . " ";
                                                        $comparator_info .= $comparator_info_arr[0];
                                                    } elseif ($comparator_info_arr[1] != "") {
                                                        $comparator_info .= vtranslate("LBL_IS", $this->currentModule) . " ";
                                                        $comparator_info .= $comparator_info_arr[1];
                                                    }
                                                }
                                                $criteria_info_value = "";
                                            } else {
                                                $comparator = self::$adv_filter_options[$criteria_groups_arr["comparator"]];
                                                $comparator_info = vtranslate($comparator, $this->currentModule);
                                                $criteria_info_value = $criteria_groups_arr["value"];
                                            }
                                            $ft_column_lbl = ITS4YouReports::getColumnStr_Label($criteria_groups_arr["columnname"]);
                                            $condition_info = $ft_column_lbl . " " . $comparator_info . " " . $criteria_info_value . " " . $column_condition;
                                            $conditions_arr[$group_id][] = $condition_info;
                                        }
                                    }
                                }
                            }
                            $group_conditions[$group_id] = $group_arr["condition"];
                        }
                    }
                }
            } else {
                $criteria_columns = $this->reportinformations["advft_criteria"];
                $criteria_groups = $this->reportinformations["advft_criteria_groups"];
                if (!empty($criteria_groups) && !empty($criteria_columns)) {
                    foreach ($criteria_groups as $criteria_groups_arr) {
                        $group_id = $criteria_groups_arr["groupid"];
                        $group_condition = $criteria_groups_arr["group_condition"];
                        $group_conditions[$group_id] = $group_condition;
                    }
                    foreach ($criteria_columns as $criteria_groups_arr) {
                        if ($criteria_groups_arr["columnname"] != "") {
                            // filter columns and values visibility control !!!!!! start
                            if ($this->getColumnVisibilityPerm($criteria_groups_arr["columnname"],$skip_visibility_heck) == 0) {
                                if (array_key_exists($criteria_groups_arr["columnname"], $this->adv_sel_fields)) {
                                    $this->getColumnValuesVisibilityPerm($criteria_groups_arr["value"], $this->adv_sel_fields[$criteria_groups_arr["columnname"]],$skip_visibility_heck);
                                }
                            }
                            // filter columns and values visibility control !!!!!! end
                            $column_condition = "";
                            if ($criteria_groups_arr["column_condition"] != "") {
                                $column_condition = $criteria_groups_arr["column_condition"];
                            }
                            if (in_array($criteria_groups_arr["columnname"], $std_filter_columns)) {
                                $comparator = $criteria_groups_arr["comparator"];
                                $comparator_val = $this->Date_Filter_Values[$comparator];
                                $comparator_info = vtranslate($comparator_val, $this->currentModule);
                                if ($comparator == "custom") {
                                    $comparator_info_arr = explode("<;@STDV@;>", html_entity_decode(trim($criteria_groups_arr["value"]), ENT_QUOTES, $default_charset));
                                    if ($comparator_info_arr[0] != "" && $comparator_info_arr[1] != "") {
                                        $comparator_info .= vtranslate("BETWEEN", $this->currentModule) . " ";
                                        $comparator_info .= $comparator_info_arr[0] . " ";
                                        $comparator_info .= vtranslate("LBL_AND", $this->currentModule) . " ";
                                        $comparator_info .= $comparator_info_arr[1];
                                    } elseif ($comparator_info_arr[0] != "") {
                                        $comparator_info .= vtranslate("LBL_IS", $this->currentModule) . " ";
                                        $comparator_info .= $comparator_info_arr[0];
                                    } elseif ($comparator_info_arr[1] != "") {
                                        $comparator_info .= vtranslate("LBL_IS", $this->currentModule) . " ";
                                        $comparator_info .= $comparator_info_arr[1];
                                    }
                                }
                                $criteria_info_value = "";
                            } else {
                                $comparator = self::$adv_filter_options[$criteria_groups_arr["comparator"]];
                                $comparator_info = vtranslate($comparator, $this->currentModule);
                                $criteria_info_value = $criteria_groups_arr["value"];
                            }
                            $ft_column_lbl = ITS4YouReports::getColumnStr_Label($criteria_groups_arr["columnname"]);
                            $conditions_arr[$criteria_groups_arr["groupid"]][] = $ft_column_lbl . " " . $comparator_info . " " . $criteria_info_value . " " . $column_condition;
                        }
                    }
                }
            }
            $filters_str = "";
            if (!empty($group_conditions)) {
                foreach ($group_conditions as $g_condition_id => $g_condition) {
                    if (isset($conditions_arr[$g_condition_id]) && !empty($conditions_arr[$g_condition_id])) {
                        $filters_str .= " (" . trim(implode(" ", $conditions_arr[$g_condition_id])) . ") ";
                        if ($g_condition != "") {
                            $filters_str .= " " . vtranslate($g_condition, $this->currentModule) . " ";
                        }
                    }
                }
            }
            if ($filters_str == "") {
                $filters_str .= vtranslate("LBL_NONE", $this->currentModule);
            }
            $return_val .= $filters_str;
            $return_arr[] = array("val" => $return_val, "colspan" => "2");

            $td_i = 0;
            foreach ($return_arr as $ra_key => $ra_arr) {
                if (isset($ra_arr["colspan"]) && $ra_arr["colspan"] != "") {
                    $ra_colspan = $ra_arr["colspan"];
                } else {
                    $ra_colspan = 1;
                }
                $ra_val = $ra_arr["val"];
                if ($ra_key === "reportname") {
                    /*$return_name .= "<tr>";
                    $return_name .= "<td colspan='$ra_colspan' class='rpt4youGrpHeadInfoText' width='100%' style='border:0px;'>";
                    $return_name .= "$ra_val";
                    $return_name .= "</td>";
                    $return_name .= "</tr>";*/
                } else {
                    if ($td_i == 0) {
                        $return .= "<tr>";
                    }
                    $return .= "<td colspan='$ra_colspan' class='rpt4youGrpHeadInfo' style='text-align:left;padding-left:20px;width:50%;'>";
                    $return .= $ra_val;
                    $return .= "</td>";
                    $td_i += $ra_colspan;
                    if ($td_i == $colspan) {
                        $return .= "</tr>";
                        $td_i = 0;
                    }
                }
            }
            /*$final_return = "<table class='rpt4youTableText' width='100%'>";
            $final_return .= $return_name;
            $final_return .= "</table>";*/
            $final_return = "<table width='100%' ><tr><td align='center'>";
            $layout = Vtiger_Viewer::getDefaultLayoutName();
            if($layout == "v7"){
                $tableWidth = '100%';
            } else {
                $tableWidth = '98%';
            }
            $final_return .= "<table class='rpt4youTable' width='$tableWidth'>";
            $final_return .= $return;
            $final_return .= "</table>";
            $final_return .= "</td></tr></table>";
            // ITS4YOU-UP SlOl 4. 12. 2014 13:56:43
            // ADD PAGE BREAK AFTER HEADER INFO disabled - remove // to enable it please
            // $final_return .= "<div style='page-break-after:always'></div>"; 
        }
        return $final_return;
    }

    // ITS4YOU-END 23.6.2014 10:57 
    // ITS4YOU-CR SlOl | 22.7.2014 16:34 
    public function getStdFilterColumns() {
        if (isset($this->std_filter_columns) && !empty($this->std_filter_columns)) {
            $std_filter_columns = $this->std_filter_columns;
        } else {
            $request = new Vtiger_Request($_REQUEST);

            if (!$this->primarymoduleid && $request->has('primarymodule')) {
                $this->primarymoduleid = $request->get('primarymodule');
                $this->primarymodule = vtlib_getModuleNameById($this->primarymoduleid);
            }

            if ($this->primarymoduleid) {
                $std_filter_columns = array();
                $std_filter_array[] = getPrimaryStdFilter($this->primarymodule, $this);
                $rel_modules = $this->getReportRelatedModules($this->primarymoduleid);
                if (!empty($rel_modules)) {
                    foreach ($rel_modules as $r_m_key => $r_m_array) {
                        $s_std_arr = getSecondaryStdFilter($r_m_array, array(), $this);
                        if (!empty($s_std_arr)) {
                            $std_filter_array[] = $s_std_arr;
                        }
                    }
                }
                if (!empty($std_filter_array)) {
                    foreach ($std_filter_array as $just_key => $std_m_array) {
                        foreach ($std_m_array as $j_key => $std_m_v_array) {
                            $std_filter_columns[] = $std_m_v_array["value"];
                        }
                    }
                }
                $this->std_filter_columns = $std_filter_columns;
            }
        }
        return $std_filter_columns;
    }

    // ITS4YOU-END 22.7.2014 16:34
    // ITS4YOU-CR SlOl | 28.7.2014 15:31 
    private function getColumnVisibilityPerm($column_str = "",$skip_visibility_heck=false) {
        $return = 0;
        $adb = PearDatabase::getInstance();
        $die_columns = array();
        $profileGlobalPermission = '';
        $is_admin = false;

        if($skip_visibility_heck==true){
            return $return;
        }
        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }

        if (file_exists($user_privileges_path) && ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)) {
            return $return;
        } else {
            if ($column_str != "") {
                $current_user_id = $this->current_user->id;
                $column_array = explode(":", $column_str);
                $calculation_type_tmp = $column_array[(count($column_array) - 1)];
                $calculation_type = strtolower($calculation_type_tmp);
                if (!in_array($calculation_type, $this->calculation_type_array)) {
                    $calculation_type = "";
                }
                if ($calculation_type == "count") {
                    $return = 0;
                } else {
                    $column_name = $column_array[1];
                    if (!in_array($column_name,array("crmid","converted","r4u_hyperlink",)) && !in_array($column_name, self::$intentory_fields)) {
                        $module_array = explode("_", $column_array[2]);
                        $module_name = $module_array[0];
                        if ($module_name == "Calendar") {
                            $f_p_sql = "SELECT tabid FROM vtiger_field WHERE columnname=? AND tabid IN (9,16)";
                            $f_p_result = $adb->pquery($f_p_sql, array($column_name));
                            if ($adb->num_rows($f_p_result) > 0) {
                                $f_p_row = $adb->fetchByAssoc($f_p_result);
                                $f_p_tabid = $f_p_row["tabid"];
                                $module_name = vtlib_getModuleNameById($f_p_tabid);
                            }
                        }
                        $return = getColumnVisibilityPermission($current_user_id, $column_name, $module_name);
                        /* if($return==1){
                          $die_columns[] = $column_name;
                          } */
                    }
                }
            }
            if ($return == 1) {
                $this->DieDuePermission("columns", $die_columns);
            }
        }
        return $return;
    }

    // ITS4YOU-CR SlOl | 29.7.2014 9:41 \
    private function getColumnValuesVisibilityPerm($values_str = "", $available_values_arr = array(),$skip_visibility_heck=false) {
        $permitted_array = array();
        $return = 0;
        $profileGlobalPermission = '';

        if($skip_visibility_heck==true){
            return $return;
        }

        $user_privileges_path = 'user_privileges/user_privileges_' . $this->current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }

        if (file_exists($user_privileges_path) && (is_admin($this->current_user) == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)) {
            return $return;
        } else {
            // ITS4YOU-UP SlOl 27. 11. 2014 11:08:32 + is_array($available_values_arr) into condition
            if ($values_str != "" && !empty($available_values_arr) && is_array($available_values_arr)) {
                global $default_charset;
                $values_arr = explode(",", $values_str);
                foreach ($available_values_arr as $k => $val_arr) {
                    $permitted_array[] = $val_arr["value"];
                }
                foreach ($values_arr as $check_val) {
                    if (!in_array($check_val, $permitted_array)) {
                        $return = 1;
                    }
                }
            }
            if ($return == 1) {
                $this->DieDuePermission("values");
            }
        }
        return $return;
    }

    // ITS4YOU-END

    public static function RegisterReports4YouScheduler() {
        $adb = PearDatabase::getInstance();
//        $adb->setDebug(true);
        include_once 'vtlib/Vtiger/Cron.php';
        Vtiger_Cron::register('Schedule Reports4You', 'modules/ITS4YouReports/ScheduleReports4You.service', 900, 'ITS4YouReports', '', '', 'Recommended frequency for ScheduleReports4You is 15 mins');
        $adb->pquery("UPDATE `vtiger_cron_task` SET `status` = '1' WHERE `name` = 'Schedule Reports4You';", array());
//        $adb->setDebug(false);
        return true;
    }

    // ITS4YOU-CR SlOl | 8.8.2014 20:38 
    // function used in scheduling to get User info of recipient User
    public static function getReports4YouOwnerUser($user_id = "") {
        global $current_user;
        if ($user_id != "") {
            $user = new Users();
            $user->retrieveCurrentUserInfoFromFile($user_id);
        } else {
            $user = Users::getActiveAdminUser();
        }
        $current_user = $user;
        return $user;
    }

    public static function revertSchedulerUser() {
        global $current_user;
        $current_user = null;
        return $current_user;
    }

    // ITS4YOU-END 8.8.2014 20:38
    public function getSelectedValuesToSmarty($smarty_obj = "", $step_name = "") {
        if ($smarty_obj != "" && $step_name != "") {
            global $app_strings;
            global $mod_strings;
            global $default_charset;
            global $current_language;
            global $image_path;
            global $theme;
            $theme_path = "themes/" . $theme . "/";
            $image_path = $theme_path . "images/";
            $smarty_obj->assign("THEME", $theme_path);
            $smarty_obj->assign("IMAGE_PATH", $image_path);

            $adb = PEARDatabase::getInstance();
            $get_all_steps = "all";
            $restricted_modules = '';

            if (isset($_REQUEST["record"]) && $_REQUEST['record'] != '') {
                $recordid = vtlib_purify($_REQUEST["record"]);
            } else {
                $recordid = "";
            }
            $smarty_obj->assign("RECORDID", $recordid);

            $smarty_obj->assign("DISPLAY_FILTER_HEADER", false);

            if (in_array($step_name, array("ReportsStep1"))) {
                if (isset($_REQUEST["reportname"]) && $_REQUEST["reportname"] != "") {
                    $reportname = htmlspecialchars(vtlib_purify($_REQUEST["reportname"]));
                } else {
                    $reportname = $this->reportinformations["reports4youname"];
                }
                $smarty_obj->assign("REPORTNAME", $reportname);
                if (isset($_REQUEST["reportdesc"]) && $_REQUEST["reportdesc"] != "") {
                    $reportdesc = htmlspecialchars(vtlib_purify($_REQUEST["reportdesc"]));
                } else {
                    $reportdesc = $this->reportinformations["reportdesc"];
                }
                $smarty_obj->assign("REPORTDESC", $reportdesc);
                $smarty_obj->assign("REP_MODULE", $this->reportinformations["primarymodule"]);
                $smarty_obj->assign("PRIMARYMODULES", $this->getPrimaryModules());
                $smarty_obj->assign("REP_FOLDERS", $this->getReportFolders());
                if (isset($this->primarymodule) && $this->primarymodule != '') {
                    $rel_modules = $this->getReportRelatedModules($this->primarymoduleid);
                    foreach ($rel_modules as $key => $relmodule) {
                        $restricted_modules .= $relmodule['id'] . ":";
                    }
                    $smarty_obj->assign("REL_MODULES_STR", trim($restricted_modules, ":"));

                    $smarty_obj->assign("RELATEDMODULES", $rel_modules);
                }
                $smarty_obj->assign("FOLDERID", vtlib_purify($_REQUEST['folder']));
            }
            if (in_array($step_name, array("ReportsStep2", $get_all_steps))) {
                if (isset($this->primarymodule) && $this->primarymodule != '') {
                    $rel_modules = $this->getReportRelatedModules($this->primarymoduleid);
                    foreach ($rel_modules as $key => $relmodule) {
                        $restricted_modules .= $relmodule['id'] . ":";
                    }
                    $smarty_obj->assign("REL_MODULES_STR", trim($restricted_modules, ":"));
                    $smarty_obj->assign("RELATEDMODULES", $rel_modules);
                }
            }
            if (in_array($step_name, array("ReportGrouping", $get_all_steps))) {
                // TIMELINE COLUMNS DEFINITION CHANGED New Code 13.5.2014 11:58
                // ITS4YOU-CR SlOl | 13.5.2014 11:53
                if (isset($_REQUEST["primarymodule"]) && $_REQUEST["primarymodule"] != "") {
                    $primary_moduleid = $_REQUEST["primarymodule"];
                    $primary_module = vtlib_getModuleNameById($_REQUEST["primarymodule"]);
                    if (vtlib_isModuleActive($primary_module)) {
                        $primary_df_arr = getPrimaryTLStdFilter($primary_module, $this);
                    }
                } else {
                    $primary_module = $this->primarymodule;
                    $primary_moduleid = $this->primarymoduleid;
                    $primary_df_arr = getPrimaryTLStdFilter($primary_module, $this);
                }

                $date_options = array();
                if (!empty($primary_df_arr)) {
                    foreach ($primary_df_arr as $val_arr) {
                        foreach ($val_arr as $val_dtls) {
                            $date_options[] = $val_dtls["value"];
                        }
                    }
                }
                $date_options_json = Zend_JSON::encode($date_options);
                $smarty_obj->assign("date_options_json", $date_options_json);

                $timelinecolumn = $this->getTimeLineColumnHTML();
                $smarty_obj->assign("timelinecolumn", $timelinecolumn);
                // ITS4YOU-END 13.5.2014 11:53

                if (isset($_REQUEST["record"]) && $_REQUEST['record'] != '') {
                    $reportid = vtlib_purify($_REQUEST["record"]);

                    $secondarymodule = '';
                    $secondarymodules = Array();

                    if (!empty($this->related_modules[$primary_module])) {
                        foreach ($this->related_modules[$primary_module] as $key => $value) {
                            if (isset($_REQUEST["secondarymodule_" . $value]))
                                $secondarymodules [] = vtlib_purify($_REQUEST["secondarymodule_" . $value]);
                        }
                    }
                    if ($primary_moduleid == getTabid('Invoice')) {
                        $secondarymodules[] = getTabid('Products');
                        $secondarymodules[] = getTabid('Services');
                    }
                    $secondarymodule = implode(":", $secondarymodules);
                    if ($secondarymodule != '') {
                        $this->secondarymodules .= $secondarymodule;
                    }
                    if (isset($_REQUEST["summaries_limit"])) {
                        $summaries_limit = vtlib_purify($_REQUEST["summaries_limit"]);
                    } else {
                        $summaries_limit = $this->reportinformations["summaries_limit"];
                    }
                } else {
                    $secondarymodule = '';
                    $secondarymodules = Array();

                    $this->getPriModuleColumnsList($primary_module);
                    foreach ($this->secondarymodules as $key => $secmodid) {
                        $this->getSecModuleColumnsList(vtlib_getModuleNameById($secmodid));
                    }
                    $summaries_limit = "20";
                }
                $smarty_obj->assign("SUMMARIES_MAX_LIMIT", $summaries_limit);
                for ($tc_i = 1; $tc_i < 4; $tc_i++) {
                    $timelinecol = $selected_timeline_column = "";
                    if (isset($_REQUEST["group$tc_i"]) && $_REQUEST["group$tc_i"] != "" && $step_name != "ReportGrouping") {
                        $group = vtlib_purify($_REQUEST["group$tc_i"]);
                        if (isset($_REQUEST["timeline_column$tc_i"]) && $_REQUEST["timeline_column$tc_i"] != "") {
                            $selected_timeline_column = vtlib_purify($_REQUEST["timeline_column$tc_i"]);
                        }
                    } else {
                        $group = $this->reportinformations["Group$tc_i"];
                        $selected_timeline_column = $this->reportinformations["timeline_columnstr$tc_i"];
                    }
                    if (isset($selected_timeline_column) && !in_array($selected_timeline_column, array("", "none", "@vlv@"))) {
                        $timelinecol = $this->getTimeLineColumnHTML($tc_i, $selected_timeline_column);
                        $smarty_obj->assign("timelinecolumn" . $tc_i . "_html", $timelinecol);
                    }

                    $RG_BLOCK = getPrimaryColumns_GroupingHTML($primary_module, $group, $this);
                    $smarty_obj->assign("RG_BLOCK$tc_i", $RG_BLOCK);

                    if ($tc_i > 1) {
                        if (isset($_REQUEST["timeline_type$tc_i"]) && $_REQUEST["timeline_type$tc_i"] != "") {
                            $timeline_type = vtlib_purify($_REQUEST["timeline_type$tc_i"]);
                        } else {
                            $timeline_type = $this->reportinformations["timeline_type$tc_i"];
                        }
                        $smarty_obj->assign("timeline_type$tc_i", $timeline_type);
                    }
                }

                for ($sci = 1; $sci < 4; $sci++) {
                    if (isset($_REQUEST["sort" . $sci]) && $_REQUEST["sort" . $sci] != "") {
                        $sortorder = vtlib_purify($_REQUEST["sort" . $sci]);
                    } else {
                        $sortorder = $this->reportinformations["Sort" . $sci];
                    }
                    $sa = $sd = "";

                    if ($sortorder != "Descending") {
                        $sa = "checked";
                    } else {
                        $sd = "checked";
                    }

                    $shtml = '<input type="radio" id="Sort' . $sci . 'a" name="Sort' . $sci . '" value="Ascending" ' . $sa . '>' . vtranslate('Ascending') . ' &nbsp; 
				              <input type="radio" id="Sort' . $sci . 'd" name="Sort' . $sci . '" value="Descending" ' . $sd . '>' . vtranslate('Descending');
                    $smarty_obj->assign("ASCDESC" . $sci, $shtml);
                }

                // ITS4YOU-CR SlOl 5. 3. 2014 14:50:45 SUMMARIES START
                $module_id = $primary_moduleid;
                $modulename_prefix = "";
                $module_array["module"] = $primary_module;
                $module_array["id"] = $module_id;
                $selectedmodule = $module_array["id"];

                $modulename = $module_array["module"];
                $modulename_lbl = vtranslate($modulename, $modulename);

                $availModules[$module_array["id"]] = $modulename_lbl;
                $modulename_id = $module_array["id"];
                $selectedSummariesString = '';
                if (isset($selectedmodule)) {
                    $secondarymodule_arr = $this->getReportRelatedModules($module_array["id"]);
                    $this->getSecModuleColumnsList($selectedmodule);
                    $RG_BLOCK4 = sgetSummariesHTMLOptions($module_array["id"], $module_id);
                    $available_modules[] = array("id" => $module_id, "name" => $modulename_lbl, "checked" => "checked");
                    foreach ($secondarymodule_arr as $key => $value) {
                        $exploded_mid = explode("x", $value["id"]);
                        if (strtolower($exploded_mid[1]) != "mif") {
                            $available_modules[] = array("id" => $value["id"], "name" => "- " . $value["name"], "checked" => "");
                        }
                    }
                    $smarty_obj->assign("RG_BLOCK4", $RG_BLOCK4);
                }
                $smarty_obj->assign("SummariesModules", $available_modules);
                $SumOptions = sgetSummariesOptions($selectedmodule);
                if (empty($SumOptions)) {
                    $SumOptions = vtranslate("NO_SUMMARIES_COLUMNS", $this->currentModule);
                }

                $SPSumOptions[$module_array["id"]][$module_array["id"]] = $SumOptions;
                $smarty_obj->assign("SUMOPTIONS", $SPSumOptions);

                if (isset($_REQUEST["selectedSummariesString"])) {
                    $selectedSummariesString = vtlib_purify($_REQUEST["selectedSummariesString"]);
                    $selectedSummariesArr = explode(";", $selectedSummariesString);
                    $summaries_orderby = vtlib_purify($_REQUEST["summaries_orderby"]);
                    $RG_BLOCK6 = sgetSelectedSummariesHTMLOptions($selectedSummariesArr, $summaries_orderby);
                } else {
                    if (!empty($this->reportinformations["summaries_columns"])) {
                        foreach ($this->reportinformations["summaries_columns"] as $key => $summaries_columns_arr) {
                            $selectedSummariesArr[] = $summaries_columns_arr["columnname"];
                        }
                    }
                    $selectedSummariesString = implode(";", $selectedSummariesString);
                    $summaries_orderby = "";
                    if (isset($this->reportinformations["summaries_orderby_columns"][0]) && $this->reportinformations["summaries_orderby_columns"][0] != "") {
                        $summaries_orderby = $this->reportinformations["summaries_orderby_columns"][0];
                    }
                    $RG_BLOCK6 = sgetSelectedSummariesHTMLOptions($selectedSummariesArr, $summaries_orderby);
                }

                // sum_group_columns for group filters start
                $sm_arr = sgetSelectedSummariesOptions($selectedSummariesArr);
                $sm_str = "";
                foreach ($sm_arr as $key => $opt_arr) {
                    if ($sm_str != "") {
                        $sm_str .= "(|@!@|)";
                    }
                    $sm_str .= $opt_arr["value"] . "(|@|)" . $opt_arr["text"];
                }
                $smarty_obj->assign("sum_group_columns", $sm_str);
                // sum_group_columns for group filters end
                $smarty_obj->assign("selectedSummariesString", $selectedSummariesString);
                $smarty_obj->assign("RG_BLOCK6", $RG_BLOCK6);

                $RG_BLOCKx2 = array();
                $all_fields_str = "";
                foreach ($SPSumOptions AS $module_key => $SumOptions) {
                    $RG_BLOCKx2 = "";
                    $r_modulename = vtlib_getModuleNameById($module_key);
                    $r_modulename_lbl = vtranslate($r_modulename, $r_modulename);

                    foreach ($SumOptions as $SumOptions_key => $SumOptions_value) {
                        if (is_array($SumOptions_value)) {
                            foreach ($SumOptions_value AS $optgroup => $optionsdata) {
                                if ($RG_BLOCKx2 != "")
                                    $RG_BLOCKx2 .= "(|@!@|)";
                                $RG_BLOCKx2 .= $optgroup;
                                $RG_BLOCKx2 .= "(|@|)";

                                $RG_BLOCKx2 .= Zend_JSON::encode($optionsdata);
                            }
                        }else {
                            $RG_BLOCKx2 .= $SumOptions_value;
                            $RG_BLOCKx2 .= "(|@|)";
                            $optionsdata[] = array("value" => "none", "text" => vtranslate("LBL_NONE", $this->currentModule));
                            $RG_BLOCKx2 .= Zend_JSON::encode($optionsdata);
                        }
                        $all_fields_str .= $module_key . "(!#_ID@ID_#!)" . $r_modulename_lbl . "(!#_ID@ID_#!)" . $RG_BLOCKx2;
                    }
                }
                $smarty_obj->assign("ALL_FIELDS_STRING", $all_fields_str);
                // ITS4YOU-END 5. 3. 2014 14:50:47  SUMMARIES END

                if (isset($_REQUEST["summaries_orderby"]) && $_REQUEST["summaries_orderby"] != "" && isset($_REQUEST["summaries_orderby_type"]) && $_REQUEST["summaries_orderby_type"] != "") {
                    $summaries_orderby = vtlib_purify($_REQUEST["summaries_orderby"]);
                    $summaries_orderby_type = vtlib_purify($_REQUEST["summaries_orderby_type"]);
                } elseif (isset($this->reportinformations["summaries_orderby_columns"]) && !empty($this->reportinformations["summaries_orderby_columns"])) {
                    $summaries_orderby = $this->reportinformations["summaries_orderby_columns"][0]["column"];
                    $summaries_orderby_type = $this->reportinformations["summaries_orderby_columns"][0]["type"];
                } else {
                    $summaries_orderby = "none";
                    $summaries_orderby_type = "ASC";
                }
                $smarty_obj->assign("summaries_orderby", $summaries_orderby);
                $smarty_obj->assign("summaries_orderby_type", $summaries_orderby_type);
            }
            if (in_array($step_name, array("ReportColumns", $get_all_steps))) {
                if (isset($_REQUEST["record"]) && $_REQUEST['record'] != '') {
                    $RC_BLOCK1 = getPrimaryColumnsHTML($this->primarymodule);

                    $secondarymodule = '';
                    $secondarymodules = Array();

                    if (!empty($this->related_modules[$this->primarymodule])) {
                        foreach ($this->related_modules[$this->primarymodule] as $key => $value) {
                            if (isset($_REQUEST["secondarymodule_" . $value]))
                                $secondarymodules [] = $_REQUEST["secondarymodule_" . $value];
                        }
                    }
                    $secondarymodule = implode(":", $secondarymodules);

                    $RC_BLOCK2 = $this->getSelectedColumnsList($this->selected_columns_list_arr);
                    $smarty_obj->assign("RC_BLOCK1", $RC_BLOCK1);
                    $smarty_obj->assign("RC_BLOCK2", $RC_BLOCK2);

                    $sreportsortsql = "SELECT columnname, sortorder FROM  its4you_reports4you_sortcol WHERE reportid =? AND sortcolid = 4";
                    $result_sort = $adb->pquery($sreportsortsql, array($recordid));
                    $num_rows = $adb->num_rows($result_sort);

                    if ($num_rows > 0) {
                        $columnname = $adb->query_result($result_sort, 0, "columnname");
                        $sortorder = $adb->query_result($result_sort, 0, "sortorder");
                        $RC_BLOCK3 = $this->getSelectedColumnsList($this->selected_columns_list_arr, $columnname);
                    } else {
                        $RC_BLOCK3 = $RC_BLOCK2;
                    }
                    $smarty_obj->assign("RC_BLOCK3", $RC_BLOCK3);

                    $this->secmodule = $secondarymodule;

                    $RC_BLOCK4 = "";
                    $RC_BLOCK4 = getSecondaryColumnsHTML($this->relatedmodulesstring, $this);

                    $smarty_obj->assign("RC_BLOCK4", $RC_BLOCK4);
                } else {
                    $primarymodule = vtlib_purify($_REQUEST["primarymodule"]);
                    $RC_BLOCK1 = getPrimaryColumnsHTML($primarymodule);
                    if (!empty($this->related_modules[$primarymodule])) {
                        foreach ($this->related_modules[$primarymodule] as $key => $value) {
                            $RC_BLOCK1 .= getSecondaryColumnsHTML($_REQUEST["secondarymodule_" . $value], $this);
                        }
                    }
                    $smarty_obj->assign("RC_BLOCK1", $RC_BLOCK1);

                    $this->reportinformations["columns_limit"] = "20";
                }
                $smarty_obj->assign("MAX_LIMIT", $this->reportinformations["columns_limit"]);

                if ($sortorder != "DESC") {
                    $shtml = '<input type="radio" name="SortOrderColumn" value="ASC" checked>' . vtranslate('Ascending') . ' &nbsp; 
								<input type="radio" name="SortOrderColumn" value="DESC">' . vtranslate('Descending');
                } else {
                    $shtml = '<input type="radio" name="SortOrderColumn" value="ASC">' . vtranslate('Ascending') . ' &nbsp; 
								<input type="radio" name="SortOrderColumn" value="DESC" checked>' . vtranslate('Descending');
                }
                $smarty_obj->assign("COLUMNASCDESC", $shtml);

                $timelinecolumns = '';
                $timelinecolumns .= '<input type="radio" name="TimeLineColumn" value="DAYS" checked>' . $mod_strings['TL_DAYS'] . ' ';
                $timelinecolumns .= '<input type="radio" name="TimeLineColumn" value="WEEK" >' . $mod_strings['TL_WEEKS'] . ' ';
                $timelinecolumns .= '<input type="radio" name="TimeLineColumn" value="MONTH" >' . $mod_strings['TL_MONTHS'] . ' ';
                $timelinecolumns .= '<input type="radio" name="TimeLineColumn" value="YEAR" >' . $mod_strings['TL_YEARS'] . ' ';
                $timelinecolumns .= '<input type="radio" name="TimeLineColumn" value="QUARTER" >' . $mod_strings['TL_QUARTERS'] . ' ';
                $smarty_obj->assign("TIMELINE_FIELDS", $timelinecolumns);

                // ITS4YOU-CR SlOl  19. 2. 2014 16:30:20
                $SPSumOptions = $availModules = array();
                $RC_BLOCK0 = "";

                $smarty_obj->assign("availModules", $availModules);
                $smarty_obj->assign("ALL_FIELDS_STRING", $RC_BLOCK0);
                // ITS4YOU-END 19. 2. 2014 16:30:23
                $smarty_obj->assign("currentModule", $this->currentModule);
            }
            if (in_array($step_name, array("ReportColumnsTotal", $get_all_steps))) {
                $Objects = array();

                $curl_array = array();
                if (isset($_REQUEST["curl"])) {
                    $curl = vtlib_purify($_REQUEST["curl"]);
                    $curl_array = explode('$_@_$', $curl);
                    $selectedColumnsString = str_replace("@AMPKO@", "&", $_REQUEST["selectedColumnsStr"]);
                    $R_Objects = explode("<_@!@_>", $selectedColumnsString);
                } else {
                    $curl_array = $this->getSelectedColumnsToTotal($this->record);
                    $curl = implode('$_@_$', $curl_array);
                    $selectedColumnsString = str_replace("@AMPKO@", "&", $this->reportinformations["selectedColumnsString"]);
                    $R_Objects = explode(";", $selectedColumnsString);
                }
                $smarty_obj->assign("CURL", $curl);

                $Objects = sgetNewColumnstoTotalHTMLScript($R_Objects);
                $this->columnssummary = $Objects;
                $CT_BLOCK1 = $this->sgetNewColumntoTotalSelected($recordid, $R_Objects, $curl_array);
                $smarty_obj->assign("CT_BLOCK1", $CT_BLOCK1);

                //added to avoid displaying "No data avaiable to total" when using related modules in report.
                $rows_count = 0;
                $rows_count = count($CT_BLOCK1);
                $smarty_obj->assign("ROWS_COUNT", $rows_count);
            }
            if (in_array($step_name, array("ReportLabels", $get_all_steps))) {
                // selected labels from url
                $lbl_url_string = html_entity_decode(vtlib_purify($_REQUEST["lblurl"]), ENT_QUOTES, $default_charset);
                if ($lbl_url_string != "") {
                    $lbl_url_arr = explode('$_@_$', $lbl_url_string);
                    foreach ($lbl_url_arr as $key => $lbl_value) {
                        if (strpos($lbl_value, 'hidden_') === false) {
                            if (strpos($lbl_value, '_SC_lLbLl_') !== false) {
                                $temp = explode('_SC_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = trim($temp_lbls[0]);
                                $lbl_value = trim($temp_lbls[1]);
                                $lbl_url_selected["SC"][$lbl_key] = $lbl_value;
                            }
                            if (strpos($lbl_value, '_SM_lLbLl_') !== false) {
                                $temp = explode('_SM_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = trim($temp_lbls[0]);
                                $lbl_value = trim($temp_lbls[1]);
                                $lbl_url_selected["SM"][$lbl_key] = $lbl_value;
                            }

                            if (strpos($lbl_value, '_CT_lLbLl_') !== false) {
                                $temp = explode('_CT_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = trim($temp_lbls[0]);
                                $lbl_value = trim($temp_lbls[1]);
                                $lbl_url_selected["CT"][$lbl_key] = $lbl_value;
                            }
                        }
                    }
                }
                // COLUMNS labeltype SC
                if (isset($_REQUEST["selectedColumnsStr"]) && $_REQUEST["selectedColumnsStr"] != "") {
                    $selectedColumnsString = vtlib_purify($_REQUEST["selectedColumnsStr"]);
                    $selectedColumnsString = html_entity_decode($selectedColumnsString, ENT_QUOTES, $default_charset);
                    $selected_columns_array = explode("<_@!@_>", $selectedColumnsString);
                    $decode_labels = true;
                } else {
                    $selectedColumnsString = html_entity_decode($this->reportinformations["selectedColumnsString"], ENT_QUOTES, $default_charset);
                    $selected_columns_array = explode(";", $selectedColumnsString);
                    $decode_labels = false;
                }
                $labels_html["SC"] = $this->getLabelsHTML($selected_columns_array, "SC", $lbl_url_selected, $decode_labels);
                // SUMMARIES labeltype SM
                $selectedSummariesString = vtlib_purify($_REQUEST["selectedSummariesString"]);
                if ($selectedSummariesString != "") {
                    $selectedSummaries_array = explode(";", trim($selectedSummariesString, ";"));
                } else {
                    foreach ($this->reportinformations["summaries_columns"] as $key => $sum_arr) {
                        $selectedSummaries_array[] = $sum_arr["columnname"];
                    }
                }
                $labels_html["SM"] = $this->getLabelsHTML($selectedSummaries_array, "SM", $lbl_url_selected, $decode_labels);
                $smarty_obj->assign("labels_html", $labels_html);

                $smarty_obj->assign("LABELS", $curl);

                //added to avoid displaying "No data avaiable to total" when using related modules in report.
                $rows_count = count($labels_html);
                foreach ($labels_html as $key => $labels_type_arr) {
                    $rows_count += count($labels_type_arr);
                }
                $smarty_obj->assign("ROWS_COUNT", $rows_count);
            }
            if (in_array($step_name, array("ReportFilters", $get_all_steps))) {
                require_once('modules/ITS4YouReports/FilterUtils.php');

                if (isset($_REQUEST["primarymodule"]) && $_REQUEST["primarymodule"] != "") {
                    $primary_moduleid = $_REQUEST["primarymodule"];
                    $primary_module = vtlib_getModuleNameById($_REQUEST["primarymodule"]);
                } else {
                    $primary_module = $this->primarymodule;
                    $primary_moduleid = $this->primarymoduleid;
                }

                // NEW ADVANCE FILTERS START
                $this->getGroupFilterList($this->record);
                $this->getAdvancedFilterList($this->record);
                $this->getSummariesFilterList($this->record);

                $sel_fields = Zend_Json::encode($this->adv_sel_fields);
                $smarty_obj->assign("SEL_FIELDS", $sel_fields);
                if (isset($_REQUEST["reload"])) {
                    $criteria_groups = $this->getRequestCriteria($sel_fields);
                } else {
                    $criteria_groups = $this->advft_criteria;
                }
                $smarty_obj->assign("CRITERIA_GROUPS", $criteria_groups);
                $smarty_obj->assign("EMPTY_CRITERIA_GROUPS", empty($criteria_groups));
                $smarty_obj->assign("SUMMARIES_CRITERIA", $this->summaries_criteria);
                $FILTER_OPTION = getAdvCriteriaHTML();
                $smarty_obj->assign("FOPTION", $FILTER_OPTION);

                $COLUMNS_BLOCK_JSON = $this->getAdvanceFilterOptionsJSON($primary_module);
                $COLUMNS_BLOCK = '';
                $smarty_obj->assign("COLUMNS_BLOCK", $COLUMNS_BLOCK);
                if ($_REQUEST['mode'] != "ajax") {
                    echo "<textarea style='display:none;' id='filter_columns'>" . $COLUMNS_BLOCK_JSON . "</textarea>";
                    $smarty_obj->assign("filter_columns", $COLUMNS_BLOCK_JSON);
                    $sel_fields = Zend_Json::encode($this->adv_sel_fields);
                    $smarty_obj->assign("SEL_FIELDS", $sel_fields);
                    global $default_charset;
                    $std_filter_columns = $this->getStdFilterColumns();
                    $std_filter_columns_js = implode("<%jsstdjs%>", $std_filter_columns);
                    $std_filter_columns_js = html_entity_decode($std_filter_columns_js, ENT_QUOTES, $default_charset);
                    $smarty_obj->assign("std_filter_columns", $std_filter_columns_js);
                    $std_filter_criteria = Zend_Json::encode($this->Date_Filter_Values);
                    $smarty_obj->assign("std_filter_criteria", $std_filter_criteria);
                }
                $rel_fields = $this->adv_rel_fields;
                $smarty_obj->assign("REL_FIELDS", Zend_Json::encode($rel_fields));
                // NEW ADVANCE FILTERS END

                $BLOCKJS = $this->getCriteriaJS();
                $smarty_obj->assign("BLOCKJS_STD", $BLOCKJS);
            }
            if (in_array($step_name, array("ReportSharing", $get_all_steps))) {
                if ('__PHP_Incomplete_Class' === get_class($this->current_user->column_fields)) {
                    $this->current_user = $this->fixCurrentUser($this->current_user->id);
                }
                $roleid = $this->current_user->column_fields['roleid'];
                $user_array = getRoleAndSubordinateUsers($roleid);
                $userIdStr = "";
                $userNameStr = "";
                $m = 0;
                foreach ($user_array as $userid => $username) {
                    if ($userid != $this->current_user->id) {
                        if ($m != 0) {
                            $userIdStr .= ",";
                            $userNameStr .= ",";
                        }
                        $userIdStr .="'" . $userid . "'";
                        $userNameStr .="'" . escape_single_quotes(decode_html($username)) . "'";
                        $m++;
                    }
                }

                require_once('include/utils/GetUserGroups.php');

                // ITS4YOU-UP SlOl 26. 4. 2013 9:47:59
                $template_owners = get_user_array(false);
                if (isset($this->reportinformations["owner"]) && $this->reportinformations["owner"] != "") {
                    $selected_owner = $this->reportinformations["owner"];
                } else {
                    $selected_owner = $this->current_user->id;
                }
                $smarty_obj->assign("TEMPLATE_OWNERS", $template_owners);
                $owner = (isset($_REQUEST['template_owner']) && $_REQUEST['template_owner'] != '') ? $_REQUEST['template_owner'] : $selected_owner;
                $smarty_obj->assign("TEMPLATE_OWNER", $owner);

                $smarty_obj->assign('SHARINGTYPES', self::getSharingTypes());

                $sharingtype = self::getDefaultSharingType();
                if (isset($_REQUEST['sharing']) && $_REQUEST['sharing'] != '') {
                    $sharingtype = $_REQUEST['sharing'];
                } elseif (isset($this->reportinformations["sharingtype"]) && $this->reportinformations["sharingtype"] != "") {
                    $sharingtype = $this->reportinformations["sharingtype"];
                }

                $smarty_obj->assign("SHARINGTYPE", $sharingtype);

                $cmod = return_specified_module_language($current_language, "Settings");
                $smarty_obj->assign("CMOD", $cmod);

                $sharingMemberArray = array();
                if (isset($_REQUEST['sharingSelectedColumns']) && $_REQUEST['sharingSelectedColumns'] != '') {
                    $sharingMemberArray = explode("|", trim($_REQUEST['sharingSelectedColumns'], "|"));
                } elseif (isset($this->reportinformations["members_array"]) && !empty($this->reportinformations["members_array"])) {
                    $sharingMemberArray = $this->reportinformations["members_array"];
                }

                $sharingMemberArray = array_unique($sharingMemberArray);
                if (count($sharingMemberArray) > 0) {
                    $outputMemberArr = array();
                    foreach ($sharingMemberArray as $setype => $shareIdArr) {
                        $shareIdArr = explode("::", $shareIdArr);
                        $shareIdArray = array();
                        $shareIdArray[$shareIdArr[0]] = $shareIdArr[1];
                        foreach ($shareIdArray as $shareType => $shareId) {
                            switch ($shareType) {
                                case "groups":
                                    if ('v7' === $layout) {
                                        $memberName = getGroupName($shareId);
                                    } else {
                                        $memberName = getGroupName($shareId);
                                        $memberName = $memberName[0];
                                    }
                                    $memberDisplay = "Group::";
                                    break;
                                case "roles":
                                    $memberName = getRoleName($shareId);
                                    $memberDisplay = "Roles::";
                                    break;
                                case "rs":
                                    $memberName = getRoleName($shareId);
                                    $memberDisplay = "RoleAndSubordinates::";
                                    break;
                                case "users":
                                    $memberName = getUserFullName($shareId);
                                    $memberDisplay = "User::";
                                    break;
                            }
                            $outputMemberArr[] = $shareType . "::" . $shareId;
                            $outputMemberArr[] = $memberDisplay . $memberName;
                        }
                    }
                    $smarty_obj->assign("MEMBER", array_chunk($outputMemberArr, 2));
                }
                // ITS4YOU-END
                if ('v7' !== $layout) {
                    //Constructing the Role Array
                    $roleDetails=getAllRoleDetails();
                    $i=0;
                    $roleIdStr="";
                    $roleNameStr="";
                    $userIdStr="";
                    $userNameStr="";
                    $grpIdStr="";
                    $grpNameStr="";

                    foreach($roleDetails as $roleId=>$roleInfo) {
                        if($i !=0) {
                            if($i !=1) {
                                $roleIdStr .= ", ";
                                $roleNameStr .= ", ";
                            }
                            $roleName=$roleInfo[0];
                            $roleIdStr .= "'".$roleId."'";
                            $roleNameStr .= "'".addslashes(decode_html($roleName))."'";
                        }
                        $i++;
                    }

                    //Constructing the User Array
                    $l=0;
                    $userDetails=getAllUserName();
                    foreach($userDetails as $userId=>$userInfo) {
                        if($l !=0){
                            $userIdStr .= ", ";
                            $userNameStr .= ", ";
                        }
                        $userIdStr .= "'".$userId."'";
                        $userNameStr .= "'".decode_html($userInfo)."'";
                        $l++;
                    }
                    //Constructing the Group Array
                    $parentGroupArray = array();

                    $m=0;
                    $grpDetails=getAllGroupName();
                    foreach($grpDetails as $grpId=>$grpName) {
                        if(! in_array($grpId,$parentGroupArray)) {
                            if($m !=0) {
                                $grpIdStr .= ", ";
                                $grpNameStr .= ", ";
                            }
                            $grpIdStr .= "'".$grpId."'";
                            $grpNameStr .= "'".addslashes(decode_html($grpName))."'";
                            $m++;
                        }
                    }
                    $smarty_obj->assign("ROLEIDSTR",$roleIdStr);
                    $smarty_obj->assign("ROLENAMESTR",$roleNameStr);
                    $smarty_obj->assign("USERIDSTR",$userIdStr);
                    $smarty_obj->assign("USERNAMESTR",$userNameStr);
                    $smarty_obj->assign("GROUPIDSTR",$grpIdStr);
                    $smarty_obj->assign("GROUPNAMESTR",$grpNameStr);
                    // ITS4YOU-END 
                }

                if ('v7' === $layout) {
                    $userGroups = new GetUserGroups();
                    $userGroups->getAllUserGroups($this->current_user->id);
                    $user_groups = $userGroups->user_groups;
                    $groupIdStr = "";
                    $groupNameStr = "";
                    $l = 0;
                    foreach ($user_groups as $i => $grpid) {
                        $grp_details = getGroupDetails($grpid);
                        if ($l != 0) {
                            $groupIdStr .= ",";
                            $groupNameStr .= ",";
                        }
                        $groupIdStr .= "'" . $grp_details[0] . "'";
                        $groupNameStr .= "'" . escape_single_quotes(decode_html($grp_details[1])) . "'";
                        $l++;
                    }

                    $smarty_obj->assign("GROUPNAMESTR", $groupNameStr);
                    $smarty_obj->assign("USERNAMESTR", $userNameStr);
                    $smarty_obj->assign("GROUPIDSTR", $groupIdStr);
                    $smarty_obj->assign("USERIDSTR", $userIdStr);
                }

                $visiblecriteria = getVisibleCriteria();
                $smarty_obj->assign("VISIBLECRITERIA", $visiblecriteria);
            }
            if (in_array($step_name, array("ReportScheduler", $get_all_steps))) {
                // SEE ReportScheduler.php for this step for a reason of problem with incomplemete ReportScheduler object
            }
            if (in_array($step_name, array("ReportGraphs", $get_all_steps))) {
                if (isset($_REQUEST["chart_type"]) && $_REQUEST["chart_type"] != "" && $_REQUEST["chart_type"] != "none") {
                    $selected_chart_type = vtlib_purify($_REQUEST["chart_type"]);
                } else {
                    $selected_chart_type = $this->reportinformations["charts"]["charttype"];
                }
                $smarty_obj->assign("IMAGE_PATH", $selected_chart_type);
                if (isset($_REQUEST["data_series"]) && $_REQUEST["data_series"] != "" && $_REQUEST["data_series"] != "none") {
                    $selected_data_series = vtlib_purify($_REQUEST["data_series"]);
                } else {
                    $selected_data_series = $this->reportinformations["charts"]["dataseries"];
                }
                if (isset($_REQUEST["charttitle"]) && $_REQUEST["charttitle"] != "") {
                    $selected_charttitle = htmlspecialchars(vtlib_purify($_REQUEST["charttitle"]));
                } else {
                    $selected_charttitle = $this->reportinformations["charts"]["charttitle"];
                }
                $chart_type["horizontal"] = array("value" => vtranslate("LBL_CHART_horizontal", $this->currentModule), "selected" => ($selected_chart_type == "horizontal" ? "selected" : ""));
                $chart_type["horizontalstacked"] = array("value" => vtranslate("LBL_CHART_horizontalstacked", $this->currentModule), "selected" => ($selected_chart_type == "horizontalstacked" ? "selected" : ""));
                $chart_type["vertical"] = array("value" => vtranslate("LBL_CHART_vertical", $this->currentModule), "selected" => ($selected_chart_type == "vertical" ? "selected" : ""));
                $chart_type["verticalstacked"] = array("value" => vtranslate("LBL_CHART_verticalstacked", $this->currentModule), "selected" => ($selected_chart_type == "verticalstacked" ? "selected" : ""));
                $chart_type["linechart"] = array("value" => vtranslate("LBL_CHART_linechart", $this->currentModule), "selected" => ($selected_chart_type == "linechart" ? "selected" : ""));
                $chart_type["pie"] = array("value" => vtranslate("LBL_CHART_pie", $this->currentModule), "selected" => ($selected_chart_type == "pie" ? "selected" : ""));
                $chart_type["pie3d"] = array("value" => vtranslate("LBL_CHART_pie3D", $this->currentModule), "selected" => ($selected_chart_type == "pie3d" ? "selected" : ""));
                $chart_type["funnel"] = array("value" => vtranslate("LBL_CHART_funnel", $this->currentModule), "selected" => ($selected_chart_type == "funnel" ? "selected" : ""));
                $smarty_obj->assign("CHART_TYPE", $chart_type);

                // selected labels from url
                if (isset($_REQUEST["lblurl"])) {
                    global $default_charset;
                    $lbl_url_string = html_entity_decode(vtlib_purify($_REQUEST["lblurl"]), ENT_QUOTES, $default_charset);
                }

                $lbl_url_string = str_replace("@AMPKO@", "&", $lbl_url_string);
                if ($lbl_url_string != "") {
                    $lbl_url_arr = explode('$_@_$', $lbl_url_string);
                    foreach ($lbl_url_arr as $key => $lbl_value) {
                        if (strpos($lbl_value, 'hidden_') === false) {
                            if (strpos($lbl_value, '_SC_lLbLl_') !== false) {
                                $temp = explode('_SC_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = $temp_lbls[0];
                                $lbl_value = $temp_lbls[1];
                                $lbl_url_selected["SC"][$lbl_key] = $lbl_value;
                            }
                            if (strpos($lbl_value, '_SM_lLbLl_') !== false) {
                                $temp = explode('_SM_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = $temp_lbls[0];
                                $lbl_value = $temp_lbls[1];
                                $lbl_url_selected["SM"][$lbl_key] = $lbl_value;
                            }

                            if (strpos($lbl_value, '_CT_lLbLl_') !== false) {
                                $temp = explode('_CT_lLbLl_', $lbl_value);
                                $temp_lbls = explode('_lLGbGLl_', $temp[1]);
                                $lbl_key = $temp_lbls[0];
                                $lbl_value = $temp_lbls[1];
                                $lbl_url_selected["CT"][$lbl_key] = $lbl_value;
                            }
                        }
                    }
                }
                $selectedSummariesString = vtlib_purify($_REQUEST["selectedSummariesString"]);
                if ($selectedSummariesString != "") {
                    $selectedSummariesArray = explode(";", $selectedSummariesString);
                    if (!empty($selectedSummariesArray)) {
                        foreach ($selectedSummariesArray as $column_str) {
                            if ($column_str != "") {
                                if (isset($lbl_url_selected["SM"][$column_str]) && $lbl_url_selected["SM"][$column_str] != "") {
                                    $column_lbl = $lbl_url_selected["SM"][$column_str];
                                } else {
                                    $column_str_arr = explode(":", $column_str);
                                    $translate_arr = explode("_", $column_str_arr[2]);
                                    $translate_module = $translate_arr[0];
                                    unset($translate_arr[0]);
                                    $translate_str = implode("_", $translate_arr);
                                    $translate_mod_str = return_module_language($current_language, $translate_module);
                                    if (isset($translate_mod_str[$translate_str])) {
                                        $column_lbl = $translate_mod_str[$translate_str];
                                    } else {
                                        $column_lbl = $translate_str;
                                    }
                                }
                                $data_series[$column_str] = array("value" => $column_lbl, "selected" => ($column_str == $selected_data_series ? "selected" : ""));
                            }
                        }
                    }
                }
                if (empty($data_series) && $selected_data_series != "") {
                    $column_lbl = ITS4YouReports::getColumnStr_Label($selected_data_series, "SM");
                    $data_series[$selected_data_series] = array("value" => $column_lbl, "selected" => "selected");
                }
                $smarty_obj->assign("DATA_SERIES", $data_series);
                $smarty_obj->assign("CHART_TITLE", $selected_charttitle);
            }
            return $smarty_obj;
        }
    }

    // ITS4YOU-CR SlOl 24. 9. 2014 9:47:08
    public static function cleanITS4YouReportsCacheFiles() {
        $return = true;
        /*global $current_user;
        if (is_admin($current_user)) {*/
        foreach (glob("test/ITS4YouReports/*.png") as $filename) {
            unlink($filename);
        }
        foreach (glob("test/ITS4YouReports/*.pdf") as $filename) {
            unlink($filename);
        }
        foreach (glob("test/ITS4YouReports/*.xls") as $filename) {
            unlink($filename);
        }
        /*    $return = "<pre>" . vtranslate("LBL_DONE", $this->currentModule) . vtranslate("LBL_DOTS", $this->currentModule) . "</pre>";
        } else {
            $return = "<pre>" . vtranslate("LBL_ONLY_ADMIN", $this->currentModule) . vtranslate("LBL_DOTS", $this->currentModule) . "</pre>";
        }*/
        return $return;
    }

    // ITS4YOU-END 24. 9. 2014 9:47:10
    // ITS4YOU-CR SlOl 24. 9. 2014 10:23:42
    public static function GetReports4YouForImport() {
        global $current_user;
        $reports_to_import = array();
        $is_admin = false;
        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }
        if ($is_admin === true) {
            $imported_reports = $to_merge = array();
            foreach (glob("modules/ITS4YouReports/reports/imported/*.php") as $imported_file) {
                $imported_reports[] = str_replace("reports/imported/", "reports/", $imported_file);
            }
            foreach (glob("modules/ITS4YouReports/reports/*.php") as $report_file) {
                if (!in_array($report_file, $imported_reports)) {
                    $name_arr_temp = explode("/", $report_file);
                    $name_arr = explode("_", $name_arr_temp[((count($name_arr_temp) - 1))]);
                    $name_arr_id_temp = explode(".", $name_arr[1]);
                    $name_arr_id = $name_arr_id_temp[0];
                    if (is_numeric($name_arr_id)) {
                        $reports_to_import[$name_arr_id] = $report_file;
                    } else {
                        $to_merge[] = $report_file;
                    }
                }
            }
            ksort($reports_to_import);
            $reports_to_import = array_merge($reports_to_import, $to_merge);
        }
        return $reports_to_import;
    }

    public static function ImportReports4You($file_to_import = "", $debug = false) {
//echo "<pre>";print_r($file_to_import);echo "</pre>";

        global $currentModule;

        $adb = PEARDatabase::getInstance();

//$debug = true;
        if ($debug) {
            $adb->setDebug(true);
        }
        try {
            if ($file_to_import != "" && file_exists($file_to_import) && substr($file_to_import, -4) == ".php") {
                $result = $adb->pquery("SELECT @reports4youid:=(IF(reports4youid IS NOT NULL,max(reports4youid)+1,1)) AS reports4youid FROM its4you_reports4you;", array());

                $row = $adb->fetchByAssoc($result);
                $report_id = $row["reports4youid"];
                $ReportSql = array();
                $report_array_json = '';

                global $default_charset;

                require_once $file_to_import;
                $report_array_json = str_replace("@reportid", $report_id, $report_array_json);
                $ReportSql = Zend_Json::decode($report_array_json);

                $its4you_reports4you_modules_array = vtlib_purify($ReportSql["its4you_reports4you_modules"]);
                $reportModule = $its4you_reports4you_modules_array[1];
                if(vtlib_isModuleActive($reportModule)) {
                    if (isset($ReportSql["its4you_reports4you_settings"]) && !empty($ReportSql["its4you_reports4you_settings"])) {
                        $its4you_reports4you_settings = vtlib_purify($ReportSql["its4you_reports4you_settings"]);
                        $adb->pquery("INSERT INTO `its4you_reports4you_settings` (`reportid`, `owner`, `sharingtype`) VALUES (?,?,?)", array($its4you_reports4you_settings));
                    }
                    if (isset($ReportSql["its4you_reports4you_selectcolumn"]) && !empty($ReportSql["its4you_reports4you_selectcolumn"])) {
                        $its4you_reports4you_selectcolumn = vtlib_purify($ReportSql["its4you_reports4you_selectcolumn"]);
                        $sql = "INSERT INTO its4you_reports4you_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_selectcolumn as $c_key => $selected_column) {
                            $Qarray[] = '(?,?,?)';
                            $Values = array_merge($Values, $selected_column);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_summaries"]) && !empty($ReportSql["its4you_reports4you_summaries"])) {
                        $its4you_reports4you_summaries = vtlib_purify($ReportSql["its4you_reports4you_summaries"]);
                        $sql = "INSERT INTO its4you_reports4you_summaries (reportsummaryid, summarytype, columnname) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_summaries as $s_key => $summaries_array) {
                            $Qarray[] = '(?,?,?)';
                            $Values = array_merge($Values, $summaries_array);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_summaries_orderby"]) && !empty($ReportSql["its4you_reports4you_summaries_orderby"])) {
                        $its4you_reports4you_summaries_orderby = vtlib_purify($ReportSql["its4you_reports4you_summaries_orderby"]);
                        $adb->pquery("INSERT INTO its4you_reports4you_summaries_orderby (reportid,columnindex,summaries_orderby,summaries_orderby_type) VALUES (?,?,?,?)", array($its4you_reports4you_summaries_orderby));
                    }
                    if (isset($ReportSql["its4you_reports4you_labels"]) && !empty($ReportSql["its4you_reports4you_labels"])) {
                        $its4you_reports4you_labels = vtlib_purify($ReportSql["its4you_reports4you_labels"]);
                        $sql = "INSERT INTO its4you_reports4you_labels (reportid,type,columnname,columnlabel) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_labels as $l_key => $labels_array) {
                            $Qarray[] = '(?,?,?,?)';
                            $Values = array_merge($Values, $labels_array);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you"]) && !empty($ReportSql["its4you_reports4you"])) {
                        $its4you_reports4you = vtlib_purify($ReportSql["its4you_reports4you"]);

                        $adb->pquery("INSERT INTO its4you_reports4you (reports4youid,reports4youname,description,folderid,reporttype,deleted,columns_limit,summaries_limit) VALUES (?,?,?,?,?,?,?,?)", array($its4you_reports4you));
                    }
                    if (isset($ReportSql["its4you_reports4you_summary"]) && !empty($ReportSql["its4you_reports4you_summary"])) {
                        $its4you_reports4you_summary = vtlib_purify($ReportSql["its4you_reports4you_summary"]);

                        $sql = "INSERT INTO its4you_reports4you_summary (REPORTSUMMARYID,SUMMARYTYPE,COLUMNNAME) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_summary as $sm_key => $summary_array) {
                            $Qarray[] = '(?,?,?)';
                            $Values = array_merge($Values, $summary_array);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_modules"]) && !empty($ReportSql["its4you_reports4you_modules"])) {
                        $its4you_reports4you_modules_array[1] = getTabid($reportModule);
                        $adb->pquery("INSERT INTO its4you_reports4you_modules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) VALUES (?,?,?)", $its4you_reports4you_modules_array);
                    }
                    if (isset($ReportSql["its4you_reports4you_sortcol"]) && !empty($ReportSql["its4you_reports4you_sortcol"])) {
                        $its4you_reports4you_sortcol = vtlib_purify($ReportSql["its4you_reports4you_sortcol"]);
                        $sql = "INSERT INTO its4you_reports4you_sortcol (sortcolid,reportid,columnname,sortorder,timeline_type,timeline_columnstr,timeline_columnfreq) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_sortcol as $sortcol_i => $its4you_reports4you_sortcol_arr) {
                            $Qarray[] = '(?,?,?,?,?,?,?)';
                            if ($sortcol_i == "3") {
                                $its4you_reports4you_sortcol_arr[4] = "rows";
                                $its4you_reports4you_sortcol_arr[5] = "";
                                $its4you_reports4you_sortcol_arr[6] = "";
                            }
                            $Values = array_merge($Values, $its4you_reports4you_sortcol_arr);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_relcriteria"]) && !empty($ReportSql["its4you_reports4you_relcriteria"])) {
                        $its4you_reports4you_relcriteria = vtlib_purify($ReportSql["its4you_reports4you_relcriteria"]);
                        $sql = "INSERT INTO its4you_reports4you_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_relcriteria as $its4you_reports4you_relcriteria_arr) {
                            $Qarray[] = '(?,?,?,?,?,?,?)';
                            foreach ($its4you_reports4you_relcriteria_arr as $rc_k => $rc_v) {
                                $its4you_reports4you_relcriteria_arr[$rc_k] = html_entity_decode($rc_v, ENT_QUOTES, $default_charset);
                            }
                            $Values = array_merge($Values, $its4you_reports4you_relcriteria_arr);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_relcriteria_grouping"]) && !empty($ReportSql["its4you_reports4you_relcriteria_grouping"])) {
                        $its4you_reports4you_relcriteria_grouping = vtlib_purify($ReportSql["its4you_reports4you_relcriteria_grouping"]);
                        $sql = "INSERT INTO its4you_reports4you_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) VALUES ";
                        $Qarray = Array();
                        $Values = Array();
                        foreach ($its4you_reports4you_relcriteria_grouping as $its4you_reports4you_relcriteria_grouping_arr) {
                            $Qarray[] = '(?,?,?,?)';
                            $Values = array_merge($Values, $its4you_reports4you_relcriteria_grouping_arr);
                        }
                        $adb->pquery($sql . implode(', ', $Qarray), $Values);
                    }
                    if (isset($ReportSql["its4you_reports4you_datefilter"]) && !empty($ReportSql["its4you_reports4you_datefilter"])) {
                        $its4you_reports4you_datefilter = vtlib_purify($ReportSql["its4you_reports4you_datefilter"]);
                        $adb->pquery("INSERT INTO its4you_reports4you_datefilter (DATEFILTERID,DATECOLUMNNAME,DATEFILTER,STARTDATE,ENDDATE) VALUES (?,?,?,?,?)", array($its4you_reports4you_datefilter));
                    }
                    if (isset($ReportSql["its4you_reports4you_charts"]) && !empty($ReportSql["its4you_reports4you_charts"])) {
                        foreach ($ReportSql["its4you_reports4you_charts"] as $its4you_reports4you_charts) {
                            $adb->pquery("INSERT INTO its4you_reports4you_charts (reports4youid,charttype,dataseries,charttitle,chart_seq,x_group) VALUES (?,?,?,?,?,?)", array($its4you_reports4you_charts));
                        }
                    }
                    $adb->pquery("INSERT INTO its4you_reports4you_selectquery (`queryid`, `startindex`, `numofobjects`) VALUES (?, '0', '0')", array($report_id));
                    $adb->pquery("UPDATE its4you_reports4you_selectquery_seq SET id = ?", array($report_id));

                    if (isset($ReportSql["its4you_reports4you_selectqfcolumn"]) && !empty($ReportSql["its4you_reports4you_selectqfcolumn"])) {
                        foreach ($ReportSql["its4you_reports4you_selectqfcolumn"] as $qfInsertParams) {
                            $adb->pquery("INSERT INTO its4you_reports4you_selectqfcolumn (queryid,columnindex,columnname) VALUES (?,?,?)", $qfInsertParams);
                        }
                    }
                }

                $new_imported_file = str_replace("reports/", "reports/imported/", $file_to_import);
                if (!copy($file_to_import, $new_imported_file)) {
                    $return = vtranslate("LBL_COPPY_FAILED", $currentModule) . " $file_to_import" . vtranslate("LBL_DOTS", $currentModule) . "<br />";
                } else {
                    $return = vtranslate("LBL_IMPORT_SUCCESS", $currentModule) . " $new_imported_file" . vtranslate("LBL_DOTS", $currentModule) . "<br />";
                }
                //$adb->setDebug(false);
            } else {
                $return = vtranslate("LBL_FILE_NOT_SUPPORTED", $currentModule) . vtranslate("LBL_DOTS", $currentModule) . "<br />";
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        if ($debug) {
            $adb->setDebug(false);
        }
        return $return;
    }

    // ITS4YOU-END 24. 9. 2014 10:23:44  

    /** Function to get the vtiger_role and subordinate user ids
     * taken from vtiger 540
     * @param $roleid -- RoleId :: Type varchar
     * @returns $roleSubUserIds-- Role and Subordinates Related Users Array in the following format:
     *       $roleSubUserIds=Array($userId1,$userId2,........,$userIdn);
     */
    public static function getRoleAndSubordinateUserIds($roleId) {
        global $adb;
        $roleInfoArr = getRoleInformation($roleId);
        $parentRole = $roleInfoArr[$roleId][1];
        $query = "select vtiger_user2role.*,vtiger_users.user_name from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?";
        $result = $adb->pquery($query, array($parentRole . "%"));
        $num_rows = $adb->num_rows($result);
        $roleRelatedUsers = Array();
        for ($i = 0; $i < $num_rows; $i++) {
            $roleRelatedUsers[] = $adb->query_result($result, $i, 'userid');
        }
        return $roleRelatedUsers;
    }

    // WIDGET LINKS FIX !!! s
    private function fix_widget_labels() {
        $adb = PearDatabase::getInstance();
        $w_sql = "SELECT linkid,linklabel,linkurl FROM vtiger_links WHERE linklabel='WIDGETLABEL' AND tabid='3'";
        $w_result = $adb->pquery($w_sql, array());
        if ($adb->num_rows($w_result) > 0) {
            while ($w_row = $adb->fetchByAssoc($w_result)) {
                $link_array = explode("record=", $w_row["linkurl"]);
                if (isset($link_array[1]) && $link_array[1] != "") {
                    $w_reportid = $link_array[1];
                    $w_name_res = $adb->pquery("SELECT reports4youname FROM  its4you_reports4you WHERE reports4youid = ?", array($w_reportid));
                    if ($adb->num_rows($w_name_res) > 0) {
                        $w_name_row = $adb->fetchByAssoc($w_name_res, 0);
                        $w_name = $w_name_row["reports4youname"];
                        //$adb->setDebug(true);
                        $adb->pquery("UPDATE vtiger_links SET linklabel=? WHERE linkid=?", array($w_name, $w_row["linkid"]));
                        //$adb->setDebug(false);
                    }
                }
            }
        }
    }
    // WIDGET LINKS FIX !!! e
    // ITS4YOU-CR SlOl | 20.8.2015 12:25 
    public static function querySpecialControl($sql_query, $offset=0) {
        $black_list = array("delete ", "insert ", "update ", "drop ", "create ");
        foreach($black_list as $black_value) {
            if (stripos($sql_query, $black_value) !== false) {
                return true;
            }
        }
        return false;
    }
    // ITS4YOU-CR SlOl | 20.8.2015 12:25 
    public static function validateCustomSql($sql_query,$type="check"){
        $adb = PearDatabase::getInstance();
        if($sql_query!=""){
            global $default_charset;
            $sql_query = html_entity_decode($sql_query, ENT_QUOTES, $default_charset);

            $sql_count_test = explode(";",trim($sql_query, ';'));
            if(ITS4YouReports::querySpecialControl($sql_query)==true){
                ITS4YouReports::DieDuePermission("LBL_WRONG_QUERY_STRINGS_SQL");
                exit;
            }elseif(count($sql_count_test)>1){
                ITS4YouReports::DieDuePermission("LBL_TOO_MANY_SQL");
                exit;
            }

            if($type=='run'){
                $sql_query = str_replace("\n", " 
                ", $sql_query);
            }
        }

        return $sql_query;
    }
    // ITS4YOU-END

    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    // OPRAVA CHYBNEHO PREPISU FIELDLABEL V COLUMNNAME Z VERZIE 5.X !!!
    public static function repairColumnStrings($reportid=""){

        // SET UP ALTER TABLE
        //ITS4YouReports::repairReportsTables();

        // SET UP MODULES REPORT TYPES
        //ITS4YouReports::repairReportsTypes($reportid);

        // SELECTED COLUMNS REPAIR
        //ITS4YouReports::repairReportsSelectedColumns($reportid);

        // FILTER CONDITIONS REPAIR
        //ITS4YouReports::repairReportsRelCriteria($reportid);

        // FILTER SUMMARIES CONDITIONS REPAIR
        //ITS4YouReports::repairReportsRelCriteriaSummaries($reportid);

        // SELECTED SORT COL REPAIR
        //ITS4YouReports::repairReportsSelectedSortCol($reportid);

        // SELECTED SUMMARIES COLUMNS REPAIR
        //ITS4YouReports::repairReportsSelectedSummaries($reportid);

        // SELECTED SUMMARIES ORDER BY REPAIR
        //ITS4YouReports::repairReportsSummariesOrderBy($reportid);

        // SELECTED LABELS REPAIR
        //ITS4YouReports::repairReportsLabels($reportid);

    }
    // ITS4YOU-CR SlOl 18. 9. 2015 9:24:03
    public static function repairReportsTables(){
        $adb = PearDatabase::getInstance();

        $queryDebug = false;
        $queryDebug = true;

        $queryDebug==true?$adb->setDebug(true):"";

        //$adb->query("ALTER TABLE its4you_reports4you_charts ADD chart_seq INT( 11 ) NOT NULL");
        //$adb->query("ALTER TABLE its4you_reports4you_charts ADD x_group varchar( 255 ) NOT NULL");

        $adb->query("ALTER TABLE its4you_reports4you_folder ADD description varchar( 250 ) DEFAULT ''");
        $adb->query("ALTER TABLE its4you_reports4you_folder ADD state varchar( 50 ) DEFAULT 'SAVED'");
        $adb->query("ALTER TABLE its4you_reports4you_folder ADD ownerid int( 11 ) NOT NULL");

        $queryDebug==true?$adb->setDebug(false):"";

        return true;
    }
    // ITS4YOU-CR SlOl 18. 9. 2015 9:24:03
    public static function repairReportsTypes($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $where = "";
        if($reportid!=""){
            $where = " WHERE its4you_reports4you.reports4youid = ? ";
            $params[] = $reportid;
        }

        $ssql = "SELECT 
                reports4youid, 
                reporttype,
                its4you_reports4you_sortcol.sortcolid, 
                its4you_reports4you_sortcol.timeline_type, 
                its4you_reports4you_sortcol.columnname columnname_sc , 
                its4you_reports4you_selectcolumn.columnname columnname_dt 
                
                FROM its4you_reports4you 
                
                LEFT JOIN its4you_reports4you_sortcol ON its4you_reports4you_sortcol.reportid = its4you_reports4you.reports4youid AND its4you_reports4you_sortcol.columnname!= 'none' AND its4you_reports4you_sortcol.sortcolid!= '4' 
                LEFT JOIN its4you_reports4you_summaries ON its4you_reports4you_summaries.reportsummaryid = its4you_reports4you.reports4youid 
                LEFT JOIN its4you_reports4you_selectcolumn ON its4you_reports4you_selectcolumn.queryid = its4you_reports4you.reports4youid
                 
                $where 
                 
                GROUP BY 
                its4you_reports4you.reports4youid , 
                its4you_reports4you_sortcol.columnname
                 
                ORDER BY 
                its4you_reports4you.reports4youid ASC, 
                its4you_reports4you_sortcol.sortcolid ASC, 
                its4you_reports4you_selectcolumn.columnname ASC ";
//WHERE its4you_reports4you.reports4youid IN (57, 58, 4, 11, 15, 34, 9, 36, 30, 32) 

        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE queryid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        $check_array = array();

        if($noOfColumns>0){
            while ($reportrow = $adb->fetchByAssoc($result)) {
                $reporttype = "";

                $reportid = $reportrow["reports4youid"];

                $timeline_type = $reportrow["timeline_type"];

                $columnname_sc = $reportrow["columnname_sc"];
                $columnname_dt = $reportrow["columnname_dt"];

                if($columnname_sc!=""){
                    $check_array[$reportid]["columnname_sc"][] = $columnname_sc;
                }
                if($columnname_dt!=""){
                    $check_array[$reportid]["columnname_dt"][] = $columnname_dt;
                }
                if($timeline_type!=""){
                    $check_array[$reportid]["timeline_type"][] = $timeline_type;
                }
            }

            if(!empty($check_array)){
                foreach($check_array as $reportid => $report_array){
                    $timeline_type = $report_array["timeline_type"];
                    $columnname_dt = $report_array["columnname_dt"];
                    $columnname_sc = $report_array["columnname_sc"];
                    /*
                    ITS4YouReports::sshow("START TYPIZATION");
                    ITS4YouReports::sshow($reportid);                    
                    ITS4YouReports::sshow($report_array);
                    */
                    if(empty($timeline_type)){
                        // tabular
                        $reporttype = "tabular";
                        //ITS4YouReports::sshow($reporttype);
                        ITS4YouReports::repairReportType($reportid,$reporttype);
                        continue;
                    }
                    if(count($timeline_type)==1 && !empty($columnname_dt)){
                        $reporttype = "summaries_w_details";
                        //ITS4YouReports::sshow($reporttype);
                        ITS4YouReports::repairReportType($reportid,$reporttype);
                        continue;
                    }
                    if(in_array("cols",$timeline_type)){
                        $reporttype = "summaries_matrix";
                        //ITS4YouReports::sshow($reporttype);
                        ITS4YouReports::repairReportType($reportid,$reporttype);
                        continue;
                    }
                    $reporttype = "summaries";
                    //ITS4YouReports::sshow($reporttype);
                    ITS4YouReports::repairReportType($reportid,$reporttype);
                }
            }


        }
        return true;
    }
    public static function repairReportType($reportid,$reporttype){
        $adb = PearDatabase::getInstance();

        $queryDebug = false;
        $queryDebug = true;

        if($reportid!="" && $reporttype!=""){
            $queryDebug==true?$adb->setDebug(true):"";
            $adb->pquery("UPDATE its4you_reports4you SET reporttype = ? WHERE reports4youid = ? ",array($reporttype,$reportid));
            $queryDebug==true?$adb->setDebug(false):"";
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsSelectedColumns($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select queryid, columnindex, columnname from its4you_reports4you_selectcolumn ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE queryid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $queryid = $columnrow["queryid"];
                $columnindex = $columnrow["columnindex"];
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $result1 = $adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?", [getTabid($columnModule), $fixedLabel]);
                $newLabelCheckout = $adb->num_rows($result1);
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_selectcolumn SET columnname = ? WHERE queryid = ? AND columnindex = ? ",array($columnname,$queryid,$columnindex));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsRelCriteria($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select queryid, columnindex, columnname from its4you_reports4you_relcriteria ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE queryid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $queryid = $columnrow["queryid"];
                $columnindex = $columnrow["columnindex"];
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_relcriteria SET columnname = ? WHERE queryid = ? AND columnindex = ? ",array($columnname,$queryid,$columnindex));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsRelCriteriaSummaries($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select reportid, columnindex, columnname from its4you_reports4you_relcriteria_summaries ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE reportid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $queryid = $columnrow["reportid"];
                $columnindex = $columnrow["columnindex"];
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_relcriteria_summaries SET columnname = ? WHERE reportid = ? AND columnindex = ? ",array($columnname,$queryid,$columnindex));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsSelectedSortCol($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select sortcolid, reportid, columnname from its4you_reports4you_sortcol ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE reportid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $sreportid = $columnrow["reportid"];
                $sortcolid = $columnrow["sortcolid"];
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_sortcol SET columnname = ? WHERE reportid = ? AND sortcolid = ? ",array($columnname,$sreportid,$sortcolid));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsSelectedSummaries($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select reportsummaryid, summarytype, columnname from its4you_reports4you_summaries ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE reportsummaryid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $sreportid = $columnrow["reportsummaryid"];
                $summarytype = $columnrow["summarytype"];
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_summaries SET columnname = ? WHERE reportsummaryid = ? AND summarytype = ? ",array($columnname,$sreportid,$summarytype));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsSummariesOrderBy($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select reportid, columnindex, summaries_orderby from its4you_reports4you_summaries_orderby ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE reportid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $sreportid = $columnrow["reportid"];
                $columnindex = $columnrow["columnindex"];
                $columnname = $oldColumnname = $columnrow["summaries_orderby"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_summaries_orderby SET summaries_orderby = ? WHERE reportid = ? AND columnindex = ? ",array($columnname,$sreportid,$columnindex));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 17. 9. 2015 13:41:48
    public static function repairReportsLabels($reportid=""){
        $adb = PearDatabase::getInstance();
        global $default_charset;

        $queryDebug = false;
        $queryDebug = true;

        $ssql = 'select reportid, type, columnlabel, columnname from its4you_reports4you_labels ';
        $params = array();
        if($reportid!=""){
            $ssql .= ' WHERE reportid = ? ';
            $params[] = $reportid;
        }

        $result = $adb->pquery($ssql, $params);
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns>0){
            while ($columnrow = $adb->fetchByAssoc($result)) {
                $sreportid = $columnrow["reportid"];
                $stype = $columnrow["type"];
                $columnlabel = html_entity_decode($columnrow["columnlabel"],ENT_QUOTES,$default_charset);
                $columnname = $oldColumnname = $columnrow["columnname"];
                $columnnameArr = explode(":", $columnname);
                $columnLabelArr = explode("_", $columnnameArr[2]);
                $fixedLabel = $columnModule = "";
                foreach($columnLabelArr as $labelKey => $labelPiece){
                    if($labelKey==0){
                        $columnModule = $labelPiece;
                    }else{
                        if($fixedLabel!=""){
                            $fixedLabel .= " ";
                        }
                        $fixedLabel .= $labelPiece;
                    }
                }
                $newLabelCheckout = $adb->num_rows($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid = ? AND fieldlabel = ?",array(getTabid($columnModule),$fixedLabel)));
                if($newLabelCheckout>0){
                    $columnnameArr[2] = $columnModule."_".$fixedLabel;
                }
                $columnname = implode(":",$columnnameArr);
                $columnname = html_entity_decode($columnname,ENT_QUOTES,$default_charset);
                $queryDebug==true?$adb->setDebug(true):"";
                $adb->pquery("UPDATE its4you_reports4you_labels SET columnname = ? WHERE reportid = ? AND type = ?  AND columnlabel = ? ",array($columnname,$sreportid,$stype,$columnlabel));
                $queryDebug==true?$adb->setDebug(false):"";
            }
        }
        return true;
    }
    // ITS4YOU-END
    // ITS4YOU-CR SlOl 19. 11. 2015 11:36:07
    public static function checkUpdatesTable(){
        /*
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery("SELECT count(id) AS exist FROM its4you_reports4you_updates", array());
        $noOfColumns = $adb->num_rows($result);
        if($noOfColumns==0 || $noOfColumns==""){
            $adb->pquery("
                CREATE TABLE IF NOT EXISTS `its4you_reports4you_updates` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
                  `status` int(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `id` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;",array());
        }
        */
        $_SESSION['its4you_reports_update'] = "1";
    }
    // ITS4YOU-CR SlOl 19. 11. 2015 11:36:07
    public static function checkITS4YouUpdates(){
        $adb = PearDatabase::getInstance();

        $result = $adb->pquery("SELECT name FROM its4you_reports4you_updates WHERE status=?", array(0));
        if($result) {
            $noOfColumns = $adb->num_rows($result);
        }

        if(!isset($_SESSION['its4you_reports_update_checked']) && $_SESSION['its4you_reports_update_checked']!="1" || $noOfColumns>0){

            if(!isset($_SESSION['its4you_reports_update']) && $_SESSION['its4you_reports_update']!="1"){
                ITS4YouReports::checkUpdatesTable();
            }

            $result = $adb->pquery("SELECT name FROM its4you_reports4you_updates WHERE status=?", array(0));
            $noOfColumns = $adb->num_rows($result);

            if($noOfColumns>0){
                while ($updaterow = $adb->fetchByAssoc($result)) {
                    // run updates
                    ITS4YouReports::runUpdateReports4You($updaterow['name']);
                }
            }
        }
        $_SESSION['its4you_reports_update_checked'] = "1";
    }
    // ITS4YOU-CR SlOl 19. 11. 2015 12:05:05
    public static function runUpdateReports4You($update_name){
        if($update_name!=""){
            $adb = PearDatabase::getInstance();
            // run updates
            switch ($update_name){
                case "ndays":
                    $comparators_2_change = array("last7days","last15days","last30days","last60days","last90days","last120days","next7days","next15days","next30days","next60days","next90days","next120days","older1days","older7days","older15days","older30days","older90days","older120days",);
                    $result = $adb->pquery("SELECT queryid, columnindex, comparator, value FROM `its4you_reports4you_relcriteria` WHERE comparator IN (" . generateQuestionMarks($comparators_2_change) . ")", $comparators_2_change);
                    $noOfColumns = $adb->num_rows($result);
                    if($noOfColumns>0){
                        while ($updaterow = $adb->fetchByAssoc($result)) {
                            $queryid = $updaterow["queryid"];
                            $columnindex = $updaterow["columnindex"];
                            $comparator = $updaterow["comparator"];
                            $value = $updaterow["value"];

                            if (stripos($comparator, "last") !== false){
                                $new_comparator = "lastNdays";
                                $new_value = substr($comparator, 4, -4);
                            }elseif(stripos($comparator, "next") !== false){
                                $new_comparator = "nextNdays";
                                $new_value = substr($comparator, 4, -4);
                            }elseif (stripos($comparator, "older") !== false) {
                                $new_comparator = "olderNdays";
                                $new_value = substr($comparator, 5, -4);
                            }else{
                                $new_comparator = $comparator;
                                $new_value = $value;
                            }
                            $adb->pquery("UPDATE its4you_reports4you_relcriteria SET comparator=?, value=? WHERE queryid=? AND columnindex=?",array($new_comparator,$new_value,$queryid,$columnindex));
                        }
                    }
                    break;
                // ITS4YOU-CR SlOl 23. 5. 2016 6:42:54 
                case "cc_sortcol":
                    $adb->pquery("ALTER TABLE `its4you_reports4you_sortcol` CHANGE `sortcolid` `sortcolid` INT( 19 ) NOT NULL COMMENT 'is revealed as type of order column';",array());
                    $adb->pquery("ALTER TABLE `its4you_reports4you_sortcol` ADD `sortcolsequence` INT( 11 ) NOT NULL DEFAULT '0' AFTER `sortcolid`;",array());
                    $adb->pquery("UPDATE `its4you_reports4you_sortcol` SET `sortcolsequence`='1' WHERE `sortcolid`='4';",array());
                    $adb->pquery("ALTER TABLE `its4you_reports4you_sortcol` DROP PRIMARY KEY , ADD PRIMARY KEY ( `sortcolid` , `reportid` , `sortcolsequence` );",array());
                    break;
                // ITS4YOU-END
                // ITS4YOU-CR SlOl 17. 2. 2017 6:36:31
                case "is_active_scheduler":
                    /*$check_result = $adb->pquery("SELECT is_active FROM its4you_reports4you_scheduled_reports WHERE is_active=1 LIMIT 1",array());
                    if ($adb->num_rows($check_result) == 0) {
                        $adb->pquery("UPDATE its4you_reports4you_scheduled_reports set  is_active = '1'",array());
                    }*/
                    break;
                // ITS4YOU-CR SlOl 5. 9. 2017 13:23:16
                case "folder_name_update":
                    $adb->pquery("UPDATE its4you_reports4you_folder set state = ? WHERE foldername != ?",array('CUSTOMIZED','Default'));
                    break;
                // ITS4YOU-END
                case 'recalculate':
                    $layout = Vtiger_Viewer::getDefaultLayoutName();
                    if ('v7' === $layout ){
                        ITS4YouReports::recalculateUsers();
                    }
                    break;
            }
            ITS4YouReports::updateRunUpdate($update_name);
        }
    }
    public static function  updateRunUpdate($name){
        $adb = PearDatabase::getInstance();
        $adb->pquery("UPDATE its4you_reports4you_updates SET status=? WHERE name=?",array(1,$name));
        return true;
    }
    // ITS4YOU-END
    public static function getWidgetSearchArray($recordId=""){
        $widgetSearchBy = $primary_search_values = array();
        $adb = PearDatabase::getInstance();
        if($recordId!=""){
            $wSearch_result = $adb->pquery("SELECT primary_search FROM its4you_reports4you_widget_search WHERE reportid = ?",array($recordId));
            if ($adb->num_rows($wSearch_result) > 0) {
                $widgetSearchBy = $adb->fetchByAssoc($wSearch_result, 0);
                $primary_search = $widgetSearchBy["primary_search"];

                list($sc_tablename, $sc_columnname, $sc_modulestr, $sc_fieldname) = explode(':', $primary_search);
                list($sc_module) = explode('_', $sc_modulestr);
                $sc_module_id = getTabid($sc_module);
                $sc_tablename = strtolower($sc_tablename);

                //$adb->setDebug(true);
                $sc_field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype FROM vtiger_field WHERE tablename = ? and columnname = ? and tabid=?", array($sc_tablename, $sc_columnname, $sc_module_id)), 0);
                //$adb->setDebug(false);
                $sc_field_uitype = $sc_field_row["uitype"];
                if (in_array($sc_field_uitype, ITS4YouReports::$s_uitypes)) {
                    $primary_search_values = ITS4YouReports::getSUiTypeValueArray($sc_field_row,$sc_columnname,$sc_fieldname);
                }

                $widgetSearchBy["primary_search_values"] = $primary_search_values;
            }
        }
        return $widgetSearchBy;
    }
    // ITS4YOU-CR SlOl 10. 3. 2016 7:50:29
    public static function getSUiTypeValueArray($uitype_row,$columnname,$fieldname){
        $return = ['picklistValues' => [], 'valueArr' => [],];
        $adb = PearDatabase::getInstance();
        $currentModule = vglobal('currentModule');
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $r_sel_fields = $request->get('r_sel_fields');
        $value = '';
        $valueArr = [];

        if (!empty($uitype_row) && in_array($uitype_row['uitype'], ITS4YouReports::$s_users_uitypes)) {
            $picklistValues = get_user_array(false);
            $recordId = $request->get('record');
            $recordModel = ITS4YouReports_Record_Model::getInstanceById($recordId);
            $primary_search = $recordModel->report->reportinformations['primary_search'];
            $currField_arr = explode(':', $primary_search);
            $s_module_field_label_str = $currField_arr[2];
            $s_module_field_arr = explode('_', $s_module_field_label_str);
            $moduleName = $s_module_field_arr[0];

            if (vtlib_isModuleActive($moduleName)) {
                global $current_user;
                $currentUserModel = Users_Record_Model::getInstanceFromUserObject($current_user);
                $groups = $currentUserModel->getAccessibleGroupForModule($moduleName);

                if (!empty($groups)) {
                    foreach ($groups as $g_key => $g_name) {
                        $picklistValues[vtranslate('LBL_GROUPS')][$g_key] = $g_name;
                    }
                }
            }

            if (!empty($value)) {
                $valueArr = explode('|##|', $value);
            }
        } elseif (!empty($uitype_row) && '117' === $uitype_row['uitype']) {
            $currencyRes = $adb->query('SELECT id, currency_name FROM vtiger_currency_info WHERE deleted=0');
            $noOfCurrency = $adb->num_rows($currencyRes);
            if ($noOfCurrency) {
                while ($currencyRow = $adb->fetch_array($currencyRes)) {
                    $picklistValues[$currencyRow['currency_name']] = $currencyRow['currency_name'];
                }
            }
            $valueArr = self::sanitizeAndExplodeOptions($r_sel_fields);
        } elseif (!empty($uitype_row) && '56' === $uitype_row['uitype']) {
            $picklistValues = ['0' => 'LBL_NO', '1' => 'LBL_YES'];
            $valueArr = explode(',', $r_sel_fields);
        } elseif (!empty($uitype_row) && '26' === $uitype_row['uitype']) {
            $sql = 'select foldername,folderid from vtiger_attachmentsfolder order by foldername asc';
            $res = $adb->pquery($sql, []);
            for ($i = 0; $i < $adb->num_rows($res); $i++) {
                $fid = $adb->query_result($res, $i, 'folderid');
                $picklistValues[$fid] = $adb->query_result($res, $i, 'foldername');
            }
            $valueArr = explode(',', $r_sel_fields);
        } elseif (!empty($uitype_row) && '27' === $uitype_row['uitype']) {
            $picklistValues = ['I' => 'LBL_INTERNAL', 'E' => 'LBL_EXTERNAL'];
            $valueArr = explode(',', $r_sel_fields);
        } else {
            require_once 'modules/PickList/PickListUtils.php';
            if ('16' === $uitype_row['uitype']) {
                $picklistValues = Vtiger_Util_Helper::getPickListValues($columnname);
            } else {
                global $current_user;
                $roleid = $current_user->roleid;

                $picklistValues = getAssignedPicklistValues($fieldname, $roleid, $adb);
                $valueArr = explode('|##|', $value);
            }

        }
        $return = ['picklistValues' => $picklistValues, 'valueArr' => $valueArr,];

        return $return;
    }

    // ITS4YOU-END
    public static function getUITypeFromColumnStr($sc_col_str=""){
        $sc_field_uitype = "";
        if($sc_col_str!=""){
            $adb = PearDatabase::getInstance();
            list($sc_tablename, $sc_columnname, $sc_modulestr) = explode(':', $sc_col_str);

            $sum_col_array = explode(":", $sc_col_str);
            $last_key = count($sum_col_array) - 1;
            $fieldid = "";

            if ((is_numeric($sum_col_array[$last_key]) && is_numeric($sum_col_array[($last_key - 1)])) || in_array($sum_col_array[$last_key], ITS4YouReports::$customRelationTypes)) {
                $sc_tablename = trim($sc_tablename, "_".$sum_col_array[$last_key]);
            }

            list($sc_module) = explode('_', $sc_modulestr);
            $sc_module_id = getTabid($sc_module);
            //$sc_tablename = trim(strtolower($sc_tablename), "_mif");
            //$adb->setDebug(true);
            $sc_field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype FROM vtiger_field WHERE tablename = ? and columnname = ? and tabid=?", array($sc_tablename, $sc_columnname, $sc_module_id)), 0);
            //$adb->setDebug(false);
            $sc_field_uitype = $sc_field_row["uitype"];
        }
        return $sc_field_uitype;
    }
    // ITS4YOU-CR SlOl 30. 5. 2017 10:13:52 
    public static function getReportNameById($recordId){
        $adb = PEARDatabase::getInstance();
        $reportName = '';
        if(''!=$recordId){
            $sql = "SELECT reports4youname FROM its4you_reports4you WHERE reports4youid = ?";
            $result = $adb->pquery($sql, array($recordId));
            $row = $adb->fetchByAssoc($result);
            $reportName = $row["reports4youname"];
        }
        return $reportName;
    }
    // ITS4YOU-CR SlOl 21. 3. 2016 10:53:21
    public static function addDashboardLinkFor($moduleName="Home",$reportid){
        require_once('vtlib/Vtiger/Module.php');

        $adb = PEARDatabase::getInstance();

        $link_tabid = getTabid($moduleName);
        $link_module = Vtiger_Module::getInstance($moduleName);

        $link_label = ITS4YouReports::getReportNameById($reportid);

        $link_url = ITS4YouReports::$dashboardUrl.$reportid;

        //global $adb;$adb->setDebug(true);
        $result = $adb->pquery("SELECT linkid FROM vtiger_links WHERE linkurl=? AND linklabel=? AND tabid=?", array($link_url,$link_label,$link_tabid));
        $exist = $adb->num_rows($result);
        if ($exist <= 0){
            $link_module->addLink('DASHBOARDWIDGET',$link_label,$link_url,'','');
        }
        //$adb->setDebug(false);
    }
    public static function updateDashboardLinks($reportid,$allow_in_modules=""){
        if($allow_in_modules!=""){
            $adb = PearDatabase::getInstance();

            $allow_in_modules_arr = explode(",", $allow_in_modules);
            $dashboard_link = ITS4YouReports::$dashboardUrl.$reportid;

            $dashboards_sql = "SELECT vtiger_links.linkid, vtiger_links.tabid, vtiger_tab.name  
                                FROM vtiger_links 
                                INNER JOIN vtiger_tab USING(tabid) 
                                WHERE linkurl = ?";
            $dashboards_result = $adb->pquery($dashboards_sql, array($dashboard_link));

            if ($adb->num_rows($dashboards_result) > 0) {
                while ($dashboards_row = $adb->fetchByAssoc($dashboards_result)) {
                    if(!in_array($dashboards_row["name"],$allow_in_modules_arr)){
                        $adb->pquery("DELETE FROM vtiger_links WHERE linkid = ?", array($dashboards_row["linkid"]));
                    }else{
                        $adb->pquery("UPDATE vtiger_links SET linklabel = ? WHERE linkid = ? ",array(ITS4YouReports::getReportNameById($reportid),$dashboards_row["linkid"]));
                        unset($allow_in_modules_arr[array_search($dashboards_row["name"], $allow_in_modules_arr)]);
                    }
                }
            }
            echo "<pre>";print_r($_REQUEST);echo "</pre>";
            if(!empty($allow_in_modules_arr)){
                foreach($allow_in_modules_arr as $moduleName){
                    ITS4YouReports::addDashboardLinkFor($moduleName,$reportid);
                }
            }
        }
    }
    // ITS4YOU-CR SlOl 4. 4. 2016 13:28:19
    public static function getSchedulerGenerateFor($reportid){
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT user_column_str FROM its4you_reports4you_generatefor WHERE reportid=?', array($reportid));
        $return = array();
        if($adb->num_rows($result) > 0) {
            while ($user_column_row = $adb->fetchByAssoc($result)) {
                $return[] = $user_column_row["user_column_str"];
            }
        }
        return $return;
    }
    // ITS4YOU-CR SlOl 18. 5. 2016 11:01:01
    public static function getColumnsOptions(Vtiger_Request $request){
        $moduleName = $request->getModule();

        $Options = array();

        $record = $request->get('record');
        $reportModel = ITS4YouReports_Record_Model::getCleanInstance($record);

        if($record!=""){
            $primary_module = $reportModel->getPrimaryModule();
            $primary_moduleid = getTabid($primary_module);
        }else{
            $primary_moduleid = $request->get("primarymodule");
            $primary_module = vtlib_getModuleNameById($primary_moduleid);
        }
        $Options = $reportModel->getPrimaryColumns($Options,$primary_module,true);

        $secondarymodules =Array();
        if(!empty($reportModel->report->related_modules[$primary_module])) {
            foreach($reportModel->report->related_modules[$primary_module] as $key=>$value){
                $exploded_mid = explode("x", $value["id"]);
                if(strtolower($exploded_mid[1])!="mif"){
                    $secondarymodules[]= $value["id"];
                }
            }
        }
        $secondarymodules_str = implode(":", $secondarymodules);
        $Options_sec = getSecondaryColumns(array(),$secondarymodules_str,$reportModel->report);

        foreach ($Options_sec as $moduleid=>$sec_options) {
            $Options = array_merge($Options, $sec_options);
        }

        return $Options;
    }
    // ITS4YOU-CR SlOl 18. 5. 2016 11:01:01
    public static function getColumnsOptionsAlias(Vtiger_Request $request){
        $Options = ITS4YouReports::getColumnsOptions($request);
        $return_options = array();
        if(!empty($Options)){
            foreach($Options as $options_array){
                foreach($options_array as $opt_array){
                    $opt_col_array = explode(":", $opt_array["value"]);
                    $col_alias = $opt_col_array[1];
                    $last_key = count($opt_col_array) - 1;
                    if (is_numeric($opt_col_array[$last_key]) || in_array($opt_col_array[$last_key], ITS4YouReports::$customRelationTypes)) {
                        $col_alias .= "_".$opt_col_array[$last_key];
                    }
                    $return_options[$col_alias] = $opt_array["value"];
                }
            }
        }
        return $return_options;
    }
    // ITS4YOU-CR SlOl 3. 5. 2018 06:59:01
    public static function setModuleParentTab() {
        $db =  PearDatabase::getInstance();
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if($layout == "v7"){
            $parentTab = 'Tools';
        } else {
            $parentTab = 'Analytics';
        }
        $db->pquery('UPDATE vtiger_tab SET parent=? WHERE name=?',array($parentTab, 'ITS4YouReports'));
        return true;
    }
    // ITS4YOU-END
    public static function recalculateUsers() {
        $allUsers = Users_Record_Model::getAll();
        if (!empty($allUsers)) {
            require_once('modules/Users/CreateUserPrivilegeFile.php');
            foreach($allUsers as $user) {
                createUserPrivilegesfile($user->getId());
                createUserSharingPrivilegesfile($user->getId());
                Vtiger_AccessControl::clearUserPrivileges($user->getId());
            }
        }
        return true;
    }
    // ITS4YOU-CR SlOl 30. 05. 2018 10:08:01
    public static function getKeyMetricsRowsCount($reportId) {
        $db = PearDatabase::getInstance();
        if ($reportId) {
            $result = $db->pquery('SELECT count(id) keys_count FROM its4you_reports4you_key_metrics_rows WHERE deleted=0 AND reportid = ?', array($reportId));
            if ($result) {
                $row = $db->fetchByAssoc($result, 0);
                $keysCount = $row['keys_count'];
            }
        }
        return (int) $keysCount;
    }

    /**
     * @param $r_sel_fields
     *
     * @return array
     */
    public static function sanitizeAndExplodeOptions($r_sel_fields) {
        $r_sel_fields = str_replace(', ', '%S', $r_sel_fields);
        $valueArr = explode(',', $r_sel_fields);
        foreach ($valueArr as $vI => $vV) {
            $valueArr[$vI] = str_replace('%S', ', ', $vV);
        }

        return $valueArr;
    }

    public static function getSharingTypes()
    {
        return [
            'public'  => vtranslate('PUBLIC_FILTER', self::$MODULE_NAME),
            'private' => vtranslate('PRIVATE_FILTER', self::$MODULE_NAME),
            'share'   => vtranslate('SHARE_FILTER', self::$MODULE_NAME)
        ];
    }

    public static function getDefaultSharingType()
    {
        $db = PearDatabase::getInstance();
        $defaultSharingType = 'private';

        $sql = 'SELECT setting_value 
                FROM its4you_reports_crm_settings 
                WHERE setting_name="default_sharing";';
        $result = $db->pquery($sql);
        if ($result) {
            $row = $db->query_result_rowdata($result, 0);
            if (!empty($row['setting_value'])) {
                $defaultSharingType = $row['setting_value'];
            }
        }

        return $defaultSharingType;
    }
}
// ITS4YOU-END 13.5.2014 13:23
?>