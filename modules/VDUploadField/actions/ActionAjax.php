<?php

require_once 'modules/VDUploadField/models/Constant.php';

class VDUploadField_ActionAjax_Action  extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return;
    }

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('deleteVDUploadField');
        $this->exposeMethod('validateVDUploadField');
        $this->exposeMethod('ajaxUploadFromForm');
        $this->exposeMethod('downloadFile');
        $this->exposeMethod('removeFile');
        $this->exposeMethod('addField');        
        $this->exposeMethod('getCVIDData');
    }
    
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return NULL;
        }
    }

    public function deleteVDUploadField(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $db = PearDatabase::getInstance();
            $record = $request->get('record');
            $sql = 'SELECT * FROM `vtiger_field`' . "\r\n" . '                       WHERE vtiger_field.fieldid =? ';
            $rs = $db->pquery($sql, array($record));

            if ($row = $db->fetch_row($rs)) {
                $module = Vtiger_Module::getInstance($row['tabid']);
                $field = Vtiger_Field::getInstance($record, $module);
                $field->delete();
            }

            $response->setResult(array('success' => true));
        }
        catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        };
    }

    public function validateVDUploadField(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $name = $request->get('name');
        $module = $request->get('selected_module');
        $name_valid = preg_match('~^[A-Za-z][A-Za-z0-9_]*$~', $name);
        $sql = 'SELECT *,vtiger_tab.tablabel,vtiger_blocks.blocklabel FROM `vtiger_field`' . "\r\n" . '                       INNER JOIN `vtiger_tab` ON  `vtiger_tab`.tabid = `vtiger_field`.tabid' . "\r\n" . '                       INNER JOIN `vtiger_blocks` ON  `vtiger_blocks`.blockid = `vtiger_field`.block' . "\r\n" . '                       WHERE vtiger_field.uitype = 200 AND `vtiger_tab`.name=? AND  `vtiger_field`.columnname=? ';
        $rs = $adb->pquery($sql, array($module, $name));

        if (0 < $adb->num_rows($rs)) {
            $return = array('valid' => false, 'error' => 0);
        }
        else if (!$name_valid) {
            $return = array('valid' => false, 'error' => 1);
        }
        else {
            $return = array('valid' => true);
        }

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($return);
        $response->emit();
    }

    public function ajaxUploadFromForm(Vtiger_Request $request)
    {
        $field_name = $request->get('field_name');
        $fieldModel = Vtiger_Field_Model::getInstance($field_name, Vtiger_Module_Model::getInstance($request->get('parent')));
        $array_uploaded_files = false;

        foreach ($_FILES['upload_' . $field_name]['tmp_name'] as $key => $tmp_name) {
            $FILE = array();
            $FILE['file']['name'] = $_FILES['upload_' . $field_name]['name'][$key];
            $FILE['file']['size'] = $_FILES['upload_' . $field_name]['size'][$key];
            $FILE['file']['tmp_name'] = $_FILES['upload_' . $field_name]['tmp_name'][$key];
            $FILE['file']['type'] = $_FILES['upload_' . $field_name]['type'][$key];
            $array_uploaded_files[] = $this->save($FILE) . '$$' . $FILE['file']['size'] . '$$' . $FILE['file']['type'] . '$$' . $fieldModel->getId();
        }

        $return = array('list_file' => $array_uploaded_files);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($return);
        $response->emit();
    }

    public function save($file)
    {
        $adb = PearDatabase::getInstance();
        $attachid = $adb->getUniqueId('vtiger_crmentity');
        $uploadPath = decideFilePath();
        $fileName = $file['file']['name'];
        $binFile = sanitizeUploadFileName($fileName, vglobal('upload_badext'));
        $fileName = ltrim(basename(' ' . $binFile));
        $path = $uploadPath . $attachid . '_' . $fileName;

        try {
            $tmp_name = $file['file']['tmp_name'];
            move_uploaded_file($tmp_name, $path);
            return $path;
        }
        catch (Exception $e) {
            return $e->getCode();
        }
    }

    public function getFileName($file)
    {
        $arr_file_name = explode('/', $file);
        $name = $arr_file_name[count($arr_file_name) - 1];
        $array_name = explode('_', $name);
        return array('id' => $array_name[0], 'name' => $array_name[1]);
    }

    public function downloadFile(Vtiger_Request $request)
    {
        $filePath = $request->get('file');
        if (!$filePath) {
            $module = $request->get('parent');
            $record = $request->get('record');
            $fieldid = $request->get('fieldid');
            $parentInstance = Vtiger_Record_Model::getInstanceById($record, $module);
            $filePath = $parentInstance->get('cf_vd_ulf_' . $fieldid);
        }

        $arr_file_upload = explode('$$', $filePath);
        $file_name = $this->getFileName($arr_file_upload[0]);
        $file_name = $file_name['name'];
        $fileSize = $arr_file_upload[1];
        $fileSize = $fileSize + ($fileSize % 1024);
        $string = html_entity_decode($arr_file_upload[0]);
        $string = preg_replace("/\s/",'',$string);
        $stringArr = explode('/', $string);
        $fileName = array_pop($stringArr);

        if (fopen($string, 'r')) {
            $fileContent = fread(fopen($string, 'r'), $fileSize);
            header('Content-type: ' . $arr_file_upload[2]);
            header('Pragma: public');
            header('Cache-Control: private');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Content-Description: PHP Generated Data');
        }

        echo $fileContent;
    }

    public function removeFile(Vtiger_Request $request)
    {
        $file_path = $request->get('file_path');
        $parrent_record_id = $request->get('parrent_record_id');
        $field_name = $request->get('field_name');
        $array_file_path = explode('$$', $file_path);
        $file_name = $array_file_path[0];
        $file_info = $this->getFileName($file_name);
        $attachid = $file_info['id'];
        $parent_record_model = Vtiger_Record_Model::getInstanceById($parrent_record_id);
        $parent_record_model->set($field_name, '');
        $parent_record_model->set('mode', 'edit');
        $parent_record_model->save();
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($attachid));
        $related_doc = $adb->pquery('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ? LIMIT 1', array($attachid));

        if (0 < $adb->num_rows($related_doc)) {
            $doc_id = $adb->query_result($related_doc, 'crmid', 0);
            $adb->pquery('DELETE FROM vtiger_notes WHERE notesid = ?', array($doc_id));
        }

        $adb->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($attachid));
        $adb->pquery('DELETE FROM vtiger_crmentity WHERE crmid = ?', array($attachid));
        unlink($file_name);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(true);
        $response->emit();
    }

    public function addField(Vtiger_Request $request)
    {
        $type = $request->get('fieldType');
        $moduleName = $request->get('sourceModule');
        $blockId = $request->get('blockid');
        $moduleModel = VDUploadField_Module_Model::getInstanceByName($moduleName);
        $response = new Vtiger_Response();

        try {
            $fieldModel = $moduleModel->addField($type, $blockId, $request->getAll());
            $fieldInfo = $fieldModel->getFieldInfo();
            $responseData = array_merge(array('id' => $fieldModel->getId(), 'blockid' => $blockId, 'customField' => $fieldModel->isCustomField()), $fieldInfo);
            $response->setResult($responseData);
        }
        catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function getCVIDData(Vtiger_Request $request)
    {
        $cvid = $request->get('cvid');
        $module = $request->get('parent');
        $response = new Vtiger_Response();
        $listModel = Vtiger_ListView_Model::getInstance($module, $cvid);
        $response->setResult($listModel->getListViewHeaders());
        $response->emit();
    }
}

?>