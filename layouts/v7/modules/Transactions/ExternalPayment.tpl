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
    .modal-body {
        padding: 0px!important;
    }
    iframe {
        width:100%;
        height:100%;
        min-height: 400px;
    }

</style>

<div class="popupReminderContainer">
    <div class="modal fade" id="PopupReminder" role="dialog" data-info="{$ACTIVES}" style="z-index: 1090">
        <div class="modal-dialog" style="width: 1100px">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">External payment</h4>
                </div>
                <div class="modal-body" style="min-height: 400px">
                    <iframe src="{$PLATFORM_URL}/paymade/{$PLATFORM_ID}" marginwidth="0" marginheight="0" frameborder="no" scrolling="yes">
                    </iframe>
                </div>
                <div class="modal-footer">
                    <button id="closeModalReload" type="button" class="btn btn-warning" data-dismiss="modal" style="float: right">Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>