/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_Widget_Js('Vtiger_Getreports_Widget_Js',{},{

	generateChartData : function() {
    
		var container = this.getContainer();

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
        
        // ITS4YOU-CR SlOl 10. 3. 2016 9:30:33
        var thisInstance = this;
        var widgetContainer = thisInstance.getContainer();
        var reportid = widgetContainer.find('#widgetReports4YouId').val();
        var fieldElement = jQuery("#SelectPrimarySearchWidget"+reportid);

		app.showSelect2ElementView(fieldElement);
        app.destroyChosenElement(fieldElement);
        
        headerElement = jQuery("#dashboardWidgetHeader"+reportid);
        app.changeSelectElementView(headerElement);
        fieldElement.on('change', function(e) {
    		//var value = jQuery(e.currentTarget).val();
            //thisInstance.refreshWidget();
            var searchElement = jQuery(e.currentTarget);
    		thisInstance.registerClikOnLink(searchElement);
		})
        //thisInstance.registerClikOnLink();
        // ITS4YOU-END
    },
    
    // ITS4YOU-CR SlOl 10. 3. 2016 9:31:11
    registerClikOnLink: function(searchElement) {
        var thisInstance = this;
        var element = jQuery(searchElement);
        if(typeof element != 'undefined'){
            var widgetContainer = thisInstance.getContainer();
            var reportid = widgetContainer.find('#widgetReports4YouId').val();

            var primarySearchBy = element.val();
            if(null === primarySearchBy) {
                primarySearchBy = '';
            }
            
            var params = {
                'module' : 'ITS4YouReports',
                'view' :'ShowWidget',
                'name' : 'GetReports',
                'reportid'  : reportid,
                'record'  : reportid,
                'mode'  : 'widget',
                'primarySearchBy': primarySearchBy
            }
            
            jQuery('#reports4you_widget_'+reportid).html('');
            
            var loadingMessage = app.vtranslate('Loading','ITS4YouReports');
            var progressIndicatorElement = jQuery.progressIndicator({
                                        'message' : loadingMessage,
                                        'position' : 'html',
                                        'blockInfo' : {
                                            'enabled' : true
                                        }
                                });
//alert(JSON.stringify(params));
//window.open(JSON.stringify(params));
            AppConnector.request(params).then(function(data) {
                progressIndicatorElement.progressIndicator({
                    'mode' : 'hide'
                })
                if(data) {
                    jQuery.globalEval(data);
                    
                }
            });
        }

    },
    // ITS4YOU-END

	loadChart : function() {
            $(function () {
                
            });
	}
});
