{*<!--
/* ********************************************************************************
 * The content of this file is subject to the VTEQuickEdit Search ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{if $MODULE neq ''}
    {include file='AdvanceFilter.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS}
{else}
    <h6 style="color: red;">{vtranslate('MSG_PLEASE_SELECT_RELATED_MODULE', $QUALIFIED_MODULE)}</h6>
{/if}