{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{*<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="JAVASCRIPT" type="text/javascript" src="include/js/general.js"></script>*}
{include file="EditHeader.tpl"|vtemplate_path:$MODULE}
{*<link rel="stylesheet" type="text/css" media="all" href="Smarty/templates/modules/ITS4YouReports/Reports4You.css">*}
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
</style>

<form name="NewReport" class="form-horizontal recordEditView" id="NewReport" action="index.php" method="POST" enctype="multipart/form-data" onsubmit="return changeSteps();">

<input type="hidden" name="module" value="ITS4YouReports">
<input type="hidden" name='actual_step' id='actual_step' value='1' />
<input type="hidden" name="record" id="record" value="{$RECORDID}">
<input type="hidden" name='reload' id='reload' value='true'/>
<input type="hidden" name='action' id='action' value='Save'/>
<input type="hidden" name='file' id='file' value=''/>
<input type="hidden" name='folder' id='folder' value="{$FOLDERID}"/>
<input type="hidden" name='relatedmodules' id='relatedmodules' value='{$relmodulesstring}'/>
<input type="hidden" name='mode' id='mode' value='{$MODE}' />
<input type="hidden" name='isDuplicate' id='isDuplicate' value='{$isDuplicate}' />
<input type="hidden" name='SaveType' id='SaveType_v7' value='' />
<input type="hidden" name='actual_step' id='actual_step' value='1' />
<input type="hidden" name='cancel_btn_url' id='cancel_btn_url' value='{$cancel_btn_url}' />

<input type="hidden" name="reporttype" id="reporttype" value="{$REPORTTYPE}">

{*
<!-- DISPLAY -->
<table border=0 cellspacing=0 cellpadding=5 width=100%>
    <tr>
    {if $CREATE_MODE eq 'edit'}
        {if $DUPLICATE_REPORTNAME eq ""}
            <td class=heading2 valign=bottom>&nbsp;&nbsp;<b>{$MOD.LBL_EDIT} &quot;{$REPORTNAME}&quot; </b></td>
        {else}
            <td class=heading2 valign=bottom>&nbsp;&nbsp;<b>{$MOD.LBL_DUPLICATE} &quot;{$DUPLICATE_REPORTNAME}&quot; </b></td>
        {/if}
    {else}
        <td class=heading2 valign=bottom>&nbsp;&nbsp;<b>{$MOD.LBL_NEW_TEMPLATE}</b></td>
    {/if}
    </tr>
</table>

{*
<div class="contents tabbable ui-sortable">
    <ul class="nav nav-tabs layoutTabs massEditTabs" id="reportTabs" style="margin-left:0.6em;" >
        <li class="r4you_step active" id="rtypestep1">
            <a data-toggle="tab" data-step="1" href="#"><strong>{vtranslate('LBL_REPORT_DETAILS',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep12" >
            <a data-toggle="tab" data-step="12" href="#relatedTabReport"><strong>{vtranslate('LBL_REPORT_SQL',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep9" >
            <a data-toggle="tab" data-step="9" href="#relatedTabReport"><strong>{vtranslate('LBL_SHARING',$MODULE)}</strong></a>
        </li>
        <li class="r4you_step relatedListTab" id="rtypestep10" >
            <a data-toggle="tab" data-step="10" href="#relatedTabReport"><strong>{vtranslate('LBL_LIMIT_SCHEDULER',$MODULE)}</strong></a>
        </li>
    </ul>   
</div>  

<div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
    <div class="tab-pane active" id="detailViewLayout">            
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
{*** BUTTONS TOP *** }
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
                {include file='modules/ITS4YouReports/ReportsCustomStep1.tpl'}
        </div>
        {*************************		 STEP 1 END 		************************}
        {*************************		 STEP 13 		************************}
        <div class="{$steps_display}" id="step13">
                {$REPORT_CUSTOMSQL}
        </div>
        {*************************		 STEP 13 END 		************************}
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

        {************************************** END OF TABS BLOCK *************************************}                         
       </td></tr>
       </table>

        {****** BUTTONS BOTTOM ****** }
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
    <div class="clearfix"></div>

{include file='modules/ITS4YouReports/EditScript.tpl'}

{/strip}