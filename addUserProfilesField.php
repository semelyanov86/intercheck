<?php

// Turn on debugging level
$Vtiger_Utils_Log = true;

// Include necessary classes
include_once('vtlib/Vtiger/Module.php');

// Define instances
$users = Vtiger_Module::getInstance('Users');
die;
// Nouvelle instance pour le nouveau bloc
$block = Vtiger_Block::getInstance('LBL_USERLOGIN_ROLE', $users);
// Add field
$fieldInstance = new Vtiger_Field();
$fieldInstance->name = 'user_profiles';			              //Usually matches column name
$fieldInstance->table = 'vtiger_users';
$fieldInstance->column = 'user_profiles';		                     //Must be lower case
$fieldInstance->label = 'Profiles';		            //Upper case preceeded by LBL_
$fieldInstance->columntype = 'TEXT';	    //
$fieldInstance->uitype = 33;			                   //Multi-Combo picklist
$fieldInstance->typeofdata = 'V~O';	  //V=Varchar?, M=Mandatory, O=Optional
$block->addField($fieldInstance);

echo 'OK';
?>