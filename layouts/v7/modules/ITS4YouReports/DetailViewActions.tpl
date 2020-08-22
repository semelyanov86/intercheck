{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="listViewPageDiv">
        <div class="reportHeader">
            <div class="row">
                <div class="col-lg-3">
                    <div class="btn-toolbar">
                        <div class="btn-group">
                            {assign var=BTN_I value='0'}
                            {foreach item=DETAILVIEW_LINK from=$DETAILVIEW_ACTIONS}
                                {assign var=LINK_URL value=$DETAILVIEW_LINK->getUrl()}
                                {assign var=LINK_NAME value=$DETAILVIEW_LINK->getLabel()}
                                {assign var=LINK_ICON_CLASS value=$DETAILVIEW_LINK->get('linkiconclass')}
                                {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}
                                    <div class="btn-group">
                                {/if}
                                <button {if $LINK_URL} onclick='window.location.href = "{$LINK_URL}"' {/if} type="button"
                                                                                                            class="cursorPointer btn btn-default {$DETAILVIEW_LINK->get('customclass')}
                                               {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach' && count($DASHBOARD_TABS) gt 1} dropdown-toggle{/if}"
                                                                                                            title="{if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}
                                        {if $REPORT_MODEL->isPinnedToDashboard()}{vtranslate('LBL_UNPIN_CHART_FROM_DASHBOARD', $MODULE)}{else}{vtranslate('LBL_PIN_CHART_TO_DASHBOARD', $MODULE)}{/if}
                                        {else}{$DETAILVIEW_LINK->get('linktitle')}{/if}" {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach' && count($DASHBOARD_TABS) gt 1 }data-toggle="dropdown"{/if}
                                    {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}data-dashboard-tab-count='{count($DASHBOARD_TABS)}'{/if}
                                    style="
                                        {if 0 < $BTN_I}
                                            margin-left:5px;
                                        {/if}
                                    "
                                >
                                    {if $LINK_NAME} {$LINK_NAME}{/if}
                                    {if $LINK_ICON_CLASS}
                                        {if $LINK_ICON_CLASS eq 'icon-pencil'}&nbsp;&nbsp;&nbsp;{/if}
                                        <i class="fa {if $LINK_ICON_CLASS eq 'icon-pencil'}fa-pencil{elseif $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}
                                        {if $REPORT_MODEL->isPinnedToDashboard()}vicon-unpin{else}vicon-pin{/if}{/if}" style="font-size: 13px;"></i>
                                    {/if}
                                </button>
                                {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}
                                    <ul class='dropdown-menu dashBoardTabMenu'>
                                        <li class="dropdown-header popover-title">
                                            {vtranslate('LBL_DASHBOARD',$MODULE)}
                                        </li>
                                        {foreach from=$DASHBOARD_TABS item=TAB_INFO}
                                            <li class='dashBoardTab' data-tab-id='{$TAB_INFO.id}'>
                                                <a href='javascript:void(0)'> {$TAB_INFO.tabname}</a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                                {if $LINK_ICON_CLASS eq 'vtGlyph vticon-attach'}
                                    </div>
                                {/if}
                                {$BTN_I = $BTN_I+1}
                            {/foreach}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 textAlignCenter">
                    <h3 class="marginTop0px">{$REPORT_MODEL->getName()}</h3>
                    <div id="noOfRecords">{vtranslate('LBL_NO_OF_RECORDS',$MODULE)} <span id="countValue">{$COUNT}</span>
                        {if $COUNT > 1000}
                            <span class="redColor" id="moreRecordsText"> ({vtranslate('LBL_MORE_RECORDS_TXT',$MODULE)})</span>
                        {else}
                            <span class="redColor hide" id="moreRecordsText"> ({vtranslate('LBL_MORE_RECORDS_TXT',$MODULE)})</span>
                        {/if}
                    </div>
                    {if 'custom_report' neq $REPORT_MODEL->getReportType()}
		                    <div class="limitsAreaInfoColored" id="limitOfRecords">
														{vtranslate('LBL_LIMITED',$MODULE)}: (
														{if $COLUMNS_LIMIT > 0}
																{vtranslate('SET_LIMIT',$MODULE)} {$COLUMNS_LIMIT}
														{/if}
														{if $COLUMNS_LIMIT > 0 && $SUMMARIES_LIMIT > 0}, {else if $COLUMNS_LIMIT == 0 && $SUMMARIES_LIMIT == 0}{vtranslate('LBL_ALL_RECORDS',$MODULE)}{/if}
														{if $SUMMARIES_LIMIT > 0}
																{vtranslate('SUMMARIES_LIMIT',$MODULE)} {$SUMMARIES_LIMIT}
														{/if}
														)
												</div>
										{/if}
                    <div id='activate_pdfmaker' class="fieldValue" style="display:block;">
                        <span class="value">
                        {if $PDFMakerActive !== true}
                            {vtranslate('Please_Install_PDFMaker',$MODULE)}
                        {/if}
                        {if $IS_TEST_WRITE_ABLE !== true}
                            {vtranslate('Test_Not_WriteAble',$MODULE)}
                        {/if}
                        </span>
                    </div>
                </div>
                <div class='col-lg-3'>
                    <span class="pull-right">
                        <div class="btn-toolbar">
                            <div class="btn-group">
                                {foreach item=DETAILVIEW_LINK from=$DETAILVIEW_LINKS}
                                    {assign var=LINKNAME value=$DETAILVIEW_LINK->getLabel()}
                                    <button class="btn btn-default reportActions" name="{$LINKNAME}"
                                            data-href="{$DETAILVIEW_LINK->getUrl()}&source={$REPORT_MODEL->getReportType()}"
                                    style="margin-left:5px;"
                                    >
                                        {$LINKNAME}
                                    </button>
                                {/foreach}
                            </div>
                        </div>
                    </span>
                </div>
            </div>
        </div>
    </div>
{/strip}