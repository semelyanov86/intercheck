Vtiger_Edit_Js("Emails_Edit_js", {}, {
    /*
 * function to load CKEditor
 */
    registerEventToLoadEditor : function(){
//		var aDeferred = jQuery.Deferred();
//		data = data.children();
//		jQuery( '#editor1',data ).ckeditor(function(){
//			aDeferred.resolve(data);
//		},{});
//		return aDeferred.promise();

        var instance = CKEDITOR.instances['description'];
        if(instance)
        {
            CKEDITOR.remove(instance);
        }

        //configured ckeditor toolbar for vtiger
        var Vtiger_ckeditor_toolbar =
            [
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['NumberedList','BulletedList','-','Outdent','Indent'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Anchor'],
                ['Source','-','NewPage','Preview','Templates'],
                '/',
                ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                ['Image','Table','HorizontalRule','SpecialChar','PageBreak','TextColor','BGColor'], //,'Smiley','UniversalKey'],
                '/',
                ['Styles','Format','Font','FontSize']
            ];
        CKEDITOR.replace( 'description',
            {
                fullPage : true,
                extraPlugins : 'docprops',
                toolbar : Vtiger_ckeditor_toolbar
            });

        jQuery('.blockPage').addClass('sendEmailBlock');
    },
    /**
     * Function which will register basic events which will be used in quick create as well
     *
     */
    registerBasicEvents : function(container) {
        this._super(container);
        this.registerEventToLoadEditor(container);
    }
})