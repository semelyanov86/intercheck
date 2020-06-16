<?php

class ITS4YouReports_SaveImage_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = ITS4YouReports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
        $response = new Vtiger_Response();
		$debug_fs = "";

        $reportsDeleteDenied = array();
		
		$ReportsTempDirectory = "test/ITS4YouReports/";
		
	    //$path_fix = "../../../";
	    $path_fix = "";

	    //$filename = $path_fix."test/ITS4YouReports/".$_REQUEST["filename"].".pdf";
	    $file_path = $_REQUEST["filepath"]; // "test/$filename.png"
	    if(is_writable($path_fix.$ReportsTempDirectory)){
            if(isset($_REQUEST["canvasData"]) && $_REQUEST["canvasData"]!=""){
    	       $unencodedData=$_REQUEST["canvasData"];
               file_put_contents($path_fix.$file_path, base64_decode($unencodedData));
            }
	       
            if(isset($_REQUEST["mode"]) && $_REQUEST["mode"]=="download"){
    		    if(file_exists($path_fix.'modules/PDFMaker/resources/mpdf/mpdf.php')){
    		        require_once $path_fix.'modules/PDFMaker/resources/mpdf/mpdf.php';
    		        $export_pdf_format = $_REQUEST['export_pdf_format'];
    		        $mpdf=new mPDF('utf-8', "$export_pdf_format", "", "", "5", "5", "0", "5", "5", "5");
    		        $mpdf->keep_table_proportions = true;
    		        $mpdf->SetAutoFont();
    		
    		        $filename = $ReportsTempDirectory.$_REQUEST['report_filename'];
    		        $filename = html_entity_decode($filename, ENT_COMPAT, $default_charset);
    	            $filename = $path_fix.$filename;
    				
    		        if (is_file($filename)) {
    		            $mpdf->AddPage();
    		            $mpdf->SetImportUse();
    		            $pagecount = $mpdf->SetSourceFile($filename);
    		            if ($pagecount > 0) {
    		                for ($i = 1; $i <= $pagecount; $i++) {
    		                    $tplId = $mpdf->ImportPage($i);
    		                    $mpdf->UseTemplate($tplId);
    		                    if ($i < $pagecount)
    		                        $mpdf->AddPage();
    		                }
    		            }
                        
    		            if(file_exists($path_fix.$file_path)==true){
    		                $mpdf->AddPage('L');
    		                $ch_image_html .= "<div style='width:100%;text-align:center;'><table class='rptTable' style='border:0px;padding:0px;margin:auto;width:80%;text-align:center;' cellpadding='5' cellspacing='0' align='center'>
    		                            <tr>
    		                                <td class='rpt4youGrpHead0' nowrap='' >";
    		                $ch_image_html .= "<img src='$site_URL".$file_path."' />";
    		                $ch_image_html .= "</td>
    		                            </tr>
    		                        </table></div>";
    		                $mpdf->WriteHTML($ch_image_html);
    		            }
    		            //$mpdf->Output($filename,'F');
                        $mpdf->Output($_REQUEST['report_filename'],'D');
    		        }
    		    }
            }
		}

        //$response->setResult(array("done-P"));
        
		//$response->emit();
	}
}
