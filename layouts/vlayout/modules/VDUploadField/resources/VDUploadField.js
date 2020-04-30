var supportedVDFields = {
    "Upload_Field": {'uitype': 1, 'name': "Upload field", 'prefix': 'cf_vd_ulf'}
};

jQuery.Class("VDUploadField_Js",{

},{
    registerLoadVDUploadFieldControl : function(){
        jQuery("textarea").each(function(){
            var is_rtf = jQuery(this).attr('name');
            var view = app.getViewName();
            

        });
        jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
            var is_upload_field= jQuery(this).attr('name');
            var view = app.getViewName();
            if(typeof is_upload_field != "undefined" && jQuery(this).is(":visible")  && view =="Edit") {
                if(is_upload_field.substring(0, 9) === supportedVDFields.Upload_Field.prefix){
                    var span_parent = jQuery( this).parents('span');
                    span_parent.prepend('<form  id = "frm_'+is_upload_field+'" method="POST" action ="index.php" enctype="multipart/form-data">'
                    + '<input type="hidden" value="' + is_upload_field + '" name="field_name">'
                    + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                    + '</form>');
                    jQuery( this).hide();
                }
            }

        });
        var headerInstance = Vtiger_Header_Js.getInstance();
        headerInstance.registerTabEventsInQuickCreate = function(form) {
            var tabElements = form.find('.nav.nav-pills , .nav.nav-tabs').find('a');

            //This will remove the name attributes and assign it to data-element-name . We are doing this to avoid
            //Multiple element to send as in calendar
            var quickCreateTabOnHide = function(tabElement) {
                var container = jQuery(tabElement.attr('data-target'));

                container.find('[name]').each(function(index, element) {
                    element = jQuery(element);
                    element.attr('data-element-name', element.attr('name')).removeAttr('name');
                });
            }

            //This will add the name attributes and get value from data-element-name . We are doing this to avoid
            //Multiple element to send as in calendar
            var quickCreateTabOnShow = function(tabElement) {
                var container = jQuery(tabElement.attr('data-target'));

                container.find('[data-element-name]').each(function(index, element) {
                    element = jQuery(element);
                    element.attr('name', element.attr('data-element-name')).removeAttr('data-element-name');
                });
            }

            tabElements.on('shown', function(e) {
                var previousTab = jQuery(e.relatedTarget);
                var currentTab = jQuery(e.currentTarget);

                quickCreateTabOnHide(previousTab);
                quickCreateTabOnShow(currentTab);

                //while switching tabs we have to clear the invalid fields list
                form.data('jqv').InvalidFields = [];

            });

            //To show aleady non active element , this we are doing so that on load we can remove name attributes for other fields
            quickCreateTabOnHide(tabElements.closest('li').filter(':not(.active)').find('a'));
            jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
                var is_upload_field= jQuery(this).attr('name');
                var view = app.getViewName();
                if(typeof is_upload_field != "undefined" && jQuery(this).is(":visible")) {
                    if(is_upload_field.substring(0, 9) === supportedVDFields.Upload_Field.prefix){
                        var span_parent = jQuery( this).parents('td');
                        span_parent.prepend('<form  id = "frm_'+is_upload_field+'" method="POST" action ="index.php" enctype="multipart/form-data">'
                            + '<input type="hidden" value="' + is_upload_field + '" name="field_name">'
                            + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                            + '</form>');
                        jQuery( this).hide();
                    }
                }

            });
        }
    },
    registerDisplayVDUploadFieldControl : function(){
        //parseHTML
        var view = app.getViewName();
        if(view == 'Edit'){
            jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){                
                var html_content = jQuery( this).val();
                if(html_content != "" && jQuery(this).attr('type') == "text"){
                    var res = html_content.split("$$");
                    if(res.length > 0){
                        var parent_span = jQuery( this).parent('span');
                        parent_span.find('span').remove();
                        parent_span.find('img').remove();
                        /*if(res[2].indexOf('image') > -1){
                            var img = res[0].replace(/\s+/g," ").trim();
                            parent_span.append('<img  style="width:200px;height:200px;" src="'+img+'" />');
                            parent_span.append('<span style="float: right;margin-top:52px;">'
                            +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">'
                            +'</span>');
                        }
                        else{*/
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split('_');
                            parent_span.append('<br /><span><a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&file='+html_content+'" download targer="_blank">' +file_name[1]+'</a></span>');
                            parent_span.append('<span style="float: right;margin-top:-3px;">'
                            +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">'
                            +'</span>');
                        // }
                    }
                }
            });
        }
        else if(view == 'Detail'){
            jQuery( "span.value" ).each(function() {
                var parrent_td =  jQuery(this).closest('td').attr('id');

                var is_rtf = 0;
                var is_upload_field = 0;
                var is_field_by_val = 0;
                if(typeof parrent_td != "undefined") {            
                    if(parrent_td.indexOf(supportedVDFields.Upload_Field.prefix)!== -1){
                        is_upload_field = 1;
                    }
                }
                else{
                    var next_span =  jQuery(this).next('span.hide').find('textarea').attr('name');                    
                }
                if (!parrent_td) {
                    var curval = jQuery(this).text();
                    if (curval.indexOf('$$') > 0) {
                        is_field_by_val = 1;
                    }
                }
                if(is_upload_field == 1){
                    var html_content = jQuery( this).html();
                    var parenttd = jQuery(this).parent('td');
                    var curfieldarr = parenttd[0].id.split('_');
                    html_content = html_content.trim();
                    jQuery( this).next('span').remove();
                    if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                        var res = html_content.split("$$");
                        if(res.length > 0){
                            /*if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                                var img = res[0].replace(/\s+/g," ").trim();
                                jQuery( this ).html('<img src="'+img+'" />');
                            }
                            else{*/
                                var file_path = res[0].replace(/\s+/g," ").trim();
                                var file_name = file_path.split('/');
                                file_name = file_name[file_name.length - 1];
                                file_name = file_name.split('_');

                                jQuery( this ).html('<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+app.getRecordId()+'&parent=' + app.getModuleName() + '&fieldid=' + curfieldarr.pop() +'" target="_blank">' +file_name[1]+'</a>');
                            // }
                        }
                    }
                    is_upload_field = 0;
                } else if (is_field_by_val == 1) {
                    var html_content = jQuery( this).html();
                    var parenttd = jQuery(this).parent();
                    var inputelem = parenttd.find('input');
                    if (inputelem) {
                        var curfieldarr = inputelem[0].id.split('_');
                    } else {
                        var curfieldarr = [];
                    }
                    if (curfieldarr.length > 0) {
                        html_content = html_content.trim();
                        jQuery( this).next('span').remove();
                        jQuery(parenttd[0]).children()[1].remove();

                        if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                            var res = html_content.split("$$");
                            if(res.length > 0){
                                /*if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                                    var img = res[0].replace(/\s+/g," ").trim();
                                    jQuery( this ).html('<img src="'+img+'" />');
                                }
                                else{*/
                                var file_path = res[0].replace(/\s+/g," ").trim();
                                var file_name = file_path.split('/');
                                file_name = file_name[file_name.length - 1];
                                file_name = file_name.split('_');

                                jQuery( this ).html('<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+app.getRecordId()+'&parent=' + app.getModuleName() + '&fieldid=' + curfieldarr.pop() +'" target="_blank">' +file_name[1]+'</a>');
                                // }
                            }
                        }
                    }
                    is_field_by_val = 0;
                }

            });
            jQuery("td[data-field-type='string']").each(function () {
               var curval = jQuery(this).text();
                var html_content = jQuery( this).html();
                var curid = jQuery(this).parent()[0].dataset.id;
                html_content = html_content.trim();
                if(curval.indexOf('$$') > 0){
                    if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                        var res = html_content.split("$$");
                        if(res.length > 0){
                            var fieldid = res[3] - 1;
                            var detailInstance = Vtiger_Detail_Js.getInstance();
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split('_');
                            jQuery( this ).html('<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+curid+'&parent=' + detailInstance.getRelatedModuleName() + '&fieldid=' + fieldid +'">' +file_name[1]+'</a>');
                        }
                    }
                }

            });

        } else if (view == 'List') {
            var listInstance = Vtiger_List_Js.getInstance();
            var cvId = listInstance.getCurrentCvId();
            jQuery.ajax({
                'url': "?module=VDUploadField&action=ActionAjax&mode=getCVIDData&cvid=" + cvId + "&parent=" + app.getModuleName(),
                'type': 'GET'
            }).done(function (data) {
                if (data.success) {
                    var curfield = false;
                    var counttr = 0;
                    var numtr = 0;
                    for (var prop in data.result) {
                        if (data.result[prop].name.indexOf(supportedVDFields.Upload_Field.prefix) !== -1) {
                            curfield = data.result[prop];
                            numtr = counttr;
                            break;
                        }
                        counttr++;
                    }
                    numtr++;
                    counttr++;
                    if (curfield) {
                        jQuery('tr').each(function () {
                            if (jQuery(this)[0].id) {
                                var pretext = $(this).find("td:eq(" + counttr + ")")[0].innerText;
                                var res = pretext.split("$$");
                                var file_path = res[0].replace(/\s+/g," ").trim();
                                var file_name = file_path.split('/');
                                file_name = file_name[file_name.length - 1];
                                var curnum = curfield.id - 1;
                                $(this).find("td:eq(" + counttr + ")").html('<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+$(this)[0].dataset.id+'&parent=' + app.getModuleName() + '&fieldid=' + curnum +'">' + file_name +'</a>');
                            }

                        })
                    }

                }

            })
        }
    },
    getQueryParams:function(qs) {
        if(typeof(qs) != 'undefined' ){
            qs = qs.toString().split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;
            while (tokens = re.exec(qs)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }
            return params;
        }
    },
    registerEvents: function() {
        var thisInstance = this;

        if (typeof CkEditor === 'undefined') {
            loadScript('libraries/jquery/ckeditor/ckeditor.js', function () {
                loadScript('libraries/jquery/ckeditor/adapters/jquery.js', function () {
                    loadScript('layouts/vlayout/modules/Vtiger/resources/CkEditor.js', function () {
                        thisInstance.registerLoadVDUploadFieldControl();
                    });
                });
            });
        } else {
            thisInstance.registerLoadVDUploadFieldControl();
        }

        thisInstance.registerDisplayVDUploadFieldControl();
        
        // 123720
        thisInstance.createUITypeOption();
    },
    
    createUITypeOption: function(){
        var moduleName = app.getModuleName();
        if (moduleName == 'LayoutEditor'){
            // Append the new custom UITypes into dropdown
            for (var key in supportedVDFields) {
                if (supportedVDFields.hasOwnProperty(key)) {
                    $("select[name=fieldType]").append(new Option(supportedVDFields[key]['name'], key, false, false));
                }
            }
            
            // Overwrite event submit form
            overwriteFunctionAddCustomField();
        }
        
        $("[name='layoutEditorModules']").change(function(){
            var url = "index.php?module=LayoutEditor&parent=Settings&view=Index&sourceModule=" + $(this).val();
            location.href = url;
        });
    }

});

