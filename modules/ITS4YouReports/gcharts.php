<?php

require_once('modules/ITS4YouReports/ITS4YouReports.php');
class Gcharts {

    public $library_loaded = FALSE;
    public $create_div = TRUE;
    public $dashboard_div = NULL;
    public $class_dashboard_div = NULL;
    public $filter_div = NULL;
    public $class_filter_div = NULL;
    public $chart_div = NULL;
    public $class_chart_div = NULL;
    public $open_js_tag = TRUE;
    public $graphic_type = 'LineChart'; //LineChart,PieChart,ColumnChart,AreaChart,TreeMap,ScatterChart,Gauge,GeoChart,ComboChart,BarChart,CandlestickChart,Table
    public $chart_type = 'horizontal';
    public $control_type = 'NumberRangeFilter';
    public $ch_image_name = ""; // ITS4YOU-CR SlOl | 15.7.2014 10:59 
    private $gen_options = array();
    private $control_options = array();
    private $use_dashboard = FALSE;
    public $report_filename = "";
    public $export_pdf_format = "A4";
    public $chart_column_uitype = "";
    public $chart_column_str = "";
    public $is_currency = "";

    function __contruct($array = array()) {
        $this->load($array);
    }

    public function load_options($options = array()) {
        if ((bool) !$options) {
            return false;
        }

        $this->options = $options;
        return TRUE;
    }

    public function load($array = array()) {
        if ((bool) !$array) {
            return false;
        }

        // ITS4YOU-UP SlOl | 15.7.2014 11:03 ch_image_name
        foreach (array('library_loaded', 'graphic_type', 'create_div', 'dashboard_div', 'filter_div', 'chart_div', 'class_filter_div', 'class_dashboard_div', 'class_chart_div', 'open_js_tag', 'control_type', 'ch_image_name') as $p) {
            if (isset($array[$p])) {
                if ($p == 'graphic_type') {
                    $this->set_graphic_type($array[$p]);
                    continue;
                }
                $this->$p = $array[$p];
            }
        }
    }

    public function load_library() {
        $return_script = "";
        if (!$this->library_loaded) {
            $this->library_loaded = TRUE;
            
            $onlin_jsapi = "https://www.google.com/jsapi";
            $local_jsapi = "modules/ITS4YouReports/jsapi.js";
            $load_script = true;

            if(file_get_contents($onlin_jsapi)!==false){
                $scipt_path = $onlin_jsapi;
            }elseif(file_exists($local_jsapi)){
                $scipt_path = $local_jsapi;
            }else{
                echo "<pre>";print_r("Save content of url: <a href='https://www.google.com/jsapi' target='_blank'>JS Api</a> to directory modules/ITS4YouReports/jsapi.js please !");echo "</pre>";
                die();
            }
            
            if($scipt_path){
                $return_script .='<script type="text/javascript" src="'.$scipt_path.'"></script>';
            }
        }
        return $return_script;
    }

    public function set_graphic_type($type = NULL) {
        if (is_null($type))
            return false;

        $type = strtolower(trim($type));

        $types = array(
            'linechart' => 'LineChart',
            'piechart' => 'PieChart',
            'columnchart' => 'ColumnChart',
            'areachart' => 'AreaChart',
            'treemap' => 'TreeMap',
            'scatterchart' => 'ScatterChart',
            'gauge' => 'Gauge',
            'geochart' => 'GeoChart',
            'combochart' => 'ComboChart',
            'barchart' => 'BarChart',
            'candlestickchart' => 'CandlestickChart',
            'table' => 'Table');

        if (!in_array($type, array_keys($types))) {
            exit('Error: Type of graph is not defined. [' . $type . ']');
        }

        $this->graphic_type = $types[$type];
        return true;
    }

    public function set_options($options = array()) {
        if ((bool) !$options) {
            return array();
        }

        $this->gen_options = $options;
        return true;
    }

