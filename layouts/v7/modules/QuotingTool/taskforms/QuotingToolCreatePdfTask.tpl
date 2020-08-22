{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div class="row form-group">
        <div class="col-sm-6 col-xs-6">
            <div class="row">
                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_DOC_TITLE','QuotingTool')}<span class="redColor">*</span></div>
                <div class="col-sm-9 col-xs-9"><input type="text" name="title" value="{$TASK_OBJECT->title}" id="task_title" class="inputElement"></div>
            </div>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-6 col-xs-6">
            <div class="row">
                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_DOC_DESC','QuotingTool')}</div>
                <div class="col-sm-9 col-xs-9"><textarea name="description" class="inputElement">{$TASK_OBJECT->description}</textarea></div>
            </div>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-sm-6 col-xs-6">
            <div class="row">
                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_FLD_NAME','QuotingTool')}</div>
                <div class="col-sm-9 col-xs-9"><select id="task_folder" style="width: 205px" name="folder" class="select2">
                        {html_options  options=$TASK_OBJECT->getFolders() selected=$TASK_OBJECT->folder}
                    </select>
                    <input type="hidden" id="task_folder_value" value="{$TASK_OBJECT->folder}"></div>
            </div>
        </div>
    </div>


    <div class="row form-group">
        <div class="col-sm-6 col-xs-6">
            <div class="row">
                <div class="col-sm-3 col-xs-3">{vtranslate('LBL_PDF_TEMPLATE','QuotingTool')}</div>
                <div class="col-sm-9 col-xs-9"><select id="task_template" name="template" class="select2" style="width: 205px">
                        {html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
                    </select>
                    <input type="hidden" id="task_template_value" value="{$TASK_OBJECT->template}"></div>
            </div>
        </div>
    </div>

{/strip}