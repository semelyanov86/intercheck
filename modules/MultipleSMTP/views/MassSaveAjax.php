<?php

class MultipleSMTP_MassSaveAjax_View extends Vtiger_Footer_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("massSave");        
    }
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * Function Sends/Saves mass emails
     * @param <Vtiger_Request> $request
     */
    public function massSave(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $number_of_smtp = $request->get("number_of_smtp");
        if ($number_of_smtp == 0) {
            if (version_compare($vtiger_current_version, "7.0.0", "<")) {
                global $upload_badext;
                $adb = PearDatabase::getInstance();
                $moduleName = "Emails";
                $currentUserModel = Users_Record_Model::getCurrentUserModel();
                $recordIds = $this->getRecordsListFromRequest($request);
                $documentIds = $request->get("documentids");
                $flag = $request->get("flag");
                $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
                $_FILES = $result["file"];
                $recordId = $request->get("record");
                if (!empty($recordId)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                    $recordModel->set("mode", "edit");
                } else {
                    $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $recordModel->set("mode", "");
                }
                $parentEmailId = $request->get("parent_id", NULL);
                $attachmentsWithParentEmail = array();
                if (!empty($parentEmailId) && !empty($recordId)) {
                    $parentEmailModel = Vtiger_Record_Model::getInstanceById($parentEmailId);
                    $attachmentsWithParentEmail = $parentEmailModel->getAttachmentDetails();
                }
                $existingAttachments = $request->get("attachments", array());
                if (empty($recordId)) {
                    if (is_array($existingAttachments)) {
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            $existingAttachInfo["tmp_name"] = $existingAttachInfo["name"];
                            $existingAttachments[$index] = $existingAttachInfo;
                            if (array_key_exists("docid", $existingAttachInfo)) {
                                $documentIds[] = $existingAttachInfo["docid"];
                                unset($existingAttachments[$index]);
                            }
                        }
                    }
                } else {
                    $attachmentsToUnlink = array();
                    $documentsToUnlink = array();
                    foreach ($attachmentsWithParentEmail as $i => $attachInfo) {
                        $found = false;
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            if ($attachInfo["fileid"] == $existingAttachInfo["fileid"]) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            if (array_key_exists("docid", $attachInfo)) {
                                $documentsToUnlink[] = $attachInfo["docid"];
                            } else {
                                $attachmentsToUnlink[] = $attachInfo;
                            }
                        }
                        unset($attachmentsWithParentEmail[$i]);
                    }
                    $existingAttachments = array();
                    if (!empty($documentsToUnlink)) {
                        $recordModel->deleteDocumentLink($documentsToUnlink);
                    }
                    if (!empty($attachmentsToUnlink)) {
                        $recordModel->deleteAttachment($attachmentsToUnlink);
                    }
                }
                $toMailInfo = $request->get("toemailinfo");
                $to = $request->get("to");
                if (is_array($to)) {
                    $to = implode(",", $to);
                }
                $recordModel->set("description", $request->get("description"));
                $recordModel->set("subject", $request->get("subject"));
                $recordModel->set("toMailNamesList", $request->get("toMailNamesList"));
                $recordModel->set("saved_toid", $to);
                $recordModel->set("ccmail", $request->get("cc"));
                $recordModel->set("bccmail", $request->get("bcc"));
                $recordModel->set("assigned_user_id", $currentUserModel->getId());
                $recordModel->set("email_flag", $flag);
                $recordModel->set("documentids", $documentIds);
                $recordModel->set("toemailinfo", $toMailInfo);
                foreach ($toMailInfo as $recordId => $emailValueList) {
                    if ($recordModel->getEntityType($recordId) == "Users") {
                        $parentIds .= $recordId . "@-1|";
                    } else {
                        $parentIds .= $recordId . "@1|";
                    }
                }
                $recordModel->set("parent_id", $parentIds);
                $_REQUEST["parent_id"] = $parentIds;
                $success = false;
                $viewer = $this->getViewer($request);
                if ($recordModel->checkUploadSize($documentIds)) {
                    $recordModel->save();
                    $current_user = Users_Record_Model::getCurrentUserModel();
                    $ownerId = $recordModel->get("assigned_user_id");
                    $date_var = date("Y-m-d H:i:s");
                    if (is_array($existingAttachments)) {
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            $file_name = $existingAttachInfo["attachment"];
                            $path = $existingAttachInfo["path"];
                            $fileId = $existingAttachInfo["fileid"];
                            $oldFileName = $file_name;
                            if (!empty($fileId)) {
                                $oldFileName = $existingAttachInfo["fileid"] . "_" . $file_name;
                            }
                            $oldFilePath = $path . "/" . $oldFileName;
                            $binFile = sanitizeUploadFileName($file_name, $upload_badext);
                            $current_id = $adb->getUniqueID("vtiger_crmentity");
                            $filename = ltrim(basename(" " . $binFile));
                            $filetype = $existingAttachInfo["type"];
                            $filesize = $existingAttachInfo["size"];
                            $upload_file_path = decideFilePath();
                            $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
                            copy($oldFilePath, $newFilePath);
                            $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                            $params1 = array($current_id, $current_user->getId(), $ownerId, $moduleName . " Attachment", $recordModel->get("description"), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                            $adb->pquery($sql1, $params1);
                            $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                            $params2 = array($current_id, $filename, $recordModel->get("description"), $filetype, $upload_file_path);
                            $result = $adb->pquery($sql2, $params2);
                            $sql3 = "insert into vtiger_seattachmentsrel values(?,?)";
                            $adb->pquery($sql3, array($recordModel->getId(), $current_id));
                        }
                    }
                    $success = true;
                    if ($flag == "SENT") {
                        $status = $recordModel->send();
                        if ($status === true) {
                            $recordModel->setAccessCountValue();
                        } else {
                            $success = false;
                            $message = $status;
                        }
                    }
                } else {
                    $message = vtranslate("LBL_MAX_UPLOAD_SIZE", $moduleName) . " " . vtranslate("LBL_EXCEEDED", $moduleName);
                }
                $viewer->assign("SUCCESS", $success);
                $viewer->assign("MESSAGE", $message);
                $loadRelatedList = $request->get("related_load");
                if (!empty($loadRelatedList)) {
                    $viewer->assign("RELATED_LOAD", true);
                }
                $viewer->view("SendEmailResult.tpl", $moduleName);
            } else {
                global $upload_badext;
                $adb = PearDatabase::getInstance();
                $moduleName = "Emails";
                $currentUserModel = Users_Record_Model::getCurrentUserModel();
                $recordIds = $this->getRecordsListFromRequest($request);
                $documentIds = $request->get("documentids");
                $signature = $request->get("signature");
                $flag = $request->get("flag");
                $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
                $_FILES = $result["file"];
                $recordId = $request->get("record");
                if (!empty($recordId)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                    $recordModel->set("mode", "edit");
                } else {
                    $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $recordModel->set("mode", "");
                }
                $parentEmailId = $request->get("parent_id", NULL);
                $attachmentsWithParentEmail = array();
                if (!empty($parentEmailId) && !empty($recordId)) {
                    $parentEmailModel = Vtiger_Record_Model::getInstanceById($parentEmailId);
                    $attachmentsWithParentEmail = $parentEmailModel->getAttachmentDetails();
                }
                $existingAttachments = $request->get("attachments", array());
                if (empty($recordId)) {
                    if (is_array($existingAttachments)) {
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            $existingAttachInfo["tmp_name"] = $existingAttachInfo["name"];
                            $existingAttachments[$index] = $existingAttachInfo;
                            if (array_key_exists("docid", $existingAttachInfo)) {
                                $documentIds[] = $existingAttachInfo["docid"];
                                unset($existingAttachments[$index]);
                            }
                        }
                    }
                } else {
                    $attachmentsToUnlink = array();
                    $documentsToUnlink = array();
                    foreach ($attachmentsWithParentEmail as $i => $attachInfo) {
                        $found = false;
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            if ($attachInfo["fileid"] == $existingAttachInfo["fileid"]) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            if (array_key_exists("docid", $attachInfo)) {
                                $documentsToUnlink[] = $attachInfo["docid"];
                            } else {
                                $attachmentsToUnlink[] = $attachInfo;
                            }
                        }
                        unset($attachmentsWithParentEmail[$i]);
                    }
                    $existingAttachments = array();
                    if (!empty($documentsToUnlink)) {
                        $recordModel->deleteDocumentLink($documentsToUnlink);
                    }
                    if (!empty($attachmentsToUnlink)) {
                        $recordModel->deleteAttachment($attachmentsToUnlink);
                    }
                }
                $toMailInfo = $request->get("toemailinfo");
                $to = $request->get("to");
                if (is_array($to)) {
                    $to = implode(",", $to);
                }
                $content = $request->getRaw("description");
                $content = preg_replace("/&nbsp;/", "", $content);
                $processedContent = Emails_Mailer_Model::getProcessedContent($content);
                $processedContent = preg_replace("/&nbsp;/", "", $processedContent);
                $mailerInstance = Emails_Mailer_Model::getInstance();
                $processedContentWithURLS = decode_html($mailerInstance->convertToValidURL($processedContent));
                $processedContentWithURLS = preg_replace("/&nbsp;/", "", $processedContentWithURLS);
                $recordModel->set("description", $processedContentWithURLS);
                $recordModel->set("subject", $request->get("subject"));
                $recordModel->set("toMailNamesList", $request->get("toMailNamesList"));
                $recordModel->set("saved_toid", $to);
                $recordModel->set("ccmail", $request->get("cc"));
                $recordModel->set("bccmail", $request->get("bcc"));
                $recordModel->set("assigned_user_id", $currentUserModel->getId());
                $recordModel->set("email_flag", $flag);
                $recordModel->set("documentids", $documentIds);
                $recordModel->set("signature", $signature);
                $recordModel->set("toemailinfo", $toMailInfo);
                foreach ($toMailInfo as $recordId => $emailValueList) {
                    if ($recordModel->getEntityType($recordId) == "Users") {
                        $parentIds .= $recordId . "@-1|";
                    } else {
                        $parentIds .= $recordId . "@1|";
                    }
                }
                $recordModel->set("parent_id", $parentIds);
                $sendmail_from_module = $request->get("sendmail_from_module");
                if ($sendmail_from_module == "ModComments") {
                    $vteCommentsmoduleModel = Vtiger_Module_Model::getInstance("VTEComments");
                    $vteComments = $vteCommentsmoduleModel->getVTEComments();
                    $bccStatus = $vteComments["auto_bcc"];
                    $bccEmail = $vteComments["auto_bcc_email"];
                    if ($bccStatus == 1 && !empty($bccEmail)) {
                        $recordModel->set("bccmail", $bccEmail);
                    }
                }
                $_REQUEST["parent_id"] = $parentIds;
                $success = false;
                $viewer = $this->getViewer($request);
                if ($recordModel->checkUploadSize($documentIds)) {
                    $recordModel->save();
                    $emailRecordId = $recordModel->getId();
                    foreach ($toMailInfo as $recordId => $emailValueList) {
                        $relatedModule = $recordModel->getEntityType($recordId);
                        if (!empty($relatedModule) && $relatedModule != "Users") {
                            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
                            $relationModel = Vtiger_Relation_Model::getInstance($relatedModuleModel, $recordModel->getModule());
                            if ($relationModel) {
                                $relationModel->addRelation($recordId, $emailRecordId);
                            }
                        }
                    }
                    $current_user = Users_Record_Model::getCurrentUserModel();
                    $ownerId = $recordModel->get("assigned_user_id");
                    $date_var = date("Y-m-d H:i:s");
                    if (is_array($existingAttachments)) {
                        foreach ($existingAttachments as $index => $existingAttachInfo) {
                            $file_name = $existingAttachInfo["attachment"];
                            $path = $existingAttachInfo["path"];
                            $fileId = $existingAttachInfo["fileid"];
                            $oldFileName = $file_name;
                            if (!empty($fileId)) {
                                $oldFileName = $existingAttachInfo["fileid"] . "_" . $file_name;
                            }
                            $oldFilePath = $path . "/" . $oldFileName;
                            $binFile = sanitizeUploadFileName($file_name, $upload_badext);
                            $current_id = $adb->getUniqueID("vtiger_crmentity");
                            $filename = ltrim(basename(" " . $binFile));
                            $filetype = $existingAttachInfo["type"];
                            $filesize = $existingAttachInfo["size"];
                            $upload_file_path = decideFilePath();
                            $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
                            copy($oldFilePath, $newFilePath);
                            $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                            $params1 = array($current_id, $current_user->getId(), $ownerId, $moduleName . " Attachment", $recordModel->get("description"), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                            $adb->pquery($sql1, $params1);
                            $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                            $params2 = array($current_id, $filename, $recordModel->get("description"), $filetype, $upload_file_path);
                            $result = $adb->pquery($sql2, $params2);
                            $sql3 = "insert into vtiger_seattachmentsrel values(?,?)";
                            $adb->pquery($sql3, array($recordModel->getId(), $current_id));
                        }
                    }
                    $success = true;
                    if ($flag == "SENT") {
                        $status = $recordModel->send();
                        if ($status === true) {
                            $sourceRecordId = $request->get("source_record_id");
                            $sendmail_from_module = $request->get("sendmail_from_module");
                            if ($sendmail_from_module == "ModComments") {
                                $this->doCreatedModComments($recordModel, $sourceRecordId);
                            }
                            $recordModel->setAccessCountValue();
                        } else {
                            $success = false;
                            $message = $status;
                        }
                    }
                } else {
                    $message = vtranslate("LBL_MAX_UPLOAD_SIZE", $moduleName) . " " . vtranslate("LBL_EXCEEDED", $moduleName);
                }
                $viewer->assign("SUCCESS", $success);
                $viewer->assign("MESSAGE", $message);
                $viewer->assign("FLAG", $flag);
                $viewer->assign("MODULE", $moduleName);
                $loadRelatedList = $request->get("related_load");
                if (!empty($loadRelatedList)) {
                    $viewer->assign("RELATED_LOAD", true);
                }
                $viewer->view("SendEmailResult.tpl", $moduleName);
            }
        } else {
            global $upload_badext;
            $adb = PearDatabase::getInstance();
            $moduleName = $request->getModule();
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $recordIds = $this->getRecordsListFromRequest($request);
            $documentIds = $request->get("documentids");
            $flag = $request->get("flag");
            $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
            $_FILES = $result["file"];
            $recordId = $request->get("record");
            if (!empty($recordId)) {
                $recordModel = MultipleSMTP_Record_Model::getInstanceById($recordId, "MultipleSMTP");
                $recordModel->set("mode", "edit");
            } else {
                $recordModel = MultipleSMTP_Record_Model::getCleanInstance("MultipleSMTP");
                $recordModel->set("mode", "");
            }
            $parentEmailId = $request->get("parent_id", NULL);
            $attachmentsWithParentEmail = array();
            if (!empty($parentEmailId) && !empty($recordId)) {
                $parentEmailModel = MultipleSMTP_Record_Model::getInstanceById($parentEmailId);
                $attachmentsWithParentEmail = $parentEmailModel->getAttachmentDetails();
            }
            $existingAttachments = $request->get("attachments", array());
            if (empty($recordId)) {
                if (is_array($existingAttachments)) {
                    foreach ($existingAttachments as $index => $existingAttachInfo) {
                        $existingAttachInfo["tmp_name"] = $existingAttachInfo["name"];
                        $existingAttachments[$index] = $existingAttachInfo;
                        if (array_key_exists("docid", $existingAttachInfo)) {
                            $documentIds[] = $existingAttachInfo["docid"];
                            unset($existingAttachments[$index]);
                        }
                    }
                }
            } else {
                $attachmentsToUnlink = array();
                $documentsToUnlink = array();
                foreach ($attachmentsWithParentEmail as $i => $attachInfo) {
                    $found = false;
                    foreach ($existingAttachments as $index => $existingAttachInfo) {
                        if ($attachInfo["fileid"] == $existingAttachInfo["fileid"]) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        if (array_key_exists("docid", $attachInfo)) {
                            $documentsToUnlink[] = $attachInfo["docid"];
                        } else {
                            $attachmentsToUnlink[] = $attachInfo;
                        }
                    }
                    unset($attachmentsWithParentEmail[$i]);
                }
                $existingAttachments = array();
                if (!empty($documentsToUnlink)) {
                    $recordModel->deleteDocumentLink($documentsToUnlink);
                }
                if (!empty($attachmentsToUnlink)) {
                    $recordModel->deleteAttachment($attachmentsToUnlink);
                }
            }
            $toSendMail = array();
            $toMailNamesList = $request->get("toemailinfo_emailField");
            if (is_array($toMailNamesList)) {
                foreach ($toMailNamesList as $pid => $Emails_Data) {
                    foreach ($Emails_Data as $id => $Email_Data) {
                        $toSendMail[$id] = $Email_Data;
                    }
                }
            }
            $toMailInfo = $toSendMail;
            if (empty($toMailInfo)) {
                $toMailInfo = $request->get("toemailinfo");
            }
            $ccSendMail = array();
            $ccMailNamesList = $request->get("toemailinfo_emailCCField");
            if (is_array($toMailNamesList)) {
                foreach ($ccMailNamesList as $pid => $Emails_Data) {
                    foreach ($Emails_Data as $id => $Email_Data) {
                        $ccSendMail[$id] = $Email_Data;
                    }
                }
            }
            $ccEmailsInfo = array();
            foreach ($ccSendMail as $id => $emails) {
                foreach ($emails as $key => $value) {
                    array_push($ccEmailsInfo, $value);
                }
            }
            $ccMailInfo = implode(",", $ccEmailsInfo);
            if (empty($ccMailInfo)) {
                $ccMailInfo = $request->get("cc");
            }
            $bccSendMail = array();
            $bccMailNamesList = $request->get("toemailinfo_emailBCCField");
            if (is_array($toMailNamesList)) {
                foreach ($bccMailNamesList as $pid => $Emails_Data) {
                    foreach ($Emails_Data as $id => $Email_Data) {
                        $bccSendMail[$id] = $Email_Data;
                    }
                }
            }
            $bccEmailsInfo = array();
            foreach ($bccSendMail as $id => $emails) {
                foreach ($emails as $key => $value) {
                    array_push($bccEmailsInfo, $value);
                }
            }
            $bccMailInfo = implode(",", $bccEmailsInfo);
            if (empty($bccMailInfo)) {
                $bccMailInfo = $request->get("bcc");
            }
            $to = $request->get("to");
            if (is_array($to)) {
                $to = implode(",", $to);
            }
            $description = $request->get("description");
            require_once "modules/com_vtiger_workflow/VTSimpleTemplate.inc";
            $vtSimpleTemplate = new VTSimpleTemplate("");
            $mergeComments = array("lastComment", "last5Comments", "allComments");
            foreach ($recordIds as $rId) {
                foreach ($mergeComments as $fieldName) {
                    $mergeContent = $vtSimpleTemplate->getComments($request->get("source_module"), $fieldName, "x" . $rId);
                    $description = str_replace("\$" . $fieldName, $mergeContent, $description);
                }
            }
            $recordModel->set("from_serveremailid", $request->get("from_serveremailid"));
            $recordModel->set("from_email", MultipleSMTP_Record_Model::getMultipleSMTMFromEmailAddress($request->get("from_serveremailid")));
            $recordModel->set("description", $description);
            $recordModel->set("subject", $request->get("subject"));
            $recordModel->set("toMailNamesList", $request->get("toMailNamesList"));
            $recordModel->set("saved_toid", $to);
            $recordModel->set("ccmail", $ccMailInfo);
            $recordModel->set("bccmail", $bccMailInfo);
            $recordModel->set("assigned_user_id", $currentUserModel->getId());
            $recordModel->set("email_flag", $flag);
            $recordModel->set("documentids", $documentIds);
            $recordModel->set("toemailinfo", $toMailInfo);
            foreach ($toMailInfo as $recordId => $emailValueList) {
                if ($recordModel->getEntityType($recordId) == "Users") {
                    $parentIds .= $recordId . "@-1|";
                } else {
                    $parentIds .= $recordId . "@1|";
                }
            }
            $recordModel->set("parent_id", $parentIds);
            $sendmail_from_module = $request->get("sendmail_from_module");
            if ($sendmail_from_module == "ModComments") {
                $vteCommentsmoduleModel = Vtiger_Module_Model::getInstance("VTEComments");
                $vteComments = $vteCommentsmoduleModel->getVTEComments();
                $bccStatus = $vteComments["auto_bcc"];
                $bccEmail = $vteComments["auto_bcc_email"];
                if ($bccStatus == 1 && !empty($bccEmail)) {
                    $recordModel->set("bccmail", $bccEmail);
                }
            }
            $_REQUEST["parent_id"] = $parentIds;
            $_REQUEST["module"] = "Emails";
            $success = false;
            $viewer = $this->getViewer($request);
            if ($recordModel->checkUploadSize($documentIds)) {
                $recordModel->save();
                $current_user = Users_Record_Model::getCurrentUserModel();
                $ownerId = $recordModel->get("assigned_user_id");
                $date_var = date("Y-m-d H:i:s");
                if (is_array($existingAttachments)) {
                    foreach ($existingAttachments as $index => $existingAttachInfo) {
                        $file_name = $existingAttachInfo["attachment"];
                        $path = $existingAttachInfo["path"];
                        $fileId = $existingAttachInfo["fileid"];
                        $oldFileName = $file_name;
                        if (!empty($fileId)) {
                            $oldFileName = $existingAttachInfo["fileid"] . "_" . $file_name;
                        }
                        $oldFilePath = $path . "/" . $oldFileName;
                        $binFile = sanitizeUploadFileName($file_name, $upload_badext);
                        $current_id = $adb->getUniqueID("vtiger_crmentity");
                        $filename = ltrim(basename(" " . $binFile));
                        $filetype = $existingAttachInfo["type"];
                        $filesize = $existingAttachInfo["size"];
                        $upload_file_path = decideFilePath();
                        $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
                        copy($oldFilePath, $newFilePath);
                        $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                        $params1 = array($current_id, $current_user->getId(), $ownerId, $moduleName . " Attachment", $recordModel->get("description"), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                        $adb->pquery($sql1, $params1);
                        $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                        $params2 = array($current_id, $filename, $recordModel->get("description"), $filetype, $upload_file_path);
                        $result = $adb->pquery($sql2, $params2);
                        $sql3 = "insert into vtiger_seattachmentsrel values(?,?)";
                        $adb->pquery($sql3, array($recordModel->getId(), $current_id));
                    }
                }
                $pdftemplateid = rtrim($request->get("pdftemplateids"), ";");
                if ($pdftemplateid != "") {
                    $templateIds = explode(";", $pdftemplateid);
                    if (0 < count($templateIds)) {
                        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
                        $adb = PearDatabase::getInstance();
                        $PDFMaker = new PDFMaker_PDFMaker_Model();
                        $id = $request->get("selected_sourceid");
                        $modFocusName = $request->get("for_module");
                        $modFocus = CRMEntity::getInstance($modFocusName);
                        $modFocus->retrieve_entity_info($id, $modFocusName);
                        $modFocus->id = $id;
                        $language = $request->get("pdflanguage");
                        $emailFocus = CRMEntity::getInstance("Emails");
                        $emailFocus->id = $recordModel->getId();
                        foreach ($templateIds as $templateid) {
                            if ($templateid != "0" && $templateid != "") {
                                if ($PDFMaker->isTemplateDeleted($templateid)) {
                                    return NULL;
                                }
                                $result = $adb->query("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=" . getTabId($modFocusName));
                                $fieldname = $adb->query_result($result, 0, "fieldname");
                                if (isset($modFocus->column_fields[$fieldname]) && $modFocus->column_fields[$fieldname] != "") {
                                    $file_name = $PDFMaker->generate_cool_uri($modFocus->column_fields[$fieldname]) . ".pdf";
                                } else {
                                    $file_name = $templateid . $recordModel->parentid . date("ymdHi") . ".pdf";
                                }
                                $PDFMaker->createPDFAndSaveFile($request, $templateid, $emailFocus, $modFocus, $file_name, $modFocusName, $language);
                            }
                        }
                    }
                }
                $check_attach_file = $request->get("check_attach_file") == "on";
                if ($check_attach_file) {
                    $QuotingToolAttachFile = $request->get("QuotingToolAttachFile");
                    if (!empty($QuotingToolAttachFile)) {
                        $current_id = $adb->getUniqueID("vtiger_crmentity");
                        $file_path = explode("/", $QuotingToolAttachFile);
                        $filename = array_pop($file_path);
                        $upload_file_path = decideFilePath();
                        $filetype = "application/pdf";
                        $newFilePath = $upload_file_path . $current_id . "_" . $filename;
                        $oldFilePath = $QuotingToolAttachFile;
                        copy($oldFilePath, $newFilePath);
                        $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                        $params1 = array($current_id, $current_user->getId(), $ownerId, $moduleName . " Attachment", $recordModel->get("description"), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                        $adb->pquery($sql1, $params1);
                        $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                        $params2 = array($current_id, $filename, $recordModel->get("description"), $filetype, $upload_file_path);
                        $result = $adb->pquery($sql2, $params2);
                        $sql3 = "insert into vtiger_seattachmentsrel values(?,?)";
                        $adb->pquery($sql3, array($recordModel->getId(), $current_id));
                    }
                }
                $success = true;
                if ($flag == "SENT") {
                    $status = $recordModel->send();
                    if ($status === true) {
                        $sourceRecordId = $request->get("source_record_id");
                        $sendmail_from_module = $request->get("sendmail_from_module");
                        if ($sendmail_from_module == "ModComments") {
                            $this->doCreatedModComments($recordModel, $sourceRecordId);
                        }
                        $recordModel->setAccessCountValue();
                    } else {
                        $success = false;
                        $message = $status;
                    }
                }
            } else {
                $message = vtranslate("LBL_MAX_UPLOAD_SIZE", $moduleName) . " " . vtranslate("LBL_EXCEEDED", $moduleName);
            }
            $viewer->assign("SUCCESS", $success);
            $viewer->assign("MESSAGE", $message);
            $loadRelatedList = $request->get("related_load");
            if (!empty($loadRelatedList)) {
                $viewer->assign("RELATED_LOAD", true);
            }
            $viewer->view("SendEmailResult.tpl", "Emails");
        }
    }
    public function doCreatedModComments($recordModel, $sourceRecordId)
    {
        global $upload_badext;
        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $mergedDescription = getMergedDescription($recordModel->get("description"), $current_user->getId(), "Users");
        $mergedDescriptionWithHyperLinkConversion = $this->replaceBrowserMergeTagWithValue($mergedDescription, "HelpDesk", $sourceRecordId, $recordModel->getId());
        $description = getMergedDescription($mergedDescriptionWithHyperLinkConversion, $sourceRecordId, "HelpDesk");
        require_once "modules/ModComments/ModComments.php";
        $commentRecordModel = new ModComments();
        $commentRecordModel->column_fields["related_to"] = $sourceRecordId;
        $commentRecordModel->column_fields["userid"] = $current_user->getId();
        $commentRecordModel->column_fields["related_email_id"] = $recordModel->getId();
        $commentRecordModel->column_fields["assigned_user_id"] = $current_user->getId();
        $commentRecordModel->column_fields["creator"] = $current_user->getId();
        $commentRecordModel->column_fields["commentcontent"] = $description;
        $vteCommentsmoduleModel = Vtiger_Module_Model::getInstance("VTEComments");
        $vteComments = $vteCommentsmoduleModel->getVTEComments();
        $trigger_workflow = $vteComments["trigger_workflow"];
        if ($trigger_workflow) {
            $commentRecordModel->save();
        } else {
            $commentRecordModel->saveentity("ModComments");
        }
        $existingAttachments = $recordModel->getAttachmentDetails();
        if (is_array($existingAttachments)) {
            foreach ($existingAttachments as $index => $existingAttachInfo) {
                $file_name = $existingAttachInfo["attachment"];
                $path = $existingAttachInfo["path"];
                $fileId = $existingAttachInfo["fileid"];
                $oldFileName = $file_name;
                if (!empty($fileId)) {
                    $oldFileName = $existingAttachInfo["fileid"] . "_" . $file_name;
                }
                $oldFilePath = $path . "/" . $oldFileName;
                $binFile = sanitizeUploadFileName($file_name, $upload_badext);
                $current_id = $adb->getUniqueID("vtiger_crmentity");
                $filename = ltrim(basename(" " . $binFile));
                $filetype = $existingAttachInfo["type"];
                $filesize = $existingAttachInfo["size"];
                $upload_file_path = decideFilePath();
                $newFilePath = $upload_file_path . $current_id . "_" . $binFile;
                copy($oldFilePath, $newFilePath);
                $ownerId = $recordModel->get("assigned_user_id");
                $date_var = date("Y-m-d H:i:s");
                $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                $params1 = array($current_id, $current_user->getId(), $ownerId, "ModComments Attachment", $commentRecordModel->get("commentcontent"), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                $adb->pquery($sql1, $params1);
                $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                $params2 = array($current_id, $filename, $commentRecordModel->get("commentcontent"), $filetype, $upload_file_path);
                $result = $adb->pquery($sql2, $params2);
                $sql3 = "insert into vtiger_seattachmentsrel values(?,?)";
                $adb->pquery($sql3, array($commentRecordModel->getId(), $current_id));
            }
        }
    }
    /**
     * Function to replace browser merge tag with value
     * @param type $mergedDescription
     * @param type $parentModule
     * @param type $recipientId
     * @return type
     */
    public function replaceBrowserMergeTagWithValue($mergedDescription, $parentModule, $recipientId, $emailId)
    {
        global $application_unique_key;
        $receiverId = $parentModule[0] . $recipientId;
        $urlParameters = http_build_query(array("rid" => $receiverId, "applicationKey" => $application_unique_key));
        $rlock = $this->generateSecureKey($urlParameters);
        $URL = $this->getTrackingShortUrl($parentModule, $emailId) . "&" . $urlParameters . "&rv=" . $rlock;
        return str_replace(EmailTemplates_Module_Model::$BROWSER_MERGE_TAG, $URL, $mergedDescription);
    }
    public function generateSecureKey($urlParameters)
    {
        return md5($urlParameters);
    }
    /**
     * Function stores emailid,parentmodule and generates shorturl
     * @param type $parentModule
     * @return type
     */
    public function getTrackingShortUrl($parentModule, $emailId = "")
    {
        $options = array("handler_path" => "modules/Emails/handlers/ViewInBrowser.php", "handler_class" => "Emails_ViewInBrowser_Handler", "handler_function" => "viewInBrowser", "handler_data" => array("emailId" => $emailId, "parentModule" => $parentModule));
        $trackURL = Vtiger_ShortURL_Helper::generateURL($options);
        return $trackURL;
    }
    /**
     * Function returns the record Ids selected in the current filter
     * @param Vtiger_Request $request
     * @return integer
     */
    public function getRecordsListFromRequest(Vtiger_Request $request)
    {
        $cvId = $request->get("viewname");
        $selectedIds = $request->get("selected_ids");
        $excludedIds = $request->get("excluded_ids");
        if (!empty($selectedIds) && $selectedIds != "all" && !empty($selectedIds) && 0 < count($selectedIds)) {
            return $selectedIds;
        }
        if ($selectedIds == "all") {
            $sourceRecord = $request->get("sourceRecord");
            $sourceModule = $request->get("sourceModule");
            if ($sourceRecord && $sourceModule) {
                $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
                return $sourceRecordModel->getSelectedIdsList($request->get("parentModule"), $excludedIds);
            }
            $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
            if ($customViewModel) {
                $searchKey = $request->get("search_key");
                $searchValue = $request->get("search_value");
                $operator = $request->get("operator");
                if (!empty($operator)) {
                    $customViewModel->set("operator", $operator);
                    $customViewModel->set("search_key", $searchKey);
                    $customViewModel->set("search_value", $searchValue);
                }
                return $customViewModel->getRecordIds($excludedIds);
            }
        }
        return array();
    }
    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}

?>