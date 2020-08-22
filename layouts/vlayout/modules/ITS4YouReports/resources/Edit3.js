/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

ITS4YouReports_Edit_Js("ITS4YouReports_Edit3_Js",{},{
	
	step3Container : false,
	
	advanceFilterInstance : false,
	
	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the report step3 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step3Container;
	},

	/**
	 * Function to set the report step3 container
	 * @params : element - which represents the report step3 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step3Container = element;
		return this;
	},
	
	/**
	 * Function  to intialize the reports step3
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#report_step3');
		}
		
		if(container.is('#report_step3')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#report_step3'));
		}
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
	},
	
	registerSubmitEvent : function(){
		var thisInstance = this;
		var form = this.getContainer();
		form.submit(function(e){
			thisInstance.calculateValues();
		});
	},
	
	registerEvents : function(){
		var container = this.getContainer();
		app.changeSelectElementView(container);
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
		this.registerSubmitEvent();
		container.validationEngine();
	}
});
	



