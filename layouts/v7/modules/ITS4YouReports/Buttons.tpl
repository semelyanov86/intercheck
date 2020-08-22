{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <div class="modal-overlay-footer">
        <div class="row clearfix">
            <div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                <button type="button" class="btn btn-default" name="back_rep_top" id="back_rep_top"><i class="fa fa-chevron-left"></i>&nbsp;{vtranslate('LBL_BACK',$MODULE)}
                </button>
                &nbsp;&nbsp;
                <button class="btn btn-success saveButton" id="savebtn" type="button">{vtranslate('LBL_SAVE_BUTTON_LABEL',$MODULE)}</button>
                &nbsp;&nbsp;
                <button class="btn btn-success saveButton" id="saverunbtn" type="button">{vtranslate('LBL_SAVE_RUN_BUTTON_LABEL',$MODULE)}</button>
                &nbsp;&nbsp;
                <a class="cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL_BUTTON_LABEL',$MODULE)}</a>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-default" name="next" id="next_rep_top"><i class="fa fa-chevron-right"></i>&nbsp;{vtranslate('LNK_LIST_NEXT',$MODULE)}
                </button>
            </div>
        </div>
    </div>
{/strip}