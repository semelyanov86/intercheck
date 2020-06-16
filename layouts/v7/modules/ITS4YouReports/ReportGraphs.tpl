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
    <input type="hidden" name="none_chart" id="none_chart" value="{'LBL_SELECT_CHARTTYPE'|@getTranslatedString:'ITS4YouReports'}">
    <div style="border:1px solid #ccc;padding:4%;">
        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('LBL_CHART_Title',$MODULE)}
            </label>
            <div class="col-lg-4">
                <input class="inputElement" id="charttitle" name="charttitle" value="{$charttitle}" type="text" placeholder="{'LBL_CHART_Title'|@getTranslatedString:'ITS4YouReports'}" onblur="setChartTitle(this)">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('LBL_CHART_CollapseDataBlock',$MODULE)}
            </label>
            <div class="col-lg-4">
                <input class="span5" id="collapse_data_block" name="collapse_data_block" value="{$collapse_data_block}" type="hidden" >
                <input class="span5" id="collapse_data_checkbox" name="collapse_data_checkbox" value="" {if 1 eq $collapse_data_block}checked{/if} type="checkbox" >
            </div>
        </div>

        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('LBL_CHART_Position',$MODULE)}
            </label>
            <div class="col-lg-4">
                <select class="select2 inputElement" id="chart_position" name="chart_position">
                    <option value="bttom" {if 'bottom' eq $chart_position}selected='selected'{/if}>{vtranslate('LBL_BOTTOM',$MODULE)}</option>
                    <option value="top" {if 'top' eq $chart_position}selected='selected'{/if}>{vtranslate('LBL_TOP',$MODULE)}</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label textAlignLeft col-lg-2">
                {vtranslate('LBL_CHART_DataSeries',$MODULE)}
            </label>
            <div class="col-lg-4">
                <select class="select2 inputElement" id="x_group" name="x_group" onchange="ChartDataSeries(this);">
                    {foreach key=x_column_str item=x_column_arr from=$X_GROUP}
                        <option value="{$x_column_str}" {$x_column_arr.selected}>{$x_column_arr.value}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            {assign var="chtype1" value=$CHARTS_ARRAY.1.charttype }
            <label class="control-label textAlignLeft col-lg-1">
                {vtranslate('Graph1',$MODULE)}
            </label>
            <div class="col-lg-1" style="height:20px;margin-top:10px;">
								<i id="iconchtype1" name="iconchtype" class="{$CHART_TYPE.$chtype1.icon}" style="float:right;"></i>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="chartType1" name="chartType1" data-i="1">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                        <option value="{$chart_type_key}" {if $chart_type_key==$chtype1}selected='selected'{/if}
                            data-icon="{$charttype_arr['icon']}">
                            {$charttype_arr['value']}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="data_series1" name="data_series1">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=column_i item=column_arr from=$selected_summaries}
                        <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.1.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            {assign var="chtype2" value=$CHARTS_ARRAY.2.charttype }
            <label class="control-label textAlignLeft col-lg-1">
                {vtranslate('Graph2',$MODULE)}
            </label>
            <div class="col-lg-1" style="height:20px;margin-top:10px;">
								<i id="iconchtype2" name="iconchtype" class="{$CHART_TYPE.$chtype2.icon}" style="float:right;"></i>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="chartType2" name="chartType2" data-i="2">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                        <option value="{$chart_type_key}" {if $chart_type_key==$chtype2}selected='selected'{/if}
                            data-icon="{$charttype_arr['icon']}" >
                            {$charttype_arr.value}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="data_series2" name="data_series2">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=column_i item=column_arr from=$selected_summaries}
                        <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.2.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            {assign var="chtype3" value=$CHARTS_ARRAY.3.charttype }
            <label class="control-label textAlignLeft col-lg-1">
                {vtranslate('Graph3',$MODULE)}
            </label>
            <div class="col-lg-1" style="height:20px;margin-top:10px;">
								<i id="iconchtype3" name="iconchtype" class="{$CHART_TYPE.$chtype3.icon}" style="float:right;"></i>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="chartType3" name="chartType3" data-i="3">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=chart_type_key item=charttype_arr from=$CHART_TYPE}
                        <option value="{$chart_type_key}" {if $chart_type_key==$chtype3}selected='selected'{/if}
                            data-icon="{$charttype_arr['icon']}" >
                            {$charttype_arr.value}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-lg-2">
                <select class="select2 inputElement" id="data_series3" name="data_series3">
                    <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                    {foreach key=column_i item=column_arr from=$selected_summaries}
                        <option value="{$column_arr.value}" {if $column_arr.value==$CHARTS_ARRAY.3.dataseries}selected='selected'{/if}>{$column_arr.label}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

{/strip}