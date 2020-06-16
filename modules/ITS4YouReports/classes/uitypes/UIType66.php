<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType66 extends UITypes {
    protected $oth_as = '_66';

    public function getStJoinSQL(&$join_array, &$columns_array) {
        return "";
    }

    public function getJoinSQL_UI66(&$join_array, &$columns_array, $uirelmodule) {
        $field_case = "";
        if (vtlib_isModuleActive($uirelmodule)) {
            $adb = PEARDatabase::getInstance();
            $uirel_tabid = getTabid($uirelmodule);
            $uirel_focus = CRMEntity::getInstance($uirelmodule);
            $uirel_table_name = $uirel_focus->table_name;
            $uirel_table_index = $uirel_focus->table_index;

            $fieldid_alias = "";
            if($uirel_table_name!=$this->params["report_primary_table"]){
                if ($this->params["fieldid"] != "") {
                    $fieldid_alias = "_" . $this->params["fieldid"];
                }
            }

            $entity_field_arr = $this->getEntityNameFields($uirelmodule, $uirel_table_name . $fieldid_alias);

            $field_case .= " WHEN " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " IS NOT NULL AND " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " != '' THEN " . $entity_field_arr["fieldname"]." ";
            $oth_as = $old_oth_fieldid = $mif_as = "";
            if ($this->params["old_oth_as"] != "") {
                $old_oth_as = $this->params["old_oth_as"];
            }
            if ($this->params["old_oth_fieldid"] != "") {
                if ($this->params["old_oth_fieldid"] == "mif") {
                    $mif_as = "_" . $this->params["fieldtabid"];
                }
                $old_oth_fieldid = "_" . $this->params["old_oth_fieldid"];
            }
            $oth_as = "_" . $uirel_tabid;

            if($this->params["report_primary_table"]=="vtiger_activity"){
                if (!array_key_exists(" vtiger_seactivityrel AS vtiger_seactivityrel ", $join_array)) {
                    $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel "]["joincol"] = "vtiger_seactivityrel.activityid ";
                    $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel "]["using"] = "vtiger_activity.activityid";
                }
            }

//            LEFT JOIN  vtiger_seactivityrel AS vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid

            if($this->params["old_oth_fieldid"]=="mif" || $this->params["report_primary_table"]!=$uirel_table_name){
                if(!array_key_exists(" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " ", $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
                    $join_array[" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " "]["joincol"] = "$uirel_table_name" . $fieldid_alias . ".$uirel_table_index";
                    $join_array[" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " "]["using"] = $this->params["tablename"] . $old_oth_as . $old_oth_fieldid . $mif_as . "." . $this->params["columnname"];
                }
                if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_66" . $oth_as . $fieldid_alias . " ", $join_array)) {
                    $join_array[" vtiger_crmentity AS vtiger_crmentity_66" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_66" . $oth_as . $fieldid_alias . ".crmid";
                    $join_array[" vtiger_crmentity AS vtiger_crmentity_66" . $oth_as . $fieldid_alias . " "]["using"] = "$uirel_table_name" . $fieldid_alias . ".$uirel_table_index ";
                }
            }
        }
        return $field_case;
    }

    public function getJoinSQL(&$join_array, &$columns_array) {

        $adb = PEARDatabase::getInstance();
        $fieldid = $this->params['fieldid'];

        $fld_cond = " CASE ";

		$ui66_res = $adb->query('SELECT type module FROM vtiger_ws_referencetype WHERE fieldtypeid = (
										SELECT fieldtypeid FROM vtiger_ws_fieldtype WHERE uitype = 66
									)');
        while ($ui66_row = $adb->fetch_array($ui66_res)) {
            $uirelmodule = $ui66_row['module'];
            $fld_cond .= $this->getJoinSQL_UI66($join_array, $columns_array, $uirelmodule);

        }

        $field_col_as = $this->params['fieldname'] . $fieldid_alias;
        $fld_cond .= ' END ';
        $field_case = $fld_cond . ' AS ' . $field_col_as;
        $columns_array[] = $field_case;
        $columns_array[$this->params['fld_string']]['fld_alias'] = $field_col_as;
        $columns_array[$this->params['fld_string']]['fld_sql_str'] = $field_case;
        $columns_array[$this->params['fld_string']]['fld_cond'] = $fld_cond;
        $columns_array['uitype_'.$field_col_as] = $this->params['field_uitype'];
        $columns_array[$field_col_as] = $this->params['fld_string'];

    }

    public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array) {
        require_once('modules/ITS4YouReports/ITS4YouReports.php');

//        show("UI10params",$this->params);
        $formodule = $this->params["formodule"];
        $modulename = vtlib_getModuleNameById($formodule);
        $related_focus = CRMEntity::getInstance($modulename);

        $params_fieldname = $this->params["fieldname"];
        // first join to vtiger module table
        $this->params["fieldname"] = $related_focus->tab_name_index[$this->params["tablename"]];

        $this->getStJoinSQL($join_array, $columns_array);

        $r_tabid = getTabid($modulename);
        $uirel_row = array();
        if(!in_array($this->params["columnname"], ITS4YouReports::$intentory_fields)){
            $adb = PEARDatabase::getInstance();
            $uirel_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($r_tabid, $params_fieldname)), 0);
        }

        $related_table_name = $related_focus->table_name;
        $related_table_index = $related_focus->table_index;
        foreach ($related_focus->tab_name as $other_table) {
            $related_join_array[$other_table] = $related_focus->tab_name_index[$other_table];
        }
        $field_uitype = $uirel_row["uitype"];
        $fieldid = $this->params["fieldid"];
        $oth_as = "_$formodule";
        if ($uirel_row["tablename"] == "vtiger_crmentity") {
            $oth_as = "";
            $related_table_name = $uirel_row["tablename"];
            $related_table_index = $uirel_row["columnname"];
        }
        if(empty($uirel_row)){
            $uirel_row["fieldid"] = $this->params["fieldid"];
            $uirel_row["tabid"] = $r_tabid;
            $uirel_row["fieldname"] = $this->params["fieldname"];
            $uirel_row["columnname"] = $this->params["columnname"];
            $uirel_row["tablename"] = $related_table_name;
        }

        // stjoin content start
        if (isset($this->params["fieldid"]) && $this->params["fieldid"] != "") {
            $fieldid_alias = $old_oth_fieldid = "";
            if ($this->params["fieldid"] != "") {
                $fieldid_alias = "_$formodule"."_" . $this->params["fieldid"];
                if($this->params["tablename"]=="vtiger_crmentity"){
                    $fieldid_alias = "_66".$fieldid_alias;
                }
            }
            if ($this->params["old_oth_fieldid"] != "") {
                $old_oth_fieldid = "_" . $this->params["old_oth_fieldid"];
            }
            $fieldid = $this->params["fieldid"];
            $adb = PEARDatabase::getInstance();
            $stjoin_row = $adb->fetchByAssoc($adb->pquery("SELECT tablename, columnname FROM vtiger_field WHERE fieldid = ? ", array($fieldid)), 0);
            $tablename = $stjoin_row["tablename"];
            $columnname = $stjoin_row["columnname"];
            if ($tablename != $this->params["primary_table_name"]) {
                $primary_focus = CRMEntity::getInstance(vtlib_getModuleNameById($this->params["primary_tableid"]));
                if (array_key_exists($tablename, $primary_focus->tab_name_index)) {
                    if (!array_key_exists(" " . $tablename . " AS " . $tablename . " ", $join_array)) {
                        $join_array[" " . $tablename . " AS " . $tablename . " "]["joincol"] = $tablename . "." . $primary_focus->tab_name_index[$tablename];
                        $join_array[" " . $tablename . " AS " . $tablename . " "]["using"] = $primary_focus->table_name . "." . $primary_focus->table_index;
                    }
                }
            }
        }
        if (!array_key_exists(" " . $related_focus->table_name . " AS " . $related_focus->table_name . $fieldid_alias . " ", $join_array)) {
            $join_array[" " . $related_focus->table_name . " AS " . $related_focus->table_name . $fieldid_alias . " "]["joincol"] = $related_focus->table_name . $fieldid_alias . "." . $related_focus->table_index;
            $join_array[" " . $related_focus->table_name . " AS " . $related_focus->table_name . $fieldid_alias . " "]["using"] = $tablename . "." . $columnname;
        }

        if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " ", $join_array)) {
            $join_array[" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity" . $oth_as . $fieldid_alias . ".crmid";
            $join_array[" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " "]["using"] = $related_focus->table_name . $fieldid_alias . "." . $related_focus->table_index . " ";
        }
        // stjoin content end

//ITS4YouReports::sshow($related_table_name);
//ITS4YouReports::sshow($join_array);
        $using_aliastablename = $related_table_name . $fieldid_alias;
        $using_columnname = $related_table_index;
        $params = Array('fieldid' => $uirel_row["fieldid"],
            'fieldtabid' => $oth_as.$uirel_row["tabid"],
            'field_uitype' => $field_uitype,
            'fieldname' => $uirel_row["fieldname"],
            'columnname' => $uirel_row["columnname"],
            'tablename' => $uirel_row["tablename"],
            'table_index' => $related_join_array,
            'report_primary_table' => $this->params["report_primary_table"],
            'primary_table_name' => $related_focus->table_name,
            'primary_table_index' => $related_focus->table_index,
            'primary_tableid' => $r_tabid,
            'using_aliastablename' => $using_aliastablename,
            'using_columnname' => $using_columnname,
            'old_oth_as' => "",
            'old_oth_fieldid' => trim($fieldid_alias,"_"),
            'fld_string' => $this->params["fld_string"],
            'formodule' => $formodule,
        );
        $using_array = getJoinInformation($params);
        $params["using_array"] = $using_array;
        $uifactory = new UIFactory($params);
// show("<font color='green'>fielduitype10_IN_JO_".$field_uitype,$using_array,$this->params,$join_array,"fielduitype10_IN_JO_PO","</font>");
        if(in_array($this->params["columnname"], ITS4YouReports::$intentory_fields)){
            $uifactory->getInventoryJoinSQL($field_uitype, $join_array, $columns_array);
        }else{
            $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
        }

    }

    public function getInventoryJoinSQL(&$join_array, &$columns_array) {
        $fieldid_alias = $old_oth_fieldid = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $fieldid_alias . " "]["joincol"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".id";
        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $fieldid_alias . " "]["using"] = "vtiger_quotes" . $fieldid_alias . ".quoteid";

        $join_array[" vtiger_products AS vtiger_products_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_products_inv" . $fieldid_alias . ".productid";
        $join_array[" vtiger_products AS vtiger_products_inv" . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".productid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_products_inv" . $fieldid_alias . ".crmid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $fieldid_alias . " "]["using"] = "vtiger_products_inv" . $fieldid_alias . ".productid ";

        $join_array[" vtiger_service AS vtiger_service_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_service_inv" . $fieldid_alias . ".serviceid";
        $join_array[" vtiger_service AS vtiger_service_inv" . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".productid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_service_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_service_inv" . $fieldid_alias . ".crmid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_service_inv" . $fieldid_alias . " "]["using"] = "vtiger_service_inv" . $fieldid_alias . ".serviceid ";

        $column_tablename = "vtiger_inventoryproductrel";

        if ($this->params["columnname"] != "") {
            $column_tablename_alias = $this->params["tablename"];
            if ($column_tablename_alias == "vtiger_crmentity") {
                $column_tablename_alias = $column_tablename . "_" . strtolower($this->params["fieldmodule"]) . "_inv";
            }

            $fld_cond = $this->getInventoryColumnFldCond($this->params["columnname"],$column_tablename_alias,$fieldid_alias);
            $columns_array_value = $this->getColumnsArrayValue($fld_cond,$fieldid_alias);
            $fld_alias = $this->params["columnname"] . $fieldid_alias;

            if(!in_array(" vtiger_inventoryproductrel" . $fieldid_alias . ".lineitem_id AS lineitem_id" . $fieldid_alias . " ",$columns_array)){
                $columns_array[] = " vtiger_inventoryproductrel" . $fieldid_alias . ".lineitem_id AS lineitem_id" . $fieldid_alias . " ";
            }
            if($using_tablename!="" && $using_columnname!=""){
                $columns_array[] = " $using_tablename.$using_columnname AS record_id" . $fieldid_alias . " ";
            }
            $columns_array[] = $columns_array_value;
            $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
            $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
            $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
            $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
            $columns_array[$fld_alias] = $this->params["fld_string"];
        }
        return;
    }

    /*public function getModulesByUitype($tablename, $columnname) {
        $modulename[] = $this->relModuleName;
        return $modulename;
    }*/

    public function getSelectedFieldCol($selectedfields) {
        $return = array();
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        if ($selectedfields[1] == 'quoteid') {
            $return = "vtiger_quotes" . $fieldid_alias . "." . $selectedfields[1];
        } elseif ($selectedfields[0] == "vtiger_crmentity") {
            $return = $selectedfields[0] . $this->oth_as . $fieldid_alias . "." . $selectedfields[1];
        } else {
            $return = $selectedfields[0] . $fieldid_alias . "." . $selectedfields[1];
        }

        return $return;
    }

    // ITS4YOU-CR SlOl 3. 3. 2014 15:22:07
    function getEntityNameFields($module, $table_alias) {
        $adb = PearDatabase::getInstance();
        $data = array();
        if (!empty($module)) {
            $query = "select fieldname,tablename,entityidfield from vtiger_entityname where modulename = ?";
            $result = $adb->pquery($query, array($module));
            $fieldsname = $adb->query_result($result, 0, 'fieldname');
            $tablename = $adb->query_result($result, 0, 'tablename');
            $entityidfield = $adb->query_result($result, 0, 'entityidfield');
            if (!(strpos($fieldsname, ',') === false)) {
                $fieldlists = explode(',', $fieldsname);
                foreach ($fieldlists as $key => $c_fieldsname) {
                    $fieldlists_n[] = $table_alias . "." . $c_fieldsname;
                }
                $fieldsname = "concat(";
                $fieldsname = $fieldsname . implode(",' ',", $fieldlists_n);
                $fieldsname = $fieldsname . ")";
            } else {
                $fieldsname = $table_alias . "." . $fieldsname;
            }
        }
        $data = array("tablename" => $tablename, "fieldname" => $fieldsname);
        return $data;
    }

}

?> 