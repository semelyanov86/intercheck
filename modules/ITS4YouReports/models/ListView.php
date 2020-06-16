<?php

/**
 * ITS4YouReports ListView Model Class
 */
class ITS4YouReports_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list of listview links for the module
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
        $links = array('LISTVIEWBASIC' => array(), 'LISTVIEW' => array(), 'LISTVIEWSETTING'=> array());

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if($currentUserModel->isAdminUser() || $privileges->hasModulePermission($this->getModule()->getId())) {
            $moduleModel = $this->getModule();
			if($layout == "v7"){
	            $dbLinks = Vtiger_Link_Model::getAllByType($moduleModel->getId(), array('LISTVIEWBASIC'));
	            $addLinksBasic = array();
	            foreach($dbLinks['LISTVIEWBASIC'] as $dLink) {
					$addLinksBasic[] = $dLink;
				}
	            $links['LISTVIEWBASIC'] = $addLinksBasic;
			} else {
				$addLinksBasic = array(
						array(
								'linktype' => 'LISTVIEWBASIC',
								'linklabel' => vtranslate('LBL_ADD_TEMPLATE','ITS4YouReports'),
								'linkurl' => $this->getCreateRecordUrl(),
								'linkicon' => ''
						),
						array(
								'linktype' => 'LISTVIEWBASIC',
								'linklabel' => 'LBL_ADD_FOLDER',
								'linkurl' => 'javascript:ITS4YouReports_List_Js.triggerAddFolder("'.$this->getModule()->getAddFolderUrl().'")',
								'linkicon' => ''
						)
				);			
				foreach($addLinksBasic as $basicLink) {
					$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
				}
			}
        }
        if ($currentUserModel->isAdminUser()) {
            $SettingsLinks = ITS4YouReports_ITS4YouReports_Model::GetAvailableSettings();
            foreach($SettingsLinks as $stype => $sdata) {
                $s_parr = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => $sdata["label"],
                'linkurl' => $sdata["location"],
                'linkicon' => '');
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($s_parr);
            }

            $sParr = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_SETTINGS', 'ITS4YouReports'),
                'linkurl' => sprintf('index.php?module=%s&view=ITS4YouReportsSettings&mode=Edit', $this->getModule()->getName()),
                'linkicon' => ''];
            $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($sParr);
		}

        $sParr = [
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => vtranslate('LBL_USER_API_KEY_SETTINGS', 'ITS4YouReports'),
            'linkurl' => sprintf('index.php?module=%s&view=UserMapsKeySettings&mode=Edit', $this->getModule()->getName()),
            'linkicon' => ''];
        $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($sParr);

        return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$massActionLinks = array();
		if($currentUserModel->hasModulePermission($this->getModule()->getId())) {
			$massActionLinks[] = array(
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_DELETE',
					'linkurl' => 'javascript:ITS4YouReports_List_Js.massDelete("index.php?module='.$this->getModule()->get('name').'&action=MassDelete");',
					'linkicon' => ''
			);

			$massActionLinks[] = array(
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_MOVE_REPORT',
					'linkurl' => 'javascript:ITS4YouReports_List_Js.massMove("index.php?module='.$this->getModule()->get('name').'&view=MoveReports");',
					'linkicon' => ''
			);
		}

		foreach($massActionLinks as $massActionLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
        require_once('modules/ITS4YouReports/models/ITS4YouReportsListHeader.php');
        $newLists = new ITS4YouReports_List_Header();

        $getAllHeaderInfo = $newLists->getAllHeaders($this->get("search_params"));
        return $getAllHeaderInfo;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$reportFolderModel = ITS4YouReports_Folder_Model::getInstance();

        $reportFolderModel->set('folderid', $this->get('folderid'));
		$orderBy = $this->get('orderby');
		if(!empty($orderBy)) {
			$reportFolderModel->set('orderby', $orderBy);
			$reportFolderModel->set('sortby', $this->get('sortorder'));
		}

		$reportRecordModels = $reportFolderModel->getReports($pagingModel,$this->get("search_params"));
		$pagingModel->calculatePageRange($reportRecordModels);
                
		return $reportRecordModels;
	}

	/**
	 * Function to get the list view entries count
	 * @return <Integer>
	 */
	public function getListViewCount() {
		$reportFolderModel = ITS4YouReports_Folder_Model::getInstance();
		$reportFolderModel->set('folderid', $this->get('folderid'));
 		return $reportFolderModel->getReportsCount();
	}
	
	public function getCreateRecordUrl(){
		return 'javascript:ITS4YouReports_List_Js.addReport("'.$this->getModule()->getCreateRecordUrl().'")';
	}

}
