<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class VDUsers_List_View extends Vtiger_List_View
{
	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);

		$this->initializeListViewContents($request, $viewer);

		$viewer->view('ListViewContents.tpl', $request->getModule(false));
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		global $current_user;
		$moduleName = $request->getModule();
		$cvId = $this->getViewId();
		$roleId = $request->get('roleid');
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}
                
        $status = $request->get('status');
        if(empty($status))
            $status = 'Active';

		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);

		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'), 'CVID'=>$cvId);
		$linkModels = $listViewModel->getListViewMassActions($linkParams);
                $listViewModel->set('status', $status);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}

		$search_params = $request->get('vdsearch_params');
		$searchValue = $request->get('search_value');
        $operator = $request->get('operator');
		if(!empty($operator)) {
			$listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
		}
		if(!empty($searchValue)) {

			$listViewModel->set('search_value', $searchValue);
		}
        if(count($search_params) > 0) {

            $listViewModel->set('search_params', $search_params);
            $viewer->assign('SEARCH_DETAILS',$search_params);
        }

		if(!$this->listViewHeaders){
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if(!$this->listViewEntries){
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}

        // ADDONS
		$roles = VDUsers_Module_Model::getRoles($this->listViewEntries, $roleId);
		$roleList = Settings_Roles_Record_Model::getAll();
		$roleFields = VDUsers_Module_Model::getRoleFields($roleList);
		$allowedRoles = VDUsers_Module_Model::getAllowedRoles($roleFields);
		// echo '<pre>';print_r($allowedRoles);echo '</pre>';exit;

		$blockUsersList = VDUsers_Module_Model::sortUsersRole($this->listViewEntries);
		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('MODULE', $moduleName);

		if(!$this->listViewLinks){
			$this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);

		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);
        $viewer->assign('EDITUSER', VDUsers_Module_Model::getEditUsers());
		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);
		$viewer->assign('QUALIFIED_MODULE', 'Users');

		$viewer->assign('LISTVIEW_ENTRIES_COUNT',$noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('BLOCKS', $blockUsersList);

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			if(!$this->listViewCount){
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$totalCount = $this->listViewCount;
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}
		$viewer->assign('MODULE_MODEL', $listViewModel->getModule());
		$viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CVID', $cvId);
		$viewer->assign('ROLES', $roles);
		$viewer->assign('ROLEID', $roleId);
		$viewer->assign('ALLOWED_ROLES', $allowedRoles);
	}
	
	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$count = $this->getListViewCount($request);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get listView count
	 * @param Vtiger_Request $request
	 */
	function getListViewCount(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		if(empty($cvId)) {
			$cvId = '0';
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$listViewModel->set('search_key', $searchKey);
		$listViewModel->set('search_value', $searchValue);
		$listViewModel->set('operator', $request->get('operator'));

		$count = $listViewModel->getListViewCount();

		return $count;
	}



	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	function getPageCount(Vtiger_Request $request){
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		
		$jsFileNames = array(
			'modules.VDUsers.resources.List'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	private function getViewId()
	{
		global $adb, $current_user;
		$result = $adb->pquery("select cvid from vtiger_customview where viewname=?", array('VDUsers'));
		if ($adb->num_rows($result) > 0) {
			$viewid = $adb->query_result($result, 0, 'cvid');
			$_REQUEST['viewname'] = $viewid;
			return $viewid;
		}
		else {
			return null;
		}
	}
	public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false); // TODO: Change the autogenerated stub
        $viewer = $this->getViewer($request);
        $user_model = Users_Record_Model::getCurrentUserModel();
        $user_model->set('leftpanelhide', 1);
        $viewer->assign('CURRENT_USER_MODEL', $user_model);
        $this->preProcessDisplay($request);
    }
}
