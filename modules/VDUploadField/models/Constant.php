<?php

class VDUploadField_Constant_Model
{
    static public $supportedField = array(        
        'Upload_Field'          => array('uitype' => 1, 'name' => 'LBL_UPLOAD_FIELD', 'prefix' => 'cf_vd_ulf', 'old_prefix' => 'avcf897913ul'),        
    );
    static public $columnType = array(1 => 'varchar(100)');

    static public function getAllContent($content)
    {
        $ret = array();

        foreach (self::$supportedField as $key) {
            $ret[] = $key[$content];
        }

        return $ret;
    }

    static public function getInfoByOldPrefix($old_prefix, $content)
    {
        foreach (self::$supportedField as $item) {
            return $item[$content];
        }

        return '';
    }
}


?>