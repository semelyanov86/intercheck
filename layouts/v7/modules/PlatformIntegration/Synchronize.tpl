<div class="related-tabs">
    <form class="" method="POST" id="frmSynchronize" action="">
        <div class="row form-group">
            <div class="col-md-9">
            {foreach item=TAB key=TAB_NAME from=$ALL_TAB}
                {if $TAB_NAME neq ''}
                <div class="row form-group" data-tab="{$TAB_NAME}">
                    <div class="col-md-4">
                        <input type="checkbox" class="allowSyncField" data-tab='{$TAB_NAME}' value="1"
                            {if $TAB['OtherInfo']['allow_sync'] eq 1} checked="checked" {/if}
                            {if $TAB['OtherInfo']['vt_tab_id'] eq ''} disabled {/if}
                            data-on-text="{vtranslate('TEXT_ON', $QUALIFIED_MODULE)}" 
                            data-off-text="{vtranslate('TEXT_OFF', $QUALIFIED_MODULE)}" 
                            data-on-color="success">
                        <label>{vtranslate($TAB_NAME, $QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="col-md-5 text-right">
                        {if $TAB['OtherInfo']['tooltip'] neq ''}
                            {if $TAB['OtherInfo']['tooltip_requires'] neq ''}
                            <a class="vteqbo-tooltip" data-trigger="hover" data-html="True" data-placement="top" data-content="{vtranslate($TAB['OtherInfo']['tooltip_requires'], $QUALIFIED_MODULE)}"><i class="fa fa-info-circle"></i></a>&nbsp;
                            {else}
                            <a class="vteqbo-tooltip" data-trigger="hover" data-html="True" data-placement="top" data-content="{vtranslate($TAB['OtherInfo']['tooltip'], $QUALIFIED_MODULE)}"><i class="fa fa-info-circle"></i></a>&nbsp;
                            {/if}
                        {/if}
                        {if $TAB['OtherInfo']['has_from_date'] eq '1'}
                            <div class="input-group inputElement vteqboDate">
                                <input class='dateField form-control ' type="text" data-tab='{$TAB_NAME}' data-date-format='{$DATE_FORMAT}'
                                    value='{$TAB['OtherInfo']['from_date']}'
                                    {if $TAB['OtherInfo']['tooltip_requires'] neq ''}disabled{/if}
                                    />
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        {/if}
                        {if gettype(strpos($TAB['OtherInfo']['sync_scope'], 'Platform2VT')) neq 'boolean'}
                        <a href="javascript: void(0);" class="btn btn-primary btnSync {if $TAB['OtherInfo']['tooltip_requires'] neq ''}disabled{/if}" data-syncType="Platform2VT"
                            {if $TAB['OtherInfo']['tooltip_requires'] neq ''}disabled{/if}>{vtranslate("LBL_SYNC_TO_VTIGER", $QUALIFIED_MODULE)}</a>
                        {/if}
                    </div>
                    <div class="col-md-3">
                        {if gettype(strpos($TAB['OtherInfo']['sync_scope'], 'VT2Platform')) neq 'boolean'}
                        <a href="javascript: void(0);" class="btn btn-success btnSync {if $TAB['OtherInfo']['tooltip_requires'] neq ''}disabled{/if}" data-syncType="VT2Platform"
                            {if $TAB['OtherInfo']['tooltip_requires'] neq ''}disabled data-disabled="True"{/if}>{vtranslate("LBL_SYNC_TO_PLATFORM", $QUALIFIED_MODULE)}</a>
                        {/if}
                    </div>
                </div>
                {/if}
            {/foreach}
            </div>
            <div class="col-md-3">
                <div class="row form-group">
                    <div class="col-md-12 text-right">
                        <a href="index.php?module=PlatformIntegrationQueues&view=List" class="btn btn-warning btnVTEQBOQueues" target='_blank'>{vtranslate("LBL_PLATFORMINTEGRATIONQUEUES", $QUALIFIED_MODULE)}</a>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12 text-right">
                        <a href="index.php?module=PlatformIntegrationLogs&view=List" class="btn btn-warning btnVTEQBOLogs" target='_blank'>{vtranslate("LBL_PLATFORMINTEGRATIONLOGS", $QUALIFIED_MODULE)}</a>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12 text-right">
                        <a href="index.php?module=PlatformIntegrationLinks&view=List" class="btn btn-warning btnVTEQBOLinks" target='_blank'>{vtranslate("LBL_PLATFORMINTEGRATIONLINKS", $QUALIFIED_MODULE)}</a>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12 text-right">
                        <a href="javascript: void(0);" class="btn btn-success btnSaveDate">{vtranslate("BTN_SAVE", $QUALIFIED_MODULE)}</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="clear"></div>
    <br /><br />
    <hr />
    <br /><br />
    <div class="row form-group">
        <div class="col-md-12 vteqboDateFilters">
            <div class="btn-group dateFilters pull-left" role="group" aria-label="...">
                <button type="button" class="btn btn-default" data-filtermode="all">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</button>
                <button type="button" class="btn btn-default active" data-filtermode="today">{vtranslate('LBL_TODAY', $QUALIFIED_MODULE)}</button>
                <button type="button" class="btn btn-default" data-filtermode="thisweek">{vtranslate('LBL_THIS_WEEK', $QUALIFIED_MODULE)}</button>
                <button type="button" class="btn btn-default dateRange dateField"
                    data-calendar-type="range" data-filtermode="range"><i class="fa fa-calendar"></i></button>
                <button type="button" class="btn btn-default hide rangeDisplay">
                    <span class="selectedRange"></span>&nbsp;
                    <i class="fa fa-times clearRange"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-6 text-center">
            <table id="vtigerSummary" class="table  listview-table  floatThead-table">
                <thead>
                    <tr>
                        <th colspan="4">{{vtranslate('LBL_VTIGER', $QUALIFIED_MODULE)}}</th>
                    </tr>
                    <tr>
                        <th style="width: 40%;">{{vtranslate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_CREATED', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_UPDATED', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_FAILED', $QUALIFIED_MODULE)}}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-md-6 text-center">
            <table id="qboSummary" class="table  listview-table  floatThead-table">
                <thead>
                    <tr>
                        <th colspan="4">{{vtranslate('LBL_QUICKBOOKS', $QUALIFIED_MODULE)}}</th>
                    </tr>
                    <tr>
                        <th style="width: 40%;">{{vtranslate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_CREATED', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_UPDATED', $QUALIFIED_MODULE)}}</th>
                        <th style="width: 20%;">{{vtranslate('LBL_FAILED', $QUALIFIED_MODULE)}}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>