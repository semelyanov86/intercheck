 var supportedVDFields = {
    "Upload_Field": {'uitype': 1, 'name': "Upload field", 'prefix': 'cf_vd_ulf'},    
};

Vtiger.Class("VDUploadField_Js",{

},{
    registerLoadVDUploadFieldControl : function(){
        jQuery("textarea").each(function(){
            var is_rtf = jQuery(this).attr('name');
            var view = app.getViewName();
            if(typeof is_rtf != "undefined" && view =="Edit") {
                
            }

        });
        jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
            var is_upload_field= jQuery(this).attr('name');
            var view = app.getViewName();
            if(typeof is_upload_field != "undefined" && view =="Edit") {
                if(is_upload_field.substring(0, 9) === supportedVDFields.Upload_Field.prefix){
                    // var span_parent = jQuery( this).parents('span');
                    var span_parent = jQuery( this).parents('td');
                    span_parent.prepend('<div  id = "frm_'+is_upload_field+'">'
                    + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                    + '</div>');
                    jQuery( this).hide();
                }
            }

        });

        app.event.one("post.QuickCreateForm.show",function(event,form){
            jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
                var is_upload_field= jQuery(this).attr('name');
                if(typeof is_upload_field != "undefined") {
                    if(is_upload_field.substring(0, 9) === supportedVDFields.Upload_Field.prefix){
                        // var span_parent = jQuery( this).parents('span');
                        var span_parent = jQuery( this).parents('td');
                        span_parent.prepend('<div  id = "frm_'+is_upload_field+'">'
                            + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                            + '</div>');
                        jQuery( this).hide();
                    }
                }

            });
        });

        app.event.on("post.overLayEditView.loaded",function(event,form){
            jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
                var is_upload_field= jQuery(this).attr('name');
                if(typeof is_upload_field != "undefined") {
                    if(is_upload_field.substring(0, 9) === supportedVDFields.Upload_Field.prefix){
                        // var span_parent = jQuery( this).parents('span');
                        var html_content = jQuery( this).val();
                        if(html_content != "" && jQuery(this).attr('type') == "text"){
                            var res = html_content.split("$$");
                            if(res.length > 0){
                                var fieldName = jQuery(this).context.name;
                                var curfieldarr = fieldName.split('_');
                                var parent_span = jQuery( this).parent('td');
                                parent_span.find('span').remove();
                                parent_span.find('img').remove();
                                var recordId = app.getRecordId();
                                if (!recordId) {
                                    recordId = app.convertUrlToDataParams(window.location.href).record;
                                }

                                var file_path = res[0].replace(/\s+/g," ").trim();
                                var file_name = file_path.split('/');
                                file_name = file_name[file_name.length - 1];
                                file_name = file_name.split('_');
                                var html = [
                                    '<span>',
                                    '<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+recordId+'&parent=' + app.getModuleName() + '&fieldid=' + curfieldarr.pop() +'" download target="_blank">' +file_name[1]+'</a>',
                                    '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">',
                                    '</span>'
                                ].join('');
                                parent_span.append(html);
                            }
                        } else {
                            var span_parent = jQuery( this).parents('td');
                            span_parent.prepend('<div  id = "frm_'+is_upload_field+'">'
                                + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                                + '</div>');
                        }
                        jQuery( this).hide();

                    }
                }

            });
        });
    },
    registerDisplayVDUploadFieldControl : function(){
        //parseHTML
        var view = app.getViewName();

        var thisInstance = this;

        if(view == 'Edit'){
            jQuery( "input[name^='" + supportedVDFields.Upload_Field.prefix + "']").each(function(){
                var html_content = jQuery( this).val();
                if(html_content != "" && jQuery(this).attr('type') == "text"){
                    var res = html_content.split("$$");
                    if(res.length > 0){
                        var fieldName = jQuery(this).context.name;
                        var curfieldarr = fieldName.split('_');
                        var parent_span = jQuery( this).parent('td');
                        parent_span.find('span').remove();
                        parent_span.find('img').remove();
                        var recordId = app.getRecordId();
                        if (!recordId) {
                            recordId = app.convertUrlToDataParams(window.location.href).record;
                        }

                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split('_');
                            var html = [
                                '<span>',
                                '<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&record='+recordId+'&parent=' + app.getModuleName() + '&fieldid=' + curfieldarr.pop() +'" download target="_blank">' +file_name[1]+'</a>',
                                '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">',
                                '</span>'
                            ].join('');
                            parent_span.append(html);
                        }
                    }
                // }
            });
        }
        else if(view == 'Detail'){
            var thisInstance = this;
            this.showLinks();
            app.event.on("post.overlay.load", function (event, data) {
                thisInstance.showLinks();
            });
        }
    },
    showLinks:function() {
        jQuery("[id*='fieldValue_" + supportedVDFields.Upload_Field.prefix + "']").each(function() {
            var current = jQuery( this).find("span.value");
            var curfieldarr = current.context.id.split('_');
            if(current.length == 0){
                return;
            }
            var html_content = current.html().trim();
            var next_element = current.next('span');
            while(next_element.length > 0){
                next_element.remove();
                next_element = current.next('span');
            }
            if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                var res = html_content.split("$$");
                if(res.length > 0){
                    /*if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                        var img = res[0].replace(/\s+/g," ").trim();
                        jQuery( this ).html('<img style="width:150px;height:150px;" src="'+img+'" />');
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
        });
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
                    loadScript('layouts/v7/modules/Vtiger/resources/CkEditor.js', function () {
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
    window.onbeforeunload = null;
    var instance = new VDUploadField_Js();
    instance.registerEvents();
    jQuery( document ).ajaxComplete(function(event, xhr, settings) {
        var url = settings.data;
        if(typeof url == 'undefined' && settings.url) url = settings.url;
        var top_url = window.location.href.split('?');
        var array_url = instance.getQueryParams(top_url[1]);
        if(typeof array_url == 'undefined') return false;
        var other_url = instance.getQueryParams(url);
        if(array_url.view == 'Detail' && (array_url.mode == 'showDetailViewByMode' || other_url.action == 'SaveAjax')) {
            instance.registerDisplayVDUploadFieldControl();
        }        
        if(other_url.view == 'EditViewAjax' && other_url.mode == 'showModuleEditView') {
            instance.registerDisplayVDUploadFieldControl();
            instance.registerLoadVDUploadFieldControl();
        }        
        if (settings.data && typeof settings.data == 'string' && settings.data.indexOf("mode=unHide") > -1){
            hideAllSmartChangeButtonForVDUF();
        }
    });
    
    hideAllSmartChangeButtonForVDUF();
});

function hideAllSmartChangeButtonForVDUF(){
    var container = $("[data-field-name*='cf_acf_']").closest(".ui-sortable-handle");
    container.find(".mandatory").hide();
    container.find(".quickCreate").hide();
    container.find(".massEdit").hide();
    container.find(".summary").hide();
    container.find(".defaultValue").hide();
    container.find(".header").hide();
}

function avcf_upload_files(is_upload_field){
    var fileSelect = document.getElementsByName('upload_' + is_upload_field + '[]');
    // Get the selected files from the input.
    var files = fileSelect[0].files;
    // Create a new FormData object.
    var formData = new FormData();
    // Loop through each of the selected files.
    for (var i = 0; i < files.length; i++) {
      var file = files[i];

      // Add the file to the request.
      formData.append('upload_' + is_upload_field + '[]', file, file.name);
    }
    formData.append('field_name', is_upload_field);
    $.ajax({
        url: "index.php?module=VDUploadField&action=ActionAjax&mode=ajaxUploadFromForm&parent=KYC",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false
    }).done(function( data ) {
        var target_input = document.getElementsByName(is_upload_field);
        if (!data.result) {
            app.helper.showErrorNotification({'message' : 'Error while uploading file'});
            return false;
        }
        var return_file = data.result.list_file;
        jQuery(target_input).val(return_file[0]);
        var res = return_file[0].split("$$");
        if(res.length > 0){
            var parent_span = jQuery(target_input).closest('td');
            parent_span.find('img').remove();
            parent_span.find('span').remove();
/*            if(res[2].indexOf('image') > -1){
                var img = res[0].replace(/\s+/g," ").trim();
                var html = [
                    '<span>',
                    '<img style="width:150px;height:150px;" src="'+img+'" />',
                    '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+return_file+'" type="button" value="Delete" onclick="removeThis(this)">',
                    '</span>'
                ].join('');
                parent_span.append(html);
            }
            else{*/
                var file_path = res[0].replace(/\s+/g," ").trim();
                var file_name = file_path.split('/');
                file_name = file_name[file_name.length - 1];
                file_name = file_name.split('_');
                var html = [
                    '<span>',
                    '<a href="index.php?module=VDUploadField&action=ActionAjax&mode=downloadFile&file='+return_file[0]+'" download targer="_blank">' +file_name[1]+'</a>',
                    '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+return_file[0]+'" type="button" value="Delete" onclick="removeThis(this)">',
                    '</span>'
                ].join('');
                parent_span.append(html);

            // }
            $('[name="upload_'+is_upload_field+'[]"]').val("");
        }
    });
    return false;
}

