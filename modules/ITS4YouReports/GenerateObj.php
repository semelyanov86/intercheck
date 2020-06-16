<?php


require_once('modules/ITS4YouReports/ITS4YouReports.php');
require_once('modules/ITS4YouReports/classes/UIUtils.php');
if(file_exists('modules/ITS4YouReports/highcharts/highcharts.php')){
    require_once('modules/ITS4YouReports/highcharts/highcharts.php');
}
require_once('modules/ITS4YouReports/helpers/ITS4YouErrorLog.php');

//ini_set('display_errors', 1);error_reporting(63);
// global $adb;$adb->setDebug(true);
class GenerateObj {

    public $ch_image_name = "";
    public $report_filename = "";
    public $pdf_filename = "";
    public $temp_files_path = "test/ITS4YouReports/";
    private $groupslist = array();
    private $header_style = "background-color:#737373;color:#F6F6F6;font-weight:bold;font-size:11px;vertical-align:middle;";
    var $qf_array = array();
    var $convert_currency = array();
    var $append_currency_symbol_to_value = array();
    var $skip_fields = array("invoicing_currency_id", "currency_id", "record_id", "lineitem_id");
    public $ch_image_url = "";
    var $g_chart_types = array("vertical" => "column",
        "horizontal" => "bar",
        "verticalstacked" => "column",
        "horizontalstacked" => "bar",
        "linechart" => "line",
        "pie" => "pie",
//        "pie3d" => "PieChart",
        "funnel" => "funnel",
    );
    public $calculation_type_array = array("count", "sum", "avg", "min", "max");
    public $special_columns = array('access_count', 'duration_sum_time',);
    private $summaries_columns = array();
    private $summaries_columns_count = 0;
    private $summaries_header = array();
    private $selectedcolumns_header_row = "";
    private $display_group_totals = true;
    private $selectedcolumns_arr = array();
    private $detail_selectedcolumns_arr = array();
    private $data_record_id = "";
    private $grouping_totals_bg_color = "background-color: #CCCCCC;";

    public $create_pdf_schedule = false;
    public $generate_for = false;
    public $schedule_all_records = 0;

    private $userQuickFilterQuery = false;

    private $reports4you_type = "";
    protected $charts = array();

    private $custom_report_portrait_from = 15;

    /*here you can define portrait from columns count*/
    private $summaries_w_detail_portrait_from = 8;

    private $chartPosition = '';

    // ITS4YOU-CR SlOl 19. 1. 2016 9:48:41
    var $key_metrics_alias = "";
    var $key_metrics_calculation_type = "";
    var $key_metrics_columns_str = "";
    var $key_metrics_rows = "";
    // ITS4YOU-CR SlOl 30. 5. 2016 9:40:40
    var $is_hdn_fval = false;
    // ITS4YOU-END

    // ITS4YOU-CR SlOl 16. 6. 2017 6:32:52
    var $totalVatSumGroup = '';

    // ITS4YOU-CR SlOl 18. 5. 2016 14:10:07
    // CUSTOM CALCULATION COLUMNS ARRAY
    var $cc_array = array();
    var $cc_values_array = array();

    private $to_totals_res = array();
    private $mifEntityTables = array();
	// ITS4YOU-END
	public static $R4YouFilename = 'Reports4You';
	private static $currencyUiTypes = array(71, 72, 712);

	private static $summaryAmountColumns = array('SUM', 'MIN', 'MAX', 'AVG');

	private static $currencyArray = [];
	public $report_html_headerInfo;

    public function getGObjDifTime($t_txt = "") {
        if (G_DEBUG) {
            $c_time = time();
            echo "<pre>$t_txt TIME: " . ($c_time - GT_START) . "</pre>";
        }
        return true;
    }

    function __construct($report, $qf_field = "", $generateFor=false, $schedule_partners=false) {

		$this->to_totals_res = array();

        self::$currencyArray = Vtiger_Util_Helper::getBaseCurrency();

        $this->generate_for = $generateFor;
        if($schedule_partners==true){
            $this->schedule_partners = $schedule_partners;
        }

        $this->setCurrentLanguage4You();
        $this->setCurrentModule4You();

        $rootDirectory = vglobal('root_directory');
        $test_reports4you = $rootDirectory.$this->temp_files_path;
        if (!file_exists($test_reports4you)) {
            mkdir($test_reports4you, 0777, true);
        }

        $this->time_debug = false;

        define("GT_START", time());
        //define("G_DEBUG",true);
        define("G_DEBUG", false);
        if (G_DEBUG) {
            ITS4YouReports::sshow("START T");
            $this->getGObjDifTime();
        }
        $this->ui10_fields = array();

        // ITS4YOU-CR SlOl 17. 2. 2017 6:53:07
        ITS4YouReports::checkITS4YouUpdates();
        // ITS4YOU-END

//echo "<pre>";print_r($report);echo "</pre>";
        $this->report_obj = $report;

        $this->reports4you_type = $report->reportinformations["reporttype"];

        if(isset($this->reports4you_type) && $this->reports4you_type=="custom_report"){
            $custom_sql = ITS4YouReports::validateCustomSql($report->reportinformations["custom_sql"],'run');
            $this->tf_sql = $custom_sql;
            $this->r_permitted = $this->report_obj->CheckReportPermissions($this->report_obj->primarymodule, $this->report_obj->record, $die_due);
        }else{
            $this->generateQuery($this->report_obj, $qf_field);
            $this->summaries_labels = array();
            // setup currency array
            $adb = PearDatabase::getInstance();
            $this->currency_symbols = array();
            $currencyres = $adb->pquery("SELECT id, currency_symbol FROM vtiger_currency_info ", array());
            $nocurrency = $adb->num_rows($currencyres);
            if ($nocurrency > 0) {
                while ($currency_row = $adb->fetch_array($currencyres)) {
                    $this->currency_symbols[$currency_row["id"]] = $currency_row["currency_symbol"];
                }
            }
        }
    }

    private function getQFArray() {
        $qf_array = array();
        $qf_temp = $this->report_obj->getSelectedQFColumnsArray($this->report_obj->record);
        if (!empty($qf_temp)) {
            foreach ($qf_temp as $qf_temp_arr) {
                $qf_array[] = $qf_temp_arr["fieldcolname"];
            }
        }
        $this->qf_array = $qf_array;
        return true;
    }

    public function populateQueryInformations($selectedcolumns_arr, &$join_array, &$columns_array) {

        global $inventory_entities, $inventory_entities_tables, $related_join_array;

        $this->selectedcolumns_arr = $selectedcolumns_arr;
        $primary_focus = CRMEntity::getInstance($this->report_obj->primarymodule);
        $primary_focus->modulename = $this->report_obj->primarymodule;
        $this->parimary_table_name = $primary_table_name = $primary_focus->table_name;
        $this->parimary_table_index = $primary_table_index = $primary_focus->table_index;
        foreach ($primary_focus->tab_name as $other_table) {
            $primary_join_array[$other_table] = $primary_focus->tab_name_index[$other_table];
        }
        // realted modules array to tables ->
        if(isset($this->report_obj->relatedmodulesarray) && !empty($this->report_obj->relatedmodulesarray)){
            foreach ($this->report_obj->relatedmodulesarray as $key => $rmod_tabid) {
                $rmod_arr = explode("x", $rmod_tabid);
                $r_module = vtlib_getModuleNameById($rmod_arr[0]);
                if (vtlib_isModuleActive($r_module)) {
                    $related_focus = CRMEntity::getInstance($r_module);
                    $related_focus->modulename = $this->report_obj->primarymodule;
                    $realted_table_name = $related_focus->table_name;
                    $realted_table_index = $related_focus->table_index;
                    foreach ($related_focus->tab_name as $other_table) {
                        $related_join_array[$r_module][$other_table] = $related_focus->tab_name_index[$other_table];
                    }
                }
            }
        }
        $adb = PEARDatabase::getInstance();

        $join_array = $columns_array = Array();
        require_once("modules/ITS4YouReports/classes/UIFactory.php");

        $debug_generation = false;
        //$debug_generation = true;
        if($debug_generation){
            ITS4YouReports::sshow($selectedcolumns_arr);
        }

        /*         * * QUERY INFORMATIONS PREPARING START ** */
        foreach ($selectedcolumns_arr as $key => $arr) {
            $fld_string = $arr["fieldcolname"];
            $e_arr = explode(":", $fld_string);
            // $e_arr 0 1 2 used to define columnstotal array
            $columns_total_str = $e_arr[0] . ":" . $e_arr[1] . ":" . $e_arr[2];
            $tablename = $e_arr[0];
            $columnname = $e_arr[1];
            $field_string = $e_arr[2];
            $fieldname = $e_arr[3];
            $last_key = count($e_arr) - 1;
            $field_uitype = $fieldid = $field_columnname = $column_tablename = "";
            $field_module_arr = explode("_", $field_string);
            $field_module = $field_module_arr[0];
            $field_module_id = getTabid($field_module_arr[0]);

            $as_prefix = $e_arr[$last_key];
            if (is_numeric($as_prefix) || in_array(strtolower($as_prefix), array("inv", "mif"))) {
                $relfieldres = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldid = ? ", array($as_prefix));
                if($adb->num_rows($relfieldres) > 0){
                    $rel_field_row = $adb->fetchByAssoc($relfieldres, 0);
                    $rel_field_uitype = $rel_field_row["uitype"];
                    if($rel_field_uitype=="10"){
                        $relModulesSql = "SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid = ? ";
//$adb->setDebug(true);
                        $relModulesRes = $adb->pquery($relModulesSql, array($as_prefix));
//$adb->setDebug(false);
                        $fieldRelModules = array();
                        $numRels = $adb->num_rows($relModulesRes);
                        if ($numRels > 0) {
                            while ($relRow = $adb->fetchByAssoc($relModulesRes)) {
                                $fieldRelModules[] = $relRow["relmodule"];
                            }
                        }
                        if(!empty($fieldRelModules)){
                            foreach($fieldRelModules as $fieldRelModuleName){
                                $fieldRelModuleId = getTabid($fieldRelModuleName);
                                //$access_query_scope = "_$rel_field_uitype"."_".$fieldRelModuleId."_" . strtolower($as_prefix);
                                $access_query_scope = "_".$fieldRelModuleId."_" . strtolower($as_prefix);
                                if (!array_key_exists($fieldRelModuleName, $this->modules_to_access_query) || !in_array($access_query_tablename, $this->modules_to_access_query)) {
                                    $this->modules_to_access_query[$fieldRelModuleName] = $access_query_scope;
                                }
                            }
                        }
                    }else{
                        $access_query_scope = "_$rel_field_uitype"."_" . strtolower($as_prefix);
                        if (!array_key_exists($field_module, $this->modules_to_access_query) || !in_array($access_query_tablename, $this->modules_to_access_query)) {
                            $this->modules_to_access_query[$field_module] = $access_query_scope;
                        }
                    }
                }
            }
//error_reporting(63);
//ini_set('display_errors', 1);

            $joined = false;
            $field_row = array();
            global $inventory_entities;
            /*             * * PRIMARY FIELDS !!!!! ** */
            //$adb->setDebug(true);
            if($field_module_id=="9" || $field_module_id=="16"){
                $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE tablename=? AND columnname=? AND (tabid=? OR tabid=?)", array($tablename, $columnname, 9, 16)), 0);
            }else{
                $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE tablename=? AND columnname=? AND tabid=?", array($tablename, $columnname, $field_module_id)), 0);
            }
            //$adb->setDebug(false);
            if (!empty($field_row)) {
                $field_uitype = $field_row["uitype"];
            }
            if($debug_generation){
                ITS4YouReports::sshow($tablename." - ".$columnname);
            }

            if($tablename=='vtiger_inventoryproductrel'){
                if($this->totalVatSumGroup=='' && $columnname=='ps_producttotalvatsum'){
                    $this->totalVatSumGroup = true;
                }else{
                    $this->totalVatSumGroup = false;
                }
            }

            if ($columnname!="converted" && $columnname!="r4u_hyperlink" && $columnname!="ps_producttotalvatsum" && !is_numeric($e_arr[$last_key]) && array_key_exists($tablename, $primary_join_array) && $field_module_id == $this->report_obj->primarymoduleid && !empty($field_row)) {
                if($debug_generation){
                    ITS4YouReports::sshow(1);
                }
                $fieldid = $field_row["fieldid"];
                $params = Array('fieldid' => "$fieldid",
                    'fieldtabid' => $field_row["tabid"],
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $primary_focus->table_index,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
                /*                 * * PRIMARY INVENTORY FIELDS !!!!! ** */
            } elseif ($tablename == "vtiger_inventoryproductrel") {
                if($debug_generation){
                    ITS4YouReports::sshow(2);
                }
                $fieldid = $field_row["fieldid"];
                $params = Array('fieldid' => "",
                    'fieldtabid' => $field_row["tabid"],
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $primary_focus->table_index,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getInventoryJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            // primary joined, continue
            if ($joined) {
                continue;
            }
            if ($e_arr[$last_key] != "MIF" && in_array($columnname, $this->special_columns)) {
                if($debug_generation){
                    ITS4YouReports::sshow(3);
                }
                $params = Array('fieldid' => "",
                    'fieldtabid' => $this->report_obj->primarymoduleid,
                    'field_uitype' => "1",
                    'fieldname' => $columnname,
                    'columnname' => $columnname,
                    'tablename' => "",
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $primary_focus->table_index,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            if ($joined) {
                continue;
            }
            /*             * * OTHER INVENTORY FIELDS !!!!! ** */
            if ($e_arr[$last_key] == "INV") {
                if($debug_generation){
                    ITS4YouReports::sshow(4);
                }
                $field_uitype = "INV";
                $params = Array('fieldid' => "",
                    'fieldtabid' => $field_module_id,
                    'fieldmodule' => $field_module,
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $primary_focus->table_index,
                    'fld_string' => $fld_string,
                );
                $using_array["using"]["tablename"] = $primary_focus->table_name;
                $using_array["using"]["columnname"] = $primary_focus->table_index;
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                // going to UITypeINV
                $uifactory->getInventoryJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            // inventory joined, continue
            if ($joined) {
                continue;
            }
            /*             * * MORE INFO FIELDS !!!!! ** */
            if ($e_arr[$last_key] == "MIF") {
                if($debug_generation){
                    ITS4YouReports::sshow(5);
                }
                $field_uitype = "MIF";
                $params = Array('fieldid' => "",
                    'fieldtabid' => $field_module_id,
                    'fieldmodule' => $field_module,
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $primary_focus->table_index,
                    'fld_string' => $fld_string,
                );
                $using_array["using"]["tablename"] = $primary_focus->table_name;
                $using_array["using"]["columnname"] = $primary_focus->table_index;
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                // going to UITypeDefault
                $uifactory->getMoreInfoJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            // more info joined, continue
            if ($joined) {
                continue;
            }
            /*             * * RELATED FIELDS !!!!!  fields with FIELDID = realted Fields ** */
            if (is_numeric($e_arr[$last_key]) && trim($tablename, $e_arr[$last_key]) != "vtiger_inventoryproductrel") {
                if($debug_generation){
                    ITS4YouReports::sshow(6);
                }
                $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldid=?", array($e_arr[$last_key])), 0);
                $field_uitype = $field_row["uitype"];
                $fieldid = $e_arr[$last_key];
                /*                 * * @!! using array loaded in getJoinSQLbyFieldRelation !!@ ** */
                // for module start
                if (is_numeric($e_arr[$last_key - 1])) {
                    $formodule = $e_arr[$last_key - 1];
                } else {
                    $temp_fm = explode("_", $e_arr[2]);
                    $formodule = getTabid($temp_fm[0]);
                }
                // for module end
                $params = Array('fieldid' => $fieldid,
                    'fieldtabid' => $field_row["tabid"],
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => trim($tablename, "_".$fieldid),
                    'table_index' => "",
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $field_row["tablename"],
                    'using_columnname' => $field_row["columnname"],
                    'formodule' => $formodule,
                    'fld_string' => $fld_string,
                );
//ITS4YouReports::sshow($params);
//exit;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQLbyFieldRelation($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            // related fields joined, continue
            if ($joined) {
                continue;
            }
            // related inventoryproductrel control
            /*             * * RELATED INVENTORY FIELDS !!!!! ** */
            $inv_array = explode("vtiger_inventoryproductrel", $tablename);
            if (isset($inv_array[1]) && is_numeric($inv_array[1]) ) {
                if($debug_generation){
                    ITS4YouReports::sshow(7);
                }
                $r_id = $inv_array[1];

                $field_res = $adb->pquery("SELECT uitype,tabid,tablename,columnname FROM vtiger_field WHERE fieldid=?", array($r_id));
                $field_rows = $adb->num_rows($field_res);
                if ($field_rows > 0) {
                    $field_row = $adb->fetchByAssoc($field_res, 0);

                    // $field_uitype = $field_row["uitype"];
                    $field_uitype = "INV";

                    $forModuleArray = explode("_",$e_arr[2]);
                    $r_module = $forModuleArray[0];
                    if (vtlib_isModuleActive($r_module)) {
                        $r_tabid = getTabid($r_module);

                        $field_tablename = $field_row["tablename"];
                        $field_columnname = $field_row["columnname"];

                        //$related_focus = CRMEntity::getInstance($r_module);
                        //$related_focus->modulename = $r_module;

                        $params = Array('fieldid' => $r_id,
                            'fieldtabid' => $r_tabid,
                            'field_uitype' => $field_uitype,
                            'fieldname' => $fieldname,
                            'columnname' => $columnname,
                            'tablename' => trim($tablename, $r_id),
                            'table_index' => $primary_focus->table_index,
                            'report_primary_table' => $primary_focus->table_name,
                            'primary_table_name' => $primary_focus->table_name,
                            'primary_table_index' => $primary_focus->table_index[$primary_focus->table_name],
                            'primary_tableid' => '',
                            'using_aliastablename' => $field_tablename,
                            'using_columnname' => $field_columnname,
                            'formodule' => $r_module,
                            'fld_string' => $fld_string,
                        );
                        $using_array = getJoinInformation($params);

                        $params["using_array"] = $using_array;
                        $uifactory = new UIFactory($params);
                        $uifactory->getInventoryJoinSQL($field_uitype, $join_array, $columns_array);
                    }
                }
                $joined = true;
            }
            if($joined){
                continue;
            }
            /* 			elseif(vtlib_getModuleNameById($inv_array[1])!=""){
              / **				// vazba pomocou more info
              $r_tabid=$inv_array[1];
              $params = array("aliasid"=>$r_tabid,
              "columnname"=>$e_arr[3],
              "column_tablename"=>$e_arr[0],
              );
              $uifactory = new UIFactory($params);
              $uifactory->getInventoryJoinSQL("RelatedInventory",$join_array,$columns_array);
              $joined = true;* /
              }
              // related inventory joined, continue
              if($joined){
              continue;
              }
             */
            /*             * * PRIMARY SPECIAL FIELDS (e.g. Invoice->AccountName etc.) !!!!! ** */
            $f_field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?", array($fieldname, $this->report_obj->primarymoduleid)), 0);
            if (is_array($f_field_row)) {
                if($debug_generation){
                    ITS4YouReports::sshow(8);
                }
                $field_uitype = $f_field_row["uitype"];
                $fieldid = $f_field_row["fieldid"];
                $params = Array('fieldid' => $fieldid,
                    'fieldtabid' => $f_field_row["tabid"],
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $columnname,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            if ($joined) {
                continue;
            }

            // ITS4YOU-CR SlOl 28. 6. 2016 9:59:07
            if(in_array($columnname,ITS4YouReports::$modTrackerColumns)==true){
                if($debug_generation){
                    ITS4YouReports::sshow(9);
                }
                $field_uitype = "Default";
                $fieldid = "mif";
                $f_field_row_tabid = getTabid("ModTracker");
                $params = Array('fieldid' => $fieldid,
                    'fieldtabid' => $f_field_row_tabid,
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $columnname,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            if ($joined) {
                continue;
            }
            // ITS4YOU-END

            if ($columnname == "inviteeid" && $tablename == "vtiger_invitees") {
                if($debug_generation){
                    ITS4YouReports::sshow(9);
                }
                $field_uitype = "Default";
                $fieldid = '';
                $params = Array('fieldid' => $fieldid,
                    'fieldtabid' => $field_module_id,
                    'field_uitype' => $field_uitype,
                    'fieldname' => $fieldname,
                    'columnname' => $columnname,
                    'tablename' => $tablename,
                    'table_index' => $primary_focus->tab_name_index,
                    'report_primary_table' => $primary_focus->table_name,
                    'primary_table_name' => $primary_table_name,
                    'primary_table_index' => $primary_table_index,
                    'primary_tableid' => $this->report_obj->primarymoduleid,
                    'using_aliastablename' => $primary_focus->table_name,
                    'using_columnname' => $columnname,
                    'fld_string' => $fld_string,
                );
                $using_array = getJoinInformation($params);
                $params["using_array"] = $using_array;
                $uifactory = new UIFactory($params);
                $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
                $joined = true;
            }
            if ($joined) {
                continue;
            }

            if ($columnname == "crmid" && $tablename == "vtiger_crmentity") {
                if($debug_generation){
                    ITS4YouReports::sshow(10);
                }
                if ($this->isentitytype == "1") {
                    $fld_cond = $tablename . "." . $columnname;
                } else {
                    $fld_cond = $primary_focus->table_name . "." . $primary_focus->tab_name_index[$primary_focus->table_name];
                }
                $fld_alias = $columnname;
                if (strpos($field_string, "LBL_RECORDS") !== false && strpos($e_arr[3], "count") !== false) {
                    $fld_alias .= "_r";
                }
                $columns_array_value = " $fld_cond AS $fld_alias";
                $columns_array[] = $columns_array_value;
                $columns_array[$fld_string]["fld_alias"] = $fld_alias;
                $columns_array[$fld_string]["fld_sql_str"] = $columns_array_value;
                $columns_array[$fld_string]["fld_cond"] = $fld_cond;
                $columns_array["uitype_$fld_alias"] = "";
                $columns_array[$fld_alias] = $fld_string;
                $joined = true;
            }
            if ($joined) {
                continue;
            }

            if ($columnname == "converted" && $tablename == "vtiger_leaddetails") {
                if($debug_generation){
                    ITS4YouReports::sshow(11);
                }
                if ($this->isentitytype == "1") {
                    $fld_cond = $tablename . "." . $columnname;
                } else {
                    $fld_cond = $primary_focus->table_name . "." . $primary_focus->tab_name_index[$primary_focus->table_name];
                }
                $fld_alias = $columnname;
                if (strpos($e_arr[3], "count") !== false) {
                    $fld_alias .= "_r";
                }
                $columns_array_value = " $fld_cond AS $fld_alias";
                $columns_array[] = $columns_array_value;
                $columns_array[$fld_string]["fld_alias"] = $fld_alias;
                $columns_array[$fld_string]["fld_sql_str"] = $columns_array_value;
                $columns_array[$fld_string]["fld_cond"] = $fld_cond;
                $columns_array["uitype_$fld_alias"] = "";
                $columns_array[$fld_alias] = $fld_string;
                $joined = true;
            }
            if ($joined) {
                continue;
            }

            if ($columnname == "r4u_hyperlink") {
                if($debug_generation){
                    ITS4YouReports::sshow(12);
                }
                $fld_cond = "vtiger_crmentity.crmid";

                $fld_alias = $columnname;

                $columns_array_value = " $fld_cond AS $columnname";
                $columns_array[] = $columns_array_value;
                $columns_array[$fld_string]["fld_alias"] = $fld_alias;
                $columns_array[$fld_string]["fld_sql_str"] = $columns_array_value;
                $columns_array[$fld_string]["fld_cond"] = $fld_cond;
                $columns_array["uitype_$fld_alias"] = "";
                $columns_array[$fld_alias] = $fld_string;
                $joined = true;
            }
            if ($joined) {
                continue;
            }
        }
        /*         * * QUERY INFORMATIONS PREPARING END ** */
    }

    private function getColumnsStrToQuery($columns_array) {
        $c_columns = "";
        if ($this->generate_type == "grouping" && !empty($this->report_obj->reportinformations["summaries_columns"]) || ($this->generate_type == "grouping" && empty($this->report_obj->reportinformations["summaries_columns"]) && $this->report_obj->reportinformations["Group1"] != "none")) {
            for ($gi = 1; $gi < 4; $gi++) {
                $groupname = "Group$gi";
                if ($this->report_obj->$groupname != "none") {
                    // ITS4YOU-CR SlOl | 15.5.2014 11:46
                    $fld_col_str_arr = explode(" AS ", $columns_array[$this->report_obj->$groupname]["fld_sql_str"]);
                    $gi_con_column = $fld_col_str_arr[0];
                    $gi_con_alias = $fld_col_str_arr[1];
                    //if (isset($this->report_obj->reportinformations["timeline_columnstr$gi"]) && $this->report_obj->reportinformations["timeline_columnstr$gi"] == $this->report_obj->$groupname && $this->report_obj->reportinformations["timeline_columnfreq$gi"] != "") {
                    if (isset($this->report_obj->reportinformations["timeline_columnstr$gi"]) && $this->report_obj->reportinformations["timeline_columnstr$gi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$gi"] != "@vlv@") {
                        $gi_con_column = $this->getTimeLineColumnSql($gi_con_column, $this->report_obj->reportinformations["timeline_columnstr$gi"]);
                    }
                    // ITS4YOU-END 15.5.2014 11:46
                    $SUMcolumns_array[] = " $gi_con_column AS $gi_con_alias ";
                }
            }
            foreach ($this->sum_col_sql_a as $column_str => $summaries_arr) {
                $fld_sql_str_array = explode(" AS ", $columns_array[$column_str]["fld_sql_str"]);

                $column_arr = explode(":", $column_str);
                $tablename = $column_arr[0];
                $columnname = $column_arr[1];
                if($tablename=="vtiger_crmentity" && $columnname=="crmid" && !in_array($fld_sql_str_array[1],array("crmid","crmid_r"))){
                    $last_key = $column_arr[(count($column_arr)-1)];
                    $last_key_as = "";
                    if (is_numeric($last_key) || in_array(strtolower($last_key), array("inv", "mif"))) {
                        $last_key_as = $last_key;
                    }
                    $fld_sql_str_array = array();
                    $fld_sql_str_array[] = "vtiger_crmentity.crmid";
                    $fld_sql_str_array[] = "crmid_$last_key_as";
                }
                $fld_str = $fld_sql_str_array[0];
                $fld_str_as = $fld_sql_str_array[1];
                foreach ($summaries_arr as $calculation_type) {
                    /* if($calculation_type=="COUNT"){
                      $SUMcolumns_array[] = "$fld_str AS $fld_str_as";
                      } */
                    $SUMcolumns_array[] = "$calculation_type($fld_str) AS $fld_str_as" . "_$calculation_type";
                    $SUMcolumns_array["$fld_str_as" . "_$calculation_type"] = $column_str;
                }
            }
            $columns_array = $SUMcolumns_array;
            $this->sm_columns_array = $columns_array;
        }
        foreach ($columns_array as $r_key => $r_columns) {
            if (is_numeric($r_key)) {
                $c_columns .= "$r_columns, 
                                        ";
            }
        }
        $c_columns = trim($c_columns, ", 
");
        return $c_columns;
    }

    private function getJoinStrToQuery($join_array) {
        $j_tables = "";
        foreach ($join_array as $j_table => $j_array) {
            if($j_table!=" vtiger_crmentity AS vtiger_crmentity "){
                $joinType = 'LEFT';
                /** THIS part populate array with more info crmentity tables to use
                * deleted=0 conditon
                * = prevent deleted records displaying ! */
                if (strpos($j_table, 'vtiger_crmentity AS vtiger_crmentity_') && array_key_exists($j_table, $join_array)) {
                    $this->mifEntityTables[] = $j_table;
                }

                if (isset($j_array["using"]) && $j_array["using"] != "") {
                    if ($j_array["joincol"] == "") {
                        $j_tables.="
    $joinType JOIN $j_table ON " . $j_array["using"] . " ";
                    } else {
                        $j_tables.="
    $joinType JOIN $j_table ON " . $j_array["joincol"] . " = " . $j_array["using"] . " ";
                    }
                } else {
                    $j_tables.="
    $joinType JOIN $j_table ON " . $j_array["joincol"] . " = " . $primary_focus->table_name . "." . $primary_focus->table_index . " ";
                }
            }
        }
        return $j_tables;
    }

    private function generateQuery($report, $qf_field, $generate_type = "standard") {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        global $inventory_entities, $inventory_entities_tables, $related_join_array;
        global $current_user;
        $adb = PEARDatabase::getInstance();

        $this->generate_type = $generate_type;
        $this->mifEntityTables = array();

        $this->isentitytype = $this->isEntityType($this->report_obj->primarymoduleid);

        if (empty($this->report_obj->primarymodule)) {
			return false;
		}

        $primary_focus = CRMEntity::getInstance($this->report_obj->primarymodule);
        $primary_focus->modulename = $this->report_obj->primarymodule;

        $no_field_res = $adb->pquery("SELECT columnname FROM vtiger_field WHERE uitype = ? AND tabid = ?", array(4,$this->report_obj->primarymoduleid));
        if($adb->num_rows($no_field_res) > 0){
            $no_field_row = $adb->fetchByAssoc($no_field_res, 0);
            $this->primary_no_column = $no_field_row["columnname"];
        }

        $die_due = true;

        if(isset($_REQUEST["view"]) && $_REQUEST["view"]=="ShowWidget"){
            $die_due = false;
        } elseif(isset($_REQUEST["view"]) && $_REQUEST["view"]=="ShowKeyMetrics"){
            $die_due = false;
        } elseif (!isset($_REQUEST) || $_REQUEST["service"]=="Schedule Reports4You"){
            $die_due = false;
        }
        $this->r_permitted = $this->report_obj->CheckReportPermissions($this->report_obj->primarymodule, $this->report_obj->record, $die_due);
        if(!$this->r_permitted) {
            return false;
        }

        $primary_table_name = $primary_focus->table_name;
        $primary_table_index = $primary_focus->table_index;
        foreach ($primary_focus->tab_name as $other_table) {
            $primary_join_array[$other_table] = $primary_focus->tab_name_index[$other_table];
        }
        // realted modules array to tables ->
        if(!empty($this->report_obj->relatedmodulesarray)){
            foreach ($this->report_obj->relatedmodulesarray as $key => $rmod_tabid) {
                $rmod_arr = explode("x", $rmod_tabid);
                $r_module = vtlib_getModuleNameById($rmod_arr[0]);
                if (vtlib_isModuleActive($r_module)) {
                    $related_focus = CRMEntity::getInstance($r_module);
                    $related_focus->modulename = $this->report_obj->primarymodule;
                    $realted_table_name = $related_focus->table_name;
                    $realted_table_index = $related_focus->table_index;
                    foreach ($related_focus->tab_name as $other_table) {
                        $related_join_array[$r_module][$other_table] = $related_focus->tab_name_index[$other_table];
                    }
                }
            }
        }
        if (($this->generate_type == "grouping" && !empty($this->report_obj->reportinformations["summaries_columns"])) || ($this->generate_type == "grouping" && empty($this->report_obj->reportinformations["summaries_columns"]) && $this->report_obj->reportinformations["Group1"] != "none")) {
            $selectedcolumns_arr = $mapped_cols = array();
            global $default_charset;
            // ITS4YOU-UP SlOl 19. 1. 2017 16:25:00
            $summaries_columns_decoded = $this->report_obj->reportinformations["summaries_columns"];
            if(!empty($summaries_columns_decoded)){
                foreach($summaries_columns_decoded as $stdkey => $stdcol){
                    $summaries_columns_decoded[$stdkey]["columnname"] = html_entity_decode($stdcol["columnname"], ENT_QUOTES, $default_charset);
                }
            }
            $this->summaries_columns = $summaries_columns_decoded;
            // ITS4YOU-END
            $this->summaries_columns_count = count($this->report_obj->reportinformations["summaries_columns"]);
            $this->summaries_columns_colspan = (count($this->report_obj->reportinformations["summaries_columns"]) + 1);
            for ($gi = 1; $gi < 4; $gi++) {
                if ($this->report_obj->reportinformations["Group$gi"] != "none") {
                    $selectedcolumns_arr[] = array("fieldcolname" => $this->report_obj->reportinformations["Group$gi"], "selected" => "", "fieldlabel" => "Group$gi",);
                }
            }
            foreach ($this->report_obj->reportinformations["summaries_columns"] as $key => $summaries_column_arr) {
                $summaries_column_arr_columnname = "";
                $to_join = array();
                $summaries_column_arr_ex = explode(":", $summaries_column_arr["columnname"]);
                if (is_numeric($summaries_column_arr_ex[5]) || in_array($summaries_column_arr_ex[5], ITS4YouReports::$customRelationTypes)) {
                    $f_ng = 6;
                } else {
                    $f_ng = 5;
                }
                for ($ng = 0; $ng < $f_ng; $ng++) {
                    $to_join[] = $summaries_column_arr_ex[$ng];
                }
                $summaries_column_arr_columnname = implode(":", $to_join);
                if (!in_array($summaries_column_arr_columnname, $mapped_cols)) {
                    $summaries_lbl_arr = $this->setSummariesLBLKeys($summaries_column_arr_columnname);
                    $this->summaries_labels = array_merge($this->summaries_labels, $summaries_lbl_arr);
                    $selectedcolumns_arr[] = array("fieldcolname" => $summaries_column_arr_columnname, "selected" => "", "fieldlabel" => "",);
                    $mapped_cols[] = $summaries_column_arr_columnname;
                }
            }
            if ($this->report_obj->reportinformations["Group1"] != "none" && $this->summaries_columns_count == 0) {
                $detail_selectedcolumns_arr = $this->report_obj->getSelectedColumnListArray($this->report_obj->record);
                $this->detail_selectedcolumns_arr = $detail_selectedcolumns_arr;
            }

            // if($this->generate_type == "grouping" && empty($this->report_obj->reportinformations["summaries_columns"]) && $this->report_obj->reportinformations["Group1"] != "none"){
            if ($this->generate_type == "grouping" && $this->report_obj->reportinformations["Group1"] != "none" && $this->report_obj->reportinformations["Group2"] == "none") {
                $primarymodule = $this->report_obj->primarymodule;
                $count_records_col = "vtiger_crmentity:crmid:" . $primarymodule . "_COUNT Records:" . $primarymodule . "_count:V";
                if (!$this->report_obj->in_multiarray($count_records_col, $selectedcolumns_arr, "fieldcolname")) {
                    $selectedcolumns_arr[] = array("fieldcolname" => $count_records_col, "selected" => "", "fieldlabel" => "");
                    $this->sum_col_sql_a[$count_records_col][0] = "COUNT";
                }
            }
        } else {
            $selectedcolumns_arr = $this->report_obj->getSelectedColumnListArray($this->report_obj->record);
            $this->detail_selectedcolumns_arr = $selectedcolumns_arr;

            // pridam nakoniec sql quick filters, ktore neboli zvolene do selected columns
            $this->qf_array = $this->report_obj->getSelectedQFColumnsArray($this->report_obj->record);
            if(!empty($this->qf_array)){
                foreach ($this->qf_array as $key => $value_arr) {
                    $selectedQFcolumns_arr[] = $value_arr;
                    if (!$this->report_obj->in_multiarray($value_arr["fieldcolname"], $selectedcolumns_arr, "fieldcolname")) {
                        array_push($selectedcolumns_arr, $value_arr);
                    }
                }
            }
            if(!empty($this->report_obj->relatedmodulesarray)){
                foreach ($this->report_obj->relatedmodulesarray as $key => $module_value) {
                    $val_arr = explode("x", $module_value);
                    $mv_tabid = $val_arr[0];
                    if (isset($val_arr[1]) && $val_arr[1] != "") {
                        $mv_fieldid = $val_arr[1];
                        $related_fields_arr[] = array($mv_tabid => $mv_fieldid);
                    }
                }
            }
            for ($gi = 1; $gi < 4; $gi++) {
                if ($this->report_obj->reportinformations["Group$gi"] != "none" && $this->report_obj->reportinformations["Group1"] != "none" && !$this->report_obj->in_multiarray($this->report_obj->reportinformations["Group$gi"], $selectedcolumns_arr, "fieldcolname")) {
                    $selectedcolumns_arr[] = array("fieldcolname" => $this->report_obj->reportinformations["Group$gi"], "selected" => "", "fieldlabel" => "Group$gi",);
                }
            }

            // ITS4YOU-CR SlOl 18. 5. 2016 13:43:01
            if(!empty($this->report_obj->reportinformations["cc_array"])){
                $cc_res = $adb->pquery("SELECT columnname FROM its4you_reports4you_cc_columns WHERE reportid = ? ORDER BY columnindex ASC ",array($this->report_obj->record));
                if($adb->num_rows($cc_res)>0){
                    while($cc_row = $adb->fetchByAssoc($cc_res)) {
                        $this->cc_array[] = $cc_row["columnname"];
                        if(!$this->report_obj->in_multiarray($cc_row["columnname"], $selectedcolumns_arr, "fieldcolname")) {
                            $value_arr = array("fieldcolname" => $cc_row["columnname"], "selected" => "", "fieldlabel" => "",);
                            array_push($selectedcolumns_arr, $value_arr);
                        }
                    }
                }
            }
            // ITS4YOU-END

        }

        if (isset($_REQUEST["reload"])) {
            $xyz = $this->getReqConditions($this->report_obj->adv_sel_fields);
        } else {
            $xyz = $this->getAdvFilterSql($this->report_obj->record, array());
        }
        if (!empty($this->advf_col_array)) {
            foreach ($this->advf_col_array as $key => $advf_col_str) {
                if ($this->generate_type != "grouping" && strpos($advf_col_str, "campaignrelstatus") !== false) {
                    continue;
                }
                if ($this->generate_type != "grouping" && strpos($advf_col_str, "access_count") !== false) {
                    continue;
                }
                if ($advf_col_str != "" && !$this->report_obj->in_multiarray($advf_col_str, $selectedcolumns_arr, "fieldcolname")) {
                    $selectedcolumns_arr[] = array("fieldcolname" => $advf_col_str, "selected" => "", "fieldlabel" => "",);
                }
            }
        }
        // ITS4YOU-CR SlOl 10. 3. 2016 12:48:44
        if($request->has('primarySearchBy') && !empty($request->get('primarySearchBy'))){
            $widgetSearchBy = ITS4YouReports::getWidgetSearchArray($this->report_obj->record);
            if(!in_array($widgetSearchBy['primary_search'],$selectedcolumns_arr)){
                $this->primary_search = $widgetSearchBy['primary_search'];
                $selectedcolumns_arr[] = array("fieldcolname" => $this->primary_search, "selected" => "", "fieldlabel" => "",);
            }
        }
        // ITS4YOU-END

/* NEROZUMIEM PRECO JE TU TENTO KOD ... v tabular reportoch sposobi chybne naplnenenie stlpcov 2x
        if ($this->generate_type == "standard" && !empty($this->detail_selectedcolumns_arr)) {
            foreach ($this->detail_selectedcolumns_arr as $key => $col_arr_arr) {
                $selectedcolumns_arr[] = $col_arr_arr;
            }
        }
*/

        if($this->generate_for==true){
            $generateFor = ITS4YouReports::getSchedulerGenerateFor($this->report_obj->record);
            foreach ($generateFor as $key => $genForColStr) {
                $selectedcolumns_arr[] = array("fieldcolname" => $genForColStr, "selected" => "", "fieldlabel" => "",);
            }
        }

        if(!empty($this->report_obj->reportinformations['quick_filters'])) {
            foreach ($this->report_obj->reportinformations['quick_filters'] as $qfColumn) {
                if ($this->report_obj->in_multiarray($qfColumn, $selectedcolumns_arr, "fieldcolname") !== true) {
                    $selectedcolumns_arr[] = array("fieldcolname" => $qfColumn, "selected" => "", "fieldlabel" => "",);
                }
            }
        }

        if ('ApiMaps' === $generate_type && !empty($this->report_obj->reportinformations['maps'])) {
            foreach ($this->report_obj->reportinformations['maps'] as $mi => $mapColumn) {
                if (!in_array($mi, ITS4YouReports_BingMaps_View::$mapParameterColumns) && true !== $this->report_obj->in_multiarray($mapColumn, $selectedcolumns_arr, 'fieldcolname')) {
                    $selectedcolumns_arr[] = array('fieldcolname' => $mapColumn, 'selected' => '', 'fieldlabel' => '',);
                }
            }
        }

        $join_array = Array();
        $columns_array = Array();
        require_once("modules/ITS4YouReports/classes/UIFactory.php");
        $this->populateQueryInformations($selectedcolumns_arr, $join_array, $columns_array);

        if (!isset($this->groupslist) || empty($this->groupslist)) {
            $this->groupslist = $this->getGroupingList();
        }

        $this->report_id = $this->report_obj->record;
        $this->join_array = $join_array;
        $this->columns_array = $columns_array;

        /*         * * QUERY CREATING START ** */
        if ($qf_field != "") {
            $c_columns[] = $qf_field;
            foreach ($this->groupslist as $key => $value) {
                if (isset($columns_array[$key]["fld_sql_str"])) {
                    $c_columns[] = $columns_array[$key]["fld_sql_str"];
                }
            }
            $c_columns = implode(",", $c_columns);
        } else {
            $c_columns = $this->getColumnsStrToQuery($columns_array);
        }

// global $inventory_modules;
        if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)) {
            $group_by_currency_sql = $this->getCurrencyFieldSql($primary_focus->table_name);
            $this->group_by_currency_sql = $group_by_currency_sql;
            $c_columns .= $group_by_currency_sql;
        }
        /* NEED TO BE FINISHED
        if(isset($this->columns_array["relatedCurrencyTables"]) && !empty($this->columns_array["relatedCurrencyTables"])){
            foreach($this->columns_array["relatedCurrencyTables"] as $rel_currency_arr){
                $rel_group_by_currency_sql = $this->getCurrencyFieldSql($rel_currency_arr["table"],$rel_currency_arr["fieldid_alias"]);
                $rel_group_by_currency_arr = explode(" AS ",$rel_group_by_currency_sql);
                $this->rel_group_by_currency_sql = $rel_group_by_currency_arr[0];
                $c_columns .= $rel_group_by_currency_sql;

            }
        }*/

        $j_tables = $this->getJoinStrToQuery($join_array);

        $field_row = $adb->fetchByAssoc($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tabid=? AND columnname=? ", array($this->report_obj->primarymoduleid, "smownerid")), 0);
        $p_smownerid = $field_row["fieldid"];
        $s_sql = "SELECT DISTINCT 

 " . $c_columns . " ";
        if ($this->isentitytype == "1") {
            $s_sql .= " , vtiger_crmentity.crmid AS record_id ";
        }
        $j_sql = "

FROM " . $primary_focus->table_name . " ";

        if ($this->isentitytype == "1") {
            $j_sql .= "
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = " . $primary_focus->table_name . "." . $primary_focus->table_index . " AND vtiger_crmentity.deleted=0 ";
        }
        if(isset($this->schedule_partners) && $this->schedule_partners==true){
            $primary_focus->schedule_partners = $this->schedule_partners;
        }
        if(method_exists($primary_focus, 'getNonAdminAccessControlQuery')){
            $non_admin_sql = $primary_focus->getNonAdminAccessControlQuery($this->report_obj->primarymodule, $current_user);
        }
        $j_sql .= $non_admin_sql;

        if ($this->isentitytype == "1") {
            if (!array_key_exists(" vtiger_users AS vtiger_users_$p_smownerid ", $join_array)) {
                $j_sql .= " LEFT JOIN vtiger_users AS vtiger_users_$p_smownerid ON vtiger_users_$p_smownerid.id = vtiger_crmentity.smownerid ";
            }
            if (!array_key_exists(" vtiger_groups AS vtiger_groups_$p_smownerid ", $join_array)) {
                $j_sql .= " LEFT JOIN vtiger_groups AS vtiger_groups_$p_smownerid ON vtiger_groups_$p_smownerid.groupid = vtiger_crmentity.smownerid ";
            }
        }

        $j_sql .= " $j_tables ";

        if (!empty($this->modules_to_access_query) && $this->generate_type!="grouping") {
            foreach ($this->modules_to_access_query as $q_a_module => $q_a_scope) {
                if(array_key_exists(" vtiger_crmentity AS vtiger_crmentity$q_a_scope ",$this->join_array)){
                    $j_sql .= $primary_focus->getNonAdminAccessControlQuery($q_a_module, $current_user, $q_a_scope);
                }
            }
        }

        $stdfilterlist = $this->getStdFilterList($this->report_obj->record, $join_array);
        if (isset($stdfilterlist) && $stdfilterlist != "") {
            $conditions[] = $stdfilterlist;
        }

        /* $advfiltersql = $this->getAdvFilterSql($this->report_obj->record, $join_array);
          if (isset($advfiltersql) && $advfiltersql != "") {
          $conditions[] = $advfiltersql;
          }
          if (isset($_REQUEST["reload"])) {
          $advfiltersql_req = $this->getReqAdvFilterSql($this->report_obj->record, $join_array);
          }
          if (isset($advfiltersql_req) && $advfiltersql_req != "") {
          $conditions[] = $advfiltersql_req;
          } */
        $default_charset = vglobal("default_charset");
        if ($request->has("reload") || $request->get("mode")=="GetXLS") {
            $advfiltersql = $this->getReqAdvFilterSql($this->report_obj->record, $join_array);
//ITS4YouReports::sshow(1);
        } else {
            $advfiltersql = $this->getAdvFilterSql($this->report_obj->record, $join_array);
            $advfiltersql = html_entity_decode($advfiltersql, ENT_QUOTES, $default_charset);
//ITS4YouReports::sshow(2);
        }

        if (isset($advfiltersql) && $advfiltersql != "") {
            $conditions[] = $advfiltersql;
        }

// standard date filter for generated report
        if (isset($_REQUEST["stdDateFilterField"]) && $_REQUEST["stdDateFilterField"] != "") {
            if (isset($this->columns_array[$_REQUEST["stdDateFilterField"]]["fld_alias"]) && $this->columns_array[$_REQUEST["stdDateFilterField"]]["fld_alias"] != "") {
                $stdDateFilterField = $this->columns_array[$_REQUEST["stdDateFilterField"]]["fld_alias"];
                if (isset($_REQUEST["startdate"]) && $_REQUEST["startdate"] != "" && isset($_REQUEST["enddate"]) && $_REQUEST["enddate"] != "") {
                    $conditions[] = " ($stdDateFilterField BETWEEN '" . vtlib_purify($_REQUEST["startdate"]) . "' AND '" . vtlib_purify($_REQUEST["enddate"]) . "' ) ";
                }
            }
        }

        $qfConditions = $this->getQFConditions($request);
        $conditions = $this->getUserValuesConditions($conditions, $selectedcolumns_arr);

        // ITS4YOU-CR SlOl 10. 3. 2016 13:01:32
        if($request->has('primarySearchBy') && $request->get('primarySearchBy')!="" && $request->get('primarySearchBy')!="all"){
            if(isset($this->columns_array[$this->primary_search])){
                if(is_array($request->get('primarySearchBy'))) {
                    $primarySearchBy = implode("','", $request->get('primarySearchBy'));
                } else {
                    $primarySearchBy = $request->get('primarySearchBy');
                }
                $primarySearchBy = trim($primarySearchBy);
                $conditions[] = $this->columns_array[$this->primary_search]['fld_cond']." IN ('".$primarySearchBy."')";
        //        ITS4YouReports::sshow($conditions);
            }
        }
        // ITS4YOU-END

        if(!empty($this->mifEntityTables)) {
            $relatedCrmConditions = [];
            foreach ($this->mifEntityTables as $crmJaliasString) {
                $crmJaliasArray = explode(' AS ', $crmJaliasString);
                $crmJalias = $crmJaliasArray[1];
                $relatedCrmConditions[] = '(' . $crmJalias . '.deleted IS NULL OR ' . $crmJalias . '.deleted=0)';
            }
            $conditions[] = '(' . implode(' OR ', $relatedCrmConditions) . ')';

        }
		if (!empty($conditions)) {
            $ft_sql = " 
WHERE " . implode(" AND ", $conditions);
        }
        $qf_sql = '';
        if(!empty($qfConditions)) {
            if (!empty($conditions)) {
                $qfOperator = 'AND';
            }else{
                $qfOperator = 'WHERE';
            }
            $qf_sql = ' ' . $qfOperator . ' ' . implode(' AND ', $qfConditions);
            $this->userQuickFilterQuery = true;
        }

        $g_o_array = $this->getGOSQL($this->groupslist, $primary_focus->table_name);
        $this->g_o_array = $g_o_array;
        $group_by_sql_arr = $g_o_array["group_by_sql"];
        $order_by_sql = $g_o_array["order_by_sql"];
        // SUMMARIES CRITERIA START
        $summaries_conditions = $this->getSummariesConditions($this->report_obj);
        // SUMMARIES CRITERIA END

        if (trim($order_by_sql) != "") {
            $order_by_sql = " ORDER BY " . $order_by_sql;
        }

        // tf sql without group by for details
        $gb_sql = "";
        $gj_sql = $j_sql . $ft_sql;
        $f_sql = $s_sql . $gj_sql;

        $order_by_sql_Array = array();
        if ($this->generate_type == "grouping" && !empty($group_by_sql_arr)) {
            foreach ($group_by_sql_arr as $gkey => $gsql) {
                $group_by_sql = " GROUP BY " . $gsql;
                $gkeyStr = $gkey;
                $g_this = "group_sql_$gkeyStr";
                if ($summaries_conditions != "") {
                    $summaries_conditions_sql = " HAVING $summaries_conditions ";
                }
                // ITS4YOU-CR SlOl 24. 8. 2016 10:42:44
                $order_by_sql_arr = explode(", ", $order_by_sql);
                if(isset($order_by_sql_arr[$gkey])){
                    $order_by_sql_Array[] = $order_by_sql_arr[$gkey];
                }
                // ITS4YOU-END
                $gb_sql = $f_sql;
                $go_sql = $group_by_sql . $summaries_conditions_sql . implode(', ', $order_by_sql_Array);
                $this->$g_this = $gb_sql . $this->qf_sql . $go_sql;
                if ($gkey == 0) {
                    $go_sql = $group_by_sql . $summaries_conditions_sql . $order_by_sql_Array[0];
                    $tf_sql = $gb_sql . $this->qf_sql . $go_sql;
                }
            }
        } else {
            if (!empty($group_by_sql_arr)) {
                $group_by_sql = " GROUP BY " . $group_by_sql_arr[(count($group_by_sql_arr) - 1)];
            }
            if($this->totalVatSumGroup===true){
                if($group_by_sql==''){
                    $group_by_sql = ' GROUP BY ';
                }
                $group_by_sql .= ' vtiger_inventoryproductrel.id ';
            }
            $gb_sql = $f_sql;
            $go_sql = $group_by_sql . $order_by_sql;
            $tf_sql = $f_sql . $go_sql;
        }

//        LIMIT start
        $limit_sql = '';
        if($this->generate_for!=true){
            if ($generate_type == "standard" && isset($this->report_obj->reportinformations["columns_limit"]) && $this->report_obj->reportinformations["columns_limit"] > 0) {
                $limit_value = $this->report_obj->reportinformations["columns_limit"];
                $limit_sql = " LIMIT $limit_value ";
            }
            if ($generate_type == "grouping" && isset($this->report_obj->reportinformations["summaries_limit"]) && $this->report_obj->reportinformations["summaries_limit"] > 0) {
                $summaries_limit = $this->report_obj->reportinformations["summaries_limit"];
                $limit_sql = " LIMIT $summaries_limit ";
            }
            $tf_sql .= $limit_sql;
//        LIMIT end
        }

        $this->f_sql = $f_sql;
        // quick filters sql conditions
        $this->qf_sql = $qf_sql;
        // group and order sql part
        $this->gb_sql = $go_sql;
        // sql with quick filters
        $this->tf_qf_sql = $this->f_sql . $this->qf_sql . $this->gb_sql . $limit_sql;

        // final tabular sql
        $this->tf_sql = $tf_sql;
        if($this->generate_for==true){
            $this->gj_sql = $gj_sql;
        }
        $this->select_body = $gj_sql;

        $this->j_sql = $j_sql;
        $this->columns_array = $columns_array;
    }

    /** Function to get the Standard filter columns for the reportid
     *  This function accepts the $reportid datatype Integer
     *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
     * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
     * 				      	     )
     *
     */
    function getStdFilterList($reportid, $join_array) {
        $stdfilterlist = "";
        $adb = PEARDatabase::getInstance();

        if (isset($_REQUEST["stdDateFilterField"]) && $_REQUEST["stdDateFilterField"] != "" && isset($_REQUEST["startdate"]) && $_REQUEST["startdate"] != "" && (in_array($_REQUEST["stdDateFilter"], ITS4YouReports::$sp_date_options) || isset($_REQUEST["enddate"]) && $_REQUEST["enddate"] != "")) {
            $fieldcolname = vtlib_purify($_REQUEST["stdDateFilterField"]);
            $column_info = explode(":", $fieldcolname);
            $startdate = vtlib_purify($_REQUEST["startdate"]);
            $stemp_val = explode(",", $startdate);
            $sval = Array();
            if (!empty($stemp_val)) {
                for ($x = 0; $x < count($stemp_val); $x++) {
                    if (trim($stemp_val[$x]) != '') {
                        $date = new DateTimeField(trim($stemp_val[$x]));
                        /*
                          if($column_info[4] == 'D') { */
                        $sval[$x] = DateTimeField::convertToDBFormat(trim($stemp_val[$x]));
                        /* } elseif($column_info[4] == 'DT') {
                          $sval[$x] = $date->getDBInsertDateTimeValue();
                          } else {
                          $sval[$x] = $date->getDBInsertTimeValue();
                          } */
                    }
                }
            }
            $startdate = implode(",", $sval);
            $enddate = vtlib_purify($_REQUEST["enddate"]);
            $etemp_val = explode(",", $enddate);
            $eval = Array();
            if (!empty($etemp_val)) {
                for ($x = 0; $x < count($etemp_val); $x++) {
                    if (trim($etemp_val[$x]) != '') {
                        $date = new DateTimeField(trim($etemp_val[$x]));
                        /*
                          if($column_info[4] == 'D') {
                         */
                        $eval[$x] = DateTimeField::convertToDBFormat(trim($etemp_val[$x]));
                        /* } elseif($column_info[4] == 'DT') {

                          $eval[$x] = $date->getDBInsertDateTimeValue();
                          } else {
                          $eval[$x] = $date->getDBInsertTimeValue();
                          } */
                    }
                }
            }
            $enddate = implode(",", $eval);
            $stdfilterrow["datecolumnname"] = $fieldcolname;
            $stdfilterrow["datefilter"] = vtlib_purify($_REQUEST["stdDateFilter"]);
            $stdfilterrow["startdate"] = $startdate;
            $stdfilterrow["enddate"] = $enddate;
        } else {
            $stdfiltersql = "select  its4you_reports4you_datefilter.* from  its4you_reports4you";
            $stdfiltersql .= " inner join  its4you_reports4you_datefilter on  its4you_reports4you.reports4youid =  its4you_reports4you_datefilter.datefilterid";
            $stdfiltersql .= " where  its4you_reports4you.reports4youid = ?";

            $result = $adb->pquery($stdfiltersql, array($reportid));
            $stdfilterrow = $adb->fetch_array($result);
        }

        if (isset($stdfilterrow)) {
            $fieldcolname = $stdfilterrow["datecolumnname"];
            $datefilter = $stdfilterrow["datefilter"];
            $startdate = $stdfilterrow["startdate"];
            $enddate = $stdfilterrow["enddate"];

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
                    $selectedfields[0] = "vtiger_crmentity";

                // ITS4YOU-UP SlOl 9. 1. 2014 13:44:18
                $last_key = count($selectedfields) - 1;
                $exploded_name = explode("_", $selectedfields[3]);
                $field_module_id = getTabid($exploded_name[0]);
                if (is_numeric($selectedfields[$last_key])) {
                    $selected_field_col = trim($selectedfields[0], $selectedfields[$last_key]) . "_" . $selectedfields[$last_key] . "." . $selectedfields[1];
                } elseif ($selectedfields[$last_key] == "MIF") {
                    $alias = "_mif_" . $field_module_id;
                    $selected_field_col = trim($selectedfields[0], "MIF") . $alias . "." . $selectedfields[1];
                } else {
                    $tablename = $selectedfields[0];
                    $columnname = $selectedfields[1];
                    $field_row = $adb->fetchByAssoc($adb->pquery("SELECT uitype FROM vtiger_field WHERE columnname=? AND tabid=?", array($columnname, $field_module_id)), 0);
                    if (!empty($field_row)) {
                        $field_uitype = $field_row["uitype"];
                        $uifactory = new UIFactory($field_row);
                        $selected_field_col = $uifactory->getSelectedFieldCol($field_uitype, $selectedfields);
                    } else {
                        $selected_field_col = $selectedfields[0] . "." . $selectedfields[1];
                    }
                }
                // ITS4YOU-END 9. 1. 2014 13:44:22
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        // $stdfilterlist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." between '".$startdate." 00:00:00' and '".$enddate." 23:59:59'";
                        $stdfilterlist = $selected_field_col . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:59'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        // $stdfilterlist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." between '".$startenddate[0]." 00:00:00' and '".$startenddate[1]." 23:59:59'";
                        $stdfilterlist = $selected_field_col . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:59'";
                    }
                    if (in_array($datefilter, ITS4YouReports::$sp_date_options)) {
                        switch ($datefilter) {
                            case "todaymore":
                                $stdfilterlist = $selected_field_col . " > '" . $startenddate[0] . " 23:59:59'";
                                break;
                            default:
                                $stdfilterlist = $selected_field_col . " < '" . $startenddate[0] . " 00:00:00'";
                                break;
                        }
                    }
                }
            }
        }
        // Save the information
        // $this->_stdfilterlist = $stdfilterlist;
        return $stdfilterlist;
    }

    /** Function to get the advanced filter columns for the reportid
     *  This function accepts the $reportid
     *  This function returns  $columnslist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
     * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
     * 					      					|
     * 					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen filtercriteria
     * 				      	     )
     *
     */
    function getAdvFilterSql($reportid, $join_array) {
        $adb = PEARDatabase::getInstance();
        require_once("modules/ITS4YouReports/classes/UIFactory.php");

        $advfiltersql = "";

        $advfiltergroupssql = "SELECT * FROM its4you_reports4you_relcriteria_grouping WHERE queryid = ? and groupid != 0 ORDER BY groupid";
        $advfiltergroups = $adb->pquery($advfiltergroupssql, array($reportid));
        $numgrouprows = $adb->num_rows($advfiltergroups);

        // ITS4YOU-CR SlOl 28. 3. 2014 10:34:19 to get select options array
        $ITS4YouReports = $this->report_obj;
        // ADV FILTER START
        $ITS4YouReports->getPriModuleColumnsList($ITS4YouReports->primarymodule);
        if (!empty($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule])) {
            foreach ($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule] as $key => $value) {
                $secondarymodules[] = $value["id"];
            }
            $secondary_modules_str = implode(":", $secondarymodules);
        }
        $ITS4YouReports->getSecModuleColumnsList($secondary_modules_str);
        $sel_fields = $ITS4YouReports->adv_sel_fields;
        // ITS4YOU-END 28. 3. 2014 10:34:37
        $std_filter_columns = $this->report_obj->getStdFilterColumns();

        $groupctr = 0;
        while ($advfiltergroup = $adb->fetch_array($advfiltergroups)) {
            $groupctr++;
            $groupid = $advfiltergroup["groupid"];
            $groupcondition = $advfiltergroup["group_condition"];

            $advfiltercolumnssql = "select its4you_reports4you_relcriteria.* from  its4you_reports4you";
            $advfiltercolumnssql .= " inner join  its4you_reports4you_selectquery on  its4you_reports4you_selectquery.queryid =  its4you_reports4you.reports4youid";
            $advfiltercolumnssql .= " left join its4you_reports4you_relcriteria on its4you_reports4you_relcriteria.queryid =  its4you_reports4you_selectquery.queryid";
            $advfiltercolumnssql .= " where  its4you_reports4you.reports4youid = ? AND its4you_reports4you_relcriteria.groupid = ?";
            $advfiltercolumnssql .= " order by its4you_reports4you_relcriteria.columnindex";

//$adb->setDebug(true);
            $result = $adb->pquery($advfiltercolumnssql, array($reportid, $groupid));
//$adb->setDebug(false);
            $noofrows = $adb->num_rows($result);
            if ($noofrows > 0) {
                $advfiltergroupsql = "";
                $columnctr = 0;
                while ($advfilterrow = $adb->fetch_array($result)) {
                    $columnctr++;
                    $add_tags = true;
                    $fieldcolname = $advfilterrow["columnname"];
                    $comparator = $advfilterrow["comparator"];
                    $value = $advfilterrow["value"];
                    $column_condition = $advfilterrow["column_condition"];
                    $this->advf_col_array[] = $fieldcolname;
                    $valuehdn = $advfilterrow["valuehdn"];
                    if($valuehdn!="" && !in_array($valuehdn, $this->advf_col_array)){
                        $this->is_hdn_fval = true;
                        $add_tags = false;
                        $this->advf_col_array[] = $valuehdn;
                    }
                    if ($this->generate_type == "grouping" && strpos($fieldcolname, "campaignrelstatus") !== false) {
                        continue;
                    }
                    if ($this->generate_type == "grouping" && strpos($fieldcolname, "access_count") !== false) {
                        continue;
                    }

                    if($valuehdn!="" && isset($this->columns_array[$valuehdn]["fld_cond"])){
                        $add_tags = false;
                        $value = $this->columns_array[$valuehdn]["fld_cond"];
                    }

                    if ($fieldcolname != "" && $comparator != "") {
                        $selectedfields = explode(":", $fieldcolname);
                        //Added to handle yes or no for checkbox  field in reports advance filters. -shahul
                        if ($selectedfields[4] == 'C') {
                            if (strcasecmp(trim($value), "yes") == 0)
                                $value = "1";
                            if (strcasecmp(trim($value), "no") == 0)
                                $value = "0";
                        }
                        $valuearray = explode(",", trim($value));
                        $datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";

                        // ITS4YOU-UP SlOl 9. 1. 2014 13:44:18
						if (in_array(html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset), $std_filter_columns) || in_array($fieldcolname, $std_filter_columns)) {
                            if (isset($this->columns_array[html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset)]["fld_cond"]) && $this->columns_array[html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset)]["fld_cond"] != "") {
                                $selected_field_col = $this->columns_array[html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset)]["fld_cond"];
                            } else {
                                $selected_field_col = $selectedfields[0] . "." . $selectedfields[1];
                            }
                            if($comparator=="isn"){
                                $advcolumnsql = " (".$selected_field_col . " IS NULL OR ".$selected_field_col." = '') ";
                            }elseif($comparator=="isnn"){
                                $advcolumnsql = " (".$selected_field_col . " IS NOT NULL AND ".$selected_field_col." != '') ";
                            }elseif(in_array($comparator, ITS4YouReports::$fld_date_options)){
                                $advcolumnsql = " DATE($selected_field_col) " . $this->getStdComparator($comparator, trim($value));
                            }else{
                                $advcolumnsql = $selected_field_col . " " . $this->getStdComparator($comparator, trim($value));
                            }
                            $advfiltergroupsql .= " " . $advcolumnsql . " ";
                        } elseif (array_key_exists(html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset), $sel_fields) && isset($valuearray)) {
                            if($selectedfields[3]=="assigned_user_id" && in_array("Current User",$valuearray)){
                                $valuearray = $this->getConditionCurrentUserName($valuearray);
                            }
                            if(in_array($comparator,array("e","n",))){
                                if($add_tags==true){
                                    $value = "('".implode("','", $valuearray)."')";
                                }else{
                                    $value = "(".implode(",", $valuearray).")";
                                }
                            }

                            $advcolumnsql = $this->getSelFieldsWhereSQL($fieldcolname,$comparator,$value,$selectedfields,$add_tags);
							$advfiltergroupsql .= $advcolumnsql;
                        } else {
                            $last_key = count($selectedfields) - 1;
                            $exploded_name = explode("_", $selectedfields[2]);
                            $field_module_id = getTabid($exploded_name[0]);
                            if (is_numeric($selectedfields[$last_key])) {
                                $tablename = $selectedfields[0];
                                $fieldname = $selectedfields[3];
                                $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?", array($fieldname, $field_module_id)), 0);

                                $selected_field_col_table = trim($selectedfields[0], $selectedfields[$last_key]);
                                if (array_key_exists(" $selected_field_col_table AS " . $selected_field_col_table . "_" . $field_row["fieldid"] . " ", $join_array)) {
                                    $selected_field_col = $selected_field_col_table . "_" . $field_row["fieldid"] . "." . $selectedfields[1];
                                } elseif (array_key_exists(" $selected_field_col_table AS " . $selected_field_col_table . "_" . $selectedfields[$last_key] . " ", $join_array)) {
                                    $selected_field_col = $selected_field_col_table . "_" . $selectedfields[$last_key] . "." . $selectedfields[1];
                                }
                            } elseif ($selectedfields[$last_key] == "MIF") {
                                $alias = "mif_" . $field_module_id;
                                $selected_field_col = trim($selectedfields[0], "MIF") . $alias . "." . $selectedfields[1];
                            } else {
                                $tablename = $selectedfields[0];
                                $fieldname = $selectedfields[3];
                                $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?", array($fieldname, $field_module_id)), 0);
                                if ($tablename == "vtiger_inventoryproductrel") {
                                    $field_uitype = "INV";
                                    $params = array("fieldid" => $selectedfields[$last_key],
                                    );
                                    $uifactory = new UIFactory($field_row);
                                    $selected_field_col = $uifactory->getSelectedFieldCol($field_uitype, $selectedfields);
                                } elseif (!empty($field_row)) {
                                    $field_uitype = $field_row["uitype"];
                                    $uifactory = new UIFactory($field_row);
                                    $selected_field_col = $uifactory->getSelectedFieldCol($field_uitype, $selectedfields);
                                } elseif (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                                    $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
                                } else {
                                    $selected_field_col = $selectedfields[0] . "." . $selectedfields[1];
                                }
                            }
                            // ITS4YOU-END 9. 1. 2014 13:44:22
                            if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                                $filed_col = $this->columns_array[$fieldcolname]["fld_cond"];
                            } else {
                                $filed_col = $selected_field_col;
                            }
                            if($filed_col != "" && in_array($comparator, array('isn','isnn'))){
                                if($comparator=="isn"){
                                    $fieldvalue = " (".$filed_col . " IS NULL OR ".$filed_col." = '') ";
                                }else{
                                    $fieldvalue = " (".$filed_col . " IS NOT NULL AND ".$filed_col." != '') ";
                                }
                            }else{
                                $fieldvalue = $filed_col . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }

                            $advfiltergroupsql .= $fieldvalue;
                        }
                        if ($column_condition != NULL && $column_condition != '' && $noofrows > $columnctr) {
                            $advfiltergroupsql .= ' ' . $column_condition . ' ';
                        }
                    }
                }
                if (trim($advfiltergroupsql) != "") {
                    $advfiltergroupsql = "( $advfiltergroupsql ) ";
                    if ($groupcondition != NULL && $groupcondition != '' && $numgrouprows > $groupctr) {
                        $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                    }
                    $advfiltersql .= $advfiltergroupsql;
                }
            }
        }
        if (trim($advfiltersql) != "")
            $advfiltersql = '(' . $advfiltersql . ')';
        return $advfiltersql;
    }

    function getReqConditions($sel_fields) {
        $conditions = array();
        $this->advf_col_array = array();
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
        //<<<<<<<advancedfilter>>>>>>>>

        if (!empty($advft_criteria)) {
            foreach ($advft_criteria as $f_fol_i => $condition_array) {
                if($condition_array["columnname"]!=""){
                    $is_sbox_item = "V";

                    list($c_tablename, $c_colname, $c_module_field, $c_fieldname, $c_typeofdata) = explode(":", $condition_array["columnname"]);

                    if (array_key_exists(trim($condition_array["columnname"]), $sel_fields) && in_array($condition_array["comparator"],array("e","n"))) {

                        if ('C' === $c_typeofdata) {
							$sbox_vals = array(($condition_array["value"]==='yes'?1:0));
						} else {
                        	$sbox_vals = $condition_array["value"];
                        }
                        $condition_array["value"] = "('" . implode("','", $sbox_vals) . "')";
                        $is_sbox_item = "S";
                    }
                    $this->advf_col_array[] = $condition_array["columnname"];
                    // ITS4YOU-CR SlOl 30. 5. 2016 9:08:00
                    if($condition_array["value_hdn"]!=""){
                        $this->advf_col_array[] = $condition_array["value_hdn"];
                    }
                    // ITS4YOU-END

                    $f_value = $condition_array["value"];
                    $skipTimeFields = [
                        'createdtime',
                        'modifiedtime',
                    ];

                    if($c_typeofdata=="T" && !in_array($c_fieldname, $skipTimeFields)){
                        $checked = false;
                        if (preg_match('/^\d{2}:\d{2}$/', $f_value)) {
                            if (preg_match("/(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9])/", $f_value)) {
                                $checked = true;
                            }
                        }
                        $today = date("Y-m-d");
                        if($checked){
                            $f_value_long = Vtiger_Datetime_UIType::getDBDateTimeValue($today.' '.$f_value);
                            $f_value_arr = explode(' ', $f_value_long);
                            $f_value = $f_value_arr[1];
                        }else{
                            $f_value = date("H:i");
                        }
                    }

                    $conditions[$condition_array["groupid"]][$f_fol_i] = array("groupid" => $condition_array["groupid"],
                        "fcol$f_fol_i" => $condition_array["columnname"],
                        "fop$f_fol_i" => $condition_array["comparator"],
                        "fval$f_fol_i" => $f_value,
                        "hdn_fval$f_fol_i" => $condition_array["value_hdn"],
                        "fcon$f_fol_i" => $condition_array["column_condition"],
                        "gpcon$f_fol_i" => $advft_criteria_groups[$condition_array["groupid"]]["groupcondition"],
                        "dkey" => $f_fol_i,
                        "f_type$f_fol_i" => $is_sbox_item,
                        "from_req" => 1,);
                }
            }
        }

        return $conditions;
    }

    // ITS4YOU-CR SlOl 3. 3. 2014 12:04:10
    function getReqAdvFilterSql($reportid, $join_array) {
        $adb = PEARDatabase::getInstance();

        global $default_charset;

        $conditions = array();
        $advfiltersql = "";

        // ITS4YOU-CR SlOl 28. 3. 2014 10:34:19 to get select options array
        // $ITS4YouReports = new ITS4YouReports($record);
        $ITS4YouReports = $this->report_obj;
        // ADV FILTER START
        $ITS4YouReports->getPriModuleColumnsList($ITS4YouReports->primarymodule);
        if (!empty($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule])) {
            foreach ($ITS4YouReports->related_modules[$ITS4YouReports->primarymodule] as $key => $value) {
                $secondarymodules[] = $value["id"];
            }
            $secondary_modules_str = implode(":", $secondarymodules);
        }
        $ITS4YouReports->getSecModuleColumnsList($secondary_modules_str);
        $sel_fields = $ITS4YouReports->adv_sel_fields;
        $conditions_rc = $this->getReqConditions($sel_fields);

        // ITS4YOU-END 28. 3. 2014 10:34:37
        $last_gid = $l_groupcondition = "";
        $std_filter_columns = $this->report_obj->getStdFilterColumns();

        foreach($std_filter_columns as $stdkey => $stdcol){
            $std_filter_columns[$stdkey] = html_entity_decode($stdcol, ENT_QUOTES, $default_charset);
        }

        foreach ($conditions_rc as $groupid => $conditions) {
            $adv_condition_sql = "";
            $d_i = 1;
            $d_num_rows = count($conditions);
            foreach ($conditions as $key => $d_conditions) {
                $advfiltergroupsql = "";
                $groupid = $d_conditions["groupid"];
                if ($last_gid == "") {
                    $last_gid = $groupid;
                }

                $dkey = $d_conditions["dkey"];
                $from_req = $d_conditions["from_req"];
                $column_condition = "";
                $fieldcolname = $d_conditions["fcol$dkey"];

                $comparator = $d_conditions["fop$dkey"];
                $value = $d_conditions["fval$dkey"];

                $this->is_hdn_fval = false;
                if (isset($d_conditions["fcon$dkey"])) {
                    $column_condition = $d_conditions["fcon$dkey"];
                }
                if (isset($d_conditions["gpcon$dkey"])) {
                    $groupcondition = $d_conditions["gpcon$dkey"];
                }
                if (isset($d_conditions["f_type$dkey"])) {
                    $f_type = $d_conditions["f_type$dkey"];
                }
                if (isset($d_conditions["hdn_fval$dkey"]) && in_array($comparator, ITS4YouReports::$fld_date_options)) {
                    $this->is_hdn_fval = true;
                    $hdn_fval = $d_conditions["hdn_fval$dkey"];
                }

                // ITS4YOU-CR SlOl 30. 5. 2016 9:12:33
                if($hdn_fval!=""){
                    if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                        $fld_cond = $this->columns_array[$fieldcolname]["fld_cond"];
                    } else {
                        $fld_arr = explode(":", $fieldcolname);
                        $fld_cond = $fld_arr[0] . "." . $fld_arr[1];
                    }
                    $compare_fld_cond = $this->columns_array[$hdn_fval]["fld_cond"];
                    if (in_array($comparator, ITS4YouReports::$fld_date_options)) {
                        $advfiltergroupsql .= " DATE($fld_cond) " . $this->getStdComparator($comparator, trim($compare_fld_cond), $datatype);
                    } else {
                        $advfiltergroupsql .= $fld_cond . " " . $this->getAdvComparator($comparator, trim($compare_fld_cond), $datatype);
                    }
                }elseif($fieldcolname != "" && ($comparator=="isn" || $comparator=="isnn")){
                // ITS4YOU-END
                    if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                        $fld_cond = $this->columns_array[$fieldcolname]["fld_cond"];
                    } else {
                        $fld_arr = explode(":", $fieldcolname);
                        $fld_cond = $fld_arr[0] . "." . $fld_arr[1];
                    }

                    if($comparator=="isn"){
                        $advfiltergroupsql .= " (".$fld_cond . " IS NULL OR ".$fld_cond." = '') ";
                    }else{
                         $advfiltergroupsql .= " (".$fld_cond . " IS NOT NULL AND ".$fld_cond." != '') ";
                    }
                }elseif ($fieldcolname != "" && $comparator != "" && $f_type != "S" && !in_array($fieldcolname, $std_filter_columns)) {
                    $selectedfields = explode(":", $fieldcolname);
                    //Added to handle yes or no for checkbox  field in reports advance filters. -shahul
                    if ($selectedfields[4] == 'C') {
                        if (strcasecmp(trim($value), "yes") == 0)
                            $value = "1";
                        if (strcasecmp(trim($value), "no") == 0)
                            $value = "0";
                    }

                    $valuearray = explode(",", trim($value));
                    $datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";

                    // ADV FILTER CONDITIONS START
                    if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
                        $advcolumnsql = "";
                        if ('inviteeid' === $this->columns_array[$fieldcolname]['fld_alias']) {
                            $colSqlStr = $this->columns_array[$fieldcolname]["fld_cond"];
                        } else {
                            $colSqlStr = $selectedfields[0] . "." . $selectedfields[1];
                        }
                        for ($n = 0; $n < count($valuearray); $n++) {
                            $advcolsql[] = $colSqlStr . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                        }
                        //If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
                        if ($comparator == 'n' || $comparator == 'k') {
                            $advcolumnsql = implode(" and ", $advcolsql);
                        } else {
                            $advcolumnsql = implode(" or ", $advcolsql);
                        }
                        $fieldvalue = " (" . $advcolumnsql . ") ";
                    }else {
                        // ITS4YOU-UP SlOl 9. 1. 2014 13:44:18
                        $last_key = count($selectedfields) - 1;
                        $exploded_name = explode("_", $selectedfields[2]);
                        $field_module_id = getTabid($exploded_name[0]);
                        if (is_numeric($selectedfields[$last_key])) {
                            $tablename = $selectedfields[0];
                            $fieldname = $selectedfields[3];
                            $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?", array($fieldname, $field_module_id)), 0);

                            $selected_field_col_table = trim($selectedfields[0], "_".$selectedfields[$last_key]);
                            // ITS4YOU-UP SlOl 22. 12. 2016 6:08:56
                            if (isset($this->columns_array[$fieldcolname]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                                $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
                            // ITS4YOU-END
                            } elseif (array_key_exists(" $selected_field_col_table AS " . $selected_field_col_table . "_" . $field_row["fieldid"] . " ", $join_array)) {
                                $selected_field_col = $selected_field_col_table . "_" . $field_row["fieldid"] . "." . $selectedfields[1];
                            } elseif (array_key_exists(" $selected_field_col_table AS " . $selected_field_col_table . "_" . $selectedfields[$last_key] . " ", $join_array)) {
                                $selected_field_col = $selected_field_col_table . "_" . $selectedfields[$last_key] . "." . $selectedfields[1];
                            }
                        } elseif (isset($this->columns_array[$fieldcolname]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                            $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
                        } elseif ($selectedfields[$last_key] == "MIF") {
                            $alias = "mif_" . $field_module_id;
                            $selected_field_col = trim($selectedfields[0], "MIF") . $alias . "." . $selectedfields[1];
                        } else {
                            $tablename = $selectedfields[0];
                            $fieldname = $selectedfields[3];
                            $field_row = $adb->fetchByAssoc($adb->pquery("SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?", array($fieldname, $field_module_id)), 0);
                            if ($tablename == "vtiger_inventoryproductrel") {
                                $field_uitype = "INV";
                                $params = array("fieldid" => $selectedfields[$last_key],
                                );
                                $uifactory = new UIFactory($field_row);
                                $selected_field_col = $uifactory->getSelectedFieldCol($field_uitype, $selectedfields);
                            } elseif (!empty($field_row)) {
                                $field_uitype = $field_row["uitype"];
                                //$uifactory = new UIFactory($field_row);
                                //$selected_field_col = $uifactory->getSelectedFieldCol($field_uitype, $selectedfields);
                                if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                                    $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
                                } else {
                                    $table_alias = $tablename . "_$field_uitype";
                                    $column_alias = $fieldname;
                                    $selected_field_col = $table_alias . "." . $column_alias;
                                }
                            } elseif (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                                $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
                            } else {
                                $selected_field_col = $selectedfields[0] . "." . $selectedfields[1];
                            }
                        }
                        // ITS4YOU-END 9. 1. 2014 13:44:22
                        $fieldvalue = $selected_field_col . $this->getAdvComparator($comparator, trim($value), $datatype);
                    }
                    // ADV FILTER CONDITIONS END
                    $advfiltergroupsql .= $fieldvalue;
                } elseif ($f_type == "S") {
                    $fld_arr = explode(":", $fieldcolname);

                    if($fld_arr[3]=="assigned_user_id" && strpos($value,"Current User") !== false){
                        $value = trim($value, "('");
                        $value = trim($value, "')");
                        $valuearray = explode("','", $value);
                        $valuearray = $this->getConditionCurrentUserName($valuearray);
                        $value = "('".implode("','", $valuearray)."')";
                    }

                    $advfiltergroupsql .= $this->getSelFieldsWhereSQL($fieldcolname,$comparator,$value,$fld_arr,$add_tags);

                    /*if ($comparator == "n") {
                        $advfiltergroupsql .= $this->columns_array[$fieldcolname]["fld_cond"] . " NOT IN " . $value;
                    } else {
                        $advfiltergroupsql .= $this->columns_array[$fieldcolname]["fld_cond"] . " IN " . $value;
                    }*/
                } elseif (in_array($fieldcolname, $std_filter_columns)) {
                    $column_info = explode(":", $fieldcolname);
                    if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                        $fld_cond = $this->columns_array[$fieldcolname]["fld_cond"];
                    } else {
                        $fld_arr = explode(":", $fieldcolname);
                        $fld_cond = $fld_arr[0] . "." . $fld_arr[1];
                    }

			        if ('DT' === $column_info[4]) {
						$fld_cond = 'DATE('.$fld_cond.')';
					}
                    $advfiltergroupsql .= $fld_cond . " " . $this->getStdComparator($comparator, trim($value));

                } else {
                    $column_info = explode(":", $fieldcolname);
                    $temp_val = explode(",", $value);

                    if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $value != '' ) && !in_array($fieldcolname, $std_filter_columns)) {
                        $val = Array();
                        for ($x = 0; $x < count($temp_val); $x++) {
                            if (trim($temp_val[$x]) != '') {
                                $date = new DateTimeField(trim($temp_val[$x]));
                                if ($column_info[4] == 'D') {
                                    $val[$x] = DateTimeField::convertToDBFormat(trim($temp_val[$x]));
                                } elseif ($column_info[4] == 'DT') {
                                    $val[$x] = $date->getDBInsertDateTimeValue();
                                } else {
                                    $val[$x] = $date->getDBInsertTimeValue();
                                }
                            }
                        }
                        $value = implode(",", $val);
                    }
                    if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
                        if (in_array($fieldcolname, $std_filter_columns)) {
                            $advfiltergroupsql .= $this->columns_array[$fieldcolname]["fld_cond"] . " " . $this->getStdComparator($comparator, trim($value));
                        } else {
                            $advfiltergroupsql .= $this->columns_array[$fieldcolname]["fld_cond"] . " " . $this->getAdvComparator($comparator, trim($value));
                        }
                    } else {
                        $this->adv_fcol[] = $fieldcolname;
                        // $selectedfields = explode(":",$fieldcolname);
                        if (in_array($fieldcolname, $std_filter_columns)) {
                            $advfiltergroupsql .= $fieldcolname . " " . $this->getStdComparator($comparator, trim($value));
                        } else {
                            $advfiltergroupsql .= $fieldcolname . " " . $this->getAdvComparator($comparator, trim($value));
                        }
                    }
                }
                if (trim($advfiltergroupsql) != "") {
                    //$advfiltergroupsql = "( $advfiltergroupsql ) ";
                    if ($column_condition != NULL && $column_condition != '' && $d_i < $d_num_rows) {
                        $advfiltergroupsql .= ' ' . $column_condition . ' ';
                    }
                /*
                    if ($l_groupcondition != NULL && $l_groupcondition != '' && $groupid != $last_gid) {
                        $advfiltergroupsql = ' ' . $l_groupcondition . ' ' . $advfiltergroupsql;
                        $l_groupcondition = $groupcondition;
                        $last_gid = $groupid;
                    } else {
                        $l_groupcondition = $groupcondition;
                        $last_gid = $groupid;
                    }
                */
                    $l_groupcondition = $groupcondition;
                    $last_gid = $groupid;
                    $adv_condition_sql .= $advfiltergroupsql;
                }
                $d_i++;
            }
            $advfiltersql .= " ( $adv_condition_sql ) $l_groupcondition ";
        }
        // quick fix more and/or
        $advfiltersql = trim($advfiltersql, ' and ');
        $advfiltersql = trim($advfiltersql, ' or ');

        if (trim($advfiltersql) != ""){
            $advfiltersql = '(' . $advfiltersql . ')';
        }
        // Save the information
        return $advfiltersql;
    }

    // ITS4YOU-END 3. 3. 2014 12:04:12

    function getDateNDaysInterval($type,$value){
        $datevalue = array("","");
        $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $value = round($value);
        if($type!='daysago' && $type!='daysmore'){
            $value = ($value-1);
        }
        switch($type){
            case "olderNdays":
                $olderNdays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $value, date("Y")));
                $datevalue[0] = "";
                $datevalue[1] = $olderNdays;
            break;
            case "lastNdays":
                $lastNdays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $value, date("Y")));
                $datevalue[0] = $lastNdays;
                $datevalue[1] = $today;
            break;
            case "nextNdays":
                $nextNdays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $value, date("Y")));
                $datevalue[0] = $today;
                $datevalue[1] = $nextNdays;
            break;
            case "moreNdays":
                $moreNdays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $value, date("Y")));
                $datevalue[0] = $moreNdays;
                $datevalue[1] = "";
            break;
            case "daysago":
                $daysago = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $value, date("Y")));
                $datevalue[0] = $daysago;
                $datevalue[1] = $daysago;
            break;
            case "daysmore":
                $daysmore = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $value, date("Y")));
                $datevalue[0] = $daysmore;
                $datevalue[1] = $daysmore;
            break;
        }
        return $datevalue;
    }

    // ITS4YOU-CR SlOl 30. 11. 2015 9:56:05
    public function getDBDateFormat($value,$db_time_zone='', $time_zone=''){
        if($db_time_zone==''){
            global $current_user;
            $db_time_zone = $current_user->time_zone;
        }
        if($time_zone==''){
            $time_zone = DateTimeField::getDBTimeZone();
        }

        // ITS4YOU-CR SlOl 11. 3. 2016 14:10:43
        $now_time_His = date("H:i:s");
        $date = DateTimeField::convertTimeZone($value." ".$now_time_His, $db_time_zone, $time_zone);
        // ITS4YOU-END
        return $date->format('Y-m-d');

    }
    // ITS4YOU-END

    /** Function to get standardfilter startdate and enddate for the given type
     *  @ param $type : Type String
     *  returns the $datevalue Array in the given format
     * 		$datevalue = Array(0=>$startdate,1=>$enddate)
     */
    function getStandarFiltersStartAndEndDate($type,$getDBFormat=false) {
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

        if ($type == "today") {

            $datevalue[0] = $today;
            $datevalue[1] = $today;
        } elseif ($type == "yesterday") {

            $datevalue[0] = $yesterday;
            $datevalue[1] = $yesterday;
        } elseif ($type == "tomorrow") {

            $datevalue[0] = $tomorrow;
            $datevalue[1] = $tomorrow;
        } elseif ($type == "thisweek") {

            $datevalue[0] = $thisweek0;
            $datevalue[1] = $thisweek1;
        } elseif ($type == "lastweek") {

            $datevalue[0] = $lastweek0;
            $datevalue[1] = $lastweek1;
        } elseif ($type == "nextweek") {

            $datevalue[0] = $nextweek0;
            $datevalue[1] = $nextweek1;
        } elseif ($type == "thismonth") {

            $datevalue[0] = $currentmonth0;
            $datevalue[1] = $currentmonth1;
        } elseif ($type == "lastmonth") {

            $datevalue[0] = $lastmonth0;
            $datevalue[1] = $lastmonth1;
        } elseif ($type == "nextmonth") {

            $datevalue[0] = $nextmonth0;
            $datevalue[1] = $nextmonth1;
        } elseif ($type == "next7days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next7days;
        } elseif ($type == "next15days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next15days;
        } elseif ($type == "next30days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next30days;
        } elseif ($type == "next60days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next60days;
        } elseif ($type == "next90days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next90days;
        } elseif ($type == "next120days") {

            $datevalue[0] = $today;
            $datevalue[1] = $next120days;
        } elseif ($type == "last7days") {

            $datevalue[0] = $last7days;
            $datevalue[1] = $today;
        } elseif ($type == "last15days") {

            $datevalue[0] = $last15days;
            $datevalue[1] = $today;
        } elseif ($type == "last30days") {

            $datevalue[0] = $last30days;
            $datevalue[1] = $today;
        } elseif ($type == "last60days") {

            $datevalue[0] = $last60days;
            $datevalue[1] = $today;
        } else if ($type == "last90days") {

            $datevalue[0] = $last90days;
            $datevalue[1] = $today;
        } elseif ($type == "last120days") {

            $datevalue[0] = $last120days;
            $datevalue[1] = $today;
        } elseif ($type == "thisfy") {

            $datevalue[0] = $currentFY0;
            $datevalue[1] = $currentFY1;
        } elseif ($type == "prevfy") {

            $datevalue[0] = $lastFY0;
            $datevalue[1] = $lastFY1;
        } elseif ($type == "nextfy") {

            $datevalue[0] = $nextFY0;
            $datevalue[1] = $nextFY1;
        } elseif ($type == "nextfq") {

            $datevalue[0] = $nFq;
            $datevalue[1] = $nFq1;
        } elseif ($type == "prevfq") {

            $datevalue[0] = $pFq;
            $datevalue[1] = $pFq1;
        } elseif ($type == "thisfq") {
            $datevalue[0] = $cFq;
            $datevalue[1] = $cFq1;
        } else if ($type == "todaymore") {
            $datevalue[0] = $todaymore_start;
            $datevalue[1] = "";
        } else if ($type == "todayless") {
            $datevalue[0] = $todayless_end;
            $datevalue[1] = "";
        } else if ($type == "older1days") {
            $datevalue[0] = $older1days;
            $datevalue[1] = "";
        } else if ($type == "older7days") {
            $datevalue[0] = $older7days;
            $datevalue[1] = "";
        } else if ($type == "older15days") {
            $datevalue[0] = $older15days;
            $datevalue[1] = "";
        } else if ($type == "older30days") {
            $datevalue[0] = $older30days;
            $datevalue[1] = "";
        } else if ($type == "older60days") {
            $datevalue[0] = $older60days;
            $datevalue[1] = "";
        } else if ($type == "older90days") {
            $datevalue[0] = $older90days;
            $datevalue[1] = "";
        } else if ($type == "older120days") {
            $datevalue[0] = $older120days;
            $datevalue[1] = "";
        } else {
            $datevalue[0] = "";
            $datevalue[1] = "";
        }

        if($getDBFormat===true){
            if($datevalue[0]!=""){
                $datevalue[0] = GenerateObj::getDBDateFormat($datevalue[0]);
            }
            if($datevalue[1]!=""){
                $datevalue[1] = GenerateObj::getDBDateFormat($datevalue[1]);
            }
        }

        return $datevalue;
    }

    // ITS4YOU-CR SlOl | 23.7.2014 9:22
    public function getStdComparator($comparator, $value, $datatype = "") {
        $return = "";
        global $default_charset;
        if(in_array($comparator, ITS4YouReports::$dateNcomparators)){
            $date_array = $this->getDateNDaysInterval($comparator,$value);
        }elseif ($comparator != "custom") {
            $date_array = GenerateObj::getStandarFiltersStartAndEndDate($comparator,true);
        } else {
            $date_array = explode("<;@STDV@;>", html_entity_decode($value, ENT_QUOTES, $default_charset));
            if (count($date_array) < 2) {
            	$date_array = explode('&lt;;@STDV@;&gt;', html_entity_decode($value, ENT_QUOTES, $default_charset));
			}
        }

        $s_date = $date_array[0];
        $e_date = $date_array[1];

        switch ($comparator) {
            // ITS4YOU-CR SlOl 20. 11. 2015 14:41:09
            case "olderNdays":
                $return = " < '$e_date' ";
                break;
            case "lastNdays":
                $return = " BETWEEN '$s_date' AND '$e_date' ";
                break;
            case "nextNdays":
                $return = " BETWEEN '$s_date' AND '$e_date' ";
                break;
            case "moreNdays":
                $return = " > '$s_date' ";
                break;
            case "daysago":
                $return = " = '$s_date' ";
                break;
            case "daysmore":
                $return = " = '$s_date' ";
                break;
            // ITS4YOU-END
            case "today":
            case "yesterday":
            case "tomorrow":
                $return = " = '$s_date' ";
                break;
            case "thisweek":
            case "lastweek":
            case "nextweek":
            case "thismonth":
            case "lastmonth":
            case "nextmonth":
            case "next7days":
            case "next15days":
            case "next30days":
            case "next60days":
            case "next90days":
            case "next120days":
            case "last7days":
            case "last15days":
            case "last30days":
            case "last60days":
            case "last90days":
            case "last120days":
            case "thisfy":
            case "prevfy":
            case "nextfy":
            case "nextfq":
            case "prevfq":
            case "thisfq":
                $return = " BETWEEN '$s_date' AND '$e_date' ";
                break;
            case "todaymore":
                $return = " > '$s_date' ";
                break;
            case "todayless":
            case "older1days":
            case "older7days":
            case "older15days":
            case "older30days":
            case "older60days":
            case "older90days":
            case "older120days":
            case "todayless":
                $return = " < '$s_date' ";
                break;
            // ITS4YOU-CR SlOl 9. 6. 2016 6:27:54
            case "af":
                $return = " = DATE($value) ";
                break;
            case "nf":
                $return = " != DATE($value) ";
                break;
            case "lf":
                $return = " < DATE($value) ";
                break;
            case "gf":
                $return = " > DATE($value) ";
                break;
            case "mf":
                $return = " <= DATE($value) ";
                break;
            case "hf":
                $return = " >= DATE($value) ";
                break;
            // ITS4YOU-END
            default :
                $currentUser = Users_Record_Model::getCurrentUserModel();
                $js_cal_dateformat = $currentUser->get('date_format');
                $reg_expr_dot = '/\./';
                if (preg_match_all($reg_expr_dot, $s_date) != false) {
                    $sd_arr = explode(".", $s_date);
                } else {
                    $sd_arr = explode("-", $s_date);
                }
                if(strlen($sd_arr[0])==2){
                    if ($s_date != "") {
                        $s_date = DateTimeField::__convertToDBFormat(trim($s_date), $js_cal_dateformat);
                    }
                    if ($e_date != "") {
                        $e_date = DateTimeField::__convertToDBFormat(trim($e_date), $js_cal_dateformat);
                    }
                }
                if ($s_date != "" && $e_date != "") {
                    $return = " BETWEEN '$s_date' AND '$e_date' ";
                } elseif ($s_date != "") {
                    $return = " = '$s_date' ";
                } elseif ($e_date != "") {
                    $return = " = '$e_date' ";
                } else {
                    $return = "";
                }
                break;
        }
        return $return;
    }

    /** Function to get advanced comparator in query form for the given Comparator and value
     *  @ param $comparator : Type String
     *  @ param $value : Type String
     *  returns the check query for the comparator
     */
    function getAdvComparator($comparator, $value, $datatype = "") {
        $adb = PEARDatabase::getInstance();
        global $default_charset;
        $value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
        $value_len = strlen($value);
        $is_field = false;
        if($this->is_hdn_fval==true){
            $is_field = true;
        }

        if ($datatype == 'C') {
            $value = str_replace("yes", "1", str_replace("no", "0", $value));
        }

        if ($comparator == "e") {
            if($this->is_hdn_fval==true){
                $rtvalue = " = $value";
            }elseif (trim($value) == "NULL") {
                $rtvalue = " is NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " = " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " = " . $adb->quote($value);
            } else {
                $rtvalue = " is NULL";
            }
        }
        if ($comparator == "n") {
            if($this->is_hdn_fval==true){
                $rtvalue = " != $value";
            }elseif (trim($value) == "NULL") {
                $rtvalue = " is NOT NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " <> " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " <> " . $adb->quote($value);
            } else {
                $rtvalue = " is NOT NULL";
            }
        }
        if ($comparator == "s") {
            $rtvalue = " like '" . formatForSqlLike($value, 2, $is_field) . "'";
        }
        if ($comparator == "ew") {
            $rtvalue = " like '" . formatForSqlLike($value, 1, $is_field) . "'";
        }
        if ($comparator == "c") {
            $rtvalue = " like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "k") {
            $rtvalue = " not like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "l") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "g") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($comparator == "m") {
            $rtvalue = " <= " . $adb->quote($value);
        }
        if ($comparator == "h") {
            $rtvalue = " >= " . $adb->quote($value);
        }
        if ($comparator == "b") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "a") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($is_field == true) {
            $rtvalue = str_replace("'", "", $rtvalue);
            $rtvalue = str_replace("\\", "", $rtvalue);
        }
        return $rtvalue;
    }

    /* from reportrun obj start */

    /** Function to convert the Report Header Names into i18n
     *  @param $fldname: Type Varchar
     *  Returns Language Converted Header Strings
     * */
    function getLstringforReportHeaders($fldname) {
        global $modules, $current_language, $current_user, $app_strings;
        $rep_header = ltrim($fldname);
        $rep_header = decode_html($rep_header);
        $labelInfo = explode('_', $rep_header);
        $rep_module = $labelInfo[0];
        if (is_array($this->labelMapping) && !empty($this->labelMapping[$rep_header])) {
            $rep_header = $this->labelMapping[$rep_header];
        } else {
            if ($rep_module == 'LBL') {
                $rep_module = '';
            }
            array_shift($labelInfo);
            $fieldLabel = decode_html(implode("_", $labelInfo));
            $rep_header_temp = preg_replace("/\s+/", "_", $fieldLabel);
            $rep_header = "$rep_module $fieldLabel";
        }
        $curr_symb = "";
        $fieldLabel = ltrim(str_replace($rep_module, '', $rep_header), '_');
        $fieldInfo = getITSFieldByReportLabel($rep_module, $fieldLabel);
        if ($fieldInfo['uitype'] == '71' || $fieldInfo['uitype'] == '712') {
            $curr_symb = " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
        }
        $rep_header .=$curr_symb;

        return $rep_header;
    }

    /* from reportrun obj end */

    // ITS4YOU-CR SlOl 13. 3. 2014 13:38:39
    public function getHeaderLabel($reportid, $type, $fldname, $column_str) {
        $headerLabel = "";
        global $default_charset;
        if ($column_str != "" && $reportid != "") {
            // ITS4YOU-CR SlOl 29. 7. 2016 6:12:04
            if($fldname=="crmid_COUNT"){
                $explode_col_str_arr = explode(":", $column_str);
                $lbl_col_str = explode("_",$explode_col_str_arr[2]);
                $explode_col_str_arr[2] = $lbl_col_str[0]."_LBL_RECORDS";
                $column_str = implode(":", $explode_col_str_arr);
            }
            // ITS4YOU-END
            $adb = PEARDatabase::getInstance();
            $labelsql = "SELECT columnlabel FROM its4you_reports4you_labels WHERE reportid = ? and type = ? AND columnname=?";
//$adb->setDebug(true);
            $labelres = $adb->pquery($labelsql, array($reportid, $type, html_entity_decode($column_str, ENT_QUOTES, $default_charset)));
//$adb->setDebug(false);
            $numlabels = $adb->num_rows($labelres);
            if ($numlabels > 0) {
                while ($row = $adb->fetchByAssoc($labelres)) {
                    $headerLabel = $row["columnlabel"];
                }
            } else {
                $headerLabel = ITS4YouReports::getColumnStr_Label($column_str, $type);
            }
        }
        return $headerLabel;
    }

    // Performance Optimization: Added parameter directOutput to avoid building big-string!
    public function GenerateReport($reportid, $outputformat = "HTML", $directOutput = false) {
        global $current_user, $php_max_execution_time, $currentModule;
        global $modules, $app_strings;
        global $mod_strings, $current_language;
        $adb = PEARDatabase::getInstance();
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        if($this->r_permitted) {
            // CUSTOM REPORT START
            if($this->reports4you_type==="custom_report"){
                $sSQL = $this->tf_sql;

    //$adb->setDebug(true);
                $result = $adb->query($sSQL);
    //$adb->setDebug(false);

                $f_error = "";
                /*             * * ERROR MESSAGE FOR ITS4YOU START ** */
                if (!$result) {
                    $f_error = $this->getSqlError();
                }
                if ($f_error != "") {
                    $this->displaySqlError($adb,$f_error);
                }
                /*             * * ERROR MESSAGE FOR ITS4YOU END ** */
                $header_style = " style='" . $this->header_style . "' ";

    //error_reporting(63);ini_set("display_errors",1);
                $set_pdf_portrait = false;
                $header_populated = false;
                $header_array = array();
                $report_html = $valtemplate_tr = "";
                if ('HTML' === $outputformat) {
                    //$report_html .= $this->getReportNameHTML();
                    if ('v7' === $layout) {
                    	$tableClass = 'table table-bordered';
                    } else {
						$tableClass = 'rpt4youTableContent';
					}
                    $report_html .= '<div id="rpt4youTable" ><table cellpadding="5" cellspacing="0" align="center" class="'.$tableClass.'" border="1" style="border-collapse: collapse;" >';
                    $noofrows = $adb->num_rows($result);
                }
                $xls_i = 0;
                while ($report_data_row = $adb->fetchByAssoc($result)) {

                    if($outputformat=="XLS"){
                        $row_data = array();
                        foreach($report_data_row as $columnname => $columnvalue){
                            if($header_populated!==true){
                                $header_array[$xls_i] = str_replace("_", " ", $columnname);
                                $xls_i++;
                            }
                            $row_data[] = $columnvalue;
                        }
                        $header_populated = true;
                        $data[] = $row_data;
    //ITs4YouReports::sshow($header_array);
    //ITs4YouReports::sshow($data);
                    }else{
                        $valtemplate_tr = "<tr>";
                        foreach($report_data_row as $columnname => $columnvalue){
                            if($header_populated!==true){
                                $header_array[$columnname] = str_replace("_", " ", $columnname);
                            }
                            $valtemplate_tr .= "<td class='rpt4youGrpHead' nowrap >" . $columnvalue . "</td>";
                        }
                        $valtemplate_tr .= "</tr>";
                        if($header_populated!==true){
                            $report_html .= "<tr>";
                            if(count($header_array)>$custom_report_portrait_from){
                                $set_pdf_portrait = true;
                            }
                            foreach($header_array as $headerLabel){
                                $report_html .= "<td class='rpt4youCellLabel' $header_style nowrap >" . $headerLabel . "</td>";
                            }
                            $report_html .= "</tr>";
                            $header_populated = true;
                        }
                        $report_html .= $valtemplate_tr;
                    }
                }
                if($outputformat=="HTML"){
                    $report_html .= '</table></div>';
                }
    //exit;
                if($outputformat=="HTML"){
                    $this->setReportFileInfo($set_pdf_portrait);
                    $request = new Vtiger_Request($_REQUEST, $_REQUEST);
                    if ('ShowWidget' !== $request->get('view') && $request->has('module')==true && $request->get('module')=='ITS4YouReports' && ($request->has('mode')!=true || $request->get('mode')!='widget')){
                        $this->setNoOfRows($noofrows);
                    }
                }

                //echo $report_html;
                if($outputformat=="XLS"){
                    $return_data["headers"] = $header_array;
                    $return_data["data"] = $data;
                    return $return_data;
                }else{
                    //$this->create_pdf_schedule = true;
                    if($this->create_pdf_schedule){
                        $this->createPDFFileForScheduler($report_html,'',$set_pdf_portrait);
                    }

                    if ($directOutput) {
                        echo $report_html;
                    } else {
                        $return_data[] = $report_html;
                        $return_data[] = $noofrows;
                        $return_data[] = $sSQL;
                        return $return_data;
                    }
                }
            // CUSTOM REPORT END
            } else {
            // OTHER REPORTS START
                if($this->time_debug===true){
                    $this->report_obj->define_rt_vars(false,true);
                }

                $this->outputformat = $outputformat;
                $this->to_totals_res = array();

                $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
                if (file_exists($user_privileges_path)) {
                    require($user_privileges_path);
                }
                $modules_selected = array();

                // $ITS4YouReports = new ITS4YouReports($reportid);
                $modules_selected[] = $this->report_obj->primarymodule;
                if (!empty($this->report_obj->relatedmodulesarray)) {
                    foreach ($modules_selected as $key => $modulestr) {
                        $modulearr = explode("x", $modulestr);
                        $secmodule = vtlib_getModuleNameById($modulearr[0]);
                        if (vtlib_isModuleActive($secmodule)) {
                            $modules_selected[] = $secmodule;
                        }
                    }
                }
                // Update Currency Field list
                $currencyfieldres = $adb->pquery("SELECT tabid, fieldid, columnname, fieldlabel, uitype from vtiger_field WHERE uitype in (71,72,712,10)", array());
                if ($currencyfieldres) {
                    foreach ($currencyfieldres as $currencyfieldrow) {
                        $modprefixedlabel = getTabModuleName($currencyfieldrow['tabid']) . ' ' . $currencyfieldrow['fieldlabel'];
                        $modprefixedlabel = str_replace(' ', '_', $modprefixedlabel);
                        if ($currencyfieldrow['uitype'] != 10) {
                            //if (!in_array($modprefixedlabel, $this->convert_currency) && !in_array($modprefixedlabel, $this->append_currency_symbol_to_value)) {
                            $this->convert_currency[] = $modprefixedlabel;
                            //}
                        } else {
                            if (!in_array($modprefixedlabel, $this->ui10_fields)) {
                                $mod_key = $currencyfieldrow["columnname"] . "_fid_" . $currencyfieldrow["fieldid"];
                                $this->ui10_fields[$mod_key] = $modprefixedlabel;
                            }
                        }
                    }
                }

                // ITS4YOU-CR SlOl 7. 4. 2016 14:19:21  schedule_all_records
                if(isset($this->schedule_all_records) && $this->schedule_all_records=="1"){
                    $schedule_all_sql_array = explode(" LIMIT ", $this->tf_sql);
                    if(count($schedule_all_sql_array)>1){
                        $this->tf_sql = $schedule_all_sql_array[0];
                    }
                }
                    if (in_array($outputformat, array("HTML", "XLS"))) {
                        if ( 'ITS4YouReports' === $request->get('module') ) {
							$quickFiltersHtml = $this->getQuickFiltersHtml();
						}

                        if($this->userQuickFilterQuery) {
                            $sSQL = $this->tf_qf_sql;
                        } else {
                            $sSQL = $this->tf_sql;
                        }
                    } else {
                        $sSQL = $this->tf_sql;
                    }
                    // ITS4YOU-END

                    if($outputformat==="KEYMETRICS") {
                        $return_value = "";
                        $calculation = $this->key_metrics_calculation_type;
                        if($this->key_metrics_calculation_type=="COUNT"){
                            $fld_cond = "vtiger_crmentity.crmid";
                            $fld_alias = $this->key_metrics_alias;
                        }else{
                            if(isset($this->columns_array[$this->key_metrics_columns_str]) && !empty($this->columns_array[$this->key_metrics_columns_str])){
                                $fld_cond = $this->columns_array[$this->key_metrics_columns_str]["fld_cond"];
                                $fld_alias = $this->columns_array[$this->key_metrics_columns_str]["fld_alias"];
                                $fld_ui_type = $this->columns_array["uitype_$fld_alias"];
                            }
                        }
                        // ITS4YOU-CR SlOl 20. 1. 2016 9:03:58
                        $group_by_currency_sql = "";
                        if ($calculation!="COUNT" && ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true) {
                            $primary_focus = CRMEntity::getInstance($this->report_obj->primarymodule);
                            $group_by_currency_sql = $this->getCurrencyFieldSql($primary_focus->table_name);
                        }
                        // ITS4YOU-END
                        $key_metrics_query = " SELECT $calculation($fld_cond) AS $fld_alias $group_by_currency_sql FROM ";
                        $sSql_Arr = explode("FROM ",$sSQL);
                        if(isset($sSql_Arr[1]) && $sSql_Arr[1]!=""){
                            if ($calculation!="COUNT" && ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true) {
                                if(isset($this->report_obj->reportinformations["SortByColumn"]) && $this->report_obj->reportinformations["SortByColumn"]!=""){
                                    $groupbyquery = " GROUP BY ".trim($group_by_currency_sql,",");
                                    $sSql_Arr_1 = explode("ORDER BY ",$sSql_Arr[1]);
                                    $sSql_Arr[1] = $sSql_Arr_1[0]." $groupbyquery ORDER BY ".$sSql_Arr_1[1];
                                }
                            }

                            $sSQL = $key_metrics_query.$sSql_Arr[1];
                            $result = $adb->pquery($sSQL, array());
                            if ($adb->num_rows($result) > 0) {
                                $this->key_metrics_rows = ($adb->num_rows($result)+1);
                                while ($row = $adb->fetch_array($result)) {
                                    $currency_id = "";
                                    if(isset($row["currency_id"]) && $row["currency_id"]!=""){
                                        $currency_id = $row["currency_id"];
                                    }

                                    if($fld_ui_type!=""){
                                        $return_value .= "<div style='width:100%;text-align:right;'>".$this->getFldFormatedValue($fld_ui_type, $row[$fld_alias], $fld_alias, "", $currency_id, false)."</div>";
                                    }else{
                                        $return_value = $this->getFldFormatedValue($fld_ui_type, $row[$fld_alias], $fld_alias, "", $currency_id, false);
                                    }
                                }
                            }
                        }
                        return $return_value;
                    }

                    $this->sum_col_i = 0;

                    $selectedcolumns_arr = $this->report_obj->getSelectedColumnListArray($this->report_obj->record);

                    // GROUPING SQL START
                    //if (!empty($this->groupslist) && !empty($this->report_obj->reportinformations["summaries_columns"])) {
                    if (!empty($this->groupslist)) {
                        if (!empty($this->report_obj->reportinformations["summaries_columns"])) {
                            foreach ($this->report_obj->reportinformations["summaries_columns"] as $key => $summaries_columns_arr) {
                                $column_arr = explode(":", $summaries_columns_arr["columnname"]);
                                $imploded = "";
                                if (is_numeric($column_arr[5]) || in_array($column_arr[5], ITS4YouReports::$customRelationTypes)) {
                                    $ci_n = 6;
                                } else {
                                    $ci_n = 5;
                                }
                                for ($ci = 0; $ci < $ci_n; $ci++) {
                                    if ($ci > 0) {
                                        $imploded .= ":";
                                    }
                                    $imploded .= $column_arr[$ci];
                                }
                                $c_calculation_type = $column_arr[$ci_n];
                                $sum_col_sql_a[$imploded][] = $c_calculation_type;

                                if (in_array($c_calculation_type, self::$summaryAmountColumns)) {
                                    $this->sum_col_i++;
                                }
                            }
                            $this->sum_col_sql_a = $sum_col_sql_a;
                        }

                        if ($this->report_obj->reportinformations["Group1"] != "none" && !empty($selectedcolumns_arr) && $this->generate_type != "grouping") {
                            $this->detail_columns_array = $this->columns_array;
                            $this->detail_sql = $sSQL;
                            // $this->setResultArray($sSQL);
                        }
                        if (!empty($this->report_obj->reportinformations["summaries_columns"]) || $this->report_obj->reportinformations["Group1"] != "none") {
                            $group_columns_array = $this->group_column_alias;
                            $this->generateQuery($this->report_obj, "", "grouping");
                            $sSQL = $this->tf_sql;
                        }
                    }
                    // GROUPING SQL END
                    /* ARRAY FOR TOTALS CALCULATION START */
                    // $to_totals_array = $this->getToTotalsArray();
                    $this->to_totals_array = $this->getToTotalsArray();

                    if($this->time_debug===true){
                        $this->report_obj->getR4UDifTime("After Query Generate",true);
                    }
                    /* ARRAY FOR TOTALS CALCULATION END */
        // !!! BASE PRIMARY DEBUG !!!
    /*
	if ('78.99.157.226'===$_SERVER['REMOTE_ADDR']) {
    	ITS4YouReports::sshow($this->generate_type);
		$adb->setDebug(true);
	}
	*/
	//self::displaySqlFormattedQuery($sSQL);
                    $result = $adb->pquery($sSQL,array());
	/*
	if ('78.99.157.226'===$_SERVER['REMOTE_ADDR']) {
		$adb->setDebug(false);
	}
	*/
                    if($this->time_debug===true){
                        $this->report_obj->getR4UDifTime("After Base Result",true);
                    }
                    $f_error = "";
                    if (!$result) {
                        $f_error = $this->getSqlError();
                    }
                    /*             * * ERROR MESSAGE FOR ITS4YOU START ** */
                    if ($f_error != "") {
                        $this->displaySqlError($adb,$f_error);
                    }
        //            $adb->setDebug(false);

                    /*$error_msg = $adb->database->ErrorMsg();
                    if (!$result && $error_msg != '') {
                        // Performance Optimization: If direct output is requried
                        if ($directOutput) {
                            $report_html .= getTranslatedString('LBL_REPORT_GENERATION_FAILED', $this->getCurrentModule4You()) . "<br>" . $error_msg;

                            $error_msg = false;
                            echo $report_html;
                        }
                        // END
                        return $error_msg;
                    }*/

                    $this->setUpGroupColsArray();

                    if ($this->generate_type == "grouping") {
                        for ($mi = 1; $mi < 4; $mi++) {
                            if ($mi > 1 && isset($this->group_cols_array[$mi]) && $this->group_cols_array[$mi] != "") {
                                $group_cols[] = $this->group_cols_array[$mi];
                            }
                        }
                    }
                    $noofrows = $adb->num_rows($result);
                    if($this->time_debug===true){
                        $this->report_obj->getR4UDifTime("Going to Generate HTML",true);
                    }
                if ($outputformat == "HTML" || $outputformat == "CHARTS") {
                    $report_html_pdf = $report_html = '';

                    if(!empty($this->report_obj->reportinformations['quick_filters'])) {
                        $report_html .= $quickFiltersHtml;
                    }
                    if($layout !== "v7" && 'HTML' === $outputformat && isset($_REQUEST['view']) ) {
                        $report_html .= '<div class="padding1per textAlignCenter" style="text-align: center;"><button class="btn generateReport" data-mode="generate" value="' . vtranslate("LBL_GENERATE_NOW", $currentModule) . '"/><strong>' . vtranslate("LBL_GENERATE_NOW", $currentModule) . '</strong></button></div>';
                    }

                    if($layout !== "v7") {
						$report_html .= $return_name= $this->getReportNameHTML();
					}
                    if($layout == "v7" && $outputformat !== "CHARTS" && !$this->create_pdf_schedule){
                        $report_html .= '
                            <div class="no-print">
                                <button class="btn btn-default no-print" name="header_info_block" id="header_info_block" >
                                    <strong>'.vtranslate('LBL_HEADER_INFO_BLOCK', $currentModule).'</strong>&nbsp;&nbsp;
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                            ';
                    }

                    if(isset($this->report_obj->reportinformations["description"]) && $this->report_obj->reportinformations["description"]!=""){
                        $report_html .= "
                            <table cellpadding='5' cellspacing='0' id='reportHederInfoDesc' align='left' class='rpt4youTable hide' border='0' style='border:0px;width:100%;' >
                                <tr>
                                    <td class='rpt4youGrpHeadInfo' style='padding-left:15px;font-size:14px;text-align:center;'>
                                    ".nl2br($this->report_obj->reportinformations["description"])."
                                    </td>
                                </tr>
                            </table>";
                    }

                    if ('HTML' === $outputformat) {
                        if (!empty($this->report_obj->reportinformations['charts']) && 'top' === $this->report_obj->reportinformations['charts'][1]['chart_position']) {
                            $this->chartPosition = $this->report_obj->reportinformations['charts'][1]['chart_position'] . '_';
                            $report_html .= $this->addHighChartDiv($this->report_obj->reportinformations['charts'][1]['chart_position']);
                        }
                    }

                    $reportHederInfoClass = '';
					if($layout === "v7") {
						$reportHederInfoClass = 'hide';
					}

                    if (!$this->create_pdf_schedule) {
                        $report_html .= "<div id='reportHederInfo' class='$reportHederInfoClass' style='padding-top:1em;padding-bottom:1em;'><@ReportHeaderInfo@></div>";
                    }
                    $this->report_html_headerInfo = $this->report_obj->getReportHeaderInfo($noofrows,true);

                    $dataAreaClass = '';
                    if ($layout == "v7") {
                        if (!empty($this->report_obj->reportinformations['charts']) && 1 === (int) $this->report_obj->reportinformations['charts'][1]['collapse_data_block']) {
                            $dataIconClass = 'fa-chevron-right';
                            $dataAreaClass = 'hide';
                        } else {
                            $dataIconClass = 'fa-chevron-down';
                        }

                        if(!$this->create_pdf_schedule) {
							$report_html .= '
	                            <div class="no-print" style="padding-top: 1em;">
	                                <button class="btn btn-default no-print" name="data_info_block" id="data_info_block" >
	                                    <strong>'.vtranslate('LBL_DATA_INFO_BLOCK', $currentModule).'</strong>&nbsp;&nbsp;
	                                    <i class="fa '.$dataIconClass.'"></i>
	                                </button>
	                            </div>';
	                   }
                    }
                    $report_html .= '<div id="rpt4youTable" class="'.$dataAreaClass.'">';
		            $layout = Vtiger_Viewer::getDefaultLayoutName();
		            if($layout == "v7"){
		                $resultTableClass = 'table table-bordered';
		            } else {
		                $resultTableClass = 'rpt4youTableContent';
		            }
                    $report_html .= '<table cellpadding="5" cellspacing="0" align="center" class="'.$resultTableClass.'" border="1" style="border-collapse: collapse;" ><tr>';
                    // END

                    if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                        $picklistarray = $this->getAccessPickListValues();
                    }

                    if ($result) {
                        $y = ($adb->num_fields($result) - 1);
                        $t_y = $y;

                        $custom_field_values = $adb->fetch_array($result);

                        $column_definitions = $adb->getFieldsDefinition($result);

                        // HEADERS
                        if (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols")) {
                            if ($this->report_obj->reportinformations["Group3"] && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                $agi = 2;
                                foreach ($this->sum_col_sql_a as $column_str => $calculation_arr) {
                                    foreach ($calculation_arr as $calculation_type) {
                                        // $calculation_type = $calculation_arr[0];
                                        $label_db_key = "$column_str:$calculation_type";
                                        if ($this->report_obj->in_multiarray($label_db_key, $this->summaries_columns, "columnname") !== true) {
                                            continue;
                                        }
                                        $fld_sql_str_array = explode(" AS ", $this->columns_array[$column_str]["fld_sql_str"]);
                                        $fld_str = $fld_sql_str_array[0];
                                        $fld_str_as = $fld_sql_str_array[1] . "_$calculation_type";
                                        $g_data_key_lbl = $this->getHeaderLabel($this->report_obj->record, "SM", $fld_str_as, $label_db_key);
                                        $sum_columns_bg = $this->g_colors[1];
                                        $sum_columns_labels[] = array("style" => " background-color:$sum_columns_bg;font-weight:bold; ", "label" => $g_data_key_lbl);
                                    }
                                }
                            } else {
                                $agi = 1;
                            }
                            for ($x = 0; $x < 1; $x++) {
                                $fld = $adb->field_name($result, $x);
                                $is_hid = strpos($fld->name, "_hid");
                                if ($is_hid === false && !in_array($fld->name, $this->skip_fields)) {
                                    if (!in_array($fld->name, $group_cols)) {
                                        $header_style = "";
                                        $header_style .= " style='" . $this->header_style . "' ";
                                        //$header[] = array("style" => $header_style, "label" => "&nbsp;",);
                                        $GroupsHeaderLabel = $this->getGroupsHeaderLabelStr();
                                        $header[] = array("style" => $header_style, "label" => $GroupsHeaderLabel,);

                                        $headercols = $this->getColsHeaders($header_style, $agi);
                                        foreach ($headercols as $header_arr) {
                                            $header[] = $header_arr;
                                            $group2_headers[] = $sum_columns_labels;
                                            //$group2_headers[$header_arr["label"]] = $sum_columns_labels;
                                        }
                                    }
                                }
                                // END
                            }

                        } else {
                            $crm_id_count_done = false;
                            for ($x = 0; $x <= $y; $x++) {
                                $fld = $adb->field_name($result, $x);
                                $is_hid = strpos($fld->name, "_hid");

                                $clear_fld_name_arr = explode("_", $fld->name);
                                $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                $clear_fld_name = implode("_", $clear_fld_name_arr);

                                if ($clear_fld_calculation_type!="COUNT" && (in_array($fld->name, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields))) {
                                    continue;
                                }
                                if (!empty($selectedcolumns_arr) && in_array($fld->name, $this->g_flds) && isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
                                    if ($x == 0 && !$this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") && $this->generate_type != "grouping") {
                                        $group_value = $custom_field_values[$fld->name];
                                        continue;
                                    }
                                }

                                // ITS4YOU-CR SlOl 4. 7. 2016 13:00:53 - MISSING COUNT COLUMN IN RESULT
                                if($crm_id_count_done!=true && $clear_fld_name=="crmid" && $x > 0 && $this->generate_type == "grouping" && $this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type"!=""){
                                    $explode_col_str_arr = explode(":", $this->columns_array[$clear_fld_name]);
                                    $lbl_col_str = explode("_",$explode_col_str_arr[2]);
                                    $explode_col_str_arr[2] = $lbl_col_str[0]."_LBL_RECORDS";
                                    $check_col_str = implode(":", $explode_col_str_arr);
                                    if($this->report_obj->in_multiarray($check_col_str . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true){
                                        continue;
                                    }
                                    $crm_id_count_done = true;

                                }elseif ($x > 0 && $this->generate_type == "grouping" && $this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                    continue;
                                }

                                if ($is_hid === false && !in_array($fld->name, $this->skip_fields) && !in_array($clear_fld_name, $this->skip_fields)) {
                                    if (in_array($fld->name, $group_cols) !== true) {
                                        // ITS4YOU-CR SlOl 13. 3. 2014 13:37:11
                                        if ($this->generate_type == "grouping") {
                                            $summaries_fld_test = explode("_", $fld->name);
                                            $smft_lk = count($summaries_fld_test) - 1;
                                            if (in_array(strtolower($summaries_fld_test[$smft_lk]), $this->calculation_type_array)) {
                                                $calculation_type = $summaries_fld_test[$smft_lk];
                                                $fld_name = "";
                                                for ($index = 0; $index < (count($summaries_fld_test)); $index++) {
                                                    if ($fld_name != "") {
                                                        $fld_name .= "_";
                                                    }
                                                    $fld_name .= $summaries_fld_test[$index];
                                                }
                                            } else {
                                                $fld_name = $fld->name;
                                            }
                                        } else {
                                            $fld_name = $fld->name;
                                        }
                                        $s_type = "SC";
                                        if ($this->generate_type == "grouping" && isset($this->sm_columns_array) && !empty($this->sm_columns_array) && array_key_exists($fld_name, $this->sm_columns_array)) {
                                            $s_type = "SM";
                                            $columns_array_lbl = $this->sm_columns_array[$fld_name] . ":$calculation_type";
                                        } elseif ($this->columns_array[$fld->table . "." . $fld_name]) {
                                            $columns_array_lbl = $this->columns_array[$fld->table . "." . $fld_name];
                                        } elseif (isset($this->columns_array[$fld_name])) {
                                            $columns_array_lbl = $this->columns_array[$fld_name];
                                        } else {
                                            $columns_array_lbl = "";
                                        }
                                        // we will skipp 2,3 group by values to display later correct values !!
                                        if ($this->generate_type == "grouping" && in_array($fld_name, $group_cols)) {
                                            continue;
                                        }

                                        if (isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
                                            if (!empty($selectedcolumns_arr)) {
                                                if ($this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") !== true && !in_array($fld->name, $this->g_flds)) {
                                                    continue;
                                                }
                                            }
                                        }
                                        if($this->generate_type != "grouping" && !isset($this->columns_array[$fld->name])){
                                            continue;
                                        }

                                        // $this->getQFArray();

                                        $headerLabel = $this->getHeaderLabel($this->report_obj->record, $s_type, $fld_name, $columns_array_lbl);

                                        if (count($this->g_colors) > 1 && $x == 0 && $this->generate_type == "grouping") {
                                            // $headerLabel = "&nbsp;";
                                            $headerLabel = $this->getGroupsHeaderLabelStr();
                                        }

                                        $header_style = "";
                                        $header_style .= " style='" . $this->header_style . "' ";
                                        $header[] = array("style" => $header_style, "label" => $headerLabel,);
                                        // ITS4YOU-END 14. 3. 2014 8:33:46
                                    }
                                }
                                // END
                            }
                            // ITS4YOU-CR SlOl 18. 5. 2016 14:14:49
                            if(!empty($this->report_obj->reportinformations["cc_array"])){
                                foreach($this->report_obj->reportinformations["cc_array"] as $cc_row){
                                    $header_style .= " style='" . $this->header_style . "' ";
                                    $header[] = array("style" => $header_style, "label" => $cc_row["cc_name"],);
                                }
                            }
                            // ITS4YOU-END
                        }

                        // ITS4YOU-CR SlOl | 12.8.2014 11:40 variable to populate header for record details
                        $populate_detail_header = true;
                        // ITS4YOU-CR SlOl | 12.8.2014 11:40 variable to populate header for record details end
                        $display_groupinfo_row = false;

                        $this->group_data_array = array();
                        $grouping_totals = $group2_values = array();

                        $this->setChartsColumns();

                        if ($noofrows > 0) {
                            $f_i = $f_r_i = 0;
                            $group_info_tr_value = $old_gv = ""; // tr_html of Group Value (Count) Info in case summaries columns empty start
                            do {
                                $arraylists = Array();
                                if (count($this->groupslist) == 1) {
                                    $newvalue = $custom_field_values[0];
                                } elseif (count($this->groupslist) == 2) {
                                    $newvalue = $custom_field_values[0];
                                    $snewvalue = $custom_field_values[1];
                                } elseif (count($this->groupslist) == 3) {
                                    $newvalue = $custom_field_values[0];
                                    $snewvalue = $custom_field_values[1];
                                    $tnewvalue = $custom_field_values[2];
                                }
                                if ($newvalue == "")
                                    $newvalue = "-";

                                if ($snewvalue == "")
                                    $snewvalue = "-";

                                if ($tnewvalue == "")
                                    $tnewvalue = "-";

                                /* if($this->generate_type == "grouping"){
                                  $group_value = $custom_field_values[$this->g_flds[0]];
                                  } */

                                $valtemplate_tr = "<tr>";

                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols"))) {
                                    $y = 1;
                                }

                                $currency_id = "";
                                if (isset($custom_field_values["currency_id"]) && $custom_field_values["currency_id"] != "") {
                                    $currency_id = $custom_field_values["currency_id"];
                                }
                                if (isset($custom_field_values["invoicing_currency_id"]) && $custom_field_values["invoicing_currency_id"] != "") {
                                    $currency_id = $custom_field_values["invoicing_currency_id"];
                                }

                                $this->data_record_id = "";
                                if (isset($custom_field_values["record_id"]) && $custom_field_values["record_id"] != "") {
                                    $this->data_record_id = $custom_field_values["record_id"];
                                }

                                // ITS4YOU-CR SlOl | 12.8.2014 11:40 variables used for group info rows
                                $detail_row_info = array();
                                // ITS4YOU-END 12.8.2014 11:41
                                // Variables used for Group Value (Count) Info in case summaries columns empty start
                                $group_info_tr = "";
                                $group_info_tr_added = false;
                                $gc_i = 0;

                                // Set up group_value for Group Value (Count) Info in case group column not in selectedcolumns_arr start
                                // $this->summaries_columns_count
                                if (count($this->g_flds) == 1 && count($this->detail_selectedcolumns_arr) > 0) {
                                    if ($display_groupinfo_row !== true && isset($this->columns_array[$this->g_flds[0]]) && $this->columns_array[$this->g_flds[0]] != "") {
                                        $display_groupinfo_row = true;
                                    }
                                } elseif ($this->report_obj->reportinformations["Group1"] != "none" && $this->summaries_columns_count == 0 && $display_groupinfo_row !== true) {
                                    $display_groupinfo_row = true;
                                }
                                /* if(count($this->g_flds)>1 && count($this->detail_selectedcolumns_arr)>0 && $this->summaries_columns_count===0){
                                  $display_groupinfo_row = true;
                                  } */
                                // LIMIT FOR GROUP INFO ROW START
                                if ($display_groupinfo_row == true && isset($this->report_obj->reportinformations["columns_limit"]) && $this->report_obj->reportinformations["columns_limit"] != "" && isset($this->group_cols_array[1]) && $this->group_cols_array[1] != "") {
                                    $a_gv = $custom_field_values[$this->group_cols_array[1]];
                                    if ($old_gv != $a_gv) {
                                        $old_gv = $custom_field_values[$this->group_cols_array[1]];
                                        $f_r_i = 0;
                                    }
                                    if ($this->report_obj->reportinformations["columns_limit"] > 0) {
                                        if ($a_gv == $old_gv && $f_r_i >= $this->report_obj->reportinformations["columns_limit"]) {
                                            continue;
                                        }
                                    }
                                }

                                $this->cc_values_array = array();
                                // LIMIT FOR GROUP INFO ROW END
                                // Variables used for Group Value (Count) Info in case summaries columns empty end
                                $crm_id_count_done = false;
                                for ($i = 0; $i <= $y; $i++) {
                                    $fld = $adb->field_name($result, $i);

                                    $clear_fld_name_arr = explode("_", $fld->name);
                                    $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                    unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                    $clear_fld_name = implode("_", $clear_fld_name_arr);

                                    if (in_array($fld->name, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                        continue;
                                    }

                                    if ($i == 0 && !empty($selectedcolumns_arr) && in_array($fld->name, $this->g_flds) && isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
                                        if (!$this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") && $this->generate_type != "grouping") {
                                            $group_value = $custom_field_values[$fld->name];
                                            continue;
                                        }
                                    }
                                    // ITS4YOU-CR SlOl 28. 6. 2016 14:32:21
                                    if(isset($custom_field_values["fieldname"]) && $custom_field_values["fieldname"]!=""){
                                        $this->row_fldname = $custom_field_values["fieldname"];
                                        $this->row_module = $custom_field_values["module"];
                                        $this->row_currency_id = $currency_id;
                                    }else{
                                        $this->row_fldname = "";
                                        $this->row_module = "";
                                        $this->row_currency_id = "";
                                    }
                                    // ITS4YOU-END

                                    // ITS4YOU-CR SlOl 19. 5. 2016 6:47:34 custom calculation values population
                                    if(in_array($this->columns_array[$fld->name],$this->cc_array)){
                                        $cc_fld_name = $fld->name;
                                        $this->cc_values_array[$cc_fld_name] = $custom_field_values[$fld->name];
                                    }
                                    // ITS4YOU-END

                                    $default_charset = vglobal("default_charset");
                                    $fld_name_decode = html_entity_decode($fld->name, ENT_QUOTES, $default_charset);
                                    if (isset($this->columns_array[$fld->name]) && $this->report_obj->in_multiarray($this->columns_array[$fld->name], $this->detail_selectedcolumns_arr, "fieldcolname") !== true && in_array($fld->name, $this->g_flds) !== true) {
                                        continue;
                                    }

                                    // ITS4YOU-CR SlOl 4. 7. 2016 13:00:53 - MISSING COUNT COLUMN IN RESULT
                                    if($crm_id_count_done!=true && $clear_fld_name=="crmid" && $i > 0 && $this->generate_type == "grouping" && $this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type"!=""){
                                        $explode_col_str_arr = explode(":", $this->columns_array[$clear_fld_name]);
                                        $lbl_col_str = explode("_",$explode_col_str_arr[2]);
                                        $explode_col_str_arr[2] = $lbl_col_str[0]."_LBL_RECORDS";
                                        $check_col_str = implode(":", $explode_col_str_arr);
                                        if($this->report_obj->in_multiarray($check_col_str . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true){
                                            continue;
                                        }
                                        $crm_id_count_done = true;

                                    }elseif ($i > 0 && $this->generate_type == "grouping" && $this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                    // ITS4YOU-END
                                        continue;
                                    }

                                    if($this->generate_type != "grouping" && !isset($this->columns_array[$fld->name])){
                                        continue;
                                    }

                                    // detail_selectedcolumns_arr
                                    $first_td = true;

                                    // skipp group columns in case not in selectedcolumns_arr end
                                    // ITS4YOU-CR SlOl 17. 2. 2014 10:23:31
                                    $is_hid = strpos($fld->name, "_hid");
                                    if ($is_hid === false) {
                                        if ($this->generate_type == "grouping") {
                                            $summaries_fld_test = explode("_", $fld->name);
                                            $smft_lk = count($summaries_fld_test) - 1;
                                            if (in_array(strtolower($summaries_fld_test[$smft_lk]), $this->calculation_type_array)) {
                                                $calculation_type = $summaries_fld_test[$smft_lk];
                                                $fld_name = "";
                                                for ($index = 0; $index < (count($summaries_fld_test)); $index++) {
                                                    if ($fld_name != "") {
                                                        $fld_name .= "_";
                                                    }
                                                    $fld_name .= $summaries_fld_test[$index];
                                                }
                                            } else {
                                                $fld_name = $fld->name;
                                            }
                                            // we will skipp 2,3 group by values to display later correct values !!
                                            if ($this->generate_type == "grouping" && in_array($fld_name, $group_cols)) {
                                                continue;
                                            }
                                        }

                                        $hid_url = "";
                                        $fld_hid = $adb->query_result($result, $f_i, $fld->name . "_hid");

                                        if (array_key_exists($fld->name, $this->ui10_fields) && !empty($custom_field_values[$i])) {
                                            $fld_hid = $custom_field_values[$i];
                                        }
                                        if (isset($fld_hid) && $fld_hid != "") {
                                            $entitytype = getSalesEntityType($fld_hid);
                                            if ($entitytype != "") {
                                                global $site_URL;
                                                switch ($entitytype) {
                                                    case "Calendar":
                                                        $hid_url = $site_URL.'/index.php?module=Calendar&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]) . '&activity_mode=Task';
                                                        break;
                                                    case "Events":
                                                        $hid_url = $site_URL.'/index.php?module=Calendar&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]) . '&activity_mode=Events';
                                                        break;
                                                    default:
                                                        $hid_url = $site_URL.'/index.php?module=' . $entitytype . '&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]);
                                                        break;
                                                }
                                            } else {
                                                $user = 'no';
                                                $u_result = $adb->pquery("SELECT count(*) as count from vtiger_users where id = ?", array($fld_hid));
                                                if ($adb->query_result($u_result, 0, 'count') > 0) {
                                                    $user = 'yes';
                                                }
                                                if (is_admin($current_user)) {
                                                    global $site_URL;
                                                    if ($user == 'no') {
                                                        $hid_url = "$site_URL/index.php?module=Settings&action=GroupDetailView&groupId=" . $fld_hid;
                                                    } else {
                                                        $hid_url = "$site_URL/index.php?module=Users&action=DetailView&record=" . $fld_hid;
                                                    }
                                                }
                                            }
                                        }
                                        $fld_type = $column_definitions[$i]->type;
                                        /* if (in_array($fld->name, $this->convert_currency)) {
                                          if ($custom_field_values[$i] != '')
                                          $fieldvalue = convertFromMasterCurrency($custom_field_values[$i], $current_user->conv_rate);
                                          else
                                          $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                          } elseif (in_array($fld->name, $this->append_currency_symbol_to_value)) {
                                          $curid_value = explode("::", $custom_field_values[$i]);
                                          $currency_id = $curid_value[0];
                                          $currency_value = $curid_value[1];
                                          $cur_sym_rate = getCurrencySymbolandCRate($currency_id);
                                          if ($custom_field_values[$i] != '')
                                          $fieldvalue = $cur_sym_rate['symbol'] . " " . $currency_value;
                                          else
                                          $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                          }elseif ($fld->name == "PurchaseOrder_Currency" || $fld->name == "SalesOrder_Currency" || $fld->name == "Invoice_Currency" || $fld->name == "Quotes_Currency") {
                                          if ($custom_field_values[$i] != '')
                                          $fieldvalue = getCurrencyName($custom_field_values[$i]);
                                          else
                                          $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                          }elseif (array_key_exists($fld->name, $this->ui10_fields) && !empty($custom_field_values[$i])) { */
                                        if (array_key_exists($fld->name, $this->ui10_fields) && !empty($custom_field_values[$i])) {
                                            $type = getSalesEntityType($custom_field_values[$i]);
                                            $tmp = getEntityName($type, $custom_field_values[$i]);
                                            if (is_array($tmp)) {
                                                foreach ($tmp as $key => $val) {
                                                    $fieldvalue = $val;
                                                    break;
                                                }
                                            } else {
                                                $fieldvalue = $custom_field_values[$i];
                                            }
                                        } else {
                                            if ($custom_field_values[$i] != '')
                                                $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                            else
                                                $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                        }
                                        $fieldvalue = str_replace("<", "&lt;", $fieldvalue);
                                        $fieldvalue = str_replace(">", "&gt;", $fieldvalue);

                                        //check for Roll based pick list
                                        $temp_val = $fld->name;
                                        if (is_array($picklistarray))
                                            if (array_key_exists($temp_val, $picklistarray)) {
                                                if (!in_array($custom_field_values[$i], $picklistarray[$fld->name]) && $custom_field_values[$i] != '')
                                                    $fieldvalue = $app_strings['LBL_NOT_ACCESSIBLE'];
                                            }
                                        if (is_array($picklistarray[1]))
                                            if (array_key_exists($temp_val, $picklistarray[1])) {
                                                $temp = explode(",", str_ireplace(' |##| ', ',', $fieldvalue));
                                                $temp_val = Array();
                                                foreach ($temp as $key => $val) {
                                                    if (!in_array(trim($val), $picklistarray[1][$fld->name]) && trim($val) != '') {
                                                        $temp_val[] = $app_strings['LBL_NOT_ACCESSIBLE'];
                                                    } else
                                                        $temp_val[] = $val;
                                                }
                                                $fieldvalue = (is_array($temp_val)) ? implode(", ", $temp_val) : '';
                                            }

                                        if ($fieldvalue == "") {
                                            $fieldvalue = "-";
                                        } else if ($fld->name == 'LBL_ACTION') {
                                            global $site_URL;
                                            $fieldvalue = "<a href='$site_URL/index.php?module={$this->primarymodule}&view=Detail&record={$fieldvalue}' target='_blank'>" . getTranslatedString('LBL_VIEW_DETAILS') . "</a>";
                                        } else if (stristr($fieldvalue, "|##|")) {
                                            $fieldvalue = str_ireplace(' |##| ', ', ', $fieldvalue);
                                        } else if ($fld_type == "date" || $fld_type == "datetime") {
                                            $fieldvalue = getValidDisplayDate($fieldvalue);
                                        }
                                        if ($hid_url != "") {
                                            $fieldvalue = "<a href='$hid_url' target='_blank'>$fieldvalue</a> ";
                                        }

                                        $bg_color = "";
                                        if ($this->generate_type == "grouping") {
                                            if ($i == 0 && in_array($fld->name, $this->g_flds)) {
                                                $group_value = $custom_field_values[$i];
                                            }

                                            if (isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && $this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                                $bg_color_val = $this->g_colors[1];
                                            } elseif (isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                                $bg_color_val = $this->g_colors[2];
                                            } else {
                                                $bg_color_val = $this->g_colors[0];
                                            }
                                            $bg_color = "background-color:$bg_color_val;";
                                        }
                                        // $txt_align = $this->getFldAlignment($fld->name, $fieldvalue);
                                        $fld_style_arr = $this->getFldStyle($fld->name, $fieldvalue);
                                        if ($this->generate_type == "grouping" && is_numeric($fieldvalue) && !in_array($fld->name, $this->group_cols_array)) {
                                            $fld_name_exploded = explode("_", $fld->name);
                                            $calculation_type = $fld_name_exploded[(count($fld_name_exploded) - 1)];

                                            if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                $grouping_totals[$fld->name][$currency_id][] = number_format($fieldvalue, 3, ".", "");
                                            } else {
                                                $grouping_totals[$fld->name][] = number_format($fieldvalue, 3, ".", "");
                                            }
                                        }
        //                              // timeline_type2"]=="cols REPORT
                                        if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                            $array_to_totals = array($result, $custom_field_values, $i, $t_y);
                                            $currency_string = $this->get_currency_sumbol_str($currency_id);
                                            $group2_arr = $this->getSubGroupCols($group_value, 1, "", $currency_id, $array_to_totals);

                                            if (!empty($group2_arr["headers"])) {
                                                //$group2_headers = array_merge($group2_headers, $group2_arr["headers"]);
                                                $group2_headers = $group2_arr["headers"];
                                            }
                                        }
                                        // charts array population start
                                        $ch_fldname = strtolower($fld->name);
                                        if (!empty($this->charts) && !empty($this->charts["charttypes"])) {
                                            $this->setChArrayValues("charttitle", '', $this->charts["charttitle"]);
                                            if($this->charts["x_group"]=="group1" && in_array($ch_fldname, $this->charts["charts_ds_columns"])){
                                                $this->setDataseriesArray($group_value, $fieldvalue, $currency_id, $ch_fldname);
                                            }
                                        }
                                        // charts array population end
                                        /**                                 * **  GROUP INFO ROW DISPLAY for GROUPS In case selected columns not empty *** GROUPING REPORT * */
                                        if ($first_td === true && $this->generate_type == "grouping" && count($this->g_colors) == 1 && count($this->detail_selectedcolumns_arr) > 0) {
                                            if (!isset($this->summaries_header) || empty($this->summaries_header)) {
                                                $this->summaries_header = $header;
                                            }
                                            $header_col_lstr = "";
                                            if (isset($this->summaries_header[$i]["label"]) && trim($this->summaries_header[$i]["label"]) != "") {
                                                $header_col_lbl = $this->summaries_header[$i]["label"];
                                            } else {
                                                $header_col_as = $clear_fld_name;
                                                $header_col_str = $this->columns_array[$header_col_as];
                                                $header_col_lbl = $this->getHeaderLabel($this->report_obj->record, "SC", $header_col_as, $header_col_str);
                                            }
                                            // header population for Groups row start
                                            // disabledinv7
                                            //if ($this->selectedcolumns_header_row == "") {
                                                $header = array();
                                                if (!empty($selectedcolumns_arr)) {
                                                    foreach ($selectedcolumns_arr as $sc_key => $sc_array) {
                                                        $sc_header_style = " style='" . $this->header_style . "' ";
                                                        $sc_column_str = $sc_array["fieldcolname"];
                                                        $headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", "", $sc_column_str);
                                                        $header[] = array("style" => $sc_header_style, "label" => $headerLabel,);
                                                    }
                                                    $sc_header = $sc_header_sp = "";
                                                    /* $sc_header_sp .= "<td class='rpt4youGrpHead0' colspan='".count($header)."'>&nbsp;</td>";
                                                      $sc_header_sp .= "</tr>";
                                                      $sc_header_sp .= "<tr>"; */
                                                    foreach ($header as $header_f_arr) {
                                                        $header_style = $header_f_arr["style"];
                                                        $headerLabel = $header_f_arr["label"];
                                                        $sc_header .= "<td class='rpt4youCellLabel' rowspan='1' colspan='1' $header_style align='center' nowrap >$headerLabel</td>";
                                                    }
                                                    $sc_header .= "</tr>";
                                                    $sc_header .= "<tr>";
                                                    $this->selectedcolumns_header_row = $sc_header;
                                                    //$this->selectedcolumns_header_row_sp = $sc_header_sp;
                                                }
                                                $this->display_group_totals = false;
                                            //}
                                            // header population for Groups row end
                                            if ($display_groupinfo_row === true && count($this->g_flds) == 1) {
                                                $sp_group_value = $custom_field_values[$this->group_cols_array[1]];
                                                if ($group_info_tr_added !== true) {
                                                    $sm_calculation_type = "";
                                                    /* if($f_i==0){
                                                      $group_info_tr .= $this->selectedcolumns_header_row;
                                                      } */
                                                    if (isset($this->summaries_columns) && !empty($this->summaries_columns)) {
                                                        $detail_row_info = array();
                                                        foreach ($this->summaries_columns as $sm_key => $sm_col_array) {
                                                            $sm_col_str = $sm_col_array['columnname'];
                                                            $sm_col_alias = "";
                                                            $sm_col_str_exploded = explode(":", $sm_col_str);
                                                            $sm_lk = (count($sm_col_str_exploded) - 1);
                                                            if (in_array(strtolower($sm_col_str_exploded[$sm_lk]), $this->calculation_type_array)) {
                                                                $sm_calculation_type = strtolower($sm_col_str_exploded[$sm_lk]);
                                                                $sm_col_lbl_str = implode(":", $sm_col_str_exploded);
                                                                unset($sm_col_str_exploded[$sm_lk]);
                                                                $sm_col_str = implode(":", $sm_col_str_exploded);
                                                                if (isset($this->columns_array[$sm_col_str]["fld_alias"]) && $this->columns_array[$sm_col_str]["fld_alias"] != "") {
                                                                    $sm_col_alias = $this->columns_array[$sm_col_str]["fld_alias"];
                                                                }
                                                                $sm_col_alias .= "_$sm_calculation_type";
                                                            }
                                                            $sm_value = $custom_field_values[$sm_col_alias];
                                                            $sm_col_Label = $this->getHeaderLabel($this->report_obj->record, "SM", $sm_col_alias, $sm_col_lbl_str);
                                                            // $header_col_lstr = ";";
                                                            // $detail_row_info[] = $sm_col_Label." = ".$this->getFldNumberFormat($sm_col_alias, $sm_value, $currency_id).$header_col_lstr;
                                                            $detail_row_info[] = $sm_col_Label . " = " . $this->getFldNumberFormat($sm_col_alias, $sm_value, $currency_id);
                                                        }
                                                    }
                                                    $group_info_tr_value = $this->getFldNumberFormat($this->group_cols_array[1], $sp_group_value, $currency_id, true);
                                                    // $group_records_count = count($this->result_array[$sp_group_value]);
                                                    $group_records_count = $custom_field_values["crmid_count"];

                                                    $group_info_fld_str = $this->columns_array[$this->group_cols_array[1]];
                                                    $group_info_headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", $this->group_cols_array[1], $group_info_fld_str);

                                                    $group_info_tr .= "<tr><td class='rpt4youGrpHead' colspan='" . count($header) . "' style='text-align:left;background-color:#EEEEEE;' nowrap ><b>";
                                                    $group_info_tr .= "$group_info_headerLabel = $group_info_tr_value ($group_records_count): ";
                                                    $group_info_tr .= implode(";", $detail_row_info) . "</b></td></tr>";
                                                    $group_info_tr_added = true;
                                                }
                                            }
                                            /**                                     * **  GROUP INFO ROW DISPLAY for GROUPS In case selected columns not empty END *** STANDARD REPORT * */
                                        } else {
                                            // Group Value (Count) Info in case summaries columns empty start
                                            if ($display_groupinfo_row === true) {
                                                $sp_group_value = $custom_field_values[$this->group_cols_array[1]];
                                                if ($sp_group_value != $direct_group_info_tr_value) {
                                                    $direct_group_info_tr_value = $sp_group_value;
                                                    $group_info_tr_value = $this->getFldNumberFormat($this->group_cols_array[1], $sp_group_value, $currency_id);
                                                    // $group_records_count = count($this->result_array[$sp_group_value]);
                                                    $group_records_count = $custom_field_values["crmid_count"];
                                                    $group_info_fld_str = $this->columns_array[$this->g_flds[0]];
                                                    $group_info_headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", $this->g_flds[0], $group_info_fld_str);

                                                    $group_info_tr = "<tr><td class='rpt4youGrpHead' colspan='" . count($header) . "' style='text-align:left;background-color:#EEEEEE;' nowrap >";
                                                    $group_info_tr .= "<b>$group_info_headerLabel = $group_info_tr_value ($group_records_count)</b>";
                                                    $group_info_tr .= "</td></tr>";
                                                    $group_info_tr_added = true;
                                                }
                                            }
                                            // Group Value (Count) Info in case summaries columns empty end

                                            /**                                     * **  DEFAULT VALUE DISPLAY *** * */
                                            $fld_style = $this->getFldStyleString($fld_style_arr);
                                            $valtemplate_tr .= "<td class='rpt4youGrpHead'  style='$fld_style $bg_color' nowrap >" . $this->getFldNumberFormat($fld->name, $fieldvalue, $currency_id) . "$currency_string</td>";
                                        }
                                        /*                                 * ** DISPLAY   timeline_type2 == "cols"  *** */
                                        if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                            $g_ri = 1;

                                            foreach ($group2_headers as $group_h_key => $group_h_arr) {
                                                if (isset($group2_arr["values"][$group_h_key]) && !empty($group2_arr["values"][$group_h_key])) {
                                                    // foreach ($group2_arr["values"][$group_h_key] as $gv_arr) {
                                                    for ($gv_i = 0; $gv_i < count($group_h_arr); $gv_i++) {
                                                        if (isset($group2_arr["values"][$group_h_key][$gv_i]) && !empty($group2_arr["values"][$group_h_key][$gv_i])) {
                                                            $gv_arr = $group2_arr["values"][$group_h_key][$gv_i];
                                                            // $txt_align = $gv_arr["text-align"];
                                                            $fld_style = $gv_arr["fld_style"];
                                                            $gv_value = $gv_arr["value"];
                                                            $gv_fld_name = strtolower($gv_arr["fld_name"]);
                                                            if ($gv_fld_name != "") {
                                                                if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                                    $grouping_totals[$group_h_key][$gv_fld_name][$currency_id][] = $gv_value;
                                                                } else {
                                                                    $grouping_totals[$group_h_key][$gv_fld_name][] = $gv_value;
                                                                }
                                                            }
                                                            $fw_weight = "";
                                                            if ($group_h_key == "LBL_GROUPING_TOTALS") {
                                                                $fw_weight = "font-weight:bold;";
                                                            }

                                                            // chart data population start
                                                            if($this->charts["x_group"]=="group2" && in_array($gv_fld_name, $this->charts["charts_ds_columns"]) && $group_h_key!="LBL_GROUPING_TOTALS"){
                                                                $this->setDataseriesArray($group_h_key, $gv_value, $currency_id, $gv_fld_name, $group_value);
                                                            }

                                                            if($this->charts["x_group"]=="group1" && in_array($gv_fld_name, $this->charts["charts_ds_columns"]) && $group_h_key=="LBL_GROUPING_TOTALS"){
                                                                $this->setDataseriesArray($group_value, $gv_value, $currency_id, $gv_fld_name);
                                                            }
                                                            // chart data population end
                                                            $valtemplate_tr .= "<td class='rpt4youGrpHead' style='$fld_style $fw_weight' nowrap >" . $this->getFldNumberFormat($gv_fld_name, $gv_value, $currency_id) . "</td>";
                                                            $g_ri++;
                                                        }
                                                    }
                                                } else {
                                                    if ($group_h_key>0 && isset($group2_headers[$group_h_key]) && !empty($group2_headers[$group_h_key]) && $group2_headers[$group_h_key]!="") {
                                                        for ($gv_i = 0; $gv_i < count($group_h_arr); $gv_i++) {
                                                            $valtemplate_tr .= "<td class='rpt4youGrpHead' style='text-align:center;' nowrap > </td>";
                                                            $g_ri++;
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        /* TOTALS CALCULATION START */
                                        if (isset($this->columns_array[$fld->name])) {
                                            $columns_array_lbl = $this->columns_array[$fld->name];
                                        } elseif ($this->columns_array[$fld->table . "." . $fld->name]) {
                                            $columns_array_lbl = $this->columns_array[$fld->table . "." . $fld->name];
                                        } elseif ($fld->table != "") {
                                            $columns_array_lbl = $fld->table . "." . $fld->name;
                                        } else {
                                            $columns_array_lbl = $fld->name;
                                        }

                                        $TheaderLabel = $this->getHeaderLabel($reportid, "CT", $fld->name, $columns_array_lbl);

                                        $fld_totals_key = $fld->name;

                                        // $to_totals_res = $this->setToTotalsArray($to_totals_res,$fld_totals_key,$fieldvalue,$to_totals_array,$currency_id);
                                        $this->to_totals_res = $this->setToTotalsArray($noofrows,$this->to_totals_res, $fld_totals_key, $fieldvalue, $this->to_totals_array, $currency_id);
                                        /* TOTALS CALCULATION END */
                                    }
                                    $first_td = false;
                                    // ITS4YOU-END 17. 2. 2014 10:23:33
                                    $gc_i++;
                                }
                                // ITS4YOU-CR SlOl 19. 5. 2016 6:55:12
                                if(!empty($this->cc_values_array)){
                                    $valtemplate_tr .= $this->displayCustomCalculations($currency_id);
                                }
//                                ITS4YouReports::sshow($header);
                                // ITS4YOU-END
                                // timeline_type3"]=="cols" REPORT
                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && $this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                    $gcols3_valtemplate = "";
                                    $tl_g3_colspan = 1;

                                    if (empty($this->group_data_array[$group_value])) {
                                        $this->setGroupDataArray($group_value, $currency_id);
                                    }
                                    $group_value = html_entity_decode($group_value, ENT_QUOTES, $default_charset);
                                    if (isset($currency_id) && $currency_id != "") {
                                        $group_data_array = $this->group_data_array[$group_value][$currency_id];
                                    } else {
                                        $group_data_array = $this->group_data_array[$group_value];
                                    }

                                    // foreach($this->result_array[$group_value] as $group_row_key => $group_row_array){
                                    foreach ($group_data_array as $group_row_key => $group_row_array) {
                                        // continue in case summary filter will not contain group values
                                        /* if(!array_key_exists($group_row_key,$this->group_data_array[$group_value])){
                                          continue;
                                          } */
                                        $gcols3_valtemplate .= "</tr><tr>";
                                        $bg_color_val = $this->g_colors[2];
                                        $bg_color = "background-color:$bg_color_val;";
                                        // this is possible only in case group 2 = rows and group 3 = cols, so static rpt4youGrpHead_1 is ok here
                                        $gcols3_valtemplate .= "<td class='rpt4youGrpHead_1' style='text-align:left;' nowrap >" . $group_row_key . "</td>";

                                        $g_ri = 1;

                                        foreach ($header as $header_f_key => $header_f_arr) {
                                            $headerLabel = $header_f_arr["label"];
                                            if (!in_array($headerLabel, array("LBL_GROUPING_TOTALS")) && $header_f_key !== 0) {
                                                if (isset($group_data_array[$group_row_key][$headerLabel])) {
                                                    foreach ($group_data_array[$group_row_key][$headerLabel] as $fld_key => $fld_value) {
                                                        $clear_fld_name_arr = explode("_", $fld_key);
                                                        $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                        unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                        $clear_fld_name = implode("_", $clear_fld_name_arr);

                                                        if (in_array($fld_key, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                                            continue;
                                                        }
                                                        if ($this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                                            continue;
                                                        }

                                                        //$txt_align = $this->getFldAlignment($fld_key, $fld_value);
                                                        $fld_style_arr = $this->getFldStyle($fld_key, $fld_value);
                                                        $calculation_arr = explode("_", $fld_key);
                                                        $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                        if ($calculation_type == "avg") {
                                                            $ct_fldstr = "";
                                                            $ct_fi = (count($calculation_arr) - 1);
                                                            for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                                if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                    $ct_fldstr .= "_";
                                                                }
                                                                $ct_fldstr .= $calculation_arr[$ct_i];
                                                            }
                                                            $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                            if ($currency_id != "") {
                                                                $group_totals[$group_value][$currency_id][$group_row_key][$fld_key] = $group_totals[$group_value][$currency_id][$group_row_key][$ct_fldstr_sum];
                                                                $group_f_totals[$group_value][$currency_id][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id]["row_totals"][$fld_key][] = $fld_value;
                                                            } else {
                                                                $group_totals[$group_value][$group_row_key][$fld_key] = $group_totals[$group_value][$group_row_key][$ct_fldstr_sum];
                                                                $group_f_totals[$group_value][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value]["row_totals"][$fld_key][] = $fld_value;
                                                            }
                                                        } else {
                                                            if ($currency_id != "") {
                                                                $group_totals[$group_value][$currency_id][$group_row_key][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id]["row_totals"][$fld_key][] = $fld_value;
                                                            } else {
                                                                $group_totals[$group_value][$group_row_key][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value]["row_totals"][$fld_key][] = $fld_value;
                                                            }
                                                        }

                                                        // chart data population start
                                                        /*
                                                        if($this->charts["x_group"]=="group2" && in_array($fld_key, $this->charts["charts_ds_columns"])){
                                                            $this->setDataseriesArray($group_row_key, $fld_value, $currency_id, $fld_key, $group_value);
                                                        }
                                                        */
                                                        // chart data population end

                                                        $fld_style = $this->getFldStyleString($fld_style_arr);
                                                        $gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='$fld_style $bg_color' nowrap >" . $this->getFldNumberFormat($fld_key, $fld_value, $currency_id) . "</td>";
                                                        $g_ri++;
                                                    }
                                                } else {
                                                    for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                        $gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='text-align:center;font-weight:bold;$bg_color' nowrap > </td>";
                                                        $g_ri++;
                                                    }
                                                }
                                            } elseif ($headerLabel == "LBL_GROUPING_TOTALS") {
                                                $count_value = 0;
                                                if ($currency_id != "") {
                                                    $group_totals_array = $group_totals[$group_value][$currency_id][$group_row_key];
                                                } else {
                                                    $group_totals_array = $group_totals[$group_value][$group_row_key];
                                                }
                                                foreach ($group_totals_array as $g_t_key => $g_t_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);
                                                    $fld_style_arr = $this->getFldStyle($g_t_key, $fld_value);
                                                    $fld_style = $this->getFldStyleString($fld_style_arr);
                                                    $gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='$fld_style font-weight:bold;$bg_color' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
                                                    $g_ri++;
                                                }
                                            }
                                        }
                                    }
                                    $bg_color_val1 = $this->g_colors[1];
                                    $bg_color1 = "background-color:$bg_color_val1;";
                                    foreach ($header as $header_f_key => $header_f_arr) {
                                        $headerLabel = $header_f_arr["label"];
                                        if ($currency_id != "") {
                                            $group_totals_f_array = $group_f_totals[$group_value][$currency_id];
                                        } else {
                                            $group_totals_f_array = $group_f_totals[$group_value];
                                        }
                                        if (!in_array($headerLabel, array("LBL_GROUPING_TOTALS")) && $header_f_key !== 0) {
                                            if (isset($group_totals_f_array[$headerLabel]) && !empty($group_totals_f_array[$headerLabel])) {
                                                $count_value = 0;
                                                foreach ($group_totals_f_array[$headerLabel] as $g_t_key => $g_t_array) {
                                                    $clear_fld_name_arr = explode("_", $g_t_key);
                                                    $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                    unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                    $clear_fld_name = implode("_", $clear_fld_name_arr);

                                                    if (in_array($g_t_key, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                                        continue;
                                                    }
                                                    if ($this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                                        continue;
                                                    }
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        $g_t_array = $group_totals_f_array[$headerLabel][$ct_fldstr_sum];
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);
                                                    $fld_style_arr = $this->getFldStyle($g_t_key, $fld_value);

                                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                        $grouping_totals[$headerLabel][$g_t_key][$currency_id][] = $fld_value;
                                                    } else {
                                                        $grouping_totals[$headerLabel][$g_t_key][] = $fld_value;
                                                    }
                                                    // charts array population start
                                                    if ($g_t_key == $this->charts["charts_ds_column"]) {
                                                        // SPECIAL CHART DATA POPULATION FOR G3 COLS
                                                        $cols_alias = "";
                                                        if ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                                            $cols_alias = $this->g_flds[1];
                                                        } elseif ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                                            $cols_alias = $this->g_flds[2];
                                                        }
                                                        if (!isset($this->ch_array["dataseries_label"])) {
                                                            $this->setDataseriesLabel($cols_alias);
                                                        }
                                                        //$dataseries_label_key = $this->getHeaderLabel($this->report_obj->record, "SM", $this->g_flds[2], $this->columns_array[$this->g_flds[2]]);
                                                        //$this->setChArrayValues("dataseries_label",'key',$dataseries_label_key);
                                                        if ($currency_id != "") {
                                                            $ch_subkey = $headerLabel . " (" . $this->currency_symbols[$currency_id] . ")";
                                                        } else {
                                                            $ch_subkey = $headerLabel;
                                                        }
                                                        // addToSubvalChArrayValues($ch_key,$ch_subkey,$ch_value,$option_key="",$currency_id=""){
                                                        $this->addToSubvalChArrayValues("dataseries", $headerLabel, $fld_value, $group_value, $currency_id);
                                                        if ($this->ch_array["charttype"] != "horizontal") {
                                                            $this->setChArrayValues("dataseries", $ch_subkey, $fld_value);
                                                            $this->setHChArrayValues("hch_dataseries", $group_value, $fld_value,$currency_id);
                                                        }
                                                    }
                                                    // charts array population end
                                                    if($this->charts["x_group"]=="group1" && in_array($g_t_key, $this->charts["charts_ds_columns"])){
                                                        $this->setDataseriesArray($group_value, $fld_value, $currency_id, $g_t_key);
                                                    }
                                                    if($this->charts["x_group"]=="group2" && in_array($g_t_key, $this->charts["charts_ds_columns"])){
                                                        $this->setDataseriesArray($headerLabel, $fld_value, $currency_id, $g_t_key, $group_value);
                                                    }
                                                    $fld_style = $this->getFldStyleString($fld_style_arr);
                                                    $valtemplate_tr .= "<td class='rpt4youGrpHead'  style='$fld_style $bg_color1' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
                                                }
                                            } else {
                                                for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                    $valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:center;$bg_color1' nowrap > </td>";
                                                }
                                            }
                                        } elseif ($headerLabel == "LBL_GROUPING_TOTALS") {
                                            $count_value = 0;
                                            if (isset($group_totals_f_array["row_totals"]) && !empty($group_totals_f_array["row_totals"])) {
                                                foreach ($group_totals_f_array["row_totals"] as $g_t_key => $g_t_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "avg") {
                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        $g_t_array = $group_totals_f_array["row_totals"][$ct_fldstr_sum];
                                                    }
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);
                                                    $fld_style_arr = $this->getFldStyle($g_t_key, $fld_value);
                                                    $fld_style = $this->getFldStyleString($fld_style_arr);

                                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                        $grouping_totals[$headerLabel][$g_t_key][$currency_id][] = $fld_value;
                                                    } else {
                                                        $grouping_totals[$headerLabel][$g_t_key][] = $fld_value;
                                                    }
                                                    $valtemplate_tr .= "<td class='rpt4youGrpHead'  style='$fld_style font-weight:bold;$bg_color1' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
                                                }
                                            } else {
                                                for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                    $valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:center;$bg_color1' nowrap > </td>";
                                                }
                                            }
                                        }
                                    }
                                    $valtemplate_tr .= $gcols3_valtemplate;
                                }
                                // details for group 1
                                if ($this->generate_type == "grouping" && count($this->g_colors) == 1) {
                                    if (!empty($selectedcolumns_arr)) {
                                        $valtemplate_tr .= $this->returnGroupDetailRecordsNew($group_value, $y, $selectedcolumns_arr, $currency_id);
                                    }
                                }
                                $valtemplate_tr .= "</tr>";
                                // adding tr html of Group Value (Count) Info in case summaries columns empty start
                                if ($group_info_tr != "") {
                                    $valtemplate .= $group_info_tr;
                                }
                                // adding tr html of Group Value (Count) Info in case summaries columns empty end
                                $valtemplate .= $valtemplate_tr;
                                //   ROWS REPORT
                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && ($this->report_obj->reportinformations["timeline_type3"] != "cols" || $this->report_obj->reportinformations["timeline_type3"] == "none")) {
                                    $group2_html = $this->getSubGroupRow($group_value, $currency_id);
                                    $valtemplate .= $group2_html;
                                }

                                $lastvalue = $newvalue;
                                $secondvalue = $snewvalue;
                                $thirdvalue = $tnewvalue;
                                $arr_val[] = $arraylists;
                                set_time_limit($php_max_execution_time);
                                $f_i++;

                                // GROUPING TOTALS START
                                if ($this->display_group_totals == true && $this->generate_type == "grouping" && $f_i == $noofrows && $this->report_obj->reportinformations["timeline_type2"] != "cols" && ($this->report_obj->reportinformations["timeline_type3"] != "cols" || $this->report_obj->reportinformations["timeline_type3"] == "none")) {
                                    $bg_color_val = $this->g_colors[0];
                                    $bg_color = "background-color:$bg_color_val;";
                                    $valtemplate .= "<tr>";
                                    $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:left;" . $this->grouping_totals_bg_color . "' nowrap >" . getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You()) . "</td>";
                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                        $to_display = array();
                                        foreach ($grouping_totals AS $g_t_key => $currency_array) {
                                            foreach ($currency_array AS $currency_key => $g_t_array) {
                                                $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);

                                                //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                $fld_style_arr = $this->getFldStyle($g_t_key, $g_t_value);
                                                $fld_style = $this->getFldStyleString($fld_style_arr);

                                                $to_display[$g_t_key]["values"] .= $this->getFldNumberFormat($g_t_key, $g_t_value, $currency_key) . "<br>";
                                                //$to_display[$g_t_key]["textalign"] = $txt_align;
                                                $to_display[$g_t_key]["fld_style"] = $fld_style;
                                            }
                                        }
                                        foreach ($to_display AS $td_key => $td_arr) {
                                            //$txt_align = $td_arr["textalign"];
                                            $fld_style = $td_arr["fld_style"];
                                            $td_value = $td_arr["values"];
                                            $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='$fld_style " . $this->grouping_totals_bg_color . "' nowrap >" . $td_value . "</td>";
                                        }
                                    } else {
                                        foreach ($grouping_totals AS $g_t_key => $g_t_array) {
                                            $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                            //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                            $fld_style_arr = $this->getFldStyle($g_t_key, $g_t_value);
                                            $fld_style = $this->getFldStyleString($fld_style_arr);
                                            $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='$fld_style " . $this->grouping_totals_bg_color . "' nowrap >" . $this->getFldNumberFormat($g_t_key, $g_t_value) . "</td>";
                                        }
                                    }
                                    $valtemplate .= "</tr>";
                                }
                                $f_r_i++;
                                // GROUPING TOTALS END
                            } while ($custom_field_values = $adb->fetch_array($result));
                            // GROUPING TOTALS START
                            // GROUPING TOTAL FOR COLS START
                            if ($this->generate_type == "grouping" && (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols")) && !empty($grouping_totals)) {
                                $bg_color = "background-color:$bg_color_val;";
                                $valtemplate .= "<tr>";
                                $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals' nowrap style='text-align:left;" . $this->grouping_totals_bg_color . "' nowrap >" . getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You()) . "</td>";
								// foreach ($grouping_totals as $grouping_totals_key => $grouping_totals_array) {
                                foreach ($header as $gh_i => $gh_array) {
                                    if ($gh_i > 0 && isset($grouping_totals[$gh_array["raw"]])) {
                                        $grouping_totals_array = $grouping_totals[$gh_array["raw"]];
                                        foreach ($grouping_totals_array AS $g_t_key => $g_t_array) {
                                            if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)) {
                                                $g_t_value_display_arr = array();
                                                foreach ($g_t_array as $g_ft_currency_id => $g_ft_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        $count_value = 0;
                                                        foreach ($g_ft_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {

                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        if (!empty($grouping_totals_array[$ct_fldstr . "_sum"][$g_ft_currency_id])) {
                                                            $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        } else {
                                                            $ct_fldstr_sum = $ct_fldstr . "_SUM";
                                                        }
                                                        $g_ft_array = $grouping_totals_array[$ct_fldstr_sum][$g_ft_currency_id];
                                                        $g_t_value = (array_sum($g_ft_array) / $count_value);
                                                    } else {
                                                        $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_ft_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                    $fld_style_arr = $this->getFldStyle($g_t_key, $g_t_value);
                                                    $fld_style = $this->getFldStyleString($fld_style_arr);
                                                    $g_t_value_display_arr[] = $this->getFldNumberFormat($g_t_key, $g_t_value, $g_ft_currency_id);
                                                }
                                                $g_t_value_display = implode("<br>", $g_t_value_display_arr);
                                            } else {
                                                $calculation_arr = explode("_", $g_t_key);
                                                $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                if ($calculation_type == "count") {
                                                    $count_value = 0;
                                                    if (!empty($g_t_array)) {
                                                        foreach ($g_t_array as $count_key => $count_val) {
                                                            if (is_array($count_val)) {
                                                                $count_value += array_sum($count_val);
                                                            } else {
                                                                $count_value += $count_val;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($calculation_type == "avg") {
                                                    $ct_fldstr = "";
                                                    $ct_fi = (count($calculation_arr) - 1);
                                                    for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                        if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                            $ct_fldstr .= "_";
                                                        }
                                                        $ct_fldstr .= $calculation_arr[$ct_i];
                                                    }
                                                    if (!empty($grouping_totals_array[$ct_fldstr . "_sum"])) {
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                    } else {
                                                        $ct_fldstr_sum = $ct_fldstr . "_SUM";
                                                    }
                                                    $g_t_array = $grouping_totals_array[$ct_fldstr_sum];
                                                    $g_t_value = (array_sum($g_t_array) / $count_value);
                                                } else {
                                                    $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                }
                                                //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                $fld_style_arr = $this->getFldStyle($g_t_key, $g_t_value);
                                                $fld_style = $this->getFldStyleString($fld_style_arr);
                                                $g_t_value_display = $this->getFldNumberFormat($g_t_key, $g_t_value);
                                            }
                                            if ($grouping_totals_key == "LBL_GROUPING_TOTALS") {
                                                $valtemplate_totals .= "<td class='rpt4youGrpHeadGroupTotals'  style='$fld_style " . $this->grouping_totals_bg_color . "' nowrap >" . $g_t_value_display . "</td>";
                                            } else {
                                                $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='$fld_style " . $this->grouping_totals_bg_color . "' nowrap >" . $g_t_value_display . "</td>";
                                            }
                                        }
                                    } elseif ($gh_i > 0) {
                                        for ($gti = 0; $gti < count($this->sum_col_sql_a); $gti++) {
                                            $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='$fld_style " . $this->grouping_totals_bg_color . "' nowrap >0</td>";
                                        }
                                    }
                                }
                                $valtemplate .= $valtemplate_totals;
                                $valtemplate .= "</tr>";
                            }
                        } else {
                            $headers_count = count($header);
                            $valtemplate .= "<tr>";
                            $valtemplate .= "<td class='rpt4youGrpHead' colspan='$headers_count' nowrap style='text-align:left;' nowrap ><b>" . getTranslatedString("LBL_NO_DATA_TO_DISPLAY", $this->getCurrentModule4You()) . "</b></td>";
                            $valtemplate .= "</tr>";
                        }
                        // GROUPING TOTAL FOR COLS START
                        // GROUPING TOTALS END
                        $header_f = "";
                        if ($populate_detail_header) {
                            $header_f = $this->getDetailHeader($header, $group2_headers);
                        }
                        $report_html .= $header_f;
                        $report_html .= '</tr><tr>';
                        $report_html .= $valtemplate;
                        $report_html .= '</table></div>';
                        if (!empty($this->to_totals_res)) {
                            $report_html .= '<br>';
                            $report_html .= $this->getTotalsHTML($this->to_totals_res);
                        }
                        if($this->time_debug===true){
                            $this->report_obj->getR4UDifTime("HTML Generated / Before PDF",true);
                        }

                        if ('CHARTS' !== $this->outputformat && !$this->create_pdf_schedule) {
                            $report_html = str_replace("<@ReportHeaderInfo@>", $this->report_html_headerInfo, $report_html);
                        }

                        $this->setReportFileInfo();

                        //$this->create_pdf_schedule = true;
                        if($this->create_pdf_schedule){
                            $this->createPDFFileForScheduler($report_html);
                        }

                        if($this->time_debug===true){
                            $this->report_obj->getR4UDifTime("After PDF",true);
                        }

                        if (is_writable($this->temp_files_path)) {
                            //*** generate charts to report_html ***//
                            if (!empty($this->charts["charttypes"]) && !empty($this->ch_array["dataseries"])) {
                                if(!$request->has('mode') || 'showDetailViewByMode' === $request->get('mode')) {
									if (!empty($this->charts) && 'top' !== $this->report_obj->reportinformations['charts'][1]['chart_position']) {
	                                    $report_html_chart .= $this->addHighChartDiv();
	                                }
                                }
                                $report_html_chart .= $this->getReportHighCharts($export_pdf_format, $currency_id);
                                if($this->outputformat === "CHARTS"){
                                    return $report_html_chart;
                                } else {
                                    $report_html .= $report_html_chart;
                                }
                            } elseif ('ShowWidget' === $request->get('view')) {
                                return "$(function () {
                                        $('#reports4you_widget_".$this->chartPosition.$this->report_obj->record."').html('".vtranslate("LBL_NO_DATA_TO_DISPLAY", $this->getCurrentModule4You())."')
                                    })
                                    ";
                            }
                        } else {
                            $headers_count = count($header);
                            $report_html .= "<tr>";
                            $report_html .= "<td class='rpt4youGrpHead' colspan='$headers_count' nowrap style='width:100%;text-align:center;font-size:1.3em;'  nowrap ><b>" . getTranslatedString("Test_Not_WriteAble", $this->getCurrentModule4You()) . "</b></td>";
                            $report_html .= "</tr>";
                        }

                        if ('ShowWidget' !== $request->get('view') && $request->has('module')==true && $request->get('module')=='ITS4YouReports' && ($request->has('mode')!=true || $request->get('mode')!='widget')){
                            $this->setNoOfRows($noofrows);
                        }

                        if($this->time_debug===true){
                            $this->report_obj->getR4UDifTime("After CHARTS",true);
                        }
                        if ($directOutput) {
                            echo $report_html;
                        } else {
                            $return_data[] = $report_html;
                            $return_data[] = $noofrows;
                            $return_data[] = $sSQL;
                            return $return_data;
                        }
                    }

                }elseif($outputformat == "XLS") {
    // oldoxls
                    if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                        $picklistarray = $this->getAccessPickListValues();
                    }
                    $data = array();
                    if ($result) {
                        $y = ($adb->num_fields($result) - 1);
                        $t_y = $y;

                        $custom_field_values = $adb->fetch_array($result);

                        $column_definitions = $adb->getFieldsDefinition($result);
                        // HEADERS
                        if (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols")) {
                            if ($this->report_obj->reportinformations["Group3"] && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                $agi = 2;
                                foreach ($this->sum_col_sql_a as $column_str => $calculation_arr) {
                                    foreach ($calculation_arr as $calculation_type) {

                                        // $calculation_type = $calculation_arr[0];
                                        $label_db_key = "$column_str:$calculation_type";
                                        if ($this->report_obj->in_multiarray($label_db_key, $this->summaries_columns, "columnname") !== true) {
                                            continue;
                                        }
                                        $fld_sql_str_array = explode(" AS ", $this->columns_array[$column_str]["fld_sql_str"]);
                                        $fld_str = $fld_sql_str_array[0];
                                        $fld_str_as = $fld_sql_str_array[1] . "_$calculation_type";
                                        $g_data_key_lbl = $this->getHeaderLabel($this->report_obj->record, "SM", $fld_str_as, $label_db_key);
                                        $sum_columns_bg = $this->g_colors[1];
                                        $sum_columns_labels[] = array("style" => " background-color:$sum_columns_bg;font-weight:bold; ", "label" => $g_data_key_lbl);

                                        if (in_array($calculation_type, self::$summaryAmountColumns)) {
                                            $sum_columns_labels[] = array('style' => " background-color:$sum_columns_bg;font-weight:bold; ", 'label' => vtranslate('LBL_CURRENCY'),);
                                        }
                                    }
                                }
                            } else {
                                $agi = 1;
                            }
                            for ($x = 0; $x < 1; $x++) {
                                $fld = $adb->field_name($result, $x);
                                $is_hid = strpos($fld->name, "_hid");
                                if ($is_hid === false && !in_array($fld->name, $this->skip_fields)) {
                                    if (!in_array($fld->name, $group_cols)) {
                                        $header_style = "";
                                        $header_style .= " style='" . $this->header_style . "' ";

                                        //$header[] = array("style" => $header_style, "label" => "&nbsp;",);
                                        $GroupsHeaderLabel = $this->getGroupsHeaderLabelStr();
                                        $header[] = array('style' => $header_style, 'label' => $GroupsHeaderLabel,);
                                        if (in_array($this->columns_array['uitype_' . $fld->name], self::$currencyUiTypes)) {
                                            $header[] = array('style' => $header_style, 'label' => vtranslate('LBL_CURRENCY'),);
                                        }

                                        $headercols = $this->getColsHeaders($header_style, $agi);
                                        foreach ($headercols as $header_arr) {
                                            $header[] = $header_arr;
                                            $group2_headers[] = $sum_columns_labels;
                                        }
                                    }
                                }
                                // END
                            }
                        } else {
                        	if (count($this->g_flds) == 1 && count($this->detail_selectedcolumns_arr) > 0) {

								$header_style = " style='" . $this->header_style . "' ";

								foreach ($this->detail_selectedcolumns_arr as $detailColumnArr) {

                                    $header[] = array("style" => $header_style, "label" => $detailColumnArr['fieldlabel'],);

                                    $fldName = $this->detail_columns_array[$detailColumnArr['fieldcolname']]['fld_alias'];
                                    if (self::isAddCurrencyField($fldName, $this->detail_columns_array)) {
                                        $header[] = array('style' => $header_style, 'label' => vtranslate('LBL_CURRENCY'),);
                                    }
                                }
							} else {

	                            $crm_id_count_done = false;
	                            for ($x = 0; $x <= $y; $x++) {
	                                $fld = $adb->field_name($result, $x);
	                                $is_hid = strpos($fld->name, "_hid");

	                                $clear_fld_name_arr = explode("_", $fld->name);
	                                $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
	                                unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
	                                $clear_fld_name = implode("_", $clear_fld_name_arr);

	                                if (in_array($fld->name, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
	                                    continue;
	                                }
	                                if (!empty($selectedcolumns_arr) && in_array($fld->name, $this->g_flds) && isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
	                                    if ($x == 0 && !$this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") && $this->generate_type != "grouping") {
	                                        $group_value = $custom_field_values[$fld->name];
	                                        continue;
	                                    }
	                                }
	                                // ITS4YOU-CR SlOl 4. 7. 2016 13:00:53 - MISSING COUNT COLUMN IN RESULT
	                                if($crm_id_count_done!=true && $clear_fld_name=="crmid" && $x > 0 && $this->generate_type == "grouping" && $this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type"!=""){
	                                    $explode_col_str_arr = explode(":", $this->columns_array[$clear_fld_name]);
	                                    $lbl_col_str = explode("_",$explode_col_str_arr[2]);
	                                    $explode_col_str_arr[2] = $lbl_col_str[0]."_LBL_RECORDS";
	                                    $check_col_str = implode(":", $explode_col_str_arr);
	                                    if($this->report_obj->in_multiarray($check_col_str . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true){
	                                        continue;
	                                    }
	                                    $crm_id_count_done = true;

	                                }elseif ($x > 0 && $this->generate_type == "grouping" && $this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
	                                    continue;
	                                }

	                                if ($is_hid === false && !in_array($fld->name, $this->skip_fields) && !in_array($clear_fld_name, $this->skip_fields)) {
	                                    if (in_array($fld->name, $group_cols) !== true) {
	                                        // ITS4YOU-CR SlOl 13. 3. 2014 13:37:11
	                                        if ($this->generate_type == "grouping") {
	                                            $summaries_fld_test = explode("_", $fld->name);
	                                            $smft_lk = count($summaries_fld_test) - 1;
	                                            if (in_array(strtolower($summaries_fld_test[$smft_lk]), $this->calculation_type_array)) {
	                                                $calculation_type = $summaries_fld_test[$smft_lk];
	                                                $fld_name = "";
	                                                for ($index = 0; $index < (count($summaries_fld_test)); $index++) {
	                                                    if ($fld_name != "") {
	                                                        $fld_name .= "_";
	                                                    }
	                                                    $fld_name .= $summaries_fld_test[$index];
	                                                }
	                                            } else {
	                                                $fld_name = $fld->name;
	                                            }
	                                        } else {
	                                            $fld_name = $fld->name;
	                                        }
	                                        $s_type = "SC";
	                                        if ($this->generate_type == "grouping" && isset($this->sm_columns_array) && !empty($this->sm_columns_array) && array_key_exists($fld_name, $this->sm_columns_array)) {
	                                            $s_type = "SM";
	                                            $columns_array_lbl = $this->sm_columns_array[$fld_name] . ":$calculation_type";
	                                        } elseif ($this->columns_array[$fld->table . "." . $fld_name]) {
	                                            $columns_array_lbl = $this->columns_array[$fld->table . "." . $fld_name];
	                                        } elseif (isset($this->columns_array[$fld_name])) {
	                                            $columns_array_lbl = $this->columns_array[$fld_name];
	                                        } else {
	                                            $columns_array_lbl = "";
	                                        }
	                                        // we will skipp 2,3 group by values to display later correct values !!
	                                        if ($this->generate_type == "grouping" && in_array($fld_name, $group_cols)) {
	                                            continue;
	                                        }

	                                        if (isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
	                                            if (!empty($selectedcolumns_arr)) {
	                                                if ($this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") !== true && !in_array($fld->name, $this->g_flds)) {
	                                                    continue;
	                                                }
	                                            }
	                                        }
	                                        if($this->generate_type != "grouping" && !isset($this->columns_array[$fld->name])){
	                                            continue;
	                                        }

	                                        // $this->getQFArray();

	                                        $headerLabel = $this->getHeaderLabel($this->report_obj->record, $s_type, $fld_name, $columns_array_lbl);

	                                        if (count($this->g_colors) > 1 && $x == 0 && $this->generate_type == "grouping") {
	                                            // $headerLabel = "&nbsp;";
	                                            $headerLabel = $this->getGroupsHeaderLabelStr();
	                                        }

	                                        $header_style = '';

                                            $header_style .= ' style="' . $this->header_style . '" ';

                                            $header[] = array('style' => $header_style, 'label' => $headerLabel,);

                                            if (self::isAddCurrencyField($fld->name, $this->columns_array)) {
                                                $header[] = array('style' => $header_style, 'label' => vtranslate('LBL_CURRENCY'),);
                                            } elseif ('tabular' !== $this->reports4you_type) {

                                                $type_col_array = explode('_', $fld->name);
                                                $lk = count($type_col_array)-1;
                                                if (in_array($type_col_array[$lk], self::$summaryAmountColumns)) {
                                                    $header[] = array('style' => $header_style, 'label' => vtranslate('LBL_CURRENCY'),);
                                                }
                                            }
                                            // ITS4YOU-END 14. 3. 2014 8:33:46
	                                    }
	                                }
								}
                                // END
                            }
                        }

                        // ITS4YOU-CR SlOl | 12.8.2014 11:40 variable to populate header for record details
                        $populate_detail_header = true;
                        // ITS4YOU-CR SlOl | 12.8.2014 11:40 variable to populate header for record details end
                        $display_groupinfo_row = false;

                        $this->group_data_array = array();
                        $grouping_totals = $group2_values = array();

        //                $this->setChartsColumns();

                        if ($noofrows > 0) {
                            $xls_ri = 0;
                            $xls_fri = 0;

                            $f_i = $f_r_i = 0;
                            $group_info_tr_value = $old_gv = ""; // tr_html of Group Value (Count) Info in case summaries columns empty start

                            do {
                            $r_data = array();
                                $arraylists = Array();
                                if (count($this->groupslist) == 1) {
                                    $newvalue = $custom_field_values[0];
                                } elseif (count($this->groupslist) == 2) {
                                    $newvalue = $custom_field_values[0];
                                    $snewvalue = $custom_field_values[1];
                                } elseif (count($this->groupslist) == 3) {
                                    $newvalue = $custom_field_values[0];
                                    $snewvalue = $custom_field_values[1];
                                    $tnewvalue = $custom_field_values[2];
                                }
                                if ($newvalue == "")
                                    $newvalue = "-";

                                if ($snewvalue == "")
                                    $snewvalue = "-";

                                if ($tnewvalue == "")
                                    $tnewvalue = "-";

                                // if($this->generate_type == "grouping"){
                                //  $group_value = $custom_field_values[$this->g_flds[0]];
                                //  }

                                //$valtemplate_tr = "<tr>";

                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols"))) {
                                    $y = 1;
                                }

                                $currency_id = "";
                                if (isset($custom_field_values["currency_id"]) && $custom_field_values["currency_id"] != "") {
                                    $currency_id = $custom_field_values["currency_id"];
                                }
                                if (isset($custom_field_values["invoicing_currency_id"]) && $custom_field_values["invoicing_currency_id"] != "") {
                                    $currency_id = $custom_field_values["invoicing_currency_id"];
                                }

                                $this->data_record_id = "";
                                if (isset($custom_field_values["record_id"]) && $custom_field_values["record_id"] != "") {
                                    $this->data_record_id = $custom_field_values["record_id"];
                                }

                                // ITS4YOU-CR SlOl | 12.8.2014 11:40 variables used for group info rows
                                $detail_row_info = array();
                                // ITS4YOU-END 12.8.2014 11:41
                                // Variables used for Group Value (Count) Info in case summaries columns empty start
                                $group_info_tr = "";
                                $group_info_tr_added = false;
                                $gc_i = 0;

                                // Set up group_value for Group Value (Count) Info in case group column not in selectedcolumns_arr start
                                // $this->summaries_columns_count
                                if (count($this->g_flds) == 1 && count($this->detail_selectedcolumns_arr) > 0) {
                                    if ($display_groupinfo_row !== true && isset($this->columns_array[$this->g_flds[0]]) && $this->columns_array[$this->g_flds[0]] != "") {
                                        $display_groupinfo_row = true;
                                    }
                                } elseif ($this->report_obj->reportinformations["Group1"] != "none" && $this->summaries_columns_count == 0 && $display_groupinfo_row !== true) {
                                    $display_groupinfo_row = true;
                                }
                                // if(count($this->g_flds)>1 && count($this->detail_selectedcolumns_arr)>0 && $this->summaries_columns_count===0){
                                //  $display_groupinfo_row = true;
                                //  }
                                // LIMIT FOR GROUP INFO ROW START
                                if ($display_groupinfo_row == true && isset($this->report_obj->reportinformations["columns_limit"]) && $this->report_obj->reportinformations["columns_limit"] != "" && isset($this->group_cols_array[1]) && $this->group_cols_array[1] != "") {
                                    $a_gv = $custom_field_values[$this->group_cols_array[1]];
                                    if ($old_gv != $a_gv) {
                                        $old_gv = $custom_field_values[$this->group_cols_array[1]];
                                        $f_r_i = 0;
                                    }
                                    if ($this->report_obj->reportinformations["columns_limit"] > 0) {
                                        if ($a_gv == $old_gv && $f_r_i >= $this->report_obj->reportinformations["columns_limit"]) {
                                            continue;
                                        }
                                    }
                                }

                                // LIMIT FOR GROUP INFO ROW END
                                // Variables used for Group Value (Count) Info in case summaries columns empty end
                                $crm_id_count_done=false;
                                for ($i = 0; $i <= $y; $i++) {
                                    $fld = $adb->field_name($result, $i);

                                    $clear_fld_name_arr = explode("_", $fld->name);
                                    $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                    unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                    $clear_fld_name = implode("_", $clear_fld_name_arr);

                                    if (in_array($fld->name, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                        continue;
                                    }

                                    if ($i == 0 && !empty($selectedcolumns_arr) && in_array($fld->name, $this->g_flds) && isset($this->columns_array[$fld->name]) && $this->columns_array[$fld->name] != "") {
                                        if (!$this->report_obj->in_multiarray($this->columns_array[$fld->name], $selectedcolumns_arr, "fieldcolname") && $this->generate_type != "grouping") {
                                            $group_value = $custom_field_values[$fld->name];
                                            continue;
                                        }
                                    }

                                    // ITS4YOU-CR SlOl 28. 6. 2016 14:32:21
                                    if(isset($custom_field_values["fieldname"]) && $custom_field_values["fieldname"]!=""){
                                        $this->row_fldname = $custom_field_values["fieldname"];
                                        $this->row_module = $custom_field_values["module"];
                                        $this->row_currency_id = $currency_id;
                                    }else{
                                        $this->row_fldname = "";
                                        $this->row_module = "";
                                        $this->row_currency_id = "";
                                    }
                                    // ITS4YOU-END

                                    if (isset($this->columns_array[$fld->name]) && $this->report_obj->in_multiarray($this->columns_array[$fld->name], $this->detail_selectedcolumns_arr, "fieldcolname") !== true && in_array($fld->name, $this->g_flds) !== true) {
                                        continue;
                                    }
                                    // ITS4YOU-CR SlOl 4. 7. 2016 13:00:53 - MISSING COUNT COLUMN IN RESULT
                                    if($crm_id_count_done!=true && $clear_fld_name=="crmid" && $i > 0 && $this->generate_type == "grouping" && $this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type"!=""){
                                        $explode_col_str_arr = explode(":", $this->columns_array[$clear_fld_name]);
                                        $lbl_col_str = explode("_",$explode_col_str_arr[2]);
                                        $explode_col_str_arr[2] = $lbl_col_str[0]."_LBL_RECORDS";
                                        $check_col_str = implode(":", $explode_col_str_arr);
                                        if($this->report_obj->in_multiarray($check_col_str . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true){
                                            continue;
                                        }
                                        $crm_id_count_done = true;

                                    }elseif ($i > 0 && $this->generate_type == "grouping" && $this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                        continue;
                                    }
                                    // detail_selectedcolumns_arr
                                    $first_td = true;

                                    // skipp group columns in case not in selectedcolumns_arr end
                                    // ITS4YOU-CR SlOl 17. 2. 2014 10:23:31
                                    $is_hid = strpos($fld->name, "_hid");
                                    if ($is_hid === false) {
                                        if ($this->generate_type == "grouping") {
                                            $summaries_fld_test = explode("_", $fld->name);
                                            $smft_lk = count($summaries_fld_test) - 1;
                                            if (in_array(strtolower($summaries_fld_test[$smft_lk]), $this->calculation_type_array)) {
                                                $calculation_type = $summaries_fld_test[$smft_lk];
                                                $fld_name = "";
                                                for ($index = 0; $index < (count($summaries_fld_test)); $index++) {
                                                    if ($fld_name != "") {
                                                        $fld_name .= "_";
                                                    }
                                                    $fld_name .= $summaries_fld_test[$index];
                                                }
                                            } else {
                                                $fld_name = $fld->name;
                                            }
                                            // we will skipp 2,3 group by values to display later correct values !!
                                            if ($this->generate_type == "grouping" && in_array($fld_name, $group_cols)) {
                                                continue;
                                            }
                                        }

                                        $hid_url = "";
                                        $fld_hid = $adb->query_result($result, $f_i, $fld->name . "_hid");

                                        if (array_key_exists($fld->name, $this->ui10_fields) && !empty($custom_field_values[$i])) {
                                            $fld_hid = $custom_field_values[$i];
                                        }
                                        if (isset($fld_hid) && $fld_hid != "") {
                                            $entitytype = getSalesEntityType($fld_hid);
                                            if ($entitytype != "") {
                                                global $site_URL;
                                                switch ($entitytype) {
                                                    case "Calendar":
                                                        $hid_url = $site_URL.'/index.php?module=Calendar&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]) . '&activity_mode=Task';
                                                        break;
                                                    case "Events":
                                                        $hid_url = $site_URL.'/index.php?module=Calendar&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]) . '&activity_mode=Events';
                                                        break;
                                                    default:
                                                        $hid_url = $site_URL.'/index.php?module=' . $entitytype . '&view=Detail&record=' . $fld_hid . '&return_module=ITS4YouReports&return_action=resultGenerate&return_id=' . vtlib_purify($_REQUEST["record"]);
                                                        break;
                                                }
                                            } else {
                                                $user = 'no';
                                                $u_result = $adb->pquery("SELECT count(*) as count from vtiger_users where id = ?", array($fld_hid));
                                                if ($adb->query_result($u_result, 0, 'count') > 0) {
                                                    $user = 'yes';
                                                }
                                                if (is_admin($current_user)) {
                                                    global $site_URL;
                                                    if ($user == 'no') {
                                                        $hid_url = "$site_URL/index.php?module=Settings&action=GroupDetailView&groupId=" . $fld_hid;
                                                    } else {
                                                        $hid_url = "$site_URL/index.php?module=Users&view=Detail&record=" . $fld_hid;
                                                    }
                                                }
                                            }
                                        }
                                        $fld_type = $column_definitions[$i]->type;
                                        if (array_key_exists($fld->name, $this->ui10_fields) && !empty($custom_field_values[$i])) {
                                            $type = getSalesEntityType($custom_field_values[$i]);
                                            $tmp = getEntityName($type, $custom_field_values[$i]);
                                            if (is_array($tmp)) {
                                                foreach ($tmp as $key => $val) {
                                                    $fieldvalue = $val;
                                                    break;
                                                }
                                            } else {
                                                $fieldvalue = $custom_field_values[$i];
                                            }
                                        } else {
                                            if ($custom_field_values[$i] != '')
                                                $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                            else
                                                $fieldvalue = getTranslatedString($custom_field_values[$i]);
                                        }
                                        $fieldvalue = str_replace("<", "&lt;", $fieldvalue);
                                        $fieldvalue = str_replace(">", "&gt;", $fieldvalue);

                                        //check for Roll based pick list
                                        $temp_val = $fld->name;
                                        if (is_array($picklistarray))
                                            if (array_key_exists($temp_val, $picklistarray)) {
                                                if (!in_array($custom_field_values[$i], $picklistarray[$fld->name]) && $custom_field_values[$i] != '')
                                                    $fieldvalue = $app_strings['LBL_NOT_ACCESSIBLE'];
                                            }
                                        if (is_array($picklistarray[1]))
                                            if (array_key_exists($temp_val, $picklistarray[1])) {
                                                $temp = explode(",", str_ireplace(' |##| ', ',', $fieldvalue));
                                                $temp_val = Array();
                                                foreach ($temp as $key => $val) {
                                                    if (!in_array(trim($val), $picklistarray[1][$fld->name]) && trim($val) != '') {
                                                        $temp_val[] = $app_strings['LBL_NOT_ACCESSIBLE'];
                                                    } else
                                                        $temp_val[] = $val;
                                                }
                                                $fieldvalue = (is_array($temp_val)) ? implode(", ", $temp_val) : '';
                                            }

                                        if ($fieldvalue == "") {
                                            $fieldvalue = "-";
                                        } else if ($fld->name == 'LBL_ACTION') {
                                            global $site_URL;
                                            $fieldvalue = "<a href='$site_URL/index.php?module={$this->primarymodule}&view=Detail&record={$fieldvalue}' target='_blank'>" . getTranslatedString('LBL_VIEW_DETAILS') . "</a>";
                                        } else if (stristr($fieldvalue, "|##|")) {
                                            $fieldvalue = str_ireplace(' |##| ', ', ', $fieldvalue);
                                        } else if ($fld_type == "date" || $fld_type == "datetime") {
                                            $fieldvalue = getValidDisplayDate($fieldvalue);
                                        }

                                        $bg_color = "";
                                        if ($this->generate_type == "grouping") {
                                            if ($i == 0 && in_array($fld->name, $this->g_flds)) {
                                                $group_value = $custom_field_values[$i];
                                            }

                                            if (isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && $this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                                $bg_color_val = $this->g_colors[1];
                                            } elseif (isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                                $bg_color_val = $this->g_colors[2];
                                            } else {
                                                $bg_color_val = $this->g_colors[0];
                                            }
                                            $bg_color = "background-color:$bg_color_val;";
                                        }
                                        //$txt_align = $this->getFldAlignment($fld->name, $fieldvalue);
                                        $fld_style_arr = $this->getFldStyle($fld->name, $fieldvalue);
                                        $fld_style = $this->getFldStyleString($fld_style_arr);

                                        if ($this->generate_type == "grouping" && is_numeric($fieldvalue) && !in_array($fld->name, $this->group_cols_array)) {
                                            $fld_name_exploded = explode("_", $fld->name);
                                            $calculation_type = $fld_name_exploded[(count($fld_name_exploded) - 1)];

                                            if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                $grouping_totals[$fld->name][$currency_id][] = number_format($fieldvalue, 3, ".", "");
                                            } else {
                                                $grouping_totals[$fld->name][] = number_format($fieldvalue, 3, ".", "");
                                            }
                                        }
                                        // timeline_type2"]=="cols REPORT
                                        if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                            $array_to_totals = array($result, $custom_field_values, $i, $t_y);
                                            $currency_string = $this->get_currency_sumbol_str($currency_id);
                                            $group2_arr = $this->getSubGroupCols($group_value, 1, "", $currency_id, $array_to_totals);
                                            if (!empty($group2_arr["headers"])) {
                                                foreach($group2_arr["headers"] as $g2_header_key => $g2_header_arr){
                                                    $group2_headers[$g2_header_key] = $g2_header_arr;
                                                }
                                            }
                                        }
                                        // charts array population start
                                        $ch_fldname = strtolower($fld->name);
                                        if (!empty($this->charts) && !empty($this->charts["charttypes"])) {
                                            $this->setChArrayValues("charttitle", '', $this->charts["charttitle"]);
                                            if($this->charts["x_group"]=="group1" && in_array($ch_fldname, $this->charts["charts_ds_columns"])){
                                                $this->setDataseriesArray($group_value, $fieldvalue, $currency_id, $ch_fldname);
                                            }
                                        }
                                        // charts array population end
                                        //                                 * **  GROUP INFO ROW DISPLAY for GROUPS In case selected columns not empty *** GROUPING REPORT *
                                        if ($first_td === true && $this->generate_type == "grouping" && count($this->g_colors) == 1 && count($this->detail_selectedcolumns_arr) > 0) {
                                            if (!isset($this->summaries_header) || empty($this->summaries_header)) {
                                                $this->summaries_header = $header;
                                            }
                                            $header_col_lstr = "";
                                            if (isset($this->summaries_header[$i]["label"]) && trim($this->summaries_header[$i]["label"]) != "") {
                                                $header_col_lbl = $this->summaries_header[$i]["label"];
                                            } else {
                                                $header_col_as = $clear_fld_name;
                                                $header_col_str = $this->columns_array[$header_col_as];
                                                $header_col_lbl = $this->getHeaderLabel($this->report_obj->record, "SC", $header_col_as, $header_col_str);
                                            }
                                            // header population for Groups row start
                                            if ($this->selectedcolumns_header_row == "") {
                                                $header = array();
                                                if (!empty($selectedcolumns_arr)) {
                                                    foreach ($selectedcolumns_arr as $sc_key => $sc_array) {
                                                        $sc_header_style = " style='" . $this->header_style . "' ";
                                                        $sc_column_str = $sc_array["fieldcolname"];
                                                        $headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", "", $sc_column_str);
                                                        $header[] = array("style" => $sc_header_style, "label" => $headerLabel,);

					                                    if (!empty($this->detail_columns_array)) {
															$columnsArray = $this->detail_columns_array;
														} else {
															$columnsArray = $this->columns_array;
														}
														$fldName = $columnsArray[$sc_column_str]['fld_alias'];
					                                    if (self::isAddCurrencyField($fldName, $columnsArray)) {
					                                        $header[] = array('style' => $sc_header_style, 'label' => vtranslate('LBL_CURRENCY'),);
					                                    }
                                                    }
                                                    $sc_header = $sc_header_sp = "";
                                                    // $sc_header_sp .= "<td class='rpt4youGrpHead0' colspan='".count($header)."'>&nbsp;</td>";
                                                    //  $sc_header_sp .= "</tr>";
                                                    //  $sc_header_sp .= "<tr>";
                                                    foreach ($header as $header_f_arr) {
                                                        $header_style = $header_f_arr["style"];
                                                        $headerLabel = $header_f_arr["label"];
                                                        $sc_header .= "<td class='rpt4youCellLabel' rowspan='1' colspan='1' $header_style align='center' nowrap >$headerLabel</td>";
                                                    }
                                                    $sc_header .= "</tr>";
                                                    $sc_header .= "<tr>";
                                                    $this->selectedcolumns_header_row = $sc_header;
                                                    //$this->selectedcolumns_header_row_sp = $sc_header_sp;
                                                }
                                                $this->display_group_totals = false;
                                            }
                                            // header population for Groups row end
                                            if ($display_groupinfo_row === true && count($this->g_flds) == 1) {
                                                $sp_group_value = $custom_field_values[$this->group_cols_array[1]];
                                                if ($group_info_tr_added !== true) {
                                                    $sm_calculation_type = "";
                                                    // if($f_i==0){
                                                    //  $group_info_tr .= $this->selectedcolumns_header_row;
                                                    //  }
                                                    if (isset($this->summaries_columns) && !empty($this->summaries_columns)) {
                                                        $detail_row_info = array();
                                                        foreach ($this->summaries_columns as $sm_key => $sm_col_array) {
                                                            $sm_col_str = $sm_col_array['columnname'];
                                                            $sm_col_alias = "";
                                                            $sm_col_str_exploded = explode(":", $sm_col_str);
                                                            $sm_lk = (count($sm_col_str_exploded) - 1);
                                                            if (in_array(strtolower($sm_col_str_exploded[$sm_lk]), $this->calculation_type_array)) {
                                                                $sm_calculation_type = strtolower($sm_col_str_exploded[$sm_lk]);
                                                                $sm_col_lbl_str = implode(":", $sm_col_str_exploded);
                                                                unset($sm_col_str_exploded[$sm_lk]);
                                                                $sm_col_str = implode(":", $sm_col_str_exploded);
                                                                if (isset($this->columns_array[$sm_col_str]["fld_alias"]) && $this->columns_array[$sm_col_str]["fld_alias"] != "") {
                                                                    $sm_col_alias = $this->columns_array[$sm_col_str]["fld_alias"];
                                                                }
                                                                $sm_col_alias .= "_$sm_calculation_type";
                                                            }
                                                            $sm_value = $custom_field_values[$sm_col_alias];
                                                            $sm_col_Label = $this->getHeaderLabel($this->report_obj->record, "SM", $sm_col_alias, $sm_col_lbl_str);
                                                            // $header_col_lstr = ";";
                                                            // $detail_row_info[] = $sm_col_Label." = ".$this->getFldNumberFormat($sm_col_alias, $sm_value, $currency_id).$header_col_lstr;

                                                            $sumColsValue = $this->getFldNumberFormat($sm_col_alias, $sm_value, $currency_id);
                                                            if (is_array($sumColsValue)) {
																$sumColsValue = implode('', $sumColsValue);
															}

                                                            $detail_row_info[] = $sm_col_Label . " = " . $sumColsValue;
                                                        }
                                                    }
                                                    $group_info_tr_value = $this->getFldNumberFormat($this->group_cols_array[1], $sp_group_value, $currency_id, true);
                                                    // $group_records_count = count($this->result_array[$sp_group_value]);
                                                    if (array_key_exists('crmid_r_count', $custom_field_values)) {
														$group_records_count = $custom_field_values['crmid_r_count'];
													} else {
														$group_records_count = $custom_field_values['crmid_count'];
													}

                                                    $group_info_fld_str = $this->columns_array[$this->group_cols_array[1]];
                                                    $group_info_headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", $this->group_cols_array[1], $group_info_fld_str);

                                                    //$group_info_tr .= "<tr><td class='rpt4youGrpHead' colspan='" . count($header) . "' style='text-align:left;background-color:#EEEEEE;' nowrap ><b>";
                                                    $group_info_tr .= "$group_info_headerLabel = $group_info_tr_value ($group_records_count): ";
                                                    $group_info_tr .= implode(";", $detail_row_info);
                                                    //$group_info_tr .= "</b></td></tr>";
                                                    $group_info_tr_added = true;
                                                }
                                            }
                                            //                                     * **  GROUP INFO ROW DISPLAY for GROUPS In case selected columns not empty END *** STANDARD REPORT *
                                        } else {
                                            // Group Value (Count) Info in case summaries columns empty start
                                            if ($display_groupinfo_row === true) {
                                                $sp_group_value = $custom_field_values[$this->group_cols_array[1]];
                                                if ($sp_group_value != $direct_group_info_tr_value) {
                                                    $direct_group_info_tr_value = $sp_group_value;
                                                    $group_info_tr_value = $this->getFldNumberFormat($this->group_cols_array[1], $sp_group_value, $currency_id);
                                                    // $group_records_count = count($this->result_array[$sp_group_value]);
                                                    $group_records_count = $custom_field_values["crmid_count"];
                                                    $group_info_fld_str = $this->columns_array[$this->g_flds[0]];
                                                    $group_info_headerLabel = $this->getHeaderLabel($this->report_obj->record, "SC", $this->g_flds[0], $group_info_fld_str);

                                                    //$group_info_tr = "<tr><td class='rpt4youGrpHead' colspan='" . count($header) . "' style='text-align:left;background-color:#EEEEEE;' nowrap ><b>";
                                                    $group_info_tr .= "$group_info_headerLabel = $group_info_tr_value ($group_records_count)";
                                                    //$group_info_tr .= "</b></td></tr>";
                                                    $group_info_tr_added = true;
                                                }
                                            }
                                            // Group Value (Count) Info in case summaries columns empty end

                                            //                                    * **  DEFAULT VALUE DISPLAY *** *
                                            //$valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:$txt_align;$bg_color' nowrap >" . $this->getFldNumberFormat($fld->name, $fieldvalue, $currency_id) . "$currency_string</td>";
// default amount with symbol

                                            $fieldValues = $this->getFldNumberFormat($fld->name, $fieldvalue, $currency_id);
                                            if (is_array($fieldValues)) {
                                                $r_data[$xls_ri][] = $fieldValues[0];
                                                $r_data[$xls_ri][] = $fieldValues[1];
                                            } else {
                                                $r_data[$xls_ri][] = $fieldValues;
                                            }
                                        }
                                        //                                 * ** DISPLAY   timeline_type2 == "cols"  ***
                                        if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                            $g_ri = 1;
                                            foreach ($group2_headers as $group_h_key => $group_h_arr) {
                                                if (isset($group2_arr["values"][$group_h_key]) && !empty($group2_arr["values"][$group_h_key])) {
                                                    // foreach ($group2_arr["values"][$group_h_key] as $gv_arr) {
                                                    for ($gv_i = 0; $gv_i < count($group_h_arr); $gv_i++) {
                                                        if (isset($group2_arr["values"][$group_h_key][$gv_i]) && !empty($group2_arr["values"][$group_h_key][$gv_i])) {
                                                            $gv_arr = $group2_arr["values"][$group_h_key][$gv_i];
                                                            //$txt_align = $gv_arr["text-align"];
                                                            $gv_value = $gv_arr["value"];
                                                            $gv_fld_name = $gv_arr["fld_name"];
                                                            if ($gv_fld_name != "") {
                                                                if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                                    $grouping_totals[$group_h_key][$gv_fld_name][$currency_id][] = $gv_value;
                                                                } else {
                                                                    $grouping_totals[$group_h_key][$gv_fld_name][] = $gv_value;
                                                                }
                                                            }
                                                            $fw_weight = "";
                                                            if ($group_h_key == "LBL_GROUPING_TOTALS") {
                                                                $fw_weight = "font-weight:bold;";
                                                            }
                                                            //$valtemplate_tr .= "<td class='rpt4youGrpHead' style='text-align:$txt_align;$fw_weight' nowrap >" . $this->getFldNumberFormat($gv_fld_name, $gv_value, $currency_id) . "</td>";

                                                            $fieldValues = $this->getFldNumberFormat($gv_fld_name, $gv_value, $currency_id);
                                                            if (is_array($fieldValues)) {
                                                                $r_data[$xls_ri][] = $fieldValues[0];
                                                                $r_data[$xls_ri][] = $fieldValues[1];
                                                            } else {
                                                                $r_data[$xls_ri][] = $fieldValues;
                                                            }
                                                            $g_ri++;
                                                        }
                                                    }
                                                } else {

                                                    for ($gv_i = 0; $gv_i < count($group_h_arr); $gv_i++) {
                                                        //$valtemplate_tr .= "<td class='rpt4youGrpHead' style='text-align:center;' nowrap > </td>";
                                                        $r_data[$xls_ri][] = " ";
                                                        $g_ri++;
                                                    }
                                                }
                                            }
                                        }

                                        // TOTALS CALCULATION START
                                        if (isset($this->columns_array[$fld->name])) {
                                            $columns_array_lbl = $this->columns_array[$fld->name];
                                        } elseif ($this->columns_array[$fld->table . "." . $fld->name]) {
                                            $columns_array_lbl = $this->columns_array[$fld->table . "." . $fld->name];
                                        } elseif ($fld->table != "") {
                                            $columns_array_lbl = $fld->table . "." . $fld->name;
                                        } else {
                                            $columns_array_lbl = $fld->name;
                                        }

                                        $TheaderLabel = $this->getHeaderLabel($reportid, "CT", $fld->name, $columns_array_lbl);

                                        $fld_totals_key = $fld->name;

                                        // $to_totals_res = $this->setToTotalsArray($to_totals_res,$fld_totals_key,$fieldvalue,$to_totals_array,$currency_id);
                                        $this->to_totals_res = $this->setToTotalsArray($noofrows,$this->to_totals_res, $fld_totals_key, $fieldvalue, $this->to_totals_array, $currency_id);
                                        // TOTALS CALCULATION END
                                    }
                                    $first_td = false;
                                    // ITS4YOU-END 17. 2. 2014 10:23:33
                                    $gc_i++;
                                }
                                // timeline_type3"]=="cols" REPORT
                                $gcols3_r_data = array();
                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && $this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                    //$gcols3_valtemplate = "";
                                    $tl_g3_colspan = 1;

                                    if (empty($this->group_data_array[$group_value])) {
                                        $this->setGroupDataArray($group_value, $currency_id);
                                    }
                                    $group_value = html_entity_decode($group_value, ENT_QUOTES, $default_charset);
                                    if (isset($currency_id) && $currency_id != "") {
                                        $group_data_array = $this->group_data_array[$group_value][$currency_id];
                                    } else {
                                        $group_data_array = $this->group_data_array[$group_value];
                                    }
                                    // foreach($this->result_array[$group_value] as $group_row_key => $group_row_array){
                                    foreach ($group_data_array as $group_row_key => $group_row_array) {
                                        // continue in case summary filter will not contain group values
                                        // if(!array_key_exists($group_row_key,$this->group_data_array[$group_value])){
                                        //  continue;
                                        //  }
                                        //$gcols3_valtemplate .= "</tr><tr>";
                                        $bg_color_val = $this->g_colors[2];
                                        $bg_color = "background-color:$bg_color_val;";
                                        // this is possible only in case group 2 = rows and group 3 = cols, so static rpt4youGrpHead_1 is ok here
                                        //$gcols3_valtemplate .= "<td class='rpt4youGrpHead_1' style='text-align:left;' nowrap >" . $group_row_key . "</td>";
                                        $gcols3_r_data[$group_row_key][] = $group_row_key;

                                        foreach ($header as $header_f_key => $header_f_arr) {
                                            $headerLabel = $header_f_arr["label"];
                                            if (!in_array($headerLabel, array("LBL_GROUPING_TOTALS")) && $header_f_key !== 0) {
                                                if (isset($group_data_array[$group_row_key][$headerLabel])) {
                                                    foreach ($group_data_array[$group_row_key][$headerLabel] as $fld_key => $fld_value) {
                                                        $clear_fld_name_arr = explode("_", $fld_key);
                                                        $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                        unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                        $clear_fld_name = implode("_", $clear_fld_name_arr);

                                                        if (in_array($fld_key, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                                            continue;
                                                        }
                                                        if ($this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                                            continue;
                                                        }
                                                        //$txt_align = $this->getFldAlignment($fld_key, $fld_value);

                                                        $calculation_arr = explode("_", $fld_key);
                                                        $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                        if ($calculation_type == "avg") {
                                                            $ct_fldstr = "";
                                                            $ct_fi = (count($calculation_arr) - 1);
                                                            for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                                if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                    $ct_fldstr .= "_";
                                                                }
                                                                $ct_fldstr .= $calculation_arr[$ct_i];
                                                            }
                                                            $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                            if ($currency_id != "") {
                                                                $group_totals[$group_value][$currency_id][$group_row_key][$fld_key] = $group_totals[$group_value][$currency_id][$group_row_key][$ct_fldstr_sum];
                                                                $group_f_totals[$group_value][$currency_id][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id]["row_totals"][$fld_key][] = $fld_value;
                                                            } else {
                                                                $group_totals[$group_value][$group_row_key][$fld_key] = $group_totals[$group_value][$group_row_key][$ct_fldstr_sum];
                                                                $group_f_totals[$group_value][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value]["row_totals"][$fld_key][] = $fld_value;
                                                            }
                                                        } else {
                                                            if ($currency_id != "") {
                                                                $group_totals[$group_value][$currency_id][$group_row_key][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$currency_id]["row_totals"][$fld_key][] = $fld_value;
                                                            } else {
                                                                $group_totals[$group_value][$group_row_key][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value][$headerLabel][$fld_key][] = $fld_value;
                                                                $group_f_totals[$group_value]["row_totals"][$fld_key][] = $fld_value;
                                                            }
                                                        }
                                                        //$gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='text-align:$txt_align;$bg_color' nowrap >" . $this->getFldNumberFormat($fld_key, $fld_value, $currency_id) . "</td>";
                                                        $fieldValues = $this->getFldNumberFormat($fld_key, $fld_value, $currency_id);
                                                        if (is_array($fieldValues)) {
                                                            $gcols3_r_data[$group_row_key][] = $fieldValues[0];
                                                            $gcols3_r_data[$group_row_key][] = $fieldValues[1];
                                                        } else {
                                                            $gcols3_r_data[$group_row_key][] = $fieldValues;
                                                        }
                                                    }
                                                } else {
                                                    for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                        //$gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='text-align:center;font-weight:bold;$bg_color' nowrap > </td>";
                                                        $gcols3_r_data[$group_row_key][] = " ";
                                                    }
                                                }
                                            } elseif ($headerLabel == "LBL_GROUPING_TOTALS") {
                                                $count_value = 0;
                                                if ($currency_id != "") {
                                                    $group_totals_array = $group_totals[$group_value][$currency_id][$group_row_key];
                                                } else {
                                                    $group_totals_array = $group_totals[$group_value][$group_row_key];
                                                }
                                                foreach ($group_totals_array as $g_t_key => $g_t_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);

                                                    //$gcols3_valtemplate .= "<td class='rpt4youGrpHead'  style='text-align:$txt_align;font-weight:bold;$bg_color' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
                                                    $fieldValues = $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id);
                                                    if (is_array($fieldValues)) {
                                                        $gcols3_r_data[$group_row_key][] = $fieldValues[0];
                                                        $gcols3_r_data[$group_row_key][] = $fieldValues[1];
                                                    } else {
                                                        $gcols3_r_data[$group_row_key][] = $fieldValues;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $bg_color_val1 = $this->g_colors[1];
                                    $bg_color1 = "background-color:$bg_color_val1;";
                                    foreach ($header as $header_f_key => $header_f_arr) {
                                        $headerLabel = $header_f_arr["label"];
                                        if ($currency_id != "") {
                                            $group_totals_f_array = $group_f_totals[$group_value][$currency_id];
                                        } else {
                                            $group_totals_f_array = $group_f_totals[$group_value];
                                        }
                                        if (!in_array($headerLabel, array("LBL_GROUPING_TOTALS")) && $header_f_key !== 0) {
                                            if (isset($group_totals_f_array[$headerLabel]) && !empty($group_totals_f_array[$headerLabel])) {
                                                $count_value = 0;
                                                foreach ($group_totals_f_array[$headerLabel] as $g_t_key => $g_t_array) {
                                                    $clear_fld_name_arr = explode("_", $g_t_key);
                                                    $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                    unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                                                    $clear_fld_name = implode("_", $clear_fld_name_arr);

                                                    if (in_array($g_t_key, $this->skip_fields) || in_array($clear_fld_name, $this->skip_fields)) {
                                                        continue;
                                                    }
                                                    if ($this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                                                        continue;
                                                    }
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        $g_t_array = $group_totals_f_array[$headerLabel][$ct_fldstr_sum];
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);

                                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                        $grouping_totals[$headerLabel][$g_t_key][$currency_id][] = $fld_value;
                                                    } else {
                                                        $grouping_totals[$headerLabel][$g_t_key][] = $fld_value;
                                                    }
                                                    // charts array population start
                                                    if ($g_t_key == $this->charts["charts_ds_column"]) {
                                                        // SPECIAL CHART DATA POPULATION FOR G3 COLS
                                                        $cols_alias = "";
                                                        if ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                                            $cols_alias = $this->g_flds[1];
                                                        } elseif ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                                                            $cols_alias = $this->g_flds[2];
                                                        }
                                                        if (!isset($this->ch_array["dataseries_label"])) {
                                                            $this->setDataseriesLabel($cols_alias);
                                                        }
                                                        //$dataseries_label_key = $this->getHeaderLabel($this->report_obj->record, "SM", $this->g_flds[2], $this->columns_array[$this->g_flds[2]]);
                                                        //$this->setChArrayValues("dataseries_label",'key',$dataseries_label_key);
                                                        if ($currency_id != "") {
                                                            $ch_subkey = $headerLabel . " (" . $this->currency_symbols[$currency_id] . ")";
                                                        } else {
                                                            $ch_subkey = $headerLabel;
                                                        }
                                                        // addToSubvalChArrayValues($ch_key,$ch_subkey,$ch_value,$option_key="",$currency_id=""){
                                                        $this->addToSubvalChArrayValues("dataseries", $headerLabel, $fld_value, $group_value, $currency_id);
                                                        if ($this->ch_array["charttype"] != "horizontal") {
                                                            $this->setChArrayValues("dataseries", $ch_subkey, $fld_value);
                                                        }
                                                    }
                                                    // charts array population end
                                                    //$valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:$txt_align;$bg_color1' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
// FIRST GROUP amount with symbol
                                                    $fieldValues = $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id);
                                                    if (is_array($fieldValues)) {
                                                        $r_data[$xls_ri][] = $fieldValues[0];
                                                        $r_data[$xls_ri][] = $fieldValues[1];
                                                    } else {
                                                        $r_data[$xls_ri][] = $fieldValues;
                                                    }
                                                }
                                            } else {
                                                for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                    //$valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:center;$bg_color1' nowrap > </td>";
                                                    $r_data[$xls_ri][] = " ";
                                                }
                                            }
                                        } elseif ($headerLabel == "LBL_GROUPING_TOTALS") {
                                            $count_value = 0;
                                            if (isset($group_totals_f_array["row_totals"]) && !empty($group_totals_f_array["row_totals"])) {
                                                foreach ($group_totals_f_array["row_totals"] as $g_t_key => $g_t_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "avg") {
                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        $g_t_array = $group_totals_f_array["row_totals"][$ct_fldstr_sum];
                                                    }
                                                    if ($calculation_type == "count") {
                                                        foreach ($g_t_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {
                                                        $fld_value = (array_sum($g_t_array) / $count_value);
                                                    } else {
                                                        $fld_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $fld_value);

                                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                                        $grouping_totals[$headerLabel][$g_t_key][$currency_id][] = $fld_value;
                                                    } else {
                                                        $grouping_totals[$headerLabel][$g_t_key][] = $fld_value;
                                                    }
                                                    //$valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:$txt_align;font-weight:bold;$bg_color1' nowrap >" . $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id) . "</td>";
                                                    $fieldValues = $this->getFldNumberFormat($g_t_key, $fld_value, $currency_id);
                                                    if (is_array($fieldValues)) {
                                                        $r_data[$xls_ri][] = $fieldValues[0];
                                                        $r_data[$xls_ri][] = $fieldValues[1];
                                                    } else {
                                                        $r_data[$xls_ri][] = $fieldValues;
                                                    }
// FIRST GROUP amount with symbol total
                                                }
                                            } else {
                                                for ($g3_i = 0; $g3_i < $this->sum_col_i; $g3_i++) {
                                                    //$valtemplate_tr .= "<td class='rpt4youGrpHead'  style='text-align:center;$bg_color1' nowrap > </td>";
                                                    $r_data[$xls_ri][] = " ";
                                                }
                                            }
                                        }
                                    }
                                    if(isset($gcols3_r_data) && !empty($gcols3_r_data)){
                                        foreach($gcols3_r_data as $gcols_r_arr){
                                            $xls_ri++;
                                            $r_data[$xls_ri] = $gcols_r_arr;
                                        }
                                        $xls_ri++;
                                    }

                                    //$valtemplate_tr .= $gcols3_valtemplate;
                                }

                                // details for group 1
                                if ($this->generate_type == "grouping" && count($this->g_colors) == 1) {
                                    if (!empty($selectedcolumns_arr)) {
                                        $layout = Vtiger_Viewer::getDefaultLayoutName();
                                        $r_data = $this->returnGroupDetailRecordsNew($group_value, $y, $selectedcolumns_arr, $currency_id);
                                    }
                                }
                                //$valtemplate_tr .= "</tr>";
                                // adding tr html of Group Value (Count) Info in case summaries columns empty start
                                if ($group_info_tr != "") {
                                    $data[$xls_fri][] = $group_info_tr;
                                    $xls_fri++;
                                    //$valtemplate .= $group_info_tr;
                                }

                                // adding tr html of Group Value (Count) Info in case summaries columns empty end
                                //$valtemplate .= $valtemplate_tr;
                                //   ROWS REPORT
                                if ($this->generate_type == "grouping" && isset($this->g_colors[1]) && $this->g_colors[1] != "" && $this->report_obj->reportinformations["timeline_type2"] != "cols" && ($this->report_obj->reportinformations["timeline_type3"] != "cols" || $this->report_obj->reportinformations["timeline_type3"] == "none")) {
                                    $group2_html = $this->getSubGroupRow($group_value, $currency_id);
                                    foreach($group2_html as $group2_arr){
                                        $xls_fri++;
                                        $r_data[$xls_fri] = $group2_arr;
                                    }
                                    //$valtemplate .= $group2_html;
                                }

                                $lastvalue = $newvalue;
                                $secondvalue = $snewvalue;
                                $thirdvalue = $tnewvalue;
                                $arr_val[] = $arraylists;
                                set_time_limit($php_max_execution_time);
                                $f_i++;
                                $xls_ri++;

                                // GROUPING TOTALS START
                                if ($this->display_group_totals == true && $this->generate_type == "grouping" && $f_i == $noofrows && $this->report_obj->reportinformations["timeline_type2"] != "cols" && ($this->report_obj->reportinformations["timeline_type3"] != "cols" || $this->report_obj->reportinformations["timeline_type3"] == "none")) {
                                    $bg_color_val = $this->g_colors[0];
                                    $bg_color = "background-color:$bg_color_val;";
                                    //$valtemplate .= "<tr>";
                                    //$valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:left;" . $this->grouping_totals_bg_color . "' nowrap >" . getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You()) . "</td>";
                                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                                        $to_display = $gt_sr_data = array();
                                        $gt_xls_ri=0;
                                        foreach ($grouping_totals AS $g_t_key => $currency_array) {
                                            if(!empty($currency_array)){
                                                $gt_r_data[$gt_xls_ri][] = getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
                                                $gt_xls_sri=0;
                                                foreach ($currency_array AS $currency_key => $g_t_array) {
                                                    $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                    $fld_style_arr = $this->getFldStyle($g_t_key, $g_t_value);
                                                    $fld_style = $this->getFldStyleString($fld_style_arr);

                                                    $to_display[$g_t_key]["values"] = $this->getFldNumberFormat($g_t_key, $g_t_value, $currency_key);
                                                    //$to_display[$g_t_key]["textalign"] = $txt_align;
                                                    $to_display[$g_t_key]["fld_style"] = $fld_style;

                                                    $fieldValues = $this->getFldNumberFormat($g_t_key, $g_t_value, $currency_key);
                                                    if (is_array($fieldValues)) {
                                                        $gt_sr_data[$gt_xls_sri][] = $fieldValues[0];
                                                        $gt_sr_data[$gt_xls_sri][] = $fieldValues[1];
                                                    } else {
                                                        $gt_sr_data[$gt_xls_sri][] = $fieldValues;
                                                    }
                                                    $gt_xls_sri++;
                                                }
                                                $gt_xls_ri++;
                                            }
                                        }
                                        foreach($gt_r_data as $gt_r_key => $gt_r_data_val){
                                            if(!empty($gt_sr_data[$gt_r_key])){
                                                foreach($gt_sr_data[$gt_r_key] as $gt_sr_data_val){
                                                    $gt_r_data[$gt_r_key][] = $gt_sr_data_val;
                                                }
                                            }else{
                                                unset($gt_r_data[$gt_r_key]);
                                            }
                                        }
                                        /*foreach ($to_display AS $td_key => $td_arr) {
                                            $txt_align = $td_arr["textalign"];
                                            $td_value = $td_arr["values"];
                                            $r_data[$xls_ri][] = $td_value;
                                            //$valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:$txt_align;" . $this->grouping_totals_bg_color . "' nowrap >" . $td_value . "</td>";
                                        }*/
                                    } else {
                                        $r_data[$xls_ri][] = getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
                                        foreach ($grouping_totals AS $g_t_key => $g_t_array) {
                                            $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                            //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                            $fieldValues = $this->getFldNumberFormat($g_t_key, $g_t_value);
                                            if (is_array($fieldValues)) {
                                                $r_data[$xls_ri][] = $fieldValues[0];
                                                $r_data[$xls_ri][] = $fieldValues[1];
                                            } else {
                                                $r_data[$xls_ri][] = $fieldValues;
                                            }
                                            //$valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:$txt_align;" . $this->grouping_totals_bg_color . "' nowrap >" . $this->getFldNumberFormat($g_t_key, $g_t_value) . "</td>";
                                        }
                                    }
                                    //$valtemplate .= "</tr>";
                                }
                                if(!empty($r_data)){
                                    foreach($r_data as $row_data){
                                        $data[$xls_fri] = $row_data;
                                        $xls_fri++;
                                    }
                                }
                                if(!empty($gt_r_data)){
                                    foreach($gt_r_data as $gt_r_data_arr){
                                        $xls_fri++;
                                        $data[$xls_fri] = $gt_r_data_arr;
                                    }
                                }

                                $f_r_i++;
                                // GROUPING TOTALS END
                            } while ($custom_field_values = $adb->fetch_array($result));

                            // GROUPING TOTALS START
                            // GROUPING TOTAL FOR COLS START
                            $to_totals_data_array = $to_totals_subdata_array = array();

                            if ($this->generate_type == "grouping" && (($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") || ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols")) && !empty($grouping_totals)) {
                                $xls_t_i = 0;
                                $bg_color = "background-color:$bg_color_val;";
                                // $valtemplate .= "<tr>";
                                // $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals' nowrap style='text-align:left;" . $this->grouping_totals_bg_color . "' nowrap >" . getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You()) . "</td>";
                                // foreach ($grouping_totals as $grouping_totals_key => $grouping_totals_array) {
                                $g_t_value_display_arr = array();
                                foreach ($header as $gh_i => $gh_array) {
                                    if ($gh_i > 0 && isset($grouping_totals[$gh_array["raw"]])) {
                                        $grouping_totals_array = $grouping_totals[$gh_array["raw"]];

                                        foreach ($grouping_totals_array AS $g_t_key => $g_t_array) {
                                            if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)) {
                                                foreach ($g_t_array as $g_ft_currency_id => $g_ft_array) {
                                                    $calculation_arr = explode("_", $g_t_key);
                                                    $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                    if ($calculation_type == "count") {
                                                        $count_value = 0;
                                                        foreach ($g_ft_array as $count_val) {
                                                            $count_value += $count_val;
                                                        }
                                                    }
                                                    if ($calculation_type == "avg") {

                                                        $ct_fldstr = "";
                                                        $ct_fi = (count($calculation_arr) - 1);
                                                        for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                            if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                                $ct_fldstr .= "_";
                                                            }
                                                            $ct_fldstr .= $calculation_arr[$ct_i];
                                                        }
                                                        if (!empty($grouping_totals_array[$ct_fldstr . "_sum"][$g_ft_currency_id])) {
                                                            $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                        } else {
                                                            $ct_fldstr_sum = $ct_fldstr . "_SUM";
                                                        }
                                                        $g_ft_array = $grouping_totals_array[$ct_fldstr_sum][$g_ft_currency_id];
                                                        $g_t_value = (array_sum($g_ft_array) / $count_value);
                                                    } else {
                                                        $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_ft_array);
                                                    }
                                                    //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                    $fieldValues = $this->getFldNumberFormat($g_t_key, $g_t_value, $g_ft_currency_id);
                                                    if (is_array($fieldValues)) {
                                                        $g_t_value_display_arr[$gh_i][$g_ft_currency_id][] = $fieldValues[0];
                                                        $g_t_value_display_arr[$gh_i][$g_ft_currency_id][] = $fieldValues[1];
                                                    } else {
                                                        $g_t_value_display_arr[$gh_i][$g_ft_currency_id][] = $fieldValues;
                                                    }
                                                }
                                                //$g_t_value_display = implode("<br>", $g_t_value_display_arr);
                                            } else {
                                                $calculation_arr = explode("_", $g_t_key);
                                                $calculation_type = strtolower($calculation_arr[(count($calculation_arr) - 1)]);
                                                if ($calculation_type == "count") {
                                                    $count_value = 0;
                                                    if (!empty($g_t_array)) {
                                                        foreach ($g_t_array as $count_key => $count_val) {
                                                            if (is_array($count_val)) {
                                                                $count_value += array_sum($count_val);
                                                            } else {
                                                                $count_value += $count_val;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($calculation_type == "avg") {
                                                    $ct_fldstr = "";
                                                    $ct_fi = (count($calculation_arr) - 1);
                                                    for ($ct_i = 0; $ct_i < $ct_fi; $ct_i++) {
                                                        if ($ct_i > 0 && $ct_i != $ct_fi) {
                                                            $ct_fldstr .= "_";
                                                        }
                                                        $ct_fldstr .= $calculation_arr[$ct_i];
                                                    }
                                                    if (!empty($grouping_totals_array[$ct_fldstr . "_sum"])) {
                                                        $ct_fldstr_sum = $ct_fldstr . "_sum";
                                                    } else {
                                                        $ct_fldstr_sum = $ct_fldstr . "_SUM";
                                                    }
                                                    $g_t_array = $grouping_totals_array[$ct_fldstr_sum];
                                                    $g_t_value = (array_sum($g_t_array) / $count_value);
                                                } else {
                                                    $g_t_value = $this->getGroupTotalsValue($g_t_key, $g_t_array);
                                                }
                                                //$txt_align = $this->getFldAlignment($g_t_key, $g_t_value);
                                                // $g_t_value_display = $this->getFldNumberFormat($g_t_key, $g_t_value);
                                                $g_t_value_display_arr[$gh_i][1][] = $this->getFldNumberFormat($g_t_key, $g_t_value);
                                            }
                                            /*if ($grouping_totals_key == "LBL_GROUPING_TOTALS") {
                                                $valtemplate_totals .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:$txt_align;" . $this->grouping_totals_bg_color . "' nowrap >" . $g_t_value_display . "</td>";
                                            } else {
                                                $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:$txt_align;" . $this->grouping_totals_bg_color . "' nowrap >" . $g_t_value_display . "</td>";
                                            }*/
                                        }
                                    }/* elseif ($gh_i > 0) {
                                        for ($gti = 0; $gti < count($this->sum_col_sql_a); $gti++) {
                                            $valtemplate .= "<td class='rpt4youGrpHeadGroupTotals'  style='text-align:$txt_align;" . $this->grouping_totals_bg_color . "' nowrap >0</td>";
                                        }
                                    }*/
                                }
                                $cd_i = 0;
                                $c_count_cols = 0;
                                foreach($g_t_value_display_arr as $h_t_i => $g_t_array){
                                    foreach($g_t_array as $currency_id => $data_array){
                                        if($c_count_cols==0){
                                            $c_count_cols = count($data_array);
                                        }
                                        $to_totals_data_array_tmp[$currency_id][0][0] = getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
                                        foreach($data_array as $data_val){
                                            $to_totals_data_array_tmp[$currency_id][$cd_i][] = $data_val;
                                        }
                                    }
                                    $cd_i++;
                                }
                                $c_data = array();
                                $n_row_i = 0;
                                $c_cols_i = count($g_t_value_display_arr);
                                foreach($to_totals_data_array_tmp as $currency_id => $currency_data){
                                    for ($ni=0;$ni<$c_cols_i;$ni++){
                                        if($ni==0){
                                            $nc_count_cols = $c_count_cols+1;
                                        }else{
                                            $nc_count_cols = $c_count_cols;
                                        }
                                        if(isset($currency_data[$ni]) && !empty($currency_data[$ni])){
                                            for ($nci=0;$nci<$nc_count_cols;$nci++){
                                                $c_data[$n_row_i][] = $currency_data[$ni][$nci];
                                            }
                                        }else{
                                            for ($nci=0;$nci<$nc_count_cols;$nci++){
                                                $c_data[$n_row_i][] = " ";
                                            }
                                        }
                                    }
                                    $n_row_i++;
                                }
                                //$header_merge[$header_f_key] = $headerLabel;
                                $header_merge = array();
                                $go_merge = false;
                                $c_h_i = 0;
                                if ($this->generate_type == "grouping" && $this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols" && !empty($grouping_totals)) {
                                    $c_h_i++;
                                }
                                foreach($header as $header_key => $header_arr){
                                    if($c_h_i>0){
                                        for ($nci=0;$nci<$nc_count_cols;$nci++){
                                            if(isset($group2_headers) && !empty($group2_headers) && isset($group2_headers[$header_arr["raw"]][$nci]["label"])){
                                                $go_merge = true;
                                                $header_merge[$c_h_i] = $group2_headers[$header_arr["raw"]][$nci]["label"];
                                            }elseif(isset($group2_headers) && !empty($group2_headers) && isset($group2_headers[$header_arr["label"]][$nci]["label"])){
                                                $go_merge = true;
                                                $header_merge[$c_h_i] = $group2_headers[$header_arr["label"]][$nci]["label"];
                                            }elseif(isset($group2_headers) && !empty($group2_headers) && isset($group2_headers[$header_key][$nci]["label"])){
                                                $go_merge = true;
                                                $header_merge[$c_h_i] = $group2_headers[$header_key][$nci]["label"];
                                            } elseif ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                                                $header_merge[$c_h_i] = vtranslate('LBL_CURRENCY');
											}
                                            $c_h_i++;
                                        }
                                    }else{
                                        $c_h_i++;
                                    }
                                }
                                if(!empty($c_data)){
                                    foreach($c_data as $row_data){
                                        $data[$xls_fri] = $row_data;
                                        $xls_fri++;
                                    }
                                }
                                //$valtemplate .= $valtemplate_totals;
                                //$valtemplate .= "</tr>";
                            }
                        } else {
                            $data[0][] = vtranslate("LBL_NO_DATA_TO_DISPLAY", $this->getCurrentModule4You());
                        }
                        // GROUPING TOTAL FOR COLS END
                        // GROUPING TOTALS END
                        /*
                        $header_f = "";
                        if ($populate_detail_header) {
                            $h_i = 0;
                            foreach ($header as $header_f_arr) {
                                $header_style = $header_f_arr["style"];
                                $headerLabel = $header_f_arr["label"];
                                $header_rowspan = 1;
                                $header_colspan = 1;
                                if (!empty($group2_headers)) {
                                    if ($h_i == 0) {
                                        $header_rowspan++;
                                    } else {
                                        $header_colspan = count($group2_headers[$headerLabel]);
                                    }
                                }
                                if ($headerLabel == "LBL_GROUPING_TOTALS") {
                                    $headerLabel = getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
                                }
                                if ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols" && $h_i != 0 && isset($this->sum_col_i) && $this->sum_col_i != 1) {
                                    $header_colspan = $this->sum_col_i;
                                }
                                $header_f .= "<td class='rpt4youCellLabel' rowspan='$header_rowspan' colspan='$header_colspan' $header_style align='center' nowrap >$headerLabel</td>";
                                $h_i++;
                            }
                            if (!empty($group2_headers)) {
                                $header_f .= '</tr><tr>';
                                foreach ($group2_headers as $g2_h_arr) {
                                    foreach ($g2_h_arr as $g2_h_labels) {
                                        $headerLabel = $g2_h_labels["label"];
                                        $header_f .= "<td class='rpt4youCellLabel' style='" . $this->header_style . "' align='center' nowrap >$headerLabel</td>";
                                    }
                                }
                            }
                        }
                        $report_html .= $header_f;
                        $report_html .= '</tr><tr>';
                        $report_html .= $valtemplate;
                        $report_html .= "</table></div>";
                        */
                        if (!empty($this->to_totals_res)) {
                            //$report_html .= "<br>";
                            //$report_html .= $this->getTotalsHTML($this->to_totals_res);
                            $to_totals_data_array = $this->getTotalsHTML($this->to_totals_res);
                        }
                        if(is_array($to_totals_data_array) && !empty($to_totals_data_array)){
                            $data[$xls_fri][] = " ";
                            $xls_fri++;
                            foreach($to_totals_data_array as $row_data){
                                $data[$xls_fri] = $row_data;
                                $xls_fri++;
                            }
                        }
                    }

                    // populate array for XLS export
                    $headers = array();
                    if(!empty($header)){
                        foreach($header as $header_arr){
                            $headers[] = $header_arr["label"];
                        }
                    }

                    $return_data["headers"] = $headers;
                    $return_data["data"] = $data;
                    if($go_merge){
                        $return_data["merge_count"] = $nc_count_cols;
                        $return_data["header_merge"] = $header_merge;
                    }
//exit;
                    return $return_data;
                }
            }
        }
    }

    // ITS4YOU-CR SlOl | 20.8.2015 15:35
    private function createPDFFileForScheduler($report_html,$report_html_headerInfo="",$set_pdf_portrait=false){
        if (vtlib_isModuleActive("PDFMaker") === true && file_exists('modules/PDFMaker/resources/mpdf/mpdf.php')) {
            GenerateObj::checkInstallationMemmoryLimit();
            $layout = Vtiger_Viewer::getDefaultLayoutName();
            if (file_exists("modules/ITS4YouReports/classes/Reports4YouDefault.css")) {
                $report_html_style = file_get_contents("modules/ITS4YouReports/classes/Reports4YouDefault.css");
            }
            $report_pdf = str_replace("<@ReportHeaderInfo@>","",$report_html);
            require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';
//                     $mpdf = new mPDF('',    // mode - default ''
//                      2     '',    // format - A4, for example, default ''
//                      3     0,     // font size - default 0
//                      4     '',    // default font family
//                      5     15,    // margin_left
//                      6     15,    // margin right
//                      7     16,     // margin top
//                      8     16,    // margin bottom
//                      9     9,     // margin header
//                      10     9,     // margin footer
//                      11     'L');  // L - landscape, P - portrait
            // !!! DOKONCIT L P STRANKOVANIE !!! OLDO podla typu reportu, stlpcov a group !!!
            // $mpdf=new mPDF();
            $landscape_format = 'A4';
            $portrait_format = 'A4-L';
            $export_pdf_format = $landscape_format;
            if($this->reports4you_type=="custom_report" && $set_pdf_portrait===true){
                $export_pdf_format = $portrait_format;
            }elseif ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                $export_pdf_format = $portrait_format;
            } elseif ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                $export_pdf_format = $portrait_format;
            } elseif (isset($this->report_obj->reportinformations["summaries_columns"]) && count($this->report_obj->reportinformations["summaries_columns"]) > 7) {
                $export_pdf_format = $portrait_format;
            } elseif (isset($this->selectedcolumns_arr) && count($this->selectedcolumns_arr) > 10 && count($this->report_obj->reportinformations["summaries_columns"]) < 2) {
                $export_pdf_format = $portrait_format;
            }
            if (is_writable($this->temp_files_path)) {
                // class mPDF ([ string $mode [, mixed $format [, float $default_font_size [, string $default_font [, float $margin_left , float $margin_right , float $margin_top , float $margin_bottom , float $margin_header , float $margin_footer [, string $orientation ]]]]]])
                $mpdf = new mPDF('utf-8', "$export_pdf_format", "", "", "5", "5", "0", "5", "5", "5");
                // $this->report_obj->reportinformations["timeline_type2"] cols
                // $this->report_obj->reportinformations["timeline_type3"] cols
                // $this->report_obj->reportinformations["summaries_columns"]
                // $this->selectedcolumns_arr
                // Portrait = $mpdf=new mPDF('utf-8', 'A4');
                // Landscape = $mpdf=new mPDF('utf-8', 'A4-L');
                $mpdf->keep_table_proportions = true;
                if(number_format(mPDF_VERSION)<6){
                    $mpdf->SetAutoFont();
                }
                if ('v7' !== $layout) {
                    $mpdf->WriteHTML($report_html_style, 1);
                }

                $mpdf->WriteHTML($report_pdf);

                $filename = self::$R4YouFilename;
                global $current_user;
                if (isset($current_user) && $current_user->id != "") {
                    $filename .= "_" . $current_user->id;
                    $filename .= "_" . $this->report_obj->record;

					if('v7'===$layout){
					   $filename = $this->report_obj->reportname;
					}
                    $this->pdf_filename = "$filename.pdf";
                    $this->report_filename = $this->temp_files_path . $this->pdf_filename;

                    $mpdf->Output($this->report_filename, 'F');
                }
                //if ($directOutput) {
                //echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('report_filename')){document.getElementById('report_filename').value='" . $this->report_filename . "';}});</script>";
                //}
            }
        }
    }
    // ITS4YOU-END

    public function writeReportToExcelFile($fileName,$reportData){
        global $currentModule, $current_language;
        global $default_charset;
        $mod_strings = return_module_language($current_language, $this->getCurrentModule4You());

        require_once("libraries/PHPExcel/PHPExcel.php");

        $workbook = new PHPExcel();
        $worksheet = $workbook->setActiveSheetIndex(0);

        //$reportData = $this->GenerateReport("PDF",$filterlist);
        $headers_arr = $reportData['headers'];
        $arr_val = $reportData['data'];
//ITS4YouReports::sshow($reportData);

        //$totalxls = $this->GenerateReport("TOTALXLS",$filterlist);

        $header_styles = array(
                'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'E1E0F7') ),
                //'font' => array( 'bold' => true )
        );

        $merge_count = "";
        $header_merge = array();
        $go_merge = false;
        if(isset($reportData["merge_count"]) && isset($reportData["header_merge"]) && !empty($reportData["header_merge"])){
            $merge_count = $reportData["merge_count"];
            $header_merge = $reportData["header_merge"];
            $go_merge = true;
        }
        if(isset($arr_val)) {
            $rowcount = 1;
            $count = 0;
            $columnName = "A";
            $column_index = $target_index = "B";
            for($tci=1;$tci<$merge_count;$tci++){
                ++$target_index.PHP_EOL;
            }
            //$target_index = $column_index+$merge_count;
            foreach($headers_arr as $key=>$value) {
                $value = trim($value);
                if($value=="LBL_GROUPING_TOTALS"){
                    $value = vtranslate("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
                }
                $value = html_entity_decode($value, ENT_QUOTES,$default_charset);
                $worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $value, true);
                $worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);
                if($go_merge===true){
                    if($key==0){
                        $worksheet->mergeCells('A1:A2');
                        $count++;
                    }else{
                        $b1 = "$column_index$rowcount";
                        $b2 = "$target_index$rowcount";
                        $worksheet->mergeCells("$b1:$b2");
                        for($tci=0;$tci<$merge_count;$tci++){
                            ++$column_index.PHP_EOL;
                            ++$target_index.PHP_EOL;
                            $count++;
                        }
                    }
                }else{
                    $count++;
                }
            }
            $rowcount++;

            if($go_merge===true){
                foreach($header_merge as $count => $cols_header_val){
                    $worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $cols_header_val, true);
                    $worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);
                }
                $rowcount++;
            }
            foreach($arr_val as $row_i => $col_arr){
                $count = 0;
                foreach($col_arr as $hdr=>$value) {
                    $value = decode_html($value);
                    // TODO Determine data-type based on field-type.
                    // String type helps having numbers prefixed with 0 intact.
                    if (is_numeric($value)) {
                        $dataType = PHPExcel_Cell_DataType::TYPE_NUMERIC;
                    } else {
                        $dataType = PHPExcel_Cell_DataType::TYPE_STRING;
                    }
                    $worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $value, $dataType);
                    $count = $count + 1;
                }
                $rowcount++;
            }

        }
        /**  DO NOT USE HTML BECAUSE EXCEL COULD NOT OPEN IT !!! - damaged file **/
        //$workbookWriter = PHPExcel_IOFactory::createWriter($workbook, 'HTML');
        $workbookWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
        $workbookWriter->save($fileName);
    }


    function getGroupingList($all = false) {
        $adb = PearDatabase::getInstance();
        global $default_charset;

        if ($this->reports4you_type == "grouping") {
            $sreportsortsql = "select its4you_reports4you_sortcol.* from its4you_reports4you";
            $sreportsortsql .= " inner join its4you_reports4you_sortcol on its4you_reports4you.reports4youid = its4you_reports4you_sortcol.reportid";
            $sreportsortsql1 .= " where its4you_reports4you.reports4youid =? AND its4you_reports4you_sortcol.sortcolid > 3 order by its4you_reports4you_sortcol.sortcolid ASC";
            $sreportsortsql2 .= " where its4you_reports4you.reports4youid =? AND its4you_reports4you_sortcol.sortcolid < 4 order by its4you_reports4you_sortcol.sortcolid ASC";

            if ($all) {
                $sreportsortsql .= $sreportsortsql1;
            } else {
                $sreportsortsql .= $sreportsortsql2;
            }
            $result = $adb->pquery($sreportsortsql, array($this->report_obj->record));
        } else {
            $sreportsortsql = "select its4you_reports4you_sortcol.* from its4you_reports4you";
            $sreportsortsql .= " inner join its4you_reports4you_sortcol on its4you_reports4you.reports4youid = its4you_reports4you_sortcol.reportid";
            $sreportsortsql .= " where its4you_reports4you.reports4youid =? AND sortcolid IN (1,2,3) ";

            // i have to order by timeline_type desc in case timeline_type Rows Cols Rows selected, to display Rows Rows Cols report
            if ($this->report_obj->reportinformations["timeline_type2"] == "rows" && $this->report_obj->reportinformations["timeline_type3"] == "cols" && $this->report_obj->reportinformations["Group2"] != "" && $this->report_obj->reportinformations["Group3"] != "none") {
                $sreportsortsql .= " ORDER BY timeline_type DESC ";
            } else {
                $sreportsortsql .= " ORDER BY its4you_reports4you_sortcol.sortcolid ";
            }
//$adb->setDebug(true);
            $result = $adb->pquery($sreportsortsql, array($this->report_obj->record));
//$adb->setDebug(false);
        }
        $columns_array = $this->columns_array;

        while ($reportsortrow = $adb->fetch_array($result)) {
            if ($this->is_ajax)
                $fieldcolname = htmlentities($reportsortrow["columnname"], ENT_QUOTES, $default_charset);
            else
                $fieldcolname = $reportsortrow["columnname"];
            list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $fieldcolname);
            $sortorder = $reportsortrow["sortorder"];

            if ($sortorder == "Ascending") {
                $sortorder = "ASC";
            } elseif ($sortorder == "Descending") {
                $sortorder = "DESC";
            }

            if ($fieldcolname != "none") {
                //if (array_key_exists($fieldcolname, $columns_array)) {
                $grouplist[$fieldcolname] = $columns_array[$fieldcolname]["fld_alias"] . " " . $sortorder;
            }
        }
        // ITS4YOU-UP SlOl neviem preco je tu toto tak to zatial zakomentujem
        return $grouplist;
    }

    function replaceSpecialChar($selectedfield) {
        $selectedfield = decode_html(decode_html($selectedfield));
        preg_match('/&/', $selectedfield, $matches);
        if (!empty($matches)) {
            $selectedfield = str_replace('&', 'and', ($selectedfield));
        }
        return $selectedfield;
    }

    // ITS4YOU-CR SlOl 17. 2. 2014 14:57:42
    function getColumnsTotal($reportid) {
        // Have we initialized it already?
        if (isset($this->_columnstotallist) && $this->_columnstotallist !== false) {
            return $this->_columnstotallist;
        }

        $adb = PEARDatabase::getInstance();
        global $current_user;

        $query = "SELECT * FROM its4you_reports4you_modules WHERE reportmodulesid =?";
        $res = $adb->pquery($query, array($reportid));
        $modrow = $adb->fetch_array($res);
        $premod = $modrow["primarymodule"];
        $premod_name = vtlib_getModuleNameById($premod);
        $secmod = $modrow["secondarymodules"];

        $coltotalsql = "SELECT its4you_reports4you_summary.* FROM its4you_reports4you";
        $coltotalsql .= " INNER JOIN its4you_reports4you_summary on its4you_reports4you.reports4youid = its4you_reports4you_summary.reportsummaryid";
        $coltotalsql .= " WHERE its4you_reports4you.reports4youid =?";

        $result = $adb->pquery($coltotalsql, array($reportid));

        while ($coltotalrow = $adb->fetch_array($result)) {
            $fieldcolname = $coltotalrow["columnname"];
            if ($fieldcolname != "none") {
                $fieldlist = explode(":", $fieldcolname);
                $field_tablename = $fieldlist[1];
                $field_columnname = $fieldlist[2];

                $mod_query = $adb->pquery("SELECT distinct(tabid) AS tabid FROM vtiger_field WHERE tablename = ? AND columnname=?", array($fieldlist[1], $fieldlist[2]));
                if ($adb->num_rows($mod_query) > 0) {
                    $module_name = getTabName($adb->query_result($mod_query, 0, 'tabid'));
                    $fieldlabel = trim($fieldlist[3]);
                    if ($module_name) {
                        $field_columnalias = $module_name . "_" . $fieldlist[3];
                    } else {
                        $field_columnalias = $module_name . "_" . $fieldlist[3];
                    }
                }

                //$field_columnalias = $fieldlist[3];
                $field_permitted = false;
                if (CheckColumnPermission($field_tablename, $field_columnname, $premod) != "false") {
                    $field_permitted = true;
                } else {
                    $mod_s = explode(":", $secmod);

                    // $premod_name = vtlib_getModuleNameById($premod);
                    foreach ($mod_s as $key) {
                        $mod_arr = explode("x", $key);
                        $mod = $mod_arr[0];
                        $mod_name = vtlib_getModuleNameById($mod);
                        if (CheckColumnPermission($field_tablename, $field_columnname, $mod_name) != "false") {
                            $field_permitted = true;
                        }
                    }
                }
                if ($field_permitted == true) {
                    $field = $field_tablename . "." . $field_columnname;
                    if ($field_tablename == 'vtiger_products' && $field_columnname == 'unit_price') {
                        // Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
                        $field = " innerProduct.actual_unit_price";
                    }
                    if ($field_tablename == 'vtiger_service' && $field_columnname == 'unit_price') {
                        // Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
                        $field = " innerService.actual_unit_price";
                    }
                    if (($field_tablename == 'vtiger_invoice' || $field_tablename == 'vtiger_quotes' || $field_tablename == 'vtiger_purchaseorder' || $field_tablename == 'vtiger_salesorder') && ($field_columnname == 'total' || $field_columnname == 'subtotal' || $field_columnname == 'discount_amount' || $field_columnname == 's_h_amount')) {
                        $field = " $field_tablename.$field_columnname/$field_tablename.conversion_rate ";
                    }
//ITS4YouReports::sshow($fieldlist);
                    if ($fieldlist[4] == 2) {
                        $stdfilterlist[$fieldcolname] = "sum($field) '" . $field_columnalias . "'";
                    }
                    if ($fieldlist[4] == 3) {
                        //Fixed average calculation issue due to NULL values ie., when we use avg() function, NULL values will be ignored.to avoid this we use (sum/count) to find average.
                        //$stdfilterlist[$fieldcolname] = "avg(".$fieldlist[1].".".$fieldlist[2].") '".$fieldlist[3]."'";
                        $stdfilterlist[$fieldcolname] = "(sum($field)/count(*)) '" . $field_columnalias . "'";
                    }
                    if ($fieldlist[4] == 4) {
                        $stdfilterlist[$fieldcolname] = "min($field) '" . $field_columnalias . "'";
                    }
                    if ($fieldlist[4] == 5) {
                        $stdfilterlist[$fieldcolname] = "max($field) '" . $field_columnalias . "'";
                    }
                }
            }
        }
        // Save the information
        $this->_columnstotallist = $stdfilterlist;

        return $stdfilterlist;
    }

    // ITS4YOU-END 17. 2. 2014 14:57:44
    // ITS4YOU-CR SlOl 27. 2. 2014 13:50:16
    function getTotalsHTML($to_totals_res) {
        global $mod_strings, $currentModule;
        if(!isset($mod_strings) || empty($mod_strings)){
            global $currentModule;
            global $current_language;
            if(empty($current_language)) $current_language = 'en_us';
            $mod_strings = return_module_language($current_language, $this->getCurrentModule4You());
        }

        $coltotalhtml = '';
        if($this->outputformat=="XLS"){
            $coltotalhtml = array();
            $coltotal_ri = 0;
            $coltotalhtml[$coltotal_ri][] = $mod_strings["Totals"];
            $coltotalhtml[$coltotal_ri][] = $mod_strings["SUM"];
            $coltotalhtml[$coltotal_ri][] = $mod_strings["AVG"];
            $coltotalhtml[$coltotal_ri][] = $mod_strings["MIN"];
            $coltotalhtml[$coltotal_ri][] = $mod_strings["MAX"];
            $coltotal_ri++;
        }else{
            $header_style = $this->header_style;
            $coltotalhtml .= "<div id='rpt4youTotals'>";

            $layout = Vtiger_Viewer::getDefaultLayoutName();
            if($layout == "v7"){
                $totalsTableClass = 'table table-bordered';
            } else {
                $totalsTableClass = 'rpt4youTotals';
            }
            $coltotalhtml .= "<table align='center' cellpadding='3' cellspacing='0'  border='1' style='border-collapse: collapse' class='$totalsTableClass' style='min-width:30%;'>";

            $coltotalhtml .= "<tr>";

            // ITS4YOU-CR SlOl 20. 5. 2016 13:25:04
            $limit_nr = $this->report_obj->reportinformations["columns_limit"];
            $limit_info = "";
            if($limit_nr!="0" && $limit_nr!=""){
                $limit_info =   ' <!--LIMIT_INF-->
                                  <a data-original-title="" href="#" id="cc_tooltip_{$cc_i}" class="no-print editHelpInfo tooltipstered" onmouseover="jQuery(\'#limit_info_r\').show();" onmouseout="jQuery(\'#limit_info_r\').css(\'display\',\'none\');" data-placement="top" data-text="test" data-template="<div class=\'tooltip\' role=\'tooltip\'><div class=\'tooltip-arrow\'></div><div class=\'tooltip-inner\' style=\'text-align: left\'></div></div>">
                                    <i class="icon-info-sign alignMiddle"></i>&nbsp;
                                  </a>
                                  <span id="limit_info_r" class="no-print" style="z-index:999;position:absolute;display:none;" >
                                     <span class=\'tooltip-arrow\'></span><span class=\'tooltip-inner\' style=\'text-align: left\' id="">'.vtranslate("LBL_CALCULATION_LIMITED_TO",$currentModule)." $limit_nr ".vtranslate("LBL_RECORDS",$currentModule).'</span>
                                  </span>
                                  <!--LIMIT_INF-->';
            }
            // ITS4YOU-END
            // ITS4YOU-UP SlOl 20. 5. 2016 13:25:09 - $limit_info
            $coltotalhtml .= "<td class='rpt4youCellLabel' style='min-width:28%;$header_style' nowrap >" . $mod_strings["Totals"] . "$limit_info</td>";
            $coltotalhtml .= "<td class='rpt4youCellLabel' style='min-width:18%;$header_style' nowrap >" . $mod_strings["SUM"] . "</td>";
            $coltotalhtml .= "<td class='rpt4youCellLabel' style='min-width:18%;$header_style' nowrap >" . $mod_strings["AVG"] . "</td>";
            $coltotalhtml .= "<td class='rpt4youCellLabel' style='min-width:18%;$header_style' nowrap >" . $mod_strings["MIN"] . "</td>";
            $coltotalhtml .= "<td class='rpt4youCellLabel' style='min-width:18%;$header_style' nowrap >" . $mod_strings["MAX"] . "</td>";
            $coltotalhtml .= "</tr>";
        }

		if (!empty($to_totals_res)) {
            $k_i = 0;
            foreach ($to_totals_res as $key => $totals_array) {
                if($this->outputformat!="XLS"){
                    $coltotalhtml .= '<tr valign="middle">';
                }
                if (isset($totals_array["label"])) {
                    $col_header = $totals_array["label"];
                } else {
                    $col_header = $key;
                }

                /* if($uitype_arr[$value]==71 || in_array($fld_name_1,$this->convert_currency) || in_array($fld_name_1,$this->append_currency_symbol_to_value)
                  || in_array($fld_name_2,$this->convert_currency) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
                  $col_header .= " (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
                  $convert_price = true;
                  } else{
                  $convert_price = false;
                  } */
                if($this->outputformat=="XLS"){
                    if($col_header!=""){
                        $coltotalhtml[$coltotal_ri][] = $col_header;
                    }
                }else{
                    $coltotalhtml .= '<td class="rpt4youGrpHead" nowrap >' . $col_header . '</td>';
                }

                /*
                  if($k_i==0){
                  $td_class = "rpt4youCellLabel";
                  }else{
                  $td_class = "rpt4youGrpHead";
                  }
                  $coltotalhtml .= '<td class="rpt4youGrpHead" style="background-color:#737373;color:#F6F6F6;font-weight:bold;font-size:11px;" align="center" nowrap >'.$k_i." " . $col_header . '</td>';
                 */
                $value = trim($key);
                $to_display = array();
                $arraykey = 'SUM';
                if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true && isset($totals_array[$arraykey])) {
                    ksort($totals_array[$arraykey]);
                    foreach ($totals_array[$arraykey] as $currency_id => $totals_value) {
                        if ($convert_price)
                            $conv_value = convertFromMasterCurrency($totals_value, $current_user->conv_rate);
                        else
                            $conv_value = $totals_value;
                        $conv_value = number_format($conv_value, 2, ".", "");
                        $to_display[] = $this->getFldNumberFormat($key, $conv_value, $currency_id);
                    }
                    // dokoncit VIAC currencies !!! v totals
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = implode("<br>", $to_display);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . implode("<br>", $to_display) . '</td>';
                    }
                }elseif (isset($totals_array[$arraykey])) {
                    if ($convert_price)
                        $conv_value = convertFromMasterCurrency($totals_array[$arraykey], $current_user->conv_rate);
                    else
                        $conv_value = $totals_array[$arraykey];
                    $conv_value = number_format($conv_value, 2, ".", "");
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = $this->getFldNumberFormat($key, $conv_value);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . $this->getFldNumberFormat($key, $conv_value) . '</td>';
                    }
                }else {
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = "";
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal"> </td>';
                    }
                }

                $to_display = array();
                $arraykey = 'AVG';
                $count_key = "COUNT";
                if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true && isset($totals_array[$arraykey]) && isset($totals_array[$count_key])) {
                    ksort($totals_array[$arraykey]);
                    foreach ($totals_array[$arraykey] as $currency_id => $totals_value) {
                        $conv_value = ($totals_value / count($totals_array[$count_key][$currency_id]));
                        if ($convert_price)
                            $conv_value = convertFromMasterCurrency($conv_value, $current_user->conv_rate);
                        $conv_value = number_format($conv_value, 2, ".", "");
                        $to_display[] = $this->getFldNumberFormat($key, $conv_value, $currency_id);
                    }
                    // dokoncit VIAC currencies !!! v totals
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = implode("<br>", $to_display);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . implode("<br>", $to_display) . '</td>';
                    }
                }elseif (isset($totals_array[$arraykey]) && isset($totals_array[$count_key])) {
                    $conv_value = ($totals_array[$arraykey] / $totals_array[$count_key]);
                    if ($convert_price)
                        $conv_value = convertFromMasterCurrency($conv_value, $current_user->conv_rate);
                    $conv_value = number_format($conv_value, 2, ".", "");
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = $this->getFldNumberFormat($key, $conv_value);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . $this->getFldNumberFormat($key, $conv_value) . '</td>';
                    }
                }else {
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = "";
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal"> </td>';
                    }
                }

                $to_display = array();
                $arraykey = 'MIN';
                if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true && isset($totals_array[$arraykey])) {
                    ksort($totals_array[$arraykey]);
                    foreach ($totals_array[$arraykey] as $currency_id => $totals_value) {
                        if ($convert_price)
                            $conv_value = convertFromMasterCurrency($totals_value, $current_user->conv_rate);
                        else
                            $conv_value = $totals_value;
                        $conv_value = number_format($conv_value, 2, ".", "");
                        $to_display[] = $this->getFldNumberFormat($key, $conv_value, $currency_id);
                    }
                    // dokoncit VIAC currencies !!! v totals
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = implode("<br>", $to_display);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . implode("<br>", $to_display) . '</td>';
                    }
                }elseif (isset($totals_array[$arraykey])) {
                    if ($convert_price)
                        $conv_value = convertFromMasterCurrency($totals_array[$arraykey], $current_user->conv_rate);
                    else
                        $conv_value = $totals_array[$arraykey];
                    $conv_value = number_format($conv_value, 2, ".", "");
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = $this->getFldNumberFormat($key, $conv_value);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . $this->getFldNumberFormat($key, $conv_value) . '</td>';
                    }
                }else {
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = "";
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal"> </td>';
                    }
                }

                $to_display = array();
                $arraykey = 'MAX';
                if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true && isset($totals_array[$arraykey])) {
                    ksort($totals_array[$arraykey]);
                    foreach ($totals_array[$arraykey] as $currency_id => $totals_value) {
                        if ($convert_price)
                            $conv_value = convertFromMasterCurrency($totals_value, $current_user->conv_rate);
                        else
                            $conv_value = $totals_value;
                        $conv_value = number_format($conv_value, 2, ".", "");
                        $to_display[] = $this->getFldNumberFormat($key, $conv_value, $currency_id);
                    }
                    // dokoncit VIAC currencies !!! v totals
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = implode("<br>", $to_display);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . implode("<br>", $to_display) . '</td>';
                    }
                }elseif (isset($totals_array[$arraykey])) {

                    if ($convert_price)
                        $conv_value = convertFromMasterCurrency($totals_array[$arraykey], $current_user->conv_rate);
                    else
                        $conv_value = $totals_array[$arraykey];
                    $conv_value = number_format($conv_value, 2, ".", "");
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = $this->getFldNumberFormat($key, $conv_value);
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal" nowrap >' . $this->getFldNumberFormat($key, $conv_value) . '</td>';
                    }
                }else {
                    if($this->outputformat=="XLS"){
                        $coltotalhtml[$coltotal_ri][] = "";
                    }else{
                        $coltotalhtml .= '<td class="rpt4youTotal"> </td>';
                    }
                }
                if($this->outputformat=="XLS"){
                    $coltotal_ri++;
                }else{
                    $coltotalhtml .= '</tr>';
                }

                // Performation Optimization: If Direct output is desired
                if ($directOutput) {
                    echo $coltotalhtml;
                    $coltotalhtml = '';
                }
                // END
                $k_i++;
            }
        }
        if($this->outputformat=="XLS"){
            $sHTML = $coltotalhtml;
        }else{
            $coltotalhtml .= "</table>";
            $coltotalhtml .= "</div>";
            $sHTML .= $coltotalhtml;
        }

        return $sHTML;
    }

    // ITS4YOU-END 27. 2. 2014 13:50:19
    // ITS4YOU-CR SlOl 28. 2. 2014 10:19:19
    private function getQFConditions(Vtiger_Request $request) {

        $conditions = array();
        $qfConditionFromRequest = $this->getQuickFiltersFromRequest($request);
        if(!empty($qfConditionFromRequest)) {
            foreach($qfConditionFromRequest as $columnName => $qfInfo) {
                if (isset($this->columns_array[$columnName]) && in_array($this->columns_array[$columnName], $this->report_obj->reportinformations['quick_filters'])) {
                    $columnStr = $this->columns_array[$columnName];
                    if($this->columns_array[$columnStr]) {
                        $fld_cond = $this->columns_array[$columnStr]['fld_cond'];
                        if(1 === count($qfInfo['value']) && 'NULL' === $qfInfo['value'][0]) {
                            if('is'===$qfInfo['radio']) {
                                $cond_str = 'IS ';
                            } else {
                                $cond_str = 'IS NOT';
                            }
                            $inVales = $qfInfo['value'][0];
                            $inValesCondition = ' ' . $inVales;
                        } else {
                            if ('is' === $qfInfo['radio']) {
                                $cond_str = 'IN';
                            } else {
                                $cond_str = 'NOT IN';
                            }
                            $inVales = implode('","', $qfInfo['value']);
                            $inValesCondition = ' ("' . $inVales . '") ';
                        }
                        $conditions[] = ' ' . $fld_cond . ' ' . $cond_str . $inValesCondition;
                    }
                }
            }
        }

        return $conditions;
    }

    // ITS4YOU-CR SlOl | 17.6.2014 16:35
    private function getCurrencyFieldSql($primary_focus_table_name,$fieldid_alias="") {
        $adb = PearDatabase::getInstance();
        $currency_result = $adb->pquery("SELECT columnname FROM vtiger_field WHERE uitype=? and tablename = ? ", array("117", $primary_focus_table_name));
        $currency_num_rows = $adb->num_rows($currency_result);
        if ($currency_num_rows > 0) {
            $currency_row = $adb->fetchByAssoc($currency_result);
            if($fieldid_alias !=""){
                $group_by_currency_sql = ", " . $primary_focus_table_name.$fieldid_alias . "." . $currency_row["columnname"]." AS ". $currency_row["columnname"].$fieldid_alias;
            }else{
                $this->currency_id_sql = $primary_focus_table_name . "." . $currency_row["columnname"];
                $group_by_currency_sql = ", " . $this->currency_id_sql;
            }
        }
        return $group_by_currency_sql;
    }

    // ITS4YOU-END 28. 2. 2014 10:19:21
    private function getGOSQL($groupslist, $primary_focus_table_name) {
        global $default_charset;
        $this->g_flds = $g_o_array = array();
        $group_by_currency_sql = $order_by_sql = "";
        $adb = PearDatabase::getInstance();
        // ITS4YOU-CR SlOl | 7.7.2014 11:00
        $sortcolS = $adb->pquery("SELECT * FROM its4you_reports4you_summaries_orderby WHERE reportid = ? AND columnindex = ? ", array($this->report_obj->record, 0));
        $sortcolS_nr = $adb->num_rows($sortcolS);
        if ($sortcolS_nr > 0) {
            while ($row = $adb->fetchByAssoc($sortcolS)) {
                if ($row["summaries_orderby_type"] != "") {
                    $calculation_type = "";
                    $fld_string = $row["summaries_orderby"];
                    $fld_string_arr = explode(":", $fld_string);
                    if (in_array(strtolower($fld_string_arr[(count($fld_string_arr) - 1)]), $this->calculation_type_array)) {
                        $calculation_type = $fld_string_arr[(count($fld_string_arr) - 1)];
                        unset($fld_string_arr[(count($fld_string_arr) - 1)]);
                        $fld_string = implode(":", $fld_string_arr);
                    }
                    if ($this->columns_array[$fld_string]["fld_cond"] != "" && $row["summaries_orderby_type"] != "") {
                        if ($order_by_sql != "") {
                            $order_by_sql .= ", ";
                        }
                        if ($calculation_type != "" && $this->generate_type == "grouping") {
                            // ITS4YOU-UP SlOl 5. 11. 2015 9:48:15
                            if(strtolower($calculation_type)=="count"){
                                $fld_cond_sql = "COUNT(vtiger_crmentity.crmid)";
                            }else{
                                $fld_cond_sql = $calculation_type . "(" . $this->columns_array[$fld_string]["fld_cond"] . ")";
                            }
                            // ITS4YOU-END
                        } else {
                            $fld_cond_sql = $this->columns_array[$fld_string]["fld_cond"];
                        }
                        $order_by_sql .= $fld_cond_sql . " " . $row["summaries_orderby_type"];
                    }
                }
            }
        }

        // ITS4YOU-END 7.7.2014 11:00

        if (!empty($groupslist)) {
            $this->group_column_alias = array();
            $group_by_sql = $group_by_col_sql = "";
            $sum_col_sql_a = array();
            switch (count($groupslist)) {
                case 3:
                    $this->g_colors = array("#CCCCCC", "#EEEEEE", "#FFFFFF");
                    break;
                case 2:
                    $this->g_colors = array("#EEEEEE", "#FFFFFF");
                    break;
                default:
                    $this->g_colors = array("#FFFFFF");
                    break;
            }
            $gi = $tlgi = 0;
            foreach ($groupslist AS $ctype => $ccolumn) {

                // groupby $this->columns_array[$ctype]["fld_alias"]
                if ($group_by_sql != "") {
                    $group_by_sql .= ", ";
                }
                $g_fld_alias_arr = explode(" AS ", $this->columns_array[$ctype]["fld_sql_str"]);
                $g_fld_alias = trim($g_fld_alias_arr[1]);

                $gi_con_value = $this->columns_array[$ctype]["fld_cond"];

                if ($this->summaries_columns_count > 0 || $this->report_obj->reportinformations["Group1"] != "none") {
                    // ITS4YOU-CR SlOl | 15.5.2014 11:46
                    $tlgi = $gi + 1;
                    //if (isset($this->report_obj->reportinformations["timeline_columnstr$tlgi"]) && $this->report_obj->reportinformations["timeline_columnstr$tlgi"] == $ctype && $this->report_obj->reportinformations["timeline_columnfreq$tlgi"] != "") {
                    if (isset($this->report_obj->reportinformations["timeline_columnstr$tlgi"]) && $this->report_obj->reportinformations["timeline_columnstr$tlgi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$tlgi"] != "@vlv@") {
                        $gi_con_value = $this->getTimeLineColumnSql($gi_con_value, $this->report_obj->reportinformations["timeline_columnstr$tlgi"]);
                    }
                    // ITS4YOU-END 15.5.2014 11:46
                    $group_by_sql .= $gi_con_value;
                    if ($group_by_sql != "") {
                        $group_by_sql = " $group_by_sql ";
                    }

                    if (ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true) {
                        $group_by_currency_sql = $this->getCurrencyFieldSql($primary_focus_table_name);
                    }

                    // ITS4YOU-UP SlOl 14. 9. 2015 15:34:11
                    $rel_group_by_currency_sql = "";
                    if(isset($this->rel_group_by_currency_sql) && $this->rel_group_by_currency_sql!=""){
                        $rel_group_by_currency_sql = $this->rel_group_by_currency_sql;
                    }
                    // ITS4YOU-END

                    $g_o_array["group_by_sql"][] = $group_by_sql . $group_by_currency_sql . $rel_group_by_currency_sql;
                    $gi_con = "gi_con_$gi";
                    $this->$gi_con = $this->columns_array[$ctype]["fld_cond"];
                    $gi_con_alias = "gi_con_alias_$gi";
                    $this->$gi_con_alias = $this->columns_array[$ctype]["fld_alias"];
                }
                $this->g_flds[] = $g_fld_alias;
                // orderby $ccolumn
                if ($order_by_sql != "") {
                    $order_by_sql .= ", ";
                }

                if(!isset($this->columns_array[$ctype]["fld_cond"])){
                    $ctype = html_entity_decode($ctype,ENT_QUOTES,$default_charset);
                }
                if (isset($this->columns_array[$ctype]["fld_cond"]) && $this->columns_array[$ctype]["fld_cond"] != "") {
                    $o_by = explode(" ", $ccolumn);
                    $o_by_v = $o_by[(count($o_by) - 1)];
                    $gi_order_by_sql = $this->columns_array[$ctype]["fld_cond"] . " $o_by_v";
                } else {
                    $gi_order_by_sql = $ccolumn;
                }
                // ITS4YOU-CR SlOl 15. 8. 2016 7:03:57 - ORDER BY PROBABILITY FIX ?! test in more reports
                $order_by_sql .= $gi_order_by_sql;
                //$order_by_sql = $gi_order_by_sql;
                // ITS4YOU-END 15. 8. 2016 7:04:20
                $gi_order_by_name = "gi_order_by$gi";
                $this->$gi_order_by_name = $gi_order_by_sql;
                $gi++;
            }

            if ($order_by_sql != "") {
                $order_by_sql = " $order_by_sql ";
            }
        }

        // ITS4YOU-CR SlOl | 30.6.2014 10:45
        // ITS4YOU-UP SlOl 13. 5. 2016 9:05:21 -> multi Ordering
        $sortcol4 = $adb->pquery("SELECT columnname, sortorder FROM its4you_reports4you_sortcol WHERE reportid = ? AND sortcolid = ? ORDER BY sortcolsequence ASC ", array($this->report_obj->record, 4));
        $sortcol4_nr = $adb->num_rows($sortcol4);
        if ($sortcol4_nr > 0) {
            while ($row = $adb->fetchByAssoc($sortcol4)) {
                $columnName = html_entity_decode($row["columnname"],ENT_QUOTES,$default_charset);
                if ($this->columns_array[$columnName]["fld_cond"] != "" && $row["sortorder"] != "") {
                    if ($order_by_sql != "") {
                        $order_by_sql .= ", ";
                    }
                    $order_by_sql .= $this->columns_array[$columnName]["fld_cond"] . " " . $row["sortorder"];
                }
            }
        }
        // ITS4YOU-END 30.6.2014 10:45
        $g_o_array["order_by_sql"] = $order_by_sql;

        return $g_o_array;
    }

    private function setSummariesLBLKeys($summaries_column_arr_columnname) {
        $adb = PearDatabase::getInstance();
        if ($summaries_column_arr_columnname != "") {
            $resultsm = $adb->pquery("SELECT columnname,columnlabel FROM its4you_reports4you_labels WHERE reportid = ? and type = 'SM' AND columnname like '" . $summaries_column_arr_columnname . "%'", array($this->report_obj->record));
            $num_rowsqf = $adb->num_rows($resultsm);
            $return_arr = array();
            if ($num_rowsqf > 0) {
                while ($row = $adb->fetchByAssoc($resultsm)) {
                    $return_arr[$row["columnname"]] = $row["columnlabel"];
                }
            } else {
                $return_arr = array();
            }
        }
        return $return_arr;
    }

    private function getHeadersArray($type = "SC") {
        $selectedcolumns_arr = $this->report_obj->getSelectedColumnListArray($this->report_obj->record);
        $header_lbls = array();
        if (!empty($selectedcolumns_arr)) {
            foreach ($selectedcolumns_arr as $key => $column_array) {
                $column_str = $column_array["fieldcolname"];
                if (isset($this->columns_array[$column_str]["fld_alias"]) && $this->columns_array[$column_str]["fld_alias"] != "") {
                    $fld_alias = $this->columns_array[$column_str]["fld_alias"];
                    $header_lbls[$fld_alias] = $this->getHeaderLabel($this->report_obj->record, $type, $fld_alias, $column_str);
                }
            }
        }

        return $header_lbls;
    }

    private function setResultArray($sSQL) {
        $adb = PearDatabase::getInstance();
        if (isset($this->gb_sql) && $this->gb_sql != "") {
//$adb->setDebug(true);
            $result = $adb->pquery($this->gb_sql, array());
//$adb->setDebug(false);
            $numres = $adb->num_rows($result);
            $this->group_column_alias = array();
            $all_g_cols_arr = array();
            for ($gi = 1; $gi < 4; $gi++) {
                $column_str = $this->report_obj->reportinformations["Group$gi"];
                if ($column_str != "none") {
                    $ex_fld_alias = explode(".", $this->columns_array[$column_str]["fld_alias"]);
                    $group_cols_array[$gi] = $ex_fld_alias[(count($ex_fld_alias) - 1)];
                    $all_g_cols_arr[] = $ex_fld_alias[(count($ex_fld_alias) - 1)];
                    $this->group_column_alias[$gi] = $all_g_cols_arr;
                }
            }
            $this->group_cols_array = $group_cols_array;
            if ($numres > 0) {
                if ($result) {
                    $group_key1 = $group_key2 = $group_key3 = "";
                    $gi = 0;
                    $r_i = 0;
                    while ($row = $adb->fetchByAssoc($result)) {
                        if ($currency_id != "") {
                            $result_details_array[$group_key1][$currency_id][$gi] = $row;
                        } else {
                            $result_details_array[$group_key1][$gi] = $row;
                        }

                        $group_key1 = $row[$group_cols_array[1]];
                        $group_key2 = $group_key3 = "none";
                        if ($row[$group_cols_array[2]] != "") {
                            $group_key2 = $row[$group_cols_array[2]];
                        }
                        if ($row[$group_cols_array[3]] != "") {
                            $group_key3 = $row[$group_cols_array[3]];
                        }
                        foreach ($row as $fld_alias => $fld_value) {
                            $currency_id = "";
                            if (isset($row["currency_id"]) && $row["currency_id"] != "" && ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true) {
                                $currency_id = $row["currency_id"];
                            }
                            if (isset($row["invoicing_currency_id"]) && $row["invoicing_currency_id"] != "" && ITS4YouReports::isInventoryModule($this->report_obj->primarymodule)===true) {
                                $currency_id = $row["invoicing_currency_id"];
                            }

                            if ($currency_id != "") {
                                $result_details_array[$group_key1][$currency_id][$gi][$fld_alias] = $fld_value;
                            } else {
                                $result_details_array[$group_key1][$gi][$fld_alias] = $fld_value;
                            }

                            if ($group_key1 != "" && $group_key2 == "none" && $group_key3 == "none") {
                                $result_array[$group_key1][$gi][$fld_alias] = $fld_value;
                            } elseif ($group_key1 != "" && $group_key2 != "" && $group_key3 == "none") {
                                $result_array[$group_key1][$group_key2][$gi][$fld_alias] = $fld_value;
                            } elseif ($group_key1 != "" && $group_key2 != "" && $group_key3 != "") {
                                $result_array[$group_key1][$group_key2][$group_key3][$gi][$fld_alias] = $fld_value;
                            }
                        }
                        $gi++;
                    }
                }
            }
        }
        //$this->result_array = $result_array;
        $this->result_details_array = $result_details_array;
        $this->result_columns_array = $this->columns_array;
    }

    private function getColsHeaders($header_style, $agi = 1) {
        global $currentModule;
        $adb = PearDatabase::getInstance();
        $headerLabel_arr = array();
        $group_sql_name = "group_sql_$agi";
        $g_con_name = "gi_con_$agi";
        $header_column = $this->$g_con_name;

        global $default_charset;
        $header_column_base = html_entity_decode($header_column, ENT_QUOTES, $default_charset);
        $header_alias = $this->g_flds[$agi];
        /*
          $hc_arr = explode(".", trim($header_column));
          $header_alias = $hc_arr[(count($hc_arr) - 1)];
          $header_alias = str_replace('(','',$header_alias);
          $header_alias = str_replace(')','',$header_alias);
          $header_alias = trim($header_alias);
         */

        $exp_gsql1 = explode("DISTINCT", $this->$group_sql_name);
        $exp_gsql2 = explode("FROM", $exp_gsql1[1]);

        $gi = $agi + 1;
        if (isset($this->report_obj->reportinformations["timeline_columnstr$gi"]) && $this->report_obj->reportinformations["timeline_columnstr$gi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$gi"] != "@vlv@") {
            $header_column = $this->getTimeLineColumnSql($header_column_base, $this->report_obj->reportinformations["timeline_columnstr$gi"]) . " AS $header_alias";
            $gi_order_by_name = "gi_order_by$agi";
            $exp_ord2 = explode("ORDER BY", $exp_gsql2[1]);
            $exp_ord2[1] = $this->$gi_order_by_name;
            $exp_gsql2[1] = implode(" ORDER BY ", $exp_ord2);
        } elseif ('cols' === $this->report_obj->reportinformations['timeline_type'.($agi+1)]) {
            $header_column = $header_column_base . " AS $header_alias";
            $gi_order_by_name = "gi_order_by$agi";
            $exp_ord2 = explode("ORDER BY", $exp_gsql2[1]);
            $exp_ord2[1] = $this->$gi_order_by_name;
            $exp_gsql2[1] = implode(" ORDER BY ", $exp_ord2);
        } else {
            $header_column = $header_column_base . " AS $header_alias";
        }
        /*
          if(isset($this->report_obj->reportinformations["timeline_columnstr$agi"]) && $this->report_obj->reportinformations["timeline_columnstr$agi"]!="" && $this->report_obj->reportinformations["timeline_columnstr$agi"]!="@vlv@"){
          $g_con_col_sql = $this->getTimeLineColumnSql($this->$g_con_name,$this->report_obj->reportinformations["timeline_columnstr$agi"]);
          }else{
          $g_con_col_sql = $this->$g_con_name;
          }

         */
        // fix to quit NULL values for a reason of twice display in case e.g. Product Category is null and "", sql will generate two arrays for a matrix report so i will quit null group by which are in a previous array already
        $fix_expr = $exp_gsql2[1];
        if(stripos($fix_expr,"WHERE ")=== false){
            $fix_expr_val = "WHERE ";
        }else{
            $fix_expr_val = "AND ";
        }
        $fix_expr = str_replace("GROUP BY ", " $fix_expr_val $header_column_base IS NOT NULL GROUP BY ", $fix_expr);
        $n_sql = "SELECT DISTINCT " . $header_column . " FROM " . $fix_expr ;

		$groupColStr = $this->columns_array[$header_alias];
		$groupCondStr = $this->columns_array[$groupColStr]['fld_cond'];
		$groupModule = self::getModuleNameFromColumnStr($groupColStr);

		if (!strstr($fix_expr, $groupCondStr)) {
			$n_sql = preg_replace('/\sORDER BY\s/',' ,'.$groupCondStr.' ORDER BY ',$n_sql);
		}
//$adb->setDebug(true);
        $result = $adb->pquery($n_sql, array());
//$adb->setDebug(false);
        $num_rows = $adb->num_rows($result);
        $group_cols = array();

        if ($num_rows > 0) {
            $groupModuleName = self::getModuleNameFromColumnStr($this->report_obj->reportinformations['Group2']);
			while ($row = $adb->fetchByAssoc($result)) {
				if (isset($row[$header_column])) {
                	$rawGroupValue = $row[$header_column];
                } else {
                	$rawGroupValue = $row[$header_alias];
                }
                $group_cols[] = $rawGroupValue;
                $headerLabel = vtranslate($rawGroupValue, $groupModuleName);
                $headerLabel_arr[] = array("style" => $header_style, "label" => $headerLabel, "raw" => $rawGroupValue,);
            }
            $headerLabel_arr[] = array("style" => $header_style, "label" => "LBL_GROUPING_TOTALS", "raw" => "LBL_GROUPING_TOTALS",);
        }

        $group_cols_name = "group_cols_$agi";
        $this->$group_cols_name = $group_cols;
        return $headerLabel_arr;
    }

    private function setGroupColsTotals($group_value, $array_to_totals = array()) {
        if (!empty($array_to_totals)) {
            $adb = PearDatabase::getInstance();

            $totals_result = $array_to_totals[0];
            $custom_field_values = $array_to_totals[1];
            $i = $array_to_totals[2];
            $t_y = $array_to_totals[3];
            for ($i = 0; $i < $t_y; $i++) {
                $fld = $adb->field_name($totals_result, $i);
                if (!in_array($fld->name, $this->g_flds)) {
                    $this->cols_totals[$group_value][$fld->name] = $custom_field_values[$i];
                }
            }
        }
        return true;
    }

    // Rows Rows Cols
    private function setGroupDataArray($group_value = "", $currency_id, $agi = 1) {
        $return_arr = array();
        $g_cols_data_arr = array();
        $g2_cols_data_arr = array();
//ITS4YouReports::sshow($group_value."-".$g_data_value."-".$currency_id);
//ITS4YouReports::sshow($group_value);

        global $default_charset;
        $group_value = html_entity_decode($group_value, ENT_QUOTES, $default_charset);

        // groups 0 1 2
        $bg_color = $this->g_colors[$agi];
        // $agi = actual group id, to get previous group i pgi
        $pgi = $agi - 1;
        $ngi = $agi + 1;
        //if ($group_value != "") {
        $adb = PearDatabase::getInstance();

        $group_sql_name = "group_sql_$ngi";
        $g_con_name = "gi_con_$pgi";
        $exp_gsql = explode("GROUP BY", $this->$group_sql_name);
        $cl_alias[] = $this->g_flds[$pgi];
        $cl_alias[] = $this->g_flds[$agi];
        $cl_alias[] = $this->g_flds[$ngi];

        $group_cols_name = "group_cols_$agi";
        $group_cols = $this->$group_cols_name;
        if (isset($this->report_obj->reportinformations["timeline_columnstr$agi"]) && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "@vlv@") {
            $g_con_col_sql = $this->getTimeLineColumnSql($this->$g_con_name, $this->report_obj->reportinformations["timeline_columnstr$agi"]);
        } else {
            $g_con_col_sql = $this->$g_con_name;
        }

        if (count(explode("WHERE", $exp_gsql[0])) > 1) {
            $wstr = "AND";
        } else {
            $wstr = "WHERE";
        }
        $g_con_sql .= " $wstr " . $g_con_col_sql . ' = "' . $group_value . '" ';
        $f_gsql = $exp_gsql[0] . " " . $g_con_sql . " GROUP BY " . $exp_gsql[1];
// BASE DEBUG GROUP SQL
//$adb->setDebug(true);
/*global $current_user;if($current_user->id=="1"){
    $adb->setDebug(true);
}*/

        $result = $adb->pquery($f_gsql, array());
/*global $current_user;if($current_user->id=="1"){
    $adb->setDebug(false);
}*/
//$adb->setDebug(false);

        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $currency_id = "";
                if (isset($row["currency_id"]) && $row["currency_id"] != "") {
                    $currency_id = $row["currency_id"];
                }
                if (isset($row["invoicing_currency_id"]) && $row["invoicing_currency_id"] != "") {
                    $currency_id = $row["invoicing_currency_id"];
                }
                $r_i = 0;
                $g_datas = array();
                foreach ($row as $r_key => $r_val) {
                    if (!in_array($r_key, $cl_alias)) {
                        $g_datas[$r_key] = $r_val;
                    }
                    $r_i++;
                    if ($r_i == count($row)) {
                        $g2 = $row[$cl_alias[2]];
                        if ($currency_id != "") {
                            $g_cols_data_arr[$currency_id][$row[$cl_alias[1]]][$g2] = $g_datas;
                        } else {
                            $g_cols_data_arr[$row[$cl_alias[1]]][$g2] = $g_datas;
                        }
                    }
                }
            }
        }
        //}
        $this->group_data_array[$group_value] = $g_cols_data_arr;
    }

    private function getSubGroupCols($group_value = "", $agi = 1, $g_con_sql = "", $currency_id = "", $array_to_totals = array()) {
        $return_arr = array();
        $g_cols_data_arr = array();
        $g2_cols_data_arr = array();
        // groups 0 1 2
        $bg_color = $this->g_colors[$agi];
        // $agi = actual group id, to get previous group i pgi
        $pgi = $agi - 1;
        $ngi = $agi + 1;
        $group_value_sql = $group_value;
        $group_value .= $this->get_currency_sumbol_str($currency_id);
        //if ($group_value != "") {
        $adb = PearDatabase::getInstance();

        if (!empty($array_to_totals)) {
            $this->setGroupColsTotals($group_value, $array_to_totals);
        }

        $group_sql_name = "group_sql_$agi";
        $g_con_name = "gi_con_$pgi";
        $exp_gsql = explode("GROUP BY", $this->$group_sql_name);
        $cl_alias = $this->g_flds[$agi];

        $group_cols_name = "group_cols_$agi";
        $group_cols = $this->$group_cols_name;
        if (count(explode("WHERE", $exp_gsql[0])) > 1) {
            $wstr = "AND";
        } else {
            $wstr = "WHERE";
        }

        if (isset($this->report_obj->reportinformations["timeline_columnstr$agi"]) && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "@vlv@") {
            $g_con_col_sql = $this->getTimeLineColumnSql($this->$g_con_name, $this->report_obj->reportinformations["timeline_columnstr$agi"]);
        } else {
            $g_con_col_sql = $this->$g_con_name;
        }

        $g_con_sql .= " $wstr " . $g_con_col_sql . ' = "' . $group_value_sql . '" ';
        if ($currency_id != "") {
            $g_con_sql .= " AND " . $this->currency_id_sql . " = '$currency_id' ";
        }

        global $default_charset;
        $g_con_sql = html_entity_decode($g_con_sql, ENT_QUOTES, $default_charset);
        $f_gsql = $exp_gsql[0] . " " . $g_con_sql . " GROUP BY " . $exp_gsql[1];
//$adb->setDebug(true);
        $result = $adb->pquery($f_gsql, array());
//$adb->setDebug(false);

        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $r_i = 0;
                foreach ($row as $r_key => $r_val) {
                    if (!in_array($r_key, $this->g_flds)) {
                        $g_cols_data_arr[$row[$cl_alias]][$r_key] = $r_val;
                    }
                    if ($r_key == $this->g_flds[1]) {
                        $group2_value = $r_val;
                    }
                }
                if ($agi != 2 && isset($this->g_colors[$ngi]) && $this->g_colors[$ngi] != "") {
                    $g2_cols_data_arr[$group2_value] = $this->getSubGroupCols($group2_value, $ngi, $g_con_sql, $currency_id);
                }
            }
        }
        if ($agi > 1) {
            return $g_cols_data_arr;
        }
        foreach ($group_cols as $g_col_value) {
            $g_con_table = "";
            if (isset($g_cols_data_arr[$g_col_value]["currency_id"]) && isset($this->currency_symbols[$g_cols_data_arr[$g_col_value]["currency_id"]])) {
                $currency_id = $g_cols_data_arr[$g_col_value]["currency_id"];
            }
            if (isset($g_cols_data_arr[$g_col_value]["invoicing_currency_id"]) && isset($this->currency_symbols[$g_cols_data_arr[$g_col_value]["invoicing_currency_id"]])) {
                $currency_id = $g_cols_data_arr[$g_col_value]["invoicing_currency_id"];
            }

            if (isset($g_cols_data_arr[$g_col_value]) && !empty($g_cols_data_arr[$g_col_value])) {
                foreach ($g_cols_data_arr[$g_col_value] as $g_data_key => $g_data_value) {
                    $new_edkey = array();
                    if (in_array($g_data_key, $this->skip_fields)) {
                        continue;
                    }
                    $exploded_datakey = explode("_", $g_data_key);
                    for ($g_di = 0; $g_di < (count($exploded_datakey) - 1); $g_di++) {
                        $new_edkey[] = $exploded_datakey[$g_di];
                        $calculation_type = $exploded_datakey[(count($exploded_datakey) - 1)];
                    }
                    $clen_key = implode("_", $new_edkey);
                    $label_db_key = $this->columns_array[$clen_key] . ":" . strtoupper($calculation_type);
                    if ($r_i > 0 && $this->report_obj->in_multiarray($label_db_key, $this->summaries_columns, "columnname") !== true) {
                        continue;
                    }
                    $g_data_key_lbl = $this->getHeaderLabel($this->report_obj->record, "SM", $g_data_key, $label_db_key);
                    // charts array population start
                    if ($g_data_key == $this->charts["charts_ds_column"]) {
                        // SPECIAL CHART DATA POPULATION FOR G2 COLS
                        $cols_alias = "";
                        if ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
                            $cols_alias = $this->g_flds[1];
                        } elseif ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                            $cols_alias = $this->g_flds[2];
                        }
                        if (!isset($this->ch_array["dataseries_label"])) {
                            $this->setDataseriesLabel($cols_alias);
                        }
                        $this->addToSubvalChArrayValues("dataseries", $g_col_value, $g_data_value, $group_value, $currency_id);
                        $this->setHChArrayValues("hch_dataseries", $group_value, $g_data_value,$currency_id);
                        /* OLDO
                        if ($this->ch_array["charttype"] != "horizontal") {
                            $this->setDataseriesArray($g_col_value, $g_data_value, $currency_id, $g_data_key);
                        }
                        */
                    }
                    // charts array population end

                    $return_arr["headers"][$g_col_value][] = array("style" => "background-color:$bg_color;text-align:center;", "label" => $g_data_key_lbl,);
                    //$txt_align = $this->getFldAlignment($g_data_key, $g_data_value);
                    $fld_style_arr = $this->getFldStyle($g_data_key, $g_data_value);
                    $fld_style = $this->getFldStyleString($fld_style_arr);
                    $return_arr["values"][$g_col_value][] = array("fld_style" => $fld_style, "fld_name" => $g_data_key, "value" => $g_data_value,);
                    $r_i++;
                }
            } else {
                foreach ($this->sum_col_sql_a as $column_str => $calculation_arr) {
                    foreach ($calculation_arr as $calculation_type) {
                        // $calculation_type = $calculation_arr[0];
                        $label_db_key = "$column_str:" . strtoupper($calculation_type);
                        $fld_sql_str_array = explode(" AS ", $this->columns_array[$column_str]["fld_sql_str"]);
                        $fld_str = $fld_sql_str_array[0];
                        $fld_str_as = $fld_sql_str_array[1] . "_$calculation_type";
                        $g_data_key_lbl = $this->getHeaderLabel($this->report_obj->record, "SM", $fld_str_as, $label_db_key);
                        $return_arr["headers"][$g_col_value][] = array("style" => "background-color:$bg_color;text-align:center;", "label" => $g_data_key_lbl,);
                        $return_arr["values"][$g_col_value][] = array("fld_style" => "", "fld_name" => "", "value" => " ",);
                    }
                }
            }
        }

        if (!empty($array_to_totals)) {
            foreach ($this->sum_col_sql_a as $column_str => $calculation_arr) {
                foreach ($calculation_arr as $calculation_type) {
                    // $calculation_type = $calculation_arr[0];
                    $label_db_key = "$column_str:" . strtoupper($calculation_type);
                    $fld_sql_str_array = explode(" AS ", $this->columns_array[$column_str]["fld_sql_str"]);
                    $fld_str = $fld_sql_str_array[0];
                    $fld_str_as = $fld_sql_str_array[1] . "_$calculation_type";
                    $g_data_key_lbl = $this->getHeaderLabel($this->report_obj->record, "SM", $fld_str_as, $label_db_key);
                    $return_arr["headers"]["LBL_GROUPING_TOTALS"][] = array("style" => "background-color:$bg_color;text-align:center;", "label" => $g_data_key_lbl,);
                    $return_arr["values"]["LBL_GROUPING_TOTALS"][] = array("fld_style" => "text-align:right;", "fld_name" => $fld_str_as, "value" => $this->cols_totals[$group_value][$fld_str_as],);
                }
            }
        }
        //}

        return $return_arr;
    }

    private function getSubGroupRow($group_value = "", $currency_id, $agi = 1, $g_con_sql = "") {
        $return_html = "";
        $return_arr = array();
        // groups 0 1 2
        $bg_color = $this->g_colors[$agi];
        // $agi = actual group id, to get previous group i pgi
        $pgi = $agi - 1;
        $ngi = $agi + 1;
        if ($agi > 0) {
            $agi_first_td_class = "rpt4youGrpHead_$agi";
            $ngi_first_td_class = "rpt4youGrpHead_$ngi";
        }
        //if ($group_value != "") {
        $adb = PearDatabase::getInstance();
        $group_sql_name = "group_sql_$agi";
        $g_con_name = "gi_con_$pgi";

        $exp_gsql = explode("GROUP BY", $this->$group_sql_name);

        if (count(explode("WHERE", $exp_gsql[0])) > 1 || $g_con_sql != "") {
            $wstr = "AND";
        } else {
            $wstr = "WHERE";
        }

        if (isset($this->report_obj->reportinformations["timeline_columnstr$agi"]) && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "@vlv@") {
            $g_con_col_sql = $this->getTimeLineColumnSql($this->$g_con_name, $this->report_obj->reportinformations["timeline_columnstr$agi"]);
        } else {
            $g_con_col_sql = $this->$g_con_name;
        }

        if($group_value=="" || $group_value=="-"){
            $g_con_sql .= " $wstr ( " . $g_con_col_sql . " = '' OR " . $g_con_col_sql . " IS NULL )";
        }else{
            $g_con_sql .= " $wstr " . $g_con_col_sql . " = '$group_value'";
        }
//ITS4YouReports::sshow("Problem2-".$group_value);

        if ($currency_id != "") {
            $g_con_sql .= " AND " . $this->currency_id_sql . " = '$currency_id' ";
        }
        global $default_charset;
        $g_con_sql = html_entity_decode($g_con_sql, ENT_QUOTES, $default_charset);

        $f_gsql = $exp_gsql[0] . " " . $g_con_sql;
        if (isset($exp_gsql[1]) && $exp_gsql[1] != "") {
            $f_gsql .= " GROUP BY " . $exp_gsql[1];
        }
//ITS4YouReports::sshow($this->g_o_array);
//$adb->setDebug(true);
        $result = $adb->pquery($f_gsql, array());
//$adb->setDebug(false);
        $num_rows = $adb->num_rows($result);
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $group2_value = $g2_html = "";
                if($this->outputformat!="XLS"){
                    $return_html .= "<tr>";
                }
                $r_i = 0;
                $record_id = $row['record_id'];
                $currency_id = "";
                if (isset($row["currency_id"]) && $row["currency_id"] != "") {
                    $currency_id = $row["currency_id"];
                }
                if (isset($row["invoicing_currency_id"]) && $row["invoicing_currency_id"] != "") {
                    $currency_id = $row["invoicing_currency_id"];
                }
                foreach ($row AS $fld_name => $fld_value) {
                    $is_hid = strpos($fld_name, "_hid");
                    $clear_fld_name_arr = explode("_", $fld_name);
                    $clear_fld_calculation_type = strtoupper($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                    unset($clear_fld_name_arr[(count($clear_fld_name_arr) - 1)]);
                    $clear_fld_name = implode("_", $clear_fld_name_arr);
                    if ($r_i > 0 && $this->report_obj->in_multiarray($this->columns_array[$clear_fld_name] . ":$clear_fld_calculation_type", $this->summaries_columns, "columnname") !== true) {
                        continue;
                    }

                    if ($fld_value == "") {
                        //$fld_value = number_format("0", 3);
                        $fld_value = "-";
                    }
                    if ($is_hid === false && !in_array($fld_name, $this->skip_fields) && !in_array($clear_fld_name, $this->skip_fields)) {
                        if ($fld_name == $this->g_flds[$agi]) {
                            //$group2_value = $fld_value;
                            $group2_value = $this->getFldFormatedValue($this->columns_array["uitype_$fld_name"], $fld_value, $fld_name,"",$currency_id);

                        }
                        if ($agi==1 && !empty($this->charts) && !empty($this->charts["charttypes"])) {
                            $ch_fldname = strtolower($fld_name);
                            $this->setChArrayValues("charttitle", '', $this->charts["charttitle"]);
                            // chart data population start
                            if($this->charts["x_group"]=="group2" && in_array($ch_fldname, $this->charts["charts_ds_columns"])){
                                $this->setDataseriesArray($group2_value, $fld_value, $currency_id, $ch_fldname, $group_value);
                            }
                            // chart data population end
                        }

                        if ($fld_name != $this->g_flds[$pgi] && $fld_name != $this->g_flds[$ngi] && $fld_name != $this->g_flds[($pgi - 1)]) {
                            if ($currency_id != "") {
                                if ($group2_value != "" && $agi != 2) {
                                    $return_arr[$group_value][$group2_value][$fld_name][$currency_id] = $fld_value;
                                } elseif ($group2_value != "" && $agi == 2) {
                                    $return_arr[$group2_value][$fld_name][$currency_id] = $fld_value;
                                } else {
                                    $return_arr[$group_value][$fld_name][$currency_id] = $fld_value;
                                }
                            } else {
                                if ($agi == 1 && $group2_value != "") {
                                    $return_arr[$group_value][$group2_value][$fld_name] = $fld_value;
                                } elseif ($agi == 2 && $group2_value != "") {
                                    $return_arr[$group2_value][$fld_name] = $fld_value;
                                } elseif ($agi == 1) {
                                    $return_arr[$group_value][$fld_name] = $fld_value;
                                } elseif ($agi == 2) {
                                    $return_arr[$group_value][$fld_name] = $fld_value;
                                }
                            }
                            $r_i++;
                        }
                    }
                    if(isset($record_id) && $record_id!=""){
                        if ($agi == 1 && $group2_value != "") {
                            $return_arr[$group_value][$group2_value]["record_id"] = $record_id;
                        } elseif ($agi == 2 && $group2_value != "") {
                            $return_arr[$group2_value]["record_id"] = $record_id;
                        } elseif ($agi == 1) {
                            $return_arr[$group_value]["record_id"] = $record_id;
                        } elseif ($agi == 2) {
                            $return_arr[$group_value]["record_id"] = $record_id;
                        }
                    }
                }
                if ($agi != 2 && isset($this->g_colors[$ngi]) && $this->g_colors[$ngi] != "") {
                    //$return_html .= $this->getSubGroupRow($group2_value,$ngi,$g_con_sql);
                    $return_arr_sub = $this->getSubGroupRow($group2_value, $currency_id, $ngi, $g_con_sql);
                    $return_arr[$group_value][$group2_value]["sub_row"] = $return_arr_sub;

                }
            }
        }

        if (!empty($return_arr[$group_value])) {
            $xls_r_i = 0;
            foreach ($return_arr[$group_value] as $g1column_alias => $g1column_values) {
                $sp_cname_i = 0;
                foreach ($g1column_values as $column_alias => $column_values) {
                    if(isset($g1column_values["record_id"])){
                        $this->data_record_id = $g1column_values["record_id"];
                    }
                    if($column_alias=="record_id"){
                        continue;
                    }
                    if ($sp_cname_i == 0) {
                        $td_class = $agi_first_td_class;
                    } else {
                        $td_class = "rpt4youGrpHead";
                    }
                    if ($column_alias != "sub_row") {
                        $column_arr = explode("_", $column_alias);
                        $calculation_type = strtolower($column_arr[(count($column_arr) - 1)]);
                        $cl_c_alias_arr = array();
                        for ($cl_c_i = 0; $cl_c_i < (count($column_arr) - 1); $cl_c_i++) {
                            $cl_c_alias_arr[] = $column_arr[$cl_c_i];
                        }
                        $cl_c_alias = implode("_", $cl_c_alias_arr);
                        if (is_array($column_values)) {
                            $column_values_f = "";
                            ksort($column_values);
                            $column_values_count = count($column_values);
                            $column_values_i = 0;
                            foreach ($column_values as $currency_id => $column_value) {
                                if ($column_value != "") {
                                    //$txt_align = $this->getFldAlignment($column_alias, $column_value);
                                    $fld_style_arr = $this->getFldStyle($column_alias, $column_value);
                                    $fld_style = $this->getFldStyleString($fld_style_arr);

                                    $rate_symbol = getCurrencySymbolandCRate($currency_id);
                                    $fld_value = $this->getFldNumberFormat($column_alias, $column_value, $currency_id);
                                    /* if (is_numeric($fld_value) && $calculation_type != "count" && $cl_c_alias != "quantity" && !in_array($column_alias, $this->g_flds)) {
                                      $fld_value = CurrencyField::appendCurrencySymbol($fld_value, $rate_symbol['symbol']);
                                      } */
                                    if (is_array($fld_value)) {
                                        $column_values_f = $fld_value;
                                    } else {
                                        $column_values_f .= $fld_value;
                                        $column_values_i++;
                                        if ($column_values_i < $column_values_count) {
                                            $column_values_f .= "<br>";
                                        }
                                    }
                                }
                            }
                            if($this->outputformat=="XLS"){
                                if (is_array($column_values_f)) {
                                    $return_html[$xls_r_i][] = $column_values_f[0];
                                    $return_html[$xls_r_i][] = $column_values_f[1];
                                } else {
                                    $return_html[$xls_r_i][] = $column_values_f;
                                }
                            }else{
                                $return_html .= "<td class='$td_class' style='background-color:$bg_color;$fld_style' nowrap >" . $column_values_f . "</td>";
                            }
                        } else {
                            //$txt_align = $this->getFldAlignment($column_alias, $column_values);
                            $fld_style_arr = $this->getFldStyle($column_alias, $column_value);
                            $fld_style = $this->getFldStyleString($fld_style_arr);

                            if($this->outputformat=="XLS"){
                                $column_values_f = $this->getFldNumberFormat($column_alias, $column_values, "");

                                if (is_array($column_values_f)) {
                                    $return_html[$xls_r_i][] = $column_values_f[0];
                                    $return_html[$xls_r_i][] = $column_values_f[1];
                                } else {
                                    $return_html[$xls_r_i][] = $column_values_f;
                                }
                            }else{
                                $return_html .= "<td class='$td_class' style='background-color:$bg_color;$fld_style' nowrap >" . $this->getFldNumberFormat($column_alias, $column_values, "") . "</td>";
                            }
                        }
                        // charts array population start
                        if ($column_alias == $this->charts["charts_ds_column"]) {
                            if (!isset($this->ch_array["dataseries_label"])) {
                                $this->setDataseriesLabel($column_alias);
                            }
                            $this->addToSubvalChArrayValues("dataseries", $group_value, $column_values, $this->getFldNumberFormat($this->group_cols_array[$ngi], $g1column_alias, "", true), $currency_id);
                        }
                        // charts array population end
                    } else {
                        $xls_r_i++;
                        $sub_bg_color = $this->g_colors[$ngi];
                        foreach ($column_values as $sub_column_alias => $sub_column_arr) {
                            if($this->outputformat!="XLS"){
                                $return_html .= "</tr><tr>";
                            }
                            $n_ri = 0;
                            foreach ($sub_column_arr as $sub_column_alias => $sub_column_values) {
                                if(isset($sub_column_arr["record_id"])){
                                    $this->data_record_id = $sub_column_arr["record_id"];
                                }
                                if($sub_column_alias=="record_id"){
                                    continue;
                                }
                                if ($n_ri == 0) {
                                    $td_class = $ngi_first_td_class;
                                } else {
                                    $td_class = "rpt4youGrpHead";
                                }
                                $column_arr = explode("_", $sub_column_alias);
                                $calculation_type = strtolower($column_arr[(count($column_arr) - 1)]);
                                $cl_c_alias_arr = array();
                                for ($cl_c_i = 0; $cl_c_i < (count($column_arr) - 1); $cl_c_i++) {
                                    $cl_c_alias_arr[] = $column_arr[$cl_c_i];
                                }
                                $cl_c_alias = implode("_", $cl_c_alias_arr);
                                if (is_array($sub_column_values)) {
                                    $column_values_f = "";
                                    ksort($sub_column_values);
                                    $column_values_count = count($sub_column_values);
                                    $column_values_i = 0;

                                    foreach ($sub_column_values as $currency_id => $column_value) {
                                        if (!$n_ri) {
                                            $dataGroup = $column_value;
                                            if ($prevGroup !== $dataGroup) {
                                                $prevGroup = $dataGroup;
                                                $xls_r_i++;
                                            }
                                        }

                                        if ($column_value != "") {
                                            //$txt_align = $this->getFldAlignment($sub_column_alias, $column_value);
                                            $fld_style_arr = $this->getFldStyle($sub_column_alias, $column_value);
                                            $fld_style = $this->getFldStyleString($fld_style_arr);

                                            $fld_value = $this->getFldNumberFormat($sub_column_alias, $column_value, $currency_id);
                                            /* if (is_numeric($fld_value) && $calculation_type != "count" && $cl_c_alias != "quantity" && !in_array($sub_column_alias, $this->g_flds)) {
                                              $fld_value = CurrencyField::appendCurrencySymbol($fld_value, $this->currency_symbols[$currency_id]);
                                              } */
                                            if('XLS' === $this->outputformat && is_array($fld_value)) {
                                                $column_values_f = $fld_value;
                                            } else {
                                                $column_values_f .= $fld_value;
                                                $column_values_i++;
                                                if ($column_values_i < $$column_values_count) {
                                                    $column_values_f .= "<br>";
                                                }
                                            }
                                        }
                                    }
                                    if($this->outputformat=="XLS"){
                                        if (is_array($column_values_f)) {
                                            $return_html[$xls_r_i][] = $column_values_f[0];
                                            $return_html[$xls_r_i][] = $column_values_f[1];
                                        } else {
                                            $return_html[$xls_r_i][] = $column_values_f;
                                        }
                                    }else{
                                        $return_html .= "<td class='$td_class' style='background-color:$sub_bg_color;$fld_style' nowrap >" . $column_values_f . "</td>";
                                    }


                                } else {
                                    //$txt_align = $this->getFldAlignment($sub_column_alias, $sub_column_values);
                                    $fld_style_arr = $this->getFldStyle($sub_column_alias, $sub_column_values);
                                    $fld_style = $this->getFldStyleString($fld_style_arr);

                                    if($this->outputformat=="XLS"){
                                        $fieldValues = $this->getFldNumberFormat($sub_column_alias, $sub_column_values, "");

                                        if (is_array($fieldValues)) {
                                            $r_data[$xls_r_i][] = $fieldValues[0];
                                            $r_data[$xls_r_i][] = $fieldValues[1];
                                        } else {
                                            $r_data[$xls_r_i][] = $fieldValues;
                                        }
                                    }else{
                                        $return_html .= "<td class='$td_class' style='background-color:$sub_bg_color;$fld_style' nowrap >" . $this->getFldNumberFormat($sub_column_alias, $sub_column_values, "") . "</td>";
                                    }
                                }
                                $n_ri++;
//$xls_r_i++;
                            }
                        }
                    }
                    $sp_cname_i++;
                }
                if($this->outputformat!="XLS"){
                    $return_html .= "</tr><tr>";
                }
                $xls_r_i++;
            }
        }
        // DETAIL OF RECORDS START
        // details for group 2 rows
        /* if ($agi == 1) {
          $this->agi1_group_value = $group_value;
          }
          if ($agi == 1 && isset($this->result_details_array[$this->agi1_group_value]) && !empty($this->result_details_array[$this->agi1_group_value])) {
          $selectedcolumns_arr = $this->report_obj->getSelectedColumnListArray($this->report_obj->record);
          if (!empty($selectedcolumns_arr)) {
          if ($currency_id != "" && isset($this->result_details_array[$this->agi1_group_value][$currency_id])) {
          $group_details_array = $this->result_details_array[$this->agi1_group_value][$currency_id];
          } else {
          $group_details_array = $this->result_details_array[$this->agi1_group_value];
          }
          $return_html .= $this->returnGroupDetailRecords($group_details_array, $r_i, $selectedcolumns_arr, $currency_id);
          }
          } */
        // DETAIL OF RECORDS END
        //}

        if ($agi == 2) {
            return $return_arr;
        } else {
            return $return_html;
        }
        //return $return_arr;
    }

    private function returnGroupDetailRecordsNew($group_value, $r_i, $selectedcolumns_arr, $currency_id = "") {
        $return_html = $g_con_sql = "";

        $agi = 1;
        $pgi = 0;
        $g_con_name = "gi_con_$pgi";

        if ($this->report_obj->reportinformations["Group1"] != "none") {
            $detail_sql_arr = explode("GROUP BY", $this->detail_sql);
            $exploded_detail_sql_arr = explode("ORDER BY", $detail_sql_arr[1]);
            $detail_sql_arr[1] = $exploded_detail_sql_arr[1];
        } else {
            $detail_sql_arr = explode("ORDER BY", $this->detail_sql);
        }
        if (!empty($detail_sql_arr)) {
            $adb = PEARDatabase::getInstance();
            $where_pos = strpos($detail_sql_arr[0], "WHERE ");
            if ($where_pos === false) {
                $wstr = "WHERE";
            } else {
                $wstr = "AND";
            }

            if (isset($this->report_obj->reportinformations["timeline_columnstr$agi"]) && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "" && $this->report_obj->reportinformations["timeline_columnstr$agi"] != "@vlv@") {
                $g_con_col_sql = $this->getTimeLineColumnSql($this->$g_con_name, $this->report_obj->reportinformations["timeline_columnstr$agi"]);
            } else {
                $g_con_col_sql = $this->$g_con_name;
            }
            // quick fix campaing rel status
            if (strpos($detail_sql_arr[0], "campaignrelstatus") !== false || strpos($detail_sql_arr[0], "access_count") !== false) {
                if (isset($_REQUEST['advft_criteria']) && $_REQUEST['advft_criteria'] != "" || !empty($this->advf_col_array)) {
                    $wstr = "AND";
                } else {
                    $wstr = "WHERE";
                }
            }

            if(is_null($group_value)){
                $g_con_sql .= " $wstr " . $g_con_col_sql . " IS NULL ";
            }else{
                $g_con_sql .= " $wstr " . $g_con_col_sql . ' = "'.$group_value.'" ';
            }

            if ($currency_id != "") {
                $g_con_sql .= " AND " . $this->currency_id_sql . " = '$currency_id' ";
            }
            global $default_charset;
            $g_con_sql = html_entity_decode($g_con_sql, ENT_QUOTES, $default_charset);

            $df_gsql = $detail_sql_arr[0];

            $detail_sql_arr[0] .= $g_con_sql;
            /* if($this->report_obj->reportinformations["Group1"] != "none"){
              $df_gsql = implode("GROUP BY", $detail_sql_arr);
              }else{ */
            $df_gsql = implode("ORDER BY", $detail_sql_arr);
            //}

            $columns_limit = "";
            if ($this->report_obj->reportinformations["Group1"] == "none" && isset($this->report_obj->reportinformations["columns_limit"]) && $this->report_obj->reportinformations["columns_limit"] != "" && $this->report_obj->reportinformations["columns_limit"] != "0") {
                $columns_limit = " LIMIT " . $this->report_obj->reportinformations["columns_limit"];
            }

            $df_gsql .= $columns_limit;

// !!! BASE DETAILS DEBUG !!!
    /*if ('78.99.157.226'===$_SERVER['REMOTE_ADDR']) {
		$adb->setDebug(true);
	}*/
	//self::displaySqlFormattedQuery($df_gsql);

            $d_result = $adb->pquery($df_gsql, array());
    /*if ('78.99.157.226'===$_SERVER['REMOTE_ADDR']) {
		$adb->setDebug(false);
	}*/
            $d_num_rows = $adb->num_rows($d_result);

            $this->to_totals_array = $this->getToTotalsArray(true);

            if ($d_num_rows > 0) {
                $gr_i = 0;
                while ($detail_row = $adb->fetchByAssoc($d_result)) {
                    // ITS4YOU-CR SlOl 28. 6. 2016 14:32:21
                    if(isset($detail_row["fieldname"]) && $detail_row["fieldname"]!=""){
                        $this->row_fldname = $detail_row["fieldname"];
                        $this->row_module = $detail_row["module"];
                        $this->row_currency_id = $currency_id;
                    }else{
                        $this->row_fldname = "";
                        $this->row_module = "";
                        $this->row_currency_id = "";
                    }
                    // ITS4YOU-END
                    if(!isset($this->outputformat) || $this->outputformat=="HTML" || $this->outputformat == "CHARTS"){
                        $return_html .= "</tr>";
                        $return_html .= "<tr>";
                        foreach ($detail_row as $d_key => $d_value) {
                            if (isset($detail_row["record_id"]) && $detail_row["record_id"] != "") {
                                $this->data_record_id = $detail_row["record_id"];
                            }
                            $is_hid = strpos($d_key, "_hid");
                            if ($is_hid === false && !in_array($d_key, $this->skip_fields) && $this->report_obj->in_multiarray(html_entity_decode($this->detail_columns_array[$d_key], ENT_QUOTES, $default_charset), $this->detail_selectedcolumns_arr, "fieldcolname") === true) {
                                //$txt_align = $this->getFldAlignment($d_key, $d_value);
                                $fld_style_arr = $this->getFldStyle($d_key, $d_value);
                                $fld_style = $this->getFldStyleString($fld_style_arr);

                                $this->to_totals_res = $this->setToTotalsArray($d_num_rows,$this->to_totals_res, $d_key, $d_value, $this->to_totals_array, $currency_id);
                                $return_html .= "<td class='rpt4youGrpHead' style='background-color:#FFFFFF;$fld_style' nowrap >" . $this->getFldNumberFormat($d_key, $d_value, $currency_id) . "</td>";
                            }
                        }
                    }else{
                        foreach ($detail_row as $d_key => $d_value) {
                            if (isset($detail_row["record_id"]) && $detail_row["record_id"] != "") {
                                $this->data_record_id = $detail_row["record_id"];
                            }
                            $is_hid = strpos($d_key, "_hid");
                            if ($is_hid === false && !in_array($d_key, $this->skip_fields) && $this->report_obj->in_multiarray(html_entity_decode($this->detail_columns_array[$d_key], ENT_QUOTES, $default_charset), $this->detail_selectedcolumns_arr, "fieldcolname") === true) {
                                //$txt_align = $this->getFldAlignment($d_key, $d_value);
                                $this->to_totals_res = $this->setToTotalsArray($d_num_rows,$this->to_totals_res, $d_key, $d_value, $this->to_totals_array, $currency_id);
                                $dValue = $this->getFldNumberFormat($d_key, $d_value, $currency_id);
                                if (is_array($dValue)) {
									$return_html[$gr_i][] = $dValue[0];
									$return_html[$gr_i][] = $dValue[1];
								} else {
									$return_html[$gr_i][] = $dValue;
								}
                            }
                        }
                        $gr_i++;
                    }
                }
            }
        }

        return $return_html;
    }

    // ITS4YOU-UP SlOl 19. 5. 2016 9:46:58 - $number_style added
    private function getFldStyle($fld_name = "", $fld_value = "", $number_style=false){

        $fld_style_arr = array();

        $txt_align = "left";
        if ($fld_name != "") {
            $fld_name_arr = explode("_", $fld_name);
            if (in_array(strtolower($fld_name_arr[(count($fld_name_arr) - 1)]), $this->calculation_type_array)) {
                $txt_align = "right";
            }
        }
        if (array_key_exists($fld_name, $this->columns_array) && isset($this->columns_array["uitype_$fld_name"]) && $this->columns_array["uitype_$fld_name"] != "") {
            $fld_ui_type = $this->columns_array["uitype_$fld_name"];
        }
        if (in_array($fld_name_arr[0], ITS4YouReports::$intentory_fields) && !in_array($fld_name_arr[0],ITS4YouReports::$intentory_skip_formating)) {
            $txt_align = "right";
        }
        if (in_array($fld_name, ITS4YouReports::$intentory_fields) && !in_array($fld_name,ITS4YouReports::$intentory_skip_formating)) {
            $txt_align = "right";
        }
        // ITS4YOU-CR SlOl 19. 5. 2016 9:46:52
        if($number_style==true){
            $txt_align = "right";
        }
        // ITS4YOU-END
        $fld_style_arr["text-align"] = $txt_align;

        $listview_max_textlength = vglobal("listview_max_textlength");
        if(strlen($fld_value)>$listview_max_textlength){
            $fld_style_arr["white-space"] = "normal";
        }

        return $fld_style_arr;
    }

    private function getFldStyleString($fld_style_arr=array()){
        $fld_style_str = "";
        if(!empty($fld_style_arr)){
            foreach ($fld_style_arr as $style_key=>$style_value) {
            	$fld_style_str .= $style_key.":".$style_value.";";
            }
            //$fld_style_str = implode(";", $fld_style_arr).";";
        }
        return $fld_style_str;
    }

    private function getFldAlignment($fld_name = "", $fld_value = "") {
        $txt_align = "left";
        if ($fld_name != "") {
            $fld_name_arr = explode("_", $fld_name);
            if (in_array(strtolower($fld_name_arr[(count($fld_name_arr) - 1)]), $this->calculation_type_array)) {
                $txt_align = "right";
            }
        }
        if (array_key_exists($fld_name, $this->columns_array) && isset($this->columns_array["uitype_$fld_name"]) && $this->columns_array["uitype_$fld_name"] != "") {
            $fld_ui_type = $this->columns_array["uitype_$fld_name"];
        }
        if (in_array($fld_name_arr[0], ITS4YouReports::$intentory_fields) && is_numeric($fld_value)) {
            $txt_align = "right";
        }
        if (in_array($fld_name, ITS4YouReports::$intentory_fields) && is_numeric($fld_value)) {
            $txt_align = "right";
        }
        /* if($fld_ui_type=="" && is_numeric($fld_value)){
          $txt_align = "right";
          } */
        return $txt_align;
    }

    private function getFldNumberFormat($fld_name = "", $fld_value = "", $currency_id = "", $skip_format = false) {
        $return_value = $fld_value;

        if ($fld_name != "") {
            $fld_name_arr = explode("_", $fld_name);

            // ITS4YOU-CR SlOl | 16.6.2014 12:06 getFLD UI type and format value start
            if (in_array(strtolower($fld_name_arr[(count($fld_name_arr) - 1)]), $this->calculation_type_array)) {
                $fld_calculation_type = $fld_name_arr[(count($fld_name_arr) - 1)];
                unset($fld_name_arr[(count($fld_name_arr) - 1)]);
            }
            $fld_alias = implode("_", $fld_name_arr);
            $fld_ui_type = 1;
            if (array_key_exists($fld_alias, $this->columns_array) && isset($this->columns_array["uitype_$fld_alias"]) && $this->columns_array["uitype_$fld_alias"] != "") {
                $fld_ui_type = $this->columns_array["uitype_$fld_alias"];
            } elseif (is_array($this->detail_columns_array) && array_key_exists($fld_alias, $this->detail_columns_array) && isset($this->detail_columns_array["uitype_$fld_alias"]) && $this->detail_columns_array["uitype_$fld_alias"] != "") {
                $fld_ui_type = $this->detail_columns_array["uitype_$fld_alias"];
            }

            $return_value = $this->getFldFormatedValue($fld_ui_type, $return_value, $fld_alias, $fld_calculation_type, $currency_id, $skip_format);
            // ITS4YOU-CR SlOl | 16.6.2014 12:06 getFLD UI type and format value end
        }
        return $return_value;
    }

    private function getCurrencyInfo($currencyId) {
        $currencyInfo = array();
        if($currencyId) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery('SELECT * FROM vtiger_currency_info WHERE id=?', array($currencyId));
            if($db->num_rows($result)) return $db->query_result_rowdata($result, 0);
        }

        return $currencyInfo;
    }

    // ITS4YOU-CR SlOl | 16.6.2014 11:35
    private function getFldFormatedValue($fld_uitype, $fld_value, $fld_alias, $fld_calculation_type = "", $currency_id = "", $skip_format = false) {
        global $default_charset;
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUser->set('date_format', self::getUserDateFormatFromDB($currentUser) );
        $trimed_fld_value = trim($fld_value);
        if ($fld_calculation_type != "") {
            $fld_calculation_type = strtolower($fld_calculation_type);
        }
        if(!isset($this->outputformat) || $this->outputformat!="HTML"){
            $skip_format = true;
        }
        if('crmid' === $fld_alias){
            return $fld_value;
        }
        $fldValueForXls = array();

        $c_typeofdata = "";
        if ($trimed_fld_value != "") {
            switch ($fld_uitype) {
                case "9":
                    $fld_value = $this->formatFldNumberValue($trimed_fld_value,$fld_uitype)."%";
                    break;
                case "10":
                    $fld_value = $trimed_fld_value;
                    break;
                case "15":
                    $fieldModule = "";
                    if(isset($this->row_module) && $this->row_module!=""){
                        $fieldModule = $this->row_module;
                    }elseif(isset($this->columns_array[$fld_alias]) && $this->columns_array[$fld_alias]!=""){
                        list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $this->columns_array[$fld_alias]);
                        $module_field_arr = explode("_", $module_field);
                        $fieldModule = $module_field_arr[0];
                    }elseif(isset($this->detail_columns_array[$fld_alias]) && $this->detail_columns_array[$fld_alias]!=""){
                        list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $this->detail_columns_array[$fld_alias]);
                        $module_field_arr = explode("_", $module_field);
                        $fieldModule = $module_field_arr[0];
                    }
                    if($fieldModule!=""){
                        $fld_value = getTranslatedString($trimed_fld_value,$fieldModule);
                    }else{
                        $fld_value = getTranslatedString($trimed_fld_value);
                    }
                    break;
                case "33":
                    $fld_value = str_replace(" |##| ",", ",$trimed_fld_value);
                    break;
                case "17":
                    if ($trimed_fld_value != "" && $trimed_fld_value != "-") {
                        if ($skip_format === true) {
                            $fld_value = $trimed_fld_value;
                        } else {
                            $fld_value = "<a href='http://$trimed_fld_value' target='_blank'>$trimed_fld_value</a>";
                        }
                    }
                    break;
                case "19":
                    if ($fld_alias == "notecontent") {
                        $fld_value = decode_html($trimed_fld_value);
                    } else {
                        if($this->outputformat!="XLS"){
                            //$fld_value = nl2br($trimed_fld_value);
                            $fld_value = decode_html($trimed_fld_value);
                        }else{
                            $fld_value = $trimed_fld_value;
                        }
                    }
                    break;
                case "21":
                case "22":
                case "24":
                    if($this->outputformat!="XLS"){
                        $fld_value = nl2br($trimed_fld_value);
                    }else{
                        $fld_value = $trimed_fld_value;
                    }
                    break;
                case "56":
                    if ($trimed_fld_value == 1) {
                        //Since "yes" is not been translated it is given as app strings here..
                        $fld_value = getTranslatedString('yes');
                    } else {
                        $fld_value = getTranslatedString('no');
                    }
                    break;
                case "66":
                case "67":
                case "68":
                    if(is_numeric($trimed_fld_value)){
                        $parent_module = getSalesEntityType($trimed_fld_value);
                        $displayValueArray = getEntityName($parent_module, $trimed_fld_value);
                        if (!empty($displayValueArray)) {
                            foreach ($displayValueArray as $key => $value) {
                                $displayValue = $value;
                            }
                        }
                        if ($skip_format === true) {
                            $fld_value = $displayValue;
                        } else {
                            global $site_URL;
                            $fld_value = "<a href='$site_URL/index.php?module=$parent_module&view=Detail&record=$trimed_fld_value' >$displayValue</a>";
                            //$fld_value = "<a href='index.php?module=$parent_module&action=DetailView&record=$trimed_fld_value' >$displayValue</a>";
                        }
                    }
                    break;
                case "5":
                case "23":
                    if (!in_array($fld_alias, $this->g_flds)) {
                        $date = new DateTimeField($trimed_fld_value);
                        $fld_value = $date->getDisplayDate($currentUser);
                    }
                    break;
                case "6":
                    $c_typeofdata = "D";
                    if(isset($this->columns_array[$fld_alias])){
                        list($c_tablename, $c_colname, $c_module_field, $c_fieldname, $c_typeofdata) = explode(":", $this->columns_array[$fld_alias]);
                    }
                    if ($c_typeofdata!="DT" && !in_array($fld_alias, $this->g_flds)) {
                        $date = new DateTimeField($trimed_fld_value);
                        $fld_value = $date->getDisplayDate($currentUser);
                    }
                    break;
                case "70":
                    if (!in_array($fld_alias, $this->g_flds)) {
                        if ('-' !== $trimed_fld_value) {
                            $date = new DateTimeField($trimed_fld_value);
                            $fld_value = $date->getDisplayDateTimeValue($currentUser);
                        }
                    }
                    break;
                case '71':
                case '72':
                    if ('-' === $trimed_fld_value) {
                        $trimed_fld_value = 0;
                    }
                    $currency_id = self::getCurrencyForFld($currency_id);

                    if ('XLS' === $this->outputformat) {
                        $convertedFldValue = CurrencyField::convertToUserFormat($trimed_fld_value, null, false, true);
                        $fldValueForXls = array(
                            $convertedFldValue,
                            $this->currency_symbols[$currency_id]
                        );
                    } else {
                        $convertedFldValue = CurrencyField::convertToUserFormat($trimed_fld_value);
                        if ($skip_format) {
                            $currencySeparator = html_entity_decode($currentUser->column_fields['currency_grouping_separator'], ENT_QUOTES, $default_charset);
                            $decimalSeparator  = $currentUser->column_fields['currency_decimal_separator'];

                            $fld_value = str_replace("$currencySeparator", '', $convertedFldValue);
                            $fld_value = str_replace(' ', '', $fld_value);
                            $fld_value = str_replace("$decimalSeparator", '.', $fld_value);
                        } else{
                            $fld_value = CurrencyField::appendCurrencySymbol($convertedFldValue, $this->currency_symbols[$currency_id]);
                        }
                    }
                    break;
                case "712":
                    if ($trimed_fld_value == "-") {
                        $trimed_fld_value = 0;
                    }
                    $fld_value = number_format($trimed_fld_value, "3", ".", "");
                    $currencyField = new CurrencyField($fld_value);
                    if ($currency_id != "" && isset($this->currency_symbols[$currency_id])) {
                        if ('XLS' !== $this->outputformat) {
                            $fld_value = $currencyField->getDisplayValue(null, true);
                        }

                        if ('XLS' === $this->outputformat) {
                            $fldValueForXls = array(
                                $fld_value,
                                $this->currency_symbols[$currency_id]
                            );
                        } else {
                            $fld_value = CurrencyField::appendCurrencySymbol($fld_value, $this->currency_symbols[$currency_id]);
                        }
                    } else {
                        if(1<$currentUser->get('currency_id')) {
                            $baseCurrencyInfo = $this->getCurrencyInfo($currentUser->get('currency_id'));
                        } else {
                            $baseCurrencyInfo = Vtiger_Util_Helper::getBaseCurrency();
                        }
                        $conversionRate = $baseCurrencyInfo["conversion_rate"];
                        if( $conversionRate > 1 ) {
                            $fld_value = ( $fld_value * $conversionRate );
                            $currencyField = new CurrencyField($fld_value);
                        }
                        if ('XLS' !== $this->outputformat) {
                            $fld_value = $currencyField->getDisplayValue(null, true);
                            $currencySymbol = $currencyField->getCurrencySymbol();
                        }

                        if ('XLS' === $this->outputformat) {
                            $fldValueForXls = array(
                                $fld_value,
                                $currencySymbol
                            );
                        } else {
                            $fld_value = CurrencyField::appendCurrencySymbol($fld_value, $currencySymbol);
                        }
                    }
                    break;
                case "75":
                case "76":
                case "78":
                case "79":
                case "80":
                case "81":
                    if(is_numeric($trimed_fld_value)){
                        $parent_module = getSalesEntityType($trimed_fld_value);
                        if (vtlib_isModuleActive($parent_module)) {
                            $displayValueArray = getEntityName($parent_module, $trimed_fld_value);
                            if (!empty($displayValueArray)) {
                                foreach ($displayValueArray as $key => $value) {
                                    $displayValue = $value;
                                }
                            }
                            if ($skip_format === true) {
                                $fld_value = $displayValue;
                            } else {
                                global $site_URL;
                                $fld_value = "<a href='$site_URL/index.php?module=$parent_module&view=Detail&record=$trimed_fld_value' >$displayValue</a>";
                                //$fld_value = "<a href='index.php?module=$parent_module&action=DetailView&record=$trimed_fld_value' >$displayValue</a>";
                            }
                        }
                    }
                    break;
                case "98":
                    if(is_numeric($fld_value) && !is_bool($fld_value)){
                        $fld_value = getRoleName($fld_value);
                    }
                    break;
                // ITS4YOU-CR SlOl 16. 2. 2016 6:33:53 - IT&M uitype 120 customization s
                case "120":
                    if($fld_value!="" && $fld_value!="-"){
                        $users_array = explode(",",$fld_value);
                        $t_fld_arr = array();
                        foreach($users_array as $fldUId){
                            $t_fld_arr[] = getUserFullName($fldUId);
                        }
                        $fld_value = implode(",",$t_fld_arr);
                    }
                    break;
                // ITS4YOU-END - IT&M uitype 120 customization e
                default:
                    // ITS4YOU-UP SlOl | 26.8.2015 10:43
                    // assumption: all of fields contains uitype expect of inventory fields ! ...
                    if($this->columns_array["uitype_$fld_alias"]==""){
                        $fld_array = $clear_fld_array = explode("_",$fld_alias);
                        $relfieldid = $fld_array[(count($fld_array)-1)];
                        if(is_numeric($relfieldid)){
                            unset($clear_fld_array[(count($fld_array)-1)]);
                            $adb = PearDatabase::getInstance();
                            $relfieldres = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldid = ? ", array($relfieldid));
                            if($adb->num_rows($relfieldres) > 0){
                                $rel_field_row = $adb->fetchByAssoc($relfieldres, 0);
                                $rel_field_uitype = $rel_field_row["uitype"];
                                if ($rel_field_uitype == 10) {
                                    unset($clear_fld_array[(count($fld_array)-2)]);
                                }
                            }
                            $fld_alias = implode("_", $clear_fld_array);
                        }
                    }

                    if(($fld_uitype=="4" && $fld_alias==$this->primary_no_column && $this->data_record_id!="")
                    || ('pohoda_number' === $fld_alias && $this->data_record_id!="")
                    ){
                     if ($skip_format !== true){
                            $link_id = $this->data_record_id;
                            $link_module = getSalesEntityType($link_id);
                            global $site_URL;
                            $fld_value = "<a href='".$site_URL."/index.php?module=$link_module&view=Detail&record=$link_id' >$fld_value</a>";
                        }
                    }
                    // ITS4YOU-END 26.8.2015
                    // ITS4YOU-UP SlOl 29. 3. 2017 9:59:01
                    if(isset($this->columns_array[$fld_alias])){
                        list($c_tablename, $c_colname, $c_module_field, $c_fieldname, $c_typeofdata) = explode(":", $this->columns_array[$fld_alias]);
                    }
                    if ('T' === $c_typeofdata) {
                        /*if ( '-' !== $fld_value ) {
                            $fld_value = Vtiger_Datetime_UIType::getDateTimeValue($fld_value);
                        }*/
                    // ITS4YOU-END 29. 3. 2017 9:59:04
                    } elseif ($fld_alias=="converted") {
                        if ($trimed_fld_value == 1) {
                            //Since "yes" is not been translated it is given as app strings here..
                            $fld_value = getTranslatedString('Converted');
                        } else {
                            $fld_value = getTranslatedString('Not Converted');
                        }
                    // oldo1
                    } elseif (preg_match("/^quantity/", $fld_alias) && is_numeric($fld_value)) {
                        $fld_value = $this->formatFldNumberValue($fld_value, "0", 3);
                    }elseif ($fld_alias != "prodname" && in_array($fld_alias, ITS4YouReports::$intentory_fields) && is_numeric($fld_value) && $fld_calculation_type != "count") {

                        if ('XLS' === $this->outputformat) {
                            $fldValueForXls = array(
                                $fld_value,
                                $this->currency_symbols[$currency_id]
                            );
                        } else {
                            $fld_value = $this->formatFldNumberValue($fld_value, $currency_id);
                        }
                    } elseif (isset($fld_calculation_type) && !in_array($fld_calculation_type, array("", "count"))) {
                        $fld_value = $this->formatFldNumberValue($fld_value);
                    }elseif(in_array($fld_alias,ITS4YouReports::$modTrackerColumns)){
                        if($fld_value!=""){
                            switch ($fld_alias) {
                                case "module":
                                    $fld_value = vtranslate($trimed_fld_value,$trimed_fld_value);
                                break;
                                case "changedon":
                                    $date = new DateTimeField($trimed_fld_value);
                                    $fld_value = $date->getDisplayDate();
                                break;
                                case "status":
                                    switch ($trimed_fld_value) {
                                        case "0":
                                            $fld_value = vtranslate('LBL_UPDATED');
                                    	break;
                                        case "1":
                                            $fld_value = vtranslate('LBL_DELETED');
                                    	break;
                                        case "2":
                                            $fld_value = vtranslate('LBL_CREATED');
                                    	break;
                                        case "3":
                                            $fld_value = vtranslate('LBL_RESTORED');
                                    	break;
                                    }
                                break;
                                case "fieldname":
                                    $adb = PearDatabase::getInstance();
                                    $p_module = $this->report_obj->primarymodule;
                                    $p_module_id = $this->report_obj->primarymoduleid;
                                    switch ($trimed_fld_value) {
                                        case "record_module":
                                            $fld_value = vtranslate("LBL_MODULENAMES",$this->currentModule);
                                        break;
                                        case "record_id":
                                            $fld_value = vtranslate("LBL_RECORD");
                                        break;
                                        default:
                                            $fld_res = $adb->pquery("SELECT fieldlabel FROM vtiger_field WHERE fieldname = ? AND tabid = ? ", array($trimed_fld_value, $p_module_id));
                                            $fld_nr = $adb->num_rows($fld_res);
                                            if ($fld_nr > 0) {
                                                while ($fld_row = $adb->fetchByAssoc($fld_res)) {
                                                    $fld_value = vtranslate($fld_row["fieldlabel"],$p_module);
                                                }
                                            }else{
                                                $fld_value = $trimed_fld_value;
                                            }
                                    	break;
                                    }
                                break;
                                case "prevalue":
                               case "postvalue":
                                    if($this->row_fldname!=""){
                                        $adb = PearDatabase::getInstance();
                                        $fld_res = $adb->pquery("SELECT uitype FROM vtiger_field WHERE fieldname = ? AND tabid = ? ", array($this->row_fldname, getTabid($this->row_module)));
                                        $fld_nr = $adb->num_rows($fld_res);
                                        if ($fld_nr > 0) {
                                            while ($fld_row = $adb->fetchByAssoc($fld_res)) {
                                                $uitype = $fld_row["uitype"];
                                            }
                                            if(in_array($uitype,ITS4YouReports::$s_users_uitypes)){
                                                $trimed_fld_value = getUserFullName($trimed_fld_value);
                                            }
                                            $fld_value = $this->getFldFormatedValue($uitype, $trimed_fld_value, $this->row_fldname,"",$this->row_currency_id);
                                        }else{
                                            $fld_value = $trimed_fld_value;
                                        }
                                    }
                                break;
                                default:
                            	   $fld_value = $trimed_fld_value;
                            	break;
                            }
                        }
                    }
                    break;
            }
        } else {
            $fld_value = "";
        }

        // ITS4YOU-UP SlOl 7. 4. 2016 13:54:23 hyperlink
        if (($skip_format !== true && $this->report_obj->reportinformations["list_link_field"] === $fld_alias) || ($fld_alias=="r4u_hyperlink")) {
            if ($this->data_record_id != "") {
                $parenttab = getParentTab();
                if($this->report_obj->primarymodule=="Calendar"){
                    $data_module = getSalesEntityType($this->data_record_id);
                }else{
                    $data_module = $this->report_obj->primarymodule;
                }
                $recordId = $this->data_record_id;
                global $site_URL;
                // ITS4YOU-UP SlOl 7. 4. 2016 13:54:23 hyperlink
                $r4u_hyperlink = "$site_URL/index.php?module=$data_module&view=Detail&record=$recordId";
                if($fld_alias=="r4u_hyperlink"){
                    $fld_value = $r4u_hyperlink;
                }else{
                    $fld_value = "<a href='$r4u_hyperlink' title='" . getTranslatedString($data_module, $data_module) . "' >$fld_value</a>";
                }
                // ITS4YOU-END
            }
        }
        if (!empty($fldValueForXls)) {
            $returnValue = $fldValueForXls;
        } else {
            $returnValue = $fld_value;
        }

        return $returnValue;
    }

    private function formatFldNumberValue($fld_value, $currency_id = "", $dec_number = "3") {
        if($fld_value!=""){
            $fld_value = number_format($fld_value, $dec_number, ".", "");
            if($currency_id != ""){
                if (isset($this->currency_symbols[$currency_id])) {
                    $currencyField = new CurrencyField($fld_value);
                    $fld_value = $currencyField->getDisplayValue(null, true);
                    $fld_value = CurrencyField::appendCurrencySymbol($fld_value, $this->currency_symbols[$currency_id]);
                } else {
                    $currencyField = new CurrencyField($fld_value);
                    $fld_value = $currencyField->getDisplayValue(null, true);
                }
            }
        }
        return $fld_value;
    }

    // ITS4YOU-END 16.6.2014 11:35
    // ITS4YOU-CR SlOl | 15.5.2014 13:28
    private function getDateSQLFormat($date_format) {
        $date_format = str_replace("dd", "%d", $date_format);
        $date_format = str_replace("mm", "%m", $date_format);
        $date_format = str_replace("yyyy", "%Y", $date_format);
        return $date_format;
    }

    private function getTimeLineColumnSql($column_sql, $timeline_col_str = "") {
        global $current_user;
        $return = $column_sql;
        if ($timeline_col_str != "" && $timeline_col_str != "@vlv@") {
            $timeline_col_array = explode("@vlv@", $timeline_col_str);
            if (isset($timeline_col_array[1]) && $timeline_col_array[1] != "") {
                $timeline_freq = $timeline_col_array[1];
                // %d	Day of the month, numeric (00..31)
                // %V	Week (01..53), where Sunday is the first day of the week; WEEK() mode 2; used with %X
                // %v	Week (01..53), where Monday is the first day of the week; WEEK() mode 3; used with %x
                // %m	Month, numeric (00..12)
                // %M	Month name
                // %b	Abbreviated month name
                // %Y	Year, numeric, four digits
                // DATE_FORMAT($column_sql, '%Y-%M %Y')
                // $timeline_freq = "DAYS";
                // $timeline_freq = "WEEK";
                // $timeline_freq = "MONTH";
                // $timeline_freq = "QUARTER";
                // $timeline_freq = "HALFYEAR";
                // $timeline_freq = "YEAR";
                switch ($timeline_freq) {
                    case "DAYS":
                        $format = $current_user->date_format;
                        if (empty($format)) {
                            $format = 'dd-mm-yyyy';
                        }
                        $format = $this->getDateSQLFormat($format);
                        $return = "DATE_FORMAT($column_sql,'$format')";
                        break;
                    case "WEEK":
                        $format = '%Y-%v';
                        // $format = '%Y-%V';
                        $return = "DATE_FORMAT($column_sql,'$format')";
                        break;
                    case "MONTH":
                        $format = '%b %Y';
                        $return = "DATE_FORMAT($column_sql,'$format')";
                        break;
                    case "QUARTER":
                        $return = "CONCAT(YEAR($column_sql),'-',QUARTER($column_sql),'Q')";
                        break;
                    case "HALFYEAR":
                        $return = "IF(((MONTH($column_sql)-6)/2)<0,DATE_FORMAT($column_sql,'%Y-1H'),DATE_FORMAT($column_sql,'%Y-2H'))";
                        break;
                    case "YEAR":
                        $format = '%Y';
                        $return = "DATE_FORMAT($column_sql,'$format')";
                        break;
                }
            }
        }
        return $return;
    }

    // ITS4YOU-END 15.5.2014 13:28
    // ITS4YOU-CR SlOl | 29.5.2014 10:27
    private function getGroupTotalsValue($g_t_key, $g_t_array) {
        $fld_name_exploded = explode("_", $g_t_key);
        $calculation_type = strtolower($fld_name_exploded[(count($fld_name_exploded) - 1)]);
        switch ($calculation_type) {
            case "count":
                $g_t_value = array_sum($g_t_array);
                break;
            case "sum":
                $g_t_value = array_sum($g_t_array);
                break;
            case "avg":
                $g_t_value = (array_sum($g_t_array) / count($g_t_array));
                break;
            case "min":
                $g_t_value = min(array_filter($g_t_array));
                break;
            case "max":
                $g_t_value = max($g_t_array);
                break;
        }
        return $g_t_value;
    }

    // ITS4YOU-END 29.5.2014 10:27
    // ITS4YOU-CR SlOl | 12.6.2014 9:30
    private function getSummariesConditions($report) {
        // SUMMARIES CRITERIA START
        $report->getSummariesFilterList($report->record);
        $ngc_column_sql = "";
        if (isset($report->summaries_criteria) && !empty($report->summaries_criteria)) {
            $sc_count = count($report->summaries_criteria);
            $sc_i = 1;
            foreach ($report->summaries_criteria as $g_i => $gc_column_array) {
                $ngc_columnname = "";
                $ngc_columnname_array = array();
                $gc_columnname = $gc_column_array["columnname"];
                $gc_comparator = $gc_column_array["comparator"];
                $gc_value = $gc_column_array["value"];
                $gc_column_condition = "AND"; // $gc_column_array["column_condition"]

                $gc_columnname_array = explode(":", $gc_columnname);
                $count_gca = (count($gc_columnname_array) - 1);
                for ($gc_i = 0; $gc_i < ($count_gca); $gc_i++) {
                    $ngc_columnname_array[] = $gc_columnname_array[$gc_i];
                }
                $gc_calculation_type = $gc_columnname_array[$count_gca];
                $ngc_columnname = implode(":", $ngc_columnname_array);
                $gc_column_sql = $this->columns_array[$ngc_columnname]['fld_cond'];
                $gc_comparator_sql = $this->getAdvComparator($gc_comparator, trim($gc_value));

                $ngc_column_sql .= " $gc_calculation_type($gc_column_sql) $gc_comparator_sql ";
                if ($sc_i < $sc_count) {
                    $ngc_column_sql .= $gc_column_condition;
                }
                $sc_i++;
            }
        }
        return $ngc_column_sql;
        // SUMMARIES CRITERIA END
    }

    // ITS4YOU-END 12.6.2014 9:30
    // ITS4YOU-CR SlOl | 8.7.2014 8:57
    private function setChartsColumns() {
        $this->ch_array = array();
        if (isset($this->report_obj->reportinformations["charts"]) && !empty($this->report_obj->reportinformations["charts"])) {

            $report_charts = $this->report_obj->reportinformations["charts"];
            foreach($report_charts as $ch_i => $chart_array){
                if($chart_array["charttype"]!="none"){
                    $dataseries = $chart_array["dataseries"];
                    $dataseries_col_arr = explode(":", $dataseries);
                    $ds_lastkey = (count($dataseries_col_arr) - 1);
                    $dataseries_calculationtype = strtolower($dataseries_col_arr[$ds_lastkey]);
                    unset($dataseries_col_arr[$ds_lastkey]);
                    $dataseries_column_str = implode(":", $dataseries_col_arr);

                    if (isset($this->columns_array[$dataseries_column_str]["fld_alias"]) && $this->columns_array[$dataseries_column_str]["fld_alias"] != "") {
                        $charts_ds_column = $this->columns_array[$dataseries_column_str]["fld_alias"] . "_" . $dataseries_calculationtype;
                        $this->charts["charts_ds_columns"][] = $charts_ds_column;
                    }
                    /*
                    if (isset($this->columns_array[$chart_array["x_group"]]["fld_alias"]) && $this->columns_array[$chart_array["x_group"]]["fld_alias"] != "") {
                        $this->charts["x_group_str"] = $chart_array["x_group"];
                        $x_group = $this->columns_array[$chart_array["x_group"]]["fld_alias"];
                        $this->charts["x_group"] = $x_group;
                    }
                    */
                    $this->charts["x_group"] = strtolower($chart_array["x_group"]);
                    $this->charts["charttitle"] = $chart_array["charttitle"];
                    $this->charts["charttypes"][$charts_ds_column] = $chart_array["charttype"];
                }
            }

            /*
            $report_charts = $this->report_obj->reportinformations["charts"];
            $charttype = $report_charts["charttype"];
            $this->charts["charttype"] = $charttype;
            if ($charttype != "" && $charttype != "none") {
                $dataseries = $report_charts["dataseries"];
                $charttitle = $report_charts["charttitle"];
                $this->charts["charttitle"] = $charttitle;
                $dataseries_col_arr = explode(":", $dataseries);
                $ds_lastkey = (count($dataseries_col_arr) - 1);
                $dataseries_calculationtype = strtolower($dataseries_col_arr[$ds_lastkey]);
                unset($dataseries_col_arr[$ds_lastkey]);
                $dataseries_column_str = implode(":", $dataseries_col_arr);

                if (isset($this->columns_array[$dataseries_column_str]["fld_alias"]) && $this->columns_array[$dataseries_column_str]["fld_alias"] != "") {
                    $charts_ds_column = $this->columns_array[$dataseries_column_str]["fld_alias"] . "_" . $dataseries_calculationtype;
                    $this->charts["charts_ds_column"] = $charts_ds_column;
                }
            }
            */
        }
        return;
    }

    private function setChArrayValues($ch_key, $ch_subkey, $ch_value, $g_data_key="", $base_group="") {
        if ($ch_subkey == "") {
            $ch_subkey = getTranslatedString("LBL_NONE");
        }
        global $default_charset;
        $ch_key = html_entity_decode($ch_key, ENT_QUOTES, $default_charset);
        $ch_subkey = html_entity_decode($ch_subkey, ENT_QUOTES, $default_charset);
        $ch_value = html_entity_decode($ch_value, ENT_QUOTES, $default_charset);

        $ch_key = addslashes($ch_key);
        $ch_subkey = addslashes($ch_subkey);
        $ch_value = addslashes($ch_value);

        if ($ch_key == "dataseries_label"){
            if (!in_array($ch_value, $this->ch_array[$ch_key][$ch_subkey])) {
                if ($ch_subkey == "subval") {
                    $this->ch_array[$ch_key][$ch_subkey][] = $ch_value;
                } else {
                    if (isset($this->ch_array[$ch_key][$ch_subkey])) {
                        $this->ch_array[$ch_key][$ch_subkey] += $ch_value;
                    } else {
                        $this->ch_array[$ch_key][$ch_subkey] = $ch_value;
                    }
                }
            }
        }elseif($ch_key=="dataseries" && $g_data_key!="") {
            if($base_group!=""){
                if (!in_array($ch_value, $this->ch_array[$ch_key][$g_data_key][$ch_subkey])) {
                    if ($ch_subkey == "subval") {
                        $this->ch_array[$ch_key][$g_data_key][$base_group][$ch_subkey][] = $ch_value;
                    } else {
                        if (isset($this->ch_array[$ch_key][$g_data_key][$base_group][$ch_subkey])) {
                            $this->ch_array[$ch_key][$g_data_key][$base_group][$ch_subkey] += $ch_value;
                        } else {
                            $this->ch_array[$ch_key][$g_data_key][$base_group][$ch_subkey] = $ch_value;
                        }
                    }
                }
            }else{
                if (!in_array($ch_value, $this->ch_array[$ch_key][$g_data_key][$ch_subkey])) {
                    if ($ch_subkey == "subval") {
                        $this->ch_array[$ch_key][$g_data_key][$ch_subkey][] = $ch_value;
                    } else {
                        if (isset($this->ch_array[$ch_key][$g_data_key][$ch_subkey])) {
                            $this->ch_array[$ch_key][$g_data_key][$ch_subkey] += $ch_value;
                        } else {
                            $this->ch_array[$ch_key][$g_data_key][$ch_subkey] = $ch_value;
                        }
                    }
                }
            }
        } else {
            if (!in_array($ch_value, $this->ch_array[$ch_key])) {
                $this->ch_array[$ch_key] = $ch_value;
            }
        }
    }

    private function addToSubvalChArrayValues($ch_key, $ch_subkey, $ch_value, $option_key = "", $currency_id = "") {
        global $default_charset;
        $ch_key = html_entity_decode($ch_key, ENT_QUOTES, $default_charset);
        if ($ch_subkey == "") {
            $ch_subkey = getTranslatedString("LBL_NONE");
        }
        if ($ch_subkey != "") {
            $this->setDataseriesLabel($option_key, "subval");
            if ($currency_id != "") {
                $ch_subkey = $ch_subkey . " (" . $this->currency_symbols[$currency_id] . ")";
            }
            $ch_subkey = html_entity_decode($ch_subkey, ENT_QUOTES, $default_charset);
            if ($option_key != "") {
                $this->ch_array[$ch_key]["subval"][$ch_subkey][$option_key][] = $ch_value;
            } else {
                $this->ch_array[$ch_key]["subval"][$ch_subkey][] = $ch_value;
            }
        } else {
            $this->ch_array[$ch_key]["subval"][] = $ch_value;
        }
    }

    private function getReportCharts($reportObj, $export_pdf_format, $currency_id = "") {
        $report_html = "";
        $is_stacked = false;

        global $current_user;
        global $default_charset;
        $filename = "R4YouCharts";
        if (isset($current_user) && $current_user->id != "") {
            $filename .= "_" . $current_user->id;
        }
        $reportid = $this->report_obj->record;
        $filename .= "_" . $reportid;

        $ch_data = "";
        $dataseries = array();

        if (isset($this->ch_array) && $this->ch_array["charttype"] != "none" && !empty($this->ch_array["dataseries"])) {
            $charttype = $this->ch_array["charttype"];
            $charttitle = html_entity_decode($this->ch_array["charttitle"], ENT_QUOTES, $default_charset);
            if ($charttype == "funnel") {
                $chart_lbl_key = $this->ch_array["dataseries_label"]["val"];
                $chart_lbl_val = $this->ch_array["dataseries_label"]["key"];
            } else {
                $chart_lbl_key = $this->ch_array["dataseries_label"]["key"];
                $chart_lbl_val = $this->ch_array["dataseries_label"]["val"];
            }
            // Sort Cols Values Array Start
            if ($this->report_obj->reportinformations["timeline_type2"] == "cols") {
                if ($this->report_obj->reportinformations["Sort2"] == "Descending") {
                    krsort($this->ch_array["dataseries"]);
                    krsort($this->ch_array["dataseries"]["subval"]);
                } else {
                    ksort($this->ch_array["dataseries"]);
                    ksort($this->ch_array["dataseries"]["subval"]);
                }
            } elseif ($this->report_obj->reportinformations["timeline_type3"] == "cols") {
                if ($this->report_obj->reportinformations["Sort3"] == "Descending") {
                    krsort($this->ch_array["dataseries"]);
                    krsort($this->ch_array["dataseries"]["subval"]);
                } else {
                    ksort($this->ch_array["dataseries"]);
                    ksort($this->ch_array["dataseries"]["subval"]);
                }
            }

            // Sort Cols Values Array End
            // Stack Horizontal Charts for better Look  ;)
            if ($this->ch_array["charttype"] == "horizontal" && isset($this->ch_array["dataseries"]["subval"]) && !empty($this->ch_array["dataseries"]["subval"])) {
                $is_stacked = true;
                $dataseries[] = array($chart_lbl_key);
            } else {
                $dataseries[] = array($chart_lbl_key, $chart_lbl_val);
            }

            if (isset($this->ch_array["dataseries"]["subval"]) && !empty($this->ch_array["dataseries"]["subval"])) {
                if (isset($this->ch_array["dataseries_label"]["subval"]) && !empty($this->ch_array["dataseries_label"]["subval"])) {
                    foreach ($this->ch_array["dataseries_label"]["subval"] as $sv_key => $sv_label) {
                        $dataseries[0][] = $sv_label;
                    }
                }
            }
            if ($is_stacked) {
                $ch_array_dataseries = $this->ch_array["dataseries"]["subval"];
            } else {
                $ch_array_dataseries = $this->ch_array["dataseries"];
            }

            foreach ($ch_array_dataseries as $ch_key => $ch_value) {
                $dataseries_arr = array();

                if ($ch_key != "subval") {
                    $ch_key_coded = $ch_key;
                    $ch_key = html_entity_decode($ch_key, ENT_QUOTES, $default_charset);
                    $ch_value = html_entity_decode($ch_value, ENT_QUOTES, $default_charset);
                    settype($ch_value, "integer");
                    settype($ch_key, "string");
                    $dataseries_arr[] = $ch_key;
                    if (!$is_stacked) {
                        $dataseries_arr[] = $ch_value;
                    }
                    if (isset($this->ch_array["dataseries"]["subval"]) && !empty($this->ch_array["dataseries"]["subval"])) {
                        if (isset($this->ch_array["dataseries"]["subval"][$ch_key]) && !empty($this->ch_array["dataseries"]["subval"][$ch_key])) {
                            foreach ($this->ch_array["dataseries_label"]["subval"] as $sv_key => $sv_label) {
                                if (isset($this->ch_array["dataseries"]["subval"][$ch_key][$sv_label])) {
                                    if (is_array($this->ch_array["dataseries"]["subval"][$ch_key][$sv_label][0])) {
                                        $ds_val = end($this->ch_array["dataseries"]["subval"][$ch_key][$sv_label][0]);
                                    } elseif (count($this->ch_array["dataseries"]["subval"][$ch_key][$sv_label]) > 1) {
                                        $ds_val = array_sum($this->ch_array["dataseries"]["subval"][$ch_key][$sv_label]);
                                    } else {
                                        $ds_val = $this->ch_array["dataseries"]["subval"][$ch_key][$sv_label][0];
                                    }
                                    settype($ds_val, "integer");
                                    $dataseries_arr = array_merge($dataseries_arr, array($ds_val));
                                } else {
                                    $dataseries_arr = array_merge($dataseries_arr, array(0));
                                }
                            }
                        } else {
                            foreach ($this->ch_array["dataseries_label"]["subval"] as $sv_label) {
                                $dataseries_arr = array_merge($dataseries_arr, array(0));
                            }
                        }
                    }
                    $dataseries[] = $dataseries_arr;
                }
            }
            if ($charttype == "horizontal") {
                $yaxis_title = html_entity_decode($chart_lbl_val, ENT_QUOTES, $default_charset);
                $xaxis_title = html_entity_decode($chart_lbl_key, ENT_QUOTES, $default_charset);
            } else {
                $xaxis_title = html_entity_decode($chart_lbl_val, ENT_QUOTES, $default_charset);
                $yaxis_title = html_entity_decode($chart_lbl_key, ENT_QUOTES, $default_charset);
            }

            $ch_fld_ui_type = $chart_column_str = "";
            if (isset($this->g_flds[0]) && !empty($this->g_flds[0])) {
                $ch_fld_as = $this->g_flds[0];
                if (isset($this->columns_array["uitype_$ch_fld_as"]) && !empty($this->columns_array["uitype_$ch_fld_as"])) {
                    $ch_fld_ui_type = $this->columns_array["uitype_$ch_fld_as"];
                    if (isset($this->columns_array[$ch_fld_as]) && !empty($this->columns_array[$ch_fld_as])) {
                        $chart_column_str = $this->columns_array[$ch_fld_as];
                    }
                }
            }

            require_once('modules/ITS4YouReports/gcharts.php');
            $gcharts = new Gcharts();

            $gcharts->chart_type = $charttype;
            $gcharts->report_filename = $this->report_filename;
            $gcharts->export_pdf_format = $export_pdf_format;
            $gcharts->chart_column_uitype = $ch_fld_ui_type;
            $gcharts->chart_column_str = $chart_column_str;
            $gcharts->is_currency = $currency_id;

            if (isset($this->g_chart_types[$charttype])) {
                $graphic_type = $this->g_chart_types[$charttype];
            } else {
                $graphic_type = $this->g_chart_types["horizontal"];
            }
            if ($graphic_type == "PieChart") {
                $chart_width = "600";
            } else {
                $chart_width = "100%";
            }
            $this->ch_image_name = $filename;
            $gcharts->load(array('graphic_type' => $graphic_type, 'ch_image_name' => $filename));

            $set_options = array();
            $set_options["width"] = $chart_width;
            $chart_height = "800";

            if ($is_stacked) {
                $set_options["isStacked"] = "true";
            }

            $set_options["title"] = $charttitle;
            $set_options["vAxis"] = array('title' => "$xaxis_title",);
            $gch_height = "55%";
            $gch_width = "60%";
            if ($graphic_type == "PieChart") {
                $chart_height = "500";
                $gch_height = "85%";
                $gch_width = "100%";
                $set_options["legend"] = array("position" => "right",
                    "textStyle" => "{fontSize:5}");
                if ($charttype == "pie3d") {
                    $set_options["is3D"] = "true";
                }
            }

            if ($charttype == "funnel") {
                $set_options["bar"] = array("groupWidth" => "100%");
            } else {
                $set_options["bar"] = array("groupWidth" => "90%");
            }
            $set_options["titleTextStyle"] = array("fontSize" => "25",
                "fontWidth" => "bold",
                "margin" => "auto");
            $set_options["height"] = $chart_height;

            $hAxis_arr["textPosition"] = "out";
            $hAxis_arr["slantedText"] = "false";
            $hAxis_arr["maxAlternation"] = "255";
            $hAxis_arr["maxTextLines"] = "255";

            if ($charttype == "funnel") {
                $hAxis_arr["textPosition"] = "none";
            }
            $hAxis_arr["title"] = "$yaxis_title";

            $set_options["hAxis"] = $hAxis_arr;
            $set_options["chartArea"] = array('right' => "20%",
                'width' => "$gch_width",
                'height' => "$gch_height",
            );
            $gcharts->set_options($set_options);
            $report_html .= "<div id='break_row' class='no-print'></br></div>";
            $report_html .= "<div id='chart_div' class='no-print' style='width:100%;text-align:center;'>
                    <table class='rpt4youTableGraph' style='padding:0px;margin:auto;width:80%;height:auto;text-align:center;' cellpadding='5' cellspacing='0' align='center'>
                        <tr>
                            <td class='rpt4youGrpHead' nowrap='' align='center' style='text-align:center;border:0px;verical-align:top;'>";
            $report_html .= $gcharts->generate($dataseries);
            $report_html .= "</td>
                        </tr>
                    </table></div>";
        }
//ITS4YouReports::sshow($dataseries);
//return $dataseries;
        return $report_html;
    }

    // ITS4YOU-CR SlOl | 24.2.2015 15:32
    private function setHChArrayValues($type="hch_dataseries", $group_value, $fld_value,$currency_id=""){
        if($currency_id!=""){
            $this->ch_array["hch_dataseries"][$group_value][$currency_id][] = $fld_value;
        }else{
            $this->ch_array["hch_dataseries"][$group_value][] = $fld_value;
        }
        return true;
    }

    private function getReportHighCharts($export_pdf_format, $currency_id){
        $default_charset = vglobal('default_charset');
        $report_html = "";
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        global $current_user;

//ITS4YouReports::sshow($this->charts);
//ITS4YouReports::sshow($this->ch_array);

        $title_color = "#2b2b2b";
        $title_size = "25px";
        if('ShowWidget' === $_REQUEST['view']) {
            $axis_font_size = "8px";
        } else {
            $axis_font_size = "15px";
        }

        // ITS4YOU-UP SlOl 2. 3. 2016 7:27:16 - marginRight: 150
        $charts_events = " ,
                marginBottom: 70";

        if($this->charts["charttypes"][$this->charts['charts_ds_columns'][0]]=="bar"){
            // reversed: true, // STACKED ONLY
            // series: { stacking: 'normal', } // STACKED ONLY
            $legend_str = "{
                            reversed: true
                        }";
        }else{
            $legendAlign = 'right';
            $legend_str = "{
                            layout: 'vertical',
                            align: '$legendAlign',
                            verticalAlign: 'top',
                            y: 30,
                            navigation: {
                                enabled: false
                            },
                            adjustChartSize: true,
                        }";
        }

        $filename = "R4YouCharts";
        if (isset($current_user) && $current_user->id != "") {
            $filename .= "_" . $current_user->id;
        }
        $reportid = $this->report_obj->record;
        $filename .= "_" . $reportid;

        if (isset($this->ch_array) && $this->ch_array["charttype"] != "none" && !empty($this->ch_array["dataseries"])) {
            // ITS4YOU-UP SlOl 2. 3. 2016 9:13:48
            $charttitle = "";
            if($this->outputformat!="CHARTS"){
                $charttitle = html_entity_decode($this->charts["charttitle"], ENT_QUOTES, $default_charset);
            }
            // ITS4YOU-END
            $charttitle = addslashes($charttitle);

            //$charttype_base = $this->g_chart_types[$this->ch_array["charttype"]];
            $charttype_base = $this->charts["charttypes"][$this->charts['charts_ds_columns'][0]];
            $charttype = ($this->g_chart_types[$charttype_base]!=""?$this->g_chart_types[$charttype_base]:"column");
            if ($charttype == "funnel") {
                $chart_lbl_key = $this->ch_array["dataseries_label"]["val"];
                $chart_lbl_val = $this->ch_array["dataseries_label"]["key"];
            } else {
                $chart_lbl_key = $this->ch_array["dataseries_label"]["key"];
                $chart_lbl_val = $this->ch_array["dataseries_label"]["val"];
            }
            if ($charttype == "horizontal") {
                $yaxis_title = html_entity_decode($chart_lbl_val, ENT_QUOTES, $default_charset);
                $xaxis_title = html_entity_decode($chart_lbl_key, ENT_QUOTES, $default_charset);
            } else {
                $xaxis_title = html_entity_decode($chart_lbl_key, ENT_QUOTES, $default_charset);
                $yaxis_title = html_entity_decode($chart_lbl_val, ENT_QUOTES, $default_charset);
            }
            $categories = $k_dataseries = array();
            // NEW S

            $ch_data = "";
            $chart_labels = array();
            //ini_set("display_errors",1);error_reporting(63);
            //ITS4YouReports::sshow("C ".$currency_id);
            if(isset($this->ch_array["dataseries"]) && !empty($this->ch_array["dataseries"])){
                $dataseries_arr = array();
                $categories = $dataseries = array();
                foreach ($this->ch_array["dataseries"] as $ch_key => $ch_array) {
                    if($ch_key!="subval"){

                        $cl_fldname_arr = explode("_", $ch_key);
                        $ch_fldname_lk = (count($cl_fldname_arr) - 1);
                        $ch_calculation_type = "";
                        if (in_array($cl_fldname_arr[$ch_fldname_lk], $this->calculation_type_array)) {
                            $ch_calculation_type = strtoupper($cl_fldname_arr[$ch_fldname_lk]);
                            unset($cl_fldname_arr[$ch_fldname_lk]);
                        }
                        $cl_fldname = implode("_", $cl_fldname_arr);
                        $ch_columns_array_lbl = $this->columns_array[$cl_fldname];
                        if ($ch_calculation_type != "") {
                            $ch_columns_array_lbl .= ":$ch_calculation_type";
                        }
                        $ch_label = $this->getHeaderLabel($this->report_obj->record, "SM", $ch_key, $ch_columns_array_lbl);
                        $chart_labels[$ch_key] = $ch_label;
                        // type of chart
                        $ch_key_type = $this->charts["charttypes"][$ch_key];

                        $fieldModule = $groupFldAlias = "";
                        /*
                        if($this->charts["x_group"]=="group1"){
                            $groupFldAlias = $this->columns_array[$this->report_obj->reportinformations["Group1"]]["fld_alias"];
}elseif($this->charts["x_group"]=="group2"){
                            $groupFldAlias = $this->columns_array[$this->report_obj->reportinformations["Group2"]]["fld_alias"];
                        }
                        */
                        $groupFldAlias = $this->columns_array[$this->report_obj->reportinformations["Group1"]]["fld_alias"];

                        if($groupFldAlias != "" && $this->columns_array["uitype_$groupFldAlias"]=="15"){
                            if(isset($this->columns_array[$groupFldAlias]) && $this->columns_array[$groupFldAlias]!=""){
                                list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $this->columns_array[$groupFldAlias]);
                                $module_field_arr = explode("_", $module_field);
                                $fieldModule = $module_field_arr[0];
                            }
                        }

                        $currency_str = "";
                        if($this->charts["x_group"]=="group1"){
                            foreach($ch_array as $ch_category => $ch_value){
                                if($fieldModule!=""){
                                    if($currency_id!=""){
                                        $ch_category_arr = explode(" ", $ch_category);
                                        $currency_str = " ".$ch_category_arr[(count($ch_category_arr)-1)];
                                        unset($ch_category_arr[(count($ch_category_arr)-1)]);
                                        $ch_category = implode(" ", $ch_category_arr);
                                    }
                                    $ch_category = vtranslate($ch_category,$fieldModule).$currency_str;
                                }
                                if($ch_value==""){
                                    $ch_value = 0;
                                }
                                if(!in_array($ch_category, $categories)){
                                    $categories[] = html_entity_decode($ch_category, ENT_QUOTES, $default_charset);
                                }
                                $series_types[$ch_label] = $ch_key_type;
                                if($ch_key_type=="pie" || $ch_key_type=="funnel"){
                                    $dataseries[$ch_label][$ch_category] = $ch_value;
                                }else{
                                    $dataseries[$ch_label][] = $ch_value;
                                }

                                /*if($ch_key_type=="pie" || $ch_key_type=="funnel"){
                                    $dataseries[] = array($ch_category,$ch_value);
                                }else{
                                    $dataseries[$ch_label][] = $ch_value;
                                }*/
                            }
                        }elseif($this->charts["x_group"]=="group2"){
                            $ch_s_name_array = array();
                            foreach($ch_array as $ch_g1 => $ch_g1_array){
                                if($fieldModule!=""){
                                    $ch_g1 = getTranslatedString($ch_g1,$fieldModule);
                                }
                                if(!in_array($ch_g1, $categories)){
                                    $categories[] = html_entity_decode($ch_g1, ENT_QUOTES, $default_charset);
                                }
                                foreach($ch_g1_array as $ch_s_name => $ch_value){
                                    $series_types[$ch_label] = $ch_key_type;
                                    if(!in_array($ch_s_name,$ch_s_name_array)){
                                        $ch_s_name_array[] = $ch_s_name;
                                    }
                                    $dataseries[$ch_label][$ch_g1][$ch_s_name] = $ch_value;
                                }
                            }

                        }
                    }
                }
            }

            // funnel chart check and ordering start
            foreach($this->charts["charttypes"] as $chart_check_column_str => $chart_check_type){
                if($chart_check_type == "funnel"){
                    $s_uitypes = ITS4YouReports::$s_uitypes;

                    if($this->charts["x_group"]=="group2"){
                        $group_col_str = $this->report_obj->reportinformations["Group2"];
                        foreach($dataseries[$chart_labels[$chart_check_column_str]] as $ds_options_array){
                            foreach($ds_options_array as $ds_option_key => $ds_option_value){
                                $chart_funnel_series[$ds_option_key] += $ds_option_value;
                            }
                        }
                    }else{
                        $group_col_str = $this->report_obj->reportinformations["Group1"];
                        $chart_funnel_series = $dataseries[$chart_labels[$chart_check_column_str]];
                    }

                    $group_col_alias = $this->columns_array[$group_col_str]["fld_alias"];
                    $group_col_uitype = $this->columns_array["uitype_".$group_col_alias];

                    if($group_col_uitype!="" && in_array($group_col_uitype, $s_uitypes)){
                        global $current_user;
                        require_once 'modules/PickList/PickListUtils.php';
                        $roleid=$current_user->roleid;
                        $adb = PearDatabase::getInstance();
                        $column_str_arr = explode(":", $group_col_str);
                        $column_name = $column_str_arr[1];
                        // ITS4YOU-UP SlOl |26.8.2015 11:43
                        $picklist_lang = "";
                        if(vtlib_isModuleActive($this->report_obj->primarymodule)){
                            $current_user_language = $current_user->column_fields["language"];
                            $picklist_lang = return_module_language($current_user_language, $this->report_obj->primarymodule);
                        }
                        // ITS4YOU-END

                        $picklistValues = array();
                        $labels_picklistValues = getAssignedPicklistValues($column_name, $roleid, $adb,$picklist_lang);
                        if(!empty($labels_picklistValues)){
                            $ordered_picklistValues = Vtiger_Util_Helper::getPickListValues($column_name);
                            foreach($ordered_picklistValues as $valueName){
                                $picklistValues[$valueName] = $labels_picklistValues[$valueName];
                            }
                        }

                        if(!empty($picklistValues)){
                            $chart_funnel_series = $this->sortChartPickListData($chart_funnel_series, $picklistValues,$currency_id,$fieldModule);
                            $dataseries[$chart_labels[$chart_check_column_str]] = $chart_funnel_series;
                        }
                    } else {
						$dataseries[$chart_labels[$chart_check_column_str]] = $chart_funnel_series;
					}
                }
            }
            // funnel chart check and ordering end

            // NEW E
        }
        $series_string_arr = array();

        // population hidden input values for export to pdf functions / export_pdf_format pdf_file_name ch_image_name /
        if($request->has('module')==true && $request->get('module')=='ITS4YouReports' && ($request->has('mode')!=true || $request->get('mode')!='widget')){
            echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('export_pdf_format')){document.getElementById('export_pdf_format').value='" . $export_pdf_format . "';}});</script>";
            echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('pdf_file_name')){document.getElementById('pdf_file_name').value='" . $this->pdf_filename . "';}});</script>";
            echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('ch_image_name')){document.getElementById('ch_image_name').value='" . $filename . "';}});</script>";
            echo '<canvas id="canvas" width="1000px" height="600px" style="display:none;"></canvas>';
        }

        if(!empty($categories)){
            foreach($categories as $ckey => $cval){
                $categories[$ckey] = preg_replace('/[\r\n]/', '', $cval);
            }
        }

        $allowDecimals = '';
        if(in_array('crmid_count',$this->charts['charts_ds_columns'])){
            $allowDecimals = 'allowDecimals: false,';
        }
        // CHART STYLE 1
        if($this->charts["x_group"]=="group1"){
            foreach($dataseries as $ds_name => $ds_data){
                // $series_string_arr[$ds_name] = $this->getHighChartDataSeries(array($ds_name=>$ds_data),"");
                if($series_types[$ds_name]=="pie" || $series_types[$ds_name]=="funnel"){
                    foreach($ds_data as $slice_name => $slice_value){
                        $slices_str .= "['$slice_name',$slice_value],";
                    }
                    $series_string_arr[$ds_name] = trim($slices_str,",");
                    //$series_string_arr[$ds_name] = implode(",", $ds_data);
                }else{
                    $series_string_arr[$ds_name] = implode(",", $ds_data);
                }
            }
            // quick fix of - values !!!
            $series_string_arr = str_replace("-", "0", $series_string_arr);

            $y_axis_str = $y_series_str= array();
            $ch_ni = 0;
            // ITS4YOU-CR SlOl 24. 11. 2017 6:29:50
            $xAxisFormater = '';
            if('ShowWidget' === $_REQUEST['view']) {
                $xAxisFormater = "      formatter: function () {
                                            var label = this.axis.defaultLabelFormatter.call(this);
                                            if (label.length>4) {
                                                return Highcharts.numberFormat((this.value/1000), 0)+'k';
                                            }
                                            return label;
                                        },
                                    ";
            }
            // ITS4YOU-END 24. 11. 2017 6:30:02
            $logarithmic = false;

            foreach($series_string_arr as $lbl_str => $data_str){
                // format: '{value} $lbl_str', kde lbl_str je text pri zobrazenej hodnote ... napr. Merna Jednotka, Mena, atd.
                $hch_type = $this->g_chart_types[$series_types[$lbl_str]];
                $lbl_str = html_entity_decode($lbl_str, ENT_QUOTES, $default_charset);
                $lbl_str = addslashes($lbl_str);

                if($hch_type=="funnel"){
                    $charts_events = " ,
                        marginBottom: 50,
                        marginRight: 150";
                }

                $range = "yAxis: $ch_ni,";
//                if (in_array('linechart_logarithmic', $series_types)) {
//                    if (max(explode(',', $data_str)) < 100) {
//                        $y_axis_str[] = "{
//					    	type: 'logarithmic',
//						},";
//                        $logaritmic = true;
//                    } else {
//                        if (!$logaritmic) {
//                            $range = "yAxis: $ch_ni,";
//                        }
//                    }
//                }

                $y_axis_str[] =      "{
                                        labels: {
                                            format: '{value}',
                                            autoRotation: [0],
                                            $xAxisFormater
                                            style: {
                                                color: '$title_color'
                                            }
                                        },
                                        $allowDecimals
                                        title: {
                                            style: {
                                                color: '$title_color',
                                                fontSize: '$axis_font_size'
                                            },
                                            text: '".$lbl_str."'
                                        },
                                        opposite: false
                                    },";
                // valueSuffix je text pri zobrazenej hodnote ... napr. Merna Jednotka, Mena, atd.
                $y_series_str[] = "{
                                        name: '".$lbl_str."',
                                        type: '$hch_type',
                                        $range
                                        data: [$data_str],
                                        tooltip: {
                                            valueSuffix: ''
                                        }

                                    },";
                $ch_ni++;
            }

// ITS4YOU-UP SlOl 2. 3. 2016 7:27:16 - plotOptions
// ITS4YOU-UP SlOl 2. 3. 2016 7:27:16 - title position
            $highchart_jsdata = "$(function () {
                                    $('#reports4you_widget_".$this->chartPosition.$this->report_obj->record."').highcharts({
                                            credits: {
                                                enabled: false
                                            },
                                            chart: {
                                                zoomType: 'xy' $charts_events
                                            },
                                            plotOptions: {
                                                series: {
                                                    dataLabels: {
                                                        enabled: true,
                                                        format: '<b>{point.name}</b> ({point.y:,.0f})',
                                                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                                                        softConnector: true
                                                    },
                                                    neckWidth: '30%',
                                                    neckHeight: '25%'
                                                }
                                            },
                                            $allowDecimals
                                            title: {
                                                style: {
                                                    color: '$title_color',
                                                    fontSize: '$title_size',
                                                    fontWeight: 'bold'
                                                },
                                                align: 'left',
                                                x: 145,
                                                text: ".$this->getHighChartDataString($charttitle)."
                                            },
                                            subtitle: {
                                                text: ''
                                            },
                                            xAxis: [{
                                                categories: ".$this->getHighChartDataArrayString($categories).",
                                                crosshair: true
                                            }],
                                            yAxis: [".trim(join("", $y_axis_str),",")."],
                                            tooltip: {
                                                shared: true
                                            },
                                            legend: $legend_str,
                                            series: [".trim(join("", $y_series_str),",")."]
                                        });
                                    });";
        // CHART STYLE 2
        }elseif($this->charts["x_group"]=="group2"){
            $slices_str_arr = array();
            if($this->charts["charttypes"][$this->charts['charts_ds_columns'][0]] == "funnel"){
                $categories = array();
                $dataseries_str = "";
                foreach($dataseries as $columnNameLbl => $data_base_arr){
                    foreach($data_base_arr as $category_name=>$dava_value){
                        $category_name = vtranslate($category_name,$this->report_obj->primarymodule);
                        $category_name = addslashes($category_name);
                        $categories[] = $category_name;
                        $dataseries_str .= "['$category_name',$dava_value],";
                    }
                }
                $dataseries_str = trim($dataseries_str, ",");
                // quick fix of - values !!!
                $dataseries_str = str_replace("-", "0", $dataseries_str);

                $charts_events = " ,
                    marginBottom: 50,
                    marginRight: 150";

// ITS4YOU-UP SlOl 2. 3. 2016 7:27:16 - plotOptions
// ITS4YOU-UP SlOl 2. 3. 2016 7:27:16 - title position
                $highchart_jsdata = "$(function () {
                                    $('#reports4you_widget_".$this->chartPosition.$this->report_obj->record."').highcharts({
                                            credits: {
                                                enabled: false
                                            },
                                            chart: {
                                                zoomType: 'xy',
                                                 spacingRight: 50 $charts_events
                                            },
                                            legend: $legend_str,
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b> ({point.y:,.0f})',
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                    softConnector: true
                },
                neckWidth: '25%',
                neckHeight: '25%'
            }
        },
                                            title: {
                                                style: {
                                                    color: '$title_color',
                                                    fontSize: '$title_size',
                                                    fontWeight: 'bold'
                                                },
    align: 'left',
    x: 145,
                                                text: ".$this->getHighChartDataString($charttitle)."
                                            },
                                            subtitle: {
                                                text: ''
                                            },
                                            xAxis: [{
                                                categories: ['".implode("','", $categories)."'],
                                                crosshair: true
                                            }],
                                            yAxis: [{
                                                labels: {
                                                    format: '{value}',
                                                    style: {
                                                        color: '$title_color'
                                                    }
                                                },
                                                title: {
                                                    style: {
                                                        color: '$title_color',
                                                        fontSize: '$title_size',
                                                        fontWeight: 'bold'
                                                    },
                                                    text: ''
                                                },
                                                opposite: true
                                            }],
                                            tooltip: {
                                                shared: true
                                            },
                                            series: [{
                                                name: '$columnNameLbl',
                                                type: 'funnel',
                                                yAxis: 0,
                                                data: [$dataseries_str],
                                                tooltip: {
                                                    valueSuffix: ''
                                                }

                                            }]
                                        });
                                    });";

            }elseif($this->charts["charttypes"][$this->charts[charts_ds_columns][0]] == "pie"){
                foreach($dataseries as $column_alias => $category_data_array){
                    foreach($category_data_array as $category_name => $category_data){
                        $data_totals_total[] = array_sum($category_data);
                        $data_totals[$category_name] = $category_data;

                    }

                }
                $c_data_str = "";
                $data_totals_total_p = (array_sum($data_totals_total));
                $ci = 0;
                foreach($data_totals as $cname => $c_data){
                    $category_total_perc = ((array_sum($c_data)/$data_totals_total_p)*100);
                    $category_total_perc = number_format($category_total_perc, 2, ".", "");
                    $c_sub_categories = $c_sub_data_str = array();
                    foreach($c_data as $c_sub_name => $c_sub_data){
                        $c_sub_name = vtranslate($c_sub_name,$this->report_obj->primarymodule);
                        $c_sub_categories[] = $c_sub_name;
                        $c_sub_data_str[] = number_format((($c_sub_data/$data_totals_total_p)*100), 2, ".", "");
                    }
                    $c_data_str .= "{
                                    y: $category_total_perc,
                                    color: colors[$ci],
                                    drilldown: {
                                        name: '$cname',
                                        categories: ['".implode("','", $c_sub_categories)."'],
                                        data: [".implode(",", $c_sub_data_str)."],
                                        color: colors[$ci]
                                    }
                                    },";
                    $ci++;
                }
                $c_data_str = trim($c_data_str, ',');

                $group_info_fld_str0 = $this->columns_array[$this->g_flds[0]];
                $group_info_headerLabel0 = $this->getHeaderLabel($this->report_obj->record, "SC", $this->g_flds[0], $group_info_fld_str0);

                $group_info_fld_str1 = $this->columns_array[$this->g_flds[1]];
                $group_info_headerLabel1 = $this->getHeaderLabel($this->report_obj->record, "SC", $this->g_flds[1], $group_info_fld_str1);

                        $highchart_jsdata = "$(function () {
                var colors = Highcharts.getOptions().colors,
                    categories = ['".  implode("','", $categories)."'],
                    data = [$c_data_str],
                    browserData = [],
                    versionsData = [],
                    i,
                    j,
                    dataLen = data.length,
                    drillDataLen,
                    brightness;


                // Build the data arrays
                for (i = 0; i < dataLen; i += 1) {

                    // add browser data
                    browserData.push({
                        name: categories[i],
                        y: data[i].y,
                        color: data[i].color
                    });

                    // add version data
                    drillDataLen = data[i].drilldown.data.length;
                    for (j = 0; j < drillDataLen; j += 1) {
                        brightness = 0.2 - (j / drillDataLen) / 5;
                        versionsData.push({
                            name: data[i].drilldown.categories[j],
                            y: data[i].drilldown.data[j],
                            color: Highcharts.Color(data[i].color).brighten(brightness).get()
                        });
                    }
                }
                    $('#reports4you_widget_".$this->chartPosition.$this->report_obj->record."').highcharts({
                                        credits: {
                                            enabled: false
                                        },
                                        chart: {
                                            type: 'pie' $charts_events
                                        },
                                        legend: $legend_str,
                                        title: {
                                            style: {
                                                color: '$title_color',
                                                fontSize: '$title_size',
                                                fontWeight: 'bold'
                                            },
                                            text: ".$this->getHighChartDataString($charttitle)."
                                        },
                                        yAxis: {
                                            title: {
                                                text: ''
                                            }
                                        },
                                        plotOptions: {
                                            pie: {
                                                shadow: false,
                                                center: ['50%', '50%']
                                            }
                                        },
                                        tooltip: {
                                            valueSuffix: '%'
                                        },
                                        series: [{
                                            name: '$group_info_headerLabel0',
                                            data: browserData,
                                            size: '60%',
                                            dataLabels: {
                                                formatter: function () {
                                                    return this.y > 5 ? this.point.name : null;
                                                },
                                                color: 'white',
                                                distance: -30
                                            }
                                        }, {
                                            name: '$group_info_headerLabel1',
                                            data: versionsData,
                                            size: '80%',
                                            innerSize: '60%',
                                            dataLabels: {
                                                formatter: function () {
                                                    // display only if larger than 1
                                                    return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y + '%'  : null;
                                                }
                                            }
                                        }]
                                    });
                                });";
            }else{
                foreach($dataseries as $ch_column => $ds_data){
                    $hch_type = $this->g_chart_types[$series_types[$ch_column]];
                        foreach($ds_data as $slice_name => $slice_array){
                            $slices_str = "";
                            foreach($ch_s_name_array as $t_slice_name){
                                if(isset($slice_array[$t_slice_name])){
                                    $slices_str_arr[$t_slice_name] .= $slice_array[$t_slice_name].",";
                                }else{
                                    $slices_str_arr[$t_slice_name] .= "0,";
                                }
                            }
                        }
                }
                // ITS4YOU-CR SlOl 22. 8. 2016 13:24:35 -> sort by group 2 ordering
                // ordering is reversed in code for a reason of adding values into chart by first in array is last (top) in chart
                $percentage_column = false;
                $column_uitype = "";
                $group_info_fld_str1 = $this->g_flds[1];
                if(isset($this->columns_array["uitype_".$group_info_fld_str1])){
                    $column_uitype = $this->columns_array["uitype_".$group_info_fld_str1];
                }
                // STREAMVIBE FIX - PERCENTAGE !!! START
                if($column_uitype == "9"){
                    $sv_temp = $sv_temp_final = $sv_temp_keys = array();
                    foreach($slices_str_arr as $sv_key => $sv_val){
                        $sv_key_nr = substr($sv_key, 0, -1);
                        $sv_temp_keys[$sv_key_nr] = $sv_key;
                        $sv_temp[$sv_key_nr] = $sv_val;
                    }
                    if($this->report_obj->reportinformations["Sort2"]=="Ascending"){
                        krsort($sv_temp);
                    }else{
                        ksort($sv_temp);
                    }
                    foreach($sv_temp as $k_nr => $k_arr){
                        $k_f_key = $sv_temp_keys[$k_nr];
                        $sv_temp_final[$k_f_key] = $k_arr;
                    }
                    $slices_str_arr = $sv_temp_final;
                // STREAMVIBE FIX - PERCENTAGE !!! END
                }else{
                    if($this->report_obj->reportinformations["Sort2"]=="Ascending"){
                        krsort($slices_str_arr);
                    }else{
                        ksort($slices_str_arr);
                    }
                }
                // ITS4YOU-END 22. 8. 2016 13:30:35
                $slices_array = array();
                foreach($slices_str_arr as $sl_name => $sl_pieces){
                    // quick fix of - values !!!
                    $sl_pieces = str_replace("-", "0", $sl_pieces);
                    // quick fix of translations !!!
                    $sl_name = vtranslate($sl_name,$this->report_obj->primarymodule);
                    $slices_array[] = "{name: '$sl_name', data: [$sl_pieces]},";
                }

                // format: '{value} $lbl_str', kde lbl_str je text pri zobrazenej hodnote ... napr. Merna Jednotka, Mena, atd.
                $y_axis_str[] =      "{
                                        min: 0,
                                        $allowDecimals                                        
                                        title: {
                                            style: {
                                                color: '$title_color',
                                                fontSize: '$axis_font_size'
                                            },
                                            text: '".$ch_column."'
                                        },
                                        stackLabels: {
                                            enabled: true,
                                            style: {
                                                fontWeight: 'bold',
                                                color: 'gray'
                                            }
                                        }
                                    },";
                $y_series_str = $slices_array;

                // VERTICAL STACKED | HORIZONTAL STACKED CHARTS !|!
                $stacking_str = $format_series = "";
                if($charttype_base=="verticalstacked" || $charttype_base=="horizontalstacked"){
                    $stacking_str .= "stacking: 'normal',";
                    /*
                    $format_series .= "format: '{series.name}',";
                    /* */
                    $format_series .= "formatter: function() {
                                            if (this.y === 0) {
                                                return null;
                                            } else {
                                                return this.series.name;
                                            }
                                        },";

                }
                if($charttype_base=="horizontalstacked"){
                    $plotOptions = "series: {
                                        stacking: 'normal'
                                    },
                                    bar: {
                                        dataLabels: {
                                            enabled: true,
                                            $format_series
                                            color: 'white',
                                            style: {
                                                textShadow: '0 0 3px black'
                                            }
                                        }
                                    }";
                }else{
                    $plotOptions = "column: {
                                        $stacking_str
                                        dataLabels: {
                                            enabled: true,
                                            $format_series
                                            color: 'white',
                                            style: {
                                                textShadow: '0 0 3px black'
                                            }
                                        }
                                    }";
                }

                $highchart_jsdata = "$(function () {
                            $('#reports4you_widget_".$this->chartPosition.$this->report_obj->record."').highcharts({
                                credits: {
                                    enabled: false
                                },
                                chart: {
                                    type: '$hch_type' $charts_events
                                },
                                legend: $legend_str,
                                title: {
                                    style: {
                                        color: '$title_color',
                                        fontSize: '$title_size',
                                        fontWeight: 'bold'
                                    },
                                    text: ".$this->getHighChartDataString($charttitle)."

                                },
                                subtitle: {
                                    text: ''
                                },
                                xAxis: [{
                                    categories: ".$this->getHighChartDataArrayString($categories).",
                                    crosshair: true
                                }],
                                yAxis: [".trim(join("", $y_axis_str),",")."],
                                tooltip: {
                                    formatter: function () {
                                        return '<b>' + this.x + '</b><br/>' +
                                            this.series.name + ': ' + this.y + '<br/>' +
                                        '".vtranslate("LBL_TOTAL", "ITS4YouReports"). vtranslate("LBL_DOUBLEDOT", "ITS4YouReports")." ' + this.point.stackTotal;
                                    }
                                },
                                plotOptions: {
                                    $plotOptions
                                },
                                series: [".trim(join("", $y_series_str),",")."]
                            });
                        });";
            }
        }
        // CHART STYLES END

// CHART DEBUG
/*global $current_user;if($current_user->id=="1"){
    echo "<textarea style='height:1200px;'>$highchart_jsdata</textarea>";
}*/
        if('v7'!==$layout && !$request->has('mode') || 'showDetailViewByMode' === $request->get('mode')) {
			$report_html .= '<script type="text/javascript">';
		}
		$report_html .= $highchart_jsdata;
		if('v7'!==$layout && !$request->has('mode') || 'showDetailViewByMode' === $request->get('mode')) {
			$report_html .= '</script>';
		}
/*
          $report_html .= '<script type="text/javascript">
$("#reports4you_widget_'.$this->report_obj->record.'").load(function() {
alert("loaded";)
                    var chart = $("#reports4you_widget_'.$this->report_obj->record.'").highcharts(),
                    svg = chart.getSVG()
                    .replace(/</g, "\n&lt;")
                    .replace(/>/g, "&gt;");
                    alert(svg);
                });
                </script>';
*/

        return $report_html;
    }
    // ITS4YOU-CR SlOl 25.2.2015 14:00
    private function getHighChartDataString($data_string=""){
        $data_string = "'$data_string'";
        return $data_string;
    }
    private function getHighChartDataArrayString($data_array){
        $data_string = "[";
        if(!empty($data_array)){
            foreach ($data_array as $element){
                $element = addslashes($element);
                if (is_numeric($element)){
                    $new_array[] = $element;
                }else{
                    $new_array[] = "'$element'";
                }
            }
            $data_string .= implode(',', $new_array);
        }
        $data_string .= "]";
        //ITS4YouReports::sshow($data_string);
        return $data_string;
    }
    // ITS4YOU-CR SlOl 26.2.2015 11:08
    private function getHighChartDataSeries($dataseries=array(),$charttype="column"){
        $series_string = "["."\n";
        if(!empty($dataseries)){
            $count = count($dataseries);
            $i = 1;
            if($charttype=="pie" || $charttype=="funnel"){
                $series_string .= "{";
                if($charttype=="pie"){
                    $series_string .= "type: '$charttype',";
                }
                $series_string .= "
                name: '',
                data: [";
                foreach($dataseries as $data){
                    $series_string .= "["."\n";
                    $d_count = count($data);
                    $d_i = 1;
                    foreach($data as $d_val){
                        if(is_numeric($d_val)){
                            $series_string .= $d_val."\n";
                        }else{
                            $series_string .= "'$d_val'"."\n";
                        }
                        if($d_i<$d_count){
                            $series_string .= ",";
                        }
                        $d_i++;
                    }
                    $series_string .= "]"."\n";
                    if($i<$count){
                        $series_string .= ",";
                    }
                    $i++;
                }
                $series_string .= "]}";
            }else{
                foreach($dataseries as $name => $data){
                    $series_string .= "{"."\n"."name: '$name',\n";
                    $data_str = $this->getHighChartDataArrayString($data);

                    $series_string .= "data: $data_str"."\n"."}"."\n";
                    if($i<$count){
                        $series_string .= ",";
                    }
                    $i++;
                }
            }
        }
        $series_string .= "]"."\n";

        return $series_string;
    }
    // ITS4YOU-END 24.2.2015 15:32

    // ITS4YOU-END 8.7.2014 8:57
    // ITS4YOU-CR SlOl | 15.7.2014 13:47
    private function setDataseriesLabel($fldname, $type = "") {
        if ($type == "subval") {
            $this->setChArrayValues("dataseries_label", $type, $fldname);
        } else {
            $cl_fldname_arr = explode("_", $fldname);
            $ch_fldname_lk = (count($cl_fldname_arr) - 1);
            $ch_calculation_type = "";
            if (in_array($cl_fldname_arr[$ch_fldname_lk], $this->calculation_type_array)) {
                $ch_calculation_type = strtoupper($cl_fldname_arr[$ch_fldname_lk]);
                unset($cl_fldname_arr[$ch_fldname_lk]);
            }
            $cl_fldname = implode("_", $cl_fldname_arr);
            $ch_columns_array_lbl = $this->columns_array[$cl_fldname];
            if ($ch_calculation_type != "") {
                $ch_columns_array_lbl .= ":$ch_calculation_type";
            }
            if (empty($this->charts["dataseries_label"]) || !isset($this->charts["dataseries_label"]["key"]) || $this->charts["dataseries_label"]["key"] == 0) {
                $ch_headerLabel = $this->getHeaderLabel($this->report_obj->record, "SM", $cl_fldname, $ch_columns_array_lbl);
                if ($type != "") {
                    $dataseries_label_key = $this->getHeaderLabel($this->report_obj->record, "SC", $fldname, $this->columns_array[$type]);
                } else {
                    $dataseries_label_key = $this->getHeaderLabel($this->report_obj->record, "SC", $this->g_flds[0], $this->columns_array[$this->g_flds[0]]);
                }
                if ($this->report_obj->reportinformations["timeline_type2"] == "cols" || $this->report_obj->reportinformations["timeline_type3"] == "cols") {
                    $this->setChArrayValues("dataseries_label", 'key', $ch_headerLabel);
                    $clC_fldname_arr = explode("_", $this->charts["charts_ds_column"]);
                    $chC_fldname_lk = (count($clC_fldname_arr) - 1);
                    unset($clC_fldname_arr[$chC_fldname_lk]);
                    $clC_fldname = implode("_", $clC_fldname_arr);
                    $dataseriesC_label_key = $this->getHeaderLabel($this->report_obj->record, "SC", $clC_fldname, $this->columns_array[$clC_fldname]);
                    $this->setChArrayValues("dataseries_label", 'val', $dataseriesC_label_key);
                } else {
                    $this->setChArrayValues("dataseries_label", 'key', $dataseries_label_key);
                    $this->setChArrayValues("dataseries_label", 'val', $ch_headerLabel);
                }
            }
        }
        return true;
    }

    // ITS4YOU-END 15.7.2014 13:47
    // ITS4YOU-CR SlOl | 26.8.2014 11:23
    // group value = Direct Group Value to display
    // g data value = Direct Value to display
    // currency id = if is generated module with more currencies (Inventory module)
    // g data key = Axis Label to display only used 2 times, for X and Y axis
    private function setDataseriesArray($group_value, $g_data_value, $currency_id = "", $g_data_key = "", $base_group = "") {
        if ($currency_id != "") {
            $ch_group_value = $group_value . " (" . $this->currency_symbols[$currency_id] . ")";
        } else {
            $ch_group_value = $group_value;
        }
        if ($g_data_key != "" && !isset($this->ch_array["dataseries_label"])) {
            $this->setDataseriesLabel($g_data_key);
        }
        // ITS4YOU-CR SlOl 18. 11. 2015 14:09:00
        if($this->charts["x_group"]=="group2" && $base_group==""){
            $base_group = "-";
        }
        // ITS4YOU-END 18. 11. 2015 14:09:02
        $fld_alias = "";
        if(isset($this->charts["x_group"]) && $this->charts["x_group"]=="group1"){
            $fld_alias = $this->columns_array[$this->report_obj->reportinformations["Group1"]]["fld_alias"];
        }elseif(isset($this->charts["x_group"]) && $this->charts["x_group"]=="group2"){
            $fld_alias = $this->columns_array[$this->report_obj->reportinformations["Group2"]]["fld_alias"];
        }
        if(isset($fld_alias) && $fld_alias!="" && $fld_alias=="converted"){
            $ch_group_value = $this->getFldFormatedValue(1,$ch_group_value,$fld_alias);
        }
        $clearAlias = $this->getClearFldName($g_data_key);
        $uitype = (!empty($this->columns_array['uitype_'.$clearAlias])?$this->columns_array['uitype_'.$clearAlias]:1);
        if (in_array($uitype, self::$currencyUiTypes)) {
            $g_data_value = $this->getFldFormatedValue($uitype,$g_data_value,$fld_alias, '', '', true);
        }

        $this->setChArrayValues("dataseries", $ch_group_value, $g_data_value, $g_data_key, $base_group);
    }

    // ITS4YOU-END 26.8.2014 11:23
    function getAccessPickListValues() {
        $adb = PearDatabase::getInstance();
        global $current_user;
        $id = array(getTabid($this->primarymodule));
        if ($this->secondarymodule != '')
            array_push($id, getTabid($this->secondarymodule));

        $query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(' . generateQuestionMarks($id) . ') and uitype in (15,33,55)'; //and columnname in (?)';
        $result = $adb->pquery($query, $id); //,$select_column));
        $roleid = $current_user->roleid;
        $subrole = getRoleSubordinates($roleid);
        if (count($subrole) > 0) {
            $roleids = $subrole;
            array_push($roleids, $roleid);
        } else {
            $roleids = $roleid;
        }

        $temp_status = Array();
        for ($i = 0; $i < $adb->num_rows($result); $i++) {
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $tabid = $adb->query_result($result, $i, "tabid");
            $uitype = $adb->query_result($result, $i, "uitype");

            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $keyvalue = getTabModuleName($tabid) . "_" . $fieldlabel1;
            $fieldvalues = Array();
            if (count($roleids) > 1) {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode($roleids, "\",\"") . "\") and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            } else {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            }
            if ($fieldname != 'firstname')
                $mulselresult = $adb->query($mulsel);
            for ($j = 0; $j < $adb->num_rows($mulselresult); $j++) {
                $fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
                if (in_array($fldvalue, $fieldvalues))
                    continue;
                $fieldvalues[] = $fldvalue;
            }
            $field_count = count($fieldvalues);
            if ($uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
                $temp_count = count($temp_status[$keyvalue]);
                if ($temp_count > 0) {
                    for ($t = 0; $t < $field_count; $t++) {
                        $temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
                    }
                    $fieldvalues = $temp_status[$keyvalue];
                } else
                    $temp_status[$keyvalue] = $fieldvalues;
            }

            if ($uitype == 33)
                $fieldlists[1][$keyvalue] = $fieldvalues;
            else if ($uitype == 55 && $fieldname == 'salutationtype')
                $fieldlists[$keyvalue] = $fieldvalues;
            else if ($uitype == 15)
                $fieldlists[$keyvalue] = $fieldvalues;
        }
        return $fieldlists;
    }

    private function getUserValuesConditions($conditions, $columns_arr) {
        global $current_user;
        // kontrola picklist values permissions start
        $user_privileges_path = 'user_privileges/user_privileges_' . $current_user->id . '.php';
        if (file_exists($user_privileges_path)) {
            require($user_privileges_path);
        }
        if (file_exists($user_privileges_path) && ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)) {
            return $conditions;
        } else {
            if (!empty($columns_arr)) {
                foreach ($columns_arr as $key_fld => $sc_fld_array) {
                    $sc_fieldcolname = $sc_fld_array["fieldcolname"];
                    $sc_fld_as = $this->columns_array[$sc_fieldcolname]["fld_alias"];
                    $sc_fld_arr = explode("_", $sc_fld_as);
                    $sc_lk = (count($sc_fld_arr) - 1);
                    $sc_lk_lc = strtolower($sc_fld_arr[$sc_lk]);
                    if (in_array($sc_lk_lc, array("mif", "inv"))) {
                        unset($sc_fld_arr[$sc_lk]);
                        $sc_fld_name = implode("_", $sc_fld_arr);
                    } else {
                        $sc_fld_name = $sc_fld_as;
                    }
                    $sc_fld_cond = $this->columns_array[$sc_fieldcolname]["fld_cond"];
                    $sc_fld_ui_type = "";
                    if (array_key_exists($sc_fld_as, $this->columns_array) && isset($this->columns_array["uitype_$sc_fld_as"]) && $this->columns_array["uitype_$sc_fld_as"] != "") {
                        $sc_fld_ui_type = $this->columns_array["uitype_$sc_fld_as"];
                    }/* elseif (array_key_exists($sc_fld_as, $this->result_columns_array) && isset($this->result_columns_array["uitype_$sc_fld_as"]) && $this->result_columns_array["uitype_$sc_fld_as"] != "") {
                      $sc_fld_ui_type = $this->result_columns_array["uitype_$sc_fld_as"];
                      } */
                    if (in_array($sc_fld_ui_type, ITS4YouReports::$s_uitypes)) {
                        require_once 'modules/PickList/PickListUtils.php';
                        $roleid = $current_user->roleid;
                        $adb = PearDatabase::getInstance();

                        $picklistValues = getAssignedPicklistValues($sc_fld_name, $roleid, $adb);
                        // ITS4YOU-UP SlOl 5. 12. 2014 8:38:10 long generation selectbox values fix
                        // admin picklist values
                        $admin_role_row = $adb->fetchByAssoc($adb->pquery("SELECT roleid FROM `vtiger_user2role` WHERE userid=1", array()), 0);
                        $admin_roleid = "";
                        if (!empty($admin_role_row)) {
                            $admin_roleid = $admin_role_row["roleid"];
                        }
                        if ($admin_roleid == "") {
                            $admin_roleid = "H1";
                        }
                        $picklistValuesHA = getAssignedPicklistValues($sc_fld_name, $admin_roleid, $adb);
                        if (!empty($picklistValues) && ($picklistValues != $picklistValuesHA)) {
                            // ITS4YOU-END
                            $w_picklistValues = implode("','", $picklistValues);
                            $conditions[] = " $sc_fld_cond IN ('$w_picklistValues') ";
                        }
                    }
                }
            }
        }
        // kontrola picklist values permissions end
        return $conditions;
    }

    private function getGroupsHeaderLabelStr() {
        $headerLabel_arr = array();
        foreach ($this->group_cols_array as $group_alias) {
            $headerLabel_arr[] = $this->getHeaderLabel($this->report_obj->record, "SC", $group_alias, $this->columns_array[$group_alias]);
        }
        $headerLabel = implode(", ", $headerLabel_arr);
        return $headerLabel;
    }

    // ITS4YOU-CR SlOl 4. 9. 2014 15:11:10
    private function setUpGroupColsArray() {
        $this->group_column_alias = array();
        $all_g_cols_arr = array();
        for ($gi = 1; $gi < 4; $gi++) {
            $column_str = $this->report_obj->reportinformations["Group$gi"];
            if ($column_str != "none") {
                $ex_fld_alias = explode(".", $this->columns_array[$column_str]["fld_alias"]);
                $group_cols_array[$gi] = $ex_fld_alias[(count($ex_fld_alias) - 1)];
                $all_g_cols_arr[] = $ex_fld_alias[(count($ex_fld_alias) - 1)];
                $this->group_column_alias[$gi] = $all_g_cols_arr;
            }
        }
        $this->group_cols_array = $group_cols_array;
    }

    // ITS4YOU-END 4. 9. 2014 15:11:12
    // ITS4YOU-CR SlOl 9/24/2014 9:13:43 PM
    private function getToTotalsArray($use_detail_columns = false) {
        if($this->reports4you_type!="tabular"){
            return array();
        }
        $default_charset = vglobal("default_charset");
		$to_totals_array = array();
        $to_totals_array_temp = $this->report_obj->getSelectedColumnsToTotal($this->report_obj->record);
        foreach ($to_totals_array_temp as $key => $to_total_col) {
            $to_total_col_tp_arr = explode(":", $to_total_col);

            $type_col_array = explode("_", $to_total_col_tp_arr[5]);
            $typeofdata = $type_col_array[0];
            $calculation_type = $type_col_array[1];
            $calculation_no = "";
            switch ($calculation_type) {
                case "SUM":
                    $calculation_no = 2;
                    break;
                case "AVG":
                    $calculation_no = 3;
                    break;
                case "MIN":
                    $calculation_no = 4;
                    break;
                case "MAX":
                    $calculation_no = 5;
                    break;
                case "COUNT":
                    $calculation_no = 6;
                    break;
            }

            $last_key = count($to_total_col_tp_arr) - 1;
            $fieldid = "";
            if (in_array($to_total_col_tp_arr[$last_key], array("MIF", "INV")) || (is_array($to_total_col_tp_arr[$last_key]) && is_numeric(in_array($to_total_col_tp_arr[$last_key])))) {
                $fieldid = ":" . $to_total_col_tp_arr[$last_key];
            }
            $lbl_arr = explode("_", $to_total_col_tp_arr[3], 2);
            $lbl_value = getTranslatedString($lbl_arr[1], $lbl_arr[0]);
            if ($lbl_value == $lbl_arr[1]) {
                $lbl_value = str_replace("_", " ", $lbl_value);
            }

            $col_arr_key = $to_total_col_tp_arr[1] . ":" . $to_total_col_tp_arr[2] . ":" . $to_total_col_tp_arr[3] . ":" . $to_total_col_tp_arr[4] . ":" . $typeofdata . $fieldid;
			$col_arr_key = html_entity_decode($col_arr_key, ENT_QUOTES, $default_charset);

            $t_sum_lbl = $this->getHeaderLabel($this->report_obj->record, "SC", $this->columns_array[$col_arr_key]["fld_alias"], $col_arr_key);

            if ($use_detail_columns) {
                if (isset($this->detail_columns_array[$col_arr_key]["fld_alias"]) && $this->detail_columns_array[$col_arr_key]["fld_alias"] != "") {
                    $to_totals_array[$this->detail_columns_array[$col_arr_key]["fld_alias"]][] = $calculation_type;
                    $to_totals_array[$this->detail_columns_array[$col_arr_key]["fld_alias"]]["label"] = $t_sum_lbl;
                }
            } else {
                if (isset($this->columns_array[$col_arr_key]["fld_alias"]) && $this->columns_array[$col_arr_key]["fld_alias"] != "") {
                    $to_totals_array[$this->columns_array[$col_arr_key]["fld_alias"]][] = $calculation_type;
                    $to_totals_array[$this->columns_array[$col_arr_key]["fld_alias"]]["label"] = $t_sum_lbl;
                }
            }
        }
        return $to_totals_array;
    }

    private function setToTotalsArray($noofrows,$to_totals_res, $fld_totals_key, $fieldvalue, $to_totals_array, $currency_id = "") {
        if (array_key_exists($fld_totals_key, $to_totals_array)) {
            $to_totals_res[$fld_totals_key]["label"] = $to_totals_array[$fld_totals_key]["label"];
            if (isset($currency_id) && $currency_id != "") {
                $to_totals_res[$fld_totals_key]["COUNT"][$currency_id][] = 1;
            } else {
                $to_totals_res[$fld_totals_key]["COUNT"] = $noofrows;
            }

            foreach ($to_totals_array[$fld_totals_key] as $key => $method) {
                $method = strtoupper($method);
                if ('duration_sum_time' === $fld_totals_key) {
                    if ($fieldvalue) {
						$currentValue = strtotime($to_totals_res[$fld_totals_key][$method].':00');
	                    $newValue = strtotime($fieldvalue.':00');

	                	switch ($method) {
	                        case "SUM":
	                    		$to_totals_res[$fld_totals_key][$method] = date('H:i', $currentValue+$newValue);
	                            break;
	                        case "AVG":
	                    		$to_totals_res[$fld_totals_key][$method] = date('H:i', $currentValue+$newValue);
	                            break;
	                        case "MAX":
								$to_totals_res[$fld_totals_key][$method] = date('H:i', $currentValue+strtotime($fieldvalue.':00'));
								if ($newValue > $currentValue) {
									$to_totals_res[$fld_totals_key][$method] = $newValue;
								}
	                            break;
	                        case "MIN":
								$to_totals_res[$fld_totals_key][$method] = date('H:i', $currentValue+strtotime($fieldvalue.':00'));
								if ($newValue < $currentValue) {
									$to_totals_res[$fld_totals_key][$method] = $newValue;
								}
	                            break;
	                    }
                    }

				} elseif (isset($currency_id) && $currency_id != "") {
                    switch ($method) {
                        case "SUM":
                            $to_totals_res[$fld_totals_key][$method][$currency_id] += $fieldvalue;
                            break;
                        case "AVG":
                            $to_totals_res[$fld_totals_key][$method][$currency_id] += $fieldvalue;
                            break;
                        case "MAX":
                            if (!isset($to_totals_res[$fld_totals_key][$method][$currency_id]) || ($fieldvalue != "" && $fieldvalue != 0 && $fieldvalue > $to_totals_res[$fld_totals_key][$method][$currency_id])) {
                                $to_totals_res[$fld_totals_key][$method][$currency_id] = $fieldvalue;
                            }
                            break;
                        case "MIN":
                            if (!isset($to_totals_res[$fld_totals_key][$method][$currency_id]) || ($fieldvalue != "" && $fieldvalue != 0 && $fieldvalue < $to_totals_res[$fld_totals_key][$method][$currency_id])) {
                                $to_totals_res[$fld_totals_key][$method][$currency_id] = $fieldvalue;
                            }
                            break;
                    }
                } else {
                    switch ($method) {
                        case "SUM":
                            $to_totals_res[$fld_totals_key][$method] += $fieldvalue;
                            break;
                        case "AVG":
                            $to_totals_res[$fld_totals_key][$method] += $fieldvalue;
                            break;
                        case "MAX":
                            if (!isset($to_totals_res[$fld_totals_key][$method]) || ($fieldvalue != "" && $fieldvalue != 0 && $fieldvalue > $to_totals_res[$fld_totals_key][$method])) {
                                $to_totals_res[$fld_totals_key][$method] = $fieldvalue;
                            }
                            break;
                        case "MIN":
                            if (!isset($to_totals_res[$fld_totals_key][$method]) || ($fieldvalue != "" && $fieldvalue != 0 && $fieldvalue < $to_totals_res[$fld_totals_key][$method])) {
                                $to_totals_res[$fld_totals_key][$method] = $fieldvalue;
                            }
                            break;
                    }
                }
            }
        }
        return $to_totals_res;
    }

    // ITS4YOU-END 9/24/2014 9:13:50 PM
    // ITS4YOU-CR SlOl 25. 9. 2014 12:13:03
    public static function checkInstallationMemmoryLimit() {
        //  memory limit
        $memory_limit_min = 129;
        $memory_limit_recommended = 256;
        $memory_limit1 = ini_get("memory_limit");
        ini_set("memory_limit", "256M");
        $memory_limit2 = ini_get("memory_limit");
        // if original memory limit value is OK then set it to back
        if ((int) substr($memory_limit1, 0, -1) >= (int) substr($memory_limit2, 0, -1)) {
            ini_set("memory_limit", $memory_limit1);
            $memory_limit2 = '';
        }
    }

    // ITS4YOU-END 25. 9. 2014 12:13:07
    // ITS4YOU-CR SlOl 7. 10. 2014 15:35:42
    private function get_currency_sumbol_str($currency_id = "") {
        if ($currency_id != "") {
            $return = " (" . $this->currency_symbols[$currency_id] . ")";
        }
        return $return;
    }

    // ITS4YOU-END 7. 10. 2014 15:35:44
    private function isEntityType($tabid = "") {
        $isentitytype = 0;
        if ($tabid != "") {
            $adb = PearDatabase::getInstance();
            $isentitytype_sql = "SELECT isentitytype FROM vtiger_tab WHERE tabid = ?";
            $isentitytype_result = $adb->pquery($isentitytype_sql, array($this->report_obj->primarymoduleid));
            $isentitytype_row = $adb->fetchByAssoc($isentitytype_result);
            $isentitytype = $isentitytype_row["isentitytype"];
        }
        return $isentitytype;
    }

    private function getSelFieldsWhereSQL($fieldcolname,$comparator,$value,$selectedfields=array(),$add_tags=false){
        $default_charset = vglobal('default_charset');
        $advfiltergroupsql = '';

        if (isset($this->columns_array[html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset)]["fld_cond"]) && $this->columns_array[html_entity_decode($fieldcolname,
                ENT_QUOTES, $default_charset)]["fld_cond"] != "") {
            $selected_field_col = $this->columns_array[html_entity_decode($fieldcolname, ENT_QUOTES, $default_charset)]["fld_cond"];
        } elseif (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"] != "") {
            $selected_field_col = $this->columns_array[$fieldcolname]["fld_cond"];
        } else {
            $selected_field_col = $selectedfields[0] . "." . $selectedfields[1];
        }

        // ITS4YOU-CR SlOl 16. 2. 2016 6:43:30 - IT&M uitype 120 customization s
        if ('120' === $this->columns_array['uitype_' . $this->columns_array[$fieldcolname]['fld_alias']]) {
            if ('' !== $value) {
                $advfiltergroupsql .= $this->get120UserIdSQL($value);
            }
            // ITS4YOU-END  - IT&M uitype 120 customization e
        } else {
            switch ($comparator) {
                case "s":
                case "ew":
                case "c":
                case "k":
                    $nl_text = "";
                    if ($add_tags) {
                        switch ($comparator) {
                            case "ew";
                                $value = "'%$value'";
                                break;
                            case "s";
                                $value = "'$value%'";
                                break;
                            case "k";
                                $nl_text = " NOT ";
                                $value = "'%$value%'";
                                break;
                            default :
                                $value = "'%$value%'";
                                break;
                        }
                    }
                    $advfiltergroupsql = $selected_field_col . " $nl_text LIKE " . $value;
                    break;
                case "n":
                    $advfiltergroupsql = $selected_field_col . " NOT IN " . $value;
                    break;

                case "isn":
                    $advfiltergroupsql = " (" . $selected_field_col . " IS NULL OR " . $selected_field_col . " = '') ";
                    break;

                case "isnn":
                    $advfiltergroupsql = " (" . $selected_field_col . " IS NOT NULL AND " . $selected_field_col . " != '') ";
                    break;

                default:
                    $advfiltergroupsql = $selected_field_col . " IN " . $value;
                    break;
            }
        }

        return $advfiltergroupsql;
    }
    // ITS4YOU-CR SlOl 7.4.2015 15:45
    // sprting data Array Based on Picklist Options Array
    private function sortChartPickListData($dataArray, $picklistArray,$currency_id="",$fieldModule=""){
        $NewDataArray = array();

        if($currency_id!=""){
            //*** DOKONC KONTROLU AK JE CURRENCY !!!  ***/
            global $default_charset;
            foreach($picklistArray as $picklistKey => $picklistValue){
                if($fieldModule!=""){
                    $ch_s_name = getTranslatedString($picklistValue,$fieldModule);
                }

                foreach($this->currency_symbols as $currency_symbol){
                    $c_picklistValue = $picklistValue." ($currency_symbol)";
                    $decodedPicklistValue = html_entity_decode($c_picklistValue, ENT_QUOTES, $default_charset);
                    if(isset($dataArray[$decodedPicklistValue])){
                        //ITS4YouReports::sshow($decodedPicklistValue);
                        $NewDataArray[$decodedPicklistValue] = $dataArray[$decodedPicklistValue];
                    }
                }
                /*

                */
            }

        }else{
            foreach($picklistArray as $picklistKey => $picklistValue){
                if(isset($dataArray[$picklistValue])){
                    $t_picklistValue = $picklistValue;
                    if($fieldModule!=""){
                        $t_picklistValue = getTranslatedString($picklistValue,$fieldModule);
                    }
                    $NewDataArray[$t_picklistValue] = $dataArray[$picklistValue];
                }
            }
        }
        return $NewDataArray;
    }
    // ITS4YOU-END
    function getNonAdminAccessControlQuery($module, $user, $scope = '') {
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = getTabid($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . $user->id;
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
					count($sharingRuleInfo['GROUP']) > 0)) {
				$tableName = $tableName . '_t' . $tabId;
				$sharedTabId = $tabId;
			} elseif ($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t' . $tabId;
			}
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
            // for secondary module we should join the records even if record is not there(primary module without related record)
            if($scope == ''){
                $query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
                        "vtiger_crmentity$scope.smownerid ";
            }else{
                $query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
                        "vtiger_crmentity$scope.smownerid OR vtiger_crmentity$scope.smownerid IS NULL";
            }
        }
		return $query;
	}

    private function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups) {
		$module = null;
		if (!empty($tabId)) {
			$module = getTabModuleName($tabId);
		}
		$query = $this->getRepNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key) ignore " .
				$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array());
		if (is_object($result)) {
			return true;
		}
		return false;
	}

    private function getRepNonAdminAccessQuery($module, $user, $parentRole, $userGroups) {
		$query = CRMEntity::getNonAdminUserAccessQuery($user, $parentRole, $userGroups);
		if (!empty($module)) {
			$moduleAccessQuery = CRMEntity::getNonAdminModuleAccessQuery($module, $user);
			if (!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		return $query;
	}

    private function getConditionCurrentUserName($valuearray){
        $usersArray = get_user_array(false);
        global $current_user;
        if(isset($usersArray[$current_user->id])){
            $valuearray[array_search("Current User",$valuearray)] = trim($usersArray[$current_user->id]);
        }
        return $valuearray;
    }

    // ITS4YOU-CR SlOl | 20.8.2015 15:49
    private function getSqlError(){
        if(isset($_REQUEST["module"])){
            $f_error = "<font color='red'>MySQL Query FAILED: Please contact vendor of Reports4You. Coppy querie from textarea and send it to Reports4You vendor, thank you for your understanding.</font>";
            return $f_error;
        }
    }

    private function displaySqlError($adb,$f_error){
        global $default_charset;
        $error_message = "Error message:
" . $adb->database->ErrorMsg() . "
";
$sql_string = "Querie: 
" . html_entity_decode($this->tf_sql, ENT_QUOTES, $default_charset) . "
";
        $contents = "	<div style='width:100%;text-align:center;'>
                                        <table align='center' style='border:1px solid red;'>
                                                <tr><td>$f_error</td></tr>
                                                <tr><td><textarea rows='20'>$sql_string
$error_message
</textarea></td></tr>
                                        </table>
                                </div>";
        /** EMAIL FAILED QUERY TO DEV Oldo s */
            $email_body_content = "
Report: 
index.php?".$_SERVER["QUERY_STRING"]."

$error_message

$sql_string
";
        ITS4YouReports_ITS4YouError_Log::createLog($email_body_content);
        /** EMAIL FAILED QUERY TO DEV Oldo e */
        /** DISPLAY FAILED QUERY INFO TO User s */
        if(isset($_REQUEST["module"])){
            echo $contents;
            exit;
        }
        /** DISPLAY FAILED QUERY INFO TO User e */
    }
    // ITS4YOU-END
    // ITS4YOU-CR SlOl | 21.8.2015 12:18
    private function setReportFileInfo($set_pdf_portrait=false){
        global $current_user;
        $filename = self::$R4YouFilename;
        if (isset($current_user) && $current_user->id != "") {
            $filename .= "_" . $current_user->id;
        }
        $filename .= "_" . $this->report_obj->record;
        //$filename = $this->report_obj->reportname;
        $this->report_filename = $filename;

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        if('ShowWidget' !== $request->get('view') && $request->has('module')==true && $request->get('module')=='ITS4YouReports' && ($request->has('mode')!=true || $request->get('mode')!='widget')){
            echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('report_filename')){document.getElementById('report_filename').value='" . $this->report_filename . "';}});</script>";
        }

        $landscape_format = 'A4';
        $portrait_format = 'A4-L';
        $export_pdf_format = $landscape_format;
        if($this->reports4you_type=="custom_report" && $set_pdf_portrait===true){
            $export_pdf_format = $portrait_format;
        }elseif ($this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") {
            $export_pdf_format = $portrait_format;
        } elseif ($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols") {
            $export_pdf_format = $portrait_format;
        } elseif (isset($this->report_obj->reportinformations["summaries_columns"]) && count($this->report_obj->reportinformations["summaries_columns"]) > 7) {
            $export_pdf_format = $portrait_format;
        } elseif (isset($this->selectedcolumns_arr) && count($this->selectedcolumns_arr) > 10 && count($this->report_obj->reportinformations["summaries_columns"]) < 2) {
            $export_pdf_format = $portrait_format;
        }elseif(count($this->detail_selectedcolumns_arr)>$this->summaries_w_detail_portrait_from){
            $export_pdf_format = $portrait_format;
        }

        if('ShowWidget' !== $request->get('view') && $request->has('module')==true && $request->get('module')=='ITS4YouReports' && ($request->has('mode')!=true || $request->get('mode')!='widget')){
            echo "<script type='text/javascript' >jQuery(document).ready(function(){if(document.getElementById('export_pdf_format')){document.getElementById('export_pdf_format').value='" . $export_pdf_format . "';}});</script>";
        }
    }
    // ITS4YOU-END
    // ITS4YOU-CR SlOl | 21.8.2015 12:35
    private function getReportNameHTML(){
        if (isset($this->report_obj->reportinformations["reports4youid"]) && $this->report_obj->reportinformations["reports4youid"] != "") {
            $return_val = $this->report_obj->reportinformations["reports4youname"];
        } else {
            $return_val = vtranslate("LBL_REPORT_NAME", $this->getCurrentModule4You());
        }
        $return_name = "<table class='rpt4youTableText' width='100%'>";
        $return_name .= "<tr>";
            $return_name .= "<td colspan='1' class='rpt4youGrpHeadInfoText' width='100%' style='border:0px;'>";
            $return_name .= $return_val;
            $return_name .= "</td>";
        $return_name .= "</tr>";
        $return_name .= "</table>";
        return $return_name;
    }
    // ITS4YOU-END
    // ITS4YOU-CR SlOl | 21.8.2015 12:50
    private function setNoOfRows($noofrows=0){
        echo "<script type='text/javascript' id='__reportrun_directoutput_recordcount_script'>
                jQuery(document).ready(function(){
                    if(document.getElementById('countValue')) document.getElementById('countValue').innerHTML=$noofrows;});</script>";
        return true;
    }
    // ITS4YOU-END
    // ITS4YOU-CR SlOl | 2.9.2015 10:32
    public function setCurrentLanguage4You($language=""){
        if($language!=""){
            $this->currentLanguage = $language;
        }else{
            global $current_language;
            $this->currentLanguage = $language;
        }
    }
    public function getCurrentLanguage4You(){
        if(!isset($this->currentLanguage) || $this->currentLanguage==""){
            $this->setCurrentLanguage4You();
        }
        return $this->currentLanguage;
    }

    public function setCurrentModule4You(){
        $this->currentModule = "ITS4YouReports";
    }
    public function getCurrentModule4You(){
        if(!isset($this->currentModule) || $this->currentModule==""){
            $this->setCurrentModule4You();
        }
        return $this->currentModule;
    }
    // ITS4YOU-CR SlOl 3. 2. 2016 14:08:57
    private function getUI120UserID($username){
		global $adb;
        $user_id = 0;
        if($username=="Current User"){
            global $current_user;
            $user_id = $current_user->id;
        }else{
            $sql = "select id from vtiger_users where CASE WHEN vtiger_users.id IS NOT NULL THEN CONCAT(vtiger_users.first_name,IF(vtiger_users.first_name != '' AND vtiger_users.first_name IS NOT NULL,' ',''),vtiger_users.last_name) END = ?";
        	$result = $adb->pquery($sql, array($username));
        	$num_rows = $adb->num_rows($result);
        	if($num_rows > 0) {
        		$user_id = $adb->query_result($result,0,"id");
            }
        }
    	return $user_id;
    }
    // ITS4YOU-CR SlOl 15. 3. 2016 14:31:29
    private function get120UserIdSQL($value){
		$adb = PearDatabase::getInstance();
        if($value!=""){
            $vtmp = trim($value, "('");
            $vtmp = trim($vtmp, "')");
            $value_arr = explode("','", $vtmp);
            if(!empty($value_arr)){
                foreach($value_arr as $username){
                    $nv_arr[] = $this->getUI120UserID($username);
                }
            }
            $value_arr = $nv_arr;
            $value = " (";
            foreach($value_arr as $abc){
                $abc_arr[] = " FIND_IN_SET( $abc, vtiger_crmentity.shownerid ) ";
            }
            $value .= implode(" OR ", $abc_arr);
            $value .= " )";
        }
        return $value;
    }
    // ITS4YOU-END
    public function getGenerateForUsers($generateFor=array()){
        $generateForUsers = array();
        if(!empty($generateFor)){
            if($this->gj_sql!=""){
                $adb = PearDatabase::getInstance();
                $gj_sql = $this->gj_sql;
                foreach($generateFor as $userColumnStr){
                    if(isset($this->columns_array[$userColumnStr]) && isset($this->columns_array[$userColumnStr]["userid_fld"]) && $this->columns_array[$userColumnStr]["userid_fld"]!=""){
                        $userid_fld = $this->columns_array[$userColumnStr]["userid_fld"];

                        $userid_Query = "SELECT $userid_fld $gj_sql GROUP BY $userid_fld ORDER BY $userid_fld ";

                        $f_result = $adb->pquery($userid_Query, array());
                        if ($f_result) {
                            while ($reportrow = $adb->fetch_array($f_result)) {
                                if(!in_array($reportrow[0],$generateForUsers) && $reportrow[0]!=""){
                                    $generateForUsers[] = $reportrow[0];
                                }
                            }
                        }
                    }
                }
            }
        }
        sort($generateForUsers);
        return $generateForUsers;
    }
    // ITS4YOU-CR SlOl 19. 5. 2016 9:12:36
    private function displayCustomCalculations($currency_id){

		$layout = Vtiger_Viewer::getDefaultLayoutName();
        $default_charset = vglobal("defrpt4youTableault_charset");
        global $currentModule;

        require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

        $return_tr = $bg_color = "";

        $cc_array = $this->report_obj->reportinformations["cc_array"];
        if (!empty($cc_array) && !empty($this->cc_values_array)) {
            foreach ($cc_array as $cc_nr => $cc_row) {
                $cc_label = $cc_row["cc_name"];
                $cc_expr = html_entity_decode($cc_row["cc_calculation"], ENT_QUOTES, $default_charset);

                /** CUSTOM CALCULATION START **/
                try {
                    $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($cc_expr)));
                    $expression = $parser->expression();

                    $exprEvaluater = new VTFieldExpressionEvaluater($expression);

                    if ($layout == "v7") {
                        $newEntity = CRMEntity::getInstance($this->report_obj->primarymodule);
                        $newEntity->mode = '';
                        $entityData = VTEntityData::fromCRMEntity($newEntity);
                    } else {
                        $entityData = new VTEntityData();
                    }

                    foreach ($this->cc_values_array as $cc_fld_alias => $cc_fld_value) {
                        $entityData->set($cc_fld_alias, $cc_fld_value);
                    }
                    $fieldvalue = $exprEvaluater->evaluate($entityData);
                } catch (Exception $e) {
                    $fieldvalue = vtranslate('LBL_EXPRESSION_ERROR', $currentModule);
                }

                if (is_numeric($fieldvalue)) {
                    $fieldvalue = $this->formatFldNumberValue($fieldvalue);
                    $fld_style_arr = $this->getFldStyle($cc_label, $fieldvalue, true);
                } else {
                    $fld_style_arr = $this->getFldStyle($cc_label, $fieldvalue);
                }
                $fld_style = $this->getFldStyleString($fld_style_arr);

                // SET totals OF custom calculations ! s
                if (!empty($cc_row['cc_totals']) && !empty($cc_row['cc_totals'][0])) {
                    $to_totals_array[$cc_label] = $cc_row['cc_totals'];
                    $to_totals_array[$cc_label]['label'] = $cc_label;
                    $this->to_totals_res = $this->setToTotalsArray('-', $this->to_totals_res, $cc_label, $fieldvalue, $to_totals_array, $currency_id);
                }
                // SET totals OF custom calculations ! e

                $return_tr .= "<td class='rpt4youGrpHead'  style='$fld_style $bg_color' nowrap >" . $fieldvalue . "</td>";
            }
        }

        return $return_tr;
    }

    protected function getQuickFiltersFromRequest(Vtiger_Request $request) {
        global $default_charset;
        $qfRequest = array();
        $requestAll = $request->getAll();
        if ($requestAll['quick_filter_criteria']) {
            $decodedQFCriteria = html_entity_decode($requestAll['quick_filter_criteria'], ENT_QUOTES, $default_charset);
            $quickFilterCriteria = Zend_Json::decode($decodedQFCriteria);
            // transformation
            foreach ($quickFilterCriteria as $qfArray) {
                $qfRequest[$qfArray['columnname']]['value'] = $qfArray['value'];
                $qfRequest[$qfArray['columnname']]['radio'] = $qfArray['radio_value'];
            }
        }
        return $qfRequest;
    }

    protected function getQuickFiltersHtml() {
        $adb = PearDatabase::getInstance();
        global $currentModule, $default_charset;
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $requestAll = $request->getAll();

        $divCustomStyle = 'float:left;display: inline;';

        $qfRequest = $this->getQuickFiltersFromRequest($request);

        $quickFiltersHtml = '<div class="padding1per no-print">';

        if($layout == "v7"){
            $dataAreaClass = '';
            if (!empty($this->report_obj->reportinformations['charts']) && 1 === (int) $this->report_obj->reportinformations['charts'][1]['collapse_data_block']) {
                $dataAreaClass = 'hide';
            }
            $quickFiltersHtml .= '<div id="quickFilterArea" class="well ' . $dataAreaClass . '" style="float:left;width:100%;background: #FFFFFF;">';
        } else {
            $quickFiltersHtml .= '<div id="quickFilterArea" class="summaryWidgetContainer" style="width:98%;'.$divCustomStyle.'">';
        }
        if (!empty($this->report_obj->reportinformations['quick_filters'])) {
            $quickFiltersHtmlRows = [];
            $quickFiltersHtmlRowHtml = '';
            $rowsI = 1;
            $colsI = 1;
            foreach ($this->report_obj->reportinformations['quick_filters'] as $qfColumn) {
                if(isset($this->columns_array[$qfColumn]['fld_sql_str'])) {
                    $fldSqlStr = $this->columns_array[$qfColumn]['fld_sql_str'];
                    $fld_cond = $this->columns_array[$qfColumn]['fld_cond'];
                    $fldname = $this->columns_array[$qfColumn]['fld_alias'];
                    $columns_array_lbl = $this->columns_array[$fldname];
                    $quickFiltersHtmlRowHtml = '<div class="padding10" style="float: left;width:30%;">';
                    if ($this->select_body) {
                        $getQuickFilterSql = 'SELECT DISTINCT ' . $fldSqlStr . ' ' . $this->select_body . ' ORDER BY ' . $fld_cond;

                        //$adb->setDebug(true);
                        $getQuickFilterResult = $adb->query($getQuickFilterSql);
                        //$adb->setDebug(false);

                        if (isset($qfRequest[$fldname]['radio']) && 'isnot' === $qfRequest[$fldname]['radio']) {
                            $Radio = array("","checked");
                            $sqlradio = "NOT IN";
                        } else {
                            $Radio = array("checked","");
                            $sqlradio = "IN";
                        }

                        $headerLabel = $this->getHeaderLabel($this->report_obj->record, 'SC', $fldname, $columns_array_lbl);
                        if ($layout == "v7") {
                            $quickFiltersHtmlRowHtml .= '<table class="table table-borderless">';
                            $quickFiltersHtmlRowHtml .= '<tr><td colspan="5"><b>'.$headerLabel.'</b></td></tr>';

                            $quickFiltersHtmlRowHtml .= '<tr>';
                            $quickFiltersHtmlRowHtml .= '<td style="width:5%;">
<input type="radio" name="radio_'.$fldname.'" value="is" '.$Radio[0].' />
</td>';
                            $quickFiltersHtmlRowHtml .= '<td style="width:11%;">
'.vtranslate('LBL_IS', $currentModule).'
</td>';
                            $quickFiltersHtmlRowHtml .= '<td style="width:5%;">
<input type="radio" name="radio_'.$fldname.'" value="isnot" '.$Radio[1].' />
</td>';
                            $quickFiltersHtmlRowHtml .= '<td style="width:11%;">
'.vtranslate('LBL_IS_NOT', $currentModule).'
</td>';
                            $quickFiltersHtmlRowHtml .= '<td >
<a href="javascript:uncheckAll(' . "'" . $fldname . "'" . ');">' . vtranslate('LBL_UNCHECK_ALL', $currentModule) . '</a>
</td>';
                            $quickFiltersHtmlRowHtml .= '</tr>';
                            $quickFiltersHtmlRowHtml .= '<tr><td colspan="5">';
                        } else {
                            $quickFiltersHtmlRowHtml .= "<b>".$headerLabel."</b><br>";
                            $quickFiltersHtmlRowHtml .= "<input type='radio' name='radio_".$fldname."' value='is' ".$Radio[0]."/>".vtranslate("LBL_IS", $currentModule)."&nbsp;&nbsp;";
                            $quickFiltersHtmlRowHtml .= "<input type='radio' name='radio_".$fldname."' value='isnot' ".$Radio[1]."/>".vtranslate("LBL_IS_NOT", $currentModule);
                            $quickFiltersHtmlRowHtml .= "<br />";
                        }

                        $noOfColumnsData = $adb->num_rows($getQuickFilterResult);
                        if (!$noOfColumnsData) {
                            $quickFiltersHtmlRowHtml .= vtranslate('LBL_NO_DATA_FOR_COLUMN', $currentModule);
                        } else {
                            if($layout == "v7"){
                                $qfSelectClass = 'select2 col-lg-6';
                            } else {
                                $qfSelectClass = 'chzn-select span6';
                            }
                            $quickFiltersHtmlRowHtml .= "<select name='".$fldname."[]' id='".$fldname."' multiple class='$qfSelectClass quickFilter4You' >";

                            $SelectValues = array();
                            while ($qfRow = $adb->fetchByAssoc($getQuickFilterResult)) {

                                $fldvalue = $qfRow[$fldname];
                                $fldvalue = html_entity_decode($fldvalue, ENT_QUOTES, $default_charset);

                                list($tablename, $columnname, $moduleStr) = explode(':', $columns_array_lbl);
                                $moduleStrArray = explode('_', $moduleStr);
                                $translateFrom = $moduleStrArray[0];
                                if(''===$fldvalue){
                                    $fldvalue = 'NULL';
                                    $translateFrom = $currentModule;
                                }

                                if (isset($requestAll[$fldname]) ) {
                                    if (count($requestAll[$fldname]) > 0) {
                                        foreach ($requestAll[$fldname] as $value) {
                                            $SelectValues[$fldname][] = $value;
                                        }
                                    }
                                    $filterlist[$fldname] = $fld_cond." ".$sqlradio." ('".implode("','", $requestAll[$fldname])."') ";
                                }

                                if (isset($qfRequest[$fldname]) && count($qfRequest[$fldname]['value']) > 0 && in_array($fldvalue,$qfRequest[$fldname]['value'])){
                                    $fldselected = "selected";
                                } else {
                                    $fldselected = "";
                                }
                                $fieldvalue = vtranslate($fldvalue, $translateFrom);
                                $quickFiltersHtmlRowHtml .= "<option value='".$fldvalue."' ".$fldselected.">".$fieldvalue."</option>";
                            }
                            $quickFiltersHtmlRowHtml .= '</select>';
                            if ($layout == "v7") {
                                $quickFiltersHtmlRowHtml .= '</td></tr>';
                                $quickFiltersHtmlRowHtml .= '</table>';
                            } else {
                                $quickFiltersHtmlRowHtml .= '<br /><a href="javascript:uncheckAll(' . "'" . $fldname . "'" . ');">' . vtranslate('LBL_UNCHECK_ALL', $currentModule) . '</a>';
                            }
                        }
                    } else {
                        $quickFiltersHtmlRowHtml .= vtranslate('SQL_BODY_MISSING');
                    }
                    $quickFiltersHtmlRowHtml .= '</div>';
                } else {
                    if (isset($this->columns_array[$qfColumn]['fld_alias'])) {
                        $fldAlias = $this->columns_array[$qfColumn]['fld_alias'];
                    } else {
                        $fldAlias = $qfColumn;
                    }
                    $quickFiltersHtmlRowHtml .= vtranslate('SQL_COLUMN_NOT_PREPARED', $currentModule) . ": $fldAlias";
                }
                $colsI++;
                $quickFiltersHtmlRows[$rowsI][$colsI] = $quickFiltersHtmlRowHtml;
                if (3 < $colsI) {
                    $colsI = 1;
                    $rowsI++;
                }
            }
            if (!empty($quickFiltersHtmlRows)) {
                foreach ($quickFiltersHtmlRows as $qfRow) {
                    $quickFiltersHtml .= '<div class="row-fluid">';
                    $quickFiltersHtml .= (is_array($qfRow) ? implode('', $qfRow) : $qfRow);
                    $quickFiltersHtml .= '</div>';
                }
            }
        } else {
            $quickFiltersHtml .= '<div class="row-fluid">'.vtranslate('LBL_NO_DATA_TO_DISPLAY').'</div>';
        }
        $quickFiltersHtml .= '</div>';
        $quickFiltersHtml .= '</div>';

        return $quickFiltersHtml;
    }

    /**
     * function to return html of hidden areas for Report charts
     * @param null $postFix
     *
     * @return string
     */
    private function addHighChartDiv($postFix = null) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $return = '';
        if ('funnel' === $this->charts['charttypes'][$this->charts['charts_ds_columns'][0]]) {
            $rw_width = '63%';
            $rw_custom_style = 'margin-left:18%;';
        } else {
            $rw_width = '95%';
            $rw_custom_style = '';
        }
        $noPrintClass = 'no-print';
        if($layout == "v7"){
            $noPrintClass = '';
        }
        $return .= "<div class='$noPrintClass' style='text-align:center;width:$rw_width;margin:30px;$rw_custom_style border:0px solid red;'>";
        if (!$postFix) {
            $return .= '<br/>';
        } else {
            $postFix .= '_';
        }
        $return .= "<div id='reports4you_widget_" . $postFix . $this->report_obj->record . "' style='border:0px solid green;width:$rw_width;margin:auto;'></div>";
        if ($postFix) {
            $return .= '<br/>';
        }
        $return .= '</div>';

        return $return;
    }

    private function getDetailHeader($header, $group2_headers) {
        $default_charset = vglobal('default_charset');
        $h_i = 0;
        $header_f = '';
        foreach ($header as $header_f_arr) {
            $header_style = $header_f_arr['style'];
            if (array_key_exists('module', $header_f_arr)) {
				$headerLabel = vtranslate($header_f_arr['label'], $header_f_arr['module']);
			} else {
            	$headerLabel = $header_f_arr['label'];
            }
            $header_rowspan = 1;
            $header_colspan = 1;
            if (!empty($group2_headers)) {
                if ($h_i == 0) {
                    $header_rowspan++;
                } else {
                    if (array_key_exists($headerLabel, $group2_headers)) {
                        $header_colspan = count($group2_headers[$headerLabel]);
                    } else {
                        $header_colspan = count($this->summaries_labels);
                    }
                }
            }
            if ($headerLabel == "LBL_GROUPING_TOTALS") {
                $headerLabel = getTranslatedString("LBL_GROUPING_TOTALS", $this->getCurrentModule4You());
            }
            if (($this->report_obj->reportinformations["Group3"] != "none" && $this->report_obj->reportinformations["timeline_type3"] == "cols" || $this->report_obj->reportinformations["Group2"] != "none" && $this->report_obj->reportinformations["timeline_type2"] == "cols") && $h_i != 0 && isset($this->sum_col_i) && $this->sum_col_i != 1) {
                $header_colspan = $this->sum_col_i;
            }
            $header_f .= "<td class='rpt4youCellLabel' rowspan='$header_rowspan' colspan='$header_colspan' $header_style align='center' nowrap >$headerLabel</td>";
            $h_i++;
        }
        if (!empty($group2_headers)) {
            $header_f .= '</tr><tr>';
            foreach ($group2_headers as $g2_h_key => $g2_h_arr) {
                foreach ($g2_h_arr as $g2_h_labels) {
                    $headerLabel = $g2_h_labels["label"];
                    $header_f .= "<td class='rpt4youCellLabel' style='" . $this->header_style . "' align='center' nowrap >$headerLabel</td>";
                }
            }
        }

        return $header_f;
	}

	public static function getUserDateFormatFromDB($currentUser) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT date_format FROM vtiger_users where id=?', array($currentUser->getId()));
		$num_rows = $db->num_rows($result);
        if ($num_rows > 0) {
			$row = $db->fetch_array($result);
			$format = $row['date_format'];
		} else {
			$format = 'dd-mm-yyyy';
		}

		return $format;
	}
    // ITS4YOU-END
    private static function displaySqlFormattedQuery($query) {
		$libPath = 'modules/ITS4YouReports/lib/SqlFormatter.php';
		if ($query && file_exists($libPath)) {
			include_once($libPath);
			echo 'Formated:';
			print_r(SqlFormatter::format($query));
		} else {
			echo 'Query:';
			echo '<pre>';print_r($query);echo '</pre>';
		}
		return true;
	}

    /**
     * @return array
     */
    private function getMapsAliases() {
        $mapsAliases = [];

        if (!empty($this->report_obj->reportinformations['maps'])) {
            foreach ($this->report_obj->reportinformations['maps'] as $mi => $mapColumn) {
                if (!in_array($mi, ITS4YouReports_BingMaps_View::$mapParameterColumns) && array_key_exists($mapColumn, $this->columns_array)) {
                    $mapsAliases[] = $this->columns_array[$mapColumn]['fld_alias'];
                }
            }
        }

        return $mapsAliases;
    }

    /**
     * @param $reportId
     *
     * @return array
     * @throws Exception
     */
    public function generateBingMapsData($reportId) {
        $db = PearDatabase::getInstance();
        $mapsData = [];
        $locationsData = [];
        $mapsColumns = $this->report_obj->reportinformations['maps'];
        $maxQueryLimit = 200000;

        if (!empty($mapsColumns)) {
            $this->generateQuery($reportId, '', 'ApiMaps');
            $sSQL = $this->tf_sql;
            $mapsAliases = $this->getMapsAliases();

            if (!empty($sSQL) && !empty($mapsAliases)) {
                $locationsResult = $db->query($sSQL);

                if ($db->num_rows($locationsResult)) {
                    if ($maxQueryLimit > (int) $db->num_rows($locationsResult)) {
                        $streetAliases = [];
                        foreach (ITS4YouReports_BingMaps_View::$addressColumns as $bingColumnName) {
                            if (array_key_exists($bingColumnName, $mapsColumns)) {
                                $bingColumnString = $mapsColumns[$bingColumnName];
                                if (array_key_exists($bingColumnString, $this->columns_array)) {
                                    $streetAliases[] = $this->columns_array[$bingColumnString]['fld_alias'];
                                }
                            }
                        }

                        while ($row = $db->fetchByAssoc($locationsResult)) {
                            $locationTexts = [];
                            $pinTitle = '';
                            $pinDescription = '';
                            $this->data_record_id = $row['record_id'];

                            foreach ($streetAliases as $asName) {
                                if (!empty($row[$asName])) {
                                    $locationTexts[] = $row[$asName];
                                }
                            }
                            if (!empty($mapsColumns['pin_title'])) {
                                if (array_key_exists($mapsColumns['pin_title'], $this->columns_array)) {
                                    $fldAlias = $this->columns_array[$mapsColumns['pin_title']]['fld_alias'];
                                    $uitype = $this->columns_array[$fldAlias];
                                    $rawValue = $row[$fldAlias];
                                    $this->outputformat = '';
                                    $pinTitle = $this->getFldFormatedValue($uitype, $rawValue, $fldAlias);
                                }
                            }
                            if (!empty($mapsColumns['pin_description'])) {
                                if (array_key_exists($mapsColumns['pin_description'], $this->columns_array)) {
                                    $fldAlias = $this->columns_array[$mapsColumns['pin_description']]['fld_alias'];
                                    $uitype = $this->columns_array[$fldAlias];
                                    $rawValue = $row[$fldAlias];
                                    $this->outputformat = 'HTML';
                                    $pinDescription = $this->getFldFormatedValue($uitype, $rawValue, $fldAlias);
                                }
                            }
                            $locationsData[] = [$locationTexts, $pinTitle, $pinDescription];
                        }
                        $mapsData = ITS4YouReports_Bing_Map::handleGeoLocationsByAddress($locationsData);
                    } else {
                        throw new Exception(sprintf('Maximum of %s record should be displayed!', $maxQueryLimit));
                    }
                } else {
                    throw new Exception('Result can not be empty: ' . self::displaySqlFormattedQuery($sSQL));
                }
            }
        }

        return $mapsData;
    }


    private static function getModuleNameFromColumnStr($columnStr='') {
    	$columnModule = '';

		if (!empty($columnStr)) {
            list($tablename, $colname, $moduleField) = explode(':', $columnStr);
            $moduleFieldArr = explode('_', $moduleField);
            $columnModule = $moduleFieldArr[0];
		}

		return $columnModule;
	}

	private static function isAddCurrencyField($fldName, $columnsArray) {
		$isAddCurrencyField = false;
		if ( ((in_array($columnsArray['uitype_' . $fldName], self::$currencyUiTypes) && !in_array($fldName, ['quantity']))
	       	  || (  in_array($fldName, ITS4YouReports::$intentory_fields)
	                && !in_array($fldName, ITS4YouReports::$intentory_skip_formating)) )
	         && !in_array($fldName, ['quantity'])) {
			$isAddCurrencyField = true;
		}

		return $isAddCurrencyField;
	}

    protected function getClearFldName($fldName)
    {
        $fldNameArr = explode('_', $fldName);
        if (in_array(strtolower($fldNameArr[(count($fldNameArr) - 1)]), $this->calculation_type_array)) {
            unset($fldNameArr[(count($fldNameArr) - 1)]);
        }

        return implode('_', $fldNameArr);
    }

	protected static function getCurrencyForFld($currencyId = '')
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        if ($current_user && !empty($current_user->get('currency_id'))) {
            $currencyId = $current_user->get('currency_id');
        } elseif (empty($currencyId)) {
            self::$currencyArray['id'];
        }

        return $currencyId;
    }

    public static function placeReportNameToPDF($reportName)
    {
        $name = "<table class='rpt4youTableText' width='100%'>";
        $name .= "<tr>";
        $name .= "<td colspan='1' class='rpt4youGrpHeadInfoText' width='100%' style='border:0px;'>";
        $name .= $reportName;
        $name .= "</td>";
        $name .= "</tr>";
        $name .= "</table>";

        return $name;
    }
}

?>