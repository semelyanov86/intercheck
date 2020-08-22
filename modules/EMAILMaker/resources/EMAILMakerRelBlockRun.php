<?php

require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once("modules/Reports/Reports.php");
require_once 'modules/Reports/ReportUtils.php';
require_once("vtlib/Vtiger/Module.php");
require_once('modules/Vtiger/helpers/Util.php');

class EMAILMakerRelBlockRun extends CRMEntity {
    var $primarymodule;
    var $secondarymodule;
    var $orderbylistsql;
    var $orderbylistcolumns;
    var $selectcolumns;
    var $groupbylist;
    var $reportname;
    var $totallist;
    var $_groupinglist = false;
    var $_columnslist = false;
    var $_stdfilterlist = false;
    var $_columnstotallist = false;
    var $_advfiltersql = false;
    var $convert_currency = array('Potentials_Amount', 'Accounts_Annual_Revenue', 'Leads_Annual_Revenue', 'Campaigns_Budget_Cost',
        'Campaigns_Actual_Cost', 'Campaigns_Expected_Revenue', 'Campaigns_Actual_ROI', 'Campaigns_Expected_ROI');
    var $append_currency_symbol_to_value = array('hdnDiscountAmount', 'txtAdjustment', 'hdnSubTotal', 'hdnGrandTotal', 'hdnTaxType', 'Products_Unit_Price', 'Services_Price',
        'Invoice_Total', 'Invoice_Sub_Total', 'Invoice_S&H_Amount', 'Invoice_Discount_Amount', 'Invoice_Adjustment',
        'Quotes_Total', 'Quotes_Sub_Total', 'Quotes_S&H_Amount', 'Quotes_Discount_Amount', 'Quotes_Adjustment',
        'SalesOrder_Total', 'SalesOrder_Sub_Total', 'SalesOrder_S&H_Amount', 'SalesOrder_Discount_Amount', 'SalesOrder_Adjustment',
        'PurchaseOrder_Total', 'PurchaseOrder_Sub_Total', 'PurchaseOrder_S&H_Amount', 'PurchaseOrder_Discount_Amount', 'PurchaseOrder_Adjustment','Invoice_Paid_Amount','Invoice_Remaining_Amount','SalesOrder_Paid_Amount',
        'SalesOrder_Remaining_Amount','PurchaseOrder_Paid_Amount','PurchaseOrder_Remaining_Amount'
    );
    var $ui10_fields = array();
    var $ui101_fields = array();
    var $EMAILLanguage;
    protected $queryPlanner = null;

    function EMAILMakerRelBlockRun($crmid, $relblockid, $sorcemodule, $relatedmodule){
        $this->crmid = $crmid;
        $this->relblockid = $relblockid;
        $this->primarymodule = $sorcemodule;
        $this->secondarymodule = $relatedmodule;
        $this->queryPlanner = new EMAILMaker_ReportRunQueryPlanner();
    }

