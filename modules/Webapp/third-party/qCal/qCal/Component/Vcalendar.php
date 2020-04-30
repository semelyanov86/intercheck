<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class qCal_Component_Vcalendar extends qCal_Component {

	protected $name = "VCALENDAR";
	protected $requiredProperties = array('PRODID','VERSION');
	/**
	 * vcalendar objects have a number of requirements defined in the RFC just as most other
	 * components do. Each has a global set of validation rules as well as their own set. This
	 * is the set of rules defined by the vcalendar object. 
	 */
	public function doValidation() {
	
		// @todo make sure that all tzids that are specified have a corresponding vtimezone
		// look for tzids and make sure there are corresponding vtimezone components for each tzid
		// In order to be sure I find all tzids, I need to search through the entire tree, so either
		// I need a recursive getProperties() or I need to use a stack to find all of them.
		
	
	}

}
