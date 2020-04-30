<div class="col-md-5 config-by-module">
    <input type="hidden" class="vtModule" value="{$CONFIG_DATA['vtigerModule']}" />
    <input type="hidden" class="qboModule" value="{$CONFIG_DATA['platformModule']}" />
    <div class="row">
        {if $CONFIG_DATA['OtherInfo']['vt_tab_id'] neq ''}
        {assign var=TOTAL_QB_N_E_FIELDS value=0}
        {assign var=TOTAL_QB_FIELDS value=count($CONFIG_DATA['platform_fields'])}
        {assign var=TOTAL_MAPPED_FIELDS value=count($CONFIG_DATA['mappedFields'])}
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th colspan="3" class="text-center" style="font-size: 20px;">{vtranslate($CONFIG_DATA['vtigerModule'], 'PlatformIntegration')}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center"><b>{vtranslate('LBL_VTIGER_FIELDS', 'PlatformIntegration')}</b></td>
                    <td class="text-center"><b>{vtranslate('LBL_PLATFORM_FIELDS', 'PlatformIntegration')}</b></td>
                    <td></td>
                </tr>
                {if $CONFIG_DATA['platformModule'] == 'UserMetaData'}
                <tr class="row-item">
                    <td>
                        <select class="select2 list-fields vtiger-fields">
                            {assign var=FIELD_NAME2 value=vtranslate($CONFIG_DATA['vt_fields']['accountname']->get('label'), $CONFIG_DATA['vtigerModule'])}
                            <option value="accountname_dn" {if $CONFIG_DATA['Map_DisplayName'] eq 'accountname_dn'}selected{/if}>{$FIELD_NAME2}</option>
                            <option value="contactname_dn" {if $CONFIG_DATA['Map_DisplayName'] eq 'contactname_dn'}selected{/if}>{vtranslate('LBL_ORGANIZATIONS_CONTACTNAME', 'PlatformIntegration')}</option>
                        </select>
                    </td>
                    <td>
                        <select class="list-fields qbo-fields hide">
                            <option value='DisplayName' selected>{vtranslate('LBL_CUSTOMER_DISPLAY_NAME_AS', 'PlatformIntegration')}</option>
                        </select>
                        <input readonly="True" class="inputElement defaultField"
                        value="{vtranslate('LBL_CUSTOMER_DISPLAY_NAME_AS', 'PlatformIntegration')}" /></td>
                    <td></td>
                </tr>
                {/if}
                {foreach item=MAPPED_FIELD from=$CONFIG_DATA['mappedFields']}
                <tr class="row-item">
                    <td>
                        {if $MAPPED_FIELD['non_editable'] eq '1'}
                            {assign var=FIELD_NAME2 value=vtranslate($MAPPED_FIELD['vt_field_label'], $CONFIG_DATA['vtigerModule'])}
                            {if $FIELD_NAME2 eq ''}
                                {assign var=FIELD_NAME2 value=vtranslate($MAPPED_FIELD['platform_field_label'], 'PlatformIntegration')}
                            {/if}
                            <select class="list-fields vtiger-fields hide">
                                <option value="{$MAPPED_FIELD['vt_field']}" selected>
                                    {$FIELD_NAME2}
                                </option>
                            </select>
                            <input readonly="True" class="inputElement defaultField"
                            value="{$FIELD_NAME2}" />
                        {else}
                            <select class="select2 list-fields vtiger-fields">
                                <option></option>
                                {foreach item=FIELD key=FIELD_NAME from=$CONFIG_DATA['vt_fields']}
                                    {if $FIELD->get('is_show_on_config') eq 1}
                                    <option value="{$FIELD_NAME}" {if $MAPPED_FIELD['vt_field'] eq $FIELD_NAME}selected{/if}>
                                        {vtranslate($FIELD->get('label'), $CONFIG_DATA['vtigerModule'])}
                                    </option>
                                    {/if}
                                {/foreach}
                            </select>
                        {/if}
                    </td>
                    <td>
                        {if $MAPPED_FIELD['non_editable'] eq '1'}
                            {foreach item=FIELD from=$CONFIG_DATA['platform_fields']}
                                {if $MAPPED_FIELD['platform_field'] eq $FIELD['platform_field']}
                                    <input readonly="True" class="inputElement defaultField"
                                    value="{vtranslate($FIELD['qb_field_label'], 'PlatformIntegration')}" />
                                    <select class="list-fields qbo-fields hide">
                                        <option selected value="{$FIELD['qb_field']}">{vtranslate($FIELD['qb_field_label'], 'PlatformIntegration')}</option>
                                    </select>
                                    {break}
                                {/if}
                            {/foreach}
                        {else}
                            <select class="select2 list-fields qbo-fields">
                                <option></option>
                            {foreach item=FIELD from=$CONFIG_DATA['platform_fields']}
                                {if $FIELD['non_editable'] neq '1'}
                                <option value="{$FIELD['platform_field']}" {if $MAPPED_FIELD['platform_field'] eq $FIELD['platform_field']}selected{/if}>
                                    {vtranslate($FIELD['platform_field_label'], 'PlatformIntegration')}
                                </option>
                                {/if}
                            {/foreach}
                            </select>
                        {/if}
                    </td>
                    <td>
                        {if $MAPPED_FIELD['non_editable'] neq '1'}
                            <a href="javascript: void(0);" class="btnRemoveMappingFields"><i class="fa fa-trash"></i></a>
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
            <tfoot>
                <tr class="hide row-item">
                    <td>
                        <select class="list-fields vtiger-fields">
                            <option></option>
                            {foreach item=FIELD key=FIELD_NAME from=$CONFIG_DATA['vt_fields']}
                                {if $FIELD->get('is_show_on_config') eq 1}
                                <option value="{$FIELD_NAME}">
                                    {vtranslate($FIELD->get('label'), $CONFIG_DATA['vtigerModule'])}
                                </option>
                                {/if}
                            {/foreach}
                        </select>
                    </td>
                    <td>
                        <select class="list-fields qbo-fields">
                            <option></option>
                            {foreach item=FIELD from=$CONFIG_DATA['platform_fields']}
                                {if $FIELD['non_editable'] neq '1'}
                                <option value="{$FIELD['platform_field']}">
                                    {vtranslate($FIELD['platform_field_label'], 'PlatformIntegration')}
                                </option>
                                {else}
                                    {assign var=TOTAL_QB_N_E_FIELDS value=$TOTAL_QB_N_E_FIELDS + 1}
                                {/if}
                            {/foreach}
                        </select>
                    </td>
                    <td>
                        <a href="javascript: void(0);" class="btnRemoveMappingFields" title="{vtranslate('TITLE_REMOVE_MAPPING', 'PlatformIntegration')}"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                {assign var=SHOW_BUTTON value=1}
                {if $TOTAL_QB_N_E_FIELDS eq $TOTAL_MAPPED_FIELDS}
                    {if $TOTAL_QB_FIELDS eq $TOTAL_QB_N_E_FIELDS}
                        {assign var=SHOW_BUTTON value=0}                    
                    {/if}
                {/if}
                {if $SHOW_BUTTON eq 1}
                <tr>
                    <td class="text-center">
                        <a href="javascript: void(0);" class="btn btn-default btnAddMappingFields">
                            <i class="fa fa-plus"></i>&nbsp;{vtranslate('BTN_ADD_MAPPING', 'PlatformIntegration')}
                        </a>
                    </td>
                    <td colspan="2" class="text-center">
                        <a href="javascript: void(0);" class="btn btn-success btnSaveMappingFields">{vtranslate('BTN_SAVE', 'PlatformIntegration')}</a>
                    </td>
                </tr>
                {/if}
            </tfoot>
        </table>
        {else}
            <h5>{$CONFIG_DATA['OtherInfo']['error_missing_module']}</h5>
        {/if}
    </div>
</div>