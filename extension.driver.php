<?php

	Class Extension_Upload_Many extends Extension{
		
		public static $params = null;

		public function about() {
			return array('name' => 'Field: Upload Many',
						 'version' => '0.1',
						 'release-date' => '2010-07-14',
						 'author' => array('name' => 'Huib Keemink',
										   'website' => 'http://www.creativedutchmen.com',
										   'email' => ''),
							'description'   => 'Allows user to upload many documents/images at once using uploadify.'
				 		);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'initaliseAdminPageHead'
				)
			);
		}
		
		public function initaliseAdminPageHead($context) {
		
			//only sections that contain a upload_many field should get the extra functions.
			//could not find another way than to query the database (which is a shame, I think!)
			$page = $context['parent']->Page;
			$query = 'SELECT COUNT(*) as num FROM `tbl_fields` LEFT JOIN(`tbl_sections`) ON (tbl_fields.parent_section = tbl_sections.id) WHERE tbl_sections.handle = "'.$page->_context['section_handle'].'" AND tbl_fields.type = "upload_many" LIMIT 1';
			$result = $this->_Parent->Database->fetchRow(0,$query);
			
			if($result['num'] > 0){
				if ($page instanceof ContentPublish and ($page->_context['page'] == 'index')) {
					$page->addStylesheetToHead(URL . '/extensions/upload_many/assets/uploader.css', 'screen', 991);
					$page->addScriptToHead(URL . '/extensions/upload_many/assets/js/jquery.addButton.js', 992);
				}
				elseif($page instanceof ContentPublish and ($page->_context['page'] == 'new' || $page->_context['page'] == 'edit')) {
					$page->addStylesheetToHead(URL . '/extensions/upload_many/assets/uploader.css', 'screen', 991);
					$page->addScriptToHead(URL . '/extensions/upload_many/assets/js/swfobject.js', 992);
					$page->addScriptToHead(URL . '/extensions/upload_many/lib/stage/symphony.stage.js', 992);
					$page->addStylesheetToHead(URL . '/extensions/upload_many/lib/stage/symphony.stage.css', 992);
					$page->addScriptToHead(URL . '/extensions/upload_many/assets/js/jquery.upload_many.js', 992);
				}
			}
		}

		public function install() {
			try{
				Symphony::Database()->query(
					"CREATE TABLE IF NOT EXISTS `tbl_fields_upload_many`  (
						`id` INT UNSIGNED NOT NULL ,
						`field_id` INT UNSIGNED NOT NULL ,
						`destination` VARCHAR( 255 ) NOT NULL ,
						`validator` VARCHAR( 50 ) NULL DEFAULT NULL
					");
			}
			catch (Exception $e){
				return false;
			}
			return true;
        }
		
		public function uninstall() {
			try{
				Symphony::Database()->query("DROP TABLE `tbl_fields_upload_many`");
			}
			catch (Exception $e){
				return false;
			}
			return true;
		}
	}
	