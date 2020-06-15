{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
<div class="contents tabbable ui-sortable">
    <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="module" value="EMAILMaker">
        <input type="hidden" name="parenttab" value="{$PARENTTAB}">
        <input type="hidden" name="templateid" id="templateid" value="{$SAVETEMPLATEID}">
        <input type="hidden" name="action" value="SaveEMAILTemplate">
        <input type="hidden" name="redirect" value="true">
        <input type="hidden" name="return_module" value="{$smarty.request.return_module}">
        <input type="hidden" name="return_view" value="{$smarty.request.return_view}">
        <input type="hidden" name="is_theme" value="{if $THEME_MODE eq "true"}1{else}0{/if}">
        <input type="hidden" name="selectedTab" id="selectedTab" value="properties">
        <input type="hidden" name="selectedTab2" id="selectedTab2" value="body">
        <ul class="nav nav-tabs layoutTabs massEditTabs">
            <li class="detailviewTab active">
                <a data-toggle="tab" href="#pdfContentEdit" aria-expanded="true"><strong>{vtranslate('LBL_PROPERTIES_TAB',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentOther" aria-expanded="false"><strong>{vtranslate('LBL_OTHER_INFO',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentLabels" aria-expanded="false"><strong>{vtranslate('LBL_LABELS',$MODULE)}</strong></a>
            </li>
            {if $THEME_MODE neq "true"}
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#pdfContentProducts" aria-expanded="false"><strong>{vtranslate('LBL_ARTICLE',$MODULE)}</strong></a>
                </li>
            {/if}
            <li class="detailviewTab">
                <a data-toggle="tab" href="#editTabSettings" aria-expanded="false"><strong>{vtranslate('LBL_SETTINGS_TAB',$MODULE)}</strong></a>
            </li>
            {if $THEME_MODE neq "true"}
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#editTabSharing" aria-expanded="false"><strong>{vtranslate('LBL_SHARING_TAB',$MODULE)}</strong></a>
                </li>
            {/if}
        </ul>
        <div >
            {********************************************* Settings DIV *************************************************}
            <div>
                <div class="row" >
                    <div class="left-block col-xs-4">
                        <div>
                            <div class="tab-content layoutContent themeTableColor overflowVisible">
                                <div class="tab-pane active" id="pdfContentEdit">
                                    <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        {********************************************* PROPERTIES DIV*************************************************}
                                        <br />
                                        <div id="properties_div">
                                            {* pdf module name *}

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {if $THEME_MODE eq "true"}{vtranslate('LBL_THEME_NAME',$MODULE)}{else}{vtranslate('LBL_EMAIL_NAME',$MODULE)}{/if}:&nbsp;<span class="redColor">*</span>
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <input name="templatename" id="templatename" type="text" value="{$TEMPLATENAME}" data-rule-required="true" class="inputElement nameField" tabindex="1">
                                                </div>
                                            </div>
                                            {* EMAIL source module and its available fields *}

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_RECIPIENT_FIELDS','EMAILMaker')}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <select name="r_modulename" id="r_modulename" class="select2 form-control">
                                                        {html_options  options=$RECIPIENTMODULENAMES}
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="recipientmodulefields" id="recipientmodulefields" class="select2 form-control">
                                                            <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="recipientmodulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="recipientmodulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {* pdf source module and its available fields *}
                                            {if $THEME_MODE neq "true"}

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_MODULENAMES',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <select name="modulename" id="modulename" class="select2 form-control">
                                                            {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                                                                {html_options  options=$MODULENAMES selected=$SELECTMODULE}
                                                            {else}
                                                                {html_options  options=$MODULENAMES}
                                                            {/if}
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">

                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="modulefields" id="modulefields" class="select2 form-control">
                                                                {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                                                                    <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                                                {else}
                                                                    {html_options  options=$SELECT_MODULE_FIELD}
                                                                {/if}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* related modules and its fields *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        <label class="muted pull-right">{vtranslate('LBL_RELATED_MODULES',$MODULE)}:</label>
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <select name="relatedmodulesorce" id="relatedmodulesorce" class="select2 form-control">
                                                            <option value="">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</option>
                                                            {foreach item=RelMod from=$RELATED_MODULES}
                                                                <option value="{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="relatedmodulefields" id="relatedmodulefields" class="select2 form-control">
                                                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* related bloc tpl *}

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_RELATED_BLOCK_TPL',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="related_block" id="related_block" class="select2 form-control" >
                                                                {html_options options=$RELATED_BLOCKS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success marginLeftZero" onclick="EMAILMaker_EditJs.InsertRelatedBlock();" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn addButton marginLeftZero" onclick="EMAILMaker_EditJs.CreateRelatedBlock();" title="{vtranslate('LBL_CREATE')}"><i class="fa fa-plus"></i></button>
                                                                <button type="button" class="btn marginLeftZero" onclick="EMAILMaker_EditJs.EditRelatedBlock();" title="{vtranslate('LBL_EDIT')}"><i class="fa fa-edit"></i></button>
                                                                <button type="button" class="btn btn-danger marginLeftZero" class="crmButton small delete" onclick="EMAILMaker_EditJs.DeleteRelatedBlock();" title="{vtranslate('LBL_DELETE')}"><i class="fa fa-trash"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_COMPANY_INFO',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="acc_info" id="acc_info" class="select2 form-control">
                                                            {html_options  options=$ACCOUNTINFORMATIONS}
                                                        </select>
                                                        <div id="acc_info_div" class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_SELECT_USER_INFO',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <select name="acc_info_type" id="acc_info_type" class="select2 form-control" onChange="EMAILMaker_EditJs.change_acc_info(this)">
                                                        {html_options  options=$CUI_BLOCKS}
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                </label>
                                                <div class="controls col-sm-9">

                                                    <div id="user_info_div" class="au_info_div">
                                                        <div class="input-group">
                                                            <select name="user_info" id="user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['s']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="logged_user_info" id="logged_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['l']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="modifiedby_user_info" id="modifiedby_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['m']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="smcreator_user_info_div" class="au_info_div" style="display:none;">
                                                        <div class="input-group">
                                                            <select name="smcreator_user_info" id="smcreator_user_info" class="select2 form-control">
                                                                {html_options  options=$USERINFORMATIONS['c']}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            {if $MULTICOMPANYINFORMATIONS neq ''}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {$LBL_MULTICOMPANY}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="multicomapny" id="multicomapny" class="select2 form-control">
                                                                {html_options  options=$MULTICOMPANYINFORMATIONS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="multicomapny" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pdfContentOther">
                                    <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        <br />
                                        {********************************************* Company and User information DIV *************************************************}
                                        <div class="form-group" id="listview_block_tpl_row">
                                            {if $THEME_MODE neq "true"}

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        <input type="checkbox" name="is_listview" id="isListViewTmpl" {if $IS_LISTVIEW_CHECKED eq "yes"}checked="checked"{/if} onclick="EMAILMaker_EditJs.isLvTmplClicked();" title="{vtranslate('LBL_LISTVIEW_TEMPLATE',$MODULE)}" />&nbsp;{vtranslate('LBL_LISTVIEWBLOCK',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="listviewblocktpl" id="listviewblocktpl" class="select2 form-control" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}>
                                                                {html_options  options=$LISTVIEW_BLOCK_TPL}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" id="listviewblocktpl_butt" class="btn btn-success InsertIntoTemplate" data-type="listviewblocktpl" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('TERMS_AND_CONDITIONS',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="invterandcon" id="invterandcon" class="select2 form-control">
                                                            {html_options  options=$INVENTORYTERMSANDCONDITIONS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="invterandcon" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_CURRENT_DATE',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="dateval" id="dateval" class="select2 form-control">
                                                            {html_options  options=$DATE_VARS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="dateval" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {************************************ Custom Functions *******************************************}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('CUSTOM_FUNCTIONS',$MODULE)}: <select name="custom_function_type" id="custom_function_type" class="select2">
                                                        <option value="before">{vtranslate('LBL_BEFORE','EMAILMaker')}</option>
                                                        <option value="after">{vtranslate('LBL_AFTER','EMAILMaker')}</option>
                                                    </select>
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="customfunction" id="customfunction" class="select2 form-control">
                                                            {html_options options=$CUSTOM_FUNCTIONS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="customfunction" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pdfContentLabels">
                                    <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                        <br>
                                        {********************************************* Labels *************************************************}
                                        <div id="labels_div">


                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_GLOBAL_LANG',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="global_lang" id="global_lang" class="select2 form-control">
                                                            {html_options  options=$GLOBAL_LANG_LABELS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="global_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {if $THEME_MODE neq "true"}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_MODULE_LANG',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <div class="input-group">
                                                            <select name="module_lang" id="module_lang" class="select2 form-control">
                                                                {html_options  options=$MODULE_LANG_LABELS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="module_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_CUSTOM_LABELS',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <div class="input-group">
                                                        <select name="custom_lang" id="custom_lang" class="select2 form-control">
                                                            {html_options  options=$CUSTOM_LANG_LABELS}
                                                        </select>
                                                        <div class="input-group-btn">
                                                            <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="custom_lang" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {if $THEME_MODE neq "true"}
                                    <div class="tab-pane" id="pdfContentProducts">
                                        <div class="edit-template-content col-lg-4" style="position:fixed;z-index:1000;">
                                            <br>
                                            {*********************************************Products bloc DIV*************************************************}
                                            <div id="products_div">

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        {vtranslate('LBL_PRODUCT_BLOC_TPL',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <div class="input-group">
                                                            <select name="productbloctpl2" id="productbloctpl2" class="select2 form-control">
                                                                {html_options  options=$PRODUCT_BLOC_TPL}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productbloctpl2" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* product bloc tpl which is the same as in main Properties tab*}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        {vtranslate('LBL_ARTICLE',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <div class="input-group">
                                                            <select name="articelvar" id="articelvar" class="select2 form-control">
                                                                {html_options  options=$ARTICLE_STRINGS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="articelvar" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {* insert products & services fields into text *}

                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        *{vtranslate('LBL_PRODUCTS_AVLBL',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <div class="input-group">
                                                            <select name="psfields" id="psfields" class="select2 form-control">
                                                                {html_options  options=$SELECT_PRODUCT_FIELD}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="psfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* products fields *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        *{vtranslate('LBL_PRODUCTS_FIELDS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <div class="input-group">
                                                            <select name="productfields" id="productfields" class="select2 form-control">
                                                                {html_options  options=$PRODUCTS_FIELDS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="productfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {* services fields *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-4" style="font-weight: normal">
                                                        *{vtranslate('LBL_SERVICES_FIELDS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-8">
                                                        <div class="input-group">
                                                            <select name="servicesfields" id="servicesfields" class="select2 form-control">
                                                                {html_options  options=$SERVICES_FIELDS}
                                                            </select>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="servicesfields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <label class="muted"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO',$MODULE)}</small></label>
                                                </br>
                                            </div>
                                        </div>
                                    </div>
                                {/if}

                                    <div class="tab-pane" id="editTabSettings">
                                        <br>
                                        <div id="settings_div">

                                            <div class="form-group">
                                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                    {vtranslate('LBL_DESCRIPTION',$MODULE)}:
                                                </label>
                                                <div class="controls col-sm-9">
                                                    <input name="description" type="text" value="{$EMAIL_TEMPLATE_RESULT.description}" class="inputElement" tabindex="2">
                                                </div>
                                            </div>
                                            {if $THEME_MODE neq "true"}
                                            {* email category setting *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('Category')}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <input type="text" name="email_category" value="{$EMAIL_CATEGORY}" class="inputElement"/>
                                                    </div>
                                                </div>
                                            {* default from setting *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_DEFAULT_FROM','EMAILMaker')}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <select name="default_from_email" class="select2 form-control">
                                                            {html_options  options=$DEFAULT_FROM_OPTIONS selected=$SELECTED_DEFAULT_FROM}
                                                        </select>
                                                    </div>
                                                </div>
                                                {* ignored picklist values settings *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_IGNORE_PICKLIST_VALUES',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="inputElement"/>
                                                    </div>
                                                </div>
                                                {* status settings *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_STATUS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <select name="is_active" id="is_active" class="select2 form-control" onchange="EMAILMaker_EditJs.templateActiveChanged(this);">
                                                            {html_options options=$STATUS selected=$IS_ACTIVE}
                                                        </select>
                                                    </div>
                                                </div>
                                                {* decimal settings *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_DECIMALS',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <td align="right" nowrap>{vtranslate('LBL_DEC_POINT',$MODULE)}</td>
                                                                <td><input type="text" maxlength="2" name="dec_point" class="inputElement" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS',$MODULE)}</td>
                                                                <td><input type="text" maxlength="2" name="dec_decimals" class="inputElement" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS',$MODULE)}</td>
                                                                <td><input type="text" maxlength="2" name="dec_thousands" class="inputElement" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                                  {* is default settings *}
                                                <div class="form-group">
                                                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                        {vtranslate('LBL_SETASDEFAULT',$MODULE)}:
                                                    </label>
                                                    <div class="controls col-sm-9">
                                                        {vtranslate('LBL_FOR_DV',$MODULE)}&nbsp;&nbsp;<input type="checkbox" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
                                                        &nbsp;&nbsp;
                                                        {vtranslate('LBL_FOR_LV',$MODULE)}&nbsp;&nbsp;<input type="checkbox" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
                                                        {* hidden variable for template order settings *}
                                                        <input type="hidden" name="tmpl_order" value="{$ORDER}" />
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                </div>
                                {********************************************* Sharing DIV *************************************************}
                                <div class="tab-pane" id="editTabSharing">
                                    <br>
                                    <div id="sharing_div">

                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_TEMPLATE_OWNER',$MODULE)}
                                            </label>
                                            <div class="controls col-sm-9">
                                                <select name="template_owner" id="template_owner" class="select2 form-control">
                                                    {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                                {vtranslate('LBL_SHARING_TAB',$MODULE)}:
                                            </label>
                                            <div class="controls col-sm-9">
                                                <select name="sharing" id="sharing" data-toogle-members="true" class="select2 form-control">
                                                    {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
                                                </select><br><br>
                                                <select id="memberList" class="select2 form-control members op0{if $SHARINGTYPE eq "share"} fadeInx{/if}" multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $MODULE)}" style="margin-bottom: 10px;" data-rule-required="{if $SHARINGTYPE eq "share"}true{else}false{/if}">

                                                    {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                                        {assign var=TRANS_GROUP_LABEL value=$GROUP_LABEL}
                                                        {if $GROUP_LABEL eq 'RoleAndSubordinates'}
                                                            {assign var=TRANS_GROUP_LABEL value='LBL_ROLEANDSUBORDINATE'}
                                                        {/if}
                                                        {assign var=TRANS_GROUP_LABEL value={vtranslate($TRANS_GROUP_LABEL)}}
                                                        <optgroup label="{$TRANS_GROUP_LABEL}">
                                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                                <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($SELECTED_MEMBERS_GROUP[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{$MEMBER->getName()}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   {************************************** END OF TABS BLOCK *************************************}

                    <div class="middle-block col-xs-8">
                        {if $THEME_MODE neq "true"}
                            {* email subject *}
                            <div class="row">
                                <table class="table no-border">
                                    <tbody id="properties_div">
                                    {* pdf module name *}
                                    <tr>
                                        <td class="fieldLabel alignMiddle" nowrap="nowrap"><label class="muted pull-right">{vtranslate('LBL_EMAIL_SUBJECT','EMAILMaker')}:&nbsp;</label></td>
                                        <td class="fieldValue"><input name="subject" id="subject" type="text" value="{$EMAIL_TEMPLATE_RESULT.subject}" class="inputElement nameField" tabindex="1">

                                        </td>
                                        <td class="fieldValue">
                                            <select name="subject_fields" id="subject_fields" class="select2 form-control" onchange="EMAILMaker_EditJs.insertFieldIntoSubject(this.value);">
                                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
                                                <optgroup label="{vtranslate('LBL_COMMON_EMAILINFO','EMAILMaker')}">
                                                    {html_options  options=$SUBJECT_FIELDS}
                                                </optgroup>
                                                {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                                                    {html_options  options=$SELECT_MODULE_FIELD_SUBJECT}
                                                {/if}
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        {/if}
                        {*********************************************BODY DIV*************************************************}
                        <div class="tab-content">
                            <div class="tab-pane active" id="body_div2">
                                <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$EMAIL_TEMPLATE_RESULT.body}</textarea>
                            </div>
                            {if $ITS4YOUSTYLE_FILES neq ""}
                                <div class="tab-pane" id="cssstyle_div2">
                                    {foreach item=STYLE_DATA from=$STYLES_CONTENT}
                                        <div class="hide">
                                        <textarea class="CodeMirrorContent" id="CodeMirrorContent{$STYLE_DATA.id}"   style="border: 1px solid black; " class="CodeMirrorTextarea " tabindex="5">{$STYLE_DATA.stylecontent}</textarea>
                                        </div>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr class="listViewHeaders">
                                                <th>
                                                    <div class="pull-left">
                                                        <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank">{$STYLE_DATA.name}</a>
                                                    </div>
                                                    <div class="pull-right actions">
                                                        <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
                                                        {if $STYLE_DATA.iseditable eq "yes"}
                                                            <a href="index.php?module=ITS4YouStyles&view=Edit&record={$STYLE_DATA.id}" target="_blank" class="cursorPointer"><i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></i></a>
                                                        {/if}
                                                    </div>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td id="CodeMirrorContent{$STYLE_DATA.id}Output" class="cm-s-default">

                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                    {/foreach}
                                </div>
                            {/if}

                        </div>
                        <script type="text/javascript">
                            {literal} jQuery(document).ready(function(){{/literal}
                                {if $ITS4YOUSTYLE_FILES neq ""}
                                    //CKEDITOR.config.contentsCss = [{$ITS4YOUSTYLE_FILES}];
                                {literal}
                                jQuery('.CodeMirrorContent').each(function(index,Element) {
                                    var stylecontent = jQuery(Element).val();
                                    CKEDITOR.addCss(stylecontent);
                                });
                                {/literal}
                                {/if}{literal}
                                CKEDITOR.replace('body', {height: '1000'});
                            }){/literal}
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-overlay-footer row-fluid">
            <div class="textAlignCenter ">
                <button class="btn" type="submit" onclick="document.EditView.redirect.value = 'false';" ><strong>{vtranslate('LBL_APPLY',$MODULE)}</strong></button>&nbsp;&nbsp;
                <button class="btn btn-success" type="submit" ><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                {if $smarty.request.return_view neq ''}
                    <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $smarty.request.return_module neq ''}{$smarty.request.return_module}{else}EMAILMaker{/if}&view={$smarty.request.return_view}{if $smarty.request.templateid neq ""  && $smarty.request.return_view neq "List"}&templateid={$smarty.request.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {else}
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {/if}            			
            </div>
            <div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate('EMAIL_MAKER',$MODULE)} {vtranslate('COPYRIGHT',$MODULE)}</div>
        </div>
        <div class="hide" style="display: none">
            <div id="div_vat_block_table">{$VATBLOCK_TABLE}</div>
            <div id="div_charges_block_table">{$CHARGESBLOCK_TABLE}</div>
            <div id="div_company_header_signature">{$COMPANY_HEADER_SIGNATURE}</div>
            <div id="div_company_stamp_signature">{$COMPANY_STAMP_SIGNATURE}</div>
            <div id="div_company_logo">{$COMPANYLOGO}</div>
        </div>
    </form>
</div>
<script type="text/javascript">

    var selectedTab = 'properties';
    var selectedTab2 = 'body';
    var module_blocks = new Array();
 
    var selected_module = '{$SELECTMODULE}';

    var constructedOptionValue;
    var constructedOptionName;

    jQuery(document).ready(function() {

        jQuery.fn.scrollBottom = function() {
            return jQuery(document).height() - this.scrollTop() - this.height();
        };

        var $el = jQuery('.edit-template-content');
        var $window = jQuery(window);
        var top = 127;

        $window.bind("scroll resize", function() {

            var gap = $window.height() - $el.height() - 20;
            var scrollTop = $window.scrollTop();

            if (scrollTop < top - 125) {
                $el.css({
                    top: (top - scrollTop) + "px",
                    bottom: "auto"
                });
            } else {
                $el.css({
                    top: top  + "px",
                    bottom: "auto"
                });
            }
        }).scroll();
    });

</script>
{/strip}
