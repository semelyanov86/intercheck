<?php

include_once dirname(__FILE__) . '/../api/ws/FetchRecordWithGrouping.php';

class Webapp_UI_FetchRecordWithGrouping extends Webapp_WS_FetchRecordWithGrouping {
	
	function cachedModuleLookupWithRecordId($recordId) {
		$recordIdComponents = explode('x', $recordId);
		$modules = $this->sessionGet('_MODULES'); // Should be available post login
		foreach($modules as $module) {
			if ($module->id() == $recordIdComponents[0]) { return $module; };
		}
		return false;
	}
	
	function process(Webapp_API_Request $request) {
		$wsResponse = parent::process($request);
		
		$response = false;
		if($wsResponse->hasError()) {
			$response = $wsResponse;
		} else {
			$wsResponseResult = $wsResponse->getResult();

			$module = $this->cachedModuleLookupWithRecordId($wsResponseResult['record']['id']);
			$record = Webapp_UI_ModuleRecordModel::buildModelFromResponse($wsResponseResult['record']);
			$record->setId($wsResponseResult['record']['id']);
			
			$viewer = new Webapp_UI_Viewer();
			$viewer->assign('_MODULE', $module);
			$viewer->assign('_RECORD', $record);

			$response = $viewer->process('generic/Detail.tpl');
		}
		return $response;
	}

}
