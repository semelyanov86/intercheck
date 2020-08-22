{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{if $SETTING_EXIST}
	<a name="dfilter">
		<i class='fa fa-cog' border='0' align="absmiddle" title="{vtranslate('LBL_FILTER')}" alt="{vtranslate('LBL_FILTER')}"/>
	</a>
{/if}
{if !empty($CHART_TYPE)}
	{assign var=CHART_DATA value=ZEND_JSON::decode($DATA)}
	{assign var=CHART_VALUES value=$CHART_DATA['values']}
{/if}
{if !$WIDGET->isDefault()}
	<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&tabid={$WIDGET->get('tabid')}&linkid={$WIDGET->get('linkid')}&content=data">
		<i class="fa fa-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
	</a>
	{if 'ShowWidget' eq $smarty.request.view}
		<a name="rclose" class="widget" href="index.php?module=ITS4YouReports&view=Detail&record={$recordid}">
			<i class="fa fa-bars" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_DETAILS')}" alt="{vtranslate('LBL_DETAILS')}"></i>
		</a>
	{/if}
	<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
		<i class="fa fa-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
	</a>
{/if}