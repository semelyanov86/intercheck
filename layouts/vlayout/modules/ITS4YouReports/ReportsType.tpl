{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<div class="row-fluid">       
    <div class="span8">
        <div class="row-fluid">  
            <table class="table table-bordered">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_REPORT_TYPE',$MODULE)}
                       </th>
                   </tr>
                </thead>
                <tbody>  
                    <tr>
                            <td width="18%" class="dvtCellLabel" align="right" style="padding-right:5px;"><img src="{'tabular.gif'|@vtiger_imageurl:$THEME}" align="absmiddle"></td>
                            <td width="52%" class="dvtCellInfo" align="left" style="padding-left:5px;">		
                                    {if $REPORT_TYPE eq 'tabular'}
                                            <input checked type="radio" name="reportType" id="reportType" value="tabular" onChange="hideTabs();">
                                    {else}
                                            <input type="radio" name="reportType" id="reportType" value="tabular" onChange="hideTabs();">
                                    {/if}
                                    <b> {$MOD.LBL_TABULAR_FORMAT}</b><br />
                        {$MOD.LBL_TABULAR_REPORTS_ARE_SIMPLEST}</td>
                            </td>
                            <td class="dvtCellInfo" rowspan="4" style="vertical-align:top;">
                                    {$LBL_INFORMATIONS_4YOU}
                            </td>
                    </tr>
                    <tr>
                            <td width="18%" align="right" class="dvtCellLabel" style="padding-right:5px;"><img src="{'summarize.gif'|@vtiger_imageurl:$THEME}" align="absmiddle"></td>
                            <td width="52%" align="left" class="dvtCellInfo" style="padding-left:5px;">
                                    {if $REPORT_TYPE eq 'summary'}
                                            <input type="radio" checked name="reportType" id="reportType" value="summary" onclick="hideTabs();">
                                    {else}
                                            <input type="radio" name="reportType" id="reportType" value="summary" onclick="hideTabs();">
                                    {/if}
                                    <b> {$MOD.LBL_SUMMARY_REPORT}</b><br />
                  {$MOD.LBL_SUMMARY_REPORT_VIEW_DATA_WITH_SUBTOTALS}
                            </td>
                    </tr>
                    <tr>
                            <td width="18%" align="right" class="dvtCellLabel" style="padding-right:5px;"><img src="modules/ITS4YouReports/grouping.gif" align="absmiddle"></td>
                            <td width="52%" align="left" class="dvtCellInfo" style="padding-left:5px;">
                                    {if $REPORT_TYPE eq 'grouping'}
                                            <input type="radio" checked name="reportType" id="reportType" value="grouping" onclick="hideTabs();">
                                    {else}
                                            <input type="radio" name="reportType" id="reportType" value="grouping" onclick="hideTabs();">
                                    {/if}
                                    <b> {$MOD.LBL_GROUPING_REPORT}</b><br />
                  {$MOD.LBL_GROUPING_REPORT_VIEW_DATA_WITH_SUBTOTALS}
                            </td>
                    </tr>
                    <tr>
                            <td width="18%" align="right" class="dvtCellLabel" style="padding-right:5px;"><img src="modules/ITS4YouReports/timeline.png" align="absmiddle"></td>
                            <td width="52%" align="left" class="dvtCellInfo" style="padding-left:5px;">
                                    {if $REPORT_TYPE eq 'timeline'}
                                            <input type="radio" checked name="reportType" id="reportType" value="timeline" onclick="hideTabs();">
                                    {else}
                                            <input type="radio" name="reportType" id="reportType" value="timeline" onclick="hideTabs();">
                                    {/if}
                                    <b> {$MOD.LBL_TIME_LINE_REPORT}</b><br />
                  {$MOD.LBL_TIME_LINE_REPORT_VIEW_DATA_WITH_SUBTOTALS}
                            </td>
                    </tr>	
                <tbody> 
            </table>
        </div>
    </div>
    <div class="span4">
        <table class="dvtContentSpace" border="0" cellpadding="3" cellspacing="0" width="100%" style="float:left;border:0px;">
            <tr>
                <td class="detailedViewHeader">
                    <i class="icon-info-sign"></i>&nbsp;{vtranslate('LBL_INFORMATIONS_4YOU',$MODULE)}<br>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo">
			{$LBL_INFORMATIONS_4YOU}
		</td>
            </tr>
        </table>
    </div>
</div> 