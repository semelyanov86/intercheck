<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName => $fieldModel) {
					//Is Admin and Status fields are Ajax editable when the record user != current user
					if (in_array($fieldModel->get('uitype'), array(156, 115)) && $currentUserModel->getId() !== $recordId) {
						$fieldModel->set('editable', true);
						if ($fieldModel->get('uitype') == 156) {
							$fieldValue = false;
							if ($recordModel->get($fieldName) === 'on') {
								$fieldValue = true;
							}
							$recordModel->set($fieldName, $fieldValue);
						}
					}
					if($fieldModel->isViewableInDetailView()) {
						if($recordId) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
                            if ($fieldName == 'user_groups') {
                                $groups = Users_Record_Model::getUserGroups($recordId);
                                $groupsName = array();
                                foreach ($groups as $group) {
                                    $groupModel = Settings_Groups_Record_Model::getInstanceById($group);
                                    if ($groupModel) {
                                        $groupsName[] = $groupModel->getName();
                                    }
                                }
                                $fieldModel->set('fieldvalue', implode(' |##| ', $groupsName));
                            } elseif ($fieldName == 'user_profiles') {
                                $roleId = $recordModel->getRole();
                                $profilesName = array();
                                $roleModel = Settings_Roles_Record_Model::getInstanceById($roleId);
                                if ($roleModel) {
                                    $profiles = $roleModel->getProfiles();
                                    foreach($profiles as $profile) {
                                        $profilesName[$profile->getId()] = $profile->getName();
                                    }
                                }
                                $fieldModel->set('fieldvalue', implode(' |##| ', $profilesName));
                            }
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}