    public function set_control_options($options = array()) {
        if ((bool) !$options) {
            return array();
        }

        $this->control_options = $options;
        return true;
    }

    public function generate($data) {
        if ((bool) !$data) {
            return false;
        }

        if (is_null($this->chart_div)) {
            //$key = $this->gerarkey(10);
            //$this->chart_div = 'gcharts_' . $key;
            $this->chart_div = 'innerchdiv';
        }

        if ($this->dashboard_div === TRUE) {
            $this->dashboard_div = 'dashboard_' . $key;
            $this->use_dashboard = TRUE;
        }

        if ($this->filter_div === TRUE) {
            $this->filter_div = 'filter_' . $key;
        }
        
        /*if ($this->use_dashboard === FALSE) {
            return $this->GenWithoutDashboard($data);
        } else {
            return $this->GenWithDashboard($data);
        }*/
        if ($this->use_dashboard === FALSE) {
            return $this->GenWithoutDashboard($data);
        }else{
			return $this->GenWithDashboard($data);
		}

        return false;
    }
    
    /*private function GenWithDashboard($data) {
        $js = NULL;

        $js .= $this->load_library() . "\n";

        if ($this->open_js_tag === TRUE) {
            $js .= '<script type="text/javascript">' . "\n";
        }

        // Load the Visualization API and the controls package.
        $js .= 'google.load("visualization", "1", {packages:["controls"]});' . "\n";

        // Set a callback to run when the Google Visualization API is loaded.
        $js .= 'google.setOnLoadCallback(drawDashboard);' . "\n";

        // Callback that creates and populates a data table,
        // instantiates a dashboard, a range slider and a pie chart,
        // passes in the data and draws it.
        $js .= 'function drawDashboard() {' . "\n";

        // Create our data table.
        $js .= 'var data = google.visualization.arrayToDataTable(' . $this->array_to_jsarray($data) . ');' . "\n";

        // Create a dashboard.
        $js .= "var dashboard = new google.visualization.Dashboard(document.getElementById('" . $this->dashboard_div . "'));\n";

        // Create a range slider, passing some options
        $js .= "var donutRangeSlider  = new google.visualization.ControlWrapper({
                  'controlType': '" . $this->control_type . "',
                  'containerId': '" . $this->filter_div . "',
                          'options': " . $this->array_to_jsobject($this->control_options) . "});\n";

        // Create a pie chart, passing some options

        $js .= "var options = new google.visualization.ChartWrapper({
                  'chartType': '" . $this->graphic_type . "',
                  'containerId': '" . $this->chart_div . "',
                  'options': " . $this->array_to_jsobject($this->gen_options) . "});\n";

        // Establish dependencies, declaring that 'filter' drives 'pieChart',
        // so that the pie chart will only display entries that are let through
        // given the chosen slider range.
        $js .= "dashboard.bind(donutRangeSlider , options);\n";

        // Draw the dashboard.
        $js .= "dashboard.draw(data);\n";

        $js .= '}';

        if ($this->open_js_tag === TRUE) {
            $js .= '</script>' . "\n";
        }

        /* CRIA AS DIVS * /
        if ($this->create_div === TRUE) {
            /* DASHBOARD DIV * /
            if (!is_null($this->dashboard_div)) {
                $js .= '<div id="' . $this->dashboard_div . '" class="' . $this->class_dashboard_div . '">';
            }

            /* FILTER DIV * /
            if (!is_null($this->filter_div)) {
                $js .= '<div id="' . $this->filter_div . '" class="' . $this->class_filter_div . '"></div>';
            }

 
            /* CHART DIV * /
            $js .= '<div id="' . $this->chart_div . '" class="' . $this->class_chart_div . '"></div>';

            /* DASHBOARD CLOSE DIV * /
            if (!is_null($this->dashboard_div)) {
                $js .= '</div>';
            }
        } // FIM CREATE DIV
        $this->clean();
        return $js;
    }*/

