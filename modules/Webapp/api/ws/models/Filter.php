<?php

include_once 'modules/CustomView/CustomView.php';

class Webapp_WS_FilterModel {
	
	var $filterid, $moduleName;
	var $user;
	protected $customView;
	
	function __construct($moduleName) {
		$this->moduleName = $moduleName;
		$this->customView = new CustomView($moduleName);
	}
	
	function setUser($userInstance) {
		$this->user = $userInstance;
	}
	
	function getUser() {
		return $this->user;
	}
	
	function query() {
		// $listquery = getListQuery($this->moduleName);
		// $query = $this->customView->getModifiedCvListQuery($this->filterid,$listquery,$this->moduleName);
		
		$listViewModel = Vtiger_ListView_Model::getInstance($this->moduleName, $this->filterid);
		$query = $listViewModel->getQuery();
		return $query;
	}
	
	function queryParameters() {
		return false;
	}
	
	static function modelWithId($moduleName, $filterid) {
		$model = new Webapp_WS_FilterModel($moduleName);
		$model->filterid = $filterid;
		return $model;
	}
	
}
