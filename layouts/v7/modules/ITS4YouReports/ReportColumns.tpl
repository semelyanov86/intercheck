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

    <script>
        var moveupLinkObj,moveupDisabledObj,movedownLinkObj,movedownDisabledObj;
    </script>
    <div style="border:1px solid #ccc;padding:4%;">

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</label>
                </div>
                <div class="col-lg-2">
                    <select id="availModules" name="availModules" onchange='defineModuleFields(this)' class="select2 inputElement">
                        {foreach item=modulearr from=$availModules}
                            <option value={$modulearr.id} {if $modulearr.checked == "checked"}selected="selected"{/if} >{$modulearr.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                <input type="text" id="search_input" onkeyup="getFieldsOptionsSearch(this)" placeholder="{vtranslate('LBL_Search_column',$MODULE)}"
                           class="inputElement">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-3">
                    <div id="availModValues" style="display:none;">{$ALL_FIELDS_STRING}</div>
                    <select id="availList" multiple size="20" name="availList" class="txtBox" ondblclick="addOndblclick(this)" style="width:100%;" >
                        {$BLOCK1}
                    </select>
                </div>
                <div class="col-lg-1">
                    <button type='button' class='btn btn-default' onclick="addColumn('selectedColumns');" style="margin-top: 50%;">{vtranslate('LBL_ADD_ITEM',$MODULE)} <i class="fa fa-arrow-right"></i></button>
                </div>
                <div class="col-lg-3">
                    <input type="hidden" name="selectedColumnsString" id="selectedColumnsString">
                    <select id="selectedColumns" size="20" name="selectedColumns" onchange="selectedColumnClick(this);" multiple class="inputElement" style="width:100%;" >
                        {$BLOCK2}
                    </select>
                </div>
                <div class="col-lg-1">
                    <button type='button' class='btn btn-default' onclick="moveUp('selectedColumns');" title="{vtranslate('LBL_MOVE_UP_ITEM',$MODULE)}"><i class="fa fa-arrow-circle-up"></i></button>
                    <br>
                    <button type='button' class='btn btn-default' onclick="delColumn('selectedColumns');" title="{vtranslate('LBL_DELETE',$MODULE)}"><i class="fa fa-times-circle "></i></button>
                    <br>
                    <button type='button' class='btn btn-default' onclick="moveDown('selectedColumns');" title="{vtranslate('LBL_MOVE_UP_ITEM',$MODULE)}"><i class="fa fa-arrow-circle-down"></i></button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">{vtranslate('LBL_SORT_FIELD',$MODULE)}</label>
                </div>
                <div class="col-lg-1">
                    <span id="addSortCol1">
                        <button type='button' class='btn btn-default' style='float:left;' onclick="addSortColumnRow()"><i class="fa fa-plus"></i>&nbsp;{vtranslate('LBL_ADD_ITEM',$MODULE)}</button>
                    </span>
                </div>
                <div class="col-lg-4 fieldBlockContainer">
                    <div id="sortColumnsByDiv">
                        <input id="scolrow_n" name="scolrow_n" type="hidden" value="{$scolrow_n}">
                        <div id="sortColumnsByDivBase" style="display:none;">
                            <div id="sortColumnRow" style="padding-top:5px;vertical-align: middle;">
                                <select id="SortByColumnIdNr" name="SortByColumnIdNr" class="rep_select2 inputElement" style="margin:auto;" >
                                    <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                    {$BLOCK3}
                                </select>
                                &nbsp;&nbsp;
                                <input type="radio" name="SortOrderColumnIdNr" value="ASC" checked>{vtranslate('Ascending',$MODULE)}&nbsp;
                                <input type="radio" name="SortOrderColumnIdNr" value="DESC">{vtranslate('Descending',$MODULE)}&nbsp;
                                <a onclick="deleteSortColumnRow(this);" href="javascript:;"><img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0" ></a>
                            </div>
                        </div>

                        {assign var="sortColumnRowPadding" value="" }
                        {foreach key=SC_INDEX item=SC_BLOCK from=$BLOCKS3}
                            {if $SC_INDEX>1}
                                {assign var="sortColumnRowPadding" value="padding-top:5px;" }
                            {/if}
                            <div id="sortColumnRow" style="{$sortColumnRowPadding}vertical-align: middle;">
                                <select id="SortByColumn{$SC_INDEX}" name="SortByColumn{$SC_INDEX}" class="select2 inputElement" style="margin:auto;" >
                                    <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                    {$SC_BLOCK}
                                </select>
                                &nbsp;&nbsp;
                                <input type="radio" name="SortOrderColumn{$SC_INDEX}" value="ASC" {if $BLOCKS_ORDER3.$SC_INDEX=="ASC"}checked{/if}>{vtranslate('Ascending',$MODULE)}&nbsp;
                                <input type="radio" name="SortOrderColumn{$SC_INDEX}" value="DESC" {if $BLOCKS_ORDER3.$SC_INDEX=="DESC"}checked{/if}>{vtranslate('Descending',$MODULE)}&nbsp;
                                <a onclick="deleteSortColumnRow(this);" href="javascript:;"><img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0" ></a>
                            </div>
                            {foreachelse}
                            <div id="sortColumnRow" style="vertical-align: middle;">
                                <select id="SortByColumn1" name="SortByColumn1" class="select2 inputElement" style="margin:auto;" >
                                    addSortColumnRow        <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                                    {$BLOCK3}
                                </select>
                                &nbsp;&nbsp;
                                <input type="radio" name="SortOrderColumn1" value="ASC" checked >{vtranslate('Ascending',$MODULE)}&nbsp;
                                <input type="radio" name="SortOrderColumn1" value="DESC">{vtranslate('Descending',$MODULE)}&nbsp;
                                <a onclick="deleteSortColumnRow(this);" href="javascript:;"><img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0" ></a>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-2">
                    <label class="control-label textAlignLeft">{vtranslate('SET_LIMIT',$MODULE)}</label>
                </div>
                <div class="col-lg-4 fieldBlockContainer">
                    <input type="text" id="columns_limit" name="columns_limit" value="{if $COLUMNS_LIMIT!="0"}{$COLUMNS_LIMIT}{/if}" class="inputElement" >&nbsp;&nbsp;<small>{vtranslate('SET_EMPTY_FOR_ALL',$MODULE)}</small>
                </div>
            </div>
        </div>
    </div>

{/strip}