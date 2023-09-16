<?php

defined('ABSPATH') or die('abcd commons');

function get_all_categories(){
	// Excluding hidden categories
	$data = Category::getList();
	$results = $data['results'];
	foreach ($results as $key => $category) {
		if($category->priority < 0){
			unset($results[$key]);
		}
	}
	return $results;
}
function get_user($username){
	$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = 'SELECT * FROM users WHERE username = :username';
	$st = $conn->prepare( $sql );
	$st->bindValue( ":username", $username, PDO::PARAM_STR );
	$st->execute();
	$row = $st->fetch();
	$conn = null;
	if ( $row ) return $row;
	return false;
}
function is_login(){
	if(isset( $_SESSION['username'] )){
		return true;
	} else {
		return false;
	}
}
function show_logout(){
	// Not used
	if(is_login()){
		echo '<a href="'.DOMAIN.'admin.php?action=logout"> Log out </a>';
	}
}
function get_permalink($type, $slug = '', $arrs = []){
	/*
	Usage:
	- get_permalink('game', 'super-mario');
	- get_permalink('category', 'action', ['page' => 1]);
	- get_permalink('user', 'admin', ['action' => 'edit', 'page' => 2]);
	*/
	$type = get_custom_path($type);
	$params = '';
	$lang_id = '';
	$end_slash = '';
	if(count($arrs)){
		foreach ($arrs as $key => $value) {
			if( PRETTY_URL ){
				$params .= '/'.$value;
			} else {
				$params .= '&'.$key.'='.$value;
			}
		}
		if($slug == ''){
			$params = substr($params, 1);
		}
	}
	if(PRETTY_URL && $slug){
		// Add slash in the end of url
		if (strpos($params, '.') !== false) { //true
			//
		} else { //false
			if(get_setting_value('trailing_slash')){
				if(substr($slug.$params, -1) != '/'){
					$end_slash = '/';
				}
			}
		}
		if(get_setting_value('lang_code_in_url')){
			global $lang_code;
			if(isset($lang_code)){
				$lang_id = $lang_code.'/';
			}
		}
	}
	if($type == 'game'){
		if( PRETTY_URL ){
			return DOMAIN . $lang_id.'game/' . $slug . $end_slash;
		} else {
			return DOMAIN . 'index.php?viewpage=game&slug=' . $slug . $params;
		}
	} else if($type == 'archive'){
		if( PRETTY_URL ){
			return DOMAIN . $lang_id.'archive/' . $slug . $end_slash;
		} else {
			return DOMAIN . 'index.php?viewpage=archive&slug=' . $slug . $params;
		}
	} else if($type == 'search'){
		if( PRETTY_URL ){
			return DOMAIN . $lang_id.'search/' . $slug . $params . $end_slash;
		} else {
			return DOMAIN . 'index.php?viewpage=search&key=' . $slug . $params;
		}
	} else if($type == 'category'){
		if( PRETTY_URL ){
			return DOMAIN . $lang_id.'category/' . strtolower($slug) . $params . $end_slash;
		} else {
			return DOMAIN . 'index.php?viewpage=category&slug=' . strtolower($slug) . $params;
		}
	} else if($type == 'page'){
		if( PRETTY_URL ){
			return DOMAIN . $lang_id.'page/' . $slug . $params . $end_slash;
		} else {
			return DOMAIN . 'index.php?viewpage=page&slug=' . $slug . $params;
		}
	} else {
		if( PRETTY_URL ){
			if(!$slug){
				$slug = '';
			}
			return DOMAIN . $lang_id . $type .'/' . $slug . $params . $end_slash;
		} else {
			if(!$slug){
				$slug = '';
			} else {
				$slug = '&slug='.$slug;
			}
			return DOMAIN . 'index.php?viewpage=' . $type . $slug . $params;
		}
	}
}
function get_small_thumb($game){
	$thumb = (isset($game->thumb_small) && $game->thumb_small != '' ? esc_url($game->thumb_small) : esc_url($game->thumb_2));
	if(substr($thumb, 0, 1) == '/'){
		$thumb = DOMAIN . substr($thumb, 1);
	}
	return $thumb;
}
function get_game_url($game){
	$url = esc_url($game->url);
	if(substr($url, 0, 7) == '/games/'){
		if(get_setting_value('splash')){
			$url = get_permalink('splash', $game->slug);
			return $url;
		} else {
			$url = DOMAIN . substr($url, 1);
		}
	} elseif($game->source == 'gamedistribution'){
		//GameDistributon new url
		$url .= '?gd_sdk_referrer_url='.get_permalink('game', $game->slug);
	}
	return $url;
}
function commas_to_array($str){
	return preg_split("/\,/", $str);
}
function html_purify($html_content){
	require_once ABSPATH.'vendor/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$clean_html = $purifier->purify($html_content);
	return $clean_html;
}
function esc_string($str){
	if($str == '') return $str;
	return strip_tags($str);
}
function esc_int($int){
	return (int)preg_replace('/[^0-9]/', '', $int);
}
function esc_url($str){
	return $str;
	// Pass it for now, previously using filter_var($str, FILTER_SANITIZE_URL) that are now deprecated.
}
function esc_slug($str){
	if($str == '') return $str;
	if(UNICODE_SLUG){
		return esc_unicode_slug($str);
	} else {
		// Allow unicode letters without UNICODE SLUG
		return strtolower(preg_replace('/[^\p{L}0-9_-]/u', '-', $str));
	}
}
function esc_unicode_slug($str){
	// Not actually used anymore, esc_slug() already allowing unicode letters
	return preg_replace('/[^\p{L}0-9_-]/u', '-', $str);
}
function imgResize($path, $rs_width=160, $rs_height=160, $slug = '') {
	// use admin-functions.php generate_small_thumbnail() instead of call this function directly
	$x = getimagesize($path);
	$width  = $x['0'];
	$height = $x['1'];
	switch ($x['mime']) {
	  case "image/gif":
		 $img = imagecreatefromgif($path);
		 break;
	  case "image/jpg":
	  case "image/jpeg":
		 $img = imagecreatefromjpeg($path);
		 break;
	  case "image/png":
		 $img = imagecreatefrompng($path);
		 break;
	}
	$img_base = imagecreatetruecolor($rs_width, $rs_height);
	if($x['mime'] == "image/png"){
		imageAlphaBlending($img_base, false);
		imageSaveAlpha($img_base, true);
	}
	imagecopyresampled($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);
	$path_info = pathinfo($path);
	$output = $path_info['dirname'].'/'.$slug.'-'.$path_info['filename'].'_small.'.$path_info['extension'];
	switch ($path_info['extension']) {
	  case "gif":
		 imagegif($img_base, $output);  
		 break;
	case "jpg":
	case "jpeg":
		 imagejpeg($img_base, $output);
		 break;
	  case "png":
		 imagepng($img_base, $output);
		 break;
	}
}
function imgCopy($path, $new_file, $rs_width=160, $rs_height=160) {
	$x = getimagesize($path);
	$width  = $x['0'];
	$height = $x['1'];
	switch ($x['mime']) {
	  case "image/gif":
		 $img = imagecreatefromgif($path);
		 break;
	  case "image/jpg":
	  case "image/jpeg":
		 $img = imagecreatefromjpeg($path);
		 break;
	  case "image/png":
		 $img = imagecreatefrompng($path);
		 break;
	}
	$img_base = imagecreatetruecolor($rs_width, $rs_height);
	if($x['mime'] == "image/png"){
		imageAlphaBlending($img_base, false);
		imageSaveAlpha($img_base, true);
	}
	imagecopyresampled($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);
	$path_info = pathinfo($path);
	$output = $new_file;
	switch ($path_info['extension']) {
	  case "gif":
		 imagegif($img_base, $output);  
		 break;
	case "jpg":
	case "jpeg":
		 imagejpeg($img_base, $output);
		 break;
	  case "png":
		 imagepng($img_base, $output);
		 break;
	}
}
function image_to_webp($file_path, $quality = 100, $new_file = null, $destroy_original_file = false){
	$img = null;
	$_img = getimagesize($file_path);
	$img_format;
	if(!$_img) return;
	switch ($_img['mime']) {
	  case "image/jpg":
	  case "image/jpeg":
		 $img = imagecreatefromjpeg($file_path);
		 $img_format = 'jpg';
		 break;
	  case "image/png":
		 $img = imagecreatefrompng($file_path);
		 $img_format = 'png';
		 break;
	}
	if(!$img_format){
		return false;
	}
	$file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
	if(!$new_file){
		$new_file = str_replace('.'.$file_extension, '.webp', $file_path);
	}
	if($img_format == 'png'){
		imagepalettetotruecolor($img);
		imagealphablending($img, true);
		imagesavealpha($img, true);
	}
	imagewebp($img, $new_file, $quality);
	imagedestroy($img);
	if($destroy_original_file){
		unlink($file_path);
	}
}
function webp_to_image($file_path, $quality = 100, $new_format = 'jpg', $destroy_original_file = false){
	if($new_format != 'jpg' && $new_format != 'png'){
		echo 'File format must be jpg or png';
		return;
	}
	if(pathinfo($file_path, PATHINFO_EXTENSION) != 'webp'){
		echo 'File to convert must be .webp';
		return;
	}
	$img = imagecreatefromwebp($file_path);
	if($new_format == 'png'){
		imagepng($img, str_replace('.webp', '.'.$new_format, $file_path));
	} elseif($new_format == 'jpg'){
		imagejpeg($img, str_replace('.webp', '.'.$new_format, $file_path));
	}
	if(!$img){
		return;
	}
	imagedestroy($img);
	if($destroy_original_file){
		unlink($file_path);
	}
}
// function generate_small_thumbnail($game_id){
// 	// Can be used only in request.php and dashboard.php (including plugin page.php)
// 	$game = Game::getById($game_id);
// 	if($game){
// 		$use_webp = get_setting_value('webp_thumbnail');
// 		$thumb_2 = $image_path;
// 		$output = pathinfo($thumb_2);
// 		$thumb_small = '/thumbs/'.$game->slug.'_small.'.$output['extension'];
// 		if($use_webp){
// 			$file_extension = pathinfo($thumb_2, PATHINFO_EXTENSION);
// 			$thumb_small = str_replace('.'.$file_extension, '.webp', $thumb_small);
// 			webp_resize('..'.$thumb_2, '..'.$thumb_small, 160, 160);
// 		} else {
// 			imgResize('..'.$thumb_2, 160, 160, $game->slug);
// 		}
// 		$game->thumb_small = $thumb_small;
// 		$game->update();
// 	}
// }
function webp_resize($file_path, $new_file = null, $newwidth = 160, $newheight = 160, $quality = 95){
	$file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
	if($file_extension != 'webp'){
		return;
	}
	if(!$new_file){
		$new_file = $file_path;
	}
	$_img = getimagesize($file_path);
	$width  = $_img['0'];
	$height = $_img['1'];
	$img = imagecreatefromwebp($file_path);
	$new_img = imagecreatetruecolor($newwidth, $newheight);
	imagecopyresized($new_img, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	//output
	imagewebp($new_img, $new_file, $quality);
}
function check_purchase_code(){
	return get_setting_value('purchase_code') == '' ? null : get_setting_value('purchase_code');
}
function get_admin_warning(){
	$results = [];
	if(!check_purchase_code() && !ADMIN_DEMO){
		array_push($results, 'Please provide your <b>Item Purchase code</b>. You can submit or update your Purchase code on site settings.');
	}
	if(URL_PROTOCOL == 'http://'){
		if(is_https()){
			array_push($results, 'You\'re using HTTPS but current config use HTTP, you can switch to HTTPS in Settings -> Advanced.');
		}
	}
	if(!check_writeable()){
		array_push($results, 'CloudArcade don\'t have permissions to modify files, uploaded files can\'t be saved and can\'t do backup or update. Change all folders and files CHMOD to 777 to fix this.');
	}
	if(!class_exists('ZipArchive')){
		array_push($results, '"ZipArchive" extension is missing or disabled. Can\'t do backup or update.');
	}
	if(!function_exists('curl_init')) {
		array_push($results, '"The cURL extension is missing or disabled. Please activate it in php.ini."');
	}
	if( (int)phpversion() < 7){
		array_push($results, 'You\'re using PHP v-'.phpversion().', CloudArcade is requires PHP v-7.xx');
	}
	return $results;
}
function is_https() {
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		return true;
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
		return true;
	} else {
		return false;
	}
}
function check_writeable(){
	if (is_writable('../config.php') && is_writable('../site-settings.php') && is_writable('../admin/upload.php')) {
		return true;
	} else {
		return false;
	}
}
function get_cur_url(){
	if(SUB_FOLDER && SUB_FOLDER != ''){
		return DOMAIN . substr(str_replace(SUB_FOLDER, '', $_SERVER['REQUEST_URI']), 1);
	} else {
		return DOMAIN . substr($_SERVER['REQUEST_URI'], 1);
	}
}
function get_rating($type, $game){
	if($type == '5'){
		if($game->upvote+$game->downvote > 0){
			return round(($game->upvote/($game->upvote+$game->downvote))*5);
		} else {
			return 0;
		}
	} else if($type == '5-decimal'){
		if($game->upvote+$game->downvote > 0){
			return number_format(($game->upvote/($game->upvote+$game->downvote))*5, 1);
		} else {
			return 0;
		}
	}
}
function is_user_admin($username){
	$conn = open_connection();
	$sql = "SELECT * FROM users WHERE username = :username";
	$st = $conn->prepare($sql);
	$st->bindValue(":username", $username, PDO::PARAM_STR);
	$st->execute();
	$row = $st->fetch();
	if ($row) {
		if($row['role'] === 'admin'){
			return true;
		}
	}
	return false;
}

