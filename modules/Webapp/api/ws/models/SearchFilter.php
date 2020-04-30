<?php

include_once 'include/Webservices/Query.php';
include_once dirname(__FILE__) . '/Filter.php';

class Webapp_WS_SearchFilterModel extends Webapp_WS_FilterModel {
	
	protected $criterias;
	
	function __construct($moduleName) {
		$this->moduleName = $moduleName;
	}
	
	function query() {
		return false;
	}
	
	function queryParameters() {
		return false;
	}
	
	function setCriterias($criterias) {
		$this->criterias = $criterias;
	}
	
	function execute($fieldnames, $pagingModel = false, $paging = array(),$orderclause='',$field_name='',$field_value='') {
		$selectClause = sprintf("SELECT %s", implode(',', $fieldnames));
		$fromClause = sprintf("FROM %s", $this->moduleName);
		if($field_name && $field_value){
			if($this->moduleName == 'Users'){
				$moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);
				$fieldModels = $moduleModel->getFields();
				$tablename =  $fieldModels[$field_name]->get('table');
				$column =  $fieldModels[$field_name]->get('column');
				$whereClause = " WHERE $column LIKE '%$field_value%' ";
			}else{
				$whereClause = "";
			}
		}else{
			$whereClause = "";
		}
		if($orderclause){
			$orderClause = $orderclause;
		}else{
			if($this->moduleName == 'Users'){
				if (!empty($this->criterias)) {
					$_sortCriteria = $this->criterias['_sort'];
					if(!empty($_sortCriteria)) {
						$orderClause = $_sortCriteria;
					}
				}
			}else{
				$orderClause = " ORDER BY modifiedtime DESC";
			}
		}
		$groupClause = "";
		//$limitClause = $pagingModel? " LIMIT {$pagingModel->currentCount()},{$pagingModel->limit()}" : "" ;
		
		$index = $paging['index'];
		$size = $paging['size'];
		
		$limit = ($index*$size) - $size;
		$limitClause = " LIMIT $limit, $size"; 
		

		if($index == '' && $size == '') {
			$query = sprintf("%s %s %s %s %s;", $selectClause, $fromClause, $whereClause, $orderClause, $groupClause);
		} else {
			//$orderClause = " ORDER BY modifiedtime DESC"; 
			$query = sprintf("%s %s %s %s %s %s;", $selectClause, $fromClause, $whereClause, $orderClause, $groupClause, $limitClause);
		}
  
		//$query = sprintf("%s %s %s %s %s %s;", $selectClause, $fromClause, $whereClause, $orderClause, $groupClause, $limitClause);
		if( $this->moduleName == 'Users')	{
			$userid = 1;
			$this->activeUser = CRMEntity::getInstance('Users');
			$this->activeUser->retrieveCurrentUserInfoFromFile($userid);
			return vtws_query($query, $this->activeUser); 
		}else{
			return vtws_query($query, $this->getUser());
		}	
		 
	}
	
	static function modelWithCriterias($moduleName, $criterias = false) {
		
		$model = new Webapp_WS_SearchFilterModel($moduleName);
		$model->setCriterias($criterias);
		return $model;
	}
}