jQuery(document).ready(function(){
    var instance = new VDUploadField_Js();
    instance.registerEvents();
    jQuery( document ).ajaxComplete(function(event, xhr, settings) {
        var url = settings.data;
        if(typeof url == 'undefined' && settings.url) url = settings.url;
        var top_url = window.location.href.split('?');
        var array_url = instance.getQueryParams(top_url[1]);
        if(typeof array_url == 'undefined') return false;
        var other_url = instance.getQueryParams(url);
        if(array_url.view == 'Detail' && (array_url.mode == 'showDetailViewByMode' || array_url.mode == 'showRelatedList' || other_url.action == 'SaveAjax')) {
            instance.registerDisplayVDUploadFieldControl();
        }
    });
    
    if ($("#module").val() == "LayoutEditor"){
        $("body").delegate('.editFieldDetails', 'click', function (e) {
            $(this).parent().find("span>input[name='mandatory']").parent().hide();
            $(this).parent().find("span>input[name='quickcreate']").parent().hide();
            $(this).parent().find("span>input[name='masseditable']").parent().hide();
            $(this).parent().find("span>input[name='defaultvalue']").parent().hide();
        });
    }
});

function avcf_upload_files(is_upload_field){
    var form_data = new FormData(document.getElementById("frm_"+is_upload_field));
    jQuery.ajax({
        url: "?module=VDUploadField&action=ActionAjax&mode=ajaxUploadFromForm&parent=" + app.getModuleName(),
        type: "POST",
        data: form_data,
        processData: false,  // tell jQuery not to process the data
        contentType: false   // tell jQuery not to set contentType
    }).done(function( data ) {
        var target_input = document.getElementsByName(is_upload_field);
        if (!data.result) {
            var message = 'Error in uploading file to server';
            var params = {
                text: message,
                type: 'error'
            };
            Vtiger_Helper_Js.showMessage(params);
            return false;
        }
        var return_file = data.result.list_file;
        jQuery(target_input).val(return_file[0]);
        var res = return_file[0].split("$$");
        if(res.length > 0){
            var parent_span = jQuery(target_input).closest('span');
            parent_span.find('img').remove();
            parent_span.find('span').remove();
/*            if(res[2].indexOf('image') > -1){
                var img = res[0].replace(/\s+/g," ").trim();
                parent_span.append('<img style="width:150px;height:150px;" src="'+img+'" />');
                parent_span.append('<span style="float: right;margin-top:52px;">'
                +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+return_file+'" type="button" value="Delete" onclick="removeThis(this)">'
                +'</span>');
            }
            else{*/
                var file_path = res[0].replace(/\s+/g," ").trim();
                var file_name = file_path.split('/');
                file_name = file_name[file_name.length - 1];
                file_name = file_name.split('_');
                parent_span.append('<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&file='+return_file[0]+'" download targer="_blank">' +file_name[1]+'</a>');
                parent_span.append('<span style="float: right;margin-top:-3px;">'
                +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+return_file[0]+'" type="button" value="Delete" onclick="removeThis(this)">'
                +'</span>');

            // }

        }
    });
    return false;
}