function scan_folder($path){
	$array = [];

	$dirs = scandir( ABSPATH . $path);
	$dirs = array_diff($dirs, array('.', '..'));

	foreach ($dirs as $dir) {
		if(is_dir( ABSPATH . $path . $dir)){
			if($dir != '.' || $dir != '..'){
				array_push($array, $dir);
			}
		}
	}

	return $array;
}

function scan_files($path){
	$directory = new \RecursiveDirectoryIterator(ABSPATH.$path);
	$iterator = new \RecursiveIteratorIterator($directory);
	$files = array();
	foreach ($iterator as $info) {
		if (is_file($info->getPathname())) {
			$files[] = str_replace(ABSPATH, '', $info->getPathname());
		}
	}
	return $files;
}

function delete_files($target) {
	if(is_dir($target)){
		$files = glob( $target . '*', GLOB_MARK );
		foreach( $files as $file ){
			delete_files( $file );      
		}
		if(is_dir($target)){
			rmdir( $target );
		}
	} elseif(is_file($target)) {
		unlink( $target );  
	}
}

function do_backup($root_path, $backup_type = 'part'){
	// Backup directory and file name
	if (extension_loaded('zip') && is_login() && USER_ADMIN && !ADMIN_DEMO) {
		$backup_dir = $root_path.'/admin/backups';
		if (!file_exists($backup_dir)) {
			mkdir($backup_dir, 0755, true);
		}
		$backup_file = $_SESSION['username'].'-cloudarcade-backup-'.$backup_type.'-'.VERSION.'-'.time().'-'.generate_random_strings().'.zip';
		// Exclusions (file and directory names to exclude from backup)
		$ignore_extensions = ['zip', 'rar', '7z'];
		$exclusions = array('cloudarcade', 'private', 'cache', 'temp', 'thumbs', 'vendor', 'games', 'files', 'backups');
		if($backup_type == 'full'){
			$exclusions = array('cloudarcade', 'private', 'cache', 'temp', 'backups');
		}
		add_to_zip( $root_path, ABSPATH . 'admin/backups/'.$backup_file, $exclusions, $ignore_extensions );
	}
}

