<?php

class User {
	public $id = null;
	public $username = null;
	public $password = null;
	public $email = null;
	public $birth_date = null;
	public $join_date = null;
	public $gender = null;
	public $role = null;
	public $data = null;
	public $avatar = 0;
	public $bio = '';
	public $xp = 0;
	public $rank = '-';
	public $level = 1;

	public function __construct($data = array())
	{
		if (isset($data['id'])) $this->id = (int)$data['id'];
		if (isset($data['username'])) $this->username = $data['username'];
		if (isset($data['password'])) $this->password = $data['password'];
		if (isset($data['email'])) $this->email = $data['email'];
		if (isset($data['birth_date'])) $this->birth_date = $data['birth_date'];
		if (isset($data['join_date'])) $this->join_date = $data['join_date'];
		if (isset($data['gender'])) $this->gender = $data['gender'];
		if (isset($data['data'])) $this->data = json_decode($data['data'], true);
		if (isset($data['avatar'])) $this->avatar = $data['avatar'];
		if (isset($data['role'])) $this->role = $data['role'];
		if (isset($data['bio'])) $this->bio = $data['bio'];
		if (isset($data['xp'])) $this->xp = $data['xp'];
		if(is_null($this->xp)){
			$this->xp = 0;
		}
		if(is_null($this->birth_date)){
			$this->birth_date = date('Y-m-d');
		}

		if(!$this->data){
			$this->data = array();
			$this->data['likes'] = [];
		}

		if(file_exists(ABSPATH.'includes/rank.json')){
			$rank = json_decode(file_get_contents(ABSPATH.'includes/rank.json'), true);
			if($rank){
				$index = 0;
				foreach ($rank as $name => $value) {
					if($this->xp >= $value){
						$index++;
						$this->level = $index;
						$this->rank = $name;
					}
				}
			}
		}
	}

	public function storeFormValues($params)
	{
		$this->__construct($params);
		if(is_null($this->join_date)){
			$this->join_date = date('Y-m-d');
		}
	}