function removeThis(btn){
    var file = jQuery(btn).data('file');
    var field_name = jQuery(btn).data('field_name');
    var parrent_record_id = app.getRecordId();
    var parent_span = jQuery(btn).parent('span').parent('span');
    var url = 'index.php?module=VDUploadField&action=ActionAjax&mode=removeFile';
    jQuery.ajax({
        url: url,
        data: { file_path : file,parrent_record_id:parrent_record_id,field_name:field_name},
        async:false,
        success: function(data) {
                parent_span.find( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").val('');
                parent_span.find('img').remove();
                parent_span.find('span').remove();
        }
    });
}

/**
 * @Link http://stackoverflow.com/questions/950087/how-to-include-a-javascript-file-in-another-javascript-file#answer-950146
 */
function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}

function overwriteFunctionAddCustomField(){
    if (typeof Settings_LayoutEditor_Js == 'function'){
        Settings_LayoutEditor_Js.prototype.addCustomField = function(blockId, form) {
            var thisInstance = this;
            var modalHeader = form.closest('#globalmodal').find('.modal-header h3');
            var aDeferred = jQuery.Deferred();

            modalHeader.progressIndicator({smallLoadingImage : true, imageContainerCss : {display : 'inline', 'margin-left' : '18%',position : 'absolute'}});
            var params = form.serializeFormData();
            var supportedFields = Object.keys(supportedVDFields);
            if (supportedFields.indexOf(params.fieldType) > -1){
                params['module'] = 'VDUploadField';
                params['action'] = 'ActionAjax';
                params['mode'] = 'addField';
            } else {
                params['module'] = app.getModuleName();
                params['parent'] = app.getParentModuleName();
                params['action'] = 'Field';
                params['mode'] = 'add';
            }
            params['blockid'] = blockId;
            params['sourceModule'] = jQuery('#selectedModuleName').val();
            
            AppConnector.request(params).then(
                function(data) {
                    modalHeader.progressIndicator({'mode' : 'hide'});
                    aDeferred.resolve(data);
                    location.reload();
                },
                function(error) {
                    modalHeader.progressIndicator({'mode' : 'hide'});
                    aDeferred.reject(error);
                }
            );
            return aDeferred.promise();
        }
    } else {
        setTimeout(function(){
            overwriteFunctionAddCustomField();
        }, 10);
    }
}