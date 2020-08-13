<?php
chdir('../');
$Vtiger_Utils_Log = true;
require_once('vtlib/Vtiger/Module.php');
require_once('vtlib/Vtiger/Block.php');
require_once('vtlib/Vtiger/Field.php');
$module = Vtiger_Module::getInstance('Contacts');
if ($module) {
    $block = Vtiger_Block::getInstance('Marketing Info', $module);
    if ($block) {
        $field = Vtiger_Field::getInstance('trx_owner_id', $module);
        if (!$field) {
            $field               = new Vtiger_Field();
            $field->name         = 'trx_owner_id';
            $field->table        = $module->basetable;
            $field->label        = 'Trx Owner ID';
            $field->column       = 'trx_owner_id';
            $field->columntype   = 'VARCHAR(255)';
            $field->uitype       = 53;
            $field->displaytype = 3;
            $field->typeofdata   = 'V~M';
            $block->addField($field);
        }
        $field = Vtiger_Field::getInstance('ftd_owner_id', $module);
        if (!$field) {
            $field               = new Vtiger_Field();
            $field->name         = 'ftd_owner_id';
            $field->table        = $module->basetable;
            $field->label        = 'FTD Owner ID';
            $field->column       = 'ftd_owner_id';
            $field->columntype   = 'VARCHAR(255)';
            $field->uitype       = 53;
            $field->displaytype = 3;
            $field->typeofdata   = 'V~M';
            $block->addField($field);
        }
    } else {
        echo "No block";
    }
} else {
    echo "No module";
}

?>
