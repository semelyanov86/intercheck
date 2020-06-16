{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}

<input type="hidden" name="std_filter_columns" id="std_filter_columns" value='{$std_filter_columns}' />
<input type="hidden" name="std_filter_criteria" id="std_filter_criteria" value='{$std_filter_criteria}' />
<input type="hidden" name="sel_fields" id="sel_fields" value='{$SEL_FIELDS}' />
<input type="hidden" name="current_mk_time" id="current_mk_time" value='{$current_mk_time}' />
<input type="hidden" name="user_date_format" id="user_date_format" value='{$user_date_format}' />
<input type="hidden" name="fld_date_options" id="fld_date_options" value='{$fld_date_options}' />
<div class="none" id='filter_columns' style='display:none;'>{$COLUMNS_BLOCK_JSON}</div>
<div class="none" id='quick_filter_columns' style='display:none;'>{$QF_COLUMNS_BLOCK_JSON}</div>
<script>
    function addColumnConditionGlue(groupIndex, columnIndex){ldelim}

            var columnConditionGlueElement = document.getElementById('columnconditionglue_' + columnIndex);

            if (groupIndex != "0")
                ctype = "f";
            else
                ctype = "g";

            if (columnConditionGlueElement) {ldelim}
                        columnConditionGlueElement.innerHTML = "<select name='" + ctype + "con" + columnIndex + "' id='" + ctype + "con" + columnIndex + "' class='chzn-select' data-value='value' name='columnname' data-fieldinfo='' style='max-width:87px;'>" +
                                "<option value='and'>{vtranslate('LBL_AND',$MODULE)}</option>" +
                                "<option value='or'>{vtranslate('LBL_OR',$MODULE)}</option>" +
                                "</select>";
jQuery().ready(function() {ldelim}
    var columnConditionGlueElement_obj = jQuery('#fcon' + columnIndex);
            if(document.getElementById("hfcon_"+groupIndex+"_"+ columnIndex)) {ldelim}
              var fColColumnsObj = jQuery('#fcon' + columnIndex);
              if(jQuery('#hfcon_'+groupIndex+'_'+ columnIndex)){ldelim}
                var selected_fcon = jQuery('#hfcon_'+groupIndex+'_'+ columnIndex).val();
                //jQuery("#"+ctype + "con" + columnIndex).val(selected_fcon);
                jQuery("#fcon" + columnIndex).val(selected_fcon);
                {rdelim}
//                if(fColColumnsObj.options[i].value == document.getElementById("hfcon_"+groupIndex+"_"+ columnIndex).value){ldelim}
//                  fColColumnsObj.options[i].selected=true;
//alert(fColColumnsObj.options[i].value);
//alert(fColColumnsObj.options[i].selected);
//                  jQuery("#"+ctype + "con" + columnIndex).val(fColColumnsObj.options[i].value);
//              {rdelim}
            {rdelim}
    app.changeSelectElementView(columnConditionGlueElement_obj);
{rdelim});

    {rdelim}
    {rdelim}

        function addConditionRow(groupIndex) {ldelim}
                var groupColumns = column_index_array[groupIndex];
                if (typeof (groupColumns) != 'undefined') {ldelim}
                            for (var i = groupColumns.length - 1; i >= 0; --i) {ldelim}
                                            var prevColumnIndex = groupColumns[i];
                                            if (document.getElementById('conditioncolumn_' + groupIndex + '_' + prevColumnIndex)) {ldelim}
                                                                addColumnConditionGlue(groupIndex, prevColumnIndex);
                                                                break;
    {rdelim}
    {rdelim}
    {rdelim}
            if (groupIndex != "0")
                var ctype = "f";
            else
                var ctype = "g";

            var columnIndex = advft_column_index_count + 1;
            var nextNode = document.getElementById('groupfooter_' + groupIndex);
            var newNode = document.createElement('tr');
            var newNodeId = 'conditioncolumn_' + groupIndex + '_' + columnIndex;
            newNode.setAttribute('id', newNodeId);
            newNode.setAttribute('name', 'conditionColumn');
            nextNode.parentNode.insertBefore(newNode, nextNode);

            var node1 = document.createElement('td');
            node1.setAttribute('width', '25%');
            newNode.appendChild(node1);

            if (groupIndex != "0")
            {ldelim}
                var filtercolumns = jQuery('#filter_columns').html();
               filtercolumns = html_entity_decode(filtercolumns,"UTF-8");
