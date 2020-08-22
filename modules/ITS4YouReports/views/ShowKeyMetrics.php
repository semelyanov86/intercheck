<?php

class ITS4YouReports_ShowKeyMetrics_View extends Vtiger_IndexAjax_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
        $componentName = $request->get('name');
        $linkId = $request->get('linkid');

        if ('v7' === $layout) {
            $tabid = ($request->has('tabid') ? $request->get('tabid') : $request->get('tab'));
			if (empty($tabid)) {
				$getTabIdResult = $db->pquery('SELECT id FROM vtiger_dashboard_tabs WHERE userid=?', [$currentUser->getId()]);
				if ($getTabIdResult && $db->num_rows($getTabIdResult)) {
					$tabIdRow = $db->fetchByAssoc($getTabIdResult, 0);
					$tabid = $tabIdRow['id'];
				}
			}
            if(!empty($componentName)) {
                $className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
                if(!empty($className)) {
                    $widget = NULL;
                    $checkResult = $db->pquery('SELECT id FROM vtiger_module_dashboard_widgets
                                                                WHERE linkid=? AND userid=? AND dashboardtabid=?', array($linkId, $currentUser->getId(), $tabid));
                    if (!$db->num_rows($checkResult)) {
                        if(!empty($linkId)) {
                            $widget = new Vtiger_Widget_Model();
                            $widget->set('linkid', $linkId);
                            $widget->set('userid', $currentUser->getId());
                            $widget->set('filterid', $request->get('filterid', NULL));
                            $widget->set('tabid', $tabid);
                            if ($request->has('data')) {
                                $widget->set('data', $request->get('data'));
                            }
                            $widget->add();
                        }
                        $widgetid = $widget->has('widgetid') ? $widget->get('widgetid') : $widget->get('id');
                        $checkResult = $db->pquery('SELECT id FROM vtiger_module_dashboard_widgets
                                                                WHERE id=? AND linkid=? AND dashboardtabid=?', array($linkId, $widgetid, $tabid));
                        if (!$db->num_rows($checkResult)) {
                            $db->pquery('UPDATE vtiger_module_dashboard_widgets SET dashboardtabid=? WHERE id=? AND userid=?', array($tabid, $widgetid, $currentUser->getId()));
                        }
                    }

                    $classInstance = new $className();
                    $classInstance->process($request, $widget);
                    return;
                }
            }
        } else {
            if(!empty($componentName)) {
                $className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
                if(!empty($className)) {
                    $widget = NULL;
                    if(!empty($linkId)) {
                        $widget = new Vtiger_Widget_Model();
                        $widget->set('linkid', $linkId);
                        $widget->set('userid', $currentUser->getId());
                        $widget->set('filterid', $request->get('filterid', NULL));
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
        }


        $response = new Vtiger_Response();
        $response->setResult(array('success'=>false,'message'=>  vtranslate('NO_DATA')));
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

}
?>