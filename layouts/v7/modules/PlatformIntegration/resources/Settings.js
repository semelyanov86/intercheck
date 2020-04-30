/* ********************************************************************************
 * The content of this file is subject to the PlatformIntegration ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

var source = 'THE SOURCE';
var oldBlockName = '';
var oldBlockNameId = 0;
var oldFromTo = '';
var oldFromToId = 0;
var oTable = new Array();
var isDbClick = new Array();
var winPopupConnectToQBO = null;
        
Vtiger_Index_Js("Settings_PlatformIntegration_Settings_Js",{
    instance:false,
    getInstance: function(){
        if(Settings_PlatformIntegration_Settings_Js.instance == false){
            var instance = new Settings_PlatformIntegration_Settings_Js();
            Settings_PlatformIntegration_Settings_Js.instance = instance;
            return instance;
        }
        return Settings_PlatformIntegration_Settings_Js.instance;
    }
},{
    
    registerEventQboApi: function(){
        var thisInstance = this;
        $("body").delegate("#btnSaveQboApi, #btnSaveMainConfig", "click", function(){
            var frm = $(this).closest("form");
            var params2 = {
                submitHandler: function(form) {
                    app.helper.showProgress();
                    var formData = jQuery(form).serialize();
                    app.request.post({data:formData}).then(function(err,data){
                        if(err === null) {
                            app.helper.hideProgress();
                            var params3 = {};
                            if (data.message){
                                params3['message'] = data.message;
                                app.helper.showErrorNotification(params3);
                            } else {
                                params3['message'] = app.vtranslate('LBL_SAVE_CONFIG_DATA_SUCCESSFULLY');
                                app.helper.showSuccessNotification(params3);
                                thisInstance.showHideSynchronizeButton();
                            }
                        }
                        else {
                            app.helper.hideProgress();
                        }
                    });
                }
            };
            frm.vtValidate(params2);
        });
    },

    showHideSynchronizeButton: function () {
        $("#frmSynchronize .btnSync").removeAttr("disabled");
        if ($("#chkSyncToVtiger").prop("checked") == false){
            $("#frmSynchronize .btnSync[data-synctype='Platform2VT']").attr("disabled", true);
        }
        if ($("#chkSyncToQuickBooks").prop("checked") == false){
            $("#frmSynchronize .btnSync[data-synctype='VT2Platform']").attr("disabled", true);
        }
    },
    
    registerEventMappingModuleField: function(){
        
        vtUtils.showSelect2ElementView($("#cbb_qb_modules"));
        vtUtils.showSelect2ElementView($("#cbb_vt_modules"));
        
        $("body").delegate("#cbb_qb_modules", "change", function(){
            var modules = $(this).find("option:selected").attr("data-module");
            if (!modules){
                return;
            }
            modules = modules.split(',');
            var total = modules.length;
            $("#cbb_vt_modules").html("");
            var o = new Option("", "");
            $("#cbb_vt_modules").append(o);
            for (var i = 0; i < total; i++){
                var o = new Option(modules[i], modules[i]);
                $("#cbb_vt_modules").append(o);
            }
        });
    },
    
    validateWhenSavingMappingFields: function(container){
        return true;
    },
    
    registerSyncEvent: function(){
        var thisInstance = this;
        $("body").delegate(".btnSync", "click", function(){
            var parent = $(this).closest("div.row");
            var syncType = $(this).attr("data-syncType");
            var tab = parent.attr("data-tab");
            var params = {
                module: app.getModuleName(),
                action: 'SaveAjax',
                mode: 'platformintegrationSync',
                syncType: syncType,
                tab: tab
            };
            var html = "<div class='divProgressInfo'></div>"
            + "<div class='divProgressFooter'>"
            + "<a onclick='app.helper.hideProgress();' disabled class='btn btn-success btnCloseProgress'>"
            + app.vtranslate('BTN_OK')
            + "</a></div>";
            app.helper.showProgress(html);
            oldBlockName = '';
            oldBlockNameId = 0;
            oldFromTo = '';
            oldFromToId = 0;
            
            var url = 'modules/PlatformIntegration/resources/task.php?' + $.param(params);
            source = new EventSource(url);
             
            //a message is received
            source.addEventListener('message' , function(e) 
            {
                var result = JSON.parse( e.data );
                if (result.message){
                    updateProgressPlatformIntegration(result);
                } else {
                    updateProgressPlatformIntegration(result);
                }
                 
                if(e.data.search('TERMINATE') != -1)
                {
                    $("#messageBar img").remove();
                    $(".btnCloseProgress").removeAttr('disabled');
                    source.close();
                }
            });
             
            source.addEventListener('error' , function(e)
            {
                $("#messageBar img").remove();
                $(".btnCloseProgress").removeAttr('disabled');
                 
                //kill the object ?
                source.close();
            });
        });
        thisInstance.showHideSynchronizeButton();
    },
    
    showAlertBoxToSave: function(){
        var params = [];
        params['message'] = app.vtranslate("MSG_CLICK_SAVE_TO_APPLY_CHANGES");
        app.helper.showAlertNotification(params);
    },
    
    registerAllCustomEvent: function(){
        var thisInstance = this;
        $('body').delegate('#vteqbo_download_button', 'click', function () {
            var html = "<div style='padding-left: 182px;'>" + app.vtranslate('Downloading...') + "</div>";
            app.helper.showProgress(html);
            var params = {
                type: 'GET',
                url: 'index.php',
                dataType: 'json',
                data: {
                    module: 'PlatformIntegration',
                    action: 'SaveAjax',
                    mode: 'downloadSDK'
                }
            };
            app.request.post(params).then(
                function (err, data) {
                    app.helper.hideProgress();

                    if (err === null) {
                        window.location.href = 'index.php?module=PlatformIntegration&parent=Settings&view=Settings';
                    } else {
                        console.log(err);
                    }
                }
            );
        });
        
        $("body").delegate(".config-by-module .btnAddMappingFields", "click", function(){
            var container = $(this).closest(".config-by-module");
            var newRow = container.find("tfoot tr.row-item").clone();
            newRow.removeClass("hide");
            container.find("tbody").append(newRow);
            vtUtils.showSelect2ElementView(newRow.find("select"));
        });
        
        $("body").delegate(".removeLinkedRecord", "click", function(){
            app.helper.showProgress();
            var params = {
                module: app.getModuleName(),
                action: 'SaveAjax',
                mode: 'removeLinkedRecord',
                recordId: $(this).attr("data-id")
            };
            app.request.post({'data':params}).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        if(data.error){
                            var params = {
                                message: data.error,
                                delay: 15000
                            };
                            app.helper.showErrorNotification(params);
                        } else {
                            var params = {
                                message: app.vtranslate('LBL_REMOVE_LINKED_RECORD_SUCCESSFULLY'),
                            };
                            app.helper.showSuccessNotification(params);
                            oTable[0].fnDraw();
                        }
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );
        });
        
        $("body").delegate(".btnSaveDate", "click", function(){
            var dateData = '';
            var dateFields = $(".dateField");
            var total = dateFields.length;
            for (var i = 0; i < total; i++){
                if (dateData){
                    dateData += ';';
                }
                dateField = $(dateFields[i]);
                dateData += dateField.attr('data-tab') + ',' + dateField.val();
            }
            var allowSyncData = '';
            var allowSyncs = $(".allowSyncField");
            total = allowSyncs.length;
            for (var i = 0; i < total; i++){
                if (allowSyncData){
                    allowSyncData += ';';
                }
                allowSyncField = $(allowSyncs[i]);
                if (allowSyncField.prop('checked')){
                    allowSyncData += allowSyncField.attr('data-tab') + ',1';
                } else {
                    allowSyncData += allowSyncField.attr('data-tab') + ',0';
                }
            }
            var params = {
                module: app.getModuleName(),
                action: 'SaveAjax',
                mode: 'savePlatformIntegrationDate',
                dateData: dateData,
                allowSyncData: allowSyncData
            };
            app.helper.showProgress();
            app.request.post({'data':params}).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        if(data.error){
                            var params = {
                                message: data.error,
                                delay: 15000
                            };
                            app.helper.showErrorNotification(params);
                        } else {
                            var params = {
                                message: app.vtranslate('LBL_SAVE_CONFIG_DATA_SUCCESSFULLY'),
                            };
                            app.helper.showSuccessNotification(params);
                        }
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );
        });
        
        $("body").delegate(".config-by-module .btnSaveMappingFields", "click", function(){
            app.helper.showProgress();
            var container = $(this).closest(".config-by-module");
            if (!thisInstance.validateWhenSavingMappingFields(container)){
                return false;
            }
            var vtModule = container.find(".vtModule").val();
            var qboModule = container.find(".qboModule").val();
            var rows = container.find("tbody tr.row-item");
            var mappingFields = "";
            var total = rows.length;
            var vtigerField = '', qboField = '';
            for(var i = 0; i < total; i++){
                vtigerField = $(rows[i]).find("select.vtiger-fields").val();
                qboField = $(rows[i]).find("select.qbo-fields").val();
                if (mappingFields != ''){
                    mappingFields += ';';
                }
                mappingFields += vtigerField + ',' + qboField;
            }
            var params = {
                module: app.getModuleName(),
                action: 'SaveAjax',
                mode: 'saveMappingFields',
                vtModule: vtModule,
                platformModule: qboModule,
                mappingFields: mappingFields
            };
            app.request.post({'data':params}).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        if(data.error){
                            var params = {
                                message: data.error,
                                delay: 15000
                            };
                            app.helper.showErrorNotification(params);
                        } else {
                            var params = {
                                message: app.vtranslate('LBL_SAVE_CONFIG_DATA_SUCCESSFULLY'),
                            };
                            app.helper.showSuccessNotification(params);
                        }
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );
        });
        
        $("body").delegate(".config-by-module .btnRemoveMappingFields", "click", function(){
            $(this).closest("tr").remove();
        });
                
        $("#extensionTab li a:first").trigger("click");
        $(".allowSyncField, .enableSync").bootstrapSwitch();
        
        $('.bootstrap-switch').on('switchChange.bootstrapSwitch', function (event, state) {
            thisInstance.showAlertBoxToSave();
        });
        $('.dateField').change(function(){
            thisInstance.showAlertBoxToSave();
        });
        $("#cbb_primary_datasource").change(function(){
            thisInstance.showAlertBoxToSave();
        });
        
        $("body").delegate(".btnConnectToQBO", "click", function(){
            var url = $(this).attr("data-url");
            // Launch Popup
            var parameters = "location=1,width=800,height=650";
            parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

            winPopupConnectToQBO = window.open(url, 'connectPopup', parameters);
            var pollOAuth = window.setInterval(function () {
                try {
                    if (winPopupConnectToQBO.document.URL.indexOf("code") != -1) {
                        var params = app.convertUrlToDataParams(winPopupConnectToQBO.document.URL);
                        var code = params.code;
                        var realmId = params.realmId;
                        var id = params.id;
                        window.clearInterval(pollOAuth);
                        winPopupConnectToQBO.close();
                        var params = {
                            module: app.getModuleName(),
                            action: 'SaveAjax',
                            mode: 'getAccessTokenKey',
                            id: id,
                            code: code,
                            realmId: realmId
                        };
                        app.request.post({'data':params}).then(
                            function(err,data){
                                if(err === null) {
                                    app.helper.hideProgress();
                                    if(data.error){
                                        var params = {
                                            message: data.error,
                                            delay: 15000
                                        };
                                        app.helper.showErrorNotification(params);
                                    } else {
                                        location.reload();
                                    }
                                }else{
                                    app.helper.hideProgress();
                                }
                            }
                        );
                    }
                } catch (e) {
                    console.log(e)
                }
            }, 100);
        });
        
        $("body").delegate(".btnDisconnectFromQBO", "click", function(){
            var params = {
                module: app.getModuleName(),
                action: 'SaveAjax',
                mode: 'disconnectFromQBO',
            };
            app.request.post({'data':params}).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        if(data.error){
                            var params = {
                                message: data.error,
                                delay: 15000
                            };
                            app.helper.showErrorNotification(params);
                        } else {
                            location.reload();
                        }
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );
        });
    },

	loadContents : function(){
		var thisInstance = this;
		app.helper.showProgress();
        var filter_mode = $(".vteqboDateFilters .dateFilters button.active").attr("data-filtermode");
        var start_range = '';
        var end_range = '';
        selectedRange
        if (filter_mode == 'range'){
            var selectedRange = $(".vteqboDateFilters .selectedRange").html();
            selectedRange = selectedRange.replace('(','').replace(')','');
            selectedRange = selectedRange.split(',');
            start_range = selectedRange[0].trim();
            end_range = selectedRange[1].trim();
        }
        var params = {
            module: app.getModuleName(),
            action: 'SaveAjax',
            mode: 'getSummary',
            filter_mode: filter_mode,
            start_range: start_range,
            end_range: end_range,
        };
        app.request.post({'data':params}).then(
            function(err,data){
                if(err === null) {
                    app.helper.hideProgress();
                    if(data.error){
                        var params = {
                            message: data.error,
                            delay: 15000
                        };
                        app.helper.showErrorNotification(params);
                    } else {
                        $("#vtigerSummary tbody").html(data.vtigerSummary);
                        $("#qboSummary tbody").html(data.qboSummary);
                    }
                }else{
                    app.helper.hideProgress();
                }
            }
        );
	},

	clearExistingCustomScroll : function(){
		var blocksList = jQuery(".contentsBlock");
		blocksList.each(function(index,blockElement){
			var blockElement = jQuery(blockElement);
			var scrollableElement = blockElement.find('.scrollable');
			scrollableElement.mCustomScrollbar('destroy');
		});
	},

	registerDateFilters : function(){
		var thisInstance = this;
        var dateRangeElement = $(".dateRange");
		var pickerParams = {
            format : 'yyyy-mm-dd',
        };
        dateRangeElement.unbind();
		vtUtils.registerEventForDateFields(dateRangeElement, pickerParams);
        
		$("body").delegate(".dateFilters button", "click",function(e){
			var currentTarget = jQuery(e.currentTarget);
			if(!currentTarget.hasClass('rangeDisplay')){
			jQuery('.vteqboDateFilters .dateFilters button').removeClass('active');
				currentTarget.addClass('active');
				thisInstance.clearExistingCustomScroll();
			thisInstance.loadContents();
			app.helper.hideProgress();
			}
		});

		$("body").on('datepicker-change', 'button[data-calendar-type="range"]', function(e){
			var element = jQuery(e.currentTarget);
			jQuery('.vteqboDateFilters .dateFilters button').removeClass('active');
			element.addClass('active');
			var parentContainer = element.closest('.dateFilters');
			parentContainer.find('.selectedRange').html("("+element.val()+")").closest('button').removeClass('hide');
			thisInstance.clearExistingCustomScroll();
			thisInstance.loadContents();
		});

		$("body").delegate('.clearRange', 'click', function(e){
			var container = jQuery('.dateFilters');
			container.find('[data-filtermode="all"]').trigger('click');
			container.find('.rangeDisplay').addClass('hide');
		});
	},
    
    registerEvents: function(){
        this._super();
        this.registerEventQboApi();
        this.registerEventMappingModuleField();
        this.registerAllCustomEvent();
        this.registerSyncEvent();
        this.registerDateFilters();
        /* Tooltip */
        $('.vteqbo-tooltip').popover();
        this.loadContents();
    }
});

