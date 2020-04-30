<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Value_Time extends qCal_Value {

	/**
	 * qCal_Date object
	 */
	protected $value;
	/**
	 * Convert the internal date storage to a string
	 */
	protected function toString($value) {
	
		return $value->format('His');
	
	}
	/**
	 * This converts to a qCal_Date for internal storage
	 */
	protected function doCast($value) {
	
		$date = qCal_Time::factory($value);
		return $date;
	
	}

}
