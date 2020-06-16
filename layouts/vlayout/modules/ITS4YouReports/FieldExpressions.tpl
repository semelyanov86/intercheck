{*
/* * *******************************************************************************
 * The content of this file is subject to the Dynamic Fields 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
*}
{strip}
{assign var="columnIndex" value="WCCINRW" }
{assign var="columnIndexSpecial" value="chzn-done" }

<div id='fieldExpressionsBase' >
    <div class="fieldExpressionsBase hide" id='fieldExpressionsBaseWCCINRW' style="padding-left: 3%;padding-right: 3%">
        <div class="popupUi modal " data-backdrop="false" style="z-index: 1000006;min-width: 750px;overflow: visible">
        	<div class="modal-header contentsBackground" style="text-align:left;">
        		<button type="button" class="close" onClick="jQuery('#fieldExpressionsBase{$columnIndex}').css('display', 'none');" data-dismiss="modal" aria-hidden="true">&times;</button>
        		<h3>{vtranslate('LBL_SET_VALUE',$MODULE)}</h3>
        	</div>
        	<div class="modal-body">
        		<div class="row-fluid">
        			<span class="span4" style="text-align:left;">
                <select name="fc_fval_{$columnIndex}" id="fc_fval_{$columnIndex}" onChange="AddFieldToFilter('{$columnIndex}',this);" class="span6 chzn-select {$columnIndexSpecial}"  style="margin-top:0.5em;">
                <option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
                {$REL_FIELDS}
                </select>
        			</span>
        		</div>
{*
            <br>
        		<div class="row-fluid fieldValueContainer">
        			<textarea data-textarea="true" class="fieldValue row-fluid"></textarea>
        		</div>
            <br>
*}
        	</div>
        	<div class="modal-footer">
        		<div class=" pull-right cancelLinkContainer">
        			<a class="cancelLink closeModal" type="button" onClick="jQuery('#fieldExpressionsBase{$columnIndex}').css('display', 'none');">{vtranslate('LBL_CANCEL', $MODULE)}</a>
        		</div>
{*
        		<button class="btn btn-success" type="button" name="saveButton"><strong>{vtranslate('LBL_DONE', $MODULE)}</strong></button>
*}
        	</div>
        </div>
        <div class="clonedPopUp"></div>
    </div>
</div>
{/strip}