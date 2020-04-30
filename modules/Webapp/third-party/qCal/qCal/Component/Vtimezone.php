<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Component_Vtimezone extends qCal_Component {

	protected $name = "VTIMEZONE";
	protected $allowedComponents = array('VCALENDAR');
	protected $requiredProperties = array('TZID');
	/**
	 * Make sure that all of the rules specified above are followed
	 */
	protected function doValidation() {
	
		$children = $this->getChildren();
		if (!array_key_exists('DAYLIGHT', $children) && !array_key_exists('STANDARD', $children)) {
			throw new qCal_Exception_MissingComponent('Either a STANDARD or DAYLIGHT component is required within a VTIMEZONE component');
		}
	
	}

}
