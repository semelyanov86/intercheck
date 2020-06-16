{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
<style type="text/css">
.conditionFilterDiv{
    background: #EEEFF2;
    padding-left:5px;
    padding-top:5px;
    padding-right:5px;
    margin-bottom: 10px;
    border: 0.1px solid rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}
.tooltipR4You {
	position: relative;
	display: inline-block;
	border-bottom: 1px dotted #ccc;
	color: #006080;
}

.tooltipR4You .tooltipR4Youtext {
	visibility: hidden;
	position: absolute;
	width: 20em;
	background-color: #555;
	color: #fff;
	text-align: center;
	padding: 10px;
	border-radius: 6px;
	z-index: 1;
	opacity: 0;
	transition: opacity 0.3s;
}

.tooltipR4You:hover .tooltipR4Youtext {
  visibility: visible;
  opacity: 1;
}

.tooltip-bottom {
    top: 135%;
    left: -220%;
    margin-left: -60px;
}

.tooltip-bottom::after {
    content: "";
    position: absolute;
    bottom: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent #555 transparent;
}
</style>