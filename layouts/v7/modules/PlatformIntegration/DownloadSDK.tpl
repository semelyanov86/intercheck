{*<!--
/* ********************************************************************************
 * The content of this file is subject to the Quoting Tool ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div class="contentsDiv marginLeftZero">
        <div class="padding1per">
            <div class="editContainer" style="padding-left: 3%; padding-right: 3%">
                <br>
                <h3>{vtranslate('VTEQBO', $QUALIFIED_MODULE)} {vtranslate('LBL_INSTALL', $QUALIFIED_MODULE)}</h3>
                <hr>
                <form class="form-horizontal">
                    <div class="row">
                        <ul>
                            <li>QuickBooks-V3-PHP-SDK</li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="controls">
                            <div>
                                <strong>{vtranslate('LBL_DOWNLOAD_SRC', $QUALIFIED_MODULE)}</strong>
                            </div>
                            <br>

                            <div class="clearfix">
                            </div>
                        </div>
                        <div class="controls">
                            <div>
                                <p>{vtranslate('LBL_DOWNLOAD_SRC_DESC1', $QUALIFIED_MODULE)}</p>
                                <input type="url" value="{$QBO_SDK_LINK}" disabled="disabled" style="width: 30%;"/>
                                <p style="padding-top: 5px">{vtranslate('LBL_DOWNLOAD_SRC_DESC2', $QUALIFIED_MODULE)}</p>
                                <input type="text" value="{$QBO_SDK_SOURCE}" disabled="disabled" style="width: 30%"/>
                                {if $MB_STRING_EXISTS eq 'false'}
                                    <br>
                                    {vtranslate('LBL_MB_STRING_ERROR', $QUALIFIED_MODULE)}
                                {/if}
                            </div>
                            <br>

                            <div class="clearfix">
                            </div>
                        </div>
                        <div class="controls">
                            <button type="button" id="vteqbo_download_button" class="btn btn-success">
                                <strong>{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</strong>
                            </button>
                            &nbsp;&nbsp;
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}