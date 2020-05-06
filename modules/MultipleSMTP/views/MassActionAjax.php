<?php

class MultipleSMTP_MassActionAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("showMassEditForm");
        $this->exposeMethod("showListview");
        $this->exposeMethod("showTOField");        
    }
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * Function returns the mass edit form
     * @param Vtiger_Request $request
     */
    public function showMassEditForm(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $userId = $request->get("userid");
        if (!$userId) {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userId = $currentUser->getId();
        }
        $sid = $request->get("id");
        $viewer = $this->getViewer($request);
        $sequence = MultipleSMTP_Module_Model::getLastSequence($userId);
        $sequence = $sequence + 1;
        $smtpModuleModel = new MultipleSMTP_Module_Model();
        $serverInfo = $smtpModuleModel->getUserServer($userId, $sid);
        if (!empty($serverInfo)) {
            $sequence = $serverInfo["sequence"];
        }
        $viewer->assign("QUALIFIED_MODULE", $moduleName);
        $viewer->assign("SERVER_INFO", $serverInfo);
        $viewer->assign("USERID", $userId);
        $viewer->assign("SEQUENCE", $sequence);
        echo $viewer->view("MassEditForm.tpl", $moduleName, true);
    }
    public function showListview(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $userId = $request->get("userid");
        $viewer = $this->getViewer($request);
        $smtpModuleModel = new MultipleSMTP_Module_Model();
        $list_servers = $smtpModuleModel->getUserServers($userId);
        $viewer->assign("QUALIFIED_MODULE", $moduleName);
        $viewer->assign("SERVERS_LIST", $list_servers);
        $viewer->assign("USERID", $userId);
        echo $viewer->view("ListView.tpl", $moduleName, true);
    }
    public function showTOField(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userId = $currentUser->getId();
        $focusModule = $request->get("module_focus");
        $smtpModuleModel = new MultipleSMTP_Module_Model();
        $list_servers = $smtpModuleModel->getUserServers($userId);
        $to_field = "";
        $parentId = $request->get("parent_id");
        global $adb;
        global $vtiger_current_version;
        $results = $adb->pquery("SELECT from_email FROM `vtiger_emaildetails` WHERE emailid=?", array($parentId));
        if (0 < $adb->num_rows($results)) {
            $fromEmail = $adb->query_result($results, 0, "from_email");
        }
        if (0 < count($list_servers)) {
            if (version_compare($vtiger_current_version, "7.0.0", "<")) {
                if ($focusModule == "QuotingTool") {
                    $to_field = "<div class=\"row-fluid padding10 marginBottom10px\"><span class=\"span10\"><span class=\"row-fluid\"><span class=\"span2 pull-right\">From<span class=\"redColor\">*</span></span><span class=\"span9\">" . "<select class=\"from_field\" name=\"from_serveremailid\">";
                } else {
                    $to_field = "<div class=\"row-fluid padding10\"><span class=\"span8\"><span class=\"row-fluid\"><span class=\"span2\">From<span class=\"redColor\">*</span></span><span class=\"span9\">" . "<select class=\"from_field\" name=\"from_serveremailid\">";
                }
                foreach ($list_servers as $serverinfo) {
                    $to_field .= "<option value=\"" . $serverinfo["id"] . "\"" . ($fromEmail == $serverinfo["from_email_field"] ? "selected" : "") . ">" . $serverinfo["name"] . " (" . $serverinfo["from_email_field"] . ")</option>";
                }
                $to_field .= "</select>" . "</span></span><span class=\"span4\"></span></div>";
            } else {
                $to_field = "";
                if ($focusModule == "QuotingTool") {
                    $to_field = "<div class=\"row\"><div class=\"col-lg-12\"><div class=\"col-lg-2\"><span class=\"pull-right\">From<span class=\"redColor\">*</span></div><div class=\"col-lg-10\">" . "<select class=\"from_field select2\" name=\"from_serveremailid\" style=\"width: 100%\">";
                } else {
                    $to_field = "<div class=\"row\"><div class=\"col-lg-12\"><div class=\"col-lg-2\"><span class=\"pull-right\">From<span class=\"redColor\">*</span></div><div class=\"col-lg-6\">" . "<select class=\"from_field select2\" name=\"from_serveremailid\">";
                }
                foreach ($list_servers as $serverinfo) {
                    $to_field .= "<option value=\"" . $serverinfo["id"] . "\"" . ($fromEmail == $serverinfo["from_email_field"] ? "selected" : "") . ">" . $serverinfo["name"] . " (" . $serverinfo["from_email_field"] . ")</option>";
                }
                $to_field .= "</select>" . "</div></div><div class=\"col-lg-4\"></div></div>";
            }
            $to_field .= "<input type=\"hidden\" value=\"1\" name=\"number_of_smtp\"/>";
        } else {
            $to_field .= "<input type=\"hidden\" value=\"0\" name=\"number_of_smtp\"/>";
        }
        echo $to_field;
    }
}

?>