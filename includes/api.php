<?php

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

require( '../config.php' );
require( '../init.php' );

if(isset($_POST['action'])){
	$score = null;
	if($_POST['action'] === 'submit'){
		if($login_user){ //Only logged in user
			$user_id = $login_user->id;
			if(isset($_POST['value']) && isset($_POST['ref'])){
				$score = $_POST['value'];
				$score = base64_decode($score);
				$score = $score*1.33;
				if (strpos($score, '.')) { 
				    //invalid
				} else {
					$game = Game::getBySlug($_POST['ref']);
					if($game){
						$game_id = $game->id;
						$conn = open_connection();
						$sql = 'SELECT score FROM scores WHERE user_id = :user_id AND game_id = :game_id LIMIT 1';
						$st = $conn->prepare($sql);
						$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
						$st->bindValue(":user_id", $user_id, PDO::PARAM_INT);
						$st->execute();
						$row = $st->fetch();
						if($row){ //Update existing data
							if($row['score'] < $score){
								$sql = 'UPDATE scores SET score = :score WHERE user_id = :user_id AND game_id = :game_id LIMIT 1';
								$st = $conn->prepare($sql);
								$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
								$st->bindValue(":user_id", $user_id, PDO::PARAM_INT);
								$st->bindValue(":score", $score, PDO::PARAM_INT);
								$st->execute();
							}
						} else {
							$sql = 'INSERT INTO scores (game_id, user_id, score) VALUES ( :game_id, :user_id, :score)';
							$st = $conn->prepare($sql);
							$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
							$st->bindValue(":user_id", $user_id, PDO::PARAM_INT);
							$st->bindValue(":score", $score, PDO::PARAM_INT);
							$st->execute();
						}
						//
						$login_user->xp += 10;
						$login_user->update_xp();
						//
						echo 'ok';
					}
				}
			} else {
				die('x');
			}
		}	
	} elseif ($_POST['action'] === 'get_current_user'){
		if($login_user){
			$user = array();
			$user['username'] = $login_user->username;
			$user['id'] = $login_user->id;
			$user['gender'] = $login_user->gender;
			$user['join_date'] = $login_user->join_date;
			$user['birth_date'] = $login_user->birth_date;
			echo json_encode($user);
		}
	} elseif ($_POST['action'] === 'get_user_score'){
		//Get current user score
		if($login_user){
			$user_id = $login_user->id;
			$game = Game::getBySlug($_POST['ref']);
			if(!$game){
				die();
			}
			$game_id = $game->id;
			$sql = "SELECT score FROM scores WHERE user_id = :user_id AND game_id = :game_id LIMIT 1";
			$conn = open_connection();
			$st = $conn->prepare($sql);
			$st->bindValue(":user_id", $user_id, PDO::PARAM_INT);
			$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
			$st->execute();
			$res = $st->fetch();
			if($res){
				echo $res['score'];
			} else {
				echo 0;
			}
		}
	} elseif ($_POST['action'] === 'get_score_rank'){
		//Get current user score rank
		if($login_user){
			$user_id = $login_user->id;
			$game = Game::getBySlug($_POST['ref']);
			if(!$game){
				die();
			}
			$game_id = $game->id;
			$sql = "SELECT * FROM scores WHERE game_id = :game_id ORDER by score DESC LIMIT 5000";
			$conn = open_connection();
			$st = $conn->prepare($sql);
			$st->bindValue(":game_id", $game_id, PDO::PARAM_INT);
			$st->execute();
			$row = $st->fetchAll(PDO::FETCH_ASSOC);
			if(count($row)){
				$i = 0;
				foreach ($row as $item) {
					$i++;
					if($item['user_id'] == $user_id){
						echo $i;
						return;
					}
				}
			}
			echo 0;
		}
	} elseif ($_POST['action'] === 'get_scoreboard'){
		if(isset($_POST['conf'])){
			$config = json_decode($_POST['conf'], true);
			$type = $config['type'];
			$amount = 10;
			if(isset($config['amount'])){
				$amount = $config['amount'];
			}
			$sql = null;
			$game = null;
			$game_id = null;
			if(isset($_POST['ref'])){
				//Old method
				$game = Game::getBySlug($_POST['ref']);
				if($game){
					$game_id = $game->id;
				}
			} elseif(isset($_POST['game-id'])){
				//New preferred method
				$game_id = (int)$_POST['game-id'];
			}
			if(!$game_id){
				die();
			}
			if($type === 'top-all'){
				$sql = "SELECT * FROM scores ORDER by score DESC, created_date ASC LIMIT ".$amount;
			} elseif($type === 'top-all-day'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top-all-week'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 WEEK) ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top-all-month'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top'){
				$sql = "SELECT * FROM scores WHERE game_id = ".$game_id." ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top-day'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND game_id = ".$game_id." ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top-week'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 WEEK) AND game_id = ".$game_id." ORDER by score DESC LIMIT ".$amount;
			} elseif($type === 'top-month'){
				$sql = "SELECT * FROM scores WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 MONTH) AND game_id = ".$game_id." ORDER by score DESC LIMIT ".$amount;
			}
			if($sql){
				$conn = open_connection();
				$st = $conn->prepare($sql);
				$st->execute();
				//
				$row = $st->fetchAll(PDO::FETCH_ASSOC);
				$list = [];
				foreach($row as $item){
					$item['game_title'] = Game::getById($item['game_id'])->title;
					$item['username'] = User::getById($item['user_id'])->username;
					array_push($list, $item);
				}
				echo json_encode($list);
			}	
		}
	} elseif ($_POST['action'] === 'load_ad'){
		if(isset($_POST['value'])){
			$tags = get_pref('ads-manager');
			if($tags){
				$tags = json_decode($tags, true);
				$selected = null;
				foreach ($tags as $tag => $item) {
					if(strtolower($_POST['value']) == strtolower($tag)){
						$selected = $item;
						$selected['type'] = strtolower($tag);
						break;
					}
				}
				if(!$selected){
					foreach ($tags as $tag => $item) {
						if($item['default']){
							$selected = $item;
							$selected['type'] = strtolower($tag);
							break;
						}
					}
				}
				if($selected['type'] == 'banner'){
					if($selected['selected'] == 'random'){
						if(isset($selected['data']) && $selected['data']){
							$picked_banner = $selected['data'][rand(0, count($selected['data'])-1)];
							$selected['value'] = $picked_banner['image'];
							$selected['url'] = $picked_banner['url'];
							$selected['name'] = $picked_banner['name'];
							//Add show stats
							$ad_stats = get_pref('ads-manager-stats');
							if($ad_stats){
								$ad_stats = json_decode($ad_stats, true);
							} else {
								$ad_stats = array();
							}
							if(!isset($ad_stats[$picked_banner['name']])){
								$ad_stats[$picked_banner['name']] = array();
								$ad_stats[$picked_banner['name']]['views'] = 0;
								$ad_stats[$picked_banner['name']]['clicks'] = 0;
							}
							$ad_stats[$picked_banner['name']]['views']++;
							update_option('ads-manager-stats', json_encode($ad_stats));
							//End
						}
					}
					$selected['delay'] = 5;
				}
				echo json_encode($selected);
			} else {
				echo '{"error": "Ads Manager plugin not installed."}';
			}
		}
	} elseif ($_POST['action'] === 'ad_clicked'){
		if(isset($_POST['value'])){
			//Add click stats
			$name = $_POST['value'];
			$ad_stats = get_pref('ads-manager-stats');
			if($ad_stats){
				$ad_stats = json_decode($ad_stats, true);
			} else {
				$ad_stats = array();
			}
			if(!isset($ad_stats[$name])){
				$ad_stats[$name] = array();
				$ad_stats[$name]['views'] = 0;
				$ad_stats[$name]['clicks'] = 0;
			}
			$ad_stats[$name]['clicks']++;
			update_option('ads-manager-stats', json_encode($ad_stats));
			//End
		}
	}
}

?>