<?php

require_once("modules/ITS4YouReports/classes/uitypes/UITypes.php");
require_once('modules/ITS4YouReports/classes/UIUtils.php');
require_once('modules/ITS4YouReports/ITS4YouReports.php');

class UIFactory {

    protected $params = Array();

    function __construct($params) {
        $this->params = $params;
    }

    public function getJoinSQL($uitype, &$join_array, &$columns_array) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getJoinSQL($join_array, $columns_array);
        } else {
            return false;
        }
    }

    public function getInventoryJoinSQL($uitype, &$join_array, &$columns_array) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getInventoryJoinSQL($join_array, $columns_array);
        } else {
            return false;
        }
    }

    public function getModulesByUitype($uitype, $tablename, $columnname) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getModulesByUitype($tablename, $columnname);
        } else {
            return false;
        }
    }

    public function getMoreInfoJoinSQL($uitype, &$join_array, &$columns_array) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getMoreInfoJoinSQL($join_array, $columns_array);
        } else {
            return false;
        }
    }

    public function getAdvMoreInfoJoinSQL($uitype, &$join_array, &$columns_array) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getAdvMoreInfoJoinSQL($join_array, $columns_array);
        } else {
            return false;
        }
    }

    // ITS4YOU-CR SlOl 12. 11. 2013 14:50:11
    public function getJoinSQLbyFieldRelation($uitype, &$join_array, &$columns_array) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getJoinSQLbyFieldRelation($join_array, $columns_array);
        } else {
            return false;
        }
    }

    public function getSelectedFieldCol($uitype, $selectedfields) {
        $child = $this->getChildByUIType($uitype);
        if ($child) {
            return $child->getSelectedFieldCol($selectedfields);
        } else {
            return false;
        }
    }

    // ITS4YOU-END 12. 11. 2013 14:50:12 
    private function getChildByUIType($uitype) {
        $file_name = "modules/ITS4YouReports/classes/uitypes/UIType$uitype.php";
        if (file_exists($file_name)) {
            require_once($file_name);
            $class_name = 'UIType' . $uitype;
            return new $class_name($this->params);
        } else {
            require_once("modules/ITS4YouReports/classes/uitypes/UITypeDefault.php");
            return new UITypeDefault($this->params);
        }
    }

    public function getDisplaySQL($module, &$join_array, &$columns_array) {
        $entity_field_info = getEntityFieldNames($module);

        if (in_array($this->params["field_uitype"], array("52", "53", "531", "77"))) {
            $join_tablename_alias = $this->params["join_tablename_alias"];
        }/* elseif(isset($this->params["old_oth_fieldid"]) && $this->params["old_oth_fieldid"]!="" && $this->params["old_oth_fieldid"]!="mif"){
          $join_tablename_alias=$this->params["using_array"]["join"]["tablename"];
          } */ else {
            $join_tablename_alias = $this->params["join_tablename_alias"];
        }

        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }

        $fld_cond = "";
        if (in_array($this->params["field_uitype"], array("52", "53", "531", "77"))) {
            // User Names Start
            // first name | last name -> syntax
            $fld_cond .= "CASE WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN CONCAT(vtiger_users$fieldid_alias.first_name,IF(vtiger_users$fieldid_alias.first_name != '' AND vtiger_users$fieldid_alias.first_name IS NOT NULL,' ',''),vtiger_users$fieldid_alias.last_name) WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN vtiger_groups$fieldid_alias.groupname END ";
            // last name | first name -> syntax
            // $fld_cond .= "CASE WHEN vtiger_users$fieldid_alias.id IS NOT NULL THEN CONCAT(vtiger_users$fieldid_alias.last_name,IF(vtiger_users$fieldid_alias.first_name != '' AND vtiger_users$fieldid_alias.first_name IS NOT NULL,' ',''),vtiger_users$fieldid_alias.first_name) WHEN vtiger_groups$fieldid_alias.groupid IS NOT NULL THEN vtiger_groups$fieldid_alias.groupname END ";
            // User Names End
            $display = $fld_cond . " AS " . $this->params["columnname"] . $fieldid_alias;
            $fld_string_h = "";
        } else {
            if (is_array($entity_field_info["fieldname"])) {
                $fld_cond .= "CONCAT(";
                $wi = 0;
                foreach ($entity_field_info["fieldname"] as $fieldvalue) {
                    $fld_cond .= $join_tablename_alias . "." . $fieldvalue;
                    if ($wi == 0)
                        $fld_cond .= ",' ',";
                    $wi++;
                }
                $fld_cond .= ") ";
                $display = $fld_cond . " AS " . $this->params["columnname"] . $fieldid_alias;
            }else {
                $fld_cond .= $join_tablename_alias . "." . $entity_field_info["fieldname"];
                $display = $fld_cond . " AS " . $this->params["columnname"] . $fieldid_alias;
            }
            // $fld_string_h = $join_tablename_alias.".";
            $fld_string_h = "";
        }

        $display_arr["display"] = $display;
        $display_arr["fld_string"] = $fld_string_h . $this->params["columnname"] . $fieldid_alias;
        $hrefid = $join_tablename_alias . "." . $entity_field_info["entityidfield"] . " AS " . $this->params["columnname"] . $fieldid_alias . "_hid";
        $display_arr["hrefid"] = $hrefid;
        $display_arr["fld_cond"] = $fld_cond;
        return $display_arr;
    }

    public function getRelationTables(&$join_array, &$columns_array) {
        $adb = PEARDatabase::getInstance();
        $module = vtlib_getModuleNameById($this->params["primary_tableid"]);
        $primary_obj = CRMEntity::getInstance($module);
        $secmodule = vtlib_getModuleNameById($this->params["fieldtabid"]);
        $secondary_obj = CRMEntity::getInstance($secmodule);
        $related_join_array = $secondary_obj->tab_name_index;

        $field_tablename = trim($this->params["tablename"], "_MIF");
        $fieldid_alias = "";
        if ($this->params["fieldtabid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldtabid"];
        }

        /* $ui10_query = $adb->pquery("SELECT vtiger_field.fieldid AS fieldid, vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE vtiger_fieldmodulerel.module=? AND vtiger_fieldmodulerel.relmodule=?",array($module,$secmodule));
          if($adb->num_rows($ui10_query)>0){
          $ui10_tablename = $adb->query_result($ui10_query,0,'tablename');
          $ui10_columnname = $adb->query_result($ui10_query,0,'columnname');
          $ui10_tabid = $adb->query_result($ui10_query,0,'tabid');
          $ui10_fieldid = $adb->query_result($ui10_query,0,'fieldid');

          if($primary_obj->table_name == $ui10_tablename){
          $reltables = array($ui10_tablename=>array("".$primary_obj->table_index."","$ui10_columnname"));
          } else if($secondary_obj->table_name == $ui10_tablename){
          $reltables = array($ui10_tablename=>array("$ui10_columnname","".$secondary_obj->table_index.""),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
          } else {
          if(isset($secondary_obj->tab_name_index[$ui10_tablename])){
          $rel_field = $secondary_obj->tab_name_index[$ui10_tablename];
          $reltables = array($ui10_tablename=>array("$ui10_columnname","$rel_field"),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
          } else {
          $rel_field = $primary_obj->tab_name_index[$ui10_tablename];
          $reltables = array($ui10_tablename=>array("$rel_field","$ui10_columnname"),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
          }
          }
          }else {
         */ if (method_exists($primary_obj, setRelationTables)) {
            $reltables = $primary_obj->setRelationTables($secmodule);
        } else {
            $reltables = '';
        }

        //}
        // ITS4YOU-UP SlOl 4. 12. 2014 13:56:43
        if ('Potentials' === $module && 'Contacts' === $this->params["fieldmodule"]) {
            $tab = array("vtiger_contpotentialrel" => array("potentialid", "contactid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
            // ITS4YOU-UP SlOl 4. 12. 2014 13:56:43
        } elseif ($this->params["fieldmodule"] == "ModComments") {
            $tab = array("vtiger_modcomments" => array("related_to", $primary_obj->table_index), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
            // ITS4YOU-END 4. 12. 2014 13:56:45
        } elseif (in_array($this->params["columnname"], array("campaignrelstatus", "access_count"))) {
            $tab = [];
        } elseif (is_array($reltables) && !empty($reltables)) {
            $tab = $reltables;
        } elseif ($this->params["primary_tableid"] == "9") {
            $tab = array("vtiger_seactivityrel" => array("activityid", "crmid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
        } else {
			$rl_params = array($this->params['primary_tableid'], $secmodule);
			$get_list_name = $adb->pquery('SELECT name 
											FROM vtiger_relatedlists 
											WHERE tabid = ? AND related_tabid = (select tabid from vtiger_tab where name=?)
											',$rl_params);
			$list_name = $adb->query_result($get_list_name,0,'name');
			if ('get_dependents_list' === $list_name) {
				$tab = array();
			} else {
		    	$tab = array("vtiger_crmentityrel" => array("crmid", "relcrmid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
		  	}
        }
        if (!empty($tab)){
            foreach ($tab as $key => $value) {
                $tables[] = $key;
                $fields[] = $value;
            }
            
            $table_name = $secondary_obj->table_name;
            $column_name = $secondary_obj->table_index;
            $pritablename = $tables[0];
            $sectablename = $tables[1];
            $prifieldname = $fields[0][0];
            $secfieldname = $fields[0][1];
            /*if($table_name==$pritablename){
                $sectablename = $table_name;
                $secfieldname = $column_name;
            }*/
            $condvalue_t = "";
            if (!empty($tables[1]) && !empty($fields[1])) {
                $condvalue_t = $tables[1] . "." . $fields[1];
                $condvalue_t_table = $tables[1];
                $condvalue_t_column = $fields[1];
                $condition_t = $pritablename . "_mif" . $fieldid_alias . ".$prifieldname";
                //ITS4YouReports::sshow("MIF_EJ1");
            } else {
                $condvalue_t = $table_name . "." . $column_name;
                $condvalue_t_table = $table_name;
                $condvalue_t_column = $column_name;
                $condition_t = $pritablename . "_mif" . $fieldid_alias . ".$secfieldname";
                //ITS4YouReports::sshow("MIF_EJ2");
            }

            if($module=="Products" && $secmodule=="Products"){
                $tables[] = array("vtiger_products","vtiger_seproductsrel");
                $fields[0] = array("productid","productid");
                
                $join_array[" vtiger_seproductsrel AS vtiger_seproductsrel_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_seproductsrel_mif$fieldid_alias.productid";
                $join_array[" vtiger_seproductsrel AS vtiger_seproductsrel_mif" . $fieldid_alias . " "]["using"] = $condvalue_t;

                $join_array[" vtiger_products AS vtiger_products_mif" . $fieldid_alias . " "]["joincol"] = $pritablename . "_mif" . $fieldid_alias.".productid";
                $join_array[" vtiger_products AS vtiger_products_mif" . $fieldid_alias . " "]["using"] = "vtiger_seproductsrel_mif$fieldid_alias.crmid";
            }elseif ($pritablename == "vtiger_crmentityrel") {
                // crmentityrel join
                $join_array[" " . $pritablename . " AS " . $pritablename . "_mif" . $fieldid_alias . " "]["joincol"] = "";
                $join_array[" " . $pritablename . " AS " . $pritablename . "_mif" . $fieldid_alias . " "]["using"] = "(" . $primary_obj->table_name . "." . $primary_obj->table_index . " = " . $pritablename . "_mif" . $fieldid_alias . ".crmid AND module='$module' AND relmodule='$secmodule' ) OR (" . $primary_obj->table_name . "." . $primary_obj->table_index . " = " . $pritablename . "_mif" . $fieldid_alias . ".relcrmid AND module='$secmodule' AND relmodule='$module')";
                // secondary obj table join
                $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["joincol"] = "";
                $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["using"] = "(" . $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->table_index . " = " . $pritablename . "_mif" . $fieldid_alias . ".crmid AND module='$secmodule' AND relmodule='$module') OR (" . $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->table_index . " = " . $pritablename . "_mif" . $fieldid_alias . ".relcrmid AND module='$module' AND relmodule='$secmodule')";
                // secondary obj crmentiry deleted=0 join for primary tables
                if ($table_name == $secondary_obj->table_name) {
                    if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_mif" . $fieldid_alias . ".crmid";
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name] . " ";
                    }
                }
                if ($field_tablename != $secondary_obj->table_name && isset($secondary_obj->tab_name_index[$field_tablename])) {
                    if (!array_key_exists(" $field_tablename AS $field_tablename"."_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" $field_tablename AS $field_tablename"."_mif" . $fieldid_alias . " "]["joincol"] = $field_tablename."_mif".$fieldid_alias . ".".$secondary_obj->tab_name_index[$field_tablename];
                        $join_array[" $field_tablename AS $field_tablename"."_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name];
                    }
                }
            } elseif($this->params["fieldmodule"]=="Calendar" && vtlib_getModuleNameById($this->params["primary_tableid"])=="Contacts") {
                $join_array[" vtiger_cntactivityrel AS vtiger_cntactivityrel_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_cntactivityrel_mif$fieldid_alias.contactid";
                $join_array[" vtiger_cntactivityrel AS vtiger_cntactivityrel_mif" . $fieldid_alias . " "]["using"] = $condvalue_t;
                $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_seactivityrel_mif$fieldid_alias.crmid";
                $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel_mif" . $fieldid_alias . " "]["using"] = $condvalue_t;
                if ($pritablename == $secondary_obj->table_name) {
                    if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_mif" . $fieldid_alias . ".crmid";
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["using"] = $pritablename . "_mif" . $fieldid_alias . ".$column_name ";
                    }
                }
                if ($pritablename != $table_name) {
                    //$join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["joincol"] = $table_name . "_mif" . $fieldid_alias . "." . $column_name;
                    $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["joincol"] = "";
                    $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["using"] = "(vtiger_activity_mif$fieldid_alias.activityid = vtiger_cntactivityrel_mif$fieldid_alias.activityid OR vtiger_activity_mif$fieldid_alias.activityid = vtiger_seactivityrel_mif$fieldid_alias.activityid)";
                }
                if ($table_name == $secondary_obj->table_name) {
                    if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_mif" . $fieldid_alias . ".crmid";
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name] . " ";
                    }
                }
                if ($field_tablename != $table_name) {
                    if (!array_key_exists(" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " "]["joincol"] = $field_tablename."_mif" . $fieldid_alias . ".".$secondary_obj->tab_name_index[$field_tablename];
                        $join_array[" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name];
                    }
                }           
            } else {
                $join_array[" " . $pritablename . " AS " . $pritablename . "_mif" . $fieldid_alias . " "]["joincol"] = $condition_t;
                $join_array[" " . $pritablename . " AS " . $pritablename . "_mif" . $fieldid_alias . " "]["using"] = $condvalue_t;
                if ($pritablename == $secondary_obj->table_name) {
                    if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_mif" . $fieldid_alias . ".crmid";
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["using"] = $pritablename . "_mif" . $fieldid_alias . ".$column_name ";
                    }
                }
                if ($pritablename != $table_name) {
                    $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["joincol"] = $table_name . "_mif" . $fieldid_alias . "." . $column_name;
                    $join_array[" " . $table_name . " AS " . $table_name . "_mif" . $fieldid_alias . " "]["using"] = $pritablename . "_mif" . $fieldid_alias . "." . $secfieldname;
                }
                if ($table_name == $secondary_obj->table_name) {
                    if (!array_key_exists(" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["joincol"] = "vtiger_crmentity_mif" . $fieldid_alias . ".crmid";
                        $join_array[" vtiger_crmentity AS vtiger_crmentity_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name] . " ";
                    }
                }
                if ($field_tablename != $table_name) {
                    if (!array_key_exists(" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " ", $join_array)) {
                        $join_array[" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " "]["joincol"] = $field_tablename."_mif" . $fieldid_alias . ".".$secondary_obj->tab_name_index[$field_tablename];
                        $join_array[" $field_tablename AS ".$field_tablename."_mif" . $fieldid_alias . " "]["using"] = $table_name . "_mif" . $fieldid_alias . "." . $secondary_obj->tab_name_index[$table_name];
                    }
                }
            }
        }
        $oth_as = "mif";

        if ($this->params["columnname"]=="access_count") {
			$rel_tabid = $this->params["fieldtabid"];
			if (!array_key_exists(" vtiger_seactivityrel AS vtiger_seactivityrel_mif_$rel_tabid"."ac ", $join_array)) {
                $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel_mif_$rel_tabid"."ac "]["joincol"] = "vtiger_seactivityrel_mif_$rel_tabid"."ac.crmid";
                $join_array[" vtiger_seactivityrel AS vtiger_seactivityrel_mif_$rel_tabid"."ac "]["using"] = "vtiger_crmentity.crmid";
            }
            if (!array_key_exists(" vtiger_activity AS vtiger_activity_mif_$rel_tabid"."ac ", $join_array)) {
                $join_array[" vtiger_activity AS vtiger_activity_mif_$rel_tabid"."ac "]["joincol"] = "vtiger_activity_mif_$rel_tabid"."ac.activityid";
                $join_array[" vtiger_activity AS vtiger_activity_mif_$rel_tabid"."ac "]["using"] = "vtiger_seactivityrel_mif_$rel_tabid"."ac.activityid";
            }
			if (!array_key_exists(" vtiger_email_track AS vtiger_email_track_mif_$rel_tabid ", $join_array)) {
                $join_array[" vtiger_email_track AS vtiger_email_track_mif_$rel_tabid "]["joincol"] = "vtiger_email_track_mif_$rel_tabid.mailid";
                $join_array[" vtiger_email_track AS vtiger_email_track_mif_$rel_tabid "]["using"] = "vtiger_seactivityrel_mif_$rel_tabid"."ac.activityid";
            }
	       	if(array_key_exists(" vtiger_email_track AS vtiger_email_track_mif_$rel_tabid ", $join_array)){
                $access_count_sql .= " IF(vtiger_email_track_mif_$rel_tabid.access_count IS NOT NULL, vtiger_email_track_mif_$rel_tabid.access_count,0) ";
            }
			$fld_alias = "access_count_mif_".$this->params["fieldtabid"];
            if($access_count_sql!=""){
                $fld_cond = " $access_count_sql ";
                $access_count_col_sql = "$fld_cond AS $fld_alias ";
            }else{
                $access_count_col_sql = " 0 AS $fld_alias ";
            }
            // if(!in_array($access_count_col_sql,$columns_array) && ($this->params["fieldmodule"]=="Accounts")){
            if(!in_array($access_count_col_sql,$columns_array)){
                $columns_array[] = $access_count_col_sql;
                $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
                $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $access_count_col_sql;
                $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
                $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
                $columns_array[$fld_alias] = $this->params["fld_string"];
            }
		}elseif ($this->params["columnname"]=="campaignrelstatus") {

            $campaignstatus_sql = "";
            if($this->params["fieldmodule"]=="Contacts"){
				if (!array_key_exists(" vtiger_campaigncontrel AS vtiger_campaigncontrel_mif_4 ", $join_array)) {
	                $join_array[" vtiger_campaigncontrel AS vtiger_campaigncontrel_mif_4 "]["joincol"] = "vtiger_campaigncontrel_mif_4.campaignid";
	                $join_array[" vtiger_campaigncontrel AS vtiger_campaigncontrel_mif_4 "]["using"] = "vtiger_campaign.campaignid";
	            }
	            if(array_key_exists(" vtiger_campaigncontrel AS vtiger_campaigncontrel_mif_4 ", $join_array)){
	                $campaignstatus_sql .= " IF(vtiger_campaigncontrel_mif_4.campaignrelstatusid IS NOT NULL, (SELECT campaignrelstatus FROM vtiger_campaignrelstatus WHERE vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel_mif_4.campaignrelstatusid),NULL) ";
	            }
            }
            if($this->params["fieldmodule"]=="Accounts"){
				if (!array_key_exists(" vtiger_campaignaccountrel AS vtiger_campaignaccountrel_mif_6 ", $join_array)) {
	                $join_array[" vtiger_campaignaccountrel AS vtiger_campaignaccountrel_mif_6 "]["joincol"] = "vtiger_campaignaccountrel_mif_6.campaignid";
	                $join_array[" vtiger_campaignaccountrel AS vtiger_campaignaccountrel_mif_6 "]["using"] = "vtiger_campaign.campaignid";
	            }
	            if(array_key_exists(" vtiger_campaignaccountrel AS vtiger_campaignaccountrel_mif_6 ", $join_array)){
	                $campaignstatus_sql .= " IF(vtiger_campaignaccountrel_mif_6.campaignrelstatusid IS NOT NULL, (SELECT campaignrelstatus FROM vtiger_campaignrelstatus WHERE vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignaccountrel_mif_6.campaignrelstatusid),NULL) ";
	            }
            }
			if($this->params["fieldmodule"]=="Leads"){
				if (!array_key_exists(" vtiger_campaignleadrel AS vtiger_campaignleadrel_mif_7 ", $join_array)) {
	                $join_array[" vtiger_campaignleadrel AS vtiger_campaignleadrel_mif_7 "]["joincol"] = "vtiger_campaignleadrel_mif_7.campaignid";
	                $join_array[" vtiger_campaignleadrel AS vtiger_campaignleadrel_mif_7 "]["using"] = "vtiger_campaign.campaignid";
	            }
		       	if(array_key_exists(" vtiger_campaignleadrel AS vtiger_campaignleadrel_mif_7 ", $join_array)){
	                $campaignstatus_sql .= " IF(vtiger_campaignleadrel_mif_7.campaignrelstatusid IS NOT NULL, (SELECT campaignrelstatus FROM vtiger_campaignrelstatus WHERE vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignleadrel_mif_7.campaignrelstatusid),NULL) ";
	            }
            }
			
			$fld_alias = "campaignrelstatus_mif_".$this->params["fieldtabid"];
            if($campaignstatus_sql!=""){
                $fld_cond = " $campaignstatus_sql ";
                $campaignstatus_col_sql = "$fld_cond AS $fld_alias ";
            }else{
                $campaignstatus_col_sql = " NULL AS $fld_alias ";
            }
            // if(!in_array($campaignstatus_col_sql,$columns_array) && ($this->params["fieldmodule"]=="Accounts")){
            if(!in_array($campaignstatus_col_sql,$columns_array)){
                $columns_array[] = $campaignstatus_col_sql;
                $columns_array[$this->params["fld_string"]]["fld_alias"] = $fld_alias;
                $columns_array[$this->params["fld_string"]]["fld_sql_str"] = $campaignstatus_col_sql;
                $columns_array[$this->params["fld_string"]]["fld_cond"] = $fld_cond;
                $columns_array["uitype_$fld_alias"] = $this->params["field_uitype"];
                $columns_array[$fld_alias] = $this->params["fld_string"];
            }
        }elseif (in_array($this->params["fieldname"], ITS4YouReports::$intentory_fields)) {
            $field_uitype = "INV";
            $params = Array('fieldid' => $oth_as . $fieldid_alias,
                'fieldtabid' => $this->params["fieldtabid"],
                'fieldmodule' => $secmodule,
                'field_uitype' => $field_uitype,
                'fieldname' => $this->params["fieldname"],
                'columnname' => $this->params["columnname"],
                'tablename' => $this->params["table_name"],
                'table_index' => $secondary_obj->tab_name_index,
                'report_primary_table' => $primary_obj->table_name,
                'primary_table_name' => $secondary_obj->table_name,
                'primary_table_index' => $secondary_obj->table_index,
                'primary_tableid' => $this->params["primary_tableid"],
                'using_aliastablename' => $secondary_obj->table_name."_".$oth_as . $fieldid_alias,
                'using_columnname' => $secondary_obj->table_index,
                'old_oth_as' => "",
                'old_oth_fieldid' => $oth_as,
                'join_type' => "MIF",
                'fld_string' => $this->params["fld_string"],
            );
            $using_array["using"]["tablename"] = $pritablename . "_mif" . $fieldid_alias;
            $using_array["using"]["columnname"] = $primary_obj->tab_name_index[$pritablename];
            $params["using_array"] = $using_array;
            $uifactory = new UIFactory($params);
            // going to UITypeINV
            $pritablename = $tables[0];
            $sectablename = $tables[1];
            $prifieldname = $fields[0][0];
            $secfieldname = $fields[0][1];
// show("INV mif joining 0","_".$oth_as.$fieldid_alias,$pritablename,$sec);
            $uifactory->getInventoryJoinSQL($field_uitype, $join_array, $columns_array);
            $joined = true;
        } else {
            $uirel_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($this->params["fieldtabid"], $this->params["fieldname"])), 0);
            $field_uitype = $uirel_row["uitype"];
            /* COMMENTED FOR A REASON OF MOD COMMENTS PROBLEM RELATIONS
            $params = Array('fieldid' => $uirel_row["fieldid"],
                'fieldtabid' => $uirel_row["tabid"],
                'field_uitype' => $field_uitype,
                'fieldname' => $uirel_row["fieldname"],
                'columnname' => $uirel_row["columnname"],
                'tablename' => $uirel_row["tablename"],
                'table_index' => $related_join_array,
                'report_primary_table' => $this->params["report_primary_table"],
                'primary_table_name' => $secondary_obj->table_name,
                'primary_table_index' => $secondary_obj->table_index,
                'primary_tableid' => $r_tabid,
                'using_aliastablename' => $table_name . "_mif" . $fieldid_alias,
                'using_columnname' => $secondary_obj->tab_name_index[$table_name],
                'old_oth_as' => "",
                'old_oth_fieldid' => $oth_as,
                'join_type' => "MIF",
                'fld_string' => $this->params["fld_string"],
            );
            */
            if ('get_dependents_list' === $list_name) {
				$dependentFieldSql = $adb->pquery("SELECT tabid, tablename, fieldname, columnname FROM vtiger_field WHERE uitype='10' AND" .
						" fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=? AND module=?)", array($module, $secmodule));
				$numOfFields = $adb->num_rows($dependentFieldSql);
		
				if ($numOfFields > 0) {
					$dependentColumn = $adb->query_result($dependentFieldSql, 0, 'columnname');
					$table_name = $adb->query_result($dependentFieldSql, 0, 'tablename');
					
					$related_join_array[$table_name] = $dependentColumn;
					$using_columnname = $dependentColumn;
					$related_join_array[$primary_obj->table_name] = $primary_obj->table_index;
				}
            } else {
				$using_columnname = $related_join_array[$table_name];
			}
            $params = Array('fieldid' => $uirel_row["fieldid"],
                'fieldtabid' => $uirel_row["tabid"],
                'field_uitype' => $field_uitype,
                'fieldname' => $uirel_row["fieldname"],
                'columnname' => $uirel_row["columnname"],
                'tablename' => $uirel_row["tablename"],
                'table_index' => $related_join_array,
                'report_primary_table' => $this->params["report_primary_table"],
                'primary_table_name' => $primary_obj->table_name,
                'primary_table_index' => $primary_obj->table_index,
                'primary_tableid' => $r_tabid,
                'using_aliastablename' => $table_name . "_mif" . $fieldid_alias,
                //'using_columnname' => $primary_obj->tab_name_index[$table_name],
                'using_columnname' => $using_columnname,
                'old_oth_as' => "_mif".$fieldid_alias,
                'old_oth_fieldid' => "",
                'join_type' => "MIF",
                'list_name' => $list_name,
                'fld_string' => $this->params["fld_string"],
            );
            $using_array = getJoinInformation($params);
            $params["using_array"] = $using_array;
            $uifactory = new UIFactory($params);
            $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
        }

        return $tab;
    }

}

?>
