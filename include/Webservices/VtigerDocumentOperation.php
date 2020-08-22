<?php

include_once 'include/Webservices/VtigerModuleOperation.php';
include_once 'include/Webservices/AttachmentHelper.php';

class VtigerDocumentOperation extends VtigerModuleOperation {
    protected $tabId;
    protected $isEntity = true;

    public function __construct($webserviceObject, $user, $adb, $log) {
        parent::__construct($webserviceObject, $user, $adb, $log);
        $this->tabId = $this->meta->getTabId();
    }

    /*
     * This create function supports a few virtual fields for the attachment and the related entities
     * so it expects and $element array with the normal Document fields and these additional ones:
     *
     * 'attachment'  this is a base64encoded string contaning the full document to be saved internally,
     *     this will only be checked if filelocationtype=='I'
     *
     * 'attachment_name'  a string with the name of the attachment
     *
     * 'relations'  this is an array of related entity id's, the id's must be in webservice extended format
     *     all the indicated entities will be related to the document being created
     *     *** this is done by the main vtws_create() function  ***
     */
    public function create($elementType, $element) {
        global $adb, $default_charset;
        $crmObject = new VtigerCRMObject($elementType, false);
        if (isset($element['relations'])) {
            $parent = $element['relations'];
        } else {
            $parent = false;
        }
        if ($parent) {
            $parentId = vtws_getCRMEntityId($parent);
        } else {
            $parentId = false;
        }

        if ($element['filelocationtype']=='I' && !empty($element['filename']) && is_array($element['filename'])) {
            $file = $element['filename'];
            $file['assigned_user_id'] = $element['assigned_user_id'];
            $file['setype'] = 'Documents Attachment';
            $attachid = SaveAttachmentDB($file);
            $element['filetype']=$file['type'];
            $element['filename']= str_replace(array(' ','/'), '_', $file['name']);  // no spaces nor slashes
            $element['filesize']=$file['size'];
            if ($element['filesize']==0) {
                $dbQuery = 'SELECT * FROM vtiger_attachments WHERE attachmentsid = ?' ;
                $result = $adb->pquery($dbQuery, array($attachid));
                if ($result && $adb->num_rows($result) == 1) {
                    $name = @$adb->query_result($result, 0, 'name');
                    $filepath = @$adb->query_result($result, 0, 'path');
                    $name = html_entity_decode($name, ENT_QUOTES, $default_charset);
                    $saved_filename = $attachid.'_'.$name;
                    $disk_file_size = filesize($filepath.$saved_filename);
                    $element['filesize']=$file['size']=$disk_file_size;
                }
            }
        }

        $element = DataTransform::sanitizeForInsert($element, $this->meta);

        $error = $crmObject->create($element);
        if (!$error) {
            throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
        }

        $id = $crmObject->getObjectId();

        $error = $crmObject->read($id);
        if (!$error) {
            throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
        }

        if ($element['filelocationtype']=='I' && !empty($attachid)) {
            // Link file attached to document
            $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)', array($id, $attachid));
            if ($parentId) {
                $adb->pquery('INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)', array($parentId, $id));
            }
        }
        // Establish relations *** this is done by the main vtws_create() function  ***

        $fields = $crmObject->getFields();
        $return = DataTransform::filterAndSanitize($fields, $this->meta);
        if (isset($fields['cbuuid'])) {
            $return['cbuuid'] = $fields['cbuuid'];
        }
        $this->addMoreInformation($id, $return);
        return $return;
    }

    public function retrieve($id, $deleted = false) {
        $ids = vtws_getIdComponents($id);
        $doc = parent::retrieve($id, $deleted);
        $this->addMoreInformation($ids[1], $doc);
        return $doc;
    }

    private function addMoreInformation($elemid, &$doc) {
        global $adb,$default_charset,$site_URL;
        // Add relations
        $relsrs=$adb->pquery('SELECT crmid FROM vtiger_senotesrel where notesid=?', array($elemid));
        $rels=array();
        while ($rl = $adb->fetch_array($relsrs)) {
            $rels[] = vtws_getEntityId(getSalesEntityType($rl['crmid'])) . 'x' . $rl['crmid'];
        }
        $doc['relations']=$rels;
        if ($doc['filelocationtype']=='I') { // Add direct download link
            $relatt=$adb->pquery('SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid=?', array($elemid));
            if ($relatt && $adb->num_rows($relatt) > 0) {
                $fileid = $adb->query_result($relatt, 0, 0);
                $attrs=$adb->pquery('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', array($fileid));
                if ($attrs && $adb->num_rows($attrs) > 0) {
                    $name = @$adb->query_result($attrs, 0, 'name');
                    $filepath = @$adb->query_result($attrs, 0, 'path');
                    $name = html_entity_decode($name, ENT_QUOTES, $default_charset);
                    $doc['_downloadurl'] = $site_URL.'/'.$filepath.$fileid.'_'.$name;
                    $doc['filename'] = $name;
                }
            }
        }
    }

    /*
     * This method accepts the same virtual fields that the create method does (see create)
     *
     * It will first eliminate the current related attachement and then relate the new attachment
     *
     * It will first eliminate all the current relations and then establish the new ones being sent in
     * so ALL relations that are needed must sent in again each time
     */
    public function update($element) {
        global $adb;
        $ids = vtws_getIdComponents($element['id']);
        if ($element['filelocationtype']=='I' && !empty($element['filename']) && is_array($element['filename'])) {
            $file = $element['filename'];
            $element['filesize']=$file['size'];
            $file['assigned_user_id'] = $element['assigned_user_id'];
            $file['setype'] = 'Documents Attachment';
            $attachid = SaveAttachmentDB($file);
            $element['filetype']=$file['type'];
            $element['filename']= str_replace(' ', '_', $file['name']);
        }

        $element = DataTransform::sanitizeForInsert($element, $this->meta);

        $crmObject = new VtigerCRMObject($this->tabId, true);
        $crmObject->setObjectId($ids[1]);
        $error = $crmObject->update($element);
        if (!$error) {
            throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
        }

        $id = $crmObject->getObjectId();

        if ($element['filelocationtype']=='I' && !empty($attachid)) {
            // Link file attached to document
            $adb->pquery('DELETE from vtiger_seattachmentsrel where crmid=?', array($id));
            $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)', array($id, $attachid));
        }

        return $this->retrieve(vtws_getEntityId($id).'x'.$id);
    }
}
?>