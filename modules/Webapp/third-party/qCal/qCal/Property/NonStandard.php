<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Property_NonStandard extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL',
		'VALARM','VTIMEZONE','VFREEBUSY','VCALENDAR');
	protected $allowMultiple = true;
	public function __construct($value, $params, $name) {
	
		parent::__construct($value, $params);
		$this->name = strtoupper($name);
	
	}

}
