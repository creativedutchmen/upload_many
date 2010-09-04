<?php

require_once TOOLKIT . '/fields/field.upload.php';

Class fieldUpload_Many extends fieldUpload{

	function __construct(&$parent){
		parent::__construct($parent);
		$this->_name = __('Upload Many');
		$this->_required = false;
		
		$this->set('hide', 'no');
		$this->set('required','yes');
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
	
	function processRawFieldData($data, &$message, $entry_id=NULL){
		$data = '/'.$data;
		if(is_file(TMP . '/plupload' . $data)){
		
			$abs_path = DOCROOT . '/' . trim($this->get('destination'), '/');
			$rel_path = str_replace('/workspace', '', $this->get('destination'));
			
			rename(TMP . '/plupload/' . $data, $abs_path . $data);
			
			$data = $rel_path . $data;
			
		}
		return parent::processRawFieldData($data, $message, $entry_id);
	}
	
	function checkPostFieldData($data, &$message, $entry_id=NULL){
	
		$data = '/'.$data;

		if(file_exists(DOCROOT . '/' . trim($this->get('destination'), "/") . $data)){
			$message = __('A file with the name %1$s already exists in %2$s. Please rename the file first, or choose another.', array(substr($data,1), $this->get('destination')));
			return self::__INVALID_FIELDS__;				
		}
		
		//TODO: add validation rules. The normal rules do not apply, because the file has not yet been moved.
		//maybe this can be solved by adding the moving-logic into this function.
		
		return self::__OK__;
		//return parent::checkPostFieldData($data, &$message, $entry_id);
	}
	
}