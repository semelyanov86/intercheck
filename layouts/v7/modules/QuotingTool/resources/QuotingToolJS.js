/* ********************************************************************************
 * The content of this file is subject to the Quoting Tool ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

/** @class QuotingToolJS */
Vtiger.Class('QuotingToolJS', {
    triggerShowModal: function () {
        var thisInstance = this;
        var module = app.getModuleName();
        var recordId=app.getRecordId();
        var view = app.view();
        // Add Quoting Tool button
            var params = {
                'action': 'ActionAjax',
                'mode': 'getTemplate',
                'module': 'QuotingTool',
                'record_id':recordId,
                'rel_module': module
            };
            app.request.post({data: params}).then(
                function (err, data) {
                    app.helper.hideProgress();
                    if(data['checkMPDF']){
                        alert('MPDF Library is missing or needs to be upgraded. Please go to Document Designer template list/configuration to download MPDF library.');
                        window.open(data['checkMPDF'], '_blank');
                    }else if (err === null) {
                        var templates = data;
                        if (templates['data'].length > 0) {
                            var quotingTool = new QuotingToolJS();
                            quotingTool.showWidgetModal(templates)
                        }
                    } else {
                        console.log(err);
                    }
                }
            );
    }
}, {
    MODULE: 'QuotingTool',
    detailViewButtoncontainer: null,

    /**
     * Fn - getSelectedTemplates
     * @returns {*}
     */
    getSelectedTemplates: function () {
        var lstTemplates = jQuery('#lstTemplates');
        var selected = lstTemplates.val();

        if (selected == null || selected.length == 0) {
            alert(app.vtranslate('Please select template'));
            return null;
        }

        if (typeof selected !== 'Array') {
            return selected;
        }

        // When multi select
        var strSelected = '';
        for (var i = 0; i < selected.length; i++) {
            strSelected += selected[i] + '+';
        }

        strSelected = strSelected.substring(0, strSelected.length - 1);

        return strSelected;
    },

    /**
     * Function returns the record id
     */
    getRecordId: function () {
        var record = jQuery('#recordId');
        if (record.length) {
            return record.val();
        }
        return false;
    },

    /**
     * Fn - registerWidgetActions
     */
    registerWidgetActions: function () {
        var thisInstance = this;
        var module = app.getModuleName();
        var recordId = thisInstance.getRecordId();
        if(!recordId){
            recordId = 0;
        }

        // Export PDF
        jQuery(document).on('click', '[data-action="export"]', function (e) {
            e.preventDefault();
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                templateId = thisInstance.getSelectedTemplates();
            }

            if (templateId) {
                if(app.getViewName() == 'List'){
                    var listInstance = window.app.controller();
                    var selectedRecordCount = listInstance.getSelectedRecordCount();
                    if (selectedRecordCount > 100) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_MASS_EDIT_LIMIT')});
                        return;
                    }
                    // var params = listInstance.getListSelectAllParams(true);
                    var checkedParams = JSON.stringify(listInstance.getListSelectAllParams(true));
                    var link = 'index.php?module=QuotingTool&action=PDFHandler&mode=listExport&relmodule='
                        + module + '&template_id=' + templateId + '&checked_params='+checkedParams;
                    document.location.href = link;
                }else{
                    document.location.href = 'index.php?module=QuotingTool&action=PDFHandler&mode=export&relmodule='
                        + module + '&record=' + recordId + '&template_id=' + templateId;

                }
            }
        });

        // Send email
        jQuery(document).on('click', '[data-action="send_email"]', function () {
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                templateId = thisInstance.getSelectedTemplates();
            }

            if (templateId) {
                app.helper.showProgress();
                var params = {
                    'module': app.getModuleName,
                    'view': 'SelectEmailFields',
                    'mode': 'send_email',
                    'relmodule': module,
                    'record': recordId,
                    'template_id': templateId
                };

                app.request.post({data: params}).then(
                    function (err, data) {
                        app.helper.hideProgress();

                        if (err === null) {
                            var callBackFunction = function (data) {
                                var form = jQuery('#SendEmailFormStep1');
                                var params = app.validationEngineOptions;
                                params.onValidationComplete = function (form, valid) {
                                    if (valid) {
                                        app.helper.hideModal();
                                        app.helper.showProgress();
                                        var data = form.serializeFormData();
                                        app.request.post({data: data}).then(
                                            function (err, data) {
                                                app.helper.hideProgress();

                                                if (err === null) {
                                                    app.helper.showSuccessNotification({
                                                        'title': data.message
                                                    });
                                                } else {
                                                    app.helper.showErrorNotification({
                                                        'title': data.message
                                                    });
                                                }
                                            }
                                        );

                                        return valid;
                                    }
                                };
                                form.validationEngine(params);

                                form.submit(function (e) {
                                    e.preventDefault();
                                })
                            };

                            // css {'width': '350px'}
                            app.helper.showModal(data, {
                                'cb': function (data) {
                                    // to do
                                    if (typeof callBackFunction == 'function') {
                                        callBackFunction(data);
                                    }
                                }
                            });
                        } else {
                            console.log(err);
                        }
                    }
                );
            }
        });

        // Preview and send email
        jQuery(document).on('click', '[data-action="preview_and_send_email"]', function (e) {
            e.preventDefault();
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                // get all selected templates
                templateId = thisInstance.getSelectedTemplates();
            }

            if (!templateId) {
                // Invalid template id
                return;
            }
            var iscreatenewrecord = thisFocus.data('iscreatenewrecord');
            var childModule = thisFocus.data('childmodule');

            // #601125 DD & MultiSMTP begin
            var element = jQuery(e.currentTarget);
            var multi_smtp = element.data('multi-smtp');
            // if(multi_smtp == true){
            //     //check multiSMTP server
            //
            //     //
            //     app.helper.hideModal().then(function () {
            //         Vtiger_Detail_Js.triggerSendEmail("index.php?module="+app.getModuleName()+"&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");
            //         jQuery(document).ajaxComplete( function (event, request, settings) {
            //             var data = settings.data;
            //             if(data.indexOf('view=MassActionAjax') != -1 && data.indexOf('mode=showComposeEmailForm') != -1 ){
            //                 $('form#SendEmailFormStep1').find('button[name="saveButton"]').on('click',function(){
            //                     jQuery(document).ajaxComplete( function (event, request, settings) {
            //                         var data = settings.data;
            //                         if(data.indexOf('view=MassActionAjax') != -1 && data.indexOf('module=MultipleSMTP') != -1 && data.indexOf('mode=showTOField') != -1 ){
            //                             var form = $('#massEmailForm');
            //                             var modalTitle = 'Preview & Send Email';
            //                             form.find('div.attachment').css('display','none');
            //                             form.find('input[name="signature"]').closest('div.row').css('display','none');
            //                             form.find('div.modal-footer button[name="savedraft"]').hide();
            //                             form.find('div.modal-header h4').html(modalTitle);
            //                             form.find('div.modal-body div.toEmailField div.input-group').hide();
            //
            //                             var params = {
            //                                 module: 'QuotingTool',
            //                                 view: 'MassActionAjax',
            //                                 mode: 'createPDFAndRecipientLink',
            //                                 record: recordId,
            //                                 template_id: templateId,
            //                                 isCreateNewRecord: iscreatenewrecord,
            //                                 childModule: childModule
            //                             };
            //                             app.request.post({data: params}).then(
            //                                 function (err, data) {
            //                                     if (err === null) {
            //                                         var cancel = '<div class="pull-right cancelLinkContainer"><a href="#" class="cancelLink" type="reset" data-dismiss="modal">Cancel</a></div>';
            //                                         var sendButton = '<button id="sendEmail" name="sendemail" class="btn btn-success" title="Send Email" type="submit"><strong>Send Email</strong></button>';
            //                                         form.find('div.modal-footer').html(cancel+sendButton+'<div class="pull-left custom_proposal_link">'+
            //                                             '<label class="check_attach_file text-left" style="display: block;">'+
            //                                             '<input type="checkbox" name="check_attach_file"><input type="hidden" name="QuotingToolAttachFile" value="'+data.pdf+'">&nbsp;'+
            //                                             '<span>Attach Document</span>'+
            //                                             '</label><a href="'+data.link+'" target="_blank">See the document as your recipient sees it</a></div>');
            //                                     } else {
            //                                         console.log(err);
            //                                     }
            //                                 }
            //                             );
            //
            //
            //                         }
            //                     });
            //                 });
            //             }
            //         });
            //
            //     });
            //
            // }
            // //#601125 end
            // else{


                // Show indicator
                app.helper.showProgress();
                var multiRecordId = [];
                if(app.getViewName()=='List'){
                    var checkBox =  jQuery('input[class="listViewEntriesCheckBox"]:checked');
                    for(var i=0;i < checkBox.length;i++) {
                        multiRecordId.push(checkBox[i].value);
                    }
                    var params = {
                        module: 'QuotingTool',
                        view: 'EmailPreviewTemplate',
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,
                        // }
                    };
                } else{
                    var params = {
                        module: 'QuotingTool',
                        view: 'EmailPreviewTemplate',
                        record: recordId,
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,

                    };
                }
                app.request.post({data: params}).then(
                    function (err, data) {
                        if (err === null) {
                            // css {'width': '796px'}
                            app.helper.hideModal().then(function () {
                                app.helper.showModal(data, {
                                    'cb': function (data) {
                                        thisInstance.registerEventForEmailPopup();
                                    }
                                });
                            });
                        } else {
                            console.log(err);
                        }
                    }
                );
            // }
        });
        // Preview and send email with template
        jQuery(document).on('click', '[data-action="preview_and_send_email_with_template"]', function (e) {
            e.preventDefault();
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                // get all selected templates
                templateId = thisInstance.getSelectedTemplates();
            }

            if (!templateId) {
                // Invalid template id
                return;
            }
            var iscreatenewrecord = thisFocus.data('iscreatenewrecord');
            var childModule = thisFocus.data('childmodule');

            // #601125 DD & MultiSMTP begin
            var element = jQuery(e.currentTarget);
                // Show indicator
                app.helper.showProgress();
                var multiRecordId = [];
                if(app.getViewName()=='List'){
                    var checkBox =  jQuery('input[class="listViewEntriesCheckBox"]:checked');
                    for(var i=0;i < checkBox.length;i++) {
                        multiRecordId.push(checkBox[i].value);
                    }
                    var params = {
                        module: 'QuotingTool',
                        view: 'EmailPreviewTemplateWithTemplate',
                        record: recordId,
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,
                        // }
                    };
                } else{
                    var params = {
                        module: 'QuotingTool',
                        view: 'EmailPreviewTemplateWithTemplate',
                        record: recordId,
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,

                    };
                }
                app.request.post({data: params}).then(
                    function (err, data) {
                        if (err === null) {
                            // css {'width': '796px'}
                            app.helper.hideModal().then(function () {
                                app.helper.showModal(data, {
                                    'cb': function (data) {
                                        thisInstance.registerEventForEmailPopup();
                                    }
                                });
                            });
                        } else {
                            console.log(err);
                        }
                    }
                );
            // }
        });
        jQuery(document).on('click', '[data-action="generate_form_link"]', function (e) {
            e.preventDefault();
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                // get all selected templates
                templateId = thisInstance.getSelectedTemplates();
            }

            if (!templateId) {
                // Invalid template id
                return;
            }
            var iscreatenewrecord = thisFocus.data('iscreatenewrecord');
            var childModule = thisFocus.data('childmodule');

            // #601125 DD & MultiSMTP begin
            var element = jQuery(e.currentTarget);
                // Show indicator
                app.helper.showProgress();
                var multiRecordId = [];
                if(app.getViewName()=='List'){
                    var checkBox =  jQuery('input[class="listViewEntriesCheckBox"]:checked');
                    for(var i=0;i < checkBox.length;i++) {
                        multiRecordId.push(checkBox[i].value);
                    }
                    var params = {
                        module: 'QuotingTool',
                        view: 'EmailPreviewTemplateWithTemplate',
                        record: recordId,
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,
                        // }
                    };
                } else{
                    var params = {
                        module: 'QuotingTool',
                        view: 'GenerateFormLink',
                        record: recordId,
                        template_id: templateId,
                        isCreateNewRecord: iscreatenewrecord,
                        childModule: childModule,
                        multiRecord:multiRecordId,

                    };
                }
                app.request.post({data: params}).then(
                    function (err, data) {
                        if (err === null) {
                            if(data == ''){
                                alert(app.vtranslate("Template not have form link!"));
                                app.helper.hideProgress();
                            }else{
                                window.open(data);
                                app.helper.hideProgress();
                            }
                        } else {
                            console.log(err);
                            app.helper.hideProgress();
                        }
                    }
                );
            // }
        });

        // Download PDF with signature
        jQuery(document).on('click', '[data-action="download_with_signature"]', function () {
            var templateId = thisInstance.getSelectedTemplates();

            if (templateId) {
                document.location.href = 'index.php?module=QuotingTool&action=PDFHandler&mode=download_with_signature&relmodule='
                    + module + '&record=' + recordId + '&template_id=' + templateId;
            }
        });
        
        // Download PDF with signature
        jQuery(document).on('click', '.btnChangeSignature', function () {
            if ($(this).hasClass("btn-primary")){
                return false;
            }
            var currentInstance = $(this);
            var transaction_id = $("[name='transaction_id']").val();
            var sign_to = $(this).data('sign-to');
            app.helper.showProgress();
            var params = {};
            params['module'] = 'QuotingTool';
            params['action'] = 'ActionAjax';
            params['mode'] = 'changeSignTo';
            params['transaction_id'] = transaction_id;
            params['sign_to'] = sign_to;
            app.request.post({data:params}).then(
                function(err,data) {
                    $('.btnChangeSignature.btn-primary').addClass("btn-default");
                    $('.btnChangeSignature.btn-primary').removeClass("btn-primary");
                    currentInstance.removeClass("btn-default");
                    currentInstance.addClass("btn-primary");
                    app.helper.hideProgress();
                },
                function(error) {
                    app.helper.hideProgress();
                }
            );
        });
        jQuery(document).on('click','[data-action="preview_email"]',function (e) {
            e.preventDefault();
            var thisFocus = $(this);
            // Priority: 1. current button; 2. select box
            var templateId = thisFocus.data('template');

            if (!templateId) {
                // get all selected templates
                templateId = thisInstance.getSelectedTemplates();
            }

            if (!templateId) {
                // Invalid template id
                return;
            }
            var iscreatenewrecord = thisFocus.data('iscreatenewrecord');
            var childModule = thisFocus.data('childmodule');
            var params = {
                module: 'QuotingTool',
                view: 'PreviewTemplate',
                record: recordId,
                template_id: templateId,
                isCreateNewRecord: iscreatenewrecord,
                childModule: childModule,
            };
            app.helper.showProgress();
            app.request.post({data: params}).then(
                function (err, data) {
                    if (err === null) {
                        // $('body').append("<a id='remove-now' href='"+data+"' target='_blank'></a>");
                        // $('#remove-now').trigger('click');
                        // $('#remove-now').remove();
                        // aTag.click();
                        window.open(data);
                        app.helper.hideProgress();
                    } else {
                        console.log(err);
                        app.helper.hideProgress();
                    }
                }
            );
        })
    },

    registerEventForEmailPopup: function () {
        var thisInstance = this;
        thisInstance.registerEmailTags();

        var formEmail = jQuery('#quotingtool_emailtemplate');
        thisInstance.registerEventForSelectingRelatedRecord(formEmail);
        // console.log('formEmail =', formEmail);
        var inEmailSubject = formEmail.find('#email_subject');
        var inEmailContent = formEmail.find('#email_content');

        // console.log('email_content =', jQuery('#email_content'));

        var editorEmailContent = CKEDITOR.replace('email_content', {
            fullPage: true,
            toolbar: [
                {name: 'clipboard', items: ['Undo', 'Redo']},
                {name: 'tools', items: ['Source', 'Maximize', 'Preview']},
                {
                    name: 'editing',
                    groups: ['find', 'selection', 'spellchecker'],
                    items: ['Find', 'Replace', 'SelectAll', 'Scayt']
                },
                /*'/',*/
                {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                {name: 'colors', items: ['TextColor', 'BGColor']},
                '/',
                {name: 'insert', items: ['Image', 'Table']},
                {name: 'links', items: ['Link', 'Unlink']},
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                },
                {
                    name: 'paragraph',
                    //groups: ['list', 'indent', 'blocks', 'align', 'bidi'],
                    //items: ['Blockquote', 'CreateDiv', '-', 'BidiLtr', 'BidiRtl']
                    items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                        'JustifyRight', 'JustifyBlock', 'BidiLtr', 'BidiRtl']
                },
                {name: 'about', items: ['About']}
            ]
        });
        formEmail.on('click','button[name="saveButton"]',function(e){
            var params = {
                submitHandler: function(form) {
                    form = jQuery(form);
                    if (formEmail.find('.emailField:checked').length == 0 || formEmail.find('.emailField').length == 0) {
                        app.helper.showErrorNotification({
                            'message': app.vtranslate('Please select at least one email')
                        });
                        return false;
                    }
                    if (formEmail.find('[name="email_subject"]').val() == '') {
                        app.helper.showErrorNotification({
                            'message': app.vtranslate('Email Subject is required')
                        });
                        return false;
                    }
                    inEmailSubject.val(QuotingToolUtils.base64Encode(inEmailSubject.val()));
                    inEmailContent.val(QuotingToolUtils.base64Encode(editorEmailContent.getData()));

                    var data = new FormData(form[0]);
                    if(jQuery('input[name="multi_record"]').val() != '') {
                        data.multi_record = JSON.parse(jQuery('input[name="multi_record"]').val());
                    }else{
                        data.multi_record = '';
                    }
                    var postParams = {
                        data:data,
                        // jQuery will set contentType = multipart/form-data based on data we are sending
                        contentType:false,
                        // we don’t want jQuery trying to transform file data into a huge query string, we want raw data to be sent to server
                        processData:false
                    };
                    app.helper.showProgress();
                    app.request.post(postParams).then(function(err,data){
                        app.helper.hideModal();
                        app.helper.hideProgress();
                        if (err === null) {
                            app.helper.showSuccessNotification({
                                'message': data.message
                            });
                            app.helper.hideModal();
                        } else {
                            app.helper.showErrorNotification({
                                'message': data.message
                            });
                        }
                    });
                }
            };
            formEmail.vtValidate(params);
        });
        // When update email content
        //formEmail.submit(function (event) {
        //    event.preventDefault();
        //    inEmailSubject.val(QuotingToolUtils.base64Encode(inEmailSubject.val()));
        //    inEmailContent.val(QuotingToolUtils.base64Encode(editorEmailContent.getData()));
        //    if (formEmail.find('input[type=checkbox]:checked').length == 0) {
        //        app.helper.showErrorNotification({
        //            'title': app.vtranslate('Please select atl east one email')
        //        });
        //
        //        return false;
        //    }
        //
        //    // Show indicator
        //    app.helper.showProgress();
        //
        //
        //    var data = formEmail.serializeFormData();
        //
        //    app.request.post({data: data}).then(
        //        function (err, data) {
        //            if (err === null) {
        //                app.helper.showSuccessNotification({
        //                    'message': data.message
        //                });
        //            } else {
        //                app.helper.showErrorNotification({
        //                    'message': data.message
        //                });
        //            }
        //        }
        //    ).done(function () {
        //        app.helper.hideProgress();
        //        // Hide modal
        //        app.helper.hideModal();
        //    });
        //});

        setTimeout(function () {
            // Hide indicator
            app.helper.hideProgress();
        }, 300);
    },

    registerWidgetButtons: function () {
        var thisInstance = this;
        var module = app.getModuleName();
        var view = app.view();
        var record = thisInstance.getRecordId();
    
        // Add Quoting Tool button
        if (view == 'Detail' && record != undefined) {
            //app.helper.showProgress();
            var params = {
                'action': 'ActionAjax',
                'mode': 'getTemplate',
                'module': thisInstance.MODULE,
                'record': record,
                'rel_module': module
            };
            app.request.post({data: params}).then(
                function (err, data) {
                    app.helper.hideProgress();
                    if (err === null) {
                        var templates = data;
                        // fix issue: button push other button down
                        var currentDiv = $("#appnav").parent();
                        var currentClass = currentDiv.attr("class");
                        var newClass = currentClass.replace('col-lg-5 col-md-5', 'col-lg-7 col-md-7');
                        currentDiv.attr('class', newClass);
                        var prevDiv = currentDiv.prev();
                        currentClass = prevDiv.attr("class");
                        newClass = currentClass.replace('col-lg-7 col-md-7', 'col-lg-5 col-md-5');
                        prevDiv.attr('class', newClass);
    
                        var navContainer = jQuery('#appnav ul.nav');
                        var button = jQuery('<li><button class="btn btn-primary module-buttons btn-quoting_tool" style="background-color: #1560bd; color: #ffffff">' +
                            '<div class="fa" aria-hidden="true"></div>&nbsp;&nbsp;' + app.vtranslate('Document Designer') +
                            '</button></li>');
                        if (templates.length > 0) {
                            var firstButton = navContainer.find('li:first');
                            firstButton.before(button);
                            button.on('click', function () {
                                var params = {
                                    'action': 'ActionAjax',
                                    'mode': 'checkMPDF',
                                    'module': thisInstance.MODULE
                                };
                                app.request.post({data: params}).then(
                                    function (err, data) {
                                        if (err === null) {
                                            if(data == 'success'){
                                                thisInstance.showWidgetModal(templates);
                                            }else{
                                                alert('MPDF Library is missing or needs to be upgraded. Please go to Document Designer template list/configuration to download MPDF library.');
                                                window.open(data, '_blank');
                                            }
                                        }
                                    }
                                );
                            });
                        }
                    } else {
                        console.log(err);
                    }
                }
            );
        }
    },
    IconHelpText: function () {
        var module = app.getModuleName();
        var html = '<div class="modal modal2 fade" style="display: none;" aria-hidden="false" id="modal2" data-backdrop="static" data-keyboard="false">'
            + '<div class="modal-dialog modal-lg" style="width: 500px">'
            + '<div class="modal-content">'
            + '<div class="modal-header">'
            + '<div class="clearfix">'
            + '<div class="pull-right">'
            + '<button type="button" class="close" aria-label="Close" id="btnModal2">'
            + '<span aria-hidden="true" class="fa fa-close"></span>'
            + '</button>'
            + '</div>'

            + '</div>'
            + '</div>'
            + '<div class="modal-body" style="overflow-y: auto;">';

        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';
        // Append Modal2 to Body of Website
        jQuery("body").append(html);

        //Get Data From Input
        if(module=='QuotingTool' && app.getViewName() == 'Edit') {
            var dataHelptext = jQuery('input[name="icon_helptext"]').val();
            if (dataHelptext != '' && dataHelptext != undefined) {
                dataHelptext = JSON.parse(dataHelptext);
            }
        }
        //Click Modal Icon_HelpText
        jQuery(document).on('click','.icon-helptext',function(e) {
            var id_helptext = jQuery(this).attr("id");

            for(var i=0;i<dataHelptext.length;i++){
                if(dataHelptext[i].element==id_helptext){
                    templates = dataHelptext[i].helptext;
                    break;
                }
                else if(id_helptext=="create_new_record"){
                    templates="You can send forms to external people as well as existing customers. Filled out forms will be created as new records. Please refer to the user guide for instructions";
                    break;
                }
                else{
                    templates = "No data";
                }
            }

            jQuery(".modal2 .modal-body").append('<h5 id="templates">'+templates+'</h5>');
            jQuery("#modal2").modal('show');

        });
    },

    /**
     * @param templates
     */
    showWidgetModal: function (templates) {
        var formLink = '';

        if (templates && Array.isArray(templates['data'])) {
            var isShowLink = templates['showLink'];
            if(isShowLink == true) {
                formLink = '<th class="actions" style="width: 110px">' + app.vtranslate('Generate web form link  ') + '</th>';
            }
        }
        var html = '<div class="modal myModal fade in" style="display: block;" aria-hidden="false">'
            + '<div class="modal-backdrop fade in"></div>'
            + '<div class="modal-dialog modal-lg">'
            + '<div class="modal-content" style="width: 90%;margin:auto">'
            + '<form class="form-horizontal" action="index.php">'
            + '<div class="modal-header">'
            + '<div class="clearfix">'
            + '<div class="pull-right">'
            + '<button type="button" class="close" aria-label="Close" data-dismiss="modal">'
            + '<span aria-hidden="true" class="fa fa-close"></span>'
            + '</button>'
            + '</div>'
            + '<h4 class="pull-left">' + app.vtranslate('Document Designer (Email/PDF)') + '</h4>'
            + '</div>'
            + '</div>'
            + '<div class="modal-body" style="overflow-y: unset;">'
            + '<table id="tableQuotingToolWidget">'
            + '<thead>'
            + '<th>' + app.vtranslate('Template Name') + '</th>'
            + formLink
            + '<th class="actions" style="width: 190px">' + app.vtranslate('Email PDF/Online Document  ') + '<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Email PDF as attachment and/or Online document (link). Use this option for electronic signature/interactive online document/form (clickable link)."></span></th>'
            + '<th class="actions" style="width: 150px">' + app.vtranslate('Export to<br> PDF  ') + '<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Direct download (export to pdf)."></span></th>'
            + '<th class="actions" style="width: 200px">' + app.vtranslate('Send as Email<br> Template  ') + '<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Merge document content/template directly into email body. This is to be used as email template." ></span></th>'
            + '</thead>'
            + '<tbody>';
        var template = null;
        if (templates && Array.isArray(templates['data'])) {
            var currentModule  = app.getModuleName();
            var isCreateNewRecord = '';
            templates = templates['data'];
            for (var i = 0; i < templates.length; i++) {
                template = templates[i];
                var moduleTemplate = template.modulename;
                var btnGenerate = '';
                if(template.createnewrecords == 1 && currentModule != moduleTemplate) {
                     isCreateNewRecord = 1;
                }else{
                    isCreateNewRecord = 0;
                }
                if(template.createnewrecords == 1) {
                    btnGenerate = '<td style="text-align: left"><button class="btn btn-default" data-action="generate_form_link" data-template="' + template.id + '" data-childmodule = "' + moduleTemplate + '" data-iscreatenewrecord = "' + isCreateNewRecord + '"><a href="javascript:;">' +
                        'Generate</a></button></td>';
                }else if(isShowLink == true) {
                    btnGenerate = '<td style="text-align: left"></td>'
                }
                html += '<tr>' +
                    '<td>' + template.filename + '</td>' +btnGenerate+
                    '<td class="first-button" style="text-align: left"><button class="btn btn-default" data-action="preview_and_send_email" data-template="' + template.id + '" data-childmodule = "' + moduleTemplate + '" data-iscreatenewrecord = "' + isCreateNewRecord + '"><a href="javascript:;">' +
                '<img style="width: 15px;height: 15px;margin-top: -3px;" src="layouts/v7/modules/QuotingTool/resources/img/icons/email-icon.png" />   Send via Email</a></button>' +
                    '<a href="javascript:;" class="hide" data-action="preview_email" data-template="' + template.id + '" data-childmodule = "' + moduleTemplate + '" data-iscreatenewrecord = "' + isCreateNewRecord + '">' +
                    '<img style="width: 15px;height: 15px;margin-top: -1px;margin-left: 15px" src="layouts/v7/modules/QuotingTool/resources/img/icons/preview.png" /></a></td>' +
                    '<td style="text-align: center"><button class="btn btn-default" data-action="export" data-template="' + template.id + '"><a href="javascript:;">' +
                    '<img style="width: 14px;height: 14px;margin-top: -3px;" src="layouts/v7/modules/QuotingTool/resources/img/icons/download-icon.png" />   Export to PDF</a></button></td>' +
                    '<td style="text-align: center"><button class="btn btn-default" data-action="preview_and_send_email_with_template" data-template="' + template.id + '" data-childmodule = "' + moduleTemplate + '" data-iscreatenewrecord = "' + isCreateNewRecord + '"><a href="javascript:;">' +
                    '<img style="width: 15px;height: 15px;margin-top: -3px;" src="layouts/v7/modules/QuotingTool/resources/img/icons/email-open-icon.png" />   Use as Email Template</a></button></td>' +
                    '</tr>';
            }
        } else {
            html += templates;
        }

        html += '</tbody>'
            + '</table>'
            + '</div>'
            + '</form>'
            + '</div>'
            + '</div>'
            + '</div>';

        // css {'width': '600px'}
        app.helper.showModal(html, {
            'cb': function (data) {
                //// to do
                // if(jQuery('#hierarchyScroll').height() > 300){
                //     app.helper.showVerticalScroll(jQuery('#hierarchyScroll'), {
                //         setHeight: '680px',
                //         autoHideScrollbar: false,
                //     });
                // }
            }
        });
        jQuery('.myModal').find('.fa-question-circle').tooltip();
        jQuery('.myModal').find('button').hover(function () {
            $(this).css({'color':'#15c'})
        },function () {
            $(this).css({'color':''})
        });
        jQuery('.first-button').closest('td').hover(function (e) {
            $(this).find('a:last').removeClass('hide');
        },function () {
            $(this).find('a:last').addClass('hide');
        })

    },

    registerEmailTags: function () {
        var selectTags = jQuery('.select2-tags');

        selectTags.each(function () {
            var focus = jQuery(this);
            var tags = focus.data('tags');
            if (typeof tags === 'undefined' || !tags) {
                tags = [];
            }
            var select2params = {tags: tags/*, tokenSeparators: [',']*/};
            // app.showSelect2ElementView(focus, select2params);
            vtUtils.showSelect2ElementView(focus, select2params);
        });
    },

    //test
    showPopup : function(params) {
        var aDeferred = jQuery.Deferred();
        var popupInstance = Vtiger_Popup_Js.getInstance();
        popupInstance.show(params, function(data){
            aDeferred.resolve(data);
        });
        return aDeferred.promise();
    },
    registerEventForSelectingRelatedRecord : function(container) {
        var thisInstance = this;
        var popUpContainer = container;
        app.event.off("post.LinkRecordList.click");
        app.event.on("post.LinkRecordList.click", function(event, data) {
            if(CKEDITOR.instances["email_content"] == undefined){
                app.helper.showErrorNotification({message: "Please select position in email content to add text"});
                return;
            }
            if(app.getModuleName() != 'Quotes'){
                var templateId = popUpContainer.find('select[name="quotes-template"]').val();
            }else{
                var templateId = popUpContainer.find('[name="template_id"]').val();
            }
            var iscreatenewrecord = popUpContainer.find('select[name="quotes-template"]').find('option:selected').attr('createnewrecords');
            var responseData = JSON.parse(data);
            var idList = new Array();
            for (var id in responseData) {
                idList.push(id);
            }
            if(idList.length > 0){
                app.helper.showProgress();
                var params = {
                    module: 'QuotingTool',
                    'action': 'ActionAjax',
                    'mode': 'getLinkHtml',
                    record: idList,
                    template_id: templateId,
                    isCreateNewRecord: iscreatenewrecord,
                    getNonePreviewLink: true,
                    childModule: 'Quotes',
                };
                app.request.post({data: params}).then(
                    function (err, data) {
                        if (err === null) {
                            CKEDITOR.instances["email_content"].insertHtml(data,'text');
                            $('.myModal:last').modal('hide');
                            app.helper.hideProgress();
                        } else {
                            console.log(err);
                        }
                    }
                );
            }
        });
        jQuery(document).find(popUpContainer).find('a.selectRelation').off('click');
        jQuery(document).find(popUpContainer).find('a.selectRelation').click(function (e) {
            e.preventDefault();
            if(popUpContainer.find('select[name="quotes-template"]').val() == '' && app.getModuleName() != 'Quotes'){
                app.helper.showErrorNotification({message: "Please select template id"});
                return;
            }
            thisInstance.showSelectRelationPopup();
        })
        // popUpContainer.on('click', 'button.selectRelation', function(e){
        //     e.preventDefault();
        //     if(popUpContainer.find('select[name="quotes-template"]').val() == ''){
        //         app.helper.showErrorNotification({message: "Please select template id"});
        //         return;
        //     }
        //     thisInstance.showSelectRelationPopup();
        // });
    },
    showSelectRelationPopup : function(){
        var popupParams = this.getPopupParams();
        var popupjs = new Vtiger_Popup_Js();
        popupjs.showPopup(popupParams,"post.LinkRecordList.click");
    },
    getPopupParams : function(){
        var thisInstance = this;
        var moduleName = app.getModuleName();
        var recordId = thisInstance.getRecordId();
        if(moduleName == 'Quotes'){
            recordId = '';
        }
        var parameters = {};
        var parameters = {
            'module': 'QuotingTool',
            'child_module' : 'Quotes',
            'src_module' : 'Potentials',
            'src_record' : thisInstance.getRecordId(),
            'src_field' : "potential_id",
            'related_parent_module' : "Potentials",
            'related_parent_id' : recordId,
            'multi_select' : true,
            'view' : 'Popup',
            'relationId' : jQuery('div.related-tabs').find('li[data-module="Quotes"]').data('relationId')
        };
        return parameters;
    },
    //test
    registerEvents: function () {
        var thisInstance = this;
        thisInstance.registerWidgetActions();
        // thisInstance.registerWidgetButtons();
        thisInstance.IconHelpText();

    }
});

