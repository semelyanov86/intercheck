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
        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('PrimarySearchBy',$MODULE)}
            </label>
            <div class="col-lg-4">
                <select id="primary_search" name="primary_search" class="select2 inputElement" style="margin:auto;">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=primary_search_key item=primary_search_arr from=$primary_search_options}
                        <option value="{$primary_search_arr.value}" {if $primary_search_arr.value==$primary_search}selected='selected'{/if}>{$primary_search_arr.text}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('AllowInModules',$MODULE)}
            </label>
            <div class="col-lg-4">
                <input type="hidden" name="allow_in_modules_hidden" id="allow_in_modules_hidden" value="">
                <select id="allow_in_modules" multiple name="allow_in_modules" class="select2 inputElement" style="margin:auto;">
                    {foreach key=moduleName item=moduleLabel from=$allmodules}
                        <option value="{$moduleName}"
                            {if in_array($moduleName, $allowedmodules)}
                                selected
                            {/if}
                        >
                            {$moduleLabel}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
{/strip}