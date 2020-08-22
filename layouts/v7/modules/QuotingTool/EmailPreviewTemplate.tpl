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
    <div class="modal myModal fade in" style="display: block;" aria-hidden="false">
        <div class="modal-backdrop fade in"></div>

        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="form-horizontal" method="post" action="index.php" id="quotingtool_emailtemplate" enctype="multipart/form-data">
                    <div class="modal-header">
                        <div class="clearfix">
                            <div class="pull-right ">
                                <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                                    <span aria-hidden="true" class="fa fa-close"></span>
                                </button>
                            </div>
                            <h4 class="pull-left">{vtranslate('Preview & Send Email', $MODULE)}</h4>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="module" value="{$MODULE}"/>
                        <input type="hidden" name="action" value="PDFHandler"/>
                        <input type="hidden" name="mode" value="preview_and_send_email"/>
                        <input type="hidden" name="transaction_id" value='{$TRANSACTION_ID}'/>
                        <input type="hidden" name="record" value="{$RECORDID}"/>
                        <input type="hidden" name="template_id" value='{$TEMPLATEID}'/>
                        <input type="hidden" name="multi_record" value='{if $MULTI_RECORD eq ''}{else}{Zend_Json::encode($MULTI_RECORD)}{/if}'/>
                        <input type="hidden" id="maxUploadSize" value="{$MAX_UPLOAD_SIZE}" />
                        <input type="hidden" id="documentIds" name="documentids" value="" />
                        <input type="hidden" name="emailMode" value="" />
                        <div class="row-fluid">
                            <div class="col-md-7" style="border-right: 1px solid #A7A4A3">
                                {*MultipSMTP*}
                                <div name="multip_SMTP">
                                </div>
                                {*End MultipSMTP*}
                                {*LIST EMAIL*}
                                <div class="row-fluid">
                                    <div class="col-lg-2">
                                        <span class="pull-right" style="margin-top: 5px">To&nbsp;<span class="redColor">*</span></span>
                                    </div>
                                    <div class="col-lg-10">
                                        <div id="multiEmailContainer">
                                            {if $EMAIL_FIELD_LIST}
                                                {assign var=i value=0}
                                                {assign var=allEmailArr value=[]}
                                                {assign var=countEmail value={$EMAIL_FIELD_LIST|@count}}
                                                {foreach item=EMAIL_FIELD_LABEL key=EMAIL_FIELD_NAME from=$EMAIL_FIELD_LIST name=emailFieldIterator}
                                                    {append var=allEmailArr value=$EMAIL_FIELD_LABEL index=$i}

                                                    <div class="control-group" style="margin-left: 25px;">
                                                        <label class="checkbox">
                                                            <input type="checkbox" class="emailField" {if $countEmail eq 1}checked{/if} name="selectedEmail[{$i++}]" value='{$EMAIL_FIELD_NAME}' style="vertical-align: middle" />
                                                            <span style="padding-left: 10px">{$EMAIL_FIELD_LABEL}</span>
                                                        </label>
                                                    </div>
                                                {/foreach}
                                            {else}
                                                {vtranslate('Does not have any email to select.', $MODULE)}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {*END LIST EMAIL*}

                                {*CC, BCC*}
                                <div class="row hide ccContainer" style="margin-bottom: 5px;">
                                    <div class="col-lg-12" style="margin-top: 5px">
                                        <div class="col-lg-2" style="line-height: 30px">
                                            <span class="pull-right" >{vtranslate('LBL_CC',$MODULE)}</span>
                                        </div>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" name="ccValues" data-rule-multiEmails="true" value="" style="width: 100%"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row hide bccContainer">
                                    <div class="col-lg-12">
                                        <div class="col-lg-2" style="line-height: 30px">
                                            <span class="pull-right">{vtranslate('LBL_BCC',$MODULE)}</span>
                                        </div>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" name="bccValues" data-rule-multiEmails="true" value="" style="width: 100%"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12" style="margin-top: 5px">
                                        <div class="col-lg-2">
                                        </div>
                                        <div class="col-lg-6">
                                            <a href="#" class="cursorPointer" id="ccLink">{vtranslate('LBL_ADD_CC', $MODULE)}</a>&nbsp;&nbsp;
                                            <a href="#" class="cursorPointer" id="bccLink">{vtranslate('LBL_ADD_BCC', $MODULE)}</a>
                                        </div>
                                        <div class="col-lg-4"></div>
                                    </div>
                                </div>
                                {*END CC, BCC*}

                                {*Subject*}
                                <div class="row-fluid" style="margin-top: 10px">
                                    <div class="col-lg-2" style="line-height: 30px">
                                        <span class="pull-right">Subject&nbsp;<span class="redColor">*</span></span>
                                    </div>
                                    <div class="col-lg-10">
                                        <input type="text" name="email_subject" class="input-large form-control" id="email_subject"
                                               placeholder="Email Subject" value="{$EMAIL_SUBJECT}"
                                               style=""/>
                                    </div>
                                </div>
                                {*EndSubject*}
                            </div>

                            <div class="col-md-5">
                                <div class="row-fluid" name="multiple_signature">
                                    <div class="col-sm-12 marginBottom10px" style="padding: 0px 0px 10px 0px; border-bottom: 1px solid #A7A4A3;{if $HAS_SECONDARY_SIGNATURE neq 1}display: none;{/if}" >
                                        <div class="btn-group" role="group" aria-label="Signature" style="width: 100%">
                                            <button type="button" class="btn btn-primary btnChangeSignature" data-sign-to="PRIMARY" style="width: 50%">{vtranslate('Primary Signature', $MODULE)}</button>
                                            <button type="button" class="btn btn-default btnChangeSignature" data-sign-to="SECONDARY" style="width: 50%">{vtranslate('Secondary Signature', $MODULE)}</button>
                                        </div>
                                    </div>
                                </div>
                                {*Attach document*}
                                <div class="row-fluid">
                                    <div class="col-sm-12 marginBottom10px" style=" {if $HAS_SECONDARY_SIGNATURE eq 1}padding-top: 10px;{/if} padding-left: 0">
                                        <div class="col-sm-10" style="padding: 0" >
                                            <label class="check_attach_file text-left" style="display: block; float: left">
                                                <input style="top: 2px;margin-top: 0px;" type="checkbox" {if $CHECK_ATTACH_FILE eq 'true'}checked{/if} name="check_attach_file" />
                                                <span style="padding-left: 10px">{vtranslate('EMAIL_ATTACH_DOCUMENT', $MODULE)}</span>
                                            </label>
                                            <a href="{$CUSTOM_PROPOSAL_LINK}" target="_blank" style="margin-left: 5px; line-height: 22px; color: #15c;font-style: italic;">   ({vtranslate('EMAIL_DOCUMENT_PREVIEW2', $MODULE)})</a>
                                        </div>
                                        <div class="col-sm-2"></div>
                                    </div>
                                </div>
                                {*End Attach document*}

                                {*SIGNATURE*}
                                <div class="row-fluid">
                                    <div class="col-lg-12" style="border-bottom: 1px solid #A7A4A3; padding-bottom: 10px; padding-left: 0">
                                        <div class="col-lg-10" style="padding: 0">
                                            <input style="top: 2px;margin-top: 0px;" class="" type="checkbox" name="signature" value="Yes" checked="checked" id="signature">
                                            <span style="padding-left: 10px">{vtranslate('LBL_INCLUDE_SIGNATURE',$MODULE)}</span>
                                        </div>
                                        <div class="item col-lg-2">
                                        </div>
                                    </div>
                                </div>
                                {*END SIGNATURE*}

                                {*MultiFile*}
                                <div class="row-fluid">
                                    <div class="col-lg-12 marginTop10px" style="padding-left: 0">
                                        <div class="row">
                                            <div class="col-lg-8 browse">
                                                <input type="file" {if $FILE_ATTACHED}class="removeNoFileChosen"{/if} id="multiFile" name="file[]"/>&nbsp;
                                            </div>
                                            <div class="col-lg-4 brownseInCrm">
                                                <button type="button" class="btn btn-small btn-default" id="browseCrm" data-url="{$DOCUMENTS_URL}" title="{vtranslate('LBL_BROWSE_CRM',$MODULE)}">{vtranslate('LBL_BROWSE_CRM',$MODULE)}</button>
                                            </div>
                                        </div>
                                        <div id="attachments">
                                            {foreach item=ATTACHMENT from=$ATTACHMENTS}
                                                {if ('docid'|array_key_exists:$ATTACHMENT)}
                                                    {assign var=DOCUMENT_ID value=$ATTACHMENT['docid']}
                                                    {assign var=FILE_TYPE value="document"}
                                                {else}
                                                    {assign var=FILE_TYPE value="file"}
                                                {/if}
                                                <div class="MultiFile-label customAttachment" data-file-id="{$ATTACHMENT['fileid']}" data-file-type="{$FILE_TYPE}"  data-file-size="{$ATTACHMENT['size']}" {if $FILE_TYPE eq "document"} data-document-id="{$DOCUMENT_ID}"{/if}>
                                                    {if $ATTACHMENT['nondeletable'] neq true}
                                                        <a name="removeAttachment" class="cursorPointer">x </a>
                                                    {/if}
                                                    <span>{$ATTACHMENT['attachment']}</span>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                                {*END MULTI FILE*}

                            </div>
                        </div>
                        <div class="clearfix" ></div>
                        <div class="row-fluid" style="margin: 5px; margin-top: 10px">
                            <div class="span12">
                                <textarea placeholder="Email Content" id="email_content" name="email_content" rows="5">{$EMAIL_CONTENT}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="attachments" value='{ZEND_JSON::encode($ATTACHMENTS)}' />
                    </div>
                    <div class="modal-footer">
                        <div class="pull-left custom_proposal_link">
                            <a href="{$CUSTOM_PROPOSAL_LINK}" target="_blank">{vtranslate('EMAIL_DOCUMENT_PREVIEW', $MODULE)}</a>
                        </div>
                        <div class="pull-right cancelLinkContainer" style="margin-top:0;">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        {if $CHILD_MODULE eq 'Potentials' OR $CHILD_MODULE eq 'Quotes'}
                            {if $RELATED_CHILD_MODULE_TEMPLATE && $CHILD_MODULE neq 'Quotes' }
                                Template ID : <select style="width: 150px;text-align: left" class="select2" name="quotes-template">
                                    <option value="">Select an Option</option>
                                    {foreach from=$RELATED_CHILD_MODULE_TEMPLATE key=TEMPLATE_KEY item=RELATED_TEMPLATE}
                                        <option createnewrecords="{$RELATED_TEMPLATE.createnewrecords}" value="{$RELATED_TEMPLATE.id}">{$RELATED_TEMPLATE.filename}</option>
                                    {/foreach}
                                </select>
                            {/if}
                            <a href="#" class="selectRelation" style="text-decoration: underline !important; margin-right: 10px">{vtranslate('LBL_SELECT_QUOTES', $MODULE)}</a>
                        {/if}
                        <button class="btn btn-success addButton" type="submit" name="saveButton">
                            <strong>{vtranslate('LBL_SEND', $MODULE)}</strong>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}