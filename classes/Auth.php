<?php
if(!isset($_SESSION['username'])){
	if(isset($_COOKIE['ca_auth'])){
		$data = CA_Auth::get_data();
		if($data){
			$user = User::getByUsername(CA_Auth::decrypt($data, 'f'));
			if($user){
				$_SESSION['username'] = $user->username;
				CA_Auth::update_token();
			}
		}
	}
}

class CA_Auth {
	//
	public static function generate_token($length = 10, $hash = true) {
		$chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
		$str = substr(str_shuffle($chars), 0, $length);
		if($hash){
			return password_hash($str, PASSWORD_DEFAULT);
		} else {
			return $str;
		}
	}

	public static function insert($data){
		$token = self::generate_token();

		$conn = open_connection();
		$sql = 'INSERT INTO sessions ( token, data ) 
				  VALUES ( :token, :data )';
		$st = $conn->prepare($sql);
		$st->bindValue(":token", $token, PDO::PARAM_STR);
		$st->bindValue(":data", $data, PDO::PARAM_STR);
		$st->execute();

		setcookie('ca_auth', $token, time() + (60 * 60 * 24 * 30 * 3), "/");
	}

	public static function update_token($old_token = null){
		$new_token = self::generate_token();
		if(is_null($old_token)){
			if(isset($_COOKIE['ca_auth'])){
				$old_token = $_COOKIE['ca_auth'];
			} else {
				return false;
			}
		}
		$conn = open_connection();
		$sql = 'UPDATE sessions SET token = :new_token WHERE token = :old_token';
		$st = $conn->prepare($sql);
		$st->bindValue(":new_token", $new_token, PDO::PARAM_STR);
		$st->bindValue(":old_token", $old_token, PDO::PARAM_STR);
		$st->execute();

		setcookie('ca_auth', $new_token, time() + (60 * 60 * 24 * 30 * 3), "/");
	}

	public static function delete($token = null){
		if(is_null($token)){
			if(isset($_COOKIE['ca_auth'])){
				$token = $_COOKIE['ca_auth'];
			} else {
				return false;
			}
		}
		$conn = open_connection();
		$sql = 'DELETE FROM sessions WHERE token = :token';
		$st = $conn->prepare($sql);
		$st->bindValue(":token", $token, PDO::PARAM_STR);
		$st->execute();

		setcookie('ca_auth', time() - 3600);
	}

	public static function get_data($token = null){
		if(is_null($token)){
			if(isset($_COOKIE['ca_auth'])){
				$token = $_COOKIE['ca_auth'];
			} else {
				return false;
			}
		}
		$conn = open_connection();
		$sql = "SELECT * FROM sessions WHERE token = :token";
		$st = $conn->prepare($sql);
		$st->bindValue(":token", $token, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetchAll();
		if(count($row) == 1){
			return $row[0]['data'];
		} else {
			return false;
		}
	}

	public static function decrypt($str, $key){
		$cipher = "AES-128-CTR";
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = '1234567891011121';
		return openssl_decrypt($str, $cipher, $key, $options=0, $iv);
	}
}

?>