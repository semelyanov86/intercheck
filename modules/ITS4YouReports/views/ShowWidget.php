<?php

class ITS4YouReports_ShowWidget_View extends Vtiger_IndexAjax_View {

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

    function process(Vtiger_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
        $componentName = $request->get('name');
        $linkId = $request->get('linkid');

        if ($request->has('tabid')) {
            $tabId = $request->get('tabid');
        }

        if(!empty($componentName)) {
            $className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);

            if ($request->has('tab')) {
                $tabId = $request->get('tab');
            } else {
                $layout = Vtiger_Viewer::getDefaultLayoutName();
                if ('v7' === $layout) {
                    $dasbBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
                    $defaultTab = $dasbBoardModel->getUserDefaultTab($currentUser->getId());

                    if (!$tabId) {
                        $tabId = $defaultTab['id'];
                    }
                }
            }

            if(!empty($className)) {
                $widget = NULL;

                if(!empty($linkId)) {
                    $widget = new Vtiger_Widget_Model();
                    $widget->set('linkid', $linkId);
                    $widget->set('userid', $currentUser->getId());
                    $widget->set('filterid', $request->get('filterid', NULL));
                    $widget->set('tabid', $tabId);
                    if ($request->has('data')) {
                        $widget->set('data', $request->get('data'));
                    }
                    $widget->add();
                }
                $classInstance = new $className();
                $classInstance->process($request, $widget);
                return;
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(array('success'=>false,'message'=>  vtranslate('NO_DATA')));
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request) {
        if(method_exists(self, 'validateWriteAccess')) {
           $request->validateWriteAccess();
        }
    }
}