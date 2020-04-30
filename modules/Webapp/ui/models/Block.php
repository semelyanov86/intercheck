<?php

include_once dirname(__FILE__) . '/Field.php';

class Webapp_UI_BlockModel {
	private $_label;
	private $_fields = array();
	
	function initData($blockData) {
		$this->_label = $blockData['label'];
		if (isset($blockData['fields'])) {
			$this->_fields = Webapp_UI_FieldModel::buildModelsFromResponse($blockData['fields']);
		}
	}
	
	function label() {
		return $this->_label;
	}
	
	function fields() {
		return $this->_fields;
	}
	
	static function buildModelsFromResponse($blocks) {
		$instances = array();
		foreach($blocks as $blockData) {
			$instance = new self();
			$instance->initData($blockData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}
