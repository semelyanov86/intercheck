{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<script language="JAVASCRIPT" type="text/javascript" src="layouts/vlayout/modules/ITS4YouReports/resources/ITS4YouReports.js"></script>
{$DATE_FORMAT}	
{strip}
<script> var none_lang = "{vtranslate('LBL_NONE')}"; </script>

<style type="text/css">
.table-report th{
  border-bottom:1px solid #DDD;
}
.table-report td{
  border:0px;
}
.table-report tr td {
  background: none !important;
}
.table-bordered tr td{
  border:0px;
  vertical-align: middle;
}
.table-bordered input{
  vertical-align: middle;
  margin:auto;
}
{* //ITS4YOU-CR SlOl 11. 5. 2016 11:53:26 *}
.conditionFilterDiv{
    background: #EEEFF2;
    padding-left:5px;
    padding-top:5px;
    padding-right:5px;
    margin-bottom: 10px;
    border: 0.1px solid rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}
{* //ITS4YOU-END *}
</style>

<form name="NewReport" id="NewReport" action="index.php" method="POST" enctype="multipart/form-data" onsubmit="return changeSteps();">

<input type="hidden" name="module" value="ITS4YouReports">
{*<input type="text" name="primarymodule" id="primarymodule" value="{$REP_MODULE}">*}
<input type="hidden" name='secondarymodule' id='secondarymodule' value="{$SEC_MODULE}"/>
<input type="hidden" name="record" id="record" value="{$RECORDID}">
<input type="hidden" name='modulesString' id='modulesString' value=''/>
<input type="hidden" name='reload' id='reload' value='true'/>
<input type="hidden" name='action' id='action' value='Save'/>
<input type="hidden" name='file' id='file' value=''/>
<input type="hidden" name='folder' id='folder' value="{$FOLDERID}"/>
<input type="hidden" name='relatedmodules' id='relatedmodules' value='{$relmodulesstring}'/>
<input type="hidden" name='mode' id='mode' value='{$MODE}' />
<input type="hidden" name='isDuplicate' id='isDuplicate' value='{$isDuplicate}' />
<input type="hidden" name='SaveType' id='SaveType' value='' />
<input type="hidden" name='actual_step' id='actual_step' value='1' />
<input type="hidden" name='cancel_btn_url' id='cancel_btn_url' value='{$cancel_btn_url}' />

<input type="hidden" name="reporttype" id="reporttype" value="{$REPORTTYPE}">

<!-- DISPLAY -->
<div class="row-fluid detailViewTitle">
  <div class=" span10 ">
    <div class="row-fluid">
      <div class="span5">
        <div class="row-fluid">
          <span class="span10" style="padding:0px 0px 0px 2em;">
            <span class="row-fluid">
              <span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="">
                    {if $MODE eq 'edit'}
                        {if $isDuplicate == "true"}
                            <span class="report_name">{vtranslate('LBL_DUPLICATE',$MODULE)} &quot;{$REPORTNAME}&quot;</span>&nbsp;
                        {else}
                            <span class="report_name">{vtranslate('LBL_EDIT',$MODULE)} &quot;{$REPORTNAME}&quot;</span>&nbsp;
                        {/if}
                    {else}
                        <span class="report_name">{vtranslate('LBL_NEW_TEMPLATE',$MODULE)}</span>&nbsp;
                    {/if}
              </span>
            </span>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="contents tabbable ui-sortable">
    <ul class="nav nav-tabs layoutTabs massEditTabs" id="reportTabs" style="margin-left:0.6em;" >
        <li class="r4you_step active" id="rtypestep1">
            <a data-toggle="tab" data-step="1" href="#"><strong>{vtranslate('LBL_REPORT_DETAILS',$MODULE)}</strong></a>
        </li>
        {* <li class="relatedListTab">
            <a data-toggle="tab" href="#relatedTabReport" onclick="changeSteps4U(2)"><strong>{vtranslate('LBL_RELATIVE_MODULES',$MODULE)}</strong></a>
        </li> *}
        <li class="r4you_step relatedListTab" id="rtypestep4" >
            <a data-toggle="tab" data-step="4" href="#relatedTabReport"><strong>{vtranslate('LBL_SPECIFY_GROUPING',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep5" >
            <a data-toggle="tab" data-step="5" href="#relatedTabReport"><strong>{vtranslate('LBL_SELECT_COLUMNS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep6" >
            <a data-toggle="tab" data-step="6" href="#relatedTabReport"><strong>{vtranslate('LBL_CALCULATIONS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep7" >
            <a data-toggle="tab" data-step="7" href="#relatedTabReport"><strong>{vtranslate('LBL_LABELS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep8" >
            <a data-toggle="tab" data-step="8" href="#relatedTabReport"><strong>{vtranslate('LBL_FILTERS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep9" >
            <a data-toggle="tab" data-step="9" href="#relatedTabReport"><strong>{vtranslate('LBL_SHARING',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep10" >
            <a data-toggle="tab" data-step="10" href="#relatedTabReport"><strong>{vtranslate('LBL_LIMIT_SCHEDULER',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep11" >
            <a data-toggle="tab" data-step="11" href="#relatedTabReport"><strong>{vtranslate('LBL_GRAPHS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep12" >
            <a data-toggle="tab" data-step="12" href="#relatedTabReport"><strong>{vtranslate('LBL_REPORT_DASHBOARDS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep14" >
            <a data-toggle="tab" data-step="14" href="#relatedTabReport"><strong>{vtranslate('LBL_REPORT_MAPS',$MODULE)}</strong></a>
        </li>
    </ul>   
</div>  
<div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
    <div class="tab-pane active" id="detailViewLayout">            
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
{*** BUTTONS TOP ***}
<tr>
    <td class="small" style="text-align:center;padding:0px 0px 10px 0px;">
        <input type="button" name="back_rep_top" id="back_rep_top" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
        <div  id="submitbutton" style="display:{if $MODE !='edit'}none{else}inline{/if};" >
            <button class="btn btn-success" type="button" id="savebtn" onclick=""><strong>{vtranslate('LBL_SAVE_BUTTON_LABEL',$MODULE)}</strong></button>
            <button type="button" class="btn btn-danger backStep" id="cancelbtn" onclick=""><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
            <button class="btn btn-success" type="button" id="saverunbtn" onclick=""><strong>{vtranslate('LBL_SAVE_RUN_BUTTON_LABEL',$MODULE)}</strong></button>
        </div>
        <div  id="submitbutton0T" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
            <button type="button" class="btn btn-danger backStep" id="cancelbtn0T" onclick=""><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
        </div>
        <input type="button" name="next" id="next_rep_top" value=" &nbsp;{vtranslate('LNK_LIST_NEXT',$MODULE)}&nbsp;&rsaquo;&nbsp; " onClick="" class="btn">&nbsp;&nbsp;
    </td>
</tr>
{*** BUTTONS TOP  END ***}
<tr>
    <td align="left" valign="top">
        {*************************		 STEP 1 		************************} 
        <div class="reportTab" id="step1">
                {include file='modules/ITS4YouReports/ReportsStep1.tpl'}
        </div>
        {*************************		 STEP 3 END 		************************}
        
        {*************************		 STEP 4 		************************}
        <div class="{$steps_display}" id="step4">
                {$REPORT_GROUPING}
        </div>
        {*************************		 STEP 4 END 		************************}
        
        {*************************		 STEP 5 		************************}
        <div class="{$steps_display}" id="step5">
                {$REPORT_COLUMNS}
        </div>
        {*************************		 STEP 5 END 		************************}
        
        {*************************		 STEP 6 		************************}
        <div class="{$steps_display}" id="step6">
                {$REPORT_COLUMNS_TOTAL}
        </div>
        <div class="{$steps_display}" id="step61">
            {if $RECORDID!=""}
                {$REPORT_CUSTOM_CALCULATIONS}
            {/if}
        </div>
        {*************************		 STEP 6 END 		************************}
        {*************************		 STEP 7 		************************}
        <div class="{$steps_display}" id="step7">
                {$REPORT_LABELS}
        </div>
        {*************************		 STEP 7 END 		************************}
        {*************************		 STEP 8 		************************}
        <div class="{$steps_display}" id="step8">
                {$REPORT_FILTERS}
        </div>
        {*************************		 STEP 8 END 		************************}
        {*************************		 STEP 9 		************************}
        <div class="{$steps_display}" id="step9">
                {$REPORT_SHARING}
        </div>
        {*************************		 STEP 9 END 		************************}
        {*************************		 STEP 10 		************************}
        <div class="{$steps_display}" id="step10">
                {$REPORT_SCHEDULER}
        </div>
        {*************************		 STEP 10 END 		************************}
        {*************************		 STEP 11 		************************}
        {*<div id="step11" style="display:block;">
                {php}include("modules/ITS4YouReports/ReportQuickFilter.php");{/php}
        </div>*}
        {*************************		 STEP 11 END 		************************}
        {*************************		 STEP 12 		************************}
        <div class="{$steps_display}" id="step11">
                {$REPORT_GRAPHS}
        </div>
        {*************************		 STEP 12 END 		************************}
        {*************************		 STEP 13 		************************}
        <div class="{$steps_display}" id="step12">
                {$REPORT_DASHBOARDS}
        </div>
        {*************************		 STEP 13 END 		************************}
		    {*************************		 STEP 14 		************************}
		    <div class="{$steps_display}" id="step14">
		        {$REPORT_MAPS}
		    </div>
		    {*************************		 STEP 14 END 		************************}
                      
        {************************************** END OF TABS BLOCK *************************************}                         
       </td></tr>
       </table>

        {****** BUTTONS BOTTOM ******}
        <table width="100%"  border="0" cellspacing="0" cellpadding="5" >
            <tr><td class="small" style="text-align:center;padding:10px 0px 10px 0px;" colspan="3">
                <input type="button" name="back_rep_top" id="back_rep_top2" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
                <div  id="submitbutton2" style="display:{if $MODE !='edit'}none{else}inline{/if};" >
                    <button class="btn btn-success" type="button" id="savebtn2" onclick=""><strong>{vtranslate('LBL_SAVE_BUTTON_LABEL',$MODULE)}</strong></button>
                    <button type="button" class="btn btn-danger backStep" id="cancelbtn2" onclick=""><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
                    <button class="btn btn-success" type="button" id="saverunbtn2" onclick=""><strong>{vtranslate('LBL_SAVE_RUN_BUTTON_LABEL',$MODULE)}</strong></button>
                </div>
                <div  id="submitbutton0B" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
                    <button type="button" class="btn btn-danger backStep" id="cancelbtn0B" onclick=""><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
                </div>
                <input type="button" name="next" id="next_rep_top2" value=" &nbsp;{vtranslate('LNK_LIST_NEXT',$MODULE)}&nbsp;&rsaquo;&nbsp; " onClick="" class="btn">&nbsp;&nbsp;
            </td></tr>
        </table>
        {****** BUTTONS BOTTOM END ******}
        </div>
</div> 
</form>
 
{include file='modules/ITS4YouReports/EditScript.tpl'}

{/strip}