<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger ListView Model Class
 */
class VDUsers_ListView_Model extends Vtiger_ListView_Model {



	/**
	 * Function to get the Module Model
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule() {
		return $this->get('module');
	}

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkParams);

		$listLinkTypes = array('LISTVIEWSIDEBARLINK', 'LISTVIEWSIDEBARWIDGET');
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if($listLinks['LISTVIEWSIDEBARLINK']) {
			foreach($listLinks['LISTVIEWSIDEBARLINK'] as $link) {
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if($listLinks['LISTVIEWSIDEBARWIDGET']) {
			foreach($listLinks['LISTVIEWSIDEBARWIDGET'] as $link) {
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}

		return $moduleLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);



		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		$massActionLinks = array();




		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
	    global $current_user,$adb;
        $vdusergenerator = new QueryGenerator('VDUsers',$current_user);
        $viewId = $this->get('viewId');
        $customView = new CustomView();
        if (!empty($viewId) && $viewId != "0") {
            $vdusergenerator->initForCustomViewById($viewId);

            //Used to set the viewid into the session which will be used to load the same filter when you refresh the page
            $viewId = $customView->getViewId('VDUsers');
        } else {
            $viewId = $customView->getViewId('VDUsers');
            if(!empty($viewId) && $viewId != 0) {
                $vdusergenerator->initForDefaultCustomView();
            } else {
                $entityInstance = CRMEntity::getInstance('VDUsers');
                $listFields = $entityInstance->list_fields_name;
                $listFields[] = 'id';
                $vdusergenerator->setFields($listFields);
            }
        }
        $usergenerator = new QueryGenerator('Users',$current_user);
        $this->set('vdusergenerator',$vdusergenerator);
        $this->set('usergenerator',$usergenerator);

		$headerFieldModels = array();
        $meta = $vdusergenerator->getMeta($vdusergenerator->getModule());
        $moduleFields = $usergenerator->getModuleFields();

        $fields = $vdusergenerator->getFields();
        $headerFields = array();

        foreach($fields as $fieldName) {
            if(array_key_exists($fieldName, $moduleFields)) {
                $headerFields[$fieldName] = $moduleFields[$fieldName];
            }
        }


		foreach($headerFields as $fieldName => $webserviceField) {

			if($webserviceField && !in_array($webserviceField->getPresence(), array(0,2))) continue;

			$headerFieldModels[$fieldName] = VDUsers_Field_Model::getInstance($fieldName);


		}

		return $headerFieldModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$moduleName = 'Users';
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

         $searchParams = array();
        if(empty($searchParams)) {
            $searchParams = array();
        }
        $glue = "";
        if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);




        
        $listQuery = 'SELECT * FROM vtiger_users WHERE id > 0';
        $search_params = $this->get('search_params');
        $searchValue = $this->get('search_value');

        if (!empty($searchValue)){
            $listQuery .=" and last_name LIKE '$searchValue%'";
        }

        if (is_array($search_params) && count($search_params) > 0){
            foreach ($search_params as $label=>$param){
                $listQuery .=" and $label LIKE '%$param%'";
            }
        }

		$viewid = ListViewSession::getCurrentView($moduleName);
		if(empty($viewid)) {
            $viewid = $pagingModel->get('viewid');
		}
        $_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');

		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
        $listViewEntries = array();
		if ($db->num_rows($listResult)>0) {
            $listViewEntries = $this->getListViewRecords($moduleFocus, 'VDUsers', $listResult);
        }

		$index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}

		return $listViewRecordModels;
	}
    function getListViewRecords($focus, $module, $result) {
		
		global $listview_max_textlength, $theme, $default_charset, $current_user;
		
        require('user_privileges/user_privileges_'.$current_user->id.'.php');
        $queryGenerator = $this->get('vdusergenerator');
        $fields = $queryGenerator->getFields();
        $meta = $queryGenerator->getMeta('VDUsers');
        $usergenerator = $this->get('usergenerator');

        $moduleFields = $usergenerator->getModuleFields();
        $accessibleFieldList = array_keys($moduleFields);
        $listViewFields = array_intersect($fields, $accessibleFieldList);
		
        $referenceFieldList = $queryGenerator->getReferenceFieldList();
        foreach ($referenceFieldList as $fieldName) {
            if (in_array($fieldName, $listViewFields)) {
                $field = $moduleFields[$fieldName];
                $this->fetchNameList($field, $result);
            }
        }

        $db = PearDatabase::getInstance();
        $rowCount = $db->num_rows($result);
        $ownerFieldList = $queryGenerator->getOwnerFieldList();
        foreach ($ownerFieldList as $fieldName) {
            if (in_array($fieldName, $listViewFields)) {
                $field = $moduleFields[$fieldName];
                $idList = array();
                for ($i = 0; $i < $rowCount; $i++) {
                    $id = $db->query_result($result, $i, $field->getColumnName());
                    if (!isset($this->ownerNameList[$fieldName][$id])) {
                        $idList[] = $id;
                    }
                }
                if(count($idList) > 0) {
                    if(!is_array($this->ownerNameList[$fieldName])) {
                        $this->ownerNameList[$fieldName] = getOwnerNameList($idList);
                    } else {
                        //array_merge API loses key information so need to merge the arrays
                        // manually.
                        $newOwnerList = getOwnerNameList($idList);
                        foreach ($newOwnerList as $id => $name) {
                            $this->ownerNameList[$fieldName][$id] = $name;
                        }
                    }
                }
            }
        }

        foreach ($listViewFields as $fieldName) {
            $field = $moduleFields[$fieldName];
            if(!$is_admin && ($field->getFieldDataType() == 'picklist' ||
                    $field->getFieldDataType() == 'multipicklist')) {
                $this->setupAccessiblePicklistValueList($fieldName);
            }
        }

        $moduleInstance = Vtiger_Module_Model::getInstance("PBXManager");
        if($moduleInstance && $moduleInstance->isActive()) {
            $outgoingCallPermission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
        }

        $useAsterisk = get_use_asterisk($this->user->id);

        $data = array();
        for ($i = 0; $i < $rowCount; ++$i) {
            //Getting the recordId
            if($module != 'Users') {
                $baseTable = $meta->getEntityBaseTable();
                $moduleTableIndexList = $meta->getEntityTableIndexList();
                $baseTableIndex = $moduleTableIndexList[$baseTable];

                $recordId = $db->query_result($result,$i,$baseTableIndex);
            }else {
                $recordId = $db->query_result($result,$i,"id");
            }
            $row = array();

            foreach ($listViewFields as $fieldName) {
				
                $field = $moduleFields[$fieldName];
                $uitype = $field->getUIType();
                $rawValue = $db->query_result($result, $i, $field->getColumnName());

                if(in_array($uitype,array(15,33,16))){
                    $value = html_entity_decode($rawValue,ENT_QUOTES,$default_charset);
                } else {
                    $value = $rawValue;
                }
				
                if ($field->getUIType() == '27') {
                    if ($value == 'I') {
                        $value = getTranslatedString('LBL_INTERNAL',$module);
                    }elseif ($value == 'E') {
                        $value = getTranslatedString('LBL_EXTERNAL',$module);
                    }else {
                        $value = ' --';
                    }
                }elseif ($field->getFieldDataType() == 'picklist') {
                    //not check for permissions for non admin users for status and activity type field
                    if($module == 'Calendar' && ($fieldName == 'taskstatus' || $fieldName == 'eventstatus' || $fieldName == 'activitytype')) {
                        $value = Vtiger_Language_Handler::getTranslatedString($value,$module);
                        $value = textlength_check($value);
                    }
                    else if ($value != '' && !$is_admin && $this->picklistRoleMap[$fieldName] &&
                        !in_array($value, $this->picklistValueMap[$fieldName]) && strtolower($value) != '--none--' && strtolower($value) != 'none' ) {
                        $value = "<font color='red'>". Vtiger_Language_Handler::getTranslatedString('LBL_NOT_ACCESSIBLE',
                                $module)."</font>";
                    } else {
                        $value =  Vtiger_Language_Handler::getTranslatedString($value,$module);
                        $value = textlength_check($value);
                    }
                }elseif($field->getFieldDataType() == 'date' || $field->getFieldDataType() == 'datetime') {
                    if($value != '' && $value != '0000-00-00') {
                        $fieldDataType = $field->getFieldDataType();
                        if($module == 'Calendar' &&($fieldName == 'date_start' || $fieldName == 'due_date')) {
                            if($fieldName == 'date_start') {
                                $timeField = 'time_start';
                            }else if($fieldName == 'due_date') {
                                $timeField = 'time_end';
                            }
                            $timeFieldValue = $this->db->query_result($result, $i, $timeField);
                            if(!empty($timeFieldValue)){
                                $value .= ' '. $timeFieldValue;
                                //TO make sure it takes time value as well
                                $fieldDataType = 'datetime';
                            }
                        }
                        if($fieldDataType == 'datetime') {
                            $value = Vtiger_Datetime_UIType::getDateTimeValue($value);
                        } else if($fieldDataType == 'date') {
                            $date = new DateTimeField($value);
                            $value = $date->getDisplayDate();
                        }
                    } elseif ($value == '0000-00-00') {
                        $value = '';
                    }
                } elseif($field->getFieldDataType() == 'time') {
                    if(!empty($value)){
                        $userModel = Users_Privileges_Model::getCurrentUserModel();
                        if($userModel->get('hour_format') == '12'){
                            $value = Vtiger_Time_UIType::getTimeValueInAMorPM($value);
                        }
                    }
                } elseif($field->getFieldDataType() == 'currency') {
					if($value != '') {
                        if($field->getUIType() == 72) {
                            if($fieldName == 'unit_price') {
                                $currencyId = getProductBaseCurrency($recordId,$module);
                                $cursym_convrate = getCurrencySymbolandCRate($currencyId);
                                $currencySymbol = $cursym_convrate['symbol'];
                            } else {
                                $currencyInfo = getInventoryCurrencyInfo($module, $recordId);
                                $currencySymbol = $currencyInfo['currency_symbol'];
                            }
                            $value = CurrencyField::convertToUserFormat($value, null, true);
                            $row['currencySymbol'] = $currencySymbol;
//							$value = CurrencyField::appendCurrencySymbol($currencyValue, $currencySymbol);
                        } else {
                            if (!empty($value)) {
                                $value = CurrencyField::convertToUserFormat($value);
                            }
                        }
                    }
                } elseif($field->getFieldDataType() == 'url') {
                    $matchPattern = "^[\w]+:\/\/^";
                    preg_match($matchPattern, $rawValue, $matches);
                    if(!empty ($matches[0])){
                        $value = '<a class="urlField cursorPointer" href="'.$rawValue.'" target="_blank">'.textlength_check($value).'</a>';
                    }else{
                        $value = '<a class="urlField cursorPointer" href="http://'.$rawValue.'" target="_blank">'.textlength_check($value).'</a>';
                    }
                } elseif ($field->getFieldDataType() == 'email') {
                    global $current_user;
                    if($current_user->internal_mailer == 1){
                        //check added for email link in user detailview
                        $value = "<a class='emailField' onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId,".
                            "'$fieldName','$module');\">".textlength_check($value)."</a>";
                    } else {
                        $value = '<a class="emailField" href="mailto:'.$rawValue.'">'.textlength_check($value).'</a>';
                    }
                } elseif($field->getFieldDataType() == 'boolean') {
                    if ($value === 'on') {
                        $value = 1;
                    } else if ($value == 'off') {
                        $value = 0;
                    }
                    if($value == 1) {
                        $value = getTranslatedString('yes',$module);
                    } elseif($value == 0) {
                        $value = getTranslatedString('no',$module);
                    } else {
                        $value = '--';
                    }
                } elseif($field->getUIType() == 98) {
                    $value = '<a href="index.php?module=Roles&parent=Settings&view=Edit&record='.$value.'">'.textlength_check(getRoleName($value)).'</a>';
                } elseif($field->getFieldDataType() == 'multipicklist') {
                    $value = ($value != "") ? str_replace(' |##| ',', ',$value) : "";
                    if(!$is_admin && $value != '') {
                        $valueArray = ($rawValue != "") ? explode(' |##| ',$rawValue) : array();
                        $notaccess = '<font color="red">'.getTranslatedString('LBL_NOT_ACCESSIBLE',
                                $module)."</font>";
                        $tmp = '';
                        $tmpArray = array();
                        foreach($valueArray as $index => $val) {
                            if(!$listview_max_textlength ||
                                !(strlen(preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$tmp)) >
                                    $listview_max_textlength)) {
                                if (!$is_admin && $this->picklistRoleMap[$fieldName] &&
                                    !in_array(trim($val), $this->picklistValueMap[$fieldName])) {
                                    $tmpArray[] = $notaccess;
                                    $tmp .= ', '.$notaccess;
                                } else {
                                    $tmpArray[] = $val;
                                    $tmp .= ', '.$val;
                                }
                            } else {
                                $tmpArray[] = '...';
                                $tmp .= '...';
                            }
                        }
                        $value = implode(', ', $tmpArray);
                        $value = textlength_check($value);
                    }
                } elseif ($field->getFieldDataType() == 'skype') {
                    $value = ($value != "") ? "<a href='skype:$value?call'>".textlength_check($value)."</a>" : "";
                } elseif ($field->getUIType() == 11) {
                    if($outgoingCallPermission && !empty($value)) {
                        $phoneNumber = preg_replace('/[-()\s+]/', '',$value);
                        $value = '<a class="phoneField" data-value="'.$phoneNumber.'" record="'.$recordId.'" onclick="Vtiger_PBXManager_Js.registerPBXOutboundCall(\''.$phoneNumber.'\', '.$recordId.')">'.textlength_check($value).'</a>';
                    }else {
                        $value = textlength_check($value);
                    }
                } elseif($field->getFieldDataType() == 'reference') {
					$referenceFieldInfoList = $this->get('query_generator')->getReferenceFieldInfoList();
                    $moduleList = $referenceFieldInfoList[$fieldName];
                    if(count($moduleList) == 1) {
                        $parentModule = $moduleList[0];
                    } else {
                        $parentModule = $this->typeList[$value];
                    }
                    if(!empty($value) && !empty($this->nameList[$fieldName]) && !empty($parentModule)) {
                        $parentMeta = $this->get('query_generator')->getMeta($parentModule);
                        $value = textlength_check($this->nameList[$fieldName][$value]);
                        if ($parentMeta->isModuleEntity() && $parentModule != "Users") {
                            $value = "<a href='?module=$parentModule&view=Detail&".
                                "record=$rawValue' title='".getTranslatedString($parentModule, $parentModule)."'>$value</a>";
                        }
                    } else {
                        $value = '--';
                    }
                } elseif($field->getFieldDataType() == 'owner') {
                    $value = textlength_check($this->ownerNameList[$fieldName][$value]);
                } elseif ($field->getUIType() == 25) {
                    //TODO clean request object reference.
                    $contactId=$_REQUEST['record'];
                    $emailId=$this->db->query_result($result,$i,"activityid");
                    $result1 = $this->db->pquery("SELECT access_count FROM vtiger_email_track WHERE ".
                        "crmid=? AND mailid=?", array($contactId,$emailId));
                    $value=$this->db->query_result($result1,0,"access_count");
                    if(!$value) {
                        $value = 0;
                    }
                } elseif($field->getUIType() == 8){
                    if(!empty($value)){
                        $temp_val = html_entity_decode($value,ENT_QUOTES,$default_charset);
                        $json = new Zend_Json();
                        $value = vt_suppressHTMLTags(implode(',',$json->decode($temp_val)));
                    }
                } elseif ( in_array($uitype,array(7,9,90)) ) {
                    $value = "<span align='right'>".textlength_check($value)."</div>";
                } else {
                    $value = textlength_check($value);
                }

//				// vtlib customization: For listview javascript triggers
//				$value = "$value <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=".
//					"'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
//				// END
                $row[$fieldName] = $value;
            }
            $data[$recordId] = $row;
        }
        return $data;
    }

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');

        
        $searchParams = $this->get('search_params');
        if(empty($searchParams)) {
            $searchParams = array();
        }
        
        $glue = "";
        if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);
        
        $searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}
        $moduleName = $this->getModule()->get('name');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        
        

		$listQuery = $this->getQuery();


		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$position = stripos($listQuery, ' from ');
		if ($position) {
			$split = spliti(' from ', $listQuery);
			$splitCount = count($split);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$listQuery = $listQuery. ' FROM ' .$split[$i];
			}
		}

		if($this->getModule()->get('name') == 'Calendar'){
			$listQuery .= ' AND activitytype <> "Emails"';
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}

    /**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $value - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value) {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);

		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		
		$listFields = $moduleModel->getPopupViewFieldsList();
        
        $listFields[] = 'id';
        $queryGenerator->setFields($listFields);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if($importPermission && $createPermission) {
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => 'LBL_IMPORT',
							'linkurl' => $moduleModel->getImportUrl(),
							'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("'.$this->getModule()->getExportUrl().'")',
					'linkicon' => ''
				);
		}

		$duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
		if($duplicatePermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_FIND_DUPLICATES',
				'linkurl' => 'Javascript:Vtiger_List_Js.showDuplicateSearchForm("index.php?module='.$moduleModel->getName().
								'&view=MassActionAjax&mode=showDuplicatesSearchForm")',
				'linkicon' => ''
			);
		}

		return $advancedLinks;
	}

	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */
	public function getSettingLinks() {
		return $this->getModule()->getSettingLinks();
	}

	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */
	public function getBasicLinks(){
		$basicLinks = array();
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => $moduleModel->getCreateRecordUrl(),
					'linkicon' => ''
			);
		}
		return $basicLinks;
	}

	public function extendPopupFields($fieldsList) {
		$moduleModel = $this->get('module');
		$queryGenerator = $this->get('query_generator');

		$listFields = $moduleModel->getPopupViewFieldsList();
		
		$listFields[] = 'id';
		$listFields = array_merge($listFields, $fieldsList);
		$queryGenerator->setFields($listFields);
	}
    /**
     * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
     * @param <String> $moduleName - Module Name
     * @param <Number> $viewId - Custom View Id
     * @return Vtiger_ListView_Model instance
     */
    public static function getInstance($moduleName, $viewId='0') {
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
        $customView = new CustomView();
        if (!empty($viewId) && $viewId != "0") {
            $queryGenerator->initForCustomViewById($viewId);

            //Used to set the viewid into the session which will be used to load the same filter when you refresh the page
            $viewId = $customView->getViewId($moduleName);
        } else {
            $viewId = $customView->getViewId($moduleName);
            if(!empty($viewId) && $viewId != 0) {
                $queryGenerator->initForDefaultCustomView();
            } else {
                $entityInstance = CRMEntity::getInstance($moduleName);
                $listFields = $entityInstance->list_fields_name;
                $listFields[] = 'id';
                $queryGenerator->setFields($listFields);
            }
        }
        $controller = new ListViewController($db, $currentUser, $queryGenerator);

        return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller)->set('viewId', $viewId);
    }
}
