<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType10 extends UITypes {

    public function getStJoinSQL(&$join_array, &$columns_array) {
        /*
          if(isset($this->params["fieldid"]) && $this->params["fieldid"]!=""){
          $fieldid_alias=$old_oth_fieldid=$mif_as="";
          if($this->params["fieldid"]!=""){
          $fieldid_alias = "_".$this->params["fieldid"];
          }
          if($this->params["old_oth_fieldid"]!=""){
          if($this->params["old_oth_fieldid"]=="mif"){
          $mif_as = "_".$this->params["fieldtabid"];
          }
          $old_oth_fieldid = "_".$this->params["old_oth_fieldid"];
          }
          $fieldid=$this->params["fieldid"];
          $adb = PEARDatabase::getInstance();
          $stjoin_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE fieldid = ? ",array($fieldid)), 0);
          $tablename=$stjoin_row["tablename"];
          $columnname=$stjoin_row["columnname"];
          if(!array_key_exists(" vtiger_salesorder AS vtiger_salesorder".$fieldid_alias." ",$join_array)){
          $join_array[" vtiger_salesorder AS vtiger_salesorder".$fieldid_alias." "]["joincol"] = "vtiger_salesorder".$fieldid_alias.".salesorderid";
          $join_array[" vtiger_salesorder AS vtiger_salesorder".$fieldid_alias." "]["using"] = $tablename.$old_oth_fieldid.$mif_as.".".$columnname;
          }
          if(!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_80".$fieldid_alias." ",$join_array)){
          $join_array[" vtiger_crmentity AS vtiger_crmentity_80".$fieldid_alias." "]["joincol"] = "vtiger_crmentity_80".$fieldid_alias.".crmid";
          $join_array[" vtiger_crmentity AS vtiger_crmentity_80".$fieldid_alias." "]["using"] = "vtiger_salesorder".$fieldid_alias.".salesorderid ";
          }
          }
         */
        return "";
    }

    public function getJoinSQL_UI10(&$join_array, &$columns_array, $uirelmodule) {
        $return = array();
        $field_case = $field_case_hid = "";
        if (vtlib_isModuleActive($uirelmodule)) {
            $adb = PEARDatabase::getInstance();
            $uirel_tabid = getTabid($uirelmodule);
            $uirel_focus = CRMEntity::getInstance($uirelmodule);
            $uirel_table_name = $uirel_focus->table_name;
            $uirel_table_index = $uirel_focus->table_index;

            $fieldid_alias = "";
            //if($this->params["tablename"]!=$this->params["primary_table_name"]){
            if($uirel_table_name!=$this->params["report_primary_table"]){
                // $adb->setDebug(true);
                // $fieldid_alias_row = $adb->fetchByAssoc($adb->pquery("SELECT fieldid FROM vtiger_field WHERE columnname = ? AND tablename = ? ", array($this->params["columnname"], $this->params["tablename"])), 0);
                // $adb->setDebug(false);
                // zakomentovane pretoze robilo chybu pre napr. modul Opportunities related to ModComments  ...
                /*if (!empty($fieldid_alias_row) && $fieldid_alias_row["fieldid"] != "") {
                    $fieldid_alias = "_" . $fieldid_alias_row["fieldid"];
                } else {
                    if ($this->params["fieldid"] != "") {
                        $fieldid_alias = "_" . $this->params["fieldid"];
                    }
                }*/
                if ($this->params["fieldid"] != "") {
                    $fieldid_alias = "_" . $this->params["fieldid"];
                }
            }

            /* $fieldid_alias="";
              if($this->params["fieldid"]!=""){
              $fieldid_alias = "_".$this->params["fieldid"];
              } */
            
            $entity_field_arr = $this->getEntityNameFields($uirelmodule, $uirel_table_name . $fieldid_alias);
            // $entity_fields = " WHEN $table_alias.$uirel_table_index IS NOT NULL THEN " . $entity_field_arr["fieldname"]." ";
            $field_case .= " WHEN " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " IS NOT NULL AND " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " != '' THEN " . $entity_field_arr["fieldname"]." ";
            $field_case_hid .= " WHEN " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " IS NOT NULL AND " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index . " != '' THEN " . $uirel_table_name . $fieldid_alias . "." . $uirel_table_index." ";
            $oth_as = $old_oth_fieldid = $mif_as = "";
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
            $oth_as = "_" . $uirel_tabid;
            //if($this->params["primary_table_name"]!=$uirel_table_name){
            if($this->params["old_oth_fieldid"]=="mif" || $this->params["report_primary_table"]!=$uirel_table_name){
                // ITS4YOU-CR SlOl 10. 5. 2016 9:16:18
                // ak je stlpec v cf tabulke a repotujem iba tento stlpec tak je query chyba
                if(array_key_exists($this->params["tablename"],$this->params["table_index"]) && $this->params["tablename"]!=$this->params["report_primary_table"]){
                    $cf_table_name = $this->params["tablename"];
                    $cf_fieldid_alias = $old_oth_as . $old_oth_fieldid . $mif_as;
                    $cf_table_index = $this->params["table_index"][$cf_table_name];
                    $base_table_name = $this->params["report_primary_table"];
                    $base_table_index = $this->params["table_index"][$base_table_name];
                    if(!array_key_exists(" $cf_table_name AS $cf_table_name" . $cf_fieldid_alias . " ", $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
                        $join_array[" $cf_table_name AS $cf_table_name" . $cf_fieldid_alias . " "]["joincol"] = "$cf_table_name" . $cf_fieldid_alias . ".$cf_table_index";
                        $join_array[" $cf_table_name AS $cf_table_name" . $cf_fieldid_alias . " "]["using"] = $base_table_name . "." . $base_table_index;
                    }
                }
                // ITS4YOU-END 
                if(!array_key_exists(" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " ", $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
                    $join_array[" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " "]["joincol"] = "$uirel_table_name" . $fieldid_alias . ".$uirel_table_index";
                    $join_array[" $uirel_table_name AS $uirel_table_name" . $fieldid_alias . " "]["using"] = $this->params["tablename"] . $old_oth_as . $old_oth_fieldid . $mif_as . "." . $this->params["columnname"];
                }
                if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_10" . $oth_as . $fieldid_alias . " ", $join_array)) {
                    $join_array[" vtiger_crmentity AS vtiger_crmentity_10" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_10" . $oth_as . $fieldid_alias . ".crmid";
                    $join_array[" vtiger_crmentity AS vtiger_crmentity_10" . $oth_as . $fieldid_alias . " "]["using"] = "$uirel_table_name" . $fieldid_alias . ".$uirel_table_index ";
                }
            }
// show("oldo ui 10 joining"," $uirel_table_name AS $uirel_table_name".$fieldid_alias." ",$join_array,"oldo ui 10 joining post");
        }
        $return[] = $field_case;
        $return[] = $field_case_hid;
        return $return;
    }

    public function getJoinSQL(&$join_array, &$columns_array) {
        $adb = PEARDatabase::getInstance();

        $fieldid_alias = "";
        $fieldid_alias_row = $adb->fetchByAssoc($adb->pquery("SELECT fieldid FROM vtiger_field WHERE tablename=? AND fieldname=?", array(vtlib_purify($this->params["tablename"]), vtlib_purify($this->params["columnname"]))), 0);
        if (!empty($fieldid_alias_row) && $fieldid_alias_row["fieldid"] != "") {
            $fieldid_alias = "_" . $fieldid_alias_row["fieldid"];
            $fieldid = $fieldid_alias_row["fieldid"];
        } else {
            if ($this->params["fieldid"] != "") {
                $fieldid_alias = "_" . $this->params["fieldid"];
                $fieldid = $this->params["fieldid"];
            }
        }
        
        $fld_cond = $fld_cond_hid = " CASE ";
        
        if($this->params["tablename"]=="vtiger_modcomments" && $this->params["columnname"]=="userid"){
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
            if (!array_key_exists(" vtiger_users AS vtiger_users" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_users AS vtiger_users" . $fieldid_alias . " "]["joincol"] = "vtiger_users" . $fieldid_alias . ".id";
                $join_array[" vtiger_users AS vtiger_users" . $fieldid_alias . " "]["using"] = " ".$this->params["tablename"]."$old_oth_as" . $old_oth_fieldid. $mif_as . ".userid ";
            }
            if (!array_key_exists(" vtiger_groups AS vtiger_groups" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_groups AS vtiger_groups" . $fieldid_alias . " "]["joincol"] = "vtiger_groups" . $fieldid_alias . ".groupid";
                $join_array[" vtiger_groups AS vtiger_groups" . $fieldid_alias . " "]["using"] = " ".$this->params["tablename"]."$old_oth_as" . $old_oth_fieldid. $mif_as . ".userid ";
            }
            
            // User Names Start
            // first name | last name -> syntax
            $fld_cond .= " WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN CONCAT(vtiger_users$fieldid_alias.first_name,IF(vtiger_users$fieldid_alias.first_name != '' AND vtiger_users$fieldid_alias.first_name IS NOT NULL,' ',''),vtiger_users$fieldid_alias.last_name) WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN vtiger_groups$fieldid_alias.groupname ";
            //$fld_cond_hid .= " WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN vtiger_users$fieldid_alias.id WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN vtiger_groups$fieldid_alias.groupid ";
            $fld_cond_hid .= " WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN '' WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN '' ";
            // last name | first name -> syntax
            // $fld_cond .= " WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN CONCAT(vtiger_users$fieldid_alias.last_name,IF(vtiger_users$fieldid_alias.first_name != '' AND vtiger_users$fieldid_alias.first_name IS NOT NULL,' ',''),vtiger_users$fieldid_alias.first_name) WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN vtiger_groups$fieldid_alias.groupname ";
            // User Names End
            
        }else{
            $ui10_res = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid = ?", array($fieldid));
            while ($ui10_row = $adb->fetch_array($ui10_res)) {
                $uirelmodule = $ui10_row["relmodule"];
                $ui10_array = $this->getJoinSQL_UI10($join_array, $columns_array, $uirelmodule);
                $fld_cond .= $ui10_array[0];
                $fld_cond_hid .= $ui10_array[1];
                // $where_alias_arr[] = $this->getEntityNameSQL($uirelmodule, $fieldid_alias);
                //$fld_cond .= $this->getEntityNameSQL($uirelmodule, $fieldid_alias);;
            }
        }
        
        $field_col_as = $this->params["columnname"] . $fieldid_alias;
        $fld_cond .= " END ";
        $field_case = $fld_cond . " AS " . $field_col_as;
        
        $columns_array[] = $field_case;
        $fld_cond_hid .= " END ";
        $field_case_hid = $fld_cond_hid . " AS " . $field_col_as. "_hid";
        $columns_array[] = $field_case_hid;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $field_col_as;
        $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $field_case;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
        $columns_array["uitype_$field_col_as"] = $this->params["field_uitype"];
        $columns_array[$field_col_as] = $this->params["fld_string"];
        // $columns_array[$this->params["fld_string"]]["fld_table_alias"]=$field_case;
        // $columns_array[$this->params["fld_string"]]["fld_field_alias"]=$field_case;
        // ITS4YOU-CR SlOl 3. 3. 2014 15:54:10
        //$columns_array[$this->params["fld_string"]]["filter_conditions"] = $this->getFilterCondition($where_alias_arr);
    }

    public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array) {
        require_once('modules/ITS4YouReports/ITS4YouReports.php');

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
            $oth_as = "_10_$formodule";
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
                $fieldid_alias = "_" . $this->params["fieldid"];
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
//ITS4YouReports::sshow($join_array);
        }
            if (!array_key_exists(" " . $related_focus->table_name . " AS " . $related_focus->table_name . $oth_as . $fieldid_alias . " ", $join_array)) {
                $join_array[" " . $related_focus->table_name . " AS " . $related_focus->table_name . $oth_as . $fieldid_alias . " "]["joincol"] = $related_focus->table_name . $oth_as . $fieldid_alias . "." . $related_focus->table_index;
                $join_array[" " . $related_focus->table_name . " AS " . $related_focus->table_name . $oth_as . $fieldid_alias . " "]["using"] = $tablename . $old_oth_fieldid . "." . $columnname;
            }

            if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity" . $oth_as . $fieldid_alias . ".crmid";
                $join_array[" vtiger_crmentity AS vtiger_crmentity" . $oth_as . $fieldid_alias . " "]["using"] = $related_focus->table_name . $oth_as .$fieldid_alias . "." . $related_focus->table_index . " ";
            }
        // stjoin content end
        
//ITS4YouReports::sshow($related_table_name);
//ITS4YouReports::sshow($join_array);
        $using_aliastablename = $related_table_name . $oth_as . $fieldid_alias;
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
            'old_oth_as' => $oth_as,
            'old_oth_fieldid' => $fieldid,
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
        require_once('modules/ITS4YouReports/ITS4YouReports.php');
        
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        $formodule = $this->params["formodule"];
        $oth_as = "_" . $formodule;
// show("oldo ui 10 intentory join",$formodule,$this->params);
        $uirelmodule = vtlib_getModuleNameById($formodule);
// show("oldo ui 10 intentory join2",$uirelmodule);
        $related_focus = CRMEntity::getInstance($uirelmodule);
        
        //$ui10_array = $this->getJoinSQL_UI10($join_array, $columns_array, $uirelmodule);
        /*$field_case .= $ui10_array[0];
        $field_case_hid .= $ui10_array[1];*/
// show("oldo ui 10 intentory join000",$join_array,"oldo ui 10 intentory join000 POST");
        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".id";
        $join_array[" vtiger_inventoryproductrel AS vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . " "]["using"] = $related_focus->table_name . $fieldid_alias . "." . $related_focus->table_index;

        $join_array[" vtiger_products AS vtiger_products_inv" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_products_inv" . $oth_as . $fieldid_alias . ".productid";
        $join_array[" vtiger_products AS vtiger_products_inv" . $oth_as . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".productid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_products_inv" . $oth_as . $fieldid_alias . ".crmid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_products_inv" . $oth_as . $fieldid_alias . " "]["using"] = "vtiger_products_inv" . $oth_as . $fieldid_alias . ".productid ";

        $join_array[" vtiger_service AS vtiger_service_inv" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_service_inv" . $oth_as . $fieldid_alias . ".serviceid";
        $join_array[" vtiger_service AS vtiger_service_inv" . $oth_as . $fieldid_alias . " "]["using"] = "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".productid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_service_inv" . $oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_service_inv" . $oth_as . $fieldid_alias . ".crmid";
        $join_array[" vtiger_crmentity AS vtiger_crmentity_service_inv" . $oth_as . $fieldid_alias . " "]["using"] = "vtiger_service_inv" . $oth_as . $fieldid_alias . ".serviceid ";

        $column_tablename = "vtiger_inventoryproductrel";

        if ($this->params["columnname"] != "") {
            $column_tablename_alias = $this->params["tablename"];
            if ($column_tablename_alias == "vtiger_crmentity") {
                $column_tablename_alias = $column_tablename . "_" . strtolower($this->params["fieldmodule"]) . "_inv";
            }

            $fld_cond = $this->getInventoryColumnFldCond($this->params["columnname"],$column_tablename_alias,$oth_as . $fieldid_alias);
            $columns_array_value = $this->getColumnsArrayValue($fld_cond,$oth_as . $fieldid_alias);
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
        
/*
ITS4YouReports::sshow("6-".$this->params["columnname"]);
        if ($this->params["columnname"] == 'discount') {
            $fld_cond = " CASE WHEN (" . "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".discount_amount != '') THEN " . "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".discount_amount else ROUND((" . "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".listprice * " . "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".quantity * (" . "vtiger_inventoryproductrel" . $oth_as . $fieldid_alias . ".discount_percent/100)),3) END ";
            $columns_array_value = $fld_cond . " AS '" . $this->params["columnname"] . $oth_as . $fieldid_alias . "'";
        } elseif ($this->params["columnname"] == "prodname") {
            $fld_cond = " CASE WHEN (vtiger_products_inv" . $oth_as . $fieldid_alias . ".productname IS NOT NULL) THEN vtiger_products_inv" . $oth_as . $fieldid_alias . ".productname ELSE vtiger_service_inv" . $oth_as . $fieldid_alias . ".servicename END ";
            $columns_array_value = $fld_cond . " AS prodname" . $oth_as . $fieldid_alias . " ";
        } else {
            // $fld_cond = "vtiger_inventoryproductrel".$oth_as.$fieldid_alias.".".$this->params["columnname"];
            $fld_cond = $this->params["columnname"];
            $columns_array_value = $fld_cond . " AS " . $this->params["columnname"] . $oth_as . $fieldid_alias;
        }
        $fld_alias = $this->params["columnname"] . $oth_as . $fieldid_alias;
        
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
*/
        return;
    }

    public function getSelectedFieldCol($selectedfields) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if (isset($this->columns_array[$fieldcolname]["fld_cond"]) && $this->columns_array[$fieldcolname]["fld_cond"]!="") {
			$return = $this->columns_array[$fieldcolname]["fld_cond"];
		} else {
	        if ($this->params["tablename"] == "vtiger_crmentity") {
	            $table_alias = $this->params["tablename"] . "_10" . $fieldid_alias;
	            $column_alias = $selectedfields[1];
	        } else {
	            $table_alias = $this->params["tablename"] . $fieldid_alias;
	            $column_alias = $selectedfields[1];
	        }
	        $return = $table_alias . "." . $column_alias;
		}
        return $return;
    }

    public function getModulesByUitype($tablename, $columnname) {
        $adb = PEARDatabase::getInstance();
        $modulename = array();
        if ($tablename != "" && $columnname != "") {
            $ui10_res = $adb->pquery("SELECT fieldid, vtiger_tab.name AS relmodule FROM vtiger_field INNER JOIN vtiger_tab USING(tabid) WHERE tablename='" . vtlib_purify($tablename) . "' AND columnname='" . vtlib_purify($columnname) . "'", array());
            if ($adb->num_rows($ui10_res)) {
                while ($ui10_row = $adb->fetch_array($ui10_res)) {
                    $uirelmodule = $ui10_row["relmodule"];
                    if (vtlib_isModuleActive($uirelmodule)) {
                        $modulename[] = $uirelmodule;
                    }
                }
            }
        }
        return $modulename;
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

    // ITS4YOU-CR SlOl 3. 3. 2014 15:35:57
    function getEntityNameSQL($module, $fieldid_alias = "") {
        $entity_fields = "";
        $adb = PEARDatabase::getInstance();
        if (vtlib_isModuleActive($module)) {
            $uirel_tabid = getTabid($module);
            $uirel_focus = CRMEntity::getInstance($module);
            $uirel_table_name = $uirel_focus->table_name;
            $uirel_table_index = $uirel_focus->table_index;
            $table_alias = $uirel_table_name . $fieldid_alias;
            $entity_field_arr = $this->getEntityNameFields($module, $table_alias);
            $entity_fields = " WHEN $table_alias.$uirel_table_index IS NOT NULL THEN " . $entity_field_arr["fieldname"]." ";
        }
        return $entity_fields;
    }

    // ITS4YOU-CR SlOl 3. 3. 2014 15:54:51
    function getFilterCondition($where_alias_arr) {
        $return = "";
        if (!empty($where_alias_arr)) {
            $return = " CASE ";
            foreach ($where_alias_arr as $key => $when) {
                $return .= " $when ";
            }
            $return .= " END ";
        }
        return $return;
    }

    // ITS4YOU-END 3. 3. 2014 15:22:09 
}

?>