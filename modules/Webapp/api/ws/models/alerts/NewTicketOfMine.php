<?php

include_once dirname(__FILE__) . '/PendingTicketsOfMine.php';

/** New Ticket */
class Webapp_WS_AlertModel_NewTicketOfMine extends Webapp_WS_AlertModel_PendingTicketsOfMine {
	function __construct() {
		parent::__construct();
		$this->name = 'New Ticket Alert';
		$this->moduleName = 'HelpDesk';
		$this->refreshRate= 1 * (60 * 60); // 1 hour
		$this->description='Alert sent when a ticket is assigned to you';
	}
	
	function query() {
		$sql = parent::query();
		$sql .= " ORDER BY crmid DESC";
		return $sql;
	}
	
	function countQuery() {
		return str_replace("ORDER BY crmid DESC", "", $this->query());
	}
	
	function executeCount() {
		global $adb;
		$result = $adb->pquery($this->countQuery(), $this->queryParameters());
		return $adb->num_rows($result);
	}
}
