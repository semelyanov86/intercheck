<?php

include_once "modules/QuotingTool/QuotingTool.php";
/**
 * Class QuotingTool_EmailPreviewTemplate_View
 */
class QuotingTool_GenerateFormLink_View extends Vtiger_IndexAjax_View
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        global $site_URL;
        global $current_user;
        global $adb;
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $recordId = $request->get("record");
        $templateId = $request->get("template_id");
        $isCreateNewRecord = $request->get("isCreateNewRecord");
        $childModule = $request->get("childModule");
        $recordModel = new QuotingTool_Record_Model();
        $record = $recordModel->getById($templateId);
        $link = trim(html_entity_decode($record->get("linkproposal")));
        if ($link) {
            echo $link;
        } else {
            return false;
        }
    }
}

?>