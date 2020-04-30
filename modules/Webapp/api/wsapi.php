<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**********************************************************************
 * Expose the extensions added over webservice API
 */

function webapp_ws_fetchAllAlerts($user) {
	$request = new Webapp_API_Request();
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_FetchAllAlerts', 'ws/FetchAllAlerts.php');
}

function webapp_ws_alertDetailsWithMessage($alertid, $user) {
	$request = new Webapp_API_Request();
	$request->set('alertid', $alertid);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_AlertDetailsWithMessage', 'ws/AlertDetailsWithMessage.php');
}

function webapp_ws_fetchModuleFilters($module, $user) {
	$request = new Webapp_API_Request();
	$request->set('module', $module);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_FetchModuleFilters', 'ws/FetchModuleFilters.php');
}

function webapp_ws_fetchRecord($record, $user) {
	$request = new Webapp_API_Request();
	$request->set('record', $record);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_FetchRecord', 'ws/FetchRecord.php');
}

function webapp_ws_fetchRecordWithGrouping($record, $user) {
	$request = new Webapp_API_Request();
	$request->set('record', $record);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_FetchRecordWithGrouping', 'ws/FetchRecordWithGrouping.php');
}

function webapp_ws_filterDetailsWithCount($filterid, $user) {
	$request = new Webapp_API_Request();
	$request->set('filterid', $filterid);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_FilterDetailsWithCount', 'ws/FilterDetailsWithCount.php');
}

function webapp_ws_listModuleRecords($elements, $user) {
	$request = new Webapp_API_Request($elements); // elements can have key (module, alertid, filterid, search, page)
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_ListModuleRecords', 'ws/ListModuleRecords.php');
}

function webapp_ws_saveRecord($module, $record, $values, $user) {
	$request = new Webapp_API_Request();
	$request->set('module', $module);
	$request->set('record', $record);
	$request->set('values', $values);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_SaveRecord', 'ws/SaveRecord.php');
}

function webapp_ws_syncModuleRecords($module, $syncToken, $page, $user) {
	$request = new Webapp_API_Request();
	$request->set('module', $module);
	$request->set('syncToken', $syncToken);
	$request->set('page', $page);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_SyncModuleRecords', 'ws/SyncModuleRecords.php');
}

function webapp_ws_query($module, $query, $page, $user) {
	$request = new Webapp_API_Request();
	$request->set('module', $module);
	$request->set('query', $query);
	$request->set('page', $page);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_Query', 'ws/Query.php');
}

function webapp_ws_queryWithGrouping($module, $query, $page, $user) {
	$request = new Webapp_API_Request();
	$request->set('module', $module);
	$request->set('query', $query);
	$request->set('page', $page);
	return Webapp_WS_API::process($request, $user, 'Webapp_WS_QueryWithGrouping', 'ws/QueryWithGrouping.php');
}
/**********************************************************************/

/**
 * Mobile WS API Controller
 */
include_once dirname(__FILE__) . '/Request.php';
include_once dirname(__FILE__) . '/Response.php';
include_once dirname(__FILE__) . '/Session.php';
include_once dirname(__FILE__) . '/ws/Controller.php';

class Webapp_WS_API {
	private $controller;
	
	function initController($className, $handlerPath, $user) {
		include_once dirname(__FILE__) . "/$handlerPath";
		
		$this->controller = new $className();
		Webapp_API_Session::init(session_id());
		$this->controller->initActiveUser($user);
		return $this->controller;
	}
	
	function getController() {
		return $this->controller;
	}
	
	static function process(Webapp_API_Request $request, $user, $className, $handlerPath) {
		
		if(vtlib_isModuleActive('Mobile') === false) {
			throw new WebServiceException('1501', 'Service not available');
		}
		
		$wsapiController = new self();
		$response = $wsapiController->initController($className, $handlerPath, $user)->process($request);
		if($response->hasError()) {
			$error = $response->getError();
			throw new WebServiceException($error['code'], $error['message']);
		}
		return $response->getResult();
	} 
	
}
