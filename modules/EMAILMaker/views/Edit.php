<?php
class EMAILMaker_Edit_View extends Vtiger_Index_View {
    public $cu_language = "";
    private $ModuleFields = array();
    private $All_Related_Modules = array();
    protected $isInstalled = true;
    public function __construct() {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->exposeMethod('selectTheme');
    }
    public function process(Vtiger_Request $request) {
        $this->getProcess($request);
    }
    public function preProcess(Vtiger_Request $request, $display = true) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Vtiger_Basic_View::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        if (!empty($moduleName)) {
            $moduleModel = new EMAILMaker_EMAILMaker_Model('EMAILMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);
            if (!$permission) {
                $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                $viewer->view('OperationNotPermitted.tpl', $moduleName);
                exit;
            }
            $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
            $linkModels = $moduleModel->getSideBarLinks($linkParams);
            $viewer->assign('QUICK_LINKS', $linkModels);
        }
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
    function getProcess(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode) && $mode != "EditTheme") {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
        echo $this->showModuleEditView($request);
    }
    public function showModuleEditView(Vtiger_Request $request) {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $theme_mode = false;
        $mode = $request->get('mode');
        if ($mode == "EditTheme") $theme_mode = true;
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $current_user = Users_Record_Model::getCurrentUserModel();
        $RecipientModulenames = $EMAILMaker->getRecipientModulenames();
        $viewer->assign("RECIPIENTMODULENAMES", $RecipientModulenames);
        $site_URL = vglobal('site_URL');
        $path = $site_URL . "/test/logo/";
        if ($request->has('record') && !$request->isEmpty('record')) {
            $templateid = $request->get('record');
            $emailtemplateResult = $EMAILMaker->GetEditViewData($templateid);
            $select_module = $emailtemplateResult["module"];
            $email_category = $emailtemplateResult["category"];
            $is_listview = $emailtemplateResult["is_listview"];
            $is_active = $emailtemplateResult["is_active"];
            $is_default = $emailtemplateResult["is_default"];
            $order = $emailtemplateResult["order"];
            $owner = $emailtemplateResult["owner"];
            $sharingtype = $emailtemplateResult["sharingtype"];
            $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($templateid, true);
            if (vtlib_isModuleActive("ITS4YouStyles")) {
                $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
                $Style_Files = $ITS4YouStylesModuleModel->getStyleFiles($templateid, "EMAILMaker");
                $viewer->assign("ITS4YOUSTYLE_FILES", $Style_Files);
                $Style_Content = $ITS4YouStylesModuleModel->getStyleContent($templateid, "EMAILMaker");
                $viewer->assign("STYLES_CONTENT", $Style_Content);
            }
        } elseif ($request->has('themeid') && !$request->isEmpty('themeid') && $theme_mode) {
            $templateid = $request->get('themeid');
            $emailtemplateResult = $EMAILMaker->GetEditViewData($templateid);
            $select_module = $emailtemplateResult["module"];
            $email_category = $emailtemplateResult["category"];
            $is_listview = $emailtemplateResult["is_listview"];
            $is_active = $emailtemplateResult["is_active"];
            $is_default = $emailtemplateResult["is_default"];
            $order = $emailtemplateResult["order"];
            $owner = $emailtemplateResult["owner"];
            $sharingtype = $emailtemplateResult["sharingtype"];
            $sharingMemberArray = $EMAILMaker->GetSharingMemberArray($templateid);
        } else {
            $emailtemplateResult = array();
            $emailtemplateResult["permissions"] = $EMAILMaker->returnTemplatePermissionsData($templateid);
            $templateid = $select_module = $email_category = "";
            $is_listview = $is_default = "0";
            $is_active = $order = "1";
            $owner = $current_user->getId();
            if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany')) {
                $Company_Data = ITS4YouMultiCompany_Record_Model::getCompanyByUserId($owner);
                if ($Company_Data != null) {
                    $sharingtype = "share";
                    $companyid = $Company_Data->getId();
                    $sharingMemberArray["Companies"] = array("Companies:" . $companyid => $companyid);
                } else {
                    $sharingtype = "private";
                }
            } else {
                $sharingtype = "public";
            }
            $sharingMemberArray = array();
            if ($request->has('theme') && !$request->isEmpty('theme')) {
                $theme = $request->get('theme');
                if ($theme != "new") {
                    $theme_path = getcwd() . "/modules/EMAILMaker/templates/" . $theme . "/index.html";
                    $theme_content = file_get_contents($theme_path);
                    if (file_exists($theme_path)) {
                        $emailtemplateResult["body"] = str_replace("[site_URL]", $site_URL, $theme_content);
                    }
                }
            }
            if ($request->has('themeid') && !$request->isEmpty('themeid')) {
                $themeid = $request->get('themeid');
                $emailthemeResult = $EMAILMaker->GetEditViewData($themeid);
                $emailtemplateResult["body"] = $emailthemeResult["body"];
            }
        }
        $viewer->assign("EMAIL_TEMPLATE_RESULT", $emailtemplateResult);
        $viewer->assign("THEME_MODE", $theme_mode);
        if (!$emailtemplateResult["permissions"]["edit"]) $EMAILMaker->DieDuePermission();
        if ($request->has("isDuplicate") && $request->get("isDuplicate") == "true") {
            $viewer->assign("TEMPLATENAME", "");
            $viewer->assign("DUPLICATE_TEMPLATENAME", $emailtemplateResult["templatename"]);
        } else $viewer->assign("TEMPLATENAME", $emailtemplateResult["templatename"]);
        if (!$request->has("isDuplicate") OR ($request->has("isDuplicate") && $request->get("isDuplicate") != "true")) $viewer->assign("SAVETEMPLATEID", $templateid);
        if ($templateid != "") $viewer->assign("EMODE", "edit");
        $viewer->assign("TEMPLATEID", $templateid);
        if ($select_module != "") {
            $viewer->assign("MODULENAME", vtranslate($select_module, $select_module));
            $viewer->assign("SELECTMODULE", $select_module);
        }
        $this->cu_language = $current_user->get('language');
        $viewer->assign("THEME", $theme);
        $viewer->assign("IMAGE_PATH", $image_path);
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $viewer->assign("APP", $app_strings);
        $viewer->assign("PARENTTAB", getParentTab());
        $modArr = $EMAILMaker->GetAllModules();
        $Modulenames = $modArr[0];
        $ModuleIDS = $modArr[1];
        $viewer->assign("MODULENAMES", $Modulenames);
        $CUI_BLOCKS["Assigned"] = vtranslate("LBL_USER_INFO", 'EMAILMaker');
        $CUI_BLOCKS["Logged"] = vtranslate("LBL_LOGGED_USER_INFO", 'EMAILMaker');
        $CUI_BLOCKS["Modifiedby"] = vtranslate("LBL_MODIFIEDBY_USER_INFO", 'EMAILMaker');
        $CUI_BLOCKS["Creator"] = vtranslate("LBL_CREATOR_USER_INFO", 'EMAILMaker');
        $viewer->assign("CUI_BLOCKS", $CUI_BLOCKS);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery("SELECT * FROM vtiger_organizationdetails", array());
        $organization_logoname = decode_html($adb->query_result($result, 0, 'logoname'));
        $organization_header = decode_html($adb->query_result($result, 0, 'headername'));
        $organization_stamp_signature = $adb->query_result($result, 0, 'stamp_signature');
        if (isset($organization_logoname)) {
            $organization_logo_img = "<img src=\"" . $path . $organization_logoname . "\">";
            $viewer->assign("COMPANYLOGO", $organization_logo_img);
        }
        if (isset($organization_stamp_signature)) {
            $organization_stamp_signature_img = "<img src=\"" . $path . $organization_stamp_signature . "\">";
            $viewer->assign("COMPANY_STAMP_SIGNATURE", $organization_stamp_signature_img);
        }
        if (isset($organization_header)) {
            $organization_header_img = "<img src=\"" . $path . $organization_header . "\">";
            $viewer->assign("COMPANY_HEADER_SIGNATURE", $organization_header_img);
        }
        if (getTabId('ITS4YouMultiCompany') && vtlib_isModuleActive('ITS4YouMultiCompany')) {
            $ismulticompany = true;
            $RMAILMakerFieldsModel = new EMAILMaker_Fields_Model();
            $Acc_Info = $RMAILMakerFieldsModel->getSelectModuleFields("ITS4YouMultiCompany", "company");
        } else {
            $ismulticompany = false;
            $Settings_Vtiger_CompanyDetails_Model = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $CompanyDetails_Fields = $Settings_Vtiger_CompanyDetails_Model->getFields();
            foreach ($CompanyDetails_Fields AS $field_name => $field_type) {
                if ($field_name == "organizationname") {
                    $field_name = "name";
                } elseif ($field_name == "code") {
                    $field_name = "zip";
                } elseif ($field_name == "logoname") {
                    continue;
                }
                $l = "LBL_COMPANY_" . strtoupper($field_name);
                $label = vtranslate($l, 'EMAILMaker');
                if ($label == "" || $l == $label) $label = $field_name;
                $Acc_Info["company-" . $field_name] = $label;
            }
        }
        $viewer->assign("ACCOUNTINFORMATIONS", $Acc_Info);
        if (getTabId('MultiCompany4you') && vtlib_isModuleActive('MultiCompany4you')) {
            $MultiAcc_info = Array('' => vtranslate("LBL_PLS_SELECT", 'EMAILMaker'), "multicompany-companyname" => vtranslate("LBL_COMPANY_NAME", 'MultiCompany4you'), "multicompany-street" => vtranslate("Street", 'MultiCompany4you'), "multicompany-city" => vtranslate("City", 'MultiCompany4you'), "multicompany-code" => vtranslate("Code", 'MultiCompany4you'), "multicompany-state" => vtranslate("State", 'MultiCompany4you'), "multicompany-country" => vtranslate("Country", 'MultiCompany4you'), "multicompany-phone" => vtranslate("phone", 'MultiCompany4you'), "multicompany-fax" => vtranslate("Fax", 'MultiCompany4you'), "multicompany-email" => vtranslate("email", 'MultiCompany4you'), "multicompany-website" => vtranslate("Website", 'MultiCompany4you'), "multicompany-logo" => vtranslate("Logo", 'MultiCompany4you'), "multicompany-stamp" => vtranslate("Stamp", 'MultiCompany4you'), "multicompany-bankname" => vtranslate("BankName", 'MultiCompany4you'), "multicompany-bankaccountno" => vtranslate("BankAccountNo", 'MultiCompany4you'), "multicompany-iban" => vtranslate("IBAN", 'MultiCompany4you'), "multicompany-swift" => vtranslate("SWIFT", 'MultiCompany4you'), "multicompany-registrationno" => vtranslate("RegistrationNo", 'MultiCompany4you'), "multicompany-vatno" => vtranslate("VATNo", 'MultiCompany4you'), "multicompany-taxid" => vtranslate("TaxId", 'MultiCompany4you'), "multicompany-additionalinformations" => vtranslate("AdditionalInformations", 'MultiCompany4you'),);
            $viewer->assign("MULTICOMPANYINFORMATIONS", $MultiAcc_info);
            $viewer->assign("LBL_MULTICOMPANY", vtranslate('MultiCompany', 'MultiCompany4you'));
        }
        $res_user_block = $adb->pquery("SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC", array('29'));
        $user_block_info_arr = array();
        while ($row_user_block = $adb->fetch_array($res_user_block)) {
            $sql_user_field = "SELECT fieldid, uitype FROM vtiger_field WHERE block = ? AND (displaytype != ? OR uitype = ? ) ORDER BY sequence ASC";
            $res_user_field = $adb->pquery($sql_user_field, array($row_user_block['blockid'], '3', '55'));
            $num_user_field = $adb->num_rows($res_user_field);
            if ($num_user_field > 0) {
                $user_field_id_array = array();
                while ($row_user_field = $adb->fetch_array($res_user_field)) {
                    $user_field_id_array[] = $row_user_field['fieldid'];
                }
                $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;
            }
        }
        $user_mod_strings = $this->getModuleLanguageArray("Users");
        $b = 0;
        $User_Types = array("s", "l", "m", "c");
        foreach ($user_block_info_arr AS $block_label => $block_fields) {
            $b++;
            if (isset($user_mod_strings[$block_label]) AND $user_mod_strings[$block_label] != "") $optgroup_value = $user_mod_strings[$block_label];
            else $optgroup_value = vtranslate($block_label, 'EMAILMaker');
            if (count($block_fields) > 0) {
                $sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (" . generateQuestionMarks($block_fields) . ") AND presence != '1'";
                $result1 = $adb->pquery($sql1, $block_fields);
                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];
                    $option_key = strtolower("users" . "-" . $fieldname);
                    if (isset($current_mod_strings[$fieldlabel]) AND $current_mod_strings[$fieldlabel] != "") $option_value = $current_mod_strings[$fieldlabel];
                    elseif (isset($app_strings[$fieldlabel]) AND $app_strings[$fieldlabel] != "") $option_value = $app_strings[$fieldlabel];
                    else $option_value = $fieldlabel;
                    foreach ($User_Types AS $user_prefix) {
                        if ($fieldname == 'currency_id') {
                            $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-" . $option_key] = vtranslate('LBL_CURRENCY_ID', 'EMAILMaker');
                            $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-users-currency_name"] = $option_value;
                            $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-users-currency_code"] = vtranslate('LBL_CURRENCY_CODE', 'EMAILMaker');
                            $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-users-currency_symbol"] = vtranslate('LBL_CURRENCY_SYMBOL', 'EMAILMaker');
                        } else {
                            $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-" . $option_key] = $option_value;
                        }
                    }
                }
            }
            if ($b == 1) {
                $option_value = "Record ID";
                $option_key = strtolower("USERS_CRMID");
                foreach ($User_Types AS $user_prefix) {
                    $User_Info[$user_prefix][$optgroup_value][$user_prefix . "-" . $option_key] = $option_value;
                }
            }
        }
        $viewer->assign("USERINFORMATIONS", $User_Info);
        $Invterandcon = array("" => vtranslate("LBL_PLS_SELECT", 'EMAILMaker'), "terms-and-conditions" => vtranslate("LBL_TERMS_AND_CONDITIONS", 'EMAILMaker'));
        $viewer->assign("INVENTORYTERMSANDCONDITIONS", $Invterandcon);
        $customFunctions = $this->getCustomFunctionsList();
        $viewer->assign("CUSTOM_FUNCTIONS", $customFunctions);
        $global_lang_labels = @array_flip($app_strings);
        $global_lang_labels = @array_flip($global_lang_labels);
        asort($global_lang_labels);
        $viewer->assign("GLOBAL_LANG_LABELS", $global_lang_labels);
        $module_lang_labels = array();
        if ($select_module != "") {
            $mod_lang = $this->getModuleLanguageArray($select_module);
            $module_lang_labels = @array_flip($mod_lang);
            $module_lang_labels = @array_flip($module_lang_labels);
            asort($module_lang_labels);
        } else $module_lang_labels[""] = vtranslate("LBL_SELECT_MODULE_FIELD", 'EMAILMaker');
        $viewer->assign("MODULE_LANG_LABELS", $module_lang_labels);
        list($custom_labels, $languages) = $EMAILMaker->GetCustomLabels();
        $currLangId = "";
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == $current_language) {
                $currLangId = $langId;
                break;
            }
        }
        $vcustom_labels = array();
        if (count($custom_labels) > 0) {
            foreach ($custom_labels as $oLbl) {
                $currLangVal = $oLbl->GetLangValue($currLangId);
                if ($currLangVal == "") $currLangVal = $oLbl->GetFirstNonEmptyValue();
                $vcustom_labels[$oLbl->GetKey() ] = $currLangVal;
            }
            asort($vcustom_labels);
        } else {
            $vcustom_labels = vtranslate("LBL_SELECT_MODULE_FIELD", 'EMAILMaker');
        }
        $viewer->assign("CUSTOM_LANG_LABELS", $vcustom_labels);
        $dateVariables = array("##DD.MM.YYYY##" => vtranslate("LBL_DATE_DD.MM.YYYY", 'EMAILMaker'), "##DD-MM-YYYY##" => vtranslate("LBL_DATE_DD-MM-YYYY", 'EMAILMaker'), "##MM-DD-YYYY##" => vtranslate("LBL_DATE_MM-DD-YYYY", 'EMAILMaker'), "##YYYY-MM-DD##" => vtranslate("LBL_DATE_YYYY-MM-DD", 'EMAILMaker'));
        $viewer->assign("DATE_VARS", $dateVariables);
        $viewer->assign("EMAIL_CATEGORY", $email_category);
        $selected_default_from = "";
        if ($templateid != "") {
            $sql_lfn = "SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid = ? AND userid = ?";
            $result_lfn = $adb->pquery($sql_lfn, array($templateid, $current_user->id));
            $num_rows_lfn = $adb->num_rows($result_lfn);
            if ($num_rows_lfn > 0) $selected_default_from = $adb->query_result($result_lfn, 0, "fieldname");
        }
        $Default_From_Options = array("" => vtranslate('LBL_NONE'));
        $result_a = $adb->pquery("select * from vtiger_systems where from_email_field != ? AND server_type = ?", array('', 'email'));
        $from_email_field = $adb->query_result($result_a, 0, "from_email_field");
        if ($from_email_field != "") {
            $result2 = $adb->pquery("select * from vtiger_organizationdetails where organizationname != ''", array());
            while ($row2 = $adb->fetchByAssoc($result2)) {
                $Default_From_Options["0_organization_email"] = vtranslate('LBL_COMPANY_EMAIL', 'EMAILMaker') . " <" . $from_email_field . ">";
            }
        }
        $current_user_id = $current_user->getId();
        $Current_User_Data = Users_Record_Model::getInstanceById($current_user_id, "Users");
        $result_fm = $adb->pquery("SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid = ? AND uitype IN ( ? , ? ) ORDER BY fieldid ASC ", array('29', '104', '13'));
        while ($row_fm = $adb->fetchByAssoc($result_fm)) {
            $cue = $Current_User_Data->get($row_fm['fieldname']);
            if ($cue != "") {
                $from_name_user_email = vtranslate($row_fm['fieldlabel'], "Users");
                $from_name_user_email.= " &lt;" . $cue . "&gt;";
                $Default_From_Options["1_" . $row_fm['fieldname']] = $from_name_user_email;
            }
        }
        $viewer->assign("SELECTED_DEFAULT_FROM", $selected_default_from);
        $viewer->assign("DEFAULT_FROM_OPTIONS", $Default_From_Options);
        $Status = array("1" => $app_strings["Active"], "0" => vtranslate("Inactive", 'EMAILMaker'));
        $viewer->assign("STATUS", $Status);
        $viewer->assign("IS_ACTIVE", $is_active);
        if ($is_active == "0") {
            $viewer->assign("IS_DEFAULT_DV_CHECKED", 'disabled="disabled"');
            $viewer->assign("IS_DEFAULT_LV_CHECKED", 'disabled="disabled"');
        } elseif ($is_default > 0) {
            $is_default_bin = str_pad(base_convert($is_default, 10, 2), 2, "0", STR_PAD_LEFT);
            $is_default_lv = substr($is_default_bin, 0, 1);
            $is_default_dv = substr($is_default_bin, 1, 1);
            if ($is_default_lv == "1") $viewer->assign("IS_DEFAULT_LV_CHECKED", 'checked="checked"');
            if ($is_default_dv == "1") $viewer->assign("IS_DEFAULT_DV_CHECKED", 'checked="checked"');
        }
        $viewer->assign("ORDER", $order);
        if ($is_listview == "1") $viewer->assign("IS_LISTVIEW_CHECKED", 'checked="checked"');
        $template_owners = get_user_array(false);
        $viewer->assign("TEMPLATE_OWNERS", $template_owners);
        $viewer->assign("TEMPLATE_OWNER", $owner);
        $sharing_types = Array("public" => vtranslate("PUBLIC_FILTER", 'EMAILMaker'), "private" => vtranslate("PRIVATE_FILTER", 'EMAILMaker'), "share" => vtranslate("SHARE_FILTER", 'EMAILMaker'));
        $viewer->assign("SHARINGTYPES", $sharing_types);
        $viewer->assign("SHARINGTYPE", $sharingtype);
        $cmod = $this->getModuleLanguageArray("Settings");
        $viewer->assign("CMOD", $cmod);
        $viewer->assign('SELECTED_MEMBERS_GROUP', $sharingMemberArray);
        $viewer->assign('MEMBER_GROUPS', Settings_Groups_Member_Model::getAll());
        $result_dec = $adb->pquery("SELECT * FROM vtiger_emakertemplates_settings", array());
        $num_rows_dec = $adb->num_rows($result_dec);
        if ($num_rows_dec > 0) {
            $settingsResult = $adb->fetchByAssoc($result_dec, 0);
            $Decimals = array("point" => $settingsResult["decimal_point"], "decimals" => $settingsResult["decimals"], "thousands" => ($settingsResult["thousands_separator"] != "sp" ? $settingsResult["thousands_separator"] : " "));
        } else {
            $decimal_point = $current_user->currency_decimal_separator;
            $decimals = $current_user->no_of_currency_decimals;
            $thousands_separator = $current_user->currency_grouping_separator;
            $Decimals = array("point" => $decimal_point, "decimals" => $decimals, "thousands" => ($thousands_separator != "sp" ? $thousands_separator : " "));
        }
        $viewer->assign("DECIMALS", $Decimals);
        $ignore_picklist_values = "";
        $pvresult = $adb->pquery("SELECT value FROM vtiger_emakertemplates_ignorepicklistvalues", array());
        $pv_num_rows = $adb->num_rows($pvresult);
        if ($pv_num_rows > 0) {
            $PVValues = array();
            while ($pvrow = $adb->fetchByAssoc($pvresult)) {
                $PVValues[] = $pvrow["value"];
            }
            $ignore_picklist_values = implode(", ", $PVValues);
        }
        $viewer->assign("IGNORE_PICKLIST_VALUES", $ignore_picklist_values);
        foreach (array('VAT', 'CHARGES') AS $blockType) {
            $blockTable = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td>' . $app_strings["Name"] . '</td>';
            if ($blockType == 'CHARGES') {
                $tableColspan = '2';
                $blockTable.= '<td>' . vtranslate('LBL_' . $blockType . 'BLOCK_SUM', 'EMAILMaker') . '</td>';
            } else {
                $tableColspan = '4';
                $blockTable.= '<td>' . vtranslate('LBL_' . $blockType . 'BLOCK_VAT_PERCENT', 'EMAILMaker') . '</td>
                                                        <td>' . vtranslate('LBL_' . $blockType . 'BLOCK_SUM', 'EMAILMaker') . '</td>
                                                        <td>' . vtranslate('LBL_' . $blockType . 'BLOCK_VAT_VALUE', 'EMAILMaker') . '</td>';
            }
            $blockTable.= '</tr>
                                        <tr>
                                            <td colspan="' . $tableColspan . '">#' . $blockType . 'BLOCK_START#</td>
                                        </tr>
                                            <tr>
                                                    <td>$' . $blockType . 'BLOCK_LABEL$</td>
                                                    <td>$' . $blockType . 'BLOCK_VALUE$</td>';
            if ($blockType != 'CHARGES') {
                $blockTable.= '<td>$' . $blockType . 'BLOCK_NETTO$</td>
                                                    <td>$' . $blockType . 'BLOCK_VAT$</td>';
            }
            $blockTable.= '</tr>
                                            <tr>
                                        <td colspan="' . $tableColspan . '">#' . $blockType . 'BLOCK_END#</td>
                                    </tr>
                                </table>';
            $blockTable = str_replace(array("
", "
", "
", "	"), "", $blockTable);
            $viewer->assign($blockType . 'BLOCK_TABLE', $blockTable);
            $ListView_Block = array("" => vtranslate("LBL_PLS_SELECT", 'EMAILMaker'), "LISTVIEWBLOCK_START" => vtranslate("LBL_ARTICLE_START", 'EMAILMaker'), "LISTVIEWBLOCK_END" => vtranslate("LBL_ARTICLE_END", 'EMAILMaker'), "CRIDX" => vtranslate("LBL_COUNTER", 'EMAILMaker'),);
            $viewer->assign("LISTVIEW_BLOCK_TPL", $ListView_Block);
        }
        $tacModules = array();
        $tac4you = is_numeric(getTabId("Tac4you"));
        if ($tac4you == true) {
            $result = $adb->pquery("SELECT tac4you_module FROM vtiger_tac4you_module WHERE presence = ?", array('1'));
            while ($row = $adb->fetchByAssoc($result)) $tacModules[$row["tac4you_module"]] = $row["tac4you_module"];
        }
        $desc4youModules = array();
        $desc4you = is_numeric(getTabId("Descriptions4you"));
        if ($desc4you == true) {
            $result = $adb->pquery("SELECT b.name FROM vtiger_links AS a INNER JOIN vtiger_tab AS b USING (tabid) WHERE linktype = ? AND linkurl = ?", array('DETAILVIEWWIDGET', 'block://ModDescriptions4you:modules/Descriptions4you/ModDescriptions4you.php'));
            while ($row = $adb->fetchByAssoc($result)) $desc4youModules[$row["name"]] = $row["name"];
        }
        $Settings_Profiles_Record_Model = new Settings_Profiles_Record_Model();
        $result = $adb->pquery("SELECT * FROM vtiger_emakertemplates_productbloc_tpl", array());
        $Productbloc_tpl[""] = vtranslate("LBL_PLS_SELECT", 'EMAILMaker');
        while ($row = $adb->fetchByAssoc($result)) {
            $Productbloc_tpl[$row["body"]] = $row["name"];
        }
        $viewer->assign("PRODUCT_BLOC_TPL", $Productbloc_tpl);
        $ProductBlockFields = $EMAILMaker->GetProductBlockFields();
        foreach ($ProductBlockFields as $viewer_key => $pbFields) {
            $viewer->assign($viewer_key, $pbFields);
        }
        $Related_Blocks = $EMAILMaker->GetRelatedBlocks($select_module);
        $viewer->assign("RELATED_BLOCKS", $Related_Blocks);
        $viewer->assign("SUBJECT_FIELDS", $EMAILMaker->getSubjectFields());
        if ($select_module != "") {
            $EMAILMakerFieldsModel = new EMAILMaker_Fields_Model();
            $SelectModuleFields = $EMAILMakerFieldsModel->getSelectModuleFields($select_module);
            $RelatedModules = $EMAILMakerFieldsModel->getRelatedModules($select_module);
            $viewer->assign("RELATED_MODULES", $RelatedModules);
            $viewer->assign("SELECT_MODULE_FIELD", $SelectModuleFields);
            $smf_filename = $SelectModuleFields;
            if ($select_module == "Invoice" || $select_module == "Quotes" || $select_module == "SalesOrder" || $select_module == "PurchaseOrder" || $select_module == "Issuecards" || $select_module == "Receiptcards" || $select_module == "Creditnote" || $select_module == "StornoInvoice") unset($smf_filename["Details"]);
            $viewer->assign("SELECT_MODULE_FIELD_SUBJECT", $smf_filename);
        }
        $viewer->assign("VERSION", EMAILMaker_Version_Helper::$version);
        $category = getParentTab();
        $viewer->assign("CATEGORY", $category);
        if ($select_module != "") {
            $selectedModuleName = $select_module;
            $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);
            $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
            $recordStructure = $recordStructureInstance->getStructure();
            if (in_array($selectedModuleName, getInventoryModules())) {
                $itemsBlock = "LBL_ITEM_DETAILS";
                unset($recordStructure[$itemsBlock]);
            }
            $viewer->assign('RECORD_STRUCTURE', $recordStructure);
            $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
            foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
                $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
                $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
                $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
                $dateFilters[$comparatorKey] = $comparatorInfo;
            }
            $viewer->assign('DATE_FILTERS', $dateFilters);
            $viewer->assign('ADVANCED_FILTER_OPTIONS', EMAILMaker_Field_Model::getAdvancedFilterOptions());
            $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', EMAILMaker_Field_Model::getAdvancedFilterOpsByFieldType());
            $viewer->assign('ADVANCE_CRITERIA', Zend_Json::decode(decode_html($emailtemplateResult["conditions"])));
            $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
            $viewer->assign('SOURCE_MODULE', $selectedModuleName);
        }
        $viewer->view('Edit.tpl', 'EMAILMaker');
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array("modules.EMAILMaker.resources.ckeditor.ckeditor", "libraries.jquery.ckeditor.adapters.jquery", "libraries.jquery.jquery_windowmsg", "modules.$moduleName.resources.AdvanceFilter");
        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.lib.codemirror";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.mode.javascript.javascript";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.selection.active-line";
            $jsFileNames[] = "modules.ITS4YouStyles.resources.CodeMirror.addon.edit.matchbrackets";
        }
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
    public function getHeaderCss(Vtiger_Request $request) {
        $headerCssInstances = parent::getHeaderCss($request);
        if (vtlib_isModuleActive("ITS4YouStyles")) {
            $cssFileNames = array('~/modules/ITS4YouStyles/resources/CodeMirror/lib/codemirror.css',);
            $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
            $headerCssInstances = array_merge($headerCssInstances, $cssInstances);
        }
        return $headerCssInstances;
    }
    function getModuleLanguageArray($module) {
        if (file_exists("languages/" . $this->cu_language . "/" . $module . ".php")) $current_mod_strings_lang = $this->cu_language;
        else $current_mod_strings_lang = "en_us";
        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);
        return $current_mod_strings_big['languageStrings'];
    }
    function getCustomFunctionsList() {
        $ready = false;
        $function_name = "";
        $function_params = $functions = array();
        $files = glob('modules/EMAILMaker/resources/functions/*.php');
        foreach ($files as $file) {
            $filename = $file;
            $source = fread(fopen($filename, "r"), filesize($filename));
            $tokens = token_get_all($source);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_FUNCTION) $ready = true;
                    elseif ($ready) {
                        if ($token[0] == T_STRING && $function_name == "") $function_name = $token[1];
                        elseif ($token[0] == T_VARIABLE) $function_params[] = $token[1];
                    }
                } elseif ($ready && $token == "{") {
                    $ready = false;
                    $functions[$function_name] = $function_params;
                    $function_name = "";
                    $function_params = array();
                }
            }
        }
        $customFunctions[""] = vtranslate("LBL_PLS_SELECT", 'EMAILMaker');
        foreach ($functions as $funName => $params) {
            $parString = implode("|", $params);
            $custFun = trim($funName . "|" . str_replace("$", "", $parString), "|");
            $customFunctions[$custFun] = $funName;
        }
        return $customFunctions;
    }
    public function selectTheme(Vtiger_Request $request) {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("EDIT") == false) $EMAILMaker->DieDuePermission();
        $viewer = $this->getViewer($request);
        $viewer->assign("VERSION", EMAILMaker_Version_Helper::$version);
        $source_path = getcwd() . "/modules/EMAILMaker/templates";
        $dir_iterator = new RecursiveDirectoryIterator($source_path);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        $i = 0;
        foreach ($iterator as $folder) {
            $folder_name = substr($folder, strlen($source_path) + 1);
            if ($folder->isDir()) {
                $other_folder = strpos($folder_name, "/");
                if ($other_folder === false && file_exists($folder . "/index.html") && file_exists($folder . "/image.png")) {
                    $EmailTemplates[] = $folder_name;
                }
            }
            $i++;
        }
        asort($EmailTemplates);
        $viewer->assign("EMAILTEMPLATESPATH", $source_path);
        $viewer->assign("EMAILTEMPLATES", $EmailTemplates);
        $Themes_Data = $EMAILMaker->GetThemesData();
        $viewer->assign("EMAILTHEMES", $Themes_Data);
        $category = getParentTab();
        $viewer->assign("CATEGORY", $category);
        $viewer->view('EditSelectContent.tpl', 'EMAILMaker');
    }
} 
?>