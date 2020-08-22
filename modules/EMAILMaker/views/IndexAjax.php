<?php

class EMAILMaker_IndexAjax_View extends Vtiger_IndexAjax_View {

	public function __construct(){
		parent::__construct();
		$Methods = array('showSettingsList','editCustomLabel','showCustomLabelValues','editLicense','showComposeEmailForm','report','getModuleConditions','showMESummary');
		foreach ($Methods AS $method){
			$this->exposeMethod($method);
		}
	}
	public function preProcess(Vtiger_Request $request, $display = true){
		return true;
	}
	public function postProcess(Vtiger_Request $request){
		return true;
	}
	public function process(Vtiger_Request $request){
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$type = $request->get('type');
	}
	public function report(Vtiger_Request $request){
	}

	function showSettingsList(Vtiger_Request $request){
		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode'));
		$linkModels = $EMAILMaker->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $linkModels);
		$parent_view = $request->get('pview');
		if ($parent_view == "EditProductBlock") $parent_view = "ProductBlocks";
		$viewer->assign('CURRENT_PVIEW', $parent_view);
		echo $viewer->view('SettingsList.tpl', 'EMAILMaker', true);
	}
	function editCustomLabel(Vtiger_Request $request){
		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$viewer = $this->getViewer($request);
		$slabelid = $request->get('labelid');
		$slangid = $request->get('langid');
		$currentLanguage = Vtiger_Language_Handler::getLanguage();
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);

		list($oLabels, $languages) = $EMAILMaker->GetCustomLabels();
		$currLang = array();
		foreach ($languages as $langId => $langVal) {
			if (($langId == $slangid && $slangid != "") || ($slangid == "" && $langVal["prefix"] == $currentLanguage)) {
				$currLang["id"] = $langId;
				$currLang["name"] = $langVal["name"];
				$currLang["label"] = $langVal["label"];
				$currLang["prefix"] = $langVal["prefix"];
				break;
			}
		}
		if ($slangid == "") $slangid = $currLang["id"];
		$viewer->assign('LABELID', $slabelid);
		$viewer->assign('LANGID', $slangid);

		$viewLabels = array();
		foreach ($oLabels as $lblId => $oLabel){
			if ($slabelid == $lblId){
				$l_key = substr($oLabel->GetKey(), 2);
				$l_values = $oLabel->GetLangValsArr();

				$viewer->assign("CUSTOM_LABEL_KEY", $l_key);
				$viewer->assign("CUSTOM_LABEL_VALUE", $l_values[$currLang["id"]]);
				break;
			}
		}
		$viewer->assign("CURR_LANG", $currLang);
		echo $viewer->view('ModalEditCustomLabelContent.tpl', 'EMAILMaker', true);
	}
	function showCustomLabelValues(Vtiger_Request $request){
		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$viewer = $this->getViewer($request);
		list($oLabels, $languages) = $EMAILMaker->GetCustomLabels();
		$labelid = $request->get('labelid');
		$currLangId = $request->get('langid');
		$oLbl = $oLabels[$labelid];
		$key = $oLbl->GetKey();
		$viewer->assign("LBLKEY", $key);
		$viewer->assign("LABELID", $labelid);
		$viewer->assign("LANGID", $currLangId);
		$label_id = $labelid;
		$langValsArr = $oLbl->GetLangValsArr();
		$newLangValsArr = array();
		foreach ($langValsArr as $langId => $langVal){
			if ($langId == $currLangId)
				continue;

			$label = $languages[$langId]["label"];
			$newLangValsArr[] = array("id"=>$langId,"value"=>$langVal,"label"=>$label);
		}
		$viewer->assign("LANGVALSARR", $newLangValsArr);
		echo $viewer->view('ModalCustomLabelValuesContent.tpl', 'EMAILMaker', true);
	}
	function editLicense(Vtiger_Request $request){
		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$type = $request->get('type');
		$viewer->assign("TYPE", $type);
		$key = $request->get('key');
		$viewer->assign("LICENSEKEY", $key);
		$viewer->assign("MODULE", $moduleName);

		echo $viewer->view('EditLicense.tpl', 'EMAILMaker', true);
	}
	function showComposeEmailForm(Vtiger_Request $request){
		$moduleName = 'EMAILMaker';
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$step = $request->get('step');
		$selectedFields = $request->get('selectedFields');
		$relatedLoad = $request->get('relatedLoad');
		$selecttemplates = $request->get('selecttemplates');
		$parentModule = $sourceModule = $request->get('sourceModule');
		$parentRecord = $request->get('sourceRecord');
		$basic = $request->get('basic');
		$single_record = false;
		$forview = $request->get('forview');

		$adb = PearDatabase::getInstance();
		$current_user = Users_Record_Model::getCurrentUserModel();
		$currentLanguage = Vtiger_Language_Handler::getLanguage();
		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$recordIds = $this->getRecordsListFromRequest($request);
		$viewer = $this->getViewer($request);
		if (count($recordIds) == 1) {
			$single_record = true;
		}

		$viewer->assign('SEARCH_PARAMS', $request->get('search_params'));

		$cid = "";
		if ($request->has('cid') && !$request->isEmpty('cid')) {
			$parentRecord = $cid = $request->get('cid');
			$parentModule = "Campaigns";

			$viewer->assign('FOR_CAMPAIGN',$cid);
			if ($recordIds == 'all') {
				$single_record = false;
			}

		}

		$EMAILMaker = new EMAILMaker_EMAILMaker_Model();
		$Emails_Types = $EMAILMaker->getRecordsEmails($sourceModule,$recordIds,$basic);

		$Email_Field_List = array();
		$i = 0;
		$total_email_field_list_count = 0;
		$total_emailoptout = 0;
		foreach ($Emails_Types AS $email_type => $All_EmailFields) {
			foreach ($All_EmailFields AS $EFields) {
				$email_field_list_count = 0;

				foreach ($EFields["emails"] AS $Email_Field) {
					if ($Email_Field->isViewEnabled()) {
						$email_name = $Email_Field->get('name');
						$email_label = $Email_Field->get('label');
						$email_value = $EFields["data"][$email_name];

						if (isset($EFields["data"]["emailoptout"]))
							$emailoptout = $EFields["data"]["emailoptout"];
						else
							$emailoptout = "0";

						if ($email_value != "" || !$single_record) {


							if (!isset($Email_Fields_List[$EFields["label"]][$email_name])) {
								$email_field_list_count++;
								$total_email_field_list_count++;

								if ($emailoptout == "1" && $single_record) $total_emailoptout++;

								$EN = getEntityName($EFields["module"],$EFields["crmid"]);
								$crmname = $EN[$EFields["crmid"]];

								$ED = array('crmid' => $EFields["crmid"],
									'crmname' => $EFields["name"],
									'name' => $email_name,
									'module' => $EFields["module"],
									'fieldlabel' => $EFields["label"],
									'label' => vtranslate($email_label, $EFields["module"]),
									'value' => $email_value,
									'fieldname' => $email_name,
									'emailoptout' => $emailoptout);

								$label = $EFields["label"];

								if ($label == "") {
									$label = vtranslate("SINGLE_".$EFields["module"],$EFields["module"]);
									if ($label == "SINGLE_".$EFields["module"]){
										$label = vtranslate($EFields["module"],$EFields["module"]);
									}
								}

								if ($crmname !="") {
									$label .= ": ";
									$label .= $crmname;
								}

								$Email_Fields_List[$label][$email_name] = $ED;
							}
						}
					}
				}
				$i++;
			}
		}
		$viewer->assign('TOTAL_EMAILOPTOUT', $total_emailoptout);
		$viewer->assign('EMAIL_FIELDS_LIST', $Email_Fields_List);
		$viewer->assign('EMAIL_FIELDS_COUNT', $total_email_field_list_count);

		if ($single_record && $total_email_field_list_count == 0) {
			$recordid = $recordIds[0];
			$source_name = getEntityName($sourceModule, $recordid);
			$viewer->assign('SOURCE_NAME', $source_name[$recordid]);
		}

		$viewer->assign('BASIC', $basic);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
			$viewer->assign('SEARCH_KEY',$searchKey);
		}



		if (!empty($parentModule)) {
			$viewer->assign('PARENT_MODULE', $parentModule);
			$viewer->assign('PARENT_RECORD', $parentRecord);
			$viewer->assign('RELATED_MODULE', $sourceModule);
		}
		if($relatedLoad){
			$viewer->assign('RELATED_LOAD', true);
		}

		if ($single_record) $viewer->assign('SINGLE_RECORD', 'yes');
		if ($selecttemplates == "true"){
			$forListView = true;
			$pid = false;
			if ($request->has('selected_ids') && !$request->isEmpty('selected_ids') && $forview == "Detail" && $single_record) {
				$selected_ids = $request->get('selected_ids');
				if (is_numeric($selected_ids)) {
					$pid = $selected_ids;
					$forListView = false;
				}
			}

			$templates = $EMAILMaker->GetAvailableTemplatesArray($sourceModule, $forListView, $pid);

			if ($cid != ""){

				$campaign_templates = $EMAILMaker->GetAvailableTemplatesArray("Campaigns", true);

				if (count($campaign_templates[0]) > 0){
					if (count($templates[0]) > 0)
						$templates[0] = $templates[0] + $campaign_templates[0];
					else
						$templates[0] = $campaign_templates[0];
				}
				if (count($campaign_templates[1]) > 0){

					if (count($templates[1]) > 0)
						$templates[1] = $templates[1] + $campaign_templates[1];
					else
						$templates[1] = $campaign_templates[1];
				}
			}

			if (count($templates) > 0)
				$no_templates_exist = 0;
			else
				$no_templates_exist = 1;

			$viewer->assign('CRM_TEMPLATES', $templates);
			$viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);


			if (!isset($_SESSION["template_languages"]) || $_SESSION["template_languages"] == ""){
				$temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?",array('1'));
				while ($temp_row = $adb->fetchByAssoc($temp_res)){
					$template_languages[$temp_row["prefix"]] = $temp_row["label"];
				}
				$_SESSION["template_languages"] = $template_languages;
			}
			$viewer->assign('TEMPLATE_LANGUAGES', $_SESSION["template_languages"]);

			$def_templateid = $EMAILMaker->GetDefaultTemplateId($sourceModule,($forview=="List"?true:false));
			$viewer->assign('DEFAULT_TEMPLATE', $def_templateid);
		}
		$no_pdftemplates = true;
		if (vtlib_isModuleActive('PDFMaker')) {
			$pdftemplateid = $request->get('pdftemplateid');

			if ($request->has('pdflanguage') && !$request->isEmpty('pdflanguage')) {
				$currentLanguage = $request->get('pdflanguage');
			}

			if ($pdftemplateid != "") {
				$viewer->assign('PDFTEMPLATEID', $pdftemplateid);

				if ($pdftemplateid) $PDFTemplateIds = explode(";",$pdftemplateid);
				$viewer->assign('PDFTEMPLATEIDS', $PDFTemplateIds);
			}
			if (class_exists('PDFMaker_PDFMaker_Model')){
				$PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
				$pdf_templates = $PDFMakerModel->GetAvailableTemplates($sourceModule);
				$viewer->assign('PDF_TEMPLATES', $pdf_templates);
				if (count($pdf_templates) > 0){
					$viewer->assign('IS_PDFMAKER', 'yes');
					$no_pdftemplates = false;
				}
			}
		}

		if ((!$no_templates_exist || !$no_pdftemplates) && $selecttemplates == "true") {
			$for_list_view = "yes";
		} else {
			$for_list_view = "no";
		}

		$viewer->assign('FORLISTVIEW', $for_list_view);
		$viewer->assign('CURRENT_LANGUAGE', $currentLanguage);

		if($step == 'step1') {
			echo $viewer->view('SelectEmailFields.tpl', $moduleName, true);
			exit;
		}
	}
	public function getModuleConditions(Vtiger_Request $request){
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$selectedModuleName = $request->get("source_module");
		$selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

		$recordStructure = $recordStructureInstance->getStructure();
		if(in_array($selectedModuleName,  getInventoryModules())){
			$itemsBlock = "LBL_ITEM_DETAILS";
			unset($recordStructure[$itemsBlock]);
		}
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);

		$dateFilters = Vtiger_Field_Model::getDateFilterTypes();
		foreach($dateFilters as $comparatorKey => $comparatorInfo) {
			$comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
			$comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
			$comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
			$dateFilters[$comparatorKey] = $comparatorInfo;
		}
		$viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('ADVANCED_FILTER_OPTIONS', EMAILMaker_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', EMAILMaker_Field_Model::getAdvancedFilterOpsByFieldType());
		$viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		$viewer->assign('QUALIFIED_MODULE', 'EMAILMaker');
		$viewer->view('AdvanceFilter.tpl', 'EMAILMaker');
	}

	public function showMESummary(Vtiger_Request $request){
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$Data = $request->get('data');

		$currentUser = Users_Record_Model::getCurrentUserModel();

		$RecordME_Model = EMAILMaker_RecordME_Model::getCleanInstance();
		$RecordME_Model->setFromRequestData($Data);
		$RecordME_Model->setTemplateData();

		$start_of = $RecordME_Model->get("start_of");

		if ($start_of != ""){


			$hour_format = $currentUser->get('hour_format');

			if ($hour_format == "12") {
				$time_format = 'h:i a';
			} else {
				$time_format = 'H:i';
			}

			$convert_date_start = DateTimeField::convertToUserTimeZone($start_of);
			$start_of_date = $convert_date_start->format('Y-m-d');
			$start_of_time = $convert_date_start->format('H:00');
			$formated_time = $convert_date_start->format($time_format);
		} else {
			$start_of_date = date('Y-m-d', strtotime("+1 day"));
			$start_of_time = "00:00";
			$formated_time = "";
		}
		$user_start_of_date = DateTimeField::convertToUserFormat($start_of_date);
		$start_of = trim($user_start_of_date." ".$formated_time);
		$RecordME_Model->set("start_of",$start_of);

		//$module_columns = EMAILMaker_RecordME_Model::getModuleColumns(, );
		$for_module = $RecordME_Model->get("module_name");
		$listid = $RecordME_Model->get("listid");

		$moduleModel = Vtiger_Module_Model::getInstance($for_module);
		$emailFieldModels = $moduleModel->getFieldsByType('email');
		$emailFieldModel = $emailFieldModels[$Data["selected_email_fieldname"]];

		$email_fieldname_label = vtranslate($emailFieldModel->get('label'),$for_module);
		$RecordME_Model->set("email_fieldname_label",$email_fieldname_label);

		$viewer->assign('MASSEMAILRECORDMODEL', $RecordME_Model);


		$recipients_count = $RecordME_Model->get("total_entries");

		if ($recipients_count == "") {
			$listViewModel = Vtiger_ListView_Model::getInstance($for_module, $listid);
			$recipients_count = $listViewModel->getListViewCount();
		}

		$viewer->assign('RECIPIENTS_COUNT', $recipients_count);

		$viewer->view('SummaryMEView.tpl', 'EMAILMaker');
	}
}