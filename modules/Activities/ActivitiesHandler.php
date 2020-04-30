<?php
require_once 'include/events/VTEventHandler.inc';
//vimport('~~/vtlib/Vtiger/Module.php');

class ActivitiesHandler extends VTEventHandler {

    function handleEvent($eventName, $data) {
        global $adb;
        global $VTIGER_BULK_SAVE_MODE;
        if ($eventName == 'vtiger.entity.aftersave' && $data->getModuleName() == "Activities") {
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
                if ($key == 'cf_contacts_id') {
                    if ($value && $value > 0) {
                        $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
                        $VTIGER_BULK_SAVE_MODE = true;
                        $contactModel = Vtiger_Record_Model::getInstanceById($value, 'Contacts');
                        $activityModel = Vtiger_Record_Model::getInstanceById($moduleId, 'Activities');
                        $activityModel->set('mode', 'edit');
                        $activityModel->set('assigned_user_id', $contactModel->get('assigned_user_id'));
                        $activityModel->save();
                        $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
                    }
                }
            }
        } elseif ($eventName == 'vtiger.entity.aftersave' && $data->getModuleName() == "Contacts") {
            $changedFields =  $data->getData()->getChanged();
            $moduleId = $data->getId();
            if (in_array('assigned_user_id', $changedFields)) {
                $contactModel = Vtiger_Record_Model::getInstanceById($moduleId, 'Contacts');
                $userId = $contactModel->get('assigned_user_id');
                $relListModel = Vtiger_RelationListView_Model::getInstance($contactModel, 'Activities', 'Platform History');
                $pagingModel = new Vtiger_Paging_Model();
                $pagingModel->set('page', 1);
                $pagingModel->set('limit', 1000);
                $entries = $relListModel->getEntries($pagingModel);
                foreach($entries as $entry) {
                    $recordId = $entry->getId();
                    $historyModel = Vtiger_Record_Model::getInstanceById($recordId, 'Activities');
                    $historyModel->set('mode', 'edit');
                    $historyModel->set('assigned_user_id', $userId);
                    $historyModel->save();
                }
            }
        }
    }


}