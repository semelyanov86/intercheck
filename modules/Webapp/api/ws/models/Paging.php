<?php

class Webapp_WS_PagingModel {
	
	var $_start;
	var $_limit;
	var $_page;
	
	function __construct() {
		$this->_limit = Webapp::config('API_RECORD_FETCH_LIMIT', 20);
	}
	
	function start() {
		return $this->_start;
	}
	
	function limit() {
		return $this->_limit;
	}
	
	function currentCount() {
		return ($this->current() * $this->limit());
	}
	
	function current() {
		return $this->_page;
	}
	
	function next() {
		return ($this->current()+1);
	}
	
	function previous() {
		return ($this->current() < 1? 0 : ($this->current()-1));
	}
	
	function hasNext($countOnPage) {
		return ($countOnPage >= $this->limit());
	}
	
	function hasPrevious() {
		return ($this->start() != 0);
	}
	
	function initStart($page) {
		
		if(empty($page)) $page = 0;
		$this->_page = $page;		
		
		if($page < 1) $this->_start = 0;
		else $this->_start = ($page * $this->_limit);
	}
	
	function setLimit($limit) {
		$this->_limit = $limit;
	}
	
	static function modelWithPageStart($start) {
		$instance = new self();
		$instance->initStart($start);
		return $instance;
	}
}
?>
