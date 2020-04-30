<?php

include_once('vtlib/Vtiger/Module.php');
include_once 'modules/CloudPBX/ProvidersEnum.php';

use CloudPBX\ProvidersEnum;

class CloudPBX extends CRMEntity {

    function CloudPBX() {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }

    function save_module() {
        
    }

    function vtlib_handler($modulename, $event_type) {
        if ($event_type == 'module.postinstall') {
            $this->addResources();
            $this->createFields();
            $this->providerInfoInsertion();
            $this->settingsInsertion();
        } else if ($event_type == 'module.disabled') {
            $this->removeResources();
        } else if ($event_type == 'module.enabled') {
            $this->addResources();
        } else if ($event_type == 'module.preuninstall') {
            $this->removeResources();
        } else if ($event_type == 'module.preupdate') {
            
        } else if ($event_type == 'module.postupdate') {
            
        }
    }

    private function settingsInsertion() {
        $db = PearDatabase::getInstance();
        $displayLabel = 'CloudPBX';

        $fieldid = $db->query_result(
                $db->pquery("SELECT fieldid FROM vtiger_settings_field WHERE name=?", array($displayLabel)), 0, 'fieldid');
        if (!$fieldid) {
            $blockid = $db->query_result(
                    $db->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label='LBL_INTEGRATION'", array()), 0, 'blockid');
            $sequence = (int) $db->query_result(
                            $db->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", array($blockid)), 0, 'sequence') + 1;
            $fieldid = $db->getUniqueId('vtiger_settings_field');
            $db->pquery("INSERT INTO vtiger_settings_field (fieldid, blockid, sequence, name, iconpath, linkto)
                        VALUES (?,?,?,?,?,?)", array($fieldid, $blockid, $sequence, $displayLabel, '',
                'index.php?module=CloudPBX&parent=Settings&view=Index'));
        }
    }

    private function providerInfoInsertion() {
        $db = PearDatabase::getInstance();
        $db->query("INSERT INTO " . Settings_CloudPBX_Record_Model::settingsTable . " VALUES (1, '" . ProvidersEnum::ZADARMA . "', 'zadarma_secret', 'Zadarma secret', '')");
        $db->query("INSERT INTO " . Settings_CloudPBX_Record_Model::settingsTable . " VALUES (2, '" . ProvidersEnum::ZADARMA . "', 'zadarma_key', 'Zadarma key', '')");

        $db->query("INSERT INTO " . Settings_CloudPBX_Record_Model::settingsTable . " VALUES (26, '" . ProvidersEnum::DOMRU . "', 'domru_url', 'Dom.Ru API url', '')");
        $db->query("INSERT INTO " . Settings_CloudPBX_Record_Model::settingsTable . " VALUES (27, '" . ProvidersEnum::DOMRU . "', 'domru_key', 'Dom.Ru key', '')");
        $db->query("INSERT INTO " . Settings_CloudPBX_Record_Model::settingsTable . " VALUES (28, '" . ProvidersEnum::DOMRU . "', 'domru_crm_key', 'Dom.Ru CRM key', '')");

        $db->pquery("INSERT INTO " . Settings_CloudPBX_Record_Model::defaultProvideTable . " values(?)", array(ProvidersEnum::ZADARMA));
    }

    private function createFields() {
        $moduleInstance = Vtiger_Module_Model::getInstance('PBXManager');
        $blockInstance = Vtiger_Block_Model::getInstance('LBL_PBXMANAGER_INFORMATION', $moduleInstance);

        if (!Vtiger_Field_Model::getInstance('cloud_is_local_cached', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_is_local_cached';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Is local recorded';
            $fieldInstance->column = 'cloud_is_local_cached';
            $fieldInstance->columntype = 'tinyint';
            $fieldInstance->uitype = 1;
            $fieldInstance->defaultvalue = 0;
            $fieldInstance->displaytype = 3;
            $fieldInstance->typeofdata = 'C~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_recordingurl', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_recordingurl';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Recording url';
            $fieldInstance->column = 'cloud_recordingurl';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_is_recorded', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_is_recorded';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Is recorded';
            $fieldInstance->column = 'cloud_is_recorded';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_recorded_call_id', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_recorded_call_id';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Recorder call id';
            $fieldInstance->column = 'cloud_recorded_call_id';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_pbx_provider', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_pbx_provider';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Provider';
            $fieldInstance->column = 'cloud_pbx_provider';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_call_status_code', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_call_status_code';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'Status code';
            $fieldInstance->column = 'cloud_call_status_code';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_called_from_number', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_called_from_number';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'From number';
            $fieldInstance->column = 'cloud_called_from_number';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        if (!Vtiger_Field_Model::getInstance('cloud_called_to_number', $moduleInstance)) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_called_to_number';
            $fieldInstance->table = 'vtiger_pbxmanager';
            $fieldInstance->label = 'To number';
            $fieldInstance->column = 'cloud_called_to_number';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $blockInstance->addField($fieldInstance);
        }

        $usersModuleModel = Vtiger_Module_Model::getInstance("Users");

        if (!Vtiger_Field_Model::getInstance('cloud_pbx_extension', $usersModuleModel)) {
            $userInfoBlock = Vtiger_Block_Model::getInstance('LBL_MORE_INFORMATION', $usersModuleModel);

            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name = 'cloud_pbx_extension';
            $fieldInstance->table = 'vtiger_users';
            $fieldInstance->label = 'Cloud PBX extension';
            $fieldInstance->column = 'cloud_pbx_extension';
            $fieldInstance->columntype = 'VARCHAR(255)';
            $fieldInstance->uitype = 1;
            $fieldInstance->typeofdata = 'V~O';
            $userInfoBlock->addField($fieldInstance);
        }

    }

    private function addResources() {
        Vtiger_Link::addLink(0, 'HEADERSCRIPT', 'CloudPBX', 'modules/CloudPBX/resources/CloudPBX.js');
    }

    private function removeResources() {
        Vtiger_Link::deleteLink(0, 'HEADERSCRIPT', 'CloudPBX', 'modules/CloudPBX/resources/CloudPBX.js');
    }

}
