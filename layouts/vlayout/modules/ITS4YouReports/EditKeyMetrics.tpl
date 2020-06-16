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
	<div id="addFolderContainer" class="modelContainer">
		<div class="modal-header">
			<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
			<h3>{vtranslate('LBL_ADD_NEW_KEY_METRICS', $MODULE)}</h3>
		</div>
		<form class="form-horizontal contentsBackground" id="addKeyMetricsWidget" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="KeyMetrics" />
			<input type="hidden" name="mode" value="addwidget" />
			<input type="hidden" name="id" value="{$METRICS_MODEL->getId()}" />
        <div class="modal-body">
            <div class="row-fluid">
                <div class="control-group">
                    <label class="control-label">
                        <span class="redColor">*</span>
                        {vtranslate('LBL_WIDGET_NAME', $MODULE)}
                    </label>
                    <div class="controls">
                        <input data-validation-engine='validate[required]' id="name" name="name" class="span4" type="text" value="{$METRICS_MODEL->getName()}"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">
                        {vtranslate('LBL_WIDGET_DESCRIPTION', $MODULE)}
                    </label>
                    <div class="controls">
                        <textarea rows="5" class="input-xxlarge fieldValue span4" name="description" id="description">{$METRICS_MODEL->get('description')}</textarea>
                    </div>
                </div>
            </div>
        </div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}