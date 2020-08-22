<?php


error_reporting(0);
require_once('modules/ITS4YouReports/ITS4YouReports.php');

class ITS4YouReports_KeyMetricsRows_View extends Vtiger_Index_View {

	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWNAME',$folderId);

        $kmNameRow = self::getKMRow($request);
        if (!empty($kmNameRow)) {
            $kmNameValue = $kmNameRow["name"];
            $kmsmcreatorid = $kmNameRow["smcreatorid"];
        }
        $viewer->assign("KM_NAME", $kmNameValue);
        $viewer->assign("KM_SMCREATOR", $kmsmcreatorid);
        $viewer->assign("CURRENT_USER", Users_Record_Model::getCurrentUserModel());
        $viewer->assign("KM_ID", ($request->has('km_id')?$request->get('km_id'):$request->get('id')));

        $pagingModel = new Vtiger_Paging_Model();
        $viewer->assign('PAGING_MODEL', $pagingModel);        

        if($display) {
			$this->preProcessDisplay($request);
		}
    }

    function preProcessTplName(Vtiger_Request $request) {
        return 'ListViewPreProcess.tpl';
	}

    /**
     * function to get Key Metric information by ID
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public static function getKMRow(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        if ('EditKeyMetricsRow' === $request->get('view')) {
            $kmId = $request->get('km_id');
        } else {
            $kmId = $request->get('id');
        }
        $kmNameRow = array();
        $kmNameSql = 'SELECT name, smcreatorid FROM  its4you_reports4you_key_metrics WHERE id=? AND deleted=0';
        $kmNameRes = $adb->pquery($kmNameSql, [$kmId]);
        if ($adb->num_rows($kmNameRes) > 0) {
            $kmNameRow = $adb->fetchByAssoc($kmNameRes, 0);
        }

        return $kmNameRow;
    }

	function process(Vtiger_Request $request) {
        
        $adb = PearDatabase::getInstance();
        
        $viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

        $ITS4YouReports = new ITS4YouReports_ITS4YouReports_Model();
        
        $listview_header = array("name"=>vtranslate("LBL_KEY_METRICS_NAME",$moduleName),"column"=>"label",);
        $LISTVIEW_HEADERS[] = $listview_header;
        
        $listview_header = array("name"=>vtranslate("SINGLE_ITS4YouReports",$moduleName),"column"=>"reportid",);
        $LISTVIEW_HEADERS[] = $listview_header;
        
        $listview_header = array("name"=>vtranslate("LBL_KEY_METRICS_TYPE",$moduleName),"column"=>"calculation_type",);
        $LISTVIEW_HEADERS[] = $listview_header;
        
        $listview_header = array("name"=>vtranslate("LBL_KEY_METRICS_COLUMN",$moduleName),"column"=>"column_str",);
        $LISTVIEW_HEADERS[] = $listview_header;
        
        $listview_header = array("name"=>vtranslate("LBL_KEY_METRICS_SEQUENCE",$moduleName),"column"=>"sequence",);
        $LISTVIEW_HEADERS[] = $listview_header;
        $viewer->assign('LISTVIEW_HEADERS', $LISTVIEW_HEADERS);

        $viewer->assign("VERSION_TYPE", 'professional');
        $viewer->assign("VERSION", ITS4YouReports_Version_Helper::$version);
        
        $kmId = $request->get("id");
        $viewer->assign("KM_ID", $kmId);

        $kmNameRow = self::getKMRow($request);
        if (!empty($kmNameRow)) {
            $kmNameValue = $kmNameRow["name"];
            $kmsmcreatorid = $kmNameRow["smcreatorid"];
        }
        $viewer->assign("KM_NAME", $kmNameValue);
        $viewer->assign("KM_SMCREATOR", $kmsmcreatorid);

        // ITS4YOU-CR SlOl 16. 12. 2015 13:13:12
        ITS4YouReports_CVRecord_Helper::setCustomViewRecordsToView();
		$kmSql = ITS4YouReports_KeyMetrics_Model::getKeyMetricsRowsSql();
        $kmRes = $adb->pquery($kmSql, array($kmId));
        $keyMetricsEntries = array();
        $noOfEntries = $adb->num_rows($kmRes);
        if ($noOfEntries > 0) {
            while ($kmRow = $adb->fetchByAssoc($kmRes)) {
            	if('customview'!==$kmRow['metrics_type']) {
					$keyMetricsEntries[] = $kmRow;
				} elseif(in_array($kmRow['reportid'], ITS4YouReports_CVRecord_Helper::$cvIdsToView)) {
					$keyMetricsEntries[] = $kmRow;
				}
            }
        }
        $viewer->assign('KEY_METRICS_ENTRIES', $keyMetricsEntries);
//echo "<pre>";print_r($keyMetricsEntries);echo "</pre>";
        
        $viewer->assign("LISTVIEW_ENTRIES_COUNT", $noOfEntries);
        
        // ITS4YOU-END
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
        
        $orderBy = $request->get('orderby');
        $viewer->assign('COLUMN_NAME',$orderBy);
        
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $viewer->assign('currentuser_id',$currentUserModel->id);
//error_reporting(63);ini_set("display_errors",1);

        $viewer->view('KeyMetricsRows.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

        $jsFileNames = array();
        if ('v7' === $layout) {
            $jsFileNames[] = 'modules.Vtiger.resources.Index';
        }
        $jsFileNames[] = 'modules.Vtiger.resources.List';
        $jsFileNames[] = "modules.$moduleName.resources.KeyMetricsList";

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}
?>