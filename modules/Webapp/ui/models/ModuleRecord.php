<?php

include_once dirname(__FILE__) . '/Block.php';

class Webapp_UI_ModuleRecordModel {
	private $_id;
	private $_blocks = array();
	
	function initData($recordData) {
		$this->data = $recordData;
		if (isset($recordData['blocks'])) {
			$blocks = Webapp_UI_BlockModel::buildModelsFromResponse($recordData['blocks']);
			foreach($blocks as $block) {
				$this->_blocks[$block->label()] = $block;
			}
		}
	}
	
	function setId($newId) {
		$this->_id = $newId;
	}
	
	function id() {
		return $this->data['id'];
	}
	
	function label() {
		return $this->data['label'];
	}
	
	function blocks() {
		return $this->_blocks;
	}
	
	static function buildModelFromResponse($recordData) {
		$instance = new self();
		$instance->initData($recordData);
		return $instance;
	}
	
	static function buildModelsFromResponse($records) {
		$instances = array();
		foreach($records as $recordData) {
			$instance = new self();
			$instance->initData($recordData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}
