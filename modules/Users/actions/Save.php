<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_Save_Action extends Vtiger_Save_Action {

    public function requiresPermission(\Vtiger_Request $request) {
        return array();
    }
    
	public function checkPermission(Vtiger_Request $request) {
        global $allowedUserSettings;
		$allowed = parent::checkPermission($request);
		if ($allowed) {
			$moduleName = $request->getModule();
			$record = $request->get('record');
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			// Deny access if not administrator or account-owner or self
			if(!$currentUserModel->isAdminUser()) {
			    if (!in_array($currentUserModel->getId(), $allowedUserSettings)) {
                    if (empty($record)) {
                        $allowed = false;
                    } else if (($currentUserModel->get('id') != $recordModel->getId())) {
                        $allowed = false;
                    }
                }
			}
		}
		if(!$allowed) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
		return $allowed;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$sharedType = $request->get('sharedtype');
			if(!empty($sharedType))
				$recordModel->set('calendarsharedtype', $request->get('sharedtype'));
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		foreach ($modelData as $fieldName => $value) {
			$requestFieldExists = $request->has($fieldName);
			if(!$requestFieldExists){
				continue;
			}
			$fieldValue = $request->get($fieldName, null);
			if ($fieldName === 'is_admin' && (!$currentUserModel->isAdminUser() || !$fieldValue)) {
				$fieldValue = 'off';
			}
			//to not update is_owner from ui
			if ($fieldName == 'is_owner') {
				$fieldValue = null;
			}
			if ($fieldName == 'roleid' && !($currentUserModel->isAdminUser())) {
				$fieldValue = null;
			}
			if($fieldName == 'group_view' && !($currentUserModel->isAdminUser())) {
			    $fieldValue = null;
            }
            if($fieldName == 'user_groups' && !($currentUserModel->isAdminUser())) {
                $fieldValue = null;
            }
            if($fieldName == 'user_profiles' && !($currentUserModel->isAdminUser())) {
                $fieldValue = null;
            }

			if($fieldValue !== null) {
				if(!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		$homePageComponents = $recordModel->getHomePageComponents();
		$selectedHomePageComponents = $request->get('homepage_components', array());
		foreach ($homePageComponents as $key => $value) {
			if(in_array($key, $selectedHomePageComponents)) {
				$request->setGlobal($key, $key);
			} else {
				$request->setGlobal($key, '');
			}
		}
		if($request->has('tagcloudview')) {
			// Tag cloud save
			$tagCloud = $request->get('tagcloudview');
			if($tagCloud == "on") {
				$recordModel->set('tagcloud', 0);
			} else {
				$recordModel->set('tagcloud', 1);
			}
		}
		return $recordModel;
	}

	public function process(Vtiger_Request $request) {
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if ($request->get('is_admin') && $currentUser->getId() != '1') {
            throw new AppException('Only Super Admin can create new admin users');
        }
        if ($request->get('roleid') == 'H2' && $currentUser->getId() != '1') {
            throw new AppException('Only Super Admin can create users with admin role');
        }

		$recordId = $request->get('record');
		if (!$recordId) {
			$module = $request->getModule();
			$userName = $request->get('user_name');
			$userModuleModel = Users_Module_Model::getCleanInstance($module);
			$status = $userModuleModel->checkDuplicateUser($userName);
			if ($status == true) {
				throw new AppException(vtranslate('LBL_DUPLICATE_USER_EXISTS', $module));
			}
		}
		if ($currentUser->isAdminUser()) {
            $roleId = $this->generateProfiles($request->get('user_profiles'));
            if ($roleId) {
                $request->set('roleid', $roleId);
            }
        }
		$recordModel = $this->saveRecord($request);
		if ($currentUser->isAdminUser()) {
            $this->removeFromGroups($recordModel);
            $this->addToGroups($recordModel);
            $this->generatePermissions($recordModel);
        }
		if ($request->get('relationOperation')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('isPreference')) {
			$loadUrl =  $recordModel->getPreferenceDetailViewUrl();
		} else if ($request->get('returnmodule') && $request->get('returnview')){
			$loadUrl = 'index.php?'.$request->getReturnURL();
		} else if($request->get('mode') == 'Calendar'){
			$loadUrl = $recordModel->getCalendarSettingsDetailViewUrl();
		}else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}

		header("Location: $loadUrl");
	}
	public function generatePermissions(Users_Record_Model $userModel, $onlySelected = array())
    {
        $modules = Vtiger_Module_Model::getEntityModules();
        if (empty($onlySelected)) {
            if ($userModel->get('all_groups')) {
                $groups = array();
                $groupModels = Settings_Groups_Record_Model::getAll();
                foreach ($groupModels as $groupModel) {
                    if ($groupModel->getId() == 24) {
                        continue;
                    }
                    $groups[] = $groupModel->getId();
                }
            } else {
                if ($userModel->get('group_view')) {
                    $groups = Users_Record_Model::getUserGroups($userModel->getId());
                } else {
                    $groups = array();
                }
                $this->removeOldPermissions($userModel);
            }
        } else {
            $groups = array();
            foreach ($onlySelected as $groupModel) {
                $groups[] = $groupModel->getId();
            }
        }

        foreach ($modules as $module) {
            foreach ($groups as $group) {
                $ruleModel = Settings_SharingAccess_Rule_Model::getInstanceByData($module, $group, $userModel->getId());
                if (!$ruleModel) {
                    $ruleModel = new Settings_SharingAccess_Rule_Model();
                    $ruleModel->setModuleFromInstance($module);
                }
                $ruleModel->set('source_id', 'Groups:' . $group);
                $ruleModel->set('target_id', 'Users:' . $userModel->getId());
                $ruleModel->set('permission', 1);
                $ruleModel->save(false);
            }
        }
        Settings_SharingAccess_Module_Model::recalculateSharingRules();
    }
    public function removeOldPermissions(Users_Record_Model $user)
    {
        $userRule = 'Users:' . $user->getId();
        $modules = Vtiger_Module_Model::getEntityModules();
        foreach ($modules as $module) {
            if(!$module || !$module->isActive()) {
                continue;
            }
            $rules = Settings_SharingAccess_Rule_Model::getAllByModule($module);
            foreach ($rules as $rule) {
                if ($rule && $rule->getTargetMember()->getId() == $userRule) {
                    $rule->delete(false);
                }
            }
        }
        Settings_SharingAccess_Module_Model::recalculateSharingRules();
    }
    public function addToGroups(Users_Record_Model $recordModel)
    {
        $groups = $recordModel->get('user_groups');
        if ($groups && !empty($groups)) {
            foreach ($groups as $group) {
                $groupModel = Settings_Groups_Record_Model::getInstance($group);
                $members = $groupModel->getMembers();
                $userValue = 'Users:' . $recordModel->getId();
                $oldKeys = array_keys($members['Users']);
                if (!in_array($userValue, $oldKeys)) {
                    $oldKeys[] = $userValue;
                    $oldKeys = $this->pluckMembers($oldKeys, $members, 'Groups');
                    $oldKeys = $this->pluckMembers($oldKeys, $members, 'Roles');
                    $oldKeys = $this->pluckMembers($oldKeys, $members, 'RoleAndSubordinates');
                    $groupModel->set('group_members', $oldKeys);
                    $groupModel->save();
                }

            }
        }
    }
    public function removeFromGroups(Users_Record_Model $record_Model)
    {
        $allGroups = Settings_Groups_Record_Model::getAll();
        if (true) {
            if ($record_Model->has('user_groups')) {
                $curGroups = $record_Model->get('user_groups');
            } else {
                $curGroups = array();
            }
            foreach ($allGroups as $id=>$groupModel) {
                if (in_array($id, $curGroups) || $id == 24) {
                    continue;
                }
                $members = $groupModel->getMembers();
                $userValue = 'Users:' . $record_Model->getId();
                $keys = array_keys($members['Users']);
                $keys = array_diff($keys, [$userValue]);
                $keys = $this->pluckMembers($keys, $members, 'Groups');
                $keys = $this->pluckMembers($keys, $members, 'Roles');
                $keys = $this->pluckMembers($keys, $members, 'RoleAndSubordinates');
                $newKeys = array();
                foreach ($keys as $key) {
                    $newKeys[] = $key;
                }
                $groupModel->set('group_members', $newKeys);
                $groupModel->save();
            }
        }
    }
    private function pluckMembers($oldKeys, $members, $name)
    {
        $oldGroups = array_keys($members[$name]);
        if (!empty($oldGroups)) {
            foreach ($oldGroups as $oldgroup) {
                $oldKeys[] = $oldgroup;
            }
        }
        return $oldKeys;
    }
    public function generateProfiles($profiles)
    {
        global $adb;
        if($profiles && !empty($profiles)) {
            $query = "SELECT roleid, GROUP_CONCAT(profileid) AS `profiles` FROM vtiger_role2profile GROUP BY roleid";
            $res = $adb->pquery($query, array());
            $mappedProfiles = array();
            if (0 < $adb->num_rows($res)) {
                while ($row = $adb->fetchByAssoc($res)) {
                    $mappedProfiles[$row["roleid"]] = explode(',', $row["profiles"]);
                }
            }
            foreach($mappedProfiles as $role=>$mapProfiles) {
                if ($this->arrayEqual($profiles, $mapProfiles)) {
                    return $role;
                }
            }
            $role = $this->createRoleFromProfiles($profiles);
            return $role;
        }
    }
    public function arrayEqual($a, $b) {
        return (
            is_array($a)
            && is_array($b)
            && count($a) == count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }
    public function createRoleFromProfiles(array $profiles)
    {
        if ($profiles && !empty($profiles)) {
            $roleName = '';
            foreach ($profiles as $profileId) {
                $profileModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
                $roleName .= $profileModel->getName() . '+';
            }
            $roleName = trim($roleName, '+');
            $recordModel = new Settings_Roles_Record_Model();
            $parentRoleId = 'H2';
            if($recordModel && !empty($parentRoleId)) {
                $parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
                if($parentRole && !empty($roleName) && !empty($profiles)) {
                    $recordModel->set('rolename', $roleName);
                    $recordModel->set('profileIds', $profiles);
                    $recordModel->set('allowassignedrecordsto', 1);
                    $roleModel = $parentRole->addChildRole($recordModel);
                    if ($profiles) {
                        foreach ($profiles as $profileId) {
                            $profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
                            $profileRecordModel->recalculate(array($roleModel->getId()));
                        }
                    }
                    return $roleModel->getId();
                }
            }
        } else {
            return false;
        }
    }
}
