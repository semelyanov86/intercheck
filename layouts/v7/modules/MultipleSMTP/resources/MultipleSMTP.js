/* ********************************************************************************
 * The content of this file is subject to the Multiple SMTP ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

Vtiger.Class("MultipleSMTP_Js", {
    instance: false,
    getInstance: function () {
        if (MultipleSMTP_Js.instance == false) {
            var instance = new MultipleSMTP_Js();
            MultipleSMTP_Js.instance = instance;
            return instance;
        }
        return MultipleSMTP_Js.instance;
    },
    requestToShowComposeEmailForm: function (selectedId, fieldname, fieldmodule) {
        var selectedFields = [];
        selectedFields.push(fieldname);
        var selectedIds = [];
        selectedIds.push(selectedId);
        var params = {
            'module': 'Emails',
            'fieldModule': fieldmodule,
            'selectedFields[]': selectedFields,
            'selected_ids[]': selectedIds,
            'view': 'ComposeEmail'
        };
        emailsMassEditInstance = Vtiger_Helper_Js.getEmailMassEditInstance();
        /*var emailsMassEditInstance = Vtiger_Helper_Js.getEmailMassEditInstance();
         emailsMassEditInstance.showComposeEmailForm(params);*/
        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();
            if (err == null) {
                var modalContainer = app.helper.showModal(data, {
                    cb: function () {
                        var form = jQuery('#massEmailForm');
                        var actionParams = {};
                        actionParams['view'] = 'MassActionAjax';
                        actionParams['module'] = 'MultipleSMTP';
                        actionParams['mode'] = 'showTOField';
                        app.request.post({data: actionParams}).then(
                            function (err, data) {
                                if (err == null) {
                                    app.helper.hideProgress();
                                    /*if (targetModule == 'EMAILMaker') {
                                     form.find('[name="toEmail"]').closest("div").before(data);
                                     }
                                     else*/
                                    {
                                        var from_serveremailid = form.find("select[name='from_serveremailid']");
                                        if (from_serveremailid.length <= 0) {
                                            form.find('.toEmailField').before(data);
                                            form.find('[name="module"]').val('MultipleSMTP');
                                            $("select[name='from_serveremailid']").css({width: '100%'});
                                            vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                            //jQuery('.from_field').chosen();
                                        }
                                    }
                                }
                            },
                            function (error, err) {

                            }
                        );
                        emailsMassEditInstance.registerEvents();
                    }
                });
            }
        });

    },
}, {
    registerEventForOutgoingButton: function () {
        var thisInstance = this;
        jQuery('.btnOutgoingServer').unbind();
        jQuery('.btnOutgoingServer').on('click', function (e) {
            var currentInstance = window.app.controller();
            var url = 'index.php?module=MultipleSMTP&view=MassActionAjax&mode=showListview&userid=' + currentInstance.getRecordId();
            thisInstance.showListView(url);
        });
    },
    /*remove for task 478958*/
    //registerEventForEditButtons: function() {
    //    var thisInstance=this;
    //    jQuery('body').delegate('.edit_info','click',function(){
    //        var url = jQuery(this).data('url');
    //        thisInstance.showEditView(url);
    //    })
    //},

    //#478958 Begin
    registerEventForEditButtons: function () {
        var thisInstance = this;
        jQuery('body').delegate('.edit_info', 'click', function () {
            var url_ = jQuery(this).data('url');
            $('button.close').trigger('click');
            var func = function () {
                var url = url_;
                app.helper.hideModal();
                $('#overlayPageContent').remove();
                app.helper.showProgress();
                var params = app.convertUrlToDataParams(url);
                app.request.post({data: params}).then(
                    function (err, data) {
                        if (err == null) {
                            app.helper.hideProgress();
                            app.helper.showModal(data);
                            var form = jQuery('#outgoingMassEditContainer');
                            form.on("click", "button[name='saveButton']", function (e) {
                                e.preventDefault();
                                thisInstance.saveOutgoingServer(form);
                            });
                        }
                    }
                );
            };
            setTimeout(func, 2000);

        })
    },
    //#478958 End

    registerEventForDeleteRecord: function () {
        var thisInstance = this;
        jQuery('body').on('click', '.delete_server', function () {
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            var url = jQuery(this).data('url');
            var userid = jQuery(this).data('userid');
            app.helper.showConfirmationBox({'message': message}).then(
                function () {
                    thisInstance.deleteServer(url, userid);
                }
            );
        })
    },
    /*remove for task 478958*/
    //showEditView: function (url) {
    //    var thisInstance = this;
    //    app.helper.hideModal();
    //    app.helper.showProgress();
    //
    //    app.request.get({'url' :url}).then(function(err,resp) {
    //        app.helper.hideProgress();
    //        app.helper.showModal(resp);
    //        var form = jQuery('#outgoingMassEditContainer');
    //        form.on("click","button[name='saveButton']",function(e){
    //            e.preventDefault();
    //            thisInstance.saveOutgoingServer(form);
    //        });
    //    });
    //},
    /*remove for task 478958*/
    //#478958 Begin
    //showEditView: function (url) {
    //    var thisInstance = this;
    //    app.helper.hideModal();
    //    $('#overlayPageContent').remove();
    //    app.helper.showProgress();
    //    var params = app.convertUrlToDataParams(url);
    //    app.request.post({data:params}).then(
    //        function (err,data) {
    //            if(err == null) {
    //                app.helper.hideProgress();
    //                app.helper.showModal(data);
    //                var form = jQuery('#outgoingMassEditContainer');
    //                form.on("click","button[name='saveButton']",function(e){
    //                    e.preventDefault();
    //                    thisInstance.saveOutgoingServer(form);
    //                });
    //            }
    //        }
    //    );
    //},
    //#478958 End

    // haph86@gmail.com - #41367
    showListView: function (url) {
        var thisInstance = this;
        app.helper.hideModal();
        app.helper.showProgress();
        popupShown = true;
        app.request.post({'url': url}).then(function (err, resp) {
            app.helper.hideProgress();
            if (err === null) {
                app.helper.showModal(resp, {
                    'cb': function (modal) {
                        popupShown = false;
                    }
                });

                thisInstance.loadListUserServer();
            }
        });
    },

    deleteServer: function (url, userid) {
        var thisInstance = this;
        app.helper.showProgress();
        app.request.post({'url': url}).then(
            function (err, data) {
                if (err === null) {
                    app.helper.hideProgress();
                    var new_url = 'index.php?module=MultipleSMTP&view=MassActionAjax&mode=showListview&userid=' + userid;
                    thisInstance.showListView(new_url);
                }
            }
        );
    },

    saveOutgoingServer: function (form) {
        var thisInstance = this;
        app.helper.showProgress();
        var data = form.serializeFormData();
        data['module'] = 'MultipleSMTP';
        data['action'] = 'SaveAjax';
        app.request.post({'data': data}).then(
            function (err, data) {
                if (err === null) {
                    app.helper.hideProgress();
                    app.helper.hideModal();
                    app.helper.showSuccessNotification({'message': app.vtranslate('Outgoing Server Saved')});
                } else {
                    app.helper.hideProgress();
                    app.helper.showErrorNotification({'message': app.vtranslate("Can't save data. Error occurred while sending mail")});
                }
            },
            function (error) {
                app.helper.hideProgress();
                //TODO : Handle error
            }
        );
    },
    loadListUserServer: function () {
        var thisInstance = this;
        var blocks = jQuery('.blocksSortable');
        blocks.sortable({
            'revert': true,
            'connectWith': ".blocksSortable",
            'tolerance': 'pointer',
            'cursor': 'move',
            'placeholder': "state-highlight",
            'stop': function (event, ui) {
                thisInstance.updateSequence();
            }
        });
    },
    updateSequence: function () {
        var thisInstance = this;
        var params = {};
        $(".blockSortable").each(function (index) {
            //alert(index);
            params[$(this).data('id')] = {'index': index, 'column': $(this).closest('.blocksSortable').data('column')};
        });
        thisInstance.registerSaveEvent('updateSequence', {'data': params});
    },
    registerSaveEvent: function (mode, data) {
        var resp = '';
        var params = {};
        params.data = {
            module: 'MultipleSMTP',
            action: 'ActionAjax',
            mode: mode,
            params: data
        };
        if (mode == 'saveMultipleSMTP') {
            params.async = false;
        } else {
            params.async = true;
        }
        params.dataType = 'json';
        app.request.post(params).then(
            function (err, data) {
                if (data) {
                    app.helper.showSuccessNotification({'message': data['message']});
                }
            }
        );
    },

    registerEvents: function () {
        this.registerEventForOutgoingButton();
        this.registerEventForEditButtons();
        this.registerEventForDeleteRecord();
    }
});

