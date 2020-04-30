{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
	<div class="listViewActions pull-right">
		{if $CURRENT_USER_MODEL->get('is_admin') eq 'on'}
		<div class="settingsIcon">
			<span class="pull-right btn-group">
				<button class="btn " id="createFilter"><i class="icon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE)}" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></i></button>
			</span>
		</div>
		{/if}
	</div>

	<div class="clearfix"></div>
	<input type="hidden" id="recordsCount" value=""/>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
{/strip}