function updateProgressPlatformIntegration(result){
    var ele = $(".divProgressInfo");
    var blockName = result.blockName.trim();
    var fromTo = result.fromTo.trim();
    var result = result.result.trim();
    if (oldBlockName != blockName){
        oldBlockNameId += 1;
        oldFromToId = 0;
        ele.append("<div class='block" + oldBlockNameId + "'></div>");
        ele.find('.block' + oldBlockNameId).append("<div class='blockName'>" + blockName + "</div>");
        oldBlockName = blockName;
    }
    if (oldFromTo != fromTo || fromTo == ''){
        oldFromToId += 1;
        info = ele.find('.block' + oldBlockNameId + ' .detailInfo' + oldFromToId);
        if (info.length == 0){
            ele.find('.block' + oldBlockNameId).append("<p class='detailInfo" + oldFromToId + "'></p>");
        }
    }
    info = ele.find('.block' + oldBlockNameId + ' .detailInfo' + oldFromToId);
    var content = fromTo;
    content += '<i>' + result + '</i>';
    info.html(content);
    oldFromTo = fromTo;
}
    
function convertUrlToDataParams(url) {
    var params = {};
    if (typeof url !== 'undefined' && url.indexOf('?') !== -1) {
        var urlSplit = url.split('?');
        url = urlSplit[1];
    }
    var queryParameters = url.split('&');
    for (var index = 0; index < queryParameters.length; index++) {
        var queryParam = queryParameters[index];
        var queryParamComponents = queryParam.split('=');
        params[queryParamComponents[0]] = queryParamComponents[1];
    }
    return params;
}