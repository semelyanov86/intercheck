<?php

if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
{
    $path_fix = "../../";
    require_once($path_fix.'config.inc.php');
    require_once($path_fix.'include/utils/VtlibUtils.php');
    // Get the data
    $imageData=$GLOBALS['HTTP_RAW_POST_DATA'];
    
    $imageData_arr = explode("data:image/png;base64,", $imageData);
    $unencodedData = $imageData_arr[1];

    // Save file. This example uses a hard coded filename for testing,
    // but a real application can specify filename in POST variable
    $filename = $_REQUEST["filename"];
    $file_path = $_REQUEST["filepath"]; // "test/$filename.png"
    if(is_writable($path_fix."test/ITS4YouReports/")){
            file_put_contents($path_fix.$file_path, base64_decode($unencodedData));
	    if(file_exists($path_fix.'modules/PDFMaker/resources/mpdf/mpdf.php')){
//	        global $default_charset;
//	        global $site_URL;
	        require_once $path_fix.'modules/PDFMaker/resources/mpdf/mpdf.php';
	        $export_pdf_format = $_REQUEST['export_pdf_format'];
	        $mpdf=new mPDF('utf-8', "$export_pdf_format", "", "", "5", "5", "0", "5", "5", "5");
	        $mpdf->keep_table_proportions = true;
	        $mpdf->SetAutoFont();
	
	        $filename = $_REQUEST['report_filename'];
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
	            $mpdf->Output($filename,'F');
	        }
	    }
	}
}
?>