/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

/*
Vtiger_Widget_Js('ITS4You_Barchat_Widget_Js',{},{

	generateChartData : function() {
		var container = this.getContainer();

		var jData = container.find('#data').val();
		var data = JSON.parse(jData);

		var chartData = [];
		var xLabels = new Array();
		var yMaxValue = 0;
		for(var index in data) {
			var row = data[index];
			row[0] = parseInt(row[0]);
			xLabels.push(app.getDecodedValue(row[1]))
			chartData.push(row[0]);
			if(parseInt(row[0]) > yMaxValue){
				yMaxValue = parseInt(row[0]);
			}
		}
        // yMaxValue Should be 25% more than Maximum Value
		yMaxValue = yMaxValue + 2 + (yMaxValue/100)*25;
		return {'chartData':[chartData], 'yMaxValue':yMaxValue, 'labels':xLabels};
	},
    
     postLoadWidget: function() {
		this._super();
        var thisInstance = this;

		this.getContainer().on('jqplotDataClick', function(ev, gridpos, datapos, neighbor, plot) {
                        var jData = thisInstance.getContainer().find('.widgetData').val();
			var data = JSON.parse(jData);
			var linkUrl = data[datapos]['links'];
			if(linkUrl) window.location.href = linkUrl;
		});

		this.getContainer().on("jqplotDataHighlight", function(evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css( 'cursor', 'pointer' );
		});
		this.getContainer().on("jqplotDataUnhighlight", function(evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css( 'cursor', 'auto' );
		});
    },

	loadChart : function() {
		var data = this.generateChartData();

                this.getPlotContainer(false).jqplot(data['chartData'] , {
			title: data['title'],
			animate: !$.jqplot.use_excanvas,
			seriesDefaults:{
				renderer:jQuery.jqplot.BarRenderer,
				rendererOptions: {
					showDataLabels: true,
					dataLabels: 'value',
					barDirection : 'vertical'
				},
				pointLabels: {show: true,edgeTolerance: -15}
			},
			 axes: {
				xaxis: {
					  tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
					  renderer: jQuery.jqplot.CategoryAxisRenderer,
					  ticks: data['labels'],
					  tickOptions: {
						angle: -45
					  }
				},
				yaxis: {
					min:0,
					max: data['yMaxValue'],
					tickOptions: {
						formatString: '%d'
					},
					pad : 1.2
				}
			},
			legend: {
                show		: (data['data_labels']) ? true:false,
                location	: 'e',
                placement	: 'outside',
				showLabels	: (data['data_labels']) ? true:false,
				showSwatch	: (data['data_labels']) ? true:false,
				labels		: data['data_labels']
            }
		});
//		this.getPlotContainer(false).on('jqPlotDataClick', function(){
//			console.log('here');
//		});
//		jQuery.jqplot.eventListenerHooks.push(['jqPlotDataClick', myClickHandler]);
	}

//	registerSectionClick : function() {
//		this.getPlotContainer(false);
//	}
});
*/
/*
jQuery.Class("ITS4YouReports_ChartDetail_Js",{

	/**
	 * Function used to display message when there is no data from the server
	 * /
	displayNoDataMessage : function() {
		$('#chartcontent').html('<div>'+app.vtranslate('JS_NO_CHART_DATA_AVAILABLE')+'</div>').css(
								{'text-align':'center', 'position':'relative', 'top':'100px'});
	},

	/**
	 * Function returns if there is no data from the server
	 * /
	isEmptyData : function() {
		var jsonData = jQuery('input[name=data]').val();
		var data = JSON.parse(jsonData);
		var values = data['values'];
		if(jsonData == '' || values == '') {
			return true;
		}
		return false;
	}
},{
        initialize: function() {
            alert("init CHD.js");
        },

	/**
	 * Function returns instance of the chart type
	 * /
	getInstance : function() {
		var chartType = jQuery('input[name=charttype]').val();
		var chartClassName = chartType.toCamelCase();
		var chartClass = window["ITS4YouReports_"+chartClassName + "_Js"];

		var instance = false;
		if(typeof chartClass != 'undefined') {
			instance = new chartClass();
			instance.postInitializeCalls();
		}
		return instance;
	},

	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;

		jQuery('.generateReport').on('click',function(e){
			if(!jQuery('#chartDetailForm').validationEngine('validate')) {
				e.preventDefault();
				return false;
			}

			var advFilterCondition = thisInstance.calculateValues();
			var recordId = thisInstance.getRecordId();
			var currentMode = jQuery(e.currentTarget).data('mode');
			var postData = {
				'advanced_filter': advFilterCondition,
				'record' : recordId,
				'view' : "ChartSaveAjax",
				'module' : app.getModuleName(),
				'mode' : currentMode,
				'charttype' : jQuery('input[name=charttype]').val(),
				'groupbyfield' : jQuery('#groupbyfield').val(),
				'datafields' : jQuery('#datafields').val()
			};

			var reportChartContents = thisInstance.getContentHolder().find('#reportContentsDiv');
			var element = jQuery('<div></div>');
			element.progressIndicator({
				'position':'html',
				'blockInfo': {
					'enabled' : true,
					'elementToBlock' : reportChartContents
				}
			});

			e.preventDefault();

			AppConnector.request(postData).then(
				function(data){
					element.progressIndicator({'mode' : 'hide'});
					reportChartContents.html(data);
					thisInstance.registerEventForChartGeneration();
				}
			);
		});


	},

	registerEventForChartGeneration : function() {
		var thisInstance = this;
		try {
			thisInstance.getInstance();	// instantiate the object and calls init function
			jQuery('#chartcontent').trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
		} catch(error) {
			console.log("error");
			console.log(error);
			ITS4YouReports_ChartDetail_Js.displayNoDataMessage();
			return;
		}
	},

	registerEventForModifyCondition : function() {
		jQuery('button[name=modify_condition]').on('click', function() {
			var filter = jQuery('#filterContainer');
			var icon = jQuery(this).find('i');
			var classValue = icon.attr('class');
			if(classValue == 'icon-chevron-right') {
				icon.removeClass('icon-chevron-right').addClass('icon-chevron-down');
				filter.show('slow');
			} else {
				icon.removeClass('icon-chevron-down').addClass('icon-chevron-right');
				filter.hide('slow');
			}
			return false;
		});
	},

	registerEvents : function(){
alert("WTF");
//		this._super();
		this.registerEventForModifyCondition();
		this.registerEventForChartGeneration();
		//ITS4YouReports_ChartEdit3_Js.registerFieldForChosen();
		//ITS4YouReports_ChartEdit3_Js.initSelectValues();
		//jQuery('#chartDetailForm').validationEngine(app.validationEngineOptions);
	}
});*/

