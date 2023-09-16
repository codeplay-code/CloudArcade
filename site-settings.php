<?php
	
require( 'includes/load-settings.php' );

define( "SITE_TITLE", SETTINGS['site_title']['value'] );
define( "SITE_DESCRIPTION", SETTINGS['site_description']['value'] );
define( "META_DESCRIPTION", SETTINGS['meta_description']['value'] );
define( "SITE_LOGO", SETTINGS['site_logo']['value'] );
define( "THEME_NAME", $options['theme_name'] );
define( "TEMPLATE_PATH", "content/themes/".THEME_NAME );
define( "IMPORT_THUMB", filter_var(SETTINGS['import_thumb']['value'], FILTER_VALIDATE_BOOLEAN) );
define( "COMPRESSION_LEVEL", 95 );
define( "CUSTOM_SLUG", filter_var(SETTINGS['custom_slug']['value'], FILTER_VALIDATE_BOOLEAN) );
define( "UNICODE_SLUG", filter_var(SETTINGS['unicode_slug']['value'], FILTER_VALIDATE_BOOLEAN) );
define( "SMALL_THUMB", filter_var(SETTINGS['small_thumb']['value'], FILTER_VALIDATE_BOOLEAN) );

?>