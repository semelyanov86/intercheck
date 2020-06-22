/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Contacts_Detail_Js", {

	openPlatform: function(url) {
		var win = window.open(url, '_blank');
		win.focus();
	}
}, {
	registerAjaxPreSaveEvents: function (container) {
		var thisInstance = this;
		app.event.on(Vtiger_Detail_Js.PreAjaxSaveEvent, function (e) {
			if (!thisInstance.checkForPortalUser(container)) {
				e.preventDefault();
			}
		});
	},

	registerAddExternalPaymentEvent: function () {
		var btn = jQuery('#addExternalPayments');
		var params = {
			module: 'Transactions',
			view: 'MassActionAjax',
			record: app.getRecordId()
		};
		btn.on('click', function(e) {
			app.helper.showProgress();
			app.request.post({data: params}).then(function(err,data) {
				if (err === null) {
					app.helper.hideProgress();
					if (data != "notShow") {
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
						alert('Contact has no platform ID');
					}
				}
			});
		});
	},
	/**
	 * Function to check for Portal User
	 */
	checkForPortalUser: function (form) {
		var element = jQuery('[name="portal"]', form);
		var response = element.is(':checked');
		var primaryEmailField = jQuery('[data-name="email"]');
		if(primaryEmailField.length > 0) var primaryEmailValue = primaryEmailField["0"].attributes["data-value"].value;
		if (response) {
			if (primaryEmailField.length == 0) {
				app.helper.showErrorNotification({message: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS')});
				return false;
			}
			if (primaryEmailValue == "") {
				app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER')});
				return false;
			}
		}
		return true;
	},
	/**
	 * Function which will register all the events
	 */
	registerEvents: function () {
		var form = this.getForm();
		this._super();
		this.registerAjaxPreSaveEvents(form);
		this.registerAddExternalPaymentEvent();
	}
})