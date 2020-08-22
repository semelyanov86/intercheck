<?php

vimport('~~/modules/ITS4YouReports/ITS4YouReports.php');
vimport('~~/modules/ITS4YouReports/GenerateObj.php');
/*
error_reporting(63);
ini_set('display_errors', 1);
*/
class Vtiger_ITS4YouReport_Model extends ITS4YouReports {

	static function getInstance($reportId = "") {
		$self = new self();
		return $self->ITS4YouReports($reportId);
	}

	function ITS4YouReports($reportId = "",$run_construct=true) {
		$this->db = PearDatabase::getInstance();
		GenerateObj::checkInstallationMemmoryLimit();
		$this->setLicenseInfo();
        if($run_construct===true){
            $this->setITS4YouReport($reportId);
		}
        return $this;
	}

	function isEditable() {
		return $this->is_editable;
	}
    
    function getModulesList() {
        foreach($this->module_list as $key=>$value) {
            if(isPermitted($key,'index') == "yes") {
                $modules [$key] = vtranslate($key, $key);
            }
        }
        asort($modules);
        return $modules;
    }
	
    public function define_rt_vars($r_defug=false){
		if($r_defug){
			//define("RT_START",time());
			list($usec, $sec) = explode(' ', microtime()); 
			define("RT_START", $sec + $usec);
		}
        define("R_DEBUG",$r_defug);
	}

	public function getR4UDifTime($t_txt=""){
		if(R_DEBUG){
			if($t_txt!=""){
				$t_txt .= " ";
			}
			//$c_time = time();
			list($usec, $sec) = explode(' ', microtime()); 
			$c_time = $sec + $usec;
			echo "<pre>".$t_txt."TIME: ".($c_time - RT_START)."</pre>";
		}
		return true;
	}
	
}
