<?php

define( "ADMIN_DEMO", false );

require( 'sub-folder.php' );

define('SETTINGS', fetch_settings_data()); // SETTINGS is a replacement for $options

function fetch_settings_data(){
	if(defined('SETTINGS')){
		return SETTINGS;
	} else {
		$conn = open_connection();
		$sql = "SELECT * FROM settings";
		$st = $conn->prepare($sql);
		$st->execute();
		$rows = $st->fetchAll(PDO::FETCH_ASSOC);
		$assoc_array = []; // Convert to associative array
		foreach ($rows as $item) {
			if($item['name'] == 'custom_path'){
				if($item['value'] != ''){
					$item['value'] = json_decode($item['value'], true);
				} else {
					$item['value'] = [];
				}
			}
			if($item['type'] == 'bool' || $item['type'] == 'number'){
				$item['value'] = (int)$item['value'];
			}
			$assoc_array[$item['name']] = $item;
		}
		return $assoc_array;
	}
}

$options = [];

foreach (SETTINGS as $key => $value) {
	// Compatibility mode
	// $options is dropped, but many themes still depend on it
	if($value['type'] == 'bool'){
		$str_bool = 'false';
		if($value['value']){
			$str_bool = 'true';
		}
		$options[$key] = $str_bool;
	} else {
		$options[$key] = $value['value'];
	}
}
unset($options['purchase_code']);

if(ADMIN_DEMO){
	// Allow dynamic theme
	$theme = 'arcade-one';
	if(isset($_GET['theme'])){
		$filtered_theme_dir = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['theme']);
		$json_path = ABSPATH . 'content/themes/' . $filtered_theme_dir . '/info.json';
		if(file_exists( $json_path )){
			$theme = $_GET['theme'];
			$_SESSION['theme'] = $_GET['theme'];
		}
	} elseif(isset($_SESSION['theme'])){
		$theme = $_SESSION['theme'];
	}
	$options['theme_name'] = $theme;
}

$www = '';
if(defined('IS_VISITOR_PAGE') && SETTINGS['use_www']['value']){
	// www only work in visitor page and will be ignored in the admin panel
	// this will prevent admin panel error (false configuration from user)
	if(substr($_SERVER['SERVER_NAME'], 0, 4) != 'www.'){
		$www = 'www.';
	}
}

define( "PRETTY_URL", SETTINGS['pretty_url']['value'] );
$url_protocol = 'http://';
if(SETTINGS['use_https']['value']){
	$url_protocol = 'https://';
}
define( "URL_PROTOCOL", $url_protocol );
define( "DOMAIN", URL_PROTOCOL . $www . $_SERVER['SERVER_NAME'] . get_domain_port() . '/' . SUB_FOLDER );
define( "SITE_DOMAIN", $_SERVER['SERVER_NAME'] );

// if($options['custom_path']){
// 	$options['custom_path'] = json_decode($options['custom_path'], true);
// }

function get_domain_port(){
	//Used for localhost with port
	$port = $_SERVER['SERVER_PORT'];
	if($port && $port === '8080'){
		return ':'.$port;
	} else {
		return '';
	}
}

function load_site_settings(){
	// Deprecated since v1.6.2
	$conn = open_connection();
	$sql = "SELECT * FROM options";
	$st = $conn->prepare($sql);
	$st->execute();
	$row = $st->fetchAll();
	$opt = array();
	foreach ($row as $item) {
		$opt[$item['name']] = $item['value'];
	}
	return $opt;
}

?>