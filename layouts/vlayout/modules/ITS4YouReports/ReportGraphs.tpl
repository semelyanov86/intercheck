{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <table class="table table-bordered table-report">
                <thead>
                <tr class="blockHeader">
                    <th colspan="2">
                        {vtranslate('LBL_GRAPHS',$MODULE)}
                        <input type="hidden" name="none_chart" id="none_chart" value="{'LBL_SELECT_CHARTTYPE'|@getTranslatedString:'ITS4YouReports'}">
                        {* tooltip start *}
                        {assign var="TOOLTIP_TEXT" value=vtranslate('LBL_STEP12_INFO',$MODULE)}
                        {include file='modules/ITS4YouReports/TooltipElement.tpl'}
                        {* tooltip end *}
                    </th>
                </tr>
                </thead>
                <tbody>
                {*
                <tr>
                    <td class="medium" style="width:15%;"><label class="pull-right marginRight10px">{vtranslate('Chart type',$MODULE)}</label></td>
                    <td>
                        <select id="chartType" name="chartType" onchange="defineChartType(this)">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {if !empty($DATA_SERIES)}
                                {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                                    <option value="{$chart_type_key}" {$charttype_arr.selected}>{$charttype_arr.value}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </td>
                </tr>
                <tr style="height:25px">
                    <td class="medium"><label class="pull-right marginRight10px">{vtranslate('Data Series',$MODULE)}</label></td>
                    <td>
                        <select id="data_series" name="data_series">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=column_str item=column_arr from=$DATA_SERIES}
                                <option value="{$column_str}" {$column_arr.selected}>{$column_arr.value}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                *}
                {* NEW GRAPHS *}
                <tr style="height:25px">
                    <td class="medium"><label class="pull-right marginRight10px">{vtranslate('LBL_CHART_Title',$MODULE)}</label></td>
                    <td>
                        <input class="span5" id="charttitle" name="charttitle" value="{$charttitle}" type="text" placeholder="{'LBL_CHART_Title'|@getTranslatedString:'ITS4YouReports'}" onblur="setChartTitle(this)">
                    </td>
                </tr>
                <tr style="height:25px">
                    <td class="medium"><label class="pull-right marginRight10px">{vtranslate('LBL_CHART_DataSeries',$MODULE)}</label></td>
                    <td>
                        <select id="x_group" name="x_group" onchange="ChartDataSeries(this);">
                            {foreach key=x_column_str item=x_column_arr from=$X_GROUP}
                                <option value="{$x_column_str}" {$x_column_arr.selected}>{$x_column_arr.value}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr style="height:25px">
                    {assign var="chtype1" value=$CHARTS_ARRAY.1.charttype}
                    <td class="medium">
                            <span class="pull-right">
                                <i id="iconchtype1" name="iconchtype" class="{$CHART_TYPE.$chtype1.icon}"></i>
                            </span>
                        <span class="pull-right">
                                <label class="pull-right marginRight10px">{vtranslate('Graph1',$MODULE)}</label>
                            </span>
                    </td>
                    <td>
                        <select id="chartType1" name="chartType1" data-i="1">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                                <option value="{$chart_type_key}" {if $chart_type_key==$chtype1}selected='selected'{/if}
                                        data-icon="{$charttype_arr['icon']}">
                                    {$charttype_arr['value']}
                                </option>
                            {/foreach}
                        </select>
                        <select id="data_series1" name="data_series1">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=column_i item=column_arr from=$selected_summaries}
                                <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.1.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr style="height:25px">
                    {assign var="chtype2" value=$CHARTS_ARRAY.2.charttype}
                    <td class="medium">
                            <span class="pull-right">
                                <i id="iconchtype2" name="iconchtype" class="{$CHART_TYPE.$chtype2.icon}"></i>
                            </span>
                        <span class="pull-right">
                                <label class="pull-right marginRight10px">{vtranslate('Graph2',$MODULE)}</label>
                            </span>
                    </td>
                    <td>
                        <select id="chartType2" name="chartType2" data-i="2">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                                <option value="{$chart_type_key}" {if $chart_type_key==$chtype2}selected='selected'{/if}
                                        data-icon="{$charttype_arr['icon']}" >
                                    {$charttype_arr.value}
                                </option>
                            {/foreach}
                        </select>
                        <select id="data_series2" name="data_series2">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=column_i item=column_arr from=$selected_summaries}
                                <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.2.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr style="height:25px">
                    {assign var="chtype3" value=$CHARTS_ARRAY.3.charttype}
                    <td class="medium">
                            <span class="pull-right">
                                <i id="iconchtype3" name="iconchtype" class="{$CHART_TYPE.$chtype3.icon}"></i>
                            </span>
                        <span class="pull-right">
                                <label class="pull-right marginRight10px">{vtranslate('Graph3',$MODULE)}</label>
                            </span>
                    </td>
                    <td>
                        <select id="chartType3" name="chartType3" data-i="3">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                                <option value="{$chart_type_key}" {if $chart_type_key==$chtype3}selected='selected'{/if}
                                        data-icon="{$charttype_arr['icon']}" >
                                    {$charttype_arr.value}
                                </option>
                            {/foreach}
                        </select>
                        <select id="data_series3" name="data_series3">
                            <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                            {foreach key=column_i item=column_arr from=$selected_summaries}
                                <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.3.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>