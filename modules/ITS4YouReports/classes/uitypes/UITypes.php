<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UITypes {

    protected $relModuleName = '';
    protected $oth_as = '';
    protected $params = Array();

    function __construct($params) {
        $this->params = $params;
    }
    
    public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array) {
        $adb = PEARDatabase::getInstance();
        if (empty($this->relModuleName)) {
            $this->relModuleName = vtlib_getModuleNameById($this->params['fieldtabid']);
        }
        $related_focus = CRMEntity::getInstance($this->relModuleName);

        if (empty($this->params['columnname']) && !empty($this->params['fieldid'])) {
            $fieldRow = $adb->fetchByAssoc($adb->pquery('SELECT fieldname, columnname, tablename  FROM vtiger_field WHERE fieldid = ?', array($this->params['fieldid'])), 0);
            $this->params['fieldname'] = $fieldRow['fieldname'];
            $this->params['columnname'] = $fieldRow['fieldname'];
            $this->params['tablename'] = $fieldRow['tablename'];
        }
        $params_fieldname = $this->params["fieldname"];
        // first join to vtiger module table 
        $this->params["fieldname"] = $related_focus->tab_name_index[$this->params["tablename"]];

        $this->getStJoinSQL($join_array, $columns_array);

        $r_tabid = getTabid($this->relModuleName);
        //echo "<pre>";print_r($this);echo "</pre>";
        //$adb->setDebug(1);
        $uirel_row = $adb->fetchByAssoc($adb->pquery("SELECT *  FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($r_tabid, $params_fieldname)), 0);
        //$adb->setDebug(0);

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
        //echo "<pre>";print_r($params);echo "</pre>";
        $uifactory->getJoinSQL($field_uitype, $join_array, $columns_array);
    }
    
    public function getInventoryJoinSQL(&$join_array, &$columns_array) {
        return;
    }
    
    public function getModulesByUitype($tablename, $columnname) {
        $modulename[] = $this->relModuleName;
        return $modulename;
    }
    
    protected function getInventoryColumnFldCond($columnName,$column_tablename_alias,$fieldid_alias="",$primary_table_name=""){
        switch ($columnName) {
            case 'ps_profit':
            	$fld_cond = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productid IS NOT NULL) THEN ((vtiger_inventoryproductrel".$fieldid_alias . ".listprice - vtiger_productcf_inv" . $fieldid_alias . ".cf_cost) * vtiger_inventoryproductrel".$fieldid_alias . ".quantity) ELSE '' END ";
            	break;
            	
            case 'prodname':
                $fld_cond = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productname IS NOT NULL) THEN vtiger_products_inv" . $fieldid_alias . ".productname ELSE vtiger_service_inv" . $fieldid_alias . ".servicename END ";
                break;
            
            case 'discount':
                //$fld_cond = " CASE WHEN (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount else ROUND((vtiger_inventoryproductrel" . $fieldid_alias . ".listprice * vtiger_inventoryproductrel" . $fieldid_alias . ".quantity * (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent/100)),3) END ";
                $fld_cond = $this->getInventorySubColumnSql($columnName,$fieldid_alias,$primary_table_name);
                break;
            
            case 'ps_producttotal':
                $fld_cond = $this->getInventorySubColumnSql($columnName,$fieldid_alias,$primary_table_name);
                break;
            
            case 'ps_productstotalafterdiscount':
                $ps_producttotal = $this->getInventorySubColumnSql('ps_producttotal',$fieldid_alias,$primary_table_name);
                $discount = $this->getInventorySubColumnSql('discount',$fieldid_alias,$primary_table_name);
                $fld_cond = " ( $ps_producttotal - $discount )";
                break;
            
            case 'ps_productvatsum':
                $ps_producttotal = $this->getInventorySubColumnSql('ps_producttotal',$fieldid_alias,$primary_table_name);
                $discount = $this->getInventorySubColumnSql('discount',$fieldid_alias,$primary_table_name);
                $ps_productvatpercent = $this->getInventorySubColumnSql("ps_productvatpercent",$fieldid_alias,$primary_table_name);
                $fld_cond = " ( ( $ps_producttotal - $discount ) * ($ps_productvatpercent/100) ) ";
                break;
            
            // DOKONC
            case 'ps_producttotalsum':
                $ps_producttotal = $this->getInventorySubColumnSql('ps_producttotal',$fieldid_alias,$primary_table_name);
                $discount = $this->getInventorySubColumnSql('discount',$fieldid_alias,$primary_table_name);
                $ps_productvatpercent = $this->getInventorySubColumnSql("ps_productvatpercent",$fieldid_alias,$primary_table_name);
                $fld_cond = " ( ( $ps_producttotal - $discount ) + ( ( $ps_producttotal - $discount ) * ($ps_productvatpercent/100) ) )";
                break;
            
            case 'ps_productcategory':
                $fld_cond = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productid IS NOT NULL) THEN vtiger_products_inv" . $fieldid_alias . ".productcategory ELSE vtiger_service_inv" . $fieldid_alias . ".servicecategory END ";
                break;
            
            case 'ps_productno':
                $fld_cond = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productid IS NOT NULL) THEN vtiger_products_inv" . $fieldid_alias . ".product_no ELSE vtiger_service_inv" . $fieldid_alias . ".service_no END ";
                break;
            
            // ITS4YOU-CR SlOl 16. 6. 2016 10:35:28
            case 'ps_producttotalvatsum':
                $fld_cond = $this->getInventorySubColumnSql('ps_producttotalvatsum',$fieldid_alias,$primary_table_name);
                break;
            // ITS4YOU-END 
            
            case 's_h_amount':
            case 'pre_tax_total':
            case 'salescommission':
            case 'subtotal':
            case 'total':
                $fld_cond = $this->params["using_aliastablename"]. "." . $this->params["columnname"];
                break;

            default:
                $fld_cond = "vtiger_inventoryproductrel".$fieldid_alias . "." . $this->params["columnname"];
                break;
        }
        return $fld_cond;
    }
    
    protected function getColumnsArrayValue($fld_cond,$fieldid_alias=""){
        $columns_array_value = $fld_cond . " AS " . $this->params["columnname"] . $fieldid_alias;
        return $columns_array_value;
    }
    
    protected function getInventorySubColumnSql($columnName="",$fieldid_alias="",$primary_table_name=""){
        $adb = PEARDatabase::getInstance();
        $columnSql = "";
        
        switch ($columnName) {
            case 'ps_profit':
            	$columnSql = " CASE WHEN (vtiger_products_inv" . $fieldid_alias . ".productid IS NOT NULL) THEN ((vtiger_inventoryproductrel".$fieldid_alias . ".listprice - vtiger_productcf_inv" . $fieldid_alias . ".cf_cost) * vtiger_inventoryproductrel".$fieldid_alias . ".quantity) ELSE '' END ";
            	break;
            case 'discount':
                // old discount sql
                // $columnSql = " CASE WHEN (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount else ROUND((vtiger_inventoryproductrel" . $fieldid_alias . ".listprice * vtiger_inventoryproductrel" . $fieldid_alias . ".quantity * (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent/100)),3) END ";
                // new discount sql
                $columnSql = " (CASE WHEN (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".discount_amount 
                                WHEN (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent IS NOT NULL AND vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent != '') THEN ROUND((vtiger_inventoryproductrel" . $fieldid_alias . ".listprice * vtiger_inventoryproductrel" . $fieldid_alias . ".quantity * (vtiger_inventoryproductrel" . $fieldid_alias . ".discount_percent/100)),3) 
                                ELSE 0 END) ";
                break;
            case 'ps_producttotal':
                $columnSql = " (vtiger_inventoryproductrel".$fieldid_alias . ".quantity * vtiger_inventoryproductrel".$fieldid_alias . ".listprice) ";
                break;
            case 'ps_productvatpercent':
                if($primary_table_name==""){
                    $primary_table_name = $this->params["primary_table_name"].$fieldid_alias;
                }
                
				$result = $adb->query('SELECT taxname FROM vtiger_inventorytaxinfo ORDER BY taxid ASC');
				if ($result) {
	                $caseArray = array();
					while ($row = $adb->fetchByAssoc($result)) {
						$taxName = $row['taxname'];
						$caseArray[] = 'CASE WHEN ('.$primary_table_name.'.taxtype = "individual" AND vtiger_inventoryproductrel' . $fieldid_alias . '.'.$taxName.' IS NOT NULL AND vtiger_inventoryproductrel' . $fieldid_alias . '.'.$taxName.' != "") THEN vtiger_inventoryproductrel' . $fieldid_alias . '.'.$taxName.' ELSE 0 END';
					}
					$columnSql = '(' . implode(' + ', $caseArray) . ')';
				} else {
	                $columnSql = " ( 
                                CASE WHEN ($primary_table_name.taxtype = 'individual' AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax1 IS NOT NULL AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax1 != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".tax1 ELSE 0 END 
                                 + 
                                CASE WHEN ($primary_table_name.taxtype = 'individual' AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax2 IS NOT NULL AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax2 != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".tax2 ELSE 0 END 
                                 + 
                                 CASE WHEN ($primary_table_name.taxtype = 'individual' AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax3 IS NOT NULL AND vtiger_inventoryproductrel" . $fieldid_alias . ".tax3 != '') THEN vtiger_inventoryproductrel" . $fieldid_alias . ".tax3 ELSE 0 END 
                                ) ";
				}
                break;
            case 'ps_producttotalvatsum':
                if($primary_table_name==""){
                    $primary_table_name = $this->params["primary_table_name"].$fieldid_alias;
                }
                $ps_producttotal = $primary_table_name."$fieldid_alias.pre_tax_total";
                /*$discount = "(
                                CASE WHEN (".$primary_table_name."$fieldid_alias.discount_amount != '') 
                                        THEN ".$primary_table_name."$fieldid_alias.discount_amount 
                                    WHEN (".$primary_table_name."$fieldid_alias.discount_percent IS NOT NULL AND ".$primary_table_name."$fieldid_alias.discount_percent != '') 
                                        THEN ROUND((".$primary_table_name."$fieldid_alias.pre_tax_total * (".$primary_table_name."$fieldid_alias.discount_percent/100)),3) 
                                    ELSE 0 END
                            ) ";*/
                
				$result = $adb->query('SELECT taxname FROM vtiger_inventorytaxinfo ORDER BY taxid ASC');
				if ($result) {
	                $caseArray = array();
					while ($row = $adb->fetchByAssoc($result)) {
						$taxName = $row['taxname'];
						$caseArray[] = 'CASE WHEN ('.$primary_table_name.$fieldid_alias.'.taxtype = "group" AND vtiger_inventoryproductrel'.$fieldid_alias . '.'.$taxName.' IS NOT NULL AND vtiger_inventoryproductrel'.$fieldid_alias . '.'.$taxName.' != "") THEN vtiger_inventoryproductrel'.$fieldid_alias . '.'.$taxName.' ELSE 0 END';
					}
					$ps_productvatpercent = '(' . implode(' + ', $caseArray) . ')';
				} else {
	                $ps_productvatpercent = "( 
	                                CASE WHEN (".$primary_table_name."$fieldid_alias.taxtype = 'group' AND vtiger_inventoryproductrel".$fieldid_alias . ".tax1 IS NOT NULL AND vtiger_inventoryproductrel".$fieldid_alias . ".tax1 != '') THEN vtiger_inventoryproductrel".$fieldid_alias . ".tax1 ELSE 0 END 
	                                 + 
	                                CASE WHEN (".$primary_table_name."$fieldid_alias.taxtype = 'group' AND vtiger_inventoryproductrel".$fieldid_alias . ".tax2 IS NOT NULL AND vtiger_inventoryproductrel".$fieldid_alias . ".tax2 != '') THEN vtiger_inventoryproductrel".$fieldid_alias . ".tax2 ELSE 0 END 
	                                 + 
	                                 CASE WHEN (".$primary_table_name."$fieldid_alias.taxtype = 'group' AND vtiger_inventoryproductrel".$fieldid_alias . ".tax3 IS NOT NULL AND vtiger_inventoryproductrel".$fieldid_alias . ".tax3 != '') THEN vtiger_inventoryproductrel".$fieldid_alias . ".tax3 ELSE 0 END 
	                                )";
				}
				
                //$columnSql = " ( ( $ps_producttotal - $discount ) * ($ps_productvatpercent/100) ) ";
                $columnSql = " ( ( $ps_producttotal ) * ($ps_productvatpercent/100) ) ";
                break;
        }
        return $columnSql;
    }
  
	public static function getClearName($name) {
        $nameArr = explode('_', $name);
        $clear_fld_calculation_type = strtoupper($nameArr[(count($nameArr) - 1)]);
        unset($nameArr[(count($nameArr) - 1)]);
        
        $lk = (count($nameArr) - 1);
        $lk_lc = strtolower($nameArr[$lk]);
        if (in_array($lk_lc, array('mif', 'inv'))) {
            unset($nameArr[$lk]);
        }
        
        return implode('_', $nameArr);
	}
	
}
?>