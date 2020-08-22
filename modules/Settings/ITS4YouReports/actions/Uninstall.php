<?php

class Settings_ITS4YouReports_Uninstall_Action extends Settings_Vtiger_Basic_Action {

    function process(Vtiger_Request $request) {

        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');
        $adb = PearDatabase::getInstance();

        // ITS4YouReports Remove
        // clean chace files
        foreach (glob("test/ITS4YouReports/R4YouCharts_*.png") as $filename) {
            unlink($filename);
        }
        foreach (glob("test/ITS4YouReports/Reports4You_*.pdf") as $filename) {
            unlink($filename);
        }

        $moduleName = 'ITS4YouReports';
        $tabid = getTabid($moduleName);
        $module = Vtiger_Module::getInstance($moduleName);
        $result = array('success' => false);

        if ($module) {
            $module->delete();

            $moduleModel = new ITS4YouReports_Module_Model();
            $request->set('key', $moduleModel->GetLicenseKey());
            $license = new Settings_ITS4YouReports_License_Action();
            $license->deactivateLicense($request);

            $adb->query("DROP TABLE IF EXISTS its4you_reports4you");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_charts");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_datefilter");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_folder");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_labels");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_license");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_modules");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_profilespermissions");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_relcriteria");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_relcriteria_grouping");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_relcriteria_summaries");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_reportfilters");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_scheduled_reports");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_selectcolumn");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_selectqfcolumn");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_selectquery");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_selectquery_seq");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_settings");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_sharing");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_sortcol");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_summaries");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_summaries_orderby");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_summary");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_userstatus");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_version");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_customsql");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_updates");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_key_metrics");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_key_metrics_rows");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_widget_search");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_generatefor");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_custom_calculations");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_cc_columns");
            $adb->query("DROP TABLE IF EXISTS its4you_error_log");
            $adb->query("DROP TABLE IF EXISTS its4you_reports4you_osm_maps");
            $adb->query("DROP TABLE IF EXISTS its4you_reports_geolocations");

            $adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE linkid IN (SELECT linkid FROM `vtiger_links` WHERE linkurl like '%module=ITS4YouReports%')", array());
            $adb->pquery("DELETE FROM `vtiger_links` WHERE linkurl like '%module=ITS4YouReports%'", array());

            if($tabid!=""){
                Vtiger_Cron::deregister('Schedule Reports4You');

                // replaced with query !!!
                $adb->pquery("DELETE FROM vtiger_tab WHERE tabid=?", array($tabid));

                if(function_exists('shell_exec')){
                    @shell_exec('rm -R modules/ITS4YouReports');
                    @shell_exec('rm -R layouts/vlayout/modules/ITS4YouReports');
                    @shell_exec('rm languages/ar_ae/ITS4YouReports.php');
                    @shell_exec('rm languages/cz_cz/ITS4YouReports.php');
                    @shell_exec('rm languages/de_de/ITS4YouReports.php');
                    @shell_exec('rm languages/en_gb/ITS4YouReports.php');
                    @shell_exec('rm languages/en_us/ITS4YouReports.php');
                    @shell_exec('rm languages/es_es/ITS4YouReports.php');
                    @shell_exec('rm languages/es_mx/ITS4YouReports.php');
                    @shell_exec('rm languages/fr_fr/ITS4YouReports.php');
                    @shell_exec('rm languages/hi_hi/ITS4YouReports.php');
                    @shell_exec('rm languages/hu_hu/ITS4YouReports.php');
                    @shell_exec('rm languages/it_it/ITS4YouReports.php');
                    @shell_exec('rm languages/nl_nl/ITS4YouReports.php');
                    @shell_exec('rm languages/pl_pl/ITS4YouReports.php');
                    @shell_exec('rm languages/pt_br/ITS4YouReports.php');
                    @shell_exec('rm languages/ro_ro/ITS4YouReports.php');
                    @shell_exec('rm languages/ru_ru/ITS4YouReports.php');
                    @shell_exec('rm languages/sk_sk/ITS4YouReports.php');
                    @shell_exec('rm languages/tr_tr/ITS4YouReports.php');
                }else{
                    echo "Please go to directories below and remove ITS4YouReports files:<br />
	                modules/ITS4YouReports/*.*<br />
	                layouts/vlayout/modules/ITS4YouReports/*.*<br />
	                languages/ar_ae/ITS4YouReports.php<br />
	                languages/cz_cz/ITS4YouReports.php<br />
	                languages/de_de/ITS4YouReports.php<br />
	                languages/en_gb/ITS4YouReports.php<br />
	                languages/en_us/ITS4YouReports.php<br />
	                languages/es_es/ITS4YouReports.php<br />
	                languages/es_mx/ITS4YouReports.php<br />
	                languages/fr_fr/ITS4YouReports.php<br />
	                languages/hi_hi/ITS4YouReports.php<br />
	                languages/hu_hu/ITS4YouReports.php<br />
	                languages/it_it/ITS4YouReports.php<br />
	                languages/nl_nl/ITS4YouReports.php<br />
	                languages/pl_pl/ITS4YouReports.php<br />
	                languages/pt_br/ITS4YouReports.php<br />
	                languages/ro_ro/ITS4YouReports.php<br />
	                languages/ru_ru/ITS4YouReports.php<br />
	                languages/sk_sk/ITS4YouReports.php<br />
	                languages/tr_tr/ITS4YouReports.php<br />";
                }
            }
            $result = array('success' => true);
        }

        ob_clean();
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
