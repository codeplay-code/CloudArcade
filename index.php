<?php

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

define('IS_VISITOR_PAGE', true);

if(file_exists('static') && !defined('NO_STATIC')){
	if(file_exists('index_static.php')){
		require_once('index_static.php');
		exit();
	}
}

require( 'config.php' );
require( 'init.php' );
require( 'classes/Collection.php' );
require( 'includes/plugin.php' );

$_wgts = get_pref('widgets');
$_wgts = ($_wgts) ? json_decode($_wgts, true) : [];
$stored_widgets = $_wgts;

$lang_code = get_setting_value('language');
$lang_code_url = null; // Store language ID in url
$url_params = [];
if (PRETTY_URL && isset($_GET['viewpage']) == 'search' && strpos($_SERVER['REQUEST_URI'], '?viewpage=search')) {
	// If search page with query string URL
	// Then redirect to pretty url version
	header('Location: '.get_permalink('search', $_GET['slug']), true, 301);
	exit();
}
if(PRETTY_URL){
	$url_params = array_values(array_filter(explode('/', urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)))));
} else {
	if (isset($_GET['viewpage'])) {
		$url_params = array_values(array_filter(array_map('trim', $_GET)));
	}
}
if(SUB_FOLDER != ""){
	// Is using sub-folder
	$fname = str_replace("/", "", SUB_FOLDER);
	if(isset($url_params[0]) && $url_params[0] == $fname){
		array_shift($url_params);
	}
}

// BEGIN MULTI-LANGUAGE
if (array_key_exists('lang', $_GET)) {
	// Switch language with ?lang=en parameter
	$lang_code = $_GET['lang'];
}
$language_file_exist = true; // Set false if lang file not exist
$lang_url_enabled = get_setting_value('lang_code_in_url');
if($lang_url_enabled && PRETTY_URL){
	// Put language ID on url
	// example: domain.com/en/game
	if (!array_key_exists('lang', $_GET)) {
		$lang_code = isset($url_params[0]) ? $url_params[0] : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'en');
	}
	if (!preg_match('/^[a-z]{2}$/', $lang_code) || empty($url_params)) {
		// If url doesn't contain language ID on it's url
		// or current url is home page
		// then redirect to a new url (cur url) that contain language ID
		$is_search = (isset($url_params[1]) && $url_params[1] == 'search') ? true : false;
		if(!$is_search){
			// Exception for search page
			$lang_code = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : get_setting_value('language');
			$redirect_url = DOMAIN . "$lang_code{$_SERVER['REQUEST_URI']}";
			if(empty($url_params)){
				// Is home page
				if(get_setting_value('trailing_slash')){
					// If trailing slash is activated
				} else {
					if(substr($redirect_url, -1) == '/'){
						$redirect_url = substr($redirect_url, 0, -1);
					}
				}
				// Home page will be domain.com/en/
				// If trailing_slash is inactive, then '/' in the last character of the url will be removed
			}
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $redirect_url");
			exit();
		}
	} else {
		// url contain language ID
		$lang_code_url = $url_params[0];
	}
	if(isset($url_params[0]) && $url_params[0] == $lang_code){
		// Remove language ID from url array
		array_shift($url_params);
	}
	$file = TEMPLATE_PATH . '/locales/' . $lang_code . '.json';
	if (!file_exists($file) && $lang_code != 'en') {
		// Language file does not exist
		$lang_code = 'en';
		$language_file_exist = false;
	}
	$_GET['lang'] = $lang_code;
}
// END

if (PRETTY_URL) {
	$_GET['viewpage'] = isset($url_params[0]) ? $url_params[0] : 'homepage';
	if(isset($url_params[1])) {
		$_GET['slug'] = $url_params[1];
	}
	if(get_setting_value('trailing_slash')){
		// If trailing slash is activated
		if(count($url_params)){
			$cur_url = $_SERVER['REQUEST_URI'];
			if(substr($cur_url, -1) != '/' && !strpos($cur_url, '?')){
				// Add trailing slash, then redirect
				header('Location: '.substr(DOMAIN, 0, -1).$cur_url.'/', true, 301);
				exit();
			}
		}
	}
}

load_language('index');
load_plugins('index');

$page_name = isset( $_GET['viewpage'] ) ? $_GET['viewpage'] : 'homepage';

$base_taxonomy = get_base_taxonomy($page_name);
$custom_path = $base_taxonomy;

if ($base_taxonomy == $page_name && $page_name != get_custom_path($base_taxonomy)) {
    // Visitor is accessing old base_taxonomy, redirect to custom path
    $new_url = get_permalink('404'); // Default to 404
    if($base_taxonomy != 'login'){
    	switch (count($url_params)) {
	        case 1:
	            $new_url = get_permalink($page_name);
	            break;
	        case 2:
	            $new_url = get_permalink($page_name, $url_params[1]);
	            break;
	        default:
	            if (count($url_params) >= 3) {
	                $arrs = [];
	                for ($i = 2; $i < count($url_params); $i++) {
	                    $key = "param" . ($i - 1);
	                    $arrs[$key] = $url_params[$i];
	                }
	                $new_url = get_permalink($page_name, $url_params[1], $arrs);
	            }
	            break;
	    }
    }  
    header('Location: ' . $new_url, true, 301);
    exit();
}

if($base_taxonomy == 'search'){
	if(PRETTY_URL){
		if(isset($_GET['slug']) && strpos($_SERVER['REQUEST_URI'], 'index.php?viewpage=search')){
			header('Location: '.get_permalink('search', $_GET['slug']), true, 301);
			exit();
		}
	}
}

require_once( ABSPATH.'content/themes/theme-functions.php' );
require_once( TEMPLATE_PATH . '/functions.php' );

if($lang_url_enabled && PRETTY_URL){
	if(!$language_file_exist && ($lang_code_url != 'en')){
		// Language file requested in url is not exist
		// Show 404 page
		require( 'includes/page-404.php' );
		exit();
	}
}

if(file_exists( 'includes/page-' . $base_taxonomy . '.php' )){
	require( 'includes/page-' . $base_taxonomy . '.php' );
} else {
	if(file_exists( TEMPLATE_PATH.'/page-' . $page_name . '.php' )){
		require( TEMPLATE_PATH.'/page-' . $page_name . '.php' );
	} else {
		require( 'includes/page-404.php' );
	}
}

?>