jQuery(document).ready(function () {
    setTimeout(function () {
        initData_MultipleSMTP();
    }, 3000);
});

function initData_MultipleSMTP() {
    // Only load when loadHeaderScript=1 BEGIN #241208
    if (typeof VTECheckLoadHeaderScript == 'function') {
        if (!VTECheckLoadHeaderScript('MultipleSMTP')) {
            return;
        }
    }
    // Only load when loadHeaderScript=1 END #241208

    /*var instance = new MultipleSMTP_Js();
     instance.registerEvents();*/
    // Does not load on edit view
    if (app.view() == 'Edit') return;
    // Check enable
    var params = {};
    params['action'] = 'ActionAjax';
    params['module'] = 'MultipleSMTP';
    params['mode'] = 'checkEnable';

    app.request.post({data: params}).then(
        function (err, data) {
            if (err == null) {
                if (data.enable == '1') {
                    var sPageURL = window.location.search.substring(1);
                    var targetModule = '';
                    var targetView = '';
                    var targetRecord = '';
                    var sURLVariables = sPageURL.split('&');
                    for (var i = 0; i < sURLVariables.length; i++) {
                        var sParameterName = sURLVariables[i].split('=');
                        if (sParameterName[0] == 'module') {
                            targetModule = sParameterName[1];
                        }
                        else if (sParameterName[0] == 'view') {
                            targetView = sParameterName[1];
                        }
                        else if (sParameterName[0] == 'record') {
                            targetRecord = sParameterName[1];
                        }
                    }
                    if (targetModule == 'Users' && (targetView == 'Detail' || targetView == 'PreferenceDetail')) {
                        var buttonContainer = jQuery('.detailViewContainer');
                        var btnToolBar = buttonContainer.find('.btn-group');
                        var outGoingServerBtn = jQuery('<button type="button" class="btn btn-default btnOutgoingServer">Outgoing Server</button>');
                        btnToolBar.find('.btn-default:last').before(outGoingServerBtn);

                        var instance = new MultipleSMTP_Js();
                        instance.registerEvents();
                    }
                }
            }
        }
    );
    if (typeof Vtiger_EmailPreview_Js !== 'undefined') {
        Vtiger_EmailPreview_Js.prototype.registerEventsForActionButtons = function () {
            var thisInstance = this;
            app.helper.showVerticalScroll(jQuery('#toAddressesDropdown'));
            jQuery('[name="previewReplyAll"], [name="previewReply"], [name="previewForward"], [name="previewEdit"]').on('click', function (e) {
                var module = "Emails";
                app.helper.checkServerConfig(module).then(function (data) {
                    if (data === true) {
                        var mode = jQuery(e.currentTarget).data('mode');
                        var params = thisInstance.getEmailActionsParams(mode);
                        var container = jQuery(e.currentTarget).closest('.modal');
                        container.one('hidden.bs.modal', function () {
                            app.helper.hidePopup();
                            app.helper.showProgress();
                            app.request.post({data: params}).then(function (err, data) {
                                app.helper.hideProgress();
                                if (err === null) {
                                    /*app.helper.showModal(data);
                                     var emailEditInstance = new Emails_MassEdit_Js();
                                     emailEditInstance.registerEvents();*/
                                    var modalContainer = app.helper.showModal(data, {
                                        cb: function () {
                                            var form = jQuery('#massEmailForm');
                                            var actionParams = {};
                                            actionParams['view'] = 'MassActionAjax';
                                            actionParams['module'] = 'MultipleSMTP';
                                            actionParams['mode'] = 'showTOField';
                                            actionParams['parent_id'] = jQuery('[name="parent_id"]').val();
                                            app.request.post({data: actionParams}).then(
                                                function (err, data) {
                                                    if (err == null) {
                                                        app.helper.hideProgress();
                                                        /*if (targetModule == 'EMAILMaker') {
                                                         form.find('[name="toEmail"]').closest("div").before(data);
                                                         }
                                                         else*/
                                                        {
                                                            var from_serveremailid = form.find("select[name='from_serveremailid']");
                                                            if (from_serveremailid.length <= 0) {
                                                                form.find('.toEmailField').before(data);
                                                                form.find('[name="module"]').val('MultipleSMTP');
                                                                $("select[name='from_serveremailid']").css({width: '100%'});
                                                                vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                                                //jQuery('.from_field').chosen();
                                                            }
                                                        }
                                                    }
                                                },
                                                function (error, err) {

                                                }
                                            );
                                            var editInstance = new Emails_MassEdit_Js();
                                            editInstance.registerEvents();
                                        }
                                    });
                                }
                            });

                        });
                        container.modal('hide');

                    } else {
                        app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                    }
                })
            });
            jQuery('[name="previewPrint"]').on('click', function (e) {
                var module = "Emails";
                app.helper.hideModal();
                var mode = jQuery(e.currentTarget).data('mode');
                var params = thisInstance.getEmailActionsParams(mode);
                var urlString = (typeof params == 'string') ? params : jQuery.param(params);
                var url = 'index.php?' + urlString;
                window.open(url, '_blank');
            });
        }
    }
    if (typeof Emails_MassEdit_Js !== 'undefined') {
        Emails_MassEdit_Js.prototype.registerEventsForToField = function () {
            var thisInstance = this;
            this.getMassEmailForm().on('click', '.selectEmail', function (e) {
                var moduleSelected = jQuery('.emailModulesList').select2('val');
                var parentElem = jQuery(e.target).closest('.toEmailField');
                var sourceModule = jQuery('[name=module]').val();
                var params = {
                    'module': moduleSelected,
                    'src_module': 'Emails',
                    'view': 'EmailsRelatedModulePopup'
                };
                var popupInstance = Vtiger_Popup_Js.getInstance();
                popupInstance.show(params, function (data) {
                    var responseData = JSON.parse(data);
                    for (var id in responseData) {
                        var data = {
                            'name': responseData[id].name,
                            'id': id,
                            'emailid': responseData[id].email
                        };
                        thisInstance.setReferenceFieldValue(parentElem, data);
                        thisInstance.addToEmailAddressData(data);
                        thisInstance.appendToSelectedIds(id);
                        thisInstance.addToEmails(data);
                    }
                }, 'relatedEmailModules');
            });

            this.getMassEmailForm().on('click', '[name="clearToEmailField"]', function (e) {
                var element = jQuery(e.currentTarget);
                element.closest('div.toEmailField').find('.sourceField').val('');
                thisInstance.getMassEmailForm().find('[name="toemailinfo"]').val(JSON.stringify([]));
                thisInstance.getMassEmailForm().find('[name="selected_ids"]').val(JSON.stringify([]));
                thisInstance.getMassEmailForm().find('[name="to"]').val(JSON.stringify([]));

                var preloadData = [];
                thisInstance.setPreloadData(preloadData);
                thisInstance.getMassEmailForm().find('#emailField').select2('data', preloadData);
            });
            //thisInstance.registerAutoCompleteFields(thisInstance.getMassEmailForm());
        };
    }
    if (typeof Settings_Workflows_Edit_Js !== 'undefined') {
        Settings_Workflows_Edit_Js.prototype.registerMultipleSMTPEmailTaskEvents = function () {
            var textAreaElement = jQuery('#content');
            var ckEditorInstance = this.getckEditorInstance();
            ckEditorInstance.loadCkEditor(textAreaElement);
            this.registerFillMailContentEvent();
            this.registerFillTaskFromEmailFieldEvent();
            this.registerCcAndBccEvents();

            jQuery('#saveTask').on('submit', function () {
                var textAreaElement = jQuery('#content');
                textAreaElement.val(CKEDITOR.instances['content'].getData());
            });
        };
    }
    if (typeof Vtiger_Helper_Js !== 'undefined') {
        Vtiger_Helper_Js.getInternalMailer = function (selectedId, fieldname, fieldmodule) {
            var module = 'MultipleSMTP';
            var cacheResponse = Vtiger_Helper_Js.checkServerConfigResponseCache;
            var checkServerConfigPostOperations = function (data) {
                if (data == true) {
                    MultipleSMTP_Js.requestToShowComposeEmailForm(selectedId, fieldname, fieldmodule);
                } else {
                    alert(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                }
            };
            if (cacheResponse === '') {
                app.helper.checkServerConfig(module).then(function (data) {
                    Vtiger_Helper_Js.checkServerConfigResponseCache = data;
                    checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
                });
            } else {
                checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
            }

        };


    }

    $('[id*=LBL_SEND_EMAIL]').attr("onclick", "javascript: void(0);");
    $('[id*=LBL_SEND_EMAIL]').unbind();
    $('[name="composeEmail"]').attr("onclick", "javascript: void(0);");
    $('[name="composeEmail"]').unbind();
    var buttonSendMail = $('[id*=moreAction_Send_Email_with_PDF]').find('a');
    buttonSendMail.addClass('smtp_email');
    buttonSendMail.attr('href', 'javascript: void(0);');
    buttonSendMail.unbind();
    jQuery(document).on('click', '[id*=LBL_SEND_EMAIL], [name="composeEmail"], .smtp_email', function (e) {
        var viewName = jQuery.url().param().view;
        if (viewName != undefined && viewName == 'List')
            Vtiger_List_Js.triggerSendEmail("index.php?module=" + app.getModuleName() + "&view=MassActionAjax&mode=showComposeEmailForm&step=step1", "Emails");
        else
            Vtiger_Detail_Js.triggerSendEmail("index.php?module=" + app.getModuleName() + "&view=MassActionAjax&mode=showComposeEmailForm&step=step1", "Emails");
        /*var sPageURL = window.location.search.substring(1);
        var targetModule = '';
        var targetView = '';
        var targetRecord = '';
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) {
           var sParameterName = sURLVariables[i].split('=');
           if (sParameterName[0] == 'module') {
               targetModule = sParameterName[1];
           }
           else if (sParameterName[0] == 'view') {
               targetView = sParameterName[1];
           }
           else if (sParameterName[0] == 'record') {
               targetRecord = sParameterName[1];
           }
        }
        //
        var detailActionUrl = "index.php?module="+app.getModuleName()+"&view=MassActionAjax&mode=showComposeEmailForm&step=step1";
        var parentRecord = new Array();
        var vtList = new Vtiger_List_Js();
        var selectedIds = vtList.readSelectedIds();
        for (var i=0,l=selectedIds.length;i<l;i++) {
           parentRecord.push(""+selectedIds[i]);
        }
        var urlParams = app.convertUrlToDataParams(detailActionUrl);
        urlParams['selected_ids'] = parentRecord;
        app.helper.showProgress();
        app.hideModalWindow();
        app.helper.hideModal();
        Vtiger_Index_Js.showComposeEmailPopup(urlParams, function () {
           var form = jQuery('#massEmailForm');
           var actionParams = {};
           actionParams['view']= 'MassActionAjax';
           actionParams['module'] = 'MultipleSMTP';
           actionParams['mode'] = 'showTOField';
           app.request.post({data:actionParams}).then(
               function (err,data) {
                   if(err == null) {
                       app.helper.hideProgress();
                       if (targetModule == 'EMAILMaker') {
                        form.find('[name="toEmail"]').closest("div").before(data);
                        }
                        else {
                           var from_serveremailid = form.find("select[name='from_serveremailid']");
                           if(from_serveremailid.length <= 0) {
                               form.find('.toEmailField').before(data);
                               form.find('[name="module"]').val('MultipleSMTP');
                               //jQuery('.from_field').chosen();
                           }
                       }
                   }
               }
           );
        });*/

    });

    var parentId = jQuery('#recordId').val();

    $('[name="emailsRelatedRecord"]').attr("name", "vte_emailsRelatedRecord");
    $('[name="emailsEditView"]').attr("name", "vte_emailsEditView");
    $('[name="emailsDetailView"]').attr("name", "vte_emailsDetailView");
    var params = {};
    params['module'] = "Emails";
    params['view'] = "ComposeEmail";
    params['parentId'] = parentId;
    params['relatedLoad'] = true;
    jQuery(document).on('click', '[name="vte_emailsRelatedRecord"], [name="vte_emailsDetailView"]', function (e) {
        e.stopPropagation();
        var element = jQuery(e.currentTarget);
        var recordId = element.data('id');
        if (element.data('emailflag') == 'SAVED') {
            var mode = 'emailEdit';
        } else {
            mode = 'emailPreview';
            params['parentModule'] = app.getModuleName();
        }
        params['mode'] = mode;
        params['record'] = recordId;
        app.helper.showProgress();
        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();
            if (err === null) {
                var dataObj = jQuery(data);
                var descriptionContent = dataObj.find('#iframeDescription').val();
                app.helper.showModal(data, {
                    cb: function () {
                        if (mode === 'emailEdit') {
                            var form = jQuery('#massEmailForm');
                            var actionParams = {};
                            actionParams['view'] = 'MassActionAjax';
                            actionParams['module'] = 'MultipleSMTP';
                            actionParams['mode'] = 'showTOField';
                            app.request.post({data: actionParams}).then(
                                function (err, data) {
                                    if (err == null) {
                                        app.helper.hideProgress();
                                        /*if (targetModule == 'EMAILMaker') {
                                         form.find('[name="toEmail"]').closest("div").before(data);
                                         }
                                         else*/
                                        {
                                            var from_serveremailid = form.find("select[name='from_serveremailid']");
                                            if (from_serveremailid.length <= 0) {
                                                form.find('.toEmailField').before(data);
                                                form.find('[name="module"]').val('MultipleSMTP');
                                                $("select[name='from_serveremailid']").css({width: '100%'});
                                                vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                                //jQuery('.from_field').chosen();
                                            }
                                        }
                                    }
                                },
                                function (error, err) {

                                }
                            );

                            var editInstance = new Emails_MassEdit_Js();
                            editInstance.registerEvents();
                        } else {
                            app.event.trigger('post.EmailPreview.load', null);
                        }
                        jQuery('#emailPreviewIframe').contents().find('html').html(descriptionContent);
                        jQuery("#emailPreviewIframe").height(jQuery('.email-body-preview').height());
                        jQuery('#emailPreviewIframe').contents().find('html').find('a').on('click', function (e) {
                            e.preventDefault();
                            var url = jQuery(e.currentTarget).attr('href');
                            window.open(url, '_blank');
                        });
                        //jQuery("#emailPreviewIframe").height(jQuery('#emailPreviewIframe').contents().find('html').height());
                    }
                });
            }
        });
    });
    jQuery(document).on('click', '[name="vte_emailsEditView"]', function (e) {
        e.stopPropagation();
        var module = "Emails";
        app.helper.checkServerConfig(module).then(function (data) {
            if (data == true) {
                var element = jQuery(e.currentTarget);
                var closestROw = element.closest('tr');
                var recordId = closestROw.data('id');
                var parentRecord = [];
                parentRecord.push(parentId);

                params['mode'] = "emailEdit";
                params['record'] = recordId;
                params['selected_ids'] = parentRecord;
                app.helper.showProgress();
                app.request.post({'data': params}).then(function (err, data) {
                    app.helper.hideProgress();
                    if (err === null) {
                        var modalContainer = app.helper.showModal(data, {
                            cb: function () {
                                var form = jQuery('#massEmailForm');
                                var actionParams = {};
                                actionParams['view'] = 'MassActionAjax';
                                actionParams['module'] = 'MultipleSMTP';
                                actionParams['mode'] = 'showTOField';
                                app.request.post({data: actionParams}).then(
                                    function (err, data) {
                                        if (err == null) {
                                            app.helper.hideProgress();
                                            /*if (targetModule == 'EMAILMaker') {
                                             form.find('[name="toEmail"]').closest("div").before(data);
                                             }
                                             else*/
                                            {
                                                var from_serveremailid = form.find("select[name='from_serveremailid']");
                                                if (from_serveremailid.length <= 0) {
                                                    form.find('.toEmailField').before(data);
                                                    form.find('[name="module"]').val('MultipleSMTP');
                                                    $("select[name='from_serveremailid']").css({width: '100%'});
                                                    vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                                    //jQuery('.from_field').chosen();
                                                }
                                            }
                                        }
                                    },
                                    function (error, err) {

                                    }
                                );
                                var editInstance = new Emails_MassEdit_Js();
                                editInstance.registerEvents();
                            }
                        });
                    }
                });
            } else {
                app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
            }
        })
    });
}

