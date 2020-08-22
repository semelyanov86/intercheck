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
<div class="row-fluid">       
    <div style="width:100%;">
        <div class="row-fluid" id='maps_form_div'>           
            <table class="table table-bordered table-report">
                <tbody>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_PIN_TITLE',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 " name="maps[pin_title]" id="maps_pin_title" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_PIN_DESCRIPTION',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 "  name="maps[pin_description]" id="maps_pin_description" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_STREET',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 "  name="maps[street]" id="maps_street" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_CITY',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 "  name="maps[city]" id="maps_city" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_STATE',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 "  name="maps[state]" id="maps_state" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_ZIP',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 " name="maps[zip]" id="maps_zip" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_COUNTRY',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 " name="maps[country]" id="maps_country" data-rule-required="false">
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
												</td>
                    </tr>   
                    <tr style="height:25px">
                        <td class="fieldLabel medium" {$custom_style} >
                            <label class="pull-right marginRight10px">{vtranslate('LBL_REPORT_MAPS_ZOOM',$MODULE)}</label>
                        </td>
                        <td {$custom_style} >
												    <select class="chzn-select span6 " name="maps[zoom]" id="maps_zoom" data-rule-required="false">
						                    <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
						                    {for $i = 1 to 23}
						                        <option value="{$i}" {if $i == $MAP_COLUMNS['zoom']}selected{/if} >{$i}</option>
						                    {/for}
						                </select>
												</td>
                    </tr>
                </tbody>
            </table> 
        </div>
    </div>
</div>      
{/strip}