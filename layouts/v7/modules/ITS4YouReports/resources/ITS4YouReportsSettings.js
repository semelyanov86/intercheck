/*+***********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Index_Js('ITS4YouReports_ITS4YouReportsSettings_Js', {}, {

    showMessage : function(customParams){
        var params = {};
        params.animation = "show";
        params.type = 'info';
        params.title = app.vtranslate('JS_MESSAGE');

        if(typeof customParams != 'undefined') {
            var params = jQuery.extend(params,customParams);
        }
        Vtiger_Helper_Js.showPnotify(params);
    },

    registerSubmitEvent: function () {
        var thisInstance = this;
        var editViewForm = jQuery('form[name="settings_edit"]');
        editViewForm.submit(function (e) {
            //Form should submit only once for multiple clicks also
            if (typeof editViewForm.data('submit') != "undefined") {
                return false;
            } else {
                if ('' !== jQuery('#default_sharing').val()) {
                    return true;
                } else {
                    editViewForm.removeData('submit');
                    return false;
                }
            }
        });
    },

    checkMsgSaved: function () {
        var msgSaved = jQuery('input[name="msg_saved"]').val();

        if ('true' === msgSaved) {
            app.hideModalWindow();
            var params = {
                title: app.vtranslate('JS_SAVED'),
                type: 'success'
            };
            Vtiger_Helper_Js.showPnotify(params);
        }
    },

    registerEvents: function () {
        this.registerSubmitEvent();
        this.checkMsgSaved();
    }
});