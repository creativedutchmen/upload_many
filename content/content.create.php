<?php
require_once(TOOLKIT . '/class.administrationpage.php');
require_once(EXTENSIONS . '/upload_many/fields/field.upload_many.php');

class contentExtensionUpload_Manycreate extends AdministrationPage {
	
	
	function __construct(&$parent){
		parent::__construct($parent);
	}
	
	function __actionNew(){

		$sectionManager = new SectionManager($this->_Parent);

		if(!$section_id = $sectionManager->fetchIDFromHandle($this->_context[1])){
			$return["error"] = "Section handle is incorrect";
		}
		elseif(!$section = $sectionManager->fetch($section_id)){
			$return["error"] = "Section can not be found.";
		}
		else{

			$entryManager = new EntryManager($this->_Parent);
			$fieldManager = new FieldManager($this->_Parent);

			$entry =& $entryManager->create();
			$entry->set('section_id', $section_id);
			$entry->set('author_id', $this->_Parent->Author->get('id'));
			$entry->set('creation_date', DateTimeObj::get('Y-m-d H:i:s'));
			$entry->set('creation_date_gmt', DateTimeObj::getGMT('Y-m-d H:i:s'));

			$fields = $_POST['fields'];
			
			//$_FILES['fields'][$upload_many_field_name] = $_FILES['file'];
			
			## Combine FILES and POST arrays, indexed by their custom field handles
			if(isset($_FILES['fields'])){
				$filedata = General::processFilePostData($_FILES['fields']);

				foreach($filedata as $handle => $data){
					if(!isset($fields[$handle])) $fields[$handle] = $data;
					elseif(isset($data['error']) && $data['error'] == 4) $fields['handle'] = NULL;
					else{

						foreach($data as $ii => $d){
							if(isset($d['error']) && $d['error'] == 4) $fields[$handle][$ii] = NULL;
							elseif(is_array($d) && !empty($d)){

								foreach($d as $key => $val)
									$fields[$handle][$ii][$key] = $val;
							}
						}
					}
				}
			}
			
			if(__ENTRY_FIELD_ERROR__ == $entry->checkPostData($fields, $this->_errors)){
				
				$this->pageAlert(__('Some errors were encountered while attempting to save.'), Alert::ERROR);
				foreach($this->_errors as $fieldId => $error){
					$errors[$fieldId]['fieldName'] = $fieldManager->fetch($fieldId)->get('element_name');
					$errors[$fieldId]['error'] = $error;
				}
				$return["error"] = $errors;
			}
			elseif(__ENTRY_OK__ != $entry->setDataFromPost($fields, $error)){
				$this->pageAlert($error['message'], Alert::ERROR);
				$return["error"] = "Something went wrong";
			}
			else{

				###
				# Delegate: EntryPreCreate
				# Description: Just prior to creation of an Entry. Entry object and fields are provided
				$this->_Parent->ExtensionManager->notifyMembers('EntryPreCreate', '/publish/new/', array('section' => $section, 'fields' => &$fields, 'entry' => &$entry));

				if(!$entry->commit()){
					define_safe('__SYM_DB_INSERT_FAILED__', true);
					$this->pageAlert(NULL, Alert::ERROR);
					$return["error"] = "Something went wrong in the database";

				}

				else{

					###
					# Delegate: EntryPostCreate
					# Description: Creation of an Entry. New Entry object is provided.
					$this->_Parent->ExtensionManager->notifyMembers('EntryPostCreate', '/publish/new/', array('section' => $section, 'entry' => $entry, 'fields' => $fields));

					$prepopulate_field_id = $prepopulate_value = NULL;
					if(isset($_POST['prepopulate'])){
						$prepopulate_field_id = array_shift(array_keys($_POST['prepopulate']));
						$prepopulate_value = stripslashes(rawurldecode(array_shift($_POST['prepopulate'])));
					}
					$return["error"] = "";
				}
			}
		}

		echo json_encode($return);
		exit();
	}

	
	function __actionUpload(){
		
		// Settings
		$targetDir = TMP . DIRECTORY_SEPARATOR . "plupload";
		$maxFileAge = 60 * 10; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);
		// usleep(5000);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$filePath = $targetDir . '/' . $file;

				// Remove temp files if they are older than the max age
				if (is_file($filePath) && (substr($file, -2) != 'db') && (filemtime($filePath) < time() - $maxFileAge)){
					unlink($filePath);
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096)){
							fwrite($out, $buff);
						}
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

					fclose($out);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id","filename":"'.$fileName.'"}');
	}
}