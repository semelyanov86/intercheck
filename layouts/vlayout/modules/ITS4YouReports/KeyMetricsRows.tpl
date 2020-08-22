{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
<script type="text/javascript">
jQuery().ready(function() {
    var ITS4YouReports_KM_Js = new ITS4YouReports_KeyMetricsList_Js();
    ITS4YouReports_KM_Js.registerPickListValuesSortableEvent();
    ITS4YouReports_KM_Js.registerEditKeyMetricsRowStep1();
});
</script>

{strip}
<div class="container-fluid editViewContainer">
    <div class="contentHeader row-fluid">
        <h3 class="span8 textOverflowEllipsis">
        {$KM_NAME}
        {*{vtranslate('LBL_CREATE_NEW_KEY_METRICS', $MODULE)}*}
        </h3>
    </div>
</div>

<input type="hidden" name="km_id" id="km_id" value="{$KM_ID}">

<div class="tab-content themeTableColor overflowVisible">
	<div class="tab-pane active" id="allValuesLayout">	
		<div class="row-fluid">
			<div class="span5 marginLeftZero textOverflowEllipsis">
        {if $KM_SMCREATOR==$currentuser_id}
            {assign var=sortableTable value="pickListValuesTable"}
        {else}
            {assign var=sortableTable value="pickListValuesTableDisabled"}
        {/if}
				<table id="{$sortableTable}" class="table table-bordered" style="table-layout: fixed">
					<thead>
						<tr class="listViewHeaders">
              <th>{vtranslate('LBL_VIEW_NAME',$MODULE)}</th>
              <th>{vtranslate('LBL_WIDGET_SMCREATORID',$MODULE)}</th>
            </tr>
					</thead>
					<tbody class="ui-sortable">
					<input type="hidden" id="dragImagePath" value="{vimage_path('drag.png')}" />
					{if !empty($KEY_METRICS_ENTRIES)}
    					{foreach item=KEY_METRICS_ARRAY from=$KEY_METRICS_ENTRIES}
    						{assign var=KEY_METRICS_LABEL value=$KEY_METRICS_ARRAY.label}
                {assign var=KEY_METRICS_ID value=$KEY_METRICS_ARRAY.id}
                <tr class="pickListValue" data-key-id="{$KEY_METRICS_ID}" data-key="{Vtiger_Util_Helper::toSafeHTML($KEY_METRICS_LABEL)}">
    							<td class="textOverflowEllipsis">
                    <div style="float:left;">
                      <img class="alignMiddle" src="{vimage_path('drag.png')}"/>&nbsp;&nbsp;{$KEY_METRICS_LABEL}
                    </div>
                  </td>
                  <td class="textOverflowEllipsis">
                    <div style="float:left;">{getUserFullName($KEY_METRICS_ARRAY.smcreatorid)}</div>
                    {if $KEY_METRICS_ARRAY.smcreatorid==$currentuser_id}
                        <div class="actions pull-right">
                        <i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}" ></i>
                        &nbsp;
                        <i class="icon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $MODULE)}" ></i>
                        </div>
                    {/if}
                  </td>
    						</tr>
    					{/foreach}
          {else}
    					<tr class="pickListValue" data-key-id="" data-key="">
    					   <td class="textOverflowEllipsis"><img class="alignMiddle" src="{vimage_path('drag.png')}"/>&nbsp;&nbsp;{vtranslate("LBL_NO_KEY_METRICS",$MODULE)}</td>
    					</tr>
          {/if}
					</tbody>
				</table>
			</div>
			<div class="span2 row-fluid" style="padding-top:3px;">
        {if $KM_SMCREATOR==$currentuser_id}
					<button class="btn span10 marginLeftZero" id="addNewKeyMetricsRow" onclick="window.location.href='index.php?module=ITS4YouReports&view=EditKeyMetricsRow&km_id={$KM_ID}&id=&reportid='">{vtranslate('LBL_ADD_NEW_KEY_METRICS_ROW',$MODULE)}</button>
        {/if}
{*
					<br /><br />
          <button class="btn span10 marginLeftZero" id="keyMetricsList" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsList'">{vtranslate('LBL_KEY_METRICS_WIDGETS',$MODULE)}</button>
*}
			</div>
{*
			<div class="span4">
				<br><br><br>
				<div><i class="icon-info-sign"></i>&nbsp;<span>{vtranslate('LBL_DRAG_ITEMS_TO_RESPOSITION',$QUALIFIED_MODULE)}</span></div>
				<br><div>&nbsp;&nbsp;{vtranslate('LBL_SELECT_AN_ITEM_TO_RENAME_OR_DELETE',$QUALIFIED_MODULE)}</div> 
				<br><div>&nbsp;&nbsp;{vtranslate('LBL_TO_DELETE_MULTIPLE_HOLD_CONTROL_KEY',$QUALIFIED_MODULE)}</div>
			</div>
*}
		</div>		
	</div>
</div>
