<?php

require_once TOOLKIT . '/fields/field.upload.php';

Class fieldUpload_Many extends fieldUpload{

	function __construct(&$parent){
		parent::__construct($parent);
		$this->_name = __('Upload Many');
		$this->_required = false;
		
		$this->set('hide', 'no');
	}
	
	function isSortable(){
		return false;
	}
	
	function canFilter(){
		return false;
	}

	function allowDatasourceOutputGrouping(){
		return false;
	}
	
	function allowDatasourceParamOutput(){
		return false;
	}

	function canPrePopulate(){
		return false;
	}
	
}