function add_to_zip($source, $destination, $ignore_folder = [], $ignore_extensions = []) {
    if (extension_loaded('zip') && is_login()) {
        if (file_exists($source)) {
            $zip = new ZipArchive();
            if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                $max_size = 20 * 1024 * 1024; // 20 MB
                if (is_dir($source)) {
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
                    foreach ($files as $file) {
                        $ignored = false;
                        foreach ($ignore_folder as $ignore) {
                            if (stripos($file, $ignore) !== false) {
                                $ignored = true;
                                break;
                            }
                        }
                        if ($ignored) {
                            continue;
                        }
                        $relativePath = str_replace('\\', '/', str_replace($source . DIRECTORY_SEPARATOR, '', $file));
                        if (is_dir($file)) {
                            if (count(glob("$file/*")) > 0) { //If folder not empty
                                $zip->addEmptyDir($relativePath . '/');
                            }
                        } else if (is_file($file)) {
                            // Ignore files larger than 20 MB
                            if (filesize($file) > $max_size) {
                                continue;
                            }
                            // Ignore archive files
                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                            if (in_array($ext, $ignore_extensions)) {
                                continue;
                            }
                            $zip->addFromString($relativePath, file_get_contents($file));
                        }
                    }
                } else if (is_file($source)) {
                    // Ignore files larger than 20 MB
                    if (filesize($source) > $max_size) {
                        return false;
                    }
                    // Ignore archive files
                    $ext = pathinfo($source, PATHINFO_EXTENSION);
                    if (in_array($ext, $ignore_extensions)) {
                        return false;
                    }
                    $zip->addFromString(basename($source), file_get_contents($source));
                }
            }
            return $zip->close();
        }
    }
    return false;
}

