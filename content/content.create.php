<?php
require_once(TOOLKIT . '/class.administrationpage.php');
require_once('/./../fields/field.upload_many.php');

class contentExtensionUpload_Manycreate extends AdministrationPage {
	
	
	function __construct(&$parent){
		parent::__construct($parent);
	}
	
	//taken from content.publish.php (modified to return a json string instead of a html page).
	function __actionNew(){
		
		if(@array_key_exists('save', $_POST['action']) || @array_key_exists("done", $_POST['action'])) {

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
				
				$upload_many_field_name = substr($_POST['fieldName'],7,-1);
				
				//$_FILES['fields'][$upload_many_field_name] = $_FILES['file'];
				
				print_r($_FILES);

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
				
				$fields[$upload_many_field_name] = $_FILES['file'];
				
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
		}
		else{
			$return["error"] = "No POST data present";
		}
		echo json_encode($return);
		exit();
	}
}