<?php

include_once dirname(__FILE__) . '/../Alert.php';

/** Events for today alert */
class Webapp_WS_AlertModel_EventsOfMineToday extends Webapp_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Your events for the day';
		$this->moduleName = 'Calendar';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Alert sent when events are scheduled for the day';
	}
	
	function query() {
		$today = date('Y-m-d');
		$sql = "SELECT crmid, activitytype FROM vtiger_activity INNER JOIN 
				vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_crmentity.smownerid=? AND 
				vtiger_activity.activitytype <> 'Emails' AND 
				(vtiger_activity.date_start = '{$today}' OR vtiger_activity.due_date = '{$today}')";
		return $sql;
	}
	
	function queryParameters() {
		return array($this->getUser()->id);
	}
}
