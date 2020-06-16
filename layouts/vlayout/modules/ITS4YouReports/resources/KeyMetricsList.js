/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_List_Js("ITS4YouReports_KeyMetricsList_Js",{

	listInstance : false,
    
	addReport : function(url){
		var listInstance = ITS4YouReports_KeyMetricsList_Js.listInstance;
		window.location.href=url+'&folder='+listInstance.getCurrentCvId();
	},

	triggerAddKeyWidget : function(url) {
		var params = url;
                AppConnector.request(params).then(
			function(data) {
				var callBackFunction = function(data){
					jQuery('#addKeyMetricsWidget').validationEngine({
						// to prevent the page reload after the validation has completed
						'onValidationComplete' : function(form,valid){
                            return valid;
						}
					});
					ITS4YouReports_KeyMetricsList_Js.listInstance.ketMetricsSubmit().then(function(data){
						if(data.success){
							var result = data.result;
							if(result.success){
								//TODO use pines alert for showing ketMetrics has saved
								app.hideModalWindow();
								var info = result.info;
								//ITS4YouReports_KeyMetricsList_Js.listInstance.updateCustomFilter(info);
                                location.reload(true);
							} else {
								var result = result.message;
								var ketMetricsNameElement = jQuery('#name');
								ketMetricsNameElement.validationEngine('showPrompt', result , 'error','topLeft',true);
							}
						} else {
							app.hideModalWindow();
							var params = {
								title : app.vtranslate('JS_ERROR'),
								text : data.error.message
							}
							Vtiger_Helper_Js.showPnotify(params);
						}
					});
				};
				app.showModalWindow(data,function(data){
					if(typeof callBackFunction == 'function'){
						callBackFunction(data);
					}
				});
			}
		)
	},
    
    deleteRecord : function(recorid) {
        if(typeof(recorid) == 'undefined')
            return false;
            var listInstance = ITS4YouReports_KeyMetricsList_Js.listInstance;
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
                function(e) {
                    var deleteURL = "index.php?module=ITS4YouReports&view=DeleteKeyMetrics&id="+recorid;
                    var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
                    var progressIndicatorElement = jQuery.progressIndicator({
                            'message' : deleteMessage,
                            'position' : 'html',
                            'blockInfo' : {
                                'enabled' : true
                            }
                    });
                    AppConnector.request(deleteURL).then(
                            function(data) {
                                    progressIndicatorElement.progressIndicator({
                                        'mode' : 'hide'
                                    })
                                    if(data){
                            			data = JSON.parse(data);
                                        if(data.success) {
                        					app.hideModalWindow();
                                			var params = {
                                				title : app.vtranslate('JS_LBL_PERMISSION'),
                                				text : data.result.message
                                			}
                                			Vtiger_Helper_Js.showPnotify(params);
                                            location.reload(true);
                        				} else {
                        					app.hideModalWindow();
                        					var params = {
                        						title : app.vtranslate('JS_INFORMATION'),
                        						text : data.error.message
                        					}
                        					Vtiger_Helper_Js.showPnotify(params);
                        				}
                                    }
                            });
                    },
                function(error, err){
                }
            );
    },

	massDelete : function(url) {
		var listInstance = ITS4YouReports_KeyMetricsList_Js.listInstance;
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			
			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
                        Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var deleteURL = url+'&viewname='+cvId+'&selected_ids='+selectedIds+'&excluded_ids='+excludedIds;
					var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						'message' : deleteMessage,
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					});
					AppConnector.request(deleteURL).then(
						function(data) {
							progressIndicatorElement.progressIndicator({
								'mode' : 'hide'
							})
							if(data){
								listInstance.massActionPostOperations(data);
							}
						});
				},
				function(error, err){
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	massMove : function(url){
		var listInstance = ITS4YouReports_KeyMetricsList_Js.listInstance;
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname" : cvId
			};
			var params = {
				"url":url,
				"data" : postData
			};
			AppConnector.request(params).then(
				function(data) {
					var callBackFunction = function(data){
						var reportsListInstance = new ITS4YouReports_KeyMetricsList_Js();

						reportsListInstance.moveReports().then(function(data){
							if(data){
								listInstance.massActionPostOperations(data);
							}
						});
					}
					app.showModalWindow(data,callBackFunction);
				}
			)
		} else{
			listInstance.noRecordSelectedAlert();
		}

	}

},{

	init : function(){
		ITS4YouReports_KeyMetricsList_Js.listInstance = this;
	},

	ketMetricsSubmit : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#addKeyMetricsWidget').on('submit',function(e){
			var validationResult = jQuery(e.currentTarget).validationEngine('validate');
			if(validationResult == true){
				var formData = jQuery(e.currentTarget).serializeFormData();
				AppConnector.request(formData).then(
					function(data){
						aDeferred.resolve(data);
					}
				);
			}
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	moveReports : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#moveReports').on('submit',function(e){
			var formData = jQuery(e.currentTarget).serializeFormData();
			AppConnector.request(formData).then(
				function(data){
					aDeferred.resolve(data);
				}
			);
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	updateCustomFilter : function (info){
		var folderId = info.folderId;
		var customFilter =  jQuery("#customFilter");
		var constructedOption = this.constructOptionElement(info);
		var optionId = 'filterOptionId_'+folderId;
		var optionElement = jQuery('#'+optionId);
		if(optionElement.length > 0){
			optionElement.replaceWith(constructedOption);
			customFilter.trigger("liszt:updated");
		} else {
			customFilter.find('#foldersBlock').append(constructedOption).trigger("liszt:updated");
		}
	},

	constructOptionElement : function(info){
            return '<option data-editable="'+info.isEditable+'" data-deletable="'+info.isDeletable+'" data-editurl="'+info.editURL+'" data-deleteurl="'+info.deleteURL+'" class="filterOptionId_'+info.folderId+'" id="filterOptionId_'+info.folderId+'" value="'+info.folderId+'" data-id="'+info.folderId+'">'+info.folderName+'</option>';

	},

	/*
	 * Function to perform the operations after the mass action
	 */
	massActionPostOperations : function(data){
		var thisInstance = this;
		var cvId = this.getCurrentCvId();
		if(data.result){
        	//TODO use pines alert for showing ketMetrics has saved
            var result = data.result;            
			app.hideModalWindow();
			var info = result.info;
			
            var params = {
				title : app.vtranslate('JS_INFORMATION'),
				text : result.message
			}
			Vtiger_Helper_Js.showPnotify(params);
		    location.reload(true);
		} else {
			app.hideModalWindow();
			var params = {
				title : app.vtranslate('JS_LBL_PERMISSION'),
				text : data.error.message+ ' : ' + data.error.code
			}
			Vtiger_Helper_Js.showPnotify(params);
		}
	},
	/*
	 * function to delete the folder
	 */
	deleteFolder : function(event,url){
		var thisInstance =this;
		AppConnector.request(url).then(
			function(data){
				if(data.success) {
					var chosenOption = jQuery(event.currentTarget).closest('.select2-result-selectable');
					var selectOption = thisInstance.getSelectOptionFromChosenOption(chosenOption);
					selectOption.remove();
					var customFilterElement = thisInstance.getFilterSelectElement();
					customFilterElement.trigger("liszt:updated");
					var defaultCvid = customFilterElement.find('option:first').val();
					customFilterElement.select2("val", defaultCvid);
					customFilterElement.trigger('change');
				} else {
					app.hideModalWindow();
					var params = {
						title : app.vtranslate('JS_INFORMATION'),
						text : data.error.message
					}
					Vtiger_Helper_Js.showPnotify(params);
				}
			}
		)
	},
		/*
	 * Function to register the click event for edit filter
	 */
	registerEditFilterClickEvent : function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		listViewFilterBlock.on('mouseup','li i.editFilter',function(event){
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
			var editUrl = currentOptionElement.data('editurl');
			ITS4YouReports_KeyMetricsList_Js.triggerAddFolder(editUrl);
			event.stopPropagation();
		});
	},

	/*
	 * Function to register the click event for delete filter
	 */
	registerDeleteFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		//used mouseup event to stop the propagation of customfilter select change event.
		listViewFilterBlock.on('mouseup','li i.deleteFilter',function(event){
			// To close the custom filter Select Element drop down
			thisInstance.getFilterSelectElement().data('select2').close();
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					var deleteUrl = currentOptionElement.data('deleteurl');
					thisInstance.deleteFolder(event,deleteUrl);
				},
				function(error, err){
				}
			);
			event.stopPropagation();
		});
	},
        
        /*
	 * Function to register the list view delete record click event
	 */
	registerDeleteRecordClickEvent: function(){
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.deleteRecordButton',function(e){
			var elem = jQuery(e.currentTarget);
			var recordId = elem.closest('tr').data('id');
			ITS4YouReports_KeyMetricsList_Js.deleteRecord(recordId);
			e.stopPropagation();
		});
	},
    
	getDefaultParams : function() {
    	var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var orderBy = jQuery('#orderBy').val();
		var sortOrder = jQuery("#sortOrder").val();
		var params = {
			'module': module,
			'parent' : parent,
			'view' : "KeyMetricsList",
			'orderby' : orderBy,
			'sortorder' : sortOrder
		}

        var searchValue = this.getAlphabetSearchValue();

        if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
            params['search_key'] = this.getAlphabetSearchField();
            params['search_value'] = searchValue;
            params['operator'] = "s";
        }
        params.search_params = JSON.stringify(this.getListSearchParams());
        return params;
	},
    
    getListSearchParams : function(){
        var listViewTable = jQuery('#keyMetricsViewContents').find('.listViewEntriesTable');
        var searchParams = new Array();
        listViewTable.find('.listSearchContributor').each(function(index,domElement){
            var searchInfo = new Array();
            var searchContributorElement = jQuery(domElement);
            var fieldInfo = searchContributorElement.data('fieldinfo');
            var fieldName = searchContributorElement.attr('name');

            var searchValue = searchContributorElement.val();

            if(typeof searchValue == "object") {
                if(searchValue == null) {
                   searchValue = "";
                }else{
                    searchValue = searchValue.join(',');
                }
            }
            searchValue = searchValue.trim();
            if(searchValue.length <=0 ) {
                //continue
                return true;
            }
            var searchOperator = 'c';
            if(fieldInfo.type == "date" || fieldInfo.type == "datetime") {
                searchOperator = 'bw';
            }else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
                || fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
                fieldInfo.type == "picklist") {
                searchOperator = 'e';
            }
            searchInfo.push(fieldName);
            searchInfo.push(searchOperator);
            searchInfo.push(searchValue);
            searchParams.push(searchInfo);
        });

        return new Array(searchParams);
    },
    
    /*
	 * Function which will give you all the list view params
	 */
	getListViewRecords : function(urlParams) {
		var aDeferred = jQuery.Deferred();
		if(typeof urlParams == 'undefined') {
			urlParams = {};
		}

		var thisInstance = this;
		var loadingMessage = jQuery('.listViewLoadingMsg').text();
		var progressIndicatorElement = jQuery.progressIndicator({
			'message' : loadingMessage,
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});

		var urlParams = this.getDefaultParams();
		//var urlParams = jQuery.extend(defaultParams, urlParams);
		AppConnector.requestPjax(urlParams).then(
			function(data){
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
                var listViewContentsContainer = jQuery('#keyMetricsViewContents')
                listViewContentsContainer.html(data);
                app.showSelect2ElementView(listViewContentsContainer.find('select.select2'));
                app.changeSelectElementView(listViewContentsContainer);
                
                thisInstance.registerListSearch();
                //location.reload(true);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    registerListSearch : function() {
      var listViewPageDiv = jQuery("#keyMetricsViewContents");
      var thisInstance = this;
      listViewPageDiv.on('click','[data-trigger="listSearch"]',function(e){
			thisInstance.getListViewRecords().then(
					function(data){
                        /*
                        //To unmark the all the selected ids
                        jQuery('#deSelectAllMsg').trigger('click');
                        
                         jQuery('#recordsCount').val('');
                        //To Set the page number as first page
                        jQuery('#pageNumber').val('1');
                        jQuery('#pageToJump').val('1');
                        jQuery('#totalPageCount').text("");
                        thisInstance.calculatePages().then(function(){
                            thisInstance.updatePagination();
                        });
                        */
					},

					function(textStatus, errorThrown){
					}
			);
      });
    }, 

    registerEditKeyMetricsRowStep1 : function() {
        var deleteElement = jQuery('.icon-trash');
        deleteElement.on('click', function(e) {
            var element = jQuery(e.currentTarget);
            var currentRow = jQuery(element).closest('.pickListValue');
            var key_metrics_row_id = currentRow.attr('data-key-id');
            
            if(typeof(key_metrics_row_id) == 'undefined')
                return false;
                var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
                
                Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
                    function(e) {
                        
                        var deleteURL = "index.php?module=ITS4YouReports&action=IndexAjax&mode=deleteKeyMetricRecord&id="+key_metrics_row_id;
                        var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
                        var progressIndicatorElement = jQuery.progressIndicator({
                                'message' : deleteMessage,
                                'position' : 'html',
                                'blockInfo' : {
                                    'enabled' : true
                                }
                        });
                        AppConnector.request(deleteURL).then(
                                function(data) {
                                        progressIndicatorElement.progressIndicator({
                                            'mode' : 'hide'
                                        })
                                        if(data){
                                            if(data.success) {
                            				    app.hideModalWindow();
                                    			var params = {
                                    				title : app.vtranslate('JS_LBL_PERMISSION'),
                                    				text : data.result.message
                                    			}
                                    			Vtiger_Helper_Js.showPnotify(params);
                                                location.reload(true);
                            				} else {
                            					app.hideModalWindow();
                            					var params = {
                            						title : app.vtranslate('JS_INFORMATION'),
                            						text : data.error.message
                            					}
                            					Vtiger_Helper_Js.showPnotify(params);
                            				}
                                        }
                                });
                        },
                    function(error, err){
                    }
                );
        });
        var editElement = jQuery('.icon-pencil');
        editElement.on('click', function(e) {
            var element = jQuery(e.currentTarget);
            var currentRow = jQuery(element).closest('.pickListValue');
            var key_metrics_row_id = currentRow.attr('data-key-id');
            
            if(typeof(key_metrics_row_id) == 'undefined')
                return false;
            
            var km_id = jQuery("#km_id").val();
            if(typeof(km_id) == 'undefined' || km_id=="")
                return false;
            
            var editUrl = 'index.php?module=ITS4YouReports&view=EditKeyMetricsRow&km_id='+km_id+'&id='+key_metrics_row_id+'&reportid=';
            window.location.href=editUrl;
        });
    },
        
    registerEditKeyMetricsRowStep2 : function() {
        
        var thisInstance = this;
        var next_rep_top = jQuery('#next_rep_top');
        
        var km_id = jQuery('#km_id').val();
        var location_href = 'index.php?module=ITS4YouReports&view=EditKeyMetricsRow&km_id='+km_id+'&id=&reportid=';
        
        next_rep_top.on('click',function(){
            var reportid = jQuery('#reportname').val();
            if(reportid==""){
                alert(app.vtranslate('MISSING_REPORT_NAME'));
                return false;
            }
        	window.location.href=location_href+reportid;
        });
        var next_rep_top2 = jQuery('#next_rep_top2');
        next_rep_top2.on('click',function(){
        	var reportid = jQuery('#reportname').val();
            if(reportid==""){
                alert(app.vtranslate('MISSING_REPORT_NAME'));
                return false;
            }
            window.location.href=location_href+reportid;
        });
    },
    
    registerEditKeyMetricsRowStep3 : function() {
        var thisInstance = this;
        
		jQuery('#addKeyMetricsWidget').on('submit',function(e){
			var validationResult = jQuery(e.currentTarget).validationEngine('validate');

			if(validationResult == true){
                var label = jQuery("#label").val();
                if(typeof label == 'undefined' || label==""){
                    var params = { title : app.vtranslate('JS_ERROR'), text : app.vtranslate('label','ITS4YouReports')+" "+app.vtranslate('CANNOT_BE_EMPTY','ITS4YouReports') };
				    Vtiger_Helper_Js.showPnotify(params);
                    jQuery("#label").focus();
                    return false;
                }
                
                /*var calculation_type = jQuery("#calculation_type").val();
                if(typeof calculation_type == 'undefined' || calculation_type==""){
                    var params = { title : app.vtranslate('JS_ERROR'), text : app.vtranslate('calculation_type','ITS4YouReports')+" "+app.vtranslate('CANNOT_BE_EMPTY','ITS4YouReports') };
				    Vtiger_Helper_Js.showPnotify(params);
                    jQuery("#calculation_type").focus();
                    return false;
                }*/
                
                var reportname = jQuery("#reportname").val();
                if(typeof reportname == 'undefined' || reportname==""){
                    var params = { title : app.vtranslate('JS_ERROR'), text : app.vtranslate('reportname','ITS4YouReports')+" "+app.vtranslate('CANNOT_BE_EMPTY','ITS4YouReports') };
				    Vtiger_Helper_Js.showPnotify(params);
                    jQuery("#reportname").focus();
                    return false;
                }
                
                var column_str = jQuery("#column_str").val();
                if(typeof column_str == 'undefined' || column_str==""){
                    var params = { title : app.vtranslate('JS_ERROR'), text : app.vtranslate('column_str','ITS4YouReports')+" "+app.vtranslate('CANNOT_BE_EMPTY','ITS4YouReports') };
				    Vtiger_Helper_Js.showPnotify(params);
                    jQuery("#column_str").focus();
                    return false;
                }
				
				var formData = jQuery("#addKeyMetricsWidget").serializeFormData();
                var params = {
    				"url":'index.php?module=ITS4YouReports&action=SaveKeyMetricsRow',
    				"data" : formData
    			};
			}
		});
    },
    
    /*
	 * Function to register the click event for edit filter
	 */
	registerReportChangeEvent : function(){
		var thisInstance = this;
		var reportNameElement = jQuery('#reportname');
		reportNameElement.on('change', function(e) {
			var element = jQuery(e.currentTarget);
			var reportid = element.val();
            
            var postData = {
                "reportid": reportid,
            };
            
            var url = 'module=ITS4YouReports&action=IndexAjax&mode=getKeyMetricReportColumns';
            var actionParams = {
                "type": "POST",
                "url": 'index.php?'+ url,
                "dataType": "html",
                "data": postData
            };
            
            jQuery("#column_str").removeClass('chzn-done');
            jQuery("#column_str").removeClass('chzn-select');
            jQuery("#column_str_chzn").remove();
            
            AppConnector.request(actionParams).then(
                function(data) {
                    var result = data.split('<#@#>');
                    var res_status = result[0];
                    var result_value = result[1];
                    
					app.hideModalWindow();
                    if(res_status=="success"){
                        var column_str = jQuery("#column_str");
                        column_str.html(result_value);
                        column_str.addClass('chzn-select');
                        app.changeSelectElementView(column_str);
                    }else{
    					var params = {
    						title : app.vtranslate('JS_ERROR'),
    						text : result_value
    					}
    					Vtiger_Helper_Js.showPnotify(params);
                    }
                }
            );
                        
		});
	},

	registerEvents : function(){
		//this._super();
        
        this.registerDeleteRecordClickEvent();
        
        //this.registerRowClickEvent();
        //this.registerPageNavigationEvents();
		//this.registerMainCheckBoxClickEvent();
		//this.registerCheckBoxClickEvent();
		//this.registerSelectAllClickEvent();
		//this.registerDeselectAllClickEvent();
		//this.registerHeadersClickEvent();
		//this.registerMassActionSubmitEvent();
		//this.registerEventForAlphabetSearch();

		//this.changeCustomFilterElementView();
		//this.registerChangeCustomFilterEvent();
		//this.registerCreateFilterClickEvent();
		//this.registerEditFilterClickEvent();
		//this.registerDeleteFilterClickEvent();
		//this.registerApproveFilterClickEvent();
		//this.registerDenyFilterClickEvent();
		//this.registerCustomFilterOptionsHoverEvent();
		//this.registerEmailFieldClickEvent();
		//this.registerPhoneFieldClickEvent();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		//this.registerUrlFieldClickEvent();
		//this.registerEventForTotalRecordsCount();

		//Just reset all the checkboxes on page load: added for chrome issue.
		var listViewContainer = this.getListViewContentContainer();
		listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);

        this.registerListSearch();
/*
        this.registerDateListSearch(listViewContainer);
        this.registerTimeListSearch(listViewContainer);
*/
	}, 
    
    registerPickListValuesSortableEvent : function() {
		var thisInstance = this;
        var tbody = jQuery( "tbody",jQuery('#pickListValuesTable'));
		tbody.sortable({
			'helper' : function(e,ui){
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function(index,element){
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			},
			'containment' : tbody,
			'revert' : true,
			update: function(e, ui ) {
				thisInstance.registerSaveSequenceClickEvent();
                //alert('update');
			}
		});
	},
    
    registerSaveSequenceClickEvent : function() {
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true,
				'elementToBlock' : jQuery('.tab-content')
			}
		});
		var pickListValuesSequenceArray = {}
		var pickListValues = jQuery('#pickListValuesTable').find('.pickListValue');
		jQuery.each(pickListValues,function(i,element) {
			pickListValuesSequenceArray[jQuery(element).data('key-id')] = ++i;
		});
		var params = {
			module : app.getModuleName(),
			parent : app.getParentModuleName(),
			action : 'IndexAjax',
			mode : 'saveKeyMetricsOrder',
			picklistValues : pickListValuesSequenceArray,
			picklistName : jQuery('[name="picklistName"]').val()
		}
        
		AppConnector.request(params).then(function(data) {
			if(typeof data.result != 'undefined') {
            	var result = data.result;
				var textVal = result.message;
				if(result.success){
            		progressIndicatorElement.progressIndicator({mode : 'hide'});
    				var params = {
    					title : app.vtranslate('JS_INFORMATION'),
                        type : 'success',
    					text : textVal
    				}
                    Vtiger_Helper_Js.showMessage(params);
				} else {
            		progressIndicatorElement.progressIndicator({mode : 'hide'});
    				var params = {
    					title : app.vtranslate('JS_ERROR'),
    					text : textVal
    				}
                    Vtiger_Helper_Js.showPnotify(params);
				}
			}
		});
	}
    
});
