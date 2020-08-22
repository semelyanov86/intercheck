{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{strip}
<div style="border:1px solid #ccc;padding:4%;">
    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_NAME',$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <input type="text" class="inputElement" data-rule-required="true" name="reportname" id="reportname" value="{$REPORTNAME}"/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_PRIMARY_MODULE',$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <select class="select2 col-lg-12 inputElement" name="primarymodule" id="primarymodule" data-rule-required="true">
                    {foreach item=primarymodulearr from=$PRIMARYMODULES}
                        {if $RECORD_MODE == "ChangeSteps" || $MODE == "edit"}
                            {if $primarymodulearr.selected!=''}
                                <option value="{$primarymodulearr.id}" selected >{$primarymodulearr.module}</option>
                            {/if}
                        {else}
                            <option value="{$primarymodulearr.id}" {if $primarymodulearr.selected!=''}selected{/if} >{$primarymodulearr.module}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REP_FOLDER',$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <select class="select2 col-lg-12 inputElement" name="reportfolder" id="reportfolder" data-rule-required="true">
                    {foreach item=folder from=$REP_FOLDERS}
                        <option value="{$folder.folderid}" {if $folder.selected!=''}selected{/if} >{$folder.foldername}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_DESCRIPTION',$MODULE)}</label>
            <div class="col-lg-4 fieldBlockContainer">
                <textarea rows="3" class="inputElement textAreaElement col-lg-12" id="reportdesc" name="reportdesc" >{$REPORTDESC}</textarea>
            </div>
        </div>
    </div>

</div>
{/strip}