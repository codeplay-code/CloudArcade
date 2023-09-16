<?php

require('../config.php');
require('../init.php');

if(get_setting_value('comments')){
	if(isset($_POST['send']) && $login_user){
		// Deprecated since v165
		// Replaced with new Comment system
		// Still kept for compatibility with the old commenting system
		$conn = open_connection();
		if(isset($_POST['source']) && $_POST['source'] == 'jquery-comments'){
			if(!$_POST['parent']){
				$_POST['parent'] = null;
			}
			$_POST['content'] = trim_string(comment_filtering($_POST['content']));
			$approved = 1;
			if(get_setting_value('moderate_comment') && $login_user->role != 'admin'){
				// Moderate comment is activated
				$approved = 0;
			}
			$sql = 'INSERT INTO comments (parent_id, game_id, comment, sender_id, sender_username, created_date, approved) VALUES (:parent_id, :game_id, :comment, :sender_id, :sender_username, :created_date, :approved)';
			$st = $conn->prepare($sql);
			$st->bindValue(":parent_id", $_POST['parent'], PDO::PARAM_INT);
			$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
			$st->bindValue(":comment", $_POST['content'], PDO::PARAM_STR);
			$st->bindValue(":sender_id", $login_user->id, PDO::PARAM_INT);
			$st->bindValue(":sender_username", $login_user->username, PDO::PARAM_STR);
			$st->bindValue(":created_date", date('Y-m-d H:m:s'), PDO::PARAM_STR);
			$st->bindValue(":approved", $approved, PDO::PARAM_INT);
			$st->execute();

			$login_user->add_xp(20);

			echo('success');
		}
	}
	if(isset($_POST['load']) && isset($_POST['game_id'])){
		// Deprecated since v165
		// Replaced with new Comment system
		// Still kept for compatibility with the old commenting system
		$conn = open_connection();
		$sql = 'SELECT * FROM comments WHERE game_id = :game_id AND approved = 1 ORDER BY id asc, parent_id asc LIMIT 50';
		$st = $conn->prepare($sql);
		$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = array();
		foreach ($row as $item) {
			$item['avatar'] = get_user_avatar($item['sender_username']);
			$list[] = $item;
		}
		echo json_encode((array)$list);
	}
	// New comment system
	if(isset($_POST['send_comment']) && $login_user){
		if(strlen($_POST['content']) < 2){
			echo('too short');
			return;
		}
		$conn = open_connection();
		if(!$_POST['parent']){
			$_POST['parent'] = null;
		}
		$_POST['content'] = trim_string(comment_filtering($_POST['content']));
		$approved = 1;
		if(get_setting_value('moderate_comment') && $login_user->role != 'admin'){
			// Moderate comment is activated
			$approved = 0;
		}
		$sql = 'INSERT INTO comments (parent_id, game_id, comment, sender_id, sender_username, created_date, approved) VALUES (:parent_id, :game_id, :comment, :sender_id, :sender_username, :created_date, :approved)';
		$st = $conn->prepare($sql);
		$st->bindValue(":parent_id", $_POST['parent'], PDO::PARAM_INT);
		$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
		$st->bindValue(":comment", $_POST['content'], PDO::PARAM_STR);
		$st->bindValue(":sender_id", $login_user->id, PDO::PARAM_INT);
		$st->bindValue(":sender_username", $login_user->username, PDO::PARAM_STR);
		$st->bindValue(":created_date", date('Y-m-d H:m:s'), PDO::PARAM_STR);
		$st->bindValue(":approved", $approved, PDO::PARAM_INT);
		$st->execute();
		$login_user->add_xp(20);
		echo('success');
	} elseif(isset($_POST['load_root_comments']) && isset($_POST['game_id']) && isset($_POST['amount'])) {
		$conn = open_connection();
		$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
		$limit = $_POST['amount'];
		if($limit > 30){
			$limit = 30;
		}
		$sql = 'SELECT c.*, COUNT(r.id) as reply_count 
				FROM comments c
				LEFT JOIN comments r ON c.id = r.parent_id
				WHERE c.game_id = :game_id AND (c.parent_id IS NULL OR c.parent_id = 0) AND c.approved = 1
				GROUP BY c.id
				ORDER BY c.id DESC LIMIT :limit OFFSET :offset';
		$st = $conn->prepare($sql);
		$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
		$st->bindValue(":limit", $limit, PDO::PARAM_INT);
		$st->bindValue(":offset", $offset, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = [];
		foreach ($row as $item) {
			$item['avatar'] = get_user_avatar($item['sender_username']);
			$item['has_replies'] = $item['reply_count'] > 0;
			unset($item['reply_count']); // remove the reply_count as it's not needed anymore
			$list[] = $item;
		}
		echo json_encode((array)$list);
	} elseif(isset($_POST['load_replies']) && isset($_POST['parent_id']) && isset($_POST['amount'])) {
		$conn = open_connection();
		$limit = $_POST['amount'];
		if($limit > 30){
			$limit = 30;
		}
		$sql = 'SELECT * FROM comments WHERE parent_id = :parent_id AND approved = 1 ORDER BY id DESC LIMIT :limit';
		$st = $conn->prepare($sql);
		$st->bindValue(":parent_id", $_POST['parent_id'], PDO::PARAM_INT);
		$st->bindValue(":limit", $limit, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = [];
		foreach ($row as $item) {
			$item['avatar'] = get_user_avatar($item['sender_username']);
			$list[] = $item;
		}
		echo json_encode((array)$list);
	}

}

if(isset($_POST['delete']) && $login_user){
	$conn = open_connection();
	if( USER_ADMIN && !ADMIN_DEMO){
		$sql = 'DELETE FROM comments WHERE id = :id LIMIT 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
		$st->execute();
	} else {
		$sql = 'DELETE FROM comments WHERE sender_id = :sender_id AND id = :id LIMIT 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":sender_id", $login_user->id, PDO::PARAM_INT);
		$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
		$st->execute();
	}
	echo 'deleted';
}

if(isset($_POST['approve']) && $login_user && USER_ADMIN){
	$conn = open_connection();
	$sql = 'UPDATE comments SET approved = 1 WHERE id = :id LIMIT 1';
	$st = $conn->prepare($sql);
	$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
	$st->execute();
	echo 'ok';
}

function comment_filtering($comment){
	if(file_exists(ABSPATH.'includes/banned-words-comment.json')){
		$words = json_decode(file_get_contents(ABSPATH.'includes/banned-words-comment.json'), true);
		$comment = str_ireplace($words, '***', $comment);
	}
	return $comment;
}

function trim_string($str) {
	if (strlen($str) > 400) {
		return substr($str, 0, 397) . '...';
	}
	return $str;
}

?>