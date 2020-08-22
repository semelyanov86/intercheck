/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_List_Js("ITS4YouReports_List_Js",{

	listInstance : false,

	addReport : function(url){
		var listInstance = ITS4YouReports_List_Js.listInstance;
		window.location.href=url+'&folder='+listInstance.getCurrentCvId();
	},

	triggerAddFolder : function(url) {
		var params = url;
                AppConnector.request(params).then(
			function(data) {
				var callBackFunction = function(data){
					jQuery('#addFolder').validationEngine({
						// to prevent the page reload after the validation has completed
						'onValidationComplete' : function(form,valid){
                            return valid;
						}
					});
					ITS4YouReports_List_Js.listInstance.folderSubmit().then(function(data){
						if(data.success){
							var result = data.result;
							if(result.success){
								//TODO use pines alert for showing folder has saved
								app.hideModalWindow();
								var info = result.info;
								ITS4YouReports_List_Js.listInstance.updateCustomFilter(info);
							} else {
								var result = result.message;
								var folderNameElement = jQuery('#foldername');
								folderNameElement.validationEngine('showPrompt', result , 'error','topLeft',true);
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
                var listInstance = ITS4YouReports_List_Js.listInstance;
                var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
                Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
                        function(e) {
                                var deleteURL = "index.php?module=ITS4YouReports&action=DeleteReports4You&record="+recorid;
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
        },

	massDelete : function(url) {
		var listInstance = ITS4YouReports_List_Js.listInstance;
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
		var listInstance = ITS4YouReports_List_Js.listInstance;
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
						var reportsListInstance = new ITS4YouReports_List_Js();

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
		ITS4YouReports_List_Js.listInstance = this;
	},

	folderSubmit : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#addFolder').on('submit',function(e){
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
		if(data.success){
			var module = app.getModuleName();
			AppConnector.request('index.php?module='+module+'&view=List&viewname='+cvId).then(
				function(data) {
					jQuery('#recordsCount').val('');
					jQuery('#totalPageCount').text('');
					app.hideModalWindow();
					var listViewContainer = thisInstance.getListViewContentContainer();
					listViewContainer.html(data);
					jQuery('#deSelectAllMsg').trigger('click');
					thisInstance.calculatePages().then(function(){
						thisInstance.updatePagination();
					});
				});
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
			ITS4YouReports_List_Js.triggerAddFolder(editUrl);
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
			ITS4YouReports_List_Js.deleteRecord(recordId);
			e.stopPropagation();
		});
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

		var defaultParams = this.getDefaultParams();
		var urlParams = jQuery.extend(defaultParams, urlParams);
		AppConnector.requestPjax(urlParams).then(
			function(data){
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
                var listViewContentsContainer = jQuery('#listViewContents')
                listViewContentsContainer.html(data);
                app.showSelect2ElementView(listViewContentsContainer.find('select.select2'));
                app.changeSelectElementView(listViewContentsContainer);
                thisInstance.registerTimeListSearch(listViewContentsContainer);

                thisInstance.registerDateListSearch(listViewContentsContainer);
//alert(JSON.stringify(urlParams));
//index.php?module=ITS4YouReports&parent=&page=3&view=List&orderby=&sortorder=&search_params=[[]]
//alert(data);
				thisInstance.calculatePages().then(function(data){
					//thisInstance.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();

					var selectedIds = thisInstance.readSelectedIds();
					if(selectedIds != ''){
						if(selectedIds == 'all'){
							jQuery('.listViewEntriesCheckBox').each( function(index,element) {
								jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
							});
							jQuery('#deSelectAllMsgDiv').show();
							var excludedIds = thisInstance.readExcludedIds();
							if(excludedIds != ''){
								jQuery('#listViewEntriesMainCheckBox').attr('checked',false);
								jQuery('.listViewEntriesCheckBox').each( function(index,element) {
									if(jQuery.inArray(jQuery(element).val(),excludedIds) != -1){
										jQuery(element).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
									}
								});
							}
						} else {
							jQuery('.listViewEntriesCheckBox').each( function(index,element) {
								if(jQuery.inArray(jQuery(element).val(),selectedIds) != -1){
									jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
								}
							});
						}
						thisInstance.checkSelectAll();
					}
					aDeferred.resolve(data);

                    thisInstance.setListPagination();
					// Let listeners know about page state change.
					app.notifyPostAjaxReady();
				});
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to calculate number of pages
	 */
	calculatePages : function() {
		var aDeferred = jQuery.Deferred();
		var element = jQuery('#totalPageCount');
		var totalPageNumber = element.text();
//var previousPageExist = jQuery('#previousPageExist').val();
//var nextPageExist = jQuery('#nextPageExist').val();
//alert(previousPageExist);
//alert(nextPageExist);
		if(totalPageNumber == ""){
			var totalRecordCount = jQuery('#totalCount').val();
			if(totalRecordCount != '') {
				var pageLimit = jQuery('#pageLimit').val();
				if(pageLimit == '0') pageLimit = 1;
				pageCount = Math.ceil(totalRecordCount/pageLimit);
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
				return aDeferred.promise();
			}
			this.getPageCount().then(function(data){
				var pageCount = data['result']['page'];
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
			});
		} else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
    
    registerListSearch : function() {
      var listViewPageDiv = this.getListViewContainer();
      var thisInstance = this;
      listViewPageDiv.on('click','[data-trigger="listSearch"]',function(e){
			thisInstance.getListViewRecords({'page': '1'}).then(
					function(data){
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
					},

					function(textStatus, errorThrown){
					}
			);
      })
      
      listViewPageDiv.on('click','[data-trigger="listSearch1"]',function(e){
			thisInstance.getListViewRecords({'page': '1'}).then(
					function(data){
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
					},

					function(textStatus, errorThrown){
					}
			);
      })

      listViewPageDiv.on('keypress','input.listSearchContributor',function(e){
          if(e.keyCode == 13){
              var element = jQuery(e.currentTarget);
              var parentElement = element.closest('tr');
              var searchTriggerElement = parentElement.find('[data-trigger="listSearch"]');
              searchTriggerElement.trigger('click');
          }
      });
    },

    setListPagination : function(){
        // ITS4YOU-CR SlOl 19. 8. 2016 6:35:49
        var pageNumber = jQuery('#pageNumber').val();
        var element = jQuery('#totalPageCount');
        var totalPageNumber = element.text();
        if(totalPageNumber == ""){
            var totalPagesCount = jQuery('#totalCount').val();
        }else{
            var totalPagesCount = totalPageNumber;
        }
        if(pageNumber>1){
            var previousPageButton = jQuery('#listViewPreviousPageButton');
            previousPageButton.removeAttr('disabled');
        }
        if(pageNumber<totalPagesCount){
            var nextPageButton = jQuery('#listViewNextPageButton');
            nextPageButton.removeAttr('disabled');
        }
        // ITS4YOU-END
    },

	registerEvents : function(){
		//this._super();
        this.registerDeleteRecordClickEvent();
        
        this.registerRowClickEvent();
        this.registerPageNavigationEvents();
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
		this.registerHeadersClickEvent();
		this.registerMassActionSubmitEvent();
		this.registerEventForAlphabetSearch();

		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerCreateFilterClickEvent();
		this.registerEditFilterClickEvent();
		this.registerDeleteFilterClickEvent();
		this.registerApproveFilterClickEvent();
		this.registerDenyFilterClickEvent();
		this.registerCustomFilterOptionsHoverEvent();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();
		this.registerEventForTotalRecordsCount();

		//Just reset all the checkboxes on page load: added for chrome issue.
		var listViewContainer = this.getListViewContentContainer();
		listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);

        this.registerListSearch();
        this.setListPagination();
        this.registerDateListSearch(listViewContainer);
        this.registerTimeListSearch(listViewContainer);

        jQuery('.totalNumberOfRecords').trigger('click');
                
	}
});
