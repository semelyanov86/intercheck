/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

jQuery.Class('ITS4You_Barchat_Widget_Js',{
    	widgetPostLoadEvent : 'Vtiget.Dashboard.PostLoad',
	widgetPostRefereshEvent : 'Vtiger.Dashboard.PostRefresh',

	getInstance : function(container, widgetName, moduleName) {
            if(typeof moduleName == 'undefined') {
			moduleName = app.getModuleName();
		}
		var widgetClassName = widgetName.toCamelCase();
		var moduleClass = window[moduleName+"_"+widgetClassName+"_Widget_Js"];
		var fallbackClass = window["Vtiger_"+widgetClassName+"_Widget_Js"];

		var basicClass = Vtiger_Widget_Js;
		if(typeof moduleClass != 'undefined') {
			var instance = new moduleClass(container);
		}else if(typeof fallbackClass != 'undefined') {
			var instance = new fallbackClass(container);
		} else {
			var instance = new basicClass(container);
		}
		return instance;
	}

},{

        generateChartData : function() {
		var container = this.getContainer();
//alert(container.html());

		var jData = container.find('.widgetData').val();
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