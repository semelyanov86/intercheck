<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class qCal_Component_Daylight extends qCal_Component {

	protected $name = "DAYLIGHT";
	protected $allowedComponents = array('VTIMEZONE');
	protected $requiredProperties = array('DTSTART','TZOFFSETFROM','TZOFFSETTO');

}
