<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Phone_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Phone.tpl';
	}

    private function getPermissionValue($value, $user)
    {
        global $restrictedFieldRolesPhones;
        $restrictedArr = explode('||', $restrictedFieldRolesPhones);
        if (in_array($user->getRole(), $restrictedArr)) {
            $value = strip_tags($value);
//            return substr($value, 0, 2) . '****' . substr($value, -1, 2);
            return '****';
        } else {
            return $value;
        }
    }

    public function getDisplayValue($value, $record=false, $recordInstance=false) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        return $this->getPermissionValue($value, $currentUser);
    }

    /**
	 * Function to get the Detailview template name for the current UI Type Object 
	 * @return <String> - Template Name
	 */
	public function getDetailViewTemplateName() {
		return 'uitypes/PhoneDetailView.tpl';
	}
}