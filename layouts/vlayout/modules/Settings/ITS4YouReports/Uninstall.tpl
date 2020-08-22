{*
/* * *******************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
*}
{strip}
<div class="container-fluid" id="UninstallITS4YouReportsContainer">    
    <form name="profiles_privilegies" action="index.php" method="post" class="form-horizontal">
    <br>
    <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_UNINSTALL','Settings:ITS4YouReports')}</label>
    <br clear="all">
    <hr>
    <input type="hidden" name="module" value="ITS4YouRestrictPicklist" />
    <input type="hidden" name="view" value="" />
    <br />
    <div class="row-fluid">
        <label class="fieldLabel" style="min-width:40em;" ><strong>{vtranslate('LBL_UNINSTALL_DESC','Settings:ITS4YouReports')}:</strong></label><br>
        <table class="table table-bordered table-condensed themeTableColor">
            <thead>
                    <tr class="blockHeader">
                            <th class="mediumWidthType">
                                    <span class="alignMiddle">{vtranslate('LBL_UNINSTALL', 'Settings:ITS4YouReports')}</span>
                            </th>
                    </tr>
            </thead>
            <tbody>
                    <tr>
                        <td class="textAlignCenter">
                            <button id="uninstall_ITS4YouReports_btn" type="button" class="btn btn-danger marginLeftZero">{vtranslate('LBL_UNINSTALL','Settings:ITS4YouReports')}</button>
                        </td>
                    </tr>
             </tbody>
        </table>
    </div>
    {if $MODE eq "edit"}        
        <div class="pull-right">
            <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
            <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">Cancel</a>
        </div> 
    {/if}
    </form>    
</div>
{/strip}