var fcon_selectbox = '<select name="' + ctype + 'col' + columnIndex + '" id="' + ctype + 'col' + columnIndex + '" style="display: block;" onchange="reports4you_updatefOptions(this, \'' + ctype + 'op' + columnIndex + '\');addRequiredElements(\'' + ctype + '\',' + columnIndex + ');updateRelFieldOptions(this, \'' + ctype + 'val_' + columnIndex + '\');" class="chzn-select span6" data-value="value" name="columnname" data-fieldinfo=""></select>';
node1.innerHTML = '<div class="conditionRow"><div id="condition'+ ctype + 'col' + columnIndex +'" >'+fcon_selectbox+'</div>';

                var oOption = document.createElement("OPTION");
                oOption.value = "";
                oOption.appendChild(document.createTextNode("{vtranslate('LBL_NONE',$MODULE)}"));
                document.getElementById(ctype + 'col' + columnIndex).appendChild(oOption);

                var optgroups = filtercolumns.split("(|@!@|)");
                for (i = 0; i < optgroups.length; i++)
                {ldelim}
                    var optgroup = optgroups[i].split("(|@|)");

                    if (optgroup[0] != '')
                    {ldelim}
                        var oOptgroup = document.createElement("OPTGROUP");
                        oOptgroup.label = optgroup[0];

                        var responseVal = JSON.parse(optgroup[1]);

                        for (var widgetId in responseVal)
                        {ldelim}
                            if (responseVal.hasOwnProperty(widgetId))
                            {ldelim}
                                option = responseVal[widgetId];
                                var oOption = document.createElement("OPTION");
                                oOption.value = option["value"];
                                oOption.appendChild(document.createTextNode(option["text"]));
                                oOptgroup.appendChild(oOption);
                            {rdelim}
                        {rdelim}
                        document.getElementById(ctype + 'col' + columnIndex).appendChild(oOptgroup);
                    {rdelim}
                {rdelim}
            {rdelim}
            else
            {ldelim}
                var filtercolumns = document.getElementById('SortByColumn').innerHTML;
                node1.innerHTML = '<div class="conditionRow"><select name="' + ctype + 'col' + columnIndex + '" id="' + ctype + 'col' + columnIndex + '" onchange="reports4you_updatefOptions(this, \'' + ctype + 'op' + columnIndex + '\');addRequiredElements(\'' + ctype + '\',' + columnIndex + ');updateRelFieldOptions(this, \'' + ctype + 'val_' + columnIndex + '\');" class="detailedViewTextBox">' + filtercolumns + '</select>';
                document.getElementById(ctype + 'col' + columnIndex).value = "none";
            {rdelim}
var fcon_selectbox_obj = jQuery("#condition"+ ctype + "col" + columnIndex);
jQuery().ready(function() {ldelim}
    app.changeSelectElementView(fcon_selectbox_obj);
{rdelim});
            
            node2 = document.createElement('td');
            node2.setAttribute('width', '15%');
            newNode.appendChild(node2);
            node2.innerHTML = '<select name="' + ctype + 'op' + columnIndex + '" id="' + ctype + 'op' + columnIndex + '" class="repBox" style="width:100%;" onchange="addRequiredElements(\'' + ctype + '\',' + columnIndex + ');">' +
                    '<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>' +
                    '{$FOPTION}' +
                    '</select>';

            node3 = document.createElement('td');
    {*node3.setAttribute('class', 'dvtCellLabel');*}
            newNode.appendChild(node3);

            var node3_inner = "";

    {*node3_inner += '&nbsp;<input type="image" align="absmiddle" style="cursor: pointer;" onclick="document.getElementById(\'' + ctype + 'val' + columnIndex + '\').value=\'\';return false;" language="javascript" title="{vtranslate('LBL_CLEAR',$MODULE)}" alt="{vtranslate('LBL_CLEAR',$MODULE)}" src="layouts/vlayout/skins/images/clear.png"/>' + *}
            node3_inner +='<div class="layerPopup" id="show_val' + columnIndex + '" name="relFieldsPopupDiv" style="border:2px solid #656565;border:0; position: absolute; width:300px; z-index: 50; display: none;">' +
                    '<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="padding:5px;border-radius:5px;border:1px solid #ccc;background-color:white;">' +
                    '<tr>' +
                    '<td>' +
                    '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">' +
                    '<tr>' +
                    '<td>' +
                    '<table width="100%" cellspacing="0" cellpadding="5" border="0" bgcolor="white" class="small">' +
                    '<tr>' +
                    '<td width="30%" align="left" class="cellLabel small"><b>{vtranslate('LBL_SELECT_FIELDS',$MODULE)}</b></td>' +
                    '<td width="30%" align="left" class="cellText">' +
                    '<select name="' + ctype + 'val_' + columnIndex + '" id="' + ctype + 'val_' + columnIndex + '" onChange="AddFieldToFilter(' + columnIndex + ',this);" class="chzn-select" style="margin-top:0.5em;">' +
                    '<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>' +
                    '{$REL_FIELDS}' +
                    '</select>' +
                    '</td>' +
                    '<td width="30%" align="left" class="cellLabel small">' +
                    '<input type="button" style="width: 70px;margin-top:0.5em;" value="{vtranslate('LBL_CLOSE',$MODULE)}" name="button" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');" class="crmbutton small create" accesskey="X" title="{vtranslate('LBL_CLOSE',$MODULE)}"/>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>';
            node3.innerHTML = '<span id="node3span' + columnIndex + '_st" style="width:100%;"><input name="' + ctype + 'val' + columnIndex + '" id="' + ctype + 'val' + columnIndex + '" class="repBox" style="width: 85%;" type="text" value=""><input name="' + ctype + 'valhdn' + columnIndex + '" id="' + ctype + 'valhdn' + columnIndex + '" class="repBox" style="width: 85%;" type="hidden" value=""><span class="add-on relatedPopup cursorPointer"><i id="node3span'+columnIndex+'_select" class="icon-search relatedPopup" onClick="" title="{vtranslate('LBL_SELECT')}"></i></span><span class="add-on clearReferenceSelection cursorPointer"><i id="node3span'+columnIndex+'_clear" class="icon-remove-sign" onClick="ClearFieldToFilter('+columnIndex+');" title="{vtranslate('LBL_CLEAR')}"></i></span>' + node3_inner + '</span><span id="node3span' + columnIndex + '_ajx" style="width:100%;display:none;"></span><span id="node3span' + columnIndex + '_djx" style="width:100%;display:none;"></span>';
            setRelPopupClick('node3span'+columnIndex+'_select',columnIndex);

            node4 = document.createElement('td');
            node4.setAttribute('id', 'columnconditionglue_' + columnIndex);
            node4.setAttribute('width', '87px');
            newNode.appendChild(node4);

            node5 = document.createElement('td');
            node5.setAttribute('width', '30px');
            newNode.appendChild(node5);
            node5.innerHTML = '<a onclick="deleteColumnRow(' + groupIndex + ',' + columnIndex + ');" href="javascript:;">' +
                    '<img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0">' +
                    '</a></div>';

            if (typeof (column_index_array[groupIndex]) == 'undefined')
                column_index_array[groupIndex] = [];
            column_index_array[groupIndex].push(columnIndex);
            advft_column_index_count++;
    {rdelim}

    function addGroupConditionGlue(groupIndex) {ldelim}

                var groupConditionGlueElement = document.getElementById('groupconditionglue_' + groupIndex);
                if (groupConditionGlueElement) {ldelim}
