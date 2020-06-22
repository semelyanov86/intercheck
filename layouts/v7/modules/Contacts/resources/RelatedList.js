Vtiger_RelatedList_Js("Contacts_RelatedList_Js", {}, {
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
    init : function(parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
        this._super(parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
        this.registerAddExternalPaymentEvent();
    }
})