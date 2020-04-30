<?php

require_once 'modules/VDUploadField/models/Constant.php';

class VDUploadField_Uninstall_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        global $adb;
        global $vtiger_current_version;
        echo '<div class="container-fluid">' . "\r\n" . '                <div class="widget_header row-fluid">' . "\r\n" . '                    <h3>Advanced Custom Fields</h3>' . "\r\n" . '                </div>' . "\r\n" . '                <hr>';
        $module = Vtiger_Module::getInstance('VDUploadField');

        if ($module) {
            $module->delete();
        }

        $res_template_vlayout = $this->delete_folder('layouts/vlayout/modules/VDUploadField');
        echo '&nbsp;&nbsp;1- Delete VDUploadField vlayout folder';

        if ($res_template_vlayout) {
            echo ' - DONE';
        }
        else {
            echo ' - <b>ERROR</b>';
        }

        echo '<br>';

        if (version_compare($vtiger_current_version, '7.0.0', '>=')) {
            $res_template_v7 = $this->delete_folder('layouts/v7/modules/VDUploadField');
            echo '&nbsp;&nbsp;1- Delete VDUploadField v7 folder';

            if ($res_template_v7) {
                echo ' - DONE';
            }
            else {
                echo ' - <b>ERROR</b>';
            }

            echo '<br>';
        }

        $res_module = $this->delete_folder('modules/VDUploadField');
        echo '&nbsp;&nbsp;2- Delete VDUploadField module folder';

        if ($res_module) {
            echo ' - DONE';
        }
        else {
            echo ' - <b>ERROR</b>';
        }

        echo '<br>';
        $message = $this->removeData();
        echo $message;
        echo 'Module was Uninstalled.<br />';
        echo 'Click <a href=\'index.php?module=ModuleManager&parent=Settings&view=List\'> here </a> to back Module Management screen!';
        echo '</div>';
    }

    public function delete_folder($tmp_path)
    {
        if (!is_writeable($tmp_path) && is_dir($tmp_path) && isFileAccessible($tmp_path)) {
            chmod($tmp_path, 511);
        }

        $handle = opendir($tmp_path);

        while ($tmp = readdir($handle)) {
            if (($tmp != '..') && ($tmp != '.') && ($tmp != '')) {
                if (is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp) && isFileAccessible($tmp_path)) {
                    unlink($tmp_path . DS . $tmp);
                }
                else if (!is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp) && isFileAccessible($tmp_path)) {
                    chmod($tmp_path . DS . $tmp, 438);
                    unlink($tmp_path . DS . $tmp);
                }

                if (is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp) && isFileAccessible($tmp_path)) {
                    $this->delete_folder($tmp_path . DS . $tmp);
                }
                else if (!is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp) && isFileAccessible($tmp_path)) {
                    chmod($tmp_path . DS . $tmp, 511);
                    $this->delete_folder($tmp_path . DS . $tmp);
                }
            }
        }

        closedir($handle);
        rmdir($tmp_path);

        if (!is_dir($tmp_path)) {
            return true;
        }

        return false;
    }

    public function removeData()
    {
        global $adb;
        $message = '';
        $adb->pquery('DELETE FROM `vtiger_eventhandlers` WHERE handler_class=?', array('VDUploadFieldHandler'));
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE `name` = ?', array('Upload Field'));
        $uitype = '';
        $prefix = '';

        foreach (VDUploadField_Constant_Model::$supportedField as $item) {
            if ($uitype != '') {
                $uitype .= ',';
                $prefix .= '\',\'';
            }

            $uitype .= $item['uitype'];
            $prefix .= $item['prefix'];
        }

        $prefix = '\'' . $prefix . '\'';
        $sql = 'SELECT * FROM `vtiger_field` WHERE vtiger_field.uitype in (' . $uitype . ') AND SUBSTRING(`vtiger_field`.fieldname,1,10)IN (' . $prefix . ')';
        $rs = $adb->pquery($sql);

        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($row['fieldid']);

                try {
                    $fieldInstance->delete();
                    $moduleName = $fieldInstance->getModule()->getName();
                    Settings_Workflows_Record_Model::deleteUpadateFieldWorkflow($moduleName, $fieldInstance->getFieldName());
                }
                catch (Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }

        return $message;
    }
}

?>