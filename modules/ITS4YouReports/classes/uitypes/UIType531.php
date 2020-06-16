<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType531 extends UITypes {
    protected $id_cols_array = array("user_name", "modifiedby", "smownerid", "smcreatorid");
    protected $relModuleName = 'Users';
    protected $oth_as = '_531';
    
    public function getStJoinSQL(&$join_array, &$columns_array) {
        /*
        if (isset($this->params["fieldid"]) && $this->params["fieldid"] != "") {
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
            $fieldid = $this->params["fieldid"];
            $adb = PEARDatabase::getInstance();
            $stjoin_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE fieldid = ? ", array($fieldid)), 0);
            $tablename = $stjoin_row["tablename"];
            $columnname = $stjoin_row["columnname"];
            if (!array_key_exists(" vtiger_salesorder AS vtiger_salesorder" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_salesorder AS vtiger_salesorder" . $fieldid_alias . " "]["joincol"] = "vtiger_salesorder" . $fieldid_alias . ".salesorderid";
                $join_array[" vtiger_salesorder AS vtiger_salesorder" . $fieldid_alias . " "]["using"] = $tablename . $old_oth_fieldid . $mif_as . "." . $columnname;
            }
            if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_80" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_crmentity AS vtiger_crmentity_80" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_80" . $fieldid_alias . ".crmid";
                $join_array[" vtiger_crmentity AS vtiger_crmentity_80" . $fieldid_alias . " "]["using"] = "vtiger_salesorder" . $fieldid_alias . ".salesorderid ";
            }
        }
        */
    }

    public function getJoinSQL(&$join_array, &$columns_array) {
        if (isset($this->params["fieldid"]) && $this->params["fieldid"] != "") {
            $fieldid_alias = $old_oth_fieldid = $mif_as = "";
            if ($this->params["fieldid"] != "") {
                $fieldid_alias = "_" . $this->params["fieldid"];
            }
            if ($this->params["old_oth_as"] != "") {
                //$old_oth_as = "_" . $this->params["old_oth_as"];
                $old_oth_as = $this->params["old_oth_as"];
            }
            if ($this->params["old_oth_fieldid"] != "") {
                if ($this->params["old_oth_fieldid"] == "mif") {
                    $mif_as = "_" . $this->params["fieldtabid"];
                }
                $old_oth_fieldid = "_" . $this->params["old_oth_fieldid"];
            }
            
            // ITS4YOU-CR SlOl | 25.8.2015 11:33 
            $fieldid = $this->params["fieldid"];
            $adb = PEARDatabase::getInstance();
            $stjoin_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE fieldid = ? ", array($fieldid)), 0);
            $tablename = $stjoin_row["tablename"];
            $columnname = $stjoin_row["columnname"];
            if($this->params["primary_table_name"]!=$tablename && $tablename!="vtiger_crmentity"){
                if (!array_key_exists(" $tablename AS $tablename" . $fieldid_alias . " ", $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
                    $primary_index = $this->params["table_index"][$this->params["primary_table_name"]];
                    $rel_index = $this->params["table_index"][$tablename];
                    
                    if($this->params["primary_table_name"]!=$this->params["report_primary_table"]){
                        $primary_table_name = $this->params["primary_table_name"]. $old_oth_as . $old_oth_fieldid. $mif_as;
                        $tablename_alias = $tablename . $old_oth_as . $old_oth_fieldid. $mif_as;
                    }else{
                        $primary_table_name = $this->params["primary_table_name"];
                        $tablename_alias = $tablename;
                    }
                    $join_array[" $tablename AS $tablename_alias" . " "]["joincol"] = $primary_table_name.".".$primary_index;
                    $join_array[" $tablename AS $tablename_alias" . " "]["using"] = $tablename_alias . "." . $rel_index;
                }   
            }
            // ITS4YOU-END
            // ITS4YOU-UP SlOl | 25.8.2015 11:33 
            if (in_array($this->params["columnname"], $this->id_cols_array) == true) {
                $join_col = "vtiger_users" . $fieldid_alias . ".id";
                $using_col = " vtiger_crmentity$old_oth_as" . $old_oth_fieldid. $mif_as . ".smownerid ";
            }else{
                $join_col = "vtiger_users" . $fieldid_alias . ".id";
                if($this->params["primary_table_name"]!=$this->params["report_primary_table"]){
                    $using_col = $this->params["tablename"].$old_oth_as . $old_oth_fieldid. $mif_as.".".$this->params["columnname"];
                }else{
                    $using_col = $this->params["tablename"].".".$this->params["columnname"];
                }
            }
            
            $userid_fld = $join_col;
            if (!array_key_exists(" vtiger_users AS vtiger_users" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_users AS vtiger_users" . $fieldid_alias . " "]["joincol"] = $join_col;
                $join_array[" vtiger_users AS vtiger_users" . $fieldid_alias . " "]["using"] = $using_col;
            }
            
            if (in_array($this->params["columnname"], $this->id_cols_array) == true) {
                $join_col = "vtiger_groups" . $fieldid_alias . ".groupid";
                $using_col = " vtiger_crmentity$old_oth_as" . $old_oth_fieldid. $mif_as . ".smownerid ";
            }else{
                $join_col = "vtiger_groups" . $fieldid_alias . ".groupid";
                if($this->params["primary_table_name"]!=$this->params["report_primary_table"]){
                    $using_col = $this->params["tablename"].$old_oth_as . $old_oth_fieldid. $mif_as.".".$this->params["columnname"];
                }else{
                    $using_col = $this->params["tablename"].".".$this->params["columnname"];
                }
                
            }
            
            if (!array_key_exists(" vtiger_groups AS vtiger_groups" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_groups AS vtiger_groups" . $fieldid_alias . " "]["joincol"] = $join_col;
                $join_array[" vtiger_groups AS vtiger_groups" . $fieldid_alias . " "]["using"] = $using_col;
            }
            // ITS4YOU-END
        }
        
        $this->params["join_tablename_alias"] = "vtiger_users" . $fieldid_alias;
        $uifactory = new UIFactory($this->params);
        $test_display = $uifactory->getDisplaySQL($this->relModuleName, $join_array, $columns_array);
        $columns_array_value = $test_display["display"];
        $fld_alias = $test_display["fld_string"];
        $fld_cond = $test_display["fld_cond"];

        $columns_array[] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["userid_fld"] = $userid_fld;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
        // $columns_array[$this->params["fld_string"]]["fld_alias_hid"]=$fld_alias."_hid";
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
        $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
        $columns_array[$fld_alias] = $this->params["fld_string"];
        if ($fld_hrefid != "") {
            $columns_array[] = $fld_hrefid;
        }
//show("<font color='green'>fielduitypeJOJO531",$join_array,$this->params["field_uitype"],$columns_array,"</font>");		
    }

    /*public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array) {
        $related_focus = CRMEntity::getInstance($this->relModuleName);

        $params_fieldname = $this->params["fieldname"];
        // first join to vtiger module table 
        $this->params["fieldname"] = $related_focus->tab_name_index[$this->params["tablename"]];

        $this->getStJoinSQL(&$join_array, &$columns_array);

        $r_tabid = getTabid($this->relModuleName);
        $adb = PEARDatabase::getInstance();
        $uirel_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($r_tabid, $params_fieldname)), 0);

        $related_table_name = $related_focus->table_name;
        $related_table_index = $related_focus->table_index;
        foreach ($related_focus->tab_name as $other_table) {
            $related_join_array[$other_table] = $related_focus->tab_name_index[$other_table];
        }
        $field_uitype = $uirel_row["uitype"];
        $fieldid = $this->params["fieldid"];
        $oth_as = "";
        if ($uirel_row["tablename"] == "vtiger_crmentity") {
            $oth_as = $this->oth_as;
            $related_table_name = $uirel_row["tablename"];
            $related_table_index = $uirel_row["columnname"];
        }
        $using_aliastablename = $related_table_name . $oth_as . $fieldid;
        $using_columnname = $related_table_index;

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
            'primary_tableid' => $r_tabid,
            'using_aliastablename' => $using_array["u_tablename"],
            'using_columnname' => $using_array["u_tableindex"],
            'old_oth_as' => $oth_as,
            'old_oth_fieldid' => $fieldid,
            'fld_string' => $this->params["fld_string"],
        );
        $using_array = getJoinInformation($params);
        $params["using_array"] = $using_array;
        $uifactory = new UIFactory($params);
//show("<font color='green'>fielduitype531_IN_P_".$field_uitype,$related_join_array,$params["using_array"],"</font>");
        $uifactory->getJoinSQL($field_uitype, &$join_array, &$columns_array);
    }*/

    /*public function getInventoryJoinSQL(&$join_array, &$columns_array) {
        return;
    }*/

    /*public function getModulesByUitype($tablename, $columnname) {
        $modulename[] = $this->relModuleName;
        return $modulename;
    }*/

    public function getSelectedFieldCol($selectedfields) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        if (in_array($selectedfields[1], $this->id_cols_array)) {
            $return = "vtiger_users" . $fieldid_alias . "." . $selectedfields[1];
        } elseif ($selectedfields[0] == "vtiger_crmentity") {
            $return = $selectedfields[0] . $this->oth_as . $fieldid_alias . "." . $selectedfields[1];
        } else {
            $return = $selectedfields[0] . $fieldid_alias . "." . $selectedfields[1];
        }

        return $return;
    }

}

?>