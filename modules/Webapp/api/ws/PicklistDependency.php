<?php

class Webapp_WS_PicklistDependency extends Webapp_WS_Controller {

	function process(Webapp_API_Request $request) {
		global $current_user, $adb, $site_URL; // Few core API assumes this variable availability
		
		$current_user = $this->getActiveUser();
		$module = trim($request->get('module'));
		$field_name = trim($request->get('field_name'));
		$field_value = trim($request->get('field_value'));
		$dependecyData =  array();
		if($module && $field_name && $field_value){
			$data = Vtiger_DependencyPicklist::getDependentPicklistFields($module);
			$targetfield = "";
			if(count($data) > 0){
				foreach($data as $key => $values){
					if($values['sourcefield'] == $field_name){
						$PickListDependency = Vtiger_DependencyPicklist::getPickListDependency($module,$values['sourcefield'],$values['targetfield']);
						$valuemapping = $PickListDependency['valuemapping'];
						foreach($valuemapping as $keys => $depValues){
							if($depValues['sourcevalue'] == $field_value){
								$picklistValues = array();
								foreach($depValues['targetvalues'] as $k => $pvalues){
									$picklistValues[] = array('value'=>$pvalues,'label'=>vtranslate($pvalues,$module));
								}
								$dependecyData[] = array('sourcefield'=>$values['sourcefield'],'sourcevalue'=>$depValues['sourcevalue'],'targetfield'=>$values['targetfield'],'targetvalues'=>$picklistValues);
							}
						}
						if(count($dependecyData) == 0){
							$targetfield = $PickListDependency['targetfield'];
						}
					}
				}
			}
		}
		if(count($dependecyData) > 0){
			$response = new Webapp_API_Response();
			$response->setResult(array('dependecyData'=>$dependecyData,'message'=>''));
			return $response;
		}else{
			$response = new Webapp_API_Response();
			$response->setResult(array('dependecyData'=>$dependecyData,'message'=>vtranslate('No dependency found for picklist','Webapp'),'targetfield'=>$targetfield));
			return $response;
		}
	}
}