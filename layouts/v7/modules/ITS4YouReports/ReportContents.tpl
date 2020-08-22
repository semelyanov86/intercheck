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
    <input type="hidden" id="updatedCount" value="{$NEW_COUNT}" />

    <input type="hidden" name="date_filters" data-value='{ZEND_JSON::encode($DATE_FILTERS)}' />
    <input type="hidden" name="report_filename" id="report_filename" value="" />

    <input type="hidden" name="export_pdf_format" id="export_pdf_format" value="" />
    <input type="hidden" name="pdf_file_name" id="pdf_file_name" value="" />
    <input type="hidden" name="ch_image_name" id="ch_image_name" value="" />

    <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
    <input type="hidden" name="advft_criteria" id="advft_criteria" value='' />
    <input type="hidden" name="advft_criteria_groups" id="advft_criteria_groups" value='' />
    <input type="hidden" name="groupft_criteria" id="groupft_criteria" value='' />
    <input type="hidden" name="quick_filter_criteria" id="quick_filter_criteria" value="" />
    <input type="hidden" name='reload' id='reload' value='true'/>
    <input type="hidden" name="currentMode" id="currentMode" value='generate' />

    <input type="hidden" name="reporttype" id="reporttype" value='{$REPORTTYPE}' />

    <div id="reports4you_html" style="width: 100%;">
    {if !empty($CALCULATION_FIELDS)}
        <table class=" table-bordered table-condensed marginBottom10px" width="100%">
            <thead>
                <tr class="blockHeader">
                    <th>{vtranslate('LBL_FIELD_NAMES',$MODULE)}</th>
                    <th>{vtranslate('LBL_SUM',$MODULE)}</th>
                    <th>{vtranslate('LBL_AVG',$MODULE)}</th>
                    <th>{vtranslate('LBL_MIN',$MODULE)}</th>
                    <th>{vtranslate('LBL_MAX',$MODULE)}</th>
                </tr>
            </thead>
            {assign var=ESCAPE_CHAR value=array('_SUM','_AVG','_MIN','_MAX')}
            {foreach from=$CALCULATION_FIELDS item=CALCULATION_FIELD key=index}
                <tr>
                    {assign var=CALCULATION_FIELD_KEYS value=array_keys($CALCULATION_FIELD)}
                    {assign var=CALCULATION_FIELD_KEYS value=$CALCULATION_FIELD_KEYS|replace:$ESCAPE_CHAR:''}
                    {assign var=FIELD_IMPLODE value=explode('_',$CALCULATION_FIELD_KEYS['0'])}
                    {assign var=MODULE_NAME value=$FIELD_IMPLODE['0']}
                    {assign var=FIELD_LABEL value=" "|implode:$FIELD_IMPLODE}
                    {assign var=FIELD_LABEL value=$FIELD_LABEL|replace:$MODULE_NAME:''}
                    <td>{vtranslate($MODULE_NAME,$MODULE)} {vtranslate($FIELD_LABEL, $MODULE)}</td>
                    {foreach from=$CALCULATION_FIELD item=CALCULATION_VALUE}
                            <td width="15%">{$CALCULATION_VALUE}</td>
                    {/foreach}
                </tr>
            {/foreach}
        </table>
    {/if}

    {if $DATA neq ''}
        {assign var=HEADERS value=$DATA[0]}
            {foreach from=$DATA item=VALUES}
                    {foreach from=$VALUES item=VALUE key=NAME}
                        <span style="background-color:white;">{$VALUE}</span>
                    {/foreach}
            {/foreach}
        {if $LIMIT_EXCEEDED}
            <center>{vtranslate('LBL_LIMIT_EXCEEDED',$MODULE)} <span class="pull-right"><a href="#top" >{vtranslate('LBL_TOP',$MODULE)}</a></span></center>
        {/if}
    {else}
        {vtranslate('LBL_NO_DATA_AVAILABLE',$MODULE)}
    {/if}
</div>
</div>
</form>
<div class="no-print">
{/strip}
