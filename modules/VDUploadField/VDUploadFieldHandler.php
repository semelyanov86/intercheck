<?php

require_once 'include/events/VTEventHandler.inc';
require_once("include/Zend/Json.php");
require_once 'modules/VDUploadField/models/Constant.php';

class VDUploadFieldHandler  extends VTEventHandler
{
    public function handleEvent($eventName, $data)
    {
        $adb = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $rowModified = $data->focus->column_fields;
        $rowId = $data->focus->id;
        $moduleName = $data->getModuleName();
        $vmodule = Vtiger_Module::getInstance($moduleName);

        if ($eventName == 'vtiger.entity.aftersave') {
            $documentModuleName = 'Documents';

            foreach ($rowModified as $key => $field_value) {
                if (strpos($key, VDUploadField_Constant_Model::$supportedField['Upload_Field']['prefix']) !== false) {
                    if (empty($field_value)) {
                        continue;
                    } else {
                        continue;
                    }

                    $list_files = explode(',', $field_value);

                    if (0 < count($list_files)) {
                        $documentId = '';
                        $sql1 = 'SELECT n.notesid' . "\r\n" . '                            FROM vtiger_notes n' . "\r\n" . '                            INNER JOIN vtiger_notescf ncf ON n.notesid = ncf.notesid' . "\r\n" . '                            INNER JOIN vtiger_senotesrel rel ON rel.notesid = n.notesid' . "\r\n" . '                            WHERE 1 = 1' . "\r\n" . '                            AND rel.crmid = ?' . "\r\n" . '                            AND ncf.cf_for_field = ?' . "\r\n" . '                            ORDER BY rel.notesid ASC LIMIT 0, 1';
                        $res1 = $adb->pquery($sql1, array($rowId, $key));

                        if (0 < $adb->num_rows($res1)) {
                            while ($row1 = $adb->fetchByAssoc($res1)) {
                                $documentId = $row1['notesid'];
                            }
                        }

                        if (!empty($documentId)) {
                            $sql2 = 'DELETE FROM `vtiger_seattachmentsrel` WHERE `crmid`=?';
                            $adb->pquery($sql2, array($documentId));
                        }

                        foreach ($list_files as $file_upload) {
                            $arr_file_upload = explode('$$', $file_upload);
                            $filesize = $arr_file_upload[1];
                            $filetype = $arr_file_upload[2];
                            $arr_file_name = $this->getFileName($arr_file_upload[0]);
                            $fileName = $arr_file_name['name'];
                            $path = $arr_file_name['path'];

                            if (0 < count($arr_file_name)) {
                                if (empty($documentId)) {
                                    $document = CRMEntity::getInstance($documentModuleName);
                                    $document->column_fields['notes_title'] = $fileName;
                                    $document->column_fields['filename'] = $fileName;
                                    $document->column_fields['filetype'] = $filetype;
                                    $document->column_fields['filesize'] = $filesize;
                                    $document->column_fields['filestatus'] = 1;
                                    $document->column_fields['filelocationtype'] = 'I';
                                    $document->column_fields['folderid'] = 1;
                                    $document->column_fields['cf_for_field'] = $key;
                                    $document->column_fields['assigned_user_id'] = $currentUserModel->getId();
                                    $document->saveentity('Documents');
                                    $documentId = $document->id;
                                    $adb->pquery('INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)', array($rowId, $documentId));
                                    $adb->pquery('UPDATE vtiger_notescf SET cf_for_field = ? WHERE notesid = ?', array($key, $documentId));
                                }
                                else {
                                    $document = CRMEntity::getInstance($documentModuleName);
                                    $document->id = $documentId;
                                    $document->mode = 'edit';
                                    $document->retrieve_entity_info($documentId, $documentModuleName);
                                    $document->clearSingletonSaveFields();
                                    $document->column_fields['notes_title'] = $fileName;
                                    $document->column_fields['filename'] = $fileName;
                                    $document->column_fields['filetype'] = $filetype;
                                    $document->column_fields['filesize'] = $filesize;
                                    $document->saveentity($documentModuleName);
                                }

                                $attachid = $arr_file_name['id'];
                                $res = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE crmid = ?', array($attachid));

                                if ($adb->num_rows($res) == 0) {
                                    $description = $fileName;
                                    $date_var = $adb->formatDate(date('YmdHis'), true);
                                    $usetime = $adb->formatDate($date_var, true);
                                    $adb->pquery('INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid,modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)' . "\r\n" . '                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($attachid, $currentUserModel->getId(), $currentUserModel->getId(), $currentUserModel->getId(), 'Documents Attachment', $description, $usetime, $usetime, 1, 0));
                                    $mimetype = $arr_file_upload[2];
                                    $adb->pquery('INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?', array($attachid, $fileName, $description, $mimetype, $path));
                                }

                                $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)', array($documentId, $attachid));
                            }
                        }
                    }
                }

            }
        }
    }

    public function getFileName($file)
    {
        $arr_file_name = explode('/', $file);
        $name = $arr_file_name[count($arr_file_name) - 1];
        $path = str_replace($name, '', $file);
        $array_name = explode('_', $name);
        $id = $array_name[0];
        $sid = $id . '_';
        $c = strlen($sid);
        $name = substr($name, $c);
        return array('id' => $array_name[0], 'name' => $name, 'path' => $path);
    }
}

?>