jQuery(document).ajaxComplete(function (event, request, settings) {
    /*if(settings.url.indexOf('type=MultipleSMTPEmailTask') != -1){
        var textAreaElement = jQuery('#content');
        var ckEditorInstance = new Vtiger_CkEditor_Js();
        ckEditorInstance.loadCkEditor(textAreaElement);
    }*/
    $('[name="composeEmail"]').attr("onclick", "javascript: void(0);");
    $('[name="composeEmail"]').unbind();
    $('[id*=LBL_SEND_EMAIL]').attr("onclick", "javascript: void(0);");
    $('[id*=LBL_SEND_EMAIL]').unbind();
    $('[name="composeEmail"]').attr("onclick", "javascript: void(0);");
    $('[name="composeEmail"]').unbind();
    var buttonSendMail = $('[id*=moreAction_Send_Email_with_PDF]').find('a');
    buttonSendMail.addClass('smtp_email');
    buttonSendMail.attr('href', 'javascript: void(0);');
    buttonSendMail.unbind();
    var url = settings.data;
    if (typeof url == 'undefined' && settings.url) url = settings.url;
    if (url && url.constructor.name == 'String' && url.indexOf('module=Emails') > -1 && url.indexOf('view=ComposeEmail') > -1) {
        var module = "Emails";
        var recordId = jQuery('#recordId').val();
        // set delay to wait until the compose email is loaded
        var interval1 = setInterval(function () {
            app.helper.checkServerConfig(module).then(function (data) {
                if (data == true) {
                    var parentRecord = [];
                    parentRecord.push(recordId);
                    var params = {};
                    params['mode'] = "emailEdit";
                    params['record'] = recordId;
                    params['selected_ids'] = parentRecord;
                    app.request.post({'data': params}).then(function (err, data) {
                        if (err === null) {
                            var form = jQuery('#massEmailForm');
                            var actionParams = {};
                            actionParams['view'] = 'MassActionAjax';
                            actionParams['module'] = 'MultipleSMTP';
                            actionParams['mode'] = 'showTOField';
                            app.request.post({data: actionParams}).then(
                                function (err, data) {
                                    if (err == null) {
                                        app.helper.hideProgress();
                                        var from_serveremailid = form.find("select[name='from_serveremailid']");
                                        if (from_serveremailid.length <= 0) {
                                            form.find('.toEmailField').before(data);
                                            form.find('[name="module"]').val('MultipleSMTP');
                                            $("select[name='from_serveremailid']").css({width: '100%'});
                                            vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                            clearInterval(interval1);
                                        }
                                    }
                                },
                                function (error, err) {
                                }
                            );
                            // var editInstance = new Emails_MassEdit_Js();
                            // editInstance.registerEvents();
                        }
                    });
                } else {
                    app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                }
            })
        }, 300);
    }
    if (url && url.constructor.name == 'String' && url.indexOf('module=PDFMaker') > -1 && url.indexOf('view=SendEmail') > -1) {
        var module = "Emails";
        var recordId = jQuery('#recordId').val();
        // set delay to wait until the compose email is loaded
        var interval2 = setInterval(function () {
            app.helper.checkServerConfig(module).then(function (data) {
                if (data == true) {
                    var parentRecord = [];
                    parentRecord.push(recordId);
                    var params = {};
                    params['mode'] = "emailEdit";
                    params['record'] = recordId;
                    params['selected_ids'] = parentRecord;
                    app.request.post({'data': params}).then(function (err, data) {
                        if (err === null) {
                            var form = jQuery('#massEmailForm');
                            var actionParams = {};
                            actionParams['view'] = 'MassActionAjax';
                            actionParams['module'] = 'MultipleSMTP';
                            actionParams['mode'] = 'showTOField';
                            app.request.post({data: actionParams}).then(
                                function (err, data) {
                                    if (err == null) {
                                        app.helper.hideProgress();
                                        var from_serveremailid = form.find("select[name='from_serveremailid']");
                                        if (from_serveremailid.length <= 0) {
                                            form.find('.toEmailField').before(data);
                                            form.find('[name="module"]').val('MultipleSMTP');
                                            $("select[name='from_serveremailid']").css({width: '100%'});
                                            vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                            clearInterval(interval2);
                                        }
                                    }
                                },
                                function (error, err) {
                                }
                            );
                            // var editInstance = new Emails_MassEdit_Js();
                            // editInstance.registerEvents();
                        }
                    });
                } else {
                    app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                }
            })
        }, 300);
    }
    if (url && url.constructor.name == 'String' && url.indexOf('module=QuotingTool') > -1 && url.indexOf('view=EmailPreviewTemplate') > -1) {
        var module = "Emails";
        var recordId = jQuery('#recordId').val();
        var focusModule = 'QuotingTool';
        // set delay to wait until the compose email is loaded
        var interval3 = setInterval(function () {
            app.helper.checkServerConfig(module).then(function (data) {
                if (data == true) {
                    var parentRecord = [];
                    parentRecord.push(recordId);
                    var params = {};
                    params['mode'] = "emailEdit";
                    params['record'] = recordId;
                    params['selected_ids'] = parentRecord;
                    app.request.post({'data': params}).then(function (err, data) {
                        if (err === null) {
                            var form = jQuery('#quotingtool_emailtemplate');
                            var actionParams = {};
                            actionParams['view'] = 'MassActionAjax';
                            actionParams['module'] = 'MultipleSMTP';
                            actionParams['mode'] = 'showTOField';
                            actionParams['module_focus'] = focusModule;
                            app.request.post({data: actionParams}).then(
                                function (err, data) {
                                    if (err == null) {
                                        app.helper.hideProgress();
                                        var from_serveremailid = form.find("select[name='from_serveremailid']");
                                        if (from_serveremailid.length <= 0) {
                                            form.find("[name='multip_SMTP']").html(data);
                                            $('[name="from_serveremailid"]').select2();
                                            $('[name="from_serveremailid"]').trigger('liszt:updated');
                                            clearInterval(interval3);
                                        }
                                    }
                                },
                                function (error, err) {
                                }
                            );
                        }
                    });
                } else {
                    app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                }
            })
        }, 300);
    }
    if (url && url.constructor.name == 'String' && url.indexOf('module=EmailTemplates') > -1 && url.indexOf('view=Popup') > -1) {
        if(jQuery('#recordId').length > 0){
            var recordId = jQuery('#recordId').val();
        }else{
            recordId = 0;
        }
        var interval4 = setInterval(function () {
            if ($('#popupPageContainer #module').val() == 'EmailTemplates') {
                clearInterval(interval4);
                var url = 'index.php?module=MultipleSMTP&action=ActionAjax&mode=mergeTemplates&record=' + recordId;
                app.request.get({'url': url}).then(function (err, resp) {
                    for (i = 0; i < resp.length; i++) {
                        if ($('.emailTemplatesPopupTableContainer').find('.listViewEntries[data-id=' + resp[i].id + ']').length > 0) {
                            $('.emailTemplatesPopupTableContainer').find('.listViewEntries[data-id=' + resp[i].id + ']').data('info', resp[i].content);
                        }
                    }
                });
            }
            ;
        }, 300);
    }
    if (url && url.constructor.name == 'String' && url.indexOf('module=MailManager') > -1 && url.indexOf('view=ComposeEmail') > -1) {
        var module = "Emails";
        var recordId = jQuery('#recordId').val();
        // set delay to wait until the compose email is loaded
        var interval1 = setInterval(function () {
            app.helper.checkServerConfig(module).then(function (data) {
                if (data == true) {
                    var parentRecord = [];
                    parentRecord.push(recordId);
                    var params = {};
                    params['mode'] = "emailEdit";
                    params['record'] = recordId;
                    params['selected_ids'] = parentRecord;
                    app.request.post({'data': params}).then(function (err, data) {
                        if (err === null) {
                            var form = jQuery('#massEmailForm');
                            var actionParams = {};
                            actionParams['view'] = 'MassActionAjax';
                            actionParams['module'] = 'MultipleSMTP';
                            actionParams['mode'] = 'showTOField';
                            app.request.post({data: actionParams}).then(
                                function (err, data) {
                                    if (err == null) {
                                        app.helper.hideProgress();
                                        var from_serveremailid = form.find("select[name='from_serveremailid']");
                                        if (from_serveremailid.length <= 0) {
                                            form.find('.toEmailField').before(data);
                                            form.find('[name="module"]').val('MultipleSMTP');
                                            $("select[name='from_serveremailid']").css({width: '100%'});
                                            vtUtils.showSelect2ElementView($("select[name='from_serveremailid']"));
                                            clearInterval(interval1);
                                        }
                                    }
                                },
                                function (error, err) {
                                }
                            );
                            // var editInstance = new Emails_MassEdit_Js();
                            // editInstance.registerEvents();
                        }
                    });
                } else {
                    app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
                }
            })
        }, 300);
    }
});
//#601125
$(window).on('shown.bs.modal', function () {
    var table = $('table#tableQuotingToolWidget');
    if (table.length > 0) {
        table.find('a[data-action="preview_and_send_email"]').attr('data-multi-smtp', 'true');
    }
});
//#601125 end

