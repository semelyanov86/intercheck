<?php

class Webapp_WS_FetchModuleOwners extends Webapp_WS_Controller {

    function process(Webapp_API_Request $request) {
        global $current_user;
        
        $response = new Webapp_API_Response();
        $current_user = $this->getActiveUser();
        
        $currentUserModel = Users_Record_Model::getInstanceFromUserObject($current_user);
        
        $moduleName = trim($request->get('module'));
        $users = $this->getUsers($currentUserModel, $moduleName);
        $groups = $this->getGroups($currentUserModel, $moduleName);
        
        $result = array('users' => $users, 'groups' => $groups);
        $response->setResult($result);
        
        return $response;
    }

    function getUsers($currentUserModel, $moduleName) {
        $users = $currentUserModel->getAccessibleUsersForModule($moduleName);
        $userIds = array_keys($users);
        $usersList = array();
        $usersWSId = Webapp_WS_Utils::getEntityModuleWSId('Users');
        foreach ($userIds as $userId) {
            $userRecord = Users_Record_Model::getInstanceById($userId, 'Users');
            $usersList[] = array('value' => $usersWSId . 'x' . $userId,
                                 'label' => $userRecord->get("first_name") . ' ' . $userRecord->get('last_name')
                                );
        }
        return $usersList;
    }

    function getGroups($currentUserModel, $moduleName) {
        $groups = $currentUserModel->getAccessibleGroupForModule($moduleName);
        $groupIds = array_keys($groups);
        $groupsList = array();
        $groupsWSId = Webapp_WS_Utils::getEntityModuleWSId('Groups');
        foreach ($groupIds as $groupId) {
            $groupName = getGroupName($groupId);
            $groupsList[] = array('value' => $groupsWSId . 'x' . $groupId,
                                  'label' => $groupName[0]
                                 );
        }
        return $groupsList;
    }
}

