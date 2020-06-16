<?php

/**
 * ITS4YouReports ListView Header Class
 */

class ITS4YouReports_List_Header{
    
    public $headers;
    
    private $headersArray = array(
                                "reportname"=>array( 'LBL_REPORT_NAME',"text","reportname", ),
                                  "reporttype"=>array( 'REPORT_TYPE',"picklist","reporttype", ),
                                  "tablabel"=>array( 'LBL_MODULENAMES',"picklist","tablabel", ),
                                  "foldername"=>array( 'LBL_FOLDERNAMES',"picklist","foldername", ),
                                  "owner"=>array( 'LBL_TEMPLATE_OWNER',"user","owner", ),
                                  "description"=>array( 'LBL_DESCRIPTION',"text","description", ),
                                    );
    
    public function __construct($headerColumn="") {
        if($headerColumn!=""){
            list($this->columnLblString,$this->columnType,$this->columnName) = $this->headersArray[$headerColumn];
        }
    }
    
    public function getAllHeaders($search_params=array()){
        $hinfo = new ITS4YouReports_List_Header();
        $hinfo->set("search_params",ITS4YouReports_List_Header::getSearchParamsArray($search_params));
        $return = $hinfo->getAllHeaderInfo();
        
        return $return;
    }

    private function getHeaderInfoByName($name){
        $allHeadersInfo = $this->getAllHeaderInfo();
        $return = array();
        if(isset($allHeadersInfo[$name])){
            $return = $allHeadersInfo[$name];
        }
        return $return;
    }

    private function getAllHeaderInfo(){
        $allHeadersInfo = array();

        // structure ->
        // name | text
        // type | text, reporttype, module, folder, user
        // picklistValues | array of values to select

        foreach($this->headersArray as $colName => $colArray){
            $allHeadersInfo[$colName] = $this->getHeaderArray($colArray);
        }

        return $allHeadersInfo;
    }

    private function getHeaderArray($headerValues=array()){
        $headerArray = array();
        if(isset($headerValues[0])){
            global $currentModule;
            $headerArray['name'] = getTranslatedString($headerValues[0],$currentModule);
        }
        if(isset($headerValues[1])){
            $headerArray['type'] = $headerValues[1];
        }
        if(isset($headerValues[1])){
            $headerArray['picklistValues'] = $this->getHeaderPicklistValues($headerValues[1],$headerValues[2]);
        }
        $searchValue = "";
        if(isset($this->search_params[$headerValues[2]])){
            $searchValue = $this->search_params[$headerValues[2]];
        }
        $headerArray['searchValue'] = $searchValue;

        return $headerArray;
    }
    
    private function getHeaderPicklistValues($type,$columnName){
        $return = array();
        if($type=="picklist"){
            switch ($columnName){
                case "reporttype": 
                    $return = $this->getReportTypes();
                    break;
                case "foldername": 
                    $return = $this->getReportFolders();
                    break;
                case "tablabel": 
                    $return = $this->getReportModules();
                    break;
            }
        }elseif($type=="user"){
            $return = $this->getReportOwners();
        }
        return $return;
    }
    
    private function getReportTypes(){
        $selectedTypes = explode(',', $this->search_params['reporttype']);

        $reportTypes[] = array("tabular","LBL_TABULAR_REPORT",(in_array('tabular', $selectedTypes)?"SELECTED":""));
        $reportTypes[] = array("summaries","LBL_SUMMARIES_REPORT",(in_array('summaries', $selectedTypes)?"SELECTED":""));
        $reportTypes[] = array("summaries_w_details","LBL_SUMMARIES_WITH_DETAILS_REPORT",(in_array('summaries_w_details', $selectedTypes)?"SELECTED":""));
        $reportTypes[] = array("summaries_matrix","LBL_SUMMARIES_MATRIX_REPORT",(in_array('summaries_matrix', $selectedTypes)?"SELECTED":""));
        global $current_user;
        if(is_admin($current_user)){
            $reportTypes[] = array("custom_report","LBL_CUSTOM_REPORT",(in_array('custom_report', $selectedTypes)?"SELECTED":""));
        }
        return $reportTypes;
    }
    
