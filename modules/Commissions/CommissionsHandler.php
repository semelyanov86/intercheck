<?php
require_once 'include/events/VTEventHandler.inc';
//vimport('~~/vtlib/Vtiger/Module.php');

class CommissionsHandler extends VTEventHandler {

    function handleEvent($eventName, $data) {
        global $adb;
        if ($eventName == 'vtiger.entity.aftersave' && $data->getModuleName() == "Commissions") {
            $moduleId = $data->getId();
            $moduleName = $data->getModuleName();
            $entityData = $data->getData();
            foreach($entityData as $key => $value)
            {
                if(preg_match('/^cf_.+_id$/', $key, $name))
                {
                    $relModule = $adb->pquery("SELECT
                                    vtiger_field.*, vtiger_fieldmodulerel.relmodule, vtiger_tab.name
                                FROM
                                    vtiger_field
                                JOIN vtiger_tab ON (
                                    vtiger_field.tabid = vtiger_tab.tabid
                                )
                                JOIN vtiger_fieldmodulerel ON (
                                    vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid
                                )
                                WHERE
                                    vtiger_field.fieldname = ?
                                 ",array($key));
                    if($adb->num_rows($relModule) > 0){
                        $moduleNameRelated = '';
                        while ($row = $adb->fetchByAssoc($relModule)) {
                            $moduleNameRelated = $row['relmodule'];
                        }
                        if(!empty($value)){
                            $result = $adb->pquery("SELECT vtiger_crmentityrel.*
                                                    FROM
                                                        vtiger_crmentityrel
                                                    WHERE crmid = ? AND relcrmid = ?",array($value,$moduleId,));

                            if($adb->num_rows($result) == 0){
                                $adb->pquery("INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUE (?,?,?,?)",array($value,$moduleNameRelated,$moduleId,$moduleName));
                            }
                        }else{
                            $result = $adb->pquery("SELECT vtiger_crmentityrel.*
                                                    FROM
                                                        vtiger_crmentityrel
                                                    WHERE module = ? AND relcrmid = ? AND relmodule = ?",array($moduleNameRelated,$moduleId,$moduleName));

                            if($count = $adb->num_rows($result) > 0){
                                $adb->pquery("DELETE FROM vtiger_crmentityrel WHERE module = ? AND relcrmid = ? AND relmodule = ?",array($moduleNameRelated,$moduleId,$moduleName));
                            }
                        }
                    }

                }
            }
            $this->updateTotalCommission($entityData['cf_transactions_id']);
        }

    }
    function updateTotalCommission($transactionId)
    {
        global $VTIGER_BULK_SAVE_MODE;
        $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
        $VTIGER_BULK_SAVE_MODE = true;
        $total = Commissions_Module_Model::getTotalCommissions($transactionId);
        $transactionModel = Vtiger_Record_Model::getInstanceById($transactionId, 'Transactions');
        $transactionModel->set('mode', 'edit');
        $transactionModel->set('cf_total_commissions', $total);
        $transactionModel->save();
        $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
    }
}