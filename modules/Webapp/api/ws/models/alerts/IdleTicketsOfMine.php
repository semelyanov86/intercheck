<?php

include_once dirname(__FILE__) . '/PendingTicketsOfMine.php';

/** Idle Ticket Alert */
class Webapp_WS_AlertModel_IdleTicketsOfMine extends Webapp_WS_AlertModel_PendingTicketsOfMine {
	function __construct() {
		parent::__construct();
		$this->name = 'Idle Ticket Alert';
		$this->moduleName = 'HelpDesk';
		$this->refreshRate= 1 * (60 * 60); // 1 hour
		$this->description='Alert sent when ticket has not been updated in 24 hours';
	}
	
	function query() {
		$sql = parent::query();
		$sql .= " AND DATEDIFF(CURDATE(), vtiger_crmentity.modifiedtime) > 1";
		return $sql;
	}
}
