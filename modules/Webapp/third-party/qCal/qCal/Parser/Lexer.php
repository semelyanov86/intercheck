<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
abstract class qCal_Parser_Lexer {

    /**
     * @var string input text
     */
    protected $content;
    /**
     * Constructor
     * @param string containing the text to be tokenized
     */
    public function __construct($content) {
    
        $this->content = $content;
    
    }
	/**
	 * Tokenize content into tokens that can be used to build iCalendar objects
	 */
	abstract public function tokenize();

}