	public static function getById($id)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM users WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new User($row); //$row
	}

	public static function getByUsername($username)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":username", $username, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new User($row); //$row
	}

	public static function getList(int $amount = 30, $sort = 'desc', int $page = 0)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM users
			ORDER BY id $sort LIMIT $amount OFFSET $page";
		$st = $conn->prepare($sql);
		$st->execute();
		$list = array();

		while ($row = $st->fetch())
		{
			$user = new User($row);
			$list[] = $user;
		}
		$totalRows = $conn->query('SELECT count(*) FROM users')->fetchColumn();
		$totalPages = 0;
		if (count($list))
		{
			$totalPages = ceil($totalRows / $amount);
		}
		return (array(
			"results" => $list,
			"totalRows" => $totalRows,
			"totalPages" => $totalPages
		));
	}

	public static function getByEmail($email)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":email", $email, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new User($row); //$row
	}

	public function array_id_exist($id)
	{
		if(!is_null($this->id)){
			if(!is_null($this->data) || isset($this->data['likes'])){
				$index = 1;
				foreach ($this->data['likes'] as $val) {
					if($val == $id){
						return $index;
					}
					$index++;
				}
				return false;
			}
		}
	}

	public function favoriteGames()
	{
		if(!is_null($this->id)){
			$conn = open_connection();
			$sql = "SELECT * FROM favorites WHERE user_id = :user_id ORDER BY id DESC";
			$st = $conn->prepare($sql);
			$st->bindValue(":user_id", $this->id, PDO::PARAM_INT);
			$st->execute();
			$row = $st->fetchAll();
			return $row;
		}
		return null;
	}

	public static function getTotalUsers(){
		// Get total users
		$conn = open_connection();
		$sql = "SELECT COUNT(*) FROM users";

		$st = $conn->prepare($sql);
		$st->execute();
		return $st->fetchColumn();
	}

	public function like($id)
	{
		if(!is_null($this->id)){
			if(is_null($this->data) || $this->data == ''){
				$this->data = array();
				$this->data['likes'] = array();
			}
			if(!$this->array_id_exist($id)){
				array_push($this->data['likes'], $id);
			}
			$this->xp += 10;
			$this->update_data();
			$this->update_xp();
		} else {
			echo "User is null";
		}
	}

	public function dislike($id)
	{
		if(!is_null($this->id)){
			if(is_null($this->data) || $this->data == ''){
				$this->data = array();
				$this->data['likes'] = array();
			}
			$arr = $this->array_id_exist($id);
			if($arr){
				array_splice($this->data['likes'], $arr-1, 1);
				$this->update_data();
			}
		} else {
			echo "User is null";
		}
	}

	public function insert()
	{
		if (!is_null($this->id)) trigger_error("User::insert(): Attempt to insert an User object that already has its ID property set (to $this->id).", E_USER_ERROR);

		if(!$this->avatar){
			$this->avatar = rand(1,20);
		}

		$conn = open_connection();
		$sql = 'INSERT INTO users ( username, password, email, birth_date, join_date, gender, data, role, avatar ) 
				  VALUES ( :username, :password, :email, :birth_date, :join_date, :gender, :data, :role, :avatar )';
		$st = $conn->prepare($sql);
		$st->bindValue(":username", $this->username, PDO::PARAM_STR);
		$st->bindValue(":password", $this->password, PDO::PARAM_STR);
		$st->bindValue(":email", $this->email, PDO::PARAM_STR);
		$st->bindValue(":birth_date", $this->birth_date, PDO::PARAM_STR);
		$st->bindValue(":join_date", $this->join_date, PDO::PARAM_STR);
		$st->bindValue(":gender", $this->gender, PDO::PARAM_STR);
		$st->bindValue(":data", json_encode($this->data), PDO::PARAM_STR);
		$st->bindValue(":role", 'user', PDO::PARAM_STR);
		$st->bindValue(":avatar", $this->avatar, PDO::PARAM_INT);
		$st->execute();
		$this->id = $conn->lastInsertId();
	}

	public function update_data()
	{
		if (is_null($this->id)) trigger_error("User::update(): Attempt to update an User object that does not have its ID property set.", E_USER_ERROR);
		//
		$conn = open_connection();
		$sql = "UPDATE users SET data=:data WHERE id = :id";

		$st = $conn->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":data", json_encode($this->data), PDO::PARAM_STR);
		$st->execute();
	}

	public function update_xp()
	{
		if (is_null($this->id)) trigger_error("User::update(): Attempt to update an User object that does not have its ID property set.", E_USER_ERROR);
		//
		$conn = open_connection();
		$sql = "UPDATE users SET xp=:xp WHERE id = :id";

		$st = $conn->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":xp", $this->xp, PDO::PARAM_INT);
		$st->execute();
	}

	public function add_xp($val)
	{
		if (is_null($this->id)) trigger_error("User::update(): Attempt to update an User object that does not have its ID property set.", E_USER_ERROR);
		//
		$this->xp += (int)$val;
		
		$conn = open_connection();
		$sql = "UPDATE users SET xp=:xp WHERE id = :id";

		$st = $conn->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":xp", $this->xp, PDO::PARAM_INT);
		$st->execute();
	}

	public function update()
	{
		if (is_null($this->id)) trigger_error("User::update(): Attempt to update an User object that does not have its ID property set.", E_USER_ERROR);
		//
		$conn = open_connection();
		$sql = "UPDATE users SET username=:username, password=:password, email=:email, birth_date=:birth_date, gender=:gender, bio=:bio, avatar=:avatar WHERE id = :id";

		$st = $conn->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":username", $this->username, PDO::PARAM_STR);
		$st->bindValue(":password", $this->password, PDO::PARAM_STR);
		$st->bindValue(":email", $this->email, PDO::PARAM_STR);
		$st->bindValue(":birth_date", $this->birth_date, PDO::PARAM_STR);
		$st->bindValue(":gender", $this->gender, PDO::PARAM_STR);
		$st->bindValue(":bio", $this->bio, PDO::PARAM_STR);
		$st->bindValue(":avatar", $this->avatar, PDO::PARAM_INT);
		$st->execute();

	}

	public function delete( $pass = null )
	{
		if (is_null($this->id)) trigger_error("User::delete(): Attempt to delete an User object that does not have its ID property set.", E_USER_ERROR);

		if(password_verify($pass, $this->password) || USER_ADMIN){
			$conn = open_connection();
			$st = $conn->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
			$st->bindValue(":id", $this->id, PDO::PARAM_INT);
			$st->execute();
			//Delete its avatar if exist
			if(file_exists( ABSPATH.'images/avatar/'.$this->username.'.png' )){
				unlink( ABSPATH.'images/avatar/'.$this->username.'.png' );
			}

			//Remove all comments from this user
			$st = $conn->prepare("DELETE FROM comments WHERE sender_id = :id");
			$st->bindValue(":id", $this->id, PDO::PARAM_INT);
			$st->execute();

			//Remove all scores from this user
			$st = $conn->prepare("DELETE FROM scores WHERE user_id = :id");
			$st->bindValue(":id", $this->id, PDO::PARAM_INT);
			$st->execute();
		}
	}
}

?>
