{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <table class="table table-borderless">
                <thead>
                    <th>
                        {vtranslate('LBL_VTEQBO_SYNC', $QUALIFIED_MODULE)}
                    </th>
                    <th class="text-right">
                        <a class="btn btn-default" href="index.php?module=VTEQBO&parent=Settings&view=Settings">{vtranslate('BTN_BACK', $QUALIFIED_MODULE)}</a>
                    </th>
                </thead>
                <tbody>
                {foreach item=MAPPED_MODULE from=$MAPPED_MODULES}
                    {if ($SYNC_TYPE eq 'both') || ($SYNC_TYPE eq 'qb2vt')}
                    <tr data-fromQbo="1" data-vtModule="{$MAPPED_MODULE[1]}" data-qboModule="{$MAPPED_MODULE[0]}">
                        <td style="width: 65%">{vtranslate('LBL_GET', $QUALIFIED_MODULE)}&nbsp;{vtranslate($MAPPED_MODULE[1], $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_FROM_QUICKBOOKS', $QUALIFIED_MODULE)}</td>
                        <td style="width: 35%; text-align: right;">
                            <a href="javascript:void(0);" class="btn btn-primary btn-small btnQboSync">{vtranslate('BTN_SYNC', $QUALIFIED_MODULE)}</a>&nbsp;
                            <a href="javascript:void(0);" class="btn btn-default btn-small btnAddToQueue">{vtranslate('BTN_QUEUE', $QUALIFIED_MODULE)}</a>
                        </td>
                    </tr>
                    {/if}
                    {if ($SYNC_TYPE eq 'both') || ($SYNC_TYPE eq 'vt2qb')}
                    <tr data-fromQbo="0" data-vtModule="{$MAPPED_MODULE[1]}" data-qboModule="{$MAPPED_MODULE[0]}">
                        <td style="width: 65%">{vtranslate('LBL_SYNC', $QUALIFIED_MODULE)}&nbsp;{vtranslate($MAPPED_MODULE[1], $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_TO_QUICKBOOKS', $QUALIFIED_MODULE)}</td>
                        <td style="width: 35%; text-align: right;">
                            <a href="javascript:void(0);" class="btn btn-primary btn-small btnQboSync">{vtranslate('BTN_SYNC', $QUALIFIED_MODULE)}</a>&nbsp;
                            <a href="javascript:void(0);" class="btn btn-default btn-small btnAddToQueue">{vtranslate('BTN_QUEUE', $QUALIFIED_MODULE)}</a>
                        </td>
                    </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
{/strip}