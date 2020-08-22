/* ********************************************************************************
 * The content of this file is subject to the Quoting Tool ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

(function ($) {
    'use strict';

    var quotingtoolApp = angular.module('app', ['AppConfig', 'AppConstants', 'AppUtils', 'AppControllers', 'AppModels', 'AppDirectives',
        'AppI18N', 'ui.bootstrap', 'ngSanitize', 'ngCkeditor']);

    /**
     * trustAsResourceUrl filter
     */
    quotingtoolApp.filter('trustAsResourceUrl', ['$sce', function ($sce) {
        return function (val) {
            return $sce.trustAsResourceUrl(val);
        };
    }]);

    /**
     * Config - config
     */
    quotingtoolApp.config(function ($httpProvider, $locationProvider, $stateProvider, $urlRouterProvider, $translateProvider, GlobalConfig) {
        ////Enable cross domain calls
        //$httpProvider.defaults.useXDomain = true;
        //
        ////Remove the header used to identify ajax call  that would prevent CORS from working
        //delete $httpProvider.defaults.headers.common['X-Requested-With'];

        // Allow request api
        $httpProvider.interceptors.push('httpRequestInterceptor');
        $stateProvider.state('base', {
            url: GlobalConfig.BASE,
            abstract: true,
            views: {
                'right_panel_tool_items@': {
                    templateUrl: '',
                    controller: 'CtrlAppRightPanelContent'
                }
            }
        });

        // Go to content tab
        $urlRouterProvider.otherwise('content');

        // Default language:
        $translateProvider.preferredLanguage('en');
    });

    /**
     * Run - run
     */
    quotingtoolApp.run(
        function ($rootScope, $state, $stateParams, $translate, AppConstants, GlobalConfig, AppToolbar, SECTIONS,
                  ProgressIndicator) {
        // App
        $rootScope.app = {};

        // Indicator
        ProgressIndicator.show({
            'message': $translate.instant('Loading...')
        });

        // User
        $rootScope.app.user = {};

        // Sections
        $rootScope.SECTIONS = SECTIONS;

        // Form
        $rootScope.app.form = $('#EditView');
        $rootScope.app.form_item = {
            record: $('[name="record"]'),
            module: $('[name="module"]'),
            filename: $('[name="filename"]'),
            primary_module: $('[name="primary_module"]'),
            settings: $('[name="settings"]'),
            attachments: $('[name="attachments"]'),
            body: $('[name="body"]'),
            content: $('[name="content"]'),
            header: $('[name="header"]'),
            footer: $('[name="footer"]'),
            description: $('[name="description"]'),
            anwidget: $('[name="anwidget"]'),
            anblock: $('[name="anblock"]'),
            createnewrecords: $('[name="createnewrecords"]'),
            linkproposal: $('[name="linkproposal"]'),
            mapping_fields: $('[name="mapping_fields"]'),
            email_subject: $('[name="email_subject"]'),
            email_content: $('[name="email_content"]'),
            is_active: $('[name="is_active"]'),
            owner:$('[name="setting_owner"]'),
            share_status:$('[name="setting_share_status"]'),
            share_to:$('[name="setting_share_to"]'),
            settings_layout:$('[name="settings_layout"]'),
            check_attach_file:$('[name="check_attach_file"]')
        };
        // Container
        $rootScope.app.container = $('#quoting_tool-center');
        // TODO: need update value when load controller successful
        $rootScope.app.container_overlay = $('#quoting_tool-overlay-content');

        $rootScope.app.last_zindex = 0;
        $rootScope.app.last_focus_page = null;
        $rootScope.app.last_focus_item = null;
        $rootScope.app.last_focus_item_setting = null;
        $rootScope.app.is_debug = GlobalConfig.DEBUG_MODE;
        $rootScope.app.is_debug_show_result = false;

        // Config
        $rootScope.app.config = {
            date_format: "mm-dd-yyyy",
            hour_format: "12",
            base: ''
        };

        // Data

        $rootScope.app.data = {
            signatures: 1,
            readonlySelectModule:false,
            modules: [],
            picklistField: {
                options: []
            },
            idxModules: {},
            blocks: AppToolbar.blocks,
            blocksOrder:AppToolbar.blocksOrder,
            widgets: AppToolbar.widgets,
            idxProductBlockModules: {},
            selectedProductBlockModule: {},
            selectedProductBlockModuleField: {},
            selectedRelatedBlockModule: {},
            selectedRelatedBlockModuleField: {},
            barcode_type:[
                {'id':'TYPE_CODE_39','value':'C39'},
                {'id':'TYPE_CODE_39_CHECKSUM','value':'C39+'},
                {'id':'TYPE_CODE_39E','value':'C39E'},
                {'id':'TYPE_CODE_39E_CHECKSUM','value':'C39E+'},
                {'id':'TYPE_CODE_93','value':'C93'},
                {'id':'TYPE_STANDARD_2_5','value':'S25'},
                {'id':'TYPE_STANDARD_2_5_CHECKSUM','value':'S25+'},
                {'id':'TYPE_INTERLEAVED_2_5','value':'I25'},
                {'id':'TYPE_INTERLEAVED_2_5_CHECKSUM','value':'I25+'},
                {'id':'TYPE_CODE_128','value':'C128'},
                {'id':'TYPE_CODE_128_A','value':'C128A'},
                {'id':'TYPE_CODE_128_B','value':'C128B'},
                {'id':'TYPE_CODE_128_C','value':'C128C'},
                {'id':'TYPE_EAN_2','value':'EAN2'},
                {'id':'TYPE_EAN_5','value':'EAN5'},
                {'id':'TYPE_EAN_8','value':'EAN8'},
                {'id':'TYPE_EAN_13','value':'EAN13'},
                {'id':'TYPE_UPC_A','value':'UPCA'},
                {'id':'TYPE_UPC_E','value':'UPCE'},
                {'id':'TYPE_MSI','value':'MSI'},
                {'id':'TYPE_MSI_CHECKSUM','value':'MSI+'},
                {'id':'TYPE_POSTNET','value':'POSTNET'},
                {'id':'TYPE_PLANET','value':'PLANET'},
                {'id':'TYPE_RMS4CC','value':'RMS4CC'},
                {'id':'TYPE_KIX','value':'KIX'},
                {'id':'TYPE_IMB','value':'IMB'},
                {'id':'TYPE_CODABAR','value':'CODABAR'},
                {'id':'TYPE_CODE_11','value':'CODE11'},
                {'id':'TYPE_PHARMA_CODE','value':'PHARMA'},
                {'id':'TYPE_PHARMA_CODE_TWO_TRACKS','value':'PHARMA2T'},
            ],
            orientation:[
                {
                    id:'P',
                    name:'Portrait'
                },
                {
                    id:'L',
                    name:'Landscape'
                },
            ],
            page_format:
            [
                {
                    id:'A3',
                    name:'A3'
                },
                {
                    id:'A4',
                    name:'A4'
                },
                {
                    id:'Letter',
                    name:'Letter'
                },
                {
                    id:'Legal',
                    name:'Legal'
                },
                {
                    id:'Custom',
                    name:'Custom'
                }
            ],
            indexOfCustomFunction:0,
            customFunctionData:{}
        };

        // Model
        $rootScope.app.model = {
            id: '',
            module: 'Quotes',
            filename: '',
            body: '',
            content: '',
            header: '',
            footer: '',
            description: '',
            linkproposal: '',
            anwidget: '',
            anblock: '',
            createnewrecords: '',
            attachments: [],
            mapping_fields: {},
            email_subject: '',
            email_content: '',
            share_status:'',
            owner:[],
            share_to:[],
            page_layout:{
                id:'P',
                name:'Portrait'
            },
            page_format:{
                id:'A4',
                name:'A4'
            },
            file_name:"$record_no$_$month$_$day$_$year$",
            page_width:210,
            page_height:297,
            margin_left:15,
            margin_right:15,
            margin_top:16,
            margin_bottom:16,
            settings: {
                description: '',
                ignore_border_email: '1',
                email_signed: '0',
                email_from_copy: 'abc@gmail.com',
                email_bcc_copy: '',
                email_subject_copy: "We've received your electronically signed document.",
                email_body_copy: "We've received your electronically signed document.\n" +
                '\n' +
                "We will contact and let you the next steps shortly.\n" +
                '\n' +
                "Thank you",
                expire_in_days: 0,
                label_accept: 'Accept & Submit',
                label_decline: '',
                background: {
                    image: '',
                    size: 'auto'
                },
                success_content:'Your information has been submitted.\n' +
                '\n' +
                'You can now close this page.\n' +
                '\n' +
                'Thank you',
                track_open : 0,
                decline_message: '',
                enable_decline_mess: false,
                date_format: '',
            },
            histories: [],
            selectedHistory: null,
            is_active : '1',
            check_attach_file : false,
        };

        // Config
        var js_config = jQuery('#js_config').text();
        if(js_config != '') {
            $rootScope.app.config = JSON.parse(js_config);
        }

        // Modules
        var js_modules = jQuery('#js_modules').text();
        if(js_modules != '') {
            $rootScope.app.data.modules = JSON.parse(js_modules);
        }
        var js_helpdesk_support_email_id = jQuery('#js_helpdesk_support_email_id').text();
        if(js_helpdesk_support_email_id != '') {
        $rootScope.app.model.settings.email_from_copy = JSON.parse(js_helpdesk_support_email_id);
        }

        // Custom functions
        var js_custom_functions = jQuery('#js_custom_functions').text();
        if(js_custom_functions != '') {
            $rootScope.app.data.customFunctions = JSON.parse(js_custom_functions);
        }
        // Custom functions
        var js_date_format = jQuery('#js_date_format').text();
        if(js_date_format != '') {
            $rootScope.app.data.date_format = JSON.parse(js_date_format);
        }
        $rootScope.filterImgFields = function(field){
            return field.name.indexOf('cf_acf_ulf') !== -1 || field.name.indexOf('imagename') !== -1;
        };
        // Custom fields
        var js_custom_fields = jQuery('#js_custom_fields').text();
        if(js_custom_fields != '') {
            $rootScope.app.data.customFields = JSON.parse(js_custom_fields);
        }
        // Company fields
        var js_company_fields = jQuery('#js_company_fields').text();
        if(js_company_fields != '') {
            $rootScope.app.data.companyFields = JSON.parse(js_company_fields);
        }
        // Merge fields
        var js_merge_fields = jQuery('#js_merge_fields').text();
        if(js_merge_fields != '') {
            $rootScope.app.data.mergeFields = JSON.parse(js_merge_fields);
        }

        // Current user
        var js_currentUser = jQuery('#js_currentUser').text();
        if(js_currentUser != '') {
            $rootScope.app.user.profile = JSON.parse(js_currentUser);
        }

        // Quoter settings
        var obj_quoter_settings = jQuery('#js_quoter_settings');

        if (obj_quoter_settings.length == 0) {
            delete $rootScope.app.data.blocks.pricing_table_idc;
        } else {
            var js_quoter_settings = obj_quoter_settings.text();
            $rootScope.app.data.quoter_settings = (js_quoter_settings != '') ? JSON.parse(js_quoter_settings): {};
        }

        //Sharing
            var obj_sharing=jQuery('#js_sharing');
            if (obj_sharing.length == 0) {
                delete $rootScope.app.data.sharing;
            } else {
                var js_sharing = obj_sharing.text();
                $rootScope.app.data.sharing = (js_sharing != '') ? JSON.parse(js_sharing): {};
                $rootScope.app.data.owner=[];
                for(var i=0;i<$rootScope.app.data.sharing.length;i++){
                    if($rootScope.app.data.sharing[i].role=='Users'){
                        $rootScope.app.data.owner.push($rootScope.app.data.sharing[i]);
                    }
                }
            }
            var custom_function=jQuery('[name=custom_function]').val();
            if(custom_function!=undefined && custom_function!=''){
                $rootScope.app.data.customFunctionData=jQuery.parseJSON(custom_function);
            }else{
                $rootScope.app.data.customFunctionData={};
            }

            // if($rootScope.app.data.customFunctionData){
            //     for(var i=0;i< $rootScope.app.data.customFunctionData.length;i++){
            //         console.log(parseInt($rootScope.app.data.customFunctionData[i].id));
            //         if(!isNaN(parseInt($rootScope.app.data.customFunctionData[i].id))){
            //             $rootScope.app.data.indexOfCustomFunction=parseInt($rootScope.app.data.customFunctionData[i].id)+1;
            //         }
            //     }
            // }
            for(var key in $rootScope.app.data.customFunctionData) {
                if (key != undefined){
                    if(!isNaN(parseInt($rootScope.app.data.customFunctionData[key].id))){
                        $rootScope.app.data.indexOfCustomFunction=parseInt($rootScope.app.data.customFunctionData[key].id)+1;
                    }
                }
            }
            //TASKID:13087 - DEV: tiennguyen - DATE: 2018-10-22 - START
            //NOTE: get value of file_name for Edit mode
            var file_name=$('[name="settings_file_name"]');
            if(file_name.length>0 && (file_name.val() != '' && file_name.val() != undefined)){
                $rootScope.app.model.file_name=file_name.val();
            }
            //TASKID:13087 - DEV: tiennguyen - DATE: 2018-10-22 - END
        $rootScope.currentPosition = null;
        $rootScope.dragOffset = null;

        // ngRoute
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;

        // Document ready
        angular.element(document).ready(function () {
            // Watch object change
            $rootScope.$watchCollection("app.model.settings.background", function (newValue, oldValue) {
                // Background
                if ($rootScope.app.model.settings.background) {
                    var backgroundImage = $rootScope.app.model.settings.background.image ? 'url("' + $rootScope.app.model.settings.background.image + '")' : '';
                    var backgroundSize = $rootScope.app.model.settings.background.size ? $rootScope.app.model.settings.background.size : '';

                    $rootScope.app.container.css({
                        backgroundImage: backgroundImage,
                        backgroundSize: backgroundSize
                    });
                }
            });

        });
    });

    /**
     * Fac - httpRequestInterceptor
     * valid ACOS request
     */
    quotingtoolApp.factory('httpRequestInterceptor', function ($rootScope, $translate, GlobalConfig) {
        return {
            request: function ($config) {
                $config.headers = $config.headers || {};

                // Header: Authorization - Override Authorization header config
                if ($config.headers['Authorization'] == undefined || $config.headers['Authorization'] === null) {
                    var auth = 'admin';

                    $config.headers['Authorization'] = 'Basic ' + auth;
                }

                // Headers: Appname
                $config.headers['Appname'] = $translate.instant(GlobalConfig.APP_NAME);

                return $config;
            }
        };
    });

    /**
     * Fac - PageTitle
     */
    quotingtoolApp.factory('PageTitle', function ($rootScope, $window, $translate, GlobalConfig) {
        $rootScope.pageTitle = '';
        $rootScope.appTitle = $translate.instant(GlobalConfig.APP_NAME);

        return {
            /**
             * Fn - PageTitle.set
             * @param title
             */
            set: function (title) {
                $window.document.title = $rootScope.appTitle + ' | ' + title;
                $rootScope.pageTitle = title;
            },
            /**
             * Fn - PageTitle.reset
             */
            reset: function () {
                $window.document.title = $rootScope.appTitle;
                $rootScope.pageTitle = $rootScope.appTitle;
            }
        }
    });

    /**
     * @Link http://stackoverflow.com/questions/20715273/unshifting-to-ng-repeat-array-not-working-while-using-orderby
     */
    quotingtoolApp.filter('reverse', function() {
        return function(items) {
            return items.slice().reverse();
        };
    });

    quotingtoolApp.factory('ProgressIndicator', function ($rootScope, $translate) {
        // console.log("app.factory('ProgressIndicator'");

        var defaults = {
            'message': $translate.instant('Loading...'),
            'position': 'html',
            'blockInfo': {
                'enabled': true
            }
        };

        return {
            show: function (options) {
                // console.log('ProgressIndicator.show()');
                if (typeof options === 'undefined') {
                    options = {};
                }

                // Merge options
                options = $.extend(true, defaults, options);

                // if (!$rootScope.progressIndicatorElement || !$rootScope.progressIndicatorElement.data('progressIndicator')) {
                //     $rootScope.progressIndicatorElement = $.progressIndicator(options);
                //     $rootScope.progressIndicatorElement.data('progressIndicator').show();
                // }

                app.helper.showProgress(options.message);
            },
            hide: function () {
                // console.log('ProgressIndicator.hide()');
                // if ($rootScope.progressIndicatorElement && $rootScope.progressIndicatorElement.data('progressIndicator')) {
                //     $rootScope.progressIndicatorElement.data('progressIndicator').hide();
                // }
                app.helper.hideProgress();
            }
        }
    });

})(jQuery);