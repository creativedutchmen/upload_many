<?php

file_put_contents('log.txt',print_r(session_id(), true));

require_once(TOOLKIT . '/class.administrationpage.php');
require_once('/./../fields/field.upload_many.php');

class contentExtensionUpload_Manyindex extends AdministrationPage {
	
	
	function __construct(&$parent){
		parent::__construct($parent);
	}
	
	public function __viewIndex() {
		file_put_contents('log.txt',"login success!");		
	}
}