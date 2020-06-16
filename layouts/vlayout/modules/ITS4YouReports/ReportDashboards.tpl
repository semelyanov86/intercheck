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
    <div class="span9">
        <div class="row-fluid">  
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                            {vtranslate('LBL_REPORT_DASHBOARDS',$MODULE)}
                       </th>
                   </tr>
                </thead>
                <tbody>
                    <tr style="height:25px">
                        <td class="medium span3"><label class="pull-right marginRight10px">{vtranslate('PrimarySearchBy',$MODULE)}</label></td>
                        <td>
                            <select id="primary_search" name="primary_search" class="span4 chzn-select" style="margin:auto;">
                                <option value="none">{'LBL_NONE'|@getTranslatedString:'ITS4YouReports'}</option>
                                {foreach key=primary_search_key item=primary_search_arr from=$primary_search_options}
                                    <option value="{$primary_search_arr.value}" {if $primary_search_arr.value==$primary_search}selected='selected'{/if}>{$primary_search_arr.text}</option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr style="height:25px">
                        <td class="medium span3"><label class="pull-right marginRight10px">{vtranslate('AllowInModules',$MODULE)}</label></td>
                        <td>
                            <input type="hidden" name="allow_in_modules_hidden" id="allow_in_modules_hidden" value="">
                            <select id="allow_in_modules" multiple name="allow_in_modules" class="span4 chzn-select" style="margin:auto;">
                                {foreach key=moduleName item=moduleLabel from=$allmodules}
                                    <option value="{$moduleName}" 
                                    {if in_array($moduleName, $allowedmodules)}
                                        selected
                                    {/if} 
                                    >
                                      {$moduleLabel}
                                    </option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="span4" style="width: 20%;">
        <div class="row-fluid">           
            <table class="table table-bordered table-report">
                <thead>
                    <tr class="blockHeader">
                       <th colspan="2">
                        <i class="icon-info-sign"></i>&nbsp;{vtranslate('LBL_REPORT_DASHBOARDS',$MODULE)}<br>
                       </th>
                   </tr>
                </thead>
                <tbody>    
                    <tr style="height:25px">
                        <td>
                            <div class="padding1per">
                              <span>
                                {vtranslate('LBL_STEP13_INFO',$MODULE)}
                              </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>