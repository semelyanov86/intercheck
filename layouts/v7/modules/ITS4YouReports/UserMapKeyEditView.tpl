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
        <form name="user_map_key_edit" action="index.php" method="post" class="form-horizontal">
            <br>
            <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_USER_API_KEY_SETTINGS',$MODULE)}</label>
            <br clear="all">
            <hr>
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="view" value="{$VIEW}" />
            <input type="hidden" name="mode" value="SaveApiKey" />
            <input type="hidden" name="msg_saved" value="{$MSG_SAVED}" />
            <div class="row-fluid">
                <table class="table table-bordered table-condensed themeTableColor">
                    <thead>
                    <tr class="blockHeader">
                        <th class="mediumWidthType" colspan="2">
                            <span><strong>{vtranslate('LBL_DEFINE_API_KEY_DESC_BING',$MODULE)}</strong></span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td width="25%">
                            <label class="muted pull-right marginRight10px"><strong>{vtranslate('API_KEY_BING',$MODULE)}:</strong></label>
                        </td>
                        <td style="border-left: none;">
                            <div class="pull-left col-lg-2">
                                <select name="maps_api_use_type" id="maps_api_use_type" class="select2 inputElement row">
                                    <option value="default" {if 'detault' eq $MAPS_API_USE_TYPE}selected="selected"{/if} >
                                        {vtranslate('LBL_DEFAULT', $MODULE)}
                                    </option>
                                    <option value="user" {if 'user' eq $MAPS_API_USE_TYPE}selected="selected"{/if} >
                                        {vtranslate('LBL_USER_DEFINED', $MODULE)}
                                    </option>
                                </select>
                            </div>
                            <div class="pull-left col-lg-5 maps_api_keys">
                                {if $IS_ADMIN_USER}
                                    <input name="maps_api_key_default" id="maps_api_key_default" autocomplete="off" class="inputElement {if 'default' neq $MAPS_API_USE_TYPE}hide{/if}" value="{$DEFAULT_MAPS_API_KEY}"
                                           placeholder="{vtranslate('LBL_DEFAULT_KEY_PLACEHOLDER', $MODULE)}">
                                {/if}
                                <input name="maps_api_key_user" id="maps_api_key_user" autocomplete="off" class="inputElement {if 'user' neq $MAPS_API_USE_TYPE}hide{/if}" value="{$MAPS_API_KEY}"
                                       placeholder="{vtranslate('LBL_USER_DEFINED_KEY_PLACEHOLDER', $MODULE)}">
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            {if $MODE eq "edit"}
                <div class="pull-right">
                    <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
                    <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            {/if}
        </form>
    </div>
{/strip}