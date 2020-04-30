<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/vtlib/Vtiger/Module.php');

require_once 'modules/VDUsers/vendor/autoload.php';

use Tree\Node\Node;
use Tree\Visitor\PreOrderVisitor;

/**
 * Vtiger Module Model Class
 */
class VDUsers_Module_Model extends Vtiger_Module_Model {

	public static function sortUsersRole($data){
	    $db = PearDatabase::getInstance();
	    $sql = 'SELECT * FROM vtiger_role WHERE depth > 0 ORDER BY parentrole ASC';
	    $result = $db->pquery($sql, array());
	    $numRows = $db->num_rows($result);
	    $rows = array();
	    $role = array();
        for ($i=0; $i<$numRows; $i++){
            $rows[] = $db->query_result_rowdata($result,$i);
        }

        $block = array();
        $block['level2'] = array();
        $sql = "SELECT * FROM vtiger_user2role WHERE roleid =?";
	    foreach ($rows as $row){
            $_uResult = $db->pquery($sql, array($row['roleid']));
            $numRows = $db->num_rows($_uResult);
            $row['users'] = array();
            for ($i=0; $i<$numRows;$i++){
                if (isset($data[$db->query_result($_uResult,$i,'userid')])) {
                    array_push($row['users'], $data[$db->query_result($_uResult, $i, 'userid')]);
                }
            }

	        $level = $row['depth'];
            $block = self::setBlock($level,$row,$block);
            if ($level == 2){
                $block['level2'][]=array('title'=>$row['rolename'], 'roleid'=>$row['roleid']);
            }

        }
        $block = self::checkEmptyBlock($block);

	return $block;

    }
    public static function checkEmptyBlock($block){
	    foreach ($block as $key=>$_block){
	        if ($key == 'level2') continue;
	        if (count($_block['user'])==0){
	            $block[$key]['skip'] = true;
            }
            if (count($_block['parent']) > 0){
	            foreach ($_block['parent'] as $_key=>$parent){
                    if (count($parent['user'])==0 && count($parent['parent'])==0){
                        $block[$key]['parent'][$_key]['skip'] = true;
                    } else if (count($parent['parent']) > 0){
                        $skip = true;
                        foreach ($parent['parent'] as $__key=>$_parent){
                            if (count($_parent['user'])==0) {
                                $block[$key]['parent'][$_key]['parent'][$__key]['skip'] = true;
                            } else {
                                $skip = false;
                            }

                        }
                        if (count($parent['user']) == 0)
                            $block[$key]['parent'][$_key]['skip'] = $skip;
                    }

                }
            }
        }
        return $block;
    }
    public static function setBlock($level,$row,$block){
	    $parentrole = explode('::', $row['parentrole']);

	    if ($level > 1){
	        if (!is_array($block[$parentrole[1]]['parent'])){
                $block[$parentrole[1]]['parent'] = array();
            }
            if ($level > 2){
                if (!is_array($block[$parentrole[1]]['parent'][$parentrole[2]]['parent'])){
                    $block[$parentrole[1]]['parent'][$parentrole[2]]['parent'] = array();
                }
                if ($level > 3){
                    foreach ($row['users'] as $user) {
                        $block[$parentrole[1]]['parent'][$parentrole[2]]['parent'][$parentrole[3]]['user'][] = $user;
                    }
                } else {
                    $block[$parentrole[1]]['parent'][$parentrole[2]]['parent'][$parentrole[3]] = array('name'=>$row['rolename'],'user'=>$row['users']);
                }
            } else {
                $block[$parentrole[1]]['parent'][$parentrole[2]] = array('name'=>$row['rolename'],'user'=>$row['users']);
            }
        } else {
            $block[$parentrole[1]] = array('name'=>$row['rolename'],'user'=>$row['users']);

        }

        return $block;

    }
	
