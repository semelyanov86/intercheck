{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<table class="table table-bordered table-condensed themeTableColor">
    <thead>
        <tr class="blockHeader">
            <th class="mediumWidthType">
                <span class="alignMiddle">{vtranslate('LBL_COMPANY_LICENSE_INFO', 'ITS4YouReports')}</span>
            </th>
            <th class="mediumWidthType" style="border-left: none; text-align: right;">
                <button type="button" id="order_button" class="btn btn-info" onclick="window.location.href='index.php?module=Vtiger&parent=Settings&view=CompanyDetails&block=3&fieldid=14'"/>{vtranslate('LBL_CHANGE_COMPANY_INFORMATION','ITS4YouReports')}</button>
            </th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('organizationname', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="organizationname_label">{$ORGANIZATION->get("organizationname")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('address', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="address_label">{$ORGANIZATION->get("address")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('city', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="city_label">{$ORGANIZATION->get("city")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('state', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="state_label">{$ORGANIZATION->get("state")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('country', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="country_label">{$ORGANIZATION->get("country")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('code', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="code_label">{$ORGANIZATION->get("code")}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('vatid', 'Settings:Vtiger')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="vatid_label">{$ORGANIZATION->get("vatid")}</div>
                </td>
            </tr>
     </tbody>
</table>
<br />
<table class="table table-bordered table-condensed themeTableColor">
    <thead>
            <tr class="blockHeader">
                    <th colspan="2" class="mediumWidthType">
                            <span class="alignMiddle">{vtranslate('LBL_LICENSE', 'ITS4YouReports')}</span>
                    </th>
            </tr>
    </thead>
    <tbody>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('LBL_URL', 'ITS4YouReports')}:</label></td>
                <td style="border-left: none;">
                    <div class="pull-left" id="vatid_label">{$URL}</div>
                </td>
            </tr>
            <tr>
                <td width="25%"><label  class="muted pull-right marginRight10px">{vtranslate('LBL_LICENSE_KEY','ITS4YouReports')}:</label></td>
                <td style="border-left: none;">
                  {if $STEP }
                    <input type="text" class="input-xlarge" id="licensekey" name="licensekey" data-validation-engine="validate[required]" value="{$LICENSE}"/>
                  {else}
                    <div class="pull-left" name="licensekey" id="license_key_label">{$LICENSE}</div>
                  {/if}
                </td>
            </tr>
     </tbody>
</table>