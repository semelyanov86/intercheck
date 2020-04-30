<div class="related-tabs">
    <form class="" method="POST" id="frmSaveAPI" action="">
        <input type="hidden" name="module" value="{$QUALIFIED_MODULE}"/>
        <input type="hidden" name="action" value="SaveAjax"/>
        <input type="hidden" name="mode" value="saveAPI"/>
        <div class="row form-group">
            <div class="col-md-7">
                <div class="row form-group">
                    <div class="col-md-3">
                        <label class="" for="txt_realmid">{vtranslate("LBL_REALMID", $QUALIFIED_MODULE)}&nbsp;<span style="color: red;">(*)</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="inputElement" id="txt_realmid" name="realmid" value="{$QBO_API['realmid']}" required />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label class="" for="txt_consumer_key">{vtranslate("LBL_CONSUMER_KEY", $QUALIFIED_MODULE)}&nbsp;<span style="color: red;">(*)</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="inputElement" id="txt_consumer_key" name="consumer_key" value="{$QBO_API['consumer_key']}" required />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label class="" for="txt_consumer_secret">{vtranslate("LBL_CONSUMER_SECRET", $QUALIFIED_MODULE)}&nbsp;<span style="color: red;">(*)</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="inputElement" id="txt_consumer_secret" name="consumer_secret" value="{$QBO_API['consumer_secret']}" required />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label class="" for="txt_access_token_secret">{vtranslate("LBL_ACCESS_TOKEN_SECRET", $QUALIFIED_MODULE)}&nbsp;<span style="color: red;">(*)</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="inputElement" id="txt_access_token_secret" name="access_token_secret" value="{$QBO_API['access_token_secret']}" required />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-3">
                        <label class="" for="txt_access_token">{vtranslate("LBL_ACCESS_TOKEN", $QUALIFIED_MODULE)}&nbsp;<span style="color: red;">(*)</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="inputElement" id="txt_access_token" name="access_token" value="{$QBO_API['access_token']}" required />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="btnSaveQboApi" value="">{vtranslate('BTN_SAVE', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="box-info">
                    <h5><i class="fa fa-info-circle"></i>&nbsp;&nbsp;{vtranslate("LBL_API_INFO_HEADER", $QUALIFIED_MODULE)}</h5><br />
                    <p>{vtranslate("LBL_API_INFO", $QUALIFIED_MODULE)}</p>
                </div>
            </div>
        </div>
    </form>
</div>