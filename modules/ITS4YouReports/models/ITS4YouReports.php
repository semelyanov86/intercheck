<?php

error_reporting(0);

class ITS4YouReports_ITS4YouReports_Model extends Vtiger_Module_Model {

    private $version_type;
    private $license_key;
    private $version_no;
    private $basicModules;
    private $pageFormats;
    private $profilesActions;
    private $profilesPermissions;
    var $log;
    var $db;

    // constructor of ITS4YouReports class
    function __construct() {
        $this->log = LoggerManager::getLogger('account');
        $this->db = PearDatabase::getInstance();
    }

    public static function GetAvailableSettings() {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
		$menu_array = array();
        
        $menu_array["ITS4YouReportsLicense"]["location"] = "index.php?module=ITS4YouReports&parent=Settings&view=License";
        $menu_array["ITS4YouReportsLicense"]["image_src"] = Vtiger_Theme::getImagePath('proxy.gif');
        $menu_array["ITS4YouReportsLicense"]["desc"] = "LBL_LICENSE_DESC";
        $menu_array["ITS4YouReportsLicense"]["label"] = "LBL_LICENSE";

        $locationUninstall = "index.php?module=ITS4YouReports&view=Uninstall";
		if($layout !== "v7"){
		   $locationUninstall .= '&parent=Settings';
		}
		$menu_array["ITS4YouRestrictPicklistUninstall"]["location"] = $locationUninstall;
        $menu_array["ITS4YouRestrictPicklistUninstall"]["desc"] = "LBL_UNINSTALL_DESC";
        $menu_array["ITS4YouRestrictPicklistUninstall"]["label"] = "LBL_UNINSTALL";
        
        $menu_array["ITS4YouReportsUpgrade"]["location"] = "index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1";
        $menu_array["ITS4YouReportsUpgrade"]["desc"] = "LBL_UPGRADE";
        $menu_array["ITS4YouReportsUpgrade"]["label"] = "LBL_UPGRADE";
        
        return $menu_array;
    }
    
} 