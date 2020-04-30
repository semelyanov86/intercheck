<?php

include_once dirname(__FILE__) . '/FetchModuleFilters.php';

include_once 'modules/CustomView/CustomView.php';

class Webapp_WS_FilterDetailsWithCount extends Webapp_WS_FetchModuleFilters {
	
	function process(Webapp_API_Request $request) {
		global $current_user;
		
		$response = new Webapp_API_Response();

		$filterid = trim($request->get('filterid'));
		$current_user = $this->getActiveUser();
		
		$result = array();
		$result['filter'] = $this->getModuleFilterDetails($filterid);
		$response->setResult($result);

		return $response;
	}
	
	protected function getModuleFilterDetails($filterid) {
		global $adb;
		$result = $adb->pquery("SELECT * FROM vtiger_customview WHERE cvid=?", array($filterid));
		if ($result && $adb->num_rows($result)) {
			$resultrow = $adb->fetch_array($result);
				
			$module = $resultrow['entitytype'];
				
			$view = new CustomView($module);
			$viewid = $resultrow['cvid'];
			$view->getCustomViewByCvid($viewid);
			$viewQuery = $view->getModifiedCvListQuery($viewid, getListQuery($module), $module);
				
			$countResult = $adb->pquery(Vtiger_Functions::mkCountQuery($viewQuery), array()); 
			$count = 0;
			if($countResult && $adb->num_rows($countResult)) {
				$count = $adb->query_result($countResult, 0, 'count');
			}
				
			$filter = $this->prepareFilterDetailUsingResultRow($resultrow);
			$filter['userName'] = getUserName($resultrow['userid']);
			$filter['count'] = $count;
				
			return $filter;
		}
	}
}
