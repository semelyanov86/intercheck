(function () {
    'use strict';

    var configs = angular.module('AppConfig', []);

    configs.constant({
        GlobalConfig: {
            DOMAIN: 'index.php',
            BASE: '/',
            XDEBUG: 'XDEBUG_SESSION_START=PHPSTORM',
            MODULE_NAME: 'QuotingTool',
            APP_NAME: 'Quoting Tool',
            PUBLIC_KEY: 'XP6RLEBJ2ilY3gJvuDvr1420189516434',
            DEBUG_MODE: 1,
            DEBUG_KEY: 'QuotingTool',
            DEFAULT_BACKGROUND_IMAGE: 'layouts/vlayout/modules/QuotingTool/resources/img/placeholder.png',
            INVENTORY_MODULES: ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice'],
            PRODUCT_MODULES: ['Products', 'Services']
        },
        AppToolbar: {
            menu: {},
            tokens: {
                product_blocks: [
                    {
                        id: 0,
                        name: 'select_options',
                        token: '',
                        label: 'Please select option'
                    },
                    {
                        id: 0,
                        name: 'productbloc_start',
                        token: '#PRODUCTBLOC_START#',
                        label: 'Block start'
                    },
                    {
                        id: 0,
                        name: 'productbloc_end',
                        token: '#PRODUCTBLOC_END#',
                        label: 'Block end'
                    }
                ],
                related_blocks: [
                    {
                        id: 0,
                        name: 'select_options',
                        token: '',
                        label: 'Please select option'
                    },
                    {
                        id: 0,
                        name: 'relatedblock_start',
                        token: '#RELATEDBLOCK_START#',
                        label: 'Related block start'
                    },
                    {
                        id: 0,
                        name: 'relatedblock_end',
                        token: '#RELATEDBLOCK_END#',
                        label: 'Related block end'
                    }
                ]
            },
            base_editor: {
                settings: {
                    toolbar: [
                        {name: 'clipboard', items: ['Undo', 'Redo']},
                        {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                        {name: 'links', items: ['Link', 'Unlink']},
                        {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                        {name: 'about', items: ['About']}
                    ],
                    removePlugins: 'magicline,scayt',
                    // To enable source code editing in a dialog window, inline editors require the "sourcedialog" plugin.
                    extraPlugins: 'sharedspace,doNothing,youtube,sourcedialog,quotingtool,confighelper,abbr',
                    //removePlugins: 'floatingspace,maximize,resize',
                    sharedSpaces: {
                        top: 'quoting_tool-header',
                        bottom: 'quoting_tool-footer'
                    }
                }
            },
            blocks: {
                init: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/init.html',
                    settings: {},
                    layout: {
                        id: 'init',
                        name: '',
                        icon: '',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false
                    }
                },
                heading: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/heading.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_heading.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize', 'Format']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
                            },
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ],
                        format_tags: 'h1;h2;h3;h4;h5;h6',
                        keystrokes: [[13 /*Enter*/, 'doNothing']]
                    },
                    layout: {
                        id: 'heading',
                        name: 'Heading',
                        icon: 'icon--block-header',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                text: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/text.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_table.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'text',
                        name: 'Text',
                        icon: 'icon--block-text',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                image: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_image.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'image',
                        name: 'Image',
                        icon: 'icon--block-image',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                video: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/video.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'video',
                        name: 'Video',
                        icon: 'icon--block-video',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                table: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/table.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_table.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'table',
                        name: 'Table',
                        icon: 'icon--block-table',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                pricing_table: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_table.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_pricing_table.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'pricing_table',
                        name: 'Pricing table',
                        icon: 'icon--block-pricingtable',
                        enable_setting: true,
                        icon_helptext: 'i',
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                pricing_table_idc: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_table_idc.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_pricing_table_idc.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'pricing_table_idc',
                        name: 'Pricing table (IDC)',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        icon_supplement: 'IDC',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                related_module: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/related_module.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_related_module.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'related_module',
                        name: 'Related module',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        icon_supplement: 'REL',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                create_related_record: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/create_related_record.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_create_related_record.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'create_related_record',
                        name: 'Create Related Record',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        icon_supplement: 'REL',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                toc: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/toc.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'toc',
                        name: 'Toc',
                        icon: 'icon--block-toc',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                page_break: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_break.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'page_break',
                        name: 'Page break',
                        icon: 'icon--block-pagebreak',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                line_break: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/line_break.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'line_break',
                        name: 'Line break',
                        icon: 'fa fa-level-down force-fa',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                line_hr: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/line_hr.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'line_hr',
                        name: 'SEPERATOR',
                        icon: 'fa-arrows-h force-fa',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                cover_page: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/cover_page.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline']},
                            {
                                name: 'paragraph',
                                items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'cover_page',
                        name: 'Cover page',
                        icon: 'icon--block-cover',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                page_header: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_header.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'page_header',
                        name: 'Page header',
                        icon: 'icon--block-header',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                page_footer: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_footer.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'page_footer',
                        name: 'Page footer',
                        icon: 'icon--block-footer',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                tbl_one_column: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/tbl_one_column.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_tbl_one_column.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'tbl_one_column',
                        name: 'Table(1 col)',
                        icon: 'icon--block-table',
                        icon_supplement: '1',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                tbl_two_columns: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/tbl_two_columns.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_tbl_two_columns.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'tbl_two_columns',
                        name: 'Table(2 cols)',
                        icon: 'icon--block-table',
                        icon_supplement: '2',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                barcode: {
                    template: 'layouts/v7/modules/QuotingTool/resources/js/views/blocks/barcode.html',
                    setting_template: 'layouts/v7/modules/QuotingTool/resources/js/views/popups/settings_barcode.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'barcode',
                        name: 'BAR CODE',
                        icon: 'icon--block-barcode',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }
                },
                spacer: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/spacer.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_spacer.html',
                    layout: {
                        id: 'spacer',
                        name: 'Spacer',
                        icon: 'icon--spacer',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                icon_label: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/icon_label.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_icon_label.html',
                    layout: {
                        id: 'icon_label',
                        name: 'Icon Label',
                        icon: 'material-icons',
                        icon_name: 'star',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                team_member: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/team_member.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_team_member.html',
                    layout: {
                        id: 'team_member',
                        name: 'Team_Member',
                        icon: 'icon--team-member',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                image_box1: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box1.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box1',
                        name: 'image_box(1)',
                        icon: 'icon--block-image',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                image_box2: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box2.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box2',
                        name: 'image_box(2)',
                        icon: 'material-icons',
                        icon_name: 'photo_library',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                image_box3: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box3.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box3',
                        name: 'image_box(3)',
                        icon: 'material-icons ',
                        icon_name: 'filter',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                feature_right: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/feature_right.html',
                    layout: {
                        id: 'feature_right',
                        name: 'feature right',
                        icon: 'material-icons',
                        icon_name: 'burst_mode',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                feature_left: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/feature_left.html',
                    layout: {
                        id: 'feature_left',
                        name: 'feature left',
                        icon: 'material-icons',
                        icon_name: 'chrome_reader_mode',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                pricing_plans_triple: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_plans_triple.html',
                    layout: {
                        id: 'pricing_plans_triple',
                        name: 'Pricing Plans #2',
                        icon: 'material-icons',
                        icon_name: 'view_week',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                pricing_plans_single: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_plans_single.html',
                    layout: {
                        id: 'pricing_plans_single',
                        name: 'Pricing Plans #1',
                        icon: 'material-icons',
                        icon_name: 'view_list',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                bill_ship: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/bill_ship.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'bill_ship',
                        name: 'BILL/SHIP ADDRESS',
                        icon: 'icon--bill-ship',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true,
                    },
                },
                portfolio4x2: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio4x2.html',
                    layout: {
                        id: 'portfolio4x2',
                        name: 'Portfolio (4x2)',
                        icon: 'material-icons',
                        icon_name: 'filter_3',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                portfolio3x2: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio3x2.html',
                    layout: {
                        id: 'portfolio3x2',
                        name: 'Portfolio (3x2)',
                        icon: 'material-icons',
                        icon_name: 'filter_2',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                portfolio2x2: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio2x2.html',
                    layout: {
                        id: 'portfolio2x2',
                        name: 'Portfolio (2x2)',
                        icon: 'material-icons',
                        icon_name: 'filter_1',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                inline_input_field: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/inline_input_field.html',
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_inline_input_field.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_text_field.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'inline_input_field',
                        name: 'INLINE INPUT FIELD',
                        icon: 'clear_all',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true
                    }
                },
                inline_primary_signature: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/inline_primary_signature.html',
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_inline_primary_signature.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_signature.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'inline_primary_signature',
                        name: 'Primary Signature (Inline)',
                        icon: 'icon--sign',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true
                    }
                },
                inline_secondary_signature: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/inline_secondary_signature.html',
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_inline_primary_signature.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_signature.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'inline_secondary_signature',
                        name: 'Secondary Signature (Inline)',
                        icon: 'icon--sign',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true
                    }
                },
            },
            blocksOrder: [
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/init.html',
                    settings: {},
                    layout: {
                        id: 'init',
                        name: '',
                        icon: '',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/text.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'text',
                        name: 'TEXT AREA',
                        icon: 'icon--block-text',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/heading.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_heading.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize', 'Format']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
                            },
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ],
                        format_tags: 'h1;h2;h3;h4;h5;h6',
                        keystrokes: [[13 /*Enter*/, 'doNothing']]
                    },
                    layout: {
                        id: 'heading',
                        name: 'HEADING',
                        icon: 'icon--block-heading',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/line_hr.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'line_hr',
                        name: 'SEPARATOR DIVIDER',
                        icon: 'fa-arrows-h force-fa',
                        wide_width: true,
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_image.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'image',
                        name: 'IMAGE',
                        icon: 'icon--block-image',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/video.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'video',
                        name: 'VIDEO',
                        icon: 'icon--block-video',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/spacer.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_spacer.html',
                    layout: {
                        id: 'spacer',
                        name: 'SPACER',
                        icon: 'icon--spacer',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/table.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'table',
                        name: 'TABLE',
                        icon: 'icon--block-table',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_break.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'page_break',
                        name: 'PAGE BREAK',
                        icon: 'icon--block-pagebreak',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/cover_page.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline']},
                            {
                                name: 'paragraph',
                                items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'cover_page',
                        name: 'COVER PAGE',
                        icon: 'icon--block-cover',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_header.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'page_header',
                        name: 'PAGE HEADER',
                        icon: 'icon--block-header',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/page_footer.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'page_footer',
                        name: 'PAGE FOOTER',
                        icon: 'icon--block-footer',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/bill_ship.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'bill_ship',
                        name: 'BILL/SHIP ADDRESS',
                        icon: 'icon--bill-ship',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true,
                    },
                },
                {
                    template: '',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_product_image.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'settings_product_image',
                        name: 'settings_product_image',
                        icon: 'icon--block-image',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/text_field.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_text_field.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'text_field',
                        name: 'VTIGER FIELD (EDITABLE)',
                        icon: 'icon--text-field',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true
                    }
                },
                // {
                //     template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/textarea_field.html',
                //     setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_textarea_field.html',
                //     settings: {
                //         toolbar: [
                //             {name: 'clipboard', items: ['Undo', 'Redo']},
                //             {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                //             {name: 'colors', items: ['TextColor', 'BGColor']},
                //             {name: 'about', items: ['About']}
                //         ],
                //         enterMode: CKEDITOR.ENTER_BR
                //     },
                //     layout: {
                //         id: 'textarea_field',
                //         name: 'VTIGER FIELD (EDITABLE)',
                //         icon: 'icon--text-field',
                //         enable_setting: true,
                //         enable_remove: true,
                //         enable_move: true,
                //         is_data_elements: true
                //     }
                // },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/create_related_record.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_create_related_record.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'create_related_record',
                        name: 'REL.MODULE (ADD NEW)',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        icon_supplement: 'REL',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/related_module.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_related_module.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'related_module',
                        name: 'REL.MODULE (LIST)',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        icon_supplement: 'REL',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/inline_input_field.html',
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_inline_input_field.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_text_field.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'inline_input_field',
                        name: 'INLINE INPUT FIELD',
                        icon: 'material-icons',
                        icon_name: 'clear_all',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/tbl_two_columns.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_tbl_two_columns.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'tbl_two_columns',
                        name: 'Table',
                        name_row2: '(2 COLUMNS)',
                        icon: 'icon--block-table',
                        icon_supplement: '2',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/tbl_one_column.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_tbl_one_column.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'tbl_one_column',
                        name: 'Table',
                        name_row2: '(1 COLUMNS)',
                        icon: 'icon--block-table',
                        icon_supplement: '1',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_table.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_pricing_table.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'pricing_table',
                        name: 'Pricing table',
                        icon: 'icon--block-pricingtable',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_table_idc.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_pricing_table_idc.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'insert', items: ['Image', 'Table']},
                            {
                                name: 'basicstyles',
                                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                            },
                            {
                                name: 'paragraph',
                                items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                    'JustifyRight', 'JustifyBlock']
                            },
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'pricing_table_idc',
                        name: 'Pricing table(IDC)',
                        icon: 'icon--block-pricingtable',
                        icon_supplement: 'IDC',
                        icon_helptext: 'i',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_data_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/toc.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'toc',
                        name: 'Toc',
                        icon: 'icon--block-toc',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/line_break.html',
                    settings: {
                        toolbar: [
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']}
                        ]
                    },
                    layout: {
                        id: 'line_break',
                        name: 'Line break',
                        icon: 'fa fa-level-down force-fa',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_design_elements: true
                    }
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box1.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box1',
                        name: 'IMAGE BOX(1x)',
                        icon: 'icon--block-image',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box2.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box2',
                        name: 'IMAGE BOX(2x)',
                        icon: 'material-icons',
                        icon_name: 'photo_library',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/image_box3.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                    layout: {
                        id: 'image_box3',
                        name: 'IMAGE BOX(3x)',
                        icon: 'material-icons ',
                        icon_name: 'filter',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio2x2.html',
                    layout: {
                        id: 'portfolio2x2',
                        name: 'Portfolio (1x)',
                        icon: 'material-icons',
                        icon_name: 'filter_1',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio3x2.html',
                    layout: {
                        id: 'portfolio3x2',
                        name: 'Portfolio (2x)',
                        icon: 'material-icons',
                        icon_name: 'filter_2',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/portfolio4x2.html',
                    layout: {
                        id: 'portfolio4x2',
                        name: 'Portfolio (3x)',
                        icon: 'material-icons',
                        icon_name: 'filter_3',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/icon_label.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_icon_label.html',
                    layout: {
                        id: 'icon_label',
                        name: 'Icon Label',
                        icon: 'material-icons',
                        icon_name: 'star',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/feature_left.html',
                    layout: {
                        id: 'feature_left',
                        name: 'FEATURE LEFT',
                        icon: 'material-icons',
                        icon_name: 'chrome_reader_mode',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/feature_right.html',
                    layout: {
                        id: 'feature_right',
                        name: 'FEATURE RIGHT',
                        icon: 'material-icons',
                        icon_name: 'burst_mode',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/team_member.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_team_member.html',
                    layout: {
                        id: 'team_member',
                        name: 'Team Member',
                        icon: 'icon--team-member',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_plans_single.html',
                    layout: {
                        id: 'pricing_plans_single',
                        name: 'Pricing Plans #1',
                        icon: 'material-icons',
                        icon_name: 'view_list',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/pricing_plans_triple.html',
                    layout: {
                        id: 'pricing_plans_triple',
                        name: 'Pricing Plans #2',
                        icon: 'material-icons',
                        icon_name: 'view_week',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }, settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'insert', items: ['Image', 'Youtube', 'Table', 'Abbr']},
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
                    },
                },
                {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/blocks/barcode.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_barcode.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'barcode',
                        name: 'BAR CODE',
                        icon: 'icon--block-barcode',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        is_fancy_elements: true,
                    }
                },


            ],
            widgets: {
                init: {
                    template: '',
                    settings: {},
                    layout: {
                        id: 'init',
                        name: '',
                        icon: '',
                        enable_setting: false,
                        enable_remove: false,
                        enable_move: false
                    }
                },
                custom_functions:{
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_custom_functions.html',
                    layout:{
                        id: 'custom_functions'
                    }
                },
                field_image: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/field_image.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_field_image.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'tools', items: ['Sourcedialog', 'QuotingTool_Duplicate']},
                            {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'field_image',
                        name: 'IMAGE FIELD',
                        icon: 'icon--block-image',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        not_digital: true,
                        other: true
                    }
                },
                text_field: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/text_field.html',
                    popup_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_vtiger_field.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_text_field.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'text_field',
                        name: 'VTIGER INPUT FIELD',
                        icon: 'icon--text-field',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        icon_helptext: 'i',
                        not_digital: true,
                        other: true
                    }
                },
                textarea_field: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/textarea_field.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_textarea_field.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ],
                        enterMode: CKEDITOR.ENTER_BR
                    },
                    layout: {
                        id: 'textarea_field',
                        name: 'TEXTAREA INPUT FIELD',
                        icon: 'material-icons ',
                        icon_name: 'wrap_text',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        not_digital: true,
                        other: true
                    }
                },
                signature: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/signature.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_signature.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'signature',
                        name: 'Primary Signature',
                        icon: 'icon--sign',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true
                    }
                },
                secondary_signature: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/secondary_signature.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_secondary_signature.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'paragraph', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'secondary_signature',
                        name: 'Secondary Signature',
                        icon: 'icon--sign',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true
                    }
                },
                initials: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/initials.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_initials.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'initials',
                        name: 'Initials',
                        icon: 'icon--initials',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true
                    }
                },
                date: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/date.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_date.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'date',
                        name: 'DATE',
                        icon: 'icon--date',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        other: true
                    }
                },
                datetime: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/datetime.html',
                    setting_template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/settings_datetime.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'datetime',
                        name: 'DATE SIGNED (SECONDARY)',
                        icon: 'icon--date',
                        enable_setting: true,
                        enable_remove: true,
                        enable_move: true,
                        other: true
                    }
                },
                checkbox: {
                    template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/widgets/checkbox.html',
                    settings: {
                        toolbar: [
                            {name: 'clipboard', items: ['Undo', 'Redo']},
                            {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                            {name: 'colors', items: ['TextColor', 'BGColor']},
                            {name: 'links', items: ['Link', 'Unlink']},
                            {name: 'about', items: ['About']}
                        ]
                    },
                    layout: {
                        id: 'checkbox',
                        name: 'Checkbox',
                        icon: 'icon--checkbox',
                        enable_setting: false,
                        enable_remove: true,
                        enable_move: true,
                        not_digital: true,
                        other: true,
                    }
                }
            },
            source_editor: {
                settings: {
                    toolbar: [
                        {
                            name: 'document',
                            groups: ['mode', 'tools'],
                            items: ['Source', 'Maximize']
                        },
                        {name: 'clipboard', items: ['Undo', 'Redo']},
                        {name: 'styles', items: ['Styles', 'Font', 'FontSize']},
                        {name: 'colors', items: ['TextColor', 'BGColor']},
                        {name: 'insert', items: ['Image']},
                        {name: 'links', items: ['Link', 'Unlink']},
                        {
                            name: 'basicstyles',
                            items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                        },
                        {
                            name: 'paragraph',
                            items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                'JustifyRight', 'JustifyBlock', 'BidiLtr', 'BidiRtl']
                        },
                        {name: 'about', items: ['About']}
                    ]
                }
            },
            email_template: {
                template: 'layouts/vlayout/modules/QuotingTool/resources/js/views/popups/email_template.html',
                settings: {
                    fullPage: true,
                    height: 320,
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
                            items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter',
                                'JustifyRight', 'JustifyBlock', 'BidiLtr', 'BidiRtl']
                        },
                        {name: 'about', items: ['About']}
                    ]
                },
                css: {
                    'width': '796px',
                    'z-index': '9999'
                }
            }
        },
        SECTIONS: {
            // tabs
            BLOCKS: 'blocks',
            WIDGETS: 'widgets',
            TOKENS: 'tokens',
            DECISION: 'decision',
            THEMES: 'themes',
            HISTORIES: 'histories',
            // Content tab
            CONTENT_PROPERTIES: 'properties',
            CONTENT_PRODUCT_BLOCK: 'product_block',
            CONTENT_RELATED_BLOCK: 'related_block',
            CONTENT_OTHERS: 'other_information',
            // Properties tab
            PROPERTIES_BASIC: 'basic',
            PROPERTIES_ADVANCE: 'advance',
            PROPERTIES_OTHERS: 'others',
            // General tab
            GENERAL_ACCEPT: 'proposal_accept',
            GENERAL_DECLINE: 'proposal_decline',
            GENERAL_BACKGROUND: 'themes_background_image',
            GENERAL_OTHERS: 'proposal_others',
            GENERAL_SUCCESSCONTENT:'proposal_success_content',
            GENERAL_EMAIL_SIGNED:'proposal_email_signed',
            GENERAL_ANWIDGET: 'proposal_anwidget',
            GENERAL_ANBLOCK: 'proposal_anblock',
            GENERAL_CREATENEWRECORDS: 'proposal_createnewrecords',
            GENERAL_FILENAME:'proposal_filename',
            GENERAL_SHARING: 'proposal_sharing',
            GENERAL_THEME: 'proposal_theme',
            GENERAL_LAYOUT:'proposal_layout',
            GENERAL_COMMENT:'proposal_comment',
            // History tab
            HISTORY_TAB1: 'history_tab1'
        }

    });

})();