    function getQueryColumnsList($reportid,$outputformat=''){
        if($this->_columnslist !== false) {
                return $this->_columnslist;
        }
        $adb = PearDatabase::getInstance();
        
        global $modules;
        global $log,$current_user,$current_language;

        $ssql = "select vtiger_emakertemplates_relblockcol.* from vtiger_emakertemplates_relblocks ";
        $ssql .= " left join vtiger_emakertemplates_relblockcol on vtiger_emakertemplates_relblockcol.relblockid = vtiger_emakertemplates_relblocks.relblockid";
        $ssql .= " where vtiger_emakertemplates_relblocks.relblockid = ?";
        $ssql .= " order by vtiger_emakertemplates_relblockcol.colid";
        $result = $adb->pquery($ssql, array($reportid));
        $permitted_fields = Array();

        while($columnslistrow = $adb->fetch_array($result)){
            $fieldname ="";
            $fieldcolname = $columnslistrow["columnname"];
            list($tablename,$colname,$module_field,$fieldname,$single) = split(":",$fieldcolname);
            list($module,$field) = split("_",$module_field,2);
            $inventory_fields = array('serviceid');
            $inventory_modules = getInventoryModules();
            require('user_privileges/user_privileges_'.$current_user->id.'.php');
            if(sizeof($permitted_fields[$module]) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1){
                $permitted_fields[$module] = $this->getaccesfield($module);
            }
            if(in_array($module,$inventory_modules)){
                if (!empty ($permitted_fields)) {
                    foreach ($inventory_fields as $value){
                            array_push($permitted_fields[$module], $value);
                    }
                }
            }
            $selectedfields = explode(":",$fieldcolname);
            if($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
                            && !in_array($selectedfields[3], $permitted_fields[$module])) {
                continue;
            }

            if($selectedfields[0] == "vtiger_contactdetailsHelpDesk") $selectedfields[0] = "vtiger_contactdetailsRelHelpDesk";
            
            $concatSql = getSqlForNameInDisplayFormat(array('first_name'=>$selectedfields[0].".first_name",'last_name'=>$selectedfields[0].".last_name"), 'Users');
            $querycolumns = $this->getEscapedColumns($selectedfields);
            if(isset($module) && $module!=""){
                $mod_strings = return_module_language($current_language,$module);
            }
            $targetTableName = $tablename;
            $fieldname = $selectedfields[3];
            $fieldlabel = trim(preg_replace("/$module/"," ",$selectedfields[2],1));

            $mod_arr=explode('_',$fieldlabel);
            $fieldlabel = trim(str_replace("_"," ",$fieldlabel));
            $fld_arr = explode(" ",$fieldlabel);
            if(($mod_arr[0] == '')){
                $mod = $module;
                $mod_lbl = $this->getTranslatedString($module,$module);
            } else {
                $mod = $mod_arr[0];
                array_shift($fld_arr);
                $mod_lbl = $this->getTranslatedString($fld_arr[0],$mod);
            }
            $fld_lbl_str = implode(" ",$fld_arr);
            $fld_lbl = $this->getTranslatedString($fld_lbl_str,$module);
            $fieldlabel = $mod."_".$fieldname;

            
            
            if(($selectedfields[0] == "vtiger_usersRel1")  && ($selectedfields[1] == 'user_name') && ($selectedfields[2] == 'Quotes_Inventory_Manager')){
                $columnslist[$fieldcolname] = "trim( $concatSql ) as ".$module."_Inventory_Manager";
                $this->queryPlanner->addTable($selectedfields[0]);
                continue;
            }
            if((CheckFieldPermission($fieldname,$mod) != 'true' && $colname!="crmid" && (!in_array($fieldname,$inventory_fields) && in_array($module,$inventory_modules))) || empty($fieldname)){
                continue;
            }else{
                $this->labelMapping[$selectedfields[2]] = str_replace(" ","_",$fieldlabel);
                $header_label = $fieldlabel;

                if($querycolumns == ""){
                    if($selectedfields[4] == 'C'){
                        $field_label_data = split("_",$selectedfields[2]);
                        $module= $field_label_data[0];
                        if($module!=$this->primarymodule){
                            $columnslist[$fieldcolname] = "case when (".$selectedfields[0].".".$selectedfields[1]."='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end as '$fieldlabel'";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        }else{
                            $columnslist[$fieldcolname] = "case when (".$selectedfields[0].".".$selectedfields[1]."='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end as '$fieldlabel'";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        }
                    }elseif($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status'){
                        $columnslist[$fieldcolname] = " case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end as Calendar_Status";
                    }elseif($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
                        if($module == 'Emails'){
                            $columnslist[$fieldcolname] = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATE) as Emails_Date_Sent";
                        }else{
                            $columnslist[$fieldcolname] = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) AS DATETIME) AS Calendar_date_start";
                        }
                    }elseif(stristr($selectedfields[0],"vtiger_users") && ($selectedfields[1] == 'user_name')){
                        $temp_module_from_tablename = str_replace("vtiger_users","",$selectedfields[0]);
                        if($module!=$this->primarymodule){
                            $condition = "and vtiger_crmentity".$module.".crmid!=''";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        }else{
                            $condition = "and vtiger_crmentity.crmid!=''";
                        }
                        if($temp_module_from_tablename == $module) {
                            $columnslist[$fieldcolname] = " case when(".$selectedfields[0].".last_name NOT LIKE '' $condition ) THEN ".$concatSql." else vtiger_groups".$module.".groupname end as '".$module."_$field'";
                            $this->queryPlanner->addTable('vtiger_groups'.$module); 
                        }else
                            $columnslist[$fieldcolname] = $selectedfields[0].".user_name as '".$header_label."'";

                    }elseif(stristr($selectedfields[0],"vtiger_crmentity") && ($selectedfields[1] == 'modifiedby')){
                        $targetTableName = 'vtiger_lastModifiedBy'.$module;
                        $concatSql = getSqlForNameInDisplayFormat(array('last_name'=>$targetTableName.'.last_name', 'first_name'=>$targetTableName.'.first_name'), 'Users');
                        $columnslist[$fieldcolname] = "trim($concatSql) as $header_label";
                        $this->queryPlanner->addTable("vtiger_crmentity$module");
                        $this->queryPlanner->addTable($targetTableName);
                    }elseif($selectedfields[0] == "vtiger_crmentity".$this->primarymodule){
                        $columnslist[$fieldcolname] = "vtiger_crmentity.".$selectedfields[1]." AS '".$header_label."'";
                    }elseif($selectedfields[0] == 'vtiger_products' && $selectedfields[1] == 'unit_price'){
                        $columnslist[$fieldcolname] = "concat(".$selectedfields[0].".currency_id,'::',innerProduct.actual_unit_price) as '". $header_label ."'";
                        $this->queryPlanner->addTable("innerProduct");
                    }elseif(in_array($selectedfields[2], $this->append_currency_symbol_to_value)) {
                        if($selectedfields[1] == 'discount_amount') {
                            $columnslist[$fieldcolname] = "CONCAT(".$selectedfields[0].".currency_id,'::', IF(".$selectedfields[0].".discount_amount != '',".$selectedfields[0].".discount_amount, (".$selectedfields[0].".discount_percent/100) * ".$selectedfields[0].".subtotal)) AS ".$header_label;
                        }else{
                            $columnslist[$fieldcolname] = "concat(".$selectedfields[0].".currency_id,'::',".$selectedfields[0].".".$selectedfields[1].") as '" . $header_label ."'";
                        }
                    }elseif($selectedfields[0] == 'vtiger_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1]=='filestatus')){
                        if($selectedfields[1] == 'filelocationtype'){
                            $columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when 'I' then 'Internal' when 'E' then 'External' else '-' end as '$selectedfields[2]'";
                        }else if($selectedfields[1] == 'folderid'){
                            $columnslist[$fieldcolname] = "vtiger_attachmentsfolder.foldername as '$selectedfields[2]'";
                        }elseif($selectedfields[1] == 'filestatus'){
                            $columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when '1' then 'yes' when '0' then 'no' else '-' end as '$selectedfields[2]'";
                        }elseif($selectedfields[1] == 'filesize'){
                            $columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when '' then '-' else concat(".$selectedfields[0].".".$selectedfields[1]."/1024,'  ','KB') end as '$selectedfields[2]'";
                        }
                    }elseif($selectedfields[0] == 'vtiger_inventoryproductrel'){
                        if($selectedfields[1] == 'discount_amount'){
                            $columnslist[$fieldcolname] = " case when (vtiger_inventoryproductrel{$module}.discount_amount != '') then vtiger_inventoryproductrel{$module}.discount_amount else ROUND((vtiger_inventoryproductrel{$module}.listprice * vtiger_inventoryproductrel{$module}.quantity * (vtiger_inventoryproductrel{$module}.discount_percent/100)),3) end as '" . $header_label ."'";
                            $this->queryPlanner->addTable($selectedfields[0].$module);
                        }else if($selectedfields[1] == 'productid'){
                            $columnslist[$fieldcolname] = "vtiger_products{$module}.productname as '" . $header_label ."'";
                            $this->queryPlanner->addTable("vtiger_products{$module}");
                        }else if($selectedfields[1] == 'serviceid'){
                            $columnslist[$fieldcolname] = "vtiger_service{$module}.servicename as '" . $header_label ."'";
                            $this->queryPlanner->addTable("vtiger_service{$module}");
                        }else if($selectedfields[1] == 'listprice') {
                            $moduleInstance = CRMEntity::getInstance($module);
                            $columnslist[$fieldcolname] = $selectedfields[0].$module.".".$selectedfields[1]."/".$moduleInstance->table_name.".conversion_rate as '".$header_label."'";
                            $this->queryPlanner->addTable($selectedfields[0].$module);
                        }else{
                            $columnslist[$fieldcolname] = $selectedfields[0].$module.".".$selectedfields[1]." as '".$header_label."'";
                            $this->queryPlanner->addTable($selectedfields[0].$module);
                        }
                    }elseif(stristr($selectedfields[1],'cf_')==true && stripos($selectedfields[1],'cf_')==0){
                        $columnslist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." AS '".$adb->sql_escape_string(decode_html($header_label))."'";
                    }else{
                        $columnslist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." AS '".$header_label."'";
                    }
                }else{
                    $columnslist[$fieldcolname] = $querycolumns;
                }
                $this->queryPlanner->addTable($targetTableName);
            }
        }
        $this->_columnslist = $columnslist;
        $log->info("ReportRun :: Successfully returned getQueryColumnsList".$reportid);
        return $columnslist;
    }
    function getaccesfield($module){
      
        $adb = PearDatabase::getInstance();
        $access_fields = Array();
        $profileList = getCurrentUserProfileList();
        $query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
        $params = array();
        if ($module == "Calendar") {
            if (count($profileList) > 0) {
                $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
            }
        } else {
            array_push($params, $this->primarymodule, $this->secondarymodule);
            if (count($profileList) > 0) {
                $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
            }
        }
        $result = $adb->pquery($query, $params);
        while ($collistrow = $adb->fetch_array($result)){
            $access_fields[] = $collistrow["fieldname"];
        }
        if ($module == "HelpDesk")
            $access_fields[] = "ticketid";
        return $access_fields;
    }
    function getEscapedColumns($selectedfields){
		$tableName = $selectedfields[0];
		$columnName = $selectedfields[1];
		$moduleFieldLabel = $selectedfields[2];
		$fieldName = $selectedfields[3];
		list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
		$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
                $moduleFieldName = $moduleName."_".$fieldName;
		if($moduleName == 'ModComments' && $fieldName == 'creator') {
			$concatSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_usersModComments.first_name','last_name' => 'vtiger_usersModComments.last_name'), 'Users');
			$queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) as 'ModComments_Creator'";

		} elseif(($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
				&& $fieldInfo['uitype'] != '52' && $fieldInfo['uitype'] != '53'){
			$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
			if(count($fieldSqlColumns) > 0) {
				$queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";
				foreach($fieldSqlColumns as $columnSql){
					$queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
				}
				$queryColumn .= " ELSE '' END) ELSE '' END) AS $moduleFieldName";
				$this->queryPlanner->addTable($tableName);
			}
		}
		return $queryColumn;
	}
        function getReferenceFieldColumnList($moduleName, $fieldInfo){
		$adb = PearDatabase::getInstance();
		$columnsSqlList = array();
		$fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
		$referenceModuleList = $fieldInstance->getReferenceList();
		$reportSecondaryModules = explode(':', $this->secondarymodule);

		if($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
			$entityTableFieldNames = getEntityFieldNames($this->primarymodule);
			$entityTableName = $entityTableFieldNames['tablename'];
			$entityFieldNames = $entityTableFieldNames['fieldname'];

			$columnList = array();
			if(is_array($entityFieldNames)){
				foreach ($entityFieldNames as $entityColumnName){
					$columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
				}
			} else {
				$columnList[] = "$entityTableName.$entityFieldNames";
			}
			if(count($columnList) > 1){
				$columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
			} else {
				$columnSql = implode('', $columnList);
			}
			$columnsSqlList[] = $columnSql;
		} else {
			foreach($referenceModuleList as $referenceModule){
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityFieldNames = $entityTableFieldNames['fieldname'];
				$referenceTableName = $dependentTableName = '';
				if($moduleName == 'HelpDesk' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsRel';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Leads') {
					$referenceTableName = 'vtiger_leaddetailsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Potentials') {
					$referenceTableName = 'vtiger_potentialRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Invoice') {
					$referenceTableName = 'vtiger_invoiceRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Quotes') {
					$referenceTableName = 'vtiger_quotesRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'PurchaseOrder') {
					$referenceTableName = 'vtiger_purchaseorderRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'SalesOrder') {
					$referenceTableName = 'vtiger_salesorderRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'HelpDesk') {
					$referenceTableName = 'vtiger_troubleticketsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Campaigns') {
					$referenceTableName = 'vtiger_campaignRelCalendar';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountContacts';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsContacts';
				} elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountAccounts';
				} elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsCampaigns';
				} elseif ($moduleName == 'Faq' && $referenceModule == 'Products') {
					$referenceTableName = 'vtiger_productsFaq';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'SalesOrder') {
					$referenceTableName = 'vtiger_salesorderInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountInvoice';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Campaigns') {
					$referenceTableName = 'vtiger_campaignPotentials';
				} elseif ($moduleName == 'Products' && $referenceModule == 'Vendors') {
					$referenceTableName = 'vtiger_vendorRelProducts';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsPurchaseOrder';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Vendors') {
					$referenceTableName = 'vtiger_vendorRelPurchaseOrder';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Potentials') {
					$referenceTableName = 'vtiger_potentialRelQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsQuotes';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Potentials') {
					$referenceTableName = 'vtiger_potentialRelSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Quotes') {
					$referenceTableName = 'vtiger_quotesSalesOrder';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Contacts') {
					$referenceTableName = 'vtiger_contactdetailsPotentials';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Accounts') {
					$referenceTableName = 'vtiger_accountPotentials';
				} elseif (in_array($referenceModule, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$referenceModule";
					$dependentTableName = "vtiger_crmentityRel{$referenceModule}{$fieldInstance->getFieldId()}";
				} elseif (in_array($moduleName, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$moduleName";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
				} else {
					$referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
				}
				$this->queryPlanner->addTable($referenceTableName);
				if(isset($dependentTableName)){
				    $this->queryPlanner->addTable($dependentTableName);
				}
				$columnList = array();
				if(is_array($entityFieldNames)) {
					foreach ($entityFieldNames as $entityColumnName) {
						$columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
					}
				} else {
					$columnList[] = "$referenceTableName.$entityFieldNames";
				}
				if(count($columnList) > 1) {
					$columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
				} else {
					$columnSql = implode('', $columnList);
				}
				if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
					$columnSql = 'vtiger_attachmentsfolder.foldername';
					$this->queryPlanner->addTable("vtiger_attachmentsfolder");
				}
				if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
					$columnSql = "vtiger_currency_info$moduleName.currency_name";
					$this->queryPlanner->addTable("vtiger_currency_info$moduleName");
				    }
				$columnsSqlList[] = $columnSql;
			}
		}
		return $columnsSqlList;
	}
    function getAdvComparator($comparator, $value, $datatype = "") {

        global $log, $adb, $default_charset;
        $value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
        $value_len = strlen($value);
        $is_field = false;
        if ($value[0] == '$' && $value[$value_len - 1] == '$') {
            $temp = str_replace('$', '', $value);
            $is_field = true;
        }
        if ($datatype == 'C') {
            $value = str_replace("yes", "1", str_replace("no", "0", $value));
        }
        if ($is_field == true) {
            $value = $this->getFilterComparedField($temp);
        }
        if ($comparator == "e") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " = " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " = " . $adb->quote($value);
            } else {
                $rtvalue = " is NULL";
            }
        }
        if ($comparator == "n") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NOT NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " <> " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " <> " . $adb->quote($value);
            } else {
                $rtvalue = " is NOT NULL";
            }
        }
        if ($comparator == "s") {
            $rtvalue = " like '" . formatForSqlLike($value, 2, $is_field) . "'";
        }
        if ($comparator == "ew") {
            $rtvalue = " like '" . formatForSqlLike($value, 1, $is_field) . "'";
        }
        if ($comparator == "c") {
            $rtvalue = " like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "k") {
            $rtvalue = " not like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "l") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "g") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($comparator == "m") {
            $rtvalue = " <= " . $adb->quote($value);
        }
        if ($comparator == "h") {
            $rtvalue = " >= " . $adb->quote($value);
        }
        if ($comparator == "b") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "a") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($is_field == true) {
            $rtvalue = str_replace("'", "", $rtvalue);
            $rtvalue = str_replace("\\", "", $rtvalue);
        }
        $log->info("ReportRun :: Successfully returned getAdvComparator");
        return $rtvalue;
    }
    function getFilterComparedField($field) {
        global $adb, $ogReport;
        if (!empty($this->secondarymodule)) {
            $secModules = explode(':', $this->secondarymodule);
            foreach ($secModules as $secModule) {
                $secondary = CRMEntity::getInstance($secModule);
                $this->queryPlanner->addTable($secondary->table_name);
            }
        }
        $field = split('#', $field);
        $module = $field[0];
        $fieldname = trim($field[1]);
        $tabid = getTabId($module);
        $field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? AND fieldname= ?", array($tabid, $fieldname));
        $fieldtablename = $adb->query_result($field_query, 0, 'tablename');
        $fieldcolname = $adb->query_result($field_query, 0, 'columnname');
        $typeofdata = $adb->query_result($field_query, 0, 'typeofdata');
        $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $typeofdata[0]);
        $uitype = $adb->query_result($field_query, 0, 'uitype');

        if ($uitype == 68 || $uitype == 59) {
            $fieldtypeofdata = 'V';
        }
        if ($fieldtablename == "vtiger_crmentity") {
            $fieldtablename = $fieldtablename . $module;
        }
        if ($fieldname == "assigned_user_id") {
            $fieldtablename = "vtiger_users" . $module;
            $fieldcolname = "user_name";
        }
        if ($fieldname == "account_id") {
            $fieldtablename = "vtiger_account" . $module;
            $fieldcolname = "accountname";
        }
        if ($fieldname == "contact_id") {
            $fieldtablename = "vtiger_contactdetails" . $module;
            $fieldcolname = "lastname";
        }
        if ($fieldname == "parent_id") {
            $fieldtablename = "vtiger_crmentityRel" . $module;
            $fieldcolname = "setype";
        }
        if ($fieldname == "vendor_id") {
            $fieldtablename = "vtiger_vendorRel" . $module;
            $fieldcolname = "vendorname";
        }
        if ($fieldname == "potential_id") {
            $fieldtablename = "vtiger_potentialRel" . $module;
            $fieldcolname = "potentialname";
        }
        if ($fieldname == "assigned_user_id1") {
            $fieldtablename = "vtiger_usersRel1";
            $fieldcolname = "user_name";
        }
        if ($fieldname == 'quote_id') {
            $fieldtablename = "vtiger_quotes" . $module;
            $fieldcolname = "subject";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_troubletickets') {
            $fieldtablename = "vtiger_productsRel";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_campaign') {
            $fieldtablename = "vtiger_productsCampaigns";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_products') {
            $fieldtablename = "vtiger_productsProducts";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'campaignid' && $module == 'Potentials') {
            $fieldtablename = "vtiger_campaign" . $module;
            $fieldcolname = "campaignname";
        }
        $value = $fieldtablename . "." . $fieldcolname;
        $this->queryPlanner->addTable($fieldtablename);
        return $value;
    }
	function getAdvFilterByRBid($relblockid) {
		global $adb, $log, $default_charset;
		$advft_criteria = array();
		$sql = 'SELECT * FROM vtiger_emakertemplates_relblockcriteria_g WHERE relblockid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($relblockid));
		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_emakertemplates_relblockcriteria.* from vtiger_emakertemplates_relblocks
						inner join vtiger_emakertemplates_relblockcriteria on vtiger_emakertemplates_relblockcriteria.relblockid = vtiger_emakertemplates_relblocks.relblockid
						left join vtiger_emakertemplates_relblockcriteria_g on vtiger_emakertemplates_relblockcriteria.relblockid = vtiger_emakertemplates_relblockcriteria_g.relblockid
								and vtiger_emakertemplates_relblockcriteria.groupid = vtiger_emakertemplates_relblockcriteria_g.groupid';
			$ssql.= " where vtiger_emakertemplates_relblocks.relblockid = ? AND vtiger_emakertemplates_relblockcriteria.groupid = ? order by vtiger_emakertemplates_relblockcriteria.colid";

			$result = $adb->pquery($ssql, array($relblockid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $adb->fetch_array($result)) {
				$columnIndex = $relcriteriarow["columnindex"];
				$criteria = array();
				$criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
				$criteria['comparator'] = $relcriteriarow["comparator"];
				$advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
				$col = explode(":", $relcriteriarow["columnname"]);
				$temp_val = explode(",", $relcriteriarow["value"]);
				if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
					$val = Array();
					for ($x = 0; $x < count($temp_val); $x++) {
						if ($col[4] == 'D') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDate();
						} elseif ($col[4] == 'DT') {
							$comparator = array('e','n','b','a');
							if(in_array($criteria['comparator'], $comparator)) {
								$originalValue = $temp_val[$x];
								$dateTime = explode(' ',$originalValue);
								$temp_val[$x] = $dateTime[0];
							}
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDateTimeValue();
						} else {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayTime();
						}
					}
					$advfilterval = implode(",", $val);
				}
				$criteria['value'] = $advfilterval;
				$criteria['column_condition'] = $relcriteriarow["column_condition"];

				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;
				$j++;
			}
			if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
				$advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
			}
			$i++;
		}
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';
		return $advft_criteria;
	}
    function getAdvFilterSql($relblockid) {
        global $adb;
        global $current_user;

        $advfilterlist = $this->getAdvFilterByRBid($relblockid);
        $advfiltersql = "";
        $customView = new CustomView();
        $dateSpecificConditions = $customView->getStdFilterConditions();

		foreach($advfilterlist as $groupindex => $groupinfo) {
			$groupcondition = $groupinfo['condition'];
			$groupcolumns = $groupinfo['columns'];

			if(count($groupcolumns) > 0) {

				$advfiltergroupsql = "";
				foreach($groupcolumns as $columnindex => $columninfo) {
					$fieldcolname = $columninfo["columnname"];
					$comparator = $columninfo["comparator"];
					$value = $columninfo["value"];
					$columncondition = $columninfo["column_condition"];

					if($fieldcolname != "" && $comparator != "") {
						if(in_array($comparator, $dateSpecificConditions)) {
							if($fieldcolname != 'none') {
								$selectedFields = explode(':',$fieldcolname);
								if($selectedFields[0] == 'vtiger_crmentity'.$this->primarymodule) {
									$selectedFields[0] = 'vtiger_crmentity';
								}

								if($comparator != 'custom') {
									list($startDate, $endDate) = $this->getStandarFiltersStartAndEndDate($comparator);
								} else {
                                    list($startDateTime, $endDateTime) = explode(',', $value);
									list($startDate, $startTime) = explode(' ', $startDateTime);
									list($endDate, $endTime) = explode(' ', $endDateTime);
                                }

								$type = $selectedFields[4];
								if($startDate != '0000-00-00' && $endDate != '0000-00-00' && $startDate != '' && $endDate != '') {
									$startDateTime = new DateTimeField($startDate. ' ' .date('H:i:s'));
									$userStartDate = $startDateTime->getDisplayDate();
									if($type == 'DT') {
										$userStartDate = $userStartDate.' 00:00:00';
									}
									$startDateTime = getValidDBInsertDateTimeValue($userStartDate);

									$endDateTime = new DateTimeField($endDate. ' ' .date('H:i:s'));
									$userEndDate = $endDateTime->getDisplayDate();
									if($type == 'DT') {
										$userEndDate = $userEndDate.' 23:59:59';
									}
									$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

									if ($selectedFields[1] == 'birthday') {
										$tableColumnSql = 'DATE_FORMAT(' . $selectedFields[0] . '.' . $selectedFields[1] . ', "%m%d")';
										$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
										$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
									} else {
										if($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
											$tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
										} else {
											$tableColumnSql = $selectedFields[0]. '.' .$selectedFields[1];
										}
										$startDateTime = "'$startDateTime'";
										$endDateTime = "'$endDateTime'";
									}

									$advfiltergroupsql .= "$tableColumnSql BETWEEN $startDateTime AND $endDateTime";
									if(!empty($columncondition)) {
										$advfiltergroupsql .= ' '.$columncondition.' ';
									}

									$this->queryPlanner->addTable($selectedFields[0]);
								}
							}
							continue;
                        }

						$selectedfields = explode(":",$fieldcolname);
						$moduleFieldLabel = $selectedfields[2];
						list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
						$fieldInfo = $this->getFieldByEMAILMakerLabel($moduleName, $fieldLabel);
                        $concatSql = getSqlForNameInDisplayFormat(array('first_name'=>$selectedfields[0].".first_name",'last_name'=>$selectedfields[0].".last_name"), 'Users');
                        if($selectedfields[0] == "vtiger_crmentity".$this->primarymodule) {
                            $selectedfields[0] = "vtiger_crmentity";
                        }
						if($selectedfields[4] == 'C') {
							if(strcasecmp(trim($value),"yes")==0)
								$value="1";
							if(strcasecmp(trim($value),"no")==0)
								$value="0";
						}
                        if(in_array($comparator,$dateSpecificConditions)) {
                            $customView = new CustomView($moduleName);
                            $columninfo['stdfilter'] = $columninfo['comparator'];
                            $valueComponents = explode(',',$columninfo['value']);
                            if($comparator == 'custom') {
								if($selectedfields[4] == 'DT') {
									$startDateTimeComponents = explode(' ',$valueComponents[0]);
									$endDateTimeComponents = explode(' ',$valueComponents[1]);
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
								} else {
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
								}
                            }
                            $dateFilterResolvedList = $customView->resolveDateFilterValue($columninfo);
                            $startDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['startdate']);
                            $endDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['enddate']);
                            $columninfo['value'] = $value  = implode(',', array($startDate,$endDate));
                            $comparator = 'bw';
                        }
						$valuearray = explode(",",trim($value));
						$datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
						if(isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

							$advcolumnsql = "";
							for($n=0;$n<count($valuearray);$n++) {

		                		if(($selectedfields[0] == "vtiger_users".$this->primarymodule || $selectedfields[0] == "vtiger_users".$this->secondarymodule) && $selectedfields[1] == 'user_name') {
									$module_from_tablename = str_replace("vtiger_users","",$selectedfields[0]);
									$advcolsql[] = " (trim($concatSql)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype)." or vtiger_groups".$module_from_tablename.".groupname ".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype).")";
									$this->queryPlanner->addTable("vtiger_groups".$module_from_tablename);
								} elseif($selectedfields[1] == 'status') {
									if($selectedfields[2] == 'Calendar_Status')
									$advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									elseif($selectedfields[2] == 'HelpDesk_Status')
									$advcolsql[] = "vtiger_troubletickets.status".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								} elseif($selectedfields[1] == 'description') {
									if($selectedfields[0]=='vtiger_crmentity'.$this->primarymodule)
										$advcolsql[] = "vtiger_crmentity.description".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									else
										$advcolsql[] = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								} elseif($selectedfields[2] == 'Quotes_Inventory_Manager'){
									$advcolsql[] = ("trim($concatSql)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype));
								} else {
									$advcolsql[] = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								}
							}
							if($comparator == 'n' || $comparator == 'k')
								$advcolumnsql = implode(" and ",$advcolsql);
							else
								$advcolumnsql = implode(" or ",$advcolsql);
							$fieldvalue = " (".$advcolumnsql.") ";
						} elseif($selectedfields[1] == 'user_name') {
							if($selectedfields[0] == "vtiger_users".$this->primarymodule) {
								$module_from_tablename = str_replace("vtiger_users","",$selectedfields[0]);
								$fieldvalue = " trim(case when (".$selectedfields[0].".last_name NOT LIKE '') then ".$concatSql." else vtiger_groups".$module_from_tablename.".groupname end) ".$this->getAdvComparator($comparator,trim($value),$datatype);
								$this->queryPlanner->addTable("vtiger_groups".$module_from_tablename);
							} else {
								$secondaryModules = explode(':', $this->secondarymodule);
								$firstSecondaryModule = "vtiger_users".$secondaryModules[0];
								$secondSecondaryModule = "vtiger_users".$secondaryModules[1];
 								if(($firstSecondaryModule && $firstSecondaryModule == $selectedfields[0]) || ($secondSecondaryModule && $secondSecondaryModule == $selectedfields[0])) {
									$module_from_tablename = str_replace("vtiger_users","",$selectedfields[0]);
									$moduleInstance = CRMEntity::getInstance($module_from_tablename);
									$fieldvalue = " trim(case when (".$selectedfields[0].".last_name NOT LIKE '') then ".$concatSql." else vtiger_groups".$module_from_tablename.".groupname end) ".$this->getAdvComparator($comparator,trim($value),$datatype);
									$this->queryPlanner->addTable("vtiger_groups".$module_from_tablename);
									$this->queryPlanner->addTable($moduleInstance->table_name);
								}
							}
						} elseif($comparator == 'bw' && count($valuearray) == 2) {
							if($selectedfields[0] == "vtiger_crmentity".$this->primarymodule) {
								$fieldvalue = "("."vtiger_crmentity.".$selectedfields[1]." between '".trim($valuearray[0])."' and '".trim($valuearray[1])."')";
							} else {
								$fieldvalue = "(".$selectedfields[0].".".$selectedfields[1]." between '".trim($valuearray[0])."' and '".trim($valuearray[1])."')";
							}
						} elseif($selectedfields[0] == "vtiger_crmentity".$this->primarymodule) {
							$fieldvalue = "vtiger_crmentity.".$selectedfields[1]." ".$this->getAdvComparator($comparator,trim($value),$datatype);
						} elseif($selectedfields[2] == 'Quotes_Inventory_Manager'){
							$fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator,trim($value),$datatype));
						} elseif($selectedfields[1]=='modifiedby') {
                            $module_from_tablename = str_replace("vtiger_crmentity","",$selectedfields[0]);
                            if($module_from_tablename != '') {
								$tableName = 'vtiger_lastModifiedBy'.$module_from_tablename;
							} else {
								$tableName = 'vtiger_lastModifiedBy'.$this->primarymodule;
							}
							$this->queryPlanner->addTable($tableName);
							$fieldvalue = getSqlForNameInDisplayFormat(array('last_name'=>"$tableName.last_name",'first_name'=>"$tableName.first_name"), 'Users').
									$this->getAdvComparator($comparator,trim($value),$datatype);
						} elseif($selectedfields[0] == "vtiger_activity" && $selectedfields[1] == 'status') {
							$fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)".$this->getAdvComparator($comparator,trim($value),$datatype);
						} elseif($comparator == 'y' || ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == ''))) {
							if($selectedfields[0] == 'vtiger_inventoryproductrel') {
								$selectedfields[0]='vtiger_inventoryproductrel'.$moduleName;
							}
							$fieldvalue = "(".$selectedfields[0].".".$selectedfields[1]." IS NULL OR ".$selectedfields[0].".".$selectedfields[1]." = '')";
						} elseif($selectedfields[0] == 'vtiger_inventoryproductrel' ) {
							if($selectedfields[1] == 'productid'){
									$fieldvalue = "vtiger_products$moduleName.productname ".$this->getAdvComparator($comparator,trim($value),$datatype);
									$this->queryPlanner->addTable("vtiger_products$moduleName");
							} else if($selectedfields[1] == 'serviceid'){
								$fieldvalue = "vtiger_service$moduleName.servicename ".$this->getAdvComparator($comparator,trim($value),$datatype);
								$this->queryPlanner->addTable("vtiger_service$moduleName");
							}
							else{
								$selectedfields[0]='vtiger_inventoryproductrel'.$moduleName;
								$fieldvalue = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator, $value, $datatype);
							}
						} elseif($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {

							$comparatorValue = $this->getAdvComparator($comparator,trim($value),$datatype);
							$fieldSqls = array();
							$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
							foreach($fieldSqlColumns as $columnSql) {
							 	$fieldSqls[] = $columnSql.$comparatorValue;
							}
							$fieldvalue = ' ('. implode(' OR ', $fieldSqls).') ';
							} else {
							$fieldvalue = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($value),$datatype);
						}

						$advfiltergroupsql .= $fieldvalue;
						if(!empty($columncondition)) {
							$advfiltergroupsql .= ' '.$columncondition.' ';
						}

						$this->queryPlanner->addTable($selectedfields[0]);
					}

				}

				if (trim($advfiltergroupsql) != "") {
					$advfiltergroupsql =  "( $advfiltergroupsql ) ";
					if(!empty($groupcondition)) {
						$advfiltergroupsql .= ' '. $groupcondition . ' ';
					}

					$advfiltersql .= $advfiltergroupsql;
				}
			}
		}
		if (trim($advfiltersql) != "") $advfiltersql = '('.$advfiltersql.')';

		return $advfiltersql;
	}

	function getAdvFilterSqlOLD2($relblockid) {
		global $current_user;
		$advfilter = $this->getAdvFilterByRBid($relblockid);
		$advcvsql = "";

		foreach ($advfilter as $groupid => $groupinfo) {

			$groupcolumns = $groupinfo["columns"];
			$groupcondition = $groupinfo["condition"];
			$advfiltergroupsql = "";

			foreach ($groupcolumns as $columnindex => $columninfo) {
				$columnname = $columninfo['columnname'];
				$comparator = $columninfo['comparator'];
				$value = $columninfo['value'];
				$columncondition = $columninfo['column_condition'];

				$columns = explode(":", $columnname);
				$datatype = (isset($columns[4])) ? $columns[4] : "";

				if ($columnname != "" && $comparator != "") {
					$valuearray = explode(",", trim($value));

					if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
						$advorsql = "";
						for ($n = 0; $n < count($valuearray); $n++) {
							$advorsql[] = $this->getRealValues($columns[0], $columns[1], $comparator, trim($valuearray[$n]), $datatype);
						}
						if ($comparator == 'n' || $comparator == 'k')
							$advorsqls = implode(" and ", $advorsql);
						else
							$advorsqls = implode(" or ", $advorsql);
						$advfiltersql = " (" . $advorsqls . ") ";
					}
					elseif ($comparator == 'bw' && count($valuearray) == 2) {
						$advfiltersql = "(" . $columns[0] . "." . $columns[1] . " between '" . getValidDBInsertDateTimeValue(trim($valuearray[0]), $datatype) . "' and '" . getValidDBInsertDateTimeValue(trim($valuearray[1]), $datatype) . "')";
					}
					elseif ($comparator == 'y') {
						$advfiltersql = sprintf("(%s.%s IS NULL OR %s.%s = '')", $columns[0], $columns[1], $columns[0], $columns[1]);
					} else {
						if ($this->customviewmodule == "Calendar" && ($columns[1] == "status" || $columns[1] == "eventstatus")) {
							if (getFieldVisibilityPermission("Calendar", $current_user->id, 'taskstatus') == '0') {
								$advfiltersql = "case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
							else
								$advfiltersql = "vtiger_activity.eventstatus" . $this->getAdvComparator($comparator, trim($value), $datatype);
						}
						elseif ($this->customviewmodule == "Documents" && $columns[1] == 'folderid') {
							$advfiltersql = "vtiger_attachmentsfolder.foldername" . $this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($this->customviewmodule == "Assets") {
							if ($columns[1] == 'account') {
								$advfiltersql = "vtiger_account.accountname" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
							if ($columns[1] == 'product') {
								$advfiltersql = "vtiger_products.productname" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
							if ($columns[1] == 'invoiceid') {
								$advfiltersql = "vtiger_invoice.subject" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
						} else {
							$advfiltersql = $this->getRealValues($columns[0], $columns[1], $comparator, trim($value), $datatype);
						}
					}

					$advfiltergroupsql .= $advfiltersql;
					if ($columncondition != NULL && $columncondition != '' && count($groupcolumns) > $columnindex) {
						$advfiltergroupsql .= ' ' . $columncondition . ' ';
					}
				}
			}

			if (trim($advfiltergroupsql) != "") {
				$advfiltergroupsql = "( $advfiltergroupsql ) ";
				if ($groupcondition != NULL && $groupcondition != '' && $advfilter > $groupid) {
					$advfiltergroupsql .= ' ' . $groupcondition . ' ';
				}

				$advcvsql .= $advfiltergroupsql;
			}
		}
		if (trim($advcvsql) != "")
			$advcvsql = '(' . $advcvsql . ')';
		return $advcvsql;
	}
    function getStdFilterList($relblockid) {
        if ($this->_stdfilterlist !== false) {
            return $this->_stdfilterlist;
        }
        $adb = PearDatabase::getInstance();
        global $modules;
        global $log;

        $stdfiltersql = "select vtiger_emakertemplates_relblockdatefilter.* from vtiger_emakertemplates_relblocks";
        $stdfiltersql .= " inner join vtiger_emakertemplates_relblockdatefilter on vtiger_emakertemplates_relblocks.relblockid = vtiger_emakertemplates_relblockdatefilter.datefilterid";
        $stdfiltersql .= " where vtiger_emakertemplates_relblocks.relblockid = ?";

        $result = $adb->pquery($stdfiltersql, array($relblockid));
        $stdfilterrow = $adb->fetch_array($result);
        if (isset($stdfilterrow)) {
            $fieldcolname = $stdfilterrow["datecolumnname"];
            $datefilter = $stdfilterrow["datefilter"];
            $startdate = $stdfilterrow["startdate"];
            $enddate = $stdfilterrow["enddate"];

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
                    $selectedfields[0] = "vtiger_crmentity";
                $this->queryPlanner->addTable($selectedfields[0]);
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:59'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:59'";
                    }
                }
            }
        }
        $this->_stdfilterlist = $stdfilterlist;

        $log->info("ReportRun :: Successfully returned getStdFilterList" . $relblockid);
        return $stdfilterlist;
    }
    function getStandardCriterialSql($relblockid) {
        $adb = PearDatabase::getInstance();
        global $modules;
        global $log;

        $sreportstdfiltersql = "select vtiger_emakertemplates_relblockdatefilter.* from vtiger_emakertemplates_relblocks";
        $sreportstdfiltersql .= " inner join vtiger_emakertemplates_relblockdatefilter on vtiger_emakertemplates_relblocks.relblockid = vtiger_emakertemplates_relblockdatefilter.datefilterid";
        $sreportstdfiltersql .= " where vtiger_emakertemplates_relblocks.relblockid = ?";

        $result = $adb->pquery($sreportstdfiltersql, array($relblockid));
        $noofrows = $adb->num_rows($result);

        for ($i = 0; $i < $noofrows; $i++) {
            $fieldcolname = $adb->query_result($result, $i, "datecolumnname");
            $datefilter = $adb->query_result($result, $i, "datefilter");
            $startdate = $adb->query_result($result, $i, "startdate");
            $enddate = $adb->query_result($result, $i, "enddate");

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
                    $selectedfields[0] = "vtiger_crmentity";
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . "' and '" . $startenddate[1] . "'";
                    }
                }
            }
        }
        $log->info("ReportRun :: Successfully returned getStandardCriterialSql" . $relblockid);
        return $sSQL;
    }
    function getStandarFiltersStartAndEndDate($type) {
        $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $currentmonth1 = date("Y-m-t");
        $lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
        $lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
        $nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
        $nextmonth1 = date("Y-m-t", strtotime("+1 Month"));
        $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
        $lastweek1 = date("Y-m-d", strtotime("-1 week Saturday"));
        $thisweek0 = date("Y-m-d", strtotime("-1 week Sunday"));
        $thisweek1 = date("Y-m-d", strtotime("this Saturday"));
        $nextweek0 = date("Y-m-d", strtotime("this Sunday"));
        $nextweek1 = date("Y-m-d", strtotime("+1 week Saturday"));
        $next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
        $next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
        $next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
        $next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
        $next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));
        $last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
        $last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
        $last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
        $last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
        $last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));
        $currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
        $lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
        $lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
        $nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
        $nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

        if (date("m") <= 3) {
            $cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
        } else if (date("m") > 3 and date("m") <= 6) {
            $pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
        } else if (date("m") > 6 and date("m") <= 9) {
            $nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
        } else if (date("m") > 9 and date("m") <= 12) {
            $nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
            $cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
        }

        if ($type == "today") {
            $datevalue[0] = $today;
            $datevalue[1] = $today;
        } elseif ($type == "yesterday") {
            $datevalue[0] = $yesterday;
            $datevalue[1] = $yesterday;
        } elseif ($type == "tomorrow") {
            $datevalue[0] = $tomorrow;
            $datevalue[1] = $tomorrow;
        } elseif ($type == "thisweek") {
            $datevalue[0] = $thisweek0;
            $datevalue[1] = $thisweek1;
        } elseif ($type == "lastweek") {
            $datevalue[0] = $lastweek0;
            $datevalue[1] = $lastweek1;
        } elseif ($type == "nextweek") {
            $datevalue[0] = $nextweek0;
            $datevalue[1] = $nextweek1;
        } elseif ($type == "thismonth") {
            $datevalue[0] = $currentmonth0;
            $datevalue[1] = $currentmonth1;
        } elseif ($type == "lastmonth") {
            $datevalue[0] = $lastmonth0;
            $datevalue[1] = $lastmonth1;
        } elseif ($type == "nextmonth") {
            $datevalue[0] = $nextmonth0;
            $datevalue[1] = $nextmonth1;
        } elseif ($type == "next7days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next7days;
        } elseif ($type == "next30days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next30days;
        } elseif ($type == "next60days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next60days;
        } elseif ($type == "next90days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next90days;
        } elseif ($type == "next120days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next120days;
        } elseif ($type == "last7days") {
            $datevalue[0] = $last7days;
            $datevalue[1] = $today;
        } elseif ($type == "last30days") {
            $datevalue[0] = $last30days;
            $datevalue[1] = $today;
        } elseif ($type == "last60days") {
            $datevalue[0] = $last60days;
            $datevalue[1] = $today;
        } else if ($type == "last90days") {
            $datevalue[0] = $last90days;
            $datevalue[1] = $today;
        } elseif ($type == "last120days") {
            $datevalue[0] = $last120days;
            $datevalue[1] = $today;
        } elseif ($type == "thisfy") {
            $datevalue[0] = $currentFY0;
            $datevalue[1] = $currentFY1;
        } elseif ($type == "prevfy") {
            $datevalue[0] = $lastFY0;
            $datevalue[1] = $lastFY1;
        } elseif ($type == "nextfy") {
            $datevalue[0] = $nextFY0;
            $datevalue[1] = $nextFY1;
        } elseif ($type == "nextfq") {
            $datevalue[0] = $nFq;
            $datevalue[1] = $nFq1;
        } elseif ($type == "prevfq") {
            $datevalue[0] = $pFq;
            $datevalue[1] = $pFq1;
        } elseif ($type == "thisfq") {
            $datevalue[0] = $cFq;
            $datevalue[1] = $cFq1;
        } else {
            $datevalue[0] = "";
            $datevalue[1] = "";
        }
        return $datevalue;
    }
    function replaceSpecialChar($selectedfield) {
        $selectedfield = decode_html(decode_html($selectedfield));
        preg_match('/&/', $selectedfield, $matches);
        if (!empty($matches)) {
            $selectedfield = str_replace('&', 'and', ($selectedfield));
        }
        return $selectedfield;
    }
    function getRelatedModulesQuery($module, $secmodule) {
        global $log, $current_user;
        $query = '';
        if ($secmodule != '') {
            $secondarymodule = explode(":", $secmodule);
            foreach ($secondarymodule as $value) {
                $foc = CRMEntity::getInstance($value);
                $this->queryPlanner->addTable('vtiger_crmentity' . $value);
                $focQuery = $foc->generateReportsSecQuery($module, $value, $this->queryPlanner);
                if ($focQuery) {
                    $query .= $focQuery . getNonAdminAccessControlQuery($value, $current_user, $value);
                }
            }
        }
        $query = str_replace(array("  ",'left join as'), array(" ",'left join'), $query);
        $log->info("ReportRun :: Successfully returned getRelatedModulesQuery" . $secmodule);
        return $query;
    }
    function getReportsQuery($module) {
        global $log, $current_user;
        $secondary_module = "'";
        $secondary_module .= str_replace(":", "','", $this->secondarymodule);
        $secondary_module .="'";

        if ($module == "Leads") {
            $query = "from vtiger_leaddetails 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_leaddetails.leadid 
				inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid=vtiger_leaddetails.leadid 
				inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid=vtiger_leadsubdetails.leadsubscriptionid 
				inner join vtiger_leadscf on vtiger_leaddetails.leadid = vtiger_leadscf.leadid 
				left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0";
        } else if ($module == "Accounts") {
            $query = "from vtiger_account 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid 
				inner join vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid 
				inner join vtiger_accountshipads on vtiger_account.accountid=vtiger_accountshipads.accountaddressid 
				inner join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
				left join vtiger_groups as vtiger_groupsAccounts on vtiger_groupsAccounts.groupid = vtiger_crmentity.smownerid
				left join vtiger_account as vtiger_accountAccounts on vtiger_accountAccounts.accountid = vtiger_account.parentid
				left join vtiger_users as vtiger_usersAccounts on vtiger_usersAccounts.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0 ";
        } else if ($module == "Contacts") {
            $query = "from vtiger_contactdetails
				inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
				inner join vtiger_contactaddress on vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid 
				inner join vtiger_customerdetails on vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
				inner join vtiger_contactsubdetails on vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid 
				inner join vtiger_contactscf on vtiger_contactdetails.contactid = vtiger_contactscf.contactid 
				left join vtiger_groups vtiger_groupsContacts on vtiger_groupsContacts.groupid = vtiger_crmentity.smownerid
				left join vtiger_contactdetails as vtiger_contactdetailsContacts on vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto
				left join vtiger_account as vtiger_accountContacts on vtiger_accountContacts.accountid = vtiger_contactdetails.accountid 
				left join vtiger_users as vtiger_usersContacts on vtiger_usersContacts.id = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "Potentials") {
            $query = "from vtiger_potential 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid 
				inner join vtiger_potentialscf on vtiger_potentialscf.potentialid = vtiger_potential.potentialid
				left join vtiger_account as vtiger_accountPotentials on vtiger_potential.related_to = vtiger_accountPotentials.accountid
				left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_potential.related_to = vtiger_contactdetailsPotentials.contactid 
				left join vtiger_campaign as vtiger_campaignPotentials on vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid
				left join vtiger_groups vtiger_groupsPotentials on vtiger_groupsPotentials.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersPotentials on vtiger_usersPotentials.id = vtiger_crmentity.smownerid  
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid  
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0 ";
        } else if ($module == "Products") {
            $query = "from vtiger_products 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid 
				left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid 
				left join vtiger_users as vtiger_usersProducts on vtiger_usersProducts.id = vtiger_products.handler 
				left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id 
				LEFT JOIN (
						SELECT vtiger_products.productid, 
								(CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
									ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
								) AS actual_unit_price
						FROM vtiger_products
						LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
						LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
						AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
				) AS innerProduct ON innerProduct.productid = vtiger_products.productid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "HelpDesk") {
            $query = "from vtiger_troubletickets 
				inner join vtiger_crmentity  
				on vtiger_crmentity.crmid=vtiger_troubletickets.ticketid 
				inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
				left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id
				left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid 
				left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_crmentityRelHelpDesk.crmid
				left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id 
				left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersHelpDesk on vtiger_crmentity.smownerid=vtiger_usersHelpDesk.id 
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id 
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0 ";
        } else if ($module == "Calendar") {
            $query = "from vtiger_activity 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
				left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_crmentity.crmid
				left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid 
				left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_cntactivityrel.contactid
				left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid
				left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid
				left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid
				left join vtiger_crmentity as vtiger_crmentityRelCalendar on vtiger_crmentityRelCalendar.crmid = vtiger_seactivityrel.crmid
				left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_crmentityRelCalendar.crmid
				left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_potential as vtiger_potentialRelCalendar on vtiger_potentialRelCalendar.potentialid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_quotes as vtiger_quotesRelCalendar on vtiger_quotesRelCalendar.quoteid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_purchaseorder as vtiger_purchaseorderRelCalendar on vtiger_purchaseorderRelCalendar.purchaseorderid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_invoice as vtiger_invoiceRelCalendar on vtiger_invoiceRelCalendar.invoiceid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_salesorder as vtiger_salesorderRelCalendar on vtiger_salesorderRelCalendar.salesorderid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_crmentityRelCalendar.crmid
				left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_crmentityRelCalendar.crmid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype != 'Emails')";
        } else if ($module == "Quotes") {
            $query = "from vtiger_quotes 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid 
				inner join vtiger_quotesbillads on vtiger_quotes.quoteid=vtiger_quotesbillads.quotebilladdressid 
				inner join vtiger_quotesshipads on vtiger_quotes.quoteid=vtiger_quotesshipads.quoteshipaddressid
        left join vtiger_inventoryproductrel as vtiger_inventoryproductrelQuotes on vtiger_quotes.quoteid = vtiger_inventoryproductrelQuotes.id
				left join vtiger_products as vtiger_productsQuotes on vtiger_productsQuotes.productid = vtiger_inventoryproductrelQuotes.productid  
				left join vtiger_service as vtiger_serviceQuotes on vtiger_serviceQuotes.serviceid = vtiger_inventoryproductrelQuotes.productid
        left join vtiger_quotescf on vtiger_quotes.quoteid = vtiger_quotescf.quoteid
				left join vtiger_groups as vtiger_groupsQuotes on vtiger_groupsQuotes.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersQuotes on vtiger_usersQuotes.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager
				left join vtiger_potential as vtiger_potentialRelQuotes on vtiger_potentialRelQuotes.potentialid = vtiger_quotes.potentialid
				left join vtiger_contactdetails as vtiger_contactdetailsQuotes on vtiger_contactdetailsQuotes.contactid = vtiger_quotes.contactid
				left join vtiger_account as vtiger_accountQuotes on vtiger_accountQuotes.accountid = vtiger_quotes.accountid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "PurchaseOrder") {
            $query = "from vtiger_purchaseorder 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_purchaseorder.purchaseorderid 
				inner join vtiger_pobillads on vtiger_purchaseorder.purchaseorderid=vtiger_pobillads.pobilladdressid 
				inner join vtiger_poshipads on vtiger_purchaseorder.purchaseorderid=vtiger_poshipads.poshipaddressid
        left join vtiger_inventoryproductrel as vtiger_inventoryproductrelPurchaseOrder on vtiger_purchaseorder.purchaseorderid = vtiger_inventoryproductrelPurchaseOrder.id
				left join vtiger_products as vtiger_productsPurchaseOrder on vtiger_productsPurchaseOrder.productid = vtiger_inventoryproductrelPurchaseOrder.productid  
				left join vtiger_service as vtiger_servicePurchaseOrder on vtiger_servicePurchaseOrder.serviceid = vtiger_inventoryproductrelPurchaseOrder.productid
        left join vtiger_purchaseordercf on vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid
				left join vtiger_groups as vtiger_groupsPurchaseOrder on vtiger_groupsPurchaseOrder.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersPurchaseOrder on vtiger_usersPurchaseOrder.id = vtiger_crmentity.smownerid 
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid 
				left join vtiger_vendor as vtiger_vendorRelPurchaseOrder on vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendorid 
				left join vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder on vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contactid 
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "Invoice") {
            $query = "from vtiger_invoice 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
				inner join vtiger_invoicebillads on vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid 
				inner join vtiger_invoiceshipads on vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid
        left join vtiger_inventoryproductrel as vtiger_inventoryproductrelInvoice on vtiger_invoice.invoiceid = vtiger_inventoryproductrelInvoice.id
				left join vtiger_products as vtiger_productsInvoice on vtiger_productsInvoice.productid = vtiger_inventoryproductrelInvoice.productid
				left join vtiger_service as vtiger_serviceInvoice on vtiger_serviceInvoice.serviceid = vtiger_inventoryproductrelInvoice.productid
        left join vtiger_salesorder as vtiger_salesorderInvoice on vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorderid
				left join vtiger_invoicecf on vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid 
				left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.accountid
				left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_contactdetailsInvoice.contactid = vtiger_invoice.contactid
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "SalesOrder") {
            $query = "from vtiger_salesorder 
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_salesorder.salesorderid 
				inner join vtiger_sobillads on vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid 
				inner join vtiger_soshipads on vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid
        left join vtiger_inventoryproductrel as vtiger_inventoryproductrelSalesOrder on vtiger_salesorder.salesorderid = vtiger_inventoryproductrelSalesOrder.id
				left join vtiger_products as vtiger_productsSalesOrder on vtiger_productsSalesOrder.productid = vtiger_inventoryproductrelSalesOrder.productid  
				left join vtiger_service as vtiger_serviceSalesOrder on vtiger_serviceSalesOrder.serviceid = vtiger_inventoryproductrelSalesOrder.productid
        left join vtiger_contactdetails as vtiger_contactdetailsSalesOrder on vtiger_contactdetailsSalesOrder.contactid = vtiger_salesorder.contactid
				left join vtiger_quotes as vtiger_quotesSalesOrder on vtiger_quotesSalesOrder.quoteid = vtiger_salesorder.quoteid				
				left join vtiger_account as vtiger_accountSalesOrder on vtiger_accountSalesOrder.accountid = vtiger_salesorder.accountid
				left join vtiger_potential as vtiger_potentialRelSalesOrder on vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potentialid 
				left join vtiger_invoice_recurring_info on vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
				left join vtiger_groups as vtiger_groupsSalesOrder on vtiger_groupsSalesOrder.groupid = vtiger_crmentity.smownerid
				left join vtiger_users as vtiger_usersSalesOrder on vtiger_usersSalesOrder.id = vtiger_crmentity.smownerid 
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid 
				" . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else if ($module == "Campaigns") {
            $query = "from vtiger_campaign
			        inner join vtiger_campaignscf as vtiger_campaignscf on vtiger_campaignscf.campaignid=vtiger_campaign.campaignid   
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_campaign.campaignid
				left join vtiger_products as vtiger_productsCampaigns on vtiger_productsCampaigns.productid = vtiger_campaign.product_id
				left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentity.smownerid
		                left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
		                left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                                " . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
                    getNonAdminAccessControlQuery($this->primarymodule, $current_user) . "
				where vtiger_crmentity.deleted=0";
        } else {
            if ($module != '') {
                $focus = CRMEntity::getInstance($module);
                $query = $this->generateReportsQuery($module)
                        . $this->getRelatedModulesQuery($module, $this->secondarymodule);

                switch ($this->secondarymodule) {
                    case "HelpDesk":
                        $query .= " left join vtiger_ticketcomments on vtiger_ticketcomments.ticketid=vtiger_troubletickets.ticketid ";
                        break;

                    case "Faq":
                        $query .= " left join vtiger_products as vtiger_productsfaq on vtiger_productsfaq.productid=vtiger_faq.product_id 
                                    left join vtiger_faqcomments on vtiger_faqcomments.faqid=vtiger_faq.id ";
                        break;
                }

                $query .= getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
                        " WHERE vtiger_crmentity.deleted=0";
            }
        }

        if ($module == 'PriceBooks' && $this->secondarymodule == 'Products') {
            $query = str_replace('left join vtiger_crmentity as vtiger_crmentityProducts', 'inner join vtiger_crmentity as vtiger_crmentityProducts', $query);
        } elseif ($module == "Potentials" && $this->secondarymodule == "Contacts") {
            /*
            $query = str_replace("left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_potential.related_to = vtiger_contactdetailsPotentials.contactid", "left join vtiger_contpotentialrel on vtiger_contpotentialrel.potentialid = vtiger_potential.potentialid
                                  left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_contpotentialrel.contactid = vtiger_contactdetailsPotentials.contactid ", $query);

            $query = str_replace("on vtiger_potential.related_to=vtiger_contactdetails.contactid", "on vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid", $query);
            */
        }

        $log->info("ReportRun :: Successfully returned getReportsQuery" . $module);
        
        $sdpos = strpos($query, "vtiger_crmentity".$this->secondarymodule.".");
        if ($sdpos !== false) $query .= " AND vtiger_crmentity".$this->secondarymodule.".deleted= '0'";
        
        $query .= " AND vtiger_crmentity.crmid= '" . $this->crmid . "'";
        return $query;
    }

    function getSortColSql($columnlist, $relblockid) {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT columnname, sortorder
                FROM vtiger_emakertemplates_relblocksortcol
                WHERE relblockid=?
                ORDER BY sortcolid ASC";
        $result = $adb->pquery($sql, array($relblockid));
        $sortColList = array();
        while ($row = $adb->fetchByAssoc($result)) {
            if (isset($columnlist[$row["columnname"]])) {
                $sortDir = ($row["sortorder"] == "Descending" ? "DESC" : "ASC");
                $columnName = $columnlist[$row["columnname"]];
                $columnName = str_replace(" as ", " AS ", $columnName);
                $tmpArr = explode(" AS ", $columnName);
                $columnAlias = $tmpArr[count($tmpArr) - 1];
                if (isset($columnAlias)) {
                    $columnName = trim($columnAlias, " '");
                }
                $sortColList[$row["columnname"]] = $columnName . " " . $sortDir;
            }
        }

        $sortColSql = "";
        if (count($sortColList) > 0) {
            $sortColSql = "ORDER BY ";
            $sortColSql .= implode(", ", $sortColList);
        }

        return $sortColSql;
    }
    function sGetSQLforReport($relblockid) {
        global $log;
        $columnlist = $this->getQueryColumnsList($relblockid);
        $sortColsql = $this->getSortColSql($columnlist, $relblockid);
        $stdfilterlist = $this->getStdFilterList($relblockid);
        $advfiltersql = $this->getAdvFilterSql($relblockid);
        $this->totallist = $columnstotallist;
        $selectlist = $columnlist;
        if (isset($selectlist)) {
            $selectedcolumns = implode(", ", $selectlist);
        }
        if (isset($stdfilterlist)) {
            $stdfiltersql = implode(", ", $stdfilterlist);
        }
        if (isset($columnstotallist)) {
            $columnstotalsql = implode(", ", $columnstotallist);
        }
        if ($stdfiltersql != "") {
            $wheresql = " and " . $stdfiltersql;
        }
        if ($advfiltersql != "") {
            $wheresql .= " and " . $advfiltersql;
        }
        $reportquery = $this->getReportsQuery($this->primarymodule);

        if ($this->secondarymodule != '' && strpos($reportquery, 'left join vtiger_crmentityrel as ') !== false) {
            $Exploded1 = explode('left join vtiger_crmentityrel as ', $reportquery);
            $Exploded2 = explode(' ON ', $Exploded1[1]);
            $relalias = $Exploded2[0];
            $wheresql .= " and ($relalias.module='" . $this->secondarymodule . "' OR $relalias.relmodule='" . $this->secondarymodule . "') ";
        }
        $allColumnsRestricted = false;

        if ($selectedcolumns == '') {
            $selectedcolumns = "''"; // "''" to get blank column name
            $allColumnsRestricted = true;
        }
        if (in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) OR in_array($this->secondarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder'))) {
            $selectedcolumns = ' distinct ' . $selectedcolumns;
        }
        $reportquery = "select " . $selectedcolumns . " " . $reportquery . " " . $wheresql;
        $reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);
        $reportquery .= " " . $sortColsql;
        if ($allColumnsRestricted) {
            $reportquery .= " limit 0";
        }
        $log->info("ReportRun :: Successfully returned sGetSQLforReport" . $relblockid);
        return $reportquery;
    }
    function GenerateReport() {
        global $adb, $current_user, $php_max_execution_time;
        global $modules, $app_strings;
        global $mod_strings, $current_language;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        $modules_selected = array();
        $modules_selected[] = $this->primarymodule;
        if (!empty($this->secondarymodule)) {
            $sec_modules = split(":", $this->secondarymodule);
            for ($i = 0; $i < count($sec_modules); $i++) {
                $modules_selected[] = $sec_modules[$i];
            }
        }
        $currencyfieldres = $adb->pquery("SELECT tabid, fieldlabel, fieldname, uitype from vtiger_field WHERE uitype in (71,72,10)", array());
        if ($currencyfieldres) {
            foreach ($currencyfieldres as $currencyfieldrow) {
                $modprefixedlabel = getTabModuleName($currencyfieldrow['tabid']) . ' ' . $currencyfieldrow['fieldlabel'];
                $modprefixedlabel = str_replace(' ', '_', $modprefixedlabel);
                $modprefixedname = $currencyfieldrow['fieldname'];  // ITS4YOU VlZa		
                $uiType = $currencyfieldrow['uitype'];
                if ($uiType != 10 && $uiType != 101) {
                    if (!in_array($modprefixedlabel, $this->convert_currency) && !in_array($modprefixedlabel, $this->append_currency_symbol_to_value)) {
                        $this->convert_currency[] = $modprefixedlabel;
                    }
                } else {
                    if ($uiType == 10 &&  !in_array($modprefixedname, $this->ui10_fields)) {
                        $this->ui10_fields[] = $modprefixedlabel;
                    } elseif($uiType == 101 && !in_array($modprefixedlabel, $this->ui101_fields)) {
                        $this->ui101_fields[] = $modprefixedlabel;
                    }
                }
            }
        }
        $sSQL = $this->sGetSQLforReport($this->relblockid);
        $result = $adb->pquery($sSQL,array());
        if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
            $picklistarray = $this->getAccessPickListValues();

        if ($result) {
            $y = $adb->num_fields($result);
            $custom_field_values = $adb->fetch_array($result);
            $column_definitions = $adb->getFieldsDefinition($result);
            $cridx = 1;
            $toShow = false;
            do {
                for ($i = 0; $i < $y; $i++) {
                    $fld = $adb->field_name($result, $i);
                    $fld_type = $column_definitions[$i]->type;
                    
                    $fieldvalue = $this->getEMAILMakerFieldValue($this, $picklistarray, $fld, $custom_field_values, $i);
                    
                    list($module, $fieldLabel) = explode('_', $fld->name, 2);
                    
                    if ($fieldvalue != "-" && $fieldLabel != "listprice")      // listprice is special field for PriceBook
                        $toShow = true;
                      
                    $row_data[$fieldLabel] = $fieldvalue;
                    
                    if ($fieldLabel == "Assigned_To")
                        $row_data["assigned_user_id"] = $fieldvalue;
                }
                if ($toShow)
                    $row_data["cridx"] = $cridx++;

                set_time_limit($php_max_execution_time);

                $return_data[] = $row_data;
            }while ($custom_field_values = $adb->fetch_array($result));

            return $return_data;
        }
    }
    public function SetEMAILLanguage($language) {
        $this->EMAILLanguage = $language;
    }
    function getEntityImage($ival) {
        global $site_URL, $adb;
        $siteurl = trim($site_URL, "/");
        $result = "";
        if ($ival != "") {
            switch ($this->secondarymodule) {
                case "Contacts":
                    $id = $ival;
                    $query = "SELECT vtiger_attachments.*
							FROM vtiger_contactdetails
							INNER JOIN vtiger_seattachmentsrel
								ON vtiger_contactdetails.contactid=vtiger_seattachmentsrel.crmid
							INNER JOIN vtiger_attachments
								ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
							INNER JOIN vtiger_crmentity
								ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
							WHERE deleted=0 AND vtiger_contactdetails.contactid=?";

                    $res = $adb->pquery($query, array($id));
                    $num_rows = $adb->num_rows($res);
                    if ($num_rows > 0) {
	                    $row = $adb->query_result_rowdata($res);

	                    if(!isset($row['storedname']) || empty($row['storedname'])) {
		                    $row['storedname'] = $row['name'];
	                    }

                        $image_src = $row["path"] . $row["attachmentsid"] . "_" . $row["storedname"];
                        $result = "<img src='" . $siteurl . "/" . $image_src . "' />";
                    }
                    break;
                case "Products":
                    $attid = "";
                    $id = $ival;
                    $saved_sql1 = "SELECT attachmentid FROM vtiger_emakertemplates_images WHERE crmid=?";
                    $result1 = $adb->pquery($saved_sql1, array($id));
                    if ($adb->num_rows($result1) > 0) {
                        $saved_sql = "SELECT vtiger_attachments.*, vtiger_emakertemplates_images.width,
                                             vtiger_emakertemplates_images.height
                                      FROM vtiger_emakertemplates_images
                                      LEFT JOIN vtiger_attachments
                                             ON vtiger_attachments.attachmentsid=vtiger_emakertemplates_images.attachmentid
                                      INNER JOIN vtiger_crmentity
                                              ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE deleted=0 AND vtiger_emakertemplates_images.crmid=?";
                    } else {
                        $saved_sql = "SELECT vtiger_attachments.*, '83' AS width, '' AS height
                                      FROM vtiger_attachments
                                      LEFT JOIN vtiger_seattachmentsrel
                                             ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                                      INNER JOIN vtiger_crmentity
                                              ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE vtiger_crmentity.deleted=0 AND vtiger_seattachmentsrel.crmid=?
                                      ORDER BY attachmentsid LIMIT 1";
                    }

                    $saved_res = $adb->pquery($saved_sql, array($id));
                    if ($adb->num_rows($saved_res) > 0) {
	                    $row = $adb->query_result_rowdata($res);

	                    if(!isset($row['storedname']) || empty($row['storedname'])) {
		                    $row['storedname'] = $row['name'];
	                    }

                        $path = $row["path"];
                        $attid = $row["attachmentsid"];
                        $name = $row["storedname"];
                        $attwidth = $row["width"];
                        $attheight = $row["height"];
                    }

                    if ($attid != "") {
                        if ($attwidth > 0)
                            $width = " width='" . $attwidth . "' ";
                        if ($attheight > 0)
                            $height = " height='" . $attheight . "' ";
                        $result = "<img src='" . $siteurl . "/" . $path . $attid . "_" . $name . "' " . $width . $height . "/>";
                    }
                    break;
            }
        }
        return $result;
    }
    function getLstringforReportHeaders($fldname) {
        global $modules, $current_language, $current_user, $app_strings;
        $rep_header = ltrim(str_replace($modules, " ", $fldname));
        $rep_header_temp = preg_replace("/\s+/", "_", $rep_header);
        $rep_module = preg_replace("/_$rep_header_temp/", "", $fldname);
        $temp_mod_strings = return_module_language($current_language, $rep_module);
        $rep_header = decode_html($rep_header);
        $curr_symb = "";
        if (in_array($fldname, $this->convert_currency)) {
            $curr_symb = " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
        }
        if ($temp_mod_strings[$rep_header] != '') {
            $rep_header = $temp_mod_strings[$rep_header];
        }
        $rep_header .=$curr_symb;
        return $rep_header;
    }
    function getAccessPickListValues() {
        $adb = PearDatabase::getInstance();
        global $current_user;
        $id = array(getTabid($this->primarymodule));
        if ($this->secondarymodule != '')
            array_push($id, getTabid($this->secondarymodule));

        $query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(' . generateQuestionMarks($id) . ') and uitype in (15,33,55)'; //and columnname in (?)';
        $result = $adb->pquery($query, $id); //,$select_column));
        $roleid = $current_user->roleid;
        $subrole = getRoleSubordinates($roleid);
        if (count($subrole) > 0) {
            $roleids = $subrole;
            array_push($roleids, $roleid);
        } else {
            $roleids = $roleid;
        }

        $temp_status = Array();
        for ($i = 0; $i < $adb->num_rows($result); $i++) {
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $tabid = $adb->query_result($result, $i, "tabid");
            $uitype = $adb->query_result($result, $i, "uitype");

            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $keyvalue = getTabModuleName($tabid) . "_" . $fieldlabel1;
            $fieldvalues = Array();
            if (count($roleids) > 1) {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode($roleids, "\",\"") . "\") and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            } else {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            }
            if ($fieldname != 'firstname')
                $mulselresult = $adb->pquery($mulsel,array());
            for ($j = 0; $j < $adb->num_rows($mulselresult); $j++) {
                $fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
                if (in_array($fldvalue, $fieldvalues))
                    continue;
                $fieldvalues[] = $fldvalue;
            }
            $field_count = count($fieldvalues);
            if ($uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
                $temp_count = count($temp_status[$keyvalue]);
                if ($temp_count > 0) {
                    for ($t = 0; $t < $field_count; $t++) {
                        $temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
                    }
                    $fieldvalues = $temp_status[$keyvalue];
                }
                else
                    $temp_status[$keyvalue] = $fieldvalues;
            }

            if ($uitype == 33)
                $fieldlists[1][$keyvalue] = $fieldvalues;
            else if ($uitype == 55 && $fieldname == 'salutationtype')
                $fieldlists[$keyvalue] = $fieldvalues;
            else if ($uitype == 15)
                $fieldlists[$keyvalue] = $fieldvalues;
        }
        return $fieldlists;
    }

    public function generateReportsQuery($module){
        $adb = PearDatabase::getInstance();
        $primary = CRMEntity::getInstance($module);

        vtlib_setup_modulevars($module, $primary);
        $moduletable = $primary->table_name;
        $moduleindex = $primary->table_index;
        $modulecftable = $primary->customFieldTable[0];
        $modulecfindex = $primary->customFieldTable[1];

        if (isset($modulecftable)) {
            $cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
        } else {
            $cfquery = '';
        }
        $query = "from $moduletable $cfquery
                inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex
                left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby
                left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

        $fields_query = $adb->pquery("SELECT vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid AND vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)", array($module));

        if ($adb->num_rows($fields_query) > 0) {
            for ($i = 0; $i < $adb->num_rows($fields_query); $i++) {
                $field_name = $adb->query_result($fields_query, $i, 'fieldname');
                $field_id = $adb->query_result($fields_query, $i, 'fieldid');
                $tab_name = $adb->query_result($fields_query, $i, 'tablename');
                $ui10_modules_query = $adb->pquery("SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?", array($field_id));

                if ($adb->num_rows($ui10_modules_query) > 0) {
                    $query.= " left join vtiger_crmentity as vtiger_crmentityRel$module$field_id on vtiger_crmentityRel$module$field_id.crmid = $tab_name.$field_name and vtiger_crmentityRel$module$field_id.deleted=0";
                    for ($j = 0; $j < $adb->num_rows($ui10_modules_query); $j++) {
                        $rel_mod = $adb->query_result($ui10_modules_query, $j, 'relmodule');
                        $rel_obj = CRMEntity::getInstance($rel_mod);
                        vtlib_setup_modulevars($rel_mod, $rel_obj);

                        $rel_tab_name = $rel_obj->table_name;
                        $rel_tab_index = $rel_obj->table_index;
                        $query.= " left join $rel_tab_name as " . $rel_tab_name . "Rel$module$field_id on " . $rel_tab_name . "Rel$module$field_id.$rel_tab_index = vtiger_crmentityRel$module$field_id.crmid";
                    }
                }
            }
        }
        return $query;
    }
    
    public function getEMAILMakerFieldValue ($report, $picklistArray, $dbField, $valueArray, $fieldName) {
    	global $current_user, $default_charset;
    
    	$db = PearDatabase::getInstance();
    	$value = $valueArray[$fieldName];
    	$fld_type = $dbField->type;
    	list($module, $fieldLabel) = explode('_', $dbField->name, 2);
    	$fieldInfo = $this->getFieldByEMAILMakerLabel($module, $fieldLabel);
        $fieldType = null;
    	$fieldvalue = $value;
    	if(!empty($fieldInfo)) {
    		$field = WebserviceField::fromArray($db, $fieldInfo);
    		$fieldType = $field->getFieldDataType();
    	}
      	if ($fieldType == 'currency' && $value != '') {
    		if($field->getUIType() == '72') {
    			$curid_value = explode("::", $value);
    			$currency_id = $curid_value[0];
    			$currency_value = $curid_value[1];
    			$cur_sym_rate = getCurrencySymbolandCRate($currency_id);
    			if($value!='') {
    				if(($dbField->name == 'Products_Unit_Price')) { // need to do this only for Products Unit Price
    					if ($currency_id != 1) {
    						$currency_value = (float)$cur_sym_rate['rate'] * (float)$currency_value;
    					}
    				}
    
    				$formattedCurrencyValue = CurrencyField::convertToUserFormat($currency_value, null, true);
    				$fieldvalue = CurrencyField::appendCurrencySymbol($formattedCurrencyValue, $cur_sym_rate['symbol']);
    			}
    		} else {
    			$currencyField = new CurrencyField($value);
    			$fieldvalue = $currencyField->getDisplayValue();
    		}
    
    	} elseif ($dbField->name == "PurchaseOrder_Currency" || $dbField->name == "SalesOrder_Currency"
    				|| $dbField->name == "Invoice_Currency" || $dbField->name == "Quotes_Currency" || $dbField->name == "PriceBooks_Currency") {
    		if($value!='') {
    			$fieldvalue = getTranslatedCurrencyString($value);
    		}
    	} elseif (in_array($dbField->name,$this->ui101_fields) && !empty($value)) {
    		$entityNames = getEntityName('Users', $value);
    		$fieldvalue = $entityNames[$value];
    	} elseif( $fieldType == 'date' && !empty($value)) {
    		if($module == 'Calendar' && $field->getFieldName() == 'due_date') {
    			$endTime = $valueArray['calendar_end_time'];
    			if(empty($endTime)) {
    				$recordId = $valueArray['calendar_id'];
    				$endTime = getSingleFieldValue('vtiger_activity', 'time_end', 'activityid', $recordId);
    			}
    			$date = new DateTimeField($value.' '.$endTime);
    			$fieldvalue = $date->getDisplayDate();
    		} else {
    			$fieldvalue = DateTimeField::convertToUserFormat($value);
    		}
    	} elseif( $fieldType == "datetime" && !empty($value)) {
    		$date = new DateTimeField($value);
    		$fieldvalue = $date->getDisplayDateTimeValue();
    	} elseif( $fieldType == 'time' && !empty($value) && $field->getFieldName()
    			!= 'duration_hours') {
    		if($field->getFieldName() == "time_start" || $field->getFieldName() == "time_end") {
    			$date = new DateTimeField($value);
    			$fieldvalue = $date->getDisplayTime();   
    		} else {
    			$fieldvalue = $value;
    		}
    	} elseif( $fieldType == "picklist" && !empty($value) ) {
    		if(is_array($picklistArray)) {
    			if(is_array($picklistArray[$dbField->name]) &&
    					$field->getFieldName() != 'activitytype' && !in_array(
    					$value, $picklistArray[$dbField->name])){
    				$fieldvalue =$app_strings['LBL_NOT_ACCESSIBLE'];
    			} else {
    				$fieldvalue = $this->getTranslatedString($value, $module);
    			}
    		} else {
    			$fieldvalue = $this->getTranslatedString($value, $module);
    		}
    	} elseif( $fieldType == "multipicklist" && !empty($value) ) {
    		if(is_array($picklistArray[1])) {
    			$valueList = explode(' |##| ', $value);
    			$translatedValueList = array();
    			foreach ( $valueList as $value) {
    				if(is_array($picklistArray[1][$dbField->name]) && !in_array(
    						$value, $picklistArray[1][$dbField->name])) {
    					$translatedValueList[] =
    							$app_strings['LBL_NOT_ACCESSIBLE'];
    				} else {
    					
                        $translatedValueList[] = $this->getTranslatedString($value,$module);
    				}
    			}
    		}
    		if (!is_array($picklistArray[1]) || !is_array($picklistArray[1][$dbField->name])) {
    			$fieldvalue = str_replace(' |##| ', ', ', $value);
    		} else {
    			implode(', ', $translatedValueList);
    		}
    	} elseif ($fieldType == 'double') {
            if($current_user->truncate_trailing_zeros == true)
                $fieldvalue = decimalFormat($fieldvalue);
        }
    	if($fieldvalue == "") {
    		return "-";
    	}
    	$fieldvalue = str_replace("<", "&lt;", $fieldvalue);
    	$fieldvalue = str_replace(">", "&gt;", $fieldvalue);
    	$fieldvalue = decode_html($fieldvalue);
    
    	if (stristr($fieldvalue, "|##|") && empty($fieldType)) {
    		$fieldvalue = str_ireplace(' |##| ', ', ', $fieldvalue);
    	} elseif ($fld_type == "date" && empty($fieldType)) {
    		$fieldvalue = DateTimeField::convertToUserFormat($fieldvalue);
    	} elseif ($fld_type == "datetime" && empty($fieldType)) {
    		$date = new DateTimeField($fieldvalue);
    		$fieldvalue = $date->getDisplayDateTimeValue();
    	}
     
    	if($fieldInfo['uitype'] == '19' && ($module == 'Documents' || $module == 'Emails')) {
    		return $fieldvalue;
    	}
    	return htmlentities($fieldvalue, ENT_QUOTES, $default_charset);
    }
    
    function getFieldByEMAILMakerLabel($module, $label) {
    	$cacheLabel = VTCacheUtils::getReportFieldByLabel($module, $label);
    	if($cacheLabel) return $cacheLabel;
    	getColumnFields($module);
    	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
    	if(empty($cachedModuleFields)) {
    		return null;
    	}
    	foreach ($cachedModuleFields as $fieldInfo) {
    		$fieldName = str_replace(' ', '_', $fieldInfo['fieldname']);
            if($label == $fieldName) {
    			VTCacheUtils::setReportFieldByLabel($module, $label, $fieldInfo);
    			return $fieldInfo;
    		}
    	}
    	return null;
    }
    
    public function getTranslatedString($str, $module = '') {
		return Vtiger_Language_Handler::getTranslatedString($str, $module, $this->EMAILLanguage);
	}
}

