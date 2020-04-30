<?php

include_once dirname(__FILE__) . '/../Alert.php';

/** Upcoming Opportunity */
class Webapp_WS_AlertModel_PotentialsDueIn5Days extends Webapp_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Upcoming Opportunity';
		$this->moduleName = 'Potentials';
		$this->refreshRate= 1 * (24 * 60 * 60); // 1 day
		$this->description='Alert sent when Potential Close Date is due before 5 days or less';
	}
	
	function query() {
		$sql = Webapp_WS_Utils::getModuleListQuery('Potentials', 
					"vtiger_potential.sales_stage not like 'Closed%' AND 
					DATEDIFF(vtiger_potential.closingdate, CURDATE()) <= 5"
				);
		return preg_replace("/^SELECT count\(\*\) as count(.*)/i", "SELECT crmid $1", Vtiger_Functions::mkCountQuery($sql));
	}
	
	function queryParameters() {
		return array();
	}
}
