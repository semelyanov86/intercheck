<?php

include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Query.php';

class Webapp_WS_Query extends Webapp_WS_FetchRecordWithGrouping {
	
	function processQueryResultRecord(&$record, $user) {
		$this->resolveRecordValues($record, $user);
		return $record;
	}
	
	function process(Webapp_API_Request $request) {
		$current_user = $this->getActiveUser();
		
		$query = $request->get('query', '', false);
		$nextPage = 0;
		$queryResult = false;
		
		if (preg_match("/(.*) LIMIT[^;]+;/i", $query)) {
			$queryResult = vtws_query($query, $current_user);
		} else {
			// Implicit limit and paging
			$query = rtrim($query, ";");

			$currentPage = intval($request->get('page', 0));
			$FETCH_LIMIT = Webapp::config('API_RECORD_FETCH_LIMIT'); 
			$startLimit = $currentPage * $FETCH_LIMIT;
			
			$queryWithLimit = sprintf("%s LIMIT %u,%u;", $query, $startLimit, ($FETCH_LIMIT+1));
			$queryResult = vtws_query($queryWithLimit, $current_user);
			
			// Determine paging
			$hasNextPage = (count($queryResult) > $FETCH_LIMIT);
			if ($hasNextPage) {
				array_pop($queryResult); // Avoid sending next page record now
				$nextPage = $currentPage + 1;
			}
		}

		$records = array();
		if (!empty($queryResult)) {
			foreach($queryResult as $recordValues) {
				$records[] = $this->processQueryResultRecord($recordValues, $current_user);
			}
		}
		$result = array('records' => $records, 'nextPage' => $nextPage );
		
		$response = new Webapp_API_Response();
		$response->setResult($result);
		return $response;
	}
}
