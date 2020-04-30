//////////////////////////
// Modified Custom View //
//////////////////////////

function runClass(params) {
    var instance = new Vtiger_VDUsers_Js();
    instance.registerEvents(params);
}

jQuery(document).ready(function() {
    jQuery.extend(Vtiger_CustomView_Js, {

        url: 'index.php?module=VDUsers&view=List',

        picklist: null,

        reset: function()
        {
            this.contentsCotainer = false;
            this.columnListSelect2Element = false;
            this.advanceFilterInstance = false;
            this.columnSelectElement = false;
            this.selectedColumnsList = false;
        },

        XloadFilterView: function(url)
        {
            var progressIndicatorElement = jQuery.progressIndicator();
            var thisInstance = this;
            AppConnector.request(url).then(
                function(data) {
                    var obj = {
                        data: data,
                        unblockcb: thisInstance.unBlock
                    };
                    app.showModalWindow(obj);
                    var contents = jQuery("#globalmodal");
                    progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    thisInstance.registerEvents();
                    thisInstance.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',contents));
                }
            );
        },

        saveAndViewFilter: function()
        {
            var thisInstance = this;
            this.saveFilter().then(
                function(response){
                    if (response.success) {
                        // window.location.href = thisInstance.url;
                    } else {
                        var params = {
                            title: app.vtranslate('JS_DUPLICATE_RECORD'),
                            text: response.error.message
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                }
            );
        },

        XgetContentsContainer: function()
        {
            if(this.contentsCotainer === false) {
                this.contentsCotainer = jQuery('#globalmodal');
            }
            return this.contentsCotainer;
        },

        getPicklist: function()
        {
            this.picklist = jQuery('#VDRoleSelect').data('rolePicklist');
        },

        registerSelect2ElementForColumnsSelection2 : function()
        {
            alert('regreg');
            var columnSelectElement = this.getColumnSelectElement();
            var roleSelectElement = jQuery('#VDRoleSelect');
            app.changeSelectElementView(columnSelectElement, 'select2', {maximumSelectionSize: 12,dropdownCss : {'z-index' : 10002}});
            app.changeSelectElementView(roleSelectElement, 'select2', {dropdownCss : {'z-index' : 10002}});
            app.showSelect2ElementView(jQuery('#CustomView select.select2'));
            this.registerOnChangeEventForSelect2();
            this.registerEventForRemoveTargetModuleField();
            this.getPicklist(); // role picklist
        },

        /**
         * Function to register event for onchange event for
         * select2 element fro adding and removing fields
         */
        registerOnChangeEventForSelect2 : function()
        {
            var thisInstance = this;
            jQuery('#VDRoleSelect').on('change',function(e) {
                //var element = jQuery(e.currentTarget);
                //To handle the options that are removed from select2
                if(typeof e.removed != "undefined") {
                    var removedFieldObject = e.removed;
                    var rowName = removedFieldObject.id;
                    jQuery('#VDRoleConstraints').find('tr[data-name="'+rowName+'"]').find('.removeTargetModuleField').trigger('click');
                } else if(typeof e.added != "undefined"){
                    //To add the row according to option that is selected from select2
                    var addedFieldObject = e.added;
                    var addedFieldName = addedFieldObject.id;
                    thisInstance.displaySelectedField(addedFieldName);
                }
            });
        },

        /**
         * Function to handle target module remove field action
         */
        registerEventForRemoveTargetModuleField : function()
        {
            jQuery('#VDRoleConstraints').on('click','.removeTargetModuleField',function(e) {
                var element = jQuery(e.currentTarget);
                var containerRow = element.closest('tr');
                var removedFieldLabel = containerRow.find('td.fieldLabel').text();
                var selectElement = jQuery('#VDRoleSelect');
                var select2Element = app.getSelect2ElementFromSelect(selectElement);
                select2Element.find('li.select2-search-choice').find('div:contains('+removedFieldLabel+')').closest('li').remove();
                selectElement.find('option:contains('+removedFieldLabel+')').removeAttr('selected');
                containerRow.remove();
            });
        },

        /**
         * Function to render selected field UI
         */
        displaySelectedField : function(selectedField)
        {
            var editViewForm = jQuery('#CustomView');
            var targetFieldsTable = jQuery('#VDRoleConstraints');
            var selectedFieldOption = editViewForm.find('#VDRoleSelect option[value="'+selectedField+'"]');
            var name = selectedFieldOption.val();
            var label = selectedFieldOption.data('fieldName');
            var picklist = this.picklist;
            var fieldInfo = {
                mandatory: false,
                type: 'multipicklist',
                name: name,
                label: label,
                picklistvalues: picklist
            };
            var moduleName = app.getModuleName();
            var fieldInstance = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
            var UI = jQuery(fieldInstance.getUiTypeSpecificHtml());

            var row = '<tr class="listViewEntries" data-name="'+name+'" data-type="multipicklist" data-mandatory-field="false">'+
                '<td class="textAlignCenter fieldLabel">'+label+'</td>'+
                '<td class="textAlignCenter fieldValue" data-name="fieldUI_'+name+'"></td>'+
                '<td><div class="pull-right actions"><a class="removeTargetModuleField"><i class="fa fa-trash icon-remove-sign"></i></a></div></td></tr>';
            targetFieldsTable.append(row);
            targetFieldsTable.find('[data-name="fieldUI_'+name+'"]').html(UI);
            UI.css({"width":"60%"});
            app.showSelect2ElementView(UI);
        },

        unBlock: function()
        {
            Vtiger_CustomView_Js.reset();
        }
    });
    Vtiger_CustomView_Js('Vtiger_VDUsers_Js', {}, {
        url: 'index.php?module=VDUsers&view=List',

        picklist: null,

        reset: function()
        {
            this.contentsCotainer = false;
            this.columnListSelect2Element = false;
            this.advanceFilterInstance = false;
            this.columnSelectElement = false;
            this.selectedColumnsList = false;
        },

        XloadFilterView: function(url)
        {
            var progressIndicatorElement = jQuery.progressIndicator();
            var thisInstance = this;
            AppConnector.request(url).then(
                function(data) {
                    var obj = {
                        data: data,
                        unblockcb: thisInstance.unBlock
                    };
                    app.showModalWindow(obj);
                    var contents = jQuery("#globalmodal");
                    progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    thisInstance.registerEvents();
                    thisInstance.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',contents));
                }
            );
        },

        saveAndViewFilter: function()
        {
            var thisInstance = this;
            this.saveFilter().then(
                function(response){
                    if (response) {
                        window.location.href = response.listviewurl;
                    } else {
                        var params = {
                            title: app.vtranslate('JS_DUPLICATE_RECORD'),
                            text: response.error.message
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                }
            );
        },

        XgetContentsContainer: function()
        {
            if(this.contentsCotainer === false) {
                this.contentsCotainer = jQuery('#globalmodal');
            }
            return this.contentsCotainer;
        },

        getPicklist: function()
        {
            this.picklist = jQuery('#VDRoleSelect').data('rolePicklist');
        },

        registerSelect2ElementForColumnsSelection : function()
        {
            var columnSelectElement = this.getColumnSelectElement();
            var roleSelectElement = jQuery('#VDRoleSelect');
            app.changeSelectElementView(columnSelectElement, 'select2', {maximumSelectionSize: 12,dropdownCss : {'z-index' : 10002}});
            app.changeSelectElementView(roleSelectElement, 'select2', {dropdownCss : {'z-index' : 10002}});
            app.showSelect2ElementView(jQuery('#CustomView select.select2'));
            this.registerOnChangeEventForSelect2();
            this.registerEventForRemoveTargetModuleField();
            this.getPicklist(); // role picklist
        },

        /**
         * Function to register event for onchange event for
         * select2 element fro adding and removing fields
         */
        registerOnChangeEventForSelect2 : function()
        {
            var thisInstance = this;
            jQuery('#VDRoleSelect').on('change',function(e) {
                //var element = jQuery(e.currentTarget);
                //To handle the options that are removed from select2
                if(typeof e.removed != "undefined") {
                    var removedFieldObject = e.removed;
                    var rowName = removedFieldObject.id;
                    jQuery('#VDRoleConstraints').find('tr[data-name="'+rowName+'"]').find('.removeTargetModuleField').trigger('click');
                } else if(typeof e.added != "undefined"){
                    //To add the row according to option that is selected from select2
                    var addedFieldObject = e.added;
                    var addedFieldName = addedFieldObject.id;
                    thisInstance.displaySelectedField(addedFieldName);
                }
            });
        },

        /**
         * Function to handle target module remove field action
         */
        registerEventForRemoveTargetModuleField : function()
        {
            jQuery('#VDRoleConstraints').on('click','.removeTargetModuleField',function(e) {
                var element = jQuery(e.currentTarget);
                var containerRow = element.closest('tr');
                var removedFieldLabel = containerRow.find('td.fieldLabel').text();
                var selectElement = jQuery('#VDRoleSelect');
                var select2Element = app.getSelect2ElementFromSelect(selectElement);
                select2Element.find('li.select2-search-choice').find('div:contains('+removedFieldLabel+')').closest('li').remove();
                selectElement.find('option:contains('+removedFieldLabel+')').removeAttr('selected');
                containerRow.remove();
            });
        },

        /**
         * Function to render selected field UI
         */
        displaySelectedField : function(selectedField)
        {
            var editViewForm = jQuery('#CustomView');
            var targetFieldsTable = jQuery('#VDRoleConstraints');
            var selectedFieldOption = editViewForm.find('#VDRoleSelect option[value="'+selectedField+'"]');
            var name = selectedFieldOption.val();
            var label = selectedFieldOption.data('fieldName');
            var picklist = this.picklist;
            var fieldInfo = {
                mandatory: false,
                type: 'multipicklist',
                name: name,
                label: label,
                picklistvalues: picklist,
                picklistColors: {}
            };
            var moduleName = app.getModuleName();
            var fieldInstance = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
            var UI = jQuery(fieldInstance.getUiTypeSpecificHtml());

            var row = '<tr class="listViewEntries" data-name="'+name+'" data-type="multipicklist" data-mandatory-field="false">'+
                '<td class="textAlignCenter fieldLabel">'+label+'</td>'+
                '<td class="textAlignCenter fieldValue" data-name="fieldUI_'+name+'"></td>'+
                '<td><div class="actions"><a class="removeTargetModuleField"><i class="fa fa-trash icon-remove-sign"></i></a></div></td></tr>';
            targetFieldsTable.append(row);
            targetFieldsTable.find('[data-name="fieldUI_'+name+'"]').html(UI);
            UI.css({});
            app.showSelect2ElementView(UI);
        },

        unBlock: function()
        {
            Vtiger_CustomView_Js.reset();
        },
        registerEvents : function(params) {
            var self = this;
            self.doOperation(params.url).then(function (data) {
                self.showCreateFilter(data);
                var form = jQuery('#CustomView');
                app.helper.registerLeavePageWithoutSubmit(form);
                app.helper.registerModalDismissWithoutSubmit(form);
                self.registerSelect2ElementForColumnsSelection();
            });
        }
    });
    jQuery.extend(Vtiger_Index_Js, {

        registerShowHideLeftPanelEvent: function()
        {
            var leftPanel = jQuery('#leftPanel');
            var rightPanel = jQuery('#rightPanel');
            var tButtonImage = jQuery('#tButtonImage');
            leftPanel.addClass('hide');
            rightPanel.removeClass('span10').addClass('span12');
            tButtonImage.removeClass('icon-chevron-left').addClass("icon-chevron-right");
            jQuery('#toggleButton').off('click');
        }
    });
    Vtiger_Index_Js.registerShowHideLeftPanelEvent();
});

/////////////////////////////
// VDUsers Page Controller //
/////////////////////////////

Vtiger_List_Js('VDUsers_List_Js', {}, {

    customBaseViewUrl: 'index.php?module=VDUsers&view=EditAjax&source_module=Users',

    registerCustomViewModal: function()
    {
        var thisInstance = this;
        jQuery('#btnVDUsersCustom').on('click', function() {
            var createUrl = thisInstance.customBaseViewUrl,
                viewId = jQuery('#vdviewId').val();
            if (viewId.length > 0) {
                createUrl += '&record=' + viewId;
            }
            thisInstance.showCreateFilter(createUrl);
        });
    },

    registerSearchButton: function()
    {
        var thisInstance = this;
        jQuery('#btnVDUsersSearch').on('click', function() {
            var __searchParam = {};
            jQuery(".listSearchContributor").each(function() {
                if (this.value) {
                    __searchParam[this.name] = this.value;
                }
            });
            var params = {
                vdsearch_params: __searchParam
            };
            thisInstance.getListViewRecords(params);
        });
    },

    registerRoleFilter: function()
    {
        var thisInstance = this;

        jQuery('.vdusers-role').on('click', function(e) {
            e.preventDefault();
            var link = jQuery(e.target),
                roleId = link.attr('href').substring(1);
            var params = {
                roleid: roleId
            };
            thisInstance.getListViewRecords(params);
        });
    },
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
                var listViewContentsContainer = jQuery('#listViewContent')
                listViewContentsContainer.html(data);
                app.showSelect2ElementView(listViewContentsContainer.find('select.select2'));
                app.changeSelectElementView(listViewContentsContainer);
                thisInstance.registerRoleFilter();
                thisInstance.registerSearchButton();
                thisInstance.registerCustomViewModal();

            },

            function(textStatus, errorThrown){
                aDeferred.reject(textStatus, errorThrown);
            }
        );
        return aDeferred.promise();
    },
    registerEvents: function()
    {

        //  Vtiger_List_Js.listInstance = new Vtiger_List_Js();
        //  Vtiger_List_Js.listInstance.getPageCount = function()
        //   {
        //       var aDeferred = jQuery.Deferred();
        //       return aDeferred.promise();
        //   };
        //   Vtiger_List_Js.listInstance.registerEvents();
        // Register custom events
        this.registerCustomViewModal();
        this.registerSearchButton();
        this.registerRoleFilter();
    }

});