function generate_random_strings($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getIpAddr() {
	if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		$ipAddr = $_SERVER["HTTP_CF_CONNECTING_IP"];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ipAddr = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ipAddr = strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
	} else {
		$ipAddr = $_SERVER['REMOTE_ADDR'];
	}
	if(strlen($ipAddr) > 16){
		$ipAddr = '0.0.0.0';
	}
	return $ipAddr;
}

function get_user_avatar($username = null){
	global $login_user;
	$user;
	if(!$username){
		if($login_user){
			$username = $login_user->username;
			$user = $login_user;
		}
	} else {
		$cur_user = User::getByUsername($username);
		if($cur_user){
			$user = $cur_user;
		}
	}
	if($user){
		if(file_exists(ABSPATH.'images/avatar/'.$username.'.png')){
			return DOMAIN.'images/avatar/'.$username.'.png';
		} elseif($user->avatar){
			return DOMAIN.'images/avatar/default/'.$user->avatar.'.png';
		}
	}
	return DOMAIN.'images/default_profile.png';
}

$lang_data = [];

function load_language($type){
	global $lang_data;
	global $language_file_exist;
	$file = '';
	if($type === 'index'){
		$lang = get_setting_value('language');
		if(isset($_GET['lang'])){
			// Set dynamic language
			if(strlen($_GET['lang']) <= 3){
				setcookie('lang', $_GET['lang'], strtotime('+3 months'), '/');
				$lang = $_GET['lang'];
			}
		}
		if(isset($_COOKIE['lang']) && !isset($_GET['lang'])){
			// Load saved dynamic language
			$lang = $_COOKIE['lang'];
		}
		$file = TEMPLATE_PATH.'/locales/'.$lang.'.json';
		if(!file_exists($file)){
			if(isset($_COOKIE['lang']) && !isset($_GET['lang'])){
				// Language selected is not exist anymore, the remove cookie data
				// To avoid developer confusion
				setcookie('lang', '', time() - 3600, '/');
			}
		}
	} elseif($type === 'admin'){
		$file = ABSPATH.'locales/'.get_setting_value('language').'.json';
	}
	if(file_exists($file)){
		$lang_data = json_decode(file_get_contents($file), true);
	}
}

