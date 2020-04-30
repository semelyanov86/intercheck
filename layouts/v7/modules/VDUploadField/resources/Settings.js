Vtiger.Class("VDUploadField_Settings_Js",{

},{   
    
    //updatedBlockSequence : {},
    registerAddButtonEvent: function () {
        var thisInstance=this;
        jQuery('.settingsPageDiv').on("click",'.addButton', function(e) {
            var source_module = jQuery('#tableBlockModules').val();
            if(source_module !='' && source_module !='All') {
                var url=jQuery(e.currentTarget).data('url') + '&source_module='+source_module;
            }else {
                var url=jQuery(e.currentTarget).data('url');
            }
            thisInstance.showEditView(url,true);
        });
    },
    registerEditButtonEvent: function() {
        var thisInstance=this;
        jQuery(document).on("click",".editBlockDetails", function(e) {
            var url = jQuery(this).data('url');
            thisInstance.showEditView(url,false);
        });
    },
    /*
     * function to show editView for Add/Edit block
     * @params: url - add/edit url
     */
    showEditView : function(url,is_create_new) {
        var thisInstance = this;
        app.helper.showProgress('');
        var actionParams = {
            "url":url
        };
        app.request.post(actionParams).then(
            function(err,data){
                if(err === null) {
                    app.helper.hideProgress();
                    var callBackFunction = function(data) {
                        var frm = jQuery('#tableblocks_form');
                        var params = app.validationEngineOptions;
                        params.submitHandler = function (frm) {
                            thisInstance.saveAdvancedCustomFieldsDetails(frm);
                        };
                        frm.vtValidate(params);

                        frm.submit(function(e) {
                            e.preventDefault();
                        })
                    };
                    app.helper.showModal(data, {'width': '400px', 'cb' : function (data){
                        if(typeof callBackFunction == 'function'){
                            callBackFunction(data);
                        }
                        thisInstance.registerPopupEvents();
                        if(!is_create_new){
                            var module_selected =  jQuery("#s2id_select_module").find('span').first().html();
                            jQuery("#s2id_select_module").html(module_selected);
                            jQuery("#s2id_select_module").css('margin-top','5px');
                            var field_type =  jQuery("#s2id_select_type").find('span').first().html();
                            jQuery("#s2id_select_type").html(field_type);
                            jQuery("#s2id_select_type").css('margin-top','5px');
                            jQuery("#name").prop('disabled', true);
                        }
                    }});
                }else{
                    app.helper.hideProgress();
                }
            }
        );
    },

   
     registerPopupEvents: function() {
         var container=jQuery('#massEditContainer');
         this.registerPopupSelectModuleEvent(container);
     },

    registerPopupSelectModuleEvent : function(container) {
        var thisInstance = this;
        container.on("change",'[name="select_module"]', function(e) {
            app.helper.showProgress('');
            var select_module=jQuery(this).val();
            var params = {
                "type":"POST",
                "url": "index.php?module=VDUploadField&view=EditAjax&mode=getBlocks&select_module="+select_module,
                "dataType":"html"
            };
            app.request.post(params).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        container.find('#div_blocks').html(data);
                        // TODO Make it better with jQuery.on
                        app.changeSelectElementView(container); 
                        app.showSelect2ElementView(container.find('select.select2'));
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );       
        })
    },
    registerDeleteVDUploadFieldEvent: function () {
        var thisInstance = this;
        var contents = jQuery('.listViewEntriesDiv');
        contents.on('click','.deleteBlock', function(e) {
            var element=jQuery(e.currentTarget);
            var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
            app.helper.showConfirmationBox({'message' : message}).then(
                function(e) {
                   var blockId = jQuery(element).data('id');
                   var params = {};
                   params['module'] = 'VDUploadField';
                   params['action'] = 'ActionAjax';
                   params['mode'] = 'deleteVDUploadField';
                   params['record'] = blockId;
                   app.request.post({'data':params}).then(
                       function(err,data){
                           if(err === null) {
                               thisInstance.loadListAdvancedCustomFields();
                           }
                       }
                   );
                },
                function(error, err){
                }
            );
        });
    },
    registerEvents : function() {
        this.registerAddButtonEvent();
        this.registerEditButtonEvent();
        this.registerDeleteVDUploadFieldEvent();        
    }
});

jQuery(document).ready(function(){
    var instance = new VDUploadField_Settings_Js();
    instance.registerEvents();
    
    // Fix issue not display menu
    Vtiger_Index_Js.getInstance().registerEvents();
});