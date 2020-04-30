<?php

include_once 'include/Webservices/Retrieve.php';

class Webapp_WS_GetProcesses extends Webapp_WS_Controller {
    public $moduleModel;

	function process(Webapp_API_Request $request) {
		$current_user = $this->getActiveUser();
		$this->moduleModel = new FixdigitalProcessControl_Module_Model();
        $this->moduleModel->set('selectedModule', 'Leads');
		$result = array();
		$processes = $this->getAllProcesses();
		foreach($processes as $process) {
		    $tmp = array(
		        'id' => $process['id'],
                'title' => $process['process']
            );
		    $this->moduleModel->set('processId', $process['id']);
		    $tmp['statuses'] = $this->getStatuses();
		    $result[] = $tmp;
        }

		$response = new Webapp_API_Response();
		$response->setResult($result);
		
		return $response;
	}

	public function getAllProcesses()
    {
        return $this->moduleModel->getProcessesByModule();
    }

    public function getStatuses()
    {
        $statuses = $this->moduleModel->getStatusesByProcess();
        $statusOrder = array(
            4 => 'בטיפול',
            7 => 'פגישה',
            9 => 'עסקה',
            11 => 'לא רלוונטי'
        );
        $res = array();
        foreach ($statusOrder as $key=>$order) {
            $res[] = array('id' => $key, 'title' => $order);
        }
        foreach ($statuses as $status) {
            $fx = $status['fx_status'];
            $key = array_search($fx, array_column($res, 'title'));
            $res[$key]['subStatuses'][] = $status;
        }
        return $res;
    }
}
