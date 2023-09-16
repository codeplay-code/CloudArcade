<?php

require( '../config.php' );
require( '../init.php' );

$content_type = 'game';

if(isset($_POST['type'])){
	$content_type = $_POST['type'];
}

if($content_type == 'game'){
	if(isset($_POST['category_id'])){
		$cat_id = (int)$_POST['category_id'];
		$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 10;
		$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
		$data = Category::getListByCategory( $cat_id, $amount, $offset );
		if($data){
			echo json_encode($data['results']);
		} else {
			echo '[]';
		}
	} else {
		if(isset($_POST['sort_by'])){
			$sort = $_POST['sort_by'];
			$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 10;
			$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
			$result = [];
			if($sort == 'new'){
				$data = Game::getList( $amount, 'id DESC', $offset );
				$result = $data['results'];
			} elseif($sort == 'random'){
				$data = Game::getList( $amount, 'RAND()', $offset );
				$result = $data['results'];
			} elseif($sort == 'popular'){
				$data = Game::getList( $amount, 'views DESC', $offset );
				$result = $data['results'];
			} elseif($sort == 'likes'){
				$data = Game::getList( $amount, 'upvote DESC', $offset );
				$result = $data['results'];
			}
			echo json_encode($result);
		}	
	}
}

?>