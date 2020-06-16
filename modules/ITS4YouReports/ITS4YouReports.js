
/*+********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

var empty_values = ["todaymore", "todayless", "older1days", "older7days", "older15days", "older30days", "older60days", "older90days", "older120days"];
/* ITS4YOU-CR SlOl | 6.6.2014 8:39  */
function replaceAll(find, replace, str){
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}
/* ITS4YOU-END 6.6.2014 8:39  */

function replaceUploadSize(){
    var upload = document.getElementById('key_upload_maxsize').value;
    upload = "'" + upload + "'";
    upload = upload.replace(/000000/g, "");
    upload = upload.replace(/'/g, "");
    document.getElementById('key_upload_maxsize').value = upload;
}


function vtlib_field_help_show_this(basenode, fldname){
    var domnode = $('vtlib_fieldhelp_div');



    var helpcontent = document.getElementById('helpInfo').value;


    if (!domnode) {
        domnode = document.createElement('div');
        domnode.id = 'vtlib_fieldhelp_div';
        domnode.className = 'dvtSelectedCell';
        domnode.style.position = 'absolute';
        domnode.style.width = '150px';
        domnode.style.padding = '4px';
        domnode.style.fontWeight = 'normal';
        document.body.appendChild(domnode);

        domnode = $('vtlib_fieldhelp_div');
        Event.observe(domnode, 'mouseover', function(){
            $('vtlib_fieldhelp_div').show();
        });
        Event.observe(domnode, 'mouseout', vtlib_field_help_hide);
    }
    else {
        domnode.show();
    }
    domnode.innerHTML = helpcontent;
    fnvshobj(basenode, 'vtlib_fieldhelp_div');
}

// from old Reports4You
var typeofdata = new Array();
typeofdata['V'] = ['e', 'n', 's', 'ew', 'c', 'k'];
typeofdata['N'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['SUM'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['AVG'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['MIN'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['MAX'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['COUNT'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['T'] = ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'];
typeofdata['I'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['C'] = ['e', 'n'];
typeofdata['D'] = ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a'];
typeofdata['NN'] = ['e', 'n', 'l', 'g', 'm', 'h'];
typeofdata['E'] = ['e', 'n', 's', 'ew', 'c', 'k'];

// ITS4YOU-CR SlOl 
var s_typeofdata = new Array();
s_typeofdata['S'] = ['e', 'n'];

var fLabels = new Array();
fLabels['e'] = alert_arr.EQUALS;
fLabels['n'] = alert_arr.NOT_EQUALS_TO;
fLabels['s'] = alert_arr.STARTS_WITH;
fLabels['ew'] = alert_arr.ENDS_WITH;
fLabels['c'] = alert_arr.CONTAINS;
fLabels['k'] = alert_arr.DOES_NOT_CONTAINS;
fLabels['l'] = alert_arr.LESS_THAN;
fLabels['g'] = alert_arr.GREATER_THAN;
fLabels['m'] = alert_arr.LESS_OR_EQUALS;
fLabels['h'] = alert_arr.GREATER_OR_EQUALS;
fLabels['bw'] = alert_arr.BETWEEN;
fLabels['b'] = alert_arr.BEFORE;
fLabels['a'] = alert_arr.AFTER;
var noneLabel;
var gcurrepfolderid = 0;
function trimfValues(value){
    var string_array;
    string_array = value.split(":");
    return string_array[4];
}

function reports4you_updatefOptions(sel, opSelName,selectedVal){
    var selObj = document.getElementById(opSelName);

    if(selObj){
        var fieldtype = null;

        var currOption = selObj.options[selObj.selectedIndex];
        var currField = sel.options[sel.selectedIndex];
        
        if (currField.value != null && currField.value.length != 0)
        {
            fieldtype = trimfValues(currField.value);

            // ITS4YOU-UP SlOl 27. 3. 2014 12:21:47
            // typeofdata[S] -> selectbox
            var sel_fields = JSON.parse(document.getElementById("sel_fields").value);

            var std_filter_columns = document.getElementById("std_filter_columns").value;

            if (typeof std_filter_columns != 'undefined' && std_filter_columns!=""){
                var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
            }else{
                var std_filter_columns_arr = new Array();
            }
            
            if (std_filter_columns_arr.indexOf(currField.value) > -1) {
                var std_filter_criteria_obj = selObj;
                var std_filter_criteria = document.getElementById("std_filter_criteria").value;
                var std_filter_criteria_arr = JSON.parse(std_filter_criteria);
                var nSFCVal = std_filter_criteria_obj.length;
                for (nLoop = 0; nLoop < nSFCVal; nLoop++)
                {
                    std_filter_criteria_obj.remove(0);
                }
                std_filter_criteria_obj.options[0] = new Option('None', '');
                var sfc_i = 1;
                for (var filter_opt in std_filter_criteria_arr)
                {
                    std_filter_criteria_obj.options[sfc_i] = new Option(std_filter_criteria_arr[filter_opt], filter_opt);
                    sfc_i++;
                }
                for (var si = 0; si < std_filter_criteria_obj.length; si++)
                {
                    if(std_filter_criteria_obj.options[si].value==selectedVal){
                        std_filter_criteria_obj.options[si].selected = true;
                    }
                }
            }else if (sel_fields[currField.value]) {
                var ops = s_typeofdata["S"];
            } else {
                var ops = typeofdata[fieldtype];
            }
            // ITS4YOU-END 
            var off = 0;
            if (ops != null)
            {

                var nMaxVal = selObj.length;
                for (nLoop = 0; nLoop < nMaxVal; nLoop++)
                {
                    selObj.remove(0);
                }
                selObj.options[0] = new Option('None', '');
                if (currField.value == '') {
                    selObj.options[0].selected = true;
                }
                off = 1;
                for (var i = 0; i < ops.length; i++)
                {
                    var label = fLabels[ops[i]];
                    if (label == null)
                        continue;
                    var option = new Option(fLabels[ops[i]], ops[i]);
                    selObj.options[i + off] = option;
                    if (currOption != null && currOption.value == option.value)
                    {
                        option.selected = true;
                    }
                }
            }
        } else
        {
            var nMaxVal = selObj.length;
            for (nLoop = 0; nLoop < nMaxVal; nLoop++)
            {
                selObj.remove(0);
            }
            selObj.options[0] = new Option('None', '');
            if (currField.value == '') {
                selObj.options[0].selected = true;
            }
        }
    }
}

// Setting cookies
function set_cookie(name, value, exp_y, exp_m, exp_d, path, domain, secure){
    var cookie_string = name + "=" + escape(value);

    if (exp_y)
    {
        var expires = new Date(exp_y, exp_m, exp_d);
        cookie_string += "; expires=" + expires.toGMTString();
    }

    if (path)
        cookie_string += "; path=" + escape(path);

    if (domain)
        cookie_string += "; domain=" + escape(domain);

    if (secure)
        cookie_string += "; secure";

    document.cookie = cookie_string;
}

// Retrieving cookies
function get_cookie(cookie_name){
    var results = document.cookie.match(cookie_name + '=(.*?)(;|$)');

    if (results)
        return (unescape(results[1]));
    else
        return null;
}


// Delete cookies 
function delete_cookie(cookie_name){
    var cookie_date = new Date( );  // current date & time
    cookie_date.setTime(cookie_date.getTime() - 1);
    document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}
function goToURL(url){
    document.location.href = url;
}

function invokeAction(actionName){
    if (actionName == "newReport")
    {
        // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
        goToURL("?module=ITS4YouReports&action=NewReport0&return_module=ITS4YouReports&return_action=index");
        return;
    }
    goToURL("/crm/ScheduleReport.do?step=showAllSchedules");
}
function verify_data(form){
    var isError = false;
    var errorMessage = "";
    if (trim(form.folderName.value) == "") {
        isError = true;
        errorMessage += "\nFolder Name";
    }
    // Here we decide whether to submit the form.
    if (isError == true) {
        alert(alert_arr.MISSING_FIELDS + errorMessage);
        return false;
    }
    return true;
}

function setObjects(){
    var availListObj = getObj("availList")
    var selectedColumnsObj = getObj("selectedColumns")

    var moveupLinkObj = getObj("moveup_link")
    var moveupDisabledObj = getObj("moveup_disabled")
    var movedownLinkObj = getObj("movedown_link")
    var movedownDisabledObj = getObj("movedown_disabled")
}

function addColumn(columns){
    if (columns == "selectedColumnsRel") {
        selectedColumnsObj = getObj("selectedColumns");
    } else if (columns == "selectedQFColumnsRel") {
        selectedColumnsObj = getObj("selectedQFColumns");
    } else if (columns == "selectedSummaries") {
        selectedColumnsObj = getObj("selectedSummaries");
    } else {
        selectedColumnsObj = getObj(columns);
    }

    for (i = 0; i < selectedColumnsObj.length; i++)
    {
        selectedColumnsObj.options[i].selected = false
    }

    if (columns == "selectedColumns") {
        addColumnStep1();
    } else if (columns == "selectedColumnsRel") {
        addColumnStep3();
    } else if (columns == "selectedQFColumnsRel") {
        addColumnStep4();
    } else if (columns == "selectedSummaries") {
        addColumnStep5();
    } else {
        addColumnStep2();
    }
}

function addColumnStep1(){
    availListObj = getObj("availList");
    selectedColumnsObj = getObj("selectedColumns");

    var availModules = document.getElementById("availModules");
    var selectedModule = availModules.options[availModules.selectedIndex].text;
    var selectedModule_value = availModules.options[availModules.selectedIndex].value;
    var selectedPrimaryModule = document.getElementById('primarymodule').options[document.getElementById('primarymodule').selectedIndex].value;

    // if module is != primary module it is neccessary to remove -+space from start of selectedmodule text e.g. - Invoice to Invoice
    if (selectedModule_value != selectedPrimaryModule) {
        selectedModule = selectedModule.replace("- ", "");
    }

    if (availListObj.options.selectedIndex > -1)
    {
        for (i = 0; i < availListObj.length; i++)
        {
            if (availListObj.options[i].selected == true)
            {
                var rowFound = false;
                for (j = 0; j < selectedColumnsObj.length; j++)
                {
                    if (selectedColumnsObj.options[j].value == availListObj.options[i].value)
                    {
                        var rowFound = true;
                        var existingObj = selectedColumnsObj.options[j];
                        break;
                    }
                }

                if (rowFound != true)
                {
                    var newColObj = document.createElement("OPTION")
                    newColObj.value = availListObj.options[i].value
                    if (browser_ie)
                        newColObj.innerText = availListObj.options[i].innerText + " [" + selectedModule + "]";
                    else if (browser_nn4 || browser_nn6)
                        newColObj.text = availListObj.options[i].text + " [" + selectedModule + "]";
                    selectedColumnsObj.appendChild(newColObj)
                    newColObj.selected = true
                }
                else
                {
                    existingObj.selected = true
                }
                availListObj.options[i].selected = false
                addColumnStep1();
            }
        }
    }

    oldselectvalue = getObj("SortByColumn").value;

    if (browser_ie)
    {
        getObj("SortByColumn").outerHTML = "<select id='SortByColumn' name='SortByColumn' class='txtBox'><option value='none'>" + none_lang + "</option>" + selectedColumnsObj.innerHTML + "</select>";
    }
    else
    {
        getObj("SortByColumn").innerHTML = "<option value='none'>" + none_lang + "</option>";
        getObj("SortByColumn").innerHTML += selectedColumnsObj.innerHTML;
    }

    getObj("SortByColumn").value = oldselectvalue;
}

function addColumnStep2(){
    availListObj = getObj("availQFList");
    selectedColumnsObj = getObj("selectedQFColumns");

    if (availListObj.options.selectedIndex > -1)
    {
        for (i = 0; i < availListObj.length; i++)
        {
            if (availListObj.options[i].selected == true)
            {
                var rowFound = false;
                for (j = 0; j < selectedColumnsObj.length; j++)
                {
                    if (selectedColumnsObj.options[j].value == availListObj.options[i].value)
                    {
                        var rowFound = true;
                        var existingObj = selectedColumnsObj.options[j];
                        break;
                    }
                }

                if (rowFound != true)
                {
                    var newColObj = document.createElement("OPTION")
                    newColObj.value = availListObj.options[i].value
                    if (browser_ie)
                        newColObj.innerText = availListObj.options[i].innerText
                    else if (browser_nn4 || browser_nn6)
                        newColObj.text = availListObj.options[i].text
                    selectedColumnsObj.appendChild(newColObj)
                    newColObj.selected = true
                }
                else
                {
                    existingObj.selected = true
                }
                availListObj.options[i].selected = false
                addColumnStep2();
            }
        }
    }
}


function addColumnStep3(){
    // availListObj=getObj("availList2");
    selectedColumnsObj = getObj("selectedColumns");
    /*if (availListObj.options.selectedIndex > -1)
     {
     for (i=0;i<availListObj.length;i++) 
     {
     if (availListObj.options[i].selected==true) 
     {
     var rowFound=false;
     for (j=0;j<selectedColumnsObj.length;j++) 
     {
     if (selectedColumnsObj.options[j].value==availListObj.options[i].value) 
     {
     var rowFound=true;
     var existingObj=selectedColumnsObj.options[j];
     break;
     }
     }
     
     if (rowFound!=true) 
     {
     var newColObj=document.createElement("OPTION")
     newColObj.value=availListObj.options[i].value
     if (browser_ie) newColObj.innerText=availListObj.options[i].innerText
     else if (browser_nn4 || browser_nn6) newColObj.text=availListObj.options[i].text
     selectedColumnsObj.appendChild(newColObj)
     newColObj.selected=true
     } 
     else 
     {
     existingObj.selected=true
     }
     availListObj.options[i].selected=false
     addColumnStep3();
     }
     }
     }*/

    oldselectvalue = getObj("SortByColumn").value;

    if (browser_ie)
    {
        getObj("SortByColumn").outerHTML = "<select id='SortByColumn' name='SortByColumn' class='txtBox'><option value='none'>" + none_lang + "</option>" + selectedColumnsObj.innerHTML + "</select>";
    }
    else
    {
        getObj("SortByColumn").innerHTML = "<option value='none'>" + none_lang + "</option>";
        getObj("SortByColumn").innerHTML += selectedColumnsObj.innerHTML;
    }

    getObj("SortByColumn").value = oldselectvalue;
}

function addColumnStep4(){
    availListObj = getObj("availQFList2");
    selectedColumnsObj = getObj("selectedQFColumns");
    if (availListObj.options.selectedIndex > -1)
    {
        for (i = 0; i < availListObj.length; i++)
        {
            if (availListObj.options[i].selected == true)
            {
                var rowFound = false;
                for (j = 0; j < selectedColumnsObj.length; j++)
                {
                    if (selectedColumnsObj.options[j].value == availListObj.options[i].value)
                    {
                        var rowFound = true;
                        var existingObj = selectedColumnsObj.options[j];
                        break;
                    }
                }

                if (rowFound != true)
                {
                    var newColObj = document.createElement("OPTION")
                    newColObj.value = availListObj.options[i].value
                    if (browser_ie)
                        newColObj.innerText = availListObj.options[i].innerText
                    else if (browser_nn4 || browser_nn6)
                        newColObj.text = availListObj.options[i].text
                    selectedColumnsObj.appendChild(newColObj)
                    newColObj.selected = true
                }
                else
                {
                    existingObj.selected = true
                }
                availListObj.options[i].selected = false
                addColumnStep4();
            }
        }
    }
}

function addColumnStep5(){
    var selectedGroup1 = document.getElementById('Group1');
    var selectedGroup1Value = document.getElementById('Group1').options[selectedGroup1.selectedIndex].value;
    if(selectedGroup1Value=="none"){
        var selectedColumnLabel = document.getElementById("group1_column_label").value;
        alert(selectedColumnLabel + alert_arr.CANNOT_BE_NONE)
        return false;
    }
    
    var availListObj = getObj("availListSum");
    var selectedColumnsObj = getObj("selectedSummaries");
    if (availListObj.options.selectedIndex > -1)
    {
        for (i = 0; i < availListObj.length; i++)
        {
            if (availListObj.options[i].selected == true)
            {
                var rowFound = false;
                for (j = 0; j < selectedColumnsObj.length; j++)
                {
                    if (selectedColumnsObj.options[j].value == availListObj.options[i].value)
                    {
                        var rowFound = true;
                        var existingObj = selectedColumnsObj.options[j];
                        break;
                    }
                }

                if (rowFound != true)
                {
                    var newColObj = document.createElement("OPTION")
                    if (availListObj.options[i].value != "none") {
                        newColObj.value = availListObj.options[i].value;

                        var selectedSumModuleObj = document.getElementById("SummariesModules");
                        var selectedSumModule = selectedSumModuleObj.options[selectedSumModuleObj.selectedIndex].text;
                        if (selectedSumModule.substring(0, 2) == "- ") {
                            selectedSumModule = selectedSumModule.substring(2);
                        }
                        if (browser_ie) {
                            var avl_text = availListObj.options[i].innerText
                            newColObj.innerText = avl_text + " (" + selectedSumModule + ")";
                        } else if (browser_nn4 || browser_nn6) {
                            var avl_text = availListObj.options[i].text;
                            newColObj.text = avl_text + " (" + selectedSumModule + ")";
                        }

                        addToSummSortOrder(availListObj.options[i]);

                        selectedColumnsObj.appendChild(newColObj);
                    }
                    // newColObj.selected=true
                }
                else
                {
                    existingObj.selected = true
                }
                availListObj.options[i].selected = false
                addColumnStep5();
            }
        }
    }
}
// ITS4YOU-CR SlOl 21. 3. 2014 14:14:58
function setSelSummSortOrder(selectedval){
    var summaries_orderby_columnObj = getObj("summaries_orderby_column");

    for (j = 0; j < summaries_orderby_columnObj.length; j++)
    {
        if (summaries_orderby_columnObj.options[j].value == selectedval)
        {
            var go_j = j;
            var soc_str = summaries_orderby_columnObj.options[j].value;
            summaries_orderby_columnObj.options[j].selected=true;
            break;
        }
    }
    document.getElementById("summaries_orderby_columnString").value = soc_str;
    summaries_orderby_columnObj.options[go_j].selected = true;
}
function delSummSortOrder(opt_obj){
    sortbyColumnsObj = getObj("summaries_orderby_column");
    for (j = 0; j < sortbyColumnsObj.options.length; j++)
    {
        if (opt_obj.value == sortbyColumnsObj.options[j].value) {
            sortbyColumnsObj.remove(j);
        }
    }
}
function addToSummSortOrder(opt_obj){
    summaries_orderby_columnObj = getObj("summaries_orderby_column");
    var rowFound = false;
    for (j = 0; j < summaries_orderby_columnObj.length; j++)
    {
        if (summaries_orderby_columnObj.options[j].value == opt_obj.value)
        {
            var rowFound = true;
            break;
        }
    }
    if (rowFound != true)
    {
        var newColObj = document.createElement("OPTION")
        if (opt_obj.value != "none") {
            newColObj.value = opt_obj.value
            if (browser_ie)
                newColObj.innerText = opt_obj.innerText
            else if (browser_nn4 || browser_nn6)
                newColObj.text = opt_obj.text
            summaries_orderby_columnObj.appendChild(newColObj);
        }
    }
}
// ITS4YOU-END 21. 3. 2014 14:14:59

//this function is done for checking,whether the user has access to edit the field :Bharath
function selectedColumnClick(oSel){
    var error_msg = '';
    var error_str = false;
    if (oSel.selectedIndex > -1) {
        for (var i = 0; i < oSel.options.length; ++i) {
            if (oSel.options[i].selected == true && oSel.options[i].disabled == true) {
                error_msg = error_msg + oSel.options[i].text + ',';
                error_str = true;
                oSel.options[i].selected = false;
            }
        }
    }
    if (error_str)
    {
        error_msg = error_msg.substr(0, error_msg.length - 1);
        alert(alert_arr.NOT_ALLOWED_TO_EDIT_FIELDS + "\n" + error_msg);
        return false;
    }
    else
        return true;
}
function delColumn(columns){
    selectedColumnsObj = getObj(columns);
    if (selectedColumnsObj.options.selectedIndex > -1)
    {
        for (i = 0; i < selectedColumnsObj.options.length; i++)
        {
            if (selectedColumnsObj.options[i].selected == true)
            {
                delSummSortOrder(selectedColumnsObj.options[i]);
                // ITS4YOU-CR SlOl 4. 9. 2013 14:55:44
                if (columns == "selectedColumns") {
                    sortbyColumnsObj = getObj("SortByColumn");
                    for (j = 0; j < sortbyColumnsObj.options.length; j++)
                    {
                        if (selectedColumnsObj.options[i].value == sortbyColumnsObj.options[j].value) {
                            sortbyColumnsObj.remove(j);
                        }
                    }
                }
                // ITS4YOU-END
                selectedColumnsObj.remove(i);
                delColumn(columns);
            }
        }
    }
}
/*function formSelectColumnString()
 {
 var selectedColStr = "";
 selectedColumnsObj=getObj("selectedColumns");
 for (i=0;i<selectedColumnsObj.options.length;i++) 
 {
 selectedColStr += selectedColumnsObj.options[i].value + ";";
 }
 document.NewReport.selectedColumnsString.value = selectedColStr;
 }*/
function hasOptions(obj){
    if (obj != null && obj.options != null) {
        return true;
    }
    return false;
}

function swapOptions(obj, i, j){
    var o = obj.options;
    var i_selected = o[i].selected;
    var j_selected = o[j].selected;
    var temp = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
    var temp2 = new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
    o[i] = temp2;
    o[j] = temp;
    o[i].selected = j_selected;
    o[j].selected = i_selected;
}

function moveUp(columns){
    // ITS4YOU-CR SlOl 2/20/2014 8:13:46 PM
    obj = getObj(columns);
    if (!hasOptions(obj)) {
        return;
    }
    for (i = 0; i < obj.options.length; i++) {
        if (obj.options[i].selected) {
            if (i != 0 && !obj.options[i - 1].selected) {
                swapOptions(obj, i, i - 1);
                obj.options[i - 1].selected = true;
            }
        }
    }
    // ITS4YOU-END 2/20/2014 8:13:50 PM
    /* OLD SWAPPING COMMENTED // ITS4YOU SlOl 
     selectedColumnsObj=getObj(columns);
     var currpos=selectedColumnsObj.options.selectedIndex
     var tempdisabled= false;
     for (i=0;i<selectedColumnsObj.length;i++) 
     {
     if(i != currpos)
     selectedColumnsObj.options[i].selected=false
     }
     if (currpos>0) 
     {
     var prevpos=selectedColumnsObj.options.selectedIndex-1
     
     if (browser_ie) 
     {
     temp=selectedColumnsObj.options[prevpos].innerText
     tempdisabled = selectedColumnsObj.options[prevpos].disabled;
     selectedColumnsObj.options[prevpos].innerText=selectedColumnsObj.options[currpos].innerText
     selectedColumnsObj.options[prevpos].disabled = false;
     selectedColumnsObj.options[currpos].innerText=temp
     selectedColumnsObj.options[currpos].disabled = tempdisabled;     
     } 
     else if (browser_nn4 || browser_nn6) 
     {
     temp=selectedColumnsObj.options[prevpos].text
     tempdisabled = selectedColumnsObj.options[prevpos].disabled;
     selectedColumnsObj.options[prevpos].text=selectedColumnsObj.options[currpos].text
     selectedColumnsObj.options[prevpos].disabled = false;
     selectedColumnsObj.options[currpos].text=temp
     selectedColumnsObj.options[currpos].disabled = tempdisabled;
     }
     temp=selectedColumnsObj.options[prevpos].value
     selectedColumnsObj.options[prevpos].value=selectedColumnsObj.options[currpos].value
     selectedColumnsObj.options[currpos].value=temp
     selectedColumnsObj.options[prevpos].selected=true
     selectedColumnsObj.options[currpos].selected=false
     }*/
}

function moveDown(columns){
    obj = getObj(columns);
    if (!hasOptions(obj)) {
        return;
    }
    for (i = obj.options.length - 1; i >= 0; i--) {
        if (obj.options[i].selected) {
            if (i != (obj.options.length - 1) && !obj.options[i + 1].selected) {
                swapOptions(obj, i, i + 1);
                obj.options[i + 1].selected = true;
            }
        }
    }
    /* OLD VTIGER SWAPPING COMMENTED // ITS4YOU SlOl 
     selectedColumnsObj=getObj(columns);
     var currpos=selectedColumnsObj.options.selectedIndex
     var tempdisabled= false;
     for (i=0;i<selectedColumnsObj.length;i++) 
     {
     if(i != currpos)
     selectedColumnsObj.options[i].selected=false
     }
     if (currpos<selectedColumnsObj.options.length-1)	
     {
     var nextpos=selectedColumnsObj.options.selectedIndex+1
     
     if (browser_ie) 
     {	
     temp=selectedColumnsObj.options[nextpos].innerText
     tempdisabled = selectedColumnsObj.options[nextpos].disabled;
     selectedColumnsObj.options[nextpos].innerText=selectedColumnsObj.options[currpos].innerText
     selectedColumnsObj.options[nextpos].disabled = false;
     selectedColumnsObj.options[nextpos];
     
     selectedColumnsObj.options[currpos].innerText=temp
     selectedColumnsObj.options[currpos].disabled = tempdisabled;
     }
     else if (browser_nn4 || browser_nn6) 
     {
     temp=selectedColumnsObj.options[nextpos].text
     tempdisabled = selectedColumnsObj.options[nextpos].disabled;
     selectedColumnsObj.options[nextpos].text=selectedColumnsObj.options[currpos].text
     selectedColumnsObj.options[nextpos].disabled = false;
     selectedColumnsObj.options[nextpos];
     selectedColumnsObj.options[currpos].text=temp
     selectedColumnsObj.options[currpos].disabled = tempdisabled;
     }
     temp=selectedColumnsObj.options[nextpos].value
     selectedColumnsObj.options[nextpos].value=selectedColumnsObj.options[currpos].value
     selectedColumnsObj.options[currpos].value=temp
     
     selectedColumnsObj.options[nextpos].selected=true
     selectedColumnsObj.options[currpos].selected=false
     }*/
}

function disableMove(){
    selectedColumnsObj = getObj("selectedColumns");
    var cnt = 0
    for (i = 0; i < selectedColumnsObj.options.length; i++)
    {
        if (selectedColumnsObj.options[i].selected == true)
            cnt++
    }

    if (cnt > 1)
    {
        moveupLinkObj.style.display = movedownLinkObj.style.display = "none"
        moveupDisabledObj.style.display = movedownDisabledObj.style.display = "block"
    }
    else
    {
        moveupLinkObj.style.display = movedownLinkObj.style.display = "block"
        moveupDisabledObj.style.display = movedownDisabledObj.style.display = "none"
    }
}


function hideTabs(){
    /* REPORT TYPE REMOVED -> this is not usefull anymore
     // Check the selected report type
     var objreportType = document.getElementsByName('reportType');
     if(objreportType[0].checked == true) objreportType = objreportType[0];
     else if(objreportType[1].checked == true) objreportType = objreportType[1];
     else if(objreportType[2].checked == true) objreportType = objreportType[2];
     else if(objreportType[3].checked == true) objreportType = objreportType[3];
     
     document.NewReport.reportTypeValue.value = objreportType.value;
     if(objreportType.value != 'tabular')
     {
     divarray = new Array('step1','step2','step3','step4','step5','step6');
     getObj('step4label').style.color = '#0070BA';
     }
     else
     {
     divarray = new Array('step1','step2','step4','step5','step6');
     getObj('step4label').style.color = '#000000';		
     }
     */
}

function showSaveDialog(){
    // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
    url = "index.php?module=ITS4YouReports&action=SaveReport";
    window.open(url, "Save_Report", "width=550,height=350,top=20,left=20;toolbar=no,status=no,menubar=no,directories=no,resizable=yes,scrollbar=no")
}

function saveAndRunReport(){
    var primarymoduleObj = getObj("primarymodule"); // reportfolder
    var selectedColumn = trim(primarymoduleObj.value);
    //document.NewReport.report_primarymodule.value = selectedColumn;

    var selectedColumnsObj = document.getElementById('selectedColumns');
    var selectedSummariesObj = document.getElementById('selectedSummaries');
    
    var selectedGroup2 = document.getElementById('Group2');
    var selectedGroup2Value = document.getElementById('Group2').options[selectedGroup2.selectedIndex].value;
    if(selectedGroup2Value=="none"){
		document.getElementById('timeline_type2').options[0].selected =true;
	}
    
    var selectedGroup3 = document.getElementById('Group3');
    var selectedGroup3Value = document.getElementById('Group3').options[selectedGroup3.selectedIndex].value;
    if(selectedGroup3Value=="none"){
		document.getElementById('timeline_type3').options[0].selected =true;
	}
	
	if (selectedColumnsObj.length == 0 && selectedSummariesObj.length == 0 || selectedGroup2Value!="none" && selectedSummariesObj.length==0){
        alert(alert_arr.COLUMNS_CANNOT_BE_EMPTY);
        return false;
    }

    var relatedmodules = '';
    var all_related_modules_str = document.getElementById('all_related_modules').value;
    if (all_related_modules_str != '') {
        var all_related_modules = all_related_modules_str.split(":");
        for (i = 0; i <= (all_related_modules.length - 1); i++)
        {
            var rel_mod_actual = 'relmodule_' + all_related_modules[i];
            actual_rel_module = document.getElementById(rel_mod_actual);
            if (actual_rel_module.checked)
                relatedmodules += actual_rel_module.value + ':';
        }
    }
    document.NewReport.secondarymodule.value = relatedmodules;

    var escapedOptions = new Array('account_id', 'contactid', 'contact_id', 'product_id', 'parent_id', 'campaignid', 'potential_id', 'assigned_user_id1', 'quote_id', 'accountname', 'salesorder_id', 'vendor_id', 'time_start', 'time_end', 'lastname');

    var conditionColumns = vt_getElementsByName('tr', "conditionColumn");
    var criteriaConditions = [];
    // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
    var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
    for (var i = 0; i < conditionColumns.length; i++) {

        var columnRowId = conditionColumns[i].getAttribute("id");
        var columnRowInfo = columnRowId.split("_");
        var columnGroupId = columnRowInfo[1];
        var columnIndex = columnRowInfo[2];

        if (columnGroupId != "0")
            ctype = "f";
        else
            ctype = "g";

        var columnId = ctype + "col" + columnIndex;
        var columnObject = getObj(columnId);
        var selectedColumn = trim(columnObject.value);
        var selectedColumnIndex = columnObject.selectedIndex;
        var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
        if (columnObject.options[selectedColumnIndex].value != "none") {
            var comparatorId = ctype + "op" + columnIndex;
            var comparatorObject = getObj(comparatorId);
            var comparatorValue = trim(comparatorObject.value);

            var valueId = ctype + "val" + columnIndex;
            var valueObject = getObj(valueId);
            var specifiedValue = trim(valueObject.value);

            var extValueId = ctype + "val_ext" + columnIndex;
            var extValueObject = getObj(extValueId);
            if (extValueObject) {
                extendedValue = trim(extValueObject.value);
            }

            var glueConditionId = ctype + "con" + columnIndex;
            var glueConditionObject = getObj(glueConditionId);
            var glueCondition = '';
            if (glueConditionObject) {
                glueCondition = trim(glueConditionObject.value);
            }

            if(conditionColumns.length>1){
                if (!emptyCheck4You(columnId, " Column ", "text")){
                    // i < conditionColumns.length
                    return false;
                }
                if (!emptyCheck4You(comparatorId, selectedColumnLabel + " Option", "text")){
                    return false;
                }
            }

            var col = selectedColumn.split(":");
            
            var std_filter_columns = document.getElementById("std_filter_columns").value;
            if (typeof std_filter_columns != 'undefined' && std_filter_columns!=""){
                var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
            }else{
                var std_filter_columns_arr = new Array();
            }

            if (std_filter_columns_arr.indexOf(selectedColumn) > -1) {
                if(comparatorValue=="custom"){
                    if (!emptyCheck4You("jscal_field_sdate"+columnIndex, " Column ", "text")){
                        return false;
                    }
                }
                if(comparatorValue=="custom"){
                    if (!emptyCheck4You("jscal_field_edate"+columnIndex, " Column ", "text")){
                        return false;
                    }
                }
                var start_date = document.getElementById("jscal_field_sdate"+columnIndex).value;
                var end_date = document.getElementById("jscal_field_edate"+columnIndex).value;
                var specifiedValue = start_date+"<;@STDV@;>"+end_date;
            }else{
                if (escapedOptions.indexOf(col[3]) == -1) {
                    if (col[4] == 'T') {
                        var datime = specifiedValue.split(" ");
                        if (!re_dateValidate(datime[0], selectedColumnLabel + " (Current User Date Time Format)", "OTH"))
                            return false
                        if (datime.length > 1)
                            if (!re_patternValidate(datime[1], selectedColumnLabel + " (Time)", "TIMESECONDS"))
                                return false
                    }
                    else if (col[4] == 'D')
                    {
                        if (!dateValidate(valueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                            return false
                        if (extValueObject) {
                            if (!dateValidate(extValueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                                return false
                        }
                    } else if (col[4] == 'I')
                    {
                        if (!intValidate(valueId, selectedColumnLabel + " (Integer Criteria)" + i))
                            return false
                    } else if (col[4] == 'N')
                    {
                        if (!numValidate(valueId, selectedColumnLabel + " (Number) ", "any", true))
                            return false
                    } else if (col[4] == 'E')
                    {
                        if (!patternValidate(valueId, selectedColumnLabel + " (Email Id)", "EMAIL"))
                            return false
                    }
                    // ITS4YOU-CR SlOl 28. 3. 2014 8:39:20
                    if (sel_fields[selectedColumn]) {
                        // fop0
                        // fval0_|_3
                        var sel_fields_array = new Array();
                        xc = document.getElementsByTagName('input');
                        var m_i = 0;
                        for (var fi = 0; fi < xc.length; fi++)
                        {
                            if (xc[fi].type == 'checkbox')
                            {
                                var sel_field_cb = xc[fi].id;
                                if (sel_field_cb.substring(0, 8) == "fval" + columnIndex + "_|_") {
                                    if (xc[fi].checked == true) {
                                        sel_fields_array[m_i] = xc[fi].value;
                                        m_i++;
                                    }
                                }
                            }
                        }
                        var specifiedValue = sel_fields_array.join();
                        if (!(specifiedValue) || specifiedValue == "") {
                            alert(selectedColumnLabel + alert_arr.CANNOT_BE_NONE)
                        }
                    }
                    // ITS4YOU-END 28. 3. 2014 8:39:13
                }
            }
            //Added to handle yes or no for checkbox fields in reports advance filters. 
            if (col[4] == "C") {
                if (specifiedValue == "1")
                    specifiedValue = getObj(valueId).value = 'yes';
                else if (specifiedValue == "0")
                    specifiedValue = getObj(valueId).value = 'no';
            }
            if (extValueObject && extendedValue != null && extendedValue != '')
                specifiedValue = specifiedValue + ',' + extendedValue;

            criteriaConditions[columnIndex] = {"groupid": columnGroupId,
                "columnname": selectedColumn,
                "comparator": comparatorValue,
                "value": specifiedValue,
                "column_condition": glueCondition
            };
        }
    }
    $('advft_criteria').value = JSON.stringify(criteriaConditions);

    var conditionGroups = vt_getElementsByName('div', "conditionGroup");
    var criteriaGroups = [];
    for (var i = 0; i < conditionGroups.length; i++)
    {
        var groupTableId = conditionGroups[i].getAttribute("id");
        var groupTableInfo = groupTableId.split("_");
        var groupIndex = groupTableInfo[1];

        var groupConditionId = "gpcon" + groupIndex;
        var groupConditionObject = getObj(groupConditionId);
        var groupCondition = '';
        if (groupConditionObject) {
            groupCondition = trim(groupConditionObject.value);
        }
        criteriaGroups[groupIndex] = {"groupcondition": groupCondition};
    }
    $('advft_criteria_groups').value = JSON.stringify(criteriaGroups);

    // groupconditioncolumn start
    var GroupconditionColumns = vt_getElementsByName('tr', "groupconditionColumn");
    var GroupcriteriaConditions = [];
    // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
    for (var i = 0; i < GroupconditionColumns.length; i++) {

        var columnRowId = GroupconditionColumns[i].getAttribute("id");
        var columnRowInfo = columnRowId.split("_");
        var columnGroupId = columnRowInfo[1];
        var columnIndex = columnRowInfo[2];

        if (columnGroupId != "0")
            ctype = "f";
        else
            ctype = "g";

        var columnId = ctype + "groupcol" + columnIndex;
        var columnObject = getObj(columnId);
        var selectedColumn = trim(columnObject.value);
        var selectedColumnIndex = columnObject.selectedIndex;
        var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
        if (columnObject.options[selectedColumnIndex].value != "none") {
            var comparatorId = ctype + "groupop" + columnIndex;
            var comparatorObject = getObj(comparatorId);
            var comparatorValue = trim(comparatorObject.value);
            var valueId = ctype + "groupval" + columnIndex;
            var valueObject = getObj(valueId);
            var specifiedValue = trim(valueObject.value);

            var glueConditionId = ctype + "groupcon" + columnIndex;
            var glueConditionObject = getObj(glueConditionId);
            var glueCondition = '';
            if (glueConditionObject) {
                glueCondition = trim(glueConditionObject.value);
            }

            if(GroupconditionColumns.length>1){
                if (!emptyCheck4You(columnId, " Column ", "text")){
                    // i < GroupconditionColumns.length
                    return false;
                }
                if (!emptyCheck4You(comparatorId, selectedColumnLabel + " Option", "text")){
                    return false;
                }
            }

            var col = selectedColumn.split(":");
            if (escapedOptions.indexOf(col[3]) == -1) {
                if (col[4] == 'T') {
                    var datime = specifiedValue.split(" ");
                    if (!re_dateValidate(datime[0], selectedColumnLabel + " (Current User Date Time Format)", "OTH"))
                        return false
                    if (datime.length > 1)
                        if (!re_patternValidate(datime[1], selectedColumnLabel + " (Time)", "TIMESECONDS"))
                            return false
                }
                else if (col[4] == 'D')
                {
                    if (!dateValidate(valueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                        return false
                    if (extValueObject) {
                        if (!dateValidate(extValueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                            return false
                    }
                } else if (col[4] == 'I')
                {
                    if (!intValidate(valueId, selectedColumnLabel + " (Integer Criteria)" + i))
                        return false
                } else if (col[4] == 'N')
                {
                    if (!numValidate(valueId, selectedColumnLabel + " (Number) ", "any", true))
                        return false
                } else if (col[4] == 'E')
                {
                    if (!patternValidate(valueId, selectedColumnLabel + " (Email Id)", "EMAIL"))
                        return false
                }
            }
            //Added to handle yes or no for checkbox fields in reports advance filters. 
            if (col[4] == "C") {
                if (specifiedValue == "1")
                    specifiedValue = getObj(valueId).value = 'yes';
                else if (specifiedValue == "0")
                    specifiedValue = getObj(valueId).value = 'no';
            }
            if (extValueObject && extendedValue != null && extendedValue != '')
                specifiedValue = specifiedValue + ',' + extendedValue;

            GroupcriteriaConditions[columnIndex] = {"groupid": columnGroupId,
                "columnname": selectedColumn,
                "comparator": comparatorValue,
                "value": specifiedValue,
                "column_condition": glueCondition
            };
        }
    }
    $('groupft_criteria').value = JSON.stringify(GroupcriteriaConditions);
    // groupconditioncolumn end
    
    var date1 = getObj("startdate")
    var date2 = getObj("enddate")
    
    if (typeof getObj("stdDateFilter") != 'undefined'){
        var stdNewDateFilterObj = getObj("stdDateFilter");
        var stdNewDateFilterIndex = stdNewDateFilterObj.selectedIndex;
        var stdNewDateFilterValue = stdNewDateFilterObj.options[stdNewDateFilterIndex].value;
        // see this file on top for empty_values definition and ITS4YouReports.php file too
        var go_empty = empty_values.indexOf(stdNewDateFilterValue);
    }else{
        var go_empty = new Array();
    }
    //# validation added for date field validation in final step of report creation
    if (go_empty<0 && (date1.value != ''))
    {
        if (!dateValidate("startdate", "Start Date", "D"))
            return false;
    }
    if(go_empty<0 && (date2.value != '')){
        if (!dateValidate("enddate", "End Date", "D"))
            return false;
    }
    if(go_empty<0 && ((date1.value != '') || (date2.value != ''))){
        if (!dateComparison("startdate", 'Start Date', "enddate", 'End Date', 'LE'))
            return false;
    }

    formSelectedColumnString();
    formSelectedSummariesString();
    // formSelectColumnString();
    getCurl();
    //getQFurl();
    // ITS4YOU-CR SlOl 12. 3. 2014 14:21:47
    getLabelsurl();
    // formSelectQFColumnString();
    return true;
    // document.NewReport.submit();
}
//ITS4YOU-CR SlOl 26. 8. 2013 11:51:24
function formSelectedSummariesString(){
    selectedSummariesObj = document.getElementById("selectedSummaries");
    var selectedSumStr = "";
    for (i = 0; i < selectedSummariesObj.options.length; i++)
    {
        selectedSumStr += selectedSummariesObj.options[i].value + ";";
    }
    document.getElementById("selectedSummariesString").value = selectedSumStr;
    // document.NewReport.selectedSummariesString.value = selectedSumStr;
}
// ITS4YOU-CR SlOl 5. 3. 2014 15:53:46
function formSelectedColumnString(){
    selectedColumnsObj = getObj("selectedColumns");
    var selectedColStr = "";
    for (i = 0; i < selectedColumnsObj.options.length; i++)
    {
        selectedColStr += selectedColumnsObj.options[i].value + ";";
    }
    document.getElementById("selectedColumnsString").value = selectedColStr;
    // document.NewReport.selectedColumnsString.value = selectedColStr;
}
//ITS4YOU-END 

// ITS4YOU-CR SlOl 4. 9. 2013 16:04:23
function nextStep4You(){
    var go_to_step = actual_step + 1;
    // ITS4YOU SlOl step 2 and step 3 disabled -2 steps
    if (go_to_step == 2) {
        go_to_step = go_to_step + 2;
    }
    if (go_to_step != 12) {
        changeSteps4U(go_to_step);
    } else {
        changeSteps();
    }
}
// ITS4YOU-END 4. 9. 2013 16:04:16

function hideAllSteps(gotoStep,step){
    for (i = 1; i <= 12; i++)
    {
        if (document.getElementById("step" + i)) {
            var mystepnr = "step" + i;
            var tableid = mystepnr + 'label';
            if (i != step) {
                if (getObj(tableid)) {
                    hide(mystepnr);
                    getObj(tableid).className = 'dvtUnSelectedCell';
                }
            }
        }
    }
    var newtableid = gotoStep + 'label';
    getObj(newtableid).className = 'dvtSelectedCell';

    return true;
}

function checkEmptyAddFirstCondition(){

    // if(document.getElementById("mode").value==="create"){
        var s = document.NewReport.getElementsByTagName('select');
        var fcol_i = 0;
        for (var i = 0; i < s.length; i++)
        {
            if (s[i].name.substring(0, 4) == 'fcol')
            {
                fcol_i++;
            }
        }
        if(fcol_i===0){
            addNewConditionGroup('adv_filter_div');
        }
    // }
    return true;
}

// ITS4YOU-CR SlOl 21.12.2010 R4U
function changeSteps4U(step){
    actual_step = step;

    var gotoStep = "step" + step;
    if (!gotoStep) {
        return false;
    } else {
        /* REPORT TYPE REMOVED
         // tabular = 0 , summary = 1 , grouping = 2 , timeline = 3
         var objreportType = document.getElementsByName('reportType');
         if(objreportType[0].checked == true) objreportType = objreportType[0];
         else if(objreportType[1].checked == true) objreportType = objreportType[1];
         else if(objreportType[2].checked == true) objreportType = objreportType[2];
         else if(objreportType[3].checked == true) objreportType = objreportType[3];
         */
        if (step == 1)
        {
            document.getElementById('back_rep_top').disabled = true;
            document.getElementById('back_rep_top2').disabled = true;
        }
        else
        {
            document.getElementById('back_rep_top').disabled = false;
            document.getElementById('back_rep_top2').disabled = false;
        }
        /* REPORT TYPE REMOVED
         if((gotoStep == "step4" && (objreportType.value != 'tabular')) || gotoStep != "step4")
         {
         */
        
        // ITS4YOU-CR SlOl 2. 5. 2013 14:00:25
        if (gotoStep == 'step10')
        {
            hideAllSteps(gotoStep,step);
            showRecipientsOptions();
            show(gotoStep);
        }else{
        // ITS4YOU-END
            var newurl = 'action=ITS4YouReportsAjax&mode=ajax&file=ChangeSteps&module=ITS4YouReports';
            newurl += '&step=' + gotoStep;
            newurl += '&record=' + document.NewReport.record.value;
            
            var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
            var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

            newurl += '&reportmodule=' + selectedPrimaryModule;
            newurl += '&primarymodule=' + selectedPrimaryModule;
            var reportname = encodeURIComponent(document.getElementById('reportname').value);
            newurl += '&reportname=' + reportname;
            var reportdesc = encodeURIComponent(document.getElementById('reportdesc').value);
            newurl += '&reportdesc=' + reportdesc;
            // newurl += '&selectedreporttype='+objreportType.value;

            // ITS4YOU-UP SlOl 26. 4. 2013 11:16:28 SHARING
            newurl += '&template_owner=' + document.getElementById('template_owner').value;
            var sharing = document.getElementById('sharing').value;
            newurl += '&sharing=' + sharing;

            var sharingSelectedStr = getUpSelectedSharing();
            newurl += '&sharingSelectedColumns=' + sharingSelectedStr;
            // ITS4YOU-END

            var relatedmodules = '';
            var all_related_modules_str = document.getElementById('all_related_modules').value;
            if (all_related_modules_str != '') {
                var all_related_modules = all_related_modules_str.split(":");
                for (i = 0; i <= (all_related_modules.length - 1); i++)
                {
                    var rel_mod_actual = 'relmodule_' + all_related_modules[i];
                    actual_rel_module = document.getElementById(rel_mod_actual);
                    if (actual_rel_module.checked)
                        relatedmodules += actual_rel_module.value + ':';
                }
            }
            newurl += '&relatedmodules=' + relatedmodules;

            var selectedFolderIndex = document.getElementById('reportfolder').selectedIndex;
            var selectedFolderValue = document.getElementById('reportfolder').options[selectedFolderIndex].value;
            newurl += '&folderid=' + selectedFolderValue;

            // ITS4YOU-END 15. 4. 2013 11:27:09
            var curl = getCurl();
            newurl += '&curl=' + curl;

            // ITS4YOU-CR SlOl 12. 3. 2014 14:22:00
            var lblurl = getLabelsurl();
            newurl += '&lblurl=' + lblurl;

            // GROUP 1
            var selectedGroup1 = document.getElementById('Group1');
            var selectedGroup1Value = document.getElementById('Group1').options[selectedGroup1.selectedIndex].value;
            newurl += '&group1=' + selectedGroup1Value;
            var radio1a = document.getElementById('Sort1a');
            var radio1d = document.getElementById('Sort1d');
            var return_sort1 = radio1a.checked ? radio1a.value : radio1d.value;
            newurl += '&sort1=' + return_sort1;

            // GROUP 2
            var selectedGroup2 = document.getElementById('Group2');
            var selectedGroup2Value = document.getElementById('Group2').options[selectedGroup2.selectedIndex].value;
            newurl += '&group2=' + selectedGroup2Value;
            var radio2a = document.getElementById('Sort2a');
            var radio2d = document.getElementById('Sort2d');
            var return_sort2 = radio2a.checked ? radio2a.value : radio2d.value;
            newurl += '&sort2=' + return_sort2;

            // GROUP 3
            var selectedGroup3 = document.getElementById('Group3');
            var selectedGroup3Value = document.getElementById('Group3').options[selectedGroup3.selectedIndex].value;
            newurl += '&group3=' + selectedGroup3Value;
            var radio3a = document.getElementById('Sort3a');
            var radio3d = document.getElementById('Sort3d');
            var return_sort3 = radio3a.checked ? radio3a.value : radio3d.value;
            newurl += '&sort3=' + return_sort3;

            // LIMIT AND SCHEDULER START
            newurl += '&limit=' + encodeURIComponent(document.getElementById('limit').value);
            newurl += '&isReportScheduled=' + document.getElementById('isReportScheduled').checked;
            
            newurl += '&summaries_limit=' + encodeURIComponent(document.getElementById('summaries_limit').value);

            var scheduledTypeColumns = document.getElementById('scheduledType');
            var scheduledTypeSelectedStr = "";
            for (i = 0; i <= (scheduledTypeColumns.length - 1); i++)
            {
                if (scheduledTypeColumns[i].selected == true)
                    scheduledTypeSelectedStr += scheduledTypeColumns[i].value;
            }
            newurl += '&scheduledTypeSelectedStr=' + scheduledTypeSelectedStr;

            var scheduledInterval = {scheduletype: document.NewReport.scheduledType.value,
                month: document.NewReport.scheduledMonth.value,
                date: document.NewReport.scheduledDOM.value,
                day: document.NewReport.scheduledDOW.value,
                time: document.NewReport.scheduledTime.value
            };

            var scheduledIntervalJson = JSON.stringify(scheduledInterval);
            newurl += '&scheduledIntervalJson=' + scheduledIntervalJson;

            var scheduledReportFormatValue = '';
            var scheduledReportFormat = document.getElementById('scheduledReportFormat');
            for (i = 0; i <= (scheduledReportFormat.length - 1); i++)
            {
                if (scheduledReportFormat[i].selected == true)
                    scheduledReportFormatValue += scheduledReportFormat[i].value;
            }
            newurl += '&scheduledReportFormat=' + scheduledReportFormatValue;

            var selectedRecipientsObj = document.getElementById('selectedRecipients');
            var selectedUsers = new Array();
            var selectedGroups = new Array();
            var selectedRoles = new Array();
            var selectedRolesAndSub = new Array();
            for (i = 0; i < selectedRecipientsObj.options.length; i++) {
                var selectedCol = selectedRecipientsObj.options[i].value;
                var selectedColArr = selectedCol.split("::");
                if (selectedColArr[0] == "users")
                    selectedUsers.push(selectedColArr[1]);
                else if (selectedColArr[0] == "groups")
                    selectedGroups.push(selectedColArr[1]);
                else if (selectedColArr[0] == "roles")
                    selectedRoles.push(selectedColArr[1]);
                else if (selectedColArr[0] == "rs")
                    selectedRolesAndSub.push(selectedColArr[1]);
            }

            var selectedColumns = document.getElementById('selectedColumns');
            var selectedColumnsStr = "";
            for (i = 0; i < selectedColumns.options.length; i++) {
                if (selectedColumnsStr != "") {
                    selectedColumnsStr += "<_@!@_>";
                }
                selectedColumnsStr += selectedColumns.options[i].value;
            }
            newurl += '&selectedColumnsStr=' + encodeURIComponent(selectedColumnsStr);

            var selectedRecipients = {users: selectedUsers, groups: selectedGroups,
                roles: selectedRoles, rs: selectedRolesAndSub};
            newurl += '&selectedRecipientsStr=' + JSON.stringify(selectedRecipients);

            // ITS4YOU-CR SlOl 6. 3. 2014 9:29:42 SUMMARIES COLUMNS
            formSelectedSummariesString();
            var selectedSummariesString = document.getElementById("selectedSummariesString").value;
            newurl += '&selectedSummariesString=' + selectedSummariesString;

            var TimeLineColumn_Group1 = getGroupTimeLineValue(1);
            newurl += '&timeline_column1=' + TimeLineColumn_Group1;
            var TimeLineColumn_Group2 = getGroupTimeLineValue(2);
            newurl += '&timeline_column2=' + TimeLineColumn_Group2;
            newurl += '&timeline_type2=' + getGroupTimeLineType(2);
            var TimeLineColumn_Group3 = getGroupTimeLineValue(3);
            newurl += '&timeline_column3=' + TimeLineColumn_Group3;
            newurl += '&timeline_type3=' + getGroupTimeLineType(3);

            if (document.getElementById('summaries_orderby_column')) {
                var summaries_orderby_selectbox = document.getElementById('summaries_orderby_column');
                var summaries_orderby = summaries_orderby_selectbox.options[summaries_orderby_selectbox.selectedIndex].value;
                var summaries_orderby_type = "";
                var summaries_orderby_Radios = document.getElementsByName('summaries_orderby_type');
                for (var i = 0, length = summaries_orderby_Radios.length; i < length; i++) {
                    if (summaries_orderby_Radios[i].checked) {
                        summaries_orderby_type = summaries_orderby_Radios[i].value;
                        break;
                    }
                }
                if (summaries_orderby_type == "") {
                    summaries_orderby_type = "ASC";
                }
                newurl += '&summaries_orderby=' + summaries_orderby;
                newurl += '&summaries_orderby_type=' + summaries_orderby_type;
            }
            // ITS4YOU-END 21. 3. 2014 14:31:43
            /* ITS4YOU-CR SlOl | 2.7.2014 15:00 */
            if(document.getElementById('chartType')){
                var chart_type = document.getElementById('chartType').options[document.getElementById('chartType').selectedIndex].value;
                if(chart_type !== "none"){
                    newurl += '&chart_type=' + chart_type;
                    var data_series = document.getElementById('data_series').options[document.getElementById('data_series').selectedIndex].value;
                    newurl += '&data_series=' + data_series;
                    var charttitle = encodeURIComponent(document.getElementById('charttitle').value);
                    newurl += '&charttitle=' + charttitle;
                }
            }
            /* ITS4YOU-END 2.7.2014 15:00 */

// see Edit.js
            new Ajax.Request('index.php',
                    {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
                        postBody: newurl,
                        onComplete: function(response){
                            hideAllSteps(gotoStep,step);
                            
                            if (gotoStep === 'step5')
                            {
                                document.getElementById('step5').style.display = 'block';
                                if (document.getElementById('availList'))
                                {
                                    document.getElementById('availList').innerHTML = "";
                                    document.getElementById('availList').options.length = 0;
                                }

                                var step5_result = response.responseText.split("(!A#V_M@M_M#A!)");
                                setStep5Columns(step5_result);
                            }
                            else if (gotoStep === 'step8')
                            {
                                //if (document.getElementById('mode').value == 'create') {
        							document.getElementById('step8').style.display = 'block';
	
	                                var resp_info = response.responseText.split("__ADVFTCRI__");
	
	                                var resp_blocks_info = resp_info[0];
	                                var criteria_info = JSON.parse(resp_info[1]);
	                                document.getElementById("sel_fields").value = resp_info[2];
	                                document.getElementById("std_filter_columns").value = resp_info[3];
	                                document.getElementById("rel_fields").value = resp_info[4];
									// var rel_fields = JSON.parse(resp_info[4]);
	                                
	                                var resp_blocks = resp_blocks_info.split("__BLOCKS__");
	                                var FIELD_BLOCKS = resp_blocks[0];
	                                document.getElementById('filter_columns').innerHTML = FIELD_BLOCKS;
	                                var aviable_fields = FIELD_BLOCKS;
	                                var group_fields = FIELD_BLOCKS;
	
	                                checkEmptyAddFirstCondition();
	                                
	                                var sum_group_columns = resp_blocks[1];
	                                document.getElementById('sum_group_columns').innerHTML = sum_group_columns;
	
	                                var sortbycolumns = document.getElementById('SortByColumn').innerHTML;
	                                var optgroups = FIELD_BLOCKS.split("(|@!@|)");
	                                s = document.NewReport.getElementsByTagName('select');
	
	                                for (var i = 0; i < s.length; i++)
	                                {
	                                    if (s[i].name.substring(0, 4) == 'fcol')
	                                    {
	                                        // addNewConditionGroup('adv_filter_div');
	                                        var criteria_i = s[i].name.substring(4, 5);
	                                        if(criteria_info!=null){
	                                            var selected_column = criteria_info[criteria_i]['columnname'];
	                                    		var selected_comparator = criteria_info[criteria_i]['comparator'];
	                                    		// ITS4YOU-CR SlOl 17. 9. 2014 11:18:35
	                                    		var foptions_obj = document.getElementById('fop'+criteria_i);
	                                    		if(foptions_obj){
													for (var fopi = 0; fopi < foptions_obj.options.length; fopi++)
					                                {
					                                	if(foptions_obj.options[fopi].value==selected_comparator){
															foptions_obj.options[fopi].selected = true;
														}
													}
												}
												// ITS4YOU-END 17. 9. 2014 11:18:38
	                                        }
	                                        var oldvalue = s[i].value;
	                                        s[i].options.length = 0;
	                                        s[i].innerHTML = "";
	                                        for (k = 0; k < optgroups.length; k++)
	                                        {
	                                            var optgroup = optgroups[k].split("(|@|)");
	                                            if (optgroup[0] != '')
	                                            {
	                                                var oOptgroup = document.createElement("OPTGROUP");
	                                                oOptgroup.label = optgroup[0];
	
	                                                var responseVal = JSON.parse(optgroup[1]);
	
	                                                for (var widgetId in responseVal)
	                                                {
	                                                    if (responseVal.hasOwnProperty(widgetId))
	                                                    {
	                                                        option = responseVal[widgetId];
	
	                                                        var oOption = document.createElement("OPTION");
	                                                        oOption.value = option["value"];
	                                                        if(option["value"] == selected_column){
	                                                            oOption.selected = true;
	                                                        }
	                                                        oOption.appendChild(document.createTextNode(option["text"]));
	                                                        oOptgroup.appendChild(oOption);
	
	                                                    }
	                                                }
	                                                s[i].appendChild(oOptgroup);
	                                            }
	                                        }
	                                        var std_filter_columns = document.getElementById("std_filter_columns").value;
	
	                                        if (std_filter_columns!=""){
	                                            var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
	                                        }else{
	                                            var std_filter_columns_arr = new Array();
	                                        }
	                                        /*var selectedVal = "";
	                                        if(typeof document.getElementById('fop'+criteria_i) != "undefined"){
	                                            if (std_filter_columns_arr.indexOf(selected_column) > -1) {
	                                                selectedVal = document.getElementById('fop'+criteria_i).options[document.getElementById('fop'+criteria_i).selectedIndex].value;
	                                            }
	                                        }*/
	                                        reports4you_updatefOptions(s[i], 'fop'+criteria_i, selected_comparator);
	
	                                        s[i].value = oldvalue;
	                                    }
	                                    else if (s[i].name.substring(0, 4) == 'gcol')
	                                    {
	                                        oldvalue = s[i].value;
	
	                                        oldselectvalue = getObj("SortByColumn").value;
	
	                                        if (browser_ie)
	                                        {
	                                            selectonchange = s[i].onchange;
	                                            s[i].outerHTML = '<select id="' + s[i].id + '" name="' + s[i].name + '" class="detailedViewTextBox">' + sortbycolumns + '</select>';
	                                            s[i].onchange = selectonchange;
	                                        }
	                                        else
	                                        {
	                                            s[i].innerHTML = sortbycolumns;
	                                        }
	
	
	                                        s[i].value = oldvalue;
	                                    }
	                                }
								//}
                            }
                            else if (gotoStep == 'step11')
                            {
                                document.getElementById(gotoStep).innerHTML = response.responseText;
                            }
                            else
                            {
                                document.getElementById(gotoStep).innerHTML = response.responseText;
                            }
                            show(gotoStep);
                            if (sharing == 'share') {
                                sharing_changed();
                            }
                            setSelSummSortOrder(summaries_orderby);
                        }
                    }
            );

            //stdDateFilterField
            if (step == 8)
            {
                setSTDFilter();
            }
        }
        
        if (document.getElementById('mode').value != 'create' || step == 11) {
            document.getElementById('submitbutton').style.display = 'inline';
            document.getElementById('submitbutton2').style.display = 'inline';
        } else {
            document.getElementById('submitbutton').style.display = 'none';
            document.getElementById('submitbutton2').style.display = 'none';
        }
// REPORT TYPE REMOVED
//		}
    }
}

function setSTDFilter(selectedStdFilter){
    var newurl2 = 'action=ITS4YouReportsAjax&mode=ajax&file=ChangeSteps&module=ITS4YouReports';
    newurl2 += '&step=getStdFilter';
    if( (selectedStdFilter) && selectedStdFilter!="" ){
        newurl2 += '&selectedStdFilter='+selectedStdFilter;
    }
    if (document.getElementById('record'))
        var record = document.getElementById('record').value;
    else
        var record = '';
    newurl2 += '&record=' + record;

    if(document.getElementById('primarymodule').type=="text"){
        var selectedPrimaryModule = document.getElementById('primarymodule').value;
    }else if(document.getElementById('primarymodule')!="" && document.getElementById('primarymodule').type!="hidden"){
        var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
        var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;
    }else{
        selectedPrimaryModule = "";
    }
    newurl2 += '&reportmodule=' + selectedPrimaryModule;
    newurl2 += '&primarymodule=' + selectedPrimaryModule;
/*
    var all_related_modules_str = document.getElementById('all_related_modules').value;
    if (all_related_modules_str != '') {
        var all_related_modules = all_related_modules_str.split(":");
        var relatedmodules = '';
        for (i = 0; i <= (all_related_modules.length - 1); i++)
        {
            var rel_mod_actual = 'relmodule_' + all_related_modules[i];
            actual_rel_module = document.getElementById(rel_mod_actual);
            if (actual_rel_module.checked)
                relatedmodules += actual_rel_module.value + ':';
        }
    }
    newurl2 += '&relatedmodules=' + relatedmodules;
*/
    // remove old values
    if (typeof document.NewReport.stdDateFilterField != 'undefined' && document.NewReport.stdDateFilterField!="") {
        var selObj = document.NewReport.stdDateFilterField;
        var nMaxVal = selObj.length;
        for (nLoop = 0; nLoop < nMaxVal; nLoop++)
        {
            selObj.remove(0);
        }
        selObj.options[0] = new Option('None', '');
        selObj.options[0].selected = true;
    }
    // remove end
    if(document.getElementById("current_action")){
        var c_action = document.getElementById("current_action").value;
    }else{
        var c_action = "";
    }
    if(c_action!="" && c_action=="resultGenerate"){
        var jqxhr = jQuery.post( 'index.php?'+newurl2, function(response){
            // alert( "success" );
        })
        .done(function(response){
            var option_values = response.split("#@!@#");
            var responseVal = JSON.parse(option_values[0]);
            var selected_option = option_values[1];
            for (var widgetId in responseVal)
            {
                if (responseVal.hasOwnProperty(widgetId))
                {
                    option = responseVal[widgetId];
                    var oOption = document.createElement("OPTION");
                    oOption.value = option["value"];
                    if (option["value"] == selected_option) {
                        oOption.selected = true;
                    }
                    oOption.appendChild(document.createTextNode(option["text"]));
                    document.NewReport.stdDateFilterField.appendChild(oOption);
                }
            }
        })
    }else{
        new Ajax.Request('index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: newurl2,
                    onComplete: function(response){
                        var option_values = response.responseText.split("#@!@#");
                        var responseVal = JSON.parse(option_values[0]);
                        var selected_option = option_values[1];
                        for (var widgetId in responseVal)
                        {
                            if (responseVal.hasOwnProperty(widgetId))
                            {
                                option = responseVal[widgetId];
                                var oOption = document.createElement("OPTION");
                                oOption.value = option["value"];
                                if (option["value"] == selected_option) {
                                    oOption.selected = true;
                                }
                                oOption.appendChild(document.createTextNode(option["text"]));
                                document.NewReport.stdDateFilterField.appendChild(oOption);
                            }
                        }

                    }
                }
        );
    }
}

// ITS4YOU-CR SlOl 27. 2. 2014 9:52:47
function getQFurl(){
    var c = new Array();
    qfurl = "";
    c = document.getElementsByTagName('input');
    for (var i = 0; i < c.length; i++)
    {
        if (c[i].type == 'checkbox')
        {
            Control_Data = c[i].name.split(':');
            if (Control_Data[0] == "qf" && c[i].checked == true)
            {
                if (qfurl != "") {
                    qfurl += "$_@_$";
                }
                qfurl += c[i].name;
            }
        }
    }
    document.getElementById('qf_to_go').value = qfurl;
    return qfurl;
}
// ITS4YOU-CR SlOl 27. 2. 2014 9:56:38
function getCurl(){
    var c = new Array();
    curl = "";
    c = document.getElementsByTagName('input');
    for (var i = 0; i < c.length; i++)
    {
        if (c[i].type == 'checkbox')
        {
            Control_Data = c[i].name.split(':');
            if (Control_Data[0] == "cb" && c[i].checked == true)
            {
                if (curl != "") {
                    curl += "$_@_$";
                }
                curl += c[i].name;
            }
        }
    }
    document.getElementById('curl_to_go').value = curl;
    return curl;
}
// ITS4YOU-END 27. 2. 2014 9:52:49
// ITS4YOU-CR SlOl 27. 2. 2014 9:52:47
function getLabelsurl(){
    var c = new Array();
    var lblurl = "";
    c = document.getElementsByTagName('input');
    for (var i = 0; i < c.length; i++)
    {
        if(c[i]){
            var id_text = c[i].id;
            var search_for_lc = "_lLbLl_";
            if (id_text.indexOf(search_for_lc) > -1 && id_text.indexOf("hidden_") == -1) {
                if (lblurl != "") {
                    lblurl += "$_@_$";
                }
                var str_lblurl = c[i].id + "_lLGbGLl_" + c[i].value;
                // str_lblurl = replaceAll("&","@AMPKO@",str_lblurl);
                str_lblurl = encodeURIComponent(str_lblurl);
                lblurl += str_lblurl;
            }
        }
    }
    document.getElementById('labels_to_go').value = lblurl;

    return lblurl;
}
// ITS4YOU-END 12. 3. 2014 14:12:17

function setRelModules(relMod){
    var relModArr = document.NewReport.relatedmodules.value.split(':');
    var retstring = Array();
    if (!inMyArray(relModArr, relMod)) {
        if (relModArr == '') {
            relModArr[0] = relMod;
        } else {
            relModArr.push(relMod);
        }
        retstring = relModArr;
    } else {
        for (i = 0; i < relModArr.length; i++) {
            if (relModArr[i] != relMod) {
                retstring.push(relModArr[i]);
            }
        }

    }
    document.NewReport.relatedmodules.value = retstring.join(':');
    return false;
}

function inMyArray(myarray, myvalue){
    var i;
    for (i = 0; i < myarray.length; i++) {
        if (myarray[i] == myvalue) {
            return true;
        }
    }
    return false;
}

function changeSteps1(){
    if (getObj('step4').style.display != 'none')
    {
        var escapedOptions = new Array('account_id', 'contactid', 'contact_id', 'product_id', 'parent_id', 'campaignid', 'potential_id', 'assigned_user_id1', 'quote_id', 'accountname', 'salesorder_id', 'vendor_id', 'time_start', 'time_end', 'lastname');

        var conditionColumns = vt_getElementsByName('tr', "conditionColumn");
        var criteriaConditions = [];
        for (var i = 0; i < conditionColumns.length; i++) {

            var columnRowId = conditionColumns[i].getAttribute("id");
            var columnRowInfo = columnRowId.split("_");
            var columnGroupId = columnRowInfo[1];
            var columnIndex = columnRowInfo[2];

            var columnId = "fcol" + columnIndex;
            var columnObject = getObj(columnId);
            var selectedColumn = trim(columnObject.value);
            var selectedColumnIndex = columnObject.selectedIndex;
            var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;

            var comparatorId = "fop" + columnIndex;
            var comparatorObject = getObj(comparatorId);
            var comparatorValue = trim(comparatorObject.value);

            var valueId = "fval" + columnIndex;
            var valueObject = getObj(valueId);
            var specifiedValue = trim(valueObject.value);

            var extValueId = "fval_ext" + columnIndex;
            var extValueObject = getObj(extValueId);
            if (extValueObject) {
                extendedValue = trim(extValueObject.value);
            }

            var glueConditionId = "fcon" + columnIndex;
            var glueConditionObject = getObj(glueConditionId);
            var glueCondition = '';
            if (glueConditionObject) {
                glueCondition = trim(glueConditionObject.value);
            }

            if (!emptyCheck4You(columnId, " Column ", "text"))
                return false;
            if (!emptyCheck4You(comparatorId, selectedColumnLabel + " Option", "text"))
                return false;

            var col = selectedColumn.split(":");
            if (escapedOptions.indexOf(col[3]) == -1) {
                if (col[4] == 'T') {
                    var datime = specifiedValue.split(" ");
                    if (!re_dateValidate(datime[0], selectedColumnLabel + " (Current User Date Time Format)", "OTH"))
                        return false
                    if (datime.length > 1)
                        if (!re_patternValidate(datime[1], selectedColumnLabel + " (Time)", "TIMESECONDS"))
                            return false
                }
                else if (col[4] == 'D')
                {
                    if (!dateValidate(valueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                        return false
                    if (extValueObject) {
                        if (!dateValidate(extValueId, selectedColumnLabel + " (Current User Date Format)", "OTH"))
                            return false
                    }
                } else if (col[4] == 'I')
                {
                    if (!intValidate(valueId, selectedColumnLabel + " (Integer Criteria)" + i))
                        return false
                } else if (col[4] == 'N')
                {
                    if (!numValidate(valueId, selectedColumnLabel + " (Number) ", "any", true))
                        return false
                } else if (col[4] == 'E')
                {
                    if (!patternValidate(valueId, selectedColumnLabel + " (Email Id)", "EMAIL"))
                        return false
                }
            }

            //Added to handle yes or no for checkbox fields in reports advance filters. 
            if (col[4] == "C") {
                if (specifiedValue == "1")
                    specifiedValue = getObj(valueId).value = 'yes';
                else if (specifiedValue == "0")
                    specifiedValue = getObj(valueId).value = 'no';
            }
            if (extValueObject && extendedValue != null && extendedValue != '')
                specifiedValue = specifiedValue + ',' + extendedValue;

            criteriaConditions[columnIndex] = {"groupid": columnGroupId,
                "columnname": selectedColumn,
                "comparator": comparatorValue,
                "value": specifiedValue,
                "column_condition": glueCondition
            };
        }

        $('advft_criteria').value = JSON.stringify(criteriaConditions);

        var conditionGroups = vt_getElementsByName('div', "conditionGroup");
        var criteriaGroups = [];
        for (var i = 0; i < conditionGroups.length; i++) {
            var groupTableId = conditionGroups[i].getAttribute("id");
            var groupTableInfo = groupTableId.split("_");
            var groupIndex = groupTableInfo[1];

            var groupConditionId = "gpcon" + groupIndex;
            var groupConditionObject = getObj(groupConditionId);
            var groupCondition = '';
            if (groupConditionObject) {
                groupCondition = trim(groupConditionObject.value);
            }
            criteriaGroups[groupIndex] = {"groupcondition": groupCondition};

        }
        $('advft_criteria_groups').value = JSON.stringify(criteriaGroups);

        var date1 = getObj("startdate")
        var date2 = getObj("enddate")

        //# validation added for date field validation in final step of report creation
        if ((date1.value != '') || (date2.value != ''))
        {

            if (!dateValidate("startdate", "Start Date", "D"))
                return false

            if (!dateValidate("enddate", "End Date", "D"))
                return false

            if (!dateComparison("startdate", 'Start Date', "enddate", 'End Date', 'LE'))
                return false;
        }

    }
    if (getObj('step12').style.display != 'none') {
        saveAndRunReport();
    } else {
        for (i = 0; i < divarray.length; i++) {
            if (getObj(divarray[i]).style.display != 'none') {
                if (i == 1 && selectedColumnsObj.options.length == 0) {
                    deleteColumnRow(1, 0);
                    /*alert(alert_arr.COLUMNS_CANNOT_BE_EMPTY);
                     return false;*/
                }
                if (divarray[i] == 'step4') {
                    document.getElementById("next").value = finish_text;
                }
                hide(divarray[i]);
                show(divarray[i + 1]);
                tableid = divarray[i] + 'label';
                newtableid = divarray[i + 1] + 'label';
                getObj(tableid).className = 'dvtUnSelectedCell';
                getObj(newtableid).className = 'dvtSelectedCell';
                break;
            }
        }
    }
}
function changeStepsback1(){
    if (getObj('step1').style.display != 'none')
    {
        document.NewReport.action.value = 'NewReport0';
        // ITS4YOU-END SlOl
        document.NewReport.submit();
    } else
    {
        for (i = 0; i < divarray.length; i++)
        {
            if (getObj(divarray[i]).style.display != 'none')
            {
                document.getElementById("next").value = next_text + '>';
                hide(divarray[i]);
                show(divarray[i - 1]);
                tableid = divarray[i] + 'label';
                newtableid = divarray[i - 1] + 'label';
                getObj(tableid).className = 'settingsTabList';
                getObj(newtableid).className = 'settingsTabSelected';
                break;
            }

        }
    }
}
function changeSteps(savetype){
    if(!savetype || savetype==""){
        savetype = "Save&Run";
    }
    var report_name_val = document.getElementById('reportname').value;

    if (report_name_val == "")
    {
        alert(alert_arr.MISSING_REPORT_NAME);
        return false;
    }
    else
    {
        // ITS4YOU-CR SlOl 9. 9. 2013 13:36:29 scheduler
        var isScheduledObj = getObj("isReportScheduled");
        if (isScheduledObj.checked == true) {
            var selectedRecipientsObj = getObj("selectedRecipients");
            /*
            if (selectedRecipientsObj.options.length == 0) {
                alert(alert_arr.RECIPIENTS_CANNOT_BE_EMPTY);
                return false;
            }

            var selectedUsers = new Array();
            var selectedGroups = new Array();
            var selectedRoles = new Array();
            var selectedRolesAndSub = new Array();
            for (i = 0; i < selectedRecipientsObj.options.length; i++) {
                var selectedCol = selectedRecipientsObj.options[i].value;
                var selectedColArr = selectedCol.split("::");
                if (selectedColArr[0] == "users")
                    selectedUsers.push(selectedColArr[1]);
                else if (selectedColArr[0] == "groups")
                    selectedGroups.push(selectedColArr[1]);
                else if (selectedColArr[0] == "roles")
                    selectedRoles.push(selectedColArr[1]);
                else if (selectedColArr[0] == "rs")
                    selectedRolesAndSub.push(selectedColArr[1]);
            }

            var selectedRecipients = {users: selectedUsers, groups: selectedGroups,
                roles: selectedRoles, rs: selectedRolesAndSub};
            var selectedRecipientsJson = JSON.stringify(selectedRecipients);
            document.NewReport.selectedRecipientsString.value = selectedRecipientsJson;
            */

            var scheduledInterval = {scheduletype: document.NewReport.scheduledType.value,
                month: document.NewReport.scheduledMonth.value,
                date: document.NewReport.scheduledDOM.value,
                day: document.NewReport.scheduledDOW.value,
                time: document.NewReport.scheduledTime.value
            };

            var scheduledIntervalJson = JSON.stringify(scheduledInterval);
            document.NewReport.scheduledIntervalString.value = scheduledIntervalJson;

            curl = "";
            c = document.getElementsByTagName('input');
            for (var i = 0; i < c.length; i++)
            {
                if (c[i].type == 'checkbox')
                {
                    Control_Data = c[i].name.split(':');
                    if (Control_Data[0] == "cb" && c[i].checked == true)
                    {
                        if (curl != "") {
                            curl += "$_@_$";
                        }
                        curl += c[i].name;
                    }
                }
            }
            document.NewReport.curl_to_go.value = curl;
        }
        // ITS4YOU-END 9. 9. 2013 13:36:34
        // /* ITS4YOU-CR SlOl | 23.6.2014 15:04  */
        getUpSelectedSharing();
        /* ITS4YOU-END 23.6.2014 15:04  */
        if(document.getElementById('summaries_orderby_column')){
            var summaries_orderby_selectbox = document.getElementById('summaries_orderby_column');
            var summaries_orderby = summaries_orderby_selectbox.options[summaries_orderby_selectbox.selectedIndex].value;
            setSelSummSortOrder(summaries_orderby);
        }
        // ITS4YOU-CR SlOl 4. 3. 2014 15:58:22
        if (savetype != "") {
            document.getElementById("SaveType").value = savetype;
        }
        // ITS4YOU-END 4. 3. 2014 15:58:23
//		jQuery("#newReport").serialize();
        if (saveAndRunReport()) {
            document.NewReport.submit();
        }
    }
}
function changeStepsback(){
    if (actual_step != 1)
    {
        last_step = actual_step - 1;
        // ITS4YOU SlOl step 2 and step 3 disabled -2 steps
        if (last_step == 3) {
            last_step = last_step - 2;
        }
        /* REPORT TYPE REMOVED
         if (last_step == 4)
         {
         // var objreportType = document.forms.NewReport['reportType'];
         var objreportType = document.getElementsByName('reportType');
         if(objreportType[0].checked == true) objreportType = objreportType[0];
         else if(objreportType[1].checked == true) objreportType = objreportType[1];
         else if(objreportType[2].checked == true) objreportType = objreportType[2];
         else if(objreportType[3].checked == true) objreportType = objreportType[3];
         if (objreportType.value != 'summary' && objreportType.value != 'grouping') last_step--;
         }
         */
        changeSteps4U(last_step);

        if (last_step == 1)
        {
            document.getElementById('back_rep_top').disabled = true;
            document.getElementById('back_rep_top2').disabled = true;
        }
    }
}
function editReport(id){
    var arg = 'index.php?module=ITS4YouReports&action=NewReport&record=' + id + '&mode=edit';
    document.location.href = arg;
}
function CreateReport(module){
    var arg = 'index.php?module=ITS4YouReports&action=NewReport&folder=' + gcurrepfolderid + '&reportmodule=' + module + '&primarymodule=' + module + '&mode=create';
    document.location.href = arg;
}
function fnPopupWin(winName){
    window.open(winName, "ReportWindow", "width=790px,height=630px,scrollbars=yes");
}
function re_dateValidate(fldval, fldLabel, type){
    if (re_patternValidate(fldval, fldLabel, "DATE") == false)
        return false;
    dateval = fldval.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements = splitDateVal(dateval)

    dd = dateelements[0]
    mm = dateelements[1]
    yyyy = dateelements[2]

    if (dd < 1 || dd > 31 || mm < 1 || mm > 12 || yyyy < 1 || yyyy < 1000) {
        alert(alert_arr.ENTER_VALID + fldLabel)
        return false
    }

    if ((mm == 2) && (dd > 29)) {//checking of no. of days in february month
        alert(alert_arr.ENTER_VALID + fldLabel)
        return false
    }

    if ((mm == 2) && (dd > 28) && ((yyyy % 4) != 0)) {//leap year checking
        alert(alert_arr.ENTER_VALID + fldLabel)
        return false
    }

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
        case 11 :
            if (dd > 30) {
                alert(alert_arr.ENTER_VALID + fldLabel)
                return false
            }
    }

    var currdate = new Date()
    var chkdate = new Date()

    chkdate.setYear(yyyy)
    chkdate.setMonth(mm - 1)
    chkdate.setDate(dd)

    if (type != "OTH") {
        if (!compareDates(chkdate, fldLabel, currdate, "current date", type)) {
            return false
        } else
            return true;
    } else
        return true;
}

//Copied from general.js and altered some lines. becos we cant send vales to function present in general.js. it accept only field names.
function re_patternValidate(fldval, fldLabel, type){
    if (type.toUpperCase() == "DATE") {//DATE validation 

        switch (userDateFormat) {
            case "yyyy-mm-dd" :
                var re = /^\d{4}(-)\d{1,2}\1\d{1,2}$/
                break;
            case "mm-dd-yyyy" :
            case "dd-mm-yyyy" :
                var re = /^\d{1,2}(-)\d{1,2}\1\d{4}$/
        }
    }


    if (type.toUpperCase() == "TIMESECONDS") {//TIME validation
        var re = new RegExp("^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$");
    }
    if (!re.test(fldval)) {
        alert(alert_arr.ENTER_VALID + fldLabel)
        return false
    }
    else
        return true
}

//added to fix the ticket #5117
function standardFilterDisplay(){
    var stdDateFilterField = document.getElementById('stdDateFilterField');
    if (document.getElementById('stdDateFilterField')) {
        var stdDateFilterFieldIndex = stdDateFilterField.selectedIndex;
        var stdDateFilterFieldValue = stdDateFilterField.options[stdDateFilterFieldIndex].value;

        if (stdDateFilterField.options.length <= 0 || (stdDateFilterField.selectedIndex > -1 && stdDateFilterField.options[stdDateFilterField.selectedIndex].value == "Not Accessible"))
        {
            getObj('stdDateFilter').disabled = true;
            getObj('startdate').disabled = true;
            getObj('enddate').disabled = true;
            getObj('jscal_trigger_date_start').style.visibility = "hidden";
            getObj('jscal_trigger_date_end').style.visibility = "hidden";
        }
        else
        {
            getObj('stdDateFilter').disabled = false;
            getObj('startdate').disabled = false;
            getObj('enddate').disabled = false;
            getObj('jscal_trigger_date_start').style.visibility = "visible";
            getObj('jscal_trigger_date_end').style.visibility = "visible";
        }
    }
}

function updateRelFieldOptions(sel, opSelName){
    jQuery( document ).ready(function(){
        var selObj = document.getElementById(opSelName);
        if(selObj){
            var fieldtype = null;
            var currOption = selObj.options[selObj.selectedIndex];
            var currField = sel.options[sel.selectedIndex];
            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
            var opSelName_array = opSelName.split("val_");
            var row_i = opSelName_array[1];
            if (sel_fields[currField.value]) {
                var newurl = 'module=ITS4YouReports&action=ITS4YouReportsAjax&mode=ajax&file=getFilterColHtml&sel_fields=' + JSON.stringify(sel_fields[currField.value]) + "&sfield_name=fval" + row_i;
                newurl += '&record=' + document.NewReport.record.value;
                newurl += '&currField=' + currField.value;
//alert(newurl);
//window.open(newurl);
                var filter_criteria_obj = document.getElementById("fop" + row_i);
                var r_sel_fields = document.getElementById("fval" + row_i).value;
                if (typeof r_sel_fields != 'undefined' && r_sel_fields!="") {
                    newurl += '&r_sel_fields=' + r_sel_fields;
                }
                if(document.getElementById("current_action")){
                    var c_action = document.getElementById("current_action").value;
                }else{
                    var c_action = "";
                }

                if(c_action!="" && c_action=="resultGenerate"){
                    var jqxhr = jQuery.post( 'index.php?'+newurl, function(response){
                        // alert( "success" );
                    })
                    .done(function(response){
                        document.getElementById("node3span" + row_i + "_ajx").innerHTML = response;
                        document.getElementById("node3span" + row_i + "_ajx").style.display = "block";
                        document.getElementById("node3span" + row_i + "_st").style.display = "none";
                        document.getElementById("node3span" + row_i + "_djx").style.display = "none";
                        document.getElementById("fval" + row_i).value = "";
                    })
                    /*.always(function(response){
                    alert( response );
                    });*/
                }else{
                    new Ajax.Request('index.php',
                            {queue: {position: 'end', scope: 'command'},
                                method: 'post',
                                postBody: newurl,
                                onComplete: function(response){
                                    document.getElementById("node3span" + row_i + "_ajx").innerHTML = response.responseText;
                                    document.getElementById("node3span" + row_i + "_ajx").style.display = "block";
                                    document.getElementById("fval" + row_i).value = "";
                                    document.getElementById("node3span" + row_i + "_st").style.display = "none";
                                    document.getElementById("node3span" + row_i + "_djx").style.display = "none";
                                }
                            }
                    );
                }
            } else {
                document.getElementById("node3span" + row_i + "_ajx").innerHTML = "";
                document.getElementById("node3span" + row_i + "_st").style.display = "block";
                // document.getElementById("fval" + row_i).value = "";
                document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                document.getElementById("node3span" + row_i + "_djx").style.display = "none";
                // ITS4YOU-END 26. 3. 2014 13:25:55
                if (currField.value != null && currField.value.length != 0)
                {
                    fieldtype = trimfValues(currField.value);

                    ops = rel_fields[fieldtype];

                    var off = 0;
                    if (ops != null)
                    {
                        var nMaxVal = selObj.length;
                        for (nLoop = 0; nLoop < nMaxVal; nLoop++)
                        {
                            selObj.remove(0);
                        }
                        selObj.options[0] = new Option('None', '');
                        if (currField.value == '') {
                            selObj.options[0].selected = true;
                        }
                        off = 1;
                        for (var i = 0; i < ops.length; i++)
                        {
                            var field_array = ops[i].split("::");
                            var label = field_array[1];
                            var field = field_array[0];
                            if (label == null)
                                continue;
                            var option = new Option(label, field);
                            selObj.options[i + off] = option;
                            if (currOption != null && currOption.value == option.value)
                            {
                                option.selected = true;
                            }
                        }
                    }
                } else {
                    var nMaxVal = selObj.length;
                    for (nLoop = 0; nLoop < nMaxVal; nLoop++)
                    {
                        selObj.remove(0);
                    }
                    selObj.options[0] = new Option('None', '');
                    if (currField.value == '') {
                        selObj.options[0].selected = true;
                    }
                }
            }
        }

        // std_filter cheching
        var std_filter_columns = document.getElementById("std_filter_columns").value;
        if (typeof std_filter_columns != 'undefined' && std_filter_columns!=""){
            var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
            if (std_filter_columns_arr.indexOf(sel.value) > -1) {
                var r_sel_fields = document.getElementById("fval" + row_i).value;
                document.getElementById("fval" + row_i).value = "";
                getFilterDateHtml(row_i,r_sel_fields);
            }else{
                document.getElementById("node3span" + row_i + "_djx").innerHTML = "";
                if(sel_fields[currField.value]){
                    document.getElementById("node3span" + row_i + "_ajx").style.display = "block";
                    document.getElementById("node3span" + row_i + "_st").style.display = "none";
                }else{
                    document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                    document.getElementById("node3span" + row_i + "_st").style.display = "block";
                }
                // document.getElementById("fval" + row_i).value = "";
                document.getElementById("node3span" + row_i + "_djx").style.display = "none";
            }
        }
    });
}

function getFilterDateHtml(row_i,r_sel_fields){
    var node3span_obj = document.getElementById("node3span"+row_i+"_st");
    
    var newurl = 'module=ITS4YouReports&action=ITS4YouReportsAjax&mode=ajax&file=getFilterDateHtml&columnIndex=' + row_i;
    newurl += '&record=' + document.NewReport.record.value;
    
    if (typeof r_sel_fields != 'undefined' && r_sel_fields!="") {
        newurl += '&r_sel_fields=' + r_sel_fields;
    }
    if (typeof document.getElementById("fop"+row_i) != 'undefined') {
        var seOption_type = document.getElementById("fop"+row_i).value;
		newurl += '&fop_type=' + seOption_type;
    }

    if(document.getElementById("current_action")){
        var c_action = document.getElementById("current_action").value;
    }else{
        var c_action = "";
    }

    if(c_action!="" && c_action=="resultGenerate"){
        var jqxhr = jQuery.post( 'index.php?'+newurl, function(response){
            // alert( "success" );
        })
        .done(function(response){
            document.getElementById("node3span" + row_i + "_djx").innerHTML = response;
            if(seOption_type=="custom"){
			    setCustomDateFields(row_i);
			}
            document.getElementById("node3span" + row_i + "_st").style.display = "none";
            document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
            document.getElementById("fval" + row_i).value = "";
            document.getElementById("node3span" + row_i + "_djx").style.display = "block";
        })
    }else{
        new Ajax.Request('index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: newurl,
                    onComplete: function(response){
                        document.getElementById("node3span" + row_i + "_djx").innerHTML = response.responseText;
                        if(seOption_type=="custom"){
						    setCustomDateFields(row_i);
						}
                        document.getElementById("node3span" + row_i + "_st").style.display = "none";
                        document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                        document.getElementById("fval" + row_i).value = "";
                        document.getElementById("node3span" + row_i + "_djx").style.display = "block";
                    }
                }
        );
    }
}

function setCustomDateFields(columnindex){
    var timeformat = "%H:%M:%S";
    var dateformat = "%d-%m-%Y";
    Calendar.setup({
        inputField: 'jscal_field_sdate' + columnindex, ifFormat: dateformat , showsTime: false, button: "jscal_trigger_sdate" + columnindex, singleClick: true, step: 1
				});
	Calendar.setup({
		inputField: 'jscal_field_edate' + columnindex, ifFormat: dateformat , showsTime: false, button: "jscal_trigger_edate" + columnindex, singleClick: true, step: 1
				});
}

function AddFieldToFilter(id, sel){
    if (trim(document.getElementById("fval" + id).value) == '') {
        document.getElementById("fval" + id).value = document.getElementById("fval_" + id).value;
    } else {
        document.getElementById("fval" + id).value = document.getElementById("fval" + id).value + "," + document.getElementById("fval_" + id).value;
    }
}
function fnLoadRepValues(tab1, tab2, block1, block2){
    document.getElementById(block1).style.display = 'block';
    document.getElementById(block2).style.display = 'none';
    document.getElementById(tab1).className = 'dvtSelectedCell';
    document.getElementById(tab2).className = 'dvtUnSelectedCell';
}

/**
 * IE has a bug where document.getElementsByName doesnt include result of dynamically created 
 * elements
 */
function vt_getElementsByName(tagName, elementName){
    var inputs = document.getElementsByTagName(tagName);
    var selectedElements = [];
    for (var i = 0; i < inputs.length; i++) {
        if (inputs.item(i).getAttribute('name') == elementName) {
            selectedElements.push(inputs.item(i));
        }
    }
    return selectedElements;
}

function formSelectQFColumnString(){
    var selectedColStr = "";
    selectedQFColumnsObj = getObj("selectedQFColumns");

    for (i = 0; i < selectedQFColumnsObj.options.length; i++)
    {
        selectedColStr += selectedQFColumnsObj.options[i].value + ";";
    }
    document.NewReport.selectedQFColumnsString.value = selectedColStr;
}

function uncheckAll(el){
    selObj = document.getElementById(el);

    for (var i = 0; i < selObj.length; i++)
        selObj.options[i].selected = false;

}
function checkAll(el){
    selObj = document.getElementById(el);

    for (var i = 0; i < selObj.length; i++)
        selObj.options[i].selected = true;

}

// ITS4YOU MaJu customreports
function CreateCustomReport(reporttype){
    var arg = 'index.php?module=ITS4YouReports&action=NewCustomReport&folder=' + gcurrepfolderid + '&reporttype=' + reporttype + '&mode=create';
    document.location.href = arg;
}

function editCustomReport(id, reporttype){
    var arg = 'index.php?module=ITS4YouReports&action=NewCustomReport&record=' + id + '&reporttype=' + reporttype + '&mode=edit';
    document.location.href = arg;
}

function setRelModules(relMod){
    var relModArr = document.NewReport.relatedmodules.value.split(':');
    var retstring = Array();
    if (!inMyArray(relModArr, relMod)) {
        if (relModArr == '') {
            relModArr[0] = relMod;
        } else {
            relModArr.push(relMod);
        }
        retstring = relModArr;
    } else {
        for (i = 0; i < relModArr.length; i++) {
            if (relModArr[i] != relMod) {
                retstring.push(relModArr[i]);
            }
        }
    }
    document.NewReport.relatedmodules.value = retstring.join(':');
    return false;
}

function setScheduleOptions(){

    var stid = document.getElementById('scheduledType').value;
    switch (stid) {
        case "0": // nothing choosen
        case "1": // hourly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'inline';
            break;
        case "2": // daily
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'inline';
            break;
        case "3": // weekly
        case "4": // bi-weekly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'inline';
            document.getElementById('scheduledTimeSpan').style.display = 'inline';
            break;
        case "5": // monthly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'inline';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'inline';
            break;
        case "6": // annually
            document.getElementById('scheduledMonthSpan').style.display = 'inline';
            document.getElementById('scheduledDOMSpan').style.display = 'inline';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'inline';
            break;
    }
}

function emptyCheck4You(fldName, fldLabel, fldType){
    var currObj = getObj(fldName);
    if (fldType == "text") {
        if (currObj.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length == 0) {

            alert(fldLabel + alert_arr.CANNOT_BE_EMPTY)
            try {
                currObj.focus()
            } catch (error) {
                // Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
                // So using the try { } catch(error) { }
            }
            return false
        }
        else {
            return true
        }
    } else if ((fldType == "textarea")
            && (typeof (CKEDITOR) !== 'undefined' && CKEDITOR.intances[fldName] !== 'undefined')) {
        var textObj = CKEDITOR.intances[fldName];
        var textValue = textObj.getData();
        if (trim(textValue) == '' || trim(textValue) == '<br>') {
            alert(fldLabel + alert_arr.CANNOT_BE_NONE);
            return false;
        } else {
            return true;
        }
    } else {
        if (trim(currObj.value) == '') {
            alert(fldLabel + alert_arr.CANNOT_BE_NONE)
            return false
        } else
            return true
    }
}
// ITS4YOU-CR SlOl 19. 2. 2014 13:04:56
function getFieldsOptionsSearch(search_input){
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();
    var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
    var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

    aviable_fields = document.getElementById("availModValues").innerHTML;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];
    
    var availModules = document.getElementById("availModules");
    var selectedModule = availModules.options[availModules.selectedIndex].value;
    if (module_groupid == selectedModule) {
        document.getElementById('availList').innerHTML = "";

        optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++)
        {

            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '')
            {
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);

                for (var widgetId in responseVal)
                {
                    if (responseVal.hasOwnProperty(widgetId))
                    {
                        var option = responseVal[widgetId];
                        var option_text = option["text"];
                        var option_text_lc = option_text.toLowerCase();
                        if (option_text_lc.indexOf(search_for_lc) > -1) {
                            var oOption = document.createElement("OPTION");
                            oOption.value = option["value"];
                            oOption.appendChild(document.createTextNode(option_text));
                            oOptgroup.appendChild(oOption);
                            document.getElementById('availList').appendChild(oOptgroup);
                        }
                    }
                }
            }
        }
        return true;
    }
}
// ITS4YOU-CR SlOl 19. 2. 2014 13:04:56
function getSUMFieldsOptionsSearch(search_input){
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();
    var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
    var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

    aviable_fields = document.getElementById("availSumModValues").innerHTML;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    var AvaiSelectedModules = document.getElementById("SummariesModules");
    var selectedModule = AvaiSelectedModules.options[AvaiSelectedModules.selectedIndex].value;
    var selectedModuleText = AvaiSelectedModules.options[AvaiSelectedModules.selectedIndex].text;
    if (module_groupid == selectedModule) {
        document.getElementById('availListSum').innerHTML = "";

        optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++)
        {

            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '')
            {
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = selectedModuleText + " - " + optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);
                for (var widgetId in responseVal)
                {
                    if (responseVal.hasOwnProperty(widgetId))
                    {
                        var option = responseVal[widgetId];
                        var option_text = option["text"];
                        var option_text_lc = option_text.toLowerCase();
                        if (option_text_lc.indexOf(search_for_lc) > -1) {
                            var oOption = document.createElement("OPTION");
                            oOption.value = option["value"];
                            oOption.appendChild(document.createTextNode(option_text));
                            oOptgroup.appendChild(oOption);
                            if (i == 0) {
                                document.getElementById('availListSum').appendChild(oOption);
                            } else {
                                document.getElementById('availListSum').appendChild(oOptgroup);
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}
// ITS4YOU-CR SlOl 25. 3. 2014 12:01:58
function getTLFieldsOptionsSearch(search_input){
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();

    var aviable_fields_json = document.getElementById("avail_timeline_columns").value;
    var aviable_fields = JSON.parse(aviable_fields_json);
    document.getElementById('timeline_columns').innerHTML = "";
    for (var widgetId in aviable_fields)
    {
        if (aviable_fields.hasOwnProperty(widgetId))
        {
            var oOptgroup = document.createElement("OPTGROUP");
            oOptgroup.label = widgetId;
            for (var avfId in aviable_fields[widgetId])
            {
                if (aviable_fields[widgetId].hasOwnProperty(avfId))
                {
                    var option_text = aviable_fields[widgetId][avfId]['text'];
                    var option_val = aviable_fields[widgetId][avfId]['value'];
                    var option_text_lc = option_text.toLowerCase();
                    if (option_text_lc.indexOf(search_for_lc) > -1) {
                        var oOption = document.createElement("OPTION");
                        oOption.value = option_val;
                        oOption.appendChild(document.createTextNode(option_text));
                        oOptgroup.appendChild(oOption);
                        if (widgetId == 0) {
                            document.getElementById('timeline_columns').appendChild(oOption);
                        } else {
                            document.getElementById('timeline_columns').appendChild(oOptgroup);
                        }
                    }
                }
            }
        }
    }
    return true;
}
// ITS4YOU-CR SlOl 20. 2. 2014 10:05:47
function addOndblclick(vOption){
    if ((vOption.selectedIndex) || vOption.selectedIndex == 0) {
        var selectedOption = vOption.options[vOption.selectedIndex].value;
        addColumn('selectedColumns');
    }
}
// ITS4YOU-CR SlOl 6. 3. 2014 9:43:29
function addOndblclickSUM(vOption){
    if ((vOption.selectedIndex) || vOption.selectedIndex == 0) {
        var selectedOption = vOption.options[vOption.selectedIndex].value;
        addColumn('selectedSummaries');
    }
}
// ITS4YOU-END 19. 2. 2014 13:05:01
function defineModuleFields(availModules){
    var selectedModule = availModules.options[availModules.selectedIndex].value;
    var field_options = document.getElementById("availModValues").innerHTML;
    document.getElementById("search_input").value = "";
    var newurl = 'module=ITS4YouReports&action=ITS4YouReportsAjax&mode=ajax&file=getStep5Columns&selectedmodule=' + selectedModule;
    // tabular = 0 , summary = 1 , grouping = 2 , timeline = 3
    /* REPORT TYPE REMOVED -> this is not usefull anymore
     var objreportType = document.getElementsByName('reportType');
     if(objreportType[0].checked == true) objreportType = objreportType[0];
     else if(objreportType[1].checked == true) objreportType = objreportType[1];
     else if(objreportType[2].checked == true) objreportType = objreportType[2];
     else if(objreportType[3].checked == true) objreportType = objreportType[3];
     newurl += '&selectedreporttype='+objreportType.value;
     */
    var primarymoduleObj = getObj("primarymodule");
    var primarymoduleid = trim(primarymoduleObj.value);
    newurl += "&primarymoduleid="+primarymoduleid;
//alert(newurl)
    new Ajax.Request('index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: newurl,
                onComplete: function(response){
                    aviable_fields = response.responseText;
                    setAvailableFields(aviable_fields);
                }
            }
    );
}
// ITS4YOU-CR SlOl 6. 3. 2014 12:51:10
function defineSUMModuleFields(availModules){
    var selectedModule = availModules.options[availModules.selectedIndex].value;
    field_options = document.getElementById("SummariesModules").innerHTML;
    document.getElementById("search_input_sum").value = "";
    var newurl = 'module=ITS4YouReports&action=ITS4YouReportsAjax&mode=ajax&file=getStep5SUMColumns&selectedmodule=' + selectedModule;
    // tabular = 0 , summary = 1 , grouping = 2 , timeline = 3
    /* REPORT TYPE REMOVED -> this is not usefull anymore
     var objreportType = document.getElementsByName('reportType');
     if(objreportType[0].checked == true) objreportType = objreportType[0];
     else if(objreportType[1].checked == true) objreportType = objreportType[1];
     else if(objreportType[2].checked == true) objreportType = objreportType[2];
     else if(objreportType[3].checked == true) objreportType = objreportType[3];
     newurl += '&selectedreporttype='+objreportType.value;
     */
//alert(newurl)
    new Ajax.Request('index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: newurl,
                onComplete: function(response){
                    aviable_fields = response.responseText;
                    setAvailableSUMFields(aviable_fields);
                }
            }
    );
}
function setStep5Columns(step5_result){
    var availablemodules = JSON.parse(step5_result[0]);
    var aviable_fields = step5_result[1];

    var avaimodules_sbox = document.getElementById('availModules');
    avaimodules_sbox.innerHTML = "";
    avaimodules_sbox.options.length = 0;
    for (var widgetId in availablemodules)
    {
        if (availablemodules.hasOwnProperty(widgetId))
        {
            option = availablemodules[widgetId];
            var oOption = document.createElement("OPTION");
            oOption.value = option["id"];
            if (option["checked"] == "checked") {
                oOption.checked = true;
                var option_name = option["name"];
            } else {
                var option_name = "- " + option["name"];
            }
            oOption.appendChild(document.createTextNode(option_name));
            avaimodules_sbox.appendChild(oOption);
        }
    }
    setAvailableFields(aviable_fields)
}
function setAvailableFields(aviable_fields){
    document.getElementById("availModValues").innerHTML = aviable_fields;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    var availModules = document.getElementById("availModules");
    var selectedModule = availModules.options[availModules.selectedIndex].value;
    if (module_groupid == selectedModule) {
        document.getElementById('availList').innerHTML = "";

        optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++)
        {

            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '')
            {
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);
                for (var widgetId in responseVal)
                {
                    if (responseVal.hasOwnProperty(widgetId))
                    {
                        option = responseVal[widgetId];
                        var oOption = document.createElement("OPTION");
                        oOption.value = option["value"];
                        oOption.appendChild(document.createTextNode(option["text"]));
                        oOptgroup.appendChild(oOption);
                        document.getElementById('availList').appendChild(oOptgroup);
                    }
                }
            }
        }
    }
}
function setAvailableSUMFields(aviable_fields){
    document.getElementById("availSumModValues").innerHTML = aviable_fields;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    SummariesModules = document.getElementById("SummariesModules");
    var selectedModule = SummariesModules.options[SummariesModules.selectedIndex].value;
    var selectedModuleText = SummariesModules.options[SummariesModules.selectedIndex].text;
    if (module_groupid == selectedModule) {
        document.getElementById('availListSum').innerHTML = "";

        optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++)
        {
            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '')
            {
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = selectedModuleText + " - " + optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);

                for (var widgetId in responseVal)
                {
                    if (responseVal.hasOwnProperty(widgetId))
                    {
                        option = responseVal[widgetId];
                        var oOption = document.createElement("OPTION");
                        oOption.value = option["value"];
                        oOption.appendChild(document.createTextNode(option["text"]));
                        oOptgroup.appendChild(oOption);
                        if (i == 0) {
                            document.getElementById('availListSum').appendChild(oOption);
                        } else {
                            document.getElementById('availListSum').appendChild(oOptgroup);
                        }
                    }
                }
            }
        }
    }
}
function checkEmptyLabel(input_id){
    if (document.getElementById(input_id)) {
        str = document.getElementById(input_id).value;
        if (str.trim() == "") {
            document.getElementById(input_id).value = document.getElementById("hidden_" + input_id).value;
        }
    }
}
/* ITS4YOU-CR SlOl | 13.5.2014 12:09 */
function escapeRegExp(string){
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}
function checkTimeLineType(TimelineTypeObj,group_i){
    if(TimelineTypeObj.options[TimelineTypeObj.selectedIndex].value=="cols" && document.getElementById("timeline_type"+group_i).options[document.getElementById("timeline_type"+group_i).selectedIndex].value=="cols"){
        alert(TimelineTypeObj.name+alert_arr.SHOULDNOTBE_EQUAL+" cols");
        TimelineTypeObj.options[0].selected = true;
        return false;
    }
}
function checkTimeLineColumn(groupObj,group_i){
    if(group_i>1){
        if(document.getElementById("Group1").options[document.getElementById("Group1").selectedIndex].value=="none"){
            alert(document.getElementById("Group1").name+alert_arr.CANNOT_BE_EMPTY);
            groupObj.options[0].selected = true;
            return false;
        }
        if(document.getElementById("Group2").options[document.getElementById("Group2").selectedIndex].value=="none"){
            alert(document.getElementById("Group2").name+alert_arr.CANNOT_BE_EMPTY);
            groupObj.options[0].selected = true;
            return false;
        }
    }
    if(groupObj){
        var selected_option = groupObj.options[groupObj.selectedIndex].value;
alert(groupObj.options);
        if(document.getElementById("date_options_json")){
            var date_options_json = document.getElementById("date_options_json").innerHTML;
            if(date_options_json){
                var date_options = JSON.stringify(date_options_json);
                if(date_options.indexOf(selected_option)>0){
                    // timelinecolumn_html to replace @NMColStr , need to replace with col_str and then insert into div id = radio_group1
                    var timelinecolumn_html = document.getElementById('timelinecolumn_html').value;
                    var timelinecolumn_html = replaceAll("@NMColStr", "_Group"+group_i,timelinecolumn_html);

                    var timelinecolumn_html = replaceAll("value='", "value='"+selected_option,timelinecolumn_html);

                    document.getElementById("radio_group"+group_i).innerHTML = timelinecolumn_html;
                }else{
                    document.getElementById("radio_group"+group_i).innerHTML = "";
                }
            }
        }
    }
}
function getGroupTimeLineValue(group_i){
    var timeline_frequency = "";
    if(document.getElementsByName('TimeLineColumn_Group'+group_i)){
        var TimeLineColumnRadios = document.getElementsByName('TimeLineColumn_Group'+group_i);
        for (var i = 0, length = TimeLineColumnRadios.length; i < length; i++) {
            if (TimeLineColumnRadios[i].checked) {
                timeline_frequency = TimeLineColumnRadios[i].value;
                break;
            }
        }
    }
    return timeline_frequency;
}
function getGroupTimeLineType(group_i){
    var timeline_type_val = "";
    if(document.getElementById('timeline_type'+group_i)){
        var timeline_type = document.getElementById('timeline_type'+group_i);
        timeline_type_val = timeline_type.options[timeline_type.selectedIndex].value;
    }
    return timeline_type_val;
}
/* ITS4YOU-END */
/* ITS4YOU-CR SlOl | 23.6.2014 15:01  */
function getUpSelectedSharing(){
    var sharingSelectedStr = "";
    if(document.getElementById('sharingSelectedColumns')){
        var sharingSelectedColumns = document.getElementById('sharingSelectedColumns');
        var sharingSelectedStr = "";
        for (i = 0; i <= (sharingSelectedColumns.length - 1); i++)
        {
            sharingSelectedStr += sharingSelectedColumns[i].value + '|';
        }
        document.getElementById('sharingSelectedColumnsString').value = sharingSelectedStr;
    }
    return sharingSelectedStr;
}
/* ITS4YOU-END 23.6.2014 15:02  */
/* ITS4YOU-CR SlOl | 2.7.2014 11:45 */
function defineChartType(chart_type_element){
    var chart_type_option = chart_type_element.options[chart_type_element.selectedIndex];
    
    for (i = 0; i < chart_type_element.options.length; i++)
    {
        var image_id = chart_type_element.options[i].value+"_type";
        if(document.getElementById(image_id)){
            document.getElementById(image_id).style.display = "none";
        }
    }
    
    if (chart_type_option.value != null && chart_type_option.value.length != 0)
    {
        var chart_type_value = chart_type_option.value;
        var image_id = chart_type_value+"_type";
        setChartColumns(chart_type_option);
        if(document.getElementById(image_id)){
            document.getElementById(image_id).style.display = "block";
        }
    }
}
/* ITS4YOU-CR SlOl | 2.7.2014 13:27 */
function setChartTitle(ch_title_obj){
    var chart_type_element = document.getElementById("chartType");
    var chart_type_option = chart_type_element.options[chart_type_element.selectedIndex];
    if(chart_type_option.value=="none"){
        alert(document.getElementById("none_chart").value);
        return false;
    }
    if(ch_title_obj && ch_title_obj.value!=""){
        if(document.getElementById("chart_title_div")){
            document.getElementById("chart_title_div").innerHTML = ch_title_obj.value;
        }
    }
}
function setChartColumns(chart_type_option){
    if(chart_type_option == "pie" || chart_type_option == "funnel"){
        document.getElementById("ycols").innerHTML = "&nbsp";
        document.getElementById("xcols").innerHTML = "&nbsp";
    }
}
/* ITS4YOU-CR SlOl | 22.7.2014 13:45 */
function Reports4YouDateValidate(fldName,fldLabel,type){
	if(patternValidate(fldName,fldLabel,"DATE")==false)
		return false;
	dateval=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

	var dateelements=splitDateVal(dateval)

	dd=dateelements[0]
	mm=dateelements[1]
	yyyy=dateelements[2]

	if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
		alert(alert_arr.ENTER_VALID+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}

	if ((mm==2) && (dd>29)) {//checking of no. of days in february month
		alert(alert_arr.ENTER_VALID+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}

	if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
		alert(alert_arr.ENTER_VALID+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}

	switch (parseInt(mm)) {
		case 2 :
		case 4 :
		case 6 :
		case 9 :
		case 11 :
			if (dd>30) {
			alert(alert_arr.ENTER_VALID+fldLabel)
			try {
				getObj(fldName).focus()
			} catch(error) { }
			return false
		}
	}

	var currdate=new Date()
	var chkdate=new Date()

	chkdate.setYear(yyyy)
	chkdate.setMonth(mm-1)
	chkdate.setDate(dd)

	if (type!="OTH") {
		if (!compareDates(chkdate,fldLabel,currdate,"current date",type)) {
			try {
				getObj(fldName).focus()
			} catch(error) { }
			return false
		} else return true;
	} else return true;
}
function Reports4Youre_dateValidate(fldval,fldLabel,type){
    if(re_patternValidate(fldval,fldLabel,"DATE")==false)
        return false;
    dateval=fldval.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements=splitDateVal(dateval)

    dd=dateelements[0]
    mm=dateelements[1]
    yyyy=dateelements[2]

    if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
        alert(alert_arr.ENTER_VALID+fldLabel)
        return false
    }

    if ((mm==2) && (dd>29)) {//checking of no. of days in february month
        alert(alert_arr.ENTER_VALID+fldLabel)
        return false
    }

    if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
        alert(alert_arr.ENTER_VALID+fldLabel)
        return false
    }

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
        case 11 :
            if (dd>30) {
            alert(alert_arr.ENTER_VALID+fldLabel)
            return false
        }
    }

    var currdate=new Date()
    var chkdate=new Date()

    chkdate.setYear(yyyy)
    chkdate.setMonth(mm-1)
    chkdate.setDate(dd)

    if (type!="OTH") {
        if (!compareDates(chkdate,fldLabel,currdate,"current date",type)) {
            return false
        } else return true;
    } else return true;
}
function Reports4YouStDateValidate(columnIndex, comparatorValue, fldLabel){
    if (typeof document.getElementById("jscal_field_sdate"+columnIndex) != 'undefined' && typeof document.getElementById("jscal_field_edate"+columnIndex) != 'undefined'){
        switch (comparatorValue) {
            case "custom" :
                if (!emptyCheck4You("jscal_field_sdate"+columnIndex, fldLabel, "date")){
                    return false;
                }
                if (!emptyCheck4You("jscal_field_edate"+columnIndex, fldLabel, "date")){
                    return false;
                }
                break;
        }
    }
    return true;
}
/* ITS4YOU-END 22.7.2014 13:45 */
function setSelectedCriteriaValue(criteria_obj,selected_value){
	for(var fci = 0; fci < criteria_obj.options.length; fci++){
		if(criteria_obj.options[fci].value==selected_value){
			criteria_obj.options[fci].selected = true;
		}
	}
}
function SaveChartImg(image_path,name){
    if(document.getElementById("current_action")){
        var c_action = document.getElementById("current_action").value;
    }else{
        var c_action = "";
    }
    var download_url = "index.php?module=ITS4YouReports&action=ITS4YouReportsAjax&file=DownloadFile&filepath="+image_path+"&filename="+name+".png";
    var features="width=20,height=20";
    var opened_win = window.open(download_url,name,features);
    /*opened_win.onload = function(e){ 
        alert(e);
    }*/
    return true;
}
