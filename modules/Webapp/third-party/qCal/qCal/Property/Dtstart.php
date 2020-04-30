<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Property_Dtstart extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VFREEBUSY','VTIMEZONE','VJOURNAL','STANDARD','DAYLIGHT');
	/**
	 * Strange that in the notes for this, it says:
	 *     Conformance: This property can be specified in the "VEVENT", "VTODO",
	 *     "VFREEBUSY", or "VTIMEZONE" calendar components.
	 * But in the notes for journal it says that dtstart is allowed in a journal
	 */

}
