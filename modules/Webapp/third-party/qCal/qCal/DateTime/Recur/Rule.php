<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
abstract class qCal_DateTime_Recur_Rule {

	/**
	 * @var array The sub-rules of this rule. 
	 */
	protected $rules = array();
	/**
	 * @var mixed The value of this rule
	 */
	protected $value;
	/**
	 * Constructor
	 * @param The value of the rule. If this is a ByMonth rule, then 1 would mean January
	 */
	public function __construct($value) {
	
		$this->value = $value;
	
	}
	/**
	 * Attach rules to this rule. For instance, if this is a byMonth rule, then
	 * we can attach byDay rules like "-1SU" for the last Sunday of the month.
	 */
	public function attach(qCal_DateTime_Recur_Rule $rule) {
	
		$this->rules[] = $rule;
	
	}
	/**
	 * Creates the recurrences for this rule. Left to children to do this.
	 */
	abstract public function getRecurrences();

}
