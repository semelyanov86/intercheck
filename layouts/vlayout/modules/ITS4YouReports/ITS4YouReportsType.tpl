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
    <form class="form-horizontal recordEditView padding1per" id="form_report_types" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" >
            <input type="hidden" name="view" value="Edit" >
            <input type="hidden" name="record" value="" >
            <input type="hidden" name="reporttype" id="reporttype" value="" >
            <input type="hidden" name="mode" id="mode" value="{$MODE}" >
            
            <input type="hidden" name="is_admin_user" id="is_admin_user" value="{$IS_ADMIN_USER}" >
            
            {if $IS_ADMIN_USER == "1"}
                {assign var="imgHeight" value="height:8em;" }
            {else}
                {assign var="imgHeight" value="height:10em;" }
            {/if}
            
            <div class="padding1per border1px">
                    <div class="row-fluid">
                            <div>
                                    <div><h4><strong>{vtranslate('LBL_SELECT_REPORT_TYPE',$MODULE)}</strong></h4></div><br>
                                    <div>
                                        <div>
                                                <ul class="nav nav-tabs" name="reportypetab" id="reportypetab"  style="text-align:center;font-size:14px;font-weight: bold;margin:0 3%;border:0px">
                                                        <li class="active marginRight5px" >
                                                                <a id="tabular" data-toggle="tab">
                                                                        <div><img src="modules/{$MODULE}/chart_types/rt1.png" id="rt1" style="border:1px solid #ccc;{$imgHeight}"/></div><br>
                                                                        <div>{vtranslate('LBL_TABULAR_REPORT', $MODULE)}</div>
                                                                </a>
                                                        </li>
                                                        <li class="marginRight5px">
                                                                <a id="summaries" data-toggle="tab">
                                                                        <div><img src="modules/{$MODULE}/chart_types/rt2.png" id="rt2" style="border:1px solid #ccc;{$imgHeight}"/></div><br>
                                                                        <div>{vtranslate('LBL_SUMMARIES_REPORT', $MODULE)}</div>
                                                                </a>
                                                        </li>
                                                        <li class="marginRight5px">
                                                                <a id="summaries_w_details" data-toggle="tab">
                                                                        <div><img src="modules/{$MODULE}/chart_types/rt3.png" id="rt3" style="border:1px solid #ccc;{$imgHeight}"/></div><br>
                                                                        <div>{vtranslate('LBL_SUMMARIES_WITH_DETAILS_REPORT', $MODULE)}</div>
                                                                </a>
                                                        </li>
                                                        <li class="marginRight5px" >
                                                                <a id="summaries_matrix" data-toggle="tab">
                                                                        <div><img src="modules/{$MODULE}/chart_types/rt4.png" id="rt4" style="border:1px solid #ccc;{$imgHeight}"/></div><br>
                                                                        <div>{vtranslate('LBL_SUMMARIES_MATRIX_REPORT', $MODULE)}</div>
                                                                </a>
                                                        </li>
                                                        {if $IS_ADMIN_USER == "1"}
                                                            <li class="marginRight5px" >
                                                                    <a id="custom_report" data-toggle="tab">
                                                                            <div><img src="modules/{$MODULE}/chart_types/rt5.png" id="rt5" style="border:1px solid #ccc;{$imgHeight}"/></div><br>
                                                                            <div>{vtranslate('LBL_CUSTOM_REPORT', $MODULE)}</div>
                                                                    </a>
                                                            </li>
                                                        {/if}
                                                </ul>
                                                <div class='tab-content contentsBackground' id="reportypeInfoTab" style="height:auto;padding:4%;border:1px solid #ccc;">
                                                        {*<div class='row-fluid alert-info well' style="width:95%">
                                                                <span class='span alert-info'>
                                                                        <div>
                                                                            <i class="icon-info-sign"></i>&nbsp;&nbsp;
                                                                            {vtranslate('LBL_PLEASE_SELECT_REPORTTYPE', $MODULE)}
                                                                        </div>
                                                                </span>
                                                        </div>*}
                                                        <div>
                                                            {include file="ReportsTypeHiddenContents.tpl"|vtemplate_path:$MODULE}
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                            </div>
                    </div>
            </div>
            <br>
            <div class="pull-right block padding20px">
                    <button type="button" class="btn btn-danger backStep"><strong>{vtranslate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <button type="button" class="btn btn-success" id="createReport"><strong>{vtranslate('LBL_NEXT',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <br>
            </div>
    </form>
{/strip}