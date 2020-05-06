{*<!--
/* ********************************************************************************
* The content of this file is subject to the Multiple SMTP ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */
-->*}
<div class="container-fluid WidgetsManage">
    <div class="widget_header row">
        <div class="col-sm-6"><h4><label>{vtranslate('MultipleSMTP', 'MultipleSMTP')}</label>
        </div>
    </div>
    <hr>
    <div class="clearfix"></div>
    <div class="summaryWidgetContainer">
    <div class="widget_header row">
        <div class="form-group col-sm-3">
            <span style="padding-left:10px;">
            <label>{vtranslate('LBL_ENABLE_MODULE', 'MultipleSMTP')}</label>
                <input class="pull-right defaultWidget" type="checkbox" name="enable_module" id="enable_module" value="1" {if $ENABLE eq '1'}checked="" {/if}/>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<div>
    <div style="padding: 10px; text-align: justify; font-size: 14px; border: 1px solid #ececec; border-left: 5px solid #2a9bbc; border-radius: 5px; overflow: hidden;">
        <h4 style="color: #2a9bbc; margin: 0px -15px 10px -15px; padding: 0px 15px 8px 15px; border-bottom: 1px solid #ececec;"><i class="fa fa-info-circle"></i>&nbsp;&nbsp;{vtranslate('LBL_INFO_BLOCK', $QUALIFIED_MODULE)}</h4>
        {vtranslate('LBL_INFO_BLOCK_ON_SETTING_PAGE', $QUALIFIED_MODULE)}
    </div>
</div>