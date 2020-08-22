{*/*<!--
/*********************************************************************************
 * The content of this file is subject to the Reports 4 You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*/*}
{strip}
	{if !empty($TOOLTIP_TEXT)}
		<div class="tooltipR4You" style="float:right;"><i class="icon-info-sign"></i>&nbsp;&nbsp;{vtranslate('Tooltip',$MODULE)}
			<span class="tooltipR4Youtext tooltip-bottom" style="font-weight:normal;">{$TOOLTIP_TEXT}</span>
		</div>
	{/if}
{/strip}