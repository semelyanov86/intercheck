<?php
 /*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class qCal_Parser {

    /**
     * @param array containing any options the particular parser accepts
     */
    protected $options;
    /**
     * Constructor
     * Pass in an array of options
     * @todo Come up with list of available options
     * @param array parser options
     */
    public function __construct($options = array()) {
    
		// set defaults...
		$this->options = array(
			'searchpath' => get_include_path(),
		);
        $this->options = array_merge($this->options, $options);
    
    }
    /**
     * @todo What should this accept? filename? actual string content? either?
     * @todo Maybe even create a parse() for raw string and a parseFile() for a file name?
     */
    public function parse($content, $lexer = null) {
    
		if (is_null($lexer)) {
			$lexer = new qCal_Parser_Lexer_iCalendar($content);
		}
        $this->lexer = $lexer;
        return $this->doParse($this->lexer->tokenize());
    
    }
	/**
	 * Parse a file. The searchpath defaults to the include path. Also, if the filename
	 * provided is an absolute path, the searchpath is not used. This is determined by 
	 * either the file starting with a forward slash, or a drive letter (for Windows)
	 * @todo Throw an exception if file doesn't exist
	 * @todo I'm not really sure that it should default to the include path. That's not really what the include path is for, is it?
	 * @todo Test for path starting with a drive letter for windows (or find a better way to detect that)
	 */
	public function parseFile($filename) {
	
		// @todo This is hacky... but it works
		if (substr($filename, 0, 1) == '/' || substr($filename, 0, 3) == 'C:\\') {
			if (file_exists($filename)) {
				$content = file_get_contents($filename);
				return $this->parse($content);
			}
		} else {
			$paths = explode(PATH_SEPARATOR, $this->options['searchpath']);
			foreach ($paths as $path) {
				$fname = $path . DIRECTORY_SEPARATOR . $filename;
				if (file_exists($fname)) {
					$content = file_get_contents($fname);
					return $this->parse($content);
				}
			}
		}
		throw new qCal_Exception_FileNotFound('File cannot be found: "' . $filename . '"');
	
	}
    /**
     * Override doParse in a child class if necessary
     */
	protected function doParse($tokens) {
	
		$properties = array();
		foreach ($tokens['properties'] as $propertytoken) {
			$params = array();
			foreach ($propertytoken['params'] as $paramtoken) {
				$params[$paramtoken['param']] = $paramtoken['value'];
			}
			try {
				$properties[] = qCal_Property::factory($propertytoken['property'], $propertytoken['value'], $params);
			} catch (qCal_Exception $e) {
				// @todo There should be a better way of determining what went wrong during parsing/lexing than this
				// do nothing...
				// pr($e);
			}
		}
		$component = qCal_Component::factory($tokens['component'], $properties);
		foreach ($tokens['children'] as $child) {
			$childcmpnt = $this->doParse($child);
			$component->attach($childcmpnt);
		}
		return $component;
	
	}

}