var gcon_selectbox = '<select name="gpcon' + groupIndex + '" id="gpcon' + groupIndex + '" class="chzn-select" data-value="value" style="display: block;width:8em;" name="columnname" data-fieldinfo="">'+
                                    "<option value='and'>{vtranslate('LBL_AND',$MODULE)}</option>" +
                                    "<option value='or'>{vtranslate('LBL_OR',$MODULE)}</option>" +'</select>';
groupConditionGlueElement.innerHTML = '<div class="gconRow"><div id="gconRow' + groupIndex +'" >'+gcon_selectbox+'</div>';

var gcon_selectbox_obj = jQuery("#gconRow" + groupIndex);
jQuery().ready(function() {ldelim}
    app.changeSelectElementView(gcon_selectbox_obj);
{rdelim});

                {rdelim}
    {rdelim}

    function addConditionGroup(parentNodeId) {ldelim}
            for (var i = group_index_array.length - 1; i >= 0; --i) {ldelim}
                var prevGroupIndex = group_index_array[i];
                if (document.getElementById('conditiongroup_' + prevGroupIndex)) {ldelim}
                    addGroupConditionGlue(prevGroupIndex);
                    break;
                {rdelim}
            {rdelim}

            var groupIndex = advft_group_index_count + 1;
            var parentNode = document.getElementById(parentNodeId);

            var newNode = document.createElement('div');
            newNodeId = 'conditiongroup_' + groupIndex;
            newNode.setAttribute('id', newNodeId);
            newNode.setAttribute('name', 'conditionGroup');
            
            newNode.setAttribute('class', 'conditionGroup conditionFilterDiv');
{*
            if(groupIndex>1){ldelim}
                newNode.setAttribute('class', 'conditionGroup conditionFilterDiv');
            {rdelim}
            else
            {ldelim}
                newNode.setAttribute('class', 'conditionGroup');
            {rdelim}
*}
            
            if(groupIndex>1){
                var deleted_group_btn = "<button type='button' class='btn' style='float:right;' onclick='deleteGroup(\"" + groupIndex + "\");'><strong>{vtranslate('LBL_DELETE_GROUP',$MODULE)}</strong></button>";
            }else{
                var deleted_group_btn = "";
            }
            
            newNode.innerHTML = "<div class='conditionList'><table class='small crmTable' border='0' cellpadding='5' cellspacing='0' width='100%' valign='top' id='conditiongrouptable_" + groupIndex + "' style='border:0px;' >" +
                    "<tr id='groupfooter_" + groupIndex + "' style='display:none;'>" +
                    "<td colspan='5' align='left'>" +
                    "</td>" +
                    "</tr>" +
                    "</table>" +
                    "<table class='small' border='0' cellpadding='5' cellspacing='1' width='100%' valign='top'>" +
                    "<tr id='groupheader_" + groupIndex + "'>" +
                    "<td colspan='4' align='left'>" +
                    "<button type='button' class='btn' style='float:left;' onclick='addConditionRow(\"" + groupIndex + "\")'><strong>{vtranslate('LBL_NEW_CONDITION',$MODULE)}</strong></button>" +
{*/*
                    "<div style='float:left;'>&nbsp;&nbsp;</div>" +
                    "<button type='button' class='btn' style='float:left;' onclick='addNewConditionGroup(\"adv_filter_div\")'><strong>{vtranslate('LBL_NEW_GROUP',$MODULE)}</strong></button>" +
*/*}
                    "</td>" +
                    "<td colspan='1' align='right'>" +
                    deleted_group_btn+
                    "</td>" +
                    "</tr>" +
                    "<tr><td align='center' id='groupconditionglue_" + groupIndex + "' style='text-align:center;' colspan='5'>" +
                    "</td></tr>" +
                    "</table></div>";

            parentNode.appendChild(newNode);

            group_index_array.push(groupIndex);
            advft_group_index_count++;
            if(advft_group_index_count>0){ldelim}
                jQuery('.fgroup_btn').addClass('hide');
            {rdelim}
    {rdelim}

    function addNewConditionGroup(parentNodeId) {ldelim}
                addConditionGroup(parentNodeId);
                addConditionRow(advft_group_index_count);
    {rdelim}

    function deleteColumnRow(groupIndex, columnIndex) {ldelim}
                removeElement('conditioncolumn_' + groupIndex + '_' + columnIndex);

                var groupColumns = column_index_array[groupIndex];
                var keyOfTheColumn = groupColumns.indexOf(columnIndex);
                var isLastElement = true;

                for (var i = keyOfTheColumn; i < groupColumns.length; ++i) {ldelim}
                            var nextColumnIndex = groupColumns[i];
                            var nextColumnRowId = 'conditioncolumn_' + groupIndex + '_' + nextColumnIndex;
                            if (document.getElementById(nextColumnRowId)) {ldelim}
                                            isLastElement = false;
                                            break;
    {rdelim}
    {rdelim}

            if (isLastElement) {ldelim}
                        for (var i = keyOfTheColumn - 1; i >= 0; --i) {ldelim}
                                        var prevColumnIndex = groupColumns[i];
                                        var prevColumnGlueId = "fcon" + prevColumnIndex;
                                        if (document.getElementById(prevColumnGlueId)) {ldelim}
                                                            removeElement(prevColumnGlueId);
                                                            break;
    {rdelim}
    {rdelim}
    {rdelim}
    {rdelim}

    function deleteGroup(groupIndex) {ldelim}
        removeElement('conditiongroup_' + groupIndex);

        var keyOfTheGroup = group_index_array.indexOf(groupIndex);
        var isLastElement = false;
        
        var g_length = group_index_array.length
        var g_length_i = g_length;
        var hide_gop = false;

        for (var i = 0; i < g_length; i++) {ldelim}
            if(!document.getElementById('conditiongroup_'+g_length_i)){ldelim}
                hide_gop = true;
            {rdelim}
            else
            {ldelim}
                hide_gop = false;
            {rdelim}
            g_length_i = g_length_i-1;
            if(hide_gop==true){ldelim}
                if(g_length_i==(g_length-1)){ldelim}
                    for (var di = 0; di < g_length; di++) {ldelim}
                        if(!document.getElementById('conditiongroup_'+g_length_i)){ldelim}
                            g_length_i = g_length_i-1;
                        {rdelim}
                        else
                        {ldelim}
                            jQuery('#gconRow'+g_length_i).html('');
                            break;
                        {rdelim}
                    {rdelim}
                {rdelim}
            {rdelim}
        {rdelim}
    {rdelim}

    function removeElement(elementId) {ldelim}
            var element = document.getElementById(elementId);
            if (element) {ldelim}
                        var parent = element.parentNode;
                        if (parent) {ldelim}
                                        parent.removeChild(element);
                        {rdelim} else {ldelim}
                                        element.remove();
                        {rdelim}
            {rdelim}
    {rdelim}

    function hideAllElementsByName(name) {ldelim}
            var allElements = document.getElementsByTagName('div');
            for (var i = 0; i < allElements.length; ++i) {ldelim}
                        var element = allElements[i];
                        if (element.getAttribute('name') == name)
                            element.style.display = 'none';
            {rdelim}
            return true;
    {rdelim}

    function addRequiredElements(ctype, columnindex) {ldelim}
        jQuery().ready(function() {ldelim}
                var colObj = document.getElementById(ctype + 'col' + columnindex);
                if (colObj){ldelim}
                            var opObj = document.getElementById(ctype + 'op' + columnindex);
                            var valObj = document.getElementById(ctype + 'val' + columnindex);

                            var currField = colObj.options[colObj.selectedIndex];
                            var currOp = opObj.options[opObj.selectedIndex];

                            var fieldtype = null;

                            if (currField.value != null && currField.value.length != 0) {ldelim}
                                    fieldtype = trimfValues(currField.value);

                                    var sel_fields = JSON.parse(document.getElementById("sel_fields").value);
                                    var selected_value = html_entity_decode(colObj.value,"UTF-8");
                                    if (sel_fields[selected_value]) {
                                        updateRelFieldOptions(colObj, 'fval_'+columnindex);
                                    }
                                    
                                    switch (fieldtype) {ldelim}
                                                        case 'D':
                                                        case 'T':
                                                                var dateformat = "{$JS_DATEFORMAT}";
                                                                var timeformat = "%H:%M:%S";
                                                                var showtime = true;
                                                                if (fieldtype == 'D') {ldelim}
                                                                                            timeformat = '';
                                                                                            showtime = false;
                                                                {rdelim}

                                                            break;
                                                default:
                                                        if (document.getElementById('jscal_trigger_fval' + columnindex))
                                                            document.getElementById('jscal_trigger_fval' + columnindex).remove();
                                                        if (document.getElementById('fval_ext' + columnindex))
                                                            document.getElementById('fval_ext' + columnindex).remove();
                                                        if (document.getElementById('jscal_trigger_fval_ext' + columnindex))
                                                            document.getElementById('jscal_trigger_fval_ext' + columnindex).remove();
                                                 {rdelim}
                    var comparatorId = ctype + "op" + columnindex;
                    var comparatorObject = jQuery("#"+comparatorId);
                    var comparatorValue = comparatorObject.val();
                    
                    if(comparatorValue=="isn" || comparatorValue=="isnn"){ldelim}
		                	document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
		                  document.getElementById("node3span" + columnindex + "_st").style.display = "none";
											document.getElementById("node3span" + columnindex + "_djx").style.display = "none";
										{rdelim}else{ldelim}
											document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
		                  document.getElementById("node3span" + columnindex + "_st").style.display = "block";
											document.getElementById("node3span" + columnindex + "_djx").style.display = "none";
										{rdelim}

                    var std_filter_columns = document.getElementById("std_filter_columns").value;
                    if (std_filter_columns){ldelim}
                                        var std_filter_columns_arr = std_filter_columns.split('<%jsstdjs%>');
                                        var selected_value = html_entity_decode(colObj.value,"UTF-8");
                                        if (std_filter_columns_arr.indexOf(selected_value) > -1) {ldelim}
                                                                if ((document.getElementById("jscal_field_sdate" + columnindex)) && (document.getElementById("jscal_field_edate" + columnindex))){ldelim}
                                                                            var s_obj = document.getElementById("jscal_field_sdate" + columnindex);
                                                                            var e_obj = document.getElementById("jscal_field_edate" + columnindex);
                                                                            var st_obj = document.getElementById("jscal_trigger_sdate" + columnindex);
                                                                            var et_obj = document.getElementById("jscal_trigger_edate" + columnindex);
                                                                            var seOption_type = currOp.value;
                                                                            
                                                                            if(comparatorValue=="olderNdays" || comparatorValue=="lastNdays" || comparatorValue=="nextNdays" || comparatorValue=="moreNdays" || comparatorValue=="daysago" || comparatorValue=="daysmore"){ldelim}
                                                                                jQuery('#fval'+columnindex).val('');
                                                                                jQuery('#fvalhdn'+columnindex).val('');
                                                                                document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
                                                                                document.getElementById("node3span" + columnindex + "_st").style.display = "none";
                                                                                document.getElementById("div_nfval" + columnindex).style.display = "block";
                                                                                document.getElementById("node3span" + columnindex + "_djx").style.display = "block";
                                                                                showNdayRange(columnindex);
                                                                            {rdelim}else{ldelim}
                                                                                document.getElementById("div_nfval" + columnindex).style.display = "none";
                                                                                showDateRange(s_obj, e_obj, st_obj, et_obj, seOption_type);
                                                                                if(comparatorValue=="custom"){ldelim}
                                                                                    app.registerEventForDateFields('#jscal_field_sdate_val_' + columnindex);
                                                                                    app.registerEventForDateFields('#jscal_field_edate_val_' + columnindex);
                                                                                {rdelim}
                                                                                if(comparatorValue!=""){ldelim}
        																																						if(comparatorValue=="isn" || comparatorValue=="isnn"){ldelim}
                                                                                      jQuery('#fval'+columnindex).val('');
                                                                                      jQuery('#fvalhdn'+columnindex).val('');
        																														                	document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
        																														                  document.getElementById("node3span" + columnindex + "_st").style.display = "none";
        																																							document.getElementById("node3span" + columnindex + "_djx").style.display = "none";
                                                                                    {rdelim}else if(jQuery.inArray(comparatorValue,JSON.parse(jQuery('#fld_date_options').val()))>-1){ldelim}
                                                                                      document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
        																														                  document.getElementById("node3span" + columnindex + "_st").style.display = "block";
        																																							document.getElementById("node3span" + columnindex + "_djx").style.display = "none";
        																																						{rdelim}else{ldelim}
                                                                                      jQuery('#fval'+columnindex).val('');
                                                                                      jQuery('#fvalhdn'+columnindex).val('');
        																																							document.getElementById("node3span" + columnindex + "_ajx").style.display = "none";
        																														                  document.getElementById("node3span" + columnindex + "_st").style.display = "none";
        																																							document.getElementById("node3span" + columnindex + "_djx").style.display = "block";
        																																						{rdelim}
                                                                                {rdelim}
                                                                            {rdelim}
                                                                           
                                                                {rdelim}
                                        {rdelim}
                    {rdelim}

                {rdelim}
            {rdelim}
        {rdelim});
    {rdelim}

        function showHideDivs(showdiv, hidediv) {ldelim}
                if (document.getElementById(showdiv))
                    document.getElementById(showdiv).style.display = "block";
                if (document.getElementById(hidediv))
                    document.getElementById(hidediv).style.display = "none";
    {rdelim}
        
    /*{* FROM EDITITS4YouReports TPL *}*/
    
    function deleteGroupColumnRow(groupIndex, columnIndex) {ldelim}
            removeElement('groupconditioncolumn_' + groupIndex + '_' + columnIndex);

            var groupColumns = gf_column_index_array[groupIndex];
            var keyOfTheColumn = groupColumns.indexOf(columnIndex);
            var isLastElement = true;

            for (var i = keyOfTheColumn; i < groupColumns.length; ++i) {ldelim}
                        var nextColumnIndex = groupColumns[i];
                        var nextColumnRowId = 'groupconditioncolumn_' + groupIndex + '_' + nextColumnIndex;
                        if (document.getElementById(nextColumnRowId)) {ldelim}
                                        isLastElement = false;
                                        break;
    {rdelim}
    {rdelim}

            if (isLastElement) {ldelim}
                        for (var i = keyOfTheColumn - 1; i >= 0; --i) {ldelim}
                                        var prevColumnIndex = groupColumns[i];
                                        var prevColumnGlueId = "ggroupcon" + prevColumnIndex;
                                        if (document.getElementById(prevColumnGlueId)) {ldelim}
                                                            removeElement(prevColumnGlueId);
                                                            break;
                                        {rdelim}
                         {rdelim}
            {rdelim}
    {rdelim}

    function addGroupConditionRow(groupIndex) {ldelim}
	var groupColumns = gf_column_index_array[groupIndex];
	if(typeof(groupColumns) != 'undefined') {ldelim} 		
		for(var i=groupColumns.length - 1; i>=0; --i) {ldelim}
			var prevColumnIndex = groupColumns[i];
                        
			if(document.getElementById('groupconditioncolumn_'+groupIndex+'_'+prevColumnIndex)) {ldelim}
				addGroupColumnConditionGlue(groupIndex, prevColumnIndex);
				break;
                        {rdelim}
                {rdelim}
        {rdelim}
	if (groupIndex != "0")
	   ctype = "f";
	else
	   ctype = "g";
	var columnIndex = gf_advft_column_index_count+1;
	var nextNode = document.getElementById('ggroupfooter_'+groupIndex);
	var newNode = document.createElement('tr');
	newNodeId = 'groupconditioncolumn_'+groupIndex+'_'+columnIndex;
	newNode.setAttribute('id',newNodeId);
	newNode.setAttribute('name','groupconditionColumn');
	nextNode.parentNode.insertBefore(newNode, nextNode);
	
	node1 = document.createElement('td');
    {*node1.setAttribute('class', 'dvtCellLabel');*}
	node1.setAttribute('width', '25%');
	newNode.appendChild(node1);
    {* node1.innerHTML = '<select name="'+ctype+'col'+columnIndex+'" id="'+ctype+'col'+columnIndex+'" onchange="reports4you_updatefOptions(this, \''+ctype+'op'+columnIndex+'\');addRequiredElements(\''+ctype+'\','+columnIndex+');updateRelFieldOptions(this, \''+ctype+'val_'+columnIndex+'\');" class="detailedViewTextBox">'+
    '<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>'+
    '{$COLUMNS_BLOCK}'+
    '</select>'; *}

    {*filtercolumns = document.getElementById('SortByColumn').innerHTML;*}
    {* node1.innerHTML = '<select name="'+ctype+'groupcol'+columnIndex+'" id="'+ctype+'groupcol'+columnIndex+'" onchange="reports4you_updatefOptions(this, \''+ctype+'op'+columnIndex+'\');addRequiredElements(\''+ctype+'\','+columnIndex+');updateRelFieldOptions(this, \''+ctype+'val_'+columnIndex+'\');" class="detailedViewTextBox">'+filtercolumns+'</select>'; *}
    node1.innerHTML = '<select name="'+ctype+'groupcol'+columnIndex+'" id="'+ctype+'groupcol'+columnIndex+'" onchange="reports4you_updatefOptions(this, \''+ctype+'groupcolop'+columnIndex+'\');addRequiredElements(\''+ctype+'\','+columnIndex+');" class="chzn-select chzn-done" style="width:auto"><option value="none">{vtranslate('LBL_NONE',$MODULE)}</option></select>';
      document.getElementById(ctype+'groupcol'+columnIndex).value = "none";
      var filtercolumns_str = document.getElementById('sum_group_columns').innerHTML;
      var optgroups = filtercolumns_str.split("(|@!@|)");
      for(i=0; i < optgroups.length; i++)
    {ldelim}
         var optgroup = optgroups[i].split("(|@|)");
         if (optgroup[0] != '' && optgroup[1] != '')
    {ldelim}
             var option_value = optgroup[0];
             var option_text = optgroup[1];
             var oOption = document.createElement("OPTION");
	    		   oOption.value=option_value;
	    		   oOption.appendChild(document.createTextNode(option_text));
						 document.getElementById(ctype+'groupcol'+columnIndex).appendChild(oOption);
    {rdelim}
    {rdelim}
			
	
	node2 = document.createElement('td');
    {*node2.setAttribute('class', 'dvtCellLabel');*}
	node2.setAttribute('width', '25%');
	newNode.appendChild(node2);
	node2.innerHTML = '<select name="'+ctype+'groupop'+columnIndex+'" id="'+ctype+'groupop'+columnIndex+'" class="repBox" style="width:100%;" onchange="addRequiredElements(\''+ctype+'\','+columnIndex+');">'+
							'<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>'+
							'{$FOPTION}'+
						'</select>';
	
	node3 = document.createElement('td');
    {*node3.setAttribute('class', 'dvtCellLabel');*}
	newNode.appendChild(node3);
	node3.innerHTML = '<input name="'+ctype+'groupval'+columnIndex+'" id="'+ctype+'groupval'+columnIndex+'" class="repBox" style="width: 100%;" type="text" value="">';
	
    {*  if (groupIndex != "0")
    node3.innerHTML += '<img height=20 width=20 align="absmiddle" style="cursor: pointer;" title="{$APP.LBL_FIELD_FOR_COMPARISION}" alt="{$APP.LBL_FIELD_FOR_COMPARISION}" src="themes/images/terms.gif" onClick="hideAllElementsByName(\'relFieldsPopupDiv\'); fnvshobj(this,\'show_val'+columnIndex+'\');"/>';
    *}	
{*  node3.innerHTML += '&nbsp;<input type="image" align="absmiddle" style="cursor: pointer;" onclick="document.getElementById(\''+ctype+'groupval'+columnIndex+'\').value=\'\';return false;" language="javascript" title="{vtranslate('LBL_CLEAR',$MODULE)}" alt="{vtranslate('LBL_CLEAR',$MODULE)}" src="layouts/vlayout/skins/images/clear.png"/>'+ *}
    node3.innerHTML += '<div class="layerPopup" id="show_val'+columnIndex+'" name="relFieldsPopupDiv" style="border:0; position: absolute; width:300px; z-index: 50; display: none;">'+
							'<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="mailClient mailClientBg">'+
								'<tr>'+
									'<td>'+
										'<table width="100%" cellspacing="0" cellpadding="0" border="0" class="layerHeadingULine">'+
											'<tr class="mailSubHeader">'+
												'<td width=90% class="genHeaderSmall"><b>{vtranslate('LBL_SELECT_FIELDS',$MODULE)}</b></td>'+
												'<td align=right>'+
													'<img border="0" align="absmiddle" src="layouts/vlayout/skins/images/close.gif" style="cursor: pointer;" alt="{vtranslate('LBL_CLOSE',$MODULE)}" title="{vtranslate('LBL_CLOSE',$MODULE)}" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');"/>'+
												'</td>'+
											'</tr>'+
										'</table>'+
							
										'<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">'+
											'<tr>'+
												'<td>'+
													'<table width="100%" cellspacing="0" cellpadding="5" border="0" bgcolor="white" class="small">'+
														'<tr>'+
															'<td width="30%" align="left" class="cellLabel small">{vtranslate('LBL_RELATED_FIELDS',$MODULE)}</td>'+
															'<td width="30%" align="left" class="cellText">'+
																'<select name="'+ctype+'val_'+columnIndex+'" id="'+ctype+'val_'+columnIndex+'" onChange="AddFieldToFilter('+columnIndex+',this);" class="detailedViewTextBox">'+
																	'<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>'+
													        		'{$REL_FIELDS}'+
												        		'</select>'+
															'</td>'+
														'</tr>'+
													'</table>'+	
													'<!-- save cancel buttons -->'+
													'<table width="100%" cellspacing="0" cellpadding="5" border="0" class="layerPopupTransport">'+
														'<tr>'+
															'<td width="50%" align="center">'+
																'<input type="button" style="width: 70px;" value="{vtranslate('LBL_DONE',$MODULE)}" name="button" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');" class="crmbutton small create" accesskey="X" title="{vtranslate('LBL_DONE',$MODULE)}"/>'+
															'</td>'+
														'</tr>'+
													'</table>'+	
												'</td>'+
											'</tr>'+
										'</table>'+
									'</td>'+
								'</tr>'+
							'</table>'+
						'</div>';
	
	node4 = document.createElement('td');
    {*node4.setAttribute('class', 'dvtCellLabel');*}
	node4.setAttribute('id', 'groupcolumnconditionglue_'+columnIndex);
	node4.setAttribute('width', '87px');
	newNode.appendChild(node4);
	
	node5 = document.createElement('td');
    {*node5.setAttribute('class', 'dvtCellLabel');*}
	node5.setAttribute('width', '30px');
	newNode.appendChild(node5);
	node5.innerHTML = '<a onclick="deleteGroupColumnRow('+groupIndex+','+columnIndex+');" href="javascript:;">'+
							'<img src="modules/ITS4YouReports/img/Delete.png" align="absmiddle" title="{vtranslate('LBL_DELETE',$MODULE)}..." border="0" >'+
						'</a>';

	if(typeof(gf_column_index_array[groupIndex]) == 'undefined') gf_column_index_array[groupIndex] = [];
	gf_column_index_array[groupIndex].push(columnIndex);
	gf_advft_column_index_count++;  
    {rdelim}
    function addGroupColumnConditionGlue(groupIndex, columnIndex) {ldelim}

	var columnConditionGlueElement = document.getElementById('groupcolumnconditionglue_'+columnIndex);
	
	if (groupIndex != "0")
	   ctype = "f";
	else
	   ctype = "g";
	
	if(columnConditionGlueElement) {ldelim}		
		columnConditionGlueElement.innerHTML = "<select name='"+ctype+"groupcon"+columnIndex+"' id='"+ctype+"groupcon"+columnIndex+"' class='chzn-select chzn-done' style='display:none;width:auto'>"+
													"<option value='and'>{vtranslate('LBL_AND',$MODULE)}</option>"+
													"<option value='or'>{vtranslate('LBL_OR',$MODULE)}</option>"+
												"</select>";
    {rdelim}
    {rdelim}

    function addQuickFilterBox() {ldelim}
        var qfColumnsJson = jQuery('#quick_filter_columns').html();
        qfColumnsJson = html_entity_decode(qfColumnsJson,"UTF-8");
        var qfColumns = JSON.parse(qfColumnsJson);

        var filtercolumns = jQuery('#filter_columns').html();
        filtercolumns = html_entity_decode(filtercolumns, "UTF-8");

        var oOption = document.createElement("OPTION");
        oOption.value = "";
        oOption.appendChild(document.createTextNode("{vtranslate('LBL_NONE',$MODULE)}"));
        document.getElementById('quick_filters').appendChild(oOption);

        var aSi = 0;
        var alreadySelected = new Array();
        var optgroups = filtercolumns.split("(|@!@|)");
        for (i = 0; i < optgroups.length; i++) {ldelim}
            var optgroup = optgroups[i].split("(|@|)");

            if (optgroup[0] !== '') {ldelim}
                var oOptgroup = document.createElement("OPTGROUP");
                oOptgroup.label = optgroup[0];

                var responseVal = JSON.parse(optgroup[1]);

                for (var widgetId in responseVal) {ldelim}
                    if (responseVal.hasOwnProperty(widgetId)) {ldelim}
                        option = responseVal[widgetId];
                        var vOption = document.createElement("OPTION");
                        vOption.value = option["value"];
                        if (jQuery.inArray(vOption.value, qfColumns) !== -1 && jQuery.inArray(vOption.value, alreadySelected) === -1) {
                            vOption.selected = true;
                            alreadySelected[aSi] = vOption.value;
                            aSi++;
                        }
                        vOption.appendChild(document.createTextNode(option["text"]));
                        oOptgroup.appendChild(vOption);
                        {rdelim}
                    {rdelim}
                document.getElementById('quick_filters').appendChild(oOptgroup);
                {rdelim}
            {rdelim}

    {rdelim}
</script>
