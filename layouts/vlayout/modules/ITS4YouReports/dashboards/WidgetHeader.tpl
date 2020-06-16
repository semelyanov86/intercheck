{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<table width="100%" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td class="span5">
				<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: auto;">
                                    <b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b>&nbsp;&nbsp;
        </div>
			</td>
			<td class="refresh span2" align="right">
				<span style="position:relative;">&nbsp;</span>
			</td>
			<td class="widgeticons span5" align="right">
				<div class="box pull-right">
					{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
				</div>
			</td>
		</tr>
    <tr>
      <td colspan="3">
        {if $primary_values|count > 0}
        <div class='row-fluid span6'>
            <div class="span3 textAlignRight">
                {$primary_label}:&nbsp;
            </div>    
            <div class="span7">
                <select class="select2 row-fluid" multiple id="SelectPrimarySearchWidget{$recordid}">
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
      </td>
    </tr>
	</tbody>
</table>
