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
<style>
	.filterActions {
		padding: 20px 0px;
	}
	/*.columnsSelectDiv {
		width: 550px;
	}*/
</style>
{strip}
    {assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
    <div class="container-fluid">
        <form class="form-inline" id="CustomView" name="CustomView" method="post" action="index.php">
           <div class="modal-content">
			   <div class="overlayHeader">
            <input type="hidden" name="record" id="record" value="{$RECORD_ID}" />
            <input type="hidden" name="module" value="VDUsers" />
            <input type="hidden" name="action" value="Save" />
            <input type="hidden" name="source_module" value="Users" />
            <input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/>
            <input type="hidden" id="advfilterlist" name="advfilterlist" value=""/>
            <input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}"/>
			<input type="hidden" id="viewname" name="viewname" value="VDUsers" />
            <input type="hidden" id="setdefault" name="setdefault" value="0" />
            <input type="hidden" id="status" name="status" value="3" />
			
			<div class="modal-header">
				<div class="clearfix">
					<button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class="fa fa-close"></span></button>
				<h3>{vtranslate('LBL_CREATE_VIEW',$MODULE)}</h3>
				</div>
			</div>
			   </div>
		   <div class="modal-body">
			   <div class="customview-content row mCustomScrollbar _mCS_7 mCS-autoHide">
            <input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}">
			
            <div class="filterBlocksAlignment">
                <h4 class="filterHeaders">{vtranslate('LBL_CHOOSE_COLUMNS',$MODULE)} ({vtranslate('LBL_MAX_NUMBER_FILTER_COLUMNS')}) :</h4>
                <br>
				<div class="columnsSelectDiv">
                    {assign var=MANDATORY_FIELDS value=array()}
                    <select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="select2 columnsSelect" id="viewColumnsSelect" style="width:100%;">
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
						<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
								{if $FIELD_MODEL->isMandatory()}
									{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
								{/if}
								<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
										{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
											selected
										{/if}
										>{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
								{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
								</option>
                        {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <input type="hidden" name="columnslist" value='{ZEND_JSON::encode($SELECTED_FIELDS)}' />
                <input id="mandatoryFieldsList" type="hidden" value='{ZEND_JSON::encode($MANDATORY_FIELDS)}' />
            </div>
        </div>
		<br>
		<div class="filterBlocksAlignment">
			<h4 class="filterHeaders">Add roles constraints :</h4>
			<br>
			<div class="columnsSelectDiv">
				{assign var="PICKLIST" value=json_encode($ROLE_PICKLIST)}
				<select name="roles[]" data-placeholder="Choose roles" multiple class="select2 columnsSelect" id="VDRoleSelect" data-role-picklist='{$PICKLIST}' style="width:100%;">
				{foreach item=ROLE_MODEL key=ROLEID from=$ROLES}
					<option value="{$ROLEID}" data-field-name="{$ROLE_MODEL->getName()}"
						{if in_array($ROLEID, array_keys($ROLE_FIELDS))}
						selected
						{/if}
					>{$ROLE_MODEL->getName()}</option>
				{/foreach}
				</select>
				<input type="hidden" name="columnslist" value='{json_encode(array())}' />
				<input id="mandatoryFieldsList" type="hidden" value='[]' />
			</div>
			<br>
			<div class="columnsSelectDiv">
				<table class="table table-bordered" width="100%" id="VDRoleConstraints">
					<colgroup>
						<col style="width:35%;">
						<col style="width:60%;">
						<col style="width:5%;">
					</colgroup>
					<tr name="fieldHeaders" class="listViewEntries">
						<td class="textAlignCenter"><b>Role</b></td>
						<td class="textAlignCenter"><b>Constraints</b></td>
						<td><b>Delete</b></td>
					</tr>
					{foreach item=ROLE_FIELD_MODEL key=ROLEID from=$ROLE_FIELDS}
					<tr class="listViewEntries" data-name="{$ROLEID}" data-type="multipicklist" data-mandatory-field="false">
						<td class="textAlignCenter fieldLabel">{$ROLE_FIELD_MODEL->get('label')}</td>
						<td class="textAlignCenter fieldValue" data-name="fieldUI_{$ROLEID}">
							{include file=vtemplate_path($ROLE_FIELD_MODEL->getUITypeModel()->getTemplateName(), 'VDUsers') FIELD_MODEL=$ROLE_FIELD_MODEL}
						</td>
						<td><div id="removeField" class="actions"><a class="removeTargetModuleField"><i class="fa fa-trash icon-remove-sign"></i></a></div></td>
					</tr>
					{/foreach}
				</table>
			</div>
			<div class="filterActions">
				<a class="cancelLink pull-right" type="reset" onClick="window.location.reload()">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				<button class="btn btn-success pull-right" onclick='checkValidation(e)' id="customViewSubmit" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
			</div>
		</div>
		   </div>
		   </div>
		   </div>
    </form>
</div>
{/strip}
{literal}
	<script>
		function deleteAction()
		{
			jQuery('#VDRoleConstraints').on('click','#removeField',function(e) {
				var element = jQuery(e.currentTarget);
				var containerRow = element.closest('tr');
				var removedFieldLabel = containerRow.find('td.fieldLabel').text();
				var selectElement = jQuery('#VDRoleSelect');
				var select2Element = app.getSelect2ElementFromSelect(selectElement);
				select2Element.find('li.select2-search-choice').find('div:contains('+removedFieldLabel+')').closest('li').remove();
				selectElement.find('option:contains('+removedFieldLabel+')').removeAttr('selected');
				containerRow.remove();
			});
		}
		function checkValidation(e)
		{
			if (!document.getElementById('viewColumnsSelect').value) {
				alert('Please choose at least one column');
				e.preventDefault();
			}
		}
		deleteAction();
	</script>
{/literal}
