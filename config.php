<?php

if(!file_exists( __DIR__."/connect.php") ){
	if(file_exists("install.php")){
		header('Location: install.php');
	} elseif(file_exists("../install.php")) {
		header('Location: ../install.php');
	}
	exit('CloudArcade not installed yet.');
}

require("connect.php");
require("includes/version.php");

function handleException( $exception ) {
	echo($exception);
	error_log( $exception->getMessage() );
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_exception_handler( 'handleException' );

?>