function translate($str, $val1 = null, $val2 = null){
	global $lang_data;
	$translated = $str;
	if(isset($lang_data[$str])){
		$translated = $lang_data[$str];
	}
	if(!is_null($val1)){
		$translated = str_replace('%a', $val1, $translated);
	}
	if(!is_null($val2)){
		$translated = str_replace('%b', $val2, $translated);
	}
	return $translated;
}

function _t($str, $val1 = null, $val2 = null){
	return translate($str, $val1, $val2);
}

function _e($str, $val1 = null, $val2 = null){
	echo translate($str, $val1, $val2);
}

function get_base_taxonomy($page_name){
	// Better naming compared to get_custom_path()
	$custom_path_data = get_setting_value('custom_path');
	if(!empty($custom_path_data)){
		if(isset($custom_path_data[$page_name])){
			return $custom_path_data[$page_name];
		}
	}
	return $page_name;
}

function get_custom_path($base_name){
	// Changed in v1.6.2
	// Replacing convert_to_custom_path()
    $custom_path_data = get_setting_value('custom_path');
    if (!empty($custom_path_data)) {
    	$custom_name = array_search($base_name, $custom_path_data);
		if($custom_name){
			return $custom_name;
		}
    }
    return $base_name;
}

function convert_to_custom_path($page_name){
	// Deprecated since v1.6.2
	global $options;
	if(isset($options['custom_path']) && $options['custom_path']){
		$custom_name = array_search($page_name, $options['custom_path']);
		if($custom_name){
			return $custom_name;
		}
	}
	return $page_name;
}

function str_encrypt($str, $key){
	$cipher = "AES-128-CTR";
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = '1234567891011121';
	return openssl_encrypt($str, $cipher, $key, $options=0, $iv);
}

function str_decrypt($str, $key){
	$cipher = "AES-128-CTR";
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = '1234567891011121';
	return openssl_decrypt($str, $cipher, $key, $options=0, $iv);
}

