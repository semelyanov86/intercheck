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
    <select name="generate_for" id="generate_for" class="span10 chzn-select row-fluid" multiple style="margin:auto;">
        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
        {foreach key=moduleGroupKey item=moduleGroupOpts from=$generateForOptionsArray}
            <optgroup label="{vtranslate($moduleGroupKey,$moduleGroupKey)}">
                {foreach item=optionArray from=$moduleGroupOpts}
                    <option value="{$optionArray.value}" {if in_array($optionArray.value, $selectedForOptions)}selected{/if} >{$optionArray.text}</option>
                {/foreach}
            </optgroup>
        {/foreach}
    </select>
{/strip}