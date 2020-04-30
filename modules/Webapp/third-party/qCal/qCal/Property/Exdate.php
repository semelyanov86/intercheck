<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Property_Exdate extends qCal_Property_MultiValue {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VTIMEZONE');

}
