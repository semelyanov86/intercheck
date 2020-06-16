{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{* //ITS4YOU-CR SlOl 26. 4. 2013 11:11:06 *}
<input type="hidden" id="recordId" value="{$RECORD_ID}" />
<script>
/*Sharing functions*/
function sharing_changed(){ldelim}
    var selectedValue = jQuery('#sharing').val();
    if(selectedValue !== 'share')
    {ldelim}
        jQuery('#sharing_share_div').hide();
    {rdelim}
    else
    {ldelim}
        jQuery('#sharing_share_div').show();
    {rdelim}
{rdelim}

jQuery( document ).ready(function(){
    sharing_changed();
});
/*Sharing Ends*/
</script>
{* //ITS4YOU-END *}