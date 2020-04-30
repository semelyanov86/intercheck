<div class="related-tabs">
    <form class="" method="POST" id="frmSaveAPI" action="">
        <input type="hidden" name="module" value="{$QUALIFIED_MODULE}"/>
        <input type="hidden" name="action" value="SaveAjax"/>
        <input type="hidden" name="mode" value="saveMainConfig"/>
        {foreach item=FIELD_VALUE key=FIELD_NAME from=$QBO_API}
        {if $FIELD_NAME eq 'sync2vt'}
        <div class="row form-group">
            <div class="col-md-3">
                <label  for="chkSyncToVtiger">{vtranslate("LBL_ENABLE_SYNC_TO_VTIGER", $QUALIFIED_MODULE)}</label>
            </div>
            <div class="col-md-4">
                <input type="checkbox" id="chkSyncToVtiger" class="enableSync" name="{$FIELD_NAME}"
                {if $FIELD_VALUE eq 1} checked="checked" {/if} value="1"
                    data-on-text="{vtranslate('TEXT_ON', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('TEXT_OFF', $QUALIFIED_MODULE)}" data-on-color="success">
            </div>
        </div>
        {else if $FIELD_NAME eq 'sync2platform'}
        <div class="row form-group">
            <div class="col-md-3">
                <label  for="chkSyncToQuickBooks">{vtranslate("LBL_ENABLE_SYNC_TO_QUICKBOOKS", $QUALIFIED_MODULE)}</label>
            </div>
            <div class="col-md-4">
                <input type="checkbox" class="enableSync" id="chkSyncToQuickBooks"  name="{$FIELD_NAME}"
                    {if $FIELD_VALUE eq 1} checked="checked" {/if} value="1"
                    data-on-text="{vtranslate('TEXT_ON', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('TEXT_OFF', $QUALIFIED_MODULE)}" data-on-color="success">
            </div>
        </div>
        {else if $FIELD_NAME eq 'primary_datasource'}
        <div class="row form-group">
            <div class="col-md-3">
                <label  for="cbb_{$FIELD_NAME}">{vtranslate("LBL_PRIMARY_DATASOURCE", $QUALIFIED_MODULE)}</label>
                &nbsp;<span style="color: red;">(*)</span>
                &nbsp;<a class="vteqbo-tooltip" data-trigger="hover" data-html="True" data-placement="top" 
                    data-content="{$TOOLTIP_INFO_PD}"
                    style="display: inline-block; float: none;"><i class="fa fa-info-circle"></i></a>
            </div>
            <div class="col-md-2">
                <select class="inputElement" id="cbb_{$FIELD_NAME}" name="{$FIELD_NAME}" required>
                    <option></option>
                    <option value="VTiger" {if $FIELD_VALUE eq 'VTiger'} selected {/if}>{vtranslate('LBL_PRIMARY_DATASOURCE_VTIGER', $QUALIFIED_MODULE)}</option>
                    <option value="QuickBooks" {if $FIELD_VALUE eq 'QuickBooks'} selected {/if}>{vtranslate('LBL_PRIMARY_DATASOURCE_QUICKBOOKS', $QUALIFIED_MODULE)}</option>
                </select>
            </div>
        </div>
        {else if $FIELD_NAME eq 'qbo_version'}
        <div class="row form-group">
            <div class="col-md-3">
                <label  for="cbb_{$FIELD_NAME}">{vtranslate("LBL_QBO_VERSION", $QUALIFIED_MODULE)}</label>
                &nbsp;<span style="color: red;">(*)</span>
                &nbsp;<a class="vteqbo-tooltip" data-trigger="hover" data-html="True" data-placement="top" 
                    data-content="{$TOOLTIP_INFO_QBO_VERSION}"
                    style="display: inline-block; float: none;"><i class="fa fa-info-circle"></i></a>
            </div>
            <div class="col-md-2">
                <select class="inputElement" id="cbb_{$FIELD_NAME}" name="{$FIELD_NAME}" required>
                    {foreach item=QBO_VERSION from=$SUPPORTED_QBO_VERSIONS}
                    <option value="{$QBO_VERSION}" {if $FIELD_VALUE eq $QBO_VERSION} selected {/if}>{vtranslate('LBL_QBO_VERSION_'|cat:$QBO_VERSION, $QUALIFIED_MODULE)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {else}
        <input type="hidden" name="{$FIELD_NAME}" value="{$FIELD_VALUE}"/>
        {/if}
        {/foreach}
        <div class="row form-group">
            <div class="col-md-12">
                <button class="btn btn-primary" id="btnSaveMainConfig" value="">{vtranslate('BTN_SAVE', $QUALIFIED_MODULE)}</button>
            </div>
        </div>
    </form>
</div>