    private function getReportFolders(){
        $adb = PearDatabase::getInstance();
        
        $reportFolders = array();
        
        $folders_res = $adb->pquery("SELECT folderid, foldername FROM its4you_reports4you_folder",array());
        if($folders_res){
            $selectedFolders = explode(',', $this->search_params['foldername']);
            
            $numFolders = $adb->num_rows($folders_res);
            if($numFolders>0){
                while ($folderRow = $adb->fetch_array($folders_res)) {
                    $reportFolders[] = array($folderRow["foldername"],$folderRow["foldername"],(in_array($folderRow["foldername"], $selectedFolders)?"SELECTED":""));
                }
            }
        }
        
        return $reportFolders;
    }
    
    private function getReportOwners(){
        $reportOwners = array();
        $template_owners = get_user_array(false);
        if(!empty($template_owners)){
            $selectedOwners = explode(',', $this->search_params['owner']);
            foreach($template_owners as $uid => $uname){
                $reportOwners[] = array($uid,$uname,(in_array($uid, $selectedOwners)?"SELECTED":""));
            }
        }
        return $reportOwners;
    }
    
    private function getReportModules(){
        $adb = PearDatabase::getInstance();
        
        $selectedModules = explode(',', $this->search_params['tablabel']);
        
        $modules_res = $adb->pquery("SELECT DISTINCT primarymodule, vtiger_tab.name 
                                    FROM its4you_reports4you_modules 
                                    INNER JOIN its4you_reports4you ON its4you_reports4you_modules.reportmodulesid = its4you_reports4you.reports4youid 
                                    INNER JOIN vtiger_tab ON its4you_reports4you_modules.primarymodule = vtiger_tab.tabid 
                                    WHERE its4you_reports4you.deleted = ?",array(0));
        $reportModules = array();
        $numModules = $adb->num_rows($modules_res);
        if($numModules>0){
            while ($modulesRow = $adb->fetch_array($modules_res)) {
                $moduleid = $modulesRow["primarymodule"];
                $modulename = vtranslate($modulesRow["name"],$modulesRow["name"]);
                $reportModules[$modulename] = array($moduleid,$modulename,(in_array($moduleid, $selectedModules)?"SELECTED":""));
            }
        }
        ksort($reportModules);
        return $reportModules;
    }
    
    public function set($var, $value=""){
        $this->$var = $value;
    }
    
    public function getSearchParamsArray($search_params=array()){
        $return = array();
        if(!empty($search_params)){
            foreach($search_params as $search_arr){
                foreach($search_arr as $search_column_array){
                    list($columnName,$comparator,$searchValue) = $search_column_array;
                    $return[$columnName] = $searchValue;
                }
            }
        }
        return $return;
    }
    
    public function getHeaderColumnSql($headerColumn="",$searchValue=""){
        
        if($headerColumn!="" && $searchValue!=""){
            $adb = PearDatabase::getInstance();
            $searchValue = $adb->sql_escape_string($searchValue);
            // reportname -> reports4youname
            // reporttype -> reporttype
            // tablabel -> vtiger_tab.name 
            // foldername -> foldername
            // owner -> its4you_reports4you_settings.owner
            // description -> description
            $hedearColumnsArray["reportname"] = "reports4youname";
            $hedearColumnsArray["reporttype"] = "reporttype";
            $hedearColumnsArray["tablabel"] = "its4you_reports4you_modules.primarymodule";
            $hedearColumnsArray["foldername"] = "foldername";
            $hedearColumnsArray["owner"] = "its4you_reports4you_settings.owner";
            $hedearColumnsArray["description"] = "its4you_reports4you.description";

            if(isset($hedearColumnsArray[$headerColumn])){
                $headerColumn = $hedearColumnsArray[$headerColumn];
            }
            if($this->columnType=="picklist" || $this->columnType=="user"){
                $searchArray = explode(",", $searchValue);
                $searchValue = "'".implode("','", $searchArray)."'";
                $headerColSql = " $headerColumn IN ($searchValue)";
            }else{
                $headerColSql = " $headerColumn LIKE '%$searchValue%'";
            }
        }
        return $headerColSql;
    }
    
}

?>