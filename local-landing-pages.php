<?php
/*
Plugin Name: Local Landing Pages
Plugin URI: Pending.
Description: Allows users to set specific landing pages for visitors based on their browser's preferred language setting.
Version: 0.0.5
Author: Ross Chapman & Desiree Cox for Zendesk
Author URI: http://zendesk.com
License: GPL2
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : dcox@zendesk.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function llp_install() {
	global $llp_db_version;
	global $wpdb;
	$table_name = $wpdb->prefix . "llp";
	$sql = "CREATE TABLE " . $table_name . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		language varchar(255) NOT NULL,
		path varchar(255) NOT NULL,
		UNIQUE KEY id (id)
		);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	add_option("llp_db_version", $llp_db_version);
}
register_activation_hook(__FILE__,'llp_install');

function my_plugin_menu() {
	add_options_page('Options', 'Local Landing Pages', 'manage_options', 'llp', 'llp_options');
}
function llp_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	include('llp-menu.php');
}
add_action('admin_menu', 'my_plugin_menu');

function remove() {
	global $wpdb; // this is how you get access to the database
	$tablename = $wpdb->prefix . 'llp';
	$language = $_POST['language'];
	$wpdb->query("
					DELETE FROM $tablename WHERE language = '$language'"
					);

	die(); // this is required to return a proper result
}
add_action('wp_ajax_remove', 'remove');

function add() {
	global $wpdb;
	$data = explode('&', $_POST['data']);
	var_dump($data);
	$save = array();
	for($i = 1; $i <= (count($data)/2); $i++) {
		$s_key = substr($data[(($i * 2) - 2)], (strpos($data[(($i * 2) - 2)], '=') + 1));
		$s_value = substr($data[(($i * 2) - 1)], (strpos($data[(($i * 2) - 1)], '=') + 1));
		$save[$s_key] = $s_value;
	}

	$tablename = $wpdb->prefix . 'llp';
	foreach($save as $saved_key => $saved_value) {
		$wpdb->query("
						DELETE FROM $tablename WHERE language = '$saved_key'"
						);
		$wpdb->query("
						INSERT INTO $tablename (language, path)
						VALUES ('$saved_key', '$saved_value')"
						);		
	}

	die(); // this is required to return a proper result
}
add_action('wp_ajax_add', 'add');

// Checks cookies to determine whether it is your user's first visit.
function newuser_cookie() {
	if(!isset($_COOKIE['newuser']) && empty($_COOKIE['newuser'])) {
 		landing_page_redirect();
	}
}
add_action( 'init', 'newuser_cookie');

// Determines the user's language code and redirects to whatever page has a matching path.
function landing_page_redirect() {
	setcookie('newuser', 1, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
	// first we parse out the language code.
	$header_language = explode('-', $_SERVER['HTTP_ACCEPT_LANGUAGE']); // Format: "en-US,en;q=0.8"		
	$language = strtolower($header_language[0]); // Format: "en-US"
	// Check for foreign language.
	if($language != 'en') {
		global $wpdb; // this is how you get access to the database
		$tablename = $wpdb->prefix . 'llp';
		$path = $wpdb->get_var(
		            $wpdb->prepare( "SELECT path FROM
		                    $tablename WHERE
		                    language = %s", $language)
		            );
		$path = str_replace('%2F','/',$path);
		$base_url = get_bloginfo('url');
		$destination = $base_url . '/' . $path;
		wp_redirect($destination); 
		exit;
	}			
}