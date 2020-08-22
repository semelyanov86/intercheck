{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div id="VtEmailTaskContainer">
        <div class="contents tabbable ui-sortable">
            <ul class="nav nav-tabs layoutTabs massEditTabs">
                <li class="active">
                    <a data-toggle="tab" href="#detailViewLayout" id="detailViewLayoutBtn"><strong>{vtranslate('LBL_EMAIL_DETAILS','EMAILMaker')}</strong></a>
                </li>
                <li class="relatedListTab">
                    <a data-toggle="tab" href="#relatedTabTemplate" class="workflowTab"><strong>{vtranslate('LBL_EMAIL_CONTENT','EMAILMaker')}</strong></a>
                </li>
            </ul>
            <div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
                <div class="tab-pane active" id="detailViewLayout">

                    <div class="row form-group" >
                        <div class="col-sm-6 col-xs-6">
                            <div class="row">
                                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-9 col-xs-9">
                                    <input name="fromEmail" class=" fields inputElement" type="text" value="{$TASK_OBJECT->fromEmail}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-5">
                            <select id="fromEmailOption" style="min-width: 250px" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$FROM_EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-6 col-xs-6">
                            <div class="row">
                                <div class="col-sm-3 col-xs-3">{vtranslate('Reply To',$QUALIFIED_MODULE)}</div>
                                <div class="col-sm-9 col-xs-9">
                                    <input name="replyTo" class="fields inputElement" type="text" value="{$TASK_OBJECT->replyTo}"/>
                                </div>
                            </div>
                        </div>
                        <span class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2 overwriteSelection" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
                            {$EMAIL_FIELD_OPTION}
						</select>
					</span>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-6 col-xs-6">
                            <div class="row">
                                <span class="col-sm-3 col-xs-3">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
                                <div class="col-sm-9 col-xs-9">
                                    <input data-rule-required="true" name="recepient" class="fields inputElement" type="text" value="{$TASK_OBJECT->recepient}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-5">
                            <select style="min-width: 250px" class="task-fields select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
                        <div class="col-sm-6 col-xs-6">
                            <div class="row">
                                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
                                <div class="col-sm-9 col-xs-9">
                                    <input class="fields inputElement" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" />
                                </div>
                            </div>
                        </div>
                        <span class="col-sm-5 col-xs-5">
						<select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
							<option></option>
                            {$EMAIL_FIELD_OPTION}
						</select>
					</span>
                    </div>
                    <div class="row form-group {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
                        <div class="col-sm-6 col-xs-6">
                            <div class="row">
                                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
                                <div class="col-sm-9 col-xs-9">
                                    <input class="fields inputElement" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-5">
                            <select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
                                <option></option>
                                {$EMAIL_FIELD_OPTION}
                            </select>
                        </div>
                    </div>
                    <div class="row form-group {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
                        <div class="col-sm-8 col-xs-8">
                            <div class="row">
                                <div class="col-sm-3 col-xs-3">&nbsp;</div>
                                <div class="col-sm-9 col-xs-9">
                                    <a class="cursorPointer {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
                                    <a class="cursorPointer {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {assign var=MODULE_FIELDS value=$TASK_OBJECT->getModuleFields($SOURCE_MODULE)}
                    {if $MODULE_FIELDS}
                        <div class="row form-group" id="templateFieldsContainer">
                            <div class="col-sm-6 col-xs-6">
                                <div class="row">
                                    <div class="col-sm-3 col-xs-3">{vtranslate('LBL_EMAIL_CONTENT','EMAILMaker')}</div>
                                    <div class="col-sm-9 col-xs-9">
                                        <select id="template_field" name="template_field" class="inputElement span7 select2">
                                            {html_options  options=$MODULE_FIELDS selected=$TASK_OBJECT->template_field}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5 col-xs-5">
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="tab-pane" id="relatedTabTemplate">
                    <div class="row form-group">
                        <div class="col-sm-3 col-xs-3">{vtranslate('LBL_EMAIL_TEMPLATE','EMAILMaker')}</div>&nbsp;&nbsp;
                        <div class="col-sm-9 col-xs-9">
                            <select id="task_template" name="template" class="span7 chzn-select inputElement select2">
                                {html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
                            </select>
                            <input type="hidden" id="task_folder_value" value="{$TASK_OBJECT->template}">
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-3 col-xs-3">{vtranslate('LBL_EMAIL_LANGUAGE','EMAILMaker')}</div>&nbsp;&nbsp;
                        <div class="col-sm-9 col-xs-9">
                            {assign var=LANGUAGES_ARRAY value=$TASK_OBJECT->getLanguages()}
                            <select style="min-width: 215px" id="task_template_language" name="template_language" class="inputElement select2 chzn-select">
                                {html_options  options=$LANGUAGES_ARRAY selected=$TASK_OBJECT->template_language}
                            </select>
                            <input type="hidden" id="template_language_value" value="{$TASK_OBJECT->template_language}">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {*<script src="layouts/vlayout/modules/Vtiger/resources/CkEditor.js" type="text/javascript" charset="utf-8"></script>*}
    <script src="modules/EMAILMaker/workflow/VTEMAILMakerMailTask.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
		Settings_Workflows_Edit_Js.prototype.registerVTEMAILMakerMailTaskEvents = function () {
			var textAreaElement = jQuery('#content');
			this.registerFillTaskFromEmailFieldEvent();
			this.registerCcAndBccEvents();
		};

		Settings_Workflows_Edit_Js.prototype.VTEMAILMakerMailTaskCustomValidation = function () {
			var result = true;

			var selectElement1 = jQuery('input[name="recepient"]');
			var control1 = selectElement1.val();

			if(control1 == "") {
				jQuery('#detailViewLayoutBtn').trigger('click');
				var result = app.vtranslate('JS_REQUIRED_FIELD');
			}

			return result;
		};
    </script>
{/strip}	