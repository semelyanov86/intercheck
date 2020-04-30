<?php

include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Webapp_WS_GetMessageTemplate extends Webapp_WS_Controller {
	
	function getSearchFilterModel($module, $search) {
		return Webapp_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Webapp_API_Request $request) {
		$page = $request->get('page', 0);
		return Webapp_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Webapp_API_Request $request) {
		global $adb, $current_user;
		
		$message_type = trim($request->get('message_type'));
		$getTemplateQuery = $adb->pquery("SELECT * FROM vtiger_webmessagetemplate INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_webmessagetemplate.webmessagetemplateid where vtiger_webmessagetemplate.message_status = ? AND vtiger_webmessagetemplate.message_type = ? AND vtiger_crmentity.deleted = 0", array('Active', $message_type));
		$countTemplate = $adb->num_rows($getTemplateQuery);
		
		for($i=0;$i<$countTemplate;$i++){
			$msgTemplateId = trim($adb->query_result($getTemplateQuery, $i, 'webmessagetemplateid'));
			$templates_name = trim($adb->query_result($getTemplateQuery, $i, 'templates_name'));
			$description = trim($adb->query_result($getTemplateQuery, $i, 'description'));
			$message_status = trim($adb->query_result($getTemplateQuery, $i, 'message_status'));
			$message_type = trim($adb->query_result($getTemplateQuery, $i, 'message_type'));
			
			$messageTemplateData[] = array('msgTemplateId' => $msgTemplateId, 'templates_name' => $templates_name, 'description' => $description, 'message_status' => $message_status, 'message_type' => $message_type); 
		}
		
		$response = new Webapp_API_Response();
		$response->setResult(array('records'=>$messageTemplateData,'code'=>'','message'=>''));
		
		if ($countTemplate == 0) {
			$response->setResult(array('records'=>array(),'code'=>404,'message'=>vtranslate('No Templates found - create it from Message Templates module','Webapp')));
		}
		
		return $response;
	}
}

?>
