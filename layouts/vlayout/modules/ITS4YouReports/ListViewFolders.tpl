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
<span class="customFilterMainSpan btn-group">
	<select id="customFilter"  style="width:350px;">
		<optgroup id="foldersBlock" label="{vtranslate('LBL_FOLDERS', $MODULE)}">
			<option value="All">{vtranslate('LBL_ALL_REPORTS', $MODULE)}</option>
			{foreach item=FOLDER from=$FOLDERS}
				<option  data-editurl="{$FOLDER->getEditUrl()}" id="filterOptionId_{$FOLDER->getId()}" class="filterOptionId_{$FOLDER->getId()}" data-deletable="{$FOLDER->isDeletable()}" data-editable="{$FOLDER->isEditable()}" data-deleteurl="{$FOLDER->getDeleteUrl()}" value="{$FOLDER->getId()}" data-id="{$FOLDER->getId()}" {if $VIEWNAME eq $FOLDER->getId()}selected=""{/if}>{vtranslate($FOLDER->getName(), $MODULE)}</option>
			{/foreach}
		</optgroup>
	</select>
</span>
<span class="hide filterActionImages pull-right">
	<i title="{vtranslate('LBL_DELETE', $MODULE)}" data-value="delete" class="icon-trash alignMiddle deleteFilter filterActionImage pull-right"></i>
	<i title="{vtranslate('LBL_EDIT', $MODULE)}" data-value="edit" class="icon-pencil alignMiddle editFilter filterActionImage pull-right"></i>
</span>
{/strip}