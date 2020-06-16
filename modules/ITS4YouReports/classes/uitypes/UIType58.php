<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType58 extends UITypes {
    protected $relModuleName = 'Campaigns';
    protected $oth_as = '_58';

    public function getStJoinSQL(&$join_array, &$columns_array) {
        if (isset($this->params["fieldid"]) && $this->params["fieldid"] != "") {
            $fieldid_alias = $old_oth_fieldid = $mif_as = "";
            if ($this->params["fieldid"] != "") {
                $fieldid_alias = "_" . $this->params["fieldid"];
            }
            if ($this->params["old_oth_as"] != "") {
                $old_oth_as = $this->params["old_oth_as"];
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
            if (!array_key_exists(" vtiger_campaign AS vtiger_campaign" . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_campaign AS vtiger_campaign" . $fieldid_alias . " "]["joincol"] = "vtiger_campaign" . $fieldid_alias . ".campaignid";
                $join_array[" vtiger_campaign AS vtiger_campaign" . $fieldid_alias . " "]["using"] = $tablename . $old_oth_as.$old_oth_fieldid . $mif_as . "." . $columnname;
            }
            if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity".$this->oth_as . $fieldid_alias . " ", $join_array)) {
                $join_array[" vtiger_crmentity AS vtiger_crmentity".$this->oth_as . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity".$this->oth_as . $fieldid_alias . ".crmid";
                $join_array[" vtiger_crmentity AS vtiger_crmentity".$this->oth_as . $fieldid_alias . " "]["using"] = "vtiger_campaign" . $fieldid_alias . ".campaignid ";
            }
        }
    }

    public function getJoinSQL(&$join_array, &$columns_array) {
        $this->getStJoinSQL($join_array, $columns_array);

        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        $oth_as = "";
        if ($this->params["tablename"] == "vtiger_crmentity") {
            $oth_as = $this->oth_as;
        }
        $join_tablename_alias = $this->params["join_tablename_alias"] = $this->params["tablename"] . $oth_as . $fieldid_alias;
        $join_alias = " " . $this->params["tablename"] . " AS " . $join_tablename_alias . " ";
        if ($this->params["primary_table_name"]!=$join_tablename_alias && !array_key_exists($join_alias, $join_array) && !in_array($this->params["old_oth_fieldid"], array("inv"))) {
            if ($this->params["tablename"] != $this->params["report_primary_table"] && $this->params["tablename"] != $this->params["primary_table_name"]) {
                // ITS4YOU-CR SlOl 8. 2. 2016 7:23:04
                if(is_array($this->params["table_index"])){
                    $table_index = $this->params["table_index"][$this->params["tablename"]];
                }else{
                    $table_index = $this->params["table_index"];
                }
                // ITS4YOU-UP SlOl 8. 2. 2016 7:23:09
                $join_array[$join_alias]["joincol"] = $join_tablename_alias . "." . $table_index;
                // ITS4YOU-END
                if ($this->params["using_aliastablename"] != "" && $this->params["using_columnname"] != "") {
                    $join_array[$join_alias]["using"] = $this->params["using_aliastablename"] . "." . $this->params["using_columnname"];
                }elseif(isset($this->params["old_oth_as"]) && $this->params["old_oth_as"]!=""){
                    $join_array[$join_alias]["using"] = $this->params["using_array"]["join"]["tablename"] . "." . $this->params["using_array"]["join"]["columnname"];
                }
            }
        }

        if ($this->params["columnname"] == "campaignid") {
            $this->params["join_tablename_alias"] = "vtiger_campaign" . $fieldid_alias;
            $uifactory = new UIFactory($this->params);
            $test_display = $uifactory->getDisplaySQL($this->relModuleName, $join_array, $columns_array);
            $columns_array_value = $test_display["display"];
            $fld_alias = $test_display["fld_string"];
            $fld_hrefid = $test_display["hrefid"];
            $fld_cond = $test_display["fld_cond"];
        } else {
            $fld_cond = $join_tablename_alias . "." . $this->params["columnname"];
            $columns_array_value = $fld_cond . " AS " . $this->params["columnname"] . $fieldid_alias;
            $fld_alias = $this->params["columnname"] . $fieldid_alias;
            $fld_hrefid = "";
        }
        $columns_array[] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
$columns_array[$this->params["fld_string"]]["fld_alias_hid"] = $fld_alias . "_hid";
$columns_array[$this->params["fld_string"]]["fld_sql_str"] = $columns_array_value;
        $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
        $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
        $columns_array[$fld_alias] = $this->params["fld_string"];
        if ($fld_hrefid != "") {
            $columns_array[] = $fld_hrefid;
        }
    }
    
    public function getSelectedFieldCol($selectedfields) {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if ($selectedfields[1] == "campaignid") {
            $return = "vtiger_campaign" . $fieldid_alias . "." . $selectedfields[1];
        } elseif ($selectedfields[0] == "vtiger_crmentity") {
            $return = $selectedfields[0] . $this->oth_as . $fieldid_alias . "." . $selectedfields[1];
        } else {
            $return = $selectedfields[0] . $fieldid_alias . "." . $selectedfields[1];
        }

        return $return;
    }

}

?>