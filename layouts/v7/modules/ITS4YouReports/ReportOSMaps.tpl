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
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_PIN_TITLE',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[pin_title]" id="maps[pin_title]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['pin_title']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_PIN_DESCRIPTION',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[pin_description]" id="maps[pin_description]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['pin_description']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_STREET',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[street]" id="maps[street]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                    {if $optGroup!=""}
                        <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                            {foreach item=columnArr from=$allColumnsArray}
                                {assign var=columnVal value=$columnArr.value}
                                {assign var=columnText value=$columnArr.text}
                                <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['street']}selected{/if} >{$columnText}</option>
                            {/foreach}
                        </optgroup>
                    {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_CITY',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[city]" id="maps[city]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['city']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_STATE',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[state]" id="maps[state]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['state']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_ZIP',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[zip]" id="maps[zip]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['zip']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_COUNTRY',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[country]" id="maps[country]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {foreach key=optGroup item=allColumnsArray from=$MODULE_OPTIONS}
                        {if $optGroup!=""}
                            <optgroup label='{vtranslate($optGroup, $reportModule)}'>
                                {foreach item=columnArr from=$allColumnsArray}
                                    {assign var=columnVal value=$columnArr.value}
                                    {assign var=columnText value=$columnArr.text}
                                    <option value="{$columnVal}" {if $columnVal === $MAP_COLUMNS['country']}selected{/if} >{$columnText}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group">
            <label class="col-lg-2 control-label textAlignLeft">{vtranslate('LBL_REPORT_MAPS_ZOOM',$MODULE)}</label>
            <div class="col-lg-4" id="scheduledTypeSpan">
                <select class="select2 col-lg-12 inputElement" name="maps[zoom]" id="maps[zoom]" data-rule-required="false">
                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                    {for $i = 1 to 23}
                        <option value="{$i}" {if $i == $MAP_COLUMNS['zoom']}selected{/if} >{$i}</option>
                    {/for}
                </select>
            </div>
        </div>
    </div>

</div>
{/strip}