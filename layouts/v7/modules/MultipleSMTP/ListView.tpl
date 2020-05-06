{*<!--
/* ********************************************************************************
* The content of this file is subject to the Multiple SMTP ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */
-->*}

{strip}
    <style>
        .listViewEntriesTable{
            margin-bottom: 5px !important;
        }
    </style>
    <div class="modal-dialog modal-lg createFieldModal modelContainer " style="width: 97%">
        <div class="modal-content">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right ">
                        <button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <h4 class="pull-left">{vtranslate('LBL_OUTGOING_SERVER', 'Settings:Vtiger')}</h4>
                </div>
            </div>
            <div class="modal-body">
                <table class="table table-bordered listViewEntriesTable">
                    <thead>
                    <tr class="listViewHeaders">
                        <th nowrap="" style="width: 16%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="subject">Server Name&nbsp;&nbsp;</a>
                        </th>
                        <th nowrap="" style="width: 15%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">User Name&nbsp;&nbsp;</a>
                        </th>
						<th nowrap="" style="width: 14%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">Name&nbsp;&nbsp;</a>
                        </th>
						<th nowrap="" style="width: 14%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">From Email&nbsp;&nbsp;</a>
                        </th>
						<th nowrap="" style="width: 13%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">Reply to Email&nbsp;&nbsp;</a>
                        </th>
						<th nowrap="" style="width: 11%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">Authentication&nbsp;&nbsp;</a>
                        </th>
						<th nowrap="" style="width: 13%;">
                            <a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="ASC" data-columnname="accountid">Save to Sent Folder&nbsp;&nbsp;</a>
                        </th>
                        <th nowrap=""></th>
                    </tr>
                    </thead>
                </table>
                <div class="clearfix"></div>
                <div class="row-fluid">
                    <div class="blocksSortable ui-sortable" data-column="1" >
                        {foreach item=server from=$SERVERS_LIST}
                            <div class="blockSortable" data-id="{$server.id}">
                                <div class="border1px" style="margin-bottom: 5px;">
                                    <div class="row" style="padding: 5px;" >
                                        <div class="col-sm-2">
                                            <img class="alignMiddle" src="{vimage_path('drag.png')}" /> &nbsp;&nbsp;{$server.server}
                                        </div>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-sm-2">
													{$server.server_username}&nbsp;
												</div>
												<div class="col-sm-2">
													{$server.name}&nbsp;
												</div>
												<div class="col-sm-2">
													{$server.from_email_field}&nbsp;
												</div>
												<div class="col-sm-2">
													{$server.replyto_email_field}&nbsp;
												</div>
												<div class="col-sm-4">
													<div class="row">
														<div class="col-sm-4">
															{if $server.smtp_auth eq 1}YES{else}NO{/if}
														</div>
														<div class="col-sm-4">
															{if $server.send_folder eq 1}YES{else}NO{/if}
														</div>
														<div class="col-sm-4">
															<span class="pull-right">
																<i class="fa fa-pencil edit_info" title="{vtranslate('Edit', $QUALIFIED_MODULE)}" data-url="index.php?module=MultipleSMTP&view=MassActionAjax&mode=showMassEditForm&userid={$server.userid}&id={$server.id}"></i>
																&nbsp;&nbsp;<i class="fa fa-trash delete_server" title="{vtranslate('Remove', $QUALIFIED_MODULE)}" data-userid="{$server.userid}" data-url="index.php?module=MultipleSMTP&action=DeleteAjax&userid={$server.userid}&id={$server.id}"></i>
															</span>
														</div>
													</div>
												</div>
											</div>
										</div>
										
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right cancelLinkContainer" style="margin-top:0px;">
                    <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                <button class="btn btn-success edit_info" data-url="index.php?module=MultipleSMTP&view=MassActionAjax&mode=showMassEditForm&userid={$USERID}" type="" name=""><strong>{vtranslate('Add New Configuration', $MODULE)}</strong></button>
            </div>
        </div>
    </div>
{/strip}