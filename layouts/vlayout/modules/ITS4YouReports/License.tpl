{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<div class="container-fluid" id="licenseContainer">
    
        <form name="profiles_privilegies" {*id="editLicense"*}  action="index.php" method="post" class="form-horizontal">
    <br>
    <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_LICENSE','ITS4YouReports')}</label>
    <br clear="all">
    <hr>

        <input type="hidden" id="currentView" name='currentView' value="License"/>

    <input type="hidden" name="module" value="ITS4YouReports" />
    <input type="hidden" name="view" value="" />
    <input type="hidden" name="license_key_val" id="license_key_val" value="{$LICENSE}" />
    <input type="hidden" id="type" name="type" value="{$TYPE}"/>
     <br />
    <div class="row-fluid">
        <label class="fieldLabel"><strong>{vtranslate('LBL_LICENSE_DESC','ITS4YouReports')}:</strong></label><br>
            {include file="LicenseInformation.tpl"|@vtemplate_path:$MODULE}
            <br />
        <table class="table table-bordered table-condensed themeTableColor">
            <tr>
                <td width="25%"></td>
                <td style="border-left: none;">
                    <div id="divgroup1" class="btn-group pull-left paddingLeft10px" {if ($VERSION_TYPE eq "basic" || $VERSION_TYPE eq "professional") && $SHOW_ACTIVATE_LICENSE eq "0"}style="display:none"{/if}>
                        <button id="activate_license_btn"  class="btn addButton" title="{vtranslate('LBL_ACTIVATE_KEY_TITLE','ITS4YouReports')}" type="button"><strong>{vtranslate('LBL_ACTIVATE_KEY','ITS4YouReports')}</strong></button>
                    </div>
                    <div id="divgroup2" class="pull-left paddingLeft10px" {if $VERSION_TYPE neq "basic" && $VERSION_TYPE neq "professional"}style="display:none"{/if}>
                        <button id="reactivate_license_btn"  class="btn btn-success" title="{vtranslate('LBL_REACTIVATE_DESC','ITS4YouReports')}" type="button" {if $SHOW_ACTIVATE_LICENSE eq "1" }style="display:none"{/if}>{vtranslate('LBL_REACTIVATE','ITS4YouReports')}</button>
                        <button id="deactivate_license_btn" type="button" class="btn btn-danger marginLeftZero">{vtranslate('LBL_DEACTIVATE','ITS4YouReports')}</button>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    {if $MODE eq "edit"}        
        <div class="pull-right">
            <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE2',$MODULE)}</button>
            <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
        </div> 
    {/if}
    </form>        
</div>
    
{literal}
<script language="javascript" type="text/javascript">
//ITS4YouReports_License_Js.registerEvents();
</script>
{/literal}   