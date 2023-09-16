<?php

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$conn = null;

define( 'ABSPATH', __DIR__ . '/' );
define( "ADMIN_PATH", "admin" );
define( "CLASS_PATH", "classes" );

require( 'site-settings.php' );
require( 'includes/load-class.php' );
require( 'includes/game_list.php' );
require( 'includes/commons.php' );
require( 'includes/sessions.php' );

function open_connection(){
	global $conn;
	if(!$conn){
		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	return $conn;
}

?>