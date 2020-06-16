/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

var empty_values = ["todaymore", "todayless", "older1days", "older7days", "older15days", "older30days", "older60days", "older90days", "older120days"];

if (document.all)
    var browser_ie = true;
else if (document.layers)
    var browser_nn4 = true;
else if (document.layers || (!document.all && document.getElementById))
    var browser_nn6 = true;

function trim(string_in){
    var str_trim = string_in;
    str_trim = string_in.trim();
    return str_trim;
}

function getObj(n, d) {

    var p, i, x;

    if (!d) {
        d = document;
    }

    if (n != undefined) {
        if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
            d = parent.frames[n.substring(p + 1)].document;
            n = n.substring(0, p);
        }
    }

    if (d.getElementById) {
        x = d.getElementById(n);
        // IE7 was returning form element with name = n (if there was multiple instance)
        // But not firefox, so we are making a double check
        if (x && x.id != n)
            x = false;
    }

    for (i = 0; !x && i < d.forms.length; i++) {
        x = d.forms[i][n];
    }

    for (i = 0; !x && d.layers && i < d.layers.length; i++) {
        x = getObj(n, d.layers[i].document);
    }

    if (!x && !(x = d[n]) && d.all) {
        x = d.all[n];
    }

    if (typeof x == 'string') {
        x = null;
    }

    return x;
}

/* ITS4YOU-CR SlOl | 6.6.2014 8:39  */
function replaceAll(find, replace, str) {
    return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}
/* ITS4YOU-END 6.6.2014 8:39  */

