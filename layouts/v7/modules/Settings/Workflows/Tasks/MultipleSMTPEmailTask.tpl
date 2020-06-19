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
	<div id="VtEmailTaskContainer">
		<div class="row">
			<div class="col-sm-12 col-xs-12">
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9"><input data-validation-engine='validate[]' name="fromEmail" class="fields inputElement" type="text" value="{$TASK_OBJECT->fromEmail}" /></div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select id="fromEmailOption" style="min-width: 250px" class="select2 select2-offscreen" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$FROM_EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
							<div class="col-sm-9 col-xs-9"><input data-validation-engine='validate[required]' name="recepient" class="fields inputElement" type="text" value="{$TASK_OBJECT->recepient}" /></div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2 select2-offscreen" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9"><input class="fields inputElement" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" /></div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select class="select2 select2-offscreen" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-9 col-xs-9"><input class="fields inputElement" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" /></div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select class="select2 select2-offscreen" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 250px">
							<option></option>
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
				<div class="row form-group {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
					<div class="col-sm-8 col-xs-8">
						<div class="row">
							<div class="col-sm-3 col-xs-3"></div>
							<div class="col-sm-9 col-xs-9">
								<a class="cursorPointer {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
								<a class="cursorPointer {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
							</div>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div class="col-sm-3 col-xs-3">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
							<div class="col-sm-9 col-xs-9">
								<input data-rule-required="true" data-validation-engine='validate[required]' name="subject" class="fields inputElement" type="text" name="subject" value="{$TASK_OBJECT->subject}" id="subject" spellcheck="true"/>
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<select style="min-width: 250px" class="task-fields select2 select2-offscreen" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$ALL_FIELD_OPTIONS}
						</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-6 col-xs-6">
						<div class="row">
							<div style="margin-top: 7px" class="col-sm-3 col-xs-3">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-8 col-xs-8">
								<select style="min-width: 250px" id="task-fieldnames" class="select2 select2-offscreen" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
									<option></option>
									{$ALL_FIELD_OPTIONS}
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-5 col-xs-5">
						<div class="row">
							<div style="margin-top: 7px" class="col-sm-3 col-xs-3">{vtranslate('LBL_ADD_TIME',$QUALIFIED_MODULE)}</div>
							<div class="col-sm-8 col-xs-8">
								<select style="width: 250px" id="task_timefields" class="select2 select2-offscreen" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
									<option></option>
									{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
										<option value="${$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-12 col-xs-12">
						<textarea id="content" name="content">{$TASK_OBJECT->content}</textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		/**
		 * Fn - registerMultipleSMTPEmailTaskEvents
		 */
		Settings_Workflows_Edit_Js.prototype.registerMultipleSMTPEmailTaskEvents = function () {
			var textAreaElement = jQuery('#content');
			var ckEditorInstance = this.getckEditorInstance();
			ckEditorInstance.loadCkEditor(textAreaElement);
			this.registerFillMailContentEvent();
			this.registerFillTaskFromEmailFieldEvent();
			this.registerCcAndBccEvents();
		};

		/**
		 * Fn - preSaveMultipleSMTPMailTask
		 * @param tasktype
		 */
		Settings_Workflows_Edit_Js.prototype.preSaveMultipleSMTPEmailTask = function (tasktype) {
			var textAreaElement = jQuery('#content');
			textAreaElement.val(CKEDITOR.instances['content'].getData());
		};

	</script>
{/strip}