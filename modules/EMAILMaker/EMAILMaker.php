<?php

class EMAILMaker
{
    private $basicModules;
    private $pageFormats;
    private $profilesActions;
    private $profilesPermissions;
    public $log;
    public $db;
    public $moduleModel;
    public $id;
    public $name;

    public function __construct()
    {
        $this->log = LoggerManager::getLogger('account');
        $this->db = PearDatabase::getInstance();
        $this->basicModules = array("20", "21", "22", "23");
        $this->profilesActions = array(
            "EDIT" => "EditView",
            "DETAIL" => "DetailView", // View
            "DELETE" => "Delete", // Delete
            "EXPORT_RTF" => "Export", // Export to RTF
        );
        $this->profilesPermissions = array();
        $this->name = "EMAILMaker";
        $this->id = getTabId("EMAILMaker");

    }

    public function vtlib_handler($modulename, $event_type)
    {
        $this->moduleModel = Vtiger_Module_Model::getInstance($this->name);

        switch ($event_type) {
            case 'module.postinstall':
                $this->executeSql();
                $this->actualizeLinks();
                $this->installWorkflows();
                break;
            case 'module.enabled':
            case 'module.postupdate':
                $res = $this->db->pquery("SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = 'EMAILMaker')", array());
                if ($this->db->num_rows($res) > 0) {
                    $res = $this->db->pquery("SELECT * FROM vtiger_emakertemplates_profilespermissions", array());
                    if ($this->db->num_rows($res) == 0) {
                        $this->db->pquery("INSERT INTO vtiger_emakertemplates_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'EMAILMaker')", array());
                    }
                    $this->db->pquery("DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'EMAILMaker')", array());
                }

                $this->actualizeLinks();
                $this->installWorkflows();
                break;
            case 'module.preupdate':
            case 'module.disabled':
                $this->removeLinks();
                break;
            case 'module.preuninstall':
                $this->removeLinks();
                $this->removeWorkflows();
                break;
        }
    }

