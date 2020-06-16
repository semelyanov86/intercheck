<?php

class ITS4YouReports_Field_Model extends Vtiger_Field_Model {

    static function getPicklistValueByField($fieldName) {
        $picklistValues = false;
        if ($fieldName == 'reporttype') {
            $picklistValues = [
                'tabular' => vtranslate('tabular', 'ITS4YouReports'),
                'summaries' => vtranslate('summaries', 'ITS4YouReports'),
                'summaries_w_details' => vtranslate('summaries_w_details', 'ITS4YouReports'),
                'summaries_matrix' => vtranslate('summaries_matrix', 'ITS4YouReports'),
            ];
            global $current_user;
            if (is_admin($current_user)) {
                $picklistValues['custom_report'] = vtranslate('LBL_CUSTOM_REPORT', 'ITS4YouReports');
            }
        } else if ($fieldName == 'foldername') {
            $allFolders = ITS4YouReports_Folder_Model::getAll();
            foreach ($allFolders as $folder) {
                $picklistValues[$folder->get('folderid')] = vtranslate($folder->get('foldername'), 'ITS4YouReports');
            }
        } else if ($fieldName == 'owner') {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $allUsers = $currentUserModel->getAccessibleUsers();
            foreach ($allUsers as $userId => $userName) {
                $picklistValues[$userId] = $userName;
            }
        } else if ($fieldName == 'tablabel') {
            $reportModel = ITS4YouReports_Record_Model::getCleanInstance();
            $picklistValues = $reportModel->getModulesList();
        }

        return $picklistValues;
    }

    static function getFieldInfoByField($fieldName) {
        $fieldInfo = [
            'mandatory' => false,
            'presence' => true,
            'quickcreate' => false,
            'masseditable' => false,
            'defaultvalue' => false,
        ];
        if ($fieldName == 'reportname') {
            $fieldInfo['type'] = 'string';
            $fieldInfo['name'] = $fieldName;
            $fieldInfo['label'] = 'Report Name';
        } else if ($fieldName == 'description') {
            $fieldInfo['type'] = 'string';
            $fieldInfo['name'] = $fieldName;
            $fieldInfo['label'] = 'Description';
        } else if ($fieldName == 'reporttype') {
            $fieldInfo['type'] = 'picklist';
            $fieldInfo['name'] = $fieldName;
            $fieldInfo['label'] = 'Report Type';
            $fieldInfo['picklistvalues'] = self::getPicklistValueByField($fieldName);
        } else if ($fieldName == 'owner') {
            $fieldInfo['type'] = 'user';
            $fieldInfo['name'] = $fieldName;
            $fieldInfo['label'] = 'LBL_TEMPLATE_OWNER';
            $fieldInfo['picklistvalues'] = self::getPicklistValueByField($fieldName);
        } else if ($fieldName == 'foldername') {
            $fieldInfo['type'] = 'picklist';
            $fieldInfo['name'] = $fieldName;
            $fieldInfo['label'] = 'LBL_FOLDER_NAME';
            $fieldInfo['picklistvalues'] = self::getPicklistValueByField($fieldName);
        } else {
            $fieldInfo = false;
        }

        return $fieldInfo;
    }

    static function getListViewFieldsInfo() {
        $fields = ['reportname', 'reporttype', 'foldername', 'tablabel', 'owner', 'description'];
        $fieldsInfo = [];
        foreach ($fields as $field) {
            $fieldsInfo[$field] = ITS4YouReports_Field_Model::getFieldInfoByField($field);
        }

        return Zend_Json::encode($fieldsInfo);
    }
}

?>