    private function GenWithoutDashboard($data) {
        $js = NULL;

        $js .= $this->load_library() . "\n";

        if ($this->open_js_tag === TRUE) {
            $js .= '<script type="text/javascript">' . "\n";
        }

        // Load the Visualization API and the controls package.
        $js .= 'google.load("visualization", "1", {packages:["corechart"]});' . "\n";

        // Set a callback to run when the Google Visualization API is loaded.
        $js .= 'google.setOnLoadCallback(drawChart);' . "\n";

        // Callback that creates and populates a data table,
        // instantiates a dashboard, a range slider and a pie chart,
        // passes in the data and draws it.
        $js .= 'function drawChart() {' . "\n";

		global $default_charset;
		
        if($this->chart_type=="funnel"){
            // Create our data table.
            if(!empty($data)){
                $values_array = $data_values_array = array();
                $spacing_array = array("");
                
                foreach($data as $data_key => $data_arr){
                    // $data_arr[0] = x axis
                    // $data_arr[1] = y axis
                    if($data_key==0){
                        $values_label = $data_arr[1];
                    }else{
                        // $data_arr[0] = label , $data_arr[1] = value
                        $values_array[$data_arr[0]] = $data_arr[1];
                    }
                }
                $s_uitypes = ITS4YouReports::$s_uitypes;
                if(in_array($this->chart_column_uitype, $s_uitypes) && $this->chart_column_str!=""){
                    global $current_user;
                    require_once 'modules/PickList/PickListUtils.php';
                    $roleid=$current_user->roleid;
                    $adb = PearDatabase::getInstance();
                    $column_str_arr = explode(":", $this->chart_column_str);
                    $column_name = $column_str_arr[1];
                    $picklistValues = getAssignedPicklistValues($column_name, $roleid, $adb);
                    $ordered_values_array = array();
                    foreach($picklistValues as $picklistValuesKey => $picklistValuesVal){
                        if(array_key_exists($picklistValuesKey, $values_array)){
                            $ordered_values_array[$picklistValuesKey] = $values_array[$picklistValuesKey];
                        }
                    }
                    if(!empty($ordered_values_array)){
                        $values_array = $ordered_values_array;
                    }
                }
                $data_values_array[] = $values_label;
                $max_data = max($values_array);
                foreach($values_array as $dv_key => $data_value){
                    if($max_data==$data_value){
                        $spacing_array[] = 0;
                    }if($max_data!=$data_value && is_numeric($data_value)){
                        $spacing_array[] = (($max_data-$data_value)/2);
                    }
                    $data_labels_array[] = $dv_key." [$data_value]";
                    $data_values_array[] = $data_value;
                }
            }
			
			$spacing_array = $this->array_to_jsarray($spacing_array);
			$data_values_array = $this->array_to_jsarray($data_values_array);
			$data_labels_array = $this->array_to_jsarray($data_labels_array);
			
			$js_spacing_array = html_entity_decode($spacing_array, ENT_QUOTES, $default_charset);
			$js_data_values_array = html_entity_decode($data_values_array, ENT_QUOTES, $default_charset);
			$js_data_labels_array = html_entity_decode($data_labels_array, ENT_QUOTES, $default_charset);

            $js .= '
            var data = new google.visualization.DataTable();

            var raw_data = ['.$js_spacing_array.',
                            '.$js_data_values_array.'];

            var data_values = '.$js_data_labels_array.';

            data.addColumn("string", "");
            for (var i = 0; i  < raw_data.length; ++i) {
                data.addColumn("number", raw_data[i][0]);    
            }

            data.addRows(data_values.length);

            for (var j = 0; j < data_values.length; ++j) {    
                data.setValue(j, 0, data_values[j].toString());    
            }

            for (var i = 0; i  < raw_data.length; ++i) {
                for (var j = 1; j  < raw_data[i].length; ++j) {
                    data.setValue(j-1, i+1, raw_data[i][j]);    
                }
            }
			';
/*
			var colors=["#3366cc","#dc3912","#ff9900","#109618","#990099","#0099c6","#dd4477","#66aa00","#b82e2e","#316395","#994499","#22aa99","#aaaa11","#6633cc","#e67300","#8b0707","#651067","#329262","#5574a6","#3b3eac","#b77322","#16d620","#b91383","#f4359e","#9c5935","#a9c413","#2a778d","#668d1c","#bea413","#0c5922","#743411"];
		    //the code
		    view.columns.sort(function (a, b) {
		        return (a - b);
		    });
		    chart.getOptions().series=[];
		    for(var i=1;i<view.columns.length;i++){
		        chart.getOptions().series.push({color:colors[view.columns[i]-1]});
		    }
		    //the code
*/
            $this->gen_options["isStacked"] = "true";
            $this->gen_options["colors"] = array('ffffff','3366CC');
            
        }else{
            // Create our data table.
            $js_array = $this->array_to_jsarray($data);
            $js_array = html_entity_decode($js_array, ENT_QUOTES, $default_charset);

            $js .= 'var data = google.visualization.arrayToDataTable(' .$js_array. ');' . "\n";
        }

        //Generate the options.
        $js .= 'var options = ' . "\n";
        $js .= $this->array_to_jsobject($this->gen_options);
        $js .= ';' . "\n";
        
        $js .= "var chart = new google.visualization." . $this->graphic_type . "(document.getElementById('" . $this->chart_div . "'));\n";
        $js .= 'chart.draw(data, options);' . "\n";
        // ITS4YOU-CR SlOl | 15.7.2014 9:46 
        $js .= 'var chart_image = chart.getImageURI();' . "\n";
        if($this->ch_image_name!=""){
            $file_path = 'test/ITS4YouReports/'.$this->ch_image_name.'.png';
//var aurl = "index.php?module=ITS4YouReports&action=ITS4YouReportsAjax&mode=ajax&file=SaveImage&filename='.$this->ch_image_name.'&filepath='.$file_path.'&report_filename='.$this->report_filename.'&export_pdf_format='.$this->export_pdf_format.'";
$js .= '
//var aurl = "index.php?module=ITS4YouReports&action=IndexAjax&mode=SaveImage&filename='.$this->ch_image_name.'&filepath='.$file_path.'&report_filename='.$this->report_filename.'&export_pdf_format='.$this->export_pdf_format.'";
var aurl = "modules/ITS4YouReports/SaveImage.php?filename='.$this->ch_image_name.'&filepath='.$file_path.'&report_filename='.$this->report_filename.'&export_pdf_format='.$this->export_pdf_format.'";
                    var ajax = new XMLHttpRequest();
                    ajax.open("POST",aurl,true);
                    ajax.setRequestHeader("Content-Type", "canvas/upload");
                    var postData = "canvasData=" + chart_image;
                    ajax.send(postData);
                    ajax.onreadystatechange=function(){
                        if (ajax.readyState==4  && ajax.status==200){
//window.open(ajax.responseText);
                            document.getElementById("chimgExport").setAttribute("onclick","SaveChartImg(\''.$file_path.'\',\''.$this->ch_image_name.'\')");
                            document.getElementById("chimgExport").style.visibility="visible";
                            var chartDivContent = document.getElementById("' . $this->chart_div . '").innerHTML;
                        }
                    }
                    ';
        }
        // ITS4YOU-END 15.7.2014 9:46 
        $js .= '}';
        /*
        $js .= "var my_div = document.getElementById('" . $this->chart_div . "_image');" . "\n";
        $js .= "var my_chart = new google.visualization." . $this->graphic_type . "(document.getElementById('" . $this->chart_div . "'));" . "\n";
        $js .= "alert(my_chart.getImageURI());" . "\n";
        $js .= "google.visualization.events.addListener(my_chart, 'ready', function () {" . "\n";
        $js .= "  my_div.innerHTML = '<img src=\'' + my_chart.getImageURI() + '\'>';" . "\n";
        $js .= "});" . "\n";
        $js .= "my_chart.draw(data,options);" . "\n";
        */
        
        if ($this->open_js_tag === TRUE) {
            $js .= '</script>' . "\n";
        }
        
        /* CRIA AS DIVS */
        if ($this->create_div === TRUE) {
            /* CHART DIV */
            if($this->graphic_type=="PieChart"){
                $style_params = "width:30%;margin:auto;";
            }else{
                $style_params = "width:90%;margin:auto;";
            }
            $js .= '<div id="outerchdiv" style="'.$style_params.'"><div id="' . $this->chart_div . '" class="' . $this->class_chart_div . '" ></div></div>';
            
            $js .= '<div id="' . $this->chart_div . '_image" class="' . $this->class_chart_div . '" style=border:1px solid red;"></div>';
        } // FIM CREATE DIV

        $this->clean();
        return $js;
    }