	public static function getRoles($entries, $role = '')
	{
		global $adb, $current_user;
		// Get all roles
		$query = 'select * from vtiger_role order by depth, roleid';
		$result = $adb->pquery($query, array());
		$numRows = $adb->num_rows($result);
	    $roles = array();
		$root = null;
        for ($i = 0; $i < $numRows; ++$i) {
			$row = $adb->query_result_rowdata($result, $i);
			$id = $row['roleid'];
			$value = array(
				'id'    => $id,
				'title' => $row['rolename'],
				'level' => $row['depth'],
				'num'   => 0
			);
			$node = new Node($value);
			$roles[$id] = $node;
			$parents =  explode('::', $row['parentrole']);
			$num = count($parents);
			if ($num > 1) { // not root
				$parentId = $parents[$num - 2];
				$roles[$parentId]->addChild($node);
			}
			else {
				$root = $node;
			}
        }
		// Get users
		$roleList = Settings_Roles_Record_Model::getAll();
		$roleFields = VDUsers_Module_Model::getRoleFields($roleList);
		$params = array();
		$query = 'select * from vtiger_user2role where 1=1';
		$allowedRoles = self::getAllowedRoles($roleFields);
		if ($allowedRoles) {
			$query .= ' and roleid in '.$adb->sql_expr_datalist($allowedRoles);
		}
		if (!empty($role)) {
			$query .= ' and roleid=?';
			$params[] = $role;
		}
		$result = $adb->pquery($query, $params);
		$numRows = $adb->num_rows($result);
		for($i = 0; $i < $numRows; ++$i) {
			$row = $adb->query_result_rowdata($result, $i);
			$userId = $row['userid'];
			$roleId = $row['roleid'];
			if (isset($entries[$userId])) {
				self::addUser($roles[$roleId], $entries[$userId], $roleFields);
			}
		}
		$visitor = new PreOrderVisitor;
		return $root->accept($visitor);
	}
	
	// Get role picklist
	
	public static function getPicklist($roles)
	{
		$rolesArray = array();
		foreach($roles as $key => $value) {
			$rolesArray[$key] = $value->getName();
		}
		return $rolesArray;
	}
	
	// Get role fields
	
	public static function getRoleFields($roles)
	{
		global $adb;
		$roleFileds = array();
		$picklist =  self::getPicklist($roles);
		$result  = $adb->pquery('SELECT * FROM vtiger_vdusers_roles', array());
		$numRows = $adb->num_rows($result);
		for($i = 0; $i < $numRows; ++$i) {
			$row = $adb->query_result_rowdata($result, $i);
			$roleid = $row['roleid'];
			$roleFiled = VDUsers_RoleField_Model::getInstance($picklist);
			$roleFiled->set('name', $roleid);
			$roleFiled->set('label', $roles[$roleid]->getName());
			$roleFiled->set('fieldvalue', $row['roles']);
			$roleFileds[$roleid] = $roleFiled;
		}
		return $roleFileds;
	}
	public static function getEditUsers(){
        global $current_user,$adb;

        if ($current_user->column_fields['is_admin'] == 'on') return 1;
        $roleid = $current_user->column_fields['roleid'];
        $select = "SELECT * FROM vtiger_vdusers_roles WHERE roleid = '$roleid' and edit = 1";
        $result = $adb->pquery($select);
       // print_r ($select);  print_r ($result); die;
        if ($adb->num_rows($result) > 0){
            return 1;
        } else {
            return 0;
        }
    }
	
	public static function getAllowedRoles($roleFields)
	{
		global $current_user;
		if (isset($roleFields[$current_user->roleid])) {
			return explode(' |##| ', $roleFields[$current_user->roleid]->get('fieldvalue'));
		}
		else {
			return null;
		}
	}
	
	private static function addUser($node, $user, $roleFields = array())
	{
		$data = $node->getValue();
		if (!isset($data['users'])) {
			$data['users'] = array();
		}
		$data['users'][] = $user;
		$data['num']++;
		$node->setValue($data);
		$ancestors = $node->getAncestors();
		foreach($ancestors as $ancestor) {
			$data = $ancestor->getValue();
			$data['num']++;
			$ancestor->setValue($data);
		}
		return $node;
	}
	
	public function isQuickCreateSupported(){
	return false;
	}
}
