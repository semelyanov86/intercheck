<?php
class EMAILMaker_Record_Model extends Vtiger_Record_Model {
	
    public function getId(){
        return $this->get('templateid');
    }
    public function setId($value){
        return $this->set('templateid',$value);
    }	
    public function delete(){
        $this->getModule()->deleteRecord($this);
    }
    public function deleteAllRecords(){
        $this->getModule()->deleteAllRecords();
    }
    public function getEmailTemplateFields(){
        return $this->getModule()->getAllModuleEmailTemplateFields();
    }
    public function getTemplateData($record){
        return $this->getModule()->getTemplateData($record);
    }
    /**
     *  Functions returns delete url
     * @return String - delete url
     */
    public function getDeleteUrl() {
        return 'index.php?module=EMAILMaker&action=Delete&record='.$this->getId();
    }
    /**
     * Function to get the Edit View url for the record
     * @return <String> - Record Edit View Url
     */
    public function getEditViewUrl() {
        return 'index.php?module=EMAILMaker&view=Edit&record='.$this->getId();
    }

    /**
     * Funtion to get Duplicate Record Url
     * @return <String>
     */
    public function getDuplicateRecordUrl() {
        return 'index.php?module=EMAILMaker&view=Edit&record='.$this->getId().'&isDuplicate=true';

    }
    public function getDetailViewUrl() {
        $module = $this->getModule();
        return 'index.php?module=EMAILMaker&view='.$module->getDetailViewName().'&record='.$this->getId();
    }
    public static function getInstanceById($templateId, $module=null) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT vtiger_emakertemplates_displayed.*, vtiger_emakertemplates.*  FROM vtiger_emakertemplates 
                                    LEFT JOIN vtiger_emakertemplates_displayed ON vtiger_emakertemplates_displayed.templateid = vtiger_emakertemplates.templateid 
                                    WHERE vtiger_emakertemplates.templateid = ?', array($templateId));
        if($db->num_rows($result) > 0) {
                $row = $db->query_result_rowdata($result, 0);
                $recordModel = new self();
                $row['label'] = $row['templatename'];

                return $recordModel->setData($row)->setId($templateId)->setModule($row['module']!=""?$row['module']:'EMAILMaker');
        }
        return null;
    }
    public function getName(){
        return $this->get('templatename');
    }
    public function isDeleted() {
        if ($this->get('deleted') == '1') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Function returns valuetype of the field filter
     * @return <String>
     */
    function getFieldFilterValueType($fieldname) {
        $conditions = $this->get('conditions');
        if(!empty($conditions) && is_array($conditions)) {
            foreach($conditions as $filter) {
                if($fieldname == $filter['fieldname']) {
                    return $filter['valuetype'];
                }
            }
        }
        return false;
    }

    public function updateDisplayConditions($conditions,$displayed_value) {
        $adb = PearDatabase::getInstance();
        $templateid = $this->getId();
        $adb->pquery("DELETE FROM vtiger_emakertemplates_displayed WHERE templateid=?", array($templateid));

        $conditions = $this->transformAdvanceFilterToEMAILMakerFilter($conditions);

        $display_conditions = Zend_Json::encode($conditions);



        $adb->pquery("INSERT INTO vtiger_emakertemplates_displayed (templateid,displayed,conditions) VALUES (?,?,?)", array($templateid,$displayed_value,$display_conditions));
        return true;
    }

    public function transformAdvanceFilterToEMAILMakerFilter($conditions) {
        $wfCondition = array();

        if(!empty($conditions)) {
            foreach($conditions as $index => $condition) {
                $columns = $condition['columns'];
                if($index == '1' && empty($columns)) {
                    $wfCondition[] = array('fieldname'=>'', 'operation'=>'', 'value'=>'', 'valuetype'=>'',
                        'joincondition'=>'', 'groupid'=>'0');
                }
                if(!empty($columns) && is_array($columns)) {
                    foreach($columns as $column) {
                        $wfCondition[] = array('fieldname'=>$column['columnname'], 'operation'=>$column['comparator'],
                            'value'=>$column['value'], 'valuetype'=>$column['valuetype'], 'joincondition'=>$column['column_condition'],
                            'groupjoin'=>$condition['condition'], 'groupid'=>$column['groupid']);
                    }
                }
            }
        }
        return $wfCondition;
    }

    function getConditonDisplayValue() {
        $conditionList = array();
        $displayed = $this->get('displayed');
        $conditions = $this->get('conditions');
        $moduleName = $this->get('module');
        if (!empty($conditions)) {
            $PDFMaker_Display_Model = new EMAILMaker_Display_Model();
            $conditionList = $PDFMaker_Display_Model->getConditionsForDetail($displayed,$conditions,$moduleName);
        }
        return $conditionList;
    }
}
