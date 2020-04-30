<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class WebappHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {

		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if($eventName == 'vtiger.entity.aftersave') {
			// Entity has been saved, take next action
		}
	}
}

?>
