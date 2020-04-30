<?php

class Webapp_UI_Error  extends Webapp_WS_Controller {
	protected $error;
	
	function setError($e) {
		$this->error = $e;
	}
	
	function process(Webapp_API_Request $request) {
		$viewer = new Webapp_UI_Viewer();
		$viewer->assign('errorcode', $this->error['code']);
		$viewer->assign('errormsg', $this->error['message']);
		return $viewer->process('generic/Error.tpl');
	}

}
