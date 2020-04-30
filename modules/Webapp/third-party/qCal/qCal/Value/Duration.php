<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Value_Duration extends qCal_Value {

	/**
	 * Convert seconds to duration 
	 * @todo Some type of caching? This probably doesn't need to be "calculated" every time if it hasnt changed
	 */
	protected function toString($value) {
	
		return $value->toICal();
	
	}
	/**
	 * Convert to internal representation
	 */
	protected function doCast($value) {
	
		return new qCal_DateTime_Duration($value);
	
	}

}
