<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_DateTime_Period {

	/**
	 * Start and end date/times
	 */
	protected $start, $end;
	/**
	 * Constructor
	 */
	public function __construct($start, $end) {
	
		if (!($start instanceof qCal_DateTime)) {
			$start = qCal_DateTime::factory($start);
		}
		if (!($end instanceof qCal_DateTime)) {
			$end = qCal_DateTime::factory($end);
		}
		$this->start = $start;
		$this->end = $end;
		if ($this->getSeconds() < 0) {
			throw new qCal_DateTime_Exception_InvalidPeriod("The start date must come before the end date.");
		}
	
	}
	/**
	 * Converts to how many seconds between the two. because this is the smallest increment
	 * used in this class, seconds are used to determine other increments
	 */
	public function getSeconds() {
	
		return $this->end->getUnixTimestamp() - $this->start->getUnixTimestamp();
	
	}
	/**
	 * Returns start date
	 */
	public function getStart() {
	
		return $this->start;
	
	}
	/**
	 * Returns end date
	 */
	public function getEnd() {
	
		return $this->end;
	
	}

}
