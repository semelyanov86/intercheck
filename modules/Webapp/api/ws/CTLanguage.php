<?php
/*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

class Webapp_WS_CTLanguage extends Webapp_WS_Controller {

	function process(Webapp_API_Request $request) {
		global $adb,$current_user;

		$current_user = $this->getActiveUser();
		$language = $current_user->language;

		$languages =  array();
		
		$languages = $this->getLanguageFields($language);

		$response = new Webapp_API_Response();
		$response->setResult(array('section_list'=>$languages));
		return $response;

	}

	function getLanguageFields($language){
		global $adb;
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$query = "SELECT * FROM webapp_language_keyword WHERE keyword_lang = ?";
		$results = $adb->pquery($query,array($language));
		$numRows = $adb->num_rows($results);
		$fields = array();
		if($numRows == 0){
			$en_query = "SELECT * FROM webapp_language_keyword WHERE keyword_lang = ?";
			$en_results = $adb->pquery($en_query,array('en_us'));
			$en_numRows = $adb->num_rows($en_results);
			for($i=0;$i<$en_numRows;$i++){
				$keyword_name = $adb->query_result($en_results,$i,'keyword');
				$language_keyword = $adb->query_result($en_results,$i,'language_keyword');
				$language_keyword = html_entity_decode($language_keyword, ENT_QUOTES, $default_charset);
				if($keyword_name == 'copyright'){
					$language_keyword = 'Copyright © '.date('Y').' VTiger Version';
				}
				$fields[] = array('keyword_name'=>$keyword_name,'language_keyword'=>$language_keyword);
			}
		}else{
			for($i=0;$i<$numRows;$i++){
				$keyword_name = $adb->query_result($results,$i,'keyword');
				$language_keyword = $adb->query_result($results,$i,'language_keyword');
				$language_keyword = html_entity_decode($language_keyword, ENT_QUOTES, $default_charset);
				if($keyword_name == 'copyright'){
					$language_keyword = 'Copyright © '.date('Y').' VTiger Version';
				}
				$fields[] = array('keyword_name'=>$keyword_name,'language_keyword'=>$language_keyword);
			}
		}
		return $fields;
	}

}