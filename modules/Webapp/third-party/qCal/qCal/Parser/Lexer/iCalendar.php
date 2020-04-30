<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */
class qCal_Parser_Lexer_iCalendar extends qCal_Parser_Lexer {
	
    /**
     * @var string character(s) used to terminate lines
     */
    protected $line_terminator;
	/**
	 * Constructor 
	 */
	public function __construct($content) {
	
		parent::__construct($content);
		$this->line_terminator = chr(13) . chr(10);
	
	}
    /**
     * Return a list of tokens (to be fed to the parser)
     * @returns array tokens
     */
    public function tokenize() {
    
		$lines = $this->unfold($this->content);
        // loop through chunks of input text by separating by properties and components
        // and create tokens for each one, creating a multi-dimensional array of tokens to return
        $stack = array();
        foreach ($lines as $line) {
        	// begin a component
        	if (preg_match('#^BEGIN:([a-z]+)$#i', $line, $matches)) {
        		// create new array representing the new component
        		$array = array(
        			'component' => $matches[1],
        			'properties' => array(),
        			'children' => array(),
        		);
        		$stack[] = $array;
        	} elseif (strpos($line, "END:") === 0) {
        		// end component, pop the stack
        		$child = array_pop($stack);
				if (empty($stack)) {
					$tokens = $child;
				} else {
					$parent =& $stack[count($stack)-1];
					array_push($parent['children'], $child);
				}
        	} else {
        		// continue component
        		if (preg_match('#^([^:]+):"?([^\n]+)?"?$#i', $line, $matches)) {
					// @todo What do I do with empty values?
					$value = isset($matches[2]) ? $matches[2] : "";
					$component =& $stack[count($stack)-1];
        			// if line is a property line, start a new property, but first determine if there are any params
					$property = $matches[1];
					$params = array();
					$propparts = explode(";", $matches[1]);
					if (count($propparts) > 1) {
						foreach ($propparts as $key => $part) {
							// the first one is the property name
							if ($key == 0) {
								$property = $part;
							} else {
								// the rest are params
								// @todo Quoted param values need to be taken care of...
								list($paramname, $paramvalue) = explode("=", $part, 2);
								$params[] = array(
									'param' => $paramname,
									'value' => $paramvalue,
								);
							}
						}
					}
					$proparray = array(
						'property' => $property,
						'value' => $value,
						'params' => $params,
					);
        			$component['properties'][] = $proparray;
        		}
        	}
        }
        return $tokens;
    
    }
	/**
	 * Unfold the file before trying to parse it
	 */
	protected function unfold($content) {
	
		$return = array();
		$lines = explode($this->line_terminator, $content);
		foreach ($lines as $line) {
			$checkempty = trim($line);
			if (empty($checkempty)) continue;
			$chr1 = substr($line, 0, 1);
			$therest = substr($line, 1);
			// if character 1 is a whitespace character... (tab or space)
			if ($chr1 == chr(9) || $chr1 == chr(32)) {
				$return[count($return)-1] .= $therest;
			} else {
				$return[] = $line;
			}
		}
		return $return;
	
	}

}
