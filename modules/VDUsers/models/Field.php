<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'vtlib/Vtiger/Field.php';

/**
 * Vtiger Field Model Class
 */
class VDUsers_Field_Model extends Vtiger_Field_Model {


	/**
	 * Function to get instance
	 * @param <String> $value - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 * @return <Vtiger_Field_Model>
	 */
	public static function  getInstance($value) {
	    $module = Vtiger_Module_Model::getInstance('Users');

        $fieldObject = null;
        if($module){
            $fieldObject = Vtiger_Cache::get('field-'.$module->getId(), $value);
        }
        if(!$fieldObject){
            $fieldObject = parent::getInstance($value, $module);
            if($module){
                Vtiger_Cache::set('field-'.$module->getId(),$value,$fieldObject);
            }
        }

		if($fieldObject) {
			return self::getInstanceFromFieldObject($fieldObject);
		}
		return false;
	}



}