function show_alert($message, $type, $btn = true){
	echo '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">'._t($message);
	if($btn){
		echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	}
	echo '</div>';
}

function get_option($name){
	// Deprecated since v1.5.7, use get_pref() instead
	global $conn;
	$sql = "SELECT * FROM prefs WHERE name = :name";
	$st = $conn->prepare($sql);
	$st->bindValue(':name', $name, PDO::PARAM_STR);
	$st->execute();
	$row = $st->fetch();
	if($row){
		return $row['value'];
	} else {
		return null;
	}
}

function update_option($name, $value){
	// Deprecated since v1.5.7, use set_pref() instead
	global $conn;
	$sql = "SELECT id FROM prefs WHERE name = :name";
	$st = $conn->prepare($sql);
	$st->bindValue(':name', $name, PDO::PARAM_STR);
	$st->execute();
	$row = $st->fetch();
	if($row){
		$sql = "UPDATE prefs SET value = :value WHERE name = :name";
		$st = $conn->prepare($sql);
		$st->bindValue(':value', $value, PDO::PARAM_STR);
		$st->bindValue(':name', $name, PDO::PARAM_STR);
		$st->execute();
	} else {
		$sql = "INSERT INTO prefs (name, value) VALUES (:name, :value)";
		$st = $conn->prepare($sql);
		$st->bindValue(':value', $value, PDO::PARAM_STR);
		$st->bindValue(':name', $name, PDO::PARAM_STR);
		$st->execute();
	}
}

function get_pref($name){
	// Alternative for get_option()
	// Reason: better naming
	global $conn;
	$sql = "SELECT * FROM prefs WHERE name = :name";
	$st = $conn->prepare($sql);
	$st->bindValue(':name', $name, PDO::PARAM_STR);
	$st->execute();
	$row = $st->fetch();
	if($row){
		return $row['value'];
	} else {
		// Return null if key doesnt exist
		return null;
	}
}

function get_pref_bool($name){
	// Return boolean value
	// Only for "true" or "false" value
	$value = get_pref($name);
	if(is_null($value)){
		// The key is not exist
		return false;
	} else {
		if($value == 'true'){
			return true;
		} else {
			return false;
		}
	}
}

function set_pref($name, $value){
	// Alternative for update_option()
	// Reason: better naming
	global $conn;
	$sql = "SELECT id FROM prefs WHERE name = :name";
	$st = $conn->prepare($sql);
	$st->bindValue(':name', $name, PDO::PARAM_STR);
	$st->execute();
	$row = $st->fetch();
	if($row){
		$sql = "UPDATE prefs SET value = :value WHERE name = :name";
		$st = $conn->prepare($sql);
		$st->bindValue(':value', $value, PDO::PARAM_STR);
		$st->bindValue(':name', $name, PDO::PARAM_STR);
		$st->execute();
	} else {
		$sql = "INSERT INTO prefs (name, value) VALUES (:name, :value)";
		$st = $conn->prepare($sql);
		$st->bindValue(':value', $value, PDO::PARAM_STR);
		$st->bindValue(':name', $name, PDO::PARAM_STR);
		$st->execute();
	}
}

function register_sidebar( $args = array() ){
	global $registered_sidebars;

	$i = count( $registered_sidebars ) + 1;

	$id_is_empty = empty( $args['id'] );

	$defaults = array(
		'name'           => 'Sidebar X',
		'id'             => "sidebar-$i",
		'description'    => '',
	);

	$sidebar = merge_args($args, $defaults);

	$registered_sidebars[ $sidebar['id'] ] = $sidebar;

	return $sidebar['id'];
}

function merge_args($args, $defaults = array()){
	foreach ($args as $key => $value) {
		$defaults[$key] = $value;
	}
	return $defaults;
}

function widget_aside($name, $args = array()){
	global $stored_widgets;
	global $registered_sidebars;
	if(isset($registered_sidebars[$name])){
		if(isset($stored_widgets[$name])){
			$list = $stored_widgets[$name];
			if(count($list)){
				foreach ($list as $item) {
					$key = $item['widget'];
					$widget;
					if(widget_exists($item['widget'])){
						$widget = get_widget( $item['widget'], $item );
					} else {
						continue;
					}
					$widget->widget( $item );
				}
			}
		}
	}
}

