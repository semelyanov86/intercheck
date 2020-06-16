<?php
// This is the SPConfiguration autogenerated custom report method file


class TestReport extends AbstractCustomReportModel {

    public function getChartsViewControlData() {
        return array(
            Reports_CustomReportTypes_Model::TABLE => array(),
            Reports_CustomReportTypes_Model::PIE => array(
                'group' => array(
                    'sp_delay_level' => 'Уровень просрочки',
                ),
                'agregate' => array(
                    'treatments_count' => 'Количество задач'
                ),

            ),
            Reports_CustomReportTypes_Model::BARCHART => array(
                'group' => array(
                    'sp_delay_level' => 'Уровень просрочки',
                ),
                'agregate' => array(
                    'treatments_count' => 'Количество задач'
                ),

            ),
            Reports_CustomReportTypes_Model::LINEAR => array(
                'group' => array(
                    'sp_delay_level' => 'Уровень просрочки',
                ),
                'agregate' => array(
                    'treatments_count' => 'Количество задач'
                ),

            ),
        );
    }

    protected function getCalculationData($outputFormat = 'PDF') {
        $sql = $this->getFilterSql();

        file_put_contents("log.txt", $sql);

        if($this->getViewTypeName() === Reports_CustomReportTypes_Model::TABLE) {
            return [
                [1,2],
                [3,4]
            ];
        } else if($this->getViewTypeName() === Reports_CustomReportTypes_Model::PIE) {
            return [
                'values' => [5,6,7,8],
                'labels' => ['A','B','C','D'],
                'data_labels' => ['Q','W', 'E', 'R']
            ];
        } else {
            return [
                'values' => [[5,6,7,8],[1,2,3,8],[1,2,3,8], [1,2,3,8]],
                'labels' => ['A','B','C','D'],
                'data_labels' => ['Q','W', 'E', 'R']
            ];
        }
    }

    protected function getLabels($outputFormat = 'PDF') {
        return ["Поле 1", "Поле 2"];
    }

    protected function getVirtualField() {
        $fieldModel = new Vtiger_Field_Model();
        $fieldModel->id = -1;
        $fieldModel->name = 'sp_current_labor';
        $fieldModel->label = 'Текущая трудоемкость(час)';
        $fieldModel->table = 'vtiger_treatments';
        $fieldModel->column = 'sp_current_labor';
        $fieldModel->columntype = 0;
        $fieldModel->uitype = 7;
        $fieldModel->typeofdata = 'N~O';
        $fieldModel->block = Vtiger_Block::getInstance('LBL_CUSTOMER', Vtiger_Module_Model::getInstance('SPTreatments'));

        return $fieldModel;
    }

}

?>