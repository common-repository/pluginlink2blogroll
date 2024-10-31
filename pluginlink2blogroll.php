<?php
/*
Plugin Name: Pluginlink2Blogroll
Plugin URI: http://photozero.net/pluginlink2blogroll
Description: Copy your plugins' or themes' Author URI to your Blogroll.
Version: 1.0.0
Author: Neekey
Author URI: http://photozero.net/
*/

if (!defined('WP_CONTENT_DIR')) {
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}


//From WP-Pluginsused
function get_plugins_data($plugin_file) {
	$plugin_data = implode('', file($plugin_file));
	preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	if (preg_match("|Version:(.*)|i", $plugin_data, $version)) {
		$version = trim($version[1]);
	} else {
		$version = '';
	}
	$plugin_name = trim($plugin_name[1]);
	$plugin_uri = trim($plugin_uri[1]);
	$description = wptexturize(trim($description[1]));
	$author = trim($author_name[1]);
	$author_uri = trim($author_uri[1]);
	return array('Plugin_Name' => $plugin_name, 'Plugin_URI' => $plugin_uri, 'Description' => $description, 'Author' => $author, 'Author_URI' => $author_uri, 'Version' => $version);
}


function get_themes_data($theme_file) {
	$theme_data = implode('', file($theme_file));
	preg_match("|Theme Name:(.*)|i", $theme_data, $theme_name);
	preg_match("|Theme URI:(.*)|i", $theme_data, $theme_uri);
	preg_match("|Description:(.*)|i", $theme_data, $description);
	preg_match("|Author:(.*)|i", $theme_data, $author_name);
	preg_match("|Author URI:(.*)|i", $theme_data, $author_uri);
	if (preg_match("|Version:(.*)|i", $theme_data, $version)) {
		$version = trim($version[1]);
	} else {
		$version = '';
	}
	$theme_name = trim($theme_name[1]);
	$theme_uri = trim($theme_uri[1]);
	$description = wptexturize(trim($description[1]));
	$author = trim($author_name[1]);
	$author_uri = trim($author_uri[1]);
	return array('Theme_Name' => $theme_name, 'Theme_URI' => $theme_uri, 'Description' => $description, 'Author' => $author, 'Author_URI' => $author_uri, 'Version' => $version);
}

//From WP-Pluginsused
function pl2b_get_plugins() {
	global $wp_plugins;
	if (isset($wp_plugins)) {
		return $wp_plugins;
	}
	$wp_plugins = array();
	$plugin_root = ABSPATH.PLUGINDIR;
	$plugins_dir = @ dir($plugin_root);
	if($plugins_dir) {
		while(($file = $plugins_dir->read()) !== false) {
			if (substr($file, 0, 1) == '.') {
				continue;
			}
			if (is_dir($plugin_root.'/'.$file)) {
				$plugins_subdir = @ dir($plugin_root.'/'.$file);
				if ($plugins_subdir) {
					while (($subfile = $plugins_subdir->read()) !== false) {
						if (substr($subfile, 0, 1) == '.') {
							continue;
						}
						if (substr($subfile, -4) == '.php') {
							$plugin_files[] = "$file/$subfile";
						}
					}
				}
			} else {
				if (substr($file, -4) == '.php') {
					$plugin_files[] = $file;
				}
			}
		}
	}
	if (!$plugins_dir || !$plugin_files) {
		return $wp_plugins;
	}
	foreach ($plugin_files as $plugin_file) {
		if (!is_readable("$plugin_root/$plugin_file")) {
			continue;
		}
		$plugin_data = get_plugins_data("$plugin_root/$plugin_file");
		if (empty($plugin_data['Plugin_Name'])) {
			continue;
		}
		$wp_plugins[plugin_basename($plugin_file)] = $plugin_data;
	}
	uasort($wp_plugins, create_function('$a, $b', 'return strnatcasecmp($a["Plugin_Name"], $b["Plugin_Name"]);'));
	return $wp_plugins;
}


function pl2b_get_themes() {
	global $wp_themes;
	if (isset($wp_themes)) {
		return $wp_themes;
	}
	$wp_themes = array();
	$theme_root = WP_CONTENT_DIR.'/themes/';
	$themes_dir = @ dir($theme_root);
	if($themes_dir) {
		while(($file = $themes_dir->read()) !== false) {
			if (substr($file, 0, 1) == '.') {
				continue;
			}
			$theme_files[] = $file.'/style.css';
		}
	}
	if (!$themes_dir || !$theme_files) {
		return $wp_themes;
	}
	foreach ($theme_files as $theme_file) {
		if (!is_readable("$theme_root/$theme_file")) {
			continue;
		}
		$theme_data = get_themes_data("$theme_root/$theme_file");
		if (empty($theme_data['Theme_Name'])) {
			continue;
		}
		$wp_themes[plugin_basename($theme_file)] = $theme_data;
	}
	uasort($wp_themes, create_function('$a, $b', 'return strnatcasecmp($a["Theme_Name"], $b["Theme_Name"]);'));
	return $wp_themes;
}


