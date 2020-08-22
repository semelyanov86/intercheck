<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UITypeINV extends UITypes {

    public function getStJoinSQL(&$join_array, &$columns_array) {
        return "";
    }

    public function getJoinSQL(&$join_array, &$columns_array) {
        /*         * * !!! DO NOT TOUCH THIS CODE IF YOU ARE NOT SURE !!! */
        $fieldid_alias = $old_oth_fieldid = $mif_as = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if ($this->params["old_oth_fieldid"] != "") {
            if ($this->params["old_oth_fieldid"] == "mif") {
                $mif_as = "_" . $this->params["fieldtabid"];
            }
            $old_oth_fieldid = "_" . $this->params["old_oth_fieldid"];
        }

        $join_tablename = $this->params["using_array"]["join"]["tablename"];
        $join_columnname = $this->params["using_array"]["join"]["columnname"];

        $join_tablename_alias = $this->params["join_tablename_alias"] = $join_tablename . $fieldid_alias;
        $join_alias = " " . trim($join_tablename, $old_oth_fieldid) . " AS " . $join_tablename_alias . " ";

        if ($this->params["primary_table_name"]!=$join_tablename_alias && isset($this->params["using_array"]) && !empty($this->params["using_array"]["using"]) && !array_key_exists($join_alias, $join_array)) {
            $using_tablename = $this->params["using_array"]["using"]["tablename"];
            $using_columnname = $this->params["using_array"]["using"]["columnname"];
            if ($join_tablename != $this->params["primary_table_name"] && $using_tablename != "" && $using_columnname != "") {
                $join_array[$join_alias]["joincol"] = $join_tablename_alias . "." . $join_columnname;
                $join_array[$join_alias]["using"] = $using_tablename . "." . $using_columnname;
            }
        }
        $fld_cond = $join_tablename_alias . "." . $this->params["columnname"];
        $columns_array_value = $fld_cond . " AS " . $this->params["columnname"] . $old_oth_fieldid;
        $fld_alias = $this->params["columnname"] . $old_oth_fieldid;

        $columns_array[] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
        $columns_array[$this->params["fld_string"]]["fld_alias_hid"] = $fld_alias . "_hid";
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
        $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
        $columns_array[$fld_alias] = $this->params["fld_string"];
    }

    public function getInventoryJoinSQL(&$join_array, &$columns_array) {
        $fieldid_alias = $mif_as = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        
        $related_tablename_as = "";
        if(isset($this->params["formodule"]) && $this->params["formodule"]!=""){
            $related_focus = CRMEntity::getInstance($this->params["formodule"]);
            $related_tablename = $related_focus->table_name;
            $related_columnname = $related_focus->table_index;
            
            $related_tablename_as = "$related_tablename" . $fieldid_alias;
            if (!array_key_exists(" $related_tablename AS $related_tablename" . $fieldid_alias . " ", $join_array)) {
                if(!in_array($related_tablename,$columns_array["relatedCurrencyTables"])){
                    $columns_array["relatedCurrencyTables"][] = array("table"=>$related_tablename,"fieldid_alias"=>$fieldid_alias);
                }
                $join_array[" $related_tablename AS $related_tablename" . $fieldid_alias . " "]["joincol"] = "$related_tablename" . $fieldid_alias . ".$related_columnname";
                $join_array[" $related_tablename AS $related_tablename" . $fieldid_alias . " "]["using"] = $this->params["using_aliastablename"].".".$this->params["using_columnname"];
            }
        }

        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $fieldid_alias . " "]["joincol"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".id";

        if (isset($this->params["using_array"]) && !empty($this->params["using_array"]["using"])) {
            //$using_tablename = $this->params["using_array"]["using"]["tablename"];
            //$using_columnname = $this->params["using_array"]["using"]["columnname"];
            $using_tablename = $this->params["using_aliastablename"];
            $using_columnname = $this->params["using_columnname"];
            if ($using_tablename != "" && $using_columnname != "") {
                $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $fieldid_alias . " "]["using"] = $using_tablename . "." . $using_columnname;
            }
        }

        if (!array_key_exists(" vtiger_products AS vtiger_products_inv" . $fieldid_alias . " ", $join_array)) {
            $join_array[" vtiger_products AS vtiger_products_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_products_inv" . $fieldid_alias . ".productid";
            $join_array[" vtiger_products AS vtiger_products_inv" . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".productid";
        }
        if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_products_inv" . $fieldid_alias . " ", $join_array)) {
            $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_products_inv" . $fieldid_alias . ".crmid";
            $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $fieldid_alias . " "]["using"] = "vtiger_products_inv" . $fieldid_alias . ".productid ";
        }

        if (!array_key_exists(" vtiger_service AS vtiger_service_inv" . $fieldid_alias . " ", $join_array)) {
            $join_array[" vtiger_service AS vtiger_service_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_service_inv" . $fieldid_alias . ".serviceid";
            $join_array[" vtiger_service AS vtiger_service_inv" . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".productid";
        }
        if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_services_inv" . $fieldid_alias . " ", $join_array)) {
            $join_array[" vtiger_crmentity AS vtiger_crmentity_services_inv" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_services_inv" . $fieldid_alias . ".crmid";
            $join_array[" vtiger_crmentity AS vtiger_crmentity_services_inv" . $fieldid_alias . " "]["using"] = "vtiger_service_inv" . $fieldid_alias . ".serviceid ";
        }

        $column_tablename = "vtiger_inventoryproductrel" . $fieldid_alias;

        if ($this->params["columnname"] != "" && !in_array($this->params["columnname"], ITS4YouReports::$intentory_fields)) {
            $adb = PEARDatabase::getInstance();
            $uirel_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($this->params["fieldtabid"], $this->params["fieldname"])), 0);
            $related_focus = CRMEntity::getInstance($this->params["fieldmodule"]);
            $related_table_name = $related_focus->table_name;
            $related_table_index = $related_focus->table_index;
            $other_table_as = $old_oth_fieldid = $old_oth_fieldid_as = "";
            foreach ($related_focus->tab_name as $other_table) {
                if ($other_table == "vtiger_crmentity") {
                    $other_table_as = $other_table . "_" . strtolower($this->params["fieldmodule"]) . "_inv";
                } else {
                    $other_table_as = $other_table . "_inv";
                }
                $related_join_array[$other_table_as] = $related_focus->tab_name_index[$other_table];
            }
            $field_uitype = $uirel_row["uitype"];

            $column_tablename = $uirel_row["tablename"];
            if ($column_tablename == "vtiger_crmentity") {
                $old_oth_fieldid = strtolower($this->params["fieldmodule"]) . "_inv";
                $old_oth_fieldid_as = "_" . $old_oth_fieldid;
                $column_tablename_as = $column_tablename . $old_oth_fieldid_as;
            } else {
                $old_oth_fieldid = "inv";
                $old_oth_fieldid_as = "_" . $old_oth_fieldid;
                $column_tablename_as = $column_tablename . $old_oth_fieldid_as;
            }

            $params = Array('fieldid' => $uirel_row["fieldid"],
                'fieldtabid' => $uirel_row["tabid"],
                'field_uitype' => $field_uitype,
                'fieldname' => $uirel_row["fieldname"],
                'columnname' => $uirel_row["columnname"],
                'tablename' => $uirel_row["tablename"],
                'table_index' => $related_join_array,
                'report_primary_table' => $this->params["report_primary_table"],
                'primary_table_name' => $related_focus->table_name,
                'primary_table_index' => $related_focus->table_index,
                'primary_tableid' => "",
                'old_oth_fieldid' => $old_oth_fieldid,
                'fld_string' => $this->params["fld_string"],
            );
            $using_array = getJoinInformation($params);
            $params["using_array"] = $using_array;
            $uifactory = new UIFactory($params);
            $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
        } elseif (in_array($this->params["columnname"], ITS4YouReports::$intentory_fields)) {
            $column_tablename = "vtiger_inventoryproductrel";

            if ($this->params["columnname"] != "") {
                $column_tablename_alias = $this->params["tablename"];
                if ($column_tablename_alias == "vtiger_crmentity") {
                    $column_tablename_alias = $column_tablename . "_" . strtolower($this->params["fieldmodule"]) . "_inv";
                }

                $fld_cond = $this->getInventoryColumnFldCond($this->params["columnname"],$column_tablename_alias,$fieldid_alias,$related_tablename_as);
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
        }

        return "";
    }

    public function getModulesByUitype($tablename, $columnname) {
        $modulename = array();
        return $modulename;
    }

    public function getSelectedFieldCol($selectedfields) {
        $return = array();
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        if ($selectedfields[1] == 'discount') {
            $return = " CASE WHEN (" . "vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount != '') THEN " . "vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount else ROUND((" . "vtiger_inventoryproductrel" . $fieldid_alias . ".listprice * " . "vtiger_inventoryproductrel" . $fieldid_alias . ".quantity * (" . "vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent/100)),3) END ";
        } elseif ($selectedfields[1] == "prodname") {
            $return = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productname IS NOT NULL) THEN vtiger_products_inv" . $fieldid_alias . ".productname ELSE vtiger_service_inv" . $fieldid_alias . ".servicename END ";
        } else {
            $return = "vtiger_inventoryproductrel" . $fieldid_alias . "." . $selectedfields[1];
        }

        return $return;
    }

    // ITS4YOU-CR SlOl 17. 10. 2013 12:00:47
    public function getMoreInfoJoinSQL(&$join_array, &$columns_array) {
        $adb = PEARDatabase::getInstance();
        $primary_obj = CRMEntity::getInstance($this->params["primarymodule"]);
        $secondary_obj = CRMEntity::getInstance($this->params["relatedmodule"]);

        if (method_exists($primary_obj, setRelationTables)) {
            $rel_array = $primary_obj->setRelationTables($this->params["relatedmodule"]);
        } else {
            $rel_array = array("vtiger_crmentityrel" => array("crmid", "relcrmid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
        }
        foreach ($rel_array as $key => $value) {
            $tables[] = $key;
            $fields[] = $value;
        }

        $j_table_name = $tables[0];
        $j_table_index = $fields[0][1];
        $j_table_usingindex = $fields[0][0];
        $s_table_name = $secondary_obj->table_name;
        $s_table_index = $secondary_obj->table_index;

        $join_array[$j_table_name]["joincol"] = $j_table_name . "." . $j_table_usingindex;
        $join_array[$s_table_name]["joincol"] = $s_table_name . "." . $j_table_index;
        $join_array[$s_table_name]["using"] = $j_table_name . "." . $j_table_usingindex;
        if ($this->params["column_tablename"] != $j_table_name) {
            $join_array[$this->params["column_tablename"]]["joincol"] = $this->params["column_tablename"] . "." . $secondary_obj->tab_name_index[$this->params["column_tablename"]];
            $join_array[$this->params["column_tablename"]]["using"] = $j_table_name . "." . $j_table_index;
        }

        $columns_array_value = $this->params["column_tablename"] . "." . $this->params["column_fieldname"];
        $fld_alias = $this->params["column_fieldname"];

        $columns_array[] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
        $columns_array[$this->params["fld_string"]]["fld_alias_hid"] = $fld_alias . "_hid";
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_alias;
        $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
        $columns_array[$fld_alias] = $this->params["fld_string"];
    }

}

?>