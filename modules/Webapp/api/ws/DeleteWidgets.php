<?php

include_once dirname(__FILE__) . '/FetchRecord.php';

class Webapp_WS_DeleteWidgets extends Webapp_WS_FetchRecord {
	
	function process(Webapp_API_Request $request) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		global $adb,$current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
  		$reportid = trim($request->get('reportid'));
  		$widgetid = trim($request->get('widgetid'));
  		$currentuserid = $current_user->id;
  		if(!empty($reportid)){

  			$widgetInstance = Vtiger_Widget_Model::getInstanceWithReportId($reportid, $currentuserid);
			$widgetInstance->remove();

			$del_query = $adb->pquery("DELETE FROM webapp_dashboard_sequence WHERE id = ? AND userid = ? AND type = ?",array($reportid,$currentuserid,'report'));
  		}else{
  			if($widgetid != ''){
	  			$widget = Vtiger_Widget_Model::getInstance($widgetid, $currentuserid);
	  			$widgetsid = $widget->get('id');
	  			if($widgetsid != ''){
	  				$widgetInstance = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetsid, $currentuserid);
	  				$widgetInstance->remove();
	  			}else{
	  				$widget->remove();
	  			}
	  			
	  			$del_query = $adb->pquery("DELETE FROM webapp_dashboard_sequence WHERE id = ? AND userid = ? AND type = ?",array($widgetid,$currentuserid,'widget'));
  			}
  		}
  		
		$response = new Webapp_API_Response();
		$response->setResult(array('message'=>'Removed Successfully'));
		return $response;
	}

}
