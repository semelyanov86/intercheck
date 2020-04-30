<?php

class CloudPBX_MassActionAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("showPhonesPopup");
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * Function returns the popup edit form
     * @param Vtiger_Request $request
     */
    public function showPhonesPopup(Vtiger_Request $request)
    {
        global $adb;
        $moduleName = $request->getModule();
        $parentModule = $request->get('parent');
        $moduleModel = Vtiger_Module_Model::getInstance($parentModule);
        $recordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $parentModule);
        $phoneFields = $moduleModel->getFieldsByType('phone');
        $values = array();
        foreach ($phoneFields as $field => $fieldName) {
            $values[$fieldName->getId()] = $recordModel->get($fieldName->getName());
        }
        $viewer = $this->getViewer($request);
        $viewer->assign("USER_MODEL", Users_Record_Model::getCurrentUserModel());
        $viewer->assign("SCRIPTS", $this->getHeaderScripts($request));
        $viewer->assign("FIELD_VALUES", $values);
        $viewer->assign("FIELDS", $phoneFields);
        $viewer->assign('PARENT', $parentModule);
        echo $viewer->view("MassCallForm.tpl", $moduleName, true);
    }
    public function getEditForm(Vtiger_Request $request)
    {
        global $adb;
        $moduleName = $request->getModule();
        $source_module = $request->get("source_module");
        $record = $request->get("record");
        $mode = $request->get("editmode");
        $viewer = $this->getViewer($request);
        if ($record != "") {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $source_module);
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($source_module);
        }
        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);
        foreach ($requestFieldList as $fieldName => $fieldValue) {
            $fieldModel = $fieldList[$fieldName];
            if ($fieldModel->isEditable()) {
                $recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
            }
        }
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $viewer->assign("RECORD_STRUCTURE_MODEL", $recordStructureInstance);
        $viewer->assign("RECORD_STRUCTURE", $recordStructureInstance->getStructure());
        $viewer->assign("MODULE", $source_module);
        $viewer->assign("MODE", $mode);
        $viewer->assign("USER_MODEL", Users_Record_Model::getCurrentUserModel());
        echo $viewer->view("EditViewBlocks.tpl", $moduleName, true);
    }
    public function checkReordType(Vtiger_Request $request)
    {
        global $adb;
        $activityid = $request->get("record");
        $rs = $adb->pquery("SELECT activitytype FROM `vtiger_activity` WHERE activityid=?", array($activityid));
        if ($adb->query_result($rs, 0, "activitytype") == "Task") {
            exit("Calendar");
        }
        exit("Events");
    }
    public function getNewRecordView(Vtiger_Request $request)
    {
        global $adb;
        $moduleName = $request->getModule();
        $tabno = $request->get("tabno");
        $rel_module = $request->get("rel_module");
        $viewer = $this->getViewer($request);
        $recordModel = Vtiger_Record_Model::getCleanInstance($rel_module);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordModel->getModule()->getNameFields();
        $EntityField = array("fieldname" => Vtiger_Cache::get("EntityField", $rel_module)->basetableid, "field_info" => Zend_Json::encode(array("mandatory" => false, "presence" => true, "defaultvalue" => false, "type" => "reference", "name" => Vtiger_Cache::get("EntityField", $rel_module)->basetableid, "label" => vtranslate("SINGLE_" . $rel_module, $rel_module) . " Name")));
        $viewer->assign("ENTITY_FIELD", $EntityField);
        $viewer->assign("RECORD_STRUCTURE_MODEL", $recordStructureInstance);
        $viewer->assign("USER_MODEL", Users_Record_Model::getCurrentUserModel());
        $viewer->assign("MODULE_LABEL", $rel_module);
        $viewer->assign("TABNO", $tabno);
        echo $viewer->view("RelatedRecordView.tpl", $moduleName, true);
    }
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = array("modules.Vtiger.resources.Edit", "modules.Contacts.resources.Edit", "modules.Accounts.resources.Edit", "modules.Calendar.resources.Edit", "modules.Vtiger.resources.Detail", "modules.Leads.resources.Detail");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        return $jsScriptInstances;
    }
}

?>