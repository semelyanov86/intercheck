<?php

/**
 * Class SignedRecord_Module_Model
 */
class SignedRecord_Module_Model extends Vtiger_Module_Model
{
    /**
     * @return array
     */
    public function getSettingLinks()
    {
        $settingsLinks = parent::getSettingLinks();
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=" . $this->name . "&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
}

?>