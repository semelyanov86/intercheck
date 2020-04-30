<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Time_Timezone {

	protected $format = "P";
	
	public function __construct($timezone = null) {
	
		if (!is_null($timezone)) {
			date_default_timezone_set($timezone);
		}
	
	}
	
	public function getOffsetSeconds() {
	
		return date("Z");
	
	}
	
	public function getOffsetHours() {
	
		return date("O");
	
	}
	
	public function getOffset() {
	
		return date("P");
	
	}
	
	public function getAbbreviation() {
	
		return date("T");
	
	}
	
	public function isDaylightSavings() {
	
		return (boolean) date("I");
	
	}
	
	public function getName() {
	
		return date("e");
	
	}
	
	public function format($format) {
	
		return date($format);
	
	}
	
	public function __toString() {
	
		return $this->format($this->format);
	
	}

}