function replaceUploadSize() {
    var upload = document.getElementById('key_upload_maxsize').value;
    upload = "'" + upload + "'";
    upload = upload.replace(/000000/g, "");
    upload = upload.replace(/'/g, "");
    document.getElementById('key_upload_maxsize').value = upload;
}


function vtlib_field_help_show_this(basenode, fldname) {
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
        Event.observe(domnode, 'mouseover', function() {
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

var gcurrepfolderid = 0;
function trimfValues(value)
{
    var string_array;
    string_array = value.split(":");
    return string_array[4];
}

function reports4you_updatefOptions(sel, opSelName, selectedVal) {
    jQuery(document).ready(function() {
        var typeofdata = new Array();
        typeofdata['V'] = ['e', 'n', 's', 'ew', 'c', 'k', 'isn', 'isnn'];
        typeofdata['N'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['SUM'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['AVG'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['MIN'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['MAX'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['COUNT'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['T'] = ['e', 'n', 'l', 'g', 'm', 'h', 'b', 'a', 'isn', 'isnn'];
        typeofdata['I'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['C'] = ['e', 'n', 'isn', 'isnn'];
        typeofdata['D'] = ['e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'af', 'nf', 'lf', 'gf', 'mf', 'hf', 'isn', 'isnn'];
        typeofdata['NN'] = ['e', 'n', 'l', 'g', 'm', 'h', 'isn', 'isnn'];
        typeofdata['E'] = ['e', 'n', 's', 'ew', 'c', 'k', 'isn', 'isnn'];

        // ITS4YOU-CR SlOl 
        var s_typeofdata = new Array();
        s_typeofdata['S'] = ['e', 'n', 's', 'ew', 'c', 'k', 'isn', 'isnn'];
        // ITS4YOU SlOl 25. 2. 2016 9:01:57 IT&M uitype120 customization
        s_typeofdata['SL'] = ['e', 'n', 'isn', 'isnn'];

        var moduleName = app.getModuleName();

        var fLabels = new Array();
        fLabels['e'] = app.vtranslate('EQUALS',moduleName);
        fLabels['n'] = app.vtranslate('NOT_EQUALS_TO',moduleName);
        fLabels['s'] = app.vtranslate('STARTS_WITH',moduleName);
        fLabels['ew'] = app.vtranslate('ENDS_WITH',moduleName);
        fLabels['c'] = app.vtranslate('CONTAINS',moduleName);
        fLabels['k'] = app.vtranslate('DOES_NOT_CONTAINS',moduleName);
        fLabels['l'] = app.vtranslate('LESS_THAN',moduleName);
        fLabels['g'] = app.vtranslate('GREATER_THAN',moduleName);
        fLabels['m'] = app.vtranslate('LESS_OR_EQUALS',moduleName);
        fLabels['h'] = app.vtranslate('GREATER_OR_EQUALS',moduleName);
        fLabels['bw'] = app.vtranslate('BETWEEN',moduleName);
        fLabels['b'] = app.vtranslate('BEFORE',moduleName);
        fLabels['a'] = app.vtranslate('AFTER',moduleName);
        fLabels['af'] = app.vtranslate('EQUALS_FLD',moduleName);
        fLabels['nf'] = app.vtranslate('NOT_EQUALS_TO_FLD',moduleName);
        fLabels['lf'] = app.vtranslate('LESS_THAN_FLD',moduleName);
        fLabels['gf'] = app.vtranslate('GREATER_THAN_FLD',moduleName);
        fLabels['mf'] = app.vtranslate('LESS_OR_EQUALS_FLD',moduleName);
        fLabels['hf'] = app.vtranslate('GREATER_OR_EQUALS_FLD',moduleName);
        fLabels['isn'] = app.vtranslate('IS_NULL',moduleName);
        fLabels['isnn'] = app.vtranslate('IS_NOT_NULL',moduleName);
        
        var selObj = document.getElementById(opSelName);
        if (selObj) {
            var noneLbl = app.vtranslate("None",moduleName);
            var fieldtype = null;

            var currOption = selObj.options[selObj.selectedIndex];
            var currField = sel.options[sel.selectedIndex];

            if (currField.value != null && currField.value.length != 0) {
                fieldtype = trimfValues(currField.value);

                // ITS4YOU-UP SlOl 27. 3. 2014 12:21:47
                // typeofdata[S] -> selectbox
                var sel_fields = JSON.parse(document.getElementById("sel_fields").value);

                var std_filter_columns = document.getElementById("std_filter_columns").value;
                if (typeof std_filter_columns != 'undefined' && std_filter_columns != "") {
                    var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
                } else {
                    var std_filter_columns_arr = new Array();
                }

                var std_filter_criteria = jQuery('#std_filter_criteria').text();
                var selected_value = html_entity_decode(currField.value,"UTF-8");

                if (std_filter_columns_arr.indexOf(selected_value) > -1) {
                    var std_filter_criteria_obj = selObj;
					if ('' !== std_filter_criteria) {
	                    var std_filter_criteria_arr = JSON.parse(std_filter_criteria);
	                    var nSFCVal = std_filter_criteria_obj.length;
	                    for (nLoop = 0; nLoop < nSFCVal; nLoop++) {
	                        std_filter_criteria_obj.remove(0);
	                    }
	                    std_filter_criteria_obj.options[0] = new Option(noneLbl, '');
	                    var sfc_i = 1;
	                    for (var filter_opt in std_filter_criteria_arr) {
	                        std_filter_criteria_obj.options[sfc_i] = new Option(std_filter_criteria_arr[filter_opt], filter_opt);
	                        sfc_i++;
	                    }
	                    for (var si = 0; si < std_filter_criteria_obj.length; si++) {
	                        if (std_filter_criteria_obj.options[si].value == selectedVal) {
	                            std_filter_criteria_obj.options[si].selected = true;
	                        }
	                    }
                    }
                } else if (sel_fields[currField.value]) {
                    // ITS4YOU-CR SlOl 25. 2. 2016 7:39:06 IT&M uitype 120 customization
                    var currfieldValArray = currField.value.split(":");
                    if(currfieldValArray[3]=='shownerid'){
                        var ops = s_typeofdata["SL"];
                    }else{
                        var ops = s_typeofdata["S"];
                    }
                } else {
                    var ops = typeofdata[fieldtype];
                    if(fieldtype=='T'){
                        jQuery('#fval'+opSelName.substr(3)).attr("placeholder", "hh:mm");
                    }
                }
                // ITS4YOU-END 
                var off = 0;
                if (ops != null) {

                    var nMaxVal = selObj.length;
                    for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                        selObj.remove(0);
                    }
                    selObj.options[0] = new Option(noneLbl, '');
                    if (currField.value == '') {
                        selObj.options[0].selected = true;
                    }
                    off = 1;
                    for (var i = 0; i < ops.length; i++) {
                        var label = fLabels[ops[i]];
                        if (label == null)
                            continue;
                        var option = new Option(fLabels[ops[i]], ops[i]);
                        selObj.options[i + off] = option;
                        if (currOption != null && currOption.value == option.value || option.value == selectedVal) {
                            option.selected = true;
                        }
                    }
                }
            } else {
                var nMaxVal = selObj.length;
                for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                    selObj.remove(0);
                }
                selObj.options[0] = new Option(noneLbl, '');
                if (currField.value == '') {
                    selObj.options[0].selected = true;
                }
            }
            if ('' === jQuery('#'+opSelName).find('option:selected').val()) {
            	jQuery('#'+opSelName).select2('val', '');
            }
            jQuery('#'+opSelName).trigger('liszt:updated');
        }
    })
}

// Setting cookies
function set_cookie(name, value, exp_y, exp_m, exp_d, path, domain, secure) {
    var cookie_string = name + "=" + escape(value);

    if (exp_y) {
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
function get_cookie(cookie_name)
{
    var results = document.cookie.match(cookie_name + '=(.*?)(;|$)');

    if (results)
        return (unescape(results[1]));
    else
        return null;
}


// Delete cookies 
function delete_cookie(cookie_name)
{
    var cookie_date = new Date( );  // current date & time
    cookie_date.setTime(cookie_date.getTime() - 1);
    document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}
function goToURL(url)
{
    document.location.href = url;
}

function invokeAction(actionName)
{
    if (actionName == "newReport")
    {
        // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
        goToURL("?module=ITS4YouReports&action=NewReport0&return_module=ITS4YouReports&return_action=index");
        return;
    }
    goToURL("/crm/ScheduleReport.do?step=showAllSchedules");
}
function verify_data(form) {
    var isError = false;
    var errorMessage = "";
    if (trim(form.folderName.value) == "") {
        isError = true;
        errorMessage += "\nFolder Name";
    }
    var moduleName = app.getModuleName();
    // Here we decide whether to submit the form.
    if (isError == true) {
        alert(app.vtranslate('MISSING_FIELDS',moduleName) + errorMessage);
        return false;
    }
    return true;
}

function setObjects()
{
    var availListObj = getObj("availList")
    var selectedColumnsObj = getObj("selectedColumns")

    var moveupLinkObj = getObj("moveup_link")
    var moveupDisabledObj = getObj("moveup_disabled")
    var movedownLinkObj = getObj("movedown_link")
    var movedownDisabledObj = getObj("movedown_disabled")
}

function addColumn(columns)
{
    if (columns == "selectedColumnsRel") {
        var selectedColumnsObj = getObj("selectedColumns");
    } else if (columns == "selectedQFColumnsRel") {
        var selectedColumnsObj = getObj("selectedQFColumns");
    } else if (columns == "selectedSummaries") {
        var selectedColumnsObj = getObj("selectedSummaries");
    } else {
        var selectedColumnsObj = getObj(columns);
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
    if (jQuery('#' + columns).hasClass('inputElement')) {
        jQuery('#' + columns).trigger('change');
    }
}

function addColumnStep1()
{
    var availListObj = getObj("availList");
    var selectedColumnsObj = getObj("selectedColumns");

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

    var SortByColumninnerHTML = "<option value='none'>" + none_lang + "</option>";
    SortByColumninnerHTML += selectedColumnsObj.innerHTML;
    jQuery('#sortColumnsByDiv').find('select').each(function(index,element){
        var sortColElement = jQuery(element);
        var sboxName = sortColElement.attr('name')+'_chzn';
        var sboxObj = jQuery('#'+sboxName);
        var selElement = sboxObj.find('a.chzn-single span');
        var selectedOption = selElement.html();
        
        sortColElement.html(SortByColumninnerHTML);
        
		sortColElement.trigger('liszt:updated');
        if(selectedOption!=''){
            selElement.html(selectedOption);
        }
    });
}

function addColumnStep2()
{
    var availListObj = getObj("availQFList");
    var selectedColumnsObj = getObj("selectedQFColumns");

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


function addColumnStep3() {
    // availListObj=getObj("availList2");
    var selectedColumnsObj = getObj("selectedColumns");
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

    var oldselectvalue = getObj("SortByColumn").value;

    getObj("SortByColumn").innerHTML = "<option value='none'>" + none_lang + "</option>";
    getObj("SortByColumn").innerHTML += selectedColumnsObj.innerHTML;

    getObj("SortByColumn").value = oldselectvalue;
}

function addColumnStep4()
{
    var availListObj = getObj("availQFList2");
    var selectedColumnsObj = getObj("selectedQFColumns");
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

function addColumnStep5()
{

    var availListObj = document.getElementById('availListSum');
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

                    if (availListObj.options[i].value != "none") {
                        var newColObj = document.createElement("OPTION");
                        newColObj.value = availListObj.options[i].value;

                        var selectedSumModuleObj = document.getElementById("SummariesModules");
                        var selectedSumModule = selectedSumModuleObj.options[selectedSumModuleObj.selectedIndex].text;
                        if (selectedSumModule.substring(0, 2) == "- ") {
                            selectedSumModule = selectedSumModule.substring(2);
                        }

                        var avl_text = availListObj.options[i].text;
                        newColObj.text = avl_text + " (" + selectedSumModule + ")";
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
function setSelSummSortOrder(selectedval) {
    var summaries_orderby_columnObj = getObj("summaries_orderby_column");

    if (summaries_orderby_columnObj) {
        for (j = 0; j < summaries_orderby_columnObj.length; j++)
        {
            if (summaries_orderby_columnObj.options[j].value == selectedval)
            {
                var go_j = j;
                var soc_str = summaries_orderby_columnObj.options[j].value;
                summaries_orderby_columnObj.options[j].selected = true;
                break;
            }
        }
        document.getElementById("summaries_orderby_columnString").value = soc_str;
        if(parseInt(go_j)>-1){
            summaries_orderby_columnObj.options[go_j].selected = true;
        }
    }
}
function delSummSortOrder(opt_obj) {
    var sortbyColumnsObj = getObj("summaries_orderby_column");
    for (j = 0; j < sortbyColumnsObj.options.length; j++)
    {
        if (opt_obj.value == sortbyColumnsObj.options[j].value) {
            sortbyColumnsObj.remove(j);
        }
    }
}
function addToSummSortOrder(opt_obj) {
    var summaries_orderby_columnObj = getObj("summaries_orderby_column");
    var rowFound = false;
    for (j = 0; j < summaries_orderby_columnObj.length; j++) {
        if (summaries_orderby_columnObj.options[j].value == opt_obj.value) {
            var rowFound = true;
            break;
        }
    }
    if (rowFound != true) {
        var newColObj = document.createElement("OPTION")
        if (opt_obj.value != "none") {
            newColObj.value = opt_obj.value
            newColObj.text = opt_obj.text
            summaries_orderby_columnObj.appendChild(newColObj);
        }
    }
}
// ITS4YOU-END 21. 3. 2014 14:14:59

//this function is done for checking,whether the user has access to edit the field :Bharath
function selectedColumnClick(oSel)
{
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
        var moduleName = app.getModuleName();
        alert(app.vtranslate('NOT_ALLOWED_TO_EDIT_FIELDS',moduleName) + "\n" + error_msg);
        return false;
    }
    else
        return true;
}
function delColumn(columns)
{
    // ITS4YOU-CR SlOl 11. 5. 2016 12:52:08
    if(columns == "selectedColumns"){
        var selectedOption = jQuery('#selectedColumns').find('option:selected');
        
        var sortColumnsByContainer = jQuery('#sortColumnsByDiv');
		var sortColumnsByRows = jQuery('#sortColumnRow',sortColumnsByContainer);
		
        jQuery.each(selectedOption,function(ti,elem){
            var OptionsObj = jQuery(elem);
            jQuery.each(sortColumnsByRows,function(oi,delem){
    			var sortColumnsByElement = jQuery(delem);
                var liElements = sortColumnsByElement.find('li.active-result');
    			jQuery.each(liElements,function(i,element) {
    				var liElemVal = jQuery(element).html();
                    if(liElemVal==OptionsObj.text()){
                        jQuery(element).remove();
                    }
    			});
                var selElement = sortColumnsByElement.find('a.chzn-single span');
                if(selElement.html()==OptionsObj.text()){
                    var liFirstElements = sortColumnsByElement.find('li.active-result:first');
                    var noneText = liFirstElements.html();
                    selElement.html(noneText);
                }
            });
            selectedOption.remove();
        });       
    }else{
        var selectedColumnsObj = getObj(columns);
        if (selectedColumnsObj.options.selectedIndex > -1)
        {
            for (i = 0; i < selectedColumnsObj.options.length; i++)
            {
                if (selectedColumnsObj.options[i].selected == true)
                {
                    delSummSortOrder(selectedColumnsObj.options[i]);
                    var deleteOption = selectedColumnsObj.options[i].value;
                    
                    selectedColumnsObj.remove(i);
                    delColumn(columns);
                }
            }
        }
    }
}
// ITS4YOU-CR SlOl 12. 5. 2016 9:20:37
function addSortColumnRow(){
    var sortColumnBase = jQuery('#sortColumnsByDivBase').html();
    
    var scolrow_n = jQuery("#scolrow_n" ).val();
    if(scolrow_n==""){
        scolrow_n = 1;
    }
    var n = parseInt(scolrow_n)+1;
    jQuery("#scolrow_n" ).val(n);
    
    //SortByColumnIdNr
    sortColumnBase = replaceAll('SortByColumnIdNr', 'SortByColumn'+n, sortColumnBase);
    //SortOrderColumnIdNr
    sortColumnBase = replaceAll('SortOrderColumnIdNr', 'SortOrderColumn'+n, sortColumnBase);

    sortColumnBase = replaceAll('rep_select2', 'select2', sortColumnBase);

    jQuery('#sortColumnsByDiv').append(sortColumnBase);
    app.changeSelectElementView(jQuery('#sortColumnsByDiv'));
    
}
// ITS4YOU-CR SlOl 12. 5. 2016 11:23:25
function deleteSortColumnRow(obj){
    var delObj = jQuery(obj);
    
    var sortColumnRow = delObj.closest('#sortColumnRow');
    sortColumnRow.remove();
}
// ITS4YOU-END
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
function hasOptions(obj) {
    if (obj != null && obj.options != null) {
        return true;
    }
    return false;
}

function swapOptions(obj, i, j) {
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

function moveUp(columns)
{
    // ITS4YOU-CR SlOl 2/20/2014 8:13:46 PM
    var obj = getObj(columns);
    if (!hasOptions(obj)) {
        return;
    }
    for (var i = 0; i < obj.options.length; i++) {
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

function moveDown(columns)
{
    var obj = getObj(columns);
    if (!hasOptions(obj)) {
        return;
    }
    for (var i = obj.options.length - 1; i >= 0; i--) {
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

function disableMove()
{
    var selectedColumnsObj = getObj("selectedColumns");
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


function hideTabs()
{
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

function showSaveDialog()
{
    // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
    var url = "index.php?module=ITS4YouReports&action=SaveReport";
    window.open(url, "Save_Report", "width=550,height=350,top=20,left=20;toolbar=no,status=no,menubar=no,directories=no,resizable=yes,scrollbar=no")
}

function saveAndRunReport()
{
    var primarymoduleObj = getObj("primarymodule"); // reportfolder
    var selectedColumn = trim(primarymoduleObj.value);
    //document.NewReport.report_primarymodule.value = selectedColumn;

    var selectedColumnsObj = getObj("selectedColumns");
    var selectedSummariesObj = getObj("selectedSummaries");
    if (selectedColumnsObj.options.length == 0 && selectedSummariesObj.options.length == 0)
    {
        var moduleName = app.getModuleName();
        alert(app.vtranslate('COLUMNS_CANNOT_BE_EMPTY',moduleName));
        return false;
    }

    var relatedmodules = '';
    var all_related_modules_str = document.getElementById('all_related_modules').value;
    if (all_related_modules_str != '') {
        var all_related_modules = all_related_modules_str.split(":");
        for (i = 0; i <= (all_related_modules.length - 1); i++)
        {
            var rel_mod_actual = 'relmodule_' + all_related_modules[i];
            var actual_rel_module = document.getElementById(rel_mod_actual);
            if (actual_rel_module.checked)
                relatedmodules += actual_rel_module.value + ':';
        }
    }
    document.NewReport.secondarymodule.value = relatedmodules;

    var escapedOptions = new Array('account_id', 'contactid', 'contact_id', 'product_id', 'parent_id', 'campaignid', 'potential_id', 'assigned_user_id1', 'quote_id', 'accountname', 'salesorder_id', 'vendor_id', 'time_start', 'time_end', 'lastname');

    var conditionColumns = vt_getElementsByName('tr', "conditionColumn");
    var criteriaConditions = [];
// see resources Detail.js - registerSaveOrGenerateReportEvent too !!!
    // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
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
        var selectedColumn = columnObject.value;
        var selectedColumnIndex = columnObject.selectedIndex;
        var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
        if (columnObject.options[selectedColumnIndex].value != "none") {
            var comparatorId = ctype + "op" + columnIndex;
            var comparatorObject = getObj(comparatorId);
            var comparatorValue = comparatorObject.value;

            var valueId = ctype + "val" + columnIndex;
            var valueObject = getObj(valueId);
            var specifiedValue = valueObject.value;

            var extValueId = ctype + "val_ext" + columnIndex;
            var extValueObject = getObj(extValueId);
            if (extValueObject) {
                extendedValue = extValueObject.value;
            }

            var glueConditionId = ctype + "con" + columnIndex;
            var glueConditionObject = getObj(glueConditionId);
            var glueCondition = '';
            if (glueConditionObject) {
                glueCondition = glueConditionObject.value;
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
                        /*
                        var sel_fields_array = new Array();
                        var xc = document.getElementsByTagName('input');
                        //var xc = document.getElementsByTagName('li');
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
                            alert(selectedColumnLabel + app.vtranslate('CANNOT_BE_NONE'))
                        }
                        */
                        var selectElement = jQuery('#s_fval'+columnIndex);
                        specifiedValue = selectElement.val();
                        // fcol fval
                        /*
                        var form = jQuery('#NewReport');
                        var params = form.serializeFormData();
                        for (var param in params){
                        //for (var fi = 0; fi < params.length; fi++){
                            alert(param+" -> "+params[param])
                            if (param.substring(0, 4) == "fval") {
                                alert(param+" -> "+params[param])
                            }
                        }
                        */
                        //alert( JSON.stringify(params) );
                        // pokracuj oldo
                        //alert(xc[fi].name+" - "+xc[fi].type);

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
    jQuery('#advft_criteria').value = JSON.stringify(criteriaConditions);

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
            groupCondition = groupConditionObject.value;
        }
        criteriaGroups[groupIndex] = {"groupcondition": groupCondition};
    }
    jQuery('#advft_criteria_groups').value = JSON.stringify(criteriaGroups);

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
        var selectedColumn = columnObject.value;
        var selectedColumnIndex = columnObject.selectedIndex;
        var selectedColumnLabel = columnObject.options[selectedColumnIndex].text;
        if (columnObject.options[selectedColumnIndex].value != "none") {
            var comparatorId = ctype + "groupop" + columnIndex;
            var comparatorObject = getObj(comparatorId);
            var comparatorValue = comparatorObject.value;
            var valueId = ctype + "groupval" + columnIndex;
            var valueObject = getObj(valueId);
            var specifiedValue = valueObject.value;

            var glueConditionId = ctype + "groupcon" + columnIndex;
            var glueConditionObject = getObj(glueConditionId);
            var glueCondition = '';
            if (glueConditionObject) {
                glueCondition = glueConditionObject.value;
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
    jQuery('#groupft_criteria').value = JSON.stringify(GroupcriteriaConditions);
    // groupconditioncolumn end

    /*
    var date1 = getObj("startdate")
    var date2 = getObj("enddate")

    if (typeof getObj("stdDateFilter") != 'undefined') {
        var stdNewDateFilterObj = getObj("stdDateFilter");
        var stdNewDateFilterIndex = stdNewDateFilterObj.selectedIndex;
        var stdNewDateFilterValue = stdNewDateFilterObj.options[stdNewDateFilterIndex].value;
        // see this file on top for empty_values definition and ITS4YouReports.php file too
        var go_empty = empty_values.indexOf(stdNewDateFilterValue);
    } else {
        var go_empty = new Array();
    }
    //# validation added for date field validation in final step of report creation
    if (go_empty < 0 && (date1.value != ''))
    {
        if (!dateValidate("startdate", "Start Date", "D"))
            return false;
    }
    if (go_empty < 0 && (date2.value != '')) {
        if (!dateValidate("enddate", "End Date", "D"))
            return false;
    }
    if (go_empty < 0 && ((date1.value != '') || (date2.value != ''))) {
        if (!dateComparison("startdate", 'Start Date', "enddate", 'End Date', 'LE'))
            return false;
    }
    */

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
function formSelectedSummariesString()
{
    var reporttype = jQuery("#reporttype").val();
    if(reporttype !== 'custom_report'){
        selectedSummariesObj = getObj("selectedSummaries");
        var selectedSumStr = "";
        for (i = 0; i < selectedSummariesObj.options.length; i++)
        {
            selectedSumStr += selectedSummariesObj.options[i].value + ";";
        }
        document.getElementById("selectedSummariesString").value = selectedSumStr;
    }
}
// ITS4YOU-CR SlOl 5. 3. 2014 15:53:46
function formSelectedColumnString()
{
    var selectedColumnsObj = getObj("selectedColumns");
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
function nextStep4You() {
    var go_to_step = actual_step + 1;
    // ITS4YOU SlOl step 2 and step 3 disabled -2 steps
    if (go_to_step == 2) {
        go_to_step = go_to_step + 2;
    }
    var actual_step = go_to_step;
    if (go_to_step != 12) {
        changeSteps4U(go_to_step);
    } else {
        changeSteps();
    }
}
// ITS4YOU-END 4. 9. 2013 16:04:16

// ITS4YOU-CR SlOl 21.12.2010 R4U
function changeSteps4U(step) {
    var actual_step = step;
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
        var newurl = 'action=ITS4YouReportsAjax&mode=ajax&file=ChangeSteps&module=ITS4YouReports';
        newurl += '&step=' + gotoStep;
        newurl += '&record=' + getReportRecordID();

        var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
        var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

        newurl += '&reportmodule=' + selectedPrimaryModule;
        newurl += '&primarymodule=' + selectedPrimaryModule;
        newurl += '&reportname=' + document.getElementById('reportname').value;
        newurl += '&reportdesc=' + document.getElementById('reportdesc').value;
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
        newurl += '&limit=' + document.getElementById('limit').value;
        newurl += '&isReportScheduled=' + document.getElementById('isReportScheduled').checked;

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
        /*selectedColumnsStr = replaceAll("&", "@AMPKO@", selectedColumnsStr);
        newurl += '&selectedColumnsStr=' + selectedColumnsStr;*/
		newurl += '&selectedColumnsStr=' + encodeURIComponent(selectedColumnsStr);

        var selectedRecipients = {users: selectedUsers, groups: selectedGroups,
            roles: selectedRoles, rs: selectedRolesAndSub};
        newurl += '&selectedRecipientsStr=' + JSON.stringify(selectedRecipients);

        // ITS4YOU-CR SlOl 6. 3. 2014 9:29:42 SUMMARIES COLUMNS
        formSelectedSummariesString();
        var selectedSummariesString = document.getElementById("selectedSummariesString").value;
        newurl += '&selectedSummariesString=' + selectedSummariesString;

        // newurl += '&sharingSelectedColumns='+sharingSelectedStr;
        // LIMIT AND SCHEDULER END
        // ITS4YOU-CR SlOl 27. 2. 2014 9:28:18
        /*qfurl = getQFurl();
         newurl += '&qf_to_go=' + qfurl;*/

        // ITS4YOU-CR SlOl 20. 3. 2014 10:54:47
        var TimeLineColumn_Group1 = getGroupTimeLineValue(1);
        newurl += '&timeline_column1=' + TimeLineColumn_Group1;
        var TimeLineColumn_Group2 = getGroupTimeLineValue(2);
        newurl += '&timeline_column2=' + TimeLineColumn_Group2;
        newurl += '&timeline_type2=' + getGroupTimeLineType(2);
        var TimeLineColumn_Group3 = getGroupTimeLineValue(3);
        newurl += '&timeline_column3=' + TimeLineColumn_Group3;
        newurl += '&timeline_type3=' + getGroupTimeLineType(3);
        /* timeline columns definition changed
         if (document.getElementById('timeline_columns')) {
         var timeline_selectbox = document.getElementById('timeline_columns');
         var timeline_column = "";
         if (timeline_selectbox.options[timeline_selectbox.selectedIndex]) {
         timeline_column = timeline_selectbox.options[timeline_selectbox.selectedIndex].value;
         }
         
         var timeline_frequency = "";
         var TimeLineColumnRadios = document.getElementsByName('TimeLineColumn');
         for (var i = 0, length = TimeLineColumnRadios.length; i < length; i++) {
         if (TimeLineColumnRadios[i].checked) {
         timeline_frequency = TimeLineColumnRadios[i].value;
         break;
         }
         }
         if (timeline_frequency == "" && timeline_column != "none") {
         alert(document.getElementById('EMPTY_Frequency').value);
         return false;
         }
         newurl += '&timeline_frequency=' + timeline_frequency;
         newurl += '&timeline_column=' + timeline_column;
         }
         */
        // ITS4YOU-END 20. 3. 2014 10:54:49
        // ITS4YOU-CR SlOl 21. 3. 2014 14:31:41
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
        if (document.getElementById('chartType')) {
            var chart_type = document.getElementById('chartType').options[document.getElementById('chartType').selectedIndex].value;
            if (chart_type != "none") {
                newurl += '&chart_type=' + chart_type;
                var data_series = document.getElementById('data_series').options[document.getElementById('data_series').selectedIndex].value;
                newurl += '&data_series=' + data_series;
                var charttitle = document.getElementById('charttitle').value;
                newurl += '&charttitle=' + charttitle;
            }
        }
        /* ITS4YOU-END 2.7.2014 15:00 */

        new Ajax.Request('index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    // ITS4YOU-CR SlOl 20.12.2010 R4U singlerow
                    postBody: newurl,
                    onComplete: function(response) {
                        newtableid = gotoStep + 'label';
                        getObj(newtableid).className = 'dvtSelectedCell';

                        for (i = 1; i <= 12; i++)
                        {
                            if (document.getElementById("step" + i)) {
                                mystepnr = "step" + i;
                                tableid = mystepnr + 'label';
                                if (i != step) {
                                    if (getObj(tableid)) {
                                        hide(mystepnr);
                                        getObj(tableid).className = 'dvtUnSelectedCell';
                                    }
                                }
                            }
                        }

                        if (gotoStep == 'step5')
                        {
                            document.getElementById('step5').style.display = 'block';
                            if (document.getElementById('availList'))
                            {
                                document.getElementById('availList').innerHTML = "";
                                document.getElementById('availList').options.length = 0;
                            }

                            var step5_result = response.responseText.split("(!A#V_M@M_M#A!)");
                            setStep5Columns(step5_result);

// REPORT TYPE REMOVED
                            // TIMELINE FIELDS
                            /*
                             document.getElementById('timelinespace').style.display="table-row";
                             document.getElementById('timelineheader').style.display="table-row";
                             document.getElementById('timelinevalues').style.display="table-row";
                             */
                            /*							if(objreportType.value=='timeline'){
                             document.getElementById('timelinespace').style.display="table-row";
                             document.getElementById('timelineheader').style.display="table-row";
                             document.getElementById('timelinevalues').style.display="table-row";
                             }else{
                             document.getElementById('timelinespace').style.display="none";
                             document.getElementById('timelineheader').style.display="none";
                             document.getElementById('timelinevalues').style.display="none";
                             }
                             */
                        }
                        else if (gotoStep == 'step8')
                        {
                            document.getElementById('step8').style.display = 'block';

                            var resp_info = response.responseText.split("__ADVFTCRI__");

                            var resp_blocks_info = resp_info[0];
                            var criteria_info = JSON.parse(resp_info[1]);
                            document.getElementById("sel_fields").value = resp_info[2];
                            document.getElementById("std_filter_columns").value = resp_info[3];

                            var resp_blocks = resp_blocks_info.split("__BLOCKS__");
                            var FIELD_BLOCKS = resp_blocks[0];
                            document.getElementById('filter_columns').innerHTML = FIELD_BLOCKS;
                            var aviable_fields = FIELD_BLOCKS;
                            var group_fields = FIELD_BLOCKS;

                            if (!document.getElementById("fcol0") && document.getElementById("mode").value == "create") {
                                addNewConditionGroup('adv_filter_div')
                            }

                            var sum_group_columns = resp_blocks[1];
                            document.getElementById('sum_group_columns').innerHTML = sum_group_columns;
//                            alert(document.getElementById('sum_group_columns').innerHTML);

                            var sortbycolumns = document.getElementById('SortByColumn').innerHTML;
                            var optgroups = FIELD_BLOCKS.split("(|@!@|)");
                            s = document.NewReport.getElementsByTagName('select');

                            for (var i = 0; i < s.length; i++)
                            {
                                if (s[i].name.substring(0, 4) == 'fcol')
                                {
                                    // addNewConditionGroup('adv_filter_div');
                                    var criteria_i = s[i].name.substring(4, 5);
                                    var selected_column = criteria_info[criteria_i]["columnname"];
                                    oldvalue = s[i].value;
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
                                                    if (option["value"] == selected_column) {
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
                                    if (typeof std_filter_columns != 'undefined' && std_filter_columns != "") {
                                        var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
                                    } else {
                                        var std_filter_columns_arr = new Array();
                                    }
                                    var selectedVal = "";
                                    if (typeof document.getElementById('fop' + criteria_i) != "undefined") {
                                        if (std_filter_columns_arr.indexOf(currField.value) > -1) {
                                            selectedVal = document.getElementById('fop' + criteria_i).options[document.getElementById('fop' + criteria_i).selectedIndex].value;
                                        }
                                    }
                                    reports4you_updatefOptions(s[i], 'fop' + criteria_i, selectedVal);

                                    s[i].value = oldvalue;
                                }
                                else if (s[i].name.substring(0, 4) == 'gcol')
                                {
                                    var oldvalue = s[i].value;

                                    var oldselectvalue = getObj("SortByColumn").value;

                                    if (browser_ie)
                                    {
                                        var selectonchange = s[i].onchange;
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
                        }
                        // ITS4YOU-CR SlOl 2. 5. 2013 14:00:25
                        else if (gotoStep == 'step10')
                        {
                            showRecipientsOptions();
                        }
                        // ITS4YOU-END
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

function setSTDFilter(selectedStdFilter) {
    var newurl2 = 'action=ITS4YouReportsAjax&mode=ajax&file=ChangeSteps&module=ITS4YouReports';
    newurl2 += '&step=getStdFilter';
    if ((selectedStdFilter) && selectedStdFilter != "") {
        newurl2 += '&selectedStdFilter=' + selectedStdFilter;
    }
    if (document.getElementById('record'))
        var record = document.getElementById('record').value;
    else
        var record = '';
    newurl2 += '&record=' + record;

    if (document.getElementById('primarymodule').type == "text") {
        var selectedPrimaryModule = document.getElementById('primarymodule').value;
    } else if (document.getElementById('primarymodule') != "" && document.getElementById('primarymodule').type != "hidden") {
        var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
        var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;
    } else {
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
    if (typeof document.NewReport.stdDateFilterField != 'undefined' && document.NewReport.stdDateFilterField != "") {
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
    if (document.getElementById("current_action")) {
        var c_action = document.getElementById("current_action").value;
    } else {
        var c_action = "";
    }
    if (c_action != "" && c_action == "resultGenerate") {
        var jqxhr = jQuery.post('index.php?' + newurl2, function(response) {
            // alert( "success" );
        })
                .done(function(response) {
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
    } else {
        new Ajax.Request('index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: newurl2,
                    onComplete: function(response) {
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
function getQFurl() {
    var c = new Array();
    var qfurl = "";
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
function getCurl() {
    var c = new Array();
    var curl = "";
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
function getLabelsurl() {
    var c = new Array();
    var lblurl = "";
    c = document.getElementsByTagName('input');
    for (var i = 0; i < c.length; i++)
    {
        if (c[i]) {
            var id_text = c[i].id;
            var search_for_lc = "_lLbLl_";
            if (id_text.indexOf(search_for_lc) > -1 && id_text.indexOf("hidden_") == -1) {
                if (lblurl != "") {
                    lblurl += "$_@_$";
                }
                var str_lblurl = c[i].id + "_lLGbGLl_" + c[i].value;
                str_lblurl = replaceAll("&", "@AMPKO@", str_lblurl);
                lblurl += str_lblurl;
            }
        }
    }
    document.getElementById('labels_to_go').value = lblurl;

    return lblurl;
}
// ITS4YOU-END 12. 3. 2014 14:12:17

function setRelModules(relMod) {
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

function inMyArray(myarray, myvalue)
{
    var i;
    for (i = 0; i < myarray.length; i++) {
        if (myarray[i] == myvalue) {
            return true;
        }
    }
    return false;
}

function changeSteps1()
{
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
function changeStepsback1()
{
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
function changeSteps(savetype)
{
    if (!savetype || savetype == "") {
        savetype = "Save&Run";
    }
    var report_name_val = document.getElementById('reportname').value;

    if (report_name_val == "")
    {
        var moduleName = app.getModuleName();
        alert(app.vtranslate('MISSING_REPORT_NAME',moduleName));
        return false;
    }
    else
    {
        // ITS4YOU-CR SlOl 9. 9. 2013 13:36:29 scheduler
        var isScheduledObj = getObj("isReportScheduled");
        if (isScheduledObj.checked == true) {
            var selectedRecipientsObj = getObj("selectedRecipients");

            if (selectedRecipientsObj.options.length == 0) {
                var moduleName = app.getModuleName();
                alert(app.vtranslate('RECIPIENTS_CANNOT_BE_EMPTY',moduleName));
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
            document.NewReport.selectedRecipientsString_v7.value = selectedRecipientsJson;

            var scheduledInterval = {scheduletype: document.NewReport.scheduledType.value,
                month: document.NewReport.scheduledMonth.value,
                date: document.NewReport.scheduledDOM.value,
                day: document.NewReport.scheduledDOW.value,
                time: document.NewReport.scheduledTime.value
            };

            var scheduledIntervalJson = JSON.stringify(scheduledInterval);
            document.NewReport.scheduledIntervalString.value = scheduledIntervalJson;

            var curl = "";
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
        // ITS4YOU-CR SlOl 4. 3. 2014 15:58:22
        if (savetype != "") {
            document.getElementById("SaveType_v7").value = savetype;
        }
        // ITS4YOU-END 4. 3. 2014 15:58:23
//		jQuery("#newReport").serialize();
        if (saveAndRunReport()) {
            document.NewReport.submit();
        }
    }
}
function changeStepsback()
{
    if (actual_step != 1)
    {
        var last_step = actual_step - 1;
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
function editReport(id)
{
    var arg = 'index.php?module=ITS4YouReports&action=NewReport&record=' + id + '&mode=edit';
    document.location.href = arg;
}
function CreateReport(module)
{
    var arg = 'index.php?module=ITS4YouReports&action=NewReport&folder=' + gcurrepfolderid + '&reportmodule=' + module + '&primarymodule=' + module + '&mode=create';
    document.location.href = arg;
}
function fnPopupWin(winName) {
    window.open(winName, "ReportWindow", "width=790px,height=630px,scrollbars=yes");
}
function re_dateValidate(fldval, fldLabel, type) {
    if (re_patternValidate(fldval, fldLabel, "DATE") == false)
        return false;
    var dateval = fldval.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements = splitDateVal(dateval)

    var dd = dateelements[0]
    var mm = dateelements[1]
    var yyyy = dateelements[2]

    if (dd < 1 || dd > 31 || mm < 1 || mm > 12 || yyyy < 1 || yyyy < 1000) {
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    if ((mm == 2) && (dd > 29)) {//checking of no. of days in february month
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    if ((mm == 2) && (dd > 28) && ((yyyy % 4) != 0)) {//leap year checking
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
        case 11 :
            if (dd > 30) {
                alert(app.vtranslate('ENTER_VALID') + fldLabel);
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
function re_patternValidate(fldval, fldLabel, type) {
    if (type.toUpperCase() == "DATE") {//DATE validation 

        switch (its4youUserDateFormat) {
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
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    } else
        return true
}

//added to fix the ticket #5117
function standardFilterDisplay()
{
    var stdDateFilterField = document.getElementById('stdDateFilterField');
    if (document.getElementById('stdDateFilterField')) {
        var stdDateFilterFieldIndex = stdDateFilterField.selectedIndex;
        var stdDateFilterFieldValue = stdDateFilterField.options[stdDateFilterFieldIndex].value;

        if (stdDateFilterField.options.length <= 0 || (stdDateFilterField.selectedIndex > -1 && stdDateFilterField.options[stdDateFilterField.selectedIndex].value == "Not Accessible")) {
            getObj('stdDateFilter').disabled = true;
            getObj('startdate').disabled = true;
            getObj('enddate').disabled = true;
            getObj('jscal_trigger_date_start').style.visibility = "hidden";
            getObj('jscal_trigger_date_end').style.visibility = "hidden";
        } else {
            getObj('stdDateFilter').disabled = false;
            getObj('startdate').disabled = false;
            getObj('enddate').disabled = false;
            getObj('jscal_trigger_date_start').style.visibility = "visible";
            getObj('jscal_trigger_date_end').style.visibility = "visible";
        }
    }
}

function updateRelFieldOptions(sel, opSelName) {
    jQuery(document).ready(function() {
        var selObj = document.getElementById(opSelName);
// {"vtiger_quotes:quotestage:Quotes_Quote Stage:quotestage:V":true,"vtiger_quotes:carrier:Quotes_Carrier:carrier:V":true,"vtiger_quotes:taxtype:Quotes_Tax Type:hdnTaxType:V":true,"vtiger_potential_315:potentialtype:Potentials_Type:opportunity_type:V:315":true,"vtiger_potential_315:leadsource:Potentials_Lead Source:leadsource:V:315":true,"vtiger_potential_315:sales_stage:Potentials_Sales Stage:sales_stage:V:315":true,"vtiger_contactsubdetails_318:leadsource:Contacts_Lead Source:leadsource:V:318":true,"vtiger_account_329:industry:Accounts_industry:industry:V:329":true,"vtiger_account_329:rating:Accounts_Rating:rating:V:329":true,"vtiger_account_329:account_type:Accounts_Type:accounttype:V:329":true,"vtiger_accountscf_329:cf_877:Accounts_Clen:cf_877:V:329":true,"vtiger_products_680:manufacturer:Products_Manufacturer:manufacturer:V:14:680":true,"vtiger_products_680:productcategory:Products_Product Category:productcategory:V:14:680":true,"vtiger_products_680:glacct:Products_GL Account:glacct:V:14:680":true,"vtiger_products_680:usageunit:Products_Usage Unit:usageunit:V:14:680":true,"vtiger_service_680:service_usageunit:Services_Usage Unit:service_usageunit:V:36:680":true,"vtiger_service_680:servicecategory:Services_Service Category:servicecategory:V:36:680":true,"vtiger_salesorder_MIF:carrier:SalesOrder_Carrier:carrier:V:MIF":true,"vtiger_salesorder_MIF:sostatus:SalesOrder_Status:sostatus:V:MIF":true,"vtiger_salesorder_MIF:taxtype:SalesOrder_Tax Type:hdnTaxType:V:MIF":true,"vtiger_invoice_recurring_info_MIF:recurring_frequency:SalesOrder_Frequency:recurring_frequency:V:MIF":true,"vtiger_invoice_recurring_info_MIF:payment_duration:SalesOrder_Payment Duration:payment_duration:V:MIF":true,"vtiger_invoice_recurring_info_MIF:invoice_status:SalesOrder_Invoice Status:invoicestatus:V:MIF":true,"vtiger_activity_MIF:recurringtype:Calendar_Recurrence:recurringtype:O:MIF":true,"vtiger_activity_MIF:status:Calendar_Status:taskstatus:V:MIF":true,"vtiger_activity_MIF:priority:Calendar_Priority:taskpriority:V:MIF":true,"vtiger_activity_MIF:activitytype:Calendar_Activity Type:activitytype:V:MIF":true,"vtiger_activity_MIF:visibility:Calendar_Visibility:visibility:V:MIF":true,"vtiger_activity_MIF:duration_minutes:Calendar_Duration Minutes:duration_minutes:T:MIF":true,"vtiger_activity_MIF:duration_minutes:Calendar_Duration Minutes:duration_minutes:O:MIF":true,"vtiger_activity_MIF:eventstatus:Calendar_Status:eventstatus:V:MIF":true,"vtiger_notes_MIF:folderid:Documents_Folder Name:folderid:V:MIF":true,"its4you_preinvoice_MIF:preinvoicestatus:ITS4YouPreInvoice_Status:preinvoicestatus:V:MIF":true,"its4you_preinvoice_MIF:taxtype:ITS4YouPreInvoice_Tax Type:hdnTaxType:V:MIF":true}
        if (selObj) {
            var fieldtype = null;
            var currOption = selObj.options[selObj.selectedIndex];
            var currField = sel.options[sel.selectedIndex];
            // ITS4YOU-CR SlOl 26. 3. 2014 13:26:01 SELECTBOX VALUES INTO FILTERS
            var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
            var opSelName_array = opSelName.split("val_");
            var row_i = opSelName_array[1];
            
            var filter_criteria_obj = document.getElementById("fop" + row_i);
            if (sel_fields[currField.value] && ('e' === filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value
                    || 'n' === filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value)) {
                
                var r_sel_fields = document.getElementById("fval" + row_i).value;
//alert(r_sel_fields);

                if (document.getElementById("current_action")) {
                    var c_action = document.getElementById("current_action").value;
                } else {
                    var c_action = "";
                }

                var postData = {
                    "module": "ITS4YouReports",
                    "action": 'IndexAjax',
                    "mode": "getFilterColHtml",
                    "sfield_name": "fval" + row_i,
                    "record": getReportRecordID(),
                    "currField": currField.value,
                    "sel_fields": JSON.stringify(sel_fields)
                };

                if (typeof r_sel_fields != 'undefined' && r_sel_fields != "") {
                    postData["r_sel_fields"] = r_sel_fields;
                }

                var actionParams = {
                    "type": "POST",
                    "url": 'index.php',
                    "dataType": "html",
                    "data": postData
                };
//window.open('index.php?module=ITS4YouReports&action=IndexAjax&mode=getFilterColHtml&sfield_name=fval'+row_i+'&record=' + getReportRecordID() + '&currField=' + currField.value + '&sel_fields' + JSON.stringify(sel_fields));

                app.request.post(actionParams).then(
                    function(err,data) {
                        if(err === null){

                            var container = jQuery("#node3span" + row_i + "_ajx");
                            container.html(data);

                            if(jQuery('#fvalhdn'+row_i).val()!=''){
                                container.css("display", "none");
                                var fieldValue = getFieldLabelExpression(jQuery('#fvalhdn'+row_i).val());
                                jQuery('#fval' + row_i).val(fieldValue);
                                jQuery("#node3span" + row_i + "_st").css("display", "block");
                            }else{
                                container.css("display", "block");
                                jQuery("#node3span" + row_i + "_st").css("display", "none");
                            }

                            //jQuery("#node3span" + row_i + "_djx").css("display", "none");

                            app.changeSelectElementView(container);
                            app.showSelect2ElementView(container.find('select.select2'));
                            container.find('select.select2').on('change', function () {
                                jQuery('#saveReportBtn').removeClass('hide');
                                jQuery('#report_changed').val('1');
                            });

                            setRelPopupClick('node3span'+row_i+'_selects',row_i);
                        }
                    });

            } else {
                document.getElementById("node3span" + row_i + "_ajx").innerHTML = "";
                jQuery("#node3span" + row_i + "_st").css("display", "block");
                jQuery("#node3span" + row_i + "_ajx").css("display", "none");
                jQuery("#node3span" + row_i + "_djx").css("display", "none");
                
                if ('undefined' !== typeof filter_criteria_obj.options[filter_criteria_obj.selectedindex]) {
					if(filter_criteria_obj.options[filter_criteria_obj.selectedindex].value=="isn" || filter_criteria_obj.options[filter_criteria_obj.selectedindex].value=="isnn"){
	                    jquery("#fval"+row_i).val("");
	                }
                }

                //document.getElementById("node3span" + row_i + "_st").style.display = "block";
                //document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                //document.getElementById("node3span" + row_i + "_djx").style.display = "none";
                // ITS4YOU-END 26. 3. 2014 13:25:55
                if (currField.value != null && currField.value.length != 0) {
                    fieldtype = trimfValues(currField.value);
                    ops = rel_fields[fieldtype];
                    var off = 0;
                    if (ops != null) {
                        var nMaxVal = selObj.length;
                        for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
                            selObj.remove(0);
                        }
                        selObj.options[0] = new Option('None', '');
                        if (currField.value == '') {
                            selObj.options[0].selected = true;
                        }
                        off = 1;
                        for (var i = 0; i < ops.length; i++) {
                            var field_array = ops[i].split("::");
                            var label = field_array[1];
                            var field = field_array[0];
                            if (label == null)
                                continue;
                            var option = new Option(label, field);
                            selObj.options[i + off] = option;
                            if (currOption != null && currOption.value == option.value) {
                                option.selected = true;
                            }
                        }
                    }
                } else {
                    var nMaxVal = selObj.length;
                    for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
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
        var std_filter_columns = jQuery("#std_filter_columns").val();

        if (typeof std_filter_columns != 'undefined' && std_filter_columns != "") {
            var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
            var selected_value = html_entity_decode(sel.value,"UTF-8");

            if (std_filter_columns_arr.indexOf(selected_value) > -1) {
                var r_sel_fields = document.getElementById("fval" + row_i).value;
                getFilterDateHtml(row_i, r_sel_fields);
            } else {
                document.getElementById("node3span" + row_i + "_djx").innerHTML = "";
                if ('undefined' !== typeof filter_criteria_obj.options[filter_criteria_obj.selectedindex]) {
                    if (sel_fields[currField.value] && (filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value=="e" || filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value=="n")) {
                        document.getElementById("node3span" + row_i + "_ajx").style.display = "block";
                        document.getElementById("node3span" + row_i + "_st").style.display = "none";
                    } else {
                        document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                        if(filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value=="isn" || filter_criteria_obj.options[filter_criteria_obj.selectedIndex].value=="isnn"){
                            document.getElementById("node3span" + row_i + "_st").style.display = "none";
                        }else{
                            document.getElementById("node3span" + row_i + "_st").style.display = "block";
                        }
                    }   
                }
                document.getElementById("node3span" + row_i + "_djx").style.display = "none";
            }
        }

    });
}

function getFilterDateHtml(row_i, r_sel_fields) {
    var node3span_obj = document.getElementById("node3span" + row_i + "_st");
    
    var reportID = getReportRecordID();

    var postData = {
        "mode": "getFilterDateHtml",
        "columnIndex": row_i,
        "record": getReportRecordID()
    };

    if (typeof r_sel_fields != 'undefined' && r_sel_fields != "") {
        postData["r_sel_fields"] = r_sel_fields;
    }
    if (typeof document.getElementById("fop" + row_i) != 'undefined') {
        var fop_type_val = document.getElementById("fop" + row_i).value;
        postData["fop_type"] = fop_type_val;
    }

    var actionParams = {
        "type": "POST",
        "url": 'index.php?module=ITS4YouReports&action=IndexAjax',
        "dataType": "html",
        "data": postData
    };
//index.php?module=ITS4YouReports&action=IndexAjax&mode=getFilterDateHtml&columnIndex=3&record=117&r_sel_fields=<;@STDV@;>&fop_type=af
    var aDeferred = jQuery.Deferred();
//alert(JSON.stringify(actionParams));

    app.request.post(actionParams).then(
        function(err,dataResult) {
            if(err === null){

                if(jQuery.inArray(fop_type_val,JSON.parse(jQuery('#fld_date_options').val()))>-1){
                    var fieldValue = getFieldLabelExpression(jQuery('#fvalhdn' + row_i).val());
                    document.getElementById("fval" + row_i).value = fieldValue;
                    document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                    document.getElementById("node3span" + row_i + "_djx").innerHTML = dataResult;
                    document.getElementById("node3span" + row_i + "_djx").style.display = "none";
                    document.getElementById("div_nfval" + row_i).style.display = "none";
                    document.getElementById("node3span" + row_i + "_st").style.display = "block";
                }else if( fop_type_val==="olderNdays" ||fop_type_val==="lastNdays" || fop_type_val==="nextNdays" || fop_type_val==="moreNdays" || fop_type_val==="daysago" || fop_type_val==="daysmore"){
                    document.getElementById("fval" + row_i).value = "";
                    document.getElementById("node3span" + row_i + "_st").style.display = "none";
                    document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                    document.getElementById("node3span" + row_i + "_djx").innerHTML = dataResult;
                    document.getElementById("node3span" + row_i + "_djx").style.display = "block";
                    document.getElementById("div_nfval" + row_i).style.display = "block";
                }else{
                    document.getElementById("node3span" + row_i + "_djx").innerHTML = dataResult;
                    document.getElementById("node3span" + row_i + "_st").style.display = "none";
                    document.getElementById("node3span" + row_i + "_ajx").style.display = "none";
                    document.getElementById("fval" + row_i).value = "";
                    document.getElementById("node3span" + row_i + "_djx").style.display = "block";
                    document.getElementById("div_nfval" + row_i).style.display = "none";
                }
                if(fop_type_val==="custom" || fop_type_val.indexOf('Ndays') > -1) {
                    let opObj = document.getElementById('fop' + row_i);
                    let currOp = opObj.options[opObj.selectedIndex];
                    let seOption_type = currOp.value;
                    let s_obj = document.getElementById("jscal_field_sdate" + row_i);
                    let e_obj = document.getElementById("jscal_field_edate" + row_i);
                    let st_obj = document.getElementById("jscal_trigger_sdate" + row_i);
                    let et_obj = document.getElementById("jscal_trigger_edate" + row_i);
                    addRequiredElements('f', row_i);
                    showDateRange(s_obj, e_obj, st_obj, et_obj, seOption_type);

                    let filterConditionContainer = jQuery('.filterConditionContainer');
                    filterConditionContainer.find('.dateField').on('change', function () {
                        jQuery('#saveReportBtn').removeClass('hide');
                        jQuery('#report_changed').val('1');
                    });

                }

            }
        });
    return aDeferred.promise();
}

function ClearFieldToFilter(id){
    jQuery('#fval'+id).val('');
    
    var comparatorId = "fop" + id;
    var comparatorObject = jQuery("#"+comparatorId);
    var comparatorValue = comparatorObject.val();
    if(jQuery.inArray(comparatorValue,JSON.parse(jQuery('#fld_date_options').val()))<0){
        jQuery('#fval'+id).removeAttr('readonly');
    }
    
    var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
    
    if(sel_fields[jQuery('#fvalhdn'+id).val()]){
        document.getElementById("node3span" + id + "_st").style.display = "none";
        document.getElementById("node3span" + id + "_djx").style.display = "none";
        document.getElementById("node3span" + id + "_ajx").style.display = "block";
    }
    jQuery('#fvalhdn'+id).val('');
}

function getColumnStrColumnName(sv_name){
    var returnVal = false;
    if(sv_name){
        sv_name = jQuery(sv_name);
        var column_str_array = sv_name.val().split(":");
        if(column_str_array[1]!=undefined){
            var columnname = column_str_array[1];
            if(column_str_array[6]!=undefined){
                columnname = columnname+'_'+column_str_array[6];
            }else if(column_str_array[5]!=undefined){
                columnname = columnname+'_'+column_str_array[5];
            }
        }
        returnVal = columnname;
    }
    return returnVal;
}

function getFieldLabelExpression(columnStr){
    var fieldValue = columnText = '';
    if(columnStr!=""){
        var column_str_array = columnStr.split(":");
        
        var optObj = jQuery('#fieldExpressionsBase'+2);
        var filtercolumns = jQuery('#filter_columns').html();
        var optgroups = filtercolumns.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++){
            var optgroup = optgroups[i].split("(|@|)");
            
            if (optgroup[0] != ''){
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = optgroup[0];
                
                var responseVal = JSON.parse(optgroup[1]);
                
                for (var widgetId in responseVal){
                    if (responseVal.hasOwnProperty(widgetId)){
                        option = responseVal[widgetId];
                        if(option["value"]==columnStr){
                            columnText = option["text"];
                        }
                    }
                }
            }
        }

        if(column_str_array[1]!=undefined){
            var moduleNameArr = column_str_array[2].split("_");
            var moduleName = moduleNameArr[0];
            //var moduleNameLbl = app.vtranslate(moduleName,moduleName);
            //var columnLabel = moduleNameLbl+'.'+columnText;
            var columnLabel = columnText;
        }
        fieldValue = columnLabel;
    }
    return fieldValue;
}

function AddFieldToFilter(id, sel) {
    var rowContainer = jQuery('#fieldExpressionsBase'+id);
    
    var selObjOption = jQuery(sel).find('option:selected');
    
    var fieldOptStr = selObjOption.val();
    
    var fieldValue = getFieldLabelExpression(selObjOption.val());
        
    jQuery('#fvalhdn' + id).val(fieldOptStr);
    
    jQuery('#fval' + id).val(fieldValue);
    jQuery('#fval'+ id).attr("readonly","true");
    
    jQuery('#fieldExpressionsBase'+ id).css('display', 'none');
    
    jQuery('#node3span'+id+'_st').css('display', 'block');
    jQuery('#node3span'+id+'_ajx').css('display', 'none');
    jQuery('#node3span'+id+'_djx').css('display', 'none');
}

function setRelPopupClick(clickBtn,columnIndex){
    var clickBtnObj = jQuery('#'+clickBtn);
    clickBtnObj.on('click',function(e) {

        var topOffset = jQuery(this).offset().top;
        topOffset = (topOffset-(topOffset/2)-150);
        var leftOffset = jQuery(this).offset().left;
        leftOffset = (leftOffset-(leftOffset/2));

        var fieldExpressionsRow = jQuery('#fieldExpressionsBase').html();
        fieldExpressionsRow = replaceAll('fieldExpressionsBase hide', 'fieldExpressionsBase', fieldExpressionsRow);
        fieldExpressionsRow = replaceAll('WCCINRW', columnIndex, fieldExpressionsRow);
        
        jQuery('#fieldExpressionsBase'+columnIndex).remove();
        
        var filtercolumns = jQuery('#filter_columns').html();
        
        jQuery('#adv_filter_div').append(fieldExpressionsRow);

        jQuery('#fieldExpressionsBase'+columnIndex).offset({top: topOffset, left: leftOffset});

        var std_filter_columns = jQuery('#std_filter_columns').val();
        var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
        var columnId = "fcol" + columnIndex;
        var colObj = getObj(columnId);
        var selected_value = html_entity_decode(colObj.value,"UTF-8");
        
        var opt_limitation = new Array();
        if (std_filter_columns_arr.indexOf(selected_value) > -1){
            opt_limitation.push('D');
            opt_limitation.push('DT');
        }
        
        var optgroups = filtercolumns.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++){
            var optgroup = optgroups[i].split("(|@|)");
            
            if (optgroup[0] != ''){
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = optgroup[0];
                
                var responseVal = JSON.parse(optgroup[1]);
                
                for (var widgetId in responseVal){
                    if (responseVal.hasOwnProperty(widgetId)){
                        option = responseVal[widgetId];
                        var fieldtype = trimfValues(option["value"]);
                        if(opt_limitation.length > 0){
                            if(jQuery.inArray(fieldtype,opt_limitation)>-1){
                                var oOption = document.createElement("OPTION");
                                oOption.value = option["value"];
                                oOption.appendChild(document.createTextNode(option["text"]));
                                oOptgroup.appendChild(oOption);
                            }
                        }else{
                            var oOption = document.createElement("OPTION");
                            oOption.value = option["value"];
                            oOption.appendChild(document.createTextNode(option["text"]));
                            oOptgroup.appendChild(oOption);
                        }
                    }
                }
                document.getElementById('fc_fval_' + columnIndex).appendChild(oOptgroup);
            }
        }
        jQuery('#fc_fval_' + columnIndex).addClass('select2');

        app.changeSelectElementView(jQuery('#fieldExpressionsBase' + columnIndex));
    })
}

function displayRelPopupDiv(container){
    container.on('click','.getPopupUi',function(e) {
    	var fieldValueElement = jQuery(e.currentTarget);
    	var fieldValue = fieldValueElement.val();
    	var fieldUiHolder  = fieldValueElement.closest('.fieldUiHolder');
    	var valueType = fieldUiHolder.find('[name="valuetype"]').val();
    	if(valueType == '') {
    		valueType = 'rawtext';
    	}
    	var conditionsContainer = fieldValueElement.closest('.conditionsContainer');
    	var conditionRow = fieldValueElement.closest('.conditionRow');
    	
    	var clonedPopupUi = conditionsContainer.find('.popupUi').clone(true,true).removeClass('popupUi').addClass('clonedPopupUi')
    	clonedPopupUi.find('select').addClass('chzn-select');
    	clonedPopupUi.find('.fieldValue').val(fieldValue);
    	if(fieldValueElement.hasClass('date')){
    		clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui','input');
    		var dataFormat = fieldValueElement.data('date-format');
    		if(valueType == 'rawtext') {
    			var value = fieldValueElement.val();
    		} else {
    			value = '';
    		}
    		var clonedDateElement = '<input type="text" class="row-fluid dateField fieldValue span4" value="'+value+'" data-date-format="'+dataFormat+'" data-input="true" >'
    		clonedPopupUi.find('.fieldValueContainer').prepend(clonedDateElement);
    	} else if(fieldValueElement.hasClass('time')) {
    		clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui','input');
    		if(valueType == 'rawtext') {
    			var value = fieldValueElement.val();
    		} else {
    			value = '';
    		}
    		var clonedTimeElement = '<input type="text" class="row-fluid timepicker-default fieldValue span4" value="'+value+'" data-input="true" >'
    		clonedPopupUi.find('.fieldValueContainer').prepend(clonedTimeElement);
    	} else if(fieldValueElement.hasClass('boolean')) {
    		clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui','input');
    		if(valueType == 'rawtext') {
    			var value = fieldValueElement.val();
    		} else {
    			value = '';
    		}
    		var clonedBooleanElement = '<input type="checkbox" class="row-fluid fieldValue span4" value="'+value+'" data-input="true" >';
    		clonedPopupUi.find('.fieldValueContainer').prepend(clonedBooleanElement);
    		
    		var fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
    		if(value == 'true:boolean' || value == '') {
    			clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
    		} else {
    			clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
    		}
    	}
    	var callBackFunction = function(data) {
    		data.find('.clonedPopupUi').removeClass('hide');
    		var moduleNameElement = conditionRow.find('[name="modulename"]');
    		if(moduleNameElement.length > 0){
    			var moduleName = moduleNameElement.val();
    			data.find('.useFieldElement').addClass('hide');
    			data.find('[name="'+moduleName+'"]').removeClass('hide');
    		}
    		app.changeSelectElementView(data);
    		app.registerEventForDatePickerFields(data);
    		app.registerEventForTimeFields(data);
    		thisInstance.postShowModalAction(data,valueType);
    		thisInstance.registerChangeFieldEvent(data);
    		thisInstance.registerSelectOptionEvent(data);
    		thisInstance.registerPopUpSaveEvent(data,fieldUiHolder);
    		thisInstance.registerRemoveModalEvent(data);
    		data.find('.fieldValue').filter(':visible').trigger('focus');
    	}
    	conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);
    	jQuery('.clonedPopupUi').on('shown', function () {
    		if(typeof callBackFunction == 'function'){
    			callBackFunction(jQuery('.clonedPopupUi',conditionsContainer));
    		}
    	});
    	jQuery('.clonedPopUp',conditionsContainer).find('.clonedPopupUi').modal();
    });
}

function fnLoadRepValues(tab1, tab2, block1, block2) {
    document.getElementById(block1).style.display = 'block';
    document.getElementById(block2).style.display = 'none';
    document.getElementById(tab1).className = 'dvtSelectedCell';
    document.getElementById(tab2).className = 'dvtUnSelectedCell';
}

/**
 * IE has a bug where document.getElementsByName doesnt include result of dynamically created 
 * elements
 */
function vt_getElementsByName(tagName, elementName) {
    var inputs = document.getElementsByTagName(tagName);
    var selectedElements = [];
    for (var i = 0; i < inputs.length; i++) {
        if (inputs.item(i).getAttribute('name') == elementName) {
            selectedElements.push(inputs.item(i));
        }
    }
    return selectedElements;
}

function formSelectQFColumnString()
{
    var selectedColStr = "";
    var selectedQFColumnsObj = getObj("selectedQFColumns");

    for (i = 0; i < selectedQFColumnsObj.options.length; i++)
    {
        selectedColStr += selectedQFColumnsObj.options[i].value + ";";
    }
    document.NewReport.selectedQFColumnsString.value = selectedColStr;
}

function uncheckAll(el)
{
    jQuery('#' + el + ' option:selected').removeAttr('selected');
    jQuery('#' + el).trigger('liszt:updated');
    jQuery('#s2id_' + el).find('li.select2-search-choice').remove();
}
function checkAll(el)
{
    selObj = document.getElementById(el);

    for (var i = 0; i < selObj.length; i++)
        selObj.options[i].selected = true;

}

// ITS4YOU MaJu customreports
function CreateCustomReport(reporttype)
{
    var arg = 'index.php?module=ITS4YouReports&action=NewCustomReport&folder=' + gcurrepfolderid + '&reporttype=' + reporttype + '&mode=create';
    document.location.href = arg;
}

function editCustomReport(id, reporttype)
{
    var arg = 'index.php?module=ITS4YouReports&action=NewCustomReport&record=' + id + '&reporttype=' + reporttype + '&mode=edit';
    document.location.href = arg;
}

function setRelModules(relMod) {
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

function setScheduleOptions() {

    var stid = jQuery('#scheduledType').val();
    switch (stid) {
        case "0": // nothing choosen
        case "1": // hourly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'block';
            break;
        case "2": // daily
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'block';
            break;
        case "3": // weekly
        case "4": // bi-weekly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'none';
            document.getElementById('scheduledDOWSpan').style.display = 'block';
            document.getElementById('scheduledTimeSpan').style.display = 'block';
            break;
        case "5": // monthly
            document.getElementById('scheduledMonthSpan').style.display = 'none';
            document.getElementById('scheduledDOMSpan').style.display = 'block';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'block';
            break;
        case "6": // annually
            document.getElementById('scheduledMonthSpan').style.display = 'block';
            document.getElementById('scheduledDOMSpan').style.display = 'block';
            document.getElementById('scheduledDOWSpan').style.display = 'none';
            document.getElementById('scheduledTimeSpan').style.display = 'block';
            break;
    }
}

function r4youCompareDates(compareType, dateObj1, dateObj2) {

    // dd/mm/yyyy
    vtUtils.hideValidationMessage(dateObj1);
    let date1Val = dateObj1.val();
    let dateElements1 = splitDateVal(date1Val);
    let dd1 = dateElements1[0];
    let mm1 = dateElements1[1];
    let yyyy1 = dateElements1[2];
    //let date1 =  dd1 + '/' + mm1 + '/' + yyyy1;
    let date1 = new Date(yyyy1, mm1, dd1);

    vtUtils.hideValidationMessage(dateObj2);
    let date2Val = dateObj2.val();
    let dateElements2 = splitDateVal(date2Val);
    let dd2 = dateElements2[0];
    let mm2 = dateElements2[1];
    let yyyy2 = dateElements2[2];
    //let date2 = dd2 + '/' + mm2 + '/' + yyyy2;
    let date2 = new Date(yyyy2, mm2, dd2);

    let returnCheckout = false;
    // 1 less than 2
    if ('lt' === compareType) {
        // 1 < 2
        if (date1 <= date2) {
            returnCheckout = true;
        } else {
            var message = date1Val+ ' ' +app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO') + ' ' + date2Val;
            vtUtils.showValidationMessage(dateObj1, message);
            console.log(message);
        }
        // 1 greater than 2
    } else if ('gt' === compareType) {
        // 1 > 2
        if (date1 >= date2) {
            returnCheckout = true;
        } else {
            var message = date2Val+ ' ' +app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO') + ' ' + date1Val;
            vtUtils.showValidationMessage(dateObj1, message);
            console.log(message);
        }
    }
    return returnCheckout;
}

function emptyCheck4You(fldName, fldLabel, fldType) {
    var moduleName = app.getModuleName();
    var currObj = getObj(fldName);

    // ITS4YOU-UP SlOl 16. 9. 2015 14:59:15
    if (fldName == "fcol0" || fldName == "fop0" || fldName == "fval0") {
        var conditionColumns = vt_getElementsByName('tr', "conditionColumn");
        if (conditionColumns.length == 1) {
            return true;
        }
    }
    // ITS4YOU-E SlOl

    const fldInputType = 'select';
    let inputElement = jQuery(fldInputType + '[name="' + fldName + '"]');
    if (typeof inputElement.val() === 'undefined') {
        const fldInputType = 'input';
        inputElement = jQuery(fldInputType + '[name="' + fldName + '"]');
        if (typeof inputElement.val() === 'undefined') {
            const fldInputType = 'textarea';
            inputElement = jQuery(fldInputType + '[name="' + fldName + '"]');
            if (typeof inputElement.html() === 'undefined') {
                inputElement = jQuery('#' + fldName);
            }
        }
    }
    const errLabel = fldLabel + app.vtranslate('VAL_CANNOT_BE_EMPTY', moduleName);
    let errorValidation = false;
    let useId = false;

    if(typeof currObj ==='undefined') {
        alert(fldName);
    }

    if (inputElement.hasClass('dateField')) {
        useId = true;
        let dateValidate = ITS4YouReportsDateValidate(fldName, fldLabel);
        if (true !== dateValidate) {
            errorValidation = true;
        }
    } else if (fldType === "text") {
        if (currObj.value == "" || currObj.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length == 0) {
            if (typeof jQuery("#s_" + fldName).val() != 'undefined' && jQuery("#s_" + fldName).val() != "") {
                return true;
            }
            errorValidation = true;
        }
    } else if ((fldType === "textarea")
        && (typeof (CKEDITOR) !== 'undefined' && CKEDITOR.intances[fldName] !== 'undefined')) {
        const textObj = CKEDITOR.intances[fldName];
        const textValue = textObj.getData();
        if (trim(textValue) === '' || trim(textValue) === '<br>') {
            errorValidation = true;
        }
    } else {
        if (trim(currObj.value) === '') {
            errorValidation = true;
        }
    }
    if (errorValidation) {
        if ('undefined' !== typeof inputElement) {
            vtUtils.showValidationMessage(inputElement, errLabel);
        } else {
            alert('undefined > ' + fldName);
        }
        return false;
    }
    return true;
}
// ITS4YOU-CR SlOl 19. 2. 2014 13:04:56
function getFieldsOptionsSearch(search_input) {
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();
    var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
    var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

    aviable_fields = document.getElementById("availModValues").innerHTML;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    var selectedModule = document.getElementById("availModules").options[availModules.selectedIndex].value;
    if (module_groupid == selectedModule) {
        document.getElementById('availList').innerHTML = "";

        var optgroups = aviable_fields.split("(|@!@|)");
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
function getSUMFieldsOptionsSearch(search_input) {
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();
    var selectedPrimaryIndex = document.getElementById('primarymodule').selectedIndex;
    var selectedPrimaryModule = document.getElementById('primarymodule').options[selectedPrimaryIndex].value;

    aviable_fields = document.getElementById("availSumModValues").innerHTML;
    //alert(aviable_fields);
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    var AvaiSelectedModules = document.getElementById("SummariesModules");
    var selectedModule = AvaiSelectedModules.options[AvaiSelectedModules.selectedIndex].value;
    var selectedModuleText = SummariesModules.options[SummariesModules.selectedIndex].text;
    if (module_groupid === selectedModule) {
        document.getElementById('availListSum').innerHTML = "";

        var optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++)
        {

            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '')
            {
                var oOptgroup = document.createElement("OPTGROUP");
                //oOptgroup.label = selectedModuleText + " - " + optgroup[0];
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
function getTLFieldsOptionsSearch(search_input) {
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
function addOndblclick(vOption) {
    if ((vOption.selectedIndex) || vOption.selectedIndex == 0) {
        var selectedOption = vOption.options[vOption.selectedIndex].value;
        addColumn('selectedColumns');
    }
}
// ITS4YOU-CR SlOl 6. 3. 2014 9:43:29
function addOndblclickSUM(vOption) {
    if ((vOption.selectedIndex) || vOption.selectedIndex == 0) {
        var selectedOption = vOption.options[vOption.selectedIndex].value;
        addColumn('selectedSummaries');
    }
}
// ITS4YOU-END 19. 2. 2014 13:05:01
function defineModuleFields(availModules) {

    setModuleFields(availModules, "getStep5Columns");
}

function defineSUMModuleFields(availModules) {

    setModuleFields(availModules, "getStep5SUMColumns");
}

function setModuleFields(availModules, mode) {

    if (mode == "getStep5SUMColumns") {
        var search_input_name = "search_input_sum";
        var field_name = "SummariesModules";
        var is_sum = "Sum";
    } else if (mode == "getStep5Columns") {
        var search_input_name = "search_input";
        var field_name = "availModValues";
        var is_sum = "";
    }

    var selectedModule = availModules.options[availModules.selectedIndex].value;
    var field_options = document.getElementById(field_name).innerHTML;

    document.getElementById(search_input_name).value = "";

    var postData = {
        "selectedmodule": selectedModule,
        "mode": mode
    };
    
    var primarymodule = jQuery('#primarymodule').val();

    var actionParams = {
        "type": "POST",
        "url": 'index.php?module=ITS4YouReports&action=IndexAjax&primarymoduleid='+primarymodule+'&record=' + getReportRecordID(),
        "dataType": "html",
        "data": postData
    };
//index.php?module=ITS4YouReports&action=IndexAjax&primarymoduleid=13&selectedmodule=13&mode=getStep5Columns
//window.open('index.php?module=ITS4YouReports&action=IndexAjax&primarymoduleid='+primarymodule+'&record=' + getReportRecordID()+'&selectedmodule='+selectedModule+'&mode='+mode);
    app.request.post(actionParams).then(
        function(err, data){
            if(err === null){
                setAvailableFields(data, is_sum);
            }
        }
    );
}

function setStep5Columns(step5_result) {

    var availablemodules = JSON.parse(step5_result[0]);
    var aviable_fields = step5_result[1];

    var avaimodules_sbox = document.getElementById('availModules');
    avaimodules_sbox.innerHTML = "";
    avaimodules_sbox.options.length = 0;
    for (var widgetId in availablemodules) {
        if (availablemodules.hasOwnProperty(widgetId)) {
            var option = availablemodules[widgetId];
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
    setAvailableFields(aviable_fields, "")
}
function setAvailableFields(aviable_fields, is_sum) {
    aviable_fields = $.trim(aviable_fields);
    document.getElementById("avail" + is_sum + "ModValues").innerHTML = aviable_fields;
    var mod_groups_a2 = aviable_fields.split("(!#_ID@ID_#!)");
    var module_groupid = mod_groups_a2[0];
    var module_name = mod_groups_a2[1];
    var aviable_fields = mod_groups_a2[2];

    if (is_sum != "") {
        var availModules = document.getElementById("SummariesModules");
        var selectedModuleText = availModules.options[availModules.selectedIndex].text + " - ";
    } else {
        var availModules = document.getElementById("availModules");
        var selectedModuleText = "";
    }
    var selectedModule = availModules.options[availModules.selectedIndex].value;

    if (module_groupid == selectedModule) {
        document.getElementById('availList' + is_sum).innerHTML = "";

        var optgroups = aviable_fields.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++) {

            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] != '') {
                var oOptgroup = document.createElement("OPTGROUP");
                //oOptgroup.label = selectedModuleText + optgroup[0];
                oOptgroup.label = optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);
                for (var widgetId in responseVal) {
                    if (responseVal.hasOwnProperty(widgetId)) {
                        option = responseVal[widgetId];
                        var oOption = document.createElement("OPTION");
                        oOption.value = option["value"];
                        oOption.appendChild(document.createTextNode(option["text"]));
                        oOptgroup.appendChild(oOption);
                        document.getElementById('availList' + is_sum).appendChild(oOptgroup);
                    }
                }
            }
        }
    }
}
function setAvailableSUMFields(aviable_fields) {
    setAvailableFields(aviable_fields, "Sum");
}
function checkEmptyLabel(input_id) {
    if (document.getElementById(input_id)) {
        var str = document.getElementById(input_id).value;
        if (str.trim() == "") {
            document.getElementById(input_id).value = document.getElementById("hidden_" + input_id).value;
        }
    }
}
/* ITS4YOU-CR SlOl | 13.5.2014 12:09 */
function escapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}
/* ITS4YOU-CR SlOl | 20.4.2015 12:28 */
function matrix_js(group_i){
    if(group_i==2){
        var sec_group_i = group_i+1;
    }else if(group_i==3){
        var sec_group_i = group_i-1;
    }
    
    //alert(group_i+" - "+jQuery("#timeline_type"+group_i+"  :selected").val()+" - "+sec_group_i+" - "+jQuery("#timeline_type"+sec_group_i+' :selected').val());
    
    var timeline_type = document.getElementById("timeline_type"+group_i);
    var timeline_typeSec = document.getElementById("timeline_type"+sec_group_i);
    
    var Group = document.getElementById("Group"+group_i);
    var GroupSec = document.getElementById("Group"+sec_group_i);
    
    if(group_i==2 && jQuery('#Group'+sec_group_i).val()=="none"){
        timeline_type.options[1].selected = true;
        timeline_typeSec.options[0].selected = true;
        return false;
    }else if(group_i==3 && jQuery('#Group'+group_i).val()=="none"){
        timeline_type.options[0].selected = true;
        timeline_typeSec.options[1].selected = true;
        return false;
    }
    
    if(group_i==2){
        if(jQuery("#timeline_type"+group_i).val()=="cols"){
            jQuery("#timeline_type"+sec_group_i).val("rows");
        }else{
            jQuery("#timeline_type"+sec_group_i).val("cols");
        }
    }else if(group_i==3){
        if(jQuery("#timeline_type"+group_i).val()=="cols"){
            jQuery("#timeline_type"+sec_group_i).val("rows");
        }else{
            jQuery("#timeline_type"+sec_group_i).val("cols");
        }
    }
    
}
/* ITS4YOU-END */
function checkTimeLineColumn(groupObj, group_i) {
    if (group_i > 1) {
        if (document.getElementById("Group1").options[document.getElementById("Group1").selectedIndex].value == "none") {
            alert(document.getElementById("Group1").name + app.vtranslate('CANNOT_BE_EMPTY'));
            groupObj.options[0].selected = true;
            return false;
        }
        if (group_i > 2) {
            if (document.getElementById("Group2").options[document.getElementById("Group2").selectedIndex].value == "none") {
                alert(document.getElementById("Group2").name + app.vtranslate('CANNOT_BE_EMPTY'));
                groupObj.options[0].selected = true;
                return false;
            }
        }
    }
    if (groupObj) {
        var selected_option = groupObj.options[groupObj.selectedIndex].value;
        if (document.getElementById("date_options_json")) {
            var date_options_json = document.getElementById("date_options_json").innerHTML;
            if (date_options_json) {
                var date_options = JSON.stringify(date_options_json);
                if (date_options.indexOf(selected_option) > 0) {
                    // timelinecolumn_html to replace @NMColStr , need to replace with col_str and then insert into div id = radio_group1
                    var timelinecolumn_html = jQuery('#timelinecolumn_html').val();
                    var timelinecolumn_html = replaceAll("@NMColStr", "_Group" + group_i, timelinecolumn_html);

                    var timelinecolumn_html = replaceAll("value='", "value='" + selected_option, timelinecolumn_html);

                    document.getElementById("radio_group" + group_i).innerHTML = timelinecolumn_html;
                    app.changeSelectElementView(jQuery('#radio_group' + group_i));
                } else {
                    document.getElementById("radio_group" + group_i).innerHTML = "";
                }
            }
        }
    }
}
function getGroupTimeLineValue(group_i) {
    var timeline_frequency = "";
    if (document.getElementsByName('TimeLineColumn_Group' + group_i)) {
        var TimeLineColumnRadios = document.getElementsByName('TimeLineColumn_Group' + group_i);
        for (var i = 0, length = TimeLineColumnRadios.length; i < length; i++) {
            if (TimeLineColumnRadios[i].checked) {
                timeline_frequency = TimeLineColumnRadios[i].value;
                break;
            }
        }
    }
    return timeline_frequency;
}
function getGroupTimeLineType(group_i) {
    var timeline_type_val = "";
    if (document.getElementById('timeline_type' + group_i)) {
        var timeline_type = document.getElementById('timeline_type' + group_i);
        timeline_type_val = timeline_type.options[timeline_type.selectedIndex].value;
    }
    return timeline_type_val;
}
/* ITS4YOU-END */
/* ITS4YOU-CR SlOl | 23.6.2014 15:01  */
function getUpSelectedSharing() {
    const sharingSelectedStr = jQuery('#sharingSelectedColumns').val();
    /*if (document.getElementById('sharingSelectedColumns')) {
        sharingSelectedColumns = document.getElementById('sharingSelectedColumns');
        sharingSelectedStr = "";
        for (i = 0; i <= (sharingSelectedColumns.length - 1); i++)
        {
            sharingSelectedStr += sharingSelectedColumns[i].value + '|';
        }
        document.getElementById('sharingSelectedColumnsString').value = sharingSelectedStr;
    }*/
    return sharingSelectedStr;
}
/* ITS4YOU-END 23.6.2014 15:02  */
/* ITS4YOU-CR SlOl | 2.7.2014 11:45 */
function defineChartType(chart_type_element) {
    var chart_type_option = chart_type_element.options[chart_type_element.selectedIndex];

    for (var i = 0; i < chart_type_element.options.length; i++)
    {
        var image_id = chart_type_element.options[i].value + "_type";
        if (document.getElementById(image_id)) {
            document.getElementById(image_id).style.display = "none";
        }
    }

    if (chart_type_option.value != null && chart_type_option.value.length != 0)
    {
        var chart_type_value = chart_type_option.value;
        var image_id = chart_type_value + "_type";
        setChartColumns(chart_type_option);
        if (document.getElementById(image_id)) {
            document.getElementById(image_id).style.display = "block";
        }
    }
}
/* ITS4YOU-CR SlOl | 2.7.2014 13:27 */
function setChartTitle(ch_title_obj) {
    var chart_type_element1 = document.getElementById("chartType1");
    var chart_type_option1 = chart_type_element.options[chart_type_element1.selectedIndex];
    var chart_type_element2 = document.getElementById("chartType2");
    var chart_type_option2 = chart_type_element.options[chart_type_element2.selectedIndex];
    var chart_type_element3 = document.getElementById("chartType3");
    var chart_type_option3 = chart_type_element.options[chart_type_element3.selectedIndex];
    if (chart_type_option1.value === "none" && chart_type_option2.value === "none" && chart_type_option3.value === "none") {
        return false;
    }
    if (ch_title_obj && ch_title_obj.value != "") {
        if (document.getElementById("chart_title_div")) {
            document.getElementById("chart_title_div").innerHTML = ch_title_obj.value;
        }
    }
}
function setChartColumns(chart_type_option) {
    if (chart_type_option == "pie" || chart_type_option == "funnel") {
        document.getElementById("ycols").innerHTML = "&nbsp";
        document.getElementById("xcols").innerHTML = "&nbsp";
    }
}

function splitDateVal(dateval) {
    let dateelements = '';
    let dateFormatElements = '';
    let returnArray = [];
    if('undefined' === typeof its4youUserDateFormat) {
        alert('date format undefined');
        return false;
    } else {
        if(dateval.indexOf('-')) {
            dateFormatElements = its4youUserDateFormat.split('-');
            dateelements = dateval.split('-');
        } else {
            dateFormatElements = its4youUserDateFormat.split('.');
            dateelements = dateval.split('.');
        }
        for (let i = 0; i < dateFormatElements.length; i++) {
            if (dateFormatElements[i]) {
                if('dd'===dateFormatElements[i]){
                    returnArray[0] = dateelements[i];
                } else if('mm'===dateFormatElements[i]){
                    returnArray[1] = dateelements[i];
                } else if('yyyy'===dateFormatElements[i]){
                    returnArray[2] = dateelements[i];
                }
            }
        }
        return returnArray;
    }
}

/* ITS4YOU-CR SlOl | 22.7.2014 13:45 */
function ITS4YouReportsDateValidate(fldName, fldLabel) {
    if (patternValidate(fldName, fldLabel, "DATE") == false)
        return false;

    let dateObj = jQuery('#'+fldName);
    let dateval = dateObj.val();
    let checkVal = dateval.replace(/,/g , '').replace(/\./g, '');

    if ('' !== checkVal) {
        var dateelements = splitDateVal(dateval);

        var dd = dateelements[0]
        var mm = dateelements[1]
        var yyyy = dateelements[2]

        if (dd < 1 || dd > 31 || mm < 1 || mm > 12 || yyyy < 1 || yyyy < 1000) {
            try {
                dateObj.focus()
            } catch (error) {
            }
            return false
        }

        if ((mm == 2) && (dd > 29)) {//checking of no. of days in february month
            //alert(app.vtranslate('ENTER_VALID') + fldLabel);
            try {
                dateObj.focus()
            } catch (error) {
            }
            return false
        }

        if ((mm == 2) && (dd > 28) && ((yyyy % 4) != 0)) {//leap year checking
            //alert(app.vtranslate('ENTER_VALID') + fldLabel);
            try {
                dateObj.focus()
            } catch (error) {
            }
            return false
        }

        switch (parseInt(mm)) {
            case 2 :
            case 4 :
            case 6 :
            case 9 :
            case 11 :
                if (dd > 30) {
                    //alert(app.vtranslate('ENTER_VALID') + fldLabel);
                    try {
                        dateObj.focus()
                    } catch (error) {
                    }
                    return false
                }
        }

        var currdate = new Date()
        var chkdate = new Date()

        chkdate.setYear(yyyy)
        chkdate.setMonth(mm - 1)
        chkdate.setDate(dd)

    } else {
        return false
    }

    return true;
}
function ITS4YouReportsre_dateValidate(fldval, fldLabel, type) {
    if (re_patternValidate(fldval, fldLabel, "DATE") == false)
        return false;
    var dateval = fldval.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements = splitDateVal(dateval)

    var dd = dateelements[0]
    var mm = dateelements[1]
    var yyyy = dateelements[2]

    if (dd < 1 || dd > 31 || mm < 1 || mm > 12 || yyyy < 1 || yyyy < 1000) {
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    if ((mm == 2) && (dd > 29)) {//checking of no. of days in february month
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    if ((mm == 2) && (dd > 28) && ((yyyy % 4) != 0)) {//leap year checking
        alert(app.vtranslate('ENTER_VALID') + fldLabel);
        return false
    }

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
        case 11 :
            if (dd > 30) {
                alert(app.vtranslate('ENTER_VALID') + fldLabel);
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
function ITS4YouReportsStDateValidate(columnIndex, comparatorValue, fldLabel) {
    if (typeof document.getElementById("jscal_field_sdate" + columnIndex) != 'undefined' && typeof document.getElementById("jscal_field_edate" + columnIndex) != 'undefined') {
        switch (comparatorValue) {
            case "custom" :
                if (!emptyCheck4You("jscal_field_sdate" + columnIndex, fldLabel, "date")) {
                    return false;
                }
                if (!emptyCheck4You("jscal_field_edate" + columnIndex, fldLabel, "date")) {
                    return false;
                }
                break;
        }
    }
    return true;
}
/* ITS4YOU-END 22.7.2014 13:45 */
/* ITS4YOU-END 22.7.2014 13:45 */
function setSelectedCriteriaValue(criteria_obj, selected_value) {
    for (var fci = 0; fci < criteria_obj.options.length; fci++) {
        if (html_entity_decode(criteria_obj.options[fci].value,"UTF-8") == selected_value) {
            criteria_obj.options[fci].selected = true;
        }
    }
}

function SaveChartImg(image_path, name) {
    if (document.getElementById("current_action")) {
        var c_action = document.getElementById("current_action").value;
    } else {
        var c_action = "";
    }
    //var download_url = "index.php?module=ITS4YouReports&action=ITS4YouReportsAjax&file=DownloadFile&filepath=" + image_path + "&filename=" + name + ".png";
    var download_url = "index.php?module=ITS4YouReports&action=IndexAjax&mode=DownloadFile&filepath=" + image_path + "&filename=" + name + ".png";
    var features = "width=20,height=20";
    var opened_win = window.open(download_url, name, features);
    return true;
}


function generatePDF(id, pdfmaker_active, is_test_write_able, div_id) {
    if (parseInt(pdfmaker_active) === 1) {
        /* document.location.href='index.php?action=ITS4YouReportsAjax&module=ITS4YouReports&record='+id+'&parenttab=Tools&file=Generate&mode=ajax&generate_type=CreatePDF'; */
        var report_filename_obj = document.getElementById('report_filename');
        if (report_filename_obj && report_filename_obj.value !== "") {

            exportToPDF(report_filename_obj.value);
            return true;
        } else if (parseInt(is_test_write_able) === 1) {
            document.getElementById(div_id).style.display = "block";
            return false;
        } else {
            document.getElementById(div_id).style.display = "block";
            return false;
        }
    } else {
        if('block'===document.getElementById(div_id).style.display) {
			jQuery('#'+div_id).css('border','1px solid red');
		}
		document.getElementById(div_id).style.display = "block";
        return false;
    }
}

function printDiv(){

    let footerElm = jQuery('.app-footer');
    if (!footerElm.hasClass('no-print')) {
        footerElm.addClass('no-print');
    }
    let app_menu = jQuery('#app-menu');
    if (!app_menu.hasClass('no-print')) {
        app_menu.addClass('no-print');
    }
    let appnavigator = jQuery('#appnavigator');
    if (!appnavigator.hasClass('no-print')) {
        appnavigator.addClass('no-print');
    }
    let app_modules = jQuery('.app-modules-dropdown-container');
    if (!app_modules.hasClass('no-print')) {
        app_modules.addClass('no-print');
    }

    window.print();

    return true;
}

/**
 * return Report Record id based on edit/detail view
 */
function getReportRecordID() {
    var view = jQuery("#view").val();
    var reportID = "";
    if(view==="Edit"){
        reportID = document.NewReport.record.value;
    }else{
        reportID = jQuery("#recordId").val();
    }
    return reportID;
}

// FROM vt540
function numValidate(fldName,fldLabel,format,neg) {
	var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '');
	if(typeof userCurrencySeparator != 'undefined' && userCurrencySeparator != '') {
		while(val.indexOf(userCurrencySeparator) != -1) {
			val = val.replace(userCurrencySeparator,'');
		}
	}
	if(typeof userDecimalSeparator != 'undefined' && userDecimalSeparator != '') {
		if(val.indexOf(userDecimalSeparator) != -1) {
			val = val.replace(userDecimalSeparator,'.');
		}
	}
	if (format!="any") {
		if (isNaN(val)) {
			var invalid=true
		} else {
			var format=format.split(",")
			var splitval=val.split(".")
			if (neg==true) {
				if (splitval[0].indexOf("-")>=0) {
					if (splitval[0].length-1>format[0])
						invalid=true
				} else {
					if (splitval[0].length>format[0])
						invalid=true
				}
			} else {
				if (val<0)
					invalid=true
				else if (format[0]==2 && splitval[0]==100 && (!splitval[1] || splitval[1]==0))
					invalid=false
				else if (splitval[0].length>format[0])
					invalid=true
			}
			if (splitval[1])
				if (splitval[1].length>format[1])
					invalid=true
		}
		if (invalid==true) {
			alert(app.vtranslate('INVALID')+fldLabel)
			try {
				getObj(fldName).focus()
			} catch(error) { }
			return false
		}else return true
	} else {
		// changes made -- to fix the ticket#3272
		if(fldName == "probability" || fldName == "commissionrate")
		{
			var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '');
			var splitval=val.split(".")
			var arr_len = splitval.length;
			var len = 0;

			if(arr_len > 1)
				len = splitval[1].length;
			if(isNaN(val))
			{
				alert(app.vtranslate('INVALID')+fldLabel)
				try {
					getObj(fldName).focus()
				}catch(error) { }
				return false
			}
			else if(splitval[0] > 100 || len > 3 || (splitval[0] >= 100 && splitval[1] > 0))
			{
				alert( fldLabel + app.vtranslate('EXCEEDS_MAX'));
				return false;
			}
		}
		else {
			var splitval=val.split(".")
			if(splitval[0]>18446744073709551615)
			{
				alert( fldLabel + app.vtranslate('EXCEEDS_MAX'));
				return false;
			}
		}

		if (neg==true)
			var re=/^(-|)(\d)*(\.)?\d+(\.\d\d*)*$/
		else
			var re=/^(\d)*(\.)?\d+(\.\d\d*)*$/
	}

	//for precision check. ie.number must contains only one "."
	var dotcount=0;
	for (var i = 0; i < val.length; i++)
	{
		if (val.charAt(i) == ".")
			dotcount++;
	}

	if(dotcount>1)
	{
		alert(app.vtranslate('INVALID')+fldLabel)
		try {
			getObj(fldName).focus()
		}catch(error) { }
		return false;
	}

	if (!re.test(val)) {
		alert(app.vtranslate('INVALID')+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	} else return true
}

function intValidate(fldName,fldLabel) {
	var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '');
	if(typeof userCurrencySeparator != 'undefined' && userCurrencySeparator != '') {
		while(val.indexOf(userCurrencySeparator) != -1) {
			val = val.replace(userCurrencySeparator,'');
		}
	}
	if (isNaN(val) || (val.indexOf(".")!=-1 && fldName != 'potential_amount' && fldName != 'list_price'))
	{
		alert(app.vtranslate('INVALID')+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}
	else if((fldName != 'employees' || fldName != 'noofemployees') && (val < -2147483648 || val > 2147483647))
	{
		alert(fldLabel +app.vtranslate('OUT_OF_RANGE'));
		return false;
	}
	else if((fldName == 'employees' || fldName != 'noofemployees') && (val < 0 || val > 2147483647))
	{
		alert(fldLabel +app.vtranslate('OUT_OF_RANGE'));
		return false;
	}
	else
	{
		return true
	}
}

function dateValidate(fldName,fldLabel,type) {
	if(patternValidate(fldName,fldLabel,"DATE")==false)
		return false;
	dateval=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

	var dateelements=splitDateVal(dateval)

	dd=dateelements[0]
	mm=dateelements[1]
	yyyy=dateelements[2]

	if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}

	if ((mm==2) && (dd>29)) {//checking of no. of days in february month
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
		try {
			getObj(fldName).focus()
		} catch(error) { }
		return false
	}

	if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
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
			alert(app.vtranslate('ENTER_VALID')+fldLabel)
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

function re_dateValidate(fldval,fldLabel,type) {
	if(re_patternValidate(fldval,fldLabel,"DATE")==false)
		return false;
	dateval=fldval.replace(/^\s+/g, '').replace(/\s+$/g, '')

	var dateelements=splitDateVal(dateval)

	dd=dateelements[0]
	mm=dateelements[1]
	yyyy=dateelements[2]

	if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
		return false
	}

	if ((mm==2) && (dd>29)) {//checking of no. of days in february month
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
		return false
	}

	if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
		alert(app.vtranslate('ENTER_VALID')+fldLabel)
		return false
	}

	switch (parseInt(mm)) {
		case 2 :
		case 4 :
		case 6 :
		case 9 :
		case 11 :
			if (dd>30) {
			alert(app.vtranslate('ENTER_VALID')+fldLabel)
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

//Copied from general.js and altered some lines. becos we cant send vales to function present in general.js. it accept only field names.
function re_patternValidate(fldval,fldLabel,type) {

	if (type.toUpperCase()=="EMAIL") {
		/*changes made to fix -- ticket#3278 & ticket#3461
		  var re=new RegExp(/^.+@.+\..+$/)*/
		//Changes made to fix tickets #4633, #5111  to accomodate all possible email formats
		var re=new RegExp(/^[a-zA-Z0-9]+([\_\-\.]*[a-zA-Z0-9]+[\_\-]?)*@[a-zA-Z0-9]+([\_\-]?[a-zA-Z0-9]+)*\.+([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)*$/)
	}

	if (type.toUpperCase()=="DATE") {//DATE validation

		switch (its4youUserDateFormat) {
			case "yyyy-mm-dd" :
				var re = /^\d{4}(-)\d{1,2}\1\d{1,2}$/
				break;
			case "mm-dd-yyyy" :
			case "dd-mm-yyyy" :
				var re = /^\d{1,2}(-)\d{1,2}\1\d{4}$/
		}
	}


	if (type.toUpperCase()=="TIMESECONDS") {//TIME validation
		var re = new RegExp("^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$|^([0-1][0-9]|[2][0-3]):([0-5][0-9])$");
	}
	if (!re.test(fldval)) {
		alert(app.vtranslate('ENTER_VALID') + fldLabel)
		return false
	}
	else return true
}

function patternValidate(fldName,fldLabel,type) {
	var currObj=getObj(fldName);

	if (type.toUpperCase()=="EMAIL") //Email ID validation
	{
		/*changes made to fix -- ticket#3278 & ticket#3461
		  var re=new RegExp(/^.+@.+\..+$/)*/
		//Changes made to fix tickets #4633, #5111  to accomodate all possible email formats
 	    var re=new RegExp(/^[a-zA-Z0-9]+([!"#$%&'()*+,./:;<=>?@\^_`{|}~-]?[a-zA-Z0-9])*@[a-zA-Z0-9]+([\_\-\.]?[a-zA-Z0-9]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/);
	}

	if (type.toUpperCase()=="DATE") {//DATE validation
		//YMD
		//var reg1 = /^\d{2}(\-|\/|\.)\d{1,2}\1\d{1,2}$/ //2 digit year
		//var re = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/ //4 digit year

		//MYD
		//var reg1 = /^\d{1,2}(\-|\/|\.)\d{2}\1\d{1,2}$/
		//var reg2 = /^\d{1,2}(\-|\/|\.)\d{4}\1\d{1,2}$/

		//DMY
		//var reg1 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2}$/
		//var reg2 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/

		switch (its4youUserDateFormat) {
			case "yyyy-mm-dd" :
				var re = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/
				break;
			case "mm-dd-yyyy" :
			case "dd-mm-yyyy" :
				var re = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/
		}
	}

	if (type.toUpperCase()=="TIME") {//TIME validation
		var re = /^\d{1,2}\:\d{2}:\d{2}$|^\d{1,2}\:\d{2}$/
	}
	//Asha: Remove spaces on either side of a Email id before validating
	if (type.toUpperCase()=="EMAIL" || type.toUpperCase() == "DATE") currObj.value = trim(currObj.value);
	if (!re.test(currObj.value)) {
		//alert(app.vtranslate('ENTER_VALID') + fldLabel  + " ("+type+")");
		try {
			currObj.focus()
		} catch(error) {
		// Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
		// So using the try { } catch(error) { }
		}
		return false
	}
	else return true
}

function ChartDataSeries(ch_style_obj){
    var currOption = ch_style_obj.options[ch_style_obj.selectedIndex];
    if(currOption.value=="group2"){
        //jQuery('#data_series1').val("");
        jQuery('#data_series2').val("");
        jQuery('#chartType2').val("");
        jQuery("#chartType2").attr("disabled","true");
        jQuery("#data_series2").attr("disabled","true");
        jQuery('#data_series3').val("");
        jQuery('#chartType3').val("");
        jQuery("#chartType3").attr("disabled","true");
        jQuery("#data_series3").attr("disabled","true");
    }else{
        jQuery("#chartType2").attr("disabled","false");
        jQuery("#chartType2").removeAttr('disabled');
        jQuery("#data_series2").attr("disabled","false");
        jQuery("#data_series2").removeAttr('disabled');
        jQuery("#chartType3").attr("disabled","false");
        jQuery("#chartType3").removeAttr('disabled');
        jQuery("#data_series3").attr("disabled","false");
        jQuery("#data_series3").removeAttr('disabled');
    }
}
// ITS4YOU-CR SlOl 22. 5. 2015 12:18:19
function saveChartImageToFile(export_pdf_format,pdf_file_name,ch_image_name,chart_image){
    var file_path = 'test/ITS4YouReports/'+ch_image_name+'.png';

    var postData = {
        "filename": ch_image_name,
		"report_filename": pdf_file_name,
        "filepath": file_path,
        "export_pdf_format": export_pdf_format,
        "canvasData": chart_image
    };
    
    var actionParams = {
        "type": "POST",
        "url": 'index.php?module=ITS4YouReports&action=SaveImage',
        "dataType": "html",
        "data": postData
    };

    app.request.post(actionParams).then(
        function(err, reponseData){
            if(err === null){
                // otvorenie dokumentu v novom okne = download
                window.open('index.php?module=ITS4YouReports&action=SaveImage&mode=download&export_pdf_format='+export_pdf_format+'&report_filename='+pdf_file_name+'&filepath='+file_path, '_blank');
            }
        }
    );

}
// recordId export_pdf_format pdf_file_name ch_image_name
//function saveChartAsBinary(id,export_pdf_format,pdf_file_name,ch_image_name){
function saveChartAsBinary(){
    var id = jQuery('#recordId').val();
    var export_pdf_format = jQuery('#export_pdf_format').val();
    var pdf_file_name = jQuery('#pdf_file_name').val();
    var ch_image_name = jQuery('#ch_image_name').val();

	var svg = document.getElementById("reports4you_widget_"+id).children[0].innerHTML;
	canvg(document.getElementById("canvas"),svg);
	var img = canvas.toDataURL("image/png"); //img is data:image/png;base64
	
	img = img.replace("data:image/png;base64,", "");
    
	//jQuery("#binaryImage").attr("src", "data:image/png;base64,"+img);
	saveChartImageToFile(export_pdf_format,pdf_file_name,ch_image_name,img);
}
// ITS4YOU-END 22. 5. 2015 12:18:21
// ITS4YOU-CR SlOl 26. 5. 2015 10:56:29
function exportToPDF(report_filename){
    
    var export_pdf_format = jQuery('#export_pdf_format').val();

    //document.getElementById('reportHederInfo').style.display = "none";    
    //var reports4you_html = jQuery('#reports4you_html').html();
    //document.getElementById('reportHederInfo').style.display = "block";
    
    var reports4you_name = jQuery('.reportHeader h3').html();
    var reports4you_html = jQuery('#rpt4youTable').html();
    var reports4you_totals = jQuery('#rpt4youTotals').html();
    
    var id = jQuery('input[name="record"]').val();
    var export_pdf_format = jQuery('#export_pdf_format').val();

    var chart_image = "";

    let topAsDiv = 'top_'
    if ('undefined' === typeof jQuery('#reports4you_widget_' + topAsDiv + id).html()) {
        topAsDiv = '';
    }

    if (document.getElementById("reports4you_widget_" + topAsDiv + id) != null) {
        var ch_image_name = jQuery('#ch_image_name').val();

        var svg = document.getElementById("reports4you_widget_" + topAsDiv + id).children[0].innerHTML;
    	canvg(document.getElementById("canvas"),svg);
    	
		if (typeof canvas.toDataURL === 'undefined') {
	    	var chart_image = canvas[0].toDataURL("image/png"); //img is data:image/png;base64
		} else {
	    	var chart_image = canvas.toDataURL("image/png"); //img is data:image/png;base64
		}    	
    	
        chart_image = chart_image.replace("data:image/png;base64,", "");
    }

    jQuery('#form_export_pdf_format').val(export_pdf_format);
    jQuery('#form_filename').val(report_filename);
    jQuery('#form_report_name').val(reports4you_name);
    jQuery('#form_report_html').val(reports4you_html);
    jQuery('#form_report_totals').val(reports4you_totals);
    jQuery('#form_chart_canvas').val(chart_image);
    
    jQuery('#GeneratePDF').submit();
    return false;
}
// ITS4YOU-END 
// ITS4YOU-CR SlOl 19. 10. 2015 10:04:46
function get_html_translation_table(table, quote_style) {
    var entities = {},
    hash_map = {},
    decimal;
    var constMappingTable = {},
    constMappingQuoteStyle = {};
    var useTable = {},
    useQuoteStyle = {};
    
    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';
    
    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
    'ENT_COMPAT';
    
    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error('Table: ' + useTable + ' not supported');
        // return false;
    }
    
    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }
    
    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';
    
    // ascii decimals to real symbols
    for (decimal in entities) {
        if (entities.hasOwnProperty(decimal)) {
            hash_map[String.fromCharCode(decimal)] = entities[decimal];
        }
    }
    
    return hash_map;
}
// ITS4YOU-CR SlOl 19. 10. 2015 10:06:49
function html_entity_decode(string, quote_style) {
    var hash_map = {},
    symbol = '',
    tmp_str = '',
    entity = '';
    if(typeof string !=='undefined') {
        tmp_str = string.toString();

        if (false === (hash_map = get_html_translation_table('HTML_ENTITIES', quote_style))) {
            return false;
        }

        // fix &amp; problem
        // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
        delete(hash_map['&']);
        hash_map['&'] = '&amp;';

        for (symbol in hash_map) {
            entity = hash_map[symbol];
            tmp_str = tmp_str.split(entity)
            .join(symbol);
        }
        tmp_str = tmp_str.split('&#039;')
        .join("'");
    }
    return tmp_str;
}
// ITS4YOU-END
// ITS4YOU-CR SlOl 23. 11. 2015 14:17:23
function showNdayRange(row_i,NdaysObj){
    
    if(typeof NdaysObj!='undefined'){
        var Ndays = jQuery(NdaysObj);
    }else{
        var Ndays = jQuery('#nfval'+row_i);
    }
    
    var opObj = document.getElementById('fop' + row_i);
    var currOp = opObj.options[opObj.selectedIndex];
    var seOption_type = currOp.value;
    var s_obj = document.getElementById("jscal_field_sdate" + row_i);
    var e_obj = document.getElementById("jscal_field_edate" + row_i);
    var st_obj = document.getElementById("jscal_trigger_sdate" + row_i);
    var et_obj = document.getElementById("jscal_trigger_edate" + row_i);
    showDateRange(s_obj, e_obj, st_obj, et_obj, seOption_type);
    
    var Ndays = Ndays.val();
    if(Ndays!=''){
        Ndays = parseInt(Ndays);
        
        var comparator = jQuery('#fop'+row_i).val();
        
        var current_mk_time = jQuery('#current_mk_time').val();
        current_mk_time = parseInt(current_mk_time)*1000;
        
        //var today = new Date(current_mk_time);
        
        var date2 = new Date(current_mk_time);
        
        var datevalue = new Array();
        
        if(comparator!='daysago' && comparator!='daysmore'){
            Ndays = (Ndays-1);
        }
        
        datevalue0 = datevalue1 = "";
        switch(comparator){
            case 'olderNdays':
                var olderNdays = date2.setDate(date2.getDate() - Ndays);
                datevalue0 = "";
                datevalue1 = getFormatedDateValue(olderNdays);
            break;
            case 'lastNdays':
                var lastNdays = date2.setDate(date2.getDate() - Ndays);
                datevalue0 = getFormatedDateValue(lastNdays);
                datevalue1 = getFormatedDateValue(current_mk_time);
            break;
            case 'nextNdays':
                var nextNdays = date2.setDate(date2.getDate() + Ndays);
                datevalue0 = getFormatedDateValue(current_mk_time);
                datevalue1 = getFormatedDateValue(nextNdays);
            break;
            case 'moreNdays':
                var moreNdays = date2.setDate(date2.getDate() + Ndays);
                datevalue0 = getFormatedDateValue(moreNdays);
                datevalue1 = "";
            break;
            case 'daysago':
                var daysago = date2.setDate(date2.getDate() - Ndays);
                datevalue0 = getFormatedDateValue(daysago);
                datevalue1 = getFormatedDateValue(daysago);
            break;
            case 'daysmore':
                var daysmore = date2.setDate(date2.getDate() + Ndays);
                datevalue0 = getFormatedDateValue(daysmore);
                datevalue1 = getFormatedDateValue(daysmore);
            break;
        }
        jQuery('#jscal_field_sdate'+row_i).val(datevalue0);
        jQuery('#jscal_field_edate'+row_i).val(datevalue1);
    }
}
// ITS4YOU-CR SlOl 24. 11. 2015 9:17:41
function getFormatedDateValue(dateObj){
    if(dateObj==""){
        return dateObj;
    }
    dateObj = parseInt(dateObj);    
    var dateObj = new Date(dateObj);    
    if(typeof dateObj !='undefined'){
        var user_date_format = jQuery('#user_date_format').val();
        if(typeof user_date_format == 'undefined' || user_date_format==''){
            user_date_format = 'dd-mm-yyyy';
        }
        var day = dateObj.getDate();
        var month = (dateObj.getMonth()+1);
        var year = dateObj.getFullYear();
        var formatedDate = "";
        switch(user_date_format){
            case 'dd-mm-yyyy':
                formatedDate = day+'-'+month+'-'+year;
                break;
            case 'mm-dd-yyyy':
                formatedDate = month+'-'+day+'-'+year;
                break;
            case 'yyyy-mm-dd':
                formatedDate = year+'-'+month+'-'+day;
                break;
            case 'dd.mm.yyyy':
                formatedDate = day+'.'+month+'.'+year;
                break;
        }
    }    
    return formatedDate;    
}
// ITS4YOU-CR SlOl 16. 5. 2016 11:55:35
function addCustomCalculationValue(cc_sbox,areaObjId){
    var sv_name = jQuery(cc_sbox);
    /*
    var sv_id = jQuery(cc_sbox).attr('id');
    var srv_name = jQuery('#'+sv_id+'_chzn');
    var selElement = srv_name.find('a.chzn-single span');
    var selectedOption = selElement.html();
    */

    var column_str_array = sv_name.val().split(":");
    if(column_str_array[1]!=undefined){
        var columnname = getColumnStrColumnName(sv_name);
        /*
        var columnname = column_str_array[1];
        if(column_str_array[6]!=undefined){
            columnname = columnname+'_'+column_str_array[6];
        }else if(column_str_array[5]!=undefined){
            columnname = columnname+'_'+column_str_array[5];
        }
        */
        
        var textAreaObj = jQuery('#'+areaObjId);
        var caretPos = document.getElementById(areaObjId).selectionStart;
        if(caretPos==0){
            var caretPos = textAreaObj.val().length;
        }
        var textAreaTxt = textAreaObj.val();
        //textAreaObj.val(textAreaTxt.substring(0, caretPos) + selectedOption + textAreaTxt.substring(caretPos) );
        textAreaObj.val(textAreaTxt.substring(0, caretPos) + columnname + textAreaTxt.substring(caretPos) );
    }
}
// ITS4YOU-CR SlOl 17. 5. 2016 8:24:46
function addNewCustomCalculation(){
    var cc_count = jQuery('.cc-new-select').length;
    var cc_row_base = jQuery('#cc_row_base');
    var cc_row = cc_row_base.html();
    cc_row = replaceAll('WCCINRW', cc_count, cc_row);
    
    jQuery('#cc_td_cell').append(cc_row);
    
    jQuery('#cc_options_'+cc_count).addClass("select2");

    jQuery('#cc_totals_'+cc_count).addClass("select2");

    app.changeSelectElementView(jQuery('#cc_row_'+cc_count));

}
function displayCustomCalculationArea(cc_i){
    var cc_textarea = jQuery('#cc_calculation_'+cc_i);
    var cc_textarea_html = cc_textarea.val();
    if(cc_textarea_html!=""){
        jQuery('#cc_options_'+cc_i).find('option').each(function(index,element){
            var optionElement = jQuery(element);
            
            var column_str_array = optionElement.val().split(":");
            if(column_str_array[1]!=undefined){
                var moduleNameArr = column_str_array[2].split("_");
                var moduleName = moduleNameArr[0];
                var moduleNameLbl = app.vtranslate(moduleName,moduleName);
                var columnLabel = '<b>'+moduleNameLbl+'.'+optionElement.text()+'</b>';
                var columnname = column_str_array[1];
                if(column_str_array[6]!=undefined){
                    columnname = columnname+'_'+column_str_array[6];
                }else if(column_str_array[5]!=undefined){
                    columnname = columnname+'_'+column_str_array[5];
                }
                cc_textarea_html = replaceAll(columnname, columnLabel, cc_textarea_html);
            }
        });
        jQuery('#cc_tooltip_content'+cc_i).html(cc_textarea_html);
        jQuery('#cc_tooltip_base'+cc_i).css('display', 'inline');
    }
}
function hideCustomCalculationArea(cc_i){
    jQuery('#cc_tooltip_base'+cc_i).css('display', 'none');
}
function deleteCustomCalculationRow(cc_i){
    jQuery('#cc_row_'+cc_i).remove();
}
// ITS4YOU-CR SlOl 25. 5. 2016 13:52:17
function findPosX(obj) {
	var curleft = 0;
	if (document.getElementById || document.all) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (document.layers) {
		curleft += obj.x;
	}
	return curleft;
}
function findPosY(obj) {
	var curtop = 0;
	if (document.getElementById || document.all) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}else if (document.layers) {
		curtop += obj.y;
	}
	return curtop;
}
function fnvshobj(obj,Lay){
	var tagName = document.getElementById(Lay);
	var leftSide = findPosX(obj);
	var topSide = findPosY(obj);
	var maxW = tagName.style.width;
	var widthM = maxW.substring(0,maxW.length-2);
    
	leftSide = leftSide - 575;
	topSide = topSide - 125;

	var getVal = eval(leftSide) + eval(widthM);
	if(getVal  > document.body.clientWidth ){
		leftSide = eval(leftSide) - eval(widthM);
		tagName.style.left = leftSide + 34 + 'px';
	}
	else
		tagName.style.left= leftSide + 'px';
	tagName.style.top= topSide + 'px';
	tagName.style.display = 'block';
	tagName.style.visibility = "visible";
}
// ITS4YOU-CR SlOl 30. 1. 2017 13:31:18
function getRecipientsOptionsSearch(search_input) {
    var search_for = search_input.value;
    var search_for_lc = search_for.toLowerCase();
    var selectedPrimaryIndex = document.getElementById('recipient_type').selectedIndex;
    var selectedPrimaryModule = document.getElementById('recipient_type').options[selectedPrimaryIndex].value;
    
    showRecipientsOptions();
    
    var new_options = new Array();
    var oi = 0;
    jQuery('select#availableRecipients').find('option').each(function() {
        var option = jQuery(this);
        var option_text = option.text();
        var option_text_lc = option_text.toLowerCase();
        if (option_text_lc.indexOf(search_for_lc) > -1) {
            var oOption = document.createElement("OPTION");
            oOption.value = option.val();
            oOption.appendChild(document.createTextNode(option_text));
            
            new_options[oi] = oOption;
            oi++;
        }
    });
    document.getElementById('availableRecipients').innerHTML = "";
    if(new_options.length>0){
        for (nLoop = 0; nLoop < new_options.length; nLoop++) {
            var new_option_obj = new_options[nLoop];
            document.getElementById('availableRecipients').appendChild(new_option_obj);
        }
    }
}
function clearRecipients(){
    jQuery('#search_recipient').val('');
}
// ITS4YOU-END
jQuery(document).ready(function() {
    jQuery('#summaries_orderby_column').val(jQuery('#summaries_orderby_columnString').val());
    app.changeSelectElementView(jQuery('#summaries_orderby_column'));

    setScheduleOptions();
    jQuery('#isReportScheduled').on('click', function (e) {
        var element = jQuery(e.currentTarget);
        var checkedElement = element.prop('checked');
        if (checkedElement) {
            jQuery('#isReportScheduledArea').removeClass('hide');
        } else {
            jQuery('#isReportScheduledArea').addClass('hide');
        }
    });
});