<?php
class EMAILMaker_List_View extends Vtiger_Index_View {
    protected $listViewLinks = false;
    protected $isInstalled = true;
    public function __construct() {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->exposeMethod('getList');
    }
    public function preProcess(Vtiger_Request $request, $display = true) {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
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
    function preProcessTplName(Vtiger_Request $request) {
        return 'ListViewPreProcess.tpl';
    }
    public function postProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl');
        parent::postProcess($request);
    }
    public function process(Vtiger_Request $request) {
        $this->invokeExposedMethod('getList', $request);
    }
    public function getList(Vtiger_Request $request) {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMakerModel = Vtiger_Module_Model::getInstance('EMAILMaker');
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("DETAIL") == false) $EMAILMaker->DieDuePermission();
        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $orderby = "templateid";
        $dir = "asc";
        if (isset($_REQUEST["dir"]) && $_REQUEST["dir"] == "desc") $dir = "desc";
        if (isset($_REQUEST["orderby"])) {
            switch ($_REQUEST["orderby"]) {
                case "name":
                    $orderby = "templatename";
                break;
                default:
                    $orderby = $_REQUEST["orderby"];
                break;
            }
        }
        $viewer->assign('VERSION_TYPE', 'profesional');
        $viewer->assign("VERSION", EMAILMaker_Version_Helper::$version);
        if ($EMAILMaker->CheckPermissions("EDIT") && $this->isInstalled) {
            $viewer->assign("EXPORT", "yes");
        }
        if ($EMAILMaker->CheckPermissions("EDIT") && $this->isInstalled) {
            $viewer->assign("EDIT", "permitted");
            $viewer->assign("IMPORT", "yes");
        }
        if ($EMAILMaker->CheckPermissions("DELETE") && $this->isInstalled) {
            $viewer->assign("DELETE", "permitted");
        }
        $viewer->assign("MOD", $mod_strings);
        $viewer->assign("APP", $app_strings);
        $viewer->assign("THEME", $theme);
        $viewer->assign("PARENTTAB", getParentTab());
        $viewer->assign("IMAGE_PATH", $image_path);
        $viewer->assign("ORDERBY", $orderby);
        $viewer->assign("DIR", $dir);
        $Search_Selectbox_Data = $EMAILMaker->getSearchSelectboxData();
        $viewer->assign("SEARCHSELECTBOXDATA", $Search_Selectbox_Data);
        $return_data = $EMAILMaker->GetListviewData($orderby, $dir, "", false, $request);
        $category = getParentTab();
        $viewer->assign("CATEGORY", $category);
        $current_user = Users_Record_Model::getCurrentUserModel();
        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
        $linkModels = $EMAILMakerModel->getListViewLinks($linkParams);
        $viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);
        $viewer->assign('LISTVIEW_LINKS', $linkModels);
        if (is_admin($current_user)) {
            $viewer->assign('IS_ADMIN', '1');
        }
        $WTemplateIds = array();
        $workflows_query = $EMAILMaker->geEmailWorkflowsQuery();
        $workflows_result = $adb->pquery($workflows_query, array());
        $workflows_num_rows = $adb->num_rows($workflows_result);
        if ($workflows_num_rows > 0) {
            require_once ('modules/EMAILMaker/workflow/VTEMAILMakerMailTask.php');
            for ($i = 0;$i < $workflows_num_rows;$i++) {
                $data = $adb->raw_query_result_rowdata($workflows_result, $i);
                $task = $data["task"];
                $taskObject = unserialize($task);
                $wtemplateid = $taskObject->template;
                if (!in_array($wtemplateid, $WTemplateIds)) {
                    $WTemplateIds[] = $wtemplateid;
                }
            }
        }
        $viewer->assign('WTEMPLATESIDS', $WTemplateIds);
        if ($request->has('search_workflow') && !$request->isEmpty('search_workflow')) {
            $search_workflow = $request->get('search_workflow');
            foreach ($return_data AS $n => $Data) {
                if ($search_workflow == "wf_0") {
                    if (in_array($Data["templateid"], $WTemplateIds)) {
                        echo " unset " . $Data["templateid"] . "<br>";
                        unset($return_data[$n]);
                    }
                } else {
                    if (!in_array($Data["templateid"], $WTemplateIds)) {
                        unset($return_data[$n]);
                    }
                }
            }
        }
        $viewer->assign("EMAILTEMPLATES", $return_data);
        $sharing_types = Array("" => "", "public" => vtranslate("PUBLIC_FILTER", 'EMAILMaker'), "private" => vtranslate("PRIVATE_FILTER", 'EMAILMaker'), "share" => vtranslate("SHARE_FILTER", 'EMAILMaker'));
        $viewer->assign("SHARINGTYPES", $sharing_types);
        $Status = array("status_1" => vtranslate("Active", 'EMAILMaker'), "status_0" => vtranslate("Inactive", 'EMAILMaker'));
        $viewer->assign("STATUSOPTIONS", $Status);
        $WF = array("wf_1" => vtranslate("LBL_YES", 'EMAILMaker'), "wf_0" => vtranslate("LBL_NO", 'EMAILMaker'));
        $viewer->assign("WFOPTIONS", $WF);
        $Search_Types = array("templatename", "module", "category", "description", "sharingtype", "owner", "status", "workflow");
        if ($request->has('search_params') && !$request->isEmpty('search_params')) {
            $searchParams = $request->get('search_params');
            foreach ($searchParams as $groupInfo) {
                if (empty($groupInfo)) {
                    continue;
                }
                foreach ($groupInfo as $fieldSearchInfo) {
                    $fieldName = $st = $fieldSearchInfo[0];
                    $operator = $fieldSearchInfo[1];
                    $search_val = $fieldSearchInfo[2];
                    $viewer->assign("SEARCH_" . strtoupper($st) . "VAL", $search_val);
                    $searchParams[$fieldName] = $fieldSearchInfo;
                }
            }
        } else {
            $searchParams = array();
        }
        $viewer->assign("MAIN_PRODUCT_SUPPORT", '');
        $viewer->assign("MAIN_PRODUCT_WHITELABEL", '');
        $viewer->assign("MODULE", 'EMAILMaker');
        $viewer->assign('SEARCH_DETAILS', $searchParams);
        $viewer->view("ListEMAILTemplatesContents.tpl", 'EMAILMaker');
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = array("layouts.v7.modules.EMAILMaker.resources.License", "layouts.v7.modules.Vtiger.resources.List", "layouts.v7.modules.EMAILMaker.resources.List");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
} ?>