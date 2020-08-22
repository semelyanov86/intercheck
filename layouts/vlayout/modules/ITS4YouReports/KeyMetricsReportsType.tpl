{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
<script type="text/javascript">
jQuery().ready(function() {
    var ITS4YouReports_KM_Js = new ITS4YouReports_KeyMetricsList_Js();
    ITS4YouReports_KM_Js.registerEditKeyMetricsRowStep2();
    ITS4YouReports_KM_Js.registerEditKeyMetricsRowStep3();
});
</script>

<div class="tab-content padding20 themeTableColor overflowVisible">
    <div class="active" id="detailViewLayout">            
<div class="contentHeader row-fluid"><h3 class="span8 textOverflowEllipsis">{vtranslate('LBL_CREATE_NEW_KEY_METRICS',$MODULE)}</h3>
</div>

<form class="" id="addKeyMetricsWidget" method="post" action="index.php">
    <input type="hidden" name="module" value="{$MODULE}" />
    <input type="hidden" name="action" value="SaveKeyMetricsRow" />
    <input type="hidden" name="km_id" id="km_id" value="{$KM_ID}">
    <input type="hidden" name="id" value="{$ID}" />

<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
{*** BUTTONS TOP ***}
<tr>
    <td class="small" style="text-align:center;padding:0px 0px 10px 0px;">
        {* BACK STANDARD DISABLED BUTTON
        <input type="button" name="back_rep_top" id="back_rep_top" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
        *}
        <input type="button" name="back_rep_top" id="back_rep_top" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " class="btn" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';">&nbsp;&nbsp;        
        {* CANCEL RED BUTTON
        <div  id="submitbutton0T" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
            <button type="button" class="btn btn-danger backStep" id="cancelbtn0T" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';"><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
        </div>
        *}
        
        {*<input type="button" name="next" id="next_rep_top" value=" &nbsp;{vtranslate('LNK_LIST_NEXT',$MODULE)}&nbsp;&rsaquo;&nbsp; " onClick="" class="btn">&nbsp;&nbsp;*}
        <button class="btn btn-success" type="submit" id="savemetricbtn" onclick=""><strong>{vtranslate('LBL_SAVE',$MODULE)}</strong></button>
        
    </td>
</tr>
{*** BUTTONS TOP  END ***}
<tr>
    <td align="left" valign="top">
        {*************************		 STEP 1 		************************} 
        <div class="reportTab" id="step1">
            {include file='modules/ITS4YouReports/KeyMetricsRowStep.tpl'}
        </div>
        {*************************		 STEP 3 END 		************************}
           
    {************************************** END OF TABS BLOCK *************************************}                         
   </td>
</tr>
</table>

{****** BUTTONS BOTTOM ******}
<table width="100%"  border="0" cellspacing="0" cellpadding="5" >
    <tr><td class="small" style="text-align:center;padding:10px 0px 10px 0px;" colspan="3">
        {* BACK STANDARD DISABLED BUTTON
        <input type="button" name="back_rep_top" id="back_rep_top2" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " disabled="disabled" class="btn" onClick="">&nbsp;&nbsp;
        *}
        <input type="button" name="back_rep_top" id="back_rep_top2" value=" &nbsp;&lt;&nbsp;{vtranslate('LBL_BACK',$MODULE)}&nbsp; " class="btn" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';">&nbsp;&nbsp;        
        {* CANCEL RED BUTTON
        <div  id="submitbutton0B" style="display:{if $MODE !='edit'}inline{else}none{/if};" >
            <button type="button" class="btn btn-danger backStep" id="cancelbtn0B" onclick="window.location.href='index.php?module=ITS4YouReports&view=KeyMetricsRows&id={$KM_ID}';"><strong>{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</strong></button>&nbsp;
        </div>
        *}
                
        {*<input type="button" name="next" id="next_rep_top2" value=" &nbsp;{vtranslate('LNK_LIST_NEXT',$MODULE)}&nbsp;&rsaquo;&nbsp; " onClick="" class="btn">&nbsp;&nbsp;*}
        <button class="btn btn-success" type="submit" id="savemetricbtn2" onclick=""><strong>{vtranslate('LBL_SAVE',$MODULE)}</strong></button>
        
    </td></tr>
</table>
{****** BUTTONS BOTTOM END ******}

</form>

    </div>
</div> 

{/strip}