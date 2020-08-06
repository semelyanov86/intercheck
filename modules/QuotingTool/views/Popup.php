<?php

class QuotingTool_Popup_View extends Vtiger_Popup_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->get("child_module");
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate($moduleName, $moduleName) . " " . vtranslate("LBL_NOT_ACCESSIBLE"));
        }
    }
    /**
     * Function returns the module name for which the popup should be initialized
     * @param Vtiger_request $request
     * @return <String>
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();
        $this->initializeListViewContents($request, $viewer);
        $viewer->assign("COMPANY_LOGO", $companyLogo);
        $viewer->view("Popup.tpl", $moduleName);
    }
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        global $adb;
        $moduleName = $request->get("child_module");
        $cvId = $request->get("cvid");
        $pageNumber = $request->get("page");
        $orderBy = $request->get("orderby");
        $sortOrder = $request->get("sortorder");
        $sourceModule = $request->get("src_module");
        $sourceField = $request->get("src_field");
        $sourceRecord = $request->get("src_record");
        $searchKey = $request->get("search_key");
        $searchValue = $request->get("search_value");
        $currencyId = $request->get("currency_id");
        $relatedParentModule = $request->get("related_parent_module");
        $relatedParentId = $request->get("related_parent_id");
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $searchParams = $request->get("search_params");
        $relationId = $request->get("relationId");
        if (!$relationId) {
            $result = $adb->pquery("select relation_id from vtiger_relatedlists where tabid = ? AND related_tabid = ?", array(getTabid($moduleName), getTabid("Quotes")));
            $relationId = $adb->query_result($result, 0, 0);
        }
        if (!$relatedParentId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord);
            if (!$recordModel->get("potential_id")) {
                $relatedParentId = 0;
                $sourceRecord = 0;
            } else {
                $relatedParentId = $recordModel->get("potential_id");
                $sourceRecord = $recordModel->get("potential_id");
            }
        }
        $getUrl = $request->get("get_url");
        $autoFillModule = $moduleModel->getAutoFillModule($moduleName);
        $multiSelectMode = $request->get("multi_select");
        if (empty($multiSelectMode)) {
            $multiSelectMode = false;
        }
        if (empty($getUrl) && !empty($sourceField) && !empty($autoFillModule) && !$multiSelectMode) {
            $getUrl = "getParentPopupContentsUrl";
        }
        if (empty($cvId)) {
            $cvId = "0";
        }
        if (empty($pageNumber)) {
            $pageNumber = "1";
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set("page", $pageNumber);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
        $isRecordExists = Vtiger_Util_Helper::checkRecordExistance($relatedParentId);
        if ($isRecordExists) {
            $relatedParentModule = "";
            $relatedParentId = "";
        } else {
            if ($isRecordExists === NULL) {
                $relatedParentModule = "";
                $relatedParentId = "";
            }
        }
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
            $listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
            $searchModuleModel = $listViewModel->getRelatedModuleModel();
        } else {
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
            $searchModuleModel = $listViewModel->getModule();
        }
        if ($moduleName == "Documents" && $sourceModule == "Emails") {
            $listViewModel->extendPopupFields(array("filename" => "filename"));
        }
        if (!empty($orderBy)) {
            $listViewModel->set("orderby", $orderBy);
            $listViewModel->set("sortorder", $sortOrder);
        }
        if (!empty($sourceModule)) {
            $listViewModel->set("src_module", $sourceModule);
            $listViewModel->set("src_field", $sourceField);
            $listViewModel->set("src_record", $sourceRecord);
        }
        if (!empty($searchKey) && !empty($searchValue)) {
            $listViewModel->set("search_key", $searchKey);
            $listViewModel->set("search_value", $searchValue);
        }
        $listViewModel->set("relationId", $relationId);
        if (!empty($searchParams)) {
            $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $searchModuleModel);
            $listViewModel->set("search_params", $transformedSearchParams);
        }
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $this->listViewHeaders = $listViewModel->getHeaders();
            $models = $listViewModel->getEntries($pagingModel);
            $noOfEntries = count($models);
            foreach ($models as $recordId => $recordModel) {
                foreach ($this->listViewHeaders as $fieldName => $fieldModel) {
                    $recordModel->set($fieldName, $recordModel->getDisplayValue($fieldName));
                }
                $models[$recordId] = $recordModel;
            }
            $this->listViewEntries = $models;
            if (0 < count($this->listViewEntries)) {
                $parent_related_records = true;
            }
        } else {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        if (!$parent_related_records && !empty($relatedParentModule) && !empty($relatedParentId)) {
            $relatedParentModule = NULL;
            $relatedParentId = NULL;
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
            if (!empty($orderBy)) {
                $listViewModel->set("orderby", $orderBy);
                $listViewModel->set("sortorder", $sortOrder);
            }
            if (!empty($sourceModule)) {
                $listViewModel->set("src_module", $sourceModule);
                $listViewModel->set("src_field", $sourceField);
                $listViewModel->set("src_record", $sourceRecord);
            }
            if (!empty($searchKey) && !empty($searchValue)) {
                $listViewModel->set("search_key", $searchKey);
                $listViewModel->set("search_value", $searchValue);
            }
            if (!empty($searchParams)) {
                $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $searchModuleModel);
                $listViewModel->set("search_params", $transformedSearchParams);
            }
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        if (empty($searchParams)) {
            $searchParams = array();
        }
        foreach ($searchParams as $fieldListGroup) {
            foreach ($fieldListGroup as $fieldSearchInfo) {
                $fieldSearchInfo["searchValue"] = $fieldSearchInfo[2];
                $fieldSearchInfo["fieldName"] = $fieldName = $fieldSearchInfo[0];
                $fieldSearchInfo["comparator"] = $fieldSearchInfo[1];
                $searchParams[$fieldName] = $fieldSearchInfo;
            }
        }
        $noOfEntries = count($this->listViewEntries);
        if (empty($sortOrder)) {
            $sortOrder = "ASC";
        }
        if ($sortOrder == "ASC") {
            $nextSortOrder = "DESC";
            $sortImage = "icon-chevron-down";
            $faSortImage = "fa-sort-desc";
        } else {
            $nextSortOrder = "ASC";
            $sortImage = "icon-chevron-up";
            $faSortImage = "fa-sort-asc";
        }
        $viewer->assign("MODULE", $moduleName);
        $viewer->assign("RELATED_MODULE", $moduleName);
        $viewer->assign("MODULE_NAME", $moduleName);
        $viewer->assign("SOURCE_MODULE", $sourceModule);
        $viewer->assign("SOURCE_FIELD", $sourceField);
        $viewer->assign("SOURCE_RECORD", $sourceRecord);
        $viewer->assign("RELATED_PARENT_MODULE", $relatedParentModule);
        $viewer->assign("RELATED_PARENT_ID", $relatedParentId);
        $viewer->assign("SEARCH_KEY", $searchKey);
        $viewer->assign("SEARCH_VALUE", $searchValue);
        $viewer->assign("RELATION_ID", $relationId);
        $viewer->assign("ORDER_BY", $orderBy);
        $viewer->assign("SORT_ORDER", $sortOrder);
        $viewer->assign("NEXT_SORT_ORDER", $nextSortOrder);
        $viewer->assign("SORT_IMAGE", $sortImage);
        $viewer->assign("FASORT_IMAGE", $faSortImage);
        $viewer->assign("GETURL", $getUrl);
        $viewer->assign("CURRENCY_ID", $currencyId);
        $viewer->assign("RECORD_STRUCTURE_MODEL", $recordStructureInstance);
        $viewer->assign("RECORD_STRUCTURE", $recordStructureInstance->getStructure());
        $viewer->assign("PAGING_MODEL", $pagingModel);
        $viewer->assign("PAGE_NUMBER", $pageNumber);
        $viewer->assign("LISTVIEW_ENTRIES_COUNT", $noOfEntries);
        $viewer->assign("LISTVIEW_HEADERS", $this->listViewHeaders);
        $viewer->assign("LISTVIEW_ENTRIES", $this->listViewEntries);
        $viewer->assign("SEARCH_DETAILS", $searchParams);
        $viewer->assign("MODULE_MODEL", $moduleModel);
        $viewer->assign("VIEW", $request->get("view"));
        if (PerformancePrefs::getBoolean("LISTVIEW_COMPUTE_PAGE_COUNT", false)) {
            if (!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int) $totalCount / (int) $pageLimit);
            if ($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign("PAGE_COUNT", $pageCount);
            $viewer->assign("LISTVIEW_COUNT", $totalCount);
        }
        $viewer->assign("MULTI_SELECT", $multiSelectMode);
        $viewer->assign("CURRENT_USER_MODEL", Users_Record_Model::getCurrentUserModel());
    }
}

?>