jQuery(document).ready(function () {

    var module = app.getModuleName();

    // Add css to screen dose not move when open dialog
    $("head").append("<style>body.modal-open{padding-right: 0px!important;}</style>");
    // // Fix auto add resizeable to textarea on IE
    // if (jQuery.isFunction(jQuery.fn.resizable)) {
    //     jQuery('#quoting_tool-body').find('textarea.hide')
    //         .resizable('destroy')
    //         .removeAttr('style');
    // }

    var instance = new QuotingToolJS();
    instance.detailViewButtoncontainer = jQuery('.detailViewButtoncontainer');
    instance.registerEvents();

    // Icon_HelpText
    if(module=='QuotingTool' && app.getViewName() == 'Edit') {


        var dataHelptext = jQuery('input[name="icon_helptext"]').val();
        if(dataHelptext != '' && dataHelptext != undefined){
            dataHelptext = JSON.parse(dataHelptext);
            if (dataHelptext.length > 0) {

                setInterval(function () {
                    jQuery("span.icon-helptext").removeClass("hide");
                    //clearInterval();
                }, 1000);
            }
        }
        jQuery("#btnModal2").on('click', function () {
            var element = document.getElementById("templates");
            element.parentNode.removeChild(element);
            jQuery(".modal2.in").modal('hide');
        });
        // End of Icon_HelpText
    }

});
jQuery(document).ajaxComplete( function (event, request, settings) {
    var url = settings.data;
    if (url && url.constructor.name=='String' && url.indexOf('module=QuotingToo') > -1 && url.indexOf('view=EmailPreviewTemplate') > -1){
        setTimeout(function () {
            var formEmail = jQuery(document).find('#quotingtool_emailtemplate');
            var thisInstance = new QuotingToolJS();

            var emailInstance = new Emails_MassEdit_Js();
            formEmail.find('#browseCrm').on('click',function(e){
                var url = jQuery(e.currentTarget).data('url');
                var postParams = app.convertUrlToDataParams("index.php?"+url);

                app.request.post({"data":postParams}).then(function(err,data){
                    jQuery('.popupModal').remove();
                    var ele = jQuery('<div class="modal popupModal"></div>');
                    ele.append(data);
                    jQuery('body').append(ele);
                    emailInstance.showpopupModal();
                    app.event.trigger("post.Popup.Load",{"eventToTrigger":"post.DocumentsList.click"});
                });
            });
            var index = 0;
            formEmail.find("#multiFile").MultiFile({
                list: '#attachments',
                'afterFileSelect' : function(element, value, master_element){
                    if(index != 0) {
                        return;
                    }
                    var masterElement = master_element;
                    var newElement = jQuery(masterElement.current);
                    newElement.addClass('removeNoFileChosen');
                    emailInstance.fileAfterSelectHandler(element, value, master_element);
                },
                'afterFileRemove' : function(element, value, master_element){
                    if (jQuery('#attachments').is(':empty')){
                        jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
                    }
                    emailInstance.removeAttachmentFileSizeByElement(jQuery(element));
                }
            });
            app.event.off("post.DocumentsList.click");
            app.event.on("post.DocumentsList.click",function(event, data){
                var responseData = JSON.parse(data);
                if(index != 0) {
                    return;
                }
                jQuery('.popupModal').modal('hide');
                for(var id in responseData){
                    selectedDocumentId = id;
                    var selectedFileName = responseData[id].info['filename'];
                    var selectedFileSize = responseData[id].info['filesize'];
                    var response = emailInstance.writeDocumentIds(selectedDocumentId);
                    if(response){
                        var attachmentElement = emailInstance.getDocumentAttachmentElement(selectedFileName,id,selectedFileSize);
                        //TODO handle the validation if the size exceeds 5mb before appending.
                        jQuery(attachmentElement).appendTo(jQuery('#attachments'));
                        jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
                        emailInstance.setDocumentsFileSize(selectedFileSize);
                    }
                }
            });
            formEmail.on('click','#ccLink',function(e){
                jQuery('.ccContainer').removeClass('hide');
                jQuery(e.currentTarget).hide();
            });
            formEmail.on('click','#bccLink', function(e){
                jQuery('.bccContainer').removeClass('hide');
                jQuery(e.currentTarget).hide();
            });
        },500);

    }
});
