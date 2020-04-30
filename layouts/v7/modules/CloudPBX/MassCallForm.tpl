<!-- Large modal -->
<!-- Modal -->
<style>
    .ellipsis{
        display: -webkit-box;
        max-width: 200px;
        max-height: 50px;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0px;
    }
    .subject{
        width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .popup-reminder tr th{
        text-align: center;
    }

</style>

<div class="popupReminderContainer">
    <div class="modal fade" id="PopupReminder" role="dialog" data-info="{$ACTIVES}" style="z-index: 1090">
        <div class="modal-dialog" style="width: 1100px">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Call to client</h4>
                </div>
                <div class="modal-body" style="overflow: scroll; max-height: 400px">
                    <table class="table table-bordered popup-reminder">
                        <thead>
                        <tr>
                            <th>Phone to call</th>
                            <th style="width: 65px;">Make Call</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from = $FIELDS item =FIELD_MODEL key=FIELD_NAME}
                            {if $FIELD_MODEL->isActiveField()}
                            <tr data-info="{$FIELD_MODEL->getId()}">
                                <td style="color: #15c">{vtranslate($FIELD_MODEL->get('label'), $PARENT)}</td>
                                <td>
                                    <button data-id="{$FIELD_MODEL->get('name')}" class="btn btn-outline makeCall" type="button" onclick="javascript:CloudPBX_Js.doCall('{$FIELD_VALUES[$FIELD_MODEL->getId()]}');">Make Call</button>
                                </td>
                            </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal" style="float: right">Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