    /*
      @INPUT array:
      $array = array('title' => 'My Title');
      or
      $array = array('title' => 'My Title','vAxis' => array('title' => 'Cups'));

      @OUTPUT string:
      {title: 'title'}
      or
      {title: 'My Title',
      vAxis: {title: 'Cups'}}
     */

    private function array_to_jsobject($array = array()) {
        if ((bool) !$array) {
            return '{}';
        }

        $return = NULL;
        foreach ($array as $k => $v) {
            if (is_array($v) && $k!="colors") {
                $return .= $k . ": " . $this->array_to_jsobject($v) . ",";
            }elseif($k=="colors"){
                $return .= $k . ": ['" . implode("','", $v) . "'],";
            }
            /*if (is_array($v)) {
                $return .= $k . ": " . $this->array_to_jsobject($v) . ",";
            }*/else {
                if (is_string($v)) {
                    $return .= $k . ": '" . addslashes($v) . "',";
                } else {
                    $return .= $k . ": " . $v . ",";
                }
            }
        }
        return '{' . trim($return, ',') . '}';
    }

    /*
      @INPUT matriz:
      $array = array(array('Year', 'Sales', 'Expenses'),
      array('2004',1000,400),
      array('2005',1170,460),
      array('2006',660,1120),
      array('2007',1030,540));

      @OUTPUT string:
      [['Year','Sales','Expenses'],['2004','1000','400'],['2005','1170','460'],['2006','660','1120'],['2007','1030','540']]
     */

