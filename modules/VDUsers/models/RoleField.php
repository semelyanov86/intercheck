<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class VDUsers_RoleField_Model extends Vtiger_Field_Model
{
	public static function getInstance($picklist)
	{
		$fieldModel = new self();
		$fieldModel->set('uitype', 33); // multipicklist
		$fieldModel->set('picklist', $picklist);
		return $fieldModel;
	}
	
	public function getPicklistValues()
	{
		return $this->get('picklist');
	}
}
