{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<div class="contentsDiv marginLeftZero" >
            
<div class="padding1per">

<div class="editContainer" style="padding-left: 3%;padding-right: 3%"><h3>{vtranslate('LBL_MODULE_NAME','ITS4YouReports')} {if $TYPE eq "reactivate" }{vtranslate('LBL_REACTIVATE','ITS4YouReports')}{else}{vtranslate('LBL_INSTALL','ITS4YouReports')}{/if}</h3>    
<hr>
<div id="breadcrumb">             
    <ul class="crumbs marginLeftZero">
        <li class="step {if $STEP eq "1"}active{/if}" style="z-index:9;" id="steplabel2"><a><span class="stepNum">1</span><span class="stepText">{vtranslate('LBL_IMPORT_STEP',$MODULE)}</span></a></li>
        <li class="step {if $STEP eq "2"}active{/if}" style="z-index:9;" id="steplabel3"><a><span class="stepNum">2</span><span class="stepText">{vtranslate('LBL_DOWNLOAD_STEP',$MODULE)}</span></a></li>
        <li class="step last {if $CURRENT_STEP eq $TOTAL_STEPS}active{/if}" style="z-index:7;" id="steplabel{$TOTAL_STEPS}"><a><span class="stepNum">{$TOTAL_STEPS}</span><span class="stepText">{vtranslate('LBL_FINISH',$MODULE)}</span></a></li>
    </ul>
</div>
<div class="clearfix">
</div>
<form name="install" id="editLicense"  method="POST" action="index.php" class="form-horizontal">
{*<input type="hidden" name="module" value="ITS4YouReports"/>
<input type="hidden" name="view" value="install"/>*}

<input type="hidden" id="currentView" name='currentView' value="List"/>
<input type="hidden" name="module" value="ITS4YouReports"/>
<input type="hidden" name="view" value="License"/>
<input type="hidden" id="type" name="type" value="{$TYPE}"/>
<input type="hidden" id="import_reports" name="import_reports" value=""/>


<div id="step1" class="padding1per" style="border:1px solid #ccc;  {if $STEP neq "1"}display:none;{/if}">

    <div class="">
        <div>

            {vtranslate('LBL_IMPORT_SKIP_1','ITS4YouReports')}
            <strong>"{vtranslate('LBL_IMPORT')}"</strong>
            {vtranslate('LBL_IMPORT_SKIP_2','ITS4YouReports')}
            <strong>"{vtranslate('LBL_NEXT','ITS4YouReports')}"</strong>
            {vtranslate('LBL_IMPORT_SKIP_3','ITS4YouReports')}
            <br /><br />
        </div>
    </div>
    <div class="">
        <button type="submit" id="import_button" class="btn btn-success" /><strong>{vtranslate('LBL_IMPORT','ITS4YouReports')}</strong></button>&nbsp;&nbsp;
        <button type="submit" id="skip_import_button" class="btn btn-success" /><strong>{vtranslate('LBL_NEXT','ITS4YouReports')}</strong></button>&nbsp;&nbsp;
    </div>
</div>

<div id="step2" class="padding1per" style="border:1px solid #ccc;  {if $STEP neq "2"}display:none;{/if}">

    <input type="hidden" name="installtype" value="download_src"/>
    <div class="">
        <div>
            <strong>{vtranslate('LBL_DOWNLOAD_SRC','ITS4YouReports')}</strong>
        </div>
        <br>
        <div class="clearfix">
        </div>
    </div>

    <div class="">
        <div>
            {vtranslate('LBL_DOWNLOAD_SRC_DESC','ITS4YouReports')}
        </div>
        <br>
        <div class="clearfix">
        </div>
    </div>
    <div class="">
        <button type="button" id="download_button" class="btn btn-success" onclick="window.open( 'http://www.its4you.sk/en/images/extensions/Reports4You/src/highcharts.zip' , '_newtab');"/><strong>{vtranslate('LBL_DOWNLOAD','ITS4YouReports')}</strong></button>&nbsp;&nbsp;
        <button type="button" id="finish_button" class="btn btn-success" onclick="location.reload(true);"/><strong>{vtranslate('LBL_FINISH','ITS4YouReports')}</strong></button>&nbsp;&nbsp;
    </div>
</div>

<div id="step{$TOTAL_STEPS}" class="padding1per" style="border:1px solid #ccc; {if $STEP neq "3"}display:none;{/if}" >
    <input type="hidden" name="installtype" value="redirect_recalculate" />
    <div class="">
        <div>
            {if $TYPE eq "reactivate" }
                {vtranslate('LBL_MISMATCH_SUCCESS','ITS4YouReports')}
            {else}
                {vtranslate('LBL_INSTALL_SUCCESS','ITS4YouReports')}
            {/if }
        </div>
        <div class="clearfix">
        </div>
    </div>
    <br>
    <div class="">
        <button type="button" id="next_button" class="btn btn-success"><strong>{vtranslate('LBL_FINISH','ITS4YouReports')}</strong></button>&nbsp;&nbsp;
    </div>
</div>

</form>
</div> 
</div>
</div>
<script language="javascript" type="text/javascript">

jQuery(document).ready(function() {
    //ITS4YouReports_License_Js.registerInstallEvents();
});
</script>                                   

 				