jQuery.Class("CloudPBX_Js", {
    showPhonePopup : function(id, module) {
        var actionParams = {
            module : 'CloudPBX',
            view : 'MassActionAjax',
            mode : 'showPhonesPopup',
            parent: module,
            record : id,
        }
        app.helper.showProgress();
        app.request.post({data: actionParams}).then(
            function(err,data) {
                if (err === null) {
                    var containerModal = $(data);
                    var modal = $(containerModal).find('#PopupReminder');
                    if($('.popupReminderContainer').length > 0 && $('#PopupReminder').hasClass('in') != true) {
                        $('.popupReminderContainer').html(modal);
                    }else if($('.popupReminderContainer').length == 0) {
                        $('body').append(containerModal);
                    }
                    var actives = modal.data('info');
                    if( $('#PopupReminder').hasClass('in') != true) {
                        $('#PopupReminder').modal('show');
                    }
                } else {
                    app.helper.showErrorNotification({message: err.message});
                }
                app.helper.hideProgress();
            }
        );
    },
    doCall : function(phoneNumber) {
        var params = {};
        params['mode'] = 'startOutgoingCall';
        params['module'] = 'CloudPBX';
        params['action'] = 'IntegrationActions';
        params['number'] = phoneNumber;
        app.request.post({data: params}).then(function (e, result) {
            if (result) {
                params = {
                    text : app.vtranslate('JS_PBX_OUTGOING_SUCCESS'),
                    type : 'info'
                }
            } else if (e){
                params = {
                    text : app.vtranslate('JS_PBX_OUTGOING_FAILURE'),
                    type : 'info'
                }
            }
            Vtiger_PBXManager_Js.showPnotify(params);
        });
    },
}, {
    
    registerClick2Call : function() {
        var thisInstance = this;
        var params = {};
        params['mode'] = 'getOutgoingPermissions';
        params['module'] = 'CloudPBX';        
        params['action'] = 'IntegrationActions';
        app.request.post({data: params}).then(function (e, result) {
            var permission = result.permission;            
            if (permission == 'full_permission') {
                Vtiger_PBXManager_Js.makeOutboundCall = function(number, record){
                    thisInstance.click2Call(number);
                };
            } else if (permission == 'outgoing') {
                var form = jQuery('#detailView');       
                form.on('click','.value',function(e){                    
                    var currentTarget = jQuery(e.currentTarget);	
                    if (currentTarget.data('fieldType') == 'phone') {
                        params = {
                            text : app.vtranslate('JS_PBX_OUTGOING_CALL'),
                            type : 'info'
                        }
                        Vtiger_PBXManager_Js.showPnotify(params);
                        thisInstance.click2Call(jQuery.trim(currentTarget.text()));
                    }           
                });
            }
        });        
    },
    
    click2Call : function(phoneNumber) {
        var params = {};
        params['mode'] = 'startOutgoingCall';
        params['module'] = 'CloudPBX';        
        params['action'] = 'IntegrationActions';        
        params['number'] = phoneNumber;
        app.request.post({data: params}).then(function (e, result) {
            if (result) {    
                params = {
                    text : app.vtranslate('JS_PBX_OUTGOING_SUCCESS'),
                    type : 'info'
                }
            } else if (e){
                params = {
                    text : app.vtranslate('JS_PBX_OUTGOING_FAILURE'),
                    type : 'info'
                }
            }
            Vtiger_PBXManager_Js.showPnotify(params);
        });
    },
    
    registerEvents : function () {              
        this.registerClick2Call();    
    }
});

jQuery(document).ready(function () {
    var controller = new CloudPBX_Js();
    controller.registerEvents();
});

