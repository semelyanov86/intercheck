<?php

include_once dirname(__FILE__) . '/../Alert.php';
class Webapp_WS_AlertModel_Projects extends Webapp_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'My Projects';
		$this->moduleName = 'Project';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Projects Related To Me';
	}

	function query() {
		$sql = "SELECT crmid FROM vtiger_crmentity INNER JOIN vtiger_project ON
                    vtiger_project.projectid=vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted=0 AND vtiger_crmentity.smownerid=? AND
                    vtiger_project.projectstatus <> 'completed'";
		return $sql;
	}
        function queryParameters() {
		return array($this->getUser()->id);
	}

}

