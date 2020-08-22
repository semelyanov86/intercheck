{*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
    <div class="container-fluid" id="UninstallITS4YouReportsContainer">
        <form name="settings_edit" action="index.php" method="post" class="form-horizontal">
            <br>
            <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_SETTINGS',$MODULE)}</label>
            <br clear="all">
            <hr>
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="view" value="{$VIEW}" />
            <input type="hidden" name="mode" value="SaveSettings" />
            <input type="hidden" name="msg_saved" value="{$MSG_SAVED}" />
            <div class="row-fluid">
                <table class="table table-bordered table-condensed themeTableColor">
                    <thead>
                    <tr class="blockHeader">
                        <th class="mediumWidthType" colspan="2">
                            <span><strong>{vtranslate('LBL_SETTINGS',$MODULE)}</strong></span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td width="25%">
                            <label class="muted pull-right marginRight10px"><strong>{vtranslate('LBL_SHARING',$MODULE)}:</strong></label>
                        </td>
                        <td style="border-left: none;">
                            <div class="pull-left" style="float:left;">
                                <select name="default_sharing" id="default_sharing" class="span3 chzn-select row">
                                    {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
                                </select>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            {if $MODE eq "edit"}
            		<br>
                <div class="pull-right">
                    <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
                    <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            {/if}
        </form>
    </div>
{/strip}