/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_Detail_Js("ITS4YouReports_Detail_Js",{},{
	advanceFilterInstance : false,
	detailViewContentHolder : false,
	HeaderContentsHolder : false, 
	
	
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.contentsDiv');
		}
		return this.detailViewContentHolder;
	},
	
	getHeaderContentsHolder : function(){
		if(this.HeaderContentsHolder == false) {
			this.HeaderContentsHolder = jQuery('div.reportsDetailHeader ');
		}
		return this.HeaderContentsHolder;
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		// var advfilterlist = this.advanceFilterInstance.getValues();
		// return JSON.stringify(advfilterlist);
            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            var return_filters = "";
            var escapedOptions = new Array('account_id', 'contactid', 'contact_id', 'product_id', 'parent_id', 'campaignid', 'potential_id', 'assigned_user_id1', 'quote_id', 'accountname', 'salesorder_id', 'vendor_id', 'time_start', 'time_end', 'lastname');
            var conditionColumns = vt_getElementsByName('tr', "conditionColumn");
            var criteriaConditions = [];
            
            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
            for (var i = 0; i < conditionColumns.length; i++) {
                var columnRowId = conditionColumns[i].getAttribute("id");
                var columnRowInfo = columnRowId.split("_");
                var columnGroupId = columnRowInfo[1];
                var columnIndex = columnRowInfo[2];

                if (columnGroupId != "0")
                    ctype = "f";
                else
                    ctype = "g";

                var columnId = ctype + "col" + columnIndex;
                var columnObject = getObj(columnId);
                var selectedColumn = html_entity_decode(columnObject.value,"UTF-8");
                var selectedColumnIndex = columnObject.selectedIndex;
                var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
                if (columnObject.options[selectedColumnIndex].value != "none") {
                    var comparatorId = ctype + "op" + columnIndex;
                    var comparatorObject = getObj(comparatorId);
                    var comparatorValue = comparatorObject.value;

                    var valueId = ctype + "val" + columnIndex;
                    var valueObject = getObj(valueId);
                    var specifiedValue = valueObject.value;

                    var extValueId = ctype + "val_ext" + columnIndex;
                    var extValueObject = getObj(extValueId);
                    if (extValueObject) {
                        extendedValue = extValueObject.value;
                    }

                    var glueConditionId = ctype + "con" + columnIndex;
                    var glueConditionObject = getObj(glueConditionId);
                    var glueCondition = '';
                    if (glueConditionObject) {
                        glueCondition = glueConditionObject.value;
                    }
                    
                    // ITS4YOU-CR SlOl 30. 5. 2016 7:43:38
                    var fvalhdnId = "fvalhdn" + columnIndex;
                    var hdnConditionObject = getObj(fvalhdnId);
                    var hdnValue = '';
                    if (hdnConditionObject) {
                        hdnValue = hdnConditionObject.value;
                    }
                    // ITS4YOU-END

                    if(conditionColumns.length>0){
                        if (!emptyCheck4You(columnId, " Column ", "text")){
                            // i < conditionColumns.length
                            return false;
                        }
                        if (!emptyCheck4You(comparatorId, selectedColumnLabel + " Option", "text")){
                            return false;
                        }
                    }

                    var col = selectedColumn.split(":");

                    var std_filter_columns = document.getElementById("std_filter_columns").value;
                    if (typeof std_filter_columns != 'undefined' && std_filter_columns!=""){
                        var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
                    }else{
                        var std_filter_columns_arr = new Array();
                    }

                    if (std_filter_columns_arr.indexOf(selectedColumn) > -1) {
                        if(comparatorValue=="custom"){
                            if (!emptyCheck4You("jscal_field_sdate_val_"+columnIndex, " Column ", "text")){
                                return false;
                            }
                        }
                        if(comparatorValue=="custom"){
                            if (!emptyCheck4You("jscal_field_edate_val_"+columnIndex, " Column ", "text")){
                                return false;
                            }
                        }
                        if( comparatorValue=="olderNdays" ||comparatorValue=="lastNdays" || comparatorValue=="nextNdays" || comparatorValue=="moreNdays" || comparatorValue=="daysago" || comparatorValue=="daysmore"){
                            if (!emptyCheck4You("nfval"+columnIndex, " Column ", "text")){
                                return false;
                            }
                        }
                        if( comparatorValue=="olderNdays" ||comparatorValue=="lastNdays" || comparatorValue=="nextNdays" || comparatorValue=="moreNdays" || comparatorValue=="daysago" || comparatorValue=="daysmore"){
                            var specifiedValue = document.getElementById("nfval"+columnIndex).value;
                        }else{
                            if(jQuery.inArray(comparatorValue,JSON.parse(jQuery('#fld_date_options').val()))>-1){
                                if (!emptyCheck4You(fvalhdnId, selectedColumnLabel)){
                                    return false;
                                }
                            }else{
                                if(comparatorValue=="custom"){
                                    //var start_date = document.getElementById("jscal_field_sdate_val_"+columnIndex).value;
                                    //var end_date = document.getElementById("jscal_field_edate_val_"+columnIndex).value;
                                    var start_date = this.getDbFormatedDateValue(jQuery('#jscal_field_sdate_val_'+columnIndex));
                                    var end_date = this.getDbFormatedDateValue(jQuery('#jscal_field_edate_val_'+columnIndex));
                                }else{
                                    var start_date = document.getElementById("jscal_field_sdate"+columnIndex).value;
                                    var end_date = document.getElementById("jscal_field_edate"+columnIndex).value;
                                }
                                var specifiedValue = start_date+"<;@STDV@;>"+end_date;
                            }
                        }
                    }else{
                        if(comparatorValue!="isn" && comparatorValue!="isnn"){
                            if (!emptyCheck4You("fval"+columnIndex, " Value "+(parseInt(columnIndex)+1)+" ", "text")){
                                return false;
                            }
                        }
                        if(col[1]=="currency_id"){
                            if (!emptyCheck4You(columnId, " Column ", "text")){
                                return false;
                            }
                        }else if (escapedOptions.indexOf(col[3]) == -1) {
                            if (col[4] == 'T') {
                                var datime = specifiedValue.split(" ");
                                if (!re_dateValidate(datime[0], selectedColumnLabel + " (Current User Date Time Format)", "OTH"))
                                    return false
                                if (datime.length > 1)
                                    if (!re_patternValidate(datime[1], selectedColumnLabel + " (Time)", "TIMESECONDS"))
                                        return false
                            }
                            else if (col[4] == 'D')
                            {
                                if (!dateValidate(valueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                                    return false
                                if (extValueObject) {
                                    if (!dateValidate(extValueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                                        return false
                                }
                            } else if (col[4] == 'I')
                            {
                                if (!intValidate(valueId, selectedColumnLabel + " (Integer Criteria)" + i))
                                    return false
                            } else if (col[4] == 'N')
                            {
                                if (!numValidate(valueId, selectedColumnLabel + " (Number) ", "any", true))
                                    return false
                            } else if (col[4] == 'E')
                            {
                                if (!patternValidate(valueId, selectedColumnLabel + " (Email Id)", "EMAIL"))
                                    return false
                            }
                            // ITS4YOU-CR SlOl 28. 3. 2014 8:39:20
                            if (sel_fields[selectedColumn]) {
                                // fop0
                                // fval0_|_3
                                if(comparatorValue=="e" || comparatorValue=="n"){
                                    var selectElement = jQuery('#s_fval'+columnIndex);
                                    specifiedValue = selectElement.val();
                                }else{
                                    var selectElement = jQuery('#fval'+columnIndex);
                                    specifiedValue = selectElement.val();
                                }
                            }
                            // ITS4YOU-END 28. 3. 2014 8:39:13
                        }
                    }

                    if (extValueObject && extendedValue != null && extendedValue != '')
                        specifiedValue = specifiedValue + ',' + extendedValue;
                        criteriaConditions[columnIndex] = {"groupid": columnGroupId,
                        "columnname": selectedColumn,
                        "comparator": comparatorValue,
                        "value": specifiedValue,
                        "value_hdn": hdnValue,
                        "column_condition": glueCondition
                    };
//alert(JSON.stringify(criteriaConditions));

                }
            }
            var advft_criteria_value = JSON.stringify(criteriaConditions);
            for(var ri =0; ri<criteriaConditions.length; ri++){
                advft_criteria_value = advft_criteria_value.replace("&", "<@AMPKO@>");
            }
            //var advft_criteria_value = criteriaConditions;
            return_filters += "advft_criteria="+advft_criteria_value;

            var conditionGroups = vt_getElementsByName('div', "conditionGroup");
            var criteriaGroups = [];
            for (var i = 0; i < conditionGroups.length; i++)
            {
                var groupTableId = conditionGroups[i].getAttribute("id");
                var groupTableInfo = groupTableId.split("_");
                var groupIndex = groupTableInfo[1];

                var groupConditionId = "gpcon" + groupIndex;
                var groupConditionObject = getObj(groupConditionId);
                var groupCondition = '';
                if (groupConditionObject) {
                    groupCondition = groupConditionObject.value;
                }
                criteriaGroups[groupIndex] = {"groupcondition": groupCondition};
            }
            var advft_criteria_groups_value = JSON.stringify(criteriaGroups);
            //var advft_criteria_groups_value = criteriaGroups;
            return_filters += "&advft_criteria_groups="+advft_criteria_groups_value;

            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            var GroupconditionColumns = vt_getElementsByName('tr', "groupconditionColumn");
            var GroupcriteriaConditions = [];
            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            for (var i = 0; i < GroupconditionColumns.length; i++) {

                var columnRowId = GroupconditionColumns[i].getAttribute("id");
                var columnRowInfo = columnRowId.split("_");
                var columnGroupId = columnRowInfo[1];
                var columnIndex = columnRowInfo[2];

                if (columnGroupId != "0")
                    ctype = "f";
                else
                    ctype = "g";

                var columnId = ctype + "groupcol" + columnIndex;
                var columnObject = getObj(columnId);
                var selectedColumn = columnObject.value;
                var selectedColumnIndex = columnObject.selectedIndex;
                var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
                if (columnObject.options[selectedColumnIndex].value != "none") {
                    var comparatorId = ctype + "groupop" + columnIndex;
                    var comparatorObject = getObj(comparatorId);
                    var comparatorValue = comparatorObject.value;
                    var valueId = ctype + "groupval" + columnIndex;
                    var valueObject = getObj(valueId);
                    var specifiedValue = valueObject.value;

                    var glueConditionId = ctype + "groupcon" + columnIndex;
                    var glueConditionObject = getObj(glueConditionId);
                    var glueCondition = '';
                    if (glueConditionObject) {
                        glueCondition = glueConditionObject.value;
                    }

                    if(GroupconditionColumns.length>1){
                        if (!emptyCheck4You(columnId, " Column ", "text")){
                            // i < GroupconditionColumns.length
                            return false;
                        }
                        if (!emptyCheck4You(comparatorId, selectedColumnLabel + " Option", "text")){
                            return false;
                        }
                    }

                    var col = selectedColumn.split(":");
                    if (escapedOptions.indexOf(col[3]) == -1) {
                        if (col[4] == 'T') {
                            var datime = specifiedValue.split(" ");
                            if (!re_dateValidate(datime[0], selectedColumnLabel + " (Current User Date Time Format)", "OTH"))
                                return false
                            if (datime.length > 1)
                                if (!re_patternValidate(datime[1], selectedColumnLabel + " (Time)", "TIMESECONDS"))
                                    return false
                        }
                        else if (col[4] == 'D')
                        {
                            if (!dateValidate(valueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                                return false
                            if (extValueObject) {
                                if (!dateValidate(extValueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                                    return false
                            }
                        } else if (col[4] == 'I')
                        {
                            if (!intValidate(valueId, selectedColumnLabel + " (Integer Criteria)" + i))
                                return false
                        } else if (col[4] == 'N')
                        {
                            if (!numValidate(valueId, selectedColumnLabel + " (Number) ", "any", true))
                                return false
                        } else if (col[4] == 'E')
                        {
                            if (!patternValidate(valueId, selectedColumnLabel + " (Email Id)", "EMAIL"))
                                return false
                        }
                    }
                    //Added to handle yes or no for checkbox fields in reports advance filters. 
                    if (col[4] == "C") {
                        if (specifiedValue == "1")
                            specifiedValue = getObj(valueId).value = 'yes';
                        else if (specifiedValue == "0")
                            specifiedValue = getObj(valueId).value = 'no';
                    }
                    if (extValueObject && extendedValue != null && extendedValue != '')
                        specifiedValue = specifiedValue + ',' + extendedValue;

                    GroupcriteriaConditions[columnIndex] = {"groupid": columnGroupId,
                        "columnname": selectedColumn,
                        "comparator": comparatorValue,
                        "value": specifiedValue,
                        "column_condition": glueCondition
                    };
                }
            }
            //var groupft_criteria_value = JSON.stringify(GroupcriteriaConditions);
            var groupft_criteria_value = GroupcriteriaConditions;
            //return_filters[2] = groupft_criteria_value;
            return_filters += "&groupft_criteria="+groupft_criteria_value;
            // groupconditioncolumn end

            return return_filters;
	},

    calculateQuickFilterConditions : function() {
        var criteriaConditions = new Array();

        var columnIndex = 0;
        jQuery('.quickFilter4You').each(function(){
            var quickFilterObj = jQuery(this);
            var value = quickFilterObj.val();
            if (value) {
                var qfId = quickFilterObj.attr('id');
                var radioValue = jQuery('input[name="radio_' + qfId + '"]:checked').val();
                criteriaConditions[columnIndex] = {"columnname": qfId,
                    "radio_value": radioValue,
                    "value": value
                };
                columnIndex++;
            }
        });
        return JSON.stringify(criteriaConditions);
    },
		
	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;
		jQuery('.generateReport').on('click',function(e){
            if(jQuery("#reporttype").val()=="custom_report"){
                jQuery('#detailView').submit();
            }else{
                var advFilterCondition = thisInstance.calculateValues();
                if(advFilterCondition!==false){
                    var advFilterCondition_arr = advFilterCondition.split("&");
                    var advft_criteria_tmp = advFilterCondition_arr[0].split("=");
                    var advft_criteria = advft_criteria_tmp[1];
                    var advft_criteria_groups_tmp = advFilterCondition_arr[1].split("=");
                    var advft_criteria_groups = advft_criteria_groups_tmp[1];
                    var groupft_criteria_tmp = advFilterCondition_arr[2].split("=");
                    var groupft_criteria = groupft_criteria_tmp[1];

                    var recordId = thisInstance.getRecordId();
                    var currentMode = jQuery(e.currentTarget).data('mode');

                    jQuery('#advft_criteria').val(advft_criteria);
                    jQuery('#advft_criteria_groups').val(advft_criteria_groups);
                    jQuery('#groupft_criteria').val(groupft_criteria);

                    var quickFilterCriteria = thisInstance.calculateQuickFilterConditions();
                    jQuery('#quick_filter_criteria').val(quickFilterCriteria);
                    //alert(quickFilterCriteria);

                    jQuery('#currentMode').val(currentMode);
                    jQuery('#detailView').submit();
                }
                return false;
            }
		});
                
                jQuery('.addWidget').on('click',function(e){

                    var aDeferred = jQuery.Deferred();
                    var thisInstance = this;

                    var progressIndicatorElement = jQuery.progressIndicator({
                        'position' : 'html',
                        'blockInfo' : {
                                'enabled' : true
                        }
                    });

                    var recordId = jQuery("#recordId").val();
                    
                    var url = "index.php?module=ITS4YouReports&action=IndexAjax&mode=addWidget&record="+recordId;

                    AppConnector.request(url).then(
                        function(data) {

                            var params = {
                                    text: data['result']['message']
                            };
                            Vtiger_Helper_Js.showMessage(params); 
                            
                            jQuery('.addWidget').hide();
                            
                            progressIndicatorElement.progressIndicator({'mode':'hide'});
                        },
                        function(error) {
                            //TODO : Handle error
                            aDeferred.reject(error);
                        }
                    );
                    return aDeferred.promise();
/*                    */
                });
	},
	
    getDbFormatedDateValue : function(dateObj) {
		if(dateObj==""){
	        return dateObj;
	    }
	    if(typeof dateObj !='undefined'){
	        
			var db_date_format = 'dd-mm-yyyy';
	        var from_date_format = dateObj.data('date-format');
	        var from_value = dateObj.val();
	        
			var dateArray = from_value.split('-');
	        var dateArrayDots = from_value.split('.');
	        
	        var formatedDate = "";
	        switch(from_date_format){
	            case 'dd-mm-yyyy':
	                formatedDate = dateArray[2]+'-'+dateArray[1]+'-'+dateArray[0];
	                break;
	            case 'mm-dd-yyyy':
	                formatedDate = dateArray[2]+'-'+dateArray[0]+'-'+dateArray[1];
	                break;
	            case 'yyyy-mm-dd':
	                formatedDate = dateArray[0]+'-'+dateArray[1]+'-'+dateArray[2];
	                break;
	            case 'dd.mm.yyyy':
	                formatedDate = dateArray[2]+'-'+dateArray[1]+'-'+dateArray[0];
	                break;
	        }
	    }    
	    return formatedDate;   
	},
	
        registerEventsForActions : function() {
            var thisInstance = this;
            jQuery('#generateMap').click(function (e) {
	            var element = jQuery(e.currentTarget);
	            var href = element.data('href');
                window.open(href);
			});
            jQuery('#XLSExport').click(function(e){

                if(jQuery("#reporttype").val()=="custom_report"){
                    jQuery('#GenerateXLS').submit();
                }else{
                    var advFilterCondition = thisInstance.calculateValues();
                    if(advFilterCondition!==false){
                        var advFilterCondition_arr = advFilterCondition.split("&");
                        var advft_criteria_tmp = advFilterCondition_arr[0].split("=");
                        var advft_criteria = advft_criteria_tmp[1];
                        var advft_criteria_groups_tmp = advFilterCondition_arr[1].split("=");
                        var advft_criteria_groups = advft_criteria_groups_tmp[1];
                        var groupft_criteria_tmp = advFilterCondition_arr[2].split("=");
                        var groupft_criteria = groupft_criteria_tmp[1];

                        var recordId = thisInstance.getRecordId();
                        var currentMode = jQuery(e.currentTarget).data('mode');
                        
                        jQuery('#advft_criteria').val();
                        jQuery('#advft_criteria_groups').val(advft_criteria_groups);
                        jQuery('#groupft_criteria').val(groupft_criteria);
                        var quickFilterCriteria = thisInstance.calculateQuickFilterConditions();
                        jQuery('#quick_filter_criteria').val(quickFilterCriteria);

                        jQuery('#GenerateXLS').append("<input type='text' name='advft_criteria' id='advft_criteria' value='"+advft_criteria+"' />");
                        jQuery('#GenerateXLS').append("<input type='text' name='advft_criteria_groups' id='advft_criteria_groups' value='"+advft_criteria_groups+"' />");
                        jQuery('#GenerateXLS').append("<input type='text' name='groupft_criteria' id='groupft_criteria' value='"+groupft_criteria+"' />");
                        jQuery('#GenerateXLS').append("<input type='hidden' name='quick_filter_criteria' id='quick_filter_criteria' value='"+quickFilterCriteria+"' />");
                        jQuery('#GenerateXLS').submit();
                        
                        jQuery('#GenerateXLS #advft_criteria').remove();
                        jQuery('#GenerateXLS #advft_criteria_groups').remove();
                        jQuery('#GenerateXLS #groupft_criteria').remove();
                        
                    }
                    return false;
                }
            })  
        },
	
	registerEvents : function(){
		this._super();
		this.registerEventsForActions();
		this.registerSaveOrGenerateReportEvent();
                var container = this.getContentHolder();
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
                
		var borderTopWidth = parseInt(jQuery(".mainContainer").css('margin-top'))+21; // (footer height 21px)
                var min_height = (jQuery(window).innerHeight()-borderTopWidth);
		jQuery('.mainContainer').css('min-height',min_height);
                jQuery('#rightPanel').css('min-height',min_height);
                jQuery('#reports4you_html').css('min-height',(min_height-210));

//min-height
	}

});
