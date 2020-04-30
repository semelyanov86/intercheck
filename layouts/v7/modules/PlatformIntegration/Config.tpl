<div class="tabbable margin0px" style="padding-bottom: 20px;">
    <ul id="extensionTab" class="nav nav-tabs" style="margin-bottom: 0px; padding-bottom: 0px;">
        <li class="active"><a href="#tabCustomer" data-toggle="tab"><strong>{vtranslate('LBL_VTEQBO_TAB_CUSTOMER', $QUALIFIED_MODULE)}</strong></a></li>
    </ul>
    
    <div class="tab-content row-fluid boxSizingBorderBox" style="background-color: #fff; padding: 20px; border: 3px solid #eeeff2; border-top-width: 0px; margin-left: 1px;">
        <div class="tab-pane active" id="tabCustomer">
            <div class="container-fluid">
                <div class="row">
                    {include file='ConfigByModule.tpl'|@vtemplate_path:$QUALIFIED_MODULE CONFIG_DATA=$CONTACT_FIELDS}
                    <div class="col-lg-2"></div>
                    {include file='ConfigByModule.tpl'|@vtemplate_path:$QUALIFIED_MODULE CONFIG_DATA=$ACCOUNT_FIELDS}
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="col-md-12 text-right">
        <a class="btn btn-default" href="index.php?module=PlatformIntegration&parent=Settings&view=Settings">{vtranslate('BTN_CANCEL', $QUALIFIED_MODULE)}</a>
    </div>
</div>