<?php

// Functions for Admin Panel

if(!USER_ADMIN){
	die('Forbidden');
}

function get_setting_group($category){
	// $conn = open_connection();
	// $sql = "SELECT * FROM settings WHERE category = :category";
	// $st = $conn->prepare($sql);
	// $st->bindValue('category', $category, PDO::PARAM_STR);
	// $st->execute();
	// $rows = $st->fetchAll(PDO::FETCH_ASSOC);
	// return $rows;
	$group = [];
	foreach (SETTINGS as $item) {
		if($item['category'] == $category){
			$group[] = $item;
		}
	}
	return $group;
}

function update_setting($name, $value){
	// Migrated, replacing update_settings()
	$this_setting = get_setting($name);
	// Validating data type
	if($this_setting['type'] == 'bool'){
		if($value == 1 || $value == 0){
			//
		} else {
			die('Type not valid');
		}
	} else if($this_setting['type'] == 'number'){
		if(!is_numeric($value)){
			die('Type not valid');
		}
	}
	$conn = open_connection();
	$sql = "UPDATE settings SET value = :value WHERE name = :name LIMIT 1";
	$st = $conn->prepare($sql);
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->bindValue(":value", $value, PDO::PARAM_STR);
	$st->execute();
}

function to_numeric_version($str_version){
	// Used to convert "1.5.0" to int 150
	return (int)str_replace('.', '', $str_version);
}

function curl_request($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		// If an error occured during the request, print the error
		echo 'Error:' . curl_error($ch);
		return false;
	}
	curl_close($ch);
	return $response;
}

function generate_small_thumbnail($path, $slug){
	// $path == $game->thumb_2
	// This function only work if thumb 2 is already stored locally
	if(!file_exists(ABSPATH.$path)){
		return 'error';
	}
	// $use_webp = get_setting_value('webp_thumbnail');
	$path_info = pathinfo($path);
	$root_folder = explode ("/", $path);
	$thumb_small = $path_info['dirname'] . "/" . $slug . '-' . $path_info['filename'] . "_small." . $path_info['extension'];
	if($path_info['extension'] == 'webp'){ // $use_webp
		$file_extension = pathinfo($path, PATHINFO_EXTENSION);
		$thumb_small = str_replace('.'.$file_extension, '.webp', $thumb_small);
		webp_resize(ABSPATH.$path, ABSPATH.$thumb_small, 160, 160);
	} else {
		imgResize(ABSPATH.$path, 160, 160, $slug);
	}
	return $thumb_small;
}

function update_content_translation($content_type, $content_id, $language, $field_data) {
	// Sample usage =
	// Single : update_content_translation('game', 1, 'en', ['title' => 'New Title']);
	// Multiple : update_content_translation('game', 1, 'en', ['title' => 'New Title', 'description' => 'New Description']);
	if (ADMIN_DEMO || !USER_ADMIN) {
		die('ERR 918');
	}
	$conn = open_connection();
	try {
		$conn->beginTransaction();
		foreach ($field_data as $field => $translation) {
			$checkSql = "SELECT COUNT(*) FROM translations WHERE content_type = :content_type AND content_id = :content_id AND language = :language AND field = :field";
			$checkStmt = $conn->prepare($checkSql);
			$checkStmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
			$checkStmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
			$checkStmt->bindParam(':language', $language, PDO::PARAM_STR);
			$checkStmt->bindParam(':field', $field, PDO::PARAM_STR);
			$checkStmt->execute();
			if ($checkStmt->fetchColumn() > 0) {
				$sql = "UPDATE translations SET translation = :translation WHERE content_type = :content_type AND content_id = :content_id AND language = :language AND field = :field";
			} else {
				$sql = "INSERT INTO translations (content_type, content_id, language, field, translation) VALUES (:content_type, :content_id, :language, :field, :translation)";
			}
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
			$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
			$stmt->bindParam(':language', $language, PDO::PARAM_STR);
			$stmt->bindParam(':field', $field, PDO::PARAM_STR);
			$stmt->bindParam(':translation', $translation, PDO::PARAM_STR);
			$stmt->execute();
		}
		$conn->commit();
		return true;
	} catch (Exception $e) {
		$conn->rollback();
		return false;
	}
}

function delete_content_translation($content_type, $content_id, $language = null, $field = null) {
	if (ADMIN_DEMO || !USER_ADMIN) {
		die('ERR 237');
	}
	$conn = open_connection();
	$sql = "DELETE FROM translations WHERE content_type = :content_type AND content_id = :content_id";
	if ($language !== null) {
		$sql .= " AND language = :language";
	}
	if ($field !== null) {
		$sql .= " AND field = :field";
	}
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':content_type', $content_type, PDO::PARAM_STR);
	$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
	if ($language !== null) {
		$stmt->bindParam(':language', $language, PDO::PARAM_STR);
	}
	if ($field !== null) {
		$stmt->bindParam(':field', $field, PDO::PARAM_STR);
	}
	return $stmt->execute();
}

?>