<?php
$Vtiger_Utils_Log = true;
require_once('vtlib/Vtiger/Module.php');
require_once('vtlib/Vtiger/Block.php');
require_once('vtlib/Vtiger/Field.php');
$module = Vtiger_Module::getInstance('SalesOrder');
if ($module) {
    $block = Vtiger_Block::getInstance('Центр контроля качества', $module);
    if ($block) {
        $field = Vtiger_Field::getInstance('assigned_master', $module);
        if (!$field) {
            $field               = new Vtiger_Field();
            $field->name         = 'assigned_master';
            $field->table        = $module->basetable;
            $field->label        = 'Ответственный Мастер';
            $field->column       = 'assigned_master';
            $field->columntype   = 'VARCHAR(255)';
            $field->uitype       = 53;
            $field2->displaytype = 3;
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