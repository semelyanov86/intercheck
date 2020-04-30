<?php

include_once dirname(__FILE__) . '/../api/ws/FetchRecordWithGrouping.php';

class Webapp_UI_SearchConfig extends Webapp_WS_FetchRecordWithGrouping {
	
	function cachedModule($moduleName) {
		$modules = $this->sessionGet('_MODULES'); // Should be available post login
		foreach($modules as $module) {
			if ($module->name() == $moduleName) return $module;
		}
		return false;
	}
	
	function cacheSearchFields($module, $fieldnames) {
		$this->sessionSet("_MODULE.{$module}.SEARCHFIELDS", $fieldnames);
	}
	
	function cachedSearchFields($module) {
		$cachekey = "_MODULE.{$module}.SEARCHFIELDS";
		return $this->sessionGet($cachekey, array());
	}
	
	function process(Webapp_API_Request $request) {
		$mode = $request->get('mode');
		$module = $this->cachedModule($request->get('module'));
		
		$searchIn = $this->cachedSearchFields($module->name());
		
		if($mode == 'update') {
			$searchIn = array();
			foreach($_REQUEST as $k=>$v) {
				if(preg_match("/field_(.*)/i", $k, $m)) {
					$searchIn[] = vtlib_purify($m[1]);
				}
			}
			$this->cacheSearchFields($module->name(), $searchIn);
			header("Location: index.php?_operation=listModuleRecords&module={$module->name()}&mode=search");
			exit;
		}
		
		$request->setDefault('record', "{$module->id()}x0");
		
		$wsResponse = parent::process($request);
		$wsResponseResult = $wsResponse->getResult();
		
		$templateRecord = Webapp_UI_ModuleRecordModel::buildModelFromResponse($wsResponseResult['record']);

		$viewer = new Webapp_UI_Viewer();
		$viewer->assign('_MODULE', $module );
		$viewer->assign('_RECORD', $templateRecord );
		$viewer->assign('_SEARCHIN', $searchIn);
		$viewer->assign('_SEARCHIN_ALL', empty($searchIn));

		$response = $viewer->process('generic/SearchConfig.tpl');
		
		return $response;
	}
}
