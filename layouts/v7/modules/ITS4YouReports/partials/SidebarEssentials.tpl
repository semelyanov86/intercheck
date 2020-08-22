{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{if 1 eq $NO_LICENSE}
		<script type="text/javascript">jQuery('#sidebar-essentials').remove()</script>
	{else}
	<div class="sidebar-menu sidebar-menu-full">
		<div class="module-filters" id="module-filters">
			<div class="sidebar-container lists-menu-container">
				<div class="sidebar-header clearfix" style="{if 'List' eq $smarty.request.view}border-bottom:2px solid;{/if}">
					<h5 class="pull-left">{vtranslate('LBL_LIST_VIEW', $MODULE)}</h5>
					<button id="keyMetricsList" onclick='window.location.href="index.php?module=ITS4YouReports&view=List"' class="btn btn-default pull-right sidebar-btn" title="{vtranslate('LBL_KEY_METRICS', $MODULE)}">
						<div class="fa fa-chevron-right"></div>
					</button>
				</div>
				<div class="sidebar-header clearfix" style="{if 'KeyMetricsList' eq $smarty.request.view}border-bottom:2px solid;{/if}">
					<h5 class="pull-left">{vtranslate('LBL_KEY_METRICS', $MODULE)}</h5>
					<button id="keyMetricsList" onclick='window.location.href="index.php?module=ITS4YouReports&view=KeyMetricsList"' class="btn btn-default pull-right sidebar-btn" title="{vtranslate('LBL_KEY_METRICS', $MODULE)}">
						<div class="fa fa-chevron-right"></div>
					</button>
				</div>
				{if 'List' === $smarty.request.view}
					<div class="sidebar-header clearfix">
						<h5 class="pull-left">{vtranslate('LBL_FOLDERS', $MODULE)}</h5>
						<button id="createFilter" onclick='ITS4YouReports_List_Js.triggerAddFolder("index.php?module=ITS4YouReports&view=EditFolder");' class="btn btn-default pull-right sidebar-btn" title="{vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}">
							<div class="fa fa-plus" aria-hidden="true"></div>
						</button>
					</div>
					<hr>
					<div>
						<input class="search-list" type="text" placeholder="{vtranslate('LBL_SEARCH_FOR_FOLDERS',$MODULE)}">
					</div>
					<div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
						<div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
							<div class="mCSB_container" style="position:relative; top:0; left:0;">
								<div class="list-menu-content">
									<div class="list-group">
										<ul class="lists-menu">
											<li style="font-size:12px;" class="listViewFilter" >
												<a href="#" class='filterName' data-filter-id="All"><i class="fa fa-folder foldericon"></i>&nbsp;{vtranslate('LBL_ALL_REPORTS', $MODULE)}</a>
											</li>
											{foreach item=FOLDER from=$FOLDERS name="folderview"}
												<li style="font-size:12px;" class="listViewFilter {if $smarty.foreach.folderview.iteration gt 18} filterHidden hide{/if}" >
													{assign var=VIEWNAME value={vtranslate($FOLDER->getName(),$MODULE)}}
													<a href="#" class='filterName' data-filter-id={$FOLDER->getId()}><i class="fa fa-folder foldericon"></i>&nbsp;{if {$VIEWNAME|strlen > 50} }{$VIEWNAME|substr:0:45}..{else}{$VIEWNAME}{/if}</a>
													{if "" !== $FOLDER->getDescription()}
														<i class="fa fa-info-circle" title="{$FOLDER->getDescription()}"></i>
													{/if}
													<div class="pull-right">
														{if $FOLDER->isEditable() && $FOLDER->isDeletable()}
															{assign var="FOLDERID" value=$FOLDER->get('folderid')}
															<span class="js-popover-container">
																<span class="fa fa-angle-down" data-id="{$FOLDERID}" data-deletable="true" data-editable="true" rel="popover" data-toggle="popover" data-deleteurl="{$FOLDER->getDeleteUrl()}" data-editurl="{$FOLDER->getEditUrl()}" data-toggle="dropdown" aria-expanded="true"></span>
															</span>
														{/if}
													</div>
												</li>
											{/foreach}
										</ul>

										<div id="filterActionPopoverHtml">
											<ul class="listmenu hide" role="menu">
												<li role="presentation" class="editFilter">
													<a role="menuitem"><i class="fa fa-pencil-square-o"></i>&nbsp;{vtranslate('LBL_EDIT',$MODULE)}</a>
												</li>
												<li role="presentation" class="deleteFilter">
													<a role="menuitem"><i class="fa fa-trash"></i>&nbsp;{vtranslate('LBL_DELETE',$MODULE)}</a>
												</li>
											</ul>
										</div>
										<h5 class="toggleFilterSize" data-more-text="{vtranslate('LBL_MORE',$MODULE)}.." data-less-text="{vtranslate('LBL_LESS',$MODULE)}..">
											{if $smarty.foreach.folderview.iteration gt 18}
												{vtranslate('LBL_MORE',$MODULE)}..
											{/if}
										</h5>
									</div>
								</div>
							</div>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
	{/if}
{/strip}