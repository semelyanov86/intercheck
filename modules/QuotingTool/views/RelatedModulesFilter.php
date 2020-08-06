<?php

class QuotingTool_RelatedModulesFilter_View extends Vtiger_Detail_View
{
    /**
     * must be overriden
     * @param Vtiger_Request $request
     * @return boolean
     */
    public function preProcess(Vtiger_Request $request)
    {
        return true;
    }
    /**
     * must be overriden
     * @param Vtiger_Request $request
     * @return boolean
     */
    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $relatedModule = $request->get("relatedModule");
        if (!empty($relatedModule)) {
            $conditions = $request->get("conditions");
            $qualifiedModuleName = $relatedModule;
            $recordRelatedModel = Vtiger_Record_Model::getCleanInstance($relatedModule);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordRelatedModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);
            $recordStructure = $recordStructureInstance->getStructure();
            if (is_array($conditions)) {
                $conditionsAll = $conditions["all"];
                $conditionsAny = $conditions["any"];
                $transformedAdvancedCondition = array();
                $allGroupColumns = array();
                $anyGroupColumns = array();
                if (!empty($conditionsAll)) {
                    foreach ($conditionsAll as $condition) {
                        $item = array();
                        $item["columnname"] = $condition["fieldname"];
                        $item["comparator"] = $condition["operation"];
                        $item["value"] = $condition["value"];
                        $item["column_condition"] = "and";
                        $allGroupColumns[] = $item;
                    }
                    end($allGroupColumns);
                    $key = key($allGroupColumns);
                    $allGroupColumns[$key]["column_condition"] = "";
                    reset($allGroupColumns);
                }
                if (!empty($conditionsAny)) {
                    foreach ($conditionsAny as $condition) {
                        $item = array();
                        $item["columnname"] = $condition["fieldname"];
                        $item["comparator"] = $condition["operation"];
                        $item["value"] = $condition["value"];
                        $item["column_condition"] = "or";
                        $anyGroupColumns[] = $item;
                    }
                    end($anyGroupColumns);
                    $key = key($anyGroupColumns);
                    $anyGroupColumns[$key]["column_condition"] = "";
                    reset($anyGroupColumns);
                }
                $transformedAdvancedCondition[1] = array("columns" => $allGroupColumns, "condition" => "and");
                $transformedAdvancedCondition[2] = array("columns" => $anyGroupColumns, "condition" => "");
                $viewer->assign("SELECTED_ADVANCED_FILTER_FIELDS", $transformedAdvancedCondition);
            }
            $viewer->assign("RECORD_STRUCTURE", $recordStructure);
            if ($relatedModule == "Calendar") {
                $advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
            } else {
                $advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
            }
            $viewer->assign("ADVANCED_FILTER_OPTIONS", Vtiger_Field_Model::getAdvancedFilterOptions());
            $viewer->assign("ADVANCED_FILTER_OPTIONS_BY_TYPE", $advanceFilterOpsByFieldType);
            $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
            foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
                $comparatorInfo["startdate"] = DateTimeField::convertToUserFormat($comparatorInfo["startdate"]);
                $comparatorInfo["enddate"] = DateTimeField::convertToUserFormat($comparatorInfo["enddate"]);
                $comparatorInfo["label"] = vtranslate($comparatorInfo["label"], $qualifiedModuleName);
                $dateFilters[$comparatorKey] = $comparatorInfo;
            }
            $viewer->assign("DATE_FILTERS", $dateFilters);
            $viewer->assign("MODULE", $relatedModule);
            $viewer->assign("SOURCE_MODULE", $relatedModule);
        } else {
            $viewer->assign("MODULE", "");
        }
        $viewer->assign("QUALIFIED_MODULE", $moduleName);
        $viewer->view("RelatedModulesFilter.tpl", $request->getModule());
    }
}

?>