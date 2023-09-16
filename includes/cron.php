<?php

if(!defined('CRON')){
	die('p');
}

$data = get_pref('cron-job');

define("LIMIT", 3);
$game_count = 0;
$log_txt = "";

if(!is_null($data)){
	$data = json_decode($data, true);
	if(isset($data['auto-post'])){
		$task_date = $data['auto-post']['date'];
		$cur_date = date("Y-m-d H:i:s");
		if($cur_date >= $task_date){
			$datetime1 = date_create($cur_date);
			$datetime2 = date_create($task_date);
			$interval = date_diff($datetime1, $datetime2);
			$diff = $interval->format('%d');

			if($diff < 4){
				$new_task_date = date('Y-m-d H:i:s', strtotime('+8 hours', strtotime(date('Y-m-d H:i:s'))));
				$data['auto-post']['date'] = $new_task_date;
				update_option('cron-job', json_encode($data));
				auto_add_games($data);
			} else { //More than 4 days inactive
				echo 'remove';
				unset($data['auto-post']);
				update_option('cron-job', json_encode($data));
			}
		} else {
			if(!defined('CRON')){
				echo 'on the way';
			}
		}
	} else {
		//Inactive
	}
}

function auto_add_games($data){
	if(!ADMIN_DEMO){
		add_to_log();
		$data['auto-post']['last-status'] = 'null';
		$url = 'https://api.cloudarcade.net/fetch-auto.php?action=fetch&code='. check_purchase_code();
		$url .= '&data='.json_encode($data['auto-post']['list']);
		$url .= '&ref='.DOMAIN.'&v='.VERSION;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$curl = curl_exec($ch);
		curl_close($ch);
		$game_data = json_decode($curl, true);
		if(isset($game_data['error'])){
			add_to_log('Failed auto add games: '.$curl);
			echo 'failed auto add games.';
		} else if($game_data){
			foreach ($game_data as $a => $b) {
				foreach ($b as $item) {
					$item['tags'] = '';
					x_add_game2($item);
				}
			}
		} else {
			add_to_log('Failed auto add games');
			echo 'failed auto add games.';
		}
		write_log();
	}
}

