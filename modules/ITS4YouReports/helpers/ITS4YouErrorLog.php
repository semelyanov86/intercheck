<?php

class ITS4YouReports_ITS4YouError_Log {
    
    protected $tableName = "its4you_error_log";
    protected $tableIndex = "id";
    protected $adb = false;
    
    public static $version = "650.1.0";

	protected $valueMap;

	/**
	 * Constructor
	 * @param Array $values
	 */
	function  __construct($values=array()) {
		$this->valueMap = $values;
        $this->adb = PearDatabase::getInstance();
	}

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function get($key){
		return isset($this->valueMap[$key]) ? $this->valueMap[$key] : false;
	}
    
	/**
	 * Function to set the value for a given key
	 * @param $key
	 * @param $value
	 * @return Vtiger_Base_Model
	 */
	public function set($key,$value){
		$this->valueMap[$key] = $value;
		return $this;
	}

    static public function getVersion()
    {
        return self::$version;
    }
    
    static public function createLog($error_message="")
    {
        $log_saved = false;
        if($error_message!=""){
            $errorLogObj = new ITS4YouReports_ITS4YouError_Log();

            $currentModule = vglobal("currentModule");
            
            $currentUser = Users_Record_Model::getCurrentUserModel();
            
            $userid = $currentUser->getId();
            
            $tableName = $errorLogObj->tableName;
            $date_var = date("Y-m-d H:i:s");
            $created_date_var = $errorLogObj->adb->formatDate($date_var, true);
            
            //$errorLogObj->adb->setDebug(true);
            $log_saved = $errorLogObj->adb->pquery("INSERT INTO $tableName (modulename,userid,error_log_time,log_text) VALUES(?,?,?,?)",array($currentModule,$userid,$created_date_var,$error_message));
            //$errorLogObj->adb->setDebug(false);
        }
        return $log_saved;
    }
    
    public function sshow($variable = array()) {
        echo "<pre>";
        print_r($variable);
        echo "</pre>";
    }
    
}