//print_r(pl2b_get_themes());exit;

function create_plugin_cat(){
	$terms['name'] = 'Plugin';
	$terms['slug'] = 'pluginlinks';
	$terms['description'] = 'Plugins\' Authors\' Links';
	$terms['cat_ID'] = '';
	$terms['action'] = 'add-link-cat';
	$return = wp_insert_term('Plugin Link', 'link_category', $terms );
	return $return['term_id'];
}

function create_theme_cat(){
	$terms['name'] = 'Theme';
	$terms['slug'] = 'themelinks';
	$terms['description'] = 'Themes\' Authors\' Links';
	$terms['cat_ID'] = '';
	$terms['action'] = 'add-link-cat';
	$return = wp_insert_term('Theme Link', 'link_category', $terms );
	return $return['term_id'];
}


function pl2b_plugin($newcat = 1){	
	$plugins = pl2b_get_plugins();
	if(is_array($plugins)){
		if($newcat == 1){
			$catid = create_plugin_cat();
		}
		$exists = array();
		foreach($plugins as $plugin){
			if(!in_array($plugin['Author_URI'],$exists)){
				$linkdata['link_name'] = $plugin['Author'];
				$linkdata['link_url'] = $plugin['Author_URI'];
				$linkdata['link_description'] = 'He supports the plugin named '.$plugin['Plugin_Name'];
				if($newcat == 1){
					$linkdata['link_category'] = array($catid);
				}else{
					
				}
				$linkdata['link_target'] = '';
				$linkdata['link_rel'] = '';
				$linkdata['friendship'] = '';
				$linkdata['geographical'] = '';
				$linkdata['family'] = '';
				$linkdata['link_image'] = '';
				$linkdata['link_rss'] = '';
				$linkdata['link_notes'] = '';
				$linkdata['link_rating'] = '0';
				$linkdata['action'] = 'add';
				$linkdata['link_visible'] = 'Y';
				require_once(ABSPATH.'wp-admin/includes/bookmark.php');
				wp_insert_link($linkdata);
				$exists[] = $plugin['Author_URI'];
			}
		}
	}
}


function pl2b_theme($newcat = 1){	
	$themes = pl2b_get_themes();
	if(is_array($themes)){
		if($newcat == 1){
			$catid = create_theme_cat();
		}
		$exists = array();
		foreach($themes as $theme){
			if($theme['Author_URI'] != ''){
				if(!in_array($theme['Author_URI'],$exists)){
					$linkdata['link_name'] = $theme['Author'];
					$linkdata['link_url'] = $theme['Author_URI'];
					$linkdata['link_description'] = 'He creates the theme named '.$theme['Theme_Name'];
					if($newcat == 1){
						$linkdata['link_category'] = array($catid);
					}else{
						
					}
					$linkdata['link_target'] = '';
					$linkdata['link_rel'] = '';
					$linkdata['friendship'] = '';
					$linkdata['geographical'] = '';
					$linkdata['family'] = '';
					$linkdata['link_image'] = '';
					$linkdata['link_rss'] = '';
					$linkdata['link_notes'] = '';
					$linkdata['link_rating'] = '0';
					$linkdata['action'] = 'add';
					$linkdata['link_visible'] = 'Y';
					require_once(ABSPATH.'wp-admin/includes/bookmark.php');
					wp_insert_link($linkdata);
					$exists[] = $theme['Author_URI'];
				}
			}
		}
	}
}


$pl2b_status = 0;
if($_GET['pl2b'] == 'createpluginlink'){
	if($_GET['pl2bnewcat'] == '1'){
		pl2b_plugin(1);
		$pl2b_status = 2;
	}else{
		pl2b_plugin(0);
		$pl2b_status = 1;
	}
}elseif($_GET['pl2b'] == 'createthemelink'){
	if($_GET['pl2bnewcat'] == '1'){
		pl2b_theme(1);
		$pl2b_status = 2;
	}else{
		pl2b_theme(0);
		$pl2b_status = 1;
	}
}


function display_pl2b(){
	add_options_page('Pluginlink2Blogroll', 'Pluginlink2Blogroll', 'manage_options', 'pluginlink2blogroll/startpage.php');
}

add_action('admin_menu', 'display_pl2b');
?>