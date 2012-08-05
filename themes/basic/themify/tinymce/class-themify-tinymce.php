<?php
/**
 * Class to interact with TinyMCE editor. 
 * @package themify 
 * @author Elio Rivero
 */

class Themify_TinyMCE {
	
	function __construct(){
		if ( current_user_can('edit_posts') && current_user_can('edit_pages') && get_user_option('rich_editing') == 'true'){
			add_filter('tiny_mce_version', array(&$this, 'tiny_mce_version') );
			add_filter("mce_external_plugins", array(&$this, "mce_external_plugins"));
			add_filter('mce_buttons_2', array(&$this, 'mce_buttons'));
		}
	}
	function mce_buttons($buttons) {
		array_push($buttons, "separator", "btnthemifyMenu");
		return $buttons;
	}
	function mce_external_plugins($plugin_array) {
		$plugin_array['themifyMenu'] = THEMIFY_URI . '/tinymce/tinymce.menu.js';
		return $plugin_array;
	}
	function tiny_mce_version($version) {
		return ++$version;
	}
}

?>