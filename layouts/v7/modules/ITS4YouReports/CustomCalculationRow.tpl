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
    {* //ITS4YOU-CR SlOlCOLUMNS_OPTIONS 16. 5. 2016 8:24:48 *}
    <div id="cc_row_{$cc_i}" class="cc_row_class col-lg-12" style="padding:5px 0px 5px 0px;">
        <div class="col-lg-3">
        <input type="hidden" id="cc_id_val" value="{$cc_i}" >
        <input type="text" name="cc_name_{$cc_i}" id="cc_name_{$cc_i}" class="inputElement col-lg-2 cc_row_name" value="{$cc_calculation_arr.cc_name}" placeholder="{vtranslate('LBL_CUSTOM_CALCULATION_NAME', $MODULE)}" >
        </div>
        <div class="col-lg-2">
        <select name="cc_options_{$cc_i}" id="cc_options_{$cc_i}" class=" {$cc_special} inputElement cc-new-select"  onChange="addCustomCalculationValue(this,'cc_calculation_{$cc_i}')">
            <option value="" selected >{vtranslate('LBL_NONE',$MODULE)}</option>
            {foreach key=optName item=all_columns_array from=$COLUMNS_OPTIONS}
                <optgroup label='{$optName}'>
                    {foreach item=column_array from=$all_columns_array}
                        <option value="{$column_array.value}" >{$column_array.text}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
        </div>
        <div class="col-lg-3">
        <textarea name="cc_calculation_{$cc_i}" id="cc_calculation_{$cc_i}" class="form-control col-sm-6 resize-vertical cc-new-textarea" style="margin:auto;height:28px;" value="" placeholder="{vtranslate('LBL_CUSTOM_CALCULATION_EXPRESSION', $MODULE)}" >
        {$cc_calculation_arr.cc_calculation}
        </textarea>
        </div>
        <div class="col-lg-2">

            <input type="hidden" id="cc_totals_hidden_{$cc_i}" name="cc_totals_hidden_{$cc_i}" value="{$cc_calculation_arr.cc_totals_hidden}" class="cc-totals-hidden">
            <select name="cc_totals_{$cc_i}" id="cc_totals_{$cc_i}" multiple class=" {$cc_special} inputElement cc-new-select cc-totals"  >
                <option value="sum" {if in_array('sum', $cc_calculation_arr.cc_totals)}selected{/if} >{vtranslate('SUM',$MODULE)}</option>
                <option value="avg" {if in_array('avg', $cc_calculation_arr.cc_totals)}selected{/if} >{vtranslate('AVG',$MODULE)}</option>
                <option value="min" {if in_array('min', $cc_calculation_arr.cc_totals)}selected{/if} >{vtranslate('MIN',$MODULE)}</option>
                <option value="max" {if in_array('max', $cc_calculation_arr.cc_totals)}selected{/if} >{vtranslate('MAX',$MODULE)}</option>
            </select>
        </div>
        <div class="col-lg-2">
        <span>
            <a onclick="deleteCustomCalculationRow({$cc_i});" href="javascript:;">
                <img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0">
            </a>
        </span>
        &nbsp;
        <span>
            <a data-original-title="" href="#" id="cc_tooltip_{$cc_i}" class="editHelpInfo tooltipstered" onmouseover="displayCustomCalculationArea({$cc_i})" onmouseout="hideCustomCalculationArea({$cc_i})" data-placement="top" data-text="test" data-template="<div class='tooltip' role='tooltip'><div class='tooltip-arrow'></div><div class='tooltip-inner' style='text-align: left'></div></div>">
                <i class="glyphicon glyphicon-info-sign alignMiddle"></i>
            </a>
            <span id="cc_tooltip_base{$cc_i}" class="" style="z-index:999;position:relative;top:-23px;left:-100px;display:none;" >
                <span class='tooltip-arrow'></span><span class='tooltip-inner' style='text-align: left' id="cc_tooltip_content{$cc_i}"></span>
            </span>
        </span>
        </div>
    </div>
{/strip}