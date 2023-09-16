<?php

require_once( TEMPLATE_PATH . '/functions.php' );

$page_title = '404 - '._t('Page not found').' | '.SITE_TITLE;
$meta_description = _t('Page not found');

require( TEMPLATE_PATH . '/404.php' );

?>