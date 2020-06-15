/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Emails_MassEdit_Js("EMAILMaker_MassEdit_Js",{

	init: function () {
		this.preloadAllData = new Array();
        //this.preloadAllData["to"] = new Array();
        //this.preloadAllData["cc"] = new Array();
        //this.preloadAllData["bcc"] = new Array();
	},

	ckEditorInstance : false,
	massEmailForm : false,
	saved : "SAVED",
	sent : "SENT",
	attachmentsFileSize : 0,
	documentsFileSize : 0,

    getPreloadAllData : function(type) {
        var sid = this.getEmailsSourceId();
		if (type == "") type = "to";

        if (typeof this.preloadAllData[sid] == 'undefined') {
            return null;
        }

        return this.preloadAllData[sid][type];
    },

    setPreloadAllData : function(type,dataInfo){
        var sid = this.getEmailsSourceId();
        if (type == "") type = "to";

        if (typeof this.preloadAllData[sid] == 'undefined') {
            this.preloadAllData[sid] = new Array();
        }

        this.preloadAllData[sid][type] = dataInfo;
        return this;
    },
	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */

	registerAutoCompleteFields : function(container,type) {
		var thisInstance = this;
		var lastResults = [];

        if (type == "") {
            var etype = "to";
        } else {
            var etype = type;
        }

        container.find('#email'+type+'Field').select2({
			minimumInputLength: 3,
			closeOnSelect : false,

			tags : [],
			tokenSeparators: [","],

			ajax : {
				'url' : 'index.php?module=EMAILMaker&action=IndexAjax&mode=SearchEmails',
				'dataType' : 'json',
				'data' : function(term,page){
					 var data = {};
					 data['searchValue'] = term;
					 return data;
				},
				'results' : function(data){
					var finalResult = [];
					var results = data.result;
					var resultData = new Array();
					for(var moduleName in results) {
						var moduleResult = [];
						moduleResult.text = moduleName;

						var children = new Array();
						for(var recordId in data.result[moduleName]) {
							var emailInfo = data.result[moduleName][recordId];

							for (var i in emailInfo) {
								var childrenInfo = [];
								childrenInfo.recordId = recordId;
								childrenInfo.id = emailInfo[i].value;
								childrenInfo.text = emailInfo[i].label;
                                childrenInfo.module = emailInfo[i].module;
								children.push(childrenInfo);
							}
						}
						moduleResult.children = children;
						resultData.push(moduleResult);
					}
					finalResult.results = resultData;
					lastResults = resultData;
					return finalResult;
				},
				transport : function(params) {
					return jQuery.ajax(params);
				}
			},
			createSearchChoice : function(term) {
				//checking for results if there is any if not creating as value
				if(lastResults.length == 0) {
					return { id: term, text: term };
				}
			},
			escapeMarkup: function(m) {
				// Do not escape HTML in the select options text
				return m;
			},

		}).on("change", function (selectedData) {
			var addedElement = selectedData.added;

			if (typeof addedElement != 'undefined') {

				var data = {
                    'eid' : addedElement.recordId + "|" + addedElement.id + "|" + addedElement.module,
					'id' : addedElement.recordId,
					'name' : addedElement.text,
					'emailid' : addedElement.id
				}
                if (typeof addedElement.recordId == 'undefined') {
                    data.eid =  "email|" + addedElement.id + "|";
                }

                if (type == "cc") {
                    thisInstance.addCCEmailAddressData(data);
                } else if (type == "bcc") {
                    thisInstance.addBCCEmailAddressData(data);
                } else {
                    thisInstance.addToEmailAddressData(data);
                    if (typeof addedElement.recordId != 'undefined') {
                        thisInstance.appendToSelectedIds(addedElement.recordId);
                    }
                }
                thisInstance.addEmails(etype,data);

				var preloadData = thisInstance.getPreloadAllData(type);
				var emailInfo = {
					'id' : addedElement.id,
                    'eid' : data.eid
				}
				if (typeof addedElement.recordId != 'undefined') {
					emailInfo['text'] = addedElement.text;
                    emailInfo['module'] = addedElement.module;
					emailInfo['recordId'] = addedElement.recordId;
				} else {
					emailInfo['text'] = addedElement.id;
				}
				preloadData.push(emailInfo);
				thisInstance.setPreloadAllData(type,preloadData);
			}

			var removedElement = selectedData.removed;

			if (typeof removedElement != 'undefined') {

				var data = {
                    'eid' : removedElement.recordId + "|" + removedElement.id + "|" + removedElement.module,
					'id' : removedElement.recordId,
					'name' : removedElement.text,
					'emailid' : removedElement.id
				}

                if (typeof removedElement.recordId == 'undefined') {
                    data.eid =  "email|" + removedElement.id + "|";
                }

				thisInstance.removeFromEmails(etype,data);
				if (typeof removedElement.recordId != 'undefined') {
					thisInstance.removeFromSelectedIds(etype,removedElement.recordId);
				}
                thisInstance.removeFromEmailAddressData(etype,data);
				var preloadData = thisInstance.getPreloadAllData(type);

				var updatedPreloadData = [];
				for(var i in preloadData) {
					var preloadDataInfo = preloadData[i];
					var skip = false;
					if (data.eid == preloadDataInfo.eid) {
						skip = true;
					}
					if (skip == false) {
						updatedPreloadData.push(preloadDataInfo);
					}
				}

				thisInstance.setPreloadAllData(type,updatedPreloadData);
			}
		});

		container.find('#email'+type+'Field').select2("container").find("ul.select2-choices").sortable({
			containment: 'parent',
			start: function(){
				container.find('#email'+type+'Field').select2("onSortStart");
			},
			update: function(){
				container.find('#email'+type+'Field').select2("onSortEnd");
			}
		});

        thisInstance.actualizeSelect2El(container,etype);
	},

	removeFromEmailAddressData : function(etype,mailInfo) {
        var thisInstance = this;
        var sid = thisInstance.getEmailsSourceId();
		var mailInfoElement = thisInstance.getMassEmailForm().find('[name="' + sid + etype + 'emailinfo"]');
		var previousValue = JSON.parse(mailInfoElement.val());

		var elementSize = previousValue[mailInfo.eid].length;
		var emailAddress = mailInfo.emailid;
		var selectedId = mailInfo.eid;
		//If element length is not more than two delete existing record.
		if(elementSize < 2){
			delete previousValue[selectedId];
		} else {
			// Update toemailinfo hidden element value
			var newValue;
			var reserveValue = previousValue[selectedId];
			delete previousValue[selectedId];
			//Remove value from an array and return the resultant array
			newValue = jQuery.grep(reserveValue, function(value) {
				return value != emailAddress;
			});
			previousValue[selectedId] = newValue;
			//update toemailnameslist hidden element value
		}
		mailInfoElement.val(JSON.stringify(previousValue));
	},

	removeFromSelectedIds : function(etype,selectedId) {
	    /*
		var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
		var previousValue = JSON.parse(selectedIdElement.val());
		var mailInfoElement = this.getMassEmailForm().find('[name="'+etype+'emailinfo"]');
		var mailAddress = JSON.parse(mailInfoElement.val());
		var elements = mailAddress[selectedId];
		var noOfEmailAddress = elements.length; 

		//Don't remove id from selected_ids if element is having more than two email id's
		if(noOfEmailAddress < 2){
			var updatedValue = [];
			for (var i in previousValue) {
				var id = previousValue[i];
				var skip = false;
				if (id == selectedId) {
					skip = true;
				}
				if (skip == false) {
					updatedValue.push(id);
				}
			}
			selectedIdElement.val(JSON.stringify(updatedValue));
		}*/
	},

	removeFromEmails : function(etype,mailInfo){
		var Emails = this.getMassEmailForm().find('[name="'+etype+'"]');
		var previousValue = JSON.parse(Emails.val());

		var updatedValue = {};
		for (var i in previousValue) {
			var email = previousValue[i];

			if (i != mailInfo.eid) {
                updatedValue[i] = email;
			}
		}
        Emails.val(JSON.stringify(updatedValue));
	},

    addEmails : function(type,mailInfo){
        var Emails = this.getMassEmailForm().find('[name="'+type+'"]');
        var value = JSON.parse(Emails.val());
        var email = mailInfo["emailid"];
        var crmid = mailInfo["id"];
        var eid = mailInfo["eid"];

        if(value == ""){
            var value = {};
        }
		value[eid] = mailInfo["name"];
        Emails.val(JSON.stringify(value));
    },

	addToEmails : function(mailInfo){
        this.addEmails('to',mailInfo);
	},

	addToEmailAddressData : function(mailInfo) {
        var sid = this.getEmailsSourceId();
		var mailInfoElement = this.getMassEmailForm().find('[name="' + sid + 'toemailinfo"]');
		var existingToMailInfo = JSON.parse(mailInfoElement.val());
		 if(typeof existingToMailInfo.length != 'undefined') {
			existingToMailInfo = {};
		} 
		//If same record having two different email id's then it should be appended to
		//existing email id

		if(existingToMailInfo.hasOwnProperty(mailInfo.eid) === true){
			var existingValues = existingToMailInfo[mailInfo.eid];
			var newValue = new Array(mailInfo.name);
			existingToMailInfo[mailInfo.eid] = jQuery.merge(existingValues,newValue);
		} else {
			existingToMailInfo[mailInfo.eid] = new Array(mailInfo.name);
		}
		mailInfoElement.val(JSON.stringify(existingToMailInfo));
	},

	appendToSelectedIds : function(selectedId) {
		/*
		var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
		var previousValue = '';
		if(JSON.parse(selectedIdElement.val()) != '') {
			previousValue = JSON.parse(selectedIdElement.val());
			//If value doesn't exist then insert into an array
			if(jQuery.inArray(selectedId,previousValue) === -1){
				previousValue.push(selectedId);
			}
		} else {
			previousValue = new Array(selectedId);
		}
		selectedIdElement.val(JSON.stringify(previousValue));
*/
	},

	checkHiddenStatusofCcandBcc : function(){
		var ccLink = jQuery('#ccLink');
		var bccLink = jQuery('#bccLink');
		if(ccLink.is(':hidden') && bccLink.is(':hidden')){
			ccLink.closest('div.row').addClass('hide');
		}
	},

	 registerEventsForToField: function() {
		 var thisInstance = this;
		this.getMassEmailForm().on('click','.selectEmail',function(e){
			var moduleSelected = jQuery('.emailModulesList').select2('val');
			var parentElem = jQuery(e.target).closest('.toEmailField');
			var sourceModule = jQuery('[name=module]').val();
			var params = {
				'module' : moduleSelected,
				'src_module' : 'Emails',
				'view': 'EmailsRelatedModulePopup'
			}
			var popupInstance =Vtiger_Popup_Js.getInstance();
			popupInstance.showPopup(params, function(data){

					var responseData = JSON.parse(data);

					for(var id in responseData){
						var data = {
                            'eid' : id + "|" + responseData[id].email  + "|" + moduleSelected,
							'name' : responseData[id].name,
							'id' : id,
                            'module' : moduleSelected,
							'emailid' : responseData[id].email
						}
						thisInstance.setReferenceFieldValue(parentElem, data);
						thisInstance.addToEmailAddressData(data);
						thisInstance.appendToSelectedIds(id);
						thisInstance.addToEmails(data);
					}
				},'relatedEmailModules');
		});

		this.getMassEmailForm().on('click','[name="clearToEmailField"]',function(e){
			var element = jQuery(e.currentTarget);
			element.closest('div.toEmailField').find('.sourceField').val('');
            var sid = thisInstance.getEmailsSourceId();
			thisInstance.getMassEmailForm().find('[name="' + sid + 'toemailinfo"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="selected_ids"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="to"]').val(JSON.stringify(new Array()));

			var preloadData = [];
			thisInstance.setPreloadAllData('',preloadData);
			thisInstance.getMassEmailForm().find('#emailField').select2('data', preloadData);
		});

	 },

    setReferenceFieldValue : function(container,object){
        var thisInstance = this;
        var preloadData = thisInstance.getPreloadAllData();

        if (typeof preloadData == 'undefined' || preloadData == null) {
            var preloadData = [];
        }
        var emailInfo = {
            'eid' : object.id + "|" + object.emailid + "|" + object.module,
            'recordId' : object.id,
            'id' : object.emailid,
            'module' : object.module,
            'text' : object.name+' <b>('+object.emailid+')</b>'
        }
        preloadData.push(emailInfo);
        thisInstance.setPreloadAllData('',preloadData);
        container.find('#emailField').select2('data', preloadData);

        var toEmailField = container.find('.sourceField');
        var toEmailFieldExistingValue = toEmailField.val();
        var toEmailFieldNewValue;
        if(toEmailFieldExistingValue != ""){
            toEmailFieldNewValue = toEmailFieldExistingValue+","+object.emailid;
        } else {
            toEmailFieldNewValue = object.emailid;
        }
        toEmailField.val(toEmailFieldNewValue);
    },
    showPDFPreviewModal: function (templateid, pdflanguage) {
        var self = this;
        var view = app.view();
        if (view == 'Detail') {
            var recordId = app.getRecordId();
        } else {
            var recordId = self.getEmailsSourceId();
        };
        forview_val = 'Detail';

		if (recordId) {
			var params = {
					module: 'PDFMaker',
					source_module: app.getModuleName(),
					formodule: app.getModuleName(),
					forview: forview_val,
					pdftemplateid: templateid,
					language: pdflanguage,
					view: 'IndexAjax',
					mode: 'getPreview',
					hidebuttons: 'true',
					record : recordId
				};

				var popupInstance =Vtiger_Popup_Js.getInstance();
				popupInstance.showPopup(params,'', function(data){
					data.find('.btn-success').hide();
				},'previewPDFMaker');
        }

/*
        app.request.get({data: params}).then(function(err, data) {

            app.helper.showModal(data, {
                'cb' : function(modalContainer) {
                    //modalContainer.find('#use_common_template').select2();
                    //self.registerPDFPreviewActionsButtons(modalContainer,templateids,pdflanguage);
                    self.setMaxModalHeight(modalContainer,'iframe');
                }
            });

            //app.helper.hideProgress();
        });*/
    },
    registerPDFMakerEvents: function (modalContainer){
        var thisInstance = this;
        pdflanguageElement = modalContainer.find('[name=pdflanguage]');

        if (pdflanguageElement.length > 0) {

            var pdflanguage = pdflanguageElement.val();

            modalContainer.find('.generatePreviewPDF').on('click',function(e){
                var element = jQuery(e.currentTarget);
                var templateid = element.data('templateid');
                thisInstance.showPDFPreviewModal(templateid,pdflanguage);
            });
        }
    },
    actualizeSelect2El : function(container,etype){
        var thisInstance = this;
        var type = etype;
        if (etype == "to") {
            type = "";
        }
        var sid = thisInstance.getEmailsSourceId();

        preloadData = thisInstance.getPreloadAllData(type);

        if (typeof preloadData == 'undefined' || preloadData == null) {

			var EmailNamesList = JSON.parse(container.find('[name="' + sid + etype + 'MailNamesList"]').val());
			var EmailInfo = JSON.parse(container.find('[name="' + sid + etype + 'emailinfo"]').val());
			var allEmails = container.find('[name="' + etype + 'Email"]').val();

			//var emailFieldValues = Array();
	/*
			if (allEmails.length > 0) {
				emailFieldValues = allEmails.split(',');
			}*/
			preloadData = [];
			//var preloadData = thisInstance.getPreloadAllData(type);
			if (typeof EmailInfo != 'undefined') {
				for(var key in EmailInfo) {

					if (EmailNamesList.hasOwnProperty(key)) {
						for (var i in EmailNamesList[key]) {
							var emailInfo = [];
							var emodule = EmailNamesList[key][i].module;
							var emailId = EmailNamesList[key][i].value;
							var recordId = EmailNamesList[key][i].recordid;
							var emailInfo = {
								'eid' : recordId + "|" + emailId + "|" + emodule,
								'module' : emodule,
								'recordId' : recordId,
								'id' : emailId,
								'text' : EmailNamesList[key][i].label+' <b>('+emailId+')</b>'
							}
							preloadData.push(emailInfo);
							/*
							if (jQuery.inArray(emailId, emailFieldValues) != -1) {
								var index = emailFieldValues.indexOf(emailId);
								if (index !== -1) {
									emailFieldValues.splice(index, 1);
								}
							}*/
						}
					} else {
						var emailId = EmailInfo[key];
						var emailInfo = {
							'eid' : key,
							'id' : key,
							'text' : emailId
						}
						preloadData.push(emailInfo);
					}
				}
			}
	/*
			if (typeof emailFieldValues != 'undefined') {
				for(var i in emailFieldValues) {
					var emailId = emailFieldValues[i];
					var emailInfo = {
						'id' : emailId,
						'text' : emailId
					}
					preloadData.push(emailInfo);
				}
			}*/

            thisInstance.setPreloadAllData(type,preloadData);

        }
        container.find('#email'+type+'Field').select2('data', preloadData);
    },
    registerEmailSourcesList : function(container){
        var thisInstance = this;
        container.find('.emailSourcesList').on('change',function(e){
            var new_sourceid = jQuery(e.currentTarget).val();
            var composeEmailForm = thisInstance.getMassEmailForm();
            composeEmailForm.find('[name="selected_sourceid"]').val(new_sourceid);

            thisInstance.actualizeSelect2El(composeEmailForm,'to');
            thisInstance.actualizeSelect2El(composeEmailForm,'cc');
            thisInstance.actualizeSelect2El(composeEmailForm,'bcc');

            var ccLink = container.find('#ccLink');
            var ccContainer = container.find('.ccContainer');
            var bccLink = container.find('#bccLink');
            var bccContainer = container.find('.bccContainer');
            var emailCCFieldData = thisInstance.getPreloadAllData('cc');

            var cchide = false;
			if (typeof emailCCFieldData != "undefined") {
            	if (emailCCFieldData.length > 0) {
					cchide = true;
				}
            }

			if (cchide) {
				ccContainer.removeClass("hide");
				ccLink.hide();
			} else {
				ccContainer.addClass("hide");
				ccLink.removeClass("hide");
				ccLink.show();
			}

            var emailBCCFieldData = thisInstance.getPreloadAllData('bcc');
            var bcchide = false;
            if (typeof emailBCCFieldData != "undefined") {
                if (emailBCCFieldData.length > 0) {
                    cchide = true;
                }
            }

            if (bcchide) {
                bccContainer.removeClass("hide");
                bccLink.hide();
            } else {
                bccContainer.addClass("hide");
                bccLink.removeClass("hide");
                bccLink.show();
            }
            thisInstance.checkHiddenStatusofCcandBcc();

        });
    },
    registerIncludeSignatureEvent : function(container){
        var thisInstance = this;

        var ckEditorInstance = thisInstance.getckEditorInstance();
        var CkEditor = ckEditorInstance.getCkEditorInstanceFromName();

        var params = {
            module: 'EMAILMaker',
            action: 'IndexAjax',
            mode: 'getUserSignature'
        };

        container.find('.includeSignature').on('click',function(e){
            app.helper.showProgress();
			app.request.post({'data' : params}).then(
				function(err,response) {
					app.helper.hideProgress();
					if(err === null){
						var result = response.success;
						if(result == true) {
                            CkEditor.insertHtml(response.signature);
						}
					}
				}
			);
        });
    },
    getModalNewHeight: function (modalContainer){

        var modalHeaderHeight = modalContainer.find('.modal-header').height();
        var windowHeight = jQuery(window).height();
        var modalFooterHeight = modalContainer.find('.modal-footer').height();
        return windowHeight - modalHeaderHeight - modalFooterHeight - 100;
    },
    loadCkEditor : function(textAreaElement, container){
        var ckEditorInstance = this.getckEditorInstance();
        var new_height = this.getModalNewHeight(container);

        var topContentHeight = container.find('.topContent').height();
        new_height = new_height - topContentHeight - 180;

        ckEditorInstance.loadCkEditor(textAreaElement,{'height' : (new_height)});
    },
    registerSaveDraftOrSendEmailEvent : function(){
        var thisInstance = this;
        var form = this.getMassEmailForm();
        form.on('click','#sendEmail, #saveDraft',function(e){
            var targetName = jQuery(e.currentTarget).attr('name');
            if(targetName === 'savedraft'){
                jQuery('#flag').val(thisInstance.saved);
            } else {
                jQuery('#flag').val(thisInstance.sent);
            }
            var params = {
                submitHandler: function(form) {
                    form = jQuery(form);
                    app.helper.hideModal();
                    app.helper.showProgress();
                    if (CKEDITOR.instances['description']) {
                        form.find('#description').val(CKEDITOR.instances['description'].getData());
                    }
                    var data = new FormData(form[0]);
                    var postParams = {
                        data:data,
                        // jQuery will set contentType = multipart/form-data based on data we are sending
                        contentType:false,
                        // we don’t want jQuery trying to transform file data into a huge query string, we want raw data to be sent to server
                        processData:false
                    };

                    app.request.post(postParams).then(function(err,data){
                        app.helper.hideProgress();
                        if (typeof data != 'undefined') {
                            var ele = jQuery(data);
                            var success = ele.find('.mailSentSuccessfully');
                            if (success.length <= 0) {
                                app.helper.showModal(data);
                            } else {
                                app.event.trigger('post.mail.sent', data);
                            }
                        } else {
                            app.helper.showErrorNotification({'message': err['message']});
						}
                    });
                }
            };
            form.vtValidate(params);
        });
    },
	registerEvents : function(){
		var thisInstance = this;
		var container = this.getMassEmailForm();
		if(container.length > 0){
            this.registerCcAndBccEvents();
            this.registerPDFMakerEvents(container);
			this.registerPreventFormSubmitEvent();
			this.registerAutoCompleteFields(container,"");
            this.registerAutoCompleteFields(container,"cc");
            this.registerAutoCompleteFields(container,"bcc");
            this.registerEmailSourcesList(container);

			jQuery("#multiFile").MultiFile({
				list: '#attachments',
				'afterFileSelect' : function(element, value, master_element){
					var masterElement = master_element;
					var newElement = jQuery(masterElement.current);
					newElement.addClass('removeNoFileChosen');
					thisInstance.fileAfterSelectHandler(element, value, master_element);
				},
				'afterFileRemove' : function(element, value, master_element){
					if (jQuery('#attachments').is(':empty')){
						jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
					}
					thisInstance.removeAttachmentFileSizeByElement(jQuery(element));
				}
			});
			this.registerRemoveAttachmentEvent();
			this.registerBrowseCrmEvent();
			this.calculateUploadFileSize();
			this.registerSaveDraftOrSendEmailEvent();
			var isCkeditorApplied = jQuery('#description').data('isCkeditorApplied');
			if(isCkeditorApplied != true){
				this.loadCkEditor(jQuery('#description').data('isCkeditorApplied',true), container);
			}
			this.registerSelectEmailTemplateEvent();
			this.registerEventsForToField();
			this.registerEventForRemoveCustomAttachments();
            this.registerIncludeSignatureEvent(container);

			app.event.on("post.DocumentsList.click",function(event, data){
				var responseData = JSON.parse(data);
				jQuery('.popupModal').modal('hide');
				for(var id in responseData){
					selectedDocumentId = id;
					var selectedFileName = responseData[id].info['filename'];
					var selectedFileSize = responseData[id].info['filesize'];
					var response = thisInstance.writeDocumentIds(selectedDocumentId)
					if(response){
						var attachmentElement = thisInstance.getDocumentAttachmentElement(selectedFileName,id,selectedFileSize);
						//TODO handle the validation if the size exceeds 5mb before appending.
						jQuery(attachmentElement).appendTo(jQuery('#attachments'));
						jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
						thisInstance.setDocumentsFileSize(selectedFileSize);
					}
				}
			});

			jQuery('#emailTemplateWarning .alert-warning .close').click(function(e){
				e.preventDefault();
				e.stopPropagation();
				jQuery('#emailTemplateWarning').addClass('hide');
			});

			app.event.on("post.EmailTemplateList.click",function(event, data){

				var responseData = JSON.parse(data);
				jQuery('.popupModal').modal('hide');

				var ckEditorInstance = thisInstance.getckEditorInstance();

				for(let id in responseData){
					let data = responseData[id],
						DataInfo = data['info'];
					ckEditorInstance.loadContentsInCkeditor(DataInfo['body']);
					jQuery('#subject').val(DataInfo['subject']);
					let selectedTemplateBody = responseData[id].info;
				}
				let sourceModule = jQuery('[name=source_module]').val(),
					showWarning = false;
				if(typeof selectedTemplateBody === 'string') {
					var tokenDataPair = selectedTemplateBody.split('$');
					for (var i=0; i<tokenDataPair.length; i++) {
						var module = tokenDataPair[i].split('-'),
							pattern = /^[A-z]+$/;
						if(pattern.test(module[0])) {
							if(!(module[0] == sourceModule.toLowerCase() || module[0] == 'users' || module[0] == 'custom')) {
								showWarning = true;
							}
						}
					}
				}
				if(showWarning) {
					jQuery('#emailTemplateWarning').removeClass('hide');
				} else {
					jQuery('#emailTemplateWarning').addClass('hide');
				}
			});
			var params = {
				setHeight:(jQuery(window).height() - container.find('.modal-header').height() - container.find('.modal-footer').height() - 100)+'px'
			};
			app.helper.showVerticalScroll(container.find('.modal-body'), params);

		}
	},
    getEmailsSourceId : function(mailInfo) {
        var mailInfoElement = this.getMassEmailForm().find('[name="selected_sourceid"]');
        return mailInfoElement.val();
    },
    addCCEmailAddressData : function(mailInfo) {
		var sid = this.getEmailsSourceId();
        var mailInfoElement = this.getMassEmailForm().find('[name="'+sid+'ccemailinfo"]');
        var existingCCMailInfo = JSON.parse(mailInfoElement.val());
        if(typeof existingCCMailInfo.length != 'undefined') {
            existingCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if(existingCCMailInfo.hasOwnProperty(mailInfo.eid) === true){
            var existingValues = existingCCMailInfo[mailInfo.eid];
            var newValue = new Array(mailInfo.name);
            existingCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues,newValue);
        } else {
            existingCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }
        mailInfoElement.val(JSON.stringify(existingCCMailInfo));
    },
    addBCCEmailAddressData : function(mailInfo) {
        var sid = this.getEmailsSourceId();
        var mailInfoElement = this.getMassEmailForm().find('[name="'+sid+'bccemailinfo"]');
        var existingBCCMailInfo = JSON.parse(mailInfoElement.val());
        if(typeof existingBCCMailInfo.length != 'undefined') {
            existingBCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if(existingBCCMailInfo.hasOwnProperty(mailInfo.eid) === true){
            var existingValues = existingBCCMailInfo[mailInfo.eid];
            var newValue = new Array(mailInfo.name);
            existingBCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues,newValue);
        } else {
            existingBCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }
        mailInfoElement.val(JSON.stringify(existingBCCMailInfo));
    }
});


