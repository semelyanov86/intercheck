{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
{if $display_widget_header == 'true'}
    <script type="text/javascript" src="layouts/v7/modules/ITS4YouReports/resources/Getreports.js"></script>

    <div class="dashboardWidgetHeader" id="dashboardWidgetHeader{$recordid}" >
    {foreach key=index item=cssModel from=$STYLES}
        <link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
    {/foreach}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

        <div class="title clearfix">
            <span class="dashboardTitle" title={vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 25em;" >
                <b>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)|@escape:'html'}</b>
            </span>
        </div>

    {if $primary_values|count > 0}
        <div class="dashboardWidgetHeader clearfix">
            <div class="userList">
                <select class="select2 widgetFilter "  name="primarySearchBy" id="SelectPrimarySearchWidget{$recordid}">
                    <option value="all"  selected>{vtranslate('All', $MODULE_NAME)}</option>
                    {foreach key=primary_value_key item="primary_value" from=$primary_values}
                        {assign var=optGroupDone value="0"}
                        {if is_array($primary_value)}
                            {foreach item="primary_value_opt" from=$primary_value}
                                {if $optGroupDone!="1"}
                                    {assign var=optGroupDone value="1"}
                                    <optgroup label="{$primary_value_key}">
                                {/if}
                                <option value="{$primary_value_opt}">{vtranslate($primary_value_opt,$LModule)}</option>
                            {/foreach}
                        {else}
                            <option value="{$primary_value}">{vtranslate($primary_value,$LModule)}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    {/if}
    </div>
{/if}
