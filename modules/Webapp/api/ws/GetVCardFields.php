<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Webapp_WS_GetVCardFields extends Webapp_WS_FetchRecordWithGrouping {
	public $totalQuery = "";
	public $totalParams = array();
    protected $recordValues = false;

	function process(Webapp_API_Request $request) {
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		$module = trim($request->get('module'));
        $recordid = trim($request->get('record'));
		if($module == ''){
			$message = vtranslate($module,$module)." ".vtranslate('Module is required','Webapp');
			throw new WebServiceException(404,$message);
		}
        if($recordid == ''){
            $message = vtranslate('Recordid is required','Webapp');
            throw new WebServiceException(404,$message);
        }
		$vcardModules = array('Contacts','Leads','Vendors');
		if(!in_array($module, $vcardModules)){
			$message = vtranslate('Invalid Module','Webapp');
			throw new WebServiceException(404,$message);
		}
        if(!empty($recordid)){
            $this->recordValues = vtws_retrieve($recordid, $current_user);
        }
        $refrenceUitypes = array(10,51,57,58,59,66,73,75,76,78,80,81,101);
		$vcard_field=$adb->pquery("SELECT * FROM `webapp_vcard_fields` WHERE module=?",array($module));
        $selectedFields =  array();
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $fieldModels = $moduleModel->getFields();
        $vcard_fields = array();
        if($adb->num_rows($vcard_field) > 0) {
            while($row=$adb->fetch_array($vcard_field)) {
            	if($row['fieldname'] != ''){
                    $fieldname = explode(':', $row['fieldname']);
                    $selectedFields[]=$fieldname[2];
                    $fieldname = $fieldname[2];
                    $fieldModel = $fieldModels[$fieldname];
                    if($fieldModel){
                        if(in_array($fieldModel->get('uitype'),$refrenceUitypes)){
                            $name = $fieldModel->get('name');
                            $label = vtranslate($fieldModel->get('label'),$module);
                            $relationid = $this->recordValues[$name];
                            if($relationid != ''){
                                $recordid = explode('x',$relationid);
                                $selQuery = "SELECT * FROM vtiger_crmentity where crmid = ? AND deleted = 0";
                                $selParams = array($recordid[1]);
                                $selResult = $adb->pquery($selQuery,$selParams);
                                if($adb->num_rows($selResult) > 0){
                                    $value = $adb->query_result($selResult,0,'label');
                                }else{
                                   $value = ""; 
                                }
                            }
                        }else{
                            $name = $fieldModel->get('name');
                            $label = vtranslate($fieldModel->get('label'),$module);
                            $value = $this->recordValues[$name];
                        }
                        $vcard_fields[] = array('name'=>$name,'label'=>$label,'value'=>$value);
                    }
            	}
            }
        }
        $modulelabel =  vtranslate($module,$module);
        $data = array('modulename'=>$module,'modulelabel'=>$modulelabel,'vcard_fields'=>$vcard_fields);
		$response = new Webapp_API_Response();
		$response->setResult($data);
		return $response;
	}
}