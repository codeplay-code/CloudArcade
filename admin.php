<?php

session_start();

require( "config.php" );
require( "init.php" );
require( 'includes/plugin.php' );

$action = isset( $_GET['action'] ) ? $_GET['action'] : "";
$username = isset( $_SESSION['username'] ) ? $_SESSION['username'] : "";

if ( $action != "logout" && !$username ) {
	require("includes/page-login.php" );
	exit;
}

switch ( $action ) {
	case 'logout':
		logout();
		break;
	default:
		header( "Location: admin/dashboard.php" );
}

function logout() {
	CA_Auth::delete();
	unset( $_SESSION['username'] );
	header( "Location: ".DOMAIN );
	return;
}

?>