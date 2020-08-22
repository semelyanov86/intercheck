{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

{include file='modules/ITS4YouReports/TooltipCss.tpl'}

<div class="row-fluid">       
    <div class="span12">
        <div class="row-fluid">  
            {include file='modules/ITS4YouReports/ReportSchedulerContent.tpl'}
        </div>
    </div>
</div> 
<script>
// SCHEDULE REPORTS START
function showRecipientsOptions(){ldelim}
	var option;
	var selectedOption=document.getElementById("recipient_type");
	for(var i=0; i<selectedOption.options.length; i++) {ldelim}
		if (selectedOption.options[i].selected==true) {ldelim}
			option=selectedOption.value;
			break;
		{rdelim}
	{rdelim}

	var availableRecipientsWrapper = document.getElementById('availableRecipientsWrapper');

	if(option == 'users') {ldelim}
		availableRecipientsWrapper.innerHTML = '{$AVAILABLE_USERS}';
	{rdelim} else if(option == 'roles') {ldelim}
		availableRecipientsWrapper.innerHTML = '{$AVAILABLE_ROLES}';
	{rdelim} else if(option == 'rs') {ldelim}
		availableRecipientsWrapper.innerHTML = '{$AVAILABLE_ROLESANDSUB}';
	{rdelim} else if(option == 'groups') {ldelim}
		availableRecipientsWrapper.innerHTML = '{$AVAILABLE_GROUPS}';
	{rdelim}
{rdelim}

function addOption(){ldelim}

	var availableRecipientsObj=getObj("availableRecipients");
	var selectedRecipientsObj=getObj("selectedRecipients");
	
	for (i=0;i<selectedRecipientsObj.length;i++) {ldelim}
		selectedRecipientsObj.options[i].selected=false
	{rdelim}

	for (i=0;i<availableRecipientsObj.length;i++) {ldelim}

		if (availableRecipientsObj.options[i].selected==true) {ldelim}
			var rowFound=false;
			var existingObj=null;
			for (j=0;j<selectedRecipientsObj.length;j++) {ldelim}
				if (selectedRecipientsObj.options[j].value==availableRecipientsObj.options[i].value)
				{ldelim}
					rowFound=true
					existingObj=selectedRecipientsObj.options[j]
					break
				{rdelim}
			{rdelim}

			if (rowFound!=true) {ldelim}
				var newColObj=document.createElement("OPTION")
				newColObj.value=availableRecipientsObj.options[i].value
				if (document.all) 
                                    newColObj.innerText=availableRecipientsObj.options[i].innerText
				else 
                                    newColObj.text=availableRecipientsObj.options[i].text
				selectedRecipientsObj.appendChild(newColObj)
				availableRecipientsObj.options[i].selected=false
				newColObj.selected=true
				rowFound=false
			{rdelim}
			else {ldelim}
				if(existingObj != null) existingObj.selected=true
			{rdelim}
		{rdelim}
	{rdelim}
{rdelim}

function delOption(){ldelim}
	var selectedRecipientsObj=getObj("selectedRecipients");
	for (var i=selectedRecipientsObj.options.length; i>0; i--) {ldelim}
			if (selectedRecipientsObj.options.selectedIndex>=0)
				selectedRecipientsObj.remove(selectedRecipientsObj.options.selectedIndex)
	{rdelim}
{rdelim}

jQuery( document ).ready(function(){
    showRecipientsOptions();
    setScheduleOptions();
});
// SCHEDULE REPORTS ENDS
</script>