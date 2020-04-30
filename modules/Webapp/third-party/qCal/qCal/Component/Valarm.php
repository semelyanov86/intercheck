<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class qCal_Component_Valarm extends qCal_Component {

	protected $name = "VALARM";
	protected $allowedComponents = array('VEVENT','VTODO');
	protected $requiredProperties = array('ACTION', 'TRIGGER');
	protected function doValidation() {
	
		$action = $this->getAction();
		switch(strtoupper($action->getValue())) {
			case "AUDIO":
				// action, trigger (already covered by parent constructor)
				// attach can only occur once
				$attach = $this->getProperty('ATTACH');
				if (count($attach) > 1) {
					throw new qCal_Exception_InvalidProperty('VALARM audio component can contain one and only one ATTACH property');
				}
				break;
			case "DISPLAY":
				// action, trigger, description 
				if (!$this->hasProperty('DESCRIPTION')) {
					throw new qCal_Exception_MissingProperty("DISPLAY VALARM component requires DESCRIPTION property");
				}
				break;
			case "EMAIL":
				// action, description, trigger, summary
				if (!$this->hasProperty('DESCRIPTION')) {
					throw new qCal_Exception_MissingProperty("EMAIL VALARM component requires DESCRIPTION property");
				}
				if (!$this->hasProperty('SUMMARY')) {
					throw new qCal_Exception_MissingProperty("EMAIL VALARM component requires SUMMARY property");
				}
				break;
			case "PROCEDURE":
				// action, attach, trigger
				$attach = $this->getProperty('ATTACH');
				if (count($attach) > 1) {
					throw new qCal_Exception_InvalidProperty('VALARM procedure component can contain one and only one ATTACH property');
				}
				if (count($attach) < 1) {
					throw new qCal_Exception_MissingProperty("PROCEDURE VALARM component requires ATTACH property");
				}
				break;
		}
		if ($this->hasProperty('DURATION')) {
			if (!$this->hasProperty('REPEAT')) {
				throw new qCal_Exception_MissingProperty("VALARM component with a DURATION property requires a REPEAT property");
			}
		}
		if ($this->hasProperty('REPEAT')) {
			if (!$this->hasProperty('DURATION')) {
				throw new qCal_Exception_MissingProperty("VALARM component with a REPEAT property requires a DURATION property");
			}
		}
	
	}

}
