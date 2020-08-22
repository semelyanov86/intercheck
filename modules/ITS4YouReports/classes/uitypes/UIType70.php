<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
 // ITS4YOU-CR SlOl 3. 2. 2016 12:36:18 CREATED FOR IT&M Company 

class UIType70 extends UITypes {
    
    public function getJoinSQL(&$join_array, &$columns_array) {
        $old_oth_as = $old_oth_fieldid = $mif_as = "";
        if ($this->params["old_oth_as"] != "") {
            $old_oth_as = $this->params["old_oth_as"];
        }
        if ($this->params["old_oth_fieldid"] != "") {
            if ($this->params["old_oth_fieldid"] == "mif") {
                $mif_as = "_" . $this->params["fieldtabid"];
            }
            $old_oth_fieldid = "_" . $this->params["old_oth_fieldid"];
        }
        
        $join_tablename = " " . $this->params["using_array"]["join"]["tablename"];
        $clear_tablename = $this->params["tablename"];
        $join_columnname = $this->params["using_array"]["join"]["columnname"];

        $join_tablename_alias = $this->params["join_tablename_alias"] = $clear_tablename . $old_oth_as . $old_oth_fieldid . $mif_as;
        $join_alias = " " . $clear_tablename . " AS " . $join_tablename_alias . " ";

        $fld_alias = $this->params["columnname"] . $old_oth_as . $old_oth_fieldid . $mif_as;

        // ITS4YOU-CR SlOl 2. 11. 2015 13:48:13
        $typeofdata = 'V';
        if(isset($this->params["fld_string"]) && $this->params["fld_string"]!=""){
            $fld_arr = explode(":",$this->params["fld_string"]);
            if(isset($fld_arr[4]) && $fld_arr[4]!=""){
                $typeofdata = $fld_arr[4];
            }
        }
        // ITS4YOU-END
        $fld_cond = 'DATE_FORMAT('.$join_tablename_alias . "." . $this->params["columnname"].',"%Y-%m-%d")';
        $columns_array_value = $fld_cond . " AS " . $this->params["columnname"] . $old_oth_fieldid;

        $columns_array[] = $join_tablename_alias . "." . $this->params["columnname"] . ' AS ' . $this->params["columnname"] . $old_oth_as . $old_oth_fieldid . $mif_as;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
        $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
        $columns_array[$fld_alias] = $this->params["fld_string"];
        if ($fld_hrefid != "") {
            $columns_array[] = $fld_hrefid;
        }
//ITS4YouReports::sshow($columns_array);exit;
        
    }

    public function getSelectedFieldCol($selectedfields) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        if ($selectedfields[0] == "vtiger_crmentity") {
            $return = $selectedfields[0] . $this->oth_as . $fieldid_alias . "." . $selectedfields[1];
        } else {
            $return = $selectedfields[0] . $fieldid_alias . "." . $selectedfields[1];
        }

        return $return;
    }

}

?>