class EMAILMaker_ReportRunQueryPlanner {
    protected $disablePlanner = false;
    protected $tables = array();
    protected $tempTables = array();
    protected $allowTempTables = true;
    protected $tempTablePrefix = 'vtiger_reptmptbl_';
    protected static $tempTableCounter = 0;
    protected $registeredCleanup = false;

    function addTable($table) {
        $this->tables[$table] = $table;
    }

    function requireTable($table, $dependencies = null) {
        if ($this->disablePlanner) {
            return true;
        }

        if (isset($this->tables[$table])) {
            return true;
        }
        if (is_array($dependencies)) {
            foreach ($dependencies as $dependentTable) {
                if (isset($this->tables[$dependentTable])) {
                    return true;
                }
            }
        } else if ($dependencies instanceof EMAILMaker_ReportRunQueryDependencyMatrix) {
            $dependents = $dependencies->getDependents($table);
            if ($dependents) {
                return count(array_intersect($this->tables, $dependents)) > 0;
            }
        }
        return false;
    }
    function getTables() {
        return $this->tables;
    }
    function newDependencyMatrix() {
        return new EMAILMaker_ReportRunQueryDependencyMatrix();
    }
    function registerTempTable($query, $keyColumn) {
    }
    function initializeTempTables() {
        $adb = PearDatabase::getInstance();
        $this->tempTables = array();
    }
    function cleanup() {
        $adb = PearDatabase::getInstance();

        $oldDieOnError = $adb->dieOnError;
        $adb->dieOnError = false; // To avoid abnormal termination during shutdown...
        foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
            $adb->pquery('DROP TABLE ' . $uniqueName, array());
        }
        $adb->dieOnError = $oldDieOnError;

        $this->tempTables = array();
    }

}

class EMAILMaker_ReportRunQueryDependencyMatrix {

    protected $matrix = array();
    protected $computedMatrix = null;

    function setDependency($table, array $dependents) {
        $this->matrix[$table] = $dependents;
    }

    function addDependency($table, $dependent) {
        if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
            $this->matrix[$table][] = $dependent;
        } else {
            $this->setDependency($table, array($dependent));
        }
    }

    function getDependents($table) {
        $this->computeDependencies();
        return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : array();
    }

    protected function computeDependencies() {
        if ($this->computedMatrix !== null)
            return;

        $this->computedMatrix = array();
        foreach ($this->matrix as $key => $values) {
            $this->computedMatrix[$key] =
                    $this->computeDependencyForKey($key, $values);
        }
    }

    protected function computeDependencyForKey($key, $values) {
        $merged = array();
        foreach ($values as $value) {
            $merged[] = $value;
            if (isset($this->matrix[$value])) {
                $merged = array_merge($merged, $this->matrix[$value]);
            }
        }
        return $merged;
    }
}