function removeThis(btn){
    var file = jQuery(btn).data('file');
    var field_name = jQuery(btn).data('field_name');
    var parrent_record_id = app.getRecordId();
    var parent_span = jQuery(btn).parent('span').parent('td');
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
            var aDeferred = jQuery.Deferred();
            app.helper.showProgress();

            var params = form.serializeFormData();
            var supportedFields = Object.keys(supportedVDFields);
            if (supportedFields.indexOf(params.fieldType) > -1){
                params['module'] = 'VDUploadField';
                params['action'] = 'ActionAjax';
                params['mode'] = 'addField';
                params['masseditable'] = '2';
            } else {
                params['module'] = thisInstance.getModuleName();
                params['parent'] = app.getParentModuleName();
                params['action'] = 'Field';
                params['mode'] = 'add';
            }
            params['blockid'] = blockId;
            params['sourceModule'] = jQuery('#selectedModuleName').val();
            params['fieldLength'] = parseInt(params['fieldLength']);
            if (params['decimal'])
                params['decimal'] = parseInt(params['decimal']);

            if (!this.isHeaderAllowed() && params.headerfield == true) {
                aDeferred.reject();
            } else {
                this.updateHeaderFieldMeta(params);
                app.request.post({'data': params}).then(
                    function (err, data) {
                        app.helper.hideProgress();
                        if (err === null) {
                            var fieldId = data.id;
                            var headerFieldValue = data.isHeaderField ? 1 : 0;
                            thisInstance.headerFieldsMeta[fieldId] = headerFieldValue;
                            aDeferred.resolve(data);
                            location.reload();
                        } else {
                            aDeferred.reject(err);
                        }
                    });
            }
            return aDeferred.promise();
        }
        
        $("body").delegate("#createFieldForm [name='fieldType']", "change", function(){
            var supportedFields = Object.keys(supportedVDFields);
            fieldType = $(this).val();
            if (supportedFields.indexOf(fieldType) > -1){
                $("#createFieldForm [name='masseditable']").trigger("click");
                $("#createFieldForm [name='fieldDefaultValue']").closest(".form-group").hide();
                $("#createFieldForm .fieldProperty").hide();
            } else {
                $("#createFieldForm [name='fieldDefaultValue']").closest(".form-group").show();
                $("#createFieldForm .fieldProperty").show();
            }
        });
        
        registerEditFieldButton();
    } else {
        setTimeout(function(){
            overwriteFunctionAddCustomField();
        }, 10);
    }
}

function registerEditFieldButton(){
    $("body").delegate('.editFieldDetails', 'click', function (e) {
        hideOptionsOnEditPopup();
    });
}

function hideOptionsOnEditPopup(){
    if ($("#createFieldForm [name='fieldType']").length > 0){
        // Append the new custom UITypes into dropdown
        for (var key in supportedVDFields) {
            if (supportedVDFields.hasOwnProperty(key)) {
                $("#createFieldForm [name='fieldType']").append(new Option(supportedVDFields[key]['name'], key, false, false));
            }
        }
        var fieldName = $("#createFieldForm [name='fieldname']").val();
        var prefix = fieldName.substring(0, 10);
        if (prefix == supportedVDFields.Upload_Field.prefix){
            $("#createFieldForm [name='fieldType']").val("Upload_Field");
        }
        $("#createFieldForm [name='fieldType']").trigger("change");
    } else {
        setTimeout(function(){
            hideOptionsOnEditPopup();
        }, 10);
    }
}