    public function actualizeSeqTables()
    {

        if ($this->db->num_rows($this->db->pquery("SELECT id FROM vtiger_emakertemplates_drips_seq", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_drips_seq VALUES (?)", array('0'));
        }
        if ($this->db->num_rows($this->db->pquery("SELECT id FROM vtiger_emakertemplates_drip_groups_seq", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_drip_groups_seq VALUES (?)", array('0'));
        }
        if ($this->db->num_rows($this->db->pquery("SELECT id FROM vtiger_emakertemplates_drip_tpls_seq", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_drip_tpls_seq VALUES (?)", array('0'));
        }
        if ($this->db->num_rows($this->db->pquery("SELECT id FROM vtiger_emakertemplates_seq", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_seq VALUES (?)", array('0'));
        }
        if ($this->db->num_rows($this->db->pquery("SELECT delay_active FROM vtiger_emakertemplates_delay", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_delay VALUES (?)", array('0'));
        }
        if ($this->db->num_rows($this->db->pquery("SELECT id FROM vtiger_emakertemplates_relblocks_seq", array())) < 1) {
            $this->db->pquery("INSERT INTO vtiger_emakertemplates_relblocks_seq VALUES (?)", array('0'));
        }
    }

    public function executeSql()
    {

        $this->actualizeSeqTables();

        $productblocData = "INSERT INTO `vtiger_emakertemplates_productbloc_tpl` (`id`, `name`, `body`) VALUES
      (1, 'product block for individual tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_NET_PRICE%<br />\r\n				without TAX<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax% (%)</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax%</strong> (<strong>$" . "CURRENCYCODE$</strong>)</span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATPERCENT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATSUM$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTALSUM$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Subtotals</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "TOTALWITHOUTVAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				&nbsp;</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "VAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SUBTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				Total with TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n'),
(2, 'product block for group tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span>%G_LBL_NET_PRICE% without TAX</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHOUTVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Total without TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_Tax% $" . "VATPERCENT$ % %G_LBL_LIST_OF% $" . "TOTALAFTERDISCOUNT$</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "VAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				Total with TAX</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n');";

        $this->db->pquery($productblocData, array());
    }

    public function actualizeLinks()
    {
        require_once('vtlib/Vtiger/Module.php');

        $Related_Modules = getEmailRelatedModules();
        $result1 = $this->db->pquery("SELECT module FROM vtiger_emakertemplates WHERE deleted = ? GROUP BY module", array('0'));
        $num_rows1 = $this->db->num_rows($result1);

        if ($num_rows1 > 0) {
            while ($row = $this->db->fetchByAssoc($result1)) {
                if (!in_array($row["module"], $Related_Modules)) {
                    $Related_Modules[] = $row["module"];
                }
            }
        }

        if (count($Related_Modules) > 0) {
            foreach ($Related_Modules AS $module) {
                $this->moduleModel->AddLinks($module);
            }
        }

        $link_module = Vtiger_Module::getInstance("EMAILMaker");
        $link_module->addLink('HEADERSCRIPT', 'EMAILMakerJS', 'layouts/v7/modules/EMAILMaker/resources/EMAILMakerActions.js');
        $link_module->addLink('HEADERSCRIPT', 'EMAILMakerJS', 'layouts/v7/modules/EMAILMaker/resources/MassEdit.js');
    }

    public function removeLinks()
    {
        $this->db->pquery('DELETE FROM vtiger_links WHERE linktype=? AND linklabel=? AND linkurl LIKE ?', ['HEADERSCRIPT', 'EMAILMakerJS', '%EMAILMaker%']);
        $this->db->pquery('DELETE FROM vtiger_links WHERE linktype=? AND linklabel=? AND linkurl LIKE ?', ['LISTVIEWMASSACTION', 'Send Emails with EMAILMaker', '%EMAILMaker%']);
        $this->db->pquery('DELETE FROM vtiger_links WHERE linktype=? AND linklabel=? AND linkurl LIKE ?', ['DETAILVIEWSIDEBARWIDGET', 'EMAILMaker', '%EMAILMaker%']);
        $this->db->pquery('DELETE FROM vtiger_links WHERE linktype=? AND linklabel=? AND linkurl LIKE ?', ['HEADERSCRIPT', 'EMAILMakerActionsJS', '%EMAILMaker%']);
    }

    public function GetPageFormats()
    {
        return $this->pageFormats;
    }

    public function GetBasicModules()
    {
        return $this->basicModules;
    }

    public function GetProfilesActions()
    {
        return $this->profilesActions;
    }

    public function installWorkflows()
    {
        $this->installWorkflow("VTEMAILMakerMailTask", "Send Email from EMAIL Maker");
    }

    public function installWorkflow($name, $info)
    {
        $file_exist = false;
        $dest1 = "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
        $source1 = "modules/EMAILMaker/workflow/" . $name . ".inc";
        if (file_exists($dest1)) {
            $file_exist1 = true;
        } else {
            if (copy($source1, $dest1)) {
                $file_exist1 = true;
            }
        }
        $dest2 = "layouts/v7/modules/Settings/Workflows/Tasks/" . $name . ".tpl";
        $source2 = "layouts/v7/modules/EMAILMaker/taskforms/" . $name . ".tpl";
        if (file_exists($dest2)) {
            $file_exist2 = true;
        } else {
            if (copy($source2, $dest2)) {
                $file_exist2 = true;
            }
        }
        if ($file_exist1 && $file_exist2) {
            $sql1 = "SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?";
            $result1 = $this->db->pquery($sql1, array($name));
            if ($this->db->num_rows($result1) == 0) {
                $workflow_id = $this->db->getUniqueID("com_vtiger_workflow_tasktypes");
                $sql2 = "INSERT INTO `com_vtiger_workflow_tasktypes` (`id`, `tasktypename`, `label`, `classname`, `classpath`, `templatepath`, `modules`, `sourcemodule`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $this->db->pquery($sql2, array($workflow_id, $name, $info, $name, $source1, 'modules/EMAILMaker/taskforms/' . $name . '.tpl', '{"include":[],"exclude":[]}', 'EMAILMaker'));
            }
        }
    }

    private function removeWorkflows()
    {
        $sql1 = "DELETE FROM com_vtiger_workflow_tasktypes WHERE sourcemodule = ?";
        $this->db->pquery($sql1, array('EMAILMaker'));

        $sql2 = "DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?";
        $this->db->pquery($sql2, array('%:"VTEMAILMakerMailTask":%'));

        @shell_exec('rm -f modules/com_vtiger_workflow/tasks/VTEMAILMakerMailTask.inc');
        @shell_exec('rm -f layouts/v7/modules/Settings/Workflows/Tasks/VTEMAILMakerMailTask.tpl');
    }
}