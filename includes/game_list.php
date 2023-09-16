<?php

// Deprecated since v1.6.4 replaced with theme-functions.php
// However, this script maybe still used in admin area

function get_game_list($type, $amount=12, $page=0, $count=true){
	if($type == 'new'){
		$data = Game::getList( $amount, 'id DESC', $page, $count );
		return $data;
	} elseif($type == 'random'){
		$data = Game::getList( $amount, 'RAND()', $page, $count );
		return $data;
	} elseif($type == 'popular'){
		$data = Game::getList( $amount, 'views DESC', $page, $count );
		return $data;
	} elseif($type == 'likes'){
		$data = Game::getList( $amount, 'upvote DESC', $page, $count );
		return $data;
	} elseif($type == 'trending'){
		// Last 7 days trending
		$data = [];
		$conn = open_connection();
		$date = new \DateTime('now');
		// Get last 7 days
		$date->sub(new DateInterval('P7D'));  
		$sql = "SELECT * FROM trends WHERE created >= '{$date->format('Y-m-d')}'";
		$st = $conn->prepare($sql);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = array();
		if(count($row)){
			foreach ($row as $item) {
				if(isset($list[$item['slug']])){
					$list[$item['slug']] += (int)$item['views'];
				} else {
					$list[$item['slug']] = (int)$item['views'];
				}
			}
			arsort($list);
			$i = 0;
			foreach ($list as $slug => $views) {
				if($i < $amount){
					$game = Game::getBySlug($slug);
					if($game){
						$data[] = $game;
					}
				}
				$i++;
			}
		}
		return (array(
			"results" => $data,
			"totalRows" => count($list),
			"totalPages" => 1
		));
	}
}
function get_collection($name, $amount = 12){
	$data = Collection::getListByCollection( $name, $amount );
	return $data;
}
function get_game_list_category($cat_name, $amount, $page=0){
	$cat_id = Category::getIdByName( $cat_name );
	$data = Category::getListByCategory( $cat_id, $amount, $page );
	return $data;
}
function get_game_list_category_id($cat_id, $amount, $page=0){
	$data = Category::getListByCategory( $cat_id, $amount, $page );
	return $data;
}
function get_game_list_categories($arr, $amount, $page=0, $random = true){
	$ids = array();
	foreach ($arr as $cat_name) {
		$cat_id = Category::getIdByName( $cat_name );
		array_push($ids, $cat_id);
	}
	$data = Category::getListByCategories( $ids, $amount, $page, $random );
	return $data;
}

?>