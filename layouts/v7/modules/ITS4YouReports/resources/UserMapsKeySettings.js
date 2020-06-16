/*+***********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Index_Js('ITS4YouReports_UserMapsKeySettings_Js', {}, {

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

    displayKeyByType: function (typeValue) {
        jQuery('.maps_api_keys .inputElement').addClass('hide');
        if ('undefined' !== typeof jQuery('#maps_api_key_' + typeValue)) {
            jQuery('#maps_api_key_' + typeValue).removeClass('hide');
        }
    },

    registerEventsForKeyTypeChange: function () {
        var thisInstance = this;
        jQuery('#maps_api_use_type').on('change', function () {
            thisInstance.displayKeyByType(jQuery(this).val());
        });
    },

    registerSubmitEvent: function () {
        var thisInstance = this;
        var editViewForm = jQuery('form[name="user_map_key_edit"]');
        editViewForm.submit(function(e) {
            //Form should submit only once for multiple clicks also
            if(typeof editViewForm.data('submit') != "undefined") {
                return false;
            } else {
                var useType = jQuery('#maps_api_use_type').val();
                if (('default' === useType && '' !== jQuery('#maps_api_key_default').val())
                    || ('user' === useType && '' !== jQuery('#maps_api_key_user').val())) {
                    return true;
                } else {
                    var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
                    var params = {
                        title: app.vtranslate('JS_EMPTY_API_KEY'),
                        type: 'error'
                    };
                    if(isAlertAlreadyShown <= 0) {
                        thisInstance.showMessage(params);
                    }
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
                title : app.vtranslate('JS_MSG_SAVED'),
                type : 'success'
            };
            Vtiger_Helper_Js.showPnotify(params);
        }
    },

    registerEvents: function () {
        Vtiger_Index_Js.getInstance().registerAppTriggerEvent();
        this.registerEventsForKeyTypeChange();
        this.displayKeyByType(jQuery('#maps_api_use_type').val());
        this.registerSubmitEvent();
        this.checkMsgSaved();
    }
});