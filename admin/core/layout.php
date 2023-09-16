<?php

$slug = isset($_GET['slug']) ? $_GET['slug'] : 'menus';

$tab_list = array(
	'menus' => 'Menus',
	'widgets' => 'Widgets',
);

if(file_exists( ABSPATH . TEMPLATE_PATH . '/options.php' )){
	$tab_list['theme-options'] = 'Theme Options';
}

if($slug == 'menus'){
	require_once( 'core/menus.php' );
} elseif($slug == 'widgets'){
	require_once( 'core/widgets.php' );
} elseif($slug == 'theme-options'){
	require_once( 'core/theme-options.php' );
}

?>