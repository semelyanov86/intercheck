<?php

require_once('modules/ITS4YouReports/GenerateObj.php');

class ITS4YouReports_GeneratePDF_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
        //global $adb;$adb->setDebug(true);
		/*$moduleName = $request->getModule();
		$moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
        */
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $response = new Vtiger_Response();
		$debug_fs = $report_chartpdf = "";
		@ob_clean();

        if (vtlib_isModuleActive("PDFMaker") === true && file_exists('modules/PDFMaker/resources/mpdf/mpdf.php')) {
            if (file_exists("modules/ITS4YouReports/classes/Reports4YouDefault.css")) {
                $report_html_style = file_get_contents("modules/ITS4YouReports/classes/Reports4YouDefault.css");
            }
            
            $report_html = $request->get("form_report_html");

            $report_head = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
                        <html>
                          <head>
                          </head>
                          <body>";
            //$report_htmlpdf = "<div>$report_html</div>";
            if ($request->has('form_chart_canvas') && '' !== $request->get('form_chart_canvas')) {
                $chart_image = "data:image/png;base64," . $request->get('form_chart_canvas');

                $report_chartpdf = "
                <div style='height:21cm;text-align:center;'><img src='" . $chart_image . "'></div>";
            }
            $report_foot = "</body>
                        </html>";

            require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';
//                     $mpdf = new mPDF('',    // mode - default ''
//                      2	 '',    // format - A4, for example, default ''
//                      3	 0,     // font size - default 0
//                      4	 '',    // default font family
//                      5	 15,    // margin_left
//                      6	 15,    // margin right
//                      7	 16,     // margin top
//                      8	 16,    // margin bottom
//                      9	 9,     // margin header
//                      10	 9,     // margin footer
//                      11	 'L');  // L - landscape, P - portrait 
            $report_filename = $request->get('form_filename').".pdf";

            $export_pdf_format = $request->get('form_export_pdf_format');
            // class mPDF ([ string $mode [, mixed $format [, float $default_font_size [, string $default_font [, float $margin_left , float $margin_right , float $margin_top , float $margin_bottom , float $margin_header , float $margin_footer [, string $orientation ]]]]]])
            $mpdf = new mPDF('utf-8', "$export_pdf_format", "", "", "5", "5", "0", "5", "5", "5");
            // Portrait = $mpdf=new mPDF('utf-8', 'A4');
            // Landscape = $mpdf=new mPDF('utf-8', 'A4-L');
            $mpdf->keep_table_proportions = true;
            if(number_format(mPDF_VERSION)<6){
                $mpdf->SetAutoFont();
            }

            if ('v7' !== $layout) {
                $mpdf->WriteHTML($report_html_style, 1);
            }

            $mpdf->WriteHTML($report_head);

            if ($request->has("form_report_name") && $request->get("form_report_name") !== "") {
                $form_report_name = $request->get('form_report_name');
                $mpdf->WriteHTML(GenerateObj::placeReportNameToPDF($form_report_name));
                $form_report_name .= '.pdf';
            }

            $mpdf->WriteHTML($report_html);

            $report_totals = "<br />" . $request->get("form_report_totals");
            $report_totals_arr = explode("<!--LIMIT_INF-->", $report_totals);
            if (count($report_totals_arr) > 1) {
                $report_totals = $report_totals_arr[0] . $report_totals_arr[2];
            }
            $mpdf->WriteHTML($report_totals);

            if ($report_chartpdf != "") {
                $mpdf->AddPage('L');
                $mpdf->WriteHTML($report_chartpdf);
            }

            $mpdf->WriteHTML($report_foot);
            $mpdf->Output($form_report_name, 'D');
            exit;
        }
	}
}
