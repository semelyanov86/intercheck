<?php

class ITS4YouReports_Module_Model extends Vtiger_Module_Model {
    public $licensePermissions = array();
    public function getLicensePermissions($type = 'List') {
        if (empty($this->name)) {
            $this->name = explode('_', get_class($this)) [0];
        }
        $installer = 'ITS4YouInstaller';
        $licenseMode = 'Settings_ITS4YouInstaller_License_Model';
        if (vtlib_isModuleActive($installer)) {
            if (class_exists($licenseMode)) {
                $permission = new $licenseMode();
                $result = $permission->permission($this->name, $type);
                $this->licensePermissions['info'] = $result['errors'];
                return $result['success'];
            } else {
                $this->licensePermissions['errors'] = 'LBL_INSTALLER_UPDATE';
            }
        } else {
            $this->licensePermissions['errors'] = 'LBL_INSTALLER_NOT_ACTIVE';
        }
        return false;
    }
    public function deleteRecord(Vtiger_Record_Model $reportModel) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $subOrdinateUsers = $currentUser->getSubordinateUsers();
        $subOrdinates = [];
        foreach ($subOrdinateUsers as $id => $name) {
            $subOrdinates[] = $id;
        }
        $owner = $reportModel->get('owner');
        if ($currentUser->isAdminUser() || in_array($owner, $subOrdinates) || $owner == $currentUser->getId()) {
            $reportId = $reportModel->getId();
            $db = PearDatabase::getInstance();
            $db->pquery('UPDATE its4you_reports4you SET deleted=1 WHERE reports4youid=?', [$reportId]);
            return true;
        }
        return false;
    }
    public function getSideBarLinks($linkParams) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);
        $quickLinks = [['linktype' => 'SIDEBARLINK', 'linklabel' => 'LBL_REPORTS', 'linkurl' => $this->getListViewUrl(), 'linkicon' => '', ], ];
        foreach ($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }
        $quickS2Links = ['linktype' => "SIDEBARLINK", 'linklabel' => "LBL_KEY_METRICS_WIDGETS", 'linkurl' => "index.php?module=ITS4YouReports&view=KeyMetricsList", 'linkicon' => ''];
        $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickS2Links);
        if ($currentUserModel->isAdminUser()) {
            if ($request->get('view') == "IndexAjax") {
                $quickS2Links = ['linktype' => "SIDEBARWIDGET", 'linklabel' => "LBL_SETTINGS", 'linkurl' => "index.php?module=ITS4YouReports&view=License&parent=Settings", 'linkicon' => ''];
                $links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickS2Links);
            } else {
                $quickS2Links = ['linktype' => "SIDEBARWIDGET", 'linklabel' => "LBL_SETTINGS", 'linkurl' => "module=ITS4YouReports&view=IndexAjax&mode=showSettingsList&pview=" . $linkParams["ACTION"], 'linkicon' => ''];
                $links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickS2Links);
            }
        }
        return $links;
    }
    function getRecentRecords($limit = 10) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_report ORDER BY reportid DESC LIMIT ?', [$limit]);
        $rows = $db->num_rows($result);
        $recentRecords = [];
        for ($i = 0;$i < $rows;++$i) {
            $row = $db->query_result_rowdata($result, $i);
            $recentRecords[$row['reportid']] = $this->getRecordFromArray($row);
        }
        return $recentRecords;
    }
    function getFolders() {
        return ITS4YouReports_Folder_Model::getAll();
    }
    function getAddFolderUrl() {
        return 'index.php?module=' . $this->get('name') . '&view=EditFolder';
    }
    function getReports4You($reportid, $owner, $dateFilter) {
        $db = PearDatabase::getInstance();
        require_once 'modules/ITS4YouReports/ITS4YouReports.php';
        require_once 'modules/ITS4YouReports/GenerateObj.php';
        require_once 'include/Zend/Json.php';
        $report4YouRun = Report4YouRun::getInstance($reportid);
        $data = $report4YouRun->GenerateReport($reportid, 'CHARTS');
        return $data;
    }
    public function isPagingSupported() {
        return true;
    }
    function getReports4YouKeyMetrics($reportid, $column_str) {
        $db = PearDatabase::getInstance();
        require_once 'modules/ITS4YouReports/ITS4YouReports.php';
        require_once 'modules/ITS4YouReports/GenerateObj.php';
        $report4YouRun = Report4YouRun::getIntanceForKeyMetrics($reportid);
        $column_array = explode(":", $column_str);
        $lk = (count($column_array) - 1);
        $report4YouRun->key_metrics_calculation_type = $column_array[$lk];
        $report4YouRun->key_metrics_alias = $column_array[1];
        unset($column_array[$lk]);
        $report4YouRun->key_metrics_columns_str = implode(":", $column_array);
        $data["entries"] = $report4YouRun->GenerateReport($reportid, 'KEYMETRICS');
        $data["rows"] = $report4YouRun->key_metrics_rows;
        return $data;
    }
    public function checkLinkAccess($linkData) {
        $privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $reportModuleModel = Vtiger_Module_Model::getInstance('ITS4YouReports');
        return $privileges->hasModulePermission($reportModuleModel->getId());
    }
    function isStarredEnabled() {
        return false;
    }
    public function getSettingLinks() {
        $settingsLinks = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            $SettingsLinks = ITS4YouReports_ITS4YouReports_Model::GetAvailableSettings();
            foreach ($SettingsLinks as $stype => $sdata) {
                $settingsLinks[] = ['linktype' => 'LISTVIEWSETTING', 'linklabel' => $sdata["label"], 'linkurl' => $sdata["location"], 'linkicon' => ''];
            }
        }
        return $settingsLinks;
    }
} 

?>