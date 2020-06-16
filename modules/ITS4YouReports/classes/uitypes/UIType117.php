<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType117 extends UITypes {

    public function getStJoinSQL(&$join_array, &$columns_array) {
        return;
    }

    public function getJoinSQL(&$join_array, &$columns_array) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if (!array_key_exists(" vtiger_currency_info AS vtiger_currency_info" . $fieldid_alias . " ", $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
            $join_array[" vtiger_currency_info AS vtiger_currency_info" . $fieldid_alias . " "]["joincol"] = "vtiger_currency_info" . $fieldid_alias . ".id";
            /*             * * DO NOT TOUCH THIS CURRENCY JOINNING */
            $join_array[" vtiger_currency_info AS vtiger_currency_info" . $fieldid_alias . " "]["using"] = $this->params["using_array"]["join"]["tablename"] . "." . $this->params["using_array"]["join"]["columnname"];
        }
        $columns_array[] = "vtiger_currency_info" . $fieldid_alias . ".currency_name";
        $columns_array[$this->params["fld_string"]]["fld_alias"] = "currency_name";
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = "vtiger_currency_info" . $fieldid_alias . ".currency_name";
        $columns_array[$this->params["fld_string"]]["fld_cond"] = "vtiger_currency_info" . $fieldid_alias . ".currency_name";
        $columns_array["currency_name"] = $this->params["field_uitype"];
        $columns_array["currency_name"] = $this->params["fld_string"];
    }

    public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array) {
        return;
    }

    public function getInventoryJoinSQL(&$join_array, &$columns_array) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $fieldid_alias . " "]["joincol"] = "vtiger_inventoryproductrel" . $fieldid_alias . ".id";

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
            $columns_array[$this->params["fld_string"]]["fld_alias"] = $column_tablename . "." . $this->params["columnname"] . $fieldid_alias;
            $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
            $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
            $columns_array["uitype_" . $this->params["columnname"] . $fieldid_alias] = $this->params["field_uitype"];
            $columns_array[$this->params["columnname"] . $fieldid_alias] = $this->params["fld_string"];
        }
        return;
    }

    public function getModulesByUitype($tablename, $columnname) {
        $modulename = array();
        return $modulename;
    }

    public function getSelectedFieldCol($selectedfields) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if ($this->params["tablename"] == "vtiger_crmentity") {
            $table_alias = $this->params["tablename"] . "_117" . $fieldid_alias;
            $column_alias = $selectedfields[1];
        } else {
            $table_alias = $this->params["tablename"] . $fieldid_alias;
            $column_alias = $selectedfields[1];
        }

        $return = $table_alias . "." . $column_alias;
        return $return;
    }

}

?>