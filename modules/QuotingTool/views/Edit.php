<?php

include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class QuotingTool_Edit_View
 */
class QuotingTool_Edit_View extends Vtiger_Edit_View
{
    /**
     * @var bool
     */
    protected $record = false;
    /**
     * @constructor
     */
    public function __construct()
    {
        parent::__construct();        
    }
    /**
     * @param Vtiger_Request $request
     * @return bool|void
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $record = $request->get("record");
        $recordPermission = Users_Privileges_Model::isPermitted($moduleName, "EditView", $record);
        if (!$recordPermission) {
            throw new AppException(vtranslate("LBL_PERMISSION_DENIED"));
        }
    }
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        global $current_user;
        global $vtiger_current_version;
        global $adb;
        global $HELPDESK_SUPPORT_EMAIL_ID;
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $quotingTool = new QuotingTool();
        $primaryModule = $request->get("primary_module");
        $record = $request->get("record");
        $quotingToolRecordModel = new QuotingTool_Record_Model();
        $template = $quotingToolRecordModel->getById($record);
        if (version_compare($vtiger_current_version, "7.0.0", ">=") && $record != "") {
            $template = $this->updateModule($template);
        }
        $userProfile = array("user_name" => $current_user->user_name, "first_name" => $current_user->first_name, "last_name" => $current_user->last_name, "full_name" => $current_user->first_name . " " . $current_user->last_name, "email1" => $current_user->email1);
        $quotingToolSettingRecordModel = new QuotingTool_SettingRecord_Model();
        $settings = array();
        if ($template) {
            $objSettings = $quotingToolSettingRecordModel->findByTemplateId($record);
            if ($objSettings) {
                $settings = array("template_id" => $objSettings->get("template_id"), "description" => $objSettings->get("description"), "expire_in_days" => $objSettings->get("expire_in_days"), "label_decline" => $objSettings->get("label_decline"), "label_accept" => $objSettings->get("label_accept"), "background" => json_decode(html_entity_decode($objSettings->get("background"))), "success_content" => $objSettings->get("success_content"), "email_signed" => $objSettings->get("email_signed"), "email_from_copy" => $objSettings->get("email_from_copy"), "email_bcc_copy" => $objSettings->get("email_bcc_copy"), "email_body_copy" => $objSettings->get("email_body_copy"), "email_subject_copy" => $objSettings->get("email_subject_copy"), "ignore_border_email" => $objSettings->get("ignore_border_email"), "track_open" => $objSettings->get("track_open"), "decline_message" => $objSettings->get("decline_message"), "enable_decline_mess" => $objSettings->get("enable_decline_mess"), "date_format" => $objSettings->get("date_format"));
            }
        } else {
            $template = Vtiger_Record_Model::getCleanInstance($moduleName);
            $template->set("module", $primaryModule);
        }
        $vteItemsModuleName = "VTEItems";
        $vteItemsModuleModel = Vtiger_Module_Model::getInstance($vteItemsModuleName);
        $quoterModuleName = "Quoter";
        $quoterModel = Vtiger_Module_Model::getInstance($quoterModuleName);
        if ($vteItemsModuleModel && $quoterModel && $quoterModel->isActive() && $vteItemsModuleModel->isActive()) {
            $columnDefault = array("item_name", "quantity", "listprice", "total", "tax_total", "net_price", "comment", "discount_amount", "discount_percent");
            $listTable = array("quoter_quotes_settings", "quoter_invoice_settings", "quoter_salesorder_settings", "quoter_purchaseorder_settings");
            $settingsFieldItems = array();
            $breakFields = array();
            foreach ($listTable as $table) {
                $rs = $adb->pquery("SELECT * FROM " . $table, array());
                if (0 < $adb->num_rows($rs)) {
                    $data = $adb->fetchByAssoc($rs, 0);
                    $module = $data["module"];
                    $settingsFieldItems[$module][] = "sequence";
                    foreach ($data as $key => $val) {
                        if (!empty($val) && $key != "module" && $key != "total_fields" && $key != "section_setting") {
                            if ($key == "item_name") {
                                $settingsFieldItems[$module][] = "productid";
                            } else {
                                $decodeVal = json_decode(html_entity_decode($val));
                                if ($decodeVal->isActive != "active") {
                                    $breakFields[$module][] = $key;
                                }
                                $settingsFieldItems[$module][] = $key;
                            }
                        }
                    }
                }
            }
            $vteItemsModuleInfo = $quotingTool->parseModule($vteItemsModuleModel);
            $settingFieldsQuoter = array();
            foreach ($settingsFieldItems as $module => $value) {
                $settingFieldsQuoter[$module]["id"] = $vteItemsModuleModel->getId();
                $settingFieldsQuoter[$module]["name"] = $vteItemsModuleModel->getName();
                $settingFieldsQuoter[$module]["label"] = vtranslate($vteItemsModuleModel->get("label"), $vteItemsModuleModel->getName());
                foreach ($vteItemsModuleInfo["fields"] as $key => $val) {
                    if ($vteItemsModuleInfo["fields"][$key]["token"] == "\$VTEItems__tax_totalamount\$") {
                        continue;
                    }
                    if (in_array($val["name"], $value)) {
                        if (in_array($val["name"], $breakFields[$module])) {
                            continue;
                        }
                        $vteItemsModuleInfo["fields"][$key]["block"]["name"] = "LBL_ITEM_DETAILS";
                        $vteItemsModuleInfo["fields"][$key]["block"]["label"] = vtranslate("LBL_ITEM_DETAILS", $vteItemsModuleName);
                        $settingFieldsQuoter[$module]["fields"][] = $vteItemsModuleInfo["fields"][$key];
                    }
                }
                $sequence = array("id" => "", "uitype" => "", "datatype" => "text", "name" => "sequence", "label" => "Sequence No", "token" => "\$VTEItems__sequence\$", "block" => array("id" => "", "name" => "LBL_ITEM_DETAILS", "label" => vtranslate("LBL_ITEM_DETAILS", $module)));
                $itemNameWithDes = array("id" => "", "uitype" => "", "datatype" => "text", "name" => "itemNameWithDes", "label" => "Item Name (with description)", "token" => "\$VTEItems__productid\$\$VTEItems__comment\$", "block" => array("id" => "", "name" => "LBL_ITEM_DETAILS", "label" => vtranslate("LBL_ITEM_DETAILS", $module)));
                $settingFieldsQuoter[$module]["fields"][] = $sequence;
                $settingFieldsQuoter[$module]["fields"][] = $itemNameWithDes;
            }
            $fieldSpecial = array("source", "starred", "tags", "related_to");
            foreach ($vteItemsModuleInfo["fields"] as $key => $item) {
                if (in_array($item["name"], $fieldSpecial)) {
                    unset($vteItemsModuleInfo["fields"][$key]);
                }
            }
            $vteItemsModuleInfo["fields"] = array_values($vteItemsModuleInfo["fields"]);
            $quoterSettings = array();
            $totalSetting = $quoterModel->getAllTotalFieldsSetting();
            foreach ($totalSetting as $module => $setting) {
                $moduleInfo = $settingFieldsQuoter[$module];
                $totalBlock = array("name" => "LBL_TOTAL_BLOCK", "fields" => array());
                foreach ($setting as $totalFieldName => $totalField) {
                    $fieldLabel = vtranslate($totalField["fieldLabel"], $quoterModuleName);
                    if ($totalField["fieldLabel"] == "Tax (%)") {
                        $fieldLabel = "Tax";
                    }
                    $totalBlock["fields"][] = array("name" => $totalFieldName, "datatype" => $totalField["fieldType"], "label" => $fieldLabel);
                }
                $blocks = array();
                $blocks[] = $totalBlock;
                $moduleInfo["final_details"] = $quotingTool->fillBlockFields($vteItemsModuleName, $blocks);
                $quoterSettings[$module] = $moduleInfo;
            }
            $viewer->assign("QUOTER_SETTINGS", $quoterSettings);
        }
        $memberGroups = Settings_Groups_Member_Model::getAll();
        $groupMembers = array();
        foreach ($memberGroups as $label => $member) {
            foreach ($member as $key => $values) {
                $arrayValue = array();
                $arrayValue["id"] = $values->get("id");
                $arrayValue["name"] = str_replace("&quot;", "\"", $values->get("name"));
                $arrayValue["role"] = $label;
                $groupMembers[] = $arrayValue;
            }
        }
        $viewer->assign("MEMBER_GROUPS", $groupMembers);
        $isIconHelpText = $quotingToolRecordModel->isIconHelpText();
        $customFunctions = QuotingTool::getCustomFunctions();
        $pageNumber = array("token" => "Page #PG_NUM# of #PG_NUM_TOTAL#", "name" => "pageNumber", "label" => "pageNumber");
        $customFunctions[] = $pageNumber;
        $dataDateFormat = $quotingToolRecordModel->getDateFormat();
        $viewer->assign("RECORD_ID", $record);
        $viewer->assign("MODULE", $moduleName);
        $viewer->assign("TEMPLATE", $template);
        $viewer->assign("SETTINGS", QuotingToolUtils::jsonUnescapedSlashes(json_encode($settings, JSON_FORCE_OBJECT)));
        $viewer->assign("USER_PROFILE", $userProfile);
        $viewer->assign("CONFIG", QuotingTool::getConfig());
        $viewer->assign("MODULES", QuotingTool::getModules());
        $viewer->assign("CUSTOM_FUNCTIONS", $customFunctions);
        $viewer->assign("CUSTOM_FIELDS", QuotingTool::getCustomFields());
        $viewer->assign("COMPANY_FIELDS", QuotingTool::getCompanyFields());
        $viewer->assign("MERGE_FIELDS", array("Text Field", "Checkbox", "Date", "Text Area Field"));
        $viewer->assign("ICON_HELPTEXT", $isIconHelpText);
        $viewer->assign("HELPDESK_SUPPORT_EMAIL_ID", $HELPDESK_SUPPORT_EMAIL_ID);
        $viewer->assign("DATA_DATE_FORMAT", $dataDateFormat);
        $viewer->view("EditView.tpl", $moduleName);
    }
    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $fontAwesome = "";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $fontAwesome = "~/" . $template_folder . "/modules/" . $moduleName . "/resources/css/font-awesome-4.5.0/css/font-awesome.min.css";
        } else {
            $template_folder = "layouts/v7";
        }
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = array("~/" . $template_folder . "/modules/" . $moduleName . "/resources/css/bootstrap.icon.css", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ckeditor_4.5.6_full/CustomFonts/fonts.css", "~/libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker.css", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/signature-pad/assets/jquery.signaturepad.css", $fontAwesome, "https://fonts.googleapis.com/icon?family=Material+Icons", "~/modules/" . $moduleName . "/resources/styles.css", "~/modules/" . $moduleName . "/resources/web.css", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/css/app.css", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/css/font.php", "~/libraries/jquery/colorpicker/css/colorpicker.css");
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);
        return $headerCssInstances;
    }
    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = array("~/modules/" . $moduleName . "/resources/mpdf/mpdf.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/modernizr-2.8.3/modernizr.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/angularjs-1.3.1/angular.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/angular-resource-1.3.1/angular-resource.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/angular-ui-router-0.2.11/angular-ui-router.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/angular-translate-2.4.2/angular-translate.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ui-bootstrap-tpls-0.14.3/ui-bootstrap-tpls-0.14.3.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/angular-sanitize-1.2.26/angular-sanitize.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/jquery.nicescroll-3.6.0/jquery.nicescroll.min.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ckeditor_4.5.6_full/override_ckeditor.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ckeditor_4.5.6_full/ckeditor.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ckeditor_4.5.6_full/adapters/jquery.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/ng-ckeditor-0.2.0/ng-ckeditor.min.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/signature-pad/jquery.signaturepad.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/signature-pad/assets/flashcanvas.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/css-element-queries/src/ResizeSensor.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/libs/css-element-queries/src/ElementQueries.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/configs/app-constants.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/configs/app-config.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/app.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/utils/app-utils.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/utils/helper.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/utils/jQuery-customs.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/directives/app-directive.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/directives/file.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/directives/datetime.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/directives/select2.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/locale/i18n.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/locale/app-i18n.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/locale/en.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/models/app-model.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/models/template.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/controllers/app-controller.js", "~/" . $template_folder . "/modules/" . $moduleName . "/resources/js/controllers/right-panel-controller.js", "modules.Emails.resources.Emails", "libraries/jquery/colorpicker/js/colorpicker", "libraries/jquery/colorpicker/js/eye", "libraries/jquery/colorpicker/js/utils");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
    public function updateModule($template)
    {
        $body = base64_decode($template->get("body"));
        $find = "layouts/vlayout/modules/QuotingTool";
        $replace = "layouts/v7/modules/QuotingTool";
        $results = str_replace($find, $replace, $body);
        $data = $template->set("body", base64_encode($results));
        return $data;
    }
}

?>