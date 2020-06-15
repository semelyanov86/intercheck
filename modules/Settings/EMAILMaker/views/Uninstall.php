<?php
/*******************************************************************************
 * The content of this file is subject to the EMAILMaker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ***************************************************************************** */

class Settings_EMAILMaker_Uninstall_View extends Settings_Vtiger_Index_View
{

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);

        $moduleName = $request->getModule();
        $settingsModuleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);

        $settingLinks = array();
        $moduleSettingLinks = $settingsModuleModel->getSettingLinks();

        foreach ($moduleSettingLinks as $settingsLink) {
            $settingLinks['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('LISTVIEW_LINKS', $settingLinks);
        $this->preProcessDisplay($request);
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->view('Uninstall.tpl', $qualifiedModule);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'modules.Settings.' . $moduleName . '.resources.Uninstall',
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}