function render_nav_children($array_menu, $args){
	$defaults = array(
		'no_ul'				=> false,
		'ul_id'				=> '',
		'ul_class'			=> 'dropdown-menu',
		'li_id'				=> '',
		'li_class'			=> 'nav-item-child',
		'a_class'			=> 'nav-link-child',
	);
 
	$args = merge_args( $args, $defaults );

	if(count($array_menu)){
		if(!$args['no_ul']){
			echo '<ul role="menu" ';
			echo !empty($args['ul_id']) ? ' id="'.$args['ul_id'].'"' : '';
			echo !empty($args['ul_class']) ? ' class="'.$args['ul_class'].'"' : '';
			echo '>';
		}
		foreach($array_menu as $menu){
			echo '<li';
			echo !empty($args['li_id']) ? ' id="'.$args['li_id'].'"' : '';
			echo !empty($args['li_class']) ? ' class="'.$args['li_class'].'"' : '';
			echo '>';
			echo '<a class="'.$args['a_class'].'" href="'.$menu['url'].'">';
			echo $menu['label'];
			echo '</a>';
			echo '</li>';
		}
		if(!$args['no_ul']){
			echo '</ul>';
		}
	}
}

function render_nav_menu($name = 'top_nav', $args = array()){
	$defaults = array(
		'container'			=> '',
		'container_id'		=> '',
		'container_class'	=> '',
		'no_ul'				=> false,
		'ul_id'				=> '',
		'ul_class'			=> '',
		'li_id'				=> '',
		'li_class'			=> 'nav-item',
		'li_class_parent'	=> 'nav-item-parent',
		'a_class'			=> 'nav-link',
		'a_class_parent'	=> '',
		'after_parent'		=> '<i class="dropdown-icon fa fa-caret-down"></i>',
		'bs-5'				=> false,
		'children'			=> array(),
	);
 
	$args = merge_args( $args, $defaults );

	$array_menu = nav_menu_array($name);

	if(count($array_menu)){
		if($args['container'] != ''){
			echo '<'.$args['container'];
			echo !empty($args['container_id']) ? ' id="'.$args['container_id'].'"' : '';
			echo !empty($args['container_class']) ? ' class="'.$args['container_class'].'"' : '';
			echo '>';
		}
		if(!$args['no_ul']){
			echo '<ul';
			echo !empty($args['ul_id']) ? ' id="'.$args['ul_id'].'"' : '';
			echo !empty($args['ul_class']) ? ' class="'.$args['ul_class'].'"' : '';
			echo '>';
		}
		foreach($array_menu as $menu){
			$parent_class = '';
			if(isset($menu['children'])){
				$parent_class = !empty($args['li_class_parent']) ? $args['li_class_parent'] : '';
			}
			echo '<li';
			echo !empty($args['li_id']) ? ' id="'.$args['li_id'].'"' : '';
			echo !empty($args['li_class']) ? ' class="'.$args['li_class'].' '.$parent_class.'"' : '';
			echo '>';
			if(isset($menu['children'])){
				$menu['url'] = '#';
			}
			$a_class_parent = '';
			if(isset($menu['children'])){
				$a_class_parent = $args['a_class_parent'];
			}
			echo '<a class="'.$args['a_class'].' '.$a_class_parent.'" href="'.$menu['url'].'"';
			if(isset($menu['children'])){
				if($args['bs-5']){
					echo ' data-bs-toggle="dropdown"';
				} else {
					echo ' data-toggle="dropdown"';
				}
			}
			echo '>';
			echo $menu['label'];
			if(isset($menu['children'])){
				echo $args['after_parent'];
			}
			echo '</a>';
			if(isset($menu['children'])){
				render_nav_children($menu['children'], $args['children']);
			}
			echo '</li>';
		}
		if(!$args['no_ul']){
			echo '</ul>';
		}
		if($args['container'] != ''){
			echo '</'.$args['container'].'>';
		}
	}
}
function nav_get_children($name, $parent_id = 0){
	global $conn;
	$items = [];
	$sql = "SELECT * FROM menus WHERE parent_id = :parent_id AND name = :name ORDER BY id ASC";
	$st = $conn->prepare($sql);
	$st->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->execute();
	$result = $st->fetchAll(PDO::FETCH_ASSOC);
	if (count($result)) {
		foreach ($result as $row) {
			$child = nav_get_children($name, $row['id']);
			if($child){
				$row['children'] = $child;
			}
			$items[] = $row;
		}
	} else {
		$items = [];
	}
	return $items;
}
function nav_menu_array($name = 'top_nav'){
	return nav_get_children($name, 0);
}

