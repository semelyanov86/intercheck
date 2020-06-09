<?php
chdir('../');
// Turn on debugging level
$Vtiger_Utils_Log = true;

// Include necessary classes
include_once('vtlib/Vtiger/Module.php');

// Define instances
$users = Vtiger_Module::getInstance('Users');

// Nouvelle instance pour le nouveau bloc
$block = Vtiger_Block::getInstance('LBL_USERLOGIN_ROLE', $users);


// Add field
$fieldInstance = new Vtiger_Field();
$fieldInstance->name = 'allow_risks';			              //Usually matches column name
$fieldInstance->table = 'vtiger_users';
$fieldInstance->column = 'allow_risks';		                     //Must be lower case
$fieldInstance->label = 'Allow to see risk clients';		            //Upper case preceeded by LBL_
$fieldInstance->columntype = 'VARCHAR(3)';	    //
$fieldInstance->uitype = 156;			                   //Multi-Combo picklist
$fieldInstance->typeofdata = 'V~O';	  //V=Varchar?, M=Mandatory, O=Optional
$block->addField($fieldInstance);

echo 'OK';
?>