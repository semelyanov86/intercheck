{*/* * *******************************************************************************
* The content of this file is subject to the VTEQBO ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
<div class="container-fluid">
    <h4>{vtranslate('LBL_PLATFORM_ONLINE_INTEGRATION', $QUALIFIED_MODULE)}</h4>
    <hr>
    <div class="tabbable margin0px" style="padding-bottom: 20px;">
        <ul id="extensionTab" class="nav nav-tabs" style="margin-bottom: 0px; padding-bottom: 0px;">
            <li class=""><a href="#tabQBOAPI" data-toggle="tab"><strong>{vtranslate('LBL_PLATFORMINTEGRATION_TAB_QBOAPI', $QUALIFIED_MODULE)}</strong></a></li>
            {foreach item=TAB key=TAB_NAME from=$ALL_TAB}
            <li class=""><a href="#{$TAB_NAME}" data-toggle="tab"><strong>{vtranslate($TAB_NAME, $QUALIFIED_MODULE)}</strong></a></li>
            {/foreach}
            <li class=""><a href="#tabMainConfig" data-toggle="tab"><strong>{vtranslate('LBL_PLATFORMINTEGRATION_TAB_CONFIG', $QUALIFIED_MODULE)}</strong></a></li>
            <li class=""><a href="#tabSynchronize" data-toggle="tab"><strong>{vtranslate('LBL_PLATFORMINTEGRATION_TAB_SYNCHRONIZE', $QUALIFIED_MODULE)}</strong></a></li>
        </ul>
        
        <div class="tab-content row-fluid boxSizingBorderBox" style="background-color: #fff; padding: 20px; border: 1px solid #eeeff2; border-top-width: 0px; margin-left: 1px;">
            <div class="tab-pane" id="tabQBOAPI">
                <div class="container-fluid">
                    {include file='PlatformApi.tpl'|@vtemplate_path:$QUALIFIED_MODULE QBO_API=$PLATFORM_API}
                </div>
                <div class="clearfix"></div>
            </div>
            {foreach item=TAB key=TAB_NAME from=$ALL_TAB}
            <div class="tab-pane" id="{$TAB_NAME}">
                <div class="container-fluid">
                    <div class="row">
                        {foreach item=CONFIG_DATA key=VTMODULE from=$TAB}
                        {if $VTMODULE neq 'OtherInfo'}
                            {include file='ConfigByModule.tpl'|@vtemplate_path:$QUALIFIED_MODULE CONFIG_DATA=$CONFIG_DATA}
                            <div class="col-lg-2"></div>
                        {/if}
                        {/foreach}
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            {/foreach}
            <div class="tab-pane" id="tabMainConfig">
                <div class="container-fluid">
                    {include file='MainConfig.tpl'|@vtemplate_path:$QUALIFIED_MODULE QBO_API=$PLATFORM_API}
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="tab-pane" id="tabSynchronize">
                <div class="container-fluid">
                    {include file='Synchronize.tpl'|@vtemplate_path:$QUALIFIED_MODULE ALL_TAB=$ALL_TAB}
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

</div>
{/strip}