<?php

/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class UIType83 extends UITypes
{

    public function getStJoinSQL(&$join_array, &$columns_array)
    {
        return;
    }

    public function getJoinSQL(&$join_array, &$columns_array)
    {
        $fieldid_alias = '';
        if (!empty($this->params['fieldid'])) {
            $fieldid_alias = '_' . $this->params['fieldid'];
        }
        if (!array_key_exists(' vtiger_producttaxrel AS vtiger_producttaxrel' . $fieldid_alias . ' ', $join_array)
            && !in_array($this->params['old_oth_fieldid'], ['inv'])) {
            $join_array[' vtiger_producttaxrel AS vtiger_producttaxrel' . $fieldid_alias . ' ']['joincol'] = 'vtiger_producttaxrel' . $fieldid_alias . '.productid';
            /*             * * DO NOT TOUCH THIS CURRENCY JOINNING */
            $join_array[' vtiger_producttaxrel AS vtiger_producttaxrel' . $fieldid_alias . ' ']['using'] = $this->params['using_array']['join']['tablename'] . '.productid';
        }
        $columns_array[] = 'vtiger_producttaxrel' . $fieldid_alias . '.taxpercentage AS ' . $this->params['fieldname'];
        $columns_array[$this->params['fld_string']]['fld_alias'] = $this->params['fieldname'];
        $columns_array[$this->params['fld_string']]['fld_sql_str'] = 'vtiger_producttaxrel' . $fieldid_alias . '.taxpercentage';
        $columns_array[$this->params['fld_string']]['fld_cond'] = 'vtiger_producttaxrel' . $fieldid_alias . '.taxpercentage';
        $columns_array['uitype_' . $this->params['fieldname']] = $this->params['field_uitype'];
        $columns_array[$this->params['fieldname']] = $this->params['fld_string'];
    }

    public function getJoinSQLbyFieldRelation(&$join_array, &$columns_array)
    {
        return;
    }

    public function getInventoryJoinSQL(&$join_array, &$columns_array)
    {
        return;
    }

    public function getModulesByUitype($tablename, $columnname)
    {
        return [];
    }

    public function getSelectedFieldCol($selectedfields)
    {
        $fieldid_alias = "";
        if ($this->params["fieldid"] != "") {
            $fieldid_alias = "_" . $this->params["fieldid"];
        }
        if ($this->params["tablename"] == "vtiger_crmentity") {
            $table_alias = $this->params["tablename"] . "_83" . $fieldid_alias;
            $column_alias = $selectedfields[1];
        } else {
            $table_alias = $this->params["tablename"] . $fieldid_alias;
            $column_alias = $selectedfields[1];
        }

        return $table_alias . "." . $column_alias;
    }

}

?>