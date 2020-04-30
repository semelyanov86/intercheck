{*<!--
/* ********************************************************************************
 * The content of this file is subject to the Quoting Tool ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div class="contentsDiv marginLeftZero">
        <div class="padding1per">
            <div class="editContainer" style="padding-left: 3%; padding-right: 3%">
                <br>
                <h3>{vtranslate('VTEQBO', $QUALIFIED_MODULE)} {vtranslate('LBL_INSTALL', $QUALIFIED_MODULE)}</h3>
                <hr>
                <form class="form-horizontal">
                    <div class="row">
                        <div class="controls">
                            <div>
                                <p>
                                    {vtranslate('LBL_CURRENT_PHP_VERSION_ON_YOUR_SERVER_IS', $QUALIFIED_MODULE)}<span style="color: red; font-weight: bolder;">{$CURRENT_PHP_VERSION}</span><br />
                                    {vtranslate('LBL_PHP_REQUIREMENTS_1', $QUALIFIED_MODULE)}<span style="color: green; font-weight: bolder;">{$PHP_VERSION_REQUIRED}</span>
                                    {vtranslate('LBL_PHP_REQUIREMENTS_2', $QUALIFIED_MODULE)}
                                </p>
                            </div>
                            <br>

                            <div class="clearfix">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}