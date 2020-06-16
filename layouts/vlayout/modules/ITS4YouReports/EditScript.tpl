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
<script>
var constructedOptionValue;
var constructedOptionName;

var roleIdArr=new Array({$ROLEIDSTR});
var roleNameArr=new Array({$ROLENAMESTR});
var userIdArr=new Array({$USERIDSTR});
var userNameArr=new Array({$USERNAMESTR});
var grpIdArr=new Array({$GROUPIDSTR});
var grpNameArr=new Array({$GROUPNAMESTR});

/*Sharing functions*/
function sharing_changed(){ldelim}
    var selectedValue = document.getElementById('sharing').value;
    if(selectedValue != 'share')
    {ldelim}
        document.getElementById('sharing_share_div').style.display = 'none';
    {rdelim}
    else
    {ldelim}
        document.getElementById('sharing_share_div').style.display = 'block';
        setSharingObjects();
        showSharingMemberTypes();
    {rdelim}
{rdelim}

function showSharingMemberTypes(){ldelim}
	var selectedOption=document.getElementById('sharingMemberType').value;
	/*Completely clear the select box*/
	document.getElementById('sharingAvailList').options.length = 0;

	if(selectedOption == 'groups')
	{ldelim}
		constructSelectOptions('groups',grpIdArr,grpNameArr);
	{rdelim}
	else if(selectedOption == 'roles')
	{ldelim}
		constructSelectOptions('roles',roleIdArr,roleNameArr);
	{rdelim}
	else if(selectedOption == 'rs')
	{ldelim}
		constructSelectOptions('rs',roleIdArr,roleNameArr);
	{rdelim}
	else if(selectedOption == 'users')
	{ldelim}
		constructSelectOptions('users',userIdArr,userNameArr);
	{rdelim}
{rdelim}

function constructSelectOptions(selectedMemberType,idArr,nameArr){ldelim}
	var i;
	var findStr=document.getElementById('sharingFindStr').value;
	if(findStr.replace(/^\s+/g, '').replace(/\s+$/g, '').length !=0)
	{ldelim}
		var k=0;
		for(i=0; i<nameArr.length; i++)
		{ldelim}
			if(nameArr[i].indexOf(findStr) ==0)
			{ldelim}
				constructedOptionName[k]=nameArr[i];
				constructedOptionValue[k]=idArr[i];
				k++;
			{rdelim}
		{rdelim}
	{rdelim}
	else
	{ldelim}
		constructedOptionValue = idArr;
		constructedOptionName = nameArr;
	{rdelim}

	/*Constructing the selectoptions*/
	var j;
	var nowNamePrefix;
	for(j=0;j<constructedOptionName.length;j++)
	{ldelim}
		if(selectedMemberType == 'roles')
		{ldelim}
			nowNamePrefix = 'Roles::';
		{rdelim}
		else if(selectedMemberType == 'rs')
		{ldelim}
			nowNamePrefix = 'RoleAndSubordinates::';
		{rdelim}
		else if(selectedMemberType == 'groups')
		{ldelim}
			nowNamePrefix = 'Group::';
		{rdelim}
		else if(selectedMemberType == 'users')
		{ldelim}
			nowNamePrefix = 'User::';
		{rdelim}

		var nowName = nowNamePrefix + constructedOptionName[j];
		var nowId = selectedMemberType + '::'  + constructedOptionValue[j];
		document.getElementById('sharingAvailList').options[j] = new Option(nowName,nowId);
	{rdelim}
	/*clearing the array*/
	constructedOptionValue = new Array();
  constructedOptionName = new Array();
{rdelim}

function sharingAddColumn(){ldelim}
    for (i=0;i<selectedColumnsObj.length;i++)
    {ldelim}
        selectedColumnsObj.options[i].selected=false;
    {rdelim}

    for (i=0;i<availListObj.length;i++)
    {ldelim}
        if (availListObj.options[i].selected==true)
        {ldelim}
        	var rowFound=false;
        	var existingObj=null;
            for (j=0;j<selectedColumnsObj.length;j++)
            {ldelim}
                if (selectedColumnsObj.options[j].value==availListObj.options[i].value)
                {ldelim}
                    rowFound=true;
                    existingObj=selectedColumnsObj.options[j];
                    break
                {rdelim}
            {rdelim}

            if (rowFound!=true)
            {ldelim}
                var newColObj=document.createElement("OPTION");
                newColObj.value=availListObj.options[i].value;
                if (browser_ie) newColObj.innerText=availListObj.options[i].innerText;
                else if (browser_nn4 || browser_nn6) newColObj.text=availListObj.options[i].text;
                selectedColumnsObj.appendChild(newColObj);
                availListObj.options[i].selected=false;
                newColObj.selected=true;
                rowFound=false;
            {rdelim}
            else
            {ldelim}
                if(existingObj != null) existingObj.selected=true;
            {rdelim}
        {rdelim}
    {rdelim}
{rdelim}

function sharingDelColumn(){ldelim}
    for (i=selectedColumnsObj.options.length;i>0;i--)
    {ldelim}
    	if (selectedColumnsObj.options.selectedIndex>=0)
            selectedColumnsObj.remove(selectedColumnsObj.options.selectedIndex);
    {rdelim}
{rdelim}

function setSharingObjects(){ldelim}
    availListObj=getObj("sharingAvailList");
    selectedColumnsObj=getObj("sharingSelectedColumns");
{rdelim}

jQuery( document ).ready(function(){
    sharing_changed();
});
/*Sharing Ends*/

</script>
{* //ITS4YOU-END *}