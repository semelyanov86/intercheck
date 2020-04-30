<?php

include_once dirname(__FILE__) . '/Query.php';

include_once 'include/Webservices/Query.php';

class Webapp_WS_QueryWithGrouping extends Webapp_WS_Query {
	
	private $queryModule;
	
	function processQueryResultRecord($record, $user) {
		parent::processQueryResultRecord($record, $user);

		if ($this->cachedDescribeInfo() === false) {
			$describeInfo = vtws_describe($this->queryModule, $user);
			$this->cacheDescribeInfo($describeInfo);
		}
		$transformedRecord = $this->transformRecordWithGrouping($record, $this->queryModule);
		// Update entity fieldnames
		$transformedRecord['labelFields'] = $this->cachedEntityFieldnames($this->queryModule);
		return $transformedRecord;
	}
	
	function process(Webapp_API_Request $request) {
		$this->queryModule = $request->get('module');
		return parent::process($request);
	}
}