    private function array_to_jsarray($array = array()) {
        if ((bool) !$array) {
            return '[]';
        }

        $return = NULL;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return .= ',' . $this->array_to_jsarray($v);
            } else {
                if (is_string($v)) {
                    $return .= ",'" . addslashes($v) . "'";
                } else {
                    $return .= "," . $v;
                }
            }
        }

        return '[' . trim($return, ',') . ']';
    }

    public function clean() {
        //$this->library_loaded 		= FALSE;
        $this->create_div = TRUE;
        $this->dashboard_div = NULL;
        $this->class_dashboard_div = NULL;
        $this->filter_div = NULL;
        $this->class_filter_div = NULL;
        $this->chart_div = NULL;
        $this->class_chart_div = NULL;
        $this->open_js_tag = TRUE;
        $this->graphic_type = 'LineChart';
        $this->control_type = 'NumberRangeFilter';

        $this->gen_options = array();
        $this->control_options = array();
        $this->use_dashboard = FALSE;
    }

    public function is_number($num) {
        if ((bool) preg_match("/^([0-9\.])+$/i", $num))
            return true;
        else
            return false;
    }

    public function gerarkey($length = 40) {
        $key = NULL;
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRTWXYZ';
        for ($i = 0; $i < $length; ++$i) {
            $key .= $pattern{rand(0, 58)};
        }
        return $key;
    }

}

?>