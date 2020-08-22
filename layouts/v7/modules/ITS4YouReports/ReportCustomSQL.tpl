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
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_SQL',$MODULE)}<span class="redColor">*</span></label>
            <div class="col-lg-4">
                <textarea id="reportcustomsql" name="reportcustomsql" class="inputElement textAreaElement col-lg-12" rows="8" >{$REPORT_CUSTOM_SQL}</textarea>
            </div>
        </div>
    </div>
</div>      
{/strip}