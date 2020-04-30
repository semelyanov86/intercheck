<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Component_Vevent extends qCal_Component {

	protected $name = "VEVENT";
	protected $allowedComponents = array('VCALENDAR');
	protected function doValidation() {
	
		$properties = $this->getProperties();
		$propnames = array_keys($properties);
		if (in_array('DTEND', $propnames) && in_array('DURATION', $propnames)) {
			throw new qCal_Exception_InvalidProperty('DTEND and DURATION cannot both occur in the same VEVENT component');
		}
		if (in_array('DTSTART', $propnames)) {
			$dtstart = $this->getProperty('dtstart');
			$dtstart = $dtstart[0];
			// check that if dtstart is a DATE that dtend is a DATE
			if ($dtstart->getType() == 'DATE') {
				if (in_array('DTEND', $propnames)) {
					$dtend = $this->getProperty('dtend');
					$dtend = $dtend[0];
					if ($dtend->getType() != 'DATE') {
						throw new qCal_Exception_InvalidProperty('If DTSTART property is specified as a DATE property, so must DTEND');
					}
				}
			}
			// check that dtstart comes before dtend
			if (in_array('DTEND', $propnames)) {
				$dtend = $this->getProperty('dtend');
				$dtend = $dtend[0];
				$startdate = strtotime($dtstart->getValue());
				$enddate = strtotime($dtend->getValue());
				if ($startdate > $enddate) {
					throw new qCal_Exception_InvalidProperty('DTSTART property must come before DTEND');
				}
			}
		}
	
	}

}
