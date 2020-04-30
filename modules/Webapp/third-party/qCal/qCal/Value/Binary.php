<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Value_Binary extends qCal_Value {

	/**
	 * When the value of a binary property is requested, it will be returned as a base64 encoded string
	 * @todo Base64 is the only encoding supported by this standard, but the encoding=base64 parameter must be
	 * provided regardless.
	 */
	protected function toString($value) {
	
		return base64_encode($value);
	
	}
	/**
	 * Binary can be store as-is I believe, so don't change it
	 */
	protected function doCast($value) {
	
		return $value;
	
	}

}
