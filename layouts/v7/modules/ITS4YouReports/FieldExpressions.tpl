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
    <div class="form-group hide" id='fieldExpressionsBase'>
        <div class="fieldExpressionsBase " id='fieldExpressionsBaseWCCINRW' style="position:relative;left:40%;padding-left: 3%;padding-right: 3%">
            <div class="col-lg-5" data-backdrop="false" style="z-index: 900;min-width: 750px;overflow: visible;border:1px solid #ccc;padding:0px;">
                <div class="modal-header contentsBackground" style="text-align:left;">
                    <button type="button" class="close" onClick="jQuery('#fieldExpressionsBase{$columnIndex}').css('display', 'none');" data-dismiss="modal" aria-hidden="true" style="margin-top:2px; opacity: .9;">
                        &times;
                    </button>

                    <h3>{vtranslate('LBL_SET_VALUE',$MODULE)}</h3>
                </div>
                <div class="modal-body">
                    <div class="row-fluid">
        			<span class="span4" style="text-align:left;">
                <select name="fc_fval_{$columnIndex}" id="fc_fval_{$columnIndex}" onChange="AddFieldToFilter('{$columnIndex}',this);" class="inputElement"
                        style="margin-top:0.5em;">

                </select>
        			</span>
                    </div>
                </div>
            </div>
            <div class="clonedPopUp"></div>

        </div>
    </div>
{/strip}