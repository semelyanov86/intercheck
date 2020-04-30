<?php

include_once 'includes/runtime/Viewer.php';

class Webapp_UI_Viewer extends Vtiger_Viewer{

	private $parameters = array();
	function assign($key, $value) {
		$this->parameters[$key] = $value;
	}

	function viewController() {
		$smarty = new Vtiger_Viewer();

		foreach($this->parameters as $k => $v) {
			$smarty->assign($k, $v);
		}

		$smarty->assign("IS_SAFARI", Webapp::isSafari());
		$smarty->assign("SKIN", Webapp::config('Default.Skin'));
		return $smarty;
	}

	function process($templateName) {
		$smarty = $this->viewController();
		$response = new Webapp_API_Response();
		$response->setResult($smarty->fetch(vtlib_getModuleTemplate('Mobile', $templateName)));
		return $response;
	}

}
