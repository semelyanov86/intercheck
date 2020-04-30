/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Emails_List_Js", {

}, {
    /*
 * Function to register the list view row click event
 */
    registerRowClickEvent: function () {
        var thisInstance = this;
        var listViewContentDiv = this.getListViewContainer();

        // added to stop the link functunality for few milli seconds
        listViewContentDiv.on('click', '.listViewEntries a', function (e) {
            var currentAElement = jQuery(e.currentTarget);
            var curTd = currentAElement.parents('td');
            if (curTd.data('name') == 'subject') {
                e.preventDefault();
                var curTr = curTd.parents('tr');
                var curId = curTr.data('id');
                Vtiger_Index_Js.showEmailPreview(curId,'');
            }
            e.stopPropagation();
        });

        // Single click event - detail view
        /*listViewContentDiv.on('click', '.listViewEntries', function (e) {
            var target = jQuery(e.target);
            if (!target.hasClass('js-reference-display-value')) {
                setTimeout(function () {
                    var editedLength = jQuery('.listViewEntries.edited').length;
                    if (editedLength === 0) {
                        var selection = window.getSelection().toString();
                        if (selection.length == 0) {
                            var target = jQuery(e.target, jQuery(e.currentTarget));
                            if (target.closest('td').is('td:first-child'))
                                return;
                            if (target.closest('tr').hasClass('edited'))
                                return;
                            if (jQuery(e.target).is('input[type="checkbox"]'))
                                return;
                            var elem = jQuery(e.currentTarget);
                            var recordUrl = elem.data('recordurl');
                            if (typeof recordUrl == 'undefined') {
                                return;
                            }
                            window.location.href = recordUrl;
                        }
                    }
                }, 300);
            }
        });*/
    },
});
