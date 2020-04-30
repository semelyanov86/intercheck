<?php

include_once dirname(__FILE__) . '/../api/ws/Login.php';

class Webapp_UI_Login  extends Webapp_WS_Login {
	
	function process(Webapp_API_Request $request) {
		$viewer = new Webapp_UI_Viewer();
		return $viewer->process('generic/Login.tpl');
	}

}
