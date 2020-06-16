<?php 

require_once ('modules/ITS4YouReports/ITS4YouReports.php');

class ITS4YouReports_List_View extends Vtiger_Index_View {
    protected $listViewHeaders = false;
    protected $listViewEntries = false;
    protected $listViewCount = false;
    protected $isInstalled = true;
    public function __construct() {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
    }
    public function process(Vtiger_Request $request) {
        $this->getProcess($request);
    }
    public function preProcessTplName(Vtiger_Request $request) {
        return (!$this->isInstalled) ? 'IndexViewPreProcess.tpl' : $this->getPreProcessTplName($request);
    }
    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Reports_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }
    function preProcess(Vtiger_Request $request, $display = true) {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $folders = $moduleModel->getFolders();
        $listViewModel = new ITS4YouReports_ListView_Model();
        $listViewModel->set('module', $moduleModel);
        $folderId = $request->get('viewname');
        if (empty($folderId) || $folderId == 'undefined') {
            $folderId = 'All';
        }
        $listViewModel->set('folderid', $folderId);
        $pageNumber = $request->get('page');
        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
        $listViewMassActionModels = $listViewModel->getListViewMassActions($linkParams);
        $linkModels = $listViewModel->getListViewLinks($linkParams);
        if (empty($pageNumber)) {
            $pageNumber = '1';
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        $reports_to_import = ITS4YouReports::GetReports4YouForImport();
        if ($this->isInstalled && empty($reports_to_import) && file_exists("modules/ITS4YouReports/highcharts/highcharts.php") === true && file_exists("modules/ITS4YouReports/highcharts/js/canvg.js") === true) {
            $searchParmams = $request->get('search_params');
            if (empty($searchParmams)) {
                $searchParmams = [];
            }
            $listViewModel->set("search_params", $searchParmams);
            if (!$this->listViewHeaders) {
                $this->listViewHeaders = $listViewModel->getListViewHeaders();
            }
            if (!$this->listViewEntries) {
                $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
            }
            if ($this->listViewEntries) {
                if ($listViewModel->get('folderid') == "All") {
                    $folderid = false;
                } else {
                    $folderid = $listViewModel->get('folderid');
                }
                $listViewEntriesPgn = ITS4YouReports::sgetRptsforFldr($folderid, false);
                $pageLimit = vglobal('list_max_entries_per_page');
                $t_range = '';
                $noOfEntries = count($listViewEntriesPgn);
                if ($noOfEntries > 0) {
                    if ($pageNumber == 1) {
                        $pagingModel->set('prevPageExists', false);
                    } else {
                        $pagingModel->set('prevPageExists', true);
                    }
                    if ($pageNumber < ($noOfEntries / $pageLimit)) {
                        $pagingModel->set('nextPageExists', true);
                    } else {
                        $pagingModel->set('nextPageExists', false);
                    }
                    $range = $pagingModel->getRecordRange();
                    $t_range = $range;
                }
                $pagingModel->set("limit", $pageLimit);
                $pagingModel = $pagingModel->calculatePageRange($listViewEntriesPgn);
                $pagingModel->set("range", $t_range);
            }
            $viewer->assign('PAGING_MODEL', $pagingModel);
            $noLicense = 0;
        } else {
            $noLicense = 1;
        }
        $viewer->assign('NO_LICENSE', $noLicense);
        $viewer->assign('LISTVIEW_LINKS', $linkModels);
        $viewer->assign('FOLDERS', $folders);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEWNAME', $folderId);
        $viewer->assign('PAGE_NUMBER', $pageNumber);
        $viewer->assign('LISTVIEW_MASSACTIONS', $listViewMassActionModels);
        $viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
        if (!$this->listViewCount) {
            $this->listViewCount = $listViewModel->getListViewCount();
        }
        $totalCount = $this->listViewCount;
        $pageLimit = $pagingModel->getPageLimit();
        $pageCount = ceil((int)$totalCount / (int)$pageLimit);
        if ($pageCount == 0) {
            $pageCount = 1;
        }
        $viewer->assign('PAGE_COUNT', $pageCount);
        $viewer->assign('LISTVIEW_COUNT', $totalCount);
        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view') ];
        $linkModels = $moduleModel->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
    function getPreProcessTplName(Vtiger_Request $request) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        if ($layout == "v7") {
            return 'ListViewPreProcess.tpl';
        } else {
            $reports_to_import = ITS4YouReports::GetReports4YouForImport();
            if ($this->isInstalled && empty($reports_to_import) && file_exists("modules/ITS4YouReports/highcharts/highcharts.php") === true && file_exists("modules/ITS4YouReports/highcharts/js/canvg.js") === true) {
                return 'ListViewPreProcess.tpl';
            } else {
                return 'InstallPreProcess.tpl';
            }
        }
    }
    function getProcess(Vtiger_Request $request) {
        $ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();
        $reports_to_import = ITS4YouReports::GetReports4YouForImport();
        $viewer = $this->getViewer($request);
        if ($this->isInstalled && empty($reports_to_import) && file_exists("modules/ITS4YouReports/highcharts/highcharts.php") === true && file_exists("modules/ITS4YouReports/highcharts/js/canvg.js") === true) {
            $moduleName = $request->getModule();
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $folderId = $request->get('viewname');
            if (empty($folderId) || $folderId == 'undefined') {
                $folderId = 'All';
            }
            $pageNumber = $request->get('page');
            $orderBy = $request->get('orderby');
            $sortOrder = $request->get('sortorder');
            if ($sortOrder == "ASC") {
                $nextSortOrder = "DESC";
                $sortImage = "icon-chevron-down";
            } else {
                $nextSortOrder = "ASC";
                $sortImage = "icon-chevron-up";
            }
            $listViewModel = new ITS4YouReports_ListView_Model();
            $listViewModel->set('module', $moduleModel);
            $listViewModel->set('folderid', $folderId);
            if (!empty($orderBy)) {
                $listViewModel->set('orderby', $orderBy);
                $listViewModel->set('sortorder', $sortOrder);
            }
            $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
            $listViewMassActionModels = $listViewModel->getListViewMassActions($linkParams);
            if (empty($pageNumber)) {
                $pageNumber = '1';
            }
            $viewer->assign('MODULE', $moduleName);
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
            $viewer->assign('LISTVIEW_MASSACTIONS', $listViewMassActionModels);
            $searchParmams = $request->get('search_params');
            if (empty($searchParmams)) {
                $searchParmams = [];
            }
            $listViewModel->set("search_params", $searchParmams);
            if (!$this->listViewHeaders) {
                $this->listViewHeaders = $listViewModel->getListViewHeaders();
            }
            if (!$this->listViewEntries) {
                $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
            }
            $pagingModel = $pagingModel->calculatePageRange($this->listViewEntries);
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $noOfEntries = count($this->listViewEntries);
            $searchParams = $request->get('search_params');
            $searchParams = $searchParams[0];
            if (!empty($searchParams)) {
                $listSearchParams = array();
                foreach ($searchParams as $conditions) {
                    $fieldname = $conditions[0];
                    $searchValue = $conditions[2];
                    $comparator = $conditions[1];
                    $listSearchParams[$fieldname] = array('searchValue' => $searchValue, 'comparator' => $comparator);
                }
                $viewer->assign('SEARCH_DETAILS', $listSearchParams);
            }
            $viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
            $viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
            $viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
            $viewer->assign('MODULE_MODEL', $moduleModel);
            $viewer->assign('VIEWNAME', $folderId);
            $viewer->assign('ORDER_BY', $orderBy);
            $viewer->assign('SORT_ORDER', $sortOrder);
            $viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
            $viewer->assign('SORT_IMAGE', $sortImage);
            $viewer->assign('COLUMN_NAME', $orderBy);
            $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $viewer->assign('RECORD_ACTIONS', $this->getRecordActionsFromModule($moduleModel));
            if (!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int)$totalCount / (int)$pageLimit);
            if ($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign('PAGE_COUNT', $pageCount);
            $viewer->assign('LISTVIEW_COUNT', $totalCount);
            if ($noOfEntries > 0) {
                if ($pageNumber == 1) {
                    $pagingModel->set('prevPageExists', false);
                } else {
                    $pagingModel->set('prevPageExists', true);
                }
                if (1 < $pageCount && $pageNumber < $pageCount) {
                    $pagingModel->set('nextPageExists', true);
                } else {
                    $pagingModel->set('nextPageExists', false);
                }
                $range = $pagingModel->getRecordRange();
                $t_range = $range;
                $pagingModel->set("range", $t_range);
            }
            $viewer->assign('PAGING_MODEL', $pagingModel);
            $viewer->assign('PAGE_NUMBER', $pageNumber);
            $viewer->assign("VERSION_TYPE", 'professional');
            $viewer->assign("VERSION", ITS4YouReports_Version_Helper::$version);
            $viewer->view('ListViewContents.tpl', $moduleName);
        } else {
            $viewer = $this->getViewer($request);
            $s = "site_URL";
            $step = 1;
            $current_step = 1;
            if ($this->isInstalled) {
                if (!empty($reports_to_import) && $request->has("import_reports") != 1) {
                    $step = 1;
                } else {
                    $step = 2;
                }
                $total_steps = 3;
                if ($request->has("import_reports")) {
                    if ($request->get("import_reports") === 'true') {
                        $return_html = "<table>";
                        $reports_to_import = ITS4YouReports::GetReports4YouForImport();
                        if (!empty($reports_to_import)) {
                            foreach ($reports_to_import as $file_to_import) {
                                $return = ITS4YouReports::ImportReports4You($file_to_import);
                                $return_html.= "<tr><td align='left' valign='top' style='padding-left:40px;'>$return</td></tr>";
                            }
                        } else {
                            echo "<tr><td align='left' valign='top' style='padding-left:40px;'>" . getTranslatedString("LBL_ANY_TO_IMPORT", "ITS4YouReports") . "</td></tr>";
                        }
                        $return_html.= "</table>";
                    } else {
                        if (!empty($reports_to_import)) {
                            foreach ($reports_to_import as $file_to_import) {
                                $new_imported_file = str_replace("reports/", "reports/imported/", $file_to_import);
                                copy($file_to_import, $new_imported_file);
                            }
                        }
                    }
                }
            } else {
                $viewer->assign("TYPE", 'reactivate');
                $step = 1;
                $total_steps = 3;
            }
            $viewer->assign("STEP", $step);
            $viewer->assign("CURRENT_STEP", $current_step);
            $viewer->assign("TOTAL_STEPS", $total_steps);
            $company_details = Vtiger_CompanyDetails_Model::getInstanceById();
            $viewer->assign("ORGANIZATION", $company_details);
            $viewer->assign("COMPANY_DETAILS", $company_details);
            $viewer->assign("URL", vglobal($s));
            $viewer->view('Install.tpl', 'ITS4YouReports');
        }
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ["modules.Vtiger.resources.List", "modules.$moduleName.resources.List", "modules.$moduleName.resources.License", "modules.Vtiger.resources.ListSidebar", ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
    function getRecordsCount(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $cvId = $request->get('viewname');
        $count = $this->getListViewCount($request);
        $result = [];
        $result['module'] = $moduleName;
        $result['viewname'] = $cvId;
        $result['count'] = $count;
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
    function getListViewCount(Vtiger_Request $request) {
        $folderId = $request->get('viewname');
        if (empty($folderId)) {
            $folderId = 'All';
        }
        $listViewModel = new ITS4YouReports_ListView_Model();
        $listViewModel->set('folderid', $folderId);
        $count = $listViewModel->getListViewCount();
        return $count;
    }
    function getPageCount(Vtiger_Request $request) {
        $listViewCount = $this->getListViewCount($request);
        $pagingModel = new Vtiger_Paging_Model();
        $pageLimit = $pagingModel->getPageLimit();
        $pageCount = ceil((int)$listViewCount / (int)$pageLimit);
        if ($pageCount == 0) {
            $pageCount = 1;
        }
        $result = [];
        $result['page'] = $pageCount;
        $result['numberOfRecords'] = $listViewCount;
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
    public function getRecordActionsFromModule($moduleModel) {
        $editPermission = $deletePermission = $duplicatePermission = 0;
        if ($moduleModel) {
            $editPermission = $moduleModel->isPermitted('EditView');
            $duplicatePermission = $moduleModel->isPermitted('EditView');
            $deletePermission = $moduleModel->isPermitted('Delete');
        }
        $recordActions = array();
        $recordActions['edit'] = $editPermission;
        $recordActions['delete'] = $deletePermission;
        $recordActions['duplicate'] = $duplicatePermission;
        return $recordActions;
    }
} ?>