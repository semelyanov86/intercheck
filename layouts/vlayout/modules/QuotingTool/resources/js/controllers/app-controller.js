/* ********************************************************************************
 * The content of this file is subject to the Quoting Tool ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

(function ($) {
    'use strict';

    var controllers = angular.module('AppControllers', ['AppModels', 'AppConfig', 'AppI18N', 'AppDirectives', 'ui.router']);

    controllers.controller('CtrlApp',
        function ($rootScope, $scope, $templateCache, $compile, $timeout, $translate, PageTitle, AppUtils, AppToolbar, AppConstants,
                  Template, TemplateSetting, TemplateProposal, GlobalConfig) {
            // Reset page title
            PageTitle.reset();

            $scope.inventoryFields = [];
            var productModuleFields = [];
            var idxProductBlockModules = {};

            $scope.tableBlockTotal = [];
            $scope.tableBlockTheme = AppConstants.TABLE_BLOCK.THEMES;
            $scope.tableBlockSize = AppConstants.TABLE_BLOCK.SIZE;

            $scope.settings = {};
            $scope.fieldImage = {};
            $scope.settings.pricing_table = {};
            $scope.settings.related_module = {};
            $scope.settings.pricing_table.theme = $scope.tableBlockTheme[0];    // Default is first theme
            $scope.settings.pricing_table.size = $scope.tableBlockSize[1];      // Default is first size
            $scope.settings.related_module.theme = $scope.tableBlockTheme[0];
            $scope.settings.related_module.size = $scope.tableBlockSize[1];
            $scope.settings.pricing_table.total_fields = [];
            $scope.settings.pricing_table.total_fields_order = [];
            $scope.settings.pricing_table.item_fields = [];
            $scope.settings.pricing_table.item_fields_order = [];
            $scope.settings.create_related_record = {};
            $scope.settings.create_related_record.theme = $scope.tableBlockTheme[0];
            $scope.settings.create_related_record.size = $scope.tableBlockSize[1];
            $scope.settings.create_related_record.item_fields = [];
            $scope.settings.create_related_record.item_fields_order = [];
            $scope.settings.create_related_record.link_module = [];

            $scope.changeLanguage = function (key) {
                $translate.use(key);
            };

            /**
             * @param container
             */
            $rootScope.refreshHeadings = function (container) {
                if (typeof container === 'undefined') {
                    container = $rootScope.app.container.find('[data-id="' + $rootScope.app.data.blocks.toc.template + '"]');
                }

                var htmlHeading = '';
                var tagName = '';
                var tagText = '';
                var headingNumber = 1;
                var marginLeft = '0';
                var tag = null;
                var objTag = null;
                var info = null;
                var indexing = false;

                var headings = $rootScope.app.container
                    .find('.quoting_tool-content:not([data-page-name="cover_page"])')
                    .find('.quoting_tool-content-main')
                    .find('.content-container:not([data-id="' + $rootScope.app.data.blocks.toc.template + '"])')
                    .find('h1, h2, h3, h4, h5, h6');

                for (var i = 0; i < headings.length; i++) {
                    tag = headings[i];
                    objTag = $(tag);
                    info = objTag.data('info');

                    if (!info) {
                        info = {};
                    }

                    indexing = (info['indexing']) ? info['indexing'] : false;

                    if (indexing) {
                        // Only show if it enable
                        tagName = tag.tagName.toLowerCase();
                        tagText = tag.textContent;
                        headingNumber = parseInt(tagName.substring(1));
                        marginLeft = ((headingNumber - 1) * 20) + 'px'; // Margin from h2

                        htmlHeading += '<' + tagName + ' style="margin-left: ' + marginLeft + ';">' + tagText + '</' + tagName + '>';
                    }

                }

                var tocBlockContainer = null;

                for (var i = 0; i < container.length; i++) {
                    tocBlockContainer = $(container[i]).find('.content-editable');
                    // Replace content
                    tocBlockContainer.html(htmlHeading);
                }
            };

            /**
             * @param $event
             */
            $rootScope.remove = function ($event) {
                $event.preventDefault();

                var target = $($event.target);
                var container = target.closest('.content-container');
                var template = container.data('id');

                // Only with draggable object (widgets)
                var next = container.next('.content-container.quoting_tool-draggable');
                // Remove the component
                container.remove();

                if (template == $rootScope.app.data.blocks.heading.template) {
                    // Refresh headings
                    $rootScope.refreshHeadings();
                }

                // Re calculate position. Only with draggable object (widgets)
                if (next && next.length > 0) {
                    $rootScope.calculateWidgetPosition(next);
                }
            };

            /**
             * Fn - $scope.removeCoverPage
             *
             * @param $event
             */
            $rootScope.removeCoverPage = function ($event) {
                $event.preventDefault();

                var coverPage = $('[data-id="' + $rootScope.app.data.blocks.cover_page.template + '"]');
                // Switch focus page:
                $rootScope.app.last_focus_page = coverPage.next('.quoting_tool-content');
                coverPage.remove();
            };

            /**
             * Fn - $scope.setting
             *
             * @param $event
             * @param {Object=} focus
             */
            $rootScope.setting = function ($event, focus) {
                $event.preventDefault();

                if (!focus) {
                    return false;
                }

                var target = $($event.target);
                var container = target.closest('.content-container');
                var editable = container.find('.content-editable');
                $rootScope.app.last_focus_item_setting = editable.find('input, select, textarea');
                if(focus.layout.name == 'Spacer'){
                    $rootScope.app.last_focus_item_setting = editable.find('div.spacer');
                }
                if(focus.layout.name == 'Icon Label'){
                    $rootScope.app.last_focus_item_setting = editable.find('img.image-icon-label');
                }
                // Update exist values
                var info = $rootScope.app.last_focus_item_setting.data('info');

                if (typeof info === 'undefined') {
                    info = {};
                }

                // When is block
                switch (focus) {
                    case $rootScope.app.data.blocks.heading:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            var objTextField = container.find('h1, h2, h3, h4, h5, h6');
                            var info = objTextField.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            html.find('[name="indexing"]').prop({
                                'checked': info['indexing'] ? info['indexing'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var indexing = html.find('[name="indexing"]');
                                    info['indexing'] = indexing.is(':checked');
                                    objTextField.attr('data-info', JSON.stringify(info));
                                    // Refresh heading
                                    $rootScope.refreshHeadings();

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.widgets.signature:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            AppHelper.showModalWindow(html, '#', function () {
                                // Before open modal
                                var contenteditable = editable.find('[contenteditable="true"]');

                                if (!contenteditable || contenteditable.length == 0) {
                                    return;
                                }

                                var objSignature = contenteditable.find('.quoting_tool-widget-signature-main');
                                var dataInfo = objSignature.data('info');

                                if (typeof dataInfo === 'undefined') {
                                    dataInfo = {};
                                }

                                var sigPad = html.find('.sigPad').signaturePad({
                                    lineTop: 114,
                                    lineWidth: 1,
                                    drawOnly: true,
                                    bgColour: 'transparent'
                                });

                                //
                                var widgetSignatureHeader = container.find('.quoting_tool-widget-signature-header');
                                var widgetSignatureImage = container.find('.quoting_tool-widget-signature-image');
                                var widgetSignature = container.find('.quoting_tool-widget-signature');
                                var targetContainer = widgetSignatureImage.parent('[data-target="#myModal"]');

                                //
                                var inName = html.find('[name="name"]');
                                var inSignature = html.find('[name="output"]');
                                inName.val(dataInfo['signature_name'] ? dataInfo['signature_name'] : $rootScope.app.user.profile['full_name']);
                                inSignature.val(dataInfo['signature']);
                                // Init signature pad image
                                if (dataInfo['signature']) {
                                    sigPad.regenerate(dataInfo['signature']);
                                }

                                // Default replace signature
                                var inReplaceSignatureButton = $('input[name="replace_signature_button"]');
                                if (targetContainer.length > 0) {
                                    inReplaceSignatureButton.prop('checked', true);
                                } else {
                                    inReplaceSignatureButton.prop('checked', false);
                                }

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var signatureImage = sigPad.getSignatureImage();
                                    var signatureName = inName.val();
                                    var signature = inSignature.val();
                                    dataInfo['signature_name'] = signatureName;
                                    dataInfo['signature'] = signature;
                                    widgetSignature.html(signatureName);

                                    if (inReplaceSignatureButton.is(':checked')) {
                                        if (targetContainer.length == 0) {
                                            widgetSignatureImage.wrap('<a href="javascript:void(0);" data-target="#myModal"/>');
                                        }

                                        widgetSignatureImage.attr('src', $rootScope.app.config.base + 'modules/QuotingTool/resources/images/placeholder-signature.png');
                                        widgetSignatureHeader.css({
                                            'visibility': 'visible'
                                        });
                                    } else {
                                        if (targetContainer.length > 0) {
                                            widgetSignatureImage.unwrap();
                                        }

                                        widgetSignatureImage.attr('src', signatureImage);
                                        widgetSignatureHeader.css({
                                            'visibility': 'hidden'
                                        });
                                    }

                                    objSignature.attr('data-info', JSON.stringify(dataInfo));

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.widgets.secondary_signature:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            AppHelper.showModalWindow(html, '#', function () {
                                // Before open modal
                                var contenteditable = editable.find('[contenteditable="true"]');

                                if (!contenteditable || contenteditable.length == 0) {
                                    return;
                                }

                                var objSignature = contenteditable.find('.quoting_tool-widget-secondary_signature-main');
                                var dataInfo = objSignature.data('info');

                                if (typeof dataInfo === 'undefined') {
                                    dataInfo = {};
                                }

                                var sigPad = html.find('.sigPad').signaturePad({
                                    lineTop: 114,
                                    lineWidth: 1,
                                    drawOnly: true,
                                    bgColour: 'transparent'
                                });

                                //
                                var widgetSignatureHeader = container.find('.quoting_tool-widget-secondary_signature-header');
                                var widgetSignatureImage = container.find('.quoting_tool-widget-secondary_signature-image');
                                var widgetSignature = container.find('.quoting_tool-widget-secondary_signature');
                                var targetContainer = widgetSignatureImage.parent('[data-target="#myModal"]');

                                //
                                var inName = html.find('[name="name"]');
                                var inSignature = html.find('[name="output"]');
                                inName.val(dataInfo['signature_name'] ? dataInfo['signature_name'] : $rootScope.app.user.profile['full_name']);
                                inSignature.val(dataInfo['secondary_signature']);
                                // Init secondary_signature pad image
                                if (dataInfo['secondary_signature']) {
                                    sigPad.regenerate(dataInfo['secondary_signature']);
                                }

                                // Default replace secondary_signature
                                var inReplaceSignatureButton = $('input[name="replace_signature_button"]');
                                if (targetContainer.length > 0) {
                                    inReplaceSignatureButton.prop('checked', true);
                                } else {
                                    inReplaceSignatureButton.prop('checked', false);
                                }

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var signatureImage = sigPad.getSignatureImage();
                                    var signatureName = inName.val();
                                    var secondary_signature = inSignature.val();
                                    dataInfo['signature_name'] = signatureName;
                                    dataInfo['secondary_signature'] = secondary_signature;
                                    widgetSignature.html(signatureName);

                                    if (inReplaceSignatureButton.is(':checked')) {
                                        if (targetContainer.length == 0) {
                                            widgetSignatureImage.wrap('<a href="javascript:void(0);" data-target="#myModal"/>');
                                        }

                                        widgetSignatureImage.attr('src', $rootScope.app.config.base + 'modules/QuotingTool/resources/images/placeholder-signature.png');
                                        widgetSignatureHeader.css({
                                            'visibility': 'visible'
                                        });
                                    } else {
                                        if (targetContainer.length > 0) {
                                            widgetSignatureImage.unwrap();
                                        }

                                        widgetSignatureImage.attr('src', signatureImage);
                                        widgetSignatureHeader.css({
                                            'visibility': 'hidden'
                                        });
                                    }

                                    objSignature.attr('data-info', JSON.stringify(dataInfo));

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.widgets.date:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            var objPicker = container.find('[name="datepicker"]');
                            var info = objPicker.data('info');
                            var dateFormat = objPicker.data('date-format');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            // Current statuses
                            // time stamp
                            html.find('[name="current_timestamp"]').prop({
                                'checked': info['current_timestamp'] ? info['current_timestamp'] : false
                            });

                            // Editable
                            html.find('[name="editable"]').prop({
                                'checked': info['editable'] ? info['editable'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var currentTimestamp = html.find('[name="current_timestamp"]');
                                    info['current_timestamp'] = currentTimestamp.is(':checked');
                                    var editable = html.find('[name="editable"]');
                                    info['editable'] = editable.is(':checked');
                                    // date format
                                    info['date_format'] = dateFormat;
                                    objPicker.attr('data-info', JSON.stringify(info));

                                    //
                                    if (info['current_timestamp']) {
                                        var timestamp = new Date();
                                        var currentDate = AppHelper.formatDate(dateFormat, timestamp);
                                        objPicker.attr({
                                            'value': currentDate
                                        });
                                        // Update
                                        objPicker.datepicker('update', timestamp);
                                    }

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.widgets.datetime:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            var objPicker = container.find('[name="datetimepicker"]');
                            var info = objPicker.data('info');
                            var datetime_format = objPicker.data('datetime-format');
                            var date_format = objPicker.data('date-format');
                            var time_format = objPicker.data('time-format');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            // Current statuses
                            // time stamp
                            html.find('[name="current_timestamp"]').prop({
                                'checked': info['current_timestamp'] ? info['current_timestamp'] : false
                            });

                            // Editable
                            html.find('[name="editable"]').prop({
                                'checked': info['editable'] ? info['editable'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var currentTimestamp = html.find('[name="current_timestamp"]');
                                    info['current_timestamp'] = currentTimestamp.is(':checked');
                                    var editable = html.find('[name="editable"]');
                                    info['editable'] = editable.is(':checked');
                                    // date format
                                    info['datetime_format'] = datetime_format;
                                    info['date_format'] = date_format;
                                    info['time_format'] = time_format;
                                    objPicker.attr('data-info', JSON.stringify(info));

                                    var currentTime = '00:00';

                                    //
                                    if (info['current_timestamp']) {
                                        var timestamp = new Date();
                                        var currentDate = AppHelper.formatDate(date_format, timestamp);
                                        objPicker.attr({
                                            'value': currentDate + ' ' + currentTime
                                        });
                                        // // Update
                                        // objPicker.datepicker('update', timestamp);
                                    }

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.blocks.pricing_table:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            $scope.inventoryFields = [];
                            $scope.inventoryFields.length = 0;

                            if (idxProductBlockModules[$rootScope.app.model.module]) {
                                $scope.inventoryFields = angular.copy(productModuleFields);

                                var module = angular.copy(idxProductBlockModules[$rootScope.app.model.module]);
                                // Final fields
                                $scope.tableBlockTotal = module['final_details'];
                                // fields
                                var fields = module.fields;
                                var field = null;
                                var LBL_ITEM_DETAILS = [];
                                var idxRemove = [];

                                for (var f = 0; f < fields.length; f++) {
                                    field = fields[f];
                                    field.block.label = module.name + ' - ' + field.block.label;

                                    if (field.block.name == 'LBL_ITEM_DETAILS') {
                                        LBL_ITEM_DETAILS.push(field);
                                        idxRemove.push(f);
                                    }
                                }

                                // Remove duplicate before concat
                                var removed = 0;
                                for (var f = 0; f < idxRemove.length; f++) {
                                    fields.splice((idxRemove[f] - removed), 1);

                                    removed++;
                                }
                                for (var i = 0; i < $scope.inventoryFields.length; i++) {
                                    if($scope.inventoryFields[i].id == '-1'){
                                        $scope.inventoryFields.splice(i, 1);
                                    }
                                }
                                // $scope.inventoryFields = $.merge($scope.inventoryFields, fields);
                                //get Quote detial field and products/service field
                                $scope.inventoryFields = $.merge(LBL_ITEM_DETAILS, $scope.inventoryFields);
                                //Add Item detail to first
                                // $scope.inventoryFields = LBL_ITEM_DETAILS.concat($scope.inventoryFields);
                                //only get itemDetails field.
                                // $scope.inventoryFields = LBL_ITEM_DETAILS;
                            }

                            // Reset settings
                            $scope.settings.pricing_table.theme = $scope.tableBlockTheme[0];    // Default is first theme
                            $scope.settings.pricing_table.size = $scope.tableBlockSize[1];    // Default is first size
                            $scope.settings.pricing_table.total_fields = [];
                            $scope.settings.pricing_table.total_fields.length = 0;
                            $scope.settings.pricing_table.total_fields_order = [];
                            $scope.settings.pricing_table.total_fields_order.length = 0;
                            $scope.settings.pricing_table.item_fields = [];
                            $scope.settings.pricing_table.item_fields.length = 0;
                            $scope.settings.pricing_table.item_fields_order = [];
                            $scope.settings.pricing_table.item_fields_order.length = 0;
                            var itemFieldOrder = [];
                            var itemFieldTotalOrder =[];

                            // Default selected
                            if (info['settings']) {
                                var selected = null;
                                var selectedItem = null;
                                var inventoryFieldItem = null;

                                // item fields
                                if (info['settings']['item_fields']) {
                                    selected = info['settings']['item_fields'];
                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];
                                        for (var inv = 0; inv < $scope.inventoryFields.length; inv++) {
                                            inventoryFieldItem = $scope.inventoryFields[inv];
                                            if ((inventoryFieldItem.module == selectedItem.module)
                                                && (inventoryFieldItem.block.id == selectedItem.block.id)
                                                && (inventoryFieldItem.id == selectedItem.id)
                                                && (inventoryFieldItem.name == selectedItem.name)) {
                                                $scope.settings.pricing_table.item_fields.push($scope.inventoryFields[inv]);
                                            }
                                        }
                                    }
                                }

                                // total fields
                                if (info['settings']['total_fields']) {
                                    selected = info['settings']['total_fields'];

                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];

                                        for (var inv = 0; inv < $scope.tableBlockTotal.length; inv++) {
                                            inventoryFieldItem = $scope.tableBlockTotal[inv];

                                            if ((inventoryFieldItem.module == selectedItem.module)
                                                && (inventoryFieldItem.block.id == selectedItem.block.id)
                                                && (inventoryFieldItem.id == selectedItem.id)
                                                && (inventoryFieldItem.name == selectedItem.name)) {
                                                $scope.settings.pricing_table.total_fields.push($scope.tableBlockTotal[inv]);
                                            }
                                        }
                                    }
                                }

                                // Theme
                                if (info['settings']['theme']) {
                                    selectedItem = info['settings']['theme'];

                                    for (var inv = 0; inv < $scope.tableBlockTheme.length; inv++) {
                                        inventoryFieldItem = $scope.tableBlockTheme[inv];

                                        if ((inventoryFieldItem.id == selectedItem.id)
                                            && (inventoryFieldItem.name == selectedItem.name)) {
                                            $scope.settings.pricing_table.theme = $scope.tableBlockTheme[inv];
                                            break;
                                        }
                                    }
                                }
                                // Size
                                if (info['settings']['size']) {
                                    selectedItem = info['settings']['size'];

                                    for (var inv = 0; inv < $scope.tableBlockSize.length; inv++) {
                                        inventoryFieldItem = $scope.tableBlockSize[inv];

                                        if ((inventoryFieldItem.id == selectedItem.id)
                                            && (inventoryFieldItem.name == selectedItem.name)) {
                                            $scope.settings.pricing_table.size = $scope.tableBlockSize[inv];
                                            break;
                                        }
                                    }
                                }
                                // order item fields
                                if (info['settings']['item_fields_order']) {
                                    $scope.settings.pricing_table.item_fields_order = info['settings']['item_fields_order'];
                                }

                                // order total fields
                                if (info['settings']['total_fields_order']) {
                                    $scope.settings.pricing_table.total_fields_order = info['settings']['total_fields_order'];
                                }

                            } else {
                                var templateFieldSelected = [];
                                var tbodyContent = objTable.find('tbody > tr.tbody-content');
                                var objFieldContent = tbodyContent.find('td');
                                var objFieldContentItem = null;

                                for (var fct = 0; fct < objFieldContent.length; fct++) {
                                    objFieldContentItem = $(objFieldContent[fct]);
                                    var n= objFieldContentItem.text().indexOf("productid");
                                    var m= objFieldContentItem.text().indexOf("comment");
                                    if(n>=0 && m >=0){
                                        templateFieldSelected.push(objFieldContentItem.text().trim());
                                    }
                                    else{
                                        objFieldContentItem=objFieldContentItem.html().trim().replace('\\$ ','');
                                        if(typeof objFieldContentItem =='string'){
                                            templateFieldSelected.push(objFieldContentItem);
                                        }else {
                                            templateFieldSelected.push(objFieldContentItem.text().trim());
                                        }
                                    }
                                }


                                // item fields
                                var field = null;

                                for (var sf = 0; sf < templateFieldSelected.length; sf++) {
                                    for (var gbf = 0; gbf < $scope.inventoryFields.length; gbf++) {
                                        field = $scope.inventoryFields[gbf];
                                        if (field.token == templateFieldSelected[sf]) {
                                            $scope.settings.pricing_table.item_fields.push($scope.inventoryFields[gbf]);
                                            itemFieldOrder.push(field.label);
                                        }
                                    }
                                }

                                // item total selected
                                var itemTotalSelected = [];
                                var tfooterContent = objTable.find('.item-total');
                                var objFieldContentItemTotal = null;
                                for (var fct = 0; fct < tfooterContent.length; fct++) {
                                    objFieldContentItemTotal = $(tfooterContent[fct]);
                                    var n= objFieldContentItemTotal.text().indexOf("productid");
                                    var m= objFieldContentItemTotal.text().indexOf("comment");
                                    if(n>=0 && m >=0){
                                        itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                    }
                                    else{
                                        objFieldContentItemTotal=objFieldContentItemTotal.html().trim().replace('\\$ ','');
                                        if(typeof objFieldContentItemTotal =='string'){
                                            itemTotalSelected.push(objFieldContentItemTotal);
                                        }else {
                                            itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                        }
                                    }
                                    // itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                }
                                // item total fields
                                var fieldTotal = null;

                                for (var sf = 0; sf < itemTotalSelected.length; sf++) {
                                    for (var gbf = 0; gbf < $scope.tableBlockTotal.length; gbf++) {
                                        fieldTotal = $scope.tableBlockTotal[gbf];

                                        if (fieldTotal.token == itemTotalSelected[sf]) {
                                            $scope.settings.pricing_table.total_fields.push($scope.tableBlockTotal[gbf]);
                                            itemFieldTotalOrder.push(fieldTotal.label);
                                        }
                                    }
                                }
                            }

                            // Open modal
                            app.showModalWindow(html, '#', function () {
                                var obj_quoter_settings = jQuery('#js_quoter_settings');

                                if (obj_quoter_settings.length != 0) {
                                    html.find('[name="quoterStatus"]').removeAttr('hidden');
                                }
                                var settingsPricingTableFields = html.find('#settings_pricing_table_fields');
                                var settingsPricingTotalFields = html.find('#settings_pricing_total_fields');

                                $timeout(function () {
                                    if (info['settings']) {
                                        var quoting_tool_product_image =editable.find('img.quoting_tool_product_image');
                                        if(quoting_tool_product_image.length > 0){
                                            var html_images_size = '<tr>'+
                                                '<th>Images size</th>' +
                                                '<td>' +
                                                '<input name="product_image_width" value="'+quoting_tool_product_image.width()+'" size="5" style="text-align: center;" /> X <input size="5" name="product_image_height" value="'+quoting_tool_product_image.height()+'"  style="text-align: center;" /> '+
                                                '</td>'+
                                                '</tr>';
                                            $('#settings_pricing_table_fields').closest('tr').after(html_images_size);
                                        }
                                    } else {
                                        // item field
                                        var selectedOptions = $('#settings_pricing_table_fields').find('option:selected');
                                        var rsOrderItem = [];
                                        var order = [];
                                        selectedOptions.each(function () {
                                            var focus = $(this);
                                            var myIndex = focus.index();
                                            order[myIndex] = focus.text();
                                        });
                                        jQuery.each(itemFieldOrder, function (idx, val) {
                                            jQuery.each(order, function (key, label) {
                                                if(val == label) {
                                                    rsOrderItem.push(key);
                                                }
                                            })
                                        });
                                        $scope.settings.pricing_table.item_fields_order = rsOrderItem;

                                        // item total field
                                        selectedOptions = $('#settings_pricing_total_fields').find('option:selected');
                                        rsOrderItem = [];
                                        order = [];
                                        selectedOptions.each(function () {
                                            var focus = $(this);
                                            var myIndex = focus.index();
                                            order[myIndex] = focus.text();
                                        });
                                        jQuery.each(itemFieldTotalOrder, function (idx, val) {
                                            jQuery.each(order, function (key, label) {
                                                if(val == label) {
                                                    rsOrderItem.push(key);
                                                }
                                            })
                                        });
                                        $scope.settings.pricing_table.total_fields_order = rsOrderItem;
                                    }
                                    AppHelper.arrangeSelectChoicesInOrder(settingsPricingTableFields, $scope.settings.pricing_table.item_fields_order);
                                    AppHelper.arrangeSelectChoicesInOrder(settingsPricingTotalFields, $scope.settings.pricing_table.total_fields_order);
                                }, 100);

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    // order item fields
                                    var itemFieldIds = AppHelper.getSelectedColumns(settingsPricingTableFields);
                                    var itemFields = [];
                                    var itemFieldSortedOption = null;
                                    var itemFieldSortedInfo = {};

                                    for (var id = 0; id < itemFieldIds.length; id++) {
                                        itemFieldSortedOption = settingsPricingTableFields.find('option[value="' + itemFieldIds[id] + '"]:selected');
                                        itemFieldSortedInfo = itemFieldSortedOption.data('info');
                                        if(itemFieldSortedInfo.datatype == "image"){
                                            itemFieldSortedInfo.width = html.find('input[name="product_image_width"]').val();
                                            itemFieldSortedInfo.height = html.find('input[name="product_image_height"]').val();
                                        }
                                        itemFields.push(itemFieldSortedInfo);
                                    }

                                    $scope.settings.pricing_table.item_fields_order = itemFieldIds;

                                    // Total field order
                                    var totalFieldIds = AppHelper.getSelectedColumns(settingsPricingTotalFields);
                                    var totalFields = [];
                                    var totalFieldSortedOption = null;
                                    var totalFieldSortedInfo = {};
                                    for (var id = 0; id < totalFieldIds.length; id++) {
                                        totalFieldSortedOption = settingsPricingTotalFields.find('option[value="' + totalFieldIds[id] + '"]:selected');
                                        totalFieldSortedInfo = totalFieldSortedOption.data('info');
                                        totalFields.push(totalFieldSortedInfo);
                                    }

                                    $scope.settings.pricing_table.total_fields_order = totalFieldIds;

                                    var selectedItem = null;
                                    var colHeader = [],
                                        colItem = [],
                                        colFooter = [];

                                    for (var i = 0; i < itemFields.length; i++) {
                                        selectedItem = itemFields[i];
                                        if(selectedItem.label=="Selling Price"){
                                            selectedItem.label="Price";
                                        }
                                        if(selectedItem.label=="Sequence No"){
                                            selectedItem.label="#";
                                        }
                                        if(selectedItem.label=="Item Discount Amount"){
                                            selectedItem.label="Discount";
                                        }
                                        if(selectedItem.label=="Item Discount Percent"){
                                            selectedItem.label="Discount %";
                                        }
                                        if(selectedItem.label=="Item Description"){
                                            selectedItem.label="Description";
                                        }
                                        if(selectedItem.label=="Item Name (with description)"){
                                            selectedItem.label="Item Name ";
                                        }
                                        if(selectedItem.label=='#'){
                                            colHeader.unshift(selectedItem.label);
                                        }else{
                                            colHeader.push(selectedItem.label);
                                        }
                                        if(selectedItem.label=='#'){
                                            colItem.unshift(selectedItem)
                                        }else{
                                            colItem.push(selectedItem);
                                        }
                                    }

                                    for (var i = 0; i < totalFields.length; i++) {
                                        selectedItem = totalFields[i];
                                        colFooter.push({
                                            label: selectedItem.label,
                                            token: selectedItem.token
                                        });
                                    }

                                    // Save settings
                                    info['settings'] = angular.copy($scope.settings.pricing_table);
                                    objTable.attr('data-info', angular.toJson(info));

                                    // Set table style:
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['style']) {
                                        // objTable.css(info['settings']['theme']['settings']['style']);
                                    }
                                    if (info['settings'] && info['settings']['size'] && info['settings']['size']['settings'] && info['settings']['size']['settings']['style']) {
                                        objTable.css(info['settings']['size']['settings']['style']);
                                        objTable.attr('cellpadding', info['settings']['size']['settings']['cellpadding']);
                                    }
                                    // Cell style:
                                    var cellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['cell'] && info['settings']['theme']['settings']['cell']['style']) {
                                        cellStyle = info['settings']['theme']['settings']['cell']['style'];

                                        objTable.find('th, td').css(cellStyle);
                                    }

                                    // Replace table
                                    // header
                                    var thead = objTable.find('thead');

                                    // Set thead style:
                                    var theadCellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['thead'] && info['settings']['theme']['settings']['thead']['style']) {
                                        theadCellStyle = $.extend({}, cellStyle, info['settings']['theme']['settings']['thead']['style']);
                                        thead.find('th, td').css(theadCellStyle);
                                    }
                                    thead.find('th, td').css({
                                        'text-align':'center'
                                    });
                                    var theadRows = thead.find('tr');
                                    var theadRow = null;
                                    var objTheadRow = null;
                                    var firstTheadCell = null;
                                    var newTheadCell = null;

                                    for (var thr = 0; thr < theadRows.length; thr++) {
                                        theadRow = theadRows[thr];
                                        objTheadRow = $(theadRow);
                                        firstTheadCell = objTheadRow.find('th, td').filter(':first');
                                        if (firstTheadCell && firstTheadCell.length > 0) {
                                            firstTheadCell = firstTheadCell.clone();
                                        } else {
                                            firstTheadCell = $('<th/>');
                                        }

                                        objTheadRow.empty();

                                        for (var i = 0; i < colHeader.length; i++) {
                                            newTheadCell = firstTheadCell.clone();
                                            newTheadCell.text(colHeader[i]);
                                            objTheadRow.append(newTheadCell);
                                        }
                                    }

                                    // body
                                    var tbody = objTable.find('tbody');
                                    var arrTr = tbody.find('tr');
                                    var tr = null,
                                        trText = null,
                                        trBlockStart = '',
                                        objTrBlockStart = null,
                                        trBlockEnd = '',
                                        objTrBlockEnd = null,
                                        markCell = null,
                                        objTrBlockItem = [],
                                        isStart = false,
                                        isEnd = false,
                                        oddStyle = {},
                                        evenStyle = {};

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['odd']
                                        && info['settings']['theme']['settings']['tbody']['odd']['style']) {
                                        oddStyle = info['settings']['theme']['settings']['tbody']['odd']['style'];
                                    }

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['even']
                                        && info['settings']['theme']['settings']['tbody']['even']['style']) {
                                        evenStyle = info['settings']['theme']['settings']['tbody']['even']['style'];
                                    }

                                    tbody.addAttributes({
                                        'data-odd-style': JSON.stringify(oddStyle),
                                        'data-even-style': JSON.stringify(evenStyle)
                                    });

                                    for (var i = 0; i < arrTr.length; i++) {
                                        tr = $(arrTr[i]);
                                        trText = tr.text();
                                        trText = trText ? trText.trim() : '';

                                        if (trText == '#PRODUCTBLOC_START#') {
                                            objTrBlockStart = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockStart = tr[0].outerHTML;
                                            isStart = true;
                                            continue;
                                        }

                                        if (trText && trText.trim() == '#PRODUCTBLOC_END#') {
                                            objTrBlockEnd = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockEnd = tr[0].outerHTML;
                                            isEnd = true;
                                            continue;
                                        }

                                        // item row
                                        if (isStart && !isEnd) {
                                            objTrBlockItem.push(tr);
                                        }
                                    }

                                    var tbodyRow = null;
                                    var objTbodyRow = null;
                                    var firstTbodyCell = null;
                                    var newTbodyCell = null;
                                    var arrNumberTypes = ['currency', 'double', 'integer', 'float'];
                                    for (var tbr = 0; tbr < objTrBlockItem.length; tbr++) {
                                        tbodyRow = objTrBlockItem[tbr];
                                        objTbodyRow = $(tbodyRow);
                                        firstTbodyCell = objTbodyRow.find('th, td').filter(':first');
                                        if (firstTbodyCell && firstTbodyCell.length > 0) {
                                            firstTbodyCell = firstTbodyCell.clone();
                                        } else {
                                            firstTbodyCell = $('<td/>');
                                        }
                                        objTbodyRow.empty();
                                        for (var i = 0; i < colItem.length; i++) {
                                            newTbodyCell = firstTbodyCell.clone();
                                            if (colItem[i].datatype == "image") {
                                                 var right_btn = "<div style='float: left;'><img data-fieldname = '"+colItem[i].token+"' data-productid = '$Products__product_no$' class='quoting_tool-cke-keep-element quoting_tool_product_image' src='modules/QuotingTool/resources/images/placeholder-image.png' alt=''   style='width: "+colItem[i].width+"px;height:"+colItem[i].height+"px;cursor: pointer;'></div>";
                                                 right_btn += "<div style='float: left;cursor: pointer;' class='doc-block__control btn--block-control quoting_tool-btn-options-product-image'>" +
                                                                 "<div class='p-image'></div>" +
                                                              "</div>";
                                                newTbodyCell.html(right_btn);
                                            }
                                            else if(colItem[i].datatype=='currency')
                                            {
                                                newTbodyCell.html("\\$ "+colItem[i].token)
                                            }else if(colItem[i].label=="Item Name "){
                                                newTbodyCell.html("<b>$"+$rootScope.app.model.module+"__productid$</b><br>$"+$rootScope.app.model.module+"__comment$");
                                            }
                                                else newTbodyCell.text(colItem[i].token);
                                            if ($.inArray(colItem[i].datatype, arrNumberTypes) >= 0) {
                                                newTbodyCell.css({
                                                    'text-align': 'right'
                                                })
                                            }
                                            newTbodyCell.css({
                                                'text-align': 'right'
                                            });
                                            if(colItem[i].label=="#"){
                                                newTbodyCell.css({
                                                    'text-align': 'center'
                                                })
                                            }
                                            if(colItem[i].label=="Item Name " || colItem[i].label=="Item Name"|| colItem[i].label=="Description"){
                                                newTbodyCell.css({
                                                    'text-align': 'left'
                                                })
                                            }
                                            if(colItem[i].token=='$'+$rootScope.app.model.module+'__taxTotal$'){
                                                newTbodyCell.attr('data-info','isTax');
                                            }else{
                                                newTbodyCell.removeAttr('data-info');
                                            }
                                            newTbodyCell.attr('valign','top');
                                            objTbodyRow.append(newTbodyCell);
                                                if(i==(colItem.length-1)){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"12%");
                                                } else if(colItem[i].label=="Item Name "){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                                    objTbodyRow.find('td').slice(i,i+1).css({
                                                        'min-width':"37%",
                                                    })
                                                }else if(colItem[i].label=="Item Name"){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"15%");
                                                }else if(colItem[i].label=="#"){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"4%");
                                                }else if(colItem[i].label=="Description"){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                                }else if(colItem[i].label=="Quantity"){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"8%");
                                                }else if(colItem[i].label=="Net Price" || colItem[i].label=="Tax " || colItem[i].label=="Price" || colItem[i].label=="Discount" ){
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"13%");
                                                } else {
                                                    objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                                }
                                            /////////////////
                                        }
                                    }
                                    var indexOfTax=objTable.find('[data-info="isTax"]').index();
                                    objTable.find('thead tr').find('th').removeAttr('data-info');
                                    objTable.find('thead tr').find('th').slice(indexOfTax,indexOfTax+1).attr('data-info','isTax');
                                    // footer
                                    var tfoot = objTable.find('tfoot');
                                    var footerColspan1 = colHeader.length - 1;
                                    var tfootRow = null;
                                    var newFootRow = null;
                                    var newFootCell = null;

                                    var objFootRow = tfoot.find('tr:first');
                                    if (tfootRow && tfootRow.length > 0) {
                                        tfootRow.clone();
                                    } else {
                                        tfootRow = $('<tr/>');
                                    }
                                    var firstFootCell = objFootRow.find('th, td').filter(':first');
                                    if (firstFootCell && firstFootCell.length > 0) {
                                        firstFootCell = firstFootCell.clone();
                                    } else {
                                        firstFootCell = $('<td/>');
                                    }

                                    // Empty tfoot
                                    tfoot.empty();

                                    for (var i = 0; i < colFooter.length; i++) {
                                        selectedItem = colFooter[i];
                                        newFootRow = tfootRow.clone();
                                        newFootRow.empty();
                                        // Label
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.attr('colspan', footerColspan1);
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.label);
                                        newFootRow.append(newFootCell);
                                        // Value
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.removeAttr('colspan');
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        // newFootCell.text(selectedItem.token);
                                        newFootCell.html("\\$ "+selectedItem.token);
                                        newFootRow.append(newFootCell);
                                        newFootRow.attr('valign','top');
                                        newFootRow.find('td:last').attr('width','12%');
                                        tfoot.append(newFootRow);
                                    }

                                    // Close modal
                                    app.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.pricing_table_idc:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            $scope.inventoryFields = [];
                            $scope.inventoryFields.length = 0;

                            if ($rootScope.app.data.quoter_settings[$rootScope.app.model.module]) {
                                $scope.inventoryFields = angular.copy($rootScope.app.data.quoter_settings[$rootScope.app.model.module].fields);
                                $scope.inventoryFields = $.merge($scope.inventoryFields, productModuleFields);
                                for (var i = 0; i < $scope.inventoryFields.length; i++) {
                                    if($scope.inventoryFields[i].id == '-1'){
                                        $scope.inventoryFields.splice(i, 1);
                                    }
                                }
                                var module = angular.copy($rootScope.app.data.quoter_settings[$rootScope.app.model.module]);
                                // Final fields
                                $scope.tableBlockTotal = module['final_details'];
                            }

                            // Reset settings
                            $scope.settings.pricing_table.theme = $scope.tableBlockTheme[0];    // Default is first theme
                            $scope.settings.pricing_table.size = $scope.tableBlockSize[1];    // Default is first size
                            $scope.settings.pricing_table.total_fields = [];
                            $scope.settings.pricing_table.total_fields.length = 0;
                            $scope.settings.pricing_table.total_fields_order = [];
                            $scope.settings.pricing_table.total_fields_order.length = 0;
                            $scope.settings.pricing_table.item_fields = [];
                            $scope.settings.pricing_table.item_fields.length = 0;
                            $scope.settings.pricing_table.item_fields_order = [];
                            $scope.settings.pricing_table.item_fields_order.length = 0;
                            $scope.settings.pricing_table.include_sections = false;
                            $scope.settings.pricing_table.include_running_totals = false;
                            var itemFieldOrder = [];
                            var itemFieldTotalOrder =[];

                            // Default selected
                            if (info['settings']) {
                                var selected = null;
                                var selectedItem = null;
                                var inventoryFieldItem = null;

                                // item fields
                                if (info['settings']['item_fields']) {
                                    selected = info['settings']['item_fields'];

                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];

                                        for (var inv = 0; inv < $scope.inventoryFields.length; inv++) {
                                            inventoryFieldItem = $scope.inventoryFields[inv];

                                            if ((inventoryFieldItem.module == selectedItem.module)
                                                && (inventoryFieldItem.block.id == selectedItem.block.id)
                                                && (inventoryFieldItem.id == selectedItem.id)
                                                && (inventoryFieldItem.name == selectedItem.name)) {
                                                $scope.settings.pricing_table.item_fields.push($scope.inventoryFields[inv]);
                                            }
                                        }
                                    }
                                }

                                // total fields
                                if (info['settings']['total_fields']) {
                                    selected = info['settings']['total_fields'];

                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];

                                        for (var inv = 0; inv < $scope.tableBlockTotal.length; inv++) {
                                            inventoryFieldItem = $scope.tableBlockTotal[inv];

                                            if ((inventoryFieldItem.module == selectedItem.module)
                                                && (inventoryFieldItem.block.id == selectedItem.block.id)
                                                && (inventoryFieldItem.id == selectedItem.id)
                                                && (inventoryFieldItem.name == selectedItem.name)) {
                                                $scope.settings.pricing_table.total_fields.push($scope.tableBlockTotal[inv]);
                                            }
                                        }
                                    }
                                }

                                // Theme
                                if (info['settings']['theme']) {
                                    selectedItem = info['settings']['theme'];

                                    for (var inv = 0; inv < $scope.tableBlockTheme.length; inv++) {
                                        inventoryFieldItem = $scope.tableBlockTheme[inv];

                                        if ((inventoryFieldItem.id == selectedItem.id)
                                            && (inventoryFieldItem.name == selectedItem.name)) {
                                            $scope.settings.pricing_table.theme = $scope.tableBlockTheme[inv];
                                            break;
                                        }
                                    }
                                }
                                // Size
                                if (info['settings']['size']) {
                                    selectedItem = info['settings']['size'];

                                    for (var inv = 0; inv < $scope.tableBlockSize.length; inv++) {
                                        inventoryFieldItem = $scope.tableBlockSize[inv];

                                        if ((inventoryFieldItem.id == selectedItem.id)
                                            && (inventoryFieldItem.name == selectedItem.name)) {
                                            $scope.settings.pricing_table.size = $scope.tableBlockSize[inv];
                                            break;
                                        }
                                    }
                                }

                                // order item fields
                                if (info['settings']['item_fields_order']) {
                                    $scope.settings.pricing_table.item_fields_order = info['settings']['item_fields_order'];
                                }

                                // order total fields
                                if (info['settings']['total_fields_order']) {
                                    $scope.settings.pricing_table.total_fields_order = info['settings']['total_fields_order'];
                                }

                                // Include Sections
                                if (info['settings']['include_sections']) {
                                    $scope.settings.pricing_table.include_sections = info['settings']['include_sections'];
                                }

                                // Include Running Totals
                                if (info['settings']['include_running_totals']) {
                                    $scope.settings.pricing_table.include_running_totals = info['settings']['include_running_totals'];
                                }

                            } else {
                                var templateFieldSelected = [];
                                var tbodyContent = objTable.find('tbody > tr.tbody-content');
                                var objFieldContent = tbodyContent.find('td');
                                var objFieldContentItem = null;

                                for (var fct = 0; fct < objFieldContent.length; fct++) {
                                    objFieldContentItem = $(objFieldContent[fct]);
                                    var n= objFieldContentItem.text().indexOf("productid");
                                    var m= objFieldContentItem.text().indexOf("comment");
                                    if(n>=0 && m >=0){
                                        templateFieldSelected.push(objFieldContentItem.text().trim());
                                    }
                                    else{
                                        objFieldContentItem=objFieldContentItem.html().trim().replace('\\$ ','');
                                        if(typeof objFieldContentItem =='string'){
                                            templateFieldSelected.push(objFieldContentItem);
                                        }else {
                                            templateFieldSelected.push(objFieldContentItem.text().trim());
                                        }
                                    }
                                }


                                // item fields
                                var field = null;

                                for (var sf = 0; sf < templateFieldSelected.length; sf++) {
                                    for (var gbf = 0; gbf < $scope.inventoryFields.length; gbf++) {
                                        field = $scope.inventoryFields[gbf];
                                        if (field.token == templateFieldSelected[sf]) {
                                            $scope.settings.pricing_table.item_fields.push($scope.inventoryFields[gbf]);
                                            itemFieldOrder.push(field.label);
                                        }
                                    }
                                }
                                // item total selected
                                var itemTotalSelected = [];
                                var tfooterContent = objTable.find('.item-total');
                                var objFieldContentItemTotal = null;
                                for (var fct = 0; fct < tfooterContent.length; fct++) {
                                    objFieldContentItemTotal = $(tfooterContent[fct]);
                                    var n= objFieldContentItemTotal.text().indexOf("productid");
                                    var m= objFieldContentItemTotal.text().indexOf("comment");
                                    if(n>=0 && m >=0){
                                        itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                    }
                                    else{
                                        objFieldContentItemTotal=objFieldContentItemTotal.html().trim().replace('\\$ ','');
                                        if(typeof objFieldContentItemTotal =='string'){
                                            itemTotalSelected.push(objFieldContentItemTotal);
                                        }else {
                                            itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                        }
                                    }
                                    // itemTotalSelected.push(objFieldContentItemTotal.text().trim());
                                }
                                // item total fields
                                var fieldTotal = null;

                                for (var sf = 0; sf < itemTotalSelected.length; sf++) {
                                    for (var gbf = 0; gbf < $scope.tableBlockTotal.length; gbf++) {
                                        fieldTotal = $scope.tableBlockTotal[gbf];

                                        if (fieldTotal.token == itemTotalSelected[sf]) {
                                            $scope.settings.pricing_table.total_fields.push($scope.tableBlockTotal[gbf]);
                                            itemFieldTotalOrder.push(fieldTotal.label);
                                        }
                                    }
                                }
                            }

                            // Open modal
                            app.showModalWindow(html, '#', function () {
                                var settingsPricingTableFields = html.find('#settings_pricing_table_fields');
                                var settingsPricingTotalFields = html.find('#settings_pricing_total_fields');

                                $timeout(function () {
                                    if (info['settings']) {
                                    var quoting_tool_product_image =editable.find('img.quoting_tool_product_image');
                                    if(quoting_tool_product_image.length > 0){
                                        var html_images_size = '<tr>'+
                                            '<th>Images size</th>' +
                                            '<td>' +
                                            '<input name="product_image_width" value="'+quoting_tool_product_image.width()+'" size="5" style="text-align: center;" /> X <input size="5" name="product_image_height" value="'+quoting_tool_product_image.height()+'"  style="text-align: center;" /> '+
                                            '</td>'+
                                            '</tr>';
                                        $('#settings_pricing_table_fields').closest('tr').after(html_images_size);
                                    }
                                    } else {
                                        // item field
                                        var selectedOptions = $('#settings_pricing_table_fields').find('option:selected');
                                        var rsOrderItem = [];
                                        var order = [];
                                        selectedOptions.each(function () {
                                            var focus = $(this);
                                            var myIndex = focus.index();
                                            order[myIndex] = focus.text();
                                        });
                                        jQuery.each(itemFieldOrder, function (idx, val) {
                                            jQuery.each(order, function (key, label) {
                                                if(val == label) {
                                                    rsOrderItem.push(key);
                                                }
                                            })
                                        });
                                        $scope.settings.pricing_table.item_fields_order = rsOrderItem;

                                        // item total field
                                        selectedOptions = $('#settings_pricing_total_fields').find('option:selected');
                                        rsOrderItem = [];
                                        order = [];
                                        selectedOptions.each(function () {
                                            var focus = $(this);
                                            var myIndex = focus.index();
                                            order[myIndex] = focus.text();
                                        });
                                        jQuery.each(itemFieldTotalOrder, function (idx, val) {
                                            jQuery.each(order, function (key, label) {
                                                if(val == label) {
                                                    rsOrderItem.push(key);
                                                }
                                            })
                                        });
                                        $scope.settings.pricing_table.total_fields_order = rsOrderItem;
                                    }
                                    AppHelper.arrangeSelectChoicesInOrder(settingsPricingTableFields, $scope.settings.pricing_table.item_fields_order);
                                    AppHelper.arrangeSelectChoicesInOrder(settingsPricingTotalFields, $scope.settings.pricing_table.total_fields_order);
                                }, 100);

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    // order item fields
                                    var itemFieldIds = AppHelper.getSelectedColumns(settingsPricingTableFields);
                                    var itemFields = [];
                                    var itemFieldSortedOption = null;
                                    var itemFieldSortedInfo = {};

                                    for (var id = 0; id < itemFieldIds.length; id++) {
                                        itemFieldSortedOption = settingsPricingTableFields.find('option[value="' + itemFieldIds[id] + '"]:selected');
                                        itemFieldSortedInfo = itemFieldSortedOption.data('info');
                                        if(itemFieldSortedInfo.datatype == "image"){
                                            itemFieldSortedInfo.width = html.find('input[name="product_image_width"]').val();
                                            itemFieldSortedInfo.height = html.find('input[name="product_image_height"]').val();
                                        }
                                        itemFields.push(itemFieldSortedInfo);
                                    }

                                    $scope.settings.pricing_table.item_fields_order = itemFieldIds;

                                    // Total field order
                                    var totalFieldIds = AppHelper.getSelectedColumns(settingsPricingTotalFields);
                                    var totalFields = [];
                                    var totalFieldSortedOption = null;
                                    var totalFieldSortedInfo = {};

                                    for (var id = 0; id < totalFieldIds.length; id++) {
                                        totalFieldSortedOption = settingsPricingTotalFields.find('option[value="' + totalFieldIds[id] + '"]:selected');
                                        totalFieldSortedInfo = totalFieldSortedOption.data('info');
                                        totalFields.push(totalFieldSortedInfo);
                                    }

                                    $scope.settings.pricing_table.total_fields_order = totalFieldIds;

                                    var selectedItem = null;
                                    var colHeader = [],
                                        colItem = [],
                                        colFooter = [];

                                    for (var i = 0; i < itemFields.length; i++) {
                                        selectedItem = itemFields[i];
                                        if(selectedItem.label=="Selling Price"){
                                            selectedItem.label="Price";
                                        }
                                        if(selectedItem.label=="Sequence No"){
                                            selectedItem.label="#";
                                        }
                                        if(selectedItem.label=="Item Discount Amount"){
                                            selectedItem.label="Discount";
                                        }
                                        if(selectedItem.label=="Item Discount Percent"){
                                            selectedItem.label="Discount %";
                                        }
                                        if(selectedItem.label=="Item Description"){
                                            selectedItem.label="Description";
                                        }
                                        if(selectedItem.label=="Item Name (with description)"){
                                            selectedItem.label="Item Name ";
                                        }
                                        if(selectedItem.label=='#'){
                                            colHeader.unshift(selectedItem.label);
                                        }else{
                                            colHeader.push(selectedItem.label);
                                        }
                                        if(selectedItem.label=='#'){
                                            colItem.unshift(selectedItem)
                                        }else{
                                            colItem.push(selectedItem);
                                        }
                                    }

                                    for (var i = 0; i < totalFields.length; i++) {
                                        selectedItem = totalFields[i];
                                        colFooter.push({
                                            label: selectedItem.label,
                                            token: selectedItem.token
                                        });
                                    }

                                    // Save settings
                                    info['settings'] = angular.copy($scope.settings.pricing_table);
                                    objTable.attr('data-info', angular.toJson(info));

                                    // Set table style:
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['style']) {
                                        // objTable.css(info['settings']['theme']['settings']['style']);
                                    }
                                    if (info['settings'] && info['settings']['size'] && info['settings']['size']['settings'] && info['settings']['size']['settings']['style']) {
                                        objTable.css(info['settings']['size']['settings']['style']);
                                        objTable.attr('cellpadding', info['settings']['size']['settings']['cellpadding']);
                                    }
                                    // Cell style:
                                    var cellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['cell'] && info['settings']['theme']['settings']['cell']['style']) {
                                        cellStyle = info['settings']['theme']['settings']['cell']['style'];

                                        objTable.find('th, td').css(cellStyle);
                                    }

                                    // Replace table
                                    // header
                                    var thead = objTable.find('thead');

                                    // Set thead style:
                                    var theadCellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['thead'] && info['settings']['theme']['settings']['thead']['style']) {
                                        theadCellStyle = $.extend({}, cellStyle, info['settings']['theme']['settings']['thead']['style']);
                                        thead.find('th, td').css(theadCellStyle);
                                    }
                                    thead.find('th, td').css({
                                        'text-align':'center'
                                    });
                                    var theadRows = thead.find('tr');
                                    var theadRow = null;
                                    var objTheadRow = null;
                                    var firstTheadCell = null;
                                    var newTheadCell = null;

                                    for (var thr = 0; thr < theadRows.length; thr++) {
                                        theadRow = theadRows[thr];
                                        objTheadRow = $(theadRow);
                                        firstTheadCell = objTheadRow.find('th, td').filter(':first');
                                        if (firstTheadCell && firstTheadCell.length > 0) {
                                            firstTheadCell = firstTheadCell.clone();
                                        } else {
                                            firstTheadCell = $('<th/>');
                                        }

                                        objTheadRow.empty();

                                        for (var i = 0; i < colHeader.length; i++) {
                                            newTheadCell = firstTheadCell.clone();
                                            newTheadCell.text(colHeader[i]);
                                            objTheadRow.append(newTheadCell);
                                        }
                                    }

                                    // body
                                    var tbody = objTable.find('tbody');
                                    var arrTr = tbody.find('tr');
                                    var tr = null,
                                        trText = null,
                                        trBlockStart = '',
                                        objTrBlockStart = null,
                                        trBlockEnd = '',
                                        objTrBlockEnd = null,
                                        markCell = null,
                                        objTrBlockItem = [],
                                        isStart = false,
                                        isEnd = false,
                                        oddStyle = {},
                                        evenStyle = {};

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['odd']
                                        && info['settings']['theme']['settings']['tbody']['odd']['style']) {
                                        oddStyle = info['settings']['theme']['settings']['tbody']['odd']['style'];
                                    }

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['even']
                                        && info['settings']['theme']['settings']['tbody']['even']['style']) {
                                        evenStyle = info['settings']['theme']['settings']['tbody']['even']['style'];
                                    }

                                    tbody.addAttributes({
                                        'data-odd-style': JSON.stringify(oddStyle),
                                        'data-even-style': JSON.stringify(evenStyle)
                                    });

                                    for (var i = 0; i < arrTr.length; i++) {
                                        tr = $(arrTr[i]);
                                        trText = tr.text();
                                        trText = trText ? trText.trim() : '';

                                        if (trText == '#PRODUCTBLOC_START#') {
                                            objTrBlockStart = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockStart = tr[0].outerHTML;
                                            isStart = true;
                                            continue;
                                        }

                                        if (trText && trText.trim() == '#PRODUCTBLOC_END#') {
                                            objTrBlockEnd = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockEnd = tr[0].outerHTML;
                                            isEnd = true;
                                            continue;
                                        }

                                        // item row
                                        if (isStart && !isEnd) {
                                            objTrBlockItem.push(tr);
                                        }
                                    }

                                    var tbodyRow = null;
                                    var objTbodyRow = null;
                                    var firstTbodyCell = null;
                                    var newTbodyCell = null;
                                    var arrNumberTypes = ['currency', 'double', 'integer', 'float'];

                                    for (var tbr = 0; tbr < objTrBlockItem.length; tbr++) {
                                        tbodyRow = objTrBlockItem[tbr];
                                        objTbodyRow = $(tbodyRow);
                                        firstTbodyCell = objTbodyRow.find('th, td').filter(':first');
                                        if (firstTbodyCell && firstTbodyCell.length > 0) {
                                            firstTbodyCell = firstTbodyCell.clone();
                                        } else {
                                            firstTbodyCell = $('<td/>');
                                        }

                                        objTbodyRow.empty();

                                        for (var i = 0; i < colItem.length; i++) {
                                            newTbodyCell = firstTbodyCell.clone();
                                            if (colItem[i].datatype == "image") {
                                                var right_btn = "<div style='float: left;'><img data-fieldname = '"+colItem[i].token+"' data-productid = '$Products__product_no$' class='quoting_tool-cke-keep-element quoting_tool_product_image' src='modules/QuotingTool/resources/images/placeholder-image.png' alt=''   style='width: "+colItem[i].width+"px;height:"+colItem[i].height+"px;cursor: pointer;'></div>";
                                                right_btn += "<div style='float: left;cursor: pointer;' class='doc-block__control btn--block-control quoting_tool-btn-options-product-image'>" +
                                                    "<div class='p-image'></div>" +
                                                    "</div>";
                                                newTbodyCell.html(right_btn);
                                            }
                                           else if(colItem[i].token=="$VTEItems__cf_quotes_currency$" ||
                                                colItem[i].token=="$VTEItems__net_price$" ||
                                                colItem[i].token=="$VTEItems__listprice$" ||
                                                colItem[i].token=="$VTEItems__tax_total$" ||
                                                colItem[i].token=="$VTEItems__tax_totalamount$" ||
                                                colItem[i].token=="$VTEItems__discount_amount$" ||
                                                colItem[i].token=="$VTEItems__total$"
                                            ){
                                                newTbodyCell.html("\\$ "+colItem[i].token)
                                            }else if(colItem[i].datatype=='currency')
                                            {
                                                newTbodyCell.html("\\$ "+colItem[i].token)
                                            }else if(colItem[i].label=="Item Name "){
                                                newTbodyCell.html("<b>$VTEItems__productid$</b><br>$VTEItems__comment$");
                                            }
                                                else newTbodyCell.text(colItem[i].token);
                                            if ($.inArray(colItem[i].datatype, arrNumberTypes) >= 0) {
                                                newTbodyCell.css({
                                                    'text-align': 'right'
                                                })
                                            }
                                            newTbodyCell.css({
                                                'text-align': 'right'
                                            });
                                            if(colItem[i].label=="#"){
                                                newTbodyCell.css({
                                                    'text-align': 'center'
                                                })
                                            }
                                            if(colItem[i].label=="Item Name " || colItem[i].label=="Item Name"|| colItem[i].label=="Description"){
                                                newTbodyCell.css({
                                                    'text-align': 'left'
                                                })
                                            }
                                            if(colItem[i].token=='$VTEItems__tax_total$'){
                                                newTbodyCell.attr('data-info','isTax');
                                            }else{
                                                newTbodyCell.removeAttr('data-info');
                                            }
                                            newTbodyCell.attr('valign','top');
                                            objTbodyRow.append(newTbodyCell);

                                            if(i==(colItem.length-1)){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"12%");
                                            } else if(colItem[i].label=="Item Name "){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                                objTbodyRow.find('td').slice(i,i+1).css({
                                                    'min-width':"37%",
                                                })
                                            }else if(colItem[i].label=="Item Name"){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"15%");
                                            }else if(colItem[i].label=="#"){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"4%");
                                            }else if(colItem[i].label=="Description"){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                            }else if(colItem[i].label=="Quantity"){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"8%");
                                            }else if(colItem[i].label=="Net Price" || colItem[i].label=="Tax" || colItem[i].label=="Price" || colItem[i].label=="Discount" ){
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"13%");
                                            } else {
                                                objTbodyRow.find('td').slice(i,i+1).attr('width',"");
                                            }
                                        }
                                    }
                                    var indexOfTax=objTable.find('[data-info="isTax"]').index();
                                    objTable.find('thead tr').find('th').removeAttr('data-info');
                                    objTable.find('thead tr').find('th').slice(indexOfTax,indexOfTax+1).attr('data-info','isTax');
                                    // footer
                                    var tfoot = objTable.find('tfoot');
                                    var footerColspan1 = colHeader.length - 1;
                                    var tfootRow = null;
                                    var newFootRow = null;
                                    var newFootCell = null;

                                    var objFootRow = tfoot.find('tr:first');
                                    if (tfootRow && tfootRow.length > 0) {
                                        tfootRow.clone();
                                    } else {
                                        tfootRow = $('<tr/>');
                                    }
                                    var firstFootCell = objFootRow.find('th, td').filter(':first');
                                    if (firstFootCell && firstFootCell.length > 0) {
                                        firstFootCell = firstFootCell.clone();
                                    } else {
                                        firstFootCell = $('<td/>');
                                    }

                                    // Empty tfoot
                                    tfoot.empty();

                                    for (var i = 0; i < colFooter.length; i++) {
                                        selectedItem = colFooter[i];
                                        newFootRow = tfootRow.clone();
                                        newFootRow.empty();
                                        // Label
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.attr('colspan', footerColspan1);
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.label);
                                        newFootRow.append(newFootCell);
                                        // Value
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.removeAttr('colspan');
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        // newFootCell.text(selectedItem.token);
                                        newFootCell.html("\\$ "+selectedItem.token);
                                        newFootRow.append(newFootCell);
                                        newFootRow.attr('valign','top');
                                        newFootRow.find('td:last').attr('width','12%');
                                        tfoot.append(newFootRow);
                                    }

                                    // Close modal
                                    app.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.related_module:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.data('info');
                            if (typeof info === 'undefined') {
                                info = {};
                            }


                            // Reset settings
                            $scope.settings.related_module.theme = $scope.tableBlockTheme[0];    // Default is first theme
                            $scope.settings.related_module.size = $scope.tableBlockSize[1];    // Default is first theme
                            $scope.settings.related_module.item_fields = [];
                            $scope.settings.related_module.item_fields.length = 0;
                            $scope.settings.related_module.link_module= [];
                            $scope.settings.related_module.link_module.length = 0;
                            // $scope.settings.related_module.link_module = [];
                            // $scope.settings.related_module.link_module.length = 0;
                            $scope.settings.related_module.item_fields_order = [];
                            $scope.settings.related_module.item_fields_order.length = 0;
                            var itemFieldOrder = [];




                            // Default selected
                            if (info['settings']) {
                                var selected = null;
                                var selectedItem = null;
                                var selectedLinkModule = null;
                                var linkModuleFieldItem = null;
                                var linkModuleItem = null;

                                // link module
                                if (info['settings']['link_module']) {
                                    selectedLinkModule  = info['settings']['link_module'];
                                    // $scope.settings.related_module.link_module = info['settings']['link_module'];
                                    jQuery.each($rootScope.app.data.selectedModule.link_modules, function (i, val) {
                                        if(selectedLinkModule == val["name"]) {
                                            $scope.settings.related_module.link_module = $rootScope.app.data.selectedModule.link_modules[i];
                                        }
                                    });
                                }
                                // item fields
                                if (info['settings']['item_fields']) {
                                    selected = info['settings']['item_fields'];

                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];

                                        for (var inv = 0; inv < $scope.settings.related_module.link_module.fields.length; inv++) {
                                            linkModuleFieldItem = $scope.settings.related_module.link_module.fields[inv];
                                            if ((linkModuleFieldItem.block.id == selectedItem.block.id)
                                                && (linkModuleFieldItem.id == selectedItem.id)
                                                && (linkModuleFieldItem.name == selectedItem.name)) {
                                                $scope.settings.related_module.item_fields.push($scope.settings.related_module.link_module.fields[inv]);
                                            }
                                        }
                                    }
                                }

                                // Theme
                                if (info['settings']['theme']) {
                                    selectedItem = info['settings']['theme'];

                                    for (var inv = 0; inv < $scope.tableBlockTheme.length; inv++) {
                                        linkModuleFieldItem = $scope.tableBlockTheme[inv];

                                        if ((linkModuleFieldItem.id == selectedItem.id)
                                            && (linkModuleFieldItem.name == selectedItem.name)) {
                                            $scope.settings.related_module.theme = $scope.tableBlockTheme[inv];
                                            break;
                                        }
                                    }
                                }
                                // Size
                                if (info['settings']['size']) {
                                    selectedItem = info['settings']['size'];

                                    for (var inv = 0; inv < $scope.tableBlockSize.length; inv++) {
                                        var related_module_size = $scope.tableBlockSize[inv];

                                        if ((related_module_size.id == selectedItem.id)
                                            && (related_module_size.name == selectedItem.name)) {
                                            $scope.settings.related_module.size = $scope.tableBlockSize[inv];
                                            break;
                                        }
                                    }
                                }

                                // order item fields
                                if (info['settings']['item_fields_order']) {
                                    $scope.settings.related_module.item_fields_order = info['settings']['item_fields_order'];
                                }


                            } else {
                                // default selected
                                $scope.settings.related_module.link_module = $rootScope.app.data.selectedModule.link_modules[0];
                                // var templateFieldSelected = [];
                                // var tbodyContent = objTable.find('tbody > tr.tbody-content');
                                // var objFieldContent = tbodyContent.find('td');
                                // var objFieldContentItem = null;
                                //
                                // for (var fct = 0; fct < objFieldContent.length; fct++) {
                                //     objFieldContentItem = $(objFieldContent[fct]);
                                //     templateFieldSelected.push(objFieldContentItem.text().trim());
                                // }
                                //
                                //
                                // // item fields
                                // var field = null;
                                //
                                // for (var sf = 0; sf < templateFieldSelected.length; sf++) {
                                //     for (var gbf = 0; gbf < $scope.settings.related_module.link_module.fields.length; gbf++) {
                                //         field = $scope.settings.related_module.link_module.fields[gbf];
                                //         if (field.token == templateFieldSelected[sf]) {
                                //             $scope.settings.related_module.item_fields.push($scope.settings.related_module.link_module.fields[gbf]);
                                //             itemFieldOrder.push(field.label);
                                //         }
                                //     }
                                // }
                            }

                            // Open modal
                            app.showModalWindow(html, '#', function () {
                                var settingsRelatedModuleFields = html.find('#settings_related_module_fields');
                                var settingsLinkModule = html.find('#settings_link_modules');

                                // // item field
                                // var selectedOptions = $('#settings_related_module_fields').find('option:selected');
                                // var rsOrderItem = [];
                                // var order = [];
                                // selectedOptions.each(function () {
                                //     var focus = $(this);
                                //     var myIndex = focus.index();
                                //     order[myIndex] = focus.text();
                                // });
                                // jQuery.each(itemFieldOrder, function (idx, val) {
                                //     jQuery.each(order, function (key, label) {
                                //         if(val == label) {
                                //             rsOrderItem.push(key);
                                //         }
                                //     })
                                // })
                                // $scope.settings.related_module.item_fields_order = rsOrderItem;

                                $timeout(function () {
                                    AppHelper.arrangeSelectChoicesInOrder(settingsRelatedModuleFields, $scope.settings.related_module.item_fields_order);
                                }, 100);

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    // order item fields
                                    var itemFieldIds = AppHelper.getSelectedColumns(settingsRelatedModuleFields);
                                    var itemFields = [];
                                    var itemFieldSortedOption = null;
                                    var itemFieldSortedInfo = {};

                                    for (var id = 0; id < itemFieldIds.length; id++) {
                                        itemFieldSortedOption = settingsRelatedModuleFields.find('option[value="' + itemFieldIds[id] + '"]:selected');
                                        itemFieldSortedInfo = itemFieldSortedOption.data('info');
                                        itemFields.push(itemFieldSortedInfo);
                                    }

                                    $scope.settings.related_module.item_fields_order = itemFieldIds;

                                    var selectedItem = null;
                                    var colHeader = [],
                                        colItem = [],
                                        colFooter = [];

                                    for (var i = 0; i < itemFields.length; i++) {
                                        selectedItem = itemFields[i];
                                        colHeader.push(selectedItem.label);
                                        colItem.push(selectedItem);
                                    }
                                    // Save settings
                                    $scope.settings.related_module.link_module = $scope.settings.related_module.link_module.name;
                                    info['settings'] = angular.copy($scope.settings.related_module);
                                    objTable.attr('data-info', angular.toJson(info));

                                    // Set table style:
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['style']) {
                                        // objTable.css(info['settings']['theme']['settings']['style']);
                                    }
                                    if (info['settings'] && info['settings']['size'] && info['settings']['size']['settings'] && info['settings']['size']['settings']['style']) {
                                        objTable.css(info['settings']['size']['settings']['style']);
                                        objTable.attr('cellpadding', info['settings']['size']['settings']['cellpadding']);
                                    }
                                    // Cell style:
                                    var cellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['cell'] && info['settings']['theme']['settings']['cell']['style']) {
                                        cellStyle = info['settings']['theme']['settings']['cell']['style'];

                                        objTable.find('th, td').css(cellStyle);
                                    }

                                    // Replace table
                                    // header
                                    var thead = objTable.find('thead');

                                    // Set thead style:
                                    var theadCellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['thead'] && info['settings']['theme']['settings']['thead']['style']) {
                                        theadCellStyle = $.extend({}, cellStyle, info['settings']['theme']['settings']['thead']['style']);
                                        thead.find('th, td').css(theadCellStyle);
                                        thead.find('th').css({'text-align':'left'});
                                    }

                                    var theadRows = thead.find('tr');
                                    var theadRow = null;
                                    var objTheadRow = null;
                                    var firstTheadCell = null;
                                    var newTheadCell = null;

                                    for (var thr = 0; thr < theadRows.length; thr++) {
                                        theadRow = theadRows[thr];
                                        objTheadRow = $(theadRow);
                                        firstTheadCell = objTheadRow.find('th, td').filter(':first');
                                        if (firstTheadCell && firstTheadCell.length > 0) {
                                            firstTheadCell = firstTheadCell.clone();
                                        } else {
                                            firstTheadCell = $('<th/>');
                                        }

                                        objTheadRow.empty();

                                        for (var i = 0; i < colHeader.length; i++) {
                                            newTheadCell = firstTheadCell.clone();
                                            newTheadCell.text(colHeader[i]);
                                            objTheadRow.append(newTheadCell);
                                        }
                                    }

                                    // body
                                    var tbody = objTable.find('tbody');
                                    var arrTr = tbody.find('tr');
                                    var tr = null,
                                        trText = null,
                                        trBlockStart = '',
                                        objTrBlockStart = null,
                                        trBlockEnd = '',
                                        objTrBlockEnd = null,
                                        markCell = null,
                                        objTrBlockItem = [],
                                        isStart = false,
                                        isEnd = false,
                                        oddStyle = {},
                                        evenStyle = {};

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['odd']
                                        && info['settings']['theme']['settings']['tbody']['odd']['style']) {
                                        oddStyle = info['settings']['theme']['settings']['tbody']['odd']['style'];
                                    }

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['even']
                                        && info['settings']['theme']['settings']['tbody']['even']['style']) {
                                        evenStyle = info['settings']['theme']['settings']['tbody']['even']['style'];
                                    }

                                    tbody.addAttributes({
                                        'data-odd-style': JSON.stringify(oddStyle),
                                        'data-even-style': JSON.stringify(evenStyle)
                                    });

                                    for (var i = 0; i < arrTr.length; i++) {
                                        tr = $(arrTr[i]);
                                        trText = tr.text();
                                        trText = trText ? trText.trim() : '';

                                        if (trText == '#RELATEDBLOCK_START#') {
                                            objTrBlockStart = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockStart = tr[0].outerHTML;
                                            isStart = true;
                                            continue;
                                        }

                                        if (trText && trText.trim() == '#RELATEDBLOCK_END#') {
                                            objTrBlockEnd = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockEnd = tr[0].outerHTML;
                                            isEnd = true;
                                            continue;
                                        }

                                        // item row
                                        if (isStart && !isEnd) {
                                            objTrBlockItem.push(tr);
                                        }
                                    }

                                    var tbodyRow = null;
                                    var objTbodyRow = null;
                                    var firstTbodyCell = null;
                                    var newTbodyCell = null;
                                    var arrNumberTypes = ['currency', 'double', 'integer', 'float'];

                                    for (var tbr = 0; tbr < objTrBlockItem.length; tbr++) {
                                        tbodyRow = objTrBlockItem[tbr];
                                        objTbodyRow = $(tbodyRow);
                                        firstTbodyCell = objTbodyRow.find('th, td').filter(':first');
                                        if (firstTbodyCell && firstTbodyCell.length > 0) {
                                            firstTbodyCell = firstTbodyCell.clone();
                                        } else {
                                            firstTbodyCell = $('<td/>');
                                        }

                                        objTbodyRow.empty();

                                        for (var i = 0; i < colItem.length; i++) {
                                            newTbodyCell = firstTbodyCell.clone();
                                            newTbodyCell.text(colItem[i].token);
                                            if ($.inArray(colItem[i].datatype, arrNumberTypes) >= 0) {
                                                newTbodyCell.css({
                                                    'text-align': 'right'
                                                })
                                            }

                                            objTbodyRow.append(newTbodyCell);
                                        }
                                    }

                                    // footer
                                    var tfoot = objTable.find('tfoot');
                                    var footerColspan1 = colHeader.length - 1;
                                    var tfootRow = null;
                                    var newFootRow = null;
                                    var newFootCell = null;

                                    var objFootRow = tfoot.find('tr:first');
                                    if (tfootRow && tfootRow.length > 0) {
                                        tfootRow.clone();
                                    } else {
                                        tfootRow = $('<tr/>');
                                    }

                                    var firstFootCell = objFootRow.find('th, td').filter(':first');
                                    if (firstFootCell && firstFootCell.length > 0) {
                                        firstFootCell = firstFootCell.clone();
                                    } else {
                                        firstFootCell = $('<td/>');
                                    }

                                    // Empty tfoot
                                    tfoot.empty();

                                    for (var i = 0; i < colFooter.length; i++) {
                                        selectedItem = colFooter[i];
                                        newFootRow = tfootRow.clone();
                                        newFootRow.empty();
                                        // Label
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.attr('colspan', footerColspan1);
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.label);
                                        newFootRow.append(newFootCell);
                                        // Value
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.removeAttr('colspan');
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.token);
                                        newFootRow.append(newFootCell);

                                        tfoot.append(newFootRow);
                                    }

                                    // Close modal
                                    app.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.create_related_record:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.data('info');
                            if (typeof info === 'undefined') {
                                info = {};
                            }


                            // Reset settings
                            $scope.settings.create_related_record.size = $scope.tableBlockSize[1];
                            $scope.settings.create_related_record.theme = $scope.tableBlockTheme[0];    // Default is first theme
                            $scope.settings.create_related_record.item_fields = [];
                            $scope.settings.create_related_record.item_fields.length = 0;
                            $scope.settings.create_related_record.link_module= [];
                            $scope.settings.create_related_record.link_module.length = 0;
                            // $scope.settings.create_related_record.link_module = [];
                            // $scope.settings.create_related_record.link_module.length = 0;
                            $scope.settings.create_related_record.item_fields_order = [];
                            $scope.settings.create_related_record.item_fields_order.length = 0;
                            var itemFieldOrder = [];




                            // Default selected
                            if (info['settings']) {
                                var selected = null;
                                var selectedItem = null;
                                var selectedLinkModule = null;
                                var linkModuleFieldItem = null;
                                var linkModuleItem = null;

                                // link module



                                if (info['settings']['link_module']) {
                                    var oldSlectedModule= [];
                                    selectedLinkModule  = info['settings']['link_module'];
                                    // $scope.settings.create_related_record.link_module = info['settings']['link_module'];
                                    jQuery.each($rootScope.app.data.selectedModule.create_related_record, function (i, val) {
                                        if (selectedLinkModule == val["name"]) {
                                            $scope.settings.create_related_record.link_module = $rootScope.app.data.selectedModule.create_related_record[i];
                                        }
                                    });
                                }
                                // item fields
                                if (info['settings']['item_fields']) {
                                    selected = info['settings']['item_fields'];

                                    for (var sf = 0; sf < selected.length; sf++) {
                                        selectedItem = selected[sf];
                                        if ($scope.settings.create_related_record.link_module.fields) {
                                            for (var inv = 0; inv < $scope.settings.create_related_record.link_module.fields.length; inv++) {
                                                linkModuleFieldItem = $scope.settings.create_related_record.link_module.fields[inv];
                                                if ((linkModuleFieldItem.block.id == selectedItem.block.id)
                                                    && (linkModuleFieldItem.id == selectedItem.id)
                                                    && (linkModuleFieldItem.name == selectedItem.name)) {
                                                    $scope.settings.create_related_record.item_fields.push($scope.settings.create_related_record.link_module.fields[inv]);
                                                }
                                            }
                                        }
                                    }
                                }

                                // Theme
                                if (info['settings']['theme']) {
                                    selectedItem = info['settings']['theme'];

                                    for (var inv = 0; inv < $scope.tableBlockTheme.length; inv++) {
                                        linkModuleFieldItem = $scope.tableBlockTheme[inv];

                                        if ((linkModuleFieldItem.id == selectedItem.id)
                                            && (linkModuleFieldItem.name == selectedItem.name)) {
                                            $scope.settings.create_related_record.theme = $scope.tableBlockTheme[inv];
                                            break;
                                        }
                                    }
                                }
                                if (info['settings']['size']) {
                                    selectedItem = info['settings']['size'];

                                    for (var inv = 0; inv < $scope.tableBlockSize.length; inv++) {
                                        var related_module_size = $scope.tableBlockSize[inv];

                                        if ((related_module_size.id == selectedItem.id)
                                            && (related_module_size.name == selectedItem.name)) {
                                            $scope.settings.create_related_record.size = $scope.tableBlockSize[inv];
                                            break;
                                        }
                                    }
                                }
                                // Require at least one row
                                html.find('[name="require_data"]').prop({
                                    'checked': info['require_data'] ? info['require_data'] : false
                                });
                                // order item fields
                                if (info['settings']['item_fields_order']) {
                                    $scope.settings.create_related_record.item_fields_order = info['settings']['item_fields_order'];
                                }

                                // conditions
                                if (info['settings']['conditions']) {
                                    $scope.settings.create_related_record.conditions = info['settings']['conditions'];
                                }


                            } else {
                                $scope.settings.create_related_record.link_module = $rootScope.app.data.selectedModule.create_related_record[0];
                            }

                            // Open modal
                            AppHelper.showModalWindow(html, '#', function () {
                                $("#popup_settings_create_related_record").css("width", "auto");
                                var settingsRelatedModuleFields = html.find('#settings_create_related_record_fields');
                                var settingsLinkModule = html.find('#settings_link_modules');

                                // $scope.changeRelatedModuleRecord();
                                if(!info['settings']) {
                                    var selectedOptions = $('#settings_create_related_record_fields').find('option:selected');
                                    var rsOrderItem = [];
                                    var order = [];
                                    selectedOptions.each(function () {
                                        var focus = $(this);
                                        var myIndex = focus.index();
                                        order[myIndex] = focus.text();
                                    });
                                    jQuery.each(itemFieldOrder, function (idx, val) {
                                        jQuery.each(order, function (key, label) {
                                            if (val == label) {
                                                rsOrderItem.push(key);
                                            }
                                        })
                                    });
                                    $scope.settings.create_related_record.item_fields_order = rsOrderItem;
                                }

                                var myVar = setInterval(function(){
                                    AppHelper.arrangeSelectChoicesInOrder(settingsRelatedModuleFields, $scope.settings.create_related_record.item_fields_order);
                                    clearInterval(myVar);
                                }, 100);

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    // order item fields
                                    settingsRelatedModuleFields.find('option[selected="selected"]').each(function () {
                                        $(this).prop('selected',true);
                                    });
                                    var itemFieldIds = AppHelper.getSelectedColumns(settingsRelatedModuleFields);
                                    var itemFields = [];
                                    var itemFieldSortedInfo = {};
                                    var itemFieldSortedOption=null;
                                    for (var id = 0; id < itemFieldIds.length; id++) {
                                        itemFieldSortedOption = settingsRelatedModuleFields.find('option[value="' + itemFieldIds[id] + '"]:selected');
                                        itemFieldSortedInfo = itemFieldSortedOption.data('info');
                                        itemFields.push(itemFieldSortedInfo);
                                    }
                                    $scope.settings.create_related_record.item_fields = itemFields;
                                    $scope.settings.create_related_record.item_fields_order = itemFieldIds;

                                    var selectedItem = null;
                                    var colHeader = [],
                                        colItem = [],
                                        colFooter = [];

                                    for (var i = 0; i < itemFields.length; i++) {
                                        selectedItem = itemFields[i];
                                        colHeader.push(selectedItem.label);
                                        colItem.push(selectedItem);
                                    }

                                    var require_data = html.find('[name="require_data"]');
                                    info['require_data'] = require_data.is(':checked');
                                    info['related_module_label'] = $scope.settings.create_related_record.link_module.label;

                                    // Save settings
                                    $scope.settings.create_related_record.link_module = $scope.settings.create_related_record.link_module.name;
                                    info['settings'] = angular.copy($scope.settings.create_related_record);

                                    objTable.attr('data-info', angular.toJson(info));

                                    // Set table style:
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['style']) {
                                        // objTable.css(info['settings']['theme']['settings']['style']);
                                    }

                                    // Cell style:
                                    var cellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['cell'] && info['settings']['theme']['settings']['cell']['style']) {
                                        cellStyle = info['settings']['theme']['settings']['cell']['style'];

                                        objTable.find('th, td').css(cellStyle);
                                    }

                                    // Replace table
                                    // header
                                    var thead = objTable.find('thead');

                                    // Set thead style:
                                    var theadCellStyle = {};
                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['thead'] && info['settings']['theme']['settings']['thead']['style']) {
                                        theadCellStyle = $.extend({}, cellStyle, info['settings']['theme']['settings']['thead']['style']);
                                        thead.find('th, td').css(theadCellStyle);
                                    }

                                    var theadRows = thead.find('tr');
                                    var theadRow = null;
                                    var objTheadRow = null;
                                    var firstTheadCell = null;
                                    var newTheadCell = null;

                                    for (var thr = 0; thr < theadRows.length; thr++) {
                                        theadRow = theadRows[thr];
                                        objTheadRow = $(theadRow);
                                        firstTheadCell = objTheadRow.find('th, td').filter(':first');
                                        if (firstTheadCell && firstTheadCell.length > 0) {
                                            firstTheadCell = firstTheadCell.clone();
                                        } else {
                                            firstTheadCell = $('<th/>');
                                        }

                                        objTheadRow.empty();

                                        for (var i = 0; i < colHeader.length; i++) {
                                            newTheadCell = firstTheadCell.clone();
                                            newTheadCell.text(colHeader[i]);
                                            objTheadRow.append(newTheadCell);
                                        }
                                    }

                                    // body
                                    var tbody = objTable.find('tbody');
                                    var arrTr = tbody.find('tr');
                                    var tr = null,
                                        trText = null,
                                        trBlockStart = '',
                                        objTrBlockStart = null,
                                        trBlockEnd = '',
                                        objTrBlockEnd = null,
                                        markCell = null,
                                        objTrBlockItem = [],
                                        isStart = false,
                                        isEnd = false,
                                        oddStyle = {},
                                        evenStyle = {};

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['odd']
                                        && info['settings']['theme']['settings']['tbody']['odd']['style']) {
                                        oddStyle = info['settings']['theme']['settings']['tbody']['odd']['style'];
                                    }

                                    if (info['settings'] && info['settings']['theme'] && info['settings']['theme']['settings']
                                        && info['settings']['theme']['settings']['tbody'] && info['settings']['theme']['settings']['tbody']['even']
                                        && info['settings']['theme']['settings']['tbody']['even']['style']) {
                                        evenStyle = info['settings']['theme']['settings']['tbody']['even']['style'];
                                    }
                                    if (info['settings'] && info['settings']['size'] && info['settings']['size']['settings'] && info['settings']['size']['settings']['style']) {
                                        objTable.css(info['settings']['size']['settings']['style']);
                                        objTable.attr('cellpadding', info['settings']['size']['settings']['cellpadding']);
                                    }
                                    tbody.addAttributes({
                                        'data-odd-style': JSON.stringify(oddStyle),
                                        'data-even-style': JSON.stringify(evenStyle)
                                    });

                                    for (var i = 0; i < arrTr.length; i++) {
                                        tr = $(arrTr[i]);
                                        trText = tr.text();
                                        trText = trText ? trText.trim() : '';

                                        if (trText == '#RELATEDBLOCK_START#') {
                                            objTrBlockStart = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockStart = tr[0].outerHTML;
                                            isStart = true;
                                            continue;
                                        }

                                        if (trText && trText.trim() == '#RELATEDBLOCK_END#') {
                                            objTrBlockEnd = tr;
                                            markCell = tr.find('td:contains(' + trText + ')');
                                            markCell.attr('colspan', (colHeader.length));
                                            markCell.css(oddStyle);
                                            trBlockEnd = tr[0].outerHTML;
                                            isEnd = true;
                                            continue;
                                        }

                                        // item row
                                        if (isStart && !isEnd) {
                                            objTrBlockItem.push(tr);
                                        }
                                    }

                                    var tbodyRow = null;
                                    var objTbodyRow = null;
                                    var firstTbodyCell = null;
                                    var newTbodyCell = null;
                                    var arrNumberTypes = ['currency', 'double', 'integer', 'float'];

                                    for (var tbr = 0; tbr < objTrBlockItem.length; tbr++) {
                                        tbodyRow = objTrBlockItem[tbr];
                                        objTbodyRow = $(tbodyRow);
                                        firstTbodyCell = objTbodyRow.find('th, td').filter(':first');
                                        if (firstTbodyCell && firstTbodyCell.length > 0) {
                                            firstTbodyCell = firstTbodyCell.clone();
                                        } else {
                                            firstTbodyCell = $('<td/>');
                                        }

                                        objTbodyRow.empty();

                                        for (var i = 0; i < colItem.length; i++) {
                                            newTbodyCell = firstTbodyCell.clone();
                                            newTbodyCell.text(colItem[i].token);
                                            if ($.inArray(colItem[i].datatype, arrNumberTypes) >= 0) {
                                                newTbodyCell.css({
                                                    'text-align': 'right'
                                                })
                                            }
                                            if (colItem[i].uitype== 56 || colItem[i].uitype== 156) {
                                                newTbodyCell.css({
                                                    'text-align': 'center'
                                                })
                                            }
                                            objTbodyRow.append(newTbodyCell);
                                        }
                                    }

                                    // footer
                                    var tfoot = objTable.find('tfoot');
                                    var footerColspan1 = colHeader.length - 1;
                                    var tfootRow = null;
                                    var newFootRow = null;
                                    var newFootCell = null;

                                    var objFootRow = tfoot.find('tr:first');
                                    if (tfootRow && tfootRow.length > 0) {
                                        tfootRow.clone();
                                    } else {
                                        tfootRow = $('<tr/>');
                                    }

                                    var firstFootCell = objFootRow.find('th, td').filter(':first');
                                    if (firstFootCell && firstFootCell.length > 0) {
                                        firstFootCell = firstFootCell.clone();
                                    } else {
                                        firstFootCell = $('<td/>');
                                    }

                                    // Empty tfoot
                                    tfoot.empty();

                                    for (var i = 0; i < colFooter.length; i++) {
                                        selectedItem = colFooter[i];
                                        newFootRow = tfootRow.clone();
                                        newFootRow.empty();
                                        // Label
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.attr('colspan', footerColspan1);
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.label);
                                        newFootRow.append(newFootCell);
                                        // Value
                                        newFootCell = firstFootCell.clone();
                                        newFootCell.removeAttr('colspan');
                                        newFootCell.css({
                                            'text-align': 'right'
                                        });
                                        newFootCell.text(selectedItem.token);
                                        newFootRow.append(newFootCell);

                                        tfoot.append(newFootRow);
                                    }

                                    // Close modal
                                    AppHelper.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.image:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var contenteditableId = contenteditable.attr('id');

                            var objTextField = container.find('img');
                            var info = objTextField.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            html.find('[name="edge_to_edge"]').prop({
                                'checked': info['edge_to_edge'] ? info['edge_to_edge'] : false
                            });

                            html.find('[name="flatten"]').prop({
                                'checked': info['flatten'] ? info['flatten'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    if (typeof info['edge_to_edge'] === 'undefined') {
                                        // get original width before change
                                        info['original_width'] = objTextField[0].style.width;
                                        info['original_height'] = objTextField[0].style.height;
                                        info['height'] = objTextField.height();
                                    }

                                    // feature with flatten
                                    var sortable = container.find('.doc-block__control--drag');
                                    var editor = CKEDITOR.instances[contenteditableId];
                                    var myEditor = {editor: editor};
                                    var imageContextMenu = myEditor.editor._.menuItems.image;

                                    if (typeof info['flatten'] === 'undefined') {
                                        // get original image context menu before change
                                        var contextmenu_image = jQuery.extend({}, imageContextMenu);
                                        delete contextmenu_image.editor;
                                        contextmenu_image.group = 'image';
                                        info['contextmenu_image'] = contextmenu_image;
                                    }

                                    var edge_to_edge = html.find('[name="edge_to_edge"]');
                                    info['edge_to_edge'] = edge_to_edge.is(':checked');
                                    var flatten = html.find('[name="flatten"]');
                                    info['flatten'] = flatten.is(':checked');

                                    // set width for image
                                    if (info['edge_to_edge']) {
                                        objTextField.css({
                                            'width': '798px',
                                            'height': info['height'],
                                            'margin-left': '-60px',
                                            'margin-right': '-60px'
                                        });
                                    } else {
                                        objTextField.css({
                                            'width': (info['original_width']) ? info['original_width'] : '100%',
                                            'height': (info['original_height']) ? info['original_height'] : '',
                                            'margin-left': '',
                                            'margin-right': ''
                                        });
                                    }

                                    if (info['flatten']) {
                                        sortable.addClass('hide');
                                        var itemsToRemove = ['image'];
                                        $rootScope.customCKEditorContextMenu(myEditor, null, itemsToRemove);
                                    } else {
                                        sortable.removeClass('hide');
                                        var itemsToAdd = [
                                            {
                                                name: 'image',
                                                options: info['contextmenu_image']
                                            }
                                        ];
                                        $rootScope.customCKEditorContextMenu(myEditor, itemsToAdd);
                                    }

                                    // Save settings
                                    objTextField.attr('data-info', JSON.stringify(info));

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.widgets.field_image:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            // Before open modal
                            var objImgField = container.find('img');
                            var info = objImgField.data('info');
                            if (typeof info === 'undefined') {
                                info = {};
                            }
                            //html.find('#settings_field_image').val(info['settings_field_image']);
                            //html.find('#settings_field_image_fields').val(info['settings_field_image_fields']);
                            //html.find('#settings_field_image_fields').trigger('change');
                            $rootScope.selectedModuleFieldImage= info['settings_field_image'];
                            $rootScope.selectedFieldImage= info['settings_field_image_fields'];
                            $rootScope.field_image_selected = {};
                            for (var i = 0; i < $rootScope.app.data.selectedModule.fields.length; i++) {
                                if($rootScope.app.data.selectedModule.fields[i].id == info['settings_field_image_fields']){
                                    $rootScope.field_image_selected = $rootScope.app.data.selectedModule.fields[i];
                                }
                            }
                            //console.log( $rootScope.field_image_selected);
                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    if (typeof info['settings_field_image'] === 'undefined') {
                                        // get original width before change
                                        info['original_width'] = objImgField[0].style.width;
                                        info['original_height'] = objImgField[0].style.height;
                                        info['height'] = objImgField.height();
                                        info['settings_field_image'] = objImgField[0].settings_field_image;
                                        info['settings_field_image_fields'] = objImgField[0].settings_field_image_fields;
                                    }
                                    info['settings_field_image'] = html.find('#settings_field_image').val();
                                    info['settings_field_image_fields'] = html.find('#settings_field_image_fields').val();
                                    objImgField.css({
                                        'width': (info['original_width']) ? info['original_width'] : '100%',
                                        'height': (info['original_height']) ? info['original_height'] : '',
                                        'margin-left': '',
                                        'margin-right': ''
                                    });
                                    // Save settings
                                    objImgField.attr('data-info', JSON.stringify(info));
                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.blocks.tbl_one_column:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');
                            var maintable1 = target.closest('.content-container.block-handle');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.attr('data-info');
                            if (typeof info === 'undefined') {
                                info = {};
                            }
                            // Default selected
                            if (!jQuery.isEmptyObject(info)) {
                                info = JSON.parse(info);
                                for(var i= 0; i< info.length ; i++) {
                                    (function (info) {
                                        var labelField = info[i].label;
                                        var text = info[i].token;
                                        var required = info[i].required;
                                        var editablefield = info[i].editable;
                                        var id= info[i].id;
                                        var name= info[i].name;
                                        var uitype= info[i].uitype;
                                        var datatype= info[i].datatype;
                                        var module= info[i].module;

                                        var infofield = {};

                                        AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/custom_table/field_table.html', true, function (html) {
                                            if(labelField != 'PLACEHOLDER(BLANK)') {
                                                infofield = {
                                                    editable: editablefield,
                                                    required: required,
                                                    token: text,
                                                    label: labelField,
                                                    id: id,
                                                    name: name,
                                                    uitype: uitype,
                                                    datatype: datatype,
                                                    module: module
                                                };
                                            }else {
                                                infofield = {
                                                    token: 'blank',
                                                    label: labelField
                                                };
                                            }
                                            var inputField = html.find('[name="inputTable"]');
                                            inputField.val(labelField);

                                            // $compile(html.contents())($scope);
                                            jQuery('[name="sortable1"]').append(html);
                                            // Init data-info
                                            if (inputField && inputField.length > 0) {
                                                inputField.attr('data-info', JSON.stringify(infofield));
                                            }

                                        });
                                    })(info);
                                }
                                jQuery(html).find("#sortable1").sortable();
                            }

                            // Open modal
                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.off('click').on('click', '.btn-submit', function () {
                                    container.addClass("quoting_tool-border-dotted-fixed");

                                    var sortable1  = html.find('[name ="sortable1"]');
                                    var fieldSelected = [];
                                    var tbody = '';
                                    jQuery.each(sortable1.find('li'), function (idx, val) {
                                        var thisFocus = $(this);
                                        var data = thisFocus.find('[name="inputTable"]').data('info');
                                        var allowEdit = data.editable;
                                        var mandatory = data.required;
                                        var uitype = data.uitype;
                                        var label = data.label;
                                        var token = data.token;
                                        var name = data.name;
                                        fieldSelected.push(data);
                                        var text = '';
                                        info = JSON.stringify(data);
                                        var dataInfoEncode = info.replace(/"/g, "&quot;");
                                        if(allowEdit == true && jQuery.inArray(uitype, ['72']) == -1 && name.indexOf('ctf_'+data.module.toLowerCase()) == -1) {
                                            if(jQuery.inArray(uitype, ['19', '21']) >= 0){
                                                text = '<textarea type="text" name="textarea_field" title="'+label+'" class="interactive_form_item" input-change="" ' +
                                                    'style="background-color: rgba(255,255,255,0); margin-bottom: 0px" ' +
                                                    'data-info="'+dataInfoEncode+'" ' +
                                                    'aria-invalid="false">'+token+'</textarea>';
                                            }else{
                                                text = '<input type="text" name="text_field" title="'+label+'" class="interactive_form_item is_merge_field" input-change=""' +
                                                    ' style="background-color: rgba(255,255,255,0); width: 192px; margin-bottom: 0px" data-info="'+dataInfoEncode+'"' +
                                                    ' value="'+token+'" aria-invalid="false">';
                                            }
                                            if(mandatory == true) {
                                                text += '<span class="mark-required" style="color: red;vertical-align: top;white-space: normal;">*</span>';
                                            }
                                        }else{
                                            text = token;
                                        }


                                        if(token == 'blank') {
                                            tbody += '<tr class="tr-table" style="height: 36px;"><td class="left-td">&nbsp;</td><td></td></tr>'
                                        }else {
                                            tbody += '<tr class="tr-table" style="height: 36px;">' +
                                                '<td class="left-td" style="width: 40%; text-align: right;"><span>'+label+'</span></td>' +
                                                '<td>'+text+'</td>' +
                                                '</tr>';
                                        }
                                    });
                                    // Save settings
                                    objTable.data('info', JSON.stringify(fieldSelected));
                                    objTable.attr('data-info', JSON.stringify(fieldSelected));
                                    objTable.attr('border','0');
                                    if(tbody !='') {
                                        // remove text_field in table1
                                        maintable1.find('.content-container.quoting_tool-draggable').remove();
                                        //append tbody
                                        objTable.find('tbody').empty().append(tbody);
                                    }
                                    // Close modal
                                    AppHelper.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.tbl_two_columns:
                        AppUtils.loadTemplate($scope, focus.setting_template, false, function (html) {
                            // Before open modal
                            var contenteditable = editable.find('[contenteditable="true"]');
                            var maintable1 = target.closest('.content-container.block-handle');

                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTable = contenteditable.find('table');
                            var info = objTable.attr('data-info');
                            if (typeof info === 'undefined') {
                                info = {};
                            }
                            // Default selected
                            if (!jQuery.isEmptyObject(info)) {
                                info = JSON.parse(info);
                                var sort1 =  info['sortable1'].length;
                                var sort2 =  info['sortable2'].length;
                                var numRow = 0;
                                if(sort1 >= sort2) {
                                    numRow = sort1;
                                }else {
                                    numRow = sort2;
                                }
                                for(var i = 0; i < numRow; i++) {
                                    // with sort 1
                                    if (i < sort1) {
                                        var labelField = info['sortable1'][i].label;
                                        var text = info['sortable1'][i].token;
                                        var required = info['sortable1'][i].required;
                                        var editablefield = info['sortable1'][i].editable;
                                        var id1 = info['sortable1'][i].id;
                                        var name1 = info['sortable1'][i].name;
                                        var uitype1 = info['sortable1'][i].uitype;
                                        var datatype1 = info['sortable1'][i].datatype;
                                        var module1 = info['sortable1'][i].module;

                                        (function (labelField, text, required, editablefield,id1 ,name1, uitype1, datatype1, module1) {

                                            AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/custom_table/field_table.html', false, function (html1) {
                                                var infofield = {};
                                                if (labelField != 'PLACEHOLDER(BLANK)') {
                                                    infofield = {
                                                        editable: editablefield,
                                                        required: required,
                                                        token: text,
                                                        label: labelField,
                                                        id : id1,
                                                        name : name1,
                                                        uitype : uitype1,
                                                        datatype : datatype1,
                                                        module : module1,
                                                    };
                                                } else {
                                                    infofield = {
                                                        token: 'blank',
                                                        label: labelField
                                                    };
                                                }
                                                var inputField1 = html1.find('[name="inputTable"]');
                                                inputField1.val(labelField);

                                                $compile(html1.contents())($scope);
                                                jQuery('[name="sortable1"]').append(html1);
                                                // Init data-info
                                                if (inputField1 && inputField1.length > 0) {
                                                    inputField1.attr('data-info', JSON.stringify(infofield));
                                                }
                                            });
                                        })(labelField, text, required, editablefield, id1, name1, uitype1, datatype1, module1);
                                    }

                                    if (i < sort2) {
                                        var labelField2 = info['sortable2'][i].label;
                                        var text2 = info['sortable2'][i].token;
                                        var required2 = info['sortable2'][i].required;
                                        var editablefield2 = info['sortable2'][i].editable;
                                        var id2 = info['sortable2'][i].id;
                                        var name2 = info['sortable2'][i].name;
                                        var uitype2 = info['sortable2'][i].uitype;
                                        var datatype2 = info['sortable2'][i].datatype;
                                        var module2 = info['sortable2'][i].module;

                                        (function (labelField2, text2, required2, editablefield2, id2 ,name2, uitype2, datatype2, module2) {
                                            AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/custom_table/field_table.html', false, function (html2) {
                                                var infofield2 = {};
                                                if (labelField2 != 'PLACEHOLDER(BLANK)') {
                                                    infofield2 = {
                                                        editable: editablefield2,
                                                        required: required2,
                                                        token: text2,
                                                        label: labelField2,
                                                        id : id2,
                                                        name : name2,
                                                        uitype : uitype2,
                                                        datatype : datatype2,
                                                        module : module2,
                                                    };
                                                } else {
                                                    infofield2 = {
                                                        token: 'blank',
                                                        label: labelField2
                                                    };
                                                }
                                                var inputField2 = html2.find('[name="inputTable"]');
                                                inputField2.val(labelField2);

                                                $compile(html2.contents())($scope);
                                                jQuery('[name="sortable2"]').append(html2);
                                                // Init data-info
                                                if (inputField2 && inputField2.length > 0) {
                                                    inputField2.attr('data-info', JSON.stringify(infofield2));
                                                }
                                            });
                                        })(labelField2, text2, required2, editablefield2, id2 ,name2, uitype2, datatype2, module2);
                                    }
                                }
                                jQuery(html).find("#sortable1").sortable();
                                jQuery(html).find("#sortable2").sortable();
                            }

                            // Open modal
                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.off('click').on('click', '.btn-submit', function () {
                                    container.addClass("quoting_tool-border-dotted-fixed");
                                    var sortable1  = html.find('[name ="sortable1"]');
                                    var sortable2  = html.find('[name ="sortable2"]');
                                    var fieldSelected1 = [];
                                    var fieldSelected2 = [];
                                    var fieldSelected = {};
                                    var tbody = '';
                                    jQuery.each(sortable1.find('li'), function (idx, val) {
                                        var thisFocus = $(this);
                                        var data = thisFocus.find('[name="inputTable"]').data('info');
                                        fieldSelected1.push(data);
                                    });
                                    jQuery.each(sortable2.find('li'), function (idx, val) {
                                        var thisFocus = $(this);
                                        var data = thisFocus.find('[name="inputTable"]').data('info');
                                        fieldSelected2.push(data);
                                    });
                                    fieldSelected['sortable1'] = fieldSelected1;
                                    fieldSelected['sortable2'] = fieldSelected2;
                                    var sort1 =  fieldSelected['sortable1'].length;
                                    var sort2 =  fieldSelected['sortable2'].length;
                                    var numRow = 0;
                                    if(sort1 >= sort2) {
                                        numRow = sort1;
                                    }else {
                                        numRow = sort2;
                                    }
                                    var runSubmit = false;
                                    var tbody = '<tr class="tr-table" style="height: 36px; line-height: 18px"><td class="left-td"; style="line-height: 18px">&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
                                    for(var i = 0; i < numRow; i++) {
                                        tbody+='<tr class="tr-table" style="height: 36px; line-height: 18px">';
                                        runSubmit = true;
                                        // with sort 1
                                        var tmpSort1 = (i < sort1) ? fieldSelected['sortable1'][i]['label'] : '&nbsp;';
                                        if(tmpSort1 =='PLACEHOLDER(BLANK)') {
                                            tbody += '<td style="width: 20%;line-height: 18px;" class="left-td">&nbsp;</td><td>&nbsp;</td>';
                                        } else{
                                            text = '&nbsp;';
                                            if(i< sort1) {
                                                var data1 = fieldSelected['sortable1'][i];
                                                var allowEdit1 = data1.editable;
                                                var mandatory1 = data1.required;
                                                var uitype1 = data1.uitype;
                                                var label1 = data1.label;
                                                var token1 = data1.token;
                                                var text = '';
                                                var name1 = data1.name;
                                                info = JSON.stringify(data1);
                                                var dataInfoEncode = info.replace(/"/g, "&quot;");
                                                    if(allowEdit1 == true && jQuery.inArray(uitype1, ['72']) == -1 && name1.indexOf('ctf_'+data1.module.toLowerCase()) == -1) {
                                                    if(jQuery.inArray(uitype1, ['19', '21']) >= 0){
                                                        text = '<textarea type="text" name="textarea_field" title="'+label1+'" class="interactive_form_item" input-change="" ' +
                                                            'style="background-color: rgba(255,255,255,0); margin-bottom: 0px" ' +
                                                            'data-info="'+dataInfoEncode+'" ' +
                                                            'aria-invalid="false">'+token1+'</textarea>';
                                                    }else{
                                                        text = '<input type="text" name="text_field" title="'+label1+'" class="interactive_form_item is_merge_field" input-change=""' +
                                                            ' style="background-color: rgba(255,255,255,0); width: 192px; margin-bottom: 0px" data-info="'+dataInfoEncode+'"' +
                                                            ' value="'+token1+'" aria-invalid="false">';
                                                    }
                                                    if(mandatory1 == true) {
                                                        text += '<span class="mark-required" style="color: red;vertical-align: top;white-space: normal;">*</span>';
                                                    }
                                                }else{
                                                    text = token1;
                                                }
                                            }
                                            tbody += '<td style="width: 20%; text-align: right; line-height: 18px" class="left-td">' + tmpSort1 + '</td><td>'+text+'</td>';
                                        }

                                        // with sort 2
                                        var tmpSort2 = (i < sort2) ? fieldSelected['sortable2'][i]['label'] : '&nbsp;';
                                        if(tmpSort2 =='PLACEHOLDER(BLANK)') {
                                            tbody += '<td style="width: 20% line-height: 18px" class="left-td">&nbsp;</td><td>&nbsp;</td>';
                                        } else{
                                            text = '&nbsp;';
                                            if(i < sort2){
                                                var data2 = fieldSelected['sortable2'][i];
                                                var allowEdit2 = data2.editable;
                                                var mandatory2 = data2.required;
                                                var uitype2 = data2.uitype;
                                                var label2 = data2.label;
                                                var token2 = data2.token;
                                                var name2 = data2.name;
                                                info = JSON.stringify(data2);
                                                var dataInfoEncode2 = info.replace(/"/g, "&quot;");
                                                if(allowEdit2 == true && jQuery.inArray(uitype2, ['72']) == -1 && name2.indexOf('ctf_'+data2.module.toLowerCase()) == -1) {
                                                    if(jQuery.inArray(uitype2, ['19', '21']) >= 0){
                                                        text = '<textarea type="text" name="textarea_field" title="'+label2+'" class="interactive_form_item" input-change="" ' +
                                                            'style="background-color: rgba(255,255,255,0);" ' +
                                                            'data-info="'+dataInfoEncode2+'" ' +
                                                            'aria-invalid="false">'+token2+'</textarea>';
                                                    }else{
                                                        text = '<input type="text" name="text_field" title="'+label2+'" class="interactive_form_item is_merge_field" input-change=""' +
                                                            ' style="background-color: rgba(255,255,255,0); width: 192px" data-info="'+dataInfoEncode2+'"' +
                                                            ' value="'+token2+'" aria-invalid="false">';
                                                    }
                                                    if(mandatory2 == true) {
                                                        text += '<span class="mark-required" style="color: red;vertical-align: top;white-space: normal;">*</span>';
                                                    }
                                                }else{
                                                    text = token2;
                                                }
                                            }
                                            tbody += '<td style="width: 20%; text-align: right;line-height: 18px" class="left-td">' + tmpSort2 + '</td><td>'+text+'</td>';
                                        }

                                        tbody+='</tr>';
                                    }

                                    // Save settings
                                    objTable.data('info', JSON.stringify(fieldSelected));
                                    objTable.attr('data-info', JSON.stringify(fieldSelected));
                                    objTable.attr('border','0');
                                    if(runSubmit) {
                                        // remove text_field in table1
                                        maintable1.find('.content-container.quoting_tool-draggable').remove();
                                        //append tbody
                                        objTable.find('tbody').empty().append(tbody);
                                    }
                                    // Close modal
                                    AppHelper.hideModalWindow();
                                });
                            });
                            $compile(html.contents())($scope);
                        });
                        break;
                    case $rootScope.app.data.blocks.spacer:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            var form = html.find('.form');
                            if(info == ''){
                                info = {}
                            }else{
                                if(typeof info == 'string'){
                                    info = JSON.parse(info);
                                }
                            }
                            var value = info['height'];
                            var field = form.find('input');
                            if(value == '' || typeof value == 'undefined'){
                                value = 25;
                            }
                            field.val(value);
                            registerFormatOption(form);
                            registerfontweigth(form);
                            AppHelper.showModalWindow(html);
                        });
                        break;
                    case $rootScope.app.data.blocks.icon_label:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            $scope.iconsLabel = ["modules/QuotingTool/html_icons_iconlabel/500px.png",
                                "modules/QuotingTool/html_icons_iconlabel/address-book.png",
                                "modules/QuotingTool/html_icons_iconlabel/address-book-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/address-card.png",
                                "modules/QuotingTool/html_icons_iconlabel/address-card-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/adjust.png",
                                "modules/QuotingTool/html_icons_iconlabel/adn.png",
                                "modules/QuotingTool/html_icons_iconlabel/align-center.png",
                                "modules/QuotingTool/html_icons_iconlabel/align-justify.png",
                                "modules/QuotingTool/html_icons_iconlabel/align-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/align-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/amazon.png",
                                "modules/QuotingTool/html_icons_iconlabel/ambulance.png",
                                "modules/QuotingTool/html_icons_iconlabel/american-sign-language-interpreting.png",
                                "modules/QuotingTool/html_icons_iconlabel/anchor.png",
                                "modules/QuotingTool/html_icons_iconlabel/android.png",
                                "modules/QuotingTool/html_icons_iconlabel/angellist.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-double-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-double-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-double-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-double-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/angle-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/apple.png",
                                "modules/QuotingTool/html_icons_iconlabel/archive.png",
                                "modules/QuotingTool/html_icons_iconlabel/area-chart.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-o-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-o-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-o-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-o-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-circle-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrow-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrows.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrows-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrows-h.png",
                                "modules/QuotingTool/html_icons_iconlabel/arrows-v.png",
                                "modules/QuotingTool/html_icons_iconlabel/asl-interpreting.png",
                                "modules/QuotingTool/html_icons_iconlabel/assistive-listening-systems.png",
                                "modules/QuotingTool/html_icons_iconlabel/asterisk.png",
                                "modules/QuotingTool/html_icons_iconlabel/at.png",
                                "modules/QuotingTool/html_icons_iconlabel/audio-description.png",
                                "modules/QuotingTool/html_icons_iconlabel/automobile.png",
                                "modules/QuotingTool/html_icons_iconlabel/backward.png",
                                "modules/QuotingTool/html_icons_iconlabel/balance-scale.png",
                                "modules/QuotingTool/html_icons_iconlabel/ban.png",
                                "modules/QuotingTool/html_icons_iconlabel/bandcamp.png",
                                "modules/QuotingTool/html_icons_iconlabel/bank.png",
                                "modules/QuotingTool/html_icons_iconlabel/bar-chart.png",
                                "modules/QuotingTool/html_icons_iconlabel/bar-chart-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/barcode.png",
                                "modules/QuotingTool/html_icons_iconlabel/bars.png",
                                "modules/QuotingTool/html_icons_iconlabel/bath.png",
                                "modules/QuotingTool/html_icons_iconlabel/bathtub.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-0.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-1.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-2.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-3.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-4.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-empty.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-full.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-half.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-quarter.png",
                                "modules/QuotingTool/html_icons_iconlabel/battery-three-quarters.png",
                                "modules/QuotingTool/html_icons_iconlabel/bed.png",
                                "modules/QuotingTool/html_icons_iconlabel/beer.png",
                                "modules/QuotingTool/html_icons_iconlabel/behance.png",
                                "modules/QuotingTool/html_icons_iconlabel/behance-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/bell.png",
                                "modules/QuotingTool/html_icons_iconlabel/bell-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/bell-slash.png",
                                "modules/QuotingTool/html_icons_iconlabel/bell-slash-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/bicycle.png",
                                "modules/QuotingTool/html_icons_iconlabel/binoculars.png",
                                "modules/QuotingTool/html_icons_iconlabel/birthday-cake.png",
                                "modules/QuotingTool/html_icons_iconlabel/bitbucket.png",
                                "modules/QuotingTool/html_icons_iconlabel/bitbucket-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/bitcoin.png",
                                "modules/QuotingTool/html_icons_iconlabel/black-tie.png",
                                "modules/QuotingTool/html_icons_iconlabel/blind.png",
                                "modules/QuotingTool/html_icons_iconlabel/bluetooth.png",
                                "modules/QuotingTool/html_icons_iconlabel/bluetooth-b.png",
                                "modules/QuotingTool/html_icons_iconlabel/bold.png",
                                "modules/QuotingTool/html_icons_iconlabel/bolt.png",
                                "modules/QuotingTool/html_icons_iconlabel/bomb.png",
                                "modules/QuotingTool/html_icons_iconlabel/book.png",
                                "modules/QuotingTool/html_icons_iconlabel/bookmark.png",
                                "modules/QuotingTool/html_icons_iconlabel/bookmark-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/braille.png",
                                "modules/QuotingTool/html_icons_iconlabel/briefcase.png",
                                "modules/QuotingTool/html_icons_iconlabel/btc.png",
                                "modules/QuotingTool/html_icons_iconlabel/bug.png",
                                "modules/QuotingTool/html_icons_iconlabel/building.png",
                                "modules/QuotingTool/html_icons_iconlabel/building-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/bullhorn.png",
                                "modules/QuotingTool/html_icons_iconlabel/bullseye.png",
                                "modules/QuotingTool/html_icons_iconlabel/bus.png",
                                "modules/QuotingTool/html_icons_iconlabel/buysellads.png",
                                "modules/QuotingTool/html_icons_iconlabel/cab.png",
                                "modules/QuotingTool/html_icons_iconlabel/calculator.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar-check-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar-minus-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar-plus-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/calendar-times-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/camera.png",
                                "modules/QuotingTool/html_icons_iconlabel/camera-retro.png",
                                "modules/QuotingTool/html_icons_iconlabel/car.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-square-o-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-square-o-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-square-o-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-square-o-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/caret-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/cart-arrow-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/cart-plus.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-amex.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-diners-club.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-discover.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-jcb.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-mastercard.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-paypal.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-stripe.png",
                                "modules/QuotingTool/html_icons_iconlabel/cc-visa.png",
                                "modules/QuotingTool/html_icons_iconlabel/certificate.png",
                                "modules/QuotingTool/html_icons_iconlabel/chain.png",
                                "modules/QuotingTool/html_icons_iconlabel/chain-broken.png",
                                "modules/QuotingTool/html_icons_iconlabel/check.png",
                                "modules/QuotingTool/html_icons_iconlabel/check-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/check-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/check-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/check-square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-circle-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-circle-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-circle-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-circle-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/chevron-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/child.png",
                                "modules/QuotingTool/html_icons_iconlabel/chrome.png",
                                "modules/QuotingTool/html_icons_iconlabel/circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/circle-o-notch.png",
                                "modules/QuotingTool/html_icons_iconlabel/circle-thin.png",
                                "modules/QuotingTool/html_icons_iconlabel/clipboard.png",
                                "modules/QuotingTool/html_icons_iconlabel/clock-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/clone.png",
                                "modules/QuotingTool/html_icons_iconlabel/close.png",
                                "modules/QuotingTool/html_icons_iconlabel/cloud.png",
                                "modules/QuotingTool/html_icons_iconlabel/cloud-download.png",
                                "modules/QuotingTool/html_icons_iconlabel/cloud-upload.png",
                                "modules/QuotingTool/html_icons_iconlabel/cny.png",
                                "modules/QuotingTool/html_icons_iconlabel/code.png",
                                "modules/QuotingTool/html_icons_iconlabel/code-fork.png",
                                "modules/QuotingTool/html_icons_iconlabel/codepen.png",
                                "modules/QuotingTool/html_icons_iconlabel/codiepie.png",
                                "modules/QuotingTool/html_icons_iconlabel/coffee.png",
                                "modules/QuotingTool/html_icons_iconlabel/cog.png",
                                "modules/QuotingTool/html_icons_iconlabel/cogs.png",
                                "modules/QuotingTool/html_icons_iconlabel/columns.png",
                                "modules/QuotingTool/html_icons_iconlabel/comment.png",
                                "modules/QuotingTool/html_icons_iconlabel/comment-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/commenting.png",
                                "modules/QuotingTool/html_icons_iconlabel/commenting-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/comments.png",
                                "modules/QuotingTool/html_icons_iconlabel/comments-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/compass.png",
                                "modules/QuotingTool/html_icons_iconlabel/compress.png",
                                "modules/QuotingTool/html_icons_iconlabel/connectdevelop.png",
                                "modules/QuotingTool/html_icons_iconlabel/contao.png",
                                "modules/QuotingTool/html_icons_iconlabel/copy.png",
                                "modules/QuotingTool/html_icons_iconlabel/copyright.png",
                                "modules/QuotingTool/html_icons_iconlabel/creative-commons.png",
                                "modules/QuotingTool/html_icons_iconlabel/credit-card.png",
                                "modules/QuotingTool/html_icons_iconlabel/credit-card-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/crop.png",
                                "modules/QuotingTool/html_icons_iconlabel/crosshairs.png",
                                "modules/QuotingTool/html_icons_iconlabel/css3.png",
                                "modules/QuotingTool/html_icons_iconlabel/cube.png",
                                "modules/QuotingTool/html_icons_iconlabel/cubes.png",
                                "modules/QuotingTool/html_icons_iconlabel/cut.png",
                                "modules/QuotingTool/html_icons_iconlabel/cutlery.png",
                                "modules/QuotingTool/html_icons_iconlabel/dashboard.png",
                                "modules/QuotingTool/html_icons_iconlabel/dashcube.png",
                                "modules/QuotingTool/html_icons_iconlabel/database.png",
                                "modules/QuotingTool/html_icons_iconlabel/deaf.png",
                                "modules/QuotingTool/html_icons_iconlabel/deafness.png",
                                "modules/QuotingTool/html_icons_iconlabel/dedent.png",
                                "modules/QuotingTool/html_icons_iconlabel/delicious.png",
                                "modules/QuotingTool/html_icons_iconlabel/desktop.png",
                                "modules/QuotingTool/html_icons_iconlabel/deviantart.png",
                                "modules/QuotingTool/html_icons_iconlabel/diamond.png",
                                "modules/QuotingTool/html_icons_iconlabel/digg.png",
                                "modules/QuotingTool/html_icons_iconlabel/dollar.png",
                                "modules/QuotingTool/html_icons_iconlabel/dot-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/download.png",
                                "modules/QuotingTool/html_icons_iconlabel/dribbble.png",
                                "modules/QuotingTool/html_icons_iconlabel/drivers-license.png",
                                "modules/QuotingTool/html_icons_iconlabel/drivers-license-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/dropbox.png",
                                "modules/QuotingTool/html_icons_iconlabel/drupal.png",
                                "modules/QuotingTool/html_icons_iconlabel/edge.png",
                                "modules/QuotingTool/html_icons_iconlabel/edit.png",
                                "modules/QuotingTool/html_icons_iconlabel/eercast.png",
                                "modules/QuotingTool/html_icons_iconlabel/eject.png",
                                "modules/QuotingTool/html_icons_iconlabel/ellipsis-h.png",
                                "modules/QuotingTool/html_icons_iconlabel/ellipsis-v.png",
                                "modules/QuotingTool/html_icons_iconlabel/empire.png",
                                "modules/QuotingTool/html_icons_iconlabel/envelope.png",
                                "modules/QuotingTool/html_icons_iconlabel/envelope-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/envelope-open.png",
                                "modules/QuotingTool/html_icons_iconlabel/envelope-open-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/envelope-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/envira.png",
                                "modules/QuotingTool/html_icons_iconlabel/eraser.png",
                                "modules/QuotingTool/html_icons_iconlabel/etsy.png",
                                "modules/QuotingTool/html_icons_iconlabel/eur.png",
                                "modules/QuotingTool/html_icons_iconlabel/euro.png",
                                "modules/QuotingTool/html_icons_iconlabel/exchange.png",
                                "modules/QuotingTool/html_icons_iconlabel/exclamation.png",
                                "modules/QuotingTool/html_icons_iconlabel/exclamation-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/exclamation-triangle.png",
                                "modules/QuotingTool/html_icons_iconlabel/expand.png",
                                "modules/QuotingTool/html_icons_iconlabel/expeditedssl.png",
                                "modules/QuotingTool/html_icons_iconlabel/external-link.png",
                                "modules/QuotingTool/html_icons_iconlabel/external-link-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/eye.png",
                                "modules/QuotingTool/html_icons_iconlabel/eye-slash.png",
                                "modules/QuotingTool/html_icons_iconlabel/eyedropper.png",
                                "modules/QuotingTool/html_icons_iconlabel/fa.png",
                                "modules/QuotingTool/html_icons_iconlabel/facebook.png",
                                "modules/QuotingTool/html_icons_iconlabel/facebook-f.png",
                                "modules/QuotingTool/html_icons_iconlabel/facebook-official.png",
                                "modules/QuotingTool/html_icons_iconlabel/facebook-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/fast-backward.png",
                                "modules/QuotingTool/html_icons_iconlabel/fast-forward.png",
                                "modules/QuotingTool/html_icons_iconlabel/fax.png",
                                "modules/QuotingTool/html_icons_iconlabel/feed.png",
                                "modules/QuotingTool/html_icons_iconlabel/female.png",
                                "modules/QuotingTool/html_icons_iconlabel/fighter-jet.png",
                                "modules/QuotingTool/html_icons_iconlabel/file.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-archive-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-audio-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-code-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-excel-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-image-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-movie-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-pdf-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-photo-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-picture-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-powerpoint-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-sound-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-text.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-text-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-video-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-word-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/file-zip-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/files-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/film.png",
                                "modules/QuotingTool/html_icons_iconlabel/filter.png",
                                "modules/QuotingTool/html_icons_iconlabel/fire.png",
                                "modules/QuotingTool/html_icons_iconlabel/fire-extinguisher.png",
                                "modules/QuotingTool/html_icons_iconlabel/firefox.png",
                                "modules/QuotingTool/html_icons_iconlabel/first-order.png",
                                "modules/QuotingTool/html_icons_iconlabel/flag.png",
                                "modules/QuotingTool/html_icons_iconlabel/flag-checkered.png",
                                "modules/QuotingTool/html_icons_iconlabel/flag-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/flash.png",
                                "modules/QuotingTool/html_icons_iconlabel/flask.png",
                                "modules/QuotingTool/html_icons_iconlabel/flickr.png",
                                "modules/QuotingTool/html_icons_iconlabel/floppy-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/folder.png",
                                "modules/QuotingTool/html_icons_iconlabel/folder-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/folder-open.png",
                                "modules/QuotingTool/html_icons_iconlabel/folder-open-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/font.png",
                                "modules/QuotingTool/html_icons_iconlabel/font-awesome.png",
                                "modules/QuotingTool/html_icons_iconlabel/fonticons.png",
                                "modules/QuotingTool/html_icons_iconlabel/fort-awesome.png",
                                "modules/QuotingTool/html_icons_iconlabel/forumbee.png",
                                "modules/QuotingTool/html_icons_iconlabel/forward.png",
                                "modules/QuotingTool/html_icons_iconlabel/foursquare.png",
                                "modules/QuotingTool/html_icons_iconlabel/free-code-camp.png",
                                "modules/QuotingTool/html_icons_iconlabel/frown-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/futbol-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/gamepad.png",
                                "modules/QuotingTool/html_icons_iconlabel/gavel.png",
                                "modules/QuotingTool/html_icons_iconlabel/gbp.png",
                                "modules/QuotingTool/html_icons_iconlabel/ge.png",
                                "modules/QuotingTool/html_icons_iconlabel/gear.png",
                                "modules/QuotingTool/html_icons_iconlabel/gears.png",
                                "modules/QuotingTool/html_icons_iconlabel/genderless.png",
                                "modules/QuotingTool/html_icons_iconlabel/get-pocket.png",
                                "modules/QuotingTool/html_icons_iconlabel/gg.png",
                                "modules/QuotingTool/html_icons_iconlabel/gg-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/gift.png",
                                "modules/QuotingTool/html_icons_iconlabel/git.png",
                                "modules/QuotingTool/html_icons_iconlabel/git-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/github.png",
                                "modules/QuotingTool/html_icons_iconlabel/github-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/github-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/gitlab.png",
                                "modules/QuotingTool/html_icons_iconlabel/gittip.png",
                                "modules/QuotingTool/html_icons_iconlabel/glass.png",
                                "modules/QuotingTool/html_icons_iconlabel/glide.png",
                                "modules/QuotingTool/html_icons_iconlabel/glide-g.png",
                                "modules/QuotingTool/html_icons_iconlabel/globe.png",
                                "modules/QuotingTool/html_icons_iconlabel/google.png",
                                "modules/QuotingTool/html_icons_iconlabel/google-plus.png",
                                "modules/QuotingTool/html_icons_iconlabel/google-plus-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/google-plus-official.png",
                                "modules/QuotingTool/html_icons_iconlabel/google-plus-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/google-wallet.png",
                                "modules/QuotingTool/html_icons_iconlabel/graduation-cap.png",
                                "modules/QuotingTool/html_icons_iconlabel/gratipay.png",
                                "modules/QuotingTool/html_icons_iconlabel/grav.png",
                                "modules/QuotingTool/html_icons_iconlabel/group.png",
                                "modules/QuotingTool/html_icons_iconlabel/h-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/hacker-news.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-grab-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-lizard-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-o-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-o-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-o-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-o-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-paper-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-peace-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-pointer-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-rock-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-scissors-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-spock-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hand-stop-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/handshake-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hard-of-hearing.png",
                                "modules/QuotingTool/html_icons_iconlabel/hashtag.png",
                                "modules/QuotingTool/html_icons_iconlabel/hdd-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/header.png",
                                "modules/QuotingTool/html_icons_iconlabel/headphones.png",
                                "modules/QuotingTool/html_icons_iconlabel/heart.png",
                                "modules/QuotingTool/html_icons_iconlabel/heart-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/heartbeat.png",
                                "modules/QuotingTool/html_icons_iconlabel/history.png",
                                "modules/QuotingTool/html_icons_iconlabel/home.png",
                                "modules/QuotingTool/html_icons_iconlabel/hospital-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hotel.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-1.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-2.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-3.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-end.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-half.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/hourglass-start.png",
                                "modules/QuotingTool/html_icons_iconlabel/houzz.png",
                                "modules/QuotingTool/html_icons_iconlabel/html5.png",
                                "modules/QuotingTool/html_icons_iconlabel/i-cursor.png",
                                "modules/QuotingTool/html_icons_iconlabel/id-badge.png",
                                "modules/QuotingTool/html_icons_iconlabel/id-card.png",
                                "modules/QuotingTool/html_icons_iconlabel/id-card-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/ils.png",
                                "modules/QuotingTool/html_icons_iconlabel/image.png",
                                "modules/QuotingTool/html_icons_iconlabel/imdb.png",
                                "modules/QuotingTool/html_icons_iconlabel/inbox.png",
                                "modules/QuotingTool/html_icons_iconlabel/indent.png",
                                "modules/QuotingTool/html_icons_iconlabel/industry.png",
                                "modules/QuotingTool/html_icons_iconlabel/info.png",
                                "modules/QuotingTool/html_icons_iconlabel/info-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/inr.png",
                                "modules/QuotingTool/html_icons_iconlabel/instagram.png",
                                "modules/QuotingTool/html_icons_iconlabel/institution.png",
                                "modules/QuotingTool/html_icons_iconlabel/internet-explorer.png",
                                "modules/QuotingTool/html_icons_iconlabel/intersex.png",
                                "modules/QuotingTool/html_icons_iconlabel/ioxhost.png",
                                "modules/QuotingTool/html_icons_iconlabel/italic.png",
                                "modules/QuotingTool/html_icons_iconlabel/joomla.png",
                                "modules/QuotingTool/html_icons_iconlabel/jpy.png",
                                "modules/QuotingTool/html_icons_iconlabel/jsfiddle.png",
                                "modules/QuotingTool/html_icons_iconlabel/key.png",
                                "modules/QuotingTool/html_icons_iconlabel/keyboard-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/krw.png",
                                "modules/QuotingTool/html_icons_iconlabel/language.png",
                                "modules/QuotingTool/html_icons_iconlabel/laptop.png",
                                "modules/QuotingTool/html_icons_iconlabel/lastfm.png",
                                "modules/QuotingTool/html_icons_iconlabel/lastfm-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/leaf.png",
                                "modules/QuotingTool/html_icons_iconlabel/leanpub.png",
                                "modules/QuotingTool/html_icons_iconlabel/legal.png",
                                "modules/QuotingTool/html_icons_iconlabel/lemon-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/level-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/level-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/life-bouy.png",
                                "modules/QuotingTool/html_icons_iconlabel/life-buoy.png",
                                "modules/QuotingTool/html_icons_iconlabel/life-ring.png",
                                "modules/QuotingTool/html_icons_iconlabel/life-saver.png",
                                "modules/QuotingTool/html_icons_iconlabel/lightbulb-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/line-chart.png",
                                "modules/QuotingTool/html_icons_iconlabel/link.png",
                                "modules/QuotingTool/html_icons_iconlabel/linkedin.png",
                                "modules/QuotingTool/html_icons_iconlabel/linkedin-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/linode.png",
                                "modules/QuotingTool/html_icons_iconlabel/linux.png",
                                "modules/QuotingTool/html_icons_iconlabel/list.png",
                                "modules/QuotingTool/html_icons_iconlabel/list-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/list-ol.png",
                                "modules/QuotingTool/html_icons_iconlabel/list-ul.png",
                                "modules/QuotingTool/html_icons_iconlabel/location-arrow.png",
                                "modules/QuotingTool/html_icons_iconlabel/lock.png",
                                "modules/QuotingTool/html_icons_iconlabel/long-arrow-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/long-arrow-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/long-arrow-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/long-arrow-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/low-vision.png",
                                "modules/QuotingTool/html_icons_iconlabel/magic.png",
                                "modules/QuotingTool/html_icons_iconlabel/magnet.png",
                                "modules/QuotingTool/html_icons_iconlabel/mail-forward.png",
                                "modules/QuotingTool/html_icons_iconlabel/mail-reply.png",
                                "modules/QuotingTool/html_icons_iconlabel/mail-reply-all.png",
                                "modules/QuotingTool/html_icons_iconlabel/male.png",
                                "modules/QuotingTool/html_icons_iconlabel/map.png",
                                "modules/QuotingTool/html_icons_iconlabel/map-marker.png",
                                "modules/QuotingTool/html_icons_iconlabel/map-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/map-pin.png",
                                "modules/QuotingTool/html_icons_iconlabel/map-signs.png",
                                "modules/QuotingTool/html_icons_iconlabel/mars.png",
                                "modules/QuotingTool/html_icons_iconlabel/mars-double.png",
                                "modules/QuotingTool/html_icons_iconlabel/mars-stroke.png",
                                "modules/QuotingTool/html_icons_iconlabel/mars-stroke-h.png",
                                "modules/QuotingTool/html_icons_iconlabel/mars-stroke-v.png",
                                "modules/QuotingTool/html_icons_iconlabel/maxcdn.png",
                                "modules/QuotingTool/html_icons_iconlabel/meanpath.png",
                                "modules/QuotingTool/html_icons_iconlabel/medium.png",
                                "modules/QuotingTool/html_icons_iconlabel/medkit.png",
                                "modules/QuotingTool/html_icons_iconlabel/meetup.png",
                                "modules/QuotingTool/html_icons_iconlabel/meh-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/mercury.png",
                                "modules/QuotingTool/html_icons_iconlabel/microchip.png",
                                "modules/QuotingTool/html_icons_iconlabel/microphone.png",
                                "modules/QuotingTool/html_icons_iconlabel/microphone-slash.png",
                                "modules/QuotingTool/html_icons_iconlabel/minus.png",
                                "modules/QuotingTool/html_icons_iconlabel/minus-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/minus-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/minus-square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/mixcloud.png",
                                "modules/QuotingTool/html_icons_iconlabel/mobile.png",
                                "modules/QuotingTool/html_icons_iconlabel/mobile-phone.png",
                                "modules/QuotingTool/html_icons_iconlabel/modx.png",
                                "modules/QuotingTool/html_icons_iconlabel/money.png",
                                "modules/QuotingTool/html_icons_iconlabel/moon-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/mortar-board.png",
                                "modules/QuotingTool/html_icons_iconlabel/motorcycle.png",
                                "modules/QuotingTool/html_icons_iconlabel/mouse-pointer.png",
                                "modules/QuotingTool/html_icons_iconlabel/music.png",
                                "modules/QuotingTool/html_icons_iconlabel/navicon.png",
                                "modules/QuotingTool/html_icons_iconlabel/neuter.png",
                                "modules/QuotingTool/html_icons_iconlabel/newspaper-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/object-group.png",
                                "modules/QuotingTool/html_icons_iconlabel/object-ungroup.png",
                                "modules/QuotingTool/html_icons_iconlabel/odnoklassniki.png",
                                "modules/QuotingTool/html_icons_iconlabel/odnoklassniki-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/opencart.png",
                                "modules/QuotingTool/html_icons_iconlabel/openid.png",
                                "modules/QuotingTool/html_icons_iconlabel/opera.png",
                                "modules/QuotingTool/html_icons_iconlabel/optin-monster.png",
                                "modules/QuotingTool/html_icons_iconlabel/outdent.png",
                                "modules/QuotingTool/html_icons_iconlabel/pagelines.png",
                                "modules/QuotingTool/html_icons_iconlabel/paint-brush.png",
                                "modules/QuotingTool/html_icons_iconlabel/paper-plane.png",
                                "modules/QuotingTool/html_icons_iconlabel/paper-plane-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/paperclip.png",
                                "modules/QuotingTool/html_icons_iconlabel/paragraph.png",
                                "modules/QuotingTool/html_icons_iconlabel/paste.png",
                                "modules/QuotingTool/html_icons_iconlabel/pause.png",
                                "modules/QuotingTool/html_icons_iconlabel/pause-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/pause-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/paw.png",
                                "modules/QuotingTool/html_icons_iconlabel/paypal.png",
                                "modules/QuotingTool/html_icons_iconlabel/pencil.png",
                                "modules/QuotingTool/html_icons_iconlabel/pencil-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/pencil-square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/percent.png",
                                "modules/QuotingTool/html_icons_iconlabel/phone.png",
                                "modules/QuotingTool/html_icons_iconlabel/phone-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/photo.png",
                                "modules/QuotingTool/html_icons_iconlabel/picture-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/pie-chart.png",
                                "modules/QuotingTool/html_icons_iconlabel/pied-piper.png",
                                "modules/QuotingTool/html_icons_iconlabel/pied-piper-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/pied-piper-pp.png",
                                "modules/QuotingTool/html_icons_iconlabel/pinterest.png",
                                "modules/QuotingTool/html_icons_iconlabel/pinterest-p.png",
                                "modules/QuotingTool/html_icons_iconlabel/pinterest-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/plane.png",
                                "modules/QuotingTool/html_icons_iconlabel/play.png",
                                "modules/QuotingTool/html_icons_iconlabel/play-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/play-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/plug.png",
                                "modules/QuotingTool/html_icons_iconlabel/plus.png",
                                "modules/QuotingTool/html_icons_iconlabel/plus-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/plus-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/plus-square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/podcast.png",
                                "modules/QuotingTool/html_icons_iconlabel/power-off.png",
                                "modules/QuotingTool/html_icons_iconlabel/print.png",
                                "modules/QuotingTool/html_icons_iconlabel/product-hunt.png",
                                "modules/QuotingTool/html_icons_iconlabel/puzzle-piece.png",
                                "modules/QuotingTool/html_icons_iconlabel/qq.png",
                                "modules/QuotingTool/html_icons_iconlabel/qrcode.png",
                                "modules/QuotingTool/html_icons_iconlabel/question.png",
                                "modules/QuotingTool/html_icons_iconlabel/question-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/question-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/quora.png",
                                "modules/QuotingTool/html_icons_iconlabel/quote-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/quote-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/ra.png",
                                "modules/QuotingTool/html_icons_iconlabel/random.png",
                                "modules/QuotingTool/html_icons_iconlabel/ravelry.png",
                                "modules/QuotingTool/html_icons_iconlabel/rebel.png",
                                "modules/QuotingTool/html_icons_iconlabel/recycle.png",
                                "modules/QuotingTool/html_icons_iconlabel/reddit.png",
                                "modules/QuotingTool/html_icons_iconlabel/reddit-alien.png",
                                "modules/QuotingTool/html_icons_iconlabel/reddit-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/refresh.png",
                                "modules/QuotingTool/html_icons_iconlabel/registered.png",
                                "modules/QuotingTool/html_icons_iconlabel/remove.png",
                                "modules/QuotingTool/html_icons_iconlabel/renren.png",
                                "modules/QuotingTool/html_icons_iconlabel/reorder.png",
                                "modules/QuotingTool/html_icons_iconlabel/repeat.png",
                                "modules/QuotingTool/html_icons_iconlabel/reply.png",
                                "modules/QuotingTool/html_icons_iconlabel/reply-all.png",
                                "modules/QuotingTool/html_icons_iconlabel/resistance.png",
                                "modules/QuotingTool/html_icons_iconlabel/retweet.png",
                                "modules/QuotingTool/html_icons_iconlabel/rmb.png",
                                "modules/QuotingTool/html_icons_iconlabel/road.png",
                                "modules/QuotingTool/html_icons_iconlabel/rocket.png",
                                "modules/QuotingTool/html_icons_iconlabel/rotate-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/rotate-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/rouble.png",
                                "modules/QuotingTool/html_icons_iconlabel/rss.png",
                                "modules/QuotingTool/html_icons_iconlabel/rss-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/rub.png",
                                "modules/QuotingTool/html_icons_iconlabel/ruble.png",
                                "modules/QuotingTool/html_icons_iconlabel/rupee.png",
                                "modules/QuotingTool/html_icons_iconlabel/s15.png",
                                "modules/QuotingTool/html_icons_iconlabel/safari.png",
                                "modules/QuotingTool/html_icons_iconlabel/save.png",
                                "modules/QuotingTool/html_icons_iconlabel/scissors.png",
                                "modules/QuotingTool/html_icons_iconlabel/scribd.png",
                                "modules/QuotingTool/html_icons_iconlabel/search.png",
                                "modules/QuotingTool/html_icons_iconlabel/search-minus.png",
                                "modules/QuotingTool/html_icons_iconlabel/search-plus.png",
                                "modules/QuotingTool/html_icons_iconlabel/sellsy.png",
                                "modules/QuotingTool/html_icons_iconlabel/send.png",
                                "modules/QuotingTool/html_icons_iconlabel/send-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/server.png",
                                "modules/QuotingTool/html_icons_iconlabel/share.png",
                                "modules/QuotingTool/html_icons_iconlabel/share-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/share-alt-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/share-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/share-square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/shekel.png",
                                "modules/QuotingTool/html_icons_iconlabel/sheqel.png",
                                "modules/QuotingTool/html_icons_iconlabel/shield.png",
                                "modules/QuotingTool/html_icons_iconlabel/ship.png",
                                "modules/QuotingTool/html_icons_iconlabel/shirtsinbulk.png",
                                "modules/QuotingTool/html_icons_iconlabel/shopping-bag.png",
                                "modules/QuotingTool/html_icons_iconlabel/shopping-basket.png",
                                "modules/QuotingTool/html_icons_iconlabel/shopping-cart.png",
                                "modules/QuotingTool/html_icons_iconlabel/shower.png",
                                "modules/QuotingTool/html_icons_iconlabel/sign-in.png",
                                "modules/QuotingTool/html_icons_iconlabel/sign-language.png",
                                "modules/QuotingTool/html_icons_iconlabel/sign-out.png",
                                "modules/QuotingTool/html_icons_iconlabel/signal.png",
                                "modules/QuotingTool/html_icons_iconlabel/signing.png",
                                "modules/QuotingTool/html_icons_iconlabel/simplybuilt.png",
                                "modules/QuotingTool/html_icons_iconlabel/sitemap.png",
                                "modules/QuotingTool/html_icons_iconlabel/skyatlas.png",
                                "modules/QuotingTool/html_icons_iconlabel/skype.png",
                                "modules/QuotingTool/html_icons_iconlabel/slack.png",
                                "modules/QuotingTool/html_icons_iconlabel/sliders.png",
                                "modules/QuotingTool/html_icons_iconlabel/slideshare.png",
                                "modules/QuotingTool/html_icons_iconlabel/smile-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/snapchat.png",
                                "modules/QuotingTool/html_icons_iconlabel/snapchat-ghost.png",
                                "modules/QuotingTool/html_icons_iconlabel/snapchat-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/snowflake-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/soccer-ball-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-alpha-asc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-alpha-desc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-amount-asc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-amount-desc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-asc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-desc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-numeric-asc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-numeric-desc.png",
                                "modules/QuotingTool/html_icons_iconlabel/sort-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/soundcloud.png",
                                "modules/QuotingTool/html_icons_iconlabel/space-shuttle.png",
                                "modules/QuotingTool/html_icons_iconlabel/spinner.png",
                                "modules/QuotingTool/html_icons_iconlabel/spoon.png",
                                "modules/QuotingTool/html_icons_iconlabel/spotify.png",
                                "modules/QuotingTool/html_icons_iconlabel/square.png",
                                "modules/QuotingTool/html_icons_iconlabel/square-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/stack-exchange.png",
                                "modules/QuotingTool/html_icons_iconlabel/stack-overflow.png",
                                "modules/QuotingTool/html_icons_iconlabel/star.png",
                                "modules/QuotingTool/html_icons_iconlabel/star-half.png",
                                "modules/QuotingTool/html_icons_iconlabel/star-half-empty.png",
                                "modules/QuotingTool/html_icons_iconlabel/star-half-full.png",
                                "modules/QuotingTool/html_icons_iconlabel/star-half-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/star-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/steam.png",
                                "modules/QuotingTool/html_icons_iconlabel/steam-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/step-backward.png",
                                "modules/QuotingTool/html_icons_iconlabel/step-forward.png",
                                "modules/QuotingTool/html_icons_iconlabel/stethoscope.png",
                                "modules/QuotingTool/html_icons_iconlabel/sticky-note.png",
                                "modules/QuotingTool/html_icons_iconlabel/sticky-note-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/stop.png",
                                "modules/QuotingTool/html_icons_iconlabel/stop-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/stop-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/street-view.png",
                                "modules/QuotingTool/html_icons_iconlabel/strikethrough.png",
                                "modules/QuotingTool/html_icons_iconlabel/stumbleupon.png",
                                "modules/QuotingTool/html_icons_iconlabel/stumbleupon-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/subscript.png",
                                "modules/QuotingTool/html_icons_iconlabel/subway.png",
                                "modules/QuotingTool/html_icons_iconlabel/suitcase.png",
                                "modules/QuotingTool/html_icons_iconlabel/sun-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/superpowers.png",
                                "modules/QuotingTool/html_icons_iconlabel/superscript.png",
                                "modules/QuotingTool/html_icons_iconlabel/support.png",
                                "modules/QuotingTool/html_icons_iconlabel/table.png",
                                "modules/QuotingTool/html_icons_iconlabel/tablet.png",
                                "modules/QuotingTool/html_icons_iconlabel/tachometer.png",
                                "modules/QuotingTool/html_icons_iconlabel/tag.png",
                                "modules/QuotingTool/html_icons_iconlabel/tags.png",
                                "modules/QuotingTool/html_icons_iconlabel/tasks.png",
                                "modules/QuotingTool/html_icons_iconlabel/taxi.png",
                                "modules/QuotingTool/html_icons_iconlabel/telegram.png",
                                "modules/QuotingTool/html_icons_iconlabel/television.png",
                                "modules/QuotingTool/html_icons_iconlabel/tencent-weibo.png",
                                "modules/QuotingTool/html_icons_iconlabel/terminal.png",
                                "modules/QuotingTool/html_icons_iconlabel/text-height.png",
                                "modules/QuotingTool/html_icons_iconlabel/text-width.png",
                                "modules/QuotingTool/html_icons_iconlabel/th.png",
                                "modules/QuotingTool/html_icons_iconlabel/th-large.png",
                                "modules/QuotingTool/html_icons_iconlabel/th-list.png",
                                "modules/QuotingTool/html_icons_iconlabel/themeisle.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-0.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-1.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-2.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-3.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-4.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-empty.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-full.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-half.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-quarter.png",
                                "modules/QuotingTool/html_icons_iconlabel/thermometer-three-quarters.png",
                                "modules/QuotingTool/html_icons_iconlabel/thumb-tack.png",
                                "modules/QuotingTool/html_icons_iconlabel/thumbs-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/thumbs-o-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/thumbs-o-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/thumbs-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/ticket.png",
                                "modules/QuotingTool/html_icons_iconlabel/times.png",
                                "modules/QuotingTool/html_icons_iconlabel/times-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/times-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/times-rectangle.png",
                                "modules/QuotingTool/html_icons_iconlabel/times-rectangle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/tint.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-left.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-off.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-on.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-right.png",
                                "modules/QuotingTool/html_icons_iconlabel/toggle-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/trademark.png",
                                "modules/QuotingTool/html_icons_iconlabel/train.png",
                                "modules/QuotingTool/html_icons_iconlabel/transgender.png",
                                "modules/QuotingTool/html_icons_iconlabel/transgender-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/trash.png",
                                "modules/QuotingTool/html_icons_iconlabel/trash-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/tree.png",
                                "modules/QuotingTool/html_icons_iconlabel/trello.png",
                                "modules/QuotingTool/html_icons_iconlabel/tripadvisor.png",
                                "modules/QuotingTool/html_icons_iconlabel/trophy.png",
                                "modules/QuotingTool/html_icons_iconlabel/truck.png",
                                "modules/QuotingTool/html_icons_iconlabel/try.png",
                                "modules/QuotingTool/html_icons_iconlabel/tty.png",
                                "modules/QuotingTool/html_icons_iconlabel/tumblr.png",
                                "modules/QuotingTool/html_icons_iconlabel/tumblr-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/turkish-lira.png",
                                "modules/QuotingTool/html_icons_iconlabel/tv.png",
                                "modules/QuotingTool/html_icons_iconlabel/twitch.png",
                                "modules/QuotingTool/html_icons_iconlabel/twitter.png",
                                "modules/QuotingTool/html_icons_iconlabel/twitter-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/umbrella.png",
                                "modules/QuotingTool/html_icons_iconlabel/underline.png",
                                "modules/QuotingTool/html_icons_iconlabel/undo.png",
                                "modules/QuotingTool/html_icons_iconlabel/universal-access.png",
                                "modules/QuotingTool/html_icons_iconlabel/university.png",
                                "modules/QuotingTool/html_icons_iconlabel/unlink.png",
                                "modules/QuotingTool/html_icons_iconlabel/unlock.png",
                                "modules/QuotingTool/html_icons_iconlabel/unlock-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/unsorted.png",
                                "modules/QuotingTool/html_icons_iconlabel/upload.png",
                                "modules/QuotingTool/html_icons_iconlabel/usb.png",
                                "modules/QuotingTool/html_icons_iconlabel/usd.png",
                                "modules/QuotingTool/html_icons_iconlabel/user.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-circle.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-circle-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-md.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-plus.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-secret.png",
                                "modules/QuotingTool/html_icons_iconlabel/user-times.png",
                                "modules/QuotingTool/html_icons_iconlabel/users.png",
                                "modules/QuotingTool/html_icons_iconlabel/vcard.png",
                                "modules/QuotingTool/html_icons_iconlabel/vcard-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/venus.png",
                                "modules/QuotingTool/html_icons_iconlabel/venus-double.png",
                                "modules/QuotingTool/html_icons_iconlabel/venus-mars.png",
                                "modules/QuotingTool/html_icons_iconlabel/viacoin.png",
                                "modules/QuotingTool/html_icons_iconlabel/viadeo.png",
                                "modules/QuotingTool/html_icons_iconlabel/viadeo-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/video-camera.png",
                                "modules/QuotingTool/html_icons_iconlabel/vimeo.png",
                                "modules/QuotingTool/html_icons_iconlabel/vimeo-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/vine.png",
                                "modules/QuotingTool/html_icons_iconlabel/vk.png",
                                "modules/QuotingTool/html_icons_iconlabel/volume-control-phone.png",
                                "modules/QuotingTool/html_icons_iconlabel/volume-down.png",
                                "modules/QuotingTool/html_icons_iconlabel/volume-off.png",
                                "modules/QuotingTool/html_icons_iconlabel/volume-up.png",
                                "modules/QuotingTool/html_icons_iconlabel/warning.png",
                                "modules/QuotingTool/html_icons_iconlabel/wechat.png",
                                "modules/QuotingTool/html_icons_iconlabel/weibo.png",
                                "modules/QuotingTool/html_icons_iconlabel/weixin.png",
                                "modules/QuotingTool/html_icons_iconlabel/whatsapp.png",
                                "modules/QuotingTool/html_icons_iconlabel/wheelchair.png",
                                "modules/QuotingTool/html_icons_iconlabel/wheelchair-alt.png",
                                "modules/QuotingTool/html_icons_iconlabel/wifi.png",
                                "modules/QuotingTool/html_icons_iconlabel/wikipedia-w.png",
                                "modules/QuotingTool/html_icons_iconlabel/window-close.png",
                                "modules/QuotingTool/html_icons_iconlabel/window-close-o.png",
                                "modules/QuotingTool/html_icons_iconlabel/window-maximize.png",
                                "modules/QuotingTool/html_icons_iconlabel/window-minimize.png",
                                "modules/QuotingTool/html_icons_iconlabel/window-restore.png",
                                "modules/QuotingTool/html_icons_iconlabel/windows.png",
                                "modules/QuotingTool/html_icons_iconlabel/won.png",
                                "modules/QuotingTool/html_icons_iconlabel/wordpress.png",
                                "modules/QuotingTool/html_icons_iconlabel/wpbeginner.png",
                                "modules/QuotingTool/html_icons_iconlabel/wpexplorer.png",
                                "modules/QuotingTool/html_icons_iconlabel/wpforms.png",
                                "modules/QuotingTool/html_icons_iconlabel/wrench.png",
                                "modules/QuotingTool/html_icons_iconlabel/xing.png",
                                "modules/QuotingTool/html_icons_iconlabel/xing-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/y-combinator.png",
                                "modules/QuotingTool/html_icons_iconlabel/y-combinator-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/yahoo.png",
                                "modules/QuotingTool/html_icons_iconlabel/yc.png",
                                "modules/QuotingTool/html_icons_iconlabel/yc-square.png",
                                "modules/QuotingTool/html_icons_iconlabel/yelp.png",
                                "modules/QuotingTool/html_icons_iconlabel/yen.png",
                                "modules/QuotingTool/html_icons_iconlabel/yoast.png",
                                "modules/QuotingTool/html_icons_iconlabel/youtube.png",
                                "modules/QuotingTool/html_icons_iconlabel/youtube-play.png",
                                "modules/QuotingTool/html_icons_iconlabel/youtube-square.png",
                            ];
                            for(var i=0;i<$scope.iconsLabel.length;i++){
                                $scope.iconsLabel[i]=$.parseJSON($('#js_config').text()).base+$scope.iconsLabel[i];
                            }
                            var form = html.find('.form');
                            if(info == '' || typeof info == 'undefined'){
                                info = {}
                            }else{
                                if(typeof info == 'string'){
                                    info = JSON.parse(info);
                                }
                            }
                            var iconChecked = info['urlIcon'];
                            $scope.iconChecked = iconChecked;
                            registerFormatOption(form);
                            registerfontweigth(form);
                            AppHelper.showModalWindow(html);
                        });
                        break;
                    case $rootScope.app.data.blocks.team_member:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            // Before open modal
                            var objTable = container.find('table:first');

                            var info = parseInt(objTable.data('info')) ;
                            if (typeof info === 'undefined') {
                                info = {};
                            }
                            //console.log( $rootScope.field_image_selected);
                            AppHelper.showModalWindow(html, '#', function () {
                                html.find('[name ="numberMember"]').val(info);
                                // Trigger click
                                html.on('click', '.btn-submit-teammember', function () {
                                    var numberMember  = html.find('[name ="numberMember"]').val();
                                    if(numberMember<1){
                                        numberMember=1;
                                    };
                                    var htmltdraw = '<td valign="top" class="td-last" style="text-align: center;max-width: 191px">\n' +
                                        '                                        <table class="quoting_tool-cke-keep-element" style="width: 100%">\n' +
                                        '                                            <tr>\n' +
                                        '                                                <td><img  style="width: 191px;height: 150px;max-height: 150px;max-width: 191px"\n' +
                                        '                                                          src="modules/QuotingTool/resources/images/avatar.png" alt=""></td>\n' +
                                        '                                            </tr>\n' +
                                        '                                            <tr>\n' +
                                        '                                                  <td style="max-width: 100%;padding-top: 20px"><p style="max-width: 100%"> Text goes here!</p></td>' +
                                        '                                            </tr>\n' +
                                        '                                        </table>\n' +
                                        '                                    </td>';
                                    var htmlbody=objTable.find('tbody:first').clone();
                                    if (numberMember > info && numberMember<=3){
                                        if( jQuery(htmlbody).children('tr').length==0){
                                            jQuery(htmlbody).append('<tr>' +
                                                ' <td style="text-align: center;max-width: 191px">' +
                                                '<table class="quoting_tool-cke-keep-element" style="width: 100%;">' +
                                                '<tbody>' +
                                                '<tr class="tr-last">' +
                                                htmltdraw
                                                +'</tr>' +
                                                '</tbody>' +
                                                '</table>' +
                                                '</td>' +
                                                '</tr>');
                                        }else {
                                            for(var i=info;i<numberMember;i++){
                                                jQuery(htmlbody).children('tr:last').find('table').find('tbody').find('.tr-last').append(htmltdraw);
                                            }
                                        }
                                    }
                                    if (numberMember > info && numberMember>3){
                                        for(var i=info;i<numberMember;i++){
                                            console.log(i+'\t'+numberMember);
                                            if(jQuery(htmlbody).children('tr:last').find('table').find('.td-last').length==3){
                                                jQuery(htmlbody).append('<tr> <td style="text-align: center;max-width: 191px"><table class="quoting_tool-cke-keep-element" style="width: 100%"><tbody><tr class="tr-last">' +
                                                    htmltdraw
                                                    +'</tr></tbody></table></td></tr>');
                                            }else if(jQuery(htmlbody).children('tr:last').find('tbody').find('table').find('.td-last').length<3){
                                                jQuery(htmlbody).children('tr:last').find('.tr-last').append(htmltdraw)
                                            }
                                        }
                                    }
                                    if (numberMember < info){
                                        for(var i=numberMember;i<info;i++){
                                            jQuery(htmlbody).children('tr:last').find('table').find('.td-last:last').remove();
                                            if(jQuery(htmlbody).children('tr:last').find('table').find('.td-last').length==0){
                                                jQuery(htmlbody).children('tr:last').remove();
                                            }
                                        }
                                    }
                                    objTable.data('info', numberMember);
                                    objTable.attr('data-info', numberMember);
                                    objTable.html(htmlbody);
                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    //TASKID: 12957 - DEV: haihoang - DATE: 08/10/2018 - START
                    //NOTES: add settings ('edge to edge') for table, text blocks
                    case $rootScope.app.data.blocks.table:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            // Before open modal
                            var contenteditable = editable.closest('.content-container');
                            //NOTE: fix already have settings to default
                            editable.find('[contenteditable="true"]').css({
                                'margin-left': '',
                                'margin-right': ''
                            });
                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTextField = container.find('table');
                            var info = objTextField.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            html.find('[name="edge_to_edge"]').prop({
                                'checked': info['edge_to_edge'] ? info['edge_to_edge'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var edge_to_edge = html.find('[name="edge_to_edge"]');
                                    info['edge_to_edge'] = edge_to_edge.is(':checked');

                                    // set width for image
                                    if (info['edge_to_edge']) {
                                        contenteditable.css({
                                            'margin-left': '-57.6px',
                                            'margin-right': '-57.6px'
                                        });
                                    } else {
                                        contenteditable.css({
                                            'margin-left': '',
                                            'margin-right': ''
                                        });
                                    }
                                    // Save settings
                                    objTextField.attr('data-info', JSON.stringify(info));

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    case $rootScope.app.data.blocks.text:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            // Before open modal
                            var contenteditable = editable.closest('.content-container');
                            //NOTE: fix already have settings to default
                            editable.find('[contenteditable="true"]').css({
                                'margin-left': '',
                                'margin-right': ''
                            });
                            if (!contenteditable || contenteditable.length == 0) {
                                return;
                            }

                            var objTextField = contenteditable;
                            var info = objTextField.data('info');

                            if (typeof info === 'undefined') {
                                info = {};
                            }

                            html.find('[name="edge_to_edge"]').prop({
                                'checked': info['edge_to_edge'] ? info['edge_to_edge'] : false
                            });

                            AppHelper.showModalWindow(html, '#', function () {
                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var edge_to_edge = html.find('[name="edge_to_edge"]');
                                    info['edge_to_edge'] = edge_to_edge.is(':checked');

                                    // set width for image
                                    if (info['edge_to_edge']) {
                                        contenteditable.css({
                                            'margin-left': '-57.6px',
                                            'margin-right': '-57.6px'
                                        });
                                    } else {
                                        contenteditable.css({
                                            'margin-left': '',
                                            'margin-right': ''
                                        });
                                    }
                                    // Save settings
                                    objTextField.attr('data-info', JSON.stringify(info));

                                    AppHelper.hideModalWindow();
                                });
                            });
                        });
                        break;
                    //TASKID: 12957 - DEV: haihoang - DATE: 08/10/2018 - END
                    default:
                        AppUtils.loadTemplate($scope, focus.setting_template, true, function (html) {
                            var form = html.find('.form');
                            var key = null;
                            var value = null;

                            var fields = form.find('input, select, textarea');
                            for (var i = 0; i < fields.length; i++) {
                                var field = fields[i];
                                var objField = $(field);
                                var type = field.type;

                                switch (type) {
                                    case 'checkbox':
                                        key = objField.attr('name');
                                        value = info[key] ? info[key] : false;
                                        objField.prop({
                                            'checked': value
                                        });
                                        break;
                                    case 'text':
                                        if(objField.hasClass("color_picker") || objField.hasClass("fore_color")) {
                                            var classObj =  objField.attr('class');
                                            registerColorPicker(form,classObj);
                                            key = objField.attr('name');
                                            value = info[key] ? info[key] : '';
                                            objField.css("background-color", value);
                                            objField.val(value);
                                        }
                                        break;
                                    case 'select-one':
                                        if(objField.hasClass("fontFamSelect")){
                                            value = info['fontName'] ? info['fontName'] : '';
                                            objField.find('option[value="' + value + '"]').attr('selected', 'selected');
                                        }else if(objField.hasClass("fontSizeSelect")){
                                            value = info['fontSize'] ? info['fontSize'] : '';
                                            objField.find('option[value="' + value + '"]').attr('selected', 'selected');
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                            var fontWeight = form.find('.cke_button');
                            for (var i = 0; i < fontWeight.length; i++) {
                                var field = fontWeight[i];
                                var objField = $(field);
                                key = objField.attr('title');
                                value = info[key] ? info[key] : false;
                                if(value=='true') {
                                    if($(objField).hasClass('cke_button_off')){
                                        $(objField).removeClass('cke_button_off').addClass('cke_button_on');
                                    }
                                    if($(objField).hasClass('align_off')){
                                        $(objField).removeClass('align_off').addClass('align_on');
                                    }
                                }
                            }
                            registerFormatOption(form);
                            registerfontweigth(form);
                            AppHelper.showModalWindow(html);
                        });
                        break;
                }
            };
            var registerFormatOption = function (form) {
                var valEditable = jQuery(form).find("input[name='editable']");
                var FormatOption = jQuery(form).find(".formatOption");
                if(valEditable.is(":checked")) {
                    FormatOption.css("display","none");
                }else {
                    FormatOption.css("display","block");
                }
                valEditable.on("click",function () {
                    if(valEditable.is(":checked")) {
                        FormatOption.css("display","none");
                    }else {
                        FormatOption.css("display","block");
                    }
                })};
            var registerfontweigth = function (form) {
                jQuery(form).find('.cke_button').on('click',function () {
                    var focus = $(this);
                    if(focus.hasClass('cke_button_off')) {
                        focus.removeClass('cke_button_off').addClass('cke_button_on');
                        focus.css('background',"#C2D5F2")
                    }else if(focus.hasClass('cke_button_on')) {
                        focus.removeClass('cke_button_on').addClass('cke_button_off');
                        focus.css('background',"#FFFFFF")
                    }

                    if(focus.hasClass('align_off')) {
                        jQuery(form).find('.align_on').removeClass('align_on').addClass('align_off');
                        focus.removeClass('align_off').addClass('align_on');
                    }else if(focus.hasClass('align_on')) {
                        focus.removeClass('align_on').addClass('align_off');
                    }
                })
            };
            var  registerColorPicker = function(form,input){
                jQuery(form).find('input[name="'+input+'"]').ColorPicker({
                    color: '#0000ff',
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        jQuery(colpkr).css({'zIndex': '10010'});
                        // jQuery(colpkr).css({'position': 'fixed'});
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        jQuery('input[name="'+input+'"]').css('backgroundColor', '#' + hex);
                        jQuery('input[name="'+input+'"]').val('#' + hex);
                    }
                }).bind('keyup', function(){
                    jQuery(this).ColorPickerSetColor(this.value);
                });
            };

            $scope.changeRelatedModule = function () {
                $scope.settings.related_module.item_fields = [];
                $scope.settings.related_module.item_fields.length = 0;
                $('#settings_related_module_fields').select2("val", "");
            };
            $scope.changeRelatedModuleRecord = function () {
                $scope.settings.create_related_record.item_fields = [];
                $scope.settings.create_related_record.item_fields.length = 0;
                $('#settings_create_related_record_fields').select2("val", "");
            };
            $scope.changeFISelectedModule = function () {
                $rootScope.app.model.module = $rootScope.app.data.selectedModule.name;
                $rootScope.app.data.selectedModuleField = $rootScope.app.data.selectedModule ? $rootScope.app.data.selectedModule.fields[0] : null;
                $rootScope.app.data.selectedRelatedModule = $rootScope.app.data.selectedModule ? $rootScope.app.data.selectedModule.related_modules[0] : null;
                $rootScope.app.data.selectedRelatedModuleField = $rootScope.app.data.selectedRelatedModule ? $rootScope.app.data.selectedRelatedModule.fields[0] : null;

                // link module
                $rootScope.app.data.selectedRelatedBlockModule = $rootScope.app.data.selectedRelatedModule ? $rootScope.app.data.selectedModule.link_modules[0] : null;
                $rootScope.app.data.selectedRelatedBlockModuleField = $rootScope.app.data.selectedRelatedBlockModule ? $rootScope.app.data.selectedRelatedBlockModule.fields[0] : null;

                // Change picklist values
                if ($rootScope.app.data.idxModules[$rootScope.app.model.module]) {
                    $rootScope.app.data.picklistField.options = $rootScope.app.data.idxModules[$rootScope.app.model.module]['picklist'];
                }
                if ($rootScope.app.model.id) {
                    // Update module to db
                    Template.save({
                        record: $rootScope.app.model.id,
                        primary_module: $rootScope.app.model.module
                    }, function (response) {
                        if (response.success == true) {
                            var data = response.result;

                            if (!$rootScope.app.model.id) {
                                $rootScope.app.model.id = data['id'];
                            }
                        } else {
                            AppHelper.showMessage(response.error.message)
                        }
                    });
                }
            };
            /**
             * Fn - changeSelectedModule
             * @link http://www.grobmeier.de/angular-js-ng-select-and-ng-options-21112012.html
             */
            $rootScope.changeSelectedFIModule = function () {
                alert('choice');
                $rootScope.app.model.module = $rootScope.app.data.selectedModule.name;
                $rootScope.app.data.selectedModuleField = $rootScope.app.data.selectedModule ? $rootScope.app.data.selectedModule.fields[0] : null;
                $rootScope.app.data.selectedRelatedModule = $rootScope.app.data.selectedModule ? $rootScope.app.data.selectedModule.related_modules[0] : null;
                $rootScope.app.data.selectedRelatedModuleField = $rootScope.app.data.selectedRelatedModule ? $rootScope.app.data.selectedRelatedModule.fields[0] : null;

                // link module
                $rootScope.app.data.selectedRelatedBlockModule = $rootScope.app.data.selectedRelatedModule ? $rootScope.app.data.selectedModule.link_modules[0] : null;
                $rootScope.app.data.selectedRelatedBlockModuleField = $rootScope.app.data.selectedRelatedBlockModule ? $rootScope.app.data.selectedRelatedBlockModule.fields[0] : null;

                // Change picklist values
                if ($rootScope.app.data.idxModules[$rootScope.app.model.module]) {
                    $rootScope.app.data.picklistField.options = $rootScope.app.data.idxModules[$rootScope.app.model.module]['picklist'];
                }
                if ($rootScope.app.model.id) {
                    // Update module to db
                    Template.save({
                        record: $rootScope.app.model.id,
                        primary_module: $rootScope.app.model.module
                    }, function (response) {
                        if (response.success == true) {
                            var data = response.result;

                            if (!$rootScope.app.model.id) {
                                $rootScope.app.model.id = data['id'];
                            }
                        } else {
                            AppHelper.showMessage(response.error.message)
                        }
                    });
                }
            };
            $rootScope.changeFieldImagesSetting = function ($event) {
                // Close modal
                AppHelper.hideModalWindow();
            },
            /**
             * Fn - changeSetting
             * @param $event
             */
                $rootScope.changeSetting = function ($event) {
                    var target = $($event.target);
                    var modal = target.closest('#globalmodal');
                    var form = modal.find('.form');
                    // var settingBox = modal.find('.modal-settings');
                    // var dataType = settingBox.data('type');
                    // var dataTarget = settingBox.data('target');

                    var key = null;
                    var value = null;

                    if (!$rootScope.app.last_focus_item_setting || $rootScope.app.last_focus_item_setting.length == 0) {
                        // For blocks and others
                        return;
                    }

                    // if OK
                    var newInfo = {};
                    var fields = form.find('input, select, textarea');

                    for (var i = 0; i < fields.length; i++) {
                        var field = fields[i];
                        var objField = $(field);
                        var type = field.type;

                        switch (type) {
                            case 'checkbox':
                                key = objField.attr('name');
                                value = objField.is(':checked');
                                break;
                            case 'text':
                                if(objField.hasClass("color_picker") || objField.hasClass("fore_color")) {
                                    key = objField.attr('name');
                                    value = objField.val();
                                }
                                break;
                            default:
                                break;
                        }

                        newInfo[key] = value;
                    }
                    var fontWeight = form.find('.cke_button');
                    for (var i = 0; i < fontWeight.length; i++) {
                        var field = fontWeight[i];
                        var objField = $(field);
                        var type = field.type;
                        key = objField.attr('title');
                        if($(objField).hasClass('cke_button_on')) {
                            value = 'true';
                        }else if ($(objField).hasClass('cke_button_off')) {
                            value = 'false';
                        }
                        if($(objField).hasClass('align_on')) {
                            value = 'true';
                        }else if ($(objField).hasClass('align_off')) {
                            value = 'false';
                        }
                        newInfo[key] = value;
                    }

                    //font family
                    var fontfamily =form.find(".fontFamSelect").val();
                    newInfo['fontName'] = fontfamily;
                    //font size
                    var fontSize = form.find('.fontSizeSelect').val();
                    newInfo['fontSize'] = fontSize;

                    // Update for all item focus
                    $rootScope.app.last_focus_item_setting.each(function () {
                        var thisFocus = $(this);
                        var container = thisFocus.closest(".content-container");
                        var parentFocus = thisFocus.parent();
                        var markRequired = parentFocus.find('.mark-required');
                        var info = thisFocus.data('info');

                        if (typeof info === 'undefined') {
                            info = {};
                        }

                        $.extend(info, newInfo);
                        thisFocus.attr('data-info', JSON.stringify(info));


                        var editable = newInfo['editable'];
                        if(editable === false) {
                            if(newInfo['color_picker'] == ''){
                                newInfo['color_picker'] = 'rgba(255,255,255,0)';
                            }
                            if(newInfo['fore_color'] == ''){
                                newInfo['fore_color'] = '#808080';
                            }
                            //background-color
                            thisFocus.css('background-color', newInfo['color_picker']);
                            thisFocus.css('color', newInfo['fore_color']);

                            //font-weight
                            if(newInfo['Bold']=='true'){
                                thisFocus.css("font-weight", "bold");
                            }else if(newInfo['Bold']=='false'){
                                thisFocus.css("font-weight", "normal");
                            }

                            //font-style
                            if(newInfo['Italic']=='true'){
                                thisFocus.css("font-style", "italic");
                            }else if(newInfo['Italic']=='false'){
                                thisFocus.css("font-style", "normal");
                            }

                            //text-decoration
                            if(newInfo['Underline']=='true'){
                                thisFocus.css("text-decoration", "underline");
                            }else if(newInfo['Underline']=='false'){
                                thisFocus.css("text-decoration", "none");
                            }

                            // Align text
                            if(newInfo['AlignLeft']=='true'){
                                thisFocus.css("text-align", "left");
                            }else if(newInfo['Center']=='true'){
                                thisFocus.css("text-align", "center");
                            }else if(newInfo['AlignRight']=='true') {
                                thisFocus.css("text-align", "right");
                            }else{
                                thisFocus.css("text-align", "left");
                            }

                            // font-family
                            thisFocus.css("font-family", newInfo['fontName']);

                            // font-size
                            thisFocus.css("font-size",newInfo['fontSize']);
                        }else {
                            thisFocus.css('background-color', "rgba(255,255,255,0)");
                            thisFocus.css("font-weight", "normal");
                            thisFocus.css("font-style", "normal");
                            thisFocus.css("text-decoration", "none");
                            thisFocus.css("text-align", "left");
                            thisFocus.css("font-family", '"Helvetica Neue", Helvetica, Arial, sans-serif');
                            thisFocus.css("font-size","13px");
                        }
                        // Mandatory field
                        if (info['required']) {
                            if (!markRequired || markRequired.length == 0) {
                                thisFocus.after('<span class="mark-required">*</span>');
                            }
                        } else {
                            markRequired.remove();
                        }

                        //allow to edit
                        if (info['editable']) {
                            container.resizable( "disable" );
                            container.css("opacity","1.35")
                        } else {
                            container.resizable("enable");
                        }

                    });

                    // Close modal
                    AppHelper.hideModalWindow();
                };

            $rootScope.changeTableBlockTheme = function ($event, theme) {
                $event.preventDefault();

                $scope.settings.pricing_table.theme = theme;
            };
            $rootScope.changeTableBlockSize = function (size) {
                $scope.settings.pricing_table.size = size.settings.pricing_table.size;
            };
            $rootScope.changeLinkModuleTheme = function ($event, theme) {
                $event.preventDefault();
                $scope.settings.related_module.theme = theme;
            };
            $rootScope.changeLinkRecordTheme = function ($event, theme) {
                $event.preventDefault();
                $scope.settings.create_related_record.theme = theme;
            };
            $rootScope.changeLinkModuleSize = function (size) {
                $scope.settings.related_module.size = size.settings.related_module.size;
            };
            $rootScope.changeCreateRelatedRecordSize = function (size) {
                $scope.settings.create_related_record.size = size.settings.create_related_record.size;
            };
            /**
             * Fn - $rootScope.registerEventFocusInput
             */
            $rootScope.registerEventFocusInput = function (container) {
                if (!container) {
                    container = $rootScope.app.container;
                }

                // With input
                var input = $(container).find('input[type="text"]');
                $.each(input, function () {
                    $(this).on('focus', function () {
                        var thisFocus = $(this);
                        $rootScope.app.last_focus_item = {
                            type: AppConstants.FOCUS_TYPE.INPUT,
                            focus: thisFocus
                        };
                    });
                });

                // With textarea
                var textarea = $(container).find('textarea');
                $.each(textarea, function () {
                    $(this).on('focus', function () {
                        var thisFocus = $(this);
                        $rootScope.app.last_focus_item = {
                            type: AppConstants.FOCUS_TYPE.TEXTAREA,
                            focus: thisFocus
                        };
                    });
                });

                // With CKEditor
                for (var name in CKEDITOR.instances) {
                    if (!CKEDITOR.instances.hasOwnProperty(name)) {
                        continue;
                    }

                    var thisCKEditor = CKEDITOR.instances[name];

                    (function (thisCKEditor) {
                        thisCKEditor.on('focus', function () {
                            $rootScope.app.last_focus_item = {
                                type: AppConstants.FOCUS_TYPE.CKEDITOR,
                                focus: thisCKEditor
                            };
                        });
                    })(thisCKEditor);
                }

                // With contenteditable
                var contenteditable = $(container).find('[contenteditable]');
                $.each(contenteditable, function () {
                    $(this).on('focus', function () {
                        var thisFocus = $(this);
                        $rootScope.app.last_focus_item = {
                            type: AppConstants.FOCUS_TYPE.CONTENTEDITABLE,
                            focus: thisFocus
                        };
                    });
                });

            };

            /**
             * Fn - registerEventFocusPage
             */
            $rootScope.registerEventFocusPage = function () {
                // Init (first time)
                var pages = $(document).find('.quoting_tool-content').first();
                if (pages.length > 0) {
                    // Init first page
                    $rootScope.app.last_focus_page = pages;
                }

                // When click to switch the pages
                $rootScope.app.container.on('click', '.quoting_tool-content', function (event) {
                    var target = event.target;
                    var isRemovePage = $(target).hasClass('quoting_tool-icon-remove-page');

                    if (!isRemovePage && !$rootScope.app.last_focus_page.is($(this))) {
                        $rootScope.app.last_focus_page = $(this);
                    }
                });
            };

            /**
             * Fn - registerEventSupportOptions
             * @param focus
             */
            $rootScope.registerEventSupportOptions = function (focus) {
                if (!focus.hasClass('content-container')) {
                    focus = focus.find('.content-container');
                }

                // Show support actions
                focus.on('mouseenter', function () {
                    var thisInstance = $(this);
                    thisInstance.addClass('quoting_tool-border-dotted');
                    thisInstance.find('> .quoting_tool-btn-options').show();
                });

                // Hide support actions
                focus.on('mouseleave', function () {
                    var thisInstance = $(this);
                    thisInstance.removeClass('quoting_tool-border-dotted');
                    thisInstance.find('.quoting_tool-btn-options').hide();
                });
            };

            /**
             * Fn - registerEventCoverPageSupportOptions
             */
            $rootScope.registerEventCoverPageSupportOptions = function () {
                // Show support actions
                $(document).on('mouseenter', '.quoting_tool-cover-page', function () {
                    var thisFocus = $(this);
                    thisFocus.find('> .quoting_tool-cover_page-options').show();
                });

                // Hide support actions
                $(document).on('mouseleave', function () {
                    var thisFocus = $(this);
                    thisFocus.find('> .quoting_tool-cover_page-options').hide();
                });
            };

            /**
             * @param focus
             */
            $rootScope.calculateWidgetPosition = function (focus) {
                var container = focus.closest('.content-container.block-handle');
                var containerHeight = container.height();
                // Apply css for the draggable object
                var objAction = focus.find('.quoting_tool-draggable-object');
                var objActionWidth = objAction.width();
                var objActionHeight = objAction.height();
                var marginTop = 0;
                var tmp = 2;
                var preElement = focus.prev('.content-container.quoting_tool-draggable');

                if (preElement && preElement.length > 0) {
                    marginTop = focus[0].offsetTop - (preElement[0].offsetTop + preElement.height()) + tmp;
                } else {
                    marginTop = '-' + (containerHeight - focus[0].offsetTop + tmp);
                }
                // fix bug position of field incorrect
                var imgElement = container.find("img");
                if(imgElement && imgElement.length >0) {
                    imgElement.css("width", imgElement.width());
                }
                // Update css for the Object
                objAction.css({
                    'margin-top': parseInt(marginTop),
                    'margin-left': focus[0].offsetLeft,
                    'width': ((objActionWidth >= 26) ? objActionWidth : 26), // Fix checkbox block on PDF
                    'height': objActionHeight
                });

                var next = focus.next('.content-container.quoting_tool-draggable');

                if (next && next.length > 0) {
                    $rootScope.calculateWidgetPosition(next);
                }
            };

            /**
             * Fn - registerMoveOnContainer
             * Allow the widget moveable on the container
             *
             * @param focus
             */
            $rootScope.registerMoveOnContainer = function (focus) {
                focus.draggable({
                    handle: 'i.icon-move, i.icon-align-justify, .quoting_tool-widget-signature-image, .quoting_tool-widget-secondary_signature-image',
                    scope: 'add-widget-dropzone',
                    revert: 'invalid',
                    cursor: 'move',
                    stop: function (event, ui) {
                        var objContext = $(ui.helper.context);
                        // Current focus
                        $rootScope.calculateWidgetPosition(objContext);
                    }
                });
            };

            /**
             * Fn - registerDropToContainer
             * @param focus
             */
            $rootScope.registerDropToContainer = function (focus) {
                focus.droppable({
                    scope: 'add-widget-dropzone',
                    tolerance: 'pointer',
                    drop: function (event, ui) {
                        var thisPosition = $(focus[0]).offset();
                        var positionX = thisPosition.left;
                        var positionY = thisPosition.top;
                        var moveX = 0;
                        var moveY = 0;

                        if (ui.helper.hasClass('quoting_tool-drag-widget-component-to-content')) {
                            var id = ui.helper.data('id');

                            moveX = $rootScope.dragOffset.left;
                            moveY = $rootScope.dragOffset.top;

                            // Change the coordinate before append
                            var css = {
                                top: (moveY - positionY) + 'px',
                                left: (moveX - positionX) + 'px'
                            };

                            // Remove origin element
                            ui.helper.replaceWith('');

                            $timeout(function () {
                                // Trigger mouse position after drop widget
                                var blockContainer = $($rootScope.currentPosition.target).closest('.content-container.block-handle');
                                var args = {
                                    id: id,
                                    css: css,
                                    container: blockContainer
                                };
                                $rootScope.$broadcast('$evtAddWidget', args);
                            }, 10);
                        } else {
                            // Move a widget from a block to another block
                            var currentContainer = $(ui.draggable).closest('.content-container.block-handle');

                            if (!focus.is(currentContainer)) {
                                moveX = $rootScope.currentPosition.clientX;
                                moveY = $rootScope.currentPosition.clientY;

                                // Change the coordinate before append
                                ui.draggable.css({
                                    top: (moveY - positionY) + 'px',
                                    left: (moveX - positionX) + 'px'
                                });
                                focus.append(ui.draggable);

                                var objActions1 = currentContainer.find('.content-container.quoting_tool-draggable');
                                var objAction1 = null;
                                for (var i = 0; i < objActions1.length; i++) {
                                    objAction1 = $(objActions1[i]);
                                    // Re-calculate all widget positions
                                    $rootScope.calculateWidgetPosition(objAction1);
                                }

                                var objActions2 = focus.find('.content-container.quoting_tool-draggable');
                                var objAction2 = null;
                                for (var i = 0; i < objActions2.length; i++) {
                                    objAction2 = $(objActions2[i]);
                                    // Re-calculate all widget positions
                                    $rootScope.calculateWidgetPosition(objAction2);
                                }
                            }
                        }
                    }
                });
            };

            /**
             * @param templateId
             */
            $rootScope.loadTemplate = function (templateId) {
                var body = QuotingToolUtils.base64Decode($rootScope.app.model.body);

                var content = $(body);

                $rootScope.app.container.html(content);

                /**
                 * Bind event for template
                 * @link http://stackoverflow.com/questions/18618069/angularjs-event-binding-in-directive-template-doesnt-work-if-mouseout-used-but
                 */
                $compile(content.contents())($scope);

                var contentMain = $rootScope.app.container.find('.quoting_tool-content-main');
                var elementType = AppConstants.COMPONENT_TYPE.BLOCK;

                var container = content.find('.content-container').each(function () {
                    var html = $(this);
                    // var myContentContainer = thisFocus.closest('.content-container');
                    var myDataId = $(html[0]).data('id');
                    var component = $rootScope.app.data.blocks.init;

                    // Match with blocks
                    for (var b in $rootScope.app.data.blocks) {
                        if (!$rootScope.app.data.blocks.hasOwnProperty(b)) {
                            continue;
                        }

                        var bItem = $rootScope.app.data.blocks[b];
                        if (bItem.template == myDataId) {
                            component = bItem;
                            elementType = AppConstants.COMPONENT_TYPE.BLOCK;
                        }
                    }

                    // Match with widget
                    for (var w in $rootScope.app.data.widgets) {
                        if (!$rootScope.app.data.widgets.hasOwnProperty(w)) {
                            continue;
                        }

                        var wItem = $rootScope.app.data.widgets[w];
                        if (wItem.template == myDataId) {
                            component = wItem;
                            elementType = AppConstants.COMPONENT_TYPE.WIDGET;
                        }
                    }

                    // Integrate CKEditor to the element
                    html.find('[contenteditable]').each(function () {
                        var thisFocus = $(this);
                        if (typeof thisFocus.attr('id') === 'undefined') {
                            thisFocus.attr('id', QuotingToolUtils.getRandomId());
                        }
                        if(thisFocus.is('input') == true || thisFocus.is('span') == true || thisFocus.is('textarea') == true){
                            return;
                        }

                        // Merge settings
                        var settings = $.extend({}, AppToolbar.base_editor.settings, component.settings);

                        var editor = thisFocus.ckeditor(settings, function () {
                            // IFrame cke-realelement
                            var CKEIframes = thisFocus.find('img.cke_iframe');
                            var CKEIframe = null;
                            var frame = null;

                            for (var ckf = 0; ckf < CKEIframes.length; ckf++) {
                                CKEIframe = $(CKEIframes[ckf]);
                                frame = $(decodeURIComponent(CKEIframe.data('cke-realelement')));
                                CKEIframe.attr('src', QuotingToolUtils.getYoutubeThumbnailFromIframe(frame));
                            }

                            AppHelper.customKeyPress(editor);
                            // Custom focus
                            AppHelper.customFocus(editor);
                            // Context menu
                            $rootScope.customCKEditorContextMenu(editor);

                            // Text change:
                            editor.on('blur', function () {
                                // Refresh heading indexing
                                if (component == $rootScope.app.data.blocks.heading) {
                                    $rootScope.refreshHeadings();
                                }
                            });
                        });
                    });

                    if (elementType == AppConstants.COMPONENT_TYPE.BLOCK) {
                        $rootScope.registerDropToContainer(html);

                        // Sortable
                        contentMain.sortable({
                            handle: 'i.icon-move, i.icon-align-justify, .doc-block__control--drag',
                            axis: 'y',
                            stop: function (event, ui) {
                                var prev = $(document).find(ui.item).prev();
                                if (!ui.item.hasClass("quoting_tool-drag-component-to-content")) {
                                    return;
                                }

                                var id = ui.item.data('id');
                                // Remove origin element
                                ui.item.replaceWith('');

                                for (var k in $rootScope.app.data.blocks) {
                                    if (!$rootScope.app.data.blocks.hasOwnProperty(k)) {
                                        continue;
                                    }

                                    if ($rootScope.app.data.blocks[k].layout.id == id) {
                                        var args = {
                                            id: $rootScope.app.data.blocks[k].layout.id,
                                            // type: 'after',
                                            // focus: prev
                                        };
                                        // $rootScope.$broadcast('$evtAddBlock', args);

                                        if (prev.length > 0) {
                                            args['type'] = 'after';
                                            args['focus'] = prev;
                                        }
                                        else {
                                            args['type'] = 'prepend';
                                        }

                                        // Add block
                                        $rootScope.$broadcast('$evtAddBlock', args);

                                        break;
                                    }
                                }
                            }
                        });
                    } else if (elementType == AppConstants.COMPONENT_TYPE.WIDGET) {
                        if(html.find('[name="checkbox"]').length == 0 || html.find('[name="date_signed"]').length == 0) {
                            AppHelper.resizeable(html);
                        }
                        $rootScope.registerMoveOnContainer(html);
                    }

                    $rootScope.registerEventSupportOptions(html);
                });
            };

            /**
             * Drag content component
             *
             * How to check if click event is already bound - JQuery
             * @link http://stackoverflow.com/questions/6361465/how-to-check-if-click-event-is-already-bound-jquery\
             *
             * Fix draggable on overflow
             * @link http://stackoverflow.com/questions/811037/jquery-draggable-and-overflow-issue
             */
            $rootScope.registerEventDragAndDropBlocks = function () {
                // Drag block to container
                $rootScope.app.container.droppable({
                    drop: function (e, ui) {
                        var dragObject = $(ui.draggable);
                        var dataId = dragObject.data('id');
                        // Special blocks
                        if (dataId == $rootScope.app.data.blocks.cover_page.layout.id
                            || dataId == $rootScope.app.data.blocks.page_header.layout.id
                            || dataId == $rootScope.app.data.blocks.page_footer.layout.id) {
                            var args = {
                                id: dataId
                            };

                            $rootScope.$broadcast('$evtAddBlock', args);
                        }
                    }
                });
            };

            // var initTemplate = function () {
            //     // First block in App body
            //     var args = {id: $rootScope.app.data.blocks.heading.layout.id};
            //     $rootScope.$broadcast('$evtAddBlock', args);
            // };

            $rootScope.watchCurrentPosition = function () {
                // Get current mouse position (coordinate)
                $(document).on('mousemove', '.content-container.block-handle', function (event) {
                    $rootScope.currentPosition = event;
                });
            };

            $rootScope.saveTemplate = function ($event) {
                $event.preventDefault();

                // Validate form data
                if (!$rootScope.app.model.filename) {
                    AppHelper.showMessage($translate.instant('Document Name is missing'));
                    return false;
                }

                if (!$rootScope.app.form.isValid2()) {
                    AppHelper.showMessage($translate.instant('Form invalid'));
                    return false;
                }

                $rootScope.app.progressIndicatorElement.progressIndicator({
                    'message': $translate.instant('Processing...'),
                    'position': 'html',
                    'blockInfo': {
                        'enabled': true
                    },
                    'mode': 'show'
                });

                var htmlContainer = $rootScope.app.container.clone();

                /**
                 * Fix placeholder
                 * @link http://stackoverflow.com/questions/11324559/jquery-if-div-contains-this-text-replace-that-part-of-the-text
                 */
                htmlContainer.find('[placeholder]').each(function () {
                    var thisFocus = $(this);
                    if (thisFocus.hasClass('placeholder')) {
                        var placeholderText = thisFocus.attr('placeholder');
                        var elem = thisFocus.find(':contains("' + placeholderText + '")');
                        elem.text(elem.text().replace(placeholderText, ''));
                    }
                });

                // Destroy with clone DOM element
                htmlContainer.find('.ui-resizable-handle').remove();
                htmlContainer.find('*')
                    .removeAttributes([])
                    .removeClasses(['focus-contenteditable']);

                var htmlBody = htmlContainer.html();
                var htmlMain = htmlContainer.find('.quoting_tool-content:not([data-page-name="cover_page"])');
                var htmlHeader = '';
                var htmlFooter = '';

                // Header & Footer
                if (htmlMain.length > 0) {
                    var tmpHtmlHeader = $($(htmlMain[0]).find('.quoting_tool-content-header')[0]).find('.content-editable');
                    var tmpHtmlFooter = $($(htmlMain[0]).find('.quoting_tool-content-footer')[0]).find('.content-editable');

                    if (tmpHtmlHeader.length > 0) {
                        htmlHeader = $(tmpHtmlHeader[0]).html();
                    }
                    if (tmpHtmlFooter.length > 0) {
                        htmlFooter = $(tmpHtmlFooter[0]).html();
                    }
                }

                var htmlContent = '';
                // Clone the content to new object
                var myContent = htmlContainer.find('.quoting_tool-content-main, .quoting_tool-content-page-break');
                myContent.find('*')
                    .removeAttributes([/*'placeholder',*/ 'contenteditable', 'tabindex', 'role', 'aria-label', 'aria-describedby',
                        'spellcheck', 'data-cke-saved-src', 'input-change'])
                    .removeClasses(['ui-droppable', 'cke_editable_inline', 'cke_contents_ltr', 'quoting_tool-cke-keep-element',
                        'cke_editable', /*'cke_show_border', 'cke_show_borders', */'doc-block--pagebreak', 'doc-block--editable',
                        'quoting_tool-first-focus', 'quoting_tool-disable-margin', 'cke_focus', 'focus-contenteditable']);

                if (myContent.length > 0) {
                    for (var i = 0; i < myContent.length; i++) {
                        var c = myContent[i];
                        htmlContent += AppHelper.getContentFromHtml(c);
                    }
                }
                // IFrame cke-realelement
                var tmpHtmlContent = $('<div/>');
                tmpHtmlContent.html(htmlContent);
                // htmlContent = tmpHtmlContent.html();
                var CKEIframes = tmpHtmlContent.find('.cke_iframe');
                var CKEIframe = null;
                var frame = null;

                for (var ckf = 0; ckf < CKEIframes.length; ckf++) {
                    CKEIframe = $(CKEIframes[ckf]);
                    frame = $(decodeURIComponent(CKEIframe.data('cke-realelement')));
                    CKEIframe.after(frame);
                    CKEIframe.attr('src', QuotingToolUtils.getYoutubeThumbnailFromIframe(frame));
                    CKEIframe.wrap('<a href="' + frame.attr('src') + '" class="wrap_video_link"></a>');
                }

                // Check multi line
                htmlContent = tmpHtmlContent.html();

                // Header
                $rootScope.app.model.header = QuotingToolUtils.base64Encode(htmlHeader);
                // Footer
                $rootScope.app.model.footer = QuotingToolUtils.base64Encode(htmlFooter);
                // Body
                $rootScope.app.model.body = QuotingToolUtils.base64Encode(htmlBody);

                $rootScope.app.model.content = QuotingToolUtils.base64Encode(htmlContent);
                var stringSettingsLayout=
                    '{"orientation":"'+$rootScope.app.model.page_layout.id+'",' +
                    '"format":"'+$rootScope.app.model.page_format.id+'",'+
                    '"width":"'+$rootScope.app.model.page_width+'",'+
                    '"height":"'+$rootScope.app.model.page_height+'",'+
                    '"margin_left":"'+$rootScope.app.model.margin_left+'",'+
                    '"margin_right":"'+$rootScope.app.model.margin_right+'",'+
                    '"margin_top":"'+$rootScope.app.model.margin_top+'",'+
                    '"margin_bottom":"'+$rootScope.app.model.margin_bottom+'"}';
                $rootScope.app.model.layout_settings=stringSettingsLayout;
                if (!$rootScope.app.model.id) {
                    // Init template by primary info on db
                    Template.save({
                        record: 0,
                        filename: $rootScope.app.model.filename,
                        primary_module: $rootScope.app.model.module,
                        is_active : $rootScope.app.model.is_active
                    }, function (response) {
                        if (response.success == true) {
                            var data = response.result;
                            $rootScope.app.model.id = data['id'];
                            var attach = $rootScope.app.model.attachments;
                            var att = null;
                            for (var a = 0; a < attach.length; a++) {
                                att = attach[a];
                                if (typeof(att['$$hashKey']) !== 'undefined') {
                                    delete attach[a]['$$hashKey'];
                                }
                            }

                            // Update custom info to db
                            Template.save({
                                record: $rootScope.app.model.id,
                                description: $rootScope.app.model.description,
                                linkproposal: $rootScope.app.model.linkproposal,
                                expire_in_days: $rootScope.app.model.expire_in_days,
                                anwidget: $rootScope.app.model.anwidget,
                                createnewrecords: $rootScope.app.model.createnewrecords,
                                email_subject: QuotingToolUtils.base64Encode($rootScope.app.model.email_subject),
                                email_content: QuotingToolUtils.base64Encode($rootScope.app.model.email_content),
                                mapping_fields: $rootScope.app.model.mapping_fields,
                                attachments: attach,
                                owner:$rootScope.app.model.owner,
                                share_status:$rootScope.app.model.share_status,
                                share_to:$('[name="share_to"]').val(),
                                settings_layout: $rootScope.app.model.layout_settings,
                                custom_function: angular.toJson($rootScope.app.data.customFunctionData),
                                file_name: $rootScope.app.model.file_name
                            }, function (response) {
                                if (response.success == true) {
                                } else {
                                    AppHelper.showMessage(response.error.message)
                                }
                            });

                            // Update content to db
                            Template.save({
                                record: $rootScope.app.model.id,
                                body: $rootScope.app.model.body,
                                content: $rootScope.app.model.content,
                                header: $rootScope.app.model.header,
                                footer: $rootScope.app.model.footer,
                                history: true,
                                owner:$rootScope.app.model.owner,
                                share_status:$rootScope.app.model.share_status,
                                share_to:$('[name="share_to"]').val(),
                                settings_layout: $rootScope.app.model.layout_settings,
                                custom_function: angular.toJson($rootScope.app.data.customFunctionData),
                                file_name: $rootScope.app.model.file_name
                            }, function (response) {
                                if (response.success == true) {
                                } else {
                                    AppHelper.showMessage(response.error.message)
                                }
                            });

                            // Update settings to db
                            TemplateSetting.save({
                                record: $rootScope.app.model.id,
                                description: $rootScope.app.model.settings.description,
                                expire_in_days: $rootScope.app.model.settings.expire_in_days,
                                label_decline: $rootScope.app.model.settings.label_decline,
                                label_accept: $rootScope.app.model.settings.label_accept,
                                background: $rootScope.app.model.settings.background,
                                success_content:$rootScope.app.model.settings.success_content,
                                email_signed:$rootScope.app.model.settings.email_signed,
                                email_from_copy:$rootScope.app.model.settings.email_from_copy,
                                email_bcc_copy:$rootScope.app.model.settings.email_bcc_copy,
                                email_subject_copy:$rootScope.app.model.settings.email_subject_copy,
                                email_body_copy:$rootScope.app.model.settings.email_body_copy,
                                ignore_border_email:$rootScope.app.model.settings.ignore_border_email,
                                track_open : $rootScope.app.model.settings.track_open,
                                decline_message : $rootScope.app.model.settings.decline_message,
                                enable_decline_mess : $rootScope.app.model.settings.enable_decline_mess
                            }, function (response) {
                                if (response.success == true) {
                                } else {
                                    AppHelper.showMessage(response.error.message)
                                }
                            });

                            $scope.$apply(function () {
                                var newHistory = data['history'];

                                if (newHistory) {
                                    $rootScope.app.model.histories.push(newHistory);
                                    var args = {
                                        'history': newHistory
                                    };
                                    $rootScope.$broadcast('$evtSetSelectedHistory', args);
                                }
                            });

                            // Hide indicator
                            if($rootScope.app.data.readonlySelectModule==false){
                                $rootScope.app.data.readonlySelectModule=true;
                                $('#tokens_modules').trigger('change');
                                $('#tokens_modules').closest('.m--bottom').children('div:first').remove();
                            }
                            $rootScope.app.progressIndicatorElement.progressIndicator({'mode': 'hide'});
                            AppHelper.showMessage(data.message, AppHelper.MESSAGE_TYPE.SUCCESS);
                        } else {
                            AppHelper.showMessage(response.error.message)
                        }
                    });
                } else {
                    // Update content to db
                    Template.save({
                        record: $rootScope.app.model.id,
                        primary_module: $rootScope.app.model.module,
                        body: $rootScope.app.model.body,
                        content: $rootScope.app.model.content,
                        header: $rootScope.app.model.header,
                        footer: $rootScope.app.model.footer,
                        history: true,
                        owner:$rootScope.app.model.owner,
                        share_status:$rootScope.app.model.share_status,
                        share_to:$('[name="share_to"]').val(),
                        settings_layout: $rootScope.app.model.layout_settings,
                        custom_function: angular.toJson($rootScope.app.data.customFunctionData),
                        file_name: $rootScope.app.model.file_name
                    }, function (response) {
                        if (response.success == true) {
                            var data = response.result;

                            $scope.$apply(function () {
                                var newHistory = data['history'];

                                if (newHistory) {
                                    $rootScope.app.model.histories.push(newHistory);
                                    var args = {
                                        'history': newHistory
                                    };
                                    $rootScope.$broadcast('$evtSetSelectedHistory', args);
                                }
                            });
                            // Update content of proposal when edit record
                            var linkProposal = jQuery("#link-proposal").val();
                            var idTransaction = AppHelper.getRequestParam('record', linkProposal);
                            if(idTransaction != ''){
                                TemplateProposal.save({
                                    record: $rootScope.app.model.id,
                                    idTransaction: idTransaction,
                                }, function (response) {
                                    if (response.success == true) {
                                    } else {
                                        AppHelper.showMessage(response.error.message)
                                    }
                                });
                            }

                            // Hide indicator
                            $rootScope.app.progressIndicatorElement.progressIndicator({'mode': 'hide'});
                            AppHelper.showMessage(data.message, AppHelper.MESSAGE_TYPE.SUCCESS);
                        } else {
                            AppHelper.showMessage(response.error.message)
                        }
                    });
                    TemplateSetting.save({
                        record: $rootScope.app.model.id,
                        description: $rootScope.app.model.settings.description,
                        expire_in_days: $rootScope.app.model.settings.expire_in_days,
                        label_decline: $rootScope.app.model.settings.label_decline,
                        label_accept: $rootScope.app.model.settings.label_accept,
                        background: $rootScope.app.model.settings.background,
                        success_content:$rootScope.app.model.settings.success_content,
                        email_signed:$rootScope.app.model.settings.email_signed,
                        email_from_copy:$rootScope.app.model.settings.email_from_copy,
                        email_bcc_copy:$rootScope.app.model.settings.email_bcc_copy,
                        email_subject_copy:$rootScope.app.model.settings.email_subject_copy,
                        email_body_copy:$rootScope.app.model.settings.email_body_copy,
                        ignore_border_email:$rootScope.app.model.settings.ignore_border_email,
                        track_open : $rootScope.app.model.settings.track_open,
                        decline_message : $rootScope.app.model.settings.decline_message,
                        enable_decline_mess : $rootScope.app.model.settings.enable_decline_mess,
                    }, function (response) {
                        if (response.success == true) {
                        } else {
                            AppHelper.showMessage(response.error.message)
                        }
                    });
                }

                return false;
            };

            /**
             * @param {CKEDITOR} editor
             * @param {Array=} itemsToAdd
             * @param {Array=} itemsToRemove
             */
            $rootScope.customCKEditorContextMenu = function (editor, itemsToAdd, itemsToRemove) {
                // editor.editor.removeMenuItem('cut');
                // editor.editor.removeMenuItem('copy');
                // editor.editor.removeMenuItem('paste');

                if ($rootScope.app.data.blocks.pricing_table || $rootScope.app.data.blocks.table) {
                    editor.editor.removeMenuItem('editdiv');
                    editor.editor.removeMenuItem('removediv');
                    editor.editor.removeMenuItem('tabledelete');
                }

                if (itemsToAdd && $.isArray(itemsToAdd)) {
                    var item = null;

                    for (var i = 0; i < itemsToAdd.length; i++) {
                        item = itemsToAdd[i];
                        editor.editor.addMenuItem(item.name, item.options);
                    }
                }

                if (itemsToRemove && $.isArray(itemsToRemove)) {
                    var item = null;

                    for (var i = 0; i < itemsToRemove.length; i++) {
                        item = itemsToRemove[i];
                        editor.editor.removeMenuItem(item);
                    }
                }
            };
            var sortable = function () {
                $( "#sortable1" ).sortable({
                    change: function( event, ui ) {
                    }
                });
                $( "#sortable1" ).disableSelection();
                $( "#sortable2" ).sortable({
                    change: function( event, ui ) {
                    }
                });
                $( "#sortable2" ).disableSelection();
            };
            $scope.removeFieldTable = function ($event) {
                var focus = $event.currentTarget;
                var element = focus.closest('li');
                $(element).remove();
            };
            $scope.showPopupSettingField = function ($event) {
                $event.preventDefault();
                var focus = $event.currentTarget;
                var element = focus.closest('li');
                var input = jQuery(element).find('[name="inputTable"]');
                var dataInput = input.data('info');
                AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/custom_table/popup_settings_field.html', true, function (html) {
                    var modalFieldTable = $("#modalFieldTable");
                    if(modalFieldTable.length >0) {
                        modalFieldTable.remove();
                    }
                    html.modal('show');
                    $(".modal-backdrop").css("background-color","transparent");
                    //editable
                    if(dataInput.editable) {
                        html.find('[name="editable"]').attr('checked','checked');
                    }else{
                        html.find('[name="editable"]').removeAttr('checked');
                    }
                    //required
                    if(dataInput.required) {
                        html.find('[name="required"]').attr('checked','checked');
                    }else{
                        html.find('[name="required"]').removeAttr('checked');
                    }

                    // event hide module
                    html.find("button[data-number=2]").click(function(e){
                        e.preventDefault();
                        html.modal('hide');
                    });
                    html.on('click', '.btn-submit', function () {
                        var form = html.find('.form');
                        var newInfo = {};
                        var key = null;
                        var value = null;
                        var fields = form.find('input, select, textarea');
                        for (var i = 0; i < fields.length; i++) {
                            var field = fields[i];
                            var objField = $(field);
                            var type = field.type;

                            switch (type) {
                                case 'checkbox':
                                    key = objField.attr('name');
                                    value = objField.is(':checked');
                                    break;
                                default:
                                    break;
                            }
                            newInfo[key] = value;
                        }
                        var info = dataInput;
                        if (typeof info === 'undefined') {
                            info = {};
                        }
                        $.extend(info, newInfo);
                        input.attr('data-info', JSON.stringify(info));
                        html.modal('hide');
                    });
                });


            };

            $scope.insertFieldTable = function ($event) {
                sortable();
                $event.preventDefault();

                var valueField = $rootScope.app.data.selectedModuleField;
                var labelfield = null;
                var info = {};
                var focus = $event.currentTarget;
                var nameFocus = $(focus).attr("name");

                AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/custom_table/field_table.html', true, function (html) {
                    if(valueField != null) {
                        var text = $rootScope.app.data.selectedModuleField.token;
                        labelfield = $rootScope.app.data.selectedModuleField['label'];
                        info = {
                            editable: true,
                            required: false,
                            token: text,
                            label: labelfield,
                            id: $rootScope.app.data.selectedModuleField['id'],
                            name: $rootScope.app.data.selectedModuleField['name'],
                            uitype: $rootScope.app.data.selectedModuleField['uitype'],
                            datatype: $rootScope.app.data.selectedModuleField['datatype'],
                            module: $rootScope.app.data.selectedModule.name
                        };
                    }else {
                        labelfield = 'PLACEHOLDER(BLANK)';
                        info = {
                            editable: true,
                            token: 'blank',
                            label: labelfield
                        };
                    }
                    html.find('[name="inputTable"]').val(labelfield);
                    var inputField = html.find('[name="inputTable"]');

                    // $compile(html.contents())($scope);
                    if(nameFocus == "insert-left") {
                        jQuery('[name="sortable1"]').append(html);
                    }else if(nameFocus == "insert-right") {
                        jQuery('[name="sortable2"]').append(html);
                    }
                    // Init data-info
                    if (inputField && inputField.length > 0) {
                        inputField.attr('data-info', JSON.stringify(info));
                    }

                });
            };
            $rootScope.skipItemDetailsFields = function (param) {
                return function (item) {
                    // item.block
                    if(item.block.name == 'LBL_ITEM_DETAILS' && jQuery.inArray(param, ['Invoice','Quotes','PurchaseOrder','SalesOrder']) >= 0) {
                        return false;
                    }
                    return true;
                };
            };
            //only get fields for Vtiger Field (widget)
            $rootScope.getWidgetFields = function (param) {
                return function (item) {
                    // item.block
                    if((item.block.name == 'LBL_ITEM_DETAILS' || item.block.name == 'Total') && jQuery.inArray(param, ['Invoice','Quotes','PurchaseOrder','SalesOrder']) >= 0) {
                        return false;
                    }
                    if(jQuery.inArray(item.uitype, ['10', '53', '69','57','51','4', '70', '52']) >= 0 ||
                        jQuery.inArray(item.name, ['crmid',  'source', 'cf_team', 'cf_teammembers', 'isconvertedfromlead', 'conversion_rate']) >= 0|| (item.name).indexOf('cf_acf') > -1){
                        return false;

                    }
                    return true;
                };
            };
            $rootScope.getItemDetailsFields = function (param) {
                return function (item) {
                    if((item.block.name == 'LBL_ITEM_DETAILS' && item.name != 'hdnDiscountAmount'&& item.name != 'hdnDiscountPercent') ||item.block.name == '' ||
                        jQuery.inArray(param, ['Products','Services']) >= 0 ) {
                        return true;
                    }
                    return false;
                };
            };
            $rootScope.changePixelSpacer = function ($event) {
                var target = $($event.target);
                var modal = target.closest('.modal-settings');
                var form = modal.find('.form');
                if (!$rootScope.app.last_focus_item_setting || $rootScope.app.last_focus_item_setting.length == 0) {
                    // For blocks and others
                    return;
                }
                var pixcelChange = form.find('#settings_spacer').val();
                $rootScope.app.last_focus_item_setting.css('height',pixcelChange+'px');
                $rootScope.app.last_focus_item_setting.data('info',JSON.stringify({height : pixcelChange}));

                $rootScope.app.last_focus_item_setting.attr('data-info',JSON.stringify({height : pixcelChange}));
                // Close modal
                AppHelper.hideModalWindow();
            };
            $rootScope.selectIconLabel = function ($event) {
                var target = $($event.target);
                var modal = target.closest('.modal-settings');
                var form = modal.find('.form');
                if (!$rootScope.app.last_focus_item_setting || $rootScope.app.last_focus_item_setting.length == 0) {
                    // For blocks and others
                    return;
                }
                var urlIcon = form.find('input[name="iconLabel"]:checked').val();
                $rootScope.app.last_focus_item_setting.attr('src',urlIcon);
                $rootScope.app.last_focus_item_setting.data('info', JSON.stringify({urlIcon : urlIcon}));
                $rootScope.app.last_focus_item_setting.attr('data-info', JSON.stringify({urlIcon : urlIcon}));
                // Close modal
                AppHelper.hideModalWindow();
            };
            /**
             * @link http://stackoverflow.com/questions/16150289/running-angularjs-initialization-code-when-view-is-loaded
             */
            var init = function () {
                if($('input[name="record"]').val()!=''){
                    $rootScope.app.data.readonlySelectModule=true;
                }
                var owner="Users:"+String($rootScope.app.form_item.owner.val());
                for(var i=0;i<$rootScope.app.data.owner.length;i++){
                    if($rootScope.app.data.owner[i].id==owner){
                        $rootScope.app.model.owner=$rootScope.app.data.owner[i];
                    }
                }
                var share_to=$rootScope.app.form_item.share_to.val();
                share_to=share_to.split('|##|');
                for(var i=0;i<$rootScope.app.data.sharing.length;i++){
                    if($.inArray($rootScope.app.data.sharing[i].id,share_to) >=0){
                        $rootScope.app.model.share_to.push($rootScope.app.data.sharing[i]);
                    }
                }
                $rootScope.app.model.share_status=$rootScope.app.form_item.share_status.val();
                //Layout settings
                var layoutSettings=String($rootScope.app.form_item.settings_layout.val());
                if(layoutSettings){
                    layoutSettings=$.parseJSON(layoutSettings);
                    for(var i=0;i<$rootScope.app.data.orientation.length;i++){
                        if(layoutSettings.orientation==$rootScope.app.data.orientation[i].id){
                            $rootScope.app.model.page_layout=$rootScope.app.data.orientation[i];
                        }
                    }
                    for(var i=0;i<$rootScope.app.data.page_format.length;i++){
                        if(layoutSettings.format==$rootScope.app.data.page_format[i].id){
                            $rootScope.app.model.page_format=$rootScope.app.data.page_format[i];
                        }
                    }
                    $rootScope.app.model.page_width=parseFloat(layoutSettings.width);
                    $rootScope.app.model.page_height=parseFloat(layoutSettings.height);
                    $rootScope.app.model.margin_left=parseFloat(layoutSettings.margin_left);
                    $rootScope.app.model.margin_right=parseFloat(layoutSettings.margin_right);
                    $rootScope.app.model.margin_top=parseFloat(layoutSettings.margin_top);
                    $rootScope.app.model.margin_bottom=parseFloat(layoutSettings.margin_bottom);
                }
                var format=$rootScope.app.model.page_format.id.toUpperCase();
                var orientation=$rootScope.app.model.page_layout.id;
                var margin_top= $rootScope.app.model.margin_top;
                var margin_left= $rootScope.app.model.margin_left;
                var margin_right= $rootScope.app.model.margin_right;
                var margin_bottom= $rootScope.app.model.margin_bottom;
                var page_width= $rootScope.app.model.page_width;
                var page_height= $rootScope.app.model.page_height;
                var pageWidth=0;
                var pageHeight=0;
                var pWidth=0;
                var pHeight=0;
                if(format=='CUSTOM'){
                    pWidth=page_width *72/25.4;
                    pHeight=page_height *72/25.4;
                }else{
                    switch(format){
                        case 'A4':
                            pWidth=595.28;
                            pHeight=841.89;
                            break;
                        case 'A3':{
                            pWidth=841.89;
                            pHeight=1190.55;
                            break;
                        }
                        case 'LETTER':{
                            pWidth=612.00;
                            pHeight=792.00;
                            break;
                        }
                        case 'LEGAL':{
                            pWidth=612.00;
                            pHeight=1008.00;
                            break;
                        }
                        default :
                            pWidth=595.28;
                            pHeight=841.89;
                            break;
                    }
                }
                if(orientation=='L'){
                    pageWidth=pHeight;
                    pageHeight=pWidth;
                }else {
                    pageWidth=pWidth;
                    pageHeight=pHeight;
                }
                var page_dpi=$('#check_dpi').height();
                pageWidth=pageWidth*page_dpi/72;
                // Template id
                $rootScope.app.model.id = $rootScope.app.form_item.record.val();
                // Primary module
                $rootScope.app.model.module = $rootScope.app.form_item.primary_module.val();
                // Settings
                var valSettings = $rootScope.app.form_item.settings.val();
                var objSettings = (valSettings && valSettings !== '[]') ? JSON.parse(valSettings) : {};
                if (!$.isEmptyObject(objSettings)) {
                    $rootScope.app.model.settings = objSettings;

                    if (!$rootScope.app.model.settings.expire_in_days) {
                        $rootScope.app.model.settings.expire_in_days = 0;
                    }
                    if (!$rootScope.app.model.settings.email_body_copy) {
                        $rootScope.app.model.settings.email_body_copy = 'Your information has been submitted.\n' +
                            '\n' +
                            'You can now close this page.\n' +
                            '\n' +
                            'Thank you';
                    }

                    $rootScope.app.model.settings.expire_in_days = parseInt($rootScope.app.model.settings.expire_in_days);
                    if (!$rootScope.app.model.settings.success_content) {
                        $rootScope.app.model.settings.success_content = 'Your information has been submitted.\n' +
                            '\n' +
                            'You can now close this page.\n' +
                            '\n' +
                            'Thank you';
                    }
                    if (!$rootScope.app.model.settings.decline_message) {
                        $rootScope.app.model.settings.decline_message = '';
                    }

                    $rootScope.app.model.settings.success_content = $rootScope.app.model.settings.success_content;
                    $rootScope.app.model.settings.decline_message = $rootScope.app.model.settings.decline_message;

                    if(!$rootScope.app.model.settings.track_open){
                        $rootScope.app.model.settings.track_open = 0;
                    }
                }
                // Body
                $rootScope.app.model.body = $rootScope.app.form_item.body.val();
                // File name
                $rootScope.app.model.filename = $rootScope.app.form_item.filename.val();
                // Page title
                PageTitle.set($rootScope.app.model.filename);
                // Description
                $rootScope.app.model.description = $rootScope.app.form_item.description.val();
                //linkproposal
                $rootScope.app.model.linkproposal = $rootScope.app.form_item.linkproposal.val();
                // // expire_in_days
                // console.log($rootScope.app.model.settings);
                // $rootScope.app.model.expire_in_days = $rootScope.app.form_item.expire_in_days.val();
                // Anwidget
                $rootScope.app.model.anwidget = $rootScope.app.form_item.anwidget.val() == 1;
                // Configuration
                $rootScope.app.model.createnewrecords = $rootScope.app.form_item.createnewrecords.val() == 1;
                // Mapping fields
                var valMappingFields = $rootScope.app.form_item.mapping_fields.val();
                var objMappingFields = (valMappingFields && valMappingFields !== '[]') ? JSON.parse(valMappingFields) : {};
                if (!$.isEmptyObject(objMappingFields)) {
                    $rootScope.app.model.mapping_fields = objMappingFields;
                }
                // Attachments
                var valAttachments = $rootScope.app.form_item.attachments.val();
                var arrAttachments = (valAttachments && valAttachments !== '{}') ? JSON.parse(valAttachments) : [];
                if ($.isArray(arrAttachments)) {
                    $rootScope.app.model.attachments = arrAttachments;
                }
                // Email
                var valEmailSubject = $rootScope.app.form_item.email_subject.val();
                var valEmailContent = $rootScope.app.form_item.email_content.val();
                $rootScope.app.model.email_subject = (valEmailSubject) ? QuotingToolUtils.base64Decode(valEmailSubject) : '';
                $rootScope.app.model.email_content = (valEmailContent) ? QuotingToolUtils.base64Decode(valEmailContent) : '';

                // is_active Record
                $rootScope.app.model.is_active = $rootScope.app.form_item.is_active.val();
                // Config
                $('[ng-app="app"]').attr({
                    'base': $rootScope.app.config.base
                });

                // Init default & selected
                if ($rootScope.app.data.modules && $rootScope.app.data.modules.length > 0) {
                    if (!$rootScope.app.model.module) {
                        // Init selected module if not set
                        var defaultModule = $rootScope.app.data.modules[0];
                        $rootScope.app.form_item.primary_module.val(defaultModule.name);
                        $rootScope.app.model.module = defaultModule.name;
                        $rootScope.app.data.selectedModule = defaultModule;
                        $rootScope.app.data.newSelectedModule = [];
                    } else {
                        var flag = false;
                        var module = null;

                        for (var m = 0; m < $rootScope.app.data.modules.length; m++) {
                            module = $rootScope.app.data.modules[m];

                            if (module.name == $rootScope.app.model.module) {
                                // Init selected module
                                $rootScope.app.data.selectedModule = module;
                                $rootScope.app.data.newSelectedModule = [];
                                flag = true;
                                break;
                            }
                        }

                        if (!flag) {
                            // Show error if the selected module isn't match in enable module list
                            return AppHelper.showMessage($translate.instant('Invalid selected module'));
                        }
                    }
                }

                if ($rootScope.app.data.selectedModule) {
                    // Set default selected
                    $rootScope.app.data.selectedModuleField = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedModule.fields);
                    $rootScope.app.data.selectedRelatedModule = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedModule.related_modules);
                    if ($rootScope.app.data.selectedRelatedModule) {
                        $rootScope.app.data.selectedRelatedModuleField = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedRelatedModule.fields);
                    }
                }

                // Product block modules
                var inventoryModules = $.merge(GlobalConfig.INVENTORY_MODULES, GlobalConfig.PRODUCT_MODULES);
                var prepareModule = null;

                for (var i = 0; i < $rootScope.app.data.modules.length; i++) {
                    prepareModule = angular.copy($rootScope.app.data.modules[i]);
                    $rootScope.app.data.idxModules[prepareModule.name] = prepareModule;

                    if (($.inArray(prepareModule.name, inventoryModules) < 0) || $rootScope.app.data.idxProductBlockModules[prepareModule.name]) {
                        // continue if the module isn't inventory module or exist on product block module list
                        continue;
                    }

                    // Push new product block module
                    $rootScope.app.data.idxProductBlockModules[prepareModule.name] = prepareModule;
                    // Get relation inventory modules

                    idxProductBlockModules = $rootScope.app.data.idxProductBlockModules;
                }

                $rootScope.app.data.selectedProductBlockModule = QuotingToolUtils.defaultSelected($rootScope.app.data.idxProductBlockModules);
                if ($rootScope.app.data.selectedProductBlockModule) {
                    $rootScope.app.data.selectedProductBlockModuleField = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedProductBlockModule.fields);
                }

                $rootScope.app.data.selectedRelatedBlockModule = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedModule.link_modules);
                if ($rootScope.app.data.selectedRelatedBlockModule) {
                    $rootScope.app.data.selectedRelatedBlockModuleField = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedRelatedBlockModule.fields);
                }

                // Inventory module fields:
                var inventoryModuleNames = GlobalConfig.PRODUCT_MODULES;
                var inventoryModuleName = null;
                var inventoryModule = null;
                var inventoryFields = null;
                var inventoryField = null;
                for (var i = 0; i < inventoryModuleNames.length; i++) {
                    inventoryModuleName = inventoryModuleNames[i];

                    if (!$rootScope.app.data.idxProductBlockModules[inventoryModuleName]) {
                        continue;
                    }

                    inventoryModule = angular.copy($rootScope.app.data.idxProductBlockModules[inventoryModuleName]);
                    inventoryFields = inventoryModule.fields;

                    for (var f = 0; f < inventoryFields.length; f++) {
                        inventoryField = inventoryFields[f];
                        inventoryField.block.label = inventoryModule.name + ' - ' + inventoryField.block.label;
                        inventoryField.module = inventoryModuleName;
                        if(inventoryModuleName == 'Services'){
                            inventoryField.label = inventoryField.label + ' ' ;
                        }
                        productModuleFields.push(inventoryField);
                    }
                }

                // Change picklist values
                if ($rootScope.app.data.idxModules[$rootScope.app.model.module]) {
                    $rootScope.app.data.picklistField.options = $rootScope.app.data.idxModules[$rootScope.app.model.module]['picklist'];
                }

                // Initial
                if ($rootScope.app.model.id) {
                    $rootScope.loadTemplate($rootScope.app.model.id);
                }
                // else {
                //     initTemplate()
                // }

                var obj_quoter_settings = jQuery('#js_quoter_settings');

                if (obj_quoter_settings.length == 0) {
                    $rootScope.app.data.blocksOrder;
                    for (var i = 0; i < $rootScope.app.data.blocksOrder.length; i++) {
                        if($rootScope.app.data.blocksOrder[i].layout.id == "pricing_table_idc") {
                            $rootScope.app.data.blocksOrder[i] = {};
                        }
                    }
                }

                $rootScope.registerEventDragAndDropBlocks();
                $rootScope.watchCurrentPosition();
                // Last focus page:
                $rootScope.registerEventFocusPage();
                // mouse enter event
                $rootScope.registerEventCoverPageSupportOptions();
                // Last focus item
                $rootScope.registerEventFocusInput();
                // Delay to hide progress indicator
                $timeout(function () {
                    // vtiger indicator
                    $rootScope.app.progressIndicatorElement.progressIndicator({'mode': 'hide'});
                }, 8000);
                var view = app.getViewName();
                var recordId = jQuery('[name="record"]').val();
                var moduleName = app.getModuleName();
                if(view == 'Edit' && recordId == false && moduleName == 'QuotingTool'){
                    AppUtils.loadTemplate($scope, 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_create_new_record.html', true, function (html) {
                        $timeout(function () {
                            AppHelper.showModalWindow(html, '#', function () {
                                html.closest('.blockUI').prev('.blockUI.blockOverlay').unbind('click');
                                html.closest('.blockUI').prev('.blockUI.blockOverlay').click(function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                });
                                var selectModule = html.find('#create_new_record');
                                var templateName = html.find('[name="newDocumentName"]');

                                // Trigger click
                                html.on('click', '.btn-submit', function () {
                                    var templateNameVal = templateName.val();
                                    if($("#create_new_record option:selected").text() == 'Please select' ){
                                        AppHelper.showMessage($translate.instant('Please select primary module'));
                                        return;
                                    }
                                    if(templateNameVal ==''){
                                        templateName.focus();
                                        AppHelper.showMessage($translate.instant('Template name is missing'));
                                        return;
                                    }
                                    jQuery('[name="tmp_filename"]').val(templateNameVal);
                                    jQuery('[name="tmp_filename"]').attr('value',templateNameVal);
                                    jQuery('[name="tmp_filename"]').trigger('change');
                                    module = $rootScope.app.data.selectedModuleCreate;
                                    $rootScope.app.data.selectedModule = module;
                                    $rootScope.app.form_item.primary_module.val(module.name);
                                    $rootScope.app.model.module = module.name;
                                    $rootScope.app.data.readonlySelectModule=true;
                                    $rootScope.changeSelectedModule();
                                    jQuery('#tokens_modules').trigger('change');
                                    AppHelper.hideModalWindow();

                                });
                            });
                        },9000);
                    });
                }
                $rootScope.skipItemDetailsFields();
                $rootScope.getWidgetFields();
                $rootScope.getItemDetailsFields();
                // Product block modules: only show Products', 'Services', primary module when reload page
                // var prepareModule = null;
                var prepareModule = null;
                var selectedModule = $rootScope.app.data.selectedModule.name;
                jQuery.each($rootScope.app.data.idxProductBlockModules, function (idx, val) {
                    if (jQuery.inArray(idx, ['Products', 'Services']) == -1)
                    {
                        delete $rootScope.app.data.idxProductBlockModules[idx];
                    }
                });
                for (var i = 0; i < $rootScope.app.data.modules.length; i++) {
                    prepareModule = angular.copy($rootScope.app.data.modules[i]);
                    if($.inArray(selectedModule, ['Invoice','Quotes','PurchaseOrder','SalesOrder']) >= 0 && prepareModule.name == selectedModule && !$rootScope.app.data.idxProductBlockModules[selectedModule] ){
                        // Push new product block module
                        $rootScope.app.data.idxProductBlockModules[prepareModule.name] = prepareModule;
                    }
                }
                $rootScope.app.data.idxProductBlockModules['selectOption'] = {
                    fields: [
                        {
                            block: {
                                name : '',
                                label : ''
                            },
                            label: "Please select option",
                            id: '-1',
                            name : ''
                        }
                    ],
                    id: '-1',
                    label: 'Please select option',
                    name: '-1'

                };
                $rootScope.app.data.selectedProductBlockModule = $rootScope.app.data.idxProductBlockModules['selectOption'];
                $rootScope.app.data.selectedProductBlockModuleField = QuotingToolUtils.defaultSelected($rootScope.app.data.selectedProductBlockModule.fields);
                var margin_page=0 + "mm " + margin_right + "mm " +margin_bottom + "mm " +margin_left +'mm';
                $('.quoting_tool-content').css({
                    'width':pageWidth,
                    'padding': margin_page
                });
                $('.quoting_tool-content-footer').css({
                    'height':0
                });
                $('.quoting_tool-content-header').css({
                    'height':margin_top+'mm'
                });
            };
            init();
        });

})(jQuery);
