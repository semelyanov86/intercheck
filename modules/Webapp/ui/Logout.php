<?php

class Webapp_UI_Logout extends Webapp_WS_Controller {
	
	function process(Webapp_API_Request $request) {
		HTTP_Session2::destroy(HTTP_Session2::detectId());
		header('Location: index.php');
		exit;
	}

}
