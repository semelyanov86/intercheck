{*<!--
/* ********************************************************************************
* The content of this file is subject to the Multiple SMTP ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */
-->*}

{strip}
    <div class="modal-dialog createFieldModal modelContainer ">
        <div class="modal-content">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right ">
                        <button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <h4 class="pull-left">{vtranslate('LBL_OUTGOING_SERVER', 'Settings:Vtiger')}</h4>
                </div>
                <div class="clearfix">{vtranslate('LBL_OUTGOING_SERVER_DESC', 'Settings:Vtiger')}</div >
            </div>
            <form class="form-horizontal" action="index.php" id="outgoingMassEditContainer">
                <input type="hidden" name="id" value="{$SERVER_INFO['id']}" />
                <input type="hidden" name="userid" value="{$USERID}" />
                <input type="hidden" name="sequence" value="{$SEQUENCE}" />
                <div class="row-fluid hide errorMessage">
                    <div class="alert alert-error">
                        {vtranslate('LBL_TESTMAILSTATUS', 'Settings:Vtiger')}<strong>{vtranslate('LBL_MAILSENDERROR', 'Settings:Vtiger')}</strong>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_SERVER_NAME', 'Settings:Vtiger')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="text" class="inputElement col-sm-9" style="width: 75%" name="server" value="{$SERVER_INFO['server']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_USER_NAME', 'Settings:Vtiger')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="text" class="inputElement col-sm-9" style="width: 75%" name="server_username" value="{$SERVER_INFO['server_username']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_PASSWORD', 'Settings:Vtiger')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="password" class="inputElement col-sm-9" style="width: 75%" name="server_password" value="{$SERVER_INFO['server_password']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_FROM_EMAIL', 'Settings:Vtiger')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="text" class="inputElement col-sm-9" style="width: 75%" name="from_email_field" value="{$SERVER_INFO['from_email_field']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_REPLYTO_EMAIL', 'MultipleSMTP')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="text" class="inputElement col-sm-9" style="width: 75%" name="replyto_email_field" value="{$SERVER_INFO['replyto_email_field']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_NAME', 'MultipleSMTP')}</strong>
                        </label>
                        <div class="controls col-sm-7">
                            <input type="text" class="inputElement col-sm-9" style="width: 75%" name="name" value="{$SERVER_INFO['name']}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_REQUIRES_AUTHENTICATION', 'Settings:Vtiger')}</strong>
                        </label>
                        <div class="controls col-sm-7" style="padding-top: 5px;">
                            <input type="checkbox" name="smtp_auth" {if $SERVER_INFO['smtp_auth']}checked{/if}/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">
                            &nbsp;<strong>{vtranslate('LBL_SAVE_TO_SEND_FOLDER', 'MultipleSMTP')}</strong>
                        </label>
                        <div class="controls col-sm-7" style="padding-top: 5px;">
                            {if $SERVER_INFO['id'] ==''}
                                <input type="checkbox" class="span3" name="send_folder" value="1" />
                            {else}
                                <input type="checkbox" class="span3" name="send_folder" value="1" {if $SERVER_INFO['send_folder']}{/if}/>
                            {/if}
                            &nbsp;<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Select this option if you want save your email to the SENT folder."></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-right cancelLinkContainer" style="margin-top:0px;">
                        <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                </div>
            </form>
        </div>
    </div>
{/strip}