function get_template_path(){
	return DOMAIN . TEMPLATE_PATH;
}

function get_category_icon($slug, $array = []){
	foreach ($array as $key => $item) {
		foreach ($item as $child) {
			if($child == $slug){
				return $key;
			}
		}
	}
	return 'other';
}

function is_favorited_game($game_id){
	// Check if a game is favorited by current user
	global $login_user;
	global $conn;
	if($login_user){
		$conn = open_connection();
		$sql = "SELECT * FROM favorites WHERE user_id = :user_id AND game_id = :game_id LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":user_id", $login_user->id, PDO::PARAM_INT);
		$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch(PDO::FETCH_ASSOC);
		if($row){
			return true;
		} else {
			return false;
		}
	}
	return null;
}

function format_number_abbreviated($number) {
	if($number >= 1000){
		return substr($number, 0, -3).'k';
	}
	return $number;
}

function get_tags($sort = 'random', $limit = 20){
	global $conn;
	$_sort;
	if($sort == 'name'){
		$_sort = 'tags.name ASC';
	} else if($sort == 'usage'){
		$_sort = 'tags.usage_count DESC';
	} else {
		$_sort = 'RAND()';
	}
	$conn = open_connection();
	$sql = 'SELECT name FROM tags
    ORDER BY '.$_sort.'
    LIMIT :limit';
	$st = $conn->prepare($sql);
	$st->bindValue(':limit', $limit, PDO::PARAM_INT);
	$st->execute();
	$tag_names = $st->fetchAll(PDO::FETCH_COLUMN);
	return $tag_names;
}
function get_tag_usage($name){
	global $conn;
	$conn = open_connection();
	$sql = 'SELECT usage_count FROM tags
    WHERE name = :name LIMIT 1';
	$st = $conn->prepare($sql);
	$st->bindValue(':name', $name, PDO::PARAM_STR);
	$st->execute();
	$count = $st->fetch(PDO::FETCH_ASSOC);
	return $count['usage_count'];
}
function get_setting_value($name){
	if(isset(SETTINGS[$name])){
		return SETTINGS[$name]['value'];
	}
	throw new Exception("Key does not exist = ".$name);
}

function get_setting($name){
	if(isset(SETTINGS[$name])){
		return SETTINGS[$name];
	}
	throw new Exception("Key does not exist = ".$name);
}

function is_valid_json($json) {
    json_decode($json);
    return (json_last_error() === JSON_ERROR_NONE);
}

function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    $isValid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    // Regenerate the token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $isValid;
}

function get_content_translation($content_type, $content_id, $language, $field = 'all') {
	// Sample usage : get_content_translation('game', 1, 'en', 'title');
	$conn = open_connection();
	if ($field === 'all') {
		$sql = "SELECT field, translation FROM translations WHERE content_type = :content_type AND content_id = :content_id AND language = :language";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
		$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
		$stmt->bindParam(':language', $language, PDO::PARAM_STR);
	} else {
		$sql = "SELECT translation FROM translations WHERE content_type = :content_type AND content_id = :content_id AND language = :language AND field = :field";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
		$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
		$stmt->bindParam(':language', $language, PDO::PARAM_STR);
		$stmt->bindParam(':field', $field, PDO::PARAM_STR);
	}
	$stmt->execute();
	if ($field === 'all') {
		$translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // This will fetch the results in key-value pairs ['title' => 'Translation of title', 'description' => 'Translation of description']
		return $translations;
	} else {
		$translation = $stmt->fetchColumn();
		return $translation === false ? null : $translation; // Will return null if no result found
	}
}

function has_content_translation($content_type, $content_id, $language = null, $specific_field = 'all') {
    $conn = open_connection();
    $sql = "SELECT 1 FROM translations WHERE content_type = :content_type AND content_id = :content_id";
    if ($language !== null) {
        $sql .= " AND language = :language";
    }
    if ($specific_field !== 'all') {
        $sql .= " AND field = :field";
    }
    $sql .= " LIMIT 1";  // Added LIMIT 1 for better performance
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
    $stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
    if ($language !== null) {
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
    }
    if ($specific_field !== 'all') {
        $stmt->bindParam(':field', $specific_field, PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchColumn() !== false;
}

?>