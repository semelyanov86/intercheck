<?php

include_once dirname(__FILE__) . '/../api/ws/LoginAndFetchModules.php';

class Webapp_UI_LoginAndFetchModules extends Webapp_WS_LoginAndFetchModules {
	
	protected function cacheModules($modules) {
		$this->sessionSet("_MODULES", $modules);
	}
	
	function process(Webapp_API_Request $request) {
		$wsResponse = parent::process($request);
		
		$response = false;
		if($wsResponse->hasError()) {
			$response = $wsResponse;
		} else {
			$wsResponseResult = $wsResponse->getResult();
			
			$modules = Webapp_UI_ModuleModel::buildModelsFromResponse($wsResponseResult['modules']);
			$this->cacheModules($modules);

			$viewer = new Webapp_UI_Viewer();
			$viewer->assign('_MODULES', $modules);

			$response = $viewer->process('generic/Home.tpl');
		}
		return $response;
	}

}
