<?php

require_once(TOOLKIT . '/class.administrationpage.php');

class contentExtensionUpload_Manyindex extends AdministrationPage {
	
	
	function __construct(&$parent){
		parent::__construct($parent);
	}
	
	public function __viewIndex() {
		header('content-type: text/plain');
		print_r($_FILES);
		exit;
	}
}