<?php

class ITS4YouReports_Uninstall_Action extends Settings_Vtiger_Basic_Action {

    function __construct() {
        parent::__construct();
    }

    function process(Vtiger_Request $request) {

        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');
        $adb = PearDatabase::getInstance();
        $module = Vtiger_Module::getInstance('ITS4YouReports');
        if ($module) {

            $ITS4YouReportsModel = new ITS4YouReports_ITS4YouReports_Model();

            $tabid = $ITS4YouReportsModel->getId();

            $module->delete();
            @shell_exec('rm -r modules/ITS4YouReports');
            @shell_exec('rm -r layouts/v7/modules/ITS4YouReports');
            @shell_exec('rm -r layouts/vlayout/modules/ITS4YouReports');
            @shell_exec('rm -f languages/ar_ae/ITS4YouReports.php');
            @shell_exec('rm -f languages/cz_cz/ITS4YouReports.php');
            @shell_exec('rm -f languages/de_de/ITS4YouReports.php');
            @shell_exec('rm -f languages/en_gb/ITS4YouReports.php');
            @shell_exec('rm -f languages/en_us/ITS4YouReports.php');
            @shell_exec('rm -f languages/es_co/ITS4YouReports.php');
            @shell_exec('rm -f languages/es_es/ITS4YouReports.php');
            @shell_exec('rm -f languages/es_mx/ITS4YouReports.php');
            @shell_exec('rm -f languages/es_ve/ITS4YouReports.php');
            @shell_exec('rm -f languages/fi_fi/ITS4YouReports.php');
            @shell_exec('rm -f languages/fr_fr/ITS4YouReports.php');
            @shell_exec('rm -f languages/hi_hi/ITS4YouReports.php');
            @shell_exec('rm -f languages/hu_hu/ITS4YouReports.php');
            @shell_exec('rm -f languages/it_it/ITS4YouReports.php');
            @shell_exec('rm -f languages/nl_nl/ITS4YouReports.php');
            @shell_exec('rm -f languages/pl_pl/ITS4YouReports.php');
            @shell_exec('rm -f languages/pt_br/ITS4YouReports.php');
            @shell_exec('rm -f languages/ro_ro/ITS4YouReports.php');
            @shell_exec('rm -f languages/ru_ru/ITS4YouReports.php');
            @shell_exec('rm -f languages/sk_sk/ITS4YouReports.php');
            @shell_exec('rm -f languages/sv_se/ITS4YouReports.php');
            @shell_exec('rm -f languages/tr_tr/ITS4YouReports.php');

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

            if ($tabid != "") {
                Vtiger_Cron::deregister('Schedule Reports4You');
                $adb->pquery("DELETE FROM vtiger_tab WHERE tabid=?", [$tabid]);

                $adb->pquery("DELETE FROM vtiger_links WHERE linkurl LIKE '%ITS4YouReports%'", []);
            }

            $adb->query("DELETE FROM vtiger_module_dashboard_widgets WHERE linkid IN (SELECT linkid FROM vtiger_links WHERE linkurl like '%ITS4YouReports%');");
            $adb->query("DELETE FROM vtiger_links WHERE linkurl like '%ITS4YouReports%';");

            $result = ['success' => true];
        } else {
            $result = ['success' => false];
        }
        ob_clean();
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