//Vtiger_Barchat_Widget_Js('ITS4YouReports_Verticalbarchart_Widget_Js', {},{
ITS4YouReports_Chartdetail_Widget_Js('ITS4YouReports_Barchat_Widget_Js', {},{

	postInitializeCalls : function() {
		jQuery('table.jqplot-table-legend').css('width','95px');
		var thisInstance = this;

		this.getContainer().on('jqplotDataClick', function(ev, gridpos, datapos, neighbor, plot) {
			var linkUrl = thisInstance.data['links'][neighbor[0]-1];
			if(linkUrl) window.location.href = linkUrl;
		});

		this.getContainer().on("jqplotDataHighlight", function(evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css( 'cursor', 'pointer' );
		});
		this.getContainer().on("jqplotDataUnhighlight", function(evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css( 'cursor', 'auto' );
		});
	},

	postLoadWidget : function() {
		if(!Reports_ChartDetail_Js.isEmptyData()) {
			this.loadChart();
		}else{
			this.positionNoDataMsg();
		}
		this.postInitializeCalls();
	},

	positionNoDataMsg : function() {
		Reports_ChartDetail_Js.displayNoDataMessage();
	},

	getPlotContainer : function(useCache) {
		if(typeof useCache == 'undefined'){
			useCache = false;
		}

		if(this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = jQuery('#chartcontent');
		}
		return this.plotContainer;
	},

	init : function() {
alert("si myslis co");
            this._super(jQuery('#reportContentsDiv'));
	},

	generateChartData : function() {
		if(Reports_ChartDetail_Js.isEmptyData()) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return false;
		}

		var jsonData = jQuery('input[name=data]').val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		if(data['type'] == 'singleBar') {
			chartData[0] = [];
			for(var i in values) {
				var multiValue = values[i];
				for(var j in multiValue) {
					chartData[0].push(multiValue[j]);
					if(multiValue[j] > yMaxValue) yMaxValue = multiValue[j];
				}
			}
		} else {
			chartData[0] = [];
			chartData[1] = [];
			chartData[2] = [];
			for(var i in values) {
				var multiValue = values[i];
				var info = [];
				for(var j in multiValue) {
					chartData[j].push(multiValue[j]);
					if(multiValue[j] > yMaxValue) yMaxValue = multiValue[j];
				}
			}
		}
		yMaxValue = yMaxValue + (yMaxValue*0.15);

		return {'chartData':chartData,
				'yMaxValue':yMaxValue,
				'labels':data['labels'],
				'data_labels':data['data_labels'],
				'title' : data['graph_label']
			};
	}
});

jQuery(document).ready(function() {
var ITS4YouReports_Chartdetail_Widget_Js = ITS4YouReports_Verticalbarchart_Widget_Js.getInstance(jQuery('#chartcontent'),'ChartDetail');
    alert(ITS4YouReports_Chartdetail_Widget_Js.registerEvents());
// var plot = ITS4YouReports_Verticalbarchart_Js_Instance.getPlotContainer();
});

alert("oldo");