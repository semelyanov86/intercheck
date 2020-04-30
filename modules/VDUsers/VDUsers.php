<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

class VDUsers extends Users {

    var $log;
    /**
     * @var PearDatabase
     */
    var $db;
    // Stored fields
    var $id;
    var $authenticated = false;
    var $error_string;
    var $is_admin;
    var $deleted;

    var $tab_name = Array('vtiger_users','vtiger_attachments','vtiger_user2role','vtiger_asteriskextensions');
    var $tab_name_index = Array('vtiger_users'=>'id','vtiger_attachments'=>'attachmentsid','vtiger_user2role'=>'userid','vtiger_asteriskextensions'=>'userid');

    var $table_name = "vtiger_users";
    var $table_index= 'id';

    // This is the list of fields that are in the lists.
    var $list_link_field= 'last_name';

    var $list_mode;
    var $popup_type;

    var $search_fields = Array(
        'Name'=>Array('vtiger_users'=>'last_name'),
        'Email'=>Array('vtiger_users'=>'email1'),
        'Email2'=>Array('vtiger_users'=>'email2')
    );
    var $search_fields_name = Array(
        'Name'=>'last_name',
        'Email'=>'email1',
        'Email2'=>'email2'
    );

    var $module_name = "Users";

    var $object_name = "User";
    var $user_preferences;
    var $homeorder_array = array('HDB','ALVT','PLVT','QLTQ','CVLVT','HLT','GRT','OLTSO','ILTI','MNL','OLTPO','LTFAQ', 'UA', 'PA');

    var $encodeFields = Array("first_name", "last_name", "description");

    // This is used to retrieve related fields from form posts.
    var $additional_column_fields = Array('reports_to_name');

    var $sortby_fields = Array('status','email1','email2','phone_work','is_admin','user_name','last_name');

    // This is the list of vtiger_fields that are in the lists.
    var $list_fields = Array(
        'First Name'=>Array('vtiger_users'=>'first_name'),
        'Last Name'=>Array('vtiger_users'=>'last_name'),
       // 'Role Name'=>Array('vtiger_user2role'=>'roleid'),
       // 'User Name'=>Array('vtiger_users'=>'user_name'),
       // 'Status'=>Array('vtiger_users'=>'status'),
        'Email'=>Array('vtiger_users'=>'email1'),
     //   'Email2'=>Array('vtiger_users'=>'email2'),
      //  'Admin'=>Array('vtiger_users'=>'is_admin'),
        'Phone'=>Array('vtiger_users'=>'phone_work'),
        'Phone'=>Array('vtiger_users'=>'phone_crm_extension'),
        'Title'=>Array('vtiger_users'=>'title'),
    );
    var $list_fields_name = Array(
        'Last Name'=>'last_name',
        'First Name'=>'first_name',
       // 'Role Name'=>'roleid',
      //  'User Name'=>'user_name',
      //  'Status'=>'status',
        'Mobile'=>'phone_mobile',
      //  'Home Phone'=>'phone_home',
        'Office Phone'=>'phone_work',
        'CRM Phone'=>'phone_crm_extension',

        'Email'=>'email1',
      //  'Email2'=>'email2',
        'Title'=>'title'
       // 'Admin'=>'is_admin',

    );

    //Default Fields for Email Templates -- Pavani
    var $emailTemplate_defaultFields = array('first_name','last_name','title','department','phone_home','phone_mobile','signature','email1','email2','address_street','address_city','address_state','address_country','address_postalcode');

    var $popup_fields = array('last_name');

    // This is the list of fields that are in the lists.
    var $default_order_by = "user_name";
    var $default_sort_order = 'ASC';

    var $record_id;
    var $new_schema = true;

    var $DEFAULT_PASSWORD_CRYPT_TYPE; //'BLOWFISH', /* before PHP5.3*/ MD5;

    //Default Widgests
    var $default_widgets = array('PLVT', 'CVLVT', 'UA');

    /** constructor function for the main user class
    instantiates the Logger class and PearDatabase Class
     *
     */
	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
            self::createSettingField();
            self::createWSEntity();
			// TODO Handle actions after this module is installed.
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
            self::removeSettings();
            self::deleteFolders();
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}
	static function createSettingField() {
        global $adb;
        $sql = "set @lastfieldid = (select `id` from `vtiger_settings_field_seq`);";
        $adb->pquery($sql,array());
        $sql = "set @blockid = (select `blockid` from `vtiger_settings_blocks` where `label` = 'LBL_OTHER_SETTINGS');";
        $adb->pquery($sql,array());
        $sql = "set @maxseq = (select max(`sequence`) from `vtiger_settings_field` where `blockid` = @blockid);";
        $adb->pquery($sql,array());
        $sql = "INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `iconpath`, `description`, `linkto`, `sequence`, `active`) "
            . " VALUES (@lastfieldid+1, @blockid, 'Users list', '', 'LBL_VDUSERS', 'index.php?module=VDUsers&parent=Settings&view=List', @maxseq+1, 1);";
        $adb->pquery($sql,array());
        $sql = "UPDATE `vtiger_settings_field_seq` SET `id` = @lastfieldid+1;";
        $adb->pquery($sql,array());
    }
    
    static function createWSEntity()
    {
        global $adb;
        $max_id=$adb->getUniqueID('vtiger_ws_entity');
        $params = array(
            $max_id,
            'VDUsers',
            'include/Webservices/VtigerModuleOperation.php',
            'VtigerModuleOperation',
            1
        );
        $sql = "insert into vtiger_ws_entity (id, name, handler_path, handler_class, ismodule) values (?,?,?,?,?)";
        $adb->pquery($sql, $params);
    }
    
    static function removeSettings()
    {
        global $adb;
        $query1 = 'delete from vtiger_settings_field where name=? and description=?'; // [Users list, LBL_VDUSERS]
        $query2 = 'delete from vtiger_ws_entity where name=?'; // [VDUsers]
        $query3 = 'select * from vtiger_customview where viewname=?'; // [VDUsers]
        
        $adb->pquery($query1, array('Users list', 'LBL_VDUSERS'));
        Vtiger_Utils::Log("Delete from vtiger_settings_field ... DONE", true);
        $adb->pquery($query2, array('VDUsers'));
        Vtiger_Utils::Log("Delete from vtiger_ws_entity ... DONE", true);
        
        $cvResult = $adb->pquery($query3, array('VDUsers'));
        $cVNum = $adb->num_rows($cvResult);
        if ($cVNum > 0) {
            $cv = $adb->query_result_rowdata($cvResult, 0);
            $cvId = $cv['cvid'];
            $cvModel = CustomView_Record_Model::getInstanceById($cvId);
            $cvModel->delete();
            Vtiger_Utils::Log("Delete custom view ... DONE", true);
        }
    }
    
    static function deleteFolders()
	{
		$result = self::_deleteFolder('layouts/vlayout/modules/VDUsers');
		if ($result) Vtiger_Utils::Log("Delete folder layouts/vlayout/modules/VDUsers ... DONE", true);
		$result = self::_deleteFolder('modules/VDUsers');
		if ($result) Vtiger_Utils::Log("Delete folder modules/VDUsers ... DONE", true);
	}
	
	private static function _deleteFolder($dir)
	{
        $entities = array_diff(\scandir($dir), array('.','..'));
		foreach($entities as $entity) {
			$realpath = $dir.DIRECTORY_SEPARATOR.$entity;
			if (is_dir($realpath)) self::_deleteFolder($realpath);
			else unlink($realpath);
		}
		return rmdir($dir);
    }
}
