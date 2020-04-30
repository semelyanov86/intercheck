<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Value_Period extends qCal_Value {

	protected $value;
	/**
	 * Cast a string value into a qCal_DateTime_Period object
	 */
	protected function doCast($value) {
	
		$parts = explode("/", $value);
		if (count($parts) !== 2) {
			throw new qCal_DateTime_Exception_InvalidPeriod("A period must contain a start date and either an end date, or a duration of time.");
		}
		$start = qCal_DateTime::factory($parts[0]);
		try {
			$end = qCal_DateTime::factory($parts[1]);
		} catch (qCal_DateTime_Exception $e) { // @todo This should probably be a more specific exception
			// invalid date, so try duration
			// @todo: I might want to create a qCal_Date object to represent a duration (not tied to any points in time)
			// using a qCal_Value object here is sort of inconsistent. Plus, I can see value in having that functionality
			// within the qCal_Date subcomponent
			// also, there is a difference in a period and a duration in that if you say start on feb 26 and end on march 2
			// that will be a different "duration" depending on the year. that goes for months with alternate amounts of days too
			$duration = new qCal_DateTime_Duration($parts[1]);
			$end = qCal_DateTime::factory($start->getUnixTimestamp() + $duration->getSeconds()); // @todo This needs to be updated once qCal_DateTime accepts timestamps 
		}
		return new qCal_DateTime_Period($start, $end);
	
	}
	/**
	 * Convert to string - this converts to string into the UTC/UTC format
	 */
	protected function toString($value) {
	
		return $value->getStart()->getUtc() . "/"
				 . $value->getEnd()->getUtc();
	
	}

}
