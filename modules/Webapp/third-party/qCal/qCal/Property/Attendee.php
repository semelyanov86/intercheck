<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Property_Attendee extends qCal_Property {

	protected $type = 'CAL-ADDRESS';
	// If I'm reading the RFC correctly above, this property can be specified
	// on the following components, but I'm still a bit confused about it. I 
	// need to read up on it more to really understand
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VALARM');
	protected $allowMultiple = true;

}