function x_add_game2($data){
	$_POST = $data;
	// Copied from request.php add_game()
	$ref = '';
	if(isset($_POST['ref'])) $ref = $_POST['ref'];
	$_POST['description'] = html_purify($_POST['description']);
	$_POST['instructions'] = html_purify($_POST['instructions']);
	if($_POST['source'] == 'self' || $_POST['source'] == 'remote'){
		if(!isset($_POST['published'])){
			$_POST['published'] = false;
		}
	}
	$redirect = 0;
	if(isset($_POST['redirect'])){
		$redirect = $_POST['redirect'];
	}
	if(isset($_POST['slug'])){
		$slug = esc_slug($_POST['slug']);
	} else {
		$slug = esc_slug(strtolower(str_replace(' ', '-', $_POST["title"])));
	}
	$_POST['slug'] = $slug;
	if(is_array($_POST['category'])){
		// Array category is not allowed
		// Convert to string
		$cats = '';
		$i = 0;
		$total = count($_POST['category']);
		foreach ($_POST['category'] as $key) {
			$cats = $cats.$key;
			if($i < $total-1){
				$cats = $cats.',';
			}
			$i++;
		}
		$_POST['category'] = $cats;
	}
	if($_POST['category'] == '' || $_POST['category'] == ' '){
		$_POST['category'] = 'Other';
	}
	// Begin category filter
	if(file_exists(ABSPATH."content/plugins/category-filter")){
		// Plugin exist
		$cats = '';
		$categories = commas_to_array($_POST['category']);
		$i = 0;
		$total = count($categories);
		foreach ($categories as $key) {
			$cats = $cats.category_name_filtering($key);
			if($i < $total-1){
				$cats = $cats.',';
			}
			$i++;
		}
		$_POST['category'] = $cats;
	}
	$game = new Game;
	$check=$game->getBySlug($slug);
	$status='failed';
	if(is_null($check)){
		if($ref != 'upload'){
			if(IMPORT_THUMB){
				// Check if webp is activated
				$use_webp = get_pref('webp-thumbnail');
				import_thumb($_POST['thumb_2'], $slug);
				$name = basename($_POST['thumb_2']);
				$_POST['thumb_2'] = '/thumbs/'.$slug.'-'.$name;
				if($use_webp){
					$file_extension = pathinfo($_POST['thumb_2'], PATHINFO_EXTENSION);
					$_POST['thumb_2'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_2']);
				}
				//
				import_thumb($_POST['thumb_1'], $slug);
				$name = basename($_POST['thumb_1']);
				$_POST['thumb_1'] = '/thumbs/'.$slug.'-'.$name;
				if($use_webp){
					$file_extension = pathinfo($_POST['thumb_1'], PATHINFO_EXTENSION);
					$_POST['thumb_1'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_1']);
				}
				if( SMALL_THUMB ){
					$output = pathinfo($_POST['thumb_2']);
					$_POST['thumb_small'] = '/thumbs/'.$slug.'-'.$output['filename'].'_small.'.$output['extension'];
					if($use_webp){
						$file_extension = pathinfo($_POST['thumb_2'], PATHINFO_EXTENSION);
						$_POST['thumb_small'] = str_replace('.'.$file_extension, '.webp', $_POST['thumb_small']);
						webp_resize(substr($_POST['thumb_2'], 1), substr($_POST['thumb_small'], 1), 160, 160);
					} else {
						imgResize(substr($_POST['thumb_2'], 1), 160, 160, $slug);
					}
				}
			}
		}
		$game->storeFormValues( $_POST );
		$game->insert();
		$status='added';
		//
		$cats = commas_to_array($_POST['category']);
		if(is_array($cats)){ //Add new category if not exist
			$length = count($cats);
			for($i = 0; $i < $length; $i++){
				$_POST['name'] = $cats[$i];
				$category = new Category;
				$exist = $category->isCategoryExist($_POST['name']);
				if($exist){
				  //
				} else {
					unset($_POST['slug']);
					$_POST['description'] = '';
					$category->storeFormValues( $_POST );
					$category->insert();
				}
				$category->addToCategory($game->id, $category->id);
			}
		}
		add_to_log('Game added - '.$_POST['source'].' - '.$slug);
	}
	else{
		add_to_log('Game alredy exist - '.$_POST['source'].' - '.$slug);
		$status='exist';
	}
}
function category_name_filtering($category_name){
	// Specific function for "Category Filter" plugin
	if(true){
		$json = get_pref("category-filter");
		if($json){
			$data = json_decode($json, true);
			foreach ($data as $key => $value) {
				if($key == $category_name){
					return $value;
				}
			}
		}
	}
	return $category_name;
}
function import_thumb($url, $game_slug){
	if($url) {
		if (!file_exists('thumbs')) {
			mkdir('thumbs', 0777, true);
		}
		$name = basename($url);
		$new = 'thumbs/'.$game_slug.'-'.$name;
		if( get_pref('webp-thumbnail') ){
			// Using WEBP format
			$file_extension = pathinfo($url, PATHINFO_EXTENSION);
			$new = str_replace('.'.$file_extension, '.webp', $new);
			image_to_webp($url, 85, $new);
		} else {
			compressImage($url, $new , COMPRESSION_LEVEL);
		}
	}
}
function compressImage($source, $destination, $quality) {
	$info = getimagesize($source);
	if ($info['mime'] == 'image/jpeg') 
	$image = imagecreatefromjpeg($source);
	elseif ($info['mime'] == 'image/gif') 
	$image = imagecreatefromgif($source);
	elseif ($info['mime'] == 'image/png') 
	$image = imagecreatefrompng($source);

	if ($info['mime'] == 'image/png'){
		imageAlphaBlending($image, true);
		imageSaveAlpha($image, true);
		imagepng($image, $destination, 9);
	} else {
		imagejpeg($image, $destination, $quality);
	}
}
function add_to_log($msg = ""){
	global $log_txt;
	if($msg == ""){
		$log_txt .= "---- Executed - ".date('Y-m-d H:i:s');
	} else {
		$log_txt .= $msg;
	}
	$log_txt .= PHP_EOL;
}
function write_log(){
	global $log_txt;
	if($log_txt != ""){
		$path = ABSPATH . PLUGIN_PATH . '/auto-publish';
		if(file_exists($path . '/log.txt')){
			$filesizeKB = filesize($path . '/log.txt') / 1024;
			if($filesizeKB >= 50){
				file_put_contents($path . '/log_prev.txt', file_get_contents($path . '/log.txt'));
				unlink($path . '/log.txt');
			}
		}
		if(file_exists($path)){
			$full_log = "";
			if(file_exists($path . '/log.txt')){
				$full_log = file_get_contents($path . '/log.txt');
			}
			$full_log = $log_txt.$full_log;
			file_put_contents($path . '/log.txt', $full_log);
		}
	}
}

?>