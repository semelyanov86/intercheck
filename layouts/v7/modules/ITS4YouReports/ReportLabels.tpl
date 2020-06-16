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

    {assign var="ROWS_COUNT1" value=$ROWS_COUNT }
    {assign var="ROWS_COUNT2" value=$ROWS_COUNT+1 }
    {assign var="COL_SPAN" value=2 }
    {if $ROWS_COUNT>20}
        {math assign="ROWS_COUNT1" equation="($ROWS_COUNT1/2)+2"}
        {math assign="ROWS_COUNT2" equation="($ROWS_COUNT2/2)+2"}
        {assign var="COL_SPAN" value=$COL_SPAN+2 }
    {/if}

    <div style="border:1px solid #ccc;padding:4%;">
        <input type="hidden" name="labels_to_go" id="labels_to_go" value="_XYZ_">

        {assign var="ROWS_I" value=0 }
        {foreach key=lbl_type item=type_arr from=$labels_html}
            {if $lbl_type == "SC"}
                {assign var="type_row_lbl" value=vtranslate("LBL_SC_LABELS",$MODULE)}
            {elseif $lbl_type == "SM"}
                {assign var="type_row_lbl" value=vtranslate("LBL_SM_LABELS",$MODULE)}
            {/if}
            {if !empty($type_arr)}
                <div class="row">
                    <div class="form-group">
                        <label class="control-label textAlignLeft">
                            <h4>{$type_row_lbl}</h4>
                        </label>
                    </div>
                </div>
            {/if}
            {assign var="ROWS_I" value=$ROWS_I+1 }
            {assign var="make_row" value=1 }
            {foreach key=fieldi item=fieldarray from=$type_arr}
                {assign var="fieldkey" value=$fieldarray.translated_key}
                {assign var="fieldinput" value=$fieldarray.translate_html}
                <div class="row">
                    <div class="form-group">
                        <label class="control-label textAlignLeft col-lg-2">
                            {$fieldkey}
                        </label>
                        <div class="col-lg-4">
                            {$fieldinput}
                        </div>
                    </div>
                </div>
                {assign var="ROWS_I" value=$ROWS_I+1 }
            {/foreach}
        {/foreach}

    </div>
{/strip}