<?php

error_reporting(0);

class ITS4YouReports_Folder_Model extends Vtiger_Base_Model {

    /**
     * Function to get the id of the folder
     * @return <Number>
     */
    function getId() {
        return $this->get('folderid');
    }

    /**
     * Function to set the if for the folder
     * @param <Number>
     */
    function setId($value) {
        $this->set('folderid', $value);
    }

    /**
     * Function to get the name of the folder
     * @return <String>
     */
    function getName() {
        return $this->get('foldername');
    }

    /**
     * Function returns the instance of Folder model
     * @return <ITS4YouReports_Folder_Model>
     */
    public static function getInstance() {
        return new ITS4YouReports_Folder_Model();
    }

    /**
     * Function saves the folder
     */
    function save() {
        $db = PearDatabase::getInstance();

        $folderId = $this->getId();
        if (!empty($folderId)) {
            $db->pquery('UPDATE its4you_reports4you_folder SET foldername = ?, description = ? WHERE folderid = ?', array($this->getName(), $this->getDescription(), $folderId));
        } else {
            $result = $db->pquery('SELECT MAX(folderid) AS folderid FROM its4you_reports4you_folder', array());
            $folderId = (int) ($db->query_result($result, 0, 'folderid')) + 1;
            $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
            $db->pquery('INSERT INTO its4you_reports4you_folder(folderid, foldername, description, state, ownerid) VALUES(?, ?, ?, ?, ?)', array($folderId, $this->getName(), $this->getDescription(), 'CUSTOMIZED', $currentUserModel->get('id')));
            $this->set('folderid', $folderId);
        }
    }

    /**
     * Function deletes the folder
     */
    function delete() {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM its4you_reports4you_folder WHERE folderid = ?', array($this->getId()));
    }

    /**
     * Function returns Report Models for the folder
     * @param <Vtiger_Paging_Model> $pagingModel
     * @return <ITS4YouReports_Record_Model>
     */
    function getReports($pagingModel,$search_params=array()) {

        $paramsList = array('startIndex' => $pagingModel->getStartIndex(),
            'pageLimit' => $pagingModel->getPageLimit(),
            'orderBy' => $this->get('orderby'),
            'sortBy' => $this->get('sortby'));

        $fldrId = $this->getId();

        if ($fldrId == 'All') {
            $fldrId = false;
            $paramsList = array('startIndex' => $pagingModel->getStartIndex(),
                'pageLimit' => $pagingModel->getPageLimit(),
                'orderBy' => $this->get('orderby'),
                'sortBy' => $this->get('sortby')
            );
        }

        //global $adb;$adb->setDebug(true);
        $reportsList = ITS4YouReports::sgetRptsforFldr($fldrId, $paramsList,$search_params);
        //$adb->setDebug(false);

        $reportModuleModel = Vtiger_Module_Model::getInstance('ITS4YouReports');

        if ($fldrId == false) {
            return $this->getAllReportModels($reportsList, $reportModuleModel);
        } else {
            $reportModels = array();
            for ($i = 0; $i < count($reportsList); $i++) {
                $reportModel = new ITS4YouReports_Record_Model();

                $reportModel->setData($reportsList[$i])->setModuleFromInstance($reportModuleModel);
                $reportModels[] = $reportModel;
                unset($reportModel);
            }
            return $reportModels;
        }
    }

    /**
     * Function to get the description of the folder
     * @return <String>
     */
    function getDescription() {
        return $this->get('description');
    }

    /**
     * Function to get the url for edit folder from list view of the module
     * @return <string> - url
     */
    function getEditUrl() {
        return 'index.php?module=ITS4YouReports&view=EditFolder&folderid=' . $this->getId();
    }

    /**
     * Function to get the url for delete folder from list view of the module
     * @return <string> - url
     */
    function getDeleteUrl() {
        return 'index.php?module=ITS4YouReports&action=Folder&mode=delete&folderid=' . $this->getId();
    }

    /**
     * Function returns the instance of Folder model
     * @param FolderId
     * @return <Reports_Folder_Model>
     */
    public static function getInstanceById($folderId) {
        $folderModel = Vtiger_Cache::get('reportsFolder', $folderId);
        if (!$folderModel) {
            $db = PearDatabase::getInstance();
            $folderModel = ITS4YouReports_Folder_Model::getInstance();

            $result = $db->pquery("SELECT * FROM its4you_reports4you_folder WHERE folderid = ?", array($folderId));

            if ($db->num_rows($result) > 0) {
                $values = $db->query_result_rowdata($result, 0);
                $folderModel->setData($values);
            }
            Vtiger_Cache::set('reportsFolder', $folderId, $folderModel);
        }
        return $folderModel;
    }

    /**
     * Function returns the instance of Folder model
     * @return <Reports_Folder_Model>
     */
    public static function getAll() {
        $db = PearDatabase::getInstance();
        $folders = Vtiger_Cache::get('reports', 'folders');
        if (!$folders) {
            $folders = array();
            $result = $db->pquery("SELECT * FROM its4you_reports4you_folder ORDER BY foldername ASC", array());
            $noOfFolders = $db->num_rows($result);
            if ($noOfFolders > 0) {
                for ($i = 0; $i < $noOfFolders; $i++) {
                    $folderModel = ITS4YouReports_Folder_Model::getInstance();
                    $values = $db->query_result_rowdata($result, $i);
                    $folders[$values['folderid']] = $folderModel->setData($values);
                    Vtiger_Cache::set('reportsFolder', $values['folderid'], $folderModel);
                }
            }
            Vtiger_Cache::set('reports', 'folders', $folders);
        }
        return $folders;
    }

    /**
     * Function returns duplicate record status of the module
     * @return true if duplicate records exists else false
     */
    function checkDuplicate() {
        $db = PearDatabase::getInstance();

        $query = 'SELECT 1 FROM its4you_reports4you_folder WHERE foldername = ?';
        $params = array($this->getName());

        $folderId = $this->getId();
        if ($folderId) {
            $query .= ' AND folderid != ?';
            array_push($params, $folderId);
        }

        $result = $db->pquery($query, $params);

        if ($db->num_rows($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Function returns whether reports are exist or not in this folder
     * @return true if exists else false
     */
    function hasReports() {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT 1 FROM its4you_reports4you WHERE folderid = ?', array($this->getId()));

        if ($db->num_rows($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Function returns whether folder is Default or not
     * @return true if it is read only else false
     */
    function isDefault() {
        if ($this->get('state') == 'SAVED') {
            return true;
        }
        return false;
    }

    /**
     * Function to get info array while saving a folder
     * @return Array  info array
     */
    public function getInfoArray() {
        return array('folderId' => $this->getId(),
            'folderName' => $this->getName(),
            'editURL' => $this->getEditUrl(),
            'deleteURL' => $this->getDeleteUrl(),
            'isEditable' => $this->isEditable(),
            'isDeletable' => $this->isDeletable());
    }

    /**
     * Function to check whether folder is editable or not
     * @return <boolean>
     */
    public function isEditable() {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if ($currentUserModel->isAdminUser() || $currentUserModel->getId() === $this->get('ownerid')) {
            return true;
        }

        return false;
    }

    /**
     * Function to get check whether folder is deletable or not
     * @return <boolean>
     */
    public function isDeletable() {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if ($currentUserModel->isAdminUser() || $currentUserModel->getId() === $this->get('ownerid')) {
            return true;
        }

        return false;
    }

    /**
     * Function to calculate number of reports in this folder
     * @return <Integer>
     */
    public function getReportsCount() {
        $db = PearDatabase::getInstance();
        $params = array();

        $sql = "SELECT count(*) AS count FROM its4you_reports4you
                    INNER JOIN its4you_reports4you_settings ON  its4you_reports4you_settings.reportid = its4you_reports4you.reports4youid
                    INNER JOIN its4you_reports4you_folder ON  its4you_reports4you_folder.folderid = its4you_reports4you.folderid
                     WHERE its4you_reports4you.deleted=0";
        $fldrId = $this->getId();
        if ($fldrId == 'All') {
            $fldrId = false;
        }

        // If information is required only for specific report folder?
        if ($fldrId !== false) {
            $sql .= " AND its4you_reports4you_folder.folderid=?";
            array_push($params, $fldrId);
        }
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserModel->isAdminUser()) {
            $currentUserId = $currentUserModel->getId();

            $groupId = implode(',', $currentUserModel->get('groups'));
            if ($groupId) {
                $groupQuery = "( shareid IN ($groupId) AND setype = 'groups') OR ";
            }

            $sql .= " AND (its4you_reports4you.reports4youid IN (SELECT reports4youid from its4you_reports4you_sharing WHERE $groupQuery (shareid = ? AND setype = 'users'))
						OR its4you_reports4you_settings.sharingtype = 'Public'
						OR its4you_reports4you_settings.owner = ?
						OR its4you_reports4you_settings.owner IN (SELECT vtiger_user2role.userid FROM vtiger_user2role
													INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
													INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
													WHERE vtiger_role.parentrole LIKE ?))";

            $parentRoleSeq = $currentUserModel->get('parent_role_seq') . '::%';
            array_push($params, $currentUserId, $currentUserId, $parentRoleSeq);
        }
        $result = $db->pquery($sql, $params);
        return $db->query_result($result, 0, 'count');
    }

    /**
     * Function to get all Report Record Models
     * @param <Array> $allReportsList
     * @param <Vtiger_Module_Model> - Reports Module Model
     * @return <Array> Reports Record Models
     */
    private function getAllReportModels($allReportsList, $reportModuleModel) {
        $allReportModels = array();

        foreach ($allReportsList as $key => $reportsList) {
            for ($i = 0; $i < count($reportsList); $i++) {
                $reportModel = Vtiger_Record_Model::getCleanInstance('ITS4YouReports');
                $reportModel->setData($reportsList[$i])->setModuleFromInstance($reportModuleModel);
                $reportModel->setId($reportModel->get('reportid'));
                $allReportModels[] = $reportModel;
                unset($reportModel);
            }
        }

        return $allReportModels;
    }

    /**
     * Function which provides the records for the current view
     * @param <Boolean> $skipRecords - List of the RecordIds to be skipped
     * @return <Array> List of RecordsIds
     */
    public function getRecordIds($skipRecords = false, $module) {
        $db = PearDatabase::getInstance();
        $baseTableName = "vtiger_report";
        $baseTableId = "reportid";
        $folderId = $this->getId();
        $listQuery = $this->getListViewQuery($folderId);

        if ($skipRecords && !empty($skipRecords) && is_array($skipRecords) && count($skipRecords) > 0) {
            $listQuery .= ' AND ' . $baseTableName . '.' . $baseTableId . ' NOT IN (' . implode(',', $skipRecords) . ')';
        }
        $result = $db->query($listQuery);
        $noOfRecords = $db->num_rows($result);
        $recordIds = array();
        for ($i = 0; $i < $noOfRecords; ++$i) {
            $recordIds[] = $db->query_result($result, $i, $baseTableId);
